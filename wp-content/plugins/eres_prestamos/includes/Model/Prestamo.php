<?php
class Prestamo{
    private $con;
    public $file;
    private $id;
    private $id_customer;
    private $cuotas;
    private $total;
    private $fecha_prestamo;
    private $interes;
    private $fechacorte;
    private $estado;
    private $nombreTabla;
    private $dia_corte;
    private $maxvalorprestamo=0;
    private $objcuotas;

    public function __construct($max=0) {
        global $wpdb;
        $this->file             = __FILE__;
        $this->con              = $wpdb;
        $this->nombreTabla      = $this->con->prefix.'eres_prestamos';
        $this->cuotas           = 0;
        $this->total            = 0;
        $this->fecha_prestamo   = '';
        $this->dia              = 0;
        $this->estado           = 'P';
        $this->id_customer      = 0;
        $this->interes          = 0;
        $this->dia_corte        = 0;
        $this->maxvalorprestamo = $max;
        $this->objcuotas        = array();
    }
    public function getId(){
        return $this->id;
    }
    public function getIdCustomer(){
        return $this->id_customer;
    }
    public function setIdCUstomer($id_customer){
        $this->id_customer = $id_customer;
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
        return $this->fecha_prestamo;
    }
    public function setFechaPrestamo($fecha_prestamo){
        $this->fecha_prestamo = $fecha_prestamo;
    }
    public function getInteres(){
        return $this->interes;
    }
    public function setInteres($interes){
        $this->interes = $interes;
    }
    public function getDiaCorte(){
        return $this->dia_corte;
    }
    public function setDiaCorte($dia_corte){
        $this->dia_corte = $dia_corte;
    }
    public function getEstado(){
        return $this->estado;
    }
    public function setEstado($estado){
        $this->estado = $estado;
    }
    public function getObjetoCuotas(){
        return $this->objcuotas;
    }
    public function save(){
        try{
            if(isset($this->id) && is_numeric($this->id) && $this->id>0){
                $this->update();
            }else{
                $this->create();
            }
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    private function create(){
        try{
            if($this->isLoanWithoutClose()){
                throw new Exception("No se puede realizar solicitud porque tiene una activa");
            }
            if($this->isFirstLoan()){
                if($this->maxvalorprestamo!=0){
                    if($this->total > $this->maxvalorprestamo){
                        throw new Exception("La primera solicitud de prestamo no puede ser mayor a $".number_format($this->maxvalorprestamo));
                    }
                }
            }
            $params = array(
                'id_customer'       => $this->id_customer,
                'cuotas'            => $this->cuotas,
                'interes'           => $this->interes,
                'total'             => $this->total,
                'fecha_prestamo'    => $this->fecha_prestamo,
                'estado'            => $this->estado,
                'dia_corte'         => $this->dia_corte
            );
            $typeData = array(
                '%d',
                '%d',
                '%f',
                '%d',
                '%s',
                '%s',
                '%d'
            );
            $success = $this->con->insert($this->nombreTabla,$params,$typeData);
            if($success){
                $this->id = $this->con->insert_id;    
            }else{
                throw new Exception("No se pudo enviar prestamo. ".$this->con->last_error );
            }
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    public function isLoanWithoutClose(){
        $params = array(
            'id_customer'       => $this->id_customer
        );
        $results = $this->con->get_results( 
            $this->con->prepare(
                "SELECT 
                    id
                FROM 
                    $this->nombreTabla 
                WHERE 
                    id_customer=%d
                    AND estado NOT IN('cancel','completado')", 
            $params) 
        );
        if(isset($results) && is_array($results) && count($results)>0){
            return true;
        }else{
            return false;
        }
    }
    public function isFirstLoan(){
        $params = array(
            'id_customer' => $this->id_customer
        );
        $results = $this->con->get_results( 
            $this->con->prepare(
                "SELECT 
                    id
                FROM 
                    $this->nombreTabla 
                WHERE 
                    id_customer=%d", 
            $params) 
        );
        if(isset($results) && is_array($results) && count($results)>0){
            return false;
        }else{
            return true;
        }
    }
    public function loadById($id){
        if(!isset($id) || !is_numeric($id) || $id<0){
            throw new Exception("El id del prestamo debe ser numerico.");
        }
        $this->id = $id;
        $params = array(
            'id' => $this->id
        );
        $results = $this->con->get_results( 
            $this->con->prepare(
                "SELECT 
                        id_customer,
                        cuotas,
                        interes,
                        total,
                        fecha_prestamo,
                        estado,
                        dia_corte 
                    FROM 
                        ".$this->nombreTabla." 
                    WHERE 
                        id=%d", 
            $params) 
        );
        if(isset($results) && is_array($results) && count($results)>0){
            foreach($results as $item){
                $this->id_customer      = $item->id_customer;
                $this->cuotas           = $item->cuotas;
                $this->interes          = $item->interes;
                $this->total            = $item->total;
                $this->fecha_prestamo    = $item->fecha_prestamo;
                $this->estado           = $item->estado;
                $this->dia_corte        = $item->dia_corte;
                
            }
            return true;
        }else{
            return false;
        }
    }
    public function loadListByCustomer($id_customer){
        if(!isset($id_customer) || !is_numeric($id_customer) || $id_customer<0){
            throw new Exception("El id del cliente debe ser numerico.");
        }
        $this->id_customer = $id_customer;
        $params = array(
            'id_customer' => $this->id_customer
        );
        $list = array();
        $results = $this->con->get_results( 
            $this->con->prepare(
                "SELECT 
                        id,
                        id_customer,
                        cuotas,
                        interes,
                        total,
                        fecha_prestamo,
                        estado,
                        dia_corte 
                    FROM 
                        ".$this->nombreTabla." 
                    WHERE 
                        id_customer=%d", 
            $params) 
        );
        if(isset($results) && is_array($results) && count($results)>0){
            foreach($results as $item){
                $this->id               = $item->id;
                $this->id_customer      = $item->id_customer;
                $this->cuotas           = $item->cuotas;
                $this->interes          = $item->interes;
                $this->total            = $item->total;
                $this->fecha_prestamo   = $item->fecha_prestamo;
                $this->estado           = $item->estado;
                $this->dia_corte        = $item->dia_corte;
                $list[] = $this;
            }
        }
        return $list;
    }


}