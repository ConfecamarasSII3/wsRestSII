<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait verificarRegistro {

    public function verificarRegistro(API $api) {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["tipousuario"] = '';

        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("identificacion", true);
        $api->validarParametro("email", true);
        $api->validarParametro("clave", false);

        if ($_SESSION["entrada"]["identificacion"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó la identificación del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["email"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el correo del usuario';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if (!filter_var($_SESSION["entrada"]["email"], FILTER_VALIDATE_EMAIL) === true) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó un correo válido';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('verificarRegistro', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // $claveph = password_hash($clavelimpia, PASSWORD_DEFAULT);
        // ********************************************************************** //
        // Busca el usuario primero como verificado y luego como registrado
        // ********************************************************************** // 
        if (defined('ACTIVAR_USUARIOS_NACIONALES') && substr(ACTIVAR_USUARIOS_NACIONALES, 0, 2) == 'SI') {
            $ok = \funcionesGenerales::validarSuscripcionNacional($_SESSION["entrada"]["email"], $_SESSION["entrada"]["identificacion"]);
        } else {
            $ok = array();
            $ok["codigoerror"] = '0001';
        }
        if ($ok["codigoerror"] === '0000') {
            $_SESSION["jsonsalida"]["codigoerror"] = '0000';
            $_SESSION["jsonsalida"]["tipousuario"] = "Verificado nacional";
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($ok["codigoerror"] == '9994') {
            $_SESSION["jsonsalida"]["codigoerror"] = '0001';
            $_SESSION["jsonsalida"]["tipousuario"] = "LA suscripción del usuario se encuentra verificada para no ha sido activada";
            $_SESSION["jsonsalida"]["tipousuario"] = "Verificado nacional - sin activar";
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $arrTemD = retornarRegistroMysqliApi($mysqli, 'usuarios_verificados', "estado='VE' and claveconfirmacion<>'' and identificacion='" . $_SESSION["entrada"]["identificacion"] . "' and email='" . $_SESSION["entrada"]["email"] . "'");
        if (!$arrTemD || empty($arrTemD)) {
            $arrTemD = retornarRegistroMysqliApi($mysqli, 'usuarios_registrados', "estado='AP' and fechaactivacion<>'' and identificacion='" . $_SESSION["entrada"]["identificacion"] . "' and email='" . $_SESSION["entrada"]["email"] . "'");
            if (!$arrTemD || empty($arrTemD)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "0001";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Combinación Email/Identificación';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {
                $mysqli->close();
                $_SESSION["jsonsalida"]["tipousuario"] = "Registrado local";
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } else {
            $mysqli->close();
            $_SESSION["jsonsalida"]["tipousuario"] = "Verificado local";
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
    }

}
