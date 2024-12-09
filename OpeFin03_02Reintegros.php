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
        <form name="formReintegros" id="formReintegros" method="post" enctype="multipart/form-data">
            <div id="main_container">    
                <?php include('OpeFin00_MenuPrincipal.php'); // Incluye el menú principal?>
                <section class="datos-personales2">
                    <h2 class="titleM">Reintegros JL y JD</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <section class="seccion_caja_despliegue" id="despliegue">
                                    <div class="caja_captura">
                                        <label for="idunidad" class="lbl_txt">Unidad</label>
                                         <select name="idunidad" id="idunidad" onfocus="this.click();" data-info="Unidad Responsable";
                                         onblur="cambiaDes(this.value);" onchange="cambiaDes(this.value);"></select>
                                    </div>
                                     <div class="caja_captura">
                                        <label for="nombreunidad" class="lbl_txt">Nombre</label>
                                         <input type="text" name="nombreunidad" id="nombreunidad" readonly disabled/>
                                    </div>
                                    <div class="caja_captura">
                                        <label for="cta_ur" class="lbl_txt">Cuenta</label>
                                         <input type="text" name="cta_ur" id="cta_ur" readonly disabled/>
                                    </div>

                                    <div class="caja_captura">
                                        <label for="folio" class="lbl_txt">Folio</label>
                                         <input type="text" id="folio"/ maxlength="10" pattern="(?:\d+(-\d+)*|S\/F)" 
                                         title="Solo números, numeros con guión o la palabra S/F" onblur="validaPatron(this);"> 
                                    </div> 
                                    <div class="caja_captura">
                                        <!--  pattern="[A-Z0-9\/\-]+\/[A-Z0-9\-]+\/[A-Z0-9\-]+\/[0-9]+\/[0-9]{4}"   -->
                                        <label for="oficio" class="lbl_txt">Oficio</label>
                                         <input type="text" id="oficio"  maxlength="50" 
                                         pattern="[A-Za-z0-9\-\/]+" 
                                         title="Oficio: texto alfanumérico con guiones y barras diagonales, seguido de un número y un año en el formato YYYY. Ejemplo válido: INE-DEA-DRF-SP-669-2024" onblur="validaPatron(this);" />
                                    </div>
                                    <div class="caja_captura">
                                        <label for="monto" class="lbl_txt">Monto</label>
                                        <input type="text" name="monto" id="monto" maxlength="15" pattern="\d+(\.\d{1,2})?"
                                        title="Introduzca un importe positivo con hasta dos decimales. Ejemplo válido: 123.45 o 123."
                                        onblur="validaPatron(this);"/>
                                    </div>
                                    <div class="caja_captura2">
                                        <label for="origen" class="lbl_txt">Origen</label>
                                        <select name="origen" id="origen"></select>
                                    </div>
                                    <div class="caja_captura2">
                                        <label for="tipo" class="lbl_txt">Tipo</label>
                                        <select name="tipo" id="tipo">
                                            <option value="">Seleccione</option>
                                            <option value="P">Pasivo</option>
                                            <option value="E">Economía</option>
                                            <option value="PE">Pasivo,Economía</option>
                                        </select>
                                    </div>
                                    <div class="caja_captura">
                                        <label for="operacion" class="lbl_txt">Operación</label>
                                         <input type="text" name="operacion" id="operacion"  maxlength="20" 
                                         pattern="[0-9]+" title="Introduzca solo números. Ejemplo válido: 123456" onblur="validaPatron(this);"/> 
                                    </div> 
                                    <div class="caja_captura2">
                                        <label for="fecha_ope" class="lbl_txt">Fec Ope</label>
                                         <input type="date" name="fecha_ope" id="fecha_ope"/> 
                                    </div> 
                                    <div class="caja_captura2">
                                        <label for="anio" class="lbl_txt">Año Rein</label>
                                        <input type="text" name="anio" id="anio" maxlength="4" onblur="validaPatron(this);validaAnio(this);"
                                        pattern="20\d{2}" title="Año en formato de cuatro dígitos comenzando con '20'. Ejemplo válido: 2024."
                                        /> 
                                    </div> 
                                    <div class="caja_captura">
                                        <label for="folio_interno" class="lbl_txt">Folio Interno</label>
                                        <input type="text"  name="folio_interno" id="folio_interno" maxlength="20"  onblur="validaPatron(this);"
                                        pattern="\d+(-\d+)*$|^S\/F"
                                        title="Solo números, números con guiones (567-678) o 'S/F'"

                                        />
                                    </div>
                                    <div class="caja_captura">
                                        <label for="idcuentabancaria" class="lbl_txt">Cuenta OFC</label>
                                         <select name="idcuentabancaria" id="idcuentabancaria" onfocus="this.click();" 
                                         data-info="Cuenta OFC";></select>
                                    </div>
                                    <div class="caja_captura">
                                        <label for="idreintegro" class="lbl_txt">Id</label>
                                        <input type="text" name="idreintegro" id="idreintegro" readonly disabled/>
                                    </div>
                                </section>
<!--                            ____________________________________________________________________                -->  
                                <section class="seccion_caja" id="botones">
                                    <div class="form-field-button_" id="grpBotones">
                                        <a class="btn_1 efecto" onclick="GrabarReintegro();">
                                            <span>Grabar</span>
                                        </a>
                                        <a class="btn_1 efecto" onclick="EliminarReintegro();" >
                                            <span>Eliminar</span>
                                        </a>
                                        <a class="btn_1 efecto" onclick="NuevoReintegro('');" >
                                            <span>Nuevo</span>
                                        </a>
                                        <a class="btn_1 efecto" onclick="ReporteReintegrosModal();" >
                                            <span>Reporte</span>
                                        </a>
                                    </div>
                                    <div class="caja_captura"></div>
                                    <div class="caja_captura" id="divLayOut1">
                                        <label for="selectLayOut" class="lbl_txt">Carga x layOut</label>
                                        <select id="selectLayOut" name="selectLayOut" onchange="archivoLayOut(this.value);">
                                            <option value="">Seleccione</option>
                                            <option value="Carga">LayOut</option>
                                        </select>
                                    </div>

                                </section>
<!--                            ____________________________________________________________________                -->
                                <section class="seccion_caja1" id="busqueda">
                                    <div class="caja_captura" caja="cajaSeleReg">
                                        <p data-name="idOperacion" class="titles titles-filled">Registros : </p>
                                        <select name="num_registros" id="num_registros" class="form-select" onchange="paginaReintegros(-1);">
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
                                            <option value="B">Folio</option>
                                            <option value="O">Operacion Banco</option>
                                            <option value="R">Folio Interno</option>
                                            <option value="D">Oficio</option>
                                        </select>
                                    </div>
                                    <div class="caja_captura" caja="cajaBusca">
                                        <label for="campo" class="col-form-label" id="labelFiltro">Buscar: </label>
                                        <input type="text" name="campo" id="campo" class="form-control"> <!-- onkeyup="paginaMovimientos(-1);" -->
                                    </div>
                                    <div class="form-field-button_" id="grpBotones">
                                        <a class="btn_1 efecto" onclick="paginaReintegros(-1);"> 
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
                                        <table class="tablex" id="tablaReintegros">
                                            <thead>
                                                <tr>
                                                    <th>Id</th>
                                                    <th>UR</th>
                                                    <th>Folio</th>
                                                    <th>Oficio</th>   
                                                    <th>Monto</th>  
                                                    <th>Origen</th>
                                                    <th>Economía</th>
                                                    <th>Pasivo</th>
                                                    <th>Operación</th>
                                                    <th>Fecha</th>
                                                    <th>Año</th>
                                                    <th>F.Interno</th>
                                                    <th>Cta.OFC</th>
                                                    <th>Ctrl</th>
                                                    <th>Mov</th>
                                                    <th>U.Alta</th>
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
            <dialog id="cajaReporteReintegros" class="dialogo">
                <div class="dialogo_header">
                    <div id="dialogo_close5" class="claseX">&#8999;</div>
                </div>
                <hr>
                <div class="dialogo_body">
                    <div class="caja_captura">
                        <label class="lbl_txt">Fecha Inicial</label>
                        <input type="date" id="idFecIni"/> <!-- onblur="validaFecha(this.value,'idFechaCan',true);" -->
                    </div>
                    <div class="caja_captura">
                        <label class="lbl_txt">Fecha Final</label>
                        <input type="date" id="idFecFin"/> <!-- onblur="validaFecha(this.value,'idFechaCan',true);" -->
                    </div>
                    <hr>
                    <div class="caja_captura">
                        <label for="anioI" class="lbl_txt">Año Reintegro Ini</label>
                        <input type="text" name="anioRi" id="anioRi" maxlength="4" onblur="validaPatron(this);validaAnio(this);"
                        pattern="20\d{2}" title="Año a cuatro dígitos comenzando con '20'. Ejemplo: 2024."
                        /> 
                    </div> 
                    <div class="caja_captura">
                        <label for="anioF" class="lbl_txt">Año Reintegro Fin</label>
                        <input type="text" name="anioRf" id="anioRf" maxlength="4" onblur="validaPatron(this);validaAnio(this);"
                        pattern="20\d{2}" title="Año a cuatro dígitos comenzando con '20'. Ejemplo: 2024."
                        /> 
                    </div> 
                </div>
                <div class="dialogo_botones">
                    <button id="btnImprime" class="detalle_button1">Reporte</button> <!-- Tiene un add listener el JS -->
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
                        <input type="file" name="ArchivoCarga_file" id="ArchivoCarga_file" class="inputF-file1" accept="XLS,XLSX" onchange="ponArchivoCarga();">
                        <label for="ArchivoCarga_file" class="inputF-label1" id="lblCarga">
                            <i class="large material-icons space" id="input_icon">&#10697;</i>
                            <span id="input_text">
                                Seleccione Archivo XLS
                            </span>
                        </label>
                    </div>
                </div>
                <div class="dialogo_botones">
                    <button id="btnCancelaLayOut" class="detalle_button1"><span id="btn_text">Iniciar Carga</span></button>
                </div>
            </dialog>
<!--        Fin Cancela LayOut ____________________________________________________            -->
        </form>
        <script src="jsF/backspace_.js"></script>
        <script src="jsF/cerrarSesion_.js"></script>
        <script src="jsF/rutinas_.js"></script>
        <script src="jsF/Reintegros_.js"></script>
        <script src="assetsF/js/xlsx.mini.min.js"></script>
</html>