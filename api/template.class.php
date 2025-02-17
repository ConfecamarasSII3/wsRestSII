<?php

/**
 * Clase para el manejo de templates y despliegue de pantallas
 * 
 * 
 * @author Jose Ivan Nieto Tabares
 * @author http://segaweb.confecamaras.org.co
 * @copyright Derechos reservados 
 * @version Versi&oacute;n 1.0 (&uacute;ltima modificaci&oacute;n 2008/06/13)
 * @package Template
 * @access Public
 */
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

class template {

    var $page = "";

    /**
     *  Constructor of the class
     *
     * @param	string	$archivo	Nombre de la plantilla
     * @return 	none
     */
    function loadTemplate($archivo) {
        $this->page = join("", file($archivo));
    }

    function armarDate() {
        $date = date('l dS \of F Y h:i:s A');
        $this->page = str_replace("[DATE]", $date, $this->page);
    }

    function armarLogoEntidad() {
        if (file_exists('images/logos/logo_cc_' . $_SESSION["generales"]["codigoempresa"] . '.gif')) {
            $img = 'images/logos/logo_cc_' . $_SESSION["generales"]["codigoempresa"] . '.gif';
        } else {
            if (file_exists('images/logos/logo_cc_' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
                $img = 'images/logos/logo_cc_' . $_SESSION["generales"]["codigoempresa"] . '.jpg';
            } else {
                $img = '';
            }
        }
        $this->page = str_replace("[LOGOENTIDAD]", $img, $this->page);
    }

    function armarBanner($txt) {
        $this->page = str_replace("[BANNER]", $txt, $this->page);
    }

    function armarTituloAplicacion($titulo) {
        $this->page = str_replace("[TITULOAPLICACION]", $titulo, $this->page);
    }

    function armarTituloMenu($titulo) {
        $this->page = str_replace("[TITULOMENU]", $titulo, $this->page);
    }

    function armarWidthCuerpo($titulo) {
        $this->page = str_replace("[WIDTHCONTAINER]", $titulo, $this->page);
    }

    function armarTxtHome($home) {
        $this->page = str_replace("[HOME]", $home, $this->page);
    }

    function armarTxtError($txt, $dispositivo = 'movil') {


        if (trim($txt) != '') {
            if ($dispositivo != 'computer') {
                $txtSalida = '<center>';
                $txtSalida .= '<table width="100%" height="100%" cellspacing="1" cellpadding="3" border="0" bgcolor="#1E679A">';
                $txtSalida .= '<tr>';
                $txtSalida .= '<td bgcolor="#ffffcc" align="center">';
                $txtSalida .= '<font face="arial, verdana, helvetica">' . str_replace(array("\r\n", '\r\n', chr(13) . chr(10), "\r", "\n"), "<br>", $txt) . '</font>';
                $txtSalida .= '</td>';
                $txtSalida .= '</tr>';
                $txtSalida .= '</table>';
                $txtSalida .= '</center>';
                $txtSalida .= '<br>';
            } else {
                $txtSalida = '<center>';
                $txtSalida .= strtoupper(str_replace(array("\r\n", chr(13) . chr(10), "\r", "\n"), "<br>", $txt));
                $txtSalida .= '</center>';
            }
        } else {
            $txtSalida = '';
        }

        $txtSalida = str_replace('"', "'", $txtSalida);

        $this->page = str_replace("[TXTERROR]", $txtSalida, $this->page);
    }

    function armarTxtError2($txt) {
        if (trim($txt) != '') {
            $txtSalida = '<center>';
            $txtSalida .= '<table width="95%" cellspacing="1" cellpadding="3" border="0" bgcolor="#1E679A">';
            $txtSalida .= '<tr>';
            $txtSalida .= '<td bgcolor="#ffffcc" align="justify">';
            $txtSalida .= '<font face="arial, verdana, helvetica">' . str_replace(array("\r\n", chr(13) . chr(10), "\r", "\n"), "<br>", $txt) . '</font>';
            $txtSalida .= '</td>';
            $txtSalida .= '</tr>';
            $txtSalida .= '</table>';
            $txtSalida .= '</center>';
            $txtSalida .= '<br>';
        } else {
            $txtSalida = '';
        }
        $this->page = str_replace("[TXTERROR]", $txtSalida, $this->page);
    }

    function armarNombreEntidad($titulo) {
        $this->page = str_replace("[NOMBREENTIDAD]", $titulo, $this->page);
    }

    function armarNombreSistema($titulo) {
        $this->page = str_replace("[NOMBRE_SISTEMA]", $titulo, $this->page);
    }

    function armarNombreSistema1($titulo) {
        $this->page = str_replace("[NOMBRE_SISTEMA1]", $titulo, $this->page);
    }

    function armarNombreCasaSoftware($titulo) {
        $this->page = str_replace("[NOMBRE_CASA_SOFTWARE]", $titulo, $this->page);
    }

    function armarDireccionCasaSoftware($titulo) {
        $this->page = str_replace("[DIRECCION_CASA_SOFTWARE]", $titulo, $this->page);
    }

    function armarCiudadCasaSoftware($titulo) {
        $this->page = str_replace("[CIUDAD_CASA_SOFTWARE]", $titulo, $this->page);
    }

    function armarTelefonoCasaSoftware($titulo) {
        $this->page = str_replace("[TELEFONO_CASA_SOFTWARE]", $titulo, $this->page);
    }

    function armarDeclaracionPrivacidad($titulo) {
        $this->page = str_replace("[DECLARACION_PRIVACIDAD]", $titulo, $this->page);
    }

    function armarSelectAnos($txt) {
        $this->page = str_replace("[SELECT_ANOS]", $txt, $this->page);
    }

    function armarCodigoEmpresa($cod) {
        $this->page = str_replace("CODIGOEMPRESA", $cod, $this->page);
    }

    function armarNombreExpedientes($cod) {
        $this->page = str_replace("[NOMBRE_EXPEDIENTE]", $cod, $this->page);
    }

    function armarIdentificacionExpedientes($cod) {
        $this->page = str_replace("[IDENTIFICACION_EXPEDIENTE]", $cod, $this->page);
    }

    function armarDomicilioExpedientes($cod) {
        $this->page = str_replace("[DOMICILIO_EXPEDIENTE]", $cod, $this->page);
    }

    function armarSesion() {
        $this->page = str_replace("[SESSION]", session_id(), $this->page);
    }

    function armarDhtmlx25($txt = '') {
        $this->page = str_replace("[DHTMLX25]", $txt, $this->page);
    }

    function armarDhtmlx26($txt = '') {
        $this->page = str_replace("[DHTMLX26]", $txt, $this->page);
    }

    function armarDhtmlx36($txt = '') {
        $this->page = str_replace("[DHTMLX36]", $txt, $this->page);
    }

    function armarDhtmlxActual($txt = '') {
        $this->page = str_replace("[DHTMLX_ACTUAL]", $txt, $this->page);
    }

    function armarHeightDivCuerpo($tam) {
        $this->page = str_replace("[HEIGHTDIVCUERPO]", $tam, $this->page);
    }

    function armarContentWidth() {
        if ($_SESSION["generales"]["tipodispositivo"] == 'computer') {
            $txt = "width=400";
        } else {
            $txt = "width=device-width";
        }
        $txt = "width=400";
        $this->page = str_replace("[CONTENT_WIDTH]", $txt, $this->page);
    }

    function armarMostrarSombra($stxt = '') {
        if (trim($stxt) != '') {
            $ts = '<div id="WM_sombra" style="z-index: 100; position: absolute; width: 100%; top: 0pt; left: 0pt; height: 100%; background-image: url(../../html/default/images/pack/overlay.png); visibility: hidden;">';
            $ts .= '<div id="WM_box" style="border: 1px double black; width: 200px; height: 70px; margin-left: auto; margin-right: auto; margin-top: 25%; padding-top: 10px; background-color: rgb(255, 255, 255);">';
            $ts .= '<img id="IMG__Load" height="32" width="32" align="left" name="IMG__Load" src="../../html/default/images/ajax-loader.gif"/>';
            $ts .= '<p style="padding:5px 5px 5px 40px">' . $stxt . '</p></div></div>';
        } else {
            $ts = '';
        }

        $this->page = str_replace("[SOMBRA]", $ts, $this->page);
    }

    function armarMetaRefresh($bol = false, $time = 30) {
        if ($bol) {
            $this->page = str_replace("[META_REFRESH]", '<meta content="' . $time . '" http-equiv="REFRESH"></meta>', $this->page);
        } else {
            $this->page = str_replace("[META_REFRESH]", '', $this->page);
        }
    }

    function armarScriptHeader($script = '') {
        $this->page = str_replace("[SCRIPTHEADER]", $script, $this->page);
    }

    function armarScriptBody($script = '') {
        $this->page = str_replace("[SCRIPTBODY]", $script, $this->page);
    }

    function armarMenuMovil($menu = '') {
        $this->page = str_replace("[MENUMOVIL]", $menu, $this->page);
    }

    function armarBackImage($img = '') {
        $this->page = str_replace("[BACK_IMAGE]", $img, $this->page);
    }

    function armarEncabezado($txt) {
        $this->page = str_replace("[ENCABEZADO]", $txt, $this->page);
    }

    function armarBody($txt = '') {
        $this->page = str_replace("[BODY]", $txt, $this->page);
    }

    function armarBodyParametros($txt = '') {
        $this->page = str_replace("[BODYPARAMETROS]", $txt, $this->page);
    }

    function armarDisplayHeader($boolean = 'si', $width = 1200, $height = 122) {
        if ($boolean == 'si') {
            $this->page = str_replace("[DISPLAYHEDAER]", "block", $this->page);
        } else {
            $this->page = str_replace("[DISPLAYHEADER]", "none", $this->page);
        }
        $this->page = str_replace("[DISPLAYHEADERWIDTH]", $width, $this->page);
        $this->page = str_replace("[DISPLAYHEADERHEIGHT]", $height, $this->page);
    }

    function armarDisplayBarraOpciones($boolean = 'si', $width = 800, $height = 24) {
        if ($boolean == 'si') {
            $this->page = str_replace("[DISPLAYBARRAOPCIONES]", "block", $this->page);
        } else {
            $this->page = str_replace("[DISPLAYBARRAOPCIONES]", "none", $this->page);
        }
        $this->page = str_replace("[DISPLAYBARRAOPCIONESWIDTH]", $width, $this->page);
        $this->page = str_replace("[DISPLAYBARRAOPCIONESHEIGHT]", $height, $this->page);
    }

    function armarDisplayFramePrincipal($boolean = 'si', $width = 800, $height = 450) {
        if ($boolean == 'si') {
            $this->page = str_replace("[DISPLAYFRAMEPRINCIPAL]", "block", $this->page);
        } else {
            $this->page = str_replace("[DISPLAYFRAMEPRINCIPAL]", "none", $this->page);
        }
        $this->page = str_replace("[DISPLAYFRAMEPRINCIPALWIDTH]", $width, $this->page);
        $this->page = str_replace("[DISPLAYFRAMEPRINCIPALHEIGHT]", $height, $this->page);
    }

    function armarDisplayFrameSecundario($boolean = 'si', $width = 800, $height = 450) {
        if ($boolean == 'si') {
            $this->page = str_replace("[DISPLAYFRAMESECUNDARIO]", "block", $this->page);
        } else {
            $this->page = str_replace("[DISPLAYFRAMESECUNDARIO]", "none", $this->page);
        }
        $this->page = str_replace("[DISPLAYFRAMESECUNDARIOWIDTH]", $width, $this->page);
        $this->page = str_replace("[DISPLAYFRAMESECUNDARIOHEIGHT]", $height, $this->page);
    }

    function armarDisplayFOOTER($boolean = 'si', $width = 800, $height = 36) {
        if ($boolean == 'si') {
            $this->page = str_replace("[DISPLAYFOOTER]", "block", $this->page);
        } else {
            $this->page = str_replace("[DISPLAYFOOTER]", "none", $this->page);
        }
        $this->page = str_replace("[DISPLAYFOOTERWIDTH]", $width, $this->page);
    }

    function armarBarraOpciones($txt = '') {
        $txt1 = '';
        if (isset($_SESSION["generales"]["codigoopcion"])) {
            if (trim($_SESSION["generales"]["codigoopcion"]) != '') {
                $txt1 = '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/presentacion/verDescripcionOpcion.php?codigoopcion=' . $_SESSION["generales"]["codigoopcion"] . '" target="_blank"><img src="' . TIPO_HTTP . HTTP_HOST . '/html/default/images/pack/help16.png"/></a>';
            }
        }
        if (isset($_SESSION["generales"]["tipousario"]) && $_SESSION["generales"]["tipousario"] != '' && $_SESSION["generales"]["tipousario"] == '00') {
            $txt1 .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/disparador.php?accion=cargarprincipalindex"><img src="' . TIPO_HTTP . HTTP_HOST . '/html/default/images/pack/home32.png" width="20px"/></a>';
        }
        $this->page = str_replace("[USER1]", $txt . '&nbsp;&nbsp;' . $txt1, $this->page);
    }

    function armarTipoUsuario($txt = '') {
        $this->page = str_replace("[TIPOUSUARIO]", $txt, $this->page);
    }

    function armarIdUsuario($txt = '') {
        $this->page = str_replace("[IDUSUARIO]", $txt, $this->page);
    }

    function armarTareasPendientes($cantipen, $cantinue) {
        if (isset($_SESSION["generales"]["codigousuario"])) {
            if (trim($_SESSION["generales"]["codigousuario"]) != '') {
                if (($cantipen != 0) || ($cantinue != 0)) {
                    $this->page = str_replace("[TARPEN]", "Bandeja de entrada (" . number_format($cantipen, 0) . "/" . number_format($cantinue, 0) . ")", $this->page);
                } else {
                    $this->page = str_replace("[TARPEN]", "", $this->page);
                }
            } else {
                $this->page = str_replace("[TARPEN]", "", $this->page);
            }
        } else {
            $this->page = str_replace("[TARPEN]", "", $this->page);
        }
    }

    function armarFrameSecundarioLateral($txt = '') {
        if (trim($txt) != '') {
            $salida = '<td><div id="frameSecundarioLateral">';
            $salida .= '[USER2]';
            $salida .= '</div></td>';
            $salida = str_replace("[USER2]", $txt, $salida);
        } else {
            $salida = '';
        }
        $this->page = str_replace("[USER2]", $salida, $this->page);
    }

    function armarFrameSecundarioCentral($txt = '') {
        if (trim($txt) != '') {
            $salida = '<td><div id="frameSecundarioCentral">';
            $salida .= '[USER3]';
            $salida .= '</div></td>';
            $salida = str_replace("[USER3]", $txt, $salida);
        } else {
            $salida = '';
        }
        $this->page = str_replace("[USER3]", $salida, $this->page);
    }

    function armarHeader($bool = false, $mostrarenlacesmenu = 'si', $bannersuperior = 'si', $menusuperiorcompleto = 'no') {

        if ($bool) {
            require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
            require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
            require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/persistencia/MasterTablasBasicos.class.php');
            require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');

            //
            $txt = '<input type="hidden" id="session_parameters" value="' . \funcionesGenerales::armarVariablesPantalla() . '">';
            $txt .= '<input type="hidden" id="_sitiogeneral" value="' . HTTP_HOST . '">';
            $txt .= '<input type="hidden" id="_servidorrss" value="' . base64_encode(SERVIDOR_RSS) . '">';
            $txt .= '<input type="hidden" id="_tipohttp" value="' . TIPO_HTTP . '">';
            $txt .= '<input type="hidden" id="_tipomenu" value="' . $_SESSION["generales"]["tipomenu"] . '">';
            if (!isset($_SESSION["generales"]["tipousuario"])) {
                $txt .= '<input type="hidden" id="_tipousuario" value="' . base64_encode('') . '">';
            } else {
                $txt .= '<input type="hidden" id="_tipousuario" value="' . base64_encode($_SESSION["generales"]["tipousuario"]) . '">';
            }
            $txt .= '<input type="hidden" id="_empresaorigen" value="' . base64_encode($_SESSION["generales"]["codigoempresa"]) . '">';

            //
            if ($bannersuperior == 'si') {
                $banner = TIPO_HTTP . HTTP_HOST . '/images/top1200x80-' . $_SESSION["generales"]["codigoempresa"] . '.jpg';
                $txt .= '<img src="' . $banner . '" width="100%">';
            }

            //
            if ($mostrarenlacesmenu == 'si') {
                if ($menusuperiorcompleto == 'si') {
                    if (file_exists(PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $_SESSION["generales"]["codigousuario"] . '.xml')) {
                        $txt .= '<center>';
                        $txt .= '<table width=100%>';
                        $txt .= '<tr>';
                        $txt .= '<div id="menuGen"></div>' . chr(13);
                        $txt .= '</td>';
                        $txt .= '</tr>';
                        $txt .= '</table>';

                        $txt .= '<script type="text/javascript">' . chr(13);
                        $txt .= 'var menuGen, mid;' . chr(13);
                        $txt .= 'var lid = 0;' . chr(13);
                        if (DHTMLX_ACTUAL == '2.6') {
                            $txt .= 'menuGen = new dhtmlXMenuObject("menuGen");' . chr(13);
                            $txt .= 'menuGen.setIconsPath("' . $_SESSION["generales"]["pathabsoluto"] . '/html/default/images/");' . chr(13);
                            $txt .= 'menuGen.attachEvent("onClick", menuClickGeneral);' . chr(13);
                            $txt .= 'menuGen.loadXML("' . TIPO_HTTP . HTTP_HOST . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $_SESSION["generales"]["codigousuario"] . '.xml");' . chr(13);
                        } else {
                            $txt .= 'menuGen = new dhtmlXMenuObject({
				parent: "menuGen",
				xml: "' . TIPO_HTTP . HTTP_HOST . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $_SESSION["generales"]["codigousuario"] . '.xml",
                            });';
                            $txt .= 'menuGen.attachEvent("onClick", menuClickGeneral);' . chr(13);
                        }
                        $txt .= '</script>' . chr(13);
                        $txt .= "</center>";
                    }
                }
                if ($menusuperiorcompleto == 'no') {
                    if (file_exists(PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $_SESSION["generales"]["codigousuario"] . '-res.xml')) {
                        $txt .= '<center>';
                        $txt .= '<table width=50%>';
                        $txt .= '<tr>';
                        $txt .= '<div id="menuGen"></div>' . chr(13);
                        $txt .= '</td>';
                        $txt .= '</tr>';
                        $txt .= '</table>';

                        $txt .= '<script type="text/javascript">' . chr(13);
                        $txt .= 'var menuGen, mid;' . chr(13);
                        $txt .= 'var lid = 0;' . chr(13);
                        if (DHTMLX_ACTUAL == '2.6') {
                            $txt .= 'menuGen = new dhtmlXMenuObject("menuGen");' . chr(13);
                            $txt .= 'menuGen.setIconsPath("' . $_SESSION["generales"]["pathabsoluto"] . '/html/default/images/");' . chr(13);
                            $txt .= 'menuGen.attachEvent("onClick", menuClickGeneral);' . chr(13);
                            $txt .= 'menuGen.loadXML("' . TIPO_HTTP . HTTP_HOST . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $_SESSION["generales"]["codigousuario"] . '-res.xml");' . chr(13);
                        } else {
                            $txt .= 'menuGen = new dhtmlXMenuObject({
				parent: "menuGen",
				xml: "' . TIPO_HTTP . HTTP_HOST . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $_SESSION["generales"]["codigousuario"] . '-res.xml",
                            });';
                            $txt .= 'menuGen.attachEvent("onClick", menuClickGeneral);' . chr(13);
                        }
                        $txt .= '</script>' . chr(13);
                        $txt .= "</center>";
                    }
                }
            }
            $this->page = str_replace("[USERTOP]", $txt, $this->page);
        } else {
            $this->page = str_replace("[USERTOP]", '', $this->page);
        }
    }

    function armarGoogleAnalitics() {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        $txt = "";
        $uid = '';
        if (!defined('UID_GOOGLE_ANALYTICS')) {
            define('UID_GOOGLE_ANALYTICS', '');
        }
        if (trim(UID_GOOGLE_ANALYTICS) != '') {
            $txt = "<script>
                    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
                    ga('create', '" . UID_GOOGLE_ANALYTICS . "', 'auto');
                    ga('send', 'pageview');
                    </script>";
        }
        $this->page = str_replace("[SCRIPTGOOGLEANALITICS]", $txt, $this->page);
    }

    function armarFooter($bool = false) {
        if ($bool) {
            require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
            require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
            $txt = "<center>";
            $txt .= "<div>" . 'Derechos Reservados' . "  <a href='http://" . WEB_CASA_SOFTWARE . "' target='_blank'>" . NOMBRE_CASA_SOFTWARE . "</a>, PBX: " . TELEFONO_CASA_SOFTWARE . ", " . \funcionesGenerales::utf8_decode(CIUDAD_CASA_SOFTWARE) . ", " . PAIS_CASA_SOFTWARE . ", Copyright " . date("Y") . "</div>";
            $txt .= "<div>" . 'Licenciado para' . " <strong>" . RAZONSOCIAL . "</strong>, " . DIRECCION1 . " - " . DIRECCION2 . ", PBX: " . PBX . ", " . 'Contacto ' . ": " . EMAIL_ATENCION_USUARIOS . ", <a href='../../librerias/presentacion/mostrarPantallas.php?pantalla=declaracion.privacidad' target=_blank>" . 'Declaración de privacidad' . "</a></div>";
            $txt .= "</center>";
            $this->page = str_replace("[USER4]", $txt, $this->page);
        } else {
            $this->page = str_replace("[USER4]", '', $this->page);
        }
    }

    function armarFooterRse($txt = '') {
        $this->page = str_replace("[USER4]", $txt, $this->page);
    }

    function armarEstilo($txt = '') {
        if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/html/default/estilo' . $txt . 'css')) {
            $txt = 'basico';
        }
        $this->page = str_replace("[CODIGO_ESTILO]", $txt, $this->page);
    }

    function armarColor($txt = '') {
        $this->page = str_replace("[COLOR]", $txt, $this->page);
    }

    function armarHttpHost($txt) {
        $this->page = str_replace("[HTTPHOST]", $txt, $this->page);
        $this->page = str_replace("[HTTP_HOST]", $txt, $this->page);
    }

    function armarUrlSite($txt) {
        $this->page = str_replace("[URLSITE]", $txt, $this->page);
    }

    function armarTipoHttp($txt) {
        $this->page = str_replace("[TIPOHTTP]", $txt, $this->page);
        $this->page = str_replace("[TIPO_HTTP]", $txt, $this->page);
    }

    function armarServidorRss($txt) {
        $this->page = str_replace("[SERVIDORRSS]", $txt, $this->page);
    }

    function armarTipoMenu($txt) {
        $this->page = str_replace("[TIPOMENU]", $txt, $this->page);
    }

    function armarPathAbsolutoSitio($txt) {
        $this->page = str_replace("[PATHABSOLUTOSITIO]", $txt, $this->page);
    }

    function armarSessionId($txt) {
        $this->page = str_replace("[SESSIONID]", $txt, $this->page);
    }

    function armarCodigoEntidad($txt) {
        $this->page = str_replace("[NUMEROENTIDAD]", $txt, $this->page);
    }

    function armarCodigoUsuario($txt) {
        $this->page = str_replace("[CODIGOUSUARIO]", $txt, $this->page);
    }

    function armarCodigoEntidad1($txt) {
        $this->page = str_replace("[CODIGOENTIDAD]", $txt, $this->page);
    }

    function armarSitioWeb($txt) {
        $this->page = str_replace("[SITIOWEB]", $txt, $this->page);
    }

    function armarString($txt) {
        $this->page = str_replace("[STRING]", $txt, $this->page);
    }

    function armarEstadoExpediente($txt) {
        $this->page = str_replace("[ESTADO_EXPEDIENTE]", $txt, $this->page);
    }

    function armarCuerpo($txt) {
        $this->page = str_replace("[CUERPO]", $txt, $this->page);
    }

    function armarPeriodo($txt) {
        $this->page = str_replace("[PERIODO]", $txt, $this->page);
    }

    function armarEmailAtencionUsuarios($txt) {
        $this->page = str_replace("[EMAILATENCIONUSUARIOS]", $txt, $this->page);
    }

    function armarTelefonoAtencionUsuarios($txt) {
        $this->page = str_replace("[TELEFONOATENCIONUSUARIOS]", $txt, $this->page);
    }

    function armarTipoDispositivo($txt) {
        $this->page = str_replace("[TIPODISPOSITIVO]", $txt, $this->page);
    }

    function armarTiempoPromedioProponentes($txt) {
        $this->page = str_replace("[TIEMPOPROMEDIOPROPONENTES]", $txt, $this->page);
    }

    function armarCuerpoInicial($txt) {
        $this->page = str_replace("[CUERPOINICIAL]", $txt, $this->page);
    }

    function armarCuerpoFinal($txt) {
        $this->page = str_replace("[CUERPOFINAL]", $txt, $this->page);
    }

    function armarListaEmpresas($txt) {
        $this->page = str_replace("[LISTAEMPRESAS]", $txt, $this->page);
    }

    function armarDHTMLX($txt) {
        $this->page = str_replace("[DHTMLX]", $txt, $this->page);
    }

    function armarDHTMLXVERSION($txt) {
        $this->page = str_replace("[DHTMLXVERSION]", 'dhtmlx' . DHTMLX_ACTUAL, $this->page);
    }

    function armarWWWENLACE($txt) {
        $this->page = str_replace("[WWW_ENLACE]", $txt . DHTMLX_ACTUAL, $this->page);
    }

    function armarScriptEmergente($txt) {
        $string = '';
        if (trim($txt) != '') {
            // $txt = str_replace(array("&aacute;", "&eacute;", "&iacute;", "&oacute;", "&uacute;"), array('&aacute;', '&eacute;', '&iacute;', '&oacute;', 'u'), $txt);            
            if ($_SESSION["generales"]["tipodispositivo"] == 'computer') {
                $txt = utf8_encode($txt);
            }
            $string .= "<script>" . chr(13);
            $string .= "alert ('" . $txt . "')" . chr(13);
            $string .= "</script>" . chr(13);
        }
        $this->page = str_replace("[SCRIPTEMERGENTE]", $string, $this->page);
    }

    function armarTituloOpciones($txt) {
        $this->page = str_replace("[TITULOOPCIONES]", $txt, $this->page);
    }

    function armarTituloBuscar($txt) {
        $this->page = str_replace("[TITULOBUSCAR]", $txt, $this->page);
    }

    function armarTituloVersion($txt) {
        $this->page = str_replace("[TITULOVERSION]", $txt, $this->page);
    }

    function armarTituloPeriodo($txt) {
        $this->page = str_replace("[TITULOPERIODO]", $txt, $this->page);
    }

    function armarTituloAccesoRapido($txt) {
        $this->page = str_replace("[TITULOACCESORAPIDO]", $txt, $this->page);
    }

    function armarTituloSalir($txt) {
        $this->page = str_replace("[TITULOSALIR]", $txt, $this->page);
    }

    function armarTituloUsuario($txt) {
        $this->page = str_replace("[TITULOUSUARIO]", $txt, $this->page);
    }

    function armarTituloUsuarioPublico($txt) {
        $this->page = str_replace("[TITULOUSUARIOPUBLICO]", $txt, $this->page);
    }

    function armarTituloPalabrasClave($txt) {
        $this->page = str_replace("[TITULOPALABRASCLAVE]", $txt, $this->page);
    }

    function armarTituloSistema($txt) {
        $this->page = str_replace("[TITULOSISTEMA]", $txt, $this->page);
    }

    function mostrar() {
        /*
          header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
          header('Cache-Control: no-store, no-cache, must-revalidate');
          header('Cache-Control: post-check=0, pre-check=0', FALSE);
          header('Pragma: no-cache');
         */
        echo $this->page;
    }

}

?>
