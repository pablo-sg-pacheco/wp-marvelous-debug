<?php
/**
 * WP Marvelous Debug - WP_Config
 *
 * @version 1.1.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\WPMD;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\WPMD\WP_Config' ) ) {

	class WP_Config {

		/**
		 * @var Options
		 */
		private $options;

		/**
		 * get_default_wp_config_path.
		 *
		 * @version 1.1.0
		 * @since   1.0.0
		 *
		 * @return string
		 */
		function get_default_wp_config_path() {
			return trailingslashit( ABSPATH ) . 'wp-config.php';
		}

		/**
		 * try_to_enable_debug_on_activation.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @throws \Exception
		 */
		function try_to_enable_debug_on_activation() {
			if ( 'on' !== $this->get_options()->get_option( 'enable_debug_on_plugin_activation', 'wpmd_general', 'on' ) ) {
				return;
			}
			$this->update_variable( 'WP_DEBUG', true );
			$this->update_variable( 'WP_DEBUG_LOG', true );
			$this->update_variable( 'WP_DEBUG_DISPLAY', false );
		}

		/**
		 * try_to_disable_debug_on_deactivation.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @throws \Exception
		 */
		function try_to_disable_debug_on_deactivation() {
			if ( 'on' !== $this->get_options()->get_option( 'disable_debug_on_plugin_deactivation', 'wpmd_general', 'on' ) ) {
				return;
			}
			$this->update_variable( 'WP_DEBUG', false );
			$this->update_variable( 'WP_DEBUG_LOG', false );
			$this->update_variable( 'WP_DEBUG_DISPLAY', true );
		}

		/**
		 * get_wp_config_file.
		 *
		 * @version 1.1.0
		 * @since   1.0.0
		 *
		 * @return string
		 */
		function get_wp_config_file() {
			return $this->get_options()->get_option( 'wp_config_file', 'wpmd_general', $this->get_default_wp_config_path() );
			//return trailingslashit( $this->get_options()->get_option( 'wp_config_file', 'wpmd_general', $this->get_default_wp_config_path() ) ) . 'wp-config.php';
		}

		/**
		 * is_wp_config_file_name_valid.
		 *
		 * @version 1.1.0
		 * @since   1.1.0
		 *
		 * @return bool
		 */
		private function is_wp_config_file_name_valid() {
			$file          = $this->get_wp_config_file();
			$log_file_only = basename( parse_url( $file, PHP_URL_PATH ) );
			if ( $log_file_only != 'wp-config.php' ) {
				return false;
			}
			return true;
		}

		/**
		 * is_wp_config_path_valid.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return bool
		 */
		function is_wp_config_path_valid() {
			WP_Filesystem();
			global $wp_filesystem;
			if (
				$wp_filesystem &&
				$this->is_wp_config_file_name_valid() &&
				$wp_filesystem->exists( $this->get_wp_config_file() )
			) {
				return true;
			}
			return false;
		}

		/**
		 * is_wp_config_writable.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return mixed
		 */
		function is_wp_config_writable() {
			WP_Filesystem();
			global $wp_filesystem;
			return $wp_filesystem->is_writable( $this->get_wp_config_file() );
		}

		/**
		 * get_variable_value.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $var_name
		 * @param string $default
		 *
		 * @return array|bool|string
		 * @throws \Exception
		 */
		function get_variable_value( $var_name, $default = false ) {
			if ( ! $this->is_wp_config_writable() ) {
				return false;
			}
			$config_transformer = new \WPConfigTransformer( $this->get_wp_config_file() );
			if ( ! $config_transformer->exists( 'constant', $var_name ) ) {
				return $default;
			} else {
				return $config_transformer->get_value( 'constant', $var_name );
			}
		}

		/**
		 * maybe_bool_to_string.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $value
		 *
		 * @return string
		 */
		function maybe_bool_to_string( $value ) {
			if ( ! is_string( $value ) ) {
				if ( filter_var( $value, FILTER_VALIDATE_BOOLEAN ) ) {
					return 'true';
				} else {
					return 'false';
				}
			}
			return $value;
		}

		/**
		 * update_variable.
		 *
		 * @version 1.1.0
		 * @since   1.0.0
		 *
		 * @param $var_name
		 * @param $value
		 *
		 * @return bool
		 * @throws \Exception
		 */
		function update_variable( $var_name, $value ) {
			if ( ! $this->is_wp_config_writable() ) {
				return false;
			}
			$config_transformer = new \WPConfigTransformer( $this->get_wp_config_file() );
			$response           = false;
			try {
				$response = $config_transformer->update( 'constant', $var_name, $this->maybe_bool_to_string( $value ), $config_args = [
					'raw'       => true,
					'normalize' => true,
				] );
			} catch ( \Exception $e ) {
				$response = false;
			}
			return $response;
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

	}
}