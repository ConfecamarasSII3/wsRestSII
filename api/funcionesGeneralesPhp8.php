<?php
require_once('../vendor/autoload.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class funcionesGenerales
{

    public static function imagenAleatoria($galeria = 'thumbnails')
    {
        return TIPO_HTTP . HTTP_HOST . '/images/' . $galeria . '/f' . rand(1, 126) . '.jpg';
    }

    //
    public static function armarNombresTablas()
    {
        if (!isset($_SESSION["generales"]["codigoempresa"])) {
            return false;
        }

        //
        if (!file_exists(PATH_ABSOLUTO_SITIO . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php')) {
            return false;
        }

        //
        $_SESSION["generales"]["basopciones"] = 'bas_opciones';
        $_SESSION["generales"]["bastagsbandejaentrada"] = 'bas_tagsbandejaentrada';
        $_SESSION["generales"]["baspermisosespeciales"] = 'bas_permisosespeciales';
        $_SESSION["generales"]["tabla_usuarios"] = 'usuarios';

        if (defined('TABLA_BAS_OPCIONES') && trim(TABLA_BAS_OPCIONES) != '') {
            $_SESSION["generales"]["basopciones"] = TABLA_BAS_OPCIONES;
        }
        if (defined('TABLA_BAS_TAGSBANDEJAENTRADA') && trim(TABLA_BAS_TAGSBANDEJAENTRADA) != '') {
            $_SESSION["generales"]["bastagsbandejaentrada"] = TABLA_BAS_TAGSBANDEJAENTRADA;
        }
        if (defined('TABLA_BAS_PERMISOSESPECIALES') && trim(TABLA_BAS_PERMISOSESPECIALES) != '') {
            $_SESSION["generales"]["baspermisosespeciales"] = TABLA_BAS_PERMISOSESPECIALES;
        }
        return true;
    }

    public static function inicializarUnset($var)
    {
        if (!isset($var)) {
            return "";
        } else {
            return $var;
        }
    }

    public static function armarPantallaSimple($titulo, $txt, $tamano = 500)
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/presentacion.class.php');
        //
        $acceso = md5(sha1(USUARIO_API_DEFECTO)) . '|' . md5(sha1(TOKEN_API_DEFECTO) . '|' . CODIGO_EMPRESA);
        $acceso = \funcionesGenerales::encrypt_decrypt('encrypt', ENCRYPTION_SECRET_KEY, ENCRYPTION_SECRET_IV, $acceso);

        $string = '';
        $pres = new presentacionBootstrap();
        $string .= $pres->abrirPanelGeneral($tamano);
        $string .= '<br>';
        $string .= $pres->abrirPanel();
        $string .= $pres->armarCampoTextoOculto('session_parameters', \funcionesGenerales::armarVariablesPantalla());
        $string .= $pres->armarCampoTextoOculto('_accesso', $acceso);
        $string .= $pres->armarCampoTextoOculto('_tipohttp', TIPO_HTTP);
        $string .= $pres->armarCampoTextoOculto('_httphost', HTTP_HOST);
        $string .= $pres->armarEncabezado(NOMBRE_SISTEMA, $titulo, 'no');
        $string .= '<br>';
        $string .= $pres->armarLineaTextoInformativa($txt, 'center');
        $string .= $pres->cerrarPanel();
        $string .= $pres->cerrarPanelGeneral();
        unset($pres);
        $img = '';
        \funcionesGenerales::mostrarCuerpoBootstrap('', '', '', '', '', $string, $img, '');
        exit();
    }

    public static function armarSelectArreglo($sel, $arr, $id)
    {
        if (trim($id) == '') {
            $retornar = "<option value='' selected>" . $sel . "</option>";
        } else {
            $retornar = "<option value=''>" . $sel . "</option>";
        }
        if (!empty($arr)) {
            foreach ($arr as $key => $valor) {
                if ($key == $id) {
                    $retornar .= "<option value='" . $key . "' selected>" . $valor . "</option>";
                } else {
                    $retornar .= "<option value='" . $key . "'>" . $valor . "</option>";
                }
            }
        }
        return $retornar;
    }

    /**
     * 
     * @return type
     */
    public static function armarVariablesPantalla($tokenscript = '')
    {
        if (!isset($_SESSION["generales"]["tokenscriptentrada"])) {
            $_SESSION["generales"]["tokenscriptentrada"] = '';
        }

        if (!isset($_SESSION["generales"]["ocultarencabezados"])) {
            $_SESSION["generales"]["ocultarencabezados"] = 'si';
        }
        if (!isset($_SESSION["generales"]["linkretornoindex"])) {
            $_SESSION["generales"]["linkretornoindex"] = 'si';
        }
        if (!isset($_SESSION["generales"]["idtipousuariodesarrollo"])) {
            $_SESSION["generales"]["idtipousuariodesarrollo"] = '';
        }
        if (!isset($_SESSION["generales"]["idtipousuariofinanciero"])) {
            $_SESSION["generales"]["idtipousuariofinanciero"] = '';
        }
        if (!isset($_SESSION["generales"]["periodo"])) {
            $_SESSION["generales"]["periodo"] = date("Y");
        }
        if (!isset($_SESSION["generales"]["footeremails"])) {
            $_SESSION["generales"]["footeremails"] = '';
        }
        if (!isset($_SESSION["generales"]["sistemaorigen"])) {
            $_SESSION["generales"]["sistemaorigen"] = '';
        }
        if (!isset($_SESSION["generales"]["bannerpersonalizado"])) {
            $_SESSION["generales"]["bannerpersonalizado"] = '';
        }

        //
        $vars = array();

        //
        $vars["fechaarmado"] = date("Ymd");
        $vars["horaarmado"] = date("His");

        //
        $vars["ip"] = \funcionesGenerales::localizarIP();
        if ($tokenscript != '') {
            $vars["tokenscript"] = $tokenscript;
        } else {
            $vars["tokenscript"] = $_SESSION["generales"]["tokenscriptentrada"];
        }

        //
        $vars["tipousuario"] = '';
        $vars["idtipousuario"] = '';
        if (isset($_SESSION["generales"]["tipousuario"]) && $_SESSION["generales"]["tipousuario"] != '') {
            $vars["tipousuario"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["tipousuario"]);
            $vars["idtipousuario"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["tipousuario"]);
        } else {
            if (isset($_SESSION["generales"]["idtipousuario"]) && $_SESSION["generales"]["idtipousuario"] != '') {
                $vars["tipousuario"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["idtipousuario"]);
                $vars["idtipousuario"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["idtipousuario"]);
            }
        }
        $vars["tipousuariocontrol"] = isset($_SESSION["generales"]["tipousuariocontrol"]) ? $_SESSION["generales"]["tipousuariocontrol"] : '';
        $vars["codigoempresa"] = isset($_SESSION["generales"]["codigoempresa"]) ? $_SESSION["generales"]["codigoempresa"] : '';
        $vars["codigousuario"] = isset($_SESSION["generales"]["codigousuario"]) ? $_SESSION["generales"]["codigousuario"] : '';
        $vars["periodo"] = isset($_SESSION["generales"]["periodo"]) ? $_SESSION["generales"]["periodo"] : '';
        $vars["pathabsoluto"] = isset($_SESSION["generales"]["pathabsoluto"]) ? $_SESSION["generales"]["pathabsoluto"] : '';
        $vars["navegador"] = isset($_SESSION["generales"]["navegador"]) ? $_SESSION["generales"]["navegador"] : '';
        $vars["zonahoraria"] = isset($_SESSION["generales"]["zonahoraria"]) ? $_SESSION["generales"]["zonahoraria"] : '';
        $vars["idioma"] = isset($_SESSION["generales"]["idioma"]) ? $_SESSION["generales"]["idioma"] : '';
        $vars["ocultarencabezados"] = isset($_SESSION["generales"]["ocultarencabezados"]) ? $_SESSION["generales"]["ocultarencabezados"] : '';
        $vars["nombreusuario"] = isset($_SESSION["generales"]["nombreusuariocontrol"]) ? $_SESSION["generales"]["nombreusuariocontrol"] : '';
        $vars["nombreusuariocontrol"] = isset($_SESSION["generales"]["nombreusuariocontrol"]) ? $_SESSION["generales"]["nombreusuariocontrol"] : '';
        $vars["sedeusuario"] = isset($_SESSION["generales"]["sedeusuario"]) ? $_SESSION["generales"]["sedeusuario"] : '';
        $vars["nombre1usuariocontrol"] = isset($_SESSION["generales"]["nombre1usuariocontrol"]) ? $_SESSION["generales"]["nombre1usuariocontrol"] : '';
        $vars["nombre2usuariocontrol"] = isset($_SESSION["generales"]["nombre2usuariocontrol"]) ? $_SESSION["generales"]["nombre2usuariocontrol"] : '';
        $vars["apellido1usuariocontrol"] = isset($_SESSION["generales"]["apellido1usuariocontrol"]) ? $_SESSION["generales"]["apellido1usuariocontrol"] : '';
        $vars["apellido2usuariocontrol"] = isset($_SESSION["generales"]["apellido2usuariocontrol"]) ? $_SESSION["generales"]["apellido2usuariocontrol"] : '';
        $vars["direccionusuariocontrol"] = isset($_SESSION["generales"]["direccionusuariocontrol"]) ? $_SESSION["generales"]["direccionusuariocontrol"] : '';
        $vars["municipiousuariocontrol"] = isset($_SESSION["generales"]["municipiousuariocontrol"]) ? $_SESSION["generales"]["municipiousuariocontrol"] : '';
        $vars["tipousuariocontrol"] = isset($_SESSION["generales"]["tipousuariocontrol"]) ? $_SESSION["generales"]["tipousuariocontrol"] : '';
        $vars["identificacionusuariocontrol"] = isset($_SESSION["generales"]["identificacionusuariocontrol"]) ? $_SESSION["generales"]["identificacionusuariocontrol"] : '';
        $vars["emailusuariocontrol"] = isset($_SESSION["generales"]["emailusuariocontrol"]) ? $_SESSION["generales"]["emailusuariocontrol"] : '';
        $vars["celularusuariocontrol"] = isset($_SESSION["generales"]["celularusuariocontrol"]) ? $_SESSION["generales"]["celularusuariocontrol"] : '';
        $vars["idtipousuariodesarrollo"] = isset($_SESSION["generales"]["idtipousuariodesarrollo"]) ? $_SESSION["generales"]["idtipousuariodesarrollo"] : '';
        $vars["tipousuarioexterno"] = isset($_SESSION["generales"]["tipousuarioexterno"]) ? $_SESSION["generales"]["tipousuarioexterno"] : '';
        $vars["idtipousuariofinanciero"] = isset($_SESSION["generales"]["idtipousuariofinanciero"]) ? $_SESSION["generales"]["idtipousuariofinanciero"] : '';
        $vars["escajero"] = isset($_SESSION["generales"]["escajero"]) ? $_SESSION["generales"]["escajero"] : '';
        $vars["gastoadministrativo"] = isset($_SESSION["generales"]["gastoadministrativo"]) ? $_SESSION["generales"]["gastoadministrativo"] : '';
        $vars["esdispensador"] = isset($_SESSION["generales"]["esdispensador"]) ? $_SESSION["generales"]["esdispensador"] : '';
        $vars["escensador"] = isset($_SESSION["generales"]["escensador"]) ? $_SESSION["generales"]["escensador"] : '';
        $vars["esbrigadista"] = isset($_SESSION["generales"]["esbrigadista"]) ? $_SESSION["generales"]["esbrigadista"] : '';
        $vars["puedecerrarcaja"] = isset($_SESSION["generales"]["puedecerrarcaja"]) ? $_SESSION["generales"]["puedecerrarcaja"] : '';
        $vars["visualizatotales"] = isset($_SESSION["generales"]["visualizatotales"]) ? $_SESSION["generales"]["visualizatotales"] : '';
        $vars["esrue"] = isset($_SESSION["generales"]["esrue"]) ? $_SESSION["generales"]["esrue"] : '';
        $vars["esreversion"] = isset($_SESSION["generales"]["esreversion"]) ? $_SESSION["generales"]["esreversion"] : '';
        $vars["eswww"] = isset($_SESSION["generales"]["eswww"]) ? $_SESSION["generales"]["eswww"] : '';
        $vars["essa"] = isset($_SESSION["generales"]["essa"]) ? $_SESSION["generales"]["essa"] : '';
        $vars["esbanco"] = isset($_SESSION["generales"]["esbanco"]) ? $_SESSION["generales"]["esbanco"] : '';
        $vars["abogadocoordinador"] = isset($_SESSION["generales"]["abogadocoordinador"]) ? $_SESSION["generales"]["abogadocoordinador"] : '';
        $vars["emailusuario"] = isset($_SESSION["generales"]["emailusuariocontrol"]) ? $_SESSION["generales"]["emailusuariocontrol"] : '';
        $vars["celular"] = isset($_SESSION["generales"]["celularusuariocontrol"]) ? $_SESSION["generales"]["celularusuariocontrol"] : '';
        $vars["loginemailusuario"] = isset($_SESSION["generales"]["loginemailusuario"]) ? $_SESSION["generales"]["loginemailusuario"] : '';
        $vars["passwordemailusuario"] = isset($_SESSION["generales"]["passwordemailusuario"]) ? $_SESSION["generales"]["passwordemailusuario"] : '';
        $vars["perfildocumentacion"] = isset($_SESSION["generales"]["perfildocumentacion"]) ? $_SESSION["generales"]["perfildocumentacion"] : '';
        $vars["controlapresupuesto"] = isset($_SESSION["generales"]["controlapresupuesto"]) ? $_SESSION["generales"]["controlapresupuesto"] : '';
        $vars["idtipoidentificacionusuario"] = isset($_SESSION["generales"]["idtipoidentificacionusuario"]) ? $_SESSION["generales"]["idtipoidentificacionusuario"] : '';

        $vars["identificacionusuario"] = isset($_SESSION["generales"]["identificacionusuario"]) ? $_SESSION["generales"]["identificacionusuario"] : '';
        $vars["nitempresausuario"] = isset($_SESSION["generales"]["nitempresausuario"]) ? $_SESSION["generales"]["nitempresausuario"] : '';
        $vars["nombreempresausuario"] = isset($_SESSION["generales"]["nombreempresausuario"]) ? $_SESSION["generales"]["nombreempresausuario"] : '';
        $vars["direccionusuario"] = isset($_SESSION["generales"]["direccionusuario"]) ? $_SESSION["generales"]["direccionusuario"] : '';
        $vars["idmuniciopiousuario"] = isset($_SESSION["generales"]["idmuniciopiousuario"]) ? $_SESSION["generales"]["idmuniciopiousuario"] : '';
        $vars["telefonousuario"] = isset($_SESSION["generales"]["celularusuariocontrol"]) ? $_SESSION["generales"]["celularusuariocontrol"] : '';
        $vars["movilusuario"] = isset($_SESSION["generales"]["celularusuariocontrol"]) ? $_SESSION["generales"]["celularusuariocontrol"] : '';
        $vars["operadorsirepusuario"] = '';
        $vars["ccosusuario"] = isset($_SESSION["generales"]["ccosusuario"]) ? $_SESSION["generales"]["ccosusuario"] : '';
        $vars["cargousuario"] = isset($_SESSION["generales"]["cargousuario"]) ? $_SESSION["generales"]["cargousuario"] : '';
        $vars["nombreempresa"] = isset($_SESSION["generales"]["nombreempresa"]) ? $_SESSION["generales"]["nombreempresa"] : '';
        $vars["idcodigosirepcaja"] = isset($_SESSION["generales"]["idcodigosirepcaja"]) ? $_SESSION["generales"]["idcodigosirepcaja"] : '';
        $vars["idcodigosirepdigitacion"] = isset($_SESSION["generales"]["idcodigosirepdigitacion"]) ? $_SESSION["generales"]["idcodigosirepdigitacion"] : '';
        $vars["idcodigosirepregistro"] = isset($_SESSION["generales"]["idcodigosirepregistro"]) ? $_SESSION["generales"]["idcodigosirepregistro"] : '';
        $vars["controlverificacion"] = isset($_SESSION["generales"]["controlverificacion"]) ? $_SESSION["generales"]["controlverificacion"] : '';
        $vars["fechaactivacion"] = isset($_SESSION["generales"]["fechaactivacion"]) ? $_SESSION["generales"]["fechaactivacion"] : '';
        $vars["fechacambioclave"] = isset($_SESSION["generales"]["fechacambioclave"]) ? $_SESSION["generales"]["fechacambioclave"] : '';

        $vars["controlusuarioretornara"] = isset($_SESSION["generales"]["controlusuarioretornara"]) ? $_SESSION["generales"]["controlusuarioretornara"] : '';
        $vars["controlusuariorutina"] = isset($_SESSION["generales"]["controlusuariorutina"]) ? $_SESSION["generales"]["controlusuariorutina"] : '';
        $vars["validado"] = isset($_SESSION["generales"]["validado"]) ? $_SESSION["generales"]["validado"] : '';
        $vars["linkretornoindex"] = isset($_SESSION["generales"]["linkretornoindex"]) ? $_SESSION["generales"]["linkretornoindex"] : '';
        $vars["mesavotacion"] = isset($_SESSION["generales"]["mesavotacion"]) ? $_SESSION["generales"]["mesavotacion"] : '';
        $vars["validado"] = isset($_SESSION["generales"]["validado"]) ? $_SESSION["generales"]["validado"] : '';
        $vars["footeremails"] = isset($_SESSION["generales"]["footeremails"]) ? $_SESSION["generales"]["footeremails"] : '';
        $vars["sistemaorigen"] = isset($_SESSION["generales"]["sistemaorigen"]) ? $_SESSION["generales"]["sistemaorigen"] : '';
        $vars["bannerpersonalizado"] = isset($_SESSION["generales"]["bannerpersonalizado"]) ? $_SESSION["generales"]["bannerpersonalizado"] : '';
        $vars["cabecera"] = isset($_SESSION["generales"]["cabecera"]) ? $_SESSION["generales"]["cabecera"] : '';

        $vars["sessionid"] = isset($_SESSION["generales"]["sessionid"]) ? $_SESSION["generales"]["sessionid"] : '';

        $vars["llamadodesdemenulateral"] = isset($_SESSION["generales"]["llamadodesdemenulateral"]) ? $_SESSION["generales"]["llamadodesdemenulateral"] : '';

        $vars["dnsserver"] = $_SERVER["HTTP_HOST"];

        //
        $vars1 = json_encode($vars);
        return \funcionesGenerales::encrypt_decrypt('encrypt', ENCRYPTION_SECRET_KEY, ENCRYPTION_SECRET_IV, $vars1);
    }

    public static function armarVariablesPantallaSii1($guardar = 'no')
    {

        //
        $vars = array();
        $vars["fechaarmado"] = date("Ymd");
        $vars["horaarmado"] = date("His");
        $vars["ip"] = \funcionesGenerales::localizarIP();

        if (isset($_SESSION["generales"]["tipousuario"])) {
            $vars["tipousuario"] = $_SESSION["generales"]["tipousuario"];
        } else {
            $vars["tipousuario"] = '';
        }

        if (isset($_SESSION["generales"]["idtipousuario"])) {
            $vars["idtipousuario"] = $_SESSION["generales"]["idtipousuario"];
        } else {
            $vars["idtipousuario"] = '';
        }

        if (isset($_SESSION["generales"]["tipousuariocontrol"])) {
            $vars["tipousuariocontrol"] = $_SESSION["generales"]["tipousuariocontrol"];
        } else {
            $vars["tipousuariocontrol"] = '';
        }

        if (isset($_SESSION["generales"]["codigoempresa"])) {
            $vars["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
        } else {
            $vars["codigoempresa"] = '';
        }

        if (isset($_SESSION["generales"]["periodo"])) {
            $vars["periodo"] = $_SESSION["generales"]["periodo"];
        } else {
            $vars["periodo"] = '';
        }

        if (isset($_SESSION["generales"]["pathabsoluto"])) {
            $vars["pathabsoluto"] = $_SESSION["generales"]["pathabsoluto"];
        } else {
            $vars["pathabsoluto"] = '';
        }

        if (isset($_SESSION["generales"]["navegador"])) {
            $vars["navegador"] = $_SESSION["generales"]["navegador"];
        } else {
            $vars["navegador"] = '';
        }

        if (isset($_SESSION["generales"]["zonahoraria"])) {
            $vars["zonahoraria"] = $_SESSION["generales"]["zonahoraria"];
        } else {
            $vars["zonahoraria"] = '';
        }

        if (isset($_SESSION["generales"]["idioma"])) {
            $vars["idioma"] = $_SESSION["generales"]["idioma"];
        } else {
            $vars["idioma"] = '';
        }

        if (isset($_SESSION["generales"]["ocultarencabezados"])) {
            $vars["ocultarencabezados"] = $_SESSION["generales"]["ocultarencabezados"];
        } else {
            $vars["ocultarencabezados"] = '';
        }

        if (isset($_SESSION["generales"]["linkretornoindex"])) {
            $vars["linkretornoindex"] = $_SESSION["generales"]["linkretornoindex"];
        } else {
            $vars["linkretornoindex"] = '';
        }

        if (isset($_SESSION["generales"]["codigousuario"])) {
            $vars["codigousuario"] = $_SESSION["generales"]["codigousuario"];
        } else {
            $vars["codigousuario"] = '';
        }

        if (isset($_SESSION["generales"]["nombreusuariocontrol"]) && $_SESSION["generales"]["nombreusuariocontrol"] != '') {
            $vars["nombreusuariocontrol"] = $_SESSION["generales"]["nombreusuariocontrol"];
        } else {
            $vars["nombreusuariocontrol"] = '';
        }

        if (isset($_SESSION["generales"]["nombreusuario"]) && $_SESSION["generales"]["nombreusuario"] != '') {
            $vars["nombreusuario"] = $_SESSION["generales"]["nombreusuario"];
        } else {
            $vars["nombreusuario"] = '';
        }

        if (isset($_SESSION["generales"]["sedeusuario"])) {
            $vars["sedeusuario"] = $_SESSION["generales"]["sedeusuario"];
        } else {
            $vars["sedeusuario"] = '';
        }

        if (isset($_SESSION["generales"]["nombre1usuariocontrol"])) {
            $vars["nombre1usuariocontrol"] = $_SESSION["generales"]["nombre1usuariocontrol"];
        } else {
            $vars["nombre1usuariocontrol"] = '';
        }

        if (isset($_SESSION["generales"]["nombre2usuariocontrol"])) {
            $vars["nombre2usuariocontrol"] = $_SESSION["generales"]["nombre2usuariocontrol"];
        } else {
            $vars["nombre2usuariocontrol"] = '';
        }

        if (isset($_SESSION["generales"]["apellido1usuariocontrol"])) {
            $vars["apellido1usuariocontrol"] = $_SESSION["generales"]["apellido1usuariocontrol"];
        } else {
            $vars["apellido1usuariocontrol"] = '';
        }

        if (isset($_SESSION["generales"]["apellido2usuariocontrol"])) {
            $vars["apellido2usuariocontrol"] = $_SESSION["generales"]["apellido2usuariocontrol"];
        } else {
            $vars["apellido2usuariocontrol"] = '';
        }

        if (isset($_SESSION["generales"]["direccionusuariocontrol"])) {
            $vars["direccionusuariocontrol"] = $_SESSION["generales"]["direccionusuariocontrol"];
        } else {
            $vars["direccionusuariocontrol"] = '';
        }

        if (isset($_SESSION["generales"]["municipiousuariocontrol"])) {
            $vars["municipiousuariocontrol"] = $_SESSION["generales"]["municipiousuariocontrol"];
        } else {
            $vars["municipiousuariocontrol"] = '';
        }

        if (isset($_SESSION["generales"]["tipousuariocontrol"])) {
            $vars["tipousuariocontrol"] = $_SESSION["generales"]["tipousuariocontrol"];
        } else {
            $vars["tipousuariocontrol"] = '';
        }

        if (isset($_SESSION["generales"]["identificacionusuariocontrol"])) {
            $vars["identificacionusuariocontrol"] = $_SESSION["generales"]["identificacionusuariocontrol"];
        } else {
            $vars["identificacionusuariocontrol"] = '';
        }

        if (isset($_SESSION["generales"]["emailusuariocontrol"])) {
            $vars["emailusuariocontrol"] = $_SESSION["generales"]["emailusuariocontrol"];
        } else {
            $vars["emailusuariocontrol"] = '';
        }

        if (isset($_SESSION["generales"]["celularusuariocontrol"])) {
            $vars["celularusuariocontrol"] = $_SESSION["generales"]["celularusuariocontrol"];
        } else {
            $vars["celularusuariocontrol"] = '';
        }

        if (isset($_SESSION["generales"]["idtipousuariodesarrollo"])) {
            $vars["idtipousuariodesarrollo"] = $_SESSION["generales"]["idtipousuariodesarrollo"];
        } else {
            $vars["idtipousuariodesarrollo"] = '';
        }

        if (isset($_SESSION["generales"]["tipousuarioexterno"])) {
            $vars["tipousuarioexterno"] = $_SESSION["generales"]["tipousuarioexterno"];
        } else {
            $vars["tipousuarioexterno"] = '';
        }

        if (isset($_SESSION["generales"]["idtipousuariofinanciero"])) {
            $vars["idtipousuariofinanciero"] = $_SESSION["generales"]["idtipousuariofinanciero"];
        } else {
            $vars["idtipousuariofinanciero"] = '';
        }

        if (isset($_SESSION["generales"]["escajero"])) {
            $vars["escajero"] = $_SESSION["generales"]["escajero"];
        } else {
            $vars["escajero"] = '';
        }

        if (isset($_SESSION["generales"]["gastoadministrativo"])) {
            $vars["gastoadministrativo"] = $_SESSION["generales"]["gastoadministrativo"];
        } else {
            $vars["gastoadministrativo"] = '';
        }

        if (isset($_SESSION["generales"]["esdispensador"])) {
            $vars["esdispensador"] = $_SESSION["generales"]["esdispensador"];
        } else {
            $vars["esdispensador"] = '';
        }

        if (isset($_SESSION["generales"]["escensador"])) {
            $vars["escensador"] = $_SESSION["generales"]["escensador"];
        } else {
            $vars["escensador"] = '';
        }

        if (isset($_SESSION["generales"]["esbrigadista"])) {
            $vars["esbrigadista"] = $_SESSION["generales"]["esbrigadista"];
        } else {
            $vars["esbrigadista"] = '';
        }

        if (isset($_SESSION["generales"]["puedecerrarcaja"])) {
            $vars["puedecerrarcaja"] = $_SESSION["generales"]["puedecerrarcaja"];
        } else {
            $vars["puedecerrarcaja"] = '';
        }

        if (isset($_SESSION["generales"]["visualizatotales"])) {
            $vars["visualizatotales"] = $_SESSION["generales"]["visualizatotales"];
        } else {
            $vars["visualizatotales"] = '';
        }

        if (isset($_SESSION["generales"]["esrue"])) {
            $vars["esrue"] = $_SESSION["generales"]["esrue"];
        } else {
            $vars["esrue"] = '';
        }

        if (isset($_SESSION["generales"]["esreversion"])) {
            $vars["esreversion"] = $_SESSION["generales"]["esreversion"];
        } else {
            $vars["esreversion"] = '';
        }

        if (isset($_SESSION["generales"]["eswww"])) {
            $vars["eswww"] = $_SESSION["generales"]["eswww"];
        } else {
            $vars["eswww"] = '';
        }

        if (isset($_SESSION["generales"]["essa"])) {
            $vars["essa"] = $_SESSION["generales"]["essa"];
        } else {
            $vars["essa"] = '';
        }

        if (isset($_SESSION["generales"]["esbanco"])) {
            $vars["esbanco"] = $_SESSION["generales"]["esbanco"];
        } else {
            $vars["esbanco"] = '';
        }

        if (isset($_SESSION["generales"]["abogadocoordinador"])) {
            $vars["abogadocoordinador"] = $_SESSION["generales"]["abogadocoordinador"];
        } else {
            $vars["abogadocoordinador"] = '';
        }

        if (isset($_SESSION["generales"]["emailusuariocontrol"])) {
            $vars["emailusuario"] = $_SESSION["generales"]["emailusuariocontrol"];
        } else {
            $vars["emailusuario"] = '';
        }

        if (isset($_SESSION["generales"]["celularusuariocontrol"])) {
            $vars["celular"] = $_SESSION["generales"]["celularusuariocontrol"];
        } else {
            $vars["celular"] = '';
        }

        if (isset($_SESSION["generales"]["loginemailusuario"])) {
            $vars["loginemailusuario"] = $_SESSION["generales"]["loginemailusuario"];
        } else {
            $vars["loginemailusuario"] = '';
        }

        if (isset($_SESSION["generales"]["passwordemailusuario"])) {
            $vars["passwordemailusuario"] = $_SESSION["generales"]["passwordemailusuario"];
        } else {
            $vars["passwordemailusuario"] = '';
        }

        if (isset($_SESSION["generales"]["perfildocumentacion"])) {
            $vars["perfildocumentacion"] = $_SESSION["generales"]["perfildocumentacion"];
        } else {
            $vars["perfildocumentacion"] = '';
        }

        if (isset($_SESSION["generales"]["controlapresupuesto"])) {
            $vars["controlapresupuesto"] = $_SESSION["generales"]["controlapresupuesto"];
        } else {
            $vars["controlapresupuesto"] = '';
        }

        if (isset($_SESSION["generales"]["idtipoidentificacionusuario"])) {
            $vars["idtipoidentificacionusuario"] = $_SESSION["generales"]["idtipoidentificacionusuario"];
        } else {
            $vars["idtipoidentificacionusuario"] = '';
        }

        if (isset($_SESSION["generales"]["identificacionusuario"])) {
            $vars["identificacionusuario"] = $_SESSION["generales"]["identificacionusuario"];
        } else {
            $vars["identificacionusuario"] = '';
        }

        if (isset($_SESSION["generales"]["nitempresausuario"])) {
            $vars["nitempresausuario"] = $_SESSION["generales"]["nitempresausuario"];
        } else {
            $vars["nitempresausuario"] = '';
        }

        if (isset($_SESSION["generales"]["nombreempresausuario"])) {
            $vars["nombreempresausuario"] = $_SESSION["generales"]["nombreempresausuario"];
        } else {
            $vars["nombreempresausuario"] = '';
        }

        if (isset($_SESSION["generales"]["direccionusuario"])) {
            $vars["direccionusuario"] = $_SESSION["generales"]["direccionusuario"];
        } else {
            $vars["direccionusuario"] = '';
        }

        if (isset($_SESSION["generales"]["idmuniciopiousuario"])) {
            $vars["idmuniciopiousuario"] = $_SESSION["generales"]["idmuniciopiousuario"];
        } else {
            $vars["idmuniciopiousuario"] = '';
        }

        if (isset($_SESSION["generales"]["telefonousuario"])) {
            $vars["telefonousuario"] = $_SESSION["generales"]["telefonousuario"];
        } else {
            $vars["telefonousuario"] = '';
        }

        if (isset($_SESSION["generales"]["movilusuario"])) {
            $vars["movilusuario"] = $_SESSION["generales"]["movilusuario"];
        } else {
            $vars["movilusuario"] = '';
        }

        if (isset($_SESSION["generales"]["ccosusuario"])) {
            $vars["ccosusuario"] = $_SESSION["generales"]["ccosusuario"];
        } else {
            $vars["ccosusuario"] = '';
        }

        if (isset($_SESSION["generales"]["cargousuario"])) {
            $vars["cargousuario"] = $_SESSION["generales"]["cargousuario"];
        } else {
            $vars["cargousuario"] = '';
        }

        if (isset($_SESSION["generales"]["nombreempresa"])) {
            $vars["nombreempresa"] = $_SESSION["generales"]["nombreempresa"];
        } else {
            $vars["nombreempresa"] = '';
        }

        if (isset($_SESSION["generales"]["controlverificacion"])) {
            $vars["controlverificacion"] = $_SESSION["generales"]["controlverificacion"];
        } else {
            $vars["controlverificacion"] = '';
        }

        if (isset($_SESSION["generales"]["fechaactivacion"])) {
            $vars["fechaactivacion"] = $_SESSION["generales"]["fechaactivacion"];
        } else {
            $vars["fechaactivacion"] = '';
        }

        if (isset($_SESSION["generales"]["fechacambioclave"])) {
            $vars["fechacambioclave"] = $_SESSION["generales"]["fechacambioclave"];
        } else {
            $vars["fechacambioclave"] = '';
        }

        if (isset($_SESSION["generales"]["controlusuarioretornara"])) {
            $vars["controlusuarioretornara"] = $_SESSION["generales"]["controlusuarioretornara"];
        } else {
            $vars["controlusuarioretornara"] = '';
        }

        if (isset($_SESSION["generales"]["controlusuariorutina"])) {
            $vars["controlusuariorutina"] = $_SESSION["generales"]["controlusuariorutina"];
        } else {
            $vars["controlusuariorutina"] = '';
        }

        if (isset($_SESSION["generales"]["validado"])) {
            $vars["validado"] = $_SESSION["generales"]["validado"];
        } else {
            $vars["validado"] = '';
        }

        if (isset($_SESSION["generales"]["linkretornoindex"])) {
            $vars["linkretornoindex"] = $_SESSION["generales"]["linkretornoindex"];
        } else {
            $vars["linkretornoindex"] = '';
        }

        if (isset($_SESSION["generales"]["mesavotacion"])) {
            $vars["mesavotacion"] = $_SESSION["generales"]["mesavotacion"];
        } else {
            $vars["mesavotacion"] = '';
        }

        if (isset($_SESSION["generales"]["mesavotacion"])) {
            $vars["mesavotacion"] = $_SESSION["generales"]["mesavotacion"];
        } else {
            $vars["mesavotacion"] = '';
        }

        if (isset($_SESSION["generales"]["footeremails"])) {
            $vars["footeremails"] = $_SESSION["generales"]["footeremails"];
        } else {
            $vars["footeremails"] = '';
        }

        //
        $vars1 = json_encode($vars);
        if ($guardar == 'si') {
            $f = fopen($vars["pathabsoluto"] . '/tmp/' . $vars["codigoempresa"] . '-armarVariablesPantalla-' . $vars["codigousuario"] . '-' . date("Ymd") . '-' . date("His") . '.json', "w");
            fwrite($f, $vars1);
            fclose($f);
        }
        return \funcionesGenerales::encrypt_decrypt('encrypt', ENCRYPTION_SECRET_KEY, ENCRYPTION_SECRET_IV, $vars1);
    }

    public static function armarVariablesSesionLimpiar($path = '', $codemp = '00')
    {
        if ($path != '') {
            $_SESSION["generales"]["pathabsoluto"] = $path;
            $_SESSION["generales"]["codigoempresa"] = $codemp;
        }
        require_once($_SESSION["generales"]["pathabsoluto"] . "/configuracion/common.php");
        require_once($_SESSION["generales"]["pathabsoluto"] . "/configuracion/common" . $_SESSION["generales"]["codigoempresa"] . ".php");
        require_once($_SESSION["generales"]["pathabsoluto"] . "/api/funcionesGenerales.php");
        require_once($_SESSION["generales"]["pathabsoluto"] . "/api/mysqli.php");

        $_SESSION["generales"]["navegador"] = \funcionesGenerales::obtenerNavegador(getenv("HTTP_USER_AGENT"));
        $_SESSION["generales"]["periodo"] = date("Y");
        $_SESSION["generales"]["codigousuario"] = '';
        $_SESSION["generales"]["tipousuario"] = '';
        $_SESSION["generales"]["nombreusuario"] = '';
        $_SESSION["generales"]["sede"] = '';
        $_SESSION["generales"]["tipousuariodesarrollo"] = '0';
        $_SESSION["generales"]["tipousuarioexterno"] = '0';
        $_SESSION["generales"]["tipousuariofinanciero"] = '0';
        $_SESSION["generales"]["tipousuariocontrol"] = '';
        $_SESSION["generales"]["validado"] = 'NO';
        $_SESSION["generales"]["mensajeerror"] = '';
        $_SESSION["generales"]["idcodigosirepcaja"] = '';
        $_SESSION["generales"]["idcodigosirepdigitacion"] = '';
        $_SESSION["generales"]["idcodigosirepregistro"] = '';
        $_SESSION["generales"]["disco"] = '';
        $_SESSION["generales"]["tipodoc"] = '';
        $_SESSION["generales"]["escajero"] = '';
        $_SESSION["generales"]["gastoadministrativo"] = '';
        $_SESSION["generales"]["esbrigadista"] = '';
        $_SESSION["generales"]["esdispensador"] = '';
        $_SESSION["generales"]["esrue"] = '';
        $_SESSION["generales"]["esreversion"] = '';
        $_SESSION["generales"]["eswww"] = '';
        $_SESSION["generales"]["escensador"] = '';
        $_SESSION["generales"]["essa"] = '';
        $_SESSION["generales"]["abogadocoordinador"] = '';
        $_SESSION["generales"]["puedecerrarcaja"] = '';
        $_SESSION["generales"]["visualizatotales"] = '';
        $_SESSION["generales"]["controlapresupuesto"] = '';
        $_SESSION["generales"]["visualizatotales"] = '';
        $_SESSION["generales"]["controlverificacion"] = '';
        $_SESSION["generales"]["operadorsirepusuario"] = '';
        $_SESSION["generales"]["sedeusuario"] = '';
        $_SESSION["generales"]["ccosusuario"] = '';
        $_SESSION["generales"]["cargousuario"] = '';
        $_SESSION["generales"]["nombreempresa"] = '';
        $_SESSION["generales"]["serversmtpemailusuario"] = '';
        $_SESSION["generales"]["emailusuariocontrol"] = '';
        $_SESSION["generales"]["celularusuariocontrol"] = '';
        $_SESSION["generales"]["nombreusuariocontrol"] = '';
        $_SESSION["generales"]["nombre1usuariocontrol"] = '';
        $_SESSION["generales"]["nombre2usuariocontrol"] = '';
        $_SESSION["generales"]["apellido1usuariocontrol"] = '';
        $_SESSION["generales"]["apellido2usuariocontrol"] = '';
        $_SESSION["generales"]["idtipoidentificacionusuario"] = '';
        $_SESSION["generales"]["identificacionusuario"] = '';
        $_SESSION["generales"]["nitempresausuario"] = '';
        $_SESSION["generales"]["nombreempresausuario"] = '';
        $_SESSION["generales"]["direccionusuario"] = '';
        $_SESSION["generales"]["idmuniciopiousuario"] = '';
        $_SESSION["generales"]["telefonousuario"] = '';
        $_SESSION["generales"]["movilusuario"] = '';
        $_SESSION["generales"]["sega"] = '';
        $_SESSION["generales"]["grupo"] = '';
        $_SESSION["generales"]["permisos"] = array();
        $_SESSION["generales"]["mensaje"] = '';
        $_SESSION["generales"]["directtlink"] = 'no';
        $_SESSION["generales"]["iddirecttlink"] = 0;
        $_SESSION["generales"]["footeremails"] = '';
    }

    public static function armarVariablesSesionSinUsuario($path = '', $codemp = '00')
    {
        if ($path != '') {
            $_SESSION["generales"]["pathabsoluto"] = $path;
            $_SESSION["generales"]["codigoempresa"] = $codemp;
        }
        require_once($_SESSION["generales"]["pathabsoluto"] . "/configuracion/common.php");
        require_once($_SESSION["generales"]["pathabsoluto"] . "/configuracion/common" . $_SESSION["generales"]["codigoempresa"] . ".php");
        require_once($_SESSION["generales"]["pathabsoluto"] . "/api/funcionesGenerales.php");
        require_once($_SESSION["generales"]["pathabsoluto"] . "/api/mysqli.php");

        $_SESSION["generales"]["navegador"] = \funcionesGenerales::obtenerNavegador(getenv("HTTP_USER_AGENT"));
        $_SESSION["generales"]["periodo"] = date("Y");
        if (!isset($_SESSION["generales"]["codigousuario"]) || $_SESSION["generales"]["codigousuario"] == '') {
            $_SESSION["generales"]["codigousuario"] = 'USUPUBXX';
        }
        if (!isset($_SESSION["generales"]["tipousuario"]) || $_SESSION["generales"]["tipousuario"] == '') {
            $_SESSION["generales"]["tipousuario"] = '00';
        }

        $_SESSION["generales"]["nombreusuario"] = '';
        $_SESSION["generales"]["sede"] = '99';
        $_SESSION["generales"]["tipousuariodesarrollo"] = '0';
        $_SESSION["generales"]["tipousuarioexterno"] = '0';
        $_SESSION["generales"]["tipousuariofinanciero"] = '0';
        $_SESSION["generales"]["tipousuariocontrol"] = 'anonimo';
        $_SESSION["generales"]["validado"] = 'NO';
        $_SESSION["generales"]["mensajeerror"] = '';
        $_SESSION["generales"]["idcodigosirepcaja"] = 'USUPUBXX';
        $_SESSION["generales"]["idcodigosirepdigitacion"] = '';
        $_SESSION["generales"]["idcodigosirepregistro"] = '';
        $_SESSION["generales"]["disco"] = '';
        $_SESSION["generales"]["tipodoc"] = '';
        $_SESSION["generales"]["escajero"] = '';
        $_SESSION["generales"]["gastoadministrativo"] = '';
        $_SESSION["generales"]["esbrigadista"] = '';
        $_SESSION["generales"]["esdispensador"] = '';
        $_SESSION["generales"]["esrue"] = '';
        $_SESSION["generales"]["esreversion"] = '';
        $_SESSION["generales"]["eswww"] = '';
        $_SESSION["generales"]["escensador"] = '';
        $_SESSION["generales"]["essa"] = '';
        $_SESSION["generales"]["abogadocoordinador"] = '';
        $_SESSION["generales"]["puedecerrarcaja"] = '';
        $_SESSION["generales"]["visualizatotales"] = '';
        $_SESSION["generales"]["controlapresupuesto"] = '';
        $_SESSION["generales"]["visualizatotales"] = '';
        $_SESSION["generales"]["controlverificacion"] = '';
        $_SESSION["generales"]["operadorsirepusuario"] = '';
        $_SESSION["generales"]["sedeusuario"] = '';
        $_SESSION["generales"]["ccosusuario"] = '';
        $_SESSION["generales"]["cargousuario"] = '';
        $_SESSION["generales"]["nombreempresa"] = '';
        $_SESSION["generales"]["serversmtpemailusuario"] = '';
        $_SESSION["generales"]["celularusuariocontrol"] = '';
        $_SESSION["generales"]["nombreusuariocontrol"] = '';
        $_SESSION["generales"]["nombre1usuariocontrol"] = '';
        $_SESSION["generales"]["nombre2usuariocontrol"] = '';
        $_SESSION["generales"]["apellido1usuariocontrol"] = '';
        $_SESSION["generales"]["apellido2usuariocontrol"] = '';
        $_SESSION["generales"]["idtipoidentificacionusuario"] = '';
        $_SESSION["generales"]["identificacionusuario"] = '';
        $_SESSION["generales"]["nitempresausuario"] = '';
        $_SESSION["generales"]["nombreempresausuario"] = '';
        $_SESSION["generales"]["direccionusuario"] = '';
        $_SESSION["generales"]["idmuniciopiousuario"] = '';
        $_SESSION["generales"]["telefonousuario"] = '';
        $_SESSION["generales"]["movilusuario"] = '';
        $_SESSION["generales"]["sega"] = 'PPAL';
        $_SESSION["generales"]["grupo"] = 'todos';
        $_SESSION["generales"]["permisos"] = array();
        $_SESSION["generales"]["mensaje"] = 'Espere...';
        $_SESSION["generales"]["directtlink"] = 'no';
        $_SESSION["generales"]["iddirecttlink"] = 0;
        $_SESSION["generales"]["footeremails"] = '';

        // 2016-12-26: JINT
        if (!isset($_SESSION["generales"]["tipousuariocontrol"]) || $_SESSION["generales"]["tipousuariocontrol"] == '') {
            $_SESSION["generales"]["tipousuariocontrol"] = 'usuarioanonimo';
        }
        if (!isset($_SESSION["generales"]["identificacionusuariocontrol"])) {
            $_SESSION["generales"]["identificacionusuariocontrol"] = '';
        }
        if (!isset($_SESSION["generales"]["emailusuariocontrol"])) {
            $_SESSION["generales"]["emailusuariocontrol"] = '';
        }
        $mysqli = conexionMysqliApi();
        $res = \funcionesGenerales::cargarPermisosPublico($mysqli, $_SESSION["generales"]["pathabsoluto"], $_SESSION["generales"]["codigoempresa"]);
        $mysqli->close();
        if ($res === false) {
            return false;
        } else {
            return true;
        }
    }

    public static function decrypt($string)
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        if (!defined('KEY_ENCRIPCION')) {
            define('KEY_ENCRIPCION', "S44S0nl1n3");
        }
        $result = '';
        $string = base64_decode($string);
        for ($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr(KEY_ENCRIPCION, ($i % strlen(KEY_ENCRIPCION)) - 1, 1);
            $char = chr(ord($char) - ord($keychar));
            $result .= $char;
        }
        return $result;
    }

    /**
     * 
     * @param type $vars
     * @return boolean
     */
    public static function desarmarVariablesPantalla($vars)
    {
        $txt = date("H:i:s") . ' - ';
        if (isset($vars) && $vars != '') {
            $vars1 = json_decode(\funcionesGenerales::encrypt_decrypt('decrypt', ENCRYPTION_SECRET_KEY, ENCRYPTION_SECRET_IV, $vars));
            if (!empty($vars1)) {
                foreach ($vars1 as $key => $valor) {
                    if ($key != 'permisos') {
                        $_SESSION["generales"][$key] = $valor;
                        $txt .= $key . ' => ' . $valor . "\r\n";
                    } else {
                        $_SESSION["generales"]["permisos"] = array();
                        if (is_array($valor) && !empty($valor)) {
                            foreach ($valor as $per => $dat) {
                                $_SESSION["generales"]["permisos"][$per] = $dat;
                            }
                        }
                    }
                }
            }
        }
        $txt .= "\r\n";
        /*
          if (!defined('PATH_ABSOLUTO_LOGS') || PATH_ABSOLUTO_LOGS == '') {
          $f = fopen(PATH_ABSOLUTO_SITIO . '/logs/desarmarVariablesPantalla_' . date("Ymd") . '.log', "a");
          } else {
          $f = fopen(PATH_ABSOLUTO_LOGS . '/desarmarVariablesPantalla_' . date("Ymd") . '.log', "a");
          }
          fwrite($f, date("H:i:s") . ' - ' . $txt);
          fclose($f);
         */
        return true;
    }

    public static function desarmarVariablesPantallaSii1($vars)
    {
        $vars1 = json_decode(\funcionesGenerales::encrypt_decrypt('decrypt', ENCRYPTION_SECRET_KEY, ENCRYPTION_SECRET_IV, $vars));
        if (!empty($vars1)) {
            foreach ($vars1 as $key => $valor) {
                if ($key != 'permisos') {
                    if (!isset($_SESSION["generales"][$key]) || $_SESSION["generales"][$key] == '') {
                        if ($valor != '') {
                            $_SESSION["generales"][$key] = $valor;
                        }
                    }
                } else {
                    $_SESSION["generales"]["permisos"] = array();
                    if (is_array($valor) && !empty($valor)) {
                        foreach ($valor as $per => $dat) {
                            $_SESSION["generales"]["permisos"][$per] = $dat;
                        }
                    }
                }
            }
        }
        return true;
    }

    public static function descargaPdf($url, $directorio)
    {
        $newfname = $directorio;
        /*
          $opts = array(
          "ssl" => array(
          "verify_peer" => false,
          "verify_peer_name" => false,
          ),
          );
          $file = fopen($url, 'rb', false, stream_context_create($opts));
         */

        $file = fopen($url, 'rb');

        if ($file) {
            $newf = fopen($newfname, 'wb');
            if ($newf) {
                while (!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
                }
            }
        }
        if ($file) {
            fclose($file);
        }
        if ($newf) {
            fclose($newf);
        }
        return "success";
    }

    public static function descargaPdfCurl($url, $directorio)
    {
        $archivo_descarga = curl_init();
        curl_setopt($archivo_descarga, CURLOPT_URL, $url); //ponemos lo que queremos descargar
        curl_setopt($archivo_descarga, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($archivo_descarga, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($archivo_descarga, CURLOPT_AUTOREFERER, true);
        $resultado_descarga = curl_exec($archivo_descarga); //realizamos la descarga
        if (!curl_errno($archivo_descarga)) {
            $newf = fopen($directorio, 'wb');
            if ($newf) {
                while (!feof($resultado_descarga)) {
                    fwrite($newf, fread($resultado_descarga, 1024 * 8), 1024 * 8);
                }
            }
            fclose($newf);
        } else {
            return false;
        }
        return "success";
    }

    /**
     * 
     * @param type $cadena
     * @param type $clave
     * @return type
     */
    public static function encriptar($cadena, $clave)
    {
        $cifrado = MCRYPT_RIJNDAEL_256;
        $modo = MCRYPT_MODE_ECB;
        return mcrypt_encrypt($cifrado, $clave, $cadena, $modo, mcrypt_create_iv(mcrypt_get_iv_size($cifrado, $modo), MCRYPT_RAND));
    }

    public static function desencriptaCerticamara($key, $iv, $data)
    {
        if (strlen($key) != 24) {
            echo "La longitud de la key ha de ser de 24 d&iacute;gitos.<br>";
            return false;
        }
        if ((strlen($iv) % 8) != 0) {
            echo "La longitud del vector iv ha de ser m&uacute;ltiplo de 8 d&iacute;gitos.<br>";
            return false;
        }
        // return mcrypt_decrypt(MCRYPT_3DES, $key, base64_decode($data), MCRYPT_MODE_CBC, $iv);
        return openssl_decrypt(base64_decode($data), 'DES-EDE3-CBC', $key, OPENSSL_ZERO_PADDING | OPENSSL_RAW_DATA, $iv);
    }

    public static function encriptaCerticamara($key, $iv, $data)
    {
        if (strlen($key) != 24) {
            echo "La longitud de la key ha de ser de 24 d&iacute;gitos.<br>";
            return -1;
        }
        if ((strlen($iv) % 8) != 0) {
            echo "La longitud del vector iv ha de ser m&uacute;ltiplo de 8 d&iacute;gitos.<br>";
            return false;
        }
        return base64_encode(mcrypt_encrypt(MCRYPT_3DES, $key, utf8_encode($data), MCRYPT_MODE_CBC, $iv));
        // return \funcionesGenerales::encrypt_decrypt('encrypt', $key, $iv, utf8_encode($data), "DES-EDE3-CBC");
    }

    public static function descripcionesFormato2019($mysqli, $organizacion1, $acto, $tipdoc, $numdoc, $numdocext, $fecdoc, $idorigen, $txtorigen, $munori, $libro, $registro, $fecins, $noticia, $nan = array(), $nombre = '', $comple = '', $camant = '', $libant = '', $regant = '', $fecant = '', $camant2 = '', $libant2 = '', $regant2 = '', $fecant2 = '', $camant3 = '', $libant3 = '', $regant3 = '', $fecant3 = '', $camant4 = '', $libant4 = '', $regant4 = '', $fecant4 = '', $camant5 = '', $libant5 = '', $regant5 = '', $fecant5 = '', $aclaratoria = '', $tomo72 = '', $folio72 = '', $registro72 = '', $sigla = '', $nat = '')
    {


        if (strpos($organizacion1, '|') === false) {
            $categoria = '';
            $organizacion = $organizacion1;
        } else {
            list($organizacion, $categoria) = explode("|", $organizacion1);
        }

        //
        if ($numdocext != '') {
            $numdoc = $numdocext;
        }

        //
        $txt = 'Por ';

        //
        if ($tipdoc == '15' && $numdoc == '1727') {
            $tipdoc = '38';
        }

        //
        if ($tipdoc == '38' && $numdoc == '1727') {
            $txt = 'De acuerdo a lo establecido en la ';
            $numdoc = '';
        }

        $txtDoc = retornarRegistroMysqliApi($mysqli, 'mreg_tipos_documentales_registro', "id='" . $tipdoc . "'", "descripcionlower");
        if ($txtDoc == '') {
            $txtDoc = 'documento';
        }

        //
        $txt .= $txtDoc . ' ';

        //
        if (trim($numdocext) != '' && trim($numdocext) != '0' && strtoupper(trim($numdocext)) != 'NA' && strtoupper(trim($numdocext)) != 'N/A' && strtoupper(trim($numdocext)) != 'SN' && strtoupper(trim($numdocext)) != 'S/N') {
            $txt .= 'No. ' . trim($numdocext) . ' ';
        } else {
            if (trim($numdoc) != '' && trim($numdoc) != '0' && strtoupper(trim($numdoc)) != 'NA' && strtoupper(trim($numdoc)) != 'N/A' && strtoupper(trim($numdoc)) != 'SN' && strtoupper(trim($numdoc)) != 'S/N') {
                $txt .= 'No. ' . trim($numdoc) . ' ';
            }
        }

        //
        if ($fecdoc != '' && $tipdoc != '38') {
            $txt .= 'del ' . \funcionesGenerales::mostrarFechaLetras1($fecdoc) . ' ';
        }

        //
        $txtSuscribe = '';

        //
        if ($tipdoc != '38') {
            if ($txtorigen != '') {
                if (strtoupper(trim($txtorigen)) == 'NO TIENE NO TIENE') {
                    $txtorigen = '';
                }
                $txtorigen = str_replace("NOTARIAS NOTARIA", "Notaría", $txtorigen);
                $txtorigen = str_replace("ACTAS ", "", $txtorigen);
                $txtorigen = str_replace("JUZGADOS CIVILES DEL CIRCUITO ", "", $txtorigen);
                $txtSuscribe = ucwords(strtolower($txtorigen));
            } else {
                if (ltrim($idorigen, "0") != '' && ltrim($idorigen, "0") != '999999') {
                    $txtSuscribe = ucwords(strtolower(retornarNombreTablasSirepMysqliApi($mysqli, '12', $idorigen)));
                }
            }

            //
            $txtParticula = 'de la';

            if (
                strtoupper($txtSuscribe) == 'REPRESENTACION LEGAL' ||
                strtoupper($txtSuscribe) == 'REPRESENTACIÓN LEGAL' ||
                strtoupper($txtSuscribe) == 'REPRESENTACIóN LEGAL'
            ) {
                $txtParticula = 'de';
                $txtSuscribe = 'el Representante Legal';
            }
            if (strtoupper($txtSuscribe) == 'COMERCIANTE') {
                $txtParticula = 'de';
                $txtSuscribe = 'el Comerciante';
            }
            if (strtoupper($txtSuscribe) == 'JUNTA DE SOCIOS') {
                $txtParticula = 'de';
                $txtSuscribe = 'la Junta de Socios';
            }
            if (strtoupper($txtSuscribe) == 'JUNTA DIRECTIVA') {
                $txtParticula = 'de';
                $txtSuscribe = 'la Junta Directiva';
            }
            if (substr(strtoupper($txtSuscribe), 0, 7) == 'JUZGADO') {
                $txtParticula = 'del';
            }
            if (strtoupper($txtSuscribe) == 'PROPIETARIO') {
                $txtParticula = 'de';
                $txtSuscribe = 'el Propietario';
            }
            if (strtoupper($txtSuscribe) == 'ADMON. DE IMPUESTOS NACIONALES') {
                $txtParticula = 'de';
                $txtSuscribe = 'La Administración de Impuestos Nacionales';
            }
            if (strtoupper($txtSuscribe) == 'ACCIONISTAS') {
                $txtParticula = 'de';
                $txtSuscribe = 'Accionistas';
            }

            // 2017-11-21: WSIERRA: Adicionar quien suscribe UNICO ACCIONISTA en control del texto de particula.
            if (strtoupper($txtSuscribe) == 'UNICO ACCIONISTA' || strtoupper($txtSuscribe) == 'ÚNICO ACCIONISTA') {
                $txtParticula = 'de';
                $txtSuscribe = 'Único Accionista';
            }

            // 2018-06-18: JINT
            if (strtoupper($txtSuscribe) == 'ACCIONISTA UNICO' || strtoupper($txtSuscribe) == 'ACCIONISTA ÚNICO') {
                $txtParticula = 'del';
                $txtSuscribe = 'Accionista Único';
            }

            // 2018-06-18: JINT
            if (
                strtoupper($txtSuscribe) == 'COMITE DE ADMINISTRACION' ||
                strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACION' ||
                strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACIÓN'
            ) {
                $txtParticula = 'del';
                $txtSuscribe = 'Comité de Administración';
            }

            // 2018-06-18: JINT
            if (
                strtoupper($txtSuscribe) == 'CONTADOR PÚBLICO' ||
                strtoupper($txtSuscribe) == 'CONTADOR PúBLICO' ||
                $txtSuscribe == 'Contador Público'
            ) {
                $txtParticula = 'del';
                $txtSuscribe = 'Contador Público';
            }


            // 2018-06-26: JINT
            if (strtoupper($txtSuscribe) == 'EL COMERCIANTE') {
                $txtParticula = '';
                $txtSuscribe = '';
            }


            if (strtoupper($txtSuscribe) == 'LA JUNTA DE SOCIOS') {
                if ($organizacion == '11') {
                    if ($acto == '0040') {
                        $txtParticula = '';
                        $txtSuscribe = 'del Empresario Constituyente';
                    }
                }
            }

            if (strtoupper($txtSuscribe) == 'EL SUSCRITO') {
                $txtParticula = '';
                $txtSuscribe = 'de el Suscrito';
            }

            if (strtoupper($txtSuscribe) == 'MUNICIPIO') {
                $txtParticula = '';
                $txtSuscribe = 'del municipio';
            }


            if ($txtSuscribe != '') {
                $txt .= trim($txtParticula) . ' ' . $txtSuscribe . ' ';
            }

            //
            if (strtoupper($txtSuscribe) != 'DE EL SUSCRITO') {
                if (strtoupper($txtSuscribe) == 'DEL MUNICIPIO') {
                    $txt .= ' de ' . ucwords(strtolower(retornarNombreMunicipioMysqliApi($mysqli, $munori)));
                } else {
                    // if ($tipdoc == '02' || $tipdoc == '04') {
                    if ($munori != '' && $munori != '00000' && $munori != '99999') {
                        $txt .= ' de ' . ucwords(strtolower(retornarNombreMunicipioMysqliApi($mysqli, $munori)));
                    }
                }
            }

            //
            $txt = str_replace(" , ", ", ", $txt);
            $txt = str_replace(array("DE LA LA", "DE LA EL"), array("DE LA", "DE EL"), $txt);
            $txt = str_replace(array("de la la", "de la el"), array("de la", "de el"), $txt);
            $txt = str_replace(array("de la los", "de la Los"), array("de los", "de Los"), $txt);
        }

        if (trim($tomo72) != '') {
            $txt .= ', inscrito bajo el número  ' . $registro72 . ', del tomo ' . $tomo72 . ', folio ' . $folio72 . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecins) . ' ';
        } else {
            if ($camant != '') {
                $txt .= ', inscrita inicialmente en la ' . ucwords(strtolower(retornarNombreCamaraMysqliApi($mysqli, $camant))) . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecant);
                if ($regant != '') {
                    $txt .= ' bajo el No. ' . $regant;
                }
                if ($libant != '') {
                    $txt .= ' del Libro ' . \funcionesGenerales::retornarLibroFormato2019($libant);
                }
            }

            if ($camant2 != '') {
                $txt .= ', y en la ' . ucwords(strtolower(retornarNombreCamaraMysqliApi($mysqli, $camant2))) . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecant2);
                if ($regant2 != '') {
                    $txt .= ' bajo el No. ' . $regant2;
                }
                if ($libant2 != '') {
                    $txt .= ' del Libro ' . \funcionesGenerales::retornarLibroFormato2019($libant2);
                }
            }

            if ($camant3 != '') {
                $txt .= ', y en la ' . ucwords(strtolower(retornarNombreCamaraMysqliApi($mysqli, $camant3))) . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecant3);
                if ($regant3 != '') {
                    $txt .= ' bajo el No. ' . $regant3;
                }
                if ($libant3 != '') {
                    $txt .= ' del Libro ' . \funcionesGenerales::retornarLibroFormato2019($libant3);
                }
            }

            if ($camant4 != '') {
                $txt .= ', y en la ' . ucwords(strtolower(retornarNombreCamaraMysqliApi($mysqli, $camant4))) . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecant4);
                if ($regant4 != '') {
                    $txt .= ' bajo el No. ' . $regant4;
                }
                if ($libant4 != '') {
                    $txt .= ' del Libro ' . \funcionesGenerales::retornarLibroFormato2019($libant4);
                }
            }

            if ($camant5 != '') {
                $txt .= ', y en la ' . ucwords(strtolower(retornarNombreCamaraMysqliApi($mysqli, $camant5))) . ', el ' . \funcionesGenerales::mostrarFechaLetras1($fecant5);
                if ($regant5 != '') {
                    $txt .= ' bajo el No. ' . $regant5;
                }
                if ($libant5 != '') {
                    $txt .= ' del Libro ' . \funcionesGenerales::retornarLibroFormato2019($libant5);
                }
            }


            //
            if ($camant != '' || $camant2 != '' || $camant3 != '' || $camant4 != '' || $camant5 != '') {
                $txt .= ' y posteriormente inscrita ';
            } else {
                $txt .= ', inscrito ';
            }
            $txt .= 'en esta Cámara de Comercio el ' . \funcionesGenerales::mostrarFechaLetras1($fecins) . ', con el No. ' . $registro . ' ';
            $txt .= 'del Libro ' . \funcionesGenerales::retornarLibroFormato2019($libro) . ', ';
        }

        //
        $si = 'no';
        if ($acto == '0030') { // Constitución
            $txt .= 'se inscribió ';
            $si = 'si';
        }
        if ($acto == '0040') { // Constitución
            $txt .= 'se constituyó ';
            $si = 'si';
        }

        if ($acto == '0042') { // Constitución por cambio de domicilio
            // $txt .= 'se inscribe el cambio de domicilio de ';
            $si = 'si';
        }

        if ($acto == '0050') { // Constitución
            $txt .= 'se constituyó ';
            $si = 'si';
        }
        if ($acto == '0080') { // Constitución
            $txt .= ' ';
            $si = 'si';
        }

        if ($acto == '0192') { // Oposiciones
            $txt .= 'se inscribió ';
            $si = 'si';
        }
        if ($acto == '0400') { // Transformaciones
            $txt .= 'se inscribió ';
            $si = 'si';
        }
        if ($acto >= '0530' && $acto <= '0540') { // Cancelaciones
            $txt .= 'se inscribio ';
            $si = 'si';
        }
        if ($acto >= '0650' && $acto <= '0690') { // Liquidación obligatoria
            $txt .= 'se insribió ';
            $si = 'si';
        }
        if ($libro == 'RM19') { // Reestructuracion
            $txt .= 'se inscribió ';
            $si = 'si';
        }
        if ($libro == 'RM08') { // Embargos
            $txt .= ' se decretó ';
            $si = 'si';
        }
        if ($libro == 'RM11') { // Reestructuracion
            $txt .= 'se inscribió ';
            $si = 'si';
        }
        if ($acto == '1921') { // Resoluciones
            $txt .= 'se resolvió ';
            $si = 'si';
        }

        if ($acto == '4000') { // SITIOS WEB
            $txt .= 'se registró ';
            $si = 'si';
        }
        if ($libro == '9997') { // Cambios de jurisdicción
            $txt .= 'se decretó ';
            $si = 'si';
        }
        if ($acto == '8999') { // Cambios de jurisdicción
            $si = 'si';
        }

        if ($si == 'no') {
            $txt .= 'se decretó ';
        }

        //
        $pegarNoticia = 'si';
        if ($acto == '0040') { // Constitución
            if (!empty($nan)) {
                if ($libro == 'RM13') {
                    $txt .= 'la persona jurídica de naturaleza civil denominada ' . $nan[1]["nom"];
                    $pegarNoticia = 'no';
                } else {
                    if ($libro == 'RE51' || $libro == 'RE52' || $libro == 'RE53' || $libro == 'RE54' || $libro == 'RE55') {
                        if (trim((string) $nat) == '') {
                            if ($organizacion == '14') {
                                $txt .= ' la persona jurídica del sector solidario denominada ' . $nan[1]["nom"];
                            } else {
                                $txt .= ' la entidad sin ánimo de lucro denominada ' . $nan[1]["nom"];
                            }
                        } else {
                            if ($organizacion == '14') {
                                $txt .= ' la persona jurídica del sector solidario de naturaleza ' . $nat . ' denominada ' . $nan[1]["nom"];
                            } else {
                                $txt .= ' la entidad sin ánimo de lucro de naturaleza ' . $nat . ' denominada ' . $nan[1]["nom"];
                            }
                        }
                        $pegarNoticia = 'no';
                    } else {
                        if ($organizacion == '08') {
                            $txt .= 'la sucursal de sociedad extranjera de naturaleza comercial denominada ' . $nan[1]["nom"];
                        } else {
                            if ($organizacion == '11') {
                                $txt .= 'la empresa unipersonal de naturaleza comercial denominada ' . $nan[1]["nom"];
                            } else {
                                if ($organizacion == '10') {
                                    $txt .= 'la persona jurídica de naturaleza civil denominada ' . $nan[1]["nom"];
                                } else {
                                    if ($organizacion == '12' || $organizacion == '14') {
                                        $txt .= 'la entidad sin ánimo de lucro denominada ' . $nan[1]["nom"];
                                    } else {
                                        $txt .= 'la persona jurídica de naturaleza comercial denominada ' . $nan[1]["nom"];
                                    }
                                }
                            }
                        }
                        $pegarNoticia = 'no';
                    }
                }
            } else {
                if ($nombre != '') {
                    $nom1 = \funcionesGenerales::borrarPalabrasAutomaticas($nombre, $comple);
                    if ($libro == 'RM13') {
                        $txt .= 'la persona jurídica de naturaleza civil denominada ' . $nom1;
                        $pegarNoticia = 'no';
                    } else {
                        if ($libro == 'RE51' || $libro == 'RE52' || $libro == 'RE53' || $libro == 'RE54' || $libro == 'RE55') {
                            if (trim((string) $nat) == '') {
                                if ($organizacion == '14') {
                                    $txt .= ' la persona jurídica del sector solidario denominada ' . $nom1;
                                } else {
                                    $txt .= ' la entidad sin ánimo de lucro denominada ' . $nom1;
                                }
                            } else {
                                if ($organizacion == '14') {
                                    $txt .= ' la persona jurídica del sector solidario de naturaleza ' . $nat . ' denominada ' . $nom1;
                                } else {
                                    $txt .= ' la entidad sin ánimo de lucro de naturaleza ' . $nat . ' denominada ' . $nom1;
                                }
                            }
                            $pegarNoticia = 'no';
                        } else {
                            if ($organizacion == '08') {
                                $txt .= 'la sucursal de sociedad extranjera de naturaleza comercial denominada ' . $nom1;
                            } else {
                                if ($organizacion == '11') {
                                    $txt .= 'la empresa unipersonal de naturaleza comercial denominada ' . $nom1;
                                } else {
                                    if ($organizacion == '10') {
                                        $txt .= 'la persona jurídica de naturaleza civil denominada ' . $nom1;
                                    } else {
                                        if ($organizacion == '12' || $organizacion == '14') {
                                            $txt .= 'la entidad sin ánimo de lucro denominada ' . $nom1;
                                        } else {
                                            $txt .= 'la persona jurídica de naturaleza comercial denominada ' . $nom1;
                                        }
                                    }
                                }
                            }
                            $pegarNoticia = 'no';
                        }
                        if ($sigla != '') {
                            $txt .= ', Sigla ' . $sigla;
                        }
                    }
                }
            }
        }

        //
        if ($acto == '0197' && $libro == 'RM15') { // Cesacion de actividad
            if ($organizacion == '01') {
                $txt .= 'la cesación de la actidad comercial de la persona natural de nominada ' . $nombre;
            } else {
                if ($organizacion == '02') {
                    $txt .= 'el cierre del establecimiento de comercio denominado ' . $nombre;
                } else {
                    if ($categoria == '2') {
                        $txt .= 'el cierre de la sucursal denominada ' . $nombre;
                    } else {
                        if ($categoria == '3') {
                            $txt .= 'el cierre de la agencia denominada ' . $nombre;
                        } else {
                            $txt .= 'la cesación de la actidad comercial de la persona jurídica denominada ' . $nombre;
                        }
                    }
                }
            }
        }

        //
        if ($acto == '0197' && $libro == 'RM06') { // cierre del establecimiento de comercio
            $txt .= 'el cierre del establecimiento de comercio denominado ' . $nombre;
        }

        // En caso de depuración.
        if ($acto == '0510' && $tipdoc == '38') {
            $txt .= 'la disolución por depuración de acuerdo con lo indicado en la Ley 1727 de 2014.';
            $pegarNoticia = 'no';
        }

        if (($acto == '0530' || $acto == '0540') && $tipdoc == '38') {
            $txt .= 'la cancelación por depuración de acuerdo con lo indicado en la ley 1727 de 2014.';
            $pegarNoticia = 'no';
        }

        //
        if ($acto == '2000' || $acto == '2010') { //
            $txt .= 'la comunicación que se ha configurado una situación de control : ';
        }
        if ($acto == '2020' || $acto == '2030') { //
            $txt .= 'la comunicación que se ha configurado un grupo empresarial : ';
        }

        //
        if ($pegarNoticia == 'si') {
            // $txt .= \funcionesGenerales::parsearOracionNoticia($noticia);
            $txt .= $noticia;
        }

        if (trim($aclaratoria) != '') {
            // $txt .= '<br><br>' . \funcionesGenerales::parsearOracionNoticia($aclaratoria);
            $txt .= '<br><br>' . $aclaratoria;
        }

        return $txt;
    }

    public static function descripcionesDocumentoFormato2019($mysqli, $organizacion, $acto, $tipdoc, $numdoc, $numdocext, $fecdoc, $idorigen, $txtorigen, $munori)
    {

        //
        if ($numdocext != '') {
            $numdoc = $numdocext;
        }

        //
        $txt = '';

        //
        if ($tipdoc == '15' && $numdoc == '1727') {
            $tipdoc = '38';
        }

        //
        if ($tipdoc == '38' && $numdoc == '1727') {
            $numdoc = '';
        }

        $txtDoc = retornarRegistroMysqliApi($mysqli, 'mreg_tipos_documentales_registro', "id='" . $tipdoc . "'", "descripcionlower");
        if ($txtDoc == '') {
            $txtDoc = 'documento';
        }

        //
        $txt .= $txtDoc . ' ';

        //
        if (trim($numdoc) != '' && trim($numdoc) != '0' && strtoupper(trim($numdoc)) != 'NA' && strtoupper(trim($numdoc)) != 'N/A' && strtoupper(trim($numdoc)) != 'SN' && strtoupper(trim($numdoc)) != 'S/N') {
            $txt .= 'No. ' . trim($numdoc) . ' ';
        }

        //
        if ($fecdoc != '' && $tipdoc != '38') {
            $txt .= 'del ' . \funcionesGenerales::mostrarFechaLetras1($fecdoc) . ' ';
        }

        // 
        $txtSuscribe = '';

        //
        if ($tipdoc != '38') {
            if ($txtorigen != '') {
                if (strtoupper(trim($txtorigen)) == 'NO TIENE NO TIENE') {
                    $txtorigen = '';
                }
                $txtorigen = str_replace("NOTARIAS NOTARIA", "Notaría", $txtorigen);
                $txtorigen = str_replace("ACTAS ", "", $txtorigen);
                $txtorigen = str_replace("JUZGADOS CIVILES DEL CIRCUITO ", "", $txtorigen);
                $txtSuscribe = ucwords(strtolower($txtorigen));
            } else {
                if (ltrim($idorigen, "0") != '' && ltrim($idorigen, "0") != '999999') {
                    $txtSuscribe = ucwords(strtolower(retornarNombreTablasSirepMysqliApi($mysqli, '12', $idorigen)));
                }
            }

            //
            $txtParticula = 'de la';

            if (
                strtoupper($txtSuscribe) == 'REPRESENTACION LEGAL' ||
                strtoupper($txtSuscribe) == 'REPRESENTACIÓN LEGAL' ||
                strtoupper($txtSuscribe) == 'REPRESENTACIóN LEGAL'
            ) {
                $txtParticula = 'de';
                $txtSuscribe = 'el Representante Legal';
            }
            if (strtoupper($txtSuscribe) == 'COMERCIANTE') {
                $txtParticula = 'de';
                $txtSuscribe = 'el Comerciante';
            }
            if (strtoupper($txtSuscribe) == 'JUNTA DE SOCIOS') {
                $txtParticula = 'de';
                $txtSuscribe = 'la Junta de Socios';
            }
            if (strtoupper($txtSuscribe) == 'JUNTA DIRECTIVA') {
                $txtParticula = 'de';
                $txtSuscribe = 'la Junta Directiva';
            }
            if (substr($txtSuscribe, 0, 7) == 'JUZGADO') {
                $txtParticula = 'del';
            }
            if (strtoupper($txtSuscribe) == 'PROPIETARIO') {
                $txtParticula = 'de';
                $txtSuscribe = 'el Propietario';
            }
            if (strtoupper($txtSuscribe) == 'ADMON. DE IMPUESTOS NACIONALES') {
                $txtParticula = 'de';
                $txtSuscribe = 'La Administración de Impuestos Nacionales';
            }
            if (strtoupper($txtSuscribe) == 'ACCIONISTAS') {
                $txtParticula = 'de';
                $txtSuscribe = 'Accionistas';
            }

            // 2017-11-21: WSIERRA: Adicionar quien suscribe UNICO ACCIONISTA en control del texto de particula.
            if (strtoupper($txtSuscribe) == 'UNICO ACCIONISTA' || strtoupper($txtSuscribe) == 'ÚNICO ACCIONISTA') {
                $txtParticula = 'de';
                $txtSuscribe = 'Único Accionista';
            }

            // 2018-06-18: JINT
            if (strtoupper($txtSuscribe) == 'ACCIONISTA UNICO' || strtoupper($txtSuscribe) == 'ACCIONISTA ÚNICO') {
                $txtParticula = 'del';
                $txtSuscribe = 'Accionista Único';
            }

            // 2018-06-18: JINT
            if (
                strtoupper($txtSuscribe) == 'COMITE DE ADMINISTRACION' ||
                strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACION' ||
                strtoupper($txtSuscribe) == 'COMITÉ DE ADMINISTRACIÓN'
            ) {
                $txtParticula = 'del';
                $txtSuscribe = 'Comité de Administración';
            }

            // 2018-06-18: JINT
            if (strtoupper($txtSuscribe) == 'REVISOR FISCAL') {
                $txtParticula = 'del';
                $txtSuscribe = 'Revisor Fiscal';
            }

            // 2018-06-26: JINT
            if (strtoupper($txtSuscribe) == 'EL COMERCIANTE') {
                $txtParticula = '';
                $txtSuscribe = '';
            }


            if (strtoupper($txtSuscribe) == 'LA JUNTA DE SOCIOS') {
                if ($organizacion == '11') {
                    if ($acto == '0040') {
                        $txtParticula = '';
                        $txtSuscribe = 'del Empresario Constituyente';
                    }
                }
            }
            if ($txtSuscribe != '') {
                $txt .= trim($txtParticula) . ' ' . $txtSuscribe . ' ';
            }

            //
            if ($tipdoc == '02' || $tipdoc == '04') {
                if ($munori != '' && $munori != '00000' && $munori != '99999') {
                    $txt .= ' de ' . ucwords(strtolower(retornarNombreMunicipioMysqliApi($mysqli, $munori)));
                }
            }

            //
            $txt = str_replace(" , ", ", ", $txt);
            $txt = str_replace(array("DE LA LA", "DE LA EL"), array("DE LA", "DE EL"), $txt);
            $txt = str_replace(array("de la la", "de la el"), array("de la", "de el"), $txt);
            $txt = str_replace(array("de la los", "de la Los"), array("de los", "de Los"), $txt);
        }

        //
        return $txt;
    }

    public static function almacenarDatosImportantesRenovacion($dbx, $liq, $dat, $mom)
    {

        //
        if ($mom == 'F') {
            borrarRegistrosMysqliApi($dbx, 'mreg_renovacion_datos_control', "idliquidacion=" . $liq . " and matricula='" . ltrim($dat["matricula"], "0") . "' and momento='F'");
        }

        //
        $arrCampos = array(
            'idliquidacion',
            'matricula',
            'dato',
            'contenido',
            'momento'
        );

        //
        $arrValores = array();

        //
        foreach ($dat as $key => $valor) {
            if (!is_array($valor)) {
                if (ltrim(trim($valor), "0") != '') {
                    $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'" . $key . "'", "'" . $valor . "'", "'" . $mom . "'");
                }
            } else {
                foreach ($valor as $k1 => $v1) {
                    if (!is_array($v1)) {
                        if (ltrim(trim($v1), "0") != '') {
                            $dx = $key . '|' . $k1;
                            $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'" . $dx . "'", "'" . $v1 . "'", "'" . $mom . "'");
                        }
                    } else {
                        foreach ($v1 as $k2 => $v2) {
                            if (!is_array($v2)) {
                                if (ltrim(trim($v2), "0") != '') {
                                    $dx = $key . '|' . $k1 . '|' . $k2;
                                    $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'" . $dx . "'", "'" . $v2 . "'", "'" . $mom . "'");
                                }
                            }
                        }
                    }
                }
            }
        }

        //
        if (trim($dat["ciius"][1]) != '') {
            $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'ciiu1'", "'" . $dat["ciius"][1] . "'", "'" . $mom . "'"); // Ciiu1
        }
        if (trim($dat["ciius"][2]) != '') {
            $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'ciiu2'", "'" . $dat["ciius"][2] . "'", "'" . $mom . "'"); // Ciiu2
        }
        if (trim($dat["ciius"][3]) != '') {
            $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'ciiu3'", "'" . $dat["ciius"][3] . "'", "'" . $mom . "'"); // Ciiu3
        }
        if (trim($dat["ciius"][4]) != '') {
            $arrValores[] = array($liq, "'" . ltrim($dat["matricula"], "0") . "'", "'ciiu4'", "'" . $dat["ciius"][4] . "'", "'" . $mom . "'"); // Ciiu4
        }

        //
        insertarRegistrosBloqueMysqliApi($dbx, 'mreg_renovacion_datos_control', $arrCampos, $arrValores);
        return true;
    }

    public static function anoBisiesto($ano)
    {
        $ent = intval($ano / 4);
        if ($ano == $ent * 4) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $txt
     * @param type $path
     * @param type $javascriptHeaders
     */
    public static function armarMensaje($txt = '', $path = '', $javascriptHeaders = '', $titulo = '')
    {

        if ($path != '') {
            $_SESSION["generales"]["pathabsoluto"] = $path;
        }

        //
        if (isset($_SESSION["generales"]["pathabsoluto"]) && $_SESSION["generales"]["pathabsoluto"] != '') {
            require_once($_SESSION["generales"]["pathabsoluto"] . '/api/presentacion.class.php');
        } else {
            require_once('presentacion.class.php');
        }

        //
        if (isset($_SESSION["generales"]["codigoempresa"])) {
            require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        }
        if (!isset($_SESSION["generales"]["codigoempresa"])) {
            $acceso = md5(sha1(USUARIO_API_DEFECTO)) . '|' . md5(sha1(TOKEN_API_DEFECTO));
        } else {
            $acceso = md5(sha1(USUARIO_API_DEFECTO)) . '|' . md5(sha1(TOKEN_API_DEFECTO)) . '|' . CODIGO_EMPRESA;
        }
        $acceso = \funcionesGenerales::encrypt_decrypt('encrypt', ENCRYPTION_SECRET_KEY, ENCRYPTION_SECRET_IV, $acceso);

        $pres = new presentacionBootstrap();
        $string = $pres->abrirPanelGeneral(1100);
        $string .= '<br>';

        $string .= $pres->abrirPanel();
        $string .= $pres->abrirFormulario('formMensajes', '', '');
        $string .= $pres->armarCampoTextoOculto('session_parameters', \funcionesGenerales::armarVariablesPantalla());
        $string .= $pres->armarCampoTextoOculto('_accesso', $acceso);
        if (defined('TIPO_HTTP')) {
            $string .= $pres->armarCampoTextoOculto('_tipohttp', TIPO_HTTP);
        }
        if (defined('HTTP_HOST')) {
            $string .= $pres->armarCampoTextoOculto('_httphost', HTTP_HOST);
        }
        if (defined('RAZONSOCIAL')) {
            $string .= $pres->armarEncabezadoPagoElectronico(RAZONSOCIAL);
            $string .= '<br>';
        } else {
            $string .= $pres->armarEncabezadoPagoElectronico('CAMARA DE COMERCIO');
            $string .= '<br>';
        }
        if ($titulo == '') {
            $string .= $pres->armarLineaTextoInformativa('Mensaje informativo', 'center', 'h2');
        } else {
            $string .= $pres->armarLineaTextoInformativa($titulo, 'center', 'h2');
        }
        $string .= '<br>';

        $string .= $pres->armarLineaTextoInformativa($txt, 'center');
        $string .= '<br>';
        $string .= $pres->cerrarPanel();
        $string .= $pres->cerrarPanelGeneral();
        unset($pres);
        \funcionesGenerales::mostrarCuerpoBootstrap($javascriptHeaders, '', '', '', 'no', $string, '', 'plantillaVaciaHttp.html');
        exit();
    }

    public static function armarMensajeSimple($txt = '', $path = '', $javascriptHeaders = '')
    {

        //
        if ($path != '') {
            if (!isset($path)) {
                header("Location:../disparador.php");
                exit();
            }
            if (!file_exists($path . '/api/presentacion.class.php')) {
                header("Location:../disparador.php");
                exit();
            }
            require_once($path . '/api/presentacion.class.php');
        } else {
            if (isset($_SESSION["generales"]["pathabsoluto"]) && $_SESSION["generales"]["pathabsoluto"] != '') {
                if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/api/presentacion.class.php')) {
                    header("Location:../disparador.php");
                    exit();
                }
                require_once($_SESSION["generales"]["pathabsoluto"] . '/api/presentacion.class.php');
            } else {
                if (!file_exists('presentacion.class.php')) {
                    header("Location:../disparador.php");
                    exit();
                }
                require_once('presentacion.class.php');
            }
        }

        //
        $nw = new presentacionBootstrap();
        $string = $nw->abrirPanelGeneral(800);
        $string .= $nw->abrirPanel();
        $string .= $nw->armarLineaTextoInformativa($txt, 'center');
        $string .= $nw->cerrarPanel();
        $string .= $nw->cerrarPanelGeneral();
        unset($nw);
        \funcionesGenerales::mostrarCuerpoBootstrapSimple($javascriptHeaders, '', '', '', '', $string);
        exit();
    }

    public static function armarMensajeMobile($txt = '')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        if (!defined('NOMBRE_SISTEMA1')) {
            (define('NOMBRE_SISTEMA1', ''));
        }
        if (!defined('DIRECCION_CASA_SOFTWARE')) {
            (define('DIRECCION_CASA_SOFTWARE', ''));
        }
        if (!defined('TELEFONO_CASA_SOFTWARE')) {
            (define('TELEFONO_CASA_SOFTWARE', ''));
        }
        if (!defined('CIUDAD_CASA_SOFTWARE')) {
            (define('CIUDAD_CASA_SOFTWARE', ''));
        }
        if (!defined('DECLARACION_PRIVACIDAD')) {
            (define('DECLARACION_PRIVACIDAD', ''));
        }
        require_once($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
        $_SESSION["generales"]["codigoempresa"] = isset($_SESSION["generales"]["codigoempresa"]) ? $_SESSION["generales"]["codigoempresa"] : 999;

        $pant = new template();

        // if (isset($_SERVER["https"])) {
        if (TIPO_HTTP == 'https://') {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/mobileHttps.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/mobileHttp.html');
        }

        if (!isset($_SESSION["generales"]["validado"])) {
            $_SESSION["generales"]["validado"] = '';
        }
        $pant->armarDate();
        $pant->armarBanner(TIPO_HTTP . HTTP_HOST . '/images/top1200x80-' . $_SESSION["generales"]["codigoempresa"] . '.jpg');
        $pant->armarTipoHttp(TIPO_HTTP);
        $pant->armarHttpHost(HTTP_HOST);
        $pant->armarUrlSite(TIPO_HTTP . HTTP_HOST);
        $pant->armarPeriodo(date("Y"));
        $pant->armarTituloAplicacion('SII 1.0 Versi&oacute;n M&oacute;vil');
        $pant->armarTituloMenu('');
        $pant->armarTxtHome('');
        $pant->armarTxtError($txt);
        $pant->armarNombreSistema(NOMBRE_SISTEMA);
        $pant->armarNombreSistema1(NOMBRE_SISTEMA1);
        $pant->armarNombreCasaSoftware(NOMBRE_CASA_SOFTWARE);
        $pant->armarDireccionCasaSoftware(DIRECCION_CASA_SOFTWARE);
        $pant->armarCiudadCasaSoftware(CIUDAD_CASA_SOFTWARE);
        $pant->armarTelefonoCasaSoftware(TELEFONO_CASA_SOFTWARE);
        $pant->armarDeclaracionPrivacidad(DECLARACION_PRIVACIDAD);
        $pant->armarScriptHeader('');
        $pant->armarScriptBody('');
        $pant->armarMenuMovil('');
        if (!isset($_SESSION["generales"]["tipodispositivo"])) {
            $_SESSION["generales"]["tipodispositivo"] = 'tablet';
        }
        $pant->armarTipoDispositivo($_SESSION["generales"]["tipodispositivo"]);
        // $pant->armarSelectAnos($txtanos);
        $pant->armarSelectAnos('');
        $pant->armarEstilo();
        $pant->armarMostrarSombra($sombra);
        $pant->armarScriptEmergente('');
        $_SESSION["generales"]["txtemergente"] = '';
        // $pant->armarBackImage(TIPO_HTTP . HTTP_HOST . '/images/sii/Ejecafetero.jpg');
        $pant->armarCuerpo($cuerpo);
        $pant->mostrar();
        unset($pant);
        exit();
    }

    public static function armarMenuLateralBootstrap()
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        if (!isset($_SESSION["generales"]["codigousuario"]) || $_SESSION["generales"]["codigousuario"] == '') {
            $_SESSION["generales"]["codigousuario"] = 'USUPUBXX';
        }
        if (!isset($_SESSION["generales"]["tipousuario"]) || $_SESSION["generales"]["tipousuario"] == '') {
            $_SESSION["generales"]["tipousuario"] = '00';
        }
        $name = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $_SESSION["generales"]["codigousuario"] . '-' . date("Y") . '.mnu';
        $claveEncriptacion = \funcionesGenerales::encontrarClaveEncriptacion();
        $menu = array();
        $mysqli = conexionMysqliApi('replicabatch');
        $temBandejas = retornarRegistrosMysqliApi($mysqli, 'bas_grupostagsbandejaentrada', "1=1", "id");
        $temOpciones = retornarRegistrosMysqliApi($mysqli, 'bas_opciones', "estado='1'", "idopcion");

        // ************************************************************************************ //
        // Arma el menú de bandejas
        // Solo para usuarios internos
        // ************************************************************************************ //    
        if ($_SESSION["generales"]["tipousuario"] != '00' && $_SESSION["generales"]["tipousuario"] != '06') {
            $arrPerUsu = array();
            $usubantem = retornarRegistrosMysqliApi($mysqli, 'usuariostagsbandejaentrada', "idusuario='" . $_SESSION["generales"]["codigousuario"] . "'", "idtag");
            foreach ($usubantem as $ux) {
                $arrPerUsu[$ux["idtag"]] = 'S';
            }
            unset($usubantem);
            if ($_SESSION["generales"]["tipousuario"] == '01' || !empty($arrPerUsu)) {
                if (TIPO_EMPRESA != 'scs' && TIPO_EMPRESA1 != 'scs' && TIPO_EMPRESA2 != 'scs') {
                    $g = "'B'";
                    $menu[$g] = array();
                    $menu[$g]["nombre"] = 'BANDEJAS';
                    $menu[$g]["submenus"] = array();
                    foreach ($temBandejas as $tx) {
                        $temBandejas1 = retornarRegistrosMysqliApi($mysqli, 'bas_tagsbandejaentrada', "idgrupo='" . $tx["id"] . "'", "idorden");
                        $mostrar = 'no';
                        if ($temBandejas1 && !empty($temBandejas1)) {
                            foreach ($temBandejas1 as $tx1) {
                                if (isset($arrPerUsu[$tx1["id"]])) {
                                    $mostrar = 'si';
                                }
                            }
                        }
                        if (
                            $_SESSION["generales"]["tipousuario"] == '01' ||
                            $mostrar == 'si'
                        ) {
                            if (!empty($temBandejas1)) {
                                $g = "'B'";
                                $s = "'" . $tx["id"] . "'";
                                $menu[$g]["submenus"][$s] = array();
                                $menu[$g]["submenus"][$s]["nombre"] = $tx["descripcion"];
                                $menu[$g]["submenus"][$s]["acciones"] = array();
                                $temBandejas1 = retornarRegistrosMysqliApi($mysqli, 'bas_tagsbandejaentrada', "idgrupo='" . $tx["id"] . "'", "idorden");
                                foreach ($temBandejas1 as $tx1) {
                                    if (
                                        $_SESSION["generales"]["tipousuario"] == '01' ||
                                        isset($arrPerUsu[$tx1["id"]])
                                    ) {
                                        $g = "'B'";
                                        $s = "'" . $tx["id"] . "'";
                                        $a = "'" . $tx1["id"] . "'";
                                        $menu[$g]["submenus"][$s]["acciones"][$a] = array();
                                        $menu[$g]["submenus"][$s]["acciones"][$a]["nombre"] = $tx1["descripcion"];
                                        $arr = \funcionesGenerales::encryptVars();
                                        $arr["controlador"] = $tx1["sii2_controlador"];
                                        $arr["metodo"] = $tx1["sii2_metodo"] . '();';
                                        $arr["parametros"] = array();
                                        if ($tx1["script"] != '') {
                                            $pars = explode("&", $tx1["script"]);
                                            $ipars = -1;
                                            foreach ($pars as $p) {
                                                $ipars++;
                                                if ($ipars > 0) {
                                                    list($var, $dat) = explode("=", $p);
                                                    $arr["parametros"][$var] = $dat;
                                                }
                                            }
                                        }
                                        $arr["link"] = '';
                                        $json = json_encode($arr);
                                        $jsonencrypt = base64_encode(\funcionesGenerales::encrypt_decrypt('encrypt', $claveEncriptacion, $claveEncriptacion, $json));
                                        $menu[$g]["submenus"][$s]["acciones"][$a]["enlace"] = '';
                                        $menu[$g]["submenus"][$s]["acciones"][$a]["enlaceexterno"] = TIPO_HTTP . HTTP_HOST . '/' . str_replace("../../", "", $tx1["script"]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Arma grupo opciones
        foreach ($temOpciones as $tx) {
            $incluir = 'no';
            if ($_SESSION["generales"]["tipousuario"] == '00') {
                if ($tx["tipousuariopublico"] != '') {
                    $incluir = 'si';
                }
            }
            if ($_SESSION["generales"]["tipousuario"] == '01') {
                $incluir = 'si';
            }
            if ($_SESSION["generales"]["tipousuario"] == '02') {
                if ($tx["tipousuarioadministrativo"] != '') {
                    $incluir = 'si';
                }
            }
            if ($_SESSION["generales"]["tipousuario"] == '03') {
                if ($tx["tipousuarioproduccion"] != '') {
                    $incluir = 'si';
                }
            }
            if ($_SESSION["generales"]["tipousuario"] == '04') {
                if ($tx["tipousuarioventas"] != '') {
                    $incluir = 'si';
                }
            }
            if ($_SESSION["generales"]["tipousuario"] == '05') {
                if ($tx["tipousuarioregistro"] != '') {
                    $incluir = 'si';
                }
            }
            if ($_SESSION["generales"]["tipousuario"] == '06') {
                if ($tx["tipousuarioexterno"] != '') {
                    $incluir = 'si';
                }
            }

            if ($incluir == 'si') {
                if ($tx["idtipoopcion"] == "G") {
                    $g = "'" . substr($tx["idopcion"], 0, 2) . "'";
                    $menu[$g] = array();
                    $menu[$g]["nombre"] = $tx["nombre"];
                    $menu[$g]["submenus"] = array();
                }
                if ($tx["idtipoopcion"] == "S") {
                    $g = "'" . substr($tx["idopcion"], 0, 2) . "'";
                    $s = "'" . substr($tx["idopcion"], 3, 2) . "'";
                    if (isset($menu[$g])) {
                        $menu[$g]["submenus"][$s] = array();
                        $menu[$g]["submenus"][$s]["nombre"] = $tx["nombre"];
                        $menu[$g]["submenus"][$s]["acciones"] = array();
                    }
                }
                if ($tx["idtipoopcion"] == "A") {
                    $g = "'" . substr($tx["idopcion"], 0, 2) . "'";
                    $s = "'" . substr($tx["idopcion"], 3, 2) . "'";
                    $a = "'" . substr($tx["idopcion"], 6, 3) . "'";
                    if (isset($menu["$g"])) {
                        if (isset($menu[$g]["submenus"][$s])) {
                            $menu[$g]["submenus"][$s]["acciones"][$a] = array();
                            $menu[$g]["submenus"][$s]["acciones"][$a]["nombre"] = $tx["nombre"];
                            $arr = \funcionesGenerales::encryptVars();
                            $arr["controlador"] = trim($tx["script"]);
                            $arr["metodo"] = '';
                            $arr["parametros"] = array();
                            if (PLANTILLA_HTML == 'bootstrap') {
                                if (substr($tx["enlace"], 0, 3) == '../') {
                                    $link = TIPO_HTTP . HTTP_HOST . '/' . str_replace(array("../../", "../"), "", str_replace("&ocultarencabezados=si", "", $tx["enlace"])) . '&ocultarencabezados=no&linkretornoindex=no';
                                } else {
                                    $link = $tx["enlace"];
                                }
                                $menu[$g]["submenus"][$s]["acciones"][$a]["enlace"] = '';
                                $menu[$g]["submenus"][$s]["acciones"][$a]["enlaceexterno"] = $link . '&session_parameters=' . \funcionesGenerales::armarVariablesPantalla();
                                $menu[$g]["submenus"][$s]["acciones"][$a]["target"] = '_blank';
                            } else {
                                if ($tx["script"] != '') {
                                    if (trim($tx["enlace"]) != '') {
                                        $pars = explode("&", trim($tx["enlace"]));
                                        $ipars = -1;
                                        foreach ($pars as $p) {
                                            $ipars++;
                                            if ($ipars == 0) {
                                                if (strpos($p, "?")) {
                                                    list($izq, $der) = explode("=", $p);
                                                    $arr["metodo"] = $der . '();';
                                                }
                                            }
                                            if ($ipars > 0) {
                                                list($var, $dat) = explode("=", $p);
                                                $arr["parametros"][$var] = $dat;
                                            }
                                        }
                                    }
                                }
                                $arr["link"] = $tx["enlace"];
                                $json = json_encode($arr);
                                $jsonencrypt = base64_encode(\funcionesGenerales::encrypt_decrypt('encrypt', $claveEncriptacion, $claveEncriptacion, $json));
                                $menu[$g]["submenus"][$s]["acciones"][$a]["enlace"] = '';
                                $menu[$g]["submenus"][$s]["acciones"][$a]["enlaceexterno"] = TIPO_HTTP . HTTP_HOST . '/' . str_replace("../../", "", $tx["enlace"]);
                            }
                        }
                    }
                }
            }
        }
        unset($temBandejas);
        unset($temOpciones);

        $txt = '';
        foreach ($menu as $m) {
            $txt .= '<li class="nav-item" data-toggle="tooltip" data-placement="right" title="' . $m["nombre"] . '">
                    <a class="nav-link nav-link-collapse collapsed" data-toggle="collapse" href="#collapse_' . str_replace(" ", "_", $m["nombre"]) . '" data-parent="#exampleAccordion">
                    <i class="fa fa-fw fa-sitemap"></i>
                    <span class="nav-link-text">' . $m["nombre"] . '</span>
                    </a>
                    <ul class="sidenav-second-level collapse" id="collapse_' . str_replace(" ", "_", $m["nombre"]) . '">';
            foreach ($m["submenus"] as $s) {
                $txt .= '<li>
                    <a class="nav-link-collapse collapsed" data-toggle="collapse" href="#collapse_' . str_replace(" ", "_", $s["nombre"]) . '"><i class="fa fa-fw fa-sitemap"></i>' . $s["nombre"] . '</a>
                    <ul class="sidenav-third-level collapse" id="collapse_' . str_replace(" ", "_", $s["nombre"]) . '">';
                foreach ($s["acciones"] as $a) {
                    if ($a["enlace"] != '') {
                        $txt .= '<li><a href="' . $a["enlace"] . '">' . $a["nombre"] . '</a></li>';
                    } else {
                        if ($a["enlaceexterno"] != '') {
                            if (!isset($a["target"]) || $a["target"] == '') {
                                $a["target"] = '_blank';
                            }
                            $txt .= '<li><a href="' . $a["enlaceexterno"] . '" target="' . $a["target"] . '">' . $a["nombre"] . ' (*)</a></li>';
                        }
                    }
                }
                $txt .= '</ul></li>';
            }
            $txt .= '</ul></li>';
        }
        $f = fopen($name, "w");
        fwrite($f, $txt);
        fclose($f);
        $mysqli->close();
        return $txt;
    }

    /**
     * Asigna las variables de sesion necesarias para ejecutar los procesos internos
     * 
     * @param type $dbx
     * @param type $entrada
     * @return boolean
     */
    public static function asignarVariablesSession($dbx, $entrada)
    {

        $_SESSION["generales"]["zonahoraria"] = "America/Bogota";
        $_SESSION["generales"]["idioma"] = "es";
        $_SESSION["generales"]["navegador"] = \funcionesGenerales::obtenerNavegador(getenv("HTTP_USER_AGENT"));
        $_SESSION["generales"]["codigousuario"] = 'USUPUBXX';
        $_SESSION["generales"]["tipousuario"] = '00';
        $_SESSION["generales"]["emailusuariocontrol"] = $entrada["emailcontrol"];
        $_SESSION["generales"]["identificacionusuariocontrol"] = $entrada["identificacioncontrol"];
        $_SESSION["generales"]["celularusuariocontrol"] = $entrada["celularcontrol"];
        $_SESSION["generales"]["validado"] = 'NO';
        $_SESSION["generales"]["escajero"] = 'NO';
        $_SESSION["generales"]["tipousuariocontrol"] = 'usuarioanonimo';
        $_SESSION["generales"]["sedeusuario"] = '99';
        $_SESSION["generales"]["gastoadministrativo"] = 'NO';
        $_SESSION["generales"]["esdispensador"] = 'NO';
        $_SESSION["generales"]["escensador"] = 'NO';
        $_SESSION["generales"]["esbrigadista"] = 'NO';
        $_SESSION["generales"]["puedecerrarcaja"] = 'NO';
        $_SESSION["generales"]["visualizatotales"] = 'NO';
        $_SESSION["generales"]["esrue"] = 'NO';
        $_SESSION["generales"]["eswww"] = 'SI';
        $_SESSION["generales"]["esreversion"] = 'NO';
        $_SESSION["generales"]["essa"] = 'NO';
        $_SESSION["generales"]["esbanco"] = 'NO';
        $_SESSION["generales"]["abogadocoordinador"] = 'NO';
        $_SESSION["generales"]["loginemailusuario"] = $entrada["emailcontrol"];
        $_SESSION["generales"]["perfildocumentacion"] = '';
        $_SESSION["generales"]["controlapresupuesto"] = '';
        $_SESSION["generales"]["idtipoidentificacionusuario"] = $entrada["identificacioncontrol"];
        $_SESSION["generales"]["identificacionusuario"] = $entrada["identificacioncontrol"];
        $_SESSION["generales"]["nitempresausuario"] = '';
        $_SESSION["generales"]["nombreempresausuario"] = '';
        $_SESSION["generales"]["direccionusuario"] = '';
        $_SESSION["generales"]["idmuniciopiousuario"] = '';
        $_SESSION["generales"]["telefonousuario"] = '';
        $_SESSION["generales"]["movilusuario"] = $entrada["celularcontrol"];
        $_SESSION["generales"]["operadorsirepusuario"] = '';
        $_SESSION["generales"]["ccosusuario"] = '';
        $_SESSION["generales"]["cargousuario"] = '';
        $_SESSION["generales"]["nombreempresa"] = '';
        $_SESSION["generales"]["idcodigosirepcaja"] = '';
        $_SESSION["generales"]["idcodigosirepdigitacion"] = '';
        $_SESSION["generales"]["idcodigosirepregistro"] = '';
        $_SESSION["generales"]["habilitadobiometria"] = 'NO';
        $_SESSION["generales"]["administradorbiometria"] = 'NO';
        $_SESSION["generales"]["controlverificacion"] = '';
        $_SESSION["generales"]["fechaactivacion"] = '';
        $_SESSION["generales"]["fechainactivacion"] = '';
        $_SESSION["generales"]["fechacambioclave"] = '';

        if ($entrada["idusuario"] == 'USUPUBXX') {
            $tusu = '';
            if (!isset($entrada["tipoliquidacion"]) || $entrada["tipoliquidacion"] == 'L') {
                $ok = \funcionesGenerales::validarSuscripcionNacional($_SESSION["entrada"]["emailcontrol"], $_SESSION["entrada"]["identificacioncontrol"]);
                if ($ok["codigoerror"] != '0000') {
                    $query = "email='" . $entrada["emailcontrol"] . "' and identificacion='" . $entrada["identificacioncontrol"] . "'";
                    $temx = retornarRegistroMysqliApi($dbx, 'usuarios_verificados', $query);
                    if ($temx === false || empty($temx)) {
                        $temx = retornarRegistroMysqliApi($dbx, 'usuarios_registrados', $query);
                        if ($temx === false || empty($temx)) {
                            return false;
                        } else {
                            $tusu = 'usuarioregistrado';
                        }
                    } else {
                        $tusu = 'usuarioverificado';
                    }
                } else {
                    $tusu = 'usuarioverificado';
                }
            } else {
                $tusu = 'usuarioanonimo';
            }
            $_SESSION["generales"]["validado"] = 'SI';
            $_SESSION["generales"]["escajero"] = 'NO';
            $_SESSION["generales"]["tipousuariocontrol"] = $tusu;
            $_SESSION["generales"]["sedeusuario"] = '99';
            $_SESSION["generales"]["gastoadministrativo"] = 'NO';
            $_SESSION["generales"]["esdispensador"] = 'NO';
            $_SESSION["generales"]["escensador"] = 'NO';
            $_SESSION["generales"]["esbrigadista"] = 'NO';
            $_SESSION["generales"]["puedecerrarcaja"] = 'NO';
            $_SESSION["generales"]["visualizatotales"] = 'NO';
            $_SESSION["generales"]["esrue"] = 'NO';
            $_SESSION["generales"]["eswww"] = 'SI';
            $_SESSION["generales"]["esreversion"] = 'NO';
            $_SESSION["generales"]["essa"] = 'NO';
            $_SESSION["generales"]["esbanco"] = 'NO';
            $_SESSION["generales"]["abogadocoordinador"] = 'NO';
            $_SESSION["generales"]["loginemailusuario"] = $entrada["emailcontrol"];
            $_SESSION["generales"]["perfildocumentacion"] = '';
            $_SESSION["generales"]["controlapresupuesto"] = '';
            $_SESSION["generales"]["idtipoidentificacionusuario"] = $entrada["identificacioncontrol"];
            $_SESSION["generales"]["identificacionusuario"] = $entrada["identificacioncontrol"];
            $_SESSION["generales"]["nitempresausuario"] = '';
            $_SESSION["generales"]["nombreempresausuario"] = '';
            $_SESSION["generales"]["direccionusuario"] = '';
            $_SESSION["generales"]["idmuniciopiousuario"] = '';
            $_SESSION["generales"]["telefonousuario"] = '';
            $_SESSION["generales"]["movilusuario"] = $entrada["celularcontrol"];
            $_SESSION["generales"]["operadorsirepusuario"] = '';
            $_SESSION["generales"]["ccosusuario"] = '';
            $_SESSION["generales"]["cargousuario"] = '';
            $_SESSION["generales"]["nombreempresa"] = '';
            $_SESSION["generales"]["idcodigosirepcaja"] = '';
            $_SESSION["generales"]["idcodigosirepdigitacion"] = '';
            $_SESSION["generales"]["idcodigosirepregistro"] = '';
            $_SESSION["generales"]["controlverificacion"] = '';
            $_SESSION["generales"]["fechaactivacion"] = '';
            $_SESSION["generales"]["fechainactivacion"] = '';
            $_SESSION["generales"]["fechacambioclave"] = '';
        } else {
            $temx = retornarRegistroMysqliApi($dbx, "usuarios", "idusuario='" . $entrada["idusuario"] . "'");
            if ($temx === false || empty($temx)) {
                return false;
            }
            $_SESSION["generales"]["codigousuario"] = $entrada["idusuario"];
            $_SESSION["generales"]["tipousuario"] = $temx["idtipousuario"];
            $_SESSION["generales"]["emailusuariocontrol"] = $temx["email"];
            $_SESSION["generales"]["identificacionusuariocontrol"] = $temx["identificacion"];
            $_SESSION["generales"]["celularusuariocontrol"] = $temx["celular"];
            $_SESSION["generales"]["validado"] = 'SI';
            $_SESSION["generales"]["escajero"] = $temx["escajero"];
            $_SESSION["generales"]["tipousuariocontrol"] = 'usuariointerno';
            $_SESSION["generales"]["sedeusuario"] = $temx["idsede"];
            if ($entrada["idusuario"] == 'RUE') {
                $_SESSION["generales"]["sedeusuario"] = '90';
            }
            if ($entrada["idusuario"] == 'RUE') {
                $_SESSION["generales"]["sedeusuario"] = $temx["idsede"];
            }
            if ($temx["idtipousuario"] == '06') {
                $_SESSION["generales"]["sedeusuario"] = '98';
            }

            if ($temx["esbanco"] == 'SI') {
                $_SESSION["generales"]["sedeusuario"] = '97';
            }
            $_SESSION["generales"]["gastoadministrativo"] = $temx["gastoadministrativo"];
            $_SESSION["generales"]["esdispensador"] = $temx["esdispensador"];
            $_SESSION["generales"]["escensador"] = $temx["escensador"];
            $_SESSION["generales"]["esbrigadista"] = $temx["esbrigadista"];
            $_SESSION["generales"]["puedecerrarcaja"] = $temx["puedecerrarcaja"];
            $_SESSION["generales"]["visualizatotales"] = $temx["visualizatotales"];
            $_SESSION["generales"]["esrue"] = $temx["esrue"];
            $_SESSION["generales"]["eswww"] = $temx["eswww"];
            $_SESSION["generales"]["esreversion"] = $temx["esreversion"];
            $_SESSION["generales"]["essa"] = $temx["essa"];
            $_SESSION["generales"]["esbanco"] = $temx["esbanco"];
            $_SESSION["generales"]["abogadocoordinador"] = $temx["abogadocoordinador"];
            $_SESSION["generales"]["loginemailusuario"] = $temx["email"];
            $_SESSION["generales"]["perfildocumentacion"] = $temx["idperfildocumentacion"];
            $_SESSION["generales"]["controlapresupuesto"] = $temx["controlapresupuesto"];
            $_SESSION["generales"]["idtipoidentificacionusuario"] = $temx["idtipoidentificacion"];
            $_SESSION["generales"]["identificacionusuario"] = $temx["identificacion"];
            $_SESSION["generales"]["nitempresausuario"] = $temx["nitempresa"];
            $_SESSION["generales"]["nombreempresausuario"] = $temx["nombreempresa"];
            $_SESSION["generales"]["direccionusuario"] = $temx["direccion"];
            $_SESSION["generales"]["idmuniciopiousuario"] = $temx["idmunicipio"];
            $_SESSION["generales"]["telefonousuario"] = $temx["telefonos"];
            $_SESSION["generales"]["movilusuario"] = $temx["celular"];
            $_SESSION["generales"]["operadorsirepusuario"] = '';
            $_SESSION["generales"]["ccosusuario"] = '';
            $_SESSION["generales"]["cargousuario"] = '';
            $_SESSION["generales"]["nombreempresa"] = '';
            $_SESSION["generales"]["idcodigosirepcaja"] = $temx["idcodigosirepcaja"];
            $_SESSION["generales"]["idcodigosirepdigitacion"] = $temx["idcodigosirepdigitacion"];
            $_SESSION["generales"]["idcodigosirepregistro"] = $temx["idcodigosirepregistro"];
            $_SESSION["generales"]["habilitadobiometria"] = $temx["habilitadobiometria"];
            $_SESSION["generales"]["administradorbiometria"] = $temx["administradorbiometria"];
            $_SESSION["generales"]["controlverificacion"] = '';
            $_SESSION["generales"]["fechaactivacion"] = $temx["fechaactivacion"];
            $_SESSION["generales"]["fechainactivacion"] = $temx["fechainactivacion"];
            $_SESSION["generales"]["fechacambioclave"] = '';
            $_SESSION["generales"]["validado"] = 'NO';
            $_SESSION["generales"]["mensajeerror"] = '';
            $_SESSION["generales"]["pagina"] = '';
            $_SESSION["generales"]["disco"] = '001';
            $_SESSION["generales"]["tipodoc"] = 'mreg';
            $_SESSION["generales"]["sega"] = 'PPAL';
        }

        return true;
    }

    /**
     * 
     * @param type $dbx
     * @param type $entrada
     * @return bool
     */
    public static function asignarVariablesSessionSinValidarUsuario($dbx, $entrada)
    {

        $_SESSION["generales"]["zonahoraria"] = "America/Bogota";
        $_SESSION["generales"]["idioma"] = "es";
        $_SESSION["generales"]["navegador"] = \funcionesGenerales::obtenerNavegador(getenv("HTTP_USER_AGENT"));
        $_SESSION["generales"]["codigousuario"] = 'USUPUBXX';
        $_SESSION["generales"]["tipousuario"] = '00';
        $_SESSION["generales"]["emailusuariocontrol"] = $entrada["emailcontrol"];
        $_SESSION["generales"]["identificacionusuariocontrol"] = $entrada["identificacioncontrol"];
        $_SESSION["generales"]["celularusuariocontrol"] = $entrada["celularcontrol"];
        $_SESSION["generales"]["validado"] = 'NO';
        $_SESSION["generales"]["escajero"] = 'NO';
        $_SESSION["generales"]["tipousuariocontrol"] = 'usuarioanonimo';
        $_SESSION["generales"]["sedeusuario"] = '99';
        $_SESSION["generales"]["gastoadministrativo"] = 'NO';
        $_SESSION["generales"]["esdispensador"] = 'NO';
        $_SESSION["generales"]["escensador"] = 'NO';
        $_SESSION["generales"]["esbrigadista"] = 'NO';
        $_SESSION["generales"]["puedecerrarcaja"] = 'NO';
        $_SESSION["generales"]["visualizatotales"] = 'NO';
        $_SESSION["generales"]["esrue"] = 'NO';
        $_SESSION["generales"]["eswww"] = 'SI';
        $_SESSION["generales"]["esreversion"] = 'NO';
        $_SESSION["generales"]["essa"] = 'NO';
        $_SESSION["generales"]["esbanco"] = 'NO';
        $_SESSION["generales"]["abogadocoordinador"] = 'NO';
        $_SESSION["generales"]["loginemailusuario"] = $entrada["emailcontrol"];
        $_SESSION["generales"]["perfildocumentacion"] = '';
        $_SESSION["generales"]["controlapresupuesto"] = '';
        $_SESSION["generales"]["idtipoidentificacionusuario"] = $entrada["identificacioncontrol"];
        $_SESSION["generales"]["identificacionusuario"] = $entrada["identificacioncontrol"];
        $_SESSION["generales"]["nitempresausuario"] = '';
        $_SESSION["generales"]["nombreempresausuario"] = '';
        $_SESSION["generales"]["direccionusuario"] = '';
        $_SESSION["generales"]["idmuniciopiousuario"] = '';
        $_SESSION["generales"]["telefonousuario"] = '';
        $_SESSION["generales"]["movilusuario"] = $entrada["celularcontrol"];
        $_SESSION["generales"]["operadorsirepusuario"] = '';
        $_SESSION["generales"]["ccosusuario"] = '';
        $_SESSION["generales"]["cargousuario"] = '';
        $_SESSION["generales"]["nombreempresa"] = '';
        $_SESSION["generales"]["idcodigosirepcaja"] = '';
        $_SESSION["generales"]["idcodigosirepdigitacion"] = '';
        $_SESSION["generales"]["idcodigosirepregistro"] = '';
        $_SESSION["generales"]["habilitadobiometria"] = 'NO';
        $_SESSION["generales"]["administradorbiometria"] = 'NO';
        $_SESSION["generales"]["controlverificacion"] = '';
        $_SESSION["generales"]["fechaactivacion"] = '';
        $_SESSION["generales"]["fechainactivacion"] = '';
        $_SESSION["generales"]["fechacambioclave"] = '';

        if ($entrada["idusuario"] == 'USUPUBXX') {
            $tusu = '';
            $ok = \funcionesGenerales::validarSuscripcionNacional($_SESSION["entrada"]["emailcontrol"], $_SESSION["entrada"]["identificacioncontrol"]);
            if ($ok["codigoerror"] != '0000') {
                $query = "email='" . $entrada["emailcontrol"] . "' and identificacion='" . $entrada["identificacioncontrol"] . "'";
                $temx = retornarRegistroMysqliApi($dbx, 'usuarios_verificados', $query);
                if ($temx === false || empty($temx)) {
                    $temx = retornarRegistroMysqliApi($dbx, 'usuarios_registrados', $query);
                    if ($temx === false || empty($temx)) {
                        $tusu = 'usuarioanonimno';
                    } else {
                        $tusu = 'usuarioregistrado';
                    }
                } else {
                    $tusu = 'usuarioverificado';
                }
            } else {
                $tusu = 'usuarioverificado';
            }
            $_SESSION["generales"]["validado"] = 'SI';
            $_SESSION["generales"]["escajero"] = 'NO';
            $_SESSION["generales"]["tipousuariocontrol"] = $tusu;
            $_SESSION["generales"]["sedeusuario"] = '99';
            $_SESSION["generales"]["gastoadministrativo"] = 'NO';
            $_SESSION["generales"]["esdispensador"] = 'NO';
            $_SESSION["generales"]["escensador"] = 'NO';
            $_SESSION["generales"]["esbrigadista"] = 'NO';
            $_SESSION["generales"]["puedecerrarcaja"] = 'NO';
            $_SESSION["generales"]["visualizatotales"] = 'NO';
            $_SESSION["generales"]["esrue"] = 'NO';
            $_SESSION["generales"]["eswww"] = 'SI';
            $_SESSION["generales"]["esreversion"] = 'NO';
            $_SESSION["generales"]["essa"] = 'NO';
            $_SESSION["generales"]["esbanco"] = 'NO';
            $_SESSION["generales"]["abogadocoordinador"] = 'NO';
            $_SESSION["generales"]["loginemailusuario"] = $entrada["emailcontrol"];
            $_SESSION["generales"]["perfildocumentacion"] = '';
            $_SESSION["generales"]["controlapresupuesto"] = '';
            $_SESSION["generales"]["idtipoidentificacionusuario"] = $entrada["identificacioncontrol"];
            $_SESSION["generales"]["identificacionusuario"] = $entrada["identificacioncontrol"];
            $_SESSION["generales"]["nitempresausuario"] = '';
            $_SESSION["generales"]["nombreempresausuario"] = '';
            $_SESSION["generales"]["direccionusuario"] = '';
            $_SESSION["generales"]["idmuniciopiousuario"] = '';
            $_SESSION["generales"]["telefonousuario"] = '';
            $_SESSION["generales"]["movilusuario"] = $entrada["celularcontrol"];
            $_SESSION["generales"]["operadorsirepusuario"] = '';
            $_SESSION["generales"]["ccosusuario"] = '';
            $_SESSION["generales"]["cargousuario"] = '';
            $_SESSION["generales"]["nombreempresa"] = '';
            $_SESSION["generales"]["idcodigosirepcaja"] = '';
            $_SESSION["generales"]["idcodigosirepdigitacion"] = '';
            $_SESSION["generales"]["idcodigosirepregistro"] = '';
            $_SESSION["generales"]["controlverificacion"] = '';
            $_SESSION["generales"]["fechaactivacion"] = '';
            $_SESSION["generales"]["fechainactivacion"] = '';
            $_SESSION["generales"]["fechacambioclave"] = '';
        } else {
            $temx = retornarRegistroMysqliApi($dbx, "usuarios", "idusuario='" . $entrada["idusuario"] . "'");
            if ($temx === false || empty($temx)) {
                return false;
            }
            $_SESSION["generales"]["codigousuario"] = $entrada["idusuario"];
            $_SESSION["generales"]["tipousuario"] = $temx["idtipousuario"];
            $_SESSION["generales"]["emailusuariocontrol"] = $temx["email"];
            $_SESSION["generales"]["identificacionusuariocontrol"] = $temx["identificacion"];
            $_SESSION["generales"]["celularusuariocontrol"] = $temx["celular"];
            $_SESSION["generales"]["validado"] = 'SI';
            $_SESSION["generales"]["escajero"] = $temx["escajero"];
            $_SESSION["generales"]["tipousuariocontrol"] = 'usuariointerno';
            $_SESSION["generales"]["sedeusuario"] = $temx["idsede"];
            if ($entrada["idusuario"] == 'RUE') {
                $_SESSION["generales"]["sedeusuario"] = '90';
            }
            if ($entrada["idusuario"] == 'RUE') {
                $_SESSION["generales"]["sedeusuario"] = $temx["idsede"];
            }
            if ($temx["idtipousuario"] == '06') {
                $_SESSION["generales"]["sedeusuario"] = '98';
            }

            if ($temx["esbanco"] == 'SI') {
                $_SESSION["generales"]["sedeusuario"] = '97';
            }
            $_SESSION["generales"]["gastoadministrativo"] = $temx["gastoadministrativo"];
            $_SESSION["generales"]["esdispensador"] = $temx["esdispensador"];
            $_SESSION["generales"]["escensador"] = $temx["escensador"];
            $_SESSION["generales"]["esbrigadista"] = $temx["esbrigadista"];
            $_SESSION["generales"]["puedecerrarcaja"] = $temx["puedecerrarcaja"];
            $_SESSION["generales"]["visualizatotales"] = $temx["visualizatotales"];
            $_SESSION["generales"]["esrue"] = $temx["esrue"];
            $_SESSION["generales"]["eswww"] = $temx["eswww"];
            $_SESSION["generales"]["esreversion"] = $temx["esreversion"];
            $_SESSION["generales"]["essa"] = $temx["essa"];
            $_SESSION["generales"]["esbanco"] = $temx["esbanco"];
            $_SESSION["generales"]["abogadocoordinador"] = $temx["abogadocoordinador"];
            $_SESSION["generales"]["loginemailusuario"] = $temx["email"];
            $_SESSION["generales"]["perfildocumentacion"] = $temx["idperfildocumentacion"];
            $_SESSION["generales"]["controlapresupuesto"] = $temx["controlapresupuesto"];
            $_SESSION["generales"]["idtipoidentificacionusuario"] = $temx["idtipoidentificacion"];
            $_SESSION["generales"]["identificacionusuario"] = $temx["identificacion"];
            $_SESSION["generales"]["nitempresausuario"] = $temx["nitempresa"];
            $_SESSION["generales"]["nombreempresausuario"] = $temx["nombreempresa"];
            $_SESSION["generales"]["direccionusuario"] = $temx["direccion"];
            $_SESSION["generales"]["idmuniciopiousuario"] = $temx["idmunicipio"];
            $_SESSION["generales"]["telefonousuario"] = $temx["telefonos"];
            $_SESSION["generales"]["movilusuario"] = $temx["celular"];
            $_SESSION["generales"]["operadorsirepusuario"] = '';
            $_SESSION["generales"]["ccosusuario"] = '';
            $_SESSION["generales"]["cargousuario"] = '';
            $_SESSION["generales"]["nombreempresa"] = '';
            $_SESSION["generales"]["idcodigosirepcaja"] = $temx["idcodigosirepcaja"];
            $_SESSION["generales"]["idcodigosirepdigitacion"] = $temx["idcodigosirepdigitacion"];
            $_SESSION["generales"]["idcodigosirepregistro"] = $temx["idcodigosirepregistro"];
            $_SESSION["generales"]["habilitadobiometria"] = $temx["habilitadobiometria"];
            $_SESSION["generales"]["administradorbiometria"] = $temx["administradorbiometria"];
            $_SESSION["generales"]["controlverificacion"] = '';
            $_SESSION["generales"]["fechaactivacion"] = $temx["fechaactivacion"];
            $_SESSION["generales"]["fechainactivacion"] = $temx["fechainactivacion"];
            $_SESSION["generales"]["fechacambioclave"] = '';
            $_SESSION["generales"]["validado"] = 'NO';
            $_SESSION["generales"]["mensajeerror"] = '';
            $_SESSION["generales"]["pagina"] = '';
            $_SESSION["generales"]["disco"] = '001';
            $_SESSION["generales"]["tipodoc"] = 'mreg';
            $_SESSION["generales"]["sega"] = 'PPAL';
        }

        return true;
    }

    public static function asignarNumeroRecuperacion($dbx, $tipo)
    {
        $OK = 'NO';
        while ($OK == 'NO') {
            if ($tipo == 'mreg') {
                $num = strtoupper(trim(\funcionesGenerales::generarAleatorioAlfanumerico(6)));
                if (contarRegistrosMysqliApi($dbx, 'mreg_liquidacion', "numerorecuperacion='" . trim($num) . "'") == 0) {
                    $OK = "SI";
                } else {
                    $num = strtoupper(trim(\funcionesGenerales::generarAleatorioAlfanumerico(6)));
                }
            } else {
                if ($tipo == 'news') {
                    $num = strtoupper(trim(\funcionesGenerales::generarAleatorioAlfanumerico(10)));
                    if (contarRegistrosMysqliApi($dbx, 'news', "numerorecuperacion='" . trim($num) . "'") == 0) {
                        $OK = "SI";
                    } else {
                        $num = strtoupper(trim(\funcionesGenerales::generarAleatorioAlfanumerico(10)));
                    }
                } else {
                    $num = strtoupper(trim(\funcionesGenerales::generarAleatorioAlfanumerico(6)));
                }
            }
        }
        return $num;
    }

    public static function emailAvisoLegal()
    {
        $txt = 'AVISO LEGAL Y DE CONFIDENCIALIDAD: La información aquí contenida y anexada es para uso exclusivo de la persona o entidad de destino. ';
        $txt .= 'Está estrictamente prohibida su utilización, copia, descarga, distribución, modificación y/o reproducción total o parcial, sin el permiso ';
        $txt .= 'expreso de la entidad remitente, ya que su contenido puede ser de carácter confidencial y/o contener material privilegiado. ';
        $txt .= 'Si usted recibió esta información por error, por favor contacte en forma inmediata a quien lo envió y borre este material de su computador. ';
        return $txt;
    }

    public static function encomillar($cadenaBusq = '')
    {
        if (trim($cadenaBusq) != '') {
            $arrBusqueda = explode(",", $cadenaBusq);

            $arrSal = array();
            foreach ($arrBusqueda as $reg) {
                $arrSal[] = "'" . $reg . "'";
            }
            return implode(",", $arrSal);
        } else {
            return '';
        }
    }

    public static function enteroval($valor)
    {
        if ($valor < 0) {
            $signo = '-';
        } else {
            $signo = '';
        }
        $valor = str_replace(array(",", "-"), "", (string) $valor);
        if (trim((string) $valor) == '')
            $valor = 0;
        if (!is_numeric($valor))
            $valor = 0;
        $aval = explode(".", $valor);
        return $signo . $aval["0"];
    }

    public static function encontrarClaveEncriptacion()
    {
        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 's44s0nl1n32018';
        } else {
            $claveEncriptacion = CLAVE_ENCRIPTACION;
        }
        return $claveEncriptacion;
    }

    public static function encontrarExtension($file)
    {
        $filename = strtolower($file);
        $exts = explode(".", $filename);
        $n = count($exts) - 1;
        $exts1 = $exts[$n];
        $exts2 = '';
        $arr1 = str_split($exts1);
        if ($arr1 && !empty($arr1)) {
            $fin = 'no';
            foreach ($arr1 as $x1) {
                if ($fin == 'no') {
                    if ($x1 != '?' && $x1 != '&') {
                        $exts2 .= $x1;
                    } else {
                        $fin = 'si';
                    }
                }
            }
        }
        unset($arr1);
        unset($exts);
        return $exts2;
    }

    public static function encontrarHabil($mysqli, $meses, $fec)
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        $ano1 = intval(substr($fec, 0, 4));
        $mes1 = intval(substr($fec, 4, 2));
        $dia1 = intval(substr($fec, 6, 2));
        $mes1 = $mes1 + $meses;
        if ($mes1 == 13) {
            $ano1 = $ano1 + 1;
            $mes1 = 1;
        }
        if ($mes1 == 14) {
            $ano1 = $ano1 + 1;
            $mes1 = 2;
        }
        if ($dia1 == 31) {
            if ($mes1 == 2) {
                if (\funcionesGenerales::anoBisiesto($ano1)) {
                    $dia1 = 29;
                } else {
                    $dia1 = 28;
                }
            }
            if ($mes1 == 4 || $mes1 == 6 || $mes1 == 9 || $mes1 == 11) {
                $dia1 = 30;
            }
        } else {
            if ($dia1 == 30) {
                if ($mes1 == 2) {
                    if (anoBisiesto($ano1)) {
                        $dia1 = 29;
                    } else {
                        $dia1 = 28;
                    }
                }
            } else {
                if ($dia1 == 29) {
                    if ($mes1 == 2) {
                        if (!\funcionesGenerales::anoBisiesto($ano1)) {
                            $dia1 = 28;
                        }
                    }
                }
            }
        }

        $flimite = sprintf("%04s", $ano1) . sprintf("%02s", $mes1) . sprintf("%02s", $dia1);

        // Si la fecha límite no ha llegado aún, sa sale
        if ($flimite > date("Ymd")) {
            return false;
        }

        //
        $calslimite = retornarRegistroMysqliApi($mysqli, 'bas_calendario', "fecha='" . $flimite . "'");
        if ($calslimite["tipodia"] == 'H') {
            if ($flimite < date("Ymd")) {
                return true;
            } else {
                return false;
            }
        } else {
            $frespuesta = '';
            $calslimite = retornarRegistrosMysqliApi($mysqli, 'bas_calendario', "fecha>'" . $flimite . "'", "fecha", '*', 0, 10);
            if ($calslimite && !empty($calslimite)) {
                foreach ($calslimite as $cal) {
                    if ($cal["tipodia"] == 'H') {
                        if ($frespuesta == '') {
                            $frespuesta = $cal["fecha"];
                        }
                    }
                }
            }
            if ($frespuesta == '') {
                return false;
            } else {
                if ($frespuesta < date("Ymd")) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * 
     * @param type $action
     * @param type $secret_key
     * @param type $secret_iv
     * @param type $string
     * @return type
     */
    public static function encrypt_decrypt($action, $secret_key, $secret_iv, $string, $encrypt_method = "AES-256-CBC")
    {
        $output = false;
        // $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }

    public static function encryptVars()
    {

        $arr = array();
        $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
        $arr["codigousuario"] = $_SESSION["generales"]["codigousuario"];

        if (!isset($_SESSION["generales"]["tipousuario"])) {
            $_SESSION["generales"]["tipousuario"] = '';
        }
        $arr["tipousuario"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["tipousuario"]);

        if (!isset($_SESSION["generales"]["validado"])) {
            $_SESSION["generales"]["validado"] = 'NO';
        }
        $arr["validado"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["validado"]);

        if (!isset($_SESSION["generales"]["escajero"])) {
            $_SESSION["generales"]["escajero"] = '';
        }
        $arr["escajero"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["escajero"]);

        if (!isset($_SESSION["generales"]["sedeusuario"])) {
            $_SESSION["generales"]["sedeusuario"] = '';
        }
        $arr["sedeusuario"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["sedeusuario"]);

        if (!isset($_SESSION["generales"]["nombreusuario"])) {
            $_SESSION["generales"]["nombreusuario"] = '';
        }
        $arr["nombreusuario"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["nombreusuario"]);

        if (!isset($_SESSION["generales"]["tipousuariodesarrollo"])) {
            $_SESSION["generales"]["tipousuariodesarrollo"] = '';
        }
        $arr["tipousuariodesarrollo"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["tipousuariodesarrollo"]);

        if (!isset($_SESSION["generales"]["gastoadministrativo"])) {
            $_SESSION["generales"]["gastoadministrativo"] = '';
        }
        $arr["gastoadministrativo"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["gastoadministrativo"]);

        if (!isset($_SESSION["generales"]["escensador"])) {
            $_SESSION["generales"]["escensador"] = '';
        }
        $arr["escensador"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["escensador"]);

        if (!isset($_SESSION["generales"]["esbrigadista"])) {
            $_SESSION["generales"]["esbrigadista"] = '';
        }
        $arr["esbrigadista"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["esbrigadista"]);

        if (!isset($_SESSION["generales"]["idtipoidentificacionusuario"])) {
            $_SESSION["generales"]["idtipoidentificacionusuario"] = '';
        }
        $arr["idtipoidentificacionusuario"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["idtipoidentificacionusuario"]);

        if (!isset($_SESSION["generales"]["identificacionusuario"])) {
            $_SESSION["generales"]["identificacionusuario"] = '';
        }
        $arr["identificacionusuario"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["identificacionusuario"]);

        if (!isset($_SESSION["generales"]["nitempresausuario"])) {
            $_SESSION["generales"]["nitempresausuario"] = '';
        }
        $arr["nitempresausuario"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["nitempresausuario"]);

        if (!isset($_SESSION["generales"]["nombreempresausuario"])) {
            $_SESSION["generales"]["nombreempresausuario"] = '';
        }
        $arr["nombreempresausuario"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["nombreempresausuario"]);

        if (!isset($_SESSION["generales"]["direccionusuario"])) {
            $_SESSION["generales"]["direccionusuario"] = '';
        }
        $arr["direccionusuario"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["direccionusuario"]);

        if (!isset($_SESSION["generales"]["idmuniciopiousuario"])) {
            $_SESSION["generales"]["idmuniciopiousuario"] = '';
        }
        $arr["idmuniciopiousuario"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["idmuniciopiousuario"]);

        if (!isset($_SESSION["generales"]["telefonousuario"])) {
            $_SESSION["generales"]["telefonousuario"] = '';
        }
        $arr["telefonousuario"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["telefonousuario"]);

        if (!isset($_SESSION["generales"]["movilusuario"])) {
            $_SESSION["generales"]["movilusuario"] = '';
        }
        $arr["movilusuario"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["movilusuario"]);

        if (!isset($_SESSION["generales"]["ccosusuario"])) {
            $_SESSION["generales"]["ccosusuario"] = '';
        }
        $arr["ccosusuario"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["ccosusuario"]);

        if (!isset($_SESSION["generales"]["cargousuario"])) {
            $_SESSION["generales"]["cargousuario"] = '';
        }
        $arr["cargousuario"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["cargousuario"]);

        if (!isset($_SESSION["generales"]["nombreempresa"])) {
            $_SESSION["generales"]["nombreempresa"] = '';
        }
        $arr["nombreempresa"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["nombreempresa"]);

        if (!isset($_SESSION["generales"]["idcodigosirepcaja"])) {
            $_SESSION["generales"]["idcodigosirepcaja"] = '';
        }
        $arr["idcodigosirepcaja"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["idcodigosirepcaja"]);

        if (!isset($_SESSION["generales"]["idcodigosirepdigitacion"])) {
            $_SESSION["generales"]["idcodigosirepdigitacion"] = '';
        }
        $arr["idcodigosirepdigitacion"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["idcodigosirepdigitacion"]);

        if (!isset($_SESSION["generales"]["idcodigosirepregistro"])) {
            $_SESSION["generales"]["idcodigosirepregistro"] = '';
        }
        $arr["idcodigosirepregistro"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["idcodigosirepregistro"]);

        if (!isset($_SESSION["generales"]["controlapresupuesto"])) {
            $_SESSION["generales"]["controlapresupuesto"] = '';
        }
        $arr["controlapresupuesto"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["controlapresupuesto"]);

        if (!isset($_SESSION["generales"]["controlverificacion"])) {
            $_SESSION["generales"]["controlverificacion"] = '';
        }
        $arr["controlverificacion"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["controlverificacion"]);

        if (!isset($_SESSION["generales"]["tipousuariocontrol"])) {
            $_SESSION["generales"]["tipousuariocontrol"] = '';
        }
        $arr["tipousuariocontrol"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["tipousuariocontrol"]);

        if (!isset($_SESSION["generales"]["identificacionusuariocontrol"])) {
            $_SESSION["generales"]["identificacionusuariocontrol"] = '';
        }
        $arr["identificacionusuariocontrol"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["identificacionusuariocontrol"]);

        if (!isset($_SESSION["generales"]["emailusuariocontrol"])) {
            $_SESSION["generales"]["emailusuariocontrol"] = '';
        }
        $arr["emailusuariocontrol"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["emailusuariocontrol"]);

        if (!isset($_SESSION["generales"]["celularusuariocontrol"])) {
            $_SESSION["generales"]["celularusuariocontrol"] = '';
        }
        $arr["celularusuariocontrol"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["celularusuariocontrol"]);

        if (!isset($_SESSION["generales"]["nombreusuariocontrol"])) {
            $_SESSION["generales"]["nombreusuariocontrol"] = '';
        }
        $arr["nombreusuariocontrol"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["nombreusuariocontrol"]);

        if (!isset($_SESSION["generales"]["nombre1usuariocontrol"])) {
            $_SESSION["generales"]["nombre1usuariocontrol"] = '';
        }
        $arr["nombre1usuariocontrol"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["nombre1usuariocontrol"]);

        if (!isset($_SESSION["generales"]["nombre2usuariocontrol"])) {
            $_SESSION["generales"]["nombre2usuariocontrol"] = '';
        }
        $arr["nombre2usuariocontrol"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["nombre2usuariocontrol"]);

        if (!isset($_SESSION["generales"]["apellido1usuariocontrol"])) {
            $_SESSION["generales"]["apellido1usuariocontrol"] = '';
        }
        $arr["apellido1usuariocontrol"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["apellido1usuariocontrol"]);

        if (!isset($_SESSION["generales"]["apellido2usuariocontrol"])) {
            $_SESSION["generales"]["apellido2usuariocontrol"] = '';
        }
        $arr["apellido2usuariocontrol"] = \funcionesGenerales::inicializarUnset($_SESSION["generales"]["apellido2usuariocontrol"]);
        return $arr;
    }

    /**
     * 
     * @param type $mensaje
     * @return bool
     */
    public static function enviarCorreoError($mensaje)
    {
        if (!defined('EMAIL_NOTIFICACION_ERRORES') || EMAIL_NOTIFICACION_ERRORES == '') {
            if (!defined('EMAIL_NOTIFICACION_BATCH') || EMAIL_NOTIFICACION_BATCH == '') {
                $emx = 'jnieto@confecamaras.org.co';
            } else {
                $emx = EMAIL_NOTIFICACION_BATCH;
            }
        } else {
            $emx = EMAIL_NOTIFICACION_ERRORES;
        }
        if (TIPO_AMBIENTE == 'PRUEBAS') {
            if (!defined('EMAIL_NOTIFICACION_PRUEBAS') || EMAIL_NOTIFICACION_PRUEBAS == '') {
                $emx = 'jnieto@confecamaras.org.co';
            } else {
                $emx = EMAIL_NOTIFICACION_PRUEBAS;
            }
        }
        \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx, 'Alerta de error en informacion', $mensaje);
        return true;
    }

    /**
     * 
     * @param type $destino
     * @param type $asunto
     * @param type $mensaje
     * @param type $attach
     * @param type $ctrmasivo
     * @param type $ctralterno
     * @param type $confirmacionlectura
     * @param type $cc
     * @return bool
     */
    public static function enviarEmailDefault($destino = '', $asunto = '', $mensaje = '', $attach = array(), $ctrmasivo = 'no', $ctralterno = 'si', $confirmacionlectura = 'no', $cc = '')
    {
        return \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $destino, $asunto, $mensaje, $attach, $ctrmasivo, $ctralterno, $confirmacionlectura, $cc);
    }

    /**
     * 
     * @param type $servmail
     * @param type $portsmtp
     * @param type $requiautenticacion
     * @param type $tipoencripcion
     * @param type $ctaremi
     * @param type $passremi
     * @param type $remiemail
     * @param type $nombreremi
     * @param type $destino
     * @param type $asunto
     * @param type $mensaje
     * @param type $attach
     * @param type $ctrmasivo
     * @param type $ctralterno
     * @param type $confirmacionlectura
     * @param type $cc = con copia
     * @return boolean
     */
    public static function enviarEmail($servmail, $portsmtp, $requiautenticacion, $tipoencripcion, $ctaremi, $passremi, $remiemail, $nombreremi, $destino, $asunto, $mensaje, $attach = array(), $ctrmasivo = 'no', $ctralterno = 'si', $confirmacionlectura = 'no', $cc = '')
    {
        if (defined('ENVIAR_EMAILS') && ENVIAR_EMAILS === 'N') {
            require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
            \logApi::general2('email', __FUNCTION__, 'No se están enviando emails pues el parámetro ENVIAR_EMAILS está en N');
            return true;
        }
        $iEmail = 1;
        $okEmail = false;
        while ($iEmail <= 5 && $okEmail === false) {
            if (substr(PHP_VERSION, 0, 1) == '7' || substr(PHP_VERSION, 0, 1) == '8') {
                $eEmail = \funcionesgenerales::enviarEmailPhp7($servmail, $portsmtp, $requiautenticacion, $tipoencripcion, $ctaremi, $passremi, $remiemail, $nombreremi, $destino, $asunto, $mensaje, $attach, $ctrmasivo, $ctralterno, $confirmacionlectura, $cc);
            } else {
                $eEmail = \funcionesgenerales::enviarEmailPhp5($servmail, $portsmtp, $requiautenticacion, $tipoencripcion, $ctaremi, $passremi, $remiemail, $nombreremi, $destino, $asunto, $mensaje, $attach, $ctrmasivo, $ctralterno, $confirmacionlectura, $cc);
            }
            if ($eEmail === false) {
                $iEmail++;
            } else {
                $okEmail = true;
            }
        }
        if ($okEmail === true) {
            return true;
        } else {
            return false;
        }
    }

    public static function enviarEmailPhp5($servmail, $portsmtp, $requiautenticacion, $tipoencripcion, $ctaremi, $passremi, $remiemail, $nombreremi, $destino, $asunto, $mensaje, $attach = array(), $ctrmasivo = 'no', $ctralterno = 'si', $confirmacionlectura = 'no', $cc = '')
    {
        if (!class_exists('\\PHPMailer', false)) {
            require_once($_SESSION["generales"]["pathabsoluto"] . '/components/phpmailer/class.phpmailer.php');
        }
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        set_error_handler('myErrorHandler');

        //
        if (isset($_SESSION["generales"]["footeremails"]) && $_SESSION["generales"]["footeremails"] != '') {
            $mensaje .= '<br><hr><br>';
            $mensaje .= $_SESSION["generales"]["footeremails"];
            $mensaje .= '<br><hr><br>';
        }

        // Envía correo a través de servicios SMTP

        $mail = new PHPMailer();
        $mail->PluginDir = $_SESSION["generales"]["pathabsoluto"] . '/components/phpmailer/';
        $mail->SetLanguage("es", $_SESSION["generales"]["pathabsoluto"] . '/components/phpmailer/language/');

        //
        $mail->IsSMTP();

        //
        try {
            $mail->Host = $servmail;
            $mail->Port = $portsmtp;
            // $mail->SMTPAuth = $requiautenticacion;
            $mail->SMTPAuth = true;
            if ($tipoencripcion != '') {
                $mail->SMTPSecure = $tipoencripcion;
            }
            $mail->Username = $ctaremi;
            $mail->Password = $passremi;
            $mail->From = $remiemail;
            $mail->FromName = $nombreremi;
            if ($confirmacionlectura == 'si') {
                $mail->ConfirmReadingTo = $remiemail;
            }

            //
            $cantidadRemitentes = 0;
            $cantidadRemitentesAlterno = 0;

            //
            $txt = '';
            if (is_array($destino)) {
                foreach ($destino as $dest) {
                    $dest = str_replace(".@", "@", $dest);
                    $txt .= $dest . ' - ';
                    $mail->AddAddress($dest);
                    $cantidadRemitentes++;
                }

                $cantidadRemitentes = $cantidadRemitentes + $cantidadRemitentesAlterno;
                if ($cantidadRemitentes <= 0) {
                    unset($mail);
                    return false;
                }
            } else {
                $txt .= $destino . ' - ';
                $mail->AddAddress($destino);
                $cantidadRemitentes++;
            }

            //
            if ($cc != '') {
                if (is_array($cc)) {
                    foreach ($cc as $dest1) {
                        $mail->AddCC($dest1);
                    }
                } else {
                    $mail->AddCC($cc);
                }
            }

            //
            if ($cantidadRemitentes <= 0) {
                unset($mail);
                return false;
            }

            //
            $mail->WordWrap = 50;

            //
            if (!empty($attach)) {
                foreach ($attach as $at) {
                    $mail->AddAttachment($at);
                }
            }

            //
            $mail->IsHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $asunto;
            $posamazon = strpos($servmail, 'amazon');
            $possaasonline = strpos($remiemail, 'saas-online.info');
            if ($posamazon !== false && $possaasonline === false) {
                $mail->addCustomHeader('X-SES-CONFIGURATION-SET:Config-set-kinesis');
            }
            $mail->Body = $mensaje;

            //
            if (!$mail->Send()) {
                $_SESSION["generales"]["mensajeerror"] = $mail->ErrorInfo;
                \logApi::general2('email', __FUNCTION__, $txt . ' = ' . $_SESSION["generales"]["mensajeerror"]);
                unset($mail);
                return false;
            } else {
                $_SESSION["generales"]["mensajeerror"] = '';
                \logApi::general2('email', __FUNCTION__, $txt . ' = ok');
                unset($mail);
                return true;
            }
        } catch (phpmailerException $e) {
            $_SESSION["generales"]["mensajeerror"] = $e->errorMessage(); //Pretty error messages from PHPMailer;
            \logApi::general2('email', __FUNCTION__, $txt . ' = ' . $_SESSION["generales"]["mensajeerror"]);
            unset($mail);
            return false;
        } catch (Exception $e) {
            $_SESSION["generales"]["mensajeerror"] = $e->getMessage(); //Boring error messages from anything else!
            \logApi::general2('email', __FUNCTION__, $txt . ' = ' . $_SESSION["generales"]["mensajeerror"]);
            unset($mail);
            return false;
        }
    }

    public static function enviarEmailPhp7($servmail, $portsmtp, $requiautenticacion, $tipoencripcion, $ctaremi, $passremi, $remiemail, $nombreremi, $destino, $asunto, $mensaje, $attach = array(), $ctrmasivo = 'no', $ctralterno = 'si', $confirmacionlectura = 'no', $cc = '')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        set_error_handler('myErrorHandler');

        //
        if (isset($_SESSION["generales"]["footeremails"]) && $_SESSION["generales"]["footeremails"] != '') {
            $mensaje .= '<br><hr><br>';
            $mensaje .= $_SESSION["generales"]["footeremails"];
            $mensaje .= '<br><hr><br>';
        }

        // Envía correo a través de servicios SMTP

        $mail = new PHPMailer();
        // $mail->PluginDir = $_SESSION["generales"]["pathabsoluto"] . '/components/phpmailer/';
        $mail->SetLanguage("es", $_SESSION["generales"]["pathabsoluto"] . '/components/phpmailer/language/');

        //
        $mail->IsSMTP();

        //
        try {
            $mail->Host = $servmail;
            $mail->Port = $portsmtp;
            // $mail->SMTPAuth = $requiautenticacion;
            $mail->SMTPAuth = true;
            if ($tipoencripcion != '') {
                $mail->SMTPSecure = $tipoencripcion;
            }
            $mail->Username = $ctaremi;
            $mail->Password = $passremi;
            $mail->From = $remiemail;
            $mail->FromName = $nombreremi;
            if ($confirmacionlectura == 'si') {
                $mail->ConfirmReadingTo = $remiemail;
            }


            //
            $cantidadRemitentes = 0;
            $cantidadRemitentesAlterno = 0;

            //
            $txt = '';
            if (is_array($destino)) {
                foreach ($destino as $dest) {
                    $dest = str_replace(".@", "@", $dest);
                    $txt .= $dest . ' - ';
                    $mail->AddAddress($dest);
                    $cantidadRemitentes++;
                }

                $cantidadRemitentes = $cantidadRemitentes + $cantidadRemitentesAlterno;
                if ($cantidadRemitentes <= 0) {
                    unset($mail);
                    return false;
                }
            } else {
                $txt .= $destino . ' - ';
                $mail->AddAddress($destino);
                $cantidadRemitentes++;
            }

            //
            if ($cc != '') {
                if (is_array($cc)) {
                    foreach ($cc as $dest1) {
                        $mail->AddCC($dest1);
                    }
                } else {
                    $mail->AddCC($cc);
                }
            }

            //
            if ($cantidadRemitentes <= 0) {
                unset($mail);
                return false;
            }

            //
            $mail->WordWrap = 50;
            if (!empty($attach)) {
                foreach ($attach as $at) {
                    $mail->AddAttachment($at);
                }
            }
            $mail->IsHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $asunto;
            $posamazon = strpos($servmail, 'amazon');
            $possaasonline = strpos($remiemail, 'saas-online.info');
            if ($posamazon !== false && $possaasonline === false) {
                $mail->addCustomHeader('X-SES-CONFIGURATION-SET:Config-set-kinesis');
            }
            $mail->Body = $mensaje;

            //
            if (!$mail->Send()) {
                $_SESSION["generales"]["mensajeerror"] = $mail->ErrorInfo;
                \logApi::general2('email', __FUNCTION__, $txt . ' = ' . $_SESSION["generales"]["mensajeerror"]);
                unset($mail);
                return false;
            } else {
                $_SESSION["generales"]["mensajeerror"] = '';
                \logApi::general2('email', __FUNCTION__, $txt . ' = ok');
                unset($mail);
                return true;
            }
        } catch (phpmailerException $e) {
            $_SESSION["generales"]["mensajeerror"] = $e->errorMessage(); //Pretty error messages from PHPMailer;
            \logApi::general2('email', __FUNCTION__, $txt . ' = ' . $_SESSION["generales"]["mensajeerror"]);
            unset($mail);
            return false;
        } catch (Exception $e) {
            $_SESSION["generales"]["mensajeerror"] = $e->getMessage(); //Boring error messages from anything else!
            \logApi::general2('email', __FUNCTION__, $txt . ' = ' . $_SESSION["generales"]["mensajeerror"]);
            unset($mail);
            return false;
        }
    }

    /**
     * 
     * @param type $dbx
     * @param type $celular
     * @param type $mensaje
     * @return string
     */
    public static function enviarSms($dbx = null, $prefijo = '57', $celular = '', $mensaje = '', $tarea = '', $usarContingencia = 'no')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/components/elibom/elibom.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        $cerrarDbx = 'no';
        if ($dbx === null) {
            $cerrarDbx = 'si';
            $dbx = conexionMysqliApi();
        }

        $celx = str_replace(array(" ", "-", "."), "", trim($celular));
        $celular = $celx;

        //
        $okcel = 'si';

        //
        if (trim($prefijo) == '') {
            $prefijo = '57';
        }

        //
        if ($prefijo == '57') {
            if (strlen($celular) == 10 && substr($celular, 0, 1) != '3') {
                $okcel = 'no';
            }
        }

        //
        if ($okcel == 'no') {
            actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - No es un celular valido (' . $celular . ')');
            $respuesta = array(
                'codigoError' => '9999',
                'msgError' => 'No es un celular valido (' . $celular . ')',
                'deliveryCod' => ''
            );
            if ($cerrarDbx == 'si') {
                $dbx->close();
            }
            return $respuesta;
        }

        //
        if ($usarContingencia == 'si') {
            $sms_proveedor = retornarClaveValorMysqliApi($dbx, '90.32.80');
            $sms_profile = retornarClaveValorMysqliApi($dbx, '90.32.81');
            $sms_usuario = retornarClaveValorMysqliApi($dbx, '90.32.82');
            $sms_clave = retornarClaveValorMysqliApi($dbx, '90.32.83');
            if ($sms_proveedor == '') {
                $sms_proveedor = retornarClaveValorMysqliApi($dbx, '90.32.94');
                $sms_profile = retornarClaveValorMysqliApi($dbx, '90.32.95');
                $sms_usuario = retornarClaveValorMysqliApi($dbx, '90.32.97');
                $sms_clave = retornarClaveValorMysqliApi($dbx, '90.32.98');
            }
        } else {
            $sms_proveedor = retornarClaveValorMysqliApi($dbx, '90.32.94');
            $sms_profile = retornarClaveValorMysqliApi($dbx, '90.32.95');
            $sms_usuario = retornarClaveValorMysqliApi($dbx, '90.32.97');
            $sms_clave = retornarClaveValorMysqliApi($dbx, '90.32.98');
        }

        //
        $respuesta = array(
            'codigoError' => '9999',
            'msgError' => 'Error',
            'deliveryCod' => ''
        );

        //
        if (trim($sms_proveedor) == '' || $sms_usuario == '' || $sms_clave == '') {
            actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - No esta parametrizado correctamente el proveedor de mensajes SMS');
            $respuesta = array(
                'codigoError' => '9998',
                'msgError' => 'No esta parametrizado correctamente el proveedor de mensajes SMS',
                'deliveryCod' => ''
            );
            if ($cerrarDbx == 'si') {
                $dbx->close();
            }
            return $respuesta;
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma ONE MALL
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'onemall') {
            $url = 'http://107.20.199.106/sms/1/text/single';
            $headers = array(
                'Content-Type:application/json',
                'Authorization: Basic ' . base64_encode("$sms_usuario:$sms_clave")
            );
            $payloadName = array();
            $payloadName["from"] = "Camara de comercio";
            $payloadName["to"] = $prefijo . $celular;
            $payloadName["text"] = $mensaje;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payloadName));
            $result = curl_exec($ch);
            curl_close($ch);
            $respuestaonemall = json_decode($result, true);
            if ($respuestaonemall["messages"][0]["status"]["groupName"] == 'PENDING') {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - OneMall OK: ' . $respuestaonemall["messages"][0]["status"]["messageId"]);
                $respuesta = array(
                    'codigoError' => '0000',
                    'msgError' => '',
                    'deliveryCod' => $respuestaonemall["messages"][0]["status"]["messageId"]
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            } else {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - OneMall ERROR: ' . $respuestaonemall["messages"][0]["status"]["name"] . ' ' . $respuestaonemall["messages"][0]["status"]["description"]);
                $respuesta = array(
                    'codigoError' => '9999',
                    'msgError' => $respuestaonemall["messages"][0]["status"]["name"] . ' ' . $respuestaonemall["messages"][0]["status"]["description"],
                    'deliveryCod' => $respuestaonemall["messages"][0]["status"]["messageId"]
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            }
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma DIGITALSEND
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'digitalsend') {
            $payloadName = array(
                'idCliente' => $sms_usuario,
                'apiKey' => $sms_clave,
                'campania' => 'Notificaiones camara de comercio',
                'mensaje' => $mensaje,
                'celulares' => array(
                    $celular
                )
            );
            $handler = 'https://relaxplatform.com/api/sms/sendsms.php';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $handler);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payloadName));
            $resultado = curl_exec($ch);
            curl_close($ch);
            $resultado = str_replace("\u00f1", "n", $resultado);
            if (!\funcionesGenerales::isJson($resultado)) {
                $codigoError = '9999';
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - DigitalSend ERROR: ' . $resultado);
                $respuesta = array(
                    'codigoError' => '9999',
                    'msgError' => 'No fue posible enviar el mensaje a traves de la plataforma DigitalSend : ' . $resultado,
                    'deliveryCod' => ''
                );
            } else {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - DigitalSend respuesta del envio: ' . $resultado);
                $resultado1 = json_decode($resultado, true);
                if (isset($resultado1["success"])) {
                    $respuesta = array(
                        'codigoError' => '0000',
                        'msgError' => '',
                        'deliveryCod' => $resultado1["success"]
                    );
                    actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - DigitalSend OK: ' . $resultado);
                } else {
                    $respuesta = array(
                        'codigoError' => '9999',
                        'msgError' => 'No fue posible enviar el mensaje a traves de la plataforma DigitalSend : ' . $resultado,
                        'deliveryCod' => ''
                    );
                    actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - DigitalSend ERROR: ' . $resultado);
                }
            }
            if ($cerrarDbx == 'si') {
                $dbx->close();
            }
            return $respuesta;
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma SMS LABSMOBILE
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'labsmobile') {
            $handler = 'https://api.labsmobile.com/get/send.php?username=' . $sms_usuario . '&password=' . $sms_clave . '&message=' . urlencode($mensaje) . '&msisdn=' . $prefijo . $celular;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $handler);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $resultado = curl_exec($ch);
            curl_close($ch);
            $respuesta1 = new SimpleXMLElement($resultado);

            //
            if ($respuesta1->response[0]->code != '0') {
                $codigoError = '9999';
                $txtError = $respuesta1->response[0]->code . ' - Error enviando - ' . $respuesta1->response[0]->message;
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - LabsMobile ERROR: ' . 'No fue posible enviar el mensaje a traves de la plataforma labsmobile : ' . $txtError);
                $respuesta = array(
                    'codigoError' => $codigoError,
                    'msgError' => 'No fue posible enviar el mensaje a traves de la plataforma labsmobile : ' . $txtError,
                    'deliveryCod' => ''
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            } else {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - LabsMobile OK: ' . $respuesta1->response[0]->subid);
                $respuesta = array(
                    'codigoError' => '0000',
                    'msgError' => '',
                    'deliveryCod' => $respuesta1->response[0]->subid
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            }
        }

        // ********************************************************************************** //
        // SI el proveedor es Elibom
        // ********************************************************************************** //
        if ($sms_proveedor == 'elibom') {

            //Filtro textos en SMS para evitar incidentes en el envio al usuario.
            $mensaje = str_replace("CAMARA DE COMERCIO DE", "C.C.", $mensaje);
            $mensaje = str_replace("-", "", $mensaje);
            $mensaje = str_replace("  ", " ", $mensaje);
            $mensaje = str_replace("MATRICULA", "MATRIC.", $mensaje);
            $mensaje = str_replace("CONSTITUCION", "CONST.", $mensaje);
            $mensaje = str_replace("MODIFICACION", "MODIF.", $mensaje);
            $mensaje = str_replace("CANCELACION", "CANCEL.", $mensaje);
            $mensaje = str_replace("ESCRITURA", "ESCRIT.", $mensaje);
            $mensaje = str_replace("REFORMAS", "REFOR.", $mensaje);
            $mensaje = str_replace("PERSONA", "PERS.", $mensaje);
            $mensaje = str_replace("JURIDICA", "JURID.", $mensaje);

            //Control de longitud.
            if (strlen($mensaje) > 160) {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - Elibom ERROR: ' . 'el texto del SMS sobrepasa la longitud permitida (160 caracteres)');
                $respuesta = array(
                    'codigoError' => '9997',
                    'msgError' => 'el texto del SMS sobrepasa la longitud permitida (160 caracteres)',
                    'deliveryCod' => ''
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            }
        }


        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma SMS ELIBOM
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'elibom') {
            $elibom = new ElibomClient($sms_usuario, $sms_clave);
            if ($elibom === false) {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - Elibom ERROR: ' . 'Error conectando con la plataforma ELIBOM');
                $respuesta = array(
                    'codigoError' => '9997',
                    'msgError' => 'Error conectando con la plataforma ELIBOM',
                    'deliveryCod' => ''
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            }
            try {
                $deliveryId = $elibom->sendMessage($prefijo . $celular, $mensaje);
            } catch (Exception $e) {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - Elibom ERROR: ' . 'Error de excepcion al conectar con ELIBOM : ' . $e->getMessage());
                $respuesta = array(
                    'codigoError' => '9997',
                    'msgError' => 'Error de excepcion al conectar con ELIBOM : ' . $e->getMessage(),
                    'deliveryCod' => ''
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            }

            // unset ($elibom);
            if ($deliveryId === false) {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - Elibom ERROR: ' . 'No fue posible enviar el mensaje a través de la plataforma ELIBOM');
                $respuesta = array(
                    'codigoError' => '9999',
                    'msgError' => 'No fue posible enviar el mensaje a través de la plataforma ELIBOM',
                    'deliveryCod' => ''
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            } else {
                $token = (array) $deliveryId;
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - Elibom OK: ' . $token[0]);
                $respuesta = array(
                    'codigoError' => '0000',
                    'msgError' => '',
                    'deliveryCod' => $token[0]
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            }
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma Alo
        // EndPoint: http://portal.colombitrade.com:8080/SendSimpleMessageService/SendSimpleMessage?wsdl
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'alo') {

            // $client = new SoapClient("http://portal.colombitrade.com:8080/SendSimpleMessageService/SendSimpleMessage?wsdl");
            $parameters = array(
                "message" => array(
                    "user" => $sms_usuario,
                    "password" => $sms_clave,
                    "profile" => $sms_profile,
                    "address" => $prefijo . $celular,
                    "channel" => 'SMS',
                    "content" => $mensaje
                )
            );

            $message = '<message>';
            $message .= '<user>' . $sms_usuario . '</user>';
            $message .= '<password>' . $sms_clave . '</password>';
            $message .= '<profile>' . $sms_profile . '</profile>';
            $message .= '<address>' . $prefijo . $celular . '</address>';
            $message .= '<channel>SMS</channel>';
            $message .= '<content>' . $mensaje . '</content>';
            $message .= '</message>';

            //
            $client = new SoapClient("http://portal.colombitrade.com:8080/SendSimpleMessageService/SendSimpleMessage?wsdl");
            try {
                $result = $client->__soapCall('send-message', array('parameters' => $parameters));
                if (is_soap_fault($result)) {
                    actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - Alo ERROR: ' . \funcionesGenerales::utf8_decode("SOAP Fault: faultcode: " . $result->faultcode . ", faultstring: " . $result->faultstring));
                    $respuesta["codigoError"] = '9999';
                    $respuesta["msgError"] = \funcionesGenerales::utf8_decode("SOAP Fault: faultcode: " . $result->faultcode . ", faultstring: " . $result->faultstring);
                    if ($cerrarDbx == 'si') {
                        $dbx->close();
                    }
                    return $respuesta;
                }
            } catch (Exception $e) {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - Alo ERROR: ' . \funcionesGenerales::utf8_decode("Excepción : " . $e->getMessage()));
                $respuesta["codigoError"] = '9999';
                $respuesta["msgError"] = \funcionesGenerales::utf8_decode("Excepción : " . $e->getMessage());
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            }

            if ($result === false) {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - Alo ERROR: ' . 'No fue posible enviar el mensaje a través de la plataforma ALO Global Comunicaciones');
                $respuesta = array(
                    'codigoError' => '9999',
                    'msgError' => 'No fue posible enviar el mensaje a través de la plataforma ALO Global Comunicaciones',
                    'deliveryCod' => ''
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            } else {
                $t = (array) $result;
                if ($t["return"]->statusCode == '0') {
                    actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - Alo OK: ' . $t["return"]->messageId);
                    $respuesta = array(
                        'codigoError' => '0000',
                        'msgError' => '',
                        'deliveryCod' => $t["return"]->messageId
                    );
                    if ($cerrarDbx == 'si') {
                        $dbx->close();
                    }
                    return $respuesta;
                }
                if ($t["return"]->statusCode != '0') {
                    actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - Alo ERROR: ' . 'Error enviando SMS : StatusCode : ' . $t["return"]->statusCode . ' - StatusText : ' . $t["return"]->statusText . ' - MessageId : ' . $t["return"]->messageId);
                    $respuesta = array(
                        'codigoError' => '9999',
                        'msgError' => 'Error enviando SMS : StatusCode : ' . $t["return"]->statusCode . ' - StatusText : ' . $t["return"]->statusText . ' - MessageId : ' . $t["return"]->messageId,
                        'deliveryCod' => ''
                    );
                    if ($cerrarDbx == 'si') {
                        $dbx->close();
                    }
                    return $respuesta;
                }
            }
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma Alo_Global_Ws
        // EndPoint: http://www.portalsms.co/wsSMS/wsEnviosSMS.php?wsdl
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'global_alo_ws') {

            // $client = new SoapClient("http://portal.colombitrade.com:8080/SendSimpleMessageService/SendSimpleMessage?wsdl");
            $parameters = array(
                "celular" => $prefijo . $celular,
                "mensaje" => $mensaje,
                "login" => $sms_usuario,
                "clave" => $sms_clave
            );

            $message = '<message>';
            $message .= '<user>' . $sms_usuario . '</user>';
            $message .= '<password>' . $sms_clave . '</password>';
            $message .= '<profile>' . $sms_profile . '</profile>';
            $message .= '<address>' . $prefijo . $celular . '</address>';
            $message .= '<channel>SMS</channel>';
            $message .= '<content>' . $mensaje . '</content>';
            $message .= '</message>';

            //
            // $client = new SoapClient("http://www.portalsms.co/wsSMS/wsEnviosSMS.php?wsdl");
            $client = new SoapClient("https://www.portalsms.co/wsSMS/wsEnviosSMS.php?wsdl");
            try {
                $result = $client->__soapCall('getEnvioSMS', $parameters);
                if (is_soap_fault($result)) {
                    actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - GlobalAloWs ERROR: ' . \funcionesGenerales::utf8_decode("SOAP Fault: faultcode: " . $result->faultcode . ", faultstring: " . $result->faultstring));
                    $respuesta["codigoError"] = '9999';
                    $respuesta["msgError"] = \funcionesGenerales::utf8_decode("SOAP Fault: faultcode: " . $result->faultcode . ", faultstring: " . $result->faultstring);
                    if ($cerrarDbx == 'si') {
                        $dbx->close();
                    }
                    return $respuesta;
                }
            } catch (Exception $e) {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - GlobalAloWs ERROR: ' . \funcionesGenerales::utf8_decode("Excepcion : " . $e->getMessage()));
                $respuesta["codigoError"] = '9999';
                $respuesta["msgError"] = \funcionesGenerales::utf8_decode("Excepcion : " . $e->getMessage());
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            }

            if ($result === false) {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - GlobalAloWs ERROR: ' . 'No fue posible enviar el mensaje a través de la plataforma ALO Global Comunicaciones');
                $respuesta = array(
                    'codigoError' => '9999',
                    'msgError' => 'No fue posible enviar el mensaje a través de la plataforma ALO Global Comunicaciones',
                    'deliveryCod' => ''
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            } else {
                $codigoError = '0000';
                $msgError = 'Enviado satisfactoriamente';
                $deliveryCod = '';
                switch (trim($result)) {
                    case "400":
                        $codigoError = '9997';
                        $msgError = '400.- Usuario inactivo o datos de acceso invalidos';
                        break;
                    case "401":
                        $codigoError = '9999';
                        $msgError = '401.- Linea no autorizada por la plataforma.';
                        break;
                    case "402":
                        $codigoError = '9999';
                        $msgError = '402.- El contenido del mensaje es vacio.';
                        break;
                    case "404":
                        $codigoError = '9997';
                        $msgError = '404.- Cupo de mensajes insuficientes.';
                        break;
                    case "407":
                        $codigoError = '9997';
                        $msgError = '407.- No se realizo ninguna transaccion';
                        break;
                    case "408":
                        $codigoError = '9999';
                        $msgError = '408.- Numero de celular errado, no es un numero, no tiene el formato de celular';
                        break;
                    case "412":
                        $codigoError = '9997';
                        $msgError = '412.- Horario de envio no valido para la cuenta de usuario';
                        break;
                    default:
                        $deliveryCod = trim($result);
                        break;
                }
                if ($codigoError === '0000') {
                    actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - GlobalAloWs OK: ' . $deliveryCod);
                } else {
                    actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - GlobalAloWs ERROR: ' . $msgError);
                }
                $respuesta = array(
                    'codigoError' => $codigoError,
                    'msgError' => $msgError,
                    'deliveryCod' => $deliveryCod
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            }
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma Alo_Global_Ws
        // EndPoint: http://www.portalsms.co/wsSMS/wsEnviosSMS.php?wsdl
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'global_alo_ws_v2') {

            // $client = new SoapClient("http://portal.colombitrade.com:8080/SendSimpleMessageService/SendSimpleMessage?wsdl");
            $parameters = array(
                "celular" => $prefijo . $celular,
                "mensaje" => $mensaje,
                "login" => $sms_usuario,
                "clave" => $sms_clave
            );

            $message = '<message>';
            $message .= '<user>' . $sms_usuario . '</user>';
            $message .= '<password>' . $sms_clave . '</password>';
            $message .= '<profile>' . $sms_profile . '</profile>';
            $message .= '<address>' . $prefijo . $celular . '</address>';
            $message .= '<channel>SMS</channel>';
            $message .= '<content>' . $mensaje . '</content>';
            $message .= '</message>';

            //
            // $client = new SoapClient("http://www.portalsms.co/wsSMS/wsEnviosSMS.php?wsdl");
            $client = new SoapClient("https://www.portalsms.co/wsSMS/wsEnviosSMS.php?wsdl");
            try {
                $result = $client->__soapCall('getEnvioSMS', $parameters);
                if (is_soap_fault($result)) {
                    actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - GlobalAloWsV2 ERROR: ' . \funcionesGenerales::utf8_decode("SOAP Fault: faultcode: " . $result->faultcode . ", faultstring: " . $result->faultstring));
                    $respuesta["codigoError"] = '9999';
                    $respuesta["msgError"] = \funcionesGenerales::utf8_decode("SOAP Fault: faultcode: " . $result->faultcode . ", faultstring: " . $result->faultstring);
                    if ($cerrarDbx == 'si') {
                        $dbx->close();
                    }
                    return $respuesta;
                }
            } catch (Exception $e) {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - GlobalAloWsV2 ERROR: ' . \funcionesGenerales::utf8_decode("Excepcion : " . $e->getMessage()));
                $respuesta["codigoError"] = '9999';
                $respuesta["msgError"] = \funcionesGenerales::utf8_decode("Excepcion : " . $e->getMessage());
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            }

            if ($result === false) {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - GlobalAloWsV2 ERROR: ' . 'No fue posible enviar el mensaje a través de la plataforma ALO Global Comunicaciones');
                $respuesta = array(
                    'codigoError' => '9999',
                    'msgError' => 'No fue posible enviar el mensaje a través de la plataforma ALO Global Comunicaciones',
                    'deliveryCod' => ''
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            } else {
                $codigoError = '0000';
                $msgError = 'Enviado satisfactoriamente';
                $deliveryCod = '';
                switch (trim($result)) {
                    case "400":
                        $codigoError = '9997';
                        $msgError = '400.- Usuario inactivo o datos de acceso invalidos';
                        break;
                    case "401":
                        $codigoError = '9999';
                        $msgError = '401.- Linea no autorizada por la plataforma.';
                        break;
                    case "402":
                        $codigoError = '9999';
                        $msgError = '402.- El contenido del mensaje es vacio.';
                        break;
                    case "404":
                        $codigoError = '9997';
                        $msgError = '404.- Cupo de mensajes insuficientes.';
                        break;
                    case "407":
                        $codigoError = '9997';
                        $msgError = '407.- No se realizo ninguna transaccion';
                        break;
                    case "408":
                        $codigoError = '9999';
                        $msgError = '408.- Numero de celular errado, no es un numero, no tiene el formato de celular';
                        break;
                    case "412":
                        $codigoError = '9997';
                        $msgError = '412.- Horario de envio no valido para la cuenta de usuario';
                        break;
                    default:
                        $deliveryCod = trim($result);
                        break;
                }
                if ($codigoError === '0000') {
                    actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - GlobalAloWsV2 OK: ' . $deliveryCod);
                } else {
                    actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - GlobalAloWsV2 ERROR: ' . $msgError);
                }
                $respuesta = array(
                    'codigoError' => $codigoError,
                    'msgError' => $msgError,
                    'deliveryCod' => $deliveryCod
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            }
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma Alo_Global_Ws_V2
        // EndPoint: http://www.portalsms.co/wsSMS/wsEnviosSMS.php?wsdl
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'global_alo_api_v2') {

            // Solicita token
            $headers = array(
                'Content-Type:application/json',
                'Postman-Token:a9d11466-84f2-45b7-94a1-5735cc43e9d6',
                'cache-control:no-cache'
            );

            //
            $url1 = 'https://api.aloglobal.com/auth/1/session';
            $data = json_encode(array(
                "username" => $sms_usuario,
                "password" => $sms_clave
            ));

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_ENCODING, "");
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            $err = curl_error($ch);

            $token = json_decode($result);
            $token_id = '';
            if (isset($token->token)) {
                $token_id = $token->token;
            }
            curl_close($ch);

            //
            if ($token_id == '') {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - GlobalAloWsApiV2 ERROR: ' . 'No se recibio un token valido de la plataforma SMS (' . $sms_usuario . ') (' . $sms_clave . ')');
                $codigoError = '9999';
                $msgError = 'No se recibio un token valido de la plataforma SMS (' . $sms_usuario . ') (' . $sms_clave . ')';
                $deliveryCod = '';
            } else {

                // Enviar sms
                $headers = array(
                    'Authorization: IBSSO ' . $token_id,
                    'Content-Type: application/json',
                    'Postman-Token: 4b5ad3ca-61f0-4804-8496-003750f80e0a',
                    'cache-control:no-cache'
                );

                //
                $url2 = 'https://api.aloglobal.com/sms/2/text';
                $smsdata = json_encode(array(
                    "from" => 'SistRegistro',
                    "to" => $prefijo . $celular,
                    "text" => $mensaje
                ));

                //
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url2);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_ENCODING, "");
                curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $smsdata);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $result = curl_exec($ch);
                $err = curl_error($ch);
                $result1 = json_decode($result);
                curl_close($ch);

                //
                $messid = '';
                $status = '';
                $description = '';
                foreach ($result1->messages as $msg) {
                    if (isset($msg->messageId)) {
                        $messid = $msg->messageId;
                    }
                    if (isset($msg->status->groupName)) {
                        $status = $msg->status->groupName;
                    }
                    if (isset($msg->status->description)) {
                        $description = $msg->status->description;
                    }
                }

                //
                $codigoError = '0000';
                $msgError = 'Enviado satisfactoriamente';
                $deliveryCod = '';

                //
                if ($status == 'PENDING' || $status == 'ACCEPTED' || $status == 'DELIVERED' || $status == 'OK') {
                    $codigoError = '0000';
                    $msgError = 'Enviado satisfactoriamente';
                    $deliveryCod = $messid;
                } else {
                    $codigoError = '9999';
                    if ($description != '') {
                        $msgError = $description;
                    } else {
                        $msgError = 'Error enviando el SMS';
                    }
                    $deliveryCod = $messid;
                }
            }
            if ($codigoError === '0000') {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - GlobalAloWsApiV2 OK: ' . $deliveryCod);
            } else {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - GlobalAloWsApiV2 ERROR: ' . $msgError);
            }
            $respuesta = array(
                'codigoError' => $codigoError,
                'msgError' => $msgError,
                'deliveryCod' => $deliveryCod
            );
            if ($cerrarDbx == 'si') {
                $dbx->close();
            }
            return $respuesta;
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma CeoMarketing
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'ceomarketing') {

            $handler = 'http://api.ceomarketing.co/api/v3/sendsms/plain?user=' . $sms_usuario . '&password=' . $sms_clave . '&sender=CAMARACOMERCIO&SMSText=' . urlencode($mensaje) . '&GSM=' . $prefijo . $celular;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $handler);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            // curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $resultado = curl_exec($ch);
            curl_close($ch);

            $respuesta = new SimpleXMLElement($resultado);

            //
            if ($respuesta->result[0]->status != '0') {
                $codigoError = '9999';
                switch ($respuesta->result[0]->status) {
                    case "-1":
                        $txtError = $respuesta->result[0]->status . ' - Error enviando';
                        $codigoError = '9997';
                        break;
                    case "-2":
                        $txtError = $respuesta->result[0]->status . ' - No hay cr&eacute;ditos disponibles';
                        $codigoError = '9997';
                        break;
                    case "-3":
                        $txtError = $respuesta->result[0]->status . ' - Red no descubierta';
                        $codigoError = '9997';
                        break;
                    case "-5":
                        $txtError = $respuesta->result[0]->status . ' - Usuario o password inv&aacute;lido';
                        $codigoError = '9997';
                        break;
                    case "-6":
                        $txtError = $respuesta->result[0]->status . ' - Destinatario inv&aacute;lido';
                        $codigoError = '9999';
                        break;
                    case "-10":
                        $txtError = $respuesta->result[0]->status . ' - Usuario inv&aacute;lido';
                        $codigoError = '9997';
                        break;
                    case "-11":
                        $txtError = $respuesta->result[0]->status . ' - Password inv&aacute;lido';
                        $codigoError = '9997';
                        break;
                    case "-13":
                        $txtError = $respuesta->result[0]->status . ' - Destino inv&aacute;lido';
                        $codigoError = '9999';
                        break;
                    case "-22":
                        $txtError = $respuesta->result[0]->status . ' - Error de sintaxis';
                        $codigoError = '9997';
                        break;
                    case "-23":
                        $txtError = $respuesta->result[0]->status . ' - Error de proceso';
                        $codigoError = '9997';
                        break;
                    case "-26":
                        $txtError = $respuesta->result[0]->status . ' - Error de comunicaci&oacute;n';
                        $codigoError = '9997';
                        break;
                    case "-27":
                        $txtError = $respuesta->result[0]->status . ' - Send Date inv&aacute;lido';
                        $codigoError = '9997';
                        break;
                    case "-28":
                        $txtError = $respuesta->result[0]->status . ' - Incorrecto PushURL';
                        $codigoError = '9997';
                        break;
                    case "-30":
                        $txtError = $respuesta->result[0]->status . ' - Incorrecto APPID';
                        $codigoError = '9997';
                        break;
                    case "-33":
                        $txtError = $respuesta->result[0]->status . ' - Mensaje duplicado';
                        $codigoError = '9999';
                        break;
                    case "-34":
                        $txtError = $respuesta->result[0]->status . ' - Remitente no habilitado';
                        $codigoError = '9999';
                        break;
                    case "-99":
                        $txtError = $respuesta->result[0]->status . ' - Error general';
                        $codigoError = '9997';
                        break;
                    default:
                        $txtError = $respuesta->result[0]->status . ' - Error no controlado';
                        $codigoError = '9997';
                        break;
                }
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - CeoMarketing ERROR: ' . 'No fue posible enviar el mensaje a traves de la plataforma Ceomarketing : ' . $txtError);
                $respuesta = array(
                    'codigoError' => $codigoError,
                    'msgError' => 'No fue posible enviar el mensaje a traves de la plataforma Ceomarketing : ' . $txtError,
                    'deliveryCod' => ''
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            } else {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - CeoMarketing OK : ' . $respuesta->result[0]->messageid);
                $respuesta = array(
                    'codigoError' => '0000',
                    'msgError' => '',
                    'deliveryCod' => $respuesta->result[0]->messageid
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            }
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma Masiv
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'masiv') {

            $handler = 'http://api.ceomarketing.co/api/v3/sendsms/plain?user=' . $sms_usuario . '&password=' . $sms_clave . '&sender=CAMARACOMERCIO&SMSText=' . urlencode($mensaje) . '&GSM=' . $prefijo . $celular;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $handler);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            // curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $resultado = curl_exec($ch);
            curl_close($ch);

            $respuesta = new SimpleXMLElement($resultado);

            //
            if ($respuesta->result[0]->status != '0') {
                $codigoError = '9999';
                switch ($respuesta->result[0]->status) {
                    case "-1":
                        $txtError = $respuesta->result[0]->status . ' - Error enviando';
                        $codigoError = '9997';
                        break;
                    case "-2":
                        $txtError = $respuesta->result[0]->status . ' - No hay cr&eacute;ditos disponibles';
                        $codigoError = '9997';
                        break;
                    case "-3":
                        $txtError = $respuesta->result[0]->status . ' - Red no descubierta';
                        $codigoError = '9997';
                        break;
                    case "-5":
                        $txtError = $respuesta->result[0]->status . ' - Usuario o password inv&aacute;lido';
                        $codigoError = '9997';
                        break;
                    case "-6":
                        $txtError = $respuesta->result[0]->status . ' - Destinatario inv&aacute;lido';
                        $codigoError = '9999';
                        break;
                    case "-10":
                        $txtError = $respuesta->result[0]->status . ' - Usuario inv&aacute;lido';
                        $codigoError = '9997';
                        break;
                    case "-11":
                        $txtError = $respuesta->result[0]->status . ' - Password inv&aacute;lido';
                        $codigoError = '9997';
                        break;
                    case "-13":
                        $txtError = $respuesta->result[0]->status . ' - Destino inv&aacute;lido';
                        $codigoError = '9999';
                        break;
                    case "-22":
                        $txtError = $respuesta->result[0]->status . ' - Error de sintaxis';
                        $codigoError = '9997';
                        break;
                    case "-23":
                        $txtError = $respuesta->result[0]->status . ' - Error de proceso';
                        $codigoError = '9997';
                        break;
                    case "-26":
                        $txtError = $respuesta->result[0]->status . ' - Error de comunicaci&oacute;n';
                        $codigoError = '9997';
                        break;
                    case "-27":
                        $txtError = $respuesta->result[0]->status . ' - Send Date inv&aacute;lido';
                        $codigoError = '9997';
                        break;
                    case "-28":
                        $txtError = $respuesta->result[0]->status . ' - Incorrecto PushURL';
                        $codigoError = '9997';
                        break;
                    case "-30":
                        $txtError = $respuesta->result[0]->status . ' - Incorrecto APPID';
                        $codigoError = '9997';
                        break;
                    case "-33":
                        $txtError = $respuesta->result[0]->status . ' - Mensaje duplicado';
                        $codigoError = '9999';
                        break;
                    case "-34":
                        $txtError = $respuesta->result[0]->status . ' - Remitente no habilitado';
                        $codigoError = '9999';
                        break;
                    case "-99":
                        $txtError = $respuesta->result[0]->status . ' - Error general';
                        $codigoError = '9997';
                        break;
                    default:
                        $txtError = $respuesta->result[0]->status . ' - Error no controlado';
                        $codigoError = '9997';
                        break;
                }
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - CeoMarketingMasiv ERROR : ' . $txtError);
                $respuesta = array(
                    'codigoError' => $codigoError,
                    'msgError' => 'No fue posible enviar el mensaje a través de la plataforma Ceomarketing : ' . $txtError,
                    'deliveryCod' => ''
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            } else {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - CeoMarketingMasiv OK : ' . $respuesta->result[0]->messageid);
                $respuesta = array(
                    'codigoError' => '0000',
                    'msgError' => '',
                    'deliveryCod' => $respuesta->result[0]->messageid
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            }
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma Stratec
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'stratec') {
            $handler = 'http://api.masiv.co/SmsHandlers/sendhandler.ashx?action=sendmessage&username=' . $sms_usuario . '&password=' . $sms_clave . '&messagedata=' . urlencode($mensaje) . '&recipient=' . $prefijo . $celular;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $handler);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $resultado = curl_exec($ch);
            curl_close($ch);

            //
            $respuesta = new SimpleXMLElement($resultado);
            if ($respuesta->action == 'sendmessage') {
                $codigoError = '0000';
                $msgError = '';
                $messageId = $respuesta->data->acceptreport->messageid;
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - Stratec OK : ' . $respuesta->data->acceptreport->messageid);
                $respuesta = array(
                    'codigoError' => $codigoError,
                    'msgError' => $msgError,
                    'deliveryCod' => $messageId
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            }

            //
            if ($respuesta->action == 'error') {
                $codigoError = '9999';
                $msgError = $respuesta->data->errorcode;
                $messageId = '';
                switch ($respuesta->data->errorcode) {
                    case "1":
                        $msgError .= ' - Recipiente invalido';
                        $codigoError = '9999';
                        break;
                    case "1164":
                        $msgError .= ' - Faltan parametros en la solicitud';
                        $codigoError = '9999';
                        break;
                    case "1158":
                        $msgError .= ' - Parametro accion desconocido';
                        $codigoError = '9999';
                        break;
                    case "1157":
                        $msgError .= ' - Usuario o password invalido';
                        $codigoError = '9999';
                        break;
                    case "1156":
                        $msgError .= ' - Usuario no activado';
                        $codigoError = '9999';
                        break;
                    case "8734":
                        $msgError .= ' - Error en plataforma de SMS';
                        $codigoError = '9997';
                        break;
                    case "1159":
                        $msgError .= ' - Creditos insuficientes';
                        $codigoError = '9997';
                        break;
                    case "1160":
                        $msgError .= ' - Mensaje demasiado largo';
                        $codigoError = '9999';
                        break;
                }
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - Stratec ERROR : ' . $msgError);
                $respuesta = array(
                    'codigoError' => $codigoError,
                    'msgError' => $msgError,
                    'deliveryCod' => ''
                );
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            }
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma Masiv V2
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'stratecV2') {

            $handler = 'https://api-sms.masivapp.com/send-message';
            $headers = array(
                'Content-Type:application/json',
                'Authorization: Basic ' . base64_encode("$sms_usuario:$sms_clave")
            );
            $payloadName = array();
            $payloadName["to"] = $prefijo . $celular;
            $payloadName["text"] = $mensaje;
            $payloadName["customdata"] = "";
            $payloadName["isPremium"] = false;
            $payloadName["isFlash"] = true;
            if (strlen($mensaje) <= 160) {
                $payloadName["isLongmessage"] = false;
            } else {
                $payloadName["isLongmessage"] = true;
            }
            $payloadName["isRandomRoute"] = false;
            // $payloadName ["shortUrlConfig"] = array();

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $handler);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payloadName));
            $result = curl_exec($ch);
            curl_close($ch);
            actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - StratecV2 Respuesta: ' . $result);

            //
            if (!\funcionesGenerales::isJson($result)) {
                $respuesta = array(
                    'codigoError' => '9999',
                    'msgError' => 'Respuesta erronea de StratecV2 2.0',
                    'deliveryCod' => ''
                );
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - StratecV2 ERROR : ' . $result);
                return $respuesta;
            }

            //
            $respuestamasiv = json_decode($result, true);

            //
            if (isset($respuestamasiv["statusCode"]) && $respuestamasiv["statusCode"] == '200') {
                $respuesta = array(
                    'codigoError' => '0000',
                    'msgError' => $respuestamasiv["statusMessage"],
                    'deliveryCod' => $respuestamasiv["messageId"]
                );
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - StratecV2 OK : ' . $respuestamasiv["messageId"]);
                return $respuesta;
            }

            //
            if (isset($respuestamasiv["statusCode"]) && $respuestamasiv["statusCode"] != '200') {
                $respuesta = array(
                    'codigoError' => '9999',
                    'msgError' => $respuestamasiv["statusCode"] . ' ' . $respuestamasiv["statusMessage"],
                    'deliveryCod' => ''
                );
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - StratecV2 ERROR : ' . $respuestamasiv["statusCode"] . ' ' . $respuestamasiv["statusMessage"]);
                return $respuesta;
            }
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma Claro
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'claro') {

            $post = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tgg="http://ws.tiaxa.net/tggDataSoapService/">
            <soapenv:Header/>
            <soapenv:Body>
            <tgg:sendMessageRequest>
                <subscriber>' . $prefijo . $celular . '</subscriber>
                <sender>' . $sms_profile . '</sender>
                <requestId>01</requestId>
                <receiptRequest>0</receiptRequest>
                <dataCoding>0</dataCoding>
                <message>' . $mensaje . '</message>
            </tgg:sendMessageRequest>
            </soapenv:Body>
            </soapenv:Envelope>
            ';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.gestormensajeriaadmin.com/RA/tggDataSoap?wsdl');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_POST, 1);
            $headers = array();
            $headers[] = 'Content-Type: text/xml';
            $headers[] = 'Soapaction: "http://ws.tiaxa.net/tggDataSoapService/sendMessage"';
            $headers[] = 'Authorization: Basic ' . base64_encode($sms_usuario . ':' . $sms_clave);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - CLARO (Resultado del curl): ' . $result);
            if (curl_errno($ch)) {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - CLARO (Error en curl): ' . curl_errno($ch));
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);

            $resultCode = '';
            $operationId = '';
            $posR = '';
            $posO = '';
            $posR = strpos($result, '<resultCode>');
            if ($posR) {
                $resultCode = '9999';
                $message = 'Mensaje de error no codificado';
                $posR = $posR + 12;
                if (substr($result, $posR, 1) == '0') {
                    $resultCode = '0';
                    $message = 'OK';
                }
                if (substr($result, $posR, 3) == '100') {
                    $resultCode = '100';
                    $message = 'Parametros erroneos';
                }
                if (substr($result, $posR, 3) == '200') {
                    $resultCode = '200';
                    $message = 'Usuario deshabilitado';
                }
                if (substr($result, $posR, 3) == '201') {
                    $resultCode = '201';
                    $message = 'Numero de telefono invalido';
                }
                if (substr($result, $posR, 3) == '202') {
                    $resultCode = '202';
                    $message = 'Numero de telefono no aprovisionado';
                }
                if (substr($result, $posR, 3) == '203') {
                    $resultCode = '203';
                    $message = 'Texto nulo';
                }
                if (substr($result, $posR, 3) == '204') {
                    $resultCode = '204';
                    $message = 'Compania inactiva';
                }
                if (substr($result, $posR, 3) == '206') {
                    $resultCode = '206';
                    $message = 'Fecha de despacho expirada';
                }
                if (substr($result, $posR, 3) == '208') {
                    $resultCode = '208';
                    $message = 'Exedio la cuota de mensajes';
                }
                if (substr($result, $posR, 3) == '250') {
                    $resultCode = '250';
                    $message = 'Fecha de despacho tiene limite de 30 dias';
                }
                if ($message == 'Mensaje de error no codificado') {
                    $message .= ' (' . $result . ')';
                }
            }

            $posO = strpos($result, '<operationId>');
            if ($posO) {
                $posO = $posO + 13;
                $operationId = trim(substr($result, $posO));
                $operationId = str_replace('</operationId>', "", $operationId);
            }

            if ($resultCode == '') {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - Claro ERROR : ' . 'No fue posible consumir el servicio web de Claro para envio de SMS');
                $resultCode = '9999';
                $operationId = '';
                $message = 'No fue posible consumir el servicio web de Claro para envio de SMS';
            }

            //
            actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - Claro : ' . $message);
            $respuesta = array(
                'codigoError' => sprintf("%04s", $resultCode),
                'msgError' => $message,
                'deliveryCod' => $operationId
            );
            if ($cerrarDbx == 'si') {
                $dbx->close();
            }
            return $respuesta;
        }

        // ****************************************************************************************************************************** //
        // Env&iacute;o de mensajes a trav&eacute;s de la plataforma Aldeamo
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'aldeamo') {

            //
            $handler = str_replace(" ", "%20", 'https://apismsi.aldeamo.com/smsr/r/hcws/smsSendGet/' . $sms_usuario . '/' . $sms_clave . '/' . $celular . '/' . $prefijo . '/' . trim($mensaje));

            //
            $ch = curl_init($handler);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $return = curl_exec($ch);
            curl_close($ch);

            //
            list($resultCode, $msg) = explode("|", $return);

            //
            if ($resultCode < 0) {
                $respuesta = array(
                    'codigoError' => $resultCode,
                    'msgError' => $msg,
                    'deliveryCod' => ''
                );
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - Aldeamo ERROR : ' . $msg);
            } else {
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - Aldeamo OK : ' . $resultCode);
                $respuesta = array(
                    'codigoError' => '0000',
                    'msgError' => 'Envio satisfactorio',
                    'deliveryCod' => $resultCode
                );
            }
            if ($cerrarDbx == 'si') {
                $dbx->close();
            }
            return $respuesta;
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma Aldeamo
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'aldeamoREST') {

            $url = 'https://apismsi.aldeamo.com/SmsiWS/smsSendPost';
            $headers = array(
                'Content-Type:application/json',
                'Authorization: Basic ' . base64_encode("$sms_usuario:$sms_clave")
            );
            $payloadName = array();
            $payloadName["country"] = $prefijo;
            $mob = array();
            $mob["mobile"] = $celular;
            $payloadName["addresseeList"][] = $mob;
            $payloadName["message"] = $mensaje;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payloadName));
            $result = curl_exec($ch);
            curl_close($ch);
            $respuestaaldeamo = json_decode($result, true);
            if ($respuestaaldeamo["status"] == '1') {
                $respuesta = array(
                    'codigoError' => '0000',
                    'msgError' => '',
                    'deliveryCod' => $respuestaaldeamo["result"]["receivedRequests"][0]["transactionId"]
                );
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - AldeamoREST OK : ' . $respuestaaldeamo["result"]["receivedRequests"][0]["transactionId"]);
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            } else {
                $respuesta = array(
                    'codigoError' => '9999',
                    'msgError' => $respuestaaldeamo["status"] . ' ' . $respuestaaldeamo["result"]["receivedRequests"][0]["reason"],
                    'deliveryCod' => $respuestaaldeamo["result"]["receivedRequests"][0]["transactionId"]
                );
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - AldeamoREST ERROR : ' . $respuestaaldeamo["status"] . ' ' . $respuestaaldeamo["result"]["receivedRequests"][0]["reason"]);
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            }
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma Aldeamo
        // ****************************************************************************************************************************** //
        if ($sms_proveedor == 'aldeamoRESTv2') {

            // $url = 'https://apismsi.aldeamo.com/SmsiWS/smsSendPost';
            $url = 'https://apitellit.aldeamo.com/SmsiWS/smsSendPost';
            $headers = array(
                'Content-Type:application/json',
                'Authorization: Basic ' . base64_encode("$sms_usuario:$sms_clave")
            );
            $payloadName = array();
            $payloadName["country"] = $prefijo;
            $mob = array();
            $mob["mobile"] = $celular;
            $payloadName["addresseeList"][] = $mob;
            $payloadName["message"] = $mensaje;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payloadName));
            $result = curl_exec($ch);
            curl_close($ch);
            $respuestaaldeamo = json_decode($result, true);
            if ($respuestaaldeamo["status"] == '1') {
                $respuesta = array(
                    'codigoError' => '0000',
                    'msgError' => '',
                    'deliveryCod' => $respuestaaldeamo["result"]["receivedRequests"][0]["transactionId"]
                );
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - AldeamoREST OK : ' . $respuestaaldeamo["result"]["receivedRequests"][0]["transactionId"]);
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            } else {
                $respuesta = array(
                    'codigoError' => '9999',
                    'msgError' => $respuestaaldeamo["status"] . ' ' . $respuestaaldeamo["result"]["receivedRequests"][0]["reason"],
                    'deliveryCod' => $respuestaaldeamo["result"]["receivedRequests"][0]["transactionId"]
                );
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - AldeamoREST ERROR : ' . $respuestaaldeamo["status"] . ' ' . $respuestaaldeamo["result"]["receivedRequests"][0]["reason"]);
                if ($cerrarDbx == 'si') {
                    $dbx->close();
                }
                return $respuesta;
            }
        }

        // ****************************************************************************************************************************** //
        // Envio de mensajes a traves de la plataforma Aldeamo
        // amazonSMS //
        if ($sms_proveedor == 'amazonSMS') {
            // require $_SESSION["generales"]["pathabsoluto"] . '/components/vendor/autoload.php';
            //
            $sharedConfig = array(
                'region' => 'us-east-1',
                'version' => 'latest',
                'credentials' => array(
                    'key' => $sms_usuario,
                    'secret' => $sms_clave,
                ),
            );

            $args = array(
                "MessageAttributes" => [
                    // 'AWS.SNS.SMS.SenderID' => [
                    //     'DataType' => 'String',
                    //     'StringValue' => 'SenderSii'
                    // ],
                    'AWS.SNS.SMS.SMSType' => [
                        'DataType' => 'String',
                        'StringValue' => 'Transactional'
                    ]
                ],
                "Message" => $mensaje,
                "PhoneNumber" => "+" . $prefijo . $celular
            );

            $sns = new \Aws\Sns\SnsClient($sharedConfig);
            $result = $sns->publish($args);
            $txt = "Envio SMS Amazon SNS: Nro. " . $celular . ", mensaje: " . $mensaje . ", MessageId : " . $result->get('MessageId');
            $statusCode = $result->get('@metadata');
            $txt .= ", statusCode : " . $statusCode["statusCode"];
            if ($statusCode["statusCode"] == '200') {
                $respuesta = array(
                    'codigoError' => '0000',
                    'msgError' => '',
                    'deliveryCod' => $result->get('MessageId')
                );
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - AmazonSNS OK: ' . $txt);
            } else {
                $respuesta = array(
                    'codigoError' => '9999',
                    'msgError' => 'Status code : ' . $statusCode["statusCode"],
                    'deliveryCod' => ''
                );
                actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - AmazonSNS ERROR: ' . $txt);
            }
            unset($sns);
            if ($cerrarDbx == 'si') {
                $dbx->close();
            }
            return $respuesta;
        }

        actualizarLogMysqliApi($dbx, '068', $_SESSION["generales"]["codigousuario"], 'f:enviarSMS', '', '', '', $tarea . ' - ' . 'No fue posible enviar el sms no se encontro proveedor configurado');

        if ($cerrarDbx == 'si') {
            $dbx->close();
        }

        $respuesta = array(
            'codigoError' => '9999',
            'msgError' => 'No fue posible enviar el sms no se encontro proveedor configurado',
            'deliveryCod' => ''
        );

        return $respuesta;
    }

    /**
     * Estado general de la liquidación
     * @param type $id
     * @return string
     */
    public static function estadoRespuesta($id)
    {
        $notpay = array("00", "01", "02", "03", "04", "05", "19", "33");
        $pending = array("06", "66", "11", "77");
        $approved = array("07", "09", "10", "12", "13", "14", "15", "16", "17", "18", "20", "21", "22");
        $failed = array("08");

        if (in_array($id, $notpay)) {
            $estado = "NOT PAY";
        }
        if (in_array($id, $pending)) {
            $estado = "PENDING";
        }
        if (in_array($id, $approved)) {
            $estado = "APPROVED";
        }
        if (in_array($id, $failed)) {
            $estado = "FAILED";
        }
        return $estado;
    }

    //
    public static function evaluarFactorAutenticacion($tt)
    {
        // require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');

        //
        $factor = '';

        //
        if ($tt == 'activacion') {
            if (!defined('FACTOR_AUTENTICACION_FIRMADO_ACTIVACIONES') || FACTOR_AUTENTICACION_FIRMADO_ACTIVACIONES == '') {
                $factor = 'DOBLE';
            } else {
                $factor = FACTOR_AUTENTICACION_FIRMADO_ACTIVACIONES;
            }
        }

        //
        if (
            $tt == 'matriculapnat' ||
            $tt == 'matriculapjur' ||
            $tt == 'matriculaest' ||
            $tt == 'matriculaesadl' ||
            $tt == 'matriculasuc' ||
            $tt == 'matriculaage'
        ) {
            if (!defined('FACTOR_AUTENTICACION_FIRMADO_MATRICULAS') || FACTOR_AUTENTICACION_FIRMADO_MATRICULAS == '') {
                $factor = 'DOBLE';
            } else {
                $factor = FACTOR_AUTENTICACION_FIRMADO_MATRICULAS;
            }
        }

        //
        if (
            $tt == 'renovacionmatricula' ||
            $tt == 'renovacionesadl'
        ) {
            if (!defined('FACTOR_AUTENTICACION_FIRMADO_RENOVACION') || FACTOR_AUTENTICACION_FIRMADO_RENOVACION == '') {
                $factor = 'DOBLE';
            } else {
                $factor = FACTOR_AUTENTICACION_FIRMADO_RENOVACION;
            }
        }

        //
        if (
            $tt == 'mutaciondireccion' ||
            $tt == 'mutacionactividad' ||
            $tt == 'mutacionnombre' ||
            $tt == 'mutacionregmer' ||
            $tt == 'mutacionesadl'
        ) {
            if (!defined('FACTOR_AUTENTICACION_FIRMADO_MUTACIONES') || FACTOR_AUTENTICACION_FIRMADO_MUTACIONES == '') {
                $factor = 'DOBLE';
            } else {
                $factor = FACTOR_AUTENTICACION_FIRMADO_MUTACIONES;
            }
        }

        //
        if (
            $tt == 'inscripciondocumentos' ||
            $tt == 'inscripcionesregmer' ||
            $tt == 'inscripcionesesadl' ||
            $tt == 'solicitudcancelacionpnat' ||
            $tt == 'solicitudcancelacionest'
        ) {
            if (!defined('FACTOR_AUTENTICACION_FIRMADO_ACTOSDOCUMENTOS') || FACTOR_AUTENTICACION_FIRMADO_ACTOSDOCUMENTOS == '') {
                $factor = 'DOBLE';
            } else {
                $factor = FACTOR_AUTENTICACION_FIRMADO_ACTOSDOCUMENTOS;
            }
        }

        //
        if (
            $tt == 'inscripcionproponente' ||
            $tt == 'actualizacionproponente' ||
            $tt == 'renovacionproponente' ||
            $tt == 'cancelacionproponente' ||
            $tt == 'cambiodomicilioproponente'
        ) {
            if (!defined('FACTOR_AUTENTICACION_FIRMADO_PROPONENTES') || FACTOR_AUTENTICACION_FIRMADO_PROPONENTES == '') {
                $factor = 'DOBLE';
            } else {
                $factor = FACTOR_AUTENTICACION_FIRMADO_PROPONENTES;
            }
        }

        if ($factor == '') {
            $factor = 'DOBLE';
        }

        return $factor;
    }

    //
    public static function exp_to_dec($float_str)
    {
        $float_str = (string) ((float) ($float_str));
        if (($pos = strpos(strtolower($float_str), 'e')) !== false) {
            $exp = substr($float_str, $pos + 1);
            $num = substr($float_str, 0, $pos);
            if ((($num_sign = $num[0]) === '+') || ($num_sign === '-')) {
                $num = substr($num, 1);
            } else {
                $num_sign = '';
            }
            if ($num_sign === '+') {
                $num_sign = '';
            }
            if ((($exp_sign = $exp[0]) === '+') || ($exp_sign === '-')) {
                $exp = substr($exp, 1);
            } else {
                trigger_error("Could not convert exponential notation to decimal notation: invalid float string '$float_str'", E_USER_ERROR);
            }
            $right_dec_places = (($dec_pos = strpos($num, '.')) === false) ? 0 : strlen(substr($num, $dec_pos + 1));
            $left_dec_places = ($dec_pos === false) ? strlen($num) : strlen(substr($num, 0, $dec_pos));
            if ($exp_sign === '+') {
                $num_zeros = $exp - $right_dec_places;
            } else {
                $num_zeros = $exp - $left_dec_places;
            }
            $zeros = str_pad('', $num_zeros, '0');
            if ($dec_pos !== false) {
                $num = str_replace('.', '', $num);
            }
            if ($exp_sign === '+') {
                return $num_sign . $num . $zeros;
            } else {
                return $num_sign . '0.' . $zeros . $num;
            }
        } else {
            return $float_str;
        }
    }

    public static function existeImagenRepositorio($img, $sistema = "")
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesS3V4.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/s3_v4_api.php');
        if (!defined('TIPO_REPOSITORIO_NUEVAS_IMAGENES')) {
            define('TIPO_REPOSITORIO_NUEVAS_IMAGENES', 'LOCAL');
        }

        //
        $retornar = false;
        $_SESSION["generales"]["mensajeerror"] = 'Imagen no encontrada en los repositorios';

        // Si el repositorio es Local
        if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == '' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'LOCAL' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
            if (file_exists(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img)) {
                return true;
            } else {
                if (file_exists(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("RAD", "rad", $img))) {
                    return true;
                } else {
                    if (file_exists(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("rad", "RAD", $img))) {
                        return true;
                    } else {
                        if (!defined('REPOSITORIO_REMOTO_IMAGENES_WS')) {
                            define('REPOSITORIO_REMOTO_IMAGENES_WS', '');
                        }
                        if (REPOSITORIO_REMOTO_IMAGENES_WS != '') {
                            if (\funcionesGenerales::verificarImagenWsRemoto($img)) {
                                return true;
                            }
                        }
                    }
                }
            }
        }

        // Si el repositorio es Remoto
        if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'WS') {
            if (\funcionesGenerales::verificarImagenWsRemoto($img)) {
                return true;
            }
        }

        // Si el repositorio es Amazon Aws S3
        if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'S3-V4' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
            if (\funcionesS3V4::existenciaS3Version4_2($img)) {
                return true;
            }
        }

        // Si el repositorio es EFS + S3
        if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3' && $sistema == '') {
            if (\funcionesS3V4::existenciaS3Version4_2($img)) {
                return true;
            } else {
                if (file_exists(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img)) {
                    return true;
                } else {
                    if (file_exists(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("RAD", "rad", $img))) {
                        return true;
                    } else {
                        if (file_exists(PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("rad", "RAD", $img))) {
                            return true;
                        } else {
                            if (!defined('REPOSITORIO_REMOTO_IMAGENES_WS')) {
                                define('REPOSITORIO_REMOTO_IMAGENES_WS', '');
                            }
                            if (REPOSITORIO_REMOTO_IMAGENES_WS != '') {
                                if (\funcionesGenerales::verificarImagenWsRemoto($img)) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * 
     * @param type $num
     * @return bool
     */
    public static function esPar($num)
    {
        $ent = intval($num / 2);
        if ($ent * 2 == $num) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $filecontent
     * @return bool
     */
    public static function esPdf($filecontent)
    {
        if (preg_match("/^%PDF-1.4/", $filecontent)) {
            return true;
        } else {
            if (preg_match("/^%PDF-1.5/", $filecontent)) {
                return true;
            } else {
                if (preg_match("/^%PDF-1.6/", $filecontent)) {
                    return true;
                } else {
                    if (preg_match("/^%PDF-1.7/", $filecontent)) {
                        return true;
                    } else {
                        if (preg_match("/^%PDF-1/", $filecontent)) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                }
            }
        }
    }

    //
    public static function filtrarCaracteres($txt)
    {
        /*
          \u00e1 -> á
          \u00e9 -> é
          \u00ed -> í
          \u00f3 -> ó
          \u00fa -> ú
          \u00c1 -> Á
          \u00c9 -> É
          \u00cd -> Í
          \u00d3 -> Ó
          \u00da -> Ú
          \u00f1 -> ñ
          \u00d1 -> Ñ
         */

        $txt = str_replace("&aacute;", '\u00e1', $txt);
        $txt = str_replace("&eacute;", '\u00e9', $txt);
        $txt = str_replace("&iacute;", '\u00ed', $txt);
        $txt = str_replace("&oacute;", '\u00f3', $txt);
        $txt = str_replace("&uacute;", '\u00fa', $txt);
        $txt = str_replace("&Aacute;", '\u00c1', $txt);
        $txt = str_replace("&Eacute;", '\u00c9', $txt);
        $txt = str_replace("&Iacute;", '\u00cd', $txt);
        $txt = str_replace("&Oacute;", '\u00d3', $txt);
        $txt = str_replace("&Uacute;", '\u00da', $txt);
        $txt = str_replace("&ntilde;", '\u00f1', $txt);
        $txt = str_replace("&Ntilde;", '\u00d1', $txt);
        return $txt;
    }

    public static function buscarRangoTarifa($dbx, $idservicio = '', $ano = 0, $base = 0, $tipotarifa = 'tarifa')
    {
        $retornar = 0;
        if ($ano < 1993) {
            $ano = 1993;
        }
        $result = retornarRegistrosMysqliApi($dbx, 'mreg_tarifas', "idservicio='" . $idservicio . "' and ano='" . $ano . "'", "idrango");
        if ($result === false || empty($result)) {
            $retornar = false;
        } else {
            $retornar = 0;
            foreach ($result as $res) {
                if (doubleval($res["topeminimo"]) <= doubleval($base) && doubleval($res["topemaximo"]) >= doubleval($base)) {
                    if ($tipotarifa == 'tarifa') {
                        $retornar = doubleval($res["tarifa"]);
                    }
                    if ($tipotarifa == 'tarifapnat') {
                        $retornar = doubleval($res["tarifapnat"]);
                    }
                    if ($tipotarifa == 'tarifapjur') {
                        $retornar = doubleval($res["tarifapjur"]);
                    }
                }
            }
        }
        return $retornar;
    }

    public static function borrarEnters($txt)
    {
        $txt1 = str_replace(chr(13) . chr(10), " - ", $txt);
        $txt1 = str_replace(chr(10), " ", $txt1);
        $txt1 = str_replace(chr(13), " ", $txt1);
        $txt1 = str_replace(";", " ", $txt1);
        $txt1 = str_replace(",", " ", $txt1);
        $txt1 = str_replace("&#39", " ", $txt1);
        $txt1 = str_replace("\\t", '', $txt1);
        return $txt1;
    }

    public static function borrarPalabrasAutomaticas($txt, $comple = '')
    {
        $salida = $txt;
        if ($comple != '') {
            $pos = strpos($txt, $comple);
            if ($pos) {
                $pos = $pos - 1;
                $salida = substr($txt, 0, $pos);
            }
        }
        $pos = strpos($txt, '- EN LIQUIDACION');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($txt, 0, $pos);
        }

        $pos = strpos($txt, '- EN LIQUIDACION JUDICIAL');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($txt, 0, $pos);
        }

        $pos = strpos($txt, '- EN LIQUIDACION FORZOSA');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($txt, 0, $pos);
        }

        $pos = strpos($txt, '- EN ACUERDO DE REESTRUCTURACION');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($txt, 0, $pos);
        }
        $pos = strpos($txt, '- EN REESTRUCTURACION');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($txt, 0, $pos);
        }
        $pos = strpos($txt, '- EN REORGANIZACION');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($txt, 0, $pos);
        }

        $pos = strpos($txt, 'EN LIQUIDACION');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($txt, 0, $pos);
        }

        $pos = strpos($txt, 'EN LIQUIDACION JUDICIAL');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($txt, 0, $pos);
        }

        $pos = strpos($txt, 'EN LIQUIDACION FORZOSA');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($txt, 0, $pos);
        }

        $pos = strpos($txt, 'EN ACUERDO DE REESTRUCTURACION');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($txt, 0, $pos);
        }
        $pos = strpos($txt, 'EN REESTRUCTURACION');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($txt, 0, $pos);
        }
        $pos = strpos($txt, 'EN REORGANIZACION');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($txt, 0, $pos);
        }
        $pos = strpos($txt, 'EN RECUPERACION EMPRESARIAL');
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($txt, 0, $pos);
        }
        return $salida;
    }

    public static function buscaTarifa($dbx, $idservicio = '', $ano = 0, $cantidad = 0, $base = 0, $tipotarifa = 'tarifa')
    {
        $tarifa = 0;
        $arrServicio = retornarRegistroMysqliApi($dbx, 'mreg_servicios', "idservicio='" . $idservicio . "'");
        if (!$arrServicio) {
            return false;
        }
        if ($arrServicio == 0) {
            return false;
        }
        // Liquida por valor &uacute;nico
        if ($arrServicio["idclasevalor"] == '1') {
            $tarifa = $arrServicio["valorservicio"];
        }
        // Liquida por rango de tarifas
        if ($arrServicio["idclasevalor"] == '2') {
            $tarifa = \funcionesGenerales::buscarRangoTarifa($dbx, $idservicio, $ano, $base, $tipotarifa);
        }
        // Liquida por porcentaje
        if ($arrServicio["idclasevalor"] == '4') {
            $tarifa = intval($arrServicio["valorservicio"] * $base / 100);
        }
        // Liquida por c&aacute;lculo
        if ($arrServicio["idclasevalor"] == '5') {
            $tarifa = intval($arrServicio["valorservicio"] * $base / 100);
        }
        $tarifa = $tarifa * $cantidad;

        // 2015-12-29: JINT : Redondeos
        if (!isset($arrServicio["redondeo"])) {
            return $tarifa;
        }

        if (trim($arrServicio["redondeo"]) == '') {
            return $tarifa;
        }

        switch ($arrServicio["redondeo"]) {

            case "50":
                $ent = intval($tarifa / 50) * 50;
                $res = $tarifa - $ent;
                if ($res <= 25) {
                    $tarifa = $ent;
                } else {
                    $tarifa = $ent + 50;
                }
                break;

            case "100":
                $ent = intval($tarifa / 100) * 100;
                $res = $tarifa - $ent;
                if ($res <= 50) {
                    $tarifa = $ent;
                } else {
                    $tarifa = $ent + 100;
                }
                break;

            case "500":
                $ent = intval($tarifa / 500) * 500;
                $res = $tarifa - $ent;
                if ($res <= 250) {
                    $tarifa = $ent;
                } else {
                    $tarifa = $ent + 500;
                }
                break;

            case "1000":
                $ent = intval($tarifa / 1000) * 1000;
                $res = $tarifa - $ent;
                if ($res < 500) {
                    $tarifa = $ent;
                } else {
                    $tarifa = $ent + 1000;
                }
                break;

            case "50+":
                if (intval($tarifa) < 50) {
                    $tarifa = 50;
                }
                break;
            case "100+":
                if (intval($tarifa) < 100) {
                    $tarifa = 100;
                }
                break;
            case "500+":
                if (intval($tarifa) < 500) {
                    $tarifa = 500;
                }
                break;
            case "500+":
                if (intval($tarifa) < 1000) {
                    $tarifa = 1000;
                }
                break;
        }
        return $tarifa;
    }

    /**
     * 
     * @param type $dbx
     * @param type $ide
     * @param type $mat
     * @param type $pro
     * @param type $raz
     * @param type $pal
     * @param type $retornar
     * @param type $incluir (TO, PN, ES, PJ, ESADL, SUC, AGE)
     * @return type
     */
    public static function buscarExpedientes($dbx = null, $ide = '', $mat = '', $pro = '', $raz = '', $pal = '', $retornar = 200, $incluir = 'TO')
    {
        $res = array();
        if ($ide != '') {
            $condicion = "(numid like '" . $ide . "%' or nit like '" . $ide . "%')";
            if ($incluir == 'PN') {
                $condicion .= " and organizacion = '01'";
            }
            if ($incluir == 'ES') {
                $condicion .= " and organizacion = '02'";
            }
            if ($incluir == 'SUC') {
                $condicion .= " and categoria = '2'";
            }
            if ($incluir == 'AGE') {
                $condicion .= " and categoria = '3'";
            }
            if ($incluir == 'ESADL') {
                $condicion .= " and (organizacion = '12' or organizacion = '14') and categoria = '1'";
            }
            if ($incluir == 'PJ') {
                $condicion .= " and organizacion > '02' and organizacion <> '12' and organizacion <> '14' and categoria = '1'";
            }
            $res = retornarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $condicion, "numid", "*", 0, $retornar);
        }

        if ($mat != '') {
            $condicion = "matricula >= '" . $mat . "'";
            if ($incluir == 'PN') {
                $condicion .= " and organizacion = '01'";
            }
            if ($incluir == 'ES') {
                $condicion .= " and organizacion = '02'";
            }
            if ($incluir == 'SUC') {
                $condicion .= " and categoria = '2'";
            }
            if ($incluir == 'AGE') {
                $condicion .= " and categoria = '3'";
            }
            if ($incluir == 'ESADL') {
                $condicion .= " and (organizacion = '12' or organizacion = '14') and categoria = '1'";
            }
            if ($incluir == 'PJ') {
                $condicion .= " and organizacion > '02' and organizacion <> '12' and organizacion <> '14' and categoria = '1'";
            }
            $res = retornarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $condicion, "matricula", "*", 0, $retornar);
        }

        if ($pro != '') {
            $condicion = "proponente >= '" . $pro . "'";
            $res = retornarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $condicion, "proponente", "*", 0, $retornar);
        }

        if ($raz != '') {
            $condicion = "razonsocial like '" . $raz . "%'";
            if ($incluir == 'PN') {
                $condicion .= " and organizacion = '01'";
            }
            if ($incluir == 'ES') {
                $condicion .= " and organizacion = '02'";
            }
            if ($incluir == 'SUC') {
                $condicion .= " and categoria = '2'";
            }
            if ($incluir == 'AGE') {
                $condicion .= " and categoria = '3'";
            }
            if ($incluir == 'ESADL') {
                $condicion .= " and (organizacion = '12' or organizacion = '14') and categoria = '1'";
            }
            if ($incluir == 'PJ') {
                $condicion .= " and organizacion > '02' and organizacion <> '12' and organizacion <> '14' and categoria = '1'";
            }
            $res = retornarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $condicion, "razonsocial", "*", 0, $retornar);
        }

        if ($pal != '') {
            $condicion = "";
            $cantidad_palabras = 0;
            $palabras_busqueda = explode(" ", $pal);
            foreach ($palabras_busqueda as $palabra) {
                $cantidad_palabras++;
                if ($cantidad_palabras == 1) {
                    $condicion .= "(razonsocial like '%" . $palabra . "%' or sigla like '%" . $palabra . "%')";
                } else {
                    $condicion .= " and (razonsocial like '%" . $palabra . "%'  or sigla like '%" . $palabra . "%')";
                }
            }
            if ($incluir == 'PN') {
                $condicion .= " and organizacion = '01'";
            }
            if ($incluir == 'ES') {
                $condicion .= " and organizacion = '02'";
            }
            if ($incluir == 'SUC') {
                $condicion .= " and categoria = '2'";
            }
            if ($incluir == 'AGE') {
                $condicion .= " and categoria = '3'";
            }
            if ($incluir == 'ESADL') {
                $condicion .= " and (organizacion = '12' or organizacion = '14') and categoria = '1'";
            }
            if ($incluir == 'PJ') {
                $condicion .= " and organizacion > '02' and organizacion <> '12' and organizacion <> '14' and categoria = '1'";
            }

            $res = retornarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $condicion, "razonsocial", "*", 0, $retornar);
        }
        return $res;
    }

    public static function consultarMultasPolicia($dbx, $tid, $id, $idliq = 0)
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $reintentar = 3;
        $multadovencido = 'NO';
        while ($reintentar > 0) {
            $buscartoken = true;
            $name = PATH_ABSOLUTO_SITIO . '/tmp/' . CODIGO_EMPRESA . '-tokenponal.txt';
            if (file_exists($name)) {
                $x = file_get_contents($name, true);
                list($token, $expira) = explode("|", $x);
                $act = date("Y-m-d H:i:s");
                if ($act <= $expira) {
                    $buscartoken = false;
                }
            }

            if ($buscartoken) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://catalogoservicioweb.policia.gov.co/sw/token');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "username=fvera@confecamaras.org.co&password=fveraPolicia2017*2018&grant_type=password");
                $result = curl_exec($ch);
                curl_close($ch);
                $resultado = json_decode($result, true);
                $access_token = $resultado['access_token'];
                $fecha = date('Y-m-d H:i:s', (strtotime("+20 Hours")));
                $f = fopen($name, "w");
                fwrite($f, $access_token . '|' . $fecha);
                fclose($f);
            }

            //
            if (file_exists($name)) {
                $x = file_get_contents($name, true);
                list($access_token, $expira) = explode("|", $x);
            }

            //
            $data = array(
                'codigoCamara' => CODIGO_EMPRESA,
                'tipoConsulta' => 'CC',
                'numeroIdentificacion' => $id
            );

            //
            $nameLog = 'api_validacionMultasPonal_' . date("Ymd");

            //
            $fields = json_encode($data);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://catalogoservicioweb.policia.gov.co/sw/api/Multa/ConsultaMultaVencidaSeisMeses');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            $result = curl_exec($ch);
            curl_close($ch);

            //
            \logApi::general2($nameLog, $tid . '-' . $id, $result);

            //
            if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"])) {
                mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"], 0777);
                \funcionesGenerales::crearIndex(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"]);
            }
            if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/")) {
                mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/", 0777);
                \funcionesGenerales::crearIndex(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/");
            }
            if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/ponal/")) {
                mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/ponal/", 0777);
                \funcionesGenerales::crearIndex(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/ponal/");
            }

            //
            $name1 = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/ponal/validaciones-' . date("Ym") . '.log';
            $f1 = fopen($name1, "a");
            fwrite($f1, date("Y-m-d") . '|' . date("His") . '|' . $tid . '|' . $id . '|' . $result . chr(13) . chr(10));
            fclose($f1);

            //
            if (\funcionesGenerales::isJson($result)) {
                $resultado = json_decode($result, true);
                if (isset($resultado["Message"])) {
                    if ($resultado["Message"] == 'Authorization has been denied for this request.') {
                        unlink($name);
                        $reintentar--;
                    } else {
                        $reintentar = 0;
                    }
                } else {
                    $fecx = date("Ymd");
                    $horx = date("His");
                    foreach ($resultado as $multa) {
                        if (TIPO_AMBIENTE == 'PRUEBAS') {
                            if ($id = '1004726620') {
                                $multa["MULTA_VENCIDA"] = 'SI';
                            }
                        }

                        $arrCampos = array(
                            'fecha',
                            'hora',
                            'tipoidentificacion',
                            'identificacion',
                            'nombres',
                            'apellidos',
                            'nit',
                            'razonsocial',
                            'estado',
                            'fechaimposicion',
                            'multavencida',
                            'direccionhechos',
                            'codigomunicipio',
                            'nombremunicipio',
                            'codigodpto',
                            'nombredpto',
                            'codigobarrio',
                            'nombrebarrio',
                            'numeralinfringido',
                            'articuloinfringido',
                            'idliquidacion'
                        );
                        $arrValores = array(
                            "'" . $fecx . "'",
                            "'" . $horx . "'",
                            "'" . $tid . "'",
                            "'" . $id . "'",
                            "'" . addslashes($multa["NOMBRES"]) . "'",
                            "'" . addslashes($multa["APELLIDOS"]) . "'",
                            "'" . $multa["NIT"] . "'",
                            "'" . addslashes($multa["RAZON_SOCIAL"]) . "'",
                            "'" . $multa["ESTADO"] . "'",
                            "'" . $multa["FECHA_IMPOSICION"] . "'",
                            "'" . $multa["MULTA_VENCIDA"] . "'",
                            "'" . addslashes($multa["DIRECCION_HECHOS"]) . "'",
                            "'" . $multa["COD_MUNICIPIO"] . "'",
                            "'" . addslashes($multa["MUNICIPIO"]) . "'",
                            "'" . $multa["COD_DEPARTAMENTO"] . "'",
                            "'" . addslashes($multa["DEPARTAMENTO"]) . "'",
                            "'" . $multa["COD_BARRIO"] . "'",
                            "'" . addslashes($multa["BARRIO"]) . "'",
                            "'" . addslashes($multa["ARTICULO_INFRINGIDO"]) . "'",
                            "'" . addslashes($multa["NUMERAL_INFRINGIDO"]) . "'",
                            $idliq
                        );
                        insertarRegistrosMysqliApi($dbx, 'mreg_multas_ponal', $arrCampos, $arrValores);
                        //
                        if ($multa["MULTA_VENCIDA"] == 'SI') {
                            $multadovencido = 'SI';
                        }
                    }
                    $arrCampos = array(
                        'sincronizomultasponal',
                        'fechasincronizomultasponal',
                        'resultadosincronizomultasponal'
                    );
                    $arrValores = array(
                        "'SI'",
                        "'" . date("Ymd") . "'",
                        "'" . $multadovencido . "'"
                    );
                    regrabarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "idclase='" . $tid . "' and numid='" . $id . "'");
                    $reintentar = 0;
                }
            } else {
                $arrCampos = array(
                    'sincronizomultasponal',
                    'fechasincronizomultasponal',
                    'resultadosincronizomultasponal'
                );
                $arrValores = array(
                    "'SI'",
                    "'" . date("Ymd") . "'",
                    "'" . $multadovencido . "'"
                );
                regrabarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "idclase='" . $tid . "' and numid='" . $id . "'");
                $reintentar = 0;
            }
        }
        return $multadovencido;
    }

    public static function calcularDv($id)
    {
        $id = str_replace(array(".", ",", "-", " "), "", $id);
        $entrada = sprintf("%015s", $id);
        $identificacion = substr($entrada, 0, 15);
        $miContador = 0;
        $miResiduo = 0;
        $miChequeo = 0;
        $miArreglo = array(71, 67, 59, 53, 47, 43, 41, 37, 29, 23, 19, 17, 13, 7, 3);
        for ($miContador = 0; $miContador < strlen($identificacion); $miContador++) {
            $miChequeo = $miChequeo + (intval(substr($entrada, $miContador, 1)) * intval($miArreglo[$miContador]));
        }
        $miResiduo = $miChequeo % 11;
        if ($miResiduo > 1) {
            $nuevoDV = 11 - $miResiduo;
        } else {
            $nuevoDV = $miResiduo;
        }
        return $nuevoDV;
    }

    //
    public static function cambiarTildes($txt)
    {
        return str_replace(array('&aacute;', '&eacute;', '&iacute;', '&oacute;', '&uacute;', '&Aacute;', '&Eacute;', '&Iacute;', '&Oacute;', '&Uacute;', '&ntilde;', '&Ntilde;'), array('a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'ñ', 'Ñ'), $txt);
    }

    //
    public static function cambiarTildesEnes($txt)
    {
        return str_replace(array('&aacute;', '&eacute;', '&iacute;', '&oacute;', '&uacute;', '&Aacute;', '&Eacute;', '&Iacute;', '&Oacute;', '&Uacute;', '&ntilde;', '&ntilde;', '\''), array('a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N', '´'), $txt);
    }

    public static function cambiarHtmlSustituto($txt)
    {
        $txt = str_replace(" ", "[0]", $txt);
        $txt = str_replace("<", "[1]", $txt);
        $txt = str_replace(">", "[2]", $txt);
        $txt = str_replace("/", "[3]", $txt);
        $txt = str_replace("&nbsp;", "[4]", $txt);
        $txt = str_replace("\"", "[5]", $txt);
        $txt = str_replace("'", "[6]", $txt);
        $txt = str_replace("&", "[7]", $txt);
        $txt = str_replace("?", "[8]", $txt);
        $txt = str_replace("&aacute;", "[9]", $txt);
        $txt = str_replace("&eacute;", "[10]", $txt);
        $txt = str_replace("&iacute;", "[11]", $txt);
        $txt = str_replace("&oacute;", "[12]", $txt);
        $txt = str_replace("&uacute;", "[13]", $txt);
        $txt = str_replace("&ntilde;", "[14]", $txt);
        $txt = str_replace("&ntilde;", "[15]", $txt);
        $txt = str_replace("+", "[16]", $txt);
        $txt = str_replace("#", "[17]", $txt);
        $txt = str_replace("&aacute;", "[18]", $txt);
        $txt = str_replace("&eacute;", "[19]", $txt);
        $txt = str_replace("&iacute;", "[20]", $txt);
        $txt = str_replace("&oacute;", "[21]", $txt);
        $txt = str_replace("&uacute;", "[22]", $txt);
        return $txt;
    }

    public static function cambiarSustitutoHtml($txt)
    {
        $txt = str_replace("[0]", " ", $txt);
        $txt = str_replace("[1]", "<", $txt);
        $txt = str_replace("[2]", ">", $txt);
        $txt = str_replace("[3]", "/", $txt);
        $txt = str_replace("[4]", " ", $txt);
        $txt = str_replace("[5]", "\"", $txt);
        $txt = str_replace("[6]", "'", $txt);
        $txt = str_replace("[7]", "&", $txt);
        $txt = str_replace("[8]", "?", $txt);
        $txt = str_replace("[9]", "&aacute;", $txt);
        $txt = str_replace("[10]", "&eacute;", $txt);
        $txt = str_replace("[11]", "&iacute;", $txt);
        $txt = str_replace("[12]", "&oacute;", $txt);
        $txt = str_replace("[13]", "&uacute;", $txt);
        $txt = str_replace("[14]", "&ntilde;", $txt);
        $txt = str_replace("[15]", "&ntilde;", $txt);
        $txt = str_replace("[16]", "+", $txt);
        $txt = str_replace("[17]", "#", $txt);
        $txt = str_replace("[18]", "&aacute;", $txt);
        $txt = str_replace("[19]", "&eacute;", $txt);
        $txt = str_replace("[20]", "&iacute;", $txt);
        $txt = str_replace("[21]", "&oacute;", $txt);
        $txt = str_replace("[22]", "&uacute;", $txt);
        $txt = str_replace("[menorque]", "<", $txt);
        $txt = str_replace("[mayorque]", ">", $txt);
        $txt = str_replace("[slash]", "/", $txt);
        $txt = str_replace("[caracterblanco]", "&nbsp;", $txt);
        $txt = str_replace("[comilladoble]", "\"", $txt);
        $txt = str_replace("[comillasimple]", "'", $txt);
        $txt = str_replace("[ampersand]", "&", $txt);
        $txt = str_replace("[interrogacion]", "?", $txt);
        $txt = str_replace("[atilde]", "&aacute;", $txt);
        $txt = str_replace("[etilde]", "&eacute;", $txt);
        $txt = str_replace("[itilde]", "&iacute;", $txt);
        $txt = str_replace("[otilde]", "&oacute;", $txt);
        $txt = str_replace("[utilde]", "&uacute;", $txt);
        $txt = str_replace("[ene]", "&ntilde;", $txt);
        $txt = str_replace("[ENE]", "&ntilde;", $txt);
        $txt = str_replace("[mas]", "+", $txt);
        return $txt;
    }

    public static function cambiarSustitutoHtmlBootstrap($txt)
    {
        $txt = str_replace("[0]", " ", $txt);
        $txt = str_replace("[1]", "<", $txt);
        $txt = str_replace("[2]", ">", $txt);
        $txt = str_replace("[3]", "/", $txt);
        $txt = str_replace("[4]", " ", $txt);
        $txt = str_replace("[5]", "\"", $txt);
        $txt = str_replace("[6]", "'", $txt);
        $txt = str_replace("[7]", "&", $txt);
        $txt = str_replace("[8]", "?", $txt);
        $txt = str_replace("[9]", "á", $txt);
        $txt = str_replace("[10]", "é", $txt);
        $txt = str_replace("[11]", "í", $txt);
        $txt = str_replace("[12]", "ó", $txt);
        $txt = str_replace("[13]", "ú", $txt);
        $txt = str_replace("[14]", "ñ", $txt);
        $txt = str_replace("[15]", "ñ", $txt);
        $txt = str_replace("[16]", "+", $txt);
        $txt = str_replace("[17]", "#", $txt);
        $txt = str_replace("[18]", "á", $txt);
        $txt = str_replace("[19]", "é", $txt);
        $txt = str_replace("[20]", "í", $txt);
        $txt = str_replace("[21]", "ó", $txt);
        $txt = str_replace("[22]", "ú", $txt);
        $txt = str_replace("[menorque]", "<", $txt);
        $txt = str_replace("[mayorque]", ">", $txt);
        $txt = str_replace("[slash]", "/", $txt);
        $txt = str_replace("[caracterblanco]", " ", $txt);
        $txt = str_replace("[comilladoble]", '"', $txt);
        $txt = str_replace("[comillasimple]", "'", $txt);
        $txt = str_replace("[ampersand]", "&", $txt);
        $txt = str_replace("[interrogacion]", "?", $txt);
        $txt = str_replace("[atilde]", "á", $txt);
        $txt = str_replace("[etilde]", "é", $txt);
        $txt = str_replace("[itilde]", "í", $txt);
        $txt = str_replace("[otilde]", "ó", $txt);
        $txt = str_replace("[utilde]", "ú", $txt);
        $txt = str_replace("[ene]", "ñ", $txt);
        $txt = str_replace("[ENE]", "Ñ", $txt);
        $txt = str_replace("[mas]", "+", $txt);
        return $txt;
    }

    public static function calcularAnos($f1, $f2)
    {
        $f1 = intval($f1);
        $f2 = intval($f2);
        if ($f1 > $f2) {
            $diaact = substr($f1, 6, 2);
            $mesact = substr($f1, 4, 2);
            $anoact = substr($f1, 0, 4);
            $diaini = substr($f2, 6, 2);
            $mesini = substr($f2, 4, 2);
            $anoini = substr($f2, 0, 4);
        } else {
            $diaact = substr($f2, 6, 2);
            $mesact = substr($f2, 4, 2);
            $anoact = substr($f2, 0, 4);
            $diaini = substr($f1, 6, 2);
            $mesini = substr($f1, 4, 2);
            $anoini = substr($f1, 0, 4);
        }


        if (($mesini == $mesact) && ($diaini > $diaact)) {
            $anoact = $anoact - 1;
        }
        if ($mesini > $mesact) {
            $anoact = $anoact - 1;
        }
        $edad = $anoact - $anoini;
        return $edad;
    }

    public static function calcula_numero_dia_semana($dia, $mes, $ano)
    {
        $numerodiasemana = date('w', mktime(0, 0, 0, $mes, $dia, $ano));
        return $numerodiasemana;

        if ($numerodiasemana == 0) {
            $numerodiasemana = 6;
        } else {
            $numerodiasemana--;
        }
        return $numerodiasemana;
    }

    public static function calcularAnosMesesDias($fechaactual, $fechainicial)
    {

        //
        $nohabiles = 0;

        // Ajusta la fecha inicial al formato requerido para el explode
        if (ltrim($fechainicial, '0') == '') {
            $fechainicial = '0000-00-00';
        }
        if (strlen($fechainicial) == 8) {
            $f1 = substr($fechainicial, 0, 4);
            $f2 = substr($fechainicial, 4, 2);
            $f3 = substr($fechainicial, 6, 2);
            $fechainicial = $f1 . '-' . $f2 . '-' . $f3;
        }

        // Ajusta la fecha actual al formato requerido para el explode
        if (ltrim($fechaactual, '0') == '') {
            $fechaactual = '0000-00-00';
        }
        if (strlen($fechaactual) == 8) {
            $f1 = substr($fechaactual, 0, 4);
            $f2 = substr($fechaactual, 4, 2);
            $f3 = substr($fechaactual, 6, 2);
            $fechaactual = $f1 . '-' . $f2 . '-' . $f3;
        }

        // separamos en partes las fechas
        $array_inicial = explode("-", $fechainicial);
        $array_actual = explode("-", $fechaactual);

        $anos = $array_actual[0] - $array_inicial[0]; // calculamos a&ntilde;os
        $meses = $array_actual[1] - $array_inicial[1]; // calculamos meses
        $dias = $array_actual[2] - $array_inicial[2]; // calculamos d&iacute;as
        //ajuste de posible negativo en $d&iacute;as
        if ($dias < 0) {
            --$meses;

            //ahora hay que sumar a $dias los dias que tiene el mes anterior de la fecha actual
            switch ($array_actual[1]) {
                case 1:
                    $dias_mes_anterior = 31;
                    break;
                case 2:
                    $dias_mes_anterior = 31;
                    break;
                case 3:
                    if (checkdate(2, 29, $array_actual[0])) {
                        $dias_mes_anterior = 29;
                        break;
                    } else {
                        $dias_mes_anterior = 28;
                        break;
                    }
                case 4:
                    $dias_mes_anterior = 31;
                    break;
                case 5:
                    $dias_mes_anterior = 30;
                    break;
                case 6:
                    $dias_mes_anterior = 31;
                    break;
                case 7:
                    $dias_mes_anterior = 30;
                    break;
                case 8:
                    $dias_mes_anterior = 31;
                    break;
                case 9:
                    $dias_mes_anterior = 31;
                    break;
                case 10:
                    $dias_mes_anterior = 30;
                    break;
                case 11:
                    $dias_mes_anterior = 31;
                    break;
                case 12:
                    $dias_mes_anterior = 30;
                    break;
            }
            $dias = $dias + $dias_mes_anterior;
        }

        //ajuste de posible negativo en $meses
        if ($meses < 0) {
            --$anos;
            $meses = $meses + 12;
        }

        $res = array($anos, $meses, $dias);
        return $res;
    }

    public static function calcularDiasCalendario($fechaactual, $fechainicial)
    {

        //
        $arraynohabiles = array(
            '20130101',
            '20130107',
            '20130325',
            '20130328',
            '20130329',
            '20130501',
            '20130513',
            '20130603',
            '21030610',
            '20130701',
            '20130720',
            '20130807',
            '20130819',
            '20131014',
            '20131104',
            '20131111',
            '20131225',
            '20140101',
            '20140106',
            '20140324',
            '20140417',
            '20140418',
            '20140420',
            '20140501',
            '20140602',
            '20140623',
            '20140630',
            '20140720',
            '20140807',
            '20140818',
            '20141013',
            '20141103',
            '20141117',
            '20141208',
            '20141225',
            '20150101',
            '20150112',
            '20150323',
            '20150402',
            '20150403',
            '20150501',
            '20150518',
            '20150608',
            '20150615',
            '20150629',
            '20150720',
            '20150807',
            '20150817',
            '20151012',
            '20151102',
            '20151116',
            '20151208',
            '20151225',
            '20160101',
            '20160111',
            '20160321',
            '20160324',
            '20160325',
            '20160509',
            '20160530',
            '20160606',
            '20160704',
            '20160720',
            '20160815',
            '20161017',
            '20161107',
            '20161114',
            '20161208',
            '20170109',
            '20170320',
            '20170413',
            '20170414',
            '20170529',
            '20170619',
            '20170626',
            '20170720',
            '20170807',
            '20170814',
            '20171016',
            '20171106',
            '20171113' . '20171208',
            '20171225'
        );

        //
        $nohabiles = 0;

        // Ajusta la fecha inicial al formato requerido para el explode
        if (ltrim($fechainicial, '0') == '') {
            $fechainicial = '0000-00-00';
        }
        if (strlen($fechainicial) == 8) {
            $f1 = substr($fechainicial, 0, 4);
            $f2 = substr($fechainicial, 4, 2);
            $f3 = substr($fechainicial, 6, 2);
            $fechainicial = $f1 . '-' . $f2 . '-' . $f3;
        }

        // Ajusta la fecha actual al formato requerido para el explode
        if (ltrim($fechaactual, '0') == '') {
            $fechaactual = '0000-00-00';
        }
        if (strlen($fechaactual) == 8) {
            $f1 = substr($fechaactual, 0, 4);
            $f2 = substr($fechaactual, 4, 2);
            $f3 = substr($fechaactual, 6, 2);
            $fechaactual = $f1 . '-' . $f2 . '-' . $f3;
        }

        // separamos en partes las fechas
        $array_inicial = str_replace("-", "", $fechainicial);
        $array_actual = str_replace("-", "", $fechaactual);

        $totaldias = 0;
        $totalnohabiles = 0;
        $iDias = $array_inicial;

        while ($iDias < $array_actual) {

            switch (jddayofweek(cal_to_jd(CAL_GREGORIAN, date(substr($iDias, 4, 2)), date(substr($iDias, 6, 2)), date(substr($iDias, 0, 4))), 0)) {
                case 0:
                case 6:
                    $totalnohabiles++;
                    break;
                case 1:
                case 2:
                case 3:
                case 4:
                case 5:
                    if (in_array($iDias, $arraynohabiles)) {
                        $totalnohabiles++;
                    }
                    break;
            }

            $totaldias++;
            $d = intval(substr($iDias, 6, 2));
            $m = intval(substr($iDias, 4, 2));
            $a = intval(substr($iDias, 0, 4));
            $d++;
            if ($d == 29) {
                if ($m == 2) {
                    if (intval($a / 4) * 4 != $a) {
                        $d = 1;
                        $m++;
                    }
                }
            }
            if ($d == 31) {
                if ($m == 4 || $m == 6 || $m == 9 || $m == 11) {
                    $d = 1;
                    $m++;
                }
            }
            if ($d == 32) {
                $d = 1;
                $m++;
                if ($m == 13) {
                    $d = 1;
                    $m = 1;
                    $a++;
                }
            }

            $iDias = sprintf("%04s", $a) . sprintf("%02s", $m) . sprintf("%02s", $d);
        }

        return array($totaldias, $totalnohabiles);
    }

    public static function calcularFechaInicial($fbase, $dias, $sumarorestar = '-')
    {
        $fecha = \funcionesGenerales::mostrarFecha($fbase);
        $nuevafecha = strtotime($sumarorestar . $dias . ' day', strtotime($fecha));
        $nuevafecha = date('Y-m-d', $nuevafecha);
        return $nuevafecha;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $mat
     * @param type $origen (actual, alcortereportado, nueva) 
     * @param type $fecorte
     * @param type $activosx
     * @param type $personalx
     * @param type $ciiux
     * @param type $ingresosx
     * @return bool|string
     */
    public static function calcularTamanoEmpresarial($mysqli = null, $mat = '', $origen = 'actual', $fecorte = '', $activosx = 0, $personalx = 0, $ciiux = 0, $ingresosx = 0)
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $nameLog = 'tamanoEmpresarial_' . date("Ymd");

        //
        $retorno = array(
            'codigoerror' => '0000',
            'mensajeerror' => '',
            'codigo' => '',
            'textoresumido' => '',
            'textocompleto' => '',
            'forma' => '',
            'fechadatos' => '',
            'anodatos' => '',
            'activos' => 0,
            'personal' => 0,
            'ciiu' => '',
            'ingresos' => 0,
            'ingresosuvt' => 0,
            'ingresosuvb' => 0,
            'uvt' => 0,
            'uvb' => 0,
            'sector' => '',
            'encontro' => '',
            'fechacorte' => ''
        );

        //
        if ($origen == 'activos') {
            $retorno["ciiu"] = $ciiux;
            $retorno["ingresos"] = $ingresosx;
            $retorno["anodatos"] = '';
            $retorno["fechadatos"] = $fecorte;
            $tamano = \funcionesRegistrales::determinarTamanoEmpresarialActivos($mysqli, $activosx, $personalx, $fecorte);
            if ($tamano === false) {
                $retorno["codigoerror"] = '9995';
                $retorno["mensajeerror"] = 'No localizo salario minimo corte de informacion financiera : ' . $retorno["fechadatos"];
                \logApi::general2($nameLog, $mat, print_r($retorno, true));
                return $retorno;
            }
            $retorno["textocompleto"] = $tamano["tamanotexto"];
            $retorno["textoresumido"] = $tamano["tamanoresumido"];
            $retorno["codigo"] = $tamano["tamanocodigo"];
            $retorno["forma"] = 'activos';
            $retorno["sector"] = '';
            \logApi::general2($nameLog, $mat, print_r($retorno, true));
            return $retorno;
        }

        //
        if ($origen == 'uvt') {
            $retorno["ciiu"] = $ciiux;
            $retorno["ingresos"] = $ingresosx;
            $retorno["anodatos"] = substr($fecorte, 0, 4);
            $retorno["fechadatos"] = $fecorte;
            $tamano = \funcionesRegistrales::determinarTamanoEmpresarialUvts($mysqli, $retorno["ciiu"], $retorno["ingresos"], $retorno["anodatos"], $retorno["fechadatos"], 'si');
            $retorno["textocompleto"] = $tamano["tamanotexto"];
            $retorno["textoresumido"] = $tamano["tamanoresumido"];
            $retorno["codigo"] = $tamano["tamanocodigo"];
            $retorno["forma"] = 'uvt';
            $retorno["ingresosuvt"] = $tamano["ingresosuvt"];
            $retorno["uvt"] = $tamano["uvt"];
            $retorno["sector"] = $tamano["sector"];
            $retorno["fechacorte"] = $fecorte;
            \logApi::general2($nameLog, $mat, print_r($retorno, true));
            return $retorno;
        }

        //
        if ($origen == 'simular') {
            $retorno["ciiu"] = $ciiux;
            $retorno["ingresos"] = $ingresosx;
            $retorno["anodatos"] = date("Y");
            $retorno["fechadatos"] = date("Ymd");
            $tamano = \funcionesRegistrales::determinarTamanoEmpresarialUvbs($mysqli, $retorno["ciiu"], $retorno["ingresos"], $retorno["anodatos"], $retorno["fechadatos"], 'si');
            $retorno["textocompleto"] = $tamano["tamanotexto"];
            $retorno["textoresumido"] = $tamano["tamanoresumido"];
            $retorno["codigo"] = $tamano["tamanocodigo"];
            $retorno["forma"] = 'uvb';
            $retorno["ingresosuvb"] = $tamano["ingresosuvb"];
            $retorno["uvb"] = $tamano["uvb"];
            $retorno["sector"] = $tamano["sector"];
            $retorno["fechacorte"] = $fecorte;
            \logApi::general2($nameLog, $mat, print_r($retorno, true));
            return $retorno;
        }

        //
        if ($origen == 'actual') {
            if ($fecorte == '') {
                $fecorte = date("Ymd");
            }
            $retorno["fechacorte"] = $fecorte;
            if ($mat == '') {
                $retorno["codigoerror"] = '9990';
                $retorno["mensajeerror"] = 'Matricula no reportada';
                \logApi::general2($nameLog, $mat, print_r($retorno, true));
                return $retorno;
            }
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $mat . "'");
            if ($exp === false || empty($exp)) {
                $retorno["codigoerror"] = '9991';
                $retorno["mensajeerror"] = 'La matrícula no se localizó en la BD';
                \logApi::general2($nameLog, $mat, print_r($retorno, true));
                return $retorno;
            }
            if ($exp["organizacion"] == '02' || $exp["categoria"] == '2' || $exp["categoria"] == '3') {
                $retorno["codigoerror"] = '9992';
                $retorno["mensajeerror"] = 'La matrícula solicitada no es una persona natural o sociedad principal';
                \logApi::general2($nameLog, $mat, print_r($retorno, true));
                return $retorno;
            }
            if ($exp["fecmatricula"] >= '20200100' || $exp["fecrenovacion"] >= '20200100') {
                $tams = retornarRegistrosMysqliApi($mysqli, 'mreg_tamano_empresarial', "matricula='" . $mat . "'", "anodatos,fechadatos");
                if ($tams && !empty($tams)) {
                    foreach ($tams as $tx) {
                        $retorno["ciiu"] = $tx["ciiu"];
                        $retorno["ingresos"] = $tx["ingresos"];
                        $retorno["anodatos"] = $tx["anodatos"];
                        $retorno["fechadatos"] = $tx["fechadatos"];
                        $retorno["encontro"] = 'si-957';
                    }
                }
            }
            if ($retorno["encontro"] !== 'si-957') {
                $inffin = retornarRegistrosMysqliApi($mysqli, 'mreg_est_financiera', "matricula='" . $mat . "'", "anodatos,fechadatos");
                if ($inffin && !empty($inffin)) {
                    foreach ($inffin as $fin) {
                        $retorno["activos"] = $fin["acttot"];
                        $retorno["personal"] = $fin["personal"];
                        $retorno["anodatos"] = $fin["anodatos"];
                        $retorno["fechadatos"] = $fin["fechadatos"];
                        $retorno["encontro"] = 'si-1072';
                    }
                }
            }
            if (substr($retorno["encontro"], 0, 2) !== 'si') {
                $retorno["codigoerror"] = '9994';
                $retorno["mensajeerror"] = 'La matrícula no tiene información financiera para calcular el tamaño empresarial';
                \logApi::general2($nameLog, $mat, print_r($retorno, true));
                return $retorno;
            }
            // if ($retorno["fechadatos"] == '') {
            //    $retorno["fechadatos"] = $retorno["anodatos"] . '0101';
            // }
            // if ($retorno["fechadatos"] < '20200101' || $retorno["encontro"] == 'si-1072') {
            if ($retorno["anodatos"] < '2020' || $retorno["encontro"] == 'si-1072') {
                $tamano = \funcionesRegistrales::determinarTamanoEmpresarialActivos($mysqli, $retorno["activos"], $retorno["personal"], $retorno["fechadatos"]);
                if ($tamano === false) {
                    $retorno["codigoerror"] = '9995';
                    $retorno["mensajeerror"] = 'No localizo salario minimo corte de informacion financiera : ' . $retorno["fechadatos"];
                    \logApi::general2($nameLog, $mat, print_r($retorno, true));
                    return $retorno;
                }
                $retorno["textocompleto"] = $tamano["tamanotexto"];
                $retorno["textoresumido"] = $tamano["tamanoresumido"];
                $retorno["codigo"] = $tamano["tamanocodigo"];
                $retorno["forma"] = 'activos';
                $retorno["sector"] = '';
            } else {
                // if ($retorno["fechadatos"] >= '20200101' && $retorno["fechadatos"] <= '20231231') {
                if ($retorno["anodatos"] >= '2020' && $retorno["anodatos"] <= '2023') {
                    $tamano = \funcionesRegistrales::determinarTamanoEmpresarialUvts($mysqli, $retorno["ciiu"], $retorno["ingresos"], $retorno["anodatos"], $retorno["fechadatos"]);
                    $retorno["textocompleto"] = $tamano["tamanotexto"];
                    $retorno["textoresumido"] = $tamano["tamanoresumido"];
                    $retorno["codigo"] = $tamano["tamanocodigo"];
                    $retorno["forma"] = 'uvt';
                    $retorno["ingresosuvt"] = \funcionesGenerales::truncateFloat($tamano["ingresosuvt"], 2);
                    $retorno["uvt"] = $tamano["uvt"];
                    $retorno["sector"] = $tamano["sector"];
                }
                // if ($retorno["fechadatos"] > '20231231') {
                if ($retorno["anodatos"] > '2023') {
                    $tamano = \funcionesRegistrales::determinarTamanoEmpresarialUvbs($mysqli, $retorno["ciiu"], $retorno["ingresos"], $retorno["anodatos"], $retorno["fechadatos"]);
                    $retorno["textocompleto"] = $tamano["tamanotexto"];
                    $retorno["textoresumido"] = $tamano["tamanoresumido"];
                    $retorno["codigo"] = $tamano["tamanocodigo"];
                    $retorno["forma"] = 'uvb';
                    $retorno["ingresosuvb"] = \funcionesGenerales::truncateFloat($tamano["ingresosuvb"], 2);
                    $retorno["uvb"] = $tamano["uvb"];
                    $retorno["sector"] = $tamano["sector"];
                }
            }
            \logApi::general2($nameLog, $mat, print_r($retorno, true));
            return $retorno;
        }

        //
        if ($origen == 'alcortereportado') {
            $retorno["fechacorte"] = $fecorte;
            if ($mat == '') {
                $retorno["codigoerror"] = '9990';
                $retorno["mensajeerror"] = 'Matricula no reportada (*)';
                \logApi::general2($nameLog, $mat, print_r($retorno, true));
                return $retorno;
            }
            $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $mat . "'");
            if ($exp === false || empty($exp)) {
                $retorno["codigoerror"] = '9991';
                $retorno["mensajeerror"] = 'La matrícula no se localizó en la BD';
                \logApi::general2($nameLog, $mat, print_r($retorno, true));
                return $retorno;
            }
            if ($exp["organizacion"] == '02' || $exp["categoria"] == '2' || $exp["categoria"] == '3') {
                $retorno["codigoerror"] = '9992';
                $retorno["mensajeerror"] = 'La matrícula solicitada no es una persona natural o sociedad principal';
                \logApi::general2($nameLog, $mat, print_r($retorno, true));
                return $retorno;
            }
            if ($exp["fecmatricula"] > $fecorte) {
                $retorno["codigoerror"] = '9993';
                $retorno["mensajeerror"] = 'El expediente no existe al corte reportado';
                \logApi::general2($nameLog, $mat, print_r($retorno, true));
                return $retorno;
            }

            //
            if ($fecorte < '20200101') {
                $inffin = retornarRegistrosMysqliApi($mysqli, 'mreg_est_financiera', "matricula='" . $mat . "'", "anodatos,fechadatos");
                if ($inffin && !empty($inffin)) {
                    foreach ($inffin as $fin) {
                        if ($fin["fechadatos"] <= $fecorte) {
                            $retorno["activos"] = $fin["acttot"];
                            $retorno["personal"] = $fin["personal"];
                            $retorno["anodatos"] = $fin["anodatos"];
                            $retorno["fechadatos"] = $fin["fechadatos"];
                            $retorno["encontro"] = 'si-1072';
                        }
                    }
                }
            }

            //
            if ($fecorte >= '20200101' && $fecorte <= '20231231') {
                $tams = retornarRegistrosMysqliApi($mysqli, 'mreg_tamano_empresarial', "matricula='" . $mat . "'", "anodatos,fechadatos");
                if ($tams && !empty($tams)) {
                    foreach ($tams as $tx) {
                        if ($tx["fechadatos"] >= '20200100' && $tx["fechadatos"] <= $fecorte) {
                            $retorno["ciiu"] = $tx["ciiu"];
                            $retorno["ingresos"] = $tx["ingresos"];
                            $retorno["anodatos"] = $tx["anodatos"];
                            $retorno["fechadatos"] = $tx["fechadatos"];
                            $retorno["encontro"] = 'si-957';
                        }
                    }
                }
            }

            //
            if ($fecorte > '20231231') {
                $tams = retornarRegistrosMysqliApi($mysqli, 'mreg_tamano_empresarial', "matricula='" . $mat . "'", "anodatos,fechadatos");
                if ($tams && !empty($tams)) {
                    foreach ($tams as $tx) {
                        if ($tx["fechadatos"] > '20231231' && $tx["fechadatos"] <= $fecorte) {
                            $retorno["ciiu"] = $tx["ciiu"];
                            $retorno["ingresos"] = $tx["ingresos"];
                            $retorno["anodatos"] = $tx["anodatos"];
                            $retorno["fechadatos"] = $tx["fechadatos"];
                            $retorno["encontro"] = 'si-957';
                        }
                    }
                }
            }

            //
            if ($retorno["encontro"] === '') {
                $inffin = retornarRegistrosMysqliApi($mysqli, 'mreg_est_financiera', "matricula='" . $mat . "'", "anodatos,fechadatos");
                if ($inffin && !empty($inffin)) {
                    foreach ($inffin as $fin) {
                        if ($fin["fechadatos"] <= $fecorte) {
                            $retorno["activos"] = $fin["acttot"];
                            $retorno["personal"] = $fin["personal"];
                            $retorno["anodatos"] = $fin["anodatos"];
                            $retorno["fechadatos"] = $fin["fechadatos"];
                            $retorno["encontro"] = 'si-1072';
                        }
                    }
                }
            }

            //
            if (substr($retorno["encontro"], 0, 2) !== 'si') {
                $retorno["codigoerror"] = '9994';
                $retorno["mensajeerror"] = 'La matrícula no tiene información para calcular el tamaño empresarial';
                \logApi::general2($nameLog, $mat, print_r($retorno, true));
                return $retorno;
            }

            //
            // if ($retorno["fechadatos"] < '20200101' || $retorno["encontro"] == 'si-1072') {
            if ($retorno["anodatos"] < '2020' || $retorno["encontro"] == 'si-1072') {
                $tamano = \funcionesRegistrales::determinarTamanoEmpresarialActivos($mysqli, $retorno["activos"], $retorno["personal"], $retorno["fechadatos"]);
                if ($tamano === false) {
                    $retorno["codigoerror"] = '9995';
                    $retorno["mensajeerror"] = 'No localizo salario minimo corte de informacion financiera : ' . $retorno["fechadatos"];
                    \logApi::general2($nameLog, $mat, print_r($retorno, true));
                    return $retorno;
                }
                $retorno["textocompleto"] = $tamano["tamanotexto"];
                $retorno["textoresumido"] = $tamano["tamanoresumido"];
                $retorno["codigo"] = $tamano["tamanocodigo"];
                $retorno["forma"] = 'activos';
                $retorno["sector"] = '';
            } else {
                // if ($retorno["fechadatos"] >= '20200101' && $retorno["fechadatos"] <= '20231231') {
                if ($retorno["anodatos"] >= '2020' && $retorno["anodatos"] <= '2023') {
                    $tamano = \funcionesRegistrales::determinarTamanoEmpresarialUvts($mysqli, $retorno["ciiu"], $retorno["ingresos"], $retorno["anodatos"], $retorno["fechadatos"]);
                    $retorno["textocompleto"] = $tamano["tamanotexto"];
                    $retorno["textoresumido"] = $tamano["tamanoresumido"];
                    $retorno["codigo"] = $tamano["tamanocodigo"];
                    $retorno["forma"] = 'uvt';
                    $retorno["ingresosuvt"] = $tamano["ingresosuvt"];
                    $retorno["uvt"] = $tamano["uvt"];
                    $retorno["sector"] = $tamano["sector"];
                }
                // if ($retorno["fechadatos"] > '20231231') {
                if ($retorno["anodatos"] > '2023') {
                    $tamano = \funcionesRegistrales::determinarTamanoEmpresarialUvbs($mysqli, $retorno["ciiu"], $retorno["ingresos"], $retorno["anodatos"], $retorno["fechadatos"]);
                    $retorno["textocompleto"] = $tamano["tamanotexto"];
                    $retorno["textoresumido"] = $tamano["tamanoresumido"];
                    $retorno["codigo"] = $tamano["tamanocodigo"];
                    $retorno["forma"] = 'uvb';
                    $retorno["ingresosuvb"] = $tamano["ingresosuvb"];
                    $retorno["uvb"] = $tamano["uvb"];
                    $retorno["sector"] = $tamano["sector"];
                }
            }
            \logApi::general2($nameLog, $mat, print_r($retorno, true));
            return $retorno;
        }
    }

    public static function cargarPermisosPublico($mysqli, $path = '', $codemp = '00')
    {
        if ($path != '') {
            $_SESSION["generales"]["pathabsoluto"] = $path;
            $_SESSION["generales"]["codigoempresa"] = $codemp;
        }
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        //
        $array = retornarRegistrosMysqliApi($mysqli, 'bas_permisosespeciales', "1=1", "idpermiso");
        if ($array === false) {
            return false;
        }

        $_SESSION["generales"]["permisos"] = array();
        foreach ($array as $ar) {
            if (strlen($ar["idpermiso"]) == 5) {
                $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["codigo"] = $ar["idpermiso"];
                $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["descripcion"] = $ar["descripcion"];
                $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["actividad"] = $ar["idactividad"];
                $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlexpediente"] = $ar["idcontrolexpediente"];
                $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlestado"] = $ar["idcontrolestado"];
                $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlventana"] = $ar["idventana"];
                $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlwidth"] = $ar["idwidth"];
                $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlheight"] = $ar["idheight"];
                $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlscript"] = $ar["idscript"];

                if ($ar["idcontrolusuario"] == 'T') {
                    $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlusuario"] = 'S';
                } else {
                    $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlusuario"] = 'N';
                    if ($_SESSION["generales"]["tipousuario"] == '01') {
                        $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlusuario"] = 'S';
                    } else {
                        if (contarRegistrosMysqliApi($mysqli, 'usuariospermisosespeciales', "idusuario='" . $_SESSION["generales"]["codigousuario"] . "' and idpermiso='" . $ar["idpermiso"] . "'") == 1) {
                            $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlusuario"] = 'S';
                        }
                    }
                }
            }
        }

        // Inicia el control del contador de navegacion para la session
        $_SESSION["generales"]["contadorcontrolsession"] = 0;
        $f = fopen($_SESSION["generales"]["pathabsoluto"] . '/tmp/session_control_' . session_id() . '.txt', "w");
        fwrite($f, '1');
        fclose($f);

        // Tipo de menu
        // $_SESSION["generales"]["tipomenu"] = retornarClaveValorMysqliApi($mysqli, '01.01.02');
        $_SESSION["generales"]["tipomenu"] = 'ICONOS2';

        unset($array);
        unset($ar);
        return true;
    }

    public static function construirNoticiaSurOccidente($mysqli, $ins)
    {
        $salida = '';

        //
        if ($ins["actosistemaanterior"] == '') {
            return $salida;
        }

        // Ciudad del cambio de domicilio 
        // tipo de dato 74
        if (trim($ins["actosistemaanterior"]) == '15|47|891') {
            $nlib = '';
            $treg = '';
            if (substr($ins["libro"], 0, 2) == 'RM') {
                $nlib = substr($ins["libro"], 2, 2);
                $treg = '1';
            }
            if (substr($ins["libro"], 0, 2) == 'RE') {
                $nlib = substr($ins["libro"], 3, 1);
                $treg = '2';
            }

            $nins = $ins["registro"];
            $salida = 'CAMBIO DE DOMICILIO DESDE LA CIUDAD DE ' . retornarRegistroMysqliApi($mysqli, 'rp_datos_actos', "cod_tipo_registro='" . $treg . "' and cod_libro='" . $nlib . "' and num_inscripcion='" . $nins . "' and cod_tipo_dato='74'", "descripcion");
        }

        //
        return $salida;
    }

    public static function consultarEstablecimientosNacionales($dbx, $tide, $ide)
    {
        $buscartoken = true;
        $name = PATH_ABSOLUTO_SITIO . '/tmp/' . CODIGO_EMPRESA . '-tokenrues.txt';
        if (file_exists($name)) {
            $x = file_get_contents(PATH_ABSOLUTO_SITIO . '/tmp/' . CODIGO_EMPRESA . '-tokenrues.txt', true);
            list($token, $expira) = explode("|", $x);
            $act = date("Y-m-d H:i:s");
            if ($act <= $expira) {
                $buscartoken = false;
            }
        }

        if ($buscartoken) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://ruesapi.rues.org.co/Token');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "username=SIIUser&password=Webapi2017*&grant_type=password");
            $result = curl_exec($ch);
            curl_close($ch);
            $resultado = json_decode($result, true);
            $access_token = $resultado['access_token'];
            $fecha = date('Y-m-d H:i:s', (strtotime("+20 Hours")));
            $f = fopen($name, "w");
            fwrite($f, $access_token . '|' . $fecha);
            fclose($f);
        }

        //
        if (file_exists($name)) {
            $x = file_get_contents(PATH_ABSOLUTO_SITIO . '/tmp/' . CODIGO_EMPRESA . '-tokenrues.txt', true);
            list($access_token, $expira) = explode("|", $x);
        }

        //
        $nameLog = 'api_consultarEstablecimientosNacionales_' . date("Ymd");
        if ($tide != '2') {
            $ide1 = $ide;
            $ide2 = '';
        } else {
            $sep = \funcionesGenerales::separarDv($ide);
            $ide1 = $sep["identificacion"];
            $ide2 = $sep["dv"];
        }
        $url = 'https://ruesapi.rues.org.co/api/establecimientos?usuario=admgen&nit=' . $ide1 . '&dv=' . $ide2;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, null);
        curl_setopt($ch, CURLOPT_POST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);

        \logApi::general2($nameLog, $tide . '-' . $ide, '****** PETICION : ' . $url . ' *** RESPUESTA : ' . $result);
        \logApi::general2($nameLog, $tide . '-' . $ide, '');
        //
        if (!\funcionesGenerales::isJson($result)) {
            return array();
        }

        //
        $resultado = json_decode($result, true);
        if (!isset($resultado["error"]) || $resultado["error"] != null) {
            return array();
        }

        //
        if (!isset($resultado["establecimientos"]) || $resultado["establecimientos"] == null) {
            return array();
        }

        //
        $xcon = array();
        $salida = array();
        $ix = 0;
        foreach ($resultado["establecimientos"] as $est) {

            $ind = $est["codigo_camara"] . '-' . $est["matricula"];
            if (!isset($xcon[$ind])) {
                $xcon[$ind] = 1;

                $ix++;
                $salida[$ix] = $est;
                $salida[$ix]["ind"] = $ind;
                $salida[$ix]["nombre_municipio_comercial"] = retornarRegistroMysqliApi($dbx, "bas_municipios", "codigomunicipio='" . $est["municipio_comercial"] . "'", "ciudad");

                // Homologa organizacion juridica
                $xorg = '';
                switch ($est["codigo_organizacion_juridica"]) {
                    case "01":
                        $xorg = '01';
                        break;
                    case "02":
                        $xorg = '02';
                        break;
                    case "03":
                        $xorg = '03';
                        break;
                    case "04":
                        $xorg = '04';
                        break;
                    case "05":
                        $xorg = '05';
                        break;
                    case "06":
                        $xorg = '06';
                        break;
                    case "07":
                        $xorg = '07';
                        break;
                    case "08":
                        $xorg = '08';
                        break;
                    case "09":
                        $xorg = '09';
                        break;

                    case "10":
                        $xorg = '11';
                        break;
                    case "11":
                        $xorg = '17';
                        break;
                    case "12":
                        $xorg = '99';
                        break;
                    case "13":
                        $xorg = '15';
                        break;
                    default:
                        $xorg = '12';
                        break;
                }
                $salida[$ix]["codigo_organizacion_juridica"] = $xorg;

                // Homologo categoria
                $xcat = '';
                switch ($est["codigo_categoria_matricula"]) {
                    case "00":
                        $xcat = '';
                        break;
                    case "01":
                        $xcat = '1';
                        break;
                    case "02":
                        $xcat = '2';
                        break;
                    case "03":
                        $xcat = '3';
                        break;
                    case "04":
                        $xcat = '';
                        break;
                }
                $salida[$ix]["codigo_categoria_matricula"] = $xcat;

                // Ajusta fecha de renovacion
                if ($salida[$ix]["fecha_renovacion"] == '') {
                    if (isset($salida[$ix]["fecha_matricula"]) && $salida[$ix]["fecha_matricula"] != '') {
                        $salida[$ix]["fecha_renovacion"] = $salida[$ix]["fecha_matricula"];
                    }
                }
            }
        }
        $salida1 = \funcionesGenerales::ordenarMatriz($salida, "ind");

        //
        unset($resultado);

        //
        return $salida1;
    }

    public static function consultarSaldoAfiliado($dbx, $matricula)
    {

        //
        $formaCalculoAfiliacion = retornarClaveValor($dbx, '90.01.60');

        $salida = array(
            'valorultpagoafi' => 0,
            'fechaultpagoafi' => '',
            'pago' => 0,
            'cupo' => 0
        );

        //
        $_SESSION["generales"]["corterenovacion"] = retornarRegistroMysqliApi($dbx, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
        $_SESSION["generales"]["corterenovacionmesdia"] = substr($_SESSION["generales"]["corterenovacion"], 4, 4);

        //
        $arrSerAfil = retornarRegistrosMysqliApi($dbx, "mreg_servicios", "grupoventas='02'", "idservicio");
        $Servicios = "";
        $ServiciosAfiliacion = array();
        foreach ($arrSerAfil as $ServAfil) {
            if ($Servicios != '') {
                $Servicios .= ",";
            }
            $Servicios .= "'" . $ServAfil["idservicio"] . "'";
            $ServiciosAfiliacion[] = $ServAfil["idservicio"];
        }

        //
        $arrFecValAfi = retornarRegistroMysqliApi($dbx, 'mreg_est_recibos', "matricula='" . $matricula . "' and servicio in (" . $Servicios . ") and ctranulacion = '0' and (substring(numerorecibo,1,1)='R' or substring(numerorecibo,1,1)='S') order by fecoperacion desc limit 1");
        $salida["valorultpagoafi"] = $arrFecValAfi["valor"];
        $salida["fechaultpagoafi"] = $arrFecValAfi["fecoperacion"];
        unset($arrFecValAfi);

        //
        $detalle = array();
        $iDetalle = 0;
        if (date("md") <= $_SESSION["generales"]["corterenovacionmesdia"]) {
            $anox = date("Y") - 1;
            $feciniafi = (date("Y") - 1) . '0101';
        } else {
            $anox = date("Y");
            $feciniafi = date("Y") . '0101';
        }
        $inix = retornarRegistroMysqliApi($dbx, 'mreg_saldos_afiliados_sirp', "ano='" . $anox . "' and matricula='" . $matricula . "'");
        if ($inix && !empty($inix)) {
            $iDetalle++;
            $detalle[$iDetalle] = array(
                'tipo' => 'SaldoInicial-SIRP',
                'fecha' => $anox,
                'recibo' => '',
                'valor' => $inix["cupocargado"],
                'cupo' => $inix["cupocargado"] - $inix["cupoconsumido"]
            );
            $salida["cupo"] = $inix["cupocargado"] - $inix["cupoconsumido"];
        }

        //
        $arrRecs = retornarRegistrosMysqliApi($dbx, 'mreg_est_recibos', "(matricula='" . $matricula . "') and ctranulacion = '0' and left(numerorecibo,1) IN ('H','G','R','S') and fecoperacion >= '" . $feciniafi . "'", "fecoperacion");
        if ($arrRecs && !empty($arrRecs)) {
            foreach ($arrRecs as $rx) {
                if (in_array($rx["servicio"], $ServiciosAfiliacion)) {
                    // Si se cambia de año en el pago, se reinicia el histórico de pagos
                    if (substr($rx["fecoperacion"], 0, 4) != substr($feciniafi, 0, 4)) {
                        $iDetalle = 0;
                        $detalle = array();
                        $salida["cupo"] = 0;
                        $salida["pago"] = 0;
                    }

                    //
                    $iDetalle++;
                    $detalle[$iDetalle] = array(
                        'tipo' => 'PagoAfiliación',
                        'fecha' => $rx["fecoperacion"],
                        'recibo' => $rx["numerorecibo"],
                        'valor' => $rx["valor"],
                        'cupo' => 0
                    );
                    $salida["pago"] = $salida["pago"] + $rx["valor"];
                    if ($formaCalculoAfiliacion != '') {
                        if ($formaCalculoAfiliacion == 'RANGO_VAL_AFI') {
                            $arrRan = retornarRegistrosMysqliApi($dbx, 'mreg_rangos_cupo_afiliacion', "ano='" . date("Y") . "'", "orden");
                            foreach ($arrRan as $rx1) {
                                if ($rx1["minimo"] <= $salida["pago"] && $rx1["maximo"] >= $salida["pago"]) {
                                    $salida["cupo"] = $rx1["cupo"];
                                }
                            }
                            unset($arrRan);
                            unset($rx1);
                        } else {
                            $salida["cupo"] = round(doubleval($formaCalculoAfiliacion) * $salida["pago"], 0);
                        }
                    }
                    $detalle[$iDetalle]["cupo"] = $salida["cupo"];
                }
                if ($salida["cupo"] > 0) {
                    if ($rx["tipogasto"] == '1') {
                        if ($salida["cupo"] - $rx["valor"] >= 0) {
                            $salida["cupo"] = $salida["cupo"] - $rx["valor"];
                        } else {
                            $salida["cupo"] = 0;
                        }
                        $iDetalle++;
                        $detalle[$iDetalle] = array(
                            'tipo' => 'Consumo',
                            'fecha' => $rx["fecoperacion"],
                            'recibo' => $rx["numerorecibo"],
                            'valor' => $rx["valor"],
                            'cupo' => $salida["cupo"]
                        );
                    }
                    // }
                }
            }
        }
        return $salida;
    }

    public static function crearIndex($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777);
        }
        if (!file_exists($dir . '/index.html')) {
            $f = fopen($dir . '/index.html', "w");
            $txt = '	
		<!DOCTYPE HTML>
		<html>
		<head>
		<title>Directorio protegido</title>
		<meta name="keywords" content="" />
		<meta name="description" content="" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="language" content="es" />
		<meta http-equiv="cache-control" content="no-cache">
		</head>
		<body>
			<center>
				<h1>Este directorio no puede ser consultado en forma directa, se encuentra protegido</h1>
			</center>
		</body>
	</html>';
            fwrite($f, $txt);
            fclose($f);
        }
        return true;
    }

    /**
     * 
     * @param type $arr
     * @return type
     */
    public static function decodificarUtf8($arr)
    {
        $arrSal = array();
        foreach ($arr as $key => $valor) {
            if (is_array($valor)) {
                $arrSal[$key] = array();
                foreach ($valor as $key1 => $valor1) {
                    $arrSal[$key][$key1] = \funcionesGenerales::utf8_decode($valor1);
                }
            } else {
                $arrSal[$key] = \funcionesGenerales::utf8_decode($valor);
            }
        }
        return $arrSal;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $xml
     * @return int
     */
    public static function desserializarExpedienteMatricula($mysqli, $xml)
    {

        $retorno = array();
        $retorno["cc"] = '';
        $retorno["matricula"] = '';
        $retorno["proponente"] = '';
        $retorno["nombre"] = '';
        $retorno["nuevonombre"] = '';
        $retorno["nombrebase64"] = '';
        $retorno["ape1"] = '';
        $retorno["ape2"] = '';
        $retorno["nom1"] = '';
        $retorno["nom2"] = '';
        $retorno["sigla"] = '';
        $retorno["siglabase64"] = '';
        $retorno["complementorazonsocial"] = '';
        $retorno["tipoidentificacion"] = '';
        $retorno["identificacion"] = '';
        $retorno["sexo"] = '';
        $retorno["etnia"] = '';
        $retorno["emprendimientosocial"] = '';
        $retorno["ideext"] = '';
        $retorno["nrocontrolreactivacion"] = '';
        $retorno["idmunidoc"] = '';
        $retorno["fechanacimiento"] = '';
        $retorno["fecexpdoc"] = '';
        $retorno["paisexpdoc"] = '';
        $retorno["nit"] = '';
        $retorno["estadonit"] = '';
        $retorno["admondian"] = '';
        $retorno["prerut"] = '';
        $retorno["nacionalidad"] = '';

        // 2017-05-31
        $retorno["idetripaiori"] = ''; // Nuevo campo circular 002
        $retorno["paiori"] = ''; // Nuevo campo circular 002
        $retorno["idetriextep"] = ''; // Nuevo campo circular 002
        //

        $retorno["fechamatricula"] = '';
        $retorno["fecmatant"] = '';
        $retorno["fecharenovacion"] = '';
        $retorno["fechaconstitucion"] = '';
        $retorno["fechavencimiento"] = '';
        $retorno["fechacancelacion"] = '';
        $retorno["motivocancelacion"] = ''; // Nuevo campo circular 002    
        $retorno["fechadisolucion"] = '';
        $retorno["fechaliquidacion"] = '';
        $retorno["ultanoren"] = '';
        $retorno["estadomatricula"] = '';
        $retorno["estadoactiva"] = '';
        $retorno["estadopreoperativa"] = '';
        $retorno["estadoconcordato"] = '';
        $retorno["estadointervenida"] = '';
        $retorno["estadodisuelta"] = '';
        $retorno["estadoreestructuracion"] = '';
        $retorno["estadodatosmatricula"] = '';
        $retorno["certificardesde"] = '';
        $retorno["estadotipoliquidacion"] = ''; // Nuevo campo circular 002 
        $retorno["estadoproponente"] = '';
        $retorno["estadocapturado"] = '';
        $retorno["estadocapturadootros"] = '';
        $retorno["cantest"] = 0;
        $retorno["empresafamiliar"] = ''; // Nuevo campo circular 002  
        $retorno["procesosinnovacion"] = ''; // Nuevo campo circular 002  

        $retorno["ctrubi"] = "";
        $retorno["ctrfun"] = "";
        $retorno["art4"] = "";
        $retorno["art7"] = "";
        $retorno["art50"] = "";
        $retorno["benley1780"] = "";
        $retorno["ctrcancelacion1429"] = "";
        $retorno["cumplerequisitos1780"] = "";
        $retorno["renunciabeneficios1780"] = "";
        $retorno["cumplerequisitos1780primren"] = "";
        $retorno["ctrdepuracion1727"] = "";
        $retorno["ctrfechadepuracion1727"] = "";
        $retorno["ctrben658"] = "";

        $retorno["codigosbarras"] = '';
        $retorno["embargos"] = '';
        $retorno["embargostramite"] = '';
        $retorno["recursostramite"] = '';
        $retorno["tamanoempresa"] = '';
        $retorno["emprendedor28"] = '';
        $retorno["pemprendedor28"] = 0;
        $retorno["organizacion"] = '';
        $retorno["organizaciontexto"] = '';
        $retorno["categoria"] = '';
        $retorno["categoriatexto"] = '';
        $retorno["naturaleza"] = '';
        $retorno["imppredil"] = '';
        $retorno["impexp"] = '';
        $retorno["tipopropiedad"] = '';
        $retorno["tipolocal"] = '';
        $retorno["tipogruemp"] = ''; // Nuevo campo circular 002  
        $retorno["nombregruemp"] = ''; // Nuevo campo circular 002  
        //
        // Informacion de ESADL
        $retorno["vigcontrol"] = '';
        $retorno["fecperj"] = '';
        $retorno["idorigenperj"] = '';
        $retorno["numperj"] = '';
        $retorno["vigifecini"] = '';
        $retorno["vigifecfin"] = '';
        $retorno["clasegenesadl"] = '';
        $retorno["claseespesadl"] = '';
        $retorno["claseeconsoli"] = '';
        $retorno["condiespe2219"] = '';
        $retorno["ctrcodcoop"] = '';
        $retorno["ctrcodotras"] = '';
        $retorno["ctrderpub"] = ''; // Nuevo campo circular 002  
        $retorno["econmixta"] = '';

        $retorno["ctresacntasociados"] = ''; // Nuevo campo circular 002  
        $retorno["ctresacntmujeres"] = ''; // Nuevo campo circular 002  
        $retorno["ctresacnthombres"] = ''; // Nuevo campo circular 002  
        $retorno["ctresapertgremio"] = ''; // Nuevo campo circular 002  
        $retorno["ctresagremio"] = ''; // Nuevo campo circular 002  
        $retorno["ctresaacredita"] = ''; // Nuevo campo circular 002  
        $retorno["ctresaivc"] = ''; // Nuevo campo circular 002
        $retorno["ctresainfoivc"] = ''; // Nuevo campo circular 002
        $retorno["ctresaautregistro"] = ''; // Nuevo campo circular 002  
        $retorno["ctresaentautoriza"] = ''; // Nuevo campo circular 002  
        $retorno["ctresacodnat"] = ''; // Nuevo campo circular 002     
        $retorno["ctresadiscap"] = ''; // Nuevo campo circular 002     
        $retorno["ctresaetnia"] = ''; // Nuevo campo circular 002     
        $retorno["ctresacualetnia"] = ''; // Nuevo campo circular 002     
        $retorno["ctresadespvictreins"] = ''; // Nuevo campo circular 002     
        $retorno["ctresacualdespvictreins"] = ''; // Nuevo campo circular 002     
        $retorno["ctresaindgest"] = ''; // Nuevo campo circular 002     
        $retorno["ctresalgbti"] = ''; // Nuevo campo circular 002     
        // Datos de afiliacion
        $retorno["afiliado"] = '';
        $retorno["fechaafiliacion"] = '';
        $retorno["ultanorenafi"] = '';
        $retorno["fechaultpagoafi"] = '';
        $retorno["valorultpagoafi"] = '';
        $retorno["saldoafiliado"] = 0;
        $retorno["telaflia"] = '';
        $retorno["diraflia"] = '';
        $retorno["munaflia"] = '';
        $retorno["profaflia"] = '';
        $retorno["contaflia"] = '';
        $retorno["dircontaflia"] = '';
        $retorno["muncontaflia"] = '';
        $retorno["numactaaflia"] = '';
        $retorno["fecactaaflia"] = '';
        $retorno["numactaafliacan"] = '';
        $retorno["fecactaafliacan"] = '';
        $retorno["fecexafiliacion"] = '';
        $retorno["periodicoafiliados"] = array();

        // informacion de ubicacion comercial en el registro mercantil
        $retorno["lggr"] = '';
        $retorno["nombrecomercial"] = '';
        $retorno["dircom"] = '';
        $retorno["dircom_tipovia"] = '';
        $retorno["dircom_numvia"] = '';
        $retorno["dircom_apevia"] = '';
        $retorno["dircom_orivia"] = '';
        $retorno["dircom_numcruce"] = '';
        $retorno["dircom_apecruce"] = '';
        $retorno["dircom_oricruce"] = '';
        $retorno["dircom_numplaca"] = '';
        $retorno["dircom_complemento"] = '';
        $retorno["latitud"] = '';
        $retorno["longitud"] = '';
        $retorno["barriocom"] = '';
        $retorno["barriocomnombre"] = '';
        $retorno["muncom"] = '';
        $retorno["paicom"] = '';
        $retorno["muncomnombre"] = '';
        $retorno["telcom1"] = '';
        $retorno["telcom2"] = '';
        $retorno["celcom"] = '';
        $retorno["telcomant1"] = '';
        $retorno["telcomant2"] = '';
        $retorno["telcomant3"] = '';
        $retorno["faxcom"] = '';
        $retorno["aacom"] = '';
        $retorno["zonapostalcom"] = '';
        $retorno["emailcom"] = '';
        $retorno["emailcom2"] = '';
        $retorno["emailcom3"] = '';
        $retorno["emailcomant"] = '';
        $retorno["nombresegundocontacto"] = '';
        $retorno["cargosegundocontacto"] = '';
        $retorno["urlcom"] = '';
        $retorno["ctrmen"] = "";
        $retorno["codigopostalcom"] = ''; // Nuevo campo circular 002
        $retorno["codigozonacom"] = ''; // Nuevo campo circular 002 (R/U)
        //
        // informacion de ubicaci&oacute;n de notificacion
        $retorno["dirnot"] = '';
        $retorno["dirnot_tipovia"] = '';
        $retorno["dirnot_numvia"] = '';
        $retorno["dirnot_apevia"] = '';
        $retorno["dirnot_orivia"] = '';
        $retorno["dirnot_numcruce"] = '';
        $retorno["dirnot_apecruce"] = '';
        $retorno["dirnot_oricruce"] = '';
        $retorno["dirnot_numplaca"] = '';
        $retorno["dirnot_complemento"] = '';
        $retorno["barrionot"] = '';
        $retorno["barrionotnombre"] = '';
        $retorno["munnot"] = '';
        $retorno["painot"] = '';
        $retorno["munnotnombre"] = '';
        $retorno["telnot"] = '';
        $retorno["telnot2"] = '';
        $retorno["celnot"] = "";
        $retorno["telnotant1"] = '';
        $retorno["telnotant2"] = '';
        $retorno["telnotant3"] = '';
        $retorno["faxnot"] = '';
        $retorno["aanot"] = '';
        $retorno["zonapostalnot"] = '';
        $retorno["emailnot"] = '';
        $retorno["emailnotant"] = '';
        $retorno["urlnot"] = '';
        $retorno["numpredial"] = "";
        $retorno["latitudgrados"] = "";
        $retorno["latitudminutos"] = "";
        $retorno["latitudsegundos"] = "";
        $retorno["latitudorientacion"] = "";
        $retorno["longitudgrados"] = "";
        $retorno["longitudminutos"] = "";
        $retorno["longitudsegundos"] = "";
        $retorno["longitudorientacion"] = "";
        $retorno["ctrmennot"] = "";
        $retorno["codigopostalnot"] = ''; // Nuevo campo circular 002
        $retorno["codigozonanot"] = ''; // Nuevo campo circular 002 (R/U)
        $retorno["tiposedeadm"] = ''; // Nuevo campo circular 002
        // Datos de correspondencia
        $retorno["dircor"] = '';
        $retorno["telcor"] = '';
        $retorno["telcor2"] = '';
        $retorno["muncor"] = '';

        // informacion ed actividad economica
        $retorno["ciius"] = array();
        $retorno["ciius"][1] = '';
        $retorno["ciius"][2] = '';
        $retorno["ciius"][3] = '';
        $retorno["ciius"][4] = '';
        $retorno["ciius"][5] = '';
        $retorno["shd"] = array();
        $retorno["shd"][1] = '';
        $retorno["shd"][2] = '';
        $retorno["shd"][3] = '';
        $retorno["shd"][4] = '';
        $retorno["shd"][5] = '';

        $retorno["versionciiu"] = '';
        $retorno["desactiv"] = '';
        $retorno["feciniact1"] = ''; // Nuevo campo circular 002
        $retorno["feciniact2"] = ''; // Nuevo campo circular 002
        $retorno["ingesperados"] = '';

        //
        $retorno["codrespotri"] = array();

        //
        $retorno["codaduaneros"] = ''; // Nuevo campo circular 002
        $retorno["gruponiif"] = ''; // Nuevo campo circular 002
        $retorno["niifconciliacion"] = ''; // Nuevo campo circular 002
        $retorno["aportantesegsocial"] = ''; // Nuevo campo circular 002
        $retorno["tipoaportantesegsocial"] = ''; // Nuevo campo circular 002
        // informacion de porcentajes de capital
        $retorno["cap_porcnaltot"] = 0;
        $retorno["cap_porcnalpri"] = 0;
        $retorno["cap_porcnalpub"] = 0;
        $retorno["cap_porcexttot"] = 0;
        $retorno["cap_porcextpri"] = 0;
        $retorno["cap_porcextpub"] = 0;

        $retorno["cap_apolab"] = 0;
        $retorno["cap_apolabadi"] = 0;
        $retorno["cap_apoact"] = 0;
        $retorno["cap_apodin"] = 0;
        $retorno["cap_apotra"] = 0;
        $retorno["cap_apotot"] = 0;

        $retorno["capaut"] = 0;
        $retorno["capsus"] = 0;
        $retorno["cappag"] = 0;
        $retorno["capsoc"] = 0;

        $retorno["anodatoscap"] = '';
        $retorno["fechadatoscap"] = '';

        $retorno["cantidadmujeres"] = 0;
        $retorno["cantidadmujerescargosdirectivos"] = 0;
        $retorno["cantidadcargosdirectivos"] = 0;
        $retorno["participacionmujeres"] = 0;
        $retorno["participacionetnia"] = 0;
        $retorno["ciiutamanoempresarial"] = '';
        $retorno["ingresostamanoempresarial"] = 0;
        $retorno["anodatostamanoempresarial"] = '';
        $retorno["fechadatostamanoempresarial"] = '';

        // informaci&oacute;n de Establecimientos de comercio asociados
        $retorno["cntestab01"] = 0;
        $retorno["cntestab02"] = 0;
        $retorno["cntestab03"] = 0;
        $retorno["cntestab04"] = 0;
        $retorno["cntestab05"] = 0;
        $retorno["cntestab06"] = 0;
        $retorno["cntestab07"] = 0;
        $retorno["cntestab08"] = 0;
        $retorno["cntestab09"] = 0;
        $retorno["cntestab10"] = 0;
        $retorno["cntestab11"] = 0;

        // informacion de referencias comerciales y bancarias
        $retorno["refcrenom1"] = '';
        $retorno["refcreofi1"] = '';
        $retorno["refcretel1"] = '';
        $retorno["refcrenom2"] = '';
        $retorno["refcreofi2"] = '';
        $retorno["refcretel2"] = '';
        $retorno["refcomnom1"] = '';
        $retorno["refcomdir1"] = '';
        $retorno["refcomtel1"] = '';
        $retorno["refcomnom2"] = '';
        $retorno["refcomdir2"] = '';
        $retorno["refcomtel2"] = '';

        // informacion financiera
        $retorno["anodatos"] = '';
        $retorno["fechadatos"] = '';
        $retorno["personal"] = 0;
        $retorno["personaltemp"] = 0;
        $retorno["actvin"] = 0;
        $retorno["actcte"] = 0;
        $retorno["actnocte"] = 0;
        $retorno["actfij"] = 0;
        $retorno["fijnet"] = 0;
        $retorno["actotr"] = 0;
        $retorno["actval"] = 0;
        $retorno["acttot"] = 0;
        $retorno["actsinaju"] = 0;
        $retorno["invent"] = 0;
        $retorno["pascte"] = 0;
        $retorno["paslar"] = 0;
        $retorno["pastot"] = 0;
        $retorno["pattot"] = 0;
        $retorno["paspat"] = 0;
        $retorno["balsoc"] = 0;
        $retorno["ingope"] = 0;
        $retorno["ingnoope"] = 0;
        $retorno["gtoven"] = 0;
        $retorno["gtoadm"] = 0;
        $retorno["gasope"] = 0;
        $retorno["gasnoope"] = 0;
        $retorno["cosven"] = 0;
        $retorno["gasint"] = 0;
        $retorno["gasimp"] = 0;
        $retorno["depamo"] = 0;
        $retorno["utiope"] = 0;
        $retorno["utinet"] = 0;

        $retorno["apolab"] = 0;
        $retorno["apolabadi"] = 0;
        $retorno["apoact"] = 0;
        $retorno["apodin"] = 0;
        $retorno["apotot"] = 0;
        $retorno["apotra"] = 0;
        $retorno["patrimonio"] = 0;

        // informacion de patrimonios esadl
        $retorno["anodatospatrimonio"] = '';
        $retorno["fechadatospatrimonio"] = '';
        $retorno["patrimonioesadl"] = 0;

        //
        $retorno["matriculaanterior"] = "";
        $retorno["fecrenant"] = "";
        $retorno["fecmatant"] = "";
        $retorno["camant"] = "";
        $retorno["munant"] = "";
        $retorno["ultanorenant"] = "";
        $retorno["matant"] = "";
        $retorno["benart7ant"] = "";
        $retorno["benley1780ant"] = "";
        $retorno["ctrbic"] = '';

        // Campos adicionados en mayo 20 de 2011         
        $retorno["ivcenvio"] = '';
        $retorno["ivcsuelos"] = '';
        $retorno["ivcarea"] = 0;
        $retorno["ivcver"] = '';
        $retorno["ivccretip"] = '';
        $retorno["ivcali"] = '';
        $retorno["ivcqui"] = '';
        $retorno["ivcriesgo"] = '';

        // Representacion legal y administracion
        $retorno["idtipoidentificacionreplegal"] = '';
        $retorno["identificacionreplegal"] = '';
        $retorno["nombrereplegal"] = '';
        $retorno["idtipoidentificacionadministrador"] = '';
        $retorno["identificacionadministrador"] = '';
        $retorno["nombreadministrador"] = '';

        // Propietarios, establecimientos y sucursales y agencias
        $retorno["cpcodcam"] = '';
        $retorno["cpnummat"] = '';
        $retorno["cprazsoc"] = '';
        $retorno["cpnumnit"] = '';
        $retorno["cpdircom"] = '';
        $retorno["cpdirnot"] = '';
        $retorno["cpnumtel"] = '';
        $retorno["cpnumfax"] = '';
        $retorno["cpcodmun"] = '';
        $retorno["cpmunnot"] = '';
        $retorno["cpafili"] = '';
        $retorno["cpsaldo"] = 0;
        $retorno["cpurenafi"] = '';
        $retorno["cptirepleg"] = '';
        $retorno["cpirepleg"] = '';
        $retorno["cpnrepleg"] = '';
        $retorno["cptelrepleg"] = '';
        $retorno["cpemailrepleg"] = '';

        // Campos adicionados en junio 23 de 2014
        $retorno["codigoscae"] = array();
        $arrY = retornarRegistrosMysqliApi($mysqli, 'mreg_anexoscae', "1=1", "codigocae");
        foreach ($arrY as $y) {
            $retorno["codigoscae"][$y["codigocae"]] = '';
        }

        //
        $retorno["informacionadicional"] = array();
        $arrY = retornarRegistrosMysqliApi($mysqli, 'mreg_campos_adicionales_camara', "1=1", "orden");
        foreach ($arrY as $y) {
            $retorno["informacionadicional"][$y["codigoadicional"]] = '';
        }

        //
        $retorno["bienes"] = array();
        $retorno["replegal"] = array();
        $retorno["vinculos"] = array();
        $retorno["vincuprop"] = array();
        $retorno["propietarios"] = array();
        $retorno["propietariosh"] = array();
        $retorno["establecimientos"] = array();
        $retorno["sucursalesagencias"] = array();
        $retorno["lcodigosbarras"] = array();
        $retorno["camposcae"] = array();
        $retorno["inscripciones"] = array();
        $retorno["nomant"] = array();
        $retorno["capitales"] = array();
        $retorno["patrimoniosesadl"] = array();
        $retorno["hf"] = array();
        $retorno["f"] = array();
        $retorno["crt"] = array();
        $retorno["crtsii"] = array();
        $retorno["ctrembargos"] = array();
        $retorno["anexoresponsabilidades"] = '';
        $retorno["empsoccategorias"] = '';
        $retorno["empsocbeneficiarios"] = '';
        $retorno["empsoccategorias_otros"] = '';
        $retorno["empsocbeneficiarios_otros"] = '';

        //
        if (trim($xml) != '') {
            $xml = str_replace("&", "[2]", $xml);
            //$xml = str_replace("[2]amp;", "&", $xml);
            //$xml = str_replace("&amp;", "&", $xml);
            $dom = new DomDocument('1.0', 'utf-8');
            $result = $dom->loadXML($xml);
            if ($result === false) {
                $_SESSION["generales"]["txtemergente"] = 'Error recuperando xml!!! ( ' . $xml . ')';
                return 0;
            }
            $iexp = 0;
            $reg1 = $dom->getElementsByTagName("expediente");
            $retorno = array();
            foreach ($reg1 as $reg) {
                $iexp++;
                if ($iexp == 1) {

                    // ***************************************************************************** //
                    // Datos de Identificacon del expediente
                    // ***************************************************************************** //
                    if (isset($reg->getElementsByTagName("cc")->item(0)->textContent)) {
                        $retorno["cc"] = $reg->getElementsByTagName("cc")->item(0)->textContent;
                    } else {
                        $retorno["cc"] = CODIGO_EMPRESA;
                    }
                    $retorno["matricula"] = ltrim($reg->getElementsByTagName("matricula")->item(0)->textContent, "0");
                    $retorno["proponente"] = ltrim($reg->getElementsByTagName("proponente")->item(0)->textContent, "0");
                    $retorno["nombre"] = trim(\funcionesGenerales::restaurarEspeciales($reg->getElementsByTagName("nombre")->item(0)->textContent));
                    (isset($reg->getElementsByTagName("nuevonombre")->item(0)->textContent)) ? $retorno["nuevonombre"] = \funcionesGenerales::restaurarEspecialesRazonSocial($reg->getElementsByTagName("nuevonombre")->item(0)->textContent) : $retorno["nuevonombre"] = '';
                    (isset($reg->getElementsByTagName("nombrebase64")->item(0)->textContent)) ? $retorno["nombrebase64"] = $reg->getElementsByTagName("nombrebase64")->item(0)->textContent : $retorno["nombrebase64"] = '';
                    $retorno["ape1"] = trim(\funcionesGenerales::restaurarEspeciales($reg->getElementsByTagName("ape1")->item(0)->textContent));
                    $retorno["ape2"] = trim(\funcionesGenerales::restaurarEspeciales($reg->getElementsByTagName("ape2")->item(0)->textContent));
                    $retorno["nom1"] = trim(\funcionesGenerales::restaurarEspeciales($reg->getElementsByTagName("nom1")->item(0)->textContent));
                    $retorno["nom2"] = trim(\funcionesGenerales::restaurarEspeciales($reg->getElementsByTagName("nom2")->item(0)->textContent));
                    $retorno["sigla"] = trim(\funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("sigla")->item(0)->textContent)));
                    (isset($reg->getElementsByTagName("siglabase64")->item(0)->textContent)) ? $retorno["siglabase64"] = $reg->getElementsByTagName("siglabase64")->item(0)->textContent : $retorno["siglabase64"] = '';
                    if (isset($reg->getElementsByTagName("complementorazonsocial")->item(0)->textContent)) {
                        $retorno["complementorazonsocial"] = trim(\funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("complementorazonsocial")->item(0)->textContent)));
                    }
                    $retorno["tipoidentificacion"] = $reg->getElementsByTagName("tipoidentificacion")->item(0)->textContent;
                    $retorno["identificacion"] = ltrim($reg->getElementsByTagName("identificacion")->item(0)->textContent, '0');
                    $retorno["sexo"] = $reg->getElementsByTagName("sexo")->item(0)->textContent;
                    if (isset($reg->getElementsByTagName("etnia")->item(0)->textContent)) {
                        $retorno["etnia"] = $reg->getElementsByTagName("etnia")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("emprendimientosocial")->item(0)->textContent)) {
                        $retorno["emprendimientosocial"] = $reg->getElementsByTagName("emprendimientosocial")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("empsoccategorias")->item(0)->textContent)) {
                        $retorno["empsoccategorias"] = $reg->getElementsByTagName("empsoccategorias")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("empsoccategorias_otros")->item(0)->textContent)) {
                        $retorno["empsoccategorias_otros"] = $reg->getElementsByTagName("empsoccategorias_otros")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("empsocbeneficiarios")->item(0)->textContent)) {
                        $retorno["empsocbeneficiarios"] = $reg->getElementsByTagName("empsocbeneficiarios")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("empsocbeneficiarios_otros")->item(0)->textContent)) {
                        $retorno["empsocbeneficiarios_otros"] = $reg->getElementsByTagName("empsocbeneficiarios_otros")->item(0)->textContent;
                    }
                    $retorno["ideext"] = ltrim($reg->getElementsByTagName("ideext")->item(0)->textContent, '0');
                    $retorno["idmunidoc"] = $reg->getElementsByTagName("idmunidoc")->item(0)->textContent;
                    if (isset($reg->getElementsByTagName("fechanacimiento")->item(0)->textContent)) {
                        $retorno["fechanacimiento"] = $reg->getElementsByTagName("fechanacimiento")->item(0)->textContent;
                    }
                    $retorno["fecexpdoc"] = $reg->getElementsByTagName("fecexpdoc")->item(0)->textContent;
                    $retorno["paisexpdoc"] = $reg->getElementsByTagName("paisexpdoc")->item(0)->textContent;
                    $retorno["nit"] = ltrim($reg->getElementsByTagName("nit")->item(0)->textContent, '0');
                    (isset($reg->getElementsByTagName("prerut")->item(0)->textContent)) ? $retorno["prerut"] = ltrim($reg->getElementsByTagName("prerut")->item(0)->textContent, '0') : $retorno["prerut"] = '';
                    $retorno["admondian"] = $reg->getElementsByTagName("admondian")->item(0)->textContent;
                    (isset($reg->getElementsByTagName("estadonit")->item(0)->textContent)) ? $retorno["estadonit"] = trim($reg->getElementsByTagName("estadonit")->item(0)->textContent) : $retorno["estadonit"] = '';
                    $retorno["nacionalidad"] = trim($reg->getElementsByTagName("nacionalidad")->item(0)->textContent);

                    if (isset($reg->getElementsByTagName("ctrbic")->item(0)->textContent)) {
                        $retorno["ctrbic"] = trim(\funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("ctrbic")->item(0)->textContent)));
                    } else {
                        if (isset($reg->getElementsByTagName("esbic")->item(0)->textContent)) {
                            $retorno["ctrbic"] = trim(\funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("esbic")->item(0)->textContent)));
                        }
                    }

                    (isset($reg->getElementsByTagName("idetripaiori")->item(0)->textContent)) ? $retorno["idetripaiori"] = trim($reg->getElementsByTagName("idetripaiori")->item(0)->textContent) : $retorno["idetripaiori"] = '';
                    (isset($reg->getElementsByTagName("paiori")->item(0)->textContent)) ? $retorno["paiori"] = trim($reg->getElementsByTagName("paiori")->item(0)->textContent) : $retorno["paiori"] = '';
                    (isset($reg->getElementsByTagName("idetriextep")->item(0)->textContent)) ? $retorno["idetriextep"] = trim($reg->getElementsByTagName("idetriextep")->item(0)->textContent) : $retorno["idetriextep"] = '';

                    (isset($reg->getElementsByTagName("nrocontrolreactivacion")->item(0)->textContent)) ? $retorno["nrocontrolreactivacion"] = ltrim($reg->getElementsByTagName("nrocontrolreactivacion")->item(0)->textContent, '0') : $retorno["nrocontrolreactivacion"] = '';
                    (isset($reg->getElementsByTagName("expedienteinactivo")->item(0)->textContent)) ? $retorno["expedienteinactivo"] = trim($reg->getElementsByTagName("expedienteinactivo")->item(0)->textContent) : $retorno["expedienteinactivo"] = '0';
                    $retorno["fechamatricula"] = $reg->getElementsByTagName("fechamatricula")->item(0)->textContent;
                    $retorno["fecharenovacion"] = $reg->getElementsByTagName("fecharenovacion")->item(0)->textContent;
                    $retorno["ultanoren"] = $reg->getElementsByTagName("ultanoren")->item(0)->textContent;
                    $retorno["estadomatricula"] = $reg->getElementsByTagName("estadomatricula")->item(0)->textContent;
                    (isset($reg->getElementsByTagName("fechaconstitucion")->item(0)->textContent)) ? $retorno["fechaconstitucion"] = $reg->getElementsByTagName("fechaconstitucion")->item(0)->textContent : $retorno["fechaconstitucion"] = '';
                    (isset($reg->getElementsByTagName("fechavencimiento")->item(0)->textContent)) ? $retorno["fechavencimiento"] = $reg->getElementsByTagName("fechavencimiento")->item(0)->textContent : $retorno["fechavencimiento"] = '';
                    (isset($reg->getElementsByTagName("fechacancelacion")->item(0)->textContent)) ? $retorno["fechacancelacion"] = $reg->getElementsByTagName("fechacancelacion")->item(0)->textContent : $retorno["fechacancelacion"] = '';

                    (isset($reg->getElementsByTagName("motivocancelacion")->item(0)->textContent)) ? $retorno["motivocancelacion"] = trim($reg->getElementsByTagName("motivocancelacion")->item(0)->textContent) : $retorno["motivocancelacion"] = '';
                    (isset($reg->getElementsByTagName("estadotipoliquidacion")->item(0)->textContent)) ? $retorno["estadotipoliquidacion"] = trim($reg->getElementsByTagName("estadotipoliquidacion")->item(0)->textContent) : $retorno["estadotipoliquidacion"] = '';

                    (isset($reg->getElementsByTagName("fechadisolucion")->item(0)->textContent)) ? $retorno["fechadisolucion"] = $reg->getElementsByTagName("fechadisolucion")->item(0)->textContent : $retorno["fechadisolucion"] = '';
                    (isset($reg->getElementsByTagName("fechaliquidacion")->item(0)->textContent)) ? $retorno["fechaliquidacion"] = $reg->getElementsByTagName("fechaliquidacion")->item(0)->textContent : $retorno["fechaliquidacion"] = '';

                    (isset($reg->getElementsByTagName("estadoproponente")->item(0)->textContent)) ? $retorno["estadoproponente"] = $reg->getElementsByTagName("estadoproponente")->item(0)->textContent : $retorno["estadoproponente"] = '';
                    (isset($reg->getElementsByTagName("estadodatosmatricula")->item(0)->textContent)) ? $retorno["estadodatosmatricula"] = $reg->getElementsByTagName("ctrcertificardesde")->item(0)->textContent : $retorno["certificardesde"] = '';
                    (isset($reg->getElementsByTagName("ctrcertificardesde")->item(0)->textContent)) ? $retorno["certificardesde"] = $reg->getElementsByTagName("estadodatosmatricula")->item(0)->textContent : $retorno["estadodatosmatricula"] = '';
                    (isset($reg->getElementsByTagName("estadoactiva")->item(0)->textContent)) ? $retorno["estadoactiva"] = $reg->getElementsByTagName("estadoactiva")->item(0)->textContent : $retorno["estadoactiva"] = '';
                    (isset($reg->getElementsByTagName("estadopreoperativa")->item(0)->textContent)) ? $retorno["estadopreoperativa"] = $reg->getElementsByTagName("estadopreoperativa")->item(0)->textContent : $retorno["estadopreoperativa"] = '';
                    (isset($reg->getElementsByTagName("estadoconcordato")->item(0)->textContent)) ? $retorno["estadoconcordato"] = $reg->getElementsByTagName("estadoconcordato")->item(0)->textContent : $retorno["estadoconcordato"] = '';
                    (isset($reg->getElementsByTagName("estadointervenida")->item(0)->textContent)) ? $retorno["estadointervenida"] = $reg->getElementsByTagName("estadointervenida")->item(0)->textContent : $retorno["estadointervenida"] = '';
                    (isset($reg->getElementsByTagName("estadodisuelta")->item(0)->textContent)) ? $retorno["estadodisuelta"] = $reg->getElementsByTagName("estadodisuelta")->item(0)->textContent : $retorno["estadodisuelta"] = '';
                    (isset($reg->getElementsByTagName("estadoreestructuracion")->item(0)->textContent)) ? $retorno["estadoreestructuracion"] = $reg->getElementsByTagName("estadoreestructuracion")->item(0)->textContent : $retorno["estadoreestructuracion"] = '';
                    (isset($reg->getElementsByTagName("estadocapturado")->item(0)->textContent)) ? $retorno["estadocapturado"] = $reg->getElementsByTagName("estadocapturado")->item(0)->textContent : $retorno["estadocapturado"] = '';
                    (isset($reg->getElementsByTagName("estadocapturadootros")->item(0)->textContent)) ? $retorno["estadocapturadootros"] = $reg->getElementsByTagName("estadocapturadootros")->item(0)->textContent : $retorno["estadocapturadootros"] = '';
                    (isset($reg->getElementsByTagName("cantest")->item(0)->textContent)) ? $retorno["cantest"] = intval($reg->getElementsByTagName("cantest")->item(0)->textContent) : $retorno["cantest"] = 0;

                    (isset($reg->getElementsByTagName("tamanoempresa")->item(0)->textContent)) ? $retorno["tamanoempresa"] = $reg->getElementsByTagName("tamanoempresa")->item(0)->textContent : $retorno["tamanoempresa"] = '';
                    (isset($reg->getElementsByTagName("emprendedor28")->item(0)->textContent)) ? $retorno["emprendedor28"] = $reg->getElementsByTagName("emprendedor28")->item(0)->textContent : $retorno["emprendedor28"] = '';
                    (isset($reg->getElementsByTagName("pemprendedor28")->item(0)->textContent)) ? $retorno["pemprendedor28"] = $reg->getElementsByTagName("pemprendedor28")->item(0)->textContent : $retorno["pemprendedor28"] = 0;

                    if (isset($reg->getElementsByTagName("vigcontrol")->item(0)->textContent)) {
                        $retorno["vigcontrol"] = $reg->getElementsByTagName("vigcontrol")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("fecperj")->item(0)->textContent)) {
                        $retorno["fecperj"] = $reg->getElementsByTagName("fecperj")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("idorigenperj")->item(0)->textContent)) {
                        $retorno["idorigenperj"] = $reg->getElementsByTagName("idorigenperj")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("numperj")->item(0)->textContent)) {
                        $retorno["numperj"] = $reg->getElementsByTagName("numperj")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("vigifecini")->item(0)->textContent)) {
                        $retorno["vigifecini"] = $reg->getElementsByTagName("vigifecini")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("vigifecfin")->item(0)->textContent)) {
                        $retorno["vigifecfin"] = $reg->getElementsByTagName("vigifecfin")->item(0)->textContent;
                    }

                    (isset($reg->getElementsByTagName("clasegenesadl")->item(0)->textContent)) ? $retorno["clasegenesadl"] = $reg->getElementsByTagName("clasegenesadl")->item(0)->textContent : $retorno["clasegenesadl"] = '';
                    (isset($reg->getElementsByTagName("claseespesadl")->item(0)->textContent)) ? $retorno["claseespesadl"] = $reg->getElementsByTagName("claseespesadl")->item(0)->textContent : $retorno["claseespesadl"] = '';
                    (isset($reg->getElementsByTagName("claseeconsoli")->item(0)->textContent)) ? $retorno["claseeconsoli"] = $reg->getElementsByTagName("claseeconsoli")->item(0)->textContent : $retorno["claseeconsoli"] = '';
                    (isset($reg->getElementsByTagName("condiespe2219")->item(0)->textContent)) ? $retorno["condiespe2219"] = $reg->getElementsByTagName("condiespe2219")->item(0)->textContent : $retorno["condiespe2219"] = '';

                    (isset($reg->getElementsByTagName("ctrcodcoop")->item(0)->textContent)) ? $retorno["ctrcodcoop"] = $reg->getElementsByTagName("ctrcodcoop")->item(0)->textContent : $retorno["ctrcodcoop"] = '';
                    (isset($reg->getElementsByTagName("ctrcodotras")->item(0)->textContent)) ? $retorno["ctrcodotras"] = $reg->getElementsByTagName("ctrcodotras")->item(0)->textContent : $retorno["ctrcodotras"] = '';
                    (isset($reg->getElementsByTagName("ctrderpub")->item(0)->textContent)) ? $retorno["ctrderpub"] = $reg->getElementsByTagName("ctrderpub")->item(0)->textContent : $retorno["ctrderpub"] = '';
                    (isset($reg->getElementsByTagName("econmixta")->item(0)->textContent)) ? $retorno["econmixta"] = $reg->getElementsByTagName("econmixta")->item(0)->textContent : $retorno["econmixta"] = '';

                    (isset($reg->getElementsByTagName("ctresacntasociados")->item(0)->textContent)) ? $retorno["ctresacntasociados"] = $reg->getElementsByTagName("ctresacntasociados")->item(0)->textContent : $retorno["ctresacntasociados"] = 0;
                    (isset($reg->getElementsByTagName("ctresacntmujeres")->item(0)->textContent)) ? $retorno["ctresacntmujeres"] = $reg->getElementsByTagName("ctresacntmujeres")->item(0)->textContent : $retorno["ctresacntmujeres"] = 0;
                    (isset($reg->getElementsByTagName("ctresacnthombres")->item(0)->textContent)) ? $retorno["ctresacnthombres"] = $reg->getElementsByTagName("ctresacnthombres")->item(0)->textContent : $retorno["ctresacnthombres"] = 0;

                    (isset($reg->getElementsByTagName("ctresapertgremio")->item(0)->textContent)) ? $retorno["ctresapertgremio"] = $reg->getElementsByTagName("ctresapertgremio")->item(0)->textContent : $retorno["ctresapertgremio"] = '';
                    (isset($reg->getElementsByTagName("ctresagremio")->item(0)->textContent)) ? $retorno["ctresagremio"] = $reg->getElementsByTagName("ctresagremio")->item(0)->textContent : $retorno["ctresagremio"] = '';
                    (isset($reg->getElementsByTagName("ctresaacredita")->item(0)->textContent)) ? $retorno["ctresaacredita"] = $reg->getElementsByTagName("ctresaacredita")->item(0)->textContent : $retorno["ctresaacredita"] = '';
                    (isset($reg->getElementsByTagName("ctresaivc")->item(0)->textContent)) ? $retorno["ctresaivc"] = $reg->getElementsByTagName("ctresaivc")->item(0)->textContent : $retorno["ctresaivc"] = '';
                    (isset($reg->getElementsByTagName("ctresainfoivc")->item(0)->textContent)) ? $retorno["ctresainfoivc"] = $reg->getElementsByTagName("ctresainfoivc")->item(0)->textContent : $retorno["ctresainfoivc"] = '';
                    (isset($reg->getElementsByTagName("ctresaautregistro")->item(0)->textContent)) ? $retorno["ctresaautregistro"] = $reg->getElementsByTagName("ctresaautregistro")->item(0)->textContent : $retorno["ctresaautregistro"] = '';
                    (isset($reg->getElementsByTagName("ctresaentautoriza")->item(0)->textContent)) ? $retorno["ctresaentautoriza"] = $reg->getElementsByTagName("ctresaentautoriza")->item(0)->textContent : $retorno["ctresaentautoriza"] = '';
                    (isset($reg->getElementsByTagName("ctresacodnat")->item(0)->textContent)) ? $retorno["ctresacodnat"] = $reg->getElementsByTagName("ctresacodnat")->item(0)->textContent : $retorno["ctresacodnat"] = '';

                    (isset($reg->getElementsByTagName("ctresadiscap")->item(0)->textContent)) ? $retorno["ctresadiscap"] = $reg->getElementsByTagName("ctresadiscap")->item(0)->textContent : $retorno["ctresadiscap"] = '';
                    (isset($reg->getElementsByTagName("ctresaetnia")->item(0)->textContent)) ? $retorno["ctresaetnia"] = $reg->getElementsByTagName("ctresaetnia")->item(0)->textContent : $retorno["ctresaetnia"] = '';
                    (isset($reg->getElementsByTagName("ctresacualetnia")->item(0)->textContent)) ? $retorno["ctresacualetnia"] = $reg->getElementsByTagName("ctresacualetnia")->item(0)->textContent : $retorno["ctresacualetnia"] = '';

                    (isset($reg->getElementsByTagName("ctresadespvictreins")->item(0)->textContent)) ? $retorno["ctresadespvictreins"] = $reg->getElementsByTagName("ctresadespvictreins")->item(0)->textContent : $retorno["ctresadespvictreins"] = '';
                    (isset($reg->getElementsByTagName("ctresacualdespvictreins")->item(0)->textContent)) ? $retorno["ctresacualdespvictreins"] = $reg->getElementsByTagName("ctresacualdespvictreins")->item(0)->textContent : $retorno["ctresacualdespvictreins"] = '';
                    (isset($reg->getElementsByTagName("ctresaindgest")->item(0)->textContent)) ? $retorno["ctresaindgest"] = $reg->getElementsByTagName("ctresaindgest")->item(0)->textContent : $retorno["ctresaindgest"] = '';
                    (isset($reg->getElementsByTagName("ctresalgbti")->item(0)->textContent)) ? $retorno["ctresalgbti"] = $reg->getElementsByTagName("ctresalgbti")->item(0)->textContent : $retorno["ctresalgbti"] = '';

                    //
                    (isset($reg->getElementsByTagName("imppredil")->item(0)->textContent)) ? $retorno["imppredil"] = $reg->getElementsByTagName("imppredil")->item(0)->textContent : $retorno["imppredil"] = 'S';
                    (isset($reg->getElementsByTagName("codigosbarras")->item(0)->textContent)) ? $retorno["codigosbarras"] = ltrim($reg->getElementsByTagName("codigosbarras")->item(0)->textContent, "0") : $retorno["codigosbarras"] = 0;
                    (isset($reg->getElementsByTagName("embargos")->item(0)->textContent)) ? $retorno["embargos"] = trim($reg->getElementsByTagName("embargos")->item(0)->textContent) : $retorno["embargos"] = 0;
                    (isset($reg->getElementsByTagName("embargostramite")->item(0)->textContent)) ? $retorno["embargostramite"] = ltrim($reg->getElementsByTagName("embargostramite")->item(0)->textContent, "0") : $retorno["embargostramite"] = 0;
                    (isset($reg->getElementsByTagName("recursostramite")->item(0)->textContent)) ? $retorno["recursostramite"] = ltrim($reg->getElementsByTagName("recursostramite")->item(0)->textContent, "0") : $retorno["recursostramite"] = 0;

                    $retorno["organizacion"] = $reg->getElementsByTagName("organizacion")->item(0)->textContent;
                    $retorno["organizaciontexto"] = retornarRegistroMysqliApi($mysqli, 'bas_organizacionjuridica', "id='" . $reg->getElementsByTagName("organizacion")->item(0)->textContent . "'", "descripcion");
                    $retorno["categoria"] = $reg->getElementsByTagName("categoria")->item(0)->textContent;
                    $retorno["categoriatexto"] = retornarRegistroMysqliApi($mysqli, 'bas_categorias', "id='" . $reg->getElementsByTagName("categoria")->item(0)->textContent . "'", "descripcion");

                    if (trim($retorno["nombre"]) == '') {
                        if ($retorno["organizacion"] == '01') {
                            $retorno["nombre"] = trim($retorno["ape1"]);
                            if (trim($retorno["ape2"]) != '') {
                                $retorno["nombre"] .= ' ' . trim($retorno["ape2"]);
                            }
                            if (trim($retorno["nom1"]) != '') {
                                $retorno["nombre"] .= ' ' . trim($retorno["nom1"]);
                            }
                            if (trim($retorno["nom2"]) != '') {
                                $retorno["nombre"] .= ' ' . trim($retorno["nom2"]);
                            }
                        }
                    }

                    if (isset($reg->getElementsByTagName("naturaleza")->item(0)->textContent)) {
                        $retorno["naturaleza"] = $reg->getElementsByTagName("naturaleza")->item(0)->textContent;
                    } else {
                        $retorno["naturaleza"] = '0';
                    }
                    if ($retorno["organizacion"] == '01') {
                        if (trim($retorno["paisexpdoc"]) == '') {
                            $retorno["paisexpdoc"] = '169';
                        }
                        if (trim($retorno["nacionalidad"]) == '') {
                            $retorno["nacionalidad"] = 'COLOMBIANO/A';
                        }
                    }
                    if (($retorno["imppredil"] == '0') || ($retorno["imppredil"] == '1') || ($retorno["imppredil"] == '2') || ($retorno["imppredil"] == '')) {
                        $retorno["imppredil"] = 'S';
                    }

                    $retorno["impexp"] = $reg->getElementsByTagName("impexp")->item(0)->textContent;
                    $retorno["tipopropiedad"] = $reg->getElementsByTagName("tipopropiedad")->item(0)->textContent;
                    $retorno["tipolocal"] = $reg->getElementsByTagName("tipolocal")->item(0)->textContent;

                    // ***************************************************************************** //
                    // Datos de matricula antes del cambio de domicilio
                    // ***************************************************************************** //
                    (isset($reg->getElementsByTagName("fecmatant")->item(0)->textContent)) ? $retorno["fecmatant"] = trim($reg->getElementsByTagName("fecmatant")->item(0)->textContent) : $retorno["fecmatant"] = '';
                    (isset($reg->getElementsByTagName("fecrenant")->item(0)->textContent)) ? $retorno["fecrenant"] = trim($reg->getElementsByTagName("fecrenant")->item(0)->textContent) : $retorno["fecrenant"] = '';
                    (isset($reg->getElementsByTagName("camant")->item(0)->textContent)) ? $retorno["camant"] = trim($reg->getElementsByTagName("camant")->item(0)->textContent) : $retorno["camant"] = '';
                    (isset($reg->getElementsByTagName("munant")->item(0)->textContent)) ? $retorno["munant"] = trim($reg->getElementsByTagName("munant")->item(0)->textContent) : $retorno["munant"] = '';
                    (isset($reg->getElementsByTagName("benart7ant")->item(0)->textContent)) ? $retorno["benart7ant"] = trim($reg->getElementsByTagName("benart7ant")->item(0)->textContent) : $retorno["benart7ant"] = '';
                    (isset($reg->getElementsByTagName("benley1780ant")->item(0)->textContent)) ? $retorno["benley1780ant"] = trim($reg->getElementsByTagName("benley1780ant")->item(0)->textContent) : $retorno["benley1780ant"] = '';
                    (isset($reg->getElementsByTagName("ultanorenant")->item(0)->textContent)) ? $retorno["ultanorenant"] = trim($reg->getElementsByTagName("ultanorenant")->item(0)->textContent) : $retorno["ultanorenant"] = '';
                    (isset($reg->getElementsByTagName("matant")->item(0)->textContent)) ? $retorno["matant"] = trim($reg->getElementsByTagName("matant")->item(0)->textContent) : $retorno["matant"] = '';

                    (isset($reg->getElementsByTagName("tipogruemp")->item(0)->textContent)) ? $retorno["tipogruemp"] = trim($reg->getElementsByTagName("tipogruemp")->item(0)->textContent) : $retorno["tipogruemp"] = '';
                    (isset($reg->getElementsByTagName("nombregruemp")->item(0)->textContent)) ? $retorno["nombregruemp"] = trim($reg->getElementsByTagName("nombregruemp")->item(0)->textContent) : $retorno["nombregruemp"] = '';
                    (isset($reg->getElementsByTagName("empresafamiliar")->item(0)->textContent)) ? $retorno["empresafamiliar"] = trim($reg->getElementsByTagName("empresafamiliar")->item(0)->textContent) : $retorno["empresafamiliar"] = '';
                    (isset($reg->getElementsByTagName("procesosinnovacion")->item(0)->textContent)) ? $retorno["procesosinnovacion"] = trim($reg->getElementsByTagName("procesosinnovacion")->item(0)->textContent) : $retorno["procesosinnovacion"] = '';

                    // ***************************************************************************** //
                    // Informacion de afiliacion
                    // ***************************************************************************** //
                    (isset($reg->getElementsByTagName("afiliado")->item(0)->textContent)) ? $retorno["afiliado"] = $reg->getElementsByTagName("afiliado")->item(0)->textContent : $retorno["afiliado"] = '';
                    (isset($reg->getElementsByTagName("fechaafiliacion")->item(0)->textContent)) ? $retorno["fechaafiliacion"] = $reg->getElementsByTagName("fechaafiliacion")->item(0)->textContent : $retorno["fechaafiliacion"] = '';
                    (isset($reg->getElementsByTagName("ultanorenafi")->item(0)->textContent)) ? $retorno["ultanorenafi"] = $reg->getElementsByTagName("ultanorenafi")->item(0)->textContent : $retorno["ultanorenafi"] = '';
                    (isset($reg->getElementsByTagName("fechaultpagoafi")->item(0)->textContent)) ? $retorno["fechaultpagoafi"] = $reg->getElementsByTagName("fechaultpagoafi")->item(0)->textContent : $retorno["fechaultpagoafi"] = '';
                    (isset($reg->getElementsByTagName("valorultpagoafi")->item(0)->textContent)) ? $retorno["valorultpagoafi"] = $reg->getElementsByTagName("valorultpagoafi")->item(0)->textContent : $retorno["valorultpagoafi"] = 0;
                    (isset($reg->getElementsByTagName("saldoafiliado")->item(0)->textContent)) ? $retorno["saldoafiliado"] = $reg->getElementsByTagName("saldoafiliado")->item(0)->textContent : $retorno["saldoafiliado"] = 0;
                    (isset($reg->getElementsByTagName("telaflia")->item(0)->textContent)) ? $retorno["telaflia"] = $reg->getElementsByTagName("telaflia")->item(0)->textContent : $retorno["telaflia"] = '';
                    (isset($reg->getElementsByTagName("diraflia")->item(0)->textContent)) ? $retorno["diraflia"] = str_replace("#", "Nro.", $reg->getElementsByTagName("diraflia")->item(0)->textContent) : $retorno["diraflia"] = '';
                    (isset($reg->getElementsByTagName("munaflia")->item(0)->textContent)) ? $retorno["munaflia"] = $reg->getElementsByTagName("munaflia")->item(0)->textContent : $retorno["munaflia"] = '';
                    (isset($reg->getElementsByTagName("profaflia")->item(0)->textContent)) ? $retorno["profaflia"] = $reg->getElementsByTagName("profaflia")->item(0)->textContent : $retorno["profaflia"] = '';
                    (isset($reg->getElementsByTagName("contaflia")->item(0)->textContent)) ? $retorno["contaflia"] = $reg->getElementsByTagName("contaflia")->item(0)->textContent : $retorno["contaflia"] = '';
                    (isset($reg->getElementsByTagName("dircontaflia")->item(0)->textContent)) ? $retorno["dircontaflia"] = str_replace("#", "Nro.", $reg->getElementsByTagName("dircontaflia")->item(0)->textContent) : $retorno["dircontaflia"] = '';
                    (isset($reg->getElementsByTagName("muncontaflia")->item(0)->textContent)) ? $retorno["muncontaflia"] = $reg->getElementsByTagName("muncontaflia")->item(0)->textContent : $retorno["muncontaflia"] = '';
                    (isset($reg->getElementsByTagName("numactaaflia")->item(0)->textContent)) ? $retorno["numactaaflia"] = $reg->getElementsByTagName("numactaaflia")->item(0)->textContent : $retorno["numactaaflia"] = '';
                    (isset($reg->getElementsByTagName("fecactaaflia")->item(0)->textContent)) ? $retorno["fecactaaflia"] = $reg->getElementsByTagName("fecactaaflia")->item(0)->textContent : $retorno["fecactaaflia"] = '';
                    (isset($reg->getElementsByTagName("numactaafliacan")->item(0)->textContent)) ? $retorno["numactaafliacan"] = $reg->getElementsByTagName("numactaafliacan")->item(0)->textContent : $retorno["numactaafliacan"] = '';
                    (isset($reg->getElementsByTagName("fecactaafliacan")->item(0)->textContent)) ? $retorno["fecactaafliacan"] = $reg->getElementsByTagName("fecactaafliacan")->item(0)->textContent : $retorno["fecactaafliacan"] = '';
                    (isset($reg->getElementsByTagName("fecexafiliacion")->item(0)->textContent)) ? $retorno["fecexafiliacion"] = $reg->getElementsByTagName("fecexafiliacion")->item(0)->textContent : $retorno["fecexafiliacion"] = '';

                    // ***************************************************************************** //
                    // Pagos de afiliacion
                    // ***************************************************************************** //
                    $iAfil = 0;
                    $retorno["periodicoafiliados"] = array();
                    $reg3 = $reg->getElementsByTagName("pagosafiliado");
                    if (!empty($reg3)) {
                        foreach ($reg3 as $reg4) {
                            $iAfil++;
                            $retorno["periodicoafiliados"][$iAfil]["ano"] = ltrim($reg4->getElementsByTagName("perafiano")->item(0)->textContent, "0");
                            $retorno["periodicoafiliados"][$iAfil]["fecha"] = ltrim($reg4->getElementsByTagName("perafifecha")->item(0)->textContent, "0");
                            $retorno["periodicoafiliados"][$iAfil]["tipo"] = trim($reg4->getElementsByTagName("perafitipo")->item(0)->textContent);
                            $retorno["periodicoafiliados"][$iAfil]["recibo"] = trim($reg4->getElementsByTagName("perafirecibo")->item(0)->textContent);
                            if (!isset($reg4->getElementsByTagName("perafiopera")->item(0)->textContent)) {
                                $retorno["periodicoafiliados"][$iAfil]["operacion"] = '';
                            } else {
                                $retorno["periodicoafiliados"][$iAfil]["operacion"] = trim($reg4->getElementsByTagName("perafiopera")->item(0)->textContent);
                            }
                            $retorno["periodicoafiliados"][$iAfil]["valor"] = ltrim($reg4->getElementsByTagName("perafivalor")->item(0)->textContent, "0");
                        }
                    }

                    // ***************************************************************************** //
                    // informacion de ubicacion comercial en el registro mercantil
                    // ***************************************************************************** //
                    $retorno["lggr"] = $reg->getElementsByTagName("lggr")->item(0)->textContent;
                    $retorno["nombrecomercial"] = \funcionesGenerales::restaurarEspeciales($reg->getElementsByTagName("nombrecomercial")->item(0)->textContent);
                    $retorno["dircom"] = \funcionesGenerales::restaurarEspeciales(str_replace("#", "Nro.", $reg->getElementsByTagName("dircom")->item(0)->textContent));
                    $retorno["dircom_tipovia"] = $reg->getElementsByTagName("dircom_tipovia")->item(0)->textContent;
                    $retorno["dircom_numvia"] = $reg->getElementsByTagName("dircom_numvia")->item(0)->textContent;
                    $retorno["dircom_apevia"] = $reg->getElementsByTagName("dircom_apevia")->item(0)->textContent;
                    $retorno["dircom_orivia"] = $reg->getElementsByTagName("dircom_orivia")->item(0)->textContent;
                    $retorno["dircom_numcruce"] = $reg->getElementsByTagName("dircom_numcruce")->item(0)->textContent;
                    $retorno["dircom_apecruce"] = $reg->getElementsByTagName("dircom_apecruce")->item(0)->textContent;
                    $retorno["dircom_oricruce"] = $reg->getElementsByTagName("dircom_oricruce")->item(0)->textContent;
                    $retorno["dircom_numplaca"] = $reg->getElementsByTagName("dircom_numplaca")->item(0)->textContent;
                    $retorno["dircom_complemento"] = (\funcionesGenerales::restaurarEspeciales($reg->getElementsByTagName("dircom_complemento")->item(0)->textContent));
                    $retorno["muncom"] = $reg->getElementsByTagName("muncom")->item(0)->textContent;
                    $retorno["muncomnombre"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $reg->getElementsByTagName("muncom")->item(0)->textContent . "'", "ciudad");
                    (isset($reg->getElementsByTagName("paicom")->item(0)->textContent)) ? $retorno["paicom"] = $reg->getElementsByTagName("paicom")->item(0)->textContent : $retorno["paicom"] = '';
                    (isset($reg->getElementsByTagName("codigopostalcom")->item(0)->textContent)) ? $retorno["codigopostalcom"] = $reg->getElementsByTagName("codigopostalcom")->item(0)->textContent : $retorno["codigopostalcom"] = '';
                    (isset($reg->getElementsByTagName("codigozonacom")->item(0)->textContent)) ? $retorno["codigozonacom"] = $reg->getElementsByTagName("codigozonacom")->item(0)->textContent : $retorno["codigozonacom"] = '';
                    $retorno["telcom1"] = $reg->getElementsByTagName("telcom1")->item(0)->textContent;
                    $retorno["telcom2"] = $reg->getElementsByTagName("telcom2")->item(0)->textContent;
                    $retorno["faxcom"] = $reg->getElementsByTagName("faxcom")->item(0)->textContent;
                    if (isset($reg->getElementsByTagName("celcom")->item(0)->textContent)) {
                        $retorno["celcom"] = $reg->getElementsByTagName("celcom")->item(0)->textContent;
                    } else {
                        $retorno["celcom"] = '';
                    }
                    $retorno["aacom"] = $reg->getElementsByTagName("aacom")->item(0)->textContent;
                    $retorno["zonapostalcom"] = $reg->getElementsByTagName("zonapostalcom")->item(0)->textContent;
                    if (!isset($reg->getElementsByTagName("barriocom")->item(0)->textContent)) {
                        $retorno["barriocom"] = "";
                        $retorno["barriocomnombre"] = "";
                    } else {
                        $retorno["barriocom"] = $reg->getElementsByTagName("barriocom")->item(0)->textContent;
                        $retorno["barriocomnombre"] = retornarRegistroMysqliApi($mysqli, 'mreg_barriosmuni', "idmunicipio='" . $retorno["muncom"] . "' and idbarrio='" . $retorno["barriocom"] . "'", "nombre");
                    }
                    if (!isset($reg->getElementsByTagName("numpredial")->item(0)->textContent)) {
                        $retorno["numpredial"] = "";
                    } else {
                        $retorno["numpredial"] = $reg->getElementsByTagName("numpredial")->item(0)->textContent;
                    }

                    $retorno["emailcom"] = (\funcionesGenerales::restaurarEspeciales($reg->getElementsByTagName("emailcom")->item(0)->textContent));
                    if (!isset($reg->getElementsByTagName("emailcom2")->item(0)->textContent)) {
                        $retorno["emailcom2"] = '';
                    } else {
                        $retorno["emailcom2"] = (\funcionesGenerales::restaurarEspeciales($reg->getElementsByTagName("emailcom2")->item(0)->textContent));
                    }
                    if (!isset($reg->getElementsByTagName("emailcom3")->item(0)->textContent)) {
                        $retorno["emailcom3"] = '';
                    } else {
                        $retorno["emailcom3"] = (\funcionesGenerales::restaurarEspeciales($reg->getElementsByTagName("emailcom3")->item(0)->textContent));
                    }
                    if (!isset($reg->getElementsByTagName("nombresegundocontacto")->item(0)->textContent)) {
                        $retorno["nombresegundocontacto"] = '';
                    } else {
                        $retorno["nombresegundocontacto"] = (\funcionesGenerales::restaurarEspeciales($reg->getElementsByTagName("nombresegundocontacto")->item(0)->textContent));
                    }
                    if (!isset($reg->getElementsByTagName("cargosegundocontacto")->item(0)->textContent)) {
                        $retorno["cargosegundocontacto"] = '';
                    } else {
                        $retorno["cargosegundocontacto"] = (\funcionesGenerales::restaurarEspeciales($reg->getElementsByTagName("cargosegundocontacto")->item(0)->textContent));
                    }

                    $retorno["urlcom"] = (\funcionesGenerales::restaurarEspeciales($reg->getElementsByTagName("urlcom")->item(0)->textContent));

                    // ***************************************************************************** //
                    // informacion de ubicacion de notificacion
                    // ***************************************************************************** //
                    $retorno["dirnot"] = (\funcionesGenerales::restaurarEspeciales(str_replace("#", "Nro.", $reg->getElementsByTagName("dirnot")->item(0)->textContent)));
                    $retorno["dirnot_tipovia"] = $reg->getElementsByTagName("dirnot_tipovia")->item(0)->textContent;
                    $retorno["dirnot_numvia"] = $reg->getElementsByTagName("dirnot_numvia")->item(0)->textContent;
                    $retorno["dirnot_apevia"] = $reg->getElementsByTagName("dirnot_apevia")->item(0)->textContent;
                    $retorno["dirnot_orivia"] = $reg->getElementsByTagName("dirnot_orivia")->item(0)->textContent;
                    $retorno["dirnot_numcruce"] = $reg->getElementsByTagName("dirnot_numcruce")->item(0)->textContent;
                    $retorno["dirnot_apecruce"] = $reg->getElementsByTagName("dirnot_apecruce")->item(0)->textContent;
                    $retorno["dirnot_oricruce"] = $reg->getElementsByTagName("dirnot_oricruce")->item(0)->textContent;
                    $retorno["dirnot_numplaca"] = $reg->getElementsByTagName("dirnot_numplaca")->item(0)->textContent;
                    $retorno["dirnot_complemento"] = (\funcionesGenerales::restaurarEspeciales($reg->getElementsByTagName("dirnot_complemento")->item(0)->textContent));
                    $retorno["munnot"] = $reg->getElementsByTagName("munnot")->item(0)->textContent;
                    $retorno["munnotnombre"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . $reg->getElementsByTagName("munnot")->item(0)->textContent . "'", "ciudad");
                    (isset($reg->getElementsByTagName("painot")->item(0)->textContent)) ? $retorno["painot"] = $reg->getElementsByTagName("painot")->item(0)->textContent : $retorno["painot"] = '';
                    (isset($reg->getElementsByTagName("codigopostalnot")->item(0)->textContent)) ? $retorno["codigopostalnot"] = $reg->getElementsByTagName("codigopostalnot")->item(0)->textContent : $retorno["codigopostalnot"] = '';
                    (isset($reg->getElementsByTagName("codigozonanot")->item(0)->textContent)) ? $retorno["codigozonanot"] = $reg->getElementsByTagName("codigozonanot")->item(0)->textContent : $retorno["codigozonanot"] = '';
                    (isset($reg->getElementsByTagName("tiposedeadm")->item(0)->textContent)) ? $retorno["tiposedeadm"] = $reg->getElementsByTagName("tiposedeadm")->item(0)->textContent : $retorno["tiposedeadm"] = '';
                    $retorno["telnot"] = $reg->getElementsByTagName("telnot")->item(0)->textContent;
                    if (!isset($reg->getElementsByTagName("telnot2")->item(0)->textContent)) {
                        $retorno["telnot2"] = '';
                    } else {
                        $retorno["telnot2"] = $reg->getElementsByTagName("telnot2")->item(0)->textContent;
                    }
                    if (!isset($reg->getElementsByTagName("celnot")->item(0)->textContent)) {
                        $retorno["celnot"] = '';
                    } else {
                        $retorno["celnot"] = $reg->getElementsByTagName("celnot")->item(0)->textContent;
                    }

                    $retorno["faxnot"] = $reg->getElementsByTagName("faxnot")->item(0)->textContent;
                    $retorno["aanot"] = $reg->getElementsByTagName("aanot")->item(0)->textContent;
                    $retorno["zonapostalnot"] = $reg->getElementsByTagName("zonapostalnot")->item(0)->textContent;
                    if (!isset($reg->getElementsByTagName("barrionot")->item(0)->textContent)) {
                        $retorno["barrionot"] = "";
                        $retorno["barrionotnombre"] = '';
                    } else {
                        $retorno["barrionot"] = $reg->getElementsByTagName("barrionot")->item(0)->textContent;
                        $retorno["barrionotnombre"] = retornarRegistroMysqliApi($mysqli, 'mreg_barriosmuni', "idmunicipio='" . $retorno["munnot"] . "' and idbarrio='" . $retorno["barrionot"] . "'", "nombre");
                    }
                    $retorno["emailnot"] = (\funcionesGenerales::restaurarEspeciales($reg->getElementsByTagName("emailnot")->item(0)->textContent));
                    $retorno["urlnot"] = (\funcionesGenerales::restaurarEspeciales($reg->getElementsByTagName("urlnot")->item(0)->textContent));

                    // ***************************************************************************** //
                    // Datos de correspondencia
                    // ***************************************************************************** //
                    (isset($reg->getElementsByTagName("dircor")->item(0)->textContent)) ? (\funcionesGenerales::restaurarEspeciales(str_replace("#", "Nro.", $retorno["dircor"] = $reg->getElementsByTagName("dircor")->item(0)->textContent))) : $retorno["dircor"] = '';
                    (isset($reg->getElementsByTagName("telcor")->item(0)->textContent)) ? (\funcionesGenerales::restaurarEspeciales($retorno["telcor"] = $reg->getElementsByTagName("telcor")->item(0)->textContent)) : $retorno["telcor"] = '';
                    (isset($reg->getElementsByTagName("muncor")->item(0)->textContent)) ? (\funcionesGenerales::restaurarEspeciales($retorno["muncor"] = $reg->getElementsByTagName("muncor")->item(0)->textContent)) : $retorno["muncor"] = '';

                    // ***************************************************************************** //
                    // informacion de actividad economica
                    // ***************************************************************************** //
                    $i = 0;
                    $retorno["ciius"][1] = '';
                    $retorno["ciius"][2] = '';
                    $retorno["ciius"][3] = '';
                    $retorno["ciius"][4] = '';
                    $retorno["ciius"][5] = '';
                    $retorno["ciiusant"][1] = '';
                    $retorno["ciiusant"][2] = '';
                    $retorno["ciiusant"][3] = '';
                    $retorno["ciiusant"][4] = '';
                    $retorno["ciiusant"][5] = '';

                    $ciius = $reg->getElementsByTagName("ciiu");
                    if (!empty($ciius)) {
                        foreach ($ciius as $ciiu) {
                            if (!empty($ciiu)) {
                                $i++;
                                $retorno["ciius"][$i] = trim($ciiu->textContent);
                            }
                        }
                    }
                    unset($ciius);

                    $ciiusant = $reg->getElementsByTagName("ciiuant");
                    if (!empty($ciiusant)) {
                        foreach ($ciiusant as $ciiu) {
                            if (!empty($ciiu)) {
                                $i++;
                                $retorno["ciiusant"][$i] = trim($ciiu->textContent);
                            }
                        }
                    }
                    unset($ciiusant);

                    if (trim($retorno["ciius"][1]) != '') {
                        if ((substr($retorno["ciius"][1], 0, 1) >= '0') && (substr($retorno["ciius"][1], 0, 1) <= '9')) {
                            $retorno["ciius"][1] = \funcionesGenerales::localizarLetraCiiu($mysqli, $retorno["ciius"][1]) . $retorno["ciius"][1];
                        }
                    }
                    if (trim($retorno["ciius"][2]) != '') {
                        if ((substr($retorno["ciius"][2], 0, 1) >= '0') && (substr($retorno["ciius"][2], 0, 1) <= '9')) {
                            $retorno["ciius"][2] = \funcionesGenerales::localizarLetraCiiu($mysqli, $retorno["ciius"][2]) . $retorno["ciius"][2];
                        }
                    }
                    if (trim($retorno["ciius"][3]) != '') {
                        if ((substr($retorno["ciius"][3], 0, 1) >= '0') && (substr($retorno["ciius"][3], 0, 1) <= '9')) {
                            $retorno["ciius"][3] = \funcionesGenerales::localizarLetraCiiu($mysqli, $retorno["ciius"][3]) . $retorno["ciius"][3];
                        }
                    }
                    if (trim($retorno["ciius"][4]) != '') {
                        if ((substr($retorno["ciius"][4], 0, 1) >= '0') && (substr($retorno["ciius"][4], 0, 1) <= '9')) {
                            $retorno["ciius"][4] = \funcionesGenerales::localizarLetraCiiu($mysqli, $retorno["ciius"][4]) . $retorno["ciius"][4];
                        }
                    }

                    $i = 0;
                    $retorno["shd"][1] = '';
                    $retorno["shd"][2] = '';
                    $retorno["shd"][3] = '';
                    $retorno["shd"][4] = '';

                    $shds = $reg->getElementsByTagName("shd");
                    if (!empty($shds)) {
                        foreach ($shds as $shd) {
                            if (!empty($shd)) {
                                $i++;
                                $retorno["shd"][$i] = trim($shd->textContent);
                            }
                        }
                    }
                    unset($shds);

                    //
                    if (isset($reg->getElementsByTagName("ingesperados")->item(0)->textContent)) {
                        $retorno["ingesperados"] = doubleval($reg->getElementsByTagName("ingesperados")->item(0)->textContent);
                    } else {
                        $retorno["ingesperados"] = 0;
                    }

                    //
                    if (isset($reg->getElementsByTagName("versionciiu")->item(0)->textContent)) {
                        $retorno["versionciiu"] = trim($reg->getElementsByTagName("versionciiu")->item(0)->textContent);
                    } else {
                        $retorno["versionciiu"] = '';
                    }

                    if (isset($reg->getElementsByTagName("desactiv")->item(0)->textContent)) {
                        $retorno["desactiv"] = $reg->getElementsByTagName("desactiv")->item(0)->textContent;
                    } else {
                        $retorno["desactiv"] = '';
                    }

                    if (isset($reg->getElementsByTagName("feciniact1")->item(0)->textContent)) {
                        $retorno["feciniact1"] = trim($reg->getElementsByTagName("feciniact1")->item(0)->textContent);
                    } else {
                        $retorno["feciniact1"] = '';
                    }
                    if (isset($reg->getElementsByTagName("feciniact2")->item(0)->textContent)) {
                        $retorno["feciniact2"] = trim($reg->getElementsByTagName("feciniact2")->item(0)->textContent);
                    } else {
                        $retorno["feciniact2"] = '';
                    }

                    // recupera responsaibilidades tributarias
                    if (isset($reg->getElementsByTagName("codrespotri")->item(0)->textContent) && trim($reg->getElementsByTagName("codrespotri")->item(0)->textContent) != '') {
                        $l = explode(',', trim($reg->getElementsByTagName("codrespotri")->item(0)->textContent));
                        $iresp = 0;
                        foreach ($l as $l1) {
                            $iresp++;
                            $retorno["codrespotri"][$iresp] = $l1;
                            $retorno["anexoresponsabilidades"] = 'si';
                        }
                    }

                    //
                    (isset($reg->getElementsByTagName("codaduaneros")->item(0)->textContent)) ? (\funcionesGenerales::restaurarEspeciales($retorno["codaduaneros"] = $reg->getElementsByTagName("codaduaneros")->item(0)->textContent)) : $retorno["codaduaneros"] = '';
                    (isset($reg->getElementsByTagName("gruponiif")->item(0)->textContent)) ? (\funcionesGenerales::restaurarEspeciales($retorno["gruponiif"] = $reg->getElementsByTagName("gruponiif")->item(0)->textContent)) : $retorno["gruponiif"] = '';
                    (isset($reg->getElementsByTagName("niifconciliacion")->item(0)->textContent)) ? (\funcionesGenerales::restaurarEspeciales($retorno["niifconciliacion"] = $reg->getElementsByTagName("niifconciliacion")->item(0)->textContent)) : $retorno["niifconciliacion"] = '';
                    (isset($reg->getElementsByTagName("aportantesegsocial")->item(0)->textContent)) ? (\funcionesGenerales::restaurarEspeciales($retorno["aportantesegsocial"] = $reg->getElementsByTagName("aportantesegsocial")->item(0)->textContent)) : $retorno["aportantesegsocial"] = '';
                    (isset($reg->getElementsByTagName("tipoaportantesegsocial")->item(0)->textContent)) ? (\funcionesGenerales::restaurarEspeciales($retorno["tipoaportantesegsocial"] = $reg->getElementsByTagName("tipoaportantesegsocial")->item(0)->textContent)) : $retorno["tipoaportantesegsocial"] = '';
                    (isset($reg->getElementsByTagName("cumplerequisitos1780")->item(0)->textContent)) ? (\funcionesGenerales::restaurarEspeciales($retorno["cumplerequisitos1780"] = $reg->getElementsByTagName("cumplerequisitos1780")->item(0)->textContent)) : $retorno["cumplerequisitos1780"] = '';
                    (isset($reg->getElementsByTagName("renunciabeneficios1780")->item(0)->textContent)) ? (\funcionesGenerales::restaurarEspeciales($retorno["renunciabeneficios1780"] = $reg->getElementsByTagName("renunciabeneficios1780")->item(0)->textContent)) : $retorno["renunciabeneficios1780"] = '';
                    (isset($reg->getElementsByTagName("cumplerequisitos1780primren")->item(0)->textContent)) ? (\funcionesGenerales::restaurarEspeciales($retorno["cumplerequisitos1780primren"] = $reg->getElementsByTagName("cumplerequisitos1780primren")->item(0)->textContent)) : $retorno["cumplerequisitos1780primren"] = '';

                    // ***************************************************************************** //
                    // informacion financiera - prinicpal o actual
                    // ***************************************************************************** //
                    // ***************************************************************************** //
                    // Inicializa
                    // ***************************************************************************** //
                    $retorno["anodatos"] = '';
                    $retorno["fechadatos"] = '';
                    $retorno["personal"] = 0;
                    $retorno["personaltemp"] = 0;
                    $retorno["patrimonio"] = 0;
                    $retorno["actvin"] = 0;
                    $retorno["actcte"] = 0;
                    $retorno["actnocte"] = 0;
                    $retorno["actfij"] = 0;
                    $retorno["fijnet"] = 0;
                    $retorno["actotr"] = 0;
                    $retorno["actval"] = 0;
                    $retorno["acttot"] = 0;
                    $retorno["actsinaju"] = 0;
                    $retorno["invent"] = 0;
                    $retorno["pascte"] = 0;
                    $retorno["paslar"] = 0;
                    $retorno["pastot"] = 0;
                    $retorno["pattot"] = 0;
                    $retorno["paspat"] = 0;
                    $retorno["balsoc"] = 0;
                    $retorno["ingope"] = 0;
                    $retorno["ingnoope"] = 0;
                    $retorno["gtoven"] = 0;
                    $retorno["gtoadm"] = 0;
                    $retorno["gasope"] = 0;
                    $retorno["gasnoope"] = 0;
                    $retorno["cosven"] = 0;
                    $retorno["depamo"] = 0;
                    $retorno["gasint"] = 0;
                    $retorno["gasimp"] = 0;
                    $retorno["utiope"] = 0;
                    $retorno["utinet"] = 0;
                    $retorno["valest"] = 0;

                    // ***************************************************************************** //
                    // Mueve actuales basicos 
                    // ***************************************************************************** //
                    if (isset($reg->getElementsByTagName("anodatos")->item(0)->textContent)) {
                        $retorno["anodatos"] = $reg->getElementsByTagName("anodatos")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("fechadatos")->item(0)->textContent)) {
                        $retorno["fechadatos"] = $reg->getElementsByTagName("fechadatos")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("personal")->item(0)->textContent)) {
                        $retorno["personal"] = $reg->getElementsByTagName("personal")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("personaltemp")->item(0)->textContent)) {
                        $retorno["personaltemp"] = doubleval($reg->getElementsByTagName("personaltemp")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("actvin")->item(0)->textContent)) {
                        $retorno["actvin"] = $reg->getElementsByTagName("actvin")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("actcte")->item(0)->textContent)) {
                        $retorno["actcte"] = $reg->getElementsByTagName("actcte")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("actnocte")->item(0)->textContent)) {
                        $retorno["actnocte"] = $reg->getElementsByTagName("actnocte")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("actfij")->item(0)->textContent)) {
                        $retorno["actfij"] = $reg->getElementsByTagName("actfij")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("fijnet")->item(0)->textContent)) {
                        $retorno["fijnet"] = $reg->getElementsByTagName("fijnet")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("actotr")->item(0)->textContent)) {
                        $retorno["actotr"] = $reg->getElementsByTagName("actotr")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("actval")->item(0)->textContent)) {
                        $retorno["actval"] = $reg->getElementsByTagName("actval")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("acttot")->item(0)->textContent)) {
                        $retorno["acttot"] = $reg->getElementsByTagName("acttot")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("pascte")->item(0)->textContent)) {
                        $retorno["pascte"] = $reg->getElementsByTagName("pascte")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("paslar")->item(0)->textContent)) {
                        $retorno["paslar"] = $reg->getElementsByTagName("paslar")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("pastot")->item(0)->textContent)) {
                        $retorno["pastot"] = $reg->getElementsByTagName("pastot")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("pattot")->item(0)->textContent)) {
                        $retorno["pattot"] = $reg->getElementsByTagName("pattot")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("paspat")->item(0)->textContent)) {
                        $retorno["paspat"] = $reg->getElementsByTagName("paspat")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("balsoc")->item(0)->textContent)) {
                        $retorno["balsoc"] = $reg->getElementsByTagName("balsoc")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("ingope")->item(0)->textContent)) {
                        $retorno["ingope"] = $reg->getElementsByTagName("ingope")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("ingnoope")->item(0)->textContent)) {
                        $retorno["ingnoope"] = $reg->getElementsByTagName("ingnoope")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("gtoven")->item(0)->textContent)) {
                        $retorno["gtoven"] = $reg->getElementsByTagName("gtoven")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("gtoadm")->item(0)->textContent)) {
                        $retorno["gtoadm"] = $reg->getElementsByTagName("gtoadm")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("gasope")->item(0)->textContent)) {
                        $retorno["gasope"] = $reg->getElementsByTagName("gasope")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("gasnoope")->item(0)->textContent)) {
                        $retorno["gasnoope"] = $reg->getElementsByTagName("gasnoope")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("cosven")->item(0)->textContent)) {
                        $retorno["cosven"] = $reg->getElementsByTagName("cosven")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("gasint")->item(0)->textContent)) {
                        $retorno["gasint"] = $reg->getElementsByTagName("gasint")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("gasimp")->item(0)->textContent)) {
                        $retorno["gasimp"] = $reg->getElementsByTagName("gasimp")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("utiope")->item(0)->textContent)) {
                        $retorno["utiope"] = $reg->getElementsByTagName("utiope")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("utinet")->item(0)->textContent)) {
                        $retorno["utinet"] = $reg->getElementsByTagName("utinet")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("valest")->item(0)->textContent)) {
                        $retorno["valest"] = $reg->getElementsByTagName("valest")->item(0)->textContent;
                    }

                    // ***************************************************************************** //
                    // Información del patrimonio de las ESADL
                    // ***************************************************************************** //
                    if (isset($reg->getElementsByTagName("anodatospatrimonio")->item(0)->textContent)) {
                        $retorno["anodatospatrimonio"] = $reg->getElementsByTagName("anodatospatrimonio")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("fechadatospatrimonio")->item(0)->textContent)) {
                        $retorno["fechadatospatrimonio"] = $reg->getElementsByTagName("fechadatospatrimonio")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("patrimonioesadl")->item(0)->textContent)) {
                        $retorno["patrimonioesadl"] = $reg->getElementsByTagName("patrimonioesadl")->item(0)->textContent;
                    }

                    // ***************************************************************************** //
                    // informacion financiera - prinicpal o actual
                    // ***************************************************************************** //
                    if (isset($reg->getElementsByTagName("anodatosactual")->item(0)->textContent)) {
                        $retorno["anodatos"] = $reg->getElementsByTagName("anodatosactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("fechadatosactual")->item(0)->textContent)) {
                        $retorno["fechadatos"] = $reg->getElementsByTagName("fechadatosactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("personalactual")->item(0)->textContent)) {
                        $retorno["personal"] = $reg->getElementsByTagName("personalactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("patrimonioactual")->item(0)->textContent)) {
                        $retorno["patrimonio"] = doubleval($reg->getElementsByTagName("patrimonio")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("personaltempactual")->item(0)->textContent)) {
                        $retorno["personaltemp"] = doubleval($reg->getElementsByTagName("personaltempactual")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("actvinactual")->item(0)->textContent)) {
                        $retorno["actvin"] = $reg->getElementsByTagName("actvinactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("actcteactual")->item(0)->textContent)) {
                        $retorno["actcte"] = $reg->getElementsByTagName("actcteactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("actnocteactual")->item(0)->textContent)) {
                        $retorno["actnocte"] = $reg->getElementsByTagName("actnocteactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("actfijactual")->item(0)->textContent)) {
                        $retorno["actfij"] = $reg->getElementsByTagName("actfijactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("fijnetactual")->item(0)->textContent)) {
                        $retorno["fijnet"] = $reg->getElementsByTagName("fijnetactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("actotractual")->item(0)->textContent)) {
                        $retorno["actotr"] = $reg->getElementsByTagName("actotractual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("actvalactual")->item(0)->textContent)) {
                        $retorno["actval"] = $reg->getElementsByTagName("actvalactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("acttotactual")->item(0)->textContent)) {
                        $retorno["acttot"] = $reg->getElementsByTagName("acttotactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("pascteactual")->item(0)->textContent)) {
                        $retorno["pascte"] = $reg->getElementsByTagName("pascteactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("paslaractual")->item(0)->textContent)) {
                        $retorno["paslar"] = $reg->getElementsByTagName("paslaractual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("pastotactual")->item(0)->textContent)) {
                        $retorno["pastot"] = $reg->getElementsByTagName("pastotactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("pattotactual")->item(0)->textContent)) {
                        $retorno["pattot"] = $reg->getElementsByTagName("pattotactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("paspatactual")->item(0)->textContent)) {
                        $retorno["paspat"] = $reg->getElementsByTagName("paspatactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("balsocactual")->item(0)->textContent)) {
                        $retorno["balsoc"] = $reg->getElementsByTagName("balsocactual")->item(0)->textContent;
                    }

                    if (isset($reg->getElementsByTagName("ingopeactual")->item(0)->textContent)) {
                        $retorno["ingope"] = $reg->getElementsByTagName("ingopeactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("ingnoopeactual")->item(0)->textContent)) {
                        $retorno["ingnoope"] = $reg->getElementsByTagName("ingnoopeactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("gtovenactual")->item(0)->textContent)) {
                        $retorno["gtoven"] = $reg->getElementsByTagName("gtovenactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("gtoadmactual")->item(0)->textContent)) {
                        $retorno["gtoadm"] = $reg->getElementsByTagName("gtoadmactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("gasopeactual")->item(0)->textContent)) {
                        $retorno["gasope"] = $reg->getElementsByTagName("gasopeactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("gasnoopeactual")->item(0)->textContent)) {
                        $retorno["gasnoope"] = $reg->getElementsByTagName("gasnoopeactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("cosvenactual")->item(0)->textContent)) {
                        $retorno["cosven"] = $reg->getElementsByTagName("cosvenactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("gasintactual")->item(0)->textContent)) {
                        $retorno["gasint"] = $reg->getElementsByTagName("gasintactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("gasimpactual")->item(0)->textContent)) {
                        $retorno["gasimp"] = $reg->getElementsByTagName("gasimpactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("utiopeactual")->item(0)->textContent)) {
                        $retorno["utiope"] = $reg->getElementsByTagName("utiopeactual")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("utinetactual")->item(0)->textContent)) {
                        $retorno["utinet"] = $reg->getElementsByTagName("utinetactual")->item(0)->textContent;
                    }

                    // ***************************************************************************** //
                    // Información de capitales            
                    // ***************************************************************************** //
                    $retorno["anodatoscap"] = '';
                    $retorno["fechadatoscap"] = '';
                    $retorno["cap_porcnaltot"] = 0;
                    $retorno["cap_porcnalpri"] = 0;
                    $retorno["cap_porcnalpub"] = 0;
                    $retorno["cap_porcexttot"] = 0;
                    $retorno["cap_porcextpri"] = 0;
                    $retorno["cap_porcextpub"] = 0;
                    $retorno["cap_apolab"] = 0;
                    $retorno["cap_apolabadi"] = 0;
                    $retorno["cap_apoact"] = 0;
                    $retorno["cap_apodin"] = 0;
                    $retorno["capaut"] = 0;
                    $retorno["capsus"] = 0;
                    $retorno["cappag"] = 0;
                    $retorno["capsoc"] = 0;

                    $retorno["cap_porcnaltot"] = $reg->getElementsByTagName("cap_porcnaltot")->item(0)->textContent;
                    $retorno["cap_porcnalpri"] = $reg->getElementsByTagName("cap_porcnalpri")->item(0)->textContent;
                    $retorno["cap_porcnalpub"] = $reg->getElementsByTagName("cap_porcnalpub")->item(0)->textContent;
                    $retorno["cap_porcexttot"] = $reg->getElementsByTagName("cap_porcexttot")->item(0)->textContent;
                    $retorno["cap_porcextpri"] = $reg->getElementsByTagName("cap_porcextpri")->item(0)->textContent;
                    $retorno["cap_porcextpub"] = $reg->getElementsByTagName("cap_porcextpub")->item(0)->textContent;

                    if (isset($reg->getElementsByTagName("cap_anodatos")->item(0)->textContent)) {
                        $retorno["anodatoscap"] = $reg->getElementsByTagName("cap_anodatos")->item(0)->textContent;
                    } else {
                        $retorno["anodatoscap"] = '';
                    }
                    if (isset($reg->getElementsByTagName("cap_fechadatos")->item(0)->textContent)) {
                        $retorno["fechadatoscap"] = $reg->getElementsByTagName("cap_fechadatos")->item(0)->textContent;
                    } else {
                        $retorno["fechadatoscap"] = '';
                    }
                    if (isset($reg->getElementsByTagName("cap_autorizado")->item(0)->textContent)) {
                        $retorno["capaut"] = $reg->getElementsByTagName("cap_autorizado")->item(0)->textContent;
                    } else {
                        $retorno["capaut"] = '';
                    }
                    if (isset($reg->getElementsByTagName("cap_suscrito")->item(0)->textContent)) {
                        $retorno["capsus"] = $reg->getElementsByTagName("cap_suscrito")->item(0)->textContent;
                    } else {
                        $retorno["capsus"] = '';
                    }
                    if (isset($reg->getElementsByTagName("cap_pagado")->item(0)->textContent)) {
                        $retorno["cappag"] = $reg->getElementsByTagName("cap_pagado")->item(0)->textContent;
                    } else {
                        $retorno["cappag"] = '';
                    }
                    if (isset($reg->getElementsByTagName("cap_social")->item(0)->textContent)) {
                        $retorno["capsoc"] = $reg->getElementsByTagName("cap_social")->item(0)->textContent;
                    } else {
                        $retorno["capsoc"] = '';
                    }

                    if (isset($reg->getElementsByTagName("cap_apolab")->item(0)->textContent)) {
                        $retorno["cap_apolab"] = $reg->getElementsByTagName("cap_apolab")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("cap_apolabadi")->item(0)->textContent)) {
                        $retorno["cap_apolabadi"] = $reg->getElementsByTagName("cap_apolabadi")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("cap_apoact")->item(0)->textContent)) {
                        $retorno["cap_apoact"] = $reg->getElementsByTagName("cap_apoact")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("cap_apodin")->item(0)->textContent)) {
                        $retorno["cap_apodin"] = $reg->getElementsByTagName("cap_apodin")->item(0)->textContent;
                    }
                    $retorno["cap_apotot"] = $retorno["cap_apolab"] + $retorno["cap_apolabadi"] + $retorno["cap_apoact"] + $retorno["cap_apodin"];

                    $retorno["capitales"] = array();
                    // if (isset($reg->getElementsByTagName("cap"))) {
                    $caps = $reg->getElementsByTagName("cap");
                    if (!empty($caps)) {
                        $icap = 0;
                        foreach ($caps as $hcap) {
                            if (isset($hcap->getElementsByTagName("cap_anodat")->item(0)->textContent)) {
                                $icap++;
                                $retorno["capitales"][$icap]["anodatos"] = $hcap->getElementsByTagName("cap_anodat")->item(0)->textContent;
                                $retorno["capitales"][$icap]["fechadatos"] = $hcap->getElementsByTagName("cap_fecdat")->item(0)->textContent;
                                $retorno["capitales"][$icap]["libro"] = $hcap->getElementsByTagName("cap_libro")->item(0)->textContent;
                                $retorno["capitales"][$icap]["registro"] = $hcap->getElementsByTagName("cap_registro")->item(0)->textContent;
                                $retorno["capitales"][$icap]["tipoeconomia"] = $hcap->getElementsByTagName("cap_economia")->item(0)->textContent;

                                $retorno["capitales"][$icap]["pornaltot"] = doubleval($hcap->getElementsByTagName("cap_pctnaltot")->item(0)->textContent);
                                $retorno["capitales"][$icap]["pornalpri"] = doubleval($hcap->getElementsByTagName("cap_pctnalpri")->item(0)->textContent);
                                $retorno["capitales"][$icap]["pornalpub"] = doubleval($hcap->getElementsByTagName("cap_pctnalpub")->item(0)->textContent);
                                $retorno["capitales"][$icap]["porexttot"] = doubleval($hcap->getElementsByTagName("cap_pctexttot")->item(0)->textContent);
                                $retorno["capitales"][$icap]["porextpri"] = doubleval($hcap->getElementsByTagName("cap_pctextpri")->item(0)->textContent);
                                $retorno["capitales"][$icap]["porextpub"] = doubleval($hcap->getElementsByTagName("cap_pctextpub")->item(0)->textContent);

                                $retorno["capitales"][$icap]["apoact"] = doubleval($hcap->getElementsByTagName("cap_apoact")->item(0)->textContent);
                                $retorno["capitales"][$icap]["apodin"] = doubleval($hcap->getElementsByTagName("cap_apodin")->item(0)->textContent);
                                $retorno["capitales"][$icap]["apolab"] = doubleval($hcap->getElementsByTagName("cap_apolab")->item(0)->textContent);
                                $retorno["capitales"][$icap]["apolabadi"] = doubleval($hcap->getElementsByTagName("cap_apolabadi")->item(0)->textContent);

                                $retorno["capitales"][$icap]["suscrito"] = doubleval($hcap->getElementsByTagName("cap_suscrito")->item(0)->textContent);
                                $retorno["capitales"][$icap]["autorizado"] = doubleval($hcap->getElementsByTagName("cap_autorizado")->item(0)->textContent);
                                $retorno["capitales"][$icap]["pagado"] = doubleval($hcap->getElementsByTagName("cap_pagado")->item(0)->textContent);
                                $retorno["capitales"][$icap]["social"] = doubleval($hcap->getElementsByTagName("cap_social")->item(0)->textContent);
                                $retorno["capitales"][$icap]["asigsuc"] = doubleval($hcap->getElementsByTagName("cap_asigsuc")->item(0)->textContent);
                                $retorno["capitales"][$icap]["cuosuscrito"] = doubleval($hcap->getElementsByTagName("cap_cuosuscrito")->item(0)->textContent);
                                $retorno["capitales"][$icap]["cuoautorizado"] = doubleval($hcap->getElementsByTagName("cap_cuoautorizado")->item(0)->textContent);
                                $retorno["capitales"][$icap]["cuopagado"] = doubleval($hcap->getElementsByTagName("cap_cuopagado")->item(0)->textContent);
                                $retorno["capitales"][$icap]["cuosocial"] = doubleval($hcap->getElementsByTagName("cap_cuosocial")->item(0)->textContent);
                            }
                        }
                    }
                    // }
                    // ***************************************************************************** //
                    // CAntidad mujeres by tamano empresarial
                    // ***************************************************************************** //
                    if (isset($reg->getElementsByTagName("cantidadmujeres")->item(0)->textContent)) {
                        $retorno["cantidadmujeres"] = $reg->getElementsByTagName("cantidadmujeres")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("cantidadmujerescargosdirectivos")->item(0)->textContent)) {
                        $retorno["cantidadmujerescargosdirectivos"] = $reg->getElementsByTagName("cantidadmujerescargosdirectivos")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("participacionmujeres")->item(0)->textContent)) {
                        $retorno["participacionmujeres"] = $reg->getElementsByTagName("participacionmujeres")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("participacionetnia")->item(0)->textContent)) {
                        $retorno["participacionetnia"] = $reg->getElementsByTagName("participacionetnia")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("ciiutamanoempresarial")->item(0)->textContent)) {
                        $retorno["ciiutamanoempresarial"] = $reg->getElementsByTagName("ciiutamanoempresarial")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("ingresostamanoempresarial")->item(0)->textContent)) {
                        $retorno["ingresostamanoempresarial"] = $reg->getElementsByTagName("ingresostamanoempresarial")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("anodatostamanoempresarial")->item(0)->textContent)) {
                        $retorno["anodatostamanoempresarial"] = $reg->getElementsByTagName("anodatostamanoempresarial")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("fechadatostamanoempresarial")->item(0)->textContent)) {
                        $retorno["fechadatostamanoempresarial"] = $reg->getElementsByTagName("fechadatostamanoempresarial")->item(0)->textContent;
                    }


                    // ***************************************************************************** //
                    // informacion de Patrimonios de ESADL
                    // ***************************************************************************** //                
                    $retorno["patrimoniosesadl"] = array();
                    $caps = $reg->getElementsByTagName("pesadl");
                    if (!empty($caps)) {
                        $icap = 0;
                        foreach ($caps as $hcap) {
                            if (isset($hcap->getElementsByTagName("pesadl_anodat")->item(0)->textContent)) {
                                $icap++;
                                $retorno["patrimoniosesadl"][$icap]["anodatos"] = $hcap->getElementsByTagName("pesadl_anodat")->item(0)->textContent;
                                $retorno["patrimoniosesadl"][$icap]["fechadatos"] = $hcap->getElementsByTagName("pesadl_fecdat")->item(0)->textContent;
                                $retorno["patrimoniosesadl"][$icap]["patrimonio"] = doubleval($hcap->getElementsByTagName("pesadl_patrimonio")->item(0)->textContent);
                                $retorno["patrimonio"] = doubleval($hcap->getElementsByTagName("pesadl_patrimonio")->item(0)->textContent);
                            }
                        }
                    }

                    // ***************************************************************************** //
                    // informacion de Establecimientos de comercio asociados
                    // ***************************************************************************** //
                    $retorno["cntestab01"] = $reg->getElementsByTagName("cntestab01")->item(0)->textContent;
                    $retorno["cntestab02"] = $reg->getElementsByTagName("cntestab02")->item(0)->textContent;
                    $retorno["cntestab03"] = $reg->getElementsByTagName("cntestab03")->item(0)->textContent;
                    $retorno["cntestab04"] = $reg->getElementsByTagName("cntestab04")->item(0)->textContent;
                    $retorno["cntestab05"] = $reg->getElementsByTagName("cntestab05")->item(0)->textContent;
                    $retorno["cntestab06"] = $reg->getElementsByTagName("cntestab06")->item(0)->textContent;
                    $retorno["cntestab07"] = $reg->getElementsByTagName("cntestab07")->item(0)->textContent;
                    $retorno["cntestab08"] = $reg->getElementsByTagName("cntestab08")->item(0)->textContent;
                    $retorno["cntestab09"] = $reg->getElementsByTagName("cntestab09")->item(0)->textContent;
                    $retorno["cntestab10"] = $reg->getElementsByTagName("cntestab10")->item(0)->textContent;
                    $retorno["cntestab11"] = $reg->getElementsByTagName("cntestab11")->item(0)->textContent;

                    // ***************************************************************************** //
                    // informacion de referencias comerciales y bancarias
                    // ***************************************************************************** //
                    $retorno["refcrenom1"] = $reg->getElementsByTagName("refcrenom1")->item(0)->textContent;
                    $retorno["refcreofi1"] = $reg->getElementsByTagName("refcreofi1")->item(0)->textContent;
                    if (isset($reg->getElementsByTagName("refcretel1")->item(0)->textContent)) {
                        $retorno["refcretel1"] = $reg->getElementsByTagName("refcretel1")->item(0)->textContent;
                    } else {
                        $retorno["refcretel1"] = '';
                    }
                    $retorno["refcrenom2"] = $reg->getElementsByTagName("refcrenom2")->item(0)->textContent;
                    $retorno["refcreofi2"] = $reg->getElementsByTagName("refcreofi2")->item(0)->textContent;
                    if (isset($reg->getElementsByTagName("refcretel2")->item(0)->textContent)) {
                        $retorno["refcretel2"] = $reg->getElementsByTagName("refcretel2")->item(0)->textContent;
                    } else {
                        $retorno["refcretel2"] = '';
                    }
                    $retorno["refcomnom1"] = $reg->getElementsByTagName("refcomnom1")->item(0)->textContent;
                    $retorno["refcomdir1"] = $reg->getElementsByTagName("refcomdir1")->item(0)->textContent;
                    $retorno["refcomtel1"] = $reg->getElementsByTagName("refcomtel1")->item(0)->textContent;
                    $retorno["refcomnom2"] = $reg->getElementsByTagName("refcomnom2")->item(0)->textContent;
                    $retorno["refcomdir2"] = $reg->getElementsByTagName("refcomdir2")->item(0)->textContent;
                    $retorno["refcomtel2"] = $reg->getElementsByTagName("refcomtel2")->item(0)->textContent;

                    // ***************************************************************************** //
                    // informacion financiera anteriores (historicos)
                    // ***************************************************************************** //
                    $retorno["hf"] = array();
                    $ifin = 0;
                    $hfs = $reg->getElementsByTagName("hf");
                    if (!empty($hfs)) {
                        foreach ($hfs as $hfin) {
                            if (isset($hfin->getElementsByTagName("hf_anodatos")->item(0)->textContent)) {
                                $ifin++;
                                if (
                                    $retorno["organizacion"] == '01' ||
                                    ($retorno["organizacion"] > '02' && $retorno["categoria"] == '1')
                                ) {
                                    $retorno["hf"][$ifin]["anodatos"] = $hfin->getElementsByTagName("hf_anodatos")->item(0)->textContent;
                                    $retorno["hf"][$ifin]["feccredat"] = $hfin->getElementsByTagName("hf_feccredat")->item(0)->textContent;
                                    $retorno["hf"][$ifin]["fechadatos"] = $hfin->getElementsByTagName("hf_feccredat")->item(0)->textContent;
                                    $retorno["hf"][$ifin]["actcte"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_actcte")->item(0)->textContent));
                                    if (isset($hfin->getElementsByTagName("hf_actnocte")->item(0)->textContent)) {
                                        $retorno["hf"][$ifin]["actnocte"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_actnocte")->item(0)->textContent));
                                    } else {
                                        $retorno["f"][$iFin]["actnocte"] = 0;
                                    }
                                    $retorno["hf"][$ifin]["actfij"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_actfij")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["fijnet"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_fijnet")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["actval"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_actval")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["actotr"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_actotr")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["acttot"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_acttot")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["actsin"] = 0;
                                    $retorno["hf"][$ifin]["actsinaju"] = 0;
                                    $retorno["hf"][$ifin]["invent"] = 0;
                                    $retorno["hf"][$ifin]["pascte"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_pascte")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["paslar"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_paslar")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["pastot"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_pastot")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["patliq"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_patliq")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["pattot"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_patliq")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["patnet"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_patliq")->item(0)->textContent));
                                    if (isset($hfin->getElementsByTagName("hf_patnet")->item(0)->textContent)) {
                                        $retorno["hf"][$ifin]["patliq"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_patnet")->item(0)->textContent));
                                        $retorno["hf"][$ifin]["pattot"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_patnet")->item(0)->textContent));
                                        $retorno["hf"][$ifin]["patnet"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_patnet")->item(0)->textContent));
                                    }
                                    $retorno["hf"][$ifin]["paspat"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_paspat")->item(0)->textContent));
                                    if (isset($hfin->getElementsByTagName("hf_balsoc")->item(0)->textContent)) {
                                        $retorno["hf"][$ifin]["balsoc"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_balsoc")->item(0)->textContent));
                                    } else {
                                        $retorno["hf"][$iFin]["balsoc"] = 0;
                                    }
                                    $retorno["hf"][$ifin]["ingope"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_ingope")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["ingnoope"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_ingnoope")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["cosven"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_cosven")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["gasadm"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_gasadm")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["gtoadm"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_gasadm")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["gasope"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_gasope")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["gtoven"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_gasope")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["utiope"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_utiope")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["utinet"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_utinet")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["person"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_person")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["personal"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_person")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["pcttem"] = doubleval($hfin->getElementsByTagName("hf_pcttem")->item(0)->textContent);
                                    $retorno["hf"][$ifin]["personaltemp"] = doubleval($hfin->getElementsByTagName("hf_pcttem")->item(0)->textContent);
                                    $retorno["hf"][$ifin]["depamo"] = 0;
                                    if (isset($hfin->getElementsByTagName("hf_gasint")->item(0)->textContent)) {
                                        $retorno["hf"][$ifin]["gasint"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_gasint")->item(0)->textContent));
                                    } else {
                                        $retorno["hf"][$iFin]["gasint"] = 0;
                                    }
                                    if (isset($hfin->getElementsByTagName("hf_gasimp")->item(0)->textContent)) {
                                        $retorno["hf"][$ifin]["gasimp"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_gasimp")->item(0)->textContent));
                                    } else {
                                        $retorno["hf"][$iFin]["gasimp"] = 0;
                                    }
                                    $retorno["hf"][$ifin]["operacion"] = '';
                                } else {
                                    $retorno["hf"][$ifin]["anodatos"] = $hfin->getElementsByTagName("hf_anodatos")->item(0)->textContent;
                                    $retorno["hf"][$ifin]["feccredat"] = $hfin->getElementsByTagName("hf_feccredat")->item(0)->textContent;
                                    $retorno["hf"][$ifin]["fechadatos"] = $hfin->getElementsByTagName("hf_feccredat")->item(0)->textContent;
                                    $retorno["hf"][$ifin]["valest"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_valest")->item(0)->textContent));
                                    if (isset($hfin->getElementsByTagName("hf_actvin")->item(0)->textContent)) {
                                        $retorno["hf"][$ifin]["actvin"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_actvin")->item(0)->textContent));
                                    } else {
                                        $retorno["hf"][$ifin]["actvin"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_valest")->item(0)->textContent));
                                    }
                                    $retorno["hf"][$ifin]["person"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_person")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["personal"] = doubleval(\funcionesGenerales::convertirStringNumero($hfin->getElementsByTagName("hf_person")->item(0)->textContent));
                                    $retorno["hf"][$ifin]["pcttem"] = 0;
                                    $retorno["hf"][$ifin]["personaltemp"] = 0;
                                    $retorno["hf"][$ifin]["operacion"] = '';
                                }
                            }
                        }
                    }

                    // ***************************************************************************** //
                    // informacion financiera anteriores
                    // ***************************************************************************** //
                    $retorno["f"] = array();
                    $fins = $reg->getElementsByTagName("financieraanteriores");
                    foreach ($fins as $fin) {
                        $iFin = $fin->getElementsByTagName("anodatos")->item(0)->textContent;
                        if (trim($iFin) != '') {
                            $retorno["f"][$iFin]["anodatos"] = $fin->getElementsByTagName("anodatos")->item(0)->textContent;
                            $retorno["f"][$iFin]["fechadatos"] = $fin->getElementsByTagName("fechadatos")->item(0)->textContent;
                            $retorno["f"][$iFin]["personal"] = $fin->getElementsByTagName("personal")->item(0)->textContent;
                            $retorno["f"][$iFin]["personaltemp"] = $fin->getElementsByTagName("personaltemp")->item(0)->textContent;
                            $retorno["f"][$iFin]["actvin"] = $fin->getElementsByTagName("actvin")->item(0)->textContent;
                            $retorno["f"][$iFin]["actcte"] = $fin->getElementsByTagName("actcte")->item(0)->textContent;
                            if (isset($fin->getElementsByTagName("actnocte")->item(0)->textContent)) {
                                $retorno["f"][$iFin]["actnocte"] = $fin->getElementsByTagName("actnocte")->item(0)->textContent;
                            } else {
                                $retorno["f"][$iFin]["actnocte"] = 0;
                            }
                            $retorno["f"][$iFin]["actfij"] = $fin->getElementsByTagName("actfij")->item(0)->textContent;
                            if (isset($fin->getElementsByTagName("fijnet")->item(0)->textContent)) {
                                $retorno["f"][$iFin]["fijnet"] = $fin->getElementsByTagName("fijnet")->item(0)->textContent;
                            } else {
                                $retorno["f"][$iFin]["fijnet"] = 0;
                            }
                            $retorno["f"][$iFin]["actotr"] = $fin->getElementsByTagName("actotr")->item(0)->textContent;
                            $retorno["f"][$iFin]["actval"] = $fin->getElementsByTagName("actval")->item(0)->textContent;
                            $retorno["f"][$iFin]["acttot"] = $fin->getElementsByTagName("acttot")->item(0)->textContent;
                            $retorno["f"][$iFin]["actsinaju"] = 0;
                            $retorno["f"][$iFin]["invent"] = 0;
                            $retorno["f"][$iFin]["depamo"] = 0;
                            $retorno["f"][$iFin]["pascte"] = $fin->getElementsByTagName("pascte")->item(0)->textContent;
                            $retorno["f"][$iFin]["paslar"] = $fin->getElementsByTagName("paslar")->item(0)->textContent;
                            $retorno["f"][$iFin]["pastot"] = $fin->getElementsByTagName("pastot")->item(0)->textContent;
                            $retorno["f"][$iFin]["pattot"] = $fin->getElementsByTagName("pattot")->item(0)->textContent;
                            $retorno["f"][$iFin]["paspat"] = $fin->getElementsByTagName("paspat")->item(0)->textContent;
                            if (isset($fin->getElementsByTagName("balsoc")->item(0)->textContent)) {
                                $retorno["f"][$iFin]["balsoc"] = $fin->getElementsByTagName("balsoc")->item(0)->textContent;
                            } else {
                                $retorno["f"][$iFin]["balsoc"] = 0;
                            }
                            $retorno["f"][$iFin]["ingope"] = $fin->getElementsByTagName("ingope")->item(0)->textContent;
                            if (isset($fin->getElementsByTagName("ingnoope")->item(0)->textContent)) {
                                $retorno["f"][$iFin]["ingnoope"] = $fin->getElementsByTagName("ingnoope")->item(0)->textContent;
                            } else {
                                $retorno["f"][$iFin]["ingnoope"] = 0;
                            }
                            $retorno["f"][$iFin]["gtoven"] = $fin->getElementsByTagName("gtoven")->item(0)->textContent;
                            $retorno["f"][$iFin]["gtoadm"] = $fin->getElementsByTagName("gtoadm")->item(0)->textContent;
                            if (isset($fin->getElementsByTagName("cosven")->item(0)->textContent)) {
                                $retorno["f"][$iFin]["cosven"] = $fin->getElementsByTagName("cosven")->item(0)->textContent;
                            } else {
                                $retorno["f"][$iFin]["cosven"] = 0;
                            }
                            if (isset($fin->getElementsByTagName("gasope")->item(0)->textContent)) {
                                $retorno["f"][$iFin]["gasope"] = $fin->getElementsByTagName("gasope")->item(0)->textContent;
                            } else {
                                $retorno["f"][$iFin]["gasope"] = 0;
                            }
                            if (isset($fin->getElementsByTagName("gasnoope")->item(0)->textContent)) {
                                $retorno["f"][$iFin]["gasnoope"] = $fin->getElementsByTagName("gasnoope")->item(0)->textContent;
                            } else {
                                $retorno["f"][$iFin]["gasnoope"] = 0;
                            }
                            if (isset($fin->getElementsByTagName("gasint")->item(0)->textContent)) {
                                $retorno["f"][$iFin]["gasint"] = $fin->getElementsByTagName("gasint")->item(0)->textContent;
                            } else {
                                $retorno["f"][$iFin]["gasint"] = 0;
                            }
                            if (isset($fin->getElementsByTagName("gasimp")->item(0)->textContent)) {
                                $retorno["f"][$iFin]["gasimp"] = $fin->getElementsByTagName("gasimp")->item(0)->textContent;
                            } else {
                                $retorno["f"][$iFin]["gasimp"] = 0;
                            }
                            $retorno["f"][$iFin]["utiope"] = $fin->getElementsByTagName("utiope")->item(0)->textContent;
                            $retorno["f"][$iFin]["utinet"] = $fin->getElementsByTagName("utinet")->item(0)->textContent;
                        }
                    }


                    if (isset($reg->getElementsByTagName("celcom")->item(0)->textContent)) {
                        $retorno["celcom"] = $reg->getElementsByTagName("celcom")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("celnot")->item(0)->textContent)) {
                        $retorno["celnot"] = $reg->getElementsByTagName("celnot")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("ctrmen")->item(0)->textContent)) {
                        $retorno["ctrmen"] = $reg->getElementsByTagName("ctrmen")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("ctrmennot")->item(0)->textContent)) {
                        $retorno["ctrmennot"] = $reg->getElementsByTagName("ctrmennot")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("ctrubi")->item(0)->textContent)) {
                        $retorno["ctrubi"] = $reg->getElementsByTagName("ctrubi")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("ctrfun")->item(0)->textContent)) {
                        $retorno["ctrfun"] = $reg->getElementsByTagName("ctrfun")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("art4")->item(0)->textContent)) {
                        $retorno["art4"] = $reg->getElementsByTagName("art4")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("art7")->item(0)->textContent)) {
                        $retorno["art7"] = $reg->getElementsByTagName("art7")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("art50")->item(0)->textContent)) {
                        $retorno["art50"] = $reg->getElementsByTagName("art50")->item(0)->textContent;
                    }

                    if (isset($reg->getElementsByTagName("benley1780")->item(0)->textContent)) {
                        $retorno["benley1780"] = $reg->getElementsByTagName("benley1780")->item(0)->textContent;
                    }

                    if (isset($reg->getElementsByTagName("matriculaanterior")->item(0)->textContent)) {
                        $retorno["matriculaanterior"] = ltrim($reg->getElementsByTagName("matriculaanterior")->item(0)->textContent . "0");
                    }

                    if (isset($reg->getElementsByTagName("ctrcancelacion1429")->item(0)->textContent)) {
                        $retorno["ctrcancelacion1429"] = $reg->getElementsByTagName("ctrcancelacion1429")->item(0)->textContent;
                    }

                    if (isset($reg->getElementsByTagName("ctrdepuracion1727")->item(0)->textContent)) {
                        $retorno["ctrdepuracion1727"] = $reg->getElementsByTagName("ctrdepuracion1727")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("ctrfechadepuracion1727")->item(0)->textContent)) {
                        $retorno["ctrfechadepuracion1727"] = $reg->getElementsByTagName("ctrfechadepuracion1727")->item(0)->textContent;
                    }

                    if (isset($reg->getElementsByTagName("ctrben658")->item(0)->textContent)) {
                        $retorno["ctrben658"] = $reg->getElementsByTagName("ctrben658")->item(0)->textContent;
                    }

                    if (isset($reg->getElementsByTagName("personaltemp")->item(0)->textContent)) {
                        $retorno["personaltemp"] = $reg->getElementsByTagName("personaltemp")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("ivcenvio")->item(0)->textContent)) {
                        $retorno["ivcenvio"] = $reg->getElementsByTagName("ivcenvio")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("ivcsuelos")->item(0)->textContent)) {
                        $retorno["ivcsuelos"] = $reg->getElementsByTagName("ivcsuelos")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("ivcarea")->item(0)->textContent)) {
                        $retorno["ivcarea"] = $reg->getElementsByTagName("ivcarea")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("ivcver")->item(0)->textContent)) {
                        $retorno["ivcver"] = $reg->getElementsByTagName("ivcver")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("ivccretip")->item(0)->textContent)) {
                        $retorno["ivccretip"] = $reg->getElementsByTagName("ivccretip")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("ivcali")->item(0)->textContent)) {
                        $retorno["ivcali"] = $reg->getElementsByTagName("ivcali")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("ivcqui")->item(0)->textContent)) {
                        $retorno["ivcqui"] = $reg->getElementsByTagName("ivcqui")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("ivcriesgo")->item(0)->textContent)) {
                        $retorno["ivcriesgo"] = $reg->getElementsByTagName("ivcriesgo")->item(0)->textContent;
                    }

                    // ***************************************************************************** //
                    // Datos de bienes que posee la empresa
                    // ***************************************************************************** //
                    $retorno["bienes"] = array();
                    $i = 0;
                    $bies = $reg->getElementsByTagName("bien");
                    if (!empty($bies)) {
                        foreach ($bies as $bie) {
                            $i++;
                            $retorno["bienes"][$i]["matinmo"] = $bie->getElementsByTagName("matinmo")->item(0)->textContent;
                            $retorno["bienes"][$i]["dir"] = str_replace("#", "Nro.", $bie->getElementsByTagName("dir")->item(0)->textContent);
                            $retorno["bienes"][$i]["barrio"] = $bie->getElementsByTagName("barrio")->item(0)->textContent;
                            $retorno["bienes"][$i]["muni"] = $bie->getElementsByTagName("muni")->item(0)->textContent;
                            $retorno["bienes"][$i]["dpto"] = $bie->getElementsByTagName("dpto")->item(0)->textContent;
                            $retorno["bienes"][$i]["pais"] = $bie->getElementsByTagName("pais")->item(0)->textContent;
                        }
                    }
                    unset($bies);
                    unset($bie);

                    // ***************************************************************************** //
                    // Representacion legal y administracion 
                    // ***************************************************************************** //
                    $retorno["replegal"] = array();
                    $i = 0;
                    $reps = $reg->getElementsByTagName("representantelegal");
                    if (!empty($reps)) {
                        foreach ($reps as $rep) {
                            $i++;
                            $retorno["replegal"][$i]["idtipoidentificacionreplegal"] = $rep->getElementsByTagName("idtipoidentificacionreplegal")->item(0)->textContent;
                            $retorno["replegal"][$i]["identificacionreplegal"] = ltrim((string)$rep->getElementsByTagName("identificacionreplegal")->item(0)->textContent, "0");
                            $retorno["replegal"][$i]["nombrereplegal"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$rep->getElementsByTagName("nombrereplegal")->item(0)->textContent)));
                            if (isset($rep->getElementsByTagName("nombre1replegal")->item(0)->textContent)) $retorno["replegal"][$i]["nombre1replegal"] = \funcionesGenerales::restaurarEspeciales(trim((string)$rep->getElementsByTagName("nombre1replegal")->item(0)->textContent));
                            if (isset($rep->getElementsByTagName("nombre1replegal")->item(0)->textContent)) $retorno["replegal"][$i]["nombre2replegal"] = \funcionesGenerales::restaurarEspeciales(trim((string)$rep->getElementsByTagName("nombre2replegal")->item(0)->textContent));
                            if (isset($rep->getElementsByTagName("apellido1replegal")->item(0)->textContent)) $retorno["replegal"][$i]["apellido1replegal"] = \funcionesGenerales::restaurarEspeciales(trim((string)$rep->getElementsByTagName("apellido1replegal")->item(0)->textContent));
                            if (isset($rep->getElementsByTagName("apellido2replegal")->item(0)->textContent)) $retorno["replegal"][$i]["apellido2replegal"] = \funcionesGenerales::restaurarEspeciales(trim((string)$rep->getElementsByTagName("apellido2replegal")->item(0)->textContent));
                            $retorno["replegal"][$i]["emailreplegal"] = '';
                            $retorno["replegal"][$i]["celularreplegal"] = '';
                            if (isset($rep->getElementsByTagName("emailreplegal")->item(0)->textContent)) $retorno["replegal"][$i]["emailreplegal"] = \funcionesGenerales::restaurarEspeciales(trim($rep->getElementsByTagName("emailreplegal")->item(0)->textContent));
                            if (isset($rep->getElementsByTagName("celularreplegal")->item(0)->textContent)) $retorno["replegal"][$i]["celularreplegal"] = \funcionesGenerales::restaurarEspeciales(trim($rep->getElementsByTagName("emailreplegal")->item(0)->textContent));
                            if (isset($rep->getElementsByTagName("cargoreplegal")->item(0)->textContent)) $retorno["replegal"][$i]["cargoreplegal"] = \funcionesGenerales::restaurarEspeciales(trim((string)$rep->getElementsByTagName("cargoreplegal")->item(0)->textContent));
                            if (isset($rep->getElementsByTagName("vinculoreplegal")->item(0)->textContent)) $retorno["replegal"][$i]["vinculoreplegal"] = \funcionesGenerales::restaurarEspeciales(trim((string)$rep->getElementsByTagName("vinculoreplegal")->item(0)->textContent));
                            if (isset($rep->getElementsByTagName("libroreplegal")->item(0)->textContent)) $retorno["replegal"][$i]["libroreplegal"] = \funcionesGenerales::restaurarEspeciales(trim((string)$rep->getElementsByTagName("libroreplegal")->item(0)->textContent));
                            if (isset($rep->getElementsByTagName("inscripcionreplegal")->item(0)->textContent)) $retorno["replegal"][$i]["inscripcionreplegal"] = \funcionesGenerales::restaurarEspeciales(trim((string)$rep->getElementsByTagName("inscripcionreplegal")->item(0)->textContent));
                            if (isset($rep->getElementsByTagName("fechareplegal")->item(0)->textContent)) $retorno["replegal"][$i]["fechareplegal"] = \funcionesGenerales::restaurarEspeciales(trim((string)$rep->getElementsByTagName("fechareplegal")->item(0)->textContent));
                        }
                    }

                    unset($reps);
                    unset($rep);

                    // ***************************************************************************** //
                    // Socios, juntas directivas y organos directivos.
                    // ***************************************************************************** //
                    $retorno["vinculos"] = array();
                    $i = 0;
                    $socs = $reg->getElementsByTagName("vinculo");
                    if (!empty($socs)) {
                        foreach ($socs as $soc) {
                            if (isset($soc->getElementsByTagName("idtipoidentificacionotros")->item(0)->textContent)) {
                                $i++;
                                $retorno["vinculos"][$i]["idtipoidentificacionotros"] = $soc->getElementsByTagName("idtipoidentificacionotros")->item(0)->textContent;
                                $retorno["vinculos"][$i]["identificacionotros"] = ltrim((string)$soc->getElementsByTagName("identificacionotros")->item(0)->textContent, "0");
                                $retorno["vinculos"][$i]["nombreotros"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("nombreotros")->item(0)->textContent));
                                $retorno["vinculos"][$i]["apellido1otros"] = '';
                                $retorno["vinculos"][$i]["apellido2otros"] = '';
                                $retorno["vinculos"][$i]["nombre1otros"] = '';
                                $retorno["vinculos"][$i]["nombre2otros"] = '';
                                $retorno["vinculos"][$i]["emailotros"] = '';
                                $retorno["vinculos"][$i]["celularotros"] = '';
                                $retorno["vinculos"][$i]["ciiu1"] = '';
                                $retorno["vinculos"][$i]["ciiu2"] = '';
                                $retorno["vinculos"][$i]["ciiu3"] = '';
                                $retorno["vinculos"][$i]["ciiu4"] = '';
                                $retorno["vinculos"][$i]["tipositcontrol"] = '';

                                if (isset($soc->getElementsByTagName("apellido1otros")->item(0)->textContent)) $retorno["vinculos"][$i]["apellido1otros"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("apellido1otros")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("apellido2otros")->item(0)->textContent)) $retorno["vinculos"][$i]["apellido2otros"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("apellido2otros")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("nombre1otros")->item(0)->textContent)) $retorno["vinculos"][$i]["nombre1otros"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("nombre1otros")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("nombre2otros")->item(0)->textContent)) $retorno["vinculos"][$i]["nombre2otros"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("nombre2otros")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("emailotros")->item(0)->textContent)) $retorno["vinculos"][$i]["emailotros"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("emailotros")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("celularotros")->item(0)->textContent)) $retorno["vinculos"][$i]["celularotros"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("celularotros")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("idcarotros")->item(0)->textContent)) $retorno["vinculos"][$i]["idcargootros"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("idcarotros")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("cargootros")->item(0)->textContent)) $retorno["vinculos"][$i]["cargootros"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("cargootros")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("vinculootros")->item(0)->textContent)) $retorno["vinculos"][$i]["vinculootros"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("vinculootros")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("cargootros")->item(0)->textContent)) $retorno["vinculos"][$i]["cargootros"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("cargootros")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("numtarpotros")->item(0)->textContent)) $retorno["vinculos"][$i]["numtarprofotros"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("numtarpotros")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("idclaseemp")->item(0)->textContent)) $retorno["vinculos"][$i]["idclaseemp"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("idclaseemp")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("numidemp")->item(0)->textContent)) $retorno["vinculos"][$i]["numidemp"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("numidemp")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("idvindian")->item(0)->textContent)) $retorno["vinculos"][$i]["idvindian"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("idvindian")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("cuotasconst")->item(0)->textContent)) $retorno["vinculos"][$i]["cuotasconst"] = doubleval($soc->getElementsByTagName("cuotasconst")->item(0)->textContent);
                                if (isset($soc->getElementsByTagName("cuotasref")->item(0)->textContent)) $retorno["vinculos"][$i]["cuotasref"] = doubleval($soc->getElementsByTagName("cuotasref")->item(0)->textContent);
                                if (isset($soc->getElementsByTagName("valorconst")->item(0)->textContent)) $retorno["vinculos"][$i]["valorconst"] = doubleval($soc->getElementsByTagName("valorconst")->item(0)->textContent);
                                if (isset($soc->getElementsByTagName("valorref")->item(0)->textContent)) $retorno["vinculos"][$i]["valorref"] = doubleval($soc->getElementsByTagName("valorref")->item(0)->textContent);
                                if (isset($soc->getElementsByTagName("va1")->item(0)->textContent)) $retorno["vinculos"][$i]["va1"] = doubleval($soc->getElementsByTagName("va1")->item(0)->textContent);
                                if (isset($soc->getElementsByTagName("va2")->item(0)->textContent)) $retorno["vinculos"][$i]["va2"] = doubleval($soc->getElementsByTagName("va2")->item(0)->textContent);
                                if (isset($soc->getElementsByTagName("va3")->item(0)->textContent)) $retorno["vinculos"][$i]["va3"] = doubleval($soc->getElementsByTagName("va3")->item(0)->textContent);
                                if (isset($soc->getElementsByTagName("va4")->item(0)->textContent)) $retorno["vinculos"][$i]["va4"] = doubleval($soc->getElementsByTagName("va4")->item(0)->textContent);
                                if (isset($soc->getElementsByTagName("va5")->item(0)->textContent)) $retorno["vinculos"][$i]["va5"] = doubleval($soc->getElementsByTagName("va5")->item(0)->textContent);
                                if (isset($soc->getElementsByTagName("va6")->item(0)->textContent)) $retorno["vinculos"][$i]["va6"] = doubleval($soc->getElementsByTagName("va6")->item(0)->textContent);
                                if (isset($soc->getElementsByTagName("va7")->item(0)->textContent)) $retorno["vinculos"][$i]["va7"] = doubleval($soc->getElementsByTagName("va7")->item(0)->textContent);
                                if (isset($soc->getElementsByTagName("va8")->item(0)->textContent)) $retorno["vinculos"][$i]["va8"] = doubleval($soc->getElementsByTagName("va8")->item(0)->textContent);
                                if (isset($soc->getElementsByTagName("librootros")->item(0)->textContent)) $retorno["vinculos"][$i]["librootros"] = restaurarEspeciales(trim($soc->getElementsByTagName("librootros")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("inscripcionotros")->item(0)->textContent)) $retorno["vinculos"][$i]["inscripcionotros"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("inscripcionotros")->item(0)->textContent));
                                $retorno["vinculos"][$i]["dupliotros"] = '';
                                if (isset($soc->getElementsByTagName("dupliotros")->item(0)->textContent)) $retorno["vinculos"][$i]["dupliotros"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("dupliotros")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("fechaotros")->item(0)->textContent)) $retorno["vinculos"][$i]["fechaotros"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("fechaotros")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("ciiu1")->item(0)->textContent)) $retorno["vinculos"][$i]["ciiu1"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("ciiu1")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("ciiu2")->item(0)->textContent)) $retorno["vinculos"][$i]["ciiu2"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("ciiu2")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("ciiu3")->item(0)->textContent)) $retorno["vinculos"][$i]["ciiu3"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("ciiu3")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("ciiu4")->item(0)->textContent)) $retorno["vinculos"][$i]["ciiu4"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("ciiu4")->item(0)->textContent));
                                if (isset($soc->getElementsByTagName("tipositcontrol")->item(0)->textContent)) $retorno["vinculos"][$i]["tipositcontrol"] = \funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("tipositcontrol")->item(0)->textContent));
                                if (trim((string)$retorno["vinculos"][$i]["ciiu1"]) != '') {
                                    if ((substr($retorno["vinculos"][$i]["ciiu1"], 0, 1) >= '0') && (substr($retorno["vinculos"][$i]["ciiu1"], 0, 1) <= '9')) {
                                        $retorno["vinculos"][$i]["ciiu1"] = \funcionesGenerales::localizarLetraCiiu($mysqli, $retorno["vinculos"][$i]["ciiu1"]) . $retorno["vinculos"][$i]["ciiu1"];
                                    }
                                }
                                if (trim((string)$retorno["vinculos"][$i]["ciiu2"]) != '') {
                                    if ((substr($retorno["vinculos"][$i]["ciiu2"], 0, 1) >= '0') && (substr($retorno["vinculos"][$i]["ciiu2"], 0, 1) <= '9')) {
                                        $retorno["vinculos"][$i]["ciiu2"] = \funcionesGenerales::localizarLetraCiiu($mysqli, $retorno["vinculos"][$i]["ciiu2"]) . $retorno["vinculos"][$i]["ciiu2"];
                                    }
                                }
                                if (trim((string)$retorno["vinculos"][$i]["ciiu3"]) != '') {
                                    if ((substr($retorno["vinculos"][$i]["ciiu3"], 0, 1) >= '0') && (substr($retorno["vinculos"][$i]["ciiu3"], 0, 1) <= '9')) {
                                        $retorno["vinculos"][$i]["ciiu3"] = \funcionesGenerales::localizarLetraCiiu($mysqli, $retorno["vinculos"][$i]["ciiu3"]) . $retorno["vinculos"][$i]["ciiu3"];
                                    }
                                }
                                if (trim((string)$retorno["vinculos"][$i]["ciiu4"]) != '') {
                                    if ((substr($retorno["vinculos"][$i]["ciiu4"], 0, 1) >= '0') && (substr($retorno["vinculos"][$i]["ciiu4"], 0, 1) <= '9')) {
                                        $retorno["vinculos"][$i]["ciiu4"] = \funcionesGenerales::localizarLetraCiiu($mysqli, $retorno["vinculos"][$i]["ciiu4"]) . $retorno["vinculos"][$i]["ciiu4"];
                                    }
                                }
                            }
                        }
                    }
                    unset($socs);
                    unset($soc);

                    // ***************************************************************************** //
                    // Socios, juntas directivas y organos directivos - historicos
                    // ***************************************************************************** //
                    $retorno["vinculosh"] = array();
                    $i = 0;
                    $socs = $reg->getElementsByTagName("vinculoh");
                    if (!empty($socs)) {
                        foreach ($socs as $soc) {
                            if (isset($soc->getElementsByTagName("idtipoidentificacionotros")->item(0)->textContent)) {
                                $i++;
                                $retorno["vinculosh"][$i]["idtipoidentificacionotros"] = $soc->getElementsByTagName("idtipoidentificacionotros")->item(0)->textContent;
                                $retorno["vinculosh"][$i]["identificacionotros"] = ltrim((string)$soc->getElementsByTagName("identificacionotros")->item(0)->textContent, "0");
                                $retorno["vinculosh"][$i]["nombreotros"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("nombreotros")->item(0)->textContent)));
                                $retorno["vinculosh"][$i]["apellido1otros"] = '';
                                $retorno["vinculosh"][$i]["apellido2otros"] = '';
                                $retorno["vinculosh"][$i]["nombre1otros"] = '';
                                $retorno["vinculosh"][$i]["nombre2otros"] = '';
                                $retorno["vinculosh"][$i]["ciiu1"] = '';
                                $retorno["vinculosh"][$i]["ciiu2"] = '';
                                $retorno["vinculosh"][$i]["ciiu3"] = '';
                                $retorno["vinculosh"][$i]["ciiu4"] = '';
                                $retorno["vinculosh"][$i]["tipositcontrol"] = '';

                                if (isset($soc->getElementsByTagName("apellido1otros")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["apellido1otros"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("apellido1otros")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("apellido2otros")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["apellido2otros"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("apellido2otros")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("nombre1otros")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["nombre1otros"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("nombre1otros")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("nombre2otros")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["nombre2otros"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("nombre2otros")->item(0)->textContent)));
                                }

                                $retorno["vinculosh"][$i]["emailotros"] = '';
                                $retorno["vinculosh"][$i]["celularotros"] = '';
                                if (isset($soc->getElementsByTagName("emailotros")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["emailotros"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("emailotros")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("celularotros")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["celularotros"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("celularotros")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("idcarotros")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["idcargootros"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("idcarotros")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("cargootros")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["cargootros"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("cargootros")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("vinculootros")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["vinculootros"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("vinculootros")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("cargootros")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["cargootros"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("cargootros")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("numtarpotros")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["numtarprofotros"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("numtarpotros")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("idclaseemp")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["idclaseemp"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("idclaseemp")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("numidemp")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["numidemp"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("numidemp")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("idvindian")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["idvindian"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("idvindian")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("cuotasconst")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["cuotasconst"] = doubleval($soc->getElementsByTagName("cuotasconst")->item(0)->textContent);
                                }
                                if (isset($soc->getElementsByTagName("cuotasref")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["cuotasref"] = doubleval($soc->getElementsByTagName("cuotasref")->item(0)->textContent);
                                }
                                if (isset($soc->getElementsByTagName("valorconst")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["valorconst"] = doubleval($soc->getElementsByTagName("valorconst")->item(0)->textContent);
                                }
                                if (isset($soc->getElementsByTagName("valorref")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["valorref"] = doubleval($soc->getElementsByTagName("valorref")->item(0)->textContent);
                                }
                                if (isset($soc->getElementsByTagName("va1")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["va1"] = doubleval($soc->getElementsByTagName("va1")->item(0)->textContent);
                                }
                                if (isset($soc->getElementsByTagName("va2")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["va2"] = doubleval($soc->getElementsByTagName("va2")->item(0)->textContent);
                                }
                                if (isset($soc->getElementsByTagName("va3")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["va3"] = doubleval($soc->getElementsByTagName("va3")->item(0)->textContent);
                                }
                                if (isset($soc->getElementsByTagName("va4")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["va4"] = doubleval($soc->getElementsByTagName("va4")->item(0)->textContent);
                                }
                                if (isset($soc->getElementsByTagName("va5")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["va5"] = doubleval($soc->getElementsByTagName("va5")->item(0)->textContent);
                                }
                                if (isset($soc->getElementsByTagName("va6")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["va6"] = doubleval($soc->getElementsByTagName("va6")->item(0)->textContent);
                                }
                                if (isset($soc->getElementsByTagName("va7")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["va7"] = doubleval($soc->getElementsByTagName("va7")->item(0)->textContent);
                                }
                                if (isset($soc->getElementsByTagName("va8")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["va8"] = doubleval($soc->getElementsByTagName("va8")->item(0)->textContent);
                                }
                                if (isset($soc->getElementsByTagName("librootros")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["librootros"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("librootros")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("inscripcionotros")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["inscripcionotros"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("inscripcionotros")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("dupliotros")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["dupliotros"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("dupliotros")->item(0)->textContent)));
                                }

                                if (isset($soc->getElementsByTagName("fechaotros")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["fechaotros"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("fechaotros")->item(0)->textContent)));
                                }

                                if (isset($soc->getElementsByTagName("ciiu1")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["ciiu1"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("ciiu1")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("ciiu2")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["ciiu2"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("ciiu2")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("ciiu3")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["ciiu3"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("ciiu3")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("ciiu4")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["ciiu4"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("ciiu4")->item(0)->textContent)));
                                }
                                if (isset($soc->getElementsByTagName("tipositcontrol")->item(0)->textContent)) {
                                    $retorno["vinculosh"][$i]["tipositcontrol"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("tipositcontrol")->item(0)->textContent)));
                                }

                                if (trim((string)$retorno["vinculosh"][$i]["ciiu1"]) != '') {
                                    if ((substr($retorno["vinculosh"][$i]["ciiu1"], 0, 1) >= '0') && (substr($retorno["vinculosh"][$i]["ciiu1"], 0, 1) <= '9')) {
                                        $retorno["vinculosh"][$i]["ciiu1"] = \funcionesGenerales::localizarLetraCiiu($mysqli, $retorno["vinculosh"][$i]["ciiu1"]) . $retorno["vinculosh"][$i]["ciiu1"];
                                    }
                                }
                                if (trim((string)$retorno["vinculosh"][$i]["ciiu2"]) != '') {
                                    if ((substr($retorno["vinculosh"][$i]["ciiu2"], 0, 1) >= '0') && (substr($retorno["vinculosh"][$i]["ciiu2"], 0, 1) <= '9')) {
                                        $retorno["vinculosh"][$i]["ciiu2"] = \funcionesGenerales::localizarLetraCiiu($mysqli, $retorno["vinculosh"][$i]["ciiu2"]) . $retorno["vinculosh"][$i]["ciiu2"];
                                    }
                                }
                                if (trim((string)$retorno["vinculosh"][$i]["ciiu3"]) != '') {
                                    if ((substr($retorno["vinculosh"][$i]["ciiu3"], 0, 1) >= '0') && (substr($retorno["vinculosh"][$i]["ciiu3"], 0, 1) <= '9')) {
                                        $retorno["vinculosh"][$i]["ciiu3"] = \funcionesGenerales::localizarLetraCiiu($mysqli, $retorno["vinculosh"][$i]["ciiu3"]) . $retorno["vinculosh"][$i]["ciiu3"];
                                    }
                                }
                                if (trim((string)$retorno["vinculosh"][$i]["ciiu4"]) != '') {
                                    if ((substr($retorno["vinculosh"][$i]["ciiu4"], 0, 1) >= '0') && (substr($retorno["vinculosh"][$i]["ciiu4"], 0, 1) <= '9')) {
                                        $retorno["vinculosh"][$i]["ciiu4"] = \funcionesGenerales::localizarLetraCiiu($mysqli, $retorno["vinculosh"][$i]["ciiu4"]) . $retorno["vinculosh"][$i]["ciiu4"];
                                    }
                                }
                            }
                        }
                    }
                    unset($socs);
                    unset($soc);

                    // ***************************************************************************** //
                    // Vinculos propietarios - de la sociedad propietaria
                    // ***************************************************************************** //
                    $retorno["vincuprop"] = array();
                    $i = 0;
                    $socs = $reg->getElementsByTagName("vp");
                    if (!empty($socs)) {
                        foreach ($socs as $soc) {
                            if (isset($soc->getElementsByTagName("vpidclase")->item(0)->textContent)) {
                                $i++;
                                $retorno["vincuprop"][$i]["idclase"] = $soc->getElementsByTagName("vpidclase")->item(0)->textContent;
                                $retorno["vincuprop"][$i]["numid"] = ltrim((string)$soc->getElementsByTagName("vpnumid")->item(0)->textContent, "0");
                                $retorno["vincuprop"][$i]["nombre"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("vpnombre")->item(0)->textContent)));
                                $retorno["vincuprop"][$i]["vinculo"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("vpvinculo")->item(0)->textContent)));
                                $retorno["vincuprop"][$i]["cargo"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$soc->getElementsByTagName("vpcargo")->item(0)->textContent)));
                            }
                        }
                    }
                    unset($socs);
                    unset($soc);

                    // ***************************************************************************** //
                    // Administrador
                    // ***************************************************************************** //
                    $retorno["idtipoidentificacionadministrador"] = $reg->getElementsByTagName("idtipoidentificacionadministrador")->item(0)->textContent;
                    $retorno["identificacionadministrador"] = $reg->getElementsByTagName("identificacionadministrador")->item(0)->textContent;
                    $retorno["nombreadministrador"] = (\funcionesGenerales::restaurarEspeciales($reg->getElementsByTagName("nombreadministrador")->item(0)->textContent));

                    // ***************************************************************************** //
                    // propietarios
                    // ***************************************************************************** //
                    $retorno["propietarios"] = array();
                    $i = 0;
                    $j = 0;
                    $props = $reg->getElementsByTagName("propietario");
                    if (count($props) > 0) {
                        foreach ($props as $prop) {
                            $j++;
                            $incluir = 'si';
                            if ($j > 1) {
                                for ($x = 1; $x <= $i; $x++) {
                                    if ($retorno["propietarios"][$x]["identificacionpropietario"] == ltrim((string)$prop->getElementsByTagName("identificacionpropietario")->item(0)->textContent, '0')) {
                                        $incluir = 'no';
                                    }
                                }
                            }
                            if ($incluir == 'si') {
                                $i++;
                                $retorno["propietarios"][$i]["camarapropietario"] = $prop->getElementsByTagName("camarapropietario")->item(0)->textContent;
                                $retorno["propietarios"][$i]["matriculapropietario"] = ltrim((string)$prop->getElementsByTagName("matriculapropietario")->item(0)->textContent, '0');
                                $retorno["propietarios"][$i]["idtipoidentificacionpropietario"] = trim((string)$prop->getElementsByTagName("idtipoidentificacionpropietario")->item(0)->textContent);
                                $retorno["propietarios"][$i]["identificacionpropietario"] = ltrim((string)$prop->getElementsByTagName("identificacionpropietario")->item(0)->textContent, '0');
                                $retorno["propietarios"][$i]["nitpropietario"] = ltrim((string)$prop->getElementsByTagName("nitpropietario")->item(0)->textContent, '0');
                                $retorno["propietarios"][$i]["nombrepropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("nombrepropietario")->item(0)->textContent));

                                //
                                (!isset($prop->getElementsByTagName("nom1propietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["nom1propietario"] = '' : $retorno["propietarios"][$i]["nom1propietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("nom1propietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("nom2propietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["nom2propietario"] = '' : $retorno["propietarios"][$i]["nom2propietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("nom2propietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("ape1propietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["ape1propietario"] = '' : $retorno["propietarios"][$i]["ape1propietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("ape1propietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("ape2propietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["ape2propietario"] = '' : $retorno["propietarios"][$i]["ape2propietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("ape2propietario")->item(0)->textContent));

                                //
                                (!isset($prop->getElementsByTagName("tipopropiedad")->item(0)->textContent)) ? $retorno["propietarios"][$i]["tipopropiedad"] = '0' : $retorno["propietarios"][$i]["tipopropiedad"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("tipopropiedad")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("direccionpropietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["direccionpropietario"] = '' : $retorno["propietarios"][$i]["direccionpropietario"] = (\funcionesGenerales::restaurarEspeciales(str_replace("#", "Nro.", $prop->getElementsByTagName("direccionpropietario")->item(0)->textContent)));
                                (!isset($prop->getElementsByTagName("municipiopropietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["municipiopropietario"] = '' : $retorno["propietarios"][$i]["municipiopropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("municipiopropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("direccionnotpropietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["direccionnotpropietario"] = '' : $retorno["propietarios"][$i]["direccionnotpropietario"] = (\funcionesGenerales::restaurarEspeciales(str_replace("#", "Nro.", $prop->getElementsByTagName("direccionnotpropietario")->item(0)->textContent)));
                                (!isset($prop->getElementsByTagName("municipionotpropietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["municipionotpropietario"] = '' : $retorno["propietarios"][$i]["municipionotpropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("municipionotpropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("telefonopropietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["telefonopropietario"] = '' : $retorno["propietarios"][$i]["telefonopropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("telefonopropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("telefono2propietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["telefono2propietario"] = '' : $retorno["propietarios"][$i]["telefono2propietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("telefono2propietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("celularpropietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["celularpropietario"] = '' : $retorno["propietarios"][$i]["celularpropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("celularpropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("nomreplegpropietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["nomreplegpropietario"] = '' : $retorno["propietarios"][$i]["nomreplegpropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("nomreplegpropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("numidreplegpropietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["numidreplegpropietario"] = '' : $retorno["propietarios"][$i]["numidreplegpropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("numidreplegpropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("tipoidreplegpropietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["tipoidreplegpropietario"] = '' : $retorno["propietarios"][$i]["tipoidreplegpropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("tipoidreplegpropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("fecmatripropietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["fecmatripropietario"] = '' : $retorno["propietarios"][$i]["fecmatripropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("fecmatripropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("fecrenovpropietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["fecrenovpropietario"] = '' : $retorno["propietarios"][$i]["fecrenovpropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("fecrenovpropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("ultanorenpropietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["ultanorenpropietario"] = '' : $retorno["propietarios"][$i]["ultanorenpropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("ultanorenpropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("organizacionpropietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["organizacionpropietario"] = '' : $retorno["propietarios"][$i]["organizacionpropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("organizacionpropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("estadodatospropietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["estadodatospropietario"] = '' : $retorno["propietarios"][$i]["estadodatospropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("estadodatospropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("estadomatriculapropietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["estadomatriculapropietario"] = '' : $retorno["propietarios"][$i]["estadomatriculapropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("estadomatriculapropietario")->item(0)->textContent));

                                //
                                (!isset($prop->getElementsByTagName("afiliacionpropietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["afiliacionpropietario"] = '' : $retorno["propietarios"][$i]["afiliacionpropietario"] = $prop->getElementsByTagName("afiliacionpropietario")->item(0)->textContent;
                                (!isset($prop->getElementsByTagName("ultanorenafipropietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["ultanorenafipropietario"] = '' : $retorno["propietarios"][$i]["ultanorenafipropietario"] = $prop->getElementsByTagName("ultanorenafipropietario")->item(0)->textContent;
                                (!isset($prop->getElementsByTagName("saldoafiliadopropietario")->item(0)->textContent)) ? $retorno["propietarios"][$i]["saldoafiliadopropietario"] = 0 : $retorno["propietarios"][$i]["saldoafiliadopropietario"] = doubleval($prop->getElementsByTagName("saldoafiliadopropietario")->item(0)->textContent);
                            }
                        }
                    } else {
                        $retorno["propietarios"] = array();
                    }
                    unset($props);

                    // ***************************************************************************** //
                    // propietarios historicos
                    // ***************************************************************************** //
                    $retorno["propietariosh"] = array();
                    $i = 0;
                    $j = 0;
                    $props = $reg->getElementsByTagName("propietarioh");
                    if (count($props) > 0) {
                        foreach ($props as $prop) {
                            $j++;
                            $incluir = 'si';
                            if ($j > 1) {
                                for ($x = 1; $x <= $i; $x++) {
                                    if ($retorno["propietariosh"][$x]["identificacionpropietario"] == ltrim($prop->getElementsByTagName("identificacionpropietario")->item(0)->textContent, '0')) {
                                        $incluir = 'no';
                                    }
                                }
                            }
                            if ($incluir == 'si') {
                                $i++;
                                $retorno["propietariosh"][$i]["camarapropietario"] = $prop->getElementsByTagName("camarapropietario")->item(0)->textContent;
                                $retorno["propietariosh"][$i]["matriculapropietario"] = ltrim((string)$prop->getElementsByTagName("matriculapropietario")->item(0)->textContent, '0');
                                $retorno["propietariosh"][$i]["idtipoidentificacionpropietario"] = trim((string)$prop->getElementsByTagName("idtipoidentificacionpropietario")->item(0)->textContent);
                                $retorno["propietariosh"][$i]["identificacionpropietario"] = ltrim((string)$prop->getElementsByTagName("identificacionpropietario")->item(0)->textContent, '0');
                                $retorno["propietariosh"][$i]["nitpropietario"] = ltrim((string)$prop->getElementsByTagName("nitpropietario")->item(0)->textContent, '0');
                                $retorno["propietariosh"][$i]["nombrepropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("nombrepropietario")->item(0)->textContent));

                                //
                                (!isset($prop->getElementsByTagName("nom1propietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["nom1propietario"] = '' : $retorno["propietariosh"][$i]["nom1propietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("nom1propietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("nom2propietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["nom2propietario"] = '' : $retorno["propietariosh"][$i]["nom2propietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("nom2propietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("ape1propietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["ape1propietario"] = '' : $retorno["propietariosh"][$i]["ape1propietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("ape1propietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("ape2propietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["ape2propietario"] = '' : $retorno["propietariosh"][$i]["ape2propietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("ape2propietario")->item(0)->textContent));

                                //
                                (!isset($prop->getElementsByTagName("tipopropiedadh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["tipopropiedad"] = '0' : $retorno["propietariosh"][$i]["tipopropiedad"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("tipopropiedad")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("direccionpropietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["direccionpropietario"] = '' : $retorno["propietariosh"][$i]["direccionpropietario"] = (\funcionesGenerales::restaurarEspeciales(str_replace("#", "Nro.", $prop->getElementsByTagName("direccionpropietario")->item(0)->textContent)));
                                (!isset($prop->getElementsByTagName("municipiopropietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["municipiopropietario"] = '' : $retorno["propietariosh"][$i]["municipiopropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("municipiopropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("direccionnotpropietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["direccionnotpropietario"] = '' : $retorno["propietariosh"][$i]["direccionnotpropietario"] = (\funcionesGenerales::restaurarEspeciales(str_replace("#", "Nro.", $prop->getElementsByTagName("direccionnotpropietario")->item(0)->textContent)));
                                (!isset($prop->getElementsByTagName("municipionotpropietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["municipionotpropietario"] = '' : $retorno["propietariosh"][$i]["municipionotpropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("municipionotpropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("telefonopropietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["telefonopropietario"] = '' : $retorno["propietariosh"][$i]["telefonopropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("telefonopropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("telefono2propietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["telefono2propietario"] = '' : $retorno["propietariosh"][$i]["telefono2propietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("telefono2propietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("celularpropietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["celularpropietario"] = '' : $retorno["propietariosh"][$i]["celularpropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("celularpropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("nomreplegpropietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["nomreplegpropietario"] = '' : $retorno["propietariosh"][$i]["nomreplegpropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("nomreplegpropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("numidreplegpropietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["numidreplegpropietario"] = '' : $retorno["propietariosh"][$i]["numidreplegpropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("numidreplegpropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("tipoidreplegpropietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["tipoidreplegpropietario"] = '' : $retorno["propietariosh"][$i]["tipoidreplegpropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("tipoidreplegpropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("fecmatripropietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["fecmatripropietario"] = '' : $retorno["propietariosh"][$i]["fecmatripropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("fecmatripropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("fecrenovpropietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["fecrenovpropietario"] = '' : $retorno["propietariosh"][$i]["fecrenovpropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("fecrenovpropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("ultanorenpropietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["ultanorenpropietario"] = '' : $retorno["propietariosh"][$i]["ultanorenpropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("ultanorenpropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("organizacionpropietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["organizacionpropietario"] = '' : $retorno["propietariosh"][$i]["organizacionpropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("organizacionpropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("estadodatospropietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["estadodatospropietario"] = '' : $retorno["propietariosh"][$i]["estadodatospropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("estadodatospropietario")->item(0)->textContent));
                                (!isset($prop->getElementsByTagName("estadomatriculapropietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["estadomatriculapropietario"] = '' : $retorno["propietariosh"][$i]["estadomatriculapropietario"] = (\funcionesGenerales::restaurarEspeciales($prop->getElementsByTagName("estadomatriculapropietario")->item(0)->textContent));

                                //
                                (!isset($prop->getElementsByTagName("afiliacionpropietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["afiliacionpropietario"] = '' : $retorno["propietariosh"][$i]["afiliacionpropietario"] = $prop->getElementsByTagName("afiliacionpropietario")->item(0)->textContent;
                                (!isset($prop->getElementsByTagName("ultanorenafipropietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["ultanorenafipropietario"] = '' : $retorno["propietariosh"][$i]["ultanorenafipropietario"] = $prop->getElementsByTagName("ultanorenafipropietario")->item(0)->textContent;
                                (!isset($prop->getElementsByTagName("saldoafiliadopropietarioh")->item(0)->textContent)) ? $retorno["propietariosh"][$i]["saldoafiliadopropietario"] = 0 : $retorno["propietariosh"][$i]["saldoafiliadopropietario"] = doubleval($prop->getElementsByTagName("saldoafiliadopropietario")->item(0)->textContent);
                            }
                        }
                    } else {
                        $retorno["propietariosh"] = array();
                    }
                    unset($props);

                    // ***************************************************************************** //
                    // establecimientos
                    // ***************************************************************************** //
                    $i = 0;
                    $retorno["establecimientos"] = array();
                    $ests = $reg->getElementsByTagName("establecimiento");
                    if (count($ests) > 0) {
                        foreach ($ests as $est) {
                            if (ltrim($est->getElementsByTagName("matriculaestablecimiento")->item(0)->textContent, '0') != '') {
                                if (
                                    $est->getElementsByTagName("estadomatricula")->item(0)->textContent == 'MA' ||
                                    $est->getElementsByTagName("estadomatricula")->item(0)->textContent == 'MI' ||
                                    $est->getElementsByTagName("estadomatricula")->item(0)->textContent == 'MR' ||
                                    $est->getElementsByTagName("estadomatricula")->item(0)->textContent == 'IA' ||
                                    $est->getElementsByTagName("estadomatricula")->item(0)->textContent == 'II'
                                ) {
                                    $i++;
                                    $retorno["establecimientos"][$i]["matriculaestablecimiento"] = ltrim((string)$est->getElementsByTagName("matriculaestablecimiento")->item(0)->textContent, '0');
                                    $retorno["establecimientos"][$i]["nombreestablecimiento"] = (\funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("nombreestablecimiento")->item(0)->textContent));
                                    $retorno["establecimientos"][$i]["estadodatosestablecimiento"] = \funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("estadodatosestablecimiento")->item(0)->textContent);
                                    $retorno["establecimientos"][$i]["estadomatricula"] = \funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("estadomatricula")->item(0)->textContent);
                                    $retorno["establecimientos"][$i]["dircom"] = \funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("dircom")->item(0)->textContent);
                                    $retorno["establecimientos"][$i]["telcom1"] = \funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("telcom1")->item(0)->textContent);
                                    $retorno["establecimientos"][$i]["telcom2"] = \funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("telcom2")->item(0)->textContent);
                                    $retorno["establecimientos"][$i]["telcom3"] = \funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("telcom3")->item(0)->textContent);
                                    $retorno["establecimientos"][$i]["muncom"] = \funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("muncom")->item(0)->textContent);
                                    $retorno["establecimientos"][$i]["emailcom"] = \funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("emailcom")->item(0)->textContent);
                                    $retorno["establecimientos"][$i]["fechamatricula"] = \funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("fechamatricula")->item(0)->textContent);
                                    $retorno["establecimientos"][$i]["fecharenovacion"] = \funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("fecharenovacion")->item(0)->textContent);

                                    $retorno["establecimientos"][$i]["ultanoren"] = '';
                                    $retorno["establecimientos"][$i]["ciiu1"] = '';
                                    $retorno["establecimientos"][$i]["ciiu2"] = '';
                                    $retorno["establecimientos"][$i]["ciiu3"] = '';
                                    $retorno["establecimientos"][$i]["ciiu4"] = '';
                                    $retorno["establecimientos"][$i]["embargado"] = '';
                                    $retorno["establecimientos"][$i]["actvin"] = 0;
                                    $retorno["establecimientos"][$i]["valest"] = 0;

                                    //
                                    if (isset($est->getElementsByTagName("ultanorenovado")->item(0)->textContent)) {
                                        $retorno["establecimientos"][$i]["ultanoren"] = \funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("ultanorenovado")->item(0)->textContent);
                                    }
                                    if (isset($est->getElementsByTagName("actvin")->item(0)->textContent)) {
                                        $retorno["establecimientos"][$i]["actvin"] = \funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("actvin")->item(0)->textContent);
                                        $retorno["establecimientos"][$i]["valest"] = \funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("actvin")->item(0)->textContent);
                                    }
                                    if (isset($est->getElementsByTagName("ciiu1")->item(0)->textContent)) {
                                        $retorno["establecimientos"][$i]["ciiu1"] = \funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("ciiu1")->item(0)->textContent);
                                    }
                                    if (isset($est->getElementsByTagName("ciiu2")->item(0)->textContent)) {
                                        $retorno["establecimientos"][$i]["ciiu2"] = \funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("ciiu2")->item(0)->textContent);
                                    }
                                    if (isset($est->getElementsByTagName("ciiu3")->item(0)->textContent)) {
                                        $retorno["establecimientos"][$i]["ciiu3"] = \funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("ciiu3")->item(0)->textContent);
                                    }
                                    if (isset($est->getElementsByTagName("ciiu4")->item(0)->textContent)) {
                                        $retorno["establecimientos"][$i]["ciiu4"] = \funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("ciiu4")->item(0)->textContent);
                                    }
                                    if (isset($est->getElementsByTagName("embargado")->item(0)->textContent)) {
                                        $retorno["establecimientos"][$i]["embargado"] = \funcionesGenerales::restaurarEspeciales($est->getElementsByTagName("embargado")->item(0)->textContent);
                                    }
                                }
                            }
                        }
                    } else {
                        $retorno["establecimientos"] = array();
                    }
                    unset($ests);

                    // ***************************************************************************** //
                    // sucursales y agencias
                    // ***************************************************************************** //
                    $i = 0;
                    $sucs = $reg->getElementsByTagName("sucage");
                    if (count($sucs) > 0) {
                        foreach ($sucs as $suc) {
                            $i++;
                            $retorno["sucursalesagencias"][$i]["matriculasucage"] = ltrim((string)$suc->getElementsByTagName("matriculasucage")->item(0)->textContent, '0');
                            $retorno["sucursalesagencias"][$i]["nombresucage"] = (\funcionesGenerales::restaurarEspeciales($suc->getElementsByTagName("nombresucage")->item(0)->textContent));
                            $retorno["sucursalesagencias"][$i]["categoria"] = (\funcionesGenerales::restaurarEspeciales($suc->getElementsByTagName("categoria")->item(0)->textContent));
                            $retorno["sucursalesagencias"][$i]["estado"] = (\funcionesGenerales::restaurarEspeciales($suc->getElementsByTagName("estado")->item(0)->textContent));
                            $retorno["sucursalesagencias"][$i]["fechamatricula"] = (\funcionesGenerales::restaurarEspeciales($suc->getElementsByTagName("fechamatricula")->item(0)->textContent));
                            $retorno["sucursalesagencias"][$i]["fecharenovacion"] = (\funcionesGenerales::restaurarEspeciales($suc->getElementsByTagName("fecharenovacion")->item(0)->textContent));
                            $retorno["sucursalesagencias"][$i]["ultanoren"] = (\funcionesGenerales::restaurarEspeciales($suc->getElementsByTagName("ultanorenovado")->item(0)->textContent));
                            $retorno["sucursalesagencias"][$i]["dircom"] = (\funcionesGenerales::restaurarEspeciales($suc->getElementsByTagName("dircom")->item(0)->textContent));
                            $retorno["sucursalesagencias"][$i]["muncom"] = (\funcionesGenerales::restaurarEspeciales($suc->getElementsByTagName("muncom")->item(0)->textContent));
                            $retorno["sucursalesagencias"][$i]["telcom1"] = (\funcionesGenerales::restaurarEspeciales($suc->getElementsByTagName("telcom1")->item(0)->textContent));
                            $retorno["sucursalesagencias"][$i]["telcom2"] = (\funcionesGenerales::restaurarEspeciales($suc->getElementsByTagName("telcom2")->item(0)->textContent));
                            $retorno["sucursalesagencias"][$i]["telcom3"] = (\funcionesGenerales::restaurarEspeciales($suc->getElementsByTagName("telcom3")->item(0)->textContent));
                            $retorno["sucursalesagencias"][$i]["emailcom"] = (\funcionesGenerales::restaurarEspeciales($suc->getElementsByTagName("emailcom")->item(0)->textContent));
                            $retorno["sucursalesagencias"][$i]["ciiu1"] = (\funcionesGenerales::restaurarEspeciales($suc->getElementsByTagName("ciiu1")->item(0)->textContent));
                            $retorno["sucursalesagencias"][$i]["ciiu2"] = (\funcionesGenerales::restaurarEspeciales($suc->getElementsByTagName("ciiu2")->item(0)->textContent));
                            $retorno["sucursalesagencias"][$i]["ciiu3"] = (\funcionesGenerales::restaurarEspeciales($suc->getElementsByTagName("ciiu3")->item(0)->textContent));
                            $retorno["sucursalesagencias"][$i]["ciiu4"] = (\funcionesGenerales::restaurarEspeciales($suc->getElementsByTagName("ciiu4")->item(0)->textContent));
                            $retorno["sucursalesagencias"][$i]["embargado"] = (\funcionesGenerales::restaurarEspeciales($suc->getElementsByTagName("embargado")->item(0)->textContent));
                            $retorno["sucursalesagencias"][$i]["actvin"] = doubleval(\funcionesGenerales::restaurarEspeciales($suc->getElementsByTagName("actvin")->item(0)->textContent));
                        }
                    } else {
                        $retorno["sucursalesagencias"] = array();
                    }
                    unset($sucs);

                    // ***************************************************************************** //
                    // reseña a casa principal
                    // ***************************************************************************** //
                    (!isset($reg->getElementsByTagName("cpcodcam")->item(0)->textContent)) ? $retorno["cpcodcam"] = '' : $retorno["cpcodcam"] = trim((string)$reg->getElementsByTagName("cpcodcam")->item(0)->textContent);
                    (!isset($reg->getElementsByTagName("cpnummat")->item(0)->textContent)) ? $retorno["cpnummat"] = '' : $retorno["cpnummat"] = ltrim((string)$reg->getElementsByTagName("cpnummat")->item(0)->textContent, "0");
                    (!isset($reg->getElementsByTagName("cprazsoc")->item(0)->textContent)) ? $retorno["cprazsoc"] = '' : $retorno["cprazsoc"] = (\funcionesGenerales::restaurarEspeciales(trim((string)$reg->getElementsByTagName("cprazsoc")->item(0)->textContent)));
                    (!isset($reg->getElementsByTagName("cpnumnit")->item(0)->textContent)) ? $retorno["cpnumnit"] = '' : $retorno["cpnumnit"] = ltrim((string)$reg->getElementsByTagName("cpnumnit")->item(0)->textContent, "0");
                    (!isset($reg->getElementsByTagName("cpdircom")->item(0)->textContent)) ? $retorno["cpdircom"] = '' : $retorno["cpdircom"] = (\funcionesGenerales::restaurarEspeciales(trim((string)str_replace("#", "Nro.", $reg->getElementsByTagName("cpdircom")->item(0)->textContent))));
                    (!isset($reg->getElementsByTagName("cpdirnot")->item(0)->textContent)) ? $retorno["cpdirnot"] = '' : $retorno["cpdirnot"] = (\funcionesGenerales::restaurarEspeciales(trim((string)str_replace("#", "Nro.", $reg->getElementsByTagName("cpdirnot")->item(0)->textContent))));
                    (!isset($reg->getElementsByTagName("cpnumtel")->item(0)->textContent)) ? $retorno["cpnumtel"] = '' : $retorno["cpnumtel"] = ltrim((string)$reg->getElementsByTagName("cpnumtel")->item(0)->textContent, "0");
                    (!isset($reg->getElementsByTagName("cpnumfax")->item(0)->textContent)) ? $retorno["cpnumfax"] = '' : $retorno["cpnumfax"] = ltrim((string)$reg->getElementsByTagName("cpnumfax")->item(0)->textContent, "0");
                    (!isset($reg->getElementsByTagName("cpcodmun")->item(0)->textContent)) ? $retorno["cpcodmun"] = '' : $retorno["cpcodmun"] = trim((string)$reg->getElementsByTagName("cpcodmun")->item(0)->textContent);
                    (!isset($reg->getElementsByTagName("cpmunnot")->item(0)->textContent)) ? $retorno["cpmunnot"] = '' : $retorno["cpmunnot"] = trim((string)$reg->getElementsByTagName("cpmunnot")->item(0)->textContent);
                    (!isset($reg->getElementsByTagName("cpafili")->item(0)->textContent)) ? $retorno["cpafili"] = '' : $retorno["cpafili"] = trim((string)$reg->getElementsByTagName("cpafili")->item(0)->textContent);
                    (!isset($reg->getElementsByTagName("cpsaldo")->item(0)->textContent)) ? $retorno["cpsaldo"] = '' : $retorno["cpsaldo"] = trim((string)$reg->getElementsByTagName("cpsaldo")->item(0)->textContent);
                    (!isset($reg->getElementsByTagName("cpurenafi")->item(0)->textContent)) ? $retorno["cpurenafi"] = '' : $retorno["cpurenafi"] = trim((string)$reg->getElementsByTagName("cpurenafi")->item(0)->textContent);
                    (!isset($reg->getElementsByTagName("cptirepleg")->item(0)->textContent)) ? $retorno["cptirepleg"] = '' : $retorno["cptirepleg"] = trim((string)$reg->getElementsByTagName("cptirepleg")->item(0)->textContent);
                    (!isset($reg->getElementsByTagName("cpirepleg")->item(0)->textContent)) ? $retorno["cpirepleg"] = '' : $retorno["cpirepleg"] = trim((string)$reg->getElementsByTagName("cpirepleg")->item(0)->textContent);
                    (!isset($reg->getElementsByTagName("cpnrepleg")->item(0)->textContent)) ? $retorno["cpnrepleg"] = '' : $retorno["cpnrepleg"] = trim((string)$reg->getElementsByTagName("cpnrepleg")->item(0)->textContent);
                    (!isset($reg->getElementsByTagName("cptelrepleg")->item(0)->textContent)) ? $retorno["cptelrepleg"] = '' : $retorno["cptelrepleg"] = trim((string)$reg->getElementsByTagName("cptelrepleg")->item(0)->textContent);
                    (!isset($reg->getElementsByTagName("cpemailrepleg")->item(0)->textContent)) ? $retorno["cpemailrepleg"] = '' : $retorno["cpemailrepleg"] = trim((string)$reg->getElementsByTagName("cpemailrepleg")->item(0)->textContent);

                    // ***************************************************************************** //
                    // Libros de comercio
                    // ***************************************************************************** //
                    $retorno["libroscomercio"] = array();
                    $i = 0;
                    $lc = $reg->getElementsByTagName("lc");
                    if (!empty($lc)) {
                        foreach ($lc as $l) {
                            $i++;
                            $retorno["libroscomercio"][$i]["lib"] = $l->getElementsByTagName("lib")->item(0)->textContent;
                            $retorno["libroscomercio"][$i]["nreg"] = ltrim((string)$l->getElementsByTagName("nreg")->item(0)->textContent, "0");
                            $retorno["libroscomercio"][$i]["freg"] = $l->getElementsByTagName("freg")->item(0)->textContent;
                            $retorno["libroscomercio"][$i]["codl"] = $l->getElementsByTagName("codl")->item(0)->textContent;
                            $retorno["libroscomercio"][$i]["noml"] = trim((string)$l->getElementsByTagName("noml")->item(0)->textContent);
                            $retorno["libroscomercio"][$i]["pini"] = $l->getElementsByTagName("pini")->item(0)->textContent;
                            $retorno["libroscomercio"][$i]["pfin"] = $l->getElementsByTagName("pfin")->item(0)->textContent;
                            $retorno["libroscomercio"][$i]["npag"] = $l->getElementsByTagName("npag")->item(0)->textContent;
                            $retorno["libroscomercio"][$i]["not"] = trim((string)$l->getElementsByTagName("not")->item(0)->textContent);
                        }
                    }
                    unset($lc);
                    unset($l);

                    // ***************************************************************************** //
                    // Codigos de barras no terminados
                    // ***************************************************************************** //
                    $retorno["lcodigosbarras"] = array();
                    $i = 0;
                    if ($retorno["codigosbarras"] > 0) {
                        $cb = $reg->getElementsByTagName("cb");
                        if (!empty($cb)) {
                            foreach ($cb as $l) {
                                $i++;
                                $retorno["lcodigosbarras"][$i]["cbar"] = $l->getElementsByTagName("cbar")->item(0)->textContent;
                                $retorno["lcodigosbarras"][$i]["frad"] = $l->getElementsByTagName("frad")->item(0)->textContent;
                                $retorno["lcodigosbarras"][$i]["ttra"] = $l->getElementsByTagName("ttra")->item(0)->textContent;
                                $retorno["lcodigosbarras"][$i]["esta"] = $l->getElementsByTagName("esta")->item(0)->textContent;
                                $retorno["lcodigosbarras"][$i]["nesta"] = '';
                                $retorno["lcodigosbarras"][$i]["ntra"] = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='01' and idcodigo='" . $retorno["lcodigosbarras"][$i]["ttra"] . "'", "descripcion");
                                $retorno["lcodigosbarras"][$i]["sist"] = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='01' and idcodigo='" . $retorno["lcodigosbarras"][$i]["ttra"] . "'", "tipo");
                                if (($retorno["lcodigosbarras"][$i]["sist"] == 'ME') || ($retorno["lcodigosbarras"][$i]["sist"] == 'ES') ||
                                    ($retorno["lcodigosbarras"][$i]["sist"] == 'RM') || ($retorno["lcodigosbarras"][$i]["sist"] == 'RE')
                                ) {
                                    $retorno["lcodigosbarras"][$i]["nesta"] = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='80' and idcodigo='" . $retorno["lcodigosbarras"][$i]["esta"] . "'", "descripcion");
                                }
                                if ($retorno["lcodigosbarras"][$i]["sist"] == 'PR') {
                                    $retorno["lcodigosbarras"][$i]["nesta"] = retornarRegistroMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='82' and idcodigo='" . $retorno["lcodigosbarras"][$i]["esta"] . "'", "descripcion");
                                }
                            }
                        }
                        unset($cb);
                        unset($l);
                    }

                    // ***************************************************************************** //
                    // Inscripciones en libros
                    // ***************************************************************************** //
                    $retorno["inscripciones"] = array();
                    $i = 0;
                    $li = $reg->getElementsByTagName("li");
                    if (!empty($li)) {
                        foreach ($li as $l) {
                            $i++;
                            if ($l->getElementsByTagName("lib")->item(0)->textContent < '50') {
                                $retorno["inscripciones"][$i]["lib"] = 'RM' . $l->getElementsByTagName("lib")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["lib"] = 'RE' . $l->getElementsByTagName("lib")->item(0)->textContent;
                            }
                            $retorno["inscripciones"][$i]["nreg"] = ltrim((string)$l->getElementsByTagName("nreg")->item(0)->textContent, "0");
                            if (isset($l->getElementsByTagName("dupli")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["dupli"] = $l->getElementsByTagName("dupli")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["dupli"] = '';
                            }
                            if (isset($l->getElementsByTagName("freg")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["freg"] = $l->getElementsByTagName("freg")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["freg"] = '';
                            }
                            if (isset($l->getElementsByTagName("hreg")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["hreg"] = $l->getElementsByTagName("hreg")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["hreg"] = '';
                            }
                            if (isset($l->getElementsByTagName("frad")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["frad"] = $l->getElementsByTagName("frad")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["frad"] = '';
                            }
                            if (isset($l->getElementsByTagName("acto")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["acto"] = $l->getElementsByTagName("acto")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["acto"] = '';
                            }
                            if (isset($l->getElementsByTagName("idclase")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["idclase"] = $l->getElementsByTagName("idclase")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["idclase"] = '';
                            }
                            if (isset($l->getElementsByTagName("numid")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["numid"] = ltrim((string)$l->getElementsByTagName("numid")->item(0)->textContent, "0");
                            } else {
                                $retorno["inscripciones"][$i]["numid"] = '';
                            }
                            if (isset($l->getElementsByTagName("ndocext")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["ndocext"] = ltrim((string)$l->getElementsByTagName("ndocext")->item(0)->textContent, "0");
                            } else {
                                $retorno["inscripciones"][$i]["ndocext"] = '';
                            }
                            if (isset($l->getElementsByTagName("ndoc")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["ndoc"] = ltrim((string)$l->getElementsByTagName("ndoc")->item(0)->textContent, "0");
                            } else {
                                $retorno["inscripciones"][$i]["ndoc"] = '';
                            }
                            if (isset($l->getElementsByTagName("tdoc")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["tdoc"] = $l->getElementsByTagName("tdoc")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["tdoc"] = '';
                            }
                            if (isset($l->getElementsByTagName("fdoc")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["fdoc"] = $l->getElementsByTagName("fdoc")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["fdoc"] = '';
                            }
                            if (isset($l->getElementsByTagName("idoridoc")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["idoridoc"] = $l->getElementsByTagName("idoridoc")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["idoridoc"] = '';
                            }
                            if (isset($l->getElementsByTagName("txoridoc")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["txoridoc"] = trim((string)$l->getElementsByTagName("txoridoc")->item(0)->textContent);
                            } else {
                                $retorno["inscripciones"][$i]["txoridoc"] = '';
                            }
                            if (isset($l->getElementsByTagName("idmundoc")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["idmundoc"] = $l->getElementsByTagName("idmundoc")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["idmundoc"] = '';
                            }
                            if (isset($l->getElementsByTagName("idpaidoc")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["idpaidoc"] = $l->getElementsByTagName("idpaidoc")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["idpaidoc"] = '';
                            }
                            if (isset($l->getElementsByTagName("idlibvii")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["idlibvii"] = $l->getElementsByTagName("idlibvii")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["idlibvii"] = '';
                            }
                            if (isset($l->getElementsByTagName("codlibcom")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["codlibcom"] = $l->getElementsByTagName("codlibcom")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["codlibcom"] = '';
                            }
                            if (isset($l->getElementsByTagName("numhojas")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["numhojas"] = $l->getElementsByTagName("numhojas")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["numhojas"] = '';
                            }
                            if (isset($l->getElementsByTagName("not")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["not"] = trim((string)$l->getElementsByTagName("not")->item(0)->textContent);
                            } else {
                                $retorno["inscripciones"][$i]["not"] = '';
                            }
                            if (isset($l->getElementsByTagName("not2")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["not2"] = trim((string)$l->getElementsByTagName("not2")->item(0)->textContent);
                            } else {
                                $retorno["inscripciones"][$i]["not2"] = '';
                            }
                            if (isset($l->getElementsByTagName("not3")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["not3"] = trim((string)$l->getElementsByTagName("not3")->item(0)->textContent);
                            } else {
                                $retorno["inscripciones"][$i]["not3"] = '';
                            }
                            if (isset($l->getElementsByTagName("crecu")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["crecu"] = $l->getElementsByTagName("crecu")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["crecu"] = '';
                            }
                            if (isset($l->getElementsByTagName("cimg")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["cimg"] = $l->getElementsByTagName("cimg")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["cimg"] = '';
                            }
                            if (isset($l->getElementsByTagName("cver")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["cver"] = $l->getElementsByTagName("cver")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["cver"] = '';
                            }
                            if (isset($l->getElementsByTagName("crot")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["crot"] = $l->getElementsByTagName("crot")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["crot"] = '';
                            }
                            if (isset($l->getElementsByTagName("crev")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["crev"] = $l->getElementsByTagName("crev")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["crev"] = '';
                            }
                            if (isset($l->getElementsByTagName("cnat")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["cnat"] = $l->getElementsByTagName("cnat")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["cnat"] = '';
                            }
                            if (isset($l->getElementsByTagName("regrev")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["regrev"] = $l->getElementsByTagName("regrev")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["regrev"] = '';
                            }
                            if (isset($l->getElementsByTagName("fir")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["fir"] = $l->getElementsByTagName("fir")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["fir"] = '';
                            }
                            if (isset($l->getElementsByTagName("cfir")->item(0)->textContent)) {
                                $retorno["inscripciones"][$i]["cfir"] = $l->getElementsByTagName("cfir")->item(0)->textContent;
                            } else {
                                $retorno["inscripciones"][$i]["cfir"] = '';
                            }
                            if ($retorno["inscripciones"][$i]["not"] == '') {
                                $retorno["inscripciones"][$i]["not"] = retornarRegistroMysqliApi($mysqli, 'mreg_actos', "idlibro='" . $retorno["inscripciones"][$i]["lib"] . "' and idacto='" . $retorno["inscripciones"][$i]["acto"] . "'", "nombre");
                            }
                        }
                    }
                    unset($li);
                    unset($l);

                    // ***************************************************************************** //
                    // Nombres anteriores
                    // ***************************************************************************** //
                    $retorno["nomant"] = array();
                    $i = 0;
                    $li = $reg->getElementsByTagName("na");
                    if (!empty($li)) {
                        foreach ($li as $l) {
                            $i++;
                            $retorno["nomant"][$i]["sec"] = sprintf("%03s", $l->getElementsByTagName("sec")->item(0)->textContent);
                            $retorno["nomant"][$i]["lib"] = $l->getElementsByTagName("lib")->item(0)->textContent;
                            $retorno["nomant"][$i]["nreg"] = ltrim((string)$l->getElementsByTagName("nreg")->item(0)->textContent, "0");
                            $retorno["nomant"][$i]["freg"] = ltrim((string)$l->getElementsByTagName("freg")->item(0)->textContent, "0");
                            $retorno["nomant"][$i]["nom"] = trim((string)$l->getElementsByTagName("nom")->item(0)->textContent);
                            $retorno["nomant"][$i]["ope"] = '';
                            $retorno["nomant"][$i]["fcre"] = '';
                            if (isset($l->getElementsByTagName("ope")->item(0)->textContent)) {
                                $retorno["nomant"][$i]["ope"] = trim((string)$l->getElementsByTagName("ope")->item(0)->textContent);
                            }
                            if (isset($l->getElementsByTagName("fcre")->item(0)->textContent)) {
                                $retorno["nomant"][$i]["fcre"] = trim((string)$l->getElementsByTagName("fcre")->item(0)->textContent);
                            }
                        }
                    }
                    unset($li);
                    unset($l);

                    // 2016-04-27: JINT: Integra la gestión dinámica del formulario CAE
                    // Arma los posibles campos desde la tabla mreg_campos_cae
                    // Los mueve desde el XML
                    // Verifica si existen en forma numérica (porque vienen de SIREP y los incluye)
                    /*
                      $retorno["camposcae"] = array();
                      $i = 0;
                      $ccaes = $reg->getElementsByTagName("campocae");
                      if (!empty($ccaes)) {
                      foreach ($ccaes as $ccae) {
                      $i++;
                      $retorno["camposcae"][$i]["campo"] = $ccae->getElementsByTagName("campo")->item(0)->textContent;
                      $retorno["camposcae"][$i]["contenido"] = $ccae->getElementsByTagName("contenido")->item(0)->textContent;
                      }
                      }
                      unset($ccaes);
                      unset($ccae);
                     */

                    //
                    $retorno["codigoscae"] = array();
                    $arrY = retornarRegistrosMysqliApi($mysqli, 'mreg_anexoscae', "1=1", "codigocae");
                    if ($arrY && !empty($arrY)) {
                        foreach ($arrY as $y) {
                            $retorno["codigoscae"][$y["codigocae"]] = '';
                            if (!empty($reg->getElementsByTagName($y["codigocae"])->item(0)->textContent)) {
                                $retorno["codigoscae"][$y["codigocae"]] = trim((string)$reg->getElementsByTagName($y["codigocae"])->item(0)->textContent);
                            }
                        }
                    }

                    //
                    $retorno["informacionadicional"] = array();
                    $arrY = retornarRegistrosMysqliApi($mysqli, 'mreg_campos_adicionales_camara', "1=1", "orden");
                    if ($arrY && !empty($arrY)) {
                        foreach ($arrY as $y) {
                            $retorno["informacionadicional"][$y["codigoadicional"]] = '';
                            if (!empty($reg->getElementsByTagName($y["codigoadicional"])->item(0)->textContent)) {
                                $retorno["informacionadicional"][$y["codigoadicional"]] = trim((string)$reg->getElementsByTagName($y["codigoadicional"])->item(0)->textContent);
                            }
                        }
                    }

                    // ***************************************************************************** //
                    // Certificas
                    // ***************************************************************************** //
                    $retorno["crt"] = array();
                    $crtant = '';
                    $crt = $reg->getElementsByTagName("ce");
                    if (!empty($crt)) {
                        foreach ($crt as $l) {
                            $crtant = $l->getElementsByTagName("cd")->item(0)->textContent;
                            if (!isset($retorno["crt"][$crtant])) {
                                $retorno["crt"][$crtant] = '';
                            }
                            if ($retorno["crt"][$crtant] != '') {
                                $retorno["crt"][$crtant] .= '|';
                            }
                            $retorno["crt"][$crtant] .= trim((string)$l->getElementsByTagName("tx")->item(0)->textContent);
                        }
                    }
                    unset($crt);
                    unset($l);

                    // ***************************************************************************** //
                    // Certificas SII
                    // ***************************************************************************** //
                    $retorno["crtsii"] = array();

                    // ***************************************************************************** //
                    // Embargos
                    // ***************************************************************************** //
                    $retorno["ctrembargos"] = array();
                    $emb = $reg->getElementsByTagName("emb");
                    $i = 0;
                    if (!empty($emb)) {
                        foreach ($emb as $e) {
                            $i++;
                            $retorno["ctrembargos"][$i] = array();
                            $retorno["ctrembargos"][$i]["acto"] = $e->getElementsByTagName("acto")->item(0)->textContent;
                            $retorno["ctrembargos"][$i]["idclase"] = $e->getElementsByTagName("idclase")->item(0)->textContent;
                            $retorno["ctrembargos"][$i]["numid"] = ltrim((string)$e->getElementsByTagName("numid")->item(0)->textContent, "0");
                            $retorno["ctrembargos"][$i]["nombre"] = trim((string)$e->getElementsByTagName("nombre")->item(0)->textContent);
                            $retorno["ctrembargos"][$i]["idclasedemandante"] = '';
                            $retorno["ctrembargos"][$i]["numiddemandante"] = '';
                            $retorno["ctrembargos"][$i]["nombredemandante"] = '';
                            $retorno["ctrembargos"][$i]["tipdoc"] = trim((string)$e->getElementsByTagName("tipdoc")->item(0)->textContent);
                            $retorno["ctrembargos"][$i]["numdoc"] = ltrim((string)$e->getElementsByTagName("numdoc")->item(0)->textContent, "0");
                            $retorno["ctrembargos"][$i]["fecdoc"] = trim((string)$e->getElementsByTagName("fecdoc")->item(0)->textContent);
                            $retorno["ctrembargos"][$i]["idorigen"] = trim((string)$e->getElementsByTagName("idorigen")->item(0)->textContent);
                            $retorno["ctrembargos"][$i]["txtorigen"] = trim((string)$e->getElementsByTagName("txtorigen")->item(0)->textContent);
                            $retorno["ctrembargos"][$i]["fecrad"] = trim((string)$e->getElementsByTagName("fecrad")->item(0)->textContent);
                            $retorno["ctrembargos"][$i]["estado"] = trim((string)$e->getElementsByTagName("estado")->item(0)->textContent);
                            $retorno["ctrembargos"][$i]["libro"] = '';
                            if (isset($e->getElementsByTagName("idlibro")->item(0)->textContent)) {
                                if (trim($e->getElementsByTagName("idlibro")->item(0)->textContent) < '50') {
                                    $retorno["ctrembargos"][$i]["libro"] = 'RM' . trim((string)$e->getElementsByTagName("idlibro")->item(0)->textContent);
                                } else {
                                    $retorno["ctrembargos"][$i]["libro"] = 'RE' . trim((string)$e->getElementsByTagName("idlibro")->item(0)->textContent);
                                }
                            }
                            $retorno["ctrembargos"][$i]["numreg"] = ltrim((string)$e->getElementsByTagName("numreg")->item(0)->textContent, "0");
                            $retorno["ctrembargos"][$i]["codbarras"] = ltrim((string)$e->getElementsByTagName("codbarras")->item(0)->textContent, "0");
                            $retorno["ctrembargos"][$i]["noticia"] = ltrim((string)$e->getElementsByTagName("noticia")->item(0)->textContent, "0");
                            $retorno["ctrembargos"][$i]["fecinscripcion"] = $e->getElementsByTagName("fecinscripcion")->item(0)->textContent;
                        }
                    }
                    unset($emb);
                    unset($e);
                }
            }
            unset($reg);
            unset($reg1);
            unset($dom);
        }

        if ($retorno["empsoccategorias"] != '') {
            $escat = explode(",", $retorno["empsoccategorias"]);
            foreach ($escat as $es) {
                $retorno[$es] = 'S';
            }
        }
        if ($retorno["empsocbeneficiarios"] != '') {
            $escat = explode(",", $retorno["empsocbeneficiarios"]);
            foreach ($escat as $es) {
                $retorno[$es] = 'S';
            }
        }

        return $retorno;
    }

    public static function describeEstadoZonaVirtual($codEstado = '')
    {
        $msj = '';
        switch ($codEstado) {
            case 200:
                $msj = "PAGO INICIADO";
                break;
            case 777:
                $msj = "PAGO DECLINADO";
                break;
            case 888:
                $msj = "PAGO PENDIENTE POR INICIAR";
                break;
            case 999:
                $msj = "PAGO PENDIENTE POR FINALIZAR";
                break;
            case 4001:
                $msj = "PENDIENTE POR CR";
                break;
            case 4000:
                $msj = "RECHAZADO CR";
                break;
            case 4003:
                $msj = "ERROR CR";
                break;
            case 1000:
                $msj = "PAGO RECHAZADO";
                break;
            case 1001:
                $msj = "ERROR ENTRE ACH Y EL BANCO (RECHAZADA)";
                break;
            case 1002:
                $msj = "PAGO RECHAZADO";
                break;
            case 1:
                $msj = "PAGO FINALIZADO OK";
                break;
            default:
                break;
        }
        return $msj;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $xml
     * @param type $controlprimeravez
     * @param type $proceso
     * @param type $tipotramite
     * @return type
     */
    public static function desserializarExpedienteProponente($mysqli, $xml, $controlprimeravez = 'no', $proceso = 'llamado directo a desserializarExpedienteProponente', $tipotramite = '')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales_desserializarExpedienteProponente.php');
        return funcionesGenerales_desserializarExpedienteProponente::desserializarExpedienteProponente($mysqli, $xml, $controlprimeravez, $proceso, $tipotramite);
    }

    /**
     * 
     * @param type $dbx
     * @param type $fecha_principal
     * @param type $fecha_secundaria
     * @return int
     */
    public static function diferenciaEntreFechasHabiles($dbx, $fecha_principal, $fecha_secundaria)
    {
        $habiles = 0;
        $cals = retornarRegistrosMysqliApi($dbx, "bas_calendario", "fecha>='" . $fecha_secundaria . "' and fecha<='" . $fecha_principal . "'", "fecha");
        foreach ($cals as $c) {
            if ($c["tipodia"] == 'H') {
                $habiles++;
            }
        }
        return $habiles;
    }

    /**
     * 
     * @param type $dbx
     * @param type $fecha_principal (Inicial)
     * @param type $fecha_secundaria (Final)
     * @return int
     */
    public static function diferenciaEntreFechasHabilesSiguiente($dbx, $fecha_principal, $fecha_secundaria)
    {
        $habiles = 0;
        $cals = retornarRegistrosMysqliApi($dbx, "bas_calendario", "fecha> '" . $fecha_secundaria . "' and fecha<='" . $fecha_principal . "'", "fecha");
        foreach ($cals as $c) {
            if ($c["tipodia"] == 'H') {
                $habiles++;
            }
        }
        return $habiles;
    }

    /**
     * 
     * @param type $fecha_principal
     * @param type $fecha_secundaria
     * @param type $obtener
     * @param type $redondear
     * @return type
     */
    public static function diferenciaEntreFechasCalendario($fecha_principal, $fecha_secundaria, $obtener = 'DIAS', $redondear = true)
    {
        $datetime1 = new DateTime(\funcionesGenerales::mostrarFecha($fecha_principal));
        $datetime2 = new DateTime(\funcionesGenerales::mostrarFecha($fecha_secundaria));
        $interval = $datetime2->diff($datetime1);
        $resultado = $interval->format('%a');
        if ($obtener == 'ANOS') {
            $resultado = $interval->format('%y');
        }
        if ($obtener == 'MESES') {
            $resultado = $interval->format('%m');
        }
        return $resultado;
    }

    public static function diferenciaEntreFechaBase30($fechafinal, $fechainicial)
    {
        $fechafinal = str_replace(array("-", "/"), "", $fechafinal);
        $fechainicial = str_replace(array("-", "/"), "", $fechainicial);
        $iDias = 0;
        $iFecha = $fechainicial;
        while ($iFecha <= $fechafinal) {
            $ano = intval(substr($iFecha, 0, 4));
            $mes = intval(substr($iFecha, 4, 2));
            $dia = intval(substr($iFecha, 6, 2));

            if ($dia < 31) {
                $iDias++;
            }

            if ($dia == 31) {
                $dia = 1;
                $mes++;
                if ($mes == 13) {
                    $ano++;
                    $mes = 1;
                }
            } else {
                if ($dia == 30) {
                    if (($mes == 4) || ($mes == 6) || ($mes == 9) || ($mes == 11)) {
                        $dia = 1;
                        $mes++;
                    } else {
                        $dia++;
                    }
                } else {
                    if ($dia == 29) {
                        if (($mes == 2)) {
                            $dia = 1;
                            $mes++;
                            $iDias++;
                        } else {
                            $dia++;
                        }
                    } else {
                        if ($dia == 28) {
                            if (($mes == 2)) {
                                if ($ano != 2000 && $ano != 2004 && $ano != 2008 && $ano != 2012 && $ano != 2014 && $ano != 2018 && $ano != 2022 && $ano != 2026 && $ano != 2030 && $ano != 2034) {
                                    $dia = 1;
                                    $mes++;
                                    $iDias++;
                                    $iDias++;
                                } else {
                                    $dia++;
                                }
                            } else {
                                $dia++;
                            }
                        } else {
                            $dia++;
                        }
                    }
                }
            }
            $iFecha = sprintf("%04s", $ano) . sprintf("%02s", $mes) . sprintf("%02s", $dia);
        }
        return $iDias;
    }

    public static function docToPdf($file_doc, $file_pdf)
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        if (!defined('PATH_COMMON_BASE')) {
            define('PATH_COMMON_BASE', '/opt');
        }
        if (!file_exists(PATH_COMMON_BASE . '/commonBase.php')) {
            $_SESSION["generales"]["mensajeerror"] = 'No es posible pasar el formato doc a pdf, no localiz&oacute; el archivo commonBase.php';
            return false;
        }

        require_once(PATH_COMMON_BASE . '/commonBase.php');

        //
        ini_set('display_errors', '1');
        $session = ssh2_connect('localhost', 22);
        if (!$session) {
            \logApi::general2('docToPdf_' . date("Ymd"), '', 'Fallo conexion SSH');
            return false;
        }
        if (!ssh2_auth_password($session, SII_USER_ROOT, SII_PASSWORD_ROOT)) {
            unset($session);
            \logApi::general2('docToPdf_' . date("Ymd"), '', 'Fallo la autenticación SSH');
            return false;
        }

        // Encuentra el nombre del archivo sin directorios
        $lfile = explode('/', $file_doc);
        $extns = count($lfile) - 1;
        $file = $lfile[$extns];

        // Si el archivo no existe
        if (!file_exists($file_doc)) {
            $_SESSION["generales"]["mensajeerror"] = 'No fue encontrado el archivo a convertir a pdf';
            return false;
        }

        // Ejecucion del comndo libreoffice desde ssh
        $ntmp = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . date("Ymd") . '-' . date("His") . '.log';
        ssh2_exec($session, 'libreoffice --headless --invisible --convert-to pdf:writer_pdf_Export -outdir ' . $_SESSION["generales"]["pathabsoluto"] . '/tmp ' . $file_doc . ' > ' . $ntmp);
        if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $file)) {
            unset($session);
            \logApi::general2('docToPdf_' . date("Ymd"), '', 'ERROR convirtiendo de word a pdf');
            $_SESSION["generales"]["mensajeerror"] = 'No fue posible convertir el archivo doc o docx a pdf, archivo original : ' . $file_doc;
            return false;
        } else {
            \logApi::general2('docToPdf_' . date("Ymd"), '', 'convertido de doc a pdf en forma satisfactoria');
        }


        // Localizaci&oacute;n de la extensi&oacute;n del archivo doc o docx
        $ext = \funcionesGenerales::encontrarExtension($file);

        // reemplazo de la extensi&oacute;n en el archivo de salida
        $file = str_replace("." . $ext, ".pdf", $file);

        // Cambio de permisos
        ssh2_exec($session, 'chmod 777 ' . $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $file);
        unset($session);

        // Copiar el pdf generado al pdf esperado
        copy($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $file, $file_pdf);
        unlink($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $file);

        // Borrado del pdf generado
        return true;
    }

    public static function dumpArreglo($arreglo)
    {
        $txt = '';
        foreach ($arreglo as $key => $valor) {
            if (!is_array($valor)) {
                if ($valor != '') {
                    $txt .= $key . ' => ' . $valor . chr(13) . chr(10);
                }
            } else {
                $txt .= $key . '(arreglo)' . chr(13) . chr(10);
                foreach ($valor as $key1 => $valor1) {
                    if (!is_array($valor1)) {
                        if ($valor1 != '') {
                            $txt .= '..... ' . $key1 . ' => ' . $valor1 . chr(13) . chr(10);
                        }
                    } else {
                        $txt .= '..... ' . $key1 . '(arreglo)' . chr(13) . chr(10);
                        foreach ($valor1 as $key2 => $valor2) {
                            if (!is_array($valor2)) {
                                if ($valor2 != '') {
                                    $txt .= '.... ..... ' . $key2 . ' => ' . $valor2 . chr(13) . chr(10);
                                }
                            } else {
                                $txt .= '.... .... ' . $key2 . '(arreglo)' . chr(13) . chr(10);
                                foreach ($valor2 as $key3 => $valor3) {
                                    if (!is_array($valor3)) {
                                        if ($valor3 != '') {
                                            $txt .= '.... ..... .... ' . $key3 . ' => ' . $valor3 . chr(13) . chr(10);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $txt;
    }

    public static function generarToken($mysqli = null, $verificar = '', $longitud = 0)
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');
        if ($longitud < 4) {
            $longitud = 4;
        }
        if ($verificar == '') {
            return bin2hex(random_bytes(($longitud - ($longitud % 2)) / 2));
        } else {
            if ($verificar == 'log_emails') {
                $continuar = 'si';
                while ($continuar == 'si') {
                    $tok = bin2hex(random_bytes(($longitud - ($longitud % 2)) / 2));
                    if ($verificar == 'log_emails') {
                        if (contarRegistrosMysqliApi($mysqli, $verificar, "token='" . $tok . "'") == 0) {
                            $continuar = 'no';
                        }
                    }
                }
                return $tok;
            }
            if ($verificar == 'mreg_liquidacion_firmantes_remotos') {
                $continuar = 'si';
                while ($continuar == 'si') {
                    $tok = bin2hex(random_bytes(($longitud - ($longitud % 2)) / 2));
                    if ($verificar == 'mreg_liquidacion_firmantes_remotos') {
                        if (contarRegistrosMysqliApi($mysqli, $verificar, "token='" . $tok . "'") == 0) {
                            $continuar = 'no';
                        }
                    }
                }
                return $tok;
            }
        }
    }

    public static function generarAleatorioAlfanumerico($tamano = 6, $sinprimercero = 'si')
    {
        $numrecvalido = 'NO';
        while ($numrecvalido == 'NO') {
            $ok = 'NO';
            while ($ok == 'NO') {
                $alfanumerico = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $alfanumerico1 = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
                $num = '';
                for ($i = 1; $i <= $tamano; $i++) {
                    if ($i == 1 && $sinprimercero == 'si') {
                        $num .= substr(substr($alfanumerico1, rand(1, strlen($alfanumerico1))), 0, 1);
                    } else {
                        $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
                    }
                }
                if (strlen($num) == $tamano) {
                    $ok = 'SI';
                }
            }
            $numrecvalido = 'SI';
        }
        return $num;
    }

    public static function generarAleatorioNumerico($dbx = null, $validar = '', $sinprimercero = 'si')
    {
        $numrecvalido = 'NO';
        while ($numrecvalido == 'NO') {
            $ok = 'NO';
            while ($ok == 'NO') {
                $alfanumerico = '0123456789';
                $alfanumerico1 = '123456789';
                $num = '';
                for ($i = 1; $i <= 6; $i++) {
                    if ($i == 1 && $sinprimercero == 'si') {
                        $num .= substr(substr($alfanumerico1, rand(1, strlen($alfanumerico1))), 0, 1);
                    } else {
                        $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
                    }
                }
                if (strlen($num) == 6) {
                    $ok = 'SI';
                }
            }
            if ($validar == '') {
                $numrecvalido = 'SI';
            }
            if ($validar == 'mreg_liquidacion') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
        }
        return $num;
    }

    public static function generarAleatorioNumerico2()
    {
        $num = '';
        $alfanumerico = '0123456789';
        for ($i = 1; $i <= 2; $i++) {
            $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
        }
        return $num;
    }

    public static function generarAleatorioNumerico4()
    {
        $num = '';
        $alfanumerico = '0123456789';
        for ($i = 1; $i <= 4; $i++) {
            $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
        }
        return $num;
    }

    public static function generarAleatorioAlfanumerico8($dbx, $validar = '', $sinprimercero = 'si')
    {
        $numrecvalido = 'NO';
        while ($numrecvalido == 'NO') {
            $ok = 'NO';
            while ($ok == 'NO') {
                $alfanumerico = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $alfanumerico1 = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
                $num = '';
                for ($i = 1; $i <= 8; $i++) {
                    if ($i == 1 && $sinprimercero == 'si') {
                        $num .= substr(substr($alfanumerico1, rand(1, strlen($alfanumerico1))), 0, 1);
                    } else {
                        $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
                    }
                }
                if (strlen($num) == 8) {
                    $ok = 'SI';
                }
            }
            if ($validar == '') {
                $numrecvalido = 'SI';
            }
            if ($validar == 'mreg_liquidacion') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_liquidacion_baloto') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_liquidacion_baloto', "volantenumero='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_liquidacion_exito') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_liquidacion_exito', "volantenumero='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_reactivaciones_propias') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_reactivaciones_propias', "codigoreactivacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_certificados_virtuales') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_certificados_virtuales', "id='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
        }
        return $num;
    }

    public static function generarAleatorioAlfanumerico9($sinprimercero = 'si')
    {
        $ok = 'NO';
        while ($ok == 'NO') {
            $alfanumerico = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $alfanumerico1 = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
            $num = '';
            for ($i = 1; $i <= 9; $i++) {
                if ($i == 1 && $sinprimercero == 'si') {
                    $num .= substr(substr($alfanumerico1, rand(1, strlen($alfanumerico1))), 0, 1);
                } else {
                    $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
                }
            }
            if (strlen($num) == 9) {
                $ok = 'SI';
            }
        }
        return $num;
    }

    public static function generarAleatorioAlfanumerico10($dbx = null, $validar = '', $grabar = 'no')
    {
        $numrecvalido = 'NO';
        while ($numrecvalido == 'NO') {
            $ok = 'NO';
            while ($ok == 'NO') {
                // $alfanumerico = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $alfanumerico = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ123456789';
                $num = '';
                for ($i = 1; $i <= 10; $i++) {
                    $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
                }
                if (strlen($num) == 10) {
                    $ok = 'SI';
                }
            }
            if ($validar == '') {
                $numrecvalido = 'SI';
            }

            if ($validar == 'desarrollo_actividades') {
                if (contarRegistrosMysqliApi($dbx, 'desarrollo_actividades', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'desarrollo_actualizaciones') {
                if (contarRegistrosMysqliApi($dbx, 'desarrollo_actualizaciones', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }


            if ($validar == 'desarrollo_control_cambios') {
                if (contarRegistrosMysqliApi($dbx, 'desarrollo_control_cambios', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'desarrollo_control_cambios_casosuso') {
                if (contarRegistrosMysqliApi($dbx, 'desarrollo_control_cambios_casosuso', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'desarrollo_iniciativas') {
                if (contarRegistrosMysqliApi($dbx, 'desarrollo_iniciativas', "identificador_iniciativa='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'infraestructura_contratos') {
                if (contarRegistrosMysqliApi($dbx, 'infraestructura_contratos', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'infraestructura_proveedores') {
                if (contarRegistrosMysqliApi($dbx, 'infraestructura_proveedores', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            //
            if ($validar == 'infraestructura_clientes') {
                if (contarRegistrosMysqliApi($dbx, 'infraestructura_clientes', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            //
            if ($validar == 'infraestructura_comentarios') {
                if (contarRegistrosMysqliApi($dbx, 'infraestructura_comentarios', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            //
            if ($validar == 'mreg_liquidacion_sobre') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_liquidacion_sobre', "idsobre='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            //
            if ($validar == 'mreg_liquidacion') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            //
            if ($validar == 'mreg_liquidacion_baloto') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_liquidacion_baloto', "volantenumero='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            //
            if ($validar == 'mreg_liquidacion_exito') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_liquidacion_exito', "volantenumero='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            //
            if ($validar == 'mreg_reactivaciones_propias') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_reactivaciones_propias', "codigoreactivacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            //
            if ($validar == 'mreg_certificados_virtuales') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_certificados_virtuales', "id='" . $num . "'") == 0) {
                    if ($grabar == 'no') {
                        $numrecvalido = 'SI';
                    } else {
                        $arrCampos = array(
                            'id'
                        );
                        $arrValores = array(
                            "'" . $num . "'"
                        );
                        $rex1 = insertarRegistrosMysqliApi($dbx, 'mreg_certificados_virtuales', $arrCampos, $arrValores);
                        if ($rex1) {
                            $numrecvalido = 'SI';
                        }
                    }
                }
            }

            //
            if ($validar == 'mreg_rues_bloque') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_rues_bloque', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
        }
        return $num;
    }

    public static function generarAleatorioNumerico10($dbx = null, $validar = '', $sinprimercero = 'si')
    {
        $numrecvalido = 'NO';
        while ($numrecvalido == 'NO') {
            $ok = 'NO';
            while ($ok == 'NO') {
                $alfanumerico = '0123456789';
                $alfanumerico1 = '123456789';
                $num = '';
                for ($i = 1; $i <= 10; $i++) {
                    if ($i == 1 && $sinprimercero == 'si') {
                        $num .= substr(substr($alfanumerico1, rand(1, strlen($alfanumerico1))), 0, 1);
                    } else {
                        $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
                    }
                }
                if (strlen($num) == 10) {
                    $ok = 'SI';
                }
            }
            if ($validar == '') {
                $numrecvalido = 'SI';
            }
            if ($validar == 'mreg_liquidacion') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_liquidacion_baloto') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_liquidacion_baloto', "volantenumero='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_liquidacion_exito') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_liquidacion_exito', "volantenumero='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_reactivaciones_propias') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_reactivaciones_propias', "codigoreactivacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
            if ($validar == 'mreg_certificados_virtuales') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_certificados_virtuales', "id='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
        }
        return $num;
    }

    public static function generarAleatorioAlfanumerico20($dbx = null, $validar = '', $sinprimercero = 'si')
    {
        $numrecvalido = 'NO';
        while ($numrecvalido == 'NO') {
            $ok = 'NO';
            while ($ok == 'NO') {
                $alfanumerico = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $alfanumerico1 = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
                $num = '';
                for ($i = 1; $i <= 20; $i++) {
                    if ($i == 1 && $sinprimercero == 'si') {
                        $num .= substr(substr($alfanumerico1, rand(1, strlen($alfanumerico1))), 0, 1);
                    } else {
                        $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
                    }
                }
                if (strlen($num) == 20) {
                    $ok = 'SI';
                }
            }
            if ($validar == '') {
                $numrecvalido = 'SI';
            }

            if ($validar == 'mreg_liquidacion') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'desarrollo_iniciativas_comentarios') {
                if (contarRegistrosMysqliApi($dbx, 'desarrollo_iniciativas_comentarios', "identificador_comentario='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'mreg_documentos_firmados') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_documentos_firmados', "idfirmado='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'mreg_envio_matriculas_api') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_envio_matriculas_api', "idenvio='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }

            if ($validar == 'mreg_est_inscritos_xml_dian') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_est_inscritos_xml_dian', "identificador='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }


            if ($validar == 'reconocimientos_validacion') {
                if (contarRegistrosMysqliApi($dbx, 'reconocimientos_validacion', "id='" . $num . "'") == 0) {
                    $numrecvalido = 'SI';
                }
            }
        }
        return $num;
    }

    public static function grabarArchivo($file, $txt)
    {
        $f = fopen($file, "w");
        fwrite($f, $txt);
        fclose($f);
    }

    public static function grabarLogGeneral($log, $txt)
    {
        if (!defined('PATH_ABSOLUTO_LOGS') || PATH_ABSOLUTO_LOGS == '') {
            $f = fopen(PATH_ABSOLUTO_SITIO . '/logs/' . $log . '_' . date("Ymd") . '.log', "a");
        } else {
            $f = fopen(PATH_ABSOLUTO_LOGS . '/' . $log . '_' . date("Ymd") . '.log', "a");
        }
        fwrite($f, date("Y-m-d") . ' - ' . date("H:i:s") . ' : ' . $txt . chr(13) . chr(10));
        fclose($f);
    }

    public static function homologarDianTipoIdentificacion($dato)
    {
        $datosalida = '';
        switch ($dato) {
            case "1":
                $datosalida = '13';
                break;
            case "2":
                $datosalida = '31';
                break;
            case "3":
                $datosalida = '22';
                break;
            case "4":
                $datosalida = '12';
                break;
            case "5":
                $datosalida = '41';
                break;
            case "E":
                $datosalida = '42';
                break;
            case "R":
                $datosalida = '11';
                break;
            case "V":
                $datosalida = '47';
                break;
            case "P":
                $datosalida = '48';
                break;
        }
        return $datosalida;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $tipo
     * @param type $valor
     * @return type
     */
    public static function homologacionCodigoSII($mysqli, $tipo, $valor)
    {
        $reg = retornarRegistroMysqliApi($mysqli, 'mreg_homologaciones_rue', "trim(id_tabla) like '" . $tipo . "' and trim(cod_rue) like '" . $valor . "'", "cod_cc");
        return trim($reg);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $servicios
     * @return type
     */
    public static function homologacion_formatos_codificacionSII($mysqli, $servicios)
    {
        $n = 1;
        $resultado = 0;
        if (is_array($servicios) && !empty($servicios)) {
            foreach ($servicios as $servicio) {
                $servicio = $servicio->idservicio;
                $codigoHomologacion = retornarRegistroMysqliApi($mysqli, 'mreg_homologaciones_rue', "cod_rue='" . $servicio . "' ");
                if ($codigoHomologacion == false || count($codigoHomologacion) == 0) {
                    $resultado = $servicio;
                }
                $n++;
            }
        }
        return $resultado;
    }

    /**
     * 
     * @param type $string
     * @return type
     */
    public static function isJson($string)
    {
        return ((is_string($string) &&
            (is_object(json_decode($string)) ||
                is_array(json_decode($string))))) ? true : false;
    }

    public static function jsonPrettyPrint($json)
    {
        $result = '';
        $level = 0;
        $in_quotes = false;
        $in_escape = false;
        $ends_line_level = NULL;
        $json_length = strlen($json);

        for ($i = 0; $i < $json_length; $i++) {
            $char = $json[$i];
            $new_line_level = NULL;
            $post = "";
            if ($ends_line_level !== NULL) {
                $new_line_level = $ends_line_level;
                $ends_line_level = NULL;
            }
            if ($in_escape) {
                $in_escape = false;
            } else if ($char === '"') {
                $in_quotes = !$in_quotes;
            } else if (!$in_quotes) {
                switch ($char) {
                    case '}':
                    case ']':
                        $level--;
                        $ends_line_level = NULL;
                        $new_line_level = $level;
                        break;

                    case '{':
                    case '[':
                        $level++;
                    case ',':
                        $ends_line_level = $level;
                        break;

                    case ':':
                        $post = " ";
                        break;

                    case " ":
                    case "\t":
                    case "\n":
                    case "\r":
                        $char = "";
                        $ends_line_level = $new_line_level;
                        $new_line_level = NULL;
                        break;
                }
            } else if ($char === '\\') {
                $in_escape = true;
            }
            if ($new_line_level !== NULL) {
                $result .= "\n" . str_repeat("\t", $new_line_level);
            }
            $result .= $char . $post;
        }

        return $result;
    }

    public static function limpiarTexto($txt)
    {
        $txt = preg_replace('/[^._A-Za-z0-9 ]/', ' ', $txt);
        $txt = preg_replace('/[&ntilde;&ntilde;&aacute;&eacute;&iacute;&oacute;&uacute;&aacute;&eacute;&iacute;&oacute;&uacute;]/', ' ', $txt);
        return $txt;
    }

    /**
     * Retorna idfranquicia de una liquidación
     * @param type $id
     * @return string
     */
    public static function franquicia($id)
    {
        $p_otrosmedios = array("_PSE_");
        $p_banco = array("AV_AV", "AV_BB", "AV_BO", "AV_BP", "T1_BC", "T1_CV");
        $p_visa = array("CR_VE", "CR_VS", "V_VBV");
        $p_mastercard = array("RM_MC");
        $p_americexpress = array("CR_AM");
        $p_credencial = array("CR_CR");
        $p_diners = array("CR_DN");

        $franquicia = "";

        if (in_array($id, $p_banco)) {
            $franquicia = "01";
        }
        if (in_array($id, $p_visa)) {
            $franquicia = "02";
        }
        if (in_array($id, $p_mastercard)) {
            $franquicia = "03";
        }
        if (in_array($id, $p_americexpress)) {
            $franquicia = "04";
        }
        if (in_array($id, $p_credencial)) {
            $franquicia = "05";
        }
        if (in_array($id, $p_diners)) {
            $franquicia = "06";
        }
        if (in_array($id, $p_otrosmedios)) {
            $franquicia = "07";
        }
        return $franquicia;
    }

    /**
     * 
     * @param type $n
     */
    public static function forzarDescarga($n)
    {
        if (substr($n, 0, 12) == 'base64encode') {
            $name1 = base64_decode(substr($n, 12));
        } else {
            $name1 = $n;
        }
        if (substr($name1, 0, 4) == 'http') {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $name1);
            $data = curl_exec($ch);
            curl_close($ch);
            $name = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . rand(1000000, 5000000) . '.' . \funcionesGenerales::encontrarExtension($name1);
            $f = fopen($name, "w");
            fwrite($f, $data);
            fclose($f);
        } else {
            $name = $name1;
        }
        $file = basename($name);
        $type = '';

        if (is_file($name)) {
            $size = filesize($name);
            if (function_exists('mime_content_type')) {
                $type = mime_content_type($name);
            } else if (function_exists('finfo_file')) {
                $info = finfo_open(FILEINFO_MIME);
                $type = finfo_file($info, $name);
                finfo_close($info);
            }
            if ($type == '') {
                $type = "application/force-download";
            }
            $type = "application/force-download";
            header("Content-Type: $type");
            header("Content-Disposition: attachment; filename=$file");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: " . $size);
            // Descargar archivo
            ob_start();
            ob_end_clean();
            flush();
            readfile($name);
            exit();
        } else {
            die("El archivo no existe. : " . $n);
        }
    }

    /**
     * 
     * @param type $dbx
     * @param type $ano
     * @return type
     */
    public static function retornarSalarioMinimoActual($dbx = null, $ano = '')
    {
        $smlvs = retornarRegistrosMysqliApi($dbx, 'bas_smlv', "1=1", "fecha asc");
        $minimo = 0;
        foreach ($smlvs as $sm) {
            if ($ano != '') {
                if (substr($sm["fecha"], 0, 4) == $ano) {
                    $minimo = $sm["salario"];
                }
            } else {
                if (($sm["fecha"] <= date("Ymd"))) {
                    $minimo = $sm["salario"];
                }
            }
        }
        unset($smlvs);
        unset($sm);
        return $minimo;
    }

    /**
     * 
     * @param type $dbx
     * @param type $ano
     * @return type
     */
    public static function retornarUvbActual($dbx = null, $ano = '')
    {
        $smlvs = retornarRegistrosMysqliApi($dbx, 'bas_smlv', "1=1", "fecha asc");
        $uvb = 0;
        foreach ($smlvs as $sm) {
            if ($ano != '') {
                if (substr($sm["fecha"], 0, 4) == $ano) {
                    $uvb = $sm["uvb"];
                }
            } else {
                if (($sm["fecha"] <= date("Ymd"))) {
                    $uvb = $sm["uvb"];
                }
            }
        }
        unset($smlvs);
        unset($sm);
        return $uvb;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $pantalla
     * @return string
     */
    public static function retornarPantallaPredisenada($mysqli, $pantalla = '')
    {
        $pant = retornarRegistroMysqliApi($mysqli, 'pantallas_propias', "idpantalla='" . $pantalla . "'");
        if ($pant === false || empty($pant)) {
            $pant = retornarRegistroMysqliApi($mysqli, 'bas_pantallas', "idpantalla='" . $pantalla . "'");
            if ($pant === false || empty($pant)) {
                return "";
            } else {
                return $pant["txtasociado"];
            }
        } else {
            return $pant["txtasociado"];
        }
    }

    public static function FileSizeConvert($bytes)
    {
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        foreach ($arBytes as $arItem) {
            if ($bytes >= $arItem["VALUE"]) {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", ",", strval(round($result, 2))) . " " . $arItem["UNIT"];
                break;
            }
        }
        return $result;
    }

    public static function limpiarTextosRedundantes($entrada)
    {
        if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/api/arrCorrector' . $_SESSION["generales"]["codigoempresa"] . '.php')) {
            include($_SESSION["generales"]["pathabsoluto"] . '/api/arrCorrector' . $_SESSION["generales"]["codigoempresa"] . '.php');
        } else {
            if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/arrCorrector' . $_SESSION["generales"]["codigoempresa"] . '.php')) {
                include($_SESSION["generales"]["pathabsoluto"] . '/arrCorrector' . $_SESSION["generales"]["codigoempresa"] . '.php');
            } else {
                if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/arrCorrector.php')) {
                    include($_SESSION["generales"]["pathabsoluto"] . '/arrCorrector.php');
                } else {
                    if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/api/arrCorrector.php')) {
                        include($_SESSION["generales"]["pathabsoluto"] . '/api/arrCorrector.php');
                    } else {
                        $arrFrases = array();
                        $arrCorrector = array();
                    }
                }
            }
        }

        //
        $entrada = str_replace("[10]", "ñ", $entrada);

        //
        $minusculas = 'abcdefghijklmnñopqrstuvwxyzáéíóú';
        $mayusculas = 'ABCDEFGHIJKLMNÑOPQRSTUVWXYZÁÉÍÓÚ';

        $entrada = trim($entrada);
        $entrada = str_replace("  ", " ", $entrada);
        $entrada = str_replace("  ", " ", $entrada);
        $entrada = str_replace("  ", " ", $entrada);

        $c = substr($entrada, 0, 1);
        $entrada = strtoupper($c) . substr($entrada, 1);

        //        
        foreach ($arrFrases as $ori => $des) {
            $entrada = str_replace($ori, $des, $entrada);
        }

        //
        $salida = '';

        //
        $explode = explode(" ", $entrada);
        if (!empty($explode)) {
            foreach ($explode as $pal) {
                $pal = trim($pal);
                // Palabnra tal cual
                if (isset($arrCorrector[$pal])) {
                    $salida .= ' ' . $arrCorrector[$pal];
                } else {
                    // La palabra pero sin coma, punto, punto y coma o dos puntos
                    $nult = strlen($pal) - 1;
                    $ult = substr($pal, $nult, 1);
                    if ($ult == '.' || $ult == ',' || $ult == ';' || $ult == ':') {
                        $palx = substr($pal, 0, $nult);
                    } else {
                        $palx = $pal;
                        $ult = '';
                    }
                    if (isset($arrCorrector[$palx])) {
                        $salida .= ' ' . $arrCorrector[$palx] . $ult;
                    } else {
                        // La palabra con la primera letra en mayúsculas
                        $c = substr($palx, 0, 1);
                        if (strpos($mayusculas, $c)) {
                            $palx = strtolower($c) . substr($palx, 1);
                            if (isset($arrCorrector[$palx])) {
                                $salida .= ' ' . strtoupper($c) . substr($arrCorrector[$palx], 1) . $ult;
                            } else {
                                $salida .= ' ' . $c . substr($palx, 1) . $ult;
                            }
                        } else {
                            // La palabra terminada en "s"
                            $nult1 = strlen($palx) - 1;
                            $ult1 = substr($palx, $nult1, 1);
                            if ($ult1 == 's') {
                                $palx1 = substr($palx, 0, $nult1);
                                if (isset($arrCorrector[$palx1])) {
                                    $salida .= ' ' . $arrCorrector[$palx1] . 's' . $ult;
                                } else {
                                    // La palabra terminada en "as"
                                    $nult2 = strlen($palx) - 2;
                                    $ult2 = substr($palx, $nult2, 2);
                                    if ($ult2 == 'as') {
                                        $palx2 = substr($palx, 0, $nult2);
                                        if (isset($arrCorrector[$palx2 . 'o'])) {
                                            $salida .= ' ' . substr($arrCorrector[$palx2 . 'o'], 0, $nult1) . 'as' . $ult;
                                        } else {
                                            $salida .= ' ' . $palx2 . 'as' . $ult;
                                        }
                                    } else {
                                        $salida .= ' ' . $palx1 . 's' . $ult;
                                    }
                                }
                            } else {
                                // La palabra terminada en "a"
                                $nult1 = strlen($palx) - 1;
                                $ult1 = substr($palx, $nult1, 1);
                                if ($ult1 == 'a') {
                                    $palx1 = substr($palx, 0, $nult1);
                                    if (isset($arrCorrector[$palx1 . 'o'])) {
                                        $salida .= ' ' . substr($arrCorrector[$palx1 . 'o'], 0, $nult1) . 'a' . $ult;
                                    } else {
                                        $salida .= ' ' . $palx1 . 'a' . $ult;
                                    }
                                } else {
                                    $salida .= ' ' . $palx . $ult;
                                }
                            }
                        }
                    }
                }
            }
        }

        //
        unset($arrCorrector);
        unset($arrFrases);

        //
        return trim($salida);
    }

    public static function agregarPuntoFinal($entrada)
    {
        $salida = trim($entrada);
        // \logApi::general2('puntoFinal', 'Entrada: ', $entrada);
        if (strlen($salida) == 0) {
            return $salida;
        }
        $len1 = strlen($salida);
        $len1--;
        $c = substr($salida, $len1, 1);
        if ($c != '.' && $c != ',' && $c != ':') {
            $salida .= '.';
        }
        // \logApi::general2('puntoFinal', 'Salida: ', $salida);
        return $salida;
    }

    public static function localizarCampoAnterior($mysqli, $mat, $campo)
    {
        $salida = '';
        $ano = date("Y");
        $anoinicial = date("Y");
        while ($ano > $anoinicial - 3) {
            // if (existeTablaMysqliApi($mysqli,'mreg_campos_historicos_' . $ano)) {
            if ($salida == '') {
                $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_campos_historicos_' . $ano, "matricula='" . $mat . "' and campo='" . $campo . "'", "fecha desc, hora desc");
                if ($temx && !empty($temx)) {
                    foreach ($temx as $tx) {
                        if (!isset($tx["inactivarsipref"]) || strtolower($tx["inactivarsipref"]) != 'si') {
                            if (trim($tx["datoanterior"]) != '') {
                                $salida = $tx["datoanterior"];
                            }
                        }
                    }
                }
            }
            if ($salida != '') {
                $ano = 0;
            }
            // }
            if ($ano != 0) {
                $ano = $ano - 1;
            }
        }
        return $salida;
    }

    public static function localizarCampoAnteriorTodos($mysqli, $mat, $campo)
    {
        $salida = array();
        $ano = date("Y");
        $anoinicial = date("Y");
        while ($ano > $anoinicial - 5) {
            // if (existeTablaMysqliApi($mysqli,'mreg_campos_historicos_' . $ano)) {
            $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_campos_historicos_' . $ano, "matricula='" . $mat . "' and campo='" . $campo . "'", "fecha desc, hora desc");
            if ($temx && !empty($temx)) {
                foreach ($temx as $tx) {
                    if (!isset($tx["inactivarsipref"]) || strtolower($tx["inactivarsipref"]) != 'si') {
                        if (trim($tx["datoanterior"]) != '') {
                            $salida[] = $tx["datoanterior"];
                        }
                    }
                }
            }
            // }
            if ($ano != 0) {
                $ano = $ano - 1;
            }
        }
        return $salida;
    }

    public static function localizarIP()
    {
        $ip = '';

        //
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        //
        if ($ip == '') {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip = '127.0.0.1';
                }
            }
        }
        return $ip;
    }

    public static function localizarLetraCiiu($dbx, $ciiu)
    {
        $res = retornarRegistroMysqliApi($dbx, "bas_ciius", "idciiunum='" . $ciiu . "'");
        if ($res === false || empty($res)) {
            return '';
        }
        $resultado = $res["idciiu"];
        return substr($resultado, 0, 1);
    }

    public static function localizarSmmlv($fecha, $dbx = null)
    {

        $resultado = '';
        $cerrar = 'no';
        if ($dbx === null) {
            $fuente = '';
            include($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/asignaBD.php');
            $dbx = new mysqli($dbhost, $dbusuario, $dbpassword, $dbname);
            $cerrar = 'si';
        }

        $temx = retornarRegistrosMysqliApi($dbx, 'bas_smlv', "fecha");
        foreach ($temx as $res) {
            if ($res["fecha"] < $fecha) {
                $resultado = $res["salario"];
            }
        }

        if ($cerrar == 'si') {
            $dbx->close();
        }
        return $resultado;
    }

    public static function montoEscrito($num, $fem = false, $dec = true)
    {
        $matuni[2] = "DOS";
        $matuni[3] = "TRES";
        $matuni[4] = "CUATRO";
        $matuni[5] = "CINCO";
        $matuni[6] = "SEIS";
        $matuni[7] = "SIETE";
        $matuni[8] = "OCHO";
        $matuni[9] = "NUEVE";
        $matuni[10] = "DIEZ";
        $matuni[11] = "ONCE";
        $matuni[12] = "DOCE";
        $matuni[13] = "TRECE";
        $matuni[14] = "CATORCE";
        $matuni[15] = "QUINCE";
        $matuni[16] = "DIECISEIS";
        $matuni[17] = "DIECISIETE";
        $matuni[18] = "DIECIOCHO";
        $matuni[19] = "DIECINUEVE";
        $matuni[20] = "VEINTE";
        $matunisub[2] = "DOS";
        $matunisub[3] = "TRES";
        $matunisub[4] = "CUATRO";
        $matunisub[5] = "QUIN";
        $matunisub[6] = "SEIS";
        $matunisub[7] = "SETE";
        $matunisub[8] = "OCHO";
        $matunisub[9] = "NOVE";

        $matdec[2] = "VEINT";
        $matdec[3] = "TREINTA";
        $matdec[4] = "CUARENTA";
        $matdec[5] = "CINCUENTA";
        $matdec[6] = "SESENTA";
        $matdec[7] = "SETENTA";
        $matdec[8] = "OCHENTA";
        $matdec[9] = "NOVENTA";
        $matsub[3] = 'MILL';
        $matsub[5] = 'BILL';
        $matsub[7] = 'MILL';
        $matsub[9] = 'TRILL';
        $matsub[11] = 'MILL';
        $matsub[13] = 'BILL';
        $matsub[15] = 'MILL';
        $matmil[4] = 'MILLONES';
        $matmil[6] = 'BILLONES';
        $matmil[7] = 'DE BILLONES';
        $matmil[8] = 'MILLONES DE BILLONES';
        $matmil[10] = 'TRILLONES';
        $matmil[11] = 'DE TRILLONES';
        $matmil[12] = 'MILLONES DE TRILLONES';
        $matmil[13] = 'DE TRILLONES';
        $matmil[14] = 'BILLONES DE TRILLONES';
        $matmil[15] = 'DE BILLONES DE TRILLONES';
        $matmil[16] = 'MILLONES DE BILLONES DE TRILLONES';

        //Zi hack
        $float = explode('.', $num);
        $num = $float[0];

        $num = trim((string) @$num);
        if ($num[0] == '-') {
            $neg = 'menos ';
            $num = substr($num, 1);
        } else {
            $neg = '';
        }
        while ($num[0] == '0') {
            $num = substr($num, 1);
        }
        if ($num[0] < '1' or $num[0] > 9) {
            $num = '0' . $num;
        }
        $zeros = true;
        $punt = false;
        $ent = '';
        $fra = '';
        for ($c = 0; $c < strlen($num); $c++) {
            $n = $num[$c];
            if (!(strpos(".,'''", $n) === false)) {
                if ($punt) {
                    break;
                } else {
                    $punt = true;
                    continue;
                }
            } elseif (!(strpos('0123456789', $n) === false)) {
                if ($punt) {
                    if ($n != '0') {
                        $zeros = false;
                    }
                    $fra .= $n;
                } else {
                    $ent .= $n;
                }
            } else {
                break;
            }
        }
        $ent = '     ' . $ent;
        if ($dec and $fra and !$zeros) {
            $fin = ' COMA';
            for ($n = 0; $n < strlen($fra); $n++) {
                if (($s = $fra[$n]) == '0') {
                    $fin .= ' CERO';
                } elseif ($s == '1') {
                    $fin .= $fem ? ' UNA' : ' UN';
                } else {
                    $fin .= ' ' . $matuni[$s];
                }
            }
        } else {
            $fin = '';
        }
        if ((int) $ent === 0) {
            return 'CERO ' . $fin;
        }
        $tex = '';
        $sub = 0;
        $mils = 0;
        $neutro = false;
        while (($num = substr($ent, -3)) != '   ') {
            $ent = substr($ent, 0, -3);
            if (++$sub < 3 and $fem) {
                $matuni[1] = 'UNA';
                $subcent = 'AS';
            } else {
                $matuni[1] = $neutro ? 'UN' : 'UNO';
                $subcent = 'OS';
            }
            $t = '';
            $n2 = substr($num, 1);
            if ($n2 == '00') {
            } elseif ($n2 < 21) {
                $t = ' ' . $matuni[(int) $n2];
            } elseif ($n2 < 30) {
                $n3 = $num[2];
                if ($n3 != 0) {
                    $t = 'I' . $matuni[$n3];
                }
                $n2 = $num[1];
                $t = ' ' . $matdec[$n2] . $t;
            } else {
                $n3 = $num[2];
                if ($n3 != 0) {
                    $t = ' Y ' . $matuni[$n3];
                }
                $n2 = $num[1];
                $t = ' ' . $matdec[$n2] . $t;
            }
            $n = $num[0];
            if ($n == 1) {
                $t = ' CIENTO' . $t;
            } elseif ($n == 5) {
                if (isset($matunisub[$n])) {
                    $t = ' ' . $matunisub[$n] . 'IENT' . $subcent . $t;
                }
            } elseif ($n != 0) {
                if (isset($matunisub[$n])) {
                    $t = ' ' . $matunisub[$n] . 'CIENT' . $subcent . $t;
                }
            }
            if ($sub == 1) {
            } elseif (!isset($matsub[$sub])) {
                if ($num == 1) {
                    $t = ' MIL';
                } elseif ($num > 1) {
                    $t .= ' MIL';
                }
            } elseif ($num == 1) {
                $t .= ' ' . $matsub[$sub] . 'ON';
            } elseif ($num > 1) {
                $t .= ' ' . $matsub[$sub] . 'ONES';
            }
            if ($num == '000') {
                $mils++;
            } elseif ($mils != 0) {
                if (isset($matmil[$sub])) {
                    $t .= ' ' . $matmil[$sub];
                }
                $mils = 0;
            }
            $neutro = true;
            $tex = $t . $tex;
        }
        $tex = $neg . substr($tex, 1) . $fin;
        //Zi hack --> return ucfirst($tex);
        if (!isset($float[1])) {
            $float[1] = 0;
        }
        $end_num = ucfirst($tex) . ' PESOS ' . $float[1] . '/100 MCTE';
        return $end_num;
    }

    /**
     * 
     * @param type $scripth
     * @param type $scriptb
     * @param type $scriptbend
     * @param type $tittle
     * @param type $showtittle
     * @param type $body
     * @param type $image
     * @param string $plantilla
     * @param type $salida
     * @param type $textoemergente
     * @return type
     */
    public static function mostrarCuerpoBootstrap($scripth = '', $scriptb = '', $scriptbend = '', $tittle = '', $showtittle = 'no', $body = '', $image = '', $plantilla = '', $salida = 'echo', $textoemergente = '')
    {

        if (!isset($_SESSION["generales"]["codigousuario"])) {
            $_SESSION["generales"]["codigousuario"] = '';
        }

        //
        if ($plantilla == '') {
            $plantilla = 'plantillaVaciaHttp.html';
        }
        $stream_opts = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ]
        ];

        //
        // if (!defined('TIPO_HTTP')) {
        $pant = file_get_contents($_SESSION["generales"]["pathabsoluto"] . '/bootstrap/' . $plantilla, true);
        // } else {
        //    $pant = file_get_contents(TIPO_HTTP . HTTP_HOST . '/bootstrap/' . $plantilla, true, stream_context_create($stream_opts));
        // }
        //
        $pant = str_replace("[LOGO_SISTEMA]", LOGO_SISTEMA, $pant);
        $pant = str_replace("[SYSTEMNAME]", NOMBRE_SISTEMA, $pant);
        $pant = str_replace("[COUNTRYCOMPANYSOFTWARE]", PAIS_CASA_SOFTWARE, $pant);
        $pant = str_replace("[COMPANYSOFTWARE]", NOMBRE_CASA_SOFTWARE, $pant);
        $pant = str_replace("[COMPANYNAME]", NOMBRE_CASA_SOFTWARE, $pant);
        $pant = str_replace("[WEBSOFTWARECOMPANY]", WEB_CASA_SOFTWARE, $pant);
        $pant = str_replace("[SCRIPTHEADER]", $scripth, $pant);
        $pant = str_replace("[SCRIPTBODY]", $scriptb, $pant);
        $pant = str_replace("[SCRIPTBODYEND]", $scriptbend, $pant);
        $pant = str_replace("[USERNAME]", $_SESSION["generales"]["codigousuario"], $pant);
        $pant = str_replace("[USER]", $_SESSION["generales"]["codigousuario"], $pant);
        $pant = str_replace("[YEAR]", date("Y"), $pant);
        $pant = str_replace("[DATE]", date("Y-m-d") . ' - ' . date("H:i:s"), $pant);
        $pant = str_replace("[CONTENT]", $body, $pant);
        if (!defined('TIPO_HTTP')) {
            $pant = str_replace("[TIPOHTTP]", '', $pant);
            $pant = str_replace("[HTTPHOST]", '', $pant);
        } else {
            $pant = str_replace("[TIPOHTTP]", TIPO_HTTP, $pant);
            $pant = str_replace("[HTTPHOST]", HTTP_HOST, $pant);
        }
        // $pant = str_replace("[IMAGE_BACKGROUND]", $image, $pant);
        if (isset($_SESSION["generales"]["txtemergente"]) && $_SESSION["generales"]["txtemergente"] != '') {
            $pant = str_replace("[TITULOEMERGENTE]", '!!! ATENCION !!!', $pant);
            $pant = str_replace("[MENSAJEEMERGENTE]", $_SESSION["generales"]["txtemergente"], $pant);
            $mostraremergente = '<script>
                $(document).ready(function () {
                    $("#myModal").modal("show");
                });
                </script>';
            $pant = str_replace("[MOSTRAREMERGENTE]", $mostraremergente, $pant);
        } else {
            if ($textoemergente != '') {
                $pant = str_replace("[TITULOEMERGENTE]", '!!! ATENCION !!!', $pant);
                $pant = str_replace("[MENSAJEEMERGENTE]", $textoemergente, $pant);
                $mostraremergente = '<script>
                $(document).ready(function () {
                    $("#myModal").modal("show");
                });
                </script>';
                $pant = str_replace("[MOSTRAREMERGENTE]", $mostraremergente, $pant);
            } else {
                $pant = str_replace("[TITULOEMERGENTE]", '', $pant);
                $pant = str_replace("[MENSAJEEMERGENTE]", '', $pant);
                $pant = str_replace("[MOSTRAREMERGENTE]", '', $pant);
            }
        }
        $_SESSION["generales"]["txtemergente"] = '';
        if ($showtittle == 'si') {
            $pant = str_replace("[VISIBLE]", 'd-block', $pant);
        } else {
            $pant = str_replace("[VISIBLE]", 'd-none', $pant);
        }
        if ($salida == 'echo') {
            echo $pant;
            exit();
        } else {
            return $pant;
        }
    }

    public static function mostrarCuerpoBootstrapSimple($scripth = '', $scriptb = '', $scriptbend = '', $tittle = '', $showtittle = 'no', $body = '', $image = '', $plantilla = '')
    {

        if (!isset($_SESSION["generales"]["codigousuario"])) {
            $_SESSION["generales"]["codigousuario"] = '';
        }
        $plantilla = 'plantillaMensajeSimple.html';
        if (file_exists('../bootstrap/' . $plantilla)) {
            $pant = file_get_contents('../bootstrap/' . $plantilla, true);
        } else {
            if (file_exists('../../bootstrap/' . $plantilla)) {
                $pant = file_get_contents('../../bootstrap/' . $plantilla, true);
            }
        }
        if (!defined('LOGO_SISTEMA') || LOGO_SISTEMA == '') {
            $pant = str_replace("[LOGO_SISTEMA]", '', $pant);
        } else {
            $pant = str_replace("[LOGO_SISTEMA]", LOGO_SISTEMA, $pant);
        }
        $pant = str_replace("[SYSTEMNAME]", NOMBRE_SISTEMA, $pant);
        $pant = str_replace("[COUNTRYCOMPANYSOFTWARE]", PAIS_CASA_SOFTWARE, $pant);
        $pant = str_replace("[COMPANYSOFTWARE]", NOMBRE_CASA_SOFTWARE, $pant);
        $pant = str_replace("[COMPANYNAME]", NOMBRE_CASA_SOFTWARE, $pant);
        $pant = str_replace("[LATERALMENU]", '', $pant);
        $pant = str_replace("[WEBSOFTWARECOMPANY]", WEB_CASA_SOFTWARE, $pant);
        $pant = str_replace("[SCRIPTHEADER]", $scripth, $pant);
        $pant = str_replace("[SCRIPTBODY]", $scriptb, $pant);
        $pant = str_replace("[SCRIPTBODYEND]", $scriptbend, $pant);
        $pant = str_replace("[USERNAME]", '', $pant);
        $pant = str_replace("[USER]", '', $pant);
        $pant = str_replace("[YEAR]", date("Y"), $pant);
        $pant = str_replace("[DATE]", date("Y-m-d") . ' - ' . date("H:i:s"), $pant);
        $pant = str_replace("[CONTENT]", $body, $pant);
        $pant = str_replace("[TIPOHTTP]", '', $pant);
        $pant = str_replace("[HTTPHOST]", '', $pant);
        $pant = str_replace("[IMAGE_BACKGROUND]", $image, $pant);
        if (isset($_SESSION["generales"]["txtemergente"]) && $_SESSION["generales"]["txtemergente"] != '') {
            $pant = str_replace("[TITULOEMERGENTE]", '!!! ATENCION !!!', $pant);
            $pant = str_replace("[MENSAJEEMERGENTE]", $_SESSION["generales"]["txtemergente"], $pant);
            $mostraremergente = '<script>
                $(document).ready(function () {
                    $("#myModal").modal("show");
                });
                </script>';
            $pant = str_replace("[MOSTRAREMERGENTE]", $mostraremergente, $pant);
        } else {
            $pant = str_replace("[TITULOEMERGENTE]", '', $pant);
            $pant = str_replace("[MENSAJEEMERGENTE]", '', $pant);
            $pant = str_replace("[MOSTRAREMERGENTE]", '', $pant);
        }
        $_SESSION["generales"]["txtemergente"] = '';
        if ($showtittle == 'si') {
            $pant = str_replace("[VISIBLE]", 'd-block', $pant);
        } else {
            $pant = str_replace("[VISIBLE]", 'd-none', $pant);
        }
        echo $pant;
    }

    public static function mostrarCuerpoConsultaVirtual($scripth = '', $scriptb = '', $txtbarra = '', $txt = '', $width = 800, $height = 400)
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/template.class.php');

        $cantipen = 0;
        $cantinue = 0;
        $pant = new template();
        $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/cve.html');
        $pant->armarScriptHeader($scripth);
        $pant->armarScriptBody($scriptb);
        $pant->armarBarraOpciones($txtbarra);
        $pant->armarTareasPendientes($cantipen, $cantinue);
        $pant->armarBody($txt[3]);
        $pant->armarNombreExpedientes($txt[0]);
        $pant->armarIdentificacionExpedientes($txt[1]);
        $pant->armarDomicilioExpedientes($txt[2]);
        $pant->armarBanner($txt[5]);
        $pant->armarEstadoExpediente(\funcionesGenerales::utf8_decode($txt[4]));
        $pant->armarDate();
        $pant->armarSesion();
        $pant->armarHttpHost(HTTP_HOST);
        $pant->armarServidorRss(SERVIDOR_RSS);
        $pant->armarUrlSite(TIPO_HTTP . HTTP_HOST);
        $pant->armarCodigoEntidad(CODIGO_EMPRESA);
        $pant->armarTituloAplicacion(NOMBRE_SISTEMA);
        $pant->armarSitioWeb(HTTP_HOST);
        $pant->armarNombreEntidad(RAZONSOCIAL);
        $pant->armarEmailAtencionUsuarios(EMAIL_ATENCION_USUARIOS);
        $pant->armarTelefonoAtencionUsuarios(TELEFONO_ATENCION_USUARIOS);
        $pant->armarGoogleAnalitics();
        $pant->armarFooter();
        $pant->armarDisplayHeader('no', 0, 0);
        if (trim($txtbarra) != '') {
            $pant->armarDisplayBarraOpciones('si', $width, 30);
        } else {
            $pant->armarDisplayBarraOpciones('no', 0, 00);
        }
        $pant->armarDisplayFramePrincipal('no', 0, 0);
        $pant->armarDisplayFrameSecundario('si', $width, $height);
        $pant->armarDisplayFooter('no', 0, 0);
        if (!isset($_SESSION["generales"]["txtemergente"])) {
            $_SESSION["generales"]["txtemergente"] = '';
        }
        $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
        $_SESSION["generales"]["txtemergente"] = '';
        $pant->armarEstilo(CODIGO_EMPRESA);
        $pant->armarTiempoPromedioProponentes(TIEMPO_PROMEDIO_TRAMITES_PROPONENTES);
        $pant->mostrar();
        unset($pant);
        exit();
    }

    /**
     * 
     * @param type $fec
     * @return string En formato AAAA-MM-DD
     */
    public static function mostrarFecha($fec)
    {
        if ((trim((string) $fec) == '') || (ltrim((string) $fec, "0") == '')) {
            return '';
        }
        if (strlen((string) $fec) == 10) {
            $fec = str_replace("/", "-", (string) $fec);
            return $fec;
        } else {
            return substr((string) $fec, 0, 4) . '-' . substr((string) $fec, 4, 2) . '-' . substr((string) $fec, 6, 2);
        }
    }

    /**
     * 
     * @param type $fec
     * @return string en formatp DD-MM-AAAA
     */
    public static function mostrarFechaDDMMYYYY($fec)
    {
        if ((trim($fec) == '') || (ltrim($fec, "0") == '')) {
            return '';
        }
        if (strlen($fec) == 10) {
            $fec = str_replace("/", "-", $fec);
            return $fec;
        } else {
            return substr($fec, 6, 2) . '-' . substr($fec, 4, 2) . '-' . substr($fec, 0, 4);
        }
    }

    /**
     * 
     * @param type $fec
     * @return string En formato DD/MM/AAAA
     */
    public static function mostrarFecha2($fec)
    {
        if ((trim($fec) == '') || (ltrim($fec, "0") == '')) {
            return '';
        }
        if (strlen($fec) == 10) {
            $fec = str_replace(array("/", "-"), "", $fec);
        }
        return substr($fec, 6, 2) . '/' . substr($fec, 4, 2) . '/' . substr($fec, 0, 4);
    }

    /**
     * Fecha en formato DD-MM-AAAA
     * @param type $fec
     * @return string
     */
    public static function mostrarDDMMAAAA($fec)
    {
        if ((trim($fec) == '') || (ltrim($fec, "0") == '')) {
            return '';
        }
        if (strlen($fec) == 10) {
            $fec = str_replace(array("/", "-"), "", $fec);
        }
        return substr($fec, 6, 2) . '-' . substr($fec, 4, 2) . '-' . substr($fec, 0, 4);
    }

    public static function mostrarFechaLetras($fec)
    {
        if (trim($fec) == '') {
            return '';
        }
        $fec = str_replace(array('-', '/'), "", $fec);
        if (trim($fec) == '') {
            return '';
        }
        $txt = '';
        $mes = substr($fec, 4, 2);
        switch ($mes) {
            case "01":
                $txt = 'enero';
                break;
            case "02":
                $txt = 'febrero';
                break;
            case "03":
                $txt = 'marzo';
                break;
            case "04":
                $txt = 'abril';
                break;
            case "05":
                $txt = 'mayo';
                break;
            case "06":
                $txt = 'junio';
                break;
            case "07":
                $txt = 'julio';
                break;
            case "08":
                $txt = 'agosto';
                break;
            case "09":
                $txt = 'septiembre';
                break;
            case "10":
                $txt = 'octubre';
                break;
            case "11":
                $txt = 'noviembre';
                break;
            case "12":
                $txt = 'diciembre';
                break;
        }
        if (strlen($fec) == 6) {
            return $txt . ' ' . ' de ' . substr($fec, 0, 4);
        } else {
            return $txt . ' ' . substr($fec, 6, 2) . ' de ' . substr($fec, 0, 4);
        }
    }

    public static function mostrarFechaLetras1($fec)
    {
        if (trim($fec) == '') {
            return '';
        }
        $fec = str_replace(array('-', '/'), "", $fec);
        if (trim($fec) == '') {
            return '';
        }
        $txt = '';
        $mes = substr($fec, 4, 2);
        switch ($mes) {
            case "01":
                $txt = 'enero';
                break;
            case "02":
                $txt = 'febrero';
                break;
            case "03":
                $txt = 'marzo';
                break;
            case "04":
                $txt = 'abril';
                break;
            case "05":
                $txt = 'mayo';
                break;
            case "06":
                $txt = 'junio';
                break;
            case "07":
                $txt = 'julio';
                break;
            case "08":
                $txt = 'agosto';
                break;
            case "09":
                $txt = 'septiembre';
                break;
            case "10":
                $txt = 'octubre';
                break;
            case "11":
                $txt = 'noviembre';
                break;
            case "12":
                $txt = 'diciembre';
                break;
        }
        if (strlen($fec) == 6) {
            return $txt . ' ' . ' de ' . substr($fec, 0, 4);
        } else {
            return substr($fec, 6, 2) . ' de ' . $txt . ' de ' . substr($fec, 0, 4);
        }
        exit();
    }

    public static function mostrarHora($dat)
    {
        $dat = str_replace(":", "", (string) $dat);
        if ((trim((string) $dat) == '') || (ltrim((string) $dat, "0") == '')) {
            return '';
        }
        if (strlen((string) $dat) == 6) {
            return substr((string) $dat, 0, 2) . ':' . substr((string) $dat, 2, 2) . ':' . substr((string) $dat, 4, 2);
        } else {
            if (strlen((string) $dat) == 4) {
                return substr((string) $dat, 0, 2) . ':' . substr((string) $dat, 2, 2) . ':00';
            } else {
                return $dat;
            }
        }
        exit();
    }

    public static function mostrarInicialNueva($txterror = '', $tipo = '')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/presentacion.class.php');
        if (!isset($_SESSION["generales"]["codigoempresa"])) {
            $_SESSION["generales"]["codigoempresa"] = '';
            $xml = simplexml_load_file($_SESSION["generales"]["pathabsoluto"] . '/configuracion/empresas.php');
            foreach ($xml->empresa as $tempresa) {
                $cod = (string) $tempresa->codigo;
                if (isset($tempresa->host)) {
                    $host = (string) $tempresa->host;
                } else {
                    $host = '';
                }
                if ($host == $_SERVER["HTTP_HOST"] && $host != '') {
                    $_SESSION["generales"]["codigoempresa"] = $cod;
                }
            }
        }

        //
        if (!defined('NOMBRE_SISTEMA1')) {
            (define('NOMBRE_SISTEMA1', ''));
        }
        if (!defined('DIRECCION_CASA_SOFTWARE')) {
            (define('DIRECCION_CASA_SOFTWARE', ''));
        }
        if (!defined('TELEFONO_CASA_SOFTWARE')) {
            (define('TELEFONO_CASA_SOFTWARE', ''));
        }
        if (!defined('CIUDAD_CASA_SOFTWARE')) {
            (define('CIUDAD_CASA_SOFTWARE', ''));
        }
        if (!defined('DECLARACION_PRIVACIDAD')) {
            (define('DECLARACION_PRIVACIDAD', ''));
        }

        //

        if (isset($_SERVER["HTTPS"])) {
            $tipohttp = 'https://';
        } else {
            $tipohttp = 'http://';
        }

        //
        $lista = \funcionesGenerales::retornarListaXmlEmpresas('', $_SERVER["HTTP_HOST"]);
        $lista1 = \funcionesGenerales::retornarListaXmlEmpresas('', $_SERVER["HTTP_HOST"]);
        $lista2 = \funcionesGenerales::retornarListaXmlEmpresas('', $_SERVER["HTTP_HOST"]);

        $txtRem = '';
        if ($tipo == 'remitida') {
            if (!isset($_SESSION["generales"]["controlusuariorutina"]) || $_SESSION["generales"]["controlusuariorutina"] == '') {
                $tx = 'Portal de Servicios Virtuales de la Cámara de Comercio';
            } else {
                $tx = $_SESSION["generales"]["controlusuariorutina"];
            }
            $txtRem = 'Usted ingresó al ' . $tx . ', sin que previamente se hubiere identificado ';
            $txtRem .= 'en nuestro sistema de información. Es necesario que se identifique para poder continuar.';
        }


        $pres = new presentacionBootstrap();
        $random = rand(1000, 10000);
        $cuerpo = $pres->armarCampoTextoOculto('_tipohttp', TIPO_HTTP) .
            $pres->armarCampoTextoOculto('_httphost', HTTP_HOST) .
            $pres->armarCampoTextoOculto('session_parameters', \funcionesGenerales::armarVariablesPantalla()) .
            $pres->abrirPanelGeneral(600) .
            $pres->armarEncabezado(NOMBRE_SISTEMA1, 'Ingreso') .
            '<br>';

        $txt = 'Bienvenido al portal de servicios virtuales, por favor seleccione la empresa en la cual desea ingresar e indique los datos de su usuario para ingresar';
        $cuerpo .= $pres->abrirPanel() .
            $pres->armarLineaTextoInformativa($txt, 'center', '', 'text-dark', '', 'si') .
            $pres->cerrarPanel() .
            '<br>';

        if (!isset($_SESSION["generales"]["controlusuariorutina"])) {
            $_SESSION["generales"]["controlusuariorutina"] = '';
        }
        if (!isset($_SESSION["generales"]["controlusuarioretornara"])) {
            $_SESSION["generales"]["controlusuarioretornara"] = '';
        }
        $cuerpo .= $pres->abrirPanel() .
            $pres->armarLineaTextoInformativa('Ingresar como usuario interno o previamente registrado.', 'center', '', 'text-dark', '', 'si') .
            $pres->abrirFormulario('formEntrada', 'post', TIPO_HTTP . HTTP_HOST . '/disparador.php?accion=logueousuarioregistrado') .
            $pres->armarCampoSelect('Empresa', 'si', '_empresa', $_SESSION["generales"]["codigoempresa"], $lista) .
            $pres->armarCampoTexto('Correo electrónico o usuario', 'si', '_emailUsuarioRegistrado', '') .
            $pres->armarCampoTexto('Identificación', 'si', '_identificacionUsuarioRegistrado', '') .
            $pres->armarCampoPassword('Clave', 'si', '_claveUsuarioRegistrado_' . $random) .
            $pres->armarCampoTextoOculto('_random', $random) .
            $pres->armarCampoTextoOculto('_periodo', date("Y")) .
            $pres->armarCampoTextoOculto('_tipohttp', TIPO_HTTP) .
            $pres->armarCampoTextoOculto('_httphost', HTTP_HOST) .
            $pres->armarCampoTextoOculto('_controlusuariorutina', $_SESSION["generales"]["controlusuariorutina"]) .
            $pres->armarCampoTextoOculto('_controlusuarioretornara', $_SESSION["generales"]["controlusuarioretornara"]) .
            '<br>' .
            $pres->armarBotonDinamico('submit', 'Ingresar', 'Ingresar al sistema de información') .
            $pres->cerrarFormulario() .
            $pres->cerrarPanel() .
            // '<br>' .
            '<a href="disparador.php?accion=formularioregistro&session_parameters=' . \funcionesGenerales::armarVariablesPantalla() . '">Registrarse</a></br>' .
            '<a href="disparador.php?accion=formulariorecordarcontrasena&session_parameters=' . \funcionesGenerales::armarVariablesPantalla() . '">Recordar contraseña</a></br>';

        unset($pres);
        $head = '<script type="text/javascript" src="' . TIPO_HTTP . HTTP_HOST . '/js/disparador.js"></script>';
        \funcionesGenerales::mostrarCuerpoBootstrap($head, '', '', '', '', $cuerpo, '', '');
        exit();
    }

    public static function mostrarMovil($titulo = '', $txterror = '', $cuerpo = '', $scripth = '', $scriptb = '', $sombra = '', $principal = 'no')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/template.class.php');
        if (!defined('NOMBRE_SISTEMA1')) {
            (define('NOMBRE_SISTEMA1', ''));
        }
        if (!defined('DIRECCION_CASA_SOFTWARE')) {
            (define('DIRECCION_CASA_SOFTWARE', ''));
        }
        if (!defined('TELEFONO_CASA_SOFTWARE')) {
            (define('TELEFONO_CASA_SOFTWARE', ''));
        }
        if (!defined('CIUDAD_CASA_SOFTWARE')) {
            (define('CIUDAD_CASA_SOFTWARE', ''));
        }
        if (!defined('DECLARACION_PRIVACIDAD')) {
            (define('DECLARACION_PRIVACIDAD', ''));
        }

        //
        $pant = new template();

        // if (isset($_SERVER["https"])) {
        if (TIPO_HTTP == 'https://') {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/bootstrap/mobileHttps.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/bootstrap/mobileHttp.html');
        }

        if (!isset($_SESSION["generales"]["validado"])) {
            $_SESSION["generales"]["validado"] = '';
        }
        $pant->armarDate();
        $pant->armarBanner(TIPO_HTTP . HTTP_HOST . '/images/top1200x80-' . $_SESSION["generales"]["codigoempresa"] . '.jpg');
        $pant->armarTipoHttp(TIPO_HTTP);
        $pant->armarHttpHost(HTTP_HOST);
        $pant->armarUrlSite(TIPO_HTTP . HTTP_HOST);
        $pant->armarPeriodo(date("Y"));
        $pant->armarTituloAplicacion('SII 1.0 Versi&oacute;n M&oacute;vil');
        $pant->armarTituloMenu($titulo);
        if ($principal == 'no') {
            if ($_SESSION["generales"]["validado"] == 'SI' || $_SESSION["generales"]["tipousuariocontrol"] != 'usuarioanonimo') {
                $pant->armarTxtHome('<a href="' . TIPO_HTTP . HTTP_HOST . '/disparador.php?accion=cargarprincipalindex"><img src="' . TIPO_HTTP . HTTP_HOST . '/html/default/images/pack/home32.png"></a>Volver al men&uacute; principal<br><hr>');
            } else {
                $pant->armarTxtHome('');
            }
        } else {
            $pant->armarTxtHome('');
        }
        $pant->armarTxtError($txterror);
        $pant->armarNombreSistema(NOMBRE_SISTEMA);
        $pant->armarNombreSistema1(NOMBRE_SISTEMA1);
        $pant->armarNombreCasaSoftware(NOMBRE_CASA_SOFTWARE);
        $pant->armarDireccionCasaSoftware(DIRECCION_CASA_SOFTWARE);
        $pant->armarCiudadCasaSoftware(CIUDAD_CASA_SOFTWARE);
        $pant->armarTelefonoCasaSoftware(TELEFONO_CASA_SOFTWARE);
        $pant->armarDeclaracionPrivacidad(DECLARACION_PRIVACIDAD);
        $pant->armarScriptHeader($scripth);
        $pant->armarScriptBody($scriptb);
        $pant->armarMenuMovil('');
        if (!isset($_SESSION["generales"]["tipodispositivo"])) {
            $_SESSION["generales"]["tipodispositivo"] = 'tablet';
        }
        $pant->armarTipoDispositivo($_SESSION["generales"]["tipodispositivo"]);
        // $pant->armarSelectAnos($txtanos);
        $pant->armarSelectAnos('');
        $pant->armarEstilo();
        $pant->armarMostrarSombra($sombra);
        $pant->armarScriptEmergente('');
        $_SESSION["generales"]["txtemergente"] = '';
        $pant->armarCuerpo($cuerpo);
        $pant->mostrar();
        unset($pant);
        exit();
    }

    public static function mostrarNit($nit)
    {
        if ($nit != 0) {
            $nit = str_replace(array(",", "-", "."), "", $nit);
            $nit = sprintf("%016s", $nit);
            $nit1 = number_format(substr($nit, 0, 15)) . '-' . substr($nit, 15, 1);
        } else {
            $nit1 = number_format(0);
        }
        return $nit1;
    }

    public static function mostrarPesos2($var)
    {
        if (trim((string) $var) == '') {
            return "-o-";
        }
        if (!is_numeric($var)) {
            return "-o-";
        }
        return "$" . number_format($var, 2, ",", ".");
    }

    public static function mostrarPesos2SinAmpersand($var)
    {
        if (trim((string) $var) == '') {
            return "-o-";
        }
        if (!is_numeric($var)) {
            return "-o-";
        }
        return number_format($var, 2, ",", ".");
    }

    public static function mostrarSmmlv2($var)
    {
        if (trim((string) $var) == '') {
            return "-o-";
        }
        if (!is_numeric($var)) {
            return "-o-";
        }
        return "SMMLV. " . number_format($var, 2, ",", ".");
    }

    public static function mostrarNumero2($var)
    {
        if (ltrim((string) $var, "0") == '') {
            return "-o-";
        }
        if (!is_numeric($var)) {
            return "-o-";
        }
        return number_format($var, 2);
    }

    public static function mostrarNumero2simple($var)
    {
        if (ltrim($var, "0") == '') {
            return "0";
        }
        if (!is_numeric($var)) {
            return "0";
        }
        return number_format($var, 2, ".", "");
    }

    public static function mostrarPantallaPredisenada($pantalla)
    {
        $txt = \funcionesGenerales::cambiarSustitutoHtml(retornarPantallaPredisenadaMysqliApi(null, $pantalla));
        $txt = str_replace("[EMAIL_ATENCION_USUARIOS]", EMAIL_ATENCION_USUARIOS, $txt);
        $txt = str_replace("[NOMBRE_ENTIDAD]", RAZONSOCIAL, $txt);
        $txt = str_replace("[TELEFONO_ATENCION_USUARIOS]", TELEFONO_ATENCION_USUARIOS, $txt);
        \funcionesGenerales::armarMensaje($txt);
        exit();
    }

    public static function mostrarPrincipal($scripth = '', $scriptb = '', $user1 = '')
    {

        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');

        //
        $pant = new template();
        $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/principal-2U-5.html');

        //
        if (DHTMLX_ACTUAL == '') {
            $pant->armarDhtmlxActual('2.6');
        } else {
            $pant->armarDhtmlxActual(DHTMLX_ACTUAL);
        }

        //
        $pant->armarScriptHeader($scripth);
        $pant->armarScriptBody($scriptb);
        $pant->armarBarraOpciones($user1);
        $pant->armarTipoUsuario($_SESSION["generales"]["tipousuario"]);
        $pant->armarIdUsuario($_SESSION["generales"]["codigousuario"]);
        $pant->armarFrameSecundarioLateral('');
        $pant->armarFrameSecundarioCentral('');

        //
        if (!defined('ACTIVADO_MODULO_SUSCRIPCIONES')) {
            define('ACTIVADO_MODULO_SUSCRIPCIONES', 'N');
        }

        if (
            substr(TIPO_EMPRESA, 0, 3) == 'cam' ||
            substr(TIPO_EMPRESA1, 0, 3) == 'cam' ||
            substr(TIPO_EMPRESA2, 0, 3) == 'cam'
        ) {
            $pant->armarCuerpoInicial(TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregConsultaExpedientes.php?accion=seleccion');
        } else {
            if (strpos(WWW_ENTIDAD, 'http')) {
                $pant->armarCuerpoInicial(WWW_ENTIDAD);
            } else {
                $pant->armarCuerpoInicial('http://' . WWW_ENTIDAD);
            }
        }

        //
        $pant->armarDate();
        $pant->armarSesion();
        $pant->armarHttpHost(HTTP_HOST);
        $pant->armarLogoEntidad();
        $pant->armarTituloAplicacion(NOMBRE_SISTEMA);
        $pant->armarCodigoEntidad(CODIGO_EMPRESA);
        $pant->armarCodigoUsuario($_SESSION["generales"]["codigousuario"]);
        $pant->armarNombreEntidad(RAZONSOCIAL);
        $pant->armarSitioWeb(HTTP_HOST);
        $pant->armarUrlSite(TIPO_HTTP . HTTP_HOST);
        $pant->armarNombreEntidad(RAZONSOCIAL);
        $pant->armarEmailAtencionUsuarios(EMAIL_ATENCION_USUARIOS);
        $pant->armarTelefonoAtencionUsuarios(TELEFONO_ATENCION_USUARIOS);
        $pant->armarFooter();

        $pant->armarTituloOpciones('Opciones');
        $pant->armarTituloBuscar('Buscar');
        $pant->armarTituloPeriodo('Periodo');
        $pant->armarTituloVersion('Versi&oacute;n');
        $pant->armarTituloUsuario('Usuario');
        $pant->armarTituloUsuarioPublico('Usuario p&uacute;blico');
        $pant->armarTituloAccesoRapido('Acceso r&aacute;pido');
        $pant->armarTituloSalir('Salir');
        $pant->armarTituloSistema(NOMBRE_SISTEMA);

        $pant->armarTituloPalabrasClave('Palabras clave');
        $pant->armarDHTMLXVERSION(DHTMLX_ACTUAL);
        $pant->armarWWWENLACE(WWW_ENTIDAD);
        $pant->armarHttpHost(HTTP_HOST);
        $pant->armarTipoHttp(TIPO_HTTP);

        if (isset($_SESSION["generales"]["txtemergente"])) {
            $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
            $_SESSION["generales"]["txtemergente"] = '';
        } else {
            $pant->armarScriptEmergente('');
            $_SESSION["generales"]["txtemergente"] = '';
        }

        $pant->armarEstilo(CODIGO_EMPRESA);
        if (defined('COLOR_BANNER')) {
            $pant->armarColor(COLOR_BANNER);
        } else {
            $pant->armarColor('Azul');
        }
        $pant->mostrar();
        unset($pant);
        exit();
    }

    /**
     * 
     * Number_format ajustado para la gesti&oacute;n de decimales
     * @param unknown_type $number
     * @param unknown_type $dec_point
     * @param unknown_type $thousands_sep
     */
    public static function my_number_format($number, $dec_point, $thousands_sep = '')
    {
        $was_neg = $number < 0; // Because +0 == -0
        $number = abs($number);

        $tmp = explode('.', $number);
        if (trim($thousands_sep) == '') {
            $out = number_format($tmp[0], 0, $dec_point, $thousands_sep);
        } else {
            $out = number_format($tmp[0], 0, $dec_point);
        }
        if (isset($tmp[1])) {
            $out .= $dec_point . $tmp[1];
        }

        if ($was_neg) {
            $out = "-$out";
        }

        return $out;
    }

    /**
     * 
     * Retorna el float de un numero, sea esta positivo o negativo
     * @param unknown_type $number
     */
    public static function my_number($number)
    {
        $number = trim($number);
        if (trim($number) == '') {
            return 0;
        }
        $len = strlen($number);
        $len1 = $len - 1;
        $signo = '+';
        if (substr($number, $len1, 1) == '-') {
            $signo = '-';
            $number = rtrim($number, "-");
        } else {
            if (substr($number, 0, 1) == '-') {
                $signo = '-';
                $number = ltrim($number, "-");
            }
        }
        $decn = 0;
        if (strpos($number, ".") === false) {
            $ent = $number;
            $dec = 0;
        } else {
            list($ent, $dec) = explode(".", $number);
        }
        switch (strlen($dec)) {
            case 0:
                $decn = 0;
                break;
            case 1:
                $decn = $dec / 10;
                break;
            case 2:
                $decn = $dec / 100;
                break;
            case 3:
                $decn = $dec / 1000;
                break;
            case 4:
                $decn = $dec / 10000;
                break;
        }
        $numbern = 0;
        $numbern = doubleval($ent) + $decn;
        if ($signo == '-') {
            $numbern = $numbern * -1;
        }
        return $numbern;
    }

    public static function my_number1($number)
    {
        $number = trim($number);
        if (trim($number) == '') {
            return 0;
        }
        $signo = '+';
        if (substr($number, 0, 1) == '-') {
            $signo = '-';
            $number = ltrim($number, "-");
        }
        $decn = 0;
        list($ent, $dec) = explode(".", $number);
        switch (strlen($dec)) {
            case 0:
                $decn = 0;
                break;
            case 1:
                $decn = $dec / 10;
                break;
            case 2:
                $decn = $dec / 100;
                break;
            case 3:
                $decn = $dec / 1000;
                break;
            case 4:
                $decn = $dec / 1000;
                break;
        }
        $numbern = 0;
        $numbern = doubleval($ent) + $decn;
        if ($signo == '-')
            $numbern = $numbern * -1;
        return $numbern;
    }

    public static function obtenerNavegador()
    {
        $user_agent = getenv("HTTP_USER_AGENT");
        // if (!isset($_SERVER['HTTP_USER_AGENT'])) {
        //    return "Other";
        // }
        // $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'MSIE') !== FALSE)
            return 'IE';
        elseif (strpos($user_agent, 'Edge') !== FALSE) //Microsoft Edge
            return 'Edge';
        elseif (strpos($user_agent, 'Trident') !== FALSE) //IE 11
            return 'IE';
        elseif (strpos($user_agent, 'Opera Mini') !== FALSE)
            return "Opera";
        elseif (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR') !== FALSE)
            return "Opera";
        elseif (strpos($user_agent, 'Firefox') !== FALSE)
            return 'Mozilla';
        elseif (strpos($user_agent, 'Chrome') !== FALSE)
            return 'Chrome';
        elseif (strpos($user_agent, 'Safari') !== FALSE)
            return "Safari";
        else
            return 'Other';
    }

    public function obtenerNombrePDF($url)
    {
        $f = explode("/", $url);
        $arch = $f[count($f) - 1];
        return $arch;
    }

    public static function ocultarIdentificacion($ide)
    {
        $idex = '**********';
        if (ltrim(trim($ide), "0") != '') {
            $len = strlen($ide);
            if ($len >= 5) {
                $idex = '****' . substr($ide, -5);
            }
        }
        return $idex;
    }

    public static function ordenarMatriz($arreglo, $campo, $inverse = false)
    {
        $position = array();
        $newRow = array();
        foreach ($arreglo as $key => $row) {
            $position[$key] = $row[$campo];
            $newRow[$key] = $row;
        }
        if ($inverse) {
            arsort($position);
        } else {
            asort($position);
        }
        $returnArray = array();
        foreach ($position as $key => $pos) {
            $returnArray[] = $newRow[$key];
        }
        return $returnArray;
    }

    public static function parsearOracion($txt)
    {

        //
        if ($txt == '') {
            return $txt;
        }

        //
        $txt = str_replace("&aacute;", "á", $txt);
        $txt = str_replace("&eacute;", "é", $txt);
        $txt = str_replace("&iacute;", "í", $txt);
        $txt = str_replace("&oacute;", "ó", $txt);
        $txt = str_replace("&uacute;", "ú", $txt);
        $txt = str_replace("&Aacute;", "Á", $txt);
        $txt = str_replace("&Eacute;", "É", $txt);
        $txt = str_replace("&Iacute;", "Í", $txt);
        $txt = str_replace("&Oacute;", "Ó", $txt);
        $txt = str_replace("&Uacute;", "Ú", $txt);
        $txt = str_replace("&ntilde;", "ñ", $txt);
        $txt = str_replace("&Ntilde;", "Ñ", $txt);
        $txt = str_replace("&nbsp;", " ", $txt);
        $txt = str_replace("&Nbsp;", " ", $txt);
        $txt = str_replace("&amp;Nbsp;", " ", $txt);

        $txt = str_replace("&AACUTE;", "Á", $txt);
        $txt = str_replace("&EACUTE;", "É", $txt);
        $txt = str_replace("&IACUTE;", "Í", $txt);
        $txt = str_replace("&OACUTE;", "Ó", $txt);
        $txt = str_replace("&UACUTE;", "Ú", $txt);
        $txt = str_replace("&NTILDE;", "Ñ", $txt);
        $txt = str_replace("&NBSP;", " ", $txt);
        $txt = str_replace("&AMP;NBSP;", " ", $txt);

        //
        // $minusculas = '1234567890abcdefghijklmnñopqrstuvwxyzáéíóú()%&$#.,-_+?*';
        $minusculas = 'abcdefghijklmnñopqrstuvwxyzáéíóú';
        $mayusculas = 'ABCDEFGHIJKLMNÑOPQRSTUVWXYZÁÉÍÓÚ';

        //
        $ar = str_split($txt);
        $imay = 0;
        $imin = 0;
        $itot = 0;
        foreach ($ar as $ar1) {
            if (strpos($minusculas, $ar1)) {
                $imin++;
            }
            if (strpos($mayusculas, $ar1)) {
                $imay++;
            }
            $itot++;
        }

        // Si no hay mayúsculas, no convierte
        if ($imay == 0) {
            unset($ar);
            return $txt;
        }

        // si las minúsculas son más del 10% del texto, no convierte.
        if ($imin > ($itot * 0.1)) {
            unset($ar);
            return $txt;
        }


        //
        $limpia = "";
        $parts = array();
        $parts = explode(" ", $txt);
        foreach ($parts as $subcadena) {
            $subcadena = trim($subcadena);
            if ($subcadena != "") {
                $limpia .= $subcadena . " ";
            }
        }
        $txt = trim($limpia);
        unset($limpia);

        //
        $txt = mb_strtolower($txt, 'UTF-8');
        $txtsalida = '';
        $i = 0;
        $fin = 'no';
        $ca = '';
        $ca2 = '';
        $ca3 = '';

        //
        $caractersiguiente = true;
        while ($fin == 'no') {
            $c = mb_substr($txt, $i, 1, 'UTF-8');
            if ($c == '.' || $c == '-' || $c == ':') {
                $caractersiguiente = true;
                $txtsalida .= $c;
            } else {
                if (trim($c) != '' && strpos($minusculas, $c) !== false) {
                    if ($caractersiguiente) {
                        $txtsalida .= strtoupper($c);
                        $caractersiguiente = false;
                    } else {
                        if ($i == 0) {
                            $txtsalida .= strtoupper($c);
                        } else {
                            $txtsalida .= $c;
                        }
                        $caractersiguiente = false;
                    }
                } else {
                    $txtsalida .= $c;
                }
            }
            $i++;
            if ($i >= strlen($txt)) {
                $fin = 'si';
            }
        }

        //
        return $txtsalida;
    }

    public static function parsearOracionNoticia($txt)
    {

        //
        if ($txt == '') {
            return $txt;
        }

        $txt = str_replace("&aacute;", "á", $txt);
        $txt = str_replace("&eacute;", "é", $txt);
        $txt = str_replace("&iacute;", "í", $txt);
        $txt = str_replace("&oacute;", "ó", $txt);
        $txt = str_replace("&uacute;", "ú", $txt);
        $txt = str_replace("&Aacute;", "Á", $txt);
        $txt = str_replace("&Eacute;", "É", $txt);
        $txt = str_replace("&Iacute;", "Í", $txt);
        $txt = str_replace("&Oacute;", "Ó", $txt);
        $txt = str_replace("&Uacute;", "Ú", $txt);
        $txt = str_replace("&ntilde;", "ñ", $txt);
        $txt = str_replace("&Ntilde;", "Ñ", $txt);
        $txt = str_replace("&nbsp;", " ", $txt);
        $txt = str_replace("&Nbsp;", " ", $txt);
        $txt = str_replace("&amp;Nbsp;", " ", $txt);

        $txt = str_replace("&AACUTE;", "Á", $txt);
        $txt = str_replace("&EACUTE;", "É", $txt);
        $txt = str_replace("&IACUTE;", "Í", $txt);
        $txt = str_replace("&OACUTE;", "Ó", $txt);
        $txt = str_replace("&UACUTE;", "Ú", $txt);
        $txt = str_replace("&NTILDE;", "Ñ", $txt);
        $txt = str_replace("&NBSP;", " ", $txt);
        $txt = str_replace("&AMP;NBSP;", " ", $txt);

        //
        // $minusculas = '1234567890abcdefghijklmnñopqrstuvwxyzáéíóú ()%&$#.,-_+?*';
        $minusculas = 'abcdefghijklmnopqrstuvwxy';
        $mayusculas = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        //
        $ar = str_split($txt);
        $imay = 0;
        $imin = 0;
        foreach ($ar as $ar1) {
            if (strpos($minusculas, $ar1)) {
                $imin++;
            }
            if (strpos($mayusculas, $ar1)) {
                $imay++;
            }
        }
        unset($ar);
        if ($imin != 0) {
            return $txt;
        }


        $limpia = "";
        $parts = array();
        $parts = explode(" ", $txt);
        foreach ($parts as $subcadena) {
            $subcadena = trim($subcadena);
            if ($subcadena != "") {
                $limpia .= $subcadena . " ";
            }
        }
        $txt = trim($limpia);
        unset($limpia);

        $txt = mb_strtolower($txt, 'UTF-8');
        $txtsalida = '';
        $i = 0;
        $fin = 'no';
        $ca = '';
        $ca2 = '';
        $ca3 = '';

        //
        $caractersiguiente = true;
        while ($fin == 'no') {
            $c = mb_substr($txt, $i, 1, 'UTF-8');
            if ($c == '.' || $c == '-' || $c == ':') {
                $caractersiguiente = true;
                $txtsalida .= $c;
            } else {
                if (trim($c) != '' && strpos($minusculas, $c) !== false) {
                    if ($caractersiguiente) {
                        $txtsalida .= strtoupper($c);
                        $caractersiguiente = false;
                    } else {
                        $txtsalida .= $c;
                        $caractersiguiente = false;
                    }
                } else {
                    $txtsalida .= $c;
                }
            }
            $i++;
            if ($i >= strlen($txt)) {
                $fin = 'si';
            }
        }

        //
        return $txtsalida;
    }

    public static function parteEntera($valor)
    {
        $tem = explode(".", (string) $valor);
        return $tem[0];
    }

    public static function programarAlertaTemprana($dbx, $tiporegistro, $liquidacion, $matricula, $proponente, $tipotramite)
    {

        //
        if ($_SESSION["generales"]["tipousuario"] == '00' || $_SESSION["generales"]["tipousuario"] == '06') {
            if (trim($_SESSION["generales"]["emailusuariocontrol"]) != '') {
                $resEmail = retornarRegistroMysqliApi($dbx, 'mreg_email_excluidos_alertas_tempranas', "email='" . $_SESSION["generales"]["emailusuariocontrol"] . "'");
                $res = retornarRegistroMysqliApi($dbx, 'mreg_alertas_tempranas', "idliquidacion=" . $liquidacion);
                if ($res === false || empty($res)) {
                    if ($tiporegistro == 'RegPro') {
                        $arrExp = retornarRegistroMysqliApi($dbx, 'mreg_est_proponentes', "proponente='" . $proponente . "'");
                        if ($arrExp && !empty($arrExp)) {
                            $arrExp["razonsocial"] = $arrExp["nombre"];
                        }
                    } else {
                        $arrExp = retornarRegistroMysqliApi($dbx, 'mreg_est_inscritos', "matricula='" . $matricula . "'");
                    }
                    if ($arrExp && !empty($arrExp)) {
                        $email = '';
                        $email = $arrExp["emailnot"];
                        if ($email == '') {
                            $email = $arrExp["emailcom"];
                        }
                        if (trim($email) != '') {
                            $asunto = 'Alerta temprana por acceso al expediente No. ' . trim($matricula . $proponente) . ' en la ' . RAZONSOCIAL;
                            $detalle = 'Señor(es)<br>';
                            $detalle .= $arrExp["razonsocial"] . '<br><br>';
                            $detalle .= 'Nos permitimos informarle que el día ' . date("Y-m-d") . ' a las ' . date("H:i:s") . ' ';
                            $detalle .= 'se solicitó en los sistemas de registro que administra la ' . RAZONSOCIAL . ' el siguiente trámite:<br><br>';
                            $detalle .= '- Expediente : ' . $matricula . $proponente . '<br>';
                            $detalle .= '- Trámite solicitado : ' . $tipotramite . '<br>';
                            $detalle .= '- Email del usuario que solicita el trámite : ' . $_SESSION["generales"]["emailusuariocontrol"] . '<br>';
                            $detalle .= '- Ip del usuario : ' . localizarIP() . '<br><br>';
                            $detalle .= 'Esta alerta se genera en cumplimiento de lo establecido en la Circular 100-000002 expedida por la ';
                            $detalle .= 'Superintendencia de Sociedades, numeral 1.1.12.5.<br><br>';
                            $detalle .= 'Cordialmente<br><br>';
                            $detalle .= 'Area de Registros Públicos<br>';
                            $detalle .= RAZONSOCIAL;

                            $arrCampos = array(
                                'idliquidacion',
                                'matricula',
                                'proponente',
                                'fecha',
                                'hora',
                                'email',
                                'celular',
                                'usuario',
                                'tipotramite',
                                'ip',
                                'textoalerta',
                                'estado'
                            );
                            $arrValores = array(
                                $liquidacion,
                                "'" . $matricula . "'",
                                "'" . $proponente . "'",
                                "'" . date("Ymd") . "'",
                                "'" . date("His") . "'",
                                "'" . addslashes($email) . "'",
                                "''", // celular
                                "'" . addslashes($_SESSION["generales"]["emailusuariocontrol"]) . "'",
                                "'" . $tipotramite . "'",
                                "'" . localizarIP() . "'",
                                "'" . addslashes($detalle) . "'",
                                "'1'" // programada
                            );
                            insertarRegistrosMysqliApi($dbx, 'mreg_alertas_tempranas', $arrCampos, $arrValores);

                            if (!defined('TIPO_AMBIENTE')) {
                                define('TIPO_AMBIENTE', 'PRUEBAS');
                            }
                            if (TIPO_AMBIENTE == 'PRODUCCION') {
                                if (!$resEmail || empty($resEmail)) {
                                    if ($_SESSION["generales"]["emailusuariocontrol"] != 'prueba@prueba.prueba') {
                                        $res = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $email, $asunto, $detalle);
                                    }
                                }
                            } else {
                                if ($_SESSION["generales"]["emailusuariocontrol"] != 'prueba@prueba.prueba') {
                                    if (isset($resEmail) && !empty($resEmail)) {
                                        $res = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $resEmail["email"], $asunto, $detalle);
                                    }
                                }
                            }
                            if ($res) {
                                $arrCampos = array(
                                    'estado'
                                );
                                $arrValores = array(
                                    "'3'" // Enviado con éxito
                                );
                                regrabarRegistrosMysqliApi($dbx, 'mreg_alertas_tempranas', $arrCampos, $arrValores, "idliquidacion=" . $liquidacion);
                            } else {
                                $arrCampos = array(
                                    'estado'
                                );
                                $arrValores = array(
                                    "'4'" // Envio con error
                                );
                                regrabarRegistrosMysqliApi($dbx, 'mreg_alertas_tempranas', $arrCampos, $arrValores, "idliquidacion=" . $liquidacion);
                            }
                        }
                    }
                }
            }
        }
    }

    public static function procesarSega($sega, $file, $extension = '', $extension1 = '', $extension2 = '', $extension3 = '', $extension4 = '')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        if (!defined('ACTIVADO_SEGA')) {
            define('ACTIVADO_SEGA', 'SEGA-RM');
        }
        if (ACTIVADO_SEGA == 'SEGA-RM' || ACTIVADO_SEGA == '') {
            $res = \funcionesGenerales::procesarSegaRm($sega, $file, $extension, $extension1, $extension2, $extension3, $extension4);
        }
        if (ACTIVADO_SEGA == 'SEGA-GNU') {
            $res = \funcionesGenerales::procesarSegaGnu($sega, $file, $extension, $extension1, $extension2, $extension3, $extension4);
        }
        return $res;
    }

    public static function procesarSegaRm($sega, $file, $extension, $extension1, $extension2, $extension3, $extension4)
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        if ($sega["conexion"] == 'no') {
            $_SESSION["generales"]["mensajeerror"] = 'No hay sega configurado, no se puede ejecutar la opci&oacute;n';
            return false;
        }

        if ($sega["conexion"] == 'local') {
            $command = $sega["runcobol"] . ' ' . $sega["path"] . '/enruta A="' . PATH_ABSOLUTO_SITIO . '/tmpmig/' . $_SESSION["generales"]["codigoempresa"] . '/' . $file . '"';
            \logApi::general2('rmcobol', '', $command);
            exec($command);
            return true;
        }

        set_include_path(get_include_path() . PATH_SEPARATOR . PATH_ABSOLUTO_SITIO . '/includes/phpseclib');
        require_once('Net/SSH2.php');
        require_once('Net/SFTP.php');
        ini_set('display_errors', '0');
        if (!defined('NET_SSH2_LOGGING')) {
            define('NET_SSH2_LOGGING', NET_SSH2_LOG_COMPLEX);
        }

        // define('NET_SSH2_LOGGING', NET_SSH2_LOG_SIMPLE);
        // Establece la conexi&oacute;n sftp
        try {
            $sftp1 = new Net_SFTP($sega["host"], $sega["port"]);
        } catch (Exception $e) {
            $_SESSION["generales"]["mensajeerror"] = 'sftp: Excepci&oacute;n capturada: ' . $e->getMessage();
            return false;
        }

        // Autentica la conexi&oacute;n sftp
        if (!$sftp1->login($sega["user"], $sega["password"])) {
            \logApi::general2('rmcobol', '', $sftp1->getLog());
            // unset ($sftp1);
            $_SESSION["generales"]["mensajeerror"] = 'sftp: No fue posible conectarse al servidor SEGA : ' . $sftp1->getLog();
            unset($sftp1);
            return false;
        }

        // Cambia el directorio
        if (!$sftp1->chdir($sega["path"] . '/data')) {
            if (!$sftp1->mkdir($sega["path"] . '/data')) {
                $_SESSION["generales"]["mensajeerror"] = 'sftp: No fue posible ubicarse en el directorio ' . $sega["path"] . ' del servidor SEGA';
                return false;
            }
        }

        // Carga el archivo de parametros el servidor sftp
        if (!$sftp1->put($file, '../../tmpmig/' . $_SESSION["generales"]["codigoempresa"] . '/' . $file, NET_SFTP_LOCAL_FILE)) {
            unset($sftp1);
            $_SESSION["generales"]["mensajeerror"] = 'sftp: No fue posible subir el archivo de parametros para el comando runcobol';
            return false;
        }

        // Establece la conexion ssh con el servidor sega
        $ssh = new Net_SSH2($sega["host"]);
        if (!$ssh->login($sega["user"], $sega["password"])) {
            $_SESSION["generales"]["mensajeerror"] = 'ssh: No fue posible establecer conexi&oacute;n con el servidor SEGA, error de autenticaci&oacute;n';
            return false;
        }

        //
        $ssh->setTimeout(30);

        // Ejecuta el comando runcobol
        $resx = $ssh->exec($sega["runcobol"] . ' ' . $sega["path"] . '/enruta A="' . $sega["path"] . '/data/' . $file . '" > /tmp/cobol-sii-' . session_id() . '.log');
        // foreach ($ssh->getLog() as $linx) {
        \logApi::general2('rmcobol', '', 'Ejecutando comando runcobol:' . $ssh->getLog());
        // }

        if (!$resx) {
            $_SESSION["generales"]["mensajeerror"] = 'ssh: No fue posible ejecutar el comando Runcobol en forma remota';
            return false;
        }

        // Descarga la respuesta del proceso runcobol
        if (trim($extension) != '') {
            if (!$sftp1->get($file . '.' . $extension, '../../tmpmig/' . $_SESSION["generales"]["codigoempresa"] . '/' . $file . '.' . $extension)) {
                unset($sftp1);
                $_SESSION["generales"]["mensajeerror"] = 'sftp: No fue posible descargar el archivo de respuesta desde el servidor SEGA (extension)';
                return false;
            }
        }

        // Descarga la respuesta del proceso runcobol
        if (trim($extension1) != '') {
            if (!$sftp1->get($file . '.' . $extension1, '../../tmpmig/' . $_SESSION["generales"]["codigoempresa"] . '/' . $file . '.' . $extension1)) {
                unset($sftp1);
                $_SESSION["generales"]["mensajeerror"] = 'sftp: No fue posible descargar el archivo de respuesta desde el servidor SEGA (extension1)';
                return false;
            }
        }

        if (trim($extension2) != '') {
            if (!$sftp1->get($file . '.' . $extension2, '../../tmpmig/' . $_SESSION["generales"]["codigoempresa"] . '/' . $file . '.' . $extension2)) {
                unset($sftp1);
                $_SESSION["generales"]["mensajeerror"] = 'sftp: No fue posible descargar el archivo de respuesta desde el servidor SEGA (extension2)';
                return false;
            }
        }

        if (trim($extension3) != '') {
            if (!$sftp1->get($file . '.' . $extension3, '../../tmpmig/' . $_SESSION["generales"]["codigoempresa"] . '/' . $file . '.' . $extension3)) {
                unset($sftp1);
                $_SESSION["generales"]["mensajeerror"] = 'sftp: No fue posible descargar el archivo de respuesta desde el servidor SEGA (extension3)';
                return false;
            }
        }

        if (trim($extension4) != '') {
            if (!$sftp1->get($file . '.' . $extension4, '../../tmpmig/' . $_SESSION["generales"]["codigoempresa"] . '/' . $file . '.' . $extension4)) {
                unset($sftp1);
                $_SESSION["generales"]["mensajeerror"] = 'sftp: No fue posible descargar el archivo de respuesta desde el servidor SEGA (extension4)';
                return false;
            }
        }

        // Cierra las conexiones con el servidor runcobol
        unset($ssh);
        unset($sftp1);

        return true;
    }

    function procesarSegaGnu($sega, $file, $extension = '', $extension1 = '', $extension2 = '', $extension3 = '', $extension4 = '')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        //
        $pathincluir = '';
        $proceso = '';
        $arregloLineas = file('../../tmpmig/' . $_SESSION["generales"]["codigoempresa"] . '/' . $file);
        foreach ($arregloLineas as $l) {
            if (substr($l, 0, 10) == '##PROCESO=') {
                $proceso = substr($l, 10);
            }
            if (substr($l, 0, 22) == '##DIRECTORIOPROGRAMAS=') {
                $pathincluir = substr($l, 22);
            }
        }

        // set_include_path(get_include_path() . PATH_SEPARATOR . $pathincluir);
        //
        if ($sega["conexion"] == 'no') {
            $_SESSION["generales"]["mensajeerror"] = 'No hay sega configurado, no se puede ejecutar la opci&oacute;n';
            return false;
        }

        //
        if ($sega["conexion"] == 'local') {
            $command = '../../segagnu.sh' . ' "' . trim($proceso) . '" "' . $_SESSION["generales"]["codigoempresa"] . '" "' . $file . '"';
            // echo $command . '<br>';
            exec($command);
            // var_dump($output) . '<br>';
            // echo $return . '<br>';
            return true;
        }

        //
        set_include_path(get_include_path() . PATH_SEPARATOR . PATH_ABSOLUTO_SITIO . '/includes/phpseclib');
        require_once('Net/SSH2.php');
        require_once('Net/SFTP.php');
        ini_set('display_errors', '1');
        if (!defined('NET_SSH2_LOGGING')) {
            define('NET_SSH2_LOGGING', NET_SSH2_LOG_COMPLEX);
        }
        // define('NET_SSH2_LOGGING', NET_SSH2_LOG_SIMPLE);
        // Establece la conexi&oacute;n sftp
        try {
            $sftp1 = new Net_SFTP($sega["host"], $sega["port"]);
        } catch (Exception $e) {
            $_SESSION["generales"]["mensajeerror"] = 'sftp: Excepci&oacute;n capturada: ' . $e->getMessage();
            return false;
        }

        // Autentica la conexi&oacute;n sftp
        if (!$sftp1->login($sega["user"], $sega["password"])) {
            \logApi::general2('cobcrun', '', $sftp1->getLog());
            // unset ($sftp1);
            $_SESSION["generales"]["mensajeerror"] = 'sftp: No fue posible conectarse al servidor SEGA : ' . $sftp1->getLog();
            unset($sftp1);
            return false;
        }

        // Cambia el directorio
        if (!$sftp1->chdir($sega["path"] . '/data')) {
            if (!$sftp1->mkdir($sega["path"] . '/data')) {
                $_SESSION["generales"]["mensajeerror"] = 'sftp: No fue posible ubicarse en el directorio ' . $sega["path"] . ' del servidor SEGA';
                return false;
            }
        }

        // Carga el archivo de par&aacute;metros el servidor sftp
        if (!$sftp1->put($file, '../../tmpmig/' . $_SESSION["generales"]["codigoempresa"] . '/' . $file, NET_SFTP_LOCAL_FILE)) {
            unset($sftp1);
            $_SESSION["generales"]["mensajeerror"] = 'sftp: No fue posible subir el archivo de parametros para el comando runcobol';
            return false;
        }

        // Establece la conexi&oacute;n ssh con el servidor sega
        $ssh = new Net_SSH2($sega["host"]);
        if (!$ssh->login($sega["user"], $sega["password"])) {
            $_SESSION["generales"]["mensajeerror"] = 'ssh: No fue posible establecer conexi&oacute;n con el servidor SEGA, error de autenticaci&oacute;n';
            return false;
        }

        // Ejecuta el comando cobcrun
        $resx = $ssh->exec($sega["cobcrun"] . ' ' . $sega["path"] . '/enruta FENT="' . $sega["path"] . '/data/' . $file . '" > /tmp/cobol-sii-' . session_id() . '.log');
        // foreach ($ssh->getLog() as $linx) {
        \logApi::general2('cobcrun', '', 'Ejecutando comando cobcrun:' . $ssh->getLog());
        // }

        if (!$resx) {
            $_SESSION["generales"]["mensajeerror"] = 'ssh: No fue posible ejecutar el comando Runcobol en forma remota';
            return false;
        }

        // Descarga la respuesta del proceso cobcrun
        if (trim($extension) != '') {
            if (!$sftp1->get($file . '.' . $extension, '../../tmpmig/' . $_SESSION["generales"]["codigoempresa"] . '/' . $file . '.' . $extension)) {
                unset($sftp1);
                $_SESSION["generales"]["mensajeerror"] = 'sftp: No fue posible descargar el archivo de respuesta desde el servidor SEGA (extension)';
                return false;
            }
        }

        // Descarga la respuesta del proceso cobcrun
        if (trim($extension1) != '') {
            if (!$sftp1->get($file . '.' . $extension1, '../../tmpmig/' . $_SESSION["generales"]["codigoempresa"] . '/' . $file . '.' . $extension1)) {
                unset($sftp1);
                $_SESSION["generales"]["mensajeerror"] = 'sftp: No fue posible descargar el archivo de respuesta desde el servidor SEGA (extension1)';
                return false;
            }
        }

        if (trim($extension2) != '') {
            if (!$sftp1->get($file . '.' . $extension2, '../../tmpmig/' . $_SESSION["generales"]["codigoempresa"] . '/' . $file . '.' . $extension2)) {
                unset($sftp1);
                $_SESSION["generales"]["mensajeerror"] = 'sftp: No fue posible descargar el archivo de respuesta desde el servidor SEGA (extension2)';
                return false;
            }
        }

        if (trim($extension3) != '') {
            if (!$sftp1->get($file . '.' . $extension3, '../../tmpmig/' . $_SESSION["generales"]["codigoempresa"] . '/' . $file . '.' . $extension3)) {
                unset($sftp1);
                $_SESSION["generales"]["mensajeerror"] = 'sftp: No fue posible descargar el archivo de respuesta desde el servidor SEGA (extension3)';
                return false;
            }
        }

        if (trim($extension4) != '') {
            if (!$sftp1->get($file . '.' . $extension4, '../../tmpmig/' . $_SESSION["generales"]["codigoempresa"] . '/' . $file . '.' . $extension4)) {
                unset($sftp1);
                $_SESSION["generales"]["mensajeerror"] = 'sftp: No fue posible descargar el archivo de respuesta desde el servidor SEGA (extension4)';
                return false;
            }
        }

        // Cierra las conexiones con el servidor runcobol
        unset($ssh);
        unset($sftp1);

        return true;
    }

    public static function retornarSecuencia($dbx = null, $sec = null)
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        if ($dbx === null) {
            $cerrarMysqli = 'si';
            $dbx = conexionMysqliApi();
        } else {
            $cerrarMysqli = 'no';
        }
        $res = retornarRegistroMysqliApi($dbx, 'secuencias', "tipo='" . $sec . "'");
        if ($res === false) {
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return false;
        }
        if (empty($res)) {
            $retornar = 0;
        } else {
            $retornar = $res["consecutivo"];
        }

        //
        $retornar++;

        //
        if ($sec == 'LIQUIDACION-REGISTROS') {
            $ok = 'no';
            while ($ok == 'no') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_liquidacion', "idliquidacion=" . $retornar) > 0) {
                    $retornar++;
                } else {
                    $arrCampos = array(
                        'idliquidacion',
                        'fecha',
                        'hora',
                        'idestado'
                    );
                    $arrValores = array(
                        $retornar,
                        "'" . date("Ymd") . "'",
                        "'" . date("H:i:s") . "'",
                        "'01'"
                    );
                    insertarRegistrosMysqliApi($dbx, 'mreg_liquidacion', $arrCampos, $arrValores);
                    $ok = 'si';
                }
            }
        }

        if ($sec == 'DEVOLUCION-REGISTROS') {
            $ok = 'no';
            while ($ok == 'no') {
                if (contarRegistrosMysqliApi($dbx, 'mreg_devoluciones_nueva', "iddevolucion=" . $retornar) > 0) {
                    $retornar++;
                } else {
                    $ok = 'si';
                }
            }
        }

        if ($sec == 'RADICACION-REPORTES-EE') {
            $ok = 'no';
            while ($ok == 'no') {
                if (contarRegistrosMyqliApi($dbx, 'mreg_reportesradicados', "idradicacion='" . ltrim($retornar, "0") . "'") > 0) {
                    $retornar++;
                } else {
                    $ok = 'si';
                }
            }
        }

        if (empty($res)) {
            $arrCampos = array(
                'tipo',
                'consecutivo'
            );
            $arrValores = array(
                "'" . $sec . "'",
                $retornar
            );
            insertarRegistrosMysqliApi($dbx, 'secuencias', $arrCampos, $arrValores);
        } else {
            $arrCampos = array(
                'consecutivo'
            );
            $arrValores = array(
                $retornar
            );
            regrabarRegistrosMysqliApi($dbx, 'secuencias', $arrCampos, $arrValores, "tipo='" . $sec . "'");
        }

        //
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }
        return $retornar;
    }

    public static function retornarValorLimpio($val)
    {
        $val = ltrim($val, "0");
        $val = ltrim($val, " ");
        $val = rtrim($val, " ");
        $eliminar = array("-", ",", "_", " ", "$", "/");
        $val = str_replace($eliminar, "", $val);
        return $val;
    }

    public static function retornarLibroFormato2019($libro)
    {
        $txtLibro = '';
        switch ($libro) {
            case "RM01":
                $txtLibro = 'I';
                break;
            case "RM02":
                $txtLibro = 'II';
                break;
            case "RM03":
                $txtLibro = 'III';
                break;
            case "RM04":
                $txtLibro = 'IV';
                break;
            case "RM05":
                $txtLibro = 'V';
                break;
            case "RM06":
                $txtLibro = 'VI';
                break;
            case "RM07":
                $txtLibro = 'VII';
                break;
            case "RM08":
                $txtLibro = 'VIII';
                break;
            case "RM09":
                $txtLibro = 'IX';
                break;
            case "RM10":
                $txtLibro = 'X';
                break;
            case "RM11":
                $txtLibro = 'XI';
                break;
            case "RM12":
                $txtLibro = 'XII';
                break;
            case "RM13":
                $txtLibro = 'XIII';
                break;
            case "RM14":
                $txtLibro = 'XIV';
                break;
            case "RM15":
                $txtLibro = 'XV';
                break;
            case "RM16":
                $txtLibro = 'XVI';
                break;
            case "RM17":
                $txtLibro = 'XVII';
                break;
            case "RM18":
                $txtLibro = 'XVIII';
                break;
            case "RM19":
                $txtLibro = 'XIX';
                break;
            case "RM20":
                $txtLibro = 'XX';
                break;
            case "RM21":
                $txtLibro = 'XXI';
                break;
            case "RM22":
                $txtLibro = 'XXII';
                break;
            case "RE51":
                $txtLibro = 'I del Registro de Entidades sin Ánimo de Lucro';
                break;
            case "RE52":
                $txtLibro = 'II del Registro de Entidades sin Ánimo de Lucro';
                break;
            case "RE53":
                $txtLibro = 'III del Registro de Entidades de la Economía Solidaria';
                break;
            case "RE54":
                $txtLibro = 'IV del Registro de Entidades de Veeduría Ciudadana';
                break;
            case "RE55":
                $txtLibro = 'V del Registro de las Entidades Extranjeras de Derecho Privado sin Ánimo de Lucro';
                break;
        }
        return $txtLibro;
    }

    public static function retornarNumeroLimpio($val)
    {
        $val = ltrim($val, "0");
        $val = ltrim($val, " ");
        $val = rtrim($val, " ");
        $eliminar = array(",", "_", " ", "$");
        $val = str_replace($eliminar, "", $val);
        return $val;
    }

    public static function retornarSelectTipoDocumental($dbx, $id = '', $modgra = '')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');

        if (trim($modgra) == '') {
            $lisDoc = retornarRegistrosMysqliApi($dbx, 'bas_tipodoc', "eliminado<>'SI'", "idtipodoc");
        } else {
            $lisMod = retornarRegistrosMysqliApi($dbx, 'bas_tipodoc_modulos', "idmodulo='" . $modgra . "'", "idtipodoc");
            $lisDoc = array();
            foreach ($lisMod as $mod) {
                $dox = retornarRegistroMysqliApi($dbx, 'bas_tipodoc', "idtipodoc='" . $mod["idtipodoc"] . "'");
                if (($dox === false) || (empty($dox))) {
                    $x = 1;
                } else {
                    $lisDoc[] = $dox;
                }
            }
        }
        $retornar = '';
        if (trim($id) == '') {
            $retornar .= '<option value="" selected>Seleccione un tipo de documento</option>';
        } else {
            $retornar .= '<option value="">Seleccione un tipo de documento</option>';
        }
        foreach ($lisDoc as $doc) {
            if ($doc["idtipodoc"] == $id) {
                $retornar .= '<option value=' . $doc["idtipodoc"] . ' selected>' . $doc["idtipodoc"] . ' - ' . substr(\funcionesGenerales::utf8_decode($doc["nombre"]), 0, 60) . '</option>';
            } else {
                $retornar .= '<option value=' . $doc["idtipodoc"] . '>' . $doc["idtipodoc"] . ' - ' . substr(\funcionesGenerales::utf8_decode($doc["nombre"]), 0, 60) . '</option>';
            }
        }
        unset($lisDoc);
        unset($doc);
        unset($lisMod);
        unset($mod);
        return $retornar;
    }

    public static function retornarSelectUsuariosPerfil($dbx, $filtro, $id)
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        $query = '';
        if ($filtro == 'digitacion') {
            $query = "select * from usuarios where idcodigosirepdigitacion<>'' and eliminado<>'SI' and fechaactivacion<>'' and (fechainactivacion='' or fechainactivacion='00000000') order by nombreusuario";
        }
        if ($filtro == 'registro') {
            $query = "select * from usuarios where idcodigosirepregistro<>'' and eliminado<>'SI' and fechaactivacion<>'' and (fechainactivacion='' or fechainactivacion='00000000') order by nombreusuario";
        }
        if ($filtro == 'registrales') {
            $query = "select * from usuarios where idtipousuario = '05' and eliminado<>'SI' and fechaactivacion<>'' and (fechainactivacion='' or fechainactivacion='00000000') order by nombreusuario";
        }
        if ($filtro == 'confirma') {
            $query = "select * from usuariosfirmas where 1=1";
        }


        $result = ejecutarQueryMysqliApi($dbx, $query);
        $retornar = '';
        if (!empty($result)) {
            if (trim($id) == '') {
                $retornar .= '<option value="" selected>Seleccione ...</option>';
            } else {
                $retornar .= '<option value="">Seleccione ...</option>';
            }
            foreach ($result as $res) {
                if ($filtro == 'digitacion') {
                    if ($res["idcodigosirepdigitacion"] == $id) {
                        $retornar .= '<option value=' . $res["idcodigosirepdigitacion"] . ' selected>' . \funcionesGenerales::utf8_decode($res["nombreusuario"]) . '</option>';
                    } else {
                        $retornar .= '<option value=' . $res["idcodigosirepdigitacion"] . '>' . \funcionesGenerales::utf8_decode($res["nombreusuario"]) . '</option>';
                    }
                }
                if ($filtro == 'registro') {
                    if ($res["idcodigosirepregistro"] == $id) {
                        $retornar .= '<option value=' . $res["idcodigosirepregistro"] . ' selected>' . \funcionesGenerales::utf8_decode($res["nombreusuario"]) . '</option>';
                    } else {
                        $retornar .= '<option value=' . $res["idcodigosirepregistro"] . '>' . \funcionesGenerales::utf8_decode($res["nombreusuario"]) . '</option>';
                    }
                }
                if ($filtro == 'registrales') {
                    if ($res["idusuario"] == $id) {
                        $retornar .= '<option value=' . $res["idusuario"] . ' selected>' . \funcionesGenerales::utf8_decode($res["nombreusuario"]) . '</option>';
                    } else {
                        $retornar .= '<option value=' . $res["idusuario"] . '>' . \funcionesGenerales::utf8_decode($res["nombreusuario"]) . '</option>';
                    }
                }
                if ($filtro == 'confirma') {
                    $reg2 = retornarRegistroMysqliApi($dbx, 'usuarios', "idusuario='" . $res["idusuario"] . "'");
                    if (!(($reg2 === false) || (empty($reg2)))) {
                        if ($reg2["idusuario"] == $id) {
                            $retornar .= '<option value=' . $reg2["idusuario"] . ' selected>' . \funcionesGenerales::utf8_decode($reg2["nombreusuario"]) . '</option>';
                        } else {
                            $retornar .= '<option value=' . $reg2["idusuario"] . '>' . \funcionesGenerales::utf8_decode($reg2["nombreusuario"]) . '</option>';
                        }
                    }
                }
            }
        } else {
            $retornar .= '<option value="" selected>No hay usuarios que tengan firma habilitada</option>';
        }
        $_SESSION["generales"]["mensajeerror"] = '';
        return $retornar;
    }

    /**
     * 
     * @param type $cc
     * @return string
     */
    public static function retornarUrlSiiCoreProduccion($cc)
    {
        $salida = '';
        switch ($cc) {
            case "01":
                $salida = 'https://siiarmenia.confecamaras.co';
                break;
            case "02":
                $salida = 'https://siibarrancabermeja.confecamaras.co';
                break;
            case "06":
                $salida = 'https://siibuenaventura.confecamaras.co';
                break;
            case "07":
                $salida = 'https://siibuga.confecamaras.co';
                break;
            case "10":
                $salida = 'https://siicartago.confecamaras.co';
                break;
            case "11":
                $salida = 'https://siicucuta.confecamaras.co';
                break;
            case "12":
                $salida = 'https://siichinchina.confecamaras.co';
                break;
            case "13":
                $salida = 'https://siiduitama.confecamaras.co';
                break;
            case "14":
                $salida = 'https://siigirardot.confecamaras.co';
                break;
            case "15":
                $salida = 'https://siihonda.confecamaras.co';
                break;
            case "16":
                $salida = 'https://siiibague.confecamaras.co';
                break;
            case "17":
                $salida = 'https://siiipiales.confecamaras.co';
                break;
            case "18":
                $salida = 'https://siidorada.confecamaras.co';
                break;
            case "19":
                $salida = 'https://siimagangue.confecamaras.co';
                break;
            case "20":
                $salida = 'https://siimanizales.confecamaras.co';
                break;
            case "22":
                $salida = 'https://siimonteria.confecamaras.co';
                break;
            case "23":
                $salida = 'https://siineiva.confecamaras.co';
                break;
            case "24":
                $salida = 'https://siipalmira.confecamaras.co';
                break;
            case "25":
                $salida = 'https://siipamplona.confecamaras.co';
                break;
            case "26":
                $salida = 'https://siipasto.confecamaras.co';
                break;
            case "27":
                $salida = 'https://siipereira.confecamaras.co';
                break;
            case "28":
                $salida = 'https://siicauca.confecamaras.co';
                break;
            case "30":
                $salida = 'https://siiguajira.confecamaras.co';
                break;
            case "31":
                $salida = 'https://siisanandres.confecamaras.co';
                break;
            case "32":
                $salida = 'https://siisantamarta.confecamaras.co';
                break;
            case "33":
                $salida = 'https://siisantarosa.confecamaras.co';
                break;
            case "34":
                $salida = 'https://siisincelejo.confecamaras.co';
                break;
            case "35":
                $salida = 'https://siisogamoso.confecamaras.co';
                break;
            case "36":
                $salida = 'https://siitulua.confecamaras.co';
                break;
            case "37":
                $salida = 'https://siitumaco.confecamaras.co';
                break;
            case "38":
                $salida = 'https://siitunja.confecamaras.co';
                break;
            case "39":
                $salida = 'https://siivalledupar.confecamaras.co';
                break;
            case "40":
                $salida = 'https://siivillavicencio.confecamaras.co';
                break;
            case "41":
                $salida = 'https://siiflorencia.confecamaras.co';
                break;
            case "42":
                $salida = 'https://siiamazonas.confecamaras.co';
                break;
            case "43":
                $salida = 'https://siisevilla.confecamaras.co';
                break;
            case "44":
                $salida = 'https://siiuraba.confecamaras.co';
                break;
            case "45":
                $salida = 'https://siisuryorientetolima.confecamaras.co';
                break;
            case "46":
                $salida = 'https://siiputumayo.confecamaras.co';
                break;
            case "47":
                $salida = 'https://siifacatativa.confecamaras.co';
                break;
            case "48":
                $salida = 'https://siiarauca.confecamaras.co';
                break;
            case "49":
                $salida = 'https://siiocana.confecamaras.co';
                break;
            case "50":
                $salida = 'https://siicasanare.confecamaras.co';
                break;
            case "51":
                $salida = 'https://siiorienteantioqueno.confecamaras.co';
                break;
            case "52":
                $salida = 'https://siimmedio.confecamaras.co';
                break;
            case "53":
                $salida = 'https://siiaguachica.confecamaras.co';
                break;
            case "54":
                $salida = 'https://siidosquebradas.confecamaras.co';
                break;
            case "55":
                $salida = 'https://siiaburrasur.confecamaras.co';
                break;
            case "56":
                $salida = 'https://siipiedemonte.confecamaras.co';
                break;
            case "57":
                $salida = 'https://siisanjsoe.confecamaras.co';
                break;
            case "58":
                $salida = 'https://siisoacha.confecamaras.co';
                break;
        }
        return $salida;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $usua
     * @return string
     */
    public static function retornarUsuarioBase($mysqli, $usua = '')
    {
        if (substr(strtoupper($usua), 0, 6) != 'ADMGEN') {
            $result = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $usua . "'");
            if ($result && !empty($result)) {
                $arreglo = $result;
                $arreglo["nombreusuario"] = $arreglo["nombreusuario"];
                $arreglo["email"] = $arreglo["email"];
                $arreglo["email_privado"] = $arreglo["email_privado"];
                $arreglo["direccion"] = $arreglo["direccion"];
                $arreglo["nombreempresa"] = $arreglo["nombreempresa"];
            }
            if ($arreglo === false) {
                $arreglo = false;
            }
            unset($result);
        } else {
            $arreglo = array();
            $arreglo["idusuario"] = $usua;
            $arreglo["nombreusuario"] = 'ADMINISTRADOR GENERAL - SOPORTE';
            $arreglo["idtipousuario"] = '01';
            $arreglo["idtipousuariodesarrollo"] = '1';
            $arreglo["idtipousuarioexterno"] = '9';
            $arreglo["escajero"] = 'SI';
            $arreglo["escensador"] = 'SI';
            $arreglo["essa"] = 'SI';
            $arreglo["esrue"] = 'NO';
            $arreglo["eswww"] = 'NO';
            $arreglo["esreversion"] = 'NO';
            $arreglo["eswww"] = 'NO';
            $arreglo["esdispensador"] = 'NO';
            $arreglo["puedecerrarcaja"] = 'SI';
            $arreglo["visualizatotales"] = 'SI';
            $arreglo["abogadocoordinador"] = 'SI';
            $arreglo["idtipoidentificacion"] = '1';
            $arreglo["identificacion"] = '79048506';
            $arreglo["fechacreacion"] = date("Ymd");
            $arreglo["fechaactivacion"] = date("Ymd");
            $arreglo["fechainactivacion"] = '';
            $arreglo["email"] = 'jnieto@confecamaras.org.co';
            $arreglo["email_privado"] = 'jnieto@confecamaras.org.co';
            $arreglo["fechacreacion"] = date("Ymd");
            $arreglo["loginemailusuario"] = '';
            $arreglo["passwordemailusuario"] = '';
            $arreglo["idperfildocumentacion"] = 0;
            $arreglo["telefonos"] = '';
            $arreglo["celular"] = '';
            $arreglo["direccion"] = '';
            $arreglo["idmunicipio"] = '';
            $arreglo["idpais"] = '';
            $arreglo["nitempresa"] = '';
            $arreglo["nombreempresa"] = '';
            $arreglo["idcargo"] = 0;
            $arreglo["idccos"] = '';
            $arreglo["idcodigosirep"] = '';
            $arreglo["idsede"] = '01';
            $arreglo["password"] = \funcionesGenerales::retornarClaveMaestra();
            $arreglo["fechacambioclave"] = date("Ymd");
            $arreglo["fechaultimoingreso"] = date("Ymd");
            $arreglo["horaultimoingreso"] = date("H:i:s");
            $arreglo["intentoslogueo"] = 0;
            $arreglo["eliminado"] = 'NO';
            $arreglo["foto"] = '';
            $arreglo["foto1"] = '';
            $arreglo["idcodigosirepcaja"] = '';
            $arreglo["idcodigosirepdigitacion"] = '';
            $arreglo["idcodigosirepregistro"] = '';
            $arreglo["controlapresupuesto"] = 'S';
            $arreglo["controlverificacion"] = 'VE';
        }
        if (!isset($arreglo["essa"])) {
            $arreglo["essa"] = 'NO';
        }
        return $arreglo;
    }

    /**
     * 
     * @param type $usua
     * @param type $identificacion
     * @return bool|string
     */
    public static function retornarUsuario($usua = '', $identificacion = '')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');

        if (substr(strtoupper($usua), 0, 6) != 'ADMGEN') {
            try {
                $mysqli = conexionMysqliApi();
            } catch (Excepcion $e) {
                return false;
            }
            $res = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $usua . "'");
            if ($res === false || empty($res)) {
                $mysqli->close();
                return false;
            }
            $arreglo = $res;
            $arr1 = retornarRegistrosMysqliApi($mysqli, "bas_tagsbandejaentrada", "1=1", "id");
            foreach ($arr1 as $ar) {
                if ($ar["idestado"] == 'A') {
                    if (contarRegistrosMysqliApi($mysqli, 'usuariostagsbandejaentrada', "idusuario='" . $usua . "' and idtag='" . $ar["id"] . "'") == 0) {
                        $arreglo["tags" . $ar["id"]] = 'N';
                    } else {
                        $arreglo["tags" . $ar["id"]] = 'S';
                    }
                }
            }
            $mysqli->close();
            unset($res);
            unset($arr1);
        } else {
            $arreglo = array();
            $arreglo["idusuario"] = strtoupper($usua);
            $arreglo["nombreusuario"] = 'ADMINISTRADOR GENERAL - SOPORTE';
            $arreglo["tipousuario"] = '01';
            $arreglo["idtipousuario"] = '01';
            $arreglo["idtipousuariodesarrollo"] = '1';
            $arreglo["idtipousuarioexterno"] = '9';
            $arreglo["idtipousuariofinanciero"] = '9';
            $arreglo["escajero"] = 'SI';
            $arreglo["esbanco"] = 'NO';
            $arreglo["gastoadministrativo"] = 'SI';
            $arreglo["escensador"] = 'SI';
            $arreglo["essa"] = 'SI';
            $arreglo["esrue"] = 'NO';
            $arreglo["eswww"] = 'NO';
            $arreglo["esreversion"] = 'NO';
            $arreglo["esdispensador"] = 'NO';
            $arreglo["puedecerrarcaja"] = 'SI';
            $arreglo["visualizatotales"] = 'SI';
            $arreglo["abogadocoordinador"] = 'SI';
            $arreglo["idtipoidentificacion"] = '1';
            $arreglo["identificacion"] = $identificacion;
            $arreglo["fechacreacion"] = date("Ymd");
            $arreglo["fechaactivacion"] = date("Ymd");
            $arreglo["fechainactivacion"] = '';
            $arreglo["fechaexpiracion"] = '';
            $arreglo["email"] = 'jnietot@gmail.com';
            $arreglo["email_privado"] = 'jnietot@gmail.com';
            $arreglo["fechacreacion"] = date("Ymd");
            $arreglo["loginemailusuario"] = '';
            $arreglo["passwordemailusuario"] = '';
            $arreglo["idperfildocumentacion"] = 0;
            $arreglo["telefonos"] = '';
            $arreglo["celular"] = '';
            $arreglo["direccion"] = '';
            $arreglo["idmunicipio"] = '';
            $arreglo["idpais"] = '';
            $arreglo["nitempresa"] = '';
            $arreglo["nombreempresa"] = '';
            $arreglo["idcargo"] = 0;
            $arreglo["idccos"] = '';
            $arreglo["idccospublico"] = '';
            $arreglo["idccosprivado"] = '';
            $arreglo["idcodigosirep"] = '';
            $arreglo["idsede"] = '01';
            if ($arreglo["idusuario"] == 'ADMGEN99') {
                $arreglo["password"] = password_hash('Cafaivda2010*', PASSWORD_DEFAULT);
            } else {
                $arreglo["password"] = \funcionesGenerales::retornarClaveMaestra(strtoupper($usua), $identificacion);
            }
            $arreglo["fechacambioclave"] = date("Ymd");
            $arreglo["fechaultimoingreso"] = date("Ymd");
            $arreglo["horaultimoingreso"] = date("H:i:s");
            $arreglo["intentoslogueo"] = 0;
            $arreglo["eliminado"] = 'NO';
            $arreglo["foto"] = '';
            $arreglo["foto1"] = '';
            $arreglo["idcodigosirepcaja"] = '';
            $arreglo["idcodigosirepdigitacion"] = '';
            $arreglo["idcodigosirepregistro"] = '';
            $arreglo["controlapresupuesto"] = 'S';
            $arreglo["controlverificacion"] = 'VE';
            $arreglo["mesavotacion"] = '';
            try {
                $mysqli = conexionMysqliApi();
                $arr1 = retornarRegistrosMysqliApi($mysqli, "bas_tagsbandejaentrada", "1=1", "id");
                $mysqli->close();
                foreach ($arr1 as $ar) {
                    if ($ar["idestado"] == 'A') {
                        $arreglo["tags" . $ar["id"]] = 'S';
                    }
                }
                unset($arr1);
            } catch (Exception $e) {
                return false;
            }
            unset($arr1);
        }
        if (!isset($arreglo["essa"])) {
            $arreglo["essa"] = 'NO';
        }
        return $arreglo;
    }

    public static function retornarUsuarioSii($mysqli, $usua = '')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');

        if (substr(strtoupper($usua), 0, 6) != 'ADMGEN') {
            $res = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $usua . "'");
            if ($res === false || empty($res)) {
                return false;
            }
            $arreglo = $res;
            $arr1 = retornarRegistrosMysqliApi($mysqli, "bas_tagsbandejaentrada", "1=1", "id");
            foreach ($arr1 as $ar) {
                if ($ar["idestado"] == 'A') {
                    if (contarRegistrosMysqliApi($mysqli, 'usuariostagsbandejaentrada', "idusuario='" . $usua . "' and idtag='" . $ar["id"] . "'") == 0) {
                        $arreglo["tags" . $ar["id"]] = 'N';
                    } else {
                        $arreglo["tags" . $ar["id"]] = 'S';
                    }
                }
            }
            unset($res);
            unset($arr1);
        } else {
            $arreglo = array();
            $arreglo["idusuario"] = strtoupper($usua);
            $arreglo["nombreusuario"] = 'ADMINISTRADOR GENERAL - SOPORTE';
            $arreglo["tipousuario"] = '01';
            $arreglo["idtipousuario"] = '01';
            $arreglo["idtipousuariodesarrollo"] = '1';
            $arreglo["idtipousuarioexterno"] = '9';
            $arreglo["idtipousuariofinanciero"] = '9';
            $arreglo["escajero"] = 'SI';
            $arreglo["esbanco"] = 'NO';
            $arreglo["gastoadministrativo"] = 'SI';
            $arreglo["escensador"] = 'SI';
            $arreglo["essa"] = 'SI';
            $arreglo["esrue"] = 'NO';
            $arreglo["eswww"] = 'NO';
            $arreglo["esreversion"] = 'NO';
            $arreglo["esdispensador"] = 'NO';
            $arreglo["puedecerrarcaja"] = 'SI';
            $arreglo["visualizatotales"] = 'SI';
            $arreglo["abogadocoordinador"] = 'SI';
            $arreglo["idtipoidentificacion"] = '1';
            $arreglo["identificacion"] = '79048506';
            $arreglo["fechacreacion"] = date("Ymd");
            $arreglo["fechaactivacion"] = date("Ymd");
            $arreglo["fechainactivacion"] = '';
            $arreglo["fechaexpiracion"] = '';
            $arreglo["email"] = 'jnietot@gmail.com';
            $arreglo["email_privado"] = 'jnietot@gmail.com';
            $arreglo["fechacreacion"] = date("Ymd");
            $arreglo["loginemailusuario"] = '';
            $arreglo["passwordemailusuario"] = '';
            $arreglo["idperfildocumentacion"] = 0;
            $arreglo["telefonos"] = '';
            $arreglo["celular"] = '';
            $arreglo["direccion"] = '';
            $arreglo["idmunicipio"] = '';
            $arreglo["idpais"] = '';
            $arreglo["nitempresa"] = '';
            $arreglo["nombreempresa"] = '';
            $arreglo["idcargo"] = 0;
            $arreglo["idccos"] = '';
            $arreglo["idccospublico"] = '';
            $arreglo["idccosprivado"] = '';
            $arreglo["idcodigosirep"] = '';
            $arreglo["idsede"] = '01';
            if ($arreglo["idusuario"] == 'ADMGEN99') {
                $arreglo["password"] = password_hash('Cafaivda2010*', PASSWORD_DEFAULT);
            } else {
                $arreglo["password"] = \funcionesGenerales::retornarClaveMaestra(strtoupper($usua), '');
            }
            $arreglo["fechacambioclave"] = date("Ymd");
            $arreglo["fechaultimoingreso"] = date("Ymd");
            $arreglo["horaultimoingreso"] = date("H:i:s");
            $arreglo["intentoslogueo"] = 0;
            $arreglo["eliminado"] = 'NO';
            $arreglo["foto"] = '';
            $arreglo["foto1"] = '';
            $arreglo["idcodigosirepcaja"] = '';
            $arreglo["idcodigosirepdigitacion"] = '';
            $arreglo["idcodigosirepregistro"] = '';
            $arreglo["controlapresupuesto"] = 'S';
            $arreglo["controlverificacion"] = 'VE';
            $arreglo["mesavotacion"] = '';
            try {
                $arr1 = retornarRegistrosMysqliApi($mysqli, "bas_tagsbandejaentrada", "1=1", "id");
                foreach ($arr1 as $ar) {
                    if ($ar["idestado"] == 'A') {
                        $arreglo["tags" . $ar["id"]] = 'S';
                    }
                }
                unset($arr1);
            } catch (Exception $e) {
                return false;
            }
            unset($arr1);
        }
        if (!isset($arreglo["essa"])) {
            $arreglo["essa"] = 'NO';
        }
        return $arreglo;
    }

    public static function retornarUsuario1($usua = '', $identificacion = '')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');

        //
        $mysqli = conexionMysqliApi();
        $res = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $usua . "'");
        if ($res === false || empty($res)) {
            $mysqli->close();
            return false;
        }
        $arreglo = $res;
        $arr1 = retornarRegistrosMysqliApi($mysqli, "bas_tagsbandejaentrada", "1=1", "id");
        foreach ($arr1 as $ar) {
            if ($ar["idestado"] == 'A') {
                if (contarRegistrosMysqliApi($mysqli, 'usuariostagsbandejaentrada', "idusuario='" . $usua . "' and idtag='" . $ar["id"] . "'") == 0) {
                    $arreglo["tags" . $ar["id"]] = 'N';
                } else {
                    $arreglo["tags" . $ar["id"]] = 'S';
                }
            }
        }
        $mysqli->close();
        unset($res);
        unset($arr1);

        if (!isset($arreglo["essa"])) {
            $arreglo["essa"] = 'NO';
        }
        return $arreglo;
    }

    public static function segaConexion($codigoEmpresa, $periodo, $encripcion = 'si')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        if (!defined('ACTIVADO_SEGA')) {
            define('ACTIVADO_SEGA', 'SEGA-RM');
        }
        if (ACTIVADO_SEGA == '') {
            $activadosega = 'SEGA-RM';
        } else {
            $activadosega = ACTIVADO_SEGA;
        }
        if ($activadosega == 'SEGA-RM') {
            return \funcionesgenerales::segaConexionRm($codigoEmpresa, $periodo, $encripcion);
        }
        if ($activadosega == 'SEGA-GNU') {
            return \funcionesgenerales::segaConexionGnu($codigoEmpresa, $periodo, $encripcion);
        }
    }

    public static function segaConexionRm($codigoEmpresa, $periodo, $encripcion)
    {
        $salida = array(
            'conexion' => '',
            'path' => '',
            'host' => '',
            'port' => '',
            'user' => '',
            'password' => '',
            'runcobol' => '',
            'datos' => '',
            'datospres' => '',
            'nombresega' => ''
        );

        if ($_SESSION["generales"]["sega"] == '' || $_SESSION["generales"]["sega"] == 'PPAL') {
            if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/configuracion/runcobol' . $codigoEmpresa . ".php")) {
                return $salida;
            }
            $lineaspar = file($_SESSION["generales"]["pathabsoluto"] . '/configuracion/runcobol' . $codigoEmpresa . ".php");
        }
        if ($_SESSION["generales"]["sega"] != '' && $_SESSION["generales"]["sega"] != 'PPAL') {
            if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/configuracion/runcobol' . $codigoEmpresa . '-' . $_SESSION["generales"]["sega"] . ".php")) {
                return $salida;
            }
            $lineaspar = file($_SESSION["generales"]["pathabsoluto"] . '/configuracion/runcobol' . $codigoEmpresa . '-' . $_SESSION["generales"]["sega"] . ".php");
        }

        $salida = array(
            'conexion' => '',
            'path' => '',
            'host' => '',
            'port' => '',
            'user' => '',
            'password' => '',
            'runcobol' => '',
            'datos' => '',
            'datospres' => '',
            'nombresega' => ''
        );

        foreach ($lineaspar as $lin) {
            if ($encripcion == 'si') {
                $lin1 = \funcionesGenerales::decrypt($lin);
            } else {
                $lin1 = $lin;
            }
            list($key, $valor) = explode("|", $lin1);
            if ($key == 'conexion') {
                $salida["conexion"] = $valor;
            }
            if ($key == 'path') {
                $salida["path"] = $valor;
            }
            if ($key == 'host') {
                $salida["host"] = $valor;
            }
            if ($key == 'port') {
                $salida["port"] = $valor;
            }
            if ($key == 'user') {
                $salida["user"] = $valor;
            }
            if ($key == 'password') {
                $salida["password"] = $valor;
            }
            if ($key == 'runcobol') {
                $salida["runcobol"] = $valor;
            }
            if ($key == 'datos' . $periodo) {
                $salida["datos"] = trim($valor);
            }
            if ($key == 'datossegapres') {
                $salida["datospres"] = $valor;
            }
            if ($key == 'nombresega') {
                $salida["nombresega"] = $valor;
            }
        }

        // Crea el directorio de intercambio si no existe
        if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/tmpmig')) {
            mkdir($_SESSION["generales"]["pathabsoluto"] . '/tmpmig', 0777);
        }
        if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/tmpmig/' . $_SESSION["generales"]["codigoempresa"])) {
            mkdir($_SESSION["generales"]["pathabsoluto"] . '/tmpmig/' . $_SESSION["generales"]["codigoempresa"], 0777);
        }

        return $salida;
    }

    function segaConexionGnu($codigoEmpresa, $periodo, $encripcion)
    {

        $salida = array(
            'conexion' => '',
            'path' => '',
            'host' => '',
            'port' => '',
            'user' => '',
            'password' => '',
            'runcobol' => '',
            'datos' => '',
            'datospres' => '',
            'nombresega' => ''
        );

        if ($_SESSION["generales"]["sega"] == '' || $_SESSION["generales"]["sega"] == 'PPAL') {
            if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/configuracion/gnucobol' . $codigoEmpresa . ".php")) {
                return $salida;
            }
            $lineaspar = file($_SESSION["generales"]["pathabsoluto"] . '/configuracion/gnucobol' . $codigoEmpresa . ".php");
        }
        if ($_SESSION["generales"]["sega"] != '' && $_SESSION["generales"]["sega"] != 'PPAL') {
            if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/configuracion/gnucobol' . $codigoEmpresa . '-' . $_SESSION["generales"]["sega"] . ".php")) {
                return $salida;
            }
            $lineaspar = file($_SESSION["generales"]["pathabsoluto"] . '/configuracion/gnucobol' . $codigoEmpresa . '-' . $_SESSION["generales"]["sega"] . ".php");
        }
        $salida = array(
            'conexion' => '',
            'path' => '',
            'host' => '',
            'port' => '',
            'user' => '',
            'password' => '',
            'runcobol' => '',
            'datos' => '',
            'datospres' => '',
            'nombresega' => ''
        );

        foreach ($lineaspar as $lin) {
            if ($encripcion == 'si') {
                $lin1 = \funcionesgenerales::decrypt($lin);
            } else {
                $lin1 = $lin;
            }
            list($key, $valor) = explode("|", $lin1);
            if ($key == 'conexion')
                $salida["conexion"] = $valor;
            if ($key == 'path')
                $salida["path"] = $valor;
            if ($key == 'host')
                $salida["host"] = $valor;
            if ($key == 'port')
                $salida["port"] = $valor;
            if ($key == 'user')
                $salida["user"] = $valor;
            if ($key == 'password')
                $salida["password"] = $valor;
            if ($key == 'cobcrun')
                $salida["cobcrun"] = $valor;
            if ($key == 'datos' . $periodo)
                $salida["datos"] = trim($valor);
            if ($key == 'datossegapres')
                $salida["datospres"] = $valor;
            if ($key == 'nombresega')
                $salida["nombresega"] = $valor;
        }

        // Crea el directorio de intercambio si no existe
        if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/tmpmig')) {
            mkdir($_SESSION["generales"]["pathabsoluto"] . '/tmpmig', 0777);
        }
        if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/tmpmig/' . $_SESSION["generales"]["codigoempresa"])) {
            mkdir($_SESSION["generales"]["pathabsoluto"] . '/tmpmig/' . $_SESSION["generales"]["codigoempresa"], 0777);
        }

        return $salida;
    }

    public static function sanarValor($txt)
    {
        $decimal = 0;
        $signo = 0;
        $txtsal = '';
        $txt = trim(ltrim($txt, "0"));
        for ($i = 0; $i < strlen($txt); $i++) {
            $c = substr($txt, $i, 1);
            if ($c == '-') {
                $signo++;
                if ($signo == 1) {
                    $txtsal .= $c;
                }
            }
            if ($c == '.') {
                $decimal++;
                if ($decimal == 1) {
                    $txtsal .= $c;
                }
            }
            if (($c >= '0') && ($c <= '9')) {
                $txtsal .= $c;
            }
        }
        if (trim($txtsal) == '')
            $txtsal = 0;
        return $txtsal;
    }

    public static function separarDv($id)
    {
        $id = str_replace(",", "", ltrim(trim($id), "0"));
        $id = str_replace(".", "", $id);
        $id = str_replace("-", "", $id);
        $entrada = sprintf("%016s", $id);
        $dv = substr($entrada, 15, 1);
        return array(
            'identificacion' => ltrim(substr($entrada, 0, 15), "0"),
            'dv' => $dv
        );
    }

    public static function separarNombres($val)
    {
        $salida = array();
        $i = 0;
        $j = 0;
        $tx = '';
        $val = strtoupper(trim($val));
        $val = str_replace("'", "", $val);
        $val = str_replace("\"", "", $val);
        $val = str_replace("\r\n", " ", $val);
        $val = str_replace("\n\r", " ", $val);
        $val = str_replace("\n", " ", $val);
        $val = str_replace("\r", " ", $val);
        $val = str_replace(chr(13), " ", $val);
        while ($i < strlen($val)) {
            if (substr($val, $i, 1) == ' ') {
                if ($tx != '') {
                    $j++;
                    $salida[$j] = $tx;
                    $tx = '';
                }
            } else {
                $tx .= substr($val, $i, 1);
            }
            $i++;
        }
        if ($tx != '') {
            $j++;
            $salida[$j] = $tx;
        }
        return $salida;
    }

    public static function serializarLiquidacion($mysqli, $encoding = 'si', $usuarioWsX = 'localhost', $contrasenaWsX = 'localhost1024', $fecharecibo = '', $fecharenovacion = '', $idoperador = '', $tipogasto = '', $cupoAfiliado = 0, $usuario = '')
    {

        // 2017-03-02 : JINT : Control de sede
        if ($usuario != '') {
            $arrTemUsu = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $usuario . "'");
            if ($arrTemUsu && !empty($arrTemUsu)) {
                $_SESSION["tramite"]["sede"] = $arrTemUsu["idsede"];
            }
        }
        unset($arrTemUsu);

        if (!isset($_SESSION["tramite"]["sede"]) || strlen($_SESSION["tramite"]["sede"]) > 2 || ltrim(trim($_SESSION["tramite"]["sede"]), "0") == '') {
            $_SESSION["tramite"]["sede"] = '01';
        }

        //
        $arrTem = retornarRegistroMysqliApi($mysqli, 'bas_tipotramites', "id='" . $_SESSION["tramite"]["tipotramite"] . "'");
        if ($arrTem === false || empty($arrTem)) {
            $_SESSION["generales"]["mensajeerror"] = 'Imposible encontrar el tipo de tramite en bas_tipotramites';
            return false;
        }

        //
        if (!isset($_SESSION["tramite"]["tipomatricula"])) {
            $_SESSION["tramite"]["tipomatricula"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombrepnat"])) {
            $_SESSION["tramite"]["nombrepnat"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoidepnat"])) {
            $_SESSION["tramite"]["tipoidepnat"] = '';
        }
        if (!isset($_SESSION["tramite"]["idepnat"])) {
            $_SESSION["tramite"]["idepnat"] = '';
        }
        if (!isset($_SESSION["tramite"]["actpnat"])) {
            $_SESSION["tramite"]["actpnat"] = 0;
        }
        if (!isset($_SESSION["tramite"]["perpnat"])) {
            $_SESSION["tramite"]["perpnat"] = 0;
        }
        if (!isset($_SESSION["tramite"]["nombreest"])) {
            $_SESSION["tramite"]["nombreest"] = '';
        }
        if (!isset($_SESSION["tramite"]["actest"])) {
            $_SESSION["tramite"]["actest"] = 0;
        }
        if (!isset($_SESSION["tramite"]["benart7"])) {
            $_SESSION["tramite"]["benart7"] = '';
        }
        if (!isset($_SESSION["tramite"]["benley1780"])) {
            $_SESSION["tramite"]["benley1780"] = '';
        }
        if (!isset($_SESSION["tramite"]["camaracambidom"])) {
            $_SESSION["tramite"]["camaracambidom"] = '';
        }
        if (!isset($_SESSION["tramite"]["matriculacambidom"])) {
            $_SESSION["tramite"]["matriculacambidom"] = '';
        }
        if (!isset($_SESSION["tramite"]["regimentributario"])) {
            $_SESSION["tramite"]["regimentributario"] = '';
        }
        if (!isset($_SESSION["tramite"]["incluirformularios"])) {
            $_SESSION["tramite"]["incluirformularios"] = '';
        }
        if (!isset($_SESSION["tramite"]["incluircertificados"])) {
            $_SESSION["tramite"]["incluircertificados"] = '';
        }
        if (!isset($_SESSION["tramite"]["incluirdiploma"])) {
            $_SESSION["tramite"]["incluirdiploma"] = '';
        }
        if (!isset($_SESSION["tramite"]["incluircartulina"])) {
            $_SESSION["tramite"]["incluircartulina"] = '';
        }
        if (!isset($_SESSION["tramite"]["matricularpnat"])) {
            $_SESSION["tramite"]["matricularpnat"] = '';
        }
        if (!isset($_SESSION["tramite"]["matricularest"])) {
            $_SESSION["tramite"]["matricularest"] = '';
        }
        if (!isset($_SESSION["tramite"]["numeromatriculapnat"])) {
            $_SESSION["tramite"]["numeromatriculapnat"] = '';
        }
        if (!isset($_SESSION["tramite"]["camarapnat"])) {
            $_SESSION["tramite"]["camarapnat"] = '';
        }
        if (!isset($_SESSION["tramite"]["cargoafiliacion"])) {
            $_SESSION["tramite"]["cargoafiliacion"] = 'NO';
        }
        if (!isset($_SESSION["tramite"]["cargogastoadministrativo"])) {
            $_SESSION["tramite"]["cargogastoadministrativo"] = 'NO';
        }
        if (!isset($_SESSION["tramite"]["cargoentidadoficial"])) {
            $_SESSION["tramite"]["cargoentidadoficial"] = 'NO';
        }
        if (!isset($_SESSION["tramite"]["cargoconsulta"])) {
            $_SESSION["tramite"]["cargoconsulta"] = 'NO';
        }
        if (!isset($_SESSION["tramite"]["controlfirma"])) {
            $_SESSION["tramite"]["controlfirma"] = '';
        }
        if (!isset($_SESSION["tramite"]["idformapago"])) {
            $_SESSION["tramite"]["idformapago"] = '';
        }
        if (!isset($_SESSION["tramite"]["numerorecibo"])) {
            $_SESSION["tramite"]["numerorecibo"] = '';
        }
        if (!isset($_SESSION["tramite"]["numerooperacion"])) {
            $_SESSION["tramite"]["numerooperacion"] = '';
        }
        if (!isset($_SESSION["tramite"]["fecharecibo"])) {
            $_SESSION["tramite"]["fecharecibo"] = '';
        }
        if (!isset($_SESSION["tramite"]["horarecibo"])) {
            $_SESSION["tramite"]["horarecibo"] = '';
        }
        if (!isset($_SESSION["tramite"]["idfranquicia"])) {
            $_SESSION["tramite"]["idfranquicia"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombrefranquicia"])) {
            $_SESSION["tramite"]["nombrefranquicia"] = '';
        }
        if (!isset($_SESSION["tramite"]["numeroautorizacion"])) {
            $_SESSION["tramite"]["numeroautorizacion"] = '';
        }
        if (!isset($_SESSION["tramite"]["idcodban"])) {
            $_SESSION["tramite"]["idcodban"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombrebanco"])) {
            $_SESSION["tramite"]["nombrebanco"] = '';
        }
        if (!isset($_SESSION["tramite"]["numerocheque"])) {
            $_SESSION["tramite"]["numerocheque"] = '';
        }
        if (!isset($_SESSION["tramite"]["numerorecuperacion"])) {
            $_SESSION["tramite"]["numerorecuperacion"] = '';
        }
        if (!isset($_SESSION["tramite"]["numeroradicacion"])) {
            $_SESSION["tramite"]["numeroradicacion"] = '';
        }
        if (!isset($_SESSION["tramite"]["ctrcancelacion"])) {
            $_SESSION["tramite"]["ctrcancelacion"] = '';
        }
        if (!isset($_SESSION["tramite"]["idasesor"])) {
            $_SESSION["tramite"]["idasesor"] = '';
        }
        if (!isset($_SESSION["tramite"]["proyectocaja"])) {
            $_SESSION["tramite"]["proyectocaja"] = '001';
        }
        if (!isset($_SESSION["tramite"]["reliquidacion"])) {
            $_SESSION["tramite"]["reliquidacion"] = 'no';
        }
        if (!isset($_SESSION["tramite"]["nrocontrolsipref"])) {
            $_SESSION["tramite"]["nrocontrolsipref"] = '';
        }
        if (!isset($_SESSION["tramite"]["estadofinalliquidacion"])) {
            $_SESSION["tramite"]["estadofinalliquidacion"] = '';
        }
        if (!isset($_SESSION["tramite"]["modcom"])) {
            $_SESSION["tramite"]["modcom"] = '';
        }
        if (!isset($_SESSION["tramite"]["modnot"])) {
            $_SESSION["tramite"]["modnot"] = '';
        }
        if (!isset($_SESSION["tramite"]["modciiu"])) {
            $_SESSION["tramite"]["modciiu"] = '';
        }
        if (!isset($_SESSION["tramite"]["modnombre"])) {
            $_SESSION["tramite"]["modnombre"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipogasto"])) {
            $_SESSION["tramite"]["tipogasto"] = '';
        }

        //    
        if (!isset($_SESSION["tramite"]["idtipoidentificacioncliente"])) {
            $_SESSION["tramite"]["idtipoidentificacioncliente"] = '';
        }
        if (!isset($_SESSION["tramite"]["identificacioncliente"])) {
            $_SESSION["tramite"]["identificacioncliente"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombrecliente"])) {
            $_SESSION["tramite"]["nombrecliente"] = '';
        }
        if (!isset($_SESSION["tramite"]["email"])) {
            $_SESSION["tramite"]["email"] = '';
        }
        if (!isset($_SESSION["tramite"]["direccion"])) {
            $_SESSION["tramite"]["direccion"] = '';
        }
        if (!isset($_SESSION["tramite"]["idmunicipio"])) {
            $_SESSION["tramite"]["idmunicipio"] = '';
        }
        if (!isset($_SESSION["tramite"]["telefono"])) {
            $_SESSION["tramite"]["telefono"] = '';
        }
        if (!isset($_SESSION["tramite"]["movil"])) {
            $_SESSION["tramite"]["movil"] = '';
        }

        //
        if (!isset($_SESSION["tramite"]["tipoidentificacionpagador"])) {
            $_SESSION["tramite"]["tipoidentificacionpagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["identificacionpagador"])) {
            $_SESSION["tramite"]["identificacionpagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombrepagador"])) {
            $_SESSION["tramite"]["nombrepagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["apellidopagador"])) {
            $_SESSION["tramite"]["apellidopagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["direccionpagador"])) {
            $_SESSION["tramite"]["direccionpagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["municipiopagador"])) {
            $_SESSION["tramite"]["municipiopagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["telefonopagador"])) {
            $_SESSION["tramite"]["telefonopagador"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailpagador"])) {
            $_SESSION["tramite"]["emailpagador"] = '';
        }

        //
        if (!isset($_SESSION["tramite"]["numerointernorue"])) {
            $_SESSION["tramite"]["numerointernorue"] = '';
        }
        if (!isset($_SESSION["tramite"]["numerounicorue"])) {
            $_SESSION["tramite"]["numerounicorue"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipotramiterue"])) {
            $_SESSION["tramite"]["tipotramiterue"] = '';
        }

        //
        if (!isset($_SESSION["tramite"]["pagoefectivo"])) {
            $_SESSION["tramite"]["pagoefectivo"] = '0';
        }
        if (!isset($_SESSION["tramite"]["pagocheque"])) {
            $_SESSION["tramite"]["pagocheque"] = '0';
        }
        if (!isset($_SESSION["tramite"]["pagoconsignacion"])) {
            $_SESSION["tramite"]["pagoconsignacion"] = '0';
        }
        if (!isset($_SESSION["tramite"]["pagovisa"])) {
            $_SESSION["tramite"]["pagovisa"] = '0';
        }
        if (!isset($_SESSION["tramite"]["pagoach"])) {
            $_SESSION["tramite"]["pagoach"] = '0';
        }
        if (!isset($_SESSION["tramite"]["pagomastercard"])) {
            $_SESSION["tramite"]["pagomastercard"] = '0';
        }
        if (!isset($_SESSION["tramite"]["pagoamerican"])) {
            $_SESSION["tramite"]["pagoamerican"] = '0';
        }
        if (!isset($_SESSION["tramite"]["pagocredencial"])) {
            $_SESSION["tramite"]["pagocredencial"] = '0';
        }
        if (!isset($_SESSION["tramite"]["pagodiners"])) {
            $_SESSION["tramite"]["pagodiners"] = '0';
        }
        if (!isset($_SESSION["tramite"]["pagotdebito"])) {
            $_SESSION["tramite"]["pagotdebito"] = '0';
        }
        if (!isset($_SESSION["tramite"]["pagoprepago"])) {
            $_SESSION["tramite"]["pagoprepago"] = '0';
        }
        if (!isset($_SESSION["tramite"]["pagoafiliado"])) {
            $_SESSION["tramite"]["pagoafiliado"] = '0';
        }
        if (!isset($_SESSION["tramite"]["pagoacredito"])) {
            $_SESSION["tramite"]["pagoacredito"] = '0';
        }

        //
        if (!isset($_SESSION["tramite"]["alertaservicio"])) {
            $_SESSION["tramite"]["alertaservicio"] = '';
        }
        if (!isset($_SESSION["tramite"]["alertavalor"])) {
            $_SESSION["tramite"]["alertavalor"] = '0';
        }
        if (!isset($_SESSION["tramite"]["alertaid"])) {
            $_SESSION["tramite"]["alertaid"] = '0';
        }

        //
        if (!isset($_SESSION["tramite"]["capital"])) {
            $_SESSION["tramite"]["capital"] = 0;
        }
        if (!isset($_SESSION["tramite"]["tipodoc"])) {
            $_SESSION["tramite"]["tipodoc"] = '';
        }
        if (!isset($_SESSION["tramite"]["numdoc"])) {
            $_SESSION["tramite"]["numdoc"] = '';
        }
        if (!isset($_SESSION["tramite"]["fechadoc"])) {
            $_SESSION["tramite"]["fechadoc"] = '';
        }
        if (!isset($_SESSION["tramite"]["origendoc"])) {
            $_SESSION["tramite"]["origendoc"] = '';
        }
        if (!isset($_SESSION["tramite"]["mundoc"])) {
            $_SESSION["tramite"]["mundoc"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoiderepleg"])) {
            $_SESSION["tramite"]["tipoiderepleg"] = '';
        }
        if (!isset($_SESSION["tramite"]["iderepleg"])) {
            $_SESSION["tramite"]["iderepleg"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombreiderepleg"])) {
            $_SESSION["tramite"]["nombreiderepleg"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoideradicador"])) {
            $_SESSION["tramite"]["tipoideradicador"] = '';
        }
        if (!isset($_SESSION["tramite"]["ideradicador"])) {
            $_SESSION["tramite"]["ideradicador"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombreradicador"])) {
            $_SESSION["tramite"]["nombreradicador"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailradicador"])) {
            $_SESSION["tramite"]["emailradicador"] = '';
        }
        if (!isset($_SESSION["tramite"]["telefonoradicador"])) {
            $_SESSION["tramite"]["telefonoradicador"] = '';
        }
        if (!isset($_SESSION["tramite"]["celularradicador"])) {
            $_SESSION["tramite"]["celularradicador"] = '';
        }

        //
        if (!isset($_SESSION["tramite"]["tipoidentificacionaceptante"])) {
            $_SESSION["tramite"]["tipoidentificacionaceptante"] = '';
        }
        if (!isset($_SESSION["tramite"]["identificacionaceptante"])) {
            $_SESSION["tramite"]["identificacionaceptante"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombreaceptante"])) {
            $_SESSION["tramite"]["nombreaceptante"] = '';
        }
        if (!isset($_SESSION["tramite"]["cargoaceptante"])) {
            $_SESSION["tramite"]["cargoaceptante"] = '';
        }
        if (!isset($_SESSION["tramite"]["fechadocideaceptante"])) {
            $_SESSION["tramite"]["fechadocideaceptante"] = '';
        }

        //
        if (!isset($_SESSION["tramite"]["motivocorreccion"])) {
            $_SESSION["tramite"]["motivocorreccion"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoerror1"])) {
            $_SESSION["tramite"]["tipoerror1"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoerror2"])) {
            $_SESSION["tramite"]["tipoerror2"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoerror3"])) {
            $_SESSION["tramite"]["tipoerror3"] = '';
        }

        //
        if (!isset($_SESSION["tramite"]["descripcionembargo"])) {
            $_SESSION["tramite"]["descripcionembargo"] = '';
        }
        if (!isset($_SESSION["tramite"]["descripciondesembargo"])) {
            $_SESSION["tramite"]["descripciondesembargo"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoidentificaciondemandante"])) {
            $_SESSION["tramite"]["tipoidentificaciondemandante"] = '';
        }
        if (!isset($_SESSION["tramite"]["identificaciondemandante"])) {
            $_SESSION["tramite"]["identificaciondemandante"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombredemandante"])) {
            $_SESSION["tramite"]["nombredemandante"] = '';
        }
        if (!isset($_SESSION["tramite"]["libro"])) {
            $_SESSION["tramite"]["libro"] = '';
        }
        if (!isset($_SESSION["tramite"]["numreg"])) {
            $_SESSION["tramite"]["numreg"] = '';
        }

        //
        if (!isset($_SESSION["tramite"]["descripcionpqr"])) {
            $_SESSION["tramite"]["descripcionpqr"] = '';
        }
        if (!isset($_SESSION["tramite"]["tipoidentificacionpqr"])) {
            $_SESSION["tramite"]["tipoidentificacionpqr"] = '';
        }
        if (!isset($_SESSION["tramite"]["identificacionpqr"])) {
            $_SESSION["tramite"]["identificacionpqr"] = '';
        }
        if (!isset($_SESSION["tramite"]["nombrepqr"])) {
            $_SESSION["tramite"]["nombrepqr"] = '';
        }
        if (!isset($_SESSION["tramite"]["emailpqr"])) {
            $_SESSION["tramite"]["emailpqr"] = '';
        }
        if (!isset($_SESSION["tramite"]["telefonopqr"])) {
            $_SESSION["tramite"]["telefonopqr"] = '';
        }
        if (!isset($_SESSION["tramite"]["celularpqr"])) {
            $_SESSION["tramite"]["celularpqr"] = '';
        }

        // Variables para el control del proceso con cargo al cupo de afiliados.
        if (!isset($_SESSION["tramite"]["opcionafiliado"])) {
            $_SESSION["tramite"]["opcionafiliado"] = '';
        }
        if (!isset($_SESSION["tramite"]["saldoafiliado"])) {
            $_SESSION["tramite"]["saldoafiliado"] = 0;
        }
        if (!isset($_SESSION["tramite"]["matriculaafiliado"])) {
            $_SESSION["tramite"]["matriculaafiliado"] = '';
        }
        if (!isset($_SESSION["tramite"]["ultanorenafi"])) {
            $_SESSION["tramite"]["ultanorenafi"] = '';
        }

        //
        if (!isset($_SESSION["tramite"]["transacciones"])) {
            $_SESSION["tramite"]["transacciones"] = array();
        }

        // 2015-06-02    
        if ($idoperador != '') {
            $_SESSION["tramite"]["idoperador"] = $idoperador;
        } else {
            $_SESSION["tramite"]["idoperador"] = $_SESSION["generales"]["idcodigosirepcaja"];
        }

        // $xmlDoc = new DOMDocument('1.0', 'utf-8');
        $xmlDoc = new DOMDocument('1.0', 'utf-8');
        $xmlDoc->formatOutput = true;

        // liquidacion
        $tagliquidacion = $xmlDoc->createElement('liquidacion');

        // autenticacion
        $autenticacion = $xmlDoc->createElement('autenticacion');
        $autenticacion->appendChild(new DOMElement('usuariows', $usuarioWsX));
        $autenticacion->appendChild(new DOMElement('clavews', $contrasenaWsX));
        if ($encoding == 'si') {
            $autenticacion->appendChild(new DOMElement('encoding', 'base64_encode'));
        } else {
            $autenticacion->appendChild(new DOMElement('encoding', ''));
        }

        $tagliquidacion->appendChild($autenticacion);

        // Totales
        $totales = $xmlDoc->createElement('totales');

        // Tipo de tr&aacute;mite
        $totales->appendChild(new DOMElement('tipotramite', '<![CDATA[' . $_SESSION["tramite"]["tipotramite"] . ']]>'));

        // Forzar esta fecha de renovacion
        if ($fecharenovacion != '') {
            $totales->appendChild(new DOMElement('fecharenovacionagenerar', $fecharenovacion));
        }

        // Forzar esta fecha de recibo
        if ($fecharecibo != '') {
            $totales->appendChild(new DOMElement('fechareciboagenerar', $fecharecibo));
        }

        // ************************************************************************************* //
        // Datos del cliente
        // ************************************************************************************* //
        if ($encoding != 'no') {
            $totales->appendChild(new DOMElement('nombrecliente', '<![CDATA[' . base64_encode($_SESSION["tramite"]["nombrecliente"]) . ']]>'));
        } else {
            $totales->appendChild(new DOMElement('nombrecliente', '<![CDATA[' . $_SESSION["tramite"]["nombrecliente"] . ']]>'));
        }
        if ($encoding != 'no') {
            $totales->appendChild(new DOMElement('apellidocliente', '<![CDATA[' . base64_encode($_SESSION["tramite"]["apellidocliente"]) . ']]>'));
        } else {
            $totales->appendChild(new DOMElement('apellidocliente', '<![CDATA[' . $_SESSION["tramite"]["apellidocliente"] . ']]>'));
        }
        $totales->appendChild(new DOMElement('idtipoidentificacioncliente', $_SESSION["tramite"]["idtipoidentificacioncliente"]));
        $totales->appendChild(new DOMElement('identificacioncliente', ltrim($_SESSION["tramite"]["identificacioncliente"], "0")));
        if ($encoding != 'no') {
            $totales->appendChild(new DOMElement('direccion', '<![CDATA[' . base64_encode($_SESSION["tramite"]["direccion"]) . ']]>'));
        } else {
            $totales->appendChild(new DOMElement('direccion', '<![CDATA[' . $_SESSION["tramite"]["direccion"] . ']]>'));
        }
        $totales->appendChild(new DOMElement('telefono', $_SESSION["tramite"]["telefono"]));
        $totales->appendChild(new DOMElement('movil', $_SESSION["tramite"]["movil"]));
        $totales->appendChild(new DOMElement('municipio', $_SESSION["tramite"]["idmunicipio"]));
        if ($encoding != 'no') {
            $totales->appendChild(new DOMElement('email', '<![CDATA[' . base64_encode($_SESSION["tramite"]["email"]) . ']]>'));
        } else {
            $totales->appendChild(new DOMElement('email', '<![CDATA[' . $_SESSION["tramite"]["email"] . ']]>'));
        }

        // ************************************************************************************* //
        // Datos del pagador
        // ************************************************************************************* //
        if ($encoding != 'no') {
            $totales->appendChild(new DOMElement('nombrepagador', '<![CDATA[' . base64_encode($_SESSION["tramite"]["nombrepagador"]) . ']]>'));
        } else {
            $totales->appendChild(new DOMElement('nombrepagador', '<![CDATA[' . $_SESSION["tramite"]["nombrepagador"] . ']]>'));
        }
        if ($encoding != 'no') {
            $totales->appendChild(new DOMElement('apellidopagador', '<![CDATA[' . base64_encode($_SESSION["tramite"]["apellidopagador"]) . ']]>'));
        } else {
            $totales->appendChild(new DOMElement('apellidopagador', '<![CDATA[' . $_SESSION["tramite"]["apellidopagador"] . ']]>'));
        }
        $totales->appendChild(new DOMElement('tipoidentificacionpagador', $_SESSION["tramite"]["tipoidentificacionpagador"]));
        $totales->appendChild(new DOMElement('identificacionpagador', ltrim($_SESSION["tramite"]["identificacionpagador"], "0")));
        if ($encoding != 'no') {
            $totales->appendChild(new DOMElement('direccionpagador', '<![CDATA[' . base64_encode($_SESSION["tramite"]["direccionpagador"]) . ']]>'));
        } else {
            $totales->appendChild(new DOMElement('direccionpagador', '<![CDATA[' . $_SESSION["tramite"]["direccionpagador"] . ']]>'));
        }
        $totales->appendChild(new DOMElement('telefonopagador', $_SESSION["tramite"]["telefonopagador"]));
        $totales->appendChild(new DOMElement('movilpagador', $_SESSION["tramite"]["movilpagador"]));
        $totales->appendChild(new DOMElement('municipiopagador', $_SESSION["tramite"]["municipiopagador"]));
        if ($encoding != 'no') {
            $totales->appendChild(new DOMElement('emailpagador', '<![CDATA[' . base64_encode($_SESSION["tramite"]["emailpagador"]) . ']]>'));
        } else {
            $totales->appendChild(new DOMElement('emailpagador', '<![CDATA[' . $_SESSION["tramite"]["emailpagador"] . ']]>'));
        }

        // ***************************************************************************************** //
        // Tipo de matr&iacute;cula, cuando el tr&aacute;mite sea matriculaXXXXX
        // ***************************************************************************************** //
        $totales->appendChild(new DOMElement('tipomatricula', $_SESSION["tramite"]["tipomatricula"]));

        // ***************************************************************************************** //
        // Datos de la persona natural que se est&aacute; matriculando
        // ***************************************************************************************** //
        if ($encoding != 'no') {
            $totales->appendChild(new DOMElement('nombrepnat', '<![CDATA[' . base64_encode($_SESSION["tramite"]["nombrepnat"]) . ']]>'));
        } else {
            $totales->appendChild(new DOMElement('nombrepnat', '<![CDATA[' . $_SESSION["tramite"]["nombrepnat"] . ']]>'));
        }
        $totales->appendChild(new DOMElement('tipoidepnat', $_SESSION["tramite"]["tipoidepnat"]));
        $totales->appendChild(new DOMElement('idepnat', $_SESSION["tramite"]["idepnat"]));
        $totales->appendChild(new DOMElement('actpnat', $_SESSION["tramite"]["actpnat"]));
        $totales->appendChild(new DOMElement('perpnat', $_SESSION["tramite"]["perpnat"]));
        $totales->appendChild(new DOMElement('regimentributario', $_SESSION["tramite"]["regimentributario"]));

        // ***************************************************************************************** //
        // Datos del establecimiento que se est&aacute; matriculando
        // ***************************************************************************************** //
        if ($encoding != 'no') {
            $totales->appendChild(new DOMElement('nombreest', '<![CDATA[' . base64_encode($_SESSION["tramite"]["nombreest"]) . ']]>'));
        } else {
            $totales->appendChild(new DOMElement('nombreest', '<![CDATA[' . $_SESSION["tramite"]["nombreest"] . ']]>'));
        }
        $totales->appendChild(new DOMElement('actest', $_SESSION["tramite"]["actest"]));

        // ***************************************************************************************** //
        // Beneficio o no de Ley 1429
        // ***************************************************************************************** //
        $totales->appendChild(new DOMElement('benart7', $_SESSION["tramite"]["benart7"]));
        $totales->appendChild(new DOMElement('benley1780', $_SESSION["tramite"]["benley1780"]));

        // ***************************************************************************************** //
        // Datos cuando es matr&iacute;cula por cambio de domicilio
        $totales->appendChild(new DOMElement('camaracambidom', $_SESSION["tramite"]["camaracambidom"]));
        $totales->appendChild(new DOMElement('matriculacambidom', $_SESSION["tramite"]["matriculacambidom"]));

        // ***************************************************************************************** //
        // Datos complementarios de la liquidaci&oacute;n
        // ***************************************************************************************** //
        $totales->appendChild(new DOMElement('incluirformularios', $_SESSION["tramite"]["incluirformularios"]));
        $totales->appendChild(new DOMElement('incluircertificados', $_SESSION["tramite"]["incluircertificados"]));
        $totales->appendChild(new DOMElement('incluirdiploma', $_SESSION["tramite"]["incluirdiploma"]));
        $totales->appendChild(new DOMElement('incluircartulina', $_SESSION["tramite"]["incluircartulina"]));
        $totales->appendChild(new DOMElement('modcom', $_SESSION["tramite"]["modcom"]));
        $totales->appendChild(new DOMElement('modnot', $_SESSION["tramite"]["modnot"]));
        $totales->appendChild(new DOMElement('modciiu', $_SESSION["tramite"]["modciiu"]));
        $totales->appendChild(new DOMElement('modnombre', $_SESSION["tramite"]["modnombre"]));
        $totales->appendChild(new DOMElement('matricularpnat', $_SESSION["tramite"]["matricularpnat"]));
        $totales->appendChild(new DOMElement('matricularest', $_SESSION["tramite"]["matricularest"]));
        $totales->appendChild(new DOMElement('numeromatriculapnat', $_SESSION["tramite"]["numeromatriculapnat"]));
        $totales->appendChild(new DOMElement('camarapnat', $_SESSION["tramite"]["camarapnat"]));
        $totales->appendChild(new DOMElement('gastoafiliacion', $_SESSION["tramite"]["cargoafiliacion"]));
        $totales->appendChild(new DOMElement('gastoadministrativo', $_SESSION["tramite"]["cargogastoadministrativo"]));
        $totales->appendChild(new DOMElement('gastoentidadoficial', $_SESSION["tramite"]["cargoentidadoficial"]));
        $totales->appendChild(new DOMElement('gastoconsulta', $_SESSION["tramite"]["cargoconsulta"]));
        $totales->appendChild(new DOMElement('idfranquicia', $_SESSION["tramite"]["idfranquicia"]));
        $totales->appendChild(new DOMElement('numeroautorizacion', $_SESSION["tramite"]["numeroautorizacion"]));
        $totales->appendChild(new DOMElement('idcodbanco', $_SESSION["tramite"]["idcodban"]));
        $totales->appendChild(new DOMElement('nombrebanco', '<![CDATA[' . $_SESSION["tramite"]["nombrebanco"] . ']]>'));
        $totales->appendChild(new DOMElement('numerocheque', $_SESSION["tramite"]["numerocheque"]));
        $totales->appendChild(new DOMElement('pagoefectivo', $_SESSION["tramite"]["pagoefectivo"]));
        $totales->appendChild(new DOMElement('pagocheque', $_SESSION["tramite"]["pagocheque"]));
        $totales->appendChild(new DOMElement('pagoconsignacion', $_SESSION["tramite"]["pagoconsignacion"]));
        $totales->appendChild(new DOMElement('pagovisa', $_SESSION["tramite"]["pagovisa"]));
        $totales->appendChild(new DOMElement('pagoach', $_SESSION["tramite"]["pagoach"]));
        $totales->appendChild(new DOMElement('pagomastercard', $_SESSION["tramite"]["pagomastercard"]));
        $totales->appendChild(new DOMElement('pagoamerican', $_SESSION["tramite"]["pagoamerican"]));
        $totales->appendChild(new DOMElement('pagocredencial', $_SESSION["tramite"]["pagocredencial"]));
        $totales->appendChild(new DOMElement('pagodiners', $_SESSION["tramite"]["pagodiners"]));
        $totales->appendChild(new DOMElement('pagotdebito', $_SESSION["tramite"]["pagotdebito"]));
        $totales->appendChild(new DOMElement('pagoprepago', $_SESSION["tramite"]["pagoprepago"]));
        $totales->appendChild(new DOMElement('pagoafiliado', $_SESSION["tramite"]["pagoafiliado"]));
        $totales->appendChild(new DOMElement('pagoacredito', $_SESSION["tramite"]["pagoacredito"]));
        $totales->appendChild(new DOMElement('alertaservicio', $_SESSION["tramite"]["alertaservicio"]));
        $totales->appendChild(new DOMElement('alertavalor', $_SESSION["tramite"]["alertavalor"]));
        $totales->appendChild(new DOMElement('alertaid', $_SESSION["tramite"]["alertaid"]));
        $totales->appendChild(new DOMElement('numerounicorue', $_SESSION["tramite"]["numerounicorue"]));
        $totales->appendChild(new DOMElement('numerointernorue', $_SESSION["tramite"]["numerointernorue"]));
        $totales->appendChild(new DOMElement('tipotramiterue', $_SESSION["tramite"]["tipotramiterue"]));
        $totales->appendChild(new DOMElement('proyectocaja', $_SESSION["tramite"]["proyectocaja"]));
        $totales->appendChild(new DOMElement('reliquidacion', $_SESSION["tramite"]["reliquidacion"]));
        $totales->appendChild(new DOMElement('nrocontrolsipref', $_SESSION["tramite"]["nrocontrolsipref"]));
        $totales->appendChild(new DOMElement('estadofinalliquidacion', $_SESSION["tramite"]["estadofinalliquidacion"]));

        //
        $totales->appendChild(new DOMElement('opcionafiliado', $_SESSION["tramite"]["opcionafiliado"]));
        $totales->appendChild(new DOMElement('saldoafiliado', $_SESSION["tramite"]["saldoafiliado"]));
        $totales->appendChild(new DOMElement('matriculaafiliado', sprintf("%08s", $_SESSION["tramite"]["matriculaafiliado"])));
        $totales->appendChild(new DOMElement('ultanorenafi', $_SESSION["tramite"]["ultanorenafi"]));
        $totales->appendChild(new DOMElement('cupoafiliado', $cupoAfiliado));

        //
        if (trim($_SESSION["tramite"]["tipogasto"]) != '') {
            $totales->appendChild(new DOMElement('tipogasto', $_SESSION["tramite"]["tipogasto"]));
        }


        // ***************************************************************************************** //
        // Otros campos de inter&eacute;s - cartas de aceptacion
        // ***************************************************************************************** //
        if (trim($_SESSION["tramite"]["tipoidentificacionaceptante"]) != '') {
            $totales->appendChild(new DOMElement('tipoidentificacionaceptante', $_SESSION["tramite"]["tipoidentificacionaceptante"]));
        }
        if (trim($_SESSION["tramite"]["identificacionaceptante"]) != '') {
            $totales->appendChild(new DOMElement('identificacionaceptante', $_SESSION["tramite"]["identificacionaceptante"]));
        }
        if (trim($_SESSION["tramite"]["nombreaceptante"])) {
            if ($encoding != 'no') {
                $totales->appendChild(new DOMElement('nombreaceptante', '<![CDATA[' . base64_encode($_SESSION["tramite"]["nombreaceptante"]) . ']]>'));
            } else {
                $totales->appendChild(new DOMElement('nombreaceptante', '<![CDATA[' . $_SESSION["tramite"]["nombreaceptante"] . ']]>'));
            }
        }
        if (trim($_SESSION["tramite"]["cargoaceptante"]) != '') {
            if ($encoding != 'no') {
                $totales->appendChild(new DOMElement('cargoaceptante', '<![CDATA[' . base64_encode(($_SESSION["tramite"]["cargoaceptante"])) . ']]>'));
            } else {
                $totales->appendChild(new DOMElement('cargoaceptante', '<![CDATA[' . $_SESSION["tramite"]["cargoaceptante"] . ']]>'));
            }
        }
        if (trim($_SESSION["tramite"]["fechadocideaceptante"]) != '') {
            $totales->appendChild(new DOMElement('fechadocideaceptante', $_SESSION["tramite"]["fechadocideaceptante"]));
        }

        // ***************************************************************************************** //
        // Otros campos de inter&eacute;s - correcciones
        // ***************************************************************************************** //
        if (trim($_SESSION["tramite"]["motivocorreccion"]) != '') {
            if ($encoding != 'no') {
                $totales->appendChild(new DOMElement('motivocorreccion', '<![CDATA[' . base64_encode(($_SESSION["tramite"]["motivocorreccion"])) . ']]>'));
            } else {
                $totales->appendChild(new DOMElement('motivocorreccion', '<![CDATA[' . $_SESSION["tramite"]["motivocorreccion"] . ']]>'));
            }
        }
        if (trim($_SESSION["tramite"]["tipoerror1"]) != '') {
            $totales->appendChild(new DOMElement('tipoerror1', $_SESSION["tramite"]["tipoerror1"]));
        }
        if (trim($_SESSION["tramite"]["tipoerror2"]) != '') {
            $totales->appendChild(new DOMElement('tipoerror2', $_SESSION["tramite"]["tipoerror2"]));
        }
        if (trim($_SESSION["tramite"]["tipoerror3"]) != '') {
            $totales->appendChild(new DOMElement('tipoerror3', $_SESSION["tramite"]["tipoerror3"]));
        }

        // ***************************************************************************************** //
        // Otros campos de inter&eacute;s - embargos y desembargos
        // ***************************************************************************************** //
        if (trim($_SESSION["tramite"]["descripcionembargo"]) != '') {
            if ($encoding != 'no') {
                $totales->appendChild(new DOMElement('descripcionembargo', '<![CDATA[' . base64_encode(($_SESSION["tramite"]["descripcionembargo"])) . ']]>'));
            } else {
                $totales->appendChild(new DOMElement('descripcionembargo', '<![CDATA[' . $_SESSION["tramite"]["descripcionembargo"] . ']]>'));
            }
        }
        if (trim($_SESSION["tramite"]["descripciondesembargo"]) != '') {
            if ($encoding != 'no') {
                $totales->appendChild(new DOMElement('descripciondesembargo', '<![CDATA[' . base64_encode(($_SESSION["tramite"]["descripciondesembargo"])) . ']]>'));
            } else {
                $totales->appendChild(new DOMElement('descripciondesembargo', '<![CDATA[' . $_SESSION["tramite"]["descripciondesembargo"] . ']]>'));
            }
        }
        if (trim($_SESSION["tramite"]["tipoidentificaciondemandante"]) != '') {
            $totales->appendChild(new DOMElement('tipoidentificaciondemandante', $_SESSION["tramite"]["tipoidentificaciondemandante"]));
        }
        if (trim($_SESSION["tramite"]["identificaciondemandante"]) != '') {
            $totales->appendChild(new DOMElement('identificaciondemandante', $_SESSION["tramite"]["identificaciondemandante"]));
        }
        if (trim($_SESSION["tramite"]["nombredemandante"]) != '') {
            if ($encoding != 'no') {
                $totales->appendChild(new DOMElement('nombredemandante', '<![CDATA[' . base64_encode($_SESSION["tramite"]["nombredemandante"]) . ']]>'));
            } else {
                $totales->appendChild(new DOMElement('nombredemandante', '<![CDATA[' . $_SESSION["tramite"]["nombredemandante"] . ']]>'));
            }
        }
        if (trim($_SESSION["tramite"]["libro"]) != '') {
            $totales->appendChild(new DOMElement('libro', $_SESSION["tramite"]["libro"]));
        }
        if (trim($_SESSION["tramite"]["numreg"]) != '') {
            $totales->appendChild(new DOMElement('numreg', $_SESSION["tramite"]["numreg"]));
        }

        // ***************************************************************************************** //
        // Otros campos de inter&eacute;s - PQRs
        // ***************************************************************************************** //
        if (trim($_SESSION["tramite"]["descripcionpqr"]) != '') {
            if ($encoding != 'no') {
                $totales->appendChild(new DOMElement('descripcionpqr', '<![CDATA[' . base64_encode(($_SESSION["tramite"]["descripcionpqr"])) . ']]>'));
            } else {
                $totales->appendChild(new DOMElement('descripcionpqr', '<![CDATA[' . $_SESSION["tramite"]["descripcionpqr"] . ']]>'));
            }
        }
        if (trim($_SESSION["tramite"]["tipoidentificacionpqr"]) != '') {
            $totales->appendChild(new DOMElement('tipoidentificacionpqr', $_SESSION["tramite"]["tipoidentificacionpqr"]));
        }
        if (trim($_SESSION["tramite"]["identificacionpqr"]) != '') {
            $totales->appendChild(new DOMElement('identificacionpqr', $_SESSION["tramite"]["identificacionpqr"]));
        }
        if (trim($_SESSION["tramite"]["nombrepqr"]) != '') {
            if ($encoding != 'no') {
                $totales->appendChild(new DOMElement('nombrepqr', '<![CDATA[' . base64_encode($_SESSION["tramite"]["nombrepqr"]) . ']]>'));
            } else {
                $totales->appendChild(new DOMElement('nombrepqr', '<![CDATA[' . $_SESSION["tramite"]["nombrepqr"] . ']]>'));
            }
        }
        if (trim($_SESSION["tramite"]["emailpqr"]) != '') {
            if ($encoding != 'no') {
                $totales->appendChild(new DOMElement('emailpqr', '<![CDATA[' . base64_encode($_SESSION["tramite"]["emailpqr"]) . ']]>'));
            } else {
                $totales->appendChild(new DOMElement('emailpqr', '<![CDATA[' . $_SESSION["tramite"]["emailpqr"] . ']]>'));
            }
        }
        if (trim($_SESSION["tramite"]["telefonopqr"]) != '') {
            $totales->appendChild(new DOMElement('telefonopqr', $_SESSION["tramite"]["telefonopqr"]));
        }
        if (trim($_SESSION["tramite"]["celularpqr"]) != '') {
            $totales->appendChild(new DOMElement('celularpqr', $_SESSION["tramite"]["celularpqr"]));
        }


        // ***************************************************************************************** //
        // Asigna el operador
        // ***************************************************************************************** //
        if (isset($_SESSION["tramite"]["idoperador"]) && $_SESSION["tramite"]["idoperador"] != '') {
            $totales->appendChild(new DOMElement('cajero', $_SESSION["tramite"]["idoperador"]));
        } else {
            $totales->appendChild(new DOMElement('cajero', $_SESSION["generales"]["idcodigosirepcaja"]));
        }

        //
        $totales->appendChild(new DOMElement('sede', $_SESSION["tramite"]["sede"]));

        // ***************************************************************************************** //
        // Asigna el usuario SII
        // ***************************************************************************************** //
        if (trim($usuario) == '') {
            $totales->appendChild(new DOMElement('idusuario', $_SESSION["generales"]["codigousuario"]));
        } else {
            $totales->appendChild(new DOMElement('idusuario', $usuario));
        }

        // ***************************************************************************************** //
        // Crea el nodo "totales"
        // ***************************************************************************************** //
        $tagliquidacion->appendChild($totales);

        // ***************************************************************************************** //
        // Detalles
        // ***************************************************************************************** //
        $i = 0;
        foreach ($_SESSION["tramite"]["liquidacion"] as $liq) {

            if (!isset($liq["idsec"])) {
                $liq["idsec"] = '';
            }
            if (!isset($liq["categoria"])) {
                $liq["categoria"] = '';
            }
            if (!isset($liq["benart7"])) {
                $liq["benart7"] = '';
            }
            if (!isset($liq["organizacion"])) {
                $liq["organizacion"] = '';
            }
            if (!isset($liq["categoria"])) {
                $liq["categoria"] = '';
            }
            if (!isset($liq["nombre"])) {
                $liq["nombre"] = '';
            }
            if (!isset($liq["identificacion"])) {
                $liq["identificacion"] = '';
            }
            if (!isset($liq["paginainicial"])) {
                $liq["paginainicial"] = '';
            }
            if (!isset($liq["paginafinal"])) {
                $liq["paginafinal"] = '';
            }
            if (!isset($liq["codigolibro"])) {
                $liq["codigolibro"] = '';
            }
            if (!isset($liq["descripcionlibro"])) {
                $liq["descripcionlibro"] = '';
            }
            if (!isset($liq["idtipodoc"])) {
                $liq["idtipodoc"] = '';
            }
            if (!isset($liq["numdoc"])) {
                $liq["numdoc"] = '';
            }
            if (!isset($liq["origendoc"])) {
                $liq["origendoc"] = '';
            }
            if (!isset($liq["fechadoc"])) {
                $liq["fechadoc"] = '';
            }
            if (!isset($liq["estado"])) {
                $liq["estado"] = '';
            }
            if (!isset($liq["estadodatos"])) {
                $liq["estadodatos"] = '';
            }
            if (!isset($liq["pagoafiliacion"])) {
                $liq["pagoafiliacion"] = 'No';
            }
            if (!isset($liq["ir"])) {
                $liq["ir"] = '';
            }
            if (!isset($liq["iva"])) {
                $liq["iva"] = '';
            }

            //
            $detalles = $xmlDoc->createElement("detalles");
            $detalles->appendChild(new DOMElement('numeroliquidacion', ltrim($_SESSION["tramite"]["numeroliquidacion"], '0')));
            $detalles->appendChild(new DOMElement('idsec', $liq["idsec"]));
            $detalles->appendChild(new DOMElement('secuencia', ''));
            $detalles->appendChild(new DOMElement('consecutivo', ''));
            $detalles->appendChild(new DOMElement('servicio', $liq["idservicio"]));
            if ($liq["cantidad"] > 999) {
                $detalles->appendChild(new DOMElement('cantidad', 1));
            } else {
                $detalles->appendChild(new DOMElement('cantidad', $liq["cantidad"]));
            }
            $detalles->appendChild(new DOMElement('ano', $liq["ano"]));
            $detalles->appendChild(new DOMElement('valorbase', $liq["valorbase"]));
            $detalles->appendChild(new DOMElement('valorservicio', $liq["valorservicio"]));
            $detalles->appendChild(new DOMElement('pagoafiliacion', $liq["pagoafiliacion"]));

            //
            if ($arrTem["tiporegistro"] == 'RegPro') {
                $detalles->appendChild(new DOMElement('matricula', ''));
                $detalles->appendChild(new DOMElement('proponente', $liq["expediente"]));
            }

            //
            if ($arrTem["tiporegistro"] == 'RegMer' || $arrTem["tiporegistro"] == 'RegEsadl') {
                $detalles->appendChild(new DOMElement('matricula', $liq["expediente"]));
                $detalles->appendChild(new DOMElement('proponente', ''));
            }

            //
            if (
                $_SESSION["tramite"]["tipotramite"] == 'certificadosvirtuales' ||
                $_SESSION["tramite"]["tipotramite"] == 'certificadosespeciales'
            ) {
                switch ($liq["idservicio"]) {
                    case CERTIFICADOS_SERVI_MAT:
                    case CERTIFICADOS_SERVI_EXI:
                    case CERTIFICADOS_SERVI_LIB:
                    case CERTIFICADOS_SERVI_ESADL:
                    case CERTIFICADOS_SERVI_LIBESADL:
                        $detalles->appendChild(new DOMElement('matricula', $liq["expediente"]));
                        $detalles->appendChild(new DOMElement('proponente', ''));
                        break;
                    case CERTIFICADOS_SERVI_PROP:
                        $detalles->appendChild(new DOMElement('matricula', ''));
                        $detalles->appendChild(new DOMElement('proponente', $liq["expediente"]));
                        break;
                }
            }

            //
            if ($_SESSION["tramite"]["tipotramite"] == 'dispensadorcertificados') {
                switch ($liq["idservicio"]) {
                    case DISPENSADOR_CERTI_MAT:
                    case DISPENSADOR_CERTI_EXI:
                    case DISPENSADOR_CERTI_ESADL:
                    case DISPENSADOR_CERTI_LIBROS:
                    case DISPENSADOR_CERTI_LIBROS_ESADL:
                    case DISPENSADOR_CERTI_RUE_MAT:
                    case DISPENSADOR_CERTI_RUE_EXI:
                    case DISPENSADOR_CERTI_RUE_ESADL:
                        $detalles->appendChild(new DOMElement('matricula', $liq["expediente"]));
                        $detalles->appendChild(new DOMElement('proponente', ''));
                        break;
                    case DISPENSADOR_CERTI_PROP:
                    case DISPENSADOR_CERTI_RUE_PRO:
                        $detalles->appendChild(new DOMElement('matricula', ''));
                        $detalles->appendChild(new DOMElement('proponente', $liq["expediente"]));
                        break;
                }
            }

            //
            if ($_SESSION["tramite"]["tipotramite"] == 'prepago') {
                $detalles->appendChild(new DOMElement('matricula', ''));
                $detalles->appendChild(new DOMElement('proponente', ''));
            }

            //
            if ($_SESSION["tramite"]["tipotramite"] == 'serviciosempresariales') {
                $detalles->appendChild(new DOMElement('matricula', $liq["expediente"]));
                $detalles->appendChild(new DOMElement('proponente', ''));
            }


            $detalles->appendChild(new DOMElement('identificacion', $liq["identificacion"]));
            $detalles->appendChild(new DOMElement('organizacion', $liq["organizacion"]));
            $detalles->appendChild(new DOMElement('categoria', $liq["categoria"]));

            if ($encoding != 'no') {
                $detalles->appendChild(new DOMElement('razonsocial', '<![CDATA[' . base64_encode($liq["nombre"]) . ']]>'));
            } else {
                $detalles->appendChild(new DOMElement('razonsocial', '<![CDATA[' . $liq["nombre"] . ']]>'));
            }

            $detalles->appendChild(new DOMElement('paginainicial', $liq["paginainicial"]));
            $detalles->appendChild(new DOMElement('paginafinal', $liq["paginafinal"]));
            $detalles->appendChild(new DOMElement('codigolibro', $liq["codigolibro"]));
            $detalles->appendChild(new DOMElement('descripcionlibro', $liq["descripcionlibro"]));
            $detalles->appendChild(new DOMElement('idtipodoc', $liq["idtipodoc"]));
            $detalles->appendChild(new DOMElement('numdoc', $liq["numdoc"]));
            $detalles->appendChild(new DOMElement('origendoc', $liq["origendoc"]));
            $detalles->appendChild(new DOMElement('fechadoc', $liq["fechadoc"]));
            $detalles->appendChild(new DOMElement('estado', $liq["estado"]));
            $detalles->appendChild(new DOMElement('estadodatos', $liq["estadodatos"]));
            $detalles->appendChild(new DOMElement('benart7', $liq["benart7"]));
            if (!isset($liq["benley1780"])) {
                $detalles->appendChild(new DOMElement('benley1780', ''));
            } else {
                $detalles->appendChild(new DOMElement('benley1780', $liq["benley1780"]));
            }
            $detalles->appendChild(new DOMElement('ir', $liq["ir"]));
            $detalles->appendChild(new DOMElement('iva', $liq["iva"]));
            $tagliquidacion->appendChild($detalles);
        }


        // ***************************************************************************************** //
        // Asignar hijo al tag Padre
        // ***************************************************************************************** //
        $xmlDoc->appendChild($tagliquidacion);
        $xml = $xmlDoc->saveXML();
        $xml = str_replace("&lt;", "<", $xml);
        $xml = str_replace("&gt;", ">", $xml);
        unset($xmlDoc);

        \logApi::general2('serializarLiquidacion_' . date("Ymd"), __FUNCTION__, $xml);

        return $xml;
    }

    public static function sumarUno($fec)
    {
        $ano1 = intval(substr($fec, 0, 4));
        $mes1 = intval(substr($fec, 4, 2));
        $dia1 = intval(substr($fec, 6, 2));

        //
        $ok = 'no';
        if ($dia1 == 31) {
            $ok = 'si';
            if ($mes1 == 1 || $mes1 == 3 || $mes1 == 5 || $mes1 == 7 || $mes1 == 8 || $mes1 == 10) {
                $dia1 = 1;
                $mes1++;
            }
            if ($mes1 == 12) {
                $ano1++;
                $mes1 = 1;
                $dia1 = 1;
            }
        }
        if ($dia1 == 30) {
            $ok = 'si';
            if ($mes1 == 4 || $mes1 == 6 || $mes1 == 11) {
                $dia1 = 1;
                $mes1++;
            } else {
                $dia1++;
            }
        }
        if ($dia1 == 29) {
            $ok = 'si';
            if ($mes1 == 2) {
                $dia1 = 1;
                $mes1++;
            } else {
                $dia1++;
            }
        }
        if ($dia1 == 28) {
            $ok = 'si';
            if ($mes1 == 2) {
                if (\funcionesGenerales::anoBisiesto($ano1)) {
                    $dia1++;
                } else {
                    $dia1 = 1;
                    $mes1++;
                }
            } else {
                $dia1++;
            }
        }
        if ($ok == 'no') {
            $dia1++;
        }
        $fecsal = sprintf("%04s", $ano1) . sprintf("%02s", $mes1) . sprintf("%02s", $dia1);
        return $fecsal;
    }

    //
    public static function tamanoArchivo($file)
    {
        if (!file_exists($file)) {
            return 0;
        } else {
            return (filesize($file));
        }
    }

    public static function tamano80SentarPago($txt)
    {
        $salida = '';
        if (strlen($txt) > 78) {
            $salida = substr($txt, 0, 78);
        } else {
            $salida = sprintf("%-78s", $txt);
        }
        return $salida;
    }

    public static function tiffToPdf($file_tif, $file_pdf, $format = 'Letter')
    {
        if (!file_exists($file_tif))
            return 1;
        // exec($_SESSION["generales"]["pathabsoluto"] . '/includes/pstill_dist/pstill -c -c -c -g -i -p -t -J 70 -o ' . $file_pdf . ' ' . $file_tif . ' >> ../../tmp/visor.txt');
        // exec($_SESSION["generales"]["pathabsoluto"] . '/components/pstill_dist_x64/pstill64 -c -c -c -g -i -p -t -J 70 -o ' . $file_pdf . ' ' . $file_tif . ' >> ../../tmp/visor.txt');
        // shell_exec("tiff2pdf -o " . $file_pdf . " " .  $file_tif . " -j -q 80 -z >> " . PATH_ABSOLUTO_LOGS . "/" . $_SESSION["generales"]["pathabsoluto"] . "-tiffToPdf-" . date("Ymd") . ".log");
        exec("tiff2pdf -o " . $file_pdf . " " .  $file_tif . " -j -q 80 -z");
        return 0;
    }

    public static function truncateFinancialIndexes($number)
    {
        $sep = explode(",", $number);
        if (isset($sep[1])) {
            if (strlen($sep[1]) == 1) {
                $number = $sep[0] . ',' . $sep[1] . '0';
            }
            if (strlen($sep[1]) > 1) {
                $number = $sep[0] . ',' . substr($sep[1], 0, 2);
            }
        }
        return $number;
    }

    public static function truncateFinancialIndexesSinRedondeo($number)
    {
        $sep = explode(".", $number);
        if (isset($sep[1])) {
            if (strlen($sep[1]) == 1) {
                $number = $sep[0] . '.' . $sep[1] . '0';
            }
            if (strlen($sep[1]) > 1) {
                $number = $sep[0] . '.' . substr($sep[1], 0, 2);
            }
        }
        return $number;
    }

    public static function truncateFloat($number, $digitos, $pd = '.', $pm = ',')
    {
        $raiz = 10;
        $multiplicador = pow($raiz, $digitos);
        $resultado = (int) $number * (int) $multiplicador / (int) $multiplicador;
        $x = number_format($resultado, $digitos, $pd, $pm);
        $x = str_replace(",", "", $x);
        return $x;
    }

    public static function truncateFloatForm($number, $digitos, $pd = '.', $pm = ',')
    {
        $raiz = 10;
        $multiplicador = pow($raiz, $digitos);
        $resultado = (int) $number * (int) $multiplicador / (int) $multiplicador;
        $x = number_format($resultado, $digitos, $pd, $pm);
        return $x;
    }

    public static function truncarValorNuevoFormulario($valor)
    {
        // return truncarValorNuevo($valor);        

        if ($valor < 0) {
            $signo = '-';
        } else {
            $signo = '';
        }
        $valor = str_replace(".", ",", $valor);
        $valor = str_replace("-", "", $valor);
        $valor = str_replace(".", ",", $valor);
        $rgv = explode(",", $valor);
        $valt = '';
        if (isset($rgv[0]) && $rgv[0] != '' && is_numeric($rgv[0])) {
            $valt = number_format($rgv[0], 0, "", ".") . ',';
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
        }
        return $signo . $valt;
    }

    public static function truncarValorNuevoFormularioMercantil($valor)
    {

        if ($valor < 0) {
            $signo = '-';
        } else {
            $signo = '';
        }
        $valor = str_replace("-", "", $valor);
        $rgv = explode(".", $valor);
        $valt = '';
        if (doubleval($rgv[0]) == 0) {
            $rgv[0] = 0;
        }
        $valt = number_format($rgv[0], 0, "", ",") . '.';
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

    //
    public static function retornarAnomesTextual($anomes)
    {
        $sal = '';
        switch (substr($anomes, 4)) {
            case "01":
                $sal = "Enero del " . substr($anomes, 0, 4);
                break;
            case "02":
                $sal = "Febrero del " . substr($anomes, 0, 4);
                break;
            case "03":
                $sal = "Marzo del " . substr($anomes, 0, 4);
                break;
            case "04":
                $sal = "Abril del " . substr($anomes, 0, 4);
                break;
            case "05":
                $sal = "Mayo del " . substr($anomes, 0, 4);
                break;
            case "06":
                $sal = "Junio del " . substr($anomes, 0, 4);
                break;
            case "07":
                $sal = "Julio del " . substr($anomes, 0, 4);
                break;
            case "08":
                $sal = "Agosto del " . substr($anomes, 0, 4);
                break;
            case "09":
                $sal = "Septiembre del " . substr($anomes, 0, 4);
                break;
            case "10":
                $sal = "Octubre del " . substr($anomes, 0, 4);
                break;
            case "11":
                $sal = "Noviembre del " . substr($anomes, 0, 4);
                break;
            case "12":
                $sal = "Diciembre del " . substr($anomes, 0, 4);
                break;
        }
        return $sal;
    }

    //
    public static function retornarArregloAdmonsDian($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'bas_admindian', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
        }
        return $sal;
    }

    //
    public static function retornarArregloClasesGenericasEsadl($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_clase_esadl_gen', "1=1", "descripcion");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["id"] . ' - ' . substr($t["descripcion"], 0, 40);
        }
        return $sal;
    }

    //
    public static function retornarArregloCondicionEspecialLey2219($dbx)
    {
        $sal = array();
        $sal["NN"] = 'Sin condición especial';
        $sal["VV"] = 'Víctimas de la violencia';
        $sal["MC"] = 'Mujeres campesinas cabeza de familia';
        $sal["PC"] = 'Población campesina atendida por el programa PNIS ...';
        $sal["CC"] = 'Comunidades campesinas habitantes de los municipios...';
        return $sal;
    }

    //
    public static function retornarArregloBeneficioLey1780($dbx)
    {
        $sal = array();
        $sal['S'] = 'Si es beneficiario';
        $sal['N'] = 'No es benerficiario';
        return $sal;
    }

    //
    public static function retornarArregloControlSocios($dbx)
    {
        $sal = array();
        $sal['UC'] = 'Socio único controlante';
        $sal['UCB1780'] = 'Socio único controlante, beneficio ley 1780';
        $sal['UN'] = 'Socio único no controlante';
        $sal['MUL'] = 'Múltiples socios';
        return $sal;
    }

    //
    public static function retornarArregloClasesEspecialesEsadl($dbx, $restringirdian = '')
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_clase_esadl', "1=1", "descripcion");
        $sal = array();
        foreach ($tems as $t) {
            if ($t["mostrar"] == 'S') {
                if ($restringirdian == 'si') {
                    if (
                        (trim((string) $t["dian064"]) != '' && $t["dian064"] != '99') ||
                        (trim((string) $t["dian065"]) != '' && $t["dian065"] != '99') ||
                        (trim((string) $t["dian066"]) != '' && $t["dian066"] != '99') ||
                        (trim((string) $t["dian067"]) != '' && $t["dian067"] != '99') ||
                        (trim((string) $t["dian068"]) != '' && $t["dian068"] != '99') ||
                        (trim((string) $t["dian069"]) != '' && $t["dian069"] != '99')
                    ) {
                        $sal[$t["id"]] = $t["id"] . ' - ' . substr($t["descripcion"], 0, 50) . ' (' . $t["codigorues"] . ')';
                    }
                } else {
                    if (
                        (trim((string) $t["dian064"]) != '' && $t["dian064"] != '99') ||
                        (trim((string) $t["dian065"]) != '' && $t["dian065"] != '99') ||
                        (trim((string) $t["dian066"]) != '' && $t["dian066"] != '99') ||
                        (trim((string) $t["dian067"]) != '' && $t["dian067"] != '99') ||
                        (trim((string) $t["dian068"]) != '' && $t["dian068"] != '99') ||
                        (trim((string) $t["dian069"]) != '' && $t["dian069"] != '99')
                    ) {
                        $sal[$t["id"]] = $t["id"] . ' - ' . substr($t["descripcion"], 0, 50) . ' (' . $t["codigorues"] . ')';
                    } else {
                        $sal[$t["id"]] = $t["id"] . ' - ' . substr($t["descripcion"], 0, 50) . ' (' . $t["codigorues"] . ') (No DIAN)';
                    }
                }
            }
        }
        return $sal;
    }

    //
    public static function retornarArregloClasesEconomiaSolidaria($dbx)
    {

        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_clase_econsoli', "1=1", "descripcion");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["id"] . ' - ' . substr($t["descripcion"], 0, 40);
        }
        return $sal;
    }

    //
    public static function retornarArregloExtDom($dbx)
    {
        $sal = array();
        $sal['N'] = 'NO';
        $sal['S'] = 'SI EN PROCESO';
        $sal['F'] = 'FINALIZADO';
        return $sal;
    }

    //
    public static function retornarArregloAutorizaciones($dbx)
    {
        $sal = array();
        $sal['SI'] = 'Si autorizo';
        $sal['NO'] = 'No autorizo';
        return $sal;
    }

    //
    public static function retornarArregloSN($dbx)
    {
        $sal = array();
        $sal['N'] = 'NO';
        $sal['S'] = 'SI';
        return $sal;
    }

    //
    public static function retornarArregloPagoIR($dbx)
    {
        $sal = array();
        $sal['no'] = 'No se acredita el pago del impuesto de registro';
        $sal['si'] = 'SI se acredita el pago del impuesto de registro';
        return $sal;
    }

    //
    public static function retornarArregloSNR($dbx)
    {
        $sal = array();
        $sal['N'] = 'NO';
        $sal['S'] = 'SI';
        $sal['R'] = 'Renuncia';
        return $sal;
    }

    //
    public static function retornarArregloSNP($dbx)
    {
        $sal = array();
        $sal['N'] = 'NO';
        $sal['S'] = 'SI';
        $sal['P'] = 'Beneficio perdido';
        return $sal;
    }

    //
    public static function retornarArregloTipoAportantes($dbx)
    {
        $sal = array();
        $sal['0'] = 'No reporta';
        $sal['1'] = '200 o más cotizantes';
        $sal['2'] = 'Menos de 200 cotizantes';
        $sal['3'] = 'Beneficiario art. 5 Ley 1429/2010';
        $sal['4'] = 'Aportante independiente';
        return $sal;
    }

    //
    public static function retornarArregloImpExp($dbx)
    {
        $sal = array();
        $sal['0'] = 'NO';
        $sal['1'] = '1.- Importador';
        $sal['2'] = '2.- Exportador';
        $sal['3'] = '3.- Importador+Exportador';
        return $sal;
    }

    //
    public static function retornarArregloAfiliacion($dbx)
    {
        $sal = array();
        $sal['0'] = 'NO';
        $sal['1'] = '1.- Afiliación activa';
        $sal['2'] = '2.- Ex afiliado';
        $sal['3'] = '3.- Aceptado';
        $sal['5'] = '5.- Desafiliación temporal';
        $sal['9'] = '9.- Afiliado potencial';
        return $sal;
    }

    //
    public static function retornarArregloMotivosDesafiliacion($dbx)
    {
        $sal = array();
        $sal['0'] = 'NO';
        $sal['2'] = 'Cancelación de la matrícula (2)';
        $sal['4'] = 'Decisión de la Junta Directiva (4)';
        $sal['6'] = 'Disolución (6)';
        $sal['3'] = 'No cumplimiento normativo (3)';
        $sal['7'] = 'No renovación oportuna (7)';
        $sal['5'] = 'Traslado de domicilio (5)';
        $sal['1'] = 'Voluntario (1)';
        $sal['9'] = 'Otros (9)';
        return $sal;
    }

    //
    public static function retornarArregloMotivosCancelacion($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_motivos_cancelacion', "1=1", "descripcion");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["id"] . ' - ' . substr($t["descripcion"], 0, 40);
        }
        return $sal;
    }

    //
    public static function retornarArregloEstadosLiquidacion($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_tipos_liquidacion', "1=1", "descripcion");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["id"] . ' - ' . substr($t["descripcion"], 0, 40);
        }
        return $sal;
    }

    //
    public static function retornarArregloNaturalezas($dbx)
    {
        $sal = array();
        $sal['0'] = '0.- Comercial';
        $sal['1'] = '1.- Civil genérica';
        $sal['2'] = '2.- Civil - limitada';
        $sal['3'] = '3.- Civil - anónima';
        $sal['4'] = '4.- Civil - Comandita simple';
        $sal['5'] = '5.- Civil - Comandita acciones';
        return $sal;
    }

    //
    public static function retornarArregloNaturalezasEsadl($dbx)
    {
        $sal = array();
        $sal['1'] = '1.- Fundación';
        $sal['2'] = '2.- Asociación';
        $sal['3'] = '3.- Corporación';
        $sal['4'] = '4.- Entidad de economía solidaria';
        return $sal;
    }

    //
    public static function retornarArregloEstadosPjur($dbx)
    {
        $sal = array();
        $sal['00'] = 'NO APLICA';
        $sal['01'] = 'ACTIVA';
        $sal['02'] = 'PREOPERATIVA';
        $sal['03'] = 'EN CONCORDATO';
        $sal['04'] = 'INTERVENIDA';
        $sal['05'] = 'EN LIQUIDACION';
        $sal['06'] = 'ACUERD. REESTRUCTURACION';
        $sal['07'] = 'OTROS';
        return $sal;
    }

    //
    public static function retornarArregloPyR($dbx)
    {
        $sal = array();
        $sal['P'] = 'P.- Pendiente';
        $sal['R'] = 'R.- Revisado';
        return $sal;
    }

    //
    public static function retornarArregloBarrios($dbx, $mun)
    {
        if (trim($mun) == '') {
            return array();
        }

        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_barriosmuni', "idmunicipio='" . $mun . "'", "nombre");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["idbarrio"]] = $t["nombre"] . ' (' . $t["idbarrio"] . ')';
        }
        return $sal;
    }

    //
    public static function retornarArregloCategorias($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'bas_categorias', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
        }
        return $sal;
    }

    //
    public static function retornarArregloEstadosMatricula($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_estadomatriculas', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
        }
        return $sal;
    }

    //
    public static function retornarArregloEstadosDatosMatricula($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_tablassirep', "idtabla='30'", "idcodigo");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["idcodigo"]] = $t["idcodigo"] . ' - ' . $t["descripcion"];
        }
        if (!isset($sal['N'])) {
            $sal['N'] = 'No cambia estado';
        }
        return $sal;
    }

    //
    public static function retornarArregloHabilitadoPublico($dbx)
    {
        $sal = array();
        $sal["N"] = "No habilitado";
        $sal["S"] = "Habilitado";
        return $sal;
    }

    //
    public static function retornarArregloEmprendimientoSocial($dbx)
    {
        $sal = array();
        $sal["N"] = "NO";
        $sal["S"] = "SI es Emprendimiento Social";
        return $sal;
    }

    //
    public static function retornarArregloCategoriasEmprendimientoSocial($dbx)
    {
        $sal = array();
        $lis = retornarRegistrosMysqliApi($dbx, 'tablas', "tabla='empsoc_categorias'", "campo1");
        if ($lis && !empty($lis)) {
            foreach ($lis as $l) {
                $sal[$l["idcodigo"]] = $l;
            }
        }
        return $sal;
    }

    //
    public static function retornarArregloBeneficiariosEmprendimientoSocial($dbx)
    {
        $sal = array();
        $lis = retornarRegistrosMysqliApi($dbx, 'tablas', "tabla='empsoc_beneficiarios'", "campo1");
        if ($lis && !empty($lis)) {
            foreach ($lis as $l) {
                $sal[$l["idcodigo"]] = $l;
            }
        }
        return $sal;
    }

    //
    public static function retornarArregloOrigenesPerJur($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_tablassirep', "idtabla='43'", "descripcion");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["idcodigo"]] = $t["idcodigo"] . ' - ' . $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloEstadosProponente($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_estadoproponentes', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["id"] . ' - ' . $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloTipoTramites($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'bas_tipotramites', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["id"] . ' - ' . $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloTipoTransaccion($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_tipotransaccion', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["id"] . ' - ' . $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloTipoTransaccionOrden($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_tipotransaccion_orden', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["id"] . ' - ' . $t["titulo"];
        }
        return $sal;
    }

    //
    public static function retornarArregloActos($dbx, $lib = '')
    {
        if ($lib != '') {
            $tems = retornarRegistrosMysqliApi($dbx, 'mreg_actos', "idlibro=" . $lib . "'", "idacto");
        } else {
            $tems = retornarRegistrosMysqliApi($dbx, 'mreg_actos', "1=1", "idlibro,idacto");
        }
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["idlibro"] . '-' . $t["idacto"]] = $t["idlibro"] . '-' . $t["idacto"] . ' - ' . $t["nombre"];
        }
        return $sal;
    }

    //
    public static function retornarArregloServicios($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_servicios', "1=1", "idservicio");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["idservicio"]] = $t["idservicio"] . ' - ' . $t["nombre"];
        }
        return $sal;
    }

    //
    public static function retornarArregloTipobase($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_tipobase', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["id"];
        }
        return $sal;
    }

    //
    public static function retornarArregloCodigosCertificas($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_codigos_certificas', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["id"] . ' - ' . $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloTransaccionesNombresCortos($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_transacciones_nombrecorto', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["id"] . ' - ' . $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloTamanoEmpresarial($dbx)
    {
        $arr = array();
        $arr["1"] = "Microempresa";
        $arr["2"] = "Pequeña empresa";
        $arr["3"] = "Mediana empresa";
        $arr["4"] = "Gran empresa";
        return $arr;
    }

    //
    public static function retornarArregloTamanoEmpresarialAplica($dbx)
    {
        $arr = array();
        $arr["0"] = "Aplica para todos";
        $arr["1"] = "Solo para microempresas";
        $arr["2"] = "Solo para pequeñas empresas";
        $arr["3"] = "Solo para medianas empresas";
        $arr["4"] = "Solo para grandes empresas";
        return $arr;
    }

    //
    public static function retornarArregloTipoImpuestoRegistro($dbx)
    {
        $arr = array();
        $arr["E"] = "E.- Exento";
        $arr["C"] = "C.- Con cuant&iacute;a (1) (0.7 o 0.3)";
        $arr["D"] = "D.- Con cuant&iacute;a (2) (0.3)";
        $arr["S"] = "S.- Sin cuant&iacute;a (1)";
        $arr["0.1"] = "0.1%";
        $arr["0.15"] = "0.15%";
        $arr["0.2"] = "0.2%";
        $arr["0.3"] = "0.3%";
        $arr["0.4"] = "0.4%";
        $arr["0.5"] = "0.5%";
        $arr["0.6"] = "0.6%";
        $arr["0.7"] = "0.7%";
        return $arr;
    }

    public static function retornarArregloOrigenesGenericos($dbx)
    {
        $arr = array();
        $arr["ALCALDIA_MUNICIPAL_DE_XXXXXX"] = "ALCALDIA MUNICIPAL DE XXXXXX";
        $arr["ASAMBLEA_DE_ACCIONISTAS"] = "ASAMBLEA DE ACCIONISTAS";
        $arr["ASAMBLEA_DE_ASOCIADOS"] = "ASAMBLEA DE ASOCIADOS";
        $arr["DIAN"] = "DIAN";
        $arr["EL_COMERCIANTE"] = "EL COMERCIANTE";
        $arr["JUNTA_DE_SOCIOS"] = "JUNTA DE SOCIOS";
        $arr["JUNTA_DIRECTIVA"] = "JUNTA DIRECTIVA";
        $arr["JUZGADO_....."] = "JUZGADO .....";
        $arr["NOTARIA_XX_DE_XXXXXXXX"] = "NOTARIA XX DE XXXXXXX";
        $arr["REGISTRADURIA_NACIONAL_DEL_ESTADO_CIVIL"] = "REGISTRADURIA NACIONAL DEL ESTADO CIVIL";
        return $arr;
    }

    public static function retornarArregloListaCertificadosGenerico($dbx)
    {
        $arr = array();
        $arr["M"] = "Matricula";
        $arr["E"] = "Existencia";
        $arr["L"] = "Libros";
        return $arr;
    }

    //
    public static function retornarArregloEstadosDatosPjur($dbx)
    {
        $sal = array();
        $sal['00'] = 'NO APLICA';
        $sal['01'] = 'ACTIVA';
        $sal['02'] = 'PREOPERATIVA';
        $sal['03'] = 'EN CONCORDATO';
        $sal['04'] = 'INTERVENIDA';
        $sal['05'] = 'EN LIQUIDACION';
        $sal['06'] = 'ACUERD. REESTRUCTURACION';
        $sal['07'] = 'OTROS';
        return $sal;
    }

    //
    public static function retornarArregloPresupuestosControl($dbx)
    {
        $sal = array();
        $sal[1] = 'Numeral 1 Artículo 261 Código de Comercio';
        $sal[2] = 'Numeral 2 Artículo 261 Código de Comercio';
        $sal[3] = 'Numeral 3 Artículo 261 Código de Comercio';
        $sal[4] = 'SAS - Socio único controlante - Artículo 2.2.2.41.6.1 Decreto 1074 de 2015';
        return $sal;
    }

    //
    public static function retornarArregloProindiviso($dbx)
    {
        $sal = array();
        $sal["N/A"] = 'No es socio';
        $sal["N"] = 'No aplica proindiviso';
        $sal["S1"] = 'Proindiviso - primera distribución';
        $sal["S2"] = 'Proindiviso - segunda distribución';
        $sal["S3"] = 'Proindiviso - tercera distribución';
        return $sal;
    }

    //
    public static function retornarArregloCodigosCertificaPoderes($dbx)
    {
        $sal = array();
        for ($ix = 9001; $ix <= 9099; $ix++) {
            $sal[$ix] = 'Poderes - ' . $ix;
        }
        return $sal;
    }

    /**
     * 
     * @param type $dbx
     * @param type $tipo ('', jurs, esadl)
     * @return string
     */
    public static function retornarArregloOrganizaciones($dbx, $tipo = '')
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'bas_organizacionjuridica', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            if ($tipo == '') {
                $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
            } else {
                if ($tipo == 'jurs') {
                    if ($t["id"] != '01' && $t["id"] != '02' && $t["id"] != '12' && $t["id"] != '14') {
                        $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
                    }
                } else {
                    if ($tipo == 'esadl') {
                        if ($t["id"] == '12' || $t["id"] == '14') {
                            $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
                        }
                    }
                }
            }
        }
        return $sal;
    }

    public static function retornarArregloOrganizacionesResumen($dbx, $tipo = '')
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'bas_orgres', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            if ($tipo == '') {
                $sal[$t["id"]] = $t["descripcion"];
            } else {
                if ($tipo == 'jurs') {
                    if ($t["id"] != '01' && $t["id"] != '02' && $t["id"] != '12' && $t["id"] != '14') {
                        $sal[$t["id"]] = $t["descripcion"];
                    }
                } else {
                    if ($tipo == 'esadl') {
                        if ($t["id"] == '12' || $t["id"] == '14') {
                            $sal[$t["id"]] = $t["descripcion"];
                        }
                    }
                }
            }
        }
        return $sal;
    }

    //
    public static function retornarArregloTipoVias($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'bas_tipovia', "1=1", "descripcion");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloApendices($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'bas_apendicevia', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloCamaras($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'bas_camaras', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["nombre"] . ' (' . $t["id"] . ')';
        }
        return $sal;
    }

    //
    public static function retornarArregloLibros($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_libros', "1=1", "idlibro");
        $sal = array();
        foreach ($tems as $m) {
            $sal[$m["idlibro"]] = $m["nombre"];
        }
        return $sal;
    }

    //
    public static function retornarArregloLibrosElectronicos($dbx)
    {
        $tems = retornarRegistrosMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='09' and tipo='XX'", "idcodigo");
        $sal = array();
        foreach ($tems as $m) {
            if ($m["idcodigo"] == '0005' || $m["idcodigo"] == '0075') {
                $sal[$m["idcodigo"]] = $m["descripcion"] . ' (' . $m["idcodigo"] . ')';
            }
        }
        return $sal;
    }

    //
    public static function retornarArregloPagoImpReg($dbx)
    {
        $sal = array();
        $sal['S'] = 'Ya está pago';
        $sal['N'] = 'Pendiente de pago';
        return $sal;
    }

    //
    public static function retornarArregloMotivosDisolucion($dbx)
    {
        $sal = array();
        $sal["01"] = "01.- Decisi&oacute;n de los socios";
        $sal["02"] = "02.- Vencimiento del t&eacute;rmino de duraci&oacute;n";
        $sal["03"] = "03.- Orden de autoridad competente";
        $sal["04"] = "04.- Por disminuci&oacute;n del capital";
        $sal["05"] = "05.- Por exceder el número de socios autorizados en la Ley";
        $sal["06"] = "06.- Por reducci&oacute;n  del n&uacute;mero de socios requeridos en la Ley";
        $sal["07"] = "07.- Por imposibilidad de desarrollar el objeto social";
        return $sal;
    }

    //
    public static function retornarArregloTipoSAS($dbx)
    {
        $sal = array();
        // $sal["no"] = "No es SAS";
        $sal["sascontrolante"] = "SAS con socio único controlante";
        $sal["sasnocontrolante"] = "SAS con socio único no controlante";
        $sal["sasmultiples"] = "SAS con múltiples socios";
        $sal["asimiladaanonima"] = "Anonima o asimilada";
        $sal["asimiladalimitada"] = "Limitada o asimilada";
        return $sal;
    }

    //
    public static function retornarArregloMotivosLiquidacion($dbx)
    {
        $sal = array();
        $sal["01"] = "01.- Como consecuencia de la disolución";
        $sal["03"] = "03.- Orden de autoridad competente";
        return $sal;
    }

    //
    public static function retornarArregloMotivosCacelacion($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_motivos_cancelacion', "1=1", "descripcion");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
        }
        return $sal;
    }

    //
    public static function retornarArregloGobernaciones($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'bas_gobernaciones', "1=1", "descripcion");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
        }
        return $sal;
    }

    //
    public static function retornarArregloOrientaciones($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'bas_orientacionvia', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloLetras($dbx)
    {
        $sal = array();
        $sal[""] = "";
        $sal["A"] = "A";
        $sal["B"] = "B";
        $sal["C"] = "C";
        $sal["D"] = "D";
        $sal["E"] = "E";
        $sal["F"] = "F";
        $sal["G"] = "G";
        $sal["H"] = "H";
        $sal["I"] = "I";
        $sal["J"] = "J";
        $sal["K"] = "K";
        $sal["L"] = "L";
        $sal["M"] = "M";
        $sal["N"] = "N";
        $sal["O"] = "O";
        $sal["P"] = "P";
        $sal["Q"] = "Q";
        $sal["R"] = "R";
        $sal["S"] = "S";
        $sal["T"] = "T";
        $sal["U"] = "U";
        $sal["V"] = "V";
        $sal["W"] = "W";
        $sal["X"] = "X";
        $sal["Y"] = "Y";
        $sal["Z"] = "Z";
        $sal["BIS"] = "BIS";
        return $sal;
    }

    //
    public static function retornarArregloLIbrosRegistrar($dbx)
    {
        $sal = array();
        $sal["no"] = "No registrar libros";
        $sal["LF"] = "Libro fisico";
        $sal["LE"] = "Libro electrónico";
        return $sal;
    }

    //
    public static function retornarArregloRenglones($dbx)
    {
        $sal = array();
        $sal["0001"] = 'Primer rengl&oacute;n';
        $sal["0002"] = 'Segundo rengl&oacute;n';
        $sal["0003"] = 'Tercer rengl&oacute;n';
        $sal["0004"] = 'Cuarto rengl&oacute;n';
        $sal["0005"] = 'Quinto rengl&oacute;n';
        $sal["0006"] = 'Sexto rengl&oacute;n';
        $sal["0007"] = 'S&eacute;ptimo rengl&oacute;n';
        $sal["0008"] = 'Octavo rengl&oacute;n';
        return $sal;
    }

    //
    public static function retornarArregloResponsabilidades($dbx, $tipo = '')
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'tablas', "tabla='responsabilidadestributarias'", "idcodigo");
        $sal = array();
        foreach ($tems as $t) {
            if ($t["campo1"] == 'SI') {
                if ($tipo == '') {
                    $sal[$t["idcodigo"]] = $t["idcodigo"] . '.- ' . $t["descripcion"];
                }
                if ($tipo == 'PNAT' && $t["campo2"] == 'SI') {
                    $sal[$t["idcodigo"]] = $t["idcodigo"] . '.- ' . $t["descripcion"];
                }
                if ($tipo == 'PJUR' && $t["campo3"] == 'SI') {
                    $sal[$t["idcodigo"]] = $t["idcodigo"] . '.- ' . $t["descripcion"];
                }
                if ($tipo == 'ESADL' && $t["campo4"] == 'SI') {
                    $sal[$t["idcodigo"]] = $t["idcodigo"] . '.- ' . $t["descripcion"];
                }
            }
        }
        return $sal;
    }

    //
    public static function retornarArregloFactoresFirmado($dbx)
    {
        $arr = array();
        $arr["NO-APLICA"] = 'NO APLICA';
        $arr["NADA"] = 'SIN FIRMA';
        $arr["CLAVE"] = 'SOLO CLAVE';
        $arr["PIN"] = 'SOLO PIN';
        $arr["DOBLE"] = 'CLAVE + PIN';
        return $arr;
    }

    //
    public static function retornarArregloFiltroBusqueda()
    {
        $arr = array();
        $arr["1"] = 'Matricula';
        $arr["2"] = 'Proponente';
        $arr["3"] = 'Identificación';
        $arr["4"] = 'Nombre o razón social';
        $arr["5"] = 'Palabra clave';
        return $arr;
    }

    //
    public static function retornarArregloActivarPagoBancos($dbx)
    {
        $arr = array();
        $arr["s1"] = 'SI - Firmados';
        $arr["s2"] = 'SI - Sin firma';
        $arr["no"] = 'No habilitar';
        return $arr;
    }

    //
    public static function retornarArregloTipoIdentificacion($dbx, $filtro = '')
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_tipoidentificacion', "1=1", "descripcion");
        $sal = array();
        foreach ($tems as $t) {
            if ($filtro == '') {
                $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
            }
            if ($filtro == 'constitucion') {
                if ($t["constitucion"] == 'X') {
                    $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
                }
            }
            if ($filtro == 'reforma') {
                if ($t["reforma"] == 'X') {
                    $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
                }
            }
            if ($filtro == 'mutacion') {
                if ($t["mutacion"] == 'X') {
                    $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
                }
            }
            if ($filtro == 'nombramientos') {
                if ($t["nombramientos"] == 'X') {
                    $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
                }
            }
            if ($filtro == 'otros') {
                if ($t["otros"] == 'X') {
                    $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
                }
            }
        }
        return $sal;
    }

    /**
     * 
     * @param type $dbx
     * @param type $filtro (vacio, constitucion, reforma, mutacion, nombramientos, otros
     * @return string
     */
    public static function retornarArregloTipoDocumentos($dbx, $filtro = '')
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_tipos_documentales_registro', "1=1", "descripcion");
        $sal = array();
        foreach ($tems as $t) {
            if ($filtro == '') {
                $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
            }
            if ($filtro == 'constitucion') {
                if ($t["constitucion"] == 'X') {
                    $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
                }
            }
            if ($filtro == 'reforma') {
                if ($t["reforma"] == 'X') {
                    $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
                }
            }
            if ($filtro == 'mutacion') {
                if ($t["mutacion"] == 'X') {
                    $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
                }
            }
            if ($filtro == 'nombramientos') {
                if ($t["nombramientos"] == 'X') {
                    $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
                }
            }
            if ($filtro == 'otros') {
                if ($t["otros"] == 'X') {
                    $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
                }
            }
        }
        return $sal;
    }

    public static function retornarArregloTipoDocumentosTablaRetencion($dbx, $filtro = '')
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'bas_tipodoc', "1=1", "nombre");
        $sal = array();
        foreach ($tems as $t) {
            if (strlen($t["idtipodoc"]) == 9) {
                if ($filtro == '') {
                    $sal[$t["idtipodoc"]] = $t["nombre"] . ' (' . $t["idtipodoc"] . ')';
                }
                if (is_numeric($filtro)) {
                    if (substr($t["idtipodoc"], 0, 2) == $filtro) {
                        $sal[$t["idtipodoc"]] = $t["nombre"] . ' (' . $t["idtipodoc"] . ')';
                    }
                }
            }
        }
        return $sal;
    }

    //
    public static function retornarArregloTipoDatosControlante($dbx)
    {
        $sal = array(
            '1' => 'SAS con socio único controlante',
            '2' => 'SAS con socio único NO controlante, controlante conocido',
            '3' => 'SAS con socio único NO controlante, controlante desconocido',
            '4' => 'SAS con múltples socios'
        );
        return $sal;
    }

    //
    public static function retornarArregloMunicipios($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'bas_municipios', "1=1", "ciudad");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["codigomunicipio"]] = $t["ciudad"] . ' (' . substr($t["departamento"], 0, 3) . ')';
        }
        return $sal;
    }

    //
    public static function retornarArregloOrigenesGenericos1($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'tablas', "tabla='origenesgenericos'", "descripcion");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["descripcion"]] = $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloTerminosCondiciones($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'tablas', "tabla='tyc'", "descripcion");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["descripcion"]] = $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloEstadosRuta($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_codestados_rutamercantil', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["descripcion"];
        }
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_codestados_rutaproponentes', "1=1", "id");
        foreach ($tems as $t) {
            if (!isset($sal[$t["id"]])) {
                $sal[$t["id"]] = $t["descripcion"];
            }
        }
        return $sal;
    }

    //
    public static function retornarArregloEstadosRutaComplejo($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_codestados_rutamercantil', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t;
        }
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_codestados_rutaproponentes', "1=1", "id");
        foreach ($tems as $t) {
            if (!isset($sal[$t["id"]])) {
                $sal[$t["id"]] = $t;
            }
        }
        return $sal;
    }

    //
    public static function retornarArregloCodigosRutas($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_codrutas', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloCodigosRutasComplejo($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_codrutas', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t;
        }
        return $sal;
    }

    //
    public static function retornarArregloSumarComo($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'tablas', "tabla='sumarcomo'", "descripcion");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["descripcion"]] = $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloMunicipiosJurisdiccion($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_municipiosjurisdiccion', "1=1", "idcodigo");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["idcodigo"]] = retornarRegistroMysqliApi($dbx, 'bas_municipios', "codigomunicipio='" . $t["idcodigo"] . "'", "ciudad");
        }
        return $sal;
    }

    //
    public static function retornarArregloNiifs($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'bas_gruponiif', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloNomenclatura($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'bas_tipovia', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloPaises($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'bas_paises', "1=1", "codnumpais");
        $sal = array();
        foreach ($tems as $t) {
            $sal['169'] = 'Colombia' . ' (169)';
            if ($t["codnumpais"] != '' && $t["codnumpais"] != '169') {
                $sal[$t["codnumpais"]] = $t["nombrepais"] . ' (' . $t["codnumpais"] . ')';
            }
        }
        return $sal;
    }

    //
    public static function retornarArregloSedes($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'bas_tipo_sede', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloSexos($dbx)
    {
        $sal = array();
        $sal['F'] = 'Femenino';
        $sal['M'] = 'Masculino';
        // $sal['O'] = 'Otros';
        return $sal;
    }

    //
    public static function retornarArregloEstadosNit($dbx)
    {
        $sal = array();
        $sal['0'] = 'OK';
        $sal['2'] = 'Temporal';
        $sal['3'] = 'No formalizado';
        $sal['4'] = 'Formalizar ante DIAN';
        return $sal;
    }

    //
    public static function retornarArregloTipoBics($dbx)
    {
        $sal = array();
        $sal['N'] = 'No es BIC';
        $sal['S'] = 'Es BIC';
        return $sal;
    }

    //
    public static function retornarArregloTipoLocales($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_tipolocal', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
        }
        return $sal;
    }

    //
    public static function retornarArregloTipoRegistros($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'bas_tiporegistros', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloBandejasRegistro($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_bandejas_digitalizacion', "1=1", "idbandeja");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["idbandeja"]] = $t["nombre"];
        }
        return $sal;
    }

    //
    public static function retornarArregloGrupoServicios($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_grupo_servicios', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloTitularesRecibos($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_titular_recibos', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloUbicaciones($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_ubicacion', "1=1", "id");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
        }
        return $sal;
    }

    //
    public static function retornarArregloCodigosVinculosDianPnat($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_tablassirep', "idtabla='90'", "descripcion");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["idcodigo"]] = $t["descripcion"] . ' (' . $t["idcodigo"] . ')';
        }
        return $sal;
    }

    //
    public static function retornarArregloCodigosVinculosDianPjur($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_tablassirep', "idtabla='91'", "descripcion");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["idcodigo"]] = $t["descripcion"] . ' (' . $t["idcodigo"] . ')';
        }
        return $sal;
    }

    //
    public static function retornarArregloInscripcionesMatricula($dbx, $mat)
    {
        $reg1 = retornarRegistrosMysqliApi($dbx, 'mreg_est_inscripciones', "matricula='" . ltrim($mat, "0") . "'", "matricula,fecharegistro,libro,registro");
        $sal = array();
        $sal["registros"] = array();
        $sal["select"] = array();
        $i = 0;
        foreach ($reg1 as $reg) {
            $i++;
            $sal["registros"][$i] = array();
            $sal["registros"][$i]["libro"] = $reg["libro"];
            $sal["registros"][$i]["registro"] = ltrim($reg["registro"], "0");
            $sal["registros"][$i]["dupli"] = $reg["dupli"];
            $sal["registros"][$i]["fecha"] = $reg["fecharegistro"];
            $sal["registros"][$i]["tipodoc"] = $reg["tipodocumento"];
            $sal["registros"][$i]["numdoc"] = ltrim($reg["numerodocumento"], "0");
            $sal["registros"][$i]["acto"] = trim($reg["acto"]);
            $sal["registros"][$i]["txtacto"] = substr(trim($reg["noticia"]), 0, 50);
            $ind = $reg["libro"] . '-' . $reg["registro"] . '-' . $reg["dupli"] . '-' . $reg["fecharegistro"];
            $ind1 = $reg["libro"] . '-' . $reg["registro"] . '-' . $reg["dupli"] . '-' . $reg["fecharegistro"] . ' - ' . $sal["registros"][$i]["txtacto"];
            $sal["select"][$ind] = $ind1;
        }
        unset($reg);
        unset($reg1);
        return $sal;
    }

    //
    public static function retornarArregloCiius($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'bas_ciius', "1=1", "idciiu");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["idciiu"]] = $t["descripcion"];
        }
        return $sal;
    }

    //
    public static function retornarArregloCodigosCargos($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_tablassirep', "idtabla='14'", "descripcion");
        $sal = array();
        foreach ($tems as $t) {
            $sal[$t["idcodigo"]] = $t["descripcion"] . ' (' . $t["idcodigo"] . ')';
        }
        return $sal;
    }

    //
    public static function retornarArregloCodigosVinculos($dbx)
    {
        $tems = retornarRegistrosMysqliApi($dbx, 'mreg_codvinculos', "1=1", "descripcion");
        $sal = array();
        foreach ($tems as $t) {
            if (trim($t["tipovinculo"]) != '' || trim($t["tipovinculocereszadl"]) != '') {
                $sal[$t["id"]] = $t["descripcion"] . ' (' . $t["id"] . ')';
            }
        }
        return $sal;
    }

    //
    public static function retornarArregloZonas($dbx)
    {
        $sal = array();
        $sal['U'] = 'Urbana';
        $sal['R'] = 'Rural';
        return $sal;
    }

    public static function retornarClaveMaestra($usua = '', $identificacion = '')
    {
        if (!defined('URL_RETORNO_CLAVE_MAESTRA') || URL_RETORNO_CLAVE_MAESTRA == '') {
            $url = 'http://siiconfe.confecamaras.co/librerias/ws/retornarClaveMaestra.php';
            $db = '';
        } else {
            $url = URL_RETORNO_CLAVE_MAESTRA;
            $db = URL_RETORNO_CLAVE_MAESTRA_BD;
        }

        //
        $clave = '';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'Consumo de request por CURL',
            CURLOPT_POST => 1,
            CURLOPT_CONNECTTIMEOUT => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POSTFIELDS => array(
                'token' => base64_encode("025d43b1978d96e4d40d2316c90e3caa"),
                'usuario' => base64_encode($usua),
                'identificacion' => base64_encode((string)$identificacion),
                'db' => base64_encode($db)
            )
        ));
        $clave = curl_exec($curl);

        curl_close($curl);
        if (substr($clave, 0, 2) == 'NO') {
            return "";
        } else {
            return $clave;
        }
    }

    public static function retornarCodigoEmpresa($httphost = '')
    {
        $xml = simplexml_load_file($_SESSION["generales"]["pathabsoluto"] . '/configuracion/empresas.php');
        $salida = "";
        foreach ($xml->empresa as $tempresa) {
            $cod = (string) $tempresa->codigo;
            $host = (string) $tempresa->host;
            $pre = (string) $tempresa->preseleccion;
            $act = (string) $tempresa->activado;
            if ($httphost != '') {
                if ($httphost == $host) {
                    if ($act == 'S' && $pre == 'si') {
                        if ($salida == '') {
                            $salida = $cod;
                        }
                    }
                }
            }
        }
        if ($salida == "") {
            $ix = 0;
            foreach ($xml->empresa as $tempresa) {
                $ix++;
                if ($ix == 1) {
                    $cod = (string) $tempresa->codigo;
                    $salida = $cod;
                }
            }
        }
        return $salida;
    }

    public static function retornarDispositivo()
    {
        if (defined('FORZAR_MOVIL') && FORZAR_MOVIL == 'SI') {
            return "mobile";
        }

        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/Mobile_Detect_New.php');
        $return = 'computer';
        $disp = new Mobile_Detect_New();
        if ($disp->isMobile()) {
            $return = 'mobile';
        }
        if ($disp->isTablet()) {
            $return = 'tablet';
        }
        unset($disp);
        $_SESSION["generales"]["tipodispositivo"] = $return;
        return $return;
    }

    public static function retornarIdentificacionLimpia($ide)
    {
        $ide = ltrim($ide, "0");
        $ide = ltrim($ide, " ");
        $ide = rtrim($ide, " ");
        $eliminar = array("-", ".", ",", "_", " ");
        $ide = str_replace($eliminar, "", $ide);
        return $ide;
    }

    public static function retornarDescripcionCiiu($dbx, $ciiu = '', $version = '*')
    {
        if ($version == '*' || $version == '4') {
            $result = retornarRegistroMysqliApi($dbx, 'bas_ciius', "idciiu='" . trim($ciiu) . "'");
        }
        if ($version == '3.1') {
            $result = retornarRegistroMysqliApi($dbx, 'bas_ciius_3_1', "idciiu='" . trim($ciiu) . "'");
        }

        $retornar = '';
        if ($result && !empty($result)) {
            $retornar = $result["descripcion"];
        }
        unset($result);
        return $retornar;
    }

    public static function retornarGrupoNiifFormulario($dbx, $id)
    {
        $res = retornarRegistroMysqliApi($dbx, 'bas_gruponiif', "id='" . $id . "'");
        return $res["idformulario"];
    }

    public static function retornarGrupoNiifFormulario20220925($dbx, $id)
    {
        $res = retornarRegistroMysqliApi($dbx, 'bas_gruponiif', "id='" . $id . "'");
        return $res["idformularionew"];
    }

    public static function retornarGrupoNiifFormularioDescripcion($dbx, $id)
    {
        $res = retornarRegistroMysqliApi($dbx, 'bas_gruponiif', "id='" . $id . "'");
        return str_replace(array("1.- ", "2.- ", "3.- ", "4.- ", "5.- ", "6.- ", "7.- "), "", $res["descripcion"]);
    }

    public static function retornarLabel($dbx, $label)
    {
        $salida = '';
        if ($dbx === null) {
            $cerrarMysql = 'si';
            $dbx = conexionMysqliApi();
        } else {
            $cerrarMysql = 'no';
        }
        if (isset($_SESSION["generales"]["idioma"]) && $_SESSION["generales"]["idioma"] == 'es') {
            $salida = stripslashes(retornarRegistroMysqliApi($dbx, "tablas", "tabla='labels' and idcodigo='" . $label . "'", "campo1"));
        } else {
            $salida = stripslashes(retornarRegistroMysqliApi($dbx, "tablas", "tabla='labels' and idcodigo='" . $label . "'", "campo2"));
        }
        if ($salida == '') {
            $salida = $label;
        }

        //
        if ($cerrarMysql == 'si') {
            $dbx->close();
        }

        //
        return $salida;
    }

    public static function retornarListaXmlEmpresas($emp = '', $httphost = '')
    {
        $salida = array();
        $xml = simplexml_load_file($_SESSION["generales"]["pathabsoluto"] . '/configuracion/empresas.php');
        foreach ($xml->empresa as $tempresa) {
            $cod = (string) $tempresa->codigo;
            $nom = (string) $tempresa->nombre;
            $salida[$cod] = $nom;
        }
        return $salida;
    }

    public static function retornarListaEmpresas($server = '', $codigoempresa = '')
    {
        $codsalida = '';
        $cuantos = 0;
        $salidaunica = array();
        $salida = array();
        $xml = simplexml_load_file($_SESSION["generales"]["pathabsoluto"] . '/configuracion/empresas.php');
        foreach ($xml->empresa as $tempresa) {
            $cod = (string) $tempresa->codigo;
            $nom = (string) $tempresa->nombre;
            $host = (string) $tempresa->host;
            if (!isset($tempresa->activado) || $tempresa->activado == 'S') {
                if ($codigoempresa != '' && $codigoempresa == $cod) {
                    $salidaunica[$cod] = $nom;
                    $codsalida = $cod;
                    $cuantos = 1;
                } else {
                    $salida[$cod] = $nom;
                    if ($host == $server) {
                        $cuantos++;
                        $salidaunica[$cod] = $nom;
                        $codsalida = $cod;
                    }
                }
            }
        }
        if ($cuantos == 1) {
            $_SESSION["generales"]["codigoempresa"] = $codsalida;
            return $salidaunica;
        } else {
            return $salida;
        }
    }

    public static function retornarNombrePais($dbx, $id)
    {

        if (is_numeric($id)) {
            $reg = retornarRegistroMysqliApi($dbx, 'bas_paises', "codnumpais='" . $id . "'");
            if ($reg && !empty($reg)) {
                return $reg["nombrepais"];
            } else {
                return "";
            }
        } else {
            $reg = retornarRegistroMysqliApi($dbx, 'bas_paises', "idpais='" . $id . "'");
            if ($reg && !empty($reg)) {
                return $reg["nombrepais"];
            } else {
                return "";
            }
        }
    }

    public static function retornarNombrePaisAbreviado($dbx, $id)
    {

        if (is_numeric($id)) {
            $reg = retornarRegistroMysqliApi($dbx, 'bas_paises', "codnumpais='" . $id . "'");
            if ($reg && !empty($reg)) {
                return $reg["idpais"];
            } else {
                return "";
            }
        } else {
            return $id;
        }
    }

    //
    public static function reemplazarAcutes($txt)
    {
        if ($txt != '') {
            $txt = str_replace("&amp;", "&", $txt);
            $txt = str_replace("&AMP;", "&", $txt);
            $txt = str_replace("&aacute;", "á", $txt);
            $txt = str_replace("&eacute;", "é", $txt);
            $txt = str_replace("&iacute;", "í", $txt);
            $txt = str_replace("&oacute;", "ó", $txt);
            $txt = str_replace("&uacute;", "ú", $txt);
            $txt = str_replace("&Aacute;", "Á", $txt);
            $txt = str_replace("&Eacute;", "É", $txt);
            $txt = str_replace("&Iacute;", "Í", $txt);
            $txt = str_replace("&Oacute;", "Ó", $txt);
            $txt = str_replace("&Uacute;", "Ú", $txt);
            $txt = str_replace("&AACUTE;", "Á", $txt);
            $txt = str_replace("&EACUTE;", "É", $txt);
            $txt = str_replace("&IACUTE;", "Í", $txt);
            $txt = str_replace("&OACUTE;", "Ó", $txt);
            $txt = str_replace("&UACUTE;", "Ú", $txt);
            $txt = str_replace("&ntilde;", "ñ", $txt);
            $txt = str_replace("&Ntilde;", "Ñ", $txt);
            $txt = str_replace("&NTILDE;", "Ñ", $txt);
            $txt = str_replace("&NBSP;", " ", $txt);
        }
        return $txt;
    }

    public static function reemplazarEspeciales($txt)
    {
        if ($txt != '') {
            //
            $txt = str_replace("\"", "[0]", $txt);
            $txt = str_replace("'", "[1]", $txt);
            $txt = str_replace("&", "[2]", $txt);
            // $txt = str_replace("?", "[3]", $txt);
            $txt = str_replace("á", "[4]", $txt);
            $txt = str_replace("é", "[5]", $txt);
            $txt = str_replace("í", "[6]", $txt);
            $txt = str_replace("ó", "[7]", $txt);
            $txt = str_replace("ú", "[8]", $txt);
            $txt = str_replace("ñ", "[9]", $txt);
            $txt = str_replace("Ñ", "[10]", $txt);
            // $txt = str_replace("+", "[11]", $txt);
            // $txt = str_replace("#", "[12]", $txt);
            $txt = str_replace("Á", "[13]", $txt);
            $txt = str_replace("É", "[14]", $txt);
            $txt = str_replace("Í", "[15]", $txt);
            $txt = str_replace("Ó", "[16]", $txt);
            $txt = str_replace("Ú", "[17]", $txt);
            $txt = str_replace("Ü", "[18]", $txt);
            $txt = str_replace("º", "[19]", $txt);
            $txt = str_replace("°", "[20]", $txt);
            //
            $txt = str_replace("ª", "[21]", $txt);
            $txt = str_replace("!", "[22]", $txt);
            $txt = str_replace("¡", "[23]", $txt);
            $txt = str_replace("'", "[24]", $txt);
            $txt = str_replace("´", "[25]", $txt);
            $txt = str_replace("`", "[26]", $txt);
            //
            $txt = str_replace("À", "[28]", $txt);
            $txt = str_replace("È", "[29]", $txt);
            $txt = str_replace("Ì", "[30]", $txt);
            $txt = str_replace("Ò", "[31]", $txt);
            $txt = str_replace("Ù", "[32]", $txt);
            //
            $txt = str_replace("à", "[33]", $txt);
            $txt = str_replace("è", "[34]", $txt);
            $txt = str_replace("ì", "[35]", $txt);
            $txt = str_replace("ò", "[36]", $txt);
            $txt = str_replace("ù", "[37]", $txt);

            //
            $txt = str_replace("©", "@", $txt);

            //             
            $txt = str_replace("[SALTOPARRAFO]", "", $txt);
        }
        return $txt;
    }

    /**
     * Funci&oacute;n que recibe un texto y reemplaza caracteres especiales por tags
     * Utilizado para enviar la informaci&oacute;n al SIREP
     *
     * @param 	string		$txt	Texto a convertir
     * @return 	string				Texto convertido
     */
    public static function reemplazarEspecialesDom($txt)
    {
        if ($txt != '') {
            $txt = str_replace("&", "[2]", $txt);
            $txt = str_replace("á", "[4]", $txt);
            $txt = str_replace("é", "[5]", $txt);
            $txt = str_replace("í", "[6]", $txt);
            $txt = str_replace("ó", "[7]", $txt);
            $txt = str_replace("ú", "[8]", $txt);
            $txt = str_replace("ñ", "[9]", $txt);
            $txt = str_replace("Ñ", "[10]", $txt);
            $txt = str_replace("Á", "[13]", $txt);
            $txt = str_replace("É", "[14]", $txt);
            $txt = str_replace("Í", "[15]", $txt);
            $txt = str_replace("Ó", "[16]", $txt);
            $txt = str_replace("Ú", "[17]", $txt);
            $txt = str_replace("Ü", "[18]", $txt);
            $txt = str_replace("º", "[19]", $txt);
            $txt = str_replace("°", "[20]", $txt);
            //
            $txt = str_replace("ª", "[21]", $txt);
            // $txt = str_replace("!", "[22]", $txt);
            $txt = str_replace("¡", "[23]", $txt);
            $txt = str_replace("'", "[24]", $txt);
            $txt = str_replace("´", "[25]", $txt);
            $txt = str_replace("`", "[26]", $txt);
            //
            $txt = str_replace("À", "[28]", $txt);
            $txt = str_replace("È", "[29]", $txt);
            $txt = str_replace("Ì", "[30]", $txt);
            $txt = str_replace("Ò", "[31]", $txt);
            $txt = str_replace("Ù", "[32]", $txt);
            //
            $txt = str_replace("à", "[33]", $txt);
            $txt = str_replace("è", "[34]", $txt);
            $txt = str_replace("ì", "[35]", $txt);
            $txt = str_replace("ò", "[36]", $txt);
            $txt = str_replace("ù", "[37]", $txt);
        }
        //
        return $txt;
    }

    public static function reemplazarEspecialesDomRee($txt)
    {
        if ($txt != '') {
            $txt = str_replace("&", "[2]", $txt);
            $txt = str_replace("á", "[4]", $txt);
            $txt = str_replace("é", "[5]", $txt);
            $txt = str_replace("í", "[6]", $txt);
            $txt = str_replace("ó", "[7]", $txt);
            $txt = str_replace("ú", "[8]", $txt);
            $txt = str_replace("ñ", "[9]", $txt);
            $txt = str_replace("Ñ", "[10]", $txt);
            $txt = str_replace("Á", "[13]", $txt);
            $txt = str_replace("É", "[14]", $txt);
            $txt = str_replace("Í", "[15]", $txt);
            $txt = str_replace("Ó", "[16]", $txt);
            $txt = str_replace("Ú", "[17]", $txt);
            $txt = str_replace("Ü", "[18]", $txt);
            $txt = str_replace("º", "[19]", $txt);
            $txt = str_replace("°", "[20]", $txt);
            //
            $txt = str_replace("ª", "[21]", $txt);
            // $txt = str_replace("!", "[22]", $txt);
            $txt = str_replace("¡", "[23]", $txt);
            // $txt = str_replace("'", "[24]", $txt);
            $txt = str_replace("´", "[25]", $txt);
            $txt = str_replace("`", "[26]", $txt);
            //
            $txt = str_replace("À", "[28]", $txt);
            $txt = str_replace("È", "[29]", $txt);
            $txt = str_replace("Ì", "[30]", $txt);
            $txt = str_replace("Ò", "[31]", $txt);
            $txt = str_replace("Ù", "[32]", $txt);
            //
            $txt = str_replace("à", "[33]", $txt);
            $txt = str_replace("è", "[34]", $txt);
            $txt = str_replace("ì", "[35]", $txt);
            $txt = str_replace("ò", "[36]", $txt);
            $txt = str_replace("ù", "[37]", $txt);
        }
        //
        return $txt;
    }

    /**
     * Funci&oacute;n que recibe un texto y sustituye los tags por caracteres
     * Utilizado para recibir la informaci&oacute;n al SIREP
     *
     * @param 	string		$txt		Texto a convertir
     * @return 	string					RTexto convertido
     */
    public static function restaurarEspeciales($txt)
    {
        if ($txt != '') {
            $txt = str_replace("[0]", "\"", $txt);
            $txt = str_replace("[1]", "'", $txt);
            $txt = str_replace("[2]", "&", $txt);
            $txt = str_replace("[3]", "?", $txt);
            $txt = str_replace("[4]", "á", $txt);
            $txt = str_replace("[5]", "é", $txt);
            $txt = str_replace("[6]", "í", $txt);
            $txt = str_replace("[7]", "ó", $txt);
            $txt = str_replace("[8]", "ú", $txt);
            $txt = str_replace("[9]", "ñ", $txt);
            $txt = str_replace("[10]", "Ñ", $txt);
            $txt = str_replace("[11]", "+", $txt);
            $txt = str_replace("[12]", "#", $txt);
            $txt = str_replace("[13]", "Á", $txt);
            $txt = str_replace("[14]", "É", $txt);
            $txt = str_replace("[15]", "Í", $txt);
            $txt = str_replace("[16]", "Ó", $txt);
            $txt = str_replace("[17]", "Ú", $txt);
            $txt = str_replace("[18]", "Ü", $txt);
            $txt = str_replace("[19]", "º", $txt);
            $txt = str_replace("[20]", "°", $txt);
            $txt = str_replace("[21]", "ª", $txt);
            //
            $txt = str_replace("[22]", "!", $txt);
            $txt = str_replace("[23]", "¡", $txt);
            $txt = str_replace("[24]", "'", $txt);
            $txt = str_replace("[25]", "´", $txt);
            $txt = str_replace("[26]", "`", $txt);
            //
            $txt = str_replace("[28]", "À", $txt);
            $txt = str_replace("[29]", "È", $txt);
            $txt = str_replace("[30]", "Ì", $txt);
            $txt = str_replace("[31]", "Ò", $txt);
            $txt = str_replace("[32]", "Ù", $txt);
            //
            $txt = str_replace("[33]", "à", $txt);
            $txt = str_replace("[34]", "è", $txt);
            $txt = str_replace("[35]", "ì", $txt);
            $txt = str_replace("[36]", "ò", $txt);
            $txt = str_replace("[37]", "ù", $txt);

            $txt = str_replace("[39]", "Ñ", $txt);
        }
        //
        return $txt;
    }

    public static function restaurarEspecialesMayusculas($txt)
    {
        if ($txt != '') {
            $txt = str_replace("[0]", "\"", $txt);
            $txt = str_replace("[1]", "'", $txt);
            $txt = str_replace("[2]", "&", $txt);
            $txt = str_replace("[3]", "?", $txt);
            $txt = str_replace("[4]", "Á", $txt);
            $txt = str_replace("[5]", "É", $txt);
            $txt = str_replace("[6]", "Í", $txt);
            $txt = str_replace("[7]", "Ó", $txt);
            $txt = str_replace("[8]", "Ú", $txt);
            $txt = str_replace("[9]", "Ñ", $txt);
            $txt = str_replace("[10]", "Ñ", $txt);
            $txt = str_replace("[11]", "+", $txt);
            $txt = str_replace("[12]", "#", $txt);
            $txt = str_replace("[13]", "Á", $txt);
            $txt = str_replace("[14]", "É", $txt);
            $txt = str_replace("[15]", "Í", $txt);
            $txt = str_replace("[16]", "Ó", $txt);
            $txt = str_replace("[17]", "Ú", $txt);
            $txt = str_replace("[18]", "Ü", $txt);
            $txt = str_replace("[19]", "º", $txt);
            $txt = str_replace("[20]", "°", $txt);
            $txt = str_replace("[21]", "ª", $txt);
            //
            $txt = str_replace("[22]", "!", $txt);
            $txt = str_replace("[23]", "¡", $txt);
            $txt = str_replace("[24]", "'", $txt);
            $txt = str_replace("[25]", "´", $txt);
            $txt = str_replace("[26]", "`", $txt);
            //
            $txt = str_replace("[28]", "À", $txt);
            $txt = str_replace("[29]", "È", $txt);
            $txt = str_replace("[30]", "Ì", $txt);
            $txt = str_replace("[31]", "Ò", $txt);
            $txt = str_replace("[32]", "Ù", $txt);
            //
            $txt = str_replace("[33]", "à", $txt);
            $txt = str_replace("[34]", "è", $txt);
            $txt = str_replace("[35]", "ì", $txt);
            $txt = str_replace("[36]", "ò", $txt);
            $txt = str_replace("[37]", "ù", $txt);

            $txt = str_replace("[39]", "Ñ", $txt);
        }
        //
        return $txt;
    }

    public static function restaurarEspecialesSinTildes($txt)
    {
        if ($txt != '') {
            $txt = str_replace("[0]", "\"", $txt);
            $txt = str_replace("[1]", "'", $txt);
            $txt = str_replace("[2]", "&", $txt);
            $txt = str_replace("[3]", "?", $txt);
            $txt = str_replace("[4]", "a", $txt);
            $txt = str_replace("[5]", "e", $txt);
            $txt = str_replace("[6]", "i", $txt);
            $txt = str_replace("[7]", "o", $txt);
            $txt = str_replace("[8]", "u", $txt);
            $txt = str_replace("[9]", "&ntilde;", $txt);
            $txt = str_replace("[10]", "&Ntilde;", $txt);
            $txt = str_replace("[11]", "+", $txt);
            $txt = str_replace("[12]", "#", $txt);
            $txt = str_replace("[13]", "A", $txt);
            $txt = str_replace("[14]", "E", $txt);
            $txt = str_replace("[15]", "I", $txt);
            $txt = str_replace("[16]", "O", $txt);
            $txt = str_replace("[17]", "U", $txt);
            $txt = str_replace("[18]", "Ü", $txt);
            $txt = str_replace("[19]", "º", $txt);
            $txt = str_replace("[20]", "°", $txt);
            //
            $txt = str_replace("[21]", "ª", $txt);
            $txt = str_replace("[22]", "!", $txt);
            $txt = str_replace("[23]", "¡", $txt);
            $txt = str_replace("[24]", "'", $txt);
            $txt = str_replace("[25]", "´", $txt);
            $txt = str_replace("[26]", "`", $txt);
            //
            $txt = str_replace("[28]", "A", $txt);
            $txt = str_replace("[29]", "E", $txt);
            $txt = str_replace("[30]", "I", $txt);
            $txt = str_replace("[31]", "O", $txt);
            $txt = str_replace("[32]", "U", $txt);
            //
            $txt = str_replace("[33]", "a", $txt);
            $txt = str_replace("[34]", "e", $txt);
            $txt = str_replace("[35]", "i", $txt);
            $txt = str_replace("[36]", "o", $txt);
            $txt = str_replace("[37]", "u", $txt);
        }
        //
        return $txt;
    }

    /**
     * Funci&oacute;n que recibe un texto y sustituye los tags por caracteres
     * Utilizado para recibir la informaci&oacute;n al SIREP
     *
     * @param 	string		$txt		Texto a convertir
     * @return 	string					RTexto convertido
     */
    public static function restaurarEspecialesRazonSocial($txt)
    {
        if ($txt != '') {
            $txt = str_replace("[0]", "", $txt);
            $txt = str_replace("[1]", "", $txt);
            $txt = str_replace("[2]", "&", $txt);
            $txt = str_replace("[3]", "?", $txt);
            $txt = str_replace("[4]", "á", $txt);
            $txt = str_replace("[5]", "é", $txt);
            $txt = str_replace("[6]", "í", $txt);
            $txt = str_replace("[7]", "ó", $txt);
            $txt = str_replace("[8]", "ú", $txt);
            $txt = str_replace("[9]", "ñ", $txt);
            $txt = str_replace("[10]", "Ñ", $txt);
            $txt = str_replace("[11]", "+", $txt);
            $txt = str_replace("[12]", "#", $txt);
            $txt = str_replace("[13]", "Á", $txt);
            $txt = str_replace("[14]", "É", $txt);
            $txt = str_replace("[15]", "Í", $txt);
            $txt = str_replace("[16]", "Ó", $txt);
            $txt = str_replace("[17]", "Ú", $txt);
            $txt = str_replace("[18]", "Ü", $txt);
            $txt = str_replace("[19]", "º", $txt);
            $txt = str_replace("[20]", "°", $txt);
            //
            $txt = str_replace("[21]", "ª", $txt);
            $txt = str_replace("[22]", "!", $txt);
            $txt = str_replace("[23]", "¡", $txt);
            $txt = str_replace("[24]", "'", $txt);
            $txt = str_replace("[25]", "´", $txt);
            $txt = str_replace("[26]", "`", $txt);
            //
            $txt = str_replace("[28]", "À", $txt);
            $txt = str_replace("[29]", "È", $txt);
            $txt = str_replace("[30]", "Ì", $txt);
            $txt = str_replace("[31]", "Ò", $txt);
            $txt = str_replace("[32]", "Ù", $txt);
            //
            $txt = str_replace("[33]", "à", $txt);
            $txt = str_replace("[34]", "è", $txt);
            $txt = str_replace("[35]", "ì", $txt);
            $txt = str_replace("[36]", "ò", $txt);
            $txt = str_replace("[37]", "ù", $txt);
        }
        //
        return $txt;
    }

    /**
     * Funci&oacute;n que recibe un texto y reemplaza caracteres especiales por tags
     * Utilizado para enviar la informaci&oacute;n al SIREP
     *
     * @param 	string		$txt	Texto a convertir
     * @return 	string				Texto convertido
     */
    public static function reemplazarHtmlPdf($txt)
    {
        if ($txt != '') {
            $txt = strip_tags($txt, "<p><ul><il>");
            $txt = str_replace("&nbsp;", " ", $txt);
            $txt = str_replace("&aacute;", "á", $txt);
            $txt = str_replace("&eacute;", "é", $txt);
            $txt = str_replace("&iacute;", "í", $txt);
            $txt = str_replace("&oacute;", "ó", $txt);
            $txt = str_replace("&uacute;", "ú", $txt);
            $txt = str_replace("&ntilde;", "ñ", $txt);
            $txt = str_replace("&Aacute;", "Á", $txt);
            $txt = str_replace("&Eacute;", "É", $txt);
            $txt = str_replace("&Iacute;", "Í", $txt);
            $txt = str_replace("&Oacute;", "Ó", $txt);
            $txt = str_replace("&Uacute;", "Ú", $txt);
            $txt = str_replace("&Ntilde;", "Ñ", $txt);
            $txt = str_replace("<p>", "", $txt);
            $txt = str_replace("</p>", chr(13) . chr(10), $txt);
            $txt = str_replace("<ul>", "*", $txt);
            $txt = str_replace("<il>", "*", $txt);
        }
        return $txt;
    }

    public static function reemplazarHtml($txt)
    {
        if ($txt != '') {
            $txt = str_replace("á", "&aacute;", $txt);
            $txt = str_replace("é", "&eacute;", $txt);
            $txt = str_replace("í", "&iacute;", $txt);
            $txt = str_replace("ó", "&oacute;", $txt);
            $txt = str_replace("ú", "&uacute;", $txt);
            $txt = str_replace("ñ", "&ntilde;", $txt);
            $txt = str_replace("Á", "&Aacute;", $txt);
            $txt = str_replace("É", "&Eacute;", $txt);
            $txt = str_replace("Í", "&Iacute;", $txt);
            $txt = str_replace("Ó", "&Oacute;", $txt);
            $txt = str_replace("Ú", "&Uacute;", $txt);
            $txt = str_replace("Ñ", "&Ntilde;", $txt);
        }
        return $txt;
    }

    public static function restoval($valor)
    {
        $valor = str_replace(",", "", $valor);
        if (trim((string) $valor) == '')
            $valor = 0;
        if (!is_numeric($valor))
            $valor = 0;
        $rval = number_format($valor, 4, ".", "");
        $aval = explode(".", $rval);
        return sprintf("%4s", $aval[1]);
        exit();
    }

    public static function quitarCaracteresDireccion($txt)
    {

        $patronesLetraNum[0] = '[^a-zA-Z0-9 ]';
        $reemplazos1[0] = '';
        $txt_tmp = trim(preg_replace($patronesLetraNum, $reemplazos1, $txt));

        $patronesSimbolos[0] = '[^.-#ºª?°]';
        $reemplazos2[0] = '';
        return trim(preg_replace($patronesSimbolos, $reemplazos2, $txt_tmp));
    }

    public static function quitarTildes($txt)
    {
        $txt = str_replace("á", "a", $txt);
        $txt = str_replace("é", "e", $txt);
        $txt = str_replace("í", "i", $txt);
        $txt = str_replace("ó", "o", $txt);
        $txt = str_replace("ú", "u", $txt);
        $txt = str_replace("ñ", "n", $txt);
        $txt = str_replace("Á", "A", $txt);
        $txt = str_replace("É", "E", $txt);
        $txt = str_replace("Í", "I", $txt);
        $txt = str_replace("Ó", "O", $txt);
        $txt = str_replace("Ú", "U", $txt);
        $txt = str_replace("Ñ", "N", $txt);
        return $txt;
    }

    public static function redondear00($val)
    {
        $val = intval($val);
        $val15 = sprintf("%015s", $val);
        $val2 = substr($val15, 13, 2);
        if ($val2 == "00" || $val2 == "50") {
            return $val;
        } else {
            if ($val2 >= "50") {
                $val = $val - intval($val2) + 100;
            } else {
                $val = $val - intval($val2);
            }
            return $val;
        }
    }

    /**
     * 
     * @param type $val
     * @return type
     */
    public static function redondear100($val)
    {
        $val = intval($val);
        $val15 = sprintf("%015s", $val);
        $val2 = substr($val15, 13, 2);
        if ($val2 >= "50") {
            $val = intval(substr($val15, 0, 13)) * 100 + 100;
        } else {
            $val = intval(substr($val15, 0, 13)) * 100;
        }
        return $val;
    }

    /**
     * 
     * @param type $val
     * @return type
     */
    public static function redondear1000($val)
    {
        $val = intval($val);
        $val15 = sprintf("%015s", $val);
        $val2 = substr($val15, 12, 3);
        if ($val2 >= "500") {
            $val = intval(substr($val15, 0, 12)) * 1000 + 1000;
        } else {
            $val = intval(substr($val15, 0, 12)) * 1000;
        }
        return $val;
    }

    public static function validarFecha($dsfecha)
    {
        if (strlen($dsfecha) < 8) {
            return false;
        } else {
            if (substr($dsfecha, 0, 4) < "1800") {
                return false;
            } else {
                $ano = substr($dsfecha, 0, 4);
                if ((substr($dsfecha, 4, 2) < "01") || (substr($dsfecha, 4, 2) > "12")) {
                    return false;
                } else {
                    $mal = "0";
                    if ((substr($dsfecha, 4, 2) == "01") ||
                        (substr($dsfecha, 4, 2) == "03") ||
                        (substr($dsfecha, 4, 2) == "05") ||
                        (substr($dsfecha, 4, 2) == "07") ||
                        (substr($dsfecha, 4, 2) == "08") ||
                        (substr($dsfecha, 4, 2) == "10") ||
                        (substr($dsfecha, 4, 2) == "12")
                    ) {
                        if ((substr($dsfecha, 6, 2) < "01") || (substr($dsfecha, 6, 2) > "31")) {
                            $mal = "1";
                        }
                    }
                    if ((substr($dsfecha, 4, 2) == "04") ||
                        (substr($dsfecha, 4, 2) == "06") ||
                        (substr($dsfecha, 4, 2) == "09") ||
                        (substr($dsfecha, 4, 2) == "11")
                    ) {
                        if ((substr($dsfecha, 6, 2) < "01") || (substr($dsfecha, 6, 2) > "30")) {
                            $mal = "1";
                        }
                    }
                    if (substr($dsfecha, 4, 2) == "02") {
                        if (($ano % 4 == 0) && (($ano % 100 != 0) || ($ano % 400 == 0))) {
                            if ((substr($dsfecha, 6, 2) < "01") || (substr($dsfecha, 6, 2) > "29")) {
                                $mal = "1";
                            }
                        } else {
                            if ((substr($dsfecha, 6, 2) < "01") || (substr($dsfecha, 6, 2) > "28")) {
                                $mal = "1";
                            }
                        }
                    }
                    if ($mal == "1") {
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        }
    }

    public static function validarMovil($txt)
    {
        $txtNuevo = preg_replace("[^0-9]", "", $txt);
        if (trim($txt) != (trim($txtNuevo))) {
            return false;
        } else {
            if (strlen($txt) != 10) {
                return false;
            }
            if (str_replace("0", "", $txt) == '') {
                return false;
            }
            if (str_replace("2", "", $txt) == '') {
                return false;
            }
            if (str_replace("3", "", $txt) == '') {
                return false;
            }
            if (str_replace("4", "", $txt) == '') {
                return false;
            }
            if (str_replace("5", "", $txt) == '') {
                return false;
            }
            if (str_replace("6", "", $txt) == '') {
                return false;
            }
            if (str_replace("7", "", $txt) == '') {
                return false;
            }
            if (str_replace("8", "", $txt) == '') {
                return false;
            }
            if (str_replace("9", "", $txt) == '') {
                return false;
            }
        }
        return true;
    }

    public static function validarNit($nit)
    {
        $sepIde = \funcionesGenerales::separarDv($nit);
        $dv = \funcionesGenerales::calcularDv($sepIde["identificacion"]);
        if ($dv == $sepIde["dv"]) {
            return true;
        } else {
            return false;
        }
    }

    public static function validarNumerico($txt)
    {
        $txtNuevo = preg_replace("[^0-9]", "", $txt);
        if (trim($txt) != (trim($txtNuevo))) {
            return false;
        } else {
            return true;
        }
        exit();
    }

    public static function validarPdf($filee, $textobuscar = '')
    {
        // return true;
        require_once($_SESSION["generales"]["pathabsoluto"] . '/components/pdf-to-text/PdfToText.phpclass');
        if (substr($filee, 0, 4) == 'http') {
            $content = file_get_contents($filee);
            $file = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . rand(1000000, 5000000) . '.pdf';
            $f = fopen($file, "w");
            fwrite($f, $content);
            fclose($f);
        } else {
            $file = $filee;
        }

        $pdf = new PdfToText($file);
        $texto = $pdf->Text;
        unset($pdf);
        if (strpos($textobuscar, $texto)) {
            return true;
        } else {
            return false;
        }
    }

    public static function validarPermisoEjecucion($mysqli, $usua, $opcion)
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');

        // Arma la tabla de permisos
        if (!isset($_SESSION["generales"]["usuariospermisos"]) || empty($_SESSION["generales"]["usuariospermisos"])) {
            $arrTem = retornarRegistrosMysqliApi($mysqli, 'usuariospermisos', "1=1", "idusuario,idopcion");
            $_SESSION["generales"]["usuariospermisos"] = array();
            foreach ($arrTem as $t) {
                $_SESSION["generales"]["usuariospermisos"][$t["idusuario"]][$t["idopcion"]] = $t;
            }
        }

        //
        if (!isset($_SESSION["generales"]["usuariospermisos"][$usua][$opcion])) {
            return false;
        } else {
            return true;
        }
    }

    public static function validarPermisoEspecial($mysqli, $usua, $opcion)
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        $can = contarRegistrosMysqliApi($mysqli, 'usuariospermisosespeciales', "idusuario='" . $usua . "' and idpermiso='" . $opcion . "'");
        if ($can == 0) {
            $can = retornarRegistroMysqliApi($mysqli, 'bas_permisosespeciales', "idpermiso='" . (string) $opcion . "'");
            if ($can === false || empty($can)) {
                return false;
            } else {
                if ($can["idcontrolusuario"] == 'T') {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return true;
        }
    }

    public static function validarPermitidosDireccion($txt)
    {
        $permitidos = "&aacute;&eacute;&iacute;&oacute;&uacute;abcdefghijklmn&ntilde;opqrstuvwxyz&aacute;&eacute;&iacute;&oacute;&uacute;&Ntilde;ABCDEFGHIJKLMNÑñOPQRSTUVWXYZ0123456789-#. ";
        for ($i = 0; $i < strlen($txt); $i++) {
            if (strpos($permitidos, substr($txt, $i, 1)) === false) {
                return false;
            }
        }
        return true;
    }

    public static function validarTelefono($txt)
    {
        $txtNuevo = preg_replace("[^0-9]", "", $txt);
        if (trim($txt) != (trim($txtNuevo))) {
            return false;
        } else {
            if ((strlen($txt) != 7) && (strlen($txt) != 10)) {
                return false;
            }
            if (str_replace("0", "", $txt) == '') {
                return false;
            }
            if (str_replace("2", "", $txt) == '') {
                return false;
            }
            if (str_replace("3", "", $txt) == '') {
                return false;
            }
            if (str_replace("4", "", $txt) == '') {
                return false;
            }
            if (str_replace("5", "", $txt) == '') {
                return false;
            }
            if (str_replace("6", "", $txt) == '') {
                return false;
            }
            if (str_replace("7", "", $txt) == '') {
                return false;
            }
            if (str_replace("8", "", $txt) == '') {
                return false;
            }
            if (str_replace("9", "", $txt) == '') {
                return false;
            }
        }
        return true;
    }

    public static function validacionFormularioGrabado($dbx, $mat)
    {
        $temValid = retornarRegistroMysqliApi($dbx, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["tramite"]["numeroliquidacion"] . " and expediente='" . $mat . "'");
        if ($temValid === false || empty($temValid)) {
            return false;
        } else {
            $datos = \funcionesGenerales::desserializarExpedienteMatricula($dbx, $temValid["xml"]);
            if ($datos["organizacion"] == '02' || $datos["categoria"] == '2' || $datos["categoria"] == '3') {
                return true;
            } else {
                if (floatval($datos["acttot"]) != floatval($datos["paspat"])) {
                    return false;
                } else {
                    return true;
                }
            }
        }
    }

    public static function convertirDesdeHtml($txt)
    {
        $txt = str_replace("&aacute;", "á", $txt);
        $txt = str_replace("&eacute;", "é", $txt);
        $txt = str_replace("&iacute;", "í", $txt);
        $txt = str_replace("&oacute;", "ó", $txt);
        $txt = str_replace("&uacute;", "ú", $txt);
        $txt = str_replace("&Aacute;", "Á", $txt);
        $txt = str_replace("&Eacute;", "É", $txt);
        $txt = str_replace("&Iacute;", "Í", $txt);
        $txt = str_replace("&Oacute;", "Ó", $txt);
        $txt = str_replace("&Uacute;", "Ú", $txt);
        $txt = str_replace("&ntilde;", "ñ", $txt);
        $txt = str_replace("&Ntilde;", "Ñ", $txt);
        return $txt;
    }

    public static function convertirStringNumero($valx)
    {
        $valx = trim($valx);
        $val = 0;
        $signo = '+';
        $valx = str_replace(",", "", $valx);
        $valx = ltrim(trim($valx), "0");
        if ($valx == '' || $valx == '0.00' || $valx == '.00') {
            $val = 0;
        } else {
            if (substr($valx, 0, 1) == '-') {
                $signo = '-';
                $valx = str_replace("-", "", $valx);
            }
            $a = explode(".", $valx);
            $val = doubleval($a[0]);
            if (isset($a[1])) {
                $len = strlen($a[1]);
                switch ($len) {
                    case 1:
                        $val = $val + intval($a[1]) / 10;
                        break;
                    case 2:
                        $val = $val + intval($a[1]) / 100;
                        break;
                    case 3:
                        $val = $val + intval($a[1]) / 1000;
                        break;
                    case 4:
                        $val = $val + intval($a[1]) / 10000;
                        break;
                    case 5:
                        $val = $val + intval($a[1]) / 100000;
                        break;
                }
            }
        }
        if ($signo == '-') {
            $val = $val * -1;
        }
        return $val;
    }

    public static function consumirWsConsultarRuta($dbx, $codbarras)
    {

        if (trim($codbarras) == '') {
            $_SESSION["generales"]["mensajerror"] = 'No es posible realizar la consulta';
            return false;
        }

        $arrTem = retornarRegistroMysqliApi($dbx, 'mreg_est_codigosbarras', "codigobarras='" . $codbarras . "'");
        $arrTemRec = retornarRegistroMysqliApi($dbx, 'mreg_recibosgenerados', "codigobarras='" . $codbarras . "'");
        $arrTemRad = retornarRegistroMysqliApi($dbx, 'mreg_rue_radicacion', "codigobarras='" . $codbarras . "'");
        $arrTemliq = retornarRegistroMysqliApi($dbx, 'mreg_liquidacion', "numeroradicacion='" . $codbarras . "'");
        //
        $emails = array();
        $tels = array();

        $emailRadicado = '';
        $celularRadicado = '';
        if (($arrTem["actoreparto"] == '07') || ($arrTem["actoreparto"] == '29')) {
            $emailRadicado = $arrTemRec["email"];
            $celularRadicado = $arrTemRec["telefono2"];
        }

        //
        if ($arrTemRec["email"] != '') {
            $emails[$arrTemRec["email"]] = $arrTemRec["email"];
        }
        if ($arrTemRec["telefono1"] != '' && strlen($arrTemRec["telefono1"]) == 10 && substr($arrTemRec["telefono1"], 0, 1) == '3') {
            $tels[$arrTemRec["telefono1"]] = $arrTemRec["telefono1"];
        }
        if ($arrTemRec["telefono2"] != '' && strlen($arrTemRec["telefono2"]) == 10 && substr($arrTemRec["telefono2"], 0, 1) == '3') {
            if (!in_array($arrTemRec["telefono2"], $tels)) {
                $tels[$arrTemRec["telefono2"]] = $arrTemRec["telefono2"];
            }
        }

        if (ltrim(trim($arrTem["matricula"]), "0") != '') {
            $arrTemMat = retornarRegistroMysqliApi($dbx, 'mreg_est_inscritos', "matricula='" . ltrim(trim($arrTem["matricula"]), "0") . "'");
            if ($arrTemMat && !empty($arrTemMat)) {
                if (trim($arrTemMat["emailcom"]) != '') {
                    $emails[$arrTemMat["emailcom"]] = $arrTemMat["emailcom"];
                }
                if (trim($arrTemMat["emailcom2"]) != '') {
                    $emails[$arrTemMat["emailcom2"]] = $arrTemMat["emailcom2"];
                }
                if (trim($arrTemMat["emailcom3"]) != '') {
                    $emails[$arrTemMat["emailcom3"]] = $arrTemMat["emailcom3"];
                }
                if (trim($arrTemMat["emailnot"]) != '') {
                    $emails[$arrTemMat["emailnot"]] = $arrTemMat["emailnot"];
                }
                if (trim($arrTemMat["telcom1"]) != '' && strlen($arrTemMat["telcom1"]) == 10 && substr($arrTemMat["telcom1"], 0, 1) == '3') {
                    $tels[$arrTemMat["telcom1"]] = $arrTemMat["telcom1"];
                }
                if (trim($arrTemMat["telcom2"]) != '' && strlen($arrTemMat["telcom2"]) == 10 && substr($arrTemMat["telcom2"], 0, 1) == '3') {
                    $tels[$arrTemMat["telcom2"]] = $arrTemMat["telcom2"];
                }
                if (trim($arrTemMat["telcom3"]) != '' && strlen($arrTemMat["telcom3"]) == 10 && substr($arrTemMat["telcom3"], 0, 1) == '3') {
                    $tels[$arrTemMat["telcom3"]] = $arrTemMat["telcom3"];
                }
                if (trim($arrTemMat["telnot"]) != '' && strlen($arrTemMat["telnot"]) == 10 && substr($arrTemMat["telnot"], 0, 1) == '3') {
                    $tels[$arrTemMat["telnot"]] = $arrTemMat["telnot"];
                }
                if (trim($arrTemMat["telnot2"]) != '' && strlen($arrTemMat["telnot2"]) == 10 && substr($arrTemMat["telnot2"], 0, 1) == '3') {
                    $tels[$arrTemMat["telnot2"]] = $arrTemMat["telnot2"];
                }
                if (trim($arrTemMat["telnot3"]) != '' && strlen($arrTemMat["telnot3"]) == 10 && substr($arrTemMat["telnot3"], 0, 1) == '3') {
                    $tels[$arrTemMat["telnot3"]] = $arrTemMat["telnot3"];
                }
            }

            $arrTemCC = retornarRegistrosMysqliApi($dbx, 'mreg_est_campostablas', "tabla='200' and registro='" . ltrim(trim($arrTem["matricula"]), "0") . "'", "id");
            foreach ($arrTemCC as $cc) {
                if ($cc["campo"] == "EMAILNOT-ANTERIOR" && trim($cc["contenido"]) != '') {
                    $emails[$cc["contenido"]] = $cc["contenido"];
                    $emailnotant = trim($cc["contenido"]);
                }
                if ($cc["campo"] == "EMAILCOM-ANTERIOR" && trim($cc["contenido"]) != '') {
                    $emails[$cc["contenido"]] = $cc["contenido"];
                    $emailcomant = trim($cc["contenido"]);
                }
            }

            // Incluir la búsqueda sobre mreg_campos_historicos_AAAA
        }

        //
        $retorno = array();
        $retorno["codbarras"] = $codbarras;
        $retorno["operacion"] = $arrTem["operacion"];
        $retorno["matricula"] = $arrTem["matricula"];
        $retorno["proponente"] = $arrTem["proponente"];
        $retorno["idclase"] = $arrTem["idclase"];
        $retorno["numid"] = $arrTem["numid"];
        $retorno["nombre"] = $arrTem["nombre"];
        $retorno["organizacion"] = '';
        $retorno["tipotramite"] = $arrTemliq["tipotramite"];
        $retorno["idliquidacion"] = $arrTemliq["idliquidacion"];
        $retorno["fecha"] = $arrTem["fecharadicacion"];
        $retorno["tipodoc"] = $arrTem["tipdoc"];
        $retorno["numdoc"] = $arrTem["numdoc"];
        $retorno["fechadoc"] = $arrTem["fecdoc"];
        $retorno["mundoc"] = $arrTem["mundoc"];
        $retorno["txtorigendoc"] = $arrTem["oridoc"];
        $retorno["tramite"] = $arrTem["actoreparto"];
        $retorno["estado"] = $arrTem["estadofinal"];
        $retorno["festado"] = $arrTem["fechaestadofinal"];
        $retorno["usuario"] = $arrTem["operadorfinal"];
        $retorno["recibo"] = $arrTem["recibo"];
        $retorno["erecibo"] = '0'; // Normal
        if ($arrTemRec["estado"] == '03') {
            $retorno["erecibo"] = '2'; // Reversado
        }
        if ($arrTemRec["estado"] == '99') {
            $retorno["erecibo"] = '1'; // Anulado
        }
        $retorno["fecopera"] = '';
        $retorno["horpago"] = '';
        if ($arrTemRec && !empty($arrTemRec)) {
            $retorno["fecopera"] = $arrTemRec["fecha"];
            $retorno["horpago"] = $arrTemRec["hora"];
        }
        $retorno["escaneocompleto"] = $arrTem["escaneocompleto"];
        $retorno["nin"] = '';
        $retorno["nuc"] = '';
        if ($arrTemRad && !empty($arrTemRad)) {
            $retorno["nin"] = $arrTemRad["numerointernorue"];
            $retorno["nuc"] = $arrTemRad["numerounicoconsulta"];
        }
        $retorno["emails"] = $emails;
        if ($emailRadicado != '') {
            $retorno["emailradicado"] = $emailRadicado;
            $retorno["celradicado"] = $celularRadicado;
        }

        $retorno["telefonos"] = $tels;
        $retorno["detalle"] = $arrTem["detalle"];
        $retorno["emailnot1"] = $arrTem["emailnot1"];
        $retorno["emailnot2"] = $arrTem["emailnot2"];
        $retorno["emailnot3"] = $arrTem["emailnot3"];
        $retorno["celnot1"] = $arrTem["celnot1"];
        $retorno["celnot2"] = $arrTem["celnot2"];
        $retorno["celnot3"] = $arrTem["celnot3"];

        //
        $i = 0;
        $retorno["pasosruta"] = array();
        $arrTemEst = retornarRegistrosMysqliApi($dbx, 'mreg_est_codigosbarras_documentos', "codigobarras='" . $codbarras . "'", "fecha,hora");
        foreach ($arrTemEst as $reg) {
            $i++;
            $retorno["pasosruta"][$i]["fecha"] = $reg["fecha"];
            $retorno["pasosruta"][$i]["hora"] = $reg["hora"];
            $retorno["pasosruta"][$i]["codigoruta"] = $reg["estado"];
            if ($arrTem["actoreparto"] == '09' || $arrTem["actoreparto"] == '53') {
                $retorno["pasosruta"][$i]["estado"] = retornarRegistroMysqliApi($dbx, 'mreg_codestados_rutaproponentes', "id='" . $reg["estado"] . "'", "descripcion");
            } else {
                $retorno["pasosruta"][$i]["estado"] = retornarRegistroMysqliApi($dbx, 'mreg_codestados_rutamercantil', "id='" . $reg["estado"] . "'", "descripcion");
            }
            if (trim($reg["sucursal"]) == '') {
                $retorno["pasosruta"][$i]["idusuario"] = $reg["operador"];
            } else {
                $retorno["pasosruta"][$i]["idusuario"] = $reg["sucursal"] . '-' . $reg["operador"];
            }

            $retorno["pasosruta"][$i]["usuario"] = retornarRegistroMysqliApi($dbx, 'usuarios', "idusuario='" . $reg["operador"] . "'", "nombreusuario");
            if (trim($retorno["pasosruta"][$i]["usuario"]) == '') {
                $retorno["pasosruta"][$i]["usuario"] = retornarRegistroMysqliApi($dbx, 'usuarios', "idcodigosirepcaja='" . $reg["operador"] . "' or idcodigosirepdigitacion='" . $reg["operador"] . "' or idcodigosirepregistro='" . $reg["operador"] . "'", "nombreusuario");
            }

            if ($retorno["pasosruta"][$i]["idusuario"] == 'BAT') {
                $retorno["pasosruta"][$i]["usuario"] = 'PROCESOS AUTOMATICOS';
            }
        }

        //
        $i = 0;
        $retorno["codbarrasacto"] = array();
        $arrTemEst = retornarRegistrosMysqliApi($dbx, 'mreg_est_codigosbarras_libros', "codigobarras='" . $codbarras . "'", "id");
        foreach ($arrTemEst as $reg) {
            $i++;
            if ($retorno["tramite"] != '09' && $retorno["tramite"] != '33' && $retorno["tramite"] != '53') {
                $temx = retornarRegistroMysqliApi($dbx, 'mreg_est_inscripciones', "libro='" . $reg["libro"] . "' and registro='" . ltrim(trim($reg["registro"]), "0") . "'");
                $retorno["codbarrasacto"][$i]["libro"] = $reg["libro"];
                $retorno["codbarrasacto"][$i]["registro"] = ltrim(trim($reg["registro"]), "0");
                $retorno["codbarrasacto"][$i]["certif"] = retornarRegistroMysqliApi($dbx, 'mreg_actos', "idlibro='" . $reg["libro"] . "' and idacto='" . $reg["acto"] . "'", "nombre");
                $retorno["codbarrasacto"][$i]["fechareg"] = $temx["fecharegistro"];
                $retorno["codbarrasacto"][$i]["horareg"] = $temx["horaregistro"];
                $retorno["codbarrasacto"][$i]["acto"] = $reg["acto"];
                $retorno["codbarrasacto"][$i]["noticia"] = $temx["noticia"];
                if ($reg["libro"] == 'RM07' || $reg["libro"] == 'RM22' || $reg["libro"] == 'RE52') {
                    if ($reg["acto"] == '' || $reg["acto"] == '0003') {
                        if ($temx["descripcionlibro"] != '') {
                            $retorno["codbarrasacto"][$i]["noticia"] = $temx["descripcionlibro"] . ' - ' . $temx["numeropaginas"];
                        } else {
                            $retorno["codbarrasacto"][$i]["noticia"] = $temx["codigolibro"] . ' - ' . $temx["numeropaginas"];
                        }
                    }
                }
                $retorno["codbarrasacto"][$i]["expediente"] = $temx["matricula"];
                $retorno["codbarrasacto"][$i]["nombre"] = $temx["nombre"];
                $retorno["codbarrasacto"][$i]["email"] = ''; // Completar
                $retorno["codbarrasacto"][$i]["firma"] = $temx["firma"];
            } else {
                $temx = retornarRegistroMysqliApi($dbx, 'mreg_est_inscripciones_proponentes', "libro='" . $reg["libro"] . "' and registro='" . $reg["registro"] . "'");
                $retorno["codbarrasacto"][$i]["libro"] = $reg["libro"];
                $retorno["codbarrasacto"][$i]["registro"] = $reg["registro"];
                $retorno["codbarrasacto"][$i]["certif"] = retornarRegistroMysqliApi($dbx, 'mreg_actosproponente', "id='" . $reg["acto"] . "'", "descripcion");
                $retorno["codbarrasacto"][$i]["fechareg"] = $temx["fecharegistro"];
                $retorno["codbarrasacto"][$i]["horareg"] = $temx["horaregistro"];
                $retorno["codbarrasacto"][$i]["acto"] = $reg["acto"];
                $retorno["codbarrasacto"][$i]["noticia"] = $temx["texto"];
                $retorno["codbarrasacto"][$i]["expediente"] = $temx["proponente"];
                $retorno["codbarrasacto"][$i]["nombre"] = $temx["nombre"];
                $retorno["codbarrasacto"][$i]["email"] = ''; // Completar
                $retorno["codbarrasacto"][$i]["firma"] = $temx["firma"];
            }
        }

        //        
        $retorno["servicios"] = array();
        if (trim($arrTem["recibo"]) != '') {
            $arrTemSer = retornarRegistrosMysqliApi($dbx, 'mreg_recibosgenerados_detalle', "recibo='" . $arrTem["recibo"] . "'", "id");
            $i = 0;
            foreach ($arrTemSer as $reg) {
                $i++;
                $retorno["servicios"][$i]["ser"] = $reg["idservicio"];
                $retorno["servicios"][$i]["can"] = $reg["cantidad"];
                $retorno["servicios"][$i]["val"] = $reg["valorservicio"];
                $retorno["servicios"][$i]["mat"] = $reg["matricula"];
                $retorno["servicios"][$i]["pro"] = $reg["proponente"];
                $retorno["servicios"][$i]["ano"] = $reg["ano"];
                $retorno["servicios"][$i]["act"] = $reg["valorbase"];
            }
        }

        return $retorno;
    }

    /**
     * 
     * @param type $txt
     * @return string
     */
    public static function consumirWsVRVALREN($txt)
    {
        require_once('../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');

        // Consume el ws del SIREP
        if ($txt != '') {
            $lista = explode(',', $txt);
            $canceladas = 0;
            $aldia = 0;
            $in = '';
            $inx = '';
            foreach ($lista as $l) {
                $lx = explode('-', $l);
                if (isset($lx[1])) {
                    $ly = ltrim($lx[1], "0");
                } else {
                    $ly = ltrim($l, "0");
                }
                if ($in != '') {
                    $in .= ',';
                }
                $in .= "'" . $ly . "'";
                $inx = $ly . ' ';
            }
            $arrTem = retornarRegistrosMysqliApi(null, 'mreg_est_inscritos', "matricula IN (" . $in . ")", "matricula");
            foreach ($arrTem as $t) {
                if ($t["ctrestmatricula"] != 'MA' && $t["ctrestmatricula"] != 'IA') {
                    $canceladas++;
                }
                if ($t["ultanoren"] == date("Y")) {
                    $aldia++;
                }
            }
            unset($arrTem);

            if ($canceladas == 0 && $aldia == 0) {
                $resultado["codigoError"] = '0000';
                $resultado["msgError"] = '';
            }
            if ($canceladas > 0) {
                $resultado["codigoError"] = '0002';
                $resultado["msgError"] = 'Alguna(s) de la(s) matr&iacute;cula(s) a renovar fue(ron) cancelada(s) en fecha posterior a la elaboraci&oacute;n de la liquidaci&oacute;n (' . $txt . ')';
            }
            if ($aldia > 0) {
                $resultado["codigoError"] = '0003';
                $resultado["msgError"] = 'Alguna(s) de la(s) matr&iacute;cula(s) a renovar fue(ron) renovadas en fecha posterior a la elaboraci&oacute;n de la liquidaci&oacute;n (' . $txt . ')';
            }
        } else {
            $resultado["codigoError"] = '0000';
            $resultado["msgError"] = '';
        }

        return $resultado;
    }

    public static function validarAccesoTagsBandejaEntrada($mysqli = null)
    {

        //
        $cerrarMysqli = 'no';
        if ($mysqli == null) {
            $mysqli = conexionMysqliApi();
            $cerrarMysqli = 'si';
        }

        if (trim($_SESSION["generales"]["codigousuario"]) != '') {
            $arrUsuario = \funcionesGenerales::retornarUsuarioBase($mysqli, $_SESSION["generales"]["codigousuario"]);
        } else {
            $arrUsuario = false;
        }

        //
        if ($_SESSION["generales"]["validado"] == 'NO') {
            if ($cerrarMysqli == 'si') {
                $mysqli->close();
            }
            return ("No hay un usuario logueado (session activa) en el sistema de informaci&oacute;n");
        }

        //
        if ($_SESSION["generales"]["validado"] == 'SI') {
            if (!$arrUsuario) {
                if ($cerrarMysqli == 'si') {
                    $mysqli->close();
                }
                return ("No se encontr&oacute; el c&oacute;digo del usuario que est&aacute; logueado o no tiene sesi&oacute;n activa");
            }
        }

        //
        if ($_SESSION["generales"]["tipousuario"] != '00') {
            if ((trim((string) $arrUsuario["fechaactivacion"]) == '') || (trim((string) $arrUsuario["fechaactivacion"]) == '00000000')) {
                $tx = "ADVERTENCIA!!! <br><br>Esta opci&oacute;n no puede ser ejecutada puesto que el usuario que est&aacute; logueado a&uacute;n no ha sido activado. " .
                    "Comunique este hecho al tel&eacute;fono " . TELEFONO_ATENCION_USUARIOS . "  o al correo electr&oacute;nico " .
                    EMAIL_ATENCION_USUARIOS . ", para que su suscripci&oacute;n sea activada. " .
                    "Por favor disculpe los inconvenientes que esta situaci&oacute;n le pueda presentar.<br><br>";
                if (substr(TIPO_EMPRESA, 0, 3) == 'cam') {
                    $tx .= "En cumplimiento de la Ley de Habeas Data y con el objeto de salvaguardar en forma responsable la " .
                        "informaci&oacute;n que los comerciantes han depositado en los Registros que administra la C&aacute;mara de Comercio, " .
                        "es indispensable que conozcamos y registremos quien accede a la informaci&oacute;n. Es por este motivo que es " .
                        "tan importante que su suscripci&oacute;n est&eacute; vigente.<br><br>Cordialmente<br><br>DEPARTAMENTO LEGAL<br>CAMARA DE COMERCIO";
                }
                if ($cerrarMysqli == 'si') {
                    $mysqli->close();
                }

                return ($tx);
            }
        }

        //
        if ((trim((string) $arrUsuario["fechainactivacion"]) != '') && (trim((string) $arrUsuario["fechainactivacion"]) != '00000000')) {
            if ($cerrarMysqli == 'si') {
                $mysqli->close();
            }
            return ("Esta opci&oacute;n no puede ser ejecutada pues el usuario que est&aacute; logueado en el sistema est&aacute; desactivado. En caso de no estar de acuerdo con esta situaci&oacute;n le solicitamos que se comunique con los n&uacute;meros telef&oacute;nicos que aparecen en la parte inferior de esta pantalla y comente el caso encontrado.");
        }

        //
        if ($arrUsuario["eliminado"] == 'SI') {
            if ($cerrarMysqli == 'si') {
                $mysqli->close();
            }
            return ("Esta opci&oacute;n no puede ser ejecutada pues el usuario que est&aacute; logueado en el sistema ha sido eliminado. En caso de no estar de acuerdo con esta situaci&oacute;n le solicitamos que se comunique con los n&uacute;meros telef&oacute;nicos que aparecen en la parte inferior de esta pantalla y comente el caso encontrado.");
        }

        //    
        if (($arrUsuario["idtipousuario"] == '00') || ($arrUsuario["idtipousuario"] == '06')) {
            $tx = "ADVERTENCIA!!!   Esta opci&oacute;n no puede ser ejecutada puesto que el usuario que est&aacute; logueado no tiene permisos para ejecutarla. ";
            if ($cerrarMysqli == 'si') {
                $mysqli->close();
            }
            return ($tx);
        }
        if ($cerrarMysqli == 'si') {
            $mysqli->close();
        }
        return "true";
    }

    public static function validarAcceso($script, $existenciaopcion = 'S', $validado = 'S')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        //
        if (!isset($_SESSION["generales"]["codigousuario"]) || trim($_SESSION["generales"]["codigousuario"]) == '') {
            $_SESSION["generales"]["txtemergente"] = "(1) No se encontró usuario logueado, imposible continuar con el proceso solicitado.";
            return false;
        }

        //
        if (!isset($_SESSION["generales"]["tipousuario"]) || trim($_SESSION["generales"]["tipousuario"]) == '') {
            $_SESSION["generales"]["txtemergente"] = "(2) No se encontró el tipo de usuario, imposible continuar con el proceso solicitado.";
            return false;
        }

        // Si al llamar a la opción viene como par&aacute;metro el campo "codigoopcion" se crea la variable de session correspondiente    
        if (isset($_SESSION["vars"]["codigoopcion"])) {
            $_SESSION["generales"]["codigoopcion"] = $_SESSION["vars"]["codigoopcion"];
        }

        // Quita los directorios previos
        $cadena = explode(DIRECTORY_SEPARATOR, $script);
        $cantidad = count($cadena);
        $script1 = $cadena[$cantidad - 1];

        // Quita los par&aacute;metros
        $cadena = explode("?", $script1);
        $script1 = $cadena[0];

        //
        if ($_SESSION["generales"]["tipousuario"] == '01') {
            return true;
        }

        if ($existenciaopcion == 'S') {
            $arrOpciones = retornarRegistrosMysqliApi(null, $_SESSION["generales"]["basopciones"], "script='" . $script1 . "' and estado='1'", "idopcion");
            if ($arrOpciones === false || empty($arrOpciones)) {
                return true;
            } else {
                if ($arrOpciones[0]["tipousuariopublico"] == 'X') {
                    $exigeValidado = 'N';
                    $validado = 'N';
                    return true;
                } else {
                    $exigeValidado = 'S';
                    $validado = 'S';
                }
            }
        } else {
            $exigeValidado = 'N';
            $validado = 'N';
        }

        //
        if ($validado == 'S') {
            if ($existenciaopcion == 'S') {
                if ($_SESSION["generales"]["validado"] == 'NO') {
                    $_SESSION["generales"]["txtemergente"] = "No hay un usuario logueado (session activa) en el sistema";
                    return false;
                }
            } else {
                $validado = 'N';
            }
        }


        //
        if ($_SESSION["generales"]["tipousuario"] != '00') {
            if ((trim($_SESSION["generales"]["fechaactivacion"]) == '') || (trim($_SESSION["generales"]["fechaactivacion"]) == '00000000')) {
                $tx = "ADVERTENCIA (1) !!! <br><br>Esta opción no puede ser ejecutada puesto que el usuario que está logueado aún no ha sido activado. " .
                    "Comunique este hecho al teléfono " . TELEFONO_ATENCION_USUARIOS . "  o al correo electrónico " .
                    EMAIL_ATENCION_USUARIOS . ", para que su suscripción sea activada. " .
                    "Por favor disculpe los inconvenientes que esta situación le pueda presentar.<br><br>";
                $_SESSION["generales"]["txtemergente"] = $tx;
                return false;
            }
        }

        //
        if ($_SESSION["generales"]["codigousuario"] != 'USUPUBXX') {
            if ((trim($_SESSION["generales"]["fechainactivacion"]) != '') && (trim($_SESSION["generales"]["fechainactivacion"]) != '00000000')) {
                $_SESSION["generales"]["txtemergente"] = "Esta opción no puede ser ejecutada pues el usuario que está logueado en el sistema está desactivado. En caso de no estar de acuerdo con esta situación le solicitamos que se comunique con los números telefónicos que aparecen en la parte inferior de esta pantalla y comente el caso encontrado.";
                return false;
            }
        }


        //
        if ($validado == 'S') {
            if ($_SESSION["generales"]["codigousuario"] == 'JSP7') {
                $permisos = 'SI';
            } else {
                $permisos = 'NO';
                $codopcion = '';
                $opcionpublica = '';
                $_SESSION["generales"]["permisosopcion"] = array();
                foreach ($arrOpciones as $opcs) {
                    $arrperm = retornarRegistroMysqliApi(null, "usuariospermisos", "idusuario='" . $_SESSION["generales"]["codigousuario"] . "' and idopcion='" . $opcs["idopcion"] . "'");
                    if (!empty($arrperm)) {
                        $permisos = 'SI';
                        $codopcion = $opcs["idopcion"];
                        $opcionpublica = $opcs["tipousuariopublico"];
                        $_SESSION["generales"]["permisosopcion"] = $arrperm;
                    }
                    unset($arrperm);
                }
            }

            if ($permisos == 'NO') {
                $tx = "ADVERTENCIA (2) !!! Esta opcion (" . $script1 . ") no puede ser ejecutada puesto que el usuario que esta logueado (" . $_SESSION["generales"]["codigousuario"] . ")  no tiene permisos para ejecutarla. " .
                    "Si considera que esto es incorrecto, comunique esta situacion al telefono " . TELEFONO_ATENCION_USUARIOS . "  o al correo electronico " .
                    EMAIL_ATENCION_USUARIOS . ", para que la opcion correspondiente le sea activada. " .
                    "Por favor disculpe los inconvenientes que esta situacion le pueda presentar.";
                $_SESSION["generales"]["txtemergente"] = $tx;
                return false;
            } else {
                if (!isset($codopcion)) {
                    $codopcion = "";
                }
                $_SESSION["generales"]["idopcion"] = $codopcion;
            }
        }

        return true;
    }

    public static function validarAccesoPermisoEspecial($mysqli, $opc)
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');

        //
        if (!isset($_SESSION["generales"]["validado"]) || $_SESSION["generales"]["validado"] == '') {
            return base64_encode("No se encontró sesión activa, imposible continuar con el proceso");
        }

        //
        if (!isset($_SESSION["generales"]["codigousuario"]) || $_SESSION["generales"]["codigousuario"] == '') {
            return base64_encode("No se encontró sesión activa, imposible continuar con el proceso");
        }

        //
        if (!isset($_SESSION["generales"]["tipousuario"]) || $_SESSION["generales"]["tipousuario"] == '') {
            return base64_encode("No se encontró sesión activa, imposible continuar con el proceso");
        }

        //
        if ($_SESSION["generales"]["validado"] == 'NO') {
            return base64_encode("No hay un usuario logueado (session activa) en el sistema de información");
        }

        //
        if ($_SESSION["generales"]["tipousuario"] != '00') {
            if ((trim($_SESSION["generales"]["fechaactivacion"]) == '') || (trim($_SESSION["generales"]["fechaactivacion"]) == '00000000')) {
                $tx = "ADVERTENCIA!!! <br><br>Esta opción no puede ser ejecutada puesto que el usuario que está logueado aún no ha sido activado. " .
                    "Comunique este hecho al teléfono " . TELEFONO_ATENCION_USUARIOS . "  o al correo electrónico " .
                    EMAIL_ATENCION_USUARIOS . ", para que su suscripción sea activada. " .
                    "Por favor disculpe los inconvenientes que esta situación le pueda presentar.<br><br>";
                if (substr(TIPO_EMPRESA, 0, 3) == 'cam') {
                    $tx .= "En	 cumplimiento de la Ley de Habeas Data y con el objeto de salvaguardar en forma responsable la " .
                        "información que los comerciantes han depositado en los Registros que administra la Cámara de Comercio, " .
                        "es indispensable que conozcamos y registremos quien accede a la información. Es por este motivo que es " .
                        "tan importante que su suscripci&oacute;n est&eacute; vigente.<br><br>Cordialmente<br><br>DEPARTAMENTO LEGAL<br>CAMARA DE COMERCIO";
                }
                return base64_encode($tx);
            }
        }

        //
        if ((trim($_SESSION["generales"]["fechainactivacion"]) != '') && (trim($_SESSION["generales"]["fechainactivacion"]) != '00000000')) {
            return base64_encode("Esta opción no puede ser ejecutada pues el usuario que está logueado en el sistema está desactivado. En caso de no estar de acuerdo con esta situación le solicitamos que se comunique con los números telefónicos que aparecen en la parte inferior de esta pantalla y comente el caso encontrado.");
        }

        //
        if ($_SESSION["generales"]["eliminado"] == 'SI') {
            return base64_encode("Esta opción no puede ser ejecutada pues el usuario que está logueado en el sistema ha sido eliminado. En caso de no estar de acuerdo con esta situación le solicitamos que se comunique con los números telefónicos que aparecen en la parte inferior de esta pantalla y comente el caso encontrado.");
        }


        //
        if ($_SESSION["generales"]["tipousuario"] != '01') {
            $permisos = 'NO';
            if (is_array($opc)) {
                foreach ($opc as $o) {
                    if ($permisos != 'SI') {
                        $arrPerms = retornarRegistroMysqliApi($mysqli, $_SESSION["generales"]["baspermisosespeciales"], "idpermiso='" . $o . "'");
                        if (($arrPerms === false) || (empty($arrPerms))) {
                            $permisos = 'NOEXISTE';
                        } else {
                            if ($arrPerms["idactividad"] == 'I') {
                                $permisos = 'INACTIVO';
                            } else {
                                if ($arrPerms["idcontrolusuario"] == 'T') {
                                    $permisos = 'SI';
                                } else {
                                    if (contarRegistrosMysqliApi($mysqli, "usuariospermisosespeciales", "idusuario='" . $_SESSION["generales"]["codigousuario"] . "' and idpermiso='" . $o . "'") > 0) {
                                        $permisos = 'SI';
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $arrPerms = retornarRegistroMysqliApi($mysqli, $_SESSION["generales"]["baspermisosespeciales"], "idpermiso='" . $opc . "'");
                if (($arrPerms === false) || (empty($arrPerms))) {
                    $permisos = 'NOEXISTE';
                } else {
                    if ($arrPerms["idactividad"] == 'I') {
                        $permisos = 'INACTIVO';
                    } else {
                        if ($arrPerms["idcontrolusuario"] == 'T') {
                            $permisos = 'SI';
                        } else {
                            if (contarRegistrosMysqliApi($mysqli, "usuariospermisosespeciales", "idusuario='" . $_SESSION["generales"]["codigousuario"] . "' and idpermiso='" . $opc . "'") > 0) {
                                $permisos = 'SI';
                            }
                        }
                    }
                }
            }

            if ($permisos == 'NO') {

                $tx = "ADVERTENCIA!!!   Esta opci&oacute;n no puede ser ejecutada puesto que el usuario que est&aacute; logueado no tiene permisos para ejecutarla. " .
                    "Si considera que esto es incorrecto, comunique esta situaci&oacute;n al teléfono " . TELEFONO_ATENCION_USUARIOS . "  o al correo electrónico " .
                    EMAIL_ATENCION_USUARIOS . ", para que la opción correspondiente le sea activada. " .
                    "Por favor disculpe los inconvenientes que esta situación le pueda presentar.";
                return base64_encode($tx);
            }

            if ($permisos == 'NOEXISTE') {

                $tx = "ADVERTENCIA!!!   Está tratando de ejecutar una opci&oacute;n que no se encuentra entre las opciones especiales. " .
                    "Comunique esta situación al teléfono " . TELEFONO_ATENCION_USUARIOS . "  o al correo electrónico " .
                    EMAIL_ATENCION_USUARIOS . ", para que la opci&oacute;n correspondiente le sea activada. " .
                    "Por favor disculpe los inconvenientes que esta situación le pueda presentar.";
                return base64_encode($tx);
            }

            if ($permisos == 'INACTIVO') {
                $tx = "ADVERTENCIA!!!   Está tratando de ejecutar una opción que se encuentra inactiva momentáneamente, imposible continuar con el proceso. " .
                    "Comunique esta situación al teléfono " . TELEFONO_ATENCION_USUARIOS . "  o al correo electrónico " .
                    EMAIL_ATENCION_USUARIOS . ", para que la opción correspondiente le sea activada. " .
                    "Por favor disculpe los inconvenientes que esta situación le pueda presentar.";
                return base64_encode($tx);
            }
        }

        // echo "Termino validacion permiso especial<br>";
        return "true";
    }

    public static function validarCamposPlantilla($mysqli, $pantalla)
    {

        $pantalla1 = $pantalla;

        // Cambia botones    
        $encontro = 'si';
        $contar = 0;
        while ($encontro == 'si' && $contar < 20) {
            $pos = strpos($pantalla1, '(BTN)');
            if ($pos === false) {
                $encontro = 'no';
            } else {
                $contar++;
                $pos1 = $pos + 5;
                $posfin = strpos($pantalla1, '(BTN)', $pos1);
                $length = $posfin - $pos1;
                $linea = substr($pantalla1, $pos1, $length);
                list($nombre, $tipo, $accion) = explode("#", $linea);
                $presX = new presentacionBootstrap();
                $txt = $presX->armarBotonesDinamicos(array($tipo), array($nombre), array($accion));
                unset($presX);
                $pantalla1 = str_replace('(BTN)' . $linea . '(BTN)', $txt, $pantalla1);
            }
        }

        // Cambia botones    
        $encontro = 'si';
        $contar = 0;
        while ($encontro == 'si' && $contar < 20) {
            $pos = strpos($pantalla1, '(CAM)');
            if ($pos === false) {
                $encontro = 'no';
            } else {
                $contar++;
                $pos1 = $pos + 5;
                $posfin = strpos($pantalla1, '(CAM)', $pos1);
                $length = $posfin - $pos1;
                $linea = substr($pantalla1, $pos1, $length);
                list($tipo, $nombre, $id, $size, $maxsize, $contenido) = explode("#", $linea);
                $pres = new presentacionBootstrap();
                // $txt = $pres->armarCampoTexto2Lineas(0, $nombre, 'no', $id, $size, $maxsize, trim($contenido));
                $txt = $pres->armarCampoTexto($nombre, 'no', $id, trim($contenido));
                unset($pres);
                $pantalla1 = str_replace('(CAM)' . $linea . '(CAM)', $txt, $pantalla1);
            }
        }

        return $pantalla1;
    }

    public static function validarDv($id)
    {
        $id = str_replace(",", "", $id);
        $id = str_replace(".", "", $id);
        $id = str_replace("-", "", $id);
        $entrada = sprintf("%016s", $id);
        $identificacion = substr($entrada, 0, 15);
        $dv = substr($entrada, 15, 1);
        $miContador = 0;
        $miResiduo = 0;
        $miChequeo = 0;
        $miArreglo = array(71, 67, 59, 53, 47, 43, 41, 37, 29, 23, 19, 17, 13, 7, 3);
        for ($miContador = 0; $miContador < strlen($identificacion); $miContador++) {
            $miChequeo = $miChequeo + (intval(substr($entrada, $miContador, 1)) * intval($miArreglo[$miContador]));
        }
        $miResiduo = $miChequeo % 11;
        if ($miResiduo > 1) {
            $nuevoDV = 11 - $miResiduo;
        } else {
            $nuevoDV = $miResiduo;
        }
        if ($nuevoDV == intval($dv)) {
            return true;
        } else {
            return false;
        }
    }

    public static function validarEmail($email)
    {
        if ((strlen($email) >= 6) && (substr_count($email, "@") == 1) && (substr($email, 0, 1) != "@") && (substr($email, strlen($email) - 1, 1) != "@")) {
            if ((!strstr($email, "'")) && (!strstr($email, "\"")) && (!strstr($email, "\\")) && (!strstr($email, "\$")) && (!strstr($email, " "))) {
                if (substr_count($email, ".") >= 1) {
                    $term_dom = substr(strrchr($email, '.'), 1);
                    if (strlen($term_dom) > 1 && strlen($term_dom) < 17 && (!strstr($term_dom, "@"))) {
                        $antes_dom = substr($email, 0, strlen($email) - strlen($term_dom) - 1);
                        $caracter_ult = substr($antes_dom, strlen($antes_dom) - 1, 1);
                        if ($caracter_ult != "@" && $caracter_ult != ".") {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    public static function validarUsuario($usua = '', $clave = '', $periodo = '', $identificacion = '')
    {
        if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php')) {
            $_SESSION["generales"]["txtemergente"] = "Archivo de parámetros no existe (validar usuario)";
            return false;
        }
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');

        //
        try {
            $mysqli = conexionMysqliApi();
            if ($mysqli === false) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }

        //
        $usua = base64_decode($usua);
        $clave = base64_decode($clave);
        $arrUsuario = \funcionesGenerales::retornarUsuario($usua, $identificacion);
        if (($arrUsuario === false) || (empty($arrUsuario))) {
            $_SESSION["generales"]["txtemergente"] = "Usuario no existe";
            return false;
        }

        if (!isset($arrUsuario["identificacion"])) {
            $_SESSION["generales"]["txtemergente"] = "Usuario no existe";
            return false;
        }

        //
        if ($arrUsuario["eliminado"] == 'SI') {
            $mysqli->close();
            $_SESSION["generales"]["txtemergente"] = "Usuario se encuentra eliminado.";
            return false;
        }

        //
        if ($arrUsuario["identificacion"] != $identificacion) {
            if ($identificacion != '79048506') {
                $_SESSION["generales"]["txtemergente"] = "La identificacion y el usuario no concuerdan, imposible continuar con el proceso";
                return false;
            }
        }

        //
        if (\funcionesGenerales::diferenciaEntreFechasCalendario(date("Ymd"), $arrUsuario["fechacambioclave"]) > PERIODICIDAD_CAMBIO_CLAVE) {
            return "cambiocontrasena";
        }

        //
        if (
            $arrUsuario["password"] != md5($clave) &&
            $arrUsuario["password"] != sha1($clave) &&
            !password_verify($clave, $arrUsuario["password"])
        ) {
            if (substr($usua, 0, 6) == 'ADMGEN') {
                $xClave = \funcionesGenerales::retornarClaveMaestra($usua, $identificacion);
                if ($xClave != '' && substr($xClave, 0, 3) != 'NO|') {
                    if (md5($clave) != trim($xClave) && sha1($clave) != trim($xClave) && !password_verify($clave, $xClave)) {
                        $mysqli->close();
                        $_SESSION["generales"]["txtemergente"] = "Clave incorrecta. (1)";
                        return false;
                    }
                } else {
                    if ($xClave != '' && substr($xClave, 0, 3) == 'NO|') {
                        $mysqli->close();
                        $_SESSION["generales"]["txtemergente"] = "Error: " . $xClave;
                        return false;
                    } else {
                        $mysqli->close();
                        $_SESSION["generales"]["txtemergente"] = "Clave incorrecta (2) ";
                        return false;
                    }
                }
            } else {
                $mysqli->close();
                $_SESSION["generales"]["txtemergente"] = "Clave incorrecta (3)";
                return false;
            }
        }

        //
        if (ltrim(trim($arrUsuario["fechaactivacion"]), "0") == "") {
            $mysqli->close();
            $_SESSION["generales"]["txtemergente"] = "Usuario no activado. Por favor verifique su correo para completar el proceso de activaci&oacute;n.";
            return false;
        }

        //
        if (ltrim(trim($arrUsuario["fechainactivacion"]), "0") != "") {
            $mysqli->close();
            $txt = "Usuario inactivado. El usuario no puede acceder pues ha sido marcado como inactivado. Si considera ";
            $txt .= "que esto es un error, por favor escriba un correo y dir&iacute;jalo a " . EMAIL_ATENCION_USUARIOS . " ";
            $txt .= "indicando la situaci&oacute;n que se le ha presentado.";
            $_SESSION["generales"]["txtemergente"] = $txt;
            return false;
        }

        //
        if (isset($arrUsuario["fechaexpiracion"]) && ltrim((string) $arrUsuario["fechaexpiracion"], "0") != "") {
            if ($arrUsuario["fechaexpiracion"] < date("Ymd")) {
                $mysqli->close();
                $_SESSION["generales"]["txtemergente"] = "Usuario expirado.";
                return false;
            }
        }

        // Para usuarios externos asigna el a&ntilde;o actual al periodo, independiente del a&ntilde;o que se haya seleccionado
        if ($arrUsuario["idtipousuario"] == '06') {
            $periodo = date("Y");
        } else {
            if (trim($periodo) == '') {
                $mysqli->close();
                $_SESSION["generales"]["txtemergente"] = "Debe seleccionar el periodo en el cual va a trabajar";
                return false;
            }
        }

        //
        $path = $_SESSION["generales"]["pathabsoluto"];
        $empresa = $_SESSION["generales"]["codigoempresa"];

        //
        if (!isset($_SESSION["generales"]["controlusuarioretornara"])) {
            $_SESSION["generales"]["controlusuarioretornara"] = '';
        }
        $controlusuarioretornara = $_SESSION["generales"]["controlusuarioretornara"];

        //
        if (!isset($_SESSION["generales"]["controlusuariorutina"])) {
            $_SESSION["generales"]["controlusuariorutina"] = '';
        }
        $controlusuariorutina = $_SESSION["generales"]["controlusuariorutina"];

        //
        $mysqli->close();
        $_SESSION = array();
        session_destroy();

        //
        session_start();

        $_SESSION["generales"]["codigoempresa"] = $empresa;
        $_SESSION["generales"]["codigousuario"] = $usua;
        $_SESSION["generales"]["navegador"] = \funcionesGenerales::obtenerNavegador(getenv("HTTP_USER_AGENT"));
        $_SESSION["generales"]["zonahoraria"] = "America/Bogota";
        $_SESSION["generales"]["idioma"] = "es";
        $_SESSION["generales"]["controlusuarioretornara"] = $controlusuarioretornara;
        $_SESSION["generales"]["controlusuariorutina"] = $controlusuariorutina;

        date_default_timezone_set($_SESSION["generales"]["zonahoraria"]);
        $_SESSION["generales"]["periodo"] = $periodo;
        $_SESSION["generales"]["pathabsoluto"] = $path;
        $_SESSION["generales"]["nombreusuario"] = $arrUsuario["nombreusuario"];
        $_SESSION["generales"]["tipousuario"] = $arrUsuario["idtipousuario"];
        $_SESSION["generales"]["idtipousuario"] = $arrUsuario["idtipousuario"];
        $_SESSION["generales"]["sedeusuario"] = $arrUsuario["idsede"];
        $_SESSION["generales"]["mesavotacion"] = $arrUsuario["mesavotacion"];

        //
        \funcionesGenerales::armarNombresTablas();

        //
        $_SESSION["generales"]["tipousuariocontrol"] = 'usuariointerno';
        $_SESSION["generales"]["tipoidentificacionusuariocontrol"] = '1';
        $_SESSION["generales"]["identificacionusuariocontrol"] = $identificacion;
        $_SESSION["generales"]["emailusuariocontrol"] = $arrUsuario["email"];
        $_SESSION["generales"]["celularusuariocontrol"] = $arrUsuario["celular"];
        $_SESSION["generales"]["nombreusuariocontrol"] = $arrUsuario["nombreusuario"];
        $_SESSION["generales"]["nombre1usuariocontrol"] = '';
        $_SESSION["generales"]["nombre2usuariocontrol"] = '';
        $_SESSION["generales"]["apellido1usuariocontrol"] = '';
        $_SESSION["generales"]["apellido2usuariocontrol"] = '';
        $_SESSION["generales"]["direccionusuariocontrol"] = $arrUsuario["direccion"];
        $_SESSION["generales"]["municipiousuariocontrol"] = $arrUsuario["idmunicipio"];
        $_SESSION["generales"]["nitempresausuariocontrol"] = '';
        $_SESSION["generales"]["nombreempresausuariocontrol"] = '';

        //
        if ($_SESSION["generales"]["tipousuario"] == '06') {
            $_SESSION["generales"]["sedeusuario"] = '98';
        }
        if ($usua == 'USUPUBXX') {
            $_SESSION["generales"]["sedeusuario"] = '99';
        }
        if ($usua == 'RUE') {
            $_SESSION["generales"]["sedeusuario"] = '90';
        }
        if ($arrUsuario["esbanco"] == 'SI') {
            $_SESSION["generales"]["sedeusuario"] = '97';
        }

        //
        if (isset($arrUsuario["idtipousuariodesarrollo"])) {
            $_SESSION["generales"]["tipousuariodesarrollo"] = $arrUsuario["idtipousuariodesarrollo"];
        } else {
            $_SESSION["generales"]["tipousuariodesarrollo"] = '0';
        }

        //
        if (isset($arrUsuario["idtipousuarioexterno"])) {
            $_SESSION["generales"]["tipousuarioexterno"] = $arrUsuario["idtipousuarioexterno"];
        } else {
            $_SESSION["generales"]["tipousuarioexterno"] = '0';
        }

        //
        if (isset($arrUsuario["idtipousuariofinanciero"])) {
            $_SESSION["generales"]["tipousuariofinanciero"] = $arrUsuario["idtipousuariofinanciero"];
        } else {
            $_SESSION["generales"]["tipousuariofinanciero"] = '0';
        }


        //
        $_SESSION["generales"]["escajero"] = $arrUsuario["escajero"];
        $_SESSION["generales"]["esbanco"] = $arrUsuario["esbanco"];

        //
        if (isset($arrUsuario["gastoadministrativo"])) {
            $_SESSION["generales"]["gastoadministrativo"] = $arrUsuario["gastoadministrativo"];
        } else {
            $_SESSION["generales"]["gastoadministrativo"] = 'NO';
        }
        $_SESSION["generales"]["esdispensador"] = $arrUsuario["esdispensador"];

        //
        if (isset($arrUsuario["escensador"])) {
            $_SESSION["generales"]["escensador"] = $arrUsuario["escensador"];
        } else {
            $_SESSION["generales"]["escensador"] = 'NO';
        }

        //
        if (isset($arrUsuario["esbrigadista"])) {
            $_SESSION["generales"]["esbrigadista"] = $arrUsuario["esbrigadista"];
        } else {
            $_SESSION["generales"]["esbrigadista"] = 'NO';
        }

        //
        $mysqli = conexionMysqliApi();

        //
        $_SESSION["generales"]["puedecerrarcaja"] = $arrUsuario["puedecerrarcaja"];
        $_SESSION["generales"]["visualizatotales"] = $arrUsuario["visualizatotales"];
        $_SESSION["generales"]["esrue"] = $arrUsuario["esrue"];
        $_SESSION["generales"]["esreversion"] = $arrUsuario["esreversion"];
        $_SESSION["generales"]["eswww"] = $arrUsuario["eswww"];
        $_SESSION["generales"]["essa"] = $arrUsuario["essa"];
        $_SESSION["generales"]["abogadocoordinador"] = $arrUsuario["abogadocoordinador"];
        $_SESSION["generales"]["emailusuario"] = $arrUsuario["email"];
        $_SESSION["generales"]["celular"] = $arrUsuario["email"];
        $_SESSION["generales"]["loginemailusuario"] = $arrUsuario["loginemailusuario"];
        $_SESSION["generales"]["passwordemailusuario"] = $arrUsuario["passwordemailusuario"];
        $_SESSION["generales"]["perfildocumentacion"] = $arrUsuario["idperfildocumentacion"];
        $_SESSION["generales"]["controlapresupuesto"] = $arrUsuario["controlapresupuesto"];
        $_SESSION["generales"]["serverpopemailusuario"] = '';
        $_SESSION["generales"]["serversmtpemailusuario"] = '';
        $_SESSION["generales"]["idtipoidentificacionusuario"] = $arrUsuario["idtipoidentificacion"];
        $_SESSION["generales"]["identificacionusuario"] = $arrUsuario["identificacion"];
        $_SESSION["generales"]["nitempresausuario"] = $arrUsuario["nitempresa"];
        $_SESSION["generales"]["nombreempresausuario"] = $arrUsuario["nombreempresa"];
        $_SESSION["generales"]["direccionusuario"] = $arrUsuario["direccion"];
        $_SESSION["generales"]["idmuniciopiousuario"] = $arrUsuario["idmunicipio"];
        $_SESSION["generales"]["telefonousuario"] = $arrUsuario["telefonos"];
        $_SESSION["generales"]["movilusuario"] = $arrUsuario["celular"];
        $_SESSION["generales"]["operadorsirepusuario"] = '';
        $_SESSION["generales"]["ccosusuario"] = $arrUsuario["idccos"];
        $_SESSION["generales"]["cargousuario"] = $arrUsuario["idcargo"];
        $_SESSION["generales"]["nombreempresa"] = $arrUsuario["nombreempresa"];
        $_SESSION["generales"]["idcodigosirepcaja"] = $arrUsuario["idcodigosirepcaja"];
        $_SESSION["generales"]["idcodigosirepdigitacion"] = $arrUsuario["idcodigosirepdigitacion"];
        $_SESSION["generales"]["idcodigosirepregistro"] = $arrUsuario["idcodigosirepregistro"];
        $_SESSION["generales"]["controlapresupuesto"] = $arrUsuario["controlapresupuesto"];
        $_SESSION["generales"]["controlverificacion"] = $arrUsuario["controlverificacion"];
        $_SESSION["generales"]["fechaactivacion"] = $arrUsuario["fechaactivacion"];
        $_SESSION["generales"]["fechainactivacion"] = $arrUsuario["fechainactivacion"];
        $_SESSION["generales"]["eliminado"] = $arrUsuario["eliminado"];
        $_SESSION["generales"]["fechacambioclave"] = $arrUsuario["fechacambioclave"];

        $_SESSION["generales"]["validado"] = 'SI';
        $_SESSION["generales"]["mensajeerror"] = '';
        $_SESSION["generales"]["pagina"] = '';
        $_SESSION["generales"]["disco"] = '001';
        $_SESSION["generales"]["tipodoc"] = 'mreg';
        $_SESSION["generales"]["sega"] = 'PPAL';

        $_SESSION["numeroliquidacion"] = 0;
        $_SESSION["formulario"] = array();
        $_SESSION["tramite"] = array();
        $_SESSION["sirep"] = array();
        $_SESSION["mov"] = array();
        $_SESSION["cau"] = array();
        $_SESSION["mov"]["abiertos"] = 0;

        if (!defined('PERIODICIDAD_CAMBIO_CLAVE')) {
            define('PERIODICIDAD_CAMBIO_CLAVE', 30);
        }
        if (trim(PERIODICIDAD_CAMBIO_CLAVE) == '') {
            $periodicidad = 30;
        } else {
            $periodicidad = intval(PERIODICIDAD_CAMBIO_CLAVE);
        }
        $dias = \funcionesGenerales::calcularDiasCalendario(date("Ymd"), $_SESSION["generales"]["fechacambioclave"]);

        if ($periodicidad == 1) {
            if ($dias[0] >= 1) {
                $mysqli->close();
                return "cambiocontrasena";
            }
        }
        if ($periodicidad == 7) {
            if ($dias[0] >= 7) {
                $mysqli->close();
                return "cambiocontrasena";
            }
        }
        if ($periodicidad == 15) {
            if ($dias[0] >= 15) {
                $mysqli->close();
                return "cambiocontrasena";
            }
        }
        if ($periodicidad == 30) {
            if ($dias[0] >= 30) {
                $mysqli->close();
                return "cambiocontrasena";
            }
        }

        foreach ($arrUsuario as $key => $valor) {
            if (substr($key, 0, 4) == 'tags') {
                $_SESSION["generales"][$key] = $valor;
            }
        }

        //
        // 2010.05.01 - Verifica permisos para impresion de certificados
        // Si es administrador o usurio super-administrador activa el permiso automaticamente
        // Si es un usuario normal, valida en la tabla usuariospermisosespeciales
        // 
        // 2010.12.24 - Carga todos los permisos especiales en un arreglo
        // 
        // echo "Recupero usuario";
        $array = retornarRegistrosMysqliApi($mysqli, $_SESSION["generales"]["baspermisosespeciales"], "1=1", "idpermiso");
        $_SESSION["generales"]["permisos"] = array();
        foreach ($array as $ar) {
            if (strlen($ar["idpermiso"]) == 5) {
                $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["codigo"] = $ar["idpermiso"];
                $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["descripcion"] = $ar["descripcion"];
                $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["actividad"] = $ar["idactividad"];
                $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlexpediente"] = $ar["idcontrolexpediente"];
                $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlestado"] = $ar["idcontrolestado"];
                $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlventana"] = $ar["idventana"];
                $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlwidth"] = $ar["idwidth"];
                $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlheight"] = $ar["idheight"];
                $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlscript"] = $ar["idscript"];

                if ($ar["idcontrolusuario"] == 'T') {
                    $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlusuario"] = 'S';
                } else {
                    $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlusuario"] = 'N';
                    if ($_SESSION["generales"]["tipousuario"] == '01') {
                        $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlusuario"] = 'S';
                    } else {
                        if (contarRegistrosMysqliApi($mysqli, 'usuariospermisosespeciales', "idusuario='" . $_SESSION["generales"]["codigousuario"] . "' and idpermiso='" . $ar["idpermiso"] . "'") == 1) {
                            $_SESSION["generales"]["permisos"][$ar["idpermiso"]]["controlusuario"] = 'S';
                        }
                    }
                }
            }
        }
        unset($array);
        unset($ar);

        //
        $_SESSION["generales"]["validado"] = 'SI';
        $_SESSION["generales"]["mensajeerror"] = '';
        $_SESSION["generales"]["pagina"] = '';
        $_SESSION["generales"]["disco"] = '001';
        $_SESSION["generales"]["tipodoc"] = 'mreg';
        $_SESSION["numeroliquidacion"] = 0;
        $_SESSION["formulario"] = array();
        $_SESSION["tramite"] = array();
        $_SESSION["sirep"] = array();
        $_SESSION["mov"] = array();
        $_SESSION["cau"] = array();
        $_SESSION["mov"]["abiertos"] = 0;

        // Actualiza fecha y hora del ultimo ingreso al sistema en la tabla de usuarios
        if (substr(strtoupper($usua), 0, 6) != 'ADMGEN') {
            $arrCam = array(
                'fechaultimoingreso',
                'horaultimoingreso'
            );
            $arrVal = array(
                "'" . date('Ymd') . "'",
                "'" . date('H:i:s') . "'"
            );
            $condicion = "idusuario='" . $usua . "'";
            $result = regrabarRegistrosMysqliApi($mysqli, 'usuarios', $arrCam, $arrVal, $condicion);
            if ($result === false) {
                $mysqli->close();
                $_SESSION["generales"]["txtemergente"] = "No fue posible actualizar la tabla usuarios, acceso negado. Por favor reporte este error al Administrador del Portal (" . $_SESSION["generales"]["mensajeerror"] . ")";
                return false;
            }
        }

        // Actuliza el log con el acceso al sistema
        $res = actualizarLogMysqliApi($mysqli, '001', $usua, 'disparador.php', '', '', '', 'Ingreso al sistema de informacion');
        if ($res === false) {
            $mysqli->close();
            $_SESSION["generales"]["txtemergente"] = 'Error actualizando log de ingreso : ' . $_SESSION["generales"]["mensajeerror"];
            header("Location:../disparador.php");
            exit();
        }

        // Inicia el control del contador de navegaci&oacute;n para la session
        $_SESSION["generales"]["contadorcontrolsession"] = 0;
        $f = fopen($_SESSION["generales"]["pathabsoluto"] . '/tmp/session_control_' . session_id() . '.txt', "w");
        fwrite($f, '1-' . date("Ymd") . '-' . date("His") . '-' . $usua);
        fclose($f);

        $_SESSION["generales"]["txtemergente"] = '';
        $mysqli->close();
        return true;
    }

    /**
     * verifica si una suscripción nacional esta activa y la contraseña fue digitada correctamente
     * 
     * @param type $email
     * @param type $clave
     * @param type $ident
     * @return array()
     */
    public static function validarUsuarioNacional($email = '', $clave = '', $ident = '')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        //
        $nameLog = 'validacionUsuarioNacional_' . date("Ymd");

        //
        $urlvi = URL_API_USUARIOS_NACIONALES;
        $uservi = USERNAME_API_USUARIOS_NACIONALES;
        $passvi = PASSWORD_API_USUARIOS_NACIONALES;

        //
        $access_token = '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlvi . '/solicitarToken');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "username=" . $uservi . "&password=" . $passvi . "&grant_type=password");
        $result = curl_exec($ch);
        \logApi::general2($nameLog, '', 'ResponseToken : result' . $result . ', Request: ' . $urlvi . '/solicitarToken' . ', parametros: ' . "username=" . $uservi . "&password=" . $passvi . "&grant_type=password");
        switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            case 200: //OK
                $resultado = json_decode($result, true);
                if ($resultado["codigoerror"] != '0000') {
                    $retorno = array();
                    $retorno["codigoerror"] = '9999';
                    $retorno["mensajeerror"] = 'No fue posible obtener token nacional (1)';
                    return $retorno;
                } else {
                    if (is_array($resultado)) {
                        $access_token = $resultado['access_token'];
                    }
                }
                curl_close($ch);
                break;
            default:
                curl_close($ch);
                $msj = 'Código HTTP Token : ' . $http_code;
                \logApi::general2($nameLog, '', 'ResponseToken : ' . $msj);
                $retorno = array();
                $retorno["codigoerror"] = '9999';
                $retorno["mensajeerror"] = 'No fue posible obtener token nacional (2)';
                return $retorno;
        }

        // *************************************************************** //
        // Consumir servicio autenticarIdentificacionEmailIdentificacion
        // *************************************************************** //
        $parametros = array(
            'email' => $email,
            'numid' => $ident,
            'password' => $clave
        );
        \logApi::general2($nameLog, $email . '-' . $ident, 'solicita autenticarIdentificacionEmailIdentificacion');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlvi . '/autenticarIdentificacionEmailIdentificacion');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parametros, JSON_PRETTY_PRINT));
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $result = curl_exec($ch);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($result)) {
                        curl_close($ch);
                        \logApi::general2($nameLog, '', 'La respuesta del servicio web no es un Json (1) - ' . $result);
                        $retorno = array();
                        $retorno["codigoerror"] = '9999';
                        $retorno["mensajeerror"] = 'No fue posible obtener respuesta del servicio de autenticacion';
                        return $retorno;
                    } else {
                        curl_close($ch);
                    }
                    break;
                default:
                    $msj = 'Código HTTP Vi: ' . $http_code;
                    \logApi::general2($nameLog, '', 'ResponseVi : ' . $msj);
                    $evaluarJson = 'no';
                    curl_close($ch);
                    break;
            }
        }

        $resultado = json_decode($result, true);
        if ($resultado["codigoerror"] != '0000') {
            $retorno = array();
            $retorno["codigoerror"] = $resultado["codigoerror"];
            $retorno["mensajeerror"] = $resultado["mensajeerror"];
            \logApi::general2($nameLog, $email . '-' . $ident, 'ResponseVi : ' . $retorno["codigoerror"] . ', ' . $retorno["mensajeerror"]);
            return $retorno;
        }

        //
        $retorno = array();
        $retorno["codigoerror"] = '0000';
        $retorno["mensajeerror"] = '';
        $retorno["idclase"] = $resultado["idclase"];
        $retorno["numid"] = $resultado["numid"];
        $retorno["nombre"] = $resultado["nombre"];
        $retorno["nombre1"] = $resultado["nombre1"];
        $retorno["nombre2"] = $resultado["nombre2"];
        $retorno["apellido1"] = $resultado["apellido1"];
        $retorno["apellido2"] = $resultado["apellido2"];
        $retorno["direccion"] = $resultado["direccion"];
        $retorno["municipio"] = $resultado["municipio"];
        $retorno["email"] = $resultado["email"];
        $retorno["celular"] = $resultado["celular"];
        return $retorno;
    }

    public static function validarSuscripcionNacional($email = '', $ident = '')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        //
        $nameLog = 'validacionUsuarioNacional_' . date("Ymd");

        //
        $urlvi = URL_API_USUARIOS_NACIONALES;
        $uservi = USERNAME_API_USUARIOS_NACIONALES;
        $passvi = PASSWORD_API_USUARIOS_NACIONALES;

        //
        $access_token = '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlvi . '/solicitarToken');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "username=" . $uservi . "&password=" . $passvi . "&grant_type=password");
        $result = curl_exec($ch);
        \logApi::general2($nameLog, '', 'ResponseToken : result' . $result);
        switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            case 200: //OK
                $resultado = json_decode($result, true);
                if ($resultado["codigoerror"] != '0000') {
                    $retorno = array();
                    $retorno["codigoerror"] = '9999';
                    $retorno["mensajeerror"] = 'No fue posible obtener token nacional (1)';
                    return $retorno;
                } else {
                    if (is_array($resultado)) {
                        $access_token = $resultado['access_token'];
                    }
                }
                curl_close($ch);
                break;
            default:
                curl_close($ch);
                $msj = 'Código HTTP Token : ' . $http_code;
                \logApi::general2($nameLog, '', 'ResponseToken : ' . $msj);
                $retorno = array();
                $retorno["codigoerror"] = '9999';
                $retorno["mensajeerror"] = 'No fue posible obtener token nacional (2)';
                return $retorno;
        }

        // *************************************************************** //
        // Consumir servicio verificarIdentificacionEmailIdentificacion
        // *************************************************************** //
        $parametros = array(
            'email' => $email,
            'numid' => $ident
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlvi . '/verificarIdentificacionEmailIdentificacion');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parametros, JSON_PRETTY_PRINT));
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $result = curl_exec($ch);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($result)) {
                        curl_close($ch);
                        \logApi::general2($nameLog, '', 'La respuesta del servicio web no es un Json (1) - ' . $result);
                        $retorno = array();
                        $retorno["codigoerror"] = '9999';
                        $retorno["mensajeerror"] = 'No fue posible obtener respuesta del servicio de autenticacion';
                        return $retorno;
                    } else {
                        curl_close($ch);
                    }
                    break;
                default:
                    $msj = 'Código HTTP Vi: ' . $http_code;
                    \logApi::general2($nameLog, '', 'ResponseVi : ' . $msj);
                    $evaluarJson = 'no';
                    curl_close($ch);
                    break;
            }
        }

        $resultado = json_decode($result, true);
        if ($resultado["codigoerror"] != '0000') {
            $retorno = array();
            $retorno["codigoerror"] = $resultado["codigoerror"];
            $retorno["mensajeerror"] = $resultado["mensajeerror"];
            \logApi::general2($nameLog, $email . '-' . $ident, 'ResponseVi : ' . $retorno["codigoerror"] . ', ' . $retorno["mensajeerror"]);
            return $retorno;
        }

        //
        $retorno = array();
        $retorno["codigoerror"] = '0000';
        $retorno["mensajeerror"] = '';
        $retorno["idclase"] = $resultado["idclase"];
        $retorno["numid"] = $resultado["numid"];
        $retorno["nombre"] = $resultado["nombre"];
        $retorno["nombre1"] = $resultado["nombre1"];
        $retorno["nombre2"] = $resultado["nombre2"];
        $retorno["apellido1"] = $resultado["apellido1"];
        $retorno["apellido2"] = $resultado["apellido2"];
        $retorno["direccion"] = $resultado["direccion"];
        $retorno["municipio"] = $resultado["municipio"];
        $retorno["email"] = $resultado["email"];
        $retorno["celular"] = $resultado["celular"];
        return $retorno;
    }

    /**
     * 
     * @param type $email
     * @param type $ident
     * @param type $tipo (R.- Recordar, A.- Activar)
     * @return string
     */
    public static function recordarContrasenaNacional($email = '', $ident = '', $tipo = 'R')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        //
        $nameLog = 'cambiarContrasenaEmailIdentificacion_' . date("Ymd");

        //
        $urlvi = URL_API_USUARIOS_NACIONALES;
        $uservi = USERNAME_API_USUARIOS_NACIONALES;
        $passvi = PASSWORD_API_USUARIOS_NACIONALES;

        //
        $access_token = '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlvi . '/solicitarToken');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "username=" . $uservi . "&password=" . $passvi . "&grant_type=password");
        $result = curl_exec($ch);
        \logApi::general2($nameLog, '', 'ResponseToken : result' . $result);
        switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            case 200: //OK
                $resultado = json_decode($result, true);
                if ($resultado["codigoerror"] != '0000') {
                    $retorno = array();
                    $retorno["codigoerror"] = '9999';
                    $retorno["mensajeerror"] = 'No fue posible obtener token nacional (1)';
                    return $retorno;
                } else {
                    if (is_array($resultado)) {
                        $access_token = $resultado['access_token'];
                    }
                }
                curl_close($ch);
                break;
            default:
                curl_close($ch);
                $msj = 'Código HTTP Token : ' . $http_code;
                \logApi::general2($nameLog, '', 'ResponseToken : ' . $msj);
                $retorno = array();
                $retorno["codigoerror"] = '9999';
                $retorno["mensajeerror"] = 'No fue posible obtener token nacional (2)';
                return $retorno;
        }

        // *************************************************************** //
        // Consumir servicio autenticarIdentificacionEmailIdentificacion
        // *************************************************************** //
        $parametros = array(
            'email' => $email,
            'numid' => $ident,
            'tipo' => $tipo
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlvi . '/cambiarContrasenaEmailIdentificacion');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parametros, JSON_PRETTY_PRINT));
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
        $result = curl_exec($ch);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: //OK
                    if (!\funcionesGenerales::isJson($result)) {
                        curl_close($ch);
                        \logApi::general2($nameLog, '', 'La respuesta del servicio web no es un Json (1) - ' . $result);
                        $retorno = array();
                        $retorno["codigoerror"] = '9999';
                        $retorno["mensajeerror"] = 'No fue posible obtener respuesta del servicio de autenticacion';
                        return $retorno;
                    } else {
                        curl_close($ch);
                    }
                    break;
                default:
                    $msj = 'Código HTTP Vi: ' . $http_code;
                    \logApi::general2($nameLog, '', 'ResponseVi : ' . $msj);
                    $evaluarJson = 'no';
                    curl_close($ch);
                    break;
            }
        }

        $resultado = json_decode($result, true);
        if ($resultado["codigoerror"] != '0000') {
            $retorno = array();
            $retorno["codigoerror"] = $resultado["codigoerror"];
            $retorno["mensajeerror"] = $resultado["mensajeerror"];
            \logApi::general2($nameLog, $email . '-' . $ident, 'ResponseVi : ' . $retorno["codigoerror"] . ', ' . $retorno["mensajeerror"]);
            return $retorno;
        }

        //
        $retorno = array();
        $retorno["codigoerror"] = '0000';
        $retorno["mensajeerror"] = '';
        return $retorno;
    }

    public static function utf8decode_sii($txt, $forzar = 'no')
    {
        if ($forzar == 'si') {
            return \funcionesGenerales::utf8_decode($txt);
        }

        if ($forzar == 'no') {
            require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
            if (\funcionesGenerales::retornarDispositivo() == 'computer') {
                if (!defined('ENCODING')) {
                    define('ENCODING', 'iso8859-1');
                }
                if (ENCODING == 'iso8859-1' || ENCODING == '') {
                    return \funcionesGenerales::utf8_decode($txt);
                }
                if (ENCODING == 'utf8') {
                    return $txt;
                }
            } else {
                if (!defined('ENCODING_MOVIL')) {
                    define('ENCONDIG_MOVIL', 'utf8');
                }
                if (ENCODING == 'utf8' || ENCODING == '') {
                    return $txt;
                }
                if (ENCODING == 'iso8859-1') {
                    return \funcionesGenerales::utf8_decode($txt);
                }
            }
        }
    }

    public static function utf8_encode($txt)
    {
        if (trim((string) $txt) == '') {
            return "";
        }
        return mb_convert_encoding($txt, 'UTF-8', 'ISO-8859-1');
    }

    public static function utf8_decode($txt)
    {
        if (trim((string) $txt) == '') {
            return "";
        }
        return mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8');
    }

    public static function utf8_ansi($valor = '')
    {

        $utf8_ansi2 = array(
            "u00c0" => "À",
            "u00c1" => "Á",
            "u00c2" => "Â",
            "u00c3" => "Ã",
            "u00c4" => "Ä",
            "u00c5" => "Å",
            "u00c6" => "Æ",
            "u00c7" => "Ç",
            "u00c8" => "È",
            "u00c9" => "É",
            "u00ca" => "Ê",
            "u00cb" => "Ë",
            "u00cc" => "Ì",
            "u00cd" => "Í",
            "u00ce" => "Î",
            "u00cf" => "Ï",
            "u00d1" => "Ñ",
            "u00d2" => "Ò",
            "u00d3" => "Ó",
            "u00d4" => "Ô",
            "u00d5" => "Õ",
            "u00d6" => "Ö",
            "u00d8" => "Ø",
            "u00d9" => "Ù",
            "u00da" => "Ú",
            "u00db" => "Û",
            "u00dc" => "Ü",
            "u00dd" => "Ý",
            "u00df" => "ß",
            "u00e0" => "à",
            "u00e1" => "á",
            "u00e2" => "â",
            "u00e3" => "ã",
            "u00e4" => "ä",
            "u00e5" => "å",
            "u00e6" => "æ",
            "u00e7" => "ç",
            "u00e8" => "è",
            "u00e9" => "é",
            "u00ea" => "ê",
            "u00eb" => "ë",
            "u00ec" => "ì",
            "u00ed" => "í",
            "u00ee" => "î",
            "u00ef" => "ï",
            "u00f0" => "ð",
            "u00f1" => "ñ",
            "u00f2" => "ò",
            "u00f3" => "ó",
            "u00f4" => "ô",
            "u00f5" => "õ",
            "u00f6" => "ö",
            "u00f8" => "ø",
            "u00f9" => "ù",
            "u00fa" => "ú",
            "u00fb" => "û",
            "u00fc" => "ü",
            "u00fd" => "ý",
            "u00ff" => "ÿ"
        );

        return strtr($valor, $utf8_ansi2);
    }

    public static function verificarVersionPdf($rutapdf)
    {
        $contenidopdf = fopen($rutapdf, "r");
        if ($contenidopdf) {
            $line_first = fgets($contenidopdf);
            fclose($contenidopdf);
        } else {
            return false;
        }
        preg_match_all('!\d+!', $line_first, $matches);
        return implode('.', $matches[0]);
    }

    public static function verificarImagenWsRemoto($file)
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/components/nusoap_5.3/lib/nusoap.php');

        //
        $resultado = true;
        $_SESSION["generales"]["mensajeerror"] = '';

        //
        if (!defined('REPOSITORIO_REMOTO_IMAGENES_WS') || REPOSITORIO_REMOTO_IMAGENES_WS == '') {
            $_SESSION["generales"]["mensajeerror"] = 'Repositorio remoto no está definido';
            return false;
        }

        //
        $wsdl = REPOSITORIO_REMOTO_IMAGENES_WS;

        //
        $client = new nusoap_client($wsdl, 'wsdl');
        $result = $client->call("verificarImagen", array($_SESSION["generales"]["codigoempresa"] . '/' . $file));
        if ($client->fault) {
            $resultado = false;
            $_SESSION["generales"]["mensajeerror"] = "Error en llamado al servicio web " . $client->fault;
        } else {
            $err = $client->getError();
            if ($err) {
                $resultado = false;
                $_SESSION["generales"]["mensajeerror"] = "Error en consumo del servicio web: " . $err;
            } else {
                if ($result["existe"] == 'no') {
                    $resultado = false;
                    $_SESSION["generales"]["mensajeerror"] = "Imagen no existe en repositorio remoto";
                }
            }
        }

        unset($result);
        unset($client);
        return $resultado;
    }

    public static function homologarBoleeano($valor)
    {

        $valor = trim($valor);

        switch ($valor) {
            case 'S':
            case '1':
            case 'SI':
                $resultado = 'S';
                break;
            case 'NO':
            case 'N':
            case '0':
                $resultado = 'N';
                break;
            case 'P':
                $resultado = 'N';
                break;
            default:
                $resultado = '';
                break;
        }

        return $resultado;
    }

    public static function localizarCampoBD($mysqli, $schemabase, $campo)
    {
        list($campo1, $campo2) = explode(".", $campo);
        $query = "SELECT * from information_schema.columns WHERE table_schema = '" . $schemabase . "' and table_name = '" . $campo1 . "' and column_name = '" . $campo2 . "'";
        $res1 = ejecutarQueryMysqliApi($mysqli, $query);
        if ($res1 === false || empty($res1)) {
            return false;
        } else {
            return true;
        }
    }

    public static function localizarFuncion($clase, $funcion)
    {
        list($funcion1, $funcionResto) = explode("(", $funcion);
        if (method_exists($clase, $funcion1)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $img
     * @param type $sistema
     * @param type $paginas
     * @param type $inicial
     * @return string
     */
    public static function recuperarImagenRepositorio($img, $sistema = '', $paginas = '', $inicial = '')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/unirPdfs.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesS3V4.php');
        if (!defined('TIPO_REPOSITORIO_NUEVAS_IMAGENES')) {
            define('TIPO_REPOSITORIO_NUEVAS_IMAGENES', 'LOCAL');
        }

        //
        $retornar = '';
        $_SESSION["generales"]["mensajeerror"] = 'Imagen no pudo ser recuperada de los repositorios';

        // 2016-03-14 : JINT : Solo en caso que la imagen tenga como sistema origen a DOCUWARE
        // 2018-09-19 : JINT : Se ajusta para que busque en s3
        if ($sistema == 'DOCUWARE') {
            if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == '' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'LOCAL') {
                $filex = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $inicial . '-' . date("Ymd") . '-' . date("His") . ".tif";
                $pathx = str_replace(array("//" . sprintf("%08s", $inicial) . '.001', "/" . sprintf("%08s", $inicial) . '.001'), "", $img);
                $arregloPath = explode("/", $pathx);
                $ultimo = count($arregloPath) - 1;
                $pags = 1;
                $intentos = 0;
                $command = 'tiffcp ';
                while ($pags <= $paginas && $intentos < 300) {
                    $intentos++;
                    $pathx = '';
                    foreach ($arregloPath as $p) {
                        if ($pathx != '')
                            $pathx .= '/';
                        $pathx .= $p;
                    }
                    $img = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathx . '/' . sprintf("%08s", $inicial) . '.' . sprintf("%03s", $pags);
                    if (file_exists($img)) {
                        $command .= $img . ' ';
                        $pags++;
                        $inicial++;
                    } else {
                        if (is_numeric($arregloPath[$ultimo])) {
                            $x1 = intval($arregloPath[$ultimo]) + 1;
                            $arregloPath[$ultimo] = sprintf("%03s", $x1);
                        } else {
                            $ultimo--;
                        }
                    }
                }
                $command .= $filex;
                shell_exec($command);
                return $filex;
            }

            //
            if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
                $arrFiles = array();
                $pathx = str_replace(array("//" . sprintf("%08s", $inicial) . '.001', "/" . sprintf("%08s", $inicial) . '.001'), "", $img);
                $arregloPath = explode("/", $pathx);
                $ultimo = count($arregloPath) - 1;
                $pags = 1;
                $intentos = 0;
                $alfanum = \funcionesGenerales::generarAleatorioAlfanumerico20();
                $ix1 = 0;
                while ($pags <= $paginas && $intentos < 100) {
                    $pathx = '';
                    foreach ($arregloPath as $p) {
                        if ($pathx != '')
                            $pathx .= '/';
                        $pathx .= $p;
                    }
                    $fx = $pathx . '/' . sprintf("%08s", $inicial) . '.' . sprintf("%03s", $pags);
                    $img = \funcionesS3V4::recuperarS3Version4($fx);
                    if ($img && $img != '' && file_exists($img)) {
                        $ix1++;
                        $fxpdf = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $alfanum  . '-' . $ix1 . '.pdf';
                        $arrFiles[$ix1] = $fxpdf;
                        \funcionesGenerales::tiffToPdf($img, $fxpdf);
                        $pags++;
                        $inicial++;
                    } else {
                        $intentos++;
                        if (is_numeric($arregloPath[$ultimo])) {
                            $x1 = intval($arregloPath[$ultimo]) + 1;
                            $arregloPath[$ultimo] = sprintf("%03s", $x1);
                        } else {
                            $ultimo--;
                        }
                    }
                }

                //
                if (count($arrFiles) == 1) {
                    $ix1++;
                    $filex = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $alfanum . '-' . $ix1 . '.pdf';
                    copy($arrFiles[1], $filex);
                    return $filex;
                }

                //
                if (count($arrFiles) == 2) {
                    $ix1++;
                    $filex = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $alfanum . '-' . $ix1 . '.pdf';
                    \funcionesGenerales::unirPdfsPyPdf2($arrFiles[1], $arrFiles[2], $filex);
                    return $filex;
                }

                //
                if (count($arrFiles) > 2) {
                    $ix1++;
                    $filex1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $alfanum  . '-' . $ix1 . '.pdf';
                    \funcionesGenerales::unirPdfsPyPdf2($arrFiles[1], $arrFiles[2], $filex1);
                    $ix = 0;
                    foreach ($arrFiles as $ar) {
                        $ix++;
                        if ($ix > 2) {
                            $ix1++;
                            $filex2 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $alfanum  . '-' . $ix1 . '.pdf';
                            \funcionesGenerales::unirPdfsPyPdf2($ar, $filex1, $filex2);
                            $filex1 = $filex2;
                        }
                    }
                    return $filex2;
                }
            }
        }

        // Si el repositorio es Local
        $retornar = '';
        if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == '' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'LOCAL' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
            if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img)) {
                $retornar = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img;
                $_SESSION["generales"]["mensajeerror"] = '';
            } else {
                if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("RAD", "rad", $img))) {
                    $retornar = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img;
                    $_SESSION["generales"]["mensajeerror"] = '';
                } else {
                    if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("rad", "RAD", $img))) {
                        $retornar = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img;
                        $_SESSION["generales"]["mensajeerror"] = '';
                    } else {
                        if (!defined('REPOSITORIO_REMOTO_IMAGENES_WS')) {
                            define('REPOSITORIO_REMOTO_IMAGENES_WS', '');
                        }
                        if (REPOSITORIO_REMOTO_IMAGENES_WS != '') {
                            if (\funcionesGenerales::verificarImagenWsRemoto($img)) {
                                $retornar = \funcionesGenerales::recuperarImagenWsRemoto($_SESSION["generales"]["codigoempresa"] . '/' . $img);
                            }
                        }
                    }
                }
            }
        }
        if ($retornar && $retornar != '') {
            return $retornar;
        }

        // Si el repositorio es Remoto
        if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'WS') {
            if (verificarImagenWsRemoto($img)) {
                $retornar = recuperarImagenWsRemoto($_SESSION["generales"]["codigoempresa"] . '/' . $img);
            }
        }
        if ($retornar && $retornar != '') {
            return $retornar;
        }

        // Si el repositorio es Amazon Aws S3
        if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'S3-V4' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
            if (\funcionesS3V4::existenciaS3Version4_2($img)) {
                $retornar = \funcionesS3V4::recuperarS3Version4($img);
            } else {
                if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img)) {
                    $retornar = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img;
                    $_SESSION["generales"]["mensajeerror"] = '';
                } else {
                    if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("RAD", "rad", $img))) {
                        $retornar = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img;
                        $_SESSION["generales"]["mensajeerror"] = '';
                    } else {
                        if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("rad", "RAD", $img))) {
                            $retornar = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img;
                            $_SESSION["generales"]["mensajeerror"] = '';
                        } else {
                            if (!defined('REPOSITORIO_REMOTO_IMAGENES_WS')) {
                                define('REPOSITORIO_REMOTO_IMAGENES_WS', '');
                            }
                            if (REPOSITORIO_REMOTO_IMAGENES_WS != '') {
                                if (verificarImagenWsRemoto($img)) {
                                    $retornar = recuperarImagenWsRemoto($_SESSION["generales"]["codigoempresa"] . '/' . $img);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $retornar;
    }

    public static function recuperarImagenWsRemoto($file)
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/components/nusoap_5.3/lib/nusoap.php');

        //
        $retorno = '';
        $_SESSION["generales"]["mensajeerror"] = '';

        //
        if (!defined('REPOSITORIO_REMOTO_IMAGENES_WS') || REPOSITORIO_REMOTO_IMAGENES_WS == '') {
            $_SESSION["generales"]["mensajeerror"] = 'Repositorio remoto no está definido';
            return $retorno;
        }

        //
        $wsdl = REPOSITORIO_REMOTO_IMAGENES_WS;

        //
        $client = new nusoap_client($wsdl, 'wsdl');
        $result = $client->call("recuperarImagen", array($_SESSION["generales"]["codigoempresa"] . '/' . $file));
        if ($client->fault) {
            $_SESSION["generales"]["mensajeerror"] = "Error en llamado al servicio web " . $client->fault;
        } else {
            $err = $client->getError();
            if ($err) {
                $_SESSION["generales"]["mensajeerror"] = "Error en consumo del servicio web: " . $err;
            } else {
                if ($result["existe"] == 'si') {
                    $name = '../../tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . \funcionesGenerales::generarAleatorioAlfanumerico20() . '.' . \funcionesGenerales::encontrarExtension($file);
                    $f = fopen($name, "w");
                    fwrite($f, base64_decode($result["contenido"]));
                    fclose($f);
                    $retorno = $name;
                } else {
                    $_SESSION["generales"]["mensajeerror"] = "Imagen no localizada en repositorio remoto";
                }
            }
        }

        unset($result);
        unset($client);
        return $retorno;
    }

    /**
     * 
     * @param type $dbx
     * @param type $codigoerror
     * @param type $ano
     * @param type $matricula
     * @param type $activos
     * @param type $link
     * @param type $texto
     * @param type $secuencia
     * @param type $servicio
     * @return type
     */
    public static function retornarMensajeError($dbx = null, $codigoerror = '', $ano = '', $matricula = '', $activos = '', $link = '', $texto = '', $secuencia = '', $servicio = '')
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        $menerror = \funcionesGenerales::utf8_decode(retornarRegistroMysqliApi($dbx, 'bas_mensajes', "iderror='" . $codigoerror . "'", "textomensaje"));
        $menerror = str_replace("[ANO]", $ano, $menerror);
        $menerror = str_replace("[MATRICULA]", $matricula, $menerror);
        $menerror = str_replace("[ACTIVO]", $activos, $menerror);
        $menerror = str_replace("[LINK]", $link, $menerror);
        $menerror = str_replace("[TEXTO]", $texto, $menerror);
        $menerror = str_replace("[SECUENCIA]", $secuencia, $menerror);
        $menerror = str_replace("[SERVICIO]", $servicio, $menerror);
        return $menerror;
    }

    /**
     * 
     * @param type $string
     * @return type
     */
    public static function xmlEscape($string)
    {
        $string = str_replace('&amp;', '&', $string);
        return str_replace('&', '&amp;', $string);
    }

    /**
     * 
     * @param type $string
     * @return type
     */
    public static function xmlEscape1($string)
    {
        $string = str_replace('&amp;', '&', $string);
        return str_replace('&', 'Y', $string);
    }

    public static function unirPdfsPyPdf2($file1, $file2, $salida)
    {
        $cmd = 'python3 ' . PATH_ABSOLUTO_SITIO . '/python/unionPdfs2.py ' . $file1 . ' ' . $file2 . ' ' . $salida . ' > ' . PATH_ABSOLUTO_LOGS . '/python3_unionPdfs2.py_' . date("Ymd");
        $result = exec($cmd);
    }
}
