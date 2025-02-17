<?php

/**
 * Función que genera el pdf de la caratula unica y el anexo de registro mercantil cuando se trata de
 * personas naturales o personas juridicas principales
 * Genera dos hojas por cada matricula
 * Recibe los datos a traves de #_SESSION["formulario]["datos"]
 * Genera el archivo pdf en pdf_files/formularios/rmer-MMMMMMM-YYYYMMDD.pdf
 *
 * @param   	string	$numrecx	Numero de recuperacion o tramite, si lo hay
 * @param   	string	$numliq		Numero de liquidacion, si lo hay
 * @param		string	$tiposalida	D.- Display (defecto), A.- Archivo, I.- Interactivo 
 * @param		string	$tipodatos	PR.- Prediligencanciado, UR.- Ultima renovacion
 * @return 		string	$name		Nombre del archivo que contiene el formulario
 */
function armarPdfPrincipalNuevoAnosAnteriores1510Sii($dbx,$numrec = '', $numliq = 0, $tipoimpresion = '', $txtFirmaElectronica = '') {

    try {

        if (!defined(ACTIVAR_CIRCULAR_002_2016)) {
            define('ACTIVAR_CIRCULAR_002_2016', '');
        }

        if (!isset($formularioPersonas)) {
            if (ACTIVAR_CIRCULAR_002_2016 == 'SI1') {
                require_once ('pdfFormularioRues-2016.php');
                $formularioPersonas = new formularioRues2016();
            } else {
                require_once ('pdfFormularioRues-2014.php');
                $formularioPersonas = new formularioRues2014();
            }
        }


        $formularioPersonas->setNumeroRecuperacion($numrec);
        $formularioPersonas->setNumeroLiquidacion($numliq);

        //Ajuste 30 abril 2015 - adicion linea 27
        $formularioPersonas->setFechaImpresion(date('Y/m/d H:i:s'));

        /*
          echo '<pre>';
          var_dump ( $_SESSION ["formulario"] ["datos"] );
          echo '</pre>';
         */
        $item = 0;
        
        foreach ($_SESSION["formulario"]["datos"]["f"] as $iAno => $fin) {

            if ($iAno != $_SESSION["formulario"]["datos"]["anodatos"]) {
                $inclu = 'si';
                if ($_SESSION["formulario"]["datos"]["organizacion"] == '12' || $_SESSION["formulario"]["datos"]["organizacion"] == '14') {
                    if ($_SESSION["formulario"]["datos"]["categoria"] == '1') {
                        if ($iAno < '2013') {
                            $inclu = 'no';                            
                           // fwrite($f, date("Y:m:d") . '-' . date("His") . $iAno. ' --> '. $inclu. chr(13) . chr(10));
                        } else {
                            if ($iAno <= $_SESSION["formulario"]["datos"]["ultanoren"]) {
                                $inclu = 'si';
                            //    fwrite($f, date("Y:m:d") . '-' . date("His") . $iAno. ' *--> '. $inclu. chr(13) . chr(10));
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
                            $formularioPersonas->agregarPagina(4, 0); // primera pagina como borrador
                        } else {
                            $formularioPersonas->agregarPagina(4, 1); // primera pagina sin borrador
                        }
                    }


                    // fecha de diligenciamiento
                    if (isset($_SESSION ["tramite"] ["fechaultimamodificacion"]) && (!empty($_SESSION ["tramite"] ["fechaultimamodificacion"]))) {
                        $fec = $_SESSION ["tramite"] ["fechaultimamodificacion"];
                    } else {
                        if (isset($_SESSION ["tramite"] ["fecha"]) && (!empty($_SESSION ["tramite"] ["fecha"]))) {
                            $fec = $_SESSION ["tramite"] ["fecha"];
                        } else {
                            $fec = date("Ymd");
                        }
                    }


                    $formularioPersonas->armarCampo('p4.cod_camara', $_SESSION ["generales"] ["codigoempresa"]);
                    $formularioPersonas->armarCampo('p4.anio', substr($fec, 0, 4));
                    $formularioPersonas->armarCampo('p4.mes', substr($fec, 4, 2));
                    $formularioPersonas->armarCampo('p4.dia', substr($fec, 6, 2));



                    $texto = ltrim($_SESSION ["formulario"] ["datos"] ["nit"], '0');
                    $textonit = '';
                    $textodv = '';
                    if (trim($texto) != '') {
                        $textonit = substr($texto, 0, strlen($texto) - 1);
                    }
                    if (trim($texto) != '') {
                        $textodv = substr($texto, strlen($texto) - 1, 1);
                    }
                    if ($_SESSION ["formulario"] ["datos"] ["organizacion"] != '01') {
                        $formularioPersonas->armarCampo('p4.num_nit', $textonit);
                        $formularioPersonas->armarCampo('p4.dv', $textodv);
                    } else {
                        $formularioPersonas->armarCampo('p4.nit', $textonit);
                        $formularioPersonas->armarCampo('p4.num_nit', $textonit);
                        $formularioPersonas->armarCampo('p4.dv', $textodv);
                    }
                    $formularioPersonas->armarCampo('p4.num_mat', $_SESSION ["formulario"] ["datos"] ["matricula"]);
                    if ($_SESSION ["formulario"] ["datos"] ["organizacion"] != '01') {
                        $formularioPersonas->armarCampo('p4.raz_soc', utf8_decode($_SESSION ["formulario"] ["datos"] ["nombre"]));
                    } else {
                        if (trim($_SESSION ["formulario"] ["datos"] ["ape1"]) == '') {
                            $formularioPersonas->armarCampo('p4.raz_soc', utf8_decode($_SESSION ["formulario"] ["datos"] ["nombre"]));
                        } else {
                            $formularioPersonas->armarCampo('p4.ape1', utf8_decode($_SESSION ["formulario"] ["datos"] ["ape1"]));
                            $formularioPersonas->armarCampo('p4.ape2', utf8_decode($_SESSION ["formulario"] ["datos"] ["ape2"]));
                            $formularioPersonas->armarCampo('p4.nom', utf8_decode($_SESSION ["formulario"] ["datos"] ["nom1"] . ' ' . $_SESSION ["formulario"] ["datos"] ["nom2"]));
                        }
                    }

                    if (ACTIVAR_CIRCULAR_002_2016 == 'SI1') {
                        switch ($item) {

                            case '1' :
                                $formularioPersonas->armarCampo('p4.f1_anio_ren', $fin ["anodatos"], $item);
                                $formularioPersonas->armarCampo('p4.f1_actcte', $fin ["actcte"], $item);
                                $formularioPersonas->armarCampo('p4.f1_actnocte', $fin ["actnocte"], $item);
                                $formularioPersonas->armarCampo('p4.f1_acttot', $fin ["acttot"], $item);
                                $formularioPersonas->armarCampo('p4.f1_pascte', $fin ["pascte"], $item);
                                $formularioPersonas->armarCampo('p4.f1_paslar', $fin ["paslar"], $item);
                                $formularioPersonas->armarCampo('p4.f1_pastot', $fin ["pastot"], $item);
                                $formularioPersonas->armarCampo('p4.f1_pattot', $fin ["pattot"], $item);
                                $formularioPersonas->armarCampo('p4.f1_paspat', $fin ["paspat"], $item);

                                if ($_SESSION["formulario"]["datos"]["organizacion"] == '12' || $_SESSION["formulario"]["datos"]["organizacion"] == '14') {
                                    if ($_SESSION["formulario"]["datos"]["categoria"] == '1') {
                                        $formularioPersonas->armarCampo('p4.f1_balsoc', $fin ["balsoc"], $item);
                                    }
                                }

                                $formularioPersonas->armarCampo('p4.f1_ingope', $fin ["ingope"], $item);
                                $formularioPersonas->armarCampo('p4.f1_ingnoope', $fin ["ingnoope"], $item);
                                $formularioPersonas->armarCampo('p4.f1_cosven', $fin ["cosven"], $item);
                                $formularioPersonas->armarCampo('p4.f1_gasope', $fin ["gtoven"], $item);
                                $formularioPersonas->armarCampo('p4.f1_gasnoope', $fin ["gtoadm"], $item);
                                $formularioPersonas->armarCampo('p4.f1_gasimp', $fin ["gasimp"], $item);
                                $formularioPersonas->armarCampo('p4.f1_utiope', $fin ["utiope"], $item);
                                $formularioPersonas->armarCampo('p4.f1_utinet', $fin ["utinet"], $item);
                                break;
                            case '2' :
                                $formularioPersonas->armarCampo('p4.f2_anio_ren', $fin ["anodatos"], $item);
                                $formularioPersonas->armarCampo('p4.f2_actcte', $fin ["actcte"], $item);
                                $formularioPersonas->armarCampo('p4.f2_actnocte', $fin ["actnocte"], $item);
                                $formularioPersonas->armarCampo('p4.f2_acttot', $fin ["acttot"], $item);
                                $formularioPersonas->armarCampo('p4.f2_pascte', $fin ["pascte"], $item);
                                $formularioPersonas->armarCampo('p4.f2_paslar', $fin ["paslar"], $item);
                                $formularioPersonas->armarCampo('p4.f2_pastot', $fin ["pastot"], $item);
                                $formularioPersonas->armarCampo('p4.f2_pattot', $fin ["pattot"], $item);
                                $formularioPersonas->armarCampo('p4.f2_paspat', $fin ["paspat"], $item);
                                if ($_SESSION["formulario"]["datos"]["organizacion"] == '12' || $_SESSION["formulario"]["datos"]["organizacion"] == '14') {
                                    if ($_SESSION["formulario"]["datos"]["categoria"] == '1') {
                                        $formularioPersonas->armarCampo('p4.f2_balsoc', $fin ["balsoc"], $item);
                                    }
                                }
                                $formularioPersonas->armarCampo('p4.f2_ingope', $fin ["ingope"], $item);
                                $formularioPersonas->armarCampo('p4.f2_ingnoope', $fin ["ingnoope"], $item);
                                $formularioPersonas->armarCampo('p4.f2_cosven', $fin ["cosven"], $item);
                                $formularioPersonas->armarCampo('p4.f2_gasope', $fin ["gtoven"], $item);
                                $formularioPersonas->armarCampo('p4.f2_gasnoope', $fin ["gtoadm"], $item);
                                $formularioPersonas->armarCampo('p4.f2_gasimp', $fin ["gasimp"], $item);
                                $formularioPersonas->armarCampo('p4.f2_utiope', $fin ["utiope"], $item);
                                $formularioPersonas->armarCampo('p4.f2_utinet', $fin ["utinet"], $item);
                                break;

                            case '3' :
                                $formularioPersonas->armarCampo('p4.f3_anio_ren', $fin ["anodatos"], $item);
                                $formularioPersonas->armarCampo('p4.f3_actcte', $fin ["actcte"], $item);
                                $formularioPersonas->armarCampo('p4.f3_actnocte', $fin ["actnocte"], $item);
                                $formularioPersonas->armarCampo('p4.f3_acttot', $fin ["acttot"], $item);
                                $formularioPersonas->armarCampo('p4.f3_pascte', $fin ["pascte"], $item);
                                $formularioPersonas->armarCampo('p4.f3_paslar', $fin ["paslar"], $item);
                                $formularioPersonas->armarCampo('p4.f3_pastot', $fin ["pastot"], $item);
                                $formularioPersonas->armarCampo('p4.f3_pattot', $fin ["pattot"], $item);
                                $formularioPersonas->armarCampo('p4.f3_paspat', $fin ["paspat"], $item);
                                if ($_SESSION["formulario"]["datos"]["organizacion"] == '12' || $_SESSION["formulario"]["datos"]["organizacion"] == '14') {
                                    if ($_SESSION["formulario"]["datos"]["categoria"] == '1') {
                                        $formularioPersonas->armarCampo('p4.f3_balsoc', $fin ["balsoc"], $item);
                                    }
                                }
                                $formularioPersonas->armarCampo('p4.f3_ingope', $fin ["ingope"], $item);
                                $formularioPersonas->armarCampo('p4.f3_ingnoope', $fin ["ingnoope"], $item);
                                $formularioPersonas->armarCampo('p4.f3_cosven', $fin ["cosven"], $item);
                                $formularioPersonas->armarCampo('p4.f3_gasope', $fin ["gtoven"], $item);
                                $formularioPersonas->armarCampo('p4.f3_gasnoope', $fin ["gtoadm"], $item);
                                $formularioPersonas->armarCampo('p4.f3_gasimp', $fin ["gasimp"], $item);
                                $formularioPersonas->armarCampo('p4.f3_utiope', $fin ["utiope"], $item);
                                $formularioPersonas->armarCampo('p4.f3_utinet', $fin ["utinet"], $item);
                                break;

                            case '4' :
                                $formularioPersonas->armarCampo('p4.f4_anio_ren', $fin ["anodatos"], $item);
                                $formularioPersonas->armarCampo('p4.f4_actcte', $fin ["actcte"], $item);
                                $formularioPersonas->armarCampo('p4.f4_actnocte', $fin ["actnocte"], $item);
                                $formularioPersonas->armarCampo('p4.f4_acttot', $fin ["acttot"], $item);
                                $formularioPersonas->armarCampo('p4.f4_pascte', $fin ["pascte"], $item);
                                $formularioPersonas->armarCampo('p4.f4_paslar', $fin ["paslar"], $item);
                                $formularioPersonas->armarCampo('p4.f4_pastot', $fin ["pastot"], $item);
                                $formularioPersonas->armarCampo('p4.f4_pattot', $fin ["pattot"], $item);
                                $formularioPersonas->armarCampo('p4.f4_paspat', $fin ["paspat"], $item);
                                if ($_SESSION["formulario"]["datos"]["organizacion"] == '12' || $_SESSION["formulario"]["datos"]["organizacion"] == '14') {
                                    if ($_SESSION["formulario"]["datos"]["categoria"] == '1') {
                                        $formularioPersonas->armarCampo('p4.f4_balsoc', $fin ["balsoc"], $item);
                                    }
                                }
                                $formularioPersonas->armarCampo('p4.f4_ingope', $fin ["ingope"], $item);
                                $formularioPersonas->armarCampo('p4.f4_ingnoope', $fin ["ingnoope"], $item);
                                $formularioPersonas->armarCampo('p4.f4_cosven', $fin ["cosven"], $item);
                                $formularioPersonas->armarCampo('p4.f4_gasope', $fin ["gtoven"], $item);
                                $formularioPersonas->armarCampo('p4.f4_gasnoope', $fin ["gtoadm"], $item);
                                $formularioPersonas->armarCampo('p4.f4_gasimp', $fin ["gasimp"], $item);
                                $formularioPersonas->armarCampo('p4.f4_utiope', $fin ["utiope"], $item);
                                $formularioPersonas->armarCampo('p4.f4_utinet', $fin ["utinet"], $item);

                                break;
                        }
                    } else {


                        switch ($item) {
                            case '1' :
                                $formularioPersonas->armarCampo('p4.f1_anio_ren', $fin ["anodatos"], $item);
                                $formularioPersonas->armarCampo('p4.f1_act_cor', $fin ["actcte"], $item);
                                $formularioPersonas->armarCampo('p4.f1_act_fn', $fin ["fijnet"], $item);
                                $formularioPersonas->armarCampo('p4.f1_act_otr', $fin ["actotr"], $item);
                                $formularioPersonas->armarCampo('p4.f1_act_val', $fin ["actval"], $item);
                                $formularioPersonas->armarCampo('p4.f1_act_tot', $fin ["acttot"], $item);
                                $formularioPersonas->armarCampo('p4.f1_pas_cor', $fin ["pascte"], $item);
                                $formularioPersonas->armarCampo('p4.f1_pas_lp', $fin ["paslar"], $item);
                                $formularioPersonas->armarCampo('p4.f1_pas_tot', $fin ["pastot"], $item);
                                $formularioPersonas->armarCampo('p4.f1_pat_tot', $fin ["pattot"], $item);
                                $formularioPersonas->armarCampo('p4.f1_pas_pat', $fin ["paspat"], $item);
                                $formularioPersonas->armarCampo('p4.f1_ing_ope', $fin ["ingope"], $item);
                                $formularioPersonas->armarCampo('p4.f1_ing_nope', $fin ["ingnoope"], $item);
                                $formularioPersonas->armarCampo('p4.f1_gas_ope', $fin ["gtoven"], $item);
                                $formularioPersonas->armarCampo('p4.f1_gas_nope', $fin ["gtoadm"], $item);
                                $formularioPersonas->armarCampo('p4.f1_upo', $fin ["utiope"], $item);
                                $formularioPersonas->armarCampo('p4.f1_upn', $fin ["utinet"], $item);
                                break;
                            case '2' :
                                $formularioPersonas->armarCampo('p4.f2_anio_ren', $fin ["anodatos"], $item);
                                $formularioPersonas->armarCampo('p4.f2_act_cor', $fin ["actcte"], $item);
                                $formularioPersonas->armarCampo('p4.f2_act_fn', $fin ["fijnet"], $item);
                                $formularioPersonas->armarCampo('p4.f2_act_otr', $fin ["actotr"], $item);
                                $formularioPersonas->armarCampo('p4.f2_act_val', $fin ["actval"], $item);
                                $formularioPersonas->armarCampo('p4.f2_act_tot', $fin ["acttot"], $item);
                                $formularioPersonas->armarCampo('p4.f2_pas_cor', $fin ["pascte"], $item);
                                $formularioPersonas->armarCampo('p4.f2_pas_lp', $fin ["paslar"], $item);
                                $formularioPersonas->armarCampo('p4.f2_pas_tot', $fin ["pastot"], $item);
                                $formularioPersonas->armarCampo('p4.f2_pat_tot', $fin ["pattot"], $item);
                                $formularioPersonas->armarCampo('p4.f2_pas_pat', $fin ["paspat"], $item);
                                $formularioPersonas->armarCampo('p4.f2_ing_ope', $fin ["ingope"], $item);
                                $formularioPersonas->armarCampo('p4.f2_ing_nope', $fin ["ingnoope"], $item);
                                $formularioPersonas->armarCampo('p4.f2_gas_ope', $fin ["gtoven"], $item);
                                $formularioPersonas->armarCampo('p4.f2_gas_nope', $fin ["gtoadm"], $item);
                                $formularioPersonas->armarCampo('p4.f2_upo', $fin ["utiope"], $item);
                                $formularioPersonas->armarCampo('p4.f2_upn', $fin ["utinet"], $item);
                                break;

                            case '3' :
                                $formularioPersonas->armarCampo('p4.f3_anio_ren', $fin ["anodatos"], $item);
                                $formularioPersonas->armarCampo('p4.f3_act_cor', $fin ["actcte"], $item);
                                $formularioPersonas->armarCampo('p4.f3_act_fn', $fin ["fijnet"], $item);
                                $formularioPersonas->armarCampo('p4.f3_act_otr', $fin ["actotr"], $item);
                                $formularioPersonas->armarCampo('p4.f3_act_val', $fin ["actval"], $item);
                                $formularioPersonas->armarCampo('p4.f3_act_tot', $fin ["acttot"], $item);
                                $formularioPersonas->armarCampo('p4.f3_pas_cor', $fin ["pascte"], $item);
                                $formularioPersonas->armarCampo('p4.f3_pas_lp', $fin ["paslar"], $item);
                                $formularioPersonas->armarCampo('p4.f3_pas_tot', $fin ["pastot"], $item);
                                $formularioPersonas->armarCampo('p4.f3_pat_tot', $fin ["pattot"], $item);
                                $formularioPersonas->armarCampo('p4.f3_pas_pat', $fin ["paspat"], $item);
                                $formularioPersonas->armarCampo('p4.f3_ing_ope', $fin ["ingope"], $item);
                                $formularioPersonas->armarCampo('p4.f3_ing_nope', $fin ["ingnoope"], $item);
                                $formularioPersonas->armarCampo('p4.f3_gas_ope', $fin ["gtoven"], $item);
                                $formularioPersonas->armarCampo('p4.f3_gas_nope', $fin ["gtoadm"], $item);
                                $formularioPersonas->armarCampo('p4.f3_upo', $fin ["utiope"], $item);
                                $formularioPersonas->armarCampo('p4.f3_upn', $fin ["utinet"], $item);
                                break;

                            case '4' :
                                $formularioPersonas->armarCampo('p4.f4_anio_ren', $fin ["anodatos"], $item);
                                $formularioPersonas->armarCampo('p4.f4_act_cor', $fin ["actcte"], $item);
                                $formularioPersonas->armarCampo('p4.f4_act_fn', $fin ["fijnet"], $item);
                                $formularioPersonas->armarCampo('p4.f4_act_otr', $fin ["actotr"], $item);
                                $formularioPersonas->armarCampo('p4.f4_act_val', $fin ["actval"], $item);
                                $formularioPersonas->armarCampo('p4.f4_act_tot', $fin ["acttot"], $item);
                                $formularioPersonas->armarCampo('p4.f4_pas_cor', $fin ["pascte"], $item);
                                $formularioPersonas->armarCampo('p4.f4_pas_lp', $fin ["paslar"], $item);
                                $formularioPersonas->armarCampo('p4.f4_pas_tot', $fin ["pastot"], $item);
                                $formularioPersonas->armarCampo('p4.f4_pat_tot', $fin ["pattot"], $item);
                                $formularioPersonas->armarCampo('p4.f4_pas_pat', $fin ["paspat"], $item);
                                $formularioPersonas->armarCampo('p4.f4_ing_ope', $fin ["ingope"], $item);
                                $formularioPersonas->armarCampo('p4.f4_ing_nope', $fin ["ingnoope"], $item);
                                $formularioPersonas->armarCampo('p4.f4_gas_ope', $fin ["gtoven"], $item);
                                $formularioPersonas->armarCampo('p4.f4_gas_nope', $fin ["gtoadm"], $item);
                                $formularioPersonas->armarCampo('p4.f4_upo', $fin ["utiope"], $item);
                                $formularioPersonas->armarCampo('p4.f4_upn', $fin ["utinet"], $item);
                                break;
                        }
                    }

                    if ($_SESSION ["formulario"] ["datos"] ["organizacion"] == '01') {

                        $nombre_completo = $_SESSION ["formulario"] ["datos"] ["ape1"] . ' ' . $_SESSION ["formulario"] ["datos"] ["ape2"] . ' ' . $_SESSION ["formulario"] ["datos"] ["nom1"] . ' ' . $_SESSION ["formulario"] ["datos"] ["nom2"];

                        $formularioPersonas->armarCampo('p4.firm_nom', utf8_decode($nombre_completo));
                        $formularioPersonas->armarCampo('p4.firm_ide', $_SESSION ["formulario"] ["datos"] ["identificacion"]);

                        switch ($_SESSION ["formulario"] ["datos"] ["tipoidentificacion"]) {
                            case "1" :
                                $formularioPersonas->armarCampo('p4.firm_cc', 'X');
                                break;
                            case "3" :
                                $formularioPersonas->armarCampo('p4.firm_ce', 'X');
                                break;
                            case "4" :
                                $formularioPersonas->armarCampo('p4.firm_ti', 'X');
                                break;
                            case "5" :
                                $formularioPersonas->armarCampo('p4.firm_pas', 'X');
                                break;
                        }
                    } else {
                        if (isset($_SESSION ["formulario"] ["datos"] ["propietarios"])) {
                            $formularioPersonas->armarCampo('p4.firm_nom', utf8_decode($_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["nombrepropietario"]));
                            $formularioPersonas->armarCampo('p4.firm_ide', $_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["identificacionpropietario"]);

                            switch ($_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["idtipoidentificacionpropietario"]) {
                                case "1" :
                                    $formularioPersonas->armarCampo('p4.firm_cc', 'X');
                                    break;
                                case "3" :
                                    $formularioPersonas->armarCampo('p4.firm_ce', 'X');
                                    break;
                                case "4" :
                                    $formularioPersonas->armarCampo('p4.firm_ti', 'X');
                                    break;
                                case "5" :
                                    $formularioPersonas->armarCampo('p4.firm_pas', 'X');
                                    break;
                            }
                        }
                    }
                    $formularioPersonas->armarCampo('p4.firma_elec', utf8_decode($txtFirmaElectronica));
                }
            } // fin if
        } // fin foreach
        // cabezote
        $fechaHora = date("Ymd") . date("His");
        $name = PATH_ABSOLUTO_SITIO . "/tmp/" . session_id() . "-Formulario-Anteriores-" . $_SESSION ["formulario"] ["datos"] ["matricula"] . '-' . $fechaHora . ".pdf";
        $name1 = session_id() . "-Formulario-Anteriores-" . $_SESSION ["formulario"] ["datos"] ["matricula"] . '-' . $fechaHora . ".pdf";
        //
   
        //fwrite($f, date("Y:m:d") . '-' . date("His") . $name. chr(13) . chr(10). $name1. chr(13) . chr(10).'--------------'. chr(13) . chr(10));
        //fclose($f);
        //
        $formularioPersonas->Output($name, "F");
        return $name1;
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

?>