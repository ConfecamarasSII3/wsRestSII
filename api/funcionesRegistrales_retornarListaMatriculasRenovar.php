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
        $retorno["codigoerror"] = '0000';
        $retorno["mensajeerror"] = '';
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
                        $retorno["codigoerror"] = '4000';
                        $retorno["mensajeerror"] = 'Se ha encontrado (en la BD local) que la identificaci&oacute;n ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeerror"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $retorno["mensajeerror"] .= 'por la Polic&iacute;a Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s), ';
                        $retorno["mensajeerror"] .= 'se sugiere revisar en la p&aacute;gina web de la Polic&iacute;a para determinar si ';
                        $retorno["mensajeerror"] .= 'la misma no ha sido pagada. Recuerde dejar la evidencia de la verificaci&OACUTE;n realizada.';
                    }
                    if ($_SESSION["generales"]["tipousuario"] == '00') {
                        $retorno["matriculas"][0]["multadoponal"] = 'L';
                        $retorno["multadoponal"] = 'L';
                        $retorno["codigoerror"] = '4000';
                        $retorno["mensajeerror"] = 'Apreciado usuario, se ha encontrado (en la BD local) que la identificaci&oacute;n ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeerror"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $retorno["mensajeerror"] .= 'por la Polic&iacute;a Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s). ';
                        $retorno["mensajeerror"] .= 'Para que su proceso de renovaci&oacute;n pueda hacerse en forma completa, le sugerimos ';
                        $retorno["mensajeerror"] .= 'tener a la mano el soporte de pago de la misma para poder cargarlo como un soporte de la renovaci&oacute;n.';
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
                        $retorno["codigoerror"] = '5000';
                        $retorno["mensajeerror"] = 'Se ha encontrado que la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeerror"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $retorno["mensajeerror"] .= 'por la Policía Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s) - ';
                        $retorno["mensajeerror"] .= 'No ha(n) sido pagada(s).<br><br>';
                        $retorno["mensajeerror"] .= 'Para realizar la renovación de la matrícula asociada con la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeerror"] .= 'se deberá(n) pagar la(s) misma(s) y esperar a que sea(n) registrada(s) en el ';
                        $retorno["mensajeerror"] .= 'Sistema Nacional de Medidas Correctiva.<br><br>';
                        $retorno["mensajeerror"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/verMultas.php?idliquidacion=' . $idliq . '" target="_blank">Ver Las multas</a><br><br>';
                        $retorno["mensajeerror"] .= 'Si desea continuar con el proceso de renovación de las matrículas asociadas ';
                        $retorno["mensajeerror"] .= 'a la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' oprima el ';
                        $retorno["mensajeerror"] .= 'siguiente enlace<br><br>';
                        $retorno["mensajeerror"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregRenovacionMatricula.php?accion=recuperarmatriculasrenovar&procesartodas=SP&identificacionbase=' . $retorno["matriculas"][0]["identificacion"] . '">Renovar establecimientos sucursales y agencias</a><br><br>';
                        $retorno["matriculas"] = array();
                    } else {
                        $retorno["codigoerror"] = '5000';
                        $retorno["mensajeerror"] = 'Se ha encontrado que la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeerror"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $retorno["mensajeerror"] .= 'por la Policía Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s) - ';
                        $retorno["mensajeerror"] .= 'No ha(n) sido pagada(s).<br><br>';
                        $retorno["mensajeerror"] .= 'Para realizar la renovación de la matrícula asociada con la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeerror"] .= 'se deberá(n) pagar la(s) misma(s) y esperar a que sea(n) registrada(s) en el ';
                        $retorno["mensajeerror"] .= 'Sistema Nacional de Medidas Correctiva.<br><br>';
                        $retorno["mensajeerror"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/verMultas.php?idliquidacion=' . $idliq . ' target="_blank">Ver Las multas</a><br><br>';
                        $retorno["mensajeerror"] .= 'No es posible continuar con el proceso de renovación. ';
                        $retorno["mensajeerror"] .= 'Para volver al módulo de renovación y seleccionar otro expediente ';
                        $retorno["mensajeerror"] .= 'oprima el siguiente enlace.<br><br>';
                        $retorno["mensajeerror"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregRenovacionMatricula.php?accion=pantallaseleccion">Reiniciar renovación</a><br><br>';
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
            $retorno["codigoerror"] = '4000';
            $retorno["mensajeerror"] = 'No fue posible verificar si el comerciante tiene multas vencidas de acuerdo ';
            $retorno["mensajeerror"] .= 'con lo establecido en el C&oacute;digo de Polic&iacute;a. Por favor verificar en la p&aacute;gina web de la polic&iacute;a ';
            $retorno["mensajeerror"] .= 'antes de continuar con el proceso de renovaci&oacute;n.';
        }
        return $retorno;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $mat
     * @param type $ide
     * @param type $procesartodas
     * @param type $idliq
     * @param type $controlmultas
     * @param type $usuario
     * @return bool|string
     */
    public static function retornarListaMatriculasRenovarNuevo($mysqli, $mat = '', $ide = '', $procesartodas = 'L', $idliq = 0, $controlmultas = '', $usuario = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        $nameLog = 'retornarListaMatriculasRenovarNuevo_' . date("Ymd");

        if (!isset($_SESSION["generales"]["limiterenovacion"])) {
            $_SESSION["generales"]["limiterenovacion"] = 10;
        }
        $iCuantas = $_SESSION["generales"]["limiterenovacion"];

        //
        $arrTem = false;
        $arrTemBase = false;
        $arrTemProp = false;
        $retorno = array();
        $retorno["codigoerror"] = '0000';
        $retorno["mensajeerror"] = '';
        $retorno["propietariojurisdiccion"] = '';
        $retorno["propietariocc"] = '';
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
        $retorno["ultanoren"] = '';
        $retorno["activos"] = 0;
        $retorno["solicitarfechanacimiento"] = '';
        $retorno["solicitar1780"] = '';
        $retorno["solicitarafiliacion"] = '';
        $retorno["cantidadpropietarios"] = 0;
        $retorno["totalmatriculas"] = 0;
        $retorno["matriculas"] = array();
        $retorno["matriculasunicas"] = array();

        //
        if ($mat != '') {
            $arrTem = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $mat, '', '', '', 'si', 'N');
            $arrTemBase = $arrTem;
            if ($arrTem === false || empty($arrTem)) {
                $retorno["codigoerror"] = '9999';
                $retorno["mensajeerror"] = 'Expediente no localizado en el SII';
                return $retorno;
            }
            if ($arrTem["estadomatricula"] != 'MA' && $arrTem["estadomatricula"] != 'MI' && $arrTem["estadomatricula"] != 'IA' && $arrTem["estadomatricula"] != 'II') {
                $retorno["codigoerror"] = '9999';
                $retorno["mensajeerror"] = 'El expediente seleccionado no se encuentra activo (esta cancelado)';
                return $retorno;
            }
            if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {
                $arrTemProp = $arrTem;
                $retorno["propietariojurisdiccion"] = 'S';
                $retorno["propietariocc"] = CODIGO_EMPRESA;
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
                $retorno["ultanoren"] = $arrTem["ultanoren"];
                $retorno["activos"] = $arrTem["acttot"];
                $retorno["cantidadpropietarios"] = 1;
                if ($retorno["benley1780"] == 'S') {
                    if (substr($arrTem["fechamatricula"], 0, 4) >= date("Y") - 1) {
                        if ($retorno["ultanoren"] == date("Y") - 1) {
                            $fcorteren = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
                            if (date("Ymd") <= $fcorteren) {
                                $retorno["solicitar1780"] = 'S';
                            } else {
                                $retorno["solicitar1780"] = 'P';
                            }
                        } else {
                            if ($retorno["ultanoren"] == date("Y")) {
                                $retorno["solicitar1780"] = $retorno["benley1780"];
                            }
                        }
                    } else {
                        $retorno["solicitar1780"] = 'N';
                    }
                } else {
                    $retorno["solicitar1780"] = 'N';
                }
                if ($retorno["solicitar1780"] == 'S') {
                    if ($arrTem["organizacion"] == '01') {
                        if ($arrTem["fechanacimiento"] == '') {
                            $retorno["solicitarfechanacimiento"] = 'S';
                        }
                    }
                }
                if ($arrTem["afiliado"] == '1') {
                    if ($retorno["ultanoren"] == date("Y") - 1) {
                        if (date("md") <= '0331') {
                            $retorno["solicitarafiliacion"] = 'S';
                        } else {
                            $retorno["solicitarafiliacion"] = 'N';
                        }
                    }
                }
            }

            if ($arrTem["organizacion"] == '02') {
                $props = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $mat . "'", "id");
                if ($props === false || empty($props)) {
                    $retorno["codigoerror"] = '9999';
                    $retorno["mensajeerror"] = 'Matricula es un establecimiento sin propietario definido en el sistema de información';
                    return $retorno;
                }
                foreach ($props as $p) {
                    if ($p["estado"] == 'V') {
                        $retorno["cantidadpropietarios"]++;
                        if ($p["codigocamara"] == CODIGO_EMPRESA && $p["matriculapropietario"] != '') { // Propietario en la jurisdiccion (matriculado)
                            $arrTem = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $p["matriculapropietario"], '', '', '', 'si', 'N');
                            if ($arrTem === false || empty($arrTem)) {
                                $retorno["codigoerror"] = '9999';
                                $retorno["mensajeerror"] = 'El propietario ' . $p["matriculapropietario"] . ' no fue encontrado en el sistema de infomación.';
                                return $retorno;
                            }
                            if ($arrTem["ctrestmatricula"] == 'MC' || $arrTem["ctrestmatricula"] == 'IC' || $arrTem["ctrestmatricula"] == 'NM') { // Propietario cancelado
                                if ($retorno["propietariojurisdiccion"] == '') {
                                    $retorno["propietariojurisdiccion"] = 'NM';
                                    $retorno["idexpedientebase"] = '';
                                    $retorno["idmatriculabase"] = '';
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
                                    $retorno["benley1780"] = '';
                                    $retorno["ultanoren"] = '';
                                    $retorno["activos"] = 0;
                                }
                            } else {
                                if ($retorno["propietariojurisdiccion"] == '') {
                                    $arrTemProp = $arrTem;
                                    $retorno["propietariojurisdiccion"] = 'S';
                                    $retorno["propietariocc"] = CODIGO_EMPRESA;
                                    $retorno["idexpedientebase"] = $arrTem["matricula"];
                                    $retorno["idmatriculabase"] = $arrTem["matricula"];
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
                                    $retorno["ultanoren"] = $arrTem["ultanoren"];
                                    $retorno["activos"] = $arrTem["acttot"];
                                    if ($retorno["benley1780"] == 'S') {
                                        if (substr($retorno["fechamatricula"], 0, 4) >= date("Y") - 1) {
                                            if ($retorno["ultanoren"] == date("Y") - 1) {
                                                $fcorteren = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
                                                if (date("Ymd") <= $fcorteren) {
                                                    $retorno["solicitar1780"] = 'S';
                                                } else {
                                                    $retorno["solicitar1780"] = 'P';
                                                }
                                            } else {
                                                if ($retorno["ultanoren"] == date("Y")) {
                                                    $retorno["solicitar1780"] = $retorno["benley1780"];
                                                }
                                            }
                                        } else {
                                            $retorno["solicitar1780"] = 'N';
                                        }
                                    } else {
                                        $retorno["solicitar1780"] = 'N';
                                    }
                                    if ($retorno["solicitar1780"] == 'S') {
                                        if ($arrTem["organizacion"] == '01') {
                                            if ($arrTem["fechanacimiento"] == '') {
                                                $retorno["solicitarfechanacimiento"] = 'S';
                                            }
                                        }
                                    }
                                    if ($arrTem["afiliado"] == '1') {
                                        if ($retorno["ultanoren"] == date("Y") - 1) {
                                            if (date("md") <= '0331') {
                                                $retorno["solicitarafiliacion"] = 'S';
                                            } else {
                                                $retorno["solicitarafiliacion"] = 'N';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if ($p["codigocamara"] != CODIGO_EMPRESA && $p["matriculapropietario"] != '') { // preguntar el propietario al RUES
                            $p = \funcionesRues::consultarRegMer($p["codigocamara"], $p["matriculapropietario"]);
                            if ($p["codigo_error"] == '0000') {
                                if ($p["codigo_estado_matricula"] == '01') {
                                    if ($retorno["propietariojurisdiccion"] == '') {
                                        $retorno["propietariojurisdiccion"] = 'N';
                                        $retorno["propietariocc"] = $p["codigo_camara"];
                                        $retorno["idexpedientebase"] = ltrim($p["matricula"], "0");
                                        $retorno["idmatriculabase"] = ltrim($p["matricula"], "0");
                                        $retorno["nombrebase"] = $p["razon_social"];
                                        $retorno["nom1base"] = $p["primer_nombre"];
                                        $retorno["nom2base"] = $p["segundo_nombre"];
                                        $retorno["ape1base"] = $p["primer_apellido"];
                                        $retorno["ape2base"] = $p["segundo_apellido"];
                                        $retorno["tipoidentificacionbase"] = ltrim($p["codigo_clase_identificacion"], "0");
                                        $retorno["identificacionbase"] = ltrim($p["numero_identificacion"], "0");
                                        $retorno["organizacionbase"] = $p["codigo_organizacion_juridica"];
                                        $retorno["categoriabase"] = '1';
                                        $retorno["afiliadobase"] = '';
                                        $retorno["email"] = $p["correo_electronico_comercial"];
                                        $retorno["direccion"] = $p["direccion_comercial"];
                                        $retorno["telefono"] = $p["telefono_comercial_1"];
                                        $retorno["movil"] = '';
                                        $retorno["idmunicipio"] = $p["municipio_comercial"];
                                        $retorno["benley1780"] = '';
                                        $retorno["multadoponal"] = '';
                                        $retorno["ultanoren"] = $p["ultimo_ano_renovado"];
                                        foreach ($p["informacion_financiera"] as $f) {
                                            if ($f["ano_informacion_financiera"] == $p["ultimo_ano_renovado"]) {
                                                $retorno["activos"] = $p["activo_total"];
                                            }
                                        }
                                    }
                                } else {
                                    if ($retorno["propietariojurisdiccion"] == '') {
                                        $retorno["propietariojurisdiccion"] = 'NM';
                                        $retorno["propietariocc"] = '';
                                        $retorno["idexpedientebase"] = '';
                                        $retorno["idmatriculabase"] = '';
                                        $retorno["nombrebase"] = $p["razon_social"];
                                        $retorno["nom1base"] = $p["primer_nombre"];
                                        $retorno["nom2base"] = $p["segundo_nombre"];
                                        $retorno["ape1base"] = $p["primer_apellido"];
                                        $retorno["ape2base"] = $p["segundo_apellido"];
                                        $retorno["tipoidentificacionbase"] = ltrim($p["codigo_clase_identificacion"], "0");
                                        $retorno["identificacionbase"] = ltrim($p["numero_identificacion"], "0");
                                        $retorno["organizacionbase"] = $p["codigo_organizacion_juridica"];
                                        $retorno["categoriabase"] = '1';
                                        $retorno["afiliadobase"] = '';
                                        $retorno["email"] = $p["correo_electronico_comercial"];
                                        $retorno["direccion"] = $p["direccion_comercial"];
                                        $retorno["telefono"] = $p["telefono_comercial_1"];
                                        $retorno["movil"] = '';
                                        $retorno["idmunicipio"] = $p["municipio_comercial"];
                                        $retorno["benley1780"] = '';
                                        $retorno["multadoponal"] = '';
                                        $retorno["ultanoren"] = '';
                                        $retorno["activos"] = 0;
                                    }
                                }
                            }
                        }
                        if ($p["codigocamara"] == CODIGO_EMPRESA && $p["matriculapropietario"] == '') { // preguntar el propietario al RUES
                            if ($p["nit"] != '') {
                                $pr = \funcionesRues::consultarRegMerIdentificacion($_SESSION["generales"]["codigousuario"], '2', $p["nit"]);
                            } else {
                                $pr = \funcionesRues::consultarRegMerIdentificacion($_SESSION["generales"]["codigousuario"], $p["tipoidentificacion"], $p["identificaicon"]);
                            }
                            if (isset($pr["registros"])) {
                                foreach ($pr["registros"] as $r) {
                                    if ($r["codigo_estado_matricula"] == '01') {
                                        if ($retorno["propietariojurisdiccion"] == '') {
                                            $retorno["propietariojurisdiccion"] = 'N';
                                            $retorno["propietariocc"] = $r["codigo_camara"];
                                            $retorno["idexpedientebase"] = ltrim($r["matricula"], "0");
                                            $retorno["idmatriculabase"] = ltrim($r["matricula"], "0");
                                            $retorno["nombrebase"] = $r["razon_social"];
                                            $retorno["nom1base"] = $r["primer_nombre"];
                                            $retorno["nom2base"] = $r["segundo_nombre"];
                                            $retorno["ape1base"] = $r["primer_apellido"];
                                            $retorno["ape2base"] = $r["segundo_apellido"];
                                            $retorno["tipoidentificacionbase"] = ltrim($p["codigo_tipo_identificacion"], "0");
                                            $retorno["identificacionbase"] = ltrim($p["numero_identificacion"], "0");
                                            $retorno["organizacionbase"] = $p["codigo_organizacion_juridica"];
                                            $retorno["categoriabase"] = '1';
                                            $retorno["afiliadobase"] = '';
                                            $retorno["email"] = $p["correo_electronico_comercial"];
                                            $retorno["direccion"] = $p["direccion_comercial"];
                                            $retorno["telefono"] = $p["telefono_comercial_1"];
                                            $retorno["movil"] = '';
                                            $retorno["idmunicipio"] = $p["municipio_comercial"];
                                            $retorno["benley1780"] = '';
                                            $retorno["multadoponal"] = '';
                                            $retorno["ultanoren"] = $p["ultimo_ano_renovado"];
                                            foreach ($p["informacionFinanciera"] as $f) {
                                                if ($f["ano_informacion_financiera"] == $p["ultimo_ano_renovado"]) {
                                                    $retorno["activos"] = $p["activo_total"];
                                                }
                                            }
                                        }
                                    } else {
                                        if ($retorno["propietariojurisdiccion"] == '') {
                                            $retorno["propietariojurisdiccion"] = 'NM';
                                            $retorno["propietariocc"] = '';
                                            $retorno["idexpedientebase"] = '';
                                            $retorno["idmatriculabase"] = '';
                                            $retorno["nombrebase"] = $p["razon_social"];
                                            $retorno["nom1base"] = $p["primer_nombre"];
                                            $retorno["nom2base"] = $p["segundo_nombre"];
                                            $retorno["ape1base"] = $p["primer_apellido"];
                                            $retorno["ape2base"] = $p["segundo_apellido"];
                                            $retorno["tipoidentificacionbase"] = ltrim($p["codigo_tipo_identificacion"], "0");
                                            $retorno["identificacionbase"] = ltrim($p["numero_identificacion"], "0");
                                            $retorno["organizacionbase"] = $p["codigo_organizacion_juridica"];
                                            $retorno["categoriabase"] = '1';
                                            $retorno["afiliadobase"] = '';
                                            $retorno["email"] = $p["correo_electronico_comercial"];
                                            $retorno["direccion"] = $p["direccion_comercial"];
                                            $retorno["telefono"] = $p["telefono_comercial_1"];
                                            $retorno["movil"] = '';
                                            $retorno["idmunicipio"] = $p["municipio_comercial"];
                                            $retorno["benley1780"] = '';
                                            $retorno["multadoponal"] = '';
                                            $retorno["ultanoren"] = '';
                                            $retorno["activos"] = 0;
                                        }
                                    }
                                }
                            } else {
                                if ($retorno["propietariojurisdiccion"] == '') {
                                    $retorno["propietariojurisdiccion"] = 'NM';
                                    $retorno["propietariocc"] = '';
                                    $retorno["idexpedientebase"] = '';
                                    $retorno["idmatriculabase"] = '';
                                    $retorno["nombrebase"] = $p["razonsocial"];
                                    $retorno["nom1base"] = $p["nombre1"];
                                    $retorno["nom2base"] = $p["nombre2"];
                                    $retorno["ape1base"] = $p["apellido1"];
                                    $retorno["ape2base"] = $p["apellido2"];
                                    $retorno["tipoidentificacionbase"] = $p["tipoidentificacion"];
                                    $retorno["identificacionbase"] = $p["identificacion"];
                                    $retorno["organizacionbase"] = '';
                                    $retorno["categoriabase"] = '';
                                    $retorno["afiliadobase"] = '';
                                    $retorno["email"] = $p["emailcom"];
                                    $retorno["direccion"] = $p["dircom"];
                                    $retorno["telefono"] = $p["telcom1"];
                                    $retorno["movil"] = '';
                                    $retorno["idmunicipio"] = $p["muncom"];
                                    $retorno["benley1780"] = '';
                                    $retorno["multadoponal"] = '';
                                    $retorno["ultanoren"] = '';
                                    $retorno["activos"] = 0;
                                }
                            }
                        }
                    }
                }
            }

            if ($arrTem["organizacion"] > '02' && $arrTem["categoria"] != '1') {
                $retorno["cantidadpropietarios"]++;
                if ($arrTem["cpcodcam"] == CODIGO_EMPRESA && $arrTem["cpnummat"] != '') { // Propietario en la jurisdiccion (matriculado)
                    $p = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $arrTem["cpnummat"], '', '', '', 'si', 'N');
                    if ($p === false || empty($p)) {
                        $retorno["codigoerror"] = '9999';
                        $retorno["mensajeerror"] = 'El propietario ' . $arrTem["cpnummat"] . ' no fue encontrado en el sistema de infomación.';
                        return $retorno;
                    }
                    if ($p["ctrestmatricula"] == 'MC' || $p["ctrestmatricula"] == 'IC' || $p["ctrestmatricula"] == 'NM') { // Propietario cancelado
                        if ($retorno["propietariojurisdiccion"] == '') {
                            $retorno["propietariojurisdiccion"] = 'NM';
                            $retorno["idexpedientebase"] = '';
                            $retorno["idmatriculabase"] = '';
                            $retorno["nombrebase"] = $p["nombre"];
                            $retorno["nom1base"] = $p["nom1"];
                            $retorno["nom2base"] = $p["nom2"];
                            $retorno["ape1base"] = $p["ape1"];
                            $retorno["ape2base"] = $p["ape2"];
                            $retorno["tipoidentificacionbase"] = $p["tipoidentificacion"];
                            $retorno["identificacionbase"] = $p["identificacion"];
                            $retorno["organizacionbase"] = $p["organizacion"];
                            $retorno["categoriabase"] = $p["categoria"];
                            $retorno["afiliadobase"] = $p["afiliado"];
                            $retorno["email"] = $p["emailcom"];
                            $retorno["direccion"] = $p["dircom"];
                            $telcom = '';
                            $celcom = '';
                            if (strlen($p["telcom1"]) == 7) {
                                $telcom = $p["telcom1"];
                            } else {
                                if (strlen($p["telcom1"]) == 10) {
                                    $celcom = $p["telcom1"];
                                }
                            }
                            if (strlen($p["telcom2"]) == 7) {
                                if (trim($telcom) == '') {
                                    $telcom = $p["telcom2"];
                                }
                            } else {
                                if (strlen($p["telcom2"]) == 10) {
                                    if (trim($celcom) == '') {
                                        $celcom = $p["telcom2"];
                                    }
                                }
                            }
                            if (strlen($p["celcom"]) == 7) {
                                if (trim($telcom) == '') {
                                    $telcom = $p["celcom"];
                                }
                            } else {
                                if (strlen($p["celcom"]) == 10) {
                                    if (trim($celcom) == '') {
                                        $celcom = $p["celcom"];
                                    }
                                }
                            }
                            $retorno["telefono"] = $telcom;
                            $retorno["movil"] = $celcom;
                            $retorno["idmunicipio"] = $p["muncom"];
                            $retorno["benley1780"] = '';
                            $retorno["ultanoren"] = '';
                            $retorno["activos"] = 0;
                        }
                    } else {
                        if ($retorno["propietariojurisdiccion"] == '') {
                            $arrTemProp = $p;
                            $retorno["propietariojurisdiccion"] = 'S';
                            $retorno["idexpedientebase"] = $arrTem["cpnummat"];
                            $retorno["idmatriculabase"] = $arrTem["cpnummat"];
                            $retorno["nombrebase"] = $p["nombre"];
                            $retorno["nom1base"] = $p["nom1"];
                            $retorno["nom2base"] = $p["nom2"];
                            $retorno["ape1base"] = $p["ape1"];
                            $retorno["ape2base"] = $p["ape2"];
                            $retorno["tipoidentificacionbase"] = $p["tipoidentificacion"];
                            $retorno["identificacionbase"] = $p["identificacion"];
                            $retorno["organizacionbase"] = $p["organizacion"];
                            $retorno["categoriabase"] = $p["categoria"];
                            $retorno["afiliadobase"] = $p["afiliado"];
                            $retorno["email"] = $p["emailcom"];
                            $retorno["direccion"] = $p["dircom"];
                            $telcom = '';
                            $celcom = '';
                            if (strlen($p["telcom1"]) == 7) {
                                $telcom = $p["telcom1"];
                            } else {
                                if (strlen($p["telcom1"]) == 10) {
                                    $celcom = $p["telcom1"];
                                }
                            }
                            if (strlen($p["telcom2"]) == 7) {
                                if (trim($telcom) == '') {
                                    $telcom = $p["telcom2"];
                                }
                            } else {
                                if (strlen($p["telcom2"]) == 10) {
                                    if (trim($celcom) == '') {
                                        $celcom = $p["telcom2"];
                                    }
                                }
                            }
                            if (strlen($p["celcom"]) == 7) {
                                if (trim($telcom) == '') {
                                    $telcom = $p["celcom"];
                                }
                            } else {
                                if (strlen($p["celcom"]) == 10) {
                                    if (trim($celcom) == '') {
                                        $celcom = $p["celcom"];
                                    }
                                }
                            }
                            $retorno["telefono"] = $telcom;
                            $retorno["movil"] = $celcom;
                            $retorno["idmunicipio"] = $p["muncom"];
                            $retorno["benley1780"] = $p["benley1780"];
                            $retorno["ultanoren"] = $p["ultanoren"];
                            $retorno["activos"] = $p["acttot"];
                        }
                    }
                }
                if ($arrTem["cpcodcam"] != CODIGO_EMPRESA && $arrTem["cpnummat"] != '') { // preguntar el propietario al RUES
                    $px = \funcionesRues::consultarRegMer($arrTem["cpcodcam"], $arrTem["cpnummat"]);
                    if ($px && $px["codigo_error"] == '0000') {
                        if ($px["codigo_estado_matricula"] == '01') {
                            if ($retorno["propietariojurisdiccion"] == '') {
                                $retorno["propietariojurisdiccion"] = 'N';
                                $retorno["propietariocc"] = $px["codigo_camara"];
                                $retorno["idexpedientebase"] = ltrim($px["matricula"], "0");
                                $retorno["idmatriculabase"] = ltrim($px["matricula"], "0");
                                $retorno["nombrebase"] = $px["razon_social"];
                                $retorno["nom1base"] = $px["primer_nombre"];
                                $retorno["nom2base"] = $px["segundo_nombre"];
                                $retorno["ape1base"] = $px["primer_apellido"];
                                $retorno["ape2base"] = $px["segundo_apellido"];
                                $retorno["tipoidentificacionbase"] = ltrim($px["codigo_clase_identificacion"], "0");
                                $retorno["identificacionbase"] = ltrim($px["numero_identificacion"], "0");
                                $retorno["organizacionbase"] = $px["codigo_organizacion_juridica"];
                                $retorno["categoriabase"] = '1';
                                $retorno["afiliadobase"] = '';
                                $retorno["email"] = $px["correo_electronico_comercial"];
                                $retorno["direccion"] = $px["direccion_comercial"];
                                $retorno["telefono"] = $px["telefono_comercial_1"];
                                $retorno["movil"] = '';
                                $retorno["idmunicipio"] = $px["municipio_comercial"];
                                $retorno["benley1780"] = '';
                                $retorno["multadoponal"] = '';
                                $retorno["ultanoren"] = $px["ultimo_ano_renovado"];
                                foreach ($px["informacion_financiera"] as $f) {
                                    if ($f["ano_informacion_financiera"] == $px["ultimo_ano_renovado"]) {
                                        $retorno["activos"] = $f["activo_total"];
                                    }
                                }
                            }
                        } else {
                            if ($retorno["propietariojurisdiccion"] == '') {
                                $retorno["propietariojurisdiccion"] = 'NM';
                                $retorno["propietariocc"] = '';
                                $retorno["idexpedientebase"] = '';
                                $retorno["idmatriculabase"] = '';
                                $retorno["nombrebase"] = $px["razon_social"];
                                $retorno["nom1base"] = $px["primer_nombre"];
                                $retorno["nom2base"] = $px["segundo_nombre"];
                                $retorno["ape1base"] = $px["primer_apellido"];
                                $retorno["ape2base"] = $px["segundo_apellido"];
                                $retorno["tipoidentificacionbase"] = ltrim($px["codigo_clase_identificacion"], "0");
                                $retorno["identificacionbase"] = ltrim($px["numero_identificacion"], "0");
                                $retorno["organizacionbase"] = $px["codigo_organizacion_juridica"];
                                $retorno["categoriabase"] = '1';
                                $retorno["afiliadobase"] = '';
                                $retorno["email"] = $px["correo_electronico_comercial"];
                                $retorno["direccion"] = $px["direccion_comercial"];
                                $retorno["telefono"] = $px["telefono_comercial_1"];
                                $retorno["movil"] = '';
                                $retorno["idmunicipio"] = $px["municipio_comercial"];
                                $retorno["benley1780"] = '';
                                $retorno["multadoponal"] = '';
                                $retorno["ultanoren"] = '';
                                $retorno["activos"] = 0;
                            }
                        }
                    }
                }
                if ($p["cpcodcam"] == CODIGO_EMPRESA && $p["cpnummat"] == '') { // preguntar el propietario al RUES
                    $pr = \funcionesRues::consultarRegMerIdentificacion($_SESSION["generales"]["codigousuario"], '2', $p["cpnumnit"]);
                    if (isset($pr["registros"])) {
                        foreach ($pr["registros"] as $r) {
                            if ($r["codigo_estado_matricula"] == '01') {
                                if ($retorno["propietariojurisdiccion"] == '') {
                                    $retorno["propietariojurisdiccion"] = 'N';
                                    $retorno["propietariocc"] = $r["codigo_camara"];
                                    $retorno["idexpedientebase"] = ltrim($r["matricula"], "0");
                                    $retorno["idmatriculabase"] = ltrim($r["matricula"], "0");
                                    $retorno["nombrebase"] = $r["razon_social"];
                                    $retorno["nom1base"] = $r["primer_nombre"];
                                    $retorno["nom2base"] = $r["segundo_nombre"];
                                    $retorno["ape1base"] = $r["primer_apellido"];
                                    $retorno["ape2base"] = $r["segundo_apellido"];
                                    $retorno["tipoidentificacionbase"] = ltrim($p["codigo_tipo_identificacion"], "0");
                                    $retorno["identificacionbase"] = ltrim($p["numero_identificacion"], "0");
                                    $retorno["organizacionbase"] = $p["codigo_organizacion_juridica"];
                                    $retorno["categoriabase"] = '1';
                                    $retorno["afiliadobase"] = '';
                                    $retorno["email"] = $p["correo_electronico_comercial"];
                                    $retorno["direccion"] = $p["direccion_comercial"];
                                    $retorno["telefono"] = $p["telefono_comercial_1"];
                                    $retorno["movil"] = '';
                                    $retorno["idmunicipio"] = $p["municipio_comercial"];
                                    $retorno["benley1780"] = '';
                                    $retorno["multadoponal"] = '';
                                    $retorno["ultanoren"] = $p["ultimo_ano_renovado"];
                                    foreach ($p["informacionFinanciera"] as $f) {
                                        if ($f["ano_informacion_financiera"] == $p["ultimo_ano_renovado"]) {
                                            $retorno["activos"] = $p["activo_total"];
                                        }
                                    }
                                }
                            } else {
                                if ($retorno["propietariojurisdiccion"] == '') {
                                    $retorno["propietariojurisdiccion"] = 'NM';
                                    $retorno["propietariocc"] = '';
                                    $retorno["idexpedientebase"] = '';
                                    $retorno["idmatriculabase"] = '';
                                    $retorno["nombrebase"] = $p["razon_social"];
                                    $retorno["nom1base"] = $p["primer_nombre"];
                                    $retorno["nom2base"] = $p["segundo_nombre"];
                                    $retorno["ape1base"] = $p["primer_apellido"];
                                    $retorno["ape2base"] = $p["segundo_apellido"];
                                    $retorno["tipoidentificacionbase"] = ltrim($p["codigo_tipo_identificacion"], "0");
                                    $retorno["identificacionbase"] = ltrim($p["numero_identificacion"], "0");
                                    $retorno["organizacionbase"] = $p["codigo_organizacion_juridica"];
                                    $retorno["categoriabase"] = '1';
                                    $retorno["afiliadobase"] = '';
                                    $retorno["email"] = $p["correo_electronico_comercial"];
                                    $retorno["direccion"] = $p["direccion_comercial"];
                                    $retorno["telefono"] = $p["telefono_comercial_1"];
                                    $retorno["movil"] = '';
                                    $retorno["idmunicipio"] = $p["municipio_comercial"];
                                    $retorno["benley1780"] = '';
                                    $retorno["multadoponal"] = '';
                                    $retorno["ultanoren"] = '';
                                    $retorno["activos"] = 0;
                                }
                            }
                        }
                    } else {
                        if ($retorno["propietariojurisdiccion"] == '') {
                            $retorno["propietariojurisdiccion"] = 'NM';
                            $retorno["propietariocc"] = '';
                            $retorno["idexpedientebase"] = '';
                            $retorno["idmatriculabase"] = '';
                            $retorno["nombrebase"] = $p["cprazsoc"];
                            $retorno["nom1base"] = '';
                            $retorno["nom2base"] = '';
                            $retorno["ape1base"] = '';
                            $retorno["ape2base"] = '';
                            $retorno["tipoidentificacionbase"] = '2';
                            $retorno["identificacionbase"] = $p["cpnumnit"];
                            $retorno["organizacionbase"] = '';
                            $retorno["categoriabase"] = '';
                            $retorno["afiliadobase"] = '';
                            $retorno["email"] = '';
                            $retorno["direccion"] = $p["cpdircom"];
                            $retorno["telefono"] = $p["cpnumtel"];
                            $retorno["movil"] = '';
                            $retorno["idmunicipio"] = $p["cpcodmun"];
                            $retorno["benley1780"] = '';
                            $retorno["multadoponal"] = '';
                            $retorno["ultanoren"] = '';
                            $retorno["activos"] = 0;
                        }
                    }
                }
            }


            //
            if ($retorno["propietariojurisdiccion"] == '') {
                $retorno["codigoerror"] = '9999';
                $retorno["mensajeerror"] = 'No se encontro propietario de la matrícula seleccionada';
                return $retorno;
            }
        }


        //
        $i = -1;
        $con = 0;

        // Solo el expediente seleccionado
        if ($procesartodas == 'N') { // Solo el expediente seleccionado
            $i++;
            $mu = array();
            $mu["idtipoidentificacion"] = $arrTemBase["tipoidentificacion"];
            $mu["identificacion"] = $arrTemBase["identificacion"];
            $mu["cc"] = CODIGO_EMPRESA;
            $mu["matricula"] = $arrTemBase["matricula"];
            $mu["nombre"] = $arrTemBase["nombre"];
            $mu["ape1"] = $arrTemBase["ape1"];
            $mu["ape2"] = $arrTemBase["ape2"];
            $mu["nom1"] = $arrTemBase["nom1"];
            $mu["nom2"] = $arrTemBase["nom2"];
            $mu["organizacion"] = $arrTemBase["organizacion"];
            $mu["categoria"] = $arrTemBase["categoria"];
            $mu["identificacionpropietario"] = '';
            $mu["identificacionrepresentantelegal"] = '';
            $mu["ultimoanorenovado"] = $arrTemBase["ultanoren"];
            if ($arrTemBase["organizacion"] == '01' || ($arrTemBase["organizacion"] > '02' && $arrTemBase["categoria"] == '1')) {
                $mu["ultimosactivos"] = $arrTemBase["acttot"];
            } else {
                $mu["ultimosactivos"] = $arrTemBase["actvin"];
            }
            $mu["afiliado"] = $arrTemBase["afiliado"];
            $mu["ultimoanoafiliado"] = $arrTemBase["ultanorenafi"];
            $mu["propietariojurisdiccion"] = $retorno["propietariojurisdiccion"];
            $mu["disolucion"] = '';
            if ($arrTemBase["disueltaporvencimiento"] == 'si' || $arrTemBase["disueltaporacto510"] == 'si') {
                $mu["disolucion"] = 'S';
            }
            $mu["fechadisolucion"] = $arrTemBase["fechadisolucion"];
            $mu["fechanacimiento"] = $arrTemBase["fechanacimiento"];
            $mu["fechamatricula"] = $arrTemBase["fechamatricula"];
            $mu["fecmatant"] = $arrTemBase["fecmatant"];
            $mu["fecharenovacion"] = $arrTemBase["fecharenovacion"];
            $mu["benart7"] = $arrTemBase["art7"];
            $mu["benley1780"] = $arrTemBase["benley1780"];
            $mu["circular19"] = '';
            $mu["municipio"] = $arrTemBase["muncom"];
            $mu["clasegenesadl"] = $arrTemBase["clasegenesadl"];
            $mu["claseespesadl"] = $arrTemBase["claseespesadl"];
            $mu["econsoli"] = $arrTemBase["claseeconsoli"];
            $mu["expedienteinactivo"] = '';
            if ($arrTemBase["estadomatricula"] == 'MI' || $arrTemBase["estadomatricula"] == 'II') {
                $mu["expedienteinactivo"] = 'S';
            }
            $mu["dircom"] = $arrTemBase["dircom"];
            $mu["emailcom"] = $arrTemBase["emailcom"];
            $mu["telcom1"] = $arrTemBase["telcom1"];
            $mu["telcom3"] = $arrTemBase["celcom"];
            $mu["multadoponal"] = '';
            $mu["ciiu1"] = $arrTemBase["ciius"][1];
            $mu["ciiu2"] = $arrTemBase["ciius"][2];
            $mu["ciiu3"] = $arrTemBase["ciius"][3];
            $mu["ciiu4"] = $arrTemBase["ciius"][4];
            $mu["protegeractivos"] = 'no';
            $mu["nuevosactivos"] = 0;
            if ($arrTemBase["organizacion"] == '02' || $arrTemBase["categoria"] == '2' || $arrTemBase["categoria"] == '3') {
                if ($retorno["ultanoren"] == date("Y")) {
                    $feinicio045 = '';
                    $decreto045 = 'no';
                    if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
                        $feinicio045 = '99999999';
                    } else {
                        $feinicio045 = FECHA_INICIO_DECRETO_045;
                    }
                    if (date("Ymd") >= $feinicio045) {
                        $decreto045 = 'si';
                    }
                    if ($decreto045 == 'si') {
                        $mu["nuevosactivos"] = $retorno["activos"];
                        $mu["protegeractivos"] = 'si';
                    }
                }
            }
            $retorno["matriculasunicas"][] = $mu;
            $retorno["totalmatriculas"]++;

            if ($arrTemBase["ultanoren"] == date("Y")) {
                $con++;
                $feinicio045 = '';
                $decreto045 = 'no';
                if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
                    $feinicio045 = '99999999';
                } else {
                    $feinicio045 = FECHA_INICIO_DECRETO_045;
                }
                if (date("Y") . '0101' >= $feinicio045) {
                    $decreto045 = 'si';
                }
                $mx = array();
                $mx["idtipoidentificacion"] = $arrTemBase["tipoidentificacion"];
                $mx["identificacion"] = $arrTemBase["identificacion"];
                $mx["cc"] = CODIGO_EMPRESA;
                $mx["matricula"] = $arrTemBase["matricula"];
                $mx["nombre"] = $arrTemBase["nombre"];
                $mx["ape1"] = $arrTemBase["ape1"];
                $mx["ape2"] = $arrTemBase["ape2"];
                $mx["nom1"] = $arrTemBase["nom1"];
                $mx["nom2"] = $arrTemBase["nom2"];
                $mx["organizacion"] = $arrTemBase["organizacion"];
                $mx["categoria"] = $arrTemBase["categoria"];
                $mx["ultimoanorenovado"] = $arrTemBase["ultanoren"];
                if ($arrTemBase["organizacion"] == '01' || ($arrTemBase["organizacion"] > '02' && $arrTemBase["categoria"] == '1')) {
                    $mx["ultimosactivos"] = $arrTemBase["acttot"];
                    $mx["nombreactivos"] = 'Activos del comerciante';
                    if ($arrTemBase["organizacion"] == '12' || $arrTemBase["organizacion"] == '14') {
                        $mx["nombreactivos"] = 'Activos de la entidad';
                    }
                } else {
                    $mx["ultimosactivos"] = $arrTemBase["actvin"];
                    if ($decreto045 == 'si') {
                        $mx["nombreactivos"] = 'Activos del propietario';
                    } else {
                        $mx["nombreactivos"] = 'Activos vinculados al establecimiento';
                    }
                }
                $mx["anoarenovar"] = date("Y");
                $mx["afiliado"] = $arrTemBase["afiliado"];
                $mx["ultimoanoafiliado"] = $arrTemBase["ultanorenafi"];
                $mx["propietariojurisdiccion"] = $retorno["propietariojurisdiccion"];
                $mx["disolucion"] = '';
                if ($arrTemBase["disueltaporvencimiento"] == 'si' || $arrTemBase["disueltaporacto510"] == 'si') {
                    $retorno["matriculas"][$i]["disolucion"] = 'S';
                }
                $mx["fechadisolucion"] = $arrTemBase["fechadisolucion"];
                $mx["fechanacimiento"] = $arrTemBase["fechanacimiento"];
                $mx["fechamatricula"] = $arrTemBase["fechamatricula"];
                $mx["fecmatant"] = $arrTemBase["fecmatant"];
                $mx["fecharenovacion"] = $arrTemBase["fecharenovacion"];
                $mx["benart7"] = $arrTemBase["art7"];
                $mx["benley1780"] = $arrTemBase["benley1780"];
                $mx["circular19"] = '';
                $mx["municipio"] = $arrTemBase["muncom"];
                $mx["clasegenesadl"] = $arrTemBase["clasegenesadl"];
                $mx["claseespesadl"] = $arrTemBase["claseespesadl"];
                $mx["econsoli"] = $arrTemBase["claseeconsoli"];
                $mx["expedienteinactivo"] = '';
                if ($arrTemBase["estadomatricula"] == 'MI' || $arrTemBase["estadomatricula"] == 'II') {
                    $mx["expedienteinactivo"] = 'S';
                }
                $mx["dircom"] = $arrTemBase["dircom"];
                $mx["emailcom"] = $arrTemBase["emailcom"];
                $mx["telcom1"] = $arrTemBase["telcom1"];
                $mx["telcom3"] = $arrTemBase["celcom"];
                $mx["multadoponal"] = '';
                $mx["ciiu1"] = $arrTemBase["ciius"][1];
                $mx["ciiu2"] = $arrTemBase["ciius"][2];
                $mx["ciiu3"] = $arrTemBase["ciius"][3];
                $mx["ciiu4"] = $arrTemBase["ciius"][4];
                $mx["protegeractivos"] = 'no';
                if ($arrTemBase["organizacion"] == '01' || ($arrTemBase["organizacion"] > '02' && $arrTemBase["categoria"] == '1')) {
                    $mx["nuevosactivos"] = 0;
                } else {
                    if ($retorno["ultanoren"] == date("Y")) {
                        if ($decreto045 == 'si') {
                            $mx["nuevosactivos"] = $retorno["activos"];
                            $mx["protegeractivos"] = 'si';
                        } else {
                            $mx["nuevosactivos"] = 0;
                        }
                    }
                }
                $retorno["matriculas"][] = $mx;
            } else {
                for ($iano = $arrTemBase["ultanoren"] + 1; $iano <= date("Y"); $iano++) {
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
                    $mx = array();
                    $mx["idtipoidentificacion"] = $arrTemBase["tipoidentificacion"];
                    $mx["identificacion"] = $arrTemBase["identificacion"];
                    $mx["cc"] = CODIGO_EMPRESA;
                    $mx["matricula"] = $arrTemBase["matricula"];
                    $mx["nombre"] = $arrTemBase["nombre"];
                    $mx["ape1"] = $arrTemBase["ape1"];
                    $mx["ape2"] = $arrTemBase["ape2"];
                    $mx["nom1"] = $arrTemBase["nom1"];
                    $mx["nom2"] = $arrTemBase["nom2"];
                    $mx["organizacion"] = $arrTemBase["organizacion"];
                    $mx["categoria"] = $arrTemBase["categoria"];
                    $mx["ultimoanorenovado"] = $arrTemBase["ultanoren"];
                    if ($arrTemBase["organizacion"] == '01' || ($arrTemBase["organizacion"] > '02' && $arrTemBase["categoria"] == '1')) {
                        $mx["ultimosactivos"] = $arrTemBase["acttot"];
                        $mx["nombreactivos"] = 'Activos del comerciante';
                        if ($arrTemBase["organizacion"] == '12' || $arrTemBase["organizacion"] == '14') {
                            $mx["nombreactivos"] = 'Activos de la entidad';
                        }
                    } else {
                        $mx["ultimosactivos"] = $arrTemBase["actvin"];
                        if ($decreto045 == 'si') {
                            $mx["nombreactivos"] = 'Activos del propietario';
                        } else {
                            $mx["nombreactivos"] = 'Activos vinculados al establecimiento';
                        }
                    }
                    $mx["anoarenovar"] = $iano;
                    $mx["afiliado"] = $arrTemBase["afiliado"];
                    $mx["ultimoanoafiliado"] = $arrTemBase["ultanorenafi"];
                    $mx["propietariojurisdiccion"] = $retorno["propietariojurisdiccion"];
                    $mx["disolucion"] = '';
                    if ($arrTemBase["disueltaporvencimiento"] == 'si' || $arrTemBase["disueltaporacto510"] == 'si') {
                        $retorno["matriculas"][$i]["disolucion"] = 'S';
                    }
                    $mx["fechadisolucion"] = $arrTemBase["fechadisolucion"];
                    $mx["fechanacimiento"] = $arrTemBase["fechanacimiento"];
                    $mx["fechamatricula"] = $arrTemBase["fechamatricula"];
                    $mx["fecmatant"] = $arrTemBase["fecmatant"];
                    $mx["fecharenovacion"] = $arrTemBase["fecharenovacion"];
                    $mx["benart7"] = $arrTemBase["art7"];
                    $mx["benley1780"] = $arrTemBase["benley1780"];
                    $mx["circular19"] = '';
                    $mx["municipio"] = $arrTemBase["muncom"];
                    $mx["clasegenesadl"] = $arrTemBase["clasegenesadl"];
                    $mx["claseespesadl"] = $arrTemBase["claseespesadl"];
                    $mx["econsoli"] = $arrTemBase["claseeconsoli"];
                    $mx["expedienteinactivo"] = '';
                    if ($arrTemBase["estadomatricula"] == 'MI' || $arrTemBase["estadomatricula"] == 'II') {
                        $mx["expedienteinactivo"] = 'S';
                    }
                    $mx["dircom"] = $arrTemBase["dircom"];
                    $mx["emailcom"] = $arrTemBase["emailcom"];
                    $mx["telcom1"] = $arrTemBase["telcom1"];
                    $mx["telcom3"] = $arrTemBase["celcom"];
                    $mx["multadoponal"] = '';
                    $mx["ciiu1"] = $arrTemBase["ciius"][1];
                    $mx["ciiu2"] = $arrTemBase["ciius"][2];
                    $mx["ciiu3"] = $arrTemBase["ciius"][3];
                    $mx["ciiu4"] = $arrTemBase["ciius"][4];
                    $mx["protegeractivos"] = 'no';
                    if ($arrTemBase["organizacion"] == '01' || ($arrTemBase["organizacion"] > '02' && $arrTemBase["categoria"] == '1')) {
                        $mx["nuevosactivos"] = 0;
                    } else {
                        if ($retorno["ultanoren"] == date("Y")) {
                            if ($decreto045 == 'si') {
                                $mx["nuevosactivos"] = $retorno["activos"];
                                $mx["protegeractivos"] = 'si';
                            } else {
                                $mx["nuevosactivos"] = 0;
                            }
                        }
                    }
                    $retorno["matriculas"][] = $mx;
                }
            }
        }

        if ($procesartodas == 'SP') {
            if ($arrTemProp) {
                $mu = array();
                $mu["idtipoidentificacion"] = $arrTemProp["tipoidentificacion"];
                $mu["identificacion"] = $arrTemProp["identificacion"];
                $mu["cc"] = CODIGO_EMPRESA;
                $mu["matricula"] = $arrTemProp["matricula"];
                $mu["nombre"] = $arrTemProp["nombre"];
                $mu["ape1"] = $arrTemProp["ape1"];
                $mu["ape2"] = $arrTemProp["ape2"];
                $mu["nom1"] = $arrTemProp["nom1"];
                $mu["nom2"] = $arrTemProp["nom2"];
                $mu["organizacion"] = $arrTemProp["organizacion"];
                $mu["categoria"] = $arrTemProp["categoria"];
                $mu["identificacionpropietario"] = '';
                $mu["identificacionrepresentantelegal"] = '';
                $mu["ultimoanorenovado"] = $arrTemProp["ultanoren"];
                if ($arrTemProp["organizacion"] == '01' || ($arrTemProp["organizacion"] > '02' && $arrTemProp["categoria"] == '1')) {
                    $mu["ultimosactivos"] = $arrTemProp["acttot"];
                } else {
                    $mu["ultimosactivos"] = $arrTemProp["actvin"];
                }
                $mu["afiliado"] = $arrTemProp["afiliado"];
                $mu["ultimoanoafiliado"] = $arrTemProp["ultanorenafi"];
                $mu["propietariojurisdiccion"] = $retorno["propietariojurisdiccion"];
                $mu["disolucion"] = '';
                if ($arrTemProp["disueltaporvencimiento"] == 'si' || $arrTemProp["disueltaporacto510"] == 'si') {
                    $mu["disolucion"] = 'S';
                }
                $mu["fechadisolucion"] = $arrTemProp["fechadisolucion"];
                $mu["fechanacimiento"] = $arrTemProp["fechanacimiento"];
                $mu["fechamatricula"] = $arrTemProp["fechamatricula"];
                $mu["fecmatant"] = $arrTemProp["fecmatant"];
                $mu["fecharenovacion"] = $arrTemProp["fecharenovacion"];
                $mu["benart7"] = $arrTemProp["art7"];
                $mu["benley1780"] = $arrTemProp["benley1780"];
                $mu["circular19"] = '';
                $mu["municipio"] = $arrTemProp["muncom"];
                $mu["clasegenesadl"] = $arrTemProp["clasegenesadl"];
                $mu["claseespesadl"] = $arrTemProp["claseespesadl"];
                $mu["econsoli"] = $arrTemProp["claseeconsoli"];
                $mu["expedienteinactivo"] = '';
                if ($arrTemProp["estadomatricula"] == 'MI' || $arrTemProp["estadomatricula"] == 'II') {
                    $mu["expedienteinactivo"] = 'S';
                }
                $mu["dircom"] = $arrTemProp["dircom"];
                $mu["emailcom"] = $arrTemProp["emailcom"];
                $mu["telcom1"] = $arrTemProp["telcom1"];
                $mu["telcom3"] = $arrTemProp["celcom"];
                $mu["multadoponal"] = '';
                $mu["ciiu1"] = $arrTemProp["ciius"][1];
                $mu["ciiu2"] = $arrTemProp["ciius"][2];
                $mu["ciiu3"] = $arrTemProp["ciius"][3];
                $mu["ciiu4"] = $arrTemProp["ciius"][4];
                $mu["protegeractivos"] = 'no';
                $mu["nuevosactivos"] = 0;
                if ($arrTemProp["organizacion"] == '02' || $arrTemProp["categoria"] == '2' || $arrTemProp["categoria"] == '3') {
                    if ($retorno["ultanoren"] == date("Y")) {
                        $feinicio045 = '';
                        $decreto045 = 'no';
                        if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
                            $feinicio045 = '99999999';
                        } else {
                            $feinicio045 = FECHA_INICIO_DECRETO_045;
                        }
                        if (date("Ymd") >= $feinicio045) {
                            $decreto045 = 'si';
                        }
                        if ($decreto045 == 'si') {
                            $mu["nuevosactivos"] = $retorno["activos"];
                            $retorno["matriculasunicas"][$i]["protegeractivos"] = 'si';
                        }
                    }
                }
                $retorno["matriculasunicas"][] = $mu;

                if ($arrTemProp["ultanoren"] == date("Y")) {
                    $con++;
                    $feinicio045 = '';
                    $decreto045 = 'no';
                    if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
                        $feinicio045 = '99999999';
                    } else {
                        $feinicio045 = FECHA_INICIO_DECRETO_045;
                    }
                    if (date("Y") . '0101' >= $feinicio045) {
                        $decreto045 = 'si';
                    }
                    $mx = array();
                    $mx["idtipoidentificacion"] = $arrTemProp["tipoidentificacion"];
                    $mx["identificacion"] = $arrTemProp["identificacion"];
                    $mx["cc"] = CODIGO_EMPRESA;
                    $mx["matricula"] = $arrTemProp["matricula"];
                    $mx["nombre"] = $arrTemProp["nombre"];
                    $mx["ape1"] = $arrTemProp["ape1"];
                    $mx["ape2"] = $arrTemProp["ape2"];
                    $mx["nom1"] = $arrTemProp["nom1"];
                    $mx["nom2"] = $arrTemProp["nom2"];
                    $mx["organizacion"] = $arrTemProp["organizacion"];
                    $mx["categoria"] = $arrTemProp["categoria"];
                    $mx["ultimoanorenovado"] = $arrTemProp["ultanoren"];
                    if ($arrTemProp["organizacion"] == '01' || ($arrTemProp["organizacion"] > '02' && $arrTemProp["categoria"] == '1')) {
                        $mx["ultimosactivos"] = $arrTemProp["acttot"];
                        $mx["nombreactivos"] = 'Activos del comerciante';
                        if ($arrTemProp["organizacion"] == '12' || $arrTemProp["organizacion"] == '14') {
                            $mx["nombreactivos"] = 'Activos de la entidad';
                        }
                    } else {
                        $mx["ultimosactivos"] = $arrTemProp["actvin"];
                        if ($decreto045 == 'si') {
                            $mx["nombreactivos"] = 'Activos del propietario';
                        } else {
                            $mx["nombreactivos"] = 'Activos vinculados al establecimiento';
                        }
                    }
                    $mx["anoarenovar"] = date("Y");
                    $mx["afiliado"] = $arrTemProp["afiliado"];
                    $mx["ultimoanoafiliado"] = $arrTemProp["ultanorenafi"];
                    $mx["propietariojurisdiccion"] = $retorno["propietariojurisdiccion"];
                    $mx["disolucion"] = '';
                    if ($arrTemProp["disueltaporvencimiento"] == 'si' || $arrTemProp["disueltaporacto510"] == 'si') {
                        $retorno["matriculas"][$i]["disolucion"] = 'S';
                    }
                    $mx["fechadisolucion"] = $arrTemProp["fechadisolucion"];
                    $mx["fechanacimiento"] = $arrTemProp["fechanacimiento"];
                    $mx["fechamatricula"] = $arrTemProp["fechamatricula"];
                    $mx["fecmatant"] = $arrTemProp["fecmatant"];
                    $mx["fecharenovacion"] = $arrTemProp["fecharenovacion"];
                    $mx["benart7"] = $arrTemProp["art7"];
                    $mx["benley1780"] = $arrTemProp["benley1780"];
                    $mx["circular19"] = '';
                    $mx["municipio"] = $arrTemProp["muncom"];
                    $mx["clasegenesadl"] = $arrTemProp["clasegenesadl"];
                    $mx["claseespesadl"] = $arrTemProp["claseespesadl"];
                    $mx["econsoli"] = $arrTemProp["claseeconsoli"];
                    $mx["expedienteinactivo"] = '';
                    if ($arrTemProp["estadomatricula"] == 'MI' || $arrTemProp["estadomatricula"] == 'II') {
                        $mx["expedienteinactivo"] = 'S';
                    }
                    $mx["dircom"] = $arrTemProp["dircom"];
                    $mx["emailcom"] = $arrTemProp["emailcom"];
                    $mx["telcom1"] = $arrTemProp["telcom1"];
                    $mx["telcom3"] = $arrTemProp["celcom"];
                    $mx["multadoponal"] = '';
                    $mx["ciiu1"] = $arrTemProp["ciius"][1];
                    $mx["ciiu2"] = $arrTemProp["ciius"][2];
                    $mx["ciiu3"] = $arrTemProp["ciius"][3];
                    $mx["ciiu4"] = $arrTemProp["ciius"][4];
                    $mx["protegeractivos"] = 'no';
                    if ($arrTemProp["organizacion"] == '01' || ($arrTemProp["organizacion"] > '02' && $arrTemProp["categoria"] == '1')) {
                        $mx["nuevosactivos"] = 0;
                    } else {
                        if ($retorno["ultanoren"] == date("Y")) {
                            if ($decreto045 == 'si') {
                                $mx["nuevosactivos"] = $retorno["activos"];
                                $mx["protegeractivos"] = 'si';
                            } else {
                                $mx["nuevosactivos"] = 0;
                            }
                        }
                    }
                    $retorno["matriculas"][] = $mx;
                } else {
                    for ($iano = $arrTemProp["ultanoren"] + 1; $iano <= date("Y"); $iano++) {
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
                        $mx = array();
                        $mx["idtipoidentificacion"] = $arrTemProp["tipoidentificacion"];
                        $mx["identificacion"] = $arrTemProp["identificacion"];
                        $mx["cc"] = CODIGO_EMPRESA;
                        $mx["matricula"] = $arrTemProp["matricula"];
                        $mx["nombre"] = $arrTemProp["nombre"];
                        $mx["ape1"] = $arrTemProp["ape1"];
                        $mx["ape2"] = $arrTemProp["ape2"];
                        $mx["nom1"] = $arrTemProp["nom1"];
                        $mx["nom2"] = $arrTemProp["nom2"];
                        $mx["organizacion"] = $arrTemProp["organizacion"];
                        $mx["categoria"] = $arrTemProp["categoria"];
                        $mx["ultimoanorenovado"] = $arrTemProp["ultanoren"];
                        if ($arrTemProp["organizacion"] == '01' || ($arrTemProp["organizacion"] > '02' && $arrTemProp["categoria"] == '1')) {
                            $mx["ultimosactivos"] = $arrTemProp["acttot"];
                            $mx["nombreactivos"] = 'Activos del comerciante';
                            if ($arrTemProp["organizacion"] == '12' || $arrTemProp["organizacion"] == '14') {
                                $mx["nombreactivos"] = 'Activos de la entidad';
                            }
                        } else {
                            $mx["ultimosactivos"] = $arrTemProp["actvin"];
                            if ($decreto045 == 'si') {
                                $mx["nombreactivos"] = 'Activos del propietario';
                            } else {
                                $mx["nombreactivos"] = 'Activos vinculados al establecimiento';
                            }
                        }
                        $mx["anoarenovar"] = $iano;
                        $mx["afiliado"] = $arrTemProp["afiliado"];
                        $mx["ultimoanoafiliado"] = $arrTemProp["ultanorenafi"];
                        $mx["propietariojurisdiccion"] = $retorno["propietariojurisdiccion"];
                        $mx["disolucion"] = '';
                        if ($arrTemProp["disueltaporvencimiento"] == 'si' || $arrTemProp["disueltaporacto510"] == 'si') {
                            $retorno["matriculas"][$i]["disolucion"] = 'S';
                        }
                        $mx["fechadisolucion"] = $arrTemProp["fechadisolucion"];
                        $mx["fechanacimiento"] = $arrTemProp["fechanacimiento"];
                        $mx["fechamatricula"] = $arrTemProp["fechamatricula"];
                        $mx["fecmatant"] = $arrTemProp["fecmatant"];
                        $mx["fecharenovacion"] = $arrTemProp["fecharenovacion"];
                        $mx["benart7"] = $arrTemProp["art7"];
                        $mx["benley1780"] = $arrTemProp["benley1780"];
                        $mx["circular19"] = '';
                        $mx["municipio"] = $arrTemProp["muncom"];
                        $mx["clasegenesadl"] = $arrTemProp["clasegenesadl"];
                        $mx["claseespesadl"] = $arrTemProp["claseespesadl"];
                        $mx["econsoli"] = $arrTemProp["claseeconsoli"];
                        $mx["expedienteinactivo"] = '';
                        if ($arrTemProp["estadomatricula"] == 'MI' || $arrTemProp["estadomatricula"] == 'II') {
                            $mx["expedienteinactivo"] = 'S';
                        }
                        $mx["dircom"] = $arrTemProp["dircom"];
                        $mx["emailcom"] = $arrTemProp["emailcom"];
                        $mx["telcom1"] = $arrTemProp["telcom1"];
                        $mx["telcom3"] = $arrTemProp["celcom"];
                        $mx["multadoponal"] = '';
                        $mx["ciiu1"] = $arrTemProp["ciius"][1];
                        $mx["ciiu2"] = $arrTemProp["ciius"][2];
                        $mx["ciiu3"] = $arrTemProp["ciius"][3];
                        $mx["ciiu4"] = $arrTemProp["ciius"][4];
                        $mx["protegeractivos"] = 'no';
                        if ($arrTemProp["organizacion"] == '01' || ($arrTemProp["organizacion"] > '02' && $arrTemProp["categoria"] == '1')) {
                            $mx["nuevosactivos"] = 0;
                        } else {
                            if ($retorno["ultanoren"] == date("Y")) {
                                if ($decreto045 == 'si') {
                                    $mx["nuevosactivos"] = $retorno["activos"];
                                    $mx["protegeractivos"] = 'si';
                                } else {
                                    $mx["nuevosactivos"] = 0;
                                }
                            }
                        }
                        $retorno["matriculas"][] = $mx;
                    }
                    $retorno["totalmatriculas"]++;
                }
            }
        }

        // L.- El propietario del expediente seleccionado y sus establecimientos locales no renovados
        // S.- El propietario del expediente seleccionado y sus establecimientos nacionales no renovados
        // E.- Los establecimientos locales no renovados sin el propietario
        // SP.- Solo el propietario
        if ($procesartodas == 'L' || $procesartodas == 'E' || $procesartodas == 'S') {
            if ($procesartodas == 'L' || $procesartodas == 'S') {
                if ($arrTemProp) {
                    if ($arrTemProp["ultanoren"] < date("Y")) {
                        $mu = array();
                        $mu["idtipoidentificacion"] = $arrTemProp["tipoidentificacion"];
                        $mu["identificacion"] = $arrTemProp["identificacion"];
                        $mu["cc"] = CODIGO_EMPRESA;
                        $mu["matricula"] = $arrTemProp["matricula"];
                        $mu["nombre"] = $arrTemProp["nombre"];
                        $mu["ape1"] = $arrTemProp["ape1"];
                        $mu["ape2"] = $arrTemProp["ape2"];
                        $mu["nom1"] = $arrTemProp["nom1"];
                        $mu["nom2"] = $arrTemProp["nom2"];
                        $mu["organizacion"] = $arrTemProp["organizacion"];
                        $mu["categoria"] = $arrTemProp["categoria"];
                        $mu["identificacionpropietario"] = '';
                        $mu["identificacionrepresentantelegal"] = '';
                        $mu["ultimoanorenovado"] = $arrTemProp["ultanoren"];
                        if ($arrTemProp["organizacion"] == '01' || ($arrTemProp["organizacion"] > '02' && $arrTemProp["categoria"] == '1')) {
                            $mu["ultimosactivos"] = $arrTemProp["acttot"];
                        } else {
                            $mu["ultimosactivos"] = $arrTemProp["actvin"];
                        }
                        $mu["afiliado"] = $arrTemProp["afiliado"];
                        $mu["ultimoanoafiliado"] = $arrTemProp["ultanorenafi"];
                        $mu["propietariojurisdiccion"] = $retorno["propietariojurisdiccion"];
                        $mu["disolucion"] = '';
                        if ($arrTemProp["disueltaporvencimiento"] == 'si' || $arrTemProp["disueltaporacto510"] == 'si') {
                            $mu["disolucion"] = 'S';
                        }
                        $mu["fechadisolucion"] = $arrTemProp["fechadisolucion"];
                        $mu["fechanacimiento"] = $arrTemProp["fechanacimiento"];
                        $mu["fechamatricula"] = $arrTemProp["fechamatricula"];
                        $mu["fecmatant"] = $arrTemProp["fecmatant"];
                        $mu["fecharenovacion"] = $arrTemProp["fecharenovacion"];
                        $mu["benart7"] = $arrTemProp["art7"];
                        $mu["benley1780"] = $arrTemProp["benley1780"];
                        $mu["circular19"] = '';
                        $mu["municipio"] = $arrTemProp["muncom"];
                        $mu["clasegenesadl"] = $arrTemProp["clasegenesadl"];
                        $mu["claseespesadl"] = $arrTemProp["claseespesadl"];
                        $mu["econsoli"] = $arrTemProp["claseeconsoli"];
                        $mu["expedienteinactivo"] = '';
                        if ($arrTemProp["estadomatricula"] == 'MI' || $arrTemProp["estadomatricula"] == 'II') {
                            $mu["expedienteinactivo"] = 'S';
                        }
                        $mu["dircom"] = $arrTemProp["dircom"];
                        $mu["emailcom"] = $arrTemProp["emailcom"];
                        $mu["telcom1"] = $arrTemProp["telcom1"];
                        $mu["telcom3"] = $arrTemProp["celcom"];
                        $mu["multadoponal"] = '';
                        $mu["ciiu1"] = $arrTemProp["ciius"][1];
                        $mu["ciiu2"] = $arrTemProp["ciius"][2];
                        $mu["ciiu3"] = $arrTemProp["ciius"][3];
                        $mu["ciiu4"] = $arrTemProp["ciius"][4];
                        $mu["protegeractivos"] = 'no';
                        $mu["nuevosactivos"] = 0;
                        if ($arrTemProp["organizacion"] == '02' || $arrTemProp["categoria"] == '2' || $arrTemProp["categoria"] == '3') {
                            if ($retorno["ultanoren"] == date("Y")) {
                                $feinicio045 = '';
                                $decreto045 = 'no';
                                if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
                                    $feinicio045 = '99999999';
                                } else {
                                    $feinicio045 = FECHA_INICIO_DECRETO_045;
                                }
                                if (date("Ymd") >= $feinicio045) {
                                    $decreto045 = 'si';
                                }
                                if ($decreto045 == 'si') {
                                    $mu["nuevosactivos"] = $retorno["activos"];
                                    $retorno["matriculasunicas"][$i]["protegeractivos"] = 'si';
                                }
                            }
                        }
                        $retorno["matriculasunicas"][] = $mu;

                        for ($iano = $arrTemProp["ultanoren"] + 1; $iano <= date("Y"); $iano++) {
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
                            $mx = array();
                            $mx["idtipoidentificacion"] = $arrTemProp["tipoidentificacion"];
                            $mx["identificacion"] = $arrTemProp["identificacion"];
                            $mx["cc"] = CODIGO_EMPRESA;
                            $mx["matricula"] = $arrTemProp["matricula"];
                            $mx["nombre"] = $arrTemProp["nombre"];
                            $mx["ape1"] = $arrTemProp["ape1"];
                            $mx["ape2"] = $arrTemProp["ape2"];
                            $mx["nom1"] = $arrTemProp["nom1"];
                            $mx["nom2"] = $arrTemProp["nom2"];
                            $mx["organizacion"] = $arrTemProp["organizacion"];
                            $mx["categoria"] = $arrTemProp["categoria"];
                            $mx["ultimoanorenovado"] = $arrTemProp["ultanoren"];
                            if ($arrTemProp["organizacion"] == '01' || ($arrTemProp["organizacion"] > '02' && $arrTemProp["categoria"] == '1')) {
                                $mx["ultimosactivos"] = $arrTemProp["acttot"];
                                $mx["nombreactivos"] = 'Activos del comerciante';
                                if ($arrTemProp["organizacion"] == '12' || $arrTemProp["organizacion"] == '14') {
                                    $mx["nombreactivos"] = 'Activos de la entidad';
                                }
                            } else {
                                $mx["ultimosactivos"] = $arrTemProp["actvin"];
                                if ($decreto045 == 'si') {
                                    $mx["nombreactivos"] = 'Activos del propietario';
                                } else {
                                    $mx["nombreactivos"] = 'Activos vinculados al establecimiento';
                                }
                            }
                            $mx["anoarenovar"] = $iano;
                            $mx["afiliado"] = $arrTemProp["afiliado"];
                            $mx["ultimoanoafiliado"] = $arrTemProp["ultanorenafi"];
                            $mx["propietariojurisdiccion"] = $retorno["propietariojurisdiccion"];
                            $mx["disolucion"] = '';
                            if ($arrTemProp["disueltaporvencimiento"] == 'si' || $arrTemProp["disueltaporacto510"] == 'si') {
                                $retorno["matriculas"][$i]["disolucion"] = 'S';
                            }
                            $mx["fechadisolucion"] = $arrTemProp["fechadisolucion"];
                            $mx["fechanacimiento"] = $arrTemProp["fechanacimiento"];
                            $mx["fechamatricula"] = $arrTemProp["fechamatricula"];
                            $mx["fecmatant"] = $arrTemProp["fecmatant"];
                            $mx["fecharenovacion"] = $arrTemProp["fecharenovacion"];
                            $mx["benart7"] = $arrTemProp["art7"];
                            $mx["benley1780"] = $arrTemProp["benley1780"];
                            $mx["circular19"] = '';
                            $mx["municipio"] = $arrTemProp["muncom"];
                            $mx["clasegenesadl"] = $arrTemProp["clasegenesadl"];
                            $mx["claseespesadl"] = $arrTemProp["claseespesadl"];
                            $mx["econsoli"] = $arrTemProp["claseeconsoli"];
                            $mx["expedienteinactivo"] = '';
                            if ($arrTemProp["estadomatricula"] == 'MI' || $arrTemProp["estadomatricula"] == 'II') {
                                $mx["expedienteinactivo"] = 'S';
                            }
                            $mx["dircom"] = $arrTemProp["dircom"];
                            $mx["emailcom"] = $arrTemProp["emailcom"];
                            $mx["telcom1"] = $arrTemProp["telcom1"];
                            $mx["telcom3"] = $arrTemProp["celcom"];
                            $mx["multadoponal"] = '';
                            $mx["ciiu1"] = $arrTemProp["ciius"][1];
                            $mx["ciiu2"] = $arrTemProp["ciius"][2];
                            $mx["ciiu3"] = $arrTemProp["ciius"][3];
                            $mx["ciiu4"] = $arrTemProp["ciius"][4];
                            $mx["protegeractivos"] = 'no';
                            if ($arrTemProp["organizacion"] == '01' || ($arrTemProp["organizacion"] > '02' && $arrTemProp["categoria"] == '1')) {
                                $mx["nuevosactivos"] = 0;
                            } else {
                                if ($retorno["ultanoren"] == date("Y")) {
                                    if ($decreto045 == 'si') {
                                        $mx["nuevosactivos"] = $retorno["activos"];
                                        $mx["protegeractivos"] = 'si';
                                    } else {
                                        $mx["nuevosactivos"] = 0;
                                    }
                                }
                            }
                            $retorno["matriculas"][] = $mx;
                        }
                        $retorno["totalmatriculas"]++;
                    }
                }
            }
            if ($arrTemProp) {
                foreach ($arrTemProp["establecimientos"] as $e) {
                    if ($e["estadomatricula"] == 'MA' || $e["estadomatricula"] == 'MI') {
                        $mu = array();
                        $mu["idtipoidentificacion"] = '';
                        $mu["identificacion"] = '';
                        $mu["cc"] = CODIGO_EMPRESA;
                        $mu["matricula"] = $e["matriculaestablecimiento"];
                        $mu["nombre"] = $e["nombreestablecimiento"];
                        $mu["ape1"] = '';
                        $mu["ape2"] = '';
                        $mu["nom1"] = '';
                        $mu["nom2"] = '';
                        $mu["organizacion"] = '02';
                        $mu["categoria"] = '';
                        $mu["identificacionpropietario"] = '';
                        $mu["identificacionrepresentantelegal"] = '';
                        $mu["ultimoanorenovado"] = $e["ultanoren"];
                        $mu["ultimosactivos"] = $e["actvin"];
                        $mu["afiliado"] = '';
                        $mu["ultimoanoafiliado"] = '';
                        $mu["propietariojurisdiccion"] = $retorno["propietariojurisdiccion"];
                        $mu["disolucion"] = '';
                        $mu["fechadisolucion"] = '';
                        $mu["fechanacimiento"] = '';
                        $mu["fechamatricula"] = $e["fechamatricula"];
                        $mu["fecmatant"] = '';
                        $mu["fecharenovacion"] = $e["fecharenovacion"];
                        $mu["benart7"] = '';
                        $mu["benley1780"] = '';
                        $mu["circular19"] = '';
                        $mu["municipio"] = $e["muncom"];
                        $mu["clasegenesadl"] = '';
                        $mu["claseespesadl"] = '';
                        $mu["econsoli"] = '';
                        $mu["expedienteinactivo"] = '';
                        if ($e["estadomatricula"] == 'MI' || $e["estadomatricula"] == 'II') {
                            $mu["expedienteinactivo"] = 'S';
                        }
                        $mu["dircom"] = $e["dircom"];
                        $mu["emailcom"] = $e["emailcom"];
                        $mu["telcom1"] = $e["telcom1"];
                        $mu["telcom2"] = $e["telcom2"];
                        $mu["telcom3"] = $e["telcom3"];
                        $mu["multadoponal"] = '';
                        $mu["ciiu1"] = $e["ciiu1"];
                        $mu["ciiu2"] = $e["ciiu2"];
                        $mu["ciiu3"] = $e["ciiu3"];
                        $mu["ciiu4"] = $e["ciiu4"];
                        $mu["protegeractivos"] = 'no';
                        $mu["nuevosactivos"] = 0;
                        if ($retorno["ultanoren"] == date("Y")) {
                            $feinicio045 = '';
                            $decreto045 = 'no';
                            if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
                                $feinicio045 = '99999999';
                            } else {
                                $feinicio045 = FECHA_INICIO_DECRETO_045;
                            }
                            if (date("Ymd") >= $feinicio045) {
                                $decreto045 = 'si';
                            }
                            if ($decreto045 == 'si') {
                                $mu["nuevosactivos"] = $retorno["activos"];
                                $mu["protegeractivos"] = 'si';
                            }
                        }
                        $retorno["matriculasunicas"][] = $mu;

                        for ($iano = $e["ultanoren"] + 1; $iano <= date("Y"); $iano++) {
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
                            $mx = array();
                            $mx["idtipoidentificacion"] = '';
                            $mx["identificacion"] = '';
                            $mx["cc"] = CODIGO_EMPRESA;
                            $mx["matricula"] = $e["matriculaestablecimiento"];
                            $mx["nombre"] = $e["nombreestablecimiento"];
                            $mx["ape1"] = '';
                            $mx["ape2"] = '';
                            $mx["nom1"] = '';
                            $mx["nom2"] = '';
                            $mx["organizacion"] = '02';
                            $mx["categoria"] = '0';
                            $mx["ultimoanorenovado"] = $e["ultanoren"];
                            $mx["ultimosactivos"] = $e["actvin"];
                            if ($decreto045 == 'si') {
                                $mx["nombreactivos"] = 'Activos del propietario';
                            } else {
                                $mx["nombreactivos"] = 'Activos vinculados al establecimiento';
                            }
                            $mx["anoarenovar"] = $iano;
                            $mx["afiliado"] = '';
                            $mx["ultimoanoafiliado"] = '';
                            $mx["propietariojurisdiccion"] = $retorno["propietariojurisdiccion"];
                            $mx["disolucion"] = '';
                            $mx["fechadisolucion"] = '';
                            $mx["fechanacimiento"] = '';
                            $mx["fechamatricula"] = $e["fechamatricula"];
                            $mx["fecmatant"] = '';
                            $mx["fecharenovacion"] = $e["fecharenovacion"];
                            $mx["benart7"] = '';
                            $mx["benley1780"] = '';
                            $mx["circular19"] = '';
                            $mx["municipio"] = $e["muncom"];
                            $mx["clasegenesadl"] = '';
                            $mx["claseespesadl"] = '';
                            $mx["econsoli"] = '';
                            $mx["expedienteinactivo"] = '';
                            if ($arrTemBase["estadomatricula"] == 'MI' || $arrTemBase["estadomatricula"] == 'II') {
                                $mx["expedienteinactivo"] = 'S';
                            }
                            $mx["dircom"] = $e["dircom"];
                            $mx["emailcom"] = $e["emailcom"];
                            $mx["telcom1"] = $e["telcom1"];
                            $mx["telcom2"] = $e["telcom2"];
                            $mx["telcom3"] = $e["telcom3"];
                            $mx["multadoponal"] = '';
                            $mx["ciiu1"] = $e["ciiu1"];
                            $mx["ciiu2"] = $e["ciiu2"];
                            $mx["ciiu3"] = $e["ciiu3"];
                            $mx["ciiu4"] = $e["ciiu4"];
                            $mx["protegeractivos"] = 'no';
                            if ($retorno["ultanoren"] == date("Y")) {
                                if ($decreto045 == 'si') {
                                    $mx["nuevosactivos"] = $retorno["activos"];
                                    $mx["protegeractivos"] = 'si';
                                } else {
                                    $mx["nuevosactivos"] = 0;
                                }
                            }
                            $retorno["matriculas"][] = $mx;
                        }
                        $retorno["totalmatriculas"]++;
                    }
                }

                foreach ($arrTem["sucursalesagencias"] as $est) {
                    if ($est["estado"] == 'MA' || $est["estado"] == 'MI') {
                        if ($est["ultanoren"] < date("Y")) {
                            $mu = array();
                            $mu["idtipoidentificacion"] = '';
                            $mu["identificacion"] = '';
                            $mu["cc"] = CODIGO_EMPRESA;
                            $mu["matricula"] = $est["matriculasucage"];
                            $mu["nombre"] = $est["nombresucage"];
                            $mu["ape1"] = '';
                            $mu["ape2"] = '';
                            $mu["nom1"] = '';
                            $mu["nom2"] = '';
                            $mu["organizacion"] = $arrTemProp["organizacion"];
                            $mu["categoria"] = $est["categoria"];
                            $mu["identificacionpropietario"] = $arrTemProp["identificacion"];
                            $mu["identificacionrepresentantelegal"] = '';
                            $mu["ultimoanorenovado"] = $est["ultanoren"];
                            $mu["ultimosactivos"] = $est["actvin"];
                            $mu["afiliado"] = '';
                            $mu["ultimoanoafiliado"] = '';
                            $mu["propietariojurisdiccion"] = 'S';
                            $mu["disolucion"] = '';
                            $mu["fechadisolucion"] = '';
                            $mu["fechamatricula"] = $est["fechamatricula"];
                            $mu["fecmatant"] = '';
                            $mu["fecharenovacion"] = $est["fecharenovacion"];
                            $mu["benart7"] = '';
                            $mu["benley1780"] = '';
                            $mu["circular19"] = '';
                            $mu["municipio"] = $est["muncom"];
                            $mu["clasegenesadl"] = '';
                            $mu["claseespesadl"] = '';
                            $mu["econsoli"] = '';
                            $mu["expedienteinactivo"] = '';
                            if ($est["estado"] == 'MI') {
                                $mu["expedienteinactivo"] = 'S';
                            }
                            $mu["dircom"] = $est["dircom"];
                            $mu["emailcom"] = $est["emailcom"];
                            $mu["telcom1"] = $est["telcom1"];
                            $mu["telcom2"] = $est["telcom2"];
                            $mu["telcom3"] = $est["telcom3"];
                            $mu["multadoponal"] = '';
                            $mu["ciiu1"] = $est["ciiu1"];
                            $mu["ciiu2"] = $est["ciiu2"];
                            $mu["ciiu3"] = $est["ciiu3"];
                            $mu["ciiu4"] = $est["ciiu4"];
                            $mu["protegeractivos"] = 'no';
                            $mu["nuevosactivos"] = 0;
                            if ($retorno["ultanoren"] == date("Y")) {
                                $feinicio045 = '';
                                $decreto045 = 'no';
                                if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
                                    $feinicio045 = '99999999';
                                } else {
                                    $feinicio045 = FECHA_INICIO_DECRETO_045;
                                }
                                if (date("Ymd") >= $feinicio045) {
                                    $decreto045 = 'si';
                                }
                                if ($decreto045 == 'si') {
                                    $mu["nuevosactivos"] = $retorno["activos"];
                                    $mu["protegeractivos"] = 'si';
                                } else {
                                    $mu["nuevosactivos"] = 0;
                                }
                            }
                            $retorno["matriculasunicas"][] = $mu;

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
                                $mx = array();
                                $mx["idtipoidentificacion"] = '';
                                $mx["identificacion"] = '';
                                $mx["cc"] = CODIGO_EMPRESA;
                                $mx["matricula"] = $est["matriculasucage"];
                                $mx["nombre"] = $est["nombresucage"];
                                $mx["ape1"] = '';
                                $mx["ape2"] = '';
                                $mx["nom1"] = '';
                                $mx["nom2"] = '';
                                $mx["organizacion"] = $arrTemProp["organizacion"];
                                $mx["categoria"] = $est["categoria"];
                                $mx["identificacionpropietario"] = $arrTemProp["identificacion"];
                                $mx["identificacionrepresentantelegal"] = '';
                                $mx["ultimoanorenovado"] = $est["ultanoren"];
                                $mx["ultimosactivos"] = $est["actvin"];
                                if ($decreto045 == 'si') {
                                    $mx["nombreactivos"] = 'Activos del propietario';
                                } else {
                                    $mx["nombreactivos"] = 'Activos vinculados al establecimiento';
                                }


                                $mx["anoarenovar"] = $iano;
                                $mx["afiliado"] = '';
                                $mx["ultimoanoafiliado"] = '';
                                $mx["propietariojurisdiccion"] = 'S';
                                $mx["disolucion"] = '';
                                $mx["fechadisolucion"] = '';
                                $mx["fechamatricula"] = $est["fechamatricula"];
                                $mx["fecmatant"] = '';
                                $mx["fecharenovacion"] = $est["fecharenovacion"];
                                $mx["benart7"] = '';
                                $mx["benley1780"] = '';
                                $mx["circular19"] = '';
                                $mx["municipio"] = $est["muncom"];
                                $mx["clasegenesadl"] = '';
                                $mx["claseespesadl"] = '';
                                $mx["econsoli"] = '';
                                $mx["expedienteinactivo"] = '';
                                if ($est["estado"] == 'MI') {
                                    $mx["expedienteinactivo"] = 'S';
                                }
                                $mx["dircom"] = $est["dircom"];
                                $mx["emailcom"] = $est["emailcom"];
                                $mx["telcom1"] = $est["telcom1"];
                                $mx["telcom2"] = $est["telcom2"];
                                $mx["telcom3"] = $est["telcom3"];
                                $mx["multadoponal"] = '';
                                $mx["ciiu1"] = $est["ciiu1"];
                                $mx["ciiu2"] = $est["ciiu2"];
                                $mx["ciiu3"] = $est["ciiu3"];
                                $mx["ciiu4"] = $est["ciiu4"];
                                $mx["protegeractivos"] = 'no';
                                if ($retorno["ultanoren"] == date("Y")) {
                                    if ($decreto045 == 'si') {
                                        $mx["nuevosactivos"] = $retorno["activos"];
                                        $mx["protegeractivos"] = 'si';
                                    } else {
                                        $mx["nuevosactivos"] = 0;
                                    }
                                }
                                $retorno["matriculas"][] = $mx;
                            }
                            $retorno["totalmatriculas"]++;
                        }
                    }
                }
            }
        }

        if ($procesartodas == 'S' || $procesartodas == 'X') {
            if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {

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
                                $retorno["matriculasunicas"][$i]["protegeractivos"] = 'no';
                                $retorno["matriculasunicas"][$i]["nuevosactivos"] = 0;
                                if ($retorno["ultanoren"] == date("Y")) {
                                    $feinicio045 = '';
                                    $decreto045 = 'no';
                                    if (!defined('FECHA_INICIO_DECRETO_045') || FECHA_INICIO_DECRETO_045 == '') {
                                        $feinicio045 = '99999999';
                                    } else {
                                        $feinicio045 = FECHA_INICIO_DECRETO_045;
                                    }
                                    if (date("Ymd") >= $feinicio045) {
                                        $decreto045 = 'si';
                                    }
                                    if ($decreto045 == 'si') {
                                        $retorno["matriculasunicas"][$i]["nuevosactivos"] = $retorno["activos"];
                                        $retorno["matriculasunicas"][$i]["protegeractivos"] = 'si';
                                    } else {
                                        $retorno["matriculasunicas"][$i]["nuevosactivos"] = 0;
                                    }
                                }

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
                                        $retorno["matriculas"][$con]["nombreactivos"] = 'Activos vinculados al establecimiento';
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
                                    $retorno["matriculas"][$con]["protegeractivos"] = 'no';
                                    if ($retorno["ultanoren"] == date("Y")) {
                                        if ($decreto045 == 'si') {
                                            $retorno["matriculas"][$con]["nuevosactivos"] = $retorno["activos"];
                                            $retorno["matriculas"][$con]["protegeractivos"] = 'si';
                                        } else {
                                            $retorno["matriculas"][$con]["nuevosactivos"] = 0;
                                        }
                                    }
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
                                            $retorno["matriculas"][$con]["nombreactivos"] = 'Activos vinculados al establecimiento';
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
                                        $retorno["matriculas"][$con]["protegeractivos"] = 'no';
                                        if ($retorno["ultanoren"] == date("Y")) {
                                            if ($decreto045 == 'si') {
                                                $retorno["matriculas"][$con]["nuevosactivos"] = $retorno["activos"];
                                                $retorno["matriculas"][$con]["protegeractivos"] = 'si';
                                            } else {
                                                $retorno["matriculas"][$con]["nuevosactivos"] = 0;
                                            }
                                        }
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
        if (isset($retorno["matriculas"][0]) && ($retorno["matriculas"][0]["organizacion"] == '01' || ($retorno["matriculas"][0]["organizacion"] > '02' && $retorno["matriculas"][0]["categoria"] == '1'))) {
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
                        $retorno["codigoerror"] = '4000';
                        $retorno["mensajeerror"] = 'Se ha encontrado (en la BD local) que la identificaci&oacute;n ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeerror"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $retorno["mensajeerror"] .= 'por la Polic&iacute;a Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s), ';
                        $retorno["mensajeerror"] .= 'se sugiere revisar en la p&aacute;gina web de la Polic&iacute;a para determinar si ';
                        $retorno["mensajeerror"] .= 'la misma no ha sido pagada. Recuerde dejar la evidencia de la verificaci&OACUTE;n realizada.';
                    }
                    if ($_SESSION["generales"]["tipousuario"] == '00') {
                        $retorno["matriculas"][0]["multadoponal"] = 'L';
                        $retorno["multadoponal"] = 'L';
                        $retorno["codigoerror"] = '4000';
                        $retorno["mensajeerror"] = 'Apreciado usuario, se ha encontrado (en la BD local) que la identificaci&oacute;n ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeerror"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $retorno["mensajeerror"] .= 'por la Polic&iacute;a Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s). ';
                        $retorno["mensajeerror"] .= 'Para que su proceso de renovaci&oacute;n pueda hacerse en forma completa, le sugerimos ';
                        $retorno["mensajeerror"] .= 'tener a la mano el soporte de pago de la misma para poder cargarlo como un soporte de la renovaci&oacute;n.';
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
                        $retorno["codigoerror"] = '5000';
                        $retorno["mensajeerror"] = 'Se ha encontrado que la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeerror"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $retorno["mensajeerror"] .= 'por la Policía Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s) - ';
                        $retorno["mensajeerror"] .= 'No ha(n) sido pagada(s).<br><br>';
                        $retorno["mensajeerror"] .= 'Para realizar la renovación de la matrícula asociada con la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeerror"] .= 'se deberá(n) pagar la(s) misma(s) y esperar a que sea(n) registrada(s) en el ';
                        $retorno["mensajeerror"] .= 'Sistema Nacional de Medidas Correctiva.<br><br>';
                        $retorno["mensajeerror"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/verMultas.php?idliquidacion=' . $idliq . '" target="_blank">Ver Las multas</a><br><br>';
                        $retorno["mensajeerror"] .= 'Si desea continuar con el proceso de renovación de las matrículas asociadas ';
                        $retorno["mensajeerror"] .= 'a la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' oprima el ';
                        $retorno["mensajeerror"] .= 'siguiente enlace<br><br>';
                        $retorno["mensajeerror"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregRenovacionMatricula.php?accion=recuperarmatriculasrenovar&procesartodas=SP&identificacionbase=' . $retorno["matriculas"][0]["identificacion"] . '">Renovar establecimientos sucursales y agencias</a><br><br>';
                        $retorno["matriculas"] = array();
                    } else {
                        $retorno["codigoerror"] = '5000';
                        $retorno["mensajeerror"] = 'Se ha encontrado que la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeerror"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $retorno["mensajeerror"] .= 'por la Policía Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s) - ';
                        $retorno["mensajeerror"] .= 'No ha(n) sido pagada(s).<br><br>';
                        $retorno["mensajeerror"] .= 'Para realizar la renovación de la matrícula asociada con la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $retorno["mensajeerror"] .= 'se deberá(n) pagar la(s) misma(s) y esperar a que sea(n) registrada(s) en el ';
                        $retorno["mensajeerror"] .= 'Sistema Nacional de Medidas Correctiva.<br><br>';
                        $retorno["mensajeerror"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/verMultas.php?idliquidacion=' . $idliq . ' target="_blank">Ver Las multas</a><br><br>';
                        $retorno["mensajeerror"] .= 'No es posible continuar con el proceso de renovación. ';
                        $retorno["mensajeerror"] .= 'Para volver al módulo de renovación y seleccionar otro expediente ';
                        $retorno["mensajeerror"] .= 'oprima el siguiente enlace.<br><br>';
                        $retorno["mensajeerror"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregRenovacionMatricula.php?accion=pantallaseleccion">Reiniciar renovación</a><br><br>';
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
        if ($retorno["multadoponal"] == 'E') {
            $retorno["codigoerror"] = '4000';
            $retorno["mensajeerror"] = 'No fue posible verificar si el comerciante tiene multas vencidas de acuerdo ';
            $retorno["mensajeerror"] .= 'con lo establecido en el C&oacute;digo de Polic&iacute;a. Por favor verificar en la p&aacute;gina web de la polic&iacute;a ';
            $retorno["mensajeerror"] .= 'antes de continuar con el proceso de renovaci&oacute;n.';
        }
        ob_clean();
        return $retorno;
    }

}

?>
