<h2 style="overflow:hidden; white-space: nowrap;">Synthesize "<?php print(htmlentities($issue_title)); ?>"</h2>

<?php function emitStatement($statement, $display_buttons = true) { ?>
	<div class="synth-statement" id="statement-<?php print($statement->StatementId); ?>">
		<span class="statement-text"><?php print(htmlentities($statement->Statement)); ?></span><br>
		<small><em><?php print(htmlentities($statement->GroupName)); ?></em></small><br>
		<!-- 
		<div class="btn-group" role="group"<?php if (!$display_buttons) { print(' style="display:none"'); } ?>>
			<button class="btn btn-default synth-new">
				<span class="glyphicon glyphicon-duplicate" aria-hidden="true"></span> New Summary
			</button>
		</div>
		-->
	</div>
<?php } ?>

</div>
<div class="row row-same-height row-full-height" id="synth-main">
	<div class="col-xs-6 col-xs-height col-full-height synth-box" id="synth-summaries">
		<h3 class="position:sticky">Summaries</h3>
		<?php foreach ($summaries as $summary) { ?>
		<div class="synth-summary" id="summary-<?php print($summary->SummaryId); ?>">
			<button class="btn btn-danger synth-delete" title="Remove Summary">
				<span class="glyphicon glyphicon-trash" aria-hidden="true" onclick=""></span>
			</button>
			<textarea class="synth-summary-text"><?php print(htmlentities($summary->Summary)); ?></textarea>
			<?php foreach ($summary->statements as $statement) { emitStatement($statement, false); } ?>
		</div>
		<?php } ?>
		<div class="synth-placeholder">
			<p>Drop Here to Add new Summary</p>
		</div>
	</div>
	<div class="col-xs-6 col-xs-height col-full-height synth-box" id="synth-statements">
		<h3 class="position:sticky">Statements</h3>
		<?php foreach ($statements as $statement) { emitStatement($statement); } ?>
	  </div>
	</div>
</div>

<script>
  <?php print($script); ?>
</script>

<?php if (DEBUG) { ?>
<div class="hidden-print">
	<h2>Debug</h2>
	<pre>$summaries = <?php print_r($summaries); ?></pre>
	<pre>$statements = <?php print_r($statements); ?></pre>
</div>
<?php } ?>