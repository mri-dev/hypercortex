<?php
class TestimonialsSC
{
    const SCTAG = 'testimonials';

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

        $posts = new WP_Query(array(
          'post_type' => 'wpm-testimonial',
          'tax_query' => array(
              array(
                 'taxonomy' => 'wpm-testimonial-category',
                 'field'    => 'slug',
                 'terms'    => 'partnerek',
              ),
          ),
        ));

        $pass_data = $attr;
        $pass_data['posts'] = $posts;

        $output = '<div class="'.self::SCTAG.'-holder">';

        $output .= (new ShortcodeTemplates('Testimonials'))->load_template( $pass_data );
        $output .= '</div>';

        /* Return the output of the tooltip. */
        return apply_filters( self::SCTAG, $output );
    }

}

new TestimonialsSC();

?>
