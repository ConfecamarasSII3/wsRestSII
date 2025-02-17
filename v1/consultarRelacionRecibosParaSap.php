<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarRelacionRecibosParaSap {

    public function consultarRelacionRecibosParaSap(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesS3V4.php');
        $resError = set_error_handler('myErrorHandler');

        //cantidad de registros
        $limit = 100;

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["cantidad"] = 0;
        $_SESSION["jsonsalida"]["recibos"] = array();
        $arrRecibos = array();
        $_SESSION["jsonsalida"]["recibos"] = $arrRecibos;
        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("fechainicial", true);
        $api->validarParametro("fechafinal", true);
        $api->validarParametro("horainicial", false);
        $api->validarParametro("horafinal", false);
        $api->validarParametro("operador", false);
        $api->validarParametro("ambiente", false);

        if (!(\funcionesGenerales::validarFecha($_SESSION["entrada"]["fechainicial"]))) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El parámetro fechainicial no es una fecha válida';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        if (!(\funcionesGenerales::validarFecha($_SESSION["entrada"]["fechafinal"]))) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El parámetro fechafinal no es una fecha válida';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["fechainicial"] > $_SESSION["entrada"]["fechafinal"]) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'fechafinal debe ser mayor a la  fechainicial';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        if (\funcionesGenerales::diferenciaEntreFechasCalendario($_SESSION["entrada"]["fechainicial"], $_SESSION["entrada"]["fechafinal"]) > 31) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La diferencia en días entre la fechainicial y la fechafinal no debe ser mayor a 11';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida si son definidos rangos de horas de consulta
        // ********************************************************************** // 
        $_SESSION["entrada"]["horainicial"] = sprintf("%06s", $_SESSION["entrada"]["horainicial"]);
        $_SESSION["entrada"]["horafinal"] = sprintf("%06s", $_SESSION["entrada"]["horafinal"]);
        if ($_SESSION["entrada"]["horafinal"] == '000000') {
            $_SESSION["entrada"]["horafinal"] = '235959';
        }
        $horas = substr(sprintf("%06s", $_SESSION["entrada"]["horainicial"]), 0, 2);
        if ($horas <= '24' && $horas >= '00') {
            $horaInicial = $_SESSION["entrada"]["horainicial"];
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'horainicial debe ser entre 00 y 24 horas';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        $horas = substr(sprintf("%06s", $_SESSION["entrada"]["horafinal"]), 0, 2);
        if ($horas <= '24' && $horas >= '00') {
            $horaFinal = $_SESSION["entrada"]["horafinal"];
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'horafinal debe ser entre 00 y 24 horas';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarRelacionRecibosParaSap', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        // ********************************************************************** //
        // Buscar recibos
        // ********************************************************************** // 
        $mysqli = false;
        if (!isset($_SESSION["entrada"]["ambiente"]) || $_SESSION["entrada"]["ambiente"] == '' || $_SESSION["entrada"]["ambiente"] == 'A') {
            $mysqli = conexionMysqliApi();
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'D') {
            $mysqli = conexionMysqliApi('D-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'P') {
            $mysqli = conexionMysqliApi('P-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9904";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        $trd = array();
        $trdtemps = retornarRegistrosMysqliApi($mysqli, 'bas_tipodoc', "1=1", "idtipodoc");
        foreach ($trdtemps as $trdtemp) {
            $trd[$trdtemp['idtipodoc']] = array(
                'tiposirep' => $trdtemp['homologasirep'],
                'tipodigitalizacion' => $trdtemp['homologadigitalizacion']
            );
        }

        if ($_SESSION["entrada"]["fechainicial"] == $_SESSION["entrada"]["fechafinal"]) {
            $query = "SELECT r.recibo as recibo, r.operacion as operacion, r.factura as factura, r.codigobarras as radicado, r.idliquidacion as idliquidacion, cb.actoreparto as rutasii, "
                    . "r.fecha as fecha, r.hora as hora, r.usuario as usuario, r.tipotramite as tipotramite,"
                    . "r.tipoidentificacion as idclase, r.identificacion as identificacion,r.razonsocial as nombre,"
                    . "r.municipio as municipio, r.email as email,r.telefono1 as telefono1, r.telefono2 as telefono2, r.direccion as direccion, r.codposcom as codposcom,"
                    . "r.numeroautorizacion as numeroautorizacion, r.cheque as cheque,r.franquicia as franquicia, r.nombrefranquicia as nombrefranquicia, r.codbanco as codbanco,"
                    . "r.valorneto as valor, r.tipogasto as tipogasto, r.estado as estado, r.idformapago as formapago, r.numerointernorue as numerointernorue, "
                    . "r.pagoefectivo as pagoefectivo, r.pagocheque as pagocheque, r.pagoconsignacion as pagoconsignacion, r.pagopseach as pagopseach, r.pagovisa as pagovisa, "
                    . "r.pagomastercard as pagomastercard, r.pagocredencial as pagocredencial, r.pagoamerican as pagoamerican, r.pagodiners as pagodiners, r.pagotdebito as pagotdebito "
                    . "FROM mreg_recibosgenerados r LEFT JOIN mreg_est_codigosbarras cb on r.codigobarras=cb.codigobarras "
                    . "WHERE r.fecha ='" . $_SESSION["entrada"]["fechafinal"] . "' and ("
                    . "r.hora between '" . $_SESSION["entrada"]["horainicial"] . "' and '" . $_SESSION["entrada"]["horafinal"] . "')"
                    . "ORDER BY r.recibo";
        }

        if ($_SESSION["entrada"]["fechainicial"] != $_SESSION["entrada"]["fechafinal"]) {
            $query = "SELECT r.recibo as recibo, r.operacion as operacion, r.codigobarras as radicado, r.idliquidacion as idliquidacion, cb.actoreparto as rutasii, "
                    . "r.fecha as fecha, r.hora as hora, r.usuario as usuario, r.tipotramite as tipotramite,"
                    . "r.tipoidentificacion as idclase, r.identificacion as identificacion,r.razonsocial as nombre,"
                    . "r.municipio as municipio, r.email as email,r.telefono1 as telefono1, r.telefono2 as telefono2, r.direccion as direccion, r.codposcom as codposcom,"
                    . "r.numeroautorizacion as numeroautorizacion, r.cheque as cheque,r.franquicia as franquicia, r.nombrefranquicia as nombrefranquicia, r.codbanco as codbanco,"
                    . "r.valorneto as valor, r.tipogasto as tipogasto, r.estado as estado, r.idformapago as formapago, r.numerointernorue as numerointernorue, "
                    . "r.pagoefectivo as pagoefectivo, r.pagocheque as pagocheque, r.pagoconsignacion as pagoconsignacion, r.pagopseach as pagopseach, r.pagovisa as pagovisa, "
                    . "r.pagomastercard as pagomastercard, r.pagocredencial as pagocredencial, r.pagoamerican as pagoamerican, r.pagodiners as pagodiners, r.pagotdebito as pagotdebito "
                    . "FROM mreg_recibosgenerados r LEFT JOIN mreg_est_codigosbarras cb on r.codigobarras=cb.codigobarras "
                    . "WHERE r.fecha between '" . $_SESSION["entrada"]["fechainicial"] . "' and '" . $_SESSION["entrada"]["fechafinal"] . "'"
                    . "ORDER BY r.recibo";
        }

        if (trim($_SESSION["entrada"]["operador"]) != '') {
            $query .= " and r.usuario = '" . $_SESSION["entrada"]["operador"] . "'";
        }

        $mysqli->set_charset("utf8");
        $resQueryReciboss = ejecutarQueryMysqliApi($mysqli, $query);
        $arrServicios = array();
        if (!empty($resQueryReciboss)) {
            foreach ($resQueryReciboss as $reciboTemp) {
                if (substr($reciboTemp["recibo"], 0, 1) == 'S' || substr($reciboTemp["recibo"], 0, 1) == 'M') {
                    if ($reciboTemp["tipogasto"] == '0' || $reciboTemp["tipogasto"] == '7' || $reciboTemp["tipogasto"] == '8') {
                        $factele = 'no';
                        $drec = retornarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_detalle', "recibo='" . $reciboTemp["recibo"] . "'", "id");
                        if ($drec && !empty($drec)) {
                            foreach ($drec as $drec1) {
                                if (!isset($arrServicios[$drec1["idservicio"]])) {
                                    $arrServicios[$drec1["idservicio"]] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $drec1["idservicio"] . "'");
                                }
                                if (isset($arrServicios[$drec1["idservicio"]])) {
                                    if ($arrServicios[$drec1["idservicio"]]["facturable_electronicamente"] == 'SI') {
                                        $factele = 'si';
                                    }
                                }
                            }
                        }

                        // if ($factele == 'si') {
                        $liqstt = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_campos', "idliquidacion=" . $reciboTemp['idliquidacion'] . " and campo='subtipotramite'", "contenido");
                        $matBase = '';
                        $proBase = '';
                        $recibo = array();
                        $recibo['recibo'] = $reciboTemp['recibo'];
                        $recibo['operacion'] = $reciboTemp['operacion'];
                        $recibo['reciboreversado'] = $reciboTemp['factura'];
                        $recibo['fecha'] = $reciboTemp['fecha'];
                        $recibo['hora'] = $reciboTemp['hora'];
                        $recibo['usuario'] = $reciboTemp['usuario'];
                        $recibo['idliquidacion'] = $reciboTemp['idliquidacion'];
                        $recibo['radicado'] = $reciboTemp['radicado'];
                        $recibo['tipotramite'] = $reciboTemp['tipotramite'];
                        $recibo['subtipotramite'] = $liqstt;

                        $recibo['origen'] = 'LOCAL';
                        $recibo['codigocamarainvolucrada'] = '';
                        $recibo['nitcamarainvolucrada'] = '';
                        $recibo['dvnitcamarainvolucrada'] = '';

                        //
                        if ($reciboTemp["tipogasto"] == '7') {
                            $recibo['origen'] = 'RUE-RECEPTORA';
                            $recibo['codigocamarainvolucrada'] = substr($reciboTemp['numerointernorue'], 19, 2);
                        }
                        if ($reciboTemp["tipogasto"] == '8') {
                            $recibo['origen'] = 'RUE-RESPONSABLE';
                            $recibo['codigocamarainvolucrada'] = substr($reciboTemp['numerointernorue'], 17, 2);
                        }
                        if ($recibo['codigocamarainvolucrada'] != '') {
                            $recibo['nitcamarainvolucrada'] = retornarRegistroMysqliApi($mysqli, 'bas_camaras', "id='" . $recibo['codigocamarainvolucrada'] . "'", "nit");
                            $recibo['dvnitcamarainvolucrada'] = \funcionesGenerales::calcularDv($recibo['nitcamarainvolucrada']);
                        }

                        //
                        $recibo['facturable'] = 'SI';
                        if ($factele != 'si') {
                            $recibo['facturable'] = 'NO';
                        }

                        //
                        $recibo['idclase'] = $reciboTemp['idclase'];
                        $recibo['nombreidclase'] = retornarRegistroMysqliApi($mysqli, 'mreg_tipoidentificacion', "id='" . $reciboTemp['idclase'] . "'", "descripcion");
                        $recibo['identificacion'] = $reciboTemp['identificacion'];
                        $recibo['nombre'] = $reciboTemp['nombre'];
                        $recibo['direccion'] = $reciboTemp['direccion'];
                        $recibo['municipio'] = $reciboTemp['municipio'];
                        $recibo['nombremunicipio'] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $reciboTemp['municipio'] . "'", "ciudad");
                        $recibo["nacionalidad"] = '';
                        if ($reciboTemp["idclase"] == '1' || $reciboTemp["idclase"] == '2' || $reciboTemp["idclase"] == '4' || $reciboTemp["idclase"] == 'R') {
                            $recibo["nacionalidad"] = 'COLOMBIANO/A';
                        }
                        if ($reciboTemp["idclase"] == '3' || $reciboTemp["idclase"] == '5' || $reciboTemp["idclase"] == 'E') {
                            $recibo["nacionalidad"] = 'EXTRANJERO/A';
                        }
                        if ($reciboTemp["idclase"] == 'P' || $reciboTemp["idclase"] == 'V') {
                            $recibo["nacionalidad"] = 'VENEZOLANO/A';
                        }
                        $recibo['telefono'] = '';
                        $recibo['celular'] = '';
                        if ($reciboTemp["telefono1"] != '' && strlen($reciboTemp["telefono1"]) == 10 && substr($reciboTemp["telefono1"], 0, 1) == '3') {
                            $recibo['celular'] = $reciboTemp["telefono1"];
                        } else {
                            if ($reciboTemp["telefono1"] != '') {
                                $recibo['telefono'] = $reciboTemp["telefono1"];
                            }
                        }
                        if ($reciboTemp["telefono2"] != '' && strlen($reciboTemp["telefono2"]) == 10 && substr($reciboTemp["telefono2"], 0, 1) == '3') {
                            $recibo['celular'] = $reciboTemp["telefono2"];
                        } else {
                            if ($reciboTemp["telefono2"] != '') {
                                $recibo['telefono'] = $reciboTemp["telefono2"];
                            }
                        }
                        $recibo['email'] = $reciboTemp["email"];
                        $recibo['codigopostal'] = $reciboTemp["codposcom"];
                        $recibo['tipopersona'] = '';
                        if ($reciboTemp["idclase"] == '2') {
                            $recibo['tipopersona'] = 'J';
                        } else {
                            $recibo['tipopersona'] = 'N';
                        }

                        $recibo['valor'] = doubleval($reciboTemp['valor']);
                        $recibo['tipogasto'] = $reciboTemp['tipogasto'];
                        $recibo['estado'] = $reciboTemp['estado'];
                        if ($reciboTemp['formapago'] == '') {
                            if ($reciboTemp["pagoefectivo"] != 0) {
                                $reciboTemp['formapago'] = '01';
                            }
                            if ($reciboTemp["pagocheque"] != 0) {
                                $reciboTemp['formapago'] = '02';
                            }
                            if ($reciboTemp["pagoconsignacion"] != 0) {
                                $reciboTemp['formapago'] = '06';
                            }
                            if ($reciboTemp["pagovisa"] != 0) {
                                $reciboTemp['formapago'] = '04';
                            }
                            if ($reciboTemp["pagopseach"] != 0) {
                                $reciboTemp['formapago'] = '09';
                            }
                            if ($reciboTemp["pagomastercard"] != 0) {
                                $reciboTemp['formapago'] = '04';
                            }
                            if ($reciboTemp["pagoamerican"] != 0) {
                                $reciboTemp['formapago'] = '04';
                            }
                            if ($reciboTemp["pagocredencial"] != 0) {
                                $reciboTemp['formapago'] = '04';
                            }
                            if ($reciboTemp["pagodiners"] != 0) {
                                $reciboTemp['formapago'] = '04';
                            }
                            if ($reciboTemp["pagotdebito"] != 0) {
                                $reciboTemp['formapago'] = '03';
                            }
                        }
                        if ($reciboTemp['formapago'] == '') {
                            $reciboTemp['formapago'] = '01';
                        }

                        //
                        $recibo['formapago'] = $reciboTemp['formapago'];
                        $recibo['nombreformapago'] = retornarRegistroMysqliApi($mysqli, 'mreg_formaspago', "id='" . $reciboTemp['formapago'] . "'", "descripcion");
                        $recibo['cuentacontable'] = '';
                        $recibo['numeroautorizacion'] = $reciboTemp['numeroautorizacion'];
                        $recibo['numerocheque'] = $reciboTemp['cheque'];
                        $recibo['franquicia'] = $reciboTemp['franquicia'];
                        $recibo['nombrefranquicia'] = $reciboTemp['nombrefranquicia'];
                        $recibo['codigobanco'] = $reciboTemp['codbanco'];
                        $recibo['naturalezarecibo'] = '';

                        //
                        $buscarctas = retornarRegistroMysqliApi($mysqli, 'tablas', "tabla='cuentassap' and idcodigo='" . substr($reciboTemp["operacion"], 0, 2) . "-" . $recibo['formapago'] . "'");
                        if ($buscarctas === false || empty($buscarctas)) {
                            $buscarctas = retornarRegistroMysqliApi($mysqli, 'tablas', "tabla='cuentassap' and idcodigo='ZZ-" . $recibo['formapago'] . "'");
                            if ($buscarctas === false || empty($buscarctas)) {
                                $buscarctas = array(
                                    'campo1' => 'XXXXXX',
                                    'campo2' => 'XXXXXX'
                                );
                            }
                        }

                        $primserv = '';
                        $servicios = array();
                        // ********************************************************************** //
                        // Retornar servicios del recibo
                        // ********************************************************************** //                 

                        if ($drec && !empty($drec)) {
                            $clacontrol = '';
                            foreach ($drec as $servent) {
                                if ($primserv == '') {
                                    $primserv = $servent['idservicio'];
                                }
                                if ($servent["clavecontrol"] == '') {
                                    if (substr($servent["idservicio"], 0, 6) == '010201') {
                                        $clacontrol = \funcionesGenerales::generarAleatorioAlfanumerico(12);
                                        $servent['clavecontrol'] = $clacontrol;
                                    }
                                    if (substr($servent["idservicio"], 0, 6) == '010202') {
                                        $clacontrol = \funcionesGenerales::generarAleatorioAlfanumerico(12);
                                        $servent['clavecontrol'] = $clacontrol;
                                    }
                                }
                                if ($servent["idservicio"] == '01090110' || $servent["idservicio"] == '01090111') {
                                    $servent['clavecontrol'] = $clacontrol;
                                }
                                $servicio = array();
                                $servicio['servicio'] = $servent['idservicio'];
                                $servicio['nservicio'] = $arrServicios[$servent['idservicio']]["nombre"];
                                $servicio['matricula'] = $servent['matricula'];
                                $servicio['proponente'] = $servent['proponente'];
                                $servicio['identificacion'] = $servent['identificacion'];
                                $servicio['nombre'] = $servent['razonsocial'];
                                $servicio['cantidad'] = $servent['cantidad'];
                                $servicio['valorbase'] = $servent['valorbase'];

                                //
                                if ($servent['cantidad'] != 0 && $servent['cantidad'] != 1) {
                                    $servicio['valorunitario'] = $servent['valorservicio'] / $servicio['cantidad'];
                                } else {
                                    $servicio['valorunitario'] = $servent['valorservicio'];
                                }

                                //
                                $servicio['valorservicio'] = $servent['valorservicio'];
                                $servicio['ano'] = $servent['ano'];
                                $servicio['clavecontrol'] = $servent['clavecontrol'];
                                $servicios[] = $servicio;
                                if ($matBase == '') {
                                    $matBase = $servicio['matricula'];
                                }
                                if ($proBase == '') {
                                    $proBase = $servicio['proponente'];
                                }
                            }
                        }

                        //
                        $recibo['servicios'] = $servicios;

                        //
                        if ($primserv != '') {
                            $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $primserv . "'");
                            if ($serv === false || empty($serv) || $serv["idtipovalor"] == '1' || $serv["idtipovalor"] == '4') {
                                $recibo['cuentacontable'] = $buscarctas["campo1"];
                            } else {
                                $recibo['cuentacontable'] = $buscarctas["campo2"];
                            }
                        } else {
                            $recibo['cuentacontable'] = $buscarctas["campo1"];
                        }


                        $arrRecibos[] = $recibo;
                        // }
                    }
                }
            }
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron resultados para los datos solicitados';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $_SESSION["jsonsalida"]["cantidad"] = count($arrRecibos);
        $_SESSION["jsonsalida"]["recibos"] = $arrRecibos;

        $mysqli->close();
        unset($resQueryRecibos);

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
