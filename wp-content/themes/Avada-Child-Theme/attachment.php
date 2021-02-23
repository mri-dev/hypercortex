<?php
// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
global $post;

$subsecured = get_post_meta( $post->ID, METAKEY_PREFIX.'subsecured', true);
$subsecured = ($subsecured == '1') ? true : false;

if( !$subsecured )
{
  $fileurl = wp_get_attachment_url( $post->ID );
  wp_redirect( $fileurl ); exit;
} 
else 
{
  $accept = false;
  if( isset($_COOKIE['hcwg_subscribed']) && !empty($_COOKIE['hcwg_subscribed']) )
  {
    $accept = true;
  }

  if( $accept )
  {
    $fileurl = wp_get_attachment_url( $post->ID );
    wp_redirect( $fileurl ); exit;
  }
}

function getSize($file)
{
  $bytes = filesize($file);
  $s = array('b', 'Kb', 'Mb', 'Gb');
  $e = floor(log($bytes)/log(1024));
  return sprintf('%.2f '.$s[$e], ($bytes/pow(1024, floor($e))));
}
?>
<?php get_header(); ?>
  <div class="attachment-sub-info">
    <div class="wrapper">
      <?php 
        $title = (!empty($post->post_excerpt)) ? $post->post_excerpt : $post->post_title; 
        $size = getSize( get_attached_file( $post->ID ) );
        $type = end(explode('.', $post->guid));
      ?>
      <div class="lock-icon"><i class="fa fa-lock"></i></div>
      <strong><?=__('A következő dokumentumhoz szeretne hozzáférni:', 'hc')?></strong>
      <div class="doc-info">
        <h1><?php echo $title; ?></h1>
        <div class="info"><?php echo strtoupper($type); ?> <sub>*</sub> <?php echo $size; ?></div>
      </div>
      <h2><?=__('A dokumentum eléréséhez <strong>feliratkozás</strong> szükséges!')?></h2>
      <strong><?=__('Adja meg a feliratkozáshoz szükséges adatokat, akkor is, ha korábban már feliratkozott.<br>A rendszer azonosítani fogja Önt és letölthetővé válik a dokumentum.','hc')?></strong>
      <div class="subber">
        <div class="wrapper">
          <form id="subscriber" action="" method="post" onsubmit="return false;">
            <div class="name">
              <label for="name"><?=__('Cégnév megadása', 'hc')?> *</label>
              <input type="text" id="name" name="f_9" value="" __required>
            </div>
            <div class="email">
              <label for="email"><?=__('E-mail cím megadása', 'hc')?> *</label>
              <input type="text" id="email" name="subscr" value="" __required>
            </div>
            <div class="cb">
              <input type="checkbox" name="f_12[]" id="adatvedelem" value="2"> 
              <label for="adatvedelem">* <?=__('A feliratkozással elfogadja az Adatvédelmi Nyilatkozatot és hozzájárulok az adataim kezeléséhez.', 'hc')?></label>            
            </div>
            <div class="cb">
              <input type="checkbox" name="f_11[]" id="marketing" value="1"> 
              <label for="marketing">* <?=__('Hozzájárulok, hogy az általam megadott e-mail címre időközönként üzleti céllal elektronikus levelet küldhetnek!', 'hc')?></label>            
            </div>
            <div class="btns">
              <div class="ajxmsg" style="display: none;"></div>
              <button type="button" class="grad-button" onclick="subscriber();" name="sub" value="1"><?=__('Feliratkozás', 'hc')?></button>
            </div>
          </form>          
        </div>
      </div>
    </div>
  </div>
  
  <pre><?php //print_r($post); ?></pre>
<?php
get_footer();

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
