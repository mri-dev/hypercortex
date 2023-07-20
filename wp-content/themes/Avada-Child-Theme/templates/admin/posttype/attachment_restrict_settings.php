<?php global $post; ?>
<table class="<?=TD?>">
  <tr>
    <td colspan="1">
      <?php $metakey = METAKEY_PREFIX . 'subsecured'; ?>
      <p><?php $value = get_post_meta($post->ID, $metakey, true); ?>
      <input autocomplete="off" id="<?=$metakey?>" <?=($value == 1)?'checked="checked"':''?> type="checkbox" name="<?=$metakey?>" value="1"> 
      <label class="post-attributes-label" for="<?=$metakey?>"><strong>A fájl / dokumentum megtekintése (Webgalamb) feliratkozáshoz kötött</strong>
      <div>A dokumentum közvetlen hivatkozásával hivatkozzon a fájlra, ne a Fájl URL-jével!</div>
      <input type="text" value="<?=get_permalink( $post->ID )?>">
      </label></p>
    </td>
  </tr>
</table>