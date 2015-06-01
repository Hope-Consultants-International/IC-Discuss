const debug = <?php print( ($debug) ? 'true' : 'false' ); ?>;
const input_timer = 750;

function get_db_id(element) {
	return element.attr("id").split("-")[1];
}

// we want to send updates as soon as they are entered,
// so we set a timeout while input is received and 
// execute the update when there is no input on the summary for 1s
var summary_timers = [];
function queue_summary_update() {
	var summary = $( this ).closest(".synth-summary");
	var summary_id = get_db_id(summary);
	
	if (summary_id in summary_timers) {
		clearTimeout(summary_timers[summary_id]);
	}
	summary_timers[summary_id] = setTimeout(function() { do_summary_update(summary_id); }, input_timer);
}
function cancel_summary_update(summary_id) {
	if (summary_id in summary_timers) {
		clearTimeout(summary_timers[summary_id]);
	}
	delete summary_timers[summary_id];
}
function do_summary_update(summary_id) {
	console.debug("Update Summary " + summary_id);
	var summary = $( "#summary-" + summary_id );
	var summary_text = summary.find(".synth-summary-text").val();
	
	var data = {
		action: "update_summary",
		summary: summary_id,
		summary_text: summary_text
	};
	var jqxhr = $.ajax({
		type: "POST",
		url: ajaxHandlerURL,
		data: data,
		dataType: 'json',
		error: function(jqXHR_obj, message, error) {
			console.error("Could not send update: " + message);
		},
		success: function(reply, message) {
			if (reply['success']) {
				console.info("Update success: " + reply['message']);
			} else {
				console.error("Update failure: " + reply['message']);
			}
		}
	});
}

function new_summary() {
	var statement = $( this ).closest(".synth-statement");
	var statement_text = statement.find(".statement-text").text();
	
	var statement_id = get_db_id(statement);
	console.debug("New Summary from Statement " + statement_id);
	
	var data = {
		action: "new_summary",
		statement: statement_id
	};
	var jqxhr = $.ajax({
		type: "POST",
		url: ajaxHandlerURL,
		data: data,
		dataType: 'json',
		error: function(jqXHR_obj, message, error) {
			console.error("Could not send update: " + message);
		},
		success: function(reply, message) {
			if (reply['success']) {
				console.info("Update success: " + reply['message']);
				var summary_id = reply['summary_id'];
	
				$( "#synth-summaries" ).append('<div class="synth-summary" id="summary-' + summary_id + '"></div>');
				var summary = $( "#summary-" + summary_id );
				summary.append('<div class="form-group"><textarea class="synth-summary-text">' + statement_text + '</textarea></div>');
				summary.append('<div class="btn-group" role="group"><button class="btn btn-danger synth-delete"><span class="glyphicon glyphicon-trash" aria-hidden="true" onclick=""></span> Delete</button></div>');
				
				// since this is a new object, we have to make it droppable
				// and bind event handlers
				make_summary_droppable(summary);
				summary.find('.synth-delete').on( "click", delete_summary);
				summary.find(".synth-summary-text").on("input", queue_summary_update);
				
				statement.appendTo(summary);
				statement.find('.btn-group').fadeOut(50);
			} else {
				console.error("Update failure: " + reply['message']);
			}
		}
	});
}

function delete_summary() {
	var summary = $( this ).closest(".synth-summary");
	var summary_id = get_db_id(summary);
	cancel_summary_update(summary_id);
		
	summary.find(".synth-statement").each(function( index ) {
		$( this ).appendTo($( "#synth-statements" ))
		$( this ).find( '.btn-group').fadeIn(50);
	});
	summary.remove();

	console.debug("Delete Summary " + summary_id);
	
	var data = {
		action: "delete_summary",
		summary: summary_id
	};
	var jqxhr = $.ajax({
		type: "POST",
		url: ajaxHandlerURL,
		data: data,
		dataType: 'json',
		error: function(jqXHR_obj, message, error) {
			console.error("Could not send update: " + message);
		},
		success: function(reply, message) {
			if (reply['success']) {
				console.info("Update success: " + reply['message']);
			} else {
				console.error("Update failure: " + reply['message']);
			}
		}
	});
}

var ajaxHandlerURL = "<?php print(htmlentities($handler_url)); ?>";

function make_summary_droppable(summary) {
	summary.droppable({
		activeClass: "ui-state-default",
		hoverClass: "ui-state-hover",
		drop: function( event, ui ) {
			// add object
			ui.draggable.appendTo( this );
			// remove buttons
			ui.draggable.find( '.btn-group').fadeOut(50);
			
			// do the data update in the background
			var statement_id = get_db_id(ui.draggable);
			var summary_id = get_db_id($(event.target));
			var old_summary_id = ui.draggable.attr("data-old-summary-id");
			console.debug("Link Statement " + statement_id + " to Summary " + summary_id + " was linked to " + old_summary_id);
			
			var data = {
				action: "link",
				statement: statement_id,
				summary: summary_id,
				summary_old: ui.draggable.attr("data-old-summary-id")
			};
			var jqxhr = $.ajax({
				type: "POST",
				url: ajaxHandlerURL,
				data: data,
				dataType: 'json',
				error: function(jqXHR_obj, message, error) {
					console.error("Could not send update: " + message);
				},
				success: function(reply, message) {
					if (reply['success']) {
						console.info("Update success: " + reply['message']);
					} else {
						console.error("Update failure: " + reply['message']);
					}
				}
			});
		}
	});
}

function make_statement_droppable(statement) {
	statement.droppable({
		activeClass: "ui-state-default",
		hoverClass: "ui-state-hover",
		drop: function( event, ui ) {
			// add object
			ui.draggable.appendTo( this );
			// remove buttons
			ui.draggable.find(".btn-group").fadeIn(50);
			
			// do the data update in the background
			var statement_id = get_db_id(ui.draggable);
			var old_summary_id = ui.draggable.attr("data-old-summary-id");
			console.debug("Unlink Statement " + statement_id + " was linked to " + old_summary_id);
			
			var data = {
				action: "unlink",
				statement: statement_id,
				summary_old: old_summary_id
			};
			var jqxhr = $.ajax({
				type: "POST",
				url: ajaxHandlerURL,
				data: data,
				dataType: 'json',
				error: function(jqXHR_obj, message, error) {
					console.error("Could not send update: " + message);
				},
				success: function(reply, message) {
					if (reply['success']) {
						console.info("Update success: " + reply['message']);
					} else {
						console.error("Update failure: " + reply['message']);
					}
				}
			});
		}
	});
}

function make_statement_draggable(statement) {
	statement.draggable({
		appendTo: "body",
		helper: "clone",
		containment: "#synth-main",
		revert: "invalid",
		start: function( event, ui ) {
			var old_summary_id = null;
			if ($(event.target).closest("#synth-statements").length) {
				old_summary_id = null;
				console.debug("Old Summary ID: null");
			} else if($(event.target).closest(".synth-summary").length) {
				var summary = $(event.target).closest(".synth-summary");
				old_summary_id = get_db_id(summary);
				console.debug("Old Summary ID: " + old_summary_id);
			}
			$(event.target).attr("data-old-summary-id", old_summary_id);
			
			// remove old object
			$( "#" + event.target.id ).fadeOut(400);

			// make sure the draggable object doesn't resize
			ui.helper.css("width", $( "#" + event.target.id ).css("width"));
			ui.helper.css("cursor", 'grabbing');
		},
		stop: function( event, ui ) {
			$( "#" + event.target.id ).fadeIn(100);
		}
    });
}

$(function() {
	// prepare draggable objects
	$( ".synth-statement" ).each( function( index ) {
		make_statement_draggable( $(this) );
	});
	
	// prepare drop areas
    $( ".synth-summary" ).each( function( index ) {
		make_summary_droppable( $(this) );
	});
	$( "#synth-statements" ).each( function( index ) {
		make_statement_droppable( $(this) );
	});
	
	// fire updates on input
	$( ".synth-summary" ).find(".synth-summary-text").on("input", queue_summary_update);
	
	// bind buttons
	$( '.synth-new' ).on( "click", new_summary);
	$( '.synth-delete' ).on( "click", delete_summary);
});