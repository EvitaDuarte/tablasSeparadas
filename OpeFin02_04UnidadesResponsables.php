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
        <!-- Datatable  -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
        <script src="assetsF/js/jquery-3.7.1.min.js"></script>
        <script src="assetsF/js/jquery.dataTables.min.js"></script>
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
                                <section class="seccion_caja_despliegue" id="secCap">
                                    <div class="caja_captura">
                                        <label for="idUnidad" class="lbl_txt">Id Control</label>
                                        <input type="text" name="idUnidad" id="idUnidad"  required maxlength=4
                                        onkeyup="this.title=this.value;">
                                    </div>
                                    <div class="caja_captura3">
                                        <label for="nombreunidad" class="lbl_txt">Nombre</label>
                                        <input type="text" name="nombreunidad" id="nombreunidad" required maxlength=200
                                        onkeyup="this.title=this.value;" >
                                    </div>
                                    <div class="caja_captura" id="divActivo">
                                        <label for="estatus" class="lbl_txt">¿ Activo ?</label>
                                        <input type="checkbox" id="estatus" name="estatus" checked="checked">
                                    </div>
                                </section>
                                <section class="seccion_caja" id="botones" style="display: none;">
                                    <div class="form-field-button_" id="grpBotones">
                                        <a class="btn efecto" onclick="AgregaControl();">
                                            <span>Agregar</span>
                                        </a>
                                        <a class="btn efecto" onclick="ModificaControl();">
                                            <span>Modificar</span>
                                        </a>
                                        <a class="btn efecto" onclick="EliminaControl();">
                                            <span>Eliminar</span>
                                        </a>
                                    </div>
                                </div>
                                <hr>
                                <div id="paginador" class="pagina" style="display: none;"></div>
                                <div class="tabla-con-cuadricula">
                                    <table class="tablex" id="unidadesResponsables">
                                        <caption class="captionTable">Unidades Responsables del Sistema</caption>
                                        <thead>
                                            <tr>
                                                <th>Id Unidad</th>
                                                <th>Nombre</th>
                                                <th>Activo</th>
                                                <th>Cta. Int</th>
                                                <th>Capturó</th>
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

        <script>

        </script>

    </body>
</html>