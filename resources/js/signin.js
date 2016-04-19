/**
 * Note that this is all disposable demo code. 
 * 
 * Only the three lines using "srpClient" are mandatory code.  
 * 
 * http://simon_massey.bitbucket.org/thinbus/login.png
 */
var Login = {

	options : null,

	initialize : function(options) {
		var me = this;

		if (options) {
			me.options = options;
		}

		$(me.options.formId).on(
				'submit',
				function(e) {
					$("#alert-danger").hide();
					$("#alert-success").hide();

					// We MUST prevent default submit logic which would submit
					// the raw password.
					e.preventDefault();

					// Instead we copy the allowed fields out of the real form
					// and AJAX post them to the server.

					var loginForm = $(me.options.formId);
					var fields = loginForm.serializeArray();

					var postValues = {
						challenge : "true"
					};

					// copy only white-listed fields such that you don't post
					// the raw password and do pass any additional required
					// fields e.g. CSRF Token
					$.each(fields, function(i, field) {
						var found = $.inArray(field.name,
								me.options.whitelistFields) > -1; // http://stackoverflow.com/a/6116511/329496
						if (found) {
							postValues[field.name] = field.value;
						}
					});

					me.options.debugOutput('Client: '
							+ JSON.stringify(postValues));

					$.post(me.options.challengeUrl, postValues, function() {
						// response containing the challenge will be dealt with in this method. 
						me.onChallengeResponse.apply(me, arguments);
					}, 'json');

					return false;
				});
	},

	onChallengeResponse : function(response) {

		var me = this;

		me.options.debugOutput('Server: ' + JSON.stringify(response));

		var email = me.getEmail();
		var password = me.getPassword();

		var start = Date.now();

		var srpClient = new SRP6JavascriptClientSessionSHA256();
		srpClient.step1(email, password);
		var credentials = srpClient.step2(response.salt, response.b);

		var end = Date.now();

		var loginForm = $(me.options.formId);
		var fields = loginForm.serializeArray();

		// here we build the password as being M1+":"+A rather than try to send two seperate 
		// values that might not be easy to do when trying to add SRP into an existing app
		var values = {
			username : me.getEmail(),
			password : credentials.M1 + ":" + credentials.A
		};

		// copy only white-listed fields such that you don't post the raw
		// password and do pass any additional required fields e.g. CSRF Token
		$.each(fields, function(i, field) {
			var found = $.inArray(field.name, me.options.whitelistFields) > -1; // http://stackoverflow.com/a/6116511/329496
			if (found) {
				values[field.name] = field.value;
			}
		});

		me.options.debugOutput('Client: crypto took ' + (end - start) + 'ms');
		me.options.debugOutput('Client: ' + JSON.stringify(values));

		$.ajax({
			type : "POST",
			data : values,
			url : me.options.securityCheckUrl,
			success : function(response) {
				console.log("success server says: " + response);
				var parsed_data = JSON.parse(response);
				if (parsed_data.hasOwnProperty('error')) {
					$("#alert-danger").html(parsed_data.error);
					$("#alert-danger").show();
				} else if (parsed_data.hasOwnProperty('message')) {
					$("#alert-success").html(parsed_data.message);
					$("#alert-success").show();
				}
			},
			error : function(xhr, ajaxOptions, thrownError) {
				if (xhr.status == 403) {
					var error = "failure server says 403 Forbidden";
					console.log(error);
					$("#alert-danger").html("server says 403 Forbidden");
					$("#alert-danger").show();
				}
			}
		});
	},

	getEmail : function() {
		return $(this.options.emailId).val();
	},

	getPassword : function() {
		return $(this.options.passwordId).val();
	}

}