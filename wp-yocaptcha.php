<?php
/*
Plugin Name: WP-yoCAPTCHA
Plugin URI: http://login.yocaptcha.com
Description: Integrates yoCAPTCHA anti-spam solutions with wordpress
Version: 1.0
Author: Neeraj Agarwal
Email: neeraj@innovese.com
Author URI: http://www.yocaptcha.com
*/

// this is the 'driver' file that instantiates the objects and registers every hook

define('ALLOW_INCLUDE', true);

require_once('yocaptcha.php');


$yocaptcha = new yoCAPTCHA('yocaptcha_options');

?>