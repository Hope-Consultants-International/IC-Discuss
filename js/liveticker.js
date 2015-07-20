// timeout for input submit
const poll_interval = 2000;

// animation speed
const animate_fast = 100;
const animate_normal = 200;
const animate_slow = 400;

var max_statement_id = 0;
var paused = false;
var issues = {};

var poll_timer = null;
function poll_issue() {
	if (paused) {
		return;
	}
	console.debug('poll issue ' + issue_id);

	var data = {
		action: 'get_statements',
		issue: issue_id,
		statement: max_statement_id
	};
	var jqxhr = $.ajax({
		type: 'POST',
		url: ajaxHandlerURL,
		data: data,
		dataType: 'json',
		error: function(jqXHR_obj, message, error) {
			console.error('Could not poll new statements: ' + message);
		},
		success: function(reply, message) {
			if (reply['success']) {
				console.info('Update success: ' + reply['message']);
				var first_update = (max_statement_id == 0);

				if (first_update) {
					// clear out div
					var scroller = $( '#ticker-scroller' );
					scroller.slideUp(animate_normal);
					scroller.children().each(function( index ) {
						$( this ).remove();
					});
					scroller.slideDown(animate_fast);
					
					// skip first_update stuff from now
					max_statement_id = -1;
				}

				$.each(reply['data'], function( index, value ) {

					var statement_id = parseInt(index);
					var statement_text = value['Statement'];
					var statement_issue_id = value['IssueId'];

					if (statement_id > max_statement_id) {
						max_statement_id = statement_id;
					}
					console.debug(statement_id.toString() + " - " + statement_text);
					var statement = $("<div class='ticker-statement' id='statement-" + statement_id.toString() +"'></div>").text(statement_text);

					if (issue_id == 0) {
						var issue = $("<br><span class='issue'></span>");
						issue.text(issues[statement_issue_id]);
						statement.append(issue);
					}
					statement.hide().prependTo( '#ticker-scroller' ).fadeIn(animate_slow);
				});
				if (first_update) {
					insert_divider();
				}
			} else {
				console.error('Update failure: ' + reply['message']);
				reload_screen();
			}
		}
	});

	poll_timer = setTimeout(function() { poll_issue(); }, poll_interval);
}

function insert_divider() {
	var first_child = $( '#ticker-scroller' ).children().first();
	if (! first_child.hasClass('ticker-divider')) {
		$('#ticker-scroller').prepend('<hr class="ticker-divider">');
	}
}

function reload_screen() {
	// pause this
	paused = true;
	clearTimeout(poll_timer);
	$('#pauseButton').html('<span class="glyphicon glyphicon-play"></span>');

	// this might be an issue problem
	get_issues();

	bootbox.dialog({
		title: "Error updating data",
		message: "<p>An error occurred trying to update some data.</p><p>For example the Issue might no longer be on the Frontpage.</p><p>You can Reload the page or Cancel to keep the current page frozen</p>",
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

function get_issues() {
	return $.ajax({
		type: 'POST',
		url: ajaxHandlerURL,
		data: {
			action: 'get_issues'
		},
		dataType: 'json',
		error: function(jqXHR_obj, message, error) {
			console.error('Could not get issues: ' + message);
		},
		success: function(reply, message) {
			if (reply['success']) {
				console.info('Retrieved issues: ' + reply['message']);

				issues = reply['data'];

				// update select
				var select = $('#issue_id');
				select.empty();
				select.append('<option value="0">All Frontpage Issues</option>');
				$.each(issues, function( index, value ) {
					$('<option value="' + index + '"></option>').text(value).appendTo(select);
				});

				// check if old issue_id still exists
				if (issue_id in issues) {
					$( '#issue_id' ).val(issue_id);
				} else {
					$( '#issue_id' ).val(0);
					issue_id = 0;
					max_statement_id = 0;
				}
			} else {
				console.error('Get issues failure: ' + reply['message']);
				reload_screen();
			}
		}
	});
}

function update_title() {
	if (issue_id == 0) {
		$( '#text-issue-title' ).text(' - All Frontpage Issues');
	} else {
		$( '#text-issue-title' ).text(' - ' + issues[issue_id]);
	}
}

$( document ).ready(function() {
	$.when(get_issues()).done(function() {
		update_title();
		poll_issue();
	});
	$( '#issue_id' ).change(function() {
			// stop updates
			clearTimeout(poll_timer);

			// set new start
			issue_id = $( '#issue_id' ).val();
			max_statement_id = 0;

			// start polling again
			update_title();
			poll_issue();
	});
	$('#pauseButton').click(function () {
		paused = ! paused;
		if (paused) {
			clearTimeout(poll_timer);
			$(this).html('<span class="glyphicon glyphicon-play"></span>');
		} else {
			// if this was paused due to an error, we need to update the title
			update_title();
			
			// insert divider to make sure new statements stand out
			insert_divider();
			
			// start polling
			poll_issue();

			$(this).html('<span class="glyphicon glyphicon-pause"></span>');
		}
	});
    $( document ).keydown(function(e) {
        var unicode = e.keyCode ? e.keyCode : e.charCode;
        if (unicode == 32) {
            $('#pauseButton').click();
            return false;
        }
    });
});
