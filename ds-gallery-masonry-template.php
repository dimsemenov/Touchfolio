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
			<?php get_template_part( 'content', 'masonry' ); ?>
		<?php endwhile; ?>
		</section>
		<?php wp_reset_postdata(); ?>
		<?php get_template_part( 'content', 'page-masonry' ); ?>
	</div>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>