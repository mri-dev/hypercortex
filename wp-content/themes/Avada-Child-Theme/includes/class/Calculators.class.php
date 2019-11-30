<?php
class Calculators
{
  const DEFAULT_VERSION = 2019;
  public $year_version = 2019;
  protected $avaiable_versions = array(2019);

  function __construct()
  {
    $this->year_version = get_option(\METAKEY_PREFIX.'calc_version', self::DEFAULT_VERSION);
    return $this;
  }

  public function getVersion( $metakeyed = false )
  {
    if ($metakeyed) {
      return \METAKEY_PREFIX.'ref_v'.$this->year_version.'_';
    } else {
      return $this->year_version;
    }
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

    $settings[] = $this->getVersion(true).'adokedvezmeny_frisshazasok';
    $settings[] = $this->getVersion(true).'adokedvezmeny_szemelyi';
    $settings[] = $this->getVersion(true).'adokedvezmeny_csalad_gyermek1';
    $settings[] = $this->getVersion(true).'adokedvezmeny_csalad_gyermek2';
    $settings[] = $this->getVersion(true).'adokedvezmeny_csalad_gyermek3';
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

  public function calculate( $calc, $data )
  {
    switch ( $calc )
    {
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

        $values['szocho_es_kiva_kedvezmeny_alap'] = $szocho_es_kiva_kedvezmeny_alap;
        $values['szokho_kedvezmeny_alap'] = $szokho_kedvezmeny_alap;
        $values['kiva_adoalany'] = $data['ceg_kisvallalati_ado_alany'];

        $ret['values'] = $values;

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
        $ev_vegeig_hatralevo_napok = round((strtotime($ev_utolso_napja) - strtotime($szamitas_kezdete)) / (60 * 60 * 24)) + 1;
        $ev_naptari_napok = round((strtotime($ev_utolso_napja) - strtotime($ev_elso_napja)) / (60 * 60 * 24)) + 1;
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
        $ret['szabadsag_idoaranyos'] = (int)$szabadsag_idoaranyos;

        $betegszabadsag_eves = $settings['betegszabadsag'];
        $betegszabadsag_idoaranyos = $betegszabadsag_eves/$ev_naptari_napok*$ev_vegeig_hatralevo_napok;

        $ret['betegszabadsag_eves'] = $betegszabadsag_eves;
        $ret['betegszabadsag_idoaranyos'] = (int)$betegszabadsag_idoaranyos;


        $values['targyev'] = $targyev;
        $values['szamitas_kezdete'] = $szamitas_kezdete;
        $values['ev_utolso_napja'] = $ev_utolso_napja;
        $values['munkavallalo_kora'] = $munkavallalo_kora;
        $values['ev_vegeig_hatralevo_napok'] = $ev_vegeig_hatralevo_napok;
        $values['ev_naptari_napok'] = $ev_naptari_napok;
        $values['kor_potszabi'] = $kor_potszabi;
        $values['gyerek16fiatalabb_potszabi'] = $gyerek16fiatalabb_potszabi;

        $ret['values'] = $values;
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
        }

        $ret['jovedelem'] = $osszes_jovedelem;
        $ret['fizetendo_szja'] = $fizetendo_szja;
        $ret['fizetendo_szocho'] = $fizetendo_szocho;
        $ret['fizetendo'] = $fizetendo_szja +  $fizetendo_szocho;
        $ret['brutto_alap'] = $data['brutto_alap'];

        return $ret;
      break;
      case 'cafeteria':
        $ret = array();
        $settings = $this->loadSettings( $calc );
        $ret['settings'] = $settings;

        return $ret;
      break;
    }

    return false;
  }

  public function loadSettings( $calc )
  {
    switch ( $calc )
    {
      case 'teljes_berkoltseg':
        return $this->load_teljes_berkoltseg_resources();
      break;
      case 'netto_ber':
        return $this->load_netto_ber_resources();
      break;
      case 'cegauto_ado':
        return $this->load_cegauto_ado_resources();
      break;
      case 'belepo_szabadsag':
        return $this->load_belepo_szabadsag_resources();
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
    }

    return false;
  }

  public function getSettingsValue( $key )
  {
    $res = array();

    $value = false;

    // temp
    $res['alapszabadsag'] = (float)get_option($this->getVersion(true).'alapszabadsag', 0 );
    $res['betegszabadsag'] = (float)get_option($this->getVersion(true).'betegszabadsag', 0 );
    $res['potszabi_ha16evnelfiatalabbgyereketnevel'] = (float)get_option($this->getVersion(true).'potszabi_ha16evnelfiatalabbgyereketnevel', 0 );
    $res['potszabi_megvaltozott_munkakepessegu'] = (float)get_option($this->getVersion(true).'potszabi_megvaltozott_munkakepessegu', 0 );

    $res['ado_szja'] = (float)get_option($this->getVersion(true).'ado_szja', 0 );
    $res['ado_termeszetegeszseg'] = (float)get_option($this->getVersion(true).'ado_termeszetegeszseg', 0 );
    $res['ado_penzbeli_egeszseg'] = (float)get_option($this->getVersion(true).'ado_penzbeli_egeszseg', 0 );
    $res['ado_nyugdij'] = (float)get_option($this->getVersion(true).'ado_nyugdij', 0 );
    $res['ado_munkaerppiac'] = (float)get_option($this->getVersion(true).'ado_munkaerppiac', 0 );
    $res['ado_szocialis_hozzajarulas'] = (float)get_option($this->getVersion(true).'ado_szocialis_hozzajarulas', 0 );
    $res['ado_szakkepzesi_hozzajarulas'] = (float)get_option($this->getVersion(true).'ado_szakkepzesi_hozzajarulas', 0 );
    $res['ado_kisvallalati'] = (float)get_option($this->getVersion(true).'ado_kisvallalati', 0 );
    $res['ado_caf_adoalap_kieg'] = (float)get_option($this->getVersion(true).'ado_caf_adoalap_kieg', 0 );

    $res['adokedvezmeny_frisshazasok'] = (float)get_option($this->getVersion(true).'adokedvezmeny_frisshazasok', 0 );
    $res['adokedvezmeny_szemelyi'] = (float)get_option($this->getVersion(true).'adokedvezmeny_szemelyi', 0 );
    $res['adokedvezmeny_csalad_gyermek1'] = (float)get_option($this->getVersion(true).'adokedvezmeny_csalad_gyermek1', 0 );
    $res['adokedvezmeny_csalad_gyermek2'] = (float)get_option($this->getVersion(true).'adokedvezmeny_csalad_gyermek2', 0 );
    $res['adokedvezmeny_csalad_gyermek3'] = (float)get_option($this->getVersion(true).'adokedvezmeny_csalad_gyermek3', 0 );

    $res['minimalber'] = (float)get_option($this->getVersion(true).'minimalber', 0 );

    $value = $res[$key];

    return $value;
  }

  private function load_teljes_berkoltseg_resources()
  {
    $res = array();

    $res['minimalber'] = $this->getSettingsValue('minimalber');

    $res['ado_szja'] = $this->getSettingsValue('ado_szja');
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

    $res['ado_szja'] = $this->getSettingsValue('ado_szja');
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
    $res['ado_szja'] = $this->getSettingsValue('ado_szja');

    // Form resources
    $forms = array();
    $res['forms'] = $forms;

    return $res;
  }

  private function load_osztalekado_resources()
  {
    $res = array();

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

    $res['ado_caf_adoalap_kieg'] = $this->getSettingsValue('ado_caf_adoalap_kieg');
    $res['ado_szja'] = $this->getSettingsValue('ado_szja');
    $res['ado_szocialis_hozzajarulas'] = $this->getSettingsValue('ado_szocialis_hozzajarulas');

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
?>
