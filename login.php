<?php
/**
 * @author      ruslan.zavackiy
 */
require 'lib/require.php';

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
        
        $N_base10str = "19502997308733555461855666625958719160994364695757801883048536560804281608617712589335141535572898798222757219122180598766018632900275026915053180353164617230434226106273953899391119864257302295174320915476500215995601482640160424279800690785793808960633891416021244925484141974964367107";
        $g_base10str = "2";
        
        $srp = new Srp($N_base10str, $g_base10str);
        
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
        $result = array(
            'M2' => $M2
        );
    } catch (Exception $e) {
        $result = array(
            'error' => 'Authentication failed',
            'debug' => array(
                'post' => $_POST,
                'session' => $_SESSION
            )
        );
    }

}

echo json_encode($result);

exit();