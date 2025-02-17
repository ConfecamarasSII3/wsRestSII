<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarCodigosEmprendimientoSocial {

    public function consultarCodigosEmprendimientoSocial (API $api) {
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
        $_SESSION["jsonsalida"]["categorias"] = array();
        $_SESSION["jsonsalida"]["beneficiarios"] = array();

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
        if (!$api->validarToken('consultarCodigosEmprendimientoSocial', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();
        
        // ********************************************************************** //
        // Busqueda de clases generales
        // ********************************************************************** //
        $rel = retornarRegistrosMysqliApi($mysqli, 'tablas', "tabla='empsoc_categorias'","campo1");
        if ($rel && !empty ($rel)) {
            foreach ($rel as $r) {
                $bar = array ();
                $bar["id"] = $r["idcodigo"];
                $bar["descripcion"] = $r["descripcion"];
                $bar["orden"] = $r["campo1"];
                $_SESSION["jsonsalida"]["categorias"][] = $bar;                
            }
        }

        // ********************************************************************** //
        // Busqueda de clases especificas
        // ********************************************************************** //
        $rel = retornarRegistrosMysqliApi($mysqli, 'tablas', "tabla='empsoc_beneficiarios'","campo1");
        if ($rel && !empty ($rel)) {
            foreach ($rel as $r) {
                $bar = array ();
                $bar["id"] = $r["idcodigo"];
                $bar["descripcion"] = $r["descripcion"];
                $bar["orden"] = $r["campo1"];
                $_SESSION["jsonsalida"]["beneficiarios"][] = $bar;                
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
