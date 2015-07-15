<h2><?php print(htmlentities($title)); ?></h2>

<form method="post" accept-charset="UTF-8" class="form-horizontal" id="frontpage-statement" style="display:none">
	<h3 id="frontpage-issue-title"></h3>
	<div class="form-group" id="frontpage-issue-select">
		<div class="col-md-6">
			<label for="issue_id">Select Issue</label>
			<select class="form-control col-md-6" name="issue_id" id="issue_id">
			</select>
		</div>
	</div>
	<div class="form-group">
		<div class="col-md-6">
			<label for="statement">Your Statement</label>
			<textarea class="form-control" rows="3" id="statement" name="statement" placeholder="Please enter your statement." required maxlength="250"></textarea>
		</div>
	</div>
    <input type="hidden" name="action" value="add_statement">
	<div class="form-group">
		<div class="col-md-6">
			<div class="btn-group">
				<button type="submit" class="btn btn-primary">Submit</button>
				<button type="reset" class="btn btn-default">Reset</button>
			</div>
		</div>
    </div>
</form>

<div id="frontpage-no-issues" style="display:none">
	<p>
		We currently don't accept statements.
	</p>
</div>

<script>
<?php print($script); ?>
</script>
<script type="text/javascript" src="js/index.js?v=<?php print(RESOURCE_VERSION); ?>"></script>
