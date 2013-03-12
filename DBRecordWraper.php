<?php
class DBRecordIterator implements Iterator {
  public function __construct($array){
    $this->var = $array;
  }
  public function rewind(){
    reset($this->var);
  }
  public function current(){
    return current($this->var);
  }
  public function key(){
    return key($this->var);
  }
  public function next(){
    return next($this->var);
  }
  public function valid(){
    $key = key($this->var);
    return ($key !== NULL && $key !== FALSE);
  }
}

class DBRecordWrapper implements IteratorAggregate {
  function __construct() {
    $this->data = array();
  }
  public function add($row){
    $this->data[] = $row;
  }
  public function size(){
    return sizeof($this->data);
  }
  public function getFirst(){
    return $this->data[0];
  }
  public function getData(){
    return $this->data;
  }
  public function getIterator() {
    return new DBRecordIterator($this->data);
  }
}
