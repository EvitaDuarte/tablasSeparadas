<!DOCTYPE html>
<html lang="es">
    <head>
        <?php   require_once("OpeFin00_00VarSesion.php"); ?>
        <meta charset="uft-8" />
        <title><?=$v_TituloS?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-------------General Style's--------------->
        <link rel="stylesheet" href="assetsF/css/panel_style.css">
        <link rel="stylesheet" href="assetsF/css/seccion.css">
    </head>
    <body>
        <form name="frmAcceso" id="frmAcceso" method="post" enctype="multipart/form-data">
            <div id="main_container">    
                <?php include('OpeFin00_MenuPrincipal.php'); // Incluye el menú principal?>
                <section class="datos-personales2">
                    <h2 class="titleM">Catálogo de Accesos</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <section class="seccion_caja_despliegue" id="secCap">
                                    <div class="caja_captura">
                                        <label for="idCuentaBancaria" class="lbl_txt">Cuenta Bancaria</label>
                                        <select name="idCuentaBancaria" id="idCuentaBancaria" class="select-input"></select>
                                    </div>
                                    <div class="caja_captura">
                                        <label for="idUsuario" class="lbl_txt">Usuario</label>
                                        <select name="idUsuario" id="idUsuario" class="select-input"></select>
                                    </div>
                                </section>
                                <section class="seccion_caja" id="botones">
                                    <div class="form-field-button_" id="grpBotones">
                                        <a class="btn_1 efecto" onclick="AccesosAgrega();">
                                            <span>Agregar</span>
                                        </a>
                                        <a class="btn_1 efecto" onclick="AccesosBusca();">
                                            <span>Buscar</span>
                                        </a>
                                        <a class="btn_1 efecto" onclick="AccesosElimina();">
                                            <span>Eliminar</span>
                                        </a>
                                        <a class="btn_1 efecto" onclick="limpiaPantalla('frmAcceso');">
                                            <span>Nuevo</span>
                                        </a>
                                    </div>
                                </section>
                                <div class="tabla-con-cuadricula">
                                    <table class="tablex" id="accesos">
                                        <caption class="captionTable">Accesos de Usuarios a Cuentas Bancarias</caption>
                                        <thead>
                                            <tr>
                                                <th>Cuenta Bancaria</th>
                                                <th>Nombre</th>
                                                <th>Acceso Usuario</th>
                                                <th>Capturó</th>
                                                <th>Fecha Alta</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cuerpo">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
<!--        ______________________________________________________________________              -->
            <dialog id="cajaMensaje" class="dialogo">
                <div class="dialogo_header">
                    <div id="dialogo_close" class="claseX">&#8999;</div>
                </div>
                <hr>
                <div class="dialogo_body">
                    <p id="dialogMessage">Mensajes al usuario en lugar del alert</p>
                </div>
            </dialog>
<!--        ______________________________________________________________________              -->
            <dialog id="cajaRespuesta" class="dialogo">
                <div class="dialogo_header">
                    <div id="dialogo_close1" class="claseX">&#8999;</div>
                </div>
                <hr>
                <div class="dialogo_body">
                    <p id="dialogRespuesta">Mensajes al usuario en lugar del alert</p>
                </div>
                <div class="dialogo_botones">
                    <button id="btnSi" class="detalle_button1">Sí</button>
                    <button id="btnNo" class="detalle_button1">No</button>
                </div>
            </dialog>
<!--        ______________________________________________________________________              -->  
        </form>
        <script src="jsF/cerrarSesion_.js"></script>
        <script src="jsF/rutinas_.js"></script>
        <script src="jsF/funcionalidad_.js"></script>
    </body>
</html>