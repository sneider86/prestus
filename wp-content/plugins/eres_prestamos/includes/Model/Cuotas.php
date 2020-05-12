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
    public function createProduct($precio='30000'){
        try{
            $nombreTabla = $this->con->prefix.'posts';
            $guid = $this->getUrl().'/?post_type=product&#038;p=';
            $params = array(
                'post_author'   => 1,
                'post_date'   => date("Y-m-d H:i:s"),
                'post_date_gmt' => date("Y-m-d H:i:s"),
                'post_content' => '',
                'post_title'     => 'Cuota',
                'post_excerpt'  => '',
                'post_status'   => 'publish',
                'comment_status' => 'open',
                'ping_status'     => 'closed',
                'post_name'    => 'cuota',
                'to_ping' => '',
                'pinged' => '',
                'post_modified'      => date("Y-m-d H:i:s"),
                'post_modified_gmt'      => date("Y-m-d H:i:s"),
                'post_content_filtered'      => '',
                'guid'      => $guid,
                'post_type'      => 'product'
            );
            $typeData = array(
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            );
            $success = $this->con->insert($nombreTabla,$params,$typeData);
            if($success){
                $id = $this->con->insert_id;
                //$sql = "UPDATE ".$nombreTabla." SET guid = '".$guid.$id."' WHERE ID=".$id;
                $params = array(
                    'guid' => $guid.$id,
                    'post_name' => 'cuota-'.$id
                );
                $where = array(
                    'ID' => $id
                );
                $typeData = array(
                    '%s',
                    '%s'
                );
                $typeDataWhere = array(
                    '%d'
                );
                $success = $this->con->update($nombreTabla,$params,$where,$typeData,$typeDataWhere);
                if($success){
                    $data = array(
                        '_backorders' => 'no',
                        '_download_expiry' => '-1',
                        '_download_limit' => '-1',
                        '_downloadable' => 'no',
                        '_downloadable' => 'no',
                        '_edit_last' => '1',
                        '_edit_lock' => '1589075411:1',
                        '_manage_stock' => 'no',
                        '_price' => $precio,
                        '_product_version' => '3.9.1',
                        '_regular_price' => $precio,
                        '_sku' => 'sku-'.$id,
                        '_sold_individually' => 'no',
                        '_stock' => null,
                        '_stock_status' => 'instock',
                        '_tax_class' => '',
                        '_tax_status' => 'taxable',
                        '_virtual' => 'yes',
                        '_wc_average_rating' => '0',
                        '_wc_review_count' => '0',
                        'custom_tab_priority1' => '40',
                        'custom_tab_priority2' => '41',
                        'dfiFeatured' => 'a:1:{i:0;s:0:"";}',
                        'layout' => 'right-sidebar',
                        'slide_template' => 'default',
                        'total_sales' => '0'
                    );
                    foreach($data as $key => $value){
                        $this->createParamsProducto($id,$key,$value);
                    }
                    return true;
                }else{
                    throw new Exception("No se pudo actualizar producto");
                }
            }else{
                throw new Exception("No se pudo crear producto");
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
    private function getUrl(){
        if(isset($_SERVER['HTTPS'])){
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        }
        else{
            $protocol = 'http';
        }
        return $protocol . "://" . $_SERVER['HTTP_HOST'];
    }

    public function createParamsProducto($post_id,$metaKey,$metaValue){
        try{
            $nombreTabla = $this->con->prefix.'postmeta';
            $params = array(
                'post_id'   => $post_id,
                'meta_key'   => $metaKey,
                'meta_value' => $metaValue
            );
            $typeData = array(
                '%d',
                '%s',
                '%s'
            );
            $success = $this->con->insert($nombreTabla,$params,$typeData);
            if($success){
                return true;
            }else{
                throw new Exception("No se pudo crear atributo producto");
            }
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }



}