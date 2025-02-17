<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait generarAlertasTempranas {

    public function generarAlertasTempranas(API $api) {
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
        
        //
        // $tipoalerta
        // "C" de Consulta
        // "T" de Trámite
        //
        // $tiporegistro
        // RegMer
        // RegEsadl
        // RegPro
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("tipoalerta", true);
        if ($_SESSION["entrada"]["tipoalerta"] != 'C' && $_SESSION["entrada"]["tipoalerta"] != 'T') { 
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Tipo alerta reportado en forma erronea';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if ($_SESSION["entrada"]["tipoalerta"] == 'C') {
            $api->validarParametro("expediente", true);
            $api->validarParametro("usuariocontrol", true);
            $api->validarParametro("emailusuariocontrol", true);
            $api->validarParametro("nombreusuariocontrol", true);
            $api->validarParametro("celularusuariocontrol", true);
            $api->validarParametro("ipcliente", true);
        }
        if ($_SESSION["entrada"]["tipoalerta"] == 'T') {
            $api->validarParametro("tiporegistro", true);
            $api->validarParametro("tipotramite", true);
            $api->validarParametro("idliquidacion", true);
            $api->validarParametro("expediente", true);
            $api->validarParametro("usuariocontrol", true);
            $api->validarParametro("emailusuariocontrol", true);
            $api->validarParametro("nombreusuariocontrol", true);
            $api->validarParametro("celularusuariocontrol", true);
            $api->validarParametro("ipcliente", true);
        }
        

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('generarAlertasTempranas', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["tipoalerta"] == 'C') {
            $res = \funcionesRegistrales::generarAlertaSiprefConsulta($mysqli, $_SESSION["entrada"]["expediente"], $_SESSION["entrada"]["usuariocontrol"], $_SESSION["entrada"]["emailusuariocontrol"], $_SESSION["entrada"]["nombreusuariocontrol"], $_SESSION["entrada"]["celularusuariocontrol"], $_SESSION["entrada"]["ipcliente"]);
        }
        
        if ($_SESSION["entrada"]["tipoalerta"] == 'T') {
            $res = \funcionesRegistrales::generarAlertaSiprefTemprana($mysqli, $_SESSION["entrada"]["idliquidacion"], $_SESSION["entrada"]["expediente"], $_SESSION["entrada"]["tiporegistro"], $_SESSION["entrada"]["tipotramite"], $_SESSION["entrada"]["usuariocontrol"], $_SESSION["entrada"]["emailusuariocontrol"], $_SESSION["entrada"]["nombreusuariocontrol"], $_SESSION["entrada"]["celularusuariocontrol"], $_SESSION["entrada"]["ipcliente"]);
        }

        if ($res === true) {
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Alerta enviada correctamente';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = substr($res,6);
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        
    
    }


}
