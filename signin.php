<!DOCTYPE html>
<html>
<head>
	<title>Sign In</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link href="./resources/css/bootstrap.min.css" rel="stylesheet" media="screen" />
	<link href="./resources/css/core.css" rel="stylesheet" media="screen" />
	<!-- Include minimised Thinbus SRP JS crypo library, chosen hashing algorithm, configuration  -->
	<script src="http://code.jquery.com/jquery-latest.js"></script>
    <!-- Include Thinbus SRP safe prime config first  -->
	<script src="./resources/js/rfc5054-safe-prime-config.js"></script>
	<!-- Include minimised Thinbus SRP JS crypo library, chosen hashing algorithm, configuration  -->
	<script src="./resources/js/thinbus-srp6a-sha256-versioned.js"></script>
	<script src="./resources/js/signin.js"></script>
</head>
<body>
	<form id="login-form" class="form-narrow form-horizontal" method="post">
		<fieldset>
			<legend>Please Sign In</legend>
			<div class="form-group">
				<label for="email-login" class="col-lg-2 control-label">Email</label>
				<div class="col-lg-10">
					<input type="text" class="form-control" id="email-login" placeholder="Email" name="email" value="ruslan.zavackiy@gmail.com" />
				</div>
			</div>
			<div class="form-group">
				<label for="password-login" class="col-lg-2 control-label">Password</label>
				<div class="col-lg-10">
					<input type="password" class="form-control" id="password-login" placeholder="Password" name="password" />
				</div>
			</div>
			<div class="form-group">
				<div class="col-lg-offset-2 col-lg-10">
					<button type="submit" class="btn btn-default" id="loginBtn">Sign
						in</button>
				</div>
			</div>
			<div class="form-group">
				<div class="col-lg-offset-2 col-lg-10">
					<p>
						New here? <a href="./signup.php">Sign Up</a>
					</p>
				</div>
			</div>
		</fieldset>
	</form>
    <?php
    if (!empty($_REQUEST['SRP_ERROR'])) {
    ?>
    <div class="alert alert-danger">
    <?php echo $_REQUEST['SRP_ERROR'] . "<br/>";?>
	</div>
    <?php
    }
    ?>
	<script>
$(function () {
 /**
 Here we demonstrate overriding the default 'options' values of the Login object in the signin.js file.
 See the comments in signin.js for details of what these values are and their defaults.
 Note that as SpringMVC has built in CSRF Token support we tell the SRP code to submit that hidden input
 field else Spring will reject the form submit as a possible CSRF attack. So we white-list that field
 to post to the server along with the email and the computed proof-of-password.
 */
  Login.initialize({
   challengeUrl: './login.php',
   securityCheckUrl: './login.php',
   emailId: '#email-login',
   passwordId: '#password-login',
   formId: '#login-form',
   whitelistFields: [
     'email'
   ],
   debugOutput: function (msg) {
	   console.log(msg);
	   $('#login-output').append('<b>'+msg+'</b><br />');
   }
  });
});
</script>
</body>
</html>