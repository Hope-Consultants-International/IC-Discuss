<h2 style="overflow:hidden; white-space: nowrap;">Live-Ticker <span id="text-issue-title"></span></h2>
</div>
<div id="scroller">
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