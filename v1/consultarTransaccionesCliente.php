<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarTransaccionesCliente {

    public function consultarTransaccionesCliente(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        //cantidad de registros
        $limit = 50;

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $arrRecibos = array();
        $_SESSION["jsonsalida"]["liquidaciones"] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("emailusuario", true);
        $api->validarParametro("tipo", true);
        $api->validarParametro("cantidad", true);


        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarTransaccionesCliente', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        // ********************************************************************** //
        // Buscar recibos
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();
        
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $cant = 0;
        $regs = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', "emailcontrol='" . $_SESSION["entrada"]["emailusuario"] . "' or email='" . $_SESSION["entrada"]["emailusuario"] . "'", "fecha desc, hora desc");
        if ($regs === false || empty($regs)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron registros para el emial indicado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        foreach ($regs as $r) {
            $incluir = 'no';
            if ($_SESSION["entrada"]["tipo"] == '1') {
                if ($r["numerorecibo"] == '') {
                    $incluir = 'si';
                }
            }
            if ($_SESSION["entrada"]["tipo"] == '2') {
                if ($r["numerorecibo"] != '') {
                    $incluir = 'si';
                }
            }
            if ($incluir == 'si') {
                $cant++;
                if ($cant <= $_SESSION["entrada"]["cantidad"]) {
                    $liq = array();
                    $liq["idliquidacion"] = $r["idliquidacion"];
                    $liq["numerorecuperacion"] = $r["numerorecuperacion"];
                    $liq["fechaliquidacion"] = $r["fecha"];
                    $liq["horaliquidacion"] = $r["hora"];
                    $liq["tipotramite"] = $r["tipotramite"];
                    $liq["subtipotramite"] = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_campos', "idliquidacion=" . $r["idliquidacion"] . " and campo='subtipotramite'", "contenido");
                    $liq["identificacioncliente"] = $r["identificacioncliente"];
                    $liq["nombrecliente"] = $r["nombrecliente"];
                    $liq["valortransaccion"] = $r["valortotal"];
                    $liq["recibo"] = $r["numerorecibo"];
                    $liq["radicado"] = $r["numeroradicado"];
                    $liq["fecharecibo"] = $r["fecharecibo"];
                    $liq["horarecibo"] = $r["horarecibo"];
                    $liq["estadoliquidacion"] = $r["idestado"];
                    $_SESSION["jsonsalida"]["liquidaciones"][] = $liq;
                }
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
