<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait actualizarEstadoLiquidacion
{

    public function actualizarEstadoLiquidacion(API $api)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';
        set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $api->armarsalidaApi("9999", "La petición debe ser POST");
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idliquidacion", true);
        $api->validarParametro("estado", true);
        $api->validarParametro("ticketid", false);
        $api->validarParametro("gateway", false);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** //
        if (!$api->validarToken('actualizarEstadoLiquidacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $_SESSION["entrada"]["estado"] = sprintf("%02s", $_SESSION["entrada"]["estado"]);
        if ($_SESSION["entrada"]["estado"] > '06' && $_SESSION["entrada"]["estado"] != '08' && $_SESSION["entrada"]["estado"] != '19' && $_SESSION["entrada"]["estado"] != '44') {
            $api->armarsalidaApi("9999", "El estado reportado no es un estado válido");
        }

        $_SESSION["entrada"]["idliquidacion"] = intval($_SESSION["entrada"]["idliquidacion"]);
        if ($_SESSION["entrada"]["idliquidacion"] == 0) {
            $api->armarsalidaApi("9999", "Liquidación no debe ser 0");
        }

        //
        $mysqli = conexionMysqliApi();
        //
        $liq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);
        if ($liq === false || empty($liq)) {
            $api->armarsalidaApi("9999", "Liquidacion no localizada", $mysqli);
        }
        if ($liq["idestado"] > '06' && $liq["idestado"] != '08' && $liq["idestado"] != '19' && $liq["idestado"] != '44') {
            $api->armarsalidaApi("9999", "Liquidacion en un estado no disponible para ser modificada (" . $liq["idestado"] . ")", $mysqli);
        }

        if (
            $liq["idestado"] != $_SESSION["entrada"]["estado"] ||
            $liq["ticketid"] != $_SESSION["entrada"]["ticketid"] ||
            $liq["gateway"] != $_SESSION["entrada"]["gateway"]
        ) {
            $arrCampos = array(
                'idestado',
                'ticketid',
                'gateway'
            );
            $arrValores = array(
                "'" . sprintf("%02s", $_SESSION["entrada"]["estado"]) . "'",
                "'" . $_SESSION["entrada"]["ticketid"] . "'",
                "'" . $_SESSION["entrada"]["gateway"] . "'"
            );
            regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', $arrCampos, $arrValores, "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);
            $detalle = 'Actualizacion estado de la liquidacion a traves del api de integracion, idliquidacion = ' . $_SESSION["entrada"]["idliquidacion"] . ', ';
            $detalle .= 'Estado: ' . $_SESSION["entrada"]["estado"] . ', ticketid = ' . $_SESSION["entrada"]["ticketid"] . ', gateway = ' . $_SESSION["entrada"]["gateway"];
            actualizarLogMysqliApi($mysqli, '005', $_SESSION["entrada"]["usuariows"], 'API-actualizarEstadoLiquidacion', '', '', '', $detalle, '', '');
        } else {
            $api->armarsalidaApi("9999", "Liquidación no actualizada, sin cambios", $mysqli);
        }
        
        \logApi::peticionRest('api_' . __FUNCTION__);
        $api->armarsalidaApi("0000", "Liquidación actualizada", $mysqli);
    }
}
