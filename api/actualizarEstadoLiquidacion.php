<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait actualizarEstadoLiquidacion {

    public function actualizarEstadoLiquidacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
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

        $_SESSION["entrada"]["estado"] = sprintf("%02s",$_SESSION["entrada"]["estado"]);
        if ($_SESSION["entrada"]["estado"] > '06' && $_SESSION["entrada"]["estado"] != '08' && $_SESSION["entrada"]["estado"] != '19' && $_SESSION["entrada"]["estado"] != '44') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El estado reportado no es un estado válido';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        $_SESSION["entrada"]["idliquidacion"] = intval($_SESSION["entrada"]["idliquidacion"]);
        if ($_SESSION["entrada"]["idliquidacion"] == 0) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no debe ser 0';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        //
        $mysqli = conexionMysqliApi();

        
        //
        $liq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);
        if ($liq === false || empty($liq)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidacion no localizada';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if ($liq["idestado"] > '06' && $liq["idestado"] != '08' && $liq["idestado"] != '19' && $liq["idestado"] != '44') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidacion en un estado no disponible para ser modificada (' . $liq["idestado"] . ')';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
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
                "'" . sprintf("%02s",$_SESSION["entrada"]["estado"]) . "'",
                "'" . $_SESSION["entrada"]["ticketid"] . "'",
                "'" . $_SESSION["entrada"]["gateway"] . "'"
            );
            regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', $arrCampos, $arrValores, "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);
            $detalle = 'Actualizacion estado de la liquidacion a traves del api de integracion, idliquidacion = ' . $_SESSION["entrada"]["idliquidacion"] . ', ';
            $detalle .= 'Estado: ' . $_SESSION["entrada"]["estado"] . ', ticketid = ' . $_SESSION["entrada"]["ticketid"] . ', gateway = ' . $_SESSION["entrada"]["gateway"];            
            actualizarLogMysqliApi($mysqli, '005', $_SESSION["entrada"]["usuariows"], 'API-actualizarEstadoLiquidacion', '', '', '', $detalle, '', '');
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no actualizada, sin cambios';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación actualizada';
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
