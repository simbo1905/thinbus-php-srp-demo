<?php
//error_log("session_id:'".session_id()."'");
if (empty($_SESSION['SRP_USER_ID'])) {
    header('HTTP/1.0 403 Forbidden');
    $_REQUEST['SRP_ERROR'] = "Forbidden";
    include __DIR__.'/signin.php';
    exit(0);
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Sign In</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="./resources/css/bootstrap.min.css" rel="stylesheet"
	media="screen" />
<link href="./resources/css/core.css" rel="stylesheet" media="screen" />
</head>
<body>
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse"
					data-target=".nav-collapse">
					<span class="icon-bar"></span> <span class="icon-bar"></span> <span
						class="icon-bar"></span>
				</button>
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav">
					<li class="active"><a href="./index.html">Home</a></li>
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<li><a href="./signout.php">Sign in</a></li>

				</ul>
			</div>
			<!--/.nav-collapse -->
		</div>
	</div>
	<div class="container">

	<?php
	echo '<pre>';
    print_r($_SESSION);
    echo '</pre>';
    ?>
	
		<div class="alert alert-dismissable alert-success">
			<button type="button" class="close" data-dismiss="alert"
				aria-hidden="true">×</button>
			<span>
    <?php
    if (! empty($_SESSION['SRP_USER_ID'])) {
        
        echo "Congratulations " . $_SESSION['SRP_USER_ID'] . " you have successfully signed into the application!.";
        
    }
    ?>
			</span>
		</div>

	</div>
</body>
</html>