<?php
if(!defined('DB_SERVER')) {
  die('include config.php first!');
}

class Database{
  public function __construct($server, $name, $username, $password) {
    try {
      $this->con = new PDO("mysql:host={$server};dbname={$name}", $username, $password);
    } catch (Exception $e) {
      die(json_encode(array('error'=>'db connect failed!')));
    }
  }

  public function execute($sql) {
    $params = func_get_args();
    array_shift($params);
    $prep = $this->con->prepare($sql);
    $prep->setFetchMode(PDO::FETCH_OBJ);
    $i = 0;
    foreach($params as $param){
      $prep->bindValue(++$i, $param, $this->arg_type($param));
    }
    $prep->execute();
    return $prep;
  }

  private function arg_type($value){
      if(is_int($value)) {
        return PDO::PARAM_INT;
      }
      return PDO::PARAM_STR;
  }
}


$con = new Database(
  DB_SERVER,
  DB_NAME,
  DB_USERNAME,
  DB_PASSWORD
);

