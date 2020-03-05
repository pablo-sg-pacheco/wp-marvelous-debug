<?php
/**
 * WP Marvelous Debug - Core
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\WPMD;

use Thanks_To_IT\WP_DICH\DIC;
use Thanks_To_IT\WP_DICH\WP_DICH;
use ThanksToIT\WPMD\Admin_Settings\Admin_Settings_Page;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\WPMD\Core' ) ) {

	class Core {

		/**
		 * @var array
		 */
		public $plugin_info = array();

		/**
		 * @var DIC
		 */
		public static $dic;

		/**
		 * @var WP_DICH
		 */
		public static $dich;

		/**
		 * init.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function init() {
			$this->handle_dependency_injection();
			$this->handle_hooks();
			add_filter( 'plugin_action_links_' . plugin_basename( $this->plugin_info['filesystem_path'] ), array( $this, 'add_settings_link' ) );
			//add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			//add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		/**
		 * enqueue_scripts.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		/*function enqueue_scripts() {
			$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? uniqid() : '.min';
			wp_enqueue_script( 'wpmd-js', $this->get_plugin_url() . 'assets/general' . $suffix . '.js', array(), $version, true );
		}*/

		/**
		 * add_settings_link.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $links
		 *
		 * @return array
		 */
		function add_settings_link( $links ) {
			$links[] = '<a href="' . admin_url( 'options-general.php?page=wpmd_settings' ) . '">' . __( 'Settings', 'wp-marvelous-debug' ) . '</a>';
			return $links;
		}

		/**
		 * handle_dependency_injection.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function handle_dependency_injection() {
			// Dependency Injection Container
			$dic = new DIC();

			// Dependency Injection Container Settings
			$dic[ Options::class ] = $dic->service( function ( DIC $container ) {
				$options = new Options();
				return $options;
			} );
			$dic[ WP_Config::class ] = $dic->service( function ( DIC $container ) {
				$wp_config = new WP_Config();
				$wp_config->set_options( $container[ Options::class ] );
				return $wp_config;
			} );
			$dic[ Debug_Log::class ] = $dic->service( function ( DIC $container ) {
				$debug_log = new Debug_Log();
				$debug_log->set_options( $container[ Options::class ] );
				return $debug_log;
			} );
			$dic[ Admin_Settings_Page::class ] = $dic->service( function ( DIC $container ) {
				$admin_settings = new Admin_Settings_Page();
				$admin_settings->set_debug_log( $container[ Debug_Log::class ] );
				$admin_settings->set_WP_Config( $container[ WP_Config::class ] );
				$admin_settings->set_options( $container[ Options::class ] );
				return $admin_settings;
			} );

			// DICH
			self::$dic  = $dic;
			self::$dich = new WP_DICH( self::$dic );
		}

		/**
		 * handle_hooks.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function handle_hooks() {
			$dich = self::$dich;

			// Admin Settings Page
			$dich->add_action( 'admin_init', array( Admin_Settings_Page::class, 'admin_init' ) );
			$dich->add_action( 'admin_menu', array( Admin_Settings_Page::class, 'admin_menu' ) );
		}

		/**
		 * Gets plugin url.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return string
		 */
		function get_plugin_url() {
			$path = $this->plugin_info['filesystem_path'];
			return plugin_dir_url( $path );
		}

		/**
		 * Gets plugins dir.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return string
		 */
		function get_plugin_dir() {
			$path = $this->plugin_info['filesystem_path'];
			return untrailingslashit( plugin_dir_path( $path ) ) . DIRECTORY_SEPARATOR;;
		}

		/**
		 * setup.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $args
		 */
		function setup( $args ) {
			$args = wp_parse_args( $args, array(
				'version'            => '',
				'filesystem_path'    => '',  // __FILE__
				'languages_path'     => '',
				'languages_rel_path' => '/languages',
				'text_domain'        => ''
			) );
			if ( empty( $args['languages_path'] ) ) {
				$args['languages_path'] = dirname( plugin_basename( $args['filesystem_path'] ) ) . trailingslashit( $args['languages_rel_path'] );
			}
			$this->plugin_info = $args;
		}
	}
}