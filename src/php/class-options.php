<?php
/**
 * WP Marvelous Debug - Options
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\WPMD;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\WPMD\Options' ) ) {

	class Options {

		protected $section = array();

		/**
		 * Get the value of a settings field.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param string $option settings field name
		 * @param string $section the section name this field belongs to
		 * @param string $default default text if it's not found
		 *
		 * @return string
		 */
		function get_option( $option, $section, $default = '' ) {

			if ( empty( $this->section ) ) {
				$options = get_option( $section );
			} else {
				$options = $this->section;
			}

			if ( isset( $options[ $option ] ) ) {
				return $options[ $option ];
			}

			return $default;
		}

		/**
		 * convert_boolean_to_string.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $value
		 *
		 * @return string
		 */
		function bool_to_string( $value ) {
			if ( filter_var( $value, FILTER_VALIDATE_BOOLEAN ) ) {
				return 'on';
			} else {
				return 'off';
			}
		}

		/**
		 * string_to_bool.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $value
		 *
		 * @return string
		 */
		function string_to_bool( $value ) {
			return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
		}
	}
}