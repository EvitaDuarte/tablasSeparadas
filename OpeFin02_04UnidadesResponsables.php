<!DOCTYPE html>
<html lang="es">
    <head>
    <?php   require_once("OpeFin00_00VarSesion.php"); ?>
        <meta charset="uft-8" />
        <title><?=$v_TituloS?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-------------General Style's--------------->
        <link rel="stylesheet" href="assetsF/css/panel_style.css">
        <link rel="stylesheet" href="assetsF/css/seccion.css">
    </head>
    <body>

        <form name="form1" id="form1" method="post" enctype="multipart/form-data">
            <div id="main_container">    
                <?php include('OpeFin00_MenuPrincipal.php'); // Incluye el menú principal?>
                <section class="datos-personales2">
                    <h2 class="titleM">Unidades Responsables</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <div class="form-field-angosto inline-block-input4" style="width:12% !important;">
                                    <input type="text" name="idUnidad" id="idUnidad"  class="input-text" required maxlength=4
                                    onkeyup="this.title=this.value;">
                                    <label for="idUnidad" class="label">Id Control</label>
                                </div>
                                <div class="form-field-angosto inline-block-input3" style="width:30% !important;">
                                    <input type="text" name="nombreunidad" id="nombreunidad" class="input-text" required maxlength=200
                                    onkeyup="this.title=this.value;" >
                                    <label for="nombreunidad" class="label">Nombre</label>
                                </div>
                                <div class="form-field inline-block-input" style="width:20%!important;" id="divActivo">
                                    <input type="checkbox" id="estatus" name="estatus" checked="checked">
                                    <label for="estatus" class="labelChk">¿ Activo ?</label>
                                </div>
                                <hr>
                                <div class="form-field-button3" style="display: none;">
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="AgregaControl();">
                                            <span>Agregar</span>
                                        </a>
                                    </div>
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="ModificaControl();">
                                            <span>Modificar</span>
                                        </a>
                                    </div>
                                    <div class="form-field-button3" inline-block-input3>
                                        <a class="btn efecto" onclick="EliminaControl();">
                                            <span>Eliminar</span>
                                        </a>
                                    </div>
                                </div>
                                <hr>
                                <div id="paginador" class="pagina"></div>
                                <div class="tabla-con-cuadricula">
                                    <table class="tablex" id="unidadesResponsables">
                                        <caption class="captionTable">Unidades Responsables del Sistema</caption>
                                        <thead>
                                            <tr>
                                                <th>Id Unidad</th>
                                                <th>Nombre</th>
                                                <th>Activo</th>
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
        <script src="jsF/cerrarSesion_.js"></script>
        <script src="jsF/rutinas_.js"></script>
        <script src="jsF/Catalogos_.js"></script>
    </body>
</html>