<!DOCTYPE html>
<html lang="es">
    <head>
        <?php
            require_once("OpeFin00_00VarSesion.php"); // Pone disponible las variables de sesión
        ?>
        <meta charset="uft-8" />
        <title><?=$v_TituloS?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-------------General Style's--------------->
        <link rel="stylesheet" href="assetsF/css/panel_style.css">
        <link rel="stylesheet" href="assetsF/css/seccion.css">
        <div id="focus-table"></div>
    </head>
    <body>

        <form name="frmUsuario" id="frmUsuario" method="post" enctype="multipart/form-data">
            <div id="main_container">    
                <?php include('OpeFin00_MenuPrincipal.php'); // Incluye el menú principal?>
                <section class="datos-personales2">
                    <h2 class="titleM">Catálogo de Usuarios</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <div class="form-field inline-block-input" style="width:18% !important;">
                                    <input type="text" name="idUsuario" id="idUsuario"  class="input-text" required maxlength=30
                                    onkeyup="this.title=this.value;" onblur="validaLdap();">
                                    <label for="idUsuario" class="label">Id Usuario</label>
                                </div>
                                <div class="form-field inline-block-input" style="width:30% !important;">
                                    <input type="text" name="nombre" id="nombre" class="input-text" required maxlength=200 
                                    onkeyup="this.title=this.value;" oninput="this.value = this.value.toUpperCase();" 
                                    onblur="sololetras(this.value,'Nombre',this.id);">
                                    <label for="nombre" class="label">Nombre</label>
                                </div>
                                <div class="form-field inline-block-input" style="width:7% !important;">
                                    <input type="text" name="idUnidad" id="idUnidad"  data-info="U.R." class="input-text" required maxlength=4
                                    onkeyup="this.title=this.value;" onblur="exclusivoUR(this);">
                                    <label for="idUnidad" class="label">U.R.</label>
                                </div>
                                <div class="form-field inline-block-input" style="width:18%!important;">
                                    <p data-name="idEsquema" class="titles titles-filled">Esquema</p>
                                    <div class="box" style="width:95% !important;">
                                        <select name="idEsquema" id="idEsquema" data-info="Esquema" 
                                        class="select-input" onblur="validarSeleccion(this)">
                                        </select>
                                    </div>
                                </div>
                                <div class="form-field inline-block-input" style="width:10%!important;" id="divActivo">
                                    <input type="checkbox" id="estatus" name="estatus" checked="checked">
                                    <label for="estatus" class="labelChk">¿ Activo ?</label>
                                </div>
                                <hr>
                                <div class="form-field-button3">
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="UsuarioAgrega();">
                                            <span>Agregar</span>
                                        </a>
                                    </div>
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="UsuarioModifica();">
                                            <span>Modificar</span>
                                        </a>
                                    </div>
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="UsuarioEliminar();">
                                            <span>Eliminar</span>
                                        </a>
                                    </div>
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="limpiaPantalla('frmUsuario');">
                                            <span>Nuevo</span>
                                        </a>
                                    </div>
                                </div>
                                <hr>
                                <div class="tabla-con-cuadricula">
                                    <table class="tablex" id="usuarios">
                                        <caption class="captionTable">Usuarios del Sistema</caption>
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Unidad</th>
                                                <th>Estatus</th>
                                                <th>Esquema</th>
                                                <th>Capturó</th>
                                                <th>Fecha Alta</th>
                                                <th>IdEsquema</th>
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
        <script src="jsF/Usuarios_.js"></script>
    </body>
</html>