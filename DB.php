<?php
class DB {  
  public $conn;
  
  /**
   * 
   * @param string $host
   * @param string $user
   * @param string $pass
   * @param string $db
   */
  function __construct($host, $user, $pass, $db){
    try {
      $this->conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    } catch (PDOException $e) {
      die("Failed to connect to database '$db' - {$e->getMessage()}");
    }
  }
  
  public function query($query){
    try {
      $res = $this->conn->query($query);
      $results = new DBRecordWrapper();
      if ($res && $res->rowCount()){        
        foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $row){
          $results->add($row);
        }
      }
      return $results;
    } catch (PDOException $e) {
      die("Failed to query database - {$e->getMessage()}");
    }
  }
  
  public function statement($query){
    try {
      $this->conn->query($query);
    } catch (PDOException $e) {
      die("Failed to query database - {$e->getMessage()}");
    }
  }
}