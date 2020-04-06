<?php
class Config{
    private $con;
    public $file;
    private $id;
    private $key;
    private $value;
    private $nombreTabla;

    public function __construct() {
        global $wpdb;
        $this->file = __FILE__;
        $this->con = $wpdb;
        $this->nombreTabla = $this->con->prefix.'eres_config';
        $this->key = '';
        $this->value = '';
    }
    public function getId(){
        return $this->id;
    }
    public function getKey(){
        return $this->key;
    }
    public function setKey($key){
        $this->key = $key;
    }
    public function getValue(){
        return $this->value;
    }
    public function setValue($value){
        $this->value = $value;
    }
    public function getConfiguration($key){
        $this->key = $key;
        $param = array($this->key);
        $results = $this->con->get_results( 
            $this->con->prepare(
                "SELECT 
                    id,
                    cvalue
                FROM 
                    $this->nombreTabla 
                WHERE 
                    ckey=%s
                ORDER BY
                    cvalue ASC", 
            $param) 
        );
        $barrios = array();
        $this->value = '';
        if(isset($results) && is_array($results) && count($results)>0){
            foreach($results as $item){
                $this->id           = $item->id;
                $this->value       = $item->cvalue;
            }
        }
        return $this->value;
    }
    public function getListConfiguration($key){
        $this->key = $key;
        $param = array($this->key);
        $results = $this->con->get_results( 
            $this->con->prepare(
                "SELECT 
                    id,
                    cvalue
                FROM 
                    $this->nombreTabla 
                WHERE 
                    ckey=%s
                ORDER BY
                    cvalue ASC", 
            $param) 
        );
        $list = array();
        $this->value = '';
        if(isset($results) && is_array($results) && count($results)>0){
            foreach($results as $item){
                $list[] = array(
                    'id' => $item->id,
                    'value' => $item->cvalue
                );
            }
        }
        return $list;
    }

}