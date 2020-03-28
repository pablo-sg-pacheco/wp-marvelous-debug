<?php
/**
 * WP Marvelous Debug - Core
 *
 * @version 1.0.2
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\WPMD;

use Thanks_To_IT\WP_DICH\DIC;
use Thanks_To_IT\WP_DICH\WP_DICH;
use ThanksToIT\WPMD\Admin\Admin_Settings_Page;
use ThanksToIT\WPMD\Admin\Log_File_Tools_Page;
use ThanksToIT\WPMD\Admin\Log_List;

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
			add_filter( 'plugin_action_links_' . plugin_basename( $this->plugin_info['filesystem_path'] ), array( $this, 'add_action_links' ) );
			add_action( 'init', array( $this, 'handle_localization' ) );
		}

		/**
		 * handle_localization.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function handle_localization(){
			$domain = $this->plugin_info['text_domain'];
			load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->plugin_info['filesystem_path'] ) ) . trailingslashit( 'languages' ) );
		}

		/**
		 * add_action_links.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $links
		 *
		 * @return array
		 */
		function add_action_links( $links ) {
			$links[] = '<a href="' . admin_url( 'options-general.php?page=wpmd_settings' ) . '">' . __( 'Settings', 'wp-marvelous-debug' ) . '</a>';
			$links[] = '<a href="' . admin_url( 'tools.php?page=wpmd_log_file' ) . '">' . __( 'Log File', 'wp-marvelous-debug' ) . '</a>';
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
			$dic[ Log_File::class ] = $dic->service( function ( DIC $container ) {
				$debug_log = new Log_File();
				$debug_log->set_options( $container[ Options::class ] );
				return $debug_log;
			} );
			$dic[ Admin_Settings_Page::class ] = $dic->service( function ( DIC $container ) {
				$admin_settings = new Admin_Settings_Page();
				$admin_settings->set_log_file( $container[ Log_File::class ] );
				$admin_settings->set_wp_config( $container[ WP_Config::class ] );
				$admin_settings->set_options( $container[ Options::class ] );
				return $admin_settings;
			} );
			$dic[ Log_File_Tools_Page::class ] = $dic->service( function ( DIC $container ) {
				$tools_page = new Log_File_Tools_Page();
				$tools_page->set_log_file( $container[ Log_File::class ] );
				$tools_page->set_log_list( $container[ Log_List::class ] );
				$tools_page->set_log_style( $container[ Log_Style::class ] );
				$tools_page->set_options( $container[ Options::class ] );
				return $tools_page;
			} );
			$dic[ Log_List::class ] = $dic->service( function ( DIC $container ) {
				$log_list = new Log_List();
				$log_list->set_log_file( $container[ Log_File::class ] );
				$log_list->set_options( $container[ Options::class ] );
				return $log_list;
			} );
			$dic[ Log_Style::class ] = $dic->service( function ( DIC $container ) {
				$log_style = new Log_Style();
				$log_style->set_options( $container[ Options::class ] );
				return $log_style;
			} );

			// DICH
			self::$dic  = $dic;
			self::$dich = new WP_DICH( self::$dic );
		}

		/**
		 * handle_hooks.
		 *
		 * @version 1.0.2
		 * @since   1.0.0
		 */
		function handle_hooks() {
			$dich = self::$dich;

			// WP Config
			$dich->add_action( 'wpmd_activation_hook', array( WP_Config::class, 'try_to_enable_debug_on_activation' ) );
			$dich->add_action( 'wpmd_deactivation_hook', array( WP_Config::class, 'try_to_disable_debug_on_deactivation' ) );

			// Admin Settings Page
			$dich->add_action( 'admin_init', array( Admin_Settings_Page::class, 'admin_init' ) );
			$dich->add_action( 'admin_menu', array( Admin_Settings_Page::class, 'admin_menu' ) );

			// Log Style
			$dich->add_filter( 'wpmd_pre_format_message', array( Log_Style::class, 'detect_log_styling_parts' ),10 );

			// Tools Page
			$dich->add_action( 'admin_menu', array( Log_File_Tools_Page::class, 'erase_log_content' ) );
			$dich->add_action( 'admin_notices', array( Log_File_Tools_Page::class, 'show_erase_log_notice' ), 10 );
			$dich->add_action( 'admin_head', array( Log_File_Tools_Page::class, 'handle_css' ) );
			$dich->add_action( 'admin_menu', array( Log_File_Tools_Page::class, 'admin_menu' ) );
			$dich->add_action( 'current_screen', array( Log_File_Tools_Page::class, 'redirect_to_last_page' ) );
			if ( ! empty( $_GET['page'] ) && 'wpmd_log_file' === $_GET['page'] ) {
				$GLOBALS['hook_suffix'] = 'wpmd';
				$dich->add_filter( 'set-screen-option', array( Log_File_Tools_Page::class, 'set_screen' ), 10, 3 );
			}

			// Log File
			$dich->add_action( 'wp_footer', array( Log_File::class, 'generate_reduced_log_file' ) );
			$dich->add_action( 'admin_init', array( Log_File::class, 'generate_reduced_log_file' ) );
			//$dich->add_action( 'admin_init', array( Log_File::class, 'search' ) );

			// Activation and deactivation hooks
			register_activation_hook( $this->plugin_info['filesystem_path'], function () {
				do_action( 'wpmd_activation_hook' );
			} );
			register_deactivation_hook( $this->plugin_info['filesystem_path'], function () {
				do_action( 'wpmd_deactivation_hook' );
			} );
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