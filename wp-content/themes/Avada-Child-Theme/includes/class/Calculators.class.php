<?php
interface CalculatorVersion
{
  public function calculate( $calc, $data );
  public function calcBerAdoLevonasok( $brutto_ber, $data, $settings );
  public function findBruttoFromNetto( $netto, $data, $settings );
}

class CalculatorBase
{
  const DEFAULT_VERSION = '2021';
  public $year_version = '2021';
  public $avaiable_versions = array('2021','2020/2', '2020/1', '2019');  
  public $version_index = array(
    '2021' => 4,
    '2020/2' => 3, 
    '2020/1' => 2, 
    '2019' => 1
  );
  public $db = null;

  function __construct( $version = false )
  {
    if ( $version ) {
      $this->year_version = $version;
    } else {
      $this->year_version = $this->get_option(\METAKEY_PREFIX.'calc_version', self::DEFAULT_VERSION);
    }

    $this->getDB();

    return $this;
  }

  public function getDB()
  {
    global $wpdb, $calc_wpdb;

    if( defined('DB_CALC_NAME') && defined('DB_CALC_USER') && defined('DB_CALC_PASSWORD') )
    {
      $this->db = $calc_wpdb;
    } else {
      $this->db = $wpdb;
    }
  }

  public function get_option( $option, $default = false )
  {
    $db = $this->db;
    $value = $default;

    if( $db ) 
    {
      if($db->prefix == 'hc_'){
        $option = str_replace(\METAKEY_PREFIX, 'hc_', $option);
      }
      //echo $db->prepare( "SELECT option_value FROM $db->options WHERE option_name = %s LIMIT 1", $option ) . "<br>";
      $row = $db->get_row( $db->prepare( "SELECT option_value FROM $db->options WHERE option_name = %s LIMIT 1", $option ) );
 
      if ( is_object( $row ) ) {
          $value = $row->option_value;
      } else { 
         $value = $default;
      }
    }
    
    return $value;
  }
  /* =============================================================== */

  public function getResultTitle( $settings, $version )
  {
    $title = 'Az eredmény kiszámítása a(z) '.$version.'. évi jogszabályok alkalmazásával történt.';

    error_log(print_r($settings, true));

    if ( !empty($settings['calc_comment']) ) {
      $title = $settings['calc_comment'];
    }

    return $title;
  }

  public function getVersion( $metakeyed = false )
  {
    if ($metakeyed) {
      return \METAKEY_PREFIX.'ref_v'.$this->year_version.'_';
    } else {
      return $this->year_version;
    }
  }

  public function getSettings()
  {
    $data = array();
    $data['current_version'] = $this->getVersion();
    $data['versions'] = $this->avaiable_versions;

    return $data;
  }

  function adminSettings()
  {
    add_action( 'admin_menu', array( &$this , 'add_settings' ) );
  }

  function add_settings()
  {
    add_options_page('Kalkulátor beállítások', 'Kalkulátor beállítások', 'manage_options', 'calc_settings', array( &$this , 'calc_settings_cb' ));
  }

  function calc_settings_cb()
  {
    $settings = array();
    // $this->getVersion(true).'
    $settings[] = $this->getVersion(true).'minimalber';
    $settings[] = $this->getVersion(true).'alapszabadsag';
    $settings[] = $this->getVersion(true).'betegszabadsag';
    $settings[] = $this->getVersion(true).'potszabi_ha16evnelfiatalabbgyereketnevel';
    $settings[] = $this->getVersion(true).'potszabi_megvaltozott_munkakepessegu';
    $settings[] = $this->getVersion(true).'calc_comment';    

    $settings[] = $this->getVersion(true).'adokedvezmeny_frisshazasok';
    $settings[] = $this->getVersion(true).'adokedvezmeny_szemelyi';
    $settings[] = $this->getVersion(true).'adokedvezmeny_csalad_gyermek1';
    $settings[] = $this->getVersion(true).'adokedvezmeny_csalad_gyermek2';
    $settings[] = $this->getVersion(true).'adokedvezmeny_csalad_gyermek3';
    $settings[] = $this->getVersion(true).'ado_tb';
    $settings[] = $this->getVersion(true).'ado_szja';
    $settings[] = $this->getVersion(true).'ado_nyugdij';
    $settings[] = $this->getVersion(true).'ado_termeszetegeszseg';
    $settings[] = $this->getVersion(true).'ado_penzbeli_egeszseg';

    $settings[] = $this->getVersion(true).'ado_munkaerppiac';
    $settings[] = $this->getVersion(true).'ado_szocialis_hozzajarulas';
    $settings[] = $this->getVersion(true).'ado_szakkepzesi_hozzajarulas';
    $settings[] = $this->getVersion(true).'ado_kisvallalati';
    $settings[] = $this->getVersion(true).'ado_caf_adoalap_kieg';

    if (isset($_POST['save_calc_settings']))
    {
      $nonce = $_REQUEST['_wpnonce'];
      if ( ! wp_verify_nonce( $nonce, 'calc_settings_nonce' ) )
      {
          die( __( 'Security check', 'hc' ) );
      }  else {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized user');
        }

        foreach ($settings as $s) {
          if (!empty($_POST[$s])) {
            update_option($s, $_POST[$s]);
          } else {
            delete_option($s);
          }
        }
      }
    }

    // Verzió váltás
    if (isset($_POST['change_calc_version']))
    {
      $nonce = $_REQUEST['_wpnonce'];
      if ( ! wp_verify_nonce( $nonce, 'calc_version_switch_nonce' ) )
      {
          die( __( 'Security check', 'hc' ) );
      }  else {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized user');
        }
        update_option(\METAKEY_PREFIX.'calc_version', $_POST['version']);
        $this->year_version = $_POST['version'];
      }
    }

    $pass_data = array();
    $pass_data['metaprefix'] = $this->getVersion(true);
    $pass_data['versions'] = $this->avaiable_versions;
    $pass_data['current_version'] = $this->year_version;
    $output = (new ShortcodeTemplates('calculator-settings', '/templates/settings/'))->load_template( $pass_data );
    echo $output;
  }

  public function loadSettings( $calc )
  {
    switch ( $calc )
    {
      case 'berkalkulator':
        return $this->load_berkalkulator_resources();
      break;
      case 'teljes_berkoltseg':
        return $this->load_teljes_berkoltseg_resources();
      break;
      case 'netto_ber':
        return $this->load_netto_ber_resources();
      break;
      case 'brutto_ber':
        return $this->load_brutto_ber_resources();
      break;
      case 'cegauto_ado':
        return $this->load_cegauto_ado_resources();
      break;
      case 'belepo_szabadsag':
        return $this->load_belepo_szabadsag_resources();
      break;
      case 'anyak_szabadsaga':
        return $this->load_anyak_szabadsaga_resources();
      break;
      case 'ingatlan_ertekesites':
        return $this->load_ingatlan_ertekesites_resources();
      break;
      case 'osztalekado':
        return $this->load_osztalekado_resources();
      break;
      case 'cafeteria':
        return $this->load_cafeteria_resources();
      break;
      /** Hozzáadva @ 2021-02-08 */
      case 'reprezentacio_ado':
        return $this->load_reprezentacio_ado_resources();
      break;
      case 'cegtelefon_ado':
        return $this->load_cegtelefon_ado_resources();
      break;
      case 'megbizasi_dij':
        return $this->load_megbizasi_dij_resources();
      break;
    }

    return false;
  }

  public function getSettingsValue( $key )
  {
    $res = array();

    $value = false;

    // temp
    $res['calc_comment'] = $this->get_option($this->getVersion(true).'calc_comment', false );

    $res['alapszabadsag'] = (float)$this->get_option($this->getVersion(true).'alapszabadsag', 0 );
    $res['betegszabadsag'] = (float)$this->get_option($this->getVersion(true).'betegszabadsag', 0 );
    $res['potszabi_ha16evnelfiatalabbgyereketnevel'] = (float)$this->get_option($this->getVersion(true).'potszabi_ha16evnelfiatalabbgyereketnevel', 0 );
    $res['potszabi_megvaltozott_munkakepessegu'] = (float)$this->get_option($this->getVersion(true).'potszabi_megvaltozott_munkakepessegu', 0 );

    $res['ado_tb'] = (float)$this->get_option($this->getVersion(true).'ado_tb', 0 );
    $res['ado_szja'] = (float)$this->get_option($this->getVersion(true).'ado_szja', 0 );
    $res['ado_termeszetegeszseg'] = (float)$this->get_option($this->getVersion(true).'ado_termeszetegeszseg', 0 );
    $res['ado_penzbeli_egeszseg'] = (float)$this->get_option($this->getVersion(true).'ado_penzbeli_egeszseg', 0 );
    $res['ado_nyugdij'] = (float)$this->get_option($this->getVersion(true).'ado_nyugdij', 0 );
    $res['ado_munkaerppiac'] = (float)$this->get_option($this->getVersion(true).'ado_munkaerppiac', 0 );
    $res['ado_szocialis_hozzajarulas'] = (float)$this->get_option($this->getVersion(true).'ado_szocialis_hozzajarulas', 0 );
    $res['ado_szakkepzesi_hozzajarulas'] = (float)$this->get_option($this->getVersion(true).'ado_szakkepzesi_hozzajarulas', 0 );
    $res['ado_kisvallalati'] = (float)$this->get_option($this->getVersion(true).'ado_kisvallalati', 0 );
    $res['ado_caf_adoalap_kieg'] = (float)$this->get_option($this->getVersion(true).'ado_caf_adoalap_kieg', 0 );

    $res['adokedvezmeny_frisshazasok'] = (float)$this->get_option($this->getVersion(true).'adokedvezmeny_frisshazasok', 0 );
    $res['adokedvezmeny_szemelyi'] = (float)$this->get_option($this->getVersion(true).'adokedvezmeny_szemelyi', 0 );
    $res['adokedvezmeny_csalad_gyermek1'] = (float)$this->get_option($this->getVersion(true).'adokedvezmeny_csalad_gyermek1', 0 );
    $res['adokedvezmeny_csalad_gyermek2'] = (float)$this->get_option($this->getVersion(true).'adokedvezmeny_csalad_gyermek2', 0 );
    $res['adokedvezmeny_csalad_gyermek3'] = (float)$this->get_option($this->getVersion(true).'adokedvezmeny_csalad_gyermek3', 0 );

    $res['minimalber'] = (float)$this->get_option($this->getVersion(true).'minimalber', 0 );

    $value = $res[$key];

    return $value;
  }

  private function load_brutto_ber_resources()
  {
    $res = array();

    $res['calc_comment'] = $this->getSettingsValue('calc_comment');

    $res['minimalber'] = $this->getSettingsValue('minimalber');

    $res['ado_szja'] = $this->getSettingsValue('ado_szja');
    $res['ado_tb'] = $this->getSettingsValue('ado_tb');
    $res['ado_termeszetegeszseg'] = $this->getSettingsValue('ado_termeszetegeszseg');
    $res['ado_penzbeli_egeszseg'] = $this->getSettingsValue('ado_penzbeli_egeszseg');
    $res['ado_nyugdij'] = $this->getSettingsValue('ado_nyugdij');
    $res['ado_munkaerppiac'] = $this->getSettingsValue('ado_munkaerppiac');

    $res['ado_szocialis_hozzajarulas'] = $this->getSettingsValue('ado_szocialis_hozzajarulas');
    $res['ado_szakkepzesi_hozzajarulas'] = $this->getSettingsValue('ado_szakkepzesi_hozzajarulas');
    $res['ado_kisvallalati'] = $this->getSettingsValue('ado_kisvallalati');

    $res['adokedvezmeny_frisshazasok'] = $this->getSettingsValue('adokedvezmeny_frisshazasok');
    $res['adokedvezmeny_szemelyi'] = $this->getSettingsValue('adokedvezmeny_szemelyi');
    $res['adokedvezmeny_csalad_gyermek1'] = $this->getSettingsValue('adokedvezmeny_csalad_gyermek1');
    $res['adokedvezmeny_csalad_gyermek2'] = $this->getSettingsValue('adokedvezmeny_csalad_gyermek2');
    $res['adokedvezmeny_csalad_gyermek3'] = $this->getSettingsValue('adokedvezmeny_csalad_gyermek3');

    // Form resources
    $forms = array();
    $forms['munkavallalo_kedvezmenyek'] = $this->loadMunkavallaloKedvezmenyek();
    $res['forms'] = $forms;

    return $res;
  }

  private function load_berkalkulator_resources()
  {
    $res = array();

    $res['calc_comment'] = $this->getSettingsValue('calc_comment');

    $res['minimalber'] = $this->getSettingsValue('minimalber');

    $res['ado_szja'] = $this->getSettingsValue('ado_szja');
    $res['ado_tb'] = $this->getSettingsValue('ado_tb');
    $res['ado_termeszetegeszseg'] = $this->getSettingsValue('ado_termeszetegeszseg');
    $res['ado_penzbeli_egeszseg'] = $this->getSettingsValue('ado_penzbeli_egeszseg');
    $res['ado_nyugdij'] = $this->getSettingsValue('ado_nyugdij');
    $res['ado_munkaerppiac'] = $this->getSettingsValue('ado_munkaerppiac');

    $res['ado_szocialis_hozzajarulas'] = $this->getSettingsValue('ado_szocialis_hozzajarulas');
    $res['ado_szakkepzesi_hozzajarulas'] = $this->getSettingsValue('ado_szakkepzesi_hozzajarulas');
    $res['ado_kisvallalati'] = $this->getSettingsValue('ado_kisvallalati');

    $res['adokedvezmeny_frisshazasok'] = $this->getSettingsValue('adokedvezmeny_frisshazasok');
    $res['adokedvezmeny_szemelyi'] = $this->getSettingsValue('adokedvezmeny_szemelyi');
    $res['adokedvezmeny_csalad_gyermek1'] = $this->getSettingsValue('adokedvezmeny_csalad_gyermek1');
    $res['adokedvezmeny_csalad_gyermek2'] = $this->getSettingsValue('adokedvezmeny_csalad_gyermek2');
    $res['adokedvezmeny_csalad_gyermek3'] = $this->getSettingsValue('adokedvezmeny_csalad_gyermek3');

    // Form resources
    $forms = array();
    $forms['munkavallalo_kedvezmenyek'] = $this->loadMunkavallaloKedvezmenyek();
    $res['forms'] = $forms;

    return $res;
  }

  private function load_teljes_berkoltseg_resources()
  {
    $res = array();

    $res['calc_comment'] = $this->getSettingsValue('calc_comment');

    $res['minimalber'] = $this->getSettingsValue('minimalber');

    $res['ado_szja'] = $this->getSettingsValue('ado_szja');
    $res['ado_tb'] = $this->getSettingsValue('ado_tb');
    $res['ado_termeszetegeszseg'] = $this->getSettingsValue('ado_termeszetegeszseg');
    $res['ado_penzbeli_egeszseg'] = $this->getSettingsValue('ado_penzbeli_egeszseg');
    $res['ado_nyugdij'] = $this->getSettingsValue('ado_nyugdij');
    $res['ado_munkaerppiac'] = $this->getSettingsValue('ado_munkaerppiac');

    $res['ado_szocialis_hozzajarulas'] = $this->getSettingsValue('ado_szocialis_hozzajarulas');
    $res['ado_szakkepzesi_hozzajarulas'] = $this->getSettingsValue('ado_szakkepzesi_hozzajarulas');
    $res['ado_kisvallalati'] = $this->getSettingsValue('ado_kisvallalati');

    $res['adokedvezmeny_frisshazasok'] = $this->getSettingsValue('adokedvezmeny_frisshazasok');
    $res['adokedvezmeny_szemelyi'] = $this->getSettingsValue('adokedvezmeny_szemelyi');
    $res['adokedvezmeny_csalad_gyermek1'] = $this->getSettingsValue('adokedvezmeny_csalad_gyermek1');
    $res['adokedvezmeny_csalad_gyermek2'] = $this->getSettingsValue('adokedvezmeny_csalad_gyermek2');
    $res['adokedvezmeny_csalad_gyermek3'] = $this->getSettingsValue('adokedvezmeny_csalad_gyermek3');

    // Form resources
    $forms = array();
    $forms['munkavallalo_kedvezmenyek'] = $this->loadMunkavallaloKedvezmenyek();
    $res['forms'] = $forms;

    return $res;
  }

  private function load_netto_ber_resources()
  {
    $res = array();

    $res['calc_comment'] = $this->getSettingsValue('calc_comment');

    $res['ado_szja'] = $this->getSettingsValue('ado_szja');
    $res['ado_tb'] = $this->getSettingsValue('ado_tb');
    $res['ado_termeszetegeszseg'] = $this->getSettingsValue('ado_termeszetegeszseg');
    $res['ado_penzbeli_egeszseg'] = $this->getSettingsValue('ado_penzbeli_egeszseg');
    $res['ado_nyugdij'] = $this->getSettingsValue('ado_nyugdij');
    $res['ado_munkaerppiac'] = $this->getSettingsValue('ado_munkaerppiac');

    $res['adokedvezmeny_frisshazasok'] = $this->getSettingsValue('adokedvezmeny_frisshazasok');
    $res['adokedvezmeny_szemelyi'] = $this->getSettingsValue('adokedvezmeny_szemelyi');
    $res['adokedvezmeny_csalad_gyermek1'] = $this->getSettingsValue('adokedvezmeny_csalad_gyermek1');
    $res['adokedvezmeny_csalad_gyermek2'] = $this->getSettingsValue('adokedvezmeny_csalad_gyermek2');
    $res['adokedvezmeny_csalad_gyermek3'] = $this->getSettingsValue('adokedvezmeny_csalad_gyermek3');

    // Form resources
    $forms = array();
    $res['forms'] = $forms;

    return $res;
  }

  private function load_belepo_szabadsag_resources()
  {
    $res = array();

    $res['calc_comment'] = $this->getSettingsValue('calc_comment');

    $res['alapszabadsag'] = $this->getSettingsValue('alapszabadsag');
    $res['betegszabadsag'] = $this->getSettingsValue('betegszabadsag');
    $res['potszabi_ha16evnelfiatalabbgyereketnevel'] = $this->getSettingsValue('potszabi_ha16evnelfiatalabbgyereketnevel');
    $res['potszabi_megvaltozott_munkakepessegu'] = $this->getSettingsValue('potszabi_megvaltozott_munkakepessegu');

    // Form resources
    $forms = array();
    $res['forms'] = $forms;

    return $res;
  }

  private function load_anyak_szabadsaga_resources()
  {
    $res = array();

    $res['calc_comment'] = $this->getSettingsValue('calc_comment');

    $res['alapszabadsag'] = $this->getSettingsValue('alapszabadsag');
    $res['betegszabadsag'] = $this->getSettingsValue('betegszabadsag');
    $res['potszabi_ha16evnelfiatalabbgyereketnevel'] = $this->getSettingsValue('potszabi_ha16evnelfiatalabbgyereketnevel');
    $res['potszabi_megvaltozott_munkakepessegu'] = $this->getSettingsValue('potszabi_megvaltozott_munkakepessegu');

    // Form resources
    $forms = array();
    $res['forms'] = $forms;

    return $res;
  }

  private function load_cegauto_ado_resources()
  {
    $res = array();

    $res['calc_comment'] = $this->getSettingsValue('calc_comment');

    // Form resources
    $forms = array();
    $forms['kornyezetvedelmi_osztalyok'] = $this->loadGepjarmuKornyezetvedelmiOsztalyok();
    $forms['teljesitmeny_osztalyok'] = $this->loadGepjarmuTeljesitmenyOsztalyok();

    $res['forms'] = $forms;

    return $res;
  }

  private function load_ingatlan_ertekesites_resources()
  {
    $res = array();

    $res['calc_comment'] = $this->getSettingsValue('calc_comment');

    $res['ado_szja'] = $this->getSettingsValue('ado_szja');

    // Form resources
    $forms = array();
    $res['forms'] = $forms;

    return $res;
  }

  private function load_osztalekado_resources()
  {
    $res = array();

    $res['calc_comment'] = $this->getSettingsValue('calc_comment');

    $res['minimalber'] = $this->getSettingsValue('minimalber');
    $res['ado_szocialis_hozzajarulas'] = $this->getSettingsValue('ado_szocialis_hozzajarulas');
    $res['ado_szja'] = $this->getSettingsValue('ado_szja');
    $res['ado_kisvallalati'] = $this->getSettingsValue('ado_kisvallalati');
    $res['ado_termeszetegeszseg'] = $this->getSettingsValue('ado_termeszetegeszseg');
    $res['ado_penzbeli_egeszseg'] = $this->getSettingsValue('ado_penzbeli_egeszseg');
    $res['ado_nyugdij'] = $this->getSettingsValue('ado_nyugdij');
    $res['ado_munkaerppiac'] = $this->getSettingsValue('ado_munkaerppiac');

    $res['ado_szocialis_hozzajarulas'] = $this->getSettingsValue('ado_szocialis_hozzajarulas');
    $res['ado_szakkepzesi_hozzajarulas'] = $this->getSettingsValue('ado_szakkepzesi_hozzajarulas');

    // Form resources
    $forms = array();
    $res['forms'] = $forms;

    return $res;
  }

  private function load_cafeteria_resources()
  {
    $res = array();

    $res['calc_comment'] = $this->getSettingsValue('calc_comment');

    $res['ado_caf_adoalap_kieg'] = $this->getSettingsValue('ado_caf_adoalap_kieg');
    $res['ado_szja'] = $this->getSettingsValue('ado_szja');
    $res['ado_tb'] = $this->getSettingsValue('ado_tb');
    $res['ado_kisvallalati'] = $this->getSettingsValue('ado_kisvallalati');
    $res['ado_termeszetegeszseg'] = $this->getSettingsValue('ado_termeszetegeszseg');
    $res['ado_penzbeli_egeszseg'] = $this->getSettingsValue('ado_penzbeli_egeszseg');
    $res['ado_nyugdij'] = $this->getSettingsValue('ado_nyugdij');
    $res['ado_munkaerppiac'] = $this->getSettingsValue('ado_munkaerppiac');

    $res['ado_szocialis_hozzajarulas'] = $this->getSettingsValue('ado_szocialis_hozzajarulas');
    $res['ado_szakkepzesi_hozzajarulas'] = $this->getSettingsValue('ado_szakkepzesi_hozzajarulas');

    // Form resources
    $forms = array();
    $res['forms'] = $forms;

    return $res;
  }
  
  /**
   * Hozzáadva 2021-es kalkulátor verziónál
   * @since 2021-02-08
   */
  private function load_cegtelefon_ado_resources()
  {
    $res = array();

    $res['calc_comment'] = $this->getSettingsValue('calc_comment');

    // Adóalap kiegészítés - cafetériából
    $res['ado_caf_adoalap_kieg'] = $this->getSettingsValue('ado_caf_adoalap_kieg');
    $res['ado_szja'] = $this->getSettingsValue('ado_szja');
    $res['ado_kisvallalati'] = $this->getSettingsValue('ado_kisvallalati');

    $res['ado_szocialis_hozzajarulas'] = $this->getSettingsValue('ado_szocialis_hozzajarulas');
    $res['ado_szakkepzesi_hozzajarulas'] = $this->getSettingsValue('ado_szakkepzesi_hozzajarulas');

    // Form resources
    $forms = array();
    $res['forms'] = $forms;

    return $res;
  }

  /**
   * Hozzáadva 2021-es kalkulátor verziónál
   * @since 2021-02-08
   */
  private function load_megbizasi_dij_resources()
  {
    $res = array();

    $res['calc_comment'] = $this->getSettingsValue('calc_comment');
    
    $res['ado_tb'] = $this->getSettingsValue('ado_tb');
    $res['minimalber'] = $this->getSettingsValue('minimalber');
    $res['ado_szja'] = $this->getSettingsValue('ado_szja');
    $res['ado_kisvallalati'] = $this->getSettingsValue('ado_kisvallalati');

    $res['ado_szocialis_hozzajarulas'] = $this->getSettingsValue('ado_szocialis_hozzajarulas');
    $res['ado_szakkepzesi_hozzajarulas'] = $this->getSettingsValue('ado_szakkepzesi_hozzajarulas');

    // Form resources
    $forms = array();
    $res['forms'] = $forms;

    return $res;
  }

  /**
   * Hozzáadva 2021-es kalkulátor verziónál
   * @since 2021-02-08
   */
  private function load_reprezentacio_ado_resources()
  {
    $res = array();

    $res['calc_comment'] = $this->getSettingsValue('calc_comment');

    // Adóalap kiegészítés - cafetériából
    $res['ado_caf_adoalap_kieg'] = $this->getSettingsValue('ado_caf_adoalap_kieg');
    $res['ado_szja'] = $this->getSettingsValue('ado_szja');
    $res['ado_kisvallalati'] = $this->getSettingsValue('ado_kisvallalati');

    $res['ado_szocialis_hozzajarulas'] = $this->getSettingsValue('ado_szocialis_hozzajarulas');
    $res['ado_szakkepzesi_hozzajarulas'] = $this->getSettingsValue('ado_szakkepzesi_hozzajarulas');

    // Form resources
    $forms = array();
    $res['forms'] = $forms;

    return $res;
  }

  public function ingatlan_ertekesites_jovedelem_szorzo( $szerzesi_eve = false, $atruhazas_eve = false )
  {
    $value = 1;
    $ydiff = $atruhazas_eve - $szerzesi_eve;

    if ( $ydiff >= 0 && $ydiff < 2 ) {
      $value = 1; // 100%
    } elseif( $ydiff == 2 ){
      $value = 0.9; // 90%
    } elseif( $ydiff == 3 ){
      $value = 0.6; // 60%
    } elseif( $ydiff == 4 ){
      $value = 0.3; // 30%
    } elseif( $ydiff >= 5 ){
      $value = 0; // 0%
    }

    return $value;
  }

  public function csaladiAdokedvezmenyOsszege( $eltartott = 0, $kedvezmenyezett = 0, $settings = array() )
  {
    $value = 0;
    $alap = 0;

    if ($eltartott == 1) {
      $alap = $settings['adokedvezmeny_csalad_gyermek1'];
    }elseif($eltartott == 2){
      $alap = $settings['adokedvezmeny_csalad_gyermek2'];
    }elseif($eltartott >= 3){
      $alap = $settings['adokedvezmeny_csalad_gyermek3'];
    }

    $value = $alap * $kedvezmenyezett;

    return $value;
  }

  public function getCafeteriaItemAdoalapLimit()
  {
    $limits = array();

    $limits['Sportrendezvényre szóló belépőjegy, bérlet'] = 161000;
    $limits['Kulturális szolgáltatásra szóló belépőjegy, bérlet'] = 161000;    
    $limits['Csekély értékű ajándék'] = 16100;

    $limits['SZÉP kártya vendéglátás'] = 265000;
    $limits['SZÉP kártya szálláshely'] = 400000;
    $limits['SZÉP kártya szabadidő'] = 135000;

    return $limits;
  }

  public function getCafeteriaGroupByTitle( $item_title = false )
  {
    if ( !$item_title ) {
      return false;
    }

    $groups = $this->cafeteriaGroups();
    foreach ((array)$groups as $gid => $group) {
      foreach ((array)$group['items'] as $title ) {
        if ($title == $item_title) {
          return array(
            'ID' => $gid,
            'title' => $group['title']
          );
        }
      }
    }

    return false;
  }

  public function cafeteriaGroups()
  {
    $g = array();

    // Adómentes
    $g[1] = array(
      'title' => 'Adómentes',
      'items' => array(
        'Számítógéphasználat',
        'Iskolarendszeren kívüli oktatás támogatása',
        'Bőlcsödei, óvodai szolgáltatás',
        'Bőlcsödei, óvodai étkeztetés',
        'Sportrendezvényre szóló belépőjegy, bérlet',
        'Kulturális szolgáltatásra szóló belépőjegy, bérlet',
        'Munkaruházat',
        'Védőoltás',
      )
    );

    // Béren kívüli juttatás
    $g[2] = array(
      'title' => 'Béren kívüli juttatás',
      'items' => array(
        'SZÉP kártya vendéglátás',
        'SZÉP kártya szálláshely',
        'SZÉP kártya szabadidő',
      )
    );

    // Egyesmeghatározott juttatás
    $g[3] = array(
      'title' => 'Egyesmeghatározott juttatás',
      'items' => array(
        'Önkéntes kölcsönös biztosítópénztár célzott szolgáltatásra befizetett összeg',
        'Csekély értékű ajándék',
        'Munkavállalónak juttatott hivatali, üzleti utazáshoz kapcsolódó étkezés vagy más szolgáltatás',
      )
    );

    // Bérjövedelem
    $g[4] = array(
      'title' => 'Bérjövedelem',
      'items' => array(
        'Erzsébet utalvány',
        'Helyi utazási bérlet',
        'Mobilitási célú lakhatási támogatás',
        'Adóköteles biztosítási díj',
        'Kockázati biztosítás',
        'Iskolarendszerű oktatás támogatása',
        'Diákhitel támogatása',
        'Lakáscélú támogatás',
        'Munkahelyi étkeztetés',
        'Iskolakezdési támogatás',
        'Üdülési szolgáltatás',
      )
    );

    return $g;
  }

  // TODO: calc adatok beszúrása és helyben számolása
  public function calculateBruttoByNetto( $netto, $calc = array() )
  {
    $brutto = 0;
    $allcalc = 0;
    $kill = false;
    $step = 0;

    while ($allcalc == 0 || !$kill )
    {
      $step++;
      if ($step >= 1000) {
        $kill = true;
      }
      $eachcalc = 0;
      foreach ( (array)$calc as $c ) {
        $eachcalc += $brutto * ($c/100);
      }
      $allcalc = $eachcalc;

      if ($brutto == 0) {
        $brutto = 1;
      }

      //echo "step: ".$step." - brutto: ".$brutto." - netto: ".($brutto - $allcalc)." - levon: ".$allcalc."<br>";

      if ( (($brutto - $allcalc) * 2) >= $netto ) {
        $kill = true;
      }

      if (!$kill) {
        $brutto = $brutto * 2;
      }
    }

    $calced_net = ($brutto - $allcalc);

    while( $calced_net < $netto )
    {
      $brutto += 1;

      $eachcalc = 0;
      foreach ( (array)$calc as $c ) {
        $eachcalc += $brutto * ($c/100);
      }
      $allcalc = $eachcalc;
      $calced_net = $brutto - $allcalc;
    }

    return $brutto;
  }

  // Pótszabadság 16 évnél fiatalabb gyermek szerint
  public function potszabadasg16evfiatalabbGyerekSzerint( $gyermek )
  {
    $potszabi = 0;

    if ( $gyermek == 1 ) {
      $potszabi = 2;
    }
    elseif( $gyermek == 2 )
    {
      $potszabi = 4;
    }
    elseif( $gyermek >= 3 )
    {
      $potszabi = 7;
    }

    return $potszabi;
  }

  // Pótszabadság kor szerint
  public function potszabadasgKorSzerint( $kor )
  {
    $potszabi = 0;

    if ( $kor >= 25 && $kor < 28 ) {
      $potszabi = 1;
    }
    elseif( $kor >= 28 && $kor < 31 )
    {
      $potszabi = 2;
    }
    elseif( $kor >= 31 && $kor < 33 )
    {
      $potszabi = 3;
    }
    elseif( $kor >= 33 && $kor < 35 )
    {
      $potszabi = 4;
    }
    elseif( $kor >= 35 && $kor < 37 )
    {
      $potszabi = 5;
    }
    elseif( $kor >= 37 && $kor < 39 )
    {
      $potszabi = 6;
    }
    elseif( $kor >= 39 && $kor < 41 )
    {
      $potszabi = 7;
    }
    elseif( $kor >= 41 && $kor < 43 )
    {
      $potszabi = 8;
    }
    elseif( $kor >= 43 && $kor < 45 )
    {
      $potszabi = 9;
    }
    elseif( $kor >= 45 )
    {
      $potszabi = 10;
    }

    return $potszabi;
  }

  public function calcCegautoAdo( $emission, $kw = -1)
  {
    $classes = $this->loadGepjarmuTeljesitmenyOsztalyok();
    $price = false;
    foreach ( (array)$classes as $c ) {
      if (in_array($emission, $c['possible_emissions'])) {
        foreach ((array)$c['kw_groups'] as $kwg ) {
          if ($kw >= $kwg['min'] && $kw <= $kwg['max'] ) {
            if ( !$price ) {
              $price = $kwg['value'];
            }
          }
        }
      }
    }

    return $price;
  }

  protected function loadMunkavallaloKedvezmenyek()
  {
    $row = array();

    $row[] = array(
      'title' => 'Kedvezményre nem jogosult',
      'calc' => array(
        'szochokiva' => array(
          'kedvezmeny_mertek' => 0,
          'kedvezmeny_max' => 0
        ),
        'szokho' => array(
          'kedvezmeny_mertek' => 0,
          'kedvezmeny_max' => 0
        )
      )
    );

    $row[] = array(
      'title' => 'Szakképzettséget nem igénylő munkakör',
      'calc' => array(
        'szochokiva' => array(
          'kedvezmeny_mertek' => 50,
          'kedvezmeny_max' => '{minimalber}'
        ),
        'szokho' => array(
          'kedvezmeny_mertek' => 0,
          'kedvezmeny_max' => '{minimalber}'
        )
      )
    );

    $row[] = array(
      'title' => 'Munkaerő piacra lépő a foglalkoztatás első 2 évében',
      'calc' => array(
        'szochokiva' => array(
          'kedvezmeny_mertek' => 100,
          'kedvezmeny_max' => '{minimalber}'
        ),
        'szokho' => array(
          'kedvezmeny_mertek' => 100,
          'kedvezmeny_max' => '{minimalber}'
        )
      )
    );

    $row[] = array(
      'title' => 'Munkaerő piacra lépő a foglalkoztatás 3. évében',
      'calc' => array(
        'szochokiva' => array(
          'kedvezmeny_mertek' => 50,
          'kedvezmeny_max' => '{minimalber}'
        ),
        'szokho' => array(
          'kedvezmeny_mertek' => 0,
          'kedvezmeny_max' => '{minimalber}'
        )
      )
    );

    $row[] = array(
      'title' => '3 v. több gyermeket nevelő munkaerőpiacra lépő nő a foglalkoztatás első 3 évében',
      'calc' => array(
        'szochokiva' => array(
          'kedvezmeny_mertek' => 100,
          'kedvezmeny_max' => '{minimalber}'
        ),
        'szokho' => array(
          'kedvezmeny_mertek' => 100,
          'kedvezmeny_max' => '{minimalber}'
        )
      )
    );

    $row[] = array(
      'title' => '3 v. több gyermeket nevelő munkaerőpiacra lépő nő a foglalkoztatás első 4-5. évében',
      'calc' => array(
        'szochokiva' => array(
          'kedvezmeny_mertek' => 50,
          'kedvezmeny_max' => '{minimalber}'
        ),
        'szokho' => array(
          'kedvezmeny_mertek' => 0,
          'kedvezmeny_max' => '{minimalber}'
        )
      )
    );

    $row[] = array(
      'title' => 'Megváltozott munkaképességű munkavállaló',
      'calc' => array(
        'szochokiva' => array(
          'kedvezmeny_mertek' => 100,
          'kedvezmeny_max' => '{minimalber}'
        ),
        'szokho' => array(
          'kedvezmeny_mertek' => 100,
          'kedvezmeny_max' => '{minimalber_ketszeres}'
        )
      )
    );

    $row[] = array(
      'title' => 'Doktori fokozattal rendelkező kutató',
      'calc' => array(
        'szochokiva' => array(
          'kedvezmeny_mertek' => 100,
          'kedvezmeny_max' => 500000
        ),
        'szokho' => array(
          'kedvezmeny_mertek' => 100,
          'kedvezmeny_max' => 500000
        )
      )
    );

    $row[] = array(
      'title' => 'Doktori képzésben résztvevő hallgató, doktorjelölt',
      'calc' => array(
        'szochokiva' => array(
          'kedvezmeny_mertek' => 50,
          'kedvezmeny_max' => 200000
        ),
        'szokho' => array(
          'kedvezmeny_mertek' => 0,
          'kedvezmeny_max' => 200000
        )
      )
    );

    $row[] = array(
      'title' => 'Kutató-fejlesztő munkakörben foglalkoztatott',
      'calc' => array(
        'szochokiva' => array(
          'kedvezmeny_mertek' => 50,
          'kedvezmeny_max' => -1
        ),
        'szokho' => array(
          'kedvezmeny_mertek' => 0,
          'kedvezmeny_max' => -1
        )
      )
    );

    return $row;
  }

  protected function loadGepjarmuTeljesitmenyOsztalyok()
  {
    $row = array();

    $row[] = array(
      'possible_emissions' => array('0','1','2','3','4'),
      'kw_groups' => array(
        array('min' => 0, 'max' => 50, 'value' => 16500),
        array('min' => 51, 'max' => 90, 'value' => 22000),
        array('min' => 91, 'max' => 120, 'value' => 33000),
        array('min' => 121, 'max' => 999999999, 'value' => 44000),
      )
    );
    $row[] = array(
      'possible_emissions' => array('6','7','8','9','10'),
      'kw_groups' => array(
        array('min' => 0, 'max' => 50, 'value' => 8800),
        array('min' => 51, 'max' => 90, 'value' => 11000),
        array('min' => 91, 'max' => 120, 'value' => 22000),
        array('min' => 121, 'max' => 999999999, 'value' => 33000),
      )
    );
    $row[] = array(
      'possible_emissions' => array('5','14','15'),
      'kw_groups' => array(
        array('min' => 0, 'max' => 50, 'value' => 7700),
        array('min' => 51, 'max' => 90, 'value' => 8800),
        array('min' => 91, 'max' => 120, 'value' => 11000),
        array('min' => 121, 'max' => 999999999, 'value' => 22000),
      )
    );
    $row[] = array(
      'possible_emissions' => array('5E','5P','5Z', '5N'),
      'kw_groups' => array(
        array('min' => 0, 'max' => 50, 'value' => 0),
        array('min' => 51, 'max' => 90, 'value' => 0),
        array('min' => 91, 'max' => 120, 'value' => 0),
        array('min' => 121, 'max' => 999999999, 'value' => 0),
      )
    );

    return $row;
  }
  protected function loadGepjarmuKornyezetvedelmiOsztalyok()
  {
    $row = array();
    $exc_i = array(11,12,13);

    for ($i=0; $i<=15; $i++) {
      if (in_array($i,$exc_i)) {
        continue;
      }
      $row[$i] = $i.'. osztály';
    }

    $row['5E'] = '5E';
    $row['5P'] = '5P';
    $row['5Z'] = '5Z';
    $row['5N'] = '5N';

    return $row;
  }
}

class Calculators extends CalculatorBase
{
  public $setted_version = false;

  function __construct( $version = false )
  {
    if ( $version ) {
      $this->setted_version = $version;
    }

    parent::__construct( $version );
  }

  public function calc( $calc, $data )
  {
    $version = $this->getVersion();

    $version = str_replace(array("/","."), array("_", ""), $version);

    $calcv = "CalculatorV".$version;

    if ( !class_exists($calcv) ) {
      return false;
    }

    $calc_class = new $calcv( $this->setted_version );

    return $calc_class->calculate($calc, $data);
  }
}

/***********************************************
* 2019-es jogszabályoknak megfelelő kalkulátor *
************************************************/
class CalculatorV2019 extends CalculatorBase implements CalculatorVersion
{
  public function __construct( $version = false )
  {
    parent::__construct( $version );
    return $this;
  }

  public function calcBerAdoLevonasok( $brutto_ber, $data, $settings )
  {
    $ret = array();

    $csaladi_adokedvezmeny_osszege = 0;
    if ($data['csaladkedvezmenyre_jogosult'] == 'Igen') {
      $csaladi_adokedvezmeny_osszege = $this->csaladiAdokedvezmenyOsszege( (int)$data['csalad_eltartott_gyermek'], (int)$data['csalad_eltartott_gyermek_kedvezmenyezett'], $settings );
    }

    $ervenyesitheto_jarulekkedvezmeny = 0;
    $friss_hazasok_kedvezmeny = 0;
    $szemelyi_kedvezmeny = 0;
    $ervenyesitheto_termeszetbeni_kedvezmeny = 0;
    $ervenyesitheto_penzbeni_kedvezmeny = 0;

    if ($data['frisshazas_jogosult'] == 'Igen') {
      $friss_hazasok_kedvezmeny = $settings['adokedvezmeny_frisshazasok'];
    }

    if ($data['szemelyikedvezmeny_jogosult'] == 'Igen') {
      $szemelyi_kedvezmeny = $settings['adokedvezmeny_szemelyi'];
    }

    $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege+$friss_hazasok_kedvezmeny-$brutto_ber;
    $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;

    $ervenyesitheto_jarulekkedvezmeny = $csaladi_adokedvezmeny_maradekalap * 0.15;
    $ervenyesitheto_jarulekkedvezmeny = ($ervenyesitheto_jarulekkedvezmeny < 0) ? 0 : $ervenyesitheto_jarulekkedvezmeny;

    $ervenyesitheto_termeszetbeni_kedvezmeny = $ervenyesitheto_jarulekkedvezmeny - ($brutto_ber * ($settings['ado_termeszetegeszseg']/100));
    $ervenyesitheto_termeszetbeni_kedvezmeny = ($ervenyesitheto_termeszetbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_termeszetbeni_kedvezmeny;

    $ervenyesitheto_penzbeni_kedvezmeny = $ervenyesitheto_termeszetbeni_kedvezmeny - ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100));
    $ervenyesitheto_penzbeni_kedvezmeny = ($ervenyesitheto_penzbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_penzbeni_kedvezmeny;

    $ret['ado_szja'] = (($brutto_ber-$friss_hazasok_kedvezmeny-$csaladi_adokedvezmeny_osszege) * ($settings['ado_szja']/100)) - $szemelyi_kedvezmeny;
    $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
    $ret['ado_szja'] = round($ret['ado_szja']);

    $ret['ado_termeszetegeszseg'] = ($brutto_ber * ($settings['ado_termeszetegeszseg']/100)) - $ervenyesitheto_jarulekkedvezmeny;
    $ret['ado_termeszetegeszseg'] = ($ret['ado_termeszetegeszseg'] < 0) ? 0 : $ret['ado_termeszetegeszseg'];
    $ret['ado_termeszetegeszseg'] = round($ret['ado_termeszetegeszseg']);

    $ret['ado_penzbeli_egeszseg'] = ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100)) - $ervenyesitheto_termeszetbeni_kedvezmeny;
    $ret['ado_penzbeli_egeszseg'] = ($ret['ado_penzbeli_egeszseg'] < 0) ? 0 : $ret['ado_penzbeli_egeszseg'];
    $ret['ado_penzbeli_egeszseg'] = round($ret['ado_penzbeli_egeszseg']);

    $ret['ado_nyugdij'] = ($brutto_ber * ($settings['ado_nyugdij']/100)) - $ervenyesitheto_penzbeni_kedvezmeny;
    $ret['ado_nyugdij'] = ($ret['ado_nyugdij'] < 0) ? 0 : $ret['ado_nyugdij'];
    $ret['ado_nyugdij'] = round($ret['ado_nyugdij']);

    $ret['ado_munkaerppiac'] = $brutto_ber * ($settings['ado_munkaerppiac']/100);
    $ret['ado_munkaerppiac'] = round($ret['ado_munkaerppiac']);

    $sum_minusbrutto = $ret['ado_szja'] + $ret['ado_termeszetegeszseg'] + $ret['ado_penzbeli_egeszseg'] + $ret['ado_nyugdij'] + $ret['ado_munkaerppiac'];

    return array(
      'levonas' => $sum_minusbrutto,
      'params' => $ret
    );
  }
  
  public function findBruttoFromNetto( $netto, $data, $settings )
  {
    $brutto = $netto * 1.5;
    $allcalc = 0;
    $running = true;
    $step = 0;
    $szamolt_netto = 0;
    $xn = 1000;
    $calced = array();

    $steps = array();
    while( $running )
    {
      $step++;

      $calced = $this->calcBerAdoLevonasok($brutto, $data, $settings);
      $levon = (float)$calced['levonas'];
      $szamolt_netto = $brutto - $levon;
      $tol = ($szamolt_netto / $netto) * 100;

      $steps[] = array(
        'br' => round($brutto),
        'le' => $levon,
        'net' => round($szamolt_netto),
        'tol' => $tol
      );

      if ($step >= 1000) {
        $running = false;
      }

      $fdiff = ($netto - $szamolt_netto);
      if ( abs($fdiff) < 1000 ) {
        $xn = 10;
      }
      if ( abs($fdiff) < 10 ) {
        $xn = 1;
      }

      if( $szamolt_netto > $netto ){
        $brutto -= $xn;
      }

      if ( $szamolt_netto < $netto ) {
        $brutto += $xn;
      }
      if ( round($szamolt_netto) == $netto ) {
        $running = false;
      }
    }

    unset($netto);unset($data);unset($settings);

    return array(
      'brutto' => $brutto,
      'values' => $calced
    );
  }
  
  public function calculate( $calc, $data )
  {
    switch ( $calc )
    {
      case 'brutto_ber':
        $ret = array(
          'netto_ber' => 0
        );
        $settings = $this->loadSettings( $calc );

        $netto = $data['netto_ber'];
        $ret['netto_ber'] = $netto;

        $find = $this->findBruttoFromNetto( $netto, $data, $settings, $values );
        $sum_minusbrutto = (int)$find['brutto'];
        $values['find'] = $find;

        $ret['sum_minusbrutto'] = $find['values']['levonas'];
        $ret['brutto_ber'] = $netto_ber + $sum_minusbrutto;

        $ret['ado_szja'] = $find['values']['params']['ado_szja'];
        $ret['ado_termeszetegeszseg'] = $find['values']['params']['ado_termeszetegeszseg'];
        $ret['ado_penzbeli_egeszseg'] = $find['values']['params']['ado_penzbeli_egeszseg'];
        $ret['ado_nyugdij'] = $find['values']['params']['ado_nyugdij'];
        $ret['ado_munkaerppiac'] = $find['values']['params']['ado_munkaerppiac'];

        // Nettó bér alap vége

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'teljes_berkoltseg':
        $ret = array(
          'brutto_ber' => 0
        );
        $settings = $this->loadSettings( $calc );

        $brutto_ber = $data['brutto_ber'];

        $ret['brutto_ber'] = $brutto_ber;

        $csaladi_adokedvezmeny_osszege = 0;
        if ($data['csaladkedvezmenyre_jogosult'] == 'Igen') {
          $csaladi_adokedvezmeny_osszege = $this->csaladiAdokedvezmenyOsszege( (int)$data['csalad_eltartott_gyermek'], (int)$data['csalad_eltartott_gyermek_kedvezmenyezett'], $settings );
        }

        $ervenyesitheto_jarulekkedvezmeny = 0;
        $friss_hazasok_kedvezmeny = 0;
        $szemelyi_kedvezmeny = 0;
        $ervenyesitheto_termeszetbeni_kedvezmeny = 0;
        $ervenyesitheto_penzbeni_kedvezmeny = 0;

        if ($data['frisshazas_jogosult'] == 'Igen') {
          $friss_hazasok_kedvezmeny = $settings['adokedvezmeny_frisshazasok'];
        }

        if ($data['szemelyikedvezmeny_jogosult'] == 'Igen') {
          $szemelyi_kedvezmeny = $settings['adokedvezmeny_szemelyi'];
        }

        $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege+$friss_hazasok_kedvezmeny-$brutto_ber;
        $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;

        $ervenyesitheto_jarulekkedvezmeny = $csaladi_adokedvezmeny_maradekalap * 0.15;
        $ervenyesitheto_jarulekkedvezmeny = ($ervenyesitheto_jarulekkedvezmeny < 0) ? 0 : $ervenyesitheto_jarulekkedvezmeny;

        $ervenyesitheto_termeszetbeni_kedvezmeny = $ervenyesitheto_jarulekkedvezmeny - ($brutto_ber * ($settings['ado_termeszetegeszseg']/100));
        $ervenyesitheto_termeszetbeni_kedvezmeny = ($ervenyesitheto_termeszetbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_termeszetbeni_kedvezmeny;

        $ervenyesitheto_penzbeni_kedvezmeny = $ervenyesitheto_termeszetbeni_kedvezmeny - ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100));
        $ervenyesitheto_penzbeni_kedvezmeny = ($ervenyesitheto_penzbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_penzbeni_kedvezmeny;

        $ret['ado_szja'] = (($brutto_ber-$friss_hazasok_kedvezmeny-$csaladi_adokedvezmeny_osszege) * ($settings['ado_szja']/100)) - $szemelyi_kedvezmeny;
        $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
        $ret['ado_szja'] = round($ret['ado_szja']);

        $ret['ado_termeszetegeszseg'] = ($brutto_ber * ($settings['ado_termeszetegeszseg']/100)) - $ervenyesitheto_jarulekkedvezmeny;
        $ret['ado_termeszetegeszseg'] = ($ret['ado_termeszetegeszseg'] < 0) ? 0 : $ret['ado_termeszetegeszseg'];
        $ret['ado_termeszetegeszseg'] = round($ret['ado_termeszetegeszseg']);

        $ret['ado_penzbeli_egeszseg'] = ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100)) - $ervenyesitheto_termeszetbeni_kedvezmeny;
        $ret['ado_penzbeli_egeszseg'] = ($ret['ado_penzbeli_egeszseg'] < 0) ? 0 : $ret['ado_penzbeli_egeszseg'];
        $ret['ado_penzbeli_egeszseg'] = round($ret['ado_penzbeli_egeszseg']);

        $ret['ado_nyugdij'] = ($brutto_ber * ($settings['ado_nyugdij']/100)) - $ervenyesitheto_penzbeni_kedvezmeny;
        $ret['ado_nyugdij'] = ($ret['ado_nyugdij'] < 0) ? 0 : $ret['ado_nyugdij'];
        $ret['ado_nyugdij'] = round($ret['ado_nyugdij']);

        $ret['ado_munkaerppiac'] = $brutto_ber * ($settings['ado_munkaerppiac']/100);
        $ret['ado_munkaerppiac'] = round($ret['ado_munkaerppiac']);

        $sum_minusbrutto = $ret['ado_szja'] + $ret['ado_termeszetegeszseg'] + $ret['ado_penzbeli_egeszseg'] + $ret['ado_nyugdij'] + $ret['ado_munkaerppiac'];

        $ret['sum_minusbrutto'] = $sum_minusbrutto;
        $netto_ber = $brutto_ber-$sum_minusbrutto;
        $ret['netto_ber'] = $netto_ber;

        $values['csaladi_adokedvezmeny_osszege'] = $csaladi_adokedvezmeny_osszege;
        $values['frisshazas_jogosult'] = $friss_hazasok_kedvezmeny;
        $values['szemelyikedvezmeny_jogosult'] = $szemelyi_kedvezmeny;
        $values['csaladi_adokedvezmeny_maradekalap'] = $csaladi_adokedvezmeny_maradekalap;
        $values['ervenyesitheto_jarulekkedvezmeny'] = $ervenyesitheto_jarulekkedvezmeny;
        $values['ervenyesitheto_termeszetbeni_kedvezmeny'] = $ervenyesitheto_termeszetbeni_kedvezmeny;
        $values['ervenyesitheto_penzbeni_kedvezmeny'] = $ervenyesitheto_penzbeni_kedvezmeny;

        // Nettó bér alap vége

        // Teljes bérköltség számítások
        $mk = $settings['forms']['munkavallalo_kedvezmenyek'];
        $minimalber = $settings['minimalber'];
        $minimalber_ketszeres = $minimalber * 2;
        $szocho_es_kiva_kedvezmeny_alap = 0;
        $szokho_kedvezmeny_alap = 0;


        $sel_mk = (int)$data['munkavallalo_kedvezmeny'];
        $mk_obj = $mk[$sel_mk];

        // szocho kiva alapszámítás
        $kedvezmeny_mertek = $mk_obj['calc']['szochokiva']['kedvezmeny_mertek'];
        $kedvezmeny_max = $mk_obj['calc']['szochokiva']['kedvezmeny_max'];
        $kedvezmeny_max = (float)str_replace(array('{minimalber}', '{minimalber_ketszeres}'), array($minimalber, $minimalber_ketszeres), $kedvezmeny_max);

        $values['szochokiva_kedvezmeny_mertek'] = $kedvezmeny_mertek;
        $values['szochokiva_kedvezmeny_max'] = $kedvezmeny_max;

        if ( $kedvezmeny_mertek > 0 ) {
          if ($kedvezmeny_max > 0) {
            $szocho_es_kiva_kedvezmeny_alap_row = array();
            $szocho_es_kiva_kedvezmeny_alap_row[] = $brutto_ber * ($kedvezmeny_mertek/100);
            $szocho_es_kiva_kedvezmeny_alap_row[] = $kedvezmeny_max * ($kedvezmeny_mertek/100);
            $szocho_es_kiva_kedvezmeny_alap = min($szocho_es_kiva_kedvezmeny_alap_row);
          } else {
            $szocho_es_kiva_kedvezmeny_alap = $brutto_ber * ($kedvezmeny_mertek/100);
          }
        }

        // szokho alapszámítás
        $kedvezmeny_mertek = $mk_obj['calc']['szokho']['kedvezmeny_mertek'];
        $kedvezmeny_max = $mk_obj['calc']['szokho']['kedvezmeny_max'];
        $kedvezmeny_max = (float)str_replace(array('{minimalber}', '{minimalber_ketszeres}'), array($minimalber, $minimalber_ketszeres), $kedvezmeny_max);

        $values['szokho_kedvezmeny_mertek'] = $kedvezmeny_mertek;
        $values['szokho_kedvezmeny_max'] = $kedvezmeny_max;

        if ( $kedvezmeny_mertek > 0 ) {
          if ($kedvezmeny_max > 0) {
            $szokho_kedvezmeny_alap_row = array();
            $szokho_kedvezmeny_alap_row[] = $brutto_ber * ($kedvezmeny_mertek/100);
            $szokho_kedvezmeny_alap_row[] = $kedvezmeny_max * ($kedvezmeny_mertek/100);
            $szokho_kedvezmeny_alap = min($szokho_kedvezmeny_alap_row);
          } else {
            $szokho_kedvezmeny_alap = $brutto_ber * ($kedvezmeny_mertek/100);
          }
        }


        $ret['ado_szocialis_hozzajarulas'] = ($brutto_ber - $szocho_es_kiva_kedvezmeny_alap) * ($settings['ado_szocialis_hozzajarulas']/100);
        $ret['ado_szocialis_hozzajarulas'] = round($ret['ado_szocialis_hozzajarulas']);

        $ret['ado_szakkepzesi_hozzajarulas'] = ($brutto_ber - $szokho_kedvezmeny_alap) * ($settings['ado_szakkepzesi_hozzajarulas']/100);
        $ret['ado_szakkepzesi_hozzajarulas'] = round($ret['ado_szakkepzesi_hozzajarulas']);

        $ret['ado_kisvallalati'] = ($brutto_ber - $szocho_es_kiva_kedvezmeny_alap) * ($settings['ado_kisvallalati']/100);
        $ret['ado_kisvallalati'] = round($ret['ado_kisvallalati']);

        $ret['berkoltseg_nem_KIVA'] = $brutto_ber + $ret['ado_szocialis_hozzajarulas'] + $ret['ado_szakkepzesi_hozzajarulas'];
        $ret['berkoltseg_KIVA'] = $brutto_ber + $ret['ado_kisvallalati'];

        $nav_osszes_ado = 0;

        if ($data['ceg_kisvallalati_ado_alany'] == 'Igen') {
          $nav_osszes_ado = $ret['berkoltseg_KIVA'] - $netto_ber;
        } else {
          $nav_osszes_ado = $ret['berkoltseg_nem_KIVA'] - $netto_ber;
        }

        $ret['nav_osszes_ado'] = $nav_osszes_ado;

        $values['szocho_es_kiva_kedvezmeny_alap'] = $szocho_es_kiva_kedvezmeny_alap;
        $values['szokho_kedvezmeny_alap'] = $szokho_kedvezmeny_alap;
        $values['kiva_adoalany'] = $data['ceg_kisvallalati_ado_alany'];

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'netto_ber':
        $ret = array(
          'brutto_ber' => 0
        );
        $settings = $this->loadSettings( $calc );

        $brutto_ber = $data['brutto_ber'];

        $ret['brutto_ber'] = $brutto_ber;

        $csaladi_adokedvezmeny_osszege = 0;
        if ($data['csaladkedvezmenyre_jogosult'] == 'Igen') {
          $csaladi_adokedvezmeny_osszege = $this->csaladiAdokedvezmenyOsszege( (int)$data['csalad_eltartott_gyermek'], (int)$data['csalad_eltartott_gyermek_kedvezmenyezett'], $settings );
        }

        $ervenyesitheto_jarulekkedvezmeny = 0;
        $friss_hazasok_kedvezmeny = 0;
        $szemelyi_kedvezmeny = 0;
        $ervenyesitheto_termeszetbeni_kedvezmeny = 0;
        $ervenyesitheto_penzbeni_kedvezmeny = 0;

        if ($data['frisshazas_jogosult'] == 'Igen') {
          $friss_hazasok_kedvezmeny = $settings['adokedvezmeny_frisshazasok'];
        }

        if ($data['szemelyikedvezmeny_jogosult'] == 'Igen') {
          $szemelyi_kedvezmeny = $settings['adokedvezmeny_szemelyi'];
        }

        $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege+$friss_hazasok_kedvezmeny-$brutto_ber;
        $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;

        $ervenyesitheto_jarulekkedvezmeny = $csaladi_adokedvezmeny_maradekalap * 0.15;
        $ervenyesitheto_jarulekkedvezmeny = ($ervenyesitheto_jarulekkedvezmeny < 0) ? 0 : $ervenyesitheto_jarulekkedvezmeny;

        $ervenyesitheto_termeszetbeni_kedvezmeny = $ervenyesitheto_jarulekkedvezmeny - ($brutto_ber * ($settings['ado_termeszetegeszseg']/100));
        $ervenyesitheto_termeszetbeni_kedvezmeny = ($ervenyesitheto_termeszetbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_termeszetbeni_kedvezmeny;

        $ervenyesitheto_penzbeni_kedvezmeny = $ervenyesitheto_termeszetbeni_kedvezmeny - ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100));
        $ervenyesitheto_penzbeni_kedvezmeny = ($ervenyesitheto_penzbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_penzbeni_kedvezmeny;

        $ret['ado_szja'] = (($brutto_ber-$friss_hazasok_kedvezmeny-$csaladi_adokedvezmeny_osszege) * ($settings['ado_szja']/100)) - $szemelyi_kedvezmeny;
        $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
        $ret['ado_szja'] = round($ret['ado_szja']);

        $ret['ado_termeszetegeszseg'] = ($brutto_ber * ($settings['ado_termeszetegeszseg']/100)) - $ervenyesitheto_jarulekkedvezmeny;
        $ret['ado_termeszetegeszseg'] = ($ret['ado_termeszetegeszseg'] < 0) ? 0 : $ret['ado_termeszetegeszseg'];
        $ret['ado_termeszetegeszseg'] = round($ret['ado_termeszetegeszseg']);

        $ret['ado_penzbeli_egeszseg'] = ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100)) - $ervenyesitheto_termeszetbeni_kedvezmeny;
        $ret['ado_penzbeli_egeszseg'] = ($ret['ado_penzbeli_egeszseg'] < 0) ? 0 : $ret['ado_penzbeli_egeszseg'];
        $ret['ado_penzbeli_egeszseg'] = round($ret['ado_penzbeli_egeszseg']);

        $ret['ado_nyugdij'] = ($brutto_ber * ($settings['ado_nyugdij']/100)) - $ervenyesitheto_penzbeni_kedvezmeny;
        $ret['ado_nyugdij'] = ($ret['ado_nyugdij'] < 0) ? 0 : $ret['ado_nyugdij'];
        $ret['ado_nyugdij'] = round($ret['ado_nyugdij']);

        $ret['ado_munkaerppiac'] = $brutto_ber * ($settings['ado_munkaerppiac']/100);
        $ret['ado_munkaerppiac'] = round($ret['ado_munkaerppiac']);

        $sum_minusbrutto = $ret['ado_szja'] + $ret['ado_termeszetegeszseg'] + $ret['ado_penzbeli_egeszseg'] + $ret['ado_nyugdij'] + $ret['ado_munkaerppiac'];

        $ret['sum_minusbrutto'] = $sum_minusbrutto;
        $netto_ber = $brutto_ber-$sum_minusbrutto;
        $ret['netto_ber'] = $netto_ber;

        $values['csaladi_adokedvezmeny_osszege'] = $csaladi_adokedvezmeny_osszege;
        $values['frisshazas_jogosult'] = $friss_hazasok_kedvezmeny;
        $values['szemelyikedvezmeny_jogosult'] = $szemelyi_kedvezmeny;
        $values['csaladi_adokedvezmeny_maradekalap'] = $csaladi_adokedvezmeny_maradekalap;
        $values['ervenyesitheto_jarulekkedvezmeny'] = $ervenyesitheto_jarulekkedvezmeny;
        $values['ervenyesitheto_termeszetbeni_kedvezmeny'] = $ervenyesitheto_termeszetbeni_kedvezmeny;
        $values['ervenyesitheto_penzbeni_kedvezmeny'] = $ervenyesitheto_penzbeni_kedvezmeny;

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'belepo_szabadsag':
        $ret = array(
          'szabadsag_eves' => 0,
          'szabadsag_idoaranyos' => 0,
          'betegszabadsag_eves' => 0,
          'betegszabadsag_idoaranyos' => 0
        );
        $values = array();
        $settings = $this->loadSettings( $calc );
        $ret['betegszabadsag_eves'] = $settings['betegszabadsag'];
        $targyev = (int)date('Y');
        $ev_elso_napja = date('Y').'-01-01';
        $ev_utolso_napja = date('Y-m-d', strtotime('last day of december this year'));
        $szamitas_kezdete = $ev_elso_napja;

        if ($data['iden_kezdett_dolgozni'] == 'Igen') {
          $szamitas_kezdete = date('Y-m-d', strtotime($data['belepes_datuma']));
        }

        // pre calc
        $munkavallalo_kora = $targyev - $data['szuletesi_ev'];
        $ev_vegeig_hatralevo_napok = round((strtotime($ev_utolso_napja) - strtotime($szamitas_kezdete)) / (60 * 60 * 24))+1;
        $ev_naptari_napok = round((strtotime($ev_utolso_napja) - strtotime($ev_elso_napja)) / (60 * 60 * 24))+1;
        $kor_potszabi = $this->potszabadasgKorSzerint($munkavallalo_kora);
        $gyerek16fiatalabb_potszabi = $this->potszabadasg16evfiatalabbGyerekSzerint((int)$data['gyerek16ev_fiatalabb']);

        // potszabi_ha16evnelfiatalabbgyereketnevel
        // megvaltozott_munkakepessegu

        $szabadsag_eves = (int)$data['athozott_szabadsagok'] + $settings['alapszabadsag'] + $kor_potszabi + $gyerek16fiatalabb_potszabi;

        if ($data['gyerek16ev_fiatalabb_fogyatekos'] == 'Igen') {
          $szabadsag_eves += $settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
          $values['ha16evnelfiatalabbgyereketnevel_potszabi'] = $settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
        }

        if ($data['megvaltozott_munkakepessegu'] == 'Igen') {
          $szabadsag_eves += $settings['potszabi_megvaltozott_munkakepessegu'];
          $values['megvaltozott_munkakepessegu_potszabi'] = $settings['potszabi_megvaltozott_munkakepessegu'];
        }

        $ret['szabadsag_eves'] = $szabadsag_eves;
        $szabadsag_idoaranyos = $szabadsag_eves/$ev_naptari_napok*$ev_vegeig_hatralevo_napok;
        $ret['szabadsag_idoaranyos'] = round($szabadsag_idoaranyos);

        $betegszabadsag_eves = $settings['betegszabadsag'];
        $betegszabadsag_idoaranyos = $betegszabadsag_eves/$ev_naptari_napok*$ev_vegeig_hatralevo_napok;

        $ret['betegszabadsag_eves'] = $betegszabadsag_eves;
        $ret['betegszabadsag_idoaranyos'] = round($betegszabadsag_idoaranyos);


        $values['targyev'] = $targyev;
        $values['szamitas_kezdete'] = $szamitas_kezdete;
        $values['ev_utolso_napja'] = $ev_utolso_napja;
        $values['munkavallalo_kora'] = $munkavallalo_kora;
        $values['ev_vegeig_hatralevo_napok'] = $ev_vegeig_hatralevo_napok;
        $values['ev_naptari_napok'] = $ev_naptari_napok;
        $values['kor_potszabi'] = $kor_potszabi;
        $values['gyerek16fiatalabb_potszabi'] = $gyerek16fiatalabb_potszabi;

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'cegauto_ado':
        return $this->calcCegautoAdo( $data['emission'], (float)$data['kw'] );
      break;

      case 'ingatlan_ertekesites':
        $ret = array();
        $settings = $this->loadSettings( $calc );
        $ret['settings'] = $settings;

        $bevetel = (float)$data['atruhazasbol_bevetel'];
        // C8+C9+(C10-C11)+C12
        $koltseg = $data['megszerzes_osszeg'] + $data['megszerzes_egyeb_kiadas'] + ($data['erteknovelo_beruhazasok'] - $data['erteknovelo_beruhazasok_allammegovas']) + $data['atruhazas_koltsegei'];
        // (C14-C15)*FKERES(C5;F5:G10;2;IGAZ)
        $szorzo = $this->ingatlan_ertekesites_jovedelem_szorzo((int)$data['szerzes_eve'], (int)$data['atruhazas_eve']);
        $jovedelem = ($bevetel - $koltseg) * $szorzo;
        $fizetendo_szja = 0;

        $fizetendo_szja = $jovedelem * ($settings['ado_szja']/100);
        $fizetendo_szja = ($fizetendo_szja < 0) ? 0 : $fizetendo_szja;
        $fizetendo_szja = round($fizetendo_szja);

        $ret['bevetel'] = $bevetel;
        $ret['koltseg'] = $koltseg;
        $ret['jovedelem'] = $jovedelem;
        $ret['fizetendo_szja'] = $fizetendo_szja;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'osztalekado':
        $ret = array();
        $settings = $this->loadSettings( $calc );
        $ret['settings'] = $settings;
        $szocho_ado_max = $settings['minimalber'] * 24;

        $osszes_jovedelem = 0;
        $fizetendo_szja = 0;
        $fizetendo_szocho = 0;

        $osszes_jovedelem =
        (float)$data['teljes_targyev_brutto_munkaber'] +
        (float)$data['teljes_targyev_brutto_tarasvall_kivet'] +
        (float)$data['targyev_megszerzett_brutto_osztalek'] +
        (float)$data['targyev_vall_kivont_jovedelem'] +
        (float)$data['targyev_ertekpapkolcson_jovedelem'] +
        (float)$data['targyev_arfolyamnyereseg_jovedelem'] +
        (float)$data['egyeb_szja_jovedelem'];

        $fizetendo_szja = (float)$data['brutto_alap'] * ($settings['ado_szja']/100);
        $fizetendo_szja = round($fizetendo_szja);

        if ( $data['osztalek_kifizetes'] == 'Igen' )
        {
          $alapszamitas = min( ((float)$data['brutto_alap'] * ($settings['ado_szocialis_hozzajarulas']/100)), (($szocho_ado_max - $osszes_jovedelem) * ($settings['ado_szocialis_hozzajarulas']/100)) );
          $fizetendo_szocho = $alapszamitas;
          $fizetendo_szocho = round($fizetendo_szocho);
          if ($fizetendo_szocho < 0 ) {
            $fizetendo_szocho = 0;
          }
        }

        $ret['jovedelem'] = $osszes_jovedelem;
        $ret['fizetendo_szja'] = $fizetendo_szja;
        $ret['fizetendo_szocho'] = $fizetendo_szocho;
        $ret['fizetendo'] = $fizetendo_szja +  $fizetendo_szocho;
        $ret['brutto_alap'] = $data['brutto_alap'];
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'cafeteria':
        $ret = array();
        $settings = $this->loadSettings( $calc );
        $ret['settings'] = $settings;

        $jg = $this->getCafeteriaGroupByTitle( $data['juttatas'] );
        $ret['juttatas_group'] = $jg;

        $adoalap_kiegeszites = 0;
        $szja = 0;
        $szocho = 0;
        $szkh = 0;
        $kiva = 0;
        $ado_munkavallalo = 0;
        $ado_mukaltato = 0;

        $adoalap = (float)$data['juttatas_osszege'];

        if ( $jg && in_array($jg['ID'], array(2, 3)) )
        {
          if ( $jg['ID'] == 2) {
            $adoalap_tetel_limits = $this->getCafeteriaItemAdoalapLimit();
            $adoalap_tetel_limit = (float)$adoalap_tetel_limits[$data['juttatas']];

            if ((float)$data['juttatas_osszege'] <= $adoalap_tetel_limit) {
              $adoalap_kiegeszites = 0;
            } else {
              $adoalap_kiegeszites = (float)$data['juttatas_osszege'] - $adoalap_tetel_limit;
              $adoalap_kiegeszites = $adoalap_kiegeszites * ($settings['ado_caf_adoalap_kieg']/100);
              $adoalap_kiegeszites = ($adoalap_kiegeszites < 0) ? 0 : $adoalap_kiegeszites;
              $adoalap_kiegeszites = round($adoalap_kiegeszites);
            }
          }

          if ( $jg['ID'] == 3) {
            $adoalap_kiegeszites = (float)$data['juttatas_osszege'] * ($settings['ado_caf_adoalap_kieg']/100);
            $adoalap_kiegeszites = ($adoalap_kiegeszites < 0) ? 0 : $adoalap_kiegeszites;
            $adoalap_kiegeszites = round($adoalap_kiegeszites);
          }

          $adoalap = (float)$adoalap_kiegeszites;
        }

        // szja
        // group 3, 4
        if ($jg && in_array($jg['ID'], array(3, 4)) ) {
          $szja = $adoalap * ($settings['ado_szja']/100);
          $szja = ($szja < 0) ? 0 : $szja;
          $szja = round($szja);
        }

        // szocho
        if ($data['ceg_kiva'] == 'Nem') {
          $szocho = $adoalap * ($settings['ado_szocialis_hozzajarulas']/100);
          $szocho = ($szocho < 0) ? 0 : $szocho;
          $szocho = round($szocho);
        }

        // Szakképzési
        if ($data['ceg_kiva'] == 'Nem' && $jg && in_array($jg['ID'], array(4))) {
          $szkh = $adoalap * ($settings['ado_szakkepzesi_hozzajarulas']/100);
          $szkh = ($szkh < 0) ? 0 : $szkh;
          $szkh = round($szkh);
        }

        // KIVA
        if ($data['ceg_kiva'] == 'Igen' && $jg && in_array($jg['ID'], array(3, 4))) {
          $kiva = $data['juttatas_osszege'] * ($settings['ado_kisvallalati']/100);
          $kiva = ($kiva < 0) ? 0 : $kiva;
          $kiva = round($kiva);
        }

        // group 4
        if ($jg && in_array($jg['ID'], array(4)))
        {
          // természetbeni egészség
          $termeszet_egeszseg_jarulek = $adoalap * ($settings['ado_termeszetegeszseg']/100);
          $termeszet_egeszseg_jarulek = ($termeszet_egeszseg_jarulek < 0) ? 0 : $termeszet_egeszseg_jarulek;
          $termeszet_egeszseg_jarulek = round($termeszet_egeszseg_jarulek);

          // pénzbeli egészség
          $penzbeli_egeszseg_jarulek = $adoalap * ($settings['ado_penzbeli_egeszseg']/100);
          $penzbeli_egeszseg_jarulek = ($penzbeli_egeszseg_jarulek < 0) ? 0 : $penzbeli_egeszseg_jarulek;
          $penzbeli_egeszseg_jarulek = round($penzbeli_egeszseg_jarulek);

          // Nyugdíjjárulék
          $nyugdij_jarulek = $adoalap * ($settings['ado_nyugdij']/100);
          $nyugdij_jarulek = ($nyugdij_jarulek < 0) ? 0 : $nyugdij_jarulek;
          $nyugdij_jarulek = round($nyugdij_jarulek);

          // munkaerő hozzájárulás
          $munkaeropiac_hozzajarulas = $adoalap * ($settings['ado_munkaerppiac']/100);
          $munkaeropiac_hozzajarulas = ($munkaeropiac_hozzajarulas < 0) ? 0 : $munkaeropiac_hozzajarulas;
          $munkaeropiac_hozzajarulas = round($munkaeropiac_hozzajarulas);

          // összes munkaválllalói teher
          $munkavallalo_osszes_jarulek = $termeszet_egeszseg_jarulek + $penzbeli_egeszseg_jarulek + $nyugdij_jarulek + $munkaeropiac_hozzajarulas + $szja;
          $ado_munkavallalo = $munkavallalo_osszes_jarulek;
        }

        // group 2

        if ($jg && in_array($jg['ID'], array(2)))
        {
          // szja
          if ( $adoalap_kiegeszites > 0 ) {
            $szja = ($adoalap_tetel_limit + $adoalap_kiegeszites) * ($settings['ado_szja']/100);
            $szja = ($szja < 0) ? 0 : $szja;
            $szja = round($szja);
          } else {
            $szja = ($adoalap_tetel_limit) * ($settings['ado_szja']/100);
            $szja = ($szja < 0) ? 0 : $szja;
            $szja = round($szja);
          }

          // szocho
          if ( $data['ceg_kiva'] == 'Nem' && $adoalap_kiegeszites > 0)
          {
            $szocho = ($adoalap_tetel_limit + $adoalap_kiegeszites) * ($settings['ado_szocialis_hozzajarulas']/100);
            $szocho = ($szocho < 0) ? 0 : $szocho;
            $szocho = round($szocho);
          }

          if ( $data['ceg_kiva'] == 'Nem' && $adoalap_kiegeszites == 0 )
          {
            $szocho = ($adoalap_tetel_limit) * ($settings['ado_szocialis_hozzajarulas']/100);
            $szocho = ($szocho < 0) ? 0 : $szocho;
            $szocho = round($szocho);
          }

          // kiva
          if ( $data['ceg_kiva'] == 'Igen' )
          {
            $kiva = (float)$data['juttatas_osszege'] * ($settings['ado_kisvallalati']/100);
            $kiva = ($kiva < 0) ? 0 : $kiva;
            $kiva = round($kiva);
          }
        }

        // Munkáltató adók
        if ($jg && in_array($jg['ID'], array(2, 3))) {
          $ado_munkaltato = $szja + $szocho + $kiva;
        }

        if ($jg && in_array($jg['ID'], array(4))) {
          $ado_munkaltato = $szocho + $szkh + $kiva;
        }

        $ret['adoalap_kiegeszites'] = $adoalap_kiegeszites;
        $ret['szja'] = $szja;
        $ret['szocho'] = $szocho;
        $ret['szkh'] = $szkh;
        $ret['kiva'] = $kiva;

        $ret['termeszet_egeszseg_jarulek'] = $termeszet_egeszseg_jarulek;
        $ret['penzbeli_egeszseg_jarulek'] = $penzbeli_egeszseg_jarulek;
        $ret['nyugdij_jarulek'] = $nyugdij_jarulek;
        $ret['munkaeropiac_hozzajarulas'] = $munkaeropiac_hozzajarulas;
        $ret['munkavallalo_osszes_jarulek'] = $munkavallalo_osszes_jarulek;

        $ret['ado_munkavallalo'] = $ado_munkavallalo;
        $ret['ado_munkaltato'] = $ado_munkaltato;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'anyak_szabadsaga':
        $ret = array(
          'szules_eveben_szabadsag' => 0,
          'szulesig_idoaranyos_szabadsag' => 0,
          'szules_eveben_igenybe_vett_szabadsag' => 0,
          'le_nem_toltott_szabadsag' => 0,


          'csed_idejere_jaro_szabadsag' => 0,
          'csed_szules_eveben_igenybe_vett_szabadsag' => 0,
          'csed_le_nem_toltott_szabadsag' => 0,

          'gyed_idejere_jaro_szabadsag' => 0,
          'gyed_szules_eveben_igenybe_vett_szabadsag' => 0,
          'gyed_le_nem_toltott_szabadsag' => 0,

          'osszes_szabadsag' => 0,
        );

        $values = array();
        $settings = $this->loadSettings( $calc );

        $targyev = (int)date('Y');
        $ev_elso_napja = date('Y').'-01-01';
        $ev_utolso_napja = date('Y-m-d', strtotime('last day of december this year'));
        $szules_eve = date('Y', strtotime($data['szules_ideje']));
        $szules_eveben_munkavallalo_eletkora = $szules_eve - (int)$data['szuletesi_ev'];
        $kor_potszabi = $this->potszabadasgKorSzerint($szules_eveben_munkavallalo_eletkora);
        $szulelotti_igyenbevett = ($data['szul_elott_igenybevett_potszabadsag_gyermek'] == '3 vagy több') ? 3  : (int)$data['szul_elott_igenybevett_potszabadsag_gyermek'];
        $gyerek_potszabi = $this->potszabadasg16evfiatalabbGyerekSzerint((int)$data['szul_elott_igenybevett_potszabadsag_gyermek']);

        $alap_szules_eveben_jaro_szabadsag = (int)$settings['alapszabadsag'] + $kor_potszabi + $gyerek_potszabi;

        $day = new DateTime($data['csed_kezdete']);
        $day_munkaviszony_kezdet = new DateTime($data['munkaviszony_kezdete']);
        $mv_day_str = strtotime($day_munkaviszony_kezdet->format('Y-m-d'));
        $csed_day_str = strtotime($day->format('Y').'-01-01');

        // alap munkaviszony kezdete
        $calc_ts = $mv_day_str;

        // ha a csed évi első napja nagyobb a mv kezdetétől
        if ( $csed_day_str > $calc_ts ) {
          $calc_ts = $csed_day_str;
        }

        if( $mv_day_str > $csed_day_str )
        {
          $szulesig_eltelt_napok_szama = (float)( (strtotime($day->format('Y-m-d')) - $calc_ts) / (60*60*24) );          
        } else {
          $szulesig_eltelt_napok_szama = (float)( (strtotime($day->format('Y-m-d')) - $calc_ts) / (60*60*24) );
        }

        if ($data['gyerek16ev_fiatalabb_fogyatekos'] == 'Igen') {
          $alap_szules_eveben_jaro_szabadsag += (float)$settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
        }

        $szulesig_idoaranyos_szabadsag = round($alap_szules_eveben_jaro_szabadsag / 365 * $szulesig_eltelt_napok_szama);
        $szules_eveben_igenybe_vett_szabadsag = (int)$data['szulev_igenybevett_szabadsag'];
        $le_nem_toltott_szabadsag = $szulesig_idoaranyos_szabadsag - $szules_eveben_igenybe_vett_szabadsag;

        $csed_kezdete = $data['csed_kezdete'];
        $minucseddays = 168-1;
        $csed_vege_datum = new DateTime($csed_kezdete); $csed_vege_datum->modify('+ '.$minucseddays.'day');
        $csed_vege = $csed_vege_datum->format('Y-m-d');

        $gyedgyes_kezdete = $data['gyedgyes_kezdete'];
        $minugyedgyesmonth = 6;
        $gyedgyes_vege_datum = new DateTime($gyedgyes_kezdete); $gyedgyes_vege_datum->modify('+ '.$minugyedgyesmonth.' month'); $gyedgyes_vege_datum->modify('-1 day');
        $gyedgyes_vege = $gyedgyes_vege_datum->format('Y-m-d');

        // Helpers
        // CSED
        $help_csed_kezdet = $csed_kezdete;
        $help_csed_vegzet = $csed_vege;
        $help_csed_eletkor1 = date('Y', strtotime($csed_kezdete)) - (int)$data['szuletesi_ev'];
        $help_csed_eletkor2 = date('Y', strtotime($help_csed_vegzet)) - (int)$data['szuletesi_ev'];
        $help_csed_potszabi1 = (int)$this->potszabadasgKorSzerint( (int)$help_csed_eletkor1 );
        $help_csed_potszabi2 = (int)$this->potszabadasgKorSzerint( (int)$help_csed_eletkor2 );

        if ( date('Y', strtotime($csed_kezdete)) == date('Y', strtotime($csed_vege)) ) {
          $vegzetplusz1 = new DateTime($help_csed_vegzet); $vegzetplusz1->modify('+1 day');
          $help_csed_potszabi = round( $help_csed_potszabi1 / 365 * ((strtotime($vegzetplusz1->format('Y-m-d')) - strtotime($help_csed_kezdet))/(60*60*24)));
        }
        else
        {
          // KEREKÍTÉS( G33/365 * (DÁTUM(ÉV(C33);12;31) + 1 - C33); 0 ) + KEREKÍTÉS( H33/365 * (D33 + 1 - DÁTUM(ÉV(D33); 1; 1)); 0 )
          $veg_s1 = date('Y', strtotime($csed_kezdete)).'-12-31';
          $veg_s1 = new DateTime($veg_s1); $veg_s1->modify('+1 day');
          $veg_s1 = $veg_s1->format('Y-m-d');

          $veg_s2 = date('Y', strtotime($csed_vege)).'-01-01';
          $veg_s21 = new DateTime($csed_vege); $veg_s21->modify('+1 day');
          $veg_s21 = $veg_s21->format('Y-m-d');

          $help_csed_potszabi =
          round( $help_csed_potszabi1 / 365 * ( (strtotime($veg_s1) - strtotime($csed_kezdete)) /(60*60*24) )) +
          round( $help_csed_potszabi2 / 365 * ( (strtotime($veg_s21) - (strtotime($veg_s2))) / (60*60*24) ));
        }

        $values['helper']['help_csed_kezdet'] = $help_csed_kezdet;
        $values['helper']['help_csed_vegzet'] = $help_csed_vegzet;
        $values['helper']['help_csed_eletkor1'] = $help_csed_eletkor1;
        $values['helper']['help_csed_eletkor2'] = $help_csed_eletkor2;
        $values['helper']['help_csed_potszabi1'] = $help_csed_potszabi1;
        $values['helper']['help_csed_potszabi2'] = $help_csed_potszabi2;
        $values['helper']['csed_potszabi'] = $help_csed_potszabi;

        // GYED
        $help_gyedgyes_kezdet = $gyedgyes_kezdete;
        $help_gyedgyes_vegzet = $gyedgyes_vege;
        $help_gyedgyes_eletkor1 = date('Y', strtotime($gyedgyes_kezdete)) - (int)$data['szuletesi_ev'];
        $help_gyedgyes_eletkor2 = date('Y', strtotime($help_gyedgyes_vegzet)) - (int)$data['szuletesi_ev'];
        $help_gyedgyes_potszabi1 = (int)$this->potszabadasgKorSzerint( (int)$help_gyedgyes_eletkor1 );
        $help_gyedgyes_potszabi2 = (int)$this->potszabadasgKorSzerint( (int)$help_gyedgyes_eletkor2 );

        if ( date('Y', strtotime($gyedgyes_kezdete)) == date('Y', strtotime($gyedgyes_vege)) ) {
          $vegzetplusz1 = new DateTime($help_gyedgyes_vegzet); $vegzetplusz1->modify('+1 day');
          $help_gyedgyes_potszabi = round( $help_gyedgyes_potszabi1 / 365 * ((strtotime($vegzetplusz1->format('Y-m-d')) - strtotime($help_gyedgyes_kezdet))/(60*60*24)));
        }
        else
        {
          $veg_s1 = date('Y', strtotime($gyedgyes_kezdete)).'-12-31';
          $veg_s1 = new DateTime($veg_s1); $veg_s1->modify('+1 day');
          $veg_s1 = $veg_s1->format('Y-m-d');

          $veg_s2 = date('Y', strtotime($gyedgyes_vege)).'-01-01';
          $veg_s21 = new DateTime($gyedgyes_vege); $veg_s21->modify('+1 day');
          $veg_s21 = $veg_s21->format('Y-m-d');

          $help_gyedgyes_potszabi =
          round( $help_gyedgyes_potszabi1 / 365 * ( (strtotime($veg_s1) - strtotime($gyedgyes_kezdete)) /(60*60*24) )) +
          round( $help_gyedgyes_potszabi2 / 365 * ( (strtotime($veg_s21) - (strtotime($veg_s2))) / (60*60*24) ));
        }

        $values['helper']['help_gyedgyes_kezdet'] = $help_gyedgyes_kezdet;
        $values['helper']['help_gyedgyes_vegzet'] = $help_gyedgyes_vegzet;
        $values['helper']['help_gyedgyes_eletkor1'] = $help_gyedgyes_eletkor1;
        $values['helper']['help_gyedgyes_eletkor2'] = $help_gyedgyes_eletkor2;
        $values['helper']['help_gyedgyes_potszabi1'] = $help_gyedgyes_potszabi1;
        $values['helper']['help_gyedgyes_potszabi2'] = $help_gyedgyes_potszabi2;
        $values['helper']['gyedgyes_potszabi'] = $help_gyedgyes_potszabi;

        // csed
        $csed_idejere_jaro_szabadsag = (int)$settings['alapszabadsag'] + $this->potszabadasg16evfiatalabbGyerekSzerint((int)$data['szul_elott_igenybevett_potszabadsag_gyermek']);
        if ($data['gyerek16ev_fiatalabb_fogyatekos'] == 'Igen') {
          $csed_idejere_jaro_szabadsag += (float)$settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
        }
        $csed_idejere_jaro_szabadsag_vt1 = new DateTime($csed_vege); $csed_idejere_jaro_szabadsag_vt1->modify('+1 day');
        $csed_idejere_jaro_szabadsag_vt1 = $csed_idejere_jaro_szabadsag_vt1->format('Y-m-d');
        $csed_idejere_jaro_szabadsag = $csed_idejere_jaro_szabadsag / 365 * ((strtotime($csed_idejere_jaro_szabadsag_vt1) - strtotime($csed_kezdete))/(60*60*24));
        $csed_idejere_jaro_szabadsag = round($csed_idejere_jaro_szabadsag);
        $csed_idejere_jaro_szabadsag += (int)$help_csed_potszabi;

        $csed_szules_eveben_igenybe_vett_szabadsag = min($le_nem_toltott_szabadsag, 0);

        $csed_le_nem_toltott_szabadsag = $csed_idejere_jaro_szabadsag + $csed_szules_eveben_igenybe_vett_szabadsag;

        // gyedgyes
        $gyedgyes_idejere_jaro_szabadsag = (int)$settings['alapszabadsag'] + $this->potszabadasg16evfiatalabbGyerekSzerint((int)$data['szul_elott_igenybevett_potszabadsag_gyermek']);
        if ($data['gyerek16ev_fiatalabb_fogyatekos'] == 'Igen') {
          $gyedgyes_idejere_jaro_szabadsag += (float)$settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
        }
        $gyedgyes_idejere_jaro_szabadsag_vt1 = new DateTime($gyedgyes_vege); $gyedgyes_idejere_jaro_szabadsag_vt1->modify('+1 day');
        $gyedgyes_idejere_jaro_szabadsag_vt1 = $gyedgyes_idejere_jaro_szabadsag_vt1->format('Y-m-d');
        $gyedgyes_idejere_jaro_szabadsag = $gyedgyes_idejere_jaro_szabadsag / 365 * ((strtotime($gyedgyes_idejere_jaro_szabadsag_vt1) - strtotime($gyedgyes_kezdete))/(60*60*24));
        $gyedgyes_idejere_jaro_szabadsag = round($gyedgyes_idejere_jaro_szabadsag);
        $gyedgyes_idejere_jaro_szabadsag += (int)$help_gyedgyes_potszabi;

        $gyedgyes_szules_eveben_igenybe_vett_szabadsag = min($csed_le_nem_toltott_szabadsag, 0);

        $gyedgyes_le_nem_toltott_szabadsag = $gyedgyes_idejere_jaro_szabadsag + $gyedgyes_szules_eveben_igenybe_vett_szabadsag;

        // összes
        $osszes_szabadsag = $le_nem_toltott_szabadsag + $csed_idejere_jaro_szabadsag + $gyedgyes_idejere_jaro_szabadsag;

        $ret['szules_eveben_szabadsag'] = $alap_szules_eveben_jaro_szabadsag;
        $ret['szulesig_idoaranyos_szabadsag'] = $szulesig_idoaranyos_szabadsag;
        $ret['szules_eveben_igenybe_vett_szabadsag'] = $szules_eveben_igenybe_vett_szabadsag;
        $ret['le_nem_toltott_szabadsag'] = $le_nem_toltott_szabadsag;
        $ret['csed_idejere_jaro_szabadsag'] = $csed_idejere_jaro_szabadsag;
        $ret['csed_szules_eveben_igenybe_vett_szabadsag'] = $csed_szules_eveben_igenybe_vett_szabadsag;
        $ret['csed_le_nem_toltott_szabadsag'] = $csed_le_nem_toltott_szabadsag;
        $ret['gyedgyes_idejere_jaro_szabadsag'] = $gyedgyes_idejere_jaro_szabadsag;
        $ret['gyedgyes_szules_eveben_igenybe_vett_szabadsag'] = $gyedgyes_szules_eveben_igenybe_vett_szabadsag;
        $ret['gyedgyes_le_nem_toltott_szabadsag'] = $gyedgyes_le_nem_toltott_szabadsag;
        $ret['osszes_szabadsag'] = $osszes_szabadsag;

        $values['szules_eve'] = $szules_eve;
        $values['szules_eveben_munkavallalo_eletkora'] = $szules_eveben_munkavallalo_eletkora;
        $values['gyerek_potszabi'] = $gyerek_potszabi;
        $values['gyerek_fogyatek_potszabi'] = $gyerek_fogyatek_potszabi;
        $values['szulesig_eltelt_napok_szama'] = $szulesig_eltelt_napok_szama;
        $values['csed_vege'] = $csed_vege;
        $values['gyedgyes_vege'] = $gyedgyes_vege;

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
    }

    return false;
  }
}

/***********************************************
* 2020/1-es jogszabályoknak megfelelő kalkulátor *
************************************************/
class CalculatorV2020_1 extends CalculatorBase implements CalculatorVersion
{
  public function __construct( $version = false )
  {
    parent::__construct( $version );
    return $this;
  }

  public function calcBerAdoLevonasok( $brutto_ber, $data, $settings )
  {
    $ret = array();

    $csaladi_adokedvezmeny_osszege = 0;
    if ($data['csaladkedvezmenyre_jogosult'] == 'Igen') {
      $csaladi_adokedvezmeny_osszege = $this->csaladiAdokedvezmenyOsszege( (int)$data['csalad_eltartott_gyermek'], (int)$data['csalad_eltartott_gyermek_kedvezmenyezett'], $settings );
    }

    $anyak_gyerek4vagytobb = false;
    if ($data['anyak_4vagytobbgyermek'] == 'Igen') {
      $anyak_gyerek4vagytobb = true;
    }

    $ervenyesitheto_jarulekkedvezmeny = 0;
    $friss_hazasok_kedvezmeny = 0;
    $szemelyi_kedvezmeny = 0;
    $ervenyesitheto_termeszetbeni_kedvezmeny = 0;
    $ervenyesitheto_penzbeni_kedvezmeny = 0;

    if ($data['frisshazas_jogosult'] == 'Igen') {
      $friss_hazasok_kedvezmeny = $settings['adokedvezmeny_frisshazasok'];
    }

    if ($data['szemelyikedvezmeny_jogosult'] == 'Igen') {
      $szemelyi_kedvezmeny = $settings['adokedvezmeny_szemelyi'];
    }

    if ( !$anyak_gyerek4vagytobb )
    {
      $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege+$friss_hazasok_kedvezmeny-$brutto_ber;
      $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
    } else {
      $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege;
      $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
    }

    $ervenyesitheto_jarulekkedvezmeny = $csaladi_adokedvezmeny_maradekalap * 0.15;
    $ervenyesitheto_jarulekkedvezmeny = ($ervenyesitheto_jarulekkedvezmeny < 0) ? 0 : $ervenyesitheto_jarulekkedvezmeny;

    $ervenyesitheto_termeszetbeni_kedvezmeny = $ervenyesitheto_jarulekkedvezmeny - ($brutto_ber * ($settings['ado_termeszetegeszseg']/100));
    $ervenyesitheto_termeszetbeni_kedvezmeny = ($ervenyesitheto_termeszetbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_termeszetbeni_kedvezmeny;

    $ervenyesitheto_penzbeni_kedvezmeny = $ervenyesitheto_termeszetbeni_kedvezmeny - ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100));
    $ervenyesitheto_penzbeni_kedvezmeny = ($ervenyesitheto_penzbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_penzbeni_kedvezmeny;

    if ( !$anyak_gyerek4vagytobb ) {
      $ret['ado_szja'] = (($brutto_ber-$friss_hazasok_kedvezmeny-$csaladi_adokedvezmeny_osszege) * ($settings['ado_szja']/100)) - $szemelyi_kedvezmeny;
      $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
      $ret['ado_szja'] = round($ret['ado_szja']);
    } else {
      $ret['ado_szja'] = 0;
      $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
      $ret['ado_szja'] = round($ret['ado_szja']);
    }

    $ret['ado_termeszetegeszseg'] = ($brutto_ber * ($settings['ado_termeszetegeszseg']/100)) - $ervenyesitheto_jarulekkedvezmeny;
    $ret['ado_termeszetegeszseg'] = ($ret['ado_termeszetegeszseg'] < 0) ? 0 : $ret['ado_termeszetegeszseg'];
    $ret['ado_termeszetegeszseg'] = round($ret['ado_termeszetegeszseg']);

    $ret['ado_penzbeli_egeszseg'] = ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100)) - $ervenyesitheto_termeszetbeni_kedvezmeny;
    $ret['ado_penzbeli_egeszseg'] = ($ret['ado_penzbeli_egeszseg'] < 0) ? 0 : $ret['ado_penzbeli_egeszseg'];
    $ret['ado_penzbeli_egeszseg'] = round($ret['ado_penzbeli_egeszseg']);

    $ret['ado_nyugdij'] = ($brutto_ber * ($settings['ado_nyugdij']/100)) - $ervenyesitheto_penzbeni_kedvezmeny;
    $ret['ado_nyugdij'] = ($ret['ado_nyugdij'] < 0) ? 0 : $ret['ado_nyugdij'];
    $ret['ado_nyugdij'] = round($ret['ado_nyugdij']);

    $ret['ado_munkaerppiac'] = $brutto_ber * ($settings['ado_munkaerppiac']/100);
    $ret['ado_munkaerppiac'] = round($ret['ado_munkaerppiac']);

    $sum_minusbrutto = $ret['ado_szja'] + $ret['ado_termeszetegeszseg'] + $ret['ado_penzbeli_egeszseg'] + $ret['ado_nyugdij'] + $ret['ado_munkaerppiac'];

    $sum_minusbrutto = round($sum_minusbrutto);

    return array(
      'levonas' => $sum_minusbrutto,
      'params' => $ret
    );
  }

  public function findBruttoFromNetto( $netto, $data, $settings )
  {
    $brutto = $netto * 1.5;
    $allcalc = 0;
    $running = true;
    $step = 0;
    $szamolt_netto = 0;
    $xn = 1000;
    $calced = array();

    $steps = array();
    while( $running )
    {
      $step++;

      $calced = $this->calcBerAdoLevonasok($brutto, $data, $settings);
      $levon = (float)$calced['levonas'];
      $szamolt_netto = $brutto - $levon;
      $tol = ($szamolt_netto / $netto) * 100;

      $steps[] = array(
        'br' => round($brutto),
        'le' => $levon,
        'net' => round($szamolt_netto),
        'tol' => $tol
      );

      if ($step >= 1000) {
        $running = false;
      }

      $fdiff = ($netto - $szamolt_netto);
      if ( abs($fdiff) < 1000 ) {
        $xn = 10;
      }
      if ( abs($fdiff) < 10 ) {
        $xn = 1;
      }

      if( $szamolt_netto > $netto ){
        $brutto -= $xn;
      }

      if ( $szamolt_netto < $netto ) {
        $brutto += $xn;
      }
      if ( round($szamolt_netto) == $netto ) {
        $running = false;
      }
    }

    unset($netto);unset($data);unset($settings);

    return array(
      'brutto' => $brutto,
      'values' => $calced
    );
  }

  public function calculate( $calc, $data )
  {
    switch ( $calc )
    {
      case 'brutto_ber':
        $ret = array(
          'netto_ber' => 0
        );
        $settings = $this->loadSettings( $calc );

        $netto = $data['netto_ber'];
        $ret['netto_ber'] = $netto;

        $find = $this->findBruttoFromNetto( $netto, $data, $settings, $values );
        $sum_minusbrutto = (int)$find['brutto'];
        $values['find'] = $find;

        $ret['sum_minusbrutto'] = $find['values']['levonas'];
        $ret['brutto_ber'] = $netto_ber + $sum_minusbrutto;

        $ret['ado_szja'] = $find['values']['params']['ado_szja'];
        $ret['ado_termeszetegeszseg'] = $find['values']['params']['ado_termeszetegeszseg'];
        $ret['ado_penzbeli_egeszseg'] = $find['values']['params']['ado_penzbeli_egeszseg'];
        $ret['ado_nyugdij'] = $find['values']['params']['ado_nyugdij'];
        $ret['ado_munkaerppiac'] = $find['values']['params']['ado_munkaerppiac'];

        // Nettó bér alap vége

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'teljes_berkoltseg':
        $ret = array(
          'brutto_ber' => 0
        );
        $settings = $this->loadSettings( $calc );

        $brutto_ber = $data['brutto_ber'];

        $ret['brutto_ber'] = $brutto_ber;

        $csaladi_adokedvezmeny_osszege = 0;
        if ($data['csaladkedvezmenyre_jogosult'] == 'Igen') {
          $csaladi_adokedvezmeny_osszege = $this->csaladiAdokedvezmenyOsszege( (int)$data['csalad_eltartott_gyermek'], (int)$data['csalad_eltartott_gyermek_kedvezmenyezett'], $settings );
        }

        $anyak_gyerek4vagytobb = false;
        if ($data['anyak_4vagytobbgyermek'] == 'Igen') {
          $anyak_gyerek4vagytobb = true;
        }

        $ervenyesitheto_jarulekkedvezmeny = 0;
        $friss_hazasok_kedvezmeny = 0;
        $szemelyi_kedvezmeny = 0;
        $ervenyesitheto_termeszetbeni_kedvezmeny = 0;
        $ervenyesitheto_penzbeni_kedvezmeny = 0;

        if ($data['frisshazas_jogosult'] == 'Igen') {
          $friss_hazasok_kedvezmeny = $settings['adokedvezmeny_frisshazasok'];
        }

        if ($data['szemelyikedvezmeny_jogosult'] == 'Igen') {
          $szemelyi_kedvezmeny = $settings['adokedvezmeny_szemelyi'];
        }

        if ( !$anyak_gyerek4vagytobb )
        {
          $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege+$friss_hazasok_kedvezmeny-$brutto_ber;
          $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
        } else {
          $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege;
          $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
        }

        $ervenyesitheto_jarulekkedvezmeny = $csaladi_adokedvezmeny_maradekalap * 0.15;
        $ervenyesitheto_jarulekkedvezmeny = ($ervenyesitheto_jarulekkedvezmeny < 0) ? 0 : $ervenyesitheto_jarulekkedvezmeny;

        $ervenyesitheto_termeszetbeni_kedvezmeny = $ervenyesitheto_jarulekkedvezmeny - ($brutto_ber * ($settings['ado_termeszetegeszseg']/100));
        $ervenyesitheto_termeszetbeni_kedvezmeny = ($ervenyesitheto_termeszetbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_termeszetbeni_kedvezmeny;

        $ervenyesitheto_penzbeni_kedvezmeny = $ervenyesitheto_termeszetbeni_kedvezmeny - ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100));
        $ervenyesitheto_penzbeni_kedvezmeny = ($ervenyesitheto_penzbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_penzbeni_kedvezmeny;

        if ( !$anyak_gyerek4vagytobb ) {
          $ret['ado_szja'] = (($brutto_ber-$friss_hazasok_kedvezmeny-$csaladi_adokedvezmeny_osszege) * ($settings['ado_szja']/100)) - $szemelyi_kedvezmeny;
          $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
          $ret['ado_szja'] = round($ret['ado_szja']);
        } else {
          $ret['ado_szja'] = 0;
          $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
          $ret['ado_szja'] = round($ret['ado_szja']);
        }

        $ret['ado_termeszetegeszseg'] = ($brutto_ber * ($settings['ado_termeszetegeszseg']/100)) - $ervenyesitheto_jarulekkedvezmeny;
        $ret['ado_termeszetegeszseg'] = ($ret['ado_termeszetegeszseg'] < 0) ? 0 : $ret['ado_termeszetegeszseg'];
        $ret['ado_termeszetegeszseg'] = round($ret['ado_termeszetegeszseg']);

        $ret['ado_penzbeli_egeszseg'] = ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100)) - $ervenyesitheto_termeszetbeni_kedvezmeny;
        $ret['ado_penzbeli_egeszseg'] = ($ret['ado_penzbeli_egeszseg'] < 0) ? 0 : $ret['ado_penzbeli_egeszseg'];
        $ret['ado_penzbeli_egeszseg'] = round($ret['ado_penzbeli_egeszseg']);

        $ret['ado_nyugdij'] = ($brutto_ber * ($settings['ado_nyugdij']/100)) - $ervenyesitheto_penzbeni_kedvezmeny;
        $ret['ado_nyugdij'] = ($ret['ado_nyugdij'] < 0) ? 0 : $ret['ado_nyugdij'];
        $ret['ado_nyugdij'] = round($ret['ado_nyugdij']);

        $ret['ado_munkaerppiac'] = $brutto_ber * ($settings['ado_munkaerppiac']/100);
        $ret['ado_munkaerppiac'] = round($ret['ado_munkaerppiac']);

        $sum_minusbrutto = $ret['ado_szja'] + $ret['ado_termeszetegeszseg'] + $ret['ado_penzbeli_egeszseg'] + $ret['ado_nyugdij'] + $ret['ado_munkaerppiac'];

        $ret['sum_minusbrutto'] = $sum_minusbrutto;
        $netto_ber = $brutto_ber-$sum_minusbrutto;
        $ret['netto_ber'] = $netto_ber;

        $values['csaladi_adokedvezmeny_osszege'] = $csaladi_adokedvezmeny_osszege;
        $values['frisshazas_jogosult'] = $friss_hazasok_kedvezmeny;
        $values['szemelyikedvezmeny_jogosult'] = $szemelyi_kedvezmeny;
        $values['csaladi_adokedvezmeny_maradekalap'] = $csaladi_adokedvezmeny_maradekalap;
        $values['ervenyesitheto_jarulekkedvezmeny'] = $ervenyesitheto_jarulekkedvezmeny;
        $values['ervenyesitheto_termeszetbeni_kedvezmeny'] = $ervenyesitheto_termeszetbeni_kedvezmeny;
        $values['ervenyesitheto_penzbeni_kedvezmeny'] = $ervenyesitheto_penzbeni_kedvezmeny;

        // Nettó bér alap vége

        // Teljes bérköltség számítások
        $mk = $settings['forms']['munkavallalo_kedvezmenyek'];
        $minimalber = $settings['minimalber'];
        $minimalber_ketszeres = $minimalber * 2;
        $szocho_es_kiva_kedvezmeny_alap = 0;
        $szokho_kedvezmeny_alap = 0;

        $sel_mk = (int)$data['munkavallalo_kedvezmeny'];
        $mk_obj = $mk[$sel_mk];

        // szocho kiva alapszámítás
        $kedvezmeny_mertek = $mk_obj['calc']['szochokiva']['kedvezmeny_mertek'];
        $kedvezmeny_max = $mk_obj['calc']['szochokiva']['kedvezmeny_max'];
        $kedvezmeny_max = (float)str_replace(array('{minimalber}', '{minimalber_ketszeres}'), array($minimalber, $minimalber_ketszeres), $kedvezmeny_max);

        $values['szochokiva_kedvezmeny_mertek'] = $kedvezmeny_mertek;
        $values['szochokiva_kedvezmeny_max'] = $kedvezmeny_max;

        if ( $kedvezmeny_mertek > 0 ) {
          if ($kedvezmeny_max > 0) {
            $szocho_es_kiva_kedvezmeny_alap_row = array();
            $szocho_es_kiva_kedvezmeny_alap_row[] = $brutto_ber * ($kedvezmeny_mertek/100);
            $szocho_es_kiva_kedvezmeny_alap_row[] = $kedvezmeny_max * ($kedvezmeny_mertek/100);
            $szocho_es_kiva_kedvezmeny_alap = min($szocho_es_kiva_kedvezmeny_alap_row);
          } else {
            $szocho_es_kiva_kedvezmeny_alap = $brutto_ber * ($kedvezmeny_mertek/100);
          }
        }

        // szokho alapszámítás
        $kedvezmeny_mertek = $mk_obj['calc']['szokho']['kedvezmeny_mertek'];
        $kedvezmeny_max = $mk_obj['calc']['szokho']['kedvezmeny_max'];
        $kedvezmeny_max = (float)str_replace(array('{minimalber}', '{minimalber_ketszeres}'), array($minimalber, $minimalber_ketszeres), $kedvezmeny_max);

        $values['szokho_kedvezmeny_mertek'] = $kedvezmeny_mertek;
        $values['szokho_kedvezmeny_max'] = $kedvezmeny_max;

        if ( $kedvezmeny_mertek > 0 ) {
          if ($kedvezmeny_max > 0) {
            $szokho_kedvezmeny_alap_row = array();
            $szokho_kedvezmeny_alap_row[] = $brutto_ber * ($kedvezmeny_mertek/100);
            $szokho_kedvezmeny_alap_row[] = $kedvezmeny_max * ($kedvezmeny_mertek/100);
            $szokho_kedvezmeny_alap = min($szokho_kedvezmeny_alap_row);
          } else {
            $szokho_kedvezmeny_alap = $brutto_ber * ($kedvezmeny_mertek/100);
          }
        }

        $ret['ado_szocialis_hozzajarulas'] = ($brutto_ber - $szocho_es_kiva_kedvezmeny_alap) * ($settings['ado_szocialis_hozzajarulas']/100);
        $ret['ado_szocialis_hozzajarulas'] = round($ret['ado_szocialis_hozzajarulas']);

        $ret['ado_szakkepzesi_hozzajarulas'] = ($brutto_ber - $szokho_kedvezmeny_alap) * ($settings['ado_szakkepzesi_hozzajarulas']/100);
        $ret['ado_szakkepzesi_hozzajarulas'] = round($ret['ado_szakkepzesi_hozzajarulas']);

        $ret['ado_kisvallalati'] = ($brutto_ber - $szocho_es_kiva_kedvezmeny_alap) * ($settings['ado_kisvallalati']/100);
        $ret['ado_kisvallalati'] = round($ret['ado_kisvallalati']);

        $ret['berkoltseg_nem_KIVA'] = $brutto_ber + $ret['ado_szocialis_hozzajarulas'] + $ret['ado_szakkepzesi_hozzajarulas'];
        $ret['berkoltseg_KIVA'] = $brutto_ber + $ret['ado_kisvallalati'];

        $nav_osszes_ado = 0;

        if ($data['ceg_kisvallalati_ado_alany'] == 'Igen') {
          $nav_osszes_ado = $ret['berkoltseg_KIVA'] - $netto_ber;
        } else {
          $nav_osszes_ado = $ret['berkoltseg_nem_KIVA'] - $netto_ber;
        }

        $ret['nav_osszes_ado'] = $nav_osszes_ado;

        $values['szocho_es_kiva_kedvezmeny_alap'] = $szocho_es_kiva_kedvezmeny_alap;
        $values['szokho_kedvezmeny_alap'] = $szokho_kedvezmeny_alap;
        $values['kiva_adoalany'] = $data['ceg_kisvallalati_ado_alany'];

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'netto_ber':
        $ret = array(
          'brutto_ber' => 0
        );
        $settings = $this->loadSettings( $calc );

        $brutto_ber = $data['brutto_ber'];

        $ret['brutto_ber'] = $brutto_ber;

        $csaladi_adokedvezmeny_osszege = 0;
        if ($data['csaladkedvezmenyre_jogosult'] == 'Igen') {
          $csaladi_adokedvezmeny_osszege = $this->csaladiAdokedvezmenyOsszege( (int)$data['csalad_eltartott_gyermek'], (int)$data['csalad_eltartott_gyermek_kedvezmenyezett'], $settings );
        }

        $anyak_gyerek4vagytobb = false;
        if ($data['anyak_4vagytobbgyermek'] == 'Igen') {
          $anyak_gyerek4vagytobb = true;
        }

        $ervenyesitheto_jarulekkedvezmeny = 0;
        $friss_hazasok_kedvezmeny = 0;
        $szemelyi_kedvezmeny = 0;
        $ervenyesitheto_termeszetbeni_kedvezmeny = 0;
        $ervenyesitheto_penzbeni_kedvezmeny = 0;

        if ($data['frisshazas_jogosult'] == 'Igen') {
          $friss_hazasok_kedvezmeny = $settings['adokedvezmeny_frisshazasok'];
        }

        if ($data['szemelyikedvezmeny_jogosult'] == 'Igen') {
          $szemelyi_kedvezmeny = $settings['adokedvezmeny_szemelyi'];
        }

        if ( !$anyak_gyerek4vagytobb )
        {
          $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege+$friss_hazasok_kedvezmeny-$brutto_ber;
          $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
        } else {
          $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege;
          $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
        }

        $ervenyesitheto_jarulekkedvezmeny = $csaladi_adokedvezmeny_maradekalap * 0.15;
        $ervenyesitheto_jarulekkedvezmeny = ($ervenyesitheto_jarulekkedvezmeny < 0) ? 0 : $ervenyesitheto_jarulekkedvezmeny;

        $ervenyesitheto_termeszetbeni_kedvezmeny = $ervenyesitheto_jarulekkedvezmeny - ($brutto_ber * ($settings['ado_termeszetegeszseg']/100));
        $ervenyesitheto_termeszetbeni_kedvezmeny = ($ervenyesitheto_termeszetbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_termeszetbeni_kedvezmeny;

        $ervenyesitheto_penzbeni_kedvezmeny = $ervenyesitheto_termeszetbeni_kedvezmeny - ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100));
        $ervenyesitheto_penzbeni_kedvezmeny = ($ervenyesitheto_penzbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_penzbeni_kedvezmeny;

        if ( !$anyak_gyerek4vagytobb ) {
          $ret['ado_szja'] = (($brutto_ber-$friss_hazasok_kedvezmeny-$csaladi_adokedvezmeny_osszege) * ($settings['ado_szja']/100)) - $szemelyi_kedvezmeny;
          $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
          $ret['ado_szja'] = round($ret['ado_szja']);
        } else {
          $ret['ado_szja'] = 0;
          $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
          $ret['ado_szja'] = round($ret['ado_szja']);
        }

        $ret['ado_termeszetegeszseg'] = ($brutto_ber * ($settings['ado_termeszetegeszseg']/100)) - $ervenyesitheto_jarulekkedvezmeny;
        $ret['ado_termeszetegeszseg'] = ($ret['ado_termeszetegeszseg'] < 0) ? 0 : $ret['ado_termeszetegeszseg'];
        $ret['ado_termeszetegeszseg'] = round($ret['ado_termeszetegeszseg']);

        $ret['ado_penzbeli_egeszseg'] = ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100)) - $ervenyesitheto_termeszetbeni_kedvezmeny;
        $ret['ado_penzbeli_egeszseg'] = ($ret['ado_penzbeli_egeszseg'] < 0) ? 0 : $ret['ado_penzbeli_egeszseg'];
        $ret['ado_penzbeli_egeszseg'] = round($ret['ado_penzbeli_egeszseg']);

        $ret['ado_nyugdij'] = ($brutto_ber * ($settings['ado_nyugdij']/100)) - $ervenyesitheto_penzbeni_kedvezmeny;
        $ret['ado_nyugdij'] = ($ret['ado_nyugdij'] < 0) ? 0 : $ret['ado_nyugdij'];
        $ret['ado_nyugdij'] = round($ret['ado_nyugdij']);

        $ret['ado_munkaerppiac'] = $brutto_ber * ($settings['ado_munkaerppiac']/100);
        $ret['ado_munkaerppiac'] = round($ret['ado_munkaerppiac']);

        $sum_minusbrutto = $ret['ado_szja'] + $ret['ado_termeszetegeszseg'] + $ret['ado_penzbeli_egeszseg'] + $ret['ado_nyugdij'] + $ret['ado_munkaerppiac'];

        $ret['sum_minusbrutto'] = $sum_minusbrutto;
        $netto_ber = $brutto_ber-$sum_minusbrutto;
        $ret['netto_ber'] = $netto_ber;

        $values['csaladi_adokedvezmeny_osszege'] = $csaladi_adokedvezmeny_osszege;
        $values['frisshazas_jogosult'] = $friss_hazasok_kedvezmeny;
        $values['szemelyikedvezmeny_jogosult'] = $szemelyi_kedvezmeny;
        $values['csaladi_adokedvezmeny_maradekalap'] = $csaladi_adokedvezmeny_maradekalap;
        $values['ervenyesitheto_jarulekkedvezmeny'] = $ervenyesitheto_jarulekkedvezmeny;
        $values['ervenyesitheto_termeszetbeni_kedvezmeny'] = $ervenyesitheto_termeszetbeni_kedvezmeny;
        $values['ervenyesitheto_penzbeni_kedvezmeny'] = $ervenyesitheto_penzbeni_kedvezmeny;

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'belepo_szabadsag':
        $ret = array(
          'szabadsag_eves' => 0,
          'szabadsag_idoaranyos' => 0,
          'betegszabadsag_eves' => 0,
          'betegszabadsag_idoaranyos' => 0
        );
        $values = array();
        $settings = $this->loadSettings( $calc );
        $ret['betegszabadsag_eves'] = $settings['betegszabadsag'];
        $targyev = (int)date('Y');
        $ev_elso_napja = date('Y').'-01-01';
        $ev_utolso_napja = date('Y-m-d', strtotime('last day of december this year'));
        $szamitas_kezdete = $ev_elso_napja;

        if ($data['iden_kezdett_dolgozni'] == 'Igen') {
          $szamitas_kezdete = date('Y-m-d', strtotime($data['belepes_datuma']));
        }

        // pre calc
        $munkavallalo_kora = $targyev - $data['szuletesi_ev'];
        $ev_vegeig_hatralevo_napok = round((strtotime($ev_utolso_napja) - strtotime($szamitas_kezdete)) / (60 * 60 * 24))+1;
        $ev_naptari_napok = round((strtotime($ev_utolso_napja) - strtotime($ev_elso_napja)) / (60 * 60 * 24))+1;
        $kor_potszabi = $this->potszabadasgKorSzerint($munkavallalo_kora);
        $gyerek16fiatalabb_potszabi = $this->potszabadasg16evfiatalabbGyerekSzerint((int)$data['gyerek16ev_fiatalabb']);

        // potszabi_ha16evnelfiatalabbgyereketnevel
        // megvaltozott_munkakepessegu

        $szabadsag_eves = (int)$data['athozott_szabadsagok'] + $settings['alapszabadsag'] + $kor_potszabi + $gyerek16fiatalabb_potszabi;

        if ($data['gyerek16ev_fiatalabb_fogyatekos'] == 'Igen') {
          $szabadsag_eves += $settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
          $values['ha16evnelfiatalabbgyereketnevel_potszabi'] = $settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
        }

        if ($data['megvaltozott_munkakepessegu'] == 'Igen') {
          $szabadsag_eves += $settings['potszabi_megvaltozott_munkakepessegu'];
          $values['megvaltozott_munkakepessegu_potszabi'] = $settings['potszabi_megvaltozott_munkakepessegu'];
        }

        $ret['szabadsag_eves'] = $szabadsag_eves;
        $szabadsag_idoaranyos = $szabadsag_eves/$ev_naptari_napok*$ev_vegeig_hatralevo_napok;
        $ret['szabadsag_idoaranyos'] = round($szabadsag_idoaranyos);

        $betegszabadsag_eves = $settings['betegszabadsag'];
        $betegszabadsag_idoaranyos = $betegszabadsag_eves/$ev_naptari_napok*$ev_vegeig_hatralevo_napok;

        $ret['betegszabadsag_eves'] = $betegszabadsag_eves;
        $ret['betegszabadsag_idoaranyos'] = round($betegszabadsag_idoaranyos);


        $values['targyev'] = $targyev;
        $values['szamitas_kezdete'] = $szamitas_kezdete;
        $values['ev_utolso_napja'] = $ev_utolso_napja;
        $values['munkavallalo_kora'] = $munkavallalo_kora;
        $values['ev_vegeig_hatralevo_napok'] = $ev_vegeig_hatralevo_napok;
        $values['ev_naptari_napok'] = $ev_naptari_napok;
        $values['kor_potszabi'] = $kor_potszabi;
        $values['gyerek16fiatalabb_potszabi'] = $gyerek16fiatalabb_potszabi;

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;

      case 'cegauto_ado':
        return $this->calcCegautoAdo( $data['emission'], (float)$data['kw'] );
      break;

      case 'ingatlan_ertekesites':
        $ret = array();
        $settings = $this->loadSettings( $calc );
        $ret['settings'] = $settings;

        $bevetel = (float)$data['atruhazasbol_bevetel'];
        // C8+C9+(C10-C11)+C12
        $koltseg = $data['megszerzes_osszeg'] + $data['megszerzes_egyeb_kiadas'] + ($data['erteknovelo_beruhazasok'] - $data['erteknovelo_beruhazasok_allammegovas']) + $data['atruhazas_koltsegei'];
        // (C14-C15)*FKERES(C5;F5:G10;2;IGAZ)
        $szorzo = $this->ingatlan_ertekesites_jovedelem_szorzo((int)$data['szerzes_eve'], (int)$data['atruhazas_eve']);
        $jovedelem = ($bevetel - $koltseg) * $szorzo;
        $fizetendo_szja = 0;

        $fizetendo_szja = $jovedelem * ($settings['ado_szja']/100);
        $fizetendo_szja = ($fizetendo_szja < 0) ? 0 : $fizetendo_szja;
        $fizetendo_szja = round($fizetendo_szja);

        $ret['bevetel'] = $bevetel;
        $ret['koltseg'] = $koltseg;
        $ret['jovedelem'] = $jovedelem;
        $ret['fizetendo_szja'] = $fizetendo_szja;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'osztalekado':
        $ret = array();
        $settings = $this->loadSettings( $calc );
        $ret['settings'] = $settings;
        $szocho_ado_max = $settings['minimalber'] * 24;

        $osszes_jovedelem = 0;
        $fizetendo_szja = 0;
        $fizetendo_szocho = 0;

        $osszes_jovedelem =
        (float)$data['teljes_targyev_brutto_munkaber'] +
        (float)$data['teljes_targyev_brutto_tarasvall_kivet'] +
        (float)$data['targyev_megszerzett_brutto_osztalek'] +
        (float)$data['targyev_vall_kivont_jovedelem'] +
        (float)$data['targyev_ertekpapkolcson_jovedelem'] +
        (float)$data['targyev_arfolyamnyereseg_jovedelem'] +
        (float)$data['egyeb_szja_jovedelem'];

        $fizetendo_szja = (float)$data['brutto_alap'] * ($settings['ado_szja']/100);
        $fizetendo_szja = round($fizetendo_szja);

        if ( $data['osztalek_kifizetes'] == 'Igen' )
        {
          $alapszamitas = min( ((float)$data['brutto_alap'] * ($settings['ado_szocialis_hozzajarulas']/100)), (($szocho_ado_max - $osszes_jovedelem) * ($settings['ado_szocialis_hozzajarulas']/100)) );
          $fizetendo_szocho = $alapszamitas;
          $fizetendo_szocho = round($fizetendo_szocho);
          if ($fizetendo_szocho < 0 ) {
            $fizetendo_szocho = 0;
          }
        }

        $ret['jovedelem'] = $osszes_jovedelem;
        $ret['fizetendo_szja'] = $fizetendo_szja;
        $ret['fizetendo_szocho'] = $fizetendo_szocho;
        $ret['fizetendo'] = $fizetendo_szja +  $fizetendo_szocho;
        $ret['brutto_alap'] = $data['brutto_alap'];
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'cafeteria':
        $ret = array();
        $settings = $this->loadSettings( $calc );
        $ret['settings'] = $settings;

        $jg = $this->getCafeteriaGroupByTitle( $data['juttatas'] );
        $ret['juttatas_group'] = $jg;

        $adoalap_kiegeszites = 0;
        $szja = 0;
        $szocho = 0;
        $szkh = 0;
        $kiva = 0;
        $ado_munkavallalo = 0;
        $ado_mukaltato = 0;

        $adoalap = (float)$data['juttatas_osszege'];

        if ( $jg && in_array($jg['ID'], array(2, 3)) )
        {
          if ( $jg['ID'] == 2) {
            $adoalap_tetel_limits = $this->getCafeteriaItemAdoalapLimit();
            $adoalap_tetel_limit = (float)$adoalap_tetel_limits[$data['juttatas']];

            if ((float)$data['juttatas_osszege'] <= $adoalap_tetel_limit) {
              $adoalap_kiegeszites = 0;
            } else {
              $adoalap_kiegeszites = (float)$data['juttatas_osszege'] - $adoalap_tetel_limit;
              $adoalap_kiegeszites = $adoalap_kiegeszites * ($settings['ado_caf_adoalap_kieg']/100);
              $adoalap_kiegeszites = ($adoalap_kiegeszites < 0) ? 0 : $adoalap_kiegeszites;
              $adoalap_kiegeszites = round($adoalap_kiegeszites);
            }
          }

          if ( $jg['ID'] == 3) {
            $adoalap_kiegeszites = (float)$data['juttatas_osszege'] * ($settings['ado_caf_adoalap_kieg']/100);
            $adoalap_kiegeszites = ($adoalap_kiegeszites < 0) ? 0 : $adoalap_kiegeszites;
            $adoalap_kiegeszites = round($adoalap_kiegeszites);
          }

          $adoalap = (float)$adoalap_kiegeszites;
        }

        // szja
        // group 3, 4
        if ($jg && in_array($jg['ID'], array(3, 4)) ) {
          $szja = $adoalap * ($settings['ado_szja']/100);
          $szja = ($szja < 0) ? 0 : $szja;
          $szja = round($szja);
        }

        // szocho
        if ($data['ceg_kiva'] == 'Nem') {
          $szocho = $adoalap * ($settings['ado_szocialis_hozzajarulas']/100);
          $szocho = ($szocho < 0) ? 0 : $szocho;
          $szocho = round($szocho);
        }

        // Szakképzési
        if ($data['ceg_kiva'] == 'Nem' && $jg && in_array($jg['ID'], array(4))) {
          $szkh = $adoalap * ($settings['ado_szakkepzesi_hozzajarulas']/100);
          $szkh = ($szkh < 0) ? 0 : $szkh;
          $szkh = round($szkh);
        }

        // KIVA
        if ($data['ceg_kiva'] == 'Igen' && $jg && in_array($jg['ID'], array(3, 4))) {
          $kiva = $data['juttatas_osszege'] * ($settings['ado_kisvallalati']/100);
          $kiva = ($kiva < 0) ? 0 : $kiva;
          $kiva = round($kiva);
        }

        // group 4
        if ($jg && in_array($jg['ID'], array(4)))
        {
          // természetbeni egészség
          $termeszet_egeszseg_jarulek = $adoalap * ($settings['ado_termeszetegeszseg']/100);
          $termeszet_egeszseg_jarulek = ($termeszet_egeszseg_jarulek < 0) ? 0 : $termeszet_egeszseg_jarulek;
          $termeszet_egeszseg_jarulek = round($termeszet_egeszseg_jarulek);

          // pénzbeli egészség
          $penzbeli_egeszseg_jarulek = $adoalap * ($settings['ado_penzbeli_egeszseg']/100);
          $penzbeli_egeszseg_jarulek = ($penzbeli_egeszseg_jarulek < 0) ? 0 : $penzbeli_egeszseg_jarulek;
          $penzbeli_egeszseg_jarulek = round($penzbeli_egeszseg_jarulek);

          // Nyugdíjjárulék
          $nyugdij_jarulek = $adoalap * ($settings['ado_nyugdij']/100);
          $nyugdij_jarulek = ($nyugdij_jarulek < 0) ? 0 : $nyugdij_jarulek;
          $nyugdij_jarulek = round($nyugdij_jarulek);

          // munkaerő hozzájárulás
          $munkaeropiac_hozzajarulas = $adoalap * ($settings['ado_munkaerppiac']/100);
          $munkaeropiac_hozzajarulas = ($munkaeropiac_hozzajarulas < 0) ? 0 : $munkaeropiac_hozzajarulas;
          $munkaeropiac_hozzajarulas = round($munkaeropiac_hozzajarulas);

          // összes munkaválllalói teher
          $munkavallalo_osszes_jarulek = $termeszet_egeszseg_jarulek + $penzbeli_egeszseg_jarulek + $nyugdij_jarulek + $munkaeropiac_hozzajarulas + $szja;
          $ado_munkavallalo = $munkavallalo_osszes_jarulek;
        }

        // group 2

        if ($jg && in_array($jg['ID'], array(2)))
        {
          // szja
          if ( $adoalap_kiegeszites > 0 ) {
            $szja = ($adoalap_tetel_limit + $adoalap_kiegeszites) * ($settings['ado_szja']/100);
            $szja = ($szja < 0) ? 0 : $szja;
            $szja = round($szja);
          } else {
            $szja = ($adoalap_tetel_limit) * ($settings['ado_szja']/100);
            $szja = ($szja < 0) ? 0 : $szja;
            $szja = round($szja);
          }

          // szocho
          if ( $data['ceg_kiva'] == 'Nem' && $adoalap_kiegeszites > 0)
          {
            $szocho = ($adoalap_tetel_limit + $adoalap_kiegeszites) * ($settings['ado_szocialis_hozzajarulas']/100);
            $szocho = ($szocho < 0) ? 0 : $szocho;
            $szocho = round($szocho);
          }

          if ( $data['ceg_kiva'] == 'Nem' && $adoalap_kiegeszites == 0 )
          {
            $szocho = ($adoalap_tetel_limit) * ($settings['ado_szocialis_hozzajarulas']/100);
            $szocho = ($szocho < 0) ? 0 : $szocho;
            $szocho = round($szocho);
          }

          // kiva
          if ( $data['ceg_kiva'] == 'Igen' )
          {
            $kiva = (float)$data['juttatas_osszege'] * ($settings['ado_kisvallalati']/100);
            $kiva = ($kiva < 0) ? 0 : $kiva;
            $kiva = round($kiva);
          }
        }

        // Munkáltató adók
        if ($jg && in_array($jg['ID'], array(2, 3))) {
          $ado_munkaltato = $szja + $szocho + $kiva;
        }

        if ($jg && in_array($jg['ID'], array(4))) {
          $ado_munkaltato = $szocho + $szkh + $kiva;
        }

        $ret['adoalap_kiegeszites'] = $adoalap_kiegeszites;
        $ret['szja'] = $szja;
        $ret['szocho'] = $szocho;
        $ret['szkh'] = $szkh;
        $ret['kiva'] = $kiva;

        $ret['termeszet_egeszseg_jarulek'] = $termeszet_egeszseg_jarulek;
        $ret['penzbeli_egeszseg_jarulek'] = $penzbeli_egeszseg_jarulek;
        $ret['nyugdij_jarulek'] = $nyugdij_jarulek;
        $ret['munkaeropiac_hozzajarulas'] = $munkaeropiac_hozzajarulas;
        $ret['munkavallalo_osszes_jarulek'] = $munkavallalo_osszes_jarulek;

        $ret['ado_munkavallalo'] = $ado_munkavallalo;
        $ret['ado_munkaltato'] = $ado_munkaltato;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'anyak_szabadsaga':
        $ret = array(
          'szules_eveben_szabadsag' => 0,
          'szulesig_idoaranyos_szabadsag' => 0,
          'szules_eveben_igenybe_vett_szabadsag' => 0,
          'le_nem_toltott_szabadsag' => 0,

          'csed_idejere_jaro_szabadsag' => 0,
          'csed_szules_eveben_igenybe_vett_szabadsag' => 0,
          'csed_le_nem_toltott_szabadsag' => 0,

          'gyed_idejere_jaro_szabadsag' => 0,
          'gyed_szules_eveben_igenybe_vett_szabadsag' => 0,
          'gyed_le_nem_toltott_szabadsag' => 0,

          'osszes_szabadsag' => 0,
        );

        $values = array();
        $settings = $this->loadSettings( $calc );

        $targyev = (int)date('Y');
        $ev_elso_napja = date('Y').'-01-01';
        $ev_utolso_napja = date('Y-m-d', strtotime('last day of december this year'));
        $szules_eve = date('Y', strtotime($data['szules_ideje']));
        $szules_eveben_munkavallalo_eletkora = $szules_eve - (int)$data['szuletesi_ev'];
        $kor_potszabi = $this->potszabadasgKorSzerint($szules_eveben_munkavallalo_eletkora);
        $szulelotti_igyenbevett = ($data['szul_elott_igenybevett_potszabadsag_gyermek'] == '3 vagy több') ? 3  : (int)$data['szul_elott_igenybevett_potszabadsag_gyermek'];
        $gyerek_potszabi = $this->potszabadasg16evfiatalabbGyerekSzerint((int)$data['szul_elott_igenybevett_potszabadsag_gyermek']);

        $alap_szules_eveben_jaro_szabadsag = (int)$settings['alapszabadsag'] + $kor_potszabi + $gyerek_potszabi;

        $day = new DateTime($data['csed_kezdete']);
        $day_munkaviszony_kezdet = new DateTime($data['munkaviszony_kezdete']);
        $mv_day_str = strtotime($day_munkaviszony_kezdet->format('Y-m-d'));
        $csed_day_str = strtotime($day->format('Y').'-01-01');

        // alap munkaviszony kezdete
        $calc_ts = $mv_day_str;

        // ha a csed évi első napja nagyobb a mv kezdetétől
        if ( $csed_day_str > $calc_ts ) {
          $calc_ts = $csed_day_str;
        }

        if( $mv_day_str > $csed_day_str )
        {
          $szulesig_eltelt_napok_szama = (float)( (strtotime($day->format('Y-m-d')) - $calc_ts) / (60*60*24) );          
        } else {
          $szulesig_eltelt_napok_szama = (float)( (strtotime($day->format('Y-m-d')) - $calc_ts) / (60*60*24) );
        }

        if ($data['gyerek16ev_fiatalabb_fogyatekos'] == 'Igen') {
          $alap_szules_eveben_jaro_szabadsag += (float)$settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
        }

        $szulesig_idoaranyos_szabadsag = round($alap_szules_eveben_jaro_szabadsag / 365 * $szulesig_eltelt_napok_szama);
        $szules_eveben_igenybe_vett_szabadsag = (int)$data['szulev_igenybevett_szabadsag'];
        $le_nem_toltott_szabadsag = $szulesig_idoaranyos_szabadsag - $szules_eveben_igenybe_vett_szabadsag;

        $csed_kezdete = $data['csed_kezdete'];
        $minucseddays = 168-1;
        $csed_vege_datum = new DateTime($csed_kezdete); $csed_vege_datum->modify('+ '.$minucseddays.'day');
        $csed_vege = $csed_vege_datum->format('Y-m-d');

        $gyedgyes_kezdete = $data['gyedgyes_kezdete'];
        $minugyedgyesmonth = 6;
        $gyedgyes_vege_datum = new DateTime($gyedgyes_kezdete); $gyedgyes_vege_datum->modify('+ '.$minugyedgyesmonth.' month'); $gyedgyes_vege_datum->modify('-1 day');
        $gyedgyes_vege = $gyedgyes_vege_datum->format('Y-m-d');

        // Helpers
        // CSED
        $help_csed_kezdet = $csed_kezdete;
        $help_csed_vegzet = $csed_vege;
        $help_csed_eletkor1 = date('Y', strtotime($csed_kezdete)) - (int)$data['szuletesi_ev'];
        $help_csed_eletkor2 = date('Y', strtotime($help_csed_vegzet)) - (int)$data['szuletesi_ev'];
        $help_csed_potszabi1 = (int)$this->potszabadasgKorSzerint( (int)$help_csed_eletkor1 );
        $help_csed_potszabi2 = (int)$this->potszabadasgKorSzerint( (int)$help_csed_eletkor2 );

        if ( date('Y', strtotime($csed_kezdete)) == date('Y', strtotime($csed_vege)) ) {
          $vegzetplusz1 = new DateTime($help_csed_vegzet); $vegzetplusz1->modify('+1 day');
          $help_csed_potszabi = round( $help_csed_potszabi1 / 365 * ((strtotime($vegzetplusz1->format('Y-m-d')) - strtotime($help_csed_kezdet))/(60*60*24)));
        }
        else
        {
          // KEREKÍTÉS( G33/365 * (DÁTUM(ÉV(C33);12;31) + 1 - C33); 0 ) + KEREKÍTÉS( H33/365 * (D33 + 1 - DÁTUM(ÉV(D33); 1; 1)); 0 )
          $veg_s1 = date('Y', strtotime($csed_kezdete)).'-12-31';
          $veg_s1 = new DateTime($veg_s1); $veg_s1->modify('+1 day');
          $veg_s1 = $veg_s1->format('Y-m-d');

          $veg_s2 = date('Y', strtotime($csed_vege)).'-01-01';
          $veg_s21 = new DateTime($csed_vege); $veg_s21->modify('+1 day');
          $veg_s21 = $veg_s21->format('Y-m-d');

          $help_csed_potszabi =
          round( $help_csed_potszabi1 / 365 * ( (strtotime($veg_s1) - strtotime($csed_kezdete)) /(60*60*24) )) +
          round( $help_csed_potszabi2 / 365 * ( (strtotime($veg_s21) - (strtotime($veg_s2))) / (60*60*24) ));
        }

        $values['helper']['help_csed_kezdet'] = $help_csed_kezdet;
        $values['helper']['help_csed_vegzet'] = $help_csed_vegzet;
        $values['helper']['help_csed_eletkor1'] = $help_csed_eletkor1;
        $values['helper']['help_csed_eletkor2'] = $help_csed_eletkor2;
        $values['helper']['help_csed_potszabi1'] = $help_csed_potszabi1;
        $values['helper']['help_csed_potszabi2'] = $help_csed_potszabi2;
        $values['helper']['csed_potszabi'] = $help_csed_potszabi;

        // GYED
        $help_gyedgyes_kezdet = $gyedgyes_kezdete;
        $help_gyedgyes_vegzet = $gyedgyes_vege;
        $help_gyedgyes_eletkor1 = date('Y', strtotime($gyedgyes_kezdete)) - (int)$data['szuletesi_ev'];
        $help_gyedgyes_eletkor2 = date('Y', strtotime($help_gyedgyes_vegzet)) - (int)$data['szuletesi_ev'];
        $help_gyedgyes_potszabi1 = (int)$this->potszabadasgKorSzerint( (int)$help_gyedgyes_eletkor1 );
        $help_gyedgyes_potszabi2 = (int)$this->potszabadasgKorSzerint( (int)$help_gyedgyes_eletkor2 );

        if ( date('Y', strtotime($gyedgyes_kezdete)) == date('Y', strtotime($gyedgyes_vege)) ) {
          $vegzetplusz1 = new DateTime($help_gyedgyes_vegzet); $vegzetplusz1->modify('+1 day');
          $help_gyedgyes_potszabi = round( $help_gyedgyes_potszabi1 / 365 * ((strtotime($vegzetplusz1->format('Y-m-d')) - strtotime($help_gyedgyes_kezdet))/(60*60*24)));
        }
        else
        {
          $veg_s1 = date('Y', strtotime($gyedgyes_kezdete)).'-12-31';
          $veg_s1 = new DateTime($veg_s1); $veg_s1->modify('+1 day');
          $veg_s1 = $veg_s1->format('Y-m-d');

          $veg_s2 = date('Y', strtotime($gyedgyes_vege)).'-01-01';
          $veg_s21 = new DateTime($gyedgyes_vege); $veg_s21->modify('+1 day');
          $veg_s21 = $veg_s21->format('Y-m-d');

          $help_gyedgyes_potszabi =
          round( $help_gyedgyes_potszabi1 / 365 * ( (strtotime($veg_s1) - strtotime($gyedgyes_kezdete)) /(60*60*24) )) +
          round( $help_gyedgyes_potszabi2 / 365 * ( (strtotime($veg_s21) - (strtotime($veg_s2))) / (60*60*24) ));
        }

        $values['helper']['help_gyedgyes_kezdet'] = $help_gyedgyes_kezdet;
        $values['helper']['help_gyedgyes_vegzet'] = $help_gyedgyes_vegzet;
        $values['helper']['help_gyedgyes_eletkor1'] = $help_gyedgyes_eletkor1;
        $values['helper']['help_gyedgyes_eletkor2'] = $help_gyedgyes_eletkor2;
        $values['helper']['help_gyedgyes_potszabi1'] = $help_gyedgyes_potszabi1;
        $values['helper']['help_gyedgyes_potszabi2'] = $help_gyedgyes_potszabi2;
        $values['helper']['gyedgyes_potszabi'] = $help_gyedgyes_potszabi;

        // csed
        $csed_idejere_jaro_szabadsag = (int)$settings['alapszabadsag'] + $this->potszabadasg16evfiatalabbGyerekSzerint((int)$data['szul_elott_igenybevett_potszabadsag_gyermek']);
        if ($data['gyerek16ev_fiatalabb_fogyatekos'] == 'Igen') {
          $csed_idejere_jaro_szabadsag += (float)$settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
        }
        $csed_idejere_jaro_szabadsag_vt1 = new DateTime($csed_vege); $csed_idejere_jaro_szabadsag_vt1->modify('+1 day');
        $csed_idejere_jaro_szabadsag_vt1 = $csed_idejere_jaro_szabadsag_vt1->format('Y-m-d');
        $csed_idejere_jaro_szabadsag = $csed_idejere_jaro_szabadsag / 365 * ((strtotime($csed_idejere_jaro_szabadsag_vt1) - strtotime($csed_kezdete))/(60*60*24));
        $csed_idejere_jaro_szabadsag = round($csed_idejere_jaro_szabadsag);
        $csed_idejere_jaro_szabadsag += (int)$help_csed_potszabi;

        $csed_szules_eveben_igenybe_vett_szabadsag = min($le_nem_toltott_szabadsag, 0);

        $csed_le_nem_toltott_szabadsag = $csed_idejere_jaro_szabadsag + $csed_szules_eveben_igenybe_vett_szabadsag;

        // gyedgyes
        $gyedgyes_idejere_jaro_szabadsag = (int)$settings['alapszabadsag'] + $this->potszabadasg16evfiatalabbGyerekSzerint((int)$data['szul_elott_igenybevett_potszabadsag_gyermek']);
        if ($data['gyerek16ev_fiatalabb_fogyatekos'] == 'Igen') {
          $gyedgyes_idejere_jaro_szabadsag += (float)$settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
        }
        $gyedgyes_idejere_jaro_szabadsag_vt1 = new DateTime($gyedgyes_vege); $gyedgyes_idejere_jaro_szabadsag_vt1->modify('+1 day');
        $gyedgyes_idejere_jaro_szabadsag_vt1 = $gyedgyes_idejere_jaro_szabadsag_vt1->format('Y-m-d');
        $gyedgyes_idejere_jaro_szabadsag = $gyedgyes_idejere_jaro_szabadsag / 365 * ((strtotime($gyedgyes_idejere_jaro_szabadsag_vt1) - strtotime($gyedgyes_kezdete))/(60*60*24));
        $gyedgyes_idejere_jaro_szabadsag = round($gyedgyes_idejere_jaro_szabadsag);
        $gyedgyes_idejere_jaro_szabadsag += (int)$help_gyedgyes_potszabi;

        $gyedgyes_szules_eveben_igenybe_vett_szabadsag = min($csed_le_nem_toltott_szabadsag, 0);

        $gyedgyes_le_nem_toltott_szabadsag = $gyedgyes_idejere_jaro_szabadsag + $gyedgyes_szules_eveben_igenybe_vett_szabadsag;

        // összes
        $osszes_szabadsag = $le_nem_toltott_szabadsag + $csed_idejere_jaro_szabadsag + $gyedgyes_idejere_jaro_szabadsag;

        $ret['szules_eveben_szabadsag'] = $alap_szules_eveben_jaro_szabadsag;
        $ret['szulesig_idoaranyos_szabadsag'] = $szulesig_idoaranyos_szabadsag;
        $ret['szules_eveben_igenybe_vett_szabadsag'] = $szules_eveben_igenybe_vett_szabadsag;
        $ret['le_nem_toltott_szabadsag'] = $le_nem_toltott_szabadsag;
        $ret['csed_idejere_jaro_szabadsag'] = $csed_idejere_jaro_szabadsag;
        $ret['csed_szules_eveben_igenybe_vett_szabadsag'] = $csed_szules_eveben_igenybe_vett_szabadsag;
        $ret['csed_le_nem_toltott_szabadsag'] = $csed_le_nem_toltott_szabadsag;
        $ret['gyedgyes_idejere_jaro_szabadsag'] = $gyedgyes_idejere_jaro_szabadsag;
        $ret['gyedgyes_szules_eveben_igenybe_vett_szabadsag'] = $gyedgyes_szules_eveben_igenybe_vett_szabadsag;
        $ret['gyedgyes_le_nem_toltott_szabadsag'] = $gyedgyes_le_nem_toltott_szabadsag;
        $ret['osszes_szabadsag'] = $osszes_szabadsag;

        $values['szules_eve'] = $szules_eve;
        $values['szules_eveben_munkavallalo_eletkora'] = $szules_eveben_munkavallalo_eletkora;
        $values['gyerek_potszabi'] = $gyerek_potszabi;
        $values['gyerek_fogyatek_potszabi'] = $gyerek_fogyatek_potszabi;
        $values['szulesig_eltelt_napok_szama'] = $szulesig_eltelt_napok_szama;
        $values['csed_vege'] = $csed_vege;
        $values['gyedgyes_vege'] = $gyedgyes_vege;

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
    }

    return false;
  }
}


/***********************************************
* 2020/2-es jogszabályoknak megfelelő kalkulátor @since 2020-07-01 *
************************************************/
class CalculatorV2020_2 extends CalculatorBase implements CalculatorVersion
{
  public function __construct( $version = false )
  {
    parent::__construct( $version );
    return $this;
  }

  public function calcBerAdoLevonasok( $brutto_ber, $data, $settings )
  {
    $ret = array();

    $csaladi_adokedvezmeny_osszege = 0;
    if ($data['csaladkedvezmenyre_jogosult'] == 'Igen') {
      $csaladi_adokedvezmeny_osszege = $this->csaladiAdokedvezmenyOsszege( (int)$data['csalad_eltartott_gyermek'], (int)$data['csalad_eltartott_gyermek_kedvezmenyezett'], $settings );
    }

    $anyak_gyerek4vagytobb = false;
    if ($data['anyak_4vagytobbgyermek'] == 'Igen') {
      $anyak_gyerek4vagytobb = true;
    }

    $ervenyesitheto_jarulekkedvezmeny = 0;
    $friss_hazasok_kedvezmeny = 0;
    $szemelyi_kedvezmeny = 0;
    $ervenyesitheto_termeszetbeni_kedvezmeny = 0;
    $ervenyesitheto_penzbeni_kedvezmeny = 0;

    if ($data['frisshazas_jogosult'] == 'Igen') {
      $friss_hazasok_kedvezmeny = $settings['adokedvezmeny_frisshazasok'];
    }

    if ($data['szemelyikedvezmeny_jogosult'] == 'Igen') {
      $szemelyi_kedvezmeny = $settings['adokedvezmeny_szemelyi'];
    }

    if ( !$anyak_gyerek4vagytobb )
    {
      $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege+$friss_hazasok_kedvezmeny-$brutto_ber;
      $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
    } else {
      $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege;
      $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
    }

    $ervenyesitheto_jarulekkedvezmeny = $csaladi_adokedvezmeny_maradekalap * 0.15;
    $ervenyesitheto_jarulekkedvezmeny = ($ervenyesitheto_jarulekkedvezmeny < 0) ? 0 : $ervenyesitheto_jarulekkedvezmeny;

    //$ervenyesitheto_termeszetbeni_kedvezmeny = $ervenyesitheto_jarulekkedvezmeny - ($brutto_ber * ($settings['ado_termeszetegeszseg']/100));
    //$ervenyesitheto_termeszetbeni_kedvezmeny = ($ervenyesitheto_termeszetbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_termeszetbeni_kedvezmeny;

    //$ervenyesitheto_penzbeni_kedvezmeny = $ervenyesitheto_termeszetbeni_kedvezmeny - ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100));
    //$ervenyesitheto_penzbeni_kedvezmeny = ($ervenyesitheto_penzbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_penzbeni_kedvezmeny;

    if ( !$anyak_gyerek4vagytobb ) {
      $ret['ado_szja'] = (($brutto_ber-$friss_hazasok_kedvezmeny-$csaladi_adokedvezmeny_osszege) * ($settings['ado_szja']/100)) - $szemelyi_kedvezmeny;
      $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
      $ret['ado_szja'] = round($ret['ado_szja']);
    } else {
      $ret['ado_szja'] = 0;
      $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
      $ret['ado_szja'] = round($ret['ado_szja']);
    }

    // TB járulék számítás
    $ret['ado_tb'] = ($brutto_ber * ($settings['ado_tb']/100)) - $ervenyesitheto_jarulekkedvezmeny;;
    $ret['ado_tb'] = ($ret['ado_tb'] < 0) ? 0 : $ret['ado_tb'];
    $ret['ado_tb'] = round($ret['ado_tb']);

    /*
    $ret['ado_termeszetegeszseg'] = ($brutto_ber * ($settings['ado_termeszetegeszseg']/100)) - $ervenyesitheto_jarulekkedvezmeny;
    $ret['ado_termeszetegeszseg'] = ($ret['ado_termeszetegeszseg'] < 0) ? 0 : $ret['ado_termeszetegeszseg'];
    $ret['ado_termeszetegeszseg'] = round($ret['ado_termeszetegeszseg']);
    */

    /*
    $ret['ado_penzbeli_egeszseg'] = ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100)) - $ervenyesitheto_termeszetbeni_kedvezmeny;
    $ret['ado_penzbeli_egeszseg'] = ($ret['ado_penzbeli_egeszseg'] < 0) ? 0 : $ret['ado_penzbeli_egeszseg'];
    $ret['ado_penzbeli_egeszseg'] = round($ret['ado_penzbeli_egeszseg']);

    $ret['ado_nyugdij'] = ($brutto_ber * ($settings['ado_nyugdij']/100)) - $ervenyesitheto_penzbeni_kedvezmeny;
    $ret['ado_nyugdij'] = ($ret['ado_nyugdij'] < 0) ? 0 : $ret['ado_nyugdij'];
    $ret['ado_nyugdij'] = round($ret['ado_nyugdij']);

    $ret['ado_munkaerppiac'] = $brutto_ber * ($settings['ado_munkaerppiac']/100);
    $ret['ado_munkaerppiac'] = round($ret['ado_munkaerppiac']);
    */

    $sum_minusbrutto = $ret['ado_szja'] + $ret['ado_tb'];

    $sum_minusbrutto = round($sum_minusbrutto);

    return array(
      'levonas' => $sum_minusbrutto,
      'params' => $ret
    );
  }

  public function findBruttoFromNetto( $netto, $data, $settings )
  {
    $brutto = $netto * 1.5;
    $allcalc = 0;
    $running = true;
    $step = 0;
    $szamolt_netto = 0;
    $xn = 1000;
    $calced = array();

    $steps = array();
    while( $running )
    {
      $step++;

      $calced = $this->calcBerAdoLevonasok($brutto, $data, $settings);
      $levon = (float)$calced['levonas'];
      $szamolt_netto = $brutto - $levon;
      $tol = ($szamolt_netto / $netto) * 100;

      $steps[] = array(
        'br' => round($brutto),
        'le' => $levon,
        'net' => round($szamolt_netto),
        'tol' => $tol
      );

      if ($step >= 1000) {
        $running = false;
      }

      $fdiff = ($netto - $szamolt_netto);
      if ( abs($fdiff) < 1000 ) {
        $xn = 10;
      }
      if ( abs($fdiff) < 10 ) {
        $xn = 1;
      }

      if( $szamolt_netto > $netto ){
        $brutto -= $xn;
      }

      if ( $szamolt_netto < $netto ) {
        $brutto += $xn;
      }
      if ( round($szamolt_netto) == $netto ) {
        $running = false;
      }
    }

    unset($netto);unset($data);unset($settings);

    return array(
      'brutto' => $brutto,
      'values' => $calced
    );
  }

  public function calculate( $calc, $data )
  {
    switch ( $calc )
    {
      case 'brutto_ber':
        $ret = array(
          'netto_ber' => 0
        );
        $settings = $this->loadSettings( $calc );

        $netto = $data['netto_ber'];
        $ret['netto_ber'] = $netto;

        $find = $this->findBruttoFromNetto( $netto, $data, $settings, $values );
        $sum_minusbrutto = (int)$find['brutto'];
        $values['find'] = $find;

        $ret['sum_minusbrutto'] = $find['values']['levonas'];
        $ret['brutto_ber'] = $netto_ber + $sum_minusbrutto;

        $ret['ado_szja'] = $find['values']['params']['ado_szja'];
        $ret['ado_tb'] = $find['values']['params']['ado_tb'];
        /*
        $ret['ado_termeszetegeszseg'] = $find['values']['params']['ado_termeszetegeszseg'];
        $ret['ado_penzbeli_egeszseg'] = $find['values']['params']['ado_penzbeli_egeszseg'];
        $ret['ado_nyugdij'] = $find['values']['params']['ado_nyugdij'];
        $ret['ado_munkaerppiac'] = $find['values']['params']['ado_munkaerppiac'];
        */

        // Nettó bér alap vége

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'teljes_berkoltseg':
        $ret = array(
          'brutto_ber' => 0
        );
        $settings = $this->loadSettings( $calc );

        $brutto_ber = $data['brutto_ber'];

        $ret['brutto_ber'] = $brutto_ber;

        $csaladi_adokedvezmeny_osszege = 0;
        if ($data['csaladkedvezmenyre_jogosult'] == 'Igen') {
          $csaladi_adokedvezmeny_osszege = $this->csaladiAdokedvezmenyOsszege( (int)$data['csalad_eltartott_gyermek'], (int)$data['csalad_eltartott_gyermek_kedvezmenyezett'], $settings );
        }

        $anyak_gyerek4vagytobb = false;
        if ($data['anyak_4vagytobbgyermek'] == 'Igen') {
          $anyak_gyerek4vagytobb = true;
        }

        $ervenyesitheto_jarulekkedvezmeny = 0;
        $friss_hazasok_kedvezmeny = 0;
        $szemelyi_kedvezmeny = 0;
        $ervenyesitheto_termeszetbeni_kedvezmeny = 0;
        $ervenyesitheto_penzbeni_kedvezmeny = 0;

        if ($data['frisshazas_jogosult'] == 'Igen') {
          $friss_hazasok_kedvezmeny = $settings['adokedvezmeny_frisshazasok'];
        }

        if ($data['szemelyikedvezmeny_jogosult'] == 'Igen') {
          $szemelyi_kedvezmeny = $settings['adokedvezmeny_szemelyi'];
        }

        if ( !$anyak_gyerek4vagytobb )
        {
          $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege+$friss_hazasok_kedvezmeny-$brutto_ber;
          $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
        } else {
          $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege;
          $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
        }

        $ervenyesitheto_jarulekkedvezmeny = $csaladi_adokedvezmeny_maradekalap * 0.15;
        $ervenyesitheto_jarulekkedvezmeny = ($ervenyesitheto_jarulekkedvezmeny < 0) ? 0 : $ervenyesitheto_jarulekkedvezmeny;

        $ervenyesitheto_termeszetbeni_kedvezmeny = $ervenyesitheto_jarulekkedvezmeny - ($brutto_ber * ($settings['ado_termeszetegeszseg']/100));
        $ervenyesitheto_termeszetbeni_kedvezmeny = ($ervenyesitheto_termeszetbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_termeszetbeni_kedvezmeny;

        //$ervenyesitheto_penzbeni_kedvezmeny = $ervenyesitheto_termeszetbeni_kedvezmeny - ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100));
        //$ervenyesitheto_penzbeni_kedvezmeny = ($ervenyesitheto_penzbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_penzbeni_kedvezmeny;

        if ( !$anyak_gyerek4vagytobb ) {
          $ret['ado_szja'] = (($brutto_ber-$friss_hazasok_kedvezmeny-$csaladi_adokedvezmeny_osszege) * ($settings['ado_szja']/100)) - $szemelyi_kedvezmeny;
          $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
          $ret['ado_szja'] = round($ret['ado_szja']);
        } else {
          $ret['ado_szja'] = 0;
          $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
          $ret['ado_szja'] = round($ret['ado_szja']);
        }

        // TB járulék számítás
        $ret['ado_tb'] = ($brutto_ber * ($settings['ado_tb']/100)) - $ervenyesitheto_jarulekkedvezmeny;;
        $ret['ado_tb'] = ($ret['ado_tb'] < 0) ? 0 : $ret['ado_tb'];
        $ret['ado_tb'] = round($ret['ado_tb']);

        $ret['ado_termeszetegeszseg'] = ($brutto_ber * ($settings['ado_termeszetegeszseg']/100)) - $ervenyesitheto_jarulekkedvezmeny;
        $ret['ado_termeszetegeszseg'] = ($ret['ado_termeszetegeszseg'] < 0) ? 0 : $ret['ado_termeszetegeszseg'];
        $ret['ado_termeszetegeszseg'] = round($ret['ado_termeszetegeszseg']);

        $ret['ado_penzbeli_egeszseg'] = ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100)) - $ervenyesitheto_termeszetbeni_kedvezmeny;
        $ret['ado_penzbeli_egeszseg'] = ($ret['ado_penzbeli_egeszseg'] < 0) ? 0 : $ret['ado_penzbeli_egeszseg'];
        $ret['ado_penzbeli_egeszseg'] = round($ret['ado_penzbeli_egeszseg']);

        //$ret['ado_nyugdij'] = ($brutto_ber * ($settings['ado_nyugdij']/100)) - $ervenyesitheto_penzbeni_kedvezmeny;
        //$ret['ado_nyugdij'] = ($ret['ado_nyugdij'] < 0) ? 0 : $ret['ado_nyugdij'];
        //$ret['ado_nyugdij'] = round($ret['ado_nyugdij']);

        //$ret['ado_munkaerppiac'] = $brutto_ber * ($settings['ado_munkaerppiac']/100);
        //$ret['ado_munkaerppiac'] = round($ret['ado_munkaerppiac']);

        //$sum_minusbrutto = $ret['ado_szja'] + $ret['ado_termeszetegeszseg'] + $ret['ado_penzbeli_egeszseg'] + $ret['ado_nyugdij'] + $ret['ado_munkaerppiac'];
        $sum_minusbrutto = $ret['ado_szja'] + $ret['ado_tb'];

        $ret['sum_minusbrutto'] = $sum_minusbrutto;
        $netto_ber = $brutto_ber-$sum_minusbrutto;
        $ret['netto_ber'] = $netto_ber;

        $values['csaladi_adokedvezmeny_osszege'] = $csaladi_adokedvezmeny_osszege;
        $values['frisshazas_jogosult'] = $friss_hazasok_kedvezmeny;
        $values['szemelyikedvezmeny_jogosult'] = $szemelyi_kedvezmeny;
        $values['csaladi_adokedvezmeny_maradekalap'] = $csaladi_adokedvezmeny_maradekalap;
        $values['ervenyesitheto_jarulekkedvezmeny'] = $ervenyesitheto_jarulekkedvezmeny;
        //$values['ervenyesitheto_termeszetbeni_kedvezmeny'] = $ervenyesitheto_termeszetbeni_kedvezmeny;
        //$values['ervenyesitheto_penzbeni_kedvezmeny'] = $ervenyesitheto_penzbeni_kedvezmeny;

        // Nettó bér alap vége

        // Teljes bérköltség számítások
        $mk = $settings['forms']['munkavallalo_kedvezmenyek'];
        $minimalber = $settings['minimalber'];
        $minimalber_ketszeres = $minimalber * 2;
        $szocho_es_kiva_kedvezmeny_alap = 0;
        $szokho_kedvezmeny_alap = 0;

        $sel_mk = (int)$data['munkavallalo_kedvezmeny'];
        $mk_obj = $mk[$sel_mk];

        // szocho kiva alapszámítás
        $kedvezmeny_mertek = $mk_obj['calc']['szochokiva']['kedvezmeny_mertek'];
        $kedvezmeny_max = $mk_obj['calc']['szochokiva']['kedvezmeny_max'];
        $kedvezmeny_max = (float)str_replace(array('{minimalber}', '{minimalber_ketszeres}'), array($minimalber, $minimalber_ketszeres), $kedvezmeny_max);

        $values['szochokiva_kedvezmeny_mertek'] = $kedvezmeny_mertek;
        $values['szochokiva_kedvezmeny_max'] = $kedvezmeny_max;

        if ( $kedvezmeny_mertek > 0 ) {
          if ($kedvezmeny_max > 0) {
            $szocho_es_kiva_kedvezmeny_alap_row = array();
            $szocho_es_kiva_kedvezmeny_alap_row[] = $brutto_ber * ($kedvezmeny_mertek/100);
            $szocho_es_kiva_kedvezmeny_alap_row[] = $kedvezmeny_max * ($kedvezmeny_mertek/100);
            $szocho_es_kiva_kedvezmeny_alap = min($szocho_es_kiva_kedvezmeny_alap_row);
          } else {
            $szocho_es_kiva_kedvezmeny_alap = $brutto_ber * ($kedvezmeny_mertek/100);
          }
        }

        // szokho alapszámítás
        $kedvezmeny_mertek = $mk_obj['calc']['szokho']['kedvezmeny_mertek'];
        $kedvezmeny_max = $mk_obj['calc']['szokho']['kedvezmeny_max'];
        $kedvezmeny_max = (float)str_replace(array('{minimalber}', '{minimalber_ketszeres}'), array($minimalber, $minimalber_ketszeres), $kedvezmeny_max);

        $values['szokho_kedvezmeny_mertek'] = $kedvezmeny_mertek;
        $values['szokho_kedvezmeny_max'] = $kedvezmeny_max;

        if ( $kedvezmeny_mertek > 0 ) {
          if ($kedvezmeny_max > 0) {
            $szokho_kedvezmeny_alap_row = array();
            $szokho_kedvezmeny_alap_row[] = $brutto_ber * ($kedvezmeny_mertek/100);
            $szokho_kedvezmeny_alap_row[] = $kedvezmeny_max * ($kedvezmeny_mertek/100);
            $szokho_kedvezmeny_alap = min($szokho_kedvezmeny_alap_row);
          } else {
            $szokho_kedvezmeny_alap = $brutto_ber * ($kedvezmeny_mertek/100);
          }
        }

        $ret['ado_szocialis_hozzajarulas'] = ($brutto_ber - $szocho_es_kiva_kedvezmeny_alap) * ($settings['ado_szocialis_hozzajarulas']/100);
        $ret['ado_szocialis_hozzajarulas'] = round($ret['ado_szocialis_hozzajarulas']);

        $ret['ado_szakkepzesi_hozzajarulas'] = ($brutto_ber - $szokho_kedvezmeny_alap) * ($settings['ado_szakkepzesi_hozzajarulas']/100);
        $ret['ado_szakkepzesi_hozzajarulas'] = round($ret['ado_szakkepzesi_hozzajarulas']);

        $ret['ado_kisvallalati'] = ($brutto_ber - $szocho_es_kiva_kedvezmeny_alap) * ($settings['ado_kisvallalati']/100);
        $ret['ado_kisvallalati'] = round($ret['ado_kisvallalati']);

        $ret['berkoltseg_nem_KIVA'] = $brutto_ber + $ret['ado_szocialis_hozzajarulas'] + $ret['ado_szakkepzesi_hozzajarulas'];
        $ret['berkoltseg_KIVA'] = $brutto_ber + $ret['ado_kisvallalati'];

        $nav_osszes_ado = 0;

        if ($data['ceg_kisvallalati_ado_alany'] == 'Igen') {
          $nav_osszes_ado = $ret['berkoltseg_KIVA'] - $netto_ber;
        } else {
          $nav_osszes_ado = $ret['berkoltseg_nem_KIVA'] - $netto_ber;
        }

        $ret['nav_osszes_ado'] = $nav_osszes_ado;

        $values['szocho_es_kiva_kedvezmeny_alap'] = $szocho_es_kiva_kedvezmeny_alap;
        $values['szokho_kedvezmeny_alap'] = $szokho_kedvezmeny_alap;
        $values['kiva_adoalany'] = $data['ceg_kisvallalati_ado_alany'];

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'netto_ber':
        $ret = array(
          'brutto_ber' => 0
        );
        $settings = $this->loadSettings( $calc );

        $brutto_ber = $data['brutto_ber'];

        $ret['brutto_ber'] = $brutto_ber;

        $csaladi_adokedvezmeny_osszege = 0;
        if ($data['csaladkedvezmenyre_jogosult'] == 'Igen') {
          $csaladi_adokedvezmeny_osszege = $this->csaladiAdokedvezmenyOsszege( (int)$data['csalad_eltartott_gyermek'], (int)$data['csalad_eltartott_gyermek_kedvezmenyezett'], $settings );
        }

        $anyak_gyerek4vagytobb = false;
        if ($data['anyak_4vagytobbgyermek'] == 'Igen') {
          $anyak_gyerek4vagytobb = true;
        }

        $ervenyesitheto_jarulekkedvezmeny = 0;
        $friss_hazasok_kedvezmeny = 0;
        $szemelyi_kedvezmeny = 0;
        $ervenyesitheto_termeszetbeni_kedvezmeny = 0;
        $ervenyesitheto_penzbeni_kedvezmeny = 0;

        if ($data['frisshazas_jogosult'] == 'Igen') {
          $friss_hazasok_kedvezmeny = $settings['adokedvezmeny_frisshazasok'];
        }

        if ($data['szemelyikedvezmeny_jogosult'] == 'Igen') {
          $szemelyi_kedvezmeny = $settings['adokedvezmeny_szemelyi'];
        }

        if ( !$anyak_gyerek4vagytobb )
        {
          $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege+$friss_hazasok_kedvezmeny-$brutto_ber;
          $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
        } else {
          $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege;
          $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
        }

        $ervenyesitheto_jarulekkedvezmeny = $csaladi_adokedvezmeny_maradekalap * 0.15;
        $ervenyesitheto_jarulekkedvezmeny = ($ervenyesitheto_jarulekkedvezmeny < 0) ? 0 : $ervenyesitheto_jarulekkedvezmeny;

        $ervenyesitheto_termeszetbeni_kedvezmeny = $ervenyesitheto_jarulekkedvezmeny - ($brutto_ber * ($settings['ado_termeszetegeszseg']/100));
        $ervenyesitheto_termeszetbeni_kedvezmeny = ($ervenyesitheto_termeszetbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_termeszetbeni_kedvezmeny;

        //$ervenyesitheto_penzbeni_kedvezmeny = $ervenyesitheto_termeszetbeni_kedvezmeny - ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100));
        //$ervenyesitheto_penzbeni_kedvezmeny = ($ervenyesitheto_penzbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_penzbeni_kedvezmeny;

        if ( !$anyak_gyerek4vagytobb ) {
          $ret['ado_szja'] = (($brutto_ber-$friss_hazasok_kedvezmeny-$csaladi_adokedvezmeny_osszege) * ($settings['ado_szja']/100)) - $szemelyi_kedvezmeny;
          $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
          $ret['ado_szja'] = round($ret['ado_szja']);
        } else {
          $ret['ado_szja'] = 0;
          $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
          $ret['ado_szja'] = round($ret['ado_szja']);
        }

        // TB járulék számítás
        $ret['ado_tb'] = ($brutto_ber * ($settings['ado_tb']/100)) - $ervenyesitheto_jarulekkedvezmeny;;
        $ret['ado_tb'] = ($ret['ado_tb'] < 0) ? 0 : $ret['ado_tb'];
        $ret['ado_tb'] = round($ret['ado_tb']);

        $ret['ado_termeszetegeszseg'] = ($brutto_ber * ($settings['ado_termeszetegeszseg']/100)) - $ervenyesitheto_jarulekkedvezmeny;
        $ret['ado_termeszetegeszseg'] = ($ret['ado_termeszetegeszseg'] < 0) ? 0 : $ret['ado_termeszetegeszseg'];
        $ret['ado_termeszetegeszseg'] = round($ret['ado_termeszetegeszseg']);

        $ret['ado_penzbeli_egeszseg'] = ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100)) - $ervenyesitheto_termeszetbeni_kedvezmeny;
        $ret['ado_penzbeli_egeszseg'] = ($ret['ado_penzbeli_egeszseg'] < 0) ? 0 : $ret['ado_penzbeli_egeszseg'];
        $ret['ado_penzbeli_egeszseg'] = round($ret['ado_penzbeli_egeszseg']);

        //$ret['ado_nyugdij'] = ($brutto_ber * ($settings['ado_nyugdij']/100)) - $ervenyesitheto_penzbeni_kedvezmeny;
        //$ret['ado_nyugdij'] = ($ret['ado_nyugdij'] < 0) ? 0 : $ret['ado_nyugdij'];
        //$ret['ado_nyugdij'] = round($ret['ado_nyugdij']);

        //$ret['ado_munkaerppiac'] = $brutto_ber * ($settings['ado_munkaerppiac']/100);
        //$ret['ado_munkaerppiac'] = round($ret['ado_munkaerppiac']);

        //$sum_minusbrutto = $ret['ado_szja'] + $ret['ado_termeszetegeszseg'] + $ret['ado_penzbeli_egeszseg'] + $ret['ado_nyugdij'] + $ret['ado_munkaerppiac'];
        $sum_minusbrutto = $ret['ado_szja'] + $ret['ado_tb'];

        $ret['sum_minusbrutto'] = $sum_minusbrutto;
        $netto_ber = $brutto_ber-$sum_minusbrutto;
        $ret['netto_ber'] = $netto_ber;

        $values['csaladi_adokedvezmeny_osszege'] = $csaladi_adokedvezmeny_osszege;
        $values['frisshazas_jogosult'] = $friss_hazasok_kedvezmeny;
        $values['szemelyikedvezmeny_jogosult'] = $szemelyi_kedvezmeny;
        $values['csaladi_adokedvezmeny_maradekalap'] = $csaladi_adokedvezmeny_maradekalap;
        $values['ervenyesitheto_jarulekkedvezmeny'] = $ervenyesitheto_jarulekkedvezmeny;
        //$values['ervenyesitheto_termeszetbeni_kedvezmeny'] = $ervenyesitheto_termeszetbeni_kedvezmeny;
        //$values['ervenyesitheto_penzbeni_kedvezmeny'] = $ervenyesitheto_penzbeni_kedvezmeny;

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'belepo_szabadsag':
        $ret = array(
          'szabadsag_eves' => 0,
          'szabadsag_idoaranyos' => 0,
          'betegszabadsag_eves' => 0,
          'betegszabadsag_idoaranyos' => 0
        );
        $values = array();
        $settings = $this->loadSettings( $calc );
        $ret['betegszabadsag_eves'] = $settings['betegszabadsag'];
        $targyev = (int)date('Y');
        $ev_elso_napja = date('Y').'-01-01';
        $ev_utolso_napja = date('Y-m-d', strtotime('last day of december this year'));
        $szamitas_kezdete = $ev_elso_napja;

        if ($data['iden_kezdett_dolgozni'] == 'Igen') {
          $szamitas_kezdete = date('Y-m-d', strtotime($data['belepes_datuma']));
        }

        // pre calc
        $munkavallalo_kora = $targyev - $data['szuletesi_ev'];
        $ev_vegeig_hatralevo_napok = round((strtotime($ev_utolso_napja) - strtotime($szamitas_kezdete)) / (60 * 60 * 24))+1;
        $ev_naptari_napok = round((strtotime($ev_utolso_napja) - strtotime($ev_elso_napja)) / (60 * 60 * 24))+1;
        $kor_potszabi = $this->potszabadasgKorSzerint($munkavallalo_kora);
        $gyerek16fiatalabb_potszabi = $this->potszabadasg16evfiatalabbGyerekSzerint((int)$data['gyerek16ev_fiatalabb']);

        // potszabi_ha16evnelfiatalabbgyereketnevel
        // megvaltozott_munkakepessegu

        $szabadsag_eves = (int)$data['athozott_szabadsagok'] + $settings['alapszabadsag'] + $kor_potszabi + $gyerek16fiatalabb_potszabi;

        if ($data['gyerek16ev_fiatalabb_fogyatekos'] == 'Igen') {
          $szabadsag_eves += $settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
          $values['ha16evnelfiatalabbgyereketnevel_potszabi'] = $settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
        }

        if ($data['megvaltozott_munkakepessegu'] == 'Igen') {
          $szabadsag_eves += $settings['potszabi_megvaltozott_munkakepessegu'];
          $values['megvaltozott_munkakepessegu_potszabi'] = $settings['potszabi_megvaltozott_munkakepessegu'];
        }

        $ret['szabadsag_eves'] = $szabadsag_eves;
        $szabadsag_idoaranyos = $szabadsag_eves/$ev_naptari_napok*$ev_vegeig_hatralevo_napok;
        $ret['szabadsag_idoaranyos'] = round($szabadsag_idoaranyos);

        $betegszabadsag_eves = $settings['betegszabadsag'];
        $betegszabadsag_idoaranyos = $betegszabadsag_eves/$ev_naptari_napok*$ev_vegeig_hatralevo_napok;

        $ret['betegszabadsag_eves'] = $betegszabadsag_eves;
        $ret['betegszabadsag_idoaranyos'] = round($betegszabadsag_idoaranyos);


        $values['targyev'] = $targyev;
        $values['szamitas_kezdete'] = $szamitas_kezdete;
        $values['ev_utolso_napja'] = $ev_utolso_napja;
        $values['munkavallalo_kora'] = $munkavallalo_kora;
        $values['ev_vegeig_hatralevo_napok'] = $ev_vegeig_hatralevo_napok;
        $values['ev_naptari_napok'] = $ev_naptari_napok;
        $values['kor_potszabi'] = $kor_potszabi;
        $values['gyerek16fiatalabb_potszabi'] = $gyerek16fiatalabb_potszabi;

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;

      case 'cegauto_ado':
        return $this->calcCegautoAdo( $data['emission'], (float)$data['kw'] );
      break;

      case 'ingatlan_ertekesites':
        $ret = array();
        $settings = $this->loadSettings( $calc );
        $ret['settings'] = $settings;

        $bevetel = (float)$data['atruhazasbol_bevetel'];
        // C8+C9+(C10-C11)+C12
        $koltseg = $data['megszerzes_osszeg'] + $data['megszerzes_egyeb_kiadas'] + ($data['erteknovelo_beruhazasok'] - $data['erteknovelo_beruhazasok_allammegovas']) + $data['atruhazas_koltsegei'];
        // (C14-C15)*FKERES(C5;F5:G10;2;IGAZ)
        $szorzo = $this->ingatlan_ertekesites_jovedelem_szorzo((int)$data['szerzes_eve'], (int)$data['atruhazas_eve']);
        $jovedelem = ($bevetel - $koltseg) * $szorzo;
        $fizetendo_szja = 0;

        $fizetendo_szja = $jovedelem * ($settings['ado_szja']/100);
        $fizetendo_szja = ($fizetendo_szja < 0) ? 0 : $fizetendo_szja;
        $fizetendo_szja = round($fizetendo_szja);

        $ret['bevetel'] = $bevetel;
        $ret['koltseg'] = $koltseg;
        $ret['jovedelem'] = $jovedelem;
        $ret['fizetendo_szja'] = $fizetendo_szja;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'osztalekado':
        $ret = array();
        $settings = $this->loadSettings( $calc );
        $ret['settings'] = $settings;
        $szocho_ado_max = $settings['minimalber'] * 24;

        $osszes_jovedelem = 0;
        $fizetendo_szja = 0;
        $fizetendo_szocho = 0;

        $osszes_jovedelem =
        (float)$data['teljes_targyev_brutto_munkaber'] +
        (float)$data['teljes_targyev_brutto_tarasvall_kivet'] +
        (float)$data['targyev_megszerzett_brutto_osztalek'] +
        (float)$data['targyev_vall_kivont_jovedelem'] + 
        (float)$data['targyev_ertekpapkolcson_jovedelem'] +
        (float)$data['targyev_arfolyamnyereseg_jovedelem'] +
        (float)$data['egyeb_szja_jovedelem'];

        $fizetendo_szja = (float)$data['brutto_alap'] * ($settings['ado_szja']/100);
        $fizetendo_szja = round($fizetendo_szja);

        if ( $data['osztalek_kifizetes'] == 'Igen' )
        {
          $alapszamitas = min( ((float)$data['brutto_alap'] * ($settings['ado_szocialis_hozzajarulas']/100)), (($szocho_ado_max - $osszes_jovedelem) * ($settings['ado_szocialis_hozzajarulas']/100)) );
          $fizetendo_szocho = $alapszamitas;
          $fizetendo_szocho = round($fizetendo_szocho);
          if ($fizetendo_szocho < 0 ) {
            $fizetendo_szocho = 0;
          }
        }

        $ret['jovedelem'] = $osszes_jovedelem;
        $ret['fizetendo_szja'] = $fizetendo_szja;
        $ret['fizetendo_szocho'] = $fizetendo_szocho;
        $ret['fizetendo'] = $fizetendo_szja +  $fizetendo_szocho;
        $ret['brutto_alap'] = $data['brutto_alap'];
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'cafeteria':
        $ret = array();
        $settings = $this->loadSettings( $calc );
        $ret['settings'] = $settings;

        $jg = $this->getCafeteriaGroupByTitle( $data['juttatas'] );
        $ret['juttatas_group'] = $jg;

        $adoalap_kiegeszites = 0;
        $szja = 0;
        $szocho = 0;
        $szkh = 0;
        $kiva = 0;
        $ado_munkavallalo = 0;
        $ado_mukaltato = 0;
        $hataron_felul = false;

        $adoalap = (float)$data['juttatas_osszege'];

        // Adómentes juttatás
        // group 1 
        if ( $jg && in_array($jg['ID'], array(1)) )
        {          
          $adoalap_tetel_limits = $this->getCafeteriaItemAdoalapLimit();
          $adoalap_tetel_limit = (float)$adoalap_tetel_limits[$data['juttatas']];

          if ($adoalap_tetel_limit == 0 || ((float)$data['juttatas_osszege'] < $adoalap_tetel_limit) ) 
          {
            $adoalap_kiegeszites = 0;
          } else {
            $hataron_felul = true;
            $adoalap_kiegeszites = (float)$data['juttatas_osszege'] - $adoalap_tetel_limit;
            $adoalap_kiegeszites = ($adoalap_kiegeszites < 0) ? 0 : $adoalap_kiegeszites;
            $adoalap_kiegeszites = round($adoalap_kiegeszites);

            $szja = $adoalap_kiegeszites * ($settings['ado_szja']/100);
            $szja = ($szja < 0) ? 0 : $szja;
            $szja = round($szja);
  
            $tb = $adoalap_kiegeszites * ($settings['ado_tb']/100);
            $tb = ($tb < 0) ? 0 : $tb;
            $tb = round($tb);

            // összes munkaválllalói teher
            $munkavallalo_osszes_jarulek = $szja + $tb;
            $munkavallalo_osszes_jarulek = ($munkavallalo_osszes_jarulek < 0) ? 0 : $munkavallalo_osszes_jarulek;
            $munkavallalo_osszes_jarulek = round($munkavallalo_osszes_jarulek);
            $ado_munkavallalo = $munkavallalo_osszes_jarulek;

            // szocho
            if ( $data['ceg_kiva'] == 'Nem' && $adoalap_kiegeszites > 0)
            {
              $szocho = $adoalap_kiegeszites * ($settings['ado_szocialis_hozzajarulas']/100);
              $szocho = ($szocho < 0) ? 0 : $szocho;
              $szocho = round($szocho);
            }

            // Szakképzési
            if ($data['ceg_kiva'] == 'Nem' && $adoalap_kiegeszites > 0) {
              $szkh = $adoalap_kiegeszites * ($settings['ado_szakkepzesi_hozzajarulas']/100);
              $szkh = ($szkh < 0) ? 0 : $szkh;
              $szkh = round($szkh);
            }

            // kiva
            if ( $data['ceg_kiva'] == 'Igen' )
            {
              $kiva = $adoalap_kiegeszites * ($settings['ado_kisvallalati']/100);
              $kiva = ($kiva < 0) ? 0 : $kiva;
              $kiva = round($kiva);
            }

            $ado_munkaltato = $szocho + $szkh + $kiva;
          }
        } 

        if ( $jg && in_array($jg['ID'], array(2, 3)) )
        {
          if ( $jg['ID'] == 2) {
            $adoalap_tetel_limits = $this->getCafeteriaItemAdoalapLimit();
            $adoalap_tetel_limit = (float)$adoalap_tetel_limits[$data['juttatas']];

            if ((float)$data['juttatas_osszege'] <= $adoalap_tetel_limit) {
              $adoalap_kiegeszites = 0;
            } else {
              $hataron_felul = true;
              $adoalap_kiegeszites = (float)$data['juttatas_osszege'] - $adoalap_tetel_limit;
              $adoalap_kiegeszites = $adoalap_kiegeszites * ($settings['ado_caf_adoalap_kieg']/100);
              $adoalap_kiegeszites = ($adoalap_kiegeszites < 0) ? 0 : $adoalap_kiegeszites;
              $adoalap_kiegeszites = round($adoalap_kiegeszites);
            }
          }

          if ( $jg['ID'] == 3) 
          {
            $adoalap_tetel_limits = $this->getCafeteriaItemAdoalapLimit();
            $adoalap_tetel_limit = (float)$adoalap_tetel_limits[$data['juttatas']];
          
            if ($adoalap_tetel_limit != 0 && ((float)$data['juttatas_osszege'] > $adoalap_tetel_limit)) {
              $adoalap_kiegeszites = 0;
              $hataron_felul = true;
            } else {
              $adoalap_kiegeszites = (float)$data['juttatas_osszege'] * ($settings['ado_caf_adoalap_kieg']/100);
              $adoalap_kiegeszites = ($adoalap_kiegeszites < 0) ? 0 : $adoalap_kiegeszites;
              $adoalap_kiegeszites = round($adoalap_kiegeszites);
            }

            // szocho
            if ( $data['ceg_kiva'] == 'Nem' && $adoalap_kiegeszites > 0)
            {
              $szocho = $adoalap_kiegeszites * ($settings['ado_szocialis_hozzajarulas']/100);
              $szocho = ($szocho < 0) ? 0 : $szocho;
              $szocho = round($szocho);
            }

            // Szakképzési
            if ($data['ceg_kiva'] == 'Nem' && $adoalap_kiegeszites > 0 && $hataron_felul) {
              $szkh = $adoalap_kiegeszites * ($settings['ado_szakkepzesi_hozzajarulas']/100);
              $szkh = ($szkh < 0) ? 0 : $szkh;
              $szkh = round($szkh);
            }

            // kiva
            if ( $data['ceg_kiva'] == 'Igen' )
            {
              $kiva = $adoalap_kiegeszites * ($settings['ado_kisvallalati']/100);
              $kiva = ($kiva < 0) ? 0 : $kiva;
              $kiva = round($kiva);
            }
          }

          $adoalap = (float)$adoalap_kiegeszites;
        }

        $ret['values']['adoalap'] = $adoalap;

        // szja
        // group 3, 4
        if ($jg && in_array($jg['ID'], array(3, 4)) ) {
          $szja = $adoalap * ($settings['ado_szja']/100);
          $szja = ($szja < 0) ? 0 : $szja;
          $szja = round($szja);

          $tb = $adoalap * ($settings['ado_tb']/100);
          $tb = ($tb < 0) ? 0 : $tb;
          $tb = round($tb);
        }

        
        $ret['values']['adoalap_tetel_limit'] = $adoalap_tetel_limit;
        $ret['values']['juttatas'] = $data['juttatas'];
        $ret['values']['hataron_felul'] = $hataron_felul;

        // group 3 módosítás, ha elérte a határértéket
        if  ($jg && in_array($jg['ID'], array(3)) && $hataron_felul ) 
        {
          /* **/
          $szja = (float)$data['juttatas_osszege'] * ($settings['ado_szja']/100);
          $szja = ($szja < 0) ? 0 : $szja;
          $szja = round($szja);

          $tb = (float)$data['juttatas_osszege'] * ($settings['ado_tb']/100);
          $tb = ($tb < 0) ? 0 : $tb;
          $tb = round($tb);
          /* */

          // összes munkaválllalói teher
          $munkavallalo_osszes_jarulek = $szja + $tb;
          $munkavallalo_osszes_jarulek = ($munkavallalo_osszes_jarulek < 0) ? 0 : $munkavallalo_osszes_jarulek;
          $munkavallalo_osszes_jarulek = round($munkavallalo_osszes_jarulek);
          $ado_munkavallalo = $munkavallalo_osszes_jarulek;
        } elseif( $jg && in_array($jg['ID'], array(3)) ) {
          $tb = 0;
        }

        // szocho
        if ($data['ceg_kiva'] == 'Nem' && !isset($szocho)) {
          $szocho = $adoalap * ($settings['ado_szocialis_hozzajarulas']/100);
          $szocho = ($szocho < 0) ? 0 : $szocho;
          $szocho = round($szocho);
        }

        // Szakképzési
        if ( !isset($szkh) && $data['ceg_kiva'] == 'Nem' && $jg && in_array($jg['ID'], array(4))) {
          $szkh = $adoalap * ($settings['ado_szakkepzesi_hozzajarulas']/100);
          $szkh = ($szkh < 0) ? 0 : $szkh;
          $szkh = round($szkh);
        }

        // KIVA
        if ($data['ceg_kiva'] == 'Igen' && $jg && in_array($jg['ID'], array(3, 4))) {
          $kiva = $data['juttatas_osszege'] * ($settings['ado_kisvallalati']/100);
          $kiva = ($kiva < 0) ? 0 : $kiva;
          $kiva = round($kiva);
        }

        // group 4
        if ($jg && in_array($jg['ID'], array(4)))
        {
          // összes munkaválllalói teher
          $mv_ado = ($settings['ado_szja'] + $settings['ado_tb']) / 100;
          $munkavallalo_osszes_jarulek = $adoalap * $mv_ado;
          $munkavallalo_osszes_jarulek = ($munkavallalo_osszes_jarulek < 0) ? 0 : $munkavallalo_osszes_jarulek;
          $munkavallalo_osszes_jarulek = round($munkavallalo_osszes_jarulek);
          $ado_munkavallalo = $munkavallalo_osszes_jarulek;

          // szocho
          if ($data['ceg_kiva'] == 'Nem') {
            $szocho = $adoalap * ($settings['ado_szocialis_hozzajarulas']/100);
            $szocho = ($szocho < 0) ? 0 : $szocho;
            $szocho = round($szocho);
          }

          // Szakképzési
          if ($data['ceg_kiva'] == 'Nem' ) {
            $szkh = $adoalap * ($settings['ado_szakkepzesi_hozzajarulas']/100);
            $szkh = ($szkh < 0) ? 0 : $szkh;
            $szkh = round($szkh);
          }
        }

        // group 2

        if ($jg && in_array($jg['ID'], array(2)))
        {
          // szja 
          if ( $adoalap_kiegeszites > 0 ) {
            $szja = ($adoalap_tetel_limit + $adoalap_kiegeszites) * ($settings['ado_szja']/100);
            $szja = ($szja < 0) ? 0 : $szja;
            $szja = round($szja);
          } else {
            $szja = ((float)$data['juttatas_osszege']) * ($settings['ado_szja']/100);
            $szja = ($szja < 0) ? 0 : $szja;
            $szja = round($szja);
          }

          // szocho
          if ( $data['ceg_kiva'] == 'Nem' && $adoalap_kiegeszites > 0)
          {
            $szocho = ($adoalap_tetel_limit + $adoalap_kiegeszites) * ($settings['ado_szocialis_hozzajarulas']/100);
            $szocho = ($szocho < 0) ? 0 : $szocho;
            $szocho = round($szocho);
          }

          if ( $data['ceg_kiva'] == 'Nem' && $adoalap_kiegeszites == 0 )
          {
            $szocho = ((float)$data['juttatas_osszege']) * ($settings['ado_szocialis_hozzajarulas']/100);
            $szocho = ($szocho < 0) ? 0 : $szocho;
            $szocho = round($szocho);
          }

          // kiva
          if ( $data['ceg_kiva'] == 'Igen' )
          {
            $kiva = (float)$data['juttatas_osszege'] * ($settings['ado_kisvallalati']/100);
            $kiva = ($kiva < 0) ? 0 : $kiva;
            $kiva = round($kiva);
          }
        }

        // Munkáltató adók
        if ($jg && in_array($jg['ID'], array(2, 3))) {
          $ado_munkaltato = $szja + $szocho + $kiva + $szkh;
        }

        if ($jg && in_array($jg['ID'], array(4))) {
          $ado_munkaltato = $szocho + $szkh + $kiva;
        }

        // group 3 módosítás, ha elérte a határértéket
        if  ($jg && in_array($jg['ID'], array(3)) && $hataron_felul ) 
        {
          if( $data['ceg_kiva'] == 'Nem' )
          {
            $szocho = (float)$data['juttatas_osszege'] * ($settings['ado_szocialis_hozzajarulas']/100);
            $szocho = ($szocho < 0) ? 0 : $szocho;
            $szocho = round($szocho);
  
            $szkh = (float)$data['juttatas_osszege'] * ($settings['ado_szakkepzesi_hozzajarulas']/100);
            $szkh = ($szkh < 0) ? 0 : $szkh;
            $szkh = round($szkh);
          }

          $ado_munkaltato = $szocho + $szkh + $kiva;
        }

        $ret['adoalap_kiegeszites'] = $adoalap_kiegeszites;
        $ret['szja'] = $szja;
        $ret['tb'] = $tb;
        $ret['szocho'] = $szocho;
        $ret['szkh'] = $szkh;
        $ret['kiva'] = $kiva;

        $ret['termeszet_egeszseg_jarulek'] = $termeszet_egeszseg_jarulek;
        $ret['penzbeli_egeszseg_jarulek'] = $penzbeli_egeszseg_jarulek;
        $ret['nyugdij_jarulek'] = $nyugdij_jarulek;
        $ret['munkaeropiac_hozzajarulas'] = $munkaeropiac_hozzajarulas;
        $ret['munkavallalo_osszes_jarulek'] = $munkavallalo_osszes_jarulek;

        $ret['ado_munkavallalo'] = $ado_munkavallalo;
        $ret['ado_munkaltato'] = $ado_munkaltato;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'anyak_szabadsaga':
        $ret = array(
          'szules_eveben_szabadsag' => 0,
          'szulesig_idoaranyos_szabadsag' => 0,
          'szules_eveben_igenybe_vett_szabadsag' => 0,
          'le_nem_toltott_szabadsag' => 0,

          'csed_idejere_jaro_szabadsag' => 0,
          'csed_szules_eveben_igenybe_vett_szabadsag' => 0,
          'csed_le_nem_toltott_szabadsag' => 0,

          'gyed_idejere_jaro_szabadsag' => 0,
          'gyed_szules_eveben_igenybe_vett_szabadsag' => 0,
          'gyed_le_nem_toltott_szabadsag' => 0,

          'osszes_szabadsag' => 0,
        );

        $values = array();
        $settings = $this->loadSettings( $calc );

        $targyev = (int)date('Y');
        $ev_elso_napja = date('Y').'-01-01';
        $ev_utolso_napja = date('Y-m-d', strtotime('last day of december this year'));
        $szules_eve = date('Y', strtotime($data['szules_ideje']));
        $szules_eveben_munkavallalo_eletkora = $szules_eve - (int)$data['szuletesi_ev'];
        $kor_potszabi = $this->potszabadasgKorSzerint($szules_eveben_munkavallalo_eletkora);
        $szulelotti_igyenbevett = ($data['szul_elott_igenybevett_potszabadsag_gyermek'] == '3 vagy több') ? 3  : (int)$data['szul_elott_igenybevett_potszabadsag_gyermek'];
        $gyerek_potszabi = $this->potszabadasg16evfiatalabbGyerekSzerint((int)$data['szul_elott_igenybevett_potszabadsag_gyermek']);

        $alap_szules_eveben_jaro_szabadsag = (int)$settings['alapszabadsag'] + $kor_potszabi + $gyerek_potszabi;

        $day = new DateTime($data['csed_kezdete']);
        $day_munkaviszony_kezdet = new DateTime($data['munkaviszony_kezdete']);
        $mv_day_str = strtotime($day_munkaviszony_kezdet->format('Y-m-d'));
        $csed_day_str = strtotime($day->format('Y').'-01-01');

        // alap munkaviszony kezdete
        $calc_ts = $mv_day_str;

        // ha a csed évi első napja nagyobb a mv kezdetétől
        if ( $csed_day_str > $calc_ts ) {
          $calc_ts = $csed_day_str;
        }

        if( $mv_day_str > $csed_day_str )
        {
          $szulesig_eltelt_napok_szama = (float)( (strtotime($day->format('Y-m-d')) - $calc_ts) / (60*60*24) );          
        } else {
          $szulesig_eltelt_napok_szama = (float)( (strtotime($day->format('Y-m-d')) - $calc_ts) / (60*60*24) );
        }

        if ($data['gyerek16ev_fiatalabb_fogyatekos'] == 'Igen') {
          $alap_szules_eveben_jaro_szabadsag += (float)$settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
        }

        $szulesig_idoaranyos_szabadsag = round($alap_szules_eveben_jaro_szabadsag / 365 * $szulesig_eltelt_napok_szama);
        $szules_eveben_igenybe_vett_szabadsag = (int)$data['szulev_igenybevett_szabadsag'];
        $le_nem_toltott_szabadsag = $szulesig_idoaranyos_szabadsag - $szules_eveben_igenybe_vett_szabadsag;

        $csed_kezdete = $data['csed_kezdete'];
        $minucseddays = 168-1;
        $csed_vege_datum = new DateTime($csed_kezdete); $csed_vege_datum->modify('+ '.$minucseddays.'day');
        $csed_vege = $csed_vege_datum->format('Y-m-d');

        $gyedgyes_kezdete = $data['gyedgyes_kezdete'];
        $minugyedgyesmonth = 6;
        $gyedgyes_vege_datum = new DateTime($gyedgyes_kezdete); $gyedgyes_vege_datum->modify('+ '.$minugyedgyesmonth.' month'); $gyedgyes_vege_datum->modify('-1 day');
        $gyedgyes_vege = $gyedgyes_vege_datum->format('Y-m-d');

        // Helpers
        // CSED
        $help_csed_kezdet = $csed_kezdete;
        $help_csed_vegzet = $csed_vege;
        $help_csed_eletkor1 = date('Y', strtotime($csed_kezdete)) - (int)$data['szuletesi_ev'];
        $help_csed_eletkor2 = date('Y', strtotime($help_csed_vegzet)) - (int)$data['szuletesi_ev'];
        $help_csed_potszabi1 = (int)$this->potszabadasgKorSzerint( (int)$help_csed_eletkor1 );
        $help_csed_potszabi2 = (int)$this->potszabadasgKorSzerint( (int)$help_csed_eletkor2 );

        if ( date('Y', strtotime($csed_kezdete)) == date('Y', strtotime($csed_vege)) ) {
          $vegzetplusz1 = new DateTime($help_csed_vegzet); $vegzetplusz1->modify('+1 day');
          $help_csed_potszabi = round( $help_csed_potszabi1 / 365 * ((strtotime($vegzetplusz1->format('Y-m-d')) - strtotime($help_csed_kezdet))/(60*60*24)));
        }
        else
        {
          // KEREKÍTÉS( G33/365 * (DÁTUM(ÉV(C33);12;31) + 1 - C33); 0 ) + KEREKÍTÉS( H33/365 * (D33 + 1 - DÁTUM(ÉV(D33); 1; 1)); 0 )
          $veg_s1 = date('Y', strtotime($csed_kezdete)).'-12-31';
          $veg_s1 = new DateTime($veg_s1); $veg_s1->modify('+1 day');
          $veg_s1 = $veg_s1->format('Y-m-d');

          $veg_s2 = date('Y', strtotime($csed_vege)).'-01-01';
          $veg_s21 = new DateTime($csed_vege); $veg_s21->modify('+1 day');
          $veg_s21 = $veg_s21->format('Y-m-d');

          $help_csed_potszabi =
          round( $help_csed_potszabi1 / 365 * ( (strtotime($veg_s1) - strtotime($csed_kezdete)) /(60*60*24) )) +
          round( $help_csed_potszabi2 / 365 * ( (strtotime($veg_s21) - (strtotime($veg_s2))) / (60*60*24) ));
        }

        $values['helper']['help_csed_kezdet'] = $help_csed_kezdet;
        $values['helper']['help_csed_vegzet'] = $help_csed_vegzet;
        $values['helper']['help_csed_eletkor1'] = $help_csed_eletkor1;
        $values['helper']['help_csed_eletkor2'] = $help_csed_eletkor2;
        $values['helper']['help_csed_potszabi1'] = $help_csed_potszabi1;
        $values['helper']['help_csed_potszabi2'] = $help_csed_potszabi2;
        $values['helper']['csed_potszabi'] = $help_csed_potszabi;

        // GYED
        $help_gyedgyes_kezdet = $gyedgyes_kezdete;
        $help_gyedgyes_vegzet = $gyedgyes_vege;
        $help_gyedgyes_eletkor1 = date('Y', strtotime($gyedgyes_kezdete)) - (int)$data['szuletesi_ev'];
        $help_gyedgyes_eletkor2 = date('Y', strtotime($help_gyedgyes_vegzet)) - (int)$data['szuletesi_ev'];
        $help_gyedgyes_potszabi1 = (int)$this->potszabadasgKorSzerint( (int)$help_gyedgyes_eletkor1 );
        $help_gyedgyes_potszabi2 = (int)$this->potszabadasgKorSzerint( (int)$help_gyedgyes_eletkor2 );

        if ( date('Y', strtotime($gyedgyes_kezdete)) == date('Y', strtotime($gyedgyes_vege)) ) {
          $vegzetplusz1 = new DateTime($help_gyedgyes_vegzet); $vegzetplusz1->modify('+1 day');
          $help_gyedgyes_potszabi = round( $help_gyedgyes_potszabi1 / 365 * ((strtotime($vegzetplusz1->format('Y-m-d')) - strtotime($help_gyedgyes_kezdet))/(60*60*24)));
        }
        else
        {
          $veg_s1 = date('Y', strtotime($gyedgyes_kezdete)).'-12-31';
          $veg_s1 = new DateTime($veg_s1); $veg_s1->modify('+1 day');
          $veg_s1 = $veg_s1->format('Y-m-d');

          $veg_s2 = date('Y', strtotime($gyedgyes_vege)).'-01-01';
          $veg_s21 = new DateTime($gyedgyes_vege); $veg_s21->modify('+1 day');
          $veg_s21 = $veg_s21->format('Y-m-d');

          $help_gyedgyes_potszabi =
          round( $help_gyedgyes_potszabi1 / 365 * ( (strtotime($veg_s1) - strtotime($gyedgyes_kezdete)) /(60*60*24) )) +
          round( $help_gyedgyes_potszabi2 / 365 * ( (strtotime($veg_s21) - (strtotime($veg_s2))) / (60*60*24) ));
        }

        $values['helper']['help_gyedgyes_kezdet'] = $help_gyedgyes_kezdet;
        $values['helper']['help_gyedgyes_vegzet'] = $help_gyedgyes_vegzet;
        $values['helper']['help_gyedgyes_eletkor1'] = $help_gyedgyes_eletkor1;
        $values['helper']['help_gyedgyes_eletkor2'] = $help_gyedgyes_eletkor2;
        $values['helper']['help_gyedgyes_potszabi1'] = $help_gyedgyes_potszabi1;
        $values['helper']['help_gyedgyes_potszabi2'] = $help_gyedgyes_potszabi2;
        $values['helper']['gyedgyes_potszabi'] = $help_gyedgyes_potszabi;

        // csed
        $csed_idejere_jaro_szabadsag = (int)$settings['alapszabadsag'] + $this->potszabadasg16evfiatalabbGyerekSzerint((int)$data['szul_elott_igenybevett_potszabadsag_gyermek']);
        if ($data['gyerek16ev_fiatalabb_fogyatekos'] == 'Igen') {
          $csed_idejere_jaro_szabadsag += (float)$settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
        }
        $csed_idejere_jaro_szabadsag_vt1 = new DateTime($csed_vege); $csed_idejere_jaro_szabadsag_vt1->modify('+1 day');
        $csed_idejere_jaro_szabadsag_vt1 = $csed_idejere_jaro_szabadsag_vt1->format('Y-m-d');
        $csed_idejere_jaro_szabadsag = $csed_idejere_jaro_szabadsag / 365 * ((strtotime($csed_idejere_jaro_szabadsag_vt1) - strtotime($csed_kezdete))/(60*60*24));
        $csed_idejere_jaro_szabadsag = round($csed_idejere_jaro_szabadsag);
        $csed_idejere_jaro_szabadsag += (int)$help_csed_potszabi;

        $csed_szules_eveben_igenybe_vett_szabadsag = min($le_nem_toltott_szabadsag, 0);

        $csed_le_nem_toltott_szabadsag = $csed_idejere_jaro_szabadsag + $csed_szules_eveben_igenybe_vett_szabadsag;

        // gyedgyes
        $gyedgyes_idejere_jaro_szabadsag = (int)$settings['alapszabadsag'] + $this->potszabadasg16evfiatalabbGyerekSzerint((int)$data['szul_elott_igenybevett_potszabadsag_gyermek']);
        if ($data['gyerek16ev_fiatalabb_fogyatekos'] == 'Igen') {
          $gyedgyes_idejere_jaro_szabadsag += (float)$settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
        }
        $gyedgyes_idejere_jaro_szabadsag_vt1 = new DateTime($gyedgyes_vege); $gyedgyes_idejere_jaro_szabadsag_vt1->modify('+1 day');
        $gyedgyes_idejere_jaro_szabadsag_vt1 = $gyedgyes_idejere_jaro_szabadsag_vt1->format('Y-m-d');
        $gyedgyes_idejere_jaro_szabadsag = $gyedgyes_idejere_jaro_szabadsag / 365 * ((strtotime($gyedgyes_idejere_jaro_szabadsag_vt1) - strtotime($gyedgyes_kezdete))/(60*60*24));
        $gyedgyes_idejere_jaro_szabadsag = round($gyedgyes_idejere_jaro_szabadsag);
        $gyedgyes_idejere_jaro_szabadsag += (int)$help_gyedgyes_potszabi;

        $gyedgyes_szules_eveben_igenybe_vett_szabadsag = min($csed_le_nem_toltott_szabadsag, 0);

        $gyedgyes_le_nem_toltott_szabadsag = $gyedgyes_idejere_jaro_szabadsag + $gyedgyes_szules_eveben_igenybe_vett_szabadsag;

        // összes
        $osszes_szabadsag = $le_nem_toltott_szabadsag + $csed_idejere_jaro_szabadsag + $gyedgyes_idejere_jaro_szabadsag;

        $ret['szules_eveben_szabadsag'] = $alap_szules_eveben_jaro_szabadsag;
        $ret['szulesig_idoaranyos_szabadsag'] = $szulesig_idoaranyos_szabadsag;
        $ret['szules_eveben_igenybe_vett_szabadsag'] = $szules_eveben_igenybe_vett_szabadsag;
        $ret['le_nem_toltott_szabadsag'] = $le_nem_toltott_szabadsag;
        $ret['csed_idejere_jaro_szabadsag'] = $csed_idejere_jaro_szabadsag;
        $ret['csed_szules_eveben_igenybe_vett_szabadsag'] = $csed_szules_eveben_igenybe_vett_szabadsag;
        $ret['csed_le_nem_toltott_szabadsag'] = $csed_le_nem_toltott_szabadsag;
        $ret['gyedgyes_idejere_jaro_szabadsag'] = $gyedgyes_idejere_jaro_szabadsag;
        $ret['gyedgyes_szules_eveben_igenybe_vett_szabadsag'] = $gyedgyes_szules_eveben_igenybe_vett_szabadsag;
        $ret['gyedgyes_le_nem_toltott_szabadsag'] = $gyedgyes_le_nem_toltott_szabadsag;
        $ret['osszes_szabadsag'] = $osszes_szabadsag;

        $values['munkaviszony_kezdete'] = $data['munkaviszony_kezdete'];
        $values['szules_eve'] = $szules_eve;
        $values['szules_eveben_munkavallalo_eletkora'] = $szules_eveben_munkavallalo_eletkora;
        $values['gyerek_potszabi'] = $gyerek_potszabi;
        $values['gyerek_fogyatek_potszabi'] = $gyerek_fogyatek_potszabi;
        $values['szulesig_eltelt_napok_szama'] = $szulesig_eltelt_napok_szama;
        $values['csed_vege'] = $csed_vege;
        $values['gyedgyes_vege'] = $gyedgyes_vege;

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
    }

    return false;
  } 
}

/***********************************************
* 2021-es jogszabályoknak megfelelő kalkulátor @since 2021-02-08 *
************************************************/
class CalculatorV2021 extends CalculatorBase implements CalculatorVersion
{
  public function __construct( $version = false )
  {
    parent::__construct( $version );
    return $this;
  }

  public function getCafeteriaItemAdoalapLimit()
  {
    $limits = array();

    $limits['Sportrendezvényre szóló belépőjegy, bérlet'] = 167400;
    $limits['Kulturális szolgáltatásra szóló belépőjegy, bérlet'] = 167400;    
    $limits['Távmunkavégzés költségtérítése'] = 16740;
    $limits['Csekély értékű ajándék'] = 16740;

    $limits['SZÉP kártya vendéglátás'] = 265000;
    $limits['SZÉP kártya szálláshely'] = 400000;
    $limits['SZÉP kártya szabadidő'] = 135000;

    return $limits;
  }

  public function calcBerAdoLevonasok( $brutto_ber, $data, $settings )
  {
    $ret = array();

    $csaladi_adokedvezmeny_osszege = 0;
    if ($data['csaladkedvezmenyre_jogosult'] == 'Igen') {
      $csaladi_adokedvezmeny_osszege = $this->csaladiAdokedvezmenyOsszege( (int)$data['csalad_eltartott_gyermek'], (int)$data['csalad_eltartott_gyermek_kedvezmenyezett'], $settings );
    }

    $anyak_gyerek4vagytobb = false;
    if ($data['anyak_4vagytobbgyermek'] == 'Igen') {
      $anyak_gyerek4vagytobb = true;
    }

    $ervenyesitheto_jarulekkedvezmeny = 0;
    $friss_hazasok_kedvezmeny = 0;
    $szemelyi_kedvezmeny = 0;
    $ervenyesitheto_termeszetbeni_kedvezmeny = 0;
    $ervenyesitheto_penzbeni_kedvezmeny = 0;

    if ($data['frisshazas_jogosult'] == 'Igen') {
      $friss_hazasok_kedvezmeny = $settings['adokedvezmeny_frisshazasok'];
    }

    if ($data['szemelyikedvezmeny_jogosult'] == 'Igen') {
      $szemelyi_kedvezmeny = $settings['minimalber'] / 3;
    }

    // Maradék családi kedvezmény alap
    if ( !$anyak_gyerek4vagytobb )
    {
      $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege+$friss_hazasok_kedvezmeny+$szemelyi_kedvezmeny-$brutto_ber;
      $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
    } else {
      $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege;
      $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
    }

    //$ervenyesitheto_termeszetbeni_kedvezmeny = $ervenyesitheto_jarulekkedvezmeny - ($brutto_ber * ($settings['ado_termeszetegeszseg']/100));
    //$ervenyesitheto_termeszetbeni_kedvezmeny = ($ervenyesitheto_termeszetbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_termeszetbeni_kedvezmeny;

    //$ervenyesitheto_penzbeni_kedvezmeny = $ervenyesitheto_termeszetbeni_kedvezmeny - ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100));
    //$ervenyesitheto_penzbeni_kedvezmeny = ($ervenyesitheto_penzbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_penzbeni_kedvezmeny;

    if ( !$anyak_gyerek4vagytobb ) {
      $ret['ado_szja'] = (($brutto_ber-$friss_hazasok_kedvezmeny-$csaladi_adokedvezmeny_osszege-$szemelyi_kedvezmeny) * ($settings['ado_szja']/100));
      $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
      $ret['ado_szja'] = round($ret['ado_szja']);
    } else {
      $ret['ado_szja'] = 0;
      $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
      $ret['ado_szja'] = round($ret['ado_szja']);
    }

    // TB járulék számítás
    $ret['ado_tb'] = ($brutto_ber * ($settings['ado_tb']/100)) - $ervenyesitheto_jarulekkedvezmeny;;
    $ret['ado_tb'] = ($ret['ado_tb'] < 0) ? 0 : $ret['ado_tb'];
    $ret['ado_tb'] = round($ret['ado_tb']);

    // Öregségi nyugdíjas @ 2021-02-08
    if ($data['oregsegi_nyugdijas'] == 'Igen') {
      $ret['ado_tb'] = 0;
    }

    /*
    $ret['ado_termeszetegeszseg'] = ($brutto_ber * ($settings['ado_termeszetegeszseg']/100)) - $ervenyesitheto_jarulekkedvezmeny;
    $ret['ado_termeszetegeszseg'] = ($ret['ado_termeszetegeszseg'] < 0) ? 0 : $ret['ado_termeszetegeszseg'];
    $ret['ado_termeszetegeszseg'] = round($ret['ado_termeszetegeszseg']);
    */

    /*
    $ret['ado_penzbeli_egeszseg'] = ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100)) - $ervenyesitheto_termeszetbeni_kedvezmeny;
    $ret['ado_penzbeli_egeszseg'] = ($ret['ado_penzbeli_egeszseg'] < 0) ? 0 : $ret['ado_penzbeli_egeszseg'];
    $ret['ado_penzbeli_egeszseg'] = round($ret['ado_penzbeli_egeszseg']);

    $ret['ado_nyugdij'] = ($brutto_ber * ($settings['ado_nyugdij']/100)) - $ervenyesitheto_penzbeni_kedvezmeny;
    $ret['ado_nyugdij'] = ($ret['ado_nyugdij'] < 0) ? 0 : $ret['ado_nyugdij'];
    $ret['ado_nyugdij'] = round($ret['ado_nyugdij']);

    $ret['ado_munkaerppiac'] = $brutto_ber * ($settings['ado_munkaerppiac']/100);
    $ret['ado_munkaerppiac'] = round($ret['ado_munkaerppiac']);
    */

    $sum_minusbrutto = $ret['ado_szja'] + $ret['ado_tb'];

    $sum_minusbrutto = round($sum_minusbrutto);

    return array(
      'levonas' => $sum_minusbrutto,
      'params' => $ret
    );
  }

  public function findBruttoFromNetto( $netto, $data, $settings )
  {
    $brutto = $netto * 1.5;
    $allcalc = 0;
    $running = true;
    $step = 0;
    $szamolt_netto = 0;
    $xn = 1000;
    $calced = array();

    $steps = array();
    while( $running )
    {
      $step++;

      $calced = $this->calcBerAdoLevonasok($brutto, $data, $settings);
      $levon = (float)$calced['levonas'];
      $szamolt_netto = $brutto - $levon;
      $tol = ($szamolt_netto / $netto) * 100;

      $steps[] = array(
        'br' => round($brutto),
        'le' => $levon,
        'net' => round($szamolt_netto),
        'tol' => $tol
      );

      if ($step >= 1000) {
        $running = false;
      }

      $fdiff = ($netto - $szamolt_netto);
      if ( abs($fdiff) < 1000 ) {
        $xn = 10;
      }
      if ( abs($fdiff) < 10 ) {
        $xn = 1;
      }

      if( $szamolt_netto > $netto ){
        $brutto -= $xn;
      }

      if ( $szamolt_netto < $netto ) {
        $brutto += $xn;
      }
      if ( round($szamolt_netto) == $netto ) {
        $running = false;
      }
    }

    unset($netto);unset($data);unset($settings);

    return array(
      'brutto' => $brutto,
      'values' => $calced
    );
  }
  
  /**
   * Megbízási díjhoz levonás számítások
   * @since 2021-02-08
   *
   * @param  mixed $brutto
   * @param  mixed $data
   * @param  mixed $settings
   * @return void
   */
  public function calcMegbizasiDijLevonasok( $brutto, $data, $settings )
  {
    $ret['brutto'] = $brutto;
    $teljes_koltseg = ($data['mode'] == 'teljes') ? true : false;
    $ret['teljes_koltseg'] = $teljes_koltseg;

    // Jövedelem
    $jovedelem = $brutto * ( (100 - $data['koltseghanyad']) / 100 );
    $jovedelem = ($jovedelem < 0) ? 0 : $jovedelem;
    $jovedelem = round($jovedelem);
    $ret['jovedelem'] = $jovedelem;

    // Napi jövedelem

    $napi_jovedelem = (float)$jovedelem / (int)$data['nap'];
    $napi_jovedelem = ($napi_jovedelem < 0) ? 0 : $napi_jovedelem;
    $napi_jovedelem = round($napi_jovedelem);
    $ret['napi_jovedelem'] = $napi_jovedelem;

    // Biztosítottság határa

    $biztonsitottsag_hatara = $settings['minimalber'] / 30 * 0.3;
    $ret['biztonsitottsag_hatara'] = $biztonsitottsag_hatara;

    // Biztosítottá válik

    if( $napi_jovedelem >= $biztonsitottsag_hatara )
    {
      $biztositotta_valik = 'Igen';
    }
    $ret['biztositotta_valik'] = $biztositotta_valik;

    // Szja

    $szja = $jovedelem * ($settings['ado_szja']/100);
    $szja = ($szja < 0) ? 0 : $szja;
    $szja = round($szja);
    $ret['ado_szja'] = $szja;

    // TB
    if( $biztositotta_valik == 'Igen' )
    {
      $tb = $jovedelem * ($settings['ado_tb']/100);
      $tb = ($tb < 0) ? 0 : $tb;
      $tb = round($tb);
      $ret['ado_tb'] = $tb;

      // Öregségi nyugdíjas @ 2021-02-08
      if ($data['oregsegi_nyugdijas'] == 'Igen') 
      {
        $tb = 0;
        $ret['ado_tb'] = $tb;
      }

    } else {          
      $tb = 0;
      $ret['ado_tb'] = $tb;
    }

    $ado_nav += $tb;
    $ado_nav += $szja;

    // Összes levonás bruttóból 
    $levonas_bruttobol = $tb + $szja;
    $ret['levonas_bruttobol'] = $levonas_bruttobol;

    // Nettó
    if( $data['mode'] == 'brutto' )
    {
      $netto = (float)$brutto - $levonas_bruttobol;
      $netto = ($netto < 0) ? 0 : $netto;
      $netto = round($netto);
    }

    // KIVA
    if( $data['ceg_kisvallalati_ado_alany'] == 'Igen' )
    {
      $kiva = $jovedelem * ($settings['ado_kisvallalati']/100);
      $kiva = ($kiva < 0) ? 0 : $kiva;
      $kiva = round($kiva);
      $ret['ado_kiva'] = $kiva;

      // Öregségi nyugdíjas @ 2021-02-08
      if ($data['oregsegi_nyugdijas'] == 'Igen') 
      {
      $kiva = 0;
      $ret['ado_kiva'] = $kiva;
      }

      $ado_raforditas = (float)$brutto + $kiva;
      $ado_nav += $kiva;
    } 
    else 
    {
      $ado_szocialis_hozzajarulas = $jovedelem * ($settings['ado_szocialis_hozzajarulas']/100);
      $ado_szocialis_hozzajarulas = ($ado_szocialis_hozzajarulas < 0) ? 0 : $ado_szocialis_hozzajarulas;
      $ado_szocialis_hozzajarulas = round($ado_szocialis_hozzajarulas);
      $ret['ado_szocialis_hozzajarulas'] = $ado_szocialis_hozzajarulas;

      $ado_szakkepzesi_hozzajarulas = $jovedelem * ($settings['ado_szakkepzesi_hozzajarulas']/100);
      $ado_szakkepzesi_hozzajarulas = ($ado_szakkepzesi_hozzajarulas < 0) ? 0 : $ado_szakkepzesi_hozzajarulas;
      $ado_szakkepzesi_hozzajarulas = round($ado_szakkepzesi_hozzajarulas);
      $ret['ado_szakkepzesi_hozzajarulas'] = $ado_szakkepzesi_hozzajarulas;

      // Öregségi nyugdíjas @ 2021-02-08
      if ($data['oregsegi_nyugdijas'] == 'Igen') 
      {
        $ado_szocialis_hozzajarulas = 0;
        $ret['ado_szocialis_hozzajarulas'] = $ado_szocialis_hozzajarulas;

        $ado_szakkepzesi_hozzajarulas = 0;
        $ret['ado_szakkepzesi_hozzajarulas'] = $ado_szakkepzesi_hozzajarulas;
      }

      $ado_raforditas = (float)$brutto + $ado_szocialis_hozzajarulas + $ado_szakkepzesi_hozzajarulas;
      $ado_nav += $ado_szocialis_hozzajarulas + $ado_szakkepzesi_hozzajarulas;
    }

    $fullreturn = [];
    
    if( $teljes_koltseg )
    {
      $osszes_nav = 0;
      $sum_minusbrutto = $tb + $szja;

      if( $data['ceg_kisvallalati_ado_alany'] == 'Igen' )
      {
        $osszes_nav = $tb + $szja + $kiva;
      } 
      else 
      {
        $osszes_nav = $tb + $szja + $ado_szocialis_hozzajarulas + $ado_szakkepzesi_hozzajarulas;
      }
      $fullreturn['osszes_nav'] = $osszes_nav;
      $fullreturn['teljes_koltseg'] = $osszes_nav + ($brutto - $sum_minusbrutto);
    }
    else 
    {
      $sum_minusbrutto = $tb + $szja;
    }

    $fullreturn['levonas'] = $sum_minusbrutto;
    $fullreturn['params'] = $ret;
    
    return $fullreturn;
  }

  public function findMegbizasiDijTeljes( $teljes, $data, $settings )
  {
    $original = $teljes;
    $running = true;
    $xn = 100000;
    $step = 0;
    $calced = array();
    $steps = [];

    while( $running )
    {
      $step++;

      $calced = $this->calcMegbizasiDijLevonasok($teljes, $data, $settings);
      $teljes_koltseg = $calced['teljes_koltseg'];

      if ($step >= 1000) {
        $running = false;
      }

      $fdiff = ( $original - $teljes_koltseg );
      $calced['fdiff'] = abs($fdiff);
      $calced['step'] = $step;

      if( abs($fdiff) < 100000 ) $xn = 10000;
      if( abs($fdiff) < 10000 ) $xn = 1000;
      if( abs($fdiff) < 1000 ) $xn = 100;
      if( abs($fdiff) < 100 ) $xn = 10;
      if( abs($fdiff) < 10 ) $xn = 1;

      if( $teljes_koltseg < $original ){
        $teljes += $xn;
      }

      if( $teljes_koltseg > $original )
      {
        $teljes -= $xn;
      }

      if ( round($teljes_koltseg) == $original ) {
        $running = false;
      }
      
      $steps[] = $calced;
    }
    
    return [
      'brutto' => $calced['params']['brutto'],
      'values' => $calced
    ];
  }

    /**
   * A megbízási díjhoz a célértékeresés alternatíva metódus
   * @since 2021-02-08
   *
   * @param  float $netto
   * @param  array $data
   * @param  array $settings
   * @return array
   */
  public function findMegbizasiDijBruttoFromNetto( $netto, $data, $settings )
  {
    $brutto = $netto * 1.5;
    $running = true;
    $step = 0;
    $szamolt_netto = 0;
    $xn = 1000;
    $calced = array();

    $steps = array();
    while( $running )
    {
      $step++;

      $calced = $this->calcMegbizasiDijLevonasok($brutto, $data, $settings);
      $levon = (float)$calced['levonas'];
      $szamolt_netto = $brutto - $levon;
      $tol = ($szamolt_netto / $netto) * 100;

      $steps[] = array(
        'br' => round($brutto),
        'le' => $levon,
        'net' => round($szamolt_netto),
        'tol' => $tol
      );

      if ($step >= 1000) {
        $running = false;
      }

      $fdiff = ($netto - $szamolt_netto);
      if ( abs($fdiff) < 1000 ) {
        $xn = 10;
      }
      if ( abs($fdiff) < 10 ) {
        $xn = 1;
      }

      if( $szamolt_netto > $netto ){
        $brutto -= $xn;
      }

      if ( $szamolt_netto < $netto ) {
        $brutto += $xn;
      }
      if ( round($szamolt_netto) == $netto ) {
        $running = false;
      }
    }

    unset($netto);unset($data);unset($settings);

    return array(
      'brutto' => $brutto,
      'values' => $calced
    );
  }

  public function calculate( $calc, $data )
  {
    switch ( $calc )
    {
      case 'berkalkulator':
        $ret = array(
          'jovedelem' => 0
        );
        $settings = $this->loadSettings( $calc );
        $mode = $data['mode'];

        $jovedelem = $data['jovedelem'];

        switch( $mode )
        {
          // Nettó bérkalkulátor
          case 'netto':
            $brutto_ber = $jovedelem;
          break;
          // Bruttó bérkalkulátor
          case 'brutto':
            $data['netto_ber'] = $jovedelem;
          break;
          // Teljes bérkalkulátor
          case 'teljes':
            $brutto_ber = $jovedelem;
          break;
        }

        $ret['jovedelem'] = $jovedelem;
        $ret['mode'] = $mode;

        if( $mode != 'brutto' )
        {
          $ret['brutto_ber'] = $brutto_ber;

          $csaladi_adokedvezmeny_osszege = 0;
          if ($data['csaladkedvezmenyre_jogosult'] == 'Igen') {
            $csaladi_adokedvezmeny_osszege = $this->csaladiAdokedvezmenyOsszege( (int)$data['csalad_eltartott_gyermek'], (int)$data['csalad_eltartott_gyermek_kedvezmenyezett'], $settings );
          }
  
          $anyak_gyerek4vagytobb = false;
          if ($data['anyak_4vagytobbgyermek'] == 'Igen') {
            $anyak_gyerek4vagytobb = true;
          }
  
          $ervenyesitheto_jarulekkedvezmeny = 0;
          $friss_hazasok_kedvezmeny = 0;
          $szemelyi_kedvezmeny = 0;
          $ervenyesitheto_termeszetbeni_kedvezmeny = 0;
          $ervenyesitheto_penzbeni_kedvezmeny = 0;
  
          if ($data['frisshazas_jogosult'] == 'Igen') {
            $friss_hazasok_kedvezmeny = $settings['adokedvezmeny_frisshazasok'];
          }
  
          if ($data['szemelyikedvezmeny_jogosult'] == 'Igen') {
            $szemelyi_kedvezmeny = $settings['minimalber'] / 3;
          }
  
          // Maradék családi kedvezmény alap
          if ( !$anyak_gyerek4vagytobb )
          {
            $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege+$friss_hazasok_kedvezmeny+$szemelyi_kedvezmeny-$brutto_ber;
            $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
          } else {
            $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege;
            $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
          }
  
          $ervenyesitheto_jarulekkedvezmeny = $csaladi_adokedvezmeny_maradekalap * 0.15;
          $ervenyesitheto_jarulekkedvezmeny = ($ervenyesitheto_jarulekkedvezmeny < 0) ? 0 : $ervenyesitheto_jarulekkedvezmeny;
  
          $ervenyesitheto_termeszetbeni_kedvezmeny = $ervenyesitheto_jarulekkedvezmeny - ($brutto_ber * ($settings['ado_termeszetegeszseg']/100));
          $ervenyesitheto_termeszetbeni_kedvezmeny = ($ervenyesitheto_termeszetbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_termeszetbeni_kedvezmeny;
  
          //$ervenyesitheto_penzbeni_kedvezmeny = $ervenyesitheto_termeszetbeni_kedvezmeny - ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100));
          //$ervenyesitheto_penzbeni_kedvezmeny = ($ervenyesitheto_penzbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_penzbeni_kedvezmeny;
  
          if ( !$anyak_gyerek4vagytobb ) {
            $ret['ado_szja'] = (($brutto_ber-$friss_hazasok_kedvezmeny-$csaladi_adokedvezmeny_osszege-$szemelyi_kedvezmeny) * ($settings['ado_szja']/100));
            $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
            $ret['ado_szja'] = round($ret['ado_szja']);
          } else {
            $ret['ado_szja'] = 0;
            $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
            $ret['ado_szja'] = round($ret['ado_szja']);
          }
  
          // TB járulék számítás
          $ret['ado_tb'] = ($brutto_ber * ($settings['ado_tb']/100)) - $ervenyesitheto_jarulekkedvezmeny;;
          $ret['ado_tb'] = ($ret['ado_tb'] < 0) ? 0 : $ret['ado_tb'];
          $ret['ado_tb'] = round($ret['ado_tb']);
  
          // Öregségi nyugdíjas @ 2021-02-08
          if ($data['oregsegi_nyugdijas'] == 'Igen') {
            $ret['ado_tb'] = 0;
          }
  
          $ret['ado_termeszetegeszseg'] = ($brutto_ber * ($settings['ado_termeszetegeszseg']/100)) - $ervenyesitheto_jarulekkedvezmeny;
          $ret['ado_termeszetegeszseg'] = ($ret['ado_termeszetegeszseg'] < 0) ? 0 : $ret['ado_termeszetegeszseg'];
          $ret['ado_termeszetegeszseg'] = round($ret['ado_termeszetegeszseg']);
  
          $ret['ado_penzbeli_egeszseg'] = ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100)) - $ervenyesitheto_termeszetbeni_kedvezmeny;
          $ret['ado_penzbeli_egeszseg'] = ($ret['ado_penzbeli_egeszseg'] < 0) ? 0 : $ret['ado_penzbeli_egeszseg'];
          $ret['ado_penzbeli_egeszseg'] = round($ret['ado_penzbeli_egeszseg']);
  
          //$ret['ado_nyugdij'] = ($brutto_ber * ($settings['ado_nyugdij']/100)) - $ervenyesitheto_penzbeni_kedvezmeny;
          //$ret['ado_nyugdij'] = ($ret['ado_nyugdij'] < 0) ? 0 : $ret['ado_nyugdij'];
          //$ret['ado_nyugdij'] = round($ret['ado_nyugdij']);
  
          //$ret['ado_munkaerppiac'] = $brutto_ber * ($settings['ado_munkaerppiac']/100);
          //$ret['ado_munkaerppiac'] = round($ret['ado_munkaerppiac']);
  
          //$sum_minusbrutto = $ret['ado_szja'] + $ret['ado_termeszetegeszseg'] + $ret['ado_penzbeli_egeszseg'] + $ret['ado_nyugdij'] + $ret['ado_munkaerppiac'];
          $sum_minusbrutto = $ret['ado_szja'] + $ret['ado_tb'];
  
          $ret['sum_minusbrutto'] = $sum_minusbrutto;
          $netto_ber = $brutto_ber-$sum_minusbrutto;
          $ret['netto_ber'] = $netto_ber;
  
          $values['csaladi_adokedvezmeny_osszege'] = $csaladi_adokedvezmeny_osszege;
          $values['frisshazas_jogosult'] = $friss_hazasok_kedvezmeny;
          $values['szemelyikedvezmeny_jogosult'] = $szemelyi_kedvezmeny;
          $values['csaladi_adokedvezmeny_maradekalap'] = $csaladi_adokedvezmeny_maradekalap;
          $values['ervenyesitheto_jarulekkedvezmeny'] = $ervenyesitheto_jarulekkedvezmeny;
          //$values['ervenyesitheto_termeszetbeni_kedvezmeny'] = $ervenyesitheto_termeszetbeni_kedvezmeny;
          //$values['ervenyesitheto_penzbeni_kedvezmeny'] = $ervenyesitheto_penzbeni_kedvezmeny;
  
          // Nettó bér alap vége
  
          /****************************
           * Teljes bérköltség számítás
           ***************************/ 
          
          $mk = $settings['forms']['munkavallalo_kedvezmenyek'];
          $minimalber = $settings['minimalber'];
          $minimalber_ketszeres = $minimalber * 2;
          $szocho_es_kiva_kedvezmeny_alap = 0;
          $szokho_kedvezmeny_alap = 0;
  
          $sel_mk = (int)$data['munkavallalo_kedvezmeny'];
          $mk_obj = $mk[$sel_mk];
  
          // szocho kiva alapszámítás
          $kedvezmeny_mertek = $mk_obj['calc']['szochokiva']['kedvezmeny_mertek'];
          $kedvezmeny_max = $mk_obj['calc']['szochokiva']['kedvezmeny_max'];
          $kedvezmeny_max = (float)str_replace(array('{minimalber}', '{minimalber_ketszeres}'), array($minimalber, $minimalber_ketszeres), $kedvezmeny_max);
  
          $values['szochokiva_kedvezmeny_mertek'] = $kedvezmeny_mertek;
          $values['szochokiva_kedvezmeny_max'] = $kedvezmeny_max;
  
          if ( $kedvezmeny_mertek > 0 ) {
            if ($kedvezmeny_max > 0) {
              $szocho_es_kiva_kedvezmeny_alap_row = array();
              $szocho_es_kiva_kedvezmeny_alap_row[] = $brutto_ber * ($kedvezmeny_mertek/100);
              $szocho_es_kiva_kedvezmeny_alap_row[] = $kedvezmeny_max * ($kedvezmeny_mertek/100);
              $szocho_es_kiva_kedvezmeny_alap = min($szocho_es_kiva_kedvezmeny_alap_row);
            } else {
              $szocho_es_kiva_kedvezmeny_alap = $brutto_ber * ($kedvezmeny_mertek/100);
            }
          }
  
          // szokho alapszámítás
          $kedvezmeny_mertek = $mk_obj['calc']['szokho']['kedvezmeny_mertek'];
          $kedvezmeny_max = $mk_obj['calc']['szokho']['kedvezmeny_max'];
          $kedvezmeny_max = (float)str_replace(array('{minimalber}', '{minimalber_ketszeres}'), array($minimalber, $minimalber_ketszeres), $kedvezmeny_max);
  
          $values['szokho_kedvezmeny_mertek'] = $kedvezmeny_mertek;
          $values['szokho_kedvezmeny_max'] = $kedvezmeny_max;
  
          if ( $kedvezmeny_mertek > 0 ) {
            if ($kedvezmeny_max > 0) {
              $szokho_kedvezmeny_alap_row = array();
              $szokho_kedvezmeny_alap_row[] = $brutto_ber * ($kedvezmeny_mertek/100);
              $szokho_kedvezmeny_alap_row[] = $kedvezmeny_max * ($kedvezmeny_mertek/100);
              $szokho_kedvezmeny_alap = min($szokho_kedvezmeny_alap_row);
            } else {
              $szokho_kedvezmeny_alap = $brutto_ber * ($kedvezmeny_mertek/100);
            }
          }
  
          $ret['ado_szocialis_hozzajarulas'] = ($brutto_ber - $szocho_es_kiva_kedvezmeny_alap) * ($settings['ado_szocialis_hozzajarulas']/100);
          $ret['ado_szocialis_hozzajarulas'] = round($ret['ado_szocialis_hozzajarulas']);
  
          // Öregségi nyugdíjas @ 2021-02-08
          if ($data['oregsegi_nyugdijas'] == 'Igen') {
            $ret['ado_szocialis_hozzajarulas'] = 0;
          }
  
          $ret['ado_szakkepzesi_hozzajarulas'] = ($brutto_ber - $szokho_kedvezmeny_alap) * ($settings['ado_szakkepzesi_hozzajarulas']/100);
          $ret['ado_szakkepzesi_hozzajarulas'] = round($ret['ado_szakkepzesi_hozzajarulas']);
  
          // Öregségi nyugdíjas @ 2021-02-08
          if ($data['oregsegi_nyugdijas'] == 'Igen') {
            $ret['ado_szakkepzesi_hozzajarulas'] = 0;
          }
  
          $ret['ado_kisvallalati'] = ($brutto_ber - $szocho_es_kiva_kedvezmeny_alap) * ($settings['ado_kisvallalati']/100);
          $ret['ado_kisvallalati'] = round($ret['ado_kisvallalati']);
  
          // Öregségi nyugdíjas @ 2021-02-08
          if ($data['oregsegi_nyugdijas'] == 'Igen') {
            $ret['ado_kisvallalati'] = 0;
          }
  
          $ret['berkoltseg_nem_KIVA'] = $brutto_ber + $ret['ado_szocialis_hozzajarulas'] + $ret['ado_szakkepzesi_hozzajarulas'];
          $ret['berkoltseg_KIVA'] = $brutto_ber + $ret['ado_kisvallalati'];
  
          $nav_osszes_ado = 0;
  
          if ($data['ceg_kisvallalati_ado_alany'] == 'Igen') {
            $nav_osszes_ado = $ret['berkoltseg_KIVA'] - $netto_ber;
          } else {
            $nav_osszes_ado = $ret['berkoltseg_nem_KIVA'] - $netto_ber;
          }
  
          $ret['nav_osszes_ado'] = $nav_osszes_ado;
  
          $values['szocho_es_kiva_kedvezmeny_alap'] = $szocho_es_kiva_kedvezmeny_alap;
          $values['szokho_kedvezmeny_alap'] = $szokho_kedvezmeny_alap;
          $values['kiva_adoalany'] = $data['ceg_kisvallalati_ado_alany'];
        } 
        else 
        {
          $netto = $data['netto_ber'];
          $ret['netto_ber'] = $netto;

          $find = $this->findBruttoFromNetto( $netto, $data, $settings, $values );
          $sum_minusbrutto = (int)$find['brutto'];
          $values['find'] = $find;

          $ret['sum_minusbrutto'] = $find['values']['levonas'];
          $ret['brutto_ber'] = $netto_ber + $sum_minusbrutto;

          $ret['ado_szja'] = $find['values']['params']['ado_szja'];
          $ret['ado_tb'] = $find['values']['params']['ado_tb'];
        }
        
        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      /** Deprecate @ 2021-02-08 */
      case 'brutto_ber':
        $ret = array(
          'netto_ber' => 0
        );
        $settings = $this->loadSettings( $calc );

        $netto = $data['netto_ber'];
        $ret['netto_ber'] = $netto;

        $find = $this->findBruttoFromNetto( $netto, $data, $settings, $values );
        $sum_minusbrutto = (int)$find['brutto'];
        $values['find'] = $find;

        $ret['sum_minusbrutto'] = $find['values']['levonas'];
        $ret['brutto_ber'] = $netto_ber + $sum_minusbrutto;

        $ret['ado_szja'] = $find['values']['params']['ado_szja'];
        $ret['ado_tb'] = $find['values']['params']['ado_tb'];
        /*
        $ret['ado_termeszetegeszseg'] = $find['values']['params']['ado_termeszetegeszseg'];
        $ret['ado_penzbeli_egeszseg'] = $find['values']['params']['ado_penzbeli_egeszseg'];
        $ret['ado_nyugdij'] = $find['values']['params']['ado_nyugdij'];
        $ret['ado_munkaerppiac'] = $find['values']['params']['ado_munkaerppiac'];
        */

        // Nettó bér alap vége

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      /** Deprecate @ 2021-02-08 */
      case 'teljes_berkoltseg':
        $ret = array(
          'brutto_ber' => 0
        );
        $settings = $this->loadSettings( $calc );

        $brutto_ber = $data['brutto_ber'];

        $ret['brutto_ber'] = $brutto_ber;

        $csaladi_adokedvezmeny_osszege = 0;
        if ($data['csaladkedvezmenyre_jogosult'] == 'Igen') {
          $csaladi_adokedvezmeny_osszege = $this->csaladiAdokedvezmenyOsszege( (int)$data['csalad_eltartott_gyermek'], (int)$data['csalad_eltartott_gyermek_kedvezmenyezett'], $settings );
        }

        $anyak_gyerek4vagytobb = false;
        if ($data['anyak_4vagytobbgyermek'] == 'Igen') {
          $anyak_gyerek4vagytobb = true;
        }

        $ervenyesitheto_jarulekkedvezmeny = 0;
        $friss_hazasok_kedvezmeny = 0;
        $szemelyi_kedvezmeny = 0;
        $ervenyesitheto_termeszetbeni_kedvezmeny = 0;
        $ervenyesitheto_penzbeni_kedvezmeny = 0;

        if ($data['frisshazas_jogosult'] == 'Igen') {
          $friss_hazasok_kedvezmeny = $settings['adokedvezmeny_frisshazasok'];
        }

        if ($data['szemelyikedvezmeny_jogosult'] == 'Igen') {
          $szemelyi_kedvezmeny = $settings['minimalber'] / 3;
        }

        // Maradék családi kedvezmény alap
        if ( !$anyak_gyerek4vagytobb )
        {
          $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege+$friss_hazasok_kedvezmeny+$szemelyi_kedvezmeny-$brutto_ber;
          $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
        } else {
          $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege;
          $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
        }

        $ervenyesitheto_jarulekkedvezmeny = $csaladi_adokedvezmeny_maradekalap * 0.15;
        $ervenyesitheto_jarulekkedvezmeny = ($ervenyesitheto_jarulekkedvezmeny < 0) ? 0 : $ervenyesitheto_jarulekkedvezmeny;

        $ervenyesitheto_termeszetbeni_kedvezmeny = $ervenyesitheto_jarulekkedvezmeny - ($brutto_ber * ($settings['ado_termeszetegeszseg']/100));
        $ervenyesitheto_termeszetbeni_kedvezmeny = ($ervenyesitheto_termeszetbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_termeszetbeni_kedvezmeny;

        //$ervenyesitheto_penzbeni_kedvezmeny = $ervenyesitheto_termeszetbeni_kedvezmeny - ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100));
        //$ervenyesitheto_penzbeni_kedvezmeny = ($ervenyesitheto_penzbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_penzbeni_kedvezmeny;

        if ( !$anyak_gyerek4vagytobb ) {
          $ret['ado_szja'] = (($brutto_ber-$friss_hazasok_kedvezmeny-$csaladi_adokedvezmeny_osszege) * ($settings['ado_szja']/100)) - $szemelyi_kedvezmeny;
          $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
          $ret['ado_szja'] = round($ret['ado_szja']);
        } else {
          $ret['ado_szja'] = 0;
          $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
          $ret['ado_szja'] = round($ret['ado_szja']);
        }

        // TB járulék számítás
        $ret['ado_tb'] = ($brutto_ber * ($settings['ado_tb']/100)) - $ervenyesitheto_jarulekkedvezmeny;;
        $ret['ado_tb'] = ($ret['ado_tb'] < 0) ? 0 : $ret['ado_tb'];
        $ret['ado_tb'] = round($ret['ado_tb']);

        // Öregségi nyugdíjas @ 2021-02-08
        if ($data['oregsegi_nyugdijas'] == 'Igen') {
          $ret['ado_tb'] = 0;
        }

        $ret['ado_termeszetegeszseg'] = ($brutto_ber * ($settings['ado_termeszetegeszseg']/100)) - $ervenyesitheto_jarulekkedvezmeny;
        $ret['ado_termeszetegeszseg'] = ($ret['ado_termeszetegeszseg'] < 0) ? 0 : $ret['ado_termeszetegeszseg'];
        $ret['ado_termeszetegeszseg'] = round($ret['ado_termeszetegeszseg']);

        $ret['ado_penzbeli_egeszseg'] = ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100)) - $ervenyesitheto_termeszetbeni_kedvezmeny;
        $ret['ado_penzbeli_egeszseg'] = ($ret['ado_penzbeli_egeszseg'] < 0) ? 0 : $ret['ado_penzbeli_egeszseg'];
        $ret['ado_penzbeli_egeszseg'] = round($ret['ado_penzbeli_egeszseg']);

        //$ret['ado_nyugdij'] = ($brutto_ber * ($settings['ado_nyugdij']/100)) - $ervenyesitheto_penzbeni_kedvezmeny;
        //$ret['ado_nyugdij'] = ($ret['ado_nyugdij'] < 0) ? 0 : $ret['ado_nyugdij'];
        //$ret['ado_nyugdij'] = round($ret['ado_nyugdij']);

        //$ret['ado_munkaerppiac'] = $brutto_ber * ($settings['ado_munkaerppiac']/100);
        //$ret['ado_munkaerppiac'] = round($ret['ado_munkaerppiac']);

        //$sum_minusbrutto = $ret['ado_szja'] + $ret['ado_termeszetegeszseg'] + $ret['ado_penzbeli_egeszseg'] + $ret['ado_nyugdij'] + $ret['ado_munkaerppiac'];
        $sum_minusbrutto = $ret['ado_szja'] + $ret['ado_tb'];

        $ret['sum_minusbrutto'] = $sum_minusbrutto;
        $netto_ber = $brutto_ber-$sum_minusbrutto;
        $ret['netto_ber'] = $netto_ber;

        $values['csaladi_adokedvezmeny_osszege'] = $csaladi_adokedvezmeny_osszege;
        $values['frisshazas_jogosult'] = $friss_hazasok_kedvezmeny;
        $values['szemelyikedvezmeny_jogosult'] = $szemelyi_kedvezmeny;
        $values['csaladi_adokedvezmeny_maradekalap'] = $csaladi_adokedvezmeny_maradekalap;
        $values['ervenyesitheto_jarulekkedvezmeny'] = $ervenyesitheto_jarulekkedvezmeny;
        //$values['ervenyesitheto_termeszetbeni_kedvezmeny'] = $ervenyesitheto_termeszetbeni_kedvezmeny;
        //$values['ervenyesitheto_penzbeni_kedvezmeny'] = $ervenyesitheto_penzbeni_kedvezmeny;

        // Nettó bér alap vége

        // Teljes bérköltség számítások
        $mk = $settings['forms']['munkavallalo_kedvezmenyek'];
        $minimalber = $settings['minimalber'];
        $minimalber_ketszeres = $minimalber * 2;
        $szocho_es_kiva_kedvezmeny_alap = 0;
        $szokho_kedvezmeny_alap = 0;

        $sel_mk = (int)$data['munkavallalo_kedvezmeny'];
        $mk_obj = $mk[$sel_mk];

        // szocho kiva alapszámítás
        $kedvezmeny_mertek = $mk_obj['calc']['szochokiva']['kedvezmeny_mertek'];
        $kedvezmeny_max = $mk_obj['calc']['szochokiva']['kedvezmeny_max'];
        $kedvezmeny_max = (float)str_replace(array('{minimalber}', '{minimalber_ketszeres}'), array($minimalber, $minimalber_ketszeres), $kedvezmeny_max);

        $values['szochokiva_kedvezmeny_mertek'] = $kedvezmeny_mertek;
        $values['szochokiva_kedvezmeny_max'] = $kedvezmeny_max;

        if ( $kedvezmeny_mertek > 0 ) {
          if ($kedvezmeny_max > 0) {
            $szocho_es_kiva_kedvezmeny_alap_row = array();
            $szocho_es_kiva_kedvezmeny_alap_row[] = $brutto_ber * ($kedvezmeny_mertek/100);
            $szocho_es_kiva_kedvezmeny_alap_row[] = $kedvezmeny_max * ($kedvezmeny_mertek/100);
            $szocho_es_kiva_kedvezmeny_alap = min($szocho_es_kiva_kedvezmeny_alap_row);
          } else {
            $szocho_es_kiva_kedvezmeny_alap = $brutto_ber * ($kedvezmeny_mertek/100);
          }
        }

        // szokho alapszámítás
        $kedvezmeny_mertek = $mk_obj['calc']['szokho']['kedvezmeny_mertek'];
        $kedvezmeny_max = $mk_obj['calc']['szokho']['kedvezmeny_max'];
        $kedvezmeny_max = (float)str_replace(array('{minimalber}', '{minimalber_ketszeres}'), array($minimalber, $minimalber_ketszeres), $kedvezmeny_max);

        $values['szokho_kedvezmeny_mertek'] = $kedvezmeny_mertek;
        $values['szokho_kedvezmeny_max'] = $kedvezmeny_max;

        if ( $kedvezmeny_mertek > 0 ) {
          if ($kedvezmeny_max > 0) {
            $szokho_kedvezmeny_alap_row = array();
            $szokho_kedvezmeny_alap_row[] = $brutto_ber * ($kedvezmeny_mertek/100);
            $szokho_kedvezmeny_alap_row[] = $kedvezmeny_max * ($kedvezmeny_mertek/100);
            $szokho_kedvezmeny_alap = min($szokho_kedvezmeny_alap_row);
          } else {
            $szokho_kedvezmeny_alap = $brutto_ber * ($kedvezmeny_mertek/100);
          }
        }

        $ret['ado_szocialis_hozzajarulas'] = ($brutto_ber - $szocho_es_kiva_kedvezmeny_alap) * ($settings['ado_szocialis_hozzajarulas']/100);
        $ret['ado_szocialis_hozzajarulas'] = round($ret['ado_szocialis_hozzajarulas']);

        // Öregségi nyugdíjas @ 2021-02-08
        if ($data['oregsegi_nyugdijas'] == 'Igen') {
          $ret['ado_szocialis_hozzajarulas'] = 0;
        }

        $ret['ado_szakkepzesi_hozzajarulas'] = ($brutto_ber - $szokho_kedvezmeny_alap) * ($settings['ado_szakkepzesi_hozzajarulas']/100);
        $ret['ado_szakkepzesi_hozzajarulas'] = round($ret['ado_szakkepzesi_hozzajarulas']);

        // Öregségi nyugdíjas @ 2021-02-08
        if ($data['oregsegi_nyugdijas'] == 'Igen') {
          $ret['ado_szakkepzesi_hozzajarulas'] = 0;
        }

        $ret['ado_kisvallalati'] = ($brutto_ber - $szocho_es_kiva_kedvezmeny_alap) * ($settings['ado_kisvallalati']/100);
        $ret['ado_kisvallalati'] = round($ret['ado_kisvallalati']);

        // Öregségi nyugdíjas @ 2021-02-08
        if ($data['oregsegi_nyugdijas'] == 'Igen') {
          $ret['ado_kisvallalati'] = 0;
        }

        $ret['berkoltseg_nem_KIVA'] = $brutto_ber + $ret['ado_szocialis_hozzajarulas'] + $ret['ado_szakkepzesi_hozzajarulas'];
        $ret['berkoltseg_KIVA'] = $brutto_ber + $ret['ado_kisvallalati'];

        $nav_osszes_ado = 0;

        if ($data['ceg_kisvallalati_ado_alany'] == 'Igen') {
          $nav_osszes_ado = $ret['berkoltseg_KIVA'] - $netto_ber;
        } else {
          $nav_osszes_ado = $ret['berkoltseg_nem_KIVA'] - $netto_ber;
        }

        $ret['nav_osszes_ado'] = $nav_osszes_ado;

        $values['szocho_es_kiva_kedvezmeny_alap'] = $szocho_es_kiva_kedvezmeny_alap;
        $values['szokho_kedvezmeny_alap'] = $szokho_kedvezmeny_alap;
        $values['kiva_adoalany'] = $data['ceg_kisvallalati_ado_alany'];

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      /** Deprecate @ 2021-02-08 */
      case 'netto_ber':
        $ret = array(
          'brutto_ber' => 0
        );
        $settings = $this->loadSettings( $calc );

        $brutto_ber = $data['brutto_ber'];

        $ret['brutto_ber'] = $brutto_ber;

        $csaladi_adokedvezmeny_osszege = 0;
        if ($data['csaladkedvezmenyre_jogosult'] == 'Igen') {
          $csaladi_adokedvezmeny_osszege = $this->csaladiAdokedvezmenyOsszege( (int)$data['csalad_eltartott_gyermek'], (int)$data['csalad_eltartott_gyermek_kedvezmenyezett'], $settings );
        }

        $anyak_gyerek4vagytobb = false;
        if ($data['anyak_4vagytobbgyermek'] == 'Igen') {
          $anyak_gyerek4vagytobb = true;
        }

        $ervenyesitheto_jarulekkedvezmeny = 0;
        $friss_hazasok_kedvezmeny = 0;
        $szemelyi_kedvezmeny = 0;
        $ervenyesitheto_termeszetbeni_kedvezmeny = 0;
        $ervenyesitheto_penzbeni_kedvezmeny = 0;

        if ($data['frisshazas_jogosult'] == 'Igen') {
          $friss_hazasok_kedvezmeny = $settings['adokedvezmeny_frisshazasok'];
        }

        if ($data['szemelyikedvezmeny_jogosult'] == 'Igen') {
          $szemelyi_kedvezmeny = $settings['adokedvezmeny_szemelyi'];
        }

        if ( !$anyak_gyerek4vagytobb )
        {
          $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege+$friss_hazasok_kedvezmeny-$brutto_ber;
          $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
        } else {
          $csaladi_adokedvezmeny_maradekalap = $csaladi_adokedvezmeny_osszege;
          $csaladi_adokedvezmeny_maradekalap = ($csaladi_adokedvezmeny_maradekalap < 0) ? 0 : $csaladi_adokedvezmeny_maradekalap;
        }

        $ervenyesitheto_jarulekkedvezmeny = $csaladi_adokedvezmeny_maradekalap * 0.15;
        $ervenyesitheto_jarulekkedvezmeny = ($ervenyesitheto_jarulekkedvezmeny < 0) ? 0 : $ervenyesitheto_jarulekkedvezmeny;

        $ervenyesitheto_termeszetbeni_kedvezmeny = $ervenyesitheto_jarulekkedvezmeny - ($brutto_ber * ($settings['ado_termeszetegeszseg']/100));
        $ervenyesitheto_termeszetbeni_kedvezmeny = ($ervenyesitheto_termeszetbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_termeszetbeni_kedvezmeny;

        //$ervenyesitheto_penzbeni_kedvezmeny = $ervenyesitheto_termeszetbeni_kedvezmeny - ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100));
        //$ervenyesitheto_penzbeni_kedvezmeny = ($ervenyesitheto_penzbeni_kedvezmeny < 0) ? 0 : $ervenyesitheto_penzbeni_kedvezmeny;

        if ( !$anyak_gyerek4vagytobb ) {
          $ret['ado_szja'] = (($brutto_ber-$friss_hazasok_kedvezmeny-$csaladi_adokedvezmeny_osszege) * ($settings['ado_szja']/100)) - $szemelyi_kedvezmeny;
          $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
          $ret['ado_szja'] = round($ret['ado_szja']);
        } else {
          $ret['ado_szja'] = 0;
          $ret['ado_szja'] = ($ret['ado_szja'] < 0) ? 0 : $ret['ado_szja'];
          $ret['ado_szja'] = round($ret['ado_szja']);
        }

        // TB járulék számítás
        $ret['ado_tb'] = ($brutto_ber * ($settings['ado_tb']/100)) - $ervenyesitheto_jarulekkedvezmeny;;
        $ret['ado_tb'] = ($ret['ado_tb'] < 0) ? 0 : $ret['ado_tb'];
        $ret['ado_tb'] = round($ret['ado_tb']);

        $ret['ado_termeszetegeszseg'] = ($brutto_ber * ($settings['ado_termeszetegeszseg']/100)) - $ervenyesitheto_jarulekkedvezmeny;
        $ret['ado_termeszetegeszseg'] = ($ret['ado_termeszetegeszseg'] < 0) ? 0 : $ret['ado_termeszetegeszseg'];
        $ret['ado_termeszetegeszseg'] = round($ret['ado_termeszetegeszseg']);

        $ret['ado_penzbeli_egeszseg'] = ($brutto_ber * ($settings['ado_penzbeli_egeszseg']/100)) - $ervenyesitheto_termeszetbeni_kedvezmeny;
        $ret['ado_penzbeli_egeszseg'] = ($ret['ado_penzbeli_egeszseg'] < 0) ? 0 : $ret['ado_penzbeli_egeszseg'];
        $ret['ado_penzbeli_egeszseg'] = round($ret['ado_penzbeli_egeszseg']);

        //$ret['ado_nyugdij'] = ($brutto_ber * ($settings['ado_nyugdij']/100)) - $ervenyesitheto_penzbeni_kedvezmeny;
        //$ret['ado_nyugdij'] = ($ret['ado_nyugdij'] < 0) ? 0 : $ret['ado_nyugdij'];
        //$ret['ado_nyugdij'] = round($ret['ado_nyugdij']);

        //$ret['ado_munkaerppiac'] = $brutto_ber * ($settings['ado_munkaerppiac']/100);
        //$ret['ado_munkaerppiac'] = round($ret['ado_munkaerppiac']);

        //$sum_minusbrutto = $ret['ado_szja'] + $ret['ado_termeszetegeszseg'] + $ret['ado_penzbeli_egeszseg'] + $ret['ado_nyugdij'] + $ret['ado_munkaerppiac'];
        $sum_minusbrutto = $ret['ado_szja'] + $ret['ado_tb'];

        $ret['sum_minusbrutto'] = $sum_minusbrutto;
        $netto_ber = $brutto_ber-$sum_minusbrutto;
        $ret['netto_ber'] = $netto_ber;

        $values['csaladi_adokedvezmeny_osszege'] = $csaladi_adokedvezmeny_osszege;
        $values['frisshazas_jogosult'] = $friss_hazasok_kedvezmeny;
        $values['szemelyikedvezmeny_jogosult'] = $szemelyi_kedvezmeny;
        $values['csaladi_adokedvezmeny_maradekalap'] = $csaladi_adokedvezmeny_maradekalap;
        $values['ervenyesitheto_jarulekkedvezmeny'] = $ervenyesitheto_jarulekkedvezmeny;
        //$values['ervenyesitheto_termeszetbeni_kedvezmeny'] = $ervenyesitheto_termeszetbeni_kedvezmeny;
        //$values['ervenyesitheto_penzbeni_kedvezmeny'] = $ervenyesitheto_penzbeni_kedvezmeny;

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'belepo_szabadsag':
        $ret = array(
          'szabadsag_eves' => 0,
          'szabadsag_idoaranyos' => 0,
          'betegszabadsag_eves' => 0,
          'betegszabadsag_idoaranyos' => 0
        );
        $values = array();
        $settings = $this->loadSettings( $calc );
        $ret['betegszabadsag_eves'] = $settings['betegszabadsag'];
        $targyev = (int)date('Y');
        $ev_elso_napja = date('Y').'-01-01';
        $ev_utolso_napja = date('Y-m-d', strtotime('last day of december this year'));
        $szamitas_kezdete = $ev_elso_napja;

        if ($data['iden_kezdett_dolgozni'] == 'Igen') {
          $szamitas_kezdete = date('Y-m-d', strtotime($data['belepes_datuma']));
        }

        // pre calc
        $munkavallalo_kora = $targyev - $data['szuletesi_ev'];
        $ev_vegeig_hatralevo_napok = round((strtotime($ev_utolso_napja) - strtotime($szamitas_kezdete)) / (60 * 60 * 24))+1;
        $ev_naptari_napok = round((strtotime($ev_utolso_napja) - strtotime($ev_elso_napja)) / (60 * 60 * 24))+1;
        $kor_potszabi = $this->potszabadasgKorSzerint($munkavallalo_kora);
        $gyerek16fiatalabb_potszabi = $this->potszabadasg16evfiatalabbGyerekSzerint((int)$data['gyerek16ev_fiatalabb']);

        // potszabi_ha16evnelfiatalabbgyereketnevel
        // megvaltozott_munkakepessegu

        $szabadsag_eves = (int)$data['athozott_szabadsagok'] + $settings['alapszabadsag'] + $kor_potszabi + $gyerek16fiatalabb_potszabi;

        if ($data['gyerek16ev_fiatalabb_fogyatekos'] == 'Igen') {
          $szabadsag_eves += $settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
          $values['ha16evnelfiatalabbgyereketnevel_potszabi'] = $settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
        }

        if ($data['megvaltozott_munkakepessegu'] == 'Igen') {
          $szabadsag_eves += $settings['potszabi_megvaltozott_munkakepessegu'];
          $values['megvaltozott_munkakepessegu_potszabi'] = $settings['potszabi_megvaltozott_munkakepessegu'];
        }

        $ret['szabadsag_eves'] = $szabadsag_eves;
        $szabadsag_idoaranyos = $szabadsag_eves/$ev_naptari_napok*$ev_vegeig_hatralevo_napok;
        $ret['szabadsag_idoaranyos'] = round($szabadsag_idoaranyos);

        $betegszabadsag_eves = $settings['betegszabadsag'];
        $betegszabadsag_idoaranyos = $betegszabadsag_eves/$ev_naptari_napok*$ev_vegeig_hatralevo_napok;

        $ret['betegszabadsag_eves'] = $betegszabadsag_eves;
        $ret['betegszabadsag_idoaranyos'] = round($betegszabadsag_idoaranyos);


        $values['targyev'] = $targyev;
        $values['szamitas_kezdete'] = $szamitas_kezdete;
        $values['ev_utolso_napja'] = $ev_utolso_napja;
        $values['munkavallalo_kora'] = $munkavallalo_kora;
        $values['ev_vegeig_hatralevo_napok'] = $ev_vegeig_hatralevo_napok;
        $values['ev_naptari_napok'] = $ev_naptari_napok;
        $values['kor_potszabi'] = $kor_potszabi;
        $values['gyerek16fiatalabb_potszabi'] = $gyerek16fiatalabb_potszabi;

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;

      case 'cegauto_ado':
        return $this->calcCegautoAdo( $data['emission'], (float)$data['kw'] );
      break;

      case 'ingatlan_ertekesites':
        $ret = array();
        $settings = $this->loadSettings( $calc );
        $ret['settings'] = $settings;

        $bevetel = (float)$data['atruhazasbol_bevetel'];
        // C8+C9+(C10-C11)+C12
        $koltseg = $data['megszerzes_osszeg'] + $data['megszerzes_egyeb_kiadas'] + ($data['erteknovelo_beruhazasok'] - $data['erteknovelo_beruhazasok_allammegovas']) + $data['atruhazas_koltsegei'];
        // (C14-C15)*FKERES(C5;F5:G10;2;IGAZ)
        $szorzo = $this->ingatlan_ertekesites_jovedelem_szorzo((int)$data['szerzes_eve'], (int)$data['atruhazas_eve']);
        $jovedelem = ($bevetel - $koltseg) * $szorzo;
        $fizetendo_szja = 0;

        $fizetendo_szja = $jovedelem * ($settings['ado_szja']/100);
        $fizetendo_szja = ($fizetendo_szja < 0) ? 0 : $fizetendo_szja;
        $fizetendo_szja = round($fizetendo_szja);

        $ret['bevetel'] = $bevetel;
        $ret['koltseg'] = $koltseg;
        $ret['jovedelem'] = $jovedelem;
        $ret['fizetendo_szja'] = $fizetendo_szja;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'osztalekado':
        $ret = array();
        $settings = $this->loadSettings( $calc );
        $ret['settings'] = $settings;
           /**
         * Manuális minimálbér korrekció
         * @since 2021-02-22
         * 
         * Gyursó Kitti:
         * A NAV utólag módosította az osztalék esetén a SZOCHO maximumot. Ugyan úgy a 161.000 Ft-os minimálbérrel kell számolni, így az nem változott 167.400 Ft-ra.
         */
        // $szocho_ado_max = $settings['minimalber'] * 24;
        $szocho_ado_max = 161000 * 24;
        
        

        $osszes_jovedelem = 0;
        $fizetendo_szja = 0;
        $fizetendo_szocho = 0;

        $osszes_jovedelem =
        (float)$data['teljes_targyev_brutto_munkaber'] +
        (float)$data['teljes_targyev_brutto_tarasvall_kivet'] +
        (float)$data['targyev_megszerzett_brutto_osztalek'] +
        (float)$data['targyev_vall_kivont_jovedelem'] + 
        (float)$data['targyev_ertekpapkolcson_jovedelem'] +
        (float)$data['targyev_arfolyamnyereseg_jovedelem'] +
        (float)$data['egyeb_szja_jovedelem'];

        $fizetendo_szja = (float)$data['brutto_alap'] * ($settings['ado_szja']/100);
        $fizetendo_szja = round($fizetendo_szja);

        if ( $data['osztalek_kifizetes'] == 'Igen' )
        {
          $alapszamitas = min( ((float)$data['brutto_alap'] * ($settings['ado_szocialis_hozzajarulas']/100)), (($szocho_ado_max - $osszes_jovedelem) * ($settings['ado_szocialis_hozzajarulas']/100)) );
          $fizetendo_szocho = $alapszamitas;
          $fizetendo_szocho = round($fizetendo_szocho);
          if ($fizetendo_szocho < 0 ) {
            $fizetendo_szocho = 0;
          }
        }

        $ret['jovedelem'] = $osszes_jovedelem;
        $ret['fizetendo_szja'] = $fizetendo_szja;
        $ret['fizetendo_szocho'] = $fizetendo_szocho;
        $ret['fizetendo'] = $fizetendo_szja +  $fizetendo_szocho;
        $ret['brutto_alap'] = $data['brutto_alap'];
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'cafeteria':
        $ret = array();
        $settings = $this->loadSettings( $calc );
        $ret['settings'] = $settings;
        $szochokiva_nemfizet_date = strtotime('2021-12-31');

        $jg = $this->getCafeteriaGroupByTitle( $data['juttatas'] );
        $ret['juttatas_group'] = $jg;

        $adoalap_kiegeszites = 0;
        $szja = 0;
        $szocho = 0;
        $szkh = 0;
        $kiva = 0;
        $ado_munkavallalo = 0;
        $ado_mukaltato = 0;
        $hataron_felul = false;

        $adoalap = (float)$data['juttatas_osszege'];

        // Adómentes juttatás
        // group 1 
        if ( $jg && in_array($jg['ID'], array(1)) )
        {          
          $adoalap_tetel_limits = $this->getCafeteriaItemAdoalapLimit();
          $adoalap_tetel_limit = (float)$adoalap_tetel_limits[$data['juttatas']];

          if ($adoalap_tetel_limit == 0 || ((float)$data['juttatas_osszege'] < $adoalap_tetel_limit) ) 
          {
            $adoalap_kiegeszites = 0;
          } else {
            $hataron_felul = true;
            $adoalap_kiegeszites = (float)$data['juttatas_osszege'] - $adoalap_tetel_limit;
            $adoalap_kiegeszites = ($adoalap_kiegeszites < 0) ? 0 : $adoalap_kiegeszites;
            $adoalap_kiegeszites = round($adoalap_kiegeszites);

            $szja = $adoalap_kiegeszites * ($settings['ado_szja']/100);
            $szja = ($szja < 0) ? 0 : $szja;
            $szja = round($szja);
  
            $tb = $adoalap_kiegeszites * ($settings['ado_tb']/100);
            $tb = ($tb < 0) ? 0 : $tb;
            $tb = round($tb);

            // összes munkaválllalói teher
            $munkavallalo_osszes_jarulek = $szja + $tb;
            $munkavallalo_osszes_jarulek = ($munkavallalo_osszes_jarulek < 0) ? 0 : $munkavallalo_osszes_jarulek;
            $munkavallalo_osszes_jarulek = round($munkavallalo_osszes_jarulek);
            $ado_munkavallalo = $munkavallalo_osszes_jarulek;

            // szocho
            if ( $data['ceg_kiva'] == 'Nem' && $adoalap_kiegeszites > 0)
            {
              $szocho = $adoalap_kiegeszites * ($settings['ado_szocialis_hozzajarulas']/100);
              $szocho = ($szocho < 0) ? 0 : $szocho;
              $szocho = round($szocho);
            }

            // Szakképzési
            if ($data['ceg_kiva'] == 'Nem' && $adoalap_kiegeszites > 0) {
              $szkh = $adoalap_kiegeszites * ($settings['ado_szakkepzesi_hozzajarulas']/100);
              $szkh = ($szkh < 0) ? 0 : $szkh;
              $szkh = round($szkh);
            }

            // kiva
            if ( $data['ceg_kiva'] == 'Igen' )
            {
              $kiva = $adoalap_kiegeszites * ($settings['ado_kisvallalati']/100);
              $kiva = ($kiva < 0) ? 0 : $kiva;
              $kiva = round($kiva);
            }

            $ado_munkaltato = $szocho + $szkh + $kiva;
          }
        } 

        if ( $jg && in_array($jg['ID'], array(2, 3)) )
        {
          if ( $jg['ID'] == 2) {
            $adoalap_tetel_limits = $this->getCafeteriaItemAdoalapLimit();
            $adoalap_tetel_limit = (float)$adoalap_tetel_limits[$data['juttatas']];

            if ((float)$data['juttatas_osszege'] <= $adoalap_tetel_limit) {
              $adoalap_kiegeszites = 0;
            } else {
              $hataron_felul = true;
              $adoalap_kiegeszites = (float)$data['juttatas_osszege'] - $adoalap_tetel_limit;
              $adoalap_kiegeszites = $adoalap_kiegeszites * ($settings['ado_caf_adoalap_kieg']/100);
              $adoalap_kiegeszites = ($adoalap_kiegeszites < 0) ? 0 : $adoalap_kiegeszites;
              $adoalap_kiegeszites = round($adoalap_kiegeszites);
            }
          }

          if ( $jg['ID'] == 3) 
          {
            $adoalap_tetel_limits = $this->getCafeteriaItemAdoalapLimit();
            $adoalap_tetel_limit = (float)$adoalap_tetel_limits[$data['juttatas']];
          
            if ($adoalap_tetel_limit != 0 && ((float)$data['juttatas_osszege'] > $adoalap_tetel_limit)) {
              $adoalap_kiegeszites = 0;
              $hataron_felul = true;
            } else {
              $adoalap_kiegeszites = (float)$data['juttatas_osszege'] * ($settings['ado_caf_adoalap_kieg']/100);
              $adoalap_kiegeszites = ($adoalap_kiegeszites < 0) ? 0 : $adoalap_kiegeszites;
              $adoalap_kiegeszites = round($adoalap_kiegeszites);
            }

            // szocho
            if ( $data['ceg_kiva'] == 'Nem' && $adoalap_kiegeszites > 0)
            {
              $szocho = $adoalap_kiegeszites * ($settings['ado_szocialis_hozzajarulas']/100);
              $szocho = ($szocho < 0) ? 0 : $szocho;
              $szocho = round($szocho);

              /** Hozzáadva @ 2021-02-08 */
              if( time() <= $szochokiva_nemfizet_date )
              {
                // Kikapcsolva
                //$szocho = 0;
              }
            }

            // Szakképzési
            if ($data['ceg_kiva'] == 'Nem' && $adoalap_kiegeszites > 0 ) {
              $szkh = $adoalap_kiegeszites * ($settings['ado_szakkepzesi_hozzajarulas']/100);
              $szkh = ($szkh < 0) ? 0 : $szkh;
              $szkh = round($szkh);
            }

            // kiva
            if ( $data['ceg_kiva'] == 'Igen' )
            {
              $kiva = $adoalap_kiegeszites * ($settings['ado_kisvallalati']/100);
              $kiva = ($kiva < 0) ? 0 : $kiva;
              $kiva = round($kiva);

              /** Hozzáadva @ 2021-02-08 */
              if( time() <= $szochokiva_nemfizet_date )
              {
                // Kikapcsolva
                // $kiva = 0;
              }
            }
          }

          $adoalap = (float)$adoalap_kiegeszites;
        }

        $ret['values']['adoalap'] = $adoalap;

        // szja
        // group 3, 4
        if ($jg && in_array($jg['ID'], array(3, 4)) ) {
          $szja = $adoalap * ($settings['ado_szja']/100);
          $szja = ($szja < 0) ? 0 : $szja;
          $szja = round($szja);

          $tb = $adoalap * ($settings['ado_tb']/100);
          $tb = ($tb < 0) ? 0 : $tb;
          $tb = round($tb);
        }

        
        $ret['values']['adoalap_tetel_limit'] = $adoalap_tetel_limit;
        $ret['values']['juttatas'] = $data['juttatas'];
        $ret['values']['hataron_felul'] = $hataron_felul;

        // group 3 módosítás, ha elérte a határértéket
        if  ($jg && in_array($jg['ID'], array(3)) && $hataron_felul ) 
        {
          /* **/
          $szja = (float)$data['juttatas_osszege'] * ($settings['ado_szja']/100);
          $szja = ($szja < 0) ? 0 : $szja;
          $szja = round($szja);

          $tb = (float)$data['juttatas_osszege'] * ($settings['ado_tb']/100);
          $tb = ($tb < 0) ? 0 : $tb;
          $tb = round($tb);
          /* */

          // összes munkaválllalói teher
          $munkavallalo_osszes_jarulek = $szja + $tb;
          $munkavallalo_osszes_jarulek = ($munkavallalo_osszes_jarulek < 0) ? 0 : $munkavallalo_osszes_jarulek;
          $munkavallalo_osszes_jarulek = round($munkavallalo_osszes_jarulek);
          $ado_munkavallalo = $munkavallalo_osszes_jarulek;
        } elseif( $jg && in_array($jg['ID'], array(3)) ) {
          $tb = 0;
        }

        // szocho
        if ($data['ceg_kiva'] == 'Nem' && !isset($szocho)) {
          $szocho = $adoalap * ($settings['ado_szocialis_hozzajarulas']/100);
          $szocho = ($szocho < 0) ? 0 : $szocho;
          $szocho = round($szocho);
        }

        // Szakképzési
        if ( !isset($szkh) && $data['ceg_kiva'] == 'Nem' && $jg && in_array($jg['ID'], array(4))) {
          $szkh = $adoalap * ($settings['ado_szakkepzesi_hozzajarulas']/100);
          $szkh = ($szkh < 0) ? 0 : $szkh;
          $szkh = round($szkh);
        }

        // KIVA
        if ($data['ceg_kiva'] == 'Igen' && $jg && in_array($jg['ID'], array(3, 4))) {
          $kiva = $data['juttatas_osszege'] * ($settings['ado_kisvallalati']/100);
          $kiva = ($kiva < 0) ? 0 : $kiva;
          $kiva = round($kiva);
        }

        // group 4
        if ($jg && in_array($jg['ID'], array(4)))
        {
          // összes munkaválllalói teher
          $mv_ado = ($settings['ado_szja'] + $settings['ado_tb']) / 100;
          $munkavallalo_osszes_jarulek = $adoalap * $mv_ado;
          $munkavallalo_osszes_jarulek = ($munkavallalo_osszes_jarulek < 0) ? 0 : $munkavallalo_osszes_jarulek;
          $munkavallalo_osszes_jarulek = round($munkavallalo_osszes_jarulek);
          $ado_munkavallalo = $munkavallalo_osszes_jarulek;

          // szocho
          if ($data['ceg_kiva'] == 'Nem') {
            $szocho = $adoalap * ($settings['ado_szocialis_hozzajarulas']/100);
            $szocho = ($szocho < 0) ? 0 : $szocho;
            $szocho = round($szocho);
          }

          // Szakképzési
          if ($data['ceg_kiva'] == 'Nem' ) {
            $szkh = $adoalap * ($settings['ado_szakkepzesi_hozzajarulas']/100);
            $szkh = ($szkh < 0) ? 0 : $szkh;
            $szkh = round($szkh);
          }
        }

        // group 2

        if ($jg && in_array($jg['ID'], array(2)))
        {
          // szja 
          if ( $adoalap_kiegeszites > 0 ) {
            $szja = ($adoalap_tetel_limit + $adoalap_kiegeszites) * ($settings['ado_szja']/100);
            $szja = ($szja < 0) ? 0 : $szja;
            $szja = round($szja);
          } else {
            $szja = ((float)$data['juttatas_osszege']) * ($settings['ado_szja']/100);
            $szja = ($szja < 0) ? 0 : $szja;
            $szja = round($szja);
          }

          // szocho
          if ( $data['ceg_kiva'] == 'Nem' && $adoalap_kiegeszites > 0)
          {
            $szocho = ($adoalap_tetel_limit + $adoalap_kiegeszites) * ($settings['ado_szocialis_hozzajarulas']/100);
            $szocho = ($szocho < 0) ? 0 : $szocho;
            $szocho = round($szocho);
          }

          if ( $data['ceg_kiva'] == 'Nem' && $adoalap_kiegeszites == 0 )
          {
            $szocho = ((float)$data['juttatas_osszege']) * ($settings['ado_szocialis_hozzajarulas']/100);
            $szocho = ($szocho < 0) ? 0 : $szocho;
            $szocho = round($szocho);
          }

          // kiva
          if ( $data['ceg_kiva'] == 'Igen' )
          {
            $kiva = (float)$data['juttatas_osszege'] * ($settings['ado_kisvallalati']/100);
            $kiva = ($kiva < 0) ? 0 : $kiva;
            $kiva = round($kiva);
          }
        }

         /** Hozzáadva @ 2021-02-08 */
        // Szakképzési hozzájárulás törlés egymegtarozós esetén [3] 06.30-ig
        if( time() <= $szochokiva_nemfizet_date && $szkh != 0 && $jg['ID'] == 3 )
        {
          // Bekapcsolva
          $szkh = 0;
        }

        /** Hozzáadva @ 2021-02-08 */
        // Szocho, kiva, szakho törlés szép kártyák esetén [2] 06.30-ig
        if( time() <= $szochokiva_nemfizet_date && $jg['ID'] == 2 )
        {
          // Bekapcsolva
          $kiva = 0;
          $szkh = 0;
          $szocho = 0;
        }

        // Munkáltató adók
        if ($jg && in_array($jg['ID'], array(2, 3))) {
          $ado_munkaltato = $szja + $szocho + $kiva + $szkh;
        }

        if ($jg && in_array($jg['ID'], array(4))) {
          $ado_munkaltato = $szocho + $szkh + $kiva;
        }

        // group 3 módosítás, ha elérte a határértéket
        if  ($jg && in_array($jg['ID'], array(3)) && $hataron_felul ) 
        {
          if( $data['ceg_kiva'] == 'Nem' )
          {
            $szocho = (float)$data['juttatas_osszege'] * ($settings['ado_szocialis_hozzajarulas']/100);
            $szocho = ($szocho < 0) ? 0 : $szocho;
            $szocho = round($szocho);
  
            $szkh = (float)$data['juttatas_osszege'] * ($settings['ado_szakkepzesi_hozzajarulas']/100);
            $szkh = ($szkh < 0) ? 0 : $szkh;
            $szkh = round($szkh);
          }

          $ado_munkaltato = $szocho + $szkh + $kiva;
        }
        
        /** Hozzáadva @ 2021-02-08 */
        // Szakképzési hozzájárulás törlés egymegtarozós esetén [3] 06.30-ig
        if( time() <= $szochokiva_nemfizet_date && $szkh != 0 && $jg['ID'] == 3 )
        {
          // Bekapcsolva
          $szkh = 0;
        }

        /** Hozzáadva @ 2021-02-08 */
        // Szocho, kiva, szakho törlés szép kártyák esetén [2] 06.30-ig
        if( time() <= $szochokiva_nemfizet_date && $jg['ID'] == 2 )
        {
          // Kikapcsolva
          $kiva = 0;
          $szkh = 0;
          $szocho = 0;
        }

        $ret['adoalap_kiegeszites'] = $adoalap_kiegeszites;
        $ret['szja'] = $szja;
        $ret['tb'] = $tb;
        $ret['szocho'] = $szocho;
        $ret['szkh'] = $szkh;
        $ret['kiva'] = $kiva;

        $ret['termeszet_egeszseg_jarulek'] = $termeszet_egeszseg_jarulek;
        $ret['penzbeli_egeszseg_jarulek'] = $penzbeli_egeszseg_jarulek;
        $ret['nyugdij_jarulek'] = $nyugdij_jarulek;
        $ret['munkaeropiac_hozzajarulas'] = $munkaeropiac_hozzajarulas;
        $ret['munkavallalo_osszes_jarulek'] = $munkavallalo_osszes_jarulek;

        $ret['ado_munkavallalo'] = $ado_munkavallalo;
        $ret['ado_munkaltato'] = $ado_munkaltato;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'anyak_szabadsaga':
        $ret = array(
          'szules_eveben_szabadsag' => 0,
          'szulesig_idoaranyos_szabadsag' => 0,
          'szules_eveben_igenybe_vett_szabadsag' => 0,
          'le_nem_toltott_szabadsag' => 0,

          'csed_idejere_jaro_szabadsag' => 0,
          'csed_szules_eveben_igenybe_vett_szabadsag' => 0,
          'csed_le_nem_toltott_szabadsag' => 0,

          'gyed_idejere_jaro_szabadsag' => 0,
          'gyed_szules_eveben_igenybe_vett_szabadsag' => 0,
          'gyed_le_nem_toltott_szabadsag' => 0,

          'osszes_szabadsag' => 0,
        );

        $values = array();
        $settings = $this->loadSettings( $calc );

        $targyev = (int)date('Y');
        $ev_elso_napja = date('Y').'-01-01';
        $ev_utolso_napja = date('Y-m-d', strtotime('last day of december this year'));
        $szules_eve = date('Y', strtotime($data['szules_ideje']));
        $szules_eveben_munkavallalo_eletkora = $szules_eve - (int)$data['szuletesi_ev'];
        $kor_potszabi = $this->potszabadasgKorSzerint($szules_eveben_munkavallalo_eletkora);
        $szulelotti_igyenbevett = ($data['szul_elott_igenybevett_potszabadsag_gyermek'] == '3 vagy több') ? 3  : (int)$data['szul_elott_igenybevett_potszabadsag_gyermek'];
        $gyerek_potszabi = $this->potszabadasg16evfiatalabbGyerekSzerint((int)$data['szul_elott_igenybevett_potszabadsag_gyermek']);

        $alap_szules_eveben_jaro_szabadsag = (int)$settings['alapszabadsag'] + $kor_potszabi + $gyerek_potszabi;

        $day = new DateTime($data['csed_kezdete']);
        $day_munkaviszony_kezdet = new DateTime($data['munkaviszony_kezdete']);
        $mv_day_str = strtotime($day_munkaviszony_kezdet->format('Y-m-d'));
        $csed_day_str = strtotime($day->format('Y').'-01-01');

        // alap munkaviszony kezdete
        $calc_ts = $mv_day_str;

        // ha a csed évi első napja nagyobb a mv kezdetétől
        if ( $csed_day_str > $calc_ts ) {
          $calc_ts = $csed_day_str;
        }

        if( $mv_day_str > $csed_day_str )
        {
          $szulesig_eltelt_napok_szama = (float)( (strtotime($day->format('Y-m-d')) - $calc_ts) / (60*60*24) );          
        } else {
          $szulesig_eltelt_napok_szama = (float)( (strtotime($day->format('Y-m-d')) - $calc_ts) / (60*60*24) );
        }

        if ($data['gyerek16ev_fiatalabb_fogyatekos'] == 'Igen') {
          $alap_szules_eveben_jaro_szabadsag += (float)$settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
        }

        $szulesig_idoaranyos_szabadsag = round($alap_szules_eveben_jaro_szabadsag / 365 * $szulesig_eltelt_napok_szama);
        $szules_eveben_igenybe_vett_szabadsag = (int)$data['szulev_igenybevett_szabadsag'];
        $le_nem_toltott_szabadsag = $szulesig_idoaranyos_szabadsag - $szules_eveben_igenybe_vett_szabadsag;

        $csed_kezdete = $data['csed_kezdete'];
        $minucseddays = 168-1;
        $csed_vege_datum = new DateTime($csed_kezdete); $csed_vege_datum->modify('+ '.$minucseddays.'day');
        $csed_vege = $csed_vege_datum->format('Y-m-d');

        $gyedgyes_kezdete = $data['gyedgyes_kezdete'];
        $minugyedgyesmonth = 6;
        $gyedgyes_vege_datum = new DateTime($gyedgyes_kezdete); $gyedgyes_vege_datum->modify('+ '.$minugyedgyesmonth.' month'); $gyedgyes_vege_datum->modify('-1 day');
        $gyedgyes_vege = $gyedgyes_vege_datum->format('Y-m-d');

        // Helpers
        // CSED
        $help_csed_kezdet = $csed_kezdete;
        $help_csed_vegzet = $csed_vege;
        $help_csed_eletkor1 = date('Y', strtotime($csed_kezdete)) - (int)$data['szuletesi_ev'];
        $help_csed_eletkor2 = date('Y', strtotime($help_csed_vegzet)) - (int)$data['szuletesi_ev'];
        $help_csed_potszabi1 = (int)$this->potszabadasgKorSzerint( (int)$help_csed_eletkor1 );
        $help_csed_potszabi2 = (int)$this->potszabadasgKorSzerint( (int)$help_csed_eletkor2 );

        if ( date('Y', strtotime($csed_kezdete)) == date('Y', strtotime($csed_vege)) ) {
          $vegzetplusz1 = new DateTime($help_csed_vegzet); $vegzetplusz1->modify('+1 day');
          $help_csed_potszabi = round( $help_csed_potszabi1 / 365 * ((strtotime($vegzetplusz1->format('Y-m-d')) - strtotime($help_csed_kezdet))/(60*60*24)));
        }
        else
        {
          // KEREKÍTÉS( G33/365 * (DÁTUM(ÉV(C33);12;31) + 1 - C33); 0 ) + KEREKÍTÉS( H33/365 * (D33 + 1 - DÁTUM(ÉV(D33); 1; 1)); 0 )
          $veg_s1 = date('Y', strtotime($csed_kezdete)).'-12-31';
          $veg_s1 = new DateTime($veg_s1); $veg_s1->modify('+1 day');
          $veg_s1 = $veg_s1->format('Y-m-d');

          $veg_s2 = date('Y', strtotime($csed_vege)).'-01-01';
          $veg_s21 = new DateTime($csed_vege); $veg_s21->modify('+1 day');
          $veg_s21 = $veg_s21->format('Y-m-d');

          $help_csed_potszabi =
          round( $help_csed_potszabi1 / 365 * ( (strtotime($veg_s1) - strtotime($csed_kezdete)) /(60*60*24) )) +
          round( $help_csed_potszabi2 / 365 * ( (strtotime($veg_s21) - (strtotime($veg_s2))) / (60*60*24) ));
        }

        $values['helper']['help_csed_kezdet'] = $help_csed_kezdet;
        $values['helper']['help_csed_vegzet'] = $help_csed_vegzet;
        $values['helper']['help_csed_eletkor1'] = $help_csed_eletkor1;
        $values['helper']['help_csed_eletkor2'] = $help_csed_eletkor2;
        $values['helper']['help_csed_potszabi1'] = $help_csed_potszabi1;
        $values['helper']['help_csed_potszabi2'] = $help_csed_potszabi2;
        $values['helper']['csed_potszabi'] = $help_csed_potszabi;

        // GYED
        $help_gyedgyes_kezdet = $gyedgyes_kezdete;
        $help_gyedgyes_vegzet = $gyedgyes_vege;
        $help_gyedgyes_eletkor1 = date('Y', strtotime($gyedgyes_kezdete)) - (int)$data['szuletesi_ev'];
        $help_gyedgyes_eletkor2 = date('Y', strtotime($help_gyedgyes_vegzet)) - (int)$data['szuletesi_ev'];
        $help_gyedgyes_potszabi1 = (int)$this->potszabadasgKorSzerint( (int)$help_gyedgyes_eletkor1 );
        $help_gyedgyes_potszabi2 = (int)$this->potszabadasgKorSzerint( (int)$help_gyedgyes_eletkor2 );

        if ( date('Y', strtotime($gyedgyes_kezdete)) == date('Y', strtotime($gyedgyes_vege)) ) {
          $vegzetplusz1 = new DateTime($help_gyedgyes_vegzet); $vegzetplusz1->modify('+1 day');
          $help_gyedgyes_potszabi = round( $help_gyedgyes_potszabi1 / 365 * ((strtotime($vegzetplusz1->format('Y-m-d')) - strtotime($help_gyedgyes_kezdet))/(60*60*24)));
        }
        else
        {
          $veg_s1 = date('Y', strtotime($gyedgyes_kezdete)).'-12-31';
          $veg_s1 = new DateTime($veg_s1); $veg_s1->modify('+1 day');
          $veg_s1 = $veg_s1->format('Y-m-d');

          $veg_s2 = date('Y', strtotime($gyedgyes_vege)).'-01-01';
          $veg_s21 = new DateTime($gyedgyes_vege); $veg_s21->modify('+1 day');
          $veg_s21 = $veg_s21->format('Y-m-d');

          $help_gyedgyes_potszabi =
          round( $help_gyedgyes_potszabi1 / 365 * ( (strtotime($veg_s1) - strtotime($gyedgyes_kezdete)) /(60*60*24) )) +
          round( $help_gyedgyes_potszabi2 / 365 * ( (strtotime($veg_s21) - (strtotime($veg_s2))) / (60*60*24) ));
        }

        $values['helper']['help_gyedgyes_kezdet'] = $help_gyedgyes_kezdet;
        $values['helper']['help_gyedgyes_vegzet'] = $help_gyedgyes_vegzet;
        $values['helper']['help_gyedgyes_eletkor1'] = $help_gyedgyes_eletkor1;
        $values['helper']['help_gyedgyes_eletkor2'] = $help_gyedgyes_eletkor2;
        $values['helper']['help_gyedgyes_potszabi1'] = $help_gyedgyes_potszabi1;
        $values['helper']['help_gyedgyes_potszabi2'] = $help_gyedgyes_potszabi2;
        $values['helper']['gyedgyes_potszabi'] = $help_gyedgyes_potszabi;

        // csed
        $csed_idejere_jaro_szabadsag = (int)$settings['alapszabadsag'] + $this->potszabadasg16evfiatalabbGyerekSzerint((int)$data['szul_elott_igenybevett_potszabadsag_gyermek']);
        if ($data['gyerek16ev_fiatalabb_fogyatekos'] == 'Igen') {
          $csed_idejere_jaro_szabadsag += (float)$settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
        }
        $csed_idejere_jaro_szabadsag_vt1 = new DateTime($csed_vege); $csed_idejere_jaro_szabadsag_vt1->modify('+1 day');
        $csed_idejere_jaro_szabadsag_vt1 = $csed_idejere_jaro_szabadsag_vt1->format('Y-m-d');
        $csed_idejere_jaro_szabadsag = $csed_idejere_jaro_szabadsag / 365 * ((strtotime($csed_idejere_jaro_szabadsag_vt1) - strtotime($csed_kezdete))/(60*60*24));
        $csed_idejere_jaro_szabadsag = round($csed_idejere_jaro_szabadsag);
        $csed_idejere_jaro_szabadsag += (int)$help_csed_potszabi;

        $csed_szules_eveben_igenybe_vett_szabadsag = min($le_nem_toltott_szabadsag, 0);

        $csed_le_nem_toltott_szabadsag = $csed_idejere_jaro_szabadsag + $csed_szules_eveben_igenybe_vett_szabadsag;

        // gyedgyes
        $gyedgyes_idejere_jaro_szabadsag = (int)$settings['alapszabadsag'] + $this->potszabadasg16evfiatalabbGyerekSzerint((int)$data['szul_elott_igenybevett_potszabadsag_gyermek']);
        if ($data['gyerek16ev_fiatalabb_fogyatekos'] == 'Igen') {
          $gyedgyes_idejere_jaro_szabadsag += (float)$settings['potszabi_ha16evnelfiatalabbgyereketnevel'];
        }
        $gyedgyes_idejere_jaro_szabadsag_vt1 = new DateTime($gyedgyes_vege); $gyedgyes_idejere_jaro_szabadsag_vt1->modify('+1 day');
        $gyedgyes_idejere_jaro_szabadsag_vt1 = $gyedgyes_idejere_jaro_szabadsag_vt1->format('Y-m-d');
        $gyedgyes_idejere_jaro_szabadsag = $gyedgyes_idejere_jaro_szabadsag / 365 * ((strtotime($gyedgyes_idejere_jaro_szabadsag_vt1) - strtotime($gyedgyes_kezdete))/(60*60*24));
        $gyedgyes_idejere_jaro_szabadsag = round($gyedgyes_idejere_jaro_szabadsag);
        $gyedgyes_idejere_jaro_szabadsag += (int)$help_gyedgyes_potszabi;

        $gyedgyes_szules_eveben_igenybe_vett_szabadsag = min($csed_le_nem_toltott_szabadsag, 0);

        $gyedgyes_le_nem_toltott_szabadsag = $gyedgyes_idejere_jaro_szabadsag + $gyedgyes_szules_eveben_igenybe_vett_szabadsag;

        // összes
        $osszes_szabadsag = $le_nem_toltott_szabadsag + $csed_idejere_jaro_szabadsag + $gyedgyes_idejere_jaro_szabadsag;

        $ret['szules_eveben_szabadsag'] = $alap_szules_eveben_jaro_szabadsag;
        $ret['szulesig_idoaranyos_szabadsag'] = $szulesig_idoaranyos_szabadsag;
        $ret['szules_eveben_igenybe_vett_szabadsag'] = $szules_eveben_igenybe_vett_szabadsag;
        $ret['le_nem_toltott_szabadsag'] = $le_nem_toltott_szabadsag;
        $ret['csed_idejere_jaro_szabadsag'] = $csed_idejere_jaro_szabadsag;
        $ret['csed_szules_eveben_igenybe_vett_szabadsag'] = $csed_szules_eveben_igenybe_vett_szabadsag;
        $ret['csed_le_nem_toltott_szabadsag'] = $csed_le_nem_toltott_szabadsag;
        $ret['gyedgyes_idejere_jaro_szabadsag'] = $gyedgyes_idejere_jaro_szabadsag;
        $ret['gyedgyes_szules_eveben_igenybe_vett_szabadsag'] = $gyedgyes_szules_eveben_igenybe_vett_szabadsag;
        $ret['gyedgyes_le_nem_toltott_szabadsag'] = $gyedgyes_le_nem_toltott_szabadsag;
        $ret['osszes_szabadsag'] = $osszes_szabadsag;

        $values['munkaviszony_kezdete'] = $data['munkaviszony_kezdete'];
        $values['szules_eve'] = $szules_eve;
        $values['szules_eveben_munkavallalo_eletkora'] = $szules_eveben_munkavallalo_eletkora;
        $values['gyerek_potszabi'] = $gyerek_potszabi;
        $values['gyerek_fogyatek_potszabi'] = $gyerek_fogyatek_potszabi;
        $values['szulesig_eltelt_napok_szama'] = $szulesig_eltelt_napok_szama;
        $values['csed_vege'] = $csed_vege;
        $values['gyedgyes_vege'] = $gyedgyes_vege;

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      /** Új kalkulátorok */
      case 'reprezentacio_ado':
        $ret = [];
        $values = [];
        $settings = $this->loadSettings( $calc );
        $ret['settings'] = $settings;
        $szakkh_nemfizet_date = strtotime('2021-12-31');

        $szamla_brutto = (float)$data['szamla_brutto'];
        $ret['szamla_brutto'] = $szamla_brutto;
        
        $ado_munkaltato = 0;
        $ado_szocialis_hozzajarulas = 0;
        $adoalap_kiegeszites = 0;
        $ado_kisvallalati = 0;
        $adoalap_kiegeszites = (float)$szamla_brutto * ($settings['ado_caf_adoalap_kieg'] / 100);
        $adoalap_kiegeszites = ($adoalap_kiegeszites < 0) ? 0 : $adoalap_kiegeszites;
        $adoalap_kiegeszites = round($adoalap_kiegeszites);

        $szja = $adoalap_kiegeszites * ($settings['ado_szja']/100);
        $szja = ($szja < 0) ? 0 : $szja;
        $szja = round($szja);
        $ret['ado_szja'] = $szja;
        $ado_munkaltato += $szja;

        if( $data['ceg_kisvallalati_ado_alany'] == 'Nem' )
        {
          // Szocho
          $ado_szocialis_hozzajarulas = (float)$adoalap_kiegeszites * ($settings['ado_szocialis_hozzajarulas'] / 100);
          $ado_szocialis_hozzajarulas = ($ado_szocialis_hozzajarulas < 0) ? 0 : $ado_szocialis_hozzajarulas;
          $ado_szocialis_hozzajarulas = round($ado_szocialis_hozzajarulas);
          $ret['ado_szocialis_hozzajarulas'] = $ado_szocialis_hozzajarulas;
          $ado_munkaltato += $ado_szocialis_hozzajarulas;

          // Szakképzési
          $ado_szakkepzesi_hozzajarulas = (float)$adoalap_kiegeszites * ($settings['ado_szakkepzesi_hozzajarulas'] / 100);
          $ado_szakkepzesi_hozzajarulas = ($ado_szakkepzesi_hozzajarulas < 0) ? 0 : $ado_szakkepzesi_hozzajarulas;
          $ado_szakkepzesi_hozzajarulas = round($ado_szakkepzesi_hozzajarulas);

          /** Hozzáadva @ 2021-02-08 */
          if( time() <= $szakkh_nemfizet_date )
          {
            $ado_szakkepzesi_hozzajarulas = 0;
          }

          $ret['ado_szakkepzesi_hozzajarulas'] = $ado_szakkepzesi_hozzajarulas;
          $ado_munkaltato += $ado_szakkepzesi_hozzajarulas;
        } 
        else 
        {
          // KIVA 
          $ado_kisvallalati = (float)$szamla_brutto * ($settings['ado_kisvallalati'] / 100);
          $ado_kisvallalati = ($ado_kisvallalati < 0) ? 0 : $ado_kisvallalati;
          $ado_kisvallalati = round($ado_kisvallalati);
          $ret['ado_kisvallalati'] = $ado_kisvallalati;
          
          $ado_munkaltato += $ado_kisvallalati;
        }

        // Values

        $values['kiva_adoalany'] = $data['ceg_kisvallalati_ado_alany'];
        $values['adoalap_kiegeszites'] = $adoalap_kiegeszites;

        $ret['ado_munkaltato'] = $ado_munkaltato;

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'cegtelefon_ado':
        $ret = [];
        $values = [];
        $settings = $this->loadSettings( $calc );
        $ret['settings'] = $settings;
        $szakkh_nemfizet_date = strtotime('2021-06-30');

        $szamla_brutto = (float)$data['szamla_brutto'];
        $ret['szamla_brutto'] = $szamla_brutto;
        
        $ado_munkaltato = 0;
        $ado_szocialis_hozzajarulas = 0;
        $adoalap_kiegeszites = 0;
        $ado_kisvallalati = 0;
        $adoalap_kiegeszites = (float)$szamla_brutto * ($settings['ado_caf_adoalap_kieg'] / 100);
        $adoalap_kiegeszites = ($adoalap_kiegeszites < 0) ? 0 : $adoalap_kiegeszites;
        $adoalap_kiegeszites = round($adoalap_kiegeszites);

        $szja = ($adoalap_kiegeszites * 0.2) * ($settings['ado_szja']/100);
        $szja = ($szja < 0) ? 0 : $szja;
        $szja = round($szja);
        $ret['ado_szja'] = $szja;
        $ado_munkaltato += $szja;

        if( $data['ceg_kisvallalati_ado_alany'] == 'Nem' )
        {
          // Szocho
          $ado_szocialis_hozzajarulas = ((float)$adoalap_kiegeszites * 0.2) * ($settings['ado_szocialis_hozzajarulas'] / 100);
          $ado_szocialis_hozzajarulas = ($ado_szocialis_hozzajarulas < 0) ? 0 : $ado_szocialis_hozzajarulas;
          $ado_szocialis_hozzajarulas = round($ado_szocialis_hozzajarulas);
          $ret['ado_szocialis_hozzajarulas'] = $ado_szocialis_hozzajarulas;
          $ado_munkaltato += $ado_szocialis_hozzajarulas;

          // Szakképzési
          $ado_szakkepzesi_hozzajarulas = ((float)$adoalap_kiegeszites * 0.2) * ($settings['ado_szakkepzesi_hozzajarulas'] / 100);
          $ado_szakkepzesi_hozzajarulas = ($ado_szakkepzesi_hozzajarulas < 0) ? 0 : $ado_szakkepzesi_hozzajarulas;
          $ado_szakkepzesi_hozzajarulas = round($ado_szakkepzesi_hozzajarulas);

          /** Hozzáadva @ 2021-02-08 */
          if( time() <= $szakkh_nemfizet_date )
          {
            $ado_szakkepzesi_hozzajarulas = 0;
          }

          $ret['ado_szakkepzesi_hozzajarulas'] = $ado_szakkepzesi_hozzajarulas;
          $ado_munkaltato += $ado_szakkepzesi_hozzajarulas;
        } 
        else 
        {
          // KIVA 
          $ado_kisvallalati = ((float)$szamla_brutto * 0.2) * ($settings['ado_kisvallalati'] / 100);
          $ado_kisvallalati = ($ado_kisvallalati < 0) ? 0 : $ado_kisvallalati;
          $ado_kisvallalati = round($ado_kisvallalati);
          $ret['ado_kisvallalati'] = $ado_kisvallalati;
          
          $ado_munkaltato += $ado_kisvallalati;
        }

        // Values
        $values['kiva_adoalany'] = $data['ceg_kisvallalati_ado_alany'];
        $values['adoalap_kiegeszites'] = $adoalap_kiegeszites;

        $ret['ado_munkaltato'] = $ado_munkaltato;

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
      case 'megbizasi_dij':
        $ret = [];
        $values = [];
        $settings = $this->loadSettings( $calc );
        $ret['settings'] = $settings;

        $values['megbizasi_dij'] = (float)$data['megbizasi_dij'];
        $values['kiva_adoalany'] = $data['ceg_kisvallalati_ado_alany'];
        
        $brutto = 0;
        $teljes = 0;
        $netto = 0;
        $jovedelem = 0;
        $napi_jovedelem = 0;
        $biztonsitottsag_hatara = 0;
        $biztositotta_valik = 'Nem';
        $levonas_bruttobol = 0;
        $ado_raforditas = 0;
        $ado_nav = 0;

        if( $data['koltseghanyad'] < 0 ) $data['koltseghanyad'] = 0;

        // BRUTTÓból számlás -> Nettó
        if( $data['mode'] == 'brutto')
        {
            $brutto = (float)$data['megbizasi_dij'];

           // Jövedelem
            $jovedelem = $data['megbizasi_dij'] * ( (100 - $data['koltseghanyad']) / 100 );
            $jovedelem = ($jovedelem < 0) ? 0 : $jovedelem;
            $jovedelem = round($jovedelem);
            $ret['jovedelem'] = $jovedelem;

            // Napi jövedelem
            $napi_jovedelem = (float)$jovedelem / (int)$data['nap'];
            $napi_jovedelem = ($napi_jovedelem < 0) ? 0 : $napi_jovedelem;
            $napi_jovedelem = round($napi_jovedelem);
            $values['napi_jovedelem'] = $napi_jovedelem;

            // Biztosítottság határa

            $biztonsitottsag_hatara = $settings['minimalber'] / 30 * 0.3;
            $values['biztonsitottsag_hatara'] = $biztonsitottsag_hatara;

            // Biztosítottá válik

            if( $napi_jovedelem >= $biztonsitottsag_hatara )
            {
              $biztositotta_valik = 'Igen';
            }
            $ret['biztositotta_valik'] = $biztositotta_valik;

            // Szja

            $szja = $jovedelem * ($settings['ado_szja']/100);
            $szja = ($szja < 0) ? 0 : $szja;
            $szja = round($szja);
            $ret['ado_szja'] = $szja;

            // TB
            if( $biztositotta_valik == 'Igen' )
            {
              $tb = $jovedelem * ($settings['ado_tb']/100);
              $tb = ($tb < 0) ? 0 : $tb;
              $tb = round($tb);
              $ret['ado_tb'] = $tb;

              // Öregségi nyugdíjas @ 2021-02-08
              if ($data['oregsegi_nyugdijas'] == 'Igen') 
              {
                $tb = 0;
                $ret['ado_tb'] = $tb;
              }

            } else {          
              $tb = 0;
              $ret['ado_tb'] = $tb;
            }

            $ado_nav += $tb;
            $ado_nav += $szja;

            // Összes levonás bruttóból 
            $levonas_bruttobol = $tb + $szja;
            $ret['levonas_bruttobol'] = $levonas_bruttobol;

            // Nettó
            if( $data['mode'] == 'brutto' )
            {
              $netto = (float)$data['megbizasi_dij'] - $levonas_bruttobol;
              $netto = ($netto < 0) ? 0 : $netto;
              $netto = round($netto);
            }

            // KIVA
            if( $data['ceg_kisvallalati_ado_alany'] == 'Igen' )
            {
              $kiva = $jovedelem * ($settings['ado_kisvallalati']/100);
              $kiva = ($kiva < 0) ? 0 : $kiva;
              $kiva = round($kiva);
              $ret['ado_kiva'] = $kiva;

              // Öregségi nyugdíjas @ 2021-02-08
              if ($data['oregsegi_nyugdijas'] == 'Igen') 
              {
                $kiva = 0;
                $ret['ado_kiva'] = $kiva;
              }

              $ado_raforditas = (float)$data['megbizasi_dij'] + $kiva;
              $ado_nav += $kiva;
            } 
            else 
            {
              $ado_szocialis_hozzajarulas = $jovedelem * ($settings['ado_szocialis_hozzajarulas']/100);
              $ado_szocialis_hozzajarulas = ($ado_szocialis_hozzajarulas < 0) ? 0 : $ado_szocialis_hozzajarulas;
              $ado_szocialis_hozzajarulas = round($ado_szocialis_hozzajarulas);
              $ret['ado_szocialis_hozzajarulas'] = $ado_szocialis_hozzajarulas;

              $ado_szakkepzesi_hozzajarulas = $jovedelem * ($settings['ado_szakkepzesi_hozzajarulas']/100);
              $ado_szakkepzesi_hozzajarulas = ($ado_szakkepzesi_hozzajarulas < 0) ? 0 : $ado_szakkepzesi_hozzajarulas;
              $ado_szakkepzesi_hozzajarulas = round($ado_szakkepzesi_hozzajarulas);
              $ret['ado_szakkepzesi_hozzajarulas'] = $ado_szakkepzesi_hozzajarulas;

              // Öregségi nyugdíjas @ 2021-02-08
              if ($data['oregsegi_nyugdijas'] == 'Igen') 
              {
                $ado_szocialis_hozzajarulas = 0;
                $ret['ado_szocialis_hozzajarulas'] = $ado_szocialis_hozzajarulas;

                $ado_szakkepzesi_hozzajarulas = 0;
                $ret['ado_szakkepzesi_hozzajarulas'] = $ado_szakkepzesi_hozzajarulas;
              }

              $ado_raforditas = (float)$data['megbizasi_dij'] + $ado_szocialis_hozzajarulas + $ado_szakkepzesi_hozzajarulas;
              $ado_nav += $ado_szocialis_hozzajarulas + $ado_szakkepzesi_hozzajarulas;
            }
        }

        // NETTÓból számolás -> Bruttó
        if( $data['mode'] == 'netto' )
        {
          $netto = (float)$data['megbizasi_dij'];
          
          $find = $this->findMegbizasiDijBruttoFromNetto( $netto, $data, $settings, $values );
          $sum_minusbrutto = (int)$find['brutto'];
          $values['find'] = $find;
          
          $ret['sum_minusbrutto'] = $find['values']['levonas'];
          $brutto = $netto_ber + $sum_minusbrutto;

          $ret['ado_szja'] = $find['values']['params']['ado_szja'];
          $ret['ado_tb'] = $find['values']['params']['ado_tb'];

          $ado_nav += $ret['ado_tb'];
          $ado_nav += $ret['ado_szja'];

          
          $levonas_bruttobol = $ret['ado_tb'] +  $ret['ado_szja'];
          $ret['levonas_bruttobol'] = $levonas_bruttobol;

          if( $data['ceg_kisvallalati_ado_alany'] == 'Igen' )
          {
            $kiva = $find['values']['params']['ado_kiva'];
            $kiva = ($kiva < 0) ? 0 : $kiva;
            $kiva = round($kiva);
            $ret['ado_kiva'] = $kiva;

            // Öregségi nyugdíjas @ 2021-02-08
            if ($data['oregsegi_nyugdijas'] == 'Igen') 
            {
              $kiva = 0;
              $ret['ado_kiva'] = $kiva;
            }

            $ado_raforditas = $brutto + $kiva;
            $ado_nav += $kiva;
          } 
          else 
          {
            $ret['ado_szocialis_hozzajarulas'] = $find['values']['params']['ado_szocialis_hozzajarulas'];
            $ret['ado_szakkepzesi_hozzajarulas'] = $find['values']['params']['ado_szakkepzesi_hozzajarulas'];

            // Öregségi nyugdíjas @ 2021-02-08
            if ($data['oregsegi_nyugdijas'] == 'Igen') 
            {
              $ado_szocialis_hozzajarulas = 0;
              $ret['ado_szocialis_hozzajarulas'] = $ado_szocialis_hozzajarulas;

              $ado_szakkepzesi_hozzajarulas = 0;
              $ret['ado_szakkepzesi_hozzajarulas'] = $ado_szakkepzesi_hozzajarulas;
            }

            $ado_raforditas = $brutto  + $ret['ado_szocialis_hozzajarulas'] + $ret['ado_szakkepzesi_hozzajarulas'];
            $ado_nav += $ret['ado_szocialis_hozzajarulas'] + $ret['ado_szakkepzesi_hozzajarulas'];
          }
        }

        // Teljes 
        if( $data['mode'] == 'teljes' )
        {
          $teljes = (float)$data['megbizasi_dij'];
          
          $find = $this->findMegbizasiDijTeljes( $teljes, $data, $settings, $values );
          $values['find'] = $find;
          $brutto = $find['brutto'];

          // Jövedelem
           $jovedelem = (float)$find['values']['params']['jovedelem'];
           $jovedelem = ($jovedelem < 0) ? 0 : $jovedelem;
           $jovedelem = round($jovedelem);
           $ret['jovedelem'] = $jovedelem;

           // Napi jövedelem
           $napi_jovedelem = (float)$jovedelem / (int)$data['nap'];
           $napi_jovedelem = ($napi_jovedelem < 0) ? 0 : $napi_jovedelem;
           $napi_jovedelem = round($napi_jovedelem);
           $values['napi_jovedelem'] = $napi_jovedelem;

           // Biztosítottság határa

           $biztonsitottsag_hatara = $settings['minimalber'] / 30 * 0.3;
           $values['biztonsitottsag_hatara'] = $biztonsitottsag_hatara;

           // Biztosítottá válik

           if( $napi_jovedelem >= $biztonsitottsag_hatara )
           {
             $biztositotta_valik = 'Igen';
           }
           $ret['biztositotta_valik'] = $biztositotta_valik;

           // Szja

           $szja = $jovedelem * ($settings['ado_szja']/100);
           $szja = ($szja < 0) ? 0 : $szja;
           $szja = round($szja);
           $ret['ado_szja'] = $szja;

           // TB
           if( $biztositotta_valik == 'Igen' )
           {
             $tb = $jovedelem * ($settings['ado_tb']/100);
             $tb = ($tb < 0) ? 0 : $tb;
             $tb = round($tb);
             $ret['ado_tb'] = $tb;

             // Öregségi nyugdíjas @ 2021-02-08
             if ($data['oregsegi_nyugdijas'] == 'Igen') 
             {
               $tb = 0;
               $ret['ado_tb'] = $tb;
             }

           } else {          
             $tb = 0;
             $ret['ado_tb'] = $tb;
           }

           $ado_nav += $tb;
           $ado_nav += $szja;

           // Összes levonás bruttóból 
           $levonas_bruttobol = $tb + $szja;
           $ret['levonas_bruttobol'] = $levonas_bruttobol;

           $netto = (float)$brutto - $levonas_bruttobol;
           $netto = ($netto < 0) ? 0 : $netto;
           $netto = round($netto);

           // KIVA
           if( $data['ceg_kisvallalati_ado_alany'] == 'Igen' )
           {
             $kiva = $jovedelem * ($settings['ado_kisvallalati']/100);
             $kiva = ($kiva < 0) ? 0 : $kiva;
             $kiva = round($kiva);
             $ret['ado_kiva'] = $kiva;

             // Öregségi nyugdíjas @ 2021-02-08
             if ($data['oregsegi_nyugdijas'] == 'Igen') 
             {
               $kiva = 0;
               $ret['ado_kiva'] = $kiva;
             }

             $ado_raforditas = (float)$brutto + $kiva;
             $ado_nav += $kiva;
           } 
           else 
           {
             $ado_szocialis_hozzajarulas = $jovedelem * ($settings['ado_szocialis_hozzajarulas']/100);
             $ado_szocialis_hozzajarulas = ($ado_szocialis_hozzajarulas < 0) ? 0 : $ado_szocialis_hozzajarulas;
             $ado_szocialis_hozzajarulas = round($ado_szocialis_hozzajarulas);
             $ret['ado_szocialis_hozzajarulas'] = $ado_szocialis_hozzajarulas;

             $ado_szakkepzesi_hozzajarulas = $jovedelem * ($settings['ado_szakkepzesi_hozzajarulas']/100);
             $ado_szakkepzesi_hozzajarulas = ($ado_szakkepzesi_hozzajarulas < 0) ? 0 : $ado_szakkepzesi_hozzajarulas;
             $ado_szakkepzesi_hozzajarulas = round($ado_szakkepzesi_hozzajarulas);
             $ret['ado_szakkepzesi_hozzajarulas'] = $ado_szakkepzesi_hozzajarulas;

             // Öregségi nyugdíjas @ 2021-02-08
             if ($data['oregsegi_nyugdijas'] == 'Igen') 
             {
               $ado_szocialis_hozzajarulas = 0;
               $ret['ado_szocialis_hozzajarulas'] = $ado_szocialis_hozzajarulas;

               $ado_szakkepzesi_hozzajarulas = 0;
               $ret['ado_szakkepzesi_hozzajarulas'] = $ado_szakkepzesi_hozzajarulas;
             }

             $ado_raforditas = (float)$brutto + $ado_szocialis_hozzajarulas + $ado_szakkepzesi_hozzajarulas;
             $ado_nav += $ado_szocialis_hozzajarulas + $ado_szakkepzesi_hozzajarulas;
           }

        }

        $ret['teljes'] = $teljes;
        $ret['brutto'] = $brutto;
        $ret['netto'] = $netto;
        $ret['ado_raforditas'] = $ado_raforditas;
        $ret['ado_nav'] = $ado_nav;

        $ret['values'] = $values;
        $ret['version'] = $this->getVersion();
        $ret['vi'] = $this->version_index[$ret['version']];
        $ret['result_comment'] = $this->getResultTitle( $settings, $ret['version'] );

        return $ret;
      break;
    }

    return false;
  } 
}

?>
