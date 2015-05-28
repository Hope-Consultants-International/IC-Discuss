<h2>Detailed Issue Report</h2>
<p>
	This is a detailed report of all issues/statements and which Groups made them.
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
				if ($num_statements == 1) {
				?>
					<li>
						<?php print(htmlentities($summary->Summary)); ?><br>
						<small>Group: <?php print(htmlentities($summary->statements[0]->GroupName)); ?></small>
					</li>
				<?php } else { ?>
					<li>
						<?php print(htmlentities($summary->Summary)); ?><br>
						<small><?php print($num_statements); ?> Statements</small>
						<ul>
							<?php foreach ($summary->statements as $statement) { ?>
								<li>
									<?php print(htmlentities($statement->Statement)); ?><br>
									<small>Group: <?php print(htmlentities($statement->GroupName)); ?></small>
								</li>
							<?php } ?>
						</ul>
					</li>
				<?php } ?>
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