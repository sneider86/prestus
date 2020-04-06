<?php
$maximo = $_SESSION['cupo'];
$step   = round($maximo/10);
?>
<style>
    /*
    *  MENU HORIZONTAL DASHBOARD
    *-------------------------------------------------------*/
    #wrapperMenu {
    width: 30%;
    margin: 20px 50px;
    }

    .wrappernav-dashboard {
        /*background: #d3d3d3;*/
        /*border: 1px dashed orange;*/
        width: 100%;
        padding: 10px;
        margin: 0;
    }
    #menu-dashboard {
        /*border: 1px dashed red;*/
        border-left: 1px solid #bcbcbc; 
        border-right: 1px solid #bcbcbc;
        border-top: 1px solid #bcbcbc;
        border-radius: 5px;
        overflow: hidden;
    }
    #menu-dashboard .active a {
        background-color: #F4BD16;
        border-radius: 0;
        color: #fff;
    }
    #menu-dashboard li {
        border-radius: 0;
        margin: 0;
        padding: 0;
        border-bottom: 1px solid #bcbcbc; 
    }
    #menu-dashboard li > a {
        border-radius: 0;
        padding: 10px;
        color: #444;
    }
    #menu-dashboard > li > a:hover,
    #menu-dashboard > li > a:focus {
        color: #fff;
        background: #F4BD16;
    -webkit-transition: all 1ms ease-in-out;
    -moz-transition: all 1ms ease-in-out;
    -ms-transition: all 1ms ease-in-out;
    -o-transition: all 1ms ease-in-out;
    transition: all 1ms ease-in-out;
    }
    #submenu {
        background: #333;
    }
    #submenu li {
        border: none;
    }
    #submenu li a {
        color: #fff;
        padding: 10px;
        margin: 0;
        text-indent: 20px;
    }
    #submenu li a:hover {
        background: rgba(0,0,0,.5);
    }
    #menu-dashboard .active a,
    #menu-dashboard > li > a:hover, #menu-dashboard > li > a:focus{
        background-color: #b2d893;
    }
    .multicontent{
        display:none;
        margin: 19px;
    }
    .conent-panel{
        width: 60%;
        margin-top: 20px;
        -webkit-box-shadow: 10px 10px 5px 0px rgba(129,136,152,1);
        -moz-box-shadow: 10px 10px 5px 0px rgba(129,136,152,1);
        box-shadow: 10px 10px 5px 0px rgba(129,136,152,1);
        margin-top: 20px;
        border: 1px solid #bcbcbc;
        
    }
    .eres-main-panel{
        display: flex;
    }
    #ncuotas,#mprestar,#diacorte{
        box-shadow: none;
    }
    .divnewprestamo p{
        margin:0px;
    }
    @media(max-width: 991px){
        .wpcf7-form > .row > *{ margin-bottom: 15px; }
        .wpcf7-form .row.center{ margin-top: 10px; }
        #wrapperMenu{
            width: 20%;
        }
        .conent-panel{
            width: 80%;
        }
    }
    @media(max-width: 767px){
        #wrapperMenu{
            width: 85%;
            margin: auto;
        }
        .conent-panel{
            width: 85%;
            margin: auto;
            margin-top: 50px;
        }
        .eres-main-panel{
            display: inline;
        }
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" type="text/css" media="all">
<script
  src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
  integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU="
  crossorigin="anonymous"></script>
<div class="eres-main-panel" style=" ">
    <div id="wrapperMenu">	            
        <ul id="menu-dashboard"class="nav nav-pills nav-stacked">
            <li class="active"><a href="#"><span class="icon-home4"></span> <span class="">Inicio</span></a></li>
            <li><a class="btnmenu" eres-action="perfil" href="#"><span class="icon-user3"></span> <span class="">Perfil</span></a></li>
            <li><a class="btnmenu" eres-action="newprestamo" href="#"><span class="icon-search-2"></span> <span class="">Hacer Prestamos</span></a></li>
            <li><a href="#"><span class="icon-speacker-1"></span> <span class=""> Publicaciones</span> <span class="badge pull-right">42</span></a></li>            
            <li><a href="#"><span class="icon-files"></span> <span class="">Facturación</a></li>
            
            
            <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="collapse" data-target="#submenu" aria-expanded="false">
                <span class="icon-link2"></span>
                Nuevos Link<span class="caret"></span></a>
            <ul class="nav collapse" id="submenu" role="menu" aria-labelledby="btn-1">                        
                <li><a href="#">Elementos del Submenu</a></li>
                <li><a href="#">Elementos del Submenu</a></li>
                <li><a href="#">Elementos del Submenu</a></li>
                <li><a href="#">Elementos del Submenu</a></li>                  
            </ul>
            </li>
            <li><a href="#"><span class="icon-link2"></span> <span class="">Nuevos link</span></a></li>
            <li><a id="btnsalir" href="#"><span class="icon-link2"></span> <span class="">Salir</span></a></li>
        </ul>
    </div>
    <div class="conent-panel">
        <div class="multicontent divnewprestamo">
            <input type="hidden" value="<?php echo $interes; ?>" name="interes" />
            <p>
                <label for="ncuotas">Numero de Cuotas:</label>
                <input type="text" id="ncuotas" readonly style="border:0; color:#f6931f; font-weight:bold;">
            </p>
            <div id="cuotas"></div>
            <p style="margin-top:20px;">
                <label for="mprestar">Monto a Prestar:</label>
                <input type="text" id="mprestar" readonly style="border:0; color:#f6931f; font-weight:bold;">
            </p>
            <div id="divprestar"></div>

            <p style="margin-top:20px;">
                <label for="diacorte">D&iacute;a de Corte:</label>
                <input type="text" id="diacorte" readonly style="border:0; color:#f6931f; font-weight:bold;">
            </p>
            <div id="divdiacorte"></div>
        </div>
    </div>
</div>
<script>
    jQuery(document).ready(function ($) {
        $('#submenu li a').on('click', function(e){
            $('#submenu').toggleClass();
        });
        $(".btnmenu").on("click",function(e){
            $(".multicontent").css({'display':'none'});
            var op = $(this).attr("eres-action");
            switch(op){
                case "newprestamo":
                    $(".divnewprestamo").css({'display':'block'});
                break;
            }
        });
        $( "#cuotas" ).slider({
            range: "max",
            min: 1,
            max: 10,
            value: 2,
            slide: function( event, ui ) {
                $( "#ncuotas" ).val( ui.value );
            }
        });
        $( "#ncuotas" ).val( $( "#cuotas" ).slider( "value" ) );


        $( "#divprestar" ).slider({
            range: "min",
            value: <?php echo $step; ?>,
            min: <?php echo $step; ?>,
            step: <?php echo $step; ?>,
            max: <?php echo $maximo; ?>,
            slide: function( event, ui ) {
                $( "#mprestar" ).val( "$" + ui.value );
            }
        });
        $( "#mprestar" ).val( "$" + $( "#divprestar" ).slider( "value" ) );


        $( "#divdiacorte" ).slider({
            range: "min",
            value: 1,
            min: 1,
            max: 30,
            slide: function( event, ui ) {
                $( "#diacorte" ).val( ui.value );
            }
        });
        $( "#diacorte" ).val( $( "#divdiacorte" ).slider( "value" ) );
    
    });
</script>