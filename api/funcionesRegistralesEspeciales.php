<?php

class funcionesRegistralesEspeciales {

    public static function calcularTarifaEspecial2021($dbx, $numliq = 0, $lista = array(), $forzardescuentos1756 = 'N') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        $nameLog = 'calcularTarifaEspecial2021_' . date("Ymd");

        //
        if (!defined('ACTIVADO_DECRETO_TARIFAESPECIAL_2021') || ACTIVADO_DECRETO_TARIFAESPECIAL_2021 == '' || ACTIVADO_DECRETO_TARIFAESPECIAL_2021 == 'N') {
            return false;
        }

        //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($dbx, $numliq);
        if ($_SESSION["tramite"] === false) {
            return false;
        }

        if (substr($_SESSION["tramite"]["tipotramite"], 0, 4) == 'rues' && substr($_SESSION["tramite"]["tipotramite"], 6) == 'receptora') {
            return false;
        }

        //
        $fcorte = retornarRegistroMysqliApi($dbx, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
        \logApi::general2($nameLog, $numliq, 'Tipo de tramite : ' . $_SESSION["tramite"]["tipotramite"]);
        \logApi::general2($nameLog, $numliq, 'Fecha corte renovacion : ' . $fcorte);
        \logApi::general2($nameLog, $numliq, 'Forzar descuento 1780/1756 : ' . $forzardescuentos1756);

        // ************************************************************************************ //
        // Borra servicios de tarifa especial previamente calculados
        // ************************************************************************************ //
        $dets = array();
        foreach ($_SESSION["tramite"]["liquidacion"] as $key => $aliq) {
            if ($aliq["valorservicio"] > 0) {
                $aliq["clavecontrol"] = \funcionesGenerales::generarAleatorioAlfanumerico10($dbx, '');
                $arrCampos = array(
                    'clavecontrol'
                );
                $arrValores = array(
                    "'" . $aliq["clavecontrol"] . "'"
                );
                // regrabarRegistrosMysqliApi($dbx, 'mreg_liquidaciondetalle', $arrCampos, $arrValores, "id=" . $aliq["id"]);
                regrabarRegistrosMysqliApi($dbx, 'mreg_liquidaciondetalle', $arrCampos, $arrValores, "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"] . " and secuencia=" . $aliq["secuencia"]);
            }
            if ($aliq["idservicio"] < '01090151' || $aliq["idservicio"] > '01090160') {
                $dets[] = $aliq;
            }
            $sec = $key;
        }
        $_SESSION["tramite"]["liquidacion"] = $dets;

        //
        $iAdic = 0;
        $servAdic = array();

        //
        $tiene1780 = '';
        foreach ($_SESSION["tramite"]["liquidacion"] as $regliq) {
            if ($regliq["idservicio"] == '01090110' || $regliq["idservicio"] == '01090111') {
                $tiene1780 = 'si';
            }
        }

        //
        $mipymeprop = '';
        $incluirmipymeprop = '';
        foreach ($_SESSION["tramite"]["liquidacion"] as $regliq) {

            if ($regliq["valorservicio"] > 0) {

                $serv = retornarRegistroMysqliApi($dbx, 'mreg_servicios', "idservicio='" . $regliq["idservicio"] . "'");
                if ($serv && !empty($serv)) {

                    // ************************************************************************************ //
                    // RENOVACION - PRINCIPALES 
                    // Aplicabilidad de descuentos sobre renovacion
                    // Personas naturales y juridicas principales
                    // Siempre y cuando sea mipyme 
                    // Esta al di­a
                    // ************************************************************************************ //
                    if ($serv["tipoingreso"] == '03' || $serv["tipoingreso"] == '13') {
                        if ($regliq["cc"] == '' || $regliq["cc"] == CODIGO_EMPRESA) {
                            if ($regliq["ano"] == date("Y")) {
                                \logApi::general2($nameLog, $numliq, 'Ano a renovar matricula  : ' . $regliq["expediente"] . ' - ' . $regliq["ano"]);

                                //
                                $incluir = 'no';

                                //
                                $exp = \funcionesRegistrales::retornarExpedienteMercantil($dbx, $regliq["expediente"]);
                                if ($exp && !empty($exp)) {
                                    if ($exp["organizacion"] == '01' && $exp["estadomatricula"] == 'MA') {
                                        $incluir = 'si';
                                    } else {
                                        if ($exp["organizacion"] != '02' && $exp["categoria"] == '1' && ($exp["estadomatricula"] == 'MA' || $exp["estadomatricula"] == 'IA')) {
                                            $incluir = 'si';
                                        }
                                    }
                                }

                                //
                                if ($incluir == 'si') {
                                    $incluir = 'no';
                                    $esmipyme = '';
                                    $form = false;
                                    $formx = retornarRegistroMysqliApi($dbx, 'mreg_liquidaciondatos', "idliquidacion=" . $numliq . " and expediente='" . $regliq["expediente"] . "'");
                                    if ($formx && !empty($formx)) {
                                        $form = \funcionesGenerales::desserializarExpedienteMatricula($dbx, $formx["xml"]);
                                        if ($form["ciiutamanoempresarial"] == '') {
                                            $form["ciiutamanoempresarial"] = $exp["ciiutamanoempresarial"];
                                        }
                                        $esmipyme = \funcionesRegistrales::determinarTamanoEmpresarialCodigo($dbx, $form["ciiutamanoempresarial"], $form["ingope"], $form["anodatos"], $form["fechadatos"], date("Y"));
                                        \logApi::general2($nameLog, $numliq, 'Datos formulario : ' . $regliq["expediente"] . ' - Ciiu: ' . $form["ciiutamanoempresarial"] . ' - Ingresos: ' . $form["ingope"] . ' - Ano datos: ' . $form["anodatos"] . ' - Fecha datos: ' . $form["fechadatos"]);
                                        \logApi::general2($nameLog, $numliq, 'Tamano empresarial segun formulario : ' . $regliq["expediente"] . ' - Tamano : ' . $esmipyme);
                                    } else {
                                        \logApi::general2($nameLog, $numliq, 'Tamano empresarial del expediente : ' . $regliq["expediente"] . ' - Tamano : ' . $exp["tamanoempresarial957codigo"]);
                                        if ($exp["tamanoempresarial957codigo"] == '') {
                                            $esmipyme = \funcionesRegistrales::determinarTamanoEmpresarialCodigo($dbx, $exp["ciius"][1], $exp["ingope"], $exp["anodatos"], $exp["fechadatos"]);
                                            \logApi::general2($nameLog, $numliq, 'Datos expediente : ' . $regliq["expediente"] . ' - Ciiu: ' . $exp["ciius"][1] . ' - Ingresos: ' . $exp["ingope"] . ' - Ano datos: ' . $exp["anodatos"] . ' - Fecha datos: ' . $exp["fechadatos"]);
                                            \logApi::general2($nameLog, $numliq, 'Tamano empresairal calculado : ' . $regliq["expediente"] . ' - Tamano : ' . $esmipyme);
                                        } else {
                                            $esmipyme = $exp["tamanoempresarial957codigo"];
                                            \logApi::general2($nameLog, $numliq, 'Tamano empresarial del tramite : ' . $regliq["expediente"] . ' - Tamano : ' . $esmipyme);
                                        }
                                    }
                                    if ($esmipyme == '1' || $esmipyme == '2' || $esmipyme == '3') {
                                        $incluir = 'si';
                                    }
                                    $mipymeprop = $esmipyme;
                                }

                                //
                                if ($incluir == 'si') {
                                    $incluir = 'no';
                                    if (substr($exp["fechamatricula"], 0, 4) == date("Y") && $exp["camant"] == '') {
                                        $incluir = 'si';
                                        $incluirmipymeprop = 'si';
                                    }
                                    if (substr($exp["fechamatricula"], 0, 4) == date("Y") && $exp["camant"] != '') {
                                        if (date("Ymd") <= $fcorte || $forzardescuentos1756 == 'S') {
                                            if ($exp["ultanoren"] == date("Y")) {
                                                $incluir = 'si';
                                                $incluirmipymeprop = 'si';
                                            } else {
                                                if ($exp["ultanoren"] == date("Y") - 1) {
                                                    $incluir = 'si';
                                                    $incluirmipymeprop = 'si';
                                                } else {
                                                    $anosrenovados = 0;
                                                    foreach ($_SESSION["tramite"]["liquidacion"] as $rx) {
                                                        if ($rx["expediente"] == $regliq["expediente"] && substr($rx["idservicio"], 0, 6) == '010202') {
                                                            $anosrenovados++;
                                                        }
                                                    }
                                                    if ($anosrenovados >= 1) {
                                                        $incluir = 'si';
                                                        $incluirmipymeprop = 'si';
                                                    }
                                                }
                                            }
                                        }
                                        if (date("Ymd") > $fcorte) {
                                            if ($exp["ultanoren"] == date("Y")) {
                                                if ($exp["fecharenovacion"] <= fcorte || $forzardescuentos1756 == 'S') {
                                                    $incluir = 'si';
                                                    $incluirmipymeprop = 'si';
                                                }
                                            }
                                        }
                                    }
                                    if (substr($exp["fechamatricula"], 0, 4) < date("Y")) {
                                        if (date("Ymd") <= $fcorte || $forzardescuentos1756 == 'S') {
                                            if ($exp["ultanoren"] == date("Y")) {
                                                $incluir = 'si';
                                                $incluirmipymeprop = 'si';
                                            } else {
                                                if ($exp["ultanoren"] == date("Y") - 1) {
                                                    $incluir = 'si';
                                                    $incluirmipymeprop = 'si';
                                                } else {
                                                    $anosrenovados = 0;
                                                    foreach ($_SESSION["tramite"]["liquidacion"] as $rx) {
                                                        if ($rx["expediente"] == $regliq["expediente"] && substr($rx["idservicio"], 0, 6) == '010202') {
                                                            $anosrenovados++;
                                                        }
                                                    }
                                                    if ($anosrenovados >= 1) {
                                                        $incluir = 'si';
                                                        $incluirmipymeprop = 'si';
                                                    }
                                                }
                                            }
                                        }
                                        if (date("Ymd") > $fcorte) {
                                            if ($exp["ultanoren"] == date("Y")) {
                                                if ($exp["fecharenovacion"] <= $fcorte || $forzardescuentos1756 == 'S') {
                                                    $incluir = 'si';
                                                    $incluirmipymeprop = 'si';
                                                }
                                            }
                                        }
                                    }
                                }

                                //
                                if ($incluir == 'si') {
                                    if ($tiene1780 != 'si') {
                                        \logApi::general2($nameLog, $numliq, 'Aplica descuento de renovacion pnat o pjur');
                                        $xser = array();
                                        $xser["idsec"] = $regliq["idsec"];
                                        if ($exp["organizacion"] == '01') {
                                            $xser["idservicio"] = '01090151';
                                        }
                                        if ($exp["organizacion"] > '02' && $exp["organizacion"] != '12' && $exp["organizacion"] != '14') {
                                            $xser["idservicio"] = '01090152';
                                        }
                                        if ($exp["organizacion"] == '12' || $exp["organizacion"] == '14') {
                                            $xser["idservicio"] = '01090154';
                                        }
                                        $xser["cc"] = $regliq["cc"];
                                        $xser["expediente"] = $regliq["expediente"];
                                        $xser["nombre"] = $regliq["nombre"];
                                        $xser["ano"] = $regliq["ano"];
                                        $xser["cantidad"] = $regliq["cantidad"] * -1;
                                        $xser["valorbase"] = $regliq["valorservicio"];
                                        $xser["porcentaje"] = 5;
                                        $xser["valorservicio"] = \funcionesGenerales::redondear00(round($xser["valorbase"] * $xser["porcentaje"] / 100)) * -1;
                                        $xser["benart7"] = '';
                                        $xser["benley1780"] = '';
                                        $xser["reliquidacion"] = $regliq["reliquidacion"];
                                        $xser["serviciobase"] = 'N';
                                        $xser["clavecontrol"] = $regliq["clavecontrol"];
                                        if ($xser["valorservicio"] != 0) {
                                            $iAdic++;
                                            $servAdic[$iAdic] = $xser;
                                        }
                                    }
                                } else {
                                    \logApi::general2($nameLog, $numliq, 'No Aplica descuento de renovacion  pnat o pjur');
                                }
                            }
                        }
                    }

                    // ************************************************************************************ //
                    // ACTOS Y DOCUMENTOS - PRINCIPALES
                    // Descuento del 7% sobre todos los servicios de mutacion, inscripcion de actos
                    // Siempre y cuando el expediente afectado sea mypime y se encuentre al dÃ­a
                    // Persona juri­dicas principales y personas naturales
                    // ************************************************************************************ //
                    if ($serv["tipoingreso"] == '04' ||
                            $serv["tipoingreso"] == '05' ||
                            $serv["tipoingreso"] == '07' ||
                            $serv["tipoingreso"] == '14' ||
                            $serv["tipoingreso"] == '15' ||
                            $serv["tipoingreso"] == '17'
                    ) {
                        if ($_SESSION["tramite"]["subtipotramite"] != 'constitucionpjur' &&
                                $_SESSION["tramite"]["subtipotramite"] != 'constitucionesadl' &&
                                $_SESSION["tramite"]["subtipotramite"] != 'matriculapjur' &&
                                $_SESSION["tramite"]["subtipotramite"] != 'matriculapjurcae') {
                            if ($regliq["cc"] == '' || $regliq["cc"] == CODIGO_EMPRESA) {
                                $incluir = 'no';
                                if (ltrim(trim($regliq["expediente"]), "0") != '') {
                                    $exp = \funcionesRegistrales::retornarExpedienteMercantil($dbx, $regliq["expediente"]);
                                } else {
                                    $exp = false;
                                }
                                if ($exp && !empty($exp)) {
                                    $incluir = 'no';
                                    if ($exp["organizacion"] == '01' && $exp["estadomatricula"] == 'MA') {
                                        $incluir = 'si';
                                    } else {
                                        if ($exp["organizacion"] != '02' && $exp["categoria"] == '1' && ($exp["estadomatricula"] == 'MA' || $exp["estadomatricula"] == 'IA')) {
                                            $incluir = 'si';
                                        }
                                    }
                                    if ($incluir == 'si') {
                                        $incluir = 'no';
                                        if ($incluirmipymeprop == 'si') {
                                            $incluir = 'si';
                                        } else {
                                            if ($exp["tamanoempresarial957codigo"] == '') {
                                                $exp["tamanoempresarial957codigo"] = \funcionesRegistrales::determinarTamanoEmpresarialCodigo($dbx, $exp["ciius"][1], $exp["ingope"], $exp["anodatos"], $exp["fechadatos"]);
                                                \logApi::general2($nameLog, $numliq, 'Datos expediente : ' . $regliq["expediente"] . ' - Ciiu: ' . $exp["ciius"][1] . ' - Ingresos: ' . $exp["ingope"] . ' - Ano datos: ' . $exp["anodatos"] . ' - Fecha datos: ' . $exp["fechadatos"]);
                                                \logApi::general2($nameLog, $numliq, 'Tamano empresairal calculado : ' . $regliq["expediente"] . ' - Tamano : ' . $exp["tamanoempresarial957codigo"]);
                                            }
                                            if ($exp["tamanoempresarial957codigo"] == '1' || $exp["tamanoempresarial957codigo"] == '2' || $exp["tamanoempresarial957codigo"] == '3') {
                                                \logApi::general2($nameLog, $numliq, 'Expediente : ' . $regliq["expediente"] . ' - renovado al ' . $exp["ultanoren"]);
                                                if (substr($exp["fechamatricula"], 0, 4) == date("Y") && $exp["camant"] == '') {
                                                    $incluir = 'si';
                                                }
                                                if (substr($exp["fechamatricula"], 0, 4) == date("Y") && $exp["camant"] != '') {
                                                    if ($exp["ultanoren"] == date("Y") - 2) {
                                                        if (defined('TARIFAESPECIAL_2021_DESCU_CANCELACION_2019') && TARIFAESPECIAL_2021_DESCU_CANCELACION_2019 == 'N') {
                                                            $incluir = 'no';
                                                        } else {
                                                            if (date("Ymd") <= $fcorte || $forzardescuentos1756 == 'S') {
                                                                $poscan = strpos($serv["nombre"], 'CANCELACION');
                                                                $poscan1 = strpos($serv["nombre"], 'CANCELA. PNAT / JURIDICA (AUTOMATICA)');
                                                                $posdis = strpos($serv["nombre"], 'DISOLUCION');
                                                                $posliq = strpos($serv["nombre"], 'LIQUIDACION');
                                                                if ($poscan === false && $poscan1 === false && $posdis === false && $posliq === false) {
                                                                    $incluir = 'no';
                                                                } else {
                                                                    if ($exp["organizacion"] == '12' || $exp["organizacion"] == '14') {
                                                                        $incluir = 'no';
                                                                    } else {
                                                                        $incluir = 'si';
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                    if ($exp["ultanoren"] == date("Y") - 1) {
                                                        $incluir = 'si';
                                                    }
                                                    if ($exp["ultanoren"] == date("Y")) {
                                                        $incluir = 'si';
                                                    }
                                                }

                                                if (substr($exp["fechamatricula"], 0, 4) < date("Y")) {
                                                    if ($exp["ultanoren"] == date("Y") - 2) {
                                                        if (defined('TARIFAESPECIAL_2021_DESCU_CANCELACION_2019') && TARIFAESPECIAL_2021_DESCU_CANCELACION_2019 == 'N') {
                                                            $incluir = 'no';
                                                        } else {
                                                            if (date("Ymd") <= $fcorte || $forzardescuentos1756 == 'S') {
                                                                $poscan = strpos($serv["nombre"], 'CANCELACION');
                                                                $poscan1 = strpos($serv["nombre"], 'CANCELA. PNAT / JURIDICA (AUTOMATICA)');
                                                                $posdis = strpos($serv["nombre"], 'DISOLUCION');
                                                                $posliq = strpos($serv["nombre"], 'LIQUIDACION');
                                                                if ($poscan === false && $poscan1 === false && $posdis === false && $posliq === false) {
                                                                    $incluir = 'no';
                                                                } else {
                                                                    if ($exp["organizacion"] == '12' || $exp["organizacion"] == '14') {
                                                                        $incluir = 'no';
                                                                    } else {
                                                                        $incluir = 'si';
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                    if ($exp["ultanoren"] == date("Y") - 1) {
                                                        // if (date("Ymd") <= $fcorte) {
                                                        $incluir = 'si';
                                                        // }
                                                    }
                                                    if ($exp["ultanoren"] == date("Y")) {
                                                        $incluir = 'si';
                                                    }
                                                }
                                                if ($incluir == 'no') {
                                                    \logApi::general2($nameLog, $numliq, 'No se encuentra renovado : ' . $regliq["expediente"] . ' - Fecha matricula : ' . $exp["fechamatricula"] . ' - Fecha renovacion : ' . $exp["fecharenovacion"] . ' - Ult ano ren : ' . $exp["ultanoren"]);
                                                }
                                            }
                                        }
                                    }

                                    if ($incluir == 'si') {
                                        \logApi::general2($nameLog, $numliq, 'Aplica descuento de actos y documentos pnat o pjur');
                                        $xser = array();
                                        $xser["idsec"] = $regliq["idsec"];
                                        if ($exp["organizacion"] == '01') {
                                            $xser["idservicio"] = '01090155';
                                        }
                                        if ($exp["organizacion"] > '02' && $exp["organizacion"] != '12' && $exp["organizacion"] != '14') {
                                            $xser["idservicio"] = '01090155';
                                        }
                                        if ($exp["organizacion"] == '12' || $exp["organizacion"] == '14') {
                                            $xser["idservicio"] = '01090156';
                                        }
                                        $xser["cc"] = $regliq["cc"];
                                        $xser["expediente"] = $regliq["expediente"];
                                        $xser["nombre"] = $regliq["nombre"];
                                        $xser["ano"] = $regliq["ano"];
                                        $xser["cantidad"] = $regliq["cantidad"] * -1;
                                        $xser["valorbase"] = $regliq["valorservicio"];
                                        $xser["porcentaje"] = 7;
                                        $xser["valorservicio"] = \funcionesGenerales::redondear00(round($xser["valorbase"] * $xser["porcentaje"] / 100)) * -1;
                                        $xser["benart7"] = '';
                                        $xser["benley1780"] = '';
                                        $xser["reliquidacion"] = $regliq["reliquidacion"];
                                        $xser["serviciobase"] = 'N';
                                        $xser["clavecontrol"] = $regliq["clavecontrol"];
                                        if ($xser["valorservicio"] != 0) {
                                            $iAdic++;
                                            $servAdic[$iAdic] = $xser;
                                        }
                                    } else {
                                        \logApi::general2($nameLog, $numliq, 'No Aplica descuento de actos y documentos pnat o pjur');
                                    }
                                }
                            }
                        }
                    }

                    // ************************************************************************************ //
                    // RENOVACION - ESTABLECIMIENTOS
                    // Aplicabilidad de descuentos sobre renovacion
                    // Establecimientos, sucursales y agencias
                    // Siempre y cuando sea mipyme el propietario
                    // Esta al dia
                    // ************************************************************************************ //
                    if ($serv["tipoingreso"] == '03' || $serv["tipoingreso"] == '13') {
                        if ($regliq["cc"] == '' || $regliq["cc"] == CODIGO_EMPRESA) {
                            if ($regliq["ano"] == date("Y")) {
                                $incluir = 'no';
                                $siprop = 'no';
                                $exp = \funcionesRegistrales::retornarExpedienteMercantil($dbx, $regliq["expediente"]);
                                if ($exp && !empty($exp)) {
                                    if ($exp["estadomatricula"] == 'MA') {
                                        if ($exp["organizacion"] == '02' || ($exp["organizacion"] > '02' && $exp["categoria"] == '2') || ($exp["organizacion"] > '02' && $exp["categoria"] == '3')) {
                                            if (substr($exp["fechamatricula"], 0, 4) == date("Y")) {
                                                $incluir = 'si';
                                            }
                                            if (substr($exp["fechamatricula"], 0, 4) < date("Y")) {
                                                if (date("Ymd") <= $fcorte || $forzardescuentos1756 == 'S') {
                                                    if ($exp["ultanoren"] == date("Y")) {
                                                        $incluir = 'si';
                                                    } else {
                                                        if ($exp["ultanoren"] == date("Y") - 1) {
                                                            $incluir = 'si';
                                                        } else {
                                                            $anospendientes = 0;
                                                            $anosrenovados = 0;
                                                            for ($ix = $exp["ultanoren"] + 1; $ix <= date("Y"); $ix++) {
                                                                $anospendientes++;
                                                                foreach ($_SESSION["tramite"]["liquidacion"] as $rx) {
                                                                    if ($rx["expediente"] == $regliq["expediente"] && substr($rx["idservicio"], 0, 6) == '010202') {
                                                                        if ($rx["ano"] == $ix) {
                                                                            $anosrenovados++;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            if ($anospendientes == $anosrenovados) {
                                                                $incluir = 'si';
                                                            }
                                                        }
                                                    }
                                                }
                                                if (date("Ymd") > $fcorte) {
                                                    if ($exp["ultanoren"] == date("Y")) {
                                                        if ($exp["fecharenovacion"] <= fcorte || $forzardescuentos1756 == 'S') {
                                                            $incluir = 'si';
                                                        }
                                                    }
                                                }
                                            }

                                            if ($incluir == 'si') {
                                                if ($mipymeprop != '') {
                                                    if ($mipymeprop == '1' || $mipymeprop == '2' || $mipymeprop == '3') {
                                                        $incluir = 'si';
                                                    } else {
                                                        $incluir = 'no';
                                                    }
                                                } else {
                                                    $incluir = 'no';
                                                    $camprop = '';
                                                    $matprop = '';
                                                    if ($exp["organizacion"] == '02') {
                                                        if (isset($exp["propietarios"][1]) && trim($exp["propietarios"][1]["matriculapropietario"] != '')) {
                                                            $camprop = $exp["propietarios"][1]["camarapropietario"];
                                                            $matprop = $exp["propietarios"][1]["matriculapropietario"];
                                                            $siprop = 'si';
                                                        }
                                                    }
                                                    if ($exp["categoria"] == '2' || $exp["categoria"] == '3') {
                                                        $camprop = $exp["cpcodcam"];
                                                        $matprop = $exp["cpnummat"];
                                                        $siprop = 'si';
                                                    }
                                                    if ($matprop != '' && ($camprop == '' || $camprop == CODIGO_EMPRESA)) {
                                                        \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - propietario es local : ' . $matprop);
                                                        $exp1 = \funcionesRegistrales::retornarExpedienteMercantil($dbx, $matprop);
                                                        if ($exp1 && !empty($exp1)) {
                                                            if ($exp1["estadomatricula"] == 'MA' || $exp1["estadomatricula"] == 'IA') {
                                                                \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - Tamano empresarial del propietario : ' . $exp1["tamanoempresarial957codigo"]);
                                                                \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - Datos renovacion del propietario -  Fecha matricula :' . $exp1["fechamatricula"] . ' - Fecha renovacion :' . $exp1["fecharenovacion"] . ' -  Ult ano ren :' . $exp1["ultanoren"]);
                                                                if ($exp1["tamanoempresarial957codigo"] == '' || $exp1["tamanoempresarial957codigo"] == '1' || $exp1["tamanoempresarial957codigo"] == '2' || $exp1["tamanoempresarial957codigo"] == '3') {
                                                                    $incluir = 'si';
                                                                    if (substr($exp1["fechamatricula"], 0, 4) == date("Y")) {
                                                                        $incluir = 'si';
                                                                    }
                                                                    if (substr($exp1["fechamatricula"], 0, 4) < date("Y")) {
                                                                        if (date("Ymd") <= $fcorte || $forzardescuentos1756 == 'S') {
                                                                            if ($exp1["ultanoren"] == date("Y")) {
                                                                                $incluir = 'si';
                                                                            } else {
                                                                                if ($exp1["ultanoren"] == date("Y") - 1) {
                                                                                    $incluir = 'si';
                                                                                } else {
                                                                                    $anospendientes = 0;
                                                                                    $anosrenovados = 0;
                                                                                    for ($ix = $exp1["ultanoren"] + 1; $ix <= date("Y"); $ix++) {
                                                                                        $anospendientes++;
                                                                                        foreach ($_SESSION["tramite"]["liquidacion"] as $rx) {
                                                                                            if ($rx["expediente"] == $matprop && substr($rx["idservicio"], 0, 6) == '010202') {
                                                                                                if ($rx["ano"] == $ix) {
                                                                                                    $anosrenovados++;
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                    if ($anospendientes == $anosrenovados) {
                                                                                        $incluir = 'si';
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                        if (date("Ymd") > $fcorte) {
                                                                            if ($exp1["ultanoren"] == date("Y")) {
                                                                                if ($exp1["fecharenovacion"] <= fcorte || $forzardescuentos1756 == 'S') {
                                                                                    $incluir = 'si';
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                    if ($matprop != '' && $camprop != '' && $camprop != CODIGO_EMPRESA) {
                                                        \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - propietario fuera de la jurisdiccion : ' . $camprop . '-' . $matprop);
                                                        $rues = \funcionesRues::consultarRegMer($camprop, trim($matprop));
                                                        if (isset($rues["codigo_error"]) && $rues["codigo_error"] == '0000') {
                                                            if ($rues["codigo_estado_matricula"] == '01' || $rues["codigo_estado_matricula"] == '07' || $rues["codigo_estado_matricula"] == '08') {
                                                                \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - Tamano empresarial del propietario : ' . $rues["codigo_tamano_empresa"]);
                                                                \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - Datos renovacion del propietario -  Fecha matricula :' . $rues["fecha_matricula"] . ' - Fecha renovacion :' . $rues["fecha_renovacion"] . ' -  Ult ano ren :' . $rues["ultimo_ano_renovado"]);
                                                                // $rues["codigo_tamano_empresa"] = '00';
                                                                if ($rues["codigo_tamano_empresa"] == '00') {
                                                                    if ($rues["ultimo_ano_renovado"] >= '2020') {
                                                                        \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - Debe calcular tamano empresarial del propietario');
                                                                        $ciiurev = '';
                                                                        $ciiux = retornarRegistroMysqliApi($dbx, 'bas_ciius', "idciiunum='" . trim($rues["cod_ciiu_act_econ_pri"]) . "'");
                                                                        if ($ciiux && !empty($ciiux)) {
                                                                            $ciiurev = $ciiux["idciiu"];
                                                                        }
                                                                        $ingrev = 0;
                                                                        $anorev = '';
                                                                        $fecharev = $rues["fecha_renovacion"];
                                                                        if (isset($rues["informacion_financiera"]) && !empty($rues["informacion_financiera"])) {
                                                                            foreach ($rues["informacion_financiera"] as $fin) {
                                                                                $ingrev = $fin["ingresos_actividad_ordinaria"];
                                                                                $anorev = $fin["ano_informacion_financiera"];
                                                                            }
                                                                        }
                                                                        \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - Datos del propietario : Ciiu ' . $ciiurev . ' - Ingresos : ' . $ingrev . ' - Ano datos : ' . $anorev . ' - fecha datos : ' . $fecharev);
                                                                        $rues["codigo_tamano_empresa"] = '0' . \funcionesRegistrales::determinarTamanoEmpresarialCodigo($dbx, $ciiurev, $ingrev, $anorev, $fecharev);
                                                                        \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - Tamano empresarial calculado del propietario : ' . $rues["codigo_tamano_empresa"]);
                                                                    }
                                                                }
                                                                if ($rues["codigo_tamano_empresa"] == '01' || $rues["codigo_tamano_empresa"] == '02' || $rues["codigo_tamano_empresa"] == '03') {
                                                                    $incluir = 'si';
                                                                }
                                                            } else {
                                                                \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - propietario cancelado en rues');
                                                            }
                                                        } else {
                                                            \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - propietario no localizado en rues');
                                                        }
                                                    }
                                                }
                                            }

                                            if ($incluir == 'no' && $siprop == 'no') {
                                                if ($_SESSION["tramite"]["tamanoempresarial957"] == '1' || $_SESSION["tramite"]["tamanoempresarial957"] == '2' || $_SESSION["tramite"]["tamanoempresarial957"] == '3') {
                                                    $incluir = 'si';
                                                }
                                            }

                                            if ($incluir == 'si') {
                                                \logApi::general2($nameLog, $numliq, 'Aplica descuento de renovacion sobre establecimientos');
                                                $xser = array();
                                                $xser["idsec"] = $regliq["idsec"];
                                                $xser["idservicio"] = '01090153';
                                                $xser["cc"] = $regliq["cc"];
                                                $xser["expediente"] = $regliq["expediente"];
                                                $xser["nombre"] = $regliq["nombre"];
                                                $xser["ano"] = $regliq["ano"];
                                                $xser["cantidad"] = $regliq["cantidad"] * -1;
                                                $xser["valorbase"] = $regliq["valorservicio"];
                                                $xser["porcentaje"] = 5;
                                                $xser["valorservicio"] = \funcionesGenerales::redondear00(round($xser["valorbase"] * $xser["porcentaje"] / 100)) * -1;
                                                $xser["benart7"] = '';
                                                $xser["benley1780"] = '';
                                                $xser["reliquidacion"] = $regliq["reliquidacion"];
                                                $xser["serviciobase"] = 'N';
                                                $xser["clavecontrol"] = $regliq["clavecontrol"];
                                                if ($xser["valorservicio"] != 0) {
                                                    $iAdic++;
                                                    $servAdic[$iAdic] = $xser;
                                                }
                                            } else {
                                                \logApi::general2($nameLog, $numliq, 'No Aplica descuento de renovacion sobre establecimientos');
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // ************************************************************************************ //
                    // ACTOS Y DOCUMENTOS - ESTABLECIMIENTOS
                    // Descuento del 7% sobre todos los servicios de mutacion, inscripcion de actos
                    // Siempre y cuando el expediente sea un establecimiento, sucursal o agencia
                    // El propietario debe ser mypime
                    // Inconvenientes con las casas principales de fuera de la jurisdiccion
                    // Debe estra activado el parametro de aplicabilidad sobre establecimientos
                    // ************************************************************************************ //
                    if ($serv["tipoingreso"] == '04' ||
                            $serv["tipoingreso"] == '05' ||
                            $serv["tipoingreso"] == '07' ||
                            $serv["tipoingreso"] == '14' ||
                            $serv["tipoingreso"] == '15' ||
                            $serv["tipoingreso"] == '17'
                    ) {
                        if ($regliq["cc"] == '' || $regliq["cc"] == CODIGO_EMPRESA) {
                            $incluir = 'no';
                            $siprop = 'no';
                            if (ltrim(trim($regliq["expediente"]), "0") != '') {
                                $exp = \funcionesRegistrales::retornarExpedienteMercantil($dbx, $regliq["expediente"]);
                            } else {
                                $exp = false;
                            }
                            if ($exp && !empty($exp)) {
                                if ($exp["ultanoren"] >= date("Y") - 1) {
                                    $camprop = '';
                                    $matprop = '';
                                    if ($exp["organizacion"] == '02') {
                                        if (isset($exp["propietarios"][1]) && trim($exp["propietarios"][1]["matriculapropietario"] != '')) {
                                            $camprop = $exp["propietarios"][1]["camarapropietario"];
                                            $matprop = $exp["propietarios"][1]["matriculapropietario"];
                                            $siprop = 'si';
                                        }
                                    }
                                    if ($exp["categoria"] == '2' || $exp["categoria"] == '3') {
                                        $camprop = $exp["cpcodcam"];
                                        $matprop = $exp["cpnummat"];
                                        $siprop = 'si';
                                    }
                                    if ($matprop != '' && ($camprop == '' || $camprop == CODIGO_EMPRESA)) {
                                        $exp1 = \funcionesRegistrales::retornarExpedienteMercantil($dbx, trim($matprop));
                                        if ($exp1 && !empty($exp1)) {
                                            if ($exp1["estadomatricula"] == 'MA' || $exp1["estadomatricula"] == 'IA') {
                                                if ($exp1["tamanoempresarial957codigo"] == '') {
                                                    $exp1["tamanoempresarial957codigo"] = \funcionesRegistrales::determinarTamanoEmpresarialCodigo($dbx, $exp1["ciius"][1], $exp1["ingope"], $exp1["anodatos"], $exp1["fechadatos"]);
                                                }
                                                if ($exp1["tamanoempresarial957codigo"] == '1' || $exp1["tamanoempresarial957codigo"] == '2' || $exp1["tamanoempresarial957codigo"] == '3') {
                                                    if ($exp1["ultanoren"] == date("Y") - 2) {
                                                        if (defined('TARIFAESPECIAL_2021_DESCU_CANCELACION_2019') && TARIFAESPECIAL_2021_DESCU_CANCELACION_2019 == 'N') {
                                                            $incluir = 'no';
                                                        } else {
                                                            if (date("Ymd") <= $fcorte || $forzardescuentos1756 == 'S') {
                                                                $poscan = strpos($serv["nombre"], 'CANCELACION');
                                                                $poscan1 = strpos($serv["nombre"], 'CANCELA. PNAT / JURIDICA (AUTOMATICA)');
                                                                $posdis = strpos($serv["nombre"], 'DISOLUCION');
                                                                $posliq = strpos($serv["nombre"], 'LIQUIDACION');
                                                                if ($poscan === false && $poscan1 === false && $posdis === false && $posliq === false) {
                                                                    $incluir = 'no';
                                                                } else {
                                                                    $incluir = 'si';
                                                                }
                                                            }
                                                        }
                                                    }
                                                    if ($exp1["ultanoren"] >= date("Y") - 1) {
                                                        $incluir = 'si';
                                                    }
                                                }
                                                \logApi::general2($nameLog, $numliq, 'tamano empresarial del propietario No. ' . $matprop . ' es ' . $exp1["tamanoempresarial957codigo"]);
                                            }
                                        }
                                    }
                                    if ($matprop != '' && $camprop != '' && $camprop != CODIGO_EMPRESA) {
                                        $rues = \funcionesRues::consultarRegMer($camprop, trim($matprop));
                                        if (isset($rues["codigo_error"]) && $rues["codigo_error"] == '0000') {
                                            if ($rues["codigo_estado_matricula"] == '01' || $rues["codigo_estado_matricula"] == '07' || $rues["codigo_estado_matricula"] == '08') {
                                                \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - Tamano empresarial del propietario : ' . $rues["codigo_tamano_empresa"]);
                                                \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - Datos renovacion del propietario -  Fecha matricula :' . $rues["fecha_matricula"] . ' - Fecha renovacion :' . $rues["fecha_renovacion"] . ' -  Ult ano ren :' . $rues["ultimo_ano_renovado"]);
                                                if ($rues["codigo_tamano_empresa"] == '00') {
                                                    \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - Debe calcular tamano empresarial del propietario');
                                                    $ciiurev = '';
                                                    $ciiux = retornarRegistroMysqliApi($dbx, 'bas_ciius', "idciiunum='" . trim($rues["cod_ciiu_act_econ_pri"]) . "'");
                                                    if ($ciiux && !empty($ciiux)) {
                                                        $ciiurev = $ciiux["idciiu"];
                                                    }
                                                    $ingrev = 0;
                                                    $anorev = '';
                                                    $fecharev = $rues["fecha_renovacion"];
                                                    if (isset($rues["informacion_financiera"]) && !empty($rues["informacion_financiera"])) {
                                                        foreach ($rues["informacion_financiera"] as $fin) {
                                                            $ingrev = $fin["ingresos_actividad_ordinaria"];
                                                            $anorev = $fin["ano_informacion_financiera"];
                                                        }
                                                    }
                                                    \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - Datos del propietario : Ciiu ' . $ciiurev . ' - Ingresos : ' . $ingrev . ' - Ano datos : ' . $anorev . ' - fecha datos : ' . $fecharev);
                                                    $rues["codigo_tamano_empresa"] = '0' . \funcionesRegistrales::determinarTamanoEmpresarialCodigo($dbx, $ciiurev, $ingrev, $anorev, $fecharev);
                                                    \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - Tamano empresarial calculado del propietario : ' . $rues["codigo_tamano_empresa"]);
                                                }
                                                if ($rues["codigo_tamano_empresa"] == '01' || $rues["codigo_tamano_empresa"] == '02' || $rues["codigo_tamano_empresa"] == '03') {
                                                    if (trim(ltrim($rues["ultimo_ano_renovado"], "0")) == '') {
                                                        $rues["ultimo_ano_renovado"] = substr($rues["fecha_matricula"], 0, 4);
                                                    }
                                                    if (trim(ltrim($rues["fecha_renovacion"], "0")) == '') {
                                                        $rues["fecha_renovacion"] = $rues["fecha_matricula"];
                                                    }
                                                    if ($rues["ultimo_ano_renovado"] >= date("Y") - 1) {
                                                        $incluir = 'si';
                                                    }
                                                    if ($rues["ultimo_ano_renovado"] == date("Y") - 2) {
                                                        if (defined('TARIFAESPECIAL_2021_DESCU_CANCELACION_2019') && TARIFAESPECIAL_2021_DESCU_CANCELACION_2019 == 'N') {
                                                            $incluir = 'no';
                                                        } else {
                                                            if (date("Ymd") <= $fcorte || $forzardescuentos1756 == 'S') {
                                                                $poscan = strpos($serv["nombre"], 'CANCELACION');
                                                                $poscan1 = strpos($serv["nombre"], 'CANCELA. PNAT / JURIDICA (AUTOMATICA)');
                                                                $posdis = strpos($serv["nombre"], 'DISOLUCION');
                                                                $posliq = strpos($serv["nombre"], 'LIQUIDACION');
                                                                if ($poscan === false && $poscan1 === false && $posdis === false && $posliq === false) {
                                                                    $incluir = 'no';
                                                                } else {
                                                                    $incluir = 'si';
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            } else {
                                                \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - propietario cancelado en rues');
                                            }
                                        } else {
                                            \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - propietario no localizado en rues');
                                        }
                                    }
                                    if ($incluir == 'no' && $siprop == 'no') {
                                        if ($_SESSION["tramite"]["tamanoempresarial957"] == '1' || $_SESSION["tramite"]["tamanoempresarial957"] == '2' || $_SESSION["tramite"]["tamanoempresarial957"] == '3') {
                                            $incluir = 'si';
                                        }
                                    }
                                }
                                // }
                            }

                            //
                            if ($incluir == 'si') {
                                \logApi::general2($nameLog, $numliq, 'Aplica descuento de actos y documentos sobre actos de establecimientos');
                                $xser = array();
                                $xser["idsec"] = $regliq["idsec"];
                                $xser["idservicio"] = '01090155';
                                $xser["cc"] = $regliq["cc"];
                                $xser["expediente"] = $regliq["expediente"];
                                $xser["nombre"] = $regliq["nombre"];
                                $xser["ano"] = $regliq["ano"];
                                $xser["cantidad"] = $regliq["cantidad"] * -1;
                                $xser["valorbase"] = $regliq["valorservicio"];
                                $xser["porcentaje"] = 7;
                                $xser["valorservicio"] = \funcionesGenerales::redondear00(round($xser["valorbase"] * $xser["porcentaje"] / 100)) * -1;
                                $xser["benart7"] = '';
                                $xser["benley1780"] = '';
                                $xser["reliquidacion"] = $regliq["reliquidacion"];
                                $xser["serviciobase"] = 'N';
                                $xser["clavecontrol"] = $regliq["clavecontrol"];
                                if ($xser["valorservicio"] != 0) {
                                    $iAdic++;
                                    $servAdic[$iAdic] = $xser;
                                }
                            }
                        }
                    }

                    // ****************************************************************************************** //
                    // CERTIFICADOS - MERCANTIL
                    // Descuento del 5% sobre todos los certificados del registro mercantil
                    // Siempre y cuando el comerciante certificado sea mipyme
                    // ****************************************************************************************** //
                    if ($serv["tipoingreso"] == '06' || $serv["tipoingreso"] == '16') {
                        if ($regliq["cc"] == '' || $regliq["cc"] == CODIGO_EMPRESA) {
                            if (ltrim(trim($regliq["expediente"]), "0") != '') {
                                $exp = \funcionesRegistrales::retornarExpedienteMercantil($dbx, $regliq["expediente"]);
                            } else {
                                $exp = false;
                            }
                            if ($exp && !empty($exp)) {
                                // if ($exp["estadomatricula"] == 'MA' || $exp["estadomatricula"] == 'IA') {
                                $incluir = 'no';
                                $siprop = 'no';
                                if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                                    if ($exp["estadomatricula"] == 'MA' || $exp["estadomatricula"] == 'IA') {
                                        if ($mipymeprop != '') {
                                            if ($mipymeprop == '1' || $mipymeprop == '2' || $mipymeprop == '3') {
                                                $incluir = 'si';
                                            } else {
                                                $incluir = 'no';
                                            }
                                        } else {
                                            if ($exp["tamanoempresarial957codigo"] == '') {
                                                $exp["tamanoempresarial957codigo"] = \funcionesRegistrales::determinarTamanoEmpresarialCodigo($dbx, $exp["ciius"][1], $exp["ingope"], $exp["anodatos"], $exp["fechadatos"]);
                                                \logApi::general2($nameLog, $numliq, 'Datos expediente : ' . $regliq["expediente"] . ' - Ciiu: ' . $exp["ciius"][1] . ' - Ingresos: ' . $exp["ingope"] . ' - Ano datos: ' . $exp["anodatos"] . ' - Fecha datos: ' . $exp["fechadatos"]);
                                                \logApi::general2($nameLog, $numliq, 'Tamano empresairal calculado : ' . $regliq["expediente"] . ' - Tamano : ' . $exp["tamanoempresarial957codigo"]);
                                            }
                                            if ($exp["tamanoempresarial957codigo"] == '1' || $exp["tamanoempresarial957codigo"] == '2' || $exp["tamanoempresarial957codigo"] == '3') {
                                                $incluir = 'si';
                                            }
                                        }
                                        if ($incluir == 'si' && $mipymeprop == '') {
                                            if ($exp["ultanoren"] < date("Y") - 1) {
                                                $incluir = 'no';
                                            }
                                        }
                                    }
                                }
                                if ($exp["organizacion"] == '02' || ($exp["organizacion"] > '02' && ($exp["categoria"] == '2' || $exp["categoria"] == '3'))) {
                                    // if ($exp["ultanoren"] >= date("Y") - 1) {
                                    $camprop = '';
                                    $matprop = '';
                                    if ($exp["organizacion"] == '02') {
                                        if (isset($exp["propietarios"][1]) && trim($exp["propietarios"][1]["matriculapropietario"] != '')) {
                                            $camprop = $exp["propietarios"][1]["camarapropietario"];
                                            $matprop = $exp["propietarios"][1]["matriculapropietario"];
                                            $siprop = 'si';
                                        }
                                    }
                                    if ($exp["categoria"] == '2' || $exp["categoria"] == '3') {
                                        $camprop = $exp["cpcodcam"];
                                        $matprop = $exp["cpnummat"];
                                        $siprop = 'si';
                                    }
                                    if ($matprop != '' && ($camprop == '' || $camprop == CODIGO_EMPRESA)) {
                                        $exp1 = \funcionesRegistrales::retornarExpedienteMercantil($dbx, trim($matprop));
                                        if ($exp1 && !empty($exp1)) {
                                            if ($exp1["estadomatricula"] == 'MA' || $exp1["estadomatricula"] == 'IA') {
                                                if ($exp1["tamanoempresarial957codigo"] == '') {
                                                    $exp1["tamanoempresarial957codigo"] = \funcionesRegistrales::determinarTamanoEmpresarialCodigo($dbx, $exp1["ciius"][1], $exp1["ingope"], $exp1["anodatos"], $exp1["fechadatos"]);
                                                    \logApi::general2($nameLog, $numliq, 'Establecimiento ' . $regliq["expediente"] . ' - Datos propietario : ' . $exp1["matricula"] . ' - Ciiu: ' . $exp1["ciius"][1] . ' - Ingresos: ' . $exp1["ingope"] . ' - Ano datos: ' . $exp1["anodatos"] . ' - Fecha datos: ' . $exp1["fechadatos"]);
                                                    \logApi::general2($nameLog, $numliq, 'Tamano empresairal calculado : ' . $regliq["expediente"] . ' - Tamano : ' . $exp["tamanoempresarial957codigo"]);
                                                }
                                                if ($exp1["tamanoempresarial957codigo"] == '' || $exp1["tamanoempresarial957codigo"] == '1' || $exp1["tamanoempresarial957codigo"] == '2' || $exp1["tamanoempresarial957codigo"] == '3') {
                                                    if ($exp1["ultanoren"] >= date("Y") - 1) {
                                                        $incluir = 'si';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if ($matprop != '' && $camprop != '' && $camprop != CODIGO_EMPRESA) {
                                        $rues = \funcionesRues::consultarRegMer($camprop, trim($matprop));
                                        if (isset($rues["codigo_error"]) && $rues["codigo_error"] == '0000') {
                                            if ($rues["codigo_estado_matricula"] == '01' || $rues["codigo_estado_matricula"] == '07' || $rues["codigo_estado_matricula"] == '08') {
                                                if ($rues["codigo_tamano_empresa"] == '00') {
                                                    \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - Debe calcular tamano empresarial del propietario');
                                                    $ciiurev = '';
                                                    $ciiux = retornarRegistroMysqliApi($dbx, 'bas_ciius', "idciiunum='" . trim($rues["cod_ciiu_act_econ_pri"]) . "'");
                                                    if ($ciiux && !empty($ciiux)) {
                                                        $ciiurev = $ciiux["idciiu"];
                                                    }
                                                    $ingrev = 0;
                                                    $anorev = '';
                                                    $fecharev = $rues["fecha_renovacion"];
                                                    if (isset($rues["informacion_financiera"]) && !empty($rues["informacion_financiera"])) {
                                                        foreach ($rues["informacion_financiera"] as $fin) {
                                                            $ingrev = $fin["ingresos_actividad_ordinaria"];
                                                            $anorev = $fin["ano_informacion_financiera"];
                                                        }
                                                    }
                                                    \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - Datos del propietario : Ciiu ' . $ciiurev . ' - Ingresos : ' . $ingrev . ' - Ano datos : ' . $anorev . ' - fecha datos : ' . $fecharev);
                                                    $rues["codigo_tamano_empresa"] = '0' . \funcionesRegistrales::determinarTamanoEmpresarialCodigo($dbx, $ciiurev, $ingrev, $anorev, $fecharev);
                                                    \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["expediente"] . ' - Tamano empresarial calculado del propietario : ' . $rues["codigo_tamano_empresa"]);
                                                }
                                                if ($rues["codigo_tamano_empresa"] == '00' || $rues["codigo_tamano_empresa"] == '01' || $rues["codigo_tamano_empresa"] == '02' || $rues["codigo_tamano_empresa"] == '03') {
                                                    if (trim(ltrim($rues["ultimo_ano_renovado"], "0")) == '') {
                                                        $rues["ultimo_ano_renovado"] = substr($rues["fecha_matricula"], 0, 4);
                                                    }
                                                    if (trim(ltrim($rues["fecha_renovacion"], "0")) == '') {
                                                        $rues["fecha_renovacion"] = $rues["fecha_matricula"];
                                                    }
                                                    if ($rues["ultimo_ano_renovado"] >= date("Y") - 1) {
                                                        $incluir = 'si';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if ($incluir == 'no' && $siprop == 'no') {
                                        if ($_SESSION["tramite"]["tamanoempresarial957"] == '1' || $_SESSION["tramite"]["tamanoempresarial957"] == '2' || $_SESSION["tramite"]["tamanoempresarial957"] == '3') {
                                            $incluir = 'si';
                                        }
                                    }
                                    // }
                                }
                                // }

                                if ($incluir == 'si') {
                                    \logApi::general2($nameLog, $numliq, 'Aplica descuento de certificados');
                                    $xser = array();
                                    $xser["idsec"] = $regliq["idsec"];
                                    $xser["idservicio"] = '';
                                    if ($serv["tipocertificado"] == 'CerMat') {
                                        $xser["idservicio"] = '01090157';
                                    }
                                    if ($serv["tipocertificado"] == 'CerExi' || $serv["tipocertificado"] == 'EspRegMer') {
                                        $xser["idservicio"] = '01090158';
                                    }
                                    if ($serv["tipocertificado"] == 'CerEsdal') {
                                        $xser["idservicio"] = '01090160';
                                    }
                                    if ($serv["tipocertificado"] == 'CerLibRegMer') {
                                        $xser["idservicio"] = '01090159';
                                    }
                                    if ($xser["idservicio"] == '') {
                                        if ($exp["organizacion"] == '01' || $exp["organizacion"] == '02' || $exp["categoria"] == '2' || $exp["categoria"] == '3') {
                                            $xser["idservicio"] = '01090157';
                                        }
                                        if ($exp["organizacion"] > '02' && $exp["organizacion"] != '12' && $exp["organizacion"] != '14' && $exp["categoria"] == '1') {
                                            $xser["idservicio"] = '01090158';
                                        }
                                        if (($exp["organizacion"] == '12' || $exp["organizacion"] == '14') && $exp["categoria"] == '1') {
                                            $xser["idservicio"] = '01090160';
                                        }
                                    }
                                    $xser["cc"] = $regliq["cc"];
                                    $xser["expediente"] = $regliq["expediente"];
                                    $xser["nombre"] = $regliq["nombre"];
                                    $xser["ano"] = $regliq["ano"];
                                    $xser["cantidad"] = $regliq["cantidad"] * -1;
                                    $xser["valorbase"] = $regliq["valorservicio"];
                                    $xser["porcentaje"] = 5;
                                    $valuni = $regliq["valorservicio"] / $regliq["cantidad"];
                                    $xser["valorservicio"] = \funcionesGenerales::redondear00($valuni * $xser["porcentaje"] / 100) * $regliq["cantidad"] * -1;
                                    $xser["benart7"] = '';
                                    $xser["benley1780"] = '';
                                    $xser["reliquidacion"] = $regliq["reliquidacion"];
                                    $xser["serviciobase"] = 'N';
                                    $xser["clavecontrol"] = $regliq["clavecontrol"];
                                    if ($xser["valorservicio"] != 0) {
                                        $iAdic++;
                                        $servAdic[$iAdic] = $xser;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // ************************************************************************************ //
        // Adiciona servicios de tarifa especial
        // ************************************************************************************ //
        if ($iAdic != 0) {
            foreach ($servAdic as $adic) {
                $xser = array();
                $xser["idsec"] = $adic["idsec"];
                $xser["idservicio"] = $adic["idservicio"];
                $xser["cc"] = $adic["cc"];
                $xser["expediente"] = $adic["expediente"];
                $xser["nombre"] = $adic["nombre"];
                $xser["ano"] = $adic["ano"];
                $xser["cantidad"] = $adic["cantidad"] * -1;
                $xser["valorbase"] = $adic["valorbase"];
                $xser["porcentaje"] = $adic["porcentaje"];
                $xser["valorservicio"] = $adic["valorservicio"];
                $xser["benart7"] = $adic["benart7"];
                $xser["benart7"] = $adic["benart7"];
                $xser["reliquidacion"] = $adic["reliquidacion"];
                $xser["serviciobase"] = $adic["serviciobase"];

                $xser["pagoafiliacion"] = '';
                $xser["ir"] = '';
                $xser["iva"] = '';
                $xser["idalerta"] = 0;
                $xser["expedienteafiliado"] = 0;
                $xser["porcentajeiva"] = 0;
                $xser["valoriva"] = 0;
                $xser["servicioiva"] = '';

                $xser["porcentajedescuento"] = 0;
                $xser["porcentajedescuento"] = 0;
                $xser["serviciodescuento"] = '';

                $xser["clavecontrol"] = $adic["clavecontrol"];

                $sec++;
                $_SESSION["tramite"]["liquidacion"][$sec] = $xser;
            }
        }

        // ************************************************************************************ //
        // Recalcula totales
        // ************************************************************************************ //
        $_SESSION["tramite"]["valorbruto"] = 0;
        $_SESSION["tramite"]["valorbaseiva"] = 0;
        $_SESSION["tramite"]["valoriva"] = 0;
        $_SESSION["tramite"]["valortotal"] = 0;
        foreach ($_SESSION["tramite"]["liquidacion"] as $regliq) {
            $serv = retornarRegistroMysqliApi($dbx, 'mreg_servicios', "idservicio='" . $regliq["idservicio"] . "'");
            if (isset($serv["esiva"]) && $serv["esiva"] == 'S') {
                $_SESSION["tramite"]["valoriva"] = $_SESSION["tramite"]["valoriva"] + $regliq["valorservicio"];
            } else {
                $_SESSION["tramite"]["valorbruto"] = $_SESSION["tramite"]["valorbruto"] + $regliq["valorservicio"];
            }
            if (isset($serv["esgravadoiva"]) && $serv["esgravadoiva"] == 'S') {
                $_SESSION["tramite"]["valorbaseiva"] = $_SESSION["tramite"]["valorbaseiva"] + $regliq["valorservicio"];
            }
            $_SESSION["tramite"]["valortotal"] = $_SESSION["tramite"]["valortotal"] + $regliq["valorservicio"];
        }

        // ************************************************************************************ //
        // regraba liquidacion
        // ************************************************************************************ //
        \logApi::general2($nameLog, $numliq, '');
        \funcionesRegistrales::grabarLiquidacionMreg($dbx);
        return true;
    }

    public static function calcularTarifaEspecial2021Rues($dbx, $numliq, $lista = array()) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        $nameLog = 'calcularTarifaEspecial2021Rues_' . date("Ymd");

        //
        if (!defined('ACTIVADO_DECRETO_TARIFAESPECIAL_2021') || ACTIVADO_DECRETO_TARIFAESPECIAL_2021 == '' || ACTIVADO_DECRETO_TARIFAESPECIAL_2021 == 'N') {
            return false;
        }

        //
        $fcorte = retornarRegistroMysqliApi($dbx, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
        \logApi::general2($nameLog, $numliq, 'fecha corte renovacion : ' . $fcorte);

        //
        $iAdic = 0;
        $servAdic = array();
        $servAdic["servicios"] = array();
        $servAdic["descuentos"] = array();

        // ************************************************************************************ //
        // Borra servicios de tarifa especial previamente calculados
        // ************************************************************************************ //
        $dets = array();
        foreach ($lista as $key => $aliq) {
            $aliq["clavecontrol"] = '';
            if ($aliq["valor_liquidacion"] > 0) {
                $aliq["clavecontrol"] = \funcionesGenerales::generarAleatorioAlfanumerico10($dbx, '');
            }
            if ($aliq["codigo_servicio"] < '01090151' || $aliq["codigo_servicio"] > '01090160') {
                $dets[] = $aliq;
                $servAdic["servicios"][] = $aliq;
            }
            $sec = $key;
        }
        $lista = $dets;

        //
        $tiene1780 = '';
        foreach ($lista as $regliq) {
            if ($regliq["codigo_servicio"] == '01090110' || $regliq["codigo_servicio"] == '01090111') {
                $tiene1780 = 'si';
            }
        }

        //
        $mipymeprop = '';
        foreach ($lista as $regliq) {

            if ($regliq["valor_liquidacion"] > 0) {

                $serv = retornarRegistroMysqliApi($dbx, 'mreg_servicios', "idservicio='" . $regliq["codigo_servicio"] . "'");
                if ($serv && !empty($serv)) {

                    // ************************************************************************************ //
                    // RENOVACION - PRINCIPALES 
                    // Aplicabilidad de descuentos sobre renovacion
                    // Personas naturales y juri­dicas principales
                    // Siempre y cuando sea mipyme 
                    // Esta al dia
                    // ************************************************************************************ //
                    if ($serv["tipoingreso"] == '03' || $serv["tipoingreso"] == '13') {
                        if ($regliq["ano_renovacion"] == date("Y")) {
                            \logApi::general2($nameLog, $numliq, 'Ano a renovar matricula  : ' . $regliq["matricula_servicio"] . ' - ' . $regliq["ano_renovacion"]);
                            $incluir = 'no';
                            $exp = \funcionesRegistrales::retornarExpedienteMercantil($dbx, $regliq["matricula_servicio"]);
                            if ($exp && !empty($exp)) {
                                if ($exp["organizacion"] == '01' && $exp["estadomatricula"] == 'MA') {
                                    $incluir = 'si';
                                } else {
                                    if ($exp["organizacion"] != '02' && $exp["categoria"] == '1' && ($exp["estadomatricula"] == 'MA' || $exp["estadomatricula"] == 'IA')) {
                                        $incluir = 'si';
                                    }
                                }
                            }

                            //
                            if ($incluir == 'si') {
                                $incluir = 'no';
                                $esmipyme = '';
                                \logApi::general2($nameLog, $numliq, 'Tamano empresarial del expediente : ' . $regliq["matricula_servicio"] . ' - Tamano : ' . $exp["tamanoempresarial957codigo"]);
                                if ($exp["tamanoempresarial957codigo"] == '' || $exp["tamanoempresarial957codigo"] == '00') {
                                    $esmipyme = \funcionesRegistrales::determinarTamanoEmpresarialCodigo($dbx, $exp["ciius"][1], $exp["ingope"], $exp["anodatos"], $exp["fechadatos"]);
                                    \logApi::general2($nameLog, $numliq, 'Datos expediente : ' . $regliq["matricula_servicio"] . ' - Ciiu: ' . $exp["ciius"][1] . ' - Ingresos: ' . $exp["ingope"] . ' - Ano datos: ' . $exp["anodatos"] . ' - Fecha datos: ' . $exp["fechadatos"]);
                                    \logApi::general2($nameLog, $numliq, 'Tamano empresairal calculado : ' . $regliq["matricula_servicio"] . ' - Tamano : ' . $esmipyme);
                                } else {
                                    $esmipyme = $exp["tamanoempresarial957codigo"];
                                    \logApi::general2($nameLog, $numliq, 'Tamano empresairal del tramite : ' . $regliq["matricula_servicio"] . ' - Tamano : ' . $esmipyme);
                                }
                                if ($esmipyme == '1' || $esmipyme == '2' || $esmipyme == '3') {
                                    $incluir = 'si';
                                }
                                $mipymeprop = $esmipyme;
                            }

                            //
                            if ($incluir == 'si') {
                                $incluir = 'no';
                                if (substr($exp["fechamatricula"], 0, 4) == date("Y")) {
                                    $incluir = 'si';
                                }
                                if (substr($exp["fechamatricula"], 0, 4) < date("Y")) {
                                    if (date("Ymd") <= $fcorte) {
                                        if ($exp["ultanoren"] == date("Y")) {
                                            $incluir = 'si';
                                        } else {
                                            if ($exp["ultanoren"] == date("Y") - 1) {
                                                $incluir = 'si';
                                            } else {
                                                $anosrenovados = 0;
                                                foreach ($lista as $rx) {
                                                    if ($rx["matricula_servicio"] == $regliq["matricula_servicio"] && substr($rx["codigo_servicio"], 0, 6) == '010202') {
                                                        $anosrenovados++;
                                                    }
                                                }
                                                if ($anosrenovados >= 2) {
                                                    $incluir = 'si';
                                                }
                                            }
                                        }
                                    }
                                    if (date("Ymd") > $fcorte) {
                                        if ($exp["ultanoren"] == date("Y")) {
                                            if ($exp["fecharenovacion"] <= fcorte) {
                                                $incluir = 'si';
                                            }
                                        }
                                    }
                                }
                            }

                            //
                            if ($incluir == 'si') {
                                if ($tiene1780 != 'si') {
                                    \logApi::general2($nameLog, $numliq, 'Aplica descuento de renovacion pnat o pjur');
                                    $xser = array();
                                    $xser["idsec"] = '';
                                    if ($exp["organizacion"] == '01') {
                                        $xser["idservicio"] = '01090151';
                                    }
                                    if ($exp["organizacion"] > '02' && $exp["organizacion"] != '12' && $exp["organizacion"] != '14') {
                                        $xser["idservicio"] = '01090152';
                                    }
                                    if ($exp["organizacion"] == '12' || $exp["organizacion"] == '14') {
                                        $xser["idservicio"] = '01090154';
                                    }
                                    $xser["cc"] = '';
                                    $xser["expediente"] = $regliq["matricula_servicio"];
                                    $xser["nombre"] = $regliq["nombre_matriculado"];
                                    $xser["ano"] = $regliq["ano_renovacion"];
                                    $xser["cantidad"] = $regliq["cantidad_servicio"] * -1;
                                    $xser["valorbase"] = $regliq["valor_liquidacion"];
                                    $xser["porcentaje"] = 5;
                                    $xser["valorservicio"] = \funcionesGenerales::redondear00(round($xser["valorbase"] * $xser["porcentaje"] / 100)) * -1;
                                    $xser["benart7"] = '';
                                    $xser["benley1780"] = '';
                                    $xser["reliquidacion"] = '';
                                    $xser["serviciobase"] = 'N';
                                    $xser["clavecontrol"] = $regliq["clavecontrol"];
                                    if ($xser["valorservicio"] != 0) {
                                        $iAdic++;
                                        $servAdic["descuentos"][$iAdic] = $xser;
                                    }
                                }
                            } else {
                                \logApi::general2($nameLog, $numliq, 'No Aplica descuento de renovacion  pnat o pjur');
                            }
                        }
                    }

                    // ************************************************************************************ //
                    // ACTOS Y DOCUMENTOS - PRINCIPALES
                    // Descuento del 7% sobre todos los servicios de mutacion, inscripcion de actos
                    // Siempre y cuando el expediente afectado sea mypime y se encuentre al di­a
                    // Persona juri­dicas principales y personas naturales
                    // ************************************************************************************ //
                    if ($serv["tipoingreso"] == '04' ||
                            $serv["tipoingreso"] == '05' ||
                            $serv["tipoingreso"] == '07' ||
                            $serv["tipoingreso"] == '14' ||
                            $serv["tipoingreso"] == '15' ||
                            $serv["tipoingreso"] == '17'
                    ) {
                        if (ltrim(trim($regliq["matricula_servicio"]), "0") != '') {
                            $exp = \funcionesRegistrales::retornarExpedienteMercantil($dbx, $regliq["matricula_servicio"]);
                            if ($exp && !empty($exp)) {
                                if ($exp["estadomatricula"] == 'MA' || $exp["estadomatricula"] == 'IA') {
                                    $incluir = 'no';
                                    if ($exp["organizacion"] == '01') {
                                        $incluir = 'si';
                                    } else {
                                        if ($exp["organizacion"] != '02' && $exp["categoria"] == '1') {
                                            $incluir = 'si';
                                        }
                                    }
                                    if ($incluir == 'si') {
                                        $incluir = 'no';
                                        if ($exp["tamanoempresarial957codigo"] == '') {
                                            $exp["tamanoempresarial957codigo"] = \funcionesRegistrales::determinarTamanoEmpresarialCodigo($dbx, $exp["ciius"][1], $exp["ingope"], $exp["anodatos"], $exp["fechadatos"]);
                                            \logApi::general2($nameLog, $numliq, 'Datos expediente : ' . $regliq["expediente"] . ' - Ciiu: ' . $exp["ciius"][1] . ' - Ingresos: ' . $exp["ingope"] . ' - Ano datos: ' . $exp["anodatos"] . ' - Fecha datos: ' . $exp["fechadatos"]);
                                            \logApi::general2($nameLog, $numliq, 'Tamano empresairal calculado : ' . $regliq["matricula_servicio"] . ' - Tamano : ' . $exp["tamanoempresarial957codigo"]);
                                        }
                                        if ($exp["tamanoempresarial957codigo"] == '1' || $exp["tamanoempresarial957codigo"] == '2' || $exp["tamanoempresarial957codigo"] == '3') {
                                            $incluir = 'no';
                                            if (substr($exp["fechamatricula"], 0, 4) == date("Y")) {
                                                $incluir = 'si';
                                            }
                                            if (substr($exp["fechamatricula"], 0, 4) < date("Y")) {
                                                if ($exp["ultanoren"] == date("Y") - 2) {
                                                    if (defined('TARIFAESPECIAL_2021_DESCU_CANCELACION_2019') && TARIFAESPECIAL_2021_DESCU_CANCELACION_2019 == 'N') {
                                                        $incluir = 'no';
                                                    } else {
                                                        $poscan = strpos($serv["nombre"], 'CANCELACION');
                                                        $poscan1 = strpos($serv["nombre"], 'CANCELA. PNAT / JURIDICA (AUTOMATICA)');
                                                        $posdis = strpos($serv["nombre"], 'DISOLUCION');
                                                        $posliq = strpos($serv["nombre"], 'LIQUIDACION');
                                                        if ($poscan === false && $poscan1 === false && $posdis === false && $posliq === false) {
                                                            $incluir = 'no';
                                                        } else {
                                                            if ($exp["organizacion"] == '12' || $exp["organizacion"] == '14') {
                                                                $incluir = 'no';
                                                            } else {
                                                                $incluir = 'si';
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($exp["ultanoren"] == date("Y") - 1) {
                                                    $incluir = 'si';
                                                }
                                                if ($exp["ultanoren"] == date("Y")) {
                                                    $incluir = 'si';
                                                }
                                            }
                                            if ($incluir == 'no') {
                                                \logApi::general2($nameLog, $numliq, 'No se encuentra renovado : ' . $regliq["matricula_servicio"] . ' - Fecha matricula : ' . $exp["fechamatricula"] . ' - Fecha renovacion : ' . $exp["fecharenovacion"] . ' - Ult ano ren : ' . $exp["ultanoren"]);
                                            }
                                        }
                                    }

                                    if ($incluir == 'si') {
                                        \logApi::general2($nameLog, $numliq, 'Aplica descuento de actos y documentos pnat o pjur');
                                        $xser = array();
                                        $xser["idsec"] = '';
                                        if ($exp["organizacion"] == '01') {
                                            $xser["idservicio"] = '01090155';
                                        }
                                        if ($exp["organizacion"] > '02' && $exp["organizacion"] != '12' && $exp["organizacion"] != '14') {
                                            $xser["idservicio"] = '01090155';
                                        }
                                        if ($exp["organizacion"] == '12' || $exp["organizacion"] == '14') {
                                            $xser["idservicio"] = '01090156';
                                        }
                                        $xser["cc"] = '';
                                        $xser["expediente"] = $regliq["matricula_servicio"];
                                        $xser["nombre"] = $regliq["nombre_matriculado"];
                                        $xser["ano"] = $regliq["ano_renovacion"];
                                        $xser["cantidad"] = $regliq["cantidad_servicio"] * -1;
                                        $xser["valorbase"] = $regliq["valor_liquidacion"];
                                        $xser["porcentaje"] = 7;
                                        $xser["valorservicio"] = \funcionesGenerales::redondear00(round($xser["valorbase"] * $xser["porcentaje"] / 100)) * -1;
                                        $xser["benart7"] = '';
                                        $xser["benley1780"] = '';
                                        $xser["reliquidacion"] = '';
                                        $xser["serviciobase"] = 'N';
                                        $xser["clavecontrol"] = $regliq["clavecontrol"];
                                        if ($xser["valorservicio"] != 0) {
                                            $iAdic++;
                                            $servAdic["descuentos"][$iAdic] = $xser;
                                        }
                                    } else {
                                        \logApi::general2($nameLog, $numliq, 'No Aplica descuento de actos y documentos pnat o pjur');
                                    }
                                }
                            }
                        }
                    }

                    // ************************************************************************************ //
                    // RENOVACION - ESTABLECIMIENTOS
                    // Aplicabilidad de descuentos sobre renovacion
                    // Establecimientos, sucursales y agencias
                    // Siempre y cuando sea mipyme el propietario
                    // Esta al dia
                    // ************************************************************************************ //
                    if ($serv["tipoingreso"] == '03' || $serv["tipoingreso"] == '13') {
                        if ($regliq["ano_renovacion"] == date("Y")) {
                            $incluir = 'no';
                            $siprop = 'no';
                            $exp = \funcionesRegistrales::retornarExpedienteMercantil($dbx, $regliq["matricula_servicio"]);
                            if ($exp && !empty($exp)) {
                                if ($exp["estadomatricula"] == 'MA') {
                                    if ($exp["organizacion"] == '02' || ($exp["organizacion"] > '02' && $exp["categoria"] == '2') || ($exp["organizacion"] > '02' && $exp["categoria"] == '3')) {
                                        if (substr($exp["fechamatricula"], 0, 4) == date("Y")) {
                                            $incluir = 'si';
                                        }
                                        if (substr($exp["fechamatricula"], 0, 4) < date("Y")) {
                                            if (date("Ymd") <= $fcorte) {
                                                $anospendientes = 0;
                                                $anosrenovados = 0;
                                                for ($ix = $exp["ultanoren"] + 1; $ix <= date("Y"); $ix++) {
                                                    $anospendientes++;
                                                    foreach ($lista as $rx) {
                                                        if ($rx["matricula_servicio"] == $regliq["matricula_servicio"] && substr($rx["codigo_servicio"], 0, 6) == '010202') {
                                                            if ($rx["ano_renovacion"] == $ix) {
                                                                $anosrenovados++;
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($anospendientes == $anosrenovados) {
                                                    $incluir = 'si';
                                                }
                                            }
                                            if (date("Ymd") > $fcorte) {
                                                if ($exp["ultanoren"] == date("Y")) {
                                                    if ($exp["fecharenovacion"] <= fcorte) {
                                                        $incluir = 'si';
                                                    }
                                                }
                                            }
                                        }

                                        if ($incluir == 'si') {
                                            if ($mipymeprop != '') {
                                                if ($mipymeprop == '1' || $mipymeprop == '2' || $mipymeprop == '3') {
                                                    $incluir = 'si';
                                                } else {
                                                    $incluir = 'no';
                                                }
                                            } else {
                                                $incluir = 'no';
                                                $camprop = '';
                                                $matprop = '';
                                                if ($exp["organizacion"] == '02') {
                                                    if (isset($exp["propietarios"][1]) && trim($exp["propietarios"][1]["matriculapropietario"] != '')) {
                                                        $camprop = $exp["propietarios"][1]["camarapropietario"];
                                                        $matprop = $exp["propietarios"][1]["matriculapropietario"];
                                                        $siprop = 'si';
                                                    }
                                                }
                                                if ($exp["categoria"] == '2' || $exp["categoria"] == '3') {
                                                    $camprop = $exp["cpcodcam"];
                                                    $matprop = $exp["cpnummat"];
                                                    $siprop = 'si';
                                                }
                                                if ($matprop != '' && ($camprop == '' || $camprop == CODIGO_EMPRESA)) {
                                                    \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - propietario es local : ' . $matprop);
                                                    $exp1 = \funcionesRegistrales::retornarExpedienteMercantil($dbx, $matprop);
                                                    if ($exp1 && !empty($exp1)) {
                                                        \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - Tamano empresarial del propietario : ' . $exp1["tamanoempresarial957codigo"]);
                                                        \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - Datos renovacion del propietario -  Fecha matricula :' . $exp1["fechamatricula"] . ' - Fecha renovacion :' . $exp1["fecharenovacion"] . ' -  Ult ano ren :' . $exp1["ultanoren"]);
                                                        if ($exp1["estadomatricula"] == 'MA' || $exp1["estadomatricula"] == 'IA') {
                                                            if ($exp1["tamanoempresarial957codigo"] == '' || $exp1["tamanoempresarial957codigo"] == '1' || $exp1["tamanoempresarial957codigo"] == '2' || $exp1["tamanoempresarial957codigo"] == '3') {
                                                                $incluir = 'si';
                                                                if (substr($exp1["fechamatricula"], 0, 4) == date("Y")) {
                                                                    $incluir = 'si';
                                                                }
                                                                if (substr($exp1["fechamatricula"], 0, 4) < date("Y")) {
                                                                    if (date("Ymd") <= $fcorte) {
                                                                        if ($exp1["ultanoren"] == date("Y")) {
                                                                            $incluir = 'si';
                                                                        } else {
                                                                            if ($exp1["ultanoren"] == date("Y") - 1) {
                                                                                $incluir = 'si';
                                                                            } else {
                                                                                $anospendientes = 0;
                                                                                $anosrenovados = 0;
                                                                                for ($ix = $exp1["ultanoren"] + 1; $ix <= date("Y"); $ix++) {
                                                                                    $anospendientes++;
                                                                                    foreach ($_SESSION["tramite"]["liquidacion"] as $rx) {
                                                                                        if ($rx["expediente"] == $matprop && substr($rx["idservicio"], 0, 6) == '010202') {
                                                                                            if ($rx["ano"] == $ix) {
                                                                                                $anosrenovados++;
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                                if ($anospendientes == $anosrenovados) {
                                                                                    $incluir = 'si';
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                    if (date("Ymd") > $fcorte) {
                                                                        if ($exp1["ultanoren"] == date("Y")) {
                                                                            if ($exp1["fecharenovacion"] <= fcorte) {
                                                                                $incluir = 'si';
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($matprop != '' && $camprop != '' && $camprop != CODIGO_EMPRESA) {
                                                    \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - propietario fuera de la jurisdiccion : ' . $camprop . '-' . $matprop);
                                                    $rues = \funcionesRues::consultarRegMer($camprop, trim($matprop));
                                                    if (isset($rues["codigo_error"]) && $rues["codigo_error"] == '0000') {
                                                        if ($rues["codigo_estado_matricula"] == '01') {
                                                            \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - Tamano empresarial del propietario : ' . $rues["codigo_tamano_empresa"]);
                                                            \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - Datos renovacion del propietario -  Fecha matricula :' . $rues["fecha_matricula"] . ' - Fecha renovacion :' . $rues["fecha_renovacion"] . ' -  Ult ano ren :' . $rues["ultimo_ano_renovado"]);
                                                            // $rues["codigo_tamano_empresa"] = '00';
                                                            if ($rues["codigo_tamano_empresa"] == '00') {
                                                                \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - Debe calcular tamano empresarial del propietario');
                                                                $ciiurev = '';
                                                                $ciiux = retornarRegistroMysqliApi($dbx, 'bas_ciius', "idciiunum='" . trim($rues["cod_ciiu_act_econ_pri"]) . "'");
                                                                if ($ciiux && !empty($ciiux)) {
                                                                    $ciiurev = $ciiux["idciiu"];
                                                                }
                                                                $ingrev = 0;
                                                                $anorev = '';
                                                                $fecharev = $rues["fecha_renovacion"];
                                                                if (isset($rues["informacion_financiera"]) && !empty($rues["informacion_financiera"])) {
                                                                    foreach ($rues["informacion_financiera"] as $fin) {
                                                                        $ingrev = $fin["ingresos_actividad_ordinaria"];
                                                                        $anorev = $fin["ano_informacion_financiera"];
                                                                    }
                                                                }
                                                                \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - Datos del propietario : Ciiu ' . $ciiurev . ' - Ingresos : ' . $ingrev . ' - Ano datos : ' . $anorev . ' - fecha datos : ' . $fecharev);
                                                                $rues["codigo_tamano_empresa"] = '0' . \funcionesRegistrales::determinarTamanoEmpresarialCodigo($dbx, $ciiurev, $ingrev, $anorev, $fecharev);
                                                                \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - Tamano empresarial calculado del propietario : ' . $rues["codigo_tamano_empresa"]);
                                                            }
                                                            if ($rues["codigo_tamano_empresa"] == '01' || $rues["codigo_tamano_empresa"] == '02' || $rues["codigo_tamano_empresa"] == '03') {
                                                                $incluir = 'si';
                                                            }
                                                        } else {
                                                            \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - propietario cancelado en rues');
                                                        }
                                                    } else {
                                                        \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - propietario no localizado en rues');
                                                    }
                                                }
                                            }
                                        }

                                        if ($incluir == 'si') {
                                            \logApi::general2($nameLog, $numliq, 'Aplica descuento de renovacion sobre establecimientos');
                                            $xser = array();
                                            $xser["idsec"] = '';
                                            $xser["idservicio"] = '01090153';
                                            $xser["cc"] = '';
                                            $xser["expediente"] = $regliq["matricula_servicio"];
                                            $xser["nombre"] = $regliq["nombre_matriculado"];
                                            $xser["ano"] = $regliq["ano_renovacion"];
                                            $xser["cantidad"] = $regliq["cantidad_servicio"] * -1;
                                            $xser["valorbase"] = $regliq["valor_liquidacion"];
                                            $xser["porcentaje"] = 5;
                                            $xser["valorservicio"] = \funcionesGenerales::redondear00(round($xser["valorbase"] * $xser["porcentaje"] / 100)) * -1;
                                            $xser["benart7"] = '';
                                            $xser["benley1780"] = '';
                                            $xser["reliquidacion"] = '';
                                            $xser["serviciobase"] = '';
                                            $xser["clavecontrol"] = $regliq["clavecontrol"];
                                            if ($xser["valorservicio"] != 0) {
                                                $iAdic++;
                                                $servAdic["descuentos"][$iAdic] = $xser;
                                            }
                                        } else {
                                            \logApi::general2($nameLog, $numliq, 'No Aplica descuento de renovacion sobre establecimientos');
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // ************************************************************************************ //
                    // ACTOS Y DOCUMENTOS - ESTABLECIMIENTOS
                    // Descuento del 7% sobre todos los servicios de mutacion, inscripciÃ³n de actos
                    // Siempre y cuando el expediente sea un establecimiento, sucursal o agencia
                    // El propietario debe ser mypime
                    // Inconvenientes con las casas principales de fuera de la jurisdicciÃ³n
                    // Debe estra activado el parÃ¡metro de aplicabilidad sobre establecimientos
                    // ************************************************************************************ //
                    if ($serv["tipoingreso"] == '04' ||
                            $serv["tipoingreso"] == '05' ||
                            $serv["tipoingreso"] == '07' ||
                            $serv["tipoingreso"] == '14' ||
                            $serv["tipoingreso"] == '15' ||
                            $serv["tipoingreso"] == '17'
                    ) {
                        $incluir = 'no';
                        $siprop = 'no';
                        if (ltrim(trim($regliq["matricula_servicio"]), "0") != '') {
                            $exp = \funcionesRegistrales::retornarExpedienteMercantil($dbx, $regliq["matricula_servicio"]);
                            if ($exp && !empty($exp)) {
                                // if ($exp["estadomatricula"] == 'MA') {
                                if ($exp["ultanoren"] >= date("Y") - 1) {
                                    $camprop = '';
                                    $matprop = '';
                                    if ($exp["organizacion"] == '02') {
                                        if (isset($exp["propietarios"][1]) && trim($exp["propietarios"][1]["matriculapropietario"] != '')) {
                                            $camprop = $exp["propietarios"][1]["camarapropietario"];
                                            $matprop = $exp["propietarios"][1]["matriculapropietario"];
                                            $siprop = 'si';
                                        }
                                    }
                                    if ($exp["categoria"] == '2' || $exp["categoria"] == '3') {
                                        $camprop = $exp["cpcodcam"];
                                        $matprop = $exp["cpnummat"];
                                        $siprop = 'si';
                                    }
                                    if ($matprop != '' && ($camprop == '' || $camprop == CODIGO_EMPRESA)) {
                                        $exp1 = \funcionesRegistrales::retornarExpedienteMercantil($dbx, $matprop);
                                        if ($exp1 && !empty($exp1)) {
                                            if ($exp1["estadomatricula"] == 'MA' || $exp1["estadomatricula"] == 'IA') {
                                                if ($exp1["tamanoempresarial957codigo"] == '') {
                                                    $exp1["tamanoempresarial957codigo"] = \funcionesRegistrales::determinarTamanoEmpresarialCodigo($dbx, $exp1["ciius"][1], $exp1["ingope"], $exp1["anodatos"], $exp1["fechadatos"]);
                                                }
                                                if ($exp1["tamanoempresarial957codigo"] == '1' || $exp1["tamanoempresarial957codigo"] == '2' || $exp1["tamanoempresarial957codigo"] == '3') {
                                                    if ($exp1["ultanoren"] == date("Y") - 2) {
                                                        if (defined('TARIFAESPECIAL_2021_DESCU_CANCELACION_2019') && TARIFAESPECIAL_2021_DESCU_CANCELACION_2019 == 'N') {
                                                            $incluir = 'no';
                                                        } else {
                                                            if (date("Ymd") <= $fcorte) {
                                                                $poscan = strpos($serv["nombre"], 'CANCELACION');
                                                                $poscan1 = strpos($serv["nombre"], 'CANCELA. PNAT / JURIDICA (AUTOMATICA)');
                                                                $posdis = strpos($serv["nombre"], 'DISOLUCION');
                                                                $posliq = strpos($serv["nombre"], 'LIQUIDACION');
                                                                if ($poscan === false && $poscan1 === false && $posdis === false && $posliq === false) {
                                                                    $incluir = 'no';
                                                                } else {
                                                                    $incluir = 'si';
                                                                }
                                                            }
                                                        }
                                                    }
                                                    if ($exp1["ultanoren"] >= date("Y") - 1) {
                                                        $incluir = 'si';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if ($matprop != '' && $camprop != '' && $camprop != CODIGO_EMPRESA) {
                                        $rues = \funcionesRues::consultarRegMer($camprop, trim($matprop));
                                        if (isset($rues["codigo_error"]) && $rues["codigo_error"] == '0000') {
                                            if ($rues["codigo_estado_matricula"] == '01') {
                                                \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - Tamano empresarial del propietario : ' . $rues["codigo_tamano_empresa"]);
                                                \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - Datos renovacion del propietario -  Fecha matricula :' . $rues["fecha_matricula"] . ' - Fecha renovacion :' . $rues["fecha_renovacion"] . ' -  Ult ano ren :' . $rues["ultimo_ano_renovado"]);
                                                if ($rues["codigo_tamano_empresa"] == '00') {
                                                    \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - Debe calcular tamano empresarial del propietario');
                                                    $ciiurev = '';
                                                    $ciiux = retornarRegistroMysqliApi($dbx, 'bas_ciius', "idciiunum='" . trim($rues["cod_ciiu_act_econ_pri"]) . "'");
                                                    if ($ciiux && !empty($ciiux)) {
                                                        $ciiurev = $ciiux["idciiu"];
                                                    }
                                                    $ingrev = 0;
                                                    $anorev = '';
                                                    $fecharev = $rues["fecha_renovacion"];
                                                    if (isset($rues["informacion_financiera"]) && !empty($rues["informacion_financiera"])) {
                                                        foreach ($rues["informacion_financiera"] as $fin) {
                                                            $ingrev = $fin["ingresos_actividad_ordinaria"];
                                                            $anorev = $fin["ano_informacion_financiera"];
                                                        }
                                                    }
                                                    \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - Datos del propietario : Ciiu ' . $ciiurev . ' - Ingresos : ' . $ingrev . ' - Ano datos : ' . $anorev . ' - fecha datos : ' . $fecharev);
                                                    $rues["codigo_tamano_empresa"] = '0' . \funcionesRegistrales::determinarTamanoEmpresarialCodigo($dbx, $ciiurev, $ingrev, $anorev, $fecharev);
                                                    \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - Tamano empresarial calculado del propietario : ' . $rues["codigo_tamano_empresa"]);
                                                }
                                                if ($rues["codigo_tamano_empresa"] == '01' || $rues["codigo_tamano_empresa"] == '02' || $rues["codigo_tamano_empresa"] == '03') {
                                                    if ($rues["ultimo_ano_renovado"] >= date("Y") - 1) {
                                                        $incluir = 'si';
                                                    }
                                                }
                                            } else {
                                                \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - propietario cancelado en rues');
                                            }
                                        } else {
                                            \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - propietario no localizado en rues');
                                        }
                                    }
                                }
                                // }
                            }

                            //
                            if ($incluir == 'si') {
                                \logApi::general2($nameLog, $numliq, 'Aplica descuento de actos y documentos sobre actos de establecimientos');
                                $xser = array();
                                $xser["idsec"] = '';
                                $xser["idservicio"] = '01090155';
                                $xser["cc"] = '';
                                $xser["expediente"] = $regliq["matricula_servicio"];
                                $xser["nombre"] = $regliq["nombre_matriculado"];
                                $xser["ano"] = $regliq["ano_renovacion"];
                                $xser["cantidad"] = $regliq["cantidad_servicio"] * -1;
                                $xser["valorbase"] = $regliq["valor_liquidacion"];
                                $xser["porcentaje"] = 7;
                                $xser["valorservicio"] = \funcionesGenerales::redondear00(round($xser["valorbase"] * $xser["porcentaje"] / 100)) * -1;
                                $xser["benart7"] = '';
                                $xser["benley1780"] = '';
                                $xser["reliquidacion"] = $regliq["reliquidacion"];
                                $xser["serviciobase"] = 'N';
                                $xser["clavecontrol"] = $regliq["clavecontrol"];
                                if ($xser["valorservicio"] != 0) {
                                    $iAdic++;
                                    $servAdic["descuentos"][$iAdic] = $xser;
                                }
                            }
                        }
                    }

                    // ****************************************************************************************** //
                    // CERTIFICADOS - MERCANTIL
                    // Descuento del 5% sobre todos los certificados del registro mercantil
                    // Siempre y cuando el comerciante certificado sea mipyme
                    // ****************************************************************************************** //
                    if ($serv["tipoingreso"] == '06' || $serv["tipoingreso"] == '16') {
                        \logApi::general2($nameLog, $numliq, 'Calculoara desceuento por certificados para la matricula ' . $regliq["matricula_servicio"]);
                        if (ltrim(trim($regliq["matricula_servicio"]), "0") != '') {
                            $exp = \funcionesRegistrales::retornarExpedienteMercantil($dbx, $regliq["matricula_servicio"]);
                            if ($exp && !empty($exp)) {
                                // if ($exp["estadomatricula"] == 'MA' || $exp["estadomatricula"] == 'IA') {
                                $incluir = 'no';
                                $siprop = 'no';
                                if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
                                    if ($exp["estadomatricula"] == 'MA' || $exp["estadomatricula"] == 'IA') {
                                        if ($mipymeprop != '') {
                                            if ($mipymeprop == '1' || $mipymeprop == '2' || $mipymeprop == '3') {
                                                $incluir = 'si';
                                            } else {
                                                $incluir = 'no';
                                            }
                                        } else {
                                            if ($exp["tamanoempresarial957codigo"] == '') {
                                                $exp["tamanoempresarial957codigo"] = \funcionesRegistrales::determinarTamanoEmpresarialCodigo($dbx, $exp["ciius"][1], $exp["ingope"], $exp["anodatos"], $exp["fechadatos"]);
                                                \logApi::general2($nameLog, $numliq, 'Datos expediente : ' . $regliq["matricula_servicio"] . ' - Ciiu: ' . $exp["ciius"][1] . ' - Ingresos: ' . $exp["ingope"] . ' - Ano datos: ' . $exp["anodatos"] . ' - Fecha datos: ' . $exp["fechadatos"]);
                                                \logApi::general2($nameLog, $numliq, 'Tamano empresairal calculado : ' . $regliq["matricula_servicio"] . ' - Tamano : ' . $exp["tamanoempresarial957codigo"]);
                                            }
                                            if ($exp["tamanoempresarial957codigo"] == '1' || $exp["tamanoempresarial957codigo"] == '2' || $exp["tamanoempresarial957codigo"] == '3') {
                                                $incluir = 'si';
                                            }
                                        }
                                        if ($incluir == 'si' && $mipymeprop == '') {
                                            if ($exp["ultanoren"] < date("Y") - 1) {
                                                $incluir = 'no';
                                            }
                                        }
                                    }
                                }
                                if ($exp["organizacion"] == '02' || ($exp["organizacion"] > '02' && ($exp["categoria"] == '2' || $exp["categoria"] == '3'))) {
                                    $camprop = '';
                                    $matprop = '';
                                    if ($exp["organizacion"] == '02') {
                                        if (isset($exp["propietarios"][1]) && trim($exp["propietarios"][1]["matriculapropietario"] != '')) {
                                            $camprop = $exp["propietarios"][1]["camarapropietario"];
                                            $matprop = $exp["propietarios"][1]["matriculapropietario"];
                                            $siprop = 'si';
                                        }
                                    }
                                    if ($exp["categoria"] == '2' || $exp["categoria"] == '3') {
                                        $camprop = $exp["cpcodcam"];
                                        $matprop = $exp["cpnummat"];
                                        $siprop = 'si';
                                    }
                                    if ($matprop != '' && ($camprop == '' || $camprop == CODIGO_EMPRESA)) {
                                        $exp1 = \funcionesRegistrales::retornarExpedienteMercantil($dbx, trim($matprop));
                                        if ($exp1 && !empty($exp1)) {
                                            if ($exp1["estadomatricula"] == 'MA' || $exp1["estadomatricula"] == 'IA') {
                                                if ($exp1["tamanoempresarial957codigo"] == '') {
                                                    $exp1["tamanoempresarial957codigo"] = \funcionesRegistrales::determinarTamanoEmpresarialCodigo($dbx, $exp1["ciius"][1], $exp1["ingope"], $exp1["anodatos"], $exp1["fechadatos"]);
                                                    \logApi::general2($nameLog, $numliq, 'Establecimiento ' . $regliq["matricula_servicio"] . ' - Datos propietario : ' . $exp1["matricula"] . ' - Ciiu: ' . $exp1["ciius"][1] . ' - Ingresos: ' . $exp1["ingope"] . ' - Ano datos: ' . $exp1["anodatos"] . ' - Fecha datos: ' . $exp1["fechadatos"]);
                                                    \logApi::general2($nameLog, $numliq, 'Tamano empresairal calculado : ' . $regliq["matricula_servicio"] . ' - Tamano : ' . $exp["tamanoempresarial957codigo"]);
                                                }
                                                if ($exp1["tamanoempresarial957codigo"] == '' || $exp1["tamanoempresarial957codigo"] == '1' || $exp1["tamanoempresarial957codigo"] == '2' || $exp1["tamanoempresarial957codigo"] == '3') {
                                                    if ($exp["ultanoren"] >= date("Y") - 1) {
                                                        $incluir = 'si';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if ($matprop != '' && $camprop != '' && $camprop != CODIGO_EMPRESA) {
                                        $rues = \funcionesRues::consultarRegMer($camprop, trim($matprop));
                                        if (isset($rues["codigo_error"]) && $rues["codigo_error"] == '0000') {
                                            if ($rues["codigo_estado_matricula"] == '01' || $rues["codigo_estado_matricula"] == '07' || $rues["codigo_estado_matricula"] == '08') {
                                                if ($rues["codigo_tamano_empresa"] == '00') {
                                                    \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - Debe calcular tamano empresarial del propietario');
                                                    $ciiurev = '';
                                                    $ciiux = retornarRegistroMysqliApi($dbx, 'bas_ciius', "idciiunum='" . trim($rues["cod_ciiu_act_econ_pri"]) . "'");
                                                    if ($ciiux && !empty($ciiux)) {
                                                        $ciiurev = $ciiux["idciiu"];
                                                    }
                                                    $ingrev = 0;
                                                    $anorev = '';
                                                    $fecharev = $rues["fecha_renovacion"];
                                                    if (isset($rues["informacion_financiera"]) && !empty($rues["informacion_financiera"])) {
                                                        foreach ($rues["informacion_financiera"] as $fin) {
                                                            $ingrev = $fin["ingresos_actividad_ordinaria"];
                                                            $anorev = $fin["ano_informacion_financiera"];
                                                        }
                                                    }
                                                    \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - Datos del propietario : Ciiu ' . $ciiurev . ' - Ingresos : ' . $ingrev . ' - Ano datos : ' . $anorev . ' - fecha datos : ' . $fecharev);
                                                    $rues["codigo_tamano_empresa"] = '0' . \funcionesRegistrales::determinarTamanoEmpresarialCodigo($dbx, $ciiurev, $ingrev, $anorev, $fecharev);
                                                    \logApi::general2($nameLog, $numliq, 'Establecimiento  : ' . $regliq["matricula_servicio"] . ' - Tamano empresarial calculado del propietario : ' . $rues["codigo_tamano_empresa"]);
                                                }
                                                if ($rues["codigo_tamano_empresa"] == '00' || $rues["codigo_tamano_empresa"] == '01' || $rues["codigo_tamano_empresa"] == '02' || $rues["codigo_tamano_empresa"] == '03') {
                                                    if ($rues["ultimo_ano_renovado"] >= date("Y") - 1) {
                                                        $incluir = 'si';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                if ($incluir == 'si') {
                                    \logApi::general2($nameLog, $numliq, 'Aplica descuento de certificados');
                                    $xser = array();
                                    $xser["idsec"] = '';
                                    $xser["idservicio"] = '';
                                    if ($serv["tipocertificado"] == 'CerMat') {
                                        $xser["idservicio"] = '01090157';
                                    }
                                    if ($serv["tipocertificado"] == 'CerExi' || $serv["tipocertificado"] == 'EspRegMer') {
                                        $xser["idservicio"] = '01090158';
                                    }
                                    if ($serv["tipocertificado"] == 'CerEsdal') {
                                        $xser["idservicio"] = '01090160';
                                    }
                                    if ($serv["tipocertificado"] == 'CerLibRegMer') {
                                        $xser["idservicio"] = '01090159';
                                    }
                                    if ($xser["idservicio"] == '') {
                                        if ($exp["organizacion"] == '01' || $exp["organizacion"] == '02' || $exp["categoria"] == '2' || $exp["categoria"] == '3') {
                                            $xser["idservicio"] = '01090157';
                                        }
                                        if ($exp["organizacion"] > '02' && $exp["organizacion"] != '12' && $exp["organizacion"] != '14' && $exp["categoria"] == '1') {
                                            $xser["idservicio"] = '01090158';
                                        }
                                        if (($exp["organizacion"] == '12' || $exp["organizacion"] == '14') && $exp["categoria"] == '1') {
                                            $xser["idservicio"] = '01090160';
                                        }
                                    }
                                    $xser["cc"] = '';
                                    $xser["expediente"] = $regliq["matricula_servicio"];
                                    $xser["nombre"] = $regliq["nombre_matriculado"];
                                    $xser["ano"] = $regliq["ano_renovacion"];
                                    $xser["cantidad"] = $regliq["cantidad_servicio"] * -1;
                                    $xser["valorbase"] = $regliq["valor_liquidacion"];
                                    $xser["porcentaje"] = 5;
                                    $valuni = $regliq["valor_liquidacion"] / $regliq["cantidad_servicio"];
                                    $xser["valorservicio"] = \funcionesGenerales::redondear00($valuni * $xser["porcentaje"] / 100) * $regliq["cantidad_servicio"] * -1;
                                    $xser["benart7"] = '';
                                    $xser["benley1780"] = '';
                                    $xser["reliquidacion"] = '';
                                    $xser["serviciobase"] = 'N';
                                    $xser["clavecontrol"] = $regliq["clavecontrol"];
                                    if ($xser["valorservicio"] != 0) {
                                        $iAdic++;
                                        $servAdic["descuentos"][$iAdic] = $xser;
                                    }
                                }
                            } else {
                                \logApi::general2($nameLog, $numliq, 'Matricula no localizada');
                            }
                        }
                    }
                }
            }
        }

        // ************************************************************************************ //
        // Adiciona servicios de tarifa especial
        // ************************************************************************************ //
        \logApi::general2($nameLog, $numliq, '');
        return $servAdic;
    }

    public static function calcularExcedente($dbx = null, $numliq = 0, $valor = 0) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        $nameLog = 'calcularExcedentes_' . date("Ymd");

        //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($dbx, $numliq);
        if ($_SESSION["tramite"] === false) {
            return false;
        }

        // ************************************************************************************ //
        // Borra servicios de tarifa especial previamente calculados
        // ************************************************************************************ //
        $dets = array();
        foreach ($_SESSION["tramite"]["liquidacion"] as $key => $aliq) {
            $dets[] = $aliq;
            $sec = $key;
        }
        $_SESSION["tramite"]["liquidacion"] = $dets;

        //
        $xser = array();
        $xser["idsec"] = '000';
        if ($valor > 0) {
            $xser["idservicio"] = '04060090';
        } else {
            $xser["idservicio"] = '04060091';
        }
        $xser["cc"] = '';
        $xser["expediente"] = '';
        $xser["nombre"] = '';
        $xser["ano"] = '';
        $xser["cantidad"] = 1;
        $xser["valorbase"] = 0;
        $xser["porcentaje"] = 0;
        $xser["valorservicio"] = $valor;
        $xser["benart7"] = '';
        $xser["benart7"] = '';
        $xser["reliquidacion"] = '';
        $xser["serviciobase"] = '';

        $xser["pagoafiliacion"] = '';
        $xser["ir"] = '';
        $xser["iva"] = '';
        $xser["idalerta"] = 0;
        $xser["expedienteafiliado"] = 0;
        $xser["porcentajeiva"] = 0;
        $xser["valoriva"] = 0;
        $xser["servicioiva"] = '';

        $xser["porcentajedescuento"] = 0;
        $xser["porcentajedescuento"] = 0;
        $xser["serviciodescuento"] = '';
        $xser["clavecontrol"] = '';

        //
        $sec++;
        $_SESSION["tramite"]["liquidacion"][$sec] = $xser;

        // ************************************************************************************ //
        // Recalcula totales
        // ************************************************************************************ //
        $_SESSION["tramite"]["valorbruto"] = 0;
        $_SESSION["tramite"]["valorbaseiva"] = 0;
        $_SESSION["tramite"]["valoriva"] = 0;
        $_SESSION["tramite"]["valortotal"] = 0;
        foreach ($_SESSION["tramite"]["liquidacion"] as $regliq) {
            $serv = retornarRegistroMysqliApi($dbx, 'mreg_servicios', "idservicio='" . $regliq["idservicio"] . "'");
            if (isset($serv["esiva"]) && $serv["esiva"] == 'S') {
                $_SESSION["tramite"]["valoriva"] = $_SESSION["tramite"]["valoriva"] + $regliq["valorservicio"];
            } else {
                $_SESSION["tramite"]["valorbruto"] = $_SESSION["tramite"]["valorbruto"] + $regliq["valorservicio"];
            }
            if (isset($serv["esgravadoiva"]) && $serv["esgravadoiva"] == 'S') {
                $_SESSION["tramite"]["valorbaseiva"] = $_SESSION["tramite"]["valorbaseiva"] + $regliq["valorservicio"];
            }
            $_SESSION["tramite"]["valortotal"] = $_SESSION["tramite"]["valortotal"] + $regliq["valorservicio"];
        }

        // ************************************************************************************ //
        // regraba liquidacion
        // ************************************************************************************ //
        \logApi::general2($nameLog, $numliq, 'Se ajusta por excedente (04060090) la liquidacio, valor : ' . $valor);
        \funcionesRegistrales::grabarLiquidacionMreg($dbx);
        return true;
    }

}
