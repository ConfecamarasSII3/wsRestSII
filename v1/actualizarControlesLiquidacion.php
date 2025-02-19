<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait actualizarControlesLiquidacion {

    public function actualizarControlesLiquidacion(API $api) {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';
        set_error_handler('myErrorHandler');

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

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idliquidacion", true);
        $api->validarParametro("cumplorequisitos1780", false);
        $api->validarParametro("mantengorequisitos1780", true);
        $api->validarParametro("renunciobeneficio1780", false);
        $api->validarParametro("controlaltoimpacto", false);
        $api->validarParametro("verificacionsoportes", true);
        $api->validarParametro("multasvencidas", true);


        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('actualizarControlesLiquidacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();

        //
        $liq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);
        if ($liq === false || empty($liq)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidacion no localizada';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if ($liq["idestado"] > '05') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidacion en un estado no disponible para ser modificada';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $arrCampos = array(
            'cumplorequisitosbenley1780',
            'mantengorequisitosbenley1780',
            'renunciobeneficiosley1780',
            'controlactividadaltoimpacto',
            'multadoponal'
        );
        $arrValores = array(
            "'" . strtoupper($_SESSION["entrada"]["cumplorequisitos1780"]) . "'",
            "'" . strtoupper($_SESSION["entrada"]["mantengorequisitos1780"]) . "'",
            "'" . strtoupper($_SESSION["entrada"]["renunciobeneficio1780"]) . "'",
            "'" . strtoupper($_SESSION["entrada"]["controlaltoimpacto"]) . "'",
            "'" . strtoupper($_SESSION["entrada"]["multasvencidas"]) . "'"
        );

        regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', $arrCampos, $arrValores, "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);

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
