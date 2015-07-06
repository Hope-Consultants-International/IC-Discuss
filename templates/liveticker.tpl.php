<h2 style="overflow:hidden; white-space: nowrap;">Live-Ticker<span id="text-issue-title"></span></h2>
</div>
<div id="ticker-options">
	<select id="issue_id">
		<option value="0" <?php if ($issue_id == 0) { print("selected"); } ?>>All Frontpage Issues</option>
		<?php foreach ($issues as $id => $value) { ?>
			<option value="<?php print($id); ?>" <?php if ($issue_id == $id) { print("selected"); } ?>><?php print(htmlentities($value)); ?></option>
		<?php } ?>
	</select>
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
	<pre>$summaries = <?php print_r($summaries); ?></pre>
	<pre>$statements = <?php print_r($statements); ?></pre>
</div>
<?php } ?>