<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarRadicados {

    public function consultarRadicados(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/s3_v4_api.php');
        $resError = set_error_handler('myErrorHandler');

        //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["tipo"] = '';
        $_SESSION["jsonsalida"]["expediente"] = '';
        $_SESSION["jsonsalida"]["radicados"] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("tipo", false);
        $api->validarParametro("expediente", false);

        //
        $_SESSION["jsonsalida"]["tipo"]  = $_SESSION["entrada"]["tipo"];
        $_SESSION["jsonsalida"]["expediente"]  = $_SESSION["entrada"]["expediente"];
        
        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarRadicados', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // 
        if (strtoupper($_SESSION["entrada"]["tipo"]) != 'M' && strtoupper($_SESSION["entrada"]["tipo"]) != 'P') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Debe indicar M o P en tipo';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (trim((string) $_SESSION["entrada"]["expediente"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Debe indicar el expediente a buscar';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();
        
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // ********************************************************************** //
        // Construye arreglo de tipos documentales
        // ********************************************************************** // 
        $trd = array();
        $es = retornarRegistrosMysqliApi($mysqli, 'bas_tipodoc', "1=1", "idtipodoc");
        foreach ($es as $e) {
            $trd[$trdtemp['idtipodoc']] = array(
                'tiposirep' => $e['homologasirep'],
                'tipodigitalizacion' => $e['homologadigitalizacion']
            );
        }

        $cbs = array();
        if ($_SESSION["entrada"]["tipo"] == 'M') {
            $cbs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', "matricula='" . $_SESSION["entrada"]["expediente"] . "'", "fecharadicacion");
        }
        if ($_SESSION["entrada"]["tipo"] == 'P') {
            $cbs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', "proponente='" . $_SESSION["entrada"]["expediente"] . "'", "fecharadicacion");
        }



        // 
        if ($cbs === false || empty($cbs)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron radicados asociados';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        foreach ($cbs as $cb) {
            $arrcb = array();

            $arrcb['radicado'] = '';
            $arrcb['liquidacion'] = '';
            $arrcb['tipotramite'] = '';
            $arrcb['subtipotramite'] = '';
            $arrcb['operacion'] = '';
            $arrcb['recibo'] = '';
            $arrcb['operaciongob'] = '';
            $arrcb['recibogob'] = '';
            $arrcb['fecharadicacion'] = '';
            $arrcb['matricula'] = '';
            $arrcb['proponente'] = '';
            $arrcb['idclase'] = '';
            $arrcb['identificacion'] = '';
            $arrcb['nombre'] = '';
            $arrcb['estadofinal'] = '';
            $arrcb['usuariofinal'] = '';
            $arrcb['fechaestadofinal'] = '';
            $arrcb['horaestadofinal'] = '';
            $arrcb['sucursalfinal'] = '';
            $arrcb['actoreparto'] = '';
            $arrcb['tipodoc'] = '';
            $arrcb['tipodocsirep'] = '';
            $arrcb['tipodocdigitalizacion'] = '';
            $arrcb['tipoingreso'] = '';
            $arrcb['numerodoc'] = '';
            $arrcb['origendoc'] = '';
            $arrcb['fechadoc'] = '';
            $arrcb['municipiodoc'] = '';
            $arrcb['numerointernorue'] = '';
            $arrcb['numerounicorue'] = '';
            $arrcb['tipogasto'] = '';
            $arrcb['cantidadfolios'] = '';
            $arrcb['cantidadhojas'] = '';
            $arrcb['firmadoelectronicamente'] = '';
            $arrcb['firmadomanuscrita'] = '';
            $arrcb['estados'] = array();
            $arrcb['servicios'] = array();

            //
            $resy = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "numeroradicacion='" . $cb["codigobarras"] . "'");
            if ($resy === false || empty($resy)) {
                $cb['liquidacion'] = '';
                $cb['tipotramite'] = '';
                $cb['numerorecibo'] = '';
                $cb['numeoperacion'] = '';
                $cb['numerorecibogob'] = '';
                $cb['numeoperaciongob'] = '';
            } else {
                $cb['liquidacion'] = $resy["idliquidacion"];
                $cb['tipotramite'] = $resy["tipotramite"];
                $cb['numerorecibo'] = $resy["numerorecibo"];
                $cb['numeoperacion'] = $resy["numeoperacion"];
                $cb['numerorecibogob'] = $resy["numerorecibogob"];
                $cb['numeoperaciongob'] = $resy["numeoperaciongob"];
            }

            // ********************************************************************** //
            // Verifica si el trámite fue pagado en forma electrónica o manuscrita
            // Siempre y cuando la liquidación sea de renovación
            // ********************************************************************** // 
            if ($resy) {
                if ($resy["tipotramite"] == 'renovacionmatricula' || $resy["tipotramite"] == 'renovacionesadl') {
                    $arrcb['firmadoelectronicamente'] = $resy["firmadoelectronicamente"];
                    $arrcb['firmadomanuscrita'] = $resy["firmadomanuscrita"];
                    if ($arrcb['firmadomanuscrita'] == '') {
                        $temxx = retornarRegistrosMysqliApi($mysqli, 'mreg_firmadoelectronico_log', "idliquidacion=" . $resy["idliquidacion"], "fecha,hora");
                        if ($temxx && !empty($temxx)) {
                            foreach ($temxx as $tx2) {
                                if (substr($tx2["respuesta"], 0, 25) == 'Firmo el tramite en forma') {
                                    $arrcb['firmadomanuscrita'] = 'si';
                                }
                            }
                        }
                    }
                }
            }

            // ********************************************************************** //
            // Retornar el radicado recuperado
            // ********************************************************************** // 
            $arrcb['radicado'] = $cb['codigobarras'];
            $arrcb['liquidacion'] = $cb['liquidacion'];
            $arrcb['tipotramite'] = $cb['tipotramite'];
            if (trim($cb['tipotramite']) == 'inscripciondocumentos') {
                $resx = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_campos', "idliquidacion=" . $cb["liquidacion"] . " and campo='subtipotramite'");
                if ($resx && !empty($resx)) {
                    $arrcb['subtipotramite'] = $resx['contenido'];
                }
            } else {
                $arrcb['tipotramite'] = $cb['tipotramite'];
            }

            //
            $arrcb['operacion'] = $cb['operacion'];
            $arrcb['recibo'] = $cb['recibo'];

            $arrcb['operaciongob'] = $cb['numeroperaciongob'];
            $arrcb['recibogob'] = $cb['numerorecibogob'];

            $arrcb['fecharadicacion'] = $cb['fecharadicacion'];
            $arrcb['matricula'] = $cb['matricula'];
            $arrcb['proponente'] = $cb['proponente'];
            $arrcb['idclase'] = $cb['idclase'];
            $arrcb['identificacion'] = $cb['numid'];
            $arrcb['nombre'] = $cb['nombre'];
            $arrcb['estadofinal'] = $cb['estadofinal'];
            $arrcb['usuariofinal'] = $cb['operadorfinal'];
            $arrcb['fechaestadofinal'] = $cb['fechaestadofinal'];
            $arrcb['horaestadofinal'] = $cb['horaestadofinal'];
            $arrcb['sucursalfinal'] = $cb['sucursalfinal'];
            $arrcb['procedereingreso'] = '';

            $arrcb['actoreparto'] = $cb['actoreparto'];
            $arrcb['tipodoc'] = $cb['tipdoc'];

            if (isset($trd[$cb['tipdoc']])) {
                $arrcb['tipodocsirep'] = $trd[$cb['tipdoc']]["tiposirep"];
                $arrcb['tipodocdigitalizacion'] = $trd[$cb['tipdoc']]["tipodigitalizacion"];
            }
            $arrcb['numerodoc'] = $cb['numdoc'];
            $arrcb['origendoc'] = $cb['oridoc'];
            $arrcb['fechadoc'] = $cb['fecdoc'];
            $arrcb['municipiodoc'] = $cb['mundoc'];
            $arrcb['numerointernorue'] = $cb['numerointernorue'];
            $arrcb['numerounicorue'] = $cb['numerounicorue'];

            //
            $arrcb['cumplorequisitosbenley1780'] = '';
            $arrcb['mantengorequisitosbenley1780'] = '';
            $arrcb['renunciobeneficiosley1780'] = '';
            $arrcb['multadoponal'] = '';
            $arrcb['controlactividadaltoimpacto'] = '';
            $arrcb['verificacionsoportes'] = '';

            //
            if ($resy && !empty($resy)) {
                if (trim((string)$resy["cumplorequisitosbenley1780"]) != '') {
                    if (strtoupper($resy["cumplorequisitosbenley1780"]) == 'S' || strtoupper($resy["cumplorequisitosbenley1780"]) == 'SI') {
                        $arrcb['cumplorequisitosbenley1780'] = 'S';
                    } else {
                        $arrcb['cumplorequisitosbenley1780'] = 'N';
                    }
                }
                if (trim((string)$resy["mantengorequisitosbenley1780"]) != '') {
                    if (strtoupper($resy["mantengorequisitosbenley1780"]) == 'S' || strtoupper($resy["mantengorequisitosbenley1780"]) == 'SI') {
                        $arrcb['mantengorequisitosbenley1780'] = 'S';
                    } else {
                        $arrcb['mantengorequisitosbenley1780'] = 'N';
                    }
                }
                if (trim((string)$resy["renunciobeneficiosley1780"]) != '') {
                    if (strtoupper($resy["renunciobeneficiosley1780"]) == 'S' || strtoupper($resy["renunciobeneficiosley1780"]) == 'SI') {
                        $arrcb['renunciobeneficiosley1780'] = 'S';
                    } else {
                        $arrcb['renunciobeneficiosley1780'] = 'N';
                    }
                }
                if (trim((string)$resy["multadoponal"]) != '') {
                    if (strtoupper($resy["multadoponal"]) == 'S' || strtoupper($resy["multadoponal"]) == 'SI') {
                        $arrcb['multadoponal'] = 'S';
                    } else {
                        $arrcb['multadoponal'] = 'N';
                    }
                }
                if (trim((string)$resy["controlactividadaltoimpacto"]) != '') {
                    if (strtoupper($resy["controlactividadaltoimpacto"]) == 'S' || strtoupper($resy["controlactividadaltoimpacto"]) == 'SI') {
                        $arrcb['controlactividadaltoimpacto'] = 'S';
                    } else {
                        $arrcb['controlactividadaltoimpacto'] = 'N';
                    }
                }
            }

            if (strtoupper($cb["verificacionsoportes"]) == 'S' || strtoupper($cb["verificacionsoportes"]) == 'SI') {
                $arrcb['verificacionsoportes'] = 'S';
            } else {
                $arrcb['verificacionsoportes'] = 'N';
            }

            $arrcb['cantidadfolios'] = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_campos', "idliquidacion=" . $cb["liquidacion"] . " and campo='cantidadfolios'", "contenido");
            $arrcb['cantidadhojas'] = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_campos', "idliquidacion=" . $cb["liquidacion"] . " and campo='cantidadhojas'", "contenido");

            // ********************************************************************** //
            // Control si la identificación y el tipo no hacen parte del código de barras (Constituciones y matriculas)
            // ********************************************************************** // 
            if (trim((string)$arrcb['identificacion']) == '') {
                if ($arrcb['matricula'] != '') {
                    $arrTemIns = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $arrcb['matricula'] . "'");
                    $_SESSION['jsonsalida']['idclase'] = $arrTemIns['idclase'];
                    $_SESSION['jsonsalida']['identificacion'] = $arrTemIns['numid'];
                    unset($arrTemIns);
                }
            }

            // ********************************************************************** //
            // Leer el recibo de caja 
            // ********************************************************************** // 
            if (trim((string)$arrcb['recibo']) != '') {
                $arrTemRec = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', "recibo='" . $arrcb['recibo'] . "'");
                $arrcb['tipogasto'] = $arrTemRec['tipogasto'];
                unset($arrTemRec);
            }

            // ********************************************************************** //
            // Retornar estados del radicado
            // ********************************************************************** // 
            $arrTemEst = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_documentos', "codigobarras='" . $arrcb["radicado"] . "'", "fecha ASC, hora ASC");
            if (!empty($arrTemEst)) {
                foreach ($arrTemEst as $estadot) {
                    $estado = array();
                    $estado['fecha'] = trim((string)$estadot['fecha']);
                    $estado['hora'] = trim((string)$estadot['hora']);
                    $estado['estado'] = trim((string)$estadot['estado']);
                    $estado['usuariofinal'] = trim((string)$estadot['operador']);
                    $estados[] = $estado;
                }
                $arrcb['estados'] = $estados;
            }

            // ********************************************************************** //
            // En caso de devuelto verificar si procede o no el reingreso
            // ********************************************************************** // 
            if ($arrcb['estadofinal'] == '05' || $arrcb['estadofinal'] == '06' || $arrcb['estadofinal'] == '07') {
                $arrTemDev = retornarRegistrosMysqliApi($mysqli, 'mreg_devoluciones_nueva', "idradicacion='" . $arrcb["radicado"] . "'", "fechadevolucion asc, horadevolucion asc");
                if (!empty($arrTemDev)) {
                    foreach ($arrTemDev as $dev) {
                        if ($dev["estado"] == '2') {
                            if ($dev["tipodevolucion"] == 'R') {
                                $arrcb['procedereingreso'] = 'S';
                            } else {
                                $arrcb['procedereingreso'] = 'N';
                            }
                        }
                    }
                }
            }

            // ********************************************************************** //
            // Retornar los servicios del recibo asociado al radicado
            // ********************************************************************** // 
            $matest = '';
            if (trim($arrcb["recibo"]) != '') {
                $detrec = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "numerorecibo='" . $arrcb["recibo"] . "'", "id");
                if (!empty($detrec)) {
                    $iServs = 0;
                    foreach ($detrec as $servicioT) {
                        $temS = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $servicioT['servicio'] . "'");
                        $servicio = array();
                        $servicio['servicio'] = $servicioT['servicio'];
                        $servicio['nservicio'] = $temS["nombre"];
                        if ($temS["tipoingreso"] >= '21' && $temS["tipoingreso"] <= '30') {
                            $servicio['matricula'] = '';
                            $servicio['proponente'] = $servicioT['matricula'];
                        } else {
                            $servicio['proponente'] = '';
                            $servicio['matricula'] = $servicioT['matricula'];
                        }
                        $servicio['identificacion'] = $servicioT['identificacion'];
                        $servicio['nombre'] = $servicioT['nombre'];
                        $servicio['cantidad'] = doubleval($servicioT['cantidad']);
                        $servicio['valorbase'] = doubleval($servicioT['base']);
                        $servicio['valorservicio'] = doubleval($servicioT['valor']);
                        $servicio['ano'] = $servicioT['anorenovacion'];
                        $servicios[] = $servicio;
                        $iServs++;
                        if ($iServs == 1) {
                            $arrcb['tipoingreso'] = $temS["tipoingreso"];
                            unset($temS);
                        }
                        if ($servicio['servicio'] == '01020102' || $servicio['servicio'] == '01020103') {
                            $matest = $servicio['matricula'];
                        }
                    }
                    $arrcb['servicios'] = $servicios;
                }
            }

            // ********************************************************************** //
            // Retornar imágenes del radicado (todas)
            // ********************************************************************** // 
            $_SESSION["jsonsalida"]["radicados"][] = $arrcb;
        }

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
