<?php
/**
 * WP Marvelous Debug - Log List
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\WPMD\Admin;


use ThanksToIT\WPMD\Log_File;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\WPMD\Admin\Log_List' ) ) {

	class Log_List extends \WP_List_Table {

		/**
		 * @var Log_File
		 */
		private $log_file;

		/**
		 * Log_List constructor.
		 */
		public function __construct() {

			parent::__construct( [
				'singular' => __( 'Line', 'wp-marvelous-debug' ), //singular name of the listed records
				'plural'   => __( 'Lines', 'wp-marvelous-debug' ), //plural name of the listed records
				'ajax'     => false //does this table support ajax?
			] );

		}

		/**
		 * Returns the count of records in the database.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return null|string
		 */
		public function record_count() {
			return $this->get_log_file()->get_total_lines_amount();

			//return 5;
			/*global $wpdb;

			$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}customers";

			return $wpdb->get_var( $sql );*/
		}

		/**
		 * Text displayed when no customer data is available.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		public function no_items() {
			_e( 'No lines avaliable.', 'wp-marvelous-debug' );
		}

		/**
		 * get_table_classes.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return array
		 */
		protected function get_table_classes() {
			$classes = parent::get_table_classes();
			if ( ( $key = array_search( 'fixed', $classes ) ) !== false ) {
				unset( $classes[ $key ] );
			}
			return $classes;
		}

		/**
		 * Render a column when no column specific method exist.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param array $item
		 * @param string $column_name
		 *
		 * @return mixed
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'line_number':
					return $item['line'];
					break;
				case 'message':
					return $item['message'];
					break;
				case 'date':
					if ( preg_match( '/(?<=\[).*(?=\])/', $item, $output_array ) ) {
						return $output_array[0];
					} else {
						return '-';
					}
					break;
				case 'type':
					if ( preg_match( '/(?<=PHP\s).+(?=\:\s{2})/', $item, $output_array ) ) {
						return $output_array[0];
					} else {
						return '-';
					}
					break;
				default:
					return print_r( $item, true ); //Show the whole array for troubleshooting purposes
			}
			//return $item['message'];
			//error_log(print_r($item,true));

		}

		/**
		 * Render the bulk edit checkbox
		 *
		 * @param array $item
		 *
		 * @return string
		 */
		/*function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
			);
		}*/


		/**
		 * Method for name column
		 *
		 * @param array $item an array of DB data
		 *
		 * @return string
		 */
		/*function column_name( $item ) {

			$delete_nonce = wp_create_nonce( 'sp_delete_customer' );

			$title = '<strong>' . $item['name'] . '</strong>';

			$actions = [
				'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce )
			];

			return $title . $this->row_actions( $actions );
		}*/


		/**
		 *  Associative array of columns.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @return array
		 */
		function get_columns() {
			$columns = [
				//'cb'      => '<input type="checkbox" />',
				//'date'    => __( 'Date', 'wp-marvelous-debug' ),
				//'type' => __( 'Type', 'wp-marvelous-debug' ),
				'message'    => __( 'Message', 'wp-marvelous-debug' ),
				'line_number'=> __( 'Line', 'wp-marvelous-debug' ),

			];

			return $columns;
		}


		/**
		 * Columns to make sortable.
		 *
		 * @return array
		 */
		/*public function get_sortable_columns() {
			$sortable_columns = array(
				'name' => array( 'name', true ),
				'city' => array( 'city', false )
			);

			return $sortable_columns;
		}*/

		/**
		 * Handles data query and filter, sorting, and pagination.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 */
		public function prepare_items() {
			if ( ! empty( $this->items ) ) {
				return;
			}
			$columns               = $this->get_columns();
			$this->_column_headers = array(
				$columns,        // columns
			);
			$this->process_bulk_action();
			$per_page     = $this->get_items_per_page( 'wpmd_lines_per_page', 15 );
			$current_page = $this->get_pagenum();
			$total_items  = $this->record_count();
			$this->set_pagination_args( [
				'total_items' => $total_items, //WE have to calculate the total number of items
				'per_page'    => $per_page //WE have to determine how many items to show on a page
			] );
			$this->items = $this->get_log_file()->get_lines( $per_page, $current_page );
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




	}
}