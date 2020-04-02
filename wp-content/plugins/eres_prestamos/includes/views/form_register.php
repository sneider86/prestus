<style>
    .wpcf7-form > .row > *{ margin-bottom: 25px; }
    .wpcf7-form i{ position: absolute; top: 10px; }
    .wpcf7-form-control.custom-contact-input {
        width: 100%;
        background: none;
        border: 0px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: none;
        padding: 10px 10px 10px 30px;
        color: #fff;
        -webkit-appearance: none;
        -moz-appearance: none;
        -ms-appearance: none;
        -o-appearance: none;
        appearance: none;
    }
    .custom-select-style:after {
        content: '';
        display: block;
        position: absolute;
        top: 50%;
        right: 25px;
        width: 10px;
        height: 10px;
        border-right: 1px solid #c8d4ea;
        border-bottom: 1px solid #c8d4ea;
        z-index: 0;
        -webkit-transform: translateY(-50%) rotate(45deg);
        -moz-transform: translateY(-50%) rotate(45deg);
        -ms-transform: translateY(-50%) rotate(45deg);
        -o-transform: translateY(-50%) rotate(45deg);
        transform: translateY(-50%) rotate(45deg);
    }
    select.custom-contact-input option { color: #53c5cd; }
    .wpcf7-submit.custom-btn-style-4 {
        background: transparent !important;
        color: #FFF;
        font-weight: 900 !important;
        border: 2px solid #fff !important;
        padding: 10px 20px !important;
        font-size: 14px;
        border-radius: 0px !important;
    }
    .wpcf7-submit.custom-btn-style-4:hover {
        background: #FFF !important;
        color: #6a80a9 !important;
    }
    ::-webkit-input-placeholder { color: #ffffff; }
    ::-moz-placeholder { color: #ffffff; }
    :-ms-input-placeholder { color: #ffffff; }
    :-moz-placeholder{ color: #ffffff; }
    .page-template-default .eres_form_register{
        background-color: #B2D893;
        padding: 50px;
        -webkit-box-shadow: 10px 10px 5px 0px rgba(129,136,152,1);
        -moz-box-shadow: 10px 10px 5px 0px rgba(129,136,152,1);
        box-shadow: 10px 10px 5px 0px rgba(129,136,152,1);
        margin-top: 30px;
    }
    .page-template-default .sidebar-content,.page-template-default .mobile-hide-sidebar{
        display: none;
    }
    .page-template-default .main-content{
        float: none;
        margin: auto;
    }
    #loader{
        position: fixed;
        width: 100%;
        height: 100%;
        background-color: #000;
        z-index: 1002;
        top: 0;
        opacity: 0.3;
        display: none;
    }
    #eres-loader{
        position: fixed;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: #000;
        opacity: 0.3;
        z-index: 1002;
        display: none;
    }
    #eres-loader.active{
        display: block;
    }
    @media(max-width: 991px){
        .wpcf7-form > .row > *{ margin-bottom: 15px; }
        .wpcf7-form .row.center{ margin-top: 10px; }
    }
</style>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" type="text/css" media="all">
<script
  src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
  integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU="
  crossorigin="anonymous"></script>

<div class="eres_form_register">
    <form id="form_customer_register" action="/contact/#wpcf7-f281-p97-o1" method="post" class="wpcf7-form" novalidate="novalidate">
        <div style="display: none;">
        </div>
        <div class="row">
            <div class="col-md-6">
                <i class="Simple-Line-Icons-envelope text-color-light"></i>
                <span class="wpcf7-form-control-wrap your-name">
                    <input type="text" name="email" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required custom-contact-input" aria-required="true" aria-invalid="false" placeholder="Email *">
                </span>
            </div>
            <div class="col-md-6">
                <i class="Simple-Line-Icons-envelope text-color-light"></i>
                <span class="wpcf7-form-control-wrap your-email">
                    <input type="password" name="password" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required custom-contact-input" aria-required="true" aria-invalid="false" placeholder="Clave *">
                </span>
            </div>
        </div> 
        
        <div class="row">
            <div class="col-md-6">
                <i class="Simple-Line-Icons-user text-color-light"></i>
                <span class="wpcf7-form-control-wrap your-name">
                    <input type="text" name="nombre1" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required custom-contact-input" aria-required="true" aria-invalid="false" placeholder="Primer Nombre*">
                </span>
            </div>
            <div class="col-md-6">
                <i class="Simple-Line-Icons-user text-color-light"></i>
                <span class="wpcf7-form-control-wrap your-email">
                    <input type="text" name="nombre2" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required custom-contact-input" aria-required="true" aria-invalid="false" placeholder="Segundo Nombre *">
                </span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <i class="Simple-Line-Icons-user text-color-light"></i>
                <span class="wpcf7-form-control-wrap your-name">
                    <input type="text" name="apellido1" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required custom-contact-input" aria-required="true" aria-invalid="false" placeholder="Primer Apellido*">
                </span>
            </div>
            <div class="col-md-6">
                <i class="Simple-Line-Icons-user text-color-light"></i>
                <span class="wpcf7-form-control-wrap your-email">
                    <input type="text" name="apellido2" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required custom-contact-input" aria-required="true" aria-invalid="false" placeholder="Segundo Apellido *">
                </span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <i class="Simple-Line-Icons-call-out icons text-color-light"></i>
                <span class="wpcf7-form-control-wrap your-name">
                    <input type="text" name="telefono" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required custom-contact-input" aria-required="true" aria-invalid="false" placeholder="Tel&eacute;fono *">
                </span>
            </div>
            <div class="col-md-6">
                <i class="Simple-Line-Icons-call-out icons text-color-light"></i>
                <span class="wpcf7-form-control-wrap your-email">
                    <input type="text" name="celular" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required custom-contact-input" aria-required="true" aria-invalid="false" placeholder="Celular *">
                </span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <i class="Simple-Line-Icons-target icons text-color-light"></i>
                <span class="wpcf7-form-control-wrap moption">
                    <select name="barrio" class="wpcf7-form-control wpcf7-select wpcf7-validates-as-required custom-contact-input" aria-required="true" aria-invalid="false">
                        <option value="">Seleccione Barrio</option>
                        <?php echo $this->getListBarriosHtml(); ?>
                    </select>
                </span>
            </div>
            <div class="col-md-6">
                <i class="Simple-Line-Icons-target icons text-color-light"></i>
                <span class="wpcf7-form-control-wrap your-email">
                    <input type="text" name="direccion" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required custom-contact-input" aria-required="true" aria-invalid="false" placeholder="Direcci&oacute;n *">
                </span>
            </div>
        </div>
        <div class="row center">
            <div class="col-md-12">
                <input id="btncreatecustomer" type="submit" value="ENVIAR" class="wpcf7-form-control wpcf7-submit custom-btn-style-4">
                <span class="ajax-loader"></span></div>
            </div>
            <div class="wpcf7-response-output wpcf7-display-none">
        </div>
    </form>
</div>