<div class="wrapper">
  <?php $bcount = count($blocks); ?>
  <?php foreach ((array)$blocks as $s):
    $post_id = $s->ID;
    ?>
    <div class="block" style="flex-basis:<?=100/$bcount?>%;">
      <div class="wrapper autocorrett-height-by-width" data-image-ratio="1:1">
        <a href="<?=get_the_permalink($post_id)?>">
          <div class="inside">
            <div class="title"><h2><?php echo $s->post_title; ?></h2></div>
          </div>          
        </a>
      </div>
    </div>
  <?php endforeach; ?>
</div>
