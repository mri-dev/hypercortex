<?php
class WGRSSMailer_Settings
{
  function __construct()
  {
    add_action( 'admin_menu', array($this, 'settings_admin_menu') );
    add_action( 'admin_init', array($this, 'settings_init') );
  }

  public function settings_init()
  {
    register_setting(
        'wgrss-settings',
        'wgrss_settings_db_name',
        array( $this, 'sanitize' )
    );
    register_setting(
        'wgrss-settings',
        'wgrss_settings_db_pass',
        array( $this, 'sanitize' )
    );
  }

  public function settings_admin_menu()
  {
    add_menu_page(
        __( 'Webgalamb RSS Automailer', 'wgrss' ),
        'Webgalamb Mailer',
        'manage_options',
        'wgrss-settings',
        array($this, 'wgrss_settings_page'),
        '',
        81
    );
  }

  public function wgrss_settings_page()
  {
    echo 'Asd';
  }

  public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['id_number'] ) )
            $new_input['id_number'] = absint( $input['id_number'] );

        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );

        return $new_input;
    }
}
