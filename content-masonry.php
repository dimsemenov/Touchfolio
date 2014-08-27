<?php
/**
 * The template used for displaying gallery thumbnail in ds-gallery-masonry-template.php and in ds-gallery-categories-navigation-template.php
 *
 * @package dsframework
 * @since dsframework 1.0
 */
?>
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