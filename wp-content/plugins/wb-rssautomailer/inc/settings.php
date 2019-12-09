<?php
class WGRSSMailer_Settings
{
  public $wg;
  function __construct( $arr = array() )
  {
    $this->wg = $arr['wg'];
    add_action( 'admin_menu', array($this, 'settings_admin_menu') );
    add_action( 'admin_init', array($this, 'settings_init') );
  }

  public function settings_init()
  {
    register_setting( 'wgrss-settings', WGRSS_DB_PREFIX.'wg_db_username', array( $this, 'sanitize' ));
    register_setting( 'wgrss-settings', WGRSS_DB_PREFIX.'wg_db_name', array( $this, 'sanitize' ));
    register_setting( 'wgrss-settings', WGRSS_DB_PREFIX.'wg_db_pw', array( $this, 'sanitize' ));
    register_setting( 'wgrss-settings', WGRSS_DB_PREFIX.'wg_db_prefix', array( $this, 'sanitize' ));

    add_action('admin_post_wgrss_add_automation',array($this, 'saving_posted_add_automation'));
    add_action('admin_post_nopriv_wgrss_add_automation',array($this, 'saving_posted_add_automation'));
  }

  public function settings_admin_menu()
  {
    add_menu_page(
        __( 'Webgalamb Automailer', 'wgrss' ),
        'Webgalamb Mailer',
        'manage_options',
        'wgrss-settings',
        array($this, 'wgrss_settings_page'),
        '',
        81
    );
    add_submenu_page(
      'wgrss-settings',
      __( 'Új automatizálás', 'wgrss' ),
      'Új automatizálás',
      'manage_options',
      'wgrss-settings-adder',
      array($this, 'wgrss_settings_page_adder'),
      '',
      82
    );
  }

  public function saving_posted_add_automation()
  {
    global $table_prefix, $wpdb;
    $errors = new WP_Error();

    $is_editing = (isset($_POST['edit_id'])) ? $_POST['edit_id'] : false;

    if ( !current_user_can( 'manage_options' ) ) {
      $errors->add('nopriv', 'Nincs megfelelő jogosultsága a művelet végrehajtására: adminisztrátori jogok.');
    }

    if ( empty($_POST['wg_group_id']) ||  empty($_POST['wg_mail_id']) ||  empty($_POST['title']) ) {
      $errors->add('empty', 'A csillaggal jelölt mezők megadása kötelező!');
    }

    if ( !$errors->has_errors() && ! wp_verify_nonce( $_POST['wgrss-settings-adder'], 'wgrss-settings-adder' ) ) {
      $errors->add('nopriv', 'Hibás azonosítás és hottáférés.');
    } else {
      $return = (isset($_POST['return'])) ? $_POST['return'] : $_POST['_wp_http_referer'];

      if ($is_editing)
      {
        // saving
        $catids = (empty($_POST['cats'])) ? NULL: implode($_POST['cats'],',');
        $wpdb->update(
          $table_prefix.WGRSS_DB_PREFIX.'automations',
          array(
            'title' => sanitize_text_field($_POST['title']),
            'category_id' => $catids,
            'wg_group_id' => (int)($_POST['wg_group_id']),
            'wg_mail_id' => (int)($_POST['wg_mail_id']),
            'active' => (int)($_POST['active'])
          ),
          array(
            'ID' => $is_editing
          )
        );
      } else {
        // create
        $catids = (empty($_POST['cats'])) ? NULL: implode($_POST['cats'],',');
        $wpdb->insert(
          $table_prefix.WGRSS_DB_PREFIX.'automations',
          array(
            'title' => sanitize_text_field($_POST['title']),
            'category_id' => $catids,
            'wg_group_id' => (int)($_POST['wg_group_id']),
            'wg_mail_id' => (int)($_POST['wg_mail_id']),
            'active' => (int)($_POST['active'])
          )
        );
      }
    }

    if ( $errors->has_errors() ) {
      $err = '<strong>Hibák a mentés során:</strong><br><br>';
      foreach ($errors->get_error_messages() as $msg) {
        $err .= '- '.$msg."<br>";
      }
      wp_die($err);
    }
    else
    {
      wp_safe_redirect($return);
    }
  }

  public function wgrss_settings_page()
  {
    global $wpdb, $table_prefix;

    $tbl = $table_prefix.WGRSS_DB_PREFIX.'automations';
    $list = $wpdb->get_results("SELECT * FROM {$tbl} ORDER BY created_at DESC", ARRAY_A);


    require_once WGRSS_TEMPLATES . "admin-settings.php";
  }

  public function wgrss_settings_page_adder()
  {
    if (isset($_GET['edit'])) {
      $editor = (int)$_GET['edit'];
    }

    if ($editor) {
      $mailer = new AutomationWG();
      $edit = $mailer->getItem($editor);
    }
    require_once WGRSS_TEMPLATES . "add-automation.php";
  }

  public function sanitize( $input )
  {
      $input = sanitize_text_field($input);

      return $input;
  }

  public function __destruct()
  {
    $this->wg = null;
  }
}
