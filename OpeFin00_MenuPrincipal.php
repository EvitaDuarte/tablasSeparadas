		<link rel="stylesheet" href="cssF/menupt.css">
		<?php
			$v_Color= 'style="color: #ff41ae;"';
		?>

		<header>
			<img class="logoMenu" src="assetsF/img/logo_ine_completo_svg1200.svg" alt="INE Logo">
			<nav>
				<ul>
            		<li><a id="b_inicio"  onClick="Enviar('OpeFin00_00.php');">Inicio</a></li>
            		<li id="ADM" data-esquema="ADM,ING,EGR,CHE,CAP"> 
                    	<a>Admin<b>▼</b></a>
                		<ul>
                    		<li onClick="Enviar('OpeFin01_01Usuarios.php');" data-esquema="ADM">
                    			<a>Usuarios</a></li>
                    		<li onClick="Enviar('OpeFin01_02Esquemas.php');" data-esquema="ADM">
                    			<a>Esquemas</a></li>
                    		<li onClick="Enviar('OpeFin01_03Configuracion.php');" data-esquema="ADM">
                    			<a>Configuración</a></li>
                    		<li onClick="Enviar('OpeFin01_04Accesos.php');" data-esquema="ADM">
                    			<a>Accesos</a></li>
                    		<li onClick="Enviar('OpeFin01_05Regenerar.php');" data-esquema="ADM">
                    			<a>Reconstruir Saldos</a></li>
                    		<li onClick="Enviar('OpeFin01_06Formato.php');" data-esquema="ADM">
                    			<a>Formato Cheques</a></li>
                    		<hr>
                    		<li onClick="Enviar('OpeFin02_01CuentasBancarias.php');" data-esquema="ING,EGR,CHE,CAP">
                    			<a>Cuentas Bancarias </a></li>
                    		<li onClick="Enviar('OpeFin02_02OperacionesBancarias.php');" data-esquema="ING,EGR,CHE,CAP">
                    			<a>Operaciones Bancarias </a></li>
                    		<li onClick="Enviar('OpeFin02_03ControlesBancarios.php');" data-esquema="ING,EGR,CHE,CAP">
                    			<a>Controles Bancarios</a></li>
                    		<li onClick="Enviar('OpeFin02_04UnidadesResponsables.php');" data-esquema="ING,EGR,CHE,CAP">
                    			<a>Unidades Responsables</a></li> 
                		</ul>
            		</li>
            		<li  data-esquema="ING,EGR,CHE,CAP">
                    	<a>Carga<b>▼</b></a>
                        <ul>
                            <li onClick="Enviar('OpeFin03_01Buzon.php');">   
                            	<a>Buzón</a></li>
							<li onClick="Enviar('OpeFin03_02Reintegros.php');">   
                            	<a>Reintegros</a></li>								
                        </ul>
                	</li>
            		<li  data-esquema="ING,EGR,CHE,CAP">
                    	<a>Captura<b>▼</b></a>
                        <ul>
                            <li onClick="Enviar('OpeFin04_01Ingresos.php');" data-esquema="ING,CAP">   
                            	<a>Ingresos</a></li>
                            <li onClick="Enviar('OpeFin04_02Egresos.php');" data-esquema="EGR,CAP">
                            	<a>Egresos</a></li>
                        	<li onClick="Enviar('OpeFin04_03Cheques.php');" data-esquema="CHE,CAP">
                        		<a>Cheques</a></li>
                        </ul>
                	</li>
            		<li  data-esquema="ING,EGR,CHE,CAP">
                    	<a>Conciliación<b>▼</b></a>
                        <ul>
                        	<li onClick="Enviar('OpeFin05_01Conciliacion.php');" data-esquema="ING,EGR,CHE,CAP">
                        		<a>Operación Bancaria</a>
                        	</li>
                        	<li onClick="Enviar('OpeFin05_02MovsBancos.php');" data-esquema="ING,EGR,CHE,CAP">
                        		<a>Bancos</a>
                        	</li>
                        </ul>
                	</li>
            		<li>
                    	<a>Consultas<b>▼</b></a>
                        <ul  id="clases_dropdown">
                            <li onClick="Enviar('OpeFin07_01ConsultasSaldos.php');"><a>Saldos</a></li>
                            <li onClick="Enviar('OpeFin07_02ConsultasMovimientos.php');"><a>Movimientos</a></li>
                            <hr>
                            <li onClick="Enviar('OpeFin08_01EdoPosFinDia.php');"><a>Posición Financiera Diaria</a></li>
                            <li onClick="Enviar('OpeFin08_02EdoPosMensual.php');"><a>Posición Financiera Mensual</a></li>
                            <li onClick="Enviar('OpeFin08_03Consolidado.php');"><a>Consolidado General</a></li>
                            <hr>
                            <li onClick="Enviar('OpeFin08_04ImpCheques.php');"><a>Impresión Cheques</a></li>
                            <hr>
                            <li onClick="Enviar('OpeFin08_05Reportes.php');"><a>Reportes</a></li>
                        </ul>
                	</li>
 					<li>
                        <a id="b_user" data-after="Mi cuenta" style="color: #ff41ae; text-transform: uppercase;">
                            <?= substr($usrNombreC,0,20) ?>
                            <ion-icon name="arrow-dropdown" size="small" class="arrow-dropdown" id="AD-Clases"></ion-icon>
                            <img src="assetsF/img/usuario.png" style="position: absolute; top: 8px; right: -30px; "//>
                        </a>
                        <ul class="sub-menu-dr">
                            <li id="li-drop"><a class="b_csesion" onclick="#"><?= $usrEsquema ?></a></li>
                            <li id="li-drop"><a class="b_csesion" onClick="Enviar('OpeFin00_Salir.php');">Cerrar sesión</a></li>
                        </ul>
                    </li>
                </ul>
			</nav>
			<div class="menuToggle"></div>
		</header>
<!-- Menu -->

		<div id="data_content"></div>

<!-- Menu -->
		<div class="side-bar">
    		<div class="menu-list" id="close_side" style="-webkit-tap-highlight-color: transparent;">
        		<ion-icon name="close-circle-outline"></ion-icon>
    		</div>
    		<img class="go-home" id="go-home" src="assetsF/img/person-at-home.png"/>
		</div>
		<!-- Menu -->

		<!-- Marca de agua SC -->
		<section class="logo_SC" id="logo_SC">
		    <a id="b_sc_logo">
		        <h1>
		            <img alt="Sistemas complementarios Logo" class="logo_sc_bn">
		        </h1>
		    </a>
		</section>
		<!-- Marca de agua CTIA -->
		<section class="logo_CTIA" id="logo_CTIA">
		    <a id="b_ctia_logo">
		        <h1>
		            <img alt="CTIA Logo" class="logo_ctia_bn">
		        </h1>
		    </a>
		</section>
		<input type="hidden" id="nomEsquema" value="<?= $usrEsquema ?>">
		<script src="jsF/menu_principal_.js"></script>
		
		<script type="module" src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons/ionicons.esm.js"></script> <!-- Iconos de IONICONS-->
		<script nomodule="" src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons/ionicons.js"></script><!--Iconos de IONICONS--> 
		
		<script type="text/javascript">
			// Oculta o Despliega los submenus de cada Opción principal, de acuerdo al ROL
			vEsquema = document.getElementById("nomEsquema").value.toUpperCase().substring(0,3);
			//console.log(`Esquema=${vEsquema}`);
			if (vEsquema!="ADM"){ // El administrador puede ver todo
			    document.querySelectorAll('li').forEach(function(li) {
			        var esquemas = li.getAttribute('data-esquema');
			        if (esquemas!=null){// Algunos li no tienen data-esquema
				        //console.log(`Esquema=${esquemas}`);
				        if (esquemas.includes(vEsquema)) {
				            li.style.display = 'block';
				        } else {
				            li.style.display = 'none';
				        }
				    }
			    });
			}	
		</script>