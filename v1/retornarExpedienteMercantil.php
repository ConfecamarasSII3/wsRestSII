<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait retornarExpedienteMercantil {

    public function retornarExpedienteMercantil(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("matricula", true);
        $api->validarParametro("ambiente", false);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('retornarExpedienteMercantil', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Abre conexión con la BD
        // ********************************************************************** // 
        if (!isset($_SESSION["entrada"]["ambiente"])) {
            $_SESSION["entrada"]["ambiente"] = '';
        }
        if ($_SESSION["entrada"]["ambiente"] == '' || $_SESSION["entrada"]["ambiente"] == 'DEF' || $_SESSION["entrada"]["ambiente"] == 'A') {
            $mysqli = conexionMysqliApi();
        }
        if ($_SESSION["entrada"]["ambiente"] == 'PRD' || $_SESSION["entrada"]["ambiente"] == 'P') {
            $mysqli = conexionMysqliApi('P-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if ($_SESSION["entrada"]["ambiente"] == 'DES' || $_SESSION["entrada"]["ambiente"] == 'D') {
            $mysqli = conexionMysqliApi('D-' . $_SESSION["generales"]["codigoempresa"]);
        }

        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $arrTem = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["matricula"]);
          
        //
        if ($arrTem === false || $arrTem == 0) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente no localizado en la BD.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        // **************************************************************************** //
        // Cerrar conexión a la BD
        // **************************************************************************** //        
        $mysqli->close();
        
        //
        $_SESSION["jsonsalida"] = $arrTem;
        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        
        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }
    
    public function retornarFechasMercantil(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');

        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["nombre"] = '';
        $_SESSION["jsonsalida"]["estadomatricula"] = '';
        $_SESSION["jsonsalida"]["fechamatricula"] = '';
        $_SESSION["jsonsalida"]["fecharenovacion"] = '';
        $_SESSION["jsonsalida"]["ultanoren"] = '';
        $_SESSION["jsonsalida"]["fechacancelacionn"] = '';
        $_SESSION["jsonsalida"]["fechavencimiento"] = '';
        $_SESSION["jsonsalida"]["estadisuelta"] = '';
        $_SESSION["jsonsalida"]["motivocancelacion"] = '';
        $_SESSION["jsonsalida"]["descripcionmotivocancelacion"] = '';
        $_SESSION["jsonsalida"]["fechaliquidacion"] = '';
        $_SESSION["jsonsalida"]["disueltaporvencimiento"] = '';
        $_SESSION["jsonsalida"]["disueltaporacto510"] = '';
        $_SESSION["jsonsalida"]["fechaacto510"] = '';
        $_SESSION["jsonsalida"]["fechaacto511"] = '';
        $_SESSION["jsonsalida"]["perdidacalidadcomerciante"] = '';
        $_SESSION["jsonsalida"]["fechaperdidacalidadcomerciante"] = '';
        $_SESSION["jsonsalida"]["fechareactivacioncalidadcomerciante"] = '';
        
            
        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("matricula", true);
        $api->validarParametro("ambiente", false);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('retornarFechasMercantil', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Abre conexión con la BD
        // ********************************************************************** // 
        if (!isset($_SESSION["entrada"]["ambiente"])) {
            $_SESSION["entrada"]["ambiente"] = '';
        }
        if ($_SESSION["entrada"]["ambiente"] == '' || $_SESSION["entrada"]["ambiente"] == 'DEF' || $_SESSION["entrada"]["ambiente"] == 'A') {
            $mysqli = conexionMysqliApi();
        }
        if ($_SESSION["entrada"]["ambiente"] == 'PRD' || $_SESSION["entrada"]["ambiente"] == 'P') {
            $mysqli = conexionMysqliApi('P-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if ($_SESSION["entrada"]["ambiente"] == 'DES' || $_SESSION["entrada"]["ambiente"] == 'D') {
            $mysqli = conexionMysqliApi('D-' . $_SESSION["generales"]["codigoempresa"]);
        }

        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        // $arrTem = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["matricula"]);
        $arrTem = \funcionesRegistrales::retornarExpedienteMercantilCorto($mysqli, $_SESSION["entrada"]["matricula"]);
          
        //
        if ($arrTem === false || $arrTem == 0) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente no localizado en la BD.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        // **************************************************************************** //
        // Cerrar conexión a la BD
        // **************************************************************************** //        
        $mysqli->close();
        
        $_SESSION["jsonsalida"]["nombre"] = $arrTem["nombre"];
        $_SESSION["jsonsalida"]["estadomatricula"] = $arrTem["estadomatricula"];
        $_SESSION["jsonsalida"]["fechamatricula"] = $arrTem["fechamatricula"];
        $_SESSION["jsonsalida"]["fecharenovacion"] = $arrTem["fecharenovacion"];
        $_SESSION["jsonsalida"]["ultanoren"] = $arrTem["ultanoren"];
        $_SESSION["jsonsalida"]["fechacancelacionn"] = $arrTem["fechacancelacion"];
        $_SESSION["jsonsalida"]["fechavencimiento"] = $arrTem["fechavencimiento"];
        $_SESSION["jsonsalida"]["estadisuelta"] = $arrTem["estadisuelta"];
        $_SESSION["jsonsalida"]["motivocancelacion"] = $arrTem["motivocancelacion"];
        $_SESSION["jsonsalida"]["descripcionmotivocancelacion"] = $arrTem["descripcionmotivocancelacion"];
        $_SESSION["jsonsalida"]["fechaliquidacion"] = $arrTem["fechaliquidacion"];
        $_SESSION["jsonsalida"]["disueltaporvencimiento"] = $arrTem["disueltaporvencimiento"];
        $_SESSION["jsonsalida"]["disueltaporacto510"] = $arrTem["disueltaporacto510"];
        $_SESSION["jsonsalida"]["fechaacto510"] = $arrTem["fechaacto510"];
        $_SESSION["jsonsalida"]["fechaacto511"] = $arrTem["fechaacto511"];
        $_SESSION["jsonsalida"]["perdidacalidadcomerciante"] = $arrTem["perdidacalidadcomerciante"];
        $_SESSION["jsonsalida"]["fechaperdidacalidadcomerciante"] = $arrTem["fechaperdidacalidadcomerciante"];
        $_SESSION["jsonsalida"]["fechareactivacioncalidadcomerciante"] = $arrTem["fechareactivacioncalidadcomerciante"];
        
        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
