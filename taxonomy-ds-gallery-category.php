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
								<?php get_template_part( 'content', 'ds-gallery' ); ?>
							<?php endwhile; ?>
							<?php wp_reset_postdata(); ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
<?php get_footer(); ?>