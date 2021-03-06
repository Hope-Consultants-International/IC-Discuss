<?php
/**
IC-Discuss (c) 2015 by Hope Consultants International Ltd.

IC-Discuss is licensed under a
Creative Commons Attribution-ShareAlike 4.0 International License.

You should have received a copy of the license along with this
work.  If not, see <http://creativecommons.org/licenses/by-sa/4.0/>.
**/
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Bootstrap -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php print(htmlentities($page_title)); ?></title>
	
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script type="text/javascript" src="jquery/jquery-2.1.4.min.js"></script>
	
	<!-- jQuery UI -->
	<link rel="stylesheet" href="jquery-ui/jquery-ui.structure.min.css">
	<link rel="stylesheet" href="jquery-ui/jquery-ui.theme.min.css">
	<script type="text/javascript" src="jquery-ui/jquery-ui.min.js"></script>
	
	<!-- jQuery highlight-->
	<script type="text/javascript" src="jquery-highlight/jquery.highlight.min.js"></script>
	
	<!-- Bootstrap -->
	<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="bootstrap/css/bootstrap-theme.min.css">
	<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>

	<!-- Bootbox -->
	<script type="text/javascript" src="bootbox/js/bootbox.min.js"></script>
	
	<!-- Bootstrap - TouchSpin -->
	<link rel="stylesheet" href="bootstrap-touchspin/jquery.bootstrap-touchspin.min.css">
	<script type="text/javascript" src="bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
	
	<!-- our own CSS, so it can override everything else -->
	<link rel="stylesheet" href="css/custom-theme.css?version=<?php print(RESOURCE_VERSION); ?>">
  </head>
  <body role="document">
    <!-- Fixed navbar -->
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.php">
		    <img alt="Logo" title="<?php print(htmlentities(APP_TITLE));?>" src="images/brand.png" style="max-width:2em; max-height:2em; margin-top:-0.4em">
		  </a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
			<?php if (checkAccess(SECTION_UPLOAD)) { ?>
				<li class=" <?php print(($current_page=='Upload')?'active':''); ?>"><a href="upload.php">Upload XLS</a></li>
			<?php } ?>
			<?php if (checkAccess(SECTION_SYNTHESIZE)) { ?>
				<li class="dropdown <?php print((strpos($current_page,'Synthesize|') !== false)?'active':''); ?>">
				  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Synthesize <span class="caret"></span></a>
				  <ul class="dropdown-menu" role="menu">
					<?php foreach ($issues as $id => $title) { ?>
					<li>
					  <a href="synthesize.php?issue=<?php print($id); ?>">
						<span class="glyphicon glyphicon-triangle-right" style="font-size:10px; <?php print((strpos($current_page,'Synthesize|'.$id) !== false)?'':'visibility:hidden'); ?>">&nbsp</span>
						<?php print(htmlentities($title)); ?>
					  </a>
					</li>
					<?php } ?>
				  </ul>
				</li>
			<?php } ?>
			<?php if (checkAccess(SECTION_SYNTHESIZE)) { ?>
			<li class="dropdown <?php print((strpos($current_page,'Report|') !== false)?'active':''); ?>">
			  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Report <span class="caret"></span></a>
			  <ul class="dropdown-menu" role="menu">
			    <li>
				  <a href="report.php?type=issues_short">
				    <span class="glyphicon glyphicon-triangle-right" style="font-size:10px; <?php print((strpos($current_page,'Report|issues_short') !== false)?'':'visibility:hidden'); ?>">&nbsp</span>
					Issues - Short
				  </a>
				</li>
				<li>
				  <a href="report.php?type=issues_highlights">
				    <span class="glyphicon glyphicon-triangle-right" style="font-size:10px; <?php print((strpos($current_page,'Report|issues_highlights') !== false)?'':'visibility:hidden'); ?>">&nbsp</span>
					Issues - Highlights
				  </a>
				</li>
				<li>
				  <a href="report.php?type=issues_detail">
				    <span class="glyphicon glyphicon-triangle-right" style="font-size:10px; <?php print((strpos($current_page,'Report|issues_detail') !== false)?'':'visibility:hidden'); ?>">&nbsp</span>
					Issues - Details
				  </a>
				</li>
				<li class="divider"></li>
				<li>
				  <a href="report.php?type=groups">
				    <span class="glyphicon glyphicon-triangle-right" style="font-size:10px; <?php print((strpos($current_page,'Report|groups') !== false)?'':'visibility:hidden'); ?>">&nbsp</span>
					Groups
				  </a>
				</li>
			  </ul>
			</li>
            <?php } ?>
            <?php if (checkAccess(array(SECTION_SYNTHESIZE, SECTION_TICKER))) { ?>
                <li class=" <?php print(($current_page == 'Live-Ticker')?'active':''); ?>"><a href="liveticker.php">Live-Ticker</a></li>
			<?php } ?>
			<?php if (checkAccess(SECTION_MANAGE)) { ?>
			<li class="dropdown <?php print((strpos($current_page,'Manage|') !== false)?'active':''); ?>">
			  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Manage <span class="caret"></span></a>
			  <ul class="dropdown-menu" role="menu">
			    <li>
				  <a href="groups.php">
				    <span class="glyphicon glyphicon-triangle-right" style="font-size:10px; <?php print((strpos($current_page,'|Groups') !== false)?'':'visibility:hidden'); ?>">&nbsp</span>
					Groups
				  </a>
				</li>
				<li>
				  <a href="issues.php">
				    <span class="glyphicon glyphicon-triangle-right" style="font-size:10px; <?php print((strpos($current_page,'|Issues') !== false)?'':'visibility:hidden'); ?>">&nbsp</span>
					Issues
				  </a>
				</li>
				<li>
				  <a href="statements.php">
				    <span class="glyphicon glyphicon-triangle-right" style="font-size:10px; <?php print((strpos($current_page,'|Statements') !== false)?'':'visibility:hidden'); ?>">&nbsp</span>
					Statements
				  </a>
				</li>
			  </ul>
			</li>
			<?php } ?>
			<li class=" <?php print(($current_page=='Help')?'active':''); ?>"><a href="help.php">Help<?php if (!isLoggedIn()) { print(" / Login"); } ?></a></li>
            <?php if (ACCESS_ENABLED && isLoggedIn()) { ?>
                <li class=" <?php print(($current_page=='Logout')?'active':''); ?>"><a href="logout.php">Logout</a></li>
            <?php } ?>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
	<!-- Contents -->
    <div style="height:4em"></div>
	<div class="container">
		<div class="row messages">
		<?php
			$message_types = array(
				MSG_TYPE_ERR => 'alert-danger',
				MSG_TYPE_WARN => 'alert-warning',
				MSG_TYPE_INFO => 'alert-info',
			);
			$fmt = '<div class="alert %s" role="alert">%s</div>';
			foreach ($message_types as $message_type => $alert_class) {
				$messages = getMessages($message_type);
				foreach ($messages as $message) {
					printf($fmt, $alert_class, $message);
				}
			}
		?>
		</div>
		<div class="row theme-showcase" role="main">
		<?php print($contents); ?>
	  </div>
	</div>
  </body>
 </html>
