<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarANI {

    public function consultarANI(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]['tipoIdentificacion'] = '';
        $_SESSION["jsonsalida"]['identificacion'] = '';
        $_SESSION["jsonsalida"]['codError'] = '';
        $_SESSION["jsonsalida"]['primerNombre'] = '';
        $_SESSION["jsonsalida"]['segundoNombre'] = '';
        $_SESSION["jsonsalida"]['particula'] = '';
        $_SESSION["jsonsalida"]['primerApellido'] = '';
        $_SESSION["jsonsalida"]['segundoApellido'] = '';
        $_SESSION["jsonsalida"]['fechaExpedicion'] = '';
        $_SESSION["jsonsalida"]['genero'] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("tipoIdentificacion", true);
        $api->validarParametro("identificacion", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 

        if (!$api->validarToken('consultarANI', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();

        // ********************************************************************** //
        // Consume ANI
        // ********************************************************************** //
        $_SESSION["generales"]["codigousuario"] = 'API';
        $res = \funcionesRues::consumirANI2($mysqli,$_SESSION["entrada"]["tipoIdentificacion"], $_SESSION["entrada"]["identificacion"]);

        //
        if ($res === false || (empty($res))) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($res["codError"] == '1') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9998';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Cedula no encontrada en la RNEC';
            $_SESSION["jsonsalida"]["tipoIdentificacion"] = $_SESSION["entrada"]["tipoIdentificacion"];
            $_SESSION["jsonsalida"]["identificacion"] = $_SESSION["entrada"]["identificacion"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {
            $_SESSION["jsonsalida"]["tipoIdentificacion"] = $_SESSION["entrada"]["tipoIdentificacion"];
            $_SESSION["jsonsalida"]["identificacion"] = $_SESSION["entrada"]["identificacion"];
            $_SESSION["jsonsalida"]["codError"] = $res["codError"];
            $_SESSION["jsonsalida"]["primerNombre"] = $res["primerNombre"];
            $_SESSION["jsonsalida"]["segundoNombre"] = $res["segundoNombre"];
            $_SESSION["jsonsalida"]["particula"] = $res["particula"];
            $_SESSION["jsonsalida"]["primerApellido"] = $res["primerApellido"];
            $_SESSION["jsonsalida"]["segundoApellido"] = $res["segundoApellido"];
            $_SESSION["jsonsalida"]["fechaExpedicion"] = $res["fechaExpedicion"];
            $_SESSION["jsonsalida"]["genero"] = $res["genero"];
            $_SESSION["jsonsalida"]["fechaNacimiento"] = $res["fechaNacimiento"];
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
