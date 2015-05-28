<h2>Statements</h2>

<table class="table table-striped">
<tr>
  <th>Issue</th>
  <th>Group</th>
  <th>Statement</th>
  <th>Actions</th>
</tr>
<?php foreach ($statements as $id => $data) { ?>
  <tr>
	<td><?php print(htmlentities($data->IssueTitle)); ?></td>
	<td><?php print(htmlentities($data->GroupName)); ?></td>
	<td><?php print(htmlentities($data->Statement)); ?></td>
	<td>
		<button class="btn btn-default" onclick="edit_statement('<?php print(htmlentities($id)); ?>')">
			<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit
		</button>
		<button class="btn btn-danger" onclick="delete_statement('<?php print(htmlentities($id)); ?>', '<?php print(htmlentities($data->Statement)); ?>')">
			<span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Delete
		</button>
	</td>
  </tr>
<?php } ?>
</table>
<button class="btn btn-primary" onclick="edit_statement('<?php print(htmlentities(NEW_ENTRY_ID)); ?>')">
	<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> New Statement
</button>

<script type="text/javascript">
function edit_statement(statement_id) {
	document.location = "<?php print(htmlentities($page_url)); ?>?action=edit&id=" + statement_id;
}
function delete_statement(statement_id, statement_title) {
	bootbox.dialog({
		message: "Do you want to delete statement \"" + statement_title + "\"",
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
					document.location = "<?php print(htmlentities($page_url)); ?>?action=delete&id=" + statement_id;
				}
			}
		}
	});
}
</script>