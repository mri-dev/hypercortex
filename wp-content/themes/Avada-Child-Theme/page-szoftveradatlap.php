<?php
/* Template Name: Szoftver adatlap */
// Do not allow directly accessing this file.

  global $post;

  if ( ! defined( 'ABSPATH' ) ) {
   exit( 'Direct script access denied.' );
  }

  $metakey = METAKEY_PREFIX . 'programcontents_set';
  $set = unserialize(get_post_meta($post->ID, $metakey, true));
  $subtitle = get_post_meta($post->ID, METAKEY_PREFIX.'szoftver_subtitle', true);
  $logo_id = get_post_meta($post->ID, METAKEY_PREFIX.'szoftver_logo_id', true);
  $logo = ($logo_id) ? wp_get_attachment_url($logo_id) : false;

  $logo_id = get_post_meta($post->ID, METAKEY_PREFIX.'szoftver_logo_id', true);

  $feat_images = array();
  for ($i=2; $i<=5 ; $i++) {
    $feat_image_id = get_post_meta($post->ID,'kd_featured-image-'.$i.'_page_id', true);
    if ($feat_image_id == 0 || empty($feat_image_id)) continue;
    $feat_img = wp_get_attachment_url($feat_image_id);
    if ($feat_img) {
      $feat_images[$i] = $feat_img;
    }
  }
?>
<?php get_header(); ?>
<section id="content" class="full-width">
<?php while ( have_posts() ) : ?>
  <?php the_post(); ?>
  <div class="szoftver-adatlap adatlap-page">
    <div class="wrapper">
      <div class="szofver-adatlap-holder">
        <div class="fusion-row">
          <div class="adatlap-top">
            <div class="image-list">
              <div class="cover-img">
                <a href="<?php echo get_the_post_thumbnail_url(get_the_ID()); ?>"rel="iLightbox"><img src="<?php echo get_the_post_thumbnail_url(get_the_ID()); ?>" alt="<?php echo the_title(); ?>"></a>
              </div>
              <?php if ($feat_images): ?>
              <div class="images">
                <?php foreach ((array)$feat_images as $img ): ?>
                <div class="img"><a href="<?=$img?>" rel="iLightbox"><img src="<?=$img?>" alt="<?php echo the_title(); ?>"></a></div>
                <?php endforeach; ?>
              </div>
              <script type="text/javascript">
                (function($){
                  $(function(){
                    $('.adatlap-top .image-list .images').slick({
                      slidesToShow: 2,
                      slidesToScroll: 1,
                      arrows: true,
                      dots: false,
                      infinite: true,
                      adaptiveHeight: true
                    });
                  })
                })(jQuery);
              </script>
              <?php endif; ?>
            </div>
            <div class="data">
              <div class="titles">
                <?php if ($logo_id && $logo): ?>
                <div class="icologo">
                  <img src="<?php echo $logo;?>" alt="<?php echo the_title(); ?>">
                </div>
                <?php endif; ?>
                <h1><?php echo the_title(); ?></h1>
                <?php if ($subtitle): ?>
                  <h2><?php echo $subtitle; ?></h2>
                <?php endif; ?>
              </div>
              <div class="divider"></div>
              <div class="short-desc">
                <?php echo the_excerpt(); ?>
              </div>
              <?php
               $metakey = METAKEY_PREFIX . 'moduls_set';
               $moduls = unserialize(get_post_meta($post->ID, $metakey, true));
              ?>
              <?php if (count($moduls) > 0): ?>
              <div class="moduls">
                <?php foreach ((array)$moduls as $modul):
                  $ct_slug = sanitize_title($modul);
                  $ct_slug_safe = str_replace(array('-'),array('_'),$ct_slug);
                  $modul_items = unserialize(get_post_meta($post->ID, METAKEY_PREFIX.'moduls_'.$ct_slug_safe, true));
                ?>
                <div class="modul">
                  <div class="title"><?=stripslashes($modul)?></div>
                  <div class="wrapper">
                    <?php $mi = -1; foreach ((array)$modul_items['title'] as $m): $mi++; ?>
                    <div class="modul-elem">
                      <div class="t"><i class="fa fa-plus-square"></i> <?=stripslashes($modul_items['title'][$mi])?></div>
                      <div class="sdesc"><?=stripslashes($modul_items['shortdesc'][$mi])?> <a href="#<?=$ct_slug_safe?>_<?=str_replace(array('-'),array('_'),stripslashes(sanitize_title($modul_items['title'][$mi])));?>" title="Részletek olvasása"><i class="fa fa-info-circle"></i></a></div>
                    </div>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="box-nav">
            <ul>
              <li><a href="#ismerteto">Szoftver ismeretető</a></li>
            <?php foreach ((array)$set as $ct) {
              $ct_slug = sanitize_title($ct);
              $meta_key = METAKEY_PREFIX . 'program_contents';
              $savekey = METAKEY_PREFIX.'program_contents_'.$ct_slug;
              $conte =  (get_post_meta($post->ID, $savekey, true));
              $cont = (is_serialized($conte)) ? maybe_unserialize($conte) : $conte;
              $cont['content'] = stripslashes($cont['content']);
            ?>
              <li><a href="#<?=$ct_slug?>"><?=$cont['title']?></a></li>
            <? } ?>
            </ul>
          </div>
          <div class="data-boxes">
            <a name="ismerteto"></a>
            <div class="box">
              <h3>Szoftver ismeretető</h3>
              <div class="box-content">
                <?php the_content(); ?>
              </div>
            </div>
            <?php foreach ((array)$set as $ct) {
              $ct_slug = sanitize_title($ct);
              $meta_key = METAKEY_PREFIX . 'program_contents';
              $savekey = METAKEY_PREFIX.'program_contents_'.$ct_slug;
              $conte =  (get_post_meta($post->ID, $savekey, true));
              $cont = (is_serialized($conte)) ? maybe_unserialize($conte) : $conte;
              $cont['content'] = stripslashes($cont['content']);
            ?>
            <a name="<?=$ct_slug?>"></a>
            <div class="box">
              <h3><?=$cont['title']?></h3>
              <div class="box-content">
                <?php echo $cont['content']; ?>
              </div>
            </div>
            <? } ?>
          </div>
          <?php if (count($moduls) > 0): ?>
          <div class="divider"></div>
          <div class="szoftver-modules data-boxes">
            <h2>Modulok</h2>
            <?php foreach ((array)$moduls as $modul):
              $ct_slug = sanitize_title($modul);
              $ct_slug_safe = str_replace(array('-'),array('_'),$ct_slug);
              $modul_items = unserialize(get_post_meta($post->ID, METAKEY_PREFIX.'moduls_'.$ct_slug_safe, true));
            ?>
            <div class="box">
              <h3><?=stripslashes($modul)?></h3>
              <div class="box-content">
                <?php $mi = -1; foreach ((array)$modul_items['title'] as $m): $mi++; ?>
                <div class="modul-elem">
                  <a name="<?=$ct_slug_safe?>_<?=str_replace(array('-'),array('_'),stripslashes(sanitize_title($modul_items['title'][$mi])));?>"></a>
                  <h4><?=stripslashes($modul_items['title'][$mi])?></h4>
                  <div class="sdesc"><?=stripslashes($modul_items['shortdesc'][$mi])?></div>
                  <div class="desc"><?=stripslashes($modul_items['desc'][$mi])?></div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php
        $videos_raw = get_post_meta($post->ID, METAKEY_PREFIX.'szoftver_videos', true);
        $videos = explode("\n", $videos_raw);
        if ($videos_raw != '' && count($videos) > 0) {
      ?>
      <div class="video-holder">
        <div class="fusion-row">
          <div class="wrapper">
            <div class="ajanlatkeres">
              <div class="ajanlat-btn">
                <a href="/ajanlatkeres">Ajánlatkérés <i class="fa fa-arrow-circle-o-right"></i></a>
              </div>
              <div class="videos">
                <h2>Videó bemutatók</h2>
                <div class="video-set">
                  <?php foreach ((array)$videos as $vid): ?>
                  <div class="video">
                    <?php echo Youtube::ember($vid); ?>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php } else { ?>
      <br><br><br>
    <?php } ?>
    </div>
  </div>
<?php endwhile; ?>
</section>
<style media="screen">
  .video-holder .slick-prev:before, .slick-next:before{
    color: #90ce63;
  }
</style>
<script type="text/javascript">
	(function($){
		$('#main > .fusion-row').css({
			width: '100%',
      maxWidth: '100%'
		});
    $('.moduls > .modul .modul-elem > .t').click(function(ev){
      ev.preventDefault();
      ev.stopPropagation();
      $('.moduls > .modul .modul-elem').removeClass('opened');
      $('.moduls > .modul .modul-elem .t i').removeClass('fa-minus-square').addClass('fa-plus-square');
      var opened = $(this).parent().hasClass('opened');
      if ( !opened ) {
        $(this).parent().addClass('opened');
        $(this).find('i').removeClass('fa-plus-square').addClass('fa-minus-square');
      } else {
        $(this).parent().removeClass('opened');
        $(this).find('i').removeClass('fa-minus-square').addClass('fa-plus-square');
      }
    });

    $('.video-set').slick({
      infinite: true,
      slidesToShow: 3,
      slidesToScroll: 3,
      dots: true,
      arrows: true
    });
	})(jQuery)
</script>
<?php wp_reset_postdata(); ?>
<?php do_action( 'avada_after_content' ); ?>
<?php
get_footer();

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
