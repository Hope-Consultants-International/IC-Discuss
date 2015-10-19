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
	<div class="col-sm-10">
		<div class="form-group">
			<label for="Weight" class="col-sm-2 control-label">Weight</label>
			<div class="col-sm-2">
				<input type="text" class="form-control" id="Weight" name="Weight" placeholder="Weight" value="<?php print(htmlentities($weight)); ?>">
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
$( 'input[name="Weight"]' ).TouchSpin({
	min: 0,
	max: 1000,
	step: 1,
	decimals: 0
});
</script>
