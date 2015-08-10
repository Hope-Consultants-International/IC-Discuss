<h2 style="overflow:hidden; white-space: nowrap;">Synthesize "<?php print(htmlentities($issue_title)); ?>"</h2>

<?php function emitStatement($statement, $for_summary_column) { ?>
	<div class="synth-statement" id="statement-<?php print($statement->StatementId); ?>">
		<div class="synth-statement-highlight <?php if ($statement->Highlight) { print("highlighted"); } ?>" <?php if (!$for_summary_column) {print('style="display:none;"'); } ?>>
			<span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>
		</div>
		<div class="synth-statement-duplicate" <?php if ($for_summary_column) {print('style="display:none;"'); } ?>>
			<span class="glyphicon glyphicon-duplicate" aria-hidden="true" title="Duplicate"></span>
		</div>
		<div class="statement-text"><?php print(htmlentities($statement->Statement)); ?></div>
		<small><em><?php print(htmlentities($statement->GroupName)); ?> (Weight: <?php print($statement->Weight); ?>)</em></small><br>
	</div>
<?php } ?>

</div>
<div class="row row-same-height row-full-height" id="synth-main">
	<div class="col-xs-6 col-xs-height col-full-height synth-box" id="synth-summaries">
		<div id="synth-summaries-buttons">
			<button class="btn btn-default" id="synth-summary-collapse-all" title="Collapse All Statements">
				<span class="glyphicon glyphicon-collapse-up" aria-hidden="true"></span>
			</button>
			<button class="btn btn-default" id="synth-summary-expand-all" title="Expand All Statements">
				<span class="glyphicon glyphicon-collapse-down" aria-hidden="true"></span>
			</button>
		</div>
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
		<div id="synth-statements-search">
			<div class="input-group">
				<span class="input-group-addon"><span class="glyphicon glyphicon-filter" title="Filter"></span></span>
				<input type="text" class="form-control" id="search-text">
				<span class="input-group-btn">
					<button class="btn btn-default" type="button" id="search-clear"><span class="glyphicon glyphicon-remove" title="Clear"></span></button>
				</span>
			</div>
		</div>
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
