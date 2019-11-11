<?php
class CustomMetabox
{
  private $template_root = 'templates/admin/posttype/';
  private $template_file = 'standard';
  private $tag = null;
  private $metabox_title = null;
  private $save_fnc = null;
  private $settings = array();

  public $nonce_key = 'custom_nonce';

  public function __construct(
    $tag,
    $metabox_title = 'Empty',
    $save_function,
    $template_file = 'standard',
    $settings = array()
  )
  {
    $this->tag = $tag;
    $this->template_file = $template_file;
    $this->metabox_title = $metabox_title;
    $this->nonce_key = $this->tag.'-mb-nonce';
    $this->settings = $settings;

    $this->save_fnc = $save_function;

    if ( is_admin() ) {
      add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
      add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
    }
  }

  public function init_metabox() {
      add_action( 'add_meta_boxes', array( $this, 'add_metabox') );
      add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
  }

  public function add_metabox() {
    $uid = $this->tag.'-meta-box'.uniqid();
    add_meta_box(
      $uid,
      $this->metabox_title,
      array( $this, 'render_metabox' ),
      $this->tag,
      'normal',
      'high'
    );

    if (isset($this->settings['class'])) {
      add_filter( 'postbox_classes_'.$this->tag.'_'.$uid, array($this, 'meta_box_classes') );
    }

  }

  public function meta_box_classes( $classes = array() )
  {
    $classes[] = $this->settings['class'];
    return $classes;
  }

  public function render_metabox( $post ) {
    // Add nonce for security and authentication.
    wp_nonce_field( $this->nonce_key.'_action', $this->nonce_key );

    include(locate_template( $this->template_root . $this->template_file .'.php' ));
  }
  public function save_metabox( $post_id, $post ) {
      // Add nonce for security and authentication.
      $nonce_name   = isset( $_POST[$this->nonce_key] ) ? $_POST[$this->nonce_key] : '';
      $nonce_action = $this->nonce_key.'_action';


      // Check if nonce is set.
      if ( ! isset( $nonce_name ) ) {
          return;
      }

      // Check if nonce is valid.
      if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
          return;
      }

      // Check if user has permissions to save data.
      if ( ! current_user_can( 'edit_post', $post_id ) ) {
          return;
      }

      // Check if not an autosave.
      if ( wp_is_post_autosave( $post_id ) ) {
          return;
      }

      // Check if not a revision.
      if ( wp_is_post_revision( $post_id ) ) {
          return;
      }

      $this->save_fnc->saving( $post_id, $post );
  }
}

interface MetaboxSaver {
  public function saving( $post_id, $post );
}
?>
