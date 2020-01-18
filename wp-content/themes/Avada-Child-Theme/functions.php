<?php
define('PROTOCOL', 'https');
define('DOMAIN', $_SERVER['HTTP_HOST']);
define('TARGETDOMAIN', DOMAIN);
define('IFROOT', str_replace(get_option('siteurl'), '//'.DOMAIN, get_stylesheet_directory_uri()));
define('DEVMODE', true);
define('IMG', IFROOT.'/images');
define('GOOGLE_API_KEY', 'AIzaSyA0Mu8_XYUGo9iXhoenj7HTPBIfS2jDU2E');
define('LANGKEY','hu');
define('FB_APP_ID', '1900170110285208');
define('METAKEY_PREFIX', 'hc_'); // Textdomain
define('DEFAULT_LANGUAGE', 'hu_HU');
define('TD', 'buso');
define('CAPTCHA_SITE_KEY', '6LeGfsEUAAAAAHUc28uWCC24RdXyfw8LSU55pEEG');
define('CAPTCHA_SECRET_KEY', '6LeGfsEUAAAAAOKxmdJ8b_JSYlI5Wv0JmA8dEB--');

// Includes
require_once "includes/include.php";
$app_settings = new Setup_General_Settings();

function get_site_title( $site = '' )
{
  global $wpdb;

  $site = ($site == '') ? : $site.'_';
  $q = "SELECT option_value FROM sbe_{$site}options WHERE option_name = 'blogname'";
  $title = $wpdb->get_var($q);

  return $title;
}

function mailer_config(PHPMailer $phpmailer)
{
  if ( ! is_object( $phpmailer ) ) {
		$phpmailer = (object) $phpmailer;
	}

	$phpmailer->Mailer     = 'smtp';
	$phpmailer->Host       = SMTP_HOST;
	$phpmailer->SMTPAuth   = SMTP_AUTH;
	$phpmailer->Port       = SMTP_PORT;
	$phpmailer->Username   = SMTP_USER;
	$phpmailer->Password   = SMTP_PASS;
	$phpmailer->SMTPSecure = SMTP_SECURE;
	$phpmailer->From       = SMTP_FROM;
	$phpmailer->FromName   = SMTP_NAME;
}
add_action( 'phpmailer_init', 'mailer_config', 10, 1);

function get_languages()
{
  global $wpdb;

  $qry = $wpdb->prepare("SELECT blog_id, domain FROM buso_blogs WHERE public = 1 and deleted = 0 and archived = 0");
  $qry = $wpdb->get_results($qry);
  $langs = array();

  if ($qry) {
    foreach ( (array)$qry as $q ) {
      $q->current = ($q->blog_id == get_current_blog_id()) ? true : false;
      // English
      if (strpos($q->domain, 'en.busojaras' ) !== false) {
        $q->lang = "English";
        $q->local = "en_US";
      } else if(strpos($q->domain, 'de.busojaras' ) !== false){
        $q->lang = "Deutsch";
        $q->local = "de_DE";
      } else if(strpos($q->domain, 'hr.busojaras' ) !== false){
        $q->lang = "Hrvatski";
        $q->local = "hr_HR";
      } else {
        $q->lang = "Magyar";
        $q->local = "hu_HU";
      }
      $q->flag = IMG.'/flags/'.$q->local.".png";
      $langs[] = $q;
    }
  }

  return $langs;
}

function get_site_prefix()
{
  return '';
}

function theme_enqueue_styles() {
    wp_enqueue_style( 'avada-parent-stylesheet', get_template_directory_uri() . '/style.css?' );
    wp_enqueue_style( 'jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css', array(), '1.12.1' );
    wp_enqueue_style( 'slick-theme', IFROOT . '/assets/vendors/slick/slick-theme.css');
    wp_enqueue_style( 'slick', IFROOT . '/assets/vendors/slick/slick.css');
    //wp_enqueue_style( 'angular-material','//ajax.googleapis.com/ajax/libs/angular_material/1.1.4/angular-material.min.css');
    //wp_enqueue_style( 'angualardatepick', IFROOT . '/assets/vendors/md-date-range-picker/md-date-range-picker.min.css?t=' . ( (DEVMODE === true) ? time() : '' ) );
    wp_enqueue_script( 'google-maps', '//maps.googleapis.com/maps/api/js?sensor=false&language='.get_locale().'&region=hu&libraries=places&key='.GOOGLE_API_KEY);
    wp_enqueue_script( 'recaptcha', '//www.google.com/recaptcha/api.js?render='.CAPTCHA_SITE_KEY);
    wp_enqueue_script( 'jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', array('jquery'), '1.12.1');
    wp_enqueue_script( 'slick', IFROOT . '/assets/vendors/slick/slick.min.js');
    wp_enqueue_script( 'master', IFROOT . '/assets/js/master.js#asyncload');
    //wp_enqueue_script( 'jquery-ui-loc-hu', IFROOT . '/assets/js/jquery-ui-loc-hu.js');
    //wp_enqueue_script( 'fontasesome', '//use.fontawesome.com/releases/v5.0.6/js/all.js');
    wp_enqueue_script( 'angularjs', '//ajax.googleapis.com/ajax/libs/angularjs/1.5.7/angular.min.js');
    //wp_enqueue_script( 'angular-moment', '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js');
    //wp_enqueue_script( 'angular-animate', '//ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-animate.min.js');
    //wp_enqueue_script( 'angular-aria', '//ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-aria.min.js');
    //wp_enqueue_script( 'angular-message', '//ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-messages.min.js');
    //wp_enqueue_script( 'angular-material', '//ajax.googleapis.com/ajax/libs/angular_material/1.1.4/angular-material.min.js');
    //wp_enqueue_script( 'angular-sanitize', '//ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-sanitize.min.js');
    //wp_enqueue_script( 'mocjax', IFROOT . '/assets/vendors/autocomplete/scripts/jquery.mockjax.js');
    //wp_enqueue_script( 'autocomplete', IFROOT . '/assets/vendors/autocomplete/dist/jquery.autocomplete.min.js');
    //wp_enqueue_script( 'angualardatepick', IFROOT . '/assets/vendors/md-date-range-picker/md-date-range-picker.js?t=' . ( (DEVMODE === true) ? time() : '' ) );
    //wp_enqueue_script( 'angualar-timer-bower', IFROOT . '/assets/vendors/angular-timer/dist/assets/js/angular-timer-bower.js?t=' . ( (DEVMODE === true) ? time() : '' ) );
    //wp_enqueue_script( 'angualar-timer-all', IFROOT . '/assets/vendors/angular-timer/dist/assets/js/angular-timer-all.min.js?t=' . ( (DEVMODE === true) ? time() : '' ) );
    wp_enqueue_script('hypercortex-ang', IFROOT . '/assets/js/calculators.ang.js?t=' . ( (DEVMODE === true) ? time() : '' ) );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

function use_admin_enqueue_scripts()
{
  wp_enqueue_editor();
}
add_action( 'admin_enqueue_scripts', 'use_admin_enqueue_scripts' );

function app_enqueue_styles()
{
  wp_enqueue_style( 'app', IFROOT . '/assets/css/style.css?t=' . ( (DEVMODE === true) ? time() : '' ) );

}
add_action( 'wp_enqueue_scripts', 'app_enqueue_styles', 100 );


function add_opengraph_doctype( $output ) {
	return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"';
}
add_filter('language_attributes', 'add_opengraph_doctype');

function app_locale( $locale )
{
  /*
    $lang = explode('/', $_SERVER['REQUEST_URI']);
    if(array_pop($lang) === 'en'){
      $locale = 'en_US';
    }else{
      $locale = 'gr_GR';
    }*/
    //$locale = 'en_US';

    return $locale;
}

add_filter('locale','app_locale', 10);

function facebook_og_meta_header()
{
  global $wp_query;

  $title = get_option('blogname');
  $image = '';
  $desc  = get_option('blogdescription');
  $url   = get_option('site_url');

  echo '<meta property="fb:app_id" content="'.FB_APP_ID.'"/>'."\n";
  echo '<meta property="og:title" content="' . $title . '"/>'."\n";
  echo '<meta property="og:type" content="article"/>'."\n";
  echo '<meta property="og:url" content="' . $url . '/"/>'."\n";
  echo '<meta property="og:description" content="' . $desc . '/"/>'."\n";
  echo '<meta property="og:site_name" content="'.get_option('blogname').'"/>'."\n";
  echo '<meta property="og:image" content="' . $image . '"/>'."\n";

}
add_action( 'wp_head', 'facebook_og_meta_header', 5);

function fontawesome_header()
{
  ?>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
  <script type="text/javascript">
    (function($){
      $(function(){
        jQuery.each($('.autocorrett-height-by-width'), function(i,e){
          var delayed = $(e).data('delayed');
          delayed = (isNaN(delayed)) ? 0 : parseFloat(delayed);
          console.log(delayed);
          setTimeout(function(){
            var ew = $(e).width();
            var ap = $(e).data('image-ratio');
            var respunder = $(e).data('image-under');
        		var pw = $(window).width();
            ap = (typeof ap !== 'undefined') ? ap : '4:3';
            var aps = ap.split(":");
            var th = ew / parseInt(aps[0])  * parseInt(aps[1]);

            if (respunder) {
        			if (pw < respunder) {
        				$(e).css({
        	        height: th
        	      });
        			}
        		} else{
        			$(e).css({
                height: th
              });
        		}
          }, delayed);
        });
      });
    })(jQuery);
  </script>
  <?
}
add_action( 'wp_head', 'fontawesome_header', 99);

function avada_lang_setup() {
	$lang = get_stylesheet_directory() . '/langs';
  load_theme_textdomain('Avada', get_template_directory().'/languages');
	load_child_theme_textdomain( 'buso', $lang );
  $ucid = ucid();
  $ucid = $_COOKIE['uid'];
}
add_action( 'after_setup_theme', 'avada_lang_setup' );

function ucid()
{
  $ucid = $_COOKIE['ucid'];

  if (!isset($ucid)) {
    $ucid = mt_rand();
    setcookie( 'ucid', $ucid, time() + 60*60*24*365*2, "/");
  }

  return $ucid;
}

function app_custom_template($template) {
  global $post, $wp_query;

  if(isset($wp_query->query_vars['custom_page'])) {

    if ('jelentkezes' == $wp_query->query_vars['custom_page']) {
      add_filter( 'body_class','jelentkezes_class_body' );
      add_filter( 'document_title_parts', 'jelentkezes_custom_title' );
    }
    return get_stylesheet_directory() . '/'.$wp_query->query_vars['custom_page'].'.php';
  } else {
    return $template;
  }
}
add_filter( 'template_include', 'app_custom_template' );

function jelentkezes_class_body( $classes ) {
  $classes[] = 'jelentkezes-form';
  return $classes;
}

function jelentkezes_custom_title( $title )
{
  $title['title'] = __('Jelentkezés', 'buso');
  return $title;
}

function rd_init()
{
  date_default_timezone_set('Europe/Budapest');
  setlocale(LC_TIME, "hu_HU");

  add_rewrite_rule('^jelentkezes/([0-9]+)/?', 'index.php?custom_page=jelentkezes&ac_id=$matches[1]', 'top');
  create_custom_posttypes();
  add_post_type_support( 'page', 'excerpt' );

  // Kalkulátor admin settings
  $calculator = new Calculators();
  $calculator->adminSettings();
}
add_action('init', 'rd_init');

function app_query_vars($aVars) {
  //$aVars[] = "ac_id";
  return $aVars;
}
add_filter('query_vars', 'app_query_vars');

function prepost_limit_change( $query ){
    if( !is_admin() && $query->is_main_query() && $query->is_category() ){
      $query->set( 'posts_per_page', 8 );
    }
    return $query;
}
add_action( 'pre_get_posts', 'prepost_limit_change', 999 );

function create_custom_posttypes()
{
  // Videók
  /*
  $videok = new PostTypeFactory( 'videok' );
	$videok->set_textdomain( TD );
	$videok->set_icon('tag');
	$videok->set_name( 'Videó', 'Videók' );
	$videok->set_labels( array(
		'add_new' => 'Új %s',
		'not_found_in_trash' => 'Nincsenek %s a lomtárban.',
		'not_found' => 'Nincsenek %s a listában.',
		'add_new_item' => 'Új %s létrehozása',
	) );
  $program_metabox = new CustomMetabox(
    'programok',
    __('Program beállítások', 'buso'),
    new ProgramMetaboxSave(),
    'programok',
    array(
      'class' => 'programsettings-postbox'
    )
  );
  */

  /*
  $szoftver_settings_metabox = new CustomMetabox(
    'page',
    __('Szoftver beállítások', 'ai'),
    new SzofverSettingsContentMetaboxSave(),
    'softwaresettings',
    array(
      'class' => 'softwaresettings-postbox'
    )
  );

  $szoftver_metabox = new CustomMetabox(
    'page',
    __('Tartalom beállítások', 'ai'),
    new ProgramContentMetaboxSave(),
    'programcontents',
    array(
      'class' => 'programsettings-postbox'
    )
  );

  $szoftver_modul_metabox = new CustomMetabox(
    'page',
    __('Szoftver modulok', 'ai'),
    new SzofverModulContentMetaboxSave(),
    'softwaremodules',
    array(
      'class' => 'softwaremodules-postbox'
    )
  );
  */

}

function wpsites_query( $query ) {
  global $wp_query;
  if ( $query->is_archive() && $query->is_main_query() && !is_admin() ) {
    $ppp = 30;
    if ($wp_query->query_vars['post_type'] == 'videok') {
      $ppp = 4;
    }
    $query->set( 'posts_per_page', $ppp );
  }
}
add_action( 'pre_get_posts', 'wpsites_query' );

function rd_query_vars($aVars) {
  return $aVars;
}
add_filter('query_vars', 'rd_query_vars');

/**
* AJAX REQUESTS
*/
function ajax_requests()
{
  $ajax = new AjaxRequests();
  $ajax->contact_form();
  $ajax->calc_api_interface();
  $ajax->calc_settings();
}
add_action( 'init', 'ajax_requests' );

// AJAX URL
function get_ajax_url( $function )
{
  return admin_url('admin-ajax.php?action='.$function);
}
function custom_js_codes () {
  ?>
  <script>
  </script>
  <?
}
add_action('wp_footer', 'custom_js_codes');

/* GOOGLE ANALYTICS */
if( defined('DEVMODE') && DEVMODE === false ) {
	function ga_tracking_code () {
		?>
		<script>


		</script>
		<?
	}
	add_action('wp_footer', 'ga_tracking_code');
}

function memory_convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}

function admin_external_scripts( $hook )
{
  wp_enqueue_script('jquery-ui-datepicker');

  wp_register_style( 'jquery-ui', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css' );
  wp_enqueue_style( 'jquery-ui' );
  //wp_enqueue_style('ang-colorpicker', IFROOT . '/assets/vendors/angular-colorpicker/css/color-picker.min.css' );

  //wp_enqueue_script('angularjs', '//cdnjs.cloudflare.com/ajax/libs/angular.js/1.6.5/angular.min.js');
  //wp_enqueue_script('ang-colorpicker', IFROOT . '/assets/vendors/angular-colorpicker/js/color-picker.min.js' );
  //wp_enqueue_script('szinvalaszto-ang', IFROOT . '/assets/js/szinvalaszto.ang.js?t=' . ( (DEVMODE === true) ? time() : '' ) );
}
add_action( 'admin_enqueue_scripts', 'admin_external_scripts' );

add_action('admin_head', 'my_custom_fonts', 999);
function my_custom_fonts() {
  echo '<style>

    .avadaredux-container #avadaredux-form-wrapper .avadaredux-main .wp-picker-container{
      height: 25px !important;
      position: relative !important;
    }
    .wp-color-result{
      float:left !important;
    }
    .avadaredux-container #avadaredux-form-wrapper .avadaredux-main .avadaredux-container-color .iris-picker .iris-picker-inner > .iris-strip.iris-alpha-slider, .avadaredux-container #avadaredux-form-wrapper .avadaredux-main .avadaredux-container-color_alpha .iris-picker .iris-picker-inner > .iris-strip.iris-alpha-slider, .avadaredux-container #avadaredux-form-wrapper .avadaredux-main .avadaredux-typography-container .wp-picker-container .iris-picker .iris-picker-inner > .iris-strip.iris-alpha-slider{
      margin-left: 15px !important;
    }
    .avadaredux-container #avadaredux-form-wrapper .avadaredux-main .avadaredux-container-color input[type="text"], .avadaredux-container #avadaredux-form-wrapper .avadaredux-main .avadaredux-container-color_alpha input[type="text"], .avadaredux-container #avadaredux-form-wrapper .avadaredux-main .avadaredux-typography-container .wp-picker-container input[type="text"]{
      display: block !important;
      float: left !important;
    }
    table.'.TD.'{
      width: 100%;
    }
    table.'.TD.' td{
      padding: 10px;
      vertical-align: top;
    }
    table.'.TD.' input[type=text],
    table.'.TD.' input[type=time],
    table.'.TD.' input[type=number],
    table.'.TD.' select{
      width: 100%;
      padding: 8px;
      height: auto;
    }
    .programcontents > .wrapper{
      display: -webkit-box;
      display: -ms-flexbox;
      display: flex;
      flex-wrap: wrap;
      align-items: flex-start;
    }
    .programcontents > .wrapper > .ct-groups{
      flex-basis: 30%;
    }
    .programcontents > .wrapper > .ct-groups .inputs .newinputs .new{
      margin: 5px 0;
    }
    .programcontents > .wrapper > .ct-groups .inputs .input:not(.new){
      background: #e8e8e8;
      padding: 5px 5px 5px 30px;
      margin: 2px 0;
      position: relative;
    }
    .programcontents > .wrapper > .ct-groups .inputs .input:not(.new) i{
      position: absolute;
      left: 12px;
      top: 50%;
      color: #c3c2c2;
      font-size: 13px;
      -webkit-transform: translateY(-50%);
          transform: translateY(-50%);
    }
    .programcontents > .wrapper > .ct-groups .new-adder{
      text-align: right;
      margin: 5px 0;
      display: block;
      text-decoration: none;
    }
    .programcontents > .wrapper > .ct-sets{
      flex: 1;
      padding: 0 0 0 25px;
    }
    .programcontents > .wrapper > .ct-sets .content-sets .set{
      margin: 0 0 15px 0;
    }
    .programcontents > .wrapper > .ct-sets .content-sets .set input[type=text] {
        width: 100%;
        margin: 5px 0;
    }
    .programcontents > .wrapper > .ct-sets .content-sets .set label {
      display: block;
      margin: 0 0 10px 0;
      font-size: 1.2em;
    }
    .programcontents > .wrapper > .ct-groups input[type=text]{
      width: 100%;
    }
    .modulcontents .content-sets .set > label{
      font-size: 1.2rem;
      font-weight:bold;
    }
    .modulcontents .content-sets .set .input .title input {
      color: #3ab83a;
      font-size: 1.1rem;
      padding: 10px;
      font-weight: bold;
    }
    .modulcontents .content-sets .set .input .title {
      display: -webkit-box;
      display: -ms-flexbox;
      display: flex;
      flex-wrap: nowrap;
      align-items:center;
    }
    .modulcontents .content-sets .set .input .title input {
      flex: 1;
    }
    .modulcontents .content-sets .set .sorting{
      border: 1px solid #e6e6e6;
      border-left: none;
      background: #f1f1f1;
      line-height: 44px;
      flex-basis: 50px;
      text-align: center;
      cursor: move;
    }
  </style>';
}

function auto_update_post_meta( $post_id, $field_name, $value = '' )
{
    if ( empty( $value ) OR ! $value )
    {
      delete_post_meta( $post_id, $field_name );
    }
    elseif ( ! get_post_meta( $post_id, $field_name ) )
    {
      add_post_meta( $post_id, $field_name, $value );
    }
    else
    {
      update_post_meta( $post_id, $field_name, $value );
    }
}

function getRecommendedPostIDSByTags( $posttype = 'posts', $postid = 0 )
{
  $tagids = array();
  $tags = wp_get_post_tags( $postid );

  foreach ( (array)$tags as $tag ) {
    $posts = new WP_Query(array(
      'post_type' => $posttype,
      'tag__in' => $tag->term_id,
      'posts_per_page' => -1,
      'post__not_in' => array($postid)
    ));

    if ($posts->have_posts()) {
      while ( $posts->have_posts() ) {
        $posts->the_post();
        $tagids[get_the_ID()]['postid'] = get_the_ID();
        $tagids[get_the_ID()]['tn'] += 1;
        $tagids[get_the_ID()]['tags'][] = $tag->name;
    	}
      wp_reset_postdata();
    }
  }

  usort($tagids, function ($item1, $item2) {
    if ($item1['tn'] == $item2['tn']) return 0;
    return $item1['tn'] < $item2['tn'] ? 1 : -1;
  });

  foreach ( (array)$tagids as $ti ) {
    $ids[] = $ti['postid'];
  }
  unset($tagids);
  unset($posts);

  return $ids;
}

//Exclude pages from WordPress Search
if (!is_admin()) {
  function wpb_search_filter( $query ) {
    if ($query->is_search) {
      $query->set('post_type', 'post');
    }
    return $query;
  }
  add_filter('pre_get_posts','wpb_search_filter');
}
