<?php
/**
 * WP Marvelous Debug - Debug_Log
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\WPMD;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\WPMD\Debug_Log' ) ) {

	class Debug_Log {

		/**
		 * @var string
		 */
		private $log_path = '';

		/**
		 * @var Options
		 */
		private $options;

		/**
		 * get_default_log_path.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return bool|string
		 */
		function get_default_log_file() {
			if ( in_array( strtolower( (string) WP_DEBUG_LOG ), array( 'true', '1' ), true ) ) {
				return WP_CONTENT_DIR . '/debug.log';
			} elseif ( is_string( WP_DEBUG_LOG ) ) {
				return WP_DEBUG_LOG;
			} else {
				return false;
			}
		}

		/**
		 * get_enhanced_log_content.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return string
		 */
		function get_enhanced_log_content() {
			return $this->get_last_n_lines( $this->get_options()->get_option( 'only_last_n_lines', 'wpmd_general', 5 ) );
		}

		/**
		 * get_filesize.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return bool|float
		 */
		function get_filesize() {
			if ( $this->is_log_file_valid() ) {
				return round( filesize( $this->get_log_file() ) / 1024 / 1024, 2 );
			}
			return false;
		}

		/**
		 * get_last_n_lines.
		 *
		 * @see https://stackoverflow.com/a/1062737/1193038
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param int $lines_count
		 *
		 * @return string
		 */
		function get_last_n_lines( $lines_count = 10 ) {
			if ( ! $this->is_log_file_valid() ) {
				return '';
			}
			$file        = $this->get_log_file();
			$handle      = fopen( $file, "r" );
			$linecounter = $lines_count;
			$pos         = - 2;
			$beginning   = false;
			$text        = array();
			while ( $linecounter > 0 ) {
				$t = " ";
				while ( $t != "\n" ) {
					if ( fseek( $handle, $pos, SEEK_END ) == - 1 ) {
						$beginning = true;
						break;
					}
					$t = fgetc( $handle );
					$pos --;
				}
				$linecounter --;
				if ( $beginning ) {
					rewind( $handle );
				}
				$text[ $lines_count - $linecounter - 1 ] = fgets( $handle );
				if ( $beginning ) {
					break;
				}
			}
			fclose( $handle );
			$lines = array_reverse( $text );
			return implode( "", $lines );
		}

		/**
		 * get_log_file.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @see wp_debug_mode();
		 *
		 * @return bool|string
		 */
		function get_log_file() {
			if ( empty( $this->log_path ) ) {
				if ( $log_path = $this->get_options()->get_option( 'log_file', 'wpmd_general', $this->get_default_log_file() ) ) {
					$this->log_path = $log_path;
				}
			}
			return $this->log_path;
		}

		/**
		 * get_log_content.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @see https://wordpress.stackexchange.com/a/306765/25264
		 *
		 * @return mixed
		 */
		function get_log_content() {
			WP_Filesystem();
			global $wp_filesystem;
			$txt_file = '';
			if ( $wp_filesystem && $wp_filesystem->exists( $this->get_log_file() ) ) {
				$txt_file = $wp_filesystem->get_contents( $this->get_log_file() );
			}
			return $txt_file;
		}

		/**
		 * is_log_path_valid.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return bool
		 */
		function is_log_file_valid() {
			WP_Filesystem();
			global $wp_filesystem;
			if (
				$wp_filesystem &&
				$wp_filesystem->exists( $this->get_log_file() ) &&
				( $pathinfo = pathinfo( $this->get_log_file() ) ) &&
				( 'txt' === $pathinfo['extension'] || 'log' === $pathinfo['extension'] )
			) {
				return true;
			}
			return false;
		}

		/**
		 * put_log_content.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $content
		 *
		 * @return string
		 */
		function put_log_content( $content ) {
			WP_Filesystem();
			global $wp_filesystem;
			$txt_file = '';
			if ( $wp_filesystem && $wp_filesystem->exists( $this->get_log_file() ) ) {
				$txt_file = $wp_filesystem->put_contents( $this->get_log_file(), $content );
			}
			return $txt_file;
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