<?php
  class AttachmentMetaboxSave implements MetaboxSaver
  {
    public function __construct()
    {
    }
    public function saving($post_id, $post)
    {
      $on = (isset($_POST[METAKEY_PREFIX . 'subsecured'])) ? 1 : false;
      auto_update_post_meta( $post_id, METAKEY_PREFIX . 'subsecured', $on );
    }
  }
?>
