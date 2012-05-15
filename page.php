<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package dsframework
 * @since dsframework 1.0
 */

get_header(); ?>
		<div id="primary" class="site-content">
			<div id="content" role="main">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'content', 'page' ); ?>
				<?php endwhile; ?>
			</div>
		</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>