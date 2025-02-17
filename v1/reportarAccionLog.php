<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait reportarAccionLog {

    public function reportarAccionLog(API $api) {

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

        // Verifica que  método de recepcion de parámetros sea POST
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
        $api->validarParametro("aplicacion", true);
        $api->validarParametro("numerorecuperacion", false);
        $api->validarParametro("accion", true);
        $api->validarParametro("ipcliente", false);
        $api->validarParametro("observaciones", false);
        
        //
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('reportarAccionLog', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Localiza el número de liquidacion en la tabla mreg_liquidacion
        // ********************************************************************** // 
        $numliq = 0;
        if ($_SESSION["entrada"]["numerorecuperacion"] != '') {
            $temx = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "numerorecuperacion='" . $_SESSION["entrada"]["numerorecuperacion"] . "'");
            if ($temx && !empty ($temx)) {
                $numliq = $temx["idliquidacion"];
            }
        }
        // ********************************************************************** //
        // Crea log
        // ********************************************************************** // 
        actualizarLogMysqliApi($mysqli, $_SESSION["entrada"]["accion"], '', $_SESSION["entrada"]["aplicacion"], '', '', '', $_SESSION["entrada"]["observaciones"], '', '','',$numliq,'','',$_SESSION["entrada"]["ipcliente"],$_SESSION["entrada"]["emailusuario"],$_SESSION["entrada"]["identificacionusuario"] );
        
        // **************************************************************************** //
        // Cierra base de datos
        // **************************************************************************** //        
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
