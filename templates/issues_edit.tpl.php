<?php
/**
IC-Discuss (c) 2015 by Hope Consultants International Ltd.

IC-Discuss is licensed under a
Creative Commons Attribution-ShareAlike 4.0 International License.

You should have received a copy of the license along with this
work.  If not, see <http://creativecommons.org/licenses/by-sa/4.0/>.
**/
?>
<h2><?php print(htmlentities($title)); ?></h2>

<form class="form-horizontal" method="post" accept-charset="UTF-8">
	<input type="hidden" name="id" value="<?php print(htmlentities($issue_id)); ?>">
	<input type="hidden" name="action" value="save">
	<div class="col-sm-10">  
		<div class="form-group">
			<label for="IssueTitle" class="col-sm-2 control-label">Name</label>
			<div class="col-sm-4">
				<input type="text" class="form-control" id="IssueTitle" name="IssueTitle" placeholder="Issue Title" required="required" value="<?php print(htmlentities($issue_title)); ?>">
			</div>
		</div>
	</div>
	<div class="col-sm-10">
		<div class="form-group">
			<label for="IssueDescription" class="col-sm-2 control-label">Description</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" id="IssueDescription" name="IssueDescription" placeholder="Issue Description" value="<?php print(htmlentities($issue_description)); ?>">
			</div>
		</div>
	</div>
	<div class="col-sm-10">
		<div class="form-group">
			<label for="Folder" class="col-sm-2 control-label">Folder</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" id="Folder" name="Folder" placeholder="Folder" value="<?php print(htmlentities($issue_folder)); ?>">
				<p class="help-block">When downloading XLS templates in ZIP files, the issue will be placed in this folder.</p>
			</div>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-10 offset">
			<div class="checkbox">
				<label>
					<input type="checkbox" name="AllowUpload" value="1" <?php if($issue_upload) { print('checked'); } ?>>
					Allow XLS uploads
				</label>
			</div>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-10 offset">
			<div class="checkbox">
				<label>
					<input type="checkbox" name="Frontpage" value="1" <?php if($issue_frontpage) { print('checked'); } ?>>
					On Frontpage
				</label>
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
