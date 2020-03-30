<?php
class Barrios{
    private $con;
    public $file;
    private $id;
    private $nombre;
    private $estado;
    private $nombreTabla;

    public function __construct() {
        global $wpdb;
        $this->file = __FILE__;
        $this->con = $wpdb;
        $this->nombreTabla = $this->con->prefix.'eres_barrios';
        $this->nombre = '';
        $this->estado = 'A';
    }
    public function getId(){
        return $this->id;
    }
    public function getNombre(){
        return $this->nombre;
    }
    public function setNombre($nombre){
        $this->nombre = $nombre;
    }
    public function getAllList(){
        $param = array($this->estado);
        $results = $this->con->get_results( 
            $this->con->prepare(
                "SELECT 
                    id,
                    nombre
                FROM 
                    $this->nombreTabla 
                WHERE 
                    estado=%s
                ORDER BY
                    nombre ASC", 
            $param) 
        );
        $barrios = array();
        if(isset($results) && is_array($results) && count($results)>0){
            foreach($results as $item){
                $this->id           = $item->id;
                $this->nombre       = $item->estado;
                $barrios[] = array(
                    'id'        => $item->id,
                    'nombre'    => $item->nombre,
                );
            }
        }
        return $barrios;
    }

}