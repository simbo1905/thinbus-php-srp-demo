/**
 * Note that this is all disposable demo code. You don't need to use jQuery 
 * or even AJAX if you do not wish to. 
 * 
 * Only the three lines using "srpClient" are mandatory code.  
 * 
 * See http://simon_massey.bitbucket.org/thinbus/register.png
 */
var Register = {

	options : null,

	initialize : function(options) {
		var me = this;

		if (options) {
			me.options = options;
		}

		me.disableSubmitBtn();

		// attach logic to the form onSubmit
		$(options.formId).on('submit', $.proxy(function(e) {
			// We MUST prevent default submit logic which would submit the raw password so that we can do the SRP protocol instead. 
			e.preventDefault();
			// Do the post of the random salt and verifier. 
			me.postSaltAndVerifier();
		}, me));

		// attach logic to the email field onKeyUp
		$(options.emailId).on('keyup', $.proxy(function(event) {
			// see recommendation in the thinbus docs to advance the random number generated in browsers that do not have secure randoms. 
			random16byteHex.advance(Math.floor(event.keyCode / 4));
		}, me));

		// attach logic to the password field onKeyUp
		$(options.passwordId).on(
				'keyup',
				$.proxy(function(event) {
					// only enable the button if the user has entered some password
					// You should connect this logic to the password stength meter and disable button for weak passwords! 
					$(event.currentTarget).val().length ? me.enableSubmitBtn()
							: me.disableSubmitBtn();
					// see recommendation in the thinbus docs to advance the random number generated in browsers that do not have secure randoms. 
					random16byteHex.advance(Math.floor(event.keyCode / 4));
				}, me));
	},

	disableSubmitBtn : function() {
		$(this.options.registerBtnId).attr('disabled', true);
	},

	enableSubmitBtn : function() {
		$(this.options.registerBtnId).removeAttr('disabled');
	},

	postSaltAndVerifier : function() {
		
		$("#alert-danger").hide();
		$("#alert-success").hide();
		
		var me = this;

		var email = me.getEmail();
		var password = me.getPassword();

		var srpClient = new SRP6JavascriptClientSessionSHA256();
		var salt = srpClient.generateRandomSalt();
		var verifier = srpClient.generateVerifier(salt, email, password);

		$(me.options.passwordSaltId).attr('value', salt);
		$(me.options.passwordVerifierId).attr('value', verifier);

		var registerForm = $(me.options.formId);
		var fields = registerForm.serializeArray();

		var postValues = {};

		// copy only white-listed fields such that you don't post the raw password and do pass any additional required fields e.g. CSRF Token
		$.each(fields, function(i, field) {
			var found = $.inArray(field.name, me.options.whitelistFields) > -1; // http://stackoverflow.com/a/6116511/329496
			if (found) {
				postValues[field.name] = field.value;
			}
		});

		console.log('Client: ' + JSON.stringify(postValues));

		$.post(me.options.registerUrl, postValues, function(response) {
			console.log("server says: "+response );
			var parsed_data = JSON.parse(response);
			if( parsed_data.hasOwnProperty('error') ) {
				$("#alert-danger").html(parsed_data.error);
				$("#alert-danger").show();
			} else if( parsed_data.hasOwnProperty('message') ) {
				$("#alert-success").html(parsed_data.message);
				$("#alert-success").show();	
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