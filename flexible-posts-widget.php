<?php
/**
 * Flexible Posts Widget
 *
 * A collection of widgets to display posts based on different criteria
 *
 * @package           DPE_Flexible_Posts_Widget
 * @author            DPE WS&D LLC <fpw@dpedesign.com>
 * @license           GPL-2.0+
 * @link              http://flexiblepostswidget.com
 * @copyright         2013 DPE WS&D LLC
 *
 * @wordpress-plugin
 * Plugin Name:       Flexible Posts Widget
 * Plugin URI:        http://flexiblepostswidget.com 
 * Description:       An advanced posts display widget with many options: get posts by post type and taxonomy & term or by post ID; sorting & ordering; feature images; custom templates and more.
 * Version:           3.5.0-dev
 * Author:            DPE WS&D LLC
 * Author URI:        http://dpedesign.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       flexible-posts-widget
 * Domain Path:       /languages
 */

/**
 * Copyright 2013 DPE WS&D LLC
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'FPW_Plugin' ) ) {

	/**
	 * Flexible Posts Widgets Plugin Class
	 *
	 * This is the bootstrapping class for the plugin.
	 */
	class FPW_Plugin {

		/**
		 * The unique plugin id or slug.
		 *
		 * @since    3.5.0
		 * @access   protected
		 * @var      string    $plugin_slug    The slug used to uniquely identify the plugin.
		 */
		protected $plugin_slug;

		/**
		 * The current version of the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $version    The current version of the plugin.
		 */
		protected $version;

		/**
		 * The current version of the plugin.
		 *
		 * @since    3.5.0
		 * @access   protected
		 * @var      int    $db_version    The current version of the plugin's database settings.
		 */
		protected $db_version;

		/**
		 * The unique identifier of this plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $text_domain    The string used for internationalization.
		 */
		protected $text_domain;

		/**
		 * The directory path for the plugin.
		 *
		 * @since    3.5.0
		 * @access   protected
		 * @var      string    $plugin_dir    The directory path to the plugin.
		 */
		protected $plugin_dir;

		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
		 * the public-facing side of the site.
		 *
		 * @since    3.5.0
		 */

		public function __construct() {
			$this->plugin_slug  = 'dpe_fp_widget';
			$this->version      = '3.5.0';
			$this->db_version   = 2;
			$this->text_domain  = 'flexible-post-widget';
			$this->plugin_dir   = plugin_dir_path( __FILE__ );
		}

		/**
		 * Setup the environment for the plugin
		 */
		public function bootstrap() {
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
		}

		/**
		 * Do some stuff upon activation
		 */
		public function activate() {
			$this->init_options();
			$this->maybe_update();
		}

		/**
		 * Initialize default option values
		 */
		public function init_options() {
			update_option( $this->plugin_slug . '_ver', $this->version );
			add_option( $this->plugin_slug . '_db_ver', $this->db_version );
		}

		/**
		 * Check to see if we need to run an update routine
		 */
		public function maybe_update() {

			// bail if this plugin data doesn't need updating
			if ( get_option( $this->plugin_slug . '_db_ver' ) >= $this->db_version ) {
				return;
			}

			// Otherwise, run the updater
			require_once( $this->plugin_dir . 'includes/class-fpw-update.php' );
			$updater = new FPW_Plugin_Updater( $this->plugin_slug, $this->db_version );
			$updater->update_plugin();

		}

		/**
		 * Retrieve the unique id (slug) for the plugin
		 *
		 * @since     3.5.0
		 * @return    string    The plugin id/slug.
		 */
		public function get_slug() {
			return $this->plugin_slug;
		}

		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @since     3.5.0
		 * @return    string    The version number of the plugin.
		 */
		public function get_version() {
			return $this->version;
		}

		/**
		 * Retrieve the database version number of the plugin.
		 *
		 * @since     3.5.0
		 * @return    int    The database version number of the plugin.
		 */
		public function get_db_version() {
			return $this->db_version;
		}

		/**
		 * Retrieve the text domain of the plugin.
		 *
		 * @since     3.5.0
		 * @return    string    The text domain number of the plugin.
		 */
		public function get_text_domain() {
			return $this->text_domain;
		}

		/**
		 * Retrieve the plugin directory path.
		 *
		 * @since     3.5.0
		 * @return    string    The directory path of the plugin.
		 */
		public function get_plugin_dir() {
			return $this->plugin_dir;
		}


	} // FPW_Plugin

}

/**
 * Begins execution of the plugin.
 *
 * @since    3.5.0
 */
global $dpe_fp_widget;
$dpe_fp_widget = new FPW_Plugin();
$dpe_fp_widget->bootstrap();