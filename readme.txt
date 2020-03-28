=== WP Marvelous Debug ===
Contributors: karzin
Tags: debug,debugging,wpconfig,log
Requires at least: 4.4
Tested up to: 5.3
Stable tag: 1.1.0
Requires PHP: 7.0.0
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Edit the debugging constants from wp-config, view the log file on the Dashboard and more debugging features.

== Description ==

This plugin allows viewing the log file (debug.log) on the Dashboard and editing the debugging constants from `wp-config.php`. It also provides other debugging oriented features.

The log file is loaded on the Dashboard **(Tools > Log File)** using the [/SplFileObject](https://www.php.net/manual/pt_BR/class.splfileobject.php) class, optimized for memory usage, and it will use a pagination in order to load only some lines from it.

The plugin will try to enable `WP_DEBUG` and `WP_DEBUG_LOG` to true and disable `WP_DEBUG_DISPLAY` on its first run and will try to do the opposite when it's deactivated.
You can change this behaviour if you want in the settings.

== WP Config Constants ==
The `wp-config.php` is being edited using [wp-cli/wp-config-transformer](https://github.com/wp-cli/wp-config-transformer) library, and these are the edited constants:

* `WP_DEBUG`
* `WP_DEBUG_LOG`
* `WP_DEBUG_DISPLAY`
* `SCRIPT_DEBUG`
* `SAVEQUERIES`

== Generate a reduced log file ==
It's possible to generate a reduced duplicate log file (debug-reduced.log) from the original one loading only the last lines from it (something like the tail command from Linux).

== Installation ==

1. Upload the entire 'wp-marvelous-debug' folder to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Start by visiting plugin settings at Settings > Debugging.

== Changelog ==

= 1.1.0 - 27/03/2020 =
* Add Try catch on `WPConfigTransformer` class
* Add Log Styling options
* Replace WP Config path by file

= 1.0.2 - 19/03/2020 =
* Add 'Erase Log Content' button

= 1.0.1 - 18/03/2020 =
* Improve readme
* Add banner and screenshots

= 1.0.0 - 16/03/2020 =
* Initial Release.

== Upgrade Notice ==

= 1.1.0 =
* Add Try catch on `WPConfigTransformer` class
* Add Log Styling options
* Replace WP Config path by file