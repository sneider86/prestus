<?php
class Clientes{
    private $con;
    public $file;
    private $id;
    private $nombre1;
    private $nombre2;
    private $apellido1;
    private $apellido2;
    private $email;
    private $telefono;
    private $celular;
    private $direccion;
    private $clave;
    private $barrio;
    private $nombreTabla;

    public function __construct() {
        //password_verify para verificar clave en el login
        global $wpdb;
        $this->file         = __FILE__;
        $this->con          = $wpdb;
        $this->nombreTabla  = $this->con->prefix.'eres_clientes';
        $this->nombre1      = '';
        $this->nombre2      = '';
        $this->apellido1    = '';
        $this->apellido2    = '';
        $this->email        = '';
        $this->telefono     = '';
        $this->celular      = '';
        $this->direccion    = '';
        $this->clave        = '';
        $this->barrio       = 0;
    }
    public function getId(){
        return $this->id;
    }
    public function getNombre1(){
        return $this->nombre1;
    }
    public function setNombre1($nombre1){
        $this->nombre1 = $nombre1;
    }
    public function getNombre2(){
        return $this->nombre2;
    }
    public function setNombre2($nombre2){
        $this->nombre2 = $nombre2;
    }
    public function getApellido1(){
        return $this->apellido1;
    }
    public function setApellido1($apellido1){
        $this->apellido1 = $apellido1;
    }
    public function getApellido2(){
        return $this->apellido2;
    }
    public function setApellido2($apellido2){
        $this->apellido2 = $apellido2;
    }
    public function getEmail(){
        return $this->email;
    }
    public function setEmail($email){
        if(!empty(trim($email))){
            if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->email = trim($email);
            }else{
                throw new Exception("El email es invalido $email.");
            }
        }else{
            throw new Exception("El email no puede estar vacio.");
        }
    }
    public function getTelefono(){
        return $this->telefono;
    }
    public function setTelefono($telefono){
        $this->telefono = $telefono;
    }
    public function getCelular(){
        return $this->celular;
    }
    public function setCelular($celular){
        $this->celular = $celular;
    }
    public function getDireccion(){
        return $this->direccion;
    }
    public function setDireccion($direccion){
        $this->direccion = $direccion;
    }
    public function setClave($clave){
        $this->clave = $clave;
    }
    public function getBarrio(){
        return $this->barrio;
    }
    public function setBarrio($barrio){
        $this->barrio = $barrio;
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
                    'barrio'    => $this->barrio
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
                    '%d'
                );
                $success = $this->con->insert($this->nombreTabla,$params,$typeData);
                if($success){
                    $this->id = $this->con->insert_id;    
                }else{
                    throw new Exception("No se pudo crear cliente");
                }
            }else{
                throw new Exception("No se puede crear un cliente con el mismo email $this->email.");
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
                    'direccion' => $this->direccion
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
        $param = array($email);
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
                    direccion
                FROM 
                    $this->nombreTabla 
                WHERE 
                    email=%s", 
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
                }
            }
        }else{
            throw new Exception("No existe cliente con este email ".$this->email);
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
                    direccion 
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
                }
            }
        }else{
            throw new Exception("No existe cliente con este id ".$this->id);
        }
    }


}