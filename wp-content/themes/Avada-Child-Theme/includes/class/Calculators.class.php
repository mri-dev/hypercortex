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
      case 'cegauto_ado':
        return $this->load_cegauto_ado_resources();
      break;
    }

    return false;
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
