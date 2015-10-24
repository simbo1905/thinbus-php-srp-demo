/**
 * The Register object uses jQuery AJAX and an SRP6JavascriptClientSessionSHA256
 * to generate a salt and a verifier which is sent to the server. See
 * http://simon_massey.bitbucket.org/thinbus/register.png
 */
var Register = {
	/**
	 * The following options may be overridden by passing a customer options object into `initialize` method. 
	 * See http://simon_massey.bitbucket.org/thinbus/register.png
	 * @param registerUrl The URL to post the email, salt and verifier. 
	 * @param registerBtnId The button to disable until the user has filled in the form. 
	 * @param formId The form who's onSubmit will run the SRP protocol. 
	 * @param emailId The id of the form input field where the user gives their id/email
	 * @param passwordId The id of the password field used to generate the password verifier. 
	 * @param passwordSaltId The field to populate with the generated salt. 
	 * @param passwordVerifierId The field to populate with the generated password verifier. 
	 * @param whitelistFields The fields to post to the server. MUST NOT INCLUDE THE RAW PASSWORD. Some frameworks embed a CSRF token in every form which must be submitted with the form so that hidden field can be whitelisted. 
	 */
	options : {
		registerUrl : './register',
		registerBtnId : '#registerBtn',
		formId : '#register-form',
		emailId : '#email-login',
		passwordId : '#password',
		passwordSaltId : '#password-salt',
		passwordVerifierId : '#password-verifier',
		whitelistFields : [ 'email', 'salt', 'verifier' ]
	},

	initialize : function(options) {
		var me = this;

		me.disableSubmitBtn();

		if (options) {
			me.options = options;
		}

		// attach logic to the form onSubmit
		$(options.formId).on('submit', $.proxy(function(e) {
			// We MUST prevent default submit logic which would submit the raw password so that we can do the SRP protocol instead. 
			e.preventDefault();
			me.postSaltAndVerifier();
		}, me));

		// attach logic to the email field onKeyUp
		$(options.emailId).on('keyup', $.proxy(function(event) {
			// see recommendation in the thinbus docs 
			random16byteHex.advance(Math.floor(event.keyCode / 4));
		}, me));

		// attach logic to the password field onKeyUp
		$(options.passwordId).on(
				'keyup',
				$.proxy(function(event) {
					// only enable the button if the user has entered some password
					$(event.currentTarget).val().length ? me.enableSubmitBtn()
							: me.disableSubmitBtn();
					// see recommendation in the thinbus docs 
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
			$('body').html(response);
		});

	},

	getEmail : function() {
		return $(this.options.emailId).val();
	},

	getPassword : function() {
		return $(this.options.passwordId).val();
	}
}