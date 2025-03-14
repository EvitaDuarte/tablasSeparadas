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
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>

    </head>
    <body>

        <form name="frmOpeFin" id="frmOpeFin" method="post" enctype="multipart/form-data">
            <div id="main_container">    
                <?php include('OpeFin00_MenuPrincipal.php'); // Incluye el menú principal?>
                <section class="datos-personales2">
                    <h2 class="titleM">Operaciones Bancarias</h2>
                    <div class="container-data">
                        <div class="data-form">
                            <div class="wrapper">
                                <section class="seccion_caja_despliegue" id="secCap">
                                    <div class="caja_captura">
                                        <label for="idOperacion" class="lbl_txt">Id Operación</label>
                                        <input type="text" name="idOperacion" id="idOperacion"  required maxlength=4 
                                        onkeyup="this.title=this.value;" onblur="exclusivoLetras('Id',this.id)">
                                    </div>
                                    <div class="caja_captura3">
                                        <label for="nombre" class="lbl_txt">Nombre</label>
                                        <input type="text" name="nombre" id="nombre" required maxlength=50
                                        onkeyup="this.title=this.value;" onblur="sololetras(this.value,'Nombre',this.id)">
                                    </div>
                                    <div class="caja_captura">
                                        <label for="tipo" class="lbl_txt">Tipo</label>
                                        <select name="tipo" id="tipo" onclick="OperacionSaldoCancelacion();" onblur="OperacionSaldoCancelacion();">
                                            <option value="" >Seleccione</option>
                                            <option value="I">INGRESOS</option>
                                            <option value="E">EGRESOS</option>
                                            <option value="C">CHEQUES</option>
                                        </select>
                                    </div>
                                    <div class="caja_captura">
                                        <label for="operador" class="lbl_txt">Saldo</label>
                                        <select name="operador" id="operador">
                                            <option value="" >Seleccione</option>
                                            <option value="+">SUMA AL SALDO</option>
                                            <option value="-">RESTA AL SALDO</option>
                                        </select>
                                    </div>
                                    <div class="caja_captura">
                                        <label for="idOperCan" class="lbl_txt">Cancelación</label>
                                        <select name="idOperCan" id="idOperCan">
                                            <option value="" >Seleccione</option>
                                            <option value="CIN">CANCELACIÓN DE INGRESOS</option>
                                            <option value="CEG">CANCELACIÓN DE EGRESOS</option>
                                            <option value="CAN">CANCELACIÓN DE CHEQUES</option>
                                        </select>
                                    </div>
                                    <div class="caja_captura" id="divActivo">
                                        <label for="visualizar" class="lbl_txt">¿ Visualizar ?</label>
                                        <input type="checkbox" id="visualizar" name="visualizar" checked="checked">
                                    </div>
                                </section>
                                <section class="seccion_caja" id="botones">
                                    <div class="form-field-button_" id="grpBotones">
                                        <a class="btn_1 efecto" onclick="AgregaOperacion();">
                                            <span>Agregar</span>
                                        </a>
                                        <a class="btn_1 efecto" onclick="ModificaOperacion();">
                                            <span>Modificar</span>
                                        </a>
                                        <a class="btn_1 efecto" onclick="EliminaOperacion();">
                                            <span>Eliminar</span>
                                        </a>
                                        <a class="btn_1 efecto" onclick="limpiaPantalla('frmOpeFin');">
                                            <span>Nuevo</span>
                                        </a>
                                    </div>
                                </section>
                                <div id="paginador" class="pagina"></div>
                                <div class="tabla-con-cuadricula">
                                    <table class="tablex" id="operacionesBancarias">
                                        <caption class="captionTable">Operaciones Bancarias del Sistema</caption>
                                        <thead>
                                            <tr>
                                                <th>Clave</th>
                                                <th>Nombre</th>
                                                <th>Tipo</th>
                                                <th>Operación?</th>
                                                <th>Cancelación</th>
                                                <th>Visualizar</th>
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
        <script>
            var TablaPadre = $('#operacionesBancarias').DataTable({
                paging          : true, // Desactiva la paginación
                searching       : false, // Desactiva la barra de búsqueda
                pageLength      : 6,
                lengthChange    : false,
                fixedHeader     : false,
                info            : false,
                ordering        : false,
                orderCellsTop   : true,
                initComplete    : function(settings, json) {
                  var api = this.api();
                  $(api.table().header()).find('th').css('border', '1px solid black');
                },
                columnDefs: [
                    {
                        targets: 1,  // Se refiere a la segunda columna (las columnas están indexadas desde 0)
                        width: '400px'  // Ancho personalizado para la segunda columna
                    },
                    {
                        targets :   3,
                        width   :   '100px'
                    },
                    {
                        targets :   4,
                        width   :   '100px'
                    }
                ]
            });
        </script>
    </body>
</html>