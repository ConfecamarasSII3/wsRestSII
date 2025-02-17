<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait enviarEmail {
    public function enviarEmail(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');

        //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida datos de entrada
        // ********************************************************************** //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("email", true);
        $api->validarParametro("asunto", true);
        $api->validarParametro("mensaje", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('enviarEmail', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        $res = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $_SESSION["entrada"]["email"], $_SESSION["entrada"]["asunto"], $_SESSION["entrada"]["mensaje"]);
        if ($res === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error enviando email';
        }
               
        // ********************************************************************** //
        // Entregar resultado
        // ********************************************************************** // 
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
