<?php
 /**
 * @author      ruslan.zavackiy
 * @since       X.X
 * @version     $Id$
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$root = dirname(dirname(__FILE__));

session_start();

require 'RedBean.php';
require 'thinbus/thinbus-srp-config.php';
require 'thinbus/thinbus-srp.php';

R::setup('sqlite:/tmp/srp_db.txt');