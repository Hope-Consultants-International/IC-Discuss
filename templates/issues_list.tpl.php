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
  <th>Actions</th>
</tr>
<?php foreach ($issues as $id => $data) { ?>
  <tr>
	<td><?php print(htmlentities($data->Title)); ?></td>
	<td><?php print(htmlentities($data->Description)); ?></td>
	<td>
		<button class="btn btn-default" onclick="edit_issue('<?php print(htmlentities($id)); ?>')">
			<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit
		</button>
		<button class="btn btn-danger" onclick="delete_issue('<?php print(htmlentities($id)); ?>', '<?php print(htmlentities($data->Title)); ?>')">
			<span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Delete
		</button>
	</td>
  </tr>
<?php } ?>
</table>

<script type="text/javascript">
function edit_issue(issue_id) {
	document.location = "<?php print(htmlentities($page_url)); ?>?action=edit&id=" + issue_id;
}
function delete_issue(issue_id, issue_title) {
	bootbox.dialog({
		message: "Do you want to delete issue \"" + issue_title + "\"",
		title: "<?php print(htmlentities(APP_TITLE)); ?>",
		buttons: {
			cancel: {
				label: "Don't Delete",
				className: "btn-default",			
			},
			delete: {
				label: "Delete",
				className: "btn-danger",
				callback: function() {
					document.location = "<?php print(htmlentities($page_url)); ?>?action=delete&id=" + issue_id;
				}
			}
		}
	});
}
</script>