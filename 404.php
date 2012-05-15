<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package dsframework
 * @since dsframework 1.0
 */
?>
<?php get_header(); ?>
	<div id="primary" class="site-content">
		<div id="content" role="main">
			<article id="post-0" class="post error404 not-found">
				<header class="entry-header">
					<h1 class="entry-title">404</h1>
				</header>
				<p><?php _e('The page you were looking for cannot be found.', 'dsframework'); ?></p>
			</article>
		</div>
	</div>
<?php get_footer(); ?>