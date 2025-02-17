<?php

/**
 * 
 * @param type $dbx
 * @param type $datosForm
 * @param type $tipotramite
 * @param type $numliq
 * @param type $numrec
 * @param type $nombrerepleg
 * @param type $identificacionrepleg
 * @param type $idtipoidentificacionrepleg
 * @param type $tipoimpresion
 * @param type $txtFirmaElectronica
 * @param type $fecha
 * @param type $hora
 * @param type $nota
 * @return string
 */
function armarPdfFormularioProponentes2020Api($dbx, $datosForm = array(), $tipotramite = '', $numliq = '', $numrec = '', $nombrerepleg = '', $identificacionrepleg = '', $idtipoidentificacionrepleg = '', $tipoimpresion = 'final', $txtFirmaElectronica = '', $fecha = '', $hora = '', $nota = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION ["generales"] ["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/fpdf.php');

    //
    ini_set('memory_limit', '2048M');

    if ($tipoimpresion != 'vacio') {

        // FECHA DEL FORMULARIO
        if (ltrim($numliq, "0") != '') {
            $arrLiq = \funcionesRegistrales::retornarMregLiquidacion($dbx, $numliq);
        }

        // Localiza datos modificados
        // - datosbasicos - S/N
        // - perjur - S / N
        // - repleg - S / N
        // - facultades - S / N
        // - ubicacion - S/N
        // - inffin1510 - S / N
        // - inffin399a - S / N
        // - inffin399b - S / N
        // - sitcontrol - S / N
        // - clasi1510 - S / N
        // - exp1510-XXX - S / N / E siendo XXX en número del contrato (secuencia)

        $aV = \funcionesRegistrales::verificarDatosModificadosApi($dbx, $numliq, 0, $tipotramite, $datosForm, $_SESSION ["generales"] ["codigoempresa"], '1510');

        // $aV = array ( "datosbasicos" => 'S', "perjur" => 'S', "repleg" => 'S', "facultades" => 'S', "ubicacion" => 'S', "inffin1510" => 'S', "sitcontrol" => 'S', "clasi1510" => 'S', "exp1510" => 'S', "exp1510-001" => 'S', "exp1510-002" => 'N', "exp1510-005" => 'S' );


        $paginador = 2;
        $cant_repleg = 0;

        if (trim($identificacionrepleg) == '') {
            if (isset($datosForm["representanteslegales"][1])) {
                $nombrerepleg = \funcionesGenerales::utf8_decode($datosForm["representanteslegales"][1]["nombrerepleg"]);
                $identificacionrepleg = $datosForm["representanteslegales"][1]["identificacionrepleg"];
                $idtipoidentificacionrepleg = $datosForm["representanteslegales"][1]["idtipoidentificacionrepleg"];
                if (!isset($datosForm["representanteslegales"][1]["paisrepleg"])) {
                    $datosForm["representanteslegales"][1]["paisrepleg"] = '';
                }
                $paisrepleg = $datosForm["representanteslegales"][1]["paisrepleg"];
            } else {
                $nombrerepleg = '';
                $identificacionrepleg = '';
                $idtipoidentificacionrepleg = '';
                $paisrepleg = '';
            }
        }

        if ($datosForm["organizacion"] == '99') {
            if (($aV ["perjur"] == 'S') || ($aV ["facultades"] == 'S')) {
                foreach ($datosForm["representanteslegales"] as $rep) {
                    if (ltrim($rep ["identificacionrepleg"], "0") != '') {
                        $cant_repleg++;
                    }
                }
            }
        }

        //
        $secuencia_imprimir = array();
        if ($tipotramite == 'inscripcionproponente' || $tipotramite == 'cambiodomicilioproponente') {
            $aV ["exp1510"] = 'S';
            $secuencia_imprimir = array();
            foreach ($datosForm["exp1510"] as $cnt) {
                $secuencia_imprimir [] = $cnt ["secuencia"];
            }
        } else {
            // if (trim($aV ["exp1510"]) == 'S') {
            foreach ($datosForm["exp1510"] as $key => $cnt) {
                if (strlen($cnt ["secuencia"]) < 4) {
                    $ind = 'exp1510-' . sprintf("%03s", $cnt ["secuencia"]);
                } else {
                    $ind = 'exp1510-' . $cnt ["secuencia"];
                }
                if (trim($aV [$ind]) != 'E') {
                    if (trim($cnt ["nombrecontratante"]) != '') {
                        $secuencia_imprimir [] = $cnt ["secuencia"];
                    }
                }
            } // fin foreach
            // }
        } // fin if exp1510
    } // fin if vacio
    //
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/pdfFormularioRues-2020.php');
    $formulario = new formularioRues2020Api();

    if ($tipoimpresion != 'vacio') {
        $formulario->setNumeroRecuperacion($numrec);
        $formulario->setNumeroLiquidacion($numliq);
        if ($fecha != '') {
            $formulario->setFechaImpresion($fecha . ' ' . $hora);
        } else {
            $formulario->setFechaImpresion(date('Y/m/d H:i:s'));
        }
        if ($nota != '') {
            $formulario->setNota($nota);
        }
    } else {
        $formulario->setNumeroRecuperacion('');
        $formulario->setNumeroLiquidacion('');
        $formulario->setFechaImpresion('');
        $formulario->setNota('');
    }


    if ($tipoimpresion != 'vacio') {

        if ($tipoimpresion == 'borrador') {
            $tipoFormulario = 0;
        } else {
            $tipoFormulario = 1;
        }

        /**
         * ************************************************************************ INICIO HOJA 1 *************************************************************************
         */
        if ($tipotramite != 'actualizacionproponente399') {
            $formulario->agregarPagina(1, $tipoFormulario);
            if (isset($arrLiq ["fechaultimamodificacion"])) {
                $fecha = $arrLiq ["fechaultimamodificacion"];
            } else {
                if (isset($arrLiq ["fecha"])) {
                    $fecha = $arrLiq ["fecha"];
                } else {
                    $fecha = date("Ymd");
                }
            }
            $formulario->armarCampo('p1.cod_camara', $_SESSION ["generales"]["codigoempresa"] . ' - ' . $fecha);
            if (!isset($_SESSION["formulario"]["tipotramite"])) {
                $_SESSION["formulario"]["tipotramite"] = '';
            }
            switch ($tipotramite) {
                case "inscripcionproponente" :
                    $formulario->armarCampo('p1.sec1_col3_ins', 'X');
                    if (ltrim($datosForm["proponente"], "0") != '') {
                        $formulario->armarCampo('p1.sec1_col3_num_ins', $datosForm["proponente"]);
                    }
                    break;
                case "renovacionproponente" :
                    $formulario->armarCampo('p1.sec1_col3_ren', 'X');
                    $formulario->armarCampo('p1.sec1_col3_num_ins', $datosForm["proponente"]);
                    break;
                case "actualizacionproponente" :
                    $formulario->armarCampo('p1.sec1_col3_act', 'X');
                    $formulario->armarCampo('p1.sec1_col3_num_ins', $datosForm["proponente"]);
                    break;
                case "cancelacionproponente" :
                    $formulario->armarCampo('p1.sec1_col3_can', 'X');
                    $formulario->armarCampo('p1.sec1_col3_num_ins', $datosForm["proponente"]);
                    break;
                case "cambiodomicilioproponente" :
                    $formulario->armarCampo('p1.sec1_col3_act_tra', 'X');
                    $formulario->armarCampo('p1.sec1_col3_cam_ant', str_replace(array("CAMARA DE COMERCIO DE ", "CAMARA DE COMERCIO DEL "), "", $datosForm["propcamaraorigennombre"]));
                    break;
            }
            if (trim($datosForm["ape1"]) == '') {
                $formulario->armarCampo('p1.sec2_raz_soc', \funcionesGenerales::utf8_decode($datosForm["nombre"]));
                $formulario->armarCampo('p1.sec2_sig', \funcionesGenerales::utf8_decode($datosForm["sigla"]));
            }
            $formulario->armarCampo('p1.sec2_nit', substr($datosForm["nit"], 0, - 1));
            $formulario->armarCampo('p1.sec2_dv', substr($datosForm["nit"], - 1, 1));
            $formulario->armarCampo('p1.sec2_ape1', \funcionesGenerales::utf8_decode($datosForm["ape1"]));
            $formulario->armarCampo('p1.sec2_ape2', \funcionesGenerales::utf8_decode($datosForm["ape2"]));
            $formulario->armarCampo('p1.sec2_nom1', \funcionesGenerales::utf8_decode($datosForm["nom1"]));
            $formulario->armarCampo('p1.sec2_nom2', \funcionesGenerales::utf8_decode($datosForm["nom2"]));
            if ($datosForm["organizacion"] == '01') {
                if (!isset($datosForm["fecexpdoc"])) {
                    $datosForm["fecexpdoc"] = '';
                }
                if (!isset($datosForm["idmunidoc"])) {
                    $datosForm["idmunidoc"] = '';
                }
                $nombrerepleg = \funcionesGenerales::utf8_decode($datosForm["nom1"]) . ' ' . \funcionesGenerales::utf8_decode($datosForm["nom2"]) . ' ' . \funcionesGenerales::utf8_decode($datosForm["ape1"]) . ' ' . \funcionesGenerales::utf8_decode($datosForm["ape2"]);
                $identificacionrepleg = $datosForm["identificacion"];
                $idtipoidentificacionrepleg = $datosForm["idtipoidentificacion"];
                $formulario->armarCampo('p1.sec2_ide', $datosForm["identificacion"]);
                if ($datosForm["fecexpdoc"] != '') {
                    $formulario->armarCampo('p1.sec2_fec_exp', $datosForm["fecexpdoc"]);
                }
                if ($datosForm["idmunidoc"] != '') {
                    $formulario->armarCampo('p1.sec2_lug_exp', retornarNombreMunicipioMysqliApi($dbx, $datosForm["idmunidoc"]));
                }
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
            if (!isset($datosForm["paisexpdoc"])) {
                $datosForm["paisexpdoc"] = '';
            }
            if (!isset($datosForm["codigozonacom"])) {
                $datosForm["codigozonacom"] = '';
            }
            
            if (!isset($datosForm["codigopostalcom"])) {
                $datosForm["codigopostalcom"] = '';
            }
            if (!isset($datosForm["ctrubi"])) {
                $datosForm["ctrubi"] = '';
            }
            if (!isset($datosForm["paicom"])) {
                $datosForm["paicom"] = '';
            }
            if (!isset($datosForm["barriocom"])) {
                $datosForm["barriocom"] = '';
            }
            if (!isset($datosForm["painot"])) {
                $datosForm["painot"] = '';
            }
            if (!isset($datosForm["barrionot"])) {
                $datosForm["barrionot"] = '';
            }
            if (!isset($datosForm["barrionot"])) {
                $datosForm["barrionot"] = '';
            }
            if (!isset($datosForm["ctrmen"])) {
                $datosForm["ctrmen"] = '';
            }
            $formulario->armarCampo('p1.sec2_pais', retornarNombrePaisMysqliApi($dbx, $datosForm["paisexpdoc"]));
            if (trim($aV ["ubicacion"]) == 'S') {
                $formulario->armarCampo('p1.sec3_dom_dir', \funcionesGenerales::utf8_decode($datosForm["dircom"]));
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
                $formulario->armarCampo('p1.sec3_dom_muni', \funcionesGenerales::utf8_decode(retornarNombreMunicipioMysqliApi($dbx, $datosForm["muncom"])));
                $formulario->armarCampo('p1.sec3_dom_muni_num', substr($datosForm["muncom"], 2, 5));
                $formulario->armarCampo('p1.sec3_dom_dep', \funcionesGenerales::utf8_decode(retornarNombreDptoMysqliApi($dbx, $datosForm["muncom"])));
                $formulario->armarCampo('p1.sec3_dom_dep_num', substr($datosForm["muncom"], 0, 2));
                $formulario->armarCampo('p1.sec3_dom_pais', retornarNombrePaisMysqliApi($dbx, $datosForm["paicom"]));
                if (ltrim((string)$datosForm["barriocom"], "0") != '') {

                    $formulario->armarCampo('p1.sec3_dom_lbvc', retornarNombreBarrioMysqliApi($dbx, $datosForm["muncom"], $datosForm["barriocom"]));
                }
                $formulario->armarCampo('p1.sec3_dom_tel1', $datosForm["telcom1"]);
                $formulario->armarCampo('p1.sec3_dom_tel2', $datosForm["telcom2"]);
                $formulario->armarCampo('p1.sec3_dom_tel3', $datosForm["celcom"]);
                $formulario->armarCampo('p1.sec3_dom_email', $datosForm["emailcom"]);
                $formulario->armarCampo('p1.sec3_not_dir', \funcionesGenerales::utf8_decode($datosForm["dirnot"]));
                $formulario->armarCampo('p1.sec3_not_muni', \funcionesGenerales::utf8_decode(retornarNombreMunicipioMysqliApi($dbx, $datosForm["munnot"])));
                $formulario->armarCampo('p1.sec3_not_muni_num', substr($datosForm["munnot"], 2, 5));
                $formulario->armarCampo('p1.sec3_not_dep', \funcionesGenerales::utf8_decode(retornarNombreDptoMysqliApi($dbx, $datosForm["munnot"])));
                $formulario->armarCampo('p1.sec3_not_dep_num', substr($datosForm["munnot"], 0, 2));
                $formulario->armarCampo('p1.sec3_not_pais', retornarNombrePaisMysqliApi($dbx, $datosForm["painot"]));
                if (ltrim((string)$datosForm["barrionot"], "0") != '') {

                    $formulario->armarCampo('p1.sec3_dom_lbvc', retornarNombreBarrioMysqliApi($dbx, $datosForm["munnot"], $datosForm["barrionot"]));
                }
                $formulario->armarCampo('p1.sec3_not_tel1', $datosForm["telnot"]);
                $formulario->armarCampo('p1.sec3_not_tel2', $datosForm["telnot2"]);
                $formulario->armarCampo('p1.sec3_not_tel3', $datosForm["celnot"]);
                $formulario->armarCampo('p1.sec3_not_email', $datosForm["emailnot"]);
                switch (substr((string)$datosForm["ctrmen"], 0, 1)) {
                    case "S" :
                        $formulario->armarCampo('p1.sec3_not_si', 'X');
                        break;
                    case "N" :
                        $formulario->armarCampo('p1.sec3_not_no', 'X');
                        break;
                }
            } else {
                $formulario->armarCampo('p1.sec3_dom_dir', '---');
                $formulario->armarCampo('p1.sec3_dom_muni', '---');
                $formulario->armarCampo('p1.sec3_dom_muni_num', '---');
                $formulario->armarCampo('p1.sec3_dom_dep', '---');
                $formulario->armarCampo('p1.sec3_dom_dep_num', '---');
                $formulario->armarCampo('p1.sec3_dom_pais', '---');
                $formulario->armarCampo('p1.sec3_dom_lbvc', '---');
                $formulario->armarCampo('p1.sec3_dom_tel1', '---');
                $formulario->armarCampo('p1.sec3_dom_tel2', '---');
                $formulario->armarCampo('p1.sec3_dom_tel3', '---');
                $formulario->armarCampo('p1.sec3_dom_email', '---');
                $formulario->armarCampo('p1.sec3_not_dir', '---');
                $formulario->armarCampo('p1.sec3_not_muni', '---');
                $formulario->armarCampo('p1.sec3_not_muni_num', '---');
                $formulario->armarCampo('p1.sec3_not_dep', '---');
                $formulario->armarCampo('p1.sec3_not_dep_num', '---');
                $formulario->armarCampo('p1.sec3_not_pais', '---');
                $formulario->armarCampo('p1.sec3_dom_lbvc', '---');
                $formulario->armarCampo('p1.sec3_not_tel1', '---');
                $formulario->armarCampo('p1.sec3_not_tel2', '---');
                $formulario->armarCampo('p1.sec3_not_tel3', '---');
                $formulario->armarCampo('p1.sec3_not_email', '---');
                $formulario->armarCampo('p1.sec3_not_si', '---');
                $formulario->armarCampo('p1.sec3_not_no', '---');
            }
        }

        /**
         * ************************************************************************ INICIO HOJA 2 *************************************************************************
         */
        if ($tipotramite != 'actualizacionproponente399') {
            $formulario->agregarPagina(2, $tipoFormulario);
            $formulario->armarCampo('p2.cod_camara', $_SESSION ["generales"]["codigoempresa"] . ' - ' . $fecha);
            if ($tipotramite != 'cancelacionproponente') {
                if (trim($aV ["inffin1510"]) == 'S') {
                    $decimalesVisibles = 2;
                    $acttot = doubleval($datosForm["inffin1510_actcte"]) + doubleval($datosForm["inffin1510_actnocte"]);
                    $formulario->armarCampo('p2.sec5_act_cor', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_actcte"]));
                    $formulario->armarCampo('p2.sec5_act_no_cor', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_actnocte"]));
                    $formulario->armarCampo('p2.sec5_act_tot', \funcionesGenerales::truncarValorNuevoFormulario($acttot));
                    $formulario->armarCampo('p2.sec5_pas_cor', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_pascte"]));
                    $formulario->armarCampo('p2.sec5_pas_no_cor', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_paslar"]));
                    $formulario->armarCampo('p2.sec5_pas_tot', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_pastot"]));
                    $formulario->armarCampo('p2.sec5_pas_net', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_patnet"]));
                    $formulario->armarCampo('p2.sec5_pas_pat', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_paspat"]));
                    if ($datosForm["organizacion"] == '12' || $datosForm["organizacion"] == '14' || $datosForm["organizacion"] == '99') {
                        $formulario->armarCampo('p2.sec5_bal_soc', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_balsoc"]));
                    }
                    $formulario->armarCampo('p2.sec5_ing_act_ord', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_ingope"]));
                    $formulario->armarCampo('p2.sec5_otr_ing', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_ingnoope"]));
                    $formulario->armarCampo('p2.sec5_cos_ven', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_cosven"]));
                    $formulario->armarCampo('p2.sec5_gas_ope', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_gasope"]));
                    $formulario->armarCampo('p2.sec5_otr_gas', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_gasnoope"]));
                    $formulario->armarCampo('p2.sec5_gas_imp', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_gasimp"]));
                    $formulario->armarCampo('p2.sec5_uti_ope', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_utiope"]));
                    $formulario->armarCampo('p2.sec5_res_per', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_utinet"]));
                } else {
                    $formulario->armarCampo('p2.sec5_act_cor', '---');
                    $formulario->armarCampo('p2.sec5_act_no_cor', '---');
                    $formulario->armarCampo('p2.sec5_act_tot', '---');
                    $formulario->armarCampo('p2.sec5_pas_cor', '---');
                    $formulario->armarCampo('p2.sec5_pas_no_cor', '---');
                    $formulario->armarCampo('p2.sec5_pas_tot', '---');
                    $formulario->armarCampo('p2.sec5_pas_net', '---');
                    $formulario->armarCampo('p2.sec5_pas_pat', '---');
                    $formulario->armarCampo('p2.sec5_bal_soc', '---');
                    $formulario->armarCampo('p2.sec5_ing_act_ord', '---');
                    $formulario->armarCampo('p2.sec5_otr_ing', '---');
                    $formulario->armarCampo('p2.sec5_cos_ven', '---');
                    $formulario->armarCampo('p2.sec5_gas_ope', '---');
                    $formulario->armarCampo('p2.sec5_otr_gas', '---');
                    $formulario->armarCampo('p2.sec5_gas_imp', '---');
                    $formulario->armarCampo('p2.sec5_uti_ope', '---');
                    $formulario->armarCampo('p2.sec5_res_per', '---');
                }
            }
            if (trim($datosForm["inffin1510_gruponiif"]) != '') {
                $formulario->armarCampo('p2.sec5_grupo_niif', \funcionesGenerales::retornarGrupoNiifFormulario($dbx, $datosForm["inffin1510_gruponiif"]));
                $formulario->armarCampo('p2.sec5_grupo_niif_des', \funcionesGenerales::retornarGrupoNiifFormularioDescripcion($dbx, $datosForm["inffin1510_gruponiif"]));
            }
            if ($idtipoidentificacionrepleg != '7') {
                $formulario->armarCampo('p2.firma_nom', strtoupper($nombrerepleg));
                $formulario->armarCampo('p2.firma_ide', $identificacionrepleg);
                switch ($idtipoidentificacionrepleg) {
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
            }
            $formulario->armarCampo('p2.firma_pais', '');
            $formulario->armarCampo('p2.firma_elec', \funcionesGenerales::utf8_decode($txtFirmaElectronica));
        }
    }

    /**
     * ************************************************************************ INICIO HOJA 6 *************************************************************************
     */
    if ($tipoimpresion != 'vacio' and $tipotramite != 'cancelacionproponente' && $tipotramite != 'cambiodomicilioproponente') {
        if ($tipotramite != 'actualizacionproponente399') {
            $formulario->agregarPagina(6, $tipoFormulario);
            $formulario->armarCampo('p6.cod_camara', $_SESSION ["generales"]["codigoempresa"] . ' - ' . $fecha);
            switch ($tipotramite) {
                case "inscripcionproponente" :
                    $formulario->armarCampo('p6.ins', 'X');
                    break;
                case "renovacionproponente" :
                    $formulario->armarCampo('p6.ren', 'X');
                    break;
                case "actualizacionproponente" :
                    $formulario->armarCampo('p6.act', 'X');
                    break;
                case "cambiodomicilioproponente" :
                    $formulario->armarCampo('p6.act_tra', 'X');
                    break;
            }
            $formulario->armarCampo('p6.nit', substr($datosForm["nit"], 0, - 1));
            $formulario->armarCampo('p6.dv', substr($datosForm["nit"], - 1, 1));
            switch ($datosForm["tamanoempresa"]) {
                case "4" :
                    $formulario->armarCampo('p6.sec1_tam1', 'X');
                    break;
                case "3" :
                    $formulario->armarCampo('p6.sec1_tam2', 'X');
                    break;
                case "2" :
                    $formulario->armarCampo('p6.sec1_tam3', 'X');
                    break;
                case "1" :
                    $formulario->armarCampo('p6.sec1_tam4', 'X');
                    break;
            }
            if (trim($aV["inffin1510"]) == 'S') {
                $formulario->armarCampo('p6.sec2_ano', substr($datosForm["inffin1510_fechacorte"], 0, 4));
                $formulario->armarCampo('p6.sec2_mes', substr($datosForm["inffin1510_fechacorte"], 4, 2));
                $formulario->armarCampo('p6.sec2_dia', substr($datosForm["inffin1510_fechacorte"], 6, 2));
                $decimalesVisibles = 2;
                if ($datosForm["inffin1510_pascte"] == 0) {
                    $formulario->armarCampo('p6.sec2_ind_liq', 'INDETERMINADO');
                } else {
                    $indiceLiquidez = \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_indliq"]);
                    $formulario->armarCampo('p6.sec2_ind_liq', $indiceLiquidez);
                }
                if (floatval($datosForm["inffin1510_acttot"]) == 0) {
                    $datosForm["inffin1510_nivend"] = 'INDETERMINADO';
                    $formulario->armarCampo('p6.sec2_ind_end', $datosForm["inffin1510_nivend"]);
                } else {
                    $indiceEndeudamiento = \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_nivend"]);
                    $formulario->armarCampo('p6.sec2_ind_end', $indiceEndeudamiento);
                }
                if ($datosForm["inffin1510_gasint"] != 0) {
                    $razonCobertura = \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_razcob"]);
                    $formulario->armarCampo('p6.sec2_raz_cob_upo', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_utiope"]));
                    $formulario->armarCampo('p6.sec2_raz_cob_gas', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_gasint"]));
                    $formulario->armarCampo('p6.sec2_raz_cob_tot', $razonCobertura);
                } else {
                    $formulario->armarCampo('p6.sec2_raz_cob_upo', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_utiope"]));
                    $formulario->armarCampo('p6.sec2_raz_cob_gas', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_gasint"]));
                    $formulario->armarCampo('p6.sec2_raz_cob_tot', 'INDETERMINADO');
                }
                if ($datosForm["inffin1510_patnet"] != 0) {
                    if (($datosForm["inffin1510_utiope"] < 0 && $datosForm["inffin1510_patnet"] < 0) ||
                            ($datosForm["inffin1510_utiope"] >= 0 && $datosForm["inffin1510_utiope"] >= 0)) {
                        $formulario->armarCampo('p6.sec3_ren_pat', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_renpat"]));
                    } else {
                        $formulario->armarCampo('p6.sec3_ren_pat', truncarValorNuevoFormularioSignoMenos($datosForm["inffin1510_renpat"]));
                    }
                } else {
                    $formulario->armarCampo('p6.sec3_ren_pat', 'INDETERMINADO');
                }
                if ($datosForm["inffin1510_acttot"] != 0) {
                    if (($datosForm["inffin1510_utiope"] < 0 && $datosForm["inffin1510_acttot"] < 0) ||
                    ($datosForm["inffin1510_utiope"] >= 0 && $datosForm["inffin1510_acttot"] >= 0)) {
                        $formulario->armarCampo('p6.sec3_ren_act', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin1510_renact"]));
                    } else {
                        $formulario->armarCampo('p6.sec3_ren_act', truncarValorNuevoFormularioSignoMenos($datosForm["inffin1510_renact"]));
                    }
                } else {
                    $formulario->armarCampo('p6.sec3_ren_act', 'INDETERMINADO');
                }
            } else {
                $formulario->armarCampo('p6.sec2_ind_liq', '---');
                $formulario->armarCampo('p6.sec2_ind_end', '---');
                $formulario->armarCampo('p6.sec2_raz_cob_upo', '---');
                $formulario->armarCampo('p6.sec2_raz_cob_gas', '---');
                $formulario->armarCampo('p6.sec2_raz_cob_tot', '---');
                $formulario->armarCampo('p6.sec3_ren_pat', '---');
                $formulario->armarCampo('p6.sec3_ren_act', '---');
            }
            if ($idtipoidentificacionrepleg != '7') {
                $formulario->armarCampo('p6.firma_nom', strtoupper($nombrerepleg));
                $formulario->armarCampo('p6.firma_ide', $identificacionrepleg);
                switch ($idtipoidentificacionrepleg) {
                    case "1" :
                        $formulario->armarCampo('p6.firma_cc', 'X');
                        break;
                    case "3" :
                        $formulario->armarCampo('p6.firma_ce', 'X');
                        break;
                    case "5" :
                        $formulario->armarCampo('p6.firma_pas', 'X');
                        break;
                }
            }
            $formulario->armarCampo('p6.firma_elec', \funcionesGenerales::utf8_decode($txtFirmaElectronica));
        }
    }

    if ($tipotramite != 'actualizacionproponente399') {
        if ($tipoimpresion == 'vacio') {
            $formulario->agregarPagina(6, $tipoFormulario);
        }
    }

    /**
     * ************************************************************************ INICIO HOJA 7 *************************************************************************
     */
    if ($tipotramite != 'actualizacionproponente399') {
        if (($tipoimpresion != 'vacio') and ( $tipotramite != 'cancelacionproponente')) {
            if (isset($datosForm["sitcontrol"]) && count($datosForm["sitcontrol"]) != 0) {
                $token_inicial_sit = 0;
                $token_final_sit = 22;
                $paginas_adicionales_sit = ceil(count($datosForm["sitcontrol"]) / 21);
                for ($hoja_sit = 1; $hoja_sit <= $paginas_adicionales_sit; $hoja_sit++) {
                    $formulario->agregarPagina(7, $tipoFormulario);
                    $formulario->armarCampo('p7.num_hoja', $paginador++, 1);
                    switch ($tipotramite) {
                        case "inscripcionproponente" :
                            $formulario->armarCampo('p7.ins', 'X');
                            break;
                        case "renovacionproponente" :
                            $formulario->armarCampo('p7.ren', 'X');
                            break;
                        case "actualizacionproponente" :
                            $formulario->armarCampo('p7.act', 'X');
                            break;
                        case "cambiodomicilioproponente" :
                            $formulario->armarCampo('p7.act_tra', 'X');
                            break;
                    }
                    $formulario->armarCampo('p7.nit', substr($datosForm["nit"], 0, - 1));
                    $formulario->armarCampo('p7.dv', substr($datosForm["nit"], - 1, 1));
                    if (trim($aV ["sitcontrol"]) == 'S') {
                        foreach ($datosForm["sitcontrol"] as $clave => $valor) {
                            if (($clave > $token_inicial_sit) and ( $clave < $token_final_sit)) {
                                $formulario->armarCampo('p7.nom', $valor ["nombre"], $clave - $token_inicial_sit);
                                $formulario->armarCampo('p7.ide', $valor ["identificacion"], $clave - $token_inicial_sit);
                                $formulario->armarCampo('p7.dom', retornarNombreMunicipioMysqliApi($dbx, $valor ["domicilio"]), $clave - $token_inicial_sit);
                                switch ($valor ["tipo"]) {
                                    case "0" :
                                        // MATRIZ
                                        $formulario->armarCampo('p7.sit_1', 'X', $clave - $token_inicial_sit);
                                        break;
                                    case "1" :
                                        // SUBORDINADA
                                        $formulario->armarCampo('p7.sit_2', 'X', $clave - $token_inicial_sit);
                                        break;
                                    case "2" :
                                        // CONTROLANTE
                                        $formulario->armarCampo('p7.sit_3', 'X', $clave - $token_inicial_sit);
                                        break;
                                    case "3" :
                                        // CONTROLADA
                                        $formulario->armarCampo('p7.sit_4', 'X', $clave - $token_inicial_sit);
                                        break;
                                    case "4" :
                                        // MATRIZ Y SUBORDINADA
                                        $formulario->armarCampo('p7.sit_1', 'X', $clave - $token_inicial_sit);
                                        $formulario->armarCampo('p7.sit_2', 'X', $clave - $token_inicial_sit);
                                        break;
                                    case "5" :
                                        // MATRIZ Y CONTROLANTE
                                        $formulario->armarCampo('p7.sit_1', 'X', $clave - $token_inicial_sit);
                                        $formulario->armarCampo('p7.sit_3', 'X', $clave - $token_inicial_sit);
                                        break;
                                    case "6" :
                                        // MATRIZ Y CONTROLADA
                                        $formulario->armarCampo('p7.sit_1', 'X', $clave - $token_inicial_sit);
                                        $formulario->armarCampo('p7.sit_4', 'X', $clave - $token_inicial_sit);
                                        break;
                                    case "7" :
                                        // SUBORDINADA Y CONTROLANTE
                                        $formulario->armarCampo('p7.sit_2', 'X', $clave - $token_inicial_sit);
                                        $formulario->armarCampo('p7.sit_3', 'X', $clave - $token_inicial_sit);
                                        break;
                                    case "8" :
                                        // SUBORDINADA Y CONTROLADA
                                        $formulario->armarCampo('p7.sit_2', 'X', $clave - $token_inicial_sit);
                                        $formulario->armarCampo('p7.sit_4', 'X', $clave - $token_inicial_sit);
                                        break;
                                    case "9" :
                                        // CONTROLANTE Y CONTROLADA
                                        $formulario->armarCampo('p7.sit_3', 'X', $clave - $token_inicial_sit);
                                        $formulario->armarCampo('p7.sit_4', 'X', $clave - $token_inicial_sit);
                                        break;
                                }
                            } // fin control tokens
                        } // fin foreach sitcontrol

                        $token_inicial_sit += 21;
                        $token_final_sit += 21;
                    } else {
                        for ($x = 1; $x <= 21; $x++) {
                            $formulario->armarCampo('p7.nom', '---', $x);
                            $formulario->armarCampo('p7.ide', '---', $x);
                            $formulario->armarCampo('p7.dom', '---', $x);
                            $formulario->armarCampo('p7.sit_1', '---', $x);
                            $formulario->armarCampo('p7.sit_2', '---', $x);
                            $formulario->armarCampo('p7.sit_3', '---', $x);
                            $formulario->armarCampo('p7.sit_4', '---', $x);
                        }
                    }

                    if ($idtipoidentificacionrepleg != '7') {
                        $formulario->armarCampo('p7.firma_nom', strtoupper($nombrerepleg));
                        $formulario->armarCampo('p7.firma_ide', $identificacionrepleg);
                        switch ($idtipoidentificacionrepleg) {
                            case "1" :
                                $formulario->armarCampo('p7.firma_cc', 'X');
                                break;
                            case "3" :
                                $formulario->armarCampo('p7.firma_ce', 'X');
                                break;
                            case "5" :
                                $formulario->armarCampo('p7.firma_pas', 'X');
                                break;
                        }
                    }
                    $formulario->armarCampo('p7.firma_elec', \funcionesGenerales::utf8_decode($txtFirmaElectronica));
                } // fin for paginas_adicionales_sit
            } // fin if count
        }
    }

    if ($tipotramite != 'actualizacionproponente399') {
        if ($tipoimpresion == 'vacio') {
            $formulario->agregarPagina(7, 1);
        }
    }

    /**
     * ************************************************************************ INICIO HOJA 8 *************************************************************************
     */
    if ($tipotramite != 'actualizacionproponente399') {
        if ($tipoimpresion != 'vacio' and $tipotramite != 'cancelacionproponente' && $tipotramite != 'cambiodomicilioproponente') {
            if (trim($aV ["clasi1510"]) == 'S') {
                $token_inicial_clasif = 0;
                $token_final_clasif = 61;
                if (empty($datosForm["clasi1510"])) {
                    $paginas_adicionales_clasif = 0;
                } else {
                $paginas_adicionales_clasif = ceil(count($datosForm["clasi1510"]) / 60);
                }
                for ($hoja_clasif = 1; $hoja_clasif <= $paginas_adicionales_clasif; $hoja_clasif++) {
                    $formulario->agregarPagina(8, $tipoFormulario);
                    $formulario->armarCampo('p8.num_hoja', $paginador++, 1);
                    switch ($tipotramite) {
                        case "inscripcionproponente" :
                            $formulario->armarCampo('p8.ins', 'X');
                            break;
                        case "renovacionproponente" :
                            $formulario->armarCampo('p8.ren', 'X');
                            break;
                        case "actualizacionproponente" :
                            $formulario->armarCampo('p8.act', 'X');
                            break;
                        case "cambiodomicilioproponente" :
                            $formulario->armarCampo('p8.tras', 'X');
                            break;
                    }
                    foreach ($datosForm["clasi1510"] as $clave => $valor) {

                        if (($clave > $token_inicial_clasif) and ( $clave < $token_final_clasif)) {
                            $formulario->armarCampo('p8.cod_seg', substr($valor, 0, 2), $clave - $token_inicial_clasif);
                            $formulario->armarCampo('p8.cod_fam', substr($valor, 2, 2), $clave - $token_inicial_clasif);
                            $formulario->armarCampo('p8.cod_cla', substr($valor, 4, 2), $clave - $token_inicial_clasif);
                        } // fin if tokens
                    } // fin foreach clasif1510
                    $token_inicial_clasif += 60;
                    $token_final_clasif += 60;
                    if ($idtipoidentificacionrepleg != '7') {
                        $formulario->armarCampo('p8.firma_nom', strtoupper($nombrerepleg));
                        $formulario->armarCampo('p8.firma_ide', $identificacionrepleg);
                        switch ($idtipoidentificacionrepleg) {
                            case "1" :
                                $formulario->armarCampo('p8.firma_cc', 'X');
                                break;
                            case "3" :
                                $formulario->armarCampo('p8.firma_ce', 'X');
                                break;
                            case "5" :
                                $formulario->armarCampo('p8.firma_pas', 'X');
                                break;
                        }
                    }
                    $formulario->armarCampo('p8.firma_elec', \funcionesGenerales::utf8_decode($txtFirmaElectronica));
                } // fin for paginas_adicionales_clasif
            } else {
                $formulario->agregarPagina(8, $tipoFormulario);
                $formulario->armarCampo('p8.num_hoja', $paginador++, 1);
                switch ($tipotramite) {
                    case "inscripcionproponente" :
                        $formulario->armarCampo('p8.ins', 'X');
                        break;
                    case "renovacionproponente" :
                        $formulario->armarCampo('p8.ren', 'X');
                        break;
                    case "actualizacionproponente" :
                        $formulario->armarCampo('p8.act', 'X');
                        break;
                    case "cambiodomicilioproponente" :
                        $formulario->armarCampo('p8.tras', 'X');
                        break;
                }
                for ($x = 1; $x <= 60; $x++) {
                    $formulario->armarCampo('p8.cod_seg', '--', $x);
                    $formulario->armarCampo('p8.cod_fam', '--', $x);
                    $formulario->armarCampo('p8.cod_cla', '--', $x);
                }
                if ($idtipoidentificacionrepleg != '7') {
                    $formulario->armarCampo('p8.firma_nom', strtoupper($nombrerepleg));
                    $formulario->armarCampo('p8.firma_ide', $identificacionrepleg);
                    switch ($idtipoidentificacionrepleg) {
                        case "1" :
                            $formulario->armarCampo('p8.firma_cc', 'X');
                            break;
                        case "3" :
                            $formulario->armarCampo('p8.firma_ce', 'X');
                            break;
                        case "5" :
                            $formulario->armarCampo('p8.firma_pas', 'X');
                            break;
                    }
                }
            }
            $formulario->armarCampo('p8.firma_elec', \funcionesGenerales::utf8_decode($txtFirmaElectronica));
        }
    }

    if ($tipotramite != 'actualizacionproponente399') {
        if ($tipoimpresion == 'vacio') {
            $formulario->agregarPagina(8, $tipoFormulario);
        }
    }

    /**
     * ************************************************************************ INICIO HOJA 9 *************************************************************************
     */
    if ($tipotramite != 'actualizacionproponente399') {
        if ($tipoimpresion != 'vacio' and $tipotramite != 'cancelacionproponente' && $tipotramite != 'cambiodomicilioproponente') {
            for ($item_repleg = 1; $item_repleg <= $cant_repleg; $item_repleg++) {
                $formulario->agregarPagina(9, $tipoFormulario);
                $formulario->armarCampo('p9.num_hoja', $paginador++, 1);
                switch ($tipotramite) {
                    case "inscripcionproponente" :
                        $formulario->armarCampo('p9.ins', 'X');
                        break;
                    case "renovacionproponente" :
                        $formulario->armarCampo('p9.ren', 'X');
                        break;
                    case "actualizacionproponente" :
                        $formulario->armarCampo('p9.act', 'X');
                        break;
                    case "cambiodomicilioproponente" :
                        $formulario->armarCampo('p9.tras', 'X');
                        break;
                }
                $formulario->armarCampo('p9.nit', substr($datosForm["nit"], 0, - 1));
                $formulario->armarCampo('p9.dv', substr($datosForm["nit"], - 1, 1));
                $formulario->armarCampo('p9.raz_soc', \funcionesGenerales::utf8_decode($datosForm["nombre"]));
                if (ltrim($datosForm["fechavencimiento"], "0") != '') {
                    $formulario->armarCampo('p9.dur_anio', substr($datosForm["fechavencimiento"], 0, 4));
                    $formulario->armarCampo('p9.dur_mes', substr($datosForm["fechavencimiento"], 4, 2));
                    $formulario->armarCampo('p9.dur_dia', substr($datosForm["fechavencimiento"], 6, 2));
                } else {
                    $formulario->armarCampo('p9.dur_ind', 'X');
                }
                $formulario->armarCampo('p9.pj_anio', substr($datosForm["fechaconstitucion"], 0, 4));
                $formulario->armarCampo('p9.pj_mes', substr($datosForm["fechaconstitucion"], 4, 2));
                $formulario->armarCampo('p9.pj_dia', substr($datosForm["fechaconstitucion"], 6, 2));
                $formulario->armarCampo('p9.pj_cla_doc', retornarNombreTablasSirepMysqliApi($dbx, "06", $datosForm["idtipodocperjur"]));
                $formulario->armarCampo('p9.pj_num_doc', $datosForm["numdocperjur"]);
                $formulario->armarCampo('p9.pj_doc_anio', substr($datosForm["fecdocperjur"], 0, 4));
                $formulario->armarCampo('p9.pj_doc_mes', substr($datosForm["fecdocperjur"], 4, 2));
                $formulario->armarCampo('p9.pj_doc_dia', substr($datosForm["fecdocperjur"], 6, 2));
                $formulario->armarCampo('p9.pj_doc_exp', $datosForm["origendocperjur"]);
                $formulario->armarCampo('p9.rep_incl', 'X');
                $formulario->armarCampo('p9.rep_nom', \funcionesGenerales::utf8_decode($datosForm["representanteslegales"] [$item_repleg] ["nombrerepleg"]));
                switch ($datosForm["representanteslegales"] [$item_repleg] ["idtipoidentificacionrepleg"]) {
                    case "1" :
                        $formulario->armarCampo('p9.rep_cc', 'X');
                        break;
                    case "2" :
                        $formulario->armarCampo('p9.rep_nit', 'X');
                        break;
                    case "3" :
                        $formulario->armarCampo('p9.rep_ce', 'X');
                        break;
                    case "5" :
                        $formulario->armarCampo('p9.rep_pas', 'X');
                        break;
                }
                $formulario->armarCampo('p9.rep_num_ide', $datosForm["representanteslegales"] [$item_repleg] ["identificacionrepleg"]);
                if (trim($aV ["facultades"]) == 'S') {
                    if (($datosForm["organizacion"] == '99')) {
                        $datosForm["facultades"] = str_replace(array(chr(10) . chr(13), "  "), " ", $datosForm["facultades"]);
                        $formulario->armarCampo('p9.facul_detalle', \funcionesGenerales::utf8_decode($datosForm["facultades"]));
                    }
                } else {

                    for ($l = 1; $l <= 5500; $l++) {
                        $cadenaRelleno .= '-';
                    }
                    $formulario->armarCampo('p9.facul_detalle', \funcionesGenerales::utf8_decode($cadenaRelleno));
                }
                if ($idtipoidentificacionrepleg != '7') {
                    $formulario->armarCampo('p9.firma_nom', strtoupper($nombrerepleg));
                    $formulario->armarCampo('p9.firma_ide', $identificacionrepleg);
                    switch ($idtipoidentificacionrepleg) {
                        case "1" :
                            $formulario->armarCampo('p9.firma_cc', 'X');
                            break;
                        case "3" :
                            $formulario->armarCampo('p9.firma_ce', 'X');
                            break;
                        case "5" :
                            $formulario->armarCampo('p9.firma_pas', 'X');
                            break;
                    }
                }
                $formulario->armarCampo('p9.firma_elec', \funcionesGenerales::utf8_decode($txtFirmaElectronica));
            }
        }
    }

    if ($tipotramite != 'actualizacionproponente399') {
        if ($tipoimpresion == 'vacio') {
            $formulario->agregarPagina(9, $tipoFormulario);
        }
    }


    /**
     * ************************************************************************ INICIO HOJA 10 *************************************************************************
     */
    if ($tipotramite != 'actualizacionproponente399') {
        if ($tipoimpresion != 'vacio' and $tipotramite != 'cancelacionproponente' && $tipotramite != 'cambiodomicilioproponente') {
            foreach ($datosForm["exp1510"] as $key => $valor) {
                // BUSCA LOS CODIGOS DE SECUENCIA EN EL ARREGLO DE CONTRATOS A IMPRIMIR
                if (in_array($valor ['secuencia'], $secuencia_imprimir)) {
                    switch ($tipotramite) {
                        case "inscripcionproponente" :
                        case "cambiodomicilioproponente" :
                            $sec = 'exp1510';
                            break;
                        case "renovacionproponente" :
                        case "actualizacionproponente" :
                            $sec = 'exp1510-' . $valor ['secuencia'];
                            break;
                    }
                    $token_inicial_exp = 0;
                    $token_final_exp = 16;
                    if (!isset($datosForm["exp1510"] [$key] ["clasif"])) {
                        $datosForm["exp1510"] [$key] ["clasif"] = array();
                    }
                    $paginas_adicionales_exp = ceil(count($datosForm["exp1510"] [$key] ["clasif"]) / 15);
                    if ($paginas_adicionales_exp == 0) {
                        $paginas_adicionales_exp = 1;
                    }
                    // CONTROLA SI EL CONTRATO (SECUENCIA) CAMBIO O NO
                    if (trim($aV [$sec]) == 'S') {
                        for ($hoja_exp = 1; $hoja_exp <= $paginas_adicionales_exp; $hoja_exp++) {
                            $formulario->agregarPagina(10, $tipoFormulario);
                            $formulario->armarCampo('p10.num_hoja', $paginador++, 1);
                            switch ($tipotramite) {
                                case "inscripcionproponente" :
                                    $formulario->armarCampo('p10.ins', 'X');
                                    break;
                                case "renovacionproponente" :
                                    $formulario->armarCampo('p10.ren', 'X');
                                    break;
                                case "actualizacionproponente" :
                                    $formulario->armarCampo('p10.act', 'X');
                                    break;
                                case "cambiodomicilioproponente" :
                                    $formulario->armarCampo('p10.tras', 'X');
                                    break;
                            }
                            $formulario->armarCampo('p10.nit', substr($datosForm["nit"], 0, - 1));
                            $formulario->armarCampo('p10.dv', substr($datosForm["nit"], - 1, 1));
                            $formulario->armarCampo('p10.num_cons_rep', $datosForm["exp1510"] [$key] ["secuencia"]);
                            switch ($datosForm["exp1510"] [$key] ["celebradopor"]) {
                                case "1" :
                                    $formulario->armarCampo('p10.exp_prop', 'X');
                                    break;
                                case "2" :
                                    $formulario->armarCampo('p10.exp_acc', 'X');
                                    break;
                                case "3" :
                                    $formulario->armarCampo('p10.exp_cons', 'X');
                                    break;
                            }
                            $valor_cont = (trim($datosForm["exp1510"] [$key] ["valor"]) != '') ? $datosForm["exp1510"] [$key] ["valor"] : 0;
                            $porcentaje_part = (trim($datosForm["exp1510"] [$key] ["porcentaje"]) != '') ? $datosForm["exp1510"] [$key] ["porcentaje"] : 0;
                            $formulario->armarCampo('p10.nom_contratista', \funcionesGenerales::utf8_decode($datosForm["exp1510"] [$key] ["nombrecontratista"]));
                            $formulario->armarCampo('p10.nom_contratante', \funcionesGenerales::utf8_decode($datosForm["exp1510"] [$key] ["nombrecontratante"]));
                            if (is_numeric($valor_cont)) {
                                $valor_cont = str_replace(".", ",", $valor_cont);
                                $rgv = explode(",", $valor_cont);
                                $valt = '';
                                $valt = $rgv[0] . ',';
                                if (!isset($rgv[1])) {
                                    $valt .= '00';
                                } else {
                                    if (strlen($rgv[1]) == 1) {
                                        $valt .= $rgv[1] . '0';
                                    } else {
                                        if (strlen($rgv[1]) == 2) {
                                            $valt .= $rgv[1];
                                        } else {
                                            $valt .= substr($rgv[1], 0, 2);
                                        }
                                    }
                                }
                                // $formulario->armarCampo('p10.val_cont', truncateFloatForm($valor_cont, 2,",","."));
                                $formulario->armarCampo('p10.val_cont', $valt);
                            }
                            if (is_numeric($porcentaje_part)) {
                                $formulario->armarCampo('p10.porc_part', \funcionesGenerales::truncarValorNuevoFormulario($porcentaje_part, 2, ",", "."));
                            }
                            // IMPRIME LOS CODIGOS DE CLASIFICACION
                            foreach ($datosForm["exp1510"] [$key] ["clasif"] as $clave => $valor) {

                                if (($clave > $token_inicial_exp) and ( $clave < $token_final_exp)) {
                                    $formulario->armarCampo('p10.cod_seg', substr($valor, 0, 2), $clave - $token_inicial_exp);
                                    $formulario->armarCampo('p10.cod_fam', substr($valor, 2, 2), $clave - $token_inicial_exp);
                                    $formulario->armarCampo('p10.cod_cla', substr($valor, 4, 2), $clave - $token_inicial_exp);
                                } // fin if tokens
                                // IMPRIME TEXTO SI REQUIERE UNA HOJA ADICIONAL
                                if ($clave == $token_final_exp) {
                                    $formulario->armarCampo('p10.continua', 'CONTINUA...', 1);
                                }
                            } // fin foreach exp1510
                            $token_inicial_exp += 15;
                            $token_final_exp += 15;
                            if ($idtipoidentificacionrepleg != '7') {
                                $formulario->armarCampo('p10.firma_nom', strtoupper($nombrerepleg));
                                $formulario->armarCampo('p10.firma_ide', $identificacionrepleg);
                                switch ($idtipoidentificacionrepleg) {
                                    case "1" :
                                        $formulario->armarCampo('p10.firma_cc', 'X');
                                        break;
                                    case "3" :
                                        $formulario->armarCampo('p10.firma_ce', 'X');
                                        break;
                                    case "5" :
                                        $formulario->armarCampo('p10.firma_pas', 'X');
                                        break;
                                }
                            }
                            $formulario->armarCampo('p10.firma_elec', \funcionesGenerales::utf8_decode($txtFirmaElectronica));
                        } // fin paginas_adicionales_exp
                    }
                } // fin if secuencia_imprimir
            } // fin foreach
        } // fin if inicial
    }

    if ($tipotramite != 'actualizacionproponente399') {
        if ($tipoimpresion == 'vacio') {
            $formulario->agregarPagina(10, $tipoFormulario);
        }
    }

    /**
     * ************************************************************************ INICIO HOJA 12 (ANEXO DECRETO 399 *************************************************************************
     */
    if ($tipoimpresion != 'vacio' && $tipotramite != 'actualizacionproponente' && $tipotramite != 'cancelacionproponente' && $tipotramite != 'cambiodomicilioproponente') {
        if (isset($arrLiq ["fechaultimamodificacion"])) {
            $fecha = $arrLiq ["fechaultimamodificacion"];
        } else {
            if (isset($arrLiq ["fecha"])) {
                $fecha = $arrLiq ["fecha"];
            } else {
                $fecha = date("Ymd");
            }
        }
        $inc12 = 'no';
        if ($datosForm["inffin399a_fechacorte"] != '' && $datosForm["inffin399a_pregrabado"] != 'si') {
            $inc12 = 'si';
        } else {
            if ($datosForm["inffin399b_fechacorte"] != '' && $datosForm["inffin399b_pregrabado"] != 'si') {
                $inc12 = 'si';
            }
        }
        if ($inc12 == 'si') {
            $formulario->agregarPagina(12, $tipoFormulario);
            $formulario->armarCampo('p12.cod_camara', $_SESSION ["generales"]["codigoempresa"] . ' - ' . $fecha);

            switch ($tipotramite) {
                case "inscripcionproponente" :
                    $formulario->armarCampo('p12.ins', 'X');
                    break;
                case "renovacionproponente" :
                    $formulario->armarCampo('p12.ren', 'X');
                    break;
                case "actualizacionproponente399" :
                    $formulario->armarCampo('p12.act', 'X');
                    break;
            }
            $formulario->armarCampo('p12.pro', $datosForm["proponente"]);
            if ($datosForm["organizacion"] != '01') {
                $formulario->armarCampo('p12.raz', $datosForm["nombre"]);
                $formulario->armarCampo('p12.sig', $datosForm["sigla"]);
            } else {
                $formulario->armarCampo('p12.ape1', $datosForm["ape1"]);
                $formulario->armarCampo('p12.ape2', $datosForm["ape2"]);
                $formulario->armarCampo('p12.nom1', $datosForm["nom1"]);
                $formulario->armarCampo('p12.nom2', $datosForm["nom2"]);
            }
            $formulario->armarCampo('p12.nit', substr($datosForm["nit"], 0, - 1));
            $formulario->armarCampo('p12.dv', substr($datosForm["nit"], - 1, 1));
            $orden = 0;
            if ($datosForm["inffin399a_fechacorte"] != '' && $datosForm["inffin399a_pregrabado"] != 'si') {
                $orden++;
                $formulario->armarCampo('p12.1.anocorte', substr($datosForm["inffin399a_fechacorte"], 0, 4));
                $formulario->armarCampo('p12.1.mescorte', substr($datosForm["inffin399a_fechacorte"], 4, 2));
                $formulario->armarCampo('p12.1.diacorte', substr($datosForm["inffin399a_fechacorte"], 6, 2));
                $formulario->armarCampo('p12.1.gruponiif', $datosForm["inffin399a_gruponiif"]);
                $formulario->armarCampo('p12.1.actcte', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399a_actcte"]));
                $formulario->armarCampo('p12.1.pascte', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399a_pascte"]));
                if ($datosForm["inffin399a_pascte"] == 0) {
                    $formulario->armarCampo('p12.1.indliq', 'INDEFINIDO');
                } else {
                    $formulario->armarCampo('p12.1.indliq', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399a_indliq"]));
                }
                $formulario->armarCampo('p12.1.pastot', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399a_pastot"]));
                $formulario->armarCampo('p12.1.acttot', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399a_acttot"]));
                $formulario->armarCampo('p12.1.nivend', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399a_nivend"]));
                $formulario->armarCampo('p12.1.utiope', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399a_utiope"]));
                $formulario->armarCampo('p12.1.gasint', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399a_gasint"]));
                if ($datosForm["inffin399a_gasint"] == 0) {
                    $formulario->armarCampo('p12.1.razcob', 'INDEFINIDO');
                } else {
                    $formulario->armarCampo('p12.1.razcob', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399a_razcob"]));
                }
                $formulario->armarCampo('p12.1.utiope1', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399a_utiope"]));
                $formulario->armarCampo('p12.1.patnet', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399a_patnet"]));
                $formulario->armarCampo('p12.1.renpat', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399a_renpat"]));
                $formulario->armarCampo('p12.1.utiope2', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399a_utiope"]));
                $formulario->armarCampo('p12.1.acttot1', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399a_acttot"]));
                $formulario->armarCampo('p12.1.renact', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399a_renact"]));
            }
            if ($datosForm["inffin399b_fechacorte"] != '' && $datosForm["inffin399b_pregrabado"] != 'si') {
                $orden++;
                if ($orden == 1) {
                    $formulario->armarCampo('p12.1.anocorte', substr($datosForm["inffin399b_fechacorte"], 0, 4));
                    $formulario->armarCampo('p12.1.mescorte', substr($datosForm["inffin399b_fechacorte"], 4, 2));
                    $formulario->armarCampo('p12.1.diacorte', substr($datosForm["inffin399b_fechacorte"], 6, 2));
                    $formulario->armarCampo('p12.1.gruponiif', $datosForm["inffin399b_gruponiif"]);
                    $formulario->armarCampo('p12.1.actcte', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_actcte"]));
                    $formulario->armarCampo('p12.1.pascte', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_pascte"]));
                    if ($datosForm["inffin399b_pascte"] == 0) {
                        $formulario->armarCampo('p12.1.indliq', 'INDEFINIDO');
                    } else {
                        $formulario->armarCampo('p12.1.indliq', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_indliq"]));
                    }
                    $formulario->armarCampo('p12.1.pastot', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_pastot"]));
                    $formulario->armarCampo('p12.1.acttot', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_acttot"]));
                    $formulario->armarCampo('p12.1.nivend', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_nivend"]));
                    $formulario->armarCampo('p12.1.utiope', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_utiope"]));
                    $formulario->armarCampo('p12.1.gasint', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_gasint"]));
                    if ($datosForm["inffin399b_gasint"] == 0) {
                        $formulario->armarCampo('p12.1.razcob', 'INDEFINIDO');
                    } else {
                        $formulario->armarCampo('p12.1.razcob', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_razcob"]));
                    }
                    $formulario->armarCampo('p12.1.utiope1', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_utiope"]));
                    $formulario->armarCampo('p12.1.patnet', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_patnet"]));
                    $formulario->armarCampo('p12.1.renpat', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_renpat"]));
                    $formulario->armarCampo('p12.1.utiope2', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_utiope"]));
                    $formulario->armarCampo('p12.1.acttot1', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_acttot"]));
                    $formulario->armarCampo('p12.1.renact', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_renact"]));
                }
                if ($orden == 2) {
                    $formulario->armarCampo('p12.2.anocorte', substr($datosForm["inffin399b_fechacorte"], 0, 4));
                    $formulario->armarCampo('p12.2.mescorte', substr($datosForm["inffin399b_fechacorte"], 4, 2));
                    $formulario->armarCampo('p12.2.diacorte', substr($datosForm["inffin399b_fechacorte"], 6, 2));
                    $formulario->armarCampo('p12.2.gruponiif', $datosForm["inffin399b_gruponiif"]);
                    $formulario->armarCampo('p12.2.actcte', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_actcte"]));
                    $formulario->armarCampo('p12.2.pascte', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_pascte"]));
                    if ($datosForm["inffin399b_pascte"] == 0) {
                        $formulario->armarCampo('p12.2.indliq', 'INDEFINIDO');
                    } else {
                        $formulario->armarCampo('p12.2.indliq', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_indliq"]));
                    }
                    $formulario->armarCampo('p12.2.pastot', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_pastot"]));
                    $formulario->armarCampo('p12.2.acttot', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_acttot"]));
                    $formulario->armarCampo('p12.2.nivend', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_nivend"]));
                    $formulario->armarCampo('p12.2.utiope', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_utiope"]));
                    $formulario->armarCampo('p12.2.gasint', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_gasint"]));
                    if ($datosForm["inffin399b_gasint"] == 0) {
                        $formulario->armarCampo('p12.2.razcob', 'INDEFINIDO');
                    } else {
                        $formulario->armarCampo('p12.2.razcob', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_razcob"]));
                    }
                    $formulario->armarCampo('p12.2.utiope1', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_utiope"]));
                    $formulario->armarCampo('p12.2.patnet', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_patnet"]));
                    $formulario->armarCampo('p12.2.renpat', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_renpat"]));
                    $formulario->armarCampo('p12.2.utiope2', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_utiope"]));
                    $formulario->armarCampo('p12.2.acttot1', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_acttot"]));
                    $formulario->armarCampo('p12.2.renact', \funcionesGenerales::truncarValorNuevoFormulario($datosForm["inffin399b_renact"]));
                }
            }
            if ($idtipoidentificacionrepleg != '7') {
                $formulario->armarCampo('p12.firma_nom', strtoupper($nombrerepleg));
                $formulario->armarCampo('p12.firma_ide', $identificacionrepleg);
                switch ($idtipoidentificacionrepleg) {
                    case "1" :
                        $formulario->armarCampo('p12.firma_cc', 'X');
                        break;
                    case "3" :
                        $formulario->armarCampo('p12.firma_ce', 'X');
                        break;
                    case "5" :
                        $formulario->armarCampo('p12.firma_pas', 'X');
                        break;
                }
            }
            $formulario->armarCampo('p12.firma_elec', \funcionesGenerales::utf8_decode($txtFirmaElectronica));
        }
    }

    if ($tipoimpresion != 'vacio') {

        if ($tipoimpresion == 'final') {
            if (!defined('BLOQUEAR_FORMULARIO_PROPONENTES_AL_IMPRIMIR')) {
                define('BLOQUEAR_FORMULARIO_PROPONENTES_AL_IMPRIMIR', 'N');
            }
            if (BLOQUEAR_FORMULARIO_PROPONENTES_AL_IMPRIMIR == 'S') {
                if (retornarRegistrosMysqliApi($dbx, 'mreg_liquidacion', "idliquidacion=" . $numliq, "idestado") < '04') {
                    \funcionesRegistrales::actualizarMregLiquidacionEstado($dbx, $numliq, '04');
                }
            }
        }
    }

    // exit(0);
    // ********************************************************************************************************
    if ($tipoimpresion != 'vacio') {
        $name = PATH_ABSOLUTO_SITIO . "/tmp/" . session_id() . "-Formulario2017-Proponentes-" . date("Ymd") . '-' . date("His") . ".pdf";
        $name1 = session_id() . "-Formulario2017-Proponentes-" . date("Ymd") . '-' . date("His") . '.pdf';
        $formulario->Output($name, "F");
        return $name1;
    } else {
        $name = PATH_ABSOLUTO_SITIO . "/tmp/" . session_id() . "-Formulario2017-Proponentes-Anexo-" . session_id() . '-' . date("Ymd") . '-' . date("His") . ".pdf";
        $name1 = session_id() . "-Formulario2017-Proponentes-Anexo-" . session_id() . '-' . date("Ymd") . '-' . date("His") . '.pdf';
        $formulario->Output($name, "F");
        return $name1;
    }

    // ********************************************************************************************************
}

function truncarValorNuevoFormularioSignoMenos($valor) {
    // return truncarValorNuevo($valor);        
    $signo = '-';
    $valor = str_replace(".", ",", $valor);
    $valor = str_replace("-", "", $valor);
    $valor = str_replace(".", ",", $valor);
    $rgv = explode(",", $valor);
    $valt = '';
    $valt = number_format($rgv[0], 0, "", ".") . ',';
    // $valt = $rgv[0] . ',';
    if (!isset($rgv[1])) {
        $valt .= '00';
    } else {
        if (strlen($rgv[1]) == 1) {
            $valt .= $rgv[1] . '0';
        } else {
            if (strlen($rgv[1]) == 2) {
                $valt .= $rgv[1];
            } else {
                $valt .= substr($rgv[1], 0, 2);
            }
        }
    }
    return $signo . $valt;
}

?>