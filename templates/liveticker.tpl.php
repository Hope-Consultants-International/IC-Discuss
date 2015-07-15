<h2 style="overflow:hidden; white-space: nowrap;">Live-Ticker<span id="text-issue-title"></span></h2>
</div>
<div id="ticker-options">
	<form class="form-inline">
		<div class="form-group">
			<select id="issue_id">
			</select>
		</div>
		<div id="ticker-buttons" class="form-group">
			<button id="pauseButton" type="button" class="btn btn-default btn-xs">
				<span class="glyphicon glyphicon-pause"></span>
			</button>
		</div>
	</form>
</div>
<div id="ticker-scroller">
</div>

<script>
<?php print($script); ?>
</script>
<script type="text/javascript" src="js/liveticker.js?v=<?php print(RESOURCE_VERSION); ?>"></script>

<?php if (DEBUG) { ?>
<div class="hidden-print">
	<h2>Debug</h2>
	<pre>$issues = <?php print_r($issues); ?></pre>
</div>
<?php } ?>
