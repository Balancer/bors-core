<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="<?= htmlspecialchars($self->description());/*"*/?>">
	<meta name="author" content="">
<?php /*	<link rel="icon" href="../../favicon.ico"> */?>

	<title><?= htmlspecialchars($self->browser_title()); ?></title>

	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">

	<!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
	<!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
	<script src="../../assets/js/ie-emulation-modes-warning.js"></script>

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	  <script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	  <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>

<body role="document">

	<!-- Fixed navbar -->
	<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>

				<a class="navbar-brand" href="<?= $self->project()->url();/*"*/?>"><?= htmlspecialchars($self->project()->title()); ?></a>
			</div>
<?php
	if($nav_menu = $self->get('navbar'))
	{
?>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav">

<?php
		foreach($nav_menu as $title => $submenu)
		{
			if(is_array($submenu))
			{
?>
				<li class="dropdown">
<!--				<a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <span class="caret"></span></a>-->
<?php
			bors_layouts_bootstrap3_dropdown::show(array('menu' => array($title => $submenu)));
?>
				</li>
<?php
			}
			else
			{
				if(preg_match('/^\w+$/', $submenu))
					$url = "/$submenu/";
				else
					$url = $submenu;
				echo "<li class=\"active2\"><a href=\"{$url}\">".htmlspecialchars($title)."</a></li>\n";
			}
		}
?>
				</ul>
			</div><!--/.nav-collapse -->
<?php
	}
?>
		</div>
	</div>

	<div class="container theme-showcase" role="main">

		<div class="jumbotron">
			<h1><?= $self->page_title() ?></h1>
			<?php if($self->description()) echo "<p>".htmlspecialchars($self->description())."</p>"; ?>
		</div>

<?php require __DIR__.'/bootstrap3/breadcrumbs.tpl.php'; ?>

		<?= $self->body() ?>

	</div> <!-- /container -->

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

	<script src="../../assets/js/docs.min.js"></script>
	<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
	<script src="../../assets/js/ie10-viewport-bug-workaround.js"></script>

</body>
</html>
