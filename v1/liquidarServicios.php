<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait liquidarServicios {

    public function liquidarServicios(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        $resError = set_error_handler('myErrorHandler');
        $nameLog = 'api_liquidarServicios_' . date("Ymd");

        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["serviciosliquidados"] = array();
        $_SESSION["jsonsalida"]["totalbruto"] = 0;
        $_SESSION["jsonsalida"]["totaliva"] = 0;
        $_SESSION["jsonsalida"]["totalneto"] = 0;
        $_SESSION["jsonsalida"]["idliquidacion"] = '';
        $_SESSION["jsonsalida"]["numerorecuperacion"] = '';
        $_SESSION["jsonsalida"]["url"] = 0;

        // Verifica método de recepcion de parámetros
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("generarliquidacion", false);
        $api->validarParametro("sistemaorigen", false);

        //
        if (!$api->validarToken('liquidarServicios', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Recupera los renglones con la información de las matrículas
        // ********************************************************************** //
        if (!isset($_SESSION["entrada1"]["servicios"]) || count($_SESSION["entrada1"]["servicios"]) == 0) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicaron los servicios a liquidar';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Crea la conexión con la BD
        // ********************************************************************** //
        if (!isset($_SESSION["entrada"]["ambiente"]) || $_SESSION["entrada"]["ambiente"] == '' || $_SESSION["entrada"]["ambiente"] == 'A') {
            $mysqli = conexionMysqliApi();
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'D') {
            $mysqli = conexionMysqliApi('D-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'P') {
            $mysqli = conexionMysqliApi('P-' . $_SESSION["generales"]["codigoempresa"]);
        }
        
        //
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $fcorte = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");

        // ************************************************************************ //
        // Arma variables de session
        // ************************************************************************ //        
        $res = \funcionesGenerales::asignarVariablesSessionSinValidarUsuario($mysqli, $_SESSION["entrada"]);
        if ($res === false) {
            $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.gen.error.autenticacion', '', '', '', '', '');
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = '(1) ' . $menerror;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Liquidación de servicios
        // ********************************************************************** //
        $txterrores = '';
        $iLin = 0;
        $matriculas = array();
        foreach ($_SESSION["entrada1"]["servicios"] as $s) {
            if ($s["matricula"] != '') {
                $matriculas[$s["matricula"]] = $s["matricula"];
            }
            $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $s["idservicio"] . "'");
            if ($serv === false || empty($serv)) {
                $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.servemp.servicio.no.encontrado', '', '', '', '', '', $iLin, $s["idservicio"]);
                $txterrores .= '(4) ' . $menerror . "<br>";
                $incluir = 'no';
            } else {
                if ($serv["idesiva"] != 'S') {
                    $iLin++;
                    $incluir = 'si';
                    if (!is_numeric($s["idservicio"])) {
                        $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.servemp.servicio.no.definido', '', '', '', '', '', $iLin, $s["idservicio"]);
                        $txterrores .= '(2) ' . $menerror . "<br>";
                        $incluir = 'no';
                    }
                    if (strlen($s["idservicio"]) != 8) {
                        $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.servemp.servicio.long.erronea', '', '', '', '', '', $iLin, $s["idservicio"]);
                        $txterrores .= '(3) ' . $menerror . "<br>";
                        $incluir = 'no';
                    }
                    if (trim((string) $serv["tipotarifa"]) == '') {
                        $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.servemp.servicio.sin.tipotarifa', '', '', '', '', '', $iLin, $s["idservicio"]);
                        $txterrores .= '(5) ' . $menerror . "<br>";
                        $incluir = 'no';
                    }

                    //
                    $exp = false;
                    if ($incluir == 'si') {
                        if ($serv["tipoingreso"] > '00' && $serv["tipoingreso"] <= '20') {
                            if (isset($s["matricula"]) && trim((string) $s["matricula"]) != '') {
                                $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $s["matricula"] . "'");
                                if ($exp === false || empty($exp)) {
                                    $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.servemp.expediente.no.localizado', '', $s["matricula"], '', '', '', $iLin, '');
                                    $txterrores .= '(6) ' . $menerror . "<br>";
                                    $incluir = 'no';
                                }
                            } else {
                                $exp = false;
                            }
                        } else {
                            if ($serv["tipoingreso"] > '21' && $serv["tipoingreso"] <= '30' && (isset($_SESSION["entrada"]["tipotramite"])) && $_SESSION["entrada"]["tipotramite"] != 'cambiodomicilioproponente') {
                                if (isset($s["proponente"]) && trim((string) $s["proponente"]) != '') {
                                    $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "proponente='" . $s["proponente"] . "'");
                                    if ($exp === false || empty($exp)) {
                                        $menerror = \funcionesGenerales::retornarMensajeError($mysqli, 'api.servemp.expediente.no.localizado', '', $s["proponente"], '', '', '', $iLin, '');
                                        $txterrores .= '(7) ' . $menerror . "<br>";
                                        $incluir = 'no';
                                    }
                                } else {
                                    $exp = false;
                                }
                            }
                        }
                    }

                    //
                    if ($incluir == 'si') {
                        if (!isset($s["valorbase"])) {
                            $s["valorbase"] = 0;
                        }
                        if (!isset($s["cantidadestablecimientos"])) {
                            $s["cantidadestablecimientos"] = 0;
                        }
                        if (!isset($s["activospropietario"])) {
                            $s["activospropietario"] = 0;
                        }
                        if ($s["cantidad"] == 0) {
                            $s["cantidad"] = 1;
                        }
                        if (!isset($s["descuentoaplicable"])) {
                            $s["descuentoaplicable"] = '';
                        } else {
                            if ($s["descuentoaplicable"] != '') {
                                $s["descuentoaplicable"] = sprintf("%02s", $s["descuentoaplicable"]);
                            }
                        }

                        $val = \funcionesRegistrales::buscaTarifa($mysqli, $s["idservicio"], date("Y"), $s["cantidad"], $s["valorbase"], 'tarifa', $s["activospropietario"], $s["cantidadestablecimientos"], $s["matricula"], $s["descuentoaplicable"]);
                        $lin = array(
                            'idservicio' => $s["idservicio"],
                            'nombre' => $serv["nombre"],
                            'matricula' => $s["matricula"],
                            'descuentoaplicable' => $s["descuentoaplicable"],
                            'cantidad' => $s["cantidad"],
                            'valorbase' => $s["valorbase"],
                            'activospropietario' => $s["activospropietario"],
                            'cantidadestablecimientos' => $s["cantidadestablecimientos"],
                            'valorunitario' => $val / $s["cantidad"],
                            'valorservicio' => $val
                        );
                        $_SESSION["jsonsalida"]["serviciosliquidados"][] = $lin;

                        // Determina si el expediente tiene o no beneficio de la Ley 2219 
                        // asociaciones campesinas y agropecuarias
                        // Que renueven a tiempo
                        // Siempre y cuando no corresponda al mismo año de matrícula
                        if ($exp && isset($exp["ctrclaseespeesadl"]) && ($exp["ctrclaseespeesadl"] == '29' || $exp["ctrclaseespeesadl"] == '73' || $exp["ctrclaseespeesadl"] == '74' || $exp["ctrclaseespeesadl"] == '75')) {
                            if ($exp["ultanoren"] == date("Y")) {
                                if (substr($exp["fecmatricula"], 0, 4) < date("Y")) {
                                    if ($exp["fecrenovacion"] <= $fcorte) {
                                        if ($serv["tipoingreso"] == '04' || $serv["tipoingreso"] == '05' || $serv["tipoingreso"] == '07' || $serv["tipoingreso"] == '10' ||
                                                $serv["tipoingreso"] == '14' || $serv["tipoingreso"] == '15' || $serv["tipoingreso"] == '17' || $serv["tipoingreso"] == '20') {
                                            $lin = array(
                                                'idservicio' => '01090170',
                                                'nombre' => 'DESCUENTO BENEFICIO LEY 2219',
                                                'matricula' => $s["matricula"],
                                                'descuentoaplicable' => '',
                                                'cantidad' => -1,
                                                'valorbase' => 0,
                                                'activospropietario' => 0,
                                                'cantidadestablecimientos' => 0,
                                                'valorunitario' => $val * -1,
                                                'valorservicio' => $val * -1
                                            );
                                            $_SESSION["jsonsalida"]["serviciosliquidados"][] = $lin;
                                        }
                                    }
                                }
                            }
                        }

                        //
                        for ($ixx = 1; $ixx <= 7; $ixx++) {
                            if (trim((string) $serv["iddependiente" . $ixx]) != '') {
                                $tempserv = $serv["iddependiente" . $ixx];
                                $val = \funcionesRegistrales::buscaTarifa($mysqli, $tempserv, date("Y"), 1, $lin["valorservicio"], 'tarifa', $s["activospropietario"], $s["cantidadestablecimientos"]);
                                $lin = array(
                                    'idservicio' => $tempserv,
                                    'nombre' => retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $tempserv . "'", "nombre"),
                                    'matricula' => $s["matricula"],
                                    'descuentoaplicable' => '',
                                    'cantidad' => 1,
                                    'valorbase' => $lin["valorservicio"],
                                    'activospropietario' => $s["activospropietario"],
                                    'cantidadestablecimientos' => $s["cantidadestablecimientos"],
                                    'valorunitario' => $val,
                                    'valorservicio' => $val
                                );
                                $_SESSION["jsonsalida"]["serviciosliquidados"][] = $lin;
                            }
                        }
                    }
                }
            }
        }
        
        //
        if ($txterrores != '') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = $txterrores;
            $_SESSION["jsonsalida"]["serviciosliquidados"] = array();
            $_SESSION["jsonsalida"]["totalbruto"] = 0;
            $_SESSION["jsonsalida"]["totaliva"] = 0;
            $_SESSION["jsonsalida"]["totalneto"] = 0;
            $json = $api->json($_SESSION["jsonsalida"]);
            $api->response(str_replace("\\/", "/", $json), 200);
        }

        // Liquida impuestos
        $list1 = $_SESSION["jsonsalida"]["serviciosliquidados"];
        foreach ($list1 as $s) {
            $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $s["idservicio"] . "'");
            if ($serv && !empty($serv)) {
                for ($ixx = 1; $ixx <= 7; $ixx++) {
                    if (trim((string) $serv["idgravado" . $ixx]) != '') {
                        $tempserv = $serv["idgravado" . $ixx];
                        $serv1 = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $tempserv . "'");
                        $s["valorbase"] = $s["valorservicio"];
                        $s["cantidad"] = 1;
                        $val = \funcionesRegistrales::buscaTarifa($mysqli, $tempserv, date("Y"), 1, $s["valorbase"], 'tarifa', $s["activospropietario"], $s["cantidadestablecimientos"]);
                        $lin = array(
                            'idservicio' => $tempserv,
                            'nombre' => retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $tempserv . "'", "nombre") . ' (' . $serv["idservicio"] . ')',
                            'matricula' => $s["matricula"],
                            'descuentoaplicable' => '',
                            'cantidad' => $s["cantidad"],
                            'valorbase' => $s["valorbase"],
                            'porcentaje' => $serv1["valorservicio"],
                            'activospropietario' => $s["activospropietario"],
                            'cantidadestablecimientos' => $s["cantidadestablecimientos"],
                            'valorunitario' => $val,
                            'valorservicio' => $val,
                            'idserviciobase' => $s["idservicio"]
                        );
                        $_SESSION["jsonsalida"]["serviciosliquidados"][] = $lin;
                    }
                }
            }
        }
        foreach ($_SESSION["jsonsalida"]["serviciosliquidados"] as $s) {
            $_SESSION["jsonsalida"]["totalneto"] = $_SESSION["jsonsalida"]["totalneto"] + $s["valorservicio"];
            $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $s["idservicio"] . "'");
            if ($serv["idesiva"] == 'S') {
                $_SESSION["jsonsalida"]["totaliva"] = $_SESSION["jsonsalida"]["totaliva"] + $s["valorservicio"];
            } else {
                $_SESSION["jsonsalida"]["totalbruto"] = $_SESSION["jsonsalida"]["totalbruto"] + $s["valorservicio"];
            }
        }

        // 2024-09-17: jint: Si se trata de mutación borra el campo nombrebase64 de la tabla mreg_est_inscritos_campos
        // solución temporal mientras se libera SII3.37.X
        /*
        if (isset($_SESSION["entrada"]["tipotramite"]) && $_SESSION["entrada"]["tipotramite"] == 'mutacionregmer') {
            if (!empty($matriculas)) {
                foreach ($matriculas as $m) {
                    borrarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos_campos', "matricula='" . $m . "' and campo='nombrebase64'");
                }
            }
        }
        */
        
        //
        if (isset($_SESSION["entrada"]["generarliquidacion"]) && strtoupper($_SESSION["entrada"]["generarliquidacion"]) == 'S') {
            if (isset($_SESSION["entrada"]["tipotramite"]) && $_SESSION["entrada"]["tipotramite"] == 'serviciosempresariales') {
                $liq = \funcionesRegistrales::retornarMregLiquidacion($mysqli, 0, 'VC');
                $liq["idliquidacion"] = \funcionesGenerales::retornarSecuencia($mysqli, 'LIQUIDACION-REGISTROS');
                $liq["numeroliquidacion"] = $liq["idliquidacion"];
                $liq["numerorecuperacion"] = \funcionesGenerales::asignarNumeroRecuperacion($mysqli, 'mreg');
                $liq["fecha"] = date("Ymd");
                $liq["hora"] = date("H:i:s");
                $liq["idusuario"] = 'USUPUBXX';
                $liq["sede"] = '99';
                $liq["tipotramite"] = $_SESSION["entrada"]["tipotramite"];
                $liq["subtipotramite"] = '';
                $liq["tipogasto"] = '0';
                $liq["origen"] = 'EXTERNO';
                if (isset($_SESSION["entrada"]["sistemaorigen"]) && $_SESSION["entrada"]["sistemaorigen"] != '') {
                    $liq["origen"] = $_SESSION["entrada"]["sistemaorigen"];
                }
                $liq["iptramite"] = '';
                $liq["idestado"] = '01';

                $liq["valorbruto"] = $_SESSION["jsonsalida"]["totalbruto"];
                $liq["valorbaseiva"] = $_SESSION["jsonsalida"]["totalbruto"];
                $liq["valoriva"] = $_SESSION["jsonsalida"]["totaliva"];
                $liq["valortotal"] = $_SESSION["jsonsalida"]["totalneto"];
                $liq["tramitepresencial"] = '1';

                $liq["liquidacion"] = array();
                $i = 0;
                foreach ($_SESSION["jsonsalida"]["serviciosliquidados"] as $lin) {
                    $i++;
                    $renglon = array();
                    $renglon["secuencia"] = $i;
                    $renglon["idsec"] = '000';
                    $renglon["idservicio"] = $lin["idservicio"];
                    $renglon["txtservicio"] = '';
                    $renglon["cc"] = '';
                    $renglon["expediente"] = '';
                    $renglon["nombre"] = '';
                    $renglon["ano"] = '';
                    $renglon["cantidad"] = $lin["cantidad"];
                    $renglon["valorbase"] = $lin["valorbase"];
                    $renglon["porcentaje"] = $lin["porcentaje"];
                    $renglon["valorservicio"] = $lin["valorservicio"];
                    $renglon["benart7"] = '';
                    $renglon["benley1780"] = '';
                    $renglon["reliquidacion"] = '';
                    $renglon["serviciobase"] = $lin["idserviciobase"];
                    $renglon["pagoafiliacion"] = '';
                    $renglon["ir"] = '';
                    $renglon["iva"] = 0;
                    $renglon["idalerta"] = 0;
                    $renglon["expedienteafiliado"] = '';
                    $renglon["porcentajeiva"] = 0;
                    $renglon["valoriva"] = 0;
                    $renglon["servicioiva"] = '';
                    $renglon["porcentajedescuento"] = 0;
                    $renglon["valordescuento"] = 0;
                    $renglon["serviciodescuento"] = '';
                    $renglon["clavecontrol"] = '';
                    $renglon["servicioorigen"] = '';
                    $renglon["diasmora"] = 0;
                    $renglon["porcentajeiva"] = 0;
                    $renglon["valoriva"] = 0;
                    $renglon["servicioiva"] = '';
                    $renglon["porcentajedescuento"] = 0;
                    $renglon["valordescuento"] = 0;
                    $renglon["serviciodescuento"] = '';
                    $liq["liquidacion"][$i] = $renglon;
                }
                \funcionesRegistrales::grabarLiquidacionMreg($mysqli, $liq);
                $_SESSION["jsonsalida"]["idliquidacion"] = $liq["idliquidacion"];
                $_SESSION["jsonsalida"]["numerorecuperacion"] = $liq["numerorecuperacion"];
                $_SESSION["jsonsalida"]["url"] = TIPO_HTTP . HTTP_HOST . '/scripts/lanzadorScripts.php?accion=pagoelectronico&numrec=' . $liq["numerorecuperacion"];
            }
        }

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        \logApi::general2($nameLog, '', 'Request: ' . json_encode($_SESSION["entrada"]));
        \logApi::general2($nameLog, '', 'Response: ' . json_encode($_SESSION["jsonsalida"]));
        \logApi::general2($nameLog, '', '');
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
