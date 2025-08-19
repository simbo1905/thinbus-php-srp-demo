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

if (! empty($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    try {
        /**
         * The Javascript sends the SRP data packed into the password as M1+":"+A.
         * This makes it easier to integration SRP with an existing content management
         * system or security framework that expects only one password string to check.
         */
        $M1A = explode(':', $password);
        $M1 = $M1A[0];
        $A = $M1A[1];
        
        /**
         * The following code is disposable demo code.
         * It is entirely up to you how you store the object between browser
         * requiests you could use your own database access code or you
         * could store in the $_SESSION.
         */
        
        /**
         * The following code uses session storage instead of database unserialize
         * to avoid security vulnerabilities with unserialize().
         */
        
        session_start();
        
        // Check if we have a valid SRP session for this email
        if (isset($_SESSION['srp_auth']) && 
            $_SESSION['srp_auth']['email'] === $email) {
            
            $srp = $_SESSION['srp_auth']['srp_object'];
            
            // Optional: Check session timeout (5 minutes)
            $session_age = time() - $_SESSION['srp_auth']['timestamp'];
            if ($session_age > 300) { // 5 minutes
                unset($_SESSION['srp_auth']);
                throw new Exception('Authentication session expired');
            }
            
            /**
             * This is the actual SRP authorisation logic which throws an exception if the password proof is bad.
             */
            $srp->step2($A, $M1);
            
            // Clear the session data after successful authentication
            unset($_SESSION['srp_auth']);
            
            /**
            This result is actually an optional proof of a shared session key that
            the client could verify.
             */
            $result = array(
                'message' => 'Success!'
            );
            
        } else {
            $result = array(
                'error' => 'No prior challenge or session expired.'
            );
        }
        
    } catch (Exception $e) {
        header('HTTP/1.0 403 Forbidden');
        $result = array(
            'error' => '403 Forbidden.'
        );
    }
} else {
    header('HTTP/1.0 400 Bad request');
    $result = array(
        'error' => 'No password found in post.'
    );
}

echo json_encode($result);

exit();



