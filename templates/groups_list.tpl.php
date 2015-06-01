<h2>Groups</h2>

<div class="form-group">
	<button class="btn btn-primary" onclick="edit_group('<?php print(htmlentities(NEW_ENTRY_ID)); ?>')">
		<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> New Group
	</button>
</div>

<table class="table table-striped">
<tr>
  <th>Name</th>
  <th>Actions</th>
</tr>
<?php foreach ($groups as $id => $data) { ?>
  <tr>
	<td><?php print(htmlentities($data->Name)); ?></td>
	<td>
		<button class="btn btn-default" onclick="edit_group('<?php print(htmlentities($id)); ?>')">
			<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit
		</button>
		<button class="btn btn-danger" onclick="delete_group('<?php print(htmlentities($id)); ?>', '<?php print(htmlentities($data->Name)); ?>')">
			<span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Delete
		</button>
	</td>
  </tr>
<?php } ?>
</table>

<script type="text/javascript">
function edit_group(group_id) {
	document.location = '<?php print(htmlentities($page_url)); ?>?action=edit&id=' + group_id;
}
function delete_group(group_id, group_name) {
	bootbox.dialog({
		message: 'Do you want to delete group "' + group_name + '"',
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
					document.location = '<?php print(htmlentities($page_url)); ?>?action=delete&id=' + group_id;
				}
			}
		}
	});
}
</script>