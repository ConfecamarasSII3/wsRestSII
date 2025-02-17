<?php

class funcionesRegistrales_rutinaNotificarRadicacion {

    public static function rutinaNotificarRadicacion($mysqli, $recibo = '', $codbarras = '', $emailsentrada = array(), $celularesentrada = array(), $nameLog = '', $idliquidacion = 0) {

        ini_set('memory_limit', '1024M');

        if ($nameLog == '') {
            $nameLog = 'rutinaNotificarRadicacion_API_' . date("Ymd");
        }
        \logApi::general2($nameLog, $idliquidacion, 'Ingreso a notificar radicacion  : ' . $recibo . ' /' . $codbarras);

        //
        $_SESSION["generales"]["corterenovacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
        $_SESSION["generales"]["corterenovacionmesdia"] = substr($_SESSION["generales"]["corterenovacion"], 4, 4);
        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {
            $_SESSION["tramite"]["registroautomatico"] = retornarRegistroMysqliApi($mysqli, 'bas_tipotramites ', "id='" . $_SESSION["tramite"]["tipotramite"] . "'", "registroinmediato");
        } else {
            $_SESSION["tramite"]["registroautomatico"] = '';
        }

        //
        $reg = false;
        $notificar = 'si';
        $matriculasnotificar = array();

        // *********************************************************************************** //
        // Localiza el recibo a través del código de barras
        // *********************************************************************************** //
        if ($recibo == '' && $codbarras != '') {
            $temx = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $codbarras . "'");
            if ($temx && !empty($temx)) {
                if ($temx["recibo"] != '') {
                    $query = "recibo='" . $temx["recibo"] . "' and tipogasto IN ('0','4','6','8')";
                    $reg = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', $query);
                }
            }
            \logApi::general2($nameLog, $idliquidacion, 'Localizo recibo a traves del codigo de barras');
        }

        // *********************************************************************************** //
        // Localiza el recibo directamente por el numero
        // *********************************************************************************** //
        if ($recibo != '') {
            $query = "recibo='" . $recibo . "' and tipogasto IN ('0','4','6','8')";
            $reg = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', $query);
            \logApi::general2($nameLog, $idliquidacion, 'Localizo recibo a traves del nro de recibo');
        }

        // *********************************************************************************** //
        // Inicializa arreglo de respuesta
        // *********************************************************************************** //
        $resultadoRecibo = array();

        // ********************************************************************************* //
        // Si no encuentra el recibo se sale con false
        // ********************************************************************************* //
        if ($reg === false || empty($reg)) {
            $notificar = 'no';
            \logApi::general2($nameLog, $idliquidacion, 'Recibo no localizado : ' . $reg["recibo"]);
            return false;
        }

        // ********************************************************************************* //
        // Lee el código de barras
        // ********************************************************************************* //
        if ($reg["codigobarras"] === false || empty($reg["codigobarras"])) {
            $notificar = 'no';
            \logApi::general2($nameLog, $idliquidacion, 'Recibo sin codigo de barras');
            return false;
        }
        $arrTemCB = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $reg["codigobarras"] . "'");

        // ********************************************************************************* //
        // Si no encuentra el código de barras se sale con no
        // ********************************************************************************* //
        if ($arrTemCB === false || empty($arrTemCB)) {
            $notificar = 'no';
            \logApi::general2($nameLog, $idliquidacion, 'Código de barras no localizado  : ' . $reg["recibo"] . ' /' . $reg["codigobarras"]);
            return false;
        }

        // ********************************************************************************* //
        // Si el código de barras es de embargos
        // se sale sin notificar
        // ********************************************************************************* //
        if ($arrTemCB["actoreparto"] == '07' || $arrTemCB["actoreparto"] == '29' || $arrTemCB["actoreparto"] == '81') {
            $notificar = 'no';
            \logApi::general2($nameLog, $idliquidacion, 'Código de barras no notificable, embargo, desembargo o medida cautelar  : ' . $reg["recibo"] . ' /' . $reg["codigobarras"]);
            return false;
        }

        // ********************************************************************************* //
        // Localiza liquidacion
        // ********************************************************************************* //
        $liq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "numerorecibo='" . $reg["recibo"] . "'");
        if ($liq === false || empty($liq)) {
            $liq = false;
        }


        // ********************************************************************************* //
        // Localiza transaccion (solo en caso de compraventas)
        // ********************************************************************************* //
        if ($arrTemCB["actoreparto"] == '25' && $liq && !empty($liq)) {
            $liqtras = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidacion_transacciones', "idliquidacion=" . $liq["idliquidacion"], "id");
        } else {
            $liqtras = false;
        }

        // ********************************************************************************* //
        // Localiza bandeja de digitalización
        // ********************************************************************************* //
        $bandejaDigitalizacion = '';
        $tt = retornarRegistroMysqliApi($mysqli, 'mreg_codrutas', "id='" . $arrTemCB["actoreparto"] . "'");
        if ($tt && !empty($tt)) {
            $bandejaDigitalizacion = $tt["bandeja"];
        }

        //
        \logApi::general2($nameLog, $idliquidacion, 'Notificaciones: Localizo recibo : ' . $reg["recibo"]);

        // ********************************************************************************* //    
        // Inicializa variables para el conrol del envío de las notificaciones
        // ********************************************************************************* //    
        $resultadoRecibo["nrec"] = $reg["recibo"];
        $resultadoRecibo["cba"] = $reg["codigobarras"];
        $resultadoRecibo["ope"] = $reg["operacion"];
        $resultadoRecibo["fec"] = $reg["fecha"];
        $resultadoRecibo["hor"] = $reg["hora"];
        $resultadoRecibo["ide"] = $arrTemCB["numid"];
        $resultadoRecibo["nom"] = $arrTemCB["nombre"];
        $resultadoRecibo["mat"] = array();
        $resultadoRecibo["pro"] = '';
        $resultadoRecibo["ser"] = array();
        $resultadoRecibo["valor"] = $reg["valorneto"];
        $resultadoRecibo["tt"] = $reg["tipotramite"];
        $resultadoRecibo["emails"] = array();
        $resultadoRecibo["emailsrecibo"] = array();
        $resultadoRecibo["telefonos"] = array();
        $resultadoRecibo["telefonosrecibo"] = array();
        $resultadoRecibo["emailshistoricos"] = array();
        $resultadoRecibo["telefonoshistoricos"] = array();

        // ********************************************************************************* //    
        // Encuentra correos y celulares asociados a la liquidacion
        // ********************************************************************************* //  
        if ($liq && !empty($liq)) {
            $liq["email"] = str_replace(".@", "@", $liq["email"]);
            $liq["emailpagador"] = str_replace(".@", "@", $liq["emailpagador"]);
            $reg["email"] = str_replace(".@", "@", $reg["email"]);

            if ($liq["email"] != '') {
                if (!isset($resultadoRecibo["emailsrecibo"][$liq["email"]])) {
                    $resultadoRecibo["emailsrecibo"][$liq["email"]] = $liq["email"];
                }
            }
            if ($liq["emailpagador"] != '') {
                if (!isset($resultadoRecibo["emailsrecibo"][$liq["emailpagador"]])) {
                    $resultadoRecibo["emailsrecibo"][$liq["emailpagador"]] = $liq["emailpagador"];
                }
            }
            if ($liq["telefono"] != '' && strlen($liq["telefono"]) == 10 && substr($liq["telefono"], 0, 1) == '3') {
                $resultadoRecibo["telefonosrecibo"][$liq["telefono"]] = $liq["telefono"];
            }
            if ($liq["movil"] != '' && strlen($liq["movil"]) == 10 && substr($liq["movil"], 0, 1) == '3') {
                $resultadoRecibo["telefonosrecibo"][$liq["movil"]] = $liq["movil"];
            }
            if ($liq["telefonopagador"] != '' && strlen($liq["telefonopagador"]) == 10 && substr($liq["telefonopagador"], 0, 1) == '3') {
                $resultadoRecibo["telefonosrecibo"][$liq["telefonopagador"]] = $liq["telefonopagador"];
            }
            if ($liq["movilpagador"] != '' && strlen($liq["movilpagador"]) == 10 && substr($liq["movilpagador"], 0, 1) == '3') {
                $resultadoRecibo["telefonosrecibo"][$liq["movilpagador"]] = $liq["movilpagador"];
            }
        }

        // ********************************************************************************* //    
        // Encuentra correos y celulares asociados al recibo
        // ********************************************************************************* // 
        $resultadoRecibo["emailsrecibo"][$reg["email"]] = $reg["email"];
        if ($reg["telefono1"] != '' && strlen($reg["telefono1"]) == 10 && substr($reg["telefono1"], 0, 1) == '3') {
            $resultadoRecibo["telefonosrecibo"][$reg["telefono1"]] = $reg["telefono1"];
        }
        if ($reg["telefono2"] != '' && strlen($reg["telefono2"]) == 10 && substr($reg["telefono2"], 0, 1) == '3') {
            $resultadoRecibo["telefonosrecibo"][$reg["telefono2"]] = $reg["telefono2"];
        }

        // ********************************************************************************* //    
        // Encuentra correos y celulares asociados a la transaccion
        // ********************************************************************************* // 
        if ($liqtras && !empty($liqtras)) {
            foreach ($liqtras as $liqtra) {
                $liqtra["emailvendedor"] = str_replace(".@", "@", $reg["emailvendedor"]);
                $liqtra["emailcomprador"] = str_replace(".@", "@", $reg["emailcomprador"]);
                if ($liqtra["emailvendedor"] != '') {
                    if (!isset($resultadoRecibo["emails"][$liqtra["emailvendedor"]])) {
                        $resultadoRecibo["emails"][$liqtra["emailvendedor"]] = $liqtra["emailvendedor"];
                    }
                }
                if ($liqtra["emailcomprador"] != '') {
                    if (!isset($resultadoRecibo["emails"][$liqtra["emailcomprador"]])) {
                        $resultadoRecibo["emails"][$liqtra["emailcomprador"]] = $liqtra["emailcomprador"];
                    }
                }
                if ($liqtra["celularvendedor"] != '' && strlen($liqtra["celularvendedor"]) == 10 && substr($liqtra["celularvendedor"], 0, 1) == '3') {
                    if (!isset($resultadoRecibo["telefonos"][$liqtra["celularvendedor"]])) {
                        $resultadoRecibo["telefonos"][$liqtra["celularvendedor"]] = $liqtra["celularvendedor"];
                    }
                }
                if ($liqtra["celularcomprador"] != '' && strlen($liqtra["celularcomprador"]) == 10 && substr($liqtra["celularcomprador"], 0, 1) == '3') {
                    if (!isset($resultadoRecibo["telefonos"][$liqtra["celularcomprador"]])) {
                        $resultadoRecibo["telefonos"][$liqtra["celularcomprador"]] = $liqtra["celularcomprador"];
                    }
                }
            }
        }

        // ********************************************************************************* //    
        // Adiciona emails reportados como parámetros
        // ********************************************************************************* // 
        if (!empty($emailsentrada)) {
            foreach ($emailsentrada as $e) {
                $e = str_replace(".@", "@", $e);
                if (!isset($resultadoRecibo["emails"][$e])) {
                    $resultadoRecibo["emails"][$e] = $e;
                }
            }
        }

        // ********************************************************************************* //    
        // Adiciona celulares reportados como parámetros
        // ********************************************************************************* // 
        if (!empty($celularesentrada)) {
            foreach ($celularesentrada as $e) {
                if (!isset($resultadoRecibo["telefonos"][$e])) {
                    $resultadoRecibo["telefonos"][$e] = $e;
                }
            }
        }

        // ******************************************************************************************** //    
        // Busca las matrículas y proponentes asociadas al trámite a traves de recibosgenerados_detalle
        // ******************************************************************************************** // 

        $txtServicios = '';
        $esren = 'no';
        $esrentodosanos = '';
        $esmat = 'no';
        $esmut = 'no';
        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_detalle', "recibo='" . $reg["recibo"] . "'", "secuencia");
        $j = 0;
        if ($arrTem && !empty($arrTem)) {
            foreach ($arrTem as $tx) {
                if (substr($tx["idservicio"], 0, 6) == '010202') {
                    if ($tx["ano"] == date("Y")) {
                        $esrentodosanos = 'si';
                    }
                }
                $j++;
                if ($tx["matricula"] != '' && substr($tx["matricula"], 0, 5) != 'NUEVA') {
                    $resultadoRecibo["mat"][$tx["matricula"]] = $tx["matricula"];
                }
                if ($tx["proponente"] != '') {
                    $resultadoRecibo["pro"] = $tx["proponente"];
                }
                if (!isset($resultadoRecibo["ser"][$tx["idservicio"]])) {
                    $resultadoRecibo["ser"][$tx["idservicio"]] = $tx["idservicio"];
                    if ($txtServicios != '') {
                        $txtServicios .= ', ';
                    }
                    $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $tx["idservicio"] . "'");
                    $txtServicios .= $serv["nombre"];
                    if ($serv["tipoingreso"] == '07' || $serv["tipoingreso"] == '17') {
                        $esmut = 'si';
                    }
                    if ($serv["tipoingreso"] == '03' || $serv["tipoingreso"] == '13') {
                        $exp1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $tx["matricula"] . "'", "matricula,organizacion,categoria");
                        if ($exp1 && $exp1["organizacion"] == '01' || ($exp1["organizacion"] > '02' && $exp1["categoria"] == '1' && $exp1["organizacion"] != '12' && $exp1["organizacion"] != '14')) {
                            $esren = 'si';
                        }
                    }
                    if ($serv["tipoingreso"] == '02' || $serv["tipoingreso"] == '12') {
                        if ($tx["matricula"] == '' || $tx["matricula"] == 'NUEVANAT' || $tx["matricula"] == 'NUEVAJUR') {
                            $esmat = 'si';
                        } else {
                            if ($tx["matricula"] != '' && substr($tx["matricula"], 0, 5) != 'NUEVA') {
                                $exp1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $tx["matricula"] . "'", "matricula,organizacion,categoria");
                                if ($exp1 && $exp1["organizacion"] == '01' || ($exp1["organizacion"] > '02' && $exp1["categoria"] == '1' && $exp1["organizacion"] != '12' && $exp1["organizacion"] != '14')) {
                                    $esmat = 'si';
                                }
                            }
                        }
                    }
                }
            }
        }
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Servicio a reportar ' . $txtServicios);
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Es renovación ' . $esren . ',  Es mutacion ' . $esmut . ', Es matrícula o constitución ' . $esmat);

        //
        $txmats = '';
        if (!empty($resultadoRecibo["mat"])) {
            foreach ($resultadoRecibo["mat"] as $mtx) {
                if ($txmats != '') {
                    $txmats .= ', ';
                }
                $txmats .= $mtx;
            }
        }
        if ($txmats == '') {
            $txmats = 'Sin expedientes para notificar';
        }
        $txservs = '';
        if (!empty($resultadoRecibo["ser"])) {
            foreach ($resultadoRecibo["ser"] as $mtx) {
                if ($txservs != '') {
                    $txservs .= ', ';
                }
                $txservs .= $mtx;
            }
        }
        if ($txservs == '') {
            $txservs = 'Sin servicios para notificar';
        }

        //
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Localizo matricula(s) : ' . $txmats);
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Localizo servicio(s) : ' . $txservs);

        // ************************************************************************************************** //
        // Adiciona los emails y celulares asociados al código de barras
        // ************************************************************************************************** //
        // $arrTemCB = retornarRegistroMysqli($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $reg["codigobarras"] . "'");
        if ($arrTemCB["emailnot1"] != '') {
            $arrTemCB["emailnot1"] = str_replace(".@", "@", $arrTemCB["emailnot1"]);
            $resultadoRecibo["emails"][$arrTemCB["emailnot1"]] = $arrTemCB["emailnot1"];
        }
        if ($arrTemCB["emailnot2"] != '') {
            $arrTemCB["emailnot2"] = str_replace(".@", "@", $arrTemCB["emailnot2"]);
            $resultadoRecibo["emails"][$arrTemCB["emailnot2"]] = $arrTemCB["emailnot2"];
        }
        if ($arrTemCB["emailnot3"] != '') {
            $arrTemCB["emailnot3"] = str_replace(".@", "@", $arrTemCB["emailnot3"]);
            $resultadoRecibo["emails"][$arrTemCB["emailnot3"]] = $arrTemCB["emailnot3"];
        }
        if ($arrTemCB["celnot1"] != '' && strlen($arrTemCB["celnot1"]) == 10 && substr($arrTemCB["celnot1"], 0, 1) == '3') {
            $resultadoRecibo["telefonos"][$arrTemCB["celnot1"]] = $arrTemCB["celnot1"];
        }
        if ($arrTemCB["celnot2"] != '' && strlen($arrTemCB["celnot2"]) == 10 && substr($arrTemCB["celnot2"], 0, 1) == '3') {
            $resultadoRecibo["telefonos"][$arrTemCB["celnot2"]] = $arrTemCB["celnot2"];
        }
        if ($arrTemCB["celnot3"] != '' && strlen($arrTemCB["celnot3"]) == 10 && substr($arrTemCB["celnot3"], 0, 1) == '3') {
            $resultadoRecibo["telefonos"][$arrTemCB["celnot3"]] = $arrTemCB["celnot3"];
        }

        // *********************************************************************************** //
        // Busca cada expediente
        // *********************************************************************************** //            
        if (!empty($resultadoRecibo["mat"])) {
            foreach ($resultadoRecibo["mat"] as $m) {
                $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $m . "'");

                // *********************************************************************************** //
                // Localiza emails y celulares actuales
                // *********************************************************************************** //                                    
                if ($exp && !empty($exp)) {
                    if (trim($exp["telcom1"]) != '' && strlen($exp["telcom1"]) == 10 && substr($exp["telcom1"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$exp["telcom1"]] = $exp["telcom1"];
                    }
                    if (trim($exp["telcom2"]) != '' && strlen($exp["telcom2"]) == 10 && substr($exp["telcom2"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$exp["telcom2"]] = $exp["telcom2"];
                    }
                    if (trim($exp["telcom3"]) != '' && strlen($exp["telcom3"]) == 10 && substr($exp["telcom3"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$exp["telcom3"]] = $exp["telcom3"];
                    }
                    if (trim($exp["telnot"]) != '' && strlen($exp["telnot"]) == 10 && substr($exp["telnot"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$exp["telnot"]] = $exp["telnot"];
                    }
                    if (trim($exp["telnot2"]) != '' && strlen($exp["telnot2"]) == 10 && substr($exp["telnot2"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$exp["telnot2"]] = $exp["telnot2"];
                    }
                    if (trim($exp["telnot3"]) != '' && strlen($exp["telnot3"]) == 10 && substr($exp["telnot3"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$exp["telnot3"]] = $exp["telnot3"];
                    }
                    if (trim($exp["emailcom"]) != '') {
                        $exp["emailcom"] = str_replace(".@", "@", $exp["emailcom"]);
                        $resultadoRecibo["emails"][$exp["emailcom"]] = $exp["emailcom"];
                    }
                    if (trim($exp["emailcom2"]) != '') {
                        $exp["emailcom2"] = str_replace(".@", "@", $exp["emailcom2"]);
                        $resultadoRecibo["emails"][$exp["emailcom2"]] = $exp["emailcom2"];
                    }
                    if (trim($exp["emailcom3"]) != '') {
                        $exp["emailcom3"] = str_replace(".@", "@", $exp["emailcom3"]);
                        $resultadoRecibo["emails"][$exp["emailcom3"]] = $exp["emailcom3"];
                    }
                    if (trim($exp["emailnot"]) != '') {
                        $exp["emailnot"] = str_replace(".@", "@", $exp["emailnot"]);
                        $resultadoRecibo["emails"][$exp["emailnot"]] = $exp["emailnot"];
                    }
                }

                // *********************************************************************************** //
                // Localiza emails y celulares anteriores
                // Solamente en caso de renovaciones o mutaciones 
                // *********************************************************************************** //  
                if ($esren == 'si' || $esmut == 'si') {
                    // *********************************************************************************** //
                    // Localiza emails y celulares modificados en mreg_campos_historicos_AAAA
                    // *********************************************************************************** //                                                            
                    $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'telcom1');
                    $ictos = 0;
                    foreach ($d as $d1) {
                        if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                            $ictos++;
                            if ($ictos < 3) {
                                if (!isset($resultadoRecibo["telefonoshistoricos"][trim($d1)])) {
                                    if (!isset($resultadoRecibo["telefonos"][trim($d1)])) {
                                        $resultadoRecibo["telefonoshistoricos"][trim($d1)] = trim($d1);
                                    }
                                }
                            }
                        }
                    }

                    $ictos = 0;
                    $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'telcom2');
                    foreach ($d as $d1) {
                        if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                            $ictos++;
                            if ($ictos < 3) {
                                if (!isset($resultadoRecibo["telefonoshistoricos"][trim($d1)])) {
                                    if (!isset($resultadoRecibo["telefonos"][trim($d1)])) {
                                        $resultadoRecibo["telefonoshistoricos"][trim($d1)] = trim($d1);
                                    }
                                }
                            }
                        }
                    }

                    $ictos = 0;
                    $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'telcom3');
                    foreach ($d as $d1) {
                        if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                            $ictos++;
                            if ($ictos < 3) {
                                if (!isset($resultadoRecibo["telefonoshistoricos"][trim($d1)])) {
                                    if (!isset($resultadoRecibo["telefonos"][trim($d1)])) {
                                        $resultadoRecibo["telefonoshistoricos"][trim($d1)] = trim($d1);
                                    }
                                }
                            }
                        }
                    }

                    $ictos = 0;
                    $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'telnot');
                    foreach ($d as $d1) {
                        if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                            $ictos++;
                            if ($ictos < 3) {
                                if (!isset($resultadoRecibo["telefonoshistoricos"][trim($d1)])) {
                                    if (!isset($resultadoRecibo["telefonos"][trim($d1)])) {
                                        $resultadoRecibo["telefonoshistoricos"][trim($d1)] = trim($d1);
                                    }
                                }
                            }
                        }
                    }

                    $ictos = 0;
                    $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'telnot2');
                    foreach ($d as $d1) {
                        if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                            $ictos++;
                            if ($ictos < 3) {
                                if (!isset($resultadoRecibo["telefonoshistoricos"][trim($d1)])) {
                                    if (!isset($resultadoRecibo["telefonos"][trim($d1)])) {
                                        $resultadoRecibo["telefonoshistoricos"][trim($d1)] = trim($d1);
                                    }
                                }
                            }
                        }
                    }

                    $ictos = 0;
                    $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'telnot3');
                    foreach ($d as $d1) {
                        if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                            $ictos++;
                            if ($ictos < 3) {
                                if (!isset($resultadoRecibo["telefonoshistoricos"][trim($d1)])) {
                                    if (!isset($resultadoRecibo["telefonos"][trim($d1)])) {
                                        $resultadoRecibo["telefonoshistoricos"][trim($d1)] = trim($d1);
                                    }
                                }
                            }
                        }
                    }

                    $ictos = 0;
                    $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'emailcom');
                    foreach ($d as $d1) {
                        $ictos++;
                        if ($ictos < 3) {
                            $d1 = str_replace(".@", "@", $d1);
                            if (!isset($resultadoRecibo["emailshistoricos"][trim($d1)])) {
                                if (!isset($resultadoRecibo["emails"][trim($d1)])) {
                                    $resultadoRecibo["emailshistoricos"][trim($d1)] = trim($d1);
                                }
                            }
                        }
                    }

                    $ictos = 0;
                    $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'emailnot');
                    foreach ($d as $d1) {
                        $ictos++;
                        if ($ictos < 3) {
                            $d1 = str_replace(".@", "@", $d1);
                            if (!isset($resultadoRecibo["emailshistoricos"][trim($d1)])) {
                                if (!isset($resultadoRecibo["emails"][trim($d1)])) {
                                    $resultadoRecibo["emailshistoricos"][trim($d1)] = trim($d1);
                                }
                            }
                        }
                    }
                }
            }
        }


        // *********************************************************************************** //
        // Busca emails y celulares en el formulario grabado en el expediente
        // *********************************************************************************** //
        $forms = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $liq["idliquidacion"]);
        if ($forms && !empty($forms)) {
            foreach ($forms as $f1) {
                if (trim((string) $f1["xml"]) != '') {
                    $f2 = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $f1["xml"]);
                    if (isset($f2["emailcom"]) && trim((string) $f2["emailcom"]) != '') {
                        $resultadoRecibo["emails"][$f2["emailcom"]] = $f2["emailcom"];
                    }
                    if (isset($f2["emailnot"]) && trim((string) $f2["emailnot"]) != '') {
                        $resultadoRecibo["emails"][$f2["emailnot"]] = $f2["emailnot"];
                    }
                    if (isset($f2["telcom1"]) && strlen($f2["telcom1"]) == 10 && substr($f2["telcom1"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$f2["telcom1"]] = $f2["telcom1"];
                    }
                    if (isset($f2["telcom2"]) && strlen($f2["telcom2"]) == 10 && substr($f2["telcom2"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$f2["telcom2"]] = $f2["telcom2"];
                    }
                    if (isset($f2["celcom"]) && strlen($f2["celcom"]) == 10 && substr($f2["celcom"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$f2["celcom"]] = $f2["celcom"];
                    }
                    if (isset($f2["telnot"]) && strlen($f2["telnot"]) == 10 && substr($f2["telnot"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$f2["telnot"]] = $f2["telnot"];
                    }
                    if (isset($f2["telnot2"]) && strlen($f2["telnot2"]) == 10 && substr($f2["telnot2"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$f2["telnot2"]] = $f2["telnot2"];
                    }
                    if (isset($f2["celnot"]) && strlen($f2["celnot"]) == 10 && substr($f2["celnot"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$f2["celnot"]] = $f2["celnot"];
                    }
                }
            }
        }


        // *********************************************************************************** //
        // Recupera números telefonicos y emails actuales - proponentes
        // *********************************************************************************** //
        if ($resultadoRecibo["pro"] != '') {
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_proponentes', "proponente='" . $resultadoRecibo["pro"] . "'");
            if ($exp && !empty($exp)) {
                if (trim($exp["telcom1"]) != '' && strlen($exp["telcom1"]) == 10 && substr($exp["telcom1"], 0, 1) == '3') {
                    $resultadoRecibo["telefonos"][$exp["telcom1"]] = $exp["telcom1"];
                }
                if (trim($exp["telcom2"]) != '' && strlen($exp["telcom2"]) == 10 && substr($exp["telcom2"], 0, 1) == '3') {
                    $resultadoRecibo["telefonos"][$exp["telcom2"]] = $exp["telcom2"];
                }
                if (trim($exp["celcom"]) != '' && strlen($exp["celcom"]) == 10 && substr($exp["celcom"], 0, 1) == '3') {
                    $resultadoRecibo["telefonos"][$exp["celcom"]] = $exp["celcom"];
                }
                if (trim($exp["telnot"]) != '' && strlen($exp["telnot"]) == 10 && substr($exp["telnot"], 0, 1) == '3') {
                    $resultadoRecibo["telefonos"][$exp["telnot"]] = $exp["telnot"];
                }
                if (trim($exp["telnot2"]) != '' && strlen($exp["telnot2"]) == 10 && substr($exp["telnot2"], 0, 1) == '3') {
                    $resultadoRecibo["telefonos"][$exp["telnot2"]] = $exp["telnot2"];
                }
                if (trim($exp["celnot"]) != '' && strlen($exp["celnot"]) == 10 && substr($exp["celnot"], 0, 1) == '3') {
                    $resultadoRecibo["telefonos"][$exp["celnot"]] = $exp["celnot"];
                }
                if (trim($exp["emailcom"]) != '') {
                    $exp["emailcom"] = str_replace(".@", "@", $exp["emailcom"]);
                    $resultadoRecibo["emails"][$exp["emailcom"]] = $exp["emailcom"];
                }
                if (trim($exp["emailnot"]) != '') {
                    $exp["emailnot"] = str_replace(".@", "@", $exp["emailnot"]);
                    $resultadoRecibo["emails"][$exp["emailnot"]] = $exp["emailnot"];
                }
            }
        }


        // unset($reg);
        // *********************************************************************************** //
        // Log de cantidad de emails y celulares
        // *********************************************************************************** //
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Total emails vigentes : ' . count($resultadoRecibo["emails"]));
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Total emails históricos : ' . count($resultadoRecibo["emailshistoricos"]));
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Total celulares vigentes : ' . count($resultadoRecibo["telefonos"]));
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Total celulares históricos : ' . count($resultadoRecibo["telefonoshistoricos"]));

        // *********************************************************************************** //
        // Localiza descripción de servicio(s) a notificar
        // *********************************************************************************** //        
        $sinemails = 0;

        // *********************************************************************************** //
        // 2019-09-26: JINT.
        // Determinar si se envía o no a los históricos
        // En caso que no se envie, limpiar los arreglos de históricos y guardar log
        // *********************************************************************************** //
        // *********************************************************************************** //
        // Une arreglos de emails y celurares
        // *********************************************************************************** //
        if (empty($resultadoRecibo["emails"])) {
            $resultadoRecibo["listaemails"] = array_merge($resultadoRecibo["emailsrecibo"], $resultadoRecibo["emailshistoricos"]);
        } else {
            $resultadoRecibo["listaemails"] = array_merge($resultadoRecibo["emails"], $resultadoRecibo["emailshistoricos"]);
        }

        //
        if (empty($resultadoRecibo["telefonos"])) {
            $resultadoRecibo["listatelefonos"] = array_merge($resultadoRecibo["telefonosrecibo"], $resultadoRecibo["telefonoshistoricos"]);
        } else {
            $resultadoRecibo["listatelefonos"] = array_merge($resultadoRecibo["telefonos"], $resultadoRecibo["telefonoshistoricos"]);
        }

        //
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Cantidad de correos electronicos ' . count($resultadoRecibo["listaemails"]));
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Cantidad de celulares ' . count($resultadoRecibo["listatelefonos"]));

        //
        $arrCampos = array(
            'recibo',
            'codigobarras',
            'idliquidacion',
            'tipo',
            'contenido'
        );
        $arrValores = array();
        if (!empty($resultadoRecibo["listaemails"])) {
            foreach ($resultadoRecibo["listaemails"] as $e) {
                $arrValores[] = array(
                    "'" . $reg["recibo"] . "'",
                    "'" . $reg["codigobarras"] . "'",
                    $reg["idliquidacion"],
                    "'email-radicacion'",
                    "'" . $e . "'"
                );
            }
        }
        if (!empty($resultadoRecibo["listatelefonos"])) {
            foreach ($resultadoRecibo["listatelefonos"] as $e) {
                $arrValores[] = array(
                    "'" . $reg["recibo"] . "'",
                    "'" . $reg["codigobarras"] . "'",
                    $reg["idliquidacion"],
                    "'telefono-radicacion'",
                    "'" . $e . "'"
                );
            }
        }

        // *********************************************************************************** //
        // Almacena tabla donde guarda que emails y celulares será notificados por recibo
        // *********************************************************************************** //
        if (!empty($arrValores)) {
            insertarRegistrosBloqueMysqliApi($mysqli, 'mreg_recibos_sipref_destinos', $arrCampos, $arrValores);
            \logApi::general2($nameLog, $liq["idliquidacion"], 'Inserto mreg_recibos_sipref_destinos');
        }

        // *********************************************************************************** //
        // Envía emails de notificaicón
        // *********************************************************************************** //
        if (count($resultadoRecibo["listaemails"]) == 0) {
            $arrCampos = array('estadoemail');
            $arrValores = array("'2'");
            regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $resultadoRecibo["nrec"] . "'");
            $sinemails++;
            \logApi::general2($nameLog, $liq["idliquidacion"], 'Actualizo recibo sin emails para notificar');
        } else {
            $msg = '';
            $msg .= 'LA ' . RAZONSOCIAL . ' le informa que el dia ' . \funcionesGenerales::mostrarFecha($resultadoRecibo["fec"]) . ' a las ' . \funcionesGenerales::mostrarHora($resultadoRecibo["hor"]) . ' ';
            $msg .= 'fue radicada en nuestras oficinas una transaccion en los registros publicos que ';
            $msg .= 'administra y maneja nuestra entidad. Los datos del tramite radicado son los siguientes:<br><br>';
            $msg .= 'Recibo de Caja No. ' . $resultadoRecibo["nrec"] . '<br>';
            $msg .= 'Numero operacion: ' . $resultadoRecibo["ope"] . '<br>';
            $msg .= 'Identificacion(es): ' . $resultadoRecibo["ide"] . '<br>';
            $msg .= 'Nombre(s): ' . $resultadoRecibo["nom"] . '<br>';
            if (ltrim($resultadoRecibo["cba"], "0") != '') {
                $msg .= 'Codigo de barras: ' . $resultadoRecibo["cba"] . '<br>';
            }
            $mats = '';
            if (!empty($resultadoRecibo["mat"])) {
                foreach ($resultadoRecibo["mat"] as $mx1) {
                    if ($mats != '') {
                        $mats .= ', ';
                    }
                    $mats .= $mx1;
                }
            }
            if ($mats != '') {
                $msg .= 'Matriculas/Inscripciones: ' . $mats . '<br>';
            }
            if (ltrim($resultadoRecibo["pro"], "0") != '') {
                $msg .= 'Proponente: ' . $resultadoRecibo["pro"] . '<br>';
            }
            $msg .= 'Tramite: ' . $txtServicios . '<br>';
            foreach ($resultadoRecibo["listaemails"] as $emx) {
                $msg .= 'Email : ' . $emx . '<br>';
            }
            $msg .= '<br>';
            $msg .= 'Valor de la transaccion: ' . $resultadoRecibo["valor"] . '<br><br>';

            $msgbenmat = '';
            $msgbenren = '';

            // 2020-02-21: JINT: Se incluye nota si es renovación y si la fecha del recibo es anterior al 31 de marzo
            if ($esren == 'si' && substr($reg["fecha"], 4, 4) <= $_SESSION["generales"]["corterenovacionmesdia"]) {
                if ($esrentodosanos == 'si') {
                    $msgbenren = \funcionesGenerales::cambiarSustitutoHtml(\funcionesGenerales::retornarPantallaPredisenada($mysqli, 'texto.beneficios.renovacion'));
                    if (trim($msgbenren) == '') {
                        $msg .= 'Señor empresario, si realiza la renovación oportuna de su Matrícula Mercantil y la de sus establecimientos de comercio ';
                        $msg .= 'puede acceder a diversos beneficios. Lo invitamos a que consulte el portafolio de servicios ';
                        $msg .= 'y programas en la página web de su Cámara de Comercio<br><br>';
                    }
                }
            }

            // 2020-02-21: JINT: Se incluye nota si es matricula 
            if ($esmat == 'si') {
                $msgbenmat = \funcionesGenerales::cambiarSustitutoHtml(\funcionesGenerales::retornarPantallaPredisenada($mysqli, 'texto.beneficios.matricula'));
                if (trim($msgbenmat) == '') {
                    $msg .= 'Señor empresario, por matricularse en la Cámara de Comercio puede acceder a diversos beneficios. Lo invitamos a que consulte el portafolio ';
                    $msg .= 'de servicios y programas en la página web de su Cámara de Comercio<br><br>';
                }
            }

            $msg .= 'De conformidad con lo establecido en el numeral 1.1.12.6 de la Circular 100-000002 de abril 25 de 2022 de la Superintendencia de ';
            $msg .= 'Sociedades el titular de la informacion tiene el derecho a oponerse del tramite cuando advierta que el acto o documento ';
            $msg .= 'que pretende modificar su registro, no es de su procedencia. La oposicion puede efectuarse verbalmente o por escrito y en el ';
            $msg .= 'termino de dos (2) dias habiles contados a partir del momento en que se manifieste la oposicion, el titular de la informacion ';
            $msg .= 'debe aportar la denuncia penal correspondiente, para que la Camara de Comercio pueda abstenerse de realizar el registro o la ';
            $msg .= 'modificacion solicitada. Si el titular de la informacion no se opone o no allega la denuncia correspondiente, la Camara de Comercio ';
            $msg .= 'debera continuar la actuacion.<br><br>';
            $msg .= 'Cuando la persona que aparece firmando la peticion de modificacion de informacion o el acta o documento del cual se solicita su registro, ';
            $msg .= 'concurre personalmente a la Camara de Comercio y manifiesta no haberlo suscrito, la entidad cameral se abstendra de realizar la inscripcion ';
            $msg .= 'o la modificacion de informacion solicitada.<br><br>';
            $msg .= 'Si alguna persona tiene otro tipo de reparo en relacion con el documento que se radico para que modifique el registro, este debe ';
            $msg .= 'debatirse utilizando los medios que le otorga la normativa vigente, dentro de los que pueden mencionarse, a manera de ejemplo, ';
            $msg .= 'los recursos administrativos, las denuncias penales por posibles delitos y las demandas de impugnacion de actas ante los jueces de ';
            $msg .= 'la Republica y/o autoridades competentes.<br><br>';

            //
            if (defined('EMAIL_ATENCION_NOTIFICACIONES_SIPREF') && EMAIL_ATENCION_NOTIFICACIONES_SIPREF != '') {
                $msg .= 'Para el efecto puede enviar un mensaje al correo electrónico ' . EMAIL_ATENCION_NOTIFICACIONES_SIPREF . ' ';
                $msg .= 'citando el Nro. ' . $resultadoRecibo["nrec"] . '<br><br>';
            } else {
                $msg .= 'Para el efecto puede comunicarse al numero ' . TELEFONO_ATENCION_USUARIOS . ' en la ciudad ';
                $msg .= 'de ' . retornarNombreMunicipioMysqliApi($mysqli, MUNICIPIO) . ' citando el Nro. ' . $resultadoRecibo["nrec"] . '<br><br>';
            }

            //
            if ($_SESSION["tramite"]["tipotramite"] == 'matriculapnatcae' || $_SESSION["tramite"]["tipotramite"] == 'matriculapjurcae' ||
                    $_SESSION["tramite"]["subtipotramite"] == 'matriculapnatcae' || $_SESSION["tramite"]["subtipotramite"] == 'matriculapjurcae') {
                
            }

            //
            $msg .= 'Este mensaje se envia en forma automatica por el Sistema de Registro de LA ' . RAZONSOCIAL . ' ';
            $msg .= 'en cumplimiento a lo contemplado en el Codigo de Procedimiento Administrativo y de lo Contencioso Administrativo.';
            $msg .= '<br><br>';
            $msg .= 'Correo desatendido: Por favor no responda a la direccion de correo electronico que envia este mensaje, dicha cuenta ';
            $msg .= 'no es revisada por ningun funcionario de nuestra entidad. Este mensaje es informativo.';
            $msg .= '<br><br>';
            $msg .= 'Los acentos y tildes de este correo han sido omitidos intencionalmente con el objeto de evitar inconvenientes en la lectura del mismo.';

            // ***************************************************************************************** //
            // En caso de renovación que se asienta automáticamente
            // ***************************************************************************************** //
            $msg1 = '';
            $emailtotales = 0;
            $emailvalidos = 0;
            foreach ($resultadoRecibo["listaemails"] as $emx) {
                if (trim($emx) != '') {
                    \logApi::general2($nameLog, $liq["idliquidacion"], 'Enviara correo a ' . $emx);
                    $emx1 = $emx;
                    if (TIPO_AMBIENTE == 'PRUEBAS') {
                        if (defined('EMAIL_NOTIFICACION_PRUEBAS') && EMAIL_NOTIFICACION_PRUEBAS != '') {
                            $emx1 = EMAIL_NOTIFICACION_PRUEBAS;
                        } else {
                            $emx1 = 'jint@confecamaras.org.co';
                        }
                    }
                    $emailtotales++;
                    if (\funcionesGenerales::validarEmail($emx1) === true) {
                        $rEmail = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx1, 'Notificacion de radicacion No. ' . $resultadoRecibo["nrec"] . ' en  LA ' . RAZONSOCIAL, $msg);
                        if ($rEmail === false) {
                            sleep(5);
                            $rEmail = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx1, 'Notificacion de radicacion No. ' . $resultadoRecibo["nrec"] . ' en  LA ' . RAZONSOCIAL, $msg);
                        }


//
                        if (($arrTemCB["actoreparto"] != '07') && ($arrTemCB["actoreparto"] != '29')) {
                            if (!isset($resultadoRecibo["mat"]) || empty($resultadoRecibo["mat"])) {
                                $resultadoRecibo["mat"] = array();
                            }
                            if ($rEmail === false) {
                                if (empty($resultadoRecibo["mat"])) {
                                    \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '01', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], '', $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR : ' . $_SESSION["generales"]["mensajeerror"], $bandejaDigitalizacion);
                                    \logApi::general2($nameLog, $liq["idliquidacion"], 'Error enviando notificacion (01) a ' . $emx1);
                                } else {
                                    foreach ($resultadoRecibo["mat"] as $mt1) {
                                        \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '01', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], $mt1, $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR : ' . $_SESSION["generales"]["mensajeerror"], $bandejaDigitalizacion);
                                        \logApi::general2($nameLog, $liq["idliquidacion"], 'Error enviando notificacion (01) a ' . $emx1);
                                    }
                                }
                            } else {
                                if (empty($resultadoRecibo["mat"])) {
                                    \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '01', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], '', $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '**OK **', $bandejaDigitalizacion);
                                    \logApi::general2($nameLog, $liq["idliquidacion"], 'Envio notificacion (01) a ' . $emx1);
                                } else {
                                    foreach ($resultadoRecibo["mat"] as $mt1) {
                                        \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '01', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], $mt1, $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '**OK **', $bandejaDigitalizacion);
                                        \logApi::general2($nameLog, $liq["idliquidacion"], 'Envio notificacion (01) a ' . $emx1);
                                    }
                                }
                            }
                        } else {
                            if (isset($resultadoRecibo["mat"]) && !empty($resultadoRecibo["mat"])) {
                                foreach ($resultadoRecibo["mat"] as $mt1) {
                                    if ($rEmail === false) {
                                        \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '01', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], $mt1, $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR : ' . $_SESSION["generales"]["mensajeerror"], $bandejaDigitalizacion);
                                        \logApi::general2($nameLog, $liq["idliquidacion"], 'Error enviando notificacion (01) a ' . $emx1 . ', expediente ' . $mt1);
                                    } else {
                                        \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '01', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], $mt1, $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '**OK **', $bandejaDigitalizacion);
                                        \logApi::general2($nameLog, $liq["idliquidacion"], 'Envio notificacion (01) a ' . $emx1 . ', expediente ' . $mt1);
                                    }
                                }
                            } else {
                                if ($rEmail === false) {
                                    \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '01', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], '', '', $resultadoRecibo["nom"], $emx, $msg, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR : ' . $_SESSION["generales"]["mensajeerror"], $bandejaDigitalizacion);
                                    \logApi::general2($nameLog, $liq["idliquidacion"], 'Error enviando notificacion (01) a ' . $emx);
                                } else {
                                    \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '01', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], '', '', $resultadoRecibo["nom"], $emx, $msg, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '**OK **', $bandejaDigitalizacion);
                                    \logApi::general2($nameLog, $liq["idliquidacion"], 'Envio notificacion (01) a ' . $emx);
                                }
                            }
                        }

//
                        if ($rEmail) {
                            $emailvalidos++;
                        }
                    }
                }
            }

            // Cunmplimiento al numeral 
            // tetxo adicional de beneficios
            if (trim($msgbenmat) != '') {
                foreach ($resultadoRecibo["listaemails"] as $emx) {
                    if (trim($emx) != '') {
                        $emx1 = $emx;
                        if (TIPO_AMBIENTE == 'PRUEBAS') {
                            if (defined('EMAIL_NOTIFICACION_PRUEBAS') && EMAIL_NOTIFICACION_PRUEBAS != '') {
                                $emx1 = EMAIL_NOTIFICACION_PRUEBAS;
                            } else {
                                $emx1 = 'jint@confecamaras.org.co';
                            }
                        }
                        $rEmail1 = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx1, 'Beneficios de ser formal en  LA ' . RAZONSOCIAL, $msgbenmat);
                        if ($rEmail === false) {
                            sleep(5);
                            $rEmail1 = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx1, 'Beneficios de ser formal en  LA ' . RAZONSOCIAL, $msgbenmat);
                        }
                        if ($rEmail === false) {
                            \logApi::general2($nameLog, $liq["idliquidacion"], 'Error enviando correo de beneficios a ' . $emx);
                            \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '20', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], '', '', $resultadoRecibo["nom"], $emx, $msgbenmat, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR **', $bandejaDigitalizacion);
                        } else {
                            \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '20', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], '', '', $resultadoRecibo["nom"], $emx, $msgbenmat, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '**OK **', $bandejaDigitalizacion);
                        }
                    }
                }
            }

            if (trim($msgbenren) != '') {
                foreach ($resultadoRecibo["listaemails"] as $emx) {
                    if (trim($emx) != '') {
                        $emx1 = $emx;
                        if (TIPO_AMBIENTE == 'PRUEBAS') {
                            if (defined('EMAIL_NOTIFICACION_PRUEBAS') && EMAIL_NOTIFICACION_PRUEBAS != '') {
                                $emx1 = EMAIL_NOTIFICACION_PRUEBAS;
                            } else {
                                $emx1 = 'jint@confecamaras.org.co';
                            }
                        }
                        $rEmail1 = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx1, 'Beneficios de renovar tu matrícula mercantil en  LA ' . RAZONSOCIAL, $msgbenren);
                        if ($rEmail === false) {
                            sleep(5);
                            $rEmail1 = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx1, 'Beneficios de renovar tu matrícula mercantil en  LA ' . RAZONSOCIAL, $msgbenren);
                        }
                        if ($rEmail === false) {
                            \logApi::general2($nameLog, $liq["idliquidacion"], 'Error enviando correo de beneficios a ' . $emx);
                            \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '21', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], '', '', $resultadoRecibo["nom"], $emx, $msgbenren, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR **', $bandejaDigitalizacion);
                        } else {
                            \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '21', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], '', '', $resultadoRecibo["nom"], $emx, $msgbenren, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '**OK **', $bandejaDigitalizacion);
                        }
                    }
                }
            }


            // Actualiza el estado de la notificacion en SIREP
            if ($emailvalidos == 0) {
                if (NOTIFICAR_RADICACION == 'SI') {
                    $arrCampos = array('estadoemail');
                    $arrValores = array("'2'");
                    regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $resultadoRecibo["nrec"] . "'");
                    \logApi::general2($nameLog, $liq["idliquidacion"], 'Actualizo a 2 estadoemail para el recibo ' . $resultadoRecibo["nrec"]);
                }
            }

            if ($emailvalidos > 0) {
                if (NOTIFICAR_RADICACION == 'SI') {
                    $arrCampos = array('estadoemail');
                    $arrValores = array("'1'");
                    regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $resultadoRecibo["nrec"] . "'");
                    \logApi::general2($nameLog, $liq["idliquidacion"], 'Actualizo a 1 estadoemail para el recibo ' . $resultadoRecibo["nrec"]);
                }
            }
        }

        // ***************************************************************************** //
        // Enviar a SMS
        // ***************************************************************************** //
        if (count($resultadoRecibo["listatelefonos"]) == 0) {
            $arrCampos = array('estadosms');
            $arrValores = array("'2'");
            regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $resultadoRecibo["nrec"] . "'");
            \logApi::general2($nameLog, $liq["idliquidacion"], 'Actualizo recibo sin celulares para notificar');
        } else {
            $mt1 = '';
            if (!empty($resultadoRecibo["mat"])) {
                foreach ($resultadoRecibo["mat"] as $mx) {
                    $mt1 = $mx;
                    if (defined('RAZONSOCIALSMS') && trim(RAZONSOCIALSMS) != '') {
                        $txtSms = 'La ' . RAZONSOCIALSMS . ' informa radicacion tramite expediente ' . $mt1 . ', al correo electronico se envio informacion para verificar procedencia.';
                    } else {
                        $txtSms = 'La ' . RAZONSOCIAL . ' informa radicacion tramite expediente ' . $mt1 . ', al correo electronico se envio informacion para verificar procedencia.';
                    }
//
                    if ($tt["tipo"] == 'PQ') {
                        if (defined('RAZONSOCIALSMS') && trim(RAZONSOCIALSMS) != '') {
                            $txtSms = 'La ' . RAZONSOCIALSMS . ' informa que el ' . \funcionesGenerales::mostrarFecha($resultadoRecibo["fec"]) . ' a las ' . \funcionesGenerales::mostrarHora($resultadoRecibo["hor"]) . ' se radico PQR expediente ' . $mt1;
                        } else {
                            $txtSms = 'La ' . RAZONSOCIAL . ' informa que el ' . \funcionesGenerales::mostrarFecha($resultadoRecibo["fec"]) . ' a las ' . \funcionesGenerales::mostrarHora($resultadoRecibo["hor"]) . ' se radico PQR expediente ' . $mt1;
                        }
                    }

                    foreach ($resultadoRecibo["listatelefonos"] as $t) {
                        \funcionesRegistrales::actualizarPilaSms($mysqli, '', $t, '1', $resultadoRecibo["nrec"], $resultadoRecibo["cba"], '', '', $mt1, $mt1, '', $resultadoRecibo["ide"], $resultadoRecibo["nom"], $txtSms, '', $bandejaDigitalizacion);
                    }
                }
            } else {
                if ($resultadoRecibo["pro"] != '') {
                    if (defined('RAZONSOCIALSMS') && trim(RAZONSOCIALSMS) != '') {
                        $txtSms = 'La ' . RAZONSOCIALSMS . ' informa radicacion tramite expediente ' . $resultadoRecibo["pro"] . ', al correo electronico se envio informacion para verificar procedencia.';
                    } else {
                        $txtSms = 'La ' . RAZONSOCIAL . ' informa radicacion tramite expediente ' . $resultadoRecibo["pro"] . ', al correo electronico se envio informacion para verificar procedencia.';
                    }
//
                    if ($tt["tipo"] == 'PQ') {
                        if (defined('RAZONSOCIALSMS') && trim(RAZONSOCIALSMS) != '') {
                            $txtSms = 'La ' . RAZONSOCIALSMS . ' informa que el ' . \funcionesGenerales::mostrarFecha($resultadoRecibo["fec"]) . ' a las ' . \funcionesGenerales::mostrarHora($resultadoRecibo["hor"]) . ' se radico PQR expediente ' . $resultadoRecibo["pro"];
                        } else {
                            $txtSms = 'La ' . RAZONSOCIAL . ' informa que el ' . \funcionesGenerales::mostrarFecha($resultadoRecibo["fec"]) . ' a las ' . \funcionesGenerales::mostrarHora($resultadoRecibo["hor"]) . ' se radico PQR expediente ' . $resultadoRecibo["pro"];
                        }
                    }
                    foreach ($resultadoRecibo["listatelefonos"] as $t) {
                        \funcionesRegistrales::actualizarPilaSms($mysqli, '', $t, '1', $resultadoRecibo["nrec"], $resultadoRecibo["cba"], '', '', '', '', $resultadoRecibo["pro"], $resultadoRecibo["ide"], $resultadoRecibo["nom"], $txtSms, '', $bandejaDigitalizacion);
                    }
                } else {
                    if (defined('RAZONSOCIALSMS') && trim(RAZONSOCIALSMS) != '') {
                        $txtSms = 'La ' . RAZONSOCIALSMS . ' informa radicacion tramite, al correo electronico se envio informacion para verificar procedencia.';
                    } else {
                        $txtSms = 'La ' . RAZONSOCIAL . ' informa radicacion tramite, al correo electronico se envio informacion para verificar procedencia.';
                    }
//
                    if ($tt["tipo"] == 'PQ') {
                        if (defined('RAZONSOCIALSMS') && trim(RAZONSOCIALSMS) != '') {
                            $txtSms = 'La ' . RAZONSOCIALSMS . ' informa que el ' . \funcionesGenerales::mostrarFecha($resultadoRecibo["fec"]) . ' a las ' . \funcionesGenerales::mostrarHora($resultadoRecibo["hor"]) . ' se radico PQR';
                        } else {
                            $txtSms = 'La ' . RAZONSOCIAL . ' informa que el ' . \funcionesGenerales::mostrarFecha($resultadoRecibo["fec"]) . ' a las ' . \funcionesGenerales::mostrarHora($resultadoRecibo["hor"]) . ' se radico PQR';
                        }
                    }

                    foreach ($resultadoRecibo["listatelefonos"] as $t) {
                        \funcionesRegistrales::actualizarPilaSms($mysqli, '', $t, '1', $resultadoRecibo["nrec"], $resultadoRecibo["cba"], '', '', '', '', '', $resultadoRecibo["ide"], $resultadoRecibo["nom"], $txtSms, '', $bandejaDigitalizacion);
                    }
                }
            }

            // ***************************************************************************** //
            // Actualización final del recibo como notificado
            // ***************************************************************************** //
            $arrCampos = array('estadosms');
            $arrValores = array("'1'");
            regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $resultadoRecibo["nrec"] . "'");
        }
    }

    public static function rutinaNotificarAsentamiento($mysqli, $recibo = '', $codbarras = '', $emailsentrada = array(), $celularesentrada = array(), $nameLog = '', $idliquidacion = 0) {

        ini_set('memory_limit', '1024M');

        if ($nameLog == '') {
            $nameLog = 'rutinaNotificarsentamiento_API_' . date("Ymd");
        }
        \logApi::general2($nameLog, $idliquidacion, 'Ingreso a notificar asentamiento : ' . $recibo . ' /' . $codbarras);

        //
        $_SESSION["generales"]["corterenovacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
        $_SESSION["generales"]["corterenovacionmesdia"] = substr($_SESSION["generales"]["corterenovacion"], 4, 4);
        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {
            $_SESSION["tramite"]["registroautomatico"] = retornarRegistroMysqliApi($mysqli, 'bas_tipotramites ', "id='" . $_SESSION["tramite"]["tipotramite"] . "'", "registroinmediato");
        } else {
            $_SESSION["tramite"]["registroautomatico"] = '';
        }

        //
        $reg = false;
        $notificar = 'si';
        $matriculasnotificar = array();

        // *********************************************************************************** //
        // Localiza el recibo a través del código de barras
        // *********************************************************************************** //
        if ($recibo == '' && $codbarras != '') {
            $temx = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $codbarras . "'");
            if ($temx && !empty($temx)) {
                if ($temx["recibo"] != '') {
                    $query = "recibo='" . $temx["recibo"] . "' and tipogasto IN ('0','4','6','8')";
                    $reg = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', $query);
                }
            }
            \logApi::general2($nameLog, $idliquidacion, 'Localizo recibo a traves del codigo de barras');
        }

        // *********************************************************************************** //
        // Localiza el recibo directamente por el numero
        // *********************************************************************************** //
        if ($recibo != '') {
            $query = "recibo='" . $recibo . "' and tipogasto IN ('0','4','6','8')";
            $reg = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', $query);
            \logApi::general2($nameLog, $idliquidacion, 'Localizo recibo a traves del nro de recibo');
        }

        // *********************************************************************************** //
        // Inicializa arreglo de respuesta
        // *********************************************************************************** //
        $resultadoRecibo = array();

        // ********************************************************************************* //
        // Si no encuentra el recibo se sale con false
        // ********************************************************************************* //
        if ($reg === false || empty($reg)) {
            $notificar = 'no';
            \logApi::general2($nameLog, $idliquidacion, 'Recibo no localizado : ' . $reg["recibo"]);
            return false;
        }

        // ********************************************************************************* //
        // Lee el código de barras
        // ********************************************************************************* //
        if ($reg["codigobarras"] === false || empty($reg["codigobarras"])) {
            $notificar = 'no';
            \logApi::general2($nameLog, $idliquidacion, 'Recibo sin codigo de barras');
            return false;
        }
        $arrTemCB = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $reg["codigobarras"] . "'");

        // ********************************************************************************* //
        // Si no encuentra el código de barras se sale con no
        // ********************************************************************************* //
        if ($arrTemCB === false || empty($arrTemCB)) {
            $notificar = 'no';
            \logApi::general2($nameLog, $idliquidacion, 'Código de barras no localizado  : ' . $reg["recibo"] . ' /' . $reg["codigobarras"]);
            return false;
        }

        // ********************************************************************************* //
        // Si el código de barras es de embargos
        // se sale sin notificar
        // ********************************************************************************* //
        if ($arrTemCB["actoreparto"] == '07' || $arrTemCB["actoreparto"] == '29') {
            $notificar = 'no';
            \logApi::general2($nameLog, $idliquidacion, 'Código de barras no notificable, embargo, desembargo o medida cautelar  : ' . $reg["recibo"] . ' /' . $reg["codigobarras"]);
            return false;
        }

        // ********************************************************************************* //
        // Localiza liquidacion
        // ********************************************************************************* //
        $liq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "numerorecibo='" . $reg["recibo"] . "'");
        if ($liq === false || empty($liq)) {
            $liq = false;
        }


        // ********************************************************************************* //
        // Localiza transaccion (solo en caso de compraventas)
        // ********************************************************************************* //
        if ($arrTemCB["actoreparto"] == '25' && $liq && !empty($liq)) {
            $liqtras = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidacion_transacciones', "idliquidacion=" . $liq["idliquidacion"], "id");
        } else {
            $liqtras = false;
        }

        // ********************************************************************************* //
        // Localiza bandeja de digitalización
        // ********************************************************************************* //
        $bandejaDigitalizacion = '';
        $tt = retornarRegistroMysqliApi($mysqli, 'mreg_codrutas', "id='" . $arrTemCB["actoreparto"] . "'");
        if ($tt && !empty($tt)) {
            $bandejaDigitalizacion = $tt["bandeja"];
        }

        //
        \logApi::general2($nameLog, $idliquidacion, 'Notificaciones: Localizo recibo : ' . $reg["recibo"]);

        // ********************************************************************************* //    
        // Inicializa variables para el conrol del envío de las notificaciones
        // ********************************************************************************* //    
        $resultadoRecibo["nrec"] = $reg["recibo"];
        $resultadoRecibo["cba"] = $reg["codigobarras"];
        $resultadoRecibo["ope"] = $reg["operacion"];
        $resultadoRecibo["fec"] = $reg["fecha"];
        $resultadoRecibo["hor"] = $reg["hora"];
        $resultadoRecibo["ide"] = $reg["identificacion"];
        $resultadoRecibo["nom"] = $reg["razonsocial"];
        $resultadoRecibo["mat"] = array();
        $resultadoRecibo["pro"] = '';
        $resultadoRecibo["ser"] = array();
        $resultadoRecibo["valor"] = $reg["valorneto"];
        $resultadoRecibo["tt"] = $reg["tipotramite"];
        $resultadoRecibo["emails"] = array();
        $resultadoRecibo["telefonos"] = array();
        $resultadoRecibo["emailshistoricos"] = array();
        $resultadoRecibo["telefonoshistoricos"] = array();

        // ********************************************************************************* //    
        // Encuentra correos y celulares asociados a la liquidacion
        // ********************************************************************************* //  
        if ($liq && !empty($liq)) {
            $liq["email"] = str_replace(".@", "@", $liq["email"]);
            $liq["emailpagador"] = str_replace(".@", "@", $liq["emailpagador"]);
            $reg["email"] = str_replace(".@", "@", $reg["email"]);

            if ($liq["email"] != '') {
                if (!isset($resultadoRecibo["emails"][$liq["email"]])) {
                    $resultadoRecibo["emails"][$liq["email"]] = $liq["email"];
                }
            }
            if ($liq["emailpagador"] != '') {
                if (!isset($resultadoRecibo["emails"][$liq["emailpagador"]])) {
                    $resultadoRecibo["emails"][$liq["emailpagador"]] = $liq["emailpagador"];
                }
            }
            if ($liq["telefono"] != '' && strlen($liq["telefono"]) == 10 && substr($liq["telefono"], 0, 1) == '3') {
                $resultadoRecibo["telefonos"][$liq["telefono"]] = $liq["telefono"];
            }
            if ($liq["movil"] != '' && strlen($liq["movil"]) == 10 && substr($liq["movil"], 0, 1) == '3') {
                $resultadoRecibo["telefonos"][$liq["movil"]] = $liq["movil"];
            }
            if ($liq["telefonopagador"] != '' && strlen($liq["telefonopagador"]) == 10 && substr($liq["telefonopagador"], 0, 1) == '3') {
                $resultadoRecibo["telefonos"][$liq["telefonopagador"]] = $liq["telefonopagador"];
            }
            if ($liq["movilpagador"] != '' && strlen($liq["movilpagador"]) == 10 && substr($liq["movilpagador"], 0, 1) == '3') {
                $resultadoRecibo["telefonos"][$liq["movilpagador"]] = $liq["movilpagador"];
            }
        }

        // ********************************************************************************* //    
        // Encuentra correos y celulares asociados al recibo
        // ********************************************************************************* // 
        $resultadoRecibo["emails"][$reg["email"]] = $reg["email"];
        if ($reg["telefono1"] != '' && strlen($reg["telefono1"]) == 10 && substr($reg["telefono1"], 0, 1) == '3') {
            $resultadoRecibo["telefonos"][$reg["telefono1"]] = $reg["telefono1"];
        }
        if ($reg["telefono2"] != '' && strlen($reg["telefono2"]) == 10 && substr($reg["telefono2"], 0, 1) == '3') {
            $resultadoRecibo["telefonos"][$reg["telefono2"]] = $reg["telefono2"];
        }

        // ********************************************************************************* //    
        // Encuentra correos y celulares asociados a la transaccion
        // ********************************************************************************* // 
        if ($liqtras && !empty($liqtras)) {
            foreach ($liqtras as $liqtra) {
                $liqtra["emailvendedor"] = str_replace(".@", "@", $reg["emailvendedor"]);
                $liqtra["emailcomprador"] = str_replace(".@", "@", $reg["emailcomprador"]);
                if ($liqtra["emailvendedor"] != '') {
                    if (!isset($resultadoRecibo["emails"][$liqtra["emailvendedor"]])) {
                        $resultadoRecibo["emails"][$liqtra["emailvendedor"]] = $liqtra["emailvendedor"];
                    }
                }
                if ($liqtra["emailcomprador"] != '') {
                    if (!isset($resultadoRecibo["emails"][$liqtra["emailcomprador"]])) {
                        $resultadoRecibo["emails"][$liqtra["emailcomprador"]] = $liqtra["emailcomprador"];
                    }
                }
                if ($liqtra["celularvendedor"] != '' && strlen($liqtra["celularvendedor"]) == 10 && substr($liqtra["celularvendedor"], 0, 1) == '3') {
                    if (!isset($resultadoRecibo["telefonos"][$liqtra["celularvendedor"]])) {
                        $resultadoRecibo["telefonos"][$liqtra["celularvendedor"]] = $liqtra["celularvendedor"];
                    }
                }
                if ($liqtra["celularcomprador"] != '' && strlen($liqtra["celularcomprador"]) == 10 && substr($liqtra["celularcomprador"], 0, 1) == '3') {
                    if (!isset($resultadoRecibo["telefonos"][$liqtra["celularcomprador"]])) {
                        $resultadoRecibo["telefonos"][$liqtra["celularcomprador"]] = $liqtra["celularcomprador"];
                    }
                }
            }
        }

        // ********************************************************************************* //    
        // Adiciona emails reportados como parámetros
        // ********************************************************************************* // 
        if (!empty($emailsentrada)) {
            foreach ($emailsentrada as $e) {
                $e = str_replace(".@", "@", $e);
                if (!isset($resultadoRecibo["emails"][$e])) {
                    $resultadoRecibo["emails"][$e] = $e;
                }
            }
        }

        // ********************************************************************************* //    
        // Adiciona celulares reportados como parámetros
        // ********************************************************************************* // 
        if (!empty($celularesentrada)) {
            foreach ($celularesentrada as $e) {
                if (!isset($resultadoRecibo["telefonos"][$e])) {
                    $resultadoRecibo["telefonos"][$e] = $e;
                }
            }
        }

        // ******************************************************************************************** //    
        // Busca las matrículas y proponentes asociadas al trámite a traves de recibosgenerados_detalle
        // ******************************************************************************************** // 

        $txtServicios = '';
        $esren = 'no';
        $esrentodosanos = '';
        $esmat = 'no';
        $esmut = 'no';
        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_detalle', "recibo='" . $reg["recibo"] . "'", "secuencia");
        $j = 0;
        if ($arrTem && !empty($arrTem)) {
            foreach ($arrTem as $tx) {
                if (substr($tx["idservicio"], 0, 6) == '010202') {
                    if ($tx["ano"] == date("Y")) {
                        $esrentodosanos = 'si';
                    }
                }
                $j++;
                if ($tx["matricula"] != '' && substr($tx["matricula"], 0, 5) != 'NUEVA') {
                    $resultadoRecibo["mat"][$tx["matricula"]] = $tx["matricula"];
                }
                if ($tx["proponente"] != '') {
                    $resultadoRecibo["pro"] = $tx["proponente"];
                }
                if (!isset($resultadoRecibo["ser"][$tx["idservicio"]])) {
                    $resultadoRecibo["ser"][$tx["idservicio"]] = $tx["idservicio"];
                    if ($txtServicios != '') {
                        $txtServicios .= ', ';
                    }
                    $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $tx["idservicio"] . "'");
                    $txtServicios .= $serv["nombre"];
                    if ($serv["tipoingreso"] == '07' || $serv["tipoingreso"] == '17') {
                        $esmut = 'si';
                    }
                    if ($serv["tipoingreso"] == '03' || $serv["tipoingreso"] == '13') {
                        $exp1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $tx["matricula"] . "'", "matricula,organizacion,categoria");
                        if ($exp1 && $exp1["organizacion"] == '01' || ($exp1["organizacion"] > '02' && $exp1["categoria"] == '1' && $exp1["organizacion"] != '12' && $exp1["organizacion"] != '14')) {
                            $esren = 'si';
                        }
                    }
                    if ($serv["tipoingreso"] == '02' || $serv["tipoingreso"] == '12') {
                        if ($tx["matricula"] == '' || $tx["matricula"] == 'NUEVANAT' || $tx["matricula"] == 'NUEVAJUR') {
                            $esmat = 'si';
                        } else {
                            if ($tx["matricula"] != '' && substr($tx["matricula"], 0, 5) != 'NUEVA') {
                                $exp1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $tx["matricula"] . "'", "matricula,organizacion,categoria");
                                if ($exp1 && $exp1["organizacion"] == '01' || ($exp1["organizacion"] > '02' && $exp1["categoria"] == '1' && $exp1["organizacion"] != '12' && $exp1["organizacion"] != '14')) {
                                    $esmat = 'si';
                                }
                            }
                        }
                    }
                }
            }
        }
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Servicio a reportar ' . $txtServicios);
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Es renovación ' . $esren . ',  Es mutacion ' . $esmut . ', Es matrícula o constitución ' . $esmat);

        //
        $txmats = '';
        if (!empty($resultadoRecibo["mat"])) {
            foreach ($resultadoRecibo["mat"] as $mtx) {
                if ($txmats != '') {
                    $txmats .= ', ';
                }
                $txmats .= $mtx;
            }
        }
        if ($txmats == '') {
            $txmats = 'Sin expedientes para notificar';
        }
        $txservs = '';
        if (!empty($resultadoRecibo["ser"])) {
            foreach ($resultadoRecibo["ser"] as $mtx) {
                if ($txservs != '') {
                    $txservs .= ', ';
                }
                $txservs .= $mtx;
            }
        }
        if ($txservs == '') {
            $txservs = 'Sin servicios para notificar';
        }
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Localizo matricula(s) : ' . $txmats);
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Localizo servicio(s) : ' . $txservs);

        // ************************************************************************************************** //
        // Adiciona los emails y celulares asociados al código de barras
        // ************************************************************************************************** //
        // $arrTemCB = retornarRegistroMysqli($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $reg["codigobarras"] . "'");
        if ($arrTemCB["emailnot1"] != '') {
            $arrTemCB["emailnot1"] = str_replace(".@", "@", $arrTemCB["emailnot1"]);
            $resultadoRecibo["emails"][$arrTemCB["emailnot1"]] = $arrTemCB["emailnot1"];
        }
        if ($arrTemCB["emailnot2"] != '') {
            $arrTemCB["emailnot2"] = str_replace(".@", "@", $arrTemCB["emailnot2"]);
            $resultadoRecibo["emails"][$arrTemCB["emailnot2"]] = $arrTemCB["emailnot2"];
        }
        if ($arrTemCB["emailnot3"] != '') {
            $arrTemCB["emailnot3"] = str_replace(".@", "@", $arrTemCB["emailnot3"]);
            $resultadoRecibo["emails"][$arrTemCB["emailnot3"]] = $arrTemCB["emailnot3"];
        }
        if ($arrTemCB["celnot1"] != '' && strlen($arrTemCB["celnot1"]) == 10 && substr($arrTemCB["celnot1"], 0, 1) == '3') {
            $resultadoRecibo["telefonos"][$arrTemCB["celnot1"]] = $arrTemCB["celnot1"];
        }
        if ($arrTemCB["celnot2"] != '' && strlen($arrTemCB["celnot2"]) == 10 && substr($arrTemCB["celnot2"], 0, 1) == '3') {
            $resultadoRecibo["telefonos"][$arrTemCB["celnot2"]] = $arrTemCB["celnot2"];
        }
        if ($arrTemCB["celnot3"] != '' && strlen($arrTemCB["celnot3"]) == 10 && substr($arrTemCB["celnot3"], 0, 1) == '3') {
            $resultadoRecibo["telefonos"][$arrTemCB["celnot3"]] = $arrTemCB["celnot3"];
        }

        // *********************************************************************************** //
        // Busca cada expediente
        // *********************************************************************************** //            
        if (!empty($resultadoRecibo["mat"])) {
            foreach ($resultadoRecibo["mat"] as $m) {
                $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $m . "'");

                // *********************************************************************************** //
                // Localiza emails y celulares actuales
                // *********************************************************************************** //                                    
                if ($exp && !empty($exp)) {
                    if (trim($exp["telcom1"]) != '' && strlen($exp["telcom1"]) == 10 && substr($exp["telcom1"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$exp["telcom1"]] = $exp["telcom1"];
                    }
                    if (trim($exp["telcom2"]) != '' && strlen($exp["telcom2"]) == 10 && substr($exp["telcom2"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$exp["telcom2"]] = $exp["telcom2"];
                    }
                    if (trim($exp["telcom3"]) != '' && strlen($exp["telcom3"]) == 10 && substr($exp["telcom3"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$exp["telcom3"]] = $exp["telcom3"];
                    }
                    if (trim($exp["telnot"]) != '' && strlen($exp["telnot"]) == 10 && substr($exp["telnot"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$exp["telnot"]] = $exp["telnot"];
                    }
                    if (trim($exp["telnot2"]) != '' && strlen($exp["telnot2"]) == 10 && substr($exp["telnot2"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$exp["telnot2"]] = $exp["telnot2"];
                    }
                    if (trim($exp["telnot3"]) != '' && strlen($exp["telnot3"]) == 10 && substr($exp["telnot3"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$exp["telnot3"]] = $exp["telnot3"];
                    }
                    if (trim($exp["emailcom"]) != '') {
                        $exp["emailcom"] = str_replace(".@", "@", $exp["emailcom"]);
                        $resultadoRecibo["emails"][$exp["emailcom"]] = $exp["emailcom"];
                    }
                    if (trim($exp["emailcom2"]) != '') {
                        $exp["emailcom2"] = str_replace(".@", "@", $exp["emailcom2"]);
                        $resultadoRecibo["emails"][$exp["emailcom2"]] = $exp["emailcom2"];
                    }
                    if (trim($exp["emailcom3"]) != '') {
                        $exp["emailcom3"] = str_replace(".@", "@", $exp["emailcom3"]);
                        $resultadoRecibo["emails"][$exp["emailcom3"]] = $exp["emailcom3"];
                    }
                    if (trim($exp["emailnot"]) != '') {
                        $exp["emailnot"] = str_replace(".@", "@", $exp["emailnot"]);
                        $resultadoRecibo["emails"][$exp["emailnot"]] = $exp["emailnot"];
                    }
                }

                // *********************************************************************************** //
                // Localiza emails y celulares anteriores
                // Solamente en caso de renovaciones o mutaciones
                // *********************************************************************************** //  
                if ($esren == 'si' || $esmut == 'si') {
                    // *********************************************************************************** //
                    // Localiza emails y celulares modificados en mreg_campos_historicos_AAAA
                    // *********************************************************************************** //                                                            
                    $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'telcom1');
                    $ictos = 0;
                    foreach ($d as $d1) {
                        if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                            $ictos++;
                            if ($ictos < 3) {
                                if (!isset($resultadoRecibo["telefonoshistoricos"][trim($d1)])) {
                                    if (!isset($resultadoRecibo["telefonos"][trim($d1)])) {
                                        $resultadoRecibo["telefonoshistoricos"][trim($d1)] = trim($d1);
                                    }
                                }
                            }
                        }
                    }

                    $ictos = 0;
                    $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'telcom2');
                    foreach ($d as $d1) {
                        if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                            $ictos++;
                            if ($ictos < 3) {
                                if (!isset($resultadoRecibo["telefonoshistoricos"][trim($d1)])) {
                                    if (!isset($resultadoRecibo["telefonos"][trim($d1)])) {
                                        $resultadoRecibo["telefonoshistoricos"][trim($d1)] = trim($d1);
                                    }
                                }
                            }
                        }
                    }

                    $ictos = 0;
                    $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'telcom3');
                    foreach ($d as $d1) {
                        if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                            $ictos++;
                            if ($ictos < 3) {
                                if (!isset($resultadoRecibo["telefonoshistoricos"][trim($d1)])) {
                                    if (!isset($resultadoRecibo["telefonos"][trim($d1)])) {
                                        $resultadoRecibo["telefonoshistoricos"][trim($d1)] = trim($d1);
                                    }
                                }
                            }
                        }
                    }

                    $ictos = 0;
                    $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'telnot');
                    foreach ($d as $d1) {
                        if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                            $ictos++;
                            if ($ictos < 3) {
                                if (!isset($resultadoRecibo["telefonoshistoricos"][trim($d1)])) {
                                    if (!isset($resultadoRecibo["telefonos"][trim($d1)])) {
                                        $resultadoRecibo["telefonoshistoricos"][trim($d1)] = trim($d1);
                                    }
                                }
                            }
                        }
                    }

                    $ictos = 0;
                    $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'telnot2');
                    foreach ($d as $d1) {
                        if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                            $ictos++;
                            if ($ictos < 3) {
                                if (!isset($resultadoRecibo["telefonoshistoricos"][trim($d1)])) {
                                    if (!isset($resultadoRecibo["telefonos"][trim($d1)])) {
                                        $resultadoRecibo["telefonoshistoricos"][trim($d1)] = trim($d1);
                                    }
                                }
                            }
                        }
                    }

                    $ictos = 0;
                    $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'telnot3');
                    foreach ($d as $d1) {
                        if (trim($d1) != '' && strlen($d1) == 10 && substr($d1, 0, 1) == '3') {
                            $ictos++;
                            if ($ictos < 3) {
                                if (!isset($resultadoRecibo["telefonoshistoricos"][trim($d1)])) {
                                    if (!isset($resultadoRecibo["telefonos"][trim($d1)])) {
                                        $resultadoRecibo["telefonoshistoricos"][trim($d1)] = trim($d1);
                                    }
                                }
                            }
                        }
                    }

                    $ictos = 0;
                    $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'emailcom');
                    foreach ($d as $d1) {
                        $ictos++;
                        if ($ictos < 3) {
                            $d1 = str_replace(".@", "@", $d1);
                            if (!isset($resultadoRecibo["emailshistoricos"][trim($d1)])) {
                                if (!isset($resultadoRecibo["emails"][trim($d1)])) {
                                    $resultadoRecibo["emailshistoricos"][trim($d1)] = trim($d1);
                                }
                            }
                        }
                    }

                    $ictos = 0;
                    $d = localizarCampoAnteriorTodosMysqliApi($mysqli, $m, 'emailnot');
                    foreach ($d as $d1) {
                        $ictos++;
                        if ($ictos < 3) {
                            $d1 = str_replace(".@", "@", $d1);
                            if (!isset($resultadoRecibo["emailshistoricos"][trim($d1)])) {
                                if (!isset($resultadoRecibo["emails"][trim($d1)])) {
                                    $resultadoRecibo["emailshistoricos"][trim($d1)] = trim($d1);
                                }
                            }
                        }
                    }
                }
            }
        }

        // *********************************************************************************** //
        // Recupera números telefonicos y emails actuales - proponentes
        // *********************************************************************************** //
        if ($resultadoRecibo["pro"] != '') {
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_proponentes', "proponente='" . $resultadoRecibo["pro"] . "'");
            if ($exp && !empty($exp)) {
                if (trim($exp["telcom1"]) != '' && strlen($exp["telcom1"]) == 10 && substr($exp["telcom1"], 0, 1) == '3') {
                    if (!isset($resultadoRecibo["telefonos"][$exp["telcom1"]])) {
                        $resultadoRecibo["telefonos"][$exp["telcom1"]] = $exp["telcom1"];
                    }
                }
                if (trim($exp["telcom2"]) != '' && strlen($exp["telcom2"]) == 10 && substr($exp["telcom2"], 0, 1) == '3') {
                    if (!isset($resultadoRecibo["telefonos"][$exp["telcom2"]])) {
                        $resultadoRecibo["telefonos"][$exp["telcom2"]] = $exp["telcom2"];
                    }
                }
                if (trim($exp["celcom"]) != '' && strlen($exp["celcom"]) == 10 && substr($exp["celcom"], 0, 1) == '3') {
                    if (!isset($resultadoRecibo["telefonos"][$exp["celcom"]])) {
                        $resultadoRecibo["telefonos"][$exp["celcom"]] = $exp["celcom"];
                    }
                }
                if (trim($exp["telnot"]) != '' && strlen($exp["telnot"]) == 10 && substr($exp["telnot"], 0, 1) == '3') {
                    if (!isset($resultadoRecibo["telefonos"][$exp["telnot"]])) {
                        $resultadoRecibo["telefonos"][$exp["telnot"]] = $exp["telnot"];
                    }
                }
                if (trim($exp["telnot2"]) != '' && strlen($exp["telnot2"]) == 10 && substr($exp["telnot2"], 0, 1) == '3') {
                    if (!isset($resultadoRecibo["telefonos"][$exp["telnot2"]])) {
                        $resultadoRecibo["telefonos"][$exp["telnot2"]] = $exp["telnot2"];
                    }
                }
                if (trim($exp["celnot"]) != '' && strlen($exp["celnot"]) == 10 && substr($exp["celnot"], 0, 1) == '3') {
                    if (!isset($resultadoRecibo["telefonos"][$exp["celnot"]])) {
                        $resultadoRecibo["telefonos"][$exp["celnot"]] = $exp["celnot"];
                    }
                }
                if (trim($exp["emailcom"]) != '') {
                    $exp["emailcom"] = str_replace(".@", "@", $exp["emailcom"]);
                    if (!isset($resultadoRecibo["emails"][$exp["emailcom"]])) {
                        $resultadoRecibo["emails"][$exp["emailcom"]] = $exp["emailcom"];
                    }
                }
                if (trim($exp["emailnot"]) != '') {
                    $exp["emailnot"] = str_replace(".@", "@", $exp["emailnot"]);
                    if (!isset($resultadoRecibo["emails"][$exp["emailnot"]])) {
                        $resultadoRecibo["emails"][$exp["emailnot"]] = $exp["emailnot"];
                    }
                }
            }
        }

        // unset($reg);
        // *********************************************************************************** //
        // Log de cantidad de emails y celulares
        // *********************************************************************************** //
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Total emails vigentes : ' . count($resultadoRecibo["emails"]));
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Total emails históricos : ' . count($resultadoRecibo["emailshistoricos"]));
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Total celulares vigentes : ' . count($resultadoRecibo["telefonos"]));
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Notificaciones: Total celulares históricos : ' . count($resultadoRecibo["telefonoshistoricos"]));

        // *********************************************************************************** //
        // Localiza descripción de servicio(s) a notificar
        // *********************************************************************************** //        
        $sinemails = 0;

        // *********************************************************************************** //
        // 2019-09-26: JINT.
        // Determinar si se envía o no a los históricos
        // En caso que no se envie, limpiar los arreglos de históricos y guardar log
        // *********************************************************************************** //
        // *********************************************************************************** //
        // Une arreglos de emails y celurares
        // *********************************************************************************** //
        $resultadoRecibo["listaemails"] = array_merge($resultadoRecibo["emails"], $resultadoRecibo["emailshistoricos"]);
        $resultadoRecibo["listatelefonos"] = array_merge($resultadoRecibo["telefonos"], $resultadoRecibo["telefonoshistoricos"]);
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Cantidad de correos electronicos ' . count($resultadoRecibo["listaemails"]));
        \logApi::general2($nameLog, $liq["idliquidacion"], 'Cantidad de celulares ' . count($resultadoRecibo["listatelefonos"]));

//
        $arrCampos = array(
            'recibo',
            'codigobarras',
            'idliquidacion',
            'tipo',
            'contenido'
        );
        $arrValores = array();
        if (!empty($resultadoRecibo["listaemails"])) {
            foreach ($resultadoRecibo["listaemails"] as $e) {
                $arrValores[] = array(
                    "'" . $reg["recibo"] . "'",
                    "'" . $reg["codigobarras"] . "'",
                    $reg["idliquidacion"],
                    "'email-asentamiento'",
                    "'" . $e . "'"
                );
            }
        }
        if (!empty($resultadoRecibo["listatelefonos"])) {
            foreach ($resultadoRecibo["listatelefonos"] as $e) {
                $arrValores[] = array(
                    "'" . $reg["recibo"] . "'",
                    "'" . $reg["codigobarras"] . "'",
                    $reg["idliquidacion"],
                    "'telefono-asentamiento'",
                    "'" . $e . "'"
                );
            }
        }

        // *********************************************************************************** //
        // Almacena tabla donde guarda que emails y celulares será notificados por recibo
        // *********************************************************************************** //
        if (!empty($arrValores)) {
            insertarRegistrosBloqueMysqliApi($mysqli, 'mreg_recibos_sipref_destinos', $arrCampos, $arrValores);
            \logApi::general2($nameLog, $liq["idliquidacion"], 'Inserto mreg_recibos_sipref_destinos');
        }

        // *********************************************************************************** //
        // Envía emails de notificaicón
        // *********************************************************************************** //
        if (count($resultadoRecibo["listaemails"]) == 0) {
            $arrCampos = array('estadoemail');
            $arrValores = array("'2'");
            regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $resultadoRecibo["nrec"] . "'");
            $sinemails++;
            \logApi::general2($nameLog, $liq["idliquidacion"], 'Actualizo recibo sin emails para notificar');
        } else {

            // ***************************************************************************************** //
            // En caso de renovación que se asienta automáticamente
            // ***************************************************************************************** //
            $msg1 = '';
            $msg1 .= 'LA ' . RAZONSOCIAL . ' le informa que el dia ' . \funcionesGenerales::mostrarFecha($resultadoRecibo["fec"]) . ' a las ' . \funcionesGenerales::mostrarHora($resultadoRecibo["hor"]) . ' ';
            $msg1 .= 'fue asentada en los registros publicos que ';
            $msg1 .= 'administra y maneja nuestra organizacion una renovacion con la siguiente informacion: <br><br>';
            $msg1 .= 'Recibo de Caja No. ' . $resultadoRecibo["nrec"] . '<br>';
            $msg1 .= 'Numero operacion: ' . $resultadoRecibo["ope"] . '<br>';
            if (ltrim($resultadoRecibo["cba"], "0") != '') {
                $msg1 .= 'Codigo de barras: ' . $resultadoRecibo["cba"] . '<br>';
            }
            $mats = '';
            if (!empty($resultadoRecibo["mat"])) {
                foreach ($resultadoRecibo["mat"] as $mt1) {
                    if ($mats != '') {
                        $mats .= ', ';
                    }
                    $mats .= $mt1;
                }
            }
            if ($mats != '') {
                $msg1 .= 'Matriculas/Inscripciones: ' . $mats . '<br>';
            }
            if (ltrim($resultadoRecibo["pro"], "0") != '') {
                $msg1 .= 'Proponente: ' . $resultadoRecibo["pro"] . '<br>';
            }

            $msg1 .= 'Identificacion: ' . $resultadoRecibo["ide"] . '<br>';
            $msg1 .= 'Nombre: ' . ($resultadoRecibo["nom"]) . '<br>';
            $msg1 .= 'Tramite: ' . $txtServicios . '<br>';
            foreach ($resultadoRecibo["listaemails"] as $emx) {
                $msg1 .= 'Email : ' . $emx . '<br>';
            }
            $msg1 .= '<br>';
            $msg1 .= 'Valor de la transaccion: ' . $resultadoRecibo["valor"] . '<br><br>';

            $msgbenmat = '';
            $msgbenren = '';

            // 2020-02-21: JINT: Se incluye nota si es renovación y si la fecha del recibo es anterior al 31 de marzo
            if ($esren == 'si' && substr($reg["fecha"], 4, 4) <= $_SESSION["generales"]["corterenovacionmesdia"]) {
                if ($esrentodosanos == 'si') {
                    $msgbenren = \funcionesGenerales::cambiarSustitutoHtml(\funcionesGenerales::retornarPantallaPredisenada($mysqli, 'texto.beneficios.renovacion'));
                }
            }

            // 2020-02-21: JINT: Se incluye nota si es matricula 
            if ($esmat == 'si') {
                $msgbenmat = \funcionesGenerales::cambiarSustitutoHtml(\funcionesGenerales::retornarPantallaPredisenada($mysqli, 'texto.beneficios.matricula'));
            }

            // 2020-02-21: JINT: Se incluye nota si es renovación y si la fecha del recibo es anterior al 31 de marzo
            if ($esren == 'si' && substr($reg["fecha"], 4, 4) <= $_SESSION["generales"]["corterenovacionmesdia"]) {
                if ($esrentodosanos == 'si') {
                    $msg1 .= 'Señor empresario, si realiza la renovación oportuna de su Matrícula Mercantil y la de sus establecimientos de comercio ';
                    $msg1 .= 'puede acceder a diversos beneficios. Lo invitamos a que consulte el portafolio de servicios ';
                    $msg1 .= 'y programas en la página web de su Cámara de Comercio<br><br>';
                }
            }

            // 2020-02-21: JINT: Se incluye nota si es matricula 
            if ($esmat == 'si') {
                $msg1 .= 'Señor empresario, por matricularse en la Cámara de Comercio puede acceder a diversos beneficios. Lo invitamos a que consulte el portafolio ';
                $msg1 .= 'de servicios y programas en la página web de su Cámara de Comercio<br><br>';
            }

            if (!defined('NOTIFICAR_TELEFONO')) {
                defined('NOTIFICAR_TELEFONO', 'NO');
            }
            if (NOTIFICAR_TELEFONO == 'SI') {
                $msg1 .= 'Si tiene alguna duda o inquietud con el contenido de esta notificacion, puede comunicarse al ';
                $msg1 .= 'numero ' . TELEFONO_ATENCION_USUARIOS . ' en la ciudad de ' . retornarNombreMunicipioMysqliApi($mysqli, MUNICIPIO) . ' ';
                $msg1 .= 'citando el tramite (recibo de caja) No. ' . $resultadoRecibo["nrec"] . '<br><br>';
            }

            $msg1 .= 'Este mensaje se envia en forma automatica por el Sistema de Registro de LA ' . RAZONSOCIAL . ' ';
            $msg1 .= 'en cumplimiento a lo contemplado en el Codigo de Procedimiento Administrativo y de lo Contencioso Administrativo.';
            $msg1 .= '<br><br>';
            $msg1 .= 'Correo desatendido: Por favor no responda a la direccion de correo electronico que envia este mensaje, dicha cuenta ';
            $msg1 .= 'no es revisada por ningun funcionario de nuestra entidad. Este mensaje es informativo.';
            $msg1 .= '<br><br>';
            $msg1 .= 'Los acentos y tildes de este correo han sido omitidos intencionalmente con el objeto de evitar inconvenientes en la lectura del mismo.';

            $emailtotales = 0;
            $emailvalidos = 0;
            foreach ($resultadoRecibo["listaemails"] as $emx) {
                if (trim($emx) != '') {
                    \logApi::general2($nameLog, $liq["idliquidacion"], 'Enviara correo a ' . $emx);
                    $emx1 = $emx;
                    if (TIPO_AMBIENTE == 'PRUEBAS') {
                        if (defined('EMAIL_NOTIFICACION_PRUEBAS') && EMAIL_NOTIFICACION_PRUEBAS != '') {
                            $emx1 = EMAIL_NOTIFICACION_PRUEBAS;
                        } else {
                            $emx1 = 'jint@confecamaras.org.co';
                        }
                    }
                    $emailtotales++;
                    if (\funcionesGenerales::validarEmail($emx1) === true) {
                        if ($resultadoRecibo["tt"] == 'renovacionmatricula' || $resultadoRecibo["tt"] == 'renovacionesadl') {
                            if (substr($_SESSION["tramite"]["numerooperacion"], 0, 2) != '97' && substr($_SESSION["tramite"]["numerooperacion"], 0, 2) != '99') {
                                $rEmail1 = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx1, 'Notificacion de asentamiento No. ' . $resultadoRecibo["nrec"] . ' en  LA ' . RAZONSOCIAL, $msg1);
                            } else {
                                $rEmail1 = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx1, 'Notificacion de asentamiento No. ' . $resultadoRecibo["nrec"] . ' en  LA ' . RAZONSOCIAL, $msg1);
                            }
                        } else {
                            unset($rEmail1);
                        }

//
                        if (($arrTemCB["actoreparto"] != '07') && ($arrTemCB["actoreparto"] != '29')) {
                            if (!isset($resultadoRecibo["mat"]) || empty($resultadoRecibo["mat"])) {
                                $resultadoRecibo["mat"] = array();
                            }
                            if (isset($rEmail1)) {
                                if ($rEmail1 === false) {
                                    if (empty($resultadoRecibo["mat"])) {
                                        \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '10', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], '', $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg1, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR : ' . $_SESSION["generales"]["mensajeerror"], $bandejaDigitalizacion);
                                        \logApi::general2($nameLog, $liq["idliquidacion"], 'Error enviando notificacion (10) a ' . $emx1);
                                    } else {
                                        foreach ($resultadoRecibo["mat"] as $mt1) {
                                            \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '10', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], $mt1, $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg1, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR : ' . $_SESSION["generales"]["mensajeerror"], $bandejaDigitalizacion);
                                            \logApi::general2($nameLog, $liq["idliquidacion"], 'Error enviando notificacion (10) a ' . $emx1);
                                        }
                                    }
                                } else {
                                    if (empty($resultadoRecibo["mat"])) {
                                        \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '10', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], '', $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg1, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '**OK **', $bandejaDigitalizacion);
                                        \logApi::general2($nameLog, $liq["idliquidacion"], 'Envio notificacion (10) a ' . $emx1);
                                    } else {
                                        foreach ($resultadoRecibo["mat"] as $mt1) {
                                            \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '10', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], $mt1, $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg1, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '**OK **', $bandejaDigitalizacion);
                                            \logApi::general2($nameLog, $liq["idliquidacion"], 'Envio notificacion (10) a ' . $emx1);
                                        }
                                    }
                                }
                            }
                        } else {
                            if (isset($resultadoRecibo["mat"]) && !empty($resultadoRecibo["mat"])) {
                                foreach ($resultadoRecibo["mat"] as $mt1) {
                                    if (isset($rEmail1)) {
                                        if ($rEmail1 === false) {
                                            \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '10', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], $mt1, $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg1, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR : ' . $_SESSION["generales"]["mensajeerror"], $bandejaDigitalizacion);
                                            \logApi::general2($nameLog, $liq["idliquidacion"], 'Error enviando notificacion (10) a ' . $emx1 . ', expediente ' . $mt1);
                                        } else {
                                            \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '10', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], $mt1, $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg1, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '**OK **', $bandejaDigitalizacion);
                                            \logApi::general2($nameLog, $liq["idliquidacion"], 'Envio notificacion (10) a ' . $emx1 . ', expediente ' . $mt1);
                                        }
                                    }
                                }
                            } else {
                                if (isset($rEmail1)) {
                                    if ($rEmail1 === false) {
                                        \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '10', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], '', '', $resultadoRecibo["nom"], $emx, $msg1, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR : ' . $_SESSION["generales"]["mensajeerror"], $bandejaDigitalizacion);
                                        \logApi::general2($nameLog, $liq["idliquidacion"], 'Error enviando notificacion (10) a ' . $emx);
                                    } else {
                                        \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '10', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], '', '', $resultadoRecibo["nom"], $emx, $msg1, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '**OK **', $bandejaDigitalizacion);
                                        \logApi::general2($nameLog, $liq["idliquidacion"], 'Envio notificacion (10) a ' . $emx);
                                    }
                                }
                            }
                        }

                        //
                        if (isset($rEmail1) && $rEmail1) {
                            $emailvalidos++;
                        }
                    }
                }
            }

            // Cunmplimiento al numeral 
            // tetxo adicional de beneficios
            if (trim($msgbenmat) != '') {
                foreach ($resultadoRecibo["listaemails"] as $emx) {
                    if (trim($emx) != '') {
                        $emx1 = $emx;
                        if (TIPO_AMBIENTE == 'PRUEBAS') {
                            if (defined('EMAIL_NOTIFICACION_PRUEBAS') && EMAIL_NOTIFICACION_PRUEBAS != '') {
                                $emx1 = EMAIL_NOTIFICACION_PRUEBAS;
                            } else {
                                $emx1 = 'jint@confecamaras.org.co';
                            }
                        }
                        $rEmail1 = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx1, 'Beneficios de ser formal en  LA ' . RAZONSOCIAL, $msgbenmat);
                        if ($rEmail1 === false) {
                            \logApi::general2($nameLog, $liq["idliquidacion"], 'Error enviando correo de beneficios a ' . $emx);
                            \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '20', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], '', '', $resultadoRecibo["nom"], $emx, $msgbenmat, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR **', $bandejaDigitalizacion);
                        } else {
                            \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '20', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], '', '', $resultadoRecibo["nom"], $emx, $msgbenmat, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '**OK **', $bandejaDigitalizacion);
                        }
                    }
                }
            }

            if (trim($msgbenren) != '') {
                foreach ($resultadoRecibo["listaemails"] as $emx) {
                    if (trim($emx) != '') {
                        $emx1 = $emx;
                        if (TIPO_AMBIENTE == 'PRUEBAS') {
                            if (defined('EMAIL_NOTIFICACION_PRUEBAS') && EMAIL_NOTIFICACION_PRUEBAS != '') {
                                $emx1 = EMAIL_NOTIFICACION_PRUEBAS;
                            } else {
                                $emx1 = 'jint@confecamaras.org.co';
                            }
                        }
                        $rEmail1 = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx1, 'Beneficios de renovar tu matrícula mercantil en  LA ' . RAZONSOCIAL, $msgbenren);
                        if ($rEmail1 === false) {
                            \logApi::general2($nameLog, $liq["idliquidacion"], 'Error enviando correo de beneficios a ' . $emx);
                            \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '21', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], '', '', $resultadoRecibo["nom"], $emx, $msgbenren, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR **', $bandejaDigitalizacion);
                        } else {
                            \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '21', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], '', '', $resultadoRecibo["nom"], $emx, $msgbenren, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '**OK **', $bandejaDigitalizacion);
                        }
                    }
                }
            }


            // Actualiza el estado de la notificacion en SIREP
            if ($emailvalidos == 0) {
                if (NOTIFICAR_RADICACION == 'SI') {
                    $arrCampos = array('estadoemail');
                    $arrValores = array("'2'");
                    regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $resultadoRecibo["nrec"] . "'");
                    \logApi::general2($nameLog, $liq["idliquidacion"], 'Actualizo a 2 estadoemail para el recibo ' . $resultadoRecibo["nrec"]);
                }
            }

            if ($emailvalidos > 0) {
                if (NOTIFICAR_RADICACION == 'SI') {
                    $arrCampos = array('estadoemail');
                    $arrValores = array("'1'");
                    regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $resultadoRecibo["nrec"] . "'");
                    \logApi::general2($nameLog, $liq["idliquidacion"], 'Actualizo a 1 estadoemail para el recibo ' . $resultadoRecibo["nrec"]);
                }
            }
        }

        // ***************************************************************************** //
        // Enviar a SMS
        // ***************************************************************************** //
        if (count($resultadoRecibo["listatelefonos"]) == 0) {
            $arrCampos = array('estadosms');
            $arrValores = array("'2'");
            regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $resultadoRecibo["nrec"] . "'");
            \logApi::general2($nameLog, $liq["idliquidacion"], 'Actualizo recibo sin celulares para notificar');
        } else {
            $mt1 = '';
            foreach ($resultadoRecibo["mat"] as $mx) {
                $mt1 = $mx;
                if (defined('RAZONSOCIALSMS') && trim(RAZONSOCIALSMS) != '') {
                    $txtSms1 = 'La ' . RAZONSOCIALSMS . ' informa asentamiento renovacion expediente ' . ltrim($mt1, "0") . ltrim($resultadoRecibo["pro"], "0") . ', al correo electronico se envio informacion para verificar procedencia.';
                } else {
                    $txtSms1 = 'La ' . RAZONSOCIAL . ' informa asentamiento renovacion expediente ' . ltrim($mt1, "0") . ltrim($resultadoRecibo["pro"], "0") . ', al correo electronico se envio informacion para verificar procedencia.';
                }

                foreach ($resultadoRecibo["listatelefonos"] as $t) {
                    $exp1 = '';
                    if (ltrim($mt1, "0") != '') {
                        $exp1 = $mt1;
                    }
                    if (ltrim($resultadoRecibo["pro"], "0") != '') {
                        $exp1 = $resultadoRecibo["pro"];
                    }
                    // \funcionesRegistrales::actualizarPilaSms($mysqli, '', $t, '1', $resultadoRecibo["nrec"], $resultadoRecibo["cba"], '', '', $exp1, $mt1, $resultadoRecibo["pro"], $resultadoRecibo["ide"], $resultadoRecibo["nom"], $txtSms, '', $bandejaDigitalizacion);
                    // Notifica asentamiento de la renovación
                    if ($resultadoRecibo["tt"] == 'renovacionmatricula' || $resultadoRecibo["tt"] == 'renovacionesadl') {
                        \funcionesRegistrales::actualizarPilaSms($mysqli, '', $t, '10', $resultadoRecibo["nrec"], $resultadoRecibo["cba"], '', '', $exp1, $mt1, $resultadoRecibo["pro"], $resultadoRecibo["ide"], $resultadoRecibo["nom"], $txtSms1, '', $bandejaDigitalizacion);
                    }
                }
            }

            // ***************************************************************************** //
            // Actualización final del recibo como notificado
            // ***************************************************************************** //
            $arrCampos = array('estadosms');
            $arrValores = array("'1'");
            regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $resultadoRecibo["nrec"] . "'");
        }
    }

    /**
     * 
     * @param type $mysqli
     * @param type $codbarras
     * @param string $nameLog
     * @return bool
     */
    public static function rutinaInformarArchivoTramite($mysqli, $codbarras = '', $nameLog = '') {

        ini_set('memory_limit', '1024M');

        if ($nameLog == '') {
            $nameLog = 'rutinaInfomarArchivoTramite_API_' . date("Ymd");
        }
        \logApi::general2($nameLog, $codbarras, 'Ingreso a informar archivo de tramite  : ' . $codbarras);

        //
        $reg = false;
        // *********************************************************************************** //
        // Localiza el recibo a través del código de barras
        // *********************************************************************************** //
        if ($codbarras == '') {
            \logApi::general2($nameLog, $codbarras, 'No se indico codigo de barras');
            return false;
        }

        //
        $arrTemCB = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $codbarras . "'");

        // ********************************************************************************* //
        // Si no encuentra el código de barras se sale con no
        // ********************************************************************************* //
        if ($arrTemCB === false || empty($arrTemCB)) {
            \logApi::general2($nameLog, $codbarras, 'Código de barras no localizado  : ' . $reg["codigobarras"]);
            return false;
        }

        if ($arrTemCB["recibo"] != '') {
            $query = "recibo='" . $arrTemCB["recibo"] . "' and tipogasto IN ('0','4','6','8')";
            $reg = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', $query);
        }

        // *********************************************************************************** //
        // Localiza el recibo directamente por el numero
        // *********************************************************************************** //
        if ($reg == false || empty($reg)) {
            \logApi::general2($nameLog, $codbarras, 'No se localizo el recibo asociado al codigo de barras');
            return false;
        }

        // *********************************************************************************** //
        // Inicializa arreglo de respuesta
        // *********************************************************************************** //
        $resultadoRecibo = array();

        // ********************************************************************************* //
        // Si el código de barras es de embargos
        // se sale sin notificar
        // ********************************************************************************* //
        if ($arrTemCB["actoreparto"] == '07' || $arrTemCB["actoreparto"] == '29' || $arrTemCB["actoreparto"] == '81') {
            \logApi::general2($nameLog, $codbarras, 'Código de barras no notificable, embargo, desembargo o medida cautelar  : ' . $reg["recibo"] . ' /' . $reg["codigobarras"]);
            return false;
        }

        // ********************************************************************************* //
        // Localiza liquidacion
        // ********************************************************************************* //
        $liq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "numerorecibo='" . $reg["recibo"] . "'");
        if ($liq === false || empty($liq)) {
            \logApi::general2($nameLog, $codbarras, 'No se localizo la liquidacion asociada al tramite');
            return false;
        }


        // ********************************************************************************* //
        // Localiza transaccion (solo en caso de compraventas)
        // ********************************************************************************* //
        if ($arrTemCB["actoreparto"] == '25' && $liq && !empty($liq)) {
            $liqtras = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidacion_transacciones', "idliquidacion=" . $liq["idliquidacion"], "id");
        } else {
            $liqtras = false;
        }

        // ********************************************************************************* //
        // Localiza bandeja de digitalización
        // ********************************************************************************* //
        $bandejaDigitalizacion = '';
        $tt = retornarRegistroMysqliApi($mysqli, 'mreg_codrutas', "id='" . $arrTemCB["actoreparto"] . "'");
        if ($tt && !empty($tt)) {
            $bandejaDigitalizacion = $tt["bandeja"];
        }

        //
        \logApi::general2($nameLog, $codbarras, 'Notificaciones: Localizo recibo : ' . $reg["recibo"]);

        // ********************************************************************************* //    
        // Inicializa variables para el conrol del envío de las notificaciones
        // ********************************************************************************* //    
        $resultadoRecibo["nrec"] = $reg["recibo"];
        $resultadoRecibo["cba"] = $reg["codigobarras"];
        $resultadoRecibo["ope"] = $reg["operacion"];
        $resultadoRecibo["fec"] = $reg["fecha"];
        $resultadoRecibo["hor"] = $reg["hora"];
        $resultadoRecibo["fecarchivo"] = '';
        $resultadoRecibo["horarchivo"] = '';
        $resultadoRecibo["devuelto"] = '';
        $resultadoRecibo["desistido"] = '';
        $resultadoRecibo["ide"] = $arrTemCB["numid"];
        $resultadoRecibo["nom"] = $arrTemCB["nombre"];
        $resultadoRecibo["mat"] = array();
        $resultadoRecibo["pro"] = '';
        $resultadoRecibo["ser"] = $liq["tipotramite"];
        $resultadoRecibo["valor"] = $reg["valorneto"];
        $resultadoRecibo["tt"] = $reg["tipotramite"];
        $resultadoRecibo["emails"] = array();
        $resultadoRecibo["telefonos"] = array();

        // ******************************************************************************************** //    
        // Localiza hora del estado de enviado a archivo
        // ******************************************************************************************** // 
        $ests = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_documentos', "cosdigobarras='" . $codbarras . "'", "fecha,hora");
        if ($ests && !empty($ests)) {
            foreach ($ests as $est) {
                if ($est["estado"] == '05') {
                    $resultadoRecibo["devuelto"] = 'si';
                }
                if ($est["estado"] == '09') {
                    $resultadoRecibo["devuelto"] = '';
                }
                if ($est["estado"] == '39') {
                    $resultadoRecibo["desistido"] = 'si';
                }
                if ($est["estado"] == '15') {
                    $resultadoRecibo["fecarchivo"] = $est["fecha"];
                    $resultadoRecibo["horarchivo"] = $est["hora"];
                }
            }
        }
        if ($resultadoRecibo["fecarchivo"] == '') {
            \logApi::general2($nameLog, $codbarras, 'No se localizo fecha y hora del estado de archivado');
            return false;
        } else {
            if ($resultadoRecibo["devuelto"] == 'si' || $resultadoRecibo["desistido"] == 'si') {
                \logApi::general2($nameLog, $codbarras, 'Tramite devuelto o desistido');
                return false;
            }
        }

        // ******************************************************************************************** //    
        // Busca las matrículas y proponentes asociadas al trámite a traves de recibosgenerados_detalle
        // ******************************************************************************************** // 

        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_detalle', "recibo='" . $reg["recibo"] . "'", "secuencia");
        $j = 0;
        if ($arrTem && !empty($arrTem)) {
            foreach ($arrTem as $tx) {
                if ($tx["matricula"] != '' && substr($tx["matricula"], 0, 5) != 'NUEVA') {
                    $resultadoRecibo["mat"][$tx["matricula"]] = $tx["matricula"];
                }
                if ($tx["proponente"] != '') {
                    $resultadoRecibo["pro"] = $tx["proponente"];
                }
            }
        }

        //
        $txmats = '';
        if (!empty($resultadoRecibo["mat"])) {
            foreach ($resultadoRecibo["mat"] as $mtx) {
                if ($txmats != '') {
                    $txmats .= ', ';
                }
                $txmats .= $mtx;
            }
        }
        if ($txmats == '') {
            $txmats = 'Sin matriculas/inscripciones para notificar';
        }

        $txprops = '';
        if (!empty($resultadoRecibo["pro"])) {
            foreach ($resultadoRecibo["pro"] as $mtx) {
                if ($txprops != '') {
                    $txprops .= ', ';
                }
                $txprops .= $mtx;
            }
        }
        if ($txprops == '') {
            $txprops = 'Sin proponentes para informar';
        }

        \logApi::general2($nameLog, $codbarras, 'Informar: Localizo matricula(s) : ' . $txmats);
        \logApi::general2($nameLog, $codbarras, 'Informar: Localizo proponente(s) : ' . $txprops);

        // *********************************************************************************** //
        // Busca cada expediente
        // *********************************************************************************** //            
        if (!empty($resultadoRecibo["mat"])) {
            foreach ($resultadoRecibo["mat"] as $m) {
                $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $m . "'");

                // *********************************************************************************** //
                // Localiza emails y celulares actuales
                // *********************************************************************************** //                                    
                if ($exp && !empty($exp)) {
                    if (trim($exp["telcom1"]) != '' && strlen($exp["telcom1"]) == 10 && substr($exp["telcom1"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$exp["telcom1"]] = $exp["telcom1"];
                    }
                    if (trim($exp["telcom2"]) != '' && strlen($exp["telcom2"]) == 10 && substr($exp["telcom2"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$exp["telcom2"]] = $exp["telcom2"];
                    }
                    if (trim($exp["telcom3"]) != '' && strlen($exp["telcom3"]) == 10 && substr($exp["telcom3"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$exp["telcom3"]] = $exp["telcom3"];
                    }
                    if (trim($exp["telnot"]) != '' && strlen($exp["telnot"]) == 10 && substr($exp["telnot"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$exp["telnot"]] = $exp["telnot"];
                    }
                    if (trim($exp["telnot2"]) != '' && strlen($exp["telnot2"]) == 10 && substr($exp["telnot2"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$exp["telnot2"]] = $exp["telnot2"];
                    }
                    if (trim($exp["telnot3"]) != '' && strlen($exp["telnot3"]) == 10 && substr($exp["telnot3"], 0, 1) == '3') {
                        $resultadoRecibo["telefonos"][$exp["telnot3"]] = $exp["telnot3"];
                    }
                    if (trim($exp["emailcom"]) != '') {
                        $exp["emailcom"] = str_replace(".@", "@", $exp["emailcom"]);
                        $resultadoRecibo["emails"][$exp["emailcom"]] = $exp["emailcom"];
                    }
                    if (trim($exp["emailcom2"]) != '') {
                        $exp["emailcom2"] = str_replace(".@", "@", $exp["emailcom2"]);
                        $resultadoRecibo["emails"][$exp["emailcom2"]] = $exp["emailcom2"];
                    }
                    if (trim($exp["emailcom3"]) != '') {
                        $exp["emailcom3"] = str_replace(".@", "@", $exp["emailcom3"]);
                        $resultadoRecibo["emails"][$exp["emailcom3"]] = $exp["emailcom3"];
                    }
                    if (trim($exp["emailnot"]) != '') {
                        $exp["emailnot"] = str_replace(".@", "@", $exp["emailnot"]);
                        $resultadoRecibo["emails"][$exp["emailnot"]] = $exp["emailnot"];
                    }
                }
            }
        }

        // *********************************************************************************** //
        // Recupera números telefonicos y emails actuales - proponentes
        // *********************************************************************************** //
        if ($resultadoRecibo["pro"] != '') {
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_proponentes', "proponente='" . $resultadoRecibo["pro"] . "'");
            if ($exp && !empty($exp)) {
                if (trim($exp["telcom1"]) != '' && strlen($exp["telcom1"]) == 10 && substr($exp["telcom1"], 0, 1) == '3') {
                    $resultadoRecibo["telefonos"][$exp["telcom1"]] = $exp["telcom1"];
                }
                if (trim($exp["telcom2"]) != '' && strlen($exp["telcom2"]) == 10 && substr($exp["telcom2"], 0, 1) == '3') {
                    $resultadoRecibo["telefonos"][$exp["telcom2"]] = $exp["telcom2"];
                }
                if (trim($exp["celcom"]) != '' && strlen($exp["celcom"]) == 10 && substr($exp["celcom"], 0, 1) == '3') {
                    $resultadoRecibo["telefonos"][$exp["celcom"]] = $exp["celcom"];
                }
                if (trim($exp["telnot"]) != '' && strlen($exp["telnot"]) == 10 && substr($exp["telnot"], 0, 1) == '3') {
                    $resultadoRecibo["telefonos"][$exp["telnot"]] = $exp["telnot"];
                }
                if (trim($exp["telnot2"]) != '' && strlen($exp["telnot2"]) == 10 && substr($exp["telnot2"], 0, 1) == '3') {
                    $resultadoRecibo["telefonos"][$exp["telnot2"]] = $exp["telnot2"];
                }
                if (trim($exp["celnot"]) != '' && strlen($exp["celnot"]) == 10 && substr($exp["celnot"], 0, 1) == '3') {
                    $resultadoRecibo["telefonos"][$exp["celnot"]] = $exp["celnot"];
                }
                if (trim($exp["emailcom"]) != '') {
                    $exp["emailcom"] = str_replace(".@", "@", $exp["emailcom"]);
                    $resultadoRecibo["emails"][$exp["emailcom"]] = $exp["emailcom"];
                }
                if (trim($exp["emailnot"]) != '') {
                    $exp["emailnot"] = str_replace(".@", "@", $exp["emailnot"]);
                    $resultadoRecibo["emails"][$exp["emailnot"]] = $exp["emailnot"];
                }
            }
        }


        // unset($reg);
        // *********************************************************************************** //
        // Log de cantidad de emails y celulares
        // *********************************************************************************** //
        \logApi::general2($nameLog, $codbarras, 'Informar: Total emails vigentes : ' . count($resultadoRecibo["emails"]));
        \logApi::general2($nameLog, $codbarras, 'Informar: Total celulares vigentes : ' . count($resultadoRecibo["telefonos"]));

        //
        $arrCampos = array(
            'recibo',
            'codigobarras',
            'idliquidacion',
            'tipo',
            'contenido'
        );
        $arrValores = array();
        if (!empty($resultadoRecibo["emails"])) {
            if (INFOMAR_ARCHIVO == 'SI-EMAIL' || INFOMAR_ARCHIVO == 'SI-AMBOS') {
                foreach ($resultadoRecibo["emails"] as $e) {
                    $arrValores[] = array(
                        "'" . $reg["recibo"] . "'",
                        "'" . $reg["codigobarras"] . "'",
                        $reg["idliquidacion"],
                        "'email-archivo'",
                        "'" . $e . "'"
                    );
                }
            }
        }
        if (!empty($resultadoRecibo["telefonos"])) {
            if (INFOMAR_ARCHIVO == 'SI-SMS' || INFOMAR_ARCHIVO == 'SI-AMBOS') {
                foreach ($resultadoRecibo["telefonos"] as $e) {
                    $arrValores[] = array(
                        "'" . $reg["recibo"] . "'",
                        "'" . $reg["codigobarras"] . "'",
                        $reg["idliquidacion"],
                        "'telefono-archivo'",
                        "'" . $e . "'"
                    );
                }
            }
        }

        // *********************************************************************************** //
        // Almacena tabla donde guarda que emails y celulares será notificados por recibo
        // *********************************************************************************** //
        if (!empty($arrValores)) {
            insertarRegistrosBloqueMysqliApi($mysqli, 'mreg_recibos_sipref_destinos', $arrCampos, $arrValores);
            \logApi::general2($nameLog, $codbarras, 'Inserto mreg_recibos_sipref_destinos');
        }

        // *********************************************************************************** //
        // Envía emails de notificaicón
        // *********************************************************************************** //
        if (count($resultadoRecibo["emails"]) != 0) {
            if (INFOMAR_ARCHIVO == 'SI-EMAIL' || INFOMAR_ARCHIVO == 'SI-AMBOS') {
                $msg = '';
                $msg .= 'LA ' . RAZONSOCIAL . ' le informa que el dia ' . \funcionesGenerales::mostrarFecha($resultadoRecibo["fec"]) . ' a las ' . \funcionesGenerales::mostrarHora($resultadoRecibo["hor"]) . ' ';
                $msg .= 'fue culminado el proceso de registro de una transaccion en los registros publicos que ';
                $msg .= 'administra y maneja nuestra entidad. Los datos del tramite culminado son los siguientes:<br><br>';
                $msg .= 'Recibo de Caja No. ' . $resultadoRecibo["nrec"] . '<br>';
                $msg .= 'Numero operacion: ' . $resultadoRecibo["ope"] . '<br>';
                $msg .= 'Identificacion(es): ' . $resultadoRecibo["ide"] . '<br>';
                $msg .= 'Nombre(s): ' . $resultadoRecibo["nom"] . '<br>';
                if (ltrim($resultadoRecibo["cba"], "0") != '') {
                    $msg .= 'Codigo de barras: ' . $resultadoRecibo["cba"] . '<br>';
                }
                $mats = '';
                if (!empty($resultadoRecibo["mat"])) {
                    foreach ($resultadoRecibo["mat"] as $mx1) {
                        if ($mats != '') {
                            $mats .= ', ';
                        }
                        $mats .= $mx1;
                    }
                }
                if ($mats != '') {
                    $msg .= 'Matriculas/Inscripciones: ' . $mats . '<br>';
                }
                if (ltrim($resultadoRecibo["pro"], "0") != '') {
                    $msg .= 'Proponente: ' . $resultadoRecibo["pro"] . '<br>';
                }
                $msg .= 'Tramite: ' . $resultadoRecibo["ser"] . '<br>';
                foreach ($resultadoRecibo["listaemails"] as $emx) {
                    $msg .= 'Email : ' . $emx . '<br>';
                }
                $msg .= '<br>';
                $msg .= 'Valor de la transaccion: ' . $resultadoRecibo["valor"] . '<br><br>';

                //
                $msg .= 'Este mensaje se envia en forma automatica por el Sistema de Registro de LA ' . RAZONSOCIAL . ' ';
                $msg .= 'de manera informtiva.';
                $msg .= '<br><br>';
                $msg .= 'Correo desatendido: Por favor no responda a la direccion de correo electronico que envia este mensaje, dicha cuenta ';
                $msg .= 'no es revisada por ningun funcionario de nuestra entidad. Este mensaje es informativo.';
                $msg .= '<br><br>';
                $msg .= 'Los acentos y tildes de este correo han sido omitidos intencionalmente con el objeto de evitar inconvenientes en la lectura del mismo.';

                // ***************************************************************************************** //
                // En caso de renovación que se asienta automáticamente
                // ***************************************************************************************** //
                foreach ($resultadoRecibo["emails"] as $emx) {
                    if (trim($emx) != '') {
                        \logApi::general2($nameLog, $codbarras, 'Enviara correo a ' . $emx);
                        $emx1 = $emx;
                        if (TIPO_AMBIENTE == 'PRUEBAS') {
                            if (defined('EMAIL_NOTIFICACION_PRUEBAS') && EMAIL_NOTIFICACION_PRUEBAS != '') {
                                $emx1 = EMAIL_NOTIFICACION_PRUEBAS;
                            } else {
                                $emx1 = 'jnieto@confecamaras.org.co';
                            }
                        }
                        if (\funcionesGenerales::validarEmail($emx1) === true) {
                            $rEmail = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx1, 'Correo informativo de culminación de trámite No. ' . $resultadoRecibo["nrec"] . ' en  LA ' . RAZONSOCIAL, $msg);
                            if ($rEmail === false) {
                                if (empty($resultadoRecibo["mat"])) {
                                    \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '41', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], '', $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR : ' . $_SESSION["generales"]["mensajeerror"], $bandejaDigitalizacion);
                                    \logApi::general2($nameLog, $codbarras, 'Error enviando notificacion (41) a ' . $emx1);
                                } else {
                                    foreach ($resultadoRecibo["mat"] as $mt1) {
                                        \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '41', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], $mt1, $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', '** ERROR : ' . $_SESSION["generales"]["mensajeerror"], $bandejaDigitalizacion);
                                        \logApi::general2($nameLog, $codbarras, 'Error enviando notificacion (41) a ' . $emx1);
                                    }
                                }
                            } else {
                                if (empty($resultadoRecibo["mat"])) {
                                    \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '41', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], '', $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '**OK **', $bandejaDigitalizacion);
                                    \logApi::general2($nameLog, $codbarras, 'Envio notificacion (41) a ' . $emx1);
                                } else {
                                    foreach ($resultadoRecibo["mat"] as $mt1) {
                                        \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($mysqli, '41', $resultadoRecibo["cba"], '', $resultadoRecibo["ope"], $resultadoRecibo["nrec"], '', '', '', '', $resultadoRecibo["ide"], $mt1, $resultadoRecibo["pro"], $resultadoRecibo["nom"], $emx, $msg, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '**OK **', $bandejaDigitalizacion);
                                        \logApi::general2($nameLog, $codbarras, 'Envio notificacion (41) a ' . $emx1);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // ***************************************************************************** //
        // Enviar a SMS
        // ***************************************************************************** //
        if (count($resultadoRecibo["telefonos"]) != 0) {
            if (INFOMAR_ARCHIVO == 'SI-SMS' || INFOMAR_ARCHIVO == 'SI-AMBOS') {
                $mt1 = '';
                if (!empty($resultadoRecibo["mat"])) {
                    foreach ($resultadoRecibo["mat"] as $mx) {
                        $mt1 = $mx;
                        if (defined('RAZONSOCIALSMS') && trim(RAZONSOCIALSMS) != '') {
                            $txtSms = 'La ' . RAZONSOCIALSMS . ' informa culminacion tramite expediente ' . $mt1 . ', al correo electronico se envio informacion para verificar procedencia.';
                        } else {
                            $txtSms = 'La ' . RAZONSOCIAL . ' informa culminacion tramite expediente ' . $mt1 . ', al correo electronico se envio informacion para verificar procedencia.';
                        }
//
                        if ($tt["tipo"] == 'PQ') {
                            if (defined('RAZONSOCIALSMS') && trim(RAZONSOCIALSMS) != '') {
                                $txtSms = 'La ' . RAZONSOCIALSMS . ' informa que el ' . \funcionesGenerales::mostrarFecha($resultadoRecibo["fec"]) . ' a las ' . \funcionesGenerales::mostrarHora($resultadoRecibo["hor"]) . ' se radico PQR expediente ' . $mt1;
                            } else {
                                $txtSms = 'La ' . RAZONSOCIAL . ' informa que el ' . \funcionesGenerales::mostrarFecha($resultadoRecibo["fec"]) . ' a las ' . \funcionesGenerales::mostrarHora($resultadoRecibo["hor"]) . ' se radico PQR expediente ' . $mt1;
                            }
                        }

                        foreach ($resultadoRecibo["telefonos"] as $t) {
                            \funcionesRegistrales::actualizarPilaSms($mysqli, '', $t, '91', $resultadoRecibo["nrec"], $resultadoRecibo["cba"], '', '', $mt1, $mt1, '', $resultadoRecibo["ide"], $resultadoRecibo["nom"], $txtSms, '', $bandejaDigitalizacion);
                        }
                    }
                } else {
                    if ($resultadoRecibo["pro"] != '') {
                        if (defined('RAZONSOCIALSMS') && trim(RAZONSOCIALSMS) != '') {
                            $txtSms = 'La ' . RAZONSOCIALSMS . ' informa culminacion tramite expediente ' . $resultadoRecibo["pro"] . ', al correo electronico se envio informacion para verificar procedencia.';
                        } else {
                            $txtSms = 'La ' . RAZONSOCIAL . ' informa culminacion tramite expediente ' . $resultadoRecibo["pro"] . ', al correo electronico se envio informacion para verificar procedencia.';
                        }
//
                        if ($tt["tipo"] == 'PQ') {
                            if (defined('RAZONSOCIALSMS') && trim(RAZONSOCIALSMS) != '') {
                                $txtSms = 'La ' . RAZONSOCIALSMS . ' informa que el ' . \funcionesGenerales::mostrarFecha($resultadoRecibo["fec"]) . ' a las ' . \funcionesGenerales::mostrarHora($resultadoRecibo["hor"]) . ' se radico PQR expediente ' . $resultadoRecibo["pro"];
                            } else {
                                $txtSms = 'La ' . RAZONSOCIAL . ' informa que el ' . \funcionesGenerales::mostrarFecha($resultadoRecibo["fec"]) . ' a las ' . \funcionesGenerales::mostrarHora($resultadoRecibo["hor"]) . ' se radico PQR expediente ' . $resultadoRecibo["pro"];
                            }
                        }
                        foreach ($resultadoRecibo["telefonos"] as $t) {
                            \funcionesRegistrales::actualizarPilaSms($mysqli, '', $t, '91', $resultadoRecibo["nrec"], $resultadoRecibo["cba"], '', '', '', '', $resultadoRecibo["pro"], $resultadoRecibo["ide"], $resultadoRecibo["nom"], $txtSms, '', $bandejaDigitalizacion);
                        }
                    } else {
                        if (defined('RAZONSOCIALSMS') && trim(RAZONSOCIALSMS) != '') {
                            $txtSms = 'La ' . RAZONSOCIALSMS . ' informa culminacion tramite, al correo electronico se envio informacion para verificar procedencia.';
                        } else {
                            $txtSms = 'La ' . RAZONSOCIAL . ' informa culminacion tramite, al correo electronico se envio informacion para verificar procedencia.';
                        }
//
                        if ($tt["tipo"] == 'PQ') {
                            if (defined('RAZONSOCIALSMS') && trim(RAZONSOCIALSMS) != '') {
                                $txtSms = 'La ' . RAZONSOCIALSMS . ' informa que el ' . \funcionesGenerales::mostrarFecha($resultadoRecibo["fec"]) . ' a las ' . \funcionesGenerales::mostrarHora($resultadoRecibo["hor"]) . ' se radico PQR';
                            } else {
                                $txtSms = 'La ' . RAZONSOCIAL . ' informa que el ' . \funcionesGenerales::mostrarFecha($resultadoRecibo["fec"]) . ' a las ' . \funcionesGenerales::mostrarHora($resultadoRecibo["hor"]) . ' se radico PQR';
                            }
                        }

                        foreach ($resultadoRecibo["telefonos"] as $t) {
                            \funcionesRegistrales::actualizarPilaSms($mysqli, '', $t, '91', $resultadoRecibo["nrec"], $resultadoRecibo["cba"], '', '', '', '', '', $resultadoRecibo["ide"], $resultadoRecibo["nom"], $txtSms, '', $bandejaDigitalizacion);
                        }
                    }
                }
            }
        }
    }

}

?>
