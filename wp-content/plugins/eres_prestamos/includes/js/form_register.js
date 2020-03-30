jQuery(document).ready(function ($) {
    $("body").append("<div id='eres-loader'></div>");
    $("body").append("<div id='eres-ajax-msg' title='Mensaje'></div>");
    $("#btncreatecustomer").on("click", function ($e) {
        $e.preventDefault();
 
        const headers = new Headers({
            'Content-Type': 'application/json',
            'X-WP-Nonce': ajax_var.nonce
        });
        
        $("#eres-loader").addClass("active");
        var data = getFormData();
        fetch(ajax_var.url, {
            method: 'post',
            body: JSON.stringify(data),
            headers: headers,
            credentials: 'same-origin'
        })
        .then(response => {
            $("#eres-loader").removeClass("active");
            return response.ok ? response.json() : 'Not Found...';
        }).then(json_response => {
            let html;
            if (typeof json_response === 'object') {
            } else {
                html = json_response;
            }
            if(json_response.sussess=="error"){
                $("#eres-ajax-msg").attr("title","Advertencia");
                $("#eres-ajax-msg").html(json_response.msg);
                $( "#eres-ajax-msg" ).dialog({
                    modal: true,
                    buttons: {
                      Ok: function() {
                        $( this ).dialog( "close" );
                      }
                    }
                });
            }else{
                $("#eres-ajax-msg").attr("title","Exito!");
                $("#eres-ajax-msg").html("Cliente Creado");
                $( "#eres-ajax-msg" ).dialog({
                    modal: true,
                    buttons: {
                      Ok: function() {
                        $( this ).dialog( "close" );
                      }
                    }
                });
            }
            
            //$("#eres-loader").removeClass("active");
        });
    });
    function getFormData(){
        var unindexed_array =$("#form_customer_register").serializeArray();
        var indexed_array = {};
    
        $.map(unindexed_array, function(n, i){
            indexed_array[n['name']] = n['value'];
        });
        return indexed_array;
    }
});