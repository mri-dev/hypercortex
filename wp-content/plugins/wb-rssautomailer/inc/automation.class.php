<?php
class AutomationWG
{
  function getItem( $id)
  {
    global $wpdb, $table_prefix;

    if (empty($id) || !is_numeric($id)) {
      return false;
    }

    $tbl = $table_prefix.WGRSS_DB_PREFIX.'automations';
    $qry = "SELECT * FROM $tbl WHERE ID = {$id}";
    return $wpdb->get_row($qry, ARRAY_A);
  }
}
