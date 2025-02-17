<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarTransaccion {

    public function consultarTransaccion(API $api) {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array ();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["estado"] = '';
        $_SESSION["jsonsalida"]["numerorecibo"] = '';
        $_SESSION["jsonsalida"]["numerooperacion"] = '';
        $_SESSION["jsonsalida"]["radicado"] = '';
        $_SESSION["jsonsalida"]["valorpagado"] = '';
        $_SESSION["jsonsalida"]["fechapago"] = '';
        $_SESSION["jsonsalida"]["horapago"] = '';
        $_SESSION["jsonsalida"]["formapago"] = '';
        $_SESSION["jsonsalida"]["numeroautorizacion"] = '';
        $_SESSION["jsonsalida"]["idbanco"] = '';
        $_SESSION["jsonsalida"]["idfranquicia"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idliquidacion", true);
        $api->validarParametro("numerorecuperacion", true);

        if (!$api->validarToken('consultarTransaccion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();
        
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        // ********************************************************************** //
        // Busca la liquidación
        // ********************************************************************** // 
        $arrTemD = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "idliquidacion='" . $_SESSION["entrada"]["idliquidacion"] . "' ");
        if (count($arrTemD) == 0) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Transacción no encontrada.';
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = '';            
            $_SESSION["jsonsalida"]["numerorecibo"] = $arrTemD['numerorecibo'];
            $_SESSION["jsonsalida"]["estado"] = \funcionesGenerales::estadoRespuesta($arrTemD['idestado']);
            if ($_SESSION["jsonsalida"]["estado"] == 'NOT PAY' && $_SESSION["jsonsalida"]["numerorecibo"] != '') {
                $_SESSION["jsonsalida"]["estado"] = 'APPROVED';
            }
            $_SESSION["jsonsalida"]["numerooperacion"] = $arrTemD['numerooperacion'];
            $_SESSION["jsonsalida"]["radicado"] = $arrTemD['numeroradicacion'];
            $_SESSION["jsonsalida"]["valorpagado"] = $arrTemD['valortotal'];
            $_SESSION["jsonsalida"]["fechapago"] = $arrTemD['fecha'];
            $_SESSION["jsonsalida"]["horapago"] = $arrTemD['hora'];
            $_SESSION["jsonsalida"]["formapago"] = $arrTemD['idformapago'];
            $_SESSION["jsonsalida"]["numeroautorizacion"] = $arrTemD['numeroautorizacion'];
            $_SESSION["jsonsalida"]["idbanco"] = $arrTemD['idcodban'];
            $_SESSION["jsonsalida"]["idfranquicia"] = \funcionesGenerales::franquicia($arrTemD['idfranquicia']);
            $_SESSION["jsonsalida"]["operador"] = $arrTemD['idusuario'];
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
