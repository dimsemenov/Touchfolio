<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package dsframework
 * @since dsframework 1.0
 */
?>

		<div id="secondary" class="widget-area" role="complementary">
			<?php if ( is_active_sidebar( 'regular-page-sidebar' ) ) { ?>
				<span class="menu-sep">&mdash;</span>
				<?php do_action( 'before_sidebar' ); ?>
				<?php if ( ! dynamic_sidebar( 'regular-page-sidebar' ) ) : ?>
				<?php endif; ?>
			<?php } ?>
		</div>