<div class="wrapper">
  <?php if ($posts->have_posts()): ?>
  <?php if ($cat): ?>
  <div class="header">
    <h3><?php echo $cat->name; ?><br><?=__('további cikkei', 'hc')?></h3>
  </div>
  <?php endif; ?>
  <div class="simple-sidebar-posts">
  <?php while ($posts->have_posts()): $posts->the_post(); ?>
  <div class="article">
    <div class="wrapper">
      <div class="content">
        <div class="title"><a href="<?php echo the_permalink(); ?>"><?php echo the_title(); ?></a></div>
        <div class="desc"><?php echo the_excerpt(); ?></div>
        <div class="published"><?php echo get_the_date(); ?></div>
        <div class="nextbtn">
          <a href="<?php echo the_permalink(); ?>"><?=__('Tovább')?></a>
        </div>
      </div>
    </div>
  </div>
  <?php endwhile; wp_reset_postdata(); ?>
  </div>
  <?php endif; ?>
  <?php if ($cat): ?>
  <div class="footer">
    <a href="<?php echo get_category_link($cat); ?>" class="grad-button"><?=__('Tovább a(z)', 'hc')?> <?php echo $cat->name; ?> <?=__('cikkeihez', 'hc')?></a>
  </div>
  <?php endif; ?>
</div>
