<?php

class funcionesRegistrales_construirJsonSistemasExternos {

    public static function construirJsonSistemasExternos($mysqli, $exp, $tiporeporte, $sistemadestino, $tipoenvio = '1') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $nameLog = 'construirJsonSistemasExternos_' . date("Ymd");
        $arrJson = array();
        $arrJson["matricula"] = $exp["matricula"];
        $arrJson["origen"] = '';
        $arrJson["idclase"] = '';
        $arrJson["numid"] = '';
        $arrJson["nit"] = '';
        $arrJson["razonsocial"] = '';
        $arrJson["sigla"] = '';
        $arrJson["apellido1"] = '';
        $arrJson["apellido2"] = '';
        $arrJson["nombre1"] = '';
        $arrJson["nombre2"] = '';
        $arrJson["organizacion"] = '';
        $arrJson["categoria"] = '';
        $arrJson["estado"] = '';
        $arrJson["fecmatricula"] = '';
        $arrJson["fecrenovacion"] = '';
        $arrJson["ultanoren"] = '';
        $arrJson["feccancelacion"] = '';
        $arrJson["dircom"] = '';
        $arrJson["telcom1"] = '';
        $arrJson["telcom2"] = '';
        $arrJson["telcom3"] = '';
        $arrJson["emailcom"] = '';
        $arrJson["muncom"] = '';
        $arrJson["dirnot"] = '';
        $arrJson["telnot1"] = '';
        $arrJson["telnot2"] = '';
        $arrJson["telnot3"] = '';
        $arrJson["emailnot"] = '';
        $arrJson["munnot"] = '';
        $arrJson["ingresosesperados"] = '';
        $arrJson["activosvinculados"] = '';
        $arrJson["personalvinculado"] = '';
        $arrJson["ciiu1"] = '';
        $arrJson["ciiu2"] = '';
        $arrJson["ciiu3"] = '';
        $arrJson["ciiu4"] = '';
        $arrJson["ciiu1consector"] = '';
        $arrJson["ciiu2consector"] = '';
        $arrJson["ciiu3consector"] = '';
        $arrJson["ciiu4consector"] = '';
        $arrJson["feciniact1"] = '';
        $arrJson["feciniact2"] = '';
        $arrJson["actividad"] = '';
        $arrJson["propietarios"] = array();
        $arrJson["representantelegal"] = array();
        $arrJson["informacionfinanciera"] = array();
        $cantprop = 0;
        
        // Identifica el origen del expediente
        if ($exp["numrecibo"] != '') {
            $liq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "numerorecibo='" . $exp["numrecibo"] . "'");
            if ($liq && !empty($liq)) {
                $liqcam = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_campos', "idliquidacion=" . $liq["idliquidacion"] . " and campo='subtipotramite'");
                if ($liqcam && !empty($liqcam)) {
                    if ($liqcam["contenido"] == 'matriculapnatcae' || $liqcam["contenido"] == 'matriculapjurcae') {
                        $arrJson["origen"] = 'VUE';
                    } else {
                        if (substr($liq["numerooperacion"], 0, 2) == '99') {
                            $arrJson["origen"] = 'VIRTUAL';
                        } else {
                            $arrJson["origen"] = 'PRESENCIAL';
                        }
                    }
                } else {
                    if (substr($liq["numerooperacion"], 0, 2) == '99') {
                        $arrJson["origen"] = 'VIRTUAL';
                    } else {
                        $arrJson["origen"] = 'PRESENCIAL';
                    }
                }
            }
        }

        //
        if ($arrJson["origen"] == '') {
            $nrec = '';
            $recs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $exp["matricula"] . "' and substr(servicio,1,4) = '0102'", "numerorecibo");
            if ($recs && !empty($recs)) {
                foreach ($recs as $r) {
                    $nrec = $r["numerorecibo"];
                }
            }

            //
            if ($nrec != '') {
                $liq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "numerorecibo='" . $nrec . "'");
                if ($liq && !empty($liq)) {
                    $liqcam = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_campos', "idliquidacion=" . $liq["idliquidacion"] . " and campo='subtipotramite'");
                    if ($liqcam && !empty($liqcam)) {
                        if ($liqcam["contenido"] == 'matriculapnatcae' || $liqcam["contenido"] == 'matriculapjurcae') {
                            $arrJson["origen"] = 'VUE';
                        } else {
                            if (substr($liq["numerooperacion"], 0, 2) == '99') {
                                $arrJson["origen"] = 'VIRTUAL';
                            } else {
                                $arrJson["origen"] = 'PRESENCIAL';
                            }
                        }
                    }
                }
            }
        }

        //
        if ($arrJson["origen"] == '') {
            $arrJson["origen"] = 'PRESENCIAL';
        }

        //
        if ($exp["organizacion"] > '02' && $exp["categoria"] == '1') {
            if ($exp["nit"] != '') {
                $arrJson["idclase"] = '2';
                $arrJson["numid"] = $exp["nit"];
                $arrJson["nit"] = $exp["nit"];
            }
        } else {
            $arrJson["idclase"] = $exp["idclase"];
            $arrJson["numid"] = $exp["numid"];
            $arrJson["nit"] = $exp["nit"];
        }

        //
        $arrJson["razonsocial"] = $exp["razonsocial"];
        $arrJson["sigla"] = $exp["sigla"];

        $arrJson["apellido1"] = $exp["apellido1"];
        $arrJson["apellido2"] = $exp["apellido2"];
        $arrJson["nombre1"] = $exp["nombre1"];
        $arrJson["nombre2"] = $exp["nombre2"];
        $arrJson["organizacion"] = $exp["organizacion"];
        $arrJson["categoria"] = $exp["categoria"];

        $arrJson["estado"] = $exp["ctrestmatricula"];

        $arrJson["fecmatricula"] = $exp["fecmatricula"];
        $arrJson["fecrenovacion"] = $exp["fecrenovacion"];
        $arrJson["ultanoren"] = $exp["ultanoren"];
        $arrJson["feccancelacion"] = $exp["feccancelacion"];

        $arrJson["dircom"] = $exp["dircom"];
        $arrJson["telcom1"] = $exp["telcom1"];
        $arrJson["telcom2"] = $exp["telcom2"];
        $arrJson["telcom3"] = $exp["telcom3"];
        $arrJson["emailcom"] = $exp["emailcom"];
        $arrJson["muncom"] = $exp["muncom"];

        $arrJson["dirnot"] = '';
        $arrJson["telnot1"] = '';
        $arrJson["telnot2"] = '';
        $arrJson["telnot3"] = '';
        $arrJson["emailnot"] = '';
        $arrJson["munnot"] = '';

        //
        if ($exp["organizacion"] != '02' && $exp["categoria"] != '3') {
            $arrJson["dirnot"] = $exp["dirnot"];
            $arrJson["telnot1"] = $exp["telnot"];
            $arrJson["telnot2"] = $exp["telnot2"];
            $arrJson["telnot3"] = $exp["telnot3"];
            $arrJson["emailnot"] = $exp["emailnot"];
            $arrJson["munnot"] = $exp["munnot"];
        }

        //
        $arrJson["ingresosesperados"] = $exp["ingesperados"];
        $arrJson["activosvinculados"] = $exp["actvin"];
        if ($exp["organizacion"] != '02' && $exp["categoria"] != '2' && $exp["categoria"] != '3') {
            $arrJson["activosvinculados"] = $exp["acttot"];
        }
        $arrJson["personalvinculado"] = $exp["personal"];

        $arrJson["ciiu1"] = '';
        $arrJson["ciiu2"] = '';
        $arrJson["ciiu3"] = '';
        $arrJson["ciiu4"] = '';

        //
        $arrJson["ciiu1"] = substr($exp["ciiu1"], 1);
        if ($exp["ciiu2"] != '') {
            $arrJson["ciiu2"] = substr($exp["ciiu2"], 1);
        }
        if ($exp["ciiu3"] != '') {
            $arrJson["ciiu3"] = substr($exp["ciiu3"], 1);
        }
        if ($exp["ciiu4"] != '') {
            $arrJson["ciiu4"] = substr($exp["ciiu4"], 1);
        }

        //
        $arrJson["ciiu1consector"] = $exp["ciiu1"];
        $arrJson["ciiu2consector"] = $exp["ciiu2"];
        $arrJson["ciiu3consector"] = $exp["ciiu3"];
        $arrJson["ciiu4consector"] = $exp["ciiu4"];

        $arrJson["feciniact1"] = $exp["feciniact1"];
        $arrJson["feciniact2"] = $exp["feciniact2"];
        $arrJson["actividad"] = $exp["actividad"];

        // propietarios
        $arrJson["propietarios"] = array();

        // Si es establecimiento
        if ($exp["organizacion"] == '02') {
            $props = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $exp["matricula"] . "'");
            foreach ($props as $p) {
                if ($p["estado"] == 'V') {
                    $cantprop++;
                    if ($p["matriculapropietario"] != '' && ($p["codigocamara"] == '' || $p["codigocamara"] == CODIGO_EMPRESA)) {
                        $p1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $p["matriculapropietario"] . "'");
                        if ($p1 && !empty($p1)) {
                            $prop = array();
                            $prop["camara"] = CODIGO_EMPRESA;
                            $prop["matricula"] = $p1["matricula"];

                            //
                            if ($p1["organizacion"] > '02' && $p1["categoria"] == '1') {
                                $prop["idclase"] = '2';
                                $prop["numid"] = $p1["nit"];
                                $prop["nit"] = $p1["nit"];
                            } else {
                                $prop["idclase"] = $p1["idclase"];
                                $prop["numid"] = $p1["numid"];
                                $prop["nit"] = $p1["nit"];
                            }

                            //
                            $prop["razonsocial"] = $p1["razonsocial"];
                            $prop["dircom"] = $p1["dircom"];
                            $prop["telcom1"] = $p1["telcom1"];
                            $prop["telcom2"] = $p1["telcom2"];
                            $prop["telcom3"] = $p1["telcom3"];
                            $prop["emailcom"] = $p1["emailcom"];
                            $prop["muncom"] = $p1["muncom"];
                            $prop["dirnot"] = $p1["dirnot"];
                            $prop["telnot1"] = $p1["telnot"];
                            $prop["telnot2"] = $p1["telnot2"];
                            $prop["telnot3"] = $p1["telnot3"];
                            $prop["emailnot"] = $p1["emailnot"];
                            $prop["munnot"] = $p1["munnot"];
                            $prop["idclasereplegal"] = '';
                            $prop["numidreplegal"] = '';
                            $prop["nombrereplegal"] = '';
                            $vins = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $p1["matricula"] . "'", "id");
                            if ($vins && !empty($vins)) {
                                foreach ($vins as $v) {
                                    if ($prop["idclasereplegal"] == '') {
                                        if ($v["estado"] == 'V') {
                                            $tv = retornarRegistroMysqliApi($mysqli, 'mreg_codvinculos', "id='" . $v["vinculo"] . "'");
                                            if ($tv && !empty($tv)) {
                                                if ($tv["tipovinculo"] == 'RLP' || $tv["tipovinculoceresadl"] == 'RLP') {
                                                    $prop["idclasereplegal"] = $v ["idclase"];
                                                    $prop["numidreplegal"] = $v["numid"];
                                                    $prop["nombrereplegal"] = $v["nombre"];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $arrJson["propietarios"][] = $prop;
                        }
                    } else {
                        $prop = array();
                        $prop["camara"] = $p["codigocamara"];
                        $prop["matricula"] = $p["matriculapropietario"];
                        $prop["idclase"] = $p["tipoidentificacion"];
                        $prop["numid"] = $p["identificacion"];
                        $prop["nit"] = $p["nit"];

                        if ($p["tipoidentificacion"] == '2') {
                            $prop["idclase"] = '2';
                            $prop["numid"] = $p["nit"];
                            $prop["nit"] = $p["nit"];
                        } else {
                            $prop["idclase"] = $p["tipoidentificacion"];
                            $prop["numid"] = $p["identificacion"];
                            $prop["nit"] = $p["nit"];
                        }

                        $prop["razonsocial"] = $p["razonsocial"];
                        $prop["dircom"] = $p["dircom"];
                        $prop["telcom1"] = $p["telcom1"];
                        $prop["telcom2"] = $p["telcom2"];
                        $prop["telcom3"] = $p["telcom3"];
                        $prop["emailcom"] = $p["emailcom"];
                        $prop["muncom"] = $p["muncom"];
                        $prop["dirnot"] = $p["dirnot"];
                        $prop["telnot1"] = $p["telnot1"];
                        $prop["telnot2"] = $p["telnot2"];
                        $prop["telnot3"] = $p["telnot3"];
                        $prop["emailnot"] = $p["emailnot"];
                        $prop["munnot"] = $p["munnot"];
                        $prop["idclasereplegal"] = $p["tipoidentificacionreplegal"];
                        $prop["numidreplegal"] = $p["identificacionreplegal"];
                        $prop["nombrereplegal"] = $p["nombrereplegal"];
                        $arrJson["propietarios"][] = $prop;
                    }
                }
            }
        }

        // Sucursales y agencias
        if ($exp["organizacion"] > '02' && ($exp["categoria"] == '2' || $exp["categoria"] == '3')) {
            if ($exp["cpnummat"] != '' && ($exp["cpcodcam"] == '' || $exp["cpcodcam"] == CODIGO_EMPRESA)) {
                $p1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $exp["cpnummat"] . "'");
                if ($p1 && !empty($p1)) {
                    $prop = array();
                    $prop["camara"] = CODIGO_EMPRESA;
                    $prop["matricula"] = $p1["cpnummat"];
                    $prop["idclase"] = '2';
                    $prop["numid"] = $p1["nit"];
                    $prop["nit"] = $p1["nit"];
                    $prop["razonsocial"] = $p1["razonsocial"];
                    $prop["dircom"] = $p1["dircom"];
                    $prop["telcom1"] = $p1["telcom1"];
                    $prop["telcom2"] = $p1["telcom2"];
                    $prop["telcom3"] = $p1["telcom3"];
                    $prop["emailcom"] = $p1["emailcom"];
                    $prop["muncom"] = $p1["muncom"];
                    $prop["dirnot"] = $p1["dirnot"];
                    $prop["telnot1"] = $p1["telnot"];
                    $prop["telnot2"] = $p1["telnot2"];
                    $prop["telnot3"] = $p1["telnot3"];
                    $prop["emailnot"] = $p1["emailnot"];
                    $prop["munnot"] = $p1["munnot"];
                    $prop["idclasereplegal"] = '';
                    $prop["numidreplegal"] = '';
                    $prop["nombrereplegal"] = '';
                    $vins = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $p1["cpnummat"] . "'", "id");
                    if ($vins && !empty($vins)) {
                        foreach ($vins as $v) {
                            if ($prop["idclasereplegal"] == '') {
                                if ($v["estado"] == 'V') {
                                    $tv = retornarRegistroMysqliApi($mysqli, 'mreg_codvinculos', "id='" . $v["vinculo"] . "'");
                                    if ($tv && !empty($tv)) {
                                        if ($tv["tipovinculo"] == 'RLP' || $tv["tipovinculoceresadl"] == 'RLP') {
                                            $prop["idclasereplegal"] = $v ["idclase"];
                                            $prop["numidreplegal"] = $v["numid"];
                                            $prop["nombrereplegal"] = $v["nombre"];
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $arrJson["propietarios"][] = $prop;
                }
            } else {
                $prop = array();
                $prop["camara"] = $exp["cpcodcam"];
                $prop["matricula"] = $exp["cpnummat"];
                $prop["idclase"] = '2';
                $prop["numid"] = $exp["cpnumnit"];
                $prop["nit"] = $exp["cpnumnit"];
                $prop["razonsocial"] = $exp["cprazsoc"];
                $prop["dircom"] = $exp["cpdircom"];
                $prop["telcom1"] = $exp["cpnumtel"];
                $prop["telcom2"] = $exp["cpnumtel2"];
                $prop["telcom3"] = $exp["cpnumtel3"];
                $prop["emailcom"] = '';
                $prop["muncom"] = $exp["cpcodmun"];
                $prop["dirnot"] = $exp["cpdirnot"];
                $prop["telnot1"] = '';
                $prop["telnot2"] = '';
                $prop["telnot3"] = '';
                $prop["emailnot"] = '';
                $prop["munnot"] = $exp["cpmunnot"];
                $prop["idclasereplegal"] = '';
                $prop["numidreplegal"] = '';
                $prop["nombrereplegal"] = '';
                $arrJson["propietarios"][] = $prop;
            }
        }


        // Representantes legales
        $arrJson["representantelegal"] = array();

        // Si se trata de personas jurídicas principales
        if ($exp["organizacion"] > '02' && $exp["categoria"] == '1') {
            $vins = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $exp["matricula"] . "'", "id");
            if ($vins && !empty($vins)) {
                foreach ($vins as $v) {
                    if ($v["estado"] == 'V') {
                        $tv = retornarRegistroMysqliApi($mysqli, 'mreg_codvinculos', "id='" . $v["vinculo"] . "'");
                        if ($tv && !empty($tv)) {
                            if ($tv["tipovinculo"] == 'RLP' || $tv["tipovinculoceresadl"] == 'RLP') {
                                $rl = array();
                                $rl["idclasereplegal"] = $v["idclase"];
                                $rl["numidreplegal"] = $v["numid"];
                                $rl["nombrereplegal"] = $v["nombre"];
                                $arrJson["representantelegal"][] = $rl;
                            }
                        }
                    }
                }
            }
        }


        // Información financiera
        $arrJson["informacionfinanciera"] = array();
        $arrJson["informacionfinanciera"]["actvin"] = 0;
        $arrJson["informacionfinanciera"]["acttot"] = 0;
        $arrJson["informacionfinanciera"]["actcte"] = 0;
        $arrJson["informacionfinanciera"]["actnocte"] = 0;
        $arrJson["informacionfinanciera"]["pascte"] = 0;
        $arrJson["informacionfinanciera"]["paslar"] = 0;
        $arrJson["informacionfinanciera"]["pattot"] = 0;
        $arrJson["informacionfinanciera"]["ingope"] = 0;
        $arrJson["informacionfinanciera"]["ingnoope"] = 0;
        $arrJson["informacionfinanciera"]["utiope"] = 0;
        $arrJson["informacionfinanciera"]["utinet"] = 0;
        $arrJson["informacionfinanciera"]["gruponiif"] = '';
        if ($exp["organizacion"] != '02' && $exp["categoria"] != '2' && $exp["categoria"] != '3') {
            $arrJson["informacionfinanciera"] = array();
            $arrJson["informacionfinanciera"]["acttot"] = $exp["acttot"];
            $arrJson["informacionfinanciera"]["actcte"] = $exp["actcte"];
            $arrJson["informacionfinanciera"]["actnocte"] = $exp["actnocte"];
            $arrJson["informacionfinanciera"]["pascte"] = $exp["pascte"];
            $arrJson["informacionfinanciera"]["paslar"] = $exp["paslar"];
            $arrJson["informacionfinanciera"]["pattot"] = $exp["pattot"];
            $arrJson["informacionfinanciera"]["ingope"] = $exp["ingope"];
            $arrJson["informacionfinanciera"]["ingnoope"] = $exp["ingnoope"];
            $arrJson["informacionfinanciera"]["utiope"] = $exp["utiope"];
            $arrJson["informacionfinanciera"]["utinet"] = $exp["utinet"];
            $arrJson["informacionfinanciera"]["gruponiif"] = $exp["gruponiif"];
        } else {
            $arrJson["informacionfinanciera"]["actvin"] = $exp["actvin"];
        }

        //
        $txtJson = json_encode($arrJson);
        $hash = hash('sha256', $txtJson);

        // 
        $continuar = 'si';

        // 2023-04-11 - JINT - Crea tabla de control con hash de envío
        // Sirve para controlar los envios por modificaciones 
        if ($tipoenvio == '1' && $tiporeporte == '2') {
            $txtJson = json_encode($arrJson);
            $hash = hash('sha256', $txtJson);
            $arrCampos = array(
                'sistemadestino',
                'matricula',
                'hash'
            );
            $arrValores = array(
                "'" . $sistemadestino . "'",
                "'" . $arrJson["matricula"] . "'",
                "'" . $hash . "'"
            );
            $env = retornarRegistroMysqliApi($mysqli, 'mreg_envio_matriculas_api_control', "sistemadestino='" . $sistemadestino . "' and matricula='" . $arrJson["matricula"] . "'");
            if ($env === false || empty($env)) {
                insertarRegistrosMysqliApi($mysqli, 'mreg_envio_matriculas_api_control', $arrCampos, $arrValores);
            } else {
                regrabarRegistrosMysqliApi($mysqli, 'mreg_envio_matriculas_api_control', $arrCampos, $arrValores, "id=" . $env["id"]);
            }
        }

        // 2023-04-11 - JINT - Si se encuentra que la matrícula ya fue enviada como nueva y el hash es igual
        // no envía la actualización
        if ($tipoenvio == '1' && $tiporeporte == '3') {
            $env = retornarRegistroMysqliApi($mysqli, 'mreg_envio_matriculas_api_control', "sistemadestino='" . $sistemadestino . "' and matricula='" . $arrJson["matricula"] . "'");
            if ($env && !empty($env)) {
                if ($env["hash"] === $hash) {
                    $continuar = 'no';
                    $_SESSION["generales"]["mensajeerror"] = 'Enviada previamente al sistema externo (' . $env["hash"] . ')';
                }
            }
        }

        //
        if ($continuar == 'no') {            
            return false;
        }

        //
        \logApi::general2($nameLog, '', json_encode($arrJson));

        // Validar Nit
        if ($arrJson["organizacion"] == '01' || ($arrJson["organizacion"] > '02' && $arrJson["categoria"] == '1')) {
            if ($arrJson["numid"] == '') {
                $_SESSION["generales"]["mensajeerror"] = 'Expediente sin numid';
                return false;
            }
        } else {
            if (!isset($arrJson["propietarios"]) || empty($arrJson["propietarios"])) {
                $_SESSION["generales"]["mensajeerror"] = 'Expediente sin propietarios';
                return false;
            } else {
                foreach ($arrJson["propietarios"] as $px) {
                    if ($px["numid"] == '') {
                        $_SESSION["generales"]["mensajeerror"] = 'Propietario sin Nit';
                        return false;
                    }
                }
            }
        }

        //
        $arrJson["idenvio"] = \funcionesGenerales::generarAleatorioAlfanumerico20($mysqli, 'mreg_envio_matriculas_api');
        $arrJson["tiporeporte"] = $tiporeporte;

        //
        if ($tipoenvio == '1') {
            $txtJson = json_encode($arrJson);
            $hash = hash('sha256', $txtJson);
            $arrCampos = array(
                'idenvio',
                'sistemadestino',
                'tiporeporte',
                'matricula',
                'fechahoraultimoenvio',
                'json',
                'hashcontrol',
                'estadoenvio',
                'fechahorarespuesta',
                'codigoasignadorespuesta',
                'observaciones'
            );
            $arrValores = array(
                "'" . $arrJson ["idenvio"] . "'",
                "'" . $sistemadestino . "'",
                "'" . $tiporeporte . "'",
                "'" . $arrJson["matricula"] . "'",
                "'" . date("Ymd") . ' ' . date("His") . "'",
                "'" . addslashes($txtJson) . "'",
                "'" . $hash . "'",
                "''",
                "''",
                "''",
                "''"
            );
            insertarRegistrosMysqliApi($mysqli, 'mreg_envio_matriculas_api', $arrCampos, $arrValores);
        }

//
        // \logApi::general2($nameLog,'',json_encode($arrJson));
        return $arrJson;
    }

}

?>
