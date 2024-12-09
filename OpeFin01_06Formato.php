<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Formato Cheques</title>
        <link rel="stylesheet" href="assetsF/css/panel_style.css">
        <link rel="stylesheet" href="assetsF/css/seccion.css">
	</head>
	<body>
		<form name="frmFormato" id="frmFormato" method="post" enctype="multipart/form-data">
            <input type="hidden" id="s_usuario"    name="s_usuario"    value="<?= $usrClave ?>">
            <div id="main_container">    
                <?php include('OpeFin00_MenuPrincipal.php'); ?>
                <section class="datos-personales2">
                    <h2 class="titleM">Formato Impresión Cheques</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
<!-- 							_______________________________________________________________________________					-->
								<section class="seccion_caja_despliegue" id="despliegue1">
                                    <div class="caja_captura" id="divRanCta1">
                                        <label class="lbl_txt" for="idCuentabancaria">Cuenta Bancaria</label>
                                    	<select name="idCuentabancaria" id="idCuentabancaria" onfocus="this.click();" 
                                    	data-info="Cuenta Bancaria Inicial"; onchange="ValidaFormato(this.value);"></select>
                                    </div>
								</section>
<!-- 							_______________________________________________________________________________					-->
								<section class="seccion_caja_despliegue" id="despliegue2">
                                    <!-- Encabezados -->
                                    <div class="forms-row">
                                        <div class="cell1"><input type="text" id="r0c1" name="r0c1" readonly value="Elemento"></div>
                                        <div class="cell1"><input type="text" id="r0c2" name="r0c2" readonly value="X"></div>
                                        <div class="cell1"><input type="text" id="r0c3" name="r1c3" readonly value="Y"></div>
                                        <div class="cell1"><input type="text" id="r0c4" name="r1c4" readonly value="Alto"></div>
                                        <div class="cell1"><input type="text" id="r0c5" name="r1c5" readonly value="Ancho"></div>
                                        <div class="cell1"><input type="text" id="r0c6" name="r1c6" readonly value="Fuente"></div>
                                        <div class="cell1"><input type="text" id="r0c7" name="r1c7" readonly value="Tamaño"></div>
                                        <div class="cell1"><input type="text" id="r0c8" name="r1c8" readonly value="Alineación"></div>
                                    </div>
                                    <div class="forms-row">
                                        <div class="cell1"><input type="text" id="r1c1" readonly value="Letra Fecha" pos="01"></div>
                                        <div class="cell"><input type="text" id="r1c2" maxlength="5" pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r1c3" maxlength="5"  pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r1c4" maxlength="4"  pattern="\d{1}(?:\.\d{1,2})?" 
                                            title="#.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r1c5" maxlength="5"  pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r1c6" maxlength="20" pattern="[A-Za-z]{1,20}" 
                                            title="Courier" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r1c7" name="r1c7" maxlength="2" pattern="\d{1,2}" 
                                            title="##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r1c8" name="r1c8" maxlength="1"  pattern="[LRC]?"  
                                            title="Solo L, R o C" onblur="validaPatron(this);"></div>
                                    </div>
                                    <div class="forms-row">
                                        <div class="cell1"><input type="text" id="r2c1" readonly value="Beneficiario" pos="02"></div>
                                        <div class="cell"><input type="text" id="r2c2" maxlength="5" pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r2c3" maxlength="5"  pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r2c4" maxlength="4"  pattern="\d{1}(?:\.\d{1,2})?" 
                                            title="#.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r2c5" maxlength="5"  pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r2c6" maxlength="20" pattern="[A-Za-z]{1,20}" 
                                            title="Courier" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r2c7" name="r1c7" maxlength="2" pattern="\d{1,2}" 
                                            title="##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r2c8" name="r1c8" maxlength="1"  pattern="[LRC]?"  
                                            title="Solo L, R o C" onblur="validaPatron(this);"></div>
                                    </div>
                                    <div class="forms-row">
                                        <div class="cell1"><input type="text" id="r3c1" readonly value="Importe Número" pos="03"></div>
                                        <div class="cell"><input type="text" id="r3c2" maxlength="5" pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r3c3" maxlength="5"  pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r3c4" maxlength="4"  pattern="\d{1}(?:\.\d{1,2})?" 
                                            title="#.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r3c5" maxlength="5"  pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r3c6" maxlength="20" pattern="[A-Za-z]{1,20}" 
                                            title="Courier" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r3c7" name="r1c7" maxlength="2" pattern="\d{1,2}" 
                                            title="##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r3c8" name="r1c8" maxlength="1"  pattern="[LRC]?"  
                                            title="Solo L, R o C" onblur="validaPatron(this);"></div>
                                    </div>
                                    <div class="forms-row">
                                        <div class="cell1"><input type="text" id="r4c1" readonly value="Importe letra" pos="04"></div>
                                        <div class="cell"><input type="text" id="r4c2" maxlength="5" pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r4c3" maxlength="5"  pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r4c4" maxlength="4"  pattern="\d{1}(?:\.\d{1,2})?" 
                                            title="#.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r4c5" maxlength="5"  pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r4c6" maxlength="20" pattern="[A-Za-z]{1,20}" 
                                            title="Courier" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r4c7" name="r1c7" maxlength="2" pattern="\d{1,2}" 
                                            title="##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r4c8" name="r1c8" maxlength="1"  pattern="[LRC]?"  
                                            title="Solo L, R o C" onblur="validaPatron(this);"></div>
                                    </div>
                                    <div class="forms-row">
                                        <div class="cell1"><input type="text" id="r5c1" readonly value="Concepto de pago" pos="05"></div>
                                        <div class="cell"><input type="text" id="r5c2" maxlength="5" pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r5c3" maxlength="5"  pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r5c4" maxlength="4"  pattern="\d{1}(?:\.\d{1,2})?" 
                                            title="#.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r5c5" maxlength="5"  pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r5c6" maxlength="20" pattern="[A-Za-z]{1,20}" 
                                            title="Courier" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r5c7" name="r1c7" maxlength="2" pattern="\d{1,2}" 
                                            title="##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r5c8" name="r1c8" maxlength="1"  pattern="[LRC]?"  
                                            title="Solo L, R o C" onblur="validaPatron(this);"></div>
                                    </div>
                                    <div class="forms-row">
                                        <div class="cell1"><input type="text" id="r6c1" readonly value="Importe Número" pos="06"></div>
                                        <div class="cell"><input type="text" id="r6c2" maxlength="5" pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r6c3" maxlength="5"  pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r6c4" maxlength="4"  pattern="\d{1}(?:\.\d{1,2})?" 
                                            title="#.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r6c5" maxlength="5"  pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r6c6" maxlength="20" pattern="[A-Za-z]{1,20}" 
                                            title="Courier" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r6c7" name="r1c7" maxlength="2" pattern="\d{1,2}" 
                                            title="##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r6c8" name="r1c8" maxlength="1"  pattern="[LRC]?"  
                                            title="Solo L, R o C" onblur="validaPatron(this);"></div>
                                    </div>
                                    <div class="forms-row">
                                        <div class="cell1"><input type="text" id="r7c1" readonly value="Número Cheque" pos="07"></div>
                                        <div class="cell"><input type="text" id="r7c2" maxlength="5" pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r7c3" maxlength="5"  pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r7c4" maxlength="4"  pattern="\d{1}(?:\.\d{1,2})?" 
                                            title="#.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r7c5" maxlength="5"  pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r7c6" maxlength="20" pattern="[A-Za-z]{1,20}" 
                                            title="Courier" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r7c7" name="r1c7" maxlength="2" pattern="\d{1,2}" 
                                            title="##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r7c8" name="r1c8" maxlength="1"  pattern="[LRC]?"  
                                            title="Solo L, R o C" onblur="validaPatron(this);"></div>
                                    </div>
                                    <div class="forms-row">
                                        <div class="cell1"><input type="text" id="r8c1" readonly value="Somire o Documento" pos="08"></div>
                                        <div class="cell"><input type="text" id="r8c2" maxlength="5" pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r8c3" maxlength="5"  pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r8c4" maxlength="4"  pattern="\d{1}(?:\.\d{1,2})?" 
                                            title="#.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r8c5" maxlength="5"  pattern="\d{1,2}(?:\.\d{1,2})?" 
                                            title="##.##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r8c6" maxlength="20" pattern="[A-Za-z]{1,20}" 
                                            title="Courier" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r8c7" name="r1c7" maxlength="2" pattern="\d{1,2}" 
                                            title="##" onblur="validaPatron(this);"></div>
                                        <div class="cell"><input type="text" id="r8c8" name="r1c8" maxlength="1"  pattern="[LRC]?"  
                                            title="Solo L, R o C" onblur="validaPatron(this);"></div>
                                    </div>
								</section>
<!-- 							_______________________________________________________________________________					-->
                                 <section class="seccion_caja" id="botones">
                                    <div class="form-field-button_" id="grpBotones">
                                        <a class="btn_1 efecto" onclick="grabarFormato();" >
                                            <span>Grabar</span>
                                        </a>
                                    </div>
                                </section>
<!-- 							_______________________________________________________________________________					-->
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
                    <p id="dialogMessage">mandaMensaje(mensaje)</p>
                </div>
            </dialog>
<!--        ______________________________________________________________________              -->
            <dialog id="cajaRespuesta" class="dialogo">
                <div class="dialogo_header">
                    <div id="dialogo_close1"  class="claseX">&#8999;</div>
                </div>
                <hr>
                <div class="dialogo_body">
                    <p id="dialogRespuesta">Mensaje para SI o NO</p>
                </div>
                <div class="dialogo_botones">
                    <button id="btnSi" class="detalle_button1">Sí</button>
                    <button id="btnNo" class="detalle_button1">No</button>
                </div>
            </dialog>
<!--        ______________________________________________________________________              -->
        </form>
        <script src="jsF/cerrarSesion_.js"></script>
        <script src="jsF/rutinas_.js"></script>
        <script src="jsF/formatos_.js"></script>
	</body>
</html>