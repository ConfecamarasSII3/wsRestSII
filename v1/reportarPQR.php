<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait reportarPQR {

    public function reportarPQR(API $api) {
        //
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
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
        $api->validarParametro("codigoempresa", true);
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);

        $api->validarParametro("tipoidentificacioncliente", true);
        $api->validarParametro("identificacioncliente", true);
        $api->validarParametro("razonsocialcliente", true);
        $api->validarParametro("nombre1cliente", true);
        $api->validarParametro("nombre2cliente", true);
        $api->validarParametro("apellido1cliente", true);
        $api->validarParametro("apellido2cliente", true);
        $api->validarParametro("emailcliente", true);
        $api->validarParametro("direccioncliente", true);
        $api->validarParametro("telefonocliente", true);
        $api->validarParametro("celularcliente", true);
        $api->validarParametro("municipiocliente", true);
        $api->validarParametro("operador", true);
        $api->validarParametro("matricula", true);
        $api->validarParametro("proponente", true);
        $api->validarParametro("detalle", true);

        $i = 0;
        foreach ($_SESSION["entrada"]["anexos"] as $anexo) {
            $i++;
            $_SESSION["entrada"][$i]["tipoanexo"] = $anexo->tipoanexo;
            $_SESSION["entrada"][$i]["contenido"] = $anexo->contenido;
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('reportarPQR', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        // **************************************************************************** //
        // Crea el log
        // **************************************************************************** //
//        \log::general2('api_reportarPQR_' . date("Ymd"), __FUNCTION__, 'Inicia proceso de reporte de PQR. Recibe: ' . json_encode($_SESSION["entrada"]));
        // ********************************************************************** //
        // rear el reporte de PQR
        // ********************************************************************** //         
        $arrTemD = "";


        // **************************************************************************** //
        // Retorna los detalles de PQR
        // **************************************************************************** //

        $_SESSION['jsonsalida']['numerorecibo'] = $arrTemD["numerorecibo"];
        $_SESSION['jsonsalida']['numerooperacion'] = $arrTemD["numerooperacion"];
        $_SESSION['jsonsalida']['radicado'] = $arrTemD["radicado"];
        $_SESSION['jsonsalida']['fecharecibo'] = $arrTemD["fecharecibo"];
        $_SESSION['jsonsalida']['horarecibo'] = $arrTemD["horarecibo"];

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
