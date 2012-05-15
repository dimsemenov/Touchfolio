<?php
/**
 * 
 * Template Name: Masonry Gallery Page
 * 
 * The template for displaying gallery page with masonry layout.
 *
 * @package dsframework
 * @since dsframework 1.0
 * 
 */
?>
<?php get_header(); ?>
<div id="primary" class="site-content">
	<div id="content" role="main">
		<?php 
		$gallery_cats = get_ds_option('album_cats_gallery_page');
		if($gallery_cats) {
			$tax_query = array(               
		    	'relation' => 'AND',                  
				array(
					'taxonomy' => 'ds-gallery-category',     
					'field' => 'slug',                
					'terms' => preg_split("/\s*,\s*/", $gallery_cats),
					'include_children' => true,         
					'operator' => 'IN'                 
				)
		    );
		} else {
			$tax_query = '';
		}
		$loop = new WP_Query( array( 
			'post_type' => 'ds-gallery', 
			'posts_per_page' => -1,
			'tax_query' => $tax_query
		));
		?>
		<section class="albums-thumbnails clearfix">
		<?php while ( $loop->have_posts() ) : $loop->the_post();  ?>
			<a class="project-thumb" href="<?php the_permalink(); ?>" data-album-id="<?php echo $post->post_name; ?>">
				<div class="project-thumb-inside">
					<?php 
					if ( has_post_thumbnail($post->ID) ) {
						the_post_thumbnail( 'gallery-thumb' );
					} else {
						$post_meta = get_post_custom();
						$post_meta = unserialize( $post_meta['dsframework-gallery'][0] );
						if( isset( $post_meta['attachment_urls'] ) ) {
							$image_urls = $post_meta['attachment_ids'];
							echo wp_get_attachment_image( $image_urls[0], 'gallery-thumb' );
						} else {
							echo '<div style="width: 360px; height: 250px; background: grey;">' . __('Album images not found.', 'dsframework') . '</div>';
						}
					}
					?>
					<h4 class="project-title"><?php the_title(); ?></h4>
					<p class="project-description"><?php echo get_the_excerpt(); ?></p>
				</div>
			</a>
		<?php endwhile; ?>
		</section>
		<?php wp_reset_postdata(); ?>
	</div>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>