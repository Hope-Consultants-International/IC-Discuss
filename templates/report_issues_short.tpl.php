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
				<?php
					foreach ($issue->summaries as $summary) {
						$num_statements = count($summary->statements);
						$num_groups = $summary->NumGroups;
				?>
					<li>
						<?php print(htmlentities($summary->Summary)); ?><br>
						<small>
							<b><?php print($num_groups); ?></b> Group<?php print(($num_groups > 1) ? 's' : ''); ?>
							/
							<b><?php print($num_statements); ?></b> Statement<?php print(($num_statements > 1) ? 's' : ''); ?>
							/
							<b><?php print(number_format($summary->AverageWeight, 2)); ?></b> Avg. Weight
							/
							<b><?php print(number_format($summary->GroupWeight, 2)); ?></b> Total Weight
						</small>
					</li>
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
