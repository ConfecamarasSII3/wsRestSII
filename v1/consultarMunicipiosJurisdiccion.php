<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarMunicipiosJurisdiccion {

    public function consultarMunicipiosJurisdiccion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["renglones"] = array();

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
        if (!$api->validarToken('consultarMunicipiosJurisdiccion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Busqueda de barrios
        // ********************************************************************** //
        $mysqli = conexionMysqliApi();
        $rel = retornarRegistrosMysqliApi($mysqli, 'mreg_municipiosjurisdiccion', "1=1","idcodigo");
        if ($rel && !empty ($rel)) {
            foreach ($rel as $r) {
                $rx = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $r["idcodigo"]. "'");
                $mun = array ();
                $mun["idcodigo"] = $r["idcodigo"];
                $mun["nombre"] = $rx["ciudad"] . ' (' . $rx["departamento"] . ')';
                $_SESSION["jsonsalida"]["renglones"][] = $mun;                
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
