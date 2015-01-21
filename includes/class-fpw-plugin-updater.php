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
	 * @TODO: Short Description. (use period)
	 *
	 * @TODO: Long Description.
	 *
	 * @since    3.5.0
	 */
	public function update_plugin() {

		// no PHP timeout for running updates
		// Maybe someday, but our updates are really minor and this is dangerous!
		//set_time_limit( 0 );

		// this is the current database schema version number
		$current_db_ver = (int)get_option( $this->plugin_slug . '_db_ver' );

		if ( ! $current_db_ver ) {
			$current_db_ver = 1;
		}

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
	 * The update routine for database version 2
	 *
	 * @TODO: Long Description.
	 *
	 * @since    3.5.0
	 */
	public function dpe_fp_widget_update_routine_2() {
		// @TODO: Write the upgrade routine.
	}

}