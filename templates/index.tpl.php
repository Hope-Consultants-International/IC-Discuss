<h2><?php print(htmlentities($title)); ?></h2>

<?php if (count($issues) > 0) { ?>
<form method="post" accept-charset="UTF-8">
	<?php
	if (count($issues) == 1) {
		$value = reset($issues);
		$key = key($issues);
	?>
		<input type="hidden" name="IssueId" id="IssueId" value="<?php print($key); ?>">
		<h3><?php print(htmlentities($value)); ?></h3>
	<?php } else { ?>
		<div class="form-group">
			<label for="IssueId">Select Issue</label>
			<select class="form-control" name="IssueId" id="IssueId">
				<?php foreach ($issues as $key => $value) { ?>
					<option value="<?php print($key); ?>" <?php if($key == $last_issue) { print('selected'); } ?>>
						<?php print(htmlentities($value)); ?>
					</option>
				<?php } ?>
			</select>
		</div>
	<?php } ?>
	<div class="form-group">
		<label for="Statement">Your Statement</label>
		<textarea class="form-control" rows="3" id="Statement" name="Statement" placeholder="Please enter your statement." required maxlength="250"></textarea>
	</div>
	<div class="form-group">
		<div class="btn-group">
			<button type="submit" class="btn btn-primary">Submit</button>
			<button type="reset" class="btn btn-default">Reset</button>
		</div>
    </div>
</form>
<script>
$( '#Statement' ).keyup(function() {
	$(this).val($(this).val().replace(/[\n\v]+/g, ' '));
	$(this).val($(this).val().replace(/\s{2,}/g, ' '));
});
</script>
<?php } else { ?>
<p>
	We currently don't accept statements.
</p>
<?php } ?>