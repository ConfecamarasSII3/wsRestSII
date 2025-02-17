<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarRadicado {

    public function consultarRadicado(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/s3_v4_api.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['radicado'] = '';
        $_SESSION['jsonsalida']['liquidacion'] = '';
        $_SESSION['jsonsalida']['tipotramite'] = '';
        $_SESSION['jsonsalida']['subtipotramite'] = '';
        $_SESSION['jsonsalida']['operacion'] = '';
        $_SESSION['jsonsalida']['recibo'] = '';
        $_SESSION['jsonsalida']['operaciongob'] = '';
        $_SESSION['jsonsalida']['recibogob'] = '';
        $_SESSION['jsonsalida']['fecharadicacion'] = '';
        $_SESSION['jsonsalida']['matricula'] = '';
        $_SESSION['jsonsalida']['proponente'] = '';
        $_SESSION['jsonsalida']['idclase'] = '';
        $_SESSION['jsonsalida']['identificacion'] = '';
        $_SESSION['jsonsalida']['nombre'] = '';
        $_SESSION['jsonsalida']['direccion'] = '';
        $_SESSION['jsonsalida']['municipio'] = '';
        $_SESSION['jsonsalida']['email'] = '';
        $_SESSION['jsonsalida']['telefono'] = '';
        $_SESSION['jsonsalida']['organizacion'] = '';
        $_SESSION['jsonsalida']['categoria'] = '';
        $_SESSION['jsonsalida']['tiporegistro'] = '';
        $_SESSION['jsonsalida']['bandeja'] = '';
        $_SESSION['jsonsalida']['idclasepagador'] = '';
        $_SESSION['jsonsalida']['numidpagador'] = '';
        $_SESSION['jsonsalida']['nombrepagador'] = '';
        $_SESSION['jsonsalida']['direccionpagador'] = '';
        $_SESSION['jsonsalida']['municipiopagador'] = '';
        $_SESSION['jsonsalida']['telefonopagador'] = '';
        $_SESSION['jsonsalida']['emailpagador'] = '';
        $_SESSION['jsonsalida']['estadofinal'] = '';
        $_SESSION['jsonsalida']['usuariofinal'] = '';
        $_SESSION['jsonsalida']['fechaestadofinal'] = '';
        $_SESSION['jsonsalida']['horaestadofinal'] = '';
        $_SESSION['jsonsalida']['sucursalfinal'] = '';
        $_SESSION['jsonsalida']['actoreparto'] = '';
        $_SESSION['jsonsalida']['tipodoc'] = '';
        $_SESSION['jsonsalida']['tipodocsirep'] = '';
        $_SESSION['jsonsalida']['tipodocdigitalizacion'] = '';
        $_SESSION['jsonsalida']['tipoingreso'] = '';
        $_SESSION['jsonsalida']['numerodoc'] = '';
        $_SESSION['jsonsalida']['origendoc'] = '';
        $_SESSION['jsonsalida']['fechadoc'] = '';
        $_SESSION['jsonsalida']['municipiodoc'] = '';
        $_SESSION['jsonsalida']['numerointernorue'] = '';
        $_SESSION['jsonsalida']['numerounicorue'] = '';
        $_SESSION['jsonsalida']['tipogasto'] = '';
        $_SESSION['jsonsalida']['cantidadfolios'] = '';
        $_SESSION['jsonsalida']['cantidadhojas'] = '';
        $_SESSION['jsonsalida']['firmadoelectronicamente'] = '';
        $_SESSION['jsonsalida']['firmadomanuscrita'] = '';
        $estados = array();
        $_SESSION['jsonsalida']['estados'] = $estados;
        $servicios = array();
        $_SESSION['jsonsalida']['servicios'] = $servicios;
        $imagenes = array();
        $_SESSION['jsonsalida']['imagenes'] = $imagenes;

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("radicado", false);
        $api->validarParametro("liquidacion", false);

        //
        $radicado = ltrim($_SESSION["entrada"]["radicado"], "0");
        $liquidacion = ltrim($_SESSION["entrada"]["liquidacion"], "0");

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarRadicado', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // 
        if ($radicado == '' && $liquidacion == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Debe indicar el radicado o la liquidación a consultar';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

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
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // ********************************************************************** //
        // Construye arreglo de tipos documentales
        // ********************************************************************** // 
        $trd = array();
        $query = "SELECT * from bas_tipodoc where 1=1 order by idtipodoc";
        $mysqli->set_charset("utf8");
        $resQueryTD = $mysqli->query($query);
        if (!empty($resQueryTD)) {
            while ($trdtemp = $resQueryTD->fetch_array(MYSQLI_ASSOC)) {
                $trd[$trdtemp['idtipodoc']] = array(
                    'tiposirep' => $trdtemp['homologasirep'],
                    'tipodigitalizacion' => $trdtemp['homologadigitalizacion']
                );
            }
        }
        $resQueryTD->free();

        // ********************************************************************** //
        // Consulta el radicado
        // ********************************************************************** // 
        if ($liquidacion != '' && $radicado == '') {
            $tempLiq = retornarRegistroMysqliApi($mysqli, "mreg_liquidacion", "idliquidacion=" . $liquidacion . " or numerorecuperacion='" . $liquidacion . "'");
            if ($tempLiq && !empty($tempLiq)) {
                if ($tempLiq["numeroradicacion"] != '') {
                    $radicado = $tempLiq["numeroradicacion"];
                }
            }
        }

        // 
        if ($radicado == '' && $liquidacion != '') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La liquidación reportada no está asociada a ningún radicado (código de barras)';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $radicadoTemp = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $radicado . "'");
        if ($radicadoTemp === false || empty($radicadoTemp)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Código de barras - radicado no localizado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $resy = false;
        if (ltrim(trim($radicadoTemp["codigobarras"]), "0") != '') {
            $resy = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "numeroradicacion='" . $radicadoTemp["codigobarras"] . "'");
        }
        if ($resy === false || empty($resy)) {
            $radicadoTemp['liquidacion'] = '';
            $radicadoTemp['tipotramite'] = '';
            $radicadoTemp['numerorecibo'] = '';
            $radicadoTemp['numeroperacion'] = '';
            $radicadoTemp['numerorecibogob'] = '';
            $radicadoTemp['numeroperaciongob'] = '';
        } else {
            $radicadoTemp['liquidacion'] = $resy["idliquidacion"];
            $radicadoTemp['tipotramite'] = $resy["tipotramite"];
            $radicadoTemp['numerorecibo'] = $resy["numerorecibo"];
            $radicadoTemp['numeroperacion'] = $resy["numeroperacion"];
            $radicadoTemp['numerorecibogob'] = $resy["numerorecibogob"];
            $radicadoTemp['numeroperaciongob'] = $resy["numeroperaciongob"];
        }

        // ********************************************************************** //
        // Verifica si el trámite fue pagado en forma electrónica o manuscrita
        // Siempre y cuando la liquidación sea de renovación
        // ********************************************************************** // 
        if ($resy) {
            if ($resy["tipotramite"] == 'renovacionmatricula' || $resy["tipotramite"] == 'renovacionesadl') {
                $_SESSION['jsonsalida']['firmadoelectronicamente'] = $resy["firmadoelectronicamente"];
                $_SESSION['jsonsalida']['firmadomanuscrita'] = $resy["firmadomanuscrita"];
                if ($_SESSION['jsonsalida']['firmadomanuscrita'] == '') {
                    $temxx = retornarRegistrosMysqliApi($mysqli, 'mreg_firmadoelectronico_log', "idliquidacion=" . $resy["idliquidacion"], "fecha,hora");
                    if ($temxx && !empty($temxx)) {
                        foreach ($temxx as $tx2) {
                            if (substr($tx2["respuesta"], 0, 25) == 'Firmo el tramite en forma') {
                                $_SESSION['jsonsalida']['firmadomanuscrita'] = 'si';
                            }
                        }
                    }
                }
            }
        }

        // ********************************************************************** //
        // Retornar el radicado recuperado
        // ********************************************************************** // 
        $_SESSION['jsonsalida']['radicado'] = trim((string) $radicadoTemp['codigobarras']);
        $_SESSION['jsonsalida']['liquidacion'] = trim((string) $radicadoTemp['liquidacion']);
        $_SESSION['jsonsalida']['tipotramite'] = trim((string) $radicadoTemp['tipotramite']);
        if (trim($radicadoTemp['tipotramite']) == 'inscripciondocumentos') {
            $resx = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_campos', "idliquidacion=" . $radicadoTemp["liquidacion"] . " and campo='subtipotramite'");
            if ($resx && !empty($resx)) {
                $_SESSION['jsonsalida']['subtipotramite'] = trim((string) $resx['contenido']);
            }
        } else {
            $_SESSION['jsonsalida']['tipotramite'] = trim((string) $radicadoTemp['tipotramite']);
        }

        //
        $_SESSION['jsonsalida']['operacion'] = trim((string) $radicadoTemp['operacion']);
        $_SESSION['jsonsalida']['recibo'] = trim((string) $radicadoTemp['recibo']);

        $_SESSION['jsonsalida']['operaciongob'] = trim((string) $radicadoTemp['numeroperaciongob']);
        $_SESSION['jsonsalida']['recibogob'] = trim((string) $radicadoTemp['numerorecibogob']);

        $_SESSION['jsonsalida']['fecharadicacion'] = trim((string) $radicadoTemp['fecharadicacion']);
        $_SESSION['jsonsalida']['matricula'] = trim((string) $radicadoTemp['matricula']);
        $_SESSION['jsonsalida']['proponente'] = trim((string) $radicadoTemp['proponente']);
        $_SESSION['jsonsalida']['idclase'] = trim((string) $radicadoTemp['idclase']);
        $_SESSION['jsonsalida']['identificacion'] = trim((string) $radicadoTemp['numid']);
        $_SESSION['jsonsalida']['nombre'] = trim((string) $radicadoTemp['nombre']);
        if ($_SESSION['jsonsalida']['matricula'] != '') {
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $_SESSION['jsonsalida']['matricula'] . "'");
            if ($exp && !empty($exp)) {
                $_SESSION['jsonsalida']['idclase'] = $exp["idclase"];
                $_SESSION['jsonsalida']['numid'] = $exp["numid"];
                $_SESSION['jsonsalida']['direccion'] = $exp["dircom"];
                $_SESSION['jsonsalida']['municipio'] = $exp["muncom"];
                $_SESSION['jsonsalida']['email'] = $exp["emailcom"];
                $_SESSION['jsonsalida']['telefono'] = $exp["telcom1"];
                $_SESSION['jsonsalida']['organizacion'] = $exp["organizacion"];
                $_SESSION['jsonsalida']['categoria'] = $exp["categoria"];
                $_SESSION['jsonsalida']['tiporegistro'] = 'RegMer';
                if ($exp["organizacion"] == '12' || $exp["organizacion"] == '14') {
                    if ($exp["categoria"] == '1') {
                        $_SESSION['jsonsalida']['tiporegistro'] = 'RegEsadl';
                    }
                }
            }
        }
        if ($_SESSION['jsonsalida']['proponente'] != '') {
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "proponente='" . $_SESSION['jsonsalida']['proponente'] . "'");
            if ($exp && !empty($exp)) {
                $_SESSION['jsonsalida']['idclase'] = $exp["idclase"];
                $_SESSION['jsonsalida']['numid'] = $exp["numid"];
                $_SESSION['jsonsalida']['direccion'] = $exp["dircom"];
                $_SESSION['jsonsalida']['municipio'] = $exp["muncom"];
                $_SESSION['jsonsalida']['email'] = $exp["emailcom"];
                $_SESSION['jsonsalida']['telefono'] = $exp["telcom1"];
                $_SESSION['jsonsalida']['organizacion'] = $exp["organizacion"];
                $_SESSION['jsonsalida']['categoria'] = $exp["categoria"];
                $_SESSION['jsonsalida']['tiporegistro'] = 'RegPro';
            }
        }

        $_SESSION['jsonsalida']['estadofinal'] = trim((string) $radicadoTemp['estadofinal']);
        $_SESSION['jsonsalida']['usuariofinal'] = trim((string) $radicadoTemp['operadorfinal']);
        $_SESSION['jsonsalida']['fechaestadofinal'] = trim((string) $radicadoTemp['fechaestadofinal']);
        $_SESSION['jsonsalida']['horaestadofinal'] = trim((string) $radicadoTemp['horaestadofinal']);
        $_SESSION['jsonsalida']['sucursalfinal'] = trim((string) $radicadoTemp['sucursalfinal']);
        $_SESSION['jsonsalida']['procedereingreso'] = '';

        $_SESSION['jsonsalida']['actoreparto'] = trim((string) $radicadoTemp['actoreparto']);
        $act = retornarRegistroMysqliApi($mysqli, 'mreg_codrutas', "id='" . $_SESSION['jsonsalida']['actoreparto'] . "'");
        if ($act && !empty($act)) {
            $_SESSION['jsonsalida']['bandeja'] = $act["bandeja"];
        }

        if ($act && !empty($act)) {
            if ($act["tipo"] == 'ME' || $act["tipo"] == 'ES') {
                if ($_SESSION['jsonsalida']['matricula'] == '') {
                    $for = retornarRegistroMysqliApi($mysqli, 'mreg_radicacionesdatos', "idradicacion='" . $_SESSION['jsonsalida']['radicado'] . "'", "secuencia");
                    if ($for && !empty($for)) {
                        if ($for["xml"] != '') {
                            $forx = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $for["xml"]);
                            $_SESSION['jsonsalida']['direccion'] = $forx["dircom"];
                            $_SESSION['jsonsalida']['municipio'] = $forx["muncom"];
                            $_SESSION['jsonsalida']['email'] = $forx["emailcom"];
                            $_SESSION['jsonsalida']['telefono'] = $forx["telcom1"];
                            $_SESSION['jsonsalida']['organizacion'] = $forx["organizacion"];
                            $_SESSION['jsonsalida']['categoria'] = $forx["categoria"];
                            if ($act["tipo"] == 'ME') {
                                $_SESSION['jsonsalida']['tiporegistro'] = 'RegMer';
                            }
                            if ($act["tipo"] == 'ES') {
                                $_SESSION['jsonsalida']['tiporegistro'] = 'RegEsadl';
                            }                            
                        }
                    }
                }
            }
        }

        //
        $_SESSION['jsonsalida']['tipodoc'] = trim((string) $radicadoTemp['tipdoc']);

        if (isset($trd[$radicadoTemp['tipdoc']])) {
            $_SESSION['jsonsalida']['tipodocsirep'] = $trd[$radicadoTemp['tipdoc']]["tiposirep"];
            $_SESSION['jsonsalida']['tipodocdigitalizacion'] = $trd[$radicadoTemp['tipdoc']]["tipodigitalizacion"];
        }
        $_SESSION['jsonsalida']['numerodoc'] = trim((string) $radicadoTemp['numdoc']);
        $_SESSION['jsonsalida']['origendoc'] = trim((string) $radicadoTemp['oridoc']);
        $_SESSION['jsonsalida']['fechadoc'] = trim((string) $radicadoTemp['fecdoc']);
        $_SESSION['jsonsalida']['municipiodoc'] = trim((string) $radicadoTemp['mundoc']);
        $_SESSION['jsonsalida']['numerointernorue'] = trim((string) $radicadoTemp['numerointernorue']);
        $_SESSION['jsonsalida']['numerounicorue'] = trim((string) $radicadoTemp['numerounicorue']);

        //
        $_SESSION['jsonsalida']['cumplorequisitosbenley1780'] = '';
        $_SESSION['jsonsalida']['mantengorequisitosbenley1780'] = '';
        $_SESSION['jsonsalida']['renunciobeneficiosley1780'] = '';
        $_SESSION['jsonsalida']['multadoponal'] = '';
        $_SESSION['jsonsalida']['controlactividadaltoimpacto'] = '';
        $_SESSION['jsonsalida']['verificacionsoportes'] = '';

        //
        if ($resy && !empty($resy)) {
            if (trim((string) $resy["cumplorequisitosbenley1780"]) != '') {
                if (strtoupper($resy["cumplorequisitosbenley1780"]) == 'S' || strtoupper($resy["cumplorequisitosbenley1780"]) == 'SI') {
                    $_SESSION['jsonsalida']['cumplorequisitosbenley1780'] = 'S';
                } else {
                    $_SESSION['jsonsalida']['cumplorequisitosbenley1780'] = 'N';
                }
            }
            if (trim((string) $resy["mantengorequisitosbenley1780"]) != '') {
                if (strtoupper($resy["mantengorequisitosbenley1780"]) == 'S' || strtoupper($resy["mantengorequisitosbenley1780"]) == 'SI') {
                    $_SESSION['jsonsalida']['mantengorequisitosbenley1780'] = 'S';
                } else {
                    $_SESSION['jsonsalida']['mantengorequisitosbenley1780'] = 'N';
                }
            }
            if (trim((string) $resy["renunciobeneficiosley1780"]) != '') {
                if (strtoupper($resy["renunciobeneficiosley1780"]) == 'S' || strtoupper($resy["renunciobeneficiosley1780"]) == 'SI') {
                    $_SESSION['jsonsalida']['renunciobeneficiosley1780'] = 'S';
                } else {
                    $_SESSION['jsonsalida']['renunciobeneficiosley1780'] = 'N';
                }
            }
            if (trim((string) $resy["multadoponal"]) != '') {
                if (strtoupper($resy["multadoponal"]) == 'S' || strtoupper($resy["multadoponal"]) == 'SI') {
                    $_SESSION['jsonsalida']['multadoponal'] = 'S';
                } else {
                    $_SESSION['jsonsalida']['multadoponal'] = 'N';
                }
            }
            if (trim((string) $resy["controlactividadaltoimpacto"]) != '') {
                if (strtoupper($resy["controlactividadaltoimpacto"]) == 'S' || strtoupper($resy["controlactividadaltoimpacto"]) == 'SI') {
                    $_SESSION['jsonsalida']['controlactividadaltoimpacto'] = 'S';
                } else {
                    $_SESSION['jsonsalida']['controlactividadaltoimpacto'] = 'N';
                }
            }
        }

        if (strtoupper($radicadoTemp["verificacionsoportes"]) == 'S' || strtoupper($radicadoTemp["verificacionsoportes"]) == 'SI') {
            $_SESSION['jsonsalida']['verificacionsoportes'] = 'S';
        } else {
            $_SESSION['jsonsalida']['verificacionsoportes'] = 'N';
        }
        // 2018-05-18: JINT
        $_SESSION['jsonsalida']['cantidadfolios'] = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_campos', "idliquidacion=" . $radicadoTemp["liquidacion"] . " and campo='cantidadfolios'", "contenido");
        $_SESSION['jsonsalida']['cantidadhojas'] = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_campos', "idliquidacion=" . $radicadoTemp["liquidacion"] . " and campo='cantidadhojas'", "contenido");

        /*
          // ********************************************************************** //
          // Control si la identificación y el tipo no hacen parte del código de barras (Constituciones y matriculas)
          // ********************************************************************** //
          if (trim($_SESSION['jsonsalida']['identificacion']) == '') {
          if ($_SESSION['jsonsalida']['matricula'] != '') {
          $arrTemIns = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $_SESSION['jsonsalida']['matricula'] . "'");
          $_SESSION['jsonsalida']['idclase'] = trim((string) $arrTemIns['idclase']);
          $_SESSION['jsonsalida']['identificacion'] = trim((string) $arrTemIns['numid']);
          unset($arrTemIns);
          }
          }
         */

        // ********************************************************************** //
        // Leer el recibo de caja 
        // ********************************************************************** // 
        if (trim((string) $_SESSION['jsonsalida']['recibo']) != '') {
            $arrTemRec = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', "recibo='" . $_SESSION['jsonsalida']['recibo'] . "'");
            $_SESSION['jsonsalida']['tipogasto'] = trim((string) $arrTemRec['tipogasto']);
            $_SESSION['jsonsalida']['emailpagador'] = $arrTemRec['email'];
            $_SESSION['jsonsalida']['nombrepagador'] = $arrTemRec['razonsocial'];
            $_SESSION['jsonsalida']['numidpagador'] = $arrTemRec['identificacion'];
            $_SESSION['jsonsalida']['idclasepagador'] = $arrTemRec['tipoidentificacion'];
            $_SESSION['jsonsalida']['telefonopagador'] = $arrTemRec['telefono1'];
            if ($_SESSION['jsonsalida']['telefonopagador'] == '') {
                $_SESSION['jsonsalida']['telefonopagador'] = $arrTemRec['telefono2'];
            }
            $_SESSION['jsonsalida']['direccionpagador'] = $arrTemRec['direccion'];
            $_SESSION['jsonsalida']['municipiopagador'] = $arrTemRec['municipio'];
            unset($arrTemRec);
        }

        // ********************************************************************** //
        // Retornar estados del radicado
        // ********************************************************************** // 
        $arrTemEst = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_documentos', "codigobarras='" . $radicado . "'", "fecha ASC, hora ASC");
        if (!empty($arrTemEst)) {
            foreach ($arrTemEst as $estadot) {
                $estado = array();
                $estado['fecha'] = trim((string) $estadot['fecha']);
                $estado['hora'] = trim((string) $estadot['hora']);
                $estado['estado'] = trim((string) $estadot['estado']);
                $estado['usuariofinal'] = trim((string) $estadot['operador']);
                $estados[] = $estado;
            }
            $_SESSION['jsonsalida']['estados'] = $estados;
        }

        // ********************************************************************** //
        // En caso de devuelto verificar si procede o no el reingreso
        // ********************************************************************** // 
        if ($_SESSION['jsonsalida']['estadofinal'] == '05' || $_SESSION['jsonsalida']['estadofinal'] == '06') {
            $arrTemDev = retornarRegistrosMysqliApi($mysqli, 'mreg_devoluciones_nueva', "idradicacion='" . $radicado . "'", "fechadevolucion asc, horadevolucion asc");
            if (!empty($arrTemDev)) {
                foreach ($arrTemDev as $dev) {
                    if ($dev["estado"] == '2') {
                        if ($dev["tipodevolucion"] == 'R') {
                            $_SESSION['jsonsalida']['procedereingreso'] = 'S';
                        } else {
                            $_SESSION['jsonsalida']['procedereingreso'] = 'N';
                        }
                    }
                }
            }
        }

        // ********************************************************************** //
        // Retornar los servicios del recibo asociado al radicado
        // ********************************************************************** // 
        $matest = '';
        if (trim($_SESSION["jsonsalida"]["recibo"]) != '') {
            $detrec = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "numerorecibo='" . $_SESSION["jsonsalida"]["recibo"] . "'", "id");
            if (!empty($detrec)) {
                $iServs = 0;
                foreach ($detrec as $servicioT) {
                    $temS = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $servicioT['servicio'] . "'");
                    $servicio = array();
                    $servicio['servicio'] = trim((string) $servicioT['servicio']);
                    $servicio['nservicio'] = $temS["nombre"];
                    if ($temS["tipoingreso"] >= '21' && $temS["tipoingreso"] <= '30') {
                        $servicio['matricula'] = '';
                        $servicio['proponente'] = trim((string) $servicioT['matricula']);
                        if ($servicio['proponente'] != '') {
                            $exp1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "proponente='" . $servicio['proponente'] . "'");
                        } else {
                            $exp1 = false;
                        }
                    } else {
                        $servicio['proponente'] = '';
                        $servicio['matricula'] = trim((string) $servicioT['matricula']);
                        if ($servicio['matricula'] != '') {
                            $exp1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $servicio['matricula'] . "'");
                        } else {
                            $exp1 = false;
                        }
                    }
                    if ($exp1) {
                        $servicio['identificacion'] = $exp1['numid'];
                        $servicio['nombre'] = $exp1['razonsocial'];
                    } else {
                        $servicio['identificacion'] = trim((string) $servicioT['identificacion']);
                        $servicio['nombre'] = trim((string) $servicioT['nombre']);
                        
                    }
                    $servicio['cantidad'] = doubleval($servicioT['cantidad']);
                    $servicio['valorbase'] = doubleval($servicioT['base']);
                    $servicio['valorservicio'] = doubleval($servicioT['valor']);
                    $servicio['ano'] = trim((string) $servicioT['anorenovacion']);
                    $servicios[] = $servicio;
                    $iServs++;
                    if ($iServs == 1) {
                        $_SESSION['jsonsalida']['tipoingreso'] = $temS["tipoingreso"];
                        unset($temS);
                    }
                    if ($servicio['servicio'] == '01020102' || $servicio['servicio'] == '01020103') {
                        $matest = $servicio['matricula'];
                    }
                }
                $_SESSION['jsonsalida']['servicios'] = $servicios;
            }
        }

        // ********************************************************************** //
        // Retornar imágenes del radicado (todas)
        // ********************************************************************** // 
        if (trim($_SESSION['jsonsalida']['recibo']) == '') {
            $queryImagenes = "SELECT * FROM mreg_radicacionesanexos  WHERE eliminado<>'SI' and idradicacion='" . ltrim(trim($_SESSION["jsonsalida"]["radicado"]), "0") . "'";
        } else {
            $queryImagenes = "SELECT * FROM mreg_radicacionesanexos WHERE eliminado<>'SI' and (idradicacion='" . ltrim(trim($_SESSION["jsonsalida"]["radicado"]), "0") . "' or numerorecibo='" . $_SESSION['jsonsalida']['recibo'] . "')";
        }

        //
        $resQueryImagenes = retornarRegistrosMysqliApi($mysqli, 'mreg_radicacionesanexos', "eliminado<>'SI' and idradicacion='" . ltrim(trim($_SESSION["jsonsalida"]["radicado"]), "0") . "'");

        if (!empty($resQueryImagenes)) {
            $images = array();
            $imgsobre = array();
            foreach ($resQueryImagenes as $imagent) {
                if ($imagent["tipoanexo"] == '601') {
                    $imgsobre = $imagent;
                } else {
                    $images[] = $imagent;
                }
            }

            //
            if (!empty($imgsobre)) {
                $images[] = $imgsobre;
            }

            foreach ($images as $imagent) {
                $tiposirep = '';
                $tipodigitalizacion = '';
                if (isset($trd[$imagent["idtipodoc"]])) {
                    $tiposirep = $trd[$imagent["idtipodoc"]]["tiposirep"];
                    $tipodigitalizacion = $trd[$imagent["idtipodoc"]]["tipodigitalizacion"];
                }
                $imagen = array();
                if ($imagent['sistemaorigen'] == 'DOCUWARE') {
                    $imagen['url'] = TIPO_HTTP . HTTP_HOST . '/tmp/' . str_replace("../../../tmp/", "", recuperarImagenRepositorioSii2($imagent['path'], 'DOCUWARE', $imagent['numeropaginas'], $imagent['idorigenexterno']));
                } else {
                    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
                        $imagen['url'] = obtenerUrlRepositorioS3Api($imagent['path']);
                    } else {
                        $imagen['url'] = TIPO_HTTP . HTTP_HOST . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["entrada"]["codigoempresa"] . "/" . $imagent['path'];
                    }
                }
                if ((string) $imagen['url'] != '') {
                    $imagen['idanexo'] = ($imagent['idanexo']);
                    $imagen['tipo'] = trim((string) $imagent['idtipodoc']);

                    //WSIERRA : 2018-11-22  - Incluye campo tipoanexo
                    $imagen['tipoanexo'] = trim((string) $imagent['tipoanexo']);

                    $imagen['tiposirep'] = $tiposirep;
                    $imagen['tipodigitalizacion'] = $tipodigitalizacion;
                    $imagen['identificador'] = trim((string) $imagent['identificador']);
                    $strings = explode(".", $imagent['path']);
                    $imagen['formato'] = $strings[count($strings) - 1];
                    $imagen['identificacion'] = trim($imagent['identificacion']);
                    $imagen['nombre'] = trim((string) $imagent['nombre']);
                    if (trim($imagent['matricula']) == 'NUEVAEST') {
                        $imagen['matricula'] = $matest;
                    } else {
                        $imagen['matricula'] = trim((string) $imagent['matricula']);
                    }
                    $imagen['proponente'] = trim((string) $imagent['proponente']);
                    if ($imagen['matricula'] != '') {
                        $exp1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "proponente='" . $imagen['matricula'] . "'");
                    } else {
                        if ($imagen['proponente'] != '') {
                            $exp1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "proponente='" . $imagen['proponente'] . "'");
                        } else {
                            $exp1 = false;
                        }
                    }
                    if ($exp1) {
                        $imagen['identificacion'] = $exp1['numid'];
                        $imagen['nombre'] = $exp1['razonsocial'];                        
                    }
                    $imagen['fechadocumento'] = trim((string) $imagent['fechadoc']);
                    $imagen['origen'] = trim((string) $imagent['txtorigendoc']);
                    $imagen['observaciones'] = trim((string) $imagent['observaciones']);
                    $imagen['idusuarioescaneo'] = trim((string) $imagent['idusuarioescaneo']);
                    $imagen['fechaescaneo'] = trim((string) $imagent['fechaescaneo']);
                    $imagenes[] = $imagen;
                }
            }

            $_SESSION['jsonsalida']['imagenes'] = $imagenes;
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
