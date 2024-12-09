<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="uft-8" />
        <title>Sistema de Operación Bancaria Web</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="assetsF/css/dialogo.css">
        <link rel="stylesheet" type="text/css" href="assetsF/css/login_style.css"/>
        <?php         
            //include("OpeFin00_00VarSesion.php");
            session_start();
        ?>
    </head>
    <body>

        <div id="main_container">
            <div class="logo" style="position: absolute; top: 8px;">
                <img src="assetsF/img/logo_ine_hd.svg" alt="Ine logo" style="user-select: none;" class="logo">
            </div>
            <div class="brand">
                <h1><span>Sistema de Operación Bancaria Web INE</span></h1>
            </div>
            <svg class="wave" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#d30076" fill-opacity="1" d="M0,96L48,133.3C96,171,192,245,288,256C384,267,480,213,576,176C672,139,768,117,864,128C960,139,1056,181,1152,192C1248,203,1344,181,1392,170.7L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>

            <div class="container">
                <div class="img">
                    <img src="assetsF/img/sesion_background_gd4.svg">
                </div>
                <div class="login-content">
                    <form name="form1" id="form1" method="post" action="backF/login_.php" enctype="application/x-www-form-urlencoded">

                        <input type="hidden" name="Start" id="Start">
                        <input type="hidden" name="recaptcha_response" id="recaptchaResponse">

                        <img class="avatar" src="assetsF/img/icono_perfil_p1.svg" style="user-select: none;">
                        <h2 class="title">Bienvenido</h2>
                        <!--Datos de logeo-->
                        <div id="login">
                            <div class="input-div one">
                                <div class="i">
                                    <i class="fas fa-user" id="i_user"></i>
                                </div>
                                <div class="div">
                                    <input type="text" class="input" spellcheck='false' name="user_login" id="user_login" 
                                    autocomplete="username" placeholder="Usuario">
                                </div> 
                            </div>
                            <div class="input-div pass">
                                <div class="i"> 
                                    <i class="fas fa-lock" id="i_pass"></i>
                                </div>
                                <div class="div">
                                    <!-- <h5 style="user-select: none; ">Contraseña</h5> -->
                                    <input type="password" class="input" spellcheck='false' name="password_login" id="password_login" 
                                    autocomplete="current-password" placeholder="Contraseña">
                                </div>
                            </div>
                            <div class="form-field-button">
                                <input type="submit" name="btnlogin" value="Ingresar Sesión" class="btn efecto">
                            </div>
                        </div>
                        <!--Fin de datos de logeo-->
                        <div>
                            <label class="label"><?//=$_SESSION['OpeFinError']?></label>
                        </div>
                        <dialog id="cajaMensaje" class="dialogo">
                            <div class="dialogo_header">
                                <div id="dialogo_close">X</div>
                            </div>
                            <hr>
                            <div class="dialogo_body">
                                <p id="dialogMessage" class="mensaje">Mensajes al usuario en lugar del alert</p>
                            </div>
                        </dialog>
                    </form>
                </div>
            </div>
            <div id="errors">
                <?php //echo $_SESSION['OpeFinError']; ?>
            </div>
        </div>
        <div id="footer">
            <?php include("OpeFin00_footer_nuevo.php"); ?>
        </div>

        <script src="jsF/rutinas_.js"></script>

        <script>
            function onClick(e) {
                e.preventDefault();
                grecaptcha.enterprise.ready(async () => {
                    const token = await grecaptcha.enterprise.execute('6LeMsp8pAAAAAPZ7mtOTBfHJ0bO0wMQKu0AleZux', {action: 'LOGIN'});
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                var formulario = document.getElementById('form1');

                formulario.addEventListener('submit', function(event) {
                    var vUser   = document.getElementById('user_login').value.trim();
                    var vClave  = document.getElementById('password_login').value.trim();

                    // Verifica si ambos inputs tienen contenido
                    if (vUser === '' ) {
                        // Si alguno de los inputs está vacío, evita que se envíe el formulario
                        event.preventDefault();
                        mandaMensaje('Se requiere nombre de usuario');
                        document.getElementById('user_login').focus();

                    }else if(vClave === ''){
                        event.preventDefault();
                        mandaMensaje('Se requiere clave de usuario');
                        document.getElementById('password_login').focus();
                    }else {
                        // Si ambos inputs tienen contenido, el formulario se enviará normalmente
                    }
                });
            });
            //
            function verificarError() {
                // Obtener el contenido de la sesión PHP como una cadena JavaScript
                var error = "<?php 
                    if ( isset($_SESSION['OpeFinError']) ){
                        if (trim($_SESSION['OpeFinError'])!=""){
                            echo $_SESSION['OpeFinError'];
                        }else{
                            echo "";
                        }
                    }else{
                        echo "";
                    } 
                ?>";

            // Verificar si error tiene algún valor
                if (error.trim() !== '') {
                // Llamar a la función mandaMensaje con el valor de error como parámetro
                    mandaMensaje("Credenciales Inválidas");

                }
            }
            // Llamar a la función verificarError al cargar la página
            window.addEventListener('DOMContentLoaded', verificarError);
        </script>
        <script src="https://www.google.com/recaptcha/api.js?render=6LeGRc4UAAAAAIwlQMJVzz0Y2obd-j97NbpAqbxh"></script>
        <script>
            grecaptcha.ready(function (){
                grecaptcha.execute('6LeGRc4UAAAAAIwlQMJVzz0Y2obd-j97NbpAqbxh', { action: 'contact' }).then(function (token) {
                    var recaptchaResponse = document.getElementById('recaptchaResponse');
                    recaptchaResponse.value = token;
                });
            });
        </script>
    </body>
</html>