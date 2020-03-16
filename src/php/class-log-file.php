<?php
/**
 * WP Marvelous Debug - Log File
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\WPMD;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\WPMD\Log_File' ) ) {

	class Log_File {

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
			return WP_CONTENT_DIR . '/debug.log';
			/*if ( in_array( strtolower( (string) WP_DEBUG_LOG ), array( 'true', '1' ), true ) ) {
				return WP_CONTENT_DIR . '/debug.log';
			} elseif ( is_string( WP_DEBUG_LOG ) ) {
				return WP_DEBUG_LOG;
			} else {
				return false;
			}*/
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
		 * get_lines_amount.
		 *
		 * @todo Get lines amount by OS, using exec('') and checking if it's windows or linux
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @see https://stackoverflow.com/a/12997081/1193038
		 * @see https://stackoverflow.com/a/15466343/1193038
		 *
		 * @return bool|int
		 */
		function get_total_lines_amount() {
			if ( ! $this->is_log_file_valid() ) {
				return false;
			}
			$file = new \SplFileObject( $this->get_log_file(), 'r' );
			$file->setFlags( \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE );
			$file->seek( PHP_INT_MAX );
			if ( 'on' === $this->get_options()->get_option( 'ignore_last_line', 'wpmd_log', 'on' ) ) {
				return $file->key();
			} else {
				return $file->key() + 1;
			}
		}

		/**
		 * get_log_file_directory.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return string
		 */
		function get_log_file_directory() {
			$log_file      = $this->get_log_file();
			$log_file_only = basename( parse_url( $log_file, PHP_URL_PATH ) );
			return trailingslashit( str_replace( $log_file_only, '', $log_file ) );
		}

		/**
		 * generate_reduced_log_file.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function generate_reduced_log_file() {
			if (
				'on' !== $this->get_options()->get_option( 'generate_reduced_log_enable', 'wpmd_log', 'off' ) ||
				! $this->is_log_file_valid()
			) {
				return false;
			}
			if (
				( 'on' === ( $generate_on_admin = $this->get_options()->get_option( 'generate_reduced_log_on_admin', 'wpmd_log', 'on' ) ) && 'admin_init' === current_filter() ) ||
				( 'on' === ( $generate_on_frontend = $this->get_options()->get_option( 'generate_reduced_log_on_frontend', 'wpmd_log', 'off' ) ) && 'wp_footer' === current_filter() )
			) {
				\WP_Filesystem();
				global $wp_filesystem;
				$reduced_log_file = trailingslashit( $this->get_log_file_directory() ) . 'debug-reduced.log';
				$wp_filesystem->put_contents( $reduced_log_file, $this->get_last_n_lines( $this->get_options()->get_option( 'last_x_log_lines', 'wpmd_log', 50 ) ) );
			}
		}

		/**
		 * search.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @see https://stackoverflow.com/a/3686287/1193038
		 *
		 * @return array|bool
		 */
		function search(){
			if ( ! $this->is_log_file_valid() ) {
				return false;
			}
			$searchthis = "trying";
			$matches = array();

			$handle = @fopen($this->get_log_file(), "r");
			if ($handle)
			{
				while (!feof($handle))
				{
					$buffer = fgets($handle);
					if(strpos($buffer, $searchthis) !== FALSE)
						$matches[] = $buffer;
				}
				fclose($handle);
			}

			//show results:
			//print_r($matches);
			//error_log(print_r($matches,true));
			return $matches;
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
		function get_log_file( $test = false ) {
			$test = true;
			if ( empty( $this->log_path ) ) {
				if ( $log_path = $this->get_options()->get_option( 'log_file', 'wpmd_log', $this->get_default_log_file() ) ) {
					$this->log_path = $log_path;
				}
			}
			if ( $test ) {
				$this->log_path = str_replace( "debug.log", "log-test.txt", $this->log_path );
			}
			return $this->log_path;
		}

		/**
		 * Gets lines from x to y.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $per_page
		 * @param $current_page
		 *
		 * @return array|string
		 */
		function get_lines( $per_page, $current_page ) {
			if ( ! $this->is_log_file_valid() ) {
				return '';
			}
			$file        = $this->get_log_file();
			$spl         = new \SplFileObject( $file );
			$total_lines = $this->get_total_lines_amount();
			$reverse_order = $this->get_options()->get_option( 'reverse_chronological_order', 'wpmd_log', 'on' );
			$lines = array();
			if ( 'on' === $reverse_order ) {
				// Reverse Order
				$initial_pos = ( $total_lines - 1 ) - ( $per_page * ( $current_page - 1 ) );
				$final_pos   = $initial_pos - $per_page;
				for ( $i = $initial_pos; $i > $final_pos; $i -- ) {
					if ( $i < 0 ) {
						break;
					}
					$spl->seek( $i );
					$lines[]=array('message' => $spl->current(),'line'=>$i+1);
				}
			} else {
				// Normal Order
				$line_pos  = ( $current_page - 1 ) * $per_page;
				$lines_max = $line_pos + $per_page < $total_lines ? $line_pos + $per_page : $total_lines;
				$spl->seek( $line_pos );
				$lines = array();
				for ( $i = $line_pos; $i < $lines_max; $i ++ ) {
					$lines[]=array('message' => $spl->current(),'line'=>$i+1);
					$spl->next();
				}
			}
			return $lines;
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
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}
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