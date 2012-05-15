<?php
/**
 * The Template for displaying all single posts.
 *
 * @package dsframework
 * @since dsframework 1.0
 */

get_header(); ?>
		<div id="primary" class="site-content">
			<div id="content" role="main">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php dsframework_content_nav( 'nav-above' ); ?>
				<?php get_template_part( 'content', 'single' ); ?>
				<?php dsframework_content_nav( 'nav-below' ); ?>
				<?php
					if ( comments_open() || '0' != get_comments_number() )
						comments_template( '', true );
				?>
			<?php endwhile; ?>
			</div>
		</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>