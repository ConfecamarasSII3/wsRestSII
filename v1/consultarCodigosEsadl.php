<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarCodigosEsadl {

    public function consultarCodigosEsadl(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
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

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarCodigosEsadl', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();
        
        // ********************************************************************** //
        // Busqueda de clases generales
        // ********************************************************************** //
        $rel = retornarRegistrosMysqliApi($mysqli, 'mreg_clase_esadl_gen', "1=1","id");
        if ($rel && !empty ($rel)) {
            foreach ($rel as $r) {
                $bar = array ();
                $bar["id"] = $r["id"];
                $bar["descripcion"] = $r["descripcion"];
                $_SESSION["jsonsalida"]["renglones_clases_generales"][] = $bar;                
            }
        }

        // ********************************************************************** //
        // Busqueda de clases especificas
        // ********************************************************************** //
        $rel = retornarRegistrosMysqliApi($mysqli, 'mreg_clase_esadl', "1=1","id");
        if ($rel && !empty ($rel)) {
            foreach ($rel as $r) {
                $bar = array ();
                $bar["id"] = $r["id"];
                $bar["descripcion"] = $r["descripcion"];
                $_SESSION["jsonsalida"]["renglones_clases_especificas"][] = $bar;                
            }
        }
        
        //
        $mysqli->close();
        
        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
