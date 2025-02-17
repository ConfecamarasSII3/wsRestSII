<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarListaResponsabilidadesTributarias {

    public function consultarListaResponsabilidadesTributarias(API $api) {
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
        if (!$api->validarToken('consultarListaResponsabilidadesTributarias', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Busqueda de responsabilidades
        // ********************************************************************** //
        if (isset($_SESSION["entrada"]["codigoresponsabilidad"]) && trim($_SESSION["entrada"]["codigoresponsabilidad"]) != '') {
            $_SESSION["entrada"]["codigoresponsabilidad"] = sprintf("%02s", $_SESSION["entrada"]["codigoresponsabilidad"]);
            $mysqli = conexionMysqliApi();
            $rel = retornarRegistrosMysqliApi($mysqli, 'tablas', "tabla='responsabilidadestributarias' and idcodigo='" . $_SESSION["entrada"]["codigoresponsabilidad"] . "'", "idcodigo");
            $mysqli->close();
            if ($rel && !empty($rel)) {
                foreach ($rel as $r) {
                    $bar = array();
                    $bar["codigo"] = $r["idcodigo"];
                    $bar["descripcion"] = $r["descripcion"];
                    $_SESSION["jsonsalida"]["renglones"][] = $bar;
                }
            } else {
                $_SESSION["jsonsalida"]["codigoerror"] = "0001";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El código enviado no fue encontrado en la lista de responsabilidades permitidas';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } else {
            $mysqli = conexionMysqliApi();
            $rel = retornarRegistrosMysqliApi($mysqli, 'tablas', "tabla='responsabilidadestributarias' and campo1 <> 'NO'", "idcodigo");
            $mysqli->close();
            if ($rel && !empty($rel)) {
                foreach ($rel as $r) {
                    $bar = array();
                    $bar["codigo"] = $r["idcodigo"];
                    $bar["descripcion"] = $r["descripcion"];
                    $_SESSION["jsonsalida"]["renglones"][] = $bar;
                }
            }
        }

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
