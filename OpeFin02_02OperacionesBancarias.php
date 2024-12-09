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
    </head>
    <body>

        <form name="frmOpeFin" id="frmOpeFin" method="post" enctype="multipart/form-data">
            <div id="main_container">    
                <?php include('OpeFin00_MenuPrincipal.php'); // Incluye el menú principal?>
                <section class="datos-personales2">
                    <h2 class="titleM">Operaciones Bancarias</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <div class="form-field-angosto inline-block-input4" style="width:9% !important;">
                                    <input type="text" name="idOperacion" id="idOperacion"  class="input-text" required maxlength=4
                                    onkeyup="this.title=this.value;" onblur="exclusivoLetras('Id',this.id)">
                                    <label for="idOperacion" class="label">Id Operación</label>
                                </div>
                                <div class="form-field-angosto inline-block-input3" style="width:20% !important;">
                                    <input type="text" name="nombre" id="nombre" class="input-text" required maxlength=50
                                    onkeyup="this.title=this.value;" onblur="sololetras(this.value,'Nombre',this.id)">
                                    <label for="nombre" class="label">Nombre</label>
                                </div>
                                <div class="form-field-angosto inline-block-input4" style="width:12%!important;">
                                    <p data-name="tipo" class="titles titles-filled">Tipo</p>
                                    <div class="box" style="width:100% !important;">
                                        <select name="tipo" id="tipo" class="select-input" onclick="OperacionSaldoCancelacion();" onblur="OperacionSaldoCancelacion();">
                                            <option value="" >Seleccione</option>
                                            <option value="I">INGRESOS</option>
                                            <option value="E">EGRESOS</option>
                                            <option value="C">CHEQUES</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-field-angosto inline-block-input4" style="width:12%!important;">
                                    <p data-name="operador" class="titles titles-filled">Saldo</p>
                                    <div class="box" style="width:100% !important;">
                                        <select name="operador" id="operador" class="select-input">
                                            <option value="" >Seleccione</option>
                                            <option value="+">SUMA AL SALDO</option>
                                            <option value="-">RESTA AL SALDO</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-field-angosto inline-block-input4" style="width:22%!important;">
                                    <p data-name="idOperCan" class="titles titles-filled">Cancelación</p>
                                    <div class="box" style="width:95% !important;">
                                        <select name="idOperCan" id="idOperCan" class="select-input">
                                            <option value="" >Seleccione</option>
                                            <option value="CIN">CANCELACIÓN DE INGRESOS</option>
                                            <option value="CEG">CANCELACIÓN DE EGRESOS</option>
                                            <option value="CAN">CANCELACIÓN DE CHEQUES</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-field-angosto inline-block-input4" style="width:14%!important;" id="divActivo">
                                    <input type="checkbox" id="visualizar" name="visualizar" checked="checked">
                                    <label for="visualizar" class="labelChk">¿ Visualizar ?</label>
                                </div>
                                <hr>
                                <div class="form-field-button3" id="divBotones">
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="AgregaOperacion();">
                                            <span>Agregar</span>
                                        </a>
                                    </div>
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="ModificaOperacion();">
                                            <span>Modificar</span>
                                        </a>
                                    </div>
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="EliminaOperacion();">
                                            <span>Eliminar</span>
                                        </a>
                                    </div>
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="limpiaPantalla('frmOpeFin');">
                                            <span>Nuevo</span>
                                        </a>
                                    </div>
                                </div>
                                <hr>
                                <div id="paginador" class="pagina"></div>
                                <div class="tabla-con-cuadricula">
                                    <table class="tablex" id="operacionesBancarias">
                                        <caption class="captionTable">Operaciones Bancarias del Sistema</caption>
                                        <thead>
                                            <tr>
                                                <th>Clave</th>
                                                <th>Nombre</th>
                                                <th>Tipo</th>
                                                <th>Operación?</th>
                                                <th>Cancelación</th>
                                                <th>Visualizar</th>
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
<!--        ______________________________________________________________________               --> 
        </form>
        <script src="jsF/cerrarSesion_.js"></script>
        <script src="jsF/rutinas_.js"></script>
        <script src="jsF/Catalogos_.js"></script>
    </body>
</html>