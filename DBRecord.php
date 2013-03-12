<?php
/**
 * DBRecord Class
 * --------------
 * Takes a database table and creates an object
 * 
 * @author Ryan <ryanthomaskendall@gmail.com>
 */
class DBRecord {
  const INSERT = 0;
  const UPDATE = 1; 
  const DELETE = 2;
  const SELECT = 3;

  /** @var $TBL the name of the database table 
      this object represents a row of.
      If null will assume table name is
      the name of the class in lowercase
      pluralized with an 's' 
  **/
  public static $TBL = null;
  public $new = false;
  
  /**
   * @param array $data leave null if you are creating a row
   * @param boolean $non_query set to true when you already have objects data
   **/
  function __construct($data=null, $non_query=false) {
    $this->tbl = static::getDBTable();
    if (!$data){
      $this->new = true;
    } else if ($non_query){
      $this->recieveData($data);
    } else {
      global $db;
      $sql = 'SELECT * FROM '.$this->tbl.' WHERE '.$this->generateSQLConditions($data, true);
      $res = $db->query($sql);
      if ($res->size()){
        $this->recieveData($res->getFirst());
      } else {
        $this->new = true;
      }
    }
  }

  public function __callStatic($name, $arguments){
    if (substr($name, 0, 6) == 'findBy'){
      $request = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', substr($name, 6)));
      return self::findBy($request, $arguments);      
    }
    die ('Undefined method: '.$name);
  }
  
  public function isNew(){
    return $this->new;
  }
  
  public function getExcludedColumns(){
    return array('tbl', 'new');
  }
  
  public function save(){
    global $db;
    $sql = $this->generateSQL(($this->isNew()?self::INSERT : self::UPDATE));
    $db->statement($sql);
    if ($this->isNew()){
      $this->id = $db->conn->lastInsertId();
      $this->new = false;
    }
  }
  
  public function delete(){
    global $db;
    $sql = $this->generateSQL(self::DELETE);
    $db->statement($sql);
  }

  private function recieveData($data){
    foreach ($data as $col => $val){
      $this->$col = $val;
    }
  }

  private static function getDBTable(){
    if (!static::$TBL){
      static::$TBL = strtolower(get_called_class().'s');
    }
    return static::$TBL;
  }
  
  private function getSaveableData($data=null){
    if (!$data){
      $vars = get_object_vars($this);
    } else {
      $vars = $data;
    }    
    $ignore = $this->getExcludedColumns();
    foreach ($vars as $column => $value){
      if (in_array($column, $ignore)){
        unset($vars[$column]);
        continue;
      }
      if (!is_numeric($value)){
        $vars[$column] = '"'.$value.'"';
      }      
    }
    return $vars;
  }
  
  private function generateSQLConditions($data=null, $type=DBRecord::UPDATE){
    $data = $this->getSaveableData(($data?$data:null));
    $sql = '';
    $i = 1;
    $query_size = sizeof($data);
    foreach ($data as $col => $val){
      $sql .= "$col = $val".($i==$query_size?' ':($type==static::SELECT?' AND ':', '));
      $i++;
    }
    return $sql;
  }
  
  private function generateSQL($method, $data=null){
    if (!$data){
      $data = $this->getSaveableData();
    }    
    switch ($method){
      case self::INSERT:
        $sql = 'INSERT INTO '.$this->tbl.' ('.implode(', ', array_keys($data)).') VALUES ('.implode(', ', $data).')';
        break;
      case self::UPDATE:
        $sql = 'UPDATE '.$this->tbl.' SET '.$this->generateSQLConditions();
        break;
      case self::DELETE:
        $sql = 'DELETE FROM '.$this->tbl.' WHERE '.$this->generateSQLConditions(null, static::SELECT);
        break;
      default:
        throw new Exception('SQL Method not found, default case used');
    }
    return $sql;
  }

  private static function findBy($column, $args){
    if (empty($args)){
      return new DBRecordWrapper();
    }
    global $db;
    $tbl = static::getDBTable();
    $class = get_called_class();
    $data = $args[0];
    $sql = 'SELECT * FROM '.$tbl.' WHERE '.$column.' = '.(is_numeric($data)?$data:'"'.$data.'"');
    $res = $db->query($sql);
    $results = new DBRecordWrapper();
    if ($res->size()){
      foreach ($res as $result){        
        $obj = new $class($result, true);
        $results->add($obj);
      }
    }
    return $results;
  }
}
