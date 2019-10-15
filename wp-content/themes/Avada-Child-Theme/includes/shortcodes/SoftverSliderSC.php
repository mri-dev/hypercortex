<?php
class SoftverSliderSC
{
    const SCTAG = 'szoftver-blocklist';

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
            )
        );

        /* Parse the arguments. */
        $attr = shortcode_atts( $defaults, $attr );

        if ( $post = get_page_by_path( 'szoftvereink', OBJECT, 'page' ) )
            $id = $post->ID;
        else
            $id = 0;

        $posts = get_children(array(
          'post_parent' => $id,
          'orderby' => 'menu_order',
          'order' => 'ASC'
        ));

        if (count($posts) == 0) {
          return '';
        }

        $attr['slides'] = $posts;

        $pass_data = $attr;

        $output = '<div class="'.self::SCTAG.'-holder">';

        $output .= (new ShortcodeTemplates('SoftverSlider'))->load_template( $pass_data );
        $output .= '</div>';

        /* Return the output of the tooltip. */
        return apply_filters( self::SCTAG, $output );
    }

}

new SoftverSliderSC();

?>
