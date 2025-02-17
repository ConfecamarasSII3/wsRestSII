<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait reportarTransaccion {

    public function reportarTransaccion(API $api) {

        require_once ('myErrorHandler.php');
        require_once ('mysqli.php');
        require_once ('generales.php');
        require_once ('LogSii2.class.php');
        require_once ('funcionesSii2.php');
        require_once ('funcionesSii2_desserializaciones.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["idliquidacion"] = '';
        $_SESSION["jsonsalida"]["numerorecuperacion"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("codificacionservicios", true);
        $api->validarParametro("tipoidentificacioncliente", true);
        $api->validarParametro("identificacioncliente", true);
        if ($_SESSION["entrada"]["tipoidentificacioncliente"] == "1") {
            $api->validarParametro("nombre1cliente", true);
            $api->validarParametro("nombre2cliente", false);
            $api->validarParametro("apellido1cliente", true);
            $api->validarParametro("apellido2cliente", false);
        }
        if ($_SESSION["entrada"]["identificacioncliente"] == "2") {
            $api->validarParametro("razonsocialcliente", true);
        }

        if (!filter_var($_SESSION["entrada"]["emailcliente"], FILTER_VALIDATE_EMAIL) === true) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó un correo válido';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("direccioncliente", true);
        $api->validarParametro("telefonocliente", true);
        $api->validarParametro("celularcliente", true);
        $api->validarParametro("municipiocliente", true);
        $api->validarParametro("emailcliente", true);
        $api->validarParametro("operador", true);
        $api->validarParametro("valoriva", false, false);
        $api->validarParametro("valortotal", false, false);
        $api->validarParametro("tipotramite", true);
        $api->validarParametro("subtipotramite", false, false);
        $api->validarParametro("proyecto", true);

        $_SESSION["entrada"]["operador"] = mb_strtoupper(trim($_SESSION["entrada"]["operador"]), 'utf-8');

        if ($_SESSION["entrada"]["operador"] == 'USUPUBXX') {
            $api->validarParametro("emailcontrol", true);
            $api->validarParametro("identificacioncontrol", true);
            $api->validarParametro("nombrecontrol", true);
            $api->validarParametro("celularcontrol", true);
            $_SESSION["entrada"]["sedeusuario"] = '99';
        }

        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME);
        if ($mysqli->connect_error) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["operador"] != 'USUPUBXX') {
            $usux = retornarRegistroMysqli2($mysqli, 'usuarios', "idusuario='" . $_SESSION["entrada"]["operador"] . "'");
            if ($usux === false || empty($usux)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario/operador no localizado en la BD';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if ($usux["eliminado"] == 'SI') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario/operador no existe en la BD';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if ($usux["escajero"] != 'SI') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario/operador no es cajero';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (ltrim(trim($usux["fechaactivacion"]), "0") == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario/operador no se encuentra activado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (ltrim(trim($usux["fechainactivacion"]), "0") != '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario/operador se encuentra inactivo';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $_SESSION["entrada"]["emailcontrol"] = $usux["email"];
            $_SESSION["entrada"]["nombrecontrol"] = $usux["nombreusuario"];
            $_SESSION["entrada"]["identificacioncontrol"] = $usux["identificacion"];
            $_SESSION["entrada"]["celularcontrol"] = $usux["celular"];
            $_SESSION["entrada"]["sedeusuario"] = $usux["idsede"];
            if (ltrim(trim($_SESSION["entrada"]["sedeusuario"]), "0") == '') {
                $_SESSION["entrada"]["sedeusuario"] = '99';
            }
        }

        if ($_SESSION["entrada"]["valortotal"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El valortotal no puede estar vacío.(*)';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["valortotal"] < 0) {
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El valortotal no puede ser negativo.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["valoriva"] < 0) {
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El valoriva no puede ser negativo.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (count($_SESSION["entrada"]["servicios"]) == 0) {
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se recibieron servicios.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('reportarTransaccion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $nombresCliente = $_SESSION["entrada"]["nombre1cliente"] . " " . $_SESSION["entrada"]["nombre2cliente"];
        $apellidosCliente = $_SESSION["entrada"]["apellido1cliente"] . " " . $_SESSION["entrada"]["apellido2cliente"];

        //
        $_SESSION["tramite"] = array();
        $_SESSION["tramite"] = \funcionesSii2::retornarMregLiquidacionSii($mysqli, 0, 'VC');

        //
        $totalservicios = 0;
        $matbase = '';
        $probase = '';
        $exp = false;

        if ($_SESSION["entrada"]["codificacionservicios"] == "R") {

            $homologacion = \funcionesGenerales::homologacion_formatos_codificacionSII($mysqli, $_SESSION["entrada"]["servicios"]);
            if ($homologacion > 0) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = '9999';
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron los códigos RUES o sus homologaciones. El servicio no encontrado es: ' . $homologacion;
                \logSii2::general2('api_' . __FUNCTION__, $_SESSION["entrada"]["usuariows"], $_SESSION["jsonsalida"]["mensajeerror"]);
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {
                $i = 0;
                foreach ($_SESSION["entrada"]["servicios"] as $servicio) {
                    $i++;
                    $_SESSION["tramite"]["rues_servicios"][$i]["codigo_servicio"] = $servicio["idservicio"];
                    $_SESSION["tramite"]["rues_servicios"][$i]["descripcion_servicio"] = '';
                    $_SESSION["tramite"]["rues_servicios"][$i]["orden_servicio"] = 1;
                    $_SESSION["tramite"]["rues_servicios"][$i]["orden_servicio_asociado"] = '';
                    $_SESSION["tramite"]["rues_servicios"][$i]["nombre_base"] = '';
                    $_SESSION["tramite"]["rues_servicios"][$i]["valor_base"] = $servicio["base"];
                    $_SESSION["tramite"]["rues_servicios"][$i]["valor_liquidacion"] = $servicio["valorservicio"];
                    $_SESSION["tramite"]["rues_servicios"][$i]["cantidad_servicio"] = $servicio["cantidad"];
                    $_SESSION["tramite"]["rues_servicios"][$i]["indicador_base"] = '';
                    $_SESSION["tramite"]["rues_servicios"][$i]["indicador_renovacion"] = '';
                    $_SESSION["tramite"]["rues_servicios"][$i]["matricula_servicio"] = $servicio["matricula"];
                    $_SESSION["tramite"]["rues_servicios"][$i]["nombre_matriculado"] = '';
                    $_SESSION["tramite"]["rues_servicios"][$i]["ano_renovacion"] = '';
                    $_SESSION["tramite"]["rues_servicios"][$i]["valor_activos_sin_ajustes"] = '';

                    $codigoHomologacion = retornarRegistroMysqli2($mysqli, 'mreg_homologaciones_rue', "trim(id_tabla) like '01' and trim(cod_rue) like '" . $servicio["idservicio"] . "'", "cod_cc");
                    $servLocal = retornarRegistroMysqli2($mysqli, 'mreg_servicios', "idservicio like '" . $codigoHomologacion . "'");

                    $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = '';
                    if (trim($servicio["matricula"]) != '') {
                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $servicio["matricula"];
                    }
                    if (trim($servicio["proponente"]) != '') {
                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $servicio["proponente"];
                    }
                    $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["ano"] = $servicio["anobase"];
                    $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = $servicio["cantidad"];
                    $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = $servicio["base"];
                    $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = $servicio["porcentaje"];
                    $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = $servicio["valorservicio"];
                    $_SESSION["tramite"]["liquidacion"][$i]["benart7"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["reliquidacion"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["serviciobase"] = 'S';
                    $_SESSION["tramite"]["liquidacion"][$i]["pagoafiliacion"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["ir"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["iva"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["idalerta"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["idsecuencia"] = $i;
                    $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = $servLocal['idservicio'];
                    if ($matbase == '') {
                        $matbase = $servicio["matricula"];
                    }
                    if ($probase == '') {
                        $probase = $servicio["proponente"];
                    }                    
                    if ($servicio["matricula"] != '') {
                        $exp = retornarRegistroMysqli2($mysqli, 'mreg_est_inscritos', "matricula='" . $servicio["matricula"] . "'");
                        if ($exp && !empty($exp)) {
                            $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $exp["razonsocial"];
                        }
                    }
                    if ($servicio["proponente"] != '') {
                        $exp = retornarRegistroMysqli2($mysqli, 'mreg_est_inscritos', "proponente='" . $servicio["proponente"] . "'");
                        if ($exp && !empty($exp)) {
                            $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $exp["razonsocial"];
                        }
                    }
                    $totalservicios = $totalservicios + $servicio["valorservicio"];
                }
            }
        } else {
            $i = 0;
            foreach ($_SESSION["entrada"]["servicios"] as $servicio) {
                $i++;
                $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $servicio["matricula"];
                $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = '';
                $_SESSION["tramite"]["liquidacion"][$i]["ano"] = $servicio["anobase"];
                $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = $servicio["cantidad"];
                $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = $servicio["base"];
                $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = $servicio["porcentaje"];
                $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = $servicio["valorservicio"];
                $_SESSION["tramite"]["liquidacion"][$i]["benart7"] = '';
                $_SESSION["tramite"]["liquidacion"][$i]["reliquidacion"] = '';
                $_SESSION["tramite"]["liquidacion"][$i]["serviciobase"] = 'S';
                $_SESSION["tramite"]["liquidacion"][$i]["pagoafiliacion"] = '';
                $_SESSION["tramite"]["liquidacion"][$i]["ir"] = '';
                $_SESSION["tramite"]["liquidacion"][$i]["iva"] = '';
                $_SESSION["tramite"]["liquidacion"][$i]["idalerta"] = '';
                $_SESSION["tramite"]["liquidacion"][$i]["idsecuencia"] = $i;
                $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = $servicio["idservicio"];
                if ($matbase == '') {
                    $matbase = $servicio["matricula"];
                }
                if ($probase == '') {
                    $probase = $servicio["proponente"];
                }
                if ($servicio["matricula"] != '') {
                    $exp = retornarRegistroMysqli2($mysqli, 'mreg_est_inscritos', "matricula='" . $servicio["matricula"] . "'");
                    if ($exp && !empty($exp)) {
                        $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $exp["razonsocial"];
                    }
                }
                if ($servicio["proponente"] != '') {
                    $exp = retornarRegistroMysqli2($mysqli, 'mreg_est_inscritos', "proponente='" . $servicio["proponente"] . "'");
                    if ($exp && !empty($exp)) {
                        $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $exp["razonsocial"];
                    }
                }

                $totalservicios = $totalservicios + $servicio["valorservicio"];
            }
        }

        //
        if ($totalservicios != $_SESSION["entrada"]["valortotal"]) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El valor total de la transacción no concuerda con la sumatoria de servicios';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //

        $_SESSION["tramite"]["numeroliquidacion"] = retornarSecuenciaMysqli2($mysqli, 'LIQUIDACION-REGISTROS');
        $_SESSION["tramite"]["idliquidacion"] = $_SESSION["tramite"]["numeroliquidacion"];
        $_SESSION["tramite"]["fecha"] = date("Ymd");
        $_SESSION["tramite"]["hora"] = date("His");
        $_SESSION["tramite"]["fechaultimamodificacion"] = date("His");
        $_SESSION["tramite"]["idusuario"] = $_SESSION["entrada"]["operador"];
        $_SESSION["tramite"]["tipotramite"] = $_SESSION["entrada"]["tipotramite"];
        $_SESSION["tramite"]["subtipotramite"] = $_SESSION["entrada"]["subtipotramite"];
        $_SESSION["tramite"]["iptramite"] = localizarIPSii2();
        $_SESSION["tramite"]["numerorecuperacion"] = \funcionesSii2::asignarNumeroRecuperacionSii($mysqli, 'mreg');
        $_SESSION["tramite"]["idestado"] = '05';
        $_SESSION["tramite"]["sedeusuario"] = $_SESSION["entrada"]["sedeusuario"];
        $_SESSION["tramite"]["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];

        //Datos del cliente
        $_SESSION["tramite"]["idtipoidentificacioncliente"] = $_SESSION["entrada"]["tipoidentificacioncliente"];
        $_SESSION["tramite"]["identificacioncliente"] = $_SESSION["entrada"]["identificacioncliente"];

        if ($_SESSION["entrada"]["tipoidentificacioncliente"] == "1") {
            $_SESSION["tramite"]["nombrecliente"] = $nombresCliente;
            $_SESSION["tramite"]["apellidocliente"] = $apellidosCliente;
        }
        if ($_SESSION["entrada"]["tipoidentificacioncliente"] == "2") {
            $_SESSION["tramite"]["nombrecliente"] = $_SESSION["entrada"]["razonsocialcliente"];
        }
        $_SESSION["tramite"]["email"] = $_SESSION["entrada"]["emailcliente"];
        $_SESSION["tramite"]["direccion"] = $_SESSION["entrada"]["direccioncliente"];
        $_SESSION["tramite"]["idmunicipio"] = $_SESSION["entrada"]["municipiocliente"];
        $_SESSION["tramite"]["telefono"] = $_SESSION["entrada"]["telefonocliente"];
        $_SESSION["tramite"]["movil"] = $_SESSION["entrada"]["celularcliente"];

        //Datos del Pagador

        $_SESSION["tramite"]["tipoidentificacionpagador"] = '';
        $_SESSION["tramite"]["identificacionpagador"] = '';
        $_SESSION["tramite"]["nombrepagador"] = '';
        $_SESSION["tramite"]["apellidopagador"] = '';
        $_SESSION["tramite"]["direccionpagador"] = '';
        $_SESSION["tramite"]["telefonopagador"] = '';
        $_SESSION["tramite"]["municipiopagador"] = '';
        $_SESSION["tramite"]["emailpagador"] = '';

        //Datos basicos del expediente afectado
        $_SESSION["tramite"]["idexpedientebase"] = '';
        $_SESSION["tramite"]["tipoidentificacionbase"] = '';
        $_SESSION["tramite"]["identificacionbase"] = '';

        //
        if ($_SESSION["entrada"]["tipoidentificacioncliente"] == "1") {
            $_SESSION["tramite"]["nombrebase"] = $apellidosCliente . ' ' . $nombresCliente;
        }
        if ($_SESSION["entrada"]["tipoidentificacioncliente"] == "2") {
            $_SESSION["tramite"]["nombrebase"] = $_SESSION["entrada"]["razonsocialcliente"];
        }

        $_SESSION["tramite"]["organizacionbase"] = '';
        $_SESSION["tramite"]["categoriabase"] = '';
        $_SESSION["tramite"]["matriculabase"] = '';
        $_SESSION["tramite"]["proponentebase"] = '';
        $_SESSION["tramite"]["actividadesbase"] = '';

        //Valores totales de la liquidacion
        if (!isset($_SESSION["entrada"]["valorbruto"])) {
            $_SESSION["entrada"]["valorbruto"] = 0;
        }
        if (!isset($_SESSION["entrada"]["valorbaseiva"])) {
            $_SESSION["entrada"]["valorbaseiva"] = 0;
        }
        if (!isset($_SESSION["entrada"]["valoriva"]) || $_SESSION["entrada"]["valoriva"] == '') {
            $_SESSION["entrada"]["valoriva"] = 0;
        }
        $_SESSION["tramite"]["valorbruto"] = $_SESSION["entrada"]["valorbruto"];
        $_SESSION["tramite"]["valorbaseiva"] = $_SESSION["entrada"]["valorbaseiva"];
        $_SESSION["tramite"]["valoriva"] = $_SESSION["entrada"]["valoriva"];
        $_SESSION["tramite"]["valortotal"] = $_SESSION["entrada"]["valortotal"];
        $_SESSION["tramite"]["idsolicitudpago"] = 0;

        //Datos del pago
        $_SESSION["tramite"]["pagoefectivo"] = 0;
        $_SESSION["tramite"]["pagoconsignacion"] = 0;
        $_SESSION["tramite"]["pagocheque"] = 0;
        $_SESSION["tramite"]["pagovisa"] = 0;
        $_SESSION["tramite"]["pagoach"] = 0;
        $_SESSION["tramite"]["pagomastercard"] = 0;
        $_SESSION["tramite"]["pagoamerican"] = 0;
        $_SESSION["tramite"]["pagocredencial"] = 0;
        $_SESSION["tramite"]["pagodiners"] = 0;
        $_SESSION["tramite"]["pagotdebito"] = 0;
        $_SESSION["tramite"]["idformapago"] = '';
        $_SESSION["tramite"]["numerorecibo"] = '';
        $_SESSION["tramite"]["numerooperacion"] = '';
        $_SESSION["tramite"]["fecharecibo"] = '';
        $_SESSION["tramite"]["horarecibo"] = '';
        $_SESSION["tramite"]["idfranquicia"] = '';
        $_SESSION["tramite"]["nombrefranquicia"] = '';
        $_SESSION["tramite"]["numeroautorizacion"] = '';
        $_SESSION["tramite"]["idcodban"] = '';
        $_SESSION["tramite"]["nombrebanco"] = '';
        $_SESSION["tramite"]["numerocheque"] = '';

        //Definicion de variable de tramite RUES
        $_SESSION["tramite"]["rues_numerointerno"] = '';
        $_SESSION["tramite"]["rues_numerounico"] = '';
        $_SESSION["tramite"]["rues_camarareceptora"] = '';
        $_SESSION["tramite"]["rues_camararesponsable"] = '';
        $_SESSION["tramite"]["rues_matricula"] = '';
        $_SESSION["tramite"]["rues_proponente"] = '';
        $_SESSION["tramite"]["rues_nombreregistrado"] = '';
        $_SESSION["tramite"]["rues_claseidentificacion "] = '';
        $_SESSION["tramite"]["rues_numeroidentificacion"] = '';
        $_SESSION["tramite"]["rues_dv"] = '';
        $_SESSION["tramite"]["rues_estado_liquidacion"] = "";
        $_SESSION["tramite"]["rues_estado_transaccion"] = "";
        $_SESSION["tramite"]["rues_nombrepagador"] = "";
        $_SESSION["tramite"]["rues_origendocumento"] = '';
        $_SESSION["tramite"]["rues_fechadocumento"] = '';
        $_SESSION["tramite"]["rues_fechapago"] = "";
        $_SESSION["tramite"]["rues_numerofactura"] = "";
        $_SESSION["tramite"]["rues_referenciaoperacion"] = "";
        $_SESSION["tramite"]["rues_totalpagado"] = 0;
        $_SESSION["tramite"]["rues_formapago"] = "";
        $_SESSION["tramite"]["rues_indicadororigen"] = 'N';
        $_SESSION["tramite"]["rues_empleados"] = 0;
        $_SESSION["tramite"]["rues_indicadorbeneficio"] = 0;
        $_SESSION["tramite"]["rues_fecharespuesta"] = '';
        $_SESSION["tramite"]["rues_horarespuesta"] = '';
        $_SESSION["tramite"]["rues_codigoerror"] = '';
        $_SESSION["tramite"]["rues_mensajeerror"] = '';
        $_SESSION["tramite"]["rues_firmadigital"] = '';

        //
        if ($matbase != '') {
            $exp = retornarRegistroMysqli2($mysqli, 'mreg_est_inscritos', "matricula='" . $matbase . "'");
            if ($exp && !empty($exp)) {
                $_SESSION["tramite"]["idexpedientebase"] = $matbase;
                $_SESSION["tramite"]["idmatriculabase"] = $matbase;
                $_SESSION["tramite"]["matriculabase"] = $matbase;
                $_SESSION["tramite"]["organizacionbase"] = $exp["organizacion"];
                $_SESSION["tramite"]["categoriabase"] = $exp["categoria"];
                $_SESSION["tramite"]["tipoidentificacionbase"] = $exp["idclase"];
                $_SESSION["tramite"]["identificacionbase"] = $exp["numid"];
                $_SESSION["tramite"]["nombrebase"] = $exp["razonsocial"];
            }
        }
        if ($probase != '') {
            $exp = retornarRegistroMysqli2($mysqli, 'mreg_est_inscritos', "proponente='" . $probase . "'");
            if ($exp && !empty($exp)) {
                $_SESSION["tramite"]["idexpedientebase"] = $probase;
                $_SESSION["tramite"]["idmatriculabase"] = $probase;
                $_SESSION["tramite"]["matriculabase"] = $probase;
                $_SESSION["tramite"]["organizacionbase"] = $exp["organizacion"];
                $_SESSION["tramite"]["categoriabase"] = $exp["categoria"];
                $_SESSION["tramite"]["tipoidentificacionbase"] = $exp["idclase"];
                $_SESSION["tramite"]["identificacionbase"] = $exp["numid"];
                $_SESSION["tramite"]["nombrebase"] = $exp["razonsocial"];
            }
        }

        // grabarLiquidacionMreg();
        $result = \funcionesSii2::grabarLiquidacionMregSii($mysqli);
        if ($result === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        if ($_SESSION["tramite"]["numerorecuperacion"] != "") {
            $_SESSION["jsonsalida"]["idliquidacion"] = $_SESSION["tramite"]["numeroliquidacion"];
            $_SESSION["jsonsalida"]["numerorecuperacion"] = $_SESSION["tramite"]["numerorecuperacion"];
        }

        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logSii2::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
