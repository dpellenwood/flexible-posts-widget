<?php
/**
 * Flexible Posts Widget: Widget Admin Form 
 */

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');

?>
<div class="dpe-fp-widget">

	<div class="section title">
        <p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget title:', 'flexible-posts-widget'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
	</div>
    
    <div class="section getemby">
		<h4><?php _e('Get posts by', 'flexible-posts-widget'); ?></h4>
		<div class="inside">
		
			<div id="<?php echo $this->get_field_id('getemby'); ?>" class="categorydiv getembytabs">
				
				<input id="<?php echo $this->get_field_id('cur_tab'); ?>" class="cur_tab" name="<?php echo $this->get_field_name('cur_tab'); ?>" type="hidden" value="<?php echo $cur_tab; ?>" />
				
				<ul id="<?php echo $this->get_field_id('getemby-tabs'); ?>" class="category-tabs">
					<li><a title="<?php _e('Post Type', 'flexible-posts-widget'); ?>" href="#<?php echo $this->get_field_id('getemby-pt'); ?>"><?php _e('Post Type', 'flexible-posts-widget'); ?></a></li>
					<li><a title="<?php _e('Taxonomy &amp; Term', 'flexible-posts-widget'); ?>" href="#<?php echo $this->get_field_id('getemby-tt'); ?>"><?php _e('Taxonomy &amp; Term', 'flexible-posts-widget'); ?></a></li>
					<li><a title="<?php _e('Post ID', 'flexible-posts-widget'); ?>" href="#<?php echo $this->get_field_id('getemby-id'); ?>"><?php _e('ID', 'flexible-posts-widget'); ?></a></li>
				</ul>
				
				<div id="<?php echo $this->get_field_id('getemby-pt'); ?>" class="tabs-panel pt">
					<?php $this->posttype_checklist( $posttype ); ?>
				</div><!-- .pt.getemby -->
				
				<div id="<?php echo $this->get_field_id('getemby-tt'); ?>" class="tabs-panel tt" style="display:none;">
					<p>	
						<label for="<?php echo $this->get_field_id('taxonomy'); ?>"><?php _e('Select a taxonomy:', 'flexible-posts-widget'); ?></label> 
						<select class="widefat dpe-fp-taxonomy" name="<?php echo $this->get_field_name('taxonomy'); ?>" id="<?php echo $this->get_field_id('taxonomy'); ?>">
							<option value="none" <?php echo 'none' == $taxonomy ? ' selected="selected"' : ''; ?>><?php _e('Ignore Taxonomy &amp; Term', 'flexible-posts-widget'); ?></option>
							<?php
							foreach ($this->taxonomies as $option) {
								echo '<option value="' . $option->name . '"', $taxonomy == $option->name ? ' selected="selected"' : '', '>', $option->label, '</option>';
							}
							?>
						</select>		
					</p>
					<label <?php echo 'none' == $taxonomy ? ' style="display:none;"' : ''; ?>><?php _e('Select terms:', 'flexible-posts-widget'); ?></label> 
					<div class="terms" <?php echo 'none' == $taxonomy ? ' style="display:none;"' : ''; ?>>
						<?php
							if ( !empty($taxonomy) && 'none' != $taxonomy ) {
							
								$args = array (
									'hide_empty' => 0,
								);
								
								$terms = get_terms( $taxonomy, $args );
								
								if( !empty( $terms ) ) {
									$output = '<ul class="categorychecklist termschecklist form-no-clear">';
									foreach ( $terms as $option ) {
										$output .= "\n<li>" . '<label class="selectit"><input value="' . esc_attr( $option->slug ) . '" type="checkbox" name="' . $this->get_field_name('term') . '[]"' . checked( in_array( $option->slug, (array)$term ), true, false ) . ' /> ' . esc_html( $option->name ) . "</label></li>\n";
									}
									$output .= "</ul>\n";
								} else {
									$output = '<p>' . __('No terms found.', 'flexible-posts-widget') . '</p>';
								}
								
								echo ( $output );
							}
						?>
					</div>
				</div><!-- .tt.getemby -->
				
				<div id="<?php echo $this->get_field_id('getemby-id'); ?>" class="tabs-panel id" style="display:none;">
					<p>	
						<label for="<?php echo $this->get_field_id('pids'); ?>"><?php _e('Comma-separated list of post IDs:', 'flexible-posts-widget'); ?></label><br />
						<input id="<?php echo $this->get_field_id('pids'); ?>" name="<?php echo $this->get_field_name('pids'); ?>" class="widefat" type="text" value="<?php echo ( empty( $pids ) ? '' : implode( ',', $pids ) ); ?>" /><br />
						<span class="description"><?php _e( 'Will override settings on the Post Type and Taxonomy &amp; Term tabs.', 'flexible-posts-widget' ); ?> <a target="_blank" href="http://wordpress.org/extend/plugins/flexible-posts-widget/faq/"><?php _e('See documentation.', 'flexible-posts-widget'); ?></a></span>
					</p>
				</div><!-- .id.getemby -->
			
			</div><!-- #<?php echo $this->get_field_id('getemby'); ?> -->
			
		</div><!-- .inside -->
	
	</div>
	
	<div class="section display">
		<h4><?php _e('Display options', 'flexible-posts-widget'); ?></h4>
		<p class="check cf">
          <input class="dpe-fp-sticky" id="<?php echo $this->get_field_id('sticky'); ?>" name="<?php echo $this->get_field_name('sticky'); ?>" type="checkbox" value="1" <?php checked( '1', $sticky ); ?>/>
          <label for="<?php echo $this->get_field_id('sticky'); ?>"><?php _e('Ignore sticky posts?', 'flexible-posts-widget'); ?></label> 
        </p>
		<p class="cf">
          <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:', 'flexible-posts-widget'); ?></label> 
          <input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" />
        </p>
		<p class="cf">
          <label for="<?php echo $this->get_field_id('offset'); ?>"><?php _e('Number of posts to skip:', 'flexible-posts-widget'); ?></label> 
          <input id="<?php echo $this->get_field_id('offset'); ?>" name="<?php echo $this->get_field_name('offset'); ?>" type="text" value="<?php echo $offset; ?>" />
        </p>
   		<p class="cf">
			<label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order posts by:', 'flexible-posts-widget'); ?></label> 
			<select name="<?php echo $this->get_field_name('orderby'); ?>" id="<?php echo $this->get_field_id('orderby'); ?>">
				<?php
				foreach ( $this->orderbys as $key => $value ) {
					echo '<option value="' . $key . '" id="' . $this->get_field_id( $key ) . '"', $orderby == $key ? ' selected="selected"' : '', '>', $value, '</option>';
				}
				?>
			</select>		
		</p>
		<p class="cf">
			<label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Order:', 'flexible-posts-widget'); ?></label> 
			<select name="<?php echo $this->get_field_name('order'); ?>" id="<?php echo $this->get_field_id('order'); ?>">
				<?php
				foreach ( $this->orders as $key => $value ) {
					echo '<option value="' . $key . '" id="' . $this->get_field_id( $key ) . '"', $order == $key ? ' selected="selected"' : '', '>', $value, '</option>';
				}
				?>
			</select>		
		</p>
	</div>
	
	<div class="section thumbnails">
		<p class="check">
          <input class="dpe-fp-thumbnail" id="<?php echo $this->get_field_id('thumbnail'); ?>" name="<?php echo $this->get_field_name('thumbnail'); ?>" type="checkbox" value="1" <?php checked( '1', $thumbnail ); ?>/>
          <label style="font-weight:bold;" for="<?php echo $this->get_field_id('thumbnail'); ?>"><?php _e('Display thumbnails?', 'flexible-posts-widget'); ?></label> 
        </p>
		<p <?php echo $thumbnail ? '' : 'style="display:none;"'?>  class="thumb-size">	
			<label for="<?php echo $this->get_field_id('thumbsize'); ?>"><?php _e('Select a thumbnail size to show:', 'flexible-posts-widget'); ?></label> 
			<select class="widefat" name="<?php echo $this->get_field_name('thumbsize'); ?>" id="<?php echo $this->get_field_id('thumbsize'); ?>">
				<?php
				foreach ($this->thumbsizes as $option) {
					echo '<option value="' . $option . '" id="' . $this->get_field_id( $option ) . '"', $thumbsize == $option ? ' selected="selected"' : '', '>', $option, '</option>';
				}
				?>
			</select>		
		</p>
	</div>
	
	<div class="section template">
		<p style="margin:1.33em 0;">
			<label for="<?php echo $this->get_field_id('template'); ?>"><?php _e('Template filename:', 'flexible-posts-widget'); ?></label>
			<input id="<?php echo $this->get_field_id('template'); ?>" name="<?php echo $this->get_field_name('template'); ?>" type="text" value="<?php echo $template; ?>" />
			<br />
			<span style="padding-top:3px;" class="description"><a target="_blank" href="http://wordpress.org/extend/plugins/flexible-posts-widget/other_notes/"><?php _e('See documentation for details.', 'flexible-posts-widget'); ?></a></span>
		</p>
	</div>
	
</div><!-- .dpe-fp-widget -->
