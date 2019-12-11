<?php
/**
 * Plugin Name: Webgalamb Automailer
 * Description: Újonnan létrehozott cikkek kiküldése a Webgalamb rendszerével, meghatározott feliratkozó csoportnak.
 * Author: WEBPRO Solutions Bt.
 * Author URI: https://www.web-pro.hu/
 * Version: 1.0
 * Text Domain: wbrss
 * Domain Path: /langs
 *
 * Copyright 2019 Molnár István molnar.istvan@web-pro.hu
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WGRSS_DB_PREFIX', 'wgrss_');

if ( ! class_exists( 'WGRSSMailer' ) ) :
  final class WGRSSMailer
  {
    private static $instance;

    public $settings;
		public $wgapi;
		public $wg;
		public $automation;

  	static function plugin_activation() {
      self::$instance->prepareDatabase();
      add_option( WGRSS_DB_PREFIX.'active', 1, '', 'yes' );
			add_option( WGRSS_DB_PREFIX.'wg_db_username', '', '', 'yes' );
			add_option( WGRSS_DB_PREFIX.'wg_db_name', '', '', 'yes' );
			add_option( WGRSS_DB_PREFIX.'wg_db_pw', '', '', 'yes' );
			add_option( WGRSS_DB_PREFIX.'wg_db_prefix', '', '', 'yes' );
  		flush_rewrite_rules();
  	}

  	static function plugin_deactivation() {
  		flush_rewrite_rules();
  		delete_option( WGRSS_DB_PREFIX.'active' );
			delete_option( WGRSS_DB_PREFIX.'wg_db_username');
			delete_option( WGRSS_DB_PREFIX.'wg_db_name');
			delete_option( WGRSS_DB_PREFIX.'wg_db_pw');
			delete_option( WGRSS_DB_PREFIX.'wg_db_prefix');
  	}

    function __construct()
    {
      // code...
    }

    public static function instance() {
      if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WGRSSMailer ) ) {
        self::$instance = new WGRSSMailer;

				defined( 'WGRSS_DIR_URL' ) || define( 'WGRSS_DIR_URL', plugin_dir_url( __FILE__ ) );
        defined( 'WGRSS_INC' ) || define( 'WGRSS_INC', plugin_dir_path( __FILE__ ).'inc/' );
				defined( 'WGRSS_TEMPLATES' ) || define( 'WGRSS_TEMPLATES', plugin_dir_path( __FILE__ ).'templates/' );

        self::$instance->includes();

        add_action( 'init', array( self::$instance, 'init' ) );

        self::$instance->add_actions();
      }

      return self::$instance;
    }

    public function init()
    {
			// Webgalambhoz használt MySQL adatbázis adatai
			$db_host = "localhost"; // adatbázis kiszolgáló címe
			$db_user = get_option( WGRSS_DB_PREFIX.'wg_db_username', '' ); // adatbázis felhasználónév
			$db_name = get_option( WGRSS_DB_PREFIX.'wg_db_name', '' ); // adatbázis neve
			$db_password = get_option( WGRSS_DB_PREFIX.'wg_db_pw', '' ); // felhasználó jelszava
			$db_prefix = get_option( WGRSS_DB_PREFIX.'wg_db_prefix', '' ); // Webgalamb 7 prefix, alap telepítés esetén => wg7_
			$this->wg = new WG($db_prefix, $db_host, $db_name, $db_user, $db_password);

			$this->settings = new WGRSSMailer_Settings(array(
				'wg' => $this->wg
			));

			$this->automation = new AutomationWG(array(
				'wg' => $this->wg
			));
			$this->automation->startWatch();
  	}

    private function includes()
    {
			require_once WGRSS_INC . 'automation.class.php';
      require_once WGRSS_INC . 'settings.php';
			require_once WGRSS_INC . 'wg7api.php';
			require_once WGRSS_INC . 'wg.php';
    }

    private static function prepareDatabase()
    {
      global $table_prefix, $wpdb;
      require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

      $queries = array();

      $tblname = 'automations';
      $wp_track_table = $table_prefix . WGRSS_DB_PREFIX . $tblname;
      if($wpdb->get_var( "show tables like '$wp_track_table'" ) != $wp_track_table)
      {
          $sql = "CREATE TABLE `".$wp_track_table."` (
            `ID` int(6) NOT NULL AUTO_INCREMENT,
						`title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `category_id` text COLLATE utf8mb4_unicode_ci,
            `wg_group_id` smallint(6) NOT NULL,
            `wg_mail_id` smallint(6) NOT NULL,
						`mezok` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  					`active` tinyint(1) NOT NULL DEFAULT '1',
						`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`ID`),
						INDEX (`active`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
          $queries[] = $sql;
      }

      $tblname = 'sended';
      $wp_track_table = $table_prefix . WGRSS_DB_PREFIX . $tblname;
      if($wpdb->get_var( "show tables like '$wp_track_table'" ) != $wp_track_table)
      {
          $sql = "CREATE TABLE `".$wp_track_table."` (
            `ID` int(8) NOT NULL AUTO_INCREMENT,
            `config_id` int(6) NOT NULL,
            `item_id` int(8) NOT NULL,
            `recepients` MEDIUMINT NOT NULL DEFAULT '0',
            `sended_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`ID`),
            INDEX (`config_id`),
            INDEX (`item_id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
          $queries[] = $sql;
      }

      dbDelta($queries);
    }

    private function add_actions() {
  		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
  	}

    public function load_textdomain() {
      load_plugin_textdomain( 'wb-rssautomailer', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
    }
  }
endif;

register_activation_hook( __FILE__, array( 'WGRSSMailer', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'WGRSSMailer', 'plugin_deactivation' ) );

function WGRSS() {
	return WGRSSMailer::instance();
}

WGRSS();
