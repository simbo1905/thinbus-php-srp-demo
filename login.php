<?php
/**
 * @author      ruslan.zavackiy
 * @author      Simon Massey
 * @see http://simon_massey.bitbucket.org/thinbus/login.png
 */
require 'require.php';

if (! empty($_POST['challenge'])) {
    $result = array();
    /*
     * Look the user up in the database
     */
    $user = R::findOne('user', 'email = :email', array(
        ':email' => $_POST['email']
    ));
    
    if (empty($user)) {
        $result = array(
            'error' => 'No user with such email'
        );
    } else {
        /*
         * If we have a user create a one-time random challenge and store the SRP object in the
         * _SESSION so that we can later check the password proof for the challenge.
         * If you don't want to use _SESSION then you can store this in your DB.
         * Return JSON with the salt the user registered with and the one-time random challenge.
         */
        $srp = new ThinbusSrp($SRP6CryptoParams["N_base10"], $SRP6CryptoParams["g_base10"], $SRP6CryptoParams["k_base16"], $SRP6CryptoParams["H"]);
        
        $B = $srp->step1($_POST['email'], $user->password_salt, $user->password_verifier);
        
        $serial = serialize($srp);
        
        $_SESSION['SRP'] = $serial;
        
        $result = array(
            'salt' => $user->password_salt,
            'b' => $B
        );
    }
    
    echo json_encode($result);
    
    exit();
} elseif (! empty($_POST['password'])) {
    $password = $_POST['password'];
    try {
        /*
         * Password is of the form M1.":".A
         * Use the SRP object to check the password proof.
         * If it is good set the SRP_USER_ID and SRP_SESSION_KEY in the session.
         */
        $serial = $_SESSION['SRP'];
        $srp = unserialize($serial);
        $M1A = explode(':', $password);
        $M1 = $M1A[0];
        $A = $M1A[1];
        
        try {
            $M2 = $srp->step2($A, $M1);
            $key = $srp->getSessionKey();
            $_SESSION['SRP_SESSION_KEY'] = $key;
            $userId = $srp->getUserID();
            $_SESSION['SRP_USER_ID'] = $userId;
            $result = array(
                'M2' => $M2
            );
            unset($_SESSION['SRP']);
            include __DIR__ . '/private.php';
        } catch (Exception $e) {
            $_REQUEST['SRP_ERROR'] = "Authentication failed";
            include __DIR__ . '/signin.php';
        }
    } catch (Exception $e) {
        error_log("got an error trying to process password '".$password."'\n".$e->getMessage(), 0);
        $_REQUEST['SRP_ERROR'] = "Authentication failed";
        include __DIR__ . '/signin.php';
    }
    exit();
}

error_log(__FILE__." got niether a _POST['challenge'] nor a _POST['password'] so ignoring", 0);

exit();



