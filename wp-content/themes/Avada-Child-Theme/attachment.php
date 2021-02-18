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
      <strong><?=__('A következő dokumentumhoz szeretne hozzáférni:', 'hc')?></strong>
      <h1><?php echo $title; ?></h1>
      <div class="info"><?php echo strtoupper($type); ?> <sub>*</sub> <?php echo $size; ?></div>
      <h2><?=__('A dokumentum eléréséhez feliratkozás szükséges!')?></h2>      
      <div class="subber">
        <div class="wrapper">
          <div>
            <input type="text" name="name" value="">
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <pre><?php //print_r($post); ?></pre>
<?php
get_footer();

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
