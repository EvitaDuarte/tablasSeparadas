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

        <form name="form1" id="form1" method="post" enctype="multipart/form-data">
            <div id="main_container">    
                <?php include('OpeFin00_MenuPrincipal.php'); // Incluye el menú principal?>
                <section class="datos-personales2">
                    <h2 class="titleM">Buzón</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <section class="seccion_caja" id="captura">
                                    <div class="caja_captura boton">
                                        <input type="file" name="ArchivoCarga_file" id="ArchivoCarga_file" class="inputF-file1" accept=".csv,.CSV">
                                        <label for="ArchivoCarga_file" class="inputF-label1" id="lblCarga">
                                            <i class="large material-icons space" id="input_icon">add_to_photos</i>
                                            <span id="input_text">
                                                Elige un archivo
                                            </span>
                                        </label>
                                    </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Cuenta</label>
                                         <select name="idCuentabancaria" id="idCuentabancaria"></select>
                                     </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Operación-Control</label>
                                         <select name="idOpeCtrl" id="idOpeCtrl"></select>
                                     </div>
                                     <div class="caja_captura">
                                        <label class="lbl_txt">Fecha Integración</label>
                                         <input type="date" id="idFechaMovs" onblur="validaFecha1(this)";/>
                                     </div>
                                     
                                </section>

                                <section class="seccion_caja" id="botones">
                                    <div class="form-field-button_" >
                                        <a class="btn_1 efecto" onclick="BuzonMarcar();">
                                            <span>Marcar-Desmarcar</span>
                                        </a>
                                        <a class="btn_1 efecto" onclick="BuzonValidar();" >
                                            <span>Integrar</span>
                                        </a>
                                    </div>
                                </section>

                                <section class="seccion_caja_despliegue" id="despliegue">
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Docto</label>
                                         <input type="text" readonly id="idDocto"/>
                                    </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Referencia</label>
                                         <input type="text" readonly id="idRefe"/>
                                    </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Importe</label>
                                         <input type="text" readonly id="idImpo"/>
                                    </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Ur</label>
                                         <input type="text" readonly id="idUr"/>
                                    </div>
                                    <div class="form_field_despliegue">
                                        <label class="lbl_txt">Beneficiario:</label>
                                        <input type="text" readonly name="txtBeneficia" id="txtBeneficia" class="input-text">
                                    </div>
                                    <div class="form_field_despliegue">
                                        <label class="lbl_txt">Concepto:</label>
                                        <input type="text" readonly name="txtCpto" id="txtCpto" class="input-text">
                                    </div>
                                </section>
                                <section class="seccion_caja_despliegue_70" id="cuadricula">
                                    <div class="form-field-button_" >
                                    <div id="paginador" class="pagina"></div>
                                        <div class="form_field_despliegue">
                                            <label class="lbl_txt">Búsqueda:</label>
                                            <input type="text" name="txtBusca" id="txtBusca" onkeyup="BusquedaTabla('buzon','txtBusca')" class="input-text">
                                        </div>
                                    </div>
                                    
                                    <div class="tabla-con-cuadricula">
                                        <table class="tablex" id="buzon">
                                            <thead>
                                                <tr>
                                                    <th>Beneficiario</th>
                                                    <th>Importe</th>
                                                    <th>Concepto</th>
                                                    <th>Referencia</th>
                                                    <th>Docto</th>
                                                    <th>UR</th>
                                                    <th>Sele</th>
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
        </form>
        <script src="jsF/backspace_.js"></script>
        <script src="jsF/cerrarSesion_.js"></script>
        <script src="jsF/rutinas_.js"></script>
        <script src="jsF/Buzon_.js"></script>
        <script type="text/javascript">
            efectoBotones("Buzon"); // Cambia el titulo del boton de carga de archivo CSV
        </script>
    </body>
</html>