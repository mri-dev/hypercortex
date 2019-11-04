<?php
class BlogListSC
{
    const SCTAG = 'bloglist';

    public function __construct()
    {
        add_action( 'init', array( &$this, 'register_shortcode' ) );
    }

    public function register_shortcode() {
        add_shortcode( self::SCTAG, array( &$this, 'do_shortcode' ) );
    }

    public function do_shortcode( $attr, $content = null )
    {
        /* Set up the default arguments. */
        $defaults = apply_filters(
            self::SCTAG.'_defaults',
            array(
              'cat' => ''
            )
        );

        /* Parse the arguments. */
        $attr = shortcode_atts( $defaults, $attr );

        $arg = array(
          'post_type' => 'post',
          'posts_per_page' => 3,
        );

        if (!empty($attr['cat'])) {
          $cat = get_category_by_slug(trim($attr['cat']));
          $cat_link = get_category_link($cat);
          $arg['category_name'] = trim($attr['cat']);
        }
        $posts = new WP_Query( $arg );

        $pass_data = $attr;
        $pass_data['posts'] = $posts;
        $pass_data['cat_link'] = $cat_link;

        $output = '<div class="'.self::SCTAG.'-holder">';

        $output .= (new ShortcodeTemplates('Bloglist'))->load_template( $pass_data );
        $output .= '</div>';

        /* Return the output of the tooltip. */
        return apply_filters( self::SCTAG, $output );
    }

}

new BlogListSC();

?>
