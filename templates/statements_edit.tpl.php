<h2><?php print(htmlentities($title)); ?></h2>

<form class="form-horizontal" method="post" accept-charset="UTF-8">
	<input type="hidden" name="id" value="<?php print(htmlentities($statement_id)); ?>">
	<input type="hidden" name="action" value="save">
	<div class="col-sm-10">  
		<div class="form-group">
			<label for="IssueId" class="col-sm-2 control-label">Issue</label>
			<div class="col-sm-6">
				<select class="form-control" id="IssueId" name="IssueId">
				<?php foreach($issues as $id => $issue) { ?>
					<option <?php print(($issue_id == $id)?'selected="selected"':'');?> value="<?php print(htmlentities($id)); ?>">
						<?php print(htmlentities($issue)); ?>
					</option>
				<?php } ?>
				</select>
			</div>
		</div>
	</div>
	<div class="col-sm-10">  
		<div class="form-group">
			<label for="IssueId" class="col-sm-2 control-label">Group</label>
			<div class="col-sm-6">
				<select class="form-control" id="GroupId" name="GroupId">
				<?php foreach($groups as $id => $group) { ?>
					<option <?php print(($group_id == $id)?'selected="selected"':'');?> value="<?php print(htmlentities($id)); ?>">
						<?php print(htmlentities($group)); ?>
					</option>
				<?php } ?>
				</select>
			</div>
		</div>
	</div>
	<div class="col-sm-10">
		<div class="form-group">
			<label for="Statement" class="col-sm-2 control-label">Statement</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" id="Statement" name="Statement" placeholder="Statement" value="<?php print(htmlentities($statement)); ?>">
			</div>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-10">
			<div class="btn-group" role="group">
				<button type="submit" class="btn btn-primary">
					<span class="glyphicon glyphicon-save" aria-hidden="true"></span> Save
				</button>
				<button type="button" class="btn btn-default" onclick="edit_cancel()">
					<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Cancel
				</button>
			</div>
		</div>
	</div>
</form>

<script type="text/javascript">
function edit_cancel() {
	document.location = "<?php print(htmlentities($page_url)); ?>?action=list";
}
</script>