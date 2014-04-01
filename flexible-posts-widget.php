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
 * Version:           3.3
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
     * Unique identifier for your widget.
     *
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


	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {
	
		// Define our version number
		if( ! defined( 'DPE_FP_Version' ) )
			define( 'DPE_FP_Version', '3.3' );
		
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
			$template = 'widget.php';
		
		// Setup the query arguments array
		$args = array();
		
		// Get posts by post_ids specifically (ignore post type & tax/term values).
		if ( !empty( $pids ) ) {
		
			// Setup the query
			$args['post__in']	= $pids;
			$args['post_type']	= get_post_types( array( 'public' => true ) );
		
		
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
		include( $this->getTemplateHierarchy( $template ) );
		
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
		
		// Get our defaults to test against
		$this->posttypes	= get_post_types( array( 'public' => true ), 'objects' );
		$this->taxonomies	= get_taxonomies( array( 'public' => true ), 'objects' );
		$this->thumbsizes	= get_intermediate_image_sizes();
		$this->orderbys		= array(
			'date'		 	=> __( 'Publish Date', 'flexible-posts-widget' ),
			'title'			=> __( 'Title', 'flexible-posts-widget' ),
			'menu_order'	=> __( 'Menu Order', 'flexible-posts-widget' ),
			'ID'			=> __( 'Post ID', 'flexible-posts-widget' ),
			'author'		=> __( 'Author', 'flexible-posts-widget' ),
			'name'	 		=> __( 'Post Slug', 'flexible-posts-widget' ),
			'comment_count'	=> __( 'Comment Count', 'flexible-posts-widget' ),
			'rand'			=> __( 'Random', 'flexible-posts-widget' ),
			'post__in'		=> __( 'Post ID Order', 'flexible-posts-widget' ),
		);
		$this->orders		= array(
			'ASC'	=> __( 'Ascending', 'flexible-posts-widget' ),
			'DESC'	=> __( 'Descending', 'flexible-posts-widget' ),
		);
		
		$pt_names		= get_post_types( array( 'public' => true ), 'names' );
		$tax_names		= get_taxonomies( array( 'public' => true ), 'names' );
		$tax_names[]	= 'none';
		
		// Validate posttype submissions
		$posttypes = array();
		foreach( $new_instance['posttype'] as $pt ) {
			if( in_array( $pt, $pt_names ) )
				$posttypes[] = $pt;
		}
		if( empty( $posttypes ) )
			$posttypes[] = 'post';
		
		// Validate taxonomy & term submissions 
		if( in_array( $new_instance['taxonomy'], $tax_names ) ) {
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
		$instance['template']	= strip_tags( $new_instance['template'] );
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
		
		$this->posttypes	= get_post_types( array( 'public' => true ), 'objects' );
		$this->taxonomies	= get_taxonomies( array( 'public' => true ), 'objects' );
		$this->thumbsizes	= get_intermediate_image_sizes();
		$this->orderbys		= array(
			'date'		 	=> __( 'Publish Date', 'flexible-posts-widget' ),
			'title'			=> __( 'Title', 'flexible-posts-widget' ),
			'menu_order'	=> __( 'Menu Order', 'flexible-posts-widget' ),
			'ID'			=> __( 'Post ID', 'flexible-posts-widget' ),
			'author'		=> __( 'Author', 'flexible-posts-widget' ),
			'name'	 		=> __( 'Post Slug', 'flexible-posts-widget' ),
			'comment_count'	=> __( 'Comment Count', 'flexible-posts-widget' ),
			'rand'			=> __( 'Random', 'flexible-posts-widget' ),
			'post__in'		=> __( 'Post ID Order', 'flexible-posts-widget' ),
		);
		$this->orders		= array(
			'ASC'	=> __( 'Ascending', 'flexible-posts-widget' ),
			'DESC'	=> __( 'Descending', 'flexible-posts-widget' ),
		);
		
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
			'template'	=> 'widget.php',
			'cur_tab'	=> '0',
		) );
		
		extract( $instance );
		
		include( $this->getTemplateHierarchy( 'admin' ) );
		
	}

	/**
	 * Loads theme files in appropriate hierarchy: 1) child theme,
	 * 2) parent template, 3) plugin resources. will look in the flexible-posts-widget/
	 * directory in a theme and the views/ directory in the plugin
	 *
	 * Based on a function in the amazing image-widget
	 * by Matt Wiebe at Modern Tribe, Inc.
	 * http://wordpress.org/extend/plugins/image-widget/
	 * 
	 * @param string $template template file to search for
	 * @return template path
	 **/
	public function getTemplateHierarchy( $template ) {
		
		// whether or not .php was added
		$template_slug = preg_replace( '/.php$/', '', $template );
		$template = $template_slug . '.php';

		if ( $theme_file = locate_template( array( 'flexible-posts-widget/' . $template ) ) ) {
			$file = $theme_file;
		} else {
			$file = 'views/' . $template;
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

		load_plugin_textdomain( $this->get_widget_slug(), false, plugin_dir_path( __FILE__ ) . 'languages/' );

	} // end widget_textdomain

	/**
	 * Registers and enqueues admin-specific styles.
	 */
	public function register_admin_styles() {

		wp_enqueue_style(
			$this->get_widget_slug() . '-admin',
			plugins_url( 'css/admin.css', __FILE__ ),
			array(),
			DPE_FP_Version
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
			DPE_FP_Version,
			true
		);
		
		wp_localize_script( $this->get_widget_slug() . '-admin', 'fpwL10n', array(
			'gettingTerms' => __( 'Getting terms...', 'flexible-posts-widget' ),
			'selectTerms' => __( 'Select terms:', 'flexible-posts-widget' ),
			'noTermsFound' => __( 'No terms found.', 'flexible-posts-widget' ),
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
			$output = '<p>' . __( 'No terms found.', 'flexible-posts-widget' ) . '</p>';
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
		
		//Get pubic post type objects
		$posttypes = get_post_types( array( 'public' => true ), 'objects' );

		$output = '<ul class="categorychecklist posttypechecklist form-no-clear">';
		foreach ( $posttypes as $type ) {
			$output .= "\n<li>" . '<label class="selectit"><input value="' . esc_attr( $type->name ) . '" type="checkbox" name="' . $this->get_field_name( 'posttype'  ) . '[]"' . checked( in_array( $type->name, (array)$posttype ), true, false ) . ' /> ' . esc_html( $type->labels->name ) . "</label></li>\n";
		}
		$output .= "</ul>\n";
		
		echo ( $output );
		
	}
	

} // class DPE_Flexible_Posts_Widget


/**
 * Initialize the widget on widgets_init
 */
add_action( 'widgets_init', create_function( '', 'register_widget("DPE_Flexible_Posts_Widget");' ) );
