<?php
/**
 * Flexible Posts Widget: Widget Admin Form 
 */

// Block direct requests
if ( !defined( 'ABSPATH' ) )
	die( '-1' );

?>
<div class="dpe-fp-widget">

	<div class="section title">
        <p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget title:', $this->widget_text_domain ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
        </p>
	</div>
    
    <div class="section getemby">
		<h4><?php _e( 'Get posts by', $this->widget_text_domain ); ?></h4>
		<div class="inside">
		
			<div id="<?php echo $this->get_field_id( 'getemby' ); ?>" class="categorydiv getembytabs">
				
				<input id="<?php echo $this->get_field_id( 'cur_tab' ); ?>" class="cur_tab" name="<?php echo $this->get_field_name( 'cur_tab' ); ?>" type="hidden" value="<?php echo $instance['cur_tab']; ?>" />
				
				<ul id="<?php echo $this->get_field_id( 'getemby-tabs' ); ?>" class="category-tabs">
					<li><a title="<?php _e( 'Post Type', $this->widget_text_domain ); ?>" href="#<?php echo $this->get_field_id( 'getemby-pt' ); ?>"><?php _e( 'Post Type', $this->widget_text_domain ); ?></a></li>
					<li><a title="<?php _e( 'Taxonomy &amp; Term', $this->widget_text_domain ); ?>" href="#<?php echo $this->get_field_id( 'getemby-tt' ); ?>"><?php _e( 'Taxonomy &amp; Term', $this->widget_text_domain ); ?></a></li>
					<li><a title="<?php _e( 'Post ID', $this->widget_text_domain ); ?>" href="#<?php echo $this->get_field_id( 'getemby-id' ); ?>"><?php _e( 'ID', $this->widget_text_domain ); ?></a></li>
				</ul>
				
				<div id="<?php echo $this->get_field_id( 'getemby-pt' ); ?>" class="tabs-panel pt">
					<?php $this->posttype_checklist( $instance['posttype'] ); ?>
				</div><!-- .pt.getemby -->
				
				<div id="<?php echo $this->get_field_id( 'getemby-tt' ); ?>" class="tabs-panel tt" style="display:none;">
					<p>	
						<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e( 'Select a taxonomy:', $this->widget_text_domain ); ?></label> 
						<select class="widefat dpe-fp-taxonomy" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>">
							<option value="none" <?php echo 'none' == $instance['taxonomy'] ? ' selected="selected"' : ''; ?>><?php _e( 'Ignore Taxonomy &amp; Term', $this->widget_text_domain ); ?></option>
							<?php
							foreach ($this->taxonomies as $option) {
								echo '<option value="' . $option->name . '"', $instance['taxonomy'] == $option->name ? ' selected="selected"' : '', '>', $option->label, '</option>';
							}
							?>
						</select>		
					</p>
					<label <?php echo 'none' == $instance['taxonomy'] ? ' style="display:none;"' : ''; ?>><?php _e( 'Select terms:', $this->widget_text_domain ); ?></label> 
					<div class="terms" <?php echo 'none' == $instance['taxonomy'] ? ' style="display:none;"' : ''; ?>>
						<?php
							if( 'none' != $instance['taxonomy'] ) {
								$this->terms_checklist( $instance['taxonomy'], $instance['term'] );
							}
						?>
					</div>
				</div><!-- .tt.getemby -->
				
				<div id="<?php echo $this->get_field_id( 'getemby-id' ); ?>" class="tabs-panel id" style="display:none;">
					<p>	
						<label for="<?php echo $this->get_field_id( 'pids' ); ?>"><?php _e( 'Comma-separated list of post IDs:', $this->widget_text_domain ); ?></label><br />
						<input id="<?php echo $this->get_field_id( 'pids' ); ?>" name="<?php echo $this->get_field_name( 'pids' ); ?>" class="widefat" type="text" value="<?php echo ( empty( $instance['pids'] ) ? '' : implode( ',', $instance['pids'] ) ); ?>" /><br />
						<span class="description"><?php _e( 'Will override settings on the Post Type and Taxonomy &amp; Term tabs.', $this->widget_text_domain  ); ?> <a target="_blank" href="http://wordpress.org/extend/plugins/flexible-posts-widget/faq/"><?php _e( 'See documentation.', $this->widget_text_domain ); ?></a></span>
					</p>
				</div><!-- .id.getemby -->
			
			</div><!-- #<?php echo $this->get_field_id( 'getemby' ); ?> -->
			
		</div><!-- .inside -->
	
	</div>
	
	<div class="section display">
		<h4><?php _e( 'Display options', $this->widget_text_domain ); ?></h4>
		<p class="check cf">
          <input class="dpe-fp-sticky" id="<?php echo $this->get_field_id( 'sticky' ); ?>" name="<?php echo $this->get_field_name( 'sticky' ); ?>" type="checkbox" value="1" <?php checked( '1', $instance['sticky'] ); ?>/>
          <label for="<?php echo $this->get_field_id( 'sticky' ); ?>"><?php _e( 'Ignore sticky posts?', $this->widget_text_domain ); ?></label> 
        </p>
		<p class="cf">
          <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:', $this->widget_text_domain ); ?></label> 
          <input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $instance['number']; ?>" />
        </p>
		<p class="cf">
          <label for="<?php echo $this->get_field_id( 'offset' ); ?>"><?php _e( 'Number of posts to skip:', $this->widget_text_domain ); ?></label> 
          <input id="<?php echo $this->get_field_id( 'offset' ); ?>" name="<?php echo $this->get_field_name( 'offset' ); ?>" type="text" value="<?php echo $instance['offset']; ?>" />
        </p>
   		<p class="cf">
			<label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e( 'Order posts by:', $this->widget_text_domain ); ?></label> 
			<select name="<?php echo $this->get_field_name( 'orderby' ); ?>" id="<?php echo $this->get_field_id( 'orderby' ); ?>">
				<?php
				foreach ( $this->orderbys as $key => $value ) {
					echo '<option value="' . $key . '" id="' . $this->get_field_id( $key ) . '"', $instance['orderby'] == $key ? ' selected="selected"' : '', '>', $value, '</option>';
				}
				?>
			</select>		
		</p>
		<p class="cf">
			<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Order:', $this->widget_text_domain ); ?></label> 
			<select name="<?php echo $this->get_field_name( 'order' ); ?>" id="<?php echo $this->get_field_id( 'order' ); ?>">
				<?php
				foreach ( $this->orders as $key => $value ) {
					echo '<option value="' . $key . '" id="' . $this->get_field_id( $key ) . '"', $instance['order'] == $key ? ' selected="selected"' : '', '>', $value, '</option>';
				}
				?>
			</select>		
		</p>
	</div>
	
	<div class="section thumbnails">
		<p class="check">
          <input class="dpe-fp-thumbnail" id="<?php echo $this->get_field_id( 'thumbnail' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail' ); ?>" type="checkbox" value="1" <?php checked( '1', $instance['thumbnail'] ); ?>/>
          <label style="font-weight:bold;" for="<?php echo $this->get_field_id( 'thumbnail' ); ?>"><?php _e( 'Display thumbnails?', $this->widget_text_domain ); ?></label> 
        </p>
		<p <?php echo $instance['thumbnail'] ? '' : 'style="display:none;"'?>  class="thumb-size">	
			<label for="<?php echo $this->get_field_id( 'thumbsize' ); ?>"><?php _e( 'Select a thumbnail size to show:', $this->widget_text_domain ); ?></label> 
			<select class="widefat" name="<?php echo $this->get_field_name( 'thumbsize' ); ?>" id="<?php echo $this->get_field_id( 'thumbsize' ); ?>">
				<?php
				foreach ($this->thumbsizes as $option) {
					echo '<option value="' . $option . '" id="' . $this->get_field_id( $option ) . '"', $instance['thumbsize'] == $option ? ' selected="selected"' : '', '>', $option, '</option>';
				}
				?>
			</select>		
		</p>
	</div>
	
	<div class="section templates">
		<p>
			<label for="<?php echo $this->get_field_id( 'template' ); ?>"><?php _e( 'Template filename:', $this->widget_text_domain ); ?></label>
			<?php 
			?>
			<select class="widefat" name="<?php echo $this->get_field_name( 'template' ); ?>" id="<?php echo $this->get_field_id( 'template' ); ?>">
				<?php
				foreach ($this->templates as $key => $value ) {
					echo '<option value="' . $key . '" id="' . $this->get_field_id( $key ) . '"', $instance['template'] == $key ? ' selected="selected"' : '', '>', ucwords( preg_replace( array( '/-/', '/_/' ), ' ', preg_replace( '/.php$/', '', $key ) ) ), '</option>';
				}
				?>
			</select>		
		</p>
	</div>
	
</div><!-- .dpe-fp-widget -->
