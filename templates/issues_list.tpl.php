<h2>Issues</h2>

<div class="form-group">
	<button class="btn btn-primary" onclick="edit_issue('<?php print(htmlentities(NEW_ENTRY_ID)); ?>')">
		<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> New Issue
	</button>
</div>

<table class="table table-striped">
<tr>
  <th>Name</th>
  <th>Description</th>
  <th>XLS Upload</th>
  <th>Frontpage</th>
  <th class="col-xs-3">Actions</th>
</tr>
<?php foreach ($issues as $id => $data) { ?>
  <tr>
	<td><?php print(htmlentities($data->Title)); ?></td>
	<td><?php print(htmlentities($data->Description)); ?></td>
	<td>
		<?php if ($data->AllowUpload) { ?>
			<span class="glyphicon glyphicon-ok" aria-label="Yes"></span>
		<?php } else { ?>
			<span class="glyphicon glyphicon-remove" aria-label="No"></span>
		<?php } ?>
	</td>
	<td>
		<?php if ($data->Frontpage) { ?>
			<span class="glyphicon glyphicon-ok" aria-label="Yes"></span>
		<?php } else { ?>
			<span class="glyphicon glyphicon-remove" aria-label="No"></span>
		<?php } ?>
	</td>
	<td>
		<div class="btn-group-vertical">
			<button class="btn btn-default" onclick="edit_issue('<?php print(htmlentities($id)); ?>')">
				<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit
			</button>
			<button class="btn btn-default" onclick="download_issue('<?php print(htmlentities($id)); ?>')">
				<span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Download XLS Templates
			</button>
			<button class="btn btn-warning" onclick="clear_issue('<?php print(htmlentities($id)); ?>', '<?php print(Utils::javascriptString($data->Title)); ?>')">
				<span class="glyphicon glyphicon-fire" aria-hidden="true"></span> Clear Statements (<?php print(htmlentities($data->StatementCount)); ?>)
			</button>
			<button class="btn btn-danger" onclick="delete_issue('<?php print(htmlentities($id)); ?>', '<?php print(Utils::javascriptString($data->Title)); ?>')">
				<span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Delete
			</button>
		</div>
	</td>
  </tr>
<?php } ?>
</table>

<script type="text/javascript">
function download_issue(issue_id) {
	document.location = '<?php print(htmlentities($download_url)); ?>?issue=' + issue_id;
}
function edit_issue(issue_id) {
	document.location = '<?php print(htmlentities($page_url)); ?>?action=edit&id=' + issue_id;
}
function clear_issue(issue_id, issue_title) {
	bootbox.dialog({
		message: 'Do you want remove all statements about "' + issue_title + '"',
		title: '<?php print(htmlentities(APP_TITLE)); ?>',
		buttons: {
			cancel: {
				label: "Cancel",
				className: 'btn-default'
			},
			delete: {
				label: 'Clear Statements',
				className: 'btn-danger',
				callback: function() {
					document.location = '<?php print(htmlentities($page_url)); ?>?action=clear-statements&id=' + issue_id;
				}
			}
		}
	});
}
function delete_issue(issue_id, issue_title) {
	bootbox.dialog({
		message: 'Do you want to delete issue "' + issue_title + '"',
		title: '<?php print(htmlentities(APP_TITLE)); ?>',
		buttons: {
			cancel: {
				label: "Don't Delete",
				className: 'btn-default'
			},
			delete: {
				label: 'Delete',
				className: 'btn-danger',
				callback: function() {
					document.location = '<?php print(htmlentities($page_url)); ?>?action=delete&id=' + issue_id;
				}
			}
		}
	});
}
</script>
