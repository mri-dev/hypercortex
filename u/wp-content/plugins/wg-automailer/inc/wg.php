<?php /**
 *
 */
class WG
{
  private $db_prefix;
  private $db_host;
  private $db_name;
  private $db_user;
  private $db_password;
  public $wgdberror;

  private $api;

  function __construct( $db_prefix, $db_host, $db_name, $db_user, $db_password )
  {
    $this->db_prefix = $db_prefix;
    $this->db_host = $db_host;
    $this->db_name = $db_name;
    $this->db_user = $db_user;
    $this->db_password = $db_password;

    $this->connect();

    return $this;
  }

  public function sending( $group_id, $mail_id, $config )
  {
    if ( !$this->wgdberror )
    {
      $sended = 0;
      $users = $this->api->GetGroupUserIDs( $group_id );
      $mezok = $this->connectMezok( $config );

      foreach ((array)$users as $uid)
      {
        // Mezők frissítése az aktuálisra
        $this->api->EditSubscriber($mezok, $uid, $group_id);

        // Levélkiküldés rögzítése
        $send = $this->api->WG_send_mail( $mail_id, $group_id, $uid );
        if ( $send ) {
          $sended++;
        }
      }

      if ( $sended > 0 ) {
        return $sended;
      }

    } else {
      return false;
    }
  }

  private function connectMezok( $config )
  {
    $mezok = array();

    $xref = unserialize($config['mezok']);

    foreach ((array)$xref as $key => $wgmezo) {
      if (!empty($wgmezo)) {
        $wgmezo = str_replace(array('{','}', 'mezo_'),'', $wgmezo);
        $mezok[$wgmezo] = $this->bindValueToMezo( $key );
      }
    }

    return $mezok;
  }

  private function bindValueToMezo( $key )
  {
    global $post;
    $value = null;

    switch ( $key ) {
      case 'post_title':
        $value = $post->post_title;
      break;
      case 'post_excerpt':
        $value = $post->post_excerpt;
      break;
      case 'post_date':
        $value = date('Y / m / d', strtotime($post->post_date));
      break;
      case 'permalink':
        $value = get_permalink( $post->ID );
      break;
    }

    return $value;
  }

  private function connect()
  {
    try {
      $dsn = 'mysql:dbname='.$this->db_name.';host='.$this->db_host;
      $con = new PDO($dsn, $this->db_user, $this->db_password);
      $test_table = $con->query("SELECT 1 FROM ".$this->db_prefix."users LIMIT 0,1");
      if ($test_table) {
          $this->api = new WG7_API($this->db_prefix, $this->db_host, $this->db_name, $this->db_user, $this->db_password);
      } else {
        $this->wgdberror = 'Nincs telepítve a Webgalamb szoftver erre a szerverre.';
      }
      $con = null;
    } catch (PDOException $e) {
      $this->wgdberror = $e->getMessage();
    }

    return $this;
  }
}
?>
