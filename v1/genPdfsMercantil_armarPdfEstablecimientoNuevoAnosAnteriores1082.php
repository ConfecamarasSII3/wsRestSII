<?php

/**
 * Función que realiza el armado de los formularios de registro mercantil 
 * a partir de la información del expediente - Incluye campos según Circular 
 * única - Establecimientos Sucursales y agencias Años anteriores
 * 
 * @param type $numrec
 * @param type $numliq
 * @param type $tipoimpresion
 * @param type $prediligenciado
 * @param type $txtFirmaElectronica
 * @since 2017/05/15
 * @return string
 */
function armarPdfEstablecimientoNuevoAnosAnteriores1082Sii($dbx,$numrec = '', $numliq = 0, $tipoimpresion = '', $txtFirmaElectronica = '', $txtFirmaManuscrita = '',$ideFirmaManuscrita = '',$nomFirmaManuscrita = '') {

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
                                $formulario->armarCampo('p5.dv', substr($nit, strlen($nit) - 1, 1));
                            } else {
                                $formulario->armarCampo('p5.nit', '');
                                $formulario->armarCampo('p5.nit_num', ltrim($datosForm["propietarios"][1]["identificacionpropietario"], '0'));
                                $formulario->armarCampo('p5.dv', calcularDvSii2(ltrim($datosForm["propietarios"][1]["identificacionpropietario"], '0')));
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


                            /*
                              if (trim($datosForm["propietarios"][1]["numidreplegpropietario"]) == '') {
                              $formulario->armarCampo('p5.firma_nom', utf8_decode($datosForm["propietarios"][1]["nombrepropietario"]));
                              $formulario->armarCampo('p5.firma_ide', ltrim($datosForm["propietarios"][1]["identificacionpropietario"], '0'));
                              $tipoIdeFirma = $datosForm["propietarios"][1]["idtipoidentificacionpropietario"];
                              } else {
                              $formulario->armarCampo('p5.firma_nom', utf8_decode($datosForm["propietarios"][1]["nomreplegpropietario"]));
                              $formulario->armarCampo('p5.firma_ide', ltrim($datosForm["propietarios"][1]["numidreplegpropietario"], '0'));
                              $tipoIdeFirma = $datosForm["propietarios"][1]["tipoidreplegpropietario"];
                              }
                             */

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
                        $formulario->armarCampo('p5.dv', calcularDvSii2($ide));
                    }
                }

                $formulario->armarCampo('p5.f' . $item . '_ano', $fin ["anodatos"], $item);
                $formulario->armarCampo('p5.f' . $item . '_act', truncateFloatFormSii2($fin ["actvin"], 0), $item);

                if (trim($txtFirmaElectronica) != '') {
                    $formulario->armarCampo('p5.firma_elec', utf8_decode($txtFirmaElectronica));
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