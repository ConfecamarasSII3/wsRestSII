<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait autenticarUsuarioRegistrado {

    public function autenticarUsuarioRegistrado(API $api) {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["nombreusuario"] = '';
        $_SESSION["jsonsalida"]["tipousuario"] = '';

        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("identificacionusuario", true);
        $api->validarParametro("emailusuario", true);
        $api->validarParametro("celularusuario", true);
        $api->validarParametro("claveusuario", true);

        if ($_SESSION["entrada"]["identificacionusuario"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó la identificación del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["emailusuario"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el correo del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if ($_SESSION["entrada"]["celularusuario"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el celular del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["claveusuario"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó la contraseña del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('autenticarUsuarioRegistrado', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Crea la conexión con la BD
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Decodifica la clave enviada
        // ********************************************************************** //
        $clavelimpia = \funcionesGenerales::encrypt_decrypt('decrypt', 'c0nf3c4m4r4s', 'c0nf3c4m4r4s', $_SESSION["entrada"]["claveusuario"]);
        $clavemd5 = md5($clavelimpia);
        $clavesha = sha1($clavelimpia);

        $clavemd5limpia = md5($_SESSION["entrada"]["claveusuario"]);
        $claveshalimpia = sha1($_SESSION["entrada"]["claveusuario"]);

        // $claveph = password_hash($clavelimpia, PASSWORD_DEFAULT);
        // ********************************************************************** //
        // Busca el usuario primero como verificado y luego como registrado
        // ********************************************************************** // 
        if (defined('ACTIVAR_USUARIOS_NACIONALES') && substr(ACTIVAR_USUARIOS_NACIONALES, 0, 2) == 'SI') {
            $ok = \funcionesGenerales::validarUsuarioNacional($_SESSION["entrada"]["emailusuario"], $_SESSION["entrada"]["claveusuario"], $_SESSION["entrada"]["identificacionusuario"]);
        } else {
            $ok = array();
            $ok["codigoerror"] = '0001';
        }
        if ($ok["codigoerror"] === '0000') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Clave Correcta';
            $_SESSION["jsonsalida"]["nombreusuario"] = $ok["nombre"];
            $_SESSION['jsonsalida']['nombres'] = trim($ok["nombre1"] . ' ' . $ok["nombre2"]);
            $_SESSION['jsonsalida']['apellido1'] = $ok["apellido1"];
            $_SESSION['jsonsalida']['apellido2'] = $ok["apellido2"];
            $_SESSION["jsonsalida"]["tipousuario"] = 'Nacional';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($ok["codigoerror"] == '9994' || $ok["codigoerror"] == '9993') {
            if ($ok["codigoerror"] == '9994') {
                $resm = \funcionesGenerales::recordarContrasenaNacional($_SESSION["entrada"]["emailusuario"], $_SESSION["entrada"]["identificacionusuario"], 'A');
                if ($resm["codigoerror"] == '0000') {
                    $mysqli->close();
                    $txt = 'Apreciado usuario, la suscripción para la identificación  No. ' . $_SESSION["entrada"]["identificacionusuario"] . ' y correo ' . $_SESSION["entrada"]["emailusuario"] . ' ';
                    $txt .= 'No ha sido activada, a su buzón de correo elecrónico hemos enviado un mensaje con la contraseña segura asignada y con los términos y condiciones ';
                    $txt .= 'del servicio. Igualmente en dicho correo lo invitamos a seguir el enlace de "Confirmación del correo y activación de sus suscripción.<br><br>';
                    $txt .= 'Recuerde que la contraseña que le enviamos es persona e instransferible, le recomendamos no compartila con nadie.';
                    $_SESSION["jsonsalida"]["mensajeerror"] = $txt;
                    $_SESSION["jsonsalida"]["codigoerror"] = '0002';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                } else {
                    $mysqli->close();
                    $txt = 'Apreciado usuario, la suscripción para la identificación  No. ' . $_SESSION["entrada"]["identificacionusuario"] . ' y correo ' . $_SESSION["entrada"]["emailusuario"] . ' ';
                    $txt .= 'No ha sido activada.';
                    $_SESSION["jsonsalida"]["mensajeerror"] = $txt;
                    $_SESSION["jsonsalida"]["codigoerror"] = '0001';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }
            if ($ok["codigoerror"] == '9993') {
                $mysqli->close();
                $txt = 'Contraseña incorrecta';
                $_SESSION["jsonsalida"]["mensajeerror"] = $txt;
                $_SESSION["jsonsalida"]["codigoerror"] = '0003';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } else {
            $arrTemD = retornarRegistroMysqliApi($mysqli, 'usuarios_verificados', "estado='VE' and claveconfirmacion<>'' and identificacion='" . $_SESSION["entrada"]["identificacionusuario"] . "' and email='" . $_SESSION["entrada"]["emailusuario"] . "'");
            if (!$arrTemD || empty($arrTemD)) {
                if (!defined('RESTRICCION_ACCESO_USUARIOS_REGISTRADOS') || RESTRICCION_ACCESO_USUARIOS_REGISTRADOS == 'NO') {
                    $arrTemD = retornarRegistroMysqliApi($mysqli, 'usuarios_registrados', "estado='AP' and fechaactivacion<>'' and identificacion='" . $_SESSION["entrada"]["identificacionusuario"] . "' and email='" . $_SESSION["entrada"]["emailusuario"] . "'");
                }
                if (!$arrTemD || empty($arrTemD)) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "0001";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Combinación Email/Identificación/Celular no existe';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                } else {
                    if (trim($clavemd5) != trim(strtoupper($arrTemD['clave'])) &&
                            trim($clavemd5) != trim($arrTemD['clave']) &&
                            trim($clavemd5limpia) != trim(strtoupper($arrTemD['clave'])) &&
                            trim($clavemd5limpia) != trim($arrTemD['clave']) &&
                            trim($clavesha) != trim(strtoupper($arrTemD['clave'])) &&
                            trim($clavesha) != trim($arrTemD['clave']) &&
                            trim($claveshalimpia) != trim(strtoupper($arrTemD['clave'])) &&
                            trim($claveshalimpia) != trim($arrTemD['clave']) &&
                            !password_verify($clavelimpia, $arrTemD['clave']) &&
                            !password_verify($_SESSION["entrada"]["claveusuario"], $arrTemD['clave'])) {
                        $mysqli->close();
                        $_SESSION["jsonsalida"]["codigoerror"] = "0003";
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'Clave errónea';
                        $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    } else {
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'Clave Correcta';
                        $_SESSION["jsonsalida"]["nombreusuario"] = $arrTemD["nombre"];
                        $_SESSION["jsonsalida"]["tipousuario"] = 'Local registrado';
                    }
                }
            } else {
                if (trim($clavemd5) != trim(strtoupper($arrTemD['claveacceso'])) &&
                        trim($clavemd5) != trim($arrTemD['claveacceso']) &&
                        trim($clavemd5limpia) != trim(strtoupper($arrTemD['claveacceso'])) &&
                        trim($clavemd5limpia) != trim($arrTemD['claveacceso']) &&
                        trim($clavesha) != trim(strtoupper($arrTemD['claveacceso'])) &&
                        trim($clavesha) != trim($arrTemD['claveacceso']) &&
                        trim($claveshalimpia) != trim(strtoupper($arrTemD['claveacceso'])) &&
                        trim($claveshalimpia) != trim($arrTemD['claveacceso']) &&
                        !password_verify($clavelimpia, $arrTemD['claveacceso'])) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "0004";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Clave errónea';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                } else {
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Clave Correcta';
                    $_SESSION["jsonsalida"]["nombreusuario"] = $arrTemD["nombre"];
                    $_SESSION["jsonsalida"]["tipousuario"] = 'Local verificado';
                }
            }
        }

        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
