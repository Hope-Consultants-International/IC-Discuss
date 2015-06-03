const debug = <?php print( ($debug) ? 'true' : 'false' ); ?>;
const ajaxHandlerURL = '<?php print(htmlentities($handler_url)); ?>';
const input_timer = 750;
// animation speed
const animate_fast = 100;
const animate_normal = 200;
const animate_slow = 400;

function get_db_id(element) {
	return element.attr('id').split('-')[1];
}

function reload_screen() {
	bootbox.dialog({
		title: "Data Inconsistency",
		message: "<p>Data on server is not consistent with your working data.</p><p>This page should be reloaded</p>",
		buttons: {
			cancel: {
				label: "Cancel",
				className: "btn-default"
			},
			reload: {
				label: "Reload",
				className: "btn-primary",
				callback: function() {
					location.reload(true);
				}
			}
		}
	});
}

// we want to send updates as soon as they are entered,
// so we set a timeout while input is received and 
// execute the update when there is no input on the summary for 1s
var summary_timers = [];
function queue_summary_update() {
	var summary = $( this ).closest('.synth-summary');
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
	console.debug('Update Summary ' + summary_id);
	var summary = $( '#summary-' + summary_id );
	var summary_text = summary.find('.synth-summary-text').val();
	
	var data = {
		action: 'update_summary',
		summary: summary_id,
		summary_text: summary_text
	};
	var jqxhr = $.ajax({
		type: 'POST',
		url: ajaxHandlerURL,
		data: data,
		dataType: 'json',
		error: function(jqXHR_obj, message, error) {
			console.error('Could not send update: ' + message);
		},
		success: function(reply, message) {
			if (reply['success']) {
				console.info('Update success: ' + reply['message']);
			} else {
				console.error('Update failure: ' + reply['message']);
				reload_screen();
			}
		}
	});
}

function summary_collapse() {
	var summary = $( this ).closest('.synth-summary');
	summary.find( '.synth-summary-statements' ).slideUp(
		animate_normal,
		function(){
			summary.find( '.synth-summary-statements' ).hide();
			summary.find( '.synth-summary-collapse').fadeOut(animate_fast);
			summary.find( '.synth-summary-expand').fadeIn(animate_fast);
		}
	);
}
function summary_expand() {
	var summary = $( this ).closest('.synth-summary');
	summary.find( '.synth-summary-statements' ).slideDown(
		animate_normal,
		function(){
			summary.find( '.synth-summary-collapse').fadeIn(animate_fast);
			summary.find( '.synth-summary-expand').fadeOut(animate_fast);
		}
	);
}

function add_new_summary(statement) {
	var statement_id = get_db_id(statement);
	var statement_text = statement.find('.statement-text').text();
	var data = {
		action: 'new_summary',
		statement: statement_id
	};
	var jqxhr = $.ajax({
		type: 'POST',
		url: ajaxHandlerURL,
		data: data,
		dataType: 'json',
		error: function(jqXHR_obj, message, error) {
			console.error('Could not send update: ' + message);
		},
		success: function(reply, message) {
			if (reply['success']) {
				console.info('Update success: ' + reply['message']);
				var summary_id = reply['summary_id'];
	
				$('<div class="synth-summary" id="summary-' + summary_id + '"></div>').insertBefore('.synth-placeholder');
				var summary = $( '#summary-' + summary_id );
				summary.append('<button class="btn btn-danger synth-summary-delete" title="Remove Summary"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>');
				summary.append('<button class="btn btn-default synth-summary-collapse" title="Collapse Statements"><span class="glyphicon glyphicon-triangle-top" aria-hidden="true"></span></button>');
				summary.append('<button class="btn btn-default synth-summary-expand" title="Expand Statements"><span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span></button>');
				summary.append('<textarea class="synth-summary-text">' + statement_text + '</textarea>');
				summary.append('<div class="synth-summary-statements" />');
				
				
				// since this is a new object, we have to make it droppable
				// and bind event handlers
				make_summary_droppable(summary);
				summary.find('.synth-summary-delete').on( 'click', delete_summary);
				summary.find('.synth-summary-text').on('input', queue_summary_update);
				summary.find('.synth-summary-collapse').on('click', summary_collapse);
				summary.find('.synth-summary-expand').on('click', summary_expand);
				summary.find('.synth-summary-expand').css('display', 'none');
				
				statement.appendTo(summary.find( '.synth-summary-statements' ));
				statement.find('.btn-group').fadeOut(animate_fast);
			} else {
				console.error('Update failure: ' + reply['message']);
				reload_screen();
			}
		}
	});
}

function new_summary() {
	var statement = $( this ).closest('.synth-statement');	
	var statement_id = get_db_id(statement);
	console.debug('New Summary from Statement ' + statement_id);
	add_new_summary(statement);
}

function delete_summary() {
	var summary = $( this ).closest('.synth-summary');
	var summary_id = get_db_id(summary);
	cancel_summary_update(summary_id);
		
	summary.find('.synth-statement').each(function( index ) {
		$( this ).appendTo($( '#synth-statements' ))
		$( this ).find( '.btn-group').fadeIn(animate_fast);
	});
	summary.remove();

	console.debug('Delete Summary ' + summary_id);
	
	var data = {
		action: 'delete_summary',
		summary: summary_id
	};
	var jqxhr = $.ajax({
		type: 'POST',
		url: ajaxHandlerURL,
		data: data,
		dataType: 'json',
		error: function(jqXHR_obj, message, error) {
			console.error('Could not send update: ' + message);
		},
		success: function(reply, message) {
			if (reply['success']) {
				console.info('Update success: ' + reply['message']);
			} else {
				console.error('Update failure: ' + reply['message']);
				reload_screen();
			}
		}
	});
}

function make_summary_droppable(summary) {
	summary.droppable({
		activeClass: 'ui-state-default',
		hoverClass: 'ui-state-hover',
		greedy: true,
		drop: function( event, ui ) {
			// add object
			ui.draggable.appendTo( $( this ).find('.synth-summary-statements') );
			// remove buttons
			ui.draggable.find( '.btn-group').fadeOut(animate_fast);
			
			// do the data update in the background
			var statement_id = get_db_id(ui.draggable);
			var summary_id = get_db_id($(event.target));
			var old_summary_id = ui.draggable.attr('data-old-summary-id');
			console.debug('Link Statement ' + statement_id + ' to Summary ' + summary_id + ' was linked to ' + old_summary_id);
			
			var data = {
				action: 'link',
				statement: statement_id,
				summary: summary_id,
				summary_old: ui.draggable.attr('data-old-summary-id')
			};
			var jqxhr = $.ajax({
				type: 'POST',
				url: ajaxHandlerURL,
				data: data,
				dataType: 'json',
				error: function(jqXHR_obj, message, error) {
					console.error('Could not send update: ' + message);
				},
				success: function(reply, message) {
					if (reply['success']) {
						console.info('Update success: ' + reply['message']);
					} else {
						console.error('Update failure: ' + reply['message']);
						reload_screen();
					}
				}
			});
		}
	});
}

function make_placeholder_droppable(placeholder) {
	placeholder.droppable({
		activeClass: 'ui-state-default',
		hoverClass: 'ui-state-hover',
		drop: function( event, ui ) {
			// add the new summary
			add_new_summary(ui.draggable);
		}
	});
}

function make_statement_droppable(statement) {
	statement.droppable({
		activeClass: 'ui-state-default',
		hoverClass: 'ui-state-hover',
		drop: function( event, ui ) {
			// add object
			ui.draggable.appendTo( this );
			// remove buttons
			ui.draggable.find('.btn-group').fadeIn(animate_fast);
			
			// do the data update in the background
			var statement_id = get_db_id(ui.draggable);
			var old_summary_id = ui.draggable.attr('data-old-summary-id');
			console.debug('Unlink Statement ' + statement_id + ' was linked to ' + old_summary_id);
			
			var data = {
				action: 'unlink',
				statement: statement_id,
				summary_old: old_summary_id
			};
			var jqxhr = $.ajax({
				type: 'POST',
				url: ajaxHandlerURL,
				data: data,
				dataType: 'json',
				error: function(jqXHR_obj, message, error) {
					console.error('Could not send update: ' + message);
				},
				success: function(reply, message) {
					if (reply['success']) {
						console.info('Update success: ' + reply['message']);
					} else {
						console.error('Update failure: ' + reply['message']);
						reload_screen();
					}
				}
			});
		}
	});
}

function make_statement_draggable(statement) {
	statement.draggable({
		appendTo: 'body',
		helper: 'clone',
		containment: '#synth-main',
		revert: 'invalid',
		start: function( event, ui ) {
			var old_summary_id = null;
			if ($(event.target).closest('#synth-statements').length) {
				old_summary_id = null;
				console.debug('Old Summary ID: null');
			} else if($(event.target).closest('.synth-summary').length) {
				var summary = $(event.target).closest('.synth-summary');
				old_summary_id = get_db_id(summary);
				console.debug('Old Summary ID: ' + old_summary_id);
			}
			$(event.target).attr('data-old-summary-id', old_summary_id);
			
			// remove old object
			$( event.target ).slideUp(animate_normal);

			// make sure the draggable object doesn't resize
			ui.helper.css('width', $( '#' + event.target.id ).css('width'));
			ui.helper.css('cursor', 'grabbing');
		},
		stop: function( event, ui ) {
			$( event.target ).slideDown(animate_normal);
		}
    });
}

$(function() {
	// prepare draggable objects
	$( '.synth-statement' ).each( function( index ) {
		make_statement_draggable( $(this) );
	});
	
	// prepare drop areas
    $( '.synth-summary' ).each( function( index ) {
		make_summary_droppable( $(this) );
	});
	$( '#synth-statements' ).each( function( index ) {
		make_statement_droppable( $(this) );
	});
	$( '.synth-placeholder' ).each( function( index ) {
		make_placeholder_droppable( $(this) );
	});
	
	// fire updates on input
	$( '.synth-summary' ).find('.synth-summary-text').on('input', queue_summary_update);
	
	// bind buttons
	$( '.synth-new' ).on( 'click', new_summary);
	$( '.synth-summary-delete' ).on( 'click', delete_summary);
	$( '.synth-summary-collapse' ).on('click', summary_collapse);
	$( '.synth-summary-expand' ).on('click', summary_expand);
	$( '.synth-summary-expand' ).css('display', 'none');
});