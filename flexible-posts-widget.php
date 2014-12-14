<?php
/**
 * Flexible Posts Widget
 *
 * Display posts as widget items.
 *
 * @package   DPE_Flexible_Posts_Widget
 * @author    David Paul Ellenwood <david@dpedesign.com>
 * @license   GPL-2.0+
 * @link      http://wordpress.org/extend/plugins/flexible-posts-widget
 * @copyright 2013 David Paul Ellenwood
 *
 * @flexible-posts-widget
 * Plugin Name:       Flexible Posts Widget
 * Plugin URI:        http://wordpress.org/extend/plugins/flexible-posts-widget
 * Description:       An advanced posts display widget with many options: get posts by post type and taxonomy & term or by post ID; sorting & ordering; feature images; custom templates and more.
 * Version:           3.4.1
 * Author:            dpe415
 * Author URI:        http://dpedesign.com
 * Text Domain:       flexible-posts-widget
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/dpellenwood/flexible-posts-widget
 */

/**
 * Copyright 2013  David Paul Ellenwood  (email : david@dpedesign.com)
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


// Block direct requests
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Flexible Posts Widget Class
 */
class DPE_Flexible_Posts_Widget extends WP_Widget {

    /**
     * Plugin version number
     *
     * The variable name is used as a unique identifier for the widget
     *
     * @since    3.3.1
     *
     * @var      string
     */
    protected $plugin_version = '3.4.1';

    /**
     * Unique identifier for your widget.
     *
     * The variable name is used as a unique identifier for the widget
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $widget_slug = 'dpe_fp_widget';
    
    /**
     * Unique identifier for your widget.
     *
     * The variable name is used as the text domain when internationalizing strings
     * of text. Its value should match the Text Domain file header in the main
     * widget file.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $widget_text_domain = 'flexible-posts-widget';
    
    /**
	 * Setup a number of variables to hold our default values
     *
     * @since    3.3.1
	 */
	protected $posttypes  = '';
	protected $pt_names   = '';
	protected $taxonomies = '';
	protected $tax_names  = '';
	protected $thumbsizes = '';
	protected $orderbys   = '';
	protected $orders     = '';
	protected $templates  = '';


	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {
		
		// load plugin text domain
		add_action( 'init', array( $this, 'widget_textdomain' ) );

		// The widget contrstructor
		parent::__construct(
			$this->get_widget_slug(),
			__( 'Flexible Posts Widget', $this->get_widget_text_domain() ),
			array(
				//'classname'   => $this->get_widget_slug(),
				'description' => __( 'Display posts as widget items.', $this->get_widget_text_domain() ),
			)
		);
		
		// Setup the default variables after wp is loaded
		add_action( 'wp_loaded', array( $this, 'setup_defaults' ) );

		// Register admin styles and scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );
		
		// Setup our get terms/AJAX callback
		add_action( 'wp_ajax_dpe_fp_get_terms', array( &$this, 'terms_checklist' ) );
		
	}
	
	/**
	 * Return the widget slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_widget_slug() {
		return $this->widget_slug;
	}

	/**
	 * Return the widget text domain.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin text domain variable.
	 */
	public function get_widget_text_domain() {
		return $this->widget_text_domain;
	}
	
	/**
	 * Return the plugin version.
	 *
	 * @since    3.3.1
	 *
	 * @return    Plugin version variable.
	 */
	public function get_plugin_version() {
		return $this->plugin_version;
	}


	/*--------------------------------------------------*/
	/* Widget API Functions
	/*--------------------------------------------------*/
	
	/**
	 * Outputs the content of the widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array args  The array of form elements
	 * @param array instance The current instance of the widget
	 */
	public function widget( $args, $instance ) {
				
		extract( $args );
		extract( $instance );
				
		$title = apply_filters( 'widget_title', empty( $title ) ? '' : $title );
		
		if ( empty( $template ) )
			$template = 'default.php';
		
		// Setup the query arguments array
		$args = array();
		
		// Get posts by post_ids specifically (ignore post type & tax/term values).
		if ( !empty( $pids ) ) {
			
			// Setup the query
			$args['post__in']	= $pids;
			$args['post_type']	= 'any';
		
		// Else get posts by post type and tax/term
		} else { 
		
			// Setup the post types
			$args['post_type'] = $posttype;
			
			// Setup the tax & term query based on the user's selection
			if ( $taxonomy != 'none' && !empty( $term ) ) {
				$args['tax_query'] = array(
					array(
						'taxonomy'	=> $taxonomy,
						'field'		=> 'slug',
						'terms'		=> $term,
					)
				);
			}
			
		}
		
		// Finish the query
		$args['post_status']			= array( 'publish', 'inherit' );
		$args['posts_per_page']			= $number;
		$args['offset']					= $offset;
		$args['orderby']				= $orderby;
		$args['order']					= $order;
		$args['ignore_sticky_posts']	= $sticky;
		
		
		// Allow filtering of the query arguments
		$args = apply_filters( 'dpe_fpw_args', $args );
		
		// Get the posts for this instance
		$flexible_posts = new WP_Query( $args );
		
		// Get and include the template we're going to use
		include( $this->get_template( $template ) );
		
		// Be sure to reset any post_data before proceeding
		wp_reset_postdata();
        
    }

    /**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		
		// Validate posttype submissions
		$posttypes = array();
		foreach( $new_instance['posttype'] as $pt ) {
			if( in_array( $pt, $this->pt_names ) )
				$posttypes[] = $pt;
		}
		if( empty( $posttypes ) )
			$posttypes[] = 'post';
		
		// Validate taxonomy & term submissions 
		if( in_array( $new_instance['taxonomy'], $this->tax_names ) ) {
			$taxonomy	= $new_instance['taxonomy'];
			$terms		= array();
			if( 'none' != $taxonomy ) {
				$term_objects = get_terms( $taxonomy, array( 'hide_empty' => false ) );
				$term_names = array();
				foreach ( $term_objects as $object ) {
					$term_names[] = $object->slug;
				}
				foreach( $new_instance['term'] as $term ) {
					if( in_array( $term, $term_names ) )
						$terms[] = $term;
				}
			}
		} else {
			$taxonomy = 'none';
			$terms = array();
		}
		
		// Validate Post ID submissions 
		$pids = array();
		if( !empty( $new_instance['pids'] ) ) {
			$pids_array = explode( ',', $new_instance['pids'] );
			foreach ( $pids_array as $id ) {
				$pids[] = absint( $id );
			}
		}		
		
		$instance 				= $old_instance;
		$instance['title']		= strip_tags( $new_instance['title'] );
		$instance['posttype']	= $posttypes;
		$instance['taxonomy']	= $taxonomy;
		$instance['term']		= $terms;
		$instance['pids']		= $pids;
		$instance['number']		= (int) $new_instance['number'];
		$instance['offset']		= (int) $new_instance['offset'];
		$instance['orderby']	= ( array_key_exists( $new_instance['orderby'], $this->orderbys ) ? $new_instance['orderby'] : 'date' );
		$instance['order']		= ( array_key_exists( $new_instance['order'], $this->orders ) ? $new_instance['order'] : 'DESC' );
		$instance['sticky']		= ( isset(  $new_instance['sticky'] ) ? (int) $new_instance['sticky'] : '0' );
		$instance['thumbnail']	= ( isset(  $new_instance['thumbnail'] ) ? (int) $new_instance['thumbnail'] : '0' );
		$instance['thumbsize']	= ( in_array ( $new_instance['thumbsize'], $this->thumbsizes ) ? $new_instance['thumbsize'] : '' );
		$instance['template']	= ( array_key_exists( $new_instance['template'], $this->templates ) ? $new_instance['template'] : 'default.php' );
		$instance['cur_tab']	= (int) $new_instance['cur_tab'];
        
        return $instance;
      
    }

    /**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		
		$instance = wp_parse_args( (array) $instance, array(
			'title'		=> '',
			'posttype'	=> array( 'post' ),
			'taxonomy'	=> 'none',
			'term'		=> array(),
			'pids'		=> '',
			'number'	=> '3',
			'offset'	=> '0',
			'orderby'	=> 'date',
			'order'		=> 'DESC',
			'sticky'	=> '0',
			'thumbnail' => '0',
			'thumbsize' => '',
			'template'	=> 'default.php',
			'cur_tab'	=> '0',
		) );
		
		extract( $instance );
		
		include( $this->get_template( 'admin' ) );
		
	}

	/**
	 * Loads theme files in appropriate hierarchy:
	 * 1. child theme 2. parent theme 3. plugin resources.
	 * Will look in the flexible-posts-widget/ directory in a theme
	 * and the views/ directory in the plugin
	 *
	 * Based on a function in the amazing image-widget
	 * by Matt Wiebe at Modern Tribe, Inc.
	 * http://wordpress.org/extend/plugins/image-widget/
	 * 
	 * @param string $template template file to search for
	 * @return template path
	 **/
	public function get_template( $template ) {
		
		// whether or not .php was added
		$template_slug = preg_replace( '/.php$/', '', $template );
		$template = $template_slug . '.php';
		
		// Set to the default
		$file = 'views/' . $template;

		// Look for a custom version
		if ( $theme_file = locate_template( array( $this->get_widget_text_domain() . '/' . $template ) ) ) {
			$file = $theme_file;
		}
		
		return apply_filters( 'dpe_fpw_template_' . $template, $file );
		
	}

	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/

	/**
	 * Loads the Widget's text domain for localization and translation.
	 */
	public function widget_textdomain() {
		
		load_plugin_textdomain( $this->get_widget_text_domain(), false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
	} // end widget_textdomain

	/**
	 * Registers and enqueues admin-specific styles.
	 */
	public function register_admin_styles() {

		wp_enqueue_style(
			$this->get_widget_slug() . '-admin',
			plugins_url( 'css/admin.css', __FILE__ ),
			array(),
			$this->get_plugin_version()
		);

	} // end register_admin_styles

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts() {
		
		$source = 'js/admin.min.js';
		
		if( SCRIPT_DEBUG ) {
			$source = 'js/admin.js';
		}
		
		wp_enqueue_script(
			$this->get_widget_slug() . '-admin',
			plugins_url( $source, __FILE__ ),
			array( 'jquery', 'jquery-ui-tabs' ),
			$this->get_plugin_version(),
			true
		);
		
		wp_localize_script( $this->get_widget_slug() . '-admin', 'fpwL10n', array(
			'gettingTerms' => __( 'Getting terms...', $this->get_widget_text_domain() ),
			'selectTerms' => __( 'Select terms:', $this->get_widget_text_domain() ),
			'noTermsFound' => __( 'No terms found.', $this->get_widget_text_domain() ),
		) );

	} // end register_admin_scripts
	
	
	/**
	 * Return a list of terms for the chosen taxonomy used via AJAX
	 */
	public function terms_checklist( $term ) {

		$taxonomy = esc_attr( $_POST['taxonomy'] );

		if ( ! isset( $term ) )
			$term = esc_attr( $_POST['term'] );
		
		if ( empty( $taxonomy ) || 'none' == $taxonomy ) {
			echo false;
			die();
		}
		
		$args = array (
			'hide_empty' => 0,
		);
		
		$terms = get_terms( $taxonomy, $args );
		
		if( empty($terms) ) { 
			$output = '<p>' . __( 'No terms found.', $this->get_widget_text_domain() ) . '</p>';
		} else {
			$output = '<ul class="categorychecklist termschecklist form-no-clear">';
			foreach ( $terms as $option ) {
				$output .= "\n<li>" . '<label class="selectit"><input value="' . esc_attr( $option->slug ) . '" type="checkbox" name="' . $this->get_field_name('term') . '[]"' . checked( in_array( $option->slug, (array)$term ), true, false ) . ' /> ' . esc_html( $option->name ) . "</label></li>\n";
			}
			$output .= "</ul>\n";
		}
		
		echo ( $output );
		
		die();
		
	}
	
	/**
     * Return a list of post types via AJAX
     */
	public function posttype_checklist( $posttype ) {

		$output = '<ul class="categorychecklist posttypechecklist form-no-clear">';
		foreach ( $this->posttypes as $type ) {
			$output .= "\n<li>" . '<label class="selectit"><input value="' . esc_attr( $type->name ) . '" type="checkbox" name="' . $this->get_field_name( 'posttype'  ) . '[]"' . checked( in_array( $type->name, (array)$posttype ), true, false ) . ' /> ' . esc_html( $type->labels->name ) . "</label></li>\n";
		}
		$output .= "</ul>\n";
		
		echo ( $output );
		
	}
	
	/**
     * Setup a number of default variables used throughout the plugin
     *
     * Since 3.3.1
     *
     */
	public function setup_defaults() {
		
		// Get the registered post types
		$this->posttypes = get_post_types( array( 'public' => true ), 'objects' );
		$this->pt_names  = get_post_types( array( 'public' => true ), 'names' );
		
		// Get the registered taxonomies
		$this->taxonomies  = get_taxonomies( array( 'public' => true ), 'objects' );
		$this->tax_names   = get_taxonomies( array( 'public' => true ), 'names' );
		$this->tax_names[] = 'none';
		
		// Get the registered image sizes
		$this->thumbsizes = get_intermediate_image_sizes();
		
		// Set the options for orderby
		$this->orderbys = array(
			'date'		 	=> __( 'Publish Date', $this->get_widget_text_domain() ),
			'modified'		=> __( 'Modified Date', $this->get_widget_text_domain() ),
			'title'			=> __( 'Title', $this->get_widget_text_domain() ),
			'menu_order'	=> __( 'Menu Order', $this->get_widget_text_domain() ),
			'ID'			=> __( 'Post ID', $this->get_widget_text_domain() ),
			'author'		=> __( 'Author', $this->get_widget_text_domain() ),
			'name'	 		=> __( 'Post Slug', $this->get_widget_text_domain() ),
			'comment_count'	=> __( 'Comment Count', $this->get_widget_text_domain() ),
			'rand'			=> __( 'Random', $this->get_widget_text_domain() ),
			'post__in'		=> __( 'Post ID Order', $this->get_widget_text_domain() ),
		);
		
		// Set the options for order
		$this->orders = array(
			'ASC'	=> __( 'Ascending', $this->get_widget_text_domain() ),
			'DESC'	=> __( 'Descending', $this->get_widget_text_domain() ),
		);
		
		// Set the available templates
		$this->templates = wp_cache_get( 'templates', $this->widget_slug );
		
		if( false === $this->templates ) {
			$this->templates = (array) $this->get_files( 'php', 0, true );
			wp_cache_set( 'templates', $this->templates, $this->widget_slug );
		}
		
		
	}

	/**
	 * Return template files from the current theme, parent theme and the plugin views directory.
	 *
	 * @since 3.3.1
	 * @access public
	 *
	 * Based on the function of the same name in wp-includes/class-wp-theme.php
	 *
	 * @param mixed $type Optional. Array of extensions to return. Defaults to all files (null).
	 * @param int $depth Optional. How deep to search for files. Defaults to a flat scan (0 depth). -1 depth is infinite.
	 * @param bool $search_parent Optional. Whether to return parent files. Defaults to false.
	 * @return array Array of files, keyed by the path to the file relative to the theme's directory, with the values
	 * 	being absolute paths.
	 */
	public function get_files( $type = null, $depth = 0, $search_parent = false ) {
		
		$files = array();
		$theme_dir = get_stylesheet_directory() . '/' . $this->get_widget_text_domain();
		$plugin_dir = dirname(__FILE__) . '/views';
		
		// Check the current theme
		if( is_dir( $theme_dir ) ) {
			$files += (array) self::scandir( $theme_dir, $type, $depth );
		}

		// Check the parent theme
		if ( $search_parent && is_child_theme() ) {
			$parent_theme_dir = get_template_directory() . '/' . $this->get_widget_text_domain();
			if( is_dir( $parent_theme_dir ) ) {
				$files += (array) self::scandir( $parent_theme_dir, $type, $depth );
			}
		}
		
		// Check the plugin views folder
		if( is_dir( $plugin_dir ) ) {
			$files += (array) self::scandir( $plugin_dir, $type, $depth );
			// Remove the admin view
			unset( $files['admin.php'] );
		}
		
		return $files;
	}
	
	/**
	 * Scans a directory for files of a certain extension.
	 *
	 * @since 3.3.1
	 * @access private
	 *
	 * Based on the function of the same name in wp-includes/class-wp-theme.php
	 *
	 * @param string $path Absolute path to search.
	 * @param mixed  Array of extensions to find, string of a single extension, or null for all extensions.
	 * @param int $depth How deep to search for files. Optional, defaults to a flat scan (0 depth). -1 depth is infinite.
	 * @param string $relative_path The basename of the absolute path. Used to control the returned path
	 * 	for the found files, particularly when this function recurses to lower depths.
	 */
	private static function scandir( $path, $extensions = null, $depth = 0, $relative_path = '' ) {
		if ( ! is_dir( $path ) )
			return false;

		if ( $extensions ) {
			$extensions = (array) $extensions;
			$_extensions = implode( '|', $extensions );
		}

		$relative_path = trailingslashit( $relative_path );
		if ( '/' == $relative_path )
			$relative_path = '';

		$results = scandir( $path );
		$files = array();

		foreach ( $results as $result ) {
			if ( '.' == $result[0] )
				continue;
			if ( is_dir( $path . '/' . $result ) ) {
				if ( ! $depth || 'CVS' == $result )
					continue;
				$found = self::scandir( $path . '/' . $result, $extensions, $depth - 1 , $relative_path . $result );
				$files = array_merge_recursive( $files, $found );
			} elseif ( ! $extensions || preg_match( '~\.(' . $_extensions . ')$~', $result ) ) {
				$files[ $relative_path . $result ] = $path . '/' . $result;
			}
		}

		return $files;
	}
	

} // class DPE_Flexible_Posts_Widget


/**
 * Initialize the widget on widgets_init
 */
add_action( 'widgets_init', create_function( '', 'register_widget("DPE_Flexible_Posts_Widget");' ) );
