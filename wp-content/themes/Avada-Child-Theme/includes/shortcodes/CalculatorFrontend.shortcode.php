<?php
class CalculatorFrontendSC
{
    const SCTAG = 'calculator';

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

        $calculators = new Calculators();
        $settings = $calculators->loadSettings( $attr['view'] );
        $pass_data['settings'] = $settings;

        $output = '<div ng-app="Hypercortex" ng-controller="Calculators" class="'.self::SCTAG.'-holder view_of_'.$attr['view'].'">';
        $output .= (new ShortcodeTemplates('calculator-'.$attr['view']))->load_template( $pass_data );
        $output .= '</div>';

        /* Return the output of the tooltip. */
        return apply_filters( self::SCTAG, $output );
    }

}

new CalculatorFrontendSC();

?>
