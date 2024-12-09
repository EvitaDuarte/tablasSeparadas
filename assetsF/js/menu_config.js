$(document).ready(function(e){
    
    //=============================================================
    // Volvemos al Inicio

    $('.b_csesion').click(function(){
        var form_data = new FormData();
        form_data.append("case", 9);
        //Realizo el ajax
        $.ajax({
            
            url: "assets/php/script.php",
            type: 'POST',
            //Esta linea 'async' sirve para hacer las variables globales. Siempre y cuando se use AJAX
            async: false,
            data: form_data,
            //Se debe de poner esto para utilizar la función append
            processData: false,
            contentType: false,
            success: function(response){
                $('#main_container').fadeOut(600);
                setTimeout(function(){
                    location.href = "OpeBan00_Salir.php";
                }, 600);
            }
        });
    });

    //=============================================================
    // Ajustamos el padding top


    var totalheight = 0;
    totalheight = totalheight + $("#Menu").outerHeight();

    totalheight = totalheight + "px";

    $("#data_content").css("padding-top",totalheight);

    //=============================================================
    // Desplegamos mensajes

    $(window).click(function() {
        closeNotification();
    });

    $('#toggle_notification').click(function() {
        closeMessages();
    });
    
    $('.notification-box').click(function(event){
        event.stopPropagation();
    });
    
    $('#toggle_notification').click(function(event){
        event.stopPropagation();
    });

    $('#toggle_notification').click(function(){
        toggleNotification();
    });

    $('#close_notifications').click(function(){
        toggleNotification();
    });

    var notification_box = document.getElementById('show-notifications');
    var toggle_notifications = false;

    function toggleNotification(){
        if(toggle_notifications){
            notification_box.style.height = '0px';
            notification_box.style.opacity = '0';
            notification_box.style.pointerEvents  = 'none';
            // notification_box.style.visibility = 'hidden';
            toggle_notifications = false;
        }else{
            notification_box.style.height = '510px';
            notification_box.style.opacity = '1';
            notification_box.style.pointerEvents  = 'all';
            // notification_box.style.visibility = 'visible';
            toggle_notifications = true;
        }
    }

    function closeNotification(){
        if(toggle_notifications == true){
            notification_box.style.height = '0px';
            notification_box.style.opacity = '0';
            notification_box.style.pointerEvents  = 'none';
            toggle_notifications = false;
        }
    }

    //=============================================================
    // Desplegamos notificaciones

    $(window).click(function() {
        closeMessages();
    });
    
    $('#toggle_messages').click(function() {
        closeNotification();
    });
    
    $('.message-box').click(function(event){
        event.stopPropagation();
    });
    
    $('#toggle_messages').click(function(event){
        event.stopPropagation();
    });

    $('#toggle_messages').click(function(){
        toggleMessages();
    });

    $('#close_messages').click(function(){
        toggleMessages();
    });

    var message_box = document.getElementById('show-messages');
    var toggle      = false;

    function toggleMessages(){
        if(toggle){
            message_box.style.height = '0px';
            message_box.style.opacity = '0';
            message_box.style.pointerEvents  = 'none';
            // message_box.style.visibility = 'hidden';
            toggle = false;
        }else{
            message_box.style.height = '510px';
            message_box.style.opacity = '1';
            message_box.style.pointerEvents  = 'all';
            // message_box.style.visibility = 'visible';
            toggle = true;
        }
    }

    function closeMessages(){
        if(toggle == true){
            message_box.style.height = '0px';
            message_box.style.opacity = '0';
            message_box.style.pointerEvents  = 'none';
            toggle = false;
        }
    }

    //=============================================================
    // Botones para regresar al Inicio

    $('#go-home-min-1200').click(function(){
        goHome();
    });

    //=============================================================
    // Botones para regresar al Inicio

    $('#go-home-max-1200').click(function(){
        goHome();
    });

    //=============================================================
    // Botones para regresar al Inicio

    $('.go-home').click(function(){
        goHome();
    });

    //=============================================================
    // Función para regresar al Inicio
    
    function goHome(){
        $('#main_container').fadeOut(600);
        setTimeout(function(){
            location.href = "OpeBan00_Salir.php";
        }, 600);
    }
});