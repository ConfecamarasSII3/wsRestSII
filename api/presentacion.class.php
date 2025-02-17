<?php

/**
 * Clase para el manejo de la capa de presentacion
 *
 * Clase que almacena las funciones que se utilizan para el armado de pantallas y formularios
 *
 * @author Jose Ivan Nieto Tabares
 * @author //virtual.confecamaras.org.co
 * @copyright Derechos reservados
 * @version Versi&oacute;n 1.0 (&uacute;ltima modificaci&oacute;n 2009/11/01)
 * @package Clases
 * @access Public
 */
error_reporting(E_ALL);

class presentacionBootstrap {

    public $borderinputrequired = ' border-success';
    public $borderinputrequireddanger = ' border-danger';

    function __construct() {
        
    }

    function progressBar($pasos = array(), $paso = '') {
        if (empty($pasos)) {
            return "";
        }
        $string = '
            <div class="mainpb">
                <div class="containerpb">
                    <ul class="progressbarpb">';
        foreach ($pasos as $p) {
            if ($p == $paso) {
                $string .= '<li class="active">' . $p . '</li>';
            } else {
                $string .= '<li>' . $p . '</li>';
            }
        }
        $string .= '
                    </ul>
                </div>
            </div>   
        ';
        return $string;
    }

    function armarBotonMovil($text, $link, $width = 280, $target = '_self') {

        $string = '<center>';
        if ($width == 0) {
            $string .= '<table width="100%" border="0">' . chr(13);
        } else {
            $string .= '<table width="' . $width . 'px" border="0">' . chr(13);
        }

        $string .= '<tr width="100%">' . chr(13);
        $string .= '<td align="center">' . chr(13);
        $string .= '<a href="' . $link . '" target="' . $target . '">' . chr(13);
        $string .= '<input type="button" value="' . $text . '">' . chr(13);
        $string .= '</a>' . chr(13);
        $string .= '</td>' . chr(13);
        $string .= '</tr>' . chr(13);
        $string .= '</table>' . chr(13);
        $string .= '</center>';
        return $string;
    }

    function armarBotonSubmitMovil($text, $width = 280) {
        // Evalua el tipo de botones a mostrar

        $string = '<center>';
        if ($width == 0) {
            $string .= '<table width="100%" border="0">' . chr(13);
        } else {
            $string .= '<table width="' . $width . 'px" border="0">' . chr(13);
        }

        $string .= '<tr width="100%">' . chr(13);
        $string .= '<td align="center">' . chr(13);
        $string .= '<input type="submit" id="submit" name="submit" value="' . $text . '">' . chr(13);
        $string .= '</td>' . chr(13);
        $string .= '</tr>' . chr(13);
        $string .= '</table>' . chr(13);
        $string .= '</center>';
        return $string;
    }

    function armarBotonMovilJavascript($text, $link, $width = 280, $target = '_self') {
        // Evalua el tipo de botones a mostrar

        $string = '';
        if ($width == 0) {
            $string .= '<table width="100%" border="0" align="center">' . chr(13);
        } else {
            $string .= '<table width="' . $width . 'px" border="0" align="center">' . chr(13);
        }

        $string .= '<tr width="100%">' . chr(13);
        $string .= '<td align="center">' . chr(13);
        $string .= '<a href="#" onclick="' . $link . '" target="' . $target . '">' . chr(13);
        $string .= '<input type="button" value="' . $text . '">' . chr(13);
        $string .= '</a>' . chr(13);
        $string .= '</td>' . chr(13);
        $string .= '</tr>' . chr(13);
        $string .= '</table>' . chr(13);
        return $string;
    }

    /**
     * 
     * @param type $sistema
     * @param type $opcion
     * @return string
     */
    function armarEncabezado($sistema = '', $opcion = '', $mostrarenc = 'si', $mostrarnombreempresa = 'si', $bannerpersonalizado = '') {
        $string = '';
        $mostrar = 'si';
        if (isset($_SESSION["generales"]["ocultarencabezados"]) && $_SESSION["generales"]["ocultarencabezados"] == 'si') {
            $mostrar = 'no';
        } else {
            if (isset($_SESSION["generales"]["mostrarencabezados"]) && $_SESSION["generales"]["mostrarencabezados"] == 'no') {
                $mostrar = 'no';
            } else {
                if (!isset($_SESSION["generales"]["mostrarencabezados"]) && !isset($_SESSION["generales"]["ocultarencabezados"])) {
                    $mostrar = $mostrarenc;
                }
            }
        }

        if (isset($_SESSION["generales"]["cabecera"]) && $_SESSION["generales"]["cabecera"] == 'si') {
            $mostrar = 'si';
            $_SESSION["generales"]["linkretornoindex"] = 'no';
        }

        // En dispositivos móviles siempre se muestran los encabezados
        $disp = \funcionesGenerales::retornarDispositivo();
        if ($_SESSION["generales"]["tipodispositivo"] != 'computer') {
            $mostrar = 'si';
        }

        if ($mostrar == 'no') {
            if (isset($_SESSION["generales"]["sistemaorigen"]) && $_SESSION["generales"]["sistemaorigen"] == 'siiiconos') {
                $mostrar = 'si';
            }
        }

        // Control de link retorno index cuando se trate de dispositivos móviles y usuarios anonimos
        if ($_SESSION["generales"]["tipodispositivo"] != 'computer') {
            if (!isset($_SESSION["generales"]["tipousuariocontrol"]) || $_SESSION["generales"]["tipousuariocontrol"] == 'usuarioanonimo') {
                $_SESSION["generales"]["linkretornoindex"] = 'no';
            } else {
                if (!isset($_SESSION["generales"]["linkretornoindex"]) || $_SESSION["generales"]["linkretornoindex"] == '') {
                    $_SESSION["generales"]["linkretornoindex"] = 'si';
                }
            }
        }

        if ($mostrar == 'si') {

            return $this->armarEncabezadoNuevo();
        }
        return $string;
    }

    /**
     * 
     * @param type $sistema
     * @param type $opcion
     * @param type $btnsalir
     * @return string
     */
    function armarEncabezadoSimple($sistema = '', $opcion = '', $btnsalir = 'si') {

        $string = '';
        $string = '<div>';
        $string .= '<nav class="navbar navbar-expand-lg navbar-dark bg-dark">';
        $string .= '<a class="navbar-brand">
                            <img src="[TIPOHTTP][HTTPHOST]/' . LOGO_SISTEMA . '" width="150" height="60" alt="">
                        </a>';
        $string .= '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav">';
        $string .= '        <li class="nav-item">
                                <a href="[TIPOHTTP][HTTPHOST]/scripts/mostrarPantallas.php?pantalla=declaracion.privacidad&session_parameters=' . \funcionesGenerales::armarVariablesPantalla() . '" target="_blank">Declaración de privacidad</a><br>
                                    <a href="[TIPOHTTP][HTTPHOST]/scripts/mostrarPantallas.php?pantalla=politica.tratamiento.datos.personales&session_parameters=' . \funcionesGenerales::armarVariablesPantalla() . '" target="_blank">Política Tratamiento Información.</a>
                            </li>';
        $string .= '        <li class="nav-item"><a class="nav-link">';
        if (!isset($_SESSION["generales"]["perfilusuariocontrol"])) {
            $_SESSION["generales"]["perfilusuariocontrol"] = '';
        }
        if (isset($_SESSION["generales"]["tipousuario"]) && trim($_SESSION["generales"]["tipousuario"]) != '') {
            $string .= '    <li class="nav-item">';
            $string .= '        <strong>Em: </strong>' . $_SESSION["generales"]["emailusuariocontrol"] . '<br>';
            $string .= '        <strong>Tp: </strong>' . $_SESSION["generales"]["tipousuariocontrol"] . '<br>';
            if ($_SESSION["generales"]["tipousuariocontrol"] == 'usuarioverificado') {
                $string .= $_SESSION["generales"]["nombreusuariocontrol"];
            } else {
                $string .= '    <strong>Pf: </strong>' . $_SESSION["generales"]["tipousuario"] . '&nbsp';
                $string .= '    <strong>Cd: </strong>' . $_SESSION["generales"]["codigousuario"];
            }
            $string .= '    </li>';
        }

        $string .= '        <li class="nav-item">&nbsp;&nbsp;&nbsp;</li>';

        $string .= '        <li class="nav-item">';
        $string .= '            <strong>Fecha: </strong>' . date("Y-m-d") . '<br>';
        $string .= '            <strong>Hora: </strong>' . date("H:i:s") . '<br>';
        $string .= str_replace(array('CAMARA DE COMERCIO DEL', 'CAMARA DE COMERCIO DE', 'CAMARA DE COMERCIO'), "C.C.", RAZONSOCIAL);
        $string .= '        </li>';

        if (isset($_SESSION["generales"]["codigousuario"]) && $_SESSION["generales"]["codigousuario"] != '') {
            $string .= '    <li class="nav-item">';
            $string .= '        &nbsp;&nbsp;';
            $string .= '    </li>';
            if ($btnsalir == 'si') {
                $string .= '<li class="nav-item">';
                $string .= '    <center>Salir<br><a href="' . TIPO_HTTP . HTTP_HOST . '/disparador.php?accion=logout"><i class="fas fa-door-open fa-lg"></i></a></center>';
                $string .= '</li>';
            }
        }
        $string .= '</ul>                        
                    </div>                    
                    </nav>';
        $string .= '</div>';
        return $string;
    }

    function armarEncabezadoNuevo($sistema = '', $opcion = '', $btnsalir = 'si') {

        //
        $httphost = $_SERVER["SERVER_NAME"];
        $tipohttp = 'http://';
        if (isset($_SERVER['HTTPS'])) {
            if ($_SERVER['HTTPS'] == "on") {
                $tipohttp = 'https://';
            }
        }

        //
        if (isset($_SESSION["generales"]["llamadodesdemenulateral"]) &&
                $_SESSION["generales"]["llamadodesdemenulateral"] == 'si') {
            return "";
        }

        //
        if ($_SESSION["generales"]["codigousuario"] != 'USUPUBXX') {
            $txtuser = $_SESSION["generales"]["codigousuario"];
        } else {
            $txtuser = $_SESSION["generales"]["emailusuariocontrol"];
        }
        $string = '
            <nav class="navbar navbar-expand-lg navbar-light">
                <img src="' . $tipohttp . $httphost . '/LogoSii.png" width="90" height="40" alt="">&nbsp;&nbsp;';
        if (!defined('RAZONSOCIAL') || RAZONSOCIAL == '') {
            $string .= '<a class="navbar-brand" href="#">SII-CORE</a>';
        } else {
            $string .= '<a class="navbar-brand" href="#">SII-CORE - ' . str_replace(array('CAMARA DE COMERCIO DEL', 'CAMARA DE COMERCIO DE', 'CAMARA DE COMERCIO', 'CC'), "C.C.", RAZONSOCIAL) . '</a>';
        }
        $string .= '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mr-auto">
                        <!--<li class="nav-item active">
                            <a class="nav-link" href="#">Inicio<span class="sr-only">(current)</span></a>
                        </li>-->';
        if ($_SESSION["generales"]["codigoempresa"] != '' && $_SESSION["generales"]["codigoempresa"] != '00') {
            $string .= '
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Documentación
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="[TIPOHTTP][HTTPHOST]/scripts/mostrarPantallas.php?pantalla=declaracion.privacidad&session_parameters=' . \funcionesGenerales::armarVariablesPantalla() . '" target="_blank">Declaración de privacidad</a>
                                <a class="dropdown-item" href="[TIPOHTTP][HTTPHOST]/scripts/mostrarPantallas.php?pantalla=politica.tratamiento.datos.personales&session_parameters=' . \funcionesGenerales::armarVariablesPantalla() . '" target="_blank">Política Tratamiento Información.</a>
                            </div>
                        </li>';
        }
        if (!isset($_SESSION["generales"]["mostrarbtnusuario"]) || $_SESSION["generales"]["mostrarbtnusuario"] == 'si' || $_SESSION["generales"]["mostrarbtnusuario"] == '') {
            $string .= '
                    </ul>
                    <span class="navbar-text">' . $txtuser . '&nbsp;                        
                        <img src="[TIPOHTTP][HTTPHOST]/images/icon-user.jpg" width="30">';
        }
        if (isset($_SESSION["generales"]["mostrarbtnsalir"]) && $_SESSION["generales"]["mostrarbtnsalir"] == 'no') {
            $btnsalir = 'no';
        }
        if ($btnsalir == 'si') {
            $string .= '<a href="' . $tipohttp . $httphost . '/disparador.php?accion=logout"><i class="fas fa-door-open fa-lg"></i></a>;';
        }
        $string .= '</span>                    
                </div>
            </nav>';
        if (trim($sistema) != '') {
            $string .= $this->armarLineaTextoInformativa($sistema, 'center', 'h3', '', '', '', '', '', $opcion) .
                    '<hr>';
        }
        return $string;
    }

    function armarEncabezadoPagoElectronico($empresax = '') {

        //
        $httphost = $_SERVER["SERVER_NAME"];
        $tipohttp = 'http://';
        if (isset($_SERVER['HTTPS'])) {
            if ($_SERVER['HTTPS'] == "on") {
                $tipohttp = 'https://';
            }
        }

        /*
        if (isset($_SESSION["generales"]["llamadodesdemenulateral"]) &&
                $_SESSION["generales"]["llamadodesdemenulateral"] == 'si') {
            return "";
        }
        */
        
        //
        if (isset($_SESSION["generales"]["codigousuario"]) && $_SESSION["generales"]["codigousuario"] != 'USUPUBXX') {
            $txtuser = $_SESSION["generales"]["codigousuario"];
        } else {
            if (isset($_SESSION["generales"]["emailusuariocontrol"])) {
                $txtuser = $_SESSION["generales"]["emailusuariocontrol"];
            } else {
                $txtuser = 'SIN USUARIO';
            }
        }
        $string = '
            <nav class="navbar navbar-expand-lg navbar-light">
                <img src="' . $tipohttp . $httphost . '/LogoSii.png" width="90" alt="">' . $empresax . '</nav>';

        $string = '<div class="form-group">
                        <div class="row">
                            <div class="col-sm-4">
                                <img src="' . $tipohttp . $httphost . '/LogoSii.png" width="90" alt="">
                            </div>  
                            <div class="col-sm-8">' .
                $empresax .
                '</div>
                        </div>
                   </div>';
        return $string;
    }

    function armarFormulario($form, $method, $action, $javascript = '', $automcplete = '') {
        $txt = $this->abrirFormulario($form, $method, $action, $javascript);
        return $txt;
    }

    /**
     * 
     * @param type $id
     * @param type $method
     * @param type $action
     * @param type $function
     * @return string
     */
    function abrirFormulario($id, $method = '', $action = '', $function = '') {
        if ($function == '') {
            $string = '<form role="form" id="' . $id . '" name="' . $id . '" method="' . $method . '" enctype="multipart/form-data" action="' . $action . '">';
        } else {
            $string = '<form role="form" id="' . $id . '" name="' . $id . '" method="' . $method . '" enctype="multipart/form-data" action="' . $action . '" onsubmit="return ' . $function . ';">';
        }
        return $string;
    }

    function abrirFormularioSimple($id, $method = '', $action = '', $function = '') {
        if ($function == '') {
            $string = '<form role="form" id="' . $id . '" name="' . $id . '" method="' . $method . '" action="' . $action . '">';
        } else {
            $string = '<form role="form" id="' . $id . '" name="' . $id . '" method="' . $method . '" action="' . $action . '" onsubmit="return ' . $function . ';">';
        }
        return $string;
    }

    function armarSubmitDinamico($width, $img, $tooltip = '', $botones = '') {
        $txt = $this->armarBotonDinamico('submit', $img, '');
        return $txt;
    }

    /**
     * 
     * @param type $btnTipo
     * @param type $btnImagen
     * @param type $btnEnlace
     * @return string
     */
    function armarBotonDinamico($btnTipo, $btnImagen, $btnEnlace = '', $help = '') {
        $string = '';
        $string .= '<div align="center">';
        $string .= '<div class="btn-group">';
        switch ($btnTipo) {
            case 'submit':
                $string .= '<button type="submit" class="btn btn-primary btn-md" id="submit" name="submit">' . $btnImagen . '</button>' . $help;
                break;
            case 'href':
                $string .= '<a href="' . $btnEnlace . '"><button type="button" class="btn btn-primary btn-md">' . $btnImagen . '</button></a>' . $help;
                break;
            case 'hrefblank':
                $string .= '<a href="' . $btnEnlace . '" target="_blank"><button type="button" class="btn btn-primary btn-md">' . $btnImagen . '</button></a>' . $help;
                break;
            case 'javascript':
                $string .= '<a href="javascript:' . $btnEnlace . '"><button type="button" class="btn btn-primary btn-md">' . $btnImagen . '</button></a>' . $help;
                break;
            case 'javascriptblank':
                $string .= '<a href="javascript:' . $btnEnlace . '" target="_blank"><button type="button" class="btn btn-primary btn-md">' . $btnImagen . '</button></a>' . $help;
                break;
            case 'javascriptsearch':
                $string .= '<a href="javascript:' . $btnEnlace . '"><button type="button" class="btn btn-primary btn-md">Buscar</button></a>' . $help;
                break;
        }
        $string .= '</div>';
        $string .= '</div>';
        return $string;
    }

    function armarBotonDinamicoMd($btnTipo = '', $btnImagen = '', $btnEnlace = '', $help = '', $md = '') {
        $string = '';
        $string .= '<div class="form-group col-md-' . $md . '  col-centered">';
        $string .= '<div class="btn-group">';
        switch ($btnTipo) {
            case 'submit':
                $string .= '<button type="submit" class="btn btn-primary btn-md" id="submit" name="submit">' . $btnImagen . '</button>' . $help;
                break;
            case 'href':
                $string .= '<a href="' . $btnEnlace . '"><button type="button" class="btn btn-primary btn-md">' . $btnImagen . '</button></a>' . $help;
                break;
            case 'hrefblank':
                $string .= '<a href="' . $btnEnlace . '" target="_blank"><button type="button" class="btn btn-primary btn-md">' . $btnImagen . '</button></a>' . $help;
                break;
            case 'javascript':
                $string .= '<a href="javascript:' . $btnEnlace . '"><button type="button" class="btn btn-primary btn-md">' . $btnImagen . '</button></a>' . $help;
                break;
            case 'javascriptblank':
                $string .= '<a href="javascript:' . $btnEnlace . '" target="_blank"><button type="button" class="btn btn-primary btn-md">' . $btnImagen . '</button></a>' . $help;
                break;
            case 'javascriptsearch':
                $string .= '<a href="javascript:' . $btnEnlace . '"><button type="button" class="btn btn-primary btn-md">Buscar</button></a>' . $help;
                break;
        }
        $string .= '</div>';
        $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $arrBtnTipo
     * @param type $arrBtnImagen
     * @param type $arrBtnEnlace
     * @return string
     */
    function armarBotonesDinamicos($arrBtnTipo, $arrBtnImagen, $arrBtnEnlace) {
        $string = '';
        $string .= '<div align="center">';
        $string .= '<div class="btn-group flex-wrap">';
        $i = -1;
        foreach ($arrBtnTipo as $b) {
            $i++;
            switch ($arrBtnTipo[$i]) {
                case 'submit':
                    $string .= '<a href=""><button type="submit" class="btn btn-primary btn-md" id="submit" name="submit">' . $arrBtnImagen[$i] . '</button></a>';
                    break;
                case 'href':
                    $string .= '<a href="' . $arrBtnEnlace[$i] . '"><button type="button" class="btn btn-primary btn-md">' . $arrBtnImagen[$i] . '</button></a>';
                    break;
                case 'hrefblank':
                    $string .= '<a href="' . $arrBtnEnlace[$i] . '" target="_blank"><button type="button" class="btn btn-primary btn-md">' . $arrBtnImagen[$i] . '</button></a>';
                    break;
                case 'javascript':
                    $string .= '<a href="javascript:' . $arrBtnEnlace[$i] . '"><button type="button" class="btn btn-primary btn-md">' . $arrBtnImagen[$i] . '</button></a>';
                    break;
                case 'javascriptblank':
                    $string .= '<a href="javascript:' . $arrBtnEnlace[$i] . '" target="_blank"><button type="button" class="btn btn-primary btn-md">' . $arrBtnImagen[$i] . '</button></a>';
                    break;
                case 'javascriptsearch':
                    $string .= '<a href="javascript:' . $arrBtnEnlace[$i] . '"><button type="button" class="btn btn-primary btn-md">Buscar</button></a>';
                    break;
            }
            $string .= '&nbsp;&nbsp;';
        }
        $string .= '</div>';
        $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $id
     * @param type $size
     * @param type $maxlength
     * @return string
     */
    function armarCampoCaptcha($txtCampo, $id, $size, $maxlength) {
        $string = '<div align="center">';
        $string .= '<label class="text-left" for="' . $id . '">' . $txtCampo . '</label><br>';
        $string .= '<img src="' . TIPO_HTTP . HTTP_HOST . '/components/secureimage/securimage_show.php" id="captcha" /><br><br>';
        $string .= '<input type="text" class="form-control" id="' . $id . '" name="' . $id . '" size="' . $size . '" maxlength="' . $maxlength . '" value="" required>';
        $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $value
     * @param type $help
     * @return string
     */
    function armarCampoCheckbox($txtCampo = '', $obli = 'si', $id = '', $value = '', $help = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }
        if ($value == '1' || $value == 'S' || $value == 'SI' || $value == 's' || $value == 'si') {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        $string = '
                <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input' . $formcontrol . '" id="' . $id . '" name="' . $id . '" value="1" ' . $checked . ' ' . $required . '>
                <label class="custom-control-label" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . '</label>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $value
     * @param type $javascript
     * @param type $help
     * @return string
     */
    function armarCampoCheckboxOnChange($txtCampo = '', $obli = 'si', $id = '', $value = '', $javascript = '', $help = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }
        if ($value == '1' || $value == 'S' || $value == 'SI' || $value == 's' || $value == 'si') {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        $string = '
                <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input' . $formcontrol . '" id="' . $id . '" name="' . $id . '" onchange="javascript:' . $javascript . ';" value="1" ' . $checked . ' ' . $required . '>
                <label class="custom-control-label" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . '</label>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $md
     * @param type $value
     * @param type $help
     * @param type $align
     * @return string
     */
    function armarCampoCheckboxMd($txtCampo = '', $obli = 'si', $id = '', $md = '3', $value = '', $help = '', $align = 'left') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }
        if ($value == '1' || $value == 'S' || $value == 'SI' || $value == 's' || $value == 'si') {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        $string = '';
        $ialign = '';
        if ($align == 'center') {
            $ialign = 'text-center';
        }
        $string .= '
                <div class="custom-control custom-checkbox  col-md-' . $md . ' ' . $ialign . '">
                <input type="checkbox" class="custom-control-input' . $formcontrol . '" id="' . $id . '" name="' . $id . '" value="1" ' . $checked . ' ' . $required . '>
                <label class="custom-control-label" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . '</label>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $md
     * @param type $value
     * @param type $javascript
     * @param type $help
     * @return string
     */
    function armarCampoCheckboxOnChangeMd($txtCampo = '', $obli = 'si', $id = '', $md = '3', $value = '', $javascript = '', $help = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }
        if ($value == '1' || $value == 'S' || $value == 'SI' || $value == 's' || $value == 'si') {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        $string = '
                <div class="custom-control custom-checkbox  col-md-' . $md . '"">
                <input type="checkbox" class="custom-control-input' . $formcontrol . '" id="' . $id . '" name="' . $id . '" onchange="javascript:' . $javascript . ';" value="1" ' . $checked . ' ' . $required . '>
                <label class="custom-control-label" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . '</label>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $value
     * @param type $hold
     * @param type $help
     * @param type $externalhelp
     * @return string
     */
    function armarCampoDate($txtCampo = '', $obli = 'si', $id = '', $value = '', $hold = '', $help = '', $externalhelp = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }

        $string = '
                <div class="form-group">';
        if ($txtCampo != '') {
            $string .= '
                <label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>';
        }
        $string .= '<input type="date" class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" value="' . \funcionesGenerales::mostrarFecha($value) . '" ' . $required . '>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $value
     * @param type $hold
     * @param type $externalhelp
     * @param type $izq
     * @param type $der
     * @return string
     */
    function armarCampoDate2($txtCampo = '', $obli = 'si', $id = '', $value = '', $hold = '', $externalhelp = '', $izq = 3, $der = 9) {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }

        $string = '<div class="form-group">
                        <div class="row">
                            <div class="col-sm-' . $izq . '">
                                <label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>                            
                            </div>
                            <div class="col-sm-' . $der . '">
                                <input type="date" class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" value="' . \funcionesGenerales::mostrarFecha($value) . '" ' . $required . '>
                            </div>
                        </div>
                    </div>';

        return $string;
    }

    function armarCampoVacioMd($md = 6, $value = '') {

        //
        $string = '
                <div class="form-group col-md-' . $md . '  col-centered">
                <label class="text-left">&nbsp;</label>';
        $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $md
     * @param type $value
     * @param type $hold
     * @param type $help
     * @param type $externalhelp
     * @param type $onchange
     * @param type $javascript - Para asignación
     * @param type $javascript1 - Para borrado
     * @return string
     */
    function armarCampoDateMd($txtCampo = '', $obli = 'si', $id = '', $md = 6, $value = '', $hold = '', $help = '', $externalhelp = '', $onchange = '', $javascript = '', $javascript1 = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        //
        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm" data-toggle="tooltip" data-placement="top" title="Ayuda"></i></a>';
        }

        //
        $tonchange = '';
        if ($onchange != '') {
            $tonchange = ' onchange="javascript:' . $onchange . ';"';
        }
        $jv = '';
        if ($javascript != '') {
            $jv .= ' <a href="javascript:' . $javascript . ';"><i class="fa fa-question-circle fa-sm"></i></a>&nbsp;&nbsp;';
        }
        $jv1 = '';
        if ($javascript1 != '') {
            $jv1 .= ' <a href="javascript:' . $javascript1 . ';"><i class="fas fa-eraser"></i></a>&nbsp;&nbsp;';
        }
        //
        $string = '
                <div class="form-group col-md-' . $md . '  col-centered">';
        $string .= '
                <label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . $jv . $jv1 . '</label>';
        $string .= '
                <input type="date" class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" value="' . \funcionesGenerales::mostrarFecha($value) . '"' . $required . $tonchange . '>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';
        return $string;
    }

    function armarCampoDateMd1($txtCampo = '', $obli = 'si', $id = '', $md = 6, $value = '', $hold = '', $help = '') {

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }

        $string = '
                <div class="form-group col-md-' . $md . '">
                <label class="text-left" for="' . $id . '">' . $txtCampo . '</label>
                <input type="date" class="form-control" id="' . $id . '" name="' . $id . '" value="' . \funcionesGenerales::mostrarFecha($value) . '">';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $id
     * @param type $value
     * @param type $hold
     * @param type $help
     * @return string
     */
    function armarCampoDateProtegido($txtCampo = '', $id = '', $value = '', $hold = '', $help = '') {

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }

        $string = '
                <div class="form-group">
                <label class="text-left" for="' . $id . '">' . $txtCampo . '</label>
                <input type="date" class="form-control" id="' . $id . '" name="' . $id . '" value="' . \funcionesGenerales::mostrarFecha($value) . '" readonly>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $id
     * @param type $md
     * @param type $value
     * @param type $hold
     * @param type $help
     * @return string
     */
    function armarCampoDateProtegidoMd($txtCampo = '', $id = '', $md = 6, $value = '', $hold = '', $help = '') {

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }

        $string = '
                <div class="form-group col-md-' . $md . '">
                <label class="text-left" for="' . $id . '">' . $txtCampo . '</label>
                <input type="date" class="form-control" id="' . $id . '" name="' . $id . '" value="' . \funcionesGenerales::mostrarFecha($value) . '" readonly>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $width
     * @param type $img
     * @param type $border
     * @return string
     */
    function armarCampoMostrarImagenRadicadorFoto($width, $img, $border = 0) {
        $string = '<div id="divFoto" align="center">';
        $string .= '<img src="' . $img . '" name="_foto" id="_imagen" width="180" height="140" border="' . $border . '">' . chr(13);
        $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $width
     * @param type $img
     * @return string
     */
    function armarCampoMostrarImagenRadicadorCedula1($width, $img) {
        $string = '<div id="divCedula1" align="center">';
        $string .= '<img src="' . $img . '" name="_cedula1" id="_imagen1" width="180" height="140" border="0">' . chr(13);
        $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $width
     * @param type $img
     * @return string
     */
    function armarCampoMostrarImagenRadicadorCedula2($width, $img) {
        $string = '<div id="divCedula2" align="center">';
        $string .= '<img src="' . $img . '" name="_cedula2" id="_imagen2" width="180" height="140" border="0">' . chr(13);
        $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $size
     * @param type $maxsize
     * @param type $value
     * @param type $hold
     * @param type $help
     * @param type $externalhelp
     * @return string
     */
    function armarCampoMultiTexto($txtCampo = '', $obli = 'si', $id = '', $size = 2, $maxsize = 70, $value = '', $hold = '', $help = '', $externalhelp = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }

        $string = '<div class="form-group">';
        if ($txtCampo != '') {
            $string .= '<label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>';
        }
        $string .= '<textarea class="form-control' . $formcontrol . '" rows="' . $size . '" id="' . $id . '" name="' . $id . '" ' . $required . '>' . $value . '</textarea>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $md
     * @param type $lines
     * @param type $columns
     * @param type $value
     * @param type $hold
     * @param type $help
     * @param type $externalhelp
     * @param type $uppercase
     * @return string
     */
    function armarCampoMultiTextoMd($txtCampo = '', $obli = 'si', $id = '', $md = 3, $lines = 2, $columns = 70, $value = '', $hold = '', $help = '', $externalhelp = '', $uppercase = 'no') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }

        if ($uppercase == 'si') {
            $tuppercase = ' onkeyup="javascript:this.value=this.value.toUpperCase();"';
        } else {
            $tuppercase = '';
        }
        $string = '<div class="form-group col-md-' . $md . ' col-centered">';
        if ($txtCampo != '') {
            $string .= '<label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>';
        }
        $string .= '<textarea class="form-control' . $formcontrol . '" rows="' . $lines . '" id="' . $id . '" name="' . $id . '" ' . $required . $tuppercase . '>' . $value . '</textarea>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $lines
     * @param type $columns
     * @param type $value
     * @param type $hold
     * @param type $help
     * @param type $externalhelp
     * @param type $izq
     * @param type $der
     * @return string
     */
    function armarCampoMultiTexto2($txtCampo = '', $obli = 'si', $id = '', $lines = 2, $columns = 70, $value = '', $hold = '', $help = '', $externalhelp = '', $izq = 3, $der = 9) {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }

        $string = '<div class="form-group">
                        <div class="row">
                            <div class="col-sm-' . $izq . '">
                                <label class="text-left" for="' . $id . '">' . $txtCampo . $exthelp . '&nbsp;&nbsp;';
        $string .= '</label>                            
                            </div>
                            <div class="col-sm-' . $der . '">
                               <textarea class="form-control' . $formcontrol . '" rows="' . $lines . '" id="' . $id . '" name="' . $id . '" ' . $required . '>' . $value . '</textarea>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>
                        </div>
                    </div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $id
     * @param type $size
     * @param type $maxsize
     * @param type $value
     * @return string
     */
    function armarCampoMultiTextoProtegido($txtCampo = '', $id = '', $size = 2, $maxsize = 70, $value = '') {

        $string = '<div class="form-group">';
        if ($txtCampo != '') {
            $string .= '<label class="text-left" for="' . $id . '">' . $txtCampo . '</label>';
        }
        $string .= '<textarea class="form-control" rows="' . $size . '" id="' . $id . '" name="' . $id . '" readonly>' . $value . '</textarea>';
        $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $id
     * @param type $md
     * @param type $lines
     * @param type $columns
     * @param type $value
     * @return string
     */
    function armarCampoMultiTextoProtegidoMd($txtCampo = '', $id = '', $md = 6, $lines = 2, $columns = 70, $value = '') {

        $string = '<div class="form-group col-md-' . $md . '  col-centered">';
        if ($txtCampo != '') {
            $string .= '<label class="text-left" for="' . $id . '">' . $txtCampo . '</label>';
        }
        $string .= '<textarea class="form-control" rows="' . $lines . '" id="' . $id . '" name="' . $id . '" readonly>' . $value . '</textarea>';
        $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $id
     * @param type $lines
     * @param type $columns
     * @param type $value
     * @param type $izq
     * @param type $der
     * @return string
     */
    function armarCampoMultiTextoProtegido2($txtCampo = '', $id = '', $lines = 2, $columns = 70, $value = '', $izq = 3, $der = 9) {

        $string = '<div class="form-group">
                        <div class="row">
                            <div class="col-sm-' . $izq . '">
                                <label class="text-left" for="' . $id . '">' . $txtCampo . '&nbsp;&nbsp;';
        $string .= '</label>                            
                            </div>
                            <div class="col-sm-' . $der . '">
                                <textarea class="form-control" rows="' . $lines . '" id="' . $id . '" name="' . $id . '" readonly>' . $value . '</textarea>
                            </div>
                        </div>
                    </div>';

        return $string;
    }

    /**
     * 
     * @param type $text
     * @param type $icon
     * @param type $link
     * @return string
     */
    function armarCampoEnlace($text = '', $icon = '', $link = '') {

        $string = $text . '&nbsp;&nbsp;<a href="' . $link . '"><i class="' . $icon . '"></i></a><br>';
        return $string;
    }

    function armarCampoEnlaceP($text = '', $icon = '', $link = '') {

        $string = '<p>' . $text . '&nbsp;&nbsp;<a href="' . $link . '"><i class="' . $icon . '"></i></a></p>';
        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $value
     * @param type $externalhelp
     * @return string
     */
    function armarCampoEditor($txtCampo = '', $obli = 'si', $id = '', $value = '', $externalhelp = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
        } else {
            $txtobli = '';
            $required = '';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }
        $string = '<div class="form-group">';
        if ($txtCampo != '') {
            $string .= '<label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>';
        }
        $string .= '<textarea id="' . $id . '" name="' . $id . '">' . $value . '</textarea>';
        $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $value
     * @param type $arreglo
     * @param type $hold
     * @param type $help
     * @param type $externalhelp
     * @param type $onchange
     * @return string
     */
    function armarCampoSelect($txtCampo = '', $obli = 'si', $id = '', $value = '', $arreglo = array(), $hold = '', $help = '', $externalhelp = '', $onchange = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }

        $tonchange = '';
        if ($onchange != '') {
            $tonchange = 'onchange="javascript:' . $onchange . ';"';
        }
        $string = '
                <div class="form-group">
                        <label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>                            
                        <select class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" ' . $required . ' ' . $tonchange . '>';
        if ($value == '') {
            $string .= '<option value="" selected>Seleccione</option>';
        } else {
            $string .= '<option value="">Seleccione</option>';
        }
        foreach ($arreglo as $v => $d) {
            if ($value == $v) {
                $string .= '<option value="' . $v . '" selected>' . $d . '</option>';
            } else {
                $string .= '<option value="' . $v . '">' . $d . '</option>';
            }
        }
        $string .= '</select>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $md
     * @param type $value
     * @param type $arreglo
     * @param type $hold
     * @param type $help
     * @param type $externalhelp
     * @param type $script
     * @return string
     */
    function armarCampoSelectMd($txtCampo = '', $obli = 'si', $id = '', $md = 6, $value = '', $arreglo = array(), $hold = '', $help = '', $externalhelp = '', $script = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }
        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm" data-toggle="tooltip" data-placement="top" title="Ayuda"></i></a>';
        }
        $script1 = '';
        if ($script != '') {
            $script1 = ' onchange="javascript:' . $script . ';" ';
        }
        $string = '<div class="form-group col-md-' . $md . ' col-centered">
                <label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>                            
                <select class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" ' . $script1 . $required . '>';
        if (trim((string) $value) == '') {
            $string .= '<option value="" selected>Seleccione</option>';
        } else {
            $string .= '<option value="">Seleccione</option>';
        }
        if ($arreglo && !empty($arreglo)) {
            foreach ($arreglo as $v => $d) {
                if (trim((string) $value) == trim((string) $v)) {
                    $string .= '<option value="' . $v . '" selected>' . $d . '</option>';
                } else {
                    $string .= '<option value="' . $v . '">' . $d . '</option>';
                }
            }
        }
        $string .= '</select>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $value
     * @param type $arreglo
     * @param type $hold
     * @param type $help
     * @param type $externalhelp
     * @param type $script
     * @param type $izq
     * @param type $der
     * @return string
     */
    function armarCampoSelect2($txtCampo = '', $obli = 'si', $id = '', $value = '', $arreglo = array(), $hold = '', $help = '', $externalhelp = '', $script = '', $izq = 3, $der = 9) {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }
        $script1 = '';
        if ($script != '') {
            $script1 = ' onBlur="javascript:' . $script . ';" onChange="javascript:' . $script . ';" ';
        }
        $string = '
                <div class="form-group">
                    <div class="row">
                    <div class="col-sm-' . $izq . '">
                        <label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>                            
                    </div>
                    <div class="col-sm-' . $der . '">
                        <select class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" ' . $script1 . $required . '>';
        if ($value == '') {
            $string .= '<option value="" selected>Seleccione</option>';
        } else {
            $string .= '<option value="">Seleccione</option>';
        }
        foreach ($arreglo as $v => $d) {
            if ($value == $v) {
                $string .= '<option value="' . $v . '" selected>' . $d . '</option>';
            } else {
                $string .= '<option value="' . $v . '">' . $d . '</option>';
            }
        }
        $string .= '</select>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';
        $string .= '</div>';
        $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $id
     * @param type $value
     * @param type $arreglo
     * @param type $hold
     * @param type $help
     * @return string
     */
    function armarCampoSelectProtegido($txtCampo = '', $id = '', $value = '', $arreglo = array(), $hold = '', $help = '') {
        $string = '
                <div class="form-group">
                        <label class="text-left" for="' . $id . '">' . $txtCampo . '</label>                            
                        <select class="form-control" id="' . $id . '" name="' . $id . '" disabled>';
        if ($value == '') {
            $string .= '<option value="" selected>Seleccione</option>';
        } else {
            $string .= '<option value="">Seleccione</option>';
        }
        foreach ($arreglo as $v => $d) {
            if ($value == $v) {
                $string .= '<option value="' . $v . '" selected>' . $d . '</option>';
            } else {
                $string .= '<option value="' . $v . '">' . $d . '</option>';
            }
        }
        $string .= '</select>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $id
     * @param type $md
     * @param type $value
     * @param type $arreglo
     * @param type $hold
     * @param type $help
     * @return string
     */
    function armarCampoSelectProtegidoMd($txtCampo = '', $id = '', $md = 6, $value = '', $arreglo = array(), $hold = '', $help = '') {
        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }

        $string = '<div class="form-group col-md-' . $md . ' col-centered">
                <label class="text-left" for="' . $id . '">' . $txtCampo . '</label>                            
                <select class="form-control" id="' . $id . '" name="' . $id . '" disabled>';
        if ($value == '') {
            $string .= '<option value="" selected>Seleccione</option>';
        } else {
            $string .= '<option value="">Seleccione</option>';
        }
        foreach ($arreglo as $v => $d) {
            if ($value == $v) {
                $string .= '<option value="' . $v . '" selected>' . $d . '</option>';
            } else {
                $string .= '<option value="' . $v . '">' . $d . '</option>';
            }
        }
        $string .= '</select>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $id
     * @param type $value
     * @param type $arreglo
     * @param type $hold
     * @param type $help
     * @return string
     */
    function armarCampoSelectProtegido2($txtCampo = '', $id = '', $value = '', $arreglo = array(), $hold = '', $help = '') {

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }

        $string = '
                <div class="form-group">
                    <div class="row">
                    <div class="col-sm-4">
                        <label class="text-left" for="' . $id . '">' . $txtCampo . '</label>                            
                    </div>
                    <div class="col-sm-8">
                        <select class="form-control" id="' . $id . '" name="' . $id . '" disabled>';
        if ($value == '') {
            $string .= '<option value="" selected>Seleccione</option>';
        } else {
            $string .= '<option value="">Seleccione</option>';
        }
        foreach ($arreglo as $v => $d) {
            if ($value == $v) {
                $string .= '<option value="' . $v . '" selected>' . $d . '</option>';
            } else {
                $string .= '<option value="' . $v . '">' . $d . '</option>';
            }
        }
        $string .= '</select>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';
        $string .= '</div>';
        $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $value
     * @param type $arreglo
     * @param type $javascript
     * @param type $hold
     * @param type $help
     * @param type $externalhelp
     * @return string
     */
    function armarCampoSelectOnChange($txtCampo = '', $obli = 'si', $id = '', $value = '', $arreglo = array(), $javascript = '', $hold = '', $help = '', $externalhelp = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }
        $string = '<div class="form-group">';
        if ($txtCampo != '') {
            $string .= '<label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>';
        }
        $string .= '<select class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" onchange="javascript:' . $javascript . ';" ' . $required . '>';
        if ($value == '') {
            $string .= '<option value="" selected>Seleccione</option>';
        } else {
            $string .= '<option value="">Seleccione</option>';
        }
        foreach ($arreglo as $v => $d) {
            if ($value == $v) {
                $string .= '<option value="' . $v . '" selected>' . $d . '</option>';
            } else {
                $string .= '<option value="' . $v . '">' . $d . '</option>';
            }
        }
        $string .= '</select>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';

        return $string;
    }

    function armarCampoSelectSimpleOnChange($id = '', $value = '', $arreglo = array(), $javascript = '') {
        $string = '<select  id="' . $id . '" name="' . $id . '" onchange="javascript:' . $javascript . ';">';
        if ($value == '') {
            $string .= '<option value="" selected>Seleccione</option>';
        } else {
            $string .= '<option value="">Seleccione</option>';
        }
        foreach ($arreglo as $v => $d) {
            if ($value == $v) {
                $string .= '<option value="' . str_replace(" ", "_", $v) . '" selected>' . $d . '</option>';
            } else {
                $string .= '<option value="' . str_replace(" ", "_", $v) . '">' . $d . '</option>';
            }
        }
        $string .= '</select>';
        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $md
     * @param type $value
     * @param type $arreglo
     * @param type $javascript
     * @param type $hold
     * @param type $help
     * @param type $externalhelp
     * @return string
     */
    function armarCampoSelectOnChangeMd($txtCampo = '', $obli = 'si', $id = '', $md = '6', $value = '', $arreglo = array(), $javascript = '', $hold = '', $help = '', $externalhelp = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }
        $string = '
                <div class="form-group  col-md-' . $md . ' col-centered">
                        <label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>                            
                        <select class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" onchange="javascript:' . $javascript . ';" ' . $required . '>';
        if ($value == '') {
            $string .= '<option value="" selected>Seleccione</option>';
        } else {
            $string .= '<option value="">Seleccione</option>';
        }
        foreach ($arreglo as $v => $d) {
            if ($value == $v) {
                $string .= '<option value="' . $v . '" selected>' . $d . '</option>';
            } else {
                $string .= '<option value="' . $v . '">' . $d . '</option>';
            }
        }
        $string .= '</select>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';

        return $string;
    }

    function armarCampoSelectOnChangeMd1($txtCampo = '', $obli = 'si', $id = '', $md = '6', $value = '', $arreglo = array(), $javascript = '', $hold = '', $help = '', $externalhelp = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }
        $string = '
                <div class="form-group  col-md-' . $md . ' col-centered">
                <div class="form-group row">
                <div class="col-md-3">
                <label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>                            
                </div>
                <div class="col-md-9">
                <select class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" onchange="javascript:' . $javascript . ';" ' . $required . '>';
        if ($value == '') {
            $string .= '<option value="" selected>Seleccione</option>';
        } else {
            $string .= '<option value="">Seleccione</option>';
        }
        foreach ($arreglo as $v => $d) {
            if ($value == $v) {
                $string .= '<option value="' . $v . '" selected>' . $d . '</option>';
            } else {
                $string .= '<option value="' . $v . '">' . $d . '</option>';
            }
        }
        $string .= '</select>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';
        $string .= '</div>';
        $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $md
     * @param type $value
     * @param type $arreglo
     * @param type $javascript
     * @param type $hold
     * @param type $help
     * @param type $externalhelp
     * @return string
     */
    function armarCampoSelectOnChangeMd2($txtCampo = '', $obli = 'si', $id = '', $md = '6', $value = '', $arreglo = array(), $javascript = '', $hold = '', $help = '', $externalhelp = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }
        $string = '
                <div class="form-group  col-md-' . $md . '">
                    <div class="row">
                    <div class="col-sm-4">
                        <label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>          
                    </div>
                    <div class="col-sm-8">
                        <select class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" onchange="javascript:' . $javascript . ';" ' . $required . '>';
        if ($value == '') {
            $string .= '<option value="" selected>Seleccione</option>';
        } else {
            $string .= '<option value="">Seleccione</option>';
        }
        foreach ($arreglo as $v => $d) {
            if ($value == $v) {
                $string .= '<option value="' . $v . '" selected>' . $d . '</option>';
            } else {
                $string .= '<option value="' . $v . '">' . $d . '</option>';
            }
        }
        $string .= '</select>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';
        $string .= '</div>';
        $string .= '</div>';

        return $string;
    }

    function armarCampoAutoCompleteMd($txtCampo = '', $obli = 'si', $id = '', $md = 6, $value = '', $arreglo = array(), $hold = '', $help = '', $externalhelp = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }
        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }
        $string = '<div class="form-group col-md-' . $md . '">
                <label for="' . $id . '" class="active">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>
                <input id="' . $id . '" type="text" name="' . $id . '" class="form-control' . $formcontrol . '" value="' . $value . '" ' . $placeholder . '>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '<script>';
        $string .= "var " . $id . " = [";
        $ia = 0;
        foreach ($arreglo as $ar) {
            $ia++;
            if ($ia != 1) {
                $string .= ",";
            }
            $string .= "'" . $ar . "'";
        }
        $string .= "];";
        $string .= 'autocomplete(document.getElementById("' . $id . '"), ' . $id . ');';
        $string .= '</script>';
        $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @return string
     */
    function armarCampoPassword($txtCampo = '', $obli = 'si', $id = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            /*
              if ($value == '') {
              $formcontrol = $this->borderinputrequireddanger;
              }
             */
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $string = '
                <div class="form-group">
                        <label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . '</label>';
        $string .= '<input type="password" class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" autocomplete="off" ' . $required . '>';
        $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $md
     * @return string
     */
    function armarCampoPasswordMd($txtCampo = '', $obli = 'si', $id = '', $md = 6) {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }
        $string = '<div class="form-group col-md-' . $md . ' col-centered">';
        if ($txtCampo != '') {
            $string .= '<label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . '</label>';
        }
        $string .= '<input type="password" class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" autocomplete="new-password" ' . $required . '>';
        $string .= '</div>';
        return $string;
    }

    function armarCampoTexto12R($width, $widthizq, $widthder, $txtCampo, $obli, $id, $size, $maxlength, $value, $ayuda = '', $widthayuda = 550, $heightayuda = 350, $uppercase = 'N', $txtderecha = '', $tipoCampo = 'texto', $lonmin = 0, $lonmax = 0, $script = '') {
        $txt = $this->armarCampoTexto($txtCampo, $obli, $id, $value);
        return $txt;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $value
     * @param type $hold
     * @param type $externalhelp
     * @return string
     */
    function armarCampoTexto($txtCampo = '', $obli = 'si', $id = '', $value = '', $hold = '', $externalhelp = '', $typeletter = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }

        //
        $string = '<div class="form-group">';
        if ($txtCampo != '') {
            $string .= '<label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>';
        }
        $string .= '<input type="text" class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" value="' . $value . '" ' . $placeholder . $required . '>
        </div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $value
     * @param type $hold
     * @param type $externalhelp
     * @param type $scriptonkey
     * @param type $type
     * @param type $izq
     * @param type $der
     * @return string
     */
    function armarCampoTexto2($txtCampo = '', $obli = 'si', $id = '', $value = '', $hold = '', $externalhelp = '', $scriptonkey = 'si', $type = "text", $izq = 3, $der = 9) {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }

        //
        $scriptonkey1 = '';
        if ($scriptonkey == 'si' || $scriptonkey == 'number') {
            $scriptonkey1 = ' onkeypress="return fun_AllowOnlyAmountAndDot(this.id);" ';
        }
        if ($scriptonkey == 'uppercase') {
            $scriptonkey1 = ' onkeyup="javascript:this.value=this.value.toUpperCase();" ';
        }
        if ($scriptonkey == 'lowercase') {
            $scriptonkey1 = ' onkeyup="javascript:this.value=this.value.toLowerCase();" ';
        }

        $string = '<div class="form-group">
                        <div class="row">
                            <div class="col-sm-' . $izq . '">
                                <label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>                            
                            </div>
                            <div class="col-sm-' . $der . '">
                                <input type="' . $type . '" class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" value="' . $value . '" ' . $scriptonkey1 . $placeholder . $required . '>
                            </div>
                        </div>
                    </div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $md
     * @param type $value
     * @param type $hold
     * @param type $help
     * @param type $externalhelp
     * @param type $scriptblur
     * @param type $scriptchange
     * @param type $type
     * @param type $scriptonfocus
     * @return string
     */
    function armarCampoTextoMd($txtCampo = '', $obli = 'si', $id = '', $md = 6, $value = '', $hold = '', $help = '', $externalhelp = '', $scriptblur = '', $scriptchange = '', $type = "text", $scriptonfocus = '', $autocomplete = 'on') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '' && !is_array($hold)) {
            $placeholder = 'placeholder="' . $hold . '"';
        }

        //
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm" data-toggle="tooltip" data-placement="top" title="Ayuda"></i></a>';
        }

        //
        $onblur = '';
        if ($scriptblur != '') {
            if ($scriptblur == 'si' || $scriptblur == 'number') {
                $onblur = ' onkeypress="return fun_AllowOnlyAmountAndDot(this.id);" ';
            }
            if ($scriptblur == 'onlynumbers') {
                $onblur = ' onkeypress="return onlyNumber(this.id);" ';
            }
            if ($scriptblur == 'uppercase') {
                $onblur = ' onkeyup="javascript:this.value=this.value.toUpperCase();" ';
            }
            if ($scriptblur == 'lowercase') {
                $onblur = ' onkeyup="javascript:this.value=this.value.toLowerCase();" ';
            }
            if ($onblur == '') {
                $onblur = ' onBlur="javascript:' . $scriptblur . '" ';
            }
        }

        //
        $onchange = '';
        if ($scriptchange != '') {
            $onchange = ' onChange="javascript:' . $scriptchange . '" ';
        }

        $onfocus = '';
        if ($scriptonfocus != '') {
            $onfocus = ' onFocus="javascript:' . $scriptonfocus . '" ';
        }


        // $string = '<div class="row justify-content-center align-items-center">';
        $string = '<div class="form-group col-md-' . $md . ' col-centered">';
        if ($txtCampo != '') {
            $string .= '<label class="text-left" for="' . $id . 'x">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>';
        }
        $string .= '<input type="' . $type . '" class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" autocomplete="' . $autocomplete . '" value="' . $value . '"' . $onfocus . $onblur . $onchange . $placeholder . $required . '>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';
        // $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $md
     * @param type $value
     * @param type $hold
     * @param type $help
     * @param type $externalhelp
     * @param type $scriptblur
     * @param type $scriptchange
     * @param type $type
     * @return string
     */
    function armarCampoTextoMd1($txtCampo = '', $obli = 'si', $id = '', $md = 6, $value = '', $hold = '', $help = '', $externalhelp = '', $scriptblur = '', $scriptchange = '', $type = "text") {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }

        //
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }

        //
        $onblur = '';
        if ($scriptblur != '') {
            $onblur = ' onBlur="javascript:' . $scriptblur . '" ';
        }

        //
        $onchange = '';
        if ($scriptchange != '') {
            $onchange = ' onChange="javascript:' . $scriptchange . '" ';
        }

        $string = '<div class="form-group col-md-' . $md . ' col-centered">';
        $string .= '<div class="form-group row">';
        if ($txtCampo != '') {
            $string .= '<div class="col-md-3">';
            $string .= '<label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>';
            $string .= '</div>';
        }
        $string .= '<div class="col-md-9">';
        $string .= '<input type="' . $type . '" class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" value="' . $value . '"' . $onblur . $onchange . $placeholder . $required . '>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';
        $string .= '</div>';
        $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $md
     * @param type $value
     * @param type $hold
     * @return string
     */
    function armarCampoCelularMd($txtCampo = '', $obli = 'si', $id = '', $md = 6, $value = '', $hold = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }

        // $string = '<div class="row justify-content-center align-items-center">';
        $string = '<div class="form-group col-md-' . $md . ' col-centered">';
        if ($txtCampo != '') {
            $string .= '<label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . '</label><br>';
        }
        $string .= '<input type="tel" class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" value="' . $value . '"' . $required . '>';

        $string .= '</div>';
        // $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $md
     * @param type $value
     * @param type $hold
     * @param type $help
     * @param type $externalhelp
     * @param type $scriptonkey (si o no)
     * @param type $type
     * @return string
     */
    function armarCampoTextoMdOnKey($txtCampo = '', $obli = 'si', $id = '', $md = 6, $value = '', $hold = '', $help = '', $externalhelp = '', $scriptonkey = 'si', $type = "text") {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }

        //
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm" data-toggle="tooltip" data-placement="top" title="Ayuda"></i></a>';
        }

        //
        $scriptonkey1 = '';
        if ($scriptonkey == 'si' || $scriptonkey == 'number') {
            $scriptonkey1 = ' onkeypress="return fun_AllowOnlyAmountAndDot(this.id);" ';
        }
        if ($scriptonkey == 'onlynumbers') {
            $scriptonkey1 = ' onkeypress="return onlyNumber(this.id);" ';
        }
        if ($scriptonkey == 'uppercase') {
            $scriptonkey1 = ' onkeyup="javascript:this.value=this.value.toUpperCase();" ';
        }
        if ($scriptonkey == 'lowercase') {
            $scriptonkey1 = ' onkeyup="javascript:this.value=this.value.toLowerCase();" ';
        }

        // $string = '<div class="row justify-content-center align-items-center">';
        $string = '<div class="form-group col-md-' . $md . ' col-centered">';
        if ($txtCampo != '') {
            $string .= '<label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>';
        }
        $string .= '<input type="' . $type . '" class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" value="' . $value . '"' . $scriptonkey1 . $placeholder . $required . '>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';
        // $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $md
     * @param type $value
     * @param type $hold
     * @param type $externalhelp
     * @return string
     */
    function armarCampoTextoMd2($txtCampo = '', $obli = 'si', $id = '', $md = 6, $value = '', $hold = '', $externalhelp = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }

        $string = '<div class="row justify-content-center align-items-center">'
                . '<div class="form-group col-md-' . $md . '">
                        <div class="row">
                            <div class="col-sm-4">
                                <label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>                            
                            </div>
                            <div class="col-sm-8">
                                <input type="text" class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" value="' . $value . '" ' . $placeholder . $required . '>
                            </div>
                        </div>
                    </div>
                    </div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $value
     * @param type $script
     * @param type $hold
     * @param type $help
     * @param type $externalhelp
     * @return string
     */
    function armarCampoTextoOnBlur($txtCampo = '', $obli = 'si', $id = '', $value = '', $script = '', $hold = '', $help = '', $externalhelp = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }

        $string = '<div class="form-group">';
        if ($txtCampo != '') {
            $string .= '<label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>';
        }
        $string .= '<input type="text" class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" value="' . $value . '" onBlur="' . $script . '" ' . $placeholder . $required . '>';
        if ($help != '') {
            $string .= '<span class="help-block"><small>' . $help . '</small></span>';
        }
        $string .= '</div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $value
     * @param type $script
     * @param type $hold
     * @param type $help
     * @param type $externalhelp
     * @return string
     */
    function armarCampoTextoOnBlur2($txtCampo = '', $obli = 'si', $id = '', $value = '', $script = '', $hold = '', $help = '', $externalhelp = '') {
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $required = ' required';
            $formcontrol = $this->borderinputrequired;
            if ($value == '') {
                $formcontrol = $this->borderinputrequireddanger;
            }
        } else {
            $txtobli = '';
            $required = '';
            $formcontrol = '';
        }

        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }
        $exthelp = '';
        if ($externalhelp != '') {
            $exthelp = '&nbsp;<a href="javascript:viewInfo(\'' . $externalhelp . '\');"><i class="fas fa-info-circle fa-sm"></i></a>';
        }

        //
        $string = '<div class="form-group">
                        <div class="row">
                            <div class="col-sm-4">
                                <label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . $exthelp . '</label>                            
                            </div>
                            <div class="col-sm-8">
                                <input type="text" class="form-control' . $formcontrol . '" id="' . $id . '" name="' . $id . '" value="' . $value . '" onBlur="' . $script . '" ' . $placeholder . $required . '>
                            </div>
                        </div>
                    </div>';

        return $string;
    }

    /**
     * 
     * @param type $id
     * @param type $value
     * @return string
     */
    function armarCampoTextoOculto($id = '', $value = '') {
        $string = '<div class="form-group"><input type="hidden" id="' . $id . '" name="' . $id . '" value="' . $value . '"></div>';
        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $id
     * @param type $value
     * @param type $hold
     * @param type $help
     * @return string
     */
    function armarCampoTextoProtegido($txtCampo = '', $id = '', $value = '', $hold = '', $help = '') {
        $string = '
                <div class="form-group">
                        <label class="text-left" for="' . $id . '">' . $txtCampo . '</label>
                        <input type="text" class="form-control" id="' . $id . '" name="' . $id . '" value="' . $value . '" readonly>
                </div>';

        return $string;
    }

    function armarCampoTextoProtegido12R($width, $widthizq, $widthder, $txtCampo, $id, $size, $maxlength, $value, $ayuda = '', $uppercase = 'N', $mostrar = 'pc') {
        return $this->armarCampoTextoProtegido($txtCampo, $id, $value);
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $id
     * @param type $md
     * @param type $value
     * @param type $hold
     * @param type $help
     * @param type $javascript
     * @param type $javascript1
     * @return string
     */
    function armarCampoTextoProtegidoMd($txtCampo = '', $id = '', $md = 6, $value = '', $hold = '', $help = '', $javascript = '', $javascript1 = '') {
        // $string = '<div class="row justify-content-center align-items-center">';
        $string = '<div class="form-group col-md-' . $md . ' col-centered">';
        $string .= '<label class="label-left" for="' . $id . '">' . $txtCampo . '&nbsp;&nbsp;';
        if ($javascript != '') {
            $string .= '<a href="javascript:' . $javascript . ';"><i class="fa fa-question-circle fa-sm"></i></a>&nbsp;&nbsp;';
        }
        if ($javascript1 != '') {
            $string .= '<a href="javascript:' . $javascript1 . ';"><i class="fas fa-eraser"></i></a>&nbsp;&nbsp;';
        }
        $string .= '</label>';
        $string .= '<input type="text" class="form-control" id="' . $id . '" name="' . $id . '" value="' . $value . '" readonly>';
        $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $id
     * @param type $value
     * @param type $hold
     * @param type $help
     * @param type $javascript1 - search
     * @param type $javascript2 - delete
     * @param type $izq
     * @param type $der
     * @return string
     */
    function armarCampoTextoProtegido2($txtCampo = '', $id = '', $value = '', $hold = '', $help = '', $javascript1 = '', $javascript2 = '', $izq = 3, $der = 9) {
        $placeholder = '';
        if ($hold != '') {
            $placeholder = 'placeholder="' . $hold . '"';
        }



        $string = '<div class="form-group">
                        <div class="row">
                            <div class="col-sm-' . $izq . '">
                                <label class="text-left" for="' . $id . '">' . $txtCampo . '&nbsp;&nbsp;';
        if ($help != '') {
            $string .= '<a href="javascript:viewInfo(\'' . $help . '\');"><i class="fa fa-question-circle fa-sm"></i></a>&nbsp;&nbsp;';
        }
        if ($javascript1 != '') {
            $string .= '<a href="javascript:' . $javascript1 . ';"><i class="fas fa-search"></i></a>&nbsp;&nbsp;';
        }
        if ($javascript2 != '') {
            $string .= '<a href="javascript:' . $javascript2 . ';"><i class="fas fa-eraser"></i></a>&nbsp;&nbsp;';
        }
        $string .= '</label>                            
                            </div>
                            <div class="col-sm-' . $der . '">
                                <input type="text" class="form-control" id="' . $id . '" name="' . $id . '" value="' . $value . '" ' . $placeholder . ' readonly>
                            </div>
                        </div>
                    </div>';

        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $tipoarchivo
     * @param type $filehtml
     * @param type $numrec
     * @param type $imagetype
     * @return string
     */
    function armarCampoUpload($txtCampo, $obli, $id, $tipoarchivo = '', $filehtml = '', $numrec = '', $imagetype = '') {

        //
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $formcontrol = $this->borderinputrequired;
        } else {
            $txtobli = '';
            $formcontrol = '';
        }

        //
        $string = '';

        //
        $string .= '
            <div class="form-group">
            <label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . '</label>
            <input type="button" class="form-control' . $formcontrol . '" "id="' . $id . '" name="' . $id . '" value="Cargar">
            <script type="text/javascript">
    		var element_' . $id . ' = document.getElementById("' . $id . '");
    		upclick({
    			element:element_' . $id . ',
    			action: "uploadFile.php?tipoarchivo=' . $tipoarchivo . '&numrec=' . $numrec . '",
    			onstart:
    			function(filename){    					    				
    			},
    			oncomplete:
    			function(response_data) {';
        if ($filehtml != '') {
            $string .= 'var txt = \'<center><a href="' . $filehtml . '" target="_blank"><img src="../../html/default/images/pack/file32.png" width="32" height="32" border="3"></a></center><br>\';
    					document.getElementById(\'div' . $id . '\').innerHTML=txt;';
        } else {
            $string .= 'var txt = \'<center><img src="../../html/default/images/pack/file32.png" width="32" height="32" border="3"></center><br>\';
    					document.getElementById(\'div' . $id . '\').innerHTML=txt;';
        }
        $string .= '}
    		});
    		</script>';

        //
        return $string;
    }

    /**
     * 
     * @param type $txtCampo
     * @param type $obli
     * @param type $id
     * @param type $md
     * @param type $tipoarchivo
     * @param type $filehtml
     * @param type $numrec
     * @param type $imagetype
     * @return string
     */
    function armarCampoUploadMd($txtCampo, $obli, $id, $md = 4, $tipoarchivo = '', $filehtml = '', $numrec = '', $imagetype = '') {

        //
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $formcontrol = $this->borderinputrequired;
        } else {
            $txtobli = '';
            $formcontrol = '';
        }

        //
        $string = '';

        //
        $string .= '
            <div class="row justify-content-center align-items-center">
            <div class="form-group col-md-' . $md . '">
            <label class="text-left" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . '</label>
            <input type="button" class="form-control' . $formcontrol . '" "id="' . $id . '" name="' . $id . '" value="Cargar">
            <script type="text/javascript">
    		var element_' . $id . ' = document.getElementById("' . $id . '");
    		upclick({
    			element:element_' . $id . ',
    			action: "uploadFile.php?tipoarchivo=' . $tipoarchivo . '&numrec=' . $numrec . '&session_parameters=' . \funcionesGenerales::armarVariablesPantalla() . '",
    			onstart:
    			function(filename){    					    				
    			},
    			oncomplete:
    			function(response_data) {';
        if ($filehtml != '') {
            $string .= 'var txt = \'<center><a href="' . $filehtml . '" target="_blank"><img src="../html/default/images/pack/file32.png" width="32" height="32" border="3"></a></center><br>\';
    					document.getElementById(\'div' . $id . '\').innerHTML=txt;';
        } else {
            $string .= 'var txt = \'<center><img src="../html/default/images/pack/file32.png" width="32" height="32" border="3"></center><br>\';
    					document.getElementById(\'div' . $id . '\').innerHTML=txt;';
        }
        $string .= '}
    		});
    		</script>';

        //
        $string .= '</div>
            </div>';

        //
        return $string;
    }

    function armarCampoUploadSimpleMd($txtCampo, $obli, $id, $md = 4) {

        //
        if ($obli == 'si' || $obli == 'yes') {
            $txtobli = '<i class="fas fa-asterisk fa-sm"></i>';
            $formcontrol = $this->borderinputrequired;
        } else {
            $txtobli = '';
            $formcontrol = '';
        }

        //
        $string = '';

        //
        $string .= '
            <div class="form-group col-md-' . $md . '">
            <label class="form-label" for="' . $id . '">' . $txtobli . '&nbsp;' . $txtCampo . '</label>
            <input type="file" class="form-control' . $formcontrol . '" "id="' . $id . '" name="' . $id . '">';
        $string .= '
            </div>';

        //
        return $string;
    }

    /**
     * 
     * @param type $id
     * @param type $filehtml
     * @param type $tamanomax
     * @return string
     */
    function armarCampoUploadWrapper($id, $filehtml = '', $tamanomax = 2) {

        //
        $string = '<div class="form-group" align="center">
                    <div class="col-sm-offset-2 col-sm-10">
                        <label class="file-upload">' .
                'Archivo .... ' . '&nbsp;&nbsp;&nbsp;&nbsp;<input type="file" id="' . $id . '" name="' . $id . '" data-max-file-size="' . $tamanomax . 'M" />
                        </label>
                    </div>
                </div>';

        //
        return $string;
    }

    /**
     * 
     * @param type $name
     * @param type $vis (none, block)
     * @return type
     */
    function armarDiv($name, $vis) {
        return '<div id="' . $name . '" style="display:' . $vis . ';">';
    }

    /**
     * 
     * @param type $img
     * @return string
     */
    function armarImagen($img) {
        $string = '<center><img class="img-responsive img-fluid" src="' . $img . '" style="width:100%"></center>';
        return $string;
    }

    /**
     * 
     * @param type $txt
     * @param type $align
     * @param type $font
     * @param type $color
     * @param type $subrayar
     * @param type $bold
     * @param type $id
     * @param type $anchor0
     * @param type $link
     * @return string
     * 
     */
    function armarLineaTextoInformativa($txt, $align = '', $font = '', $color = 'text-dark', $subrayar = 'no', $bold = 'no', $id = '', $anchor0 = '', $link = '') {
        $string = '';
        $aligntext = '';
        $colorx = '';

        //
        if ($align == 'center') {
            $aligntext = "text-center";
        }
        if ($align == 'left') {
            $aligntext = "text-left";
        }
        if ($align == 'right') {
            $aligntext = "text-right";
        }
        if ($align == 'justify') {
            $aligntext = "text-justify";
        }

        //
        if ($color == '') {
            $colorx = "text-info";
        } else {
            $colorx = $color;
        }

        $span = '';
        $espan = '';
        if ($subrayar == 'yes') {
            $subrayar = 'si';
        }
        if ($subrayar == 'not') {
            $subrayar = 'no';
        }
        if ($bold == 'yes') {
            $bold = 'si';
        }
        if ($bold == 'not') {
            $bold = 'no';
        }
        if ($subrayar == 'si' && $bold == 'si') {
            $span = '<span style="text-decoration: underline;"><strong>';
            $espan = '</strong></span>';
        }
        if ($subrayar == 'si' && $bold == 'no') {
            $span = '<span style="text-decoration: underline;">';
            $espan = '</span>';
        }
        if ($subrayar == 'no' && $bold == 'si') {
            $span = '<strong>';
            $espan = '</strong>';
        }
        if ($id != '') {
            $idt = ' id="' . $id . '" ';
        } else {
            $idt = '';
        }
        if ($anchor0 != '') {
            $tanchor0 = ' <a href="#' . $anchor0 . '"><i class="fas fa-home"></i></a>';
        } else {
            $tanchor0 = '';
        }

        $tlink = '';
        if ($link != '') {
            if (substr($link, 0, 19) == 'pantallapredisenada') {
                $link1 = '';
                if (isset($_SESSION["tramite"]["subtipotramite"]) && $_SESSION["tramite"]["subtipotramite"] != '') {
                    $link1 = retornarRegistroMysqliApi(null, 'bas_tipotramites', "id='" . $_SESSION["tramite"]["subtipotramite"] . "'", "tyc");
                } else {
                    if (isset($_SESSION["tramite"]["tipotramite"]) && $_SESSION["tramite"]["tipotramite"] != '') {
                        $link1 = retornarRegistroMysqliApi(null, 'bas_tipotramites', "id='" . $_SESSION["tramite"]["tipotramite"] . "'", "tyc");
                    }
                }
                if ($link1 == '') {
                    $link1 = substr($link, 20);
                }
                if (trim($link1) != '') {
                    $tlink = '<a href="' . TIPO_HTTP . HTTP_HOST . '/scripts/mostrarPantallasSimple.php?pantalla=' . $link1 . '&session_parameters=' . \funcionesGenerales::armarVariablesPantalla() . '" target="_blank"><img src="' . TIPO_HTTP . HTTP_HOST . '/images/terms-conditions.jpg" width="60"/></a>';
                }
            } else {
                $tlink = '<a href="' . $link . '" target="_blank"><img src="' . TIPO_HTTP . HTTP_HOST . '/images/terms-conditions.jpg" width="60"/></a>';
            }
        }


        if ($font == '') {
            $string .= '<p ' . $idt . 'class="' . $color . ' ' . $aligntext . '">' . $span . $txt . $espan . $tanchor0 . '</p>';
        } else {
            if ($tlink != '') {
                if (substr($font, 0, 1) == 'p') {
                    $string .= '<div class="row">
                            <div class="col-md-10" ' . $idt . '><' . $font . '>' . $span . $txt . $espan . $tanchor0 . '</p></div>
                            <div class="col-md-2">' . $tlink . '</div>
                            </div>';
                } else {
                    $string .= '<div class="row">
                            <div class="col-md-10" ' . $idt . '><' . $font . '><p class="' . $color . ' ' . $aligntext . '">' . $span . $txt . $espan . $tanchor0 . '</p></' . $font . '></div>
                            <div class="col-md-2">' . $tlink . '</div>
                            </div>';
                }
            } else {
                if (substr($font, 0, 1) == 'p') {
                    $string .= '<div class="row">
                            <div class="col-md-12" ' . $idt . '><' . $font . '>' . $span . $txt . $espan . $tanchor0 . '</p></div>
                            </div>';
                } else {
                    $string .= '<div class="row">
                            <div class="col-md-12" ' . $idt . '><' . $font . '><p class="' . $color . ' ' . $aligntext . '">' . $span . $txt . $espan . $tanchor0 . '</p></' . $font . '></div>
                            </div>';
                }
            }
        }
        return $string;
    }

    function armarLineaTextoInformativaMd($txt, $md = 3, $align = '', $font = '', $color = 'text-dark', $subrayar = 'no', $bold = 'no', $id = '', $anchor0 = '', $link = '') {
        $string = '<div class="form-group col-md-' . $md . ' col-centered">';
        $aligntext = '';
        $colorx = '';

        //
        if ($align == 'center') {
            $aligntext = "text-center";
        }
        if ($align == 'left') {
            $aligntext = "text-left";
        }
        if ($align == 'right') {
            $aligntext = "text-right";
        }
        if ($align == 'justify') {
            $aligntext = "text-justify";
        }

        //
        if ($color == '') {
            $colorx = "text-info";
        } else {
            $colorx = $color;
        }

        $span = '';
        $espan = '';
        if ($subrayar == 'yes') {
            $subrayar = 'si';
        }
        if ($subrayar == 'not') {
            $subrayar = 'no';
        }
        if ($bold == 'yes') {
            $bold = 'si';
        }
        if ($bold == 'not') {
            $bold = 'no';
        }
        if ($subrayar == 'si' && $bold == 'si') {
            $span = '<span style="text-decoration: underline;"><strong>';
            $espan = '</strong></span>';
        }
        if ($subrayar == 'si' && $bold == 'no') {
            $span = '<span style="text-decoration: underline;">';
            $espan = '</span>';
        }
        if ($subrayar == 'no' && $bold == 'si') {
            $span = '<strong>';
            $espan = '</strong>';
        }
        if ($id != '') {
            $idt = ' id="' . $id . '" ';
        } else {
            $idt = '';
        }
        if ($anchor0 != '') {
            $tanchor0 = ' <a href="#' . $anchor0 . '"><i class="fas fa-home"></i></a>';
        } else {
            $tanchor0 = '';
        }

        $tlink = '';
        if ($link != '') {
            if (substr($link, 0, 19) == 'pantallapredisenada') {
                $link1 = '';
                if (isset($_SESSION["tramite"]["subtipotramite"]) && $_SESSION["tramite"]["subtipotramite"] != '') {
                    $link1 = retornarRegistroMysqliApi(null, 'bas_tipotramites', "id='" . $_SESSION["tramite"]["subtipotramite"] . "'", "tyc");
                } else {
                    if (isset($_SESSION["tramite"]["tipotramite"]) && $_SESSION["tramite"]["tipotramite"] != '') {
                        $link1 = retornarRegistroMysqliApi(null, 'bas_tipotramites', "id='" . $_SESSION["tramite"]["tipotramite"] . "'", "tyc");
                    }
                }
                if ($link1 == '') {
                    $link1 = substr($link, 20);
                }
                if (trim($link1) != '') {
                    $tlink = '<a href="' . TIPO_HTTP . HTTP_HOST . '/scripts/mostrarPantallasSimple.php?pantalla=' . $link1 . '&session_parameters=' . \funcionesGenerales::armarVariablesPantalla() . '" target="_blank"><img src="' . TIPO_HTTP . HTTP_HOST . '/images/terms-conditions.jpg" width="60"/></a>';
                }
            } else {
                $tlink = '<a href="' . $link . '" target="_blank"><img src="' . TIPO_HTTP . HTTP_HOST . '/images/terms-conditions.jpg" width="60"/></a>';
            }
        }


        if ($font == '') {
            $string .= '<p ' . $idt . 'class="' . $color . ' ' . $aligntext . '">' . $span . $txt . $espan . $tanchor0 . '</p>';
        } else {
            if ($tlink != '') {
                if (substr($font, 0, 1) == 'p') {
                    $string .= '<div class="row">
                            <div class="col-md-10" ' . $idt . '><' . $font . '>' . $span . $txt . $espan . $tanchor0 . '</p></div>
                            <div class="col-md-2">' . $tlink . '</div>
                            </div>';
                } else {
                    $string .= '<div class="row">
                            <div class="col-md-10" ' . $idt . '><' . $font . '><p class="' . $color . ' ' . $aligntext . '">' . $span . $txt . $espan . $tanchor0 . '</p></' . $font . '></div>
                            <div class="col-md-2">' . $tlink . '</div>
                            </div>';
                }
            } else {
                if (substr($font, 0, 1) == 'p') {
                    $string .= '<div class="row">
                            <div class="col-md-12" ' . $idt . '><' . $font . '>' . $span . $txt . $espan . $tanchor0 . '</p></div>
                            </div>';
                } else {
                    $string .= '<div class="row">
                            <div class="col-md-12" ' . $idt . '><' . $font . '><p class="' . $color . ' ' . $aligntext . '">' . $span . $txt . $espan . $tanchor0 . '</p></' . $font . '></div>
                            </div>';
                }
            }
        }
        $string .= '</div>';
        return $string;
    }

    function armarLineaTextoInformativaCourier($txt, $align = '', $font = '', $color = 'text-dark', $subrayar = 'no', $bold = 'no') {
        $string = '';
        $aligntext = '';
        $colorx = '';

        //
        if ($align == 'center') {
            $aligntext = "text-center";
        }
        if ($align == 'left') {
            $aligntext = "text-left";
        }
        if ($align == 'right') {
            $aligntext = "text-right";
        }
        if ($align == 'jutify') {
            $aligntext = "text-justify";
        }

        //
        if ($color == '') {
            $colorx = "text-info";
        } else {
            $colorx = $color;
        }

        $span = '';
        $espan = '';
        if ($subrayar == 'yes') {
            $subrayar = 'si';
        }
        if ($subrayar == 'not') {
            $subrayar = 'no';
        }
        if ($bold == 'yes') {
            $bold = 'si';
        }
        if ($bold == 'not') {
            $bold = 'no';
        }
        if ($subrayar == 'si' && $bold == 'si') {
            $span = '<span style="text-decoration: underline;"><strong>';
            $espan = '</strong></span>';
        }
        if ($subrayar == 'si' && $bold == 'no') {
            $span = '<span style="text-decoration: underline;">';
            $espan = '</span>';
        }
        if ($subrayar == 'no' && $bold == 'si') {
            $span = '<strong>';
            $espan = '</strong>';
        }

        if ($font == '') {
            $string .= '<p class="' . $color . ' ' . $aligntext . '">' . $span . $txt . $espan . '</p>';
        } else {
            if (substr($font, 0, 1) == 'p') {
                $string .= '<div><' . $font . '>' . $span . $txt . $espan . '</p></div>';
            } else {
                $string .= '<div><' . $font . '><p class="' . $color . ' ' . $aligntext . '">' . $span . $txt . $espan . '</p></' . $font . '></div>';
            }
        }
        return $string;
    }

    function armarLineaTexto($width, $txt, $align = 'center') {
        $txt = $this->armarLineaTextoInformativa($txt, $align);
        return $txt;
    }

    /**
     * 
     * @param type $txtIzquierda
     * @param type $txtDerecha
     * @return string
     */
    function armarLineaTexto2Columnas($txtIzquierda = '', $txtDerecha = '') {
        $string = '
             <div class="row">
                <div class="col-sm-6" style="background-color:yellow;">' . $txtIzquierda . '</div>
                <div class="col-sm-6">' . $txtDerecha . '</div>
            </div>';
        return $string;
    }

    /**
     * 
     * @param type $id
     * @param type $visible
     * @param type $size
     * @return string
     */
    function abrirPanel($id = '', $visible = '', $size = 0, $border = 'si') {
        if ($visible == '' || $visible == 'si') {
            if ($border == 'si') {
                $tvis = ' style="visibility:visible"';
            } else {
                $tvis = ' style="visibility:visible; border:none;"';
            }
        } else {
            if ($border == 'si') {
                $tvis = ' style="visibility:hidden"';
            } else {
                $tvis = ' style="visibility:hidden; border:none;"';
            }
        }
        if ($id == '') {
            $string = '<div class="card fat"' . $tvis . '>';
        } else {
            $string = '<div class="card fat" id="' . $id . '"' . $tvis . '>';
        }
        if ($size == 0) {
            $string .= '<div class="card-body">';
        } else {
            $string .= '<div class="card-body" style="max-width:' . $size . 'px;margin-left:auto;margin-right:auto;">';
        }
        return $string;
    }

    function abrirTablaBorde($size = 0) {
        return $this->abrirPanel('', '', 0, 'si');
    }

    /**
     * 
     * @param int $size
     * @return type
     */
    function abrirPanelGeneral($size = 0) {
        if ($size == 0) {
            $size = 600;
        }
        return '<div class="panel panel-default bg-light" style="max-width:' . $size . 'px;margin-left:auto;margin-right:auto;">';
    }

    /**
     * 
     * @param type $align
     * @param type $size
     * @return string
     */
    function abrirRow($align = '', $size = 0) {
        if ($align == 'center') {
            if ($size == 0) {
                return '<div class="form-group row text-center">';
            } else {
                return '<div class="form-group row text-center" style="max-width:' . $size . 'px;margin-left:auto;margin-right:auto;">';
            }
        } else {
            if ($size == 0) {
                return '<div class="form-group row">';
            } else {
                return '<div class="form-group row" style="max-width:' . $size . 'px;margin-left:auto;margin-right:auto;">';
            }
        }
    }

    /**
     * 
     * @return string
     */
    function abrirCol($md = 0) {
        if ($md == 0) {
            return '<div class="col">';
        } else {
            return '<div class="col-sm-' . $md . '">';
        }
    }

    /**
     * 
     * @param type $count
     * @return string
     */
    function armarCardDeckAbrir() {
        $string = '<div class="card-deck">';
        return $string;
    }

    /**
     * 
     * @return string
     */
    function armarCardDeckCerrar() {
        $string = '</div>';
        return $string;
    }

    /**
     * 
     * @param type $count
     * @return string
     */
    function armarCardColumnAbrir() {
        $string = '<div class="card-columns>';
        return $string;
    }

    /**
     * 
     * @return string
     */
    function armarCardColumnCerrar() {
        $string = '</div>';
        return $string;
    }

    /**
     * 
     * @param type $count
     * @return string
     */
    function armarCardLineAbrir() {
        $string = '<div class="row">';
        // $string .= '<div class="col-sm-6">';
        return $string;
    }

    /**
     * 
     * @return string
     */
    function armarCardLineCerrar() {
        // $string = '</div>';
        $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $width
     * @param type $img
     * @param type $title
     * @param type $text
     * @param type $linkimg
     * @param type $linkbutton
     * @param type $target
     * @return string
     */
    function armarCard($width = 300, $img = '', $title = '', $text = '', $linkimg = '', $linkbutton = '', $target = '_blank') {
        $string = '<div class="col-sm-6">';
        $string = '';
        if ($width != 0) {
            $string .= '<div class="card bg-light" style="max-width: ' . $width . 'px;">';
        } else {
            $string .= '<div class="card bg-light">';
        }
        if ($img != '') {
            if ($linkimg != '') {
                if ($width != 0) {
                    $string .= '<a href="' . $linkimg . '" target="' . $target . '"><img class="card-img-top" src="' . $img . '"  style="width:' . ($width - 10) . 'px;height:' . $width * 0.6 . 'px;"></a>';
                } else {
                    $string .= '<a href="' . $linkimg . '" target="' . $target . '"><img class="card-img-top" src="' . $img . '"></a>';
                }
            } else {
                if ($width != 0) {
                    $string .= '<img class="card-img-top" src="' . $img . '" style="width:' . ($width - 10) . 'px;height:' . $width * 0.6 . 'px;">';
                } else {
                    $string .= '<img class="card-img-top" src="' . $img . '">';
                }
            }
        }
        $string .= '<div class="card-body">';
        if ($title != '') {
            $string .= '<h5 class="card-title">' . $title . '</h5>';
        }
        if ($text != '') {
            $string .= '<p class="card-text">' . $text . '</p>';
        }
        if ($linkbutton != '') {
            $string .= '<a href="' . $linkbutton . '" target="' . $target . '" class="btn btn-primary">Continuar</a>';
        }
        $string .= '</div>';
        $string .= '</div>';
        // $string .= '</div>';
        return $string;
    }

    function armarCardToolTip($width = 300, $img = '', $title = '', $text = '', $linkimg = '', $linkbutton = '', $target = '_blank') {
        $string = '<div class="col-sm-6">';
        $string = '';
        if ($width != 0) {
            $string .= '<div class="card bg-light" style="max-width: ' . $width . 'px;" data-toggle="tooltip" data-placement="top" title="' . $text . '">';
        } else {
            $string .= '<div class="card bg-light" data-toggle="tooltip" data-placement="top" title="' . $text . '">';
        }
        if ($img != '') {
            if ($linkimg != '') {
                if ($width != 0) {
                    $string .= '<a href="' . $linkimg . '" target="' . $target . '"><img class="card-img-top" src="' . $img . '"  style="width:' . ($width - 10) . 'px;height:' . $width * 0.6 . 'px;"></a>';
                } else {
                    $string .= '<a href="' . $linkimg . '" target="' . $target . '"><img class="card-img-top" src="' . $img . '"></a>';
                }
            } else {
                if ($width != 0) {
                    $string .= '<img class="card-img-top" src="' . $img . '" style="width:' . ($width - 10) . 'px;height:' . $width * 0.6 . 'px;">';
                } else {
                    $string .= '<img class="card-img-top" src="' . $img . '">';
                }
            }
        }
        $string .= '<div class="card-body">';
        if ($title != '') {
            $string .= '<h5 class="card-title">' . $title . '</h5>';
        }
        if ($linkbutton != '') {
            $string .= '<a href="' . $linkbutton . '" target="' . $target . '" class="btn btn-primary">Continuar</a>';
        }
        $string .= '</div>';
        $string .= '</div>';
        // $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $width
     * @param type $img
     * @param type $title
     * @param type $text
     * @param type $linkbutton
     * @param type $target
     * @return string
     */
    function armarCard1($width = 300, $img = '', $title = '', $text = '', $linkbutton = '', $target = '_blank') {
        $string = '';
        if ($width != 0) {
            $string .= '<div class="card bg-light" style="width: ' . $width . 'px; max-width: ' . $width . 'px;">';
        } else {
            $string .= '<div class="card bg-light">';
        }
        if ($img != '') {
            if ($width != 0) {
                $string .= '<img class="card-img-top" src="' . $img . '" style="width:' . ($width - 10) . 'px;height:' . $width * 0.6 . 'px;">';
            } else {
                $string .= '<img class="card-img-top" src="' . $img . '">';
            }
        }
        $string .= '<div class="card-body">';
        if ($title != '') {
            $string .= '<h5 class="card-title">' . $title . '</h5>';
        }
        if ($text != '') {
            $string .= '<p class="card-text">' . $text . '</p>';
        }
        if ($linkbutton != '') {
            $string .= '<a href="' . $linkbutton . '" target="' . $target . '" class="btn btn-primary">Continuar</a>';
        }
        $string .= '</div>';
        $string .= '</div>';
        $string .= '';
        return $string;
    }

    /**
     * 
     * @param type $width
     * @param type $img
     * @param type $title
     * @param type $text
     * @param type $linkbutton
     * @param type $list
     * @param type $idx
     * @return string
     */
    function armarCard2($width = 300, $img = '', $title = '', $text = '', $linkbutton = '', $list = array(), $idx = '') {
        $string = '';
        if ($width != 0) {
            $string .= '<div class="card bg-light" style="width: ' . $width . 'px; max-width: ' . $width . 'px;">';
        } else {
            $string .= '<div class="card bg-light">';
        }
        if ($img != '') {
            if ($width != 0) {
                $string .= '<img class="card-img-top" src="' . $img . '" style="width:' . ($width - 10) . 'px;height:' . $width * 0.6 . 'px;">';
            } else {
                $string .= '<img class="card-img-top" src="' . $img . '">';
            }
        }
        $string .= '<div class="card-body">';
        if ($title != '') {
            $string .= '<h5 class="card-title">' . $title . '</h5>';
        }
        if ($text != '') {
            $string .= '<p class="card-text">' . $text . '</p>';
        }

        if (!empty($list)) {
            $string .= '<select class="form-control" id="' . $idx . '" name="' . $idx . '">';
            $string .= '<option value="" selected>Seleccione</option>';
            foreach ($list as $v => $d) {
                $string .= '<option value="' . $v . '">' . $d . '</option>';
            }
            $string .= '</select>';
        }

        if ($linkbutton != '') {
            $string .= '<br><a href="' . $linkbutton . '" class="btn btn-primary">Continuar</a>';
        }

        $string .= '</div>';
        $string .= '</div>';
        $string .= '';
        return $string;
    }

    function armarMenuPrincipal($mysqli, $tipo) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/release.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/EncodingNew.php');

        //
        if (!defined('TIPO_EMPRESA')) {
            define('TIPO_EMPRESA', '');
        }
        if (!defined('TIPO_EMPRESA1')) {
            define('TIPO_EMPRESA1', '');
        }
        if (!defined('TIPO_EMPRESA2')) {
            define('TIPO_EMPRESA2', '');
        }

        //
        $retornar = '';
        $retornar .= '<?xml version="1.0" encoding="utf8"?>' . chr(13);
        $retornar .= '<menu>' . chr(13);
        $retornar .= '<item id="Inicial" text="Inicio"/>' . chr(13);

        $okEntrada = "NO";
        if (BANDEJA_ENTRADA_ACTIVAR == 'S') {
            if (($_SESSION["generales"]["validado"] == 'SI') && (trim($_SESSION["generales"]["codigousuario"]) != '')) {
                if (($_SESSION["generales"]["tipousuario"] != '00') && ($_SESSION["generales"]["tipousuario"] != '06')) {
                    if ($_SESSION["generales"]["tipousuario"] == '01') {
                        $mostrar = 'S';
                    } else {
                        $mostrar = 'N';
                        foreach ($_SESSION["generales"] as $tag => $valor) {
                            if (substr($tag, 0, 4) == 'tags') {
                                if ($valor == 'S') {
                                    $mostrar = 'S';
                                }
                            }
                        }
                    }
                    if ($mostrar == 'S') {
                        $okEntrada = 'SI';
                    }
                }
            }
        }

        if ($okEntrada == 'SI') {
            $arrGpTags = retornarRegistrosMysqliApi($mysqli, 'bas_grupostagsbandejaentrada', "1=1", "id");
            $retornar .= '<item id="Bandejas" text="Bandejas">' . chr(13);
            foreach ($arrGpTags as $gp) {
                $incluir = 'no';
                if (!isset($gp["tipoempresa"])) {
                    $gp["tipoempresa"] = '*';
                }
                if ($gp["tipoempresa"] == '*') {
                    $incluir = 'si';
                }
                if ($gp["tipoempresa"] == 'cam*') {
                    if (
                            (TIPO_EMPRESA == 'cam') || (TIPO_EMPRESA == 'cam1') || (TIPO_EMPRESA == 'cam2') || (TIPO_EMPRESA == 'cam3') ||
                            (TIPO_EMPRESA1 == 'cam') || (TIPO_EMPRESA1 == 'cam1') || (TIPO_EMPRESA1 == 'cam2') || (TIPO_EMPRESA1 == 'cam3') ||
                            (TIPO_EMPRESA2 == 'cam') || (TIPO_EMPRESA2 == 'cam1') || (TIPO_EMPRESA2 == 'cam2') || (TIPO_EMPRESA2 == 'cam3')
                    ) {
                        $incluir = 'si';
                    }
                }
                if ($incluir == 'no') {
                    $list = explode(",", $gp["tipoempresa"]);
                    if (array_key_exists(TIPO_EMPRESA, $list) ||
                            array_key_exists(TIPO_EMPRESA1, $list) ||
                            array_key_exists(TIPO_EMPRESA2, $list)) {
                        $incluir = 'si';
                    }
                }
                if ($incluir == 'si') {
                    $retornar .= '<item id="' . $gp["id"] . '" text="' . EncodingNew::fixUTF8($gp["descripcion"]) . '">' . chr(13);
                    $arrTags = retornarRegistrosMysqliApi($mysqli, $_SESSION["generales"]["bastagsbandejaentrada"], "idgrupo='" . $gp["id"] . "' and idestado='A'", "idorden");
                    foreach ($arrTags as $tag) {
                        if (($_SESSION["generales"]["tipousuario"] == '01') ||
                                ($_SESSION["generales"]["tags" . $tag["id"]] == 'S')) {
                            $retornar .= '<item id="B-' . $tag["id"] . '" text="' . EncodingNew::fixUTF8($tag["descripcion"]) . '"/>' . chr(13);
                        }
                    }
                    $retornar .= '</item>';
                }
            }
            $retornar .= '</item>';
            unset($arrGpTags);
            unset($arrTags);
        }


        $excluir = '';
        if ($_SESSION["generales"]["tipomenu"] != 'MOVIL') {
            $excluir = " and substring(idopcion,1,5) <> '00.00' ";
        }


        //
        $retornar .= \funcionesGenerales::filtrarCaracteres('<item id="Modulos" text="Módulos">') . chr(13);

        if ($_SESSION["generales"]["tipousuario"] != '01') {
            $grps = retornarRegistrosMysqliApi($mysqli, 'bas_opciones', "idtipoopcion='G' and mostrarmenuaplicacion = 'S'" . $excluir, "idopcion");
        } else {
            $grps = retornarRegistrosMysqliApi($mysqli, 'bas_opciones', "idtipoopcion='G'" . $excluir, "idopcion");
        }
        foreach ($grps as $g) {
            $g["nombre"] = \funcionesGenerales::utf8_decode($g["nombre"]);
            $incluir = 'no';
            if ($_SESSION["generales"]["tipousuario"] == '01') {
                $incluir = 'si';
            }
            if ($_SESSION["generales"]["tipousuario"] == '00') {
                if ($g["tipousuariopublico"] == 'X') {
                    $incluir = 'si';
                }
            }
            if ($_SESSION["generales"]["tipousuario"] == '02') {
                if ($g["tipousuarioadministrativo"] == 'X') {
                    $incluir = 'si';
                }
            }
            if ($_SESSION["generales"]["tipousuario"] == '05') {
                if ($g["tipousuarioregistro"] == 'X') {
                    $incluir = 'si';
                }
            }
            if ($_SESSION["generales"]["tipousuario"] == '06') {
                if ($g["tipousuarioexterno"] == 'X') {
                    $incluir = 'si';
                }
            }

            if ($incluir == 'si') {
                $excluir = '';
                if ($_SESSION["generales"]["tipomenu"] != 'MOVIL') {
                    $excluir = " and substring(idopcion,1,5) <> '00.00' ";
                }
                $cuantosIncluir = 0;
                if ($_SESSION["generales"]["tipousuario"] != '01') {
                    $agrps = retornarRegistrosMysqliApi($mysqli, 'bas_opciones', "idtipoopcion='A' and (idopcion between '" . $g["idopcion"] . "' and '" . $g["idopcion"] . "9999999') and mostrarmenuaplicacion = 'S'" . $excluir, "idopcion");
                } else {
                    $agrps = retornarRegistrosMysqliApi($mysqli, 'bas_opciones', "idtipoopcion='A' and (idopcion between '" . $g["idopcion"] . "' and '" . $g["idopcion"] . "9999999') " . $excluir, "idopcion");
                }
                foreach ($agrps as $a) {
                    if ($a["estado"] == '1') {
                        $incluira = 'no';
                        if ($_SESSION["generales"]["tipousuario"] == '00') {
                            if ($a["tipousuariopublico"] == 'X') {
                                $incluira = 'si';
                            }
                        }
                        if ($_SESSION["generales"]["tipousuario"] == '01') {
                            $incluira = 'si';
                        }
                        if ($_SESSION["generales"]["tipousuario"] == '02') {
                            if ($a["tipousuarioadministrativo"] == 'X') {
                                $incluira = 'si';
                            }
                        }
                        if ($_SESSION["generales"]["tipousuario"] == '05') {
                            if ($a["tipousuarioregistro"] == 'X') {
                                $incluira = 'si';
                            }
                        }
                        if ($_SESSION["generales"]["tipousuario"] == '06') {
                            if ($a["tipousuarioexterno"] == 'X') {
                                $incluira = 'si';
                            }
                        }
                        if ($incluira == 'si') {
                            if (($a["tipousuariopublico"] != 'X') && ($_SESSION["generales"]["tipousuario"] != '01')) {
                                if (\funcionesGenerales::validarPermisoEjecucion($mysqli, $_SESSION["generales"]["codigousuario"], $a["idopcion"])) {
                                    $incluira = 'si';
                                } else {
                                    $incluira = 'no';
                                }
                            }
                            if ($incluira == 'si') {
                                $cuantosIncluir++;
                            }
                        }
                    }
                }
                if ($cuantosIncluir == 0) {
                    $incluir = 'no';
                }
            }

            if ($incluir == 'si') {

                $excluir = '';
                if ($_SESSION["generales"]["tipomenu"] != 'MOVIL') {
                    $excluir = " and substring(idopcion,1,5) <> '00.00' ";
                }

                $retornar .= \funcionesGenerales::filtrarCaracteres('<item id="' . 'MG-' . $g["idopcion"] . '" text="' . $g["idopcion"] . ' - ' . $g["nombre"] . '">') . chr(13);

                if ($_SESSION["generales"]["tipousuario"] != '01') {
                    $sgrps = retornarRegistrosMysqliApi($mysqli, 'bas_opciones', "idtipoopcion='S' and (idopcion between '" . $g["idopcion"] . "' and '" . $g["idopcion"] . "9999999') and mostrarmenuaplicacion = 'S'" . $excluir, "idopcion");
                } else {
                    $sgrps = retornarRegistrosMysqliApi($mysqli, 'bas_opciones', "idtipoopcion='S' and (idopcion between '" . $g["idopcion"] . "' and '" . $g["idopcion"] . "9999999')" . $excluir, "idopcion");
                }
                foreach ($sgrps as $s) {

                    $s["nombre"] = str_replace("<br>", " ", EncodingNew::fixUTF8($s["nombre"]));
                    $incluirs = 'no';
                    if ($s["estado"] == '1') {
                        if ($_SESSION["generales"]["tipousuario"] == '01') {
                            $incluirs = 'si';
                        }
                        if ($_SESSION["generales"]["tipousuario"] == '00') {
                            if ($s["tipousuariopublico"] == 'X') {
                                $incluirs = 'si';
                            }
                        }
                        if ($_SESSION["generales"]["tipousuario"] == '02') {
                            if ($s["tipousuarioadministrativo"] == 'X') {
                                $incluirs = 'si';
                            }
                        }
                        if ($_SESSION["generales"]["tipousuario"] == '05') {
                            if ($s["tipousuarioregistro"] == 'X') {
                                $incluirs = 'si';
                            }
                        }
                        if ($_SESSION["generales"]["tipousuario"] == '06') {
                            if ($s["tipousuarioexterno"] == 'X') {
                                $incluirs = 'si';
                            }
                        }
                    }

                    if ($incluirs == 'si') {
                        $cuantosIncluir = 0;
                        $excluir = '';
                        if ($_SESSION["generales"]["tipomenu"] != 'MOVIL') {
                            $excluir = " and substring(idopcion,1,5) <> '00.00' ";
                        }

                        $agrps = retornarRegistrosMysqliApi($mysqli, 'bas_opciones', "idtipoopcion='A' and (idopcion between '" . $s["idopcion"] . "' and '" . $s["idopcion"] . "9999999')" . $excluir, "idopcion");
                        foreach ($agrps as $a) {
                            $incluira = 'no';
                            if ($a["estado"] == '1') {
                                if ($_SESSION["generales"]["tipousuario"] == '00') {
                                    if ($a["tipousuariopublico"] == 'X') {
                                        $incluira = 'si';
                                    }
                                }
                                if ($_SESSION["generales"]["tipousuario"] == '01') {
                                    $incluira = 'si';
                                }
                                if ($_SESSION["generales"]["tipousuario"] == '02') {
                                    if ($a["tipousuarioadministrativo"] == 'X') {
                                        $incluira = 'si';
                                    }
                                }
                                if ($_SESSION["generales"]["tipousuario"] == '05') {
                                    if ($a["tipousuarioregistro"] == 'X') {
                                        $incluira = 'si';
                                    }
                                }
                                if ($_SESSION["generales"]["tipousuario"] == '06') {
                                    if ($a["tipousuarioexterno"] == 'X') {
                                        $incluira = 'si';
                                    }
                                }
                                if ($incluira == 'si') {
                                    if (($a["tipousuariopublico"] != 'X') && ($_SESSION["generales"]["tipousuario"] != '01')) {
                                        if (\funcionesGenerales::validarPermisoEjecucion($mysqli, $_SESSION["generales"]["codigousuario"], $a["idopcion"])) {
                                            $incluira = 'si';
                                        } else {
                                            $incluira = 'no';
                                        }
                                    }
                                    if ($incluira == 'si') {
                                        $cuantosIncluir++;
                                    }
                                }
                            }
                        }
                        if ($cuantosIncluir == 0) {
                            $incluirs = 'no';
                        }
                    }

                    //
                    if ($incluirs == 'si') {
                        $excluir = '';
                        if ($_SESSION["generales"]["tipomenu"] != 'MOVIL') {
                            $excluir = " and substring(idopcion,1,5) <> '00.00' ";
                        }

                        $retornar .= \funcionesGenerales::filtrarCaracteres('<item id="' . 'MS-' . $s["idopcion"] . '" text="' . $s["idopcion"] . ' - ' . EncodingNew::fixUTF8($s["nombre"]) . '">') . chr(13);
                        if ($_SESSION["generales"]["tipousuario"] != '01') {
                            $agrps = retornarRegistrosMysqliApi($mysqli, 'bas_opciones', "idtipoopcion='A' and (idopcion between '" . $s["idopcion"] . "' and '" . $s["idopcion"] . "9999999' and mostrarmenuaplicacion = 'S')" . $excluir, "idopcion");
                        } else {
                            $agrps = retornarRegistrosMysqliApi($mysqli, 'bas_opciones', "idtipoopcion='A' and (idopcion between '" . $s["idopcion"] . "' and '" . $s["idopcion"] . "9999999')" . $excluir, "idopcion");
                        }
                        foreach ($agrps as $a) {
                            if ($a["estado"] == '1') {
                                $a["nombre"] = str_replace("<br>", " ", EncodingNew::fixUTF8($a["nombre"]));
                                $incluira = 'no';
                                if ($_SESSION["generales"]["tipousuario"] == '00') {
                                    if ($a["tipousuariopublico"] == 'X') {
                                        $incluira = 'si';
                                    }
                                }
                                if ($_SESSION["generales"]["tipousuario"] == '01') {
                                    $incluira = 'si';
                                }
                                if ($_SESSION["generales"]["tipousuario"] == '02') {
                                    if ($a["tipousuarioadministrativo"] == 'X') {
                                        $incluira = 'si';
                                    }
                                }
                                if ($_SESSION["generales"]["tipousuario"] == '05') {
                                    if ($a["tipousuarioregistro"] == 'X') {
                                        $incluira = 'si';
                                    }
                                }
                                if ($_SESSION["generales"]["tipousuario"] == '06') {
                                    if ($a["tipousuarioexterno"] == 'X') {
                                        $incluira = 'si';
                                    }
                                }
                                if ($incluira == 'si') {
                                    if (($a["tipousuariopublico"] != 'X') && ($_SESSION["generales"]["tipousuario"] != '01')) {
                                        if (\funcionesGenerales::validarPermisoEjecucion($mysqli, $_SESSION["generales"]["codigousuario"], $a["idopcion"])) {
                                            $incluira = 'si';
                                        } else {
                                            $incluira = 'no';
                                        }
                                    }
                                    if ($incluira == 'si') {
                                        $retornar .= \funcionesGenerales::filtrarCaracteres('<item id="' . 'O-' . $a["idopcion"] . '" text="' . $a["idopcion"] . ' - ' . EncodingNew::fixUTF8($a["nombre"]) . '"/>') . chr(13);
                                    }
                                }
                            }
                        }

                        $retornar .= '</item>' . chr(13);
                    }
                    // }
                }

                $retornar .= '</item>' . chr(13);
            }
        }

        //
        $retornar .= '</item>' . chr(13);

        //
        $resultados = retornarListaOpcionesMysqliApi($mysqli);

        //
        // $fx = fopen ("/opt/sitios/sii/tmp/menuMuestra.txt","wb");
        //
        $i = -1;
        $gru = -1;
        $sub = -1;
        $resultados1 = $resultados;
        foreach ($resultados as $res) {
            $i++;
            $ok = 'no';
            if ($res["tipoempresa"] == '*') {
                $ok = 'si';
            } else {
                // fwrite ($fx,$res["idopcion"] . ' - ' . $res["tipoempresa"] . "\r\n");
                $indiceTipo = explode(",", $res["tipoempresa"]);
                foreach ($indiceTipo as $l) {
                    if ($l == TIPO_EMPRESA ||
                            $l == TIPO_EMPRESA1 ||
                            $l == TIPO_EMPRESA2
                    ) {
                        $ok = 'si';
                    }
                }
            }
            if ($ok == 'no') {
                $indiceTipo = explode(",", $res["tipoempresa"]);
                foreach ($indiceTipo as $t) {
                    switch ($t) {
                        case "cam":
                            $ok = 'si';
                            break;
                        case "cam1":
                            $ok = 'si';
                            break;
                        case "cam2":
                            $ok = 'si';
                            break;
                        case "cam3":
                            $ok = 'si';
                            break;
                    }
                }
            }

            if ($ok == 'si') {
                if ($res["tipo"] == "G") {
                    $gru = $i;
                }
                if ($res["tipo"] == "S") {
                    $sub = $i;
                }
                if ($res["tipo"] == "A") {
                    if (($_SESSION["generales"]["tipousuario"] != '00') && ($_SESSION["generales"]["tipousuario"] != '01') && ($_SESSION["generales"]["tipousuario"] != '06')) {
                        $ejecutar = \funcionesGenerales::validarPermisoEjecucion($mysqli, $_SESSION["generales"]["codigousuario"], $res["idopcion"]);
                    } else {
                        $ejecutar = true;
                    }
                    if ($ejecutar) {
                        $resultados1[$i]["ejecutar"] = 1;
                        if ($gru != -1) {
                            $resultados1 [$gru]["cantidad"]++;
                        }
                        if ($sub != -1) {
                            $resultados1 [$sub]["cantidad"]++;
                        }
                    }
                }
            }
        }
        // fclose ($fx);
        $resultados = $resultados1;
        unset($resultados1);

        if ($_SESSION["generales"]["tipomenu"] != 'ICONOS') {
            $retornar .= '<item id="Noticias" text="Documentación"/>' . chr(13);
        }

        if ($_SESSION["generales"]["tipomenu"] != 'ICONOS') {
            if (($_SESSION["generales"]["validado"] == 'SI') && (trim($_SESSION["generales"]["codigousuario"]) != '')) {
                $retornar .= '<item id="CambiarClave" text="Cambio de clave"/>' . chr(13);
            }
        }

        if (!defined('ACTIVADO_MODULO_SUSCRIPCIONES')) {
            define('ACTIVADO_MODULO_SUSCRIPCIONES', 'N');
        }

        //T&eacute;rminos de uso y declaraci&oacute;n de privacidad
        $retornar .= '<item id="Terminos" text="Políticas de información">' . chr(13);
        // $retornar .= '<item id="TerminosUso" text="Términos de Uso del Servicio"/>' . chr(13);
        $retornar .= '<item id="DeclaracionPrivacidad" text="Declaración de Privacidad"/>' . chr(13);
        $retornar .= '<item id="DatosPersonales" text="Política Tratamiento Información"/>' . chr(13);
        $retornar .= '</item>' . chr(13);

        // men&uacute; r&aacute;pido
        // Solo para usuarios registrados y que no sean usuarios externos        
        if ($_SESSION["generales"]["tipomenu"] != 'ICONOS') {
            $menrap = retornarRegistrosMysqliApi($mysqli, 'menu_rapido', "idorden between '" . $_SESSION["generales"]["tipousuario"] . "' and '" . $_SESSION["generales"]["tipousuario"] . ".99'", "idorden");
            if (($menrap) && (!empty($menrap))) {
                $retornar .= '<item id="MenuRapido" text="Menú">' . chr(13);
                foreach ($menrap as $mr) {
                    if (strlen($mr["idorden"]) != 2) {
                        if (trim($mr["script"]) != '') {
                            $retornar .= '<item id="MR-' . $mr["idorden"] . '" text="' . $mr["titulo"] . '"/>' . chr(13);
                        }
                    }
                }
                $retornar .= '</item>' . chr(13);
            }

            // $retornar .= '</item>' . chr(13);
        }


        // Datos de control
        // $retornar .= '<item id="blanco2" text="  "/>' . chr(13);
        $retornar .= '<item id="Usuario" text="' . $_SESSION["generales"]["codigousuario"] . '(' . $_SESSION["generales"]["tipousuario"] . ')"/>' . chr(13);
        $retornar .= '<item id="Version" text="' . VERSION_RELEASE . '"/>' . chr(13);
        $retornar .= '<item id="Salir" text="Salir"/>' . chr(13);
        $retornar .= '</menu>';

        $f = fopen(PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $_SESSION["generales"]["codigousuario"] . '.xml', "w");
        fwrite($f, $retornar);
        fclose($f);

        //
        $retornar = '';
        $retornar .= '<?xml version="1.0" encoding="utf8"?>' . chr(13);
        $retornar .= '<menu>' . chr(13);
        if (!defined('PLANTILLA_HTML') || PLANTILLA_HTML == 'normal') {
            $retornar .= '<item id="Inicial" text="Inicio"/>' . chr(13);
        }
        $retornar .= '<item id="Terminos" text="Políticas de información">' . chr(13);
        // $retornar .= '<item id="TerminosUso" text="Términos de Uso del Servicio"/>' . chr(13);
        $retornar .= '<item id="DeclaracionPrivacidad" text="Declaración de Privacidad"/>' . chr(13);
        $retornar .= '<item id="DatosPersonales" text="Política Tratamiento Información"/>' . chr(13);
        $retornar .= '</item>' . chr(13);
        $retornar .= '<item id="Usuario" text="Usuario: ' . $_SESSION["generales"]["codigousuario"] . '(' . $_SESSION["generales"]["tipousuario"] . ')"/>' . chr(13);
        $retornar .= '<item id="Version" text="Versión : ' . VERSION_RELEASE . '"/>' . chr(13);
        if (!defined('PLANTILLA_HTML') || PLANTILLA_HTML == 'normal') {
            $retornar .= '<item id="Salir" text="Salir"/>' . chr(13);
        }
        $retornar .= '</menu>';

        $f = fopen(PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $_SESSION["generales"]["codigousuario"] . '-res.xml', "w");
        fwrite($f, $retornar);
        fclose($f);

        return true;
    }

    /**
     * 
     * @param type $tabla
     * @param type $height
     * @return string
     */
    function armarTabla($tabla = array(), $height = null) {
        //
        $string = '';
        $string .= '<div class="container-fluid">';
        if (isset($tabla["titulo"]) && $tabla["titulo"] != '') {
            $string .= '<div class="card">';
            $string .= '<h4 class="card-title"><center>' . $tabla["titulo"] . '</center></h4>';
            if (isset($tabla["subtitulo"]) && $tabla["subtitulo"] != '') {
                $string .= '<h5 class="card-title"><center>' . $tabla["subtitulo"] . '</center></h5>';
            }
            $string .= '</div>';
            $string .= '<br>';
        }

        if ($height === null) {
            $string .= '<div class="table-responsive">';
        } else {
            $string .= '<div class="table-responsive" style="height: ' . $height . '%;">';
        }
        $string .= '<table class="table table-fixed1">';
        $string .= '<thead class="thead-dark">';
        $string .= '<tr>';
        $i = -1;
        foreach ($tabla["headers"] as $h) {
            $i++;
            $string .= '<th scope="' . $tabla["scope"][$i] . '">' . $tabla["headers"][$i] . '</th>';
        }
        $string .= '</tr>';
        $string .= '</thead>';
        $string .= '<tbody style="height:10px !important; overflow: scroll; ">';
        foreach ($tabla["rows"] as $r) {
            $string .= '<tr>';
            foreach ($r as $key => $data) {
                if ($data["typecolhtml"] == 'th') {
                    $string .= '<th scope="' . $data["scope"] . '">';
                }
                if ($data["typecolhtml"] == 'td') {
                    $string .= '<td>';
                }

                //
                if ($data["typeform"] == '') {
                    $tami = '';
                    $tamf = '';
                    if (isset($data["sizefont"]) && $data["sizefont"] != '') {
                        $tami = '<' . $data["sizefont"] . '>';
                        $tamf = '</' . $data["sizefont"] . '>';
                    }
                    if (isset($data["align"]) && $data["align"] != '') {
                        $align = 'align="' . $data["align"] . '"';
                    }

                    $string .= '<p ' . $align . '>' . $tami . $data["value"] . $tamf . '</p>';
                    if (isset($data["idhidden"]) && $data["idhidden"] != '') {
                        $string .= '<input type="hidden" class="form-control" id="' . $data["idhidden"] . '" name="' . $data["idhidden"] . '" value="' . $data["valuehidden"] . '">';
                    }
                }

                //
                if ($data["typeform"] == 'checkbox') {
                    $string .= '<div class="form-check"><center>';
                    if ($data["value"] == '1') {
                        $string .= '<input type="checkbox" class="form-check-input" id="' . $data["id"] . '" checked>';
                    } else {
                        $string .= '<input type="checkbox" class="form-check-input" id="' . $data["id"] . '">';
                    }
                    if (isset($data["idhidden"]) && $data["idhidden"] != '') {
                        $string .= '<input type="hidden" class="form-control" id="' . $data["idhidden"] . '" name="' . $data["idhidden"] . '" value="' . $data["valuehidden"] . '">';
                    }
                    $string .= '</center></div>';
                }

                //
                if ($data["typeform"] == 'checkboxprotected') {
                    $string .= '<div class="form-check"><center>';
                    if ($data["value"] == '1') {
                        $string .= '<input type="checkbox" class="form-check-input" id="' . $data["id"] . '" checked readonly="readonly" onclick="javascript: return false;">';
                    } else {
                        $string .= '<input type="checkbox" class="form-check-input" id="' . $data["id"] . '" readonly="readonly" onclick="javascript: return false;">';
                    }
                    if (isset($data["idhidden"]) && $data["idhidden"] != '') {
                        $string .= '<input type="hidden" class="form-control" id="' . $data["idhidden"] . '" name="' . $data["idhidden"] . '" value="' . $data["valuehidden"] . '">';
                    }
                    $string .= '</center></div>';
                }

                //
                if ($data["typeform"] == 'select') {
                    $string .= '<div class="form-group">
                        <select class="form-control" id="' . $data["id"] . '" name="' . $data["id"] . '">';
                    if ($data["value"] == '') {
                        $string .= '<option value="" selected>Seleccione</option>';
                    } else {
                        $string .= '<option value="">Seleccione</option>';
                    }
                    foreach ($data["arregloselect"] as $v => $d) {
                        if ($data["value"] == $v) {
                            $string .= '<option value="' . $v . '" selected>' . $d . '</option>';
                        } else {
                            $string .= '<option value="' . $v . '">' . $d . '</option>';
                        }
                    }
                    $string .= '</select>';
                    $string .= '</div>';
                }

                //
                if ($data["typeform"] == 'inputtext') {
                    $string .= '<div class="form-group">';
                    $string .= '<input type="text" class="form-control" id="' . $data["id"] . '" name="' . $data["id"] . '" value="' . $data["value"] . '">';
                    if (isset($data["idhidden"]) && $data["idhidden"] != '') {
                        $string .= '<input type="hidden" class="form-control" id="' . $data["idhidden"] . '" name="' . $data["idhidden"] . '" value="' . $data["valuehidden"] . '">';
                    }
                    $string .= '</div>';
                }

                if ($data["typecolhtml"] == 'th') {
                    $string .= '</th>';
                }
                if ($data["typecolhtml"] == 'td') {
                    $string .= '</td>';
                }
            }
            $string .= '</tr>';
        }
        $string .= '</tbody>';
        $string .= '</table>';
        $string .= '</div>';
        $string .= '</div>';
        return $string;
    }

    /**
     * 
     * @param type $id
     * @param type $titulo
     * @param type $tabla
     * @param type $collapse
     * @param type $tizq
     * @param type $tder
     * @param type $aizq
     * @param type $ader
     * @return string
     */
    function armarTableList($id, $titulo, $tabla = array(), $collapse = 'si', $tizq = 50, $tder = 50, $aizq = 'left', $ader = 'left', $border = 'si') {
        //
        $string = '';
        if ($collapse == 'si') {
            $string .= '<p align="left"><a href="javascript:colapsarShow(\'' . $id . '\');"><i class="fa fa-plus-square" aria-hidden="true"></i></a>&nbsp;&nbsp;<a href="javascript:colapsarHide(\'' . $id . '\');"><i class="fa fa-minus-square" aria-hidden="true"></i></a>&nbsp;&nbsp;' . $titulo . '</p>';
        } else {
            $string .= '<p align="left">' . $titulo . '</p>';
        }
        if ($collapse == 'si') {
            $string .= '<div class="collapse" id="' . $id . '">';
        }
        $string .= '<div class="card">';
        $string .= '<div class="container-fluid">';
        $string .= '<div class="table-responsive">';
        if ($border == 'si') {
            $string .= '<table class="table table-sm table-condensed">';
        } else {
            $string .= '<table class="table table-sm table-condensed border-0">';
        }
        $string .= '<tbody style="height:10px !important; overflow: scroll; ">';
        foreach ($tabla as $r) {
            $string .= '<tr><td style="width: ' . $tizq . '%;" align="' . $aizq . '">' . $r[0] . '</td><td style="width: ' . $tder . '%;" align="' . $ader . '">' . $r[1] . '</td></tr>';
        }
        $string .= '</tbody>';
        $string .= '</table>';
        $string .= '</div>';
        $string .= '</div>';
        $string .= '</div>';
        if ($collapse == 'si') {
            $string .= '</div>';
        }
        return $string;
    }

    function armarTableListMultiCol($id, $titulo, $tabla = array(), $collapse = 'si', $tcol = array(), $acol = array(), $border = 'si') {
        //
        $string = '';
        if ($collapse == 'si') {
            $string .= '<p align="left"><a href="javascript:colapsarShow(\'' . $id . '\');"><i class="fa fa-plus-square" aria-hidden="true"></i></a>&nbsp;&nbsp;<a href="javascript:colapsarHide(\'' . $id . '\');"><i class="fa fa-minus-square" aria-hidden="true"></i></a>&nbsp;&nbsp;' . $titulo . '</p>';
        } else {
            $string .= '<p align="left">' . $titulo . '</p>';
        }
        if ($collapse == 'si') {
            $string .= '<div class="collapse" id="' . $id . '">';
        }
        $string .= '<div class="card">';
        $string .= '<div class="container-fluid">';
        $string .= '<div class="table-responsive">';
        if ($border == 'si') {
            $string .= '<table class="table table-sm table-condensed">';
        } else {
            $string .= '<table class="table table-sm table-condensed border-0">';
        }
        $string .= '<tbody style="height:10px !important; overflow: scroll; ">';
        if (count($tcol) == 3) {
            foreach ($tabla as $r) {
                if ($r[0] != '' && ($r[1] != '' || $r[2] != '')) {
                    $string .= '<tr>';
                    $icol = -1;
                    foreach ($r as $c) {
                        $icol++;
                        $string .= '<td style="width: ' . $tcol[$icol] . '%;" align="' . $acol[$icol] . '">' . $c . '</td>';
                    }
                    $string .= '</tr>';
                }
            }
        } else {
            foreach ($tabla as $r) {
                $string .= '<tr>';
                $icol = -1;
                foreach ($r as $c) {
                    $icol++;
                    $string .= '<td style="width: ' . $tcol[$icol] . '%;" align="' . $acol[$icol] . '">' . $c . '</td>';
                }
                $string .= '</tr>';
            }
        }
        $string .= '</tbody>';
        $string .= '</table>';
        $string .= '</div>';
        $string .= '</div>';
        $string .= '</div>';
        if ($collapse == 'si') {
            $string .= '</div>';
        }
        return $string;
    }

    /**
     * 
     * @param type $id
     * @param type $tabla
     * @return string
     */
    function armarDataTable($id = 'table', $tabla = array()) {
        //
        $string = '';
        $string .= '<div class="container-fluid">';
        if (isset($tabla["titulo"]) && $tabla["titulo"] != '') {
            $string .= '<div class="card">';
            $string .= '<h4 class="card-title"><center>' . $tabla["titulo"] . '</center></h4>';
            if (isset($tabla["subtitulo"]) && $tabla["subtitulo"] != '') {
                $string .= '<h4 class="card-title"><center>' . $tabla["subtitulo"] . '</center></h4>';
            }
            $string .= '</div>';
            $string .= '<br>';
        }
        if (isset($tabla["tabletitle"]) && $tabla["tabletitle"] != '') {
            $string .= '<div class="card">';
            $string .= '<h4 class="card-title"><center>' . $tabla["tabletitle"] . '</center></h4>';
            if (isset($tabla["tablesubtitle"]) && $tabla["tablesubtitle"] != '') {
                $string .= '<h5 class="card-title"><center>' . $tabla["tablesubtitle"] . '</center></h5>';
            }
            $string .= '</div>';
            $string .= '<br>';
        }

        $string .= '<center>';
        if (isset($tabla["subtitulo"]) && $tabla["subtitulo"] != '') {
            $string .= $this->armarLineaTextoInformativa($tabla["subtitulo"], 'left', 'h6');
        }
        if (isset($tabla["filtro"]) && $tabla["filtro"] != '') {
            $string .= $tabla["filtro"];
        }

        $string .= '<div class="table-responsive">';
        $string .= '<table id="' . $id . '" class="display dt-responsive dt-nowrap" role="grid" style="width:95%">';
        $string .= '<thead>';
        $string .= '<tr>';
        $i = -1;
        foreach ($tabla["headers"] as $h) {
            $i++;
            $string .= '<th>' . $tabla["headers"][$i] . '</th>';
        }
        $string .= '</tr>';
        $string .= '</thead>';
        $string .= '<tbody>';
        if (!empty($tabla["rows"])) {
            foreach ($tabla["rows"] as $r) {
                $string .= '<tr>';
                foreach ($r as $key => $data) {

                    //
                    $bgcolor = '';
                    if (isset($data["bgcolor"]) && $data["bgcolor"] != '') {
                        $bgcolor = ' class="' . $data["bgcolor"] . '" ';
                    }

                    if (isset($data["bgcolor1"]) && $data["bgcolor1"] != '') {
                        $bgcolor = ' ' . $data["bgcolor1"] . ' ';
                    }

                    //
                    if ($data["typecolhtml"] == 'th') {
                        $string .= '<td ' . $bgcolor . '>';
                    }

                    //
                    if ($data["typecolhtml"] == 'td') {
                        $string .= '<td ' . $bgcolor . '>';
                    }

                    //
                    if ($data["typeform"] == '') {
                        $tami = '';
                        $tamf = '';
                        if (isset($data["sizefont"]) && $data["sizefont"] != '') {
                            $tami = '<' . $data["sizefont"] . '>';
                            $tamf = '</' . $data["sizefont"] . '>';
                        }
                        $align = '';
                        if (isset($data["align"]) && $data["align"] != '') {
                            $align = ' style="text-align:' . $data["align"] . '"';
                        }
                        $bgcolor = '';
                        if (isset($data["bgcolor"]) && $data["bgcolor"] != '') {
                            $bgcolor = ' class="' . $data["bgcolor"] . '" ';
                        }
                        if (!isset($data["id"]) || $data["id"] == '') {
                            $string .= '<p' . $align . $bgcolor . '>' . $tami . $data["value"] . $tamf . '</p>';
                        } else {
                            $string .= '<div id="' . $data["id"] . '">';
                            $string .= '<p' . $align . $bgcolor . '>' . $tami . $data["value"] . $tamf . '</p>';
                            $string .= '</div>';
                        }
                        if (isset($data["idhidden"]) && $data["idhidden"] != '') {
                            $string .= '<input type="hidden" class="form-control" id="' . $data["idhidden"] . '" name="' . $data["idhidden"] . '" value="' . $data["valuehidden"] . '">';
                        }
                    }

                    //
                    if ($data["typeform"] == 'img') {
                        $string .= '<div class="form-check"><center>';
                        $string .= '<img src="' . base64_decode($data["value"]) . '" width="' . $data["sizeimg"] . '" />';
                        $string .= '</center></div>';
                    }

                    //
                    if ($data["typeform"] == 'checkboxprotected') {
                        $string .= '<div class="form-check"><center>';
                        if ($data["value"] == '1') {
                            $string .= '<input type="checkbox" class="form-check-input" id="' . $data["id"] . '" name="' . $data["id"] . '" checked readonly="readonly" onclick="javascript: return false;">';
                        } else {
                            $string .= '<input type="checkbox" class="form-check-input" id="' . $data["id"] . '" name="' . $data["id"] . '" readonly="readonly" onclick="javascript: return false;">';
                        }
                        if (isset($data["idhidden"]) && $data["idhidden"] != '') {
                            $string .= '<input type="hidden" class="form-control" id="' . $data["idhidden"] . '" name="' . $data["idhidden"] . '" value="' . $data["valuehidden"] . '">';
                        }
                        $string .= '</center></div>';
                    }

                    //
                    if ($data["typeform"] == 'checkbox') {
                        $string .= '<div class="form-check"><center>';
                        if ($data["value"] == '1' || $data["value"] == 'S' || $data["value"] == 'si') {
                            $string .= '<input type="checkbox" class="form-check-input" id="' . $data["id"] . '" name="' . $data["id"] . '" checked onclick="javascript: return true;">';
                        } else {
                            $string .= '<input type="checkbox" class="form-check-input" id="' . $data["id"] . '" name="' . $data["id"] . '" unchecked onclick="javascript: return true;">';
                        }
                        if (isset($data["idhidden"]) && $data["idhidden"] != '') {
                            $string .= '<input type="hidden" class="form-control" id="' . $data["idhidden"] . '" name="' . $data["idhidden"] . '" value="' . $data["valuehidden"] . '">';
                        }
                        $string .= '</center></div>';
                    }


                    //
                    if ($data["typeform"] == 'inputtext') {
                        $string .= '<div class="form-group">';
                        $string .= '<input type="text" class="form-control" id="' . $data["id"] . '" name="' . $data["id"] . '" value="' . $data["value"] . '">';
                        if (isset($data["idhidden"]) && $data["idhidden"] != '') {
                            $string .= '<input type="hidden" class="form-control" id="' . $data["idhidden"] . '" name="' . $data["idhidden"] . '" value="' . $data["valuehidden"] . '">';
                        }
                        $string .= '</div>';
                    }

                    //
                    if ($data["typeform"] == 'select') {
                        $string .= '<div class="form-group">
                        <select class="form-control" id="' . $data["id"] . '" name="' . $data["id"] . '">';
                        if ($data["value"] == '') {
                            $string .= '<option value="" selected>Seleccione</option>';
                        } else {
                            $string .= '<option value="">Seleccione</option>';
                        }
                        foreach ($data["arregloselect"] as $v => $d) {
                            if ($data["value"] == $v) {
                                $string .= '<option value="' . $v . '" selected>' . $d . '</option>';
                            } else {
                                $string .= '<option value="' . $v . '">' . $d . '</option>';
                            }
                        }
                        $string .= '</select>';
                        $string .= '</div>';
                    }

                    //                
                    if ($data["typecolhtml"] == 'th') {
                        $string .= '</td>';
                    }

                    //
                    if ($data["typecolhtml"] == 'td') {
                        $string .= '</td>';
                    }
                }
                $string .= '</tr>';
            }
        }
        $string .= '</tbody>';
        $string .= '</table>';
        $string .= '</div>';
        $string .= '</div>';
        $string .= '</center>';
        return $string;
    }

    /**
     * 
     * @param type $txt
     * @return string
     */
    function armarTitulo($txt, $align = 'center') {
        $string = '<div class="card fat">';
        if ($align == 'center') {
            $string .= '<div class="card-body" align="center">';
        } else {
            $string .= '<div class="card-body">';
        }
        $string .= '<h4 class="card-title">' . $txt . '</h4>';
        $string .= '</div>';
        $string .= '</div>';
        return $string;
    }

    function cerrarCol() {
        return "</div>";
    }

    function cerrarRow() {
        return "</div>";
    }

    function cerrarDiv() {
        return "</div>";
    }

    function armarFinFormulario() {
        return "</form>";
    }

    function cerrarFormulario() {
        return "</form>";
    }

    function cerrarTablaBorde() {
        return $this->cerrarPanel();
    }

    function cerrarPanel() {
        $string = '</div>';
        $string .= '</div>';
        return $string;
    }

    function cerrarPanelGeneral() {
        return '</div></center>';
    }

    /**
     * 
     * @param type $txt
     * @return string
     */
    function tituloPanel($txt) {
        $string = '<h5 class="card-title">' . $txt . '</h5>';
        return $string;
    }

    function textosFormasPago($mensajeNoFirmante = 'no') {
        if (doubleval($_SESSION["tramite"]["valortotal"]) != 0) {
            $string = $this->armarLineaTextoInformativa('Formas de pago', 'left', 'h3');
            $string .= '<br>';

            $txt = 'Usted podr&aacute; realizar el pago de su tr&aacute;mite de la(s) siguiente(s) forma(s) :';
            $string .= $this->armarLineaTextoInformativa($txt, 'justify');
            $string .= '<br>';
            $txt = '<strong>PAGO EN CAJA</strong> ';
            $txt .= 'Podr&aacute; realizar el pago directamente en ';
            $txt .= 'nuestras oficinas. para el efecto, imprima los formularios y las solicitudes, f&iacute;rmelos de ';
            $txt .= 'su puño y letra y dir&iacute;jase a una de nuestras oficinas';
            $string .= $this->armarLineaTextoInformativa($txt, 'justify');
            $string .= '<br>';

            if ($mensajeNoFirmante == 'no') {
                $txt = '<strong>PAGO POR MEDIOS ELECTRONICOS</strong> ';
                $txt .= 'Usted podr&aacute; realizar el pago en forma NO PRESENCIAL, ';
                $txt .= 'haciendo uso de las alternativas de pago por medios electr&oacute;nicos. ';
                $txt .= 'que hemos habilitado para su comodidad.<br><br>';
                $txt .= 'El proceso de firmado electr&oacute;nico tiene por objeto garantizar que el tr&aacute;mite ';
                $txt .= 'est&aacute; siendo realizado por el titular del expediente (persona ';
                $txt .= 'natural actuando en nombre propio, el propietario de un establecimiento de comercio o ';
                $txt .= 'que tiene facultades de representaci&oacute;n legal si se trata de ';
                $txt .= 'una persona jur&iacute;dica, una sucusal o una agencia). Igualmente nos permite ';
                $txt .= 'saber que efectivamente est&aacute; enterado y de acuerdo con la informaci&oacute;n ';
                $txt .= 'que se est&aacute; reportando en el tr&aacute;mite.';
                $string .= $this->armarLineaTextoInformativa($txt, 'justify');
                $string .= '<br>';

                //
                if (!defined('ACTIVAR_PAGO_BANCOS')) {
                    define('ACTIVAR_PAGO_BANCOS', 'N');
                }
                if (ACTIVAR_PAGO_BANCOS == 'S') {
                    $txt = '<strong>PAGO EN BANCOS Y CORRESPONSALES BANCARIOS</strong> ';
                    $txt .= 'Usted podr&aacute; realizar el pago en forma NO PRESENCIAL, ';
                    $txt .= 'haciendo uso de las alternativas de pago en bancos y en corresponsales ';
                    $txt .= 'bancarios que le ofrecemos.<br><br> Al igual que con el pago por medios electr&oacute;nicos, ';
                    $txt .= 'debe firmar electr&oacute;nicamente el tr&aacute;mite para poder hacer uso de esta  ';
                    $txt .= 'alternativa.';
                    $string .= $this->armarLineaTextoInformativa($txt, 'justify');
                    $string .= '<br>';
                }

                $txt = 'El pago por medios electr&oacute;nicos, en bancos y en corresponsales bancarios ';
                $txt .= 'que implica firma electr&oacute;nica, NO TIENE PARA USTED NINGUN COSTO ';
                $txt .= 'ADICIONAL, sin embargo es indispensable que verifiquemos la identidad ';
                $txt .= 'de la persona que firma el tr&aacute;mite para darle soporte legal a la ';
                $txt .= 'operaci&oacute;n.';
                $string .= $this->armarLineaTextoInformativa($txt, 'justify');
                $string .= '<br>';
            }

            $txt = 'Dependiendo de la forma de radicación y pago que seleccione, oprima el botón ';
            $txt .= 'correspondiente en la parte inferior de esta pantalla. ';
            $string .= $this->armarLineaTextoInformativa($txt, 'justify');
            $string .= '<br>';

            return $string;
        }

        if (doubleval($_SESSION["tramite"]["valortotal"]) == 0) {
            $string = $this->armarLineaTextoInformativa('Radicación del trámite', 'center', 'h3');
            $string .= '<br>';

            $txt = '<strong>RADICAR EN CAJA</strong> ';
            $txt .= 'Podrá radicar el trámite directamente en ';
            $txt .= 'nuestras oficinas. para el efecto, cuente on los documentos en físico, imprima los formularios (si los hay) y las solicitudes, f&iacute;rmelos de ';
            $txt .= 'su puño y letra y dir&iacute;jase a una de nuestras oficinas';
            $string .= $this->armarLineaTextoInformativa($txt, 'justify');
            $string .= '<br>';

            if ($mensajeNoFirmante == 'no') {
                $txt = '<strong>RADICAR EN FORMA ELECTRÓNICA</strong> ';
                $txt .= 'Usted podr&aacute; realizar la radicación del trámite en forma NO PRESENCIAL, ';
                $txt .= 'para el efecto deberá firmar el trámite en forma electrónica.<br><br>';
                $txt .= 'El proceso de firmado electr&oacute;nico tiene por objeto garantizar que el tr&aacute;mite ';
                $txt .= 'est&aacute; siendo realizado por el titular del expediente (persona ';
                $txt .= 'natural actuando en nombre propio, el propietario de un establecimiento de comercio o ';
                $txt .= 'que tiene facultades de representaci&oacute;n legal si se trata de ';
                $txt .= 'una persona jur&iacute;dica, una sucusal o una agencia). Igualmente nos permite ';
                $txt .= 'saber que efectivamente est&aacute; enterado y de acuerdo con la informaci&oacute;n ';
                $txt .= 'que se est&aacute; reportando en el tr&aacute;mite.';
                $string .= $this->armarLineaTextoInformativa($txt, 'justify');
                $string .= '<br>';
            }

            //
            $txt = 'Dependiendo de la forma de radicación que seleccione, oprima el botón ';
            $txt .= 'correspondiente en la parte inferior de esta pantalla. ';
            $string .= $this->armarLineaTextoInformativa($txt, 'justify');
            $string .= '<br>';

            return $string;
        }
    }

}

?>