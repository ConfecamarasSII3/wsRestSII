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
function armarPdfEstablecimientoNuevoAnosAnteriores1082Api($dbx = null, $numrec = '', $numliq = 0, $tipoimpresion = '', $txtFirmaElectronica = '', $txtFirmaManuscrita = '', $ideFirmaManuscrita = '', $nomFirmaManuscrita = '', $fechaimprimir = '') {
    try {
        if (!defined('ACTIVAR_CIRCULAR_002_2016')) {
            define('ACTIVAR_CIRCULAR_002_2016', '');
        }

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
            $formulario->agregarPagina(5, $tipoFormulario);
        }

        $item = 0;
        foreach ($datosForm["f"] as $iAno => $fin) {

            if ($iAno != $datosForm["anodatos"]) {
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
                    $formulario->agregarPagina(5, $tipoFormulario);
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
                    $fec = str_replace("-", "", $fechaimprimir);
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
                $formulario->armarCampo('p5.nom_est', \funcionesGenerales::utf8_decode($datosForm["nombre"]));
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

                            $nit = ltrim((string)$datosForm["propietarios"][1]["nitpropietario"], '0');
                            
                            if (trim((string)$nit) != '') {
                                $sepide = \funcionesGenerales::separarDv($nit);
                                $formulario->armarCampo('p5.nit', 'X');
                                $formulario->armarCampo('p5.nit_num', $sepide["identificacion"]);
                                $formulario->armarCampo('p5.nit_dv', $sepide["dv"]);
                            } else {
                                $formulario->armarCampo('p5.nit', '');
                                $formulario->armarCampo('p5.nit_num', ltrim($datosForm["propietarios"][1]["identificacionpropietario"], '0'));
                                $formulario->armarCampo('p5.nit_dv', \funcionesGenerales::calcularDv(ltrim($datosForm["propietarios"][1]["identificacionpropietario"], '0')));
                            }
                            
                            /**
                             * 
                             * DATOS FIRMANTE
                             * 
                             */
                            if (trim($datosForm["propietarios"][1]["numidreplegpropietario"]) == '') {
                                $formulario->armarCampo('p5.firma_nom', \funcionesGenerales::utf8_decode($datosForm["propietarios"][1]["nombrepropietario"]));
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
                        $formulario->armarCampo('p5.nom_pro_est', \funcionesGenerales::utf8_decode($datosForm["cprazsoc"]));
                        $formulario->armarCampo('p5.nom_pro_est_mat', $datosForm["cpnummat"]);

                        $ide = ltrim($datosForm["cpnumnit"], '0');
                        $formulario->armarCampo('p5.nit', 'X');
                        $formulario->armarCampo('p5.nit_num', $ide);
                        $formulario->armarCampo('p5.dv', \funcionesGenerales::calcularDv($ide));
                    }
                }

                $formulario->armarCampo('p5.f' . $item . '_ano', $fin ["anodatos"], $item);
                $formulario->armarCampo('p5.f' . $item . '_act', \funcionesGenerales::truncateFloatForm($fin ["actvin"], 0), $item);

                if (trim($txtFirmaElectronica) != '') {
                    $formulario->armarCampo('p5.firma_elec', \funcionesGenerales::utf8_decode($txtFirmaElectronica));
                }
                if (trim($txtFirmaManuscrita) != '') {
                    $formulario->armarCampoImagen('p5.firma_manuscrita', $txtFirmaManuscrita);
                }
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

?>