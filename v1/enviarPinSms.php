<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait enviarPinSms {

    /**
     * 
     * @param API $api
     * Recibe:
     * - tipo : SFDC: Sms para firma de documento
     *          SFDT: Sms para firma de trámite
     * - idliquidacion: Número de liquidación
     * - celular: Número del celular
     * - email: Email del firmante
     * - identificacion: Identificación del firmante
     * - nombre: Nombre del firmante
     * 
     */
    public function enviarPinSms(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');

        //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["tipo"] = ''; // SFDC .- Firma documento, SFDT.- Firma trámite
        $_SESSION["jsonsalida"]["idliquidacion"] = '';
        $_SESSION["jsonsalida"]["prefijo"] = '';
        $_SESSION["jsonsalida"]["celular"] = '';
        $_SESSION["jsonsalida"]["email"] = '';
        $_SESSION["jsonsalida"]["identificacion"] = '';
        $_SESSION["jsonsalida"]["nombre"] = '';
        $_SESSION["jsonsalida"]["mensaje"] = '';
        $_SESSION["jsonsalida"]["pin"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida datos de entrada
        // ********************************************************************** //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("tipo", true);
        $api->validarParametro("celular", true);
        if ($_SESSION["entrada"]["tipo"] != 'PRUEBA') {
            $api->validarParametro("idliquidacion", true);
            $api->validarParametro("email", true);
            $api->validarParametro("identificacion", true);
            $api->validarParametro("nombre", true);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('enviarPinSms', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida tipo de sms a enviar
        // ********************************************************************** //
        if ($_SESSION["entrada"]["tipo"] != 'SFDC' && $_SESSION["entrada"]["tipo"] != 'SFDT' && $_SESSION["entrada"]["tipo"] != 'PRUEBA') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error en tipo de SMS a generar';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida prefijo
        // ********************************************************************** //
        if (!isset($_SESSION["entrada"]["prefijo"]) || $_SESSION["entrada"]["prefijo"] == '') {
            $_SESSION["entrada"]["prefijo"] = '57';
        }

        // ********************************************************************** //
        // Valida celular
        // ********************************************************************** //
        if ($_SESSION["entrada"]["prefijo"] == '57') {
            if (strlen($_SESSION["entrada"]["celular"]) != 10 && substr($_SESSION["entrada"]["celular"]) != '3') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Error en el número del celular';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ********************************************************************** //
        // Conexión con BD
        // ********************************************************************** //
        $mysqli = conexionMysqliApi();
        
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        //
        if ($_SESSION["entrada"]["tipo"] == 'PRUEBA') {
            $pin = \funcionesGenerales::generarAleatorioNumerico($mysqli);
            $mensaje = 'Codigo de prueba No. ' . $pin;
            $res = \funcionesGenerales::enviarSms($mysqli, $_SESSION["entrada"]["prefijo"], $_SESSION["entrada"]["celular"], $mensaje, 'Pin de firmado a trav´s del API - enviarPinSms');
            if ($res["codigoError"] != '0000') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible enviar el SMS con el PIN de firmado.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "0000";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Enviado correctamente.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);                
            }
        }

        
        // ********************************************************************** //
        // Localiza y valida liquidacion
        // Siempre y cuando el pin a generar sea para firmado
        // ********************************************************************** //  
        $liq = false;
        if ($_SESSION["entrada"]["tipo"] == 'SFDC' || $_SESSION["entrada"]["tipo"] == 'SFDT') {
            $liq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);
            if ($liq === false || empty($liq)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if ($liq["idestado"] > '05' && $liq["idestado"] != '10') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Estado de la liquidación no permite generar SMS (' . $liq["idestado"] . ')';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }


        // ********************************************************************** //
        // Generar pin
        // ******************************************************************* //
        $pin = \funcionesGenerales::generarAleatorioNumerico($mysqli);
        $mensaje = '';
        if ($_SESSION["entrada"]["tipo"] == 'SFDC') {
            $mensaje = 'Utilice el PIN ' . $pin . ' para el firmado electronico del documento asociado al tramite No. ' . $liq["idliquidacion"];
        }
        if ($_SESSION["entrada"]["tipo"] == 'SFDT') {
            $mensaje = 'Utilice el PIN ' . $pin . ' para el firmado electronico del tramite No. ' . $liq["idliquidacion"];
        }
        $res = \funcionesGenerales::enviarSms($mysqli, $_SESSION["entrada"]["prefijo"], $_SESSION["entrada"]["celular"], $mensaje, 'Pin de firmado a trav´s del API - enviarPinSms');
        if ($res["codigoError"] != '0000') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible enviar el SMS con el PIN de firmado.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Crea sms_firmados
        // ********************************************************************** // 
        $arrCampos = array(
            'idliquidacion',
            'tiposms',
            'pkgsms',
            'email',
            'celular',
            'identificacion',
            'tipotramite',
            'nombre',
            'fecha',
            'hora',
            'estado',
            'contenido'
        );

        if (ltrim(trim($_SESSION["entrada"]["idliquidacion"])) == '') {
            $_SESSION["entrada"]["idliquidacion"] = 0;
        }
        $tipotramite = '';
        if ($liq) {
            $tipotramite = $liq["tipotramite"];
        }

        $arrValores = array(
            $_SESSION["entrada"]["idliquidacion"],
            "'" . $_SESSION["entrada"]["tipo"] . "'",
            "'" . $pin . "'",
            "'" . $_SESSION["entrada"]["email"] . "'",
            "'" . $_SESSION["entrada"]["prefijo"] . $_SESSION["entrada"]["celular"] . "'",
            "'" . $_SESSION["entrada"]["identificacion"] . "'",
            "'" . $tipotramite . "'",
            "'" . addslashes($_SESSION["entrada"]["nombre"]) . "'",
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'PE'",
            "'" . addslashes($mensaje) . "'"
        );
        insertarRegistrosMysqliApi($mysqli, 'sms_firmados', $arrCampos, $arrValores);

        // ********************************************************************** //
        // Generar respuesta
        // ********************************************************************** // 
        $_SESSION["jsonsalida"]["idliquidacion"] = $_SESSION["entrada"]["idliquidacion"];
        $_SESSION["jsonsalida"]["tipo"] = $_SESSION["entrada"]["tipo"];
        $_SESSION["jsonsalida"]["prefijo"] = $_SESSION["entrada"]["prefijo"];
        $_SESSION["jsonsalida"]["celular"] = $_SESSION["entrada"]["celular"];
        $_SESSION["jsonsalida"]["email"] = $_SESSION["entrada"]["email"];
        $_SESSION["jsonsalida"]["identificacion"] = $_SESSION["entrada"]["identificacion"];
        $_SESSION["jsonsalida"]["nombre"] = $_SESSION["entrada"]["nombre"];
        $_SESSION["jsonsalida"]["pin"] = $pin;
        $_SESSION["jsonsalida"]["mensaje"] = $mensaje;

        // ********************************************************************** //
        // Cerrar conexión con la BD
        // ********************************************************************** // 
        $mysqli->close();

        // ********************************************************************** //
        // Entregar resultado
        // ********************************************************************** // 
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
