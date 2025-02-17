<?php

/**
 * 
 * @param type $dbx
 * @param type $numrec
 * @param type $numliq
 * @param type $tipoimpresion
 * @param type $txtFirmaElectronica
 * @param type $txtFirmaManuscrita
 * @param type $ideFirmaManuscrita
 * @param type $nomFirmaManuscrita
 * @param type $fechaimprimir
 * @return string
 */
function armarPdfPrincipalNuevoAnosAnteriores1082Api($dbx = null, $numrec = '', $numliq = 0, $tipoimpresion = '', $txtFirmaElectronica = '', $txtFirmaManuscrita = '', $ideFirmaManuscrita = '', $nomFirmaManuscrita = '', $fechaimprimir = '') {
    try {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/pdfFormularioRues-2017.php'); //VERSION 23 DE MAYO DE 2017
        $formulario = new formularioRues2017Api();

        $datosForm = $_SESSION ["formulario"]["datos"];

        $formulario->setNumeroRecuperacion($numrec);
        $formulario->setNumeroLiquidacion($numliq);
        if ($fechaimprimir == '') {
            $formulario->setFechaImpresion(date('Y/m/d H:i:s'));
        } else {
            $formulario->setFechaImpresion($fechaimprimir);
        }

        if ($tipoimpresion == 'vacio') {
            $formulario->agregarPagina(4, $tipoFormulario);
        }

        $item = 0;

        foreach ($datosForm["f"] as $iAno => $fin) {

            if ($iAno != $datosForm["anodatos"]) {
                $inclu = 'si';

                // 2018-07-16: JINT: No muestra años para los que no haya valores
                if ($fin ["fechadatos"] == '' &&
                        $fin ["personal"] == 0 &&
                        $fin ["actcte"] == 0 &&
                        $fin ["actnocte"] == 0 &&
                        $fin ["acttot"] == 0 &&
                        $fin ["pascte"] == 0 &&
                        $fin ["paslar"] == 0 &&
                        $fin ["pastot"] == 0 &&
                        $fin ["pattot"] == 0 &&
                        $fin ["paspat"] == 0 &&
                        $fin ["balsoc"] == 0 &&
                        $fin ["ingope"] == 0 &&
                        $fin ["ingnoope"] == 0 &&
                        $fin ["cosven"] == 0 &&
                        $fin ["gtoven"] == 0 &&
                        $fin ["gtoadm"] == 0 &&
                        $fin ["gasimp"] == 0 &&
                        $fin ["utiope"] == 0 &&
                        $fin ["utinet"] == 0) {
                    $inclu = 'no';
                }

                //
                if ($inclu == 'si') {
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
                }

                //
                if ($inclu == 'si') {
                    $item++;
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
                    if ($fechaimprimir == '') {
                    if (isset($_SESSION ["tramite"]["fechaultimamodificacion"]) && (!empty($_SESSION ["tramite"]["fechaultimamodificacion"]))) {
                        $fec = $_SESSION ["tramite"]["fechaultimamodificacion"];
                    } else {
                        if (isset($_SESSION ["tramite"]["fecha"]) && (!empty($_SESSION ["tramite"]["fecha"]))) {
                            $fec = $_SESSION ["tramite"]["fecha"];
                        } else {
                            $fec = date("Ymd");
                        }
                    }  
                    } else {
                        $fec = str_replace("-","",$fechaimprimir);
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
                        $formulario->armarCampo('p4.raz_soc', \funcionesGenerales::utf8_decode($datosForm["nombre"]));
                    } else {
                        if (trim($datosForm["ape1"]) == '') {
                            $formulario->armarCampo('p4.raz_soc', \funcionesGenerales::utf8_decode($datosForm["nombre"]));
                        } else {
                            $formulario->armarCampo('p4.ape1', \funcionesGenerales::utf8_decode($datosForm["ape1"]));
                            $formulario->armarCampo('p4.ape2', \funcionesGenerales::utf8_decode($datosForm["ape2"]));
                            $formulario->armarCampo('p4.nom', \funcionesGenerales::utf8_decode($datosForm["nom1"] . ' ' . $datosForm["nom2"]));
                        }
                    }

                    $formulario->armarCampo('p4.f' . $item . '_ano', $fin ["anodatos"], $item);
                    $formulario->armarCampo('p4.f' . $item . '_act_cor', \funcionesGenerales::truncateFloatForm($fin ["actcte"], 0), $item);
                    $formulario->armarCampo('p4.f' . $item . '_act_no_cor', \funcionesGenerales::truncateFloatForm($fin ["actnocte"], 0), $item);
                    $formulario->armarCampo('p4.f' . $item . '_act_tot', \funcionesGenerales::truncateFloatForm($fin ["acttot"], 0), $item);
                    $formulario->armarCampo('p4.f' . $item . '_pas_cor', \funcionesGenerales::truncateFloatForm($fin ["pascte"], 0), $item);
                    $formulario->armarCampo('p4.f' . $item . '_pas_no_cor', \funcionesGenerales::truncateFloatForm($fin ["paslar"], 0), $item);
                    $formulario->armarCampo('p4.f' . $item . '_pas_tot', \funcionesGenerales::truncateFloatForm($fin ["pastot"], 0), $item);
                    $formulario->armarCampo('p4.f' . $item . '_pas_net', \funcionesGenerales::truncateFloatForm($fin ["pattot"], 0), $item);
                    $formulario->armarCampo('p4.f' . $item . '_pas_pat', \funcionesGenerales::truncateFloatForm($fin ["paspat"], 0), $item);

                    if ($datosForm["organizacion"] == '12' || $datosForm["organizacion"] == '14') {
                        if ($datosForm["categoria"] == '1') {
                            $formulario->armarCampo('p4.f' . $item . '_bal_soc', \funcionesGenerales::truncateFloatForm($fin ["balsoc"], 0), $item);
                        }
                    }

                    $formulario->armarCampo('p4.f' . $item . '_ing_act_ord', \funcionesGenerales::truncateFloatForm($fin ["ingope"], 0), $item);
                    $formulario->armarCampo('p4.f' . $item . '_otr_ing', \funcionesGenerales::truncateFloatForm($fin ["ingnoope"], 0), $item);
                    $formulario->armarCampo('p4.f' . $item . '_cos_ven', \funcionesGenerales::truncateFloatForm($fin ["cosven"], 0), $item);
                    $formulario->armarCampo('p4.f' . $item . '_gas_ope', \funcionesGenerales::truncateFloatForm($fin ["gtoven"], 0), $item);
                    $formulario->armarCampo('p4.f' . $item . '_otr_gas', \funcionesGenerales::truncateFloatForm($fin ["gtoadm"], 0), $item);
                    $formulario->armarCampo('p4.f' . $item . '_gas_imp', \funcionesGenerales::truncateFloatForm($fin ["gasimp"], 0), $item);
                    $formulario->armarCampo('p4.f' . $item . '_uti_ope', \funcionesGenerales::truncateFloatForm($fin ["utiope"], 0), $item);
                    $formulario->armarCampo('p4.f' . $item . '_res_per', \funcionesGenerales::truncateFloatForm($fin ["utinet"], 0), $item);
                    //

                    if ($datosForm["organizacion"] == '01') {

                        $nombre_completo = $datosForm["ape1"] . ' ' . $datosForm["ape2"] . ' ' . $datosForm["nom1"] . ' ' . $datosForm["nom2"];

                        $formulario->armarCampo('p4.firma_nom', \funcionesGenerales::utf8_decode($nombre_completo));
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
                        if (isset($datosForm["propietarios"]) && !empty($datosForm["propietarios"])) {
                            $formulario->armarCampo('p4.firma_nom', \funcionesGenerales::utf8_decode($datosForm["propietarios"][1]["nombrepropietario"]));
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

                    if (trim($txtFirmaElectronica) != '') {
                        $formulario->armarCampo('p4.firma_elec', \funcionesGenerales::utf8_decode($txtFirmaElectronica));
                    }
                    if (trim($txtFirmaManuscrita) != '') {
                        $formulario->armarCampoImagen('p4.firma_manuscrita', $txtFirmaManuscrita);
                    }
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

?>