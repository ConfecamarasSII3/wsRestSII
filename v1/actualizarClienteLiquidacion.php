<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait actualizarClienteLiquidacion
{

    public function actualizarClienteLiquidacion(API $api)
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
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idliquidacion", true);
        $api->validarParametro("tipoidentificacioncliente", true);
        $api->validarParametro("identificacioncliente", true);
        if ($_SESSION["entrada"]["tipoidentificacioncliente"] != "2") {
            $api->validarParametro("nombre1cliente", true);
            $api->validarParametro("nombre2cliente", false);
            $api->validarParametro("apellido1cliente", true);
            $api->validarParametro("apellido2cliente", false);
        }
        if ($_SESSION["entrada"]["tipoidentificacioncliente"] == "2") {
            $api->validarParametro("razonsocialcliente", true);
        }

        $api->validarParametro("direccioncliente", true);
        $api->validarParametro("telefonocliente", true);
        $api->validarParametro("celularcliente", true);
        $api->validarParametro("municipiocliente", true);
        $api->validarParametro("emailcliente", true);
        $api->validarParametro("paiscliente", false, false);
        $api->validarParametro("lenguajecliente", false, false);
        $api->validarParametro("codigoregimencliente", false, false);
        $api->validarParametro("codigoimpuestocliente", false, false);
        $api->validarParametro("nombreimpuestocliente", false, false);
        $api->validarParametro("responsabilidadfiscalcliente", false, false);
        $api->validarParametro("responsabilidadtributariacliente", false, false);
        //
        if (!filter_var($_SESSION["entrada"]["emailcliente"], FILTER_VALIDATE_EMAIL) === true) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó un correo válido';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('actualizarClienteLiquidacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        //
        $mysqli = conexionMysqliApi();
        //
        $nombresCliente = $_SESSION["entrada"]["nombre1cliente"] . " " . $_SESSION["entrada"]["nombre2cliente"];
        $apellidosCliente = $_SESSION["entrada"]["apellido1cliente"] . " " . $_SESSION["entrada"]["apellido2cliente"];

        //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"]);
        if ($_SESSION["tramite"] === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no encontrada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["tramite"]["estado"] > '06') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El estado de la liquidación no permite que esta sea modificada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //Datos del cliente
        $_SESSION["tramite"]["idtipoidentificacioncliente"] = $_SESSION["entrada"]["tipoidentificacioncliente"];
        $_SESSION["tramite"]["identificacioncliente"] = $_SESSION["entrada"]["identificacioncliente"];

        //
        if ($_SESSION["entrada"]["tipoidentificacioncliente"] != "2") {
            $_SESSION["tramite"]["nombrecliente"] = $nombresCliente;
            $_SESSION["tramite"]["apellidocliente"] = $apellidosCliente;
            $_SESSION["tramite"]["nombre1cliente"] = $_SESSION["entrada"]["nombre1cliente"];
            $_SESSION["tramite"]["nombre2cliente"] = $_SESSION["entrada"]["nombre2cliente"];
            $_SESSION["tramite"]["apellido1cliente"] = $_SESSION["entrada"]["apellido1cliente"];
            $_SESSION["tramite"]["apellido2cliente"] = $_SESSION["entrada"]["apellido2cliente"];
        }
        if ($_SESSION["entrada"]["tipoidentificacioncliente"] == "2") {
            $_SESSION["tramite"]["nombrecliente"] = $_SESSION["entrada"]["razonsocialcliente"];
        }
        $_SESSION["tramite"]["email"] = $_SESSION["entrada"]["emailcliente"];
        $_SESSION["tramite"]["direccion"] = $_SESSION["entrada"]["direccioncliente"];
        $_SESSION["tramite"]["idmunicipio"] = sprintf("%05s", $_SESSION["entrada"]["municipiocliente"]);
        $_SESSION["tramite"]["telefono"] = $_SESSION["entrada"]["telefonocliente"];
        $_SESSION["tramite"]["movil"] = $_SESSION["entrada"]["celularcliente"];

        $_SESSION["tramite"]["pais"] = $_SESSION["entrada"]["paiscliente"];
        $_SESSION["tramite"]["lenguaje"] = $_SESSION["entrada"]["lenguajecliente"];
        $_SESSION["tramite"]["codigoregimen"] = $_SESSION["entrada"]["codigoregimencliente"];
        $_SESSION["tramite"]["codigoimpuesto"] = $_SESSION["entrada"]["codigoimpuestocliente"];
        $_SESSION["tramite"]["nombreimpuesto"] = $_SESSION["entrada"]["nombreimpuestocliente"];
        $_SESSION["tramite"]["responsabilidadfiscal"] = $_SESSION["entrada"]["responsabilidadfiscalcliente"];
        $_SESSION["tramite"]["responsabilidadtributaria"] = $_SESSION["entrada"]["responsabilidadtributariacliente"];

        //Datos del Pagador

        $_SESSION["tramite"]["tipoidentificacionpagador"] = $_SESSION["tramite"]["idtipoidentificacioncliente"];
        $_SESSION["tramite"]["identificacionpagador"] = $_SESSION["tramite"]["identificacioncliente"];
        $_SESSION["tramite"]["nombrepagador"] = $_SESSION["tramite"]["nombrecliente"];
        $_SESSION["tramite"]["apellidopagador"] = $_SESSION["tramite"]["apellidocliente"];
        $_SESSION["tramite"]["direccionpagador"] = $_SESSION["tramite"]["direccion"];
        $_SESSION["tramite"]["telefonopagador"] = $_SESSION["tramite"]["telefono"];
        $_SESSION["tramite"]["movilpagador"] = $_SESSION["tramite"]["movil"];
        $_SESSION["tramite"]["municipiopagador"] = sprintf("%05s", $_SESSION["tramite"]["idmunicipio"]);
        $_SESSION["tramite"]["emailpagador"] = $_SESSION["tramite"]["email"];

        \funcionesRegistrales::grabarLiquidacionMreg($mysqli);

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
