=== WP Marvelous Debug ===
Contributors: karzin
Tags: debug,debugging,wpconfig,log
Requires at least: 4.4
Tested up to: 5.3
Stable tag: 1.0.0
Requires PHP: 7.0.0
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Edit the debugging constants from wp-config, view the log file on the Dashboard and more debugging features.

== Description ==

This plugin allows viewing the log file (debug.log) on the Dashboard and editing the debugging constants from `wp-config.php`. It also provides other debugging oriented features.

The log file is loaded on the Dashboard using the `/SplFileObject class`, optimized for memory usage, and it will use a pagination to load only some lines from it.

The `wp-config.php` constants that can be edited from the Dashboard are:
* `WP_DEBUG`
* `WP_DEBUG_LOG`
* `WP_DEBUG_DISPLAY`
* `SCRIPT_DEBUG`
* `SAVEQUERIES`

It's also possible to generate a reduced duplicated log file (debug-reduced.log) from the original one loading only the last files from it. Something like the tail command from Linux.

== Installation ==

1. Upload the entire 'wp-marvelous-debug' folder to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Start by visiting plugin settings at Settings > Debugging.

== Changelog ==

= 1.0.0 - 16/03/2020 =
* Initial Release.

== Upgrade Notice ==

= 1.0.0 =