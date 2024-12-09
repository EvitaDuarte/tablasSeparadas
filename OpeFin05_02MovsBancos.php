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
        <input type="hidden" id="conciRespa"/>
        <input type="hidden" id="fechaRespa"/>
        <form name="formConcilia" id="formConcilia" method="post" enctype="multipart/form-data">
            <div id="main_container">    
                <?php include('OpeFin00_MenuPrincipal.php'); // Incluye el menú principal?>
                <section class="datos-personales2">
                    <h2 class="titleM">Conciliación - Bancos</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <section class="seccion_caja_Izquierda" id="secCta">
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Cuenta</label>
                                         <select name="idCuentabancaria" id="idCuentabancaria" onfocus="this.click();" data-info="Cuenta Bancaria";
                                         onblur="validarSeleccion(this);" 
                                         onclick="consultaMovsBancos(this.value,true);"></select>
                                    </div>
                                    <div class="caja_captura" style="width=10%;!important">
                                        <label for="idFecha" class="lbl_txt">Fecha Operación</label>
                                         <input type="date" id="idFecha"; /> 
                                    </div> 
                                     <div class="caja_captura">
                                    </div>
                                    <div class="form-field-button_" id="grp_Botones1">
                                            <a class="btn_1 efecto" onclick="ConciliaMovBanco();">
                                                <span>Conciliar</span>
                                            </a>
                                            <a class="btn_1 efecto" onclick="ReporteConciliacion();">
                                                <span>Reporte</span>
                                            </a>
                                    </div>
                                </section>

                                <section class="seccion_caja_despliegue" id="despliegue">

                                    <div class="caja_captura2">
                                        <label for="idOpera" class="lbl_txt">Operacion</label>
                                        <select name="idOpera" id="idOpera" onfocus="this.click();" data-info="Operación Bancaria";></select>
                                    </div>
                                    <div class="caja_captura">
                                        <label for="idImpo" class="lbl_txt">Importe</label>
                                         <input type="text"  id="idImpo" onblur="soloImportesPositivos(this);" data-info="Importe"/>
                                    </div>
                                    <div class="caja_captura">
                                        <label  for="idRefe" class="lbl_txt">Referencia Bancaria</label>
                                         <input type="text"  id="idRefe"  maxlength="30" onfocus="guardarValorOriginal(this)"  
                                         onblur="this.value = this.value.toUpperCase();
                                         soloLetrasNumerosGuion(this.value,'Referencia del Banco','idRefe');
                                         validaReferenciaBancos(this);"
                                         data-info="Referencia del Banco" /> 
                                    </div>
                                    <div class="caja_captura2">
                                        <label for="idStaConci" class="lbl_txt">Conciliado</label>
                                         <input type="text" name="idStaConci"  id="idStaConci" 
                                         onfocus="seleccionaTexto(event);" 
                                         onkeyup="this.value = this.value.toUpperCase();" 
                                         onblur="valoresUnicos(this);" data-info="Conciliado"
                                         pattern="[SN ]" maxlength="1"/>
                                    </div>
                                    <div class="caja_captura" style="width=10%;!important">
                                        <label for="idFecConci" class="lbl_txt">F. Conciliacion</label>
                                         <input type="date" name="idFecConci" id="idFecConci"/> 
                                    </div> 
                                    <div class="caja_captura2">
                                        <label  class="lbl_txt" for="id_concimovimiento">Id Conci Mov</label>
                                         <input type="text" readonly disabled id="id_concimovimiento"/>
                                    </div>
                                    <div class="caja_captura2">
                                        <label class="lbl_txt" for="idCveLay">Clave LayOut</label>
                                         <input type="text" readonly disabled id="idCveLay"/>
                                    </div>
                                    <div class="caja_captura2">
                                        <label  class="lbl_txt" for="id_movIne">Id Mov INE</label>
                                         <input type="text" readonly disabled id="id_movIne"/>
                                    </div>
                                    <hr>
                                    <div class="form_field_despliegue">
                                        <label for="idCpto" class="lbl_txt">Concepto:</label>
                                        <input type="text" name="idCpto" id="idCpto" class="input-text"  maxlength="80" 
                                        onblur="this.value = this.value.toUpperCase();soloLetrasNumerosSeparadores(this.value,'Concepto',this.id);"
                                        />
                                    </div>
                                    <hr>


                                </section>
<!--                            ____________________________________________________________________                -->    
                                <section class="seccion_caja" id="botones">
                                    <div class="caja_captura"></div>
                                    <div class="form-field-button_" id="grp_Botones2">
                                        <a class="btn_1 efecto" onclick="GrabarMovimiento();">
                                            <span>Grabar</span>
                                        </a>
                                        <a class="btn_1 efecto" onclick="EliminarMovimiento();" >
                                            <span>Eliminar</span>
                                        </a>
                                        <a class="btn_1 efecto" onclick="NuevoMovimiento('');" >
                                            <span>Nuevo</span>
                                        </a>
                                    </div>
                                    <div class="caja_captura"></div>
                                </section>                    
<!--                            ____________________________________________________________________                -->
                                <section class="seccion_caja1" id="busqueda">
                                    <div class="caja_captura" caja="cajaSeleReg">
                                        <p data-name="idOperacion" class="titles titles-filled">Registros : </p>
                                        <select name="num_registros" id="num_registros" class="form-select" onchange="paginaBancos(-1);">
                                            <option value="15">15</option>
                                            <option value="30">30</option>
                                            <option value="60">60</option>
                                            <option value="100">100</option>
                                        </select>
                                    </div>
                                    <div class="caja_captura" id="filtroBusqueda">
                                        <label  for="opcionesFiltro" class="lbl_txt">Filtrar por</label>
                                        <select name="opcionesFiltro" id="opcionesFiltro" class="form-select" onchange="CambiaLetrero();">
                                            <option value="">Buscar</option>
                                            <option value="I">Importe</option>
                                            <option value="F">Fecha</option>
                                            <option value="C">Concepto</option>
                                            <option value="O">Operacion Banco</option>
                                        </select>
                                    </div>
                                    <div class="caja_captura" caja="cajaBusca">
                                        <label for="campo" class="col-form-label" id="labelFiltro">Buscar: </label>
                                        <input type="text" name="campo" id="campo" class="form-control"> <!-- onkeyup="paginaBancos(-1);" -->
                                    </div>
                                    <div class="form-field-button_" id="grpBotones">
                                        <a class="btn_1 efecto" onclick="paginaBancos(-1);"> 
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
                                        <table class="tablex" id="tablaConcilia">
                                            <thead>
                                                <tr>
                                                    <th>Id</th>
                                                    <th>Cuenta</th>
                                                    <th>Clave</th>
                                                    <th>Conciliado</th>
                                                    <th>F.Conci</th>
                                                    <th>Importe</th>
                                                    <th>Operación</th>
                                                    <th>Fecha</th>
                                                    <th>Concepto</th>
                                                    <th>Cve Banco</th>
                                                    <th>Id MovIne</th>
                                                    <th>Usu.Alta</th>
                                                    <th>Fecha.Alta</th>
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
            <dialog id="conciliaMovimiento" class="dialogo">
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
            <dialog id="cajaCancelaLayOut" class="dialogo"> <!-- No cambiar Id -->
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
                    <button id="btnCancelaLayOut" class="detalle_button1"><span id="btn_text">Iniciar Conciliación</span></button>
                </div>
            </dialog>
<!--        Fin Cancela LayOut ____________________________________________________            -->
        </form>
        <script src="jsF/backspace_.js"></script>
        <script src="jsF/cerrarSesion_.js"></script>
        <script src="jsF/rutinas_.js"></script>
        <script src="jsF/ConciliaBancos_.js"></script>
</html>