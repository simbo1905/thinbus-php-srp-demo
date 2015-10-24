<!DOCTYPE html>
<html>
<head>
    <title>Signup</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link href="./resources/css/bootstrap.min.css" rel="stylesheet" media="screen" />
    <link href="./resources/css/core.css" rel="stylesheet" media="screen" />
    <script src="http://code.jquery.com/jquery-latest.js"></script>
    <script src="./resources/js/bootstrap.min.js"></script>
    <script src="./resources/js/simple-password-meter.js"></script>
    <!-- Include Thinbus SRP safe prime config first  -->
	<script src="./resources/js/rfc5054-safe-prime-config.js"></script>
	<!-- Include minimised Thinbus SRP JS crypo library, chosen hashing algorithm, configuration  -->
	<script src="./resources/js/thinbus-srp6a-sha256-versioned.js"></script>
    <script src="./resources/js/signup.js"></script>
</head>
<body>
<form id="register-form" class="form-narrow form-horizontal" method="post">
    <fieldset>
        <legend>Please Sign Up</legend>
        <div class="form-group">
            <label for="email" class="col-lg-2 control-label">Email</label>
            <div class="col-lg-10">
                <input id="email-login" value="ruslan.zavackiy@gmail.com" name="email" type="text" class="form-control" placeholder="Email address" />
            </div>
        </div>
        <div class="form-group">
            <label for="password" class="col-lg-2 control-label">Password</label>
            <div class="col-lg-10">
                <input id="password" name="password" type="password" class="form-control" placeholder="Password" />
                Strength: <span id="strength_score">0</span> <span id="strength_human">None</span>
            </div>
        </div>
        <div class="form-group">
            <div class="col-lg-offset-2 col-lg-10">
                <button id="registerBtn" type="submit" class="btn btn-default">Sign up</button>
            </div>
        </div>
        <div class="form-group">
            <div class="col-lg-offset-2 col-lg-10">
                <p>Already have an account? <a href="./signin.php">Sign In</a></p>
            </div>
        </div>
        <input id="password-salt" type="hidden" name="salt" />
        <input id="password-verifier" type="hidden" name="verifier" />
    </fieldset>
    <?php
    if (!empty($_REQUEST['SRP_ERROR'])) {
    ?>
    <div class="alert alert-danger">
    <?php echo $_REQUEST['SRP_ERROR'] . "<br/>";?>
	</div>
    <?php
    }
    ?>
</form>
<script>
/**
Here we demonstrate overriding the default 'options' values of the Register object in the signup.js file.
See the comments in signup.js for details of what these values are and their defaults.
Note that as SpringMVC has built in CSRF Token support we tell the SRP code to submit that hidden input
field else Spring will reject the form submit as a possible CSRF attack. So we white-list that field
to post to the server.
*/
  $(function () {
	    Register.initialize({
	    	registerUrl: './register.php',
	        emailId: '#email-login',
	        formId: '#register-form',
	        registerBtnId: '#registerBtn',
	        passwordId: '#password',
	        passwordSaltId: '#password-salt',
	        passwordVerifierId: '#password-verifier',
	        whitelistFields: [
              'email',
              'salt',
              'verifier'
            ]
	      });
  });
</script>
</body>
</html>