<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package dsframework
 * @since dsframework 1.0
 */
get_header(); ?>
		<div id="primary" class="site-content">
			<div id="content" role="main">
			<?php if ( have_posts() ) : ?>
				<?php dsframework_content_nav( 'nav-above' ); ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'content', get_post_format() ); ?>
				<?php endwhile; ?>
				<?php dsframework_content_nav( 'nav-below' ); ?>
			<?php elseif ( current_user_can( 'edit_posts' ) ) : ?>
				<article id="post-0" class="post no-results not-found">
					<header class="entry-header">
						<h1 class="entry-title"><?php _e( 'No posts to display', 'dsframework' ); ?></h1>
					</header>
					<div class="entry-content">
						<p><?php printf( __( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'dsframework' ), admin_url( 'post-new.php' ) ); ?></p>
					</div>
				</article>
			<?php endif; ?>
			</div>
		</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>