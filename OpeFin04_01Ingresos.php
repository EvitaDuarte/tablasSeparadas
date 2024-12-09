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
                    <h2 class="titleM">Ingresos</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <section class="seccion_caja_despliegue" id="secCta">
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Cuenta</label>
                                         <select name="idCuentabancaria" id="idCuentabancaria" onfocus="this.click();" data-info="Cuenta Bancaria";
                                         onblur="validarSeleccion(this);" onchange="despliegaSaldoHoy(this.value,true);"></select>
                                    </div>
                                    <!-- <div class="caja_captura">
                                        <label class="lbl_txt">Nombre</label>
                                         <input type="text" name="nombreCta" id="nombreCta" readonly disabled></select>
                                    </div> -->
                                     <div class="caja_captura">
                                        <label class="lbl_txt">Fecha</label>
                                         <input type="text" name="FechaHoy" id="FechaHoy"  readonly disabled />
                                    </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Saldo</label>
                                         <input type="text" name="SaldoHoy" id="SaldoHoy" readonly disabled />
                                    </div>
                                </section>

                                <section class="seccion_caja_despliegue" id="despliegue">
                                    <div class="caja_captura" style="width=10%;!important">
                                        <label class="lbl_txt" for="idFecha">Fecha</label>
                                         <input type="date" id="idFecha" onfocus="guardarValorOriginal(this)"; 
                                         onblur="validaFecha1(this)"; onchange="limpiaRecibo();" /> <!-- onblur="validaFecha1(this.value,'idFecha',false);" -->
                                    </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt" for="idUr">UR</label>
                                         <select name="idUr" id="idUr"></select>
                                    </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt" for="idOpera">Operacion</label>
                                         <select name="idOpera" id="idOpera" onchange="filtraControles(this.value);"></select>
                                    </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt" for="idCtrl">Control</label>
                                         <select name="idCtrl" id="idCtrl" onblur="revisarOperacion();"></select>
                                    </div>
                                    <hr>
                                    <div class="form_field_despliegue">
                                        <label class="lbl_txt">Beneficiario:</label>
                                        <input type="text"  name="idBenefi" id="idBenefi" class="input-text" maxlength="150" onblur="this.value = this.value.toUpperCase();soloLetrasNumerosSeparadores(this.value,'Beneficiario',this.id);">
                                    </div>
                                    <div class="form_field_despliegue">
                                        <label class="lbl_txt">Concepto:</label>
                                        <input type="text" name="idCpto" id="idCpto" class="input-text" maxlength="150" onblur="this.value = this.value.toUpperCase();soloLetrasNumerosSeparadores(this.value,'Concepto',this.id);">
                                    </div>
                                    <hr>
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Importe</label>
                                         <input type="text"  id="idImpo" onblur="soloImportesPositivos(this)" data-info="Importe"/>
                                    </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Recibo</label>
                                         <input type="text"  id="idRecibo" readonly onfocus="calculaRecibo(this.value);" />
                                    </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Referencia Bancaria</label>
                                         <input type="text"  id="idRefe" onfocus="guardarValorOriginal(this)"  onblur="this.value = this.value.toUpperCase();validaReferencia(this.value);"/> 
                                    </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Año Ejercicio</label>
                                         <input type="text"  id="idAnio" onblur="soloNumeros(this.value,'Año Ejercicio',this.id);validaAnio_(this.value,this.id);" />
                                    </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt" for="idMovimiento">id</label>
                                         <input type="text" readonly disabled id="idMovimiento"/>
                                    </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt" for="idEstatus">Estatus</label>
                                         <input type="text" readonly disabled id="idEstatus"/>
                                    </div>
                                </section>
<!--                            ____________________________________________________________________                -->                                
                                <section class="seccion_caja" id="botones">
                                    <div class="caja_captura disabled" id="divLayOut1">
                                        <label class="lbl_txt">Acción x LayOut</label>
                                        <select id="selectLayOut" onchange="archivoLayOut(this.value,'Ing');">
                                            <option value="">Seleccione</option>
                                            <option value="Eliminar">Eliminar</option>
                                            <option value="Cancelar">Cancelar</option>
                                        </select>
                                    </div>
                                    <div class="caja_captura"></div>
                                    <div class="form-field-button_" id="grpBotones">
                                        <a class="btn_1 efecto" onclick="GrabarMovimiento();">
                                            <span>Grabar</span>
                                        </a>
                                        <a class="btn_1 efecto" onclick="CancelarMovimiento();" >
                                            <span>Cancelar</span>
                                        </a>
                                        <a class="btn_1 efecto" onclick="EliminarMovimiento();" >
                                            <span>Eliminar</span>
                                        </a>
                                        <a class="btn_1 efecto" onclick="NuevoMovimiento('');" >
                                            <span>Nuevo</span>
                                        </a>
                                    </div>
                                    <div class="caja_captura"></div>
                                    <div class="caja_captura disabled" id="divRecibo">
                                        <label class="lbl_txt" for="seleRecibo">Recibo</label>
                                        <select id="seleRecibo" onchange="jsReciboIngreso(this.value);">
                                            <option value="">Seleccione</option>
                                            <option value="ImpRecIng">Imprimir</option>
                                            <option value="DesRecIng">Desglose</option>
                                        </select>
                                    </div>
                                </section>
<!--                            ____________________________________________________________________                -->
                                <section class="seccion_caja" id="busqueda">
                                    <div class="caja_captura" caja="cajaSeleReg">
                                        <p data-name="idOperacion" class="titles titles-filled">Mostrar : </p>
                                        <select name="num_registros" id="num_registros" class="form-select" onchange="paginaMovimientos(-1);">
                                            <option value="15">15</option>
                                            <option value="30">30</option>
                                            <option value="60">60</option>
                                            <option value="100">100</option>
                                        </select>
                                        <label for="num_registros" class="col-form-label">registros </label>
                                    </div>
                                    <div class="caja_captura" id="filtroBusqueda">
                                        <label   for="opcionesFiltro" class="lbl_txt">Filtrar por</label>
                                        <select name="opcionesFiltro" id="opcionesFiltro" class="form-select" onchange="CambiaLetrero();">
                                            <option value="">Buscar</option>
                                            <option value="I">Importe</option>
                                            <option value="F">Fecha</option>
                                            <option value="B">Beneficiario</option>
                                            <option value="C">Concepto</option>
                                            <option value="O">Operacion Banco</option>
                                            <option value="R">Recibo Ingresos</option>
                                        </select>
                                    </div>
                                    <div class="caja_captura" caja="cajaBusca">
                                        <label id="labelFiltro" for="campo" class="col-form-label">Buscar: </label>
                                        <input type="text" name="campo" id="campo" class="form-control"> <!-- onkeyup="paginaMovimientos(-1);" -->
                                    </div>
                                    <div class="form-field-button_" id="grpBotones">
                                        <a class="btn_1 efecto" onclick="paginaMovimientos(-1);"> 
                                            <span>Buscar</span>
                                        </a>
                                    </div>
                                    <div class="caja_captura"></div>
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
                                        <table class="tablex" id="tablaIngresos">
                                            <thead>
                                                <tr>
                                                    <th>Cuenta</th>
                                                    <th>Recibo</th>
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
<!--        Cancela Movimiento Individual __________________________________________            -->
            <dialog id="cajaCancela" class="dialogo">
                <div class="dialogo_header">
                    <div id="dialogo_close2" class="claseX">&#8999;</div>
                </div>
                <hr>
                <div class="dialogo_body">
                    <div class="caja_captura">
                        <label class="lbl_txt">Fecha(dd/mm/yyyy)</label>
                        <input type="date" id="idFechaCan" onblur="validaFecha1(this)";/> <!-- onblur="validaFecha(this.value,'idFechaCan',true);" -->
                    </div>
                </div>
                <div class="dialogo_botones">
                    <button id="btnRegresa" class="detalle_button1">Aceptar</button>
                </div>
            </dialog>
<!--        Cancela por LayOut _____________________________________________________            -->
            <dialog id="cajaCancelaLayOut" class="dialogo">
                <div class="dialogo_header">
                    <div id="dialogo_close3" class="claseX">&#8999;</div>
                </div>
                <hr>
                <div class="dialogo_body">
                    <div class="caja_captura">
                        <input type="file" name="ArchivoCarga_file" id="ArchivoCarga_file" class="inputF-file1" accept=".txt,.TXT" onchange="ponArchivoCarga();">
                        <label for="ArchivoCarga_file" class="inputF-label1" id="lblCarga">
                            <i class="large material-icons space" id="input_icon">&#10697;</i>
                            <span id="input_text">
                                Seleccione Archivo de ......
                            </span>
                        </label>
                    </div>
                </div>
                <div class="dialogo_botones">
                    <button id="btnCancelaLayOut" class="detalle_button1"><span id="btn_text">Iniciar Cancelación</span></button>
                </div>
            </dialog>
<!--        Fin Cancela LayOut ____________________________________________________            -->
        </form>
        <script src="jsF/backspace_.js"></script>
        <script src="jsF/cerrarSesion_.js"></script>
        <script src="jsF/numeroletras_.js"></script>
        <script src="jsF/rutinas_.js"></script>
        <script src="jsF/Ingresos_.js"></script>
</html>