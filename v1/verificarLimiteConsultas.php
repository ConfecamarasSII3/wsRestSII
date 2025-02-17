<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait verificarLimiteConsultas {

    public function verificarLimiteConsultas(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["respuesta"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("email", true);
        $api->validarParametro("expediente", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('verificarLimiteConsultas', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();

        // ********************************************************************** //
        // Busqueda comerciantes con la identificacion
        // ********************************************************************** //
        $cantidadlimite = 999;
        if (defined('CANTIDAD_CONSULTAS_USUARIOS_PUBLICOS_HORA') && trim(CANTIDAD_CONSULTAS_USUARIOS_PUBLICOS_HORA) != '') {
            $cantidadlimite = intval(CANTIDAD_CONSULTAS_USUARIOS_PUBLICOS_HORA);
        }
        
        $fecha = date ("Ymd");
        $hora = sprintf("%02s",date ("H"));
        $res = contarRegistrosMysqliApi($mysqli, 'mreg_log_acceso_consultas', "email='" . $_SESSION["entrada"]["email"] . "' and fecha='" . $fecha . "' and hora='" . $hora . "'");
        if ($res >= $cantidadlimite) {
            $_SESSION["jsonsalida"]["codigoerror"] = '0000';
            $_SESSION["jsonsalida"]["respuesta"] = 'NO';            
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = '0000';
            $_SESSION["jsonsalida"]["respuesta"] = 'SI';                        
            $arrCampos = array (
                'email',
                'fecha',
                'hora',
                'expediente'
            );
            $arrValores = array (
                "'" . $_SESSION["entrada"]["email"] . "'",
                "'" . $fecha . "'",
                "'" . $hora . "'",
                "'" . $_SESSION["entrada"]["expediente"] . "'"
            );
            insertarRegistrosMysqliApi($mysqli, 'mreg_log_acceso_consultas', $arrCampos, $arrValores);
        }
        $mysqli->close();
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }
}
