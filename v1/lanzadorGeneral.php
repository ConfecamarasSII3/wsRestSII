<?php

session_start();

//
require_once('configuracion/common.php');

//
if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
    $claveEncriptacion = 'c0nf3c@m@r@s2017';
} else {
    $claveEncriptacion = CLAVE_ENCRIPTACION;
}

//
$jsonentrada = '';

if (isset($_GET)) {
    $jsonentrada = str_replace($claveEncriptacion, "", base64_decode(str_replace(" ", "+", $_GET["parametros"])));
} else {
    if (isset($_POST)) {
        $jsonentrada = str_replace($claveEncriptacion, "", base64_decode(str_replace(" ", "+", $_POST["parametros"])));
    }
}

//
// fwrite ($f, date ("H:i:s") . ' JSON-PARAMETROS: ' . $jsonentrada . "\r\n\r\n");

//
if (trim($jsonentrada) == '') {
    echo "Error 1 : No se recibieron datos de entrada";
    exit();
}

//
$data = json_decode($jsonentrada, true);
$arr = array();
if (!empty($data)) {
    foreach ($data as $key => $valor) {
        /*
        if (is_object($valor)) {
            fwrite ($f, date ("H:i:s") . ' PARAMETROS-VARIABLES : ' . $key . ' => Object' . "\r\n\r\n");
        } else {
            fwrite ($f, date ("H:i:s") . ' PARAMETROS-VARIABLES : ' . $key . ' => '. $valor . "\r\n\r\n");
        }
        */
        if ($key != 'parametros') {
            // echo $key . ' => '  . $valor . '<br>';
            $arr[$key] = $valor;
        } else {
            if (is_array($valor) && !empty($valor)) {
                foreach ($valor as $key1 => $valor1) {
                    // echo $key . ' => '  . $key1 . ' => ' . $valor1 . '<br>';
                    $arr[$key][$key1] = $valor1;
                    // $arr[$key1] = $valor1;
                }
                // unset($data1);
            }
        }
    }
    unset($data);
}

//
if (!isset($arr["codigoempresa"]) || trim($arr["codigoempresa"]) == '') {
    echo "Error 2 : No se indico el codigo de la empresa en la data de entrada";
    exit();
}

if (!file_exists('configuracion/common' . $arr["codigoempresa"] . '.php')) {
    echo "Error 3 : Camara no configurada en este ambiente";
    exit();
}

//
unset($_SESSION["generales"]);
$_SESSION["generales"]["codigoempresa"] = $arr["codigoempresa"];
require_once('configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');

//
if (TIPO_AMBIENTE == 'PRUEBAS') {
    if (date("Ymd") != $arr["fechainvocacion"]) {
        // if (date("Ymd") != $arr["fechainvocacion"] || date("Hi") != substr($arr["horainvocacion"],0,4)) {
        // if (date("Ymd") != $arr["fechainvocacion"] || date("His") != $arr["horainvocacion"]) {
        echo "Error 4 : Invocacion fuera de tiempo (" . date("Hi") . ") - (" . substr($arr["horainvocacion"], 0, 4) . ")";
        exit();
    }
}

//
if (TIPO_AMBIENTE == 'PRODUCCION') {
    if (date("Ymd") != $arr["fechainvocacion"]) {
        // if (date("Ymd") != $arr["fechainvocacion"] || date("Hi") != substr($arr["horainvocacion"],0,4)) {
        echo "Error 4 : Invocacion fuera de tiempo (" . date("Hi") . ") - (" . substr($arr["horainvocacion"], 0, 4) . ")";
        exit();
    }
}

//
$_SESSION["generales"]["pathabsoluto"] = getcwd();
$_SESSION["generales"]["pathabsoluto"] = str_replace("\\", "/", $_SESSION["generales"]["pathabsoluto"]);
$_SESSION["generales"]["zonahoraria"] = "America/Bogota";
$_SESSION["generales"]["idioma"] = "es";
$_SESSION["generales"]["dispositivo"] = '';
$_SESSION["generales"]["codigousuario"] = $arr["idusuario"];
$_SESSION["generales"]["validado"] = 'NO';
$_SESSION["generales"]["escajero"] = 'NO';
$_SESSION["generales"]["tipousuariocontrol"] = '';
$_SESSION["generales"]["identificacionusuariocontrol"] = '';
$_SESSION["generales"]["celularusuariocontrol"] = '';
$_SESSION["generales"]["emailusuariocontrol"] = $arr["emailcontrol"];
$_SESSION["generales"]["nombreusuariocontrol"] = '';
$_SESSION["generales"]["controlusuarioretornara"] = '';
$_SESSION["generales"]["controlusuariorutina"] = '';
$_SESSION["generales"]["claveusuariocontrol"] = '';
$_SESSION["generales"]["sedeusuario"] = '99';
$_SESSION["generales"]["tipomenu"] = 'ICONOS3';
$_SESSION["generales"]["tipodispositivo"] = retornarDispositivoLanzador();
date_default_timezone_set($_SESSION["generales"]["zonahoraria"]);


$path = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $arr["codigoempresa"] . '-' . $_SESSION["generales"]["codigousuario"] . '-res.xml';
if (file_exists($path)) {
    unlink($path);
}


//
require_once('librerias/funciones/persistencia.php');

//
// Si el usuario es un usuario interno
if ($arr["idusuario"] != 'USUPUBXX' && $arr["idusuario"] != 'USUFIRMANTE') {

    //
    if (strtoupper(substr($arr["idusuario"], 0, 6)) != 'ADMGEN') {
        $usu = retornarRegistro('usuarios', "idusuario='" . $arr["idusuario"] . "'");
        if ($usu === false || empty($usu)) {
            echo "Error 5 : Usuario inexistente";
            exit();
        }
        //
        $_SESSION["generales"]["validado"] = 'SI';
        $_SESSION["generales"]["escajero"] = $usu["escajero"];
        $_SESSION["generales"]["tipousuariocontrol"] = 'usuariointerno';
        $_SESSION["generales"]["tipousuario"] = $usu["idtipousuario"];
        $_SESSION["generales"]["idtipousuario"] = $usu["idtipousuario"];
        $_SESSION["generales"]["identificacionusuariocontrol"] = $usu["identificacion"];
        $_SESSION["generales"]["celularusuariocontrol"] = $usu["celular"];
        $_SESSION["generales"]["emailusuariocontrol"] = $usu["email"];
        $_SESSION["generales"]["emailusuario"] = $usu["email"];
        $_SESSION["generales"]["celular"] = $usu["celular"];
        $_SESSION["generales"]["nombreusuario"] = strtoupper($usu["nombreusuario"]);
        $_SESSION["generales"]["nombreusuariocontrol"] = strtoupper($usu["nombreusuario"]);
        $_SESSION["generales"]["nombre1usuariocontrol"] = '';
        $_SESSION["generales"]["nombre2usuariocontrol"] = '';
        $_SESSION["generales"]["apellido1usuariocontrol"] = '';
        $_SESSION["generales"]["apellido2usuariocontrol"] = '';
        $_SESSION["generales"]["direccionusuariocontrol"] = '';
        $_SESSION["generales"]["municipiousuariocontrol"] = '';

        $_SESSION["generales"]["loginemailusuario"] = $usu["email"];
        $_SESSION["generales"]["passwordemailusuario"] = '';
        $_SESSION["generales"]["perfildocumentacion"] = '';
        $_SESSION["generales"]["controlapresupuesto"] = '';
        $_SESSION["generales"]["idtipoidentificacionusuario"] = '';
        $_SESSION["generales"]["identificacionusuario"] = '';

        $_SESSION["generales"]["controlusuarioretornara"] = '';
        $_SESSION["generales"]["controlusuariorutina"] = '';
        $_SESSION["generales"]["claveusuariocontrol"] = $usu["password"];
        $_SESSION["generales"]["sedeusuario"] = $usu["idsede"];
        $_SESSION["generales"]["idcodigosirepcaja"] = $usu["idcodigosirepcaja"];
        $_SESSION["generales"]["idcodigosirepdigitacion"] = $usu["idcodigosirepdigitacion"];
        $_SESSION["generales"]["idcodigosirepregistro"] = $usu["idcodigosirepregistro"];
        $_SESSION["generales"]["fechaactivacion"] = $usu["fechaactivacion"];
        $_SESSION["generales"]["fechainactivacion"] = $usu["fechainactivacion"];
        $_SESSION["generales"]["gastoadministrativo"] = $usu["gastoadministrativo"];

        $_SESSION["generales"]["navegador"] = '';
        $_SESSION["generales"]["idtipousuariodesarrollo"] = '';
        $_SESSION["generales"]["tipousuarioexterno"] = '';
        $_SESSION["generales"]["idtipousuariofinanciero"] = '';
        $_SESSION["generales"]["gastoadministrativo"] = $usu["gastoadministrativo"];
        $_SESSION["generales"]["esdispensador"] = $usu["esdispensador"];
        $_SESSION["generales"]["escensador"] = $usu["escensador"];
        $_SESSION["generales"]["esbrigadista"] = $usu["esbrigadista"];
        $_SESSION["generales"]["puedecerrarcaja"] = $usu["puedecerrarcaja"];
        $_SESSION["generales"]["visualizatotales"] = $usu["visualizatotales"];

        $_SESSION["generales"]["esrue"] = $usu["esrue"];
        $_SESSION["generales"]["esreversion"] = $usu["esreversion"];
        $_SESSION["generales"]["eswww"] = $usu["eswww"];
        $_SESSION["generales"]["essa"] = $usu["essa"];
        $_SESSION["generales"]["abogadocoordinador"] = $usu["abogadocoordinador"];
        $_SESSION["generales"]["loginemailusuario"] = $usu["email"];
        $_SESSION["generales"]["passwordemailusuario"] = '';
        $_SESSION["generales"]["perfildocumentacion"] = '';
        $_SESSION["generales"]["controlapresupuesto"] = '';
        $_SESSION["generales"]["idtipoidentificacionusuario"] = '';
        $_SESSION["generales"]["identificacionusuario"] = '';
        $_SESSION["generales"]["nitempresausuario"] = '';
        $_SESSION["generales"]["nombreempresausuario"] = '';
        $_SESSION["generales"]["direccionusuario"] = '';
        $_SESSION["generales"]["idmuniciopiousuario"] = '';
        $_SESSION["generales"]["telefonousuario"] = '';

        $_SESSION["generales"]["movilusuario"] = '';
        $_SESSION["generales"]["operadorsirepusuario"] = '';
        $_SESSION["generales"]["ccosusuario"] = '';
        $_SESSION["generales"]["cargousuario"] = '';
        $_SESSION["generales"]["nombreempresa"] = '';

        $_SESSION["generales"]["controlverificacion"] = '';
        $_SESSION["generales"]["fechacambioclave"] = '';
        $_SESSION["generales"]["mesavotacion"] = $usu["mesavotacion"];
    } else {
        $_SESSION["generales"]["validado"] = 'SI';
        $_SESSION["generales"]["escajero"] = 'SI';
        $_SESSION["generales"]["tipousuariocontrol"] = 'usuariointerno';
        $_SESSION["generales"]["tipousuario"] = '01';
        $_SESSION["generales"]["identificacionusuariocontrol"] = $arr["identificacioncontrol"];
        $_SESSION["generales"]["celularusuariocontrol"] = '';
        $_SESSION["generales"]["emailusuariocontrol"] = $arr["emailcontrol"];
        $_SESSION["generales"]["nombreusuariocontrol"] = $arr["idusuario"];
        $_SESSION["generales"]["controlusuarioretornara"] = '';
        $_SESSION["generales"]["controlusuariorutina"] = '';
        $_SESSION["generales"]["claveusuariocontrol"] = '';
        $_SESSION["generales"]["sedeusuario"] = '01';
        $_SESSION["generales"]["idcodigosirepcaja"] = '';
        $_SESSION["generales"]["idcodigosirepdigitacion"] = '';
        $_SESSION["generales"]["idcodigosirepregistro"] = '';
        $_SESSION["generales"]["fechaactivacion"] = '';
        $_SESSION["generales"]["fechainactivacion"] = '';
    }
}

// Si el usuario es un usuario externo
if ($arr["idusuario"] == 'USUPUBXX') {

    //
    /*
    $usu = retornarRegistro('usuarios_verificados', "email='" . $arr["emailcontrol"] . "' and identificacion='" . $arr["identificacioncontrol"] . "' and celular='" . $arr["celularcontrol"] . "'");
    if ($usu === false || empty($usu)) {
        $usu = retornarRegistro('usuarios_registrados', "email='" . $arr["emailcontrol"] . "' and identificacion='" . $arr["identificacioncontrol"] . "' and celular='" . $arr["celularcontrol"] . "'");
        if ($usu === false || empty($usu)) {
            echo "Error 6 : No encontrado ni como usuario registrado ni como usuario verificado";
            exit();
        } else {
            $_SESSION["generales"]["tipousuariocontrol"] = 'usuarioregistrado';
            if ($usu["estado"] != 'AP') {
                echo "Error 7 : Usuario registrado no se encuentra activado";
                exit();
            }
        }
    } else {
        $_SESSION["generales"]["tipousuariocontrol"] = 'usuarioverificado';
        if ($usu["estado"] != 'VE' || $usu["claveconfirmacion"] == '') {
            echo "Error 8 : Usuario verificado no se encuentra activado";
            exit();
        }
    }
    */
    //Weymer : 2019-05-27 : Se ajusta la validación del usuario ya que cuando encuentra un usuario verificado en estado PE, no continuaba la validación con el usuario registrado.
    $usu = retornarRegistro('usuarios_verificados', "estado='VE' and email='" . $arr["emailcontrol"] . "' and identificacion='" . $arr["identificacioncontrol"] . "' and celular='" . $arr["celularcontrol"] . "'");
    if ($usu === false || empty($usu)) {
        $usu = retornarRegistro('usuarios_registrados', "estado='AP' and email='" . $arr["emailcontrol"] . "' and identificacion='" . $arr["identificacioncontrol"] . "' and celular='" . $arr["celularcontrol"] . "'");
        if ($usu === false || empty($usu)) {
            echo "Error 6 : No encontrado ni como usuario registrado ni como usuario verificado";
            exit();
        } else {
            $_SESSION["generales"]["tipousuariocontrol"] = 'usuarioregistrado';
            //Weymer 2019-05-27 : Adiciona variables requeridas para realiza verificación de identidad.
            $_SESSION["generales"]["nombre1usuariocontrol"] = strtoupper($usu["nombre1"]);
            $_SESSION["generales"]["nombre2usuariocontrol"] = strtoupper($usu["nombre2"]);
            $_SESSION["generales"]["apellido1usuariocontrol"] = strtoupper($usu["apellido1"]);
            $_SESSION["generales"]["apellido2usuariocontrol"] = strtoupper($usu["apellido2"]);
            if ($usu["estado"] != 'AP') {
                echo "Error 7 : Usuario registrado no se encuentra activado";
                exit();
            }
        }
    } else {
        $_SESSION["generales"]["tipousuariocontrol"] = 'usuarioverificado';
        //Weymer 2019-05-27 : Adiciona variables requeridas para realiza verificación de identidad.
        $_SESSION["generales"]["nombre1usuariocontrol"] = strtoupper($usu["nombres"]);
        $_SESSION["generales"]["nombre2usuariocontrol"] = '';
        $_SESSION["generales"]["apellido1usuariocontrol"] = strtoupper($usu["apellido1"]);
        $_SESSION["generales"]["apellido2usuariocontrol"] = strtoupper($usu["apellido2"]);
    }


    $claveUsuario = '';
    if (isset($usu["claveacceso"]) && (trim($usu["claveacceso"]) != '')) {
        $claveUsuario = trim($usu["claveacceso"]);
    }
    if (isset($usu["clave"]) && (trim($usu["clave"]) != '')) {
        $claveUsuario = trim($usu["clave"]);
    }


    //
    $_SESSION["generales"]["validado"] = 'SI';
    $_SESSION["generales"]["escajero"] = 'NO';
    $_SESSION["generales"]["tipousuario"] = '00';
     //Weymer 2019-05-27 : Adiciona variable tipoidentificacion para realiza verificación de identidad.
     $_SESSION["generales"]["tipoidentificacionusuariocontrol"] = $usu["tipoidentificacion"];
     $_SESSION["generales"]["identificacionusuariocontrol"] = $usu["identificacion"];
     //Weymer 2019-05-27 : Adiciona variable fechaexpedicion para realiza verificación de identidad.
     $_SESSION["generales"]["fechaexpedicionusuariocontrol"] = $usu["fechaexpedicion"];
    $_SESSION["generales"]["celularusuariocontrol"] = $usu["celular"];
    $_SESSION["generales"]["emailusuariocontrol"] = $usu["email"];
    $_SESSION["generales"]["nombreusuariocontrol"] = strtoupper($usu["nombre"]);
    //Weymer 2019-05-27 : Adiciona variables requeridas para realiza verificación de identidad.
    $_SESSION["generales"]["direccionusuariocontrol"] = $usu["direccion"];
    $_SESSION["generales"]["municipiousuariocontrol"] = $usu["municipio"];
    //
    $_SESSION["generales"]["controlusuarioretornara"] = '';
    $_SESSION["generales"]["controlusuariorutina"] = '';
    //
    $_SESSION["generales"]["claveusuariocontrol"] = $claveUsuario;
    //
    $_SESSION["generales"]["sedeusuario"] = '99';
    $_SESSION["generales"]["idcodigosirepcaja"] = '';
    $_SESSION["generales"]["idcodigosirepdigitacion"] = '';
    $_SESSION["generales"]["idcodigosirepregistro"] = '';
    $_SESSION["generales"]["fechaactivacion"] = '';
    $_SESSION["generales"]["fechainactivacion"] = '';
}

//
if ($arr["idusuario"] == 'USUFIRMANTE') {

    //
    $_SESSION["generales"]["validado"] = 'NO';
    $_SESSION["generales"]["escajero"] = 'NO';
    $_SESSION["generales"]["tipousuario"] = '00';
    $_SESSION["generales"]["identificacionusuariocontrol"] = $arr["identificacioncontrol"];
    $_SESSION["generales"]["celularusuariocontrol"] = '';
    $_SESSION["generales"]["emailusuariocontrol"] = $arr["emailcontrol"];
    $_SESSION["generales"]["nombreusuariocontrol"] = '';
    $_SESSION["generales"]["controlusuarioretornara"] = '';
    $_SESSION["generales"]["controlusuariorutina"] = '';
    $_SESSION["generales"]["claveusuariocontrol"] = '';
    $_SESSION["generales"]["sedeusuario"] = '99';
    $_SESSION["generales"]["idcodigosirepcaja"] = '';
    $_SESSION["generales"]["idcodigosirepdigitacion"] = '';
    $_SESSION["generales"]["idcodigosirepregistro"] = '';
    $_SESSION["generales"]["fechaactivacion"] = '';
    $_SESSION["generales"]["fechainactivacion"] = '';
}
//
if (!isset($arr["script"]) || $arr["script"] == '') {
    echo "Error 9 : No se indico el script a ejecutar";
    exit();
}

//
if (!isset($arr["accion"]) || $arr["accion"] == '') {
    echo "Error 10 : No se indico la accion a ejecutar";
    exit();
}

//
$_SESSION["vars1"] = array();
$tlog = '';
foreach ($arr["parametros"] as $p => $k) {
    $tlog .= date("H:i:s") . ' PAR: ' . $p . ' => ' . $k . "\r\n";
    $_SESSION["vars1"][$p] = $k;
}
$_SESSION["vars1"]["accion"] = $arr["accion"];
foreach ($_SESSION["vars1"] as $key => $valor) {
    $tlog .= date("H:i:s") . ' VARS1: ' . $key . ' => ' . $valor . "\r\n";
}
$tlog .= "\r\n";
// fwrite ($f,$tlog);
// fclose ($f);

//
header("Location:librerias/proceso/" . $arr["script"]);

function encriptarMcrypt($cadena, $clave)
{
    $cifrado = MCRYPT_RIJNDAEL_256;
    $modo = MCRYPT_MODE_ECB;
    return mcrypt_encrypt(
        $cifrado,
        $clave,
        $cadena,
        $modo,
        mcrypt_create_iv(mcrypt_get_iv_size($cifrado, $modo), MCRYPT_RAND)
    );
}

function desencriptarMcrypt($cadena, $clave)
{
    $cifrado = MCRYPT_RIJNDAEL_256;
    $modo = MCRYPT_MODE_ECB;
    return mcrypt_decrypt(
        $cifrado,
        $clave,
        $cadena,
        $modo,
        mcrypt_create_iv(mcrypt_get_iv_size($cifrado, $modo), MCRYPT_RAND)
    );
}

function retornarDispositivoLanzador()
{
    require_once('librerias/funciones/Mobile_Detect.php');
    $return = 'computer';
    $disp = new Mobile_Detect();
    if ($disp->isMobile()) {
        $return = 'mobile';
    }
    if ($disp->isTablet()) {
        $return = 'tablet';
    }
    unset($disp);
    $_SESSION["generales"]["tipodispositivo"] = $return;
    return $_SESSION["generales"]["tipodispositivo"];
}
