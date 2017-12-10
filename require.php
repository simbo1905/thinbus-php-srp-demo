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
 * to use your own existing database accesss logic.
 */
require 'rb.php';
R::setup('sqlite:/tmp/srp_db.txt');

/**
 * These two imports are the specfic config paramters and the Thinbus library.
 */
require 'thinbus/thinbus-srp-config.php';
require 'thinbus/thinbus-srp.php';
