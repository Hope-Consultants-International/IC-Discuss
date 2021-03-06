<?php
/**
IC-Discuss (c) 2015 by Hope Consultants International Ltd.

IC-Discuss is licensed under a
Creative Commons Attribution-ShareAlike 4.0 International License.

You should have received a copy of the license along with this
work.  If not, see <http://creativecommons.org/licenses/by-sa/4.0/>.
**/
?>
<h2>Group Report</h2>
<p>
	This is a short report of all groups and their statements.
	In printouts, it will print each group on a new page.
</p>

<?php foreach ($groups as $group) { ?>
	<h3 style="page-break-before: always;"><?php print(htmlentities($group->Name)); ?></h3>
	<?php foreach ($group->issues as $issue) { ?>
		<h4><?php print(htmlentities($issue->Title)); ?></h4>
		<p>
			<?php print(htmlentities($issue->Description)); ?>
		</p>
		<?php if (count($issue->statements) == 0) { ?>
			<em>No Statements recorded.</em>
		<?php } else { ?>
			<ul>
			<?php foreach ($issue->statements as $statement) { ?>
				<li>
					<?php print(htmlentities($statement->Statement)); ?>
					<small>(Weight: <?php print(htmlentities($statement->Weight)); ?>)</small>
				</li>
			<?php } ?>
			</ul>
		<?php } ?>
	<?php } ?>
<?php } ?>

<?php if (DEBUG) { ?>
<div class="hidden-print">
	<h2>Debug</h2>
	<pre>$groups = <?php print_r($groups); ?></pre>
</div>
<?php } ?>
