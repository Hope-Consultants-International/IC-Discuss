<h2>Short Issue Report</h2>
<p>
	This is a short report of all issues/statements.
</p>

<?php foreach ($issues as $issue) { ?>
	<div style="page-break-inside: avoid;">
		<h3><?php print(htmlentities($issue->Title)); ?></h3>
		<p>
			<?php print(htmlentities($issue->Description)); ?>
		</p>
		<?php if (count($issue->summaries) == 0) { ?>
			<em>No Statements recorded.</em>
		<?php } else { ?>
			<h4>Statements</h4>
			<ul>
				<?php foreach ($issue->summaries as $summary) { ?>
					<?php
					$num_statements = count($summary->statements);
					if ($num_statements == 1) {
					?>
						<li>
							<b>1 &times;</b> <?php print(htmlentities($summary->Summary)); ?>
						</li>
					<?php } else { ?>
						<li>
							<b><?php print($num_statements); ?> &times;</b> <?php print(htmlentities($summary->Summary)); ?>
						</li>
					<?php } ?>
				<?php } ?>
			</ul>
		<?php } ?>
	</div>
<?php } ?>

<?php if (DEBUG) { ?>
<div class="hidden-print">
	<h2>Debug</h2>
	<pre>$issues = <?php print_r($issues); ?></pre>
</div>
<?php } ?>