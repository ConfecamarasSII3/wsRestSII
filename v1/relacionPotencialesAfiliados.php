<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait relacionPotencialesAfiliados {

    public function relacionPotencialesAfiliados(API $api) {

        /*
          ini_set('memory_limit', '4096M');
          ini_set('display_errors', true);
          ini_set('soap.wsdl_cache_enabled', '0');
          ini_set('soap.wsdl_cache_ttl', '0');
          ini_set('default_socket_timeout', 1440);
         */

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');
        $nameLog = 'relacionPotencialesAfiliados_' . date("Ymd");

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["fechahorainicio"] = date("Ymd") . ' ' . date("His");
        $_SESSION["jsonsalida"]["fechahorafinalizacion"] = '';
        $_SESSION["jsonsalida"]["totalregistros"] = 0;
        $_SESSION["jsonsalida"]["expedientes"] = array();

        $_SESSION["jsonsalida1"] = array();
        $_SESSION["jsonsalida1"]["codigoerror"] = '0001';
        $_SESSION["jsonsalida1"]["mensajeerror"] = 'En proceso...';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("municipios", false);
        $api->validarParametro("rangoactivos", false);
        $api->validarParametro("organizaciones", false);
        $api->validarParametro("fechamatriculamaxima", false);
        $api->validarParametro("ambiente", false);
        $api->validarParametro("offset", false);
        $api->validarParametro("limit", false);
        $api->validarParametro("extendido", false);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('relacionPotencialesAfiliados', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $muns = array();
        if (isset($_SESSION["entrada"]["municipios"]) && $_SESSION["entrada"]["municipios"] != '') {
            $muns = explode(",", $_SESSION["entrada"]["municipios"]);
        }

        $acts = array();
        if (isset($_SESSION["entrada"]["rangoactivos"]) && $_SESSION["entrada"]["rangoactivos"] != '') {
            $acts = explode(",", $_SESSION["entrada"]["rangoactivos"]);
        }

        $orgs = array();
        if (isset($_SESSION["entrada"]["organizaciones"]) && $_SESSION["entrada"]["organizaciones"] != '') {
            $orgs = explode(",", $_SESSION["entrada"]["organizaciones"]);
        }

        //
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

        //
        $vinculos = array();
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_codvinculos', "1=1", "id");
        foreach ($temx as $tx) {
            $vinculos[$tx["id"]] = $tx;
        }
        unset($temx);

        // 
        $serviciosRenovacion = array();
        $serviciosMatricula = array();
        $temx1 = retornarRegistrosMysqliApi($mysqli, "mreg_servicios", "tipoingreso IN ('02','03','12','13')", "idservicio");
        foreach ($temx1 as $x1) {
            if ($x1["tipoingreso"] == '03' || $x1["tipoingreso"] == '13') {
                $serviciosRenovacion[$x1["idservicio"]] = $x1["idservicio"];
            }
            if ($x1["tipoingreso"] == '02' || $x1["tipoingreso"] == '12') {
                $serviciosMatricula[$x1["idservicio"]] = $x1["idservicio"];
            }
        }

        //
        $tServiciosRenovacion = '';
        foreach ($serviciosRenovacion as $s) {
            if ($tServiciosRenovacion != '') {
                $tServiciosRenovacion .= ",";
            }
            $tServiciosRenovacion .= "'" . $s . "'";
        }

        //
        $serviciosAfiliacion = array();
        $temx1 = retornarRegistrosMysqliApi($mysqli, "mreg_servicios", "1=1", "idservicio");
        foreach ($temx1 as $x1) {
            if ($x1["grupoventas"] == '02') {
                $serviciosAfiliacion[$x1["idservicio"]] = $x1["idservicio"];
            }
        }

        //
        $tServiciosAfiliacion = '';
        foreach ($serviciosAfiliacion as $s) {
            if ($tServiciosAfiliacion != '') {
                $tServiciosAfiliacion .= ",";
            }
            $tServiciosAfiliacion .= "'" . $s . "'";
        }



        //
        if (date("md") <= '0331') {
            $anoant = date("Y") - 1;
            $query = " ((ultanoren = '" . (string)$anoant . "' and fecrenovacion >='" . (string)$anoant . "0101' and fecrenovacion <= '" .  (string)$anoant . "0331')";
            $query .= " or (ultanoren = '" . date("Y") . "'))";
        } else {
            $query = " ultanoren = '" . date("Y") . "'";
        }        
        $query .= " and ctrestmatricula = 'MA'";
        $query .= " and ctrafiliacion IN ('','0')";
        $query .= " and (organizacion = '01' or (organizacion > '02' and categoria = '1'))";
        $query .= " and (organizacion <> '12' and organizacion <> '14')";
        if (isset($_SESSION["entrada"]["fechamatriculamaxima"]) && $_SESSION["entrada"]["fechamatriculamaxima"] != '') {
            $query .= " and (fecmatricula <= '" . $_SESSION["entrada"]["fechamatriculamaxima"] . "')";
        }
        if (!empty($muns)) {
            $tmuns = '';
            foreach ($muns as $m) {
                if ($tmuns != '') {
                    $tmuns .= ",";
                }
                $tmuns .= "'" . $m . "'";
            }
            $query .= " and (muncom IN (" . $tmuns . "))";
        }
        if (!empty($acts)) {
            if (isset($acts[0])) {
                $query .= " and (acttot >= " . $acts[0] . ")";
            }
            if (isset($acts[1])) {
                $query .= " and (acttot <= " . $acts[1] . ")";
            }
        }
        if (!empty($orgs)) {
            $torgs = '';
            foreach ($orgs as $o) {
                if ($torgs != '') {
                    $torgs .= ",";
                }
                $torgs .= "'" . $o . "'";
            }
            $query .= " and (organizacion IN (" . $torgs . "))";
        }

        \logApi::general2($nameLog, '', 'Query :' . $query);
        // echo $query;
        //     
        if (!isset($_SESSION["entrada"]["extendido"]) || $_SESSION["entrada"]["extendido"] == '') {
            $_SESSION["entrada"]["extendido"] = 'N';
        }
        if (!isset($_SESSION["entrada"]["offset"]) || $_SESSION["entrada"]["offset"] == '') {
            $_SESSION["entrada"]["offset"] = 0;
        }
        if (!isset($_SESSION["entrada"]["limit"]) || $_SESSION["entrada"]["limit"] == '') {
            $_SESSION["entrada"]["limit"] = 0;
        }
        if ($_SESSION["entrada"]["offset"] == 0 && $_SESSION["entrada"]["limit"] == 0) {
            $regs = retornarRegistrosMysqliApi($mysqli, "mreg_est_inscritos", $query, "matricula", "matricula, idclase, numid, razonsocial, organizacion, dircom, telcom1, telcom2, telcom3, muncom, emailcom, acttot, ciiu1, fecmatricula, fecrenovacion, ultanoren");
        }
        if ($_SESSION["entrada"]["offset"] != 0 || $_SESSION["entrada"]["limit"] != 0) {
            $regs = retornarRegistrosMysqliApi($mysqli, "mreg_est_inscritos", $query, "matricula", "matricula, idclase, numid, razonsocial, organizacion, dircom, telcom1, telcom2, telcom3, muncom, emailcom, acttot, ciiu1, fecmatricula, fecrenovacion, ultanoren", $_SESSION["entrada"]["offset"], $_SESSION["entrada"]["limit"]);
        }

        \logApi::general2($nameLog, '', 'Inicio proceso - Seleccionados :' . count($regs));
        //
        $icon = 0;
        foreach ($regs as $r) {
            $icon++;
            \logApi::general2($nameLog, '', $icon . '.) ' . $r["matricula"]);
            $renglon = array();
            foreach ($r as $key => $valor) {
                $renglon[$key] = $valor;
            }

            // Vinculos
            if ($_SESSION["entrada"]["extendido"] == 'S') {
                $renglon["vinculos"] = array();
                $vins = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $r["matricula"] . "'", "vinculo, numid");
                if ($vins && !empty($vins)) {
                    foreach ($vins as $v) {
                        if ($v["estado"] == 'V') {
                            $avin = array();
                            $avin["idclase"] = $v["idclase"];
                            $avin["numid"] = $v["numid"];
                            $avin["nombre"] = $v["nombre"];
                            $avin["nombre1"] = $v["nom1"];
                            $avin["nombre2"] = $v["nom2"];
                            $avin["apellido1"] = $v["ape1"];
                            $avin["apellido2"] = $v["ape2"];
                            $avin["inscripcion"] = $v["idlibro"] . '-' . $v["numreg"];
                            $avin["fecha"] = $v["fecha"];
                            $avin["vinculo"] = $v["vinculo"];
                            $avin["descripcionvinculo"] = $vinculos[$v["vinculo"]]["descripcion"];
                            $avin["tipovinculo"] = $vinculos[$v["vinculo"]]["tipovinculo"];
                            $avin["descripcioncargo"] = $v["descargo"];
                            $renglon["vinculos"][] = $avin;
                        }
                    }
                }

                // Inscripciones
                $renglon["inscripciones"] = array();
                $inscs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "matricula='" . $r["matricula"] . "'", "fecharegistro, horaregistro");
                if ($inscs && !empty($inscs)) {
                    foreach ($inscs as $in) {
                        $eslibro = '';
                        $descripcionlibro = '';
                        $tipolibro = '';
                        if ($in["acto"] == '0003' || $in["acto"] == '0004') {
                            $eslibro = 'si';
                            $tipolibro = $in["tipolibro"];
                            $descripcionlibro = $in["descripcionlibro"];
                        } else {
                            if ($in["libro"] == 'RM07' || $in["libro"] == 'RE52') {
                                $eslibro = 'si';
                                $tipolibro = $in["tipolibro"];
                                $descripcionlibro = $in["descripcionlibro"];
                            }
                        }
                        $row = array(
                            'fechahora' => $in["fecharegistro"] . ' ' . $in["horaregistro"],
                            'libro' => $in["libro"],
                            'registro' => $in["registro"],
                            'dupli' => $in["dupli"],
                            'acto' => $in["acto"],
                            'nrodoc' => $in["numerodocumento"],
                            'fecdoc' => $in["fechadocumento"],
                            'origen' => $in["origendocumento"],
                            'noticia' => $in["noticia"]
                        );
                        if ($eslibro == 'si') {
                            $row["eslibrocomercio"] = $eslibro;
                            $row["tipolibro"] = $tipolibro;
                            $row["descripcionlibro"] = $descripcionlibro;
                            if ($tipolibro == '' || $tipolibro == 'F') {
                                $row["paginainicial"] = $in["paginainicial"];
                                $row["numerohojas"] = $in["numeropaginas"];
                            }
                            if ($tipolibro == 'E') {
                                $ffin = str_replace("-", "", \funcionesGenerales::calcularFechaInicial($in["fecharegistro"], 365, '+'));
                                $row["vigencia"] = 'Vigente desde el ' . $in["fecharegistro"] . ' al ' . $ffin;
                            }
                        }

                        $renglon["inscripciones"][] = $row;
                    }
                }

                // Historico de pagos
                $renglon["pagos"] = array();
                $pagos = encontrarHistoricoPagosMysqliApi($mysqli, $r["matricula"], $serviciosRenovacion, $serviciosAfiliacion, $serviciosMatricula);
                if ($pagos && !empty($pagos) && isset($pagos["renovacionanos"]) && !empty($pagos["renovacionanos"])) {
                    foreach ($pagos["renovacionanos"] as $pg) {
                        $renglon["pagos"][] = $pg;
                    }
                }
            }

            $_SESSION["jsonsalida"]["expedientes"][] = $renglon;
        }
        \logApi::general2($nameLog, '', 'Finalizo proceso');
        \logApi::general2($nameLog, '', '');
        // ********************************************************************** //
        // Retornar registros
        // ********************************************************************** // 
        $_SESSION["jsonsalida"]["fechahorafinalizacion"] = date("Ymd") . ' ' . date("His");
        $_SESSION["jsonsalida"]["totalregistros"] = count($regs);

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
