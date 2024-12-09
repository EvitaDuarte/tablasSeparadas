<!DOCTYPE html>
<html lang="es">
    <head>
        <?php
            require_once("OpeFin00_00VarSesion.php");
        ?>
        <meta charset="uft-8" />
        <title><?=$v_TituloS?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-------------General Style's--------------->
        <link rel="stylesheet" href="assetsF/css/panel_style.css">
        <div id="focus-table"></div>
    </head>
    <body>

        <form name="form1" id="form1" method="post" enctype="multipart/form-data">
            <input type="hidden" id="s_usuario"    name="s_usuario"    value="<?= $usrClave ?>">
            <div id="main_container">    
                <?php include('OpeFin00_MenuPrincipal.php'); ?>
                <section class="datos-personales2">
                    <h2 class="titleM">Configuración</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <div class="form-field inline-block-input" style="width:40% !important;">
                                    <input type="text" name="anioMovimiento" id="anioMovimiento"  class="input-text" required maxlength=4
                                    onkeyup="this.title=this.value;" onblur="soloNumeros(this.value,'Año Mov',this.id)">
                                    <label for="anioMovimiento" class="label">Año Mínimo Captura Movimientos:</label>
                                </div>
                                <div class="form-field inline-block-input" style="width:40% !important;">
                                    <input type="text" name="anioReintegro" id="anioReintegro" class="input-text" required maxlength=4 
                                    onkeyup="this.title=this.value;" onblur="soloNumeros(this.value,'Año Mov',this.id)">
                                    <label for="anioReintegro" class="label">Año Mínimo Captura Reintegros:</label>
                                </div>
                                <div class="form-field inline-block-input" style="width:40% !important;">
                                    <input type="text" name="deptoRecibo" id="deptoRecibo"  class="input-text" required maxlength=100
                                    onkeyup="this.title=this.value;" onblur="sololetras(this.value,'Departamento',this.id)">
                                    <label for="deptoRecibo" class="label">Departamento Recibo</label>
                                </div>
                                <div class="form-field inline-block-input" style="width:40% !important;">
                                    <input type="text" name="firmaRecibo" id="firmaRecibo" class="input-text" required maxlength=100 
                                    onkeyup="this.title=this.value;" onblur="sololetras(this.value,'Firma Recibo',this.id)">
                                    <label for="firmaRecibo" class="label">Firma Recibo</label>
                                </div>
                                <hr>
                                <div class="form-field-button3">
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="ConfiguracionActualizar();">
                                            <span>Actualizar</span>
                                        </a>
                                    </div>
                                </div>
                                <hr>
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
        </form>
        <script src="jsF/cerrarSesion_.js"></script>
        <script src="jsF/rutinas_.js"></script>
        <script src="jsF/funcionalidad_.js"></script>
    </body>
</html>