<!DOCTYPE html>
<html lang="es">
    <head>
        <?php
            require_once("OpeFin00_00VarSesion.php"); // Pone disponible las variables de sesión
        ?>
        <meta charset="UTF-8-8" />
        <title><?=$v_TituloS?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-------------General Style's--------------->
        <link rel="stylesheet" href="assetsF/css/panel_style.css">
        <link rel="stylesheet" href="assetsF/css/seccion.css">
        <link rel="stylesheet" href="assetsF/css/radio.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    </head>
    <body>
        <form name="ConsoGral" id="ConsoGral" method="post" enctype="multipart/form-data">
            <div id="main_container">    
                <?php include('OpeFin00_MenuPrincipal.php'); // Incluye el menú principal?>
                <section class="datos-personales2">
                    <h2 class="titleM">Consolidado General</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <section class="seccion_caja_despliegue" id="secCta">
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Fecha (dd/mm/yyyy)</label>
                                         <input type="date" name="FechaIni" id="FechaIni" title="Fecha del Reporte"/> <!--  onblur="verificaFecha(this);" -->
                                    </div>
                                    
                                    <div class="caja_capturar" id="cajaSelCtas">
                                        <label for="cajaSelCtas" class="lbl_txt">Seleccione Cuentas</label>
                                        <input type="radio" id="idTodas"     name="filCta" value="T"/>        <label for="idTodas">Todas</label>
                                        <input type="radio" id="idActivas"   name="filCta" value="A" checked/><label for="idActivas">Activas</label>
                                        <input type="radio" id="idInactivas" name="filCta" value="I"/>        <label for="idInactivas">Inactivas</label>
                                    </div>
                                    <section class="seccion_caja" id="botones">
                                        <div class="caja_captura">
                                            <div class="form-field-button_" id="grpBotones">
                                                <a class="btn_1 efecto" onclick="ReporteConsolidadoGeneral();">
                                                    <span>Consolidado General</span>
                                                </a>
                                            </div>
                                        </div>
                                    </section>
                                </section>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <dialog id="cajaMensaje" class="dialogo">
                <div class="dialogo_header">
                    <div id="dialogo_close">X</div>
                </div>
                <hr>
                <div class="dialogo_body">
                    <p id="dialogMessage">Mensajes al usuario en lugar del alert</p>
                </div>
            </dialog>
            <dialog id="cajaRespuesta" class="dialogo">
                <div class="dialogo_header">
                    <div id="dialogo_close1">X</div>
                </div>
                <div class="dialogo_body">
                    <p id="dialogRespuesta">Mensajes al usuario en lugar del alert</p>
                </div>
                <div class="dialogo_botones">
                    <button id="btnSi" class="detalle_button1">Sí</button>
                    <button id="btnNo" class="detalle_button1">No</button>
                </div>
            </dialog>
        </form>
        <script src="jsF/cerrarSesion_.js"></script>
        <script src="jsF/rutinas_.js"></script>
        <script src="jsF/Reportes_.js"></script>
    </body>
</html>