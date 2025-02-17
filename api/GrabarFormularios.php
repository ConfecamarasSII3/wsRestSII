<?php

/*
 * Se recibe json con la siguiente información
 * 
 */

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait GrabarFormularios {

    public function grabarFormularioMercantil(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        // Verifica método de recepcion de parámetros
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);
        $api->validarParametro("ip", false);
        $api->validarParametro("sistemaorigen", false);

        //
        $api->validarParametro("idliquidacion", true);
        $api->validarParametro("sectransaccion", false);
        $api->validarParametro("cc", false);
        $api->validarParametro("matricula", true);
        $api->validarParametro("organizacion", false);
        $api->validarParametro("categoria", false);
        $api->validarParametro("anorenovar", false);

        //


        $_SESSION["formulario"] = array();
        $_SESSION["formulario"]["idliquidacion"] = $_SESSION["entrada"]["idliquidacion"];
        $_SESSION["formulario"]["tipotramite"] = '';
        $_SESSION["formulario"]["reliquidacion"] = '';
        $_SESSION["formulario"]["sectransaccion"] = $_SESSION["entrada"]["sectransaccion"];
        $_SESSION["formulario"]["cc"] = $_SESSION["entrada"]["cc"];
        $_SESSION["formulario"]["matricula"] = $_SESSION["entrada"]["matricula"];
        $_SESSION["formulario"]["organizacion"] = $_SESSION["entrada"]["organizacion"];
        $_SESSION["formulario"]["categoria"] = $_SESSION["entrada"]["categoria"];
        $_SESSION["formulario"]["anorenovar"] = $_SESSION["entrada"]["anorenovar"];


        //
        if (!$api->validarToken('grabarFormularioMercantil', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Crea la conexión con la BD
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        // ********************************************************************** //
        // Recupera liquidacion
        // ********************************************************************** // 
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"]);
        if ($_SESSION["tramite"] === false || empty($_SESSION["tramite"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $_SESSION["formulario"]["tipotramite"] = $_SESSION["tramite"]["tipotramite"];
        $_SESSION["formulario"]["reliquidacion"] = $_SESSION["tramite"]["reliquidacion"];

        // ********************************************************************** //
        // Recupera datos del expediente
        // ********************************************************************** //           
        $exps = retornarRegistroMysqliApi($mysqli, "mreg_liquidaciondatos", "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"] . " and expediente='" . $_SESSION["entrada"]["matricula"] . "'");
        if ($exps === false || empty($exps)) {
            if ($_SESSION["entrada"]["matricula"] == '' ||
                    substr($_SESSION["entrada"]["matricula"], 0, 5) == 'NUEVA') {
                $_SESSION["formulario"]["datos"] = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, '');
            } else {
                $_SESSION["formulario"]["datos"] = \funcionesRegoistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["matricula"]);
            }
        } else {
            $_SESSION["formulario"]["datos"] = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $exps["xml"]);
        }
        unset($exps);



        // ********************************************************************** //
        // Valida los datos recibidos y se almacena en mreg_liquidaciondatos
        // ********************************************************************** //    

        $exclu1 = array("/", "-");
        $exclu2 = array("\"", "/", "'", ",");

        foreach ($_SESSION["entrada1"]["registros"] as $key => $valor) {

            $valor = \funcionesGenerales::restaurarEspecialesMayusculas($valor);
            switch ($key) {
                case 'nombre': $_SESSION["formulario"]["datos"]["nombre"] = strtoupper(trim((string)$valor));
                    break;
                case 'ape1': $_SESSION["formulario"]["datos"]["ape1"] = strtoupper(trim((string)$valor));
                    break;
                case 'ape2': $_SESSION["formulario"]["datos"]["ape2"] = strtoupper(trim((string)$valor));
                    break;
                case 'nom1': $_SESSION["formulario"]["datos"]["nom1"] = strtoupper(trim((string)$valor));
                    break;
                case 'nom2': $_SESSION["formulario"]["datos"]["nom2"] = strtoupper(trim((string)$valor));
                    break;
                case 'sigla': $_SESSION["formulario"]["datos"]["sigla"] = strtoupper(trim((string)$valor));
                    break;
                case 'tipoidentificacion': $_SESSION["formulario"]["datos"]["tipoidentificacion"] = $valor;
                    break;
                case 'identificacion': $_SESSION["formulario"]["datos"]["identificacion"] = ltrim((string)str_replace($exclu1, "", $valor), '0');
                    break;
                case 'idmunidoc': $_SESSION["formulario"]["datos"]["idmunidoc"] = $valor;
                    break;
                case 'fechanacimiento': $_SESSION["formulario"]["datos"]["fechanacimiento"] = str_replace($exclu1, "", $valor);
                    break;
                case 'fecexpdoc': $_SESSION["formulario"]["datos"]["fecexpdoc"] = str_replace($exclu1, "", $valor);
                    break;
                case 'paisexpdoc': $_SESSION["formulario"]["datos"]["paisexpdoc"] = $valor;
                    break;
                case 'nit': $_SESSION["formulario"]["datos"]["nit"] = soloNumeros($valor);
                    break;
                case 'admondian': $_SESSION["formulario"]["datos"]["admondian"] = $valor;
                    break;
                case 'prerut': $_SESSION["formulario"]["datos"]["prerut"] = soloNumeros($valor);
                    break;
                case 'nacionalidad': $_SESSION["formulario"]["datos"]["nacionalidad"] = strtoupper(trim((string)$valor));
                    break;
                case 'idetripaiori': $_SESSION["formulario"]["datos"]["idetripaiori"] = strtoupper(trim((string)$valor));
                    break;
                case 'paiori': $_SESSION["formulario"]["datos"]["paiori"] = strtoupper(trim((string)$valor));
                    break;
                case 'idetriextep': $_SESSION["formulario"]["datos"]["idetriextep"] = strtoupper(trim((string)$valor));
                    break;
                case 'cumplerequisitos1780': $_SESSION["formulario"]["datos"]["cumplerequisitos1780"] = strtoupper(trim((string)$valor));
                    break;
                case 'renunciabeneficios1780': $_SESSION["formulario"]["datos"]["renunciabeneficios1780"] = strtoupper(trim((string)$valor));
                    break;
                case 'cumplerequisitos1780primren': $_SESSION["formulario"]["datos"]["cumplerequisitos1780primren"] = strtoupper(trim((string)$valor));
                    break;
                case 'tipogruemp': $_SESSION["formulario"]["datos"]["tipogruemp"] = strtoupper(trim((string)$valor));
                    break;
                case 'nombregruemp': $_SESSION["formulario"]["datos"]["nombregruemp"] = strtoupper(trim((string)$valor));
                    break;
                case 'ctrderpub': $_SESSION["formulario"]["datos"]["ctrderpub"] = (trim((string)$valor));
                    break;
                case 'claseeconsoli': $_SESSION["formulario"]["datos"]["claseeconsoli"] = (trim((string)$valor));
                    break;
                case 'ctrcodcoop': $_SESSION["formulario"]["datos"]["ctrcodcoop"] = strtoupper(trim((string)$valor));
                    break;
                case 'ctrcodotras': $_SESSION["formulario"]["datos"]["ctrcodotras"] = strtoupper(trim((string)$valor));
                    break;
                case 'ctresacntasociados': $_SESSION["formulario"]["datos"]["ctresacntasociados"] = intval(trim((string)$valor));
                    break;
                case 'ctresacntmujeres': $_SESSION["formulario"]["datos"]["ctresacntmujeres"] = intval(trim((string)$valor));
                    break;
                case 'ctresacnthombres': $_SESSION["formulario"]["datos"]["ctresacnthombres"] = intval(trim((string)$valor));
                    break;
                case 'ctresapertgremio': $_SESSION["formulario"]["datos"]["ctresapertgremio"] = strtoupper(trim((string)$valor));
                    break;
                case 'ctresagremio': $_SESSION["formulario"]["datos"]["ctresagremio"] = strtoupper(trim((string)$valor));
                    break;
                case 'ctresaacredita': $_SESSION["formulario"]["datos"]["ctresaacredita"] = strtoupper(trim((string)$valor));
                    break;
                case 'ctresaivc': $_SESSION["formulario"]["datos"]["ctresaivc"] = strtoupper(trim((string)$valor));
                    break;
                case 'ctresainfoivc': $_SESSION["formulario"]["datos"]["ctresainfoivc"] = strtoupper(trim((string)$valor));
                    break;
                case 'ctresaautregistro': $_SESSION["formulario"]["datos"]["ctresaautregistro"] = strtoupper(trim((string)$valor));
                    break;
                case 'ctresaentautoriza': $_SESSION["formulario"]["datos"]["ctresaentautoriza"] = strtoupper(trim((string)$valor));
                    break;
                case 'ctresacodnat': $_SESSION["formulario"]["datos"]["ctresacodnat"] = strtoupper(trim((string)$valor));
                    break;
                case 'claseespesadl': $_SESSION["formulario"]["datos"]["claseespesadl"] = strtoupper(trim((string)$valor));
                    break;
                case 'ctresadiscap': $_SESSION["formulario"]["datos"]["ctresadiscap"] = strtoupper(trim((string)$valor));
                    break;
                case 'ctresaetnia': $_SESSION["formulario"]["datos"]["ctresaetnia"] = strtoupper(trim((string)$valor));
                    break;
                case 'ctresacualetnia': $_SESSION["formulario"]["datos"]["ctresacualetnia"] = strtoupper(trim((string)$valor));
                    break;
                case 'ctresadespvictreins': $_SESSION["formulario"]["datos"]["ctresadespvictreins"] = strtoupper(trim((string)$valor));
                    break;
                case 'ctresacualdespvictreins': $_SESSION["formulario"]["datos"]["ctresacualdespvictreins"] = strtoupper(trim((string)$valor));
                    break;
                case 'ctresaindgest': $_SESSION["formulario"]["datos"]["ctresaindgest"] = strtoupper(trim((string)$valor));
                    break;
                case 'ctresalgbti': $_SESSION["formulario"]["datos"]["ctresalgbti"] = strtoupper(trim((string)$valor));
                    break;
                case 'estadocapturado': $_SESSION["formulario"]["datos"]["estadocapturado"] = strtoupper(trim((string)$valor));
                    break;
                case 'estadocapturadootros': $_SESSION["formulario"]["datos"]["estadocapturadootros"] = strtoupper(trim((string)$valor));
                    break;
                case 'cantest': $_SESSION["formulario"]["datos"]["cantest"] = intval(trim((string)$valor));
                    break;
                case 'fechamatricula': $_SESSION["formulario"]["datos"]["fechamatricula"] = str_replace($exclu1, "", $valor);
                    break;
                case 'fecharenovacion': $_SESSION["formulario"]["datos"]["fecharenovacion"] = str_replace($exclu1, "", $valor);
                    break;
                case 'fechaconstitucion': $_SESSION["formulario"]["datos"]["fechaconstitucion"] = str_replace($exclu1, "", $valor);
                    break;
                case 'fechavencimiento': $_SESSION["formulario"]["datos"]["fechavencimiento"] = str_replace($exclu1, "", $valor);
                    break;
                case 'ultanoren': $_SESSION["formulario"]["datos"]["ultanoren"] = $valor;
                    break;
                case 'estadomatricula': $_SESSION["formulario"]["datos"]["estadomatricula"] = $valor;
                    break;
                case 'organizacion': $_SESSION["formulario"]["datos"]["organizacion"] = $valor;
                    break;
                case 'tamanoempresa': $_SESSION["formulario"]["datos"]["tamanoempresa"] = $valor;
                    break;
                case 'emprendedor28': $_SESSION["formulario"]["datos"]["emprendedor28"] = strtoupper($valor);
                    break;
                case 'vigcontrol': $_SESSION["formulario"]["datos"]["vigcontrol"] = $valor;
                    break;
                case 'pemprendedor28': $_SESSION["formulario"]["datos"]["pemprendedor28"] = doubleval($valor);
                    break;
                case 'categoria': $_SESSION["formulario"]["datos"]["categoria"] = $valor;
                    break;
                case 'fecperjur': $_SESSION["formulario"]["datos"]["fecperjur"] = str_replace($exclu1, "", $valor);
                    break;
                case 'tipopropiedad': $_SESSION["formulario"]["datos"]["tipopropiedad"] = $valor;
                    break;
                case 'tipolocal': $_SESSION["formulario"]["datos"]["tipolocal"] = $valor;
                    break;
                case 'afiliado': $_SESSION["formulario"]["datos"]["afiliado"] = $valor;
                    break;
                case 'lggr': $_SESSION["formulario"]["datos"]["lggr"] = $valor;
                    break;
                case 'nombrecomercial': $_SESSION["formulario"]["datos"]["nombrecomercial"] = strtoupper(trim((string)$valor));
                    break;
                case 'dircom': $_SESSION["formulario"]["datos"]["dircom"] = strtoupper(trim((string)$valor));
                    break;
                case 'dircom_tipovia': $_SESSION["formulario"]["datos"]["dircom_tipovia"] = strtoupper(trim((string)$valor));
                    break;
                case 'dircom_numvia': $_SESSION["formulario"]["datos"]["dircom_numvia"] = strtoupper(trim((string)$valor));
                    break;
                case 'dircom_apevia': $_SESSION["formulario"]["datos"]["dircom_apevia"] = strtoupper(trim((string)$valor));
                    break;
                case 'dircom_orivia': $_SESSION["formulario"]["datos"]["dircom_orivia"] = strtoupper(trim((string)$valor));
                    break;
                case 'dircom_numcruce': $_SESSION["formulario"]["datos"]["dircom_numcruce"] = strtoupper(trim((string)$valor));
                    break;
                case 'dircom_apecruce': $_SESSION["formulario"]["datos"]["dircom_apecruce"] = strtoupper(trim((string)$valor));
                    break;
                case 'dircom_oricruce': $_SESSION["formulario"]["datos"]["dircom_oricruce"] = strtoupper(trim((string)$valor));
                    break;
                case 'dircom_numplaca': $_SESSION["formulario"]["datos"]["dircom_numplaca"] = strtoupper(trim((string)$valor));
                    break;
                case 'dircom_complemento': $_SESSION["formulario"]["datos"]["dircom_complemento"] = strtoupper(trim((string)$valor));
                    break;
                case 'muncom': $_SESSION["formulario"]["datos"]["muncom"] = $valor;
                    break;
                case 'paicom': $_SESSION["formulario"]["datos"]["paicom"] = $valor;
                    break;
                case 'telcom1': $_SESSION["formulario"]["datos"]["telcom1"] = $valor;
                    break;
                case 'telcom2': $_SESSION["formulario"]["datos"]["telcom2"] = $valor;
                    break;
                case 'celcom': $_SESSION["formulario"]["datos"]["celcom"] = $valor;
                    break;
                case 'ctrmen': $_SESSION["formulario"]["datos"]["ctrmen"] = $valor;
                    break;
                case 'faxcom': $_SESSION["formulario"]["datos"]["faxcom"] = $valor;
                    break;
                case 'aacom': $_SESSION["formulario"]["datos"]["aacom"] = $valor;
                    break;
                case 'zonapostalcom': $_SESSION["formulario"]["datos"]["zonapostalcom"] = $valor;
                    break;
                case 'barriocom': $_SESSION["formulario"]["datos"]["barriocom"] = $valor;
                    break;
                case 'numpredial': $_SESSION["formulario"]["datos"]["numpredial"] = $valor;
                    break;
                case 'codigopostalcom': $_SESSION["formulario"]["datos"]["codigopostalcom"] = strtoupper(trim((string)$valor));
                    break;
                case 'codigozonacom': $_SESSION["formulario"]["datos"]["codigozonacom"] = strtoupper(trim((string)$valor));
                    break;
                case 'ctrubi': $_SESSION["formulario"]["datos"]["ctrubi"] = $valor;
                    break;
                case 'ctrfun': $_SESSION["formulario"]["datos"]["ctrfun"] = $valor;
                    break;
                case 'emailcom': $_SESSION["formulario"]["datos"]["emailcom"] = strtolower(trim((string)$valor));
                    break;
                case 'urlcom': $_SESSION["formulario"]["datos"]["urlcom"] = strtolower(trim((string)$valor));
                    break;
                case 'dirnot': $_SESSION["formulario"]["datos"]["dirnot"] = strtoupper(trim((string)$valor));
                    break;
                case 'dirnot_tipovia': $_SESSION["formulario"]["datos"]["dirnot_tipovia"] = strtoupper(trim((string)$valor));
                    break;
                case 'dirnot_numvia': $_SESSION["formulario"]["datos"]["dirnot_numvia"] = strtoupper(trim((string)$valor));
                    break;
                case 'dirnot_apevia': $_SESSION["formulario"]["datos"]["dirnot_apevia"] = strtoupper(trim((string)$valor));
                    break;
                case 'dirnot_orivia': $_SESSION["formulario"]["datos"]["dirnot_orivia"] = strtoupper(trim((string)$valor));
                    break;
                case 'dirnot_numcruce': $_SESSION["formulario"]["datos"]["dirnot_numcruce"] = strtoupper(trim((string)$valor));
                    break;
                case 'dirnot_apecruce': $_SESSION["formulario"]["datos"]["dirnot_apecruce"] = strtoupper(trim((string)$valor));
                    break;
                case 'dirnot_oricruce': $_SESSION["formulario"]["datos"]["dirnot_oricruce"] = strtoupper(trim((string)$valor));
                    break;
                case 'dirnot_numplaca': $_SESSION["formulario"]["datos"]["dirnot_numplaca"] = strtoupper(trim((string)$valor));
                    break;
                case 'dirnot_complemento': $_SESSION["formulario"]["datos"]["dirnot_complemento"] = strtoupper(trim((string)$valor));
                    break;
                case 'munnot': $_SESSION["formulario"]["datos"]["munnot"] = $valor;
                    break;
                case 'painot': $_SESSION["formulario"]["datos"]["painot"] = $valor;
                    break;
                case 'telnot': $_SESSION["formulario"]["datos"]["telnot"] = soloNumeros($valor);
                    break;
                case 'telnot2': $_SESSION["formulario"]["datos"]["telnot2"] = soloNumeros($valor);
                    break;
                case 'celnot': $_SESSION["formulario"]["datos"]["celnot"] = soloNumeros($valor);
                    break;
                case 'ctrmennot': $_SESSION["formulario"]["datos"]["ctrmennot"] = $valor;
                    break;
                case 'faxnot': $_SESSION["formulario"]["datos"]["faxnot"] = soloNumeros($valor);
                    break;
                case 'aanot': $_SESSION["formulario"]["datos"]["aanot"] = $valor;
                    break;
                case 'zonapostalnot': $_SESSION["formulario"]["datos"]["zonapostalnot"] = $valor;
                    break;
                case 'barrionot': $_SESSION["formulario"]["datos"]["barrionot"] = $valor;
                    break;
                case 'emailnot': $_SESSION["formulario"]["datos"]["emailnot"] = strtolower(trim((string)$valor));
                    break;
                case 'urlnot': $_SESSION["formulario"]["datos"]["urlnot"] = strtolower(trim((string)$valor));
                    break;
                case 'codigopostalnot': $_SESSION["formulario"]["datos"]["codigopostalnot"] = strtoupper(trim((string)$valor));
                    break;
                case 'codigozonanot': $_SESSION["formulario"]["datos"]["codigozonanot"] = strtoupper(trim((string)$valor));
                    break;
                case 'tiposedeadm': $_SESSION["formulario"]["datos"]["tiposedeadm"] = strtoupper(trim((string)$valor));
                    break;
                case 'desactiv': $_SESSION["formulario"]["datos"]["desactiv"] = strtoupper(trim((string)$valor));
                    break;
                case 'ciiu1': $_SESSION["formulario"]["datos"]["ciius"][1] = strtoupper(trim((string)$valor));
                    break;
                case 'ciiu2': $_SESSION["formulario"]["datos"]["ciius"][2] = strtoupper(trim((string)$valor));
                    break;
                case 'ciiu3': $_SESSION["formulario"]["datos"]["ciius"][3] = strtoupper(trim((string)$valor));
                    break;
                case 'ciiu4': $_SESSION["formulario"]["datos"]["ciius"][4] = strtoupper(trim((string)$valor));
                    break;
                case 'feciniact1': $_SESSION["formulario"]["datos"]["feciniact1"] = str_replace(array("-", "/"), "", $valor);
                    break;
                case 'feciniact2': $_SESSION["formulario"]["datos"]["feciniact2"] = str_replace(array("-", "/"), "", $valor);
                    break;
                case 'empresafamiliar': $_SESSION["formulario"]["datos"]["empresafamiliar"] = strtoupper(trim((string)$valor));
                    break;
                case 'procesosinnovacion': $_SESSION["formulario"]["datos"]["procesosinnovacion"] = strtoupper(trim((string)$valor));
                    break;
                case 'impexp': $_SESSION["formulario"]["datos"]["impexp"] = $valor;
                    break;
                case 'codaduaneros': $_SESSION["formulario"]["datos"]["codaduaneros"] = strtoupper(trim((string)$valor));
                    break;
                case 'gruponiif': $_SESSION["formulario"]["datos"]["gruponiif"] = strtoupper(trim((string)$valor));
                    break;
                case 'aportantesegsocial': $_SESSION["formulario"]["datos"]["aportantesegsocial"] = strtoupper(trim((string)$valor));
                    break;
                case 'tipoaportantesegsocial': $_SESSION["formulario"]["datos"]["tipoaportantesegsocial"] = strtoupper(trim((string)$valor));
                    break;
                case 'cap_porcnaltot': $_SESSION["formulario"]["datos"]["cap_porcnaltot"] = doubleval($valor);
                    break;
                case 'cap_porcnalpri': $_SESSION["formulario"]["datos"]["cap_porcnalpri"] = doubleval($valor);
                    break;
                case 'cap_porcnalpub': $_SESSION["formulario"]["datos"]["cap_porcnalpub"] = doubleval($valor);
                    break;
                case 'cap_porcexttot': $_SESSION["formulario"]["datos"]["cap_porcexttot"] = doubleval($valor);
                    break;
                case 'cap_porcextpri': $_SESSION["formulario"]["datos"]["cap_porcextpri"] = doubleval($valor);
                    break;
                case 'cap_porcextpub': $_SESSION["formulario"]["datos"]["cap_porcextpub"] = doubleval($valor);
                    break;
                case 'refcrenom1': $_SESSION["formulario"]["datos"]["refcrenom1"] = $valor;
                    break;
                case 'refcreofi1': $_SESSION["formulario"]["datos"]["refcreofi1"] = $valor;
                    break;
                case 'refcretel1': $_SESSION["formulario"]["datos"]["refcretel1"] = $valor;
                    break;
                case 'refcrenom2': $_SESSION["formulario"]["datos"]["refcrenom2"] = $valor;
                    break;
                case 'refcreofi2': $_SESSION["formulario"]["datos"]["refcreofi2"] = $valor;
                    break;
                case 'refcretel2': $_SESSION["formulario"]["datos"]["refcretel2"] = $valor;
                    break;
                case 'refcomnom1': $_SESSION["formulario"]["datos"]["refcomnom1"] = $valor;
                    break;
                case 'refcomdir1': $_SESSION["formulario"]["datos"]["refcomdir1"] = $valor;
                    break;
                case 'refcomtel1': $_SESSION["formulario"]["datos"]["refcomtel1"] = $valor;
                    break;
                case 'refcomnom2': $_SESSION["formulario"]["datos"]["refcomnom2"] = $valor;
                    break;
                case 'refcomdir2': $_SESSION["formulario"]["datos"]["refcomdir2"] = $valor;
                    break;
                case 'refcomtel2': $_SESSION["formulario"]["datos"]["refcomtel2"] = $valor;
                    break;
                case 'apolab': $_SESSION["formulario"]["datos"]["apolab"] = str_replace($exclu2, "", $valor);
                    break;
                case 'apolabadi': $_SESSION["formulario"]["datos"]["apolabadi"] = str_replace($exclu2, "", $valor);
                    break;
                case 'apodin': $_SESSION["formulario"]["datos"]["apodin"] = str_replace($exclu2, "", $valor);
                    break;
                case 'apoact': $_SESSION["formulario"]["datos"]["apoact"] = str_replace($exclu2, "", $valor);
                    break;
                case 'apotot': $_SESSION["formulario"]["datos"]["apotot"] = str_replace($exclu2, "", $valor);
                    break;
                case 'ivcarea': $_SESSION["formulario"]["datos"]["ivcarea"] = ltrim((string)$valor, "0");
                    break;
                case 'ivcver': $_SESSION["formulario"]["datos"]["ivcver"] = $valor;
                    break;
                case 'ivccretip': $_SESSION["formulario"]["datos"]["ivccretip"] = $valor;
                    break;
                case 'ivcqui': $_SESSION["formulario"]["datos"]["ivcqui"] = $valor;
                    break;
                case 'ivcali': $_SESSION["formulario"]["datos"]["ivcali"] = $valor;
                    break;

                //Información Propietarios

                case 'prop_1_organizacionpropietario': $_SESSION["formulario"]["datos"]["propietarios"][1]["organizacionpropietario"] = $valor;
                    break;
                case 'prop_1_camarapropietario': $_SESSION["formulario"]["datos"]["propietarios"][1]["camarapropietario"] = sprintf("%02s", $valor);
                    break;
                case 'prop_1_matriculapropietario': $_SESSION["formulario"]["datos"]["propietarios"][1]["matriculapropietario"] = ltrim((string)$valor, "0");
                    break;
                case 'prop_1_idtipoidentificacionpropietario': $_SESSION["formulario"]["datos"]["propietarios"][1]["idtipoidentificacionpropietario"] = $valor;
                    break;
                case 'prop_1_identificacionpropietario': $_SESSION["formulario"]["datos"]["propietarios"][1]["identificacionpropietario"] = soloNumeros($valor);
                    break;
                case 'prop_1_nitpropietario': $_SESSION["formulario"]["datos"]["propietarios"][1]["nitpropietario"] = soloNumeros($valor);
                    break;
                case 'prop_1_nombrepropietario': $_SESSION["formulario"]["datos"]["propietarios"][1]["nombrepropietario"] = strtoupper($valor);
                    break;
                case 'prop_1_direccionpropietario': $_SESSION["formulario"]["datos"]["propietarios"][1]["direccionpropietario"] = strtoupper($valor);
                    break;
                case 'prop_1_municipiopropietario': $_SESSION["formulario"]["datos"]["propietarios"][1]["municipiopropietario"] = strtoupper($valor);
                    break;
                case 'prop_1_direccionnotpropietario': $_SESSION["formulario"]["datos"]["propietarios"][1]["direccionnotpropietario"] = strtoupper($valor);
                    break;
                case 'prop_1_municipionotpropietario': $_SESSION["formulario"]["datos"]["propietarios"][1]["municipionotpropietario"] = strtoupper($valor);
                    break;
                case 'prop_1_telefonopropietario': $_SESSION["formulario"]["datos"]["propietarios"][1]["telefonopropietario"] = soloNumeros($valor);
                    break;
                case 'prop_1_telefono2propietario': $_SESSION["formulario"]["datos"]["propietarios"][1]["telefono2propietario"] = soloNumeros($valor);
                    break;
                case 'prop_1_celularpropietario': $_SESSION["formulario"]["datos"]["propietarios"][1]["celularpropietario"] = soloNumeros($valor);
                    break;
                case 'prop_1_nomreplegpropietario': $_SESSION["formulario"]["datos"]["propietarios"][1]["nomreplegpropietario"] = strtoupper($valor);
                    break;
                case 'prop_1_tipoidreplegpropietario': $_SESSION["formulario"]["datos"]["propietarios"][1]["tipoidreplegpropietario"] = strtoupper($valor);
                    break;
                case 'prop_1_numidreplegpropietario': $_SESSION["formulario"]["datos"]["propietarios"][1]["numidreplegpropietario"] = strtoupper($valor);
                    break;
                case 'prop_1_ultanorenpropietario': $_SESSION["formulario"]["datos"]["propietarios"][1]["ultanorenpropietario"] = strtoupper($valor);
                    break;

                //Información de Bienes que posee # 1
                case '_rmerGen_bienes_mi_1': $_SESSION["formulario"]["datos"]["bienes"][1]["matinmo"] = trim(strtoupper((string)$valor));
                    break;
                case '_rmerGen_bienes_di_1': $_SESSION["formulario"]["datos"]["bienes"][1]["dir"] = trim(strtoupper((string)$valor));
                    break;
                case '_rmerGen_bienes_ba_1': $_SESSION["formulario"]["datos"]["bienes"][1]["barrio"] = trim(strtoupper((string)$valor));
                    break;
                case '_rmerGen_bienes_mu_1': $_SESSION["formulario"]["datos"]["bienes"][1]["muni"] = trim(strtoupper((string)$valor));
                    break;
                case '_rmerGen_bienes_dp_1': $_SESSION["formulario"]["datos"]["bienes"][1]["dpto"] = trim(strtoupper((string)$valor));
                    break;
                case '_rmerGen_bienes_pa_1': $_SESSION["formulario"]["datos"]["bienes"][1]["pais"] = trim(strtoupper((string)$valor));
                    break;

                //Información de Bienes que posee # 2
                case '_rmerGen_bienes_mi_2': $_SESSION["formulario"]["datos"]["bienes"][2]["matinmo"] = trim(strtoupper((string)$valor));
                    break;
                case '_rmerGen_bienes_di_2': $_SESSION["formulario"]["datos"]["bienes"][2]["dir"] = trim(strtoupper((string)$valor));
                    break;
                case '_rmerGen_bienes_ba_2': $_SESSION["formulario"]["datos"]["bienes"][2]["barrio"] = trim(strtoupper((string)$valor));
                    break;
                case '_rmerGen_bienes_mu_2': $_SESSION["formulario"]["datos"]["bienes"][2]["muni"] = trim(strtoupper((string)$valor));
                    break;
                case '_rmerGen_bienes_dp_2': $_SESSION["formulario"]["datos"]["bienes"][2]["dpto"] = trim(strtoupper((string)$valor));
                    break;
                case '_rmerGen_bienes_pa_2': $_SESSION["formulario"]["datos"]["bienes"][2]["pais"] = trim(strtoupper((string)$valor));
                    break;
            }


            if (substr($key, 0, 9) == 'anodatos_') {
                $anox = substr($key, 9, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["anodatos"] = soloNumeros($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["anodatos"] = soloNumeros($valor);
                }
            }
            if (substr($key, 0, 11) == 'fechadatos_') {
                $anox = substr($key, 11, 4);

                $_SESSION["formulario"]["datos"]["f"][$anox]["fechadatos"] = soloNumeros($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["fechadatos"] = soloNumeros($valor);
                }
            }
            if (substr($key, 0, 9) == 'personal_') {
                $anox = substr($key, 9, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["personal"] = soloNumeros($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["personal"] = soloNumeros($valor);
                }
            }
            if (substr($key, 0, 13) == 'personaltemp_') {
                $anox = substr($key, 13, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["personaltemp"] = soloNumeros($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["personaltemp"] = soloNumeros($valor);
                }
            }
            if (substr($key, 0, 7) == 'actvin_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["actvin"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["actvin"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 7) == 'actcte_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["actcte"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["actcte"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 9) == 'actnocte_') {
                $anox = substr($key, 9, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["actnocte"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["actnocte"] = doubleval($valor);
                }
            }

            if (substr($key, 0, 7) == 'actfij_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["actfij"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["actfij"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 7) == 'fijnet_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["fijnet"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["fijnet"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 7) == 'actotr_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["actotr"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["actotr"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 7) == 'actval_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["actval"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["actval"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 7) == 'acttot_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["acttot"] = doubleval($valor);
                $_SESSION["formulario"]["datos"]["f"][$anox]["actsinaju"] = 0;
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["acttot"] = doubleval($valor);
                    $_SESSION["formulario"]["datos"]["actsinaju"] = 0;
                }
            }
            if (substr($key, 0, 7) == 'invent_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["invent"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["invent"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 7) == 'pascte_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["pascte"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["pascte"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 7) == 'paslar_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["paslar"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["paslar"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 7) == 'pastot_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["pastot"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["pastot"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 7) == 'pattot_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["pattot"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["pattot"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 7) == 'paspat_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["paspat"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["paspat"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 7) == 'balsoc_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["balsoc"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["balsoc"] = doubleval($valor);
                }
            }

            if (substr($key, 0, 7) == 'ingope_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["ingope"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["ingope"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 9) == 'ingnoope_') {
                $anox = substr($key, 9, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["ingnoope"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["ingnoope"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 7) == 'cosven_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["cosven"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["cosven"] = doubleval($valor);
                }
            }

            if (substr($key, 0, 7) == 'gtoven_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["gtoven"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["gtoven"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 7) == 'gtoadm_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["gtoadm"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["gtoadm"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 7) == 'depamo_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["depamo"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["depamo"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 7) == 'gasimp_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["gasimp"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["gasimp"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 7) == 'gasint_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["gasint"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["gasint"] = doubleval($valor);
                }
            }


            if (substr($key, 0, 7) == 'utiope_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["utiope"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["utiope"] = doubleval($valor);
                }
            }
            if (substr($key, 0, 7) == 'utinet_') {
                $anox = substr($key, 7, 4);
                $_SESSION["formulario"]["datos"]["f"][$anox]["utinet"] = doubleval($valor);
                if ($anox == $_SESSION["formulario"]["anorenovar"]) {
                    $_SESSION["formulario"]["datos"]["utinet"] = doubleval($valor);
                }
            }

            if (substr($key, 0, 7) == 'caealc_') {
                $ind = $key;
                $_SESSION["formulario"]["datos"]["codigoscae"][$key] = trim(strtoupper((string)$valor));
            }
            if (substr($key, 0, 6) == 'cadic_') {
                $ind = $key;
                $_SESSION["formulario"]["datos"]["informacionadicional"][$key] = trim(strtoupper((string)$valor));
            }
        }

        // ********************************************************************** //
        // Serializa el xml
        // ********************************************************************** //                 
        $xml = \funcionesRegistrales::serializarExpedienteMatricula($mysqli);

        // ********************************************************************** //
        // Almacena el xml
        // ********************************************************************** //                 
        borrarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"] . " and expediente='" . $_SESSION["entrada"]["matricula"] . "'");
        $arrCampos = array(
            'idliquidacion',
            'secuencia',
            'cc',
            'expediente',
            'grupodatos',
            'xml',
            'idestado'
        );
        $arrValores = array(
            $_SESSION["entrada"]["idliquidacion"],
            "'" . sprintf("%03s", $_SESSION["entrada"]["sectransaccion"]) . "'",
            "'" . $_SESSION["entrada"]["cc"] . "'",
            "'" . $_SESSION["entrada"]["matricula"] . "'",
            "'completo'",
            "'" . addslashes($xml) . "'",
            "'2'"
        );
        insertarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondatos', $arrCampos, $arrValores);

        // ********************************************************************** //
        // Almacena en la tabla datos de control
        // ********************************************************************** //                                 
        if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl') {
            \funcionesRegistrales::almacenarDatosImportantesRenovacion($mysqli, $_SESSION["tramite"]["idliquidacion"], $_SESSION["formulario"]["datos"], 'F');
        }

        // ********************************************************************** //
        // Cierra la conexión con la bd
        // ********************************************************************** //                         
        $mysqli->close();


        $_SESSION["jsonsalida"]["codigoerror"] = "0000";
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
