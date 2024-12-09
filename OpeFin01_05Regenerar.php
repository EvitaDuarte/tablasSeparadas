<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Reconstruir Saldos</title>
        <link rel="stylesheet" href="assetsF/css/panel_style.css">
        <link rel="stylesheet" href="assetsF/css/seccion.css">
	</head>
	<body>
		<form name="frmRestaura" id="frmRestaura" method="post" enctype="multipart/form-data">
            <input type="hidden" id="s_usuario"    name="s_usuario"    value="<?= $usrClave ?>">
            <div id="main_container">    
                <?php include('OpeFin00_MenuPrincipal.php'); ?>
                <section class="datos-personales">
                    <h2 class="titleM">Reconstrucción de Saldos</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
<!-- 							_______________________________________________________________________________					-->
								<section class="seccion_caja_despliegue" id="despliegue1">
                                    <div class="caja_captura " id="SelCta">
                                        <label class="lbl_txt" for="selTipCta" >Cuentas</label>
                                        <select id="selTipCta" onchange="prendeApagaRango(this.value);" data-info="Cuentas">
                                            <option value="">Seleccione</option>
                                            <option value="Todas">Todas las Cuentas</option>
                                            <option value="Rango">Rango de Cuentas</option>
                                        </select>
                                    </div>
                                    <div class="caja_captura disabled" id="divRanCta1">
                                        <label class="lbl_txt" for="idCuentabancariaI">Cuenta Inicial</label>
                                    	<select name="idCuentabancariaI" id="idCuentabancariaI" onfocus="this.click();" 
                                    	data-info="Cuenta Bancaria Inicial";></select>
                                    </div>
                                    <div class="caja_captura disabled" id="divRanCta2">
                                        <label class="lbl_txt" for="idCuentabancariaF">Cuenta Final</label>
                                    	<select name="idCuentabancariaF" id="idCuentabancariaF" onfocus="this.click();" 
                                    	data-info="Cuenta Bancaria Final";></select>
                                    </div>
								</section>
<!-- 							_______________________________________________________________________________					-->
								<section class="seccion_caja_despliegue" id="despliegue2">
                                    <div class="caja_captura " id="SelPer">
                                        <label class="lbl_txt" for="selPeriodo">Período</label>
                                        <select id="selPeriodo" onchange="prendeApagaPeriodo(this.value);" data-info="Período">
                                            <option value="">Seleccione</option>
                                            <option value="Todo">Todo el ejercicio</option>
                                            <option value="Periodo">Por Período</option>
                                        </select>
                                    </div>
                                    <div class="caja_captura disabled" id="divPeriodo">
                                        <label class="lbl_txt" for="idFecha">Fecha Inicial(dd/mm/yyyy)</label>
                                        <input type="date" id="idFecha"; data-info="Fecha Inicial"/>
                                    </div>
								</section>
<!-- 							_______________________________________________________________________________					-->
                                <section class="seccion_caja_despliegue" id="despliegue2">
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="ReconstruirSaldos();">
                                            <span>Reconstruir</span>
                                        </a>
                                    </div>
                                </section>
<!-- 							_______________________________________________________________________________					-->
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div id="loader-container" style="display:none;">
                <div id="loader">Calculando Saldos...</div>
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
        <script src="jsF/Reconstruir_.js"></script>
	</body>
</html>