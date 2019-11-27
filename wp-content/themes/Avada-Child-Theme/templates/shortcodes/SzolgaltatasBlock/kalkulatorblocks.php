<?php global $post; ?>
<div class="wrapper">
  <?php foreach ((array)$blocks as $s):
    $post_id = $s->ID;
    ?>
    <div class="block <?=($post->ID == $post_id)?'active':''?>">
      <div class="wrapper autocorrett-height-by-width" data-image-ratio="5:3">
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
