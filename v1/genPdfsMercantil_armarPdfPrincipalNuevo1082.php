<?php

/**
 * Función que realiza el armado de los formularios de registro mercantil 
 * a partir de la información del expediente - Incluye campos según Circular 
 * única
 * 
 * @param type $numrec
 * @param type $numliq
 * @param type $tipoimpresion
 * @param type $prediligenciado
 * @param type $txtFirmaElectronica
 * @param type $txtFirmaManuscrita
 * @param type $ideFirmaManuscrita
 * @param type $nomFirmaManuscrita
 * @param type $cambidom
 * @param type $anoimprimir
 * @return boolean|string
 */
function armarPdfPrincipalNuevo1082Sii($dbx, $numrec = '', $numliq = 0, $tipoimpresion = '', $prediligenciado = 'no', $txtFirmaElectronica = '', $txtFirmaManuscrita = '', $ideFirmaManuscrita = '', $nomFirmaManuscrita = '', $cambidom = 'no', $anoimprimir = '') {
    require_once ('pdfFormularioRues-2017.php'); //VERSION 23 DE MAYO DE 2017
    $formulario = new formularioRues2017();
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
                if ($anoimprimir != '') {
                    $formulario->armarCampo('p1.sec1_col1_ano_ren', $anoimprimir);
                } else {
                    $formulario->armarCampo('p1.sec1_col1_ano_ren', $datosForm["anodatos"]);
                }
                $formulario->armarCampo('p1.sec1_col1_num_mat', $datosForm["matricula"]);
            } else {

                if (strtoupper($_SESSION["tramite"]["reliquidacion"]) == 'SI') {
                    $formulario->armarCampo('p1.sec1_col2_aju_inf', 'X');
                } else {
                    $formulario->armarCampo('p1.sec1_col2_ren', 'X');
                }
                if ($anoimprimir != '') {
                    $formulario->armarCampo('p1.sec1_col2_ano_ren', $anoimprimir);
                } else {
                    $formulario->armarCampo('p1.sec1_col2_ano_ren', $datosForm["anodatos"]);
                }
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
            if (substr($datosForm["matricula"], 0, 1) != 'S' && $datosForm["matricula"] != 'NUEVAESA') {
                if ($_SESSION["tramite"]["tipomatricula"] == 'cambidom' || $cambidom == 'si') {
                    $formulario->armarCampo('p1.sec1_col1_tra_dom', 'X');
                } else {
                    $formulario->armarCampo('p1.sec1_col1_mat_ins', 'X');
                }
                if (substr($datosForm["matricula"], 0, 5) != 'NUEVA') {
                    $formulario->armarCampo('p1.sec1_col1_num_mat', $datosForm["matricula"]);
                }
            } else {
                if ($_SESSION["tramite"]["tipomatricula"] == 'cambidom' || $cambidom == 'si') {
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
                    $formulario->armarCampo('p1.sec1_col1_cod_org_gen', homologacionOrganizacionGeneralRUES($datosForm["organizacion"], $datosForm["claseespesadl"], $datosForm["ciius"]));
                    $formulario->armarCampo('p1.sec1_col1_cod_org_esp', homologacionOrganizacionEspecificaRUES($datosForm["organizacion"], $datosForm["clasegenesadl"], $datosForm["claseespesadl"], $datosForm["claseeconsoli"]));
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

            $formulario->armarCampo('p1.sec2_lug_exp', retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $datosForm["idmunidoc"] . "'","ciudad"));

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

        $formulario->armarCampo('p1.sec2_pais', \funcionesSii2::retornarNombrePaisAbreviado($dbx,$datosForm["paisexpdoc"]));

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


        $formulario->armarCampo('p1.sec3_dom_muni', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $datosForm["muncom"] . "'","ciudad")));
        $formulario->armarCampo('p1.sec3_dom_muni_num', substr($datosForm["muncom"], 2, 5));
        $formulario->armarCampo('p1.sec3_dom_dep', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $datosForm["muncom"] . "'","departamento")));
        $formulario->armarCampo('p1.sec3_dom_dep_num', substr($datosForm["muncom"], 0, 2));
        $formulario->armarCampo('p1.sec3_dom_pais', \funcionesSii2::retornarNombrePaisAbreviado($dbx,$datosForm["paicom"]));

        if (ltrim($datosForm["barriocom"], "0") != '') {
            $formulario->armarCampo('p1.sec3_dom_lbvc', retornarRegistroMysqli2($dbx,'mreg_barriosmuni',"idmunicipio='" . $datosForm["muncom"] . "' and idbarrio='" . $datosForm["barriocom"] . "'","nombre"));
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

        $formulario->armarCampo('p1.sec3_not_muni', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $datosForm["munnot"] . "'","ciudad")));
        $formulario->armarCampo('p1.sec3_not_muni_num', substr($datosForm["munnot"], 2, 5));
        $formulario->armarCampo('p1.sec3_not_dep', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $datosForm["muncom"] . "'","departamento")));
        $formulario->armarCampo('p1.sec3_not_dep_num', substr($datosForm["munnot"], 0, 2));
        $formulario->armarCampo('p1.sec3_not_pais', \funcionesSii2::retornarNombrePaisAbreviado($dbx,$datosForm["painot"]));

        if (ltrim($datosForm["barrionot"], "0") != '') {

            $formulario->armarCampo('p1.sec3_not_lbvc', retornarRegistroMysqli2($dbx,'mreg_barriosmuni',"idmunicipio='" . $datosForm["munnot"] . "' and idbarrio='" . $datosForm["barrionot"] . "'","nombre"));
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
            if ($anoimprimir == '') {
                $formulario->armarCampo('p2.sec5_act_cor', truncarValorNuevoFormularioMercantilSii2($datosForm["actcte"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_act_no_cor', truncarValorNuevoFormularioMercantilSii2($datosForm["actnocte"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_act_tot', truncarValorNuevoFormularioMercantilSii2($datosForm["acttot"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_pas_cor', truncarValorNuevoFormularioMercantilSii2($datosForm["pascte"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_pas_no_cor', truncarValorNuevoFormularioMercantilSii2($datosForm["paslar"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_pas_tot', truncarValorNuevoFormularioMercantilSii2($datosForm["pastot"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_pas_net', truncarValorNuevoFormularioMercantilSii2($datosForm["pattot"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_pas_pat', truncarValorNuevoFormularioMercantilSii2($datosForm["paspat"], $decimalesVisibles));
                if ($datosForm["organizacion"] == '12' || $datosForm["organizacion"] == '14') {
                    if ($datosForm["categoria"] == '1') {
                        $formulario->armarCampo('p2.sec5_bal_soc', truncarValorNuevoFormularioMercantilSii2($datosForm["balsoc"], $decimalesVisibles));
                    }
                }
                $formulario->armarCampo('p2.sec5_ing_act_ord', truncarValorNuevoFormularioMercantilSii2($datosForm["ingope"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_otr_ing', truncarValorNuevoFormularioMercantilSii2($datosForm["ingnoope"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_cos_ven', truncarValorNuevoFormularioMercantilSii2($datosForm["cosven"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_gas_ope', truncarValorNuevoFormularioMercantilSii2($datosForm["gtoven"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_otr_gas', truncarValorNuevoFormularioMercantilSii2($datosForm["gtoadm"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_gas_imp', truncarValorNuevoFormularioMercantilSii2($datosForm["gasimp"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_uti_ope', truncarValorNuevoFormularioMercantilSii2($datosForm["utiope"], $decimalesVisibles));
                $formulario->armarCampo('p2.sec5_res_per', truncarValorNuevoFormularioMercantilSii2($datosForm["utinet"], $decimalesVisibles));
            } else {
                $ifx = array();
                foreach ($datosForm["financiera"] as $if) {
                    if ($if["anodatos"] == $anoimprimir) {
                        $ifx = $if;
                    }
                }
                if (!empty($ifx)) {
                    $formulario->armarCampo('p2.sec5_act_cor', truncarValorNuevoFormularioMercantilSii2($ifx["actcte"], $decimalesVisibles));
                    $formulario->armarCampo('p2.sec5_act_no_cor', truncarValorNuevoFormularioMercantilSii2($ifx["actnocte"], $decimalesVisibles));
                    $formulario->armarCampo('p2.sec5_act_tot', truncarValorNuevoFormularioMercantilSii2($ifx["acttot"], $decimalesVisibles));
                    $formulario->armarCampo('p2.sec5_pas_cor', truncarValorNuevoFormularioMercantilSii2($ifx["pascte"], $decimalesVisibles));
                    $formulario->armarCampo('p2.sec5_pas_no_cor', truncarValorNuevoFormularioMercantilSii2($ifx["paslar"], $decimalesVisibles));
                    $formulario->armarCampo('p2.sec5_pas_tot', truncarValorNuevoFormularioMercantilSii2($ifx["pastot"], $decimalesVisibles));
                    $formulario->armarCampo('p2.sec5_pas_net', truncarValorNuevoFormularioMercantilSii2($ifx["pattot"], $decimalesVisibles));
                    $formulario->armarCampo('p2.sec5_pas_pat', truncarValorNuevoFormularioMercantilSii2($ifx["paspat"], $decimalesVisibles));
                    if ($datosForm["organizacion"] == '12' || $datosForm["organizacion"] == '14') {
                        if ($datosForm["categoria"] == '1') {
                            $formulario->armarCampo('p2.sec5_bal_soc', truncarValorNuevoFormularioMercantilSii2($ifx["balsoc"], $decimalesVisibles));
                        }
                    }
                    $formulario->armarCampo('p2.sec5_ing_act_ord', truncarValorNuevoFormularioMercantilSii2($ifx["ingope"], $decimalesVisibles));
                    $formulario->armarCampo('p2.sec5_otr_ing', truncarValorNuevoFormularioMercantilSii2($ifx["ingnoope"], $decimalesVisibles));
                    $formulario->armarCampo('p2.sec5_cos_ven', truncarValorNuevoFormularioMercantilSii2($ifx["cosven"], $decimalesVisibles));
                    $formulario->armarCampo('p2.sec5_gas_ope', truncarValorNuevoFormularioMercantilSii2($ifx["gtoven"], $decimalesVisibles));
                    $formulario->armarCampo('p2.sec5_otr_gas', truncarValorNuevoFormularioMercantilSii2($ifx["gtoadm"], $decimalesVisibles));
                    $formulario->armarCampo('p2.sec5_gas_imp', truncarValorNuevoFormularioMercantilSii2($ifx["gasimp"], $decimalesVisibles));
                    $formulario->armarCampo('p2.sec5_uti_ope', truncarValorNuevoFormularioMercantilSii2($ifx["utiope"], $decimalesVisibles));
                    $formulario->armarCampo('p2.sec5_res_per', truncarValorNuevoFormularioMercantilSii2($ifx["utinet"], $decimalesVisibles));
                }
            }
        }

        /*
         * 
         * En el formulario define un campo check - 
         * Pero por instrucciones es posible que sea un código. 
         * JINT: $datosForm["gruponiif"] = del 0 al 7, donde 0 es no reportado
         *
         */

        if (trim($datosForm["gruponiif"]) != '') {
            $formulario->armarCampo('p2.sec5_grupo_niif',  \funcionesSii2::retornarGrupoNiifFormulario($dbx,$datosForm["gruponiif"]));
            $formulario->armarCampo('p2.sec5_grupo_niif_des', \funcionesSii2::retornarGrupoNiifFormularioDescripcion($dbx,$datosForm["gruponiif"]));
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

            /*
              $tiene_eas = 'N';

              $cntEstablecimientos = count($datosForm["establecimientos"]);
              $cntSucursalesAgencias = count($datosForm["sucursalesagencias"]);

              if ($cntEstablecimientos > 0) {
              $tiene_eas = 'S';
              }

              if ($cntSucursalesAgencias > 0) {
              $tiene_eas = 'S';
              }

              if ($tiene_eas == 'S') {
              $formulario->armarCampo('p2.sec8_eas_si', 'X');


              $formulario->armarCampo('p2.sec8_eas_num', $cntEstablecimientos + $cntSucursalesAgencias);
              } else {
              $formulario->armarCampo('p2.sec8_eas_no', 'X');
              }
             */

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
                if (isset($datosForm["nombrefirmante"])) {
                    $nombre_firmante = $datosForm["nombrefirmante"];
                    $numid_firmante = $datosForm["numidfirmante"];
                    $idclase_firmante = $datosForm["idclasefirmante"];
                } else {
                    $nombre_firmante = $datosForm["ape1"] . ' ' . $datosForm["ape2"] . ' ' . $datosForm["nom1"] . ' ' . $datosForm["nom2"];
                    $numid_firmante = $datosForm["identificacion"];
                    $idclase_firmante = $datosForm["tipoidentificacion"];
                }
                $formulario->armarCampo('p2.firma_nom', utf8_decode($nombre_firmante));
                $formulario->armarCampo('p2.firma_ide', $numid_firmante);

                switch ($idclase_firmante) {
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
                if (isset($datosForm["nombrefirmante"])) {
                    $nombre_firmante = $datosForm["nombrefirmante"];
                    $numid_firmante = $datosForm["numidfirmante"];
                    $idclase_firmante = $datosForm["idclasefirmante"];
                    $formulario->armarCampo('p2.firma_nom', utf8_decode($nombre_firmante));
                    $formulario->armarCampo('p2.firma_ide', $numid_firmante);
                    switch ($idclase_firmante) {
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
        }

        if (trim($txtFirmaElectronica) != '') {
            $formulario->armarCampo('p2.firma_elec', utf8_decode($txtFirmaElectronica));
        }
        if (trim($txtFirmaManuscrita) != '') {
            $formulario->armarCampoImagen('p2.firma_manuscrita', $txtFirmaManuscrita);
        }
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
            //HOMOLOGACIONES
            $formulario->armarCampo('p11.sec2_esadl_cod', homologacionOrganizacionEsadlRUES($dbx,$datosForm["claseespesadl"]));

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

            //
            $nombre_firmante = $datosForm["nombrefirmante"];
            $numid_firmante = $datosForm["numidfirmante"];
            $idclase_firmante = $datosForm["idclasefirmante"];
            $formulario->armarCampo('p11.firma_nom', utf8_decode($nombre_firmante));
            $formulario->armarCampo('p11.firma_ide', $numid_firmante);
            switch ($idclase_firmante) {
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

            //
            if (trim($txtFirmaElectronica) != '') {
                $formulario->armarCampo('p11.firma_elec', utf8_decode($txtFirmaElectronica));
            }

            //
            if (trim($txtFirmaManuscrita) != '') {
                $formulario->armarCampoImagen('p11.firma_manuscrita', $txtFirmaManuscrita);
            }
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
