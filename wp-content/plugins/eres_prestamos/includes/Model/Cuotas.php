<?php
class Cuotas{
    private $con;
    public $file;
    private $id;
    private $id_credito;
    private $valor_cuota;
    private $fecha_pago;
    private $fecha_generacion;
    private $referencia_pago;
    private $nombreTabla;

    public function __construct() {
        global $wpdb;
        $this->file             = __FILE__;
        $this->con              = $wpdb;
        $this->nombreTabla      = $this->con->prefix.'eres_cuotas';
        $this->id               = 0;
        $this->id_credito       = 0;
        $this->valor_cuota      = 0;
        $this->fecha_pago       = '';
        $this->fecha_generacion = '';
        $this->referencia_pago  = '';

    }
    public function getId(){
        return $this->id;
    }
    public function getIdCredito(){
        return $this->id_credito;
    }
    public function setIdCredito($id_credito){
        if(isset($id_credito) && is_numeric($id_credito) && $id_credito>0){
            $this->id_credito = $id_credito;
        }else{
            throw new Exception("El id del credito debe ser numerico");
        }
    }
    public function getValorCuota(){
        return $this->valor_cuota;
    }
    public function setValorCuota($valor_cuota){
        if(isset($valor_cuota) && is_numeric($valor_cuota) && $valor_cuota>0){
            $this->valor_cuota = $valor_cuota;
        }else{
            throw new Exception("El valor de la cuota ser numerico");
        }
    }
    public function getFechaPago(){
        return $this->fecha_pago;
    }
    public function setFechaPago($fecha_pago){
        if(isset($fecha_pago) && !empty($fecha_pago)){
            $this->fecha_pago = $fecha_pago;
        }else{
            throw new Exception("La fecha de pago no puede estar vacia");
        }
    }
    public function getGeneracion(){
        return $this->fecha_generacion;
    }
    public function setGeneracion($fecha_generacion){
        if(isset($fecha_generacion) && !empty($fecha_generacion)){
            $this->fecha_generacion = $fecha_generacion;
        }else{
            throw new Exception("La fecha de generacion no puede estar vacia");
        }
    }
    public function getReferenciaPago(){
        return $this->referencia_pago;
    }
    public function setReferenciaPago($referencia_pago){
        if(isset($referencia_pago) && !empty($referencia_pago)){
            $this->referencia_pago = $referencia_pago;
        }else{
            throw new Exception("La referencia de pago no puede estar vacia");
        }
    }

    public function save(){
        try{
            if(!empty(trim($this->email))){
                if(isset($this->id) && is_numeric($this->id) && $this->id>0){
                    $this->update();
                }else{
                    $this->create();
                }
            }
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    private function create(){
        try{
            if(!$this->isExist()){
                if($this->tipodocumento<=0){
                    throw new Exception("El tipo de documento no puede estar vacio");
                }
                if(empty(trim($this->documento))){
                    throw new Exception("El documento no puede estar vacio");
                }
                if(empty(trim($this->email))){
                    throw new Exception("El email no puede estar vacio");
                }
                if(empty(trim($this->clave))){
                    throw new Exception("La clave no puede estar vacia");
                }
                if(empty(trim($this->nombre1))){
                    throw new Exception("Digite nombres");
                }
                if(empty(trim($this->apellido1))){
                    throw new Exception("Digite apellidos");
                }
                $params = array(
                    'nombre1'   => $this->nombre1,
                    'nombre2'   => $this->nombre2,
                    'apellido1' => $this->apellido1,
                    'apellido2' => $this->apellido2,
                    'email'     => $this->email,
                    'telefono'  => $this->telefono,
                    'celular'   => $this->celular,
                    'direccion' => $this->direccion,
                    'clave'     => password_hash($this->clave, PASSWORD_DEFAULT),
                    'barrio'    => $this->barrio,
                    'tipodocumento' => $this->tipodocumento,
                    'documento' => $this->documento,
                    'cupo'      => $this->cupo
                );
                $typeData = array(
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                    '%d',
                    '%s',
                    '%d'
                );
                $success = $this->con->insert($this->nombreTabla,$params,$typeData);
                if($success){
                    $this->id = $this->con->insert_id;    
                }else{
                    throw new Exception("No se pudo crear cliente");
                }
            }else{
                throw new Exception("El email ya existe ".$this->email." o el documento se encuentra registrado");
            }
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    private function update(){
        try{
            if($this->isExist()){
                $params = array(
                    'nombre1'   => $this->nombre1,
                    'nombre2'   => $this->nombre2,
                    'apellido1' => $this->apellido1,
                    'apellido2' => $this->apellido2,
                    'email'     => $this->email,
                    'telefono'  => $this->telefono,
                    'celular'   => $this->celular,
                    'direccion' => $this->direccion,
                    'tipodocumento' => $this->tipodocumento,
                    'documento' => $this->documento
                );
                $where = array(
                    'id' => $this->id
                );
                $typeData = array(
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                    '%s'
                );
                $typeDataWhere = array(
                    '%d'
                );
                error_log("antes actualizar");
                $success = $this->con->update($this->nombreTabla,$params,$where,$typeData,$typeDataWhere);
                $lastQuery = $this->con->last_query;
                $this->con->flush();
                if($success>0){
                    return true;  
                }else{
                    throw new Exception("No se pudo actualizar cliente. ".$lastQuery);
                }
            }else{
                throw new Exception("El cliente no existe");
            }
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    private function isExist(){
        try{
            if(is_numeric($this->id) && $this->id>0){
                $this->getById($this->id,false);
                return true;
            }else{
                $this->getByEmail($this->email,false);
                return true;
            }
        }catch(Exception $e){
            return false;
        }
        
    }
    public function getByEmail($email,$load=true){
        $param = array($email,$this->documento,$this->tipodocumento);
        $results = $this->con->get_results( 
            $this->con->prepare(
                "SELECT 
                    id,
                    nombre1,
                    nombre2,
                    apellido1,
                    apellido2,
                    email,
                    telefono,
                    celular,
                    direccion,
                    clave,
                    tipodocumento,
                    documento,
                    cupo
                FROM 
                    $this->nombreTabla 
                WHERE 
                    email=%s 
                    OR (documento=%s AND tipodocumento=%d)", 
            $param) 
        );
        if(isset($results) && is_array($results) && count($results)>0){
            foreach($results as $item){
                if($load){
                    $this->id           = $item->id;
                    $this->nombre1      = $item->nombre1;
                    $this->nombre2      = $item->nombre2;
                    $this->apellido1    = $item->apellido1;
                    $this->apellido2    = $item->apellido2;
                    $this->email        = $item->email;
                    $this->telefono     = $item->telefono;
                    $this->celular      = $item->celular;
                    $this->direccion    = $item->direccion;
                    $this->clave        = $item->clave;
                    $this->tipodocumento= $item->tipodocumento;
                    $this->documento    = $item->documento;
                    $this->cupo         = $item->cupo;
                }
            }
        }else{
            throw new Exception("El email ya existe ".$this->email." o el documento se encuentra registrado");
        }
    }
    public function getById($id,$load=true){
        $id;
        $param = array($id);
        $results = $this->con->get_results( 
            $this->con->prepare(
                "SELECT 
                    id,
                    nombre1,
                    nombre2,
                    apellido1,
                    apellido2,
                    email,
                    telefono,
                    celular,
                    direccion,
                    tipodocumento,
                    documento,
                    cupo
                FROM 
                    $this->nombreTabla 
                WHERE 
                    id=%d", 
            $param) 
        );
        if(isset($results) && is_array($results) && count($results)>0){
            foreach($results as $item){
                if($load){
                    $this->id           = $item->id;
                    $this->nombre1      = $item->nombre1;
                    $this->nombre2      = $item->nombre2;
                    $this->apellido1    = $item->apellido1;
                    $this->apellido2    = $item->apellido2;
                    $this->email        = $item->email;
                    $this->telefono     = $item->telefono;
                    $this->celular      = $item->celular;
                    $this->direccion    = $item->direccion;
                    $this->tipodocumento= $item->tipodocumento;
                    $this->documento    = $item->documento;
                    $this->cupo         = $item->cupo;
                }
            }
        }else{
            throw new Exception("No existe cliente con este id ".$this->id);
        }
    }
    public function login($email,$clave){
        try{
            $this->getByEmail($email);
            error_log($this->clave);
            if(password_verify($clave, $this->clave)){
                return true;
            }else{
                return false;
            }
        }catch(Exception $e){
            return false;
        }
        
    }


}