<?php
/**
 * @author      ruslan.zavackiy
 */
require 'require.php';

$result = array();

if (! empty($_POST['challenge'])) {
    $user = R::findOne('user', 'email = :email', array(
        ':email' => $_POST['email']
    ));
    
    if (empty($user)) {
        $result = array(
            'error' => 'No user with such email'
        );
    } else {
        $srp = new ThinbusSrp($SRP6CryptoParams["N_base10"], $SRP6CryptoParams["g_base10"], $SRP6CryptoParams["k_base16"], $SRP6CryptoParams["H"]);
        
        $B = $srp->step1($_POST['email'], $user->password_salt, $user->password_verifier);
        
        $serial = serialize($srp);
        
        $_SESSION['SRP'] = $serial;
        
        $result = array(
            'salt' => $user->password_salt,
            'b' => $B
        );
    }
} elseif (! empty($_POST['M1'])) {
    
    $serial = $_SESSION['SRP'];
    $srp = unserialize($serial);
    $M1 = $_POST['M1'];
    $A = $_POST['A'];
    
    try {
        $M2 = $srp->step2($A, $M1);
        $key = $srp->getSessionKey();
        $_SESSION['SRP_SESSION_KEY'] = $key;
        unset($_SESSION['SRP']);
        $result = array(
            'M2' => $M2
        );
    } catch (Exception $e) {
        $result = array(
            'error' => 'Authentication failed',
            'debug' => array(
                'post' => $_POST
            )
        );
    }

}

echo json_encode($result);

exit();