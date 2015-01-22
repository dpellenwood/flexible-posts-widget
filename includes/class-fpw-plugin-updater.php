<?php
/**
 * The plugin updater class.
 *
 * @link       http://dpedesign.com
 * @since      3.5.0
 *
 * @package    Flexible_Posts_Widget
 * @subpackage Flexible_Posts_Widget/includes
 */

/**
 * The plugin updater class.
 *
 * This class handles any database updates that may need to be performed.
 * Updated are handled incrementally to make sure no changes are missed.
 *
 * @since      3.5.0
 * @package    Flexible_Posts_Widget
 * @subpackage Flexible_Posts_Widget/includes
 * @author     DPE WS&D LLC <fpw@dpedesign.com>
 */
class FPW_Plugin_Updater {

	/**
	 * The unique plugin id or slug.
	 *
	 * @since    3.5.0
	 * @access   private
	 * @var      string    $plugin_slug    The slug used to uniquely identify the plugin.
	 */
	private $plugin_slug;

	/**
	 * The current version of the plugin.
	 *
	 * @since    3.5.0
	 * @access   private
	 * @var      int    $db_version    The current version of the plugin's database settings.
	 */
	private $db_version;

	/**
	 * Initialize the class and set it's properties.
	 *
	 * @since    3.5.0
	 */
	public function __construct( $plugin_slug, $db_version ) {
		$this->plugin_slug  = $plugin_slug;
		$this->db_version   = $db_version;
	}

	/**
	 * The main updater method
	 *
	 * This method checks the database version saved in the site's options table
	 * against the version required by this version of the plugin.
	 * If the site's version is below the plugin's version, it runs an update
	 * function for each sequential version until the current version is attained.
	 *
	 * @since    3.5.0
	 */
	public function update_plugin( $current_db_ver ) {

		set_time_limit( 0 );

		// this is the target version that we need to reach
		$target_db_ver = $this->db_version;

		// run update routines one by one until the current version number
		// reaches the target version number
		while ( $current_db_ver < $target_db_ver ) {

			// increment the current db_ver by one
			$current_db_ver ++;

			// each db version will require a separate update function
			// for example, for db_ver 3, the function name should be solis_update_routine_3
			$function_name = $this->plugin_slug . '_update_routine_' . $current_db_ver;
			if ( method_exists( $this, $function_name ) ) {
				call_user_func( array( $this, $function_name ) );
			}

			// update the option in the database, so that this process can always
			// pick up where it left off
			update_option( $this->plugin_slug . '_db_ver', $current_db_ver );

		}

	}

	/**
	 * The update routine for database version 2.
	 *
	 * This routine is used to update previous version's instance settings.
	 * Old versions of the plugin used term->slug to store the terms selected in each instance.
	 * Version 3.5.0 and above will store the term->ID instead of the slug, so we need to run through
	 * any existing widget instances and convert from term->slug to term->id for each selected term.
	 *
	 * @since    3.5.0
	 */
	public function dpe_fp_widget_update_routine_2() {

		// Boolean to check if we actually need to update the widget settings.
		$needs_update = false;

		// Go get the widget configuration data from the DB and save a backup copy.
		$options = get_option( 'widget_' . $this->plugin_slug );
		$options_bk = $options;

		if( $options ) {

			// Run through each instance's settings and check if we need to update...
			foreach( $options as $key => $value ) {

				// If the taxonomy key is set and not set to none...
				if( isset( $value['taxonomy'] ) && ! empty( $value['taxonomy'] ) && 'none' != $value['taxonomy'] ) {

					// If we've got terms to convert...
					if( isset( $value['term'] ) && ! empty( $value['term'] ) ) {

						/**
						 * Setup the conversion query...
						 * Get the term IDs for the array of term slugs we have saved in the taxonomy we have saved
						 */
						$args = array(
							'fields' => 'ids',
							'slug'   => $value['term'],
						);
						$term_ids = get_terms( $value['taxonomy'], $args );

						if( ! is_wp_error( $term_ids ) && ! empty( $term_ids ) ) {
							// As long as we have have a valid term response, set it to this instance's option value.
							$options[$key]['term'] = $term_ids;
							$needs_update = true;
						}

					}

				}

			}

			// Finally, we create a backup of the existing settings and update the settings if the update is required.
			if( $needs_update ) {
				$prev_db_ver = $this->db_version - 1;
				add_option( 'widget_' . $this->plugin_slug . '_backup_v' . $prev_db_ver, $options_bk, '', 'no' );
				update_option( 'widget_' . $this->plugin_slug, $options );
			}

		}

	}

}