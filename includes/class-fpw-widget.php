<?php
/**
 * Flexible Posts Widget
 *
 * @link       http://dpedesign.com
 * @since      1.0.0
 *
 * @package    Flexible_Posts_Widget
 * @subpackage Flexible_Posts_Widget/includes
 */

/**
 * Flexible Posts Widget.
 *
 * The primary widget that has existed since the beginning of FPW time.
 *
 * @since      1.0.0
 * @package    Flexible_Posts_Widget
 * @subpackage Flexible_Posts_Widget/includes
 * @author     DPE WS&D LLC <fpw@dpedesign.com>
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Flexible Posts Widget Class
 */
class Flexible_Posts_Widget extends WP_Widget {

    /**
     * Plugin version number
     *
     * The variable name is used as a unique identifier for the widget
     *
     * @since   3.3.1
     * @access  private
     * @var     string
     */
    private $version;

    /**
     * Unique identifier for your widget.
     *
     * The variable name is used as a unique identifier for the widget
     *
     * @since   1.0.0
     * @access  private
     * @var     string
     */
    private $widget_slug;
    
    /**
     * Unique identifier for your widget.
     *
     * The variable name is used as the text domain when internationalizing strings
     * of text. Its value should match the Text Domain file header in the main
     * widget file.
     *
     * @since   1.0.0
     * @access  private
     * @var     string
     */
    private $widget_text_domain;

	/**
	 * The directory path for the plugin.
	 *
	 * @since    3.5.0
	 * @access   private
	 * @var      string    $plugin_dir    The directory path to the plugin.
	 */
	private $plugin_dir;
    
    /**
	 * Setup a number of variables to hold our default values
     *
     * @since    3.3.1
	 */
	protected $posttypes  = array();
	protected $pt_names   = array();
	protected $taxonomies = array();
	protected $tax_names  = array();
	protected $thumbsizes = array();
	protected $orderbys   = array();
	protected $orders     = array();
	protected $templates  = array();


	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {

		global $dpe_fpw_plugin;

		$this->version              = $dpe_fpw_plugin->get_version();
		$this->widget_slug          = $dpe_fpw_plugin->get_slug();
		$this->widget_text_domain   = $dpe_fpw_plugin->get_text_domain();
		$this->plugin_dir           = $dpe_fpw_plugin->get_plugin_dir();

		// Set the widget options
		$widget_opts = array(
			'description' => __( 'Display posts as widget items.', $this->widget_text_domain ),
		);

		// The widget constructor
		parent::__construct(
			$this->widget_slug,
			__( 'Flexible Posts Widget', $this->widget_text_domain ),
			$widget_opts
		);
		
		// Setup the default variables after wp is loaded
		add_action( 'wp_loaded', array( $this, 'setup_defaults' ) );
		
		// Setup our get terms/AJAX callback
		add_action( 'wp_ajax_dpe_fp_get_terms', array( &$this, 'terms_checklist' ) );
		
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
		$query_args = array();
		
		// Get posts by post_ids specifically (ignore post type & tax/term values).
		if ( !empty( $pids ) ) {
			
			// Setup the query
			$query_args['post__in']	 = $pids;
			$query_args['post_type'] = 'any';
		
		// Else get posts by post type and tax/term
		} else { 
		
			// Setup the post types
			$query_args['post_type'] = $posttype;
			
			// Setup the tax & term query based on the user's selection
			if ( $taxonomy != 'none' && !empty( $term ) ) {
				$query_args['tax_query'] = array(
					array(
						'taxonomy'	=> $taxonomy,
						'field'		=> 'id',
						'terms'		=> $term,
					)
				);
			}
			
		}
		
		// Finish the query
		$query_args['post_status']          = array( 'publish', 'inherit' );
		$query_args['posts_per_page']       = $number;
		$query_args['offset']               = $offset;
		$query_args['orderby']              = $orderby;
		$query_args['order']                = $order;
		$query_args['ignore_sticky_posts']  = $sticky;
		
		// Allow filtering of the query arguments
		$query_args = apply_filters( 'dpe_fpw_args', $query_args );
		
		// Get the posts for this instance
		$flexible_posts = new WP_Query( $query_args );
		
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
		
		$posttypes = array();
		$terms     = array();
		$taxonomy  = 'none';
		$pids      = array();
		
		// Validate posttype
		foreach ( $new_instance['posttype'] as $pt ) {
			if ( in_array( $pt, $this->pt_names ) ) {
				$posttypes[] = $pt;
			}
		}
		if ( empty( $posttypes ) ) {
			$posttypes[] = 'post';
		}
		
		// Validate taxonomy 
		if ( taxonomy_exists( $new_instance['taxonomy'] ) ) {
			$taxonomy = $new_instance['taxonomy'];
			
			/**
			 * Validate terms
			 * We have to work around the fact that the walker class for wp_terms_checklist()
			 * uses (DIFFERENT!!!) hard-coded HTML input names instead of allowing us to set
			 * a widget-instance-specific input name.
			 */
			if ( isset( $_REQUEST['widget-id'] ) && $_REQUEST['widget-id'] == $this->id ) {

				$posted_terms = array();

				/**
				 * If the posted terms are from the built-in Post Category taxonomy
				 * We have to use one $_POST variable and a different, variable
				 * $_POST variable for every other taxonomy.
				 */
				if( isset( $_POST['post_category'] ) ) {
					$posted_terms = $_POST['post_category'];
				} else {
					if ( isset( $_POST['tax_input'][$taxonomy] ) ) {
						$posted_terms = $_POST['tax_input'][$taxonomy];
					}
				}

				// Once we actually have the $_POSTed terms, validate and and save them
				foreach ( $posted_terms as $term ) {
					if( term_exists( absint( $term ), $taxonomy ) ) {
						$terms[] = absint( $term );
					}
				}

			}
			
		}
		
		// Validate Post ID submissions
		if ( !empty( $new_instance['pids'] ) ) {
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
		$file = $this->plugin_dir . 'views/' . $template;

		// Look for a custom version
		if ( $theme_file = locate_template( array( $this->widget_text_domain . '/' . $template ) ) ) {
			$file = $theme_file;
		}
		
		return apply_filters( 'dpe_fpw_template_' . $template, $file );
		
	}

	/**
	 * Return a list of terms for the chosen taxonomy
	 */
	public function terms_checklist( $taxonomy, $sel_terms = array() ) {

		if ( empty( $taxonomy ) && isset( $_POST['taxonomy'] ) )
			$taxonomy = esc_attr( $_POST['taxonomy'] );

		if ( empty( $taxonomy ) || 'none' == $taxonomy ) {
			echo false;
			if( isset ( $_POST['action'] )  && 'dpe_fp_get_terms' === $_POST['action'] ) {
				die();
			}
		}

		if( isset ( $_POST['widget_id'] ) ) {
			$widget_id = intval( $_POST['widget_id'] );
		} else {
			$widget_id = $this->number;
		}

		if( empty ( $sel_terms ) ) {
			$settings = get_option( $this->option_name );
			if( isset ( $settings[$widget_id]['taxonomy'] ) && $taxonomy == $settings[$widget_id]['taxonomy'] ) {
				$sel_terms = $settings[$widget_id]['term'];
			}
		}

		$args = array (
			'taxonomy'      => $taxonomy,
			'selected_cats' => $sel_terms,
		);

		ob_start();
		wp_terms_checklist( 0, $args );
		$terms_html = ob_get_contents();
		ob_end_clean();

		if( ! empty( $terms_html ) ) {
			$output = '<ul class="categorychecklist termschecklist form-no-clear">';
			$output .= $terms_html;
			$output .= "</ul>\n";
		} else {
			$output = '<p>' . __( 'No terms found.', $this->widget_text_domain ) . '</p>';
		}

		echo ( $output );

		if( isset ( $_POST['action'] )  && 'dpe_fp_get_terms' === $_POST['action'] ) {
			die();
		}
		
	}
	
	/**
     * Return a list of post types
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
			'date'		 	=> __( 'Publish Date', $this->widget_text_domain ),
			'modified'		=> __( 'Modified Date', $this->widget_text_domain ),
			'title'			=> __( 'Title', $this->widget_text_domain ),
			'menu_order'	=> __( 'Menu Order', $this->widget_text_domain ),
			'ID'			=> __( 'Post ID', $this->widget_text_domain ),
			'author'		=> __( 'Author', $this->widget_text_domain ),
			'name'	 		=> __( 'Post Slug', $this->widget_text_domain ),
			'comment_count'	=> __( 'Comment Count', $this->widget_text_domain ),
			'rand'			=> __( 'Random', $this->widget_text_domain ),
			'post__in'		=> __( 'Post ID Order', $this->widget_text_domain ),
		);
		
		// Set the options for order
		$this->orders = array(
			'ASC'	=> __( 'Ascending', $this->widget_text_domain ),
			'DESC'	=> __( 'Descending', $this->widget_text_domain ),
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
		$theme_dir = get_stylesheet_directory() . '/' . $this->widget_text_domain;
		$plugin_dir = $this->plugin_dir . '/views';
		
		// Check the current theme
		if( is_dir( $theme_dir ) ) {
			$files += (array) self::scandir( $theme_dir, $type, $depth );
		}

		// Check the parent theme
		if ( $search_parent && is_child_theme() ) {
			$parent_theme_dir = get_template_directory() . '/' . $this->widget_text_domain;
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

} // class Flexible_Posts_Widget
