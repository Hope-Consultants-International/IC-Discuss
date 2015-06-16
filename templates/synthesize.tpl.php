<h2 style="overflow:hidden; white-space: nowrap;">Synthesize "<?php print(htmlentities($issue_title)); ?>"</h2>

<?php function emitStatement($statement, $display_highlight) { ?>
	<div class="synth-statement" id="statement-<?php print($statement->StatementId); ?>">
		<div class="synth-statement-highlight <?php if ($statement->Highlight) { print("highlighted"); } ?>" <?php if (!$display_highlight) {print('style="display:none;"'); } ?>>
			<span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>
		</div>
		<div class="statement-text"><?php print(htmlentities($statement->Statement)); ?></div>
		<small><em><?php print(htmlentities($statement->GroupName)); ?></em></small><br>
	</div>
<?php } ?>

</div>
<div class="row row-same-height row-full-height" id="synth-main">
	<div class="col-xs-6 col-xs-height col-full-height synth-box" id="synth-summaries">
		<h3 class="position:sticky">Summaries</h3>
		<?php foreach ($summaries as $summary) { ?>
		<div class="synth-summary" id="summary-<?php print($summary->SummaryId); ?>">
			<button class="btn btn-danger synth-summary-delete" title="Remove Summary">
				<span class="glyphicon glyphicon-trash" aria-hidden="true" onclick=""></span>
			</button>
			<button class="btn btn-default synth-summary-collapse" title="Collapse Statements">
				<span class="glyphicon glyphicon-triangle-top" aria-hidden="true"></span>
			</button>
			<button class="btn btn-default synth-summary-expand" title="Expand Statements">
				<span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
			</button>
			<textarea class="synth-summary-text"><?php print(htmlentities($summary->Summary)); ?></textarea>
			<div class="synth-summary-statements">
				<?php foreach ($summary->statements as $statement) { emitStatement($statement, true); } ?>
			</div>
		</div>
		<?php } ?>
		<div class="synth-placeholder">
			<p>Drop Here to Add new Summary</p>
		</div>
	</div>
	<div class="col-xs-6 col-xs-height col-full-height synth-box" id="synth-statements">
		<h3 class="position:sticky">Statements</h3>
		<?php foreach ($statements as $statement) { emitStatement($statement, false); } ?>
	  </div>
	</div>
</div>

<script>
<?php print($script); ?>
</script>
<script type="text/javascript" src="js/synthesize.js?v=<?php print(RESOURCE_VERSION); ?>"></script>

<?php if (DEBUG) { ?>
<div class="hidden-print">
	<h2>Debug</h2>
	<pre>$summaries = <?php print_r($summaries); ?></pre>
	<pre>$statements = <?php print_r($statements); ?></pre>
</div>
<?php } ?>