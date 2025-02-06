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
        
        <form name="EdoPosFinDia" id="EdoPosFinDia" method="post" enctype="multipart/form-data">
            <div id="main_container">    
                <?php include('OpeFin00_MenuPrincipal.php'); // Incluye el menú principal?>
                <section class="datos-personales2">
                    <h2 class="titleM">Posición Financiera Mensual</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <section class="seccion_caja_despliegue" id="secCta">
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Cuenta</label>
                                         <select name="idCuentabancaria" id="idCuentabancaria" title="Cuenta Bancaria" onfocus="this.click();">  
                                         </select>
                                    </div>
                                     <div class="caja_captura">
                                        <label class="lbl_txt">Año</label>
                                         <input type="text" name="idAnio" id="idAnio" title="Año del reporte" onblur="soloNumeros(this.value,'Año Reporte',this.id);validaAnio(this.value,this.id);"/>
                                    </div>
                                     <div class="caja_captura">
                                        <label class="lbl_txt">Mes</label>
                                        <select name="idMes" id="idMes" title="Mes del reporte" onfocus="this.click();">
                                            <option value="">Seleccione</option>
                                            <option value="01">Enero</option>
                                            <option value="02">Febrero</option>
                                            <option value="03">Marzo</option>
                                            <option value="04">Abril</option>
                                            <option value="05">Mayo</option>
                                            <option value="06">Junio</option>
                                            <option value="07">Julio</option>
                                            <option value="08">Agosto</option>
                                            <option value="09">Septiembre</option>
                                            <option value="10">Octubre</option>
                                            <option value="11">Noviembre</option>
                                            <option value="12">Diciembre</option>
                                        </select>
                                    </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt" for="idSalida">Salida</label>
                                        <select name="idSalida" id="idSalida" title="OpcionesSalida" 
                                            onfocus="this.click();">
                                            <option value="Pdf">PDF</option>
                                            <option value="Xls">CSV</option>
                                        </select>
                                    </div>
                                    <section class="seccion_caja" id="botones">
                                        <div class="caja_captura">
                                            <div class="form-field-button_" id="grpBotones">
                                                <a class="btn_1 efecto" onclick="GeneraPosFinMes();">
                                                    <span>Posicion Financiera Mensual</span>
                                                </a>
                                            </div>
                                        </div>
                                    </section>
                                </section>
                                <section class="seccion_caja_despliegue_70" id="cuadricula">
                                    <div class="tabla-con-cuadricula">
                                        <table class="tablex" id="tablaVacia">
                                            <thead>
                                                <tr>
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
        <script src="jsF/Reportes_.js"></script>
    </body>
</html>