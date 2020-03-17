<?php
/**
 * WP Marvelous Debug - Admin Settings Page
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\WPMD\Admin;

use ThanksToIT\WP_Settings_API\Settings_API;
use ThanksToIT\WPMD\Log_File;
use ThanksToIT\WPMD\Options;
use ThanksToIT\WPMD\WP_Config;

if ( ! class_exists( 'ThanksToIT\WPMD\Admin\Admin_Settings_Page' ) ) {
	class Admin_Settings_Page {

		private $settings_api;

		/**
		 * @var Log_File
		 */
		private $log_file;

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
			add_action( 'admin_head', array( $this, 'handle_js' ) );
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
				('options-general.php' != $pagenow && 'tools.php' != $pagenow)||
				! isset( $_GET['page'] ) ||
				( 'wpmd_settings' != $_GET['page'] && 'wpmd_log_file' != $_GET['page'] ) ||
				$this->get_log_file()->is_log_file_valid()
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
			add_options_page( __( 'WP Marvelous Debug', 'wp-marvelous-debug' ), __( 'Debugging', 'wp-marvelous-debug' ), 'delete_posts', 'wpmd_settings', array( $this, 'plugin_page' ) );
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
				array(
					'id'    => 'wpmd_log',
					'title' => __( 'Log Settings', 'wp-marvelous-debug' )
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
						'name'  => 'general_subsection',
						'label' => __( 'General Settings', 'wp-marvelous-debug' ),
						'desc'  => __( 'General settings.', 'wp-marvelous-debug' ),
						'type'  => 'subsection'
					),
					array(
						'name'              => 'wp_config_path',
						'label'             => __( 'Wp Config Path', 'wp-marvelous-debug' ),
						'desc'              => __( 'Path to your wp-config.php file.', 'wp-marvelous-debug' ) . ' ' . __( 'Probably ', 'wp-marvelous-debug' ) . '<code>' . $this->get_wp_config()->get_default_wp_config_path() . '</code>',
						//'placeholder'       => __( 'Text Input placeholder', 'wp-marvelous-debug' ),
						'type'              => 'text',
						'default'           => $this->get_wp_config()->get_default_wp_config_path(),
						'sanitize_callback' => 'sanitize_text_field'
					),
					array(
						'name'              => 'enable_debug_on_plugin_activation',
						'label'             => __( 'Enable Debug on Activation', 'wp-marvelous-debug' ),
						'default'           => 'on',
						'desc'              => __( 'Enable', 'wp-marvelous-debug' ). '<p class="description">' . __( 'Tries to enable WP_DEBUG and WP_DEBUG_LOG and disable WP_DEBUG_DISPLAY when WP Marvelous Debug plugin is enabled.', 'wp-marvelous-debug' ) . '</p>',
						'type'              => 'checkbox'
					),
					array(
						'name'              => 'disable_debug_on_plugin_deactivation',
						'label'             => __( 'Disable Debug on Deactivation', 'wp-marvelous-debug' ),
						'default'           => 'on',
						'desc'              => __( 'Enable', 'wp-marvelous-debug' ). '<p class="description">' . __( 'Tries to disable WP_DEBUG and WP_DEBUG_LOG and enable WP_DEBUG_DISPLAY when WP Marvelous Debug plugin is disabled.', 'wp-marvelous-debug' ) . '</p>',
						'type'              => 'checkbox'
					),
					array(
						'name'  => 'wp_config_constants_subsection',
						'tag'   => 'wp-config-constant',
						'label' => __( 'WP Config Constants', 'wp-marvelous-debug' ),
						'desc'  => sprintf( __( 'WP Config constants regarding <a href="%s" target="_blank">debugging</a>.', 'wp-marvelous-debug' ), 'https://wordpress.org/support/article/debugging-in-wordpress/' ) . '<br />' . sprintf( __( 'It\'s necessary to enable at least the <strong>WP_DEBUG</strong> and <strong>WP_DEBUG_LOG</strong> constants to generate the <a href="%s">log file</a>.', 'wp-marvelous-debug' ), admin_url( 'tools.php?page=wpmd_log_file' ) ),
						'type'  => 'subsection'
					),
					array(
						'name'              => 'wp_debug',
						'class'             => 'padding-v1',
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
						'class'             => 'padding-v1',
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
						'class'             => 'padding-v1',
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
						'class'             => 'padding-v1',
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
						'class'             => 'padding-v1',
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
				),
				'wpmd_log' => array(
					array(
						'name'  => 'logs_subsection',
						'tag'   => 'debug.log-setting',
						'label' => __( 'Log Settings', 'wp-marvelous-debug' ),
						'desc'  => sprintf( __( 'Log settings that will be used to display the <a href="%s">log file</a>.', 'wp-marvelous-debug' ), admin_url( 'tools.php?page=wpmd_log_file' ) ),
						'type'  => 'subsection'
					),
					array(
						'name'              => 'log_file',
						'tag'               => 'debug.log-setting',
						'label'             => __( 'Log File', 'wp-marvelous-debug' ),
						'desc'              => __( 'Probably ', 'wp-marvelous-debug' ) . '<code>' . $this->get_log_file()->get_default_log_file() . '</code>',
						//'placeholder'       => __( 'Text Input placeholder', 'wp-marvelous-debug' ),
						'type'              => 'text',
						'default'           => $this->get_log_file()->get_default_log_file(),
						'sanitize_callback' => 'sanitize_text_field'
					),
					array(
						'name'              => 'ignore_last_line',
						'tag'               => 'debug.log-setting',
						'label'             => __( 'Ignore Last Line', 'wp-marvelous-debug' ),
						'desc'              => __( 'Enable', 'wp-marvelous-debug' ) . '<p class="description">' . __( 'Ignores last line from log file as almost always it is an empty line.', 'wp-marvelous-debug' ) . '</p>',
						//'placeholder'       => __( 'Text Input placeholder', 'wp-marvelous-debug' ),
						'type'              => 'checkbox',
						'default'           => 'on',
						'sanitize_callback' => 'sanitize_text_field'
					),
					array(
						'name'              => 'reverse_chronological_order',
						'tag'               => 'debug.log-setting',
						'label'             => __( 'Reverse Chronological Order', 'wp-marvelous-debug' ),
						'desc'              => __( 'Enable', 'wp-marvelous-debug' ) . '<p class="description">' . __( 'Most recent first.', 'wp-marvelous-debug' ) . '</p>',
						//'placeholder'       => __( 'Text Input placeholder', 'wp-marvelous-debug' ),
						'type'              => 'checkbox',
						'default'           => 'on',
						'sanitize_callback' => 'sanitize_text_field'
					),
					array(
						'name'              => 'redirect_to_last_page',
						'tag'               => 'debug.log-setting',
						'label'             => __( 'Redirect to Last Page', 'wp-marvelous-debug' ),
						'desc'              => __( 'Enable', 'wp-marvelous-debug' ) . '<p class="description">' . __( 'Displays the end of the log file by default. Only makes sense when <strong>Reverse Chronological Order</strong> is disabled', 'wp-marvelous-debug' ) . '</p>',
						//'placeholder'       => __( 'Text Input placeholder', 'wp-marvelous-debug' ),
						'type'              => 'checkbox',
						'default'           => 'off',
						'sanitize_callback' => 'sanitize_text_field'
					),
					array(
						'name'              => 'generate_reduced_log',
						'tag'               => 'debug.log-setting',
						'label'             => __( 'Generate Reduced Log', 'wp-marvelous-debug' ),
						'desc'              => __( 'Generates a reduced duplicated log file <strong>(debug-reduced.log)</strong> from the original log file on the same directory.', 'wp-marvelous-debug' ) . '<br />' . __( 'Remember to disable it when you are not debugging anymore.', 'wp-marvelous-debug' ),
						'type'              => 'subsection'
					),
					array(
						'name'              => 'generate_reduced_log_enable',
						'tag'               => 'debug.log-setting',
						'label'             => __( 'Enable', 'wp-marvelous-debug' ),
						'desc'              => __( 'Enable', 'wp-marvelous-debug' ),
						//'placeholder'       => __( 'Text Input placeholder', 'wp-marvelous-debug' ),
						'type'              => 'checkbox',
						'default'           => 'off',
						'sanitize_callback' => 'sanitize_text_field'
					),
					array(
						'name'              => 'last_x_log_lines',
						'tag'               => 'debug.log',
						'label'             => __( 'Number of Last Lines', 'wp-marvelous-debug' ),
						'desc'              => sprintf( __( 'Gets the last %d lines from the original log file to generate the reduced log file.', 'wp-marvelous-debug' ), $this->get_options()->get_option( 'last_x_log_lines', 'wpmd_log', 50 ) ),
						//'placeholder'     => __( 'Text Input placeholder', 'wp-marvelous-debug' ),
						//'class'           => 'wpmd-log-content',
						'type'              => 'number',
						'default'           => 50
					),
					array(
						'name'              => 'generate_reduced_log_on_admin',
						'tag'               => 'debug.log-setting',
						'label'             => __( 'Generate on Admin', 'wp-marvelous-debug' ),
						'desc'              => __( 'Enable', 'wp-marvelous-debug' ) . '<p class="description">' . __( 'Generates the reduced log file on the dashboard.', 'wp-marvelous-debug' ) . '</p>',
						'type'              => 'checkbox',
						'default'           => 'on',
						'sanitize_callback' => 'sanitize_text_field'
					),
					array(
						'name'              => 'generate_reduced_log_on_frontend',
						'tag'               => 'debug.log-setting',
						'label'             => __( 'Generate on Frontend', 'wp-marvelous-debug' ),
						'desc'              => __( 'Enable', 'wp-marvelous-debug' ) . '<p class="description">' . __( 'Generates the reduced log file on the frontend.', 'wp-marvelous-debug' ) . '</p>',
						'type'              => 'checkbox',
						'default'           => 'off',
						'sanitize_callback' => 'sanitize_text_field'
					),
				)
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
			echo '<h1>' . __( 'Debugging Settings', 'remove-special-characters-from-permalinks' ) . '</h1>';
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

		function handle_js() {
			global $pagenow;
			if (
				'options-general.php' != $pagenow ||
				! isset( $_GET['page'] ) ||
				'wpmd_settings' != $_GET['page']
			) {
				return;
			}
			?>
			<script>
				<?php if ( ! $this->get_wp_config()->is_wp_config_path_valid() || ! $this->get_wp_config()->is_wp_config_writable()) { ?>
				jQuery(document).ready(function () {
					jQuery('.wp_config_path').addClass('invalid-input');
				})
				<?php } ?>
				<?php if ( ! $this->get_log_file()->is_log_file_valid()) { ?>
				jQuery(document).ready(function () {
					jQuery('.log_file').addClass('invalid-input');
				})
				<?php } ?>
			</script>
			<?php
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
				.form-table th label{
					display:inline-block;
					vertical-align: top;
				}
				.invalid-input input[type="text"]{
					border:1px solid red;
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
		 * @return Log_File
		 */
		public function get_log_file() {
			return $this->log_file;
		}

		/**
		 * set_debug_log.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param Log_File $log_file
		 */
		public function set_log_file( $log_file ) {
			$this->log_file = $log_file;
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
		public function set_wp_config( $wp_config ) {
			$this->wp_config = $wp_config;
		}




	}
}