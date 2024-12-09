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
                <section class="datos-personales">
                    <h2 class="titleM">Catálogo de Accesos</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <div class="form-field inline-block-input" style="width:50%!important;">
                                    <p data-name="idCuentaBancaria" class="titles titles-filled">Cuenta Bancaria</p>
                                    <div class="box" style="width:95% !important;">
                                        <select name="idCuentaBancaria" id="idCuentaBancaria" class="select-input"></select>
                                    </div>
                                </div>
                                <div class="form-field inline-block-input" style="width:30%!important;">
                                    <p data-name="idUsuario" class="titles titles-filled">Usuario</p>
                                    <div class="box" style="width:95% !important;">
                                        <select name="idUsuario" id="idUsuario" class="select-input"></select>
                                    </div>
                                </div>
                                <hr>
                                <div class="form-field-button3">
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="AccesosAgrega();">
                                            <span>Agregar</span>
                                        </a>
                                    </div>
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="AccesosBusca();">
                                            <span>Buscar</span>
                                        </a>
                                    </div>
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="AccesosElimina();">
                                            <span>Eliminar</span>
                                        </a>
                                    </div>
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="limpiaPantalla('frmAcceso');">
                                            <span>Nuevo</span>
                                        </a>
                                    </div>
                                </div>
                                <hr>
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