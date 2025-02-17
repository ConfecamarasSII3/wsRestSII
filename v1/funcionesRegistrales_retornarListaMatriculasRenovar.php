<?php

class funcionesRegistrales_retornarListaMatriculasRenovar {

    public static function retornarListaMatriculasRenovar($mysqli, $mat = '', $procesartodas = 'L', $ide = '', $idliq = 0, $controlmultas = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        $nameLog = 'retornarListaMatriculasRenovar_' . date("Ymd");

        if (!isset($_SESSION["generales"]["limiterenovacion"])) {
            $_SESSION["generales"]["limiterenovacion"] = 5;
        }
        $iCuantas = $_SESSION["generales"]["limiterenovacion"];

//
        $retorno = array();
        $retorno["codigoError"] = '0000';
        $retorno["mensajeError"] = '';
        $retorno["idexpedientebase"] = '';
        $retorno["idmatriculabase"] = '';
        $retorno["nombrebase"] = '';
        $retorno["nom1base"] = '';
        $retorno["nom2base"] = '';
        $retorno["ape1base"] = '';
        $retorno["ape2base"] = '';
        $retorno["tipoidentificacionbase"] = '';
        $retorno["identificacionbase"] = '';
        $retorno["organizacionbase"] = '';
        $retorno["categoriabase"] = '';
        $retorno["afiliadobase"] = '';
        $retorno["email"] = '';
        $retorno["direccion"] = '';
        $retorno["telefono"] = '';
        $retorno["movil"] = '';
        $retorno["idmunicipio"] = '';
        $retorno["benley1780"] = '';
        $retorno["multadoponal"] = '';
        $retorno["matriculas"] = array();

//
        $propJurisdiccion = '';
        if ($mat != '') {
            $arrTem = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $mat, '', '', '', 'si', 'N');
            if ($arrTem === false || empty($arrTem)) {
                $_SESSION["generales"]["txtemergente"] = 'Expediente no localizado en el SII';
                return false;
            }
            if ($arrTem["estadomatricula"] != 'MA' && $arrTem["estadomatricula"] != 'MI' && $arrTem["estadomatricula"] != 'IA' && $arrTem["estadomatricula"] != 'II') {
                $_SESSION["generales"]["txtemergente"] = 'El expediente seleccionado no se encuentra activo (esta cancelado)';
                return false;
            }
            if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {
                $retorno["idexpedientebase"] = $mat;
                $retorno["idmatriculabase"] = $mat;
                $retorno["nombrebase"] = $arrTem["nombre"];
                $retorno["nom1base"] = $arrTem["nom1"];
                $retorno["nom2base"] = $arrTem["nom2"];
                $retorno["ape1base"] = $arrTem["ape1"];
                $retorno["ape2base"] = $arrTem["ape2"];
                $retorno["tipoidentificacionbase"] = $arrTem["tipoidentificacion"];
                $retorno["identificacionbase"] = $arrTem["identificacion"];
                $retorno["organizacionbase"] = $arrTem["organizacion"];
                $retorno["categoriabase"] = $arrTem["categoria"];
                $retorno["afiliadobase"] = $arrTem["afiliado"];
                $retorno["email"] = $arrTem["emailcom"];
                $retorno["direccion"] = $arrTem["dircom"];
                $telcom = '';
                $celcom = '';
                if (strlen($arrTem["telcom1"]) == 7) {
                    $telcom = $arrTem["telcom1"];
                } else {
                    if (strlen($arrTem["telcom1"]) == 10) {
                        $celcom = $arrTem["telcom1"];
                    }
                }
                if (strlen($arrTem["telcom2"]) == 7) {
                    if (trim($telcom) == '') {
                        $telcom = $arrTem["telcom2"];
                    }
                } else {
                    if (strlen($arrTem["telcom2"]) == 10) {
                        if (trim($celcom) == '') {
                            $celcom = $arrTem["telcom2"];
                        }
                    }
                }
                if (strlen($arrTem["celcom"]) == 7) {
                    if (trim($telcom) == '') {
                        $telcom = $arrTem["celcom"];
                    }
                } else {
                    if (strlen($arrTem["celcom"]) == 10) {
                        if (trim($celcom) == '') {
                            $celcom = $arrTem["celcom"];
                        }
                    }
                }
                $retorno["telefono"] = $telcom;
                $retorno["movil"] = $celcom;
                $retorno["idmunicipio"] = $arrTem["muncom"];
                $retorno["benley1780"] = $arrTem["benley1780"];
                $propJurisdiccion = 'S';
            }
        }


        if ($mat == '' && $ide != '') {
            $arrTemX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "numid like '" . $ide . "%' or nit like '" . $ide . "%'", "numid");
            if ($arrTemX === false || empty($arrTemX)) {
                $mysqli->close();
                $_SESSION["generales"]["txtemergente"] = 'Identificacion no localizado en el SII (*)';
                return false;
            }
            $arrTem = array();
            foreach ($arrTemX as $t) {
                if (ltrim(trim($t["matricula"]), "0") != '') {
                    if ($t["ctrestmatricula"] == 'MA' || $t["ctrestmatricula"] == 'MI' || $t["ctrestmatricula"] == 'IA' || $t["ctrestmatricula"] == 'II') {
                        if ($t["organizacion"] == '01' || ($t["organizacion"] > '02' && $t["categoria"] == '1')) {
                            if (empty($arrTem)) {
                                $arrTem = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $t["matricula"], '', '', '', 'si', 'N');
                                $propJurisdiccion = 'S';
                                $retorno["idexpedientebase"] = $t["matricula"];
                                $retorno["idmatriculabase"] = $t["matricula"];
                                $retorno["nombrebase"] = $arrTem["nombre"];
                                $retorno["nom1base"] = $arrTem["nom1"];
                                $retorno["nom2base"] = $arrTem["nom2"];
                                $retorno["ape1base"] = $arrTem["ape1"];
                                $retorno["ape2base"] = $arrTem["ape2"];
                                $retorno["tipoidentificacionbase"] = $arrTem["tipoidentificacion"];
                                $retorno["identificacionbase"] = $arrTem["identificacion"];
                                $retorno["organizacionbase"] = $arrTem["organizacion"];
                                $retorno["categoriabase"] = $arrTem["categoria"];
                                $retorno["afiliadobase"] = $arrTem["afiliado"];
                                $retorno["email"] = $arrTem["emailcom"];
                                $retorno["direccion"] = $arrTem["dircom"];
                                $telcom = '';
                                $celcom = '';
                                if (strlen($arrTem["telcom1"]) == 7) {
                                    $telcom = $arrTem["telcom1"];
                                } else {
                                    if (strlen($arrTem["telcom1"]) == 10) {
                                        $celcom = $arrTem["telcom1"];
                                    }
                                }
                                if (strlen($arrTem["telcom2"]) == 7) {
                                    if (trim($telcom) == '') {
                                        $telcom = $arrTem["telcom2"];
                                    }
                                } else {
                                    if (strlen($arrTem["telcom2"]) == 10) {
                                        if (trim($celcom) == '') {
                                            $celcom = $arrTem["telcom2"];
                                        }
                                    }
                                }
                                if (strlen($arrTem["celcom"]) == 7) {
                                    if (trim($telcom) == '') {
                                        $telcom = $arrTem["celcom"];
                                    }
                                } else {
                                    if (strlen($arrTem["celcom"]) == 10) {
                                        if (trim($celcom) == '') {
                                            $celcom = $arrTem["celcom"];
                                        }
                                    }
                                }
                                $retorno["telefono"] = $telcom;
                                $retorno["movil"] = $celcom;
                                $retorno["idmunicipio"] = $arrTem["muncom"];
                                $retorno["benley1780"] = $arrTem["benley1780"];
                                $retorno["ciiu1"] = $arrTem["ciius"][1];
                                $retorno["ciiu2"] = $arrTem["ciius"][2];
                                $retorno["ciiu3"] = $arrTem["ciius"][3];
                                $retorno["ciiu4"] = $arrTem["ciius"][4];
                            }
                        }
                    }
                }
            }
            if (empty($arrTem)) {
                $_SESSION["generales"]["txtemergente"] = 'Identificacion no localizado en el SII (**)';
                return false;
            }
        }

        //
        if ($arrTem["organizacion"] == '02' && ($procesartodas == 'S' || $procesartodas == 'SP' || $procesartodas == 'L')) {
            if (count($arrTem["propietarios"]) == 1) {
                if ($arrTem["propietarios"][1]["camarapropietario"] == CODIGO_EMPRESA || ltrim($arrTem["propietarios"][1]["camarapropietario"], "0") == '') {
                    if ($arrTem["propietarios"][1]["matriculapropietario"] != '') {
                        $arrTem1 = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, ltrim(trim($arrTem["propietarios"][1]["matriculapropietario"]), "0"), '', '', '', 'si', 'N');
                        if ($arrTem1 && !empty($arrTem1) && $arrTem1 != 0) {
                            if ($arrTem1["estadomatricula"] == 'MA' ||
                                    $arrTem1["estadomatricula"] == 'MI' ||
                                    $arrTem1["estadomatricula"] == 'IA' ||
                                    $arrTem1["estadomatricula"] == 'II' ||
                                    $arrTem1["estadomatricula"] == 'MC') {
                                $propJurisdiccion = 'S';
                                if ($procesartodas == 'L' || $procesartodas == 'S' || $procesartodas == 'SP') {
                                    if ($arrTem1["estadomatricula"] != 'MC') {
                                        $arrTem = $arrTem1;
                                        $retorno["idexpedientebase"] = $arrTem1["matricula"];
                                        $retorno["idmatriculabase"] = $arrTem1["matricula"];
                                        $retorno["nombrebase"] = $arrTem1["nombre"];
                                        $retorno["nom1base"] = $arrTem1["nom1"];
                                        $retorno["nom2base"] = $arrTem1["nom2"];
                                        $retorno["ape1base"] = $arrTem1["ape1"];
                                        $retorno["ape2base"] = $arrTem1["ape2"];
                                        $retorno["tipoidentificacionbase"] = $arrTem1["tipoidentificacion"];
                                        $retorno["identificacionbase"] = $arrTem1["identificacion"];
                                        $retorno["organizacionbase"] = $arrTem1["organizacion"];
                                        $retorno["categoriabase"] = $arrTem1["categoria"];
                                        $retorno["afiliadobase"] = $arrTem1["afiliado"];
                                        $retorno["email"] = $arrTem1["emailcom"];
                                        $retorno["direccion"] = $arrTem1["dircom"];
                                        $telcom = '';
                                        $celcom = '';
                                        if (strlen($arrTem1["telcom1"]) == 7) {
                                            $telcom = $arrTem1["telcom1"];
                                        } else {
                                            if (strlen($arrTem1["telcom1"]) == 10) {
                                                $celcom = $arrTem1["telcom1"];
                                            }
                                        }
                                        if (strlen($arrTem1["telcom2"]) == 7) {
                                            if (trim($telcom) == '') {
                                                $telcom = $arrTem1["telcom2"];
                                            }
                                        } else {
                                            if (strlen($arrTem1["telcom2"]) == 10) {
                                                if (trim($celcom) == '') {
                                                    $celcom = $arrTem1["telcom2"];
                                                }
                                            }
                                        }
                                        if (strlen($arrTem1["celcom"]) == 7) {
                                            if (trim($telcom) == '') {
                                                $telcom = $arrTem1["celcom"];
                                            }
                                        } else {
                                            if (strlen($arrTem1["celcom"]) == 10) {
                                                if (trim($celcom) == '') {
                                                    $celcom = $arrTem1["celcom"];
                                                }
                                            }
                                        }
                                        $retorno["telefono"] = $telcom;
                                        $retorno["movil"] = $celcom;
                                        $retorno["idmunicipio"] = $arrTem1["muncom"];
                                        $retorno["benley1780"] = $arrTem1["benley1780"];
                                        $retorno["ciiu1"] = $arrTem["ciius"][1];
                                        $retorno["ciiu2"] = $arrTem["ciius"][2];
                                        $retorno["ciiu3"] = $arrTem["ciius"][3];
                                        $retorno["ciiu4"] = $arrTem["ciius"][4];
                                    }
                                }
                            } else {
                                $propJurisdiccion = 'N';
                            }
                        } else {
                            $propJurisdiccion = 'N';
                        }
                        unset($arrTem1);
                    } else {
                        // 2018-02-09: JINT: Se adiciona control para determinar si el propietario esta o no en la jurisdicción
                        $propJurisdiccion = 'N';
                        if (trim($arrTem["propietarios"][1]["municipiopropietario"]) != '') {
                            $temx1 = retornarRegistroMysqliApi($mysqli, 'mreg_municipiosjurisdiccion', "idcodigo='" . $arrTem["propietarios"][1]["municipiopropietario"] . "'");
                            if ($temx1 && !empty($temx1)) {
                                $propJurisdiccion = 'S';
                            }
                        }
                    }
                } else {
                    $propJurisdiccion = 'N';
                }
            } else {
                $propJurisdiccion = 'N';
                if (count($arrTem["propietarios"]) > 1) {
                    if ($arrTem["propietarios"][1]["camarapropietario"] == CODIGO_EMPRESA || ltrim($arrTem["propietarios"][1]["camarapropietario"], "0") == '') {
                        $propJurisdiccion = 'S';
                    }
                }
            }
        }

        // 2018-03-01: JINT: Rutina para localizar si el propietario está dentro o fuera de la jurisdiccion
        // Cuando se trate de renovar solo el establecimiento
        if ($arrTem["organizacion"] == '02' && $procesartodas == 'N') {
            $propJurisdiccion = 'N';
            $props = retornarRegistrosMysqliApi($mysqli, "mreg_est_propietarios", "matricula='" . $mat . "'", "id");
            foreach ($props as $p) {
                if ($p["estado"] == 'V') {
                    if ($p["matriculapropietario"] != '') {
                        if ($p["codigocamara"] == '' || $p["codigocamara"] == CODIGO_EMPRESA) {
                            $propJurisdiccion = 'S';
                        }
                    } else {
                        if ($p["muncom"] != '') {
                            if (retornarRegistroMysqliApi($mysqli, "mreg_municipiosjurisdiccion", "idcodigo='" . $p["muncom"] . "'")) {
                                $propJurisdiccion = 'S';
                            }
                        }
                    }
                }
            }
        }

        // 2017-07-24: JINT: Para determinar si el propietario está dentro o fuera de la jurisdiccion
        // cuanto se trate de sucursales y agencias
        if ($arrTem["organizacion"] > '02' && ($arrTem["categoria"] == '2' || $arrTem["categoria"] == '3')) {
            $propJurisdiccion = 'S';
            if ($arrTem["cpcodcam"] != '00' && $arrTem["cpcodcam"] != CODIGO_EMPRESA) {
                $propJurisdiccion = 'N';
            }
        }


        //
        $totalMatriculas = 0;
        $i = -1;
        if ($procesartodas != 'SP' && $procesartodas != 'E' && $procesartodas != 'X') {
            $i++;
            $totalMatriculas++;
            if ($i < $iCuantas) {
                $retorno["matriculas"][$i]["idtipoidentificacion"] = $arrTem["tipoidentificacion"];
                $retorno["matriculas"][$i]["identificacion"] = $arrTem["identificacion"];
                $retorno["matriculas"][$i]["cc"] = CODIGO_EMPRESA;
                $retorno["matriculas"][$i]["matricula"] = $arrTem["matricula"];
                $retorno["matriculas"][$i]["nombre"] = $arrTem["nombre"];
                $retorno["matriculas"][$i]["ape1"] = $arrTem["ape1"];
                $retorno["matriculas"][$i]["ape2"] = $arrTem["ape2"];
                $retorno["matriculas"][$i]["nom1"] = $arrTem["nom1"];
                $retorno["matriculas"][$i]["nom2"] = $arrTem["nom2"];
                $retorno["matriculas"][$i]["organizacion"] = $arrTem["organizacion"];
                $retorno["matriculas"][$i]["categoria"] = $arrTem["categoria"];
                $retorno["matriculas"][$i]["identificacionpropietario"] = '';
                $retorno["matriculas"][$i]["identificacionrepresentantelegal"] = '';
                if ($arrTem["organizacion"] == '02') {
                    if (isset($arrTem["propietarios"][1]["idtipoidentificacionpropietario"])) {
                        $retorno["matriculas"][$i]["identificacionpropietario"] = $arrTem["propietarios"][1]["idtipoidentificacionpropietario"];
                    }
                }
                if ($arrTem["organizacion"] > '02') {
                    if (isset($arrTem["replegal"][1]["identificacionreplegal"])) {
                        $retorno["matriculas"][$i]["identificacionrepresentantelegal"] = $arrTem["replegal"][1]["identificacionreplegal"];
                    }
                }
                $retorno["matriculas"][$i]["ultimoanorenovado"] = $arrTem["ultanoren"];
                if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {
                    $retorno["matriculas"][$i]["ultimosactivos"] = $arrTem["acttot"];
                } else {
                    $retorno["matriculas"][$i]["ultimosactivos"] = $arrTem["actvin"];
                }
                $retorno["matriculas"][$i]["afiliado"] = $arrTem["afiliado"];
                $retorno["matriculas"][$i]["ultimoanoafiliado"] = $arrTem["ultanorenafi"];
                $retorno["matriculas"][$i]["propietariojurisdiccion"] = $propJurisdiccion;
                $retorno["matriculas"][$i]["disolucion"] = '';
                if ($arrTem["disueltaporvencimiento"] == 'si' || $arrTem["disueltaporacto510"] == 'si') {
                    $retorno["matriculas"][$i]["disolucion"] = 'S';
                }
                $retorno["matriculas"][$i]["fechadisolucion"] = $arrTem["fechadisolucion"];
                $retorno["matriculas"][$i]["fechanacimiento"] = $arrTem["fechanacimiento"];
                $retorno["matriculas"][$i]["fechamatricula"] = $arrTem["fechamatricula"];
                $retorno["matriculas"][$i]["fecmatant"] = $arrTem["fecmatant"];
                $retorno["matriculas"][$i]["fecharenovacion"] = $arrTem["fecharenovacion"];
                $retorno["matriculas"][$i]["benart7"] = $arrTem["art7"];
                $retorno["matriculas"][$i]["benley1780"] = $arrTem["benley1780"];
                $retorno["matriculas"][$i]["circular19"] = '';
                $retorno["matriculas"][$i]["municipio"] = $arrTem["muncom"];
                $retorno["matriculas"][$i]["clasegenesadl"] = $arrTem["clasegenesadl"];
                $retorno["matriculas"][$i]["claseespesadl"] = $arrTem["claseespesadl"];
                $retorno["matriculas"][$i]["econsoli"] = $arrTem["claseeconsoli"];
                $retorno["matriculas"][$i]["expedienteinactivo"] = '';
                if ($arrTem["estadomatricula"] == 'MI' || $arrTem["estadomatricula"] == 'II') {
                    $retorno["matriculas"][$i]["expedienteinactivo"] = 'S';
                }
                $retorno["matriculas"][$i]["dircom"] = $arrTem["dircom"];
                $retorno["matriculas"][$i]["emailcom"] = $arrTem["emailcom"];
                $retorno["matriculas"][$i]["telcom1"] = $arrTem["telcom1"];
                $retorno["matriculas"][$i]["telcom3"] = $arrTem["celcom"];
                $retorno["matriculas"][$i]["multadoponal"] = '';
                $retorno["matriculas"][$i]["ciiu1"] = $arrTem["ciius"][1];
                $retorno["matriculas"][$i]["ciiu2"] = $arrTem["ciius"][2];
                $retorno["matriculas"][$i]["ciiu3"] = $arrTem["ciius"][3];
                $retorno["matriculas"][$i]["ciiu4"] = $arrTem["ciius"][4];
            }
        }

        if ($procesartodas == 'L' || $procesartodas == 'S' || $procesartodas == 'SP' || $procesartodas == 'E' || $procesartodas == 'X') {
            if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {
                foreach ($arrTem["establecimientos"] as $est) {
                    if ($est["ultanoren"] < date("Y")) {
                        $i++;
                        $totalMatriculas++;
                        if ($i < $iCuantas) {
                            $retorno["matriculas"][$i]["idtipoidentificacion"] = '';
                            $retorno["matriculas"][$i]["identificacion"] = '';
                            $retorno["matriculas"][$i]["cc"] = CODIGO_EMPRESA;
                            $retorno["matriculas"][$i]["matricula"] = $est["matriculaestablecimiento"];
                            $retorno["matriculas"][$i]["nombre"] = $est["nombreestablecimiento"];
                            $retorno["matriculas"][$i]["ape1"] = '';
                            $retorno["matriculas"][$i]["ape2"] = '';
                            $retorno["matriculas"][$i]["nom1"] = '';
                            $retorno["matriculas"][$i]["nom2"] = '';
                            $retorno["matriculas"][$i]["organizacion"] = '02';
                            $retorno["matriculas"][$i]["categoria"] = '';
                            $retorno["matriculas"][$i]["identificacionpropietario"] = $arrTem["identificacion"];
                            $retorno["matriculas"][$i]["identificacionrepresentantelegal"] = '';
                            $retorno["matriculas"][$i]["ultimoanorenovado"] = $est["ultanoren"];
                            $retorno["matriculas"][$i]["ultimosactivos"] = $est["actvin"];
                            $retorno["matriculas"][$i]["afiliado"] = '';
                            $retorno["matriculas"][$i]["ultimoanoafiliado"] = '';
                            $retorno["matriculas"][$i]["propietariojurisdiccion"] = $propJurisdiccion;
                            $retorno["matriculas"][$i]["disolucion"] = '';
                            $retorno["matriculas"][$i]["fechadisolucion"] = '';
                            $retorno["matriculas"][$i]["fechamatricula"] = $est["fechamatricula"];
                            $retorno["matriculas"][$i]["fecmatant"] = '';
                            $retorno["matriculas"][$i]["fecharenovacion"] = $est["fecharenovacion"];
                            $retorno["matriculas"][$i]["benart7"] = '';
                            $retorno["matriculas"][$i]["benley1780"] = '';
                            $retorno["matriculas"][$i]["circular19"] = '';
                            $retorno["matriculas"][$i]["municipio"] = $est["muncom"];
                            $retorno["matriculas"][$i]["clasegenesadl"] = '';
                            $retorno["matriculas"][$i]["claseespesadl"] = '';
                            $retorno["matriculas"][$i]["econsoli"] = '';
                            $retorno["matriculas"][$i]["expedienteinactivo"] = '';
                            if ($est["estadodatosestablecimiento"] == 'MI') {
                                $retorno["matriculas"][$i]["expedienteinactivo"] = 'S';
                            }
                            $retorno["matriculas"][$i]["dircom"] = $est["dircom"];
                            $retorno["matriculas"][$i]["emailcom"] = $est["emailcom"];
                            $retorno["matriculas"][$i]["telcom1"] = $est["telcom1"];
                            $retorno["matriculas"][$i]["telcom2"] = $est["telcom2"];
                            $retorno["matriculas"][$i]["telcom3"] = $est["telcom3"];
                            $retorno["matriculas"][$i]["multadoponal"] = '';
                            $retorno["matriculas"][$i]["ciiu1"] = $est["ciiu1"];
                            $retorno["matriculas"][$i]["ciiu2"] = $est["ciiu2"];
                            $retorno["matriculas"][$i]["ciiu3"] = $est["ciiu3"];
                            $retorno["matriculas"][$i]["ciiu4"] = $est["ciiu4"];
                        }
                    }
                }

                foreach ($arrTem["sucursalesagencias"] as $est) {
                    if ($est["estado"] == 'MA' || $est["estado"] == 'MI') {
                        if ($est["ultanoren"] < date("Y")) {
                            $i++;
                            $totalMatriculas++;
                            if ($i < $iCuantas) {
                                $retorno["matriculas"][$i]["idtipoidentificacion"] = '';
                                $retorno["matriculas"][$i]["identificacion"] = '';
                                $retorno["matriculas"][$i]["cc"] = CODIGO_EMPRESA;
                                $retorno["matriculas"][$i]["matricula"] = $est["matriculasucage"];
                                $retorno["matriculas"][$i]["nombre"] = $est["nombresucage"];
                                $retorno["matriculas"][$i]["ape1"] = '';
                                $retorno["matriculas"][$i]["ape2"] = '';
                                $retorno["matriculas"][$i]["nom1"] = '';
                                $retorno["matriculas"][$i]["nom2"] = '';
                                $retorno["matriculas"][$i]["organizacion"] = $arrTem["organizacion"];
                                $retorno["matriculas"][$i]["categoria"] = $est["categoria"];
                                $retorno["matriculas"][$i]["identificacionpropietario"] = $arrTem["identificacion"];
                                $retorno["matriculas"][$i]["identificacionrepresentantelegal"] = '';
                                $retorno["matriculas"][$i]["ultimoanorenovado"] = $est["ultanoren"];
                                $retorno["matriculas"][$i]["ultimosactivos"] = $est["actvin"];
                                $retorno["matriculas"][$i]["afiliado"] = '';
                                $retorno["matriculas"][$i]["ultimoanoafiliado"] = '';
                                $retorno["matriculas"][$i]["propietariojurisdiccion"] = 'S';
                                $retorno["matriculas"][$i]["disolucion"] = '';
                                $retorno["matriculas"][$i]["fechadisolucion"] = '';
                                $retorno["matriculas"][$i]["fechamatricula"] = $est["fechamatricula"];
                                $retorno["matriculas"][$i]["fecmatant"] = '';
                                $retorno["matriculas"][$i]["fecharenovacion"] = $est["fecharenovacion"];
                                $retorno["matriculas"][$i]["benart7"] = '';
                                $retorno["matriculas"][$i]["benley1780"] = '';
                                $retorno["matriculas"][$i]["circular19"] = '';
                                $retorno["matriculas"][$i]["municipio"] = $est["muncom"];
                                $retorno["matriculas"][$i]["clasegenesadl"] = '';
                                $retorno["matriculas"][$i]["claseespesadl"] = '';
                                $retorno["matriculas"][$i]["econsoli"] = '';
                                $retorno["matriculas"][$i]["expedienteinactivo"] = '';
                                if ($est["estado"] == 'MI') {
                                    $retorno["matriculas"][$i]["expedienteinactivo"] = 'S';
                                }
                                $retorno["matriculas"][$i]["dircom"] = $est["dircom"];
                                $retorno["matriculas"][$i]["emailcom"] = $est["emailcom"];
                                $retorno["matriculas"][$i]["telcom1"] = $est["telcom1"];
                                $retorno["matriculas"][$i]["telcom2"] = $est["telcom2"];
                                $retorno["matriculas"][$i]["telcom3"] = $est["telcom3"];
                                $retorno["matriculas"][$i]["multadoponal"] = '';
                                $retorno["matriculas"][$i]["ciiu1"] = $est["ciiu1"];
                                $retorno["matriculas"][$i]["ciiu2"] = $est["ciiu2"];
                                $retorno["matriculas"][$i]["ciiu3"] = $est["ciiu3"];
                                $retorno["matriculas"][$i]["ciiu4"] = $est["ciiu4"];
                            }
                        }
                    }
                }


                if (!defined('RENOVACION_ACTIVAR_NACIONALES')) {
                    define('RENOVACION_ACTIVAR_NACIONALES', 'N');
                }
                if (($procesartodas == 'S' || $procesartodas == 'SP' || $procesartodas == 'X') && RENOVACION_ACTIVAR_NACIONALES == 'S') {
                    $inat = 0;
                    foreach ($arrTem["establecimientosnacionales"] as $est) {
                        if ($est["ultanoren"] < date("Y")) {
                            $siren = 'si';
                            $esx = retornarRegistrosMysqliApi($mysqli, "mreg_establecimientos_nacionales", "cc='" . $est["cc"] . "' and matricula='" . $est["matriculaestablecimiento"] . "'", "id");
                            if ($esx && !empty($esx)) {
                                foreach ($esx as $ex) {
                                    if ($ex["numerorecibo"] != '' && substr($ex["fecharecibo"], 0, 4) == date("Y")) {
                                        $siren = 'no';
                                    }
                                }
                            }
                            if ($siren == 'si') {
                                $i++;
                                $inat++;
                                $totalMatriculas++;
                                if ($i < $iCuantas) {
                                    $retorno["matriculas"][$i]["idtipoidentificacion"] = '';
                                    $retorno["matriculas"][$i]["identificacion"] = '';
                                    $retorno["matriculas"][$i]["cc"] = $est["cc"];
                                    $retorno["matriculas"][$i]["matricula"] = $est["matriculaestablecimiento"];
                                    $retorno["matriculas"][$i]["nombre"] = $est["nombreestablecimiento"];
                                    $retorno["matriculas"][$i]["ape1"] = '';
                                    $retorno["matriculas"][$i]["ape2"] = '';
                                    $retorno["matriculas"][$i]["nom1"] = '';
                                    $retorno["matriculas"][$i]["nom2"] = '';
                                    $retorno["matriculas"][$i]["organizacion"] = $est["organizacion"];
                                    $retorno["matriculas"][$i]["categoria"] = $est["categoria"];
                                    $retorno["matriculas"][$i]["identificacionpropietario"] = $arrTem["identificacion"];
                                    $retorno["matriculas"][$i]["identificacionrepresentantelegal"] = '';
                                    $retorno["matriculas"][$i]["ultimoanorenovado"] = $est["ultanoren"];
                                    $retorno["matriculas"][$i]["ultimosactivos"] = $est["actvin"];
                                    $retorno["matriculas"][$i]["afiliado"] = '';
                                    $retorno["matriculas"][$i]["ultimoanoafiliado"] = '';
                                    $retorno["matriculas"][$i]["propietariojurisdiccion"] = 'N';
                                    $retorno["matriculas"][$i]["disolucion"] = '';
                                    $retorno["matriculas"][$i]["fechadisolucion"] = '';
                                    $retorno["matriculas"][$i]["fechamatricula"] = $est["fechamatricula"];
                                    $retorno["matriculas"][$i]["fecmatant"] = '';
                                    $retorno["matriculas"][$i]["fecharenovacion"] = $est["fecharenovacion"];
                                    $retorno["matriculas"][$i]["benart7"] = '';
                                    $retorno["matriculas"][$i]["benley1780"] = '';
                                    $retorno["matriculas"][$i]["circular19"] = '';
                                    $retorno["matriculas"][$i]["municipio"] = $est["muncom"];
                                    $retorno["matriculas"][$i]["clasegenesadl"] = '';
                                    $retorno["matriculas"][$i]["claseespesadl"] = '';
                                    $retorno["matriculas"][$i]["econsoli"] = '';
                                    $retorno["matriculas"][$i]["expedienteinactivo"] = '';
                                    if ($est["estadomatricula"] == 'MI') {
                                        $retorno["matriculas"][$i]["expedienteinactivo"] = 'S';
                                    }
                                    $retorno["matriculas"][$i]["dircom"] = $est["dircom"];
                                    $retorno["matriculas"][$i]["emailcom"] = $est["emailcom"];
                                    $retorno["matriculas"][$i]["telcom1"] = $est["telcom1"];
                                    $retorno["matriculas"][$i]["telcom2"] = $est["telcom2"];
                                    $retorno["matriculas"][$i]["telcom3"] = $est["telcom3"];
                                    $retorno["matriculas"][$i]["multadoponal"] = '';
                                    $retorno["matriculas"][$i]["ciiu1"] = $est["ciiu1"];
                                    $retorno["matriculas"][$i]["ciiu2"] = $est["ciiu2"];
                                    $retorno["matriculas"][$i]["ciiu3"] = $est["ciiu3"];
                                    $retorno["matriculas"][$i]["ciiu4"] = $est["ciiu4"];

                                    // 2017-02-25: JINT: En caso de liquidación crea mreg_establecimientos_nacionales
                                    if ($idliq != 0) {
                                        if ($inat == 1) {
                                            borrarRegistrosMysqliApi($mysqli, 'mreg_establecimientos_nacionales', "idliquidacion=" . $idliq);
                                        }
                                        $arrCampos = array(
                                            'idliquidacion',
                                            'cc',
                                            'matricula',
                                            'razonsocial',
                                            'organizacion',
                                            'categoria',
                                            'estado',
                                            'fechamatricula',
                                            'fecharenovacion',
                                            'ultanoren',
                                            'dircom',
                                            'barriocom',
                                            'telcom1',
                                            'telcom2',
                                            'telcom3',
                                            'muncom',
                                            'emailcom',
                                            'ctrubi',
                                            'zonapostalcom',
                                            'dirnot',
                                            'barrionot',
                                            'telnot1',
                                            'munnot',
                                            'emailnot',
                                            'zonapostalnot',
                                            'tipolocal',
                                            'tipopropietario',
                                            'afiliado',
                                            'desactiv',
                                            'ciiu1',
                                            'shd1',
                                            'ciiu2',
                                            'shd2',
                                            'ciiu3',
                                            'shd3',
                                            'ciiu4',
                                            'shd4',
                                            'personal',
                                            'actvin'
                                        );
                                        $arrValores = array(
                                            $idliq,
                                            "'" . $est["cc"] . "'",
                                            "'" . ltrim($est["matriculaestablecimiento"], "0") . "'",
                                            "'" . addslashes($est["nombreestablecimiento"]) . "'",
                                            "'" . ($est["organizacion"]) . "'",
                                            "'" . ($est["categoria"]) . "'",
                                            "'" . ($est["estadomatricula"]) . "'",
                                            "'" . ($est["fechamatricula"]) . "'",
                                            "'" . ($est["fecharenovacion"]) . "'",
                                            "'" . ($est["ultanoren"]) . "'",
                                            "'" . addslashes($est["dircom"]) . "'",
                                            "'" . addslashes($est["nbarriocom"]) . "'",
                                            "'" . ($est["telcom1"]) . "'",
                                            "'" . ($est["telcom2"]) . "'",
                                            "'" . ($est["telcom3"]) . "'",
                                            "'" . ($est["muncom"]) . "'",
                                            "'" . addslashes($est["emailcom"]) . "'",
                                            "'" . ($est["ctrubi"]) . "'",
                                            "'" . ($est["codpostalcom"]) . "'",
                                            "'" . addslashes($est["dirnot"]) . "'",
                                            "'" . addslashes($est["nbarrionot"]) . "'",
                                            "''",
                                            "'" . ($est["munnot"]) . "'",
                                            "'" . addslashes($est["emailnot"]) . "'",
                                            "'" . ($est["codpostalnot"]) . "'",
                                            "'" . ($est["tipolocal"]) . "'",
                                            "'" . ($est["tipopropietario"]) . "'",
                                            "'" . ($est["afiliado"]) . "'",
                                            "'" . addslashes($est["desactiv"]) . "'",
                                            "'" . ($est["ciiu1"]) . "'",
                                            "'" . ($est["shd1"]) . "'",
                                            "'" . ($est["ciiu2"]) . "'",
                                            "'" . ($est["shd2"]) . "'",
                                            "'" . ($est["ciiu3"]) . "'",
                                            "'" . ($est["shd3"]) . "'",
                                            "'" . ($est["ciiu4"]) . "'",
                                            "'" . ($est["shd4"]) . "'",
                                            intval($est["personal"]),
                                            doubleval($est["actvin"])
                                        );
                                        $res = insertarRegistrosMysqliApi($mysqli, 'mreg_establecimientos_nacionales', $arrCampos, $arrValores);
                                        if ($res == false) {
                                            \logApi::general2($nameLog, '', 'Error grabado establecimientos nacionales ' . $_SESSION["generales"]["mensajeerror"]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }


        // Aplica para el 2019
        $anoren = date("Y");
        $anorenant = $anoren - 1;
        if ($retorno["matriculas"][0]["organizacion"] == '01' || ($retorno["matriculas"][0]["organizacion"] > '02' && $retorno["matriculas"][0]["categoria"] == '1')) {
            if ($retorno["matriculas"][0]["fechamatricula"] >= $anorenant . '0101') {
                if ($retorno["matriculas"][0]["benley1780"] == '') {
                    if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matriculas"][0]["matricula"] . "' and servicio='01090110' and (fecoperacion between '" . $anorenant . "0101' and '" . $anorenant . "1231')") > 0) {
                        $retorno["matriculas"][0]["benley1780"] = 'S';
                        $retorno["matriculas"]["benley1780"] = 'S';
                    }
                }
            }
        }

        // Verifica codigo de policia
        if (!defined('ACTIVAR_CONTROL_MULTAS_PONAL')) {
            define('ACTIVAR_CONTROL_MULTAS_PONAL', 'NO');
        }

        if ($controlmultas == '') {
            $controlmultas = ACTIVAR_CONTROL_MULTAS_PONAL;
        }

        // 2018-03-14: JINT: En caso de no estar habilitado el control de multas, valida localmente en la tabla
        // mreg_cruce_ponal_multas
        if (ACTIVAR_CONTROL_MULTAS_PONAL == 'LOCAL') {
            $retorno["matriculas"][0]["multadoponal"] = 'N';
            $retorno["multadoponal"] = 'N';
            $retorno["mensajeError"] = '';
            $mulx = retornarRegistroMysqliApi($mysqli, 'mreg_cruce_ponal_multas', "identificacion='" . $retorno["matriculas"][0]["identificacion"] . "'");
            if ($mulx && !empty($mulx)) {
                if ($mulx["multavencida"] == 'SI') {
                    if ($_SESSION["generales"]["tipousuario"] != '00') {
                        $retorno["matriculas"][0]["multadoponal"] = 'L';
                        $retorno["multadoponal"] = 'L';
                        $retorno["codigoError"] = '4000';
                        $retorno["mensajeError"] = 'Se ha encontrado (en la BD local) que la identificaci&oacute;n ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeError"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $retorno["mensajeError"] .= 'por la Polic&iacute;a Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s), ';
                        $retorno["mensajeError"] .= 'se sugiere revisar en la p&aacute;gina web de la Polic&iacute;a para determinar si ';
                        $retorno["mensajeError"] .= 'la misma no ha sido pagada. Recuerde dejar la evidencia de la verificaci&OACUTE;n realizada.';
                    }
                    if ($_SESSION["generales"]["tipousuario"] == '00') {
                        $retorno["matriculas"][0]["multadoponal"] = 'L';
                        $retorno["multadoponal"] = 'L';
                        $retorno["codigoError"] = '4000';
                        $retorno["mensajeError"] = 'Apreciado usuario, se ha encontrado (en la BD local) que la identificaci&oacute;n ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeError"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $retorno["mensajeError"] .= 'por la Polic&iacute;a Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s). ';
                        $retorno["mensajeError"] .= 'Para que su proceso de renovaci&oacute;n pueda hacerse en forma completa, le sugerimos ';
                        $retorno["mensajeError"] .= 'tener a la mano el soporte de pago de la misma para poder cargarlo como un soporte de la renovaci&oacute;n.';
                    }
                }
            }
            return $retorno;
        }

        if ($controlmultas == 'SI-NOBLOQUEAR') {
            if ($retorno["matriculas"][0]["organizacion"] == '01' && $procesartodas != 'SP') {
                $resx = \funcionesRegistrales::consultarMultasPolicia($mysqli, $retorno["matriculas"][0]["idtipoidentificacion"], $retorno["matriculas"][0]["identificacion"], $idliq);
                if ($resx == 'SI') {
                    $retorno["matriculas"][0]["multadoponal"] = 'S';
                    $retorno["multadoponal"] = 'S';
                } else {
                    if ($resx == 'NO') {
                        $retorno["matriculas"][0]["multadoponal"] = 'N';
                        $retorno["multadoponal"] = 'N';
                    } else {
                        $retorno["matriculas"][0]["multadoponal"] = 'E';
                        $retorno["multadoponal"] = 'E';
                    }
                }
            }
        }

        if ($controlmultas == 'SI-BLOQUEAR') {
            if ($retorno["matriculas"][0]["organizacion"] == '01' && $procesartodas != 'SP') {
                $resx = \funcionesRegistrales::consultarMultasPolicia($mysqli, $retorno["matriculas"][0]["idtipoidentificacion"], $retorno["matriculas"][0]["identificacion"], $idliq);
                if ($resx == 'SI') {
                    if (isset($retorno["matriculas"][1])) {
                        $retorno["codigoError"] = '5000';
                        $retorno["mensajeError"] = 'Se ha encontrado que la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeError"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $retorno["mensajeError"] .= 'por la Policía Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s) - ';
                        $retorno["mensajeError"] .= 'No ha(n) sido pagada(s).<br><br>';
                        $retorno["mensajeError"] .= 'Para realizar la renovación de la matrícula asociada con la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeError"] .= 'se deberá(n) pagar la(s) misma(s) y esperar a que sea(n) registrada(s) en el ';
                        $retorno["mensajeError"] .= 'Sistema Nacional de Medidas Correctiva.<br><br>';
                        $retorno["mensajeError"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/verMultas.php?idliquidacion=' . $idliq . '" target="_blank">Ver Las multas</a><br><br>';
                        $retorno["mensajeError"] .= 'Si desea continuar con el proceso de renovación de las matrículas asociadas ';
                        $retorno["mensajeError"] .= 'a la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' oprima el ';
                        $retorno["mensajeError"] .= 'siguiente enlace<br><br>';
                        $retorno["mensajeError"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregRenovacionMatricula.php?accion=recuperarmatriculasrenovar&procesartodas=SP&identificacionbase=' . $retorno["matriculas"][0]["identificacion"] . '">Renovar establecimientos sucursales y agencias</a><br><br>';
                        $retorno["matriculas"] = array();
                    } else {
                        $retorno["codigoError"] = '5000';
                        $retorno["mensajeError"] = 'Se ha encontrado que la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeError"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $retorno["mensajeError"] .= 'por la Policía Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s) - ';
                        $retorno["mensajeError"] .= 'No ha(n) sido pagada(s).<br><br>';
                        $retorno["mensajeError"] .= 'Para realizar la renovación de la matrícula asociada con la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeError"] .= 'se deberá(n) pagar la(s) misma(s) y esperar a que sea(n) registrada(s) en el ';
                        $retorno["mensajeError"] .= 'Sistema Nacional de Medidas Correctiva.<br><br>';
                        $retorno["mensajeError"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/verMultas.php?idliquidacion=' . $idliq . ' target="_blank">Ver Las multas</a><br><br>';
                        $retorno["mensajeError"] .= 'No es posible continuar con el proceso de renovación. ';
                        $retorno["mensajeError"] .= 'Para volver al módulo de renovación y seleccionar otro expediente ';
                        $retorno["mensajeError"] .= 'oprima el siguiente enlace.<br><br>';
                        $retorno["mensajeError"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregRenovacionMatricula.php?accion=pantallaseleccion">Reiniciar renovación</a><br><br>';
                        $retorno["matriculas"] = array();
                    }
                } else {
                    if ($resx == 'NO') {
                        $retorno["matriculas"][0]["multadoponal"] = 'N';
                        $retorno["multadoponal"] = 'N';
                    } else {
                        $retorno["matriculas"][0]["multadoponal"] = 'E';
                        $retorno["multadoponal"] = 'E';
                    }
                }
            }
        }

        // echo $procesartodas. ' ' . $i;
        $_SESSION["generales"]["totalmatriculas"] = $totalMatriculas;

        if ($retorno["multadoponal"] == 'E') {
            $retorno["codigoError"] = '4000';
            $retorno["mensajeError"] = 'No fue posible verificar si el comerciante tiene multas vencidas de acuerdo ';
            $retorno["mensajeError"] .= 'con lo establecido en el C&oacute;digo de Polic&iacute;a. Por favor verificar en la p&aacute;gina web de la polic&iacute;a ';
            $retorno["mensajeError"] .= 'antes de continuar con el proceso de renovaci&oacute;n.';
        }
        return $retorno;
    }

    public static function retornarListaMatriculasRenovarNuevo($mysqli, $mat = '', $ide = '', $procesartodas = 'L', $idliq = 0) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        $nameLog = 'retornarListaMatriculasRenovarNuevo_' . date("Ymd");

        if (!isset($_SESSION["generales"]["limiterenovacion"])) {
            $_SESSION["generales"]["limiterenovacion"] = 5;
        }
        $iCuantas = $_SESSION["generales"]["limiterenovacion"];

//
        $retorno = array();
        $retorno["codigoerror"] = '0000';
        $retorno["mensajeError"] = '';
        $retorno["idexpedientebase"] = '';
        $retorno["idmatriculabase"] = '';
        $retorno["nombrebase"] = '';
        $retorno["nom1base"] = '';
        $retorno["nom2base"] = '';
        $retorno["ape1base"] = '';
        $retorno["ape2base"] = '';
        $retorno["tipoidentificacionbase"] = '';
        $retorno["identificacionbase"] = '';
        $retorno["organizacionbase"] = '';
        $retorno["categoriabase"] = '';
        $retorno["afiliadobase"] = '';
        $retorno["email"] = '';
        $retorno["direccion"] = '';
        $retorno["telefono"] = '';
        $retorno["movil"] = '';
        $retorno["idmunicipio"] = '';
        $retorno["benley1780"] = '';
        $retorno["multadoponal"] = '';
        $retorno["matriculas"] = array();
        $retorno["matriculasunicas"] = array();

//
        $propJurisdiccion = '';
        if ($mat != '') {
            $arrTem = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $mat, '', '', '', 'si', 'N');
            if ($arrTem === false || empty($arrTem)) {
                $_SESSION["generales"]["txtemergente"] = 'Expediente no localizado en el SII';
                return false;
            }
            if ($arrTem["estadomatricula"] != 'MA' && $arrTem["estadomatricula"] != 'MI' && $arrTem["estadomatricula"] != 'IA' && $arrTem["estadomatricula"] != 'II') {
                $_SESSION["generales"]["txtemergente"] = 'El expediente seleccionado no se encuentra activo (esta cancelado)';
                return false;
            }
            if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {
                $retorno["idexpedientebase"] = $mat;
                $retorno["idmatriculabase"] = $mat;
                $retorno["nombrebase"] = $arrTem["nombre"];
                $retorno["nom1base"] = $arrTem["nom1"];
                $retorno["nom2base"] = $arrTem["nom2"];
                $retorno["ape1base"] = $arrTem["ape1"];
                $retorno["ape2base"] = $arrTem["ape2"];
                $retorno["tipoidentificacionbase"] = $arrTem["tipoidentificacion"];
                $retorno["identificacionbase"] = $arrTem["identificacion"];
                $retorno["organizacionbase"] = $arrTem["organizacion"];
                $retorno["categoriabase"] = $arrTem["categoria"];
                $retorno["afiliadobase"] = $arrTem["afiliado"];
                $retorno["email"] = $arrTem["emailcom"];
                $retorno["direccion"] = $arrTem["dircom"];
                $telcom = '';
                $celcom = '';
                if (strlen($arrTem["telcom1"]) == 7) {
                    $telcom = $arrTem["telcom1"];
                } else {
                    if (strlen($arrTem["telcom1"]) == 10) {
                        $celcom = $arrTem["telcom1"];
                    }
                }
                if (strlen($arrTem["telcom2"]) == 7) {
                    if (trim($telcom) == '') {
                        $telcom = $arrTem["telcom2"];
                    }
                } else {
                    if (strlen($arrTem["telcom2"]) == 10) {
                        if (trim($celcom) == '') {
                            $celcom = $arrTem["telcom2"];
                        }
                    }
                }
                if (strlen($arrTem["celcom"]) == 7) {
                    if (trim($telcom) == '') {
                        $telcom = $arrTem["celcom"];
                    }
                } else {
                    if (strlen($arrTem["celcom"]) == 10) {
                        if (trim($celcom) == '') {
                            $celcom = $arrTem["celcom"];
                        }
                    }
                }
                $retorno["telefono"] = $telcom;
                $retorno["movil"] = $celcom;
                $retorno["idmunicipio"] = $arrTem["muncom"];
                $retorno["benley1780"] = $arrTem["benley1780"];
                $propJurisdiccion = 'S';
            }
        }


        if ($mat == '' && $ide != '') {
            $arrTemX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "numid like '" . $ide . "%' or nit like '" . $ide . "%'", "numid");
            if ($arrTemX === false || empty($arrTemX)) {
                $mysqli->close();
                $_SESSION["generales"]["txtemergente"] = 'Identificacion no localizado en el SII (*)';
                return false;
            }
            $arrTem = array();
            foreach ($arrTemX as $t) {
                if (ltrim(trim($t["matricula"]), "0") != '') {
                    if ($t["ctrestmatricula"] == 'MA' || $t["ctrestmatricula"] == 'MI' || $t["ctrestmatricula"] == 'IA' || $t["ctrestmatricula"] == 'II') {
                        if ($t["organizacion"] == '01' || ($t["organizacion"] > '02' && $t["categoria"] == '1')) {
                            if (empty($arrTem)) {
                                $arrTem = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $t["matricula"], '', '', '', 'si', 'N');
                                $propJurisdiccion = 'S';
                                $retorno["idexpedientebase"] = $t["matricula"];
                                $retorno["idmatriculabase"] = $t["matricula"];
                                $retorno["nombrebase"] = $arrTem["nombre"];
                                $retorno["nom1base"] = $arrTem["nom1"];
                                $retorno["nom2base"] = $arrTem["nom2"];
                                $retorno["ape1base"] = $arrTem["ape1"];
                                $retorno["ape2base"] = $arrTem["ape2"];
                                $retorno["tipoidentificacionbase"] = $arrTem["tipoidentificacion"];
                                $retorno["identificacionbase"] = $arrTem["identificacion"];
                                $retorno["organizacionbase"] = $arrTem["organizacion"];
                                $retorno["categoriabase"] = $arrTem["categoria"];
                                $retorno["afiliadobase"] = $arrTem["afiliado"];
                                $retorno["email"] = $arrTem["emailcom"];
                                $retorno["direccion"] = $arrTem["dircom"];
                                $telcom = '';
                                $celcom = '';
                                if (strlen($arrTem["telcom1"]) == 7) {
                                    $telcom = $arrTem["telcom1"];
                                } else {
                                    if (strlen($arrTem["telcom1"]) == 10) {
                                        $celcom = $arrTem["telcom1"];
                                    }
                                }
                                if (strlen($arrTem["telcom2"]) == 7) {
                                    if (trim($telcom) == '') {
                                        $telcom = $arrTem["telcom2"];
                                    }
                                } else {
                                    if (strlen($arrTem["telcom2"]) == 10) {
                                        if (trim($celcom) == '') {
                                            $celcom = $arrTem["telcom2"];
                                        }
                                    }
                                }
                                if (strlen($arrTem["celcom"]) == 7) {
                                    if (trim($telcom) == '') {
                                        $telcom = $arrTem["celcom"];
                                    }
                                } else {
                                    if (strlen($arrTem["celcom"]) == 10) {
                                        if (trim($celcom) == '') {
                                            $celcom = $arrTem["celcom"];
                                        }
                                    }
                                }
                                $retorno["telefono"] = $telcom;
                                $retorno["movil"] = $celcom;
                                $retorno["idmunicipio"] = $arrTem["muncom"];
                                $retorno["benley1780"] = $arrTem["benley1780"];
                                $retorno["ciiu1"] = $arrTem["ciius"][1];
                                $retorno["ciiu2"] = $arrTem["ciius"][2];
                                $retorno["ciiu3"] = $arrTem["ciius"][3];
                                $retorno["ciiu4"] = $arrTem["ciius"][4];
                            }
                        }
                    }
                }
            }
            if (empty($arrTem)) {
                $_SESSION["generales"]["txtemergente"] = 'Identificacion no localizado en el SII (**)';
                return false;
            }
        }

        //
        if ($arrTem["organizacion"] == '02' && ($procesartodas == 'S' || $procesartodas == 'SP' || $procesartodas == 'L')) {
            if (count($arrTem["propietarios"]) == 1) {
                if ($arrTem["propietarios"][1]["camarapropietario"] == CODIGO_EMPRESA || ltrim($arrTem["propietarios"][1]["camarapropietario"], "0") == '') {
                    if ($arrTem["propietarios"][1]["matriculapropietario"] != '') {
                        $arrTem1 = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, ltrim(trim($arrTem["propietarios"][1]["matriculapropietario"]), "0"), '', '', '', 'si', 'N');
                        if ($arrTem1 && !empty($arrTem1) && $arrTem1 != 0) {
                            if ($arrTem1["estadomatricula"] == 'MA' ||
                                    $arrTem1["estadomatricula"] == 'MI' ||
                                    $arrTem1["estadomatricula"] == 'IA' ||
                                    $arrTem1["estadomatricula"] == 'II' ||
                                    $arrTem1["estadomatricula"] == 'MC') {
                                $propJurisdiccion = 'S';
                                if ($procesartodas == 'L' || $procesartodas == 'S' || $procesartodas == 'SP') {
                                    if ($arrTem1["estadomatricula"] != 'MC') {
                                        $arrTem = $arrTem1;
                                        $retorno["idexpedientebase"] = $arrTem1["matricula"];
                                        $retorno["idmatriculabase"] = $arrTem1["matricula"];
                                        $retorno["nombrebase"] = $arrTem1["nombre"];
                                        $retorno["nom1base"] = $arrTem1["nom1"];
                                        $retorno["nom2base"] = $arrTem1["nom2"];
                                        $retorno["ape1base"] = $arrTem1["ape1"];
                                        $retorno["ape2base"] = $arrTem1["ape2"];
                                        $retorno["tipoidentificacionbase"] = $arrTem1["tipoidentificacion"];
                                        $retorno["identificacionbase"] = $arrTem1["identificacion"];
                                        $retorno["organizacionbase"] = $arrTem1["organizacion"];
                                        $retorno["categoriabase"] = $arrTem1["categoria"];
                                        $retorno["afiliadobase"] = $arrTem1["afiliado"];
                                        $retorno["email"] = $arrTem1["emailcom"];
                                        $retorno["direccion"] = $arrTem1["dircom"];
                                        $telcom = '';
                                        $celcom = '';
                                        if (strlen($arrTem1["telcom1"]) == 7) {
                                            $telcom = $arrTem1["telcom1"];
                                        } else {
                                            if (strlen($arrTem1["telcom1"]) == 10) {
                                                $celcom = $arrTem1["telcom1"];
                                            }
                                        }
                                        if (strlen($arrTem1["telcom2"]) == 7) {
                                            if (trim($telcom) == '') {
                                                $telcom = $arrTem1["telcom2"];
                                            }
                                        } else {
                                            if (strlen($arrTem1["telcom2"]) == 10) {
                                                if (trim($celcom) == '') {
                                                    $celcom = $arrTem1["telcom2"];
                                                }
                                            }
                                        }
                                        if (strlen($arrTem1["celcom"]) == 7) {
                                            if (trim($telcom) == '') {
                                                $telcom = $arrTem1["celcom"];
                                            }
                                        } else {
                                            if (strlen($arrTem1["celcom"]) == 10) {
                                                if (trim($celcom) == '') {
                                                    $celcom = $arrTem1["celcom"];
                                                }
                                            }
                                        }
                                        $retorno["telefono"] = $telcom;
                                        $retorno["movil"] = $celcom;
                                        $retorno["idmunicipio"] = $arrTem1["muncom"];
                                        $retorno["benley1780"] = $arrTem1["benley1780"];
                                        $retorno["ciiu1"] = $arrTem["ciius"][1];
                                        $retorno["ciiu2"] = $arrTem["ciius"][2];
                                        $retorno["ciiu3"] = $arrTem["ciius"][3];
                                        $retorno["ciiu4"] = $arrTem["ciius"][4];
                                    }
                                }
                            } else {
                                $propJurisdiccion = 'N';
                            }
                        } else {
                            $propJurisdiccion = 'N';
                        }
                        unset($arrTem1);
                    } else {
                        // 2018-02-09: JINT: Se adiciona control para determinar si el propietario esta o no en la jurisdicción
                        $propJurisdiccion = 'N';
                        if (trim($arrTem["propietarios"][1]["municipiopropietario"]) != '') {
                            $temx1 = retornarRegistroMysqliApi($mysqli, 'mreg_municipiosjurisdiccion', "idcodigo='" . $arrTem["propietarios"][1]["municipiopropietario"] . "'");
                            if ($temx1 && !empty($temx1)) {
                                $propJurisdiccion = 'S';
                            }
                        }
                    }
                } else {
                    $propJurisdiccion = 'N';
                }
            } else {
                $propJurisdiccion = 'N';
                if (count($arrTem["propietarios"]) > 1) {
                    if ($arrTem["propietarios"][1]["camarapropietario"] == CODIGO_EMPRESA || ltrim($arrTem["propietarios"][1]["camarapropietario"], "0") == '') {
                        $propJurisdiccion = 'S';
                    }
                }
            }
        }

        // 2018-03-01: JINT: Rutina para localizar si el propietario está dentro o fuera de la jurisdiccion
        // Cuando se trate de renovar solo el establecimiento
        if ($arrTem["organizacion"] == '02' && $procesartodas == 'N') {
            $propJurisdiccion = 'N';
            $props = retornarRegistrosMysqliApi($mysqli, "mreg_est_propietarios", "matricula='" . $mat . "'", "id");
            foreach ($props as $p) {
                if ($p["estado"] == 'V') {
                    if ($p["matriculapropietario"] != '') {
                        if ($p["codigocamara"] == '' || $p["codigocamara"] == CODIGO_EMPRESA) {
                            $propJurisdiccion = 'S';
                        }
                    } else {
                        if ($p["muncom"] != '') {
                            if (retornarRegistroMysqliApi($mysqli, "mreg_municipiosjurisdiccion", "idcodigo='" . $p["muncom"] . "'")) {
                                $propJurisdiccion = 'S';
                            }
                        }
                    }
                }
            }
        }

        // 2017-07-24: JINT: Para determinar si el propietario está dentro o fuera de la jurisdiccion
        // cuanto se trate de sucursales y agencias
        if ($arrTem["organizacion"] > '02' && ($arrTem["categoria"] == '2' || $arrTem["categoria"] == '3')) {
            $propJurisdiccion = 'S';
            if ($arrTem["cpcodcam"] != '00' && $arrTem["cpcodcam"] != CODIGO_EMPRESA) {
                $propJurisdiccion = 'N';
            }
        }


        //
        $totalMatriculas = 0;
        $i = -1;
        $con = 0;
        if ($procesartodas != 'SP' && $procesartodas != 'E' && $procesartodas != 'X') {
            $i++;
            $totalMatriculas++;
            if ($i < $iCuantas) {
                $retorno["matriculasunicas"][$i]["idtipoidentificacion"] = $arrTem["tipoidentificacion"];
                $retorno["matriculasunicas"][$i]["identificacion"] = $arrTem["identificacion"];
                $retorno["matriculasunicas"][$i]["cc"] = CODIGO_EMPRESA;
                $retorno["matriculasunicas"][$i]["matricula"] = $arrTem["matricula"];
                $retorno["matriculasunicas"][$i]["nombre"] = $arrTem["nombre"];
                $retorno["matriculasunicas"][$i]["ape1"] = $arrTem["ape1"];
                $retorno["matriculasunicas"][$i]["ape2"] = $arrTem["ape2"];
                $retorno["matriculasunicas"][$i]["nom1"] = $arrTem["nom1"];
                $retorno["matriculasunicas"][$i]["nom2"] = $arrTem["nom2"];
                $retorno["matriculasunicas"][$i]["organizacion"] = $arrTem["organizacion"];
                $retorno["matriculasunicas"][$i]["categoria"] = $arrTem["categoria"];
                $retorno["matriculasunicas"][$i]["identificacionpropietario"] = '';
                $retorno["matriculasunicas"][$i]["identificacionrepresentantelegal"] = '';
                if ($arrTem["organizacion"] == '02') {
                    if (isset($arrTem["propietarios"][1]["idtipoidentificacionpropietario"])) {
                        $retorno["matriculasunicas"][$i]["identificacionpropietario"] = $arrTem["propietarios"][1]["idtipoidentificacionpropietario"];
                    }
                }
                if ($arrTem["organizacion"] > '02') {
                    if (isset($arrTem["replegal"][1]["identificacionreplegal"])) {
                        $retorno["matriculasunicas"][$i]["identificacionrepresentantelegal"] = $arrTem["replegal"][1]["identificacionreplegal"];
                    }
                }
                $retorno["matriculasunicas"][$i]["ultimoanorenovado"] = $arrTem["ultanoren"];
                if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {
                    $retorno["matriculasunicas"][$i]["ultimosactivos"] = $arrTem["acttot"];
                } else {
                    $retorno["matriculasunicas"][$i]["ultimosactivos"] = $arrTem["actvin"];
                }
                $retorno["matriculasunicas"][$i]["afiliado"] = $arrTem["afiliado"];
                $retorno["matriculasunicas"][$i]["ultimoanoafiliado"] = $arrTem["ultanorenafi"];
                $retorno["matriculasunicas"][$i]["propietariojurisdiccion"] = $propJurisdiccion;
                $retorno["matriculasunicas"][$i]["disolucion"] = '';
                if ($arrTem["disueltaporvencimiento"] == 'si' || $arrTem["disueltaporacto510"] == 'si') {
                    $retorno["matriculasunicas"][$i]["disolucion"] = 'S';
                }
                $retorno["matriculasunicas"][$i]["fechadisolucion"] = $arrTem["fechadisolucion"];
                $retorno["matriculasunicas"][$i]["fechanacimiento"] = $arrTem["fechanacimiento"];
                $retorno["matriculasunicas"][$i]["fechamatricula"] = $arrTem["fechamatricula"];
                $retorno["matriculasunicas"][$i]["fecmatant"] = $arrTem["fecmatant"];
                $retorno["matriculasunicas"][$i]["fecharenovacion"] = $arrTem["fecharenovacion"];
                $retorno["matriculasunicas"][$i]["benart7"] = $arrTem["art7"];
                $retorno["matriculasunicas"][$i]["benley1780"] = $arrTem["benley1780"];
                $retorno["matriculasunicas"][$i]["circular19"] = '';
                $retorno["matriculasunicas"][$i]["municipio"] = $arrTem["muncom"];
                $retorno["matriculasunicas"][$i]["clasegenesadl"] = $arrTem["clasegenesadl"];
                $retorno["matriculasunicas"][$i]["claseespesadl"] = $arrTem["claseespesadl"];
                $retorno["matriculasunicas"][$i]["econsoli"] = $arrTem["claseeconsoli"];
                $retorno["matriculasunicas"][$i]["expedienteinactivo"] = '';
                if ($arrTem["estadomatricula"] == 'MI' || $arrTem["estadomatricula"] == 'II') {
                    $retorno["matriculasunicas"][$i]["expedienteinactivo"] = 'S';
                }
                $retorno["matriculasunicas"][$i]["dircom"] = $arrTem["dircom"];
                $retorno["matriculasunicas"][$i]["emailcom"] = $arrTem["emailcom"];
                $retorno["matriculasunicas"][$i]["telcom1"] = $arrTem["telcom1"];
                $retorno["matriculasunicas"][$i]["telcom3"] = $arrTem["celcom"];
                $retorno["matriculasunicas"][$i]["multadoponal"] = '';
                $retorno["matriculasunicas"][$i]["ciiu1"] = $arrTem["ciius"][1];
                $retorno["matriculasunicas"][$i]["ciiu2"] = $arrTem["ciius"][2];
                $retorno["matriculasunicas"][$i]["ciiu3"] = $arrTem["ciius"][3];
                $retorno["matriculasunicas"][$i]["ciiu4"] = $arrTem["ciius"][4];

                for ($iano = $arrTem["ultanoren"] + 1; $iano <= date("Y"); $iano++) {
                    $con++;
                    $feinicio045 = '';
                    $decreto045 = 'no';
                    if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
                        $feinicio045 = '99999999';
                    } else {
                        $feinicio045 = FECHA_INICIO_DECRETO_045;
                    }
                    if ($iano . '0101' >= $feinicio045) {
                        $decreto045 = 'si';
                    }
                    $retorno["matriculas"][$con]["idtipoidentificacion"] = $arrTem["tipoidentificacion"];
                    $retorno["matriculas"][$con]["identificacion"] = $arrTem["identificacion"];
                    $retorno["matriculas"][$con]["cc"] = CODIGO_EMPRESA;
                    $retorno["matriculas"][$con]["matricula"] = $arrTem["matricula"];
                    $retorno["matriculas"][$con]["nombre"] = $arrTem["nombre"];
                    $retorno["matriculas"][$con]["ape1"] = $arrTem["ape1"];
                    $retorno["matriculas"][$con]["ape2"] = $arrTem["ape2"];
                    $retorno["matriculas"][$con]["nom1"] = $arrTem["nom1"];
                    $retorno["matriculas"][$con]["nom2"] = $arrTem["nom2"];
                    $retorno["matriculas"][$con]["organizacion"] = $arrTem["organizacion"];
                    $retorno["matriculas"][$con]["categoria"] = $arrTem["categoria"];
                    $retorno["matriculas"][$con]["ultimoanorenovado"] = $arrTem["ultanoren"];
                    if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {
                        $retorno["matriculas"][$con]["ultimosactivos"] = $arrTem["acttot"];
                        $retorno["matriculas"][$con]["nombreactivos"] = 'Activos del propietario';
                    } else {
                        $retorno["matriculas"][$con]["ultimosactivos"] = $arrTem["actvin"];
                        if ($decreto045 == 'si') {
                            $retorno["matriculas"][$con]["nombreactivos"] = 'Activos del propietario';
                        } else {
                            $retorno["matriculas"][$con]["nombreactivos"] = 'Activos del expediente';
                        }
                    }
                    $retorno["matriculas"][$con]["anoarenovar"] = $iano;
                    $retorno["matriculas"][$con]["afiliado"] = $arrTem["afiliado"];
                    $retorno["matriculas"][$con]["ultimoanoafiliado"] = $arrTem["ultanorenafi"];
                    $retorno["matriculas"][$con]["propietariojurisdiccion"] = $propJurisdiccion;
                    $retorno["matriculas"][$con]["disolucion"] = '';
                    if ($arrTem["disueltaporvencimiento"] == 'si' || $arrTem["disueltaporacto510"] == 'si') {
                        $retorno["matriculas"][$i]["disolucion"] = 'S';
                    }
                    $retorno["matriculas"][$con]["fechadisolucion"] = $arrTem["fechadisolucion"];
                    $retorno["matriculas"][$con]["fechanacimiento"] = $arrTem["fechanacimiento"];
                    $retorno["matriculas"][$con]["fechamatricula"] = $arrTem["fechamatricula"];
                    $retorno["matriculas"][$con]["fecmatant"] = $arrTem["fecmatant"];
                    $retorno["matriculas"][$con]["fecharenovacion"] = $arrTem["fecharenovacion"];
                    $retorno["matriculas"][$con]["benart7"] = $arrTem["art7"];
                    $retorno["matriculas"][$con]["benley1780"] = $arrTem["benley1780"];
                    $retorno["matriculas"][$con]["circular19"] = '';
                    $retorno["matriculas"][$con]["municipio"] = $arrTem["muncom"];
                    $retorno["matriculas"][$con]["clasegenesadl"] = $arrTem["clasegenesadl"];
                    $retorno["matriculas"][$con]["claseespesadl"] = $arrTem["claseespesadl"];
                    $retorno["matriculas"][$con]["econsoli"] = $arrTem["claseeconsoli"];
                    $retorno["matriculas"][$con]["expedienteinactivo"] = '';
                    if ($arrTem["estadomatricula"] == 'MI' || $arrTem["estadomatricula"] == 'II') {
                        $retorno["matriculas"][$con]["expedienteinactivo"] = 'S';
                    }
                    $retorno["matriculas"][$con]["dircom"] = $arrTem["dircom"];
                    $retorno["matriculas"][$con]["emailcom"] = $arrTem["emailcom"];
                    $retorno["matriculas"][$con]["telcom1"] = $arrTem["telcom1"];
                    $retorno["matriculas"][$con]["telcom3"] = $arrTem["celcom"];
                    $retorno["matriculas"][$con]["multadoponal"] = '';
                    $retorno["matriculas"][$con]["ciiu1"] = $arrTem["ciius"][1];
                    $retorno["matriculas"][$con]["ciiu2"] = $arrTem["ciius"][2];
                    $retorno["matriculas"][$con]["ciiu3"] = $arrTem["ciius"][3];
                    $retorno["matriculas"][$con]["ciiu4"] = $arrTem["ciius"][4];
                }
            }
        }

        if ($procesartodas == 'L' || $procesartodas == 'S' || $procesartodas == 'SP' || $procesartodas == 'E' || $procesartodas == 'X') {
            if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {
                foreach ($arrTem["establecimientos"] as $est) {
                    if ($est["ultanoren"] < date("Y")) {
                        $i++;
                        $totalMatriculas++;
                        if ($i < $iCuantas) {
                            $retorno["matriculasunicas"][$i]["idtipoidentificacion"] = '';
                            $retorno["matriculasunicas"][$i]["identificacion"] = '';
                            $retorno["matriculasunicas"][$i]["cc"] = CODIGO_EMPRESA;
                            $retorno["matriculasunicas"][$i]["matricula"] = $est["matriculaestablecimiento"];
                            $retorno["matriculasunicas"][$i]["nombre"] = $est["nombreestablecimiento"];
                            $retorno["matriculasunicas"][$i]["ape1"] = '';
                            $retorno["matriculasunicas"][$i]["ape2"] = '';
                            $retorno["matriculasunicas"][$i]["nom1"] = '';
                            $retorno["matriculasunicas"][$i]["nom2"] = '';
                            $retorno["matriculasunicas"][$i]["organizacion"] = '02';
                            $retorno["matriculasunicas"][$i]["categoria"] = '';
                            $retorno["matriculasunicas"][$i]["identificacionpropietario"] = $arrTem["identificacion"];
                            $retorno["matriculasunicas"][$i]["identificacionrepresentantelegal"] = '';
                            $retorno["matriculasunicas"][$i]["ultimoanorenovado"] = $est["ultanoren"];
                            $retorno["matriculasunicas"][$i]["ultimosactivos"] = $est["actvin"];
                            $retorno["matriculasunicas"][$i]["afiliado"] = '';
                            $retorno["matriculasunicas"][$i]["ultimoanoafiliado"] = '';
                            $retorno["matriculasunicas"][$i]["propietariojurisdiccion"] = $propJurisdiccion;
                            $retorno["matriculasunicas"][$i]["disolucion"] = '';
                            $retorno["matriculasunicas"][$i]["fechadisolucion"] = '';
                            $retorno["matriculasunicas"][$i]["fechamatricula"] = $est["fechamatricula"];
                            $retorno["matriculasunicas"][$i]["fecmatant"] = '';
                            $retorno["matriculasunicas"][$i]["fecharenovacion"] = $est["fecharenovacion"];
                            $retorno["matriculasunicas"][$i]["benart7"] = '';
                            $retorno["matriculasunicas"][$i]["benley1780"] = '';
                            $retorno["matriculasunicas"][$i]["circular19"] = '';
                            $retorno["matriculasunicas"][$i]["municipio"] = $est["muncom"];
                            $retorno["matriculasunicas"][$i]["clasegenesadl"] = '';
                            $retorno["matriculasunicas"][$i]["claseespesadl"] = '';
                            $retorno["matriculasunicas"][$i]["econsoli"] = '';
                            $retorno["matriculasunicas"][$i]["expedienteinactivo"] = '';
                            if ($est["estadodatosestablecimiento"] == 'MI') {
                                $retorno["matriculasunicas"][$i]["expedienteinactivo"] = 'S';
                            }
                            $retorno["matriculasunicas"][$i]["dircom"] = $est["dircom"];
                            $retorno["matriculasunicas"][$i]["emailcom"] = $est["emailcom"];
                            $retorno["matriculasunicas"][$i]["telcom1"] = $est["telcom1"];
                            $retorno["matriculasunicas"][$i]["telcom2"] = $est["telcom2"];
                            $retorno["matriculasunicas"][$i]["telcom3"] = $est["telcom3"];
                            $retorno["matriculasunicas"][$i]["multadoponal"] = '';
                            $retorno["matriculasunicas"][$i]["ciiu1"] = $est["ciiu1"];
                            $retorno["matriculasunicas"][$i]["ciiu2"] = $est["ciiu2"];
                            $retorno["matriculasunicas"][$i]["ciiu3"] = $est["ciiu3"];
                            $retorno["matriculasunicas"][$i]["ciiu4"] = $est["ciiu4"];

                            for ($iano = $arrTem["ultanoren"] + 1; $iano <= date("Y"); $iano++) {
                                $con++;
                                $feinicio045 = '';
                                $decreto045 = 'no';
                                if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
                                    $feinicio045 = '99999999';
                                } else {
                                    $feinicio045 = FECHA_INICIO_DECRETO_045;
                                }
                                if ($iano . '0101' >= $feinicio045) {
                                    $decreto045 = 'si';
                                }
                                $retorno["matriculas"][$con]["idtipoidentificacion"] = '';
                                $retorno["matriculas"][$con]["identificacion"] = '';
                                $retorno["matriculas"][$con]["cc"] = CODIGO_EMPRESA;
                                $retorno["matriculas"][$con]["matricula"] = $est["matriculaestablecimiento"];
                                $retorno["matriculas"][$con]["nombre"] = $est["nombreestablecimiento"];
                                $retorno["matriculas"][$con]["ape1"] = '';
                                $retorno["matriculas"][$con]["ape2"] = '';
                                $retorno["matriculas"][$con]["nom1"] = '';
                                $retorno["matriculas"][$con]["nom2"] = '';
                                $retorno["matriculas"][$con]["organizacion"] = '02';
                                $retorno["matriculas"][$con]["categoria"] = '';
                                $retorno["matriculas"][$con]["identificacionpropietario"] = $arrTem["identificacion"];
                                $retorno["matriculas"][$con]["identificacionrepresentantelegal"] = '';
                                $retorno["matriculas"][$con]["ultimoanorenovado"] = $est["ultanoren"];
                                $retorno["matriculas"][$con]["ultimosactivos"] = $est["actvin"];
                                if ($decreto045 == 'si') {
                                    $retorno["matriculas"][$con]["nombreactivos"] = 'Activos del propietario';
                                } else {
                                    $retorno["matriculas"][$con]["nombreactivos"] = 'Activos del expediente';
                                }
                                $retorno["matriculas"][$con]["anoarenovar"] = $iano;
                                $retorno["matriculas"][$con]["afiliado"] = '';
                                $retorno["matriculas"][$con]["ultimoanoafiliado"] = '';
                                $retorno["matriculas"][$con]["propietariojurisdiccion"] = $propJurisdiccion;
                                $retorno["matriculas"][$con]["disolucion"] = '';
                                $retorno["matriculas"][$con]["fechadisolucion"] = '';
                                $retorno["matriculas"][$con]["fechamatricula"] = $est["fechamatricula"];
                                $retorno["matriculas"][$con]["fecmatant"] = '';
                                $retorno["matriculas"][$con]["fecharenovacion"] = $est["fecharenovacion"];
                                $retorno["matriculas"][$con]["benart7"] = '';
                                $retorno["matriculas"][$con]["benley1780"] = '';
                                $retorno["matriculas"][$con]["circular19"] = '';
                                $retorno["matriculas"][$con]["municipio"] = $est["muncom"];
                                $retorno["matriculas"][$con]["clasegenesadl"] = '';
                                $retorno["matriculas"][$con]["claseespesadl"] = '';
                                $retorno["matriculas"][$con]["econsoli"] = '';
                                $retorno["matriculas"][$con]["expedienteinactivo"] = '';
                                if ($est["estadodatosestablecimiento"] == 'MI') {
                                    $retorno["matriculas"][$con]["expedienteinactivo"] = 'S';
                                }
                                $retorno["matriculas"][$con]["dircom"] = $est["dircom"];
                                $retorno["matriculas"][$con]["emailcom"] = $est["emailcom"];
                                $retorno["matriculas"][$con]["telcom1"] = $est["telcom1"];
                                $retorno["matriculas"][$con]["telcom2"] = $est["telcom2"];
                                $retorno["matriculas"][$con]["telcom3"] = $est["telcom3"];
                                $retorno["matriculas"][$con]["multadoponal"] = '';
                                $retorno["matriculas"][$con]["ciiu1"] = $est["ciiu1"];
                                $retorno["matriculas"][$con]["ciiu2"] = $est["ciiu2"];
                                $retorno["matriculas"][$con]["ciiu3"] = $est["ciiu3"];
                                $retorno["matriculas"][$con]["ciiu4"] = $est["ciiu4"];
                            }
                        }
                    }
                }

                foreach ($arrTem["sucursalesagencias"] as $est) {
                    if ($est["estado"] == 'MA' || $est["estado"] == 'MI') {
                        if ($est["ultanoren"] < date("Y")) {
                            $i++;
                            $totalMatriculas++;
                            if ($i < $iCuantas) {
                                $retorno["matriculasunicas"][$i]["idtipoidentificacion"] = '';
                                $retorno["matriculasunicas"][$i]["identificacion"] = '';
                                $retorno["matriculasunicas"][$i]["cc"] = CODIGO_EMPRESA;
                                $retorno["matriculasunicas"][$i]["matricula"] = $est["matriculasucage"];
                                $retorno["matriculasunicas"][$i]["nombre"] = $est["nombresucage"];
                                $retorno["matriculasunicas"][$i]["ape1"] = '';
                                $retorno["matriculasunicas"][$i]["ape2"] = '';
                                $retorno["matriculasunicas"][$i]["nom1"] = '';
                                $retorno["matriculasunicas"][$i]["nom2"] = '';
                                $retorno["matriculasunicas"][$i]["organizacion"] = $arrTem["organizacion"];
                                $retorno["matriculasunicas"][$i]["categoria"] = $est["categoria"];
                                $retorno["matriculasunicas"][$i]["identificacionpropietario"] = $arrTem["identificacion"];
                                $retorno["matriculasunicas"][$i]["identificacionrepresentantelegal"] = '';
                                $retorno["matriculasunicas"][$i]["ultimoanorenovado"] = $est["ultanoren"];
                                $retorno["matriculasunicas"][$i]["ultimosactivos"] = $est["actvin"];
                                $retorno["matriculasunicas"][$i]["afiliado"] = '';
                                $retorno["matriculasunicas"][$i]["ultimoanoafiliado"] = '';
                                $retorno["matriculasunicas"][$i]["propietariojurisdiccion"] = 'S';
                                $retorno["matriculasunicas"][$i]["disolucion"] = '';
                                $retorno["matriculasunicas"][$i]["fechadisolucion"] = '';
                                $retorno["matriculasunicas"][$i]["fechamatricula"] = $est["fechamatricula"];
                                $retorno["matriculasunicas"][$i]["fecmatant"] = '';
                                $retorno["matriculasunicas"][$i]["fecharenovacion"] = $est["fecharenovacion"];
                                $retorno["matriculasunicas"][$i]["benart7"] = '';
                                $retorno["matriculasunicas"][$i]["benley1780"] = '';
                                $retorno["matriculasunicas"][$i]["circular19"] = '';
                                $retorno["matriculasunicas"][$i]["municipio"] = $est["muncom"];
                                $retorno["matriculasunicas"][$i]["clasegenesadl"] = '';
                                $retorno["matriculasunicas"][$i]["claseespesadl"] = '';
                                $retorno["matriculasunicas"][$i]["econsoli"] = '';
                                $retorno["matriculasunicas"][$i]["expedienteinactivo"] = '';
                                if ($est["estado"] == 'MI') {
                                    $retorno["matriculasunicas"][$i]["expedienteinactivo"] = 'S';
                                }
                                $retorno["matriculasunicas"][$i]["dircom"] = $est["dircom"];
                                $retorno["matriculasunicas"][$i]["emailcom"] = $est["emailcom"];
                                $retorno["matriculasunicas"][$i]["telcom1"] = $est["telcom1"];
                                $retorno["matriculasunicas"][$i]["telcom2"] = $est["telcom2"];
                                $retorno["matriculasunicas"][$i]["telcom3"] = $est["telcom3"];
                                $retorno["matriculasunicas"][$i]["multadoponal"] = '';
                                $retorno["matriculasunicas"][$i]["ciiu1"] = $est["ciiu1"];
                                $retorno["matriculasunicas"][$i]["ciiu2"] = $est["ciiu2"];
                                $retorno["matriculasunicas"][$i]["ciiu3"] = $est["ciiu3"];
                                $retorno["matriculasunicas"][$i]["ciiu4"] = $est["ciiu4"];

                                for ($iano = $arrTem["ultanoren"] + 1; $iano <= date("Y"); $iano++) {
                                    $con++;
                                    $feinicio045 = '';
                                    $decreto045 = 'no';
                                    if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
                                        $feinicio045 = '99999999';
                                    } else {
                                        $feinicio045 = FECHA_INICIO_DECRETO_045;
                                    }
                                    if ($iano . '0101' >= $feinicio045) {
                                        $decreto045 = 'si';
                                    }
                                    $retorno["matriculas"][$con]["idtipoidentificacion"] = '';
                                    $retorno["matriculas"][$con]["identificacion"] = '';
                                    $retorno["matriculas"][$con]["cc"] = CODIGO_EMPRESA;
                                    $retorno["matriculas"][$con]["matricula"] = $est["matriculasucage"];
                                    $retorno["matriculas"][$con]["nombre"] = $est["nombresucage"];
                                    $retorno["matriculas"][$con]["ape1"] = '';
                                    $retorno["matriculas"][$con]["ape2"] = '';
                                    $retorno["matriculas"][$con]["nom1"] = '';
                                    $retorno["matriculas"][$con]["nom2"] = '';
                                    $retorno["matriculas"][$con]["organizacion"] = $arrTem["organizacion"];
                                    $retorno["matriculas"][$con]["categoria"] = $est["categoria"];
                                    $retorno["matriculas"][$con]["identificacionpropietario"] = $arrTem["identificacion"];
                                    $retorno["matriculas"][$con]["identificacionrepresentantelegal"] = '';
                                    $retorno["matriculas"][$con]["ultimoanorenovado"] = $est["ultanoren"];
                                    $retorno["matriculas"][$con]["ultimosactivos"] = $est["actvin"];
                                    if ($decreto045 == 'si') {
                                        $retorno["matriculas"][$con]["nombreactivos"] = 'Activos del propietario';
                                    } else {
                                        $retorno["matriculas"][$con]["nombreactivos"] = 'Activos del expediente';
                                    }
                                    $retorno["matriculas"][$con]["anoarenovar"] = $iano;
                                    $retorno["matriculas"][$con]["afiliado"] = '';
                                    $retorno["matriculas"][$con]["ultimoanoafiliado"] = '';
                                    $retorno["matriculas"][$con]["propietariojurisdiccion"] = 'S';
                                    $retorno["matriculas"][$con]["disolucion"] = '';
                                    $retorno["matriculas"][$con]["fechadisolucion"] = '';
                                    $retorno["matriculas"][$con]["fechamatricula"] = $est["fechamatricula"];
                                    $retorno["matriculas"][$con]["fecmatant"] = '';
                                    $retorno["matriculas"][$con]["fecharenovacion"] = $est["fecharenovacion"];
                                    $retorno["matriculas"][$con]["benart7"] = '';
                                    $retorno["matriculas"][$con]["benley1780"] = '';
                                    $retorno["matriculas"][$con]["circular19"] = '';
                                    $retorno["matriculas"][$con]["municipio"] = $est["muncom"];
                                    $retorno["matriculas"][$con]["clasegenesadl"] = '';
                                    $retorno["matriculas"][$con]["claseespesadl"] = '';
                                    $retorno["matriculas"][$con]["econsoli"] = '';
                                    $retorno["matriculas"][$con]["expedienteinactivo"] = '';
                                    if ($est["estado"] == 'MI') {
                                        $retorno["matriculas"][$con]["expedienteinactivo"] = 'S';
                                    }
                                    $retorno["matriculas"][$con]["dircom"] = $est["dircom"];
                                    $retorno["matriculas"][$con]["emailcom"] = $est["emailcom"];
                                    $retorno["matriculas"][$con]["telcom1"] = $est["telcom1"];
                                    $retorno["matriculas"][$con]["telcom2"] = $est["telcom2"];
                                    $retorno["matriculas"][$con]["telcom3"] = $est["telcom3"];
                                    $retorno["matriculas"][$con]["multadoponal"] = '';
                                    $retorno["matriculas"][$con]["ciiu1"] = $est["ciiu1"];
                                    $retorno["matriculas"][$con]["ciiu2"] = $est["ciiu2"];
                                    $retorno["matriculas"][$con]["ciiu3"] = $est["ciiu3"];
                                    $retorno["matriculas"][$con]["ciiu4"] = $est["ciiu4"];
                                }
                            }
                        }
                    }
                }


                if (!defined('RENOVACION_ACTIVAR_NACIONALES')) {
                    define('RENOVACION_ACTIVAR_NACIONALES', 'N');
                }
                if (($procesartodas == 'S' || $procesartodas == 'X') && RENOVACION_ACTIVAR_NACIONALES == 'S') {
                    $inat = 0;
                    foreach ($arrTem["establecimientosnacionales"] as $est) {
                        if ($est["ultanoren"] < date("Y")) {
                            $siren = 'si';
                            $esx = retornarRegistrosMysqliApi($mysqli, "mreg_establecimientos_nacionales", "cc='" . $est["cc"] . "' and matricula='" . $est["matriculaestablecimiento"] . "'", "id");
                            if ($esx && !empty($esx)) {
                                foreach ($esx as $ex) {
                                    if ($ex["numerorecibo"] != '' && substr($ex["fecharecibo"], 0, 4) == date("Y")) {
                                        $siren = 'no';
                                    }
                                }
                            }
                            if ($siren == 'si') {
                                $i++;
                                $inat++;
                                $totalMatriculas++;
                                if ($i < $iCuantas) {
                                    for ($iano = $est["ultanoren"] + 1; $iano <= date("Y"); $iano++) {
                                        $con++;
                                        $feinicio045 = '';
                                        $decreto045 = 'no';
                                        if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
                                            $feinicio045 = '99999999';
                                        } else {
                                            $feinicio045 = FECHA_INICIO_DECRETO_045;
                                        }
                                        if ($iano . '0101' >= $feinicio045) {
                                            $decreto045 = 'si';
                                        }
                                        $retorno["matriculas"][$con]["idtipoidentificacion"] = '';
                                        $retorno["matriculas"][$con]["identificacion"] = '';
                                        $retorno["matriculas"][$con]["cc"] = $est["cc"];
                                        $retorno["matriculas"][$con]["matricula"] = $est["matriculaestablecimiento"];
                                        $retorno["matriculas"][$con]["nombre"] = $est["nombreestablecimiento"];
                                        $retorno["matriculas"][$con]["ape1"] = '';
                                        $retorno["matriculas"][$con]["ape2"] = '';
                                        $retorno["matriculas"][$con]["nom1"] = '';
                                        $retorno["matriculas"][$con]["nom2"] = '';
                                        $retorno["matriculas"][$con]["organizacion"] = $est["organizacion"];
                                        $retorno["matriculas"][$con]["categoria"] = $est["categoria"];
                                        $retorno["matriculas"][$con]["identificacionpropietario"] = $arrTem["identificacion"];
                                        $retorno["matriculas"][$con]["identificacionrepresentantelegal"] = '';
                                        $retorno["matriculas"][$con]["ultimoanorenovado"] = $est["ultanoren"];
                                        $retorno["matriculas"][$con]["ultimosactivos"] = $est["actvin"];
                                        if ($decreto045 == 'si') {
                                            $retorno["matriculas"][$con]["nombreactivos"] = 'Activos del propietario';
                                        } else {
                                            $retorno["matriculas"][$con]["nombreactivos"] = 'Activos del expediente';
                                        }
                                        $retorno["matriculas"][$con]["anoarenovar"] = $iano;
                                        $retorno["matriculas"][$con]["afiliado"] = '';
                                        $retorno["matriculas"][$con]["ultimoanoafiliado"] = '';
                                        $retorno["matriculas"][$con]["propietariojurisdiccion"] = 'N';
                                        $retorno["matriculas"][$con]["disolucion"] = '';
                                        $retorno["matriculas"][$con]["fechadisolucion"] = '';
                                        $retorno["matriculas"][$con]["fechamatricula"] = $est["fechamatricula"];
                                        $retorno["matriculas"][$con]["fecmatant"] = '';
                                        $retorno["matriculas"][$con]["fecharenovacion"] = $est["fecharenovacion"];
                                        $retorno["matriculas"][$con]["benart7"] = '';
                                        $retorno["matriculas"][$con]["benley1780"] = '';
                                        $retorno["matriculas"][$con]["circular19"] = '';
                                        $retorno["matriculas"][$con]["municipio"] = $est["muncom"];
                                        $retorno["matriculas"][$con]["clasegenesadl"] = '';
                                        $retorno["matriculas"][$con]["claseespesadl"] = '';
                                        $retorno["matriculas"][$con]["econsoli"] = '';
                                        $retorno["matriculas"][$con]["expedienteinactivo"] = '';
                                        if ($est["estadomatricula"] == 'MI') {
                                            $retorno["matriculas"][$con]["expedienteinactivo"] = 'S';
                                        }
                                        $retorno["matriculas"][$con]["dircom"] = $est["dircom"];
                                        $retorno["matriculas"][$con]["emailcom"] = $est["emailcom"];
                                        $retorno["matriculas"][$con]["telcom1"] = $est["telcom1"];
                                        $retorno["matriculas"][$con]["telcom2"] = $est["telcom2"];
                                        $retorno["matriculas"][$con]["telcom3"] = $est["telcom3"];
                                        $retorno["matriculas"][$con]["multadoponal"] = '';
                                        $retorno["matriculas"][$con]["ciiu1"] = $est["ciiu1"];
                                        $retorno["matriculas"][$con]["ciiu2"] = $est["ciiu2"];
                                        $retorno["matriculas"][$con]["ciiu3"] = $est["ciiu3"];
                                        $retorno["matriculas"][$con]["ciiu4"] = $est["ciiu4"];
                                    }

                                    // 2017-02-25: JINT: En caso de liquidación crea mreg_establecimientos_nacionales

                                    if ($idliq != 0) {
                                        if ($inat == 1) {
                                            borrarRegistrosMysqliApi($mysqli, 'mreg_establecimientos_nacionales', "idliquidacion=" . $idliq);
                                        }
                                        $arrCampos = array(
                                            'idliquidacion',
                                            'cc',
                                            'matricula',
                                            'razonsocial',
                                            'organizacion',
                                            'categoria',
                                            'estado',
                                            'fechamatricula',
                                            'fecharenovacion',
                                            'ultanoren',
                                            'dircom',
                                            'barriocom',
                                            'telcom1',
                                            'telcom2',
                                            'telcom3',
                                            'muncom',
                                            'emailcom',
                                            'ctrubi',
                                            'zonapostalcom',
                                            'dirnot',
                                            'barrionot',
                                            'telnot1',
                                            'munnot',
                                            'emailnot',
                                            'zonapostalnot',
                                            'tipolocal',
                                            'tipopropietario',
                                            'afiliado',
                                            'desactiv',
                                            'ciiu1',
                                            'shd1',
                                            'ciiu2',
                                            'shd2',
                                            'ciiu3',
                                            'shd3',
                                            'ciiu4',
                                            'shd4',
                                            'personal',
                                            'actvin'
                                        );
                                        $arrValores = array(
                                            $idliq,
                                            "'" . $est["cc"] . "'",
                                            "'" . ltrim($est["matriculaestablecimiento"], "0") . "'",
                                            "'" . addslashes($est["nombreestablecimiento"]) . "'",
                                            "'" . ($est["organizacion"]) . "'",
                                            "'" . ($est["categoria"]) . "'",
                                            "'" . ($est["estadomatricula"]) . "'",
                                            "'" . ($est["fechamatricula"]) . "'",
                                            "'" . ($est["fecharenovacion"]) . "'",
                                            "'" . ($est["ultanoren"]) . "'",
                                            "'" . addslashes($est["dircom"]) . "'",
                                            "'" . addslashes($est["nbarriocom"]) . "'",
                                            "'" . ($est["telcom1"]) . "'",
                                            "'" . ($est["telcom2"]) . "'",
                                            "'" . ($est["telcom3"]) . "'",
                                            "'" . ($est["muncom"]) . "'",
                                            "'" . addslashes($est["emailcom"]) . "'",
                                            "'" . ($est["ctrubi"]) . "'",
                                            "'" . ($est["codpostalcom"]) . "'",
                                            "'" . addslashes($est["dirnot"]) . "'",
                                            "'" . addslashes($est["nbarrionot"]) . "'",
                                            "''",
                                            "'" . ($est["munnot"]) . "'",
                                            "'" . addslashes($est["emailnot"]) . "'",
                                            "'" . ($est["codpostalnot"]) . "'",
                                            "'" . ($est["tipolocal"]) . "'",
                                            "'" . ($est["tipopropietario"]) . "'",
                                            "'" . ($est["afiliado"]) . "'",
                                            "'" . addslashes($est["desactiv"]) . "'",
                                            "'" . ($est["ciiu1"]) . "'",
                                            "'" . ($est["shd1"]) . "'",
                                            "'" . ($est["ciiu2"]) . "'",
                                            "'" . ($est["shd2"]) . "'",
                                            "'" . ($est["ciiu3"]) . "'",
                                            "'" . ($est["shd3"]) . "'",
                                            "'" . ($est["ciiu4"]) . "'",
                                            "'" . ($est["shd4"]) . "'",
                                            intval($est["personal"]),
                                            doubleval($est["actvin"])
                                        );
                                        $res = insertarRegistrosMysqliApi($mysqli, 'mreg_establecimientos_nacionales', $arrCampos, $arrValores);
                                        if ($res == false) {
                                            \logApi::general2($nameLog, '', 'Error grabado establecimientos nacionales ' . $_SESSION["generales"]["mensajeerror"]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }


        // Aplica para el 2019
        $anoren = date("Y");
        $anorenant = $anoren - 1;
        if ($retorno["matriculas"][0]["organizacion"] == '01' || ($retorno["matriculas"][0]["organizacion"] > '02' && $retorno["matriculas"][0]["categoria"] == '1')) {
            if ($retorno["matriculas"][0]["fechamatricula"] >= $anorenant . '0101') {
                if ($retorno["matriculas"][0]["benley1780"] == '') {
                    if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matriculas"][0]["matricula"] . "' and servicio='01090110' and (fecoperacion between '" . $anorenant . "0101' and '" . $anorenant . "1231')") > 0) {
                        $retorno["matriculas"][0]["benley1780"] = 'S';
                        $retorno["matriculas"]["benley1780"] = 'S';
                    }
                }
            }
        }

        // Verifica codigo de policia
        if (!defined('ACTIVAR_CONTROL_MULTAS_PONAL')) {
            define('ACTIVAR_CONTROL_MULTAS_PONAL', 'NO');
        }

        if ($controlmultas == '') {
            $controlmultas = ACTIVAR_CONTROL_MULTAS_PONAL;
        }

        // 2018-03-14: JINT: En caso de no estar habilitado el control de multas, valida localmente en la tabla
        // mreg_cruce_ponal_multas
        if (ACTIVAR_CONTROL_MULTAS_PONAL == 'LOCAL') {
            $retorno["matriculas"][0]["multadoponal"] = 'N';
            $retorno["multadoponal"] = 'N';
            $retorno["mensajeError"] = '';
            $mulx = retornarRegistroMysqliApi($mysqli, 'mreg_cruce_ponal_multas', "identificacion='" . $retorno["matriculas"][0]["identificacion"] . "'");
            if ($mulx && !empty($mulx)) {
                if ($mulx["multavencida"] == 'SI') {
                    if ($_SESSION["generales"]["tipousuario"] != '00') {
                        $retorno["matriculas"][0]["multadoponal"] = 'L';
                        $retorno["multadoponal"] = 'L';
                        $retorno["codigoError"] = '4000';
                        $retorno["mensajeError"] = 'Se ha encontrado (en la BD local) que la identificaci&oacute;n ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeError"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $retorno["mensajeError"] .= 'por la Polic&iacute;a Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s), ';
                        $retorno["mensajeError"] .= 'se sugiere revisar en la p&aacute;gina web de la Polic&iacute;a para determinar si ';
                        $retorno["mensajeError"] .= 'la misma no ha sido pagada. Recuerde dejar la evidencia de la verificaci&OACUTE;n realizada.';
                    }
                    if ($_SESSION["generales"]["tipousuario"] == '00') {
                        $retorno["matriculas"][0]["multadoponal"] = 'L';
                        $retorno["multadoponal"] = 'L';
                        $retorno["codigoError"] = '4000';
                        $retorno["mensajeError"] = 'Apreciado usuario, se ha encontrado (en la BD local) que la identificaci&oacute;n ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeError"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $retorno["mensajeError"] .= 'por la Polic&iacute;a Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s). ';
                        $retorno["mensajeError"] .= 'Para que su proceso de renovaci&oacute;n pueda hacerse en forma completa, le sugerimos ';
                        $retorno["mensajeError"] .= 'tener a la mano el soporte de pago de la misma para poder cargarlo como un soporte de la renovaci&oacute;n.';
                    }
                }
            }
            return $retorno;
        }

        if ($controlmultas == 'SI-NOBLOQUEAR') {
            if ($retorno["matriculas"][0]["organizacion"] == '01' && $procesartodas != 'SP') {
                $resx = \funcionesRegistrales::consultarMultasPolicia($mysqli, $retorno["matriculas"][0]["idtipoidentificacion"], $retorno["matriculas"][0]["identificacion"], $idliq);
                if ($resx == 'SI') {
                    $retorno["matriculas"][0]["multadoponal"] = 'S';
                    $retorno["multadoponal"] = 'S';
                } else {
                    if ($resx == 'NO') {
                        $retorno["matriculas"][0]["multadoponal"] = 'N';
                        $retorno["multadoponal"] = 'N';
                    } else {
                        $retorno["matriculas"][0]["multadoponal"] = 'E';
                        $retorno["multadoponal"] = 'E';
                    }
                }
            }
        }

        if ($controlmultas == 'SI-BLOQUEAR') {
            if ($retorno["matriculas"][0]["organizacion"] == '01' && $procesartodas != 'SP') {
                $resx = \funcionesRegistrales::consultarMultasPolicia($mysqli, $retorno["matriculas"][0]["idtipoidentificacion"], $retorno["matriculas"][0]["identificacion"], $idliq);
                if ($resx == 'SI') {
                    if (isset($retorno["matriculas"][1])) {
                        $retorno["codigoError"] = '5000';
                        $retorno["mensajeError"] = 'Se ha encontrado que la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeError"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $retorno["mensajeError"] .= 'por la Policía Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s) - ';
                        $retorno["mensajeError"] .= 'No ha(n) sido pagada(s).<br><br>';
                        $retorno["mensajeError"] .= 'Para realizar la renovación de la matrícula asociada con la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeError"] .= 'se deberá(n) pagar la(s) misma(s) y esperar a que sea(n) registrada(s) en el ';
                        $retorno["mensajeError"] .= 'Sistema Nacional de Medidas Correctiva.<br><br>';
                        $retorno["mensajeError"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/verMultas.php?idliquidacion=' . $idliq . '" target="_blank">Ver Las multas</a><br><br>';
                        $retorno["mensajeError"] .= 'Si desea continuar con el proceso de renovación de las matrículas asociadas ';
                        $retorno["mensajeError"] .= 'a la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' oprima el ';
                        $retorno["mensajeError"] .= 'siguiente enlace<br><br>';
                        $retorno["mensajeError"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregRenovacionMatricula.php?accion=recuperarmatriculasrenovar&procesartodas=SP&identificacionbase=' . $retorno["matriculas"][0]["identificacion"] . '">Renovar establecimientos sucursales y agencias</a><br><br>';
                        $retorno["matriculas"] = array();
                    } else {
                        $retorno["codigoError"] = '5000';
                        $retorno["mensajeError"] = 'Se ha encontrado que la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeError"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $retorno["mensajeError"] .= 'por la Policía Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s) - ';
                        $retorno["mensajeError"] .= 'No ha(n) sido pagada(s).<br><br>';
                        $retorno["mensajeError"] .= 'Para realizar la renovación de la matrícula asociada con la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeError"] .= 'se deberá(n) pagar la(s) misma(s) y esperar a que sea(n) registrada(s) en el ';
                        $retorno["mensajeError"] .= 'Sistema Nacional de Medidas Correctiva.<br><br>';
                        $retorno["mensajeError"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/verMultas.php?idliquidacion=' . $idliq . ' target="_blank">Ver Las multas</a><br><br>';
                        $retorno["mensajeError"] .= 'No es posible continuar con el proceso de renovación. ';
                        $retorno["mensajeError"] .= 'Para volver al módulo de renovación y seleccionar otro expediente ';
                        $retorno["mensajeError"] .= 'oprima el siguiente enlace.<br><br>';
                        $retorno["mensajeError"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregRenovacionMatricula.php?accion=pantallaseleccion">Reiniciar renovación</a><br><br>';
                        $retorno["matriculas"] = array();
                    }
                } else {
                    if ($resx == 'NO') {
                        $retorno["matriculas"][0]["multadoponal"] = 'N';
                        $retorno["multadoponal"] = 'N';
                    } else {
                        $retorno["matriculas"][0]["multadoponal"] = 'E';
                        $retorno["multadoponal"] = 'E';
                    }
                }
            }
        }

        // echo $procesartodas. ' ' . $i;
        $retorno["totalmatriculas"] = $totalMatriculas;
        if ($retorno["multadoponal"] == 'E') {
            $retorno["codigoError"] = '4000';
            $retorno["mensajeError"] = 'No fue posible verificar si el comerciante tiene multas vencidas de acuerdo ';
            $retorno["mensajeError"] .= 'con lo establecido en el C&oacute;digo de Polic&iacute;a. Por favor verificar en la p&aacute;gina web de la polic&iacute;a ';
            $retorno["mensajeError"] .= 'antes de continuar con el proceso de renovaci&oacute;n.';
        }
        return $retorno;
    }

}

?>
