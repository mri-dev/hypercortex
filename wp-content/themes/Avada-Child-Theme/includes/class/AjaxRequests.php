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

    parse_str($_POST['input'], $inputs);

    $_POST['input'] = $inputs;
    $return['passed_params'] = $_POST;

    $calculators = new Calculators();

    switch ( $calculator )
    {
      case 'netto_ber':

      break;
      case 'teljes_berkoltseg':

      break;
      case 'belepo_szabadsag':

      break;
      case 'cegauto_ado':
        $result = $calculators->calculate( $calculator, $inputs );
        $return['data'] = $result;
        $this->returnJSON($return);
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

    $err_elements_text = '';

    $return['passed_params'] = $_POST;
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $uzenet = $_POST['uzenet'];
    $szoftver = $_POST['szoftver'];
    $company = $_POST['company'];
    $contacttype = $_POST['formtype'];
    $adatvedelem = $_POST['adatvedelem'];
    $marketing = $_POST['marketing'];

    switch ($contacttype) {
      case 'ajanlatkeres':
        $contact_type = 'ajánlatkérés';
      break;
      case 'kapcsolat':
        $contact_type = 'kapcsolat üzenet';
      break;
    }

    if(empty($name)) $return['missing_elements'][] = 'name';
    if(empty($email)) $return['missing_elements'][] = 'email';
    if(empty($phone)) $return['missing_elements'][] = 'phone';

    if (!isset($adatvedelem))
    {
      $return['error']  = 1;
      $return['msg']    =  __('Az ajánlatkérés elküldéséhez kötelezően el kell fogadni az Adatvédelmi Nyilatkozatot!',  'Avada');

      $return['missing']= count($return['missing_elements']);
      $this->returnJSON($return);
    }

    if(!empty($return['missing_elements'])) {
      $return['error']  = 1;
      $return['msg']    =  __('Kérjük, hogy töltse ki az összes mezőt az üzenet küldéséhez.',  'Avada');

      $return['missing']= count($return['missing_elements']);
      $this->returnJSON($return);
    }

    if(!empty($return['error_elements'])) {
      $return['error']  = 1;
      $return['msg']    =  __('A következő mezők hibásan vannak kitöltve',  'Avada').":\n". $err_elements_text;
      $return['missing']= count($return['missing_elements']);
      $this->returnJSON($return);
    }

    // captcha
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

    $to = get_option('admin_email');
    $subject = sprintf(__('Új %s érkezett: %s'), $contact_type, $name);

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
      $return['msg']    = __('Az ajánlatkérést jelenleg nem tudtuk elküldeni. Próbálja meg később.',  'Avada');
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
