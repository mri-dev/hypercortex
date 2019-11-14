<?php
class CikkSidebarSC
{
    const SCTAG = 'cikksidebar';

    public function __construct()
    {
        add_action( 'init', array( &$this, 'register_shortcode' ) );
    }

    public function register_shortcode() {
        add_shortcode( self::SCTAG, array( &$this, 'do_shortcode' ) );
    }

    public function do_shortcode( $attr, $content = null )
    {
      global $post;
      /* Set up the default arguments. */
      $defaults = apply_filters(
          self::SCTAG.'_defaults',
          array(
          )
      );

      /* Parse the arguments. */
      $attr = shortcode_atts( $defaults, $attr );

      $incats = wp_get_post_categories($post->ID);
      $cat = $incats[0];
      $cat_data = get_category($cat);

      $arg = array(
        'post_type' => 'post',
        'posts_per_page' => 3
      );

      if (!is_search()) {
        $arg['post__not_in'] = array($post->ID);
      }

      if (!empty($cat_data)) {
        $arg['category_name'] = trim($cat_data->name);
      }
      $posts = new WP_Query( $arg );

      $pass_data = $attr;
      $pass_data['posts'] = $posts;
      $pass_data['cat'] = $cat_data;

      $output = '<div class="'.self::SCTAG.'-holder">';

      $output .= (new ShortcodeTemplates('CikkSidebar'))->load_template( $pass_data );
      $output .= '</div>';

      /* Return the output of the tooltip. */
      return apply_filters( self::SCTAG, $output );
    }

}

new CikkSidebarSC();

?>
