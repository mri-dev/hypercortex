<?php
class SzolgaltatasBlockSC
{
    const SCTAG = 'szolgaltatas-block';

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
            'view' => 'big',
            'autopageparent' => 0
          )
      );

      /* Parse the arguments. */
      $attr = shortcode_atts( $defaults, $attr );

      if ( $parent = get_page_by_path( 'szolgaltatasaink', OBJECT, 'page' ) )
          $id = $parent->ID;
      else
          $id = 0;

      if ($post->post_parent != 0) {
        $ancestors = get_post_ancestors($post->ID);
        $top_parent_id = (int)trim(end($ancestors));
      }

      if ( $top_parent_id !=  $parent->ID && $attr['autopageparent'] == 1) {
        return '<div class="fake-parent-divider">&nbsp;</div>';
      }

      // Kalkulátorok
      if ( $attr['view'] == 'kalkulatorblocks' )
      {
        $parent = get_page_by_path( 'kalkulatorok', OBJECT, 'page' );
        if ($parent->ID == $post->ID) {
          $kalk_group_page = true;
        }
        $id = $parent->ID;
      }

      $postarg = array(
        'post_parent' => $id,
        'post_type' => 'page',
        'orderby' => 'menu_order',
        'order' => 'ASC'
      );

      if ($attr['autopageparent'] == 1) {
        $postarg['exclude'] = array($post->ID);
      }

      //$posts = new WP_Query($postarg);
      $posts = get_posts($postarg);

      if (count($posts) == 0) {
        return '';
      }

      $attr['blocks'] = $posts;

      $pass_data = $attr;
      $output = '<div class="'.self::SCTAG.'-holder view-of-'.$attr['view'].(($attr['autopageparent'] == 1)?' autopager':'').(($kalk_group_page)?' group-page':'').'">';

      $output .= (new ShortcodeTemplates('SzolgaltatasBlock/'.$attr['view']))->load_template( $pass_data );
      $output .= '</div>';

      /* Return the output of the tooltip. */
      return apply_filters( self::SCTAG, $output );
    }

}

new SzolgaltatasBlockSC();

?>
