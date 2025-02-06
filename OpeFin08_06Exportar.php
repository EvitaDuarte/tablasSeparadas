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

        <form name="Reportes" id="Reportes" method="post" enctype="multipart/form-data">
            <div id="main_container">    
                <?php include('OpeFin00_MenuPrincipal.php'); // Incluye el menú principal?>
                <section class="datos-personales2">
                    <h2 class="titleM">Exportar</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <section class="seccion_caja_despliegue" id="secCta">
                                    <div class="caja_captura">
                                        <label class="lbl_txt" for="idExportar">Opción</label>
                                         <select name="idExportar" id="idExportar" title="OpcionesReportes" onchange="expoArch();">  
                                            <option value=""         >Seleccionar</option>
                                            <option value="Intereses">Intereses</option>
                                            <option value="Respuesta">Respuesta</option>
                                         </select>
                                    </div>
                                    <div style="display:none;">
                                        <div class="caja_captura" >
                                            <label class="lbl_txt" for="idCuentabancaria">Cuenta</label>
                                             <select name="idCuentabancaria" id="idCuentabancaria" title="Cuenta Bancaria" 
                                             onfocus="this.click();">  
                                             </select>
                                        </div>
                                        <div class="caja_captura1">
                                            <label class="lbl_txt">Fecha Inicial</label>
                                             <input type="date" name="FechaIni" id="FechaIni"/> <!-- onblur="revisaFecha(this);" -->
                                        </div>
                                        <div class="caja_captura1">
                                            <label class="lbl_txt">Fecha Final</label>
                                             <input type="date"  name="FechaFin" id="FechaFin"/> <!-- onblur="revisaFecha(this);" -->
                                        </div>
                                        <div class="caja_captura">
                                            <label class="lbl_txt" for="idSalida">Salida</label>
                                             <select name="idSalida" id="idSalida" title="OpcionesSalida" 
                                                onfocus="this.click();">  
                                                <option value="Pdf">PDF</option>
                                                <option value="Xls">XLS</option>
                                             </select>
                                        </div>
                                    </div>
                                </section>
                                <section class="seccion_caja" id="botones">
                                    <div class="caja_captura">
                                        <div class="form-field-button_" id="grpBotones">
                                                <a class="btn_1 efecto" onclick="expoPdf();">
                                                    <span>PDF</span>
                                                </a>
                                        </div>
                                    </div>
                                </section>
                                <section class="seccion_caja_despliegue_70" id="cuadricula">
                                    <div class="tabla-con-cuadricula">
                                        <table class="tablex" id="tablaVacia">
                                            <thead>
                                                <tr>
                                                    <th>Tra</th>
                                                    <th>CtaOri</th>
                                                    <th>SucOri</th>
                                                    <th>Cuenta</th>
                                                    <th>CtaDes</th>
                                                    <th>SucOfc</th>
                                                    <th>Cuenta</th>
                                                    <th>Importe</th>
                                                    <th>Mon</th>
                                                    <th>UR</th>
                                                    <th>Fecha</th>
                                                    <th>Hora</th>
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
<!--        Ingresa por LayOut _____________________________________________________            -->
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
                    <button id="btnCancelaLayOut" class="detalle_button1"><span id="btn_text">Iniciar Carga</span></button>
                </div>
            </dialog>
<!--        Fin Ingresa LayOut ____________________________________________________            -->
        </form>
        <script src="jsF/cerrarSesion_.js"></script>
        <script src="jsF/rutinas_.js"></script>
        <script src="jsF/Reportes_.js"></script>
    </body>
</html>