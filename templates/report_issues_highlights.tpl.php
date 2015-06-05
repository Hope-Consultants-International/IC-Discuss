<h2>Issue Report with Highlights</h2>
<p>
	This is a report of all issues with summaries and highlighted statements.
	In printouts, it will print each issue on a new page.
</p>

<?php foreach ($issues as $issue) { ?>
	<h3 style="page-break-before: always"><?php print(htmlentities($issue->Title)); ?></h3>
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
					<?php if ($num_statements == 1) { 
						$statement = $summary->statements[0]; ?>
						<small>(Group: <?php print(htmlentities($statement->GroupName)); ?> /
							Weight: <?php print(htmlentities($statement->Weight)); ?>)</small>
					<?php } else { ?>
						<ul>
							<?php
							foreach ($summary->statements as $statement) { 
								if ($statement->Highlight) {
							?>
								<li>
									<?php print(htmlentities($statement->Statement)); ?><br>
									<small>
										Group: <?php print(htmlentities($statement->GroupName)); ?> /
										Weight: <?php print(htmlentities($statement->Weight)); ?>
									</small>
								</li>
							<?php
								} 
							} 
							?>
						</ul>
					<?php } ?>
				</li>
			<?php } ?>
		</ul>
	<?php } ?>
<?php } ?>

<?php if (DEBUG) { ?>
<div class="hidden-print">
	<h2>Debug</h2>
	<pre>$issues = <?php print_r($issues); ?></pre>
</div>
<?php } ?>