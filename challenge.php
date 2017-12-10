<?php
/**
 * WARNING: Do not use this file in production. This demo shows loading
 * the user salt and verifier from a database. You should use your own code
 * to do that or consider using an opensource content management system
 * that has its logic for loading the user so you only need to do is add
 * additional columns for the salt and verifier.
 *
 * The only parts of this file that are actually real code are the three lines
 * containing the variable "$srp". Everything else is disposable demo code.
 *
 * The code below is the "AJAX /challenge" logic of the following diagram:
 *
 * http://simon_massey.bitbucket.org/thinbus/login.png
 *
 * @see https://bitbucket.org/simon_massey/thinbus-srp-js
 *
 * @author      ruslan.zavackiy
 * @author      Simon Massey
 */
require 'require.php';

$result = array();

if (! empty($_POST['email'])) {
    $user = R::findOne('user', 'email = :email', array(
        ':email' => $_POST['email']
    ));
    
    if (empty($user)) {
        $result = array(
            'error' => 'No user with such email'
        );
    } else {

        /**
        Initialize a server SRP object using the configuration parameters and the user values loaded from the database.
         */
        $srp = new ThinbusSrp($SRP6CryptoParams["N_base10"], $SRP6CryptoParams["g_base10"], $SRP6CryptoParams["k_base16"], $SRP6CryptoParams["H"]);
        
        /**
        Generate the one-time server challenge.
         */
        $B = $srp->step1($_POST['email'], $user->password_salt, $user->password_verifier);
        
        /**
        Serialize the server SRP object so that we can store it until the browser responds to our challange
         */
        $serial = serialize($srp);
        
        /**
         The following code is disposable demo code. It is entirly up to you how you store the object between
         browser requiests you could use your own database access code or you could store in the $_SESSION.
         */
        
        $authentication = R::findOne('authentication', 'email = :email', array(
            ':email' => $user->email
        ));
        
        if (empty($authentication))
            $authentication = R::dispense('authentication');
        
        $authentication->email = $user->email;
        $authentication->srp = serialize($srp);
        $dbid = R::store($authentication);
        
        $result = array(
            'salt' => $user->password_salt,
            'b' => $B
        );
    }
} else {
    $result = array(
        'error' => 'No email found in post. '
    );
}

echo json_encode($result);

exit();



