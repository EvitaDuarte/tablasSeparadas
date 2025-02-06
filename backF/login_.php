<?php
//
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
//
	//echo "Hola";
	//ini_set('session.use_cookies', 1);
	//ini_set('session.use_only_cookies', 1);	
	//ini_set('session.cookie_domain', '.unadmpt.es');
	session_start();
	//echo "Hola1";
	require_once("rutinas_.php");
	//echo "hola2";
// _______________ Funcion principal ___________________________
	$vOpc 		= "validaLdap";
	$validador 	= null;
	switch ($vOpc) {
		case "validaLdap":
			$validador = array('success' => false , 'mensaje' => array()  ,  'resultados' => array(),  'parametros' => array() , 'paso' => array() ); 
			$validador["parametros"] =  array('Usuario'=>$_POST["user_login"] , 'Contra' =>$_POST["password_login"]);
			//var_dump($validador);
			validaCredenciales($validador);
			if ($validador["success"]==true){
				$_SESSION['OpeFinError'] ="";
				header_remove('x-powered-by');
				header("location:../OpeFin00_00.php");exit;
				return;
			}else{
				$_SESSION['OpeFinError'] ="Credenciales Incorrectas o Inactivo";
				header_remove('x-powered-by');
				header("location:../OpeFin00_home.php");exit;
				return;
			}
		break;
	}
	header_remove('x-powered-by');
	header('Content-type: application/json; charset=utf-8');
	echo json_encode($validador);
return;
// __________________________________________
// __________________________________________
// __________________________________________
// __________________________________________
// __________________________________________
// __________________________________________
// __________________________________________
function validaCredenciales(&$validador){
	$vUsu		= pg_escape_string($validador["parametros"]["Usuario"]);
	$v_Pass     = json_decode(json_encode(( pg_escape_string($validador["parametros"]["Contra"]) )));

	if(validaLdap(pg_escape_string($vUsu), $v_Pass) == '9'){
		
		$validador["mensaje"] = "Credenciales Validas";
		if(getPermisos(pg_escape_string($vUsu))){ // Busca en la tabla de usuarios
			$sql =  "select a.descripcion as salida from esquemas a, usuarios b " .
					"where b.idusuario='$vUsu' and a.idesquema = b.idesquema";
			$rol = getcampo($sql); $alias = obtenAliasUsuario($vUsu);
			if ($rol!=""){
				$v_Datos  			  	= getDatos(pg_escape_string($vUsu), $v_Pass);// regresa cadena de datos separada por |
				$v_Datos 			  	= explode("|", $v_Datos);					 // Se convierte en arreglo
				$salida					= $v_Datos[0];
				if ( $salida=="1"){
					$validador["paso"]			  = $rol;
					$validador["resultados"]	  = $v_Datos;
					$validador["success"] 		  = true;
					// Genera variables de Sesión
					$_SESSION['OpeFinClave']      = $v_Datos[1];
					$_SESSION['OpeFinApellidos']  = $v_Datos[3];
					$_SESSION['OpeFinNombres']    = $v_Datos[4];
					$_SESSION['OpeFinCurp']       = $v_Datos[6];
					$_SESSION['OpeFinNC']		  = $v_Datos[10]; // Nombre completo(Empezando por apellidos)
					$_SESSION["OpeFinPuesto"]     = $v_Datos[11];
					$_SESSION["OpeFinEsquema"]	  = $rol;
					$_SESSION['OpeFinTituloS']    = "Sistema de Operación Bancaria Web";
					$_SESSION["OpeFinError"]	  = "";
					$_SESSION['tiempo'] 		  = time();
					$_SESSION['alias']			  = $alias;
				}else{
					$validador["success"] = false;
					$validador["mensaje"] = "Usuario y contraseña inválidos";
				}
			}else{
				$validador["mensaje"] = "EL usuario no tiene asignado un rol";
			}
		}else{
			$validador["mensaje"] = "El usuario no esta en el sistema o se encuentra inactivo";
		}

	}else{
		$validador["success"] = false;
		$validador["mensaje"] = "Credenciales InVálidas";
	}
}
// __________________________________________
function validaLdap($username, $password){
	//return "9";
	$key = '123456';
	$decrypted = decrypt($password, $key);
	error_reporting(E_ERROR);
	$salida = "0";
	if($connect = @ldap_connect('ldap://autenticacion.ife.org.mx')){
			ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($connect, LDAP_OPT_REFERRALS,0);
			if(($bind = @ldap_bind($connect)) == false){
			  $salida = "2";
			  return $salida;
			}
			if (($res_id = ldap_search( $connect,"ou=people,dc=ife.org.mx","uid=$username")) == false) {
			  //"failure: search in LDAP-tree failed<br>";
			  return "3";
			}
			if (ldap_count_entries($connect, $res_id) != 1) {
			  //"failure: username $username found more than once<br>\n";
			  return "4";;
			}
			if (( $entry_id = ldap_first_entry($connect, $res_id))== false) {
			  //"failur: entry of searchresult couln't be fetched<br>\n";
			  return "5";
			}
			if (( $user_dn = ldap_get_dn($connect, $entry_id)) == false) {
			  //"failure: user-dn coulnd't be fetched<br>\n";
			  return "6";
			}
			/* Authentifizierung des User */
			//if (($link_id = @ldap_bind($connect, $user_dn, $decrypted)) == false) {
			if (($link_id = @ldap_bind($connect, $user_dn, $password)) == false) {
				//"failue: username, password didn't match: $user_dn<br>\n";
				return "7";
			}
			return "9";
			@ldap_close($connect);
	}else{
		$salida = "1"; //Si no hay conexion con el servidor
	}
	@ldap_close($connect);
	return $salida;
}
// __________________________________________
function getDatos($username, $password){
	$salida = "";
	/*
	switch($username){
		case 'miguel.bolanos': // Administrador
			if ($password=='Evit@100268'){
				$salida = "1|miguel.bolanos|miguel.bolanos@ine.mx|BOLAÑOS GUILLEN|MIGUEL ANGEL|DIRECCION EJECUTIVA DE ADMINISTRACION|******************|" .
				  	  	  "0|0|OF16|BOLAÑOS GUILLEN MIGUEL ANGEL|JDCS";
	  	  	}
			break;
		case 'marco.melo':	// Ingresos
			if ($password=='M1m2$9145'){
				$salida = "1|marco.melo|marco.melo@ine.mx|MELO MENDEZ|MARCO ANTONIO|DIRECCION EJECUTIVA DE ADMINISTRACION|******************|" .
				  	      "0|0|OF16|MELO MENDEZ MARCO ANTONIO|JDIS";
			}
			break;
		case 'rafael.altamirano': // Egresos
			if ($password=='R1f1Alt165#'){
				$salida = "1|rafael.altamirano|rafael.altamirano@ine.mx|ALTAMIRANO DELGADO|RAFAEL|DIRECCION EJECUTIVA DE ADMINISTRACION|******************|" .
				  	      "0|0|OF16|ALTAMIRANO DELGADO RAFAEL|JDAU";
			}
			break;
		case 'gerardo.macias': // Cheques
			if ($password=='G4raM@ci1s'){
				$salida = "1|gerardo.macias|gerardo.macias@ine.mx|MACIAS HOYOS|GERARDO AXEL|DIRECCION EJECUTIVA DE ADMINISTRACION|******************|" .
				  	      "0|0|OF16|MACIAS HOYOS GERARDO AXEL|TDS";
			}
			break;
		case 'rafael.carrasco': // Consultas
			if ($password=='D4nR1m4n__'){
				$salida = "1|rafael.carrasco|rafal.carrasco@ine.mx|CARRASCO LICEA|RAFAEL|DIRECCION EJECUTIVA DE ADMINISTRACION|******************|" .
				  	      "0|0|OF16|CARRASCO LICEA RAFAEL|SSI";
			}
			break;
		default:
			$salida = "0";
	}
	return $salida; */
	// EL Ldap solo funciona en la intranet del INE
	if($connect = @ldap_connect('ldap://autenticacion.ife.org.mx')){
		if(ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3)){}
		if(($bind = @ldap_bind($connect)) == false){
		  return "0";
		}
	
		$res_id   = ldap_search( $connect, "ou=people,dc=ife.org.mx", "uid=$username");
		$entry_id = ldap_first_entry($connect, $res_id);
		
		if($entry_id){
			$salida = "1|";
			$valores  = ldap_get_values($connect, $entry_id, "uid");
				$salida = $salida . $valores[0] ."|";
			$valores  = ldap_get_values($connect, $entry_id, "mail");
				$salida = $salida . $valores[0] ."|";
			$valores  = ldap_get_values($connect, $entry_id, "sn");
				$salida = $salida . $valores[0] ."|";
			$valores  = ldap_get_values($connect, $entry_id, "givenname");
				$salida = $salida . $valores[0] ."|";
			$valores  = ldap_get_values($connect, $entry_id, "ou");
				$salida = $salida . $valores[0] ."|";
			$valores  = ldap_get_values($connect, $entry_id, "curp");
				$salida = $salida . $valores[0] ."|";
			$valores  = ldap_get_values($connect, $entry_id, "idEstado");
				$salida = $salida . $valores[0] ."|";
			$valores  = ldap_get_values($connect, $entry_id, "idDistrito");
				$salida = $salida . $valores[0] ."|";
			$valores  = getURAdscripcion(
										ldap_get_values($connect, $entry_id, "idEstado")[0], 
										ldap_get_values($connect, $entry_id, "idDistrito")[0],
										ldap_get_values($connect, $entry_id, "ou")[0]);
				$salida = $salida . $valores ."|";
			$valores  = ldap_get_values($connect, $entry_id, "cn");
				$salida = $salida . $valores[0] . "|";
			$valores  = ldap_get_values($connect, $entry_id, "personalTitle");
				$salida = $salida . $valores[0];

			return $salida;
		}else{
			return "0";
		}
    	@ldap_close($connect);

	}else{
		$salida = "0"; //Si no hay conexion con el servidor
	}
	
	@ldap_close($connect);
	return $salida;
}
// __________________________________________
// __________________________________________
function decrypt($jsonStr, $passphrase)
{
	$json = json_decode($jsonStr, true);
	$salt = hex2bin($json["s"]);
	$iv = hex2bin($json["iv"]);
	$ct = base64_decode($json["ct"]);
	$concatedPassphrase = $passphrase . $salt;
	$md5 = [];
	$md5[0] = md5($concatedPassphrase, true);
	$result = $md5[0];
	for ($i = 1; $i < 3; $i++) {
		$md5[$i] = md5($md5[$i - 1] . $concatedPassphrase, true);
		$result .= $md5[$i];
	}
	$key = substr($result, 0, 32);
	$data = openssl_decrypt($ct, 'aes-256-cbc', $key, true, $iv);
	return json_decode($data, true);
}
// ___________________________________________
function obtenAliasUsuario($vUsu){
	$sql   = "select alias as salida from public.usuarios where idusuario='$vUsu'";
	$alias = getcampo($sql);
	if ($alias==""){
		$alias = $_SERVER['REMOTE_ADDR'];
		$alias = str_replace(".", "", $alias);
	}
	return $alias;
}
// ___________________________________________
?>