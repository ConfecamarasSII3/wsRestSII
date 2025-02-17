<?php

/**
 * Funcion que arma el pdf de Anexo Mercantil cuando se trata de establecimientos de comercio
 * sucursales o agencias
 *
 * @param 		string		$numrec		Numero de recuperacion o tramite, si lo hay
 * @return 		string		$name		Nombre del archivo que contiene el formulario
 */
function armarPdfEstablecimientoNuevo1510Sii($dbx,$numrec = '', $numliq = 0, $tipoimpresion = '', $prediligenciado = 'no', $txtFirmaElectronica = '') {
    if (!defined('ACTIVAR_CIRCULAR_002_2016')) {
        define('ACTIVAR_CIRCULAR_002_2016', '');
    }

    if (!isset($formulario)) {
        if (ACTIVAR_CIRCULAR_002_2016 == 'SI1') {
            require_once ('pdfFormularioRues-2016.php');
            $formulario = new formularioRues2016();
        } else {
            require_once ('pdfFormularioRues-2014.php');
            $formulario = new formularioRues2014();
        }
    }

    $formulario->setNumeroRecuperacion($numrec);
    $formulario->setNumeroLiquidacion($numliq);
    //Ajuste 25 marzo 2015 - Adicion linea 25
    $formulario->setFechaImpresion(date('Y/m/d H:i:s'));

    if ($tipoimpresion == 'borrador') {
        $formulario->agregarPagina(3, 0); // primera pagina como borrador
    } else {
        $formulario->agregarPagina(3, 1); // primera pagina sin borrador
    }

    if ($tipoimpresion != 'vacio') {
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

        $formulario->armarCampo('p3.cod_camara', $_SESSION ["generales"] ["codigoempresa"]);
        $formulario->armarCampo('p3.anio', substr($fec, 0, 4));
        $formulario->armarCampo('p3.mes', substr($fec, 4, 2));
        $formulario->armarCampo('p3.dia', substr($fec, 6, 2));

        // Tipo de tr�mite
        if (($_SESSION ["formulario"] ["tipotramite"] == 'renovacionmatricula') || ($_SESSION ["formulario"] ["tipotramite"] == 'renovacionesadl')) {
            $formulario->armarCampo('p3.ren', 'X');
        }

        if (($_SESSION ["formulario"] ["tipotramite"] == 'matriculapnat') || ($_SESSION ["formulario"] ["tipotramite"] == 'matriculaest')) {
            $formulario->armarCampo('p3.mat', 'X');
        }

        if ($_SESSION ["formulario"] ["datos"] ["organizacion"] == '02') {
            $formulario->armarCampo('p3.est', 'X');
        }

        if ($_SESSION ["formulario"] ["datos"] ["categoria"] == '2') {
            $formulario->armarCampo('p3.suc', 'X');
        }

        if ($_SESSION ["formulario"] ["datos"] ["categoria"] == '3') {
            $formulario->armarCampo('p3.age', 'X');
        }

        //
        if ($_SESSION ["formulario"] ["datos"] ["matricula"] != 'NUEVANAT' && $_SESSION ["formulario"] ["datos"] ["matricula"] != 'NUEVAEST') {
            $formulario->armarCampo('p3.num_mat', $_SESSION ["formulario"] ["datos"] ["matricula"]);
        }
        $formulario->armarCampo('p3.anio_ren', $_SESSION ["formulario"] ["datos"] ["anodatos"]);

        //
        $formulario->armarCampo('p3.nom', utf8_decode($_SESSION ["formulario"] ["datos"] ["nombre"]));

        //
        $formulario->armarCampo('p3.dir', utf8_decode($_SESSION ["formulario"] ["datos"] ["dircom"]));
        $formulario->armarCampo('p3.pos', '');

        if (ltrim($_SESSION ["formulario"] ["datos"] ["barriocom"], "0") != '') {
            $formulario->armarCampo('p3.bar', retornarRegistroMysqli2($dbx,'mreg_barriosmuni',"idmunicipio='" . $_SESSION ["formulario"] ["datos"] ["muncom"] . "' and idbarrio='" . $_SESSION ["formulario"] ["datos"] ["barriocom"] . "'","nombre"));
        }

        $formulario->armarCampo('p3.mun', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["muncom"] . "'","ciudad")));
        $formulario->armarCampo('p3.dep', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["muncom"] . "'","departamento")));
        $formulario->armarCampo('p3.dane', $_SESSION ["formulario"] ["datos"] ["muncom"]);
        $formulario->armarCampo('p3.tel1', $_SESSION ["formulario"] ["datos"] ["telcom1"]);
        $formulario->armarCampo('p3.tel2', $_SESSION ["formulario"] ["datos"] ["telcom2"]);
        $formulario->armarCampo('p3.tel3', $_SESSION ["formulario"] ["datos"] ["celcom"]);
        $formulario->armarCampo('p3.email', $_SESSION ["formulario"] ["datos"] ["emailcom"]);
        $formulario->armarCampo('p3.fax', '');

        //
        $formulario->armarCampo('p3.not_dir', utf8_decode($_SESSION ["formulario"] ["datos"] ["dirnot"]));
        $formulario->armarCampo('p3.not_pos', '');

        if (ltrim($_SESSION ["formulario"] ["datos"] ["barrionot"], "0") != '') {
            $formulario->armarCampo('p3.not_bar', retornarRegistroMysqli2($dbx,'mreg_barriosmuni',"idmunicipio='" . $_SESSION ["formulario"] ["datos"] ["munnot"] . "' and idbarrio='" . $_SESSION ["formulario"] ["datos"] ["barrionot"] . "'","nombre"));
        }

        $formulario->armarCampo('p3.not_mun', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["munnot"] . "'","ciudad")));
        $formulario->armarCampo('p3.not_dep', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["munnot"] . "'","departamento")));
        $formulario->armarCampo('p3.not_dane', $_SESSION ["formulario"] ["datos"] ["munnot"]);
        $formulario->armarCampo('p3.not_email', $_SESSION ["formulario"] ["datos"] ["emailnot"]);
        $formulario->armarCampo('p3.not_fax', '');

        //
        if ($prediligenciado != 'si') {
            $formulario->armarCampo('p3.activos', number_format($_SESSION ["formulario"] ["datos"] ["actvin"], "0"));
        }

        //
        $formulario->armarCampo('p3.ciiu1', substr($_SESSION ["formulario"] ["datos"] ["ciius"] [1], 1, 4));
        $formulario->armarCampo('p3.ciiu2', substr($_SESSION ["formulario"] ["datos"] ["ciius"] [2], 1, 4));
        $formulario->armarCampo('p3.ciiu3', substr($_SESSION ["formulario"] ["datos"] ["ciius"] [3], 1, 4));
        $formulario->armarCampo('p3.ciiu4', substr($_SESSION ["formulario"] ["datos"] ["ciius"] [4], 1, 4));

        //
        if ($prediligenciado != 'si') {
            $formulario->armarCampo('p3.num_trab', $_SESSION ["formulario"] ["datos"] ["personal"]);
        }

        //
        if ($_SESSION ["formulario"] ["datos"] ["tipopropiedad"] == '')
            $_SESSION ["formulario"] ["datos"] ["tipopropiedad"] = '0';
        if ($_SESSION ["formulario"] ["datos"] ["tipopropiedad"] == '0')
            $formulario->armarCampo('p3.prop', 'X');
        if ($_SESSION ["formulario"] ["datos"] ["tipopropiedad"] == '1')
            $formulario->armarCampo('p3.soci', 'X');
        if ($_SESSION ["formulario"] ["datos"] ["tipopropiedad"] == '2')
            $formulario->armarCampo('p3.coop', 'X');

        //
        if ($_SESSION ["formulario"] ["datos"] ["tipolocal"] == '1')
            $formulario->armarCampo('p3.loc_prop', 'X');
        if ($_SESSION ["formulario"] ["datos"] ["tipolocal"] == '0')
            $formulario->armarCampo('p3.loc_ajen', 'X');

        //
        $iProp = 0;

        //
        if ($_SESSION ["formulario"] ["datos"]["organizacion"] == '02') {
            if (!empty($_SESSION ["formulario"] ["datos"] ["propietarios"])) {
                if (isset($_SESSION ["formulario"] ["datos"] ["propietarios"] [1])) {

                    $formulario->armarCampo('p3.prop1_nom', utf8_decode($_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["nombrepropietario"]));
                    $formulario->armarCampo('p3.prop1_ide', $_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["identificacionpropietario"]);

                    switch ($_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["idtipoidentificacionpropietario"]) {
                        case "1" :
                            $formulario->armarCampo('p3.prop1_cc', 'X');
                            break;
                        case "2" :
                            $formulario->armarCampo('p3.prop1_nit', 'X');
                            break;
                        case "3" :
                            $formulario->armarCampo('p3.prop1_ce', 'X');
                            break;
                        case "5" :
                            $formulario->armarCampo('p3.prop1_pas', 'X');
                            break;
                    }

                    $formulario->armarCampo('p3.prop1_num_mat', $_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["matriculapropietario"]);
                    $formulario->armarCampo('p3.prop1_camara', $_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["camarapropietario"]);
                    $formulario->armarCampo('p3.prop1_dom_dir', utf8_decode($_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["direccionpropietario"]));
                    $formulario->armarCampo('p3.prop1_dom_mun', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["municipiopropietario"] . "'","ciudad")));
                    $formulario->armarCampo('p3.prop1_dom_dep', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["municipiopropietario"] . "'","departamento")));
                    $formulario->armarCampo('p3.prop1_dom_tel1', $_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["telefonopropietario"]);
                    $formulario->armarCampo('p3.prop1_dom_tel2', $_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["telefono2propietario"]);
                    $formulario->armarCampo('p3.prop1_dom_tel3', '');
                    $formulario->armarCampo('p3.prop1_not_dir', utf8_decode($_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["direccionnotpropietario"]));
                    $formulario->armarCampo('p3.prop1_not_mun', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["municipionotpropietario"] . "'","ciudad")));
                    $formulario->armarCampo('p3.prop1_not_dep', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["municipionotpropietario"] . "'","departamento")));
                    $formulario->armarCampo('p3.rep1_nom', utf8_decode($_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["nomreplegpropietario"]));
                    $formulario->armarCampo('p3.rep1_ide', $_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["numidreplegpropietario"]);

                    switch ($_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["tipoidreplegpropietario"]) {
                        case "1" :
                            $formulario->armarCampo('p3.rep1_cc', 'X');
                            break;
                        case "4" :
                            $formulario->armarCampo('p3.rep1_ti', 'X');
                            break;
                        case "3" :
                            $formulario->armarCampo('p3.rep1_ce', 'X');
                            break;
                        case "5" :
                            $formulario->armarCampo('p3.prop1_pas', 'X');
                            break;
                    }
                }

                if (isset($_SESSION ["formulario"] ["datos"] ["propietarios"] [2])) {

                    $formulario->armarCampo('p3.prop2_nom', utf8_decode($_SESSION ["formulario"] ["datos"] ["propietarios"] [2] ["nombrepropietario"]));
                    $formulario->armarCampo('p3.prop2_ide', $_SESSION ["formulario"] ["datos"] ["propietarios"] [2] ["identificacionpropietario"]);

                    switch ($_SESSION ["formulario"] ["datos"] ["propietarios"] [2] ["idtipoidentificacionpropietario"]) {
                        case "1" :
                            $formulario->armarCampo('p3.prop2_cc', 'X');
                            break;
                        case "2" :
                            $formulario->armarCampo('p3.prop2_nit', 'X');
                            break;
                        case "3" :
                            $formulario->armarCampo('p3.prop2_ce', 'X');
                            break;
                        case "5" :
                            $formulario->armarCampo('p3.prop2_pas', 'X');
                            break;
                    }

                    $formulario->armarCampo('p3.prop2_num_mat', $_SESSION ["formulario"] ["datos"] ["propietarios"] [2] ["matriculapropietario"]);
                    $formulario->armarCampo('p3.prop2_camara', $_SESSION ["formulario"] ["datos"] ["propietarios"] [2] ["camarapropietario"]);
                    $formulario->armarCampo('p3.prop2_dom_dir', utf8_decode($_SESSION ["formulario"] ["datos"] ["propietarios"] [2] ["direccionpropietario"]));
                    $formulario->armarCampo('p3.prop2_dom_mun', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["propietarios"] [2] ["municipiopropietario"] . "'","ciudad")));
                    $formulario->armarCampo('p3.prop2_dom_dep', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["propietarios"] [2] ["municipiopropietario"] . "'","departamento")));
                    $formulario->armarCampo('p3.prop2_dom_tel1', $_SESSION ["formulario"] ["datos"] ["propietarios"] [2] ["telefonopropietario"]);
                    $formulario->armarCampo('p3.prop2_dom_tel2', $_SESSION ["formulario"] ["datos"] ["propietarios"] [2] ["telefono2propietario"]);
                    $formulario->armarCampo('p3.prop2_dom_tel3', '');
                    $formulario->armarCampo('p3.prop2_not_dir', utf8_decode($_SESSION ["formulario"] ["datos"] ["propietarios"] [2] ["direccionnotpropietario"]));
                    $formulario->armarCampo('p3.prop2_not_mun', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["propietarios"] [2] ["municipionotpropietario"] . "'","ciudad")));
                    $formulario->armarCampo('p3.prop2_not_dep', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["propietarios"] [2] ["municipionotpropietario"] . "'","departamento")));
                    $formulario->armarCampo('p3.rep2_nom', utf8_decode($_SESSION ["formulario"] ["datos"] ["propietarios"] [2] ["nomreplegpropietario"]));
                    $formulario->armarCampo('p3.rep2_ide', $_SESSION ["formulario"] ["datos"] ["propietarios"] [2] ["numidreplegpropietario"]);

                    switch ($_SESSION ["formulario"] ["datos"] ["propietarios"] [2] ["tipoidreplegpropietario"]) {
                        case "1" :
                            $formulario->armarCampo('p3.rep2_cc', 'X');
                            break;
                        case "4" :
                            $formulario->armarCampo('p3.rep2_ti', 'X');
                            break;
                        case "3" :
                            $formulario->armarCampo('p3.rep2_ce', 'X');
                            break;
                        case "5" :
                            $formulario->armarCampo('p3.rep2_pas', 'X');
                            break;
                    }
                }
            }
        }

        if ($_SESSION ["formulario"] ["datos"]["organizacion"] > '02' &&
                ($_SESSION ["formulario"] ["datos"]["categoria"] == '2' || $_SESSION ["formulario"] ["datos"]["categoria"] == '3')
        ) {

            if ($_SESSION ["formulario"] ["datos"] ["cprazsoc"] != '') {

                $formulario->armarCampo('p3.prop1_nom', utf8_decode($_SESSION ["formulario"] ["datos"] ["cprazsoc"]));
                $formulario->armarCampo('p3.prop1_ide', $_SESSION ["formulario"] ["datos"] ["cpnumnit"]);
                $formulario->armarCampo('p3.prop1_nit', 'X');

                $formulario->armarCampo('p3.prop1_num_mat', $_SESSION ["formulario"] ["datos"] ["cpnummat"]);
                $formulario->armarCampo('p3.prop1_camara', $_SESSION ["formulario"] ["datos"] ["cpcodcam"]);
                $formulario->armarCampo('p3.prop1_dom_dir', $_SESSION ["formulario"] ["datos"] ["cpdircom"]);
                $formulario->armarCampo('p3.prop1_dom_mun', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["cpcodmun"] . "'","ciudad")));
                $formulario->armarCampo('p3.prop1_dom_dep', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["cpcodmun"] . "'","departamento")));
                $formulario->armarCampo('p3.prop1_dom_tel1', $_SESSION ["formulario"] ["datos"] ["cpnumtel"]);
                $formulario->armarCampo('p3.prop1_dom_tel2', '');
                $formulario->armarCampo('p3.prop1_dom_tel3', '');
                $formulario->armarCampo('p3.prop1_not_dir', $_SESSION ["formulario"] ["datos"] ["cpdirnot"]);
                $formulario->armarCampo('p3.prop1_not_mun', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["cpmunnot"] . "'","ciudad")));
                $formulario->armarCampo('p3.prop1_not_dep', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["cpmunnot"] . "'","departamento")));

                /*
                  $formulario->armarCampo ( 'p3.rep1_nom', $_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["nomreplegpropietario"] );
                  $formulario->armarCampo ( 'p3.rep1_ide', $_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["numidreplegpropietario"] );

                  switch ($_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["tipoidreplegpropietario"]) {
                  case "1" :
                  $formulario->armarCampo ( 'p3.rep1_cc', 'X' );
                  break;
                  case "4" :
                  $formulario->armarCampo ( 'p3.rep1_ti', 'X' );
                  break;
                  case "3" :
                  $formulario->armarCampo ( 'p3.rep1_ce', 'X' );
                  break;
                  case "5" :
                  $formulario->armarCampo ( 'p3.prop1_pas', 'X' );
                  break;
                  }
                 */
            }
        }
        $formulario->armarCampo('p3.firma_elec', utf8_decode($txtFirmaElectronica));
    }

    /*
     * if ($numliq != 0) { $arrFir = array (); $arrTem = retornarRegistros ('sms_firmados',"idliquidacion=" . $numliq); if ($arrTem && !empty ($arrTem)) { foreach ($arrTem as $t) { if ($t["estado"]=='FI') { $arrFir = $t; } } } if (!empty($arrFir)) { $formulario->armarCampo ( 'p2.firma_elec','Firmado electr�nicamente por ' . $arrFir["nombre"] . " el " . $arrFir["fecha"] . ' a las ' . $arrFir["hora"] . ', c�digo de firmado ' . $arrFir["pkgsms"]); } }
     */

    $fechaHora = date("Ymd") . date("His");
    if ($tipoimpresion != 'vacio') {
        $name = PATH_ABSOLUTO_SITIO . "/tmp/" . session_id() . "-Formulario-" . $_SESSION ["formulario"] ["datos"] ["matricula"] . '-' . $fechaHora . ".pdf";
        $name1 = session_id() . "-Formulario-" . $_SESSION ["formulario"] ["datos"] ["matricula"] . '-' . $fechaHora . ".pdf";
        $formulario->Output($name, "F");
        return $name1;
    } else {
        $name = PATH_ABSOLUTO_SITIO . "/tmp/" . session_id() . "-FormularioEstablecimiento-" . session_id() . '-' . $fechaHora . ".pdf";
        $name1 = session_id() . "-FormularioEstablecimiento-" . session_id() . '-' . $fechaHora . ".pdf";
        $formulario->Output($name, "F");
        return $name1;
    }
}

?>