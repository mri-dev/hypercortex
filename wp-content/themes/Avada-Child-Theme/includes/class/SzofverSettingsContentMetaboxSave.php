<?php
  class SzofverSettingsContentMetaboxSave implements MetaboxSaver
  {
    public function __construct()
    {
    }
    public function saving($post_id, $post)
    {
      auto_update_post_meta( $post_id, METAKEY_PREFIX . 'szoftver_subtitle', $_POST[METAKEY_PREFIX . 'szoftver_subtitle'] );
      auto_update_post_meta( $post_id, METAKEY_PREFIX . 'szoftver_logo_id', $_POST[METAKEY_PREFIX . 'szoftver_logo_id'] );
      auto_update_post_meta( $post_id, METAKEY_PREFIX . 'szoftver_videos', $_POST[METAKEY_PREFIX . 'szoftver_videos'] );
    }
  }
?>
