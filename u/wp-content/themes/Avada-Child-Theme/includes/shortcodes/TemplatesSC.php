<?php
class TemplatesSC
{
    const SCTAG = 'template';

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
              'view' => ''
            )
        );

        /* Parse the arguments. */
        $attr = shortcode_atts( $defaults, $attr );
        $pass_data = $attr;

        if ($attr['view'] != '') {
          $output = '<div class="custom-templates-sc view-'.$attr['view'].'">';
          $output .= (new ShortcodeTemplates('templates/'.$attr['view']))->load_template( $pass_data );
          $output .= '</div>';
        }

        /* Return the output of the tooltip. */
        return apply_filters( self::SCTAG, $output );
    }

}

new TemplatesSC();

?>
