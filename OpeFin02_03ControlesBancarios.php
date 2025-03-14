<!DOCTYPE html>
<html lang="es">
    <head>
        <?php
            require_once("OpeFin00_00VarSesion.php"); // Es necesario para que cierre por inactividad
        ?>
        <meta charset="uft-8" />
        <title><?=$v_TituloS?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-------------General Style's--------------->
        <link rel="stylesheet" href="assetsF/css/panel_style.css">
        <link rel="stylesheet" href="assetsF/css/seccion.css">
    </head>
    <body>

        <form name="frmCtrlBan" id="frmCtrlBan" method="post" enctype="multipart/form-data">
            <div id="main_container">    
                <?php include('OpeFin00_MenuPrincipal.php'); // Incluye el menú principal?>
                <section class="datos-personales2">
                    <h2 class="titleM">Controles Bancarios</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <section class="seccion_caja" id="captura">
                                    <div class="caja_captura" caja="cajaIdCtrl">
                                        <label class="lbl_txt">Id Control</label>
                                        <input type="text" name="idControl" id="idControl" required maxlength=15
                                        onkeyup="this.title=this.value;" oninput="this.value = this.value.toUpperCase();" onblur="exclusivoLetras('Id Control',this.id);">
                                    </div>
                                    <div class="caja_captura" id="cajaOpe">
                                        <label for="idOperacion" class="lbl_txt">Id Operación Bancaria</label>
                                        <select name="idOperacion" id="idOperacion"></select>
                                    </div>
                                    <div class="caja_captura1" id="cajaNombre" style="width:50% !important;">
                                        <label class="lbl_txt" for="nombre">Nombre</label>
                                        <input type="text" name="nombre" id="nombre" required maxlength=80
                                        onkeyup="this.title=this.value;" oninput="this.value = this.value.toUpperCase();" onblur="sololetras(this.value,'Nombre',this.id);" >
                                    </div>
                                </section>
                                <section class="seccion_caja" id="botones">
                                    <div class="form-field-button_" >
                                        <a class="btn_1 efecto" onclick="AgregaControl();">
                                            <span>Agregar</span>
                                        </a>
                                        <a class="btn_1 efecto" onclick="ModificaControl();">
                                            <span>Modificar</span>
                                        </a>
                                        <a class="btn_1 efecto" onclick="EliminaControl();">
                                            <span>Eliminar</span>
                                        </a>
                                        <a class="btn_1 efecto" onclick="limpiaPantalla('frmCtrlBan');">
                                            <span>Nuevo</span>
                                        </a>
                                    </div>
                                </section>
                                <section class="seccion_caja" id="busqueda">
                                    <div class="caja_captura" caja="cajaSeleReg">
                                        <p data-name="idOperacion" class="titles titles-filled">Mostrar : </p>
                                        <select name="num_registros" id="num_registros" class="form-select" onchange="paginaControlBancario(-1);">
                                            <option value="15">15</option>
                                            <option value="30">30</option>
                                            <option value="60">60</option>
                                            <option value="100">100</option>
                                        </select>
                                        <label for="num_registros" class="col-form-label">registros </label>
                                    </div>
                                    <div class="caja_captura" caja="cajaBusca">
                                        <label for="campo" class="col-form-label">Buscar: </label>
                                        <input type="text" name="campo" id="campo" class="form-control" onkeyup="paginaControlBancario(-1);">
                                    </div>
                                    <div class="caja_captura" id="cajaPagina">
                                        <div class="mirow">
                                            <div class="micol-6">
                                                <label id="lbl-total"></label>
                                            </div>
                                            <div class="col-6" id="nav-paginacion"></div>
                                            <input type="hidden" id="pagina" value="1">
                                        </div>
                                    </div>
                                </section>
                                <section class="seccion_caja_despliegue_70" id="cuadricula">
                                    <div class="tabla-con-cuadricula">
                                        <table class="tablex" id="controlesBancarios">
                                            <thead>
                                                <tr>
                                                    <th>Id Control</th>
                                                    <th>Id Operación</th>
                                                    <th>Nombre</th>
                                                    <th>Tipo</th>
                                                    <th>Capturó</th>
                                                    <th>Fecha Alta</th>
                                                </tr>
                                            </thead>
                                            <tbody id="cuerpo">
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- <section class="seccion_caja" id="paginacion"> 
                                    </section> -->
                                </section>
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