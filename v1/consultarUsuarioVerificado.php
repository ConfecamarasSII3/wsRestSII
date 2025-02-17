<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarUsuarioVerificado {

    public function consultarUsuarioVerificado(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['claveusuario'] = '';
        $_SESSION['jsonsalida']['nombres'] = '';
        $_SESSION['jsonsalida']['apellido1'] = '';
        $_SESSION['jsonsalida']['apellido2'] = '';
        $_SESSION['jsonsalida']['tipousuario'] = '';

        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("identificacionusuario", true);
        $api->validarParametro("emailusuario", true);

        if (!filter_var($_SESSION["entrada"]["emailusuario"], FILTER_VALIDATE_EMAIL) === true) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó un correo válido';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (!$api->validarToken('consultarUsuarioVerificado', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (defined('ACTIVAR_USUARIOS_NACIONALES') && substr(ACTIVAR_USUARIOS_NACIONALES, 0, 2) == 'SI') {
            $ok = \funcionesGenerales::validarSuscripcionNacional($_SESSION["entrada"]["emailusuario"], $_SESSION["entrada"]["identificacionusuario"]);
        } else {
            $ok = array();
            $ok["codigoerror"] = '0001';
        }

        //
        if ($ok["codigoerror"] === '0000') {
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario localizado';
            // $_SESSION["jsonsalida"]["nombreusuario"] = $ok["nombre"];
            $_SESSION['jsonsalida']['nombres'] = trim((string) $ok["nombre1"] . ' ' . (string) $ok["nombre2"]);
            $_SESSION['jsonsalida']['apellido1'] = $ok["apellido1"];
            $_SESSION['jsonsalida']['apellido2'] = $ok["apellido2"];
            $_SESSION['jsonsalida']['tipousuario'] = 'Nacional verificado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            exit();
        }

        // 
        if ($ok["codigoerror"] == '9994') {
            $resm = \funcionesGenerales::recordarContrasenaNacional($_SESSION["entrada"]["emailusuario"], $_SESSION["entrada"]["identificacionusuario"], 'A');
            if ($resm["codigoerror"] == '0000') {
                $txt = 'Apreciado usuario, la suscripción para la identificación  No. ' . $_SESSION["entrada"]["identificacionusuario"] . ' y correo ' . $_SESSION["entrada"]["emailusuario"] . ' ';
                $txt .= 'No ha sido activada, a su buzón de correo elecrónico hemos enviado un mensaje con la contraseña segura asignada y con los términos y condiciones ';
                $txt .= 'del servicio. Igualmente en dicho correo lo invitamos a seguir el enlace de "Confirmación del correo y activación de sus suscripción.<br><br>';
                $txt .= 'Recuerde que la contraseña que le enviamos es persona e instransferible, le recomendamos no compartila con nadie.';
                $_SESSION["jsonsalida"]["mensajeerror"] = $txt;
                $_SESSION["jsonsalida"]["codigoerror"] = '0002';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {
                $txt = 'Apreciado usuario, la suscripción para la identificación  No. ' . $_SESSION["entrada"]["identificacionusuario"] . ' y correo ' . $_SESSION["entrada"]["emailusuario"] . ' ';
                $txt .= 'No ha sido activada.';
                $_SESSION["jsonsalida"]["mensajeerror"] = $txt;
                $_SESSION["jsonsalida"]["codigoerror"] = '0001';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } else {
            $mysqli = conexionMysqliApi();

            if ($mysqli === false) {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            // ********************************************************************** //
            // Busca el usuario verificado
            // ********************************************************************** // 
            // $arrTemD = retornarRegistroMysqli2($mysqli, 'usuarios_verificados', "estado='VE' and identificacion='" . $_SESSION["entrada"]["identificacionusuario"] . "' and email='" . $_SESSION["entrada"]["emailusuario"] . "' and celular='" . $_SESSION["entrada"]["celularusuario"] . "'");
            $arrTemD = retornarRegistroMysqliApi($mysqli, 'usuarios_verificados', "estado='VE' and identificacion='" . $_SESSION["entrada"]["identificacionusuario"] . "' and email='" . $_SESSION["entrada"]["emailusuario"] . "'");
            if (!$arrTemD || empty($arrTemD)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "0001";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Combinación Email/Identificación';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
                exit();
            } else {
                if (trim((string)$arrTemD['claveconfirmacion']) == "") {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9995";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario verificado no ha activado su registro';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    exit();
                } else {
                    if ($arrTemD['claveacceso'] == "" || $arrTemD['claveacceso'] == NULL) {
                        $mysqli->close();
                        $_SESSION["jsonsalida"]["codigoerror"] = "9996";
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'El Usuario no cuenta con clave de acceso grabada.';
                        $api->response($api->json($_SESSION["jsonsalida"]), 200);
                        exit();
                    } else {
                        $mysqli->close();
                        $_SESSION['jsonsalida']['claveusuario'] = trim((string)$arrTemD["claveacceso"]);
                        $_SESSION['jsonsalida']['nombres'] = $arrTemD["nombres"];
                        $_SESSION['jsonsalida']['apellido1'] = $arrTemD["apellido1"];
                        $_SESSION['jsonsalida']['apellido2'] = $arrTemD["apellido2"];
                        $_SESSION['jsonsalida']['tipousuario'] = 'Local verificado';
                        $api->response($api->json($_SESSION["jsonsalida"]), 200);
                        exit();
                    }
                }
            }
        }
    }

}
