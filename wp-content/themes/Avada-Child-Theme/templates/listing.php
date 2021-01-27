<div class="bloglist-holder view-of-archive<?=(is_search())?' search-results':''?>">
  <div class="wrapper">
    <?php $i = 0; while(have_posts()): ?>
      <?php
      $i++;
      the_post();
      $pid = get_the_ID();
      $img = get_the_post_thumbnail_url(get_the_ID());
      $img = (empty($img)) ? IMG.'/no-image.png': $img;
      $ts = get_the_tags($pid);
      ?>
      <div class="cikk">
        <div class="wrapper">
          <div class="image autocorrett-height-by-width" data-image-ratio="4:3"><a href="<?php echo the_permalink(); ?>"><img src="<?=$img?>" alt="<? echo the_title(); ?>" title="<? echo the_title(); ?>"></a></div>
          <div class="content">
            <?php if ($ts): ?>
              <?php $tsx = ''; foreach ((array)$ts as $t): $tsx .= '<a href="/tag/'.$t->slug.'">'.ucfirst($t->name).'</a>, '; endforeach; $tsx = rtrim($tsx, ', '); ?>
              <div class="author"><?php echo $tsx; ?></div>
            <?php else: ?>
              <div class="author">&nbsp;</div>
            <?php endif; ?>
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
    <?php if (!have_posts()): ?>
      <div class="no-posts">
        <?php echo __('Nincs jelenleg megjeleníthető cikk.'); ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="pagination">
  <?php echo the_posts_pagination(); ?>
</div>
