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
                    <h2 class="titleM">M O V I M I E N T O S</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <section class="seccion_caja_despliegue" id="secCta">
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Cuenta Inicial</label>
                                         <select name="idCtaIni" id="idCtaIni"></select>
                                    </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Cuenta Final</label>
                                         <select name="idCtaFin" id="idCtaFin"></select>
                                    </div>
                                     <div class="caja_captura1">
                                        <label class="lbl_txt">Fecha Inicial</label>
                                         <input type="date" name="FechaIni" id="FechaIni"/> <!-- onblur="revisaFecha(this);" -->
                                    </div>
                                    <div class="caja_captura1">
                                        <label class="lbl_txt">Fecha Final</label>
                                         <input type="date"  name="FechaFin" id="FechaFin"/> <!-- onblur="revisaFecha(this);" -->
                                    </div>
                                    <div class="caja_captura1">
                                        <label class="lbl_txt">Importe Inicial</label>
                                         <input type="text" class="corto" name="importeIni" id="importeIni" value="-999999999.99" style="width:70% !important" />
                                    </div>
                                    <div class="caja_captura1">
                                        <label class="lbl_txt">Importe Final</label>
                                         <input type="text" class="corto" name="importeFin" id="importeFin" value="999999999.99" style="width:70% !important"/>
                                    </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Buscar</label>
                                        <select name="idBusca" id="idBusca">
                                            <option value="">Seleccione</option>
                                            <option value="beneficiario">Beneficiario</option>
                                            <option value="concepto">Concepto</option>
                                            <option value="folio">Documento</option>
                                            <option value="referenciabancaria">Referencia</option>
                                            <option value="idunidad">U.R.</option>
                                        </select>
                                    </div>
                                    <div class="form_field_despliegue">
                                        <label class="lbl_txt">Se busca:</label>
                                        <input type="text"  name="idValor" id="idValor" class="input-text" maxlength="150" 
                                        onblur="this.value = this.value.toUpperCase();">
                                    </div>
                                </section>
                                <section class="seccion_caja" id="botones">
                                    <div class="caja_captura">
                                        <div class="form-field-button_" id="grpBotones">
                                            <a class="btn_1 efecto" onclick="BuscarMovimientos(-1);">
                                                <span>Buscar</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="caja_captura"></div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt" for="idSalida">Salida</label>
                                        <select name="idSalida" id="idSalida" title="OpcionesSalida" 
                                            onfocus="this.click();">
                                            <option value="Pantalla">Pantalla</option>
                                            <option value="Pdf">PDF</option>
                                            <option value="Csv">CSV</option>
                                        </select>
                                    </div>
                                </section>
                                <section class="seccion_caja" id="busqueda">
                                    <div class="caja_captura" caja="cajaSeleReg">
                                        <p data-name="idOperacion" class="titles titles-filled">Mostrar : </p>
                                        <select name="num_registros" id="num_registros" class="form-select" onchange="BuscarMovimientos(-1);">
                                            <option value="15">15</option>
                                            <option value="30">30</option>
                                            <option value="60">60</option>
                                            <option value="100">100</option>
                                        </select>
                                        <label for="num_registros" class="col-form-label">registros </label>
                                    </div>
                                    <div class="caja_captura" caja="cajaBusca">
                                        <label for="campo" class="col-form-label">Buscar: </label>
                                        <input type="text" name="campo" id="campo" class="form-control" onkeyup="BuscarMovimientos(-1);">
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
                                                    <th>Documento</th>
                                                    <th>Referencia</th>
                                                    <th>Importe</th>
                                                    <th>Fecha</th>
                                                    <th>Beneficiario</th>
                                                    <th>Concepto</th>
                                                    <th>UR</th>
                                                    <th>Operacion</th>
                                                    <th>Control</th>
                                                    <th>Año</th>
                                                    <th>Id</th>
                                                    <th>Estatus</th>
                                                    <th>Usuario</th>
                                                    <th>F.Alta</th>
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
                    <p id="dialogRespuesta">Para que el usuario responda</p>
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