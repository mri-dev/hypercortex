<?php
class AjaxRequests
{
  public function __construct()
  {
    return $this;
  }

  public function test()
  {
    add_action( 'wp_ajax_'.__FUNCTION__, array( $this, 'testcls'));
    add_action( 'wp_ajax_nopriv_'.__FUNCTION__, array( $this, 'testcls'));
  }

  public function contact_form()
  {
    add_action( 'wp_ajax_'.__FUNCTION__, array( $this, 'ContactFormRequest'));
    add_action( 'wp_ajax_nopriv_'.__FUNCTION__, array( $this, 'ContactFormRequest'));
  }

  public function calc_api_interface()
  {
    add_action( 'wp_ajax_'.__FUNCTION__, array( $this, 'CalcAPIInterface'));
    add_action( 'wp_ajax_nopriv_'.__FUNCTION__, array( $this, 'CalcAPIInterface'));
  }

  public function calc_settings()
  {
    add_action( 'wp_ajax_'.__FUNCTION__, array( $this, 'CalcSettings'));
    add_action( 'wp_ajax_nopriv_'.__FUNCTION__, array( $this, 'CalcSettings'));
  }

  public function subscriber()
  { 
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
      // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
      // you want to allow, and if so:
      header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
      header('Access-Control-Allow-Credentials: true');
      header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
    
    add_action( 'wp_ajax_'.__FUNCTION__, array( $this, 'subscriberRequest'));
    add_action( 'wp_ajax_nopriv_'.__FUNCTION__, array( $this, 'subscriberRequest'));
  }

  public function subscriberRequest()
  {
    $form = [];

    /*
    f_9: "Cég neve"
    f_11: ["1"] GDPR
    f_12: ["2"] Hírlevél
    subscr: "email cím"
    */
    parse_str($_POST['form'], $form );

    $return = array(
      'error' => 0,
      'msg'   => '',
      'missing_elements' => [],
      'error_elements' => [],
      'missing' => 0,
      'passed_params' => false
    );

    $return['passed_params'] = $form;

    $return['data'] = [
      'subscribed' => $form['subscr']
    ];
    $this->returnJSON($return);

    echo json_encode($return);
    die();
  }

  public function CalcSettings()
  {
    extract($_POST);
    $return = array(
      'error' => 0,
      'msg'   => '',
      'missing_elements' => [],
      'error_elements' => [],
      'missing' => 0,
      'passed_params' => false
    );
    $return['passed_params'] = $_POST;
    $inputs = $_POST['input'];

    $calculators = new Calculators();

    $result = $calculators->getSettings();

    $return['data'] = $result;
    $this->returnJSON($return);
  }

  public function CalcAPIInterface()
  {
    extract($_POST);
    $return = array(
      'error' => 0,
      'msg'   => '',
      'missing_elements' => [],
      'error_elements' => [],
      'missing' => 0,
      'passed_params' => false
    );
    $return['passed_params'] = $_POST;
    $inputs = $_POST['input'];

    $calculators = new Calculators( $inputs['version'] );

    switch ( $calculator )
    {
      case 'netto_ber':
        if ( empty($inputs['brutto_ber']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'brutto_ber';
          $return['error_elements']['brutto_ber'] = 'Írja be a bruttó bérét a kalkulációhoz!';
        }

        if ( $inputs['csaladkedvezmenyre_jogosult'] == 'Igen' && empty($inputs['csalad_eltartott_gyermek']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'csalad_eltartott_gyermek';
        }

        if ( $inputs['csaladkedvezmenyre_jogosult'] == 'Igen' && empty($inputs['csalad_eltartott_gyermek_kedvezmenyezett']) && $inputs['csalad_eltartott_gyermek_kedvezmenyezett'] != '0' ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'csalad_eltartott_gyermek_kedvezmenyezett';
        }

        // Handling missing
        if (!empty($return['missing_elements'])) {
          $return['msg'] .= '<div class="head"><strong>A kalkuláció nem futott le az alábbi okok miatt:</strong></div>';
          $return['msg'] .= '- Hiányzó kötelező mezők: '.count($return['missing_elements']).' db<br>';
        }

        // Handling error
        if (!empty($return['error_elements'])) {
          $return['msg'] .= '<div class="head"><strong>Hiba a kalkuláció során:</strong></div>';
          foreach ((array)$return['error_elements'] as $key => $value) {
            $return['msg'] .= '- '.$value.'<br>';
          }
        }

        if ($return['error'] == 1) {
          $this->returnJSON($return);
          exit;
        }

        if ($return['error'] == 0) {
          $result = $calculators->calc( $calculator, $inputs );
          $return['data'] = $result;
          $this->returnJSON($return);
        }

        unset($return);
        unset($result);
        unset($calculators);
      break;
      case 'brutto_ber':
        if ( empty($inputs['netto_ber']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'netto_ber';
          $return['error_elements']['brutto_ber'] = 'Írja be a nettó bért a kalkulációhoz!';
        }

        if ( $inputs['csaladkedvezmenyre_jogosult'] == 'Igen' && empty($inputs['csalad_eltartott_gyermek']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'csalad_eltartott_gyermek';
        }

        if ( $inputs['csaladkedvezmenyre_jogosult'] == 'Igen' && empty($inputs['csalad_eltartott_gyermek_kedvezmenyezett']) && $inputs['csalad_eltartott_gyermek_kedvezmenyezett'] != '0' ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'csalad_eltartott_gyermek_kedvezmenyezett';
        }

        // Handling missing
        if (!empty($return['missing_elements'])) {
          $return['msg'] .= '<div class="head"><strong>A kalkuláció nem futott le az alábbi okok miatt:</strong></div>';
          $return['msg'] .= '- Hiányzó kötelező mezők: '.count($return['missing_elements']).' db<br>';
        }

        // Handling error
        if (!empty($return['error_elements'])) {
          $return['msg'] .= '<div class="head"><strong>Hiba a kalkuláció során:</strong></div>';
          foreach ((array)$return['error_elements'] as $key => $value) {
            $return['msg'] .= '- '.$value.'<br>';
          }
        }

        if ($return['error'] == 1) {
          $this->returnJSON($return);
          exit;
        }

        if ($return['error'] == 0) {
          $result = $calculators->calc( $calculator, $inputs );
          $return['data'] = $result;
          $this->returnJSON($return);
        }

        unset($return);
        unset($result);
        unset($calculators);
      break;
      // nettó bér kiegészítve
      case 'teljes_berkoltseg':
        if ( empty($inputs['brutto_ber']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'brutto_ber';
          $return['error_elements']['brutto_ber'] = 'Írja be a bruttó bérét a kalkulációhoz!';
        }

        if ( $inputs['csaladkedvezmenyre_jogosult'] == 'Igen' && empty($inputs['csalad_eltartott_gyermek']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'csalad_eltartott_gyermek';
        }

        if ( $inputs['csaladkedvezmenyre_jogosult'] == 'Igen' && empty($inputs['csalad_eltartott_gyermek_kedvezmenyezett']) && $inputs['csalad_eltartott_gyermek_kedvezmenyezett'] != '0' ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'csalad_eltartott_gyermek_kedvezmenyezett';
        }
        // NETTÓ BÉR ALAP EDDIG


        // Handling missing
        if (!empty($return['missing_elements'])) {
          $return['msg'] .= '<div class="head"><strong>A kalkuláció nem futott le az alábbi okok miatt:</strong></div>';
          $return['msg'] .= '- Hiányzó kötelező mezők: '.count($return['missing_elements']).' db<br>';
        }

        // Handling error
        if (!empty($return['error_elements'])) {
          $return['msg'] .= '<div class="head"><strong>Hiba a kalkuláció során:</strong></div>';
          foreach ((array)$return['error_elements'] as $key => $value) {
            $return['msg'] .= '- '.$value.'<br>';
          }
        }

        if ($return['error'] == 1) {
          $this->returnJSON($return);
          exit;
        }

        if ($return['error'] == 0) {
          $result = $calculators->calc( $calculator, $inputs );
          $return['data'] = $result;
          $this->returnJSON($return);
        }

        unset($return);
        unset($result);
        unset($calculators);
      break;

      // teljes bérköltség alapja
      case 'berkalkulator':
        if ( empty($inputs['jovedelem']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'jovedelem';
          $return['error_elements']['brutto_ber'] = 'Írja be a rendszeres havi jövedelmet a kalkulációhoz!';
        }

        if ( $inputs['csaladkedvezmenyre_jogosult'] == 'Igen' && empty($inputs['csalad_eltartott_gyermek']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'csalad_eltartott_gyermek';
        }

        if ( $inputs['csaladkedvezmenyre_jogosult'] == 'Igen' && empty($inputs['csalad_eltartott_gyermek_kedvezmenyezett']) && $inputs['csalad_eltartott_gyermek_kedvezmenyezett'] != '0' ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'csalad_eltartott_gyermek_kedvezmenyezett';
        }
        // NETTÓ BÉR ALAP EDDIG


        // Handling missing
        if (!empty($return['missing_elements'])) {
          $return['msg'] .= '<div class="head"><strong>A kalkuláció nem futott le az alábbi okok miatt:</strong></div>';
          $return['msg'] .= '- Hiányzó kötelező mezők: '.count($return['missing_elements']).' db<br>';
        }

        // Handling error
        if (!empty($return['error_elements'])) {
          $return['msg'] .= '<div class="head"><strong>Hiba a kalkuláció során:</strong></div>';
          foreach ((array)$return['error_elements'] as $key => $value) {
            $return['msg'] .= '- '.$value.'<br>';
          }
        }

        if ($return['error'] == 1) {
          $this->returnJSON($return);
          exit;
        }

        if ($return['error'] == 0) {
          $result = $calculators->calc( $calculator, $inputs );
          $return['data'] = $result;
          $this->returnJSON($return);
        }

        unset($return);
        unset($result);
        unset($calculators);
      break;
      case 'belepo_szabadsag':
        // Require field validation
        if ( empty($inputs['szuletesi_ev']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'szuletesi_ev';
          $return['error_elements']['szuletesi_ev'] = 'Pótolja a születési évét a kalkulációhoz!';
        }

        if ( $inputs['iden_kezdett_dolgozni'] == 'Igen' && empty($inputs['belepes_datuma']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'belepes_datuma';
          $return['error_elements']['belepes_datuma'] = 'A munkába állás első napját kötelező megadnia abban az esetben, ha idén kezdett el dolgozni munkahelyén!';
        }

        // Handling missing
        if (!empty($return['missing_elements'])) {
          $return['msg'] .= '<div class="head"><strong>A kalkuláció nem futott le az alábbi okok miatt:</strong></div>';
          $return['msg'] .= '- Hiányzó kötelező mezők: '.count($return['missing_elements']).' db<br>';
        }

        // Handling error
        if (!empty($return['error_elements'])) {
          $return['msg'] .= '<div class="head"><strong>Hiba a kalkuláció során:</strong></div>';
          foreach ((array)$return['error_elements'] as $key => $value) {
            $return['msg'] .= '- '.$value.'<br>';
          }
        }

        if ($return['error'] == 1) {
          $this->returnJSON($return);
          exit;
        }

        if ($return['error'] == 0) {
          $result = $calculators->calc( $calculator, $inputs );
          $return['data'] = $result;
          $this->returnJSON($return);
        }

        unset($return);
        unset($result);
        unset($calculators);
      break;
      case 'cegauto_ado':
        // Require field validation
        if ( empty($inputs['emission']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'emission';
        }

        if ( empty($inputs['kw']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'kw';
        }

        // Error fields
        /*
        if ( $inputs['emission'] < 4 ) {
          $return['error'] = 1;
          $return['error_elements']['emission'] = 'Emisszió kisebb, mint 4';
        }*/

        // Handling missing
        if (!empty($return['missing_elements'])) {
          $return['msg'] .= '<div class="head"><strong>A kalkuláció nem futott le az alábbi okok miatt:</strong></div>';
          $return['msg'] .= '- Hiányzó kötelező mezők: '.count($return['missing_elements']).' db<br>';
        }

        // Handling error
        if (!empty($return['error_elements'])) {
          $return['msg'] .= '<div class="head"><strong>Hiba a kalkuláció során:</strong></div>';
          foreach ((array)$return['error_elements'] as $key => $value) {
            $return['msg'] .= '- '.$value.'<br>';
          }
        }

        if ($return['error'] == 1) {
          $this->returnJSON($return);
          exit;
        }

        if ($return['error'] == 0) {
          $result = $calculators->calc( $calculator, $inputs );
          $return['data'] = $result;
          $this->returnJSON($return);
        }

        unset($return);
        unset($result);
        unset($calculators);
      break;
      case 'ingatlan_ertekesites':
        // Require field validation

        if ( empty($inputs['atruhazas_eve'])) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'atruhazas_eve';
        }
        if ( empty($inputs['atruhazasbol_bevetel']) && $inputs['atruhazasbol_bevetel'] != 0 ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'atruhazasbol_bevetel';
        }
        if ( empty($inputs['szerzes_eve']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'szerzes_eve';
        }
        if ( empty($inputs['megszerzes_osszeg']) && $inputs['megszerzes_osszeg'] != 0 ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'megszerzes_osszeg';
        }
        if ( empty($inputs['megszerzes_egyeb_kiadas']) && $inputs['megszerzes_egyeb_kiadas'] != 0 ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'megszerzes_egyeb_kiadas';
        }
        if ( empty($inputs['erteknovelo_beruhazasok']) && $inputs['erteknovelo_beruhazasok'] != 0 ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'erteknovelo_beruhazasok';
        }
        if ( empty($inputs['erteknovelo_beruhazasok_allammegovas']) && $inputs['erteknovelo_beruhazasok_allammegovas'] != 0) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'erteknovelo_beruhazasok_allammegovas';
        }
        if ( empty($inputs['atruhazas_koltsegei']) && $inputs['atruhazas_koltsegei'] != 0) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'atruhazas_koltsegei';
        }

        // Error fields

        if ( $inputs['szerzes_eve'] > $inputs['atruhazas_eve'] ) {
          $return['error'] = 1;
          $return['error_elements']['szerzes_eve'] = 'A szerzési év nem lehet későbbi, mint az ingatlan átruházásának éve!';
        }

        // Handling missing
        if (!empty($return['missing_elements'])) {
          $return['msg'] .= '<div class="head"><strong>A kalkuláció nem futott le az alábbi okok miatt:</strong></div>';
          $return['msg'] .= '- Hiányzó kötelező mezők: '.count($return['missing_elements']).' db<br>';
        }

        // Handling error
        if (!empty($return['error_elements'])) {
          $return['msg'] .= '<div class="head"><strong>Hiba a kalkuláció során:</strong></div>';
          foreach ((array)$return['error_elements'] as $key => $value) {
            $return['msg'] .= '- '.$value.'<br>';
          }
        }

        if ($return['error'] == 1) {
          $this->returnJSON($return);
          exit;
        }

        if ($return['error'] == 0) {
          $result = $calculators->calc( $calculator, $inputs );
          $return['data'] = $result;
          $this->returnJSON($return);
        }

        unset($return);
        unset($result);
        unset($calculators);
      break;
      case 'osztalekado':
        // Require field validation

        if ( empty($inputs['brutto_alap'])) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'brutto_alap';
        }

        // Error fields

        // Handling missing
        if (!empty($return['missing_elements'])) {
          $return['msg'] .= '<div class="head"><strong>A kalkuláció nem futott le az alábbi okok miatt:</strong></div>';
          $return['msg'] .= '- Hiányzó kötelező mezők: '.count($return['missing_elements']).' db<br>';
        }

        // Handling error
        if (!empty($return['error_elements'])) {
          $return['msg'] .= '<div class="head"><strong>Hiba a kalkuláció során:</strong></div>';
          foreach ((array)$return['error_elements'] as $key => $value) {
            $return['msg'] .= '- '.$value.'<br>';
          }
        }

        if ($return['error'] == 1) {
          $this->returnJSON($return);
          exit;
        }

        if ($return['error'] == 0) {
          $result = $calculators->calc( $calculator, $inputs );
          $return['data'] = $result;
          $this->returnJSON($return);
        }

        unset($return);
        unset($result);
        unset($calculators);
      break;
      case 'cafeteria':
        // Require field validation

        if ( empty($inputs['juttatas_osszege'])) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'juttatas_osszege';
        }

        if ( empty($inputs['juttatas'])) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'juttatas';
        }

        // Error fields

        // Handling missing
        if (!empty($return['missing_elements'])) {
          $return['msg'] .= '<div class="head"><strong>A kalkuláció nem futott le az alábbi okok miatt:</strong></div>';
          $return['msg'] .= '- Hiányzó kötelező mezők: '.count($return['missing_elements']).' db<br>';
        }

        // Handling error
        if (!empty($return['error_elements'])) {
          $return['msg'] .= '<div class="head"><strong>Hiba a kalkuláció során:</strong></div>';
          foreach ((array)$return['error_elements'] as $key => $value) {
            $return['msg'] .= '- '.$value.'<br>';
          }
        }

        if ($return['error'] == 1) {
          $this->returnJSON($return);
          exit;
        }

        if ($return['error'] == 0) {
          $result = $calculators->calc( $calculator, $inputs );
          $return['data'] = $result;
          $this->returnJSON($return);
        }

        unset($return);
        unset($result);
        unset($calculators);
      break;
      case 'anyak_szabadsaga':
        // Require field validation
        if ( empty($inputs['szuletesi_ev']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'szuletesi_ev';
          $return['error_elements']['szuletesi_ev'] = 'Pótolja a születési évét a kalkulációhoz!';
        }

        if ( empty($inputs['munkaviszony_kezdete']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'munkaviszony_kezdete';
          $return['error_elements']['munkaviszony_kezdete'] = 'Pótolja a munkaviszony kezdete időpontot a kalkulációhoz!';
        }

        if ( empty($inputs['szules_ideje']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'szules_ideje';
          $return['error_elements']['szules_ideje'] = 'Pótolja a szülés időpontját a kalkulációhoz!';
        }

        if ( empty($inputs['csed_kezdete']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'csed_kezdete';
          $return['error_elements']['csed_kezdete'] = 'Pótolja a szülési szabadság (CSED) kezdetét a kalkulációhoz!';
        }

        if ( empty($inputs['gyedgyes_kezdete']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'gyedgyes_kezdete';
          $return['error_elements']['gyedgyes_kezdete'] = 'Pótolja a fizetés nélküli szabadság (GYED/GYES) kezdetét a kalkulációhoz!';
        }


        // Handling missing
        if (!empty($return['missing_elements'])) {
          $return['msg'] .= '<div class="head"><strong>A kalkuláció nem futott le az alábbi okok miatt:</strong></div>';
          $return['msg'] .= '- Hiányzó kötelező mezők: '.count($return['missing_elements']).' db<br>';
        }

        // Handling error
        if (!empty($return['error_elements'])) {
          $return['msg'] .= '<div class="head"><strong>Hiba a kalkuláció során:</strong></div>';
          foreach ((array)$return['error_elements'] as $key => $value) {
            $return['msg'] .= '- '.$value.'<br>';
          }
        }

        if ($return['error'] == 1) {
          $this->returnJSON($return);
          exit;
        }

        if ($return['error'] == 0) {
          $result = $calculators->calc( $calculator, $inputs );
          $return['data'] = $result;
          $this->returnJSON($return);
        }

        unset($return);
        unset($result);
        unset($calculators);
      break;
      case 'reprezentacio_ado':

        if ( empty($inputs['szamla_brutto'])) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'szamla_brutto';
        }

        // Error fields
        // Handling missing
        if (!empty($return['missing_elements'])) {
          $return['msg'] .= '<div class="head"><strong>A kalkuláció nem futott le az alábbi okok miatt:</strong></div>';
          $return['msg'] .= '- Hiányzó kötelező mezők: '.count($return['missing_elements']).' db<br>';
        }

        // Handling error
        if (!empty($return['error_elements'])) {
          $return['msg'] .= '<div class="head"><strong>Hiba a kalkuláció során:</strong></div>';
          foreach ((array)$return['error_elements'] as $key => $value) {
            $return['msg'] .= '- '.$value.'<br>';
          }
        }

        if ($return['error'] == 1) {
          $this->returnJSON($return);
          exit;
        }

        if ($return['error'] == 0) {
          $result = $calculators->calc( $calculator, $inputs );
          $return['data'] = $result;
          $this->returnJSON($return);
        }


        unset($return);
        unset($result);
        unset($calculators);
      break;
      case 'cegtelefon_ado':

        if ( empty($inputs['szamla_brutto'])) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'szamla_brutto';
        }

        // Error fields
        // Handling missing
        if (!empty($return['missing_elements'])) {
          $return['msg'] .= '<div class="head"><strong>A kalkuláció nem futott le az alábbi okok miatt:</strong></div>';
          $return['msg'] .= '- Hiányzó kötelező mezők: '.count($return['missing_elements']).' db<br>';
        }

        // Handling error
        if (!empty($return['error_elements'])) {
          $return['msg'] .= '<div class="head"><strong>Hiba a kalkuláció során:</strong></div>';
          foreach ((array)$return['error_elements'] as $key => $value) {
            $return['msg'] .= '- '.$value.'<br>';
          }
        }

        if ($return['error'] == 1) {
          $this->returnJSON($return);
          exit;
        }

        if ($return['error'] == 0) {
          $result = $calculators->calc( $calculator, $inputs );
          $return['data'] = $result;
          $this->returnJSON($return);
        }


        unset($return);
        unset($result);
        unset($calculators);
      break;
      
      case 'megbizasi_dij':

        if ( empty($inputs['megbizasi_dij']) ) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'megbizasi_dij';
        }
        
        if ( empty($inputs['nap'])) {
          $return['error'] = 1;
          $return['missing_elements'][] = 'nap';
        }

        if ( $inputs['koltseghanyad'] == '') {
          $return['error'] = 1;
          $return['missing_elements'][] = 'koltseghanyad';
        }

        // Error fields
        // Handling missing
        if (!empty($return['missing_elements'])) {
          $return['msg'] .= '<div class="head"><strong>A kalkuláció nem futott le az alábbi okok miatt:</strong></div>';
          $return['msg'] .= '- Hiányzó kötelező mezők: '.count($return['missing_elements']).' db<br>';
        }

        // Handling error
        if (!empty($return['error_elements'])) {
          $return['msg'] .= '<div class="head"><strong>Hiba a kalkuláció során:</strong></div>';
          foreach ((array)$return['error_elements'] as $key => $value) {
            $return['msg'] .= '- '.$value.'<br>';
          }
        }

        if ($return['error'] == 1) {
          $this->returnJSON($return);
          exit;
        }

        if ($return['error'] == 0) {
          $result = $calculators->calc( $calculator, $inputs );
          $return['data'] = $result;
          $this->returnJSON($return);
        }


        unset($return);
        unset($result);
        unset($calculators);
      break;
      default:
        // code...
      break;
    }

    echo json_encode($return);
    die();
  }

  public function ContactFormRequest()
  {
    extract($_POST);
    $return = array(
      'error' => 0,
      'msg'   => '',
      'missing_elements' => [],
      'error_elements' => [],
      'missing' => 0,
      'passed_params' => false
    );

    $return['passed_params'] = $_POST;

    // Require field validation
    if ( empty($form['cegnev']) ) { $return['missing_elements'][] = 'cegnev'; }
    if ( empty($form['munkavallalo_letszam']) ) { $return['missing_elements'][] = 'munkavallalo_letszam'; }
    if ( $form['munkavallalo_letszam'] < 100 && empty($form['munkavallalo_meghalad100']) ) { $return['missing_elements'][] = 'munkavallalo_meghalad100'; }

    if ( empty($form['almalmi_munkavallalok']) ) { $return['missing_elements'][] = 'almalmi_munkavallalok'; }
    if ( empty($form['megbizasi_jogviszonyu_szemelyek']) ) { $return['missing_elements'][] = 'megbizasi_jogviszonyu_szemelyek'; }
    if ( empty($form['berenkivuli_juttatas']) ) { $return['missing_elements'][] = 'berenkivuli_juttatas'; }
    if ( empty($form['specialis_foglalkoztatasi_modozatok']) ) { $return['missing_elements'][] = 'specialis_foglalkoztatasi_modozatok'; }
    if ( empty($form['kikuldetes']) ) { $return['missing_elements'][] = 'kikuldetes'; }


    if ( empty($form['feladat_kapcsolatfelvetel']) ) { $return['missing_elements'][] = 'feladat_kapcsolatfelvetel'; }
    if ( empty($form['feladat_nav_bejelentes']) ) { $return['missing_elements'][] = 'feladat_nav_bejelentes'; }
    if ( empty($form['feladat_hokozi_szamfejtes']) ) { $return['missing_elements'][] = 'feladat_hokozi_szamfejtes'; }
    if ( empty($form['feladat_konyveles_feladas']) ) { $return['missing_elements'][] = 'feladat_konyveles_feladas'; }
    if ( empty($form['feladat_eveleji_szja_beker']) ) { $return['missing_elements'][] = 'feladat_eveleji_szja_beker'; }
    if ( empty($form['feladat_jovedelemigazolas']) ) { $return['missing_elements'][] = 'feladat_jovedelemigazolas'; }
    if ( empty($form['feladat_munkaszerzodes']) ) { $return['missing_elements'][] = 'feladat_munkaszerzodes'; }
    if ( empty($form['feladat_ksh_adatszolgaltatas']) ) { $return['missing_elements'][] = 'feladat_ksh_adatszolgaltatas'; }

    if ( empty($form['integralt_rendszer_hasznalat']) ) { $return['missing_elements'][] = 'integralt_rendszer_hasznalat'; }
    if ( $form['integralt_rendszer_hasznalat'] == 'igen' && empty($form['integralt_rendszer']) ) { $return['missing_elements'][] = 'integralt_rendszer'; }
    if ( $form['integralt_rendszer_hasznalat'] == 'igen' && empty($form['integralt_rendszer_hasznalat_jovoben']) ) { $return['missing_elements'][] = 'integralt_rendszer_hasznalat_jovoben'; }
    if ( $form['integralt_rendszer_hasznalat'] == 'igen' && empty($form['integralt_rendszer_hasznalat_hozzaferes']) ) { $return['missing_elements'][] = 'integralt_rendszer_hasznalat_hozzaferes'; }

    if ( empty($form['berkifizetes_datum']) ) { $return['missing_elements'][] = 'berkifizetes_datum'; }

    if ( empty($form['contact_name']) ) { $return['missing_elements'][] = 'contact_name'; }
    if ( empty($form['contact_phone']) ) { $return['missing_elements'][] = 'contact_phone'; }
    // phone validate
    $phone_valid = preg_match('/^([0-9]+)$/', trim($form['contact_phone']));
    if ( !empty($form['contact_phone']) && !$phone_valid ) { $return['error_elements']['contact_phone'] = __('A telefonszám nem megfelelő. Kérjük, hogy csak számokat használjon!'); }

    if ( empty($form['contact_email']) ) { $return['missing_elements'][] = 'contact_email'; }
    $email_valid = filter_var($form['contact_email'], \FILTER_VALIDATE_EMAIL);
    if ( !empty($form['contact_email']) && !$email_valid ) { $return['error_elements']['contact_email'] = __('Érvényes e-mail címet adjon meg. Pl.: mail@example.com'); }


    if ( empty($form['cb_adatvedelem']) ) {
      $return['missing_elements'][] = 'cb_adatvedelem';
      $return['error_elements']['cb_adatvedelem'] = __('Az adatvédelmi nyilatkozat elolvasása és elfogadása kötelező!');
    }

    // Handling missing
    if (!empty($return['missing_elements']))
    {
      $return['error'] = 1;
      $return['msg'] .= '<div class="head"><strong>A kalkuláció nem futott le az alábbi okok miatt:</strong></div>';
      $return['msg'] .= '- Hiányzó kötelező mezők: '.count($return['missing_elements']).' db<br>';
    }

    // Handling error
    if (!empty($return['error_elements'])) {
      $return['msg'] .= '<div class="head"><strong>Hiba a kalkuláció során:</strong></div>';
      foreach ((array)$return['error_elements'] as $key => $value) {
        $return['msg'] .= '- '.$value.'<br>';
      }
    }

    if ($return['error'] == 1) {
      $this->returnJSON($return);
      exit;
    }

    // captcha
    if (false)
    {
      $captcha_code = $_POST['g-recaptcha-response'];
      $recapdata = array(
          'secret' => CAPTCHA_SECRET_KEY,
          'response' => $captcha_code
      );
      $return['recaptcha']['secret'] = CAPTCHA_SECRET_KEY;
      $return['recaptcha']['response'] = $captcha_code;
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($recapdata));
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $recap_result = json_decode(curl_exec($ch), true);
      curl_close($ch);
      $return['recaptcha']['result'] = $recap_result;

      if(isset($recap_result['success']) && $recap_result['success'] === false) {
        $return['error']  = 1;
        $return['msg']    =  __('Kérjük, hogy azonosítsa magát. Ha Ön nem spam robot, jelölje be a fenti jelölő négyzetben, hogy nem robot.',  'Avada');
        $this->returnJSON($return);
      }
    }

    $to = get_option('admin_email');
    $subject = sprintf(__('Új kapcsolat üzenet érkezett: %s - %s','hc'), $form['cegnev'], $form['contact_name']);

    ob_start();
  	  include(locate_template('templates/mails/contactform.php'));
      $message = ob_get_contents();
		ob_end_clean();

    add_filter( 'wp_mail_from', array($this, 'getMailSender') );
    add_filter( 'wp_mail_from_name', array($this, 'getMailSenderName') );
    add_filter( 'wp_mail_content_type', array($this, 'getMailFormat') );

    $name = $form['contact_name'];
    $email = $form['contact_email'];

    $headers    = array();
    if (!empty($email)) {
      $headers[]  = 'Reply-To: '.$name.' <'.$email.'>';
    }

    $alert = wp_mail( $to, $subject, $message, $headers );

    /* * /
    if (!empty($email)) {
      $headers    = array();
      $headers[]  = 'Reply-To: '.get_option('blogname').' <no-reply@'.TARGETDOMAIN.'>';
      $alerttext = true;
      ob_start();
    	  include(locate_template('templates/mails/contactform-receiveuser.php'));
        $message = ob_get_contents();
  		ob_end_clean();
      $ualert = wp_mail( $email, 'Értesítés: '.$contct_type.' üzenetét megkaptuk.', $message, $headers );
    }
    /* */

    if(!$alert) {
      $return['error']  = 1;
      $return['msg']    = __('Az üzenetet jelenleg nem tudtuk elküldeni. Próbálja meg később.',  'hc');
      $this->returnJSON($return);
    }

    $return['msg'] = __('<strong>Sikeresen elküldte az üzenetetét!</strong><br>Köszönjük, hogy felvette velünk a kapcsolatot. Levelére hamasoan válaszolunk!');

    echo json_encode($return);
    die();
  }

  public function SzinvalasztoRequest()
  {
    extract($_POST);
    $settings = json_decode(stripslashes($_POST['settings']), true);

    $re = array(
      'error' => 0,
      'msg' => null,
      'data' => array()
    );

    switch  ( $type ) {
      case 'getSettings':
        $re['data'] = get_option('ajanlatkero_szinvalaszto_cfg', false);
      break;
      case 'saveSettings':
        update_option('ajanlatkero_szinvalaszto_cfg', $settings);
        $re['data'] = get_option('ajanlatkero_szinvalaszto_cfg', false);
      break;
    }

    echo json_encode($re);
    die();
  }

  public function testcls()
  {

    echo json_encode($return);
    die();
  }

  public function getMailFormat(){
      return "text/html";
  }

  public function getMailSender($default)
  {
    return get_option('admin_email');
  }

  public function getMailSenderName($default)
  {
    return get_option('blogname', 'Wordpress');
  }

  private function returnJSON($array)
  {
    echo json_encode($array);
    die();

  }
}
?>
