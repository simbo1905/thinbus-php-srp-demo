<?php
/**
 * WARNING Do not use this code in production. I would expect that you already
 * have code to manage users in the database. All this shows is saving the
 * email, salt and verifier in a database. You should use your own code
 * to do that or consider using an opensource content management system
 * that has its own features about managing users in a database.
 *
 * @see         http://simon_massey.bitbucket.org/thinbus/register.png
 *
 * @author      ruslan.zavackiy
 * @author      Simon Massey
 */
require 'require.php';

$result = array();

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
    
    $result = array('message' => 'User created with id '.$id);
  } else {
    $result = array('error' => 'User with email <b>' . htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') . '</b> already exists');
  }
} else {
    $result = array('error' => 'No verifier found in post. ');
}

echo json_encode($result);

exit();
