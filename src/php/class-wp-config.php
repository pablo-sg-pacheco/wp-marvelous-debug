<?php
/**
 * WP Marvelous Debug - WP_Config
 *
 * @version 1.0.0
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
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return string
		 */
		function get_default_wp_config_path() {
			return ABSPATH;
		}

		/**
		 * get_wp_config_file.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return string
		 */
		function get_wp_config_file() {
			return trailingslashit( $this->get_options()->get_option( 'wp_config_path', 'wpmd_general', $this->get_default_wp_config_path() ) ) . 'wp-config.php';
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
				$wp_filesystem->exists( $this->get_wp_config_file() )
			) {
				return true;
			}
			return false;
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
			$config_transformer = new \WPConfigTransformer( $this->get_wp_config_file() );
			if ( ! $config_transformer->exists( 'constant', $var_name ) ) {
				return $default;
			} else {
				return $config_transformer->get_value( 'constant', $var_name );
			}
		}

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

		function update_variable( $var_name, $value ) {
			$config_transformer = new \WPConfigTransformer( $this->get_wp_config_file() );
			return $config_transformer->update( 'constant', $var_name, $this->maybe_bool_to_string( $value ), $config_args = [
				'raw'       => true,
				'normalize' => true,
			] );
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