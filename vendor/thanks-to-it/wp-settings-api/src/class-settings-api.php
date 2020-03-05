<?php
/**
 * Remove Special Characters From Permalinks Pro - WeDevs Settings API
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Thanks to IT
 */

namespace ThanksToIT\WP_Settings_API;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'ThanksToIT\WP_Settings_API\Settings_API' ) ) {

	class Settings_API extends \WeDevs_Settings_API {

		public function __construct() {
			parent::__construct();
		}

		/**
         * Remove the h2 section title and call the custom do_settings_fields
         *
         * @see \do_settings_sections() from template.php
		 * @param $page
		 */
		function do_settings_sections( $page ) {
			global $wp_settings_sections, $wp_settings_fields;

			if ( ! isset( $wp_settings_sections[ $page ] ) ) {
				return;
			}

			foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
				if ( $section['callback'] ) {
					call_user_func( $section['callback'], $section );
				}

				if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
					continue;
				}
				echo '<table class="form-table">';
				$this->do_settings_fields( $page, $section['id'] );
				echo '</table>';
			}
		}

		/**
         * If there is a type == subsection, just call the call_user_func() instead of creating the tr
         *
		 * @see \do_settings_fields() from template.php
		 * @param $page
		 * @param $section
		 */
		function do_settings_fields( $page, $section ) {
			global $wp_settings_fields;

			if ( ! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
				return;
			}

			foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
				$class = '';

				if ( $field['args']['type'] != 'subsection' ) {
					if ( ! empty( $field['args']['class'] ) ) {
						$class = ' class="' . esc_attr( $field['args']['class'] ) . '"';
					}

					echo "<tr{$class}>";

					if ( ! empty( $field['args']['label_for'] ) ) {
						echo '<th scope="row"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . $field['title'] . '</label></th>';
					} else {
						echo '<th scope="row">' . $field['title'] . '</th>';
					}

					echo '<td>';
					call_user_func( $field['callback'], $field['args'] );
					echo '</td>';
					echo '</tr>';
				} else {
					call_user_func( $field['callback'], $field['args'] );
				}
			}
		}

		/**
		 * Show the section settings forms using a custom do_settings_sections
		 *
		 * This function displays every sections in a different form
		 */
		function show_forms() {
			?>
            <!--<div class="metabox-holder">-->
				<?php foreach ( $this->settings_sections as $form ) { ?>
                    <div id="<?php echo $form['id']; ?>" class="group" style="display: none;">
                        <form method="post" action="options.php">
							<?php
							do_action( 'wsa_form_top_' . $form['id'], $form );
							settings_fields( $form['id'] );
							$this->do_settings_sections( $form['id'] );
							do_action( 'wsa_form_bottom_' . $form['id'], $form );
							if ( isset( $this->settings_fields[ $form['id'] ] ) ):
								?>
                                <div>
									<?php submit_button(); ?>
                                </div>
							<?php endif; ?>
                        </form>
                    </div>
				<?php } ?>
            <!--</div>-->
			<?php
			$this->script();
		}

		/**
		 * Displays the html for a subsection field
		 *
		 * @param array   $args settings field args
		 * @return string
		 */
		public function callback_subsection( $args ) {
			if ( ! isset( $args['name'] ) ) {
				return;
			}
			$subection = $args['name'];
			if ( empty( $subection ) ) {
				return;
			}
			if ( isset( $args['desc'] ) ) {
				$desc = $args['desc'];
			}

			?>
            </table>
            <h2><?php echo esc_html( $subection ) ?></h2>
			<?php if ( ! empty( $desc ) ): ?>
                <p><?php echo esc_html( $desc ); ?></p>
			<?php endif; ?>
            <table class="form-table">
                <tbody>
			<?php
		}
	}
}