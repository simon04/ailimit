<?php

/**
 * commons-aiuser
 * @license GPL 3
 * @author simon04
 */

error_reporting(E_ALL ^ E_NOTICE);

header('Access-Control-Allow-Origin: *');

class UserFiles {

  public function __construct() {
    $ts_mycnf = parse_ini_file(__DIR__ . "/../replica.my.cnf");
    $this->db = new PDO("mysql:host=commonswiki.labsdb;dbname=commonswiki_p",
      $ts_mycnf['user'], $ts_mycnf['password']);
    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  public function fetch($user) {
    $sql = "select img_name, img_timestamp, img_metadata from image where img_actor in (select actor_id from actor where actor_name = :user)";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(array(':user' => $user));
    $data = [];
    while ($row = $stmt->fetch()) {
      $metadata = unserialize($row['img_metadata']);
      $metadata['File'] = $row['img_name'];
      $metadata['DateTimeUpload'] = $row['img_timestamp'];
      $data[] = $metadata;
    }
    return $data;
  }

}

if (!$_REQUEST['user']) {
  header('HTTP/1.1 400');
  echo "commons-aiuser by simon04 (licensed GPL 3)\n";
  echo "Required parameters: user\n";
  exit();
}

$userFiles = new UserFiles();
$data = $userFiles->fetch($_REQUEST['user']);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);
