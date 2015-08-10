// timeout for input submit
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
		closeButton: false,
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

function reset_highlight(highlight) {
	var is_highlighted = highlight.hasClass('highlighted');
	if (is_highlighted) {
		statement_highlight.call(highlight);
	}
}

function statement_highlight() {
    var highlight = $(this);
    var statement = highlight.closest('.synth-statement');
    var statement_id = get_db_id(statement);

    var was_highlighted = highlight.hasClass('highlighted');
    highlight.toggleClass('highlighted');
    console.debug('Set Highlight of Statement ' + statement_id + ' from ' + (was_highlighted ? 'true' : 'false') + ' to ' + (!was_highlighted ? 'true' : 'false'));
	var data = {
		action: 'highlight_statement',
		statement: statement_id,
		highlight: !was_highlighted
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

function duplicate_statement_click() {
	var statement = $( this ).closest('.synth-statement');
	var statement_id = get_db_id(statement);
	
	bootbox.dialog({
		message: 'Are you sure you want to duplicate this statement?',
		title: '<?php print(htmlentities(APP_TITLE)); ?>',
		buttons: {
			cancel: {
				label: "Don't Duplicate",
				className: 'btn-default'
			},
			delete: {
				label: 'Duplicate',
				className: 'btn-warning',
				callback: function() {
					console.debug('Duplicate Statement ' + statement_id);	
					var data = {
						action: 'duplicate_statement',
						statement: statement_id
					};
					var jqxhr = $.ajax({
						type: 'POST',
						url: ajaxHandlerURL,
						data: data,
						dataType: 'json',
						error: function(jqXHR_obj, message, error) {
							console.error('Could not duplicate: ' + message);
						},
						success: function(reply, message) {
							if (reply['success']) {
								console.info('Update success: ' + reply['message']);
								duplicate_statement(statement_id, reply['statement_id']);				
							} else {
								console.error('Update failure: ' + reply['message']);
								reload_screen();
							}
						}
					});
				}
			}
		}
	})
}

function duplicate_statement(source_id, target_id) {
	var source_statement = $( '#statement-' + source_id );
	
	source_statement.clone()
		.attr('id', 'statement-' + target_id )
		.insertAfter( source_statement );
	
	var target_statement = $( '#statement-' + target_id );
	make_statement_draggable( target_statement );
	target_statement.find('.synth-statement-duplicate').on('click', duplicate_statement_click);
	
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
	var summary_text_obj = $( '#summary-' + summary_id ).find('.synth-summary-text');
	var summary_text = summary_text_obj.val();
	var stored_text = summary_text_obj.attr('data-stored-text');
	
	var data = {
		action: 'update_summary',
		summary: summary_id,
		summary_text: summary_text,
		summary_previous: stored_text
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
				summary_text_obj.attr('data-stored-text', summary_text);
			} else {
				console.error('Update failure: ' + reply['message']);
				reload_screen();
			}
		}
	});
}

function summary_collapse_all() {
	$( '.synth-summary' ).each(function() {
		summary_collapse( $(this) );
	});
}
function summary_expand_all() {
	$( '.synth-summary' ).each(function() {
		summary_expand( $(this) );
	});
}

// handles clicks on the collapse button
function summary_collapse_click() {
	var summary = $( this ).closest('.synth-summary');
	summary_collapse(summary);
}

// collapses a summary
function summary_collapse(summary) {
	summary.find( '.synth-summary-statements' ).slideUp(
		animate_normal,
		function(){
			summary.find( '.synth-summary-statements' ).hide();
			summary.find( '.synth-summary-collapse').fadeOut(animate_fast);
			summary.find( '.synth-summary-expand').fadeIn(animate_fast);
		}
	);
}

// handles clicks on the expand button
function summary_expand_click() {
	var summary = $( this ).closest('.synth-summary');
}

// expand a summary
function summary_expand(summary) {
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
				init_summary(summary);
				
				statement.appendTo(summary.find( '.synth-summary-statements' ));
				statement.find( '.synth-statement-highlight' ).slideDown(animate_fast);
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
		$( this ).slideUp(animate_fast, function() {
			var statement = $( this );
			statement.appendTo($( '#synth-statements'));
			statement.slideDown(animate_fast);
			statement.find( '.synth-statement-highlight' ).slideUp(animate_fast);
			make_statement_draggable(statement);
			var highlight = statement.find( '.synth-statement-highlight' );
			highlight.on('click', statement_highlight);
			reset_highlight(highlight);
		});
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
		accept: '.synth-statement',
		drop: function( event, ui ) {
			// add object
			ui.draggable.appendTo( $( this ).find('.synth-summary-statements') );
			// show highlighting
			ui.draggable.find( '.synth-statement-highlight' ).slideDown(animate_fast);
			// hide duplicate
			ui.draggable.find( '.synth-statement-duplicate' ).slideUp(animate_fast);
			
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
		accept: '.synth-statement',
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
		accept: '.synth-statement',
		drop: function( event, ui ) {
			// add object
			ui.draggable.appendTo( this );
			// hide highlighting
			var highlight = ui.draggable.find( '.synth-statement-highlight' );
			highlight.slideUp(animate_fast);
			reset_highlight(highlight);
			// show duplicate
			ui.draggable.find( '.synth-statement-duplicate' ).slideDown(animate_fast);
			
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
		//containment: '#synth-main',
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

function init_summary(summary) {
	make_summary_droppable(summary);
	summary.find('.synth-summary-delete').on( 'click', delete_summary);
	summary.find('.synth-summary-collapse').on('click', summary_collapse_click);
	summary.find('.synth-summary-expand').on('click', summary_expand_click);
	summary.find('.synth-summary-expand').css('display', 'none');
	
	var summary_text = summary.find('.synth-summary-text');
	summary_text.on('input', queue_summary_update);
	summary_text.attr('data-stored-text', summary_text.val());
}

$(function() {
	// prepare draggable objects
	$( '.synth-statement' ).each( function( index ) {
		make_statement_draggable( $(this) );
	});
	
	// prepare drop areas
    $( '.synth-summary' ).each( function( index ) {
		init_summary( $(this) );
	});
	$( '#synth-statements' ).each( function( index ) {
		make_statement_droppable( $(this) );
	});
	$( '.synth-placeholder' ).each( function( index ) {
		make_placeholder_droppable( $(this) );
	});
	
	// bind buttons
	$( '.synth-new' ).on( 'click', new_summary);
	$( '.synth-statement-highlight' ).on('click', statement_highlight);
	$( '.synth-statement-duplicate' ).on('click', duplicate_statement_click);
	
	$('#synth-summary-collapse-all').on('click', summary_collapse_all);
	$('#synth-summary-expand-all').on('click', summary_expand_all);
});
