<div class="wrapper">
  <?php $bcount = count($blocks); ?>
  <?php foreach ((array)$blocks as $s):
    $post_id = $s->ID;
    ?>
    <div class="block" style="flex-basis:<?=100/$bcount?>%;">
      <div class="wrapper autocorrett-height-by-width" data-image-ratio="1:1">
        <a href="<?=get_the_permalink($post_id)?>">
          <div class="inside">
            <?php if (file_exists(get_stylesheet_directory().'/images/ico/ico-'.$s->post_name.'.svg')): ?>
            <div class="ico">
              <img src="<?=IMG?>/ico/ico-<?=$s->post_name?>.svg" alt="<?php echo $s->post_title; ?>">
            </div>
            <?php endif; ?>
            <div class="title"><h2><?php echo $s->post_title; ?></h2></div>
          </div>
        </a>
      </div>
    </div>
  <?php endforeach; ?>
</div>
