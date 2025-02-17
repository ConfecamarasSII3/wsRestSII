<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarTiposEsadl {

    public function consultarTiposEsadl(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["municipio"] = '';
        $_SESSION["jsonsalida"]["renglones"] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("municipio", true);

        //
        $_SESSION["jsonsalida"]["municipio"] = $_SESSION["entrada"]["municipio"];
        
        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarTiposEsadl', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (strlen($_SESSION["entrada"]["municipio"]) != 5) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El campo municipio no tiene la longitud correcta';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        $mysqli = conexionMysqliApi();
        
        // ********************************************************************** //
        // Busqueda de barrios
        // ********************************************************************** //
        $rel = retornarRegistrosMysqliApi($mysqli, 'mreg_barriosmuni', "idmunicipio='" . $_SESSION["entrada"]["municipio"] . "'","nombre");
        if ($rel && !empty ($rel)) {
            foreach ($rel as $r) {
                $bar = array ();
                $bar["codigo"] = $r["idbarrio"];
                $bar["nombre"] = $r["nombre"];
                $_SESSION["jsonsalida"]["renglones"][] = $bar;                
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
