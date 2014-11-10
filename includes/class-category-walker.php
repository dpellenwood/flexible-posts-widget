<?php

add_action( 'admin_init', 'fpw_category_walker_checkist' );

function fpw_category_walker_checkist() {
	
	/**
	 * Walker to output an unordered list of category checkbox <input> elements.
	 *
	 * @see Walker
	 * @see wp_category_checklist()
	 * @see wp_terms_checklist()
	 * @since 2.5.1
	 */
	class FPW_Walker_Category_Checklist extends Walker_Category_Checklist {
	
		/**
		 * Start the element output.
		 *
		 * @see Walker::start_el()
		 *
		 * @since 2.5.1
		 *
		 * @param string $output   Passed by reference. Used to append additional content.
		 * @param object $category The current term object.
		 * @param int    $depth    Depth of the term in reference to parents. Default 0.
		 * @param array  $args     An array of arguments. @see wp_terms_checklist()
		 * @param int    $id       ID of the current term.
		 */
		public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
			if ( empty( $args['taxonomy'] ) ) {
				$taxonomy = 'category';
			} else {
				$taxonomy = $args['taxonomy'];
			}
	
			if ( $taxonomy == 'category' ) {
				$name = 'post_category';
			} else {
				$name = 'tax_input[' . $taxonomy . ']';
			}
			$args['popular_cats'] = empty( $args['popular_cats'] ) ? array() : $args['popular_cats'];
			$class = in_array( $category->slug, $args['popular_cats'] ) ? ' class="popular-category"' : '';
	
			$args['selected_cats'] = empty( $args['selected_cats'] ) ? array() : $args['selected_cats'];
	
			/** This filter is documented in wp-includes/category-template.php */
			$output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" .
				'<label class="selectit"><input value="' . $category->slug . '" type="checkbox" name="'.$id.'[]" id="in-'.$taxonomy.'-' . $category->slug . '"' .
				checked( in_array( $category->slug, $args['selected_cats'] ), true, false ) .
				disabled( empty( $args['disabled'] ), false, false ) . ' /> ' .
				esc_html( apply_filters( 'the_category', $category->name ) ) . '</label>';
		}
	}
	
	
}