<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait almacenarFormularioRenovacion {

    public function almacenarFormularioRenovacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
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

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("numerorecuperacion", false);
        $api->validarParametro("idliquidacion", false);
        $api->validarParametro("expediente", false);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('almacenarFormularioRenovacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["numerorecuperacion"] == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el número de recuperación';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["idliquidacion"] == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el número de la liquidación';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["expediente"] == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el número del expediente';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error conectándose a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Consulta la liquidacion
        // ********************************************************************** //
        $liq = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["numerorecuperacion"], 'NR');
        if ($liq === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Número de recuperación no encontrado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Serializa la data recibida
        // ********************************************************************** //
        if (isset($_SESSION["entrada"]["datos"]["f"])) {
            foreach ($_SESSION["entrada"]["datos"]["f"] as $f) {
                $act = 'no';
                if ($f["anodatos"] > $_SESSION["entrada"]["datos"]["anodatos"]) {
                    $act = 'si';
                } else {
                    if ($f["anodatos"] == $_SESSION["entrada"]["datos"]["anodatos"]) {
                        if ($f["fechadatos"] >= $_SESSION["entrada"]["datos"]["fechadatos"]) {
                            $act = 'si';
                        }
                    }
                }
                if ($act == 'si') {
                    if (!isset($f["personaltemp"])) {
                        $f["personaltemp"] = '';
                    }
                    if (!isset($f["valest"])) {
                        $f["valest"] = $f["actvin"];
                    }
                    if (!isset($f["actfij"])) {
                        $f["actfij"] = 0;
                    }
                    if (!isset($f["fijnet"])) {
                        $f["fijnet"] = 0;
                    }
                    if (!isset($f["actval"])) {
                        $f["actval"] = 0;
                    }
                    if (!isset($f["actotr"])) {
                        $f["actotr"] = 0;
                    }
                    if (!isset($f["actsinaju"])) {
                        $f["actsinaju"] = 0;
                    }
                    if (!isset($f["invent"])) {
                        $f["invent"] = 0;
                    }
                    if (!isset($f["depamo"])) {
                        $f["depamo"] = 0;
                    }
                    if (!isset($f["gasint"])) {
                        $f["gasint"] = 0;
                    }
                    if (!isset($f["gasimp"])) {
                        $f["gasimp"] = 0;
                    }
                    $_SESSION["entrada"]["datos"]["anodatos"] = $f["anodatos"];
                    $_SESSION["entrada"]["datos"]["fechadatos"] = $f["fechadatos"];
                    $_SESSION["entrada"]["datos"]["personal"] = $f["personal"];
                    $_SESSION["entrada"]["datos"]["personaltemp"] = $f["personaltemp"];
                    $_SESSION["entrada"]["datos"]["actvin"] = $f["actvin"];
                    $_SESSION["entrada"]["datos"]["valest"] = $f["valest"];
                    $_SESSION["entrada"]["datos"]["actcte"] = $f["actcte"];
                    $_SESSION["entrada"]["datos"]["actnocte"] = $f["actnocte"];
                    $_SESSION["entrada"]["datos"]["actfij"] = $f["actfij"];
                    $_SESSION["entrada"]["datos"]["fijnet"] = $f["fijnet"];
                    $_SESSION["entrada"]["datos"]["actval"] = $f["actval"];
                    $_SESSION["entrada"]["datos"]["actotr"] = $f["actotr"];
                    $_SESSION["entrada"]["datos"]["acttot"] = $f["acttot"];
                    $_SESSION["entrada"]["datos"]["actsinaju"] = $f["actsinaju"];
                    $_SESSION["entrada"]["datos"]["invent"] = $f["invent"];
                    $_SESSION["entrada"]["datos"]["pascte"] = $f["pascte"];
                    $_SESSION["entrada"]["datos"]["paslar"] = $f["paslar"];
                    $_SESSION["entrada"]["datos"]["pastot"] = $f["pastot"];
                    $_SESSION["entrada"]["datos"]["pattot"] = $f["pattot"];
                    $_SESSION["entrada"]["datos"]["paspat"] = $f["paspat"];
                    $_SESSION["entrada"]["datos"]["balsoc"] = $f["balsoc"];
                    $_SESSION["entrada"]["datos"]["ingope"] = $f["ingope"];
                    $_SESSION["entrada"]["datos"]["ingnoope"] = $f["ingnoope"];
                    $_SESSION["entrada"]["datos"]["gtoven"] = $f["gtoven"];
                    $_SESSION["entrada"]["datos"]["gtoadm"] = $f["gtoadm"];
                    $_SESSION["entrada"]["datos"]["gasope"] = $f["gasope"];
                    $_SESSION["entrada"]["datos"]["gasnoope"] = $f["gasnoope"];
                    $_SESSION["entrada"]["datos"]["cosven"] = $f["cosven"];
                    $_SESSION["entrada"]["datos"]["gasint"] = $f["gasint"];
                    $_SESSION["entrada"]["datos"]["gasimp"] = $f["gasimp"];
                    $_SESSION["entrada"]["datos"]["depamo"] = $f["depamo"];
                    $_SESSION["entrada"]["datos"]["utiope"] = $f["utiope"];
                    $_SESSION["entrada"]["datos"]["utinet"] = $f["utinet"];
                }
            }
        }
       // $xml = json_encode($_SESSION["entrada"]["datos"]);
        
         $xml = \funcionesRegistrales::serializarExpedienteMatricula($mysqli, $_SESSION["entrada"]["numerorecuperacion"], $_SESSION["entrada"]["datos"]);

        // ********************************************************************** //
        // Borra el contenido anterior
        // ********************************************************************** //        
        borrarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos',"idliquidacion=" . $liq["idliquidacion"] . " and expediente='" . $_SESSION["entrada"]["expediente"] . "'");

        // ********************************************************************** //
        // Inserta el nuevo contenido
        // ********************************************************************** //                
        $arrCampos = array (
            'idliquidacion',
            'secuencia',
            'cc',
            'expediente',
            'numrue',
            'identificador',
            'grupodatos',
            'xml',
            'idestado'
        );
        $arrValores = array (
            $_SESSION["entrada"]["idliquidacion"],
            "'000'",
            "''",
            "'" . $_SESSION["entrada"]["expediente"] . "'",
            "''",
            "''",
            "'completo'",
            "'" . addslashes($xml) . "'",
            "'2'"            
        );
        insertarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', $arrCampos, $arrValores);

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
