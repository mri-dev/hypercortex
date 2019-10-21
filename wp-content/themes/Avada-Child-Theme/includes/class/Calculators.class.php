<?php
class Calculators
{
  function __construct()
  {
    return $this;
  }

  public function calculate( $calc, $data )
  {
    switch ( $calc )
    {
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
          'betegszabadsag_eves' => 15,
          'betegszabadsag_idoaranyos' => 0
        );
        $values = array();
        $settings = $this->loadSettings( $calc );
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

        $betegszabadsag_eves = 15;
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
    }

    return false;
  }

  public function loadSettings( $calc )
  {
    switch ( $calc )
    {
      case 'netto_ber':
        return $this->load_netto_ber_resources();
      break;
      case 'cegauto_ado':
        return $this->load_cegauto_ado_resources();
      break;
      case 'belepo_szabadsag':
        return $this->load_belepo_szabadsag_resources();
      break;
    }

    return false;
  }

  public function getSettingsValue( $key )
  {
    $res = array();

    $value = false;

    // temp
    $res['alapszabadsag'] = 20;
    $res['potszabi_ha16evnelfiatalabbgyereketnevel'] = 2;
    $res['potszabi_megvaltozott_munkakepessegu'] = 5;

    $res['ado_szja'] = 15;
    $res['ado_termeszetegeszseg'] = 4;
    $res['ado_penzbeli_egeszseg'] = 3;
    $res['ado_nyugdij'] = 10;
    $res['ado_munkaerppiac'] = 1.5;

    $res['adokedvezmeny_frisshazasok'] = 33335;
    $res['adokedvezmeny_szemelyi'] = 7450;
    $res['adokedvezmeny_csalad_gyermek1'] = 66670;
    $res['adokedvezmeny_csalad_gyermek2'] = 133330;
    $res['adokedvezmeny_csalad_gyermek3'] = 220000;

    $value = $res[$key];

    return $value;
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
