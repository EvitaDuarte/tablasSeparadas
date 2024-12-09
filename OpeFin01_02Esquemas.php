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
                <section class="datos-personales">
                    <h2 class="titleM">Esquemas de Usuario</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <div class="form-field inline-block-input" style="width:18% !important;">
                                    <input type="text" name="idEsquema" id="idEsquema"  class="input-text" required maxlength=2
                                    onkeyup="this.title=this.value;" onblur="soloNumeros(this.value,'Id',this.id)">
                                    <label for="idEsquema" class="label">Id Esquema</label>
                                </div>
                                <div class="form-field inline-block-input" style="width:30% !important;">
                                    <input type="text" name="descripcion" id="descripcion" class="input-text" required maxlength=100 
                                    onkeyup="this.title=this.value;" onblur="sololetras(this.value,'Descripción',this.id)" >
                                    <label for="descripcion" class="label">Descripción</label>
                                </div>
                                <div class="form-field inline-block-input" style="width:20%!important;" id="divActivo">
                                    <input type="checkbox" id="estatus" name="estatus" checked="checked">
                                    <label for="estatus" class="labelChk">¿ Activo ?</label>
                                </div>
                                <hr>
                                <div class="form-field-button3">
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="EsquemaAgrega();">
                                            <span>Agregar</span>
                                        </a>
                                    </div>
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="EsquemaModifica();">
                                            <span>Modificar</span>
                                        </a>
                                    </div>
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="EsquemaEliminar();">
                                            <span>Eliminar</span>
                                        </a>
                                    </div>
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="limpiaPantalla('frmEsquema');">
                                            <span>Nuevo</span>
                                        </a>
                                    </div>
                                </div>
                                <hr>
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