<?php
/**
 * The Template for displaying gallery (albums) post type
 *
 * @package dsframework
 * @since dsframework 1.0
 */

get_header(); ?>
		<div id="primary" class="site-content">
			<div id="content" role="main">

				<div id="main-slider" class="two-dim-slider">
					<div class="slider-data">
						<ul class="two-dim-albums-list">

							<?php while ( have_posts() ) : the_post(); ?>
							<?php echo 'dima'; global $wp_query; ?>
								<?php get_template_part( 'content', 'ds-gallery' ); ?>
								<div class="gallery-posts-navigation">
									<?php previous_post_link('%link', $wp_query->max_num_pages); ?>
									<?php next_post_link('%link', $wp_query->max_num_pages); ?>
								</div>

							<?php endwhile; ?>
							<?php wp_reset_postdata(); ?>
						</ul>
					</div>
				</div>

			</div><!-- #content -->
		</div><!-- #primary .site-content -->
<?php get_footer(); ?>