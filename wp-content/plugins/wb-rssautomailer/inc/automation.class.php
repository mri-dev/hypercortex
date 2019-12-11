<?php

class AutomationWG
{
  public $wg;
  function __construct( $arr = array() )
  {
    $this->wg = $arr['wg'];
  }

  public function startWatch()
  {
    add_action('save_post', array($this, 'watch_save_post'), 10, 3);
  }

  public function watch_save_post( $post_id, $post, $update )
  {
    if ( wp_is_post_revision( $post_id ) ) {  return;  }
    if ( !$this->wg ) { return; }

    $post_categories = (array)$_POST['post_category'];

    $list = $this->getList(array('active' => 1));
    $catok = array();

    foreach ((array)$list as $li)
    {
      if (empty($li['cats'])) {
        $catok[] = $li;
      } else {
        foreach ((array)$li['cats'] as $ci) {
          if (in_array($ci, $post_categories)) {
            $catok[] = $li;
          }
        }
      }
    }

    if (!empty($catok)) {
      foreach ( (array)$catok as $ca ) {
        if ( !$this->isSendedBefore( $ca['ID'], $post_id) ) {
          $this->sendingWGAutomation( $ca, $post_id );
        }
      }
    }
  }

  public function sendingWGAutomation( $auto_config, $post_id )
  {
    global $wpdb, $table_prefix;
    $wgsuccess = false;

    $wg_group_id = $auto_config['wg_group_id'];
    $wg_mail_id = $auto_config['wg_mail_id'];

    $wgsuccess = $this->wg->sending( $wg_group_id, $wg_mail_id, $auto_config );

    if ( $wgsuccess ) {
      $tbl = $table_prefix.WGRSS_DB_PREFIX.'sended';
      $wpdb->insert(
        $tbl,
        array(
          'config_id' => $auto_config['ID'],
          'item_id' => $post_id,
          'recepients' => $wgsuccess
        )
      );
    }
  }

  public function isSendedBefore( $cif, $id )
  {
    global $wpdb, $table_prefix;
    $tbl = $table_prefix.WGRSS_DB_PREFIX.'sended';
    $qry = "SELECT ID FROM {$tbl} WHERE config_id = {$cif} and item_id = {$id}";

    $data = $wpdb->get_row($qry, ARRAY_A);

    if ($data) {
      return true;
    } else {
      return false;
    }
  }

  function getItem( $id)
  {
    global $wpdb, $table_prefix;

    if (empty($id) || !is_numeric($id)) {
      return false;
    }

    $tbl = $table_prefix.WGRSS_DB_PREFIX.'automations';
    $qry = "SELECT * FROM $tbl WHERE ID = {$id}";
    $data = $wpdb->get_row($qry, ARRAY_A);

    $data['cats'] = (is_null($data['category_id']) || empty($data['category_id'])) ? array() : explode(",", $data['category_id']);
    $data['mezok'] = unserialize($data['mezok']);

    return $data;
  }


  public function getList( $arg = array() )
  {
    global $wpdb, $table_prefix;
    $tbl = $table_prefix.WGRSS_DB_PREFIX.'automations';
    $qry = "SELECT * FROM {$tbl} WHERE 1=1 ";
    if (isset($arg['active'])) {
      $qry .= " and active = '{$arg['active']}'";
    }
    $qry .= " ORDER BY created_at DESC";
    $list = $wpdb->get_results($qry, ARRAY_A);

    $data = array();

    foreach ((array)$list as $l) {
      if (is_null($l['category_id']) || empty($l['category_id'])) {
        $l['cats'] = array();
      } else {
        $l['cats'] = explode(",", $l['category_id']);
        foreach ((array)$l['cats'] as $c) {
          $l['cats_obj'][] = get_term($c);
        }
      }
      $data[] = $l;
    }

    return $data;
  }

  public function __destruct()
  {
    $this->wg = null;
  }
}
