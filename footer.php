<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package dsframework
 * @since dsframework 1.0
 */
?>
  </div>
  <div class="push"></div> <?php // this semantics is such a boring thing ?>
</div>
<footer class="footer-copy">
      <p><?php echo get_ds_option('footer_text'); ?></p>
</footer>
</div>
<?php echo get_ds_option('google_analytics'); ?>
<?php wp_footer(); ?>
</body>
</html>

