<div class="more"><a href="<?=$cat_link?>"><?=__('Összes')?></a></div>
<div class="wrapper">
  <?php if ($posts->have_posts()): ?>
    <?php  while ( $posts->have_posts() ) {
      $posts->the_post();
      $img = get_the_post_thumbnail_url(get_the_ID());
      $img = (empty($img)) ? IMG.'/no-image.png': $img;
    ?>
    <div class="cikk">
      <div class="wrapper">
        <div class="image autocorrett-height-by-width" data-image-ratio="4:3"><a href="<?php echo the_permalink(); ?>"><img src="<?=$img?>" alt="<? echo the_title(); ?>" title="<? echo the_title(); ?>"></a></div>
        <div class="content">
          <div class="author"><?=__('Szerző')?>: <?php echo get_the_author_meta('display_name'); ?></div>
          <div class="title"><a href="<?php echo the_permalink(); ?>"><?php echo the_title(); ?></a></div>
          <div class="desc"><?php echo the_excerpt(); ?></div>
          <div class="nextbtn">
            <a href="<?php echo the_permalink(); ?>"><?=__('Tovább')?></a>
          </div>
        </div>
      </div>
    </div>
    <? } ?>
  <?php wp_reset_postdata();?>
<?php else: ?>
  <div class="no-posts">
    <?php echo __('Nincs jelenleg megjeleníthető cikk.'); ?>
  </div>  
<?php endif; ?>
</div>
