<?php
  global $wpdb, $post;
?>
<h1>Esemény beállítások</h1>
<table class="<?=TD?>">
  <tr>
    <td>
      <?php $metakey = METAKEY_PREFIX . 'event_on_start'; ?>
      <p><label class="post-attributes-label" for="<?=$metakey?>"><strong>Esemény kezdő időpontja (dátum)</strong></label></p>
      <?php $value = get_post_meta($post->ID, $metakey, true); ?>
      <input autocomplete="off" id="<?=$metakey?>" type="text" class="datepicker" name="<?=$metakey?>" value="<?=$value?>">
    </td>
    <td style="width:80px;">
      <?php $metakey = METAKEY_PREFIX . 'event_on_start_time'; ?>
      <p><label class="post-attributes-label" for="<?=$metakey?>"><strong>Óra:Perc</strong></label></p>
      <?php $value = get_post_meta($post->ID, $metakey, true); ?>
      <input autocomplete="off" id="<?=$metakey?>" type="time" name="<?=$metakey?>" placeholder="12:00" value="<?=$value?>">
    </td>
    <td>
      <?php $metakey = METAKEY_PREFIX . 'event_on_end'; ?>
      <p><label class="post-attributes-label" for="<?=$metakey?>"><strong>Esemény befejező időpontja (dátum)</strong></label></p>
      <?php $value = get_post_meta($post->ID, $metakey, true); ?>
      <input autocomplete="off" id="<?=$metakey?>" type="text" class="datepicker" name="<?=$metakey?>" value="<?=$value?>">
    </td>
    <td style="width:80px;">
      <?php $metakey = METAKEY_PREFIX . 'event_on_end_time'; ?>
      <p><label class="post-attributes-label" for="<?=$metakey?>"><strong>Óra:Perc</strong></label></p>
      <?php $value = get_post_meta($post->ID, $metakey, true); ?>
      <input autocomplete="off" id="<?=$metakey?>" type="time" name="<?=$metakey?>" placeholder="12:00" value="<?=$value?>">
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <?php $metakey = METAKEY_PREFIX . 'event_slide'; ?>
      <p><label class="post-attributes-label" for="<?=$metakey?>"><strong>Rev. Slide a fejlécbe</strong></label></p>
      <?php $value = get_post_meta($post->ID, $metakey, true); ?>
      <?php
        $slides = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}revslider_sliders ORDER BY title ASC") );
      ?>
      <select class="" name="<?=$metakey?>" id="<?=$metakey?>">
        <option value="" selected="selected">- Ne legyen slide -</option>
        <option value="" disabled="disabled"></option>
        <?php foreach ($slides as $slide): ?>
        <option value="<?=$slide->alias?>" <?=($slide->alias == $value)?'selected="selected"':''?>><?=$slide->title?></option>
        <?php endforeach; ?>
      </select>
    </td>
    <td colspan="2">
      <?php $metakey = METAKEY_PREFIX . 'event_comment'; ?>
      <p><label class="post-attributes-label" for="<?=$metakey?>"><strong>Esemény időpont megjegyzés</strong></label></p>
      <?php $value = get_post_meta($post->ID, $metakey, true); ?>
      <input autocomplete="off" id="<?=$metakey?>" type="text" name="<?=$metakey?>" placeholder="Pl.: 5 nap, 4 éjszaka" value="<?=$value?>">
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <?php $metakey = METAKEY_PREFIX . 'helyszin'; ?>
      <p><label class="post-attributes-label" for="<?=$metakey?>"><strong>Esemény helyszíne</strong></label></p>
      <?php $value = get_post_meta($post->ID, $metakey, true); ?>
      <input autocomplete="off" id="<?=$metakey?>" type="text" name="<?=$metakey?>" value="<?=$value?>">
    </td>
    <td colspan="2">
      <?php $metakey = METAKEY_PREFIX . 'maps_cim'; ?>
      <p><label class="post-attributes-label" for="<?=$metakey?>"><strong>Térkép cím - Google Maps</strong></label></p>
      <?php $value = get_post_meta($post->ID, $metakey, true); ?>
      <input autocomplete="off" id="<?=$metakey?>" type="text" name="<?=$metakey?>" value="<?=$value?>">
    </td>
  </tr>
  <tr>
    <td colspan="4">
      <?php $metakey = METAKEY_PREFIX . 'jelentkezes_zarva'; ?>
      <p><label class="post-attributes-label" for="<?=$metakey?>"><strong>Jelentkezés lezárva</strong></label></p>
      <?php $value = get_post_meta($post->ID, $metakey, true); ?>
      <input autocomplete="off" id="<?=$metakey?>" <?=($value == 1)?'checked="checked"':''?> type="checkbox" name="<?=$metakey?>" value="">
    </td>
  </tr>
</table>
<h1>Kedvezmény</h1>
<p>Last Minute esemény esetén határozzunk megy egy kedvezmény mértéket.</p>
<table class="<?=TD?>">
  <tr>
    <td style="width: 30%;">
      <?php $metakey = METAKEY_PREFIX . 'kedvezmeny_szazalek'; ?>
      <p><label class="post-attributes-label" for="<?=$metakey?>"><strong>Kedvezmény (%)</strong></label></p>
      <?php $value = get_post_meta($post->ID, $metakey, true); ?>
      <input autocomplete="off" id="<?=$metakey?>" type="number" min="0" name="<?=$metakey?>" value="<?=$value?>">
    </td>
    <td>
      <?php $metakey = METAKEY_PREFIX . 'kedvezmeny_comment'; ?>
      <p><label class="post-attributes-label" for="<?=$metakey?>"><strong>Kedvezmény megjegyzés</strong></label></p>
      <?php $value = get_post_meta($post->ID, $metakey, true); ?>
      <input autocomplete="off" id="<?=$metakey?>" type="text" name="<?=$metakey?>" value="<?=$value?>">
    </td>
  </tr>
</table>
<h1>Címke</h1>
<p>Listázásban a kép felett megjelenő címke, rövid tájékoztató szöveggel.</p>
<table class="<?=TD?>">
  <tr>
    <td>
      <?php $metakey = METAKEY_PREFIX . 'cimke_text'; ?>
      <p><label class="post-attributes-label" for="<?=$metakey?>"><strong>Címke felirata</strong></label></p>
      <?php $value = get_post_meta($post->ID, $metakey, true); ?>
      <input autocomplete="off" id="<?=$metakey?>" maxlength="40" type="text" name="<?=$metakey?>" value="<?=$value?>">
    </td>
    <td style="width: 120px;">
      <?php $metakey = METAKEY_PREFIX . 'cimke_color_bg'; ?>
      <p><label class="post-attributes-label" for="<?=$metakey?>"><strong>Címke háttérszín</strong></label></p>
      <?php $value = get_post_meta($post->ID, $metakey, true); ?>
      <input autocomplete="off" id="<?=$metakey?>" maxlength="40" type="text" name="<?=$metakey?>" value="<?=$value?>" placeholder="pl.: #444444, black">
    </td>
    <td style="width: 120px;">
      <?php $metakey = METAKEY_PREFIX . 'cimke_color_text'; ?>
      <p><label class="post-attributes-label" for="<?=$metakey?>"><strong>Címke szövegszín</strong></label></p>
      <?php $value = get_post_meta($post->ID, $metakey, true); ?>
      <input autocomplete="off" id="<?=$metakey?>" maxlength="40" type="text" name="<?=$metakey?>" value="<?=$value?>" placeholder="pl.: #444444, black">
    </td>
  </tr>
</table>
<h1>Linkek</h1>
<p>Sidebar-ban megjelenő külső vagy egyéb linkek, hivatkozások.</p>
<?php $metakey = METAKEY_PREFIX . 'linkek'; ?>
<?php $value = get_post_meta($post->ID, $metakey, true); ?>
<?php wp_editor($value, $metakey ); ?>

<h1>Csatolt szállás ajánlatkérőhöz</h1>
<?php
  $szallasok = get_posts(array(
    'post_type' => 'szallasok',
    'posts_per_page' => -1
  ));
?>
<table class="<?=TD?>">
  <tr>
    <td>
      <?php $metakey = METAKEY_PREFIX . 'program_szallas_id'; ?>
      <p><label class="post-attributes-label" for="<?=$metakey?>"><strong>Szállás kiválasztása</strong></label></p>
      <?php $value = get_post_meta($post->ID, $metakey, true); ?>
      <select class="" name="<?=$metakey?>">
        <option value="" selected="selected">-- válasszon --</option>
        <?php foreach ($szallasok as $szallas): ?>
        <option value="<?=$szallas->ID?>" <?=($value == $szallas->ID)?'selected="selected"':''?>><?=$szallas->post_title?></option>
        <?php endforeach; ?>
      </select>
    </td>
</table>

<h1>Jelentkező form - Active Campaign</h1>
<?php
  if ( class_exists('ActiveCampaignWordPress') ) {
    $ac_settings = get_option("settings_activecampaign");

    if ( !empty( $ac_settings ) ) {
      $ac = new ActiveCampaignWordPress( $ac_settings['api_url'], $ac_settings['api_key']);
      $ac_instance = array();
      $ac_instance = activecampaign_getforms($ac, $ac_instance);

      echo '<div style="color: #00baea;">Csatlakozva: <strong>'.$ac_settings['account_view']['fname'].' '.$ac_settings['account_view']['lname'].'</strong> ('.$ac_settings['account_view']['account'].')</div>';
    } else {
      echo 'Kérjük, hogy adja meg az Active Campaign működéséhez az API adatokat. <a href="/wp-admin/options-general.php?page=activecampaign">Beállítás.</a>';
    }

    if( !empty($ac_settings) ):
?>
<table class="<?=TD?>">
  <tr>
    <td>
      <?php $metakey = METAKEY_PREFIX . 'program_ac_form'; ?>
      <p><label class="post-attributes-label" for="<?=$metakey?>"><strong>Active Campaign form kiválasztása (<?=count($ac_instance['forms'])?>)</strong></label></p>
      <?php $value = get_post_meta($post->ID, $metakey, true); ?>
      <select class="" name="<?=$metakey?>">
        <option value="" selected="selected">-- válasszon Active Campaign formot --</option>
        <?php foreach ($ac_instance['forms'] as $ac_form_id => $form ): ?>
        <option value="<?=$ac_form_id?>" <?=($value == $ac_form_id)?'selected="selected"':''?>><?=$form['name']?></option>
        <?php endforeach; ?>
      </select>
    </td>
</table>
<?php endif; } else {  ?>
  Az Active Campaign plugin nincs telepítve a modul kezeléséhez. Telepítse a plugint: <a href="https://wordpress.org/plugins/activecampaign-subscription-forms/#installation">https://wordpress.org/plugins/activecampaign-subscription-forms/#installation</a>
<?php }?>

<script type="text/javascript">
  jQuery( document ).ready( function( $ ) {
    $('.datepicker').datepicker();
} );
</script>
