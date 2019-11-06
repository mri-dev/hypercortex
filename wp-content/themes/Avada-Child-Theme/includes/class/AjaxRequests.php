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

    $calculators = new Calculators();

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
          $result = $calculators->calculate( $calculator, $inputs );
          $return['data'] = $result;
          $this->returnJSON($return);
        }
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
          $result = $calculators->calculate( $calculator, $inputs );
          $return['data'] = $result;
          $this->returnJSON($return);
        }
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
          $result = $calculators->calculate( $calculator, $inputs );
          $return['data'] = $result;
          $this->returnJSON($return);
        }
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
          $result = $calculators->calculate( $calculator, $inputs );
          $return['data'] = $result;
          $this->returnJSON($return);
        }
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
    $subject = sprintf(__('Új kapcsolatüzenet érkezett: %s','hc'), $contact_type, $name);

    ob_start();
  	  include(locate_template('templates/mails/contactform.php'));
      $message = ob_get_contents();
		ob_end_clean();

    add_filter( 'wp_mail_from', array($this, 'getMailSender') );
    add_filter( 'wp_mail_from_name', array($this, 'getMailSenderName') );
    add_filter( 'wp_mail_content_type', array($this, 'getMailFormat') );

    $headers    = array();
    if (!empty($email)) {
      $headers[]  = 'Reply-To: '.$name.' <'.$email.'>';
    }

    //$alert = wp_mail( $to, $subject, $message, $headers );

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
