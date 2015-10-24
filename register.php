<?php
/**
 * @author      ruslan.zavackiy
 * @author      Simon Massey
 * @see         http://simon_massey.bitbucket.org/thinbus/register.png
 */

require 'require.php';

if (!empty($_POST['verifier'])) {
  $user = R::findOne('user', 'email = :email', array(
    ':email' => $_POST['email']
  ));
  
  if (empty($user)) {
    $user = R::dispense('user');
    $user->email = $_POST['email'];
    $user->password_salt = $_POST['salt'];
    $user->password_verifier = $_POST['verifier'];

    $id = R::store($user);
    
    $_REQUEST['SRP_MESSAGE'] = "Congratulations "  . $_POST['email'] . " you have successfully signed up.";
    include __DIR__.'/private.php';
  } else {
      $_REQUEST['SRP_ERROR'] = " User is already registered: " . $_POST['email'];
      include __DIR__.'/signup.php';
  }

}
