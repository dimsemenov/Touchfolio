<?php
/**
 * The template used for displaying page text in ds-gallery-masonry-template.php and in ds-gallery-categories-navigation-template.php
 *
 * @package dsframework
 * @since dsframework 1.0
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-content">
		<h3><?php the_content(); ?></h3>
	</div>
</article>