<?php
class PrestamosConfig{

  public $file;

  public function __construct($file) {
      $this->file = $file;
      /* ******************* load Models ******************* */
      require_once plugin_dir_path(__DIR__).'/includes/Model/Barrios.php';
      require_once plugin_dir_path(__DIR__).'/includes/Model/Clientes.php';
      require_once plugin_dir_path(__DIR__).'/includes/Model/Config.php';
      /* ******************* load Models ******************* */

      add_action( 'admin_menu',array( $this, 'eres_add_link_prestamos' ));
      register_activation_hook(plugin_dir_path(__DIR__).'/prestamos.php',array( $this, 'db_schema_prestamos' ));
      add_shortcode( 'form_register', array($this, 'form_register' ) );
      add_shortcode( 'ingresar', array($this, 'view_ingresar' ) );
      add_action( 'rest_api_init', array( $this, 'create_customer_endpoint' ));
      add_action( 'rest_api_init', array( $this, 'login_customer_endpoint' ));
      add_action( 'rest_api_init', array( $this, 'logout_customer_endpoint' ));
      add_action( 'wp_enqueue_scripts', array($this,'form_register_js'));
      add_action( 'init', array($this,'eres_session_start'), 1 );
      
  }

  public function eres_add_link_prestamos(){
    add_menu_page(
      'Créditos', // Title of the page
      'Créditos', // Text to show on the menu link
      'manage_options', // Capability requirement to see the link
      'eres_pagina_creditos', // The 'slug' - file to display when clicking the link
      array( $this, 'funcion_mostrar_pagina' ),
      null,
      10
    );
  }
  public function db_schema_prestamos(){
    global $wpdb;
    $nombreTabla = $wpdb->prefix . "eres_prestamos";
    $table_name = '';
    if($wpdb->get_var("SHOW TABLES LIKE '$nombreTabla'") != $nombreTabla) {
      $this->tablePrestamos();
      $this->tableClientes();
      $this->tableLocalidades();
      $this->tableBarrios();
      $this->tableConfig();
    }
    
  }
  private function tablePrestamos(){
    global $wpdb;
    $nombreTabla = $wpdb->prefix . "eres_prestamos";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $nombreTabla (
      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      id_customer INT NOT NULL,
      cuotas INT NOT NULL,
      interes DOUBLE NOT NULL,
      total DOUBLE NOT NULL DEFAULT 0,
      fecha_prestamo DATETIME NOT NULL,
      dia_corte INT NOT NULL,
      PRIMARY KEY (ID)
    ) $charset_collate;";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    $sql = "CREATE INDEX indx_prestamos_cliente ON $nombreTabla (id_customer)" ;
    $wpdb->query($sql);
  }
  private function tableConfig(){
    global $wpdb;
    $nombreTabla = $wpdb->prefix . "eres_config";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $nombreTabla (
      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      ckey VARCHAR(80) NOT NULL DEFAULT '',
      cvalue VARCHAR(80) NOT NULL DEFAULT '',
      PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    $sql = "CREATE INDEX indx_config ON $nombreTabla (ckey)" ;
    $wpdb->query($sql);
    $sql = "INSERT INTO $nombreTabla(ckey,cvalue) VALUES
              ('interes','3.0'),
              ('tipodocumento','C.C'),
              ('tipodocumento','T.E')" ;
    $wpdb->query($sql);
  }
  private function tableClientes(){
    global $wpdb;
    $nombreTabla = $wpdb->prefix . "eres_clientes";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $nombreTabla (
      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      nombre1 VARCHAR(80) NOT NULL DEFAULT '',
      nombre2 VARCHAR(80) NOT NULL DEFAULT '',
      apellido1 VARCHAR(80) NOT NULL DEFAULT '',
      apellido2 VARCHAR(80) NOT NULL DEFAULT '',
      email VARCHAR(255) NOT NULL DEFAULT '',
      clave VARCHAR(255) NOT NULL DEFAULT '',
      telefono VARCHAR(80) NOT NULL DEFAULT '',
      celular VARCHAR(80) NOT NULL DEFAULT '',
      direccion VARCHAR(80) NOT NULL DEFAULT '',
      barrio VARCHAR(80) NOT NULL,
      tipodocumento int(11) NOT NULL,
      documento VARCHAR(80) NOT NULL,
      cupo DOUBLE NOT NULL,
      PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
  }
  private function tableLocalidades(){
    global $wpdb;
    $nombreTabla = $wpdb->prefix . "eres_localidades";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $nombreTabla (
      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      nombre VARCHAR(80) NOT NULL DEFAULT '',
      estado VARCHAR(1) NOT NULL DEFAULT '',
      PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    $sql = "INSERT INTO $nombreTabla(id,nombre,estado) VALUES 
    (1,'Riomar','A'),
    (2,'Norte-Centro Histórico','A'),
    (3,'Metropolitana','A'),
    (4,'Sur Occidente','A'),
    (5,'Sur Oriente','A');" ;
    $wpdb->query($sql);
  }
  private function tableBarrios(){
    global $wpdb;
    $nombreTabla = $wpdb->prefix . "eres_barrios";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $nombreTabla (
      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      nombre VARCHAR(80) NOT NULL DEFAULT '',
      estado VARCHAR(1) NOT NULL DEFAULT '',
      localidad int NOT NULL,
      PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    
    $sql = "INSERT INTO $nombreTabla(nombre,estado,localidad) VALUES
    ('La Playa','A',1)
    ,('Villa Santos','A',1)
    ,('Urbanización La Playa','A',1)
    ,('Villa campestre','A',1)
    ,('El Poblado','A',1)
    ,('Altamira','A',1)
    ,('San Vicente','A',1)
    ,('Altos del Limón','A',1)
    ,('Altos de Riomar','A',1)
    ,('Santa Mónica','A',1)
    ,('Riomar','A',1)
    ,('Andalucía','A',1)
    ,('Las Flores','A',1)
    ,('La Floresta','A',1)
    ,('San Salvador','A',1)
    ,('Siape','A',1)
    ,('Las Tres Avemarías','A',1)
    ,('Villa del Este','A',1)
    ,('El Castillo','A',1)
    ,('Solaire','A',1)
    ,('Paraíso','A',1)
    ,('El Limoncito','A',1)
    ,('Altos del Prado','A',1)
    ,('La Castellana','A',1)
    ,('Villa Carolina','A',1)
    ,('El Golf','A',1)
    ,('San Marino','A',1)
    ,('Adela de Char','A',1),
    
    ('Modelo','A',2),
    ('Alameda del Río','A',2),
    ('Ciudad Jardín','A',2),
    ('La Campiña','A',2),
    ('El Tabor','A',2),
    ('Miramar','A',2),
    ('Granadillo','A',2),
    ('Los Alpes','A',2),
    ('Nuevo Horizonte','A',2),
    ('El Porvenir','A',2),
    ('Altos del Prado','A',2),
    ('El Golf','A',2),
    ('El Country','A',2),
    ('Paraíso','A',2),
    ('Los Nogales','A',2),
    ('La Concepción','A',2),
    ('San Francisco','A',2),
    ('Santa Ana','A',2),
    ('América','A',2),
    ('Colombia','A',2),
    ('El Prado','A',2),
    ('Bellavista','A',2),
    ('Montecristo','A',2),
    ('Abajo','A',2),
    ('La Felicidad','A',2),
    ('La Cumbre','A',2),
    ('Nuevo Horizonte','A',2),
    ('Campo Alegre','A',2),
    ('Las Colinas','A',2),
    ('Los Jobos','A',2),
    ('Las Mercedes','A',2),
    ('Betania','A',2),
    ('Las Delicias','A',2),
    ('El Recreo','A',2),
    ('Boston','A',2),
    ('El Rosario','A',2),
    ('Centro','A',2),
    ('Barlovento','A',2),
    ('Villanueva','A',2),
    ('El Boliche','A',2),
    
    ('Buenos Aires','A',3),
    ('Carrizal','A',3),
    ('Siete de Abril','A',3),
    ('Cevillar','A',3),
    ('Ciudadela 20 de Julio','A',3),
    ('El Santuario','A',3),
    ('Kennedy','A',3),
    ('La Arboraya','A',3),
    ('La Sierra','A',3),
    ('La Victoria','A',3),
    ('Las Américas','A',3),
    ('Las Gardenias','A',3),
    ('Las Granjas','A',3),
    ('Los Continentes','A',3),
    ('Los Girasoles','A',3),
    ('San José','A',3),
    ('San Luis','A',3),
    ('Santa María','A',3),
    ('Urbanización Las Cayenas','A',3),
    ('Veinte de Julio','A',3),
    ('Villa San Carlos','A',3),
    ('Villa San Pedro II','A',3),
    ('Villa Sevilla','A',3),

    ('Alfonso López','A',4),
    ('Bernando Hoyos','A',4),
    ('Buena Esperanza','A',4),
    ('California','A',4),
    ('Caribe Verde','A',4),
    ('Carlos Meisel','A',4),
    ('Cevillar','A',4),
    ('Chiquinquirá','A',4),
    ('Ciudad Modesto','A',4),
    ('Ciudadela de La Salud','A',4),
    ('Ciudadela de La Paz','A',4),
    ('Colina Campestre','A',4),
    ('Conjunto Residencial Prados Del Edén','A',4),
    ('Cordialidad','A',4),
    ('Corrigimiento Juan Mina','A',4),
    ('Cuchilla de Villate','A',4),
    ('El Bosque','A',4),
    ('El Carmen','A',4),
    ('El Edén','A',4),
    ('El Golfo','A',4),
    ('El Pueblo','A',4),
    ('El Recreo','A',4),
    ('El Romance','A',4),
    ('El Rubí','A',4),
    ('El Silencio','A',4),
    ('El Valle','A',4),
    ('Evaristo Sourdis','A',4),
    ('Gerlein y Villate','A',4),
    ('Juan Mina','A',4),
    ('Kalamary','A',4),
    ('La Ceiba','A',4),
    ('La Esmeralda','A',4),
    ('La Florida','A',4),
    ('La Gloria','A',4),
    ('La Libertad','A',4),
    ('La Manga','A',4),
    ('La Paz','A',4),
    ('La Pradera','A',4),
    ('La Sierra','A',4),
    ('Las Colinas','A',4),
    ('Las Estrellas','A',4),
    ('Las Malvinas','A',4),
    ('Las Terrazas','A',4),
    ('Lipaya','A',4),
    ('Loma Fresca','A',4),
    ('Los Andes','A',4),
    ('Los Ángeles','A',4),
    ('Los Olivos','A',4),
    ('Los Pinos','A',4),
    ('Los Rosales','A',4),
    ('Lucero','A',4),
    ('Me Quejo','A',4),
    ('Mercedes Sur','A',4),
    ('Nueva Colombia','A',4),
    ('Nueva Granada','A',4),
    ('Olaya','A',4),
    ('Pinar del Río','A',4),
    ('Por Fin','A',4),
    ('Pumarejo','A',4),
    ('San Felipe','A',4),
    ('San Isidro','A',4),
    ('San José (suroccidente)','A',4),
    ('Santo Domingo','A',4),
    ('Siete de Agosto','A',4),
    ('Terrenos Pastoral Social','A',4),
    ('Urbanización Colinas Campestre Edén 2000','A',4),
    ('Urbanización El Pueblo','A',4),
    ('Urbanización Villas de San Pablo','A',4),
    ('Villa Del Rosario','A',4),
    ('Villa Flor','A',4),
    ('Villa San Pablo','A',4),
    ('Villa San Pedro Alejandrino','A',4),
    ('Villa San Pedro I Etapa','A',4),
    ('Villate','A',4),

    ('Alfonso López','A',5),
    ('Atlántico','A',5),
    ('Bella Arenas','A',5),
    ('Boyacá','A',5),
    ('Buenos Aires','A',5),
    ('Chiquinquirá','A',5),
    ('Ciudadela 20 de Julio (suroriente)','A',5),
    ('El Campito','A',5),
    ('El Ferri','A',5),
    ('El Limón','A',5),
    ('El Milagro','A',5),
    ('José Antonio Galán','A',5),
    ('La Arboraya','A',5),
    ('La Chinita','A',5),
    ('La Luz','A',5),
    ('La Magdalena','A',5),
    ('La Unión','A',5),
    ('La Victoria (suroriente)','A',5),
    ('Las Dunas','A',5),
    ('Las Nieves','A',5),
    ('Las Palmas','A',5),
    ('Los Laureles','A',5),
    ('Los Trupillos','A',5),
    ('Moderno','A',5),
    ('Montes','A',5),
    ('Pasadena','A',5),
    ('Primero de Mayo','A',5),
    ('Rebolo','A',5),
    ('San José','A',5),
    ('San Nicolás','A',5),
    ('San Roque','A',5),
    ('Santa Helena','A',5),
    ('Simón Bolívar','A',5),
    ('Costa hermosa','A',5),
    ('Tayrona','A',5),
    ('Universal I y II','A',5),
    ('Urbanización La Luz','A',5),
    ('Villa Blanca','A',5),
    ('Villa del Carmen','A',5)


    ;";
    $wpdb->query($sql);
  }    
  public function funcion_mostrar_pagina() {
    if (!current_user_can('manage_options'))  {
        wp_die( __('No tienes suficientes permisos para acceder a esta página.') );
    }
    require_once plugin_dir_path($this->file) . 'views/eres_pagina_creditos.php';   
  }
  public function form_register(){
    require_once plugin_dir_path($this->file) . 'views/form_register.php';
  }
  public function getListBarriosHtml(){
    $barrios = new Barrios();
    $html = "";
    foreach($barrios->getAllList() as $item){
      $html = $html. "<option value='".$item['id']."'>".$item['nombre']."</option>";
    }
    return $html;
  }
  public function getListTipoDocumento(){
    $config = new Config();
    $html = "";
    foreach($config->getListConfiguration("tipodocumento") as $item){
      $html = $html. "<option value='".$item['id']."'>".$item['value']."</option>";
    }
    return $html;
  }
  public function create_customer_endpoint() {
    register_rest_route( 'customer/', 'create', array(
      'methods'  => 'POST',
      'callback' => array($this,'create_customer'),
    ) );
  }
  public function create_customer( WP_REST_Request $request ) {
    try{
      $nombre1    = sanitize_text_field( $request['nombre1']);
      //$nombre2    = sanitize_text_field( $request['nombre2']);
      $apellido1  = sanitize_text_field( $request['apellido1']);
      //$apellido2  = sanitize_text_field( $request['apellido2']);
      $email      = sanitize_email( $request['email'] );
      $clave      = sanitize_text_field( $request['password']);
      $telefono   = sanitize_text_field( $request['telefono']);
      $celular    = sanitize_text_field( $request['celular']);
      $barrio     = sanitize_text_field( $request['barrio']);
      $direccion  = sanitize_text_field( $request['direccion']);
      $tdocumento = sanitize_text_field( $request['tipodocumento']);
      $documento  = sanitize_text_field( $request['documento']);

      $cliente    = new Clientes();
      $cliente->setNombre1($nombre1);
      //$cliente->setNombre2($nombre2);
      $cliente->setApellido1($apellido1);
      //$cliente->setApellido2($apellido2);
      $cliente->setTelefono($telefono);
      $cliente->setCelular($celular);
      $cliente->setEmail($email);
      $cliente->setClave($clave);
      $cliente->setBarrio($barrio);
      $cliente->setDireccion($direccion);
      $cliente->setTipoDocumento($tdocumento);
      $cliente->setDocumento($documento);
      $cliente->save();
      $_SESSION['id_user']        = $cliente->getId();
      $_SESSION['nombrecompleto'] = $cliente->getNombre1().' '.$cliente->getNombre2().' '.$cliente->getApellido1().' '.$cliente->getApellido2();
      $_SESSION['cupo']           = $cliente->getCupo();
      return array("sussess"=>"ok",'id'=>$cliente->getId());
    }catch(Exception $e){
      return array("sussess"=>"error",'msg'=>$e->getMessage());
    }
  }
  public function form_register_js(){
    wp_enqueue_script( 
      'js_form_register',
      '/wp-content/plugins/eres_prestamos/includes/js/form_register.js ',
      array( 'jquery' ) 
    );

    wp_localize_script( 'js_form_register', 'ajax_var', array(
        'url'       => rest_url( '/customer/create' ),
        'nonce'     => wp_create_nonce( 'wp_rest' ),
        'urllogin'  => rest_url( '/customer/login' ),
        'urllogout'  => rest_url( '/customer/logout' )
    ) );
  }
  public function login_customer_endpoint() {
    register_rest_route( 'customer/', 'login', array(
      'methods'  => 'POST',
      'callback' => array($this,'login_customer'),
    ) );
  }
  public function login_customer( WP_REST_Request $request ) {
    try{
      $email      = sanitize_email( $request['email'] );
      $clave      = sanitize_text_field( $request['clave']);
      $cliente    = new Clientes();
      $login      = $cliente->login($email,$clave);
      if($login){
        $_SESSION['id_user'] = $cliente->getId();
        $_SESSION['nombrecompleto'] = $cliente->getNombre1().' '.$cliente->getNombre2().' '.$cliente->getApellido1().' '.$cliente->getApellido2();
        $_SESSION['cupo'] = $cliente->getCupo();
        return array("sussess"=>"ok",'status'=>$login);
      }else{
        return array("sussess"=>"error",'status'=>$login,'msg'=>'Usuario y/o Clave invalido');
      }
      
    }catch(Exception $e){
      return array("sussess"=>"error",'msg'=>$e->getMessage());
    }
  }
  public function view_ingresar(){
    if(isset($_SESSION['id_user']) && is_numeric($_SESSION['id_user'])){
      $conf = new Config();
      $interes = (double)$conf->getConfiguration('interes');
      require_once plugin_dir_path($this->file) . 'views/panel.php';
    }else{
      require_once plugin_dir_path($this->file) . 'views/ingresar.php';
    }
    
  }
  public function eres_session_start(){
    if( ! session_id() ) {
      session_start();
    }

  }
  public function logout_customer_endpoint() {
    register_rest_route( 'customer/', 'logout', array(
      'methods'  => 'POST',
      'callback' => array($this,'logout_customer'),
    ) );
  }
  public function logout_customer( WP_REST_Request $request ) {
    if(isset($_SESSION['id_user'])){
      unset($_SESSION['id_user']);
      session_destroy();
    }
    return array("sussess"=>"ok");
  }

}

$obj = new PrestamosConfig(__FILE__);
