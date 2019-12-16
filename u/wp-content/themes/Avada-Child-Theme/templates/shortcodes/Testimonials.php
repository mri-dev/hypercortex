<div class="wrapper">
  <?php if ($posts->have_posts()): ?>
    <div class="partnerek">
    <?php  while ( $posts->have_posts() ) {
      $posts->the_post();
      $img = get_the_post_thumbnail_url(get_the_ID());
      $website = get_post_meta( get_the_ID(), 'website', true);
    ?>
    <div class="partner">
      <?php if (!empty($website)): ?><a href="<?=$website?>" target="_blank"><?php endif; ?>
      <img src="<?=$img?>" alt="<? echo the_title(); ?>" title="<? echo the_title(); ?>">
      <?php if (!empty($website)): ?></a><?php endif; ?>
    </div>
    <? } ?>
  </div>
  <script type="text/javascript">
    (function($){
      $(function(){
        $('.partnerek').slick({
          infinite: true,
          slidesToShow: 6,
          slidesToScroll: 1,
          arrows: true
        });
      });
    })(jQuery);
  </script>
  <?php wp_reset_postdata();?>
<?php endif; ?>
</div>
