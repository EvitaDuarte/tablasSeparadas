<!DOCTYPE html>
<html lang="es">
    <head>
        <?php
            require_once("OpeFin00_00VarSesion.php");
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
        <form name="frmEsquema" id="frmEsquema" method="post" enctype="multipart/form-data">
            <div id="main_container">    
                <?php include('OpeFin00_MenuPrincipal.php'); ?>
                <section class="datos-personales2">
                    <h2 class="titleM">Esquemas de Usuario</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <section class="seccion_caja_despliegue" id="secCap">
                                    <div class="caja_captura">
                                        <label for="idEsquema" class="lbl_txt">Id Esquema</label>
                                        <input type="text" name="idEsquema" id="idEsquema"  class="input-text" required maxlength=2
                                        onkeyup="this.title=this.value;" onblur="soloNumeros(this.value,'Id',this.id)">
                                    </div>
                                    <div class="caja_captura">
                                        <label for="descripcion" class="lbl_txt">Descripción</label>
                                        <input type="text" name="descripcion" id="descripcion" class="input-text" required maxlength=100 
                                        onkeyup="this.title=this.value;" onblur="sololetras(this.value,'Descripción',this.id)" >
                                    </div>
                                   <div class="caja_captura">
                                        <label for="estatus" class="lbl_txt">¿ Activo ?</label>
                                        <input type="checkbox" id="estatus" name="estatus" checked="checked" style="visibility: visible; display: inline-block;">
                                    </div>
                                </section>
                                <section class="seccion_caja" id="botones">
                                    <div class="form-field-button_" id="grpBotones">
                                            <a class="btn_1 efecto" onclick="EsquemaAgrega();">
                                                <span>Agregar</span>
                                            </a>
                                            <a class="btn_1 efecto" onclick="EsquemaModifica();">
                                                <span>Modificar</span>
                                            </a>
                                            <a class="btn_1 efecto" onclick="EsquemaEliminar();">
                                                <span>Eliminar</span>
                                            </a>
                                            <a class="btn_1 efecto" onclick="limpiaPantalla('frmEsquema');">
                                                <span>Nuevo</span>
                                            </a>
                                    </div>
                                </section>

                                <div class="tabla-con-cuadricula">
                                    <table class="tablex" id="esquemas">
                                        <caption class="captionTable">Esquemas de Usuario</caption>
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Descripción</th>
                                                <th>Activo ?</th>
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
        <script src="jsF/esquemas_.js"></script>
    </body>
</html>