// timeout for input submit
const poll_interval = 2000;

// animation speed
const animate_fast = 100;
const animate_normal = 200;
const animate_slow = 400;

var max_statement_id = 0;

var poll_timer = null;
function poll_issue() {
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
				$.each(reply['data'], function( index, value ) {
					if (parseInt(index) > max_statement_id) {
						max_statement_id = parseInt(index);
					}
					console.debug(index + " - " + value);
					$("<div class='ticker-statement' id='statement-" + index +"'></div>").hide().prependTo("#scroller");
					var statement = $( "#statement-" + index );
					statement.text(value).fadeIn(animate_slow);
				});
			} else {
				console.error('Update failure: ' + reply['message']);
				//reload_screen();
			}
		}
	});
	
	poll_timer = setTimeout(function() { poll_issue(); }, poll_interval);
}

$(function() {
	if (issue_id != 0) {
		poll_issue();
	}
});