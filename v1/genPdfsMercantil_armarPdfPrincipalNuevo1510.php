<?php

/**
 * Funcion que genera el pdf de la caratula única y el anexo de registro mercantil cuando se trata de
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
function armarPdfPrincipalNuevo1510Sii($dbx,$numrec = '', $numliq = 0, $tipoimpresion = '', $prediligenciado = 'no', $txtFirmaElectronica = '') {

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
    //Ajuste 25 marzo 2015
    $formulario->setFechaImpresion(date('Y/m/d H:i:s'));


    if ($tipoimpresion == 'borrador') {
        $formulario->agregarPagina(1, 0); // primera pagina como borrador
    } else {
        $formulario->agregarPagina(1, 1); // primera pagina sin borrador
    }


    if ($tipoimpresion != 'vacio') {

        // fecha de diligenciamiento
        if (isset($_SESSION ["tramite"] ["fechaultimamodificacion"])) {
            $fec = $_SESSION ["tramite"] ["fechaultimamodificacion"];
        } else {
            if (isset($_SESSION ["tramite"] ["fecha"])) {
                $fec = $_SESSION ["tramite"] ["fecha"];
            } else {
                $fec = date("Ymd");
            }
        }


        $formulario->armarCampo('p1.cod_camara', $_SESSION ["generales"] ["codigoempresa"]);
        $formulario->armarCampo('p1.anio', substr($fec, 0, 4));
        $formulario->armarCampo('p1.mes', substr($fec, 4, 2));
        $formulario->armarCampo('p1.dia', substr($fec, 6, 2));



        if (!isset($_SESSION["formulario"]["tipotramite"])) {
            $_SESSION["formulario"]["tipotramite"] = '';
        }

        // Tipo de tramite
        if (($_SESSION ["formulario"] ["tipotramite"] == 'renovacionmatricula') || ($_SESSION ["formulario"] ["tipotramite"] == 'renovacionesadl')) {
            if (substr($_SESSION ["formulario"] ["datos"] ["matricula"], 0, 1) != 'S') {
                $formulario->armarCampo('p1.rm_ren', 'X');
                $formulario->armarCampo('p1.rm_anio_ren', $_SESSION ["formulario"] ["datos"] ["anodatos"]);
                $formulario->armarCampo('p1.rm_num_mat', $_SESSION ["formulario"] ["datos"] ["matricula"]);
            } else {
                $formulario->armarCampo('p1.esal_ren', 'X');
                $formulario->armarCampo('p1.esal_anio_ren', $_SESSION ["formulario"] ["datos"] ["anodatos"]);
                $formulario->armarCampo('p1.esal_num_ins', $_SESSION ["formulario"] ["datos"] ["matricula"]);
            }
        }

        if ($_SESSION ["formulario"] ["tipotramite"] == 'matriculamercantil' ||
                $_SESSION ["formulario"] ["tipotramite"] == 'matriculapnat' ||
                $_SESSION ["formulario"] ["tipotramite"] == 'matriculapjur' ||
                $_SESSION ["formulario"] ["tipotramite"] == 'constitucionpjur' ||
                $_SESSION ["formulario"] ["tipotramite"] == 'compraventa' ||
                $_SESSION ["formulario"] ["tipotramite"] == 'inscripciondocumentos'
        ) {
            if (substr($_SESSION ["formulario"] ["datos"] ["matricula"], 0, 1) != 'S' &&
            $_SESSION ["formulario"] ["datos"] ["matricula"] != 'NUEVAESA') {
                if ($_SESSION["tramite"]["tipomatricula"] == 'cambidom') {
                    $formulario->armarCampo('p1.rm_tras', 'X');
                } else {
                    $formulario->armarCampo('p1.rm_mat', 'X');
                }
                // $formulario->armarCampo('p1.rm_anio_ren', $_SESSION ["formulario"] ["datos"] ["anodatos"]);
                if (substr($_SESSION ["formulario"] ["datos"] ["matricula"],0,5) != 'NUEVA') {
                    $formulario->armarCampo('p1.rm_num_mat', $_SESSION ["formulario"] ["datos"] ["matricula"]);
                }
            } else {
                if ($_SESSION["tramite"]["tipomatricula"] == 'cambidom') {
                    $formulario->armarCampo('p1.esal_tras', 'X');
                } else {
                    $formulario->armarCampo('p1.esal_ins', 'X');
                }
                // $formulario->armarCampo('p1.esal_anio_ren', $_SESSION ["formulario"] ["datos"] ["anodatos"]);
                if (substr($_SESSION ["formulario"] ["datos"] ["matricula"],0,5) != 'NUEVA') {
                    $formulario->armarCampo('p1.esal_num_ins', $_SESSION ["formulario"] ["datos"] ["matricula"]);
                }
            }
        }

        // 2017-01-04 : JINT
        if ($_SESSION ["formulario"] ["tipotramite"] == 'constitucionesadl') {
            if ($_SESSION["tramite"]["tipomatricula"] == 'cambidom') {
                $formulario->armarCampo('p1.esal_tras', 'X');
            } else {
                $formulario->armarCampo('p1.esal_ins', 'X');
            }
            // $formulario->armarCampo('p1.esal_anio_ren', $_SESSION ["formulario"] ["datos"] ["anodatos"]);
            if (substr($_SESSION ["formulario"] ["datos"] ["matricula"],0,5) != 'NUEVA') {
                $formulario->armarCampo('p1.esal_num_ins', $_SESSION ["formulario"] ["datos"] ["matricula"]);
            }
        }


        // Identificacion
        if (trim($_SESSION ["formulario"] ["datos"] ["ape1"]) == '') {

            $formulario->armarCampo('p1.raz_soc', utf8_decode($_SESSION ["formulario"] ["datos"] ["nombre"]));
            $formulario->armarCampo('p1.sig', $_SESSION ["formulario"] ["datos"] ["sigla"]);
        }

        $formulario->armarCampo('p1.ape1', utf8_decode($_SESSION ["formulario"] ["datos"] ["ape1"]));
        $formulario->armarCampo('p1.ape2', utf8_decode($_SESSION ["formulario"] ["datos"] ["ape2"]));
        $formulario->armarCampo('p1.nom', utf8_decode($_SESSION ["formulario"] ["datos"] ["nom1"] . ' ' . $_SESSION ["formulario"] ["datos"] ["nom2"]));

        if ($_SESSION ["formulario"] ["datos"] ["organizacion"] == '01') {

            $formulario->armarCampo('p1.ide', $_SESSION ["formulario"] ["datos"] ["identificacion"]);

            switch ($_SESSION ["formulario"] ["datos"] ["tipoidentificacion"]) {
                case "1" :
                    $formulario->armarCampo('p1.cc', 'X');
                    break;
                case "3" :
                    $formulario->armarCampo('p1.ce', 'X');
                    break;
                case "4" :
                    $formulario->armarCampo('p1.ti', 'X');
                    break;
                case "5" :
                    $formulario->armarCampo('p1.pas', 'X');
                    break;
            }
        }

        $formulario->armarCampo('p1.nit', substr($_SESSION ["formulario"] ["datos"] ["nit"], 0, - 1));
        $formulario->armarCampo('p1.dv', substr($_SESSION ["formulario"] ["datos"] ["nit"], - 1, 1));

        // Ubicacion y datos generales
        $formulario->armarCampo('p1.dom_dir', utf8_decode($_SESSION ["formulario"] ["datos"] ["dircom"]));
        $formulario->armarCampo('p1.dom_mun', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["muncom"] . "'","ciudad")));
        $formulario->armarCampo('p1.dom_dep', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["muncom"] . "'","departamento")));
        $formulario->armarCampo('p1.dom_pais', 'COLOMBIA');

        if (ltrim($_SESSION ["formulario"] ["datos"] ["barriocom"], "0") != '') {

            // $formulario->armarCampo('p1.dom_bar', retornarNombreBarrio2($_SESSION ["formulario"] ["datos"] ["muncom"], $_SESSION ["formulario"] ["datos"] ["barriocom"]));
            $formulario->armarCampo('p1.dom_bar', retornarRegistroMysqli2($dbx,'mreg_barriosmuni',"idmunicipio='" . $_SESSION ["formulario"] ["datos"] ["muncom"] . "' and idbarrio='" . $_SESSION ["formulario"] ["datos"] ["barriocom"] . "'","nombre"));
        }

        $formulario->armarCampo('p1.dom_tel1', $_SESSION ["formulario"] ["datos"] ["telcom1"]);
        $formulario->armarCampo('p1.dom_tel2', $_SESSION ["formulario"] ["datos"] ["telcom2"]);
        $formulario->armarCampo('p1.dom_tel3', $_SESSION ["formulario"] ["datos"] ["celcom"]);
        $formulario->armarCampo('p1.dom_email', $_SESSION ["formulario"] ["datos"] ["emailcom"]);
        $formulario->armarCampo('p1.dom_fax', $_SESSION ["formulario"] ["datos"] ["faxcom"]);
        $formulario->armarCampo('p1.not_dir', utf8_decode($_SESSION ["formulario"] ["datos"] ["dirnot"]));
        $formulario->armarCampo('p1.not_mun', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["munnot"] . "'","ciudad")));
        $formulario->armarCampo('p1.not_dep', utf8_decode(retornarRegistroMysqli2($dbx,'bas_municipios',"codigomunicipio='" . $_SESSION ["formulario"] ["datos"] ["munnot"] . "'","departamento")));
        $formulario->armarCampo('p1.not_pais', 'COLOMBIA');


        if (ltrim($_SESSION ["formulario"] ["datos"] ["barrionot"], "0") != '') {

            // $formulario->armarCampo('p1.not_bar', retornarNombreBarrio2($_SESSION ["formulario"] ["datos"] ["munnot"], $_SESSION ["formulario"] ["datos"] ["barrionot"]));
            $formulario->armarCampo('p1.not_bar', retornarRegistroMysqli2($dbx,'mreg_barriosmuni',"idmunicipio='" . $_SESSION ["formulario"] ["datos"] ["munnot"] . "' and idbarrio='" . $_SESSION ["formulario"] ["datos"] ["barrionot"] . "'","nombre"));
        }

        $formulario->armarCampo('p1.not_tel1', $_SESSION ["formulario"] ["datos"] ["telnot"]);
        $formulario->armarCampo('p1.not_tel2', $_SESSION ["formulario"] ["datos"] ["telnot2"]);
        $formulario->armarCampo('p1.not_tel3', $_SESSION ["formulario"] ["datos"] ["celnot"]);
        $formulario->armarCampo('p1.not_email', $_SESSION ["formulario"] ["datos"] ["emailnot"]);
        $formulario->armarCampo('p1.not_fax', $_SESSION ["formulario"] ["datos"] ["faxnot"]);

        // NOTIFICACION POR MAIL
        switch (substr($_SESSION ["formulario"] ["datos"] ["ctrmen"], 0, 1)) {
            case "S" :
                $formulario->armarCampo('p1.not_mail_si', 'X');
                break;
            case "N" :
                $formulario->armarCampo('p1.not_mail_no', 'X');
                break;
        }

        // NOTIFICACION POR CELULAR
        switch (substr($_SESSION ["formulario"] ["datos"] ["ctrmennot"], 0, 1)) {
            case "S" :
                $formulario->armarCampo('p1.not_cel_si', 'X');
                break;
            case "N" :
                $formulario->armarCampo('p1.not_cel_no', 'X');
                break;
        }

        switch ($_SESSION ["formulario"] ["datos"] ["ctrubi"]) {
            case "1" :
                $formulario->armarCampo('p1.loc', 'X');
                break;
            case "2" :
                $formulario->armarCampo('p1.ofi', 'X');
                break;
            case "3" :
                $formulario->armarCampo('p1.loc_ofi', 'X');
                break;
            case "4" :
                $formulario->armarCampo('p1.fab', 'X');
                break;
            case "5" :
                $formulario->armarCampo('p1.viv', 'X');
                break;
            case "6" :
                $formulario->armarCampo('p1.fin', 'X');
                break;
        }

        // Actividad economica
        $formulario->armarCampo('p1.ciiu1', substr($_SESSION ["formulario"] ["datos"] ["ciius"] [1], 1, 4));
        $formulario->armarCampo('p1.ciiu2', substr($_SESSION ["formulario"] ["datos"] ["ciius"] [2], 1, 4));
        $formulario->armarCampo('p1.ciiu3', substr($_SESSION ["formulario"] ["datos"] ["ciius"] [3], 1, 4));
        $formulario->armarCampo('p1.ciiu4', substr($_SESSION ["formulario"] ["datos"] ["ciius"] [4], 1, 4));

        // Informacion financiera
        if ($prediligenciado != 'si') {
            if (ACTIVAR_CIRCULAR_002_2016 == 'SI1') {
                $formulario->armarCampo('p1.actcte', number_format($_SESSION ["formulario"] ["datos"] ["actcte"], 2));
                $formulario->armarCampo('p1.actnocte', number_format($_SESSION ["formulario"] ["datos"] ["actnocte"], 2));
                $formulario->armarCampo('p1.acttot', number_format($_SESSION ["formulario"] ["datos"] ["acttot"], 2));
                $formulario->armarCampo('p1.pascte', number_format($_SESSION ["formulario"] ["datos"] ["pascte"], 2));
                $formulario->armarCampo('p1.paslar', number_format($_SESSION ["formulario"] ["datos"] ["paslar"], 2));
                $formulario->armarCampo('p1.pastot', number_format($_SESSION ["formulario"] ["datos"] ["pastot"], 2));
                $formulario->armarCampo('p1.pattot', number_format($_SESSION ["formulario"] ["datos"] ["pattot"], 2));
                $formulario->armarCampo('p1.paspat', number_format($_SESSION ["formulario"] ["datos"] ["paspat"], 2));
                if ($_SESSION["formulario"]["datos"]["organizacion"] == '12' || $_SESSION["formulario"]["datos"]["organizacion"] == '14') {
                    if ($_SESSION["formulario"]["datos"]["categoria"] == '1') {
                        $formulario->armarCampo('p1.balsoc', number_format($_SESSION ["formulario"] ["datos"] ["balsoc"], 2));
                    }
                }
                $formulario->armarCampo('p1.ingope', number_format($_SESSION ["formulario"] ["datos"] ["ingope"], 2));
                $formulario->armarCampo('p1.ingnoope', number_format($_SESSION ["formulario"] ["datos"] ["ingnoope"], 2));
                $formulario->armarCampo('p1.cosven', number_format($_SESSION ["formulario"] ["datos"] ["cosven"], 2));
                // $formulario->armarCampo('p1.gasope', number_format($_SESSION ["formulario"] ["datos"] ["gasope"], 2));
                // $formulario->armarCampo('p1.gasnoope', number_format($_SESSION ["formulario"] ["datos"] ["gasnoope"], 2));

                $formulario->armarCampo('p1.gasope', number_format($_SESSION ["formulario"] ["datos"] ["gtoven"], 2));
                $formulario->armarCampo('p1.gasnoope', number_format($_SESSION ["formulario"] ["datos"] ["gtoadm"], 2));

                $formulario->armarCampo('p1.gasimp', number_format($_SESSION ["formulario"] ["datos"] ["gasimp"], 2));
                $formulario->armarCampo('p1.utiope', number_format($_SESSION ["formulario"] ["datos"] ["utiope"], 2));
                $formulario->armarCampo('p1.utinet', number_format($_SESSION ["formulario"] ["datos"] ["utinet"], 2));
            } else {
                $formulario->armarCampo('p1.act_cor', number_format($_SESSION ["formulario"] ["datos"] ["actcte"], 2));
                $formulario->armarCampo('p1.act_fn', number_format($_SESSION ["formulario"] ["datos"] ["fijnet"], 2));
                $formulario->armarCampo('p1.act_otr', number_format($_SESSION ["formulario"] ["datos"] ["actotr"], 2));
                $formulario->armarCampo('p1.act_val', number_format($_SESSION ["formulario"] ["datos"] ["actval"], 2));
                $formulario->armarCampo('p1.pas_cor', number_format($_SESSION ["formulario"] ["datos"] ["pascte"], 2));
                $formulario->armarCampo('p1.pas_lp', number_format($_SESSION ["formulario"] ["datos"] ["paslar"], 2));
                $formulario->armarCampo('p1.pas_tot', number_format($_SESSION ["formulario"] ["datos"] ["pastot"], 2));
                $formulario->armarCampo('p1.pat_pn', number_format($_SESSION ["formulario"] ["datos"] ["pattot"], 2));
                $formulario->armarCampo('p1.pas_pat', number_format($_SESSION ["formulario"] ["datos"] ["paspat"], 2));
                $formulario->armarCampo('p1.ing_ope', number_format($_SESSION ["formulario"] ["datos"] ["ingope"], 2));
                $formulario->armarCampo('p1.ing_nope', number_format($_SESSION ["formulario"] ["datos"] ["ingnoope"], 2));
                $formulario->armarCampo('p1.gas_ope', number_format($_SESSION ["formulario"] ["datos"] ["gtoven"], 2));
                $formulario->armarCampo('p1.gas_nope', number_format($_SESSION ["formulario"] ["datos"] ["gtoadm"], 2));
                $formulario->armarCampo('p1.cos_ven', number_format($_SESSION ["formulario"] ["datos"] ["cosven"], 2));
                $formulario->armarCampo('p1.upo', number_format($_SESSION ["formulario"] ["datos"] ["utiope"], 2));
                $formulario->armarCampo('p1.upn', number_format($_SESSION ["formulario"] ["datos"] ["utinet"], 2));
                $formulario->armarCampo('p1.act_tot', number_format($_SESSION ["formulario"] ["datos"] ["acttot"], 2));
            }
        }


        //Ajuste 26 Agosto 2014
        //cat=1 o org=01
        if (($_SESSION ["formulario"] ["datos"] ["organizacion"] == '01') || ($_SESSION ["formulario"] ["datos"] ["categoria"] == '1')) {
            if (($_SESSION ["formulario"] ["datos"]["impexp"] == '1') || ($_SESSION ["formulario"] ["datos"] ["impexp"] == '3')) {
                $formulario->armarCampo('p1.impo', 'X');
            }
            if (($_SESSION ["formulario"] ["datos"]["impexp"] == '2') || ($_SESSION ["formulario"] ["datos"] ["impexp"] == '3')) {
                $formulario->armarCampo('p1.expo', 'X');
            }
        }


        if ($prediligenciado != 'si') {
            if (ltrim($_SESSION ["formulario"] ["datos"] ["personal"], "0") == '')
                $_SESSION ["formulario"] ["datos"] ["personal"] = 0;
            $formulario->armarCampo('p1.num_trab', number_format($_SESSION ["formulario"] ["datos"] ["personal"], 2));
            $formulario->armarCampo('p1.por_trab', $_SESSION ["formulario"] ["datos"] ["personaltemp"]);
        }

        //Ajuste 26 Agosto 2014
        //org=09 y cat=1
        if (($_SESSION ["formulario"] ["datos"] ["organizacion"] == '09') && ($_SESSION ["formulario"] ["datos"] ["categoria"] == '1')) {
            if (substr($_SESSION ["formulario"] ["datos"] ["matricula"], 0, 1) != 'S' && $_SESSION ["formulario"] ["datos"] ["matricula"] != 'NUEVAESA') {
                $formulario->armarCampo('p1.apor_lab1', number_format($_SESSION ["formulario"] ["datos"] ["apolab"], 2));
                $formulario->armarCampo('p1.apor_act1', number_format($_SESSION ["formulario"] ["datos"] ["apoact"], 2));
                $formulario->armarCampo('p1.apor_adic1', number_format($_SESSION ["formulario"] ["datos"] ["apolabadi"], 2));
                $formulario->armarCampo('p1.apor_din1', number_format($_SESSION ["formulario"] ["datos"] ["apodin"], 2));
                $tot_apo = $_SESSION ["formulario"] ["datos"] ["apolab"] +
                        $_SESSION ["formulario"] ["datos"] ["apoact"] +
                        $_SESSION ["formulario"] ["datos"] ["apolabadi"] +
                        $_SESSION ["formulario"] ["datos"] ["apodin"];
                if ($tot_apo != 0) {
                    $formulario->armarCampo('p1.apor_tot', number_format($tot_apo, 2));
                    $formulario->armarCampo('p1.apor_lab2', number_format((($_SESSION ["formulario"] ["datos"] ["apolab"] * 100) / $tot_apo), 2));
                    $formulario->armarCampo('p1.apor_act2', number_format((($_SESSION ["formulario"] ["datos"] ["apoact"] * 100) / $tot_apo), 2));
                    $formulario->armarCampo('p1.apor_adic2', number_format((($_SESSION ["formulario"] ["datos"] ["apolabadi"] * 100) / $tot_apo), 2));
                    $formulario->armarCampo('p1.apor_din2', number_format((($_SESSION ["formulario"] ["datos"] ["apodin"] * 100) / $tot_apo), 2));
                }
            }
        }



        //Ajuste 26 Agosto 2014
        //cat=1 para todas las organizaciones
        if (($_SESSION ["formulario"] ["datos"] ["categoria"] == '1') && (ltrim($_SESSION ["formulario"] ["datos"] ["fechaconstitucion"], "0") != '')) {
            $formulario->armarCampo('p1.anio_cons', substr($_SESSION ["formulario"] ["datos"] ["fechaconstitucion"], 0, 4));
            $formulario->armarCampo('p1.mes_cons', substr($_SESSION ["formulario"] ["datos"] ["fechaconstitucion"], 4, 2));
            $formulario->armarCampo('p1.dia_cons', substr($_SESSION ["formulario"] ["datos"] ["fechaconstitucion"], 6, 2));
        }
        if (($_SESSION ["formulario"] ["datos"] ["categoria"] == '1') && (ltrim($_SESSION ["formulario"] ["datos"] ["fechavencimiento"], "0") != '')) {
            $formulario->armarCampo('p1.anio_fin', substr($_SESSION ["formulario"] ["datos"] ["fechavencimiento"], 0, 4));
            $formulario->armarCampo('p1.mes_fin', substr($_SESSION ["formulario"] ["datos"] ["fechavencimiento"], 4, 2));
            $formulario->armarCampo('p1.dia_fin', substr($_SESSION ["formulario"] ["datos"] ["fechavencimiento"], 6, 2));
        }


        // Composicion del capital
        //Ajuste 26 Agosto 2014
        //Cuando cat=1 y org!=12 o 14
        if ($_SESSION ["formulario"] ["datos"] ["organizacion"] > '02' && $_SESSION ["formulario"] ["datos"] ["categoria"] == '1') {
            if (floatval($_SESSION ["formulario"] ["datos"] ["cap_porcnaltot"]) != 0) {
                $formulario->armarCampo('p1.cap_nal', $_SESSION ["formulario"] ["datos"] ["cap_porcnaltot"]);
            }
            if (floatval($_SESSION ["formulario"] ["datos"] ["cap_porcnalpub"]) != 0) {
                $formulario->armarCampo('p1.cap_nal_pub', $_SESSION ["formulario"] ["datos"] ["cap_porcnalpub"]);
            }
            if (floatval($_SESSION ["formulario"] ["datos"] ["cap_porcnalpri"]) != 0) {
                $formulario->armarCampo('p1.cap_nal_pri', $_SESSION ["formulario"] ["datos"] ["cap_porcnalpri"]);
            }
            if (floatval($_SESSION ["formulario"] ["datos"] ["cap_porcexttot"]) != 0) {
                $formulario->armarCampo('p1.cap_ext', $_SESSION ["formulario"] ["datos"] ["cap_porcexttot"]);
            }
            if (floatval($_SESSION ["formulario"] ["datos"] ["cap_porcextpub"]) != 0) {
                $formulario->armarCampo('p1.cap_ext_pub', $_SESSION ["formulario"] ["datos"] ["cap_porcextpub"]);
            }
            if (floatval($_SESSION ["formulario"] ["datos"] ["cap_porcextpri"]) != 0) {
                $formulario->armarCampo('p1.cap_ext_pri', $_SESSION ["formulario"] ["datos"] ["cap_porcextpri"]);
            }
        }



        // Estado de la empresa
        if (($_SESSION ["formulario"] ["datos"] ["organizacion"] != '01') && (($_SESSION ["formulario"] ["datos"] ["categoria"] == '0') || ($_SESSION ["formulario"] ["datos"] ["categoria"] == '1'))) {
            if ($_SESSION ["formulario"] ["datos"] ["estadoactiva"] != '') {
                $formulario->armarCampo('p1.est_01', 'X');
            }
            if ($_SESSION ["formulario"] ["datos"] ["estadopreoperativa"] != '') {
                $formulario->armarCampo('p1.est_02', 'X');
            }
            if ($_SESSION ["formulario"] ["datos"] ["estadoconcordato"] != '') {
                $formulario->armarCampo('p1.est_03', 'X');
            }
            if ($_SESSION ["formulario"] ["datos"] ["estadointervenida"] != '') {
                $formulario->armarCampo('p1.est_04', 'X');
            }
            if ($_SESSION ["formulario"] ["datos"] ["estadodisuelta"] != '') {
                $formulario->armarCampo('p1.est_05', 'X');
            }
            if ($_SESSION ["formulario"] ["datos"] ["estadoreestructuracion"] != '') {
                $formulario->armarCampo('p1.est_06', 'X');
            }
        }

        switch (strtoupper($_SESSION ["formulario"] ["datos"] ["emprendedor28"])) {
            case "S" :
                $formulario->armarCampo('p1.jov_si', 'X');
                $formulario->armarCampo('p1.jov_par', $_SESSION ["formulario"] ["datos"] ["pemprendedor28"]);
                break;
            case "N" :
                $formulario->armarCampo('p1.jov_no', 'X');
                break;
        }
    }

    $formulario->agregarPagina(2, 1);

    if ($tipoimpresion != 'vacio') {

        switch ($_SESSION ["formulario"] ["datos"] ["organizacion"]) {
            case "01" :
                $formulario->armarCampo('p2.org_11', 'X');
                break;
            case "03" :
                $formulario->armarCampo('p2.org_04', 'X');
                break;
            case "04" :
                $formulario->armarCampo('p2.org_05', 'X');
                break;
            case "05" :
                $formulario->armarCampo('p2.org_01', 'X');
                break;
            case "06" :
                $formulario->armarCampo('p2.org_02', 'X');
                break;
            case "07" :
                $formulario->armarCampo('p2.org_03', 'X');
                break;
            case "08" :
                $formulario->armarCampo('p2.org_07', 'X');
                break;
            case "09" :
                $formulario->armarCampo('p2.org_12.2', 'X');
                break;
            case "11" :
                $formulario->armarCampo('p2.org_09', 'X');
                break;
            case "12" :
                $formulario->armarCampo('p2.org_13', 'X');
                break;
            case "14" :
                $formulario->armarCampo('p2.org_12', 'X');

                switch ($_SESSION ["formulario"] ["datos"] ["claseespesadl"]) {

                    case "60" :
                        $formulario->armarCampo('p2.org_veed', 'X');
                        break;
                    case "62" :
                        $formulario->armarCampo('p2.org_veed', 'X');
                        break;
                    case "61" :
                        $formulario->armarCampo('p2.org_ent_ext', 'X');
                        break;
                }

                switch ($_SESSION ["formulario"] ["datos"] ["clasegenesadl"]) {

                    case "01" :
                        $formulario->armarCampo('p2.org_12.1', 'X');
                        break;
                    case "02" :
                        $formulario->armarCampo('p2.org_12.1', 'X');
                        break;
                    case "03" :
                        $formulario->armarCampo('p2.org_12.3', 'X');
                        break;
                    case "04" :
                        $formulario->armarCampo('p2.org_12.4', 'X');
                        break;
                    case "05" :
                        $formulario->armarCampo('p2.org_12.5', 'X');
                        break;
                    case "06" :
                        $formulario->armarCampo('p2.org_12.6', 'X');
                        break;
                    case "07" :
                        $formulario->armarCampo('p2.org_12.7', 'X');
                        break;
                    case "08" :
                        $formulario->armarCampo('p2.org_12.8', 'X');
                        break;
                    case "09" :
                        $formulario->armarCampo('p2.org_12.9', 'X');
                        break;
                    case "10" :
                        $formulario->armarCampo('p2.org_12.10', 'X');
                        break;
                }

                break;
            case "15" :
                $formulario->armarCampo('p2.org_08', 'X');
                break;
            case "16" :
                $formulario->armarCampo('p2.org_14', 'X');
                break;
            case "99" :
                $formulario->armarCampo('p2.org_99', 'X');
                break;
        }

        if ($_SESSION ["formulario"] ["datos"] ["cntestab01"] != 0)
            $formulario->armarCampo('p2.num_agro', $_SESSION ["formulario"] ["datos"] ["cntestab01"]);
        if ($_SESSION ["formulario"] ["datos"] ["cntestab02"] != 0)
            $formulario->armarCampo('p2.num_mine', $_SESSION ["formulario"] ["datos"] ["cntestab02"]);
        if ($_SESSION ["formulario"] ["datos"] ["cntestab03"] != 0)
            $formulario->armarCampo('p2.num_manu', $_SESSION ["formulario"] ["datos"] ["cntestab03"]);
        if ($_SESSION ["formulario"] ["datos"] ["cntestab04"] != 0)
            $formulario->armarCampo('p2.num_serv', $_SESSION ["formulario"] ["datos"] ["cntestab04"]);
        if ($_SESSION ["formulario"] ["datos"] ["cntestab05"] != 0)
            $formulario->armarCampo('p2.num_cons', $_SESSION ["formulario"] ["datos"] ["cntestab05"]);
        if ($_SESSION ["formulario"] ["datos"] ["cntestab06"] != 0)
            $formulario->armarCampo('p2.num_come', $_SESSION ["formulario"] ["datos"] ["cntestab06"]);
        if ($_SESSION ["formulario"] ["datos"] ["cntestab07"] != 0)
            $formulario->armarCampo('p2.num_rest', $_SESSION ["formulario"] ["datos"] ["cntestab07"]);
        if ($_SESSION ["formulario"] ["datos"] ["cntestab08"] != 0)
            $formulario->armarCampo('p2.num_tras', $_SESSION ["formulario"] ["datos"] ["cntestab08"]);
        if ($_SESSION ["formulario"] ["datos"] ["cntestab09"] != 0)
            $formulario->armarCampo('p2.num_comu', $_SESSION ["formulario"] ["datos"] ["cntestab09"]);
        if ($_SESSION ["formulario"] ["datos"] ["cntestab10"] != 0)
            $formulario->armarCampo('p2.num_fina', $_SESSION ["formulario"] ["datos"] ["cntestab10"]);
        if ($_SESSION ["formulario"] ["datos"] ["cntestab11"] != 0)
            $formulario->armarCampo('p2.num_serv_com', $_SESSION ["formulario"] ["datos"] ["cntestab11"]);

        $formulario->armarCampo('p2.cred_nom1', $_SESSION ["formulario"] ["datos"] ["refcrenom1"]);
        $formulario->armarCampo('p2.cred_ofi1', $_SESSION ["formulario"] ["datos"] ["refcreofi1"]);

        $formulario->armarCampo('p2.cred_nom2', $_SESSION ["formulario"] ["datos"] ["refcrenom2"]);
        $formulario->armarCampo('p2.cred_ofi2', $_SESSION ["formulario"] ["datos"] ["refcreofi2"]);

        $formulario->armarCampo('p2.ref_dir1', $_SESSION ["formulario"] ["datos"] ["refcomdir1"]);
        $formulario->armarCampo('p2.ref_nom1', $_SESSION ["formulario"] ["datos"] ["refcomnom1"]);
        $formulario->armarCampo('p2.ref_tel1', $_SESSION ["formulario"] ["datos"] ["refcomtel1"]);

        $formulario->armarCampo('p2.ref_dir2', $_SESSION ["formulario"] ["datos"] ["refcomdir2"]);
        $formulario->armarCampo('p2.ref_nom2', $_SESSION ["formulario"] ["datos"] ["refcomnom2"]);
        $formulario->armarCampo('p2.ref_tel2', $_SESSION ["formulario"] ["datos"] ["refcomtel2"]);

        $iBienes = 0;
        if (!empty($_SESSION ["formulario"] ["datos"] ["bienes"])) {
            foreach ($_SESSION ["formulario"] ["datos"] ["bienes"] as $bien) {
                $iBienes ++;
                switch ($iBienes) {
                    case 1 :
                    case 2 :
                    case 3 :
                    case 4 :
                        $formulario->armarCampo('p2.bien' . $iBienes . '_mat', $bien ["matinmo"]);
                        $formulario->armarCampo('p2.bien' . $iBienes . '_dir', $bien ["dir"]);
                        $formulario->armarCampo('p2.bien' . $iBienes . '_bar', $bien ["barrio"]);
                        $formulario->armarCampo('p2.bien' . $iBienes . '_mun', $bien ["muni"]);
                        $formulario->armarCampo('p2.bien' . $iBienes . '_dep', $bien ["dpto"]);
                        $formulario->armarCampo('p2.bien' . $iBienes . '_pais', $bien ["pais"]);
                        break;
                }
            }
        }

        if (($_SESSION ["formulario"] ["datos"] ["organizacion"] == '12') || ($_SESSION ["formulario"] ["datos"] ["organizacion"] == '14')) {

            if (ltrim($_SESSION ["formulario"] ["datos"] ["vigcontrol"], "0") != '') {
                
                // $formulario->armarCampo('p2.ent_vig', retornarNombreTablasSirep('43', $_SESSION ["formulario"] ["datos"] ["vigcontrol"]));
                $formulario->armarCampo('p2.ent_vig', retornarRegistroMysqli2($dbx,"mreg_tablassirep","idtabla='43' and idcodigo='" . $_SESSION ["formulario"] ["datos"] ["vigcontrol"] . "'","descripcion"));
                
            }
        }

        if ($numliq != 0) {

            if ($_SESSION ["formulario"] ["datos"] ["organizacion"] == '01') {

                $nombre_completo = $_SESSION ["formulario"] ["datos"] ["ape1"] . ' ' . $_SESSION ["formulario"] ["datos"] ["ape2"] . ' ' . $_SESSION ["formulario"] ["datos"] ["nom1"] . ' ' . $_SESSION ["formulario"] ["datos"] ["nom2"];

                $formulario->armarCampo('p2.firm_nom', utf8_decode($nombre_completo));
                $formulario->armarCampo('p2.firm_ide', $_SESSION ["formulario"] ["datos"] ["identificacion"]);

                switch ($_SESSION ["formulario"] ["datos"] ["tipoidentificacion"]) {
                    case "1" :
                        $formulario->armarCampo('p2.firm_cc', 'X');
                        break;
                    case "3" :
                        $formulario->armarCampo('p2.firm_ce', 'X');
                        break;
                    case "4" :
                        $formulario->armarCampo('p2.firm_ti', 'X');
                        break;
                    case "5" :
                        $formulario->armarCampo('p2.firm_pas', 'X');
                        break;
                }
            } else {
                if (isset($_SESSION ["formulario"] ["datos"] ["propietarios"])) {
                    if (isset($_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["nombrepropietario"])) {
                        $formulario->armarCampo('p2.firm_nom', utf8_decode($_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["nombrepropietario"]));
                        $formulario->armarCampo('p2.firm_ide', $_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["identificacionpropietario"]);

                        switch ($_SESSION ["formulario"] ["datos"] ["propietarios"] [1] ["idtipoidentificacionpropietario"]) {
                            case "1" :
                                $formulario->armarCampo('p2.firm_cc', 'X');
                                break;
                            case "3" :
                                $formulario->armarCampo('p2.firm_ce', 'X');
                                break;
                            case "4" :
                                $formulario->armarCampo('p2.firm_ti', 'X');
                                break;
                            case "5" :
                                $formulario->armarCampo('p2.firm_pas', 'X');
                                break;
                        }
                    }
                }
            }
        }
        $formulario->armarCampo('p2.firma_elec', utf8_decode($txtFirmaElectronica));
    }
    /*
     * $arrFir = array (); $arrTem = retornarRegistros ('sms_firmados',"idliquidacion=" . $numliq); if ($arrTem && !empty ($arrTem)) { foreach ($arrTem as $t) { if ($t["estado"]=='FI') { $arrFir = $t; } } } if (!empty($arrFir)) { $formulario->armarCampo ( 'p2.firm_nom','Firmado electr�nicamente por ' . $arrFir["nombre"] . " el " . $arrFir["fecha"] . ' a las ' . $arrFir["hora"] . ', c�digo de firmado ' . $arrFir["pkgsms"]); }
     */

    $fechaHora = date("Ymd") . date("His");
    if ($tipoimpresion != 'vacio') {
        $name = PATH_ABSOLUTO_SITIO . "/tmp/" . session_id() . "-Formulario-" . $_SESSION ["formulario"] ["datos"] ["matricula"] . "-" . $fechaHora . ".pdf";
        $name1 = session_id() . "-Formulario-" . $_SESSION ["formulario"] ["datos"] ["matricula"] . '-' . $fechaHora . ".pdf";
        $formulario->Output($name, "F");
    } else {
        $name = PATH_ABSOLUTO_SITIO . "/tmp/" . session_id() . "-FormularioPrincipal-" . $fechaHora . ".pdf";
        $name1 = session_id() . "-FormularioPrincipal-" . $fechaHora . ".pdf";
        $formulario->Output($name, "F");
    }
    return $name1;
}
