<?php

class funcionesSii2_formularios {

    /**
     * 
     * @param type $numrec
     * @param type $numliq
     * @param type $tipoimpresion
     * @param type $prediligenciado
     * @param type $txtFirmaElectronica
     * @return boolean|string
     */
    public static function armarPdfEstablecimiento($mysqli, $numrec = '', $numliq = 0, $tipoimpresion = '', $prediligenciado = 'no', $txtFirmaElectronica = '') {

        if (!defined('ACTIVAR_CIRCULAR_002_2016')) {
            define('ACTIVAR_CIRCULAR_002_2016', '');
        }

        if (ACTIVAR_CIRCULAR_002_2016 == 'SI2') {
            require_once ('pdfFormularioRues-2017.php'); //VERSION 23 DE MAYO DE 2017
            $formulario = new formularioRues2017();
        } else {
            return false;
        }

        $datosForm = $_SESSION ["formulario"]["datos"];

        $formulario->setNumeroRecuperacion($numrec);
        $formulario->setNumeroLiquidacion($numliq);
        $formulario->setFechaImpresion(date('Y/m/d H:i:s'));

        if ($tipoimpresion == 'borrador') {
            $tipoFormulario = 0;
        } else {
            $tipoFormulario = 1;
        }
        $formulario->agregarPagina(3, $tipoFormulario);

        if ($tipoimpresion != 'vacio') {

            /**
             * 
             * FECHA DE DILIGENCIAMIENTO
             * 
             */
            if (isset($_SESSION ["tramite"]["fechaultimamodificacion"]) && (!empty($_SESSION ["tramite"]["fechaultimamodificacion"]))) {
                $fec = $_SESSION ["tramite"]["fechaultimamodificacion"];
            } else {
                if (isset($_SESSION ["tramite"]["fecha"]) && (!empty($_SESSION ["tramite"]["fecha"]))) {
                    $fec = $_SESSION ["tramite"]["fecha"];
                } else {
                    $fec = date("Ymd");
                }
            }

            $formulario->armarCampo('p3.cod_camara', $_SESSION ["generales"]["codigoempresa"] . ' - ' . $fec);

            if (!isset($_SESSION["formulario"]["tipotramite"])) {
                $_SESSION["formulario"]["tipotramite"] = '';
            }

            /**
             * 
             * TIPO DE TRAMITE
             * 
             */
            if (($_SESSION ["formulario"]["tipotramite"] == 'renovacionmatricula') || ($_SESSION ["formulario"]["tipotramite"] == 'renovacionesadl')) {
                $formulario->armarCampo('p3.ren', 'X');
            }

            if (($_SESSION ["formulario"]["tipotramite"] == 'matriculapnat') || ($_SESSION ["formulario"]["tipotramite"] == 'matriculaest')) {
                $formulario->armarCampo('p3.mat', 'X');
            }

            if ($datosForm["organizacion"] == '02') {
                $formulario->armarCampo('p3.est', 'X');
            }

            if ($datosForm["categoria"] == '2') {
                $formulario->armarCampo('p3.suc', 'X');
            }

            if ($datosForm["categoria"] == '3') {
                $formulario->armarCampo('p3.age', 'X');
            }

            //
            if ($datosForm["matricula"] != 'NUEVANAT' && $datosForm["matricula"] != 'NUEVAEST') {
                $formulario->armarCampo('p3.num_mat', $datosForm["matricula"]);
            }
            $formulario->armarCampo('p3.ano_ren', $datosForm["anodatos"]);

            /**
             * 
             * INFORMACION GENERAL - DOMICILIO
             * 
             */
            $formulario->armarCampo('p3.sec1_nom_est', utf8_decode($datosForm["nombre"]));
            $formulario->armarCampo('p3.sec1_dom_dir', utf8_decode($datosForm["dircom"]));
            $formulario->armarCampo('p3.sec1_dom_pos', $datosForm["codigopostalcom"]);

            if (ltrim($datosForm["barriocom"], "0") != '') {
                $formulario->armarCampo('p3.sec1_dom_blvc', self::retornarNombreBarrio($mysqli, $datosForm["muncom"], $datosForm["barriocom"]));
            }

            $formulario->armarCampo('p3.sec1_dom_tel1', $datosForm["telcom1"]);
            $formulario->armarCampo('p3.sec1_dom_tel2', $datosForm["telcom2"]);
            $formulario->armarCampo('p3.sec1_dom_tel3', $datosForm["celcom"]);
            $formulario->armarCampo('p3.sec1_dom_muni', self::retornarNombreMunicipio($mysqli, $datosForm["muncom"]));
            $formulario->armarCampo('p3.sec1_dom_muni_num', substr($datosForm["muncom"], 2, 5));
            $formulario->armarCampo('p3.sec1_dom_dep', self::retornarNombreDpto($mysqli, $datosForm["muncom"]));
            $formulario->armarCampo('p3.sec1_dom_dep_num', substr($datosForm["muncom"], 0, 2));
            $formulario->armarCampo('p3.sec1_dom_email', $datosForm["emailcom"]);

            switch ($datosForm["ctrubi"]) {
                case "1" :
                    $formulario->armarCampo('p3.sec1_dom_loc', 'X');
                    break;
                case "2" :
                    $formulario->armarCampo('p3.sec1_dom_ofi', 'X');
                    break;
                case "3" :
                    $formulario->armarCampo('p3.sec1_dom_loc_ofi', 'X');
                    break;
                case "4" :
                    $formulario->armarCampo('p3.sec1_dom_fab', 'X');
                    break;
                case "5" :
                    $formulario->armarCampo('p3.sec1_dom_viv', 'X');
                    break;
                case "6" :
                    $formulario->armarCampo('p3.sec1_dom_fin', 'X');
                    break;
            }


            /**
             * 
             * INFORMACION PARA NOTIFICACIÓN JUDICIAL
             * 
             */
            if ($datosForm["organizacion"] != '02') {

                $formulario->armarCampo('p3.sec1_not_dir', utf8_decode($datosForm["dirnot"]));
                $formulario->armarCampo('p3.sec1_not_pos', $datosForm["codigopostalnot"]);

                if (ltrim($datosForm["barrionot"], "0") != '') {
                    $formulario->armarCampo('p3.sec1_not_blvc', self::retornarNombreBarrio($mysqli, $datosForm["munnot"], $datosForm["barrionot"]));
                }

                $formulario->armarCampo('p3.sec1_not_muni', self::retornarNombreMunicipio($mysqli, $datosForm["munnot"]));
                $formulario->armarCampo('p3.sec1_not_muni_num', substr($datosForm["munnot"], 2, 5));
                $formulario->armarCampo('p3.sec1_not_dep', self::retornarNombreDpto($mysqli, $datosForm["munnot"]));
                $formulario->armarCampo('p3.sec1_not_dep_num', substr($datosForm["munnot"], 0, 2));
                $formulario->armarCampo('p3.sec1_not_email', $datosForm["emailnot"]);
            }

            if ($prediligenciado != 'si') {
                $formulario->armarCampo('p3.sec1_act_vin', self::truncateFloatForm($datosForm["actvin"], 0));
                $formulario->armarCampo('p3.sec1_num_trab', $datosForm["personal"]);
            }

            /**
             * 
             * ACTIVIDAD ECONOMICA
             * 
             */
            $formulario->armarCampo('p3.sec2_ciiu1', substr($datosForm["ciius"][1], 1, 4));
            $formulario->armarCampo('p3.sec2_ciiu2', substr($datosForm["ciius"][2], 1, 4));
            $formulario->armarCampo('p3.sec2_ciiu3', substr($datosForm["ciius"][3], 1, 4));
            $formulario->armarCampo('p3.sec2_ciiu4', substr($datosForm["ciius"][4], 1, 4));

            $formulario->armarCampo('p3.sec2_desc_act_eco', utf8_decode($datosForm["desactiv"])); //DESCRIPCION ACTIVIDAD ECONOMICA

            /**
             * 
             * TIPO DE PROPIEDAD
             * 
             */
            if ($datosForm["tipopropiedad"] == '') {
                $datosForm["tipopropiedad"] = '0';
            }
            if ($datosForm["tipopropiedad"] == '0') {
                $formulario->armarCampo('p3.sec3_prop_uni', 'X');
            }
            if ($datosForm["tipopropiedad"] == '1') {
                $formulario->armarCampo('p3.sec3_soc_hec', 'X');
            }
            if ($datosForm["tipopropiedad"] == '2') {
                $formulario->armarCampo('p3.sec3_copro', 'X');
            }


            /**
             * 
             * TIPO DE LOCAL
             * 
             */
            if ($datosForm["tipolocal"] == '1') {
                $formulario->armarCampo('p3.sec3_loc_pro', 'X');
            }
            if ($datosForm["tipolocal"] == '0') {
                $formulario->armarCampo('p3.sec3_loc_aje', 'X');
            }


            //
            if ($datosForm["organizacion"] == '02') {
                if (!empty($datosForm["propietarios"])) {
                    foreach ($datosForm["propietarios"] as $ind => $prop) {

                        switch ($ind) {
                            case "1":
                            case "2":
                                /**
                                 * 
                                 * DATOS PROPIETARIO
                                 * 
                                 */
                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_nom', utf8_decode($prop["nombrepropietario"]));
                                if ($prop["idtipoidentificacionpropietario"] == '2') {
                                    $sepIde = separarDv($prop["identificacionpropietario"]);
                                    $formulario->armarCampo('p3.sec4_prop' . $ind . '_ide', $sepIde["identificacion"]);
                                    $formulario->armarCampo('p3.sec4_prop' . $ind . '_dv', $sepIde["dv"]);
                                } else {
                                    $formulario->armarCampo('p3.sec4_prop' . $ind . '_ide', $prop["identificacionpropietario"]);
                                }

                                switch ($prop["idtipoidentificacionpropietario"]) {
                                    case "1" :
                                        $formulario->armarCampo('p3.sec4_prop' . $ind . '_cc', 'X');
                                        break;
                                    case "2" :
                                        $formulario->armarCampo('p3.sec4_prop' . $ind . '_nit', 'X');
                                        break;
                                    case "3" :
                                        $formulario->armarCampo('p3.sec4_prop' . $ind . '_ce', 'X');
                                        break;
                                    case "4" :
                                        $formulario->armarCampo('p3.sec4_prop' . $ind . '_ti', 'X');
                                        break;
                                    case "5" :
                                        $formulario->armarCampo('p3.sec4_prop' . $ind . '_pas', 'X');
                                        break;
                                }

                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_mat', $prop["matriculapropietario"]);
                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_cam', $prop["camarapropietario"]);
                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_dir', utf8_decode($prop["direccionpropietario"]));
                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_muni', self::retornarNombreMunicipio($mysqli, $prop["municipiopropietario"]));
                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_muni_num', substr($prop["municipiopropietario"], 2, 5));
                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_dep', self::retornarNombreDpto($mysqli, $prop["municipiopropietario"]));
                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_dep_num', substr($prop["municipiopropietario"], 0, 2));
                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_tel1', $prop["telefonopropietario"]);
                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_tel2', $prop["telefono2propietario"]);
                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_tel3', $prop["celularpropietario"]);
                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_not_dir', utf8_decode($prop["direccionnotpropietario"]));
                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_not_mun', self::retornarNombreMunicipio($mysqli, $prop["municipionotpropietario"]));
                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_not_mun_num', substr($prop["municipionotpropietario"], 2, 5));
                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_not_dep', self::retornarNombreDpto($mysqli, $prop["municipionotpropietario"]));
                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_not_dep_num', substr($prop["municipionotpropietario"], 0, 2));

                                /**
                                 * 
                                 * DATOS REPRESENTANTE LEGAL
                                 * 
                                 */
                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_repleg_nom', utf8_decode($prop["nomreplegpropietario"]));

                                switch ($prop["tipoidreplegpropietario"]) {
                                    case "1" :
                                        $formulario->armarCampo('p3.sec4_prop' . $ind . '_repleg_cc', 'X');
                                        break;
                                    case "4" :
                                        $formulario->armarCampo('p3.sec4_prop' . $ind . '_repleg_ti', 'X');
                                        break;
                                    case "3" :
                                        $formulario->armarCampo('p3.sec4_prop' . $ind . '_repleg_ce', 'X');
                                        break;
                                    case "5" :
                                        $formulario->armarCampo('p3.sec4_prop' . $ind . '_repleg_pas', 'X');
                                        break;
                                }
                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_repleg_ide', $prop["numidreplegpropietario"]);
                                //REVISAR
                                $formulario->armarCampo('p3.sec4_prop' . $ind . '_repleg_pais', '');

                                break;
                            default:
                                break;
                        }
                    }
                    //
                }
            }



            if ($datosForm["organizacion"] > '02' &&
                    ($datosForm["categoria"] == '2' || $datosForm["categoria"] == '3')) {

                if ($datosForm["cprazsoc"] != '') {

                    $formulario->armarCampo('p3.sec4_prop1_nom', utf8_decode($datosForm["cprazsoc"]));
                    $formulario->armarCampo('p3.sec4_prop1_ide', $datosForm["cpnumnit"]);
                    $formulario->armarCampo('p3.sec4_prop1_dv', self::calcularDv($datosForm["cpnumnit"]));
                    $formulario->armarCampo('p3.sec4_prop1_nit', 'X');
                    $formulario->armarCampo('p3.sec4_prop1_mat', $datosForm["cpnummat"]);
                    $formulario->armarCampo('p3.sec4_prop1_cam', $datosForm["cpcodcam"]);
                    $formulario->armarCampo('p3.sec4_prop1_dir', $datosForm["cpdircom"]);
                    $formulario->armarCampo('p3.sec4_prop1_muni', self::retornarNombreMunicipio($mysqli, $datosForm["cpcodmun"]));
                    $formulario->armarCampo('p3.sec4_prop1_muni_num', substr($datosForm["cpcodmun"], 2, 5));
                    $formulario->armarCampo('p3.sec4_prop1_dep', self::retornarNombreDpto($mysqli, $datosForm["cpcodmun"]));
                    $formulario->armarCampo('p3.sec4_prop1_dep_num', substr($datosForm["cpcodmun"], 0, 2));
                    $formulario->armarCampo('p3.sec4_prop1_tel1', $datosForm["cpnumtel"]);
                    $formulario->armarCampo('p3.sec4_prop1_tel2', $datosForm["cpnumtel2"]);
                    $formulario->armarCampo('p3.sec4_prop1_tel3', $datosForm["cpnumtel3"]);
                    $formulario->armarCampo('p3.sec4_prop1_not_dir', $datosForm["cpdirnot"]);
                    $formulario->armarCampo('p3.sec4_prop1_not_mun', self::retornarNombreMunicipio($mysqli, $datosForm["cpmunnot"]));
                    $formulario->armarCampo('p3.sec4_prop1_not_mun_num', substr($datosForm["cpmunnot"], 2, 5));
                    $formulario->armarCampo('p3.sec4_prop1_not_dep', self::retornarNombreDpto($mysqli, $datosForm["cpmunnot"]));
                    $formulario->armarCampo('p3.sec4_prop1_not_dep_num', substr($datosForm["cpmunnot"], 0, 2));
                }
            }
            //
            $formulario->armarCampo('p3.firma_elec', utf8_decode($txtFirmaElectronica));
        }

        $fechaHora = date("Ymd") . date("His");
        if ($tipoimpresion != 'vacio') {
            $name = PATH_ABSOLUTO_SITIO . "/tmp/" . session_id() . "-Formulario2017-Establecimiento-" . $datosForm["matricula"] . '-' . $fechaHora . ".pdf";
            $name1 = session_id() . "-Formulario2017-Establecimiento-" . $datosForm["matricula"] . '-' . $fechaHora . ".pdf";
            $formulario->Output($name, "F");
            return $name1;
        } else {
            $name = PATH_ABSOLUTO_SITIO . "/tmp/" . session_id() . "-Formulario2017-Establecimiento-" . session_id() . '-' . $fechaHora . ".pdf";
            $name1 = session_id() . "-Formulario2017-Establecimiento-" . session_id() . '-' . $fechaHora . ".pdf";
            $formulario->Output($name, "F");
            return $name1;
        }
    }

    /**
     * 
     * @param type $numrec
     * @param type $numliq
     * @param type $tipoimpresion
     * @param type $txtFirmaElectronica
     * @return boolean|string
     */
    public static function armarPdfEstablecimientoAnosAnteriores($mysqli, $numrec = '', $numliq = 0, $tipoimpresion = '', $txtFirmaElectronica = '') {

        try {
            if (!defined('ACTIVAR_CIRCULAR_002_2016')) {
                define('ACTIVAR_CIRCULAR_002_2016', '');
            }

            if (ACTIVAR_CIRCULAR_002_2016 == 'SI2') {
                require_once ('pdfFormularioRues-2017.php'); //VERSION 23 DE MAYO DE 2017
                $formulario = new formularioRues2017();
            } else {
                return false;
            }

            $datosForm = $_SESSION ["formulario"]["datos"];

            $formulario->setNumeroRecuperacion($numrec);
            $formulario->setNumeroLiquidacion($numliq);
            $formulario->setFechaImpresion(date('Y/m/d H:i:s'));

            if ($tipoimpresion == 'vacio') {
                $formulario->agregarPagina(5, $tipoFormulario);
            }

            $item = 0;
            foreach ($datosForm["f"] as $iAno => $fin) {

                if ($iAno != $datosForm["anodatos"]) {
                    $item ++;
                    if ($item == 5) {
                        $item = 1;
                    }
                    if ($item == 1) {

                        if ($tipoimpresion == 'borrador') {
                            $tipoFormulario = 0;
                        } else {
                            $tipoFormulario = 1;
                        }
                        $formulario->agregarPagina(5, $tipoFormulario);
                    }

                    /**
                     * 
                     * FECHA DE DILIGENCIAMIENTO
                     * 
                     */
                    if (isset($_SESSION ["tramite"]["fechaultimamodificacion"]) && (!empty($_SESSION ["tramite"]["fechaultimamodificacion"]))) {
                        $fec = $_SESSION ["tramite"]["fechaultimamodificacion"];
                    } else {
                        if (isset($_SESSION ["tramite"]["fecha"]) && (!empty($_SESSION ["tramite"]["fecha"]))) {
                            $fec = $_SESSION ["tramite"]["fecha"];
                        } else {
                            $fec = date("Ymd");
                        }
                    }

                    $formulario->armarCampo('p5.cod_camara', $_SESSION ["generales"]["codigoempresa"] . ' - ' . $fec);

                    if ($datosForm["organizacion"] == '02') {
                        $formulario->armarCampo('p5.est', 'X');
                    } else {
                        if ($datosForm["categoria"] == '2') {
                            $formulario->armarCampo('p5.suc', 'X');
                        }
                        if ($datosForm["categoria"] == '3') {
                            $formulario->armarCampo('p5.age', 'X');
                        }
                    }

                    /**
                     * 
                     * DATOS ESTABLECIMIENTO
                     * 
                     */
                    $formulario->armarCampo('p5.nom_est', utf8_decode($datosForm["nombre"]));
                    $formulario->armarCampo('p5.mat_est', $datosForm["matricula"]);

                    /**
                     * 
                     * DATOS PROPIETARIO
                     * 
                     */
                    if ($datosForm["organizacion"] == '02') {
                        if (!empty($datosForm["propietarios"])) {
                            if (isset($datosForm["propietarios"][1])) {
                                $formulario->armarCampo('p5.nom_pro_est', $datosForm["propietarios"][1]["nombrepropietario"]);
                                $formulario->armarCampo('p5.nom_pro_est_mat', $datosForm["propietarios"][1]["matriculapropietario"]);

                                $nit = ltrim($datosForm["propietarios"][1]["nitpropietario"], '0');

                                if (trim($nit) != '') {
                                    $formulario->armarCampo('p5.nit', 'X');
                                    $formulario->armarCampo('p5.nit_num', substr($nit, 0, strlen($nit) - 1));
                                    $formulario->armarCampo('p5.nit_dv', substr($nit, strlen($nit) - 1, 1));
                                } else {
                                    $formulario->armarCampo('p5.nit', '');
                                    $formulario->armarCampo('p5.nit_num', ltrim($datosForm["propietarios"][1]["identificacionpropietario"], '0'));
                                    $formulario->armarCampo('p5.nit_dv', self::calcularDv(ltrim($datosForm["propietarios"][1]["identificacionpropietario"], '0')));
                                }

                                /**
                                 * 
                                 * DATOS FIRMANTE
                                 * 
                                 */
                                if (trim($datosForm["propietarios"][1]["numidreplegpropietario"]) == '') {
                                    $formulario->armarCampo('p5.firma_nom', utf8_decode($datosForm["propietarios"][1]["nombrepropietario"]));
                                    $formulario->armarCampo('p5.firma_ide', ltrim($datosForm["propietarios"][1]["identificacionpropietario"], '0'));
                                    $tipoIdeFirma = $datosForm["propietarios"][1]["idtipoidentificacionpropietario"];
                                }

                                switch ($tipoIdeFirma) {
                                    case "1" :
                                        $formulario->armarCampo('p5.firma_cc', 'X');
                                        break;
                                    case "3" :
                                        $formulario->armarCampo('p5.firma_ce', 'X');
                                        break;
                                    case "4" :
                                        $formulario->armarCampo('p5.firma_ti', 'X');
                                        break;
                                    case "5" :
                                        $formulario->armarCampo('p5.firma_pas', 'X');
                                        break;
                                }

                                //REVISAR
                                $formulario->armarCampo('p5.firma_pais', '');
                            }
                        }
                    }

                    if ($datosForm["organizacion"] > '02' &&
                            ($datosForm["categoria"] == '2' || $datosForm["categoria"] == '3')) {

                        if ($datosForm["cprazsoc"] != '') {
                            $formulario->armarCampo('p5.nom_pro_est', utf8_decode($datosForm["cprazsoc"]));
                            $formulario->armarCampo('p5.nom_pro_est_mat', $datosForm["cpnummat"]);

                            $ide = ltrim($datosForm["cpnumnit"], '0');
                            $formulario->armarCampo('p5.nit', 'X');
                            $formulario->armarCampo('p5.nit_num', $ide);
                            $formulario->armarCampo('p5.dv', self::calcularDv($ide));
                        }
                    }

                    $formulario->armarCampo('p5.f' . $item . '_ano', $fin ["anodatos"], $item);
                    $formulario->armarCampo('p5.f' . $item . '_act', self::truncateFloatForm($fin ["actvin"], 0), $item);

                    $formulario->armarCampo('p5.firma_elec', utf8_decode($txtFirmaElectronica));
                }
            }//fin foreach

            $fechaHora = date("Ymd") . date("His");
            $name = PATH_ABSOLUTO_SITIO . "/tmp/" . session_id() . "-Formulario2017-Establecimiento-Anteriores-" . $datosForm["matricula"] . '-' . $fechaHora . ".pdf";
            $name1 = session_id() . "-Formulario2017-Establecimiento-Anteriores-" . $datosForm["matricula"] . '-' . $fechaHora . ".pdf";
            $formulario->Output($name, "F");
            return $name1;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 
     * @param type $numrec
     * @param type $numliq
     * @param type $tipoimpresion
     * @param type $prediligenciado
     * @param type $txtFirmaElectronica
     * @return boolean|string
     */
    public static function armarPdfPrincipal($mysqli, $numrec = '', $numliq = 0, $tipoimpresion = '', $prediligenciado = 'no', $txtFirmaElectronica = '') {

        if (!defined('ACTIVAR_CIRCULAR_002_2016')) {
            define('ACTIVAR_CIRCULAR_002_2016', '');
        }

        if (ACTIVAR_CIRCULAR_002_2016 == 'SI2') {
            require_once ('pdfFormularioRues-2017.php'); //VERSION 23 DE MAYO DE 2017
            $formulario = new formularioRues2017();
        } else {
            return false;
        }

        $datosForm = $_SESSION ["formulario"]["datos"];

        $formulario->setNumeroRecuperacion($numrec);
        $formulario->setNumeroLiquidacion($numliq);
        $formulario->setFechaImpresion(date('Y/m/d H:i:s'));

        if ($tipoimpresion == 'borrador') {
            $tipoFormulario = 0;
        } else {
            $tipoFormulario = 1;
        }

        /**
         * ************************************************************************ INICIO HOJA 1 *************************************************************************
         */
        $formulario->agregarPagina(1, $tipoFormulario);

        if ($tipoimpresion != 'vacio') {

            /**
             * 
             * FECHA DE DILIGENCIAMIENTO
             * 
             */
            if (isset($_SESSION ["tramite"]["fechaultimamodificacion"])) {
                $fec = $_SESSION ["tramite"]["fechaultimamodificacion"];
            } else {
                if (isset($_SESSION ["tramite"]["fecha"])) {
                    $fec = $_SESSION ["tramite"]["fecha"];
                } else {
                    $fec = date("Ymd");
                }
            }

            $formulario->armarCampo('p1.cod_camara', $_SESSION ["generales"]["codigoempresa"] . ' - ' . $fec);

            if (!isset($_SESSION["formulario"]["tipotramite"])) {
                $_SESSION["formulario"]["tipotramite"] = '';
            }

            /**
             * 
             * TIPO DE TRAMITE
             * 
             */
            if (($_SESSION ["formulario"]["tipotramite"] == 'renovacionmatricula') ||
                    ($_SESSION ["formulario"]["tipotramite"] == 'renovacionesadl')) {

                if (substr($datosForm["matricula"], 0, 1) != 'S') {

                    if ($_SESSION["tramite"]["reliquidacion"] == 'si') {
                        $formulario->armarCampo('p1.sec1_col1_aju_inf', 'X');
                    } else {
                        $formulario->armarCampo('p1.sec1_col1_ren', 'X');
                    }
                    $formulario->armarCampo('p1.sec1_col1_ano_ren', $datosForm["anodatos"]);
                    $formulario->armarCampo('p1.sec1_col1_num_mat', $datosForm["matricula"]);
                } else {

                    if (strtoupper($_SESSION["tramite"]["reliquidacion"]) == 'SI') {
                        $formulario->armarCampo('p1.sec1_col2_aju_inf', 'X');
                    } else {
                        $formulario->armarCampo('p1.sec1_col2_ren', 'X');
                    }
                    $formulario->armarCampo('p1.sec1_col2_ano_ren', $datosForm["anodatos"]);
                    $formulario->armarCampo('p1.sec1_col2_num_ins', $datosForm["matricula"]);
                }
            }

            if ($_SESSION ["formulario"]["tipotramite"] == 'matriculamercantil' ||
                    $_SESSION ["formulario"]["tipotramite"] == 'matriculapnat' ||
                    $_SESSION ["formulario"]["tipotramite"] == 'matriculapjur' ||
                    $_SESSION ["formulario"]["tipotramite"] == 'constitucionpjur' ||
                    $_SESSION ["formulario"]["tipotramite"] == 'compraventa' ||
                    $_SESSION ["formulario"]["tipotramite"] == 'inscripciondocumentos'
            ) {
                if (substr($datosForm["matricula"], 0, 1) != 'S' &&
                        $datosForm["matricula"] != 'NUEVAESA') {
                    if ($_SESSION["tramite"]["tipomatricula"] == 'cambidom') {
                        $formulario->armarCampo('p1.sec1_col1_tra_dom', 'X');
                    } else {
                        $formulario->armarCampo('p1.sec1_col1_mat_ins', 'X');
                    }
                    if (substr($datosForm["matricula"], 0, 5) != 'NUEVA') {
                        $formulario->armarCampo('p1.sec1_col1_num_mat', $datosForm["matricula"]);
                    }
                } else {
                    if ($_SESSION["tramite"]["tipomatricula"] == 'cambidom') {
                        $formulario->armarCampo('p1.sec1_col2_tra_dom', 'X');
                    } else {
                        $formulario->armarCampo('p1.sec1_col2_ins', 'X');
                    }
                    if (substr($datosForm["matricula"], 0, 5) != 'NUEVA') {
                        $formulario->armarCampo('p1.sec1_col2_num_ins', $datosForm["matricula"]);
                    }
                }
            }





            if ($_SESSION ["formulario"]["tipotramite"] == 'constitucionesadl') {
                if ($_SESSION["tramite"]["tipomatricula"] == 'cambidom') {
                    $formulario->armarCampo('p1.sec1_col2_tra_dom', 'X');
                } else {
                    $formulario->armarCampo('p1.sec1_col2_ins', 'X');
                }
                if (substr($datosForm["matricula"], 0, 5) != 'NUEVA') {
                    $formulario->armarCampo('p1.sec1_col2_num_ins', $datosForm["matricula"]);
                }
            }

            /**
             * 
             * TIPO DE ORGANIZACION 
             * 
             */
            if ($datosForm["organizacion"] != '12' && $datosForm["organizacion"] != '14') {
                switch ($_SESSION["formulario"]["tipotramite"]) {
                    case 'inscripcionproponente':
                    case 'renovacionproponente':
                    case 'cambiodomicilioproponente':
                    case 'actualizacionproponente':
                    case 'cancelacionproponente':
                        $formulario->armarCampo('p1.sec1_col1_cod_org_gen', '');
                        $formulario->armarCampo('p1.sec1_col1_cod_org_esp', '');
                        break;
                    default:
                        //HOMOLOGACIONES
                        $formulario->armarCampo('p1.sec1_col1_cod_org_gen', funcionesSii2_formularios::homologacionOrganizacionGeneralRUES($datosForm["organizacion"], $datosForm["claseespesadl"], $datosForm["ciius"]));
                        $formulario->armarCampo('p1.sec1_col1_cod_org_esp', funcionesSii2_formularios::homologacionOrganizacionEspecificaRUES($datosForm["organizacion"], $datosForm["clasegenesadl"], $datosForm["claseespesadl"], $datosForm["claseeconsoli"]));
                        break;
                }
            }

            /**
             * 
             * IDENTIFICACIÓN
             * 
             */
            if (trim($datosForm["ape1"]) == '') {
                $formulario->armarCampo('p1.sec2_raz_soc', utf8_decode($datosForm["nombre"]));
                $formulario->armarCampo('p1.sec2_sig', $datosForm["sigla"]);
            }

            $formulario->armarCampo('p1.sec2_nit', substr($datosForm["nit"], 0, - 1));
            $formulario->armarCampo('p1.sec2_dv', substr($datosForm["nit"], - 1, 1));
            $formulario->armarCampo('p1.sec2_ape1', utf8_decode($datosForm["ape1"]));
            $formulario->armarCampo('p1.sec2_ape2', utf8_decode($datosForm["ape2"]));
            $formulario->armarCampo('p1.sec2_nom1', utf8_decode($datosForm["nom1"]));
            $formulario->armarCampo('p1.sec2_nom2', utf8_decode($datosForm["nom2"]));

            if ($datosForm["organizacion"] == '01') {

                $formulario->armarCampo('p1.sec2_ide', $datosForm["identificacion"]);

                $formulario->armarCampo('p1.sec2_fec_exp', $datosForm["fecexpdoc"]);

                $formulario->armarCampo('p1.sec2_lug_exp', self::retornarNombreMunicipio($mysqli, $datosForm["idmunidoc"]));

                switch ($datosForm["tipoidentificacion"]) {
                    case "1" :
                        $formulario->armarCampo('p1.sec2_cc', 'X');
                        break;
                    case "3" :
                        $formulario->armarCampo('p1.sec2_ce', 'X');
                        break;
                    case "4" :
                        $formulario->armarCampo('p1.sec2_ti', 'X');
                        break;
                    case "5" :
                        $formulario->armarCampo('p1.sec2_pas', 'X');
                        break;
                }
            }

            $formulario->armarCampo('p1.sec2_pais', self::retornarNombrePaisAbreviado($mysqli, $datosForm["paisexpdoc"]));

            $formulario->armarCampo('p1.sec2_ide_trib_pais_ori', $datosForm["idetripaiori"]);
            $formulario->armarCampo('p1.sec2_pais_ori', $datosForm["paiori"]);
            $formulario->armarCampo('p1.sec2_ide_tri_soc', $datosForm["idetriextep"]);


            /**
             * 
             * INFORMACION GENERAL - DOMICILIO
             * 
             */
            $formulario->armarCampo('p1.sec3_dom_dir', utf8_decode($datosForm["dircom"]));


            if ($datosForm["codigozonacom"] == 'U') {
                $formulario->armarCampo('p1.sec3_dom_urb', 'X');
            }
            if ($datosForm["codigozonacom"] == 'R') {
                $formulario->armarCampo('p1.sec3_dom_rur', 'X');
            }

            $formulario->armarCampo('p1.sec3_dom_post', $datosForm["codigopostalcom"]);

            switch ($datosForm["ctrubi"]) {
                case "1" :
                    $formulario->armarCampo('p1.sec3_dom_loc', 'X');
                    break;
                case "2" :
                    $formulario->armarCampo('p1.sec3_dom_ofi', 'X');
                    break;
                case "3" :
                    $formulario->armarCampo('p1.sec3_dom_loc_ofi', 'X');
                    break;
                case "4" :
                    $formulario->armarCampo('p1.sec3_dom_fab', 'X');
                    break;
                case "5" :
                    $formulario->armarCampo('p1.sec3_dom_viv', 'X');
                    break;
                case "6" :
                    $formulario->armarCampo('p1.sec3_dom_fin', 'X');
                    break;
            }


            $formulario->armarCampo('p1.sec3_dom_muni', self::retornarNombreMunicipio($mysqli, $datosForm["muncom"]));
            $formulario->armarCampo('p1.sec3_dom_muni_num', substr($datosForm["muncom"], 2, 5));
            $formulario->armarCampo('p1.sec3_dom_dep', self::retornarNombreDpto($mysqli, $datosForm["muncom"]));
            $formulario->armarCampo('p1.sec3_dom_dep_num', substr($datosForm["muncom"], 0, 2));
            $formulario->armarCampo('p1.sec3_dom_pais', self::retornarNombrePaisAbreviado($mysqli, $datosForm["paicom"]));

            if (ltrim($datosForm["barriocom"], "0") != '') {
                $formulario->armarCampo('p1.sec3_dom_lbvc', self::retornarNombreBarrio($mysqli, $datosForm["muncom"], $datosForm["barriocom"]));
            }

            $formulario->armarCampo('p1.sec3_dom_tel1', $datosForm["telcom1"]);
            $formulario->armarCampo('p1.sec3_dom_tel2', $datosForm["telcom2"]);
            $formulario->armarCampo('p1.sec3_dom_tel3', $datosForm["celcom"]);
            $formulario->armarCampo('p1.sec3_dom_email', $datosForm["emailcom"]);

            /**
             * 
             * INFORMACION PARA NOTIFICACIÓN JUDICIAL Y ADMINISTRATIVA
             * 
             */
            $formulario->armarCampo('p1.sec3_not_dir', utf8_decode($datosForm["dirnot"]));

            if ($datosForm["codigozonanot"] == 'U') {
                $formulario->armarCampo('p1.sec3_not_urb', 'X');
            }
            if ($datosForm["codigozonanot"] == 'R') {
                $formulario->armarCampo('p1.sec3_not_rur', 'X');
            }


            $formulario->armarCampo('p1.sec3_not_post', $datosForm["codigopostalnot"]);

            $formulario->armarCampo('p1.sec3_not_muni', self::retornarNombreMunicipio($mysqli, $datosForm["munnot"]));
            $formulario->armarCampo('p1.sec3_not_muni_num', substr($datosForm["munnot"], 2, 5));
            $formulario->armarCampo('p1.sec3_not_dep', self::retornarNombreDpto($mysqli, $datosForm["munnot"]));
            $formulario->armarCampo('p1.sec3_not_dep_num', substr($datosForm["munnot"], 0, 2));
            $formulario->armarCampo('p1.sec3_not_pais', self::retornarNombrePaisAbreviado($mysqli, $datosForm["painot"]));

            if (ltrim($datosForm["barrionot"], "0") != '') {

                $formulario->armarCampo('p1.sec3_not_lbvc', self::retornarNombreBarrio($mysqli, $datosForm["munnot"], $datosForm["barrionot"]));
            }

            $formulario->armarCampo('p1.sec3_not_tel1', $datosForm["telnot"]);
            $formulario->armarCampo('p1.sec3_not_tel2', $datosForm["telnot2"]);
            $formulario->armarCampo('p1.sec3_not_tel3', $datosForm["celnot"]);
            $formulario->armarCampo('p1.sec3_not_email', $datosForm["emailnot"]);



            switch ($datosForm["tiposedeadm"]) {
                case '1':
                    $formulario->armarCampo('p1.sec3_sede1', 'X'); //PROPIA
                    break;
                case '2':
                    $formulario->armarCampo('p1.sec3_sede2', 'X'); //ARRIENDO
                    break;
                case '3':
                    $formulario->armarCampo('p1.sec3_sede3', 'X'); //COMODATO
                    break;
                case '4':
                    $formulario->armarCampo('p1.sec3_sede4', 'X'); //PRESTAMO
                    break;
            }



            /**
             * 
             * NOTIFICACION A EMAIL
             * 
             */
            //2017-07-25 - WSI - Actualizado
            switch (substr($datosForm["ctrmennot"], 0, 1)) {
                case "S" :
                case "1" :
                    $formulario->armarCampo('p1.sec3_not_si', 'X');
                    break;
                case "N" :
                case "0" :
                    $formulario->armarCampo('p1.sec3_not_no', 'X');
                    break;
            }

            /**
             * 
             * ACTIVIDAD ECONOMICA
             * 
             */
            $formulario->armarCampo('p1.sec4_ciiu1', substr($datosForm["ciius"][1], 1, 4));
            $formulario->armarCampo('p1.sec4_ciiu2', substr($datosForm["ciius"][2], 1, 4));
            $formulario->armarCampo('p1.sec4_ciiu3', substr($datosForm["ciius"][3], 1, 4));
            $formulario->armarCampo('p1.sec4_ciiu4', substr($datosForm["ciius"][4], 1, 4));
            $formulario->armarCampo('p1.sec4_fec_ini_act1', $datosForm["feciniact1"]); //FECHA INICIO ACT PRIMARIA
            $formulario->armarCampo('p1.sec4_fec_ini_act2', $datosForm["feciniact2"]); //FECHA INICIO ACT SECUNDARIA


            if (($datosForm["organizacion"] == '01') || ($datosForm["categoria"] == '1')) {
                if (($datosForm["impexp"] == '1') || ($datosForm["impexp"] == '3')) {
                    $formulario->armarCampo('p1.sec4_imp', 'X');
                }
                if (($datosForm["impexp"] == '2') || ($datosForm["impexp"] == '3')) {
                    $formulario->armarCampo('p1.sec4_exp', 'X');
                }
            }

            if ($datosForm["codaduaneros"] == '1' || $datosForm["codaduaneros"] == 'S') {
                $formulario->armarCampo('p1.sec4_usu_adu', 'X'); //USUARIO ADUANERO
            }

            $formulario->armarCampo('p1.sec4_desc_obj_soc', utf8_decode($datosForm["desactiv"])); //OBJETO SOCIAL
        }

        /**
         * ************************************************************************ INICIO HOJA 2 *************************************************************************
         */
        $formulario->agregarPagina(2, $tipoFormulario);

        $formulario->armarCampo('p2.cod_camara', $_SESSION ["generales"]["codigoempresa"] . ' - ' . $fec);

        if ($tipoimpresion != 'vacio') {

            /**
             * 
             * INFORMACION FINANCIERA
             * 
             */
            $decimalesVisibles = 2;

            if ($prediligenciado != 'si') {
                $formulario->armarCampo('p2.sec5_act_cor', self::truncateFloatForm($datosForm["actcte"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_act_no_cor', self::truncateFloatForm($datosForm["actnocte"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_act_tot', self::truncateFloatForm($datosForm["acttot"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_pas_cor', self::truncateFloatForm($datosForm["pascte"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_pas_no_cor', self::truncateFloatForm($datosForm["paslar"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_pas_tot', self::truncateFloatForm($datosForm["pastot"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_pas_net', self::truncateFloatForm($datosForm["pattot"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_pas_pat', self::truncateFloatForm($datosForm["paspat"], $decimalesVisibles));
                if ($datosForm["organizacion"] == '12' || $datosForm["organizacion"] == '14') {
                    if ($datosForm["categoria"] == '1') {
                        $formulario->armarCampo('p2.sec5_bal_soc', self::truncateFloatForm($datosForm["balsoc"], $decimalesVisibles));
                    }
                }
                $formulario->armarCampo('p2.sec5_ing_act_ord', self::truncateFloatForm($datosForm["ingope"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_otr_ing', self::truncateFloatForm($datosForm["ingnoope"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_cos_ven', self::truncateFloatForm($datosForm["cosven"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_gas_ope', self::truncateFloatForm($datosForm["gtoven"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_otr_gas', self::truncateFloatForm($datosForm["gtoadm"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_gas_imp', self::truncateFloatForm($datosForm["gasimp"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_uti_ope', self::truncateFloatForm($datosForm["utiope"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_res_per', self::truncateFloatForm($datosForm["utinet"], $decimalesVisibles));
            }

            /*
             * 
             * En el formulario define un campo check - 
             * Pero por instrucciones es posible que sea un código. 
             * JINT: $datosForm["gruponiif"] = del 0 al 7, donde 0 es no reportado
             *
             */

            if (trim($datosForm["gruponiif"]) != '') {
                $formulario->armarCampo('p2.sec5_grupo_niif', funcionesSii2_formularios::retornarGrupoNiifFormulario($mysqli,$datosForm["gruponiif"]));
            }

            /**
             * 
             * COMPOSICION DE CAPITAL
             * 
             */
            if ($datosForm["organizacion"] > '02' && $datosForm["categoria"] == '1') {
                if (floatval($datosForm["cap_porcnalpub"]) != 0) {
                    $formulario->armarCampo('p2.sec5_cap_nac_pub', $datosForm["cap_porcnalpub"]);
                }
                if (floatval($datosForm["cap_porcnalpri"]) != 0) {
                    $formulario->armarCampo('p2.sec5_cap_nac_pri', $datosForm["cap_porcnalpri"]);
                }
                if (floatval($datosForm["cap_porcextpub"]) != 0) {
                    $formulario->armarCampo('p2.sec5_cap_ext_pub', $datosForm["cap_porcextpub"]);
                }
                if (floatval($datosForm["cap_porcextpri"]) != 0) {
                    $formulario->armarCampo('p2.sec5_cap_ext_pri', $datosForm["cap_porcextpri"]);
                }
            }

            /**
             * 
             * SI ES EMPRESA ASOCIATIVA DE TRABAJO  -  APORTES
             * 
             */
            if (($datosForm["organizacion"] == '09') && ($datosForm["categoria"] == '1')) {
                if (substr($datosForm["matricula"], 0, 1) != 'S' && $datosForm["matricula"] != 'NUEVAESA') {
                    $formulario->armarCampo('p2.sec6_apt_lab_val', number_format($datosForm["apolab"], 2));
                    $formulario->armarCampo('p2.sec6_apt_act_val', number_format($datosForm["apoact"], 2));
                    $formulario->armarCampo('p2.sec6_apt_lab_adi_val', number_format($datosForm["apolabadi"], 2));
                    $formulario->armarCampo('p2.sec6_apt_din_val', number_format($datosForm["apodin"], 2));
                    $tot_apo = $datosForm["apolab"] +
                            $datosForm["apoact"] +
                            $datosForm["apolabadi"] +
                            $datosForm["apodin"];

                    $formulario->armarCampo('p2.sec6_apt_din_val', number_format($tot_apo, 2));

                    if ($tot_apo != 0) {
                        $formulario->armarCampo('p2.sec6_apt_lab_por', number_format((($datosForm["apolab"] * 100) / $tot_apo), 2));
                        $formulario->armarCampo('p2.sec6_apt_act_por', number_format((($datosForm["apoact"] * 100) / $tot_apo), 2));
                        $formulario->armarCampo('p2.sec6_apt_lab_adi_por', number_format((($datosForm["apolabadi"] * 100) / $tot_apo), 2));
                        $formulario->armarCampo('p2.sec6_apt_din_por', number_format((($datosForm["apodin"] * 100) / $tot_apo), 2));
                        $formulario->armarCampo('p2.sec6_tot_apt_por', number_format($tot_apo, 2));
                    }
                }
            }

            $formulario->armarCampo('p2.sec7_ref_ent_nom1', $datosForm["refcrenom1"]);
            $formulario->armarCampo('p2.sec7_ref_ent_tel1', $datosForm["refcretel1"]);
            $formulario->armarCampo('p2.sec7_ref_ent_nom2', $datosForm["refcrenom2"]);
            $formulario->armarCampo('p2.sec7_ref_ent_tel2', $datosForm["refcretel2"]);

            /**
             * 
             * REFERENCIAS
             * 
             */
            $formulario->armarCampo('p2.sec7_ref_com_nom1', $datosForm["refcomnom1"]);
            $formulario->armarCampo('p2.sec7_ref_com_tel1', $datosForm["refcomtel1"]);
            $formulario->armarCampo('p2.sec7_ref_com_nom2', $datosForm["refcomnom2"]);
            $formulario->armarCampo('p2.sec7_ref_com_tel2', $datosForm["refcomtel2"]);

            /**
             * 
             * ESTADO ACTUAL DE LA EMPRESA
             * 
             */
            //2017-08-25 - WSI - Ajustado para mtomas variables estadocapturado y estadocapturadootros
            if (($datosForm["organizacion"] != '01') && (($datosForm["categoria"] == '0') || ($datosForm["categoria"] == '1'))) {
                if (isset($datosForm["estadocapturado"])) {
                    $formulario->armarCampo('p2.sec8_cod_est_per_jud', $datosForm["estadocapturado"]);
                }
                if (isset($datosForm["estadocapturadootros"])) {
                    $formulario->armarCampo('p2.sec8_otro_est', $datosForm["estadocapturadootros"]);
                }
            }


            if ($prediligenciado != 'si') {
                if (ltrim($datosForm["personal"], "0") == '') {
                    $datosForm["personal"] = 0;
                }
                $formulario->armarCampo('p2.sec8_num_emp', $datosForm["personal"]);

                //2017-08-25 - WSI - Ajustado para utilizar valor campo definido.
                $cntEstabSucAge = 0;
                if (isset($datosForm["cantest"])) {
                    $cntEstabSucAge = trim($datosForm["cantest"]);
                }

                if ($cntEstabSucAge > 0) {
                    $formulario->armarCampo('p2.sec8_eas_si', 'X');
                    $formulario->armarCampo('p2.sec8_eas_num', $cntEstabSucAge);
                } else {
                    $formulario->armarCampo('p2.sec8_eas_no', 'X');
                }


                if (strtoupper($datosForm["procesosinnovacion"]) == '1') {
                    $formulario->armarCampo('p2.sec8_innov_si', 'X');
                } else {
                    $formulario->armarCampo('p2.sec8_innov_no', 'X');
                }

                if (strtoupper($datosForm["empresafamiliar"]) == '1') {
                    $formulario->armarCampo('p2.sec8_emp_fam_si', 'X');
                } else {
                    $formulario->armarCampo('p2.sec8_emp_fam_no', 'X');
                }

                $formulario->armarCampo('p2.sec8_por_emp_temp', $datosForm["personaltemp"]);
            }

            /**
             * 
             * DETALLE DE BIENES RAICES QUE POSEE
             * 
             */
            $iBienes = 0;
            if (!empty($datosForm["bienes"])) {
                foreach ($datosForm["bienes"] as $bien) {
                    $iBienes ++;
                    switch ($iBienes) {
                        case 1 :
                        case 2 :
                            $formulario->armarCampo('p2.sec9_mat' . $iBienes, $bien ["matinmo"]);
                            $formulario->armarCampo('p2.sec9_dir' . $iBienes, $bien ["dir"]);
                            $formulario->armarCampo('p2.sec9_bar' . $iBienes, $bien ["barrio"]);
                            $formulario->armarCampo('p2.sec9_mun' . $iBienes, $bien ["muni"]);
                            $formulario->armarCampo('p2.sec9_dep' . $iBienes, $bien ["dpto"]);
                            $formulario->armarCampo('p2.sec9_pas' . $iBienes, $bien ["pais"]);
                            break;
                    }
                }
            }
            /**
             * 
             * LEY 1780 DE 2016
             * 
             */
            if (trim($datosForm["cumplerequisitos1780"]) != '') {
                if (strtoupper($datosForm["cumplerequisitos1780"]) == 'S') {
                    $formulario->armarCampo('p2.sec10_1780_dec_si', 'X');
                } else {
                    $formulario->armarCampo('p2.sec10_1780_dec_no', 'X');
                }
            }

            if (trim($datosForm["cumplerequisitos1780primren"]) != '') {
                if (strtoupper($datosForm["cumplerequisitos1780primren"]) == 'S') {
                    $formulario->armarCampo('p2.sec10_1780_cumple_si', 'X');
                } else {
                    $formulario->armarCampo('p2.sec10_1780_cumple_no', 'X');
                }
            }

            /**
             * 
             * PROTECCIÓN SOCIAL
             * 
             */
            if (strtoupper($datosForm["aportantesegsocial"]) == 'S') {
                $formulario->armarCampo('p2.sec11_apor_si', 'X');
            } else {
                $formulario->armarCampo('p2.sec11_apor_no', 'X');
            }

            /**
             * 
             * TIPO DE APORTANTE
             * 
             */
            switch ($datosForm["tipoaportantesegsocial"]) {
                case "1" :
                    $formulario->armarCampo('p2.sec11_tipo_apt1', 'X'); //APORTANTE 200 O MAS COTIZANTES
                    break;
                case "2" :
                    $formulario->armarCampo('p2.sec11_tipo_apt2', 'X'); //CUENTA CON MENOS DE 200 COTIZANTES
                    break;
                case "3" :
                    $formulario->armarCampo('p2.sec11_tipo_apt3', 'X'); //APORTANTE BENEFICIARIO ART5 1429
                    break;
                case "4" :
                    $formulario->armarCampo('p2.sec11_tipo_apt4', 'X'); //APORTANTE INDEPENDIENTE
                    break;
            }



            if ($numliq != 0) {

                if ($datosForm["organizacion"] == '01') {

                    $nombre_completo = $datosForm["ape1"] . ' ' . $datosForm["ape2"] . ' ' . $datosForm["nom1"] . ' ' . $datosForm["nom2"];

                    $formulario->armarCampo('p2.firma_nom', utf8_decode($nombre_completo));
                    $formulario->armarCampo('p2.firma_ide', $datosForm["identificacion"]);

                    switch ($datosForm["tipoidentificacion"]) {
                        case "1" :
                            $formulario->armarCampo('p2.firma_cc', 'X');
                            break;
                        case "3" :
                            $formulario->armarCampo('p2.firma_ce', 'X');
                            break;
                        case "4" :
                            $formulario->armarCampo('p2.firma_ti', 'X');
                            break;
                        case "5" :
                            $formulario->armarCampo('p2.firma_pas', 'X');
                            break;
                    }
                    //REVISAR
                    $formulario->armarCampo('p2.firma_pais', '');
                } else {
                    if (isset($datosForm["propietarios"])) {
                        if (isset($datosForm["propietarios"][1]["nombrepropietario"])) {
                            $formulario->armarCampo('p2.firma_nom', utf8_decode($datosForm["propietarios"][1]["nombrepropietario"]));
                            $formulario->armarCampo('p2.firma_ide', $datosForm["propietarios"][1]["identificacionpropietario"]);

                            switch ($datosForm["propietarios"][1]["idtipoidentificacionpropietario"]) {
                                case "1" :
                                    $formulario->armarCampo('p2.firma_cc', 'X');
                                    break;
                                case "3" :
                                    $formulario->armarCampo('p2.firma_ce', 'X');
                                    break;
                                case "4" :
                                    $formulario->armarCampo('p2.firma_ti', 'X');
                                    break;
                                case "5" :
                                    $formulario->armarCampo('p2.firma_pas', 'X');
                                    break;
                            }
                            //REVISAR
                            $formulario->armarCampo('p2.firma_pais', '');
                        }
                    }
                }
            }
            $formulario->armarCampo('p2.firma_elec', utf8_decode($txtFirmaElectronica));
        }

        if ($datosForm["organizacion"] == '12' || $datosForm["organizacion"] == '14') {

            /**
             * ************************************************************************ INICIO HOJA 11 *************************************************************************
             */
            $formulario->agregarPagina(11, $tipoFormulario);

            $formulario->armarCampo('p11.cod_camara', $_SESSION ["generales"]["codigoempresa"] . ' - ' . $fec);

            if ($tipoimpresion != 'vacio') {


                $formulario->armarCampo('p11.sec1_num_aso', $datosForm["ctresacntasociados"]);
                $formulario->armarCampo('p11.sec1_num_muj', $datosForm["ctresacntmujeres"]);
                $formulario->armarCampo('p11.sec1_num_hom', $datosForm["ctresacnthombres"]);

                if (strtoupper($datosForm["ctresapertgremio"]) == 'S') {
                    $formulario->armarCampo('p11.sec1_grem_si', 'X');
                    $formulario->armarCampo('p11.sec1_grem_cual', $datosForm["ctresagremio"]);
                } else {
                    $formulario->armarCampo('p11.sec1_grem_no', 'X');
                }

                $formulario->armarCampo('p11.sec1_ent_cur_eco', $datosForm["ctresaacredita"]);
                $formulario->armarCampo('p11.sec1_ent_eje_ivc', $datosForm["ctresaivc"]);

                if (strtoupper($datosForm["ctresainfoivc"]) == 'S') {
                    $formulario->armarCampo('p11.sec1_doc_ivc_si', 'X');
                } else {
                    $formulario->armarCampo('p11.sec1_doc_ivc_no', 'X');
                }

                if (strtoupper($datosForm["ctresaautregistro"]) == 'S') {
                    $formulario->armarCampo('p11.sec1_aut_reg_si', 'X');
                    $formulario->armarCampo('p11.sec1_ent_aut', $datosForm["ctresaentautoriza"]);
                } else {
                    $formulario->armarCampo('p11.sec1_aut_reg_no', 'X');
                }


                /**
                 * 
                 * NATURALEZA DE LA ESADL
                 * 
                 */
                if ($datosForm["organizacion"] == '12' || $datosForm["organizacion"] == '14') {
                    switch ($datosForm["ctresacodnat"]) {
                        case "1" :
                            $formulario->armarCampo('p11.sec2_esadl_fun', 'X'); //FUNDACIONES
                            break;
                        case "2" :
                            $formulario->armarCampo('p11.sec2_esadl_aso', 'X'); //ASOCIACIONES
                            break;
                        case "3" :
                            $formulario->armarCampo('p11.sec2_esadl_cor', 'X'); //CORPORACIONES
                            break;
                        case "4" :
                            $formulario->armarCampo('p11.sec2_esadl_econ_sol', 'X'); //ENTIDAD DE ECONOMIA SOLIDARIA
                            break;
                    }
                }

                $formulario->armarCampo('p11.sec2_esadl_otro', ''); //OTRO
                //REVISAR 
                $formulario->armarCampo('p11.sec2_esadl_otro_cual', ''); //TEXTO CUAL OTRO

                $homologacionEsadl = retornarRegistroMysqli2($mysqli, "mreg_clase_esadl", "mostrar='S' and id='" . $datosForm["claseespesadl"] . "'", "codigorues");
                $formulario->armarCampo('p11.sec2_esadl_cod', $homologacionEsadl);

                if (strtoupper($datosForm["ctresadiscap"]) == 'S') {
                    $formulario->armarCampo('p11.sec3_disc_si', 'X');
                } else {
                    $formulario->armarCampo('p11.sec3_disc_no', 'X');
                }

                if (strtoupper($datosForm["ctresalgbti"]) == 'S') {
                    $formulario->armarCampo('p11.sec3_lgtbi_si', 'X');
                } else {
                    $formulario->armarCampo('p11.sec3_lgtbi_no', 'X');
                }

                if (strtoupper($datosForm["ctresaetnia"]) == 'S') {
                    $formulario->armarCampo('p11.sec3_etnia_si', 'X');
                    $formulario->armarCampo('p11.sec3_etnia_cual', $datosForm["ctresacualetnia"]);
                } else {
                    $formulario->armarCampo('p11.sec3_etnia_no', 'X');
                }

                if (strtoupper($datosForm["ctresaindgest"]) == 'S') {
                    $formulario->armarCampo('p11.sec3_ind_gest_si', 'X');
                } else {
                    $formulario->armarCampo('p11.sec3_ind_gest_no', 'X');
                }

                if (strtoupper($datosForm["ctresadespvictreins"]) == 'S') {
                    $formulario->armarCampo('p11.sec3_vict_si', 'X');
                    $formulario->armarCampo('p11.sec3_vict_cual', $datosForm["ctresacualdespvictreins"]);
                } else {
                    $formulario->armarCampo('p11.sec3_vict_no', 'X');
                }


                if (isset($datosForm["propietarios"])) {
                    if (isset($datosForm["propietarios"][1]["nombrepropietario"])) {
                        $formulario->armarCampo('p2.firma_nom', utf8_decode($datosForm["propietarios"][1]["nombrepropietario"]));
                        $formulario->armarCampo('p2.firma_ide', $datosForm["propietarios"][1]["identificacionpropietario"]);

                        switch ($datosForm["propietarios"][1]["idtipoidentificacionpropietario"]) {
                            case "1" :
                                $formulario->armarCampo('p11.firma_cc', 'X');
                                break;
                            case "3" :
                                $formulario->armarCampo('p11.firma_ce', 'X');
                                break;
                            case "4" :
                                $formulario->armarCampo('p11.firma_ti', 'X');
                                break;
                            case "5" :
                                $formulario->armarCampo('p11.firma_pas', 'X');
                                break;
                        }
                        //REVISAR
                        $formulario->armarCampo('p11.firma_pais', '');
                    }
                }

                $formulario->armarCampo('p11.firma_elec', utf8_decode($txtFirmaElectronica));
            }
        }

        $fechaHora = date("Ymd") . date("His");
        if ($tipoimpresion != 'vacio') {
            $name = PATH_ABSOLUTO_SITIO . "/tmp/" . session_id() . "-Formulario2017-Principal-" . $datosForm["matricula"] . "-" . $fechaHora . ".pdf";
            $name1 = session_id() . "-Formulario2017-Principal-" . $datosForm["matricula"] . '-' . $fechaHora . ".pdf";
            $formulario->Output($name, "F");
        } else {
            $name = PATH_ABSOLUTO_SITIO . "/tmp/" . session_id() . "-Formulario2017-Principal-" . $fechaHora . ".pdf";
            $name1 = session_id() . "-Formulario2017-Principal-" . $fechaHora . ".pdf";
            $formulario->Output($name, "F");
        }
        return $name1;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $numrec
     * @param type $numliq
     * @param type $tipoimpresion
     * @param type $txtFirmaElectronica
     * @return boolean|string
     */
    public static function armarPdfPrincipalAnosAnteriores($mysqli, $numrec = '', $numliq = 0, $tipoimpresion = '', $txtFirmaElectronica = '') {


        try {

            if (!defined('ACTIVAR_CIRCULAR_002_2016')) {
                define('ACTIVAR_CIRCULAR_002_2016', '');
            }

            if (ACTIVAR_CIRCULAR_002_2016 == 'SI2') {
                require_once ('pdfFormularioRues-2017.php'); //VERSION 23 DE MAYO DE 2017
                $formulario = new formularioRues2017();
            } else {
                return false;
            }



            $datosForm = $_SESSION ["formulario"]["datos"];

            $formulario->setNumeroRecuperacion($numrec);
            $formulario->setNumeroLiquidacion($numliq);
            $formulario->setFechaImpresion(date('Y/m/d H:i:s'));

            if ($tipoimpresion == 'vacio') {
                $formulario->agregarPagina(4, $tipoFormulario);
            }

            $item = 0;

            foreach ($datosForm["f"] as $iAno => $fin) {

                if ($iAno != $datosForm["anodatos"]) {
                    $inclu = 'si';
                    if ($datosForm["organizacion"] == '12' || $datosForm["organizacion"] == '14') {
                        if ($datosForm["categoria"] == '1') {
                            if ($iAno < '2013') {
                                $inclu = 'no';
                            } else {
                                if ($iAno <= $datosForm["ultanoren"]) {
                                    $inclu = 'si';
                                }
                            }
                        }
                    }
                    if ($inclu == 'si') {
                        $item ++;
                        if ($item == 5) {
                            $item = 1;
                        }
                        if ($item == 1) {

                            if ($tipoimpresion == 'borrador') {
                                $tipoFormulario = 0;
                            } else {
                                $tipoFormulario = 1;
                            }
                            $formulario->agregarPagina(4, $tipoFormulario);
                        }

                        /**
                         * 
                         * FECHA DE DILIGENCIAMIENTO
                         * 
                         */
                        if (isset($_SESSION ["tramite"]["fechaultimamodificacion"]) && (!empty($_SESSION ["tramite"]["fechaultimamodificacion"]))) {
                            $fec = $_SESSION ["tramite"]["fechaultimamodificacion"];
                        } else {
                            if (isset($_SESSION ["tramite"]["fecha"]) && (!empty($_SESSION ["tramite"]["fecha"]))) {
                                $fec = $_SESSION ["tramite"]["fecha"];
                            } else {
                                $fec = date("Ymd");
                            }
                        }

                        $formulario->armarCampo('p4.cod_camara', $_SESSION ["generales"]["codigoempresa"] . ' - ' . $fec);

                        $texto = ltrim($datosForm["nit"], '0');
                        $textonit = '';
                        $textodv = '';
                        if (trim($texto) != '') {
                            $textonit = substr($texto, 0, strlen($texto) - 1);
                        }
                        if (trim($texto) != '') {
                            $textodv = substr($texto, strlen($texto) - 1, 1);
                        }
                        if ($datosForm["organizacion"] != '01') {
                            $formulario->armarCampo('p4.nit_num', $textonit);
                            $formulario->armarCampo('p4.nit_dv', $textodv);
                        } else {
                            $formulario->armarCampo('p4.nit', 'X');
                            $formulario->armarCampo('p4.nit_num', $textonit);
                            $formulario->armarCampo('p4.nit_dv', $textodv);
                        }

                        $formulario->armarCampo('p4.mat', $datosForm["matricula"]);
                        if ($datosForm["organizacion"] != '01') {
                            $formulario->armarCampo('p4.raz_soc', utf8_decode($datosForm["nombre"]));
                        } else {
                            if (trim($datosForm["ape1"]) == '') {
                                $formulario->armarCampo('p4.raz_soc', utf8_decode($datosForm["nombre"]));
                            } else {
                                $formulario->armarCampo('p4.ape1', utf8_decode($datosForm["ape1"]));
                                $formulario->armarCampo('p4.ape2', utf8_decode($datosForm["ape2"]));
                                $formulario->armarCampo('p4.nom', utf8_decode($datosForm["nom1"] . ' ' . $datosForm["nom2"]));
                            }
                        }

                        $formulario->armarCampo('p4.f' . $item . '_ano', $fin ["anodatos"], $item);
                        $formulario->armarCampo('p4.f' . $item . '_act_cor', self::truncateFloatForm($fin ["actcte"], 0), $item);
                        $formulario->armarCampo('p4.f' . $item . '_act_no_cor', self::truncateFloatForm($fin ["actnocte"], 0), $item);
                        $formulario->armarCampo('p4.f' . $item . '_act_tot', self::truncateFloatForm($fin ["acttot"], 0), $item);
                        $formulario->armarCampo('p4.f' . $item . '_pas_cor', self::truncateFloatForm($fin ["pascte"], 0), $item);
                        $formulario->armarCampo('p4.f' . $item . '_pas_no_cor', self::truncateFloatForm($fin ["paslar"], 0), $item);
                        $formulario->armarCampo('p4.f' . $item . '_pas_tot', self::truncateFloatForm($fin ["pastot"], 0), $item);
                        $formulario->armarCampo('p4.f' . $item . '_pas_net', self::truncateFloatForm($fin ["pattot"], 0), $item);
                        $formulario->armarCampo('p4.f' . $item . '_pas_pat', self::truncateFloatForm($fin ["paspat"], 0), $item);

                        if ($datosForm["organizacion"] == '12' || $datosForm["organizacion"] == '14') {
                            if ($datosForm["categoria"] == '1') {
                                $formulario->armarCampo('p4.f' . $item . '_bal_soc', self::truncateFloatForm($fin ["balsoc"], 0), $item);
                            }
                        }

                        $formulario->armarCampo('p4.f' . $item . '_ing_act_ord', self::truncateFloatForm($fin ["ingope"], 0), $item);
                        $formulario->armarCampo('p4.f' . $item . '_otr_ing', self::truncateFloatForm($fin ["ingnoope"], 0), $item);
                        $formulario->armarCampo('p4.f' . $item . '_cos_ven', self::truncateFloatForm($fin ["cosven"], 0), $item);
                        $formulario->armarCampo('p4.f' . $item . '_gas_ope', self::truncateFloatForm($fin ["gtoven"], 0), $item);
                        $formulario->armarCampo('p4.f' . $item . '_otr_gas', self::truncateFloatForm($fin ["gtoadm"], 0), $item);
                        $formulario->armarCampo('p4.f' . $item . '_gas_imp', self::truncateFloatForm($fin ["gasimp"], 0), $item);
                        $formulario->armarCampo('p4.f' . $item . '_uti_ope', self::truncateFloatForm($fin ["utiope"], 0), $item);
                        $formulario->armarCampo('p4.f' . $item . '_res_per', self::truncateFloatForm($fin ["utinet"], 0), $item);
                        //

                        if ($datosForm["organizacion"] == '01') {

                            $nombre_completo = $datosForm["ape1"] . ' ' . $datosForm["ape2"] . ' ' . $datosForm["nom1"] . ' ' . $datosForm["nom2"];

                            $formulario->armarCampo('p4.firma_nom', utf8_decode($nombre_completo));
                            $formulario->armarCampo('p4.firma_ide', $datosForm["identificacion"]);

                            switch ($datosForm["tipoidentificacion"]) {
                                case "1" :
                                    $formulario->armarCampo('p4.firma_cc', 'X');
                                    break;
                                case "3" :
                                    $formulario->armarCampo('p4.firma_ce', 'X');
                                    break;
                                case "4" :
                                    $formulario->armarCampo('p4.firma_ti', 'X');
                                    break;
                                case "5" :
                                    $formulario->armarCampo('p4.firma_pas', 'X');
                                    break;
                            }
                            //REVISAR
                            $formulario->armarCampo('p4.firma_pais', '');
                        } else {
                            if (isset($datosForm["propietarios"])) {
                                $formulario->armarCampo('p4.firma_nom', utf8_decode($datosForm["propietarios"][1]["nombrepropietario"]));
                                $formulario->armarCampo('p4.firma_ide', $datosForm["propietarios"][1]["identificacionpropietario"]);

                                switch ($datosForm["propietarios"][1]["idtipoidentificacionpropietario"]) {
                                    case "1" :
                                        $formulario->armarCampo('p4.firma_cc', 'X');
                                        break;
                                    case "3" :
                                        $formulario->armarCampo('p4.firma_ce', 'X');
                                        break;
                                    case "4" :
                                        $formulario->armarCampo('p4.firma_ti', 'X');
                                        break;
                                    case "5" :
                                        $formulario->armarCampo('p4.firma_pas', 'X');
                                        break;
                                }
                                //REVISAR
                                $formulario->armarCampo('p4.firma_pais', '');
                            }
                        }
                        $formulario->armarCampo('p4.firma_elec', utf8_decode($txtFirmaElectronica));
                    }
                } // fin if
            } // fin foreach

            $fechaHora = date("Ymd") . date("His");
            $name = PATH_ABSOLUTO_SITIO . "/tmp/" . session_id() . "-Formulario2017-Principal-Anteriores-" . $datosForm["matricula"] . '-' . $fechaHora . ".pdf";
            $name1 = session_id() . "-Formulario2017-Principal-Anteriores-" . $datosForm["matricula"] . '-' . $fechaHora . ".pdf";
            $formulario->Output($name, "F");
            return $name1;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Función que obtiene el código RUES de organización específica empleado 
     * en formulario H1 y H2 según Circular 004 del 2017 (instrucciones)
     * @param type $org
     * @param type $clGen
     * @param type $clEsp
     * @param type $clEco
     * @return string
     */
    private static function homologacionOrganizacionEspecificaRUES($org, $clGen, $clEsp, $clEco) {

        switch ($org) {
            case '01':
            case '02':
            case '03':
            case '04':
            case '05':
            case '06':
            case '07':
            case '08':
            case '09':
            case '16':
                $org_rues = $org;
                break;
            case '10':
                $org_rues = '12';
                break;
            case '11':
                $org_rues = '10';
                break;
            case '15':
                $org_rues = '13';
                break;
            case '12':
                if (trim($clGen) != '') {
                    switch ($clGen) {
                        case '1':
                            $org_rues = '32';
                            break;
                        case '3':
                            $org_rues = '31';
                            break;
                        case '0':
                            $org_rues = '34';
                            break;
                        default :
                            $org_rues = NULL;
                            break;
                    }
                }
                if (!empty($org_rues)) {
                    return $org_rues;
                }

                if (trim($clEsp) != '') {
                    switch ($clEsp) {
                        case '20':
                            $org_rues = '21';
                            break;
                        case '25':
                            $org_rues = '26';
                            break;
                        case '26':
                            $org_rues = '27';
                            break;
                        case '29':
                            $org_rues = '29';
                            break;
                        case '41':
                            $org_rues = '30';
                            break;
                        case '60':
                            $org_rues = '34';
                            break;
                        case '62':
                            $org_rues = '34';
                            break;
                        default :
                            $org_rues = '33';
                            break;
                    }
                } else {
                    $org_rues = '33';
                }
                break;
            case '14':
                if (trim($clEco) != '') {
                    switch ($clEco) {
                        case '03':
                            $org_rues = '25';
                            break;
                        case '05':
                            $org_rues = '23';
                            break;
                        case '07':
                            $org_rues = '24';
                            break;
                        default:
                            $org_rues = '22';
                            break;
                    }
                } else {
                    $org_rues = '22';
                }
                break;
            default :
                $org_rues = NULL;
                break;
        }

        if (!empty($org_rues)) {
            return $org_rues;
        } else {
            return '';
        }
    }

    /**
     * Función que obtiene el código RUES de organización general empleado 
     * en formulario H1 y H2 según Circular 004 del 2017 (instrucciones)
     * @param type $org
     * @param type $clEsp
     * @param type $arrCiiu
     * @return string
     */
    private static function homologacionOrganizacionGeneralRUES($org, $clEsp, $arrCiiu) {

        switch ($org) {
            case '01':
            case '02':
            case '03':
            case '04':
            case '05':
            case '06':
            case '07':
            case '09':
            case '16':
                $soc_rues = '02';
                break;
            case '10':
                $soc_rues = '01';
                break;
            case '15':
                $soc_rues = '03';
                break;
            case '12':
                $soc_rues = '04';
                break;
            case '14':
                $soc_rues = '08';
                break;
            default :
                $soc_rues = NULL;
                break;
        }

        if (!empty($soc_rues)) {
            return $soc_rues;
        }


        switch ($clEsp) {
            case '61':
                $soc_rues = '05';
                break;
            case '60':
            case '62':
                $soc_rues = '07';
                break;
            default :
                $soc_rues = NULL;
                break;
        }


        if (!empty($soc_rues)) {
            return $soc_rues;
        }

        if (($org != '12') && ($org != '14')) {

            if (!empty($arrCiiu)) {
                if (!in_array("R9200", $arrCiiu)) {
                    $soc_rues = '02';
                    return $soc_rues;
                }
            } else {
                $soc_rues = NULL;
            }
        }

        if (!empty($soc_rues)) {
            return $soc_rues;
        }


        if (in_array("R9200", $arrCiiu)) {
            $soc_rues = '06';
            return $soc_rues;
        }
        return '';
    }

    /**
     * Función que cambia el prefijo de Esadl y Solidarias al formato RUES de matrícula
     * @param type $valorMatricula
     * @return string
     */
    private static function homologacionMatriculaRUES($valorMatricula) {

        $valor = trim($valorMatricula);

        $valorSinN = str_replace('N', '800', $valor);
        $valorSinS = str_replace('S', '900', $valorSinN);
        $valorMatriculaSalida = str_pad($valorSinS, 10, "0", STR_PAD_LEFT);
        if (is_numeric($valorMatriculaSalida)) {
            return $valorMatriculaSalida;
        } else {
            return '';
        }
    }

    /**
     * Función que obtiene el código RUES de organización implementado en Sincronizaciones hacia RUES
     * @param type $org
     * @param type $clGen
     * @param type $clEsp
     * @param type $clEco
     * @return type
     */
    private static function homologacionOrganizacionRUES($org, $clGen, $clEsp, $clEco) {

        switch ($org) {
            case '01':
            case '02':
            case '03':
            case '04':
            case '05':
            case '06':
            case '07':
            case '08':
            case '09':
            case '16':
                $tipoOrgRues = $org;
                break;
            case '10':
                $tipoOrgRues = '12';
                break;
            case '11':
                $tipoOrgRues = '10';
                break;
            case '15':
                $tipoOrgRues = '13';
                break;
            case '12':
                if (trim($clGen) != '') {
                    switch ($clGen) {
                        case '1':
                            $tipoOrgRues = '32';
                            break;
                        case '3':
                            $tipoOrgRues = '31';
                            break;
                        case '0':
                            $tipoOrgRues = '34';
                            break;
                        default :
                            $tipoOrgRues = NULL;
                            break;
                    }
                }
                if (!empty($tipoOrgRues)) {
                    return $tipoOrgRues;
                }

                if (trim($clEsp) != '') {
                    switch ($clEsp) {
                        case '20':
                            $tipoOrgRues = '21';
                            break;
                        case '25':
                            $tipoOrgRues = '26';
                            break;
                        case '26':
                            $tipoOrgRues = '27';
                            break;
                        case '29':
                            $tipoOrgRues = '29';
                            break;
                        case '41':
                            $tipoOrgRues = '30';
                            break;
                        case '60':
                            $tipoOrgRues = '34';
                            break;
                        case '62':
                            $tipoOrgRues = '34';
                            break;
                        default :
                            $tipoOrgRues = '33';
                            break;
                    }
                } else {
                    $tipoOrgRues = '33';
                }
                break;
            case '14':
                if (trim($clEco) != '') {
                    switch ($clEco) {
                        case '03':
                            $tipoOrgRues = '25';
                            break;
                        case '05':
                            $tipoOrgRues = '23';
                            break;
                        case '07':
                            $tipoOrgRues = '24';
                            break;
                        default:
                            $tipoOrgRues = '22';
                            break;
                    }
                } else {
                    $tipoOrgRues = '22';
                }
                break;
            default :
                $tipoOrgRues = NULL;
                break;
        }

        if (!empty($tipoOrgRues)) {
            return $tipoOrgRues;
        } else {
            return $tipoOrgRues;
        }
    }

    /**
     * Función que obtiene el código RUES de Categoria implementado en Sincronizaciones hacia RUES
     * @param type $org
     * @param type $cat
     * @return string
     */
    private static function homologacionCategoriaRUES($org, $cat) {
        if ($org == '01') {
            return '00';
        }
        if ($org == '02') {
            return '04';
        }
        if ($cat == '2') {
            return '02';
        }
        if ($cat == '3') {
            return '03';
        }
        if (($org > '02') && ($cat == '1')) {
            return '01';
        }
    }

    /**
     * Función que obtiene el código RUES de Sociedad implementado en Sincronizaciones hacia RUES
     * @param type $org
     * @param type $clEsp
     * @param type $arrCiiu
     * @return string
     */
    private static function homologacionSociedadRUES($org, $clEsp, $arrCiiu) {

        switch ($org) {
            case '01':
            case '02':
            case '03':
            case '04':
            case '05':
            case '06':
            case '07':
            case '09':
            case '16':
                $tipoSociedadRues = '02';
                break;
            case '10':
                $tipoSociedadRues = '01';
                break;
            case '15':
                $tipoSociedadRues = '03';
                break;
            case '12':
                $tipoSociedadRues = '04';
                break;
            case '14':
                $tipoSociedadRues = '08';
                break;
            default :
                $tipoSociedadRues = NULL;
                break;
        }

        if (!empty($tipoSociedadRues)) {
            return $tipoSociedadRues;
        }


        switch ($clEsp) {
            case '61':
                $tipoSociedadRues = '05';
                break;
            case '60':
            case '62':
                $tipoSociedadRues = '07';
                break;
            default :
                $tipoSociedadRues = NULL;
                break;
        }


        if (!empty($tipoSociedadRues)) {
            return $tipoSociedadRues;
        }

        if (($org != '12') && ($org != '14')) {

            if (!empty($arrCiiu)) {
                if (!in_array("R9200", $arrCiiu)) {
                    $tipoSociedadRues = '02';
                    return $tipoSociedadRues;
                }
            } else {
                $tipoSociedadRues = NULL;
            }
        }

        if (!empty($tipoSociedadRues)) {
            return $tipoSociedadRues;
        }


        if (in_array("R9200", $arrCiiu)) {
            $tipoSociedadRues = '06';
            return $tipoSociedadRues;
        }



        return NULL;
    }

    /**
     * Función que obtiene el código RUES de Estado Matrícula implementado en Sincronizaciones hacia RUES
     * @param type $valorEstado
     * @return string
     */
    private static function homologacionEstadoMatriculaRUES($valorEstado) {

        $valor = trim($valorEstado);

        $arrEM = array(
            'MA' => '01', //activa
            'IA' => '01', //activa
            'MI' => '01', //activa para rues
            //Se vuelve a asignar MG=03 solicitud jint 2017-07-05
            'MG' => '03', //cancelada
            'MC' => '03', //cancelada
            'IC' => '03', //cancelada
            'MF' => '09',
            'NA' => '05',
            'NM' => '06'
        );

        if (isset($arrEM[$valor])) {
            return $arrEM[$valor];
        } else {
            return '';
        }
    }

    /**
     * Función que obtiene el código RUES de Tipo Identificación implementado en Sincronizaciones hacia RUES
     * @param type $valorTipoIde
     * @return string
     */
    private static function homologacionTipoIdentificacionRUES($valorTipoIde) {

        $valor = trim($valorTipoIde);

        if (!empty($valor)) {
            switch ($valor) {
                case '7':
                    $valor = '06';
                    break;
                case '0':
                    $valor = '06';
                    break;
                case 'E':
                    $valor = '08';
                    break;
                case 'R':
                    $valor = '07';
                    break;
                default:
                    $valor = str_pad($valor, 2, "0", STR_PAD_LEFT);
                    break;
            }
            return $valor;
        } else {
            return '06';
        }
    }

    /**
     * Función que obtiene el código RUES de Motivo Cancelación implementado en Sincronizaciones hacia RUES
     * @param type $valor
     * @return string
     */
    private static function homologacionMotivoCancelacionRUES($valor) {

        //BAS_MOTIVOS_CANCELACION
        //MC - IC 
        //PENDIENTE PARA REVISAR HOMOLOGACION

        if ($valor != '0') {
            $valorCampo = ltrim(trim($valor), "0");
        } else {
            $valorCampo = $valor;
        }

        $arrMC = array(
            '0' => '00', //NORMAL
            '1' => '00', //NORMAL
            '2' => '00', //NORMAL
            '3' => '00', //NORMAL
            '4' => '01', //CANCELADA POR CAMBIO DOMICILIO
            '5' => '00', //NORMAL 
            '50' => '00'//NORMAL
        );

        if (isset($arrMC[$valorCampo])) {
            return $arrMC[$valorCampo];
        } else {
            return '';
        }
    }

    /**
     * Función que obtiene el código RUES de Estado Liquidación implementado en Sincronizaciones hacia RUES
     * @param type $valor
     * @return string
     */
    private static function homologacionEstadoLiquidacionRUES($valor) {

        //BAS_CODIGOS_LIQUIDACION
        //PENDIENTE PARA REVISAR HOMOLOGACION

        $valorCampo = trim($valor);

        $arrCL = array(
            '0' => '0', //NO LIQUIDADA
            '1' => '1', //LIQUIDADA
            '2' => '2', //EN LIQUIDACION
            '3' => '3', //INSCRIPCION ACTA DE REESTRUCTURACION
            '4' => '4', //INSCRIPCION PROCESO DE REESTRUCTURACION
            '5' => '5' //INSCRIPCION DE LA TERMINACION DEL PROCESO DE REESTRUCTURACION
        );

        if (isset($arrCL[$valorCampo])) {
            return $arrCL[$valorCampo];
        } else {
            return '';
        }
    }

    /**
     * Función que obtiene el código RUES de Código de Vinculos implementado en Sincronizaciones hacia RUES
     * @param type $org
     * @param type $valor
     * @return string
     */
    private static function homologacionCodigoVinculoRUES($org, $valor) {

        $valorCampo = trim($valor);

        if ($org > '01') {

            $arrCV = array(
                '2170' => '01', //REPRESENTANTE LEGAL - PRINCIPAL        
                '2171' => '02', //REPRESENTANTE LEGAL - 1 SUPLENTE
                '2172' => '02', //REPRESENTANTE LEGAL - 2 SUPLENTE
                '2173' => '02', //REPRESENTANTE LEGAL - 3 SUPLENTE
                '1100' => '03', //SOCIO CAPITALISTA
                '1101' => '03', //SOCIO CAPITALISTA - SUPLENTE
                '1110' => '03', //SOCIO INDUSTRIAL
                '1120' => '03', //SOCIO GESTOR
                '1121' => '03', //PRIMER SUPLENTE DEL SOCIO GESTOR 
                '1122' => '03', //SEGUNDO SUPLENTE DEL SOCIO GESTOR 
                '1126' => '03', //SOCIO GESTOR ADMINISTRADOR 
                '1130' => '03', //SOCIO COMANDITARIO
                '1140' => '03', //SOCIO ACCIONISTA
                '1150' => '03', //SOCIO ADMINISTRADOR
                '1151' => '03', //PRIMER SUPLENTE DEL SOCIO ADMINISTRADOR
                '1152' => '03', //SEGUNDO SUPLENTE DEL SOCIO ADMINISTRADOR
                '1160' => '03', //SOCIO COLECTIVO
                '1170' => '03', //ASOCIADO
                '3110' => '03', //SOCIO EMPRESARIO (EU) 
                '2160' => '04', //REVISOR FISCAL - PRINCIPAL
                '2161' => '05', //REVISOR FISCAL - SUPLENTE
                '2100' => '06', //MIEMBRO DE LA JUNTA DIRECTIVA - PRINCIPAL
                '2101' => '07', //MIEMBRO DE LA JUNTA DIRECTIVA - 1 SUPLENTE
                '2101' => '07', //MIEMBRO DE LA JUNTA DIRECTIVA - 2 SUPLENTE
                '2101' => '07' //MIEMBRO DE LA JUNTA DIRECTIVA - 3 SUPLENTE
            );
        }

        if ($org == '12' || $org == '14') {

            $arrCV = array(
                '4170' => '01', //REPRESENTANTE LEGAL - PRINCIPAL        
                '4270' => '02', //REPRESENTANTE LEGAL - SUPLENTE
                '1100' => '03', //SOCIO CAPITALISTA
                '1101' => '03', //SOCIO CAPITALISTA - SUPLENTE
                '1110' => '03', //SOCIO INDUSTRIAL
                '1120' => '03', //SOCIO GESTOR
                '1121' => '03', //PRIMER SUPLENTE DEL SOCIO GESTOR 
                '1122' => '03', //SEGUNDO SUPLENTE DEL SOCIO GESTOR 
                '1126' => '03', //SOCIO GESTOR ADMINISTRADOR 
                '1130' => '03', //SOCIO COMANDITARIO
                '1140' => '03', //SOCIO ACCIONISTA
                '1150' => '03', //SOCIO ADMINISTRADOR
                '1151' => '03', //PRIMER SUPLENTE DEL SOCIO ADMINISTRADOR
                '1152' => '03', //SEGUNDO SUPLENTE DEL SOCIO ADMINISTRADOR
                '1160' => '03', //SOCIO COLECTIVO
                '1170' => '03', //ASOCIADO
                '3110' => '03', //SOCIO EMPRESARIO (EU) 
                '5160' => '04', //REVISOR FISCAL - PRINCIPAL
                '5260' => '05', //REVISOR FISCAL - SUPLENTE
                '4140' => '06', //MIEMBRO DE LA JUNTA DIRECTIVA - PRINCIPAL
                '4240' => '07' //MIEMBRO DE LA JUNTA DIRECTIVA - 3 SUPLENTE
            );
        }


        if (isset($arrCV[$valorCampo])) {
            return $arrCV[$valorCampo];
        } else {
            return '00';
        }
    }

    /**
     * Función que obtiene el código RUES de Tipo Propietario implementado en Sincronizaciones hacia RUES
     * @param type $valor
     * @return string
     */
    private static function homologacionTipoPropietarioRUES($valor) {

        $valorCampo = trim($valor);

        $arrTP = array(
            '0' => '1', //PROPIETARIO UNICO
            '1' => '2', //SOCIEDAD DE HECHO
            '2' => '3' //COPROPIETARIO
        );

        if (isset($arrTP[$valorCampo])) {
            return $arrTP[$valorCampo];
        } else {
            return '';
        }
    }

    /**
     * Función que obtiene el código RUES de Tipo de Pago implementado en Sincronizaciones hacia RUES
     * @param type $valor
     * @return string
     */
    private static function homologacionTipoPagoRUES($valor) {

        $valorCampo = trim($valor);

        $arrTP = array(
            '010201' => '01', //MATRICULA
            '010202' => '02', //RENOVACION
            '060100' => '03', //AFILIACION
            '010901' => '04' //BENEFICIO
        );

        if (isset($arrTP[$valorCampo])) {
            return $arrTP[$valorCampo];
        } else {
            return '';
        }
    }

    /**
     * Función que obtiene el código RUES de Naturaleza de ESADL implementado en Sincronizaciones hacia RUES
     * @param type $org
     * @param type $valor
     * @return string
     */
    private static function homologacionNaturalezaEsadlRUES($org, $valor) {

        if ($org == '14') {
            $valorCampo = '4';
        } else {

            $valorCampo = trim($valor);

            $arrNT = array(
                '2' => '1', //ASOCIACION
                '3' => '2', //CORPORACION
                '1' => '3' //FUNDACION
            );
        }

        if (isset($arrNT[$valorCampo])) {
            return $arrNT[$valorCampo];
        } else {
            return '';
        }
    }

    /**
     * 
     * @param type $mysqli
     * @param type $idmunicipio
     * @param type $idbarrio
     * @return type
     */
    private static function retornarNombreBarrio($mysqli, $idmunicipio, $idbarrio) {

        $nombreBarrio = retornarRegistroMysqli2($mysqli, "mreg_barriosmuni", "idmunicipio='" . $idmunicipio . "' and idbarrio='" . $idbarrio . "'", "nombre");

        if ($nombreBarrio == false || empty($nombreBarrio)) {
            return "";
        } else {
            return utf8_decode($nombreBarrio);
        }
    }

      private static function retornarGrupoNiifFormulario($mysqli, $id) {
        $res = retornarRegistroMysqli2($mysqli, 'bas_gruponiif', "id='" . $id . "'");
        if ($res == false || empty($res)) {
            return "";
        } else {
            return $res["idformulario"];
        }
    }

    /**
     * 
     * @param type $mysqli
     * @param type $idmunicipio
     * @return type
     */
    private static function retornarNombreMunicipio($mysqli, $idmunicipio) {

        $nombreMunicipio = retornarRegistroMysqli2($mysqli, "bas_municipios", "codigomunicipio='" . $idmunicipio . "'", "ciudad");

        if ($nombreMunicipio == false || empty($nombreMunicipio)) {
            return "";
        } else {
            return utf8_decode($nombreMunicipio);
        }
    }

    /**
     * 
     * @param type $mysqli
     * @param type $idmunicipio
     * @return type
     */
    private static function retornarNombreDpto($mysqli, $idmunicipio) {

        $nombreDepartamento = retornarRegistroMysqli2($mysqli, "bas_municipios", "codigomunicipio='" . $idmunicipio . "'", "departamento");

        if ($nombreDepartamento == false || empty($nombreDepartamento)) {
            return "";
        } else {
            return utf8_decode($nombreDepartamento);
        }
    }

    /**
     * 
     * @param type $mysqli
     * @param type $codnumpais
     * @return string
     */
    private static function retornarNombrePaisAbreviado($mysqli, $codnumpais) {

        if (is_numeric($codnumpais)) {

            $paisAbr = retornarRegistroMysqli2($mysqli, "bas_paises", "codnumpais='" . $codnumpais . "'", "idpais");

            if ($paisAbr == false || empty($paisAbr)) {
                return "";
            } else {
                return $paisAbr;
            }
        } else {
            return $codnumpais;
        }
    }

    /**
     * 
     * @param type $number
     * @param type $digitos
     * @return type
     */
    private static function truncateFloatForm($number, $digitos) {
        $raiz = 10;
        $multiplicador = pow($raiz, $digitos);
        $resultado = ((int) ($number * $multiplicador)) / $multiplicador;
        $x = number_format($resultado, $digitos, ".", ",");
        return $x;
    }

    /**
     * 
     * @param type $id
     * @return type
     */
    private static function calcularDv($id) {
        if (trim($id) != '') {
            $id = str_replace(array(".", ",", "-", " "), "", $id);
            $entrada = sprintf("%015s", $id);
            $identificacion = substr($entrada, 0, 15);
            $miContador = 0;
            $miResiduo = 0;
            $miChequeo = 0;
            $miArreglo = array(71, 67, 59, 53, 47, 43, 41, 37, 29, 23, 19, 17, 13, 7, 3);
            for ($miContador = 0; $miContador < strlen($identificacion); $miContador++) {
                $miChequeo = $miChequeo + (intval(substr($entrada, $miContador, 1)) * intval($miArreglo[$miContador]));
            }
            $miResiduo = $miChequeo % 11;
            if ($miResiduo > 1) {
                $nuevoDV = 11 - $miResiduo;
            } else {
                $nuevoDV = $miResiduo;
            }
            return $nuevoDV;
        } else {
            return '';
        }
    }

}

?>
