<div class="wrapper">
  <?php foreach ((array)$slides as $s):
    $post_id = $s->ID;
    $logo_id = get_post_meta($post_id, METAKEY_PREFIX.'szoftver_logo_id', true);
    $logo = ($logo_id) ? wp_get_attachment_url($logo_id) : false;
    $subtitle = get_post_meta($post_id, METAKEY_PREFIX.'szoftver_subtitle', true);

    $metakey = METAKEY_PREFIX . 'programcontents_set';
    $set = unserialize(get_post_meta($post_id, $metakey, true));
    ?>
    <div class="softver">
      <div class="wrapper">

        <div class="title">
          <?php if ($logo_id && $logo): ?>
            <img src="<?=$logo?>" class="logo" alt="<?php echo $s->post_title; ?>">
          <?php endif; ?>
          <h2><?php echo $s->post_title; ?></h2>
          <?php if ($subtitle): ?>
          <h3><?php echo $subtitle; ?></h3>
          <?php endif; ?>
        </div>

        <div class="content-data">
          <div class="cover-img">
            <img src="<?php echo get_the_post_thumbnail_url($post_id); ?>" alt="<?php echo $s->post_title; ?>">
          </div>
          <?php
            $metakey = METAKEY_PREFIX . 'moduls_set';
            $moduls = unserialize(get_post_meta($post_id, $metakey, true));
          ?>
          <?php if (count($moduls) > 0): ?>
          <div class="hlmoduls">
            <?php foreach ((array)$moduls as $modul):
              $ct_slug = sanitize_title($modul);
              $ct_slug_safe = str_replace(array('-'),array('_'),$ct_slug);
              $modul_items = unserialize(get_post_meta($post_id, METAKEY_PREFIX.'moduls_'.$ct_slug_safe, true));
            ?>
            <?php $mi = -1; foreach ((array)$modul_items['title'] as $m): $mi++; if($modul_items['kiemelt'][$mi] != '1') continue; ?>
              <div class="modul-elem">
                <div class="t"><i class="fa fa-plus-square"></i> <?=stripslashes($modul_items['title'][$mi])?></div>
                <div class="sdesc"><?=stripslashes($modul_items['shortdesc'][$mi])?> <a href="<?=get_the_permalink($post_id)?>#<?=$ct_slug_safe?>_<?=str_replace(array('-'),array('_'),stripslashes(sanitize_title($modul_items['title'][$mi])));?>" title="Részletek olvasása"><i class="fa fa-external-link"></i></a></div>
              </div>
            <?php endforeach; ?>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <div class="excerpt">
            <h4>Rövid ismertető</h4>
            <?php echo get_the_excerpt($post_id); ?>
            <div class="readmore">
              <a href="<?=get_the_permalink($post_id)?>">További információk <i class="fa fa-arrow-circle-o-right"></i> </a>
            </div>
          </div>
        </div>

      </div>
    </div>
  <?php endforeach; ?>
</div>
<script type="text/javascript">
(function($){
  $('#main > .fusion-row').css({
    width: '100%',
    maxWidth: '100%'
  });
  $('.hlmoduls .modul-elem > .t').click(function(ev){
    ev.preventDefault();
    ev.stopPropagation();
    $('.hlmoduls .modul-elem').removeClass('opened');
    $('.hlmoduls .modul-elem .t i').removeClass('fa-minus-square').addClass('fa-plus-square');
    var opened = $(this).parent().hasClass('opened');
    if ( !opened ) {
      $(this).parent().addClass('opened');
      $(this).find('i').removeClass('fa-plus-square').addClass('fa-minus-square');
    } else {
      $(this).parent().removeClass('opened');
      $(this).find('i').removeClass('fa-minus-square').addClass('fa-plus-square');
    }
  });

  $('.szoftver-blocklist-holder > .wrapper').slick({
    infinite: true,
    slidesToShow: 1,
    slidesToScroll: 1,
    dots: false,
    arrows: true,
    autoplay: true,
    delay: 5000,
    speed: 1000,
    adaptiveHeight: true
  });
})(jQuery)
</script>
