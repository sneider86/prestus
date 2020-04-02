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
    .eres-division{
        width: 100%;
        border-bottom: solid 1px;
    }
    .eres-titulo{
        font-size: 1.8rem;
        color: #000;
        font-weight: 900;
    }
    .eres-content-desc{
        line-height: 16px;
        margin-top: 14px;
    }
    .eres-btn-ingresar{
        background-color: #b2d893;
        border-color: #b2d893;
        margin-top: 40px;
        color: #fff;
        border: 0;
        padding: 6px 10px;
    }
    .eres_ingresar{
        margin-top: 70px;
    }
    .eres-input-ingresar{
        margin-top: 10px;
    }
    ::-webkit-input-placeholder { /* Edge */
        color: #818898;
    }

    :-ms-input-placeholder { /* Internet Explorer 10-11 */
        color: #818898;
    }

    ::placeholder {
        color: #818898;
    }
    @media(max-width: 991px){
        .wpcf7-form > .row > *{ margin-bottom: 15px; }
        .wpcf7-form .row.center{ margin-top: 10px; }
        .eres-content2-ingresar{
            margin-top: 60px;
        }
    }
</style>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" type="text/css" media="all">
<script
  src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
  integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU="
  crossorigin="anonymous"></script>

<div class="eres_ingresar">
    <div class="row">
        <div class="col-md-6">
            <div class="col-md-12">
                <span class="eres-titulo">Clientes Registrados</span>
                <div class="eres-division"></div>
                <div class="eres-content-desc">
                    <span>Si tiene una cuenta, inicie sesi&oacute;n con su correo electr&oacute;nico</span>
                </div>
                <form id="formlogin">
                    <div class="col-md-12">
                        <input type="text" name="email" placeholder="Email" value="" class="wpcf7-form-control custom-btn-style-4 eres-input-ingresar">
                    </div>
                    <div class="col-md-12">
                        <input type="password" name="clave" placeholder="Clave" value="" class="wpcf7-form-control custom-btn-style-4 eres-input-ingresar">
                    </div>
                    <div class="col-md-12">
                        <input id="btnloginuser" type="button" value="ENTRAR" class="wpcf7-form-control custom-btn-style-4 eres-btn-ingresar">
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-6 eres-content2-ingresar">
            <div class="col-md-12">
                <span class="eres-titulo">Nuevos Clientes</span>
                <div class="eres-division"></div>
                <div class="eres-content-desc">
                    <span>Crear una cuenta tiene muchos beneficios: Pagos m&aacute;s r&aacute;pidos,estado financiero</span>
                </div>
                <div class="row center">
                    <div class="col-md-12">
                        <input id="btnviewregistrar" type="submit" value="ENVIAR" class="wpcf7-form-control custom-btn-style-4 eres-btn-ingresar">
                        <span class="ajax-loader"></span>
                    </div>
                </div>
            </div>
        </div>
    </div> 

</div>