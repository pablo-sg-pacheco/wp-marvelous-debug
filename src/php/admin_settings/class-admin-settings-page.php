<?php
/**
 * WP Marvelous Debug - Admin Settings Page
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\WPMD\Admin_Settings;

use ThanksToIT\WP_Settings_API\Settings_API;
use ThanksToIT\WPMD\Debug_Log;
use ThanksToIT\WPMD\Options;
use ThanksToIT\WPMD\WP_Config;

if ( ! class_exists( 'ThanksToIT\WPMD\Admin_Settings\Admin_Settings_Page' ) ) {
	class Admin_Settings_Page {

		private $settings_api;

		/**
		 * @var Debug_Log
		 */
		private $debug_log;

		/**
		 * @var WP_Config
		 */
		private $wp_config;

		/**
		 * @var Options
		 */
		private $options;

		/**
		 * Admin_Settings_Page constructor.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function __construct() {
			$this->settings_api = new Settings_API();
			add_action( 'pre_update_option_' . 'wpmd_general', array( $this, 'prevent_saving_wpmd_general_settings' ) );
			add_action( 'admin_notices', array( $this, 'show_invalid_log_notice' ) );
			add_action( 'admin_notices', array( $this, 'show_invalid_wp_config_notice' ) );
			add_action( 'admin_head', array( $this, 'handle_css' ) );
			add_filter( 'wpmd_settings_fields_general', array( $this, 'control_log_content_display' ) );
			add_filter( 'wpmd_settings_fields_general', array( $this, 'control_log_settings_display' ) );
			add_filter( 'wpmd_settings_fields_general', array( $this, 'control_wp_config_settings_display' ) );
		}

		/**
		 * show_invalid_wp_config_notice.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function show_invalid_wp_config_notice() {
			global $pagenow;
			if (
				'options-general.php' != $pagenow ||
				! isset( $_GET['page'] ) ||
				'wpmd_settings' != $_GET['page']
			) {
				return;
			}
			if ( ! $this->get_wp_config()->is_wp_config_path_valid() ) {
				$class   = 'notice notice-error';
				$message = __( '<strong>Error:</strong> Invalid <code>wp-config.php</code> path.', 'wp-marvelous-debug' );
				printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
			} else {
				if ( ! $this->get_wp_config()->is_wp_config_writable() ) {
					$class   = 'notice notice-error';
					$message = __( '<strong>Error:</strong> <code>wp-config.php</code> is not writable.', 'wp-marvelous-debug' );
					printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
				}
			}
		}

		/**
		 * show_invalid_log_path.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function show_invalid_log_notice() {
			global $pagenow;
			if (
				'options-general.php' != $pagenow ||
				! isset( $_GET['page'] ) ||
				'wpmd_settings' != $_GET['page'] ||
				$this->get_debug_log()->is_log_file_valid()
			) {
				return;
			}

			$class   = 'notice notice-error';
			$message = __( '<strong>Error:</strong> Invalid Log file.', 'wp-marvelous-debug' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
		}

		/**
		 * prevent_saving_wpmd_general_settings.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $value
		 *
		 * @return mixed
		 */
		function prevent_saving_wpmd_general_settings( $value ) {
			unset( $value['log_content'] );
			unset( $value['enhanced_log_content'] );
			unset( $value['wp_debug'] );
			unset( $value['wp_debug_log'] );
			unset( $value['wp_debug_display'] );
			unset( $value['script_debug'] );
			unset( $value['savequeries'] );
			return $value;
		}

		/**
		 * admin init.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		public function admin_init() {

			//set the settings
			$this->settings_api->set_sections( $this->get_settings_sections() );
			$this->settings_api->set_fields( $this->get_settings_fields() );

			//initialize settings
			$this->settings_api->admin_init();
		}

		/**
		 * admin menu.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function admin_menu() {
			add_options_page( 'WP Marvelous Debug', 'WP Marvelous Debug', 'delete_posts', 'wpmd_settings', array( $this, 'plugin_page' ) );
		}

		/**
		 * control_log_content_display.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $settings
		 *
		 * @return mixed
		 */
		function control_log_content_display( $settings ) {
			if (
				! $this->get_debug_log()->is_log_file_valid() ||
				'on' !== $this->get_options()->get_option( 'log_content_view', 'wpmd_general', 'no' )
			) {
				$settings = $this->remove_field_by_name( $settings, 'log_content' );
			}
			return $settings;
		}

		/**
		 * control_log_settings_display.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $settings
		 *
		 * @return mixed
		 */
		function control_log_settings_display( $settings ) {
			if ( ! $this->get_debug_log()->is_log_file_valid() ) {
				$settings = $this->remove_field_by_tag( $settings, 'debug.log' );
			}

			if (
				! $this->get_wp_config()->is_wp_config_writable() ||
				! in_array( strtolower( (string) WP_DEBUG_LOG ), array( 'true', '1' ), true ) ||
				! in_array( strtolower( (string) WP_DEBUG ), array( 'true', '1' ), true )
			) {
				$settings = $this->remove_field_by_tag( $settings, 'debug.log' );
				$settings = $this->remove_field_by_tag( $settings, 'debug.log-setting' );
			}

			return $settings;
		}

		/**
		 * control_wp_config_settings_display.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $settings
		 *
		 * @return mixed
		 */
		function control_wp_config_settings_display( $settings ) {
			if (
				! $this->get_wp_config()->is_wp_config_path_valid() ||
				! $this->get_wp_config()->is_wp_config_writable()
			) {
				$settings = $this->remove_field_by_tag( $settings, 'wp-config-constant' );
			}
			return $settings;
		}

		/**
		 * remove_field_by_tag.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $settings
		 * @param $field_name
		 * @param string $section
		 *
		 * @return mixed
		 */
		function remove_field_by_tag( $settings, $field_name, $section = 'wpmd_general' ) {
			$position             = wp_list_filter( $settings[ $section ], array( 'tag' => $field_name ) );
			$settings[ $section ] = array_diff_key( $settings[ $section ], array_flip( array_keys( $position ) ) );
			return $settings;
		}

		/**
		 * remove_field_by_name.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $settings
		 * @param $field_name
		 * @param string $section
		 *
		 * @return mixed
		 */
		function remove_field_by_name( $settings, $field_name, $section = 'wpmd_general' ) {
			$position = wp_list_filter( $settings[ $section ], array( 'name' => $field_name ) );
			reset( $position );
			$first_key = key( $position );
			unset( $settings[ $section ][ $first_key ] );
			return $settings;
		}

		/**
		 * get_settings_sections.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return array
		 */
		function get_settings_sections() {
			$sections = array(
				array(
					'id'    => 'wpmd_general',
					'title' => __( 'General Settings', 'wp-marvelous-debug' )
				),
			);
			return $sections;
		}

		/**
		 * Returns all the settings fields
		 *
		 * @return array settings fields
		 * @throws \Exception
		 */
		function get_settings_fields() {
			$settings_fields = array(
				'wpmd_general' => array(
					array(
						'name'  => 'wp_config_subsection',
						'label' => __( 'WP Config Settings', 'wp-marvelous-debug' ),
						'desc'  => __( 'WP Config settings.', 'wp-marvelous-debug' ),
						'type'  => 'subsection'
					),
					array(
						'name'              => 'wp_config_path',
						'label'             => __( 'Path', 'wp-marvelous-debug' ),
						'desc'              => __( 'Path to your wp-config.php file.', 'wp-marvelous-debug' ) . ' ' . __( 'Probably ', 'wp-marvelous-debug' ) . '<code>' . $this->get_wp_config()->get_default_wp_config_path() . '</code>',
						//'placeholder'       => __( 'Text Input placeholder', 'wp-marvelous-debug' ),
						'type'              => 'text',
						'default'           => $this->get_wp_config()->get_default_wp_config_path(),
						'sanitize_callback' => 'sanitize_text_field'
					),
					array(
						'name'  => 'wp_config_constants_subsection',
						'tag'   => 'wp-config-constant',
						'label' => __( 'WP Config Constants', 'wp-marvelous-debug' ),
						'desc'  => sprintf( __( 'WP Config constants regarding <a href="%s" target="_blank">debugging</a>.', 'wp-marvelous-debug' ), 'https://wordpress.org/support/article/debugging-in-wordpress/' ) . '<br />' . __( 'It\'s necessary to enable at least the <strong>WP_DEBUG</strong> and <strong>WP_DEBUG_LOG</strong> constants to see the rest of the logging options.', 'wp-marvelous-debug' ),
						'type'  => 'subsection'
					),
					array(
						'name'              => 'wp_debug',
						'tag'               => 'wp-config-constant',
						'label'             => __( 'WP_DEBUG', 'wp-marvelous-debug' ),
						'default'           => $this->get_options()->bool_to_string( $this->get_wp_config()->get_variable_value( 'WP_DEBUG' ) ),
						'desc'              => __( 'Enable', 'wp-marvelous-debug' ) . '<p class="description">' . __( 'Triggers the Debug mode in WP, causing PHP errors, notices and warnings to be displayed.', 'wp-marvelous-debug' ) . '</p>',
						'sanitize_callback' => function ( $value ) {
							$this->get_wp_config()->update_variable( 'WP_DEBUG', $this->get_options()->string_to_bool( $value ) );
							return false;
						},
						'type'              => 'checkbox'
					),
					array(
						'name'              => 'wp_debug_log',
						'tag'               => 'wp-config-constant',
						'label'             => __( 'WP_DEBUG_LOG', 'wp-marvelous-debug' ),
						'default'           => $this->get_options()->bool_to_string( $this->get_wp_config()->get_variable_value( 'WP_DEBUG_LOG' ) ),
						'desc'              => __( 'Enable', 'wp-marvelous-debug' ). '<p class="description">' . __( 'Allows errors to be saved to a debug.log file.', 'wp-marvelous-debug' ) . '</p>',
						'sanitize_callback' => function ( $value ) {
							$this->get_wp_config()->update_variable( 'WP_DEBUG_LOG', $this->get_options()->string_to_bool( $value ) );
							return false;
						},
						'type'              => 'checkbox'
					),
					array(
						'name'              => 'wp_debug_display',
						'tag'               => 'wp-config-constant',
						'label'             => __( 'WP_DEBUG_DISPLAY', 'wp-marvelous-debug' ),
						'default'           => $this->get_options()->bool_to_string( $this->get_wp_config()->get_variable_value( 'WP_DEBUG_DISPLAY' ) ),
						'desc'              => __( 'Enable', 'wp-marvelous-debug' ). '<p class="description">' . __( 'Controls whether debug messages are shown inside the HTML of pages or not.', 'wp-marvelous-debug' ) . '</p>',
						'sanitize_callback' => function ( $value ) {
							$this->get_wp_config()->update_variable( 'WP_DEBUG_DISPLAY', $this->get_options()->string_to_bool( $value ) );
							return false;
						},
						'type'              => 'checkbox'
					),
					array(
						'name'              => 'script_debug',
						'tag'               => 'wp-config-constant',
						'label'             => __( 'SCRIPT_DEBUG', 'wp-marvelous-debug' ),
						'default'           => $this->get_options()->bool_to_string( $this->get_wp_config()->get_variable_value( 'SCRIPT_DEBUG' ) ),
						'desc'              => __( 'Enable', 'wp-marvelous-debug' ). '<p class="description">' . __( 'Forces WordPress to use the "dev" versions of CSS and JavaScript files rather than the minified versions.', 'wp-marvelous-debug' ) . '</p>',
						'sanitize_callback' => function ( $value ) {
							$this->get_wp_config()->update_variable( 'SCRIPT_DEBUG', $this->get_options()->string_to_bool( $value ) );
							return false;
						},
						'type'              => 'checkbox'
					),
					array(
						'name'              => 'savequeries',
						'tag'               => 'wp-config-constant',
						'label'             => __( 'SAVEQUERIES', 'wp-marvelous-debug' ),
						'default'           => $this->get_options()->bool_to_string( $this->get_wp_config()->get_variable_value( 'SAVEQUERIES' ) ),
						'desc'              => __( 'Enable', 'wp-marvelous-debug' ). '<p class="description">' . __( 'Saves the database queries to an array allowing to debug those queries.', 'wp-marvelous-debug' ) . '</p>',
						'sanitize_callback' => function ( $value ) {
							$this->get_wp_config()->update_variable( 'SAVEQUERIES', $this->get_options()->string_to_bool( $value ) );
							return false;
						},
						'type'              => 'checkbox'
					),
					array(
						'name'  => 'logs_subsection',
						'tag'   => 'debug.log-setting',
						'label' => __( 'Log Settings', 'wp-marvelous-debug' ),
						'desc'  => __( 'Log settings.', 'wp-marvelous-debug' ),
						'type'  => 'subsection'
					),
					array(
						'name'              => 'log_file',
						'tag'               => 'debug.log-setting',
						'label'             => __( 'Log File', 'wp-marvelous-debug' ),
						'desc'              => __( 'Probably ', 'wp-marvelous-debug' ) . '<code>' . $this->get_debug_log()->get_default_log_file() . '</code>',
						//'placeholder'       => __( 'Text Input placeholder', 'wp-marvelous-debug' ),
						'type'              => 'text',
						'default'           => $this->get_debug_log()->get_default_log_file(),
						'sanitize_callback' => 'sanitize_text_field'
					),
					array(
						'name'  => 'enhanced_log_content_subsection',
						'tag'   => 'debug.log',
						'label' => __( 'Enhanced Log', 'wp-marvelous-debug' ),
						'desc'  => __( 'The log with some legibility improvement or some other enhancement.', 'wp-marvelous-debug' ),
						'type'  => 'subsection'
					),
					array(
						'name'    => 'only_last_n_lines',
						'tag'     => 'debug.log',
						'label'   => __( 'View only last X Line(s)', 'wp-marvelous-debug' ),
						'default' => 5,
						//'desc'  => __( 'Enable', 'wp-marvelous-debug' ),
						'type'    => 'number'
					),
					array(
						'name'              => 'enhanced_log_content',
						'tag'               => 'debug.log',
						'label'             => __( 'Log Content', 'wp-marvelous-debug' ),
						//'desc'              => __( 'Debug.log Content', 'wp-marvelous-debug' ),
						//'placeholder'       => __( 'Text Input placeholder', 'wp-marvelous-debug' ),
						'class'             => 'wpmd-log-content',
						'sanitize_callback' => function () {
							return false;
						},
						'type'              => 'textarea',
						'default'           => $this->get_debug_log()->get_enhanced_log_content()
						//'sanitize_callback' => 'sanitize_text_field'
					),
					array(
						'name'  => 'raw_log_content_subsection',
						'tag'   => 'debug.log',
						'label' => __( 'Raw Log Content', 'wp-marvelous-debug' ),
						'desc'  => __( 'The original log content without any modifications.', 'wp-marvelous-debug' ),
						'type'  => 'subsection'
					),
					array(
						'name'    => 'log_content_view',
						'tag'     => 'debug.log',
						'label'   => __( 'View log Content', 'wp-marvelous-debug' ),
						'default' => 'off',
						'desc'    => __( 'Enable', 'wp-marvelous-debug' ),
						'type'    => 'checkbox'
					),
					array(
						'name'    => 'log_content_edit',
						'tag'     => 'debug.log',
						'label'   => __( 'Edit log Content', 'wp-marvelous-debug' ),
						'default' => 'off',
						'desc'    => __( 'Enable', 'wp-marvelous-debug' ),
						'type'    => 'checkbox'
					),
					array(
						'name'              => 'log_content',
						'tag'               => 'debug.log',
						'label'             => __( 'Log Content', 'wp-marvelous-debug' ),
						'sanitize_callback' => function ( $content ) {
							$this->get_debug_log()->put_log_content( $content );
							return false;
						},
						//'desc'              => __( 'Debug.log Content', 'wp-marvelous-debug' ),
						//'placeholder'       => __( 'Text Input placeholder', 'wp-marvelous-debug' ),
						'class'             => 'wpmd-log-content',
						'type'              => 'textarea',
						'default'           => $this->get_debug_log()->get_log_content(),
						//'sanitize_callback' => 'sanitize_text_field'
					),
				),
			);

			$settings_fields = apply_filters( 'wpmd_settings_fields_general', $settings_fields );
			return $settings_fields;
		}

		/**
		 * plugin_page.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function plugin_page() {
			echo '<div class="wrap">';
			echo '<h1>' . __( 'WP Marvelous Debug', 'remove-special-characters-from-permalinks' ) . '</h1>';
			$this->settings_api->show_navigation();
			$this->settings_api->show_forms();
			echo '</div>';
		}

		/**
		 * Get all the pages.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return array page names with key value pairs
		 */
		function get_pages() {
			$pages         = get_pages();
			$pages_options = array();
			if ( $pages ) {
				foreach ( $pages as $page ) {
					$pages_options[ $page->ID ] = $page->post_title;
				}
			}

			return $pages_options;
		}

		/**
		 * handle_css.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		function handle_css() {
			global $pagenow;
			if (
				'options-general.php' != $pagenow ||
				! isset( $_GET['page'] ) ||
				'wpmd_settings' != $_GET['page']
			) {
				return;
			}
			?>
			<style>
				.wpmd-log-content textarea {
					width: 99%;
				}
			</style>
			<?php
		}

		/**
		 * get_debug_log.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return Debug_Log
		 */
		public function get_debug_log() {
			return $this->debug_log;
		}

		/**
		 * set_debug_log.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param Debug_Log $debug_log
		 */
		public function set_debug_log( $debug_log ) {
			$this->debug_log = $debug_log;
		}

		/**
		 * @return Options
		 */
		public function get_options() {
			return $this->options;
		}

		/**
		 * @param Options $options
		 */
		public function set_options( $options ) {
			$this->options = $options;
		}

		/**
		 * @return WP_Config
		 */
		public function get_wp_config() {
			return $this->wp_config;
		}

		/**
		 * @param WP_Config $wp_config
		 */
		public function set_WP_Config( $wp_config ) {
			$this->wp_config = $wp_config;
		}


	}
}