<?php
/**
 * Plugin Name: WP Marvelous Debug
 * Description: View WordPress debug.log on your browser's console
 * Version: 1.0.0
 * Author: Thanks to IT
 * Author URI: https://github.com/wp-marvelous-debug
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wp-marvelous-debug
 * Domain Path: /src/languages
 */

require "vendor/autoload.php";

$core = new \ThanksToIT\WPMD\Core();
$core->setup( array(
	'filesystem_path' => __FILE__,
) );
//error_log(random_int(1,999));
$core->init();