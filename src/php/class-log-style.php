<?php
/**
 * WP Marvelous Debug - Log Style
 *
 * @version 1.1.0
 * @since   1.1.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\WPMD;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\WPMD\Log_Style' ) ) {

	class Log_Style {

		/**
		 * @var Options
		 */
		private $options;

		private $style_classes = array(
			'date'                     => 'wpmd-style-date',
			'php_warning_type'         => 'wpmd-style-php-type',
			'php_warning_type_error'   => 'wpmd-style-php-type-error',
			'php_warning_type_warning' => 'wpmd-style-php-type-warning',
			'php_warning_type_notice'  => 'wpmd-style-php-type-notice',
            'php_paths_and_namespaces' => 'wpmd-style-paths-and-namespaces'
		);

		/**
		 * get_log_style.
		 *
		 * @version 1.1.0
		 * @since   1.1.0
		 *
		 * @return string
		 */
		function get_log_style() {
			ob_start();
			?>
			<style>
				.<?php echo $this->style_classes['date']?> {
					color: #b5b5b5
				}
				.<?php echo $this->style_classes['php_warning_type']?> {
				}
				.<?php echo $this->style_classes['php_warning_type_error']?> {
					color:red;
				}
				.<?php echo $this->style_classes['php_warning_type_warning']?> {
					color: #f58700;
				}
				.<?php echo $this->style_classes['php_warning_type_notice']?> {
					color: #5171ab;
				}
				.<?php echo $this->style_classes['php_paths_and_namespaces']?> {
					font-weight: bold;
				}
			</style>
			<?php
			return ob_get_clean();
		}

		/**
		 * detect_log_styling_parts
		 *
		 * @version 1.1.0
		 * @since   1.1.0
		 *
		 * @param $message
		 *
		 * @return string
		 */
		function detect_log_styling_parts( $message ) {
			if (
				'on' === $this->get_options()->get_option( 'ignore_stack_trace', 'wpmd_log', 'on' ) &&
				preg_match( '/^\#\d/', $message )
			) {
				return $message;
			}

			// Date
			/*if ( 'on' === $this->get_options()->get_option( 'date', 'wpmd_log', 'on' ) ) {
				//$message = preg_replace( '/^\[.+?\]/', '<span class="' . $this->style_classes['date'] . '">$0</span>', $message );
			}*/

			// PHP Warning Type
			//$message = preg_replace( '/PHP\s.+?\:/i', '<span class="' . $this->style_classes['php_warning_type'] . '">$0</span>', $message );

			if ( 'on' === $this->get_options()->get_option( 'php_error_type', 'wpmd_log', 'on' ) ) {
				if ( preg_match( '/PHP\s.*?error\:/i', $message ) ) {
					$message = '<span class="' . $this->style_classes['php_warning_type_error'] . '">' . $message . '</span>';
				}
				if ( preg_match( '/PHP\s.*?warning\:/i', $message ) ) {
					$message = '<span class="' . $this->style_classes['php_warning_type_warning'] . '">' . $message . '</span>';
				}
				if ( preg_match( '/PHP\s.*?notice\:/i', $message ) ) {
					$message = '<span class="' . $this->style_classes['php_warning_type_notice'] . '">' . $message . '</span>';
				}
			}

			if ( 'on' === $this->get_options()->get_option( 'paths_and_namespaces', 'wpmd_log', 'on' ) ) {
				$message = preg_replace( '/(?<=\s).[^\s]*[^\<bbb\&lt\;][\\\\|\/].*?(?=\s)/', '<span class="' . $this->style_classes['php_paths_and_namespaces'] . '">$0</span>', $message );
			}

			return $message;
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