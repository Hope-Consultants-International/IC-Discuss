// timeout for input submit
const poll_interval = 2000;

// animation speed
const animate_fast = 100;
const animate_normal = 200;
const animate_slow = 400;

// default issue list is empty
var issues = {};

var issue_timer = null;
function poll_issues() {
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
				var old_issues = issues;
				var problem = false;
				issues = reply['data'];

				if (JSON.stringify(old_issues) != JSON.stringify(issues)) {
					if ($( '#issue_id' ).val()) {
						issue_id = $( '#issue_id' ).val();
					}
					
					// update select
					var select = $('#issue_id');
					select.empty();
					$.each(issues, function( index, value ) {
						$('<option value="' + index + '"></option>').text(value).appendTo(select);
					});

					// check if old issue_id still exists
					if (issue_id in issues) {
						$( '#issue_id' ).val(issue_id);
					} else {
						issue_id = Object.keys(issues)[0];
						$( '#issue_id' ).val(issue_id);
						if ($( '#statement' ).val().length > 0) {
							problem = true;
							issue_mismatch();
						}
					}
					
					var issues_count = Object.keys(issues).length;
					
					// hide select if no longer needed
					if (issues_count == 0) {
						$('#frontpage-statement').slideUp(animate_normal, function() {
							$('#frontpage-no-issues').slideDown(animate_normal);
						});
					} else {
						$('#frontpage-no-issues').slideUp(animate_normal);
						$('#frontpage-statement').slideUp(animate_normal, function() {
							if (issues_count == 1) {						
								$('#frontpage-issue-title').text(issues[issue_id]);
								$('#frontpage-issue-title').show();
								$('#frontpage-issue-select').hide();
							} else {
								$('#frontpage-issue-title').text('');
								$('#frontpage-issue-title').hide();
								$('#frontpage-issue-select').show();
							}
							$('#frontpage-statement').slideDown(animate_normal);
						});
					}
				}
				if (!problem) {
					issue_timer = setTimeout(function() { poll_issues(); }, poll_interval);
				}
			} else {				
				console.error('Get issues failure: ' + reply['message']);
				reload_screen();
			}
		}
	});
}

function issue_mismatch() {
	var issues_count = Object.keys(issues).length;
	if (issues_count > 0) {
		var more_issues_text = '';
		if (issues_count > 1) {
			var more_issues_text = ' or select another issue';
		}
		bootbox.dialog({
			title: "Frontpage Issues Changed",
			message: "<p>The Issue you were commenting on is no longer available</p><p>Another issue was selected for you. Please make sure your comment still applies"+more_issues_text+".</p><p>If your comment does not apply anymore, please click Reset</p>",
			closeButton: false,
			buttons: {
				ok: {
					label: "OK",
					className: "btn-primary",
					callback: function() {
						poll_issues();
					}
				},
				reset: {
					label: "Reset",
					className: "btn-default",
					callback: function() {
						$( '#statement' ).val('');
						poll_issues();
					}
				}
			}
		});
	} else {
		$( '#statement' ).val('');
		bootbox.dialog({
			title: "Frontpage Issues Changed",
			message: "<p>The Issue you were commenting on is no longer available for comment, and we do not currently accept comments on other issues.</p>",
			closeButton: false,
			buttons: {
				ok: {
					label: "OK",
					className: "btn-primary",
					callback: function() {
						poll_issues()
					}
				}
			}
		});
	}
};

function reload_screen() {
	bootbox.dialog({
		title: "Can't retrieve data",
		message: "<p>Can't communicate with the server, please reload</p>",
		closeButton: false,
		buttons: {
			cancel: {
				label: "Cancel",
				className: "btn-default",
				callback: function() {
					poll_issues();
				}
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
};

$( document ).ready(function() {
	$( '#statement' ).keyup(function() {
		$(this).val($(this).val().replace(/[\n\v]+/g, ' '));
		$(this).val($(this).val().replace(/\s{2,}/g, ' '));
	});
	poll_issues();
});
