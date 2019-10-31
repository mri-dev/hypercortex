<div class="wrapper">
  <?php if ($posts->have_posts()): ?>
    <?php  while ( $posts->have_posts() ) {
      $posts->the_post();
      $img = get_the_post_thumbnail_url(get_the_ID()); echo $img; ?>

    <? } ?>
  <?php wp_reset_postdata(); endif; ?>
</div>
