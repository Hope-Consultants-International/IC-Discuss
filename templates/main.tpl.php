<!DOCTYPE html>
<html lang="de">
  <head>
  	<!-- Anmeldung Bootstrap -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php print(htmlentities($page_title)); ?></title>
	<!-- HTML Kommentar -->
	
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="jquery/jquery-1.11.2.min.js"></script>
	
	<!-- Bootstrap -->
	<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="bootstrap/css/bootstrap-theme.min.css">
	<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>

	<!-- our own CSS, so it can override everything else -->
	<link rel="stylesheet" href="css/custom-theme.css">
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
		    <li class=" <?php print(($current_page=='Upload')?'active':''); ?>"><a href="upload.php">Upload XLS</a></li>
			<li class=" <?php print(($current_page=='Summary')?'active':''); ?>"><a href="summary.php">Summary</a></li>
			<li class="dropdown <?php print((strpos($current_page,'Manage|') !== false)?'active':''); ?>">
			  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Manage <span class="caret"></span></a>
			  <ul class="dropdown-menu" role="menu">
			    <li>
				  <a href="groups.php">
				    <span class="glyphicon glyphicon-triangle-right" style="font-size:10px; <?php print((strpos($current_page,'|groups') !== false)?'':'visibility:hidden'); ?>">&nbsp</span>
					Groups
				  </a>
				</li>
				<li>
				  <a href="issues.php">
				    <span class="glyphicon glyphicon-triangle-right" style="font-size:10px; <?php print((strpos($current_page,'|issues') !== false)?'':'visibility:hidden'); ?>">&nbsp</span>
					Issues
				  </a>
				</li>
			  </ul>
			</li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
	<!-- Inhalt -->	
    <div style="height:4em"></div>
	<div class="container messages">
	<div class="row">
	<?php
		$message_types = array(
			MSG_TYPE_ERR => 'alert-danger',
			MSG_TYPE_WARN => 'alert-warning',
			MSG_TYPE_INFO => 'alert-info',
		);
		$fmt = '<div class="alert %s" role="alert">%s</div>';
		foreach ($message_types as $message_type => $alert_class) {
			$messages = get_messages($message_type);
			foreach ($messages as $message) {
				printf($fmt, $alert_class, $message);
			}
		}
	?>
	  </div>
	</div>
    <div class="container theme-showcase" role="main">
	  <?php print($inhalt); ?>
	</div>
  </body>
 </html>