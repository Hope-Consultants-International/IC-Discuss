<?php
/**
IC-Discuss (c) 2015 by Hope Consultants International Ltd.

IC-Discuss is licensed under a
Creative Commons Attribution-ShareAlike 4.0 International License.

You should have received a copy of the license along with this
work.  If not, see <http://creativecommons.org/licenses/by-sa/4.0/>.
**/
?>
<h2>Statements</h2>

<div class="form-group">
	<button class="btn btn-primary" onclick="edit_statement('<?php print(htmlentities(NEW_ENTRY_ID)); ?>')">
		<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> New Statement
	</button>
</div>

<table class="table table-striped">
<tr>
  <th>Issue</th>
  <th>Group</th>
  <th>Statement</th>
  <th>Weight</th>
  <th>Duplicates</th>
  <th style="width:20em">Actions</th>
</tr>
<?php foreach ($statements as $id => $data) { ?>
  <tr>
	<td><?php print(htmlentities($data->IssueTitle)); ?></td>
	<td><?php print(htmlentities($data->GroupName)); ?></td>
	<td><?php print(htmlentities($data->Statement)); ?></td>
	<td><?php print(htmlentities($data->Weight)); ?></td>
	<td><?php print(number_format($data->ChildStatements, 0)); ?></td>
	<td style="width:15em">
		<div class="btn-group">
			<button class="btn btn-default" onclick="edit_statement('<?php print(htmlentities($id)); ?>')">
				<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit
			</button>
			<button class="btn btn-danger" onclick="delete_statement('<?php print(htmlentities($id)); ?>', '<?php print(Utils::javascriptString($data->Statement)); ?>')">
				<span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Delete
				<?php if ($data->ChildStatements > 0) { print("One Copy"); } ?>
			</button>
		</div>
	</td>
  </tr>
<?php } ?>
</table>

<script type="text/javascript">
function edit_statement(statement_id) {
	document.location = '<?php print(htmlentities($page_url)); ?>?action=edit&id=' + statement_id;
}
function delete_statement(statement_id, statement_title) {
	bootbox.dialog({
		message: 'Do you want to delete statement "' + statement_title + '"',
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
					document.location = '<?php print(htmlentities($page_url)); ?>?action=delete&id=' + statement_id;
				}
			}
		}
	});
}
</script>
