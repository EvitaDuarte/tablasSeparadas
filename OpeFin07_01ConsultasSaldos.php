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
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    </head>
    <body>

        <form name="formIngreso" id="formIngreso" method="post" enctype="multipart/form-data">
            <div id="main_container">    
                <?php include('OpeFin00_MenuPrincipal.php'); // Incluye el menú principal?>
                <section class="datos-personales2">
                    <h2 class="titleM">S A L D O S</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <section class="seccion_caja_despliegue" id="secCta">
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Cuenta</label>
                                         <select name="idCuentabancaria" id="idCuentabancaria" onfocus="this.click();"  
                                         onchange="GenerarSaldos();"></select>
                                    </div>
                                     <div class="caja_captura">
                                        <label class="lbl_txt">Fecha Inicial</label>
                                         <input type="date" name="FechaIni" id="FechaIni"/> <!-- onblur="revisaFecha(this);" -->
                                    </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Fecha Final</label>
                                         <input type="date" name="FechaFin" id="FechaFin"/> <!-- onblur="revisaFecha(this);" -->
                                    </div>
                                    <div class="caja_captura">
                                    </div>
                                    <section class="seccion_caja" id="botones">
                                        <div class="caja_captura">
                                            <div class="form-field-button_" id="grpBotones">
                                                <a class="btn_1 efecto" onclick="GenerarSaldos();">
                                                    <span>Consultar</span>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="caja_captura">
                                            <div class="form-field-button_" id="grpBotones">
                                                <a class="btn_1 efecto" onclick="ReporteSaldos();">
                                                    <span>Reporte</span>
                                                </a>
                                            </div>
                                        </div>
                                    </section>
                                </section>
                                <section class="seccion_caja" id="busqueda">
                                    <div class="caja_captura" caja="cajaSeleReg">
                                        <p data-name="idOperacion" class="titles titles-filled">Mostrar : </p>
                                        <select name="num_registros" id="num_registros" class="form-select" onchange="paginaSaldos(-1);">
                                            <option value="15">15</option>
                                            <option value="30">30</option>
                                            <option value="60">60</option>
                                            <option value="100">100</option>
                                        </select>
                                        <label for="num_registros" class="col-form-label">registros </label>
                                    </div>
                                    <div class="caja_captura" caja="cajaBusca">
                                        <label for="campo" class="col-form-label">Buscar: </label>
                                        <input type="text" name="campo" id="campo" class="form-control" onkeyup="paginaSaldos(-1);">
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
                                        <table class="tablex" id="tablaSaldos">
                                            <thead>
                                                <tr>
                                                    <th>Cuenta</th>
                                                    <th>Fecha</th>
                                                    <th>Saldo Inicial</th>
                                                    <th>Ingresos</th>
                                                    <th>Egresos</th>
                                                    <th>Cheques</th>
                                                    <th>Saldo Final</th>
                                                </tr>
                                            </thead>
                                            <tbody id="cuerpo">
                                            </tbody>
                                        </table>
                                    </div>
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
        <script src="jsF/Consultas_.js"></script>
    </body>
</html>