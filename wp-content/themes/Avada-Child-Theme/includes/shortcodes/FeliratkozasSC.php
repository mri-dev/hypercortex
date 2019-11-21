<?php
class FeliratkozasSC
{
    const SCTAG = 'feliratkozas';

    public $avaiable_types = array('ajanlat', 'kapcsolat', 'szallitas');

    public function __construct()
    {
        add_action( 'init', array( &$this, 'register_shortcode' ) );
    }

    public function register_shortcode() {
        add_shortcode( self::SCTAG, array( &$this, 'do_shortcode' ) );
    }

    public function do_shortcode( $attr, $content = null )
    {
        $button_text = 'Ajánlatot kérek';
        $whatisit = 'Ajánlatkérés';

    	  /* Set up the default arguments. */
        $defaults = apply_filters(
            self::SCTAG.'_defaults',
            array(
            )
        );

        $output = '<form id="feliratkozo_form_3" class="" action="http://iriszoffice.hu/wg/subscriber.php?g=3&f=123a5675fe" method="post"><div class="'.self::SCTAG.'-holder contact-form">';
        $output .= (new ShortcodeTemplates('Feliratkozas'))->load_template( $pass_data );
        $output .= '</div></form>';

        /* Return the output of the tooltip. */
        return apply_filters( self::SCTAG, $output );
    }

}

new FeliratkozasSC();

?>
