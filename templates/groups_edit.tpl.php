<h2><?php print(htmlentities($title)); ?></h2>

<form class="form-horizontal" method="post" accept-charset="UTF-8">
	<input type="hidden" name="id" value="<?php print(htmlentities($group_id)); ?>">
	<input type="hidden" name="action" value="save">
	<div class="col-sm-10">  
		<div class="form-group">
			<label for="GroupName" class="col-sm-2 control-label">Name</label>
			<div class="col-sm-4">
				<input type="text" class="form-control" id="GroupName" name="GroupName" placeholder="Group Name" required="required" value="<?php print(htmlentities($group_name)); ?>">
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
	document.location = '<?php print(htmlentities($page_url)); ?>?action=list';
}
</script>