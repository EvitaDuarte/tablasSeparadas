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
    </head>
    <body>

        <form name="frmCtaBan" id="frmCtaBan" method="post" enctype="multipart/form-data">
            <div id="main_container">    
                <?php include('OpeFin00_MenuPrincipal.php'); // Incluye el menú principal?>
                <section class="datos-personales2">
                    <h2 class="titleM">Cuentas Bancarias</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <section class="seccion_caja_despliegue" id="secCta">
                                    <div class="caja_captura">
                                        <label for="idCuentaBancaria" class="lbl_txt">Cuenta Bancaria</label>
                                        <input type="text" name="idCuentaBancaria" id="idCuentaBancaria" required maxlength=20
                                        onkeyup="this.title=this.value;" onblur="soloNumerosGuion(this.value,'Cuenta Bancaria',this.id)">
                                        
                                    </div>
                                    <div class="caja_captura3">
                                        <label for="nombre" class="lbl_txt">Nombre</label>
                                        <input type="text" name="nombre" id="nombre" required maxlength=50
                                        onkeyup="this.title=this.value;" onblur="soloCuenta('Nombre',this.id)">
                                        
                                    </div>
                                    <div class="caja_captura">
                                        <label for="siglas" class="lbl_txt">Siglas</label>
                                        <input type="text" name="siglas" id="siglas"  required maxlength=10
                                        onkeyup="this.title=this.value;" onblur="soloSiglas('Siglas',this.id)">
                                    </div>
                                    <div class="caja_captura">
                                        <label class="lbl_txt">Banco</label>
                                         <select name="idBanco" id="idBanco" onfocus="this.click();" data-info="Banco" ></select>
                                    </div>
                                    <div class="caja_captura" id="divActivo">
                                        <label for="estatus" class="lbl_txt">¿ Activa ?</label>
                                        <input type="checkbox" id="estatus" name="estatus" checked="checked" style="visibility: visible; display: inline-block;">

                                    </div> 
                                    <div class="caja_captura" id="divActivo">
                                        <label for="concilia" class="lbl_txt">¿ Concilia ?</label>
                                        <input type="checkbox" id="concilia" name="concilia" style="visibility: visible; display: inline-block;">
                                    </div>
                                </section>
                                <section class="seccion_caja_despliegue" id="despliegue">


                                </section>
                                <div class="form-field-button3" id="divBotones">
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="AgregaCuentaBancaria();">
                                            <span>Agregar</span>
                                        </a>
                                    </div>
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="ModificaCuentaBancaria();">
                                            <span>Modificar</span>
                                        </a>
                                    </div>
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="EliminaCuentaBancaria();">
                                            <span>Eliminar</span>
                                        </a>
                                    </div>
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="limpiaPantalla('frmCtaBan');">
                                            <span>Nuevo</span>
                                        </a>
                                    </div>
                                </div>
                                <hr>
                                <div class="tabla-con-cuadricula">
                                    <table class="tablex" id="cuentasBancarias">
                                        <caption class="captionTable">Cuentas Bancarias del Sistema</caption>
                                        <thead>
                                            <tr>
                                                <th>Cuenta</th>
                                                <th>Nombre</th>
                                                <th>Siglas</th>
                                                <th>Activa?</th>
                                                <th>Concilia></th>
                                                <th>Banco</th>
                                                <th>Capturó</th>
                                                <th>Fecha Alta</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cuerpo">
                                        </tbody>
                                    </table>
                                </div>
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
<!--        ______________________________________________________________________               --> 
        </form>
        <script src="jsF/cerrarSesion_.js"></script>
        <script src="jsF/rutinas_.js"></script>
        <script src="jsF/Catalogos_.js"></script>
    </body>
</html>