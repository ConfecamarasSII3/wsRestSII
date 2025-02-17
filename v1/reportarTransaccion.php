<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait reportarTransaccion {

    public function reportarTransaccion(API $api) {

        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/gestionRecibos.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
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
        if ($_SESSION["entrada"]["tipoidentificacioncliente"] != "2") {
            $api->validarParametro("nombre1cliente", true);
            $api->validarParametro("nombre2cliente", false);
            $api->validarParametro("apellido1cliente", true);
            $api->validarParametro("apellido2cliente", false);
        }
        if ($_SESSION["entrada"]["tipoidentificacioncliente"] == "2") {
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
        $api->validarParametro("paiscliente", false,false);
        $api->validarParametro("lenguajecliente", false,false);
        $api->validarParametro("codigoregimencliente", false,false);
        $api->validarParametro("codigoimpuestocliente", false,false);
        $api->validarParametro("nombreimpuestocliente", false,false);
        $api->validarParametro("responsabilidadfiscalcliente", false,false);
        $api->validarParametro("responsabilidadtributariacliente", false,false);
        $api->validarParametro("operador", true);
        $api->validarParametro("valoriva", false, false);
        $api->validarParametro("valortotal", false, false);
        $api->validarParametro("tipotramite", true);
        $api->validarParametro("subtipotramite", false, false);
        $api->validarParametro("proyecto", true);
        $api->validarParametro("estadoliquidacion", false,false);

        $_SESSION["entrada"]["operador"] = mb_strtoupper(trim($_SESSION["entrada"]["operador"]), 'utf-8');

        if ($_SESSION["entrada"]["operador"] == 'USUPUBXX') {
            $api->validarParametro("emailcontrol", true);
            $api->validarParametro("identificacioncontrol", true);
            $api->validarParametro("nombrecontrol", true);
            $api->validarParametro("celularcontrol", true);
            $_SESSION["entrada"]["sedeusuario"] = '99';
        }

        if (!isset($_SESSION["entrada"]["ipcliente"])) {
            $_SESSION["entrada"]["ipcliente"] = \funcionesGenerales::localizarIP();
        }

        $arrServicios = array ();
        $mysqli = conexionMysqliApi();
        $servTmp = retornarRegistrosMysqliApi($mysqli, 'mreg_servicios', "1=1","idservicio");
        foreach ($servTmp as $srv1) {
            $arrServicios[$srv1['idservicio']] = $srv1['nombre'];
        }


        if ($_SESSION["entrada"]["operador"] != 'USUPUBXX') {
            $usux = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION["entrada"]["operador"] . "'");
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
        
        if (!isset($_SESSION["entrada"]["estadoliquidacion"]) ||  $_SESSION["entrada"]["estadoliquidacion"] == '') {
            $_SESSION["entrada"]["estadoliquidacion"] = '05';
        }
        if (sprintf("%02s",$_SESSION["entrada"]["estadoliquidacion"]) != '05' &&  sprintf("%02s",$_SESSION["entrada"]["estadoliquidacion"]) != '06') {
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Estado de liquidación inválido, debe ser 05 o 06';
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
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, 0, 'VC');

        //
        $totalservicios = 0;
        $matbase = '';
        $probase = '';
        $exp = false;
        $idmotivocancelacion = '';  
        $motivocancelacion = '';

        if ($_SESSION["entrada"]["codificacionservicios"] == "R") {

            $homologacion = \funcionesGenerales::homologacion_formatos_codificacionSII($mysqli, $_SESSION["entrada"]["servicios"]);
            if ($homologacion > 0) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = '9999';
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron los códigos RUES o sus homologaciones. El servicio no encontrado es: ' . $homologacion;
                \logSApi::general2('api_' . __FUNCTION__, $_SESSION["entrada"]["usuariows"], $_SESSION["jsonsalida"]["mensajeerror"]);
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {
                $i = 0;
                foreach ($_SESSION["entrada"]["servicios"] as $servCodRUES) {
                    $codigoHomologacion = retornarRegistroMysqliApi($mysqli, 'mreg_homologaciones_rue', "trim(id_tabla) like '01' and trim(cod_rue) like '" . $servCodRUES["idservicio"] . "'", "cod_cc");
                    if (!isset($arrServicios[$codigoHomologacion])) {
                        $mysqli->close();
                        $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'Servicio RUES ' . $servCodRUES["idservicio"] . ' no homologado';
                        $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    } else {
                        $i++;                        
                        $_SESSION["tramite"]["rues_servicios"][$i]["codigo_servicio"] = $servCodRUES["idservicio"];
                        $_SESSION["tramite"]["rues_servicios"][$i]["descripcion_servicio"] = '';
                        $_SESSION["tramite"]["rues_servicios"][$i]["orden_servicio"] = 1;
                        $_SESSION["tramite"]["rues_servicios"][$i]["orden_servicio_asociado"] = '';
                        $_SESSION["tramite"]["rues_servicios"][$i]["nombre_base"] = '';
                        $_SESSION["tramite"]["rues_servicios"][$i]["valor_base"] = $servCodRUES["base"];
                        $_SESSION["tramite"]["rues_servicios"][$i]["valor_liquidacion"] = $servCodRUES["valorservicio"];
                        $_SESSION["tramite"]["rues_servicios"][$i]["cantidad_servicio"] = $servCodRUES["cantidad"];
                        $_SESSION["tramite"]["rues_servicios"][$i]["indicador_base"] = '';
                        $_SESSION["tramite"]["rues_servicios"][$i]["indicador_renovacion"] = '';
                        $_SESSION["tramite"]["rues_servicios"][$i]["matricula_servicio"] = $servCodRUES["matricula"];
                        $_SESSION["tramite"]["rues_servicios"][$i]["nombre_matriculado"] = '';
                        $_SESSION["tramite"]["rues_servicios"][$i]["ano_renovacion"] = '';
                        $_SESSION["tramite"]["rues_servicios"][$i]["valor_activos_sin_ajustes"] = '';


                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = '';
                        if (trim($servCodRUES["matricula"]) != '') {
                            $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $servCodRUES["matricula"];
                        }
                        if (trim($servCodRUES["proponente"]) != '') {
                            $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $servCodRUES["proponente"];
                        }
                        if (isset($servCodRUES["nombre"])) {
                            $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $servCodRUES["nombre"];
                        } else {
                            $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = '';
                        }
                        $_SESSION["tramite"]["liquidacion"][$i]["ano"] = $servCodRUES["anobase"];
                        $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = $servCodRUES["cantidad"];
                        $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = $servCodRUES["base"];
                        $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = $servCodRUES["porcentaje"];
                        $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = $servCodRUES["valorservicio"];
                        $_SESSION["tramite"]["liquidacion"][$i]["benart7"] = '';
                        $_SESSION["tramite"]["liquidacion"][$i]["reliquidacion"] = '';
                        $_SESSION["tramite"]["liquidacion"][$i]["serviciobase"] = 'S';
                        $_SESSION["tramite"]["liquidacion"][$i]["pagoafiliacion"] = '';
                        $_SESSION["tramite"]["liquidacion"][$i]["ir"] = '';
                        $_SESSION["tramite"]["liquidacion"][$i]["iva"] = '';
                        $_SESSION["tramite"]["liquidacion"][$i]["idalerta"] = '';
                        $_SESSION["tramite"]["liquidacion"][$i]["idsecuencia"] = $i;
                        $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = $codigoHomologacion;
                        if ($matbase == '') {
                            $matbase = $servCodRUES["matricula"];
                        }
                        if ($probase == '') {
                            $probase = $servCodRUES["proponente"];
                        }
                        if ($servCodRUES["matricula"] != '') {
                            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $servCodRUES["matricula"] . "'");
                            if ($exp && !empty($exp)) {
                                $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $exp["razonsocial"];
                            }
                        }
                        if ($servCodRUES["proponente"] != '') {
                            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "proponente='" . $servCodRUES["proponente"] . "'");
                            if ($exp && !empty($exp)) {
                                $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $exp["razonsocial"];
                            }
                        }
                        $totalservicios = $totalservicios + $servCodRUES["valorservicio"];
                    }
                }
            }
        } else {

            $i = 0;
            foreach ($_SESSION["entrada"]["servicios"] as $servCodSII) {
                if (!isset($arrServicios[$servCodSII["idservicio"]])) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Servicio SII ' . $servCodSII["idservicio"] . ' no encontrado';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                } else {

                    $i++;
                    if ($servCodSII["matricula"] != '') {
                        $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $servCodSII["matricula"];
                    } else {
                        if ($servCodSII["proponente"] != '') {
                            $_SESSION["tramite"]["liquidacion"][$i]["expediente"] = $servCodSII["proponente"];
                        }
                    }
                    if (isset($servCodSII["motivocancelacion"]) && $servCodSII["motivocancelacion"] != '') {
                        $idmotivocancelacion = $servCodSII["motivocancelacion"];
                    }
                    if (isset($servCodSII["detallemotivocancelacion"]) && $servCodSII["detallemotivocancelacion"] != '') {
                        $motivocancelacion = $servCodSII["detallemotivocancelacion"];
                    }                    
                    if (isset($servCodSII["nombre"])) {
                        $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $servCodSII["nombre"];
                    } else {
                        $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = '';
                    }
                    $_SESSION["tramite"]["liquidacion"][$i]["ano"] = $servCodSII["anobase"];
                    $_SESSION["tramite"]["liquidacion"][$i]["cantidad"] = $servCodSII["cantidad"];
                    $_SESSION["tramite"]["liquidacion"][$i]["valorbase"] = $servCodSII["base"];
                    $_SESSION["tramite"]["liquidacion"][$i]["porcentaje"] = $servCodSII["porcentaje"];
                    $_SESSION["tramite"]["liquidacion"][$i]["valorservicio"] = $servCodSII["valorservicio"];
                    $_SESSION["tramite"]["liquidacion"][$i]["benart7"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["reliquidacion"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["serviciobase"] = 'S';
                    $_SESSION["tramite"]["liquidacion"][$i]["pagoafiliacion"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["ir"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["iva"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["idalerta"] = '';
                    $_SESSION["tramite"]["liquidacion"][$i]["idsecuencia"] = $i;
                    $_SESSION["tramite"]["liquidacion"][$i]["idservicio"] = $servCodSII["idservicio"];
                    if ($matbase == '') {
                        $matbase = $servCodSII["matricula"];
                    }
                    if ($probase == '') {
                        $probase = $servCodSII["proponente"];
                    }
                    if ($servCodSII["matricula"] != '') {
                        $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $servCodSII["matricula"] . "'");
                        if ($exp && !empty($exp)) {
                            $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $exp["razonsocial"];
                        }
                    }
                    if ($servCodSII["proponente"] != '') {
                        $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "proponente='" . $servCodSII["proponente"] . "'");
                        if ($exp && !empty($exp)) {
                            $_SESSION["tramite"]["liquidacion"][$i]["nombre"] = $exp["razonsocial"];
                        }
                    }

                    $totalservicios = $totalservicios + $servCodSII["valorservicio"];
                }
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

        $_SESSION["tramite"]["numeroliquidacion"] = \funcionesGenerales::retornarSecuencia($mysqli, 'LIQUIDACION-REGISTROS');
        $_SESSION["tramite"]["idliquidacion"] = $_SESSION["tramite"]["numeroliquidacion"];
        $_SESSION["tramite"]["fecha"] = date("Ymd");
        $_SESSION["tramite"]["hora"] = date("His");
        $_SESSION["tramite"]["fechaultimamodificacion"] = date("His");
        $_SESSION["tramite"]["idusuario"] = $_SESSION["entrada"]["operador"];
        $_SESSION["tramite"]["tipotramite"] = $_SESSION["entrada"]["tipotramite"];
        $_SESSION["tramite"]["subtipotramite"] = $_SESSION["entrada"]["subtipotramite"];
        $_SESSION["tramite"]["iptramite"] = $_SESSION["entrada"]["ipcliente"];
        $_SESSION["tramite"]["numerorecuperacion"] = \funcionesGenerales::asignarNumeroRecuperacion($mysqli, 'mreg');
        $_SESSION["tramite"]["idestado"] = sprintf("%02s",$_SESSION["entrada"]["estadoliquidacion"]);
        $_SESSION["tramite"]["sedeusuario"] = $_SESSION["entrada"]["sedeusuario"];
        $_SESSION["tramite"]["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
        
        //
        if (isset($_SESSION["entrada"]["sistemacreacion"]) && $_SESSION["entrada"]["sistemacreacion"] != '') {
            $_SESSION["tramite"]["sistemacreacion"] = $_SESSION["entrada"]["sistemacreacion"];
        } else {
            $_SESSION["tramite"]["sistemacreacion"] = 'API - reportarTransaccion - ' . $_SESSION["entrada"]["usuariows"];
        }
        

        //Datos del cliente
        $_SESSION["tramite"]["idtipoidentificacioncliente"] = $_SESSION["entrada"]["tipoidentificacioncliente"];
        $_SESSION["tramite"]["identificacioncliente"] = $_SESSION["entrada"]["identificacioncliente"];

        if ($_SESSION["entrada"]["tipoidentificacioncliente"] != "2") {
            $_SESSION["tramite"]["nombrecliente"] = $nombresCliente;
            $_SESSION["tramite"]["apellidocliente"] = $apellidosCliente;
            $_SESSION["tramite"]["nombre1cliente"] = $_SESSION["entrada"]["nombre1cliente"];
            $_SESSION["tramite"]["nombre2cliente"] = $_SESSION["entrada"]["nombre2cliente"];
            $_SESSION["tramite"]["apellido1cliente"] = $_SESSION["entrada"]["apellido1cliente"];
            $_SESSION["tramite"]["apellido2cliente"] = $_SESSION["entrada"]["apellido2cliente"];
        }
        if ($_SESSION["entrada"]["tipoidentificacioncliente"] == "2") {
            $_SESSION["tramite"]["nombrecliente"] = str_replace("&quot;",'"',$_SESSION["entrada"]["razonsocialcliente"]);
        }
        $_SESSION["tramite"]["email"] = $_SESSION["entrada"]["emailcliente"];
        $_SESSION["tramite"]["direccion"] = str_replace("&quot;",'"',$_SESSION["entrada"]["direccioncliente"]);
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

        //Datos basicos del expediente afectado
        $_SESSION["tramite"]["idexpedientebase"] = '';
        $_SESSION["tramite"]["tipoidentificacionbase"] = '';
        $_SESSION["tramite"]["identificacionbase"] = '';

        //
        if (isset($_SESSION["entrada"]["nombrebaseliquidacion"]) && trim($_SESSION["entrada"]["nombrebaseliquidacion"]) != '') {
            $_SESSION["tramite"]["nombrebase"] = $_SESSION["entrada"]["nombrebaseliquidacion"];
        } else {
            if ($_SESSION["entrada"]["tipoidentificacioncliente"] == "1") {
                $_SESSION["tramite"]["nombrebase"] = $apellidosCliente . ' ' . $nombresCliente;
            }
            if ($_SESSION["entrada"]["tipoidentificacioncliente"] == "2") {
                $_SESSION["tramite"]["nombrebase"] = $_SESSION["entrada"]["razonsocialcliente"];
            }
        }

        //
        $_SESSION["tramite"]["organizacionbase"] = '';
        $_SESSION["tramite"]["categoriabase"] = '';
        $_SESSION["tramite"]["matriculabase"] = '';
        $_SESSION["tramite"]["proponentebase"] = '';
        $_SESSION["tramite"]["actividadesbase"] = '';

        $_SESSION["tramite"]["nombrebaseliquidacion"] = '';
        $_SESSION["tramite"]["tipdocbaseliquidacion"] = '';
        $_SESSION["tramite"]["numdocbaseliquidacion"] = '';
        $_SESSION["tramite"]["fecdocbaseliquidacion"] = '';
        $_SESSION["tramite"]["mundocbaseliquidacion"] = '';
        $_SESSION["tramite"]["oridocbaseliquidacion"] = '';
        $_SESSION["tramite"]["tiporegistrobaseliquidacion"] = '';

        if (isset($_SESSION["entrada"]["nombrebaseliquidacion"]) && trim($_SESSION["entrada"]["nombrebaseliquidacion"]) != '') {
            $_SESSION["tramite"]["nombrebaseliquidacion"] = $_SESSION["entrada"]["nombrebaseliquidacion"];
        }
        if (isset($_SESSION["entrada"]["tipdocbaseliquidacion"]) && trim($_SESSION["entrada"]["tipdocbaseliquidacion"]) != '') {
            $_SESSION["tramite"]["tipdocbaseliquidacion"] = $_SESSION["entrada"]["tipdocbaseliquidacion"];
        }
        if (isset($_SESSION["entrada"]["numdocbaseliquidacion"]) && trim($_SESSION["entrada"]["numdocbaseliquidacion"]) != '') {
            $_SESSION["tramite"]["numdocbaseliquidacion"] = $_SESSION["entrada"]["numdocbaseliquidacion"];
        }
        if (isset($_SESSION["entrada"]["fecdocbaseliquidacion"]) && trim($_SESSION["entrada"]["fecdocbaseliquidacion"]) != '') {
            $_SESSION["tramite"]["fecdocbaseliquidacion"] = $_SESSION["entrada"]["fecdocbaseliquidacion"];
        }
        if (isset($_SESSION["entrada"]["mundocbaseliquidacion"]) && trim($_SESSION["entrada"]["mundocbaseliquidacion"]) != '') {
            $_SESSION["tramite"]["mundocbaseliquidacion"] = $_SESSION["entrada"]["mundocbaseliquidacion"];
        }
        if (isset($_SESSION["entrada"]["oridocbaseliquidacion"]) && trim($_SESSION["entrada"]["oridocbaseliquidacion"]) != '') {
            $_SESSION["tramite"]["oridocbaseliquidacion"] = $_SESSION["entrada"]["oridocbaseliquidacion"];
        }
        if (isset($_SESSION["entrada"]["tiporegistrobaseliquidacion"]) && trim($_SESSION["entrada"]["tiporegistrobaseliquidacion"]) != '') {
            $_SESSION["tramite"]["tiporegistrobaseliquidacion"] = $_SESSION["entrada"]["tiporegistrobaseliquidacion"];
        }

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
        $_SESSION["tramite"]["tamanoempresarial957"] = '';
        if (isset($_SESSION["entrada"]["tamanoempresarial957"])) {
            $_SESSION["tramite"]["tamanoempresarial957"] = $_SESSION["entrada"]["tamanoempresarial957"];
        }

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
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $matbase . "'");
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
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "proponente='" . $probase . "'");
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
        
        //
        $_SESSION["tramite"]["idmotivocancelacion"] = $idmotivocancelacion;
        $_SESSION["tramite"]["motivocancelacion"] = $motivocancelacion;
        
        //
        if (isset($_SESSION["entrada"]["urlretorno"]) && $_SESSION["entrada"]["urlretorno"] != '') {
            $_SESSION["tramite"]["urlretorno"] = $_SESSION["entrada"]["urlretorno"];
        }

        
        // grabarLiquidacionMreg();
        $result = \funcionesRegistrales::grabarLiquidacionMreg($mysqli);
        if ($result === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        if ($_SESSION["tramite"]["numerorecuperacion"] != "") {
            $_SESSION["jsonsalida"]["idliquidacion"] = $_SESSION["tramite"]["numeroliquidacion"];
            $_SESSION["jsonsalida"]["numerorecuperacion"] = $_SESSION["tramite"]["numerorecuperacion"];
            $_SESSION["jsonsalida"]["urlparapago"] = TIPO_HTTP . HTTP_HOST . '/lanzarVirtual.php?_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&_opcion=pagoelectronico&_numrec=' . $_SESSION["tramite"]["numerorecuperacion"];
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
