<?php
/**
 * Plugin Name: Webgalamb RSS Automailer
 * Description: Újonnan létrehozott cikkek kiküldése RSS csatornán keresztül a Webgalamb rendszerével, meghatározott feliratkozó csoportnak.
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

  	static function plugin_activation() {
      self::$instance->prepareDatabase();
      delete_option( WGRSS_DB_PREFIX.'active', 1, '', 'yes' );
  		flush_rewrite_rules();
  	}

  	static function plugin_deactivation() {
  		flush_rewrite_rules();
  		delete_option( WGRSS_DB_PREFIX.'active' );
  	}

    function __construct()
    {
      // code...
    }

    public static function instance() {
      if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WGRSSMailer ) ) {
        self::$instance = new WGRSSMailer;
        defined( 'WGRSS_INC' ) || define( 'WGRSS_INC', plugin_dir_path( __FILE__ ).'inc/' );
        self::$instance->includes();

        add_action( 'init', array( self::$instance, 'init' ) );

        self::$instance->add_actions();
      }

      return self::$instance;
    }

    public function init()
    {
      $this->settings = new WGRSSMailer_Settings();
  	}

    private function includes()
    {
      require_once WGRSS_INC . 'settings.php';
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
            `category_id` mediumint(9) NOT NULL,
            `wg_group_id` smallint(6) NOT NULL,
            `wg_mail_id` smallint(6) NOT NULL,
            PRIMARY KEY (`ID`),
            UNIQUE KEY (`category_id`)
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
            `sended_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`ID`),
            INDEX (`config_id`)
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
