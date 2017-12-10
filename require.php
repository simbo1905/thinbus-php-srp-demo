<?php
 /**
 * @author      ruslan.zavackiy
 * @since       X.X
 * @version     $Id$
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$root = dirname(dirname(__FILE__));

/**
 * This is RedBean just to give a simple database using SqlLite.
 * There is no requirement to use this code I would expect you
 * to use your own existing database accesss logic. If you are testing
 * the demo app on a servder and get permission denied to /tmp then 
 * edit the path to be some other folder writeable to the webserver. 
 */
require 'rb.php';
R::setup('sqlite:/tmp/srp_db.txt');

/**
 * These are the dependencies of Thinbus which are installed with `composer update`.
*/
require_once 'vendor/pear/math_biginteger/Math/BigInteger.php';
require_once 'vendor/paragonie/random_compat/lib/random.php';

/**
 * These two imports are the specfic config paramters and the Thinbus library.
 * The are installed into the `vendor` folder when you run `composer update` to 
 * downlaod all the dependencies named in the `composer.json` file. 
 */
require './vendor/simon_massey/thinbus-php-srp/thinbus/thinbus-srp-config.php';
require './vendor/simon_massey/thinbus-php-srp/thinbus/thinbus-srp.php';
