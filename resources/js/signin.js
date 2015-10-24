/**
 * The Login object uses jQuery AJAX and an SRP6JavascriptClientSessionSHA256 object to perform a proof-of-password.  
 * See http://simon_massey.bitbucket.org/thinbus/login.png
 */
var Login = {
		
  /**
   * The following options may be overridden by passing a customer options object into `initialize` method. 
   * See http://simon_massey.bitbucket.org/thinbus/login.png
   * @param challengeUrl The URL to do the AJAX lookup to get the user's salt `s` and one-time random server challenge `B`. 
   * @param securityCheckUrl The URL to post the password proof. 
   * @param emailId The id of the form input field where the user gives their id/email used in the AJAX fetch of the user's salt and challenge. 
   * @param passwordId The id of the password field used to compute a proof-of-password with the server one-time challenge and the user's salt. 
   * @param formId The form who's onSubmit will run the SRP protocol. 
   * @param whitelistFields The fields to post to the server. MUST NOT INCLUDE THE RAW PASSWORD. Some frameworks embed a CSRF token in every form which must be submitted with the form so that hidden field can be whitelisted. 
   * @param debugOutput The demo overrides this to output to html in the page. 
   */
  options: {
	 challengeUrl: './challenge',
	 securityCheckUrl: '/authenticate',
     emailId: '#email-login',
     passwordId: '#password-login',
     formId: '#login-form',
     whitelistFields: ['email'],
     debugOutput: function (msg){
    	 console.log(msg);
     }
  },

  initialize: function (options) {
    var me = this;

    if (options) {
      me.options = options;
    }
    
    $(me.options.formId).on('submit', function (e) {
      // We MUST prevent default submit logic which would submit the raw password so that we can do the SRP protocol instead. 
      e.preventDefault();
      
      var loginForm = $(me.options.formId);
      var fields = loginForm.serializeArray();
      
      var postValues = {
        challenge: "true"
      };

      // copy only white-listed fields such that you don't post the raw password and do pass any additional required fields e.g. CSRF Token
      $.each(fields, function (i, field) {
    	var found = $.inArray(field.name, me.options.whitelistFields) > -1; // http://stackoverflow.com/a/6116511/329496
        if (found ) {
        	postValues[field.name] = field.value;
        }
      });

      me.options.debugOutput('Client: ' + JSON.stringify(postValues) );

      $.post(me.options.challengeUrl, postValues, function () {
        me.onChallengeResponse.apply(me, arguments);
      }, 'json');

      return false;
    });
  },

  onChallengeResponse: function (response) {
    var me = this;

    me.options.debugOutput('Server: ' + JSON.stringify(response) );

    var email = me.getEmail();
    var password = me.getPassword();
    var srpClient = new SRP6JavascriptClientSessionSHA256();
    
    var start = Date.now();

    try {
    	srpClient.step1(email, password);
    } catch(e) {
    	console.log('unexpected programmer error: '+e.message);
    	window.location = window.location;
    }
    
    var credentials = srpClient.step2(response.salt, response.b);

    var end = Date.now();

    var loginForm = $(me.options.formId);
    var fields = loginForm.serializeArray();
    
    var values = {
		username: me.getEmail(),
		password: credentials.M1+":"+credentials.A
    };

    // copy only white-listed fields such that you don't post the raw password and do pass any additional required fields e.g. CSRF Token
    $.each(fields, function (i, field) {
  	var found = $.inArray(field.name, me.options.whitelistFields) > -1; // http://stackoverflow.com/a/6116511/329496
      if (found ) {
      	values[field.name] = field.value;
      }
    });
    
	me.options.debugOutput('Client: crypto took ' + (end-start) + 'ms');
	me.options.debugOutput('Client: ' + JSON.stringify(values) );

    $.post(me.options.securityCheckUrl, values, function (response) {
  	  $('body').html(response);
    });
  },

  getEmail: function () {
	  return $(this.options.emailId).val();
  },

  getPassword: function () {
    return $(this.options.passwordId).val();
  }

}