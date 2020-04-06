<?php
class Prestamo{
    private $con;
    public $file;
    private $id;
    private $cuotas;
    private $total;
    private $fechaprestamo;
    private $interes;
    private $fechacorte;
    private $nombreTabla;

    public function __construct() {
        global $wpdb;
        $this->file = __FILE__;
        $this->con = $wpdb;
        $this->nombreTabla = $this->con->prefix.'eres_prestamos';
        $this->cuotas = 0;
        $this->total = 0;
        $this->fechaprestamo = '';
        $this->dia = 0;
        $this->interes=0;
    }
    public function getId(){
        return $this->id;
    }
    public function getCuotas(){
        return $this->cuotas;
    }
    public function setCuotas($cuotas){
        if(isset($cuotas) && is_numeric($cuotas) && $cuotas>0){
            $this->cuotas = $cuotas;
        }else{
            throw new Exception("El numero de cuotas debe ser numerico");
        }
    }
    public function getTotal(){
        return $this->total;
    }
    public function setTotal($total){
        if(isset($total) && is_numeric($total) && $total>0){
            $this->total = $total;
        }else{
            throw new Exception("El numero de cuotas debe ser numerico");
        }
    }
    public function getFechaPrestamo(){
        return $this->fechaprestamo;
    }
    public function setFechaPrestamo($fechaprestamo){
        $this->fechaprestamo = $fechaprestamo;
    }
    public function getInteres(){
        return $this->interes;
    }
    public function setInteres($interes){
        $this->interes = $interes;
    }
    public function getDiaCorte(){
        return $this->dia;
    }
    public function setDiaCorte($dia){
        $this->dia = $dia;
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