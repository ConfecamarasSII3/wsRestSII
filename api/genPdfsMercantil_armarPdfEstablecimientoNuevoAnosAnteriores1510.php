<?php

/**
 * Funcion que arma el pdf de Anexo Mercantil cuando se trata de establecimientos de comercio
 * sucursales o agencias
 *
 * @param 		string		$numrec		Numero de recuperacion o tramite, si lo hay
 * @return 		string		$name		Nombre del archivo que contiene el formulario
 */
function armarPdfEstablecimientoNuevoAnosAnteriores1510($dbx = null, $numrec = '', $numliq = 0, $tipoimpresion = '', $txtFirmaElectronica = '') {
    try {

           if (!defined('ACTIVAR_CIRCULAR_002_2016')) {
        define('ACTIVAR_CIRCULAR_002_2016', '');
    }
        
        if (!isset($formularioEst)) {
            if (ACTIVAR_CIRCULAR_002_2016 == 'SI1') {
                require_once ('pdfFormularioRues-2016.php');
                $formularioEst = new formularioRues2016();
            } else {
                require_once ('pdfFormularioRues-2014.php');
                $formularioEst = new formularioRues2014();
            }
        }
       

        $formularioEst->setNumeroRecuperacion($numrec);
        $formularioEst->setNumeroLiquidacion($numliq);
        //Ajuste 30 abril 2015 - adicion linea 27
        $formularioEst->setFechaImpresion(date('Y/m/d H:i:s'));

        $item = 0;
        foreach ($_SESSION ["formulario"] ["datos"] ["f"] as $iAno => $fin) {

            if ($iAno != $_SESSION ["formulario"] ["datos"] ["anodatos"]) {
                $item ++;
                if ($item == 7)
                    $item = 1;
                if ($item == 1) {
                    if ($tipoimpresion == 'borrador') {
                        $formularioEst->agregarPagina(5, 0);
                    } else {
                        $formularioEst->agregarPagina(5, 1);
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

                $formularioEst->armarCampo('p5.cod_camara', $_SESSION ["generales"] ["codigoempresa"]);
                $formularioEst->armarCampo('p5.anio', substr($fec, 0, 4));
                $formularioEst->armarCampo('p5.mes', substr($fec, 4, 2));
                $formularioEst->armarCampo('p5.dia', substr($fec, 6, 2));

                if ($_SESSION ["formulario"] ["datos"] ["organizacion"] == '02') {
                    $formularioEst->armarCampo('p5.est', 'X');
                } else {
                    if ($_SESSION ["formulario"] ["datos"] ["categoria"] == '2') {
                        $formularioEst->armarCampo('p5.suc', 'X');
                    }
                    if ($_SESSION ["formulario"] ["datos"] ["categoria"] == '3') {
                        $formularioEst->armarCampo('p5.agen', 'X');
                    }
                }

                $formularioEst->armarCampo('p5.est_nom', \funcionesGenerales::utf8_decode($_SESSION["formulario"]["datos"]["nombre"]));
                $formularioEst->armarCampo('p5.est_num_mat', $_SESSION["formulario"]["datos"]["matricula"]);


                //ajuste 28 enero 2015
                if ($_SESSION ["formulario"] ["datos"]["organizacion"] == '02') {
                    if (!empty($_SESSION ["formulario"] ["datos"] ["propietarios"])) {
                        if (isset($_SESSION ["formulario"] ["datos"] ["propietarios"] [1])) {
                            $formularioEst->armarCampo('p5.prop_nom', $_SESSION ["formulario"] ["datos"]["propietarios"][1]["nombrepropietario"]);
                            $formularioEst->armarCampo('p5.prop_num_mat', $_SESSION ["formulario"]["datos"]["propietarios"][1]["matriculapropietario"]);

                            $ide = ltrim($_SESSION ["formulario"]["datos"]["propietarios"] [1]["identificacionpropietario"], '0');
                            $formularioEst->armarCampo('p5.nit', $ide);
                            $formularioEst->armarCampo('p5.dv', calcularDv($ide));
                        }
                    }
                }

                if ($_SESSION ["formulario"] ["datos"]["organizacion"] > '02' &&
                        ($_SESSION ["formulario"] ["datos"]["categoria"] == '2' || $_SESSION ["formulario"]["datos"]["categoria"] == '3')
                ) {

                    if ($_SESSION ["formulario"] ["datos"] ["cprazsoc"] != '') {
                        $formularioEst->armarCampo('p5.prop_nom', \funcionesGenerales::utf8_decode($_SESSION ["formulario"]["datos"]["cprazsoc"]));
                        $formularioEst->armarCampo('p5.prop_num_mat', $_SESSION ["formulario"]["datos"]["cpnummat"]);

                        $ide = ltrim($_SESSION ["formulario"] ["datos"]["cpnumnit"], '0');
                        $formularioEst->armarCampo('p5.nit', $ide);
                        $formularioEst->armarCampo('p5.dv', \funcionesGenerales::calcularDv($ide));
                    }
                }

                switch ($item) {
                    case 1 :
                        $formularioEst->armarCampo('p5.f1_anio_ren', $fin ["anodatos"], $item);
                        $formularioEst->armarCampo('p5.f1_activo', $fin ["actvin"], $item);
                        break;

                    case 2 :
                        $formularioEst->armarCampo('p5.f2_anio_ren', $fin ["anodatos"], $item);
                        $formularioEst->armarCampo('p5.f2_activo', $fin ["actvin"], $item);
                        break;

                    case 3 :
                        $formularioEst->armarCampo('p5.f3_anio_ren', $fin ["anodatos"], $item);
                        $formularioEst->armarCampo('p5.f3_activo', $fin ["actvin"], $item);
                        break;

                    case 4 :
                        $formularioEst->armarCampo('p5.f4_anio_ren', $fin ["anodatos"], $item);
                        $formularioEst->armarCampo('p5.f4_activo', $fin ["actvin"], $item);
                        break;

                    case 5 :
                        $formularioEst->armarCampo('p5.f5_anio_ren', $fin ["anodatos"], $item);
                        $formularioEst->armarCampo('p5.f5_activo', $fin ["actvin"], $item);
                        break;

                    case 6 :
                        $formularioEst->armarCampo('p5.f6_anio_ren', $fin ["anodatos"], $item);
                        $formularioEst->armarCampo('p5.f6_activo', $fin ["actvin"], $item);
                        break;
                }


                if ($_SESSION["formulario"]["datos"]["organizacion"] == '02') {
                    if (isset($_SESSION["formulario"]["propietarios"][1])) {

                        $formularioEst->armarCampo('p5.prop_nom', \funcionesGenerales::utf8_decode($_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["nombrepropietario"]));
                        $formularioEst->armarCampo('p5.prop_num_mat', $_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["matriculapropietario"]);

                        $texto = ltrim($_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["nitpropietario"], '0');
                        $textonit = '';
                        $textodv = '';
                        if (trim($texto) != '')
                            $textonit = substr($texto, 0, strlen($texto) - 1);
                        if (trim($texto) != '')
                            $textodv = substr($texto, strlen($texto) - 1, 1);

                        $formularioEst->armarCampo('p5.nit', $textonit);
                        $formularioEst->armarCampo('p5.dv', $textodv);

                        $formularioEst->armarCampo('p5.firm_nom', \funcionesGenerales::utf8_decode($_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["nombrepropietario"]));
                        $formularioEst->armarCampo('p5.firm_ide', $_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["identificacionpropietario"]);

                        switch ($_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["idtipoidentificacionpropietario"]) {
                            case "1" :
                                $formularioEst->armarCampo('p5.firm_cc', 'X');
                                break;
                            case "3" :
                                $formularioEst->armarCampo('p5.firm_ce', 'X');
                                break;
                            case "4" :
                                $formularioEst->armarCampo('p5.firm_ti', 'X');
                                break;
                            case "5" :
                                $formularioEst->armarCampo('p5.firm_pas', 'X');
                                break;
                        }
                    }
                }

                if ($_SESSION["formulario"]["datos"]["categoria"] == '2' || $_SESSION["formulario"]["datos"]["categoria"] == '3') {

                    $formularioEst->armarCampo('p5.prop_nom', $_SESSION ["formulario"] ["datos"] ["cprazsoc"]);
                    $formularioEst->armarCampo('p5.prop_num_mat', $_SESSION ["formulario"] ["datos"] ["cpnummat"]);

                    $texto = ltrim($_SESSION ["formulario"] ["datos"] ["cpnumnit"], '0');
                    $textonit = '';
                    $textodv = '';
                    if (trim($texto) != '')
                        $textonit = substr($texto, 0, strlen($texto) - 1);
                    if (trim($texto) != '')
                        $textodv = substr($texto, strlen($texto) - 1, 1);

                    $formularioEst->armarCampo('p5.nit', $textonit);
                    $formularioEst->armarCampo('p5.dv', $textodv);

                    // $formularioEst->armarCampo ( 'p5.firm_nom', $_SESSION ["formulario"] ["datos"] ["cprazsoc"] );
                    // $formularioEst->armarCampo ( 'p5.firm_ide',$_SESSION ["formulario"] ["datos"] ["cpnumnit"]  );
                }
                $formularioEst->armarCampo('p5.firma_elec', \funcionesGenerales::utf8_decode($txtFirmaElectronica));
            }
        }

        $fechaHora = date("Ymd") . date("His");
        $name = PATH_ABSOLUTO_SITIO . "/tmp/" . session_id() . "-Formulario-Anteriores-" . $_SESSION ["formulario"] ["datos"] ["matricula"] . '-' . $fechaHora . ".pdf";
        $name1 = session_id() . "-Formulario-Anteriores-" . $_SESSION ["formulario"] ["datos"] ["matricula"] . '-' . $fechaHora . ".pdf";
        $formularioEst->Output($name, "F");
        return $name1;
        exit();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

?>