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

  private function connect()
  {
    try {
      $dsn = 'mysql:dbname='.$this->db_name.';host='.$this->db_host;
      $con = new PDO($dsn, $this->db_user, $this->db_password);
      $this->api = new WG7_API($this->db_prefix, $this->db_host, $this->db_name, $this->db_user, $this->db_password);
      $con = null;
    } catch (PDOException $e) {
      $this->wgdberror = $e->getMessage();
    }
  }
}
?>
