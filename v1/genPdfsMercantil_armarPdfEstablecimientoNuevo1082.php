<?php

/**
 * Función que realiza el armado de los formularios de registro mercantil 
 * a partir de la información del expediente - Incluye campos según Circular 
 * única - Establecimientos Sucursales y agencias Años principal
 * 
 * @param type $numrec
 * @param type $numliq
 * @param type $tipoimpresion
 * @param type $prediligenciado
 * @param type $txtFirmaElectronica
 * @param type $anoimprimir
 * @since 2017/05/15
 * @return string
 */
function armarPdfEstablecimientoNuevo1082Sii($dbx,$numrec = '', $numliq = 0, $tipoimpresion = '', $prediligenciado = 'no', $txtFirmaElectronica = '', $txtFirmaManuscrita = '', $ideFirmaManuscrita = '', $nomFirmaManuscrita = '', $anoimprimir = '') {

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

        if (!isset($datosForm["cc"]) || $datosForm["cc"] == '' || $datosForm["cc"] == CODIGO_EMPRESA) {
            $formulario->armarCampo('p3.cod_camara', $_SESSION ["generales"]["codigoempresa"] . ' - ' . $fec);
        } else {
            $formulario->armarCampo('p3.cod_camara', $datosForm["cc"] . ' - ' . $fec);
        }

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
        if ($anoimprimir == '') {
            $formulario->armarCampo('p3.ano_ren', $datosForm["anodatos"]);
        } else {
            $formulario->armarCampo('p3.ano_ren', $anoimprimir);
        }

        /**
         * 
         * INFORMACION GENERAL - DOMICILIO
         * 
         */
        $formulario->armarCampo('p3.sec1_nom_est', utf8_decode($datosForm["nombre"]));
        $formulario->armarCampo('p3.sec1_dom_dir', utf8_decode($datosForm["dircom"]));
        $formulario->armarCampo('p3.sec1_dom_pos', $datosForm["codigopostalcom"]);

        if (ltrim($datosForm["barriocom"], "0") != '') {
            $formulario->armarCampo('p3.sec1_dom_blvc', retornarRegistroMysqli2($dbx,'mreg_barriosmuni',"idmunicipio='" . $datosForm["muncom"] . "' and idbarrio='" . $datosForm["barriocom"] . "'","nombre"));
        }

        $formulario->armarCampo('p3.sec1_dom_tel1', $datosForm["telcom1"]);
        $formulario->armarCampo('p3.sec1_dom_tel2', $datosForm["telcom2"]);
        $formulario->armarCampo('p3.sec1_dom_tel3', $datosForm["celcom"]);
        $formulario->armarCampo('p3.sec1_dom_muni', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $datosForm["muncom"] . "'","ciudad")));
        $formulario->armarCampo('p3.sec1_dom_muni_num', substr($datosForm["muncom"], 2, 5));
        $formulario->armarCampo('p3.sec1_dom_dep', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $datosForm["muncom"] . "'","departamento")));
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
        if ($datosForm["organizacion"] != '02' && $datosForm["categoria"] != '3') {

            $formulario->armarCampo('p3.sec1_not_dir', utf8_decode($datosForm["dirnot"]));
            $formulario->armarCampo('p3.sec1_not_pos', $datosForm["codigopostalnot"]);

            if (ltrim($datosForm["barrionot"], "0") != '') {
                $formulario->armarCampo('p3.sec1_not_blvc', retornarRegistroMysqli2($dbx,'mreg_barriosmuni',"idmunicipio='" . $datosForm["munnot"] . "' and idbarrio='" . $datosForm["barrionot"] . "'","nombre"));
            }

            $formulario->armarCampo('p3.sec1_not_muni', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $datosForm["munnot"] . "'","ciudad")));
            $formulario->armarCampo('p3.sec1_not_muni_num', substr($datosForm["munnot"], 2, 5));
            $formulario->armarCampo('p3.sec1_not_dep', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $datosForm["munnot"] . "'","departamento")));
            $formulario->armarCampo('p3.sec1_not_dep_num', substr($datosForm["munnot"], 0, 2));
            $formulario->armarCampo('p3.sec1_not_email', $datosForm["emailnot"]);
        }

        if ($prediligenciado != 'si') {
            if ($anoimprimir == '') {
                $formulario->armarCampo('p3.sec1_act_vin', truncateFloatFormSii2($datosForm["actvin"], 0));
                $formulario->armarCampo('p3.sec1_num_trab', $datosForm["personal"]);
            } else {
                foreach ($datosForm["financiera"] as $if) {
                    if ($if["anodatos"] == $anoimprimir) {
                        $formulario->armarCampo('p3.sec1_act_vin', truncateFloatFormSii2($if["actvin"], 0));
                        $formulario->armarCampo('p3.sec1_num_trab', $if["personal"]);
                    }
                }
            }
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
                            $formulario->armarCampo('p3.sec4_prop' . $ind . '_muni', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $prop["municipiopropietario"] . "'","ciudad")));
                            $formulario->armarCampo('p3.sec4_prop' . $ind . '_muni_num', substr($prop["municipiopropietario"], 2, 5));
                            $formulario->armarCampo('p3.sec4_prop' . $ind . '_dep', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $prop["municipiopropietario"] . "'","departamento")));
                            $formulario->armarCampo('p3.sec4_prop' . $ind . '_dep_num', substr($prop["municipiopropietario"], 0, 2));
                            $formulario->armarCampo('p3.sec4_prop' . $ind . '_tel1', $prop["telefonopropietario"]);
                            $formulario->armarCampo('p3.sec4_prop' . $ind . '_tel2', $prop["telefono2propietario"]);
                            $formulario->armarCampo('p3.sec4_prop' . $ind . '_tel3', $prop["celularpropietario"]);
                            $formulario->armarCampo('p3.sec4_prop' . $ind . '_not_dir', utf8_decode($prop["direccionnotpropietario"]));
                            $formulario->armarCampo('p3.sec4_prop' . $ind . '_not_mun', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $prop["municipionotpropietario"] . "'","ciudad")));
                            $formulario->armarCampo('p3.sec4_prop' . $ind . '_not_mun_num', substr($prop["municipionotpropietario"], 2, 5));
                            $formulario->armarCampo('p3.sec4_prop' . $ind . '_not_dep', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $prop["municipionotpropietario"] . "'","departamento")));
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



        if ($datosForm["organizacion"] > '02' && ($datosForm["categoria"] == '2' || $datosForm["categoria"] == '3')) {
            if ($datosForm["cprazsoc"] != '') {
                $sep = separarDv($datosForm["cpnumnit"]);
                $formulario->armarCampo('p3.sec4_prop1_nom', utf8_decode($datosForm["cprazsoc"]));
                $formulario->armarCampo('p3.sec4_prop1_ide', $sep["identificacion"]);
                $formulario->armarCampo('p3.sec4_prop1_dv', $sep["dv"]);
                $formulario->armarCampo('p3.sec4_prop1_nit', 'X');
                $formulario->armarCampo('p3.sec4_prop1_mat', $datosForm["cpnummat"]);
                $formulario->armarCampo('p3.sec4_prop1_cam', $datosForm["cpcodcam"]);
                $formulario->armarCampo('p3.sec4_prop1_dir', $datosForm["cpdircom"]);
                $formulario->armarCampo('p3.sec4_prop1_muni', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $datosForm["cpcodmun"] . "'","ciudad")));
                $formulario->armarCampo('p3.sec4_prop1_muni_num', substr($datosForm["cpcodmun"], 2, 5));
                $formulario->armarCampo('p3.sec4_prop1_dep', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $datosForm["cpcodmun"] . "'","departamento")));
                $formulario->armarCampo('p3.sec4_prop1_dep_num', substr($datosForm["cpcodmun"], 0, 2));
                $formulario->armarCampo('p3.sec4_prop1_tel1', $datosForm["cpnumtel"]);
                $formulario->armarCampo('p3.sec4_prop1_tel2', $datosForm["cpnumtel2"]);
                $formulario->armarCampo('p3.sec4_prop1_tel3', $datosForm["cpnumtel3"]);
                $formulario->armarCampo('p3.sec4_prop1_not_dir', $datosForm["cpdirnot"]);
                $formulario->armarCampo('p3.sec4_prop1_not_mun', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $datosForm["cpmunnot"] . "'","ciudad")));
                $formulario->armarCampo('p3.sec4_prop1_not_mun_num', substr($datosForm["cpmunnot"], 2, 5));
                $formulario->armarCampo('p3.sec4_prop1_not_dep', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $datosForm["cpmunnot"] . "'","departamento")));
                $formulario->armarCampo('p3.sec4_prop1_not_dep_num', substr($datosForm["cpmunnot"], 0, 2));
            }
        }

        //
        if ($txtFirmaElectronica != '') {
            $formulario->armarCampo('p3.firma_elec', utf8_decode($txtFirmaElectronica));
        }

        //
        if ($txtFirmaManuscrita != '') {
            $formulario->armarCampoImagen('p3.firma_manuscrita', $txtFirmaManuscrita);
            $txtFirmaManuscrita = '';
            if ($ideFirmaManuscrita != '') {
                $txtFirmaManuscrita1 = "Formulario firmado en forma manuscrita, sobre dispositivo movil,";
                $txtFirmaManuscrita2 = "por " . $nomFirmaManuscrita . ', identificado con el Nro. ' . $ideFirmaManuscrita;
            }
            $formulario->armarCampoImagen('p3.firma_manuscrita_des1', $txtFirmaManuscrita1);
            $formulario->armarCampoImagen('p3.firma_manuscrita_des2', $txtFirmaManuscrita2);
        }
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

?>