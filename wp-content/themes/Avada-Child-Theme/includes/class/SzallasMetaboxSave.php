<?php
  class SzallasMetaboxSave implements MetaboxSaver
  {
    public function __construct()
    {
    }
    public function saving($post_id, $post)
    {
      auto_update_post_meta( $post_id, METAKEY_PREFIX . 'szallas_programok', $_POST[METAKEY_PREFIX . 'szallas_programok'] );
      auto_update_post_meta( $post_id, METAKEY_PREFIX . 'szallas_address', $_POST[METAKEY_PREFIX . 'szallas_address'] );
      auto_update_post_meta( $post_id, METAKEY_PREFIX . 'szallas_weburi', $_POST[METAKEY_PREFIX . 'szallas_weburi'] );
      auto_update_post_meta( $post_id, METAKEY_PREFIX . 'szallas_extra', $_POST[METAKEY_PREFIX . 'szallas_extra'] );      
    }
  }
?>
