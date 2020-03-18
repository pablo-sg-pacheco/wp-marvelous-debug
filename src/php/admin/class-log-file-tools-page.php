<?php
/**
 * WP Marvelous Debug - Log File Tools Page
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\WPMD\Admin;


use ThanksToIT\WPMD\Log_File;
use ThanksToIT\WPMD\Options;

if ( ! class_exists( 'ThanksToIT\WPMD\Admin\Log_File_Tools_Page' ) ) {
	class Log_File_Tools_Page {

		/**
		 * @var Log_List
		 */
		private $log_list;

		/**
		 * @var Log_File
		 */
		private $log_file;

		/**
		 * @var Options
		 */
		private $options;

		/**
		 * redirect_to_last_page.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function redirect_to_last_page() {
			if (
				'on' !== $this->get_options()->get_option( 'redirect_to_last_page', 'wpmd_log', 'off' ) ||
				! function_exists( 'get_current_screen' ) ||
				'tools_page_wpmd_log_file' !== ( $screen = get_current_screen() )->id ||
				isset( $_GET['paged'] )
			) {
				return;
			}
			// Handle first page from any other page
			/*if (
				isset( $_SERVER['HTTP_REFERER'] ) &&
				'tools.php' == basename( parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_PATH ) )
			) {
				parse_str( parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_QUERY ), $result );
				if ( isset( $result['test'] ) ) {
					return;
				}
			}*/
			$this->get_log_list()->prepare_items();
			$total_pages = $this->get_log_list()->get_pagination_arg( 'total_pages' );
			wp_redirect( add_query_arg( array( 'paged' => $total_pages,'test'=>'true' ), admin_url( 'tools.php?page=wpmd_log_file' ) ) );
		}

		/**
		 * add_page_content.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function add_page_content() {
			?>
			<div class="wrap">
				<h1><?php echo __( 'Log File', 'remove-special-characters-from-permalinks' ) ?></h1>
				<?php if ( $this->get_log_file()->is_log_file_valid() ) : ?>
					<p>
						<?php echo __( 'Displaying ', 'remove-special-characters-from-permalinks' ) . '<code>' . $this->get_log_file()->get_log_file() . '</code>'; ?>
					</p>
				<?php endif; ?>
				<div id="poststuff">
					<div id="post-body" class="metabox-holder">
						<div id="post-body-content">
							<div class="meta-box-sortables ui-sortable">
								<form method="post">
									<?php
									$this->log_list->prepare_items();
									$this->log_list->display(); ?>
								</form>
							</div>
						</div>
					</div>
					<br class="clear">
				</div>
			</div>
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
				'tools.php' != $pagenow ||
				! isset( $_GET['page'] ) ||
				'wpmd_log_file' != $_GET['page']
			) {
				return;
			}
			?>
			<style>
				.column-line_number{
					text-align:right !important;
					white-space: nowrap;
				}
				td.column-line_number{
					color: #000; /* Fallback for older browsers */
					color: rgba(0, 0, 0, 0.4) !important;
				}
				.column-message{
					width:100%;
				}
				td.column-message, td.column-line_number{
					padding-top:1px !important;
					padding-bottom:1px !important;
				}
				.wpmd-line-date{
					/*color:red;*/
				}
				td.column-line_number{
					font-size:12px;
					vertical-align: middle;
				}
				.wpmd-pre{
					tab-size:2;
					margin-top:0;
					margin-bottom:0;
					white-space: -moz-pre-wrap;
					white-space: -o-pre-wrap;
					white-space: pre-wrap;
					word-wrap: break-word;
					font-size:11px;
				}
			</style>
			<?php
		}

		/**
		 * admin menu.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function admin_menu() {
			$hook = add_management_page( 'Log File', 'Log File', 'delete_posts', 'wpmd_log_file', array( $this, 'add_page_content' ) );
			add_action( "load-$hook", array( $this, 'screen_option' ) );
		}

		/**
		 * screen_option.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		function screen_option() {
			$option = 'per_page';
			$args   = [
				'label' => 'Lines per page',
				'default' => 30,
				'option' => 'wpmd_lines_per_page'
			];
			add_screen_option( $option, $args );
		}

		/**
		 * set_screen.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param $status
		 * @param $option
		 * @param $value
		 *
		 * @return mixed
		 */
		function set_screen( $status, $option, $value ) {
			$GLOBALS['hook_suffix'] = 'wpmd';
			if ( 'wpmd_lines_per_page' == $option ) {
				return $value;
			}
			return $status;
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
		 * @return Log_List
		 */
		public function get_log_list() {
			return $this->log_list;
		}

		/**
		 * @param Log_List $log_list
		 */
		public function set_log_list( $log_list ) {
			$this->log_list = $log_list;
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