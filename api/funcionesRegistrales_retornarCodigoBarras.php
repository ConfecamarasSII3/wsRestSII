<?php

class funcionesRegistrales_retornarCodigoBarras {

    /**
     * 
     * @param type $mysqli
     * @param type $codbarras
     * @return bool|string
     */
    public static function retornarCodigoBarras($mysqli, $codbarras) {
        $retorno = array();
        $arrTem = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . ltrim(trim($codbarras), "0") . "'");
        if ($arrTem === false || empty($arrTem)) {
            return false; // Código de barras no localizado
        }
        $arrTem["nombrebase64"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', "codigobarras='" . ltrim(trim($codbarras), "0") . "' and campo='nombrebase64'", "contenido");
        if ($arrTem["nombrebase64"] != '') {
            $arrTem["nombre"] = base64_decode($arrTem["nombrebase64"]);
        }

        //
        $retorno["codbarras"] = $arrTem["codigobarras"];
        $retorno["codigobarras"] = $arrTem["codigobarras"];
        $retorno["usuario"] = $arrTem["operadorfinal"];
        $retorno["operadorfinal"] = $arrTem["operadorfinal"];
        $retorno["operacion"] = $arrTem["operacion"];
        $retorno["matricula"] = $arrTem["matricula"];
        $retorno["proponente"] = $arrTem["proponente"];
        $retorno["idclase"] = $arrTem["idclase"];
        $retorno["numid"] = $arrTem["numid"];
        $retorno["nombre"] = $arrTem["nombre"];
        $retorno["fecha"] = $arrTem["fecharadicacion"];
        $retorno["fecharad"] = $arrTem["fecharadicacion"];
        $retorno["fecharadicacion"] = $arrTem["fecharadicacion"];
        $retorno["tramite"] = $arrTem["actoreparto"];
        $retorno["actoreparto"] = $arrTem["actoreparto"];
        $retorno["estado"] = $arrTem["estadofinal"];
        $retorno["estadofinal"] = $arrTem["estadofinal"];
        $retorno["fechaestadofinal"] = $arrTem["fechaestadofinal"];
        $retorno["horaestadofinal"] = $arrTem["horaestadofinal"];
        $retorno["verificacionsoportes"] = $arrTem["verificacionsoportes"];
        $retorno["reliquidacion"] = $arrTem["reliquidacion"];
        $retorno["asentamientoparcial"] = $arrTem["asentamientoparcial"];
        $retorno["sucursalradicacion"] = $arrTem["sucursalradicacion"];
        $retorno["matriculasasociadas"] = array();
        $retorno["fecharenovacionrecibo"] = '';
        $retorno["fecharenovacionaplicablerecibo"] = '';
        $retorno["organizacion"] = '';
        $retorno["categoria"] = '';

        $retorno["tipodoc"] = $arrTem["tipdoc"];
        $retorno["tipdoc"] = $arrTem["tipdoc"];
        $retorno["numdoc"] = $arrTem["numdoc"];
        $retorno["fechadoc"] = $arrTem["fecdoc"];
        $retorno["fecdoc"] = $arrTem["fecdoc"];
        $retorno["idoridoc"] = $arrTem["idoridoc"];
        $retorno["txtorigendoc"] = $arrTem["oridoc"];
        $retorno["oridoc"] = $arrTem["oridoc"];
        $retorno["mundoc"] = $arrTem["mundoc"];

        $retorno["tipotramite"] = '';
        $retorno["subtipotramite"] = '';
        $retorno["idliquidacion"] = 0;
        $retorno["numerorecuperacion"] = '';
        $retorno["recibo"] = $arrTem["recibo"];
        $retorno["estadoproponente"] = '';
        $retorno["emailradicado"] = '';
        $retorno["celradicado"] = '';
        $retorno["nin"] = '';
        $retorno["nuc"] = '';
        $retorno["telefonos"] = array();
        $retorno["emails"] = array();
        $retorno["codigoservicio"] = '';
        $retorno["sellos"] = array();
        $retorno["servicios"] = array();
        $retorno["pasos"] = array();
        $retorno["arrpasos"] = array();
        $retorno["datliq"] = array();
        $retorno["fechadevolucion"] = '';
        $retorno["numerodevolucion"] = '';
        $retorno["emailnot1"] = $arrTem["emailnot1"];
        $retorno["emailnot2"] = $arrTem["emailnot2"];
        $retorno["emailnot3"] = $arrTem["emailnot3"];
        $retorno["celnot1"] = $arrTem["celnot1"];
        $retorno["celnot2"] = $arrTem["celnot2"];
        $retorno["celnot3"] = $arrTem["celnot3"];

        $retorno["idclaserecurrente"] = $arrTem["idclaserecurrente"];
        $retorno["numidrecurrente"] = $arrTem["numidrecurrente"];
        $retorno["nombrerecurrente"] = $arrTem["nombrerecurrente"];

        // desde campos
        $retorno["fregistro"] = '';
        $retorno["soloapelacion"] = '';
        $retorno["idclaserecurrente2"] = '';
        $retorno["numidrecurrente2"] = '';
        $retorno["nombrerecurrente2"] = '';
        $retorno["idclaserecurrente3"] = '';
        $retorno["numidrecurrente3"] = '';
        $retorno["nombrerecurrente3"] = '';
        $retorno["libroafectado2"] = '';
        $retorno["libroafectado3"] = '';
        $retorno["libroafectado4"] = '';
        $retorno["registroafectado2"] = '';
        $retorno["registroafectado3"] = '';
        $retorno["registroafectado4"] = '';
        $retorno["dupliafectado2"] = '';
        $retorno["dupliafectado3"] = '';
        $retorno["dupliafectado4"] = '';

        //
        $retorno["bandeja"] = retornarRegistroMysqliApi($mysqli, 'mreg_codrutas', "id='" . $arrTem["actoreparto"] . "'", "bandeja");
        if (trim($retorno["bandeja"]) == '') {
            $retorno["bandeja"] = '4.-REGMER';
        }

//
        $retorno["datliq"] = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "numeroradicacion='" . ltrim(trim($codbarras), "0") . "'");
        if ($retorno["datliq"] === false || empty($retorno["datliq"])) {
            $retorno["datliq"] = array();
        } else {
            $retorno["tipotramite"] = $retorno["datliq"]["tipotramite"];
            $retorno["idliquidacion"] = $retorno["datliq"]["idliquidacion"];
            $retorno["numerorecuperacion"] = $retorno["datliq"]["numerorecuperacion"];
        }

        //
        if ($retorno["idliquidacion"] != '') {
            $retorno["subtipotramite"] = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_campos', "idliquidacion=" . $retorno["idliquidacion"] . " and campo='subtipotramite'", "contenido");
        } else {
            $retorno["subtipotramite"] = '';
        }

        //
        $retorno["actoreparto"] = $arrTem["actoreparto"];
        $retorno["tiporuta"] = retornarRegistroMysqliApi($mysqli, 'mreg_codrutas', "id='" . $arrTem["actoreparto"] . "'", "tipo");

        //
        $esEmbargo = 'no';
        if (($arrTem["actoreparto"] == '07') || ($arrTem["actoreparto"] == '29')) {
            $esEmbargo = 'si';
        }
        $retorno["esembargo"] = $esEmbargo;

        //
        if ($arrTem["recibo"] == '') {
            $mysqli->close();
            return false; // Cödigo de barras sin recibo de caja
        }

        // Recupera números telefonicos y emails actuales
        if ($retorno["proponente"] != '') {
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_proponentes', "proponente='" . $retorno["proponente"] . "'");
            if ($exp && !empty($exp)) {
                if (trim($exp["telcom1"])) {
                    if (strlen($exp["telcom1"]) == 10 && substr($exp["telcom1"], 0, 1) == '3') {
                        $retorno["telefonos"][$exp["telcom1"]] = $exp["telcom1"];
                    }
                }
                if (trim($exp["telcom2"])) {
                    if (strlen($exp["telcom2"]) == 10 && substr($exp["telcom2"], 0, 1) == '3') {
                        $retorno["telefonos"][$exp["telcom2"]] = $exp["telcom2"];
                    }
                }
                if (trim($exp["celcom"])) {
                    if (strlen($exp["celcom"]) == 10 && substr($exp["celcom"], 0, 1) == '3') {
                        $retorno["telefonos"][$exp["celcom"]] = $exp["celcom"];
                    }
                }
                if (trim($exp["telnot"])) {
                    if (strlen($exp["telnot"]) == 10 && substr($exp["telnot"], 0, 1) == '3') {
                        $retorno["telefonos"][$exp["telnot"]] = $exp["telnot"];
                    }
                }
                if (trim($exp["telnot2"])) {
                    if (strlen($exp["telnot2"]) == 10 && substr($exp["telnot2"], 0, 1) == '3') {
                        $retorno["telefonos"][$exp["telnot2"]] = $exp["telnot2"];
                    }
                }
                if (trim($exp["celnot"])) {
                    if (strlen($exp["celnot"]) == 10 && substr($exp["celnot"], 0, 1) == '3') {
                        $retorno["telefonos"][$exp["celnot"]] = $exp["celnot"];
                    }
                }
                if (trim($exp["emailcom"])) {
                    $retorno["emails"][$exp["emailcom"]] = $exp["emailcom"];
                }
                if (trim($exp["emailnot"])) {
                    $retorno["emails"][$exp["emailnot"]] = $exp["emailnot"];
                }
            }
        }

        if ($retorno["matricula"] != '') {
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $retorno["matricula"] . "'");
            if ($exp && !empty($exp)) {
                if (trim($exp["telcom1"])) {
                    if (strlen($exp["telcom1"]) == 10 && substr($exp["telcom1"], 0, 1) == '3') {
                        $retorno["telefonos"][$exp["telcom1"]] = $exp["telcom1"];
                    }
                }
                if (trim($exp["telcom2"])) {
                    if (strlen($exp["telcom2"]) == 10 && substr($exp["telcom2"], 0, 1) == '3') {
                        $retorno["telefonos"][$exp["telcom2"]] = $exp["telcom2"];
                    }
                }
                if (trim($exp["telcom3"])) {
                    if (strlen($exp["telcom3"]) == 10 && substr($exp["telcom3"], 0, 1) == '3') {
                        $retorno["telefonos"][$exp["telcom3"]] = $exp["telcom3"];
                    }
                }
                if (trim($exp["telnot"])) {
                    if (strlen($exp["telnot"]) == 10 && substr($exp["telnot"], 0, 1) == '3') {
                        $retorno["telefonos"][$exp["telnot"]] = $exp["telnot"];
                    }
                }
                if (trim($exp["telnot2"])) {
                    if (strlen($exp["telnot2"]) == 10 && substr($exp["telnot2"], 0, 1) == '3') {
                        $retorno["telefonos"][$exp["telnot2"]] = $exp["telnot2"];
                    }
                }
                if (trim($exp["telnot3"])) {
                    if (strlen($exp["telnot3"]) == 10 && substr($exp["telnot3"], 0, 1) == '3') {
                        $retorno["telefonos"][$exp["telnot3"]] = $exp["telnot3"];
                    }
                }
                if (trim($exp["emailcom"])) {
                    $retorno["emails"][$exp["emailcom"]] = $exp["emailcom"];
                }
                if (trim($exp["emailcom2"])) {
                    $retorno["emails"][$exp["emailcom2"]] = $exp["emailcom2"];
                }
                if (trim($exp["emailcom3"])) {
                    $retorno["emails"][$exp["emailcom3"]] = $exp["emailcom3"];
                }
                if (trim($exp["emailnot"])) {
                    $retorno["emails"][$exp["emailnot"]] = $exp["emailnot"];
                }
            }
        }



        // recupera números y emails anteriores
        // desde campos tablas armadas en el sii
        if ($retorno["matricula"] != '') {
            $iano = date("Y");
            for ($ianox = 2016; $ianox <= $iano; $ianox++) {
                $exps = retornarRegistrosMysqliApi($mysqli, 'mreg_campos_historicos_' . $ianox, "matricula='" . $retorno["matricula"] . "' and campo IN ('telcom1','telcom2','telcom3','emailcom','telnot','telnot2','telnot3','emailnot')", "id");
                if ($exps && !empty($exps)) {
                    foreach ($exps as $exp) {
                        if (!isset($exp["inactivadosipref"]) || strtolower($exp["inactivadosipref"]) != 'si') {
                            if ($exp["campo"] == 'telcom1') {
                                if (strlen(trim($exp["datoanterior"])) == 10 && substr(trim($exp["datoanterior"]), 0, 1) == '3') {
                                    $retorno["telefonos"][trim($exp["datoanterior"])] = trim($exp["datoanterior"]);
                                }
                            }
                            if ($exp["campo"] == 'telcom2') {
                                if (strlen(trim($exp["datoanterior"])) == 10 && substr(trim($exp["datoanterior"]), 0, 1) == '3') {
                                    $retorno["telefonos"][trim($exp["datoanterior"])] = trim($exp["datoanterior"]);
                                }
                            }
                            if ($exp["campo"] == 'telcom3') {
                                if (strlen(trim($exp["datoanterior"])) == 10 && substr(trim($exp["datoanterior"]), 0, 1) == '3') {
                                    $retorno["telefonos"][trim($exp["datoanterior"])] = trim($exp["datoanterior"]);
                                }
                            }
                            if ($exp["campo"] == 'telnot') {
                                if (strlen(trim($exp["datoanterior"])) == 10 && substr(trim($exp["datoanterior"]), 0, 1) == '3') {
                                    $retorno["telefonos"][trim($exp["datoanterior"])] = trim($exp["datoanterior"]);
                                }
                            }
                            if ($exp["campo"] == 'telnot2') {
                                if (strlen(trim($exp["datoanterior"])) == 10 && substr(trim($exp["datoanterior"]), 0, 1) == '3') {
                                    $retorno["telefonos"][trim($exp["datoanterior"])] = trim($exp["datoanterior"]);
                                }
                            }
                            if ($exp["campo"] == 'telnot3') {
                                if (strlen(trim($exp["datoanterior"])) == 10 && substr(trim($exp["datoanterior"]), 0, 1) == '3') {
                                    $retorno["telefonos"][trim($exp["datoanterior"])] = trim($exp["datoanterior"]);
                                }
                            }
                            if ($exp["campo"] == 'emailcom') {
                                if (strlen(trim($exp["datoanterior"])) == 10 && substr(trim($exp["datoanterior"]), 0, 1) == '3') {
                                    $retorno["emails"][trim($exp["datoanterior"])] = trim($exp["datoanterior"]);
                                }
                            }
                            if ($exp["campo"] == 'emailcom2') {
                                if (strlen(trim($exp["datoanterior"])) == 10 && substr(trim($exp["datoanterior"]), 0, 1) == '3') {
                                    $retorno["emails"][trim($exp["datoanterior"])] = trim($exp["datoanterior"]);
                                }
                            }
                            if ($exp["campo"] == 'emailcom3') {
                                if (strlen(trim($exp["datoanterior"])) == 10 && substr(trim($exp["datoanterior"]), 0, 1) == '3') {
                                    $retorno["emails"][trim($exp["datoanterior"])] = trim($exp["datoanterior"]);
                                }
                            }
                            if ($exp["campo"] == 'emailnot') {
                                if (strlen(trim($exp["datoanterior"])) == 10 && substr(trim($exp["datoanterior"]), 0, 1) == '3') {
                                    $retorno["emails"][trim($exp["datoanterior"])] = trim($exp["datoanterior"]);
                                }
                            }
                        }
                    }
                }
            }
        }


        //
        $j = 0;
        if ($arrTem["recibo"] != '') {
            $arrTem1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "numerorecibo='" . $arrTem["recibo"] . "'", "id");
            if ($arrTem1 && !empty($arrTem1)) {
                foreach ($arrTem1 as $tx) {
                    $j++;
                    if ($j == 1) {
                        $retorno["codigoservicio"] = $tx["servicio"];
                    }
                    $retorno["servicios"][$j] = array();
                    $retorno["servicios"][$j]["codservicio"] = $tx["servicio"];
                    $retorno["servicios"][$j]["vrservicio"] = $tx["valor"];
                    $retorno["servicios"][$j]["canservicio"] = $tx["cantidad"];
                    $retorno["servicios"][$j]["matservicio"] = $tx["matricula"];
                    $retorno["servicios"][$j]["anoservicio"] = $tx["anorenovacion"];
                    $retorno["servicios"][$j]["actservicio"] = $tx["activos"];
                    $retorno["fecharenovacionrecibo"] = $tx["fecoperacion"];
                    $retorno["fecharenovacionaplicablerecibo"] = $tx["fecharenovacionaplicable"];
                    $retorno["fecopera"] = $tx["fecoperacion"];
                }
            }
        }

        // 2020-04-03: JINT - Para envío de devoluciones y desistimientos
        if ($arrTem["recibo"] != '') {
            $arrRecGen = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', "recibo='" . $arrTem["recibo"] . "'");
            $retorno["emailradicado"] = $arrRecGen["email"];
            $retorno["celradicado"] = '';
            if ($arrRecGen["telefono1"] != '' && strlen($arrRecGen["telefono1"]) == 10 && substr($arrRecGen["telefono1"], 0, 1) == '3') {
                $retorno["celradicado"] = $arrRecGen["telefono1"];
            } else {
                if ($arrRecGen["telefono2"] != '' && strlen($arrRecGen["telefono2"]) == 10 && substr($arrRecGen["telefono2"], 0, 1) == '3') {
                    $retorno["celradicado"] = $arrRecGen["telefono2"];
                }
            }
        }

        //
        if ($retorno["proponente"] != '') {
            $arrTem2 = retornarRegistroMysqliApi($mysqli, 'mreg_est_proponentes', "proponente='" . $retorno["proponente"] . "'");
            if ($arrTem2 && !empty($arrTem2)) {
                $retorno["estadoproponente"] = $arrTem2["idestadoproponente"];
            }
        }

        //
        $arrTem3 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "idradicacion='" . ltrim($codbarras, "0") . "'", "id");
        if ($arrTem3 && !empty($arrTem3)) {
            $j = 0;
            foreach ($arrTem3 as $t) {
                $j++;
                $retorno["sellos"][$j]["idlibro"] = substr($t["libro"], 2, 2);
                $retorno["sellos"][$j]["numregistro"] = $t["registro"];
                $retorno["sellos"][$j]["acto"] = $t["acto"];
                $retorno["sellos"][$j]["desacto"] = retornarNombreActosRegistroMysqliApi($mysqli, $t["libro"], $t["acto"]);
                $retorno["sellos"][$j]["fecregistro"] = $t["fecharegistro"];
                $retorno["sellos"][$j]["matregistro"] = $t["matricula"];
                $retorno["sellos"][$j]["noticia"] = $t["noticia"];
                $retorno["sellos"][$j]["usuarioreg"] = $t["usuarioinscribe"];
            }
        }

        // Lista de pasos

        $arrTem4 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_documentos', "codigobarras='" . ltrim($codbarras, "0") . "'", "fecha,hora");
        if ($arrTem4 && !empty($arrTem4)) {
            $j = 0;
            foreach ($arrTem4 as $t) {
                $j++;
                $retorno["pasos"][$j] = array();
                $retorno["pasos"][$j]["fecha"] = $t["fecha"];
                $retorno["pasos"][$j]["hora"] = $t["hora"];
                $retorno["pasos"][$j]["estado"] = $t["estado"];
                $retorno["pasos"][$j]["operador"] = $t["operador"];
                $retorno["pasos"][$j]["sucursal"] = $t["sucursal"];

                $retorno["arrpasos"][$j] = array();
                $retorno["arrpasos"][$j]["fecha"] = $t["fecha"];
                $retorno["arrpasos"][$j]["hora"] = $t["hora"];
                $retorno["arrpasos"][$j]["cod"] = $t["estado"];
                $retorno["arrpasos"][$j]["estado"] = retornarRegistroMysqliApi($mysqli, 'mreg_codestados_rutamercantil', "id='" . $t["estado"] . "'", "descripcion");
                $retorno["arrpasos"][$j]["operador"] = $t["operador"];
                $retorno["arrpasos"][$j]["nombre"] = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $t["operador"] . "'", "nombreusuario");
            }
        }

        // Números rues
        $rRues = retornarRegistroMysqliApi($mysqli, 'mreg_rue_radicacion', "codigobarras='" . ltrim($codbarras, "0") . "'");
        if ($rRues && !empty($rRues)) {
            $retorno["nin"] = $rRues["numerointernorue"];
            $retorno["nuc"] = $rRues["numerounicoconsulta"];
        }

        // Número y fecha de la última devolución
        $devs = retornarRegistrosMysqliApi($mysqli, 'mreg_devoluciones_nueva', "idradicacion='" . ltrim($codbarras, "0") . "'", "iddevolucion");
        if ($devs && !empty($devs)) {
            foreach ($devs as $devs1) {
                if ($devs1["estado"] == '2') {
                    $retorno["fechadevolucion"] = $devs1["fechadevolucion"];
                    $retorno["numerodevolucion"] = $devs1["numdoc"];
                }
            }
        }

        // Encuentra matrículas asociadas
        if ($arrTem["recibo"] != '') {
            $arrTemEstRec = retornarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_detalle', "recibo='" . $arrTem["recibo"] . "'", "idservicio,matricula,ano");
        } else {
            $arrTemEstRec = array ();
        }
        $arrTemInscrip = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "idradicacion='" . $codbarras . "'", "id");
        if (
                $arrTem["actoreparto"] != '09' && // proponentes
                $arrTem["actoreparto"] != '27' && // recursos regpro
                $arrTem["actoreparto"] != '33' && // correcciones
                $arrTem["actoreparto"] != '53' // rue - regpro
        ) {
            if (trim($arrTem["matricula"]) != '') {
                $retorno["matriculasasociadas"][$arrTem["matricula"]] = array(
                    'matricula' => ltrim($arrTem["matricula"], "0"),
                    'ano' => ''
                );
            }
            if (!empty($arrTemEstRec)) {
                foreach ($arrTemEstRec as $x1) {
                    if (trim((string) $x1["cc"]) == '' || trim((string) $x1["cc"]) == CODIGO_EMPRESA) {
                        if (trim($x1["matricula"]) != '') {
                            $retorno["matriculasasociadas"][$x1["matricula"]] = array(
                                'matricula' => ltrim($x1["matricula"], "0"),
                                'ano' => $x1["ano"]
                            );
                        }
                    }
                }
            }
            if (!empty($arrTemInscrip)) {
                foreach ($arrTemInscrip as $x1) {
                    if (trim((string) $x1["matricula"]) != '') {
                        $retorno["matriculasasociadas"][$x1["matricula"]] = array(
                            'matricula' => $x1["matricula"],
                            'ano' => ''
                        );
                    }
                }
            }
        }

        // Campos adicionales del código de barras
        $arrCmps = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_campos', "codigobarras='" . $codbarras . "'", "id");
        if ($arrCmps && !empty($arrCmps)) {
            foreach ($arrCmps as $cmp) {
                if ($cmp["contenido"] != '') {
                    $ya = 'no';
                    if ($cmp["campo"] == 'inscripcionafectada2') {
                        list ($retorno["libroafectado2"], $retorno["registroafectado2"], $retorno["dupliafectado2"]) = explode("-", $cmp["contenido"]);
                        $ya = 'si';
                    }
                    if ($cmp["campo"] == 'inscripcionafectada3') {
                        list ($retorno["libroafectado3"], $retorno["registroafectado3"], $retorno["dupliafectado3"]) = explode("-", $cmp["contenido"]);
                        $ya = 'si';
                    }
                    if ($cmp["campo"] == 'inscripcionafectada4') {
                        list ($retorno["libroafectado4"], $retorno["registroafectado4"], $retorno["dupliafectado4"]) = explode("-", $cmp["contenido"]);
                        $ya = 'si';
                    }
                    if ($ya == 'no') {
                        $retorno[$cmp["campo"]] = $cmp["contenido"];
                    }
                }
            }
        }

        //
        if ($retorno["fregistro"] == '') {
            $retorno["fregistro"] = 'S';
        }


        //
        unset($arrTem);
        unset($arrTem1);
        unset($arrTem2);
        unset($arrTem3);
        unset($arrTem4);
        unset($rRues);
        unset($devs);
        unset($devs1);
        unset($arrTemEstRec);
        unset($arrTemInscrip);
        unset($arrCmps);
        return $retorno;
    }

}

?>
