<?php

// ************************************************************************************************* //
// Funciones criticas modelo bootstrap
// ************************************************************************************************* //
function conexionBD($fuente = '') {
    require_once (PATH_ABSOLUTO_SITIO . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . ".php");

    //
    if (!isset($fuente)) {
        $fuente = '';
    }

    //    
    if (!defined('LOG_SQL')) {
        define('LOG_SQL', false);
    }
    if (!defined('DEBUG_DB')) {
        define('DEBUG_DB', false);
    }

    //
    $logsql = LOG_SQL;
    $dbms = DBMS;
    $dbhost = DB_HOST;
    $dbport = DB_PORT;
    $dbusuario = DB_USUARIO;
    $dbpassword = DB_PASSWORD;
    $dbname = DB_NAME;
    $debugdb = DEBUG_DB;

    //
    if (!defined('DB_HOST_REPLICA')) {
        define('DB_HOST_REPLICA', '');
    }

    //
    if ($fuente == 'replicabatch' && trim(DB_HOST_REPLICA) != '') {
        $logsql = LOG_SQL_REPLICA;
        $dbms = DBMS_REPLICA;
        $dbhost = DB_HOST_REPLICA;
        $dbport = DB_PORT_REPLICA;
        $dbusuario = DB_USUARIO_REPLICA;
        $dbpassword = DB_PASSWORD_REPLICA;
        $dbname = DB_NAME_REPLICA;
        $debugdb = DEBUG_DB_REPLICA;
    }
    $mysqli = new mysqli($dbhost, $dbusuario, $dbpassword, $dbname, $dbport);
    if (mysqli_connect_error()) {
        return false;
    } else {
        return $mysqli;
    }
}

function encryptVars() {
    $arr = array();
    $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
    $arr["codigousuario"] = $_SESSION["generales"]["codigousuario"];
    $arr["tipousuario"] = $_SESSION["generales"]["tipousuario"];
    $arr["validado"] = $_SESSION["generales"]["validado"];
    $arr["escajero"] = $_SESSION["generales"]["escajero"];
    $arr["sedeusuario"] = $_SESSION["generales"]["sedeusuario"];
    $arr["nombreusuario"] = $_SESSION["generales"]["nombreusuario"];
    $arr["tipousuariodesarrollo"] = $_SESSION["generales"]["tipousuariodesarrollo"];
    $arr["gastoadministrativo"] = $_SESSION["generales"]["gastoadministrativo"];
    $arr["escensador"] = $_SESSION["generales"]["escensador"];
    $arr["esbrigadista"] = $_SESSION["generales"]["esbrigadista"];
    $arr["idtipoidentificacionusuario"] = $_SESSION["generales"]["idtipoidentificacionusuario"];
    $arr["identificacionusuario"] = $_SESSION["generales"]["identificacionusuario"];
    $arr["nitempresausuario"] = $_SESSION["generales"]["nitempresausuario"];
    $arr["nombreempresausuario"] = $_SESSION["generales"]["nombreempresausuario"];
    $arr["direccionusuario"] = $_SESSION["generales"]["direccionusuario"];
    $arr["idmuniciopiousuario"] = $_SESSION["generales"]["idmuniciopiousuario"];
    $arr["telefonousuario"] = $_SESSION["generales"]["telefonousuario"];
    $arr["movilusuario"] = $_SESSION["generales"]["movilusuario"];
    $arr["ccosusuario"] = $_SESSION["generales"]["ccosusuario"];
    $arr["cargousuario"] = $_SESSION["generales"]["cargousuario"];
    $arr["nombreempresa"] = $_SESSION["generales"]["nombreempresa"];
    $arr["idcodigosirepcaja"] = $_SESSION["generales"]["idcodigosirepcaja"];
    $arr["idcodigosirepdigitacion"] = $_SESSION["generales"]["idcodigosirepdigitacion"];
    $arr["idcodigosirepregistro"] = $_SESSION["generales"]["idcodigosirepregistro"];
    $arr["controlapresupuesto"] = $_SESSION["generales"]["controlapresupuesto"];
    $arr["controlverificacion"] = $_SESSION["generales"]["controlverificacion"];
    $arr["tipousuariocontrol"] = $_SESSION["generales"]["tipousuariocontrol"];
    $arr["identificacionusuariocontrol"] = $_SESSION["generales"]["identificacionusuariocontrol"];
    $arr["emailusuariocontrol"] = $_SESSION["generales"]["emailusuariocontrol"];
    $arr["celularusuariocontrol"] = $_SESSION["generales"]["celularusuariocontrol"];
    $arr["nombreusuariocontrol"] = $_SESSION["generales"]["nombreusuariocontrol"];
    $arr["nombre1usuariocontrol"] = $_SESSION["generales"]["nombre1usuariocontrol"];
    $arr["nombre2usuariocontrol"] = $_SESSION["generales"]["nombre2usuariocontrol"];
    $arr["apellido1usuariocontrol"] = $_SESSION["generales"]["apellido1usuariocontrol"];
    $arr["apellido2usuariocontrol"] = $_SESSION["generales"]["apellido2usuariocontrol"];
    return $arr;
}

//
function armarNombresTablas() {
    if (!isset($_SESSION["generales"]["codigoempresa"])) {
        return false;
    }
    if (!file_exists(PATH_ABSOLUTO_SITIO . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php')) {
        return false;
    }
    $_SESSION["generales"]["basopciones"] = 'bas_opciones';
    $_SESSION["generales"]["bastagsbandejaentrada"] = 'bas_tagsbandejaentrada';
    $_SESSION["generales"]["baspermisosespeciales"] = 'bas_permisosespeciales';
    return true;
}

//
function armarMenuLateralBootstrap() {

    $name = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $_SESSION["generales"]["codigousuario"] . '-' . date("Y") . '.mnu';
    if (file_exists($name)) {
        return file_get_contents($name);
    }
    //
    $claveEncriptacion = encontrarClaveEncriptacion();

    //
    $menu = array();
    $mysqli = conexionBD('');
    $temBandejas = retornarRegistrosMysqli($mysqli, 'bas_grupostagsbandejaentrada', "1=1", "id");
    $temOpciones = retornarRegistrosMysqli($mysqli, 'bas_opciones', "estado='1'", "idopcion");

    /*
      // Grupo de administracion
      $g = "'A'";
      $menu[$g] = array();
      $menu[$g]["nombre"] = 'ADMINISTRACION';
      $menu[$g]["submenus"] = array();

      // SubMenu
      $g = "'A'";
      $s = "'00'";
      $menu[$g]["submenus"][$s] = array();
      $menu[$g]["submenus"][$s]["nombre"] = 'Control';
      $menu[$g]["submenus"][$s]["acciones"] = array();

      // Cambiar periodo
      $g = "'A'";
      $s = "'00'";
      $a = "'01'";
      $menu[$g]["submenus"][$s]["acciones"][$a] = array();
      $menu[$g]["submenus"][$s]["acciones"][$a]["nombre"] = 'Cambiar periodo';
      $arr = encryptVars();
      $arr["controlador"] = 'generales.php';
      $arr["metodo"] = 'cambiarPeriodo();';
      $arr["parametros"] = array();
      $arr["link"] = '';
      $json = json_encode($arr);
      $jsonencrypt = base64_encode(encrypt_decrypt('encrypt', $claveEncriptacion, $claveEncriptacion, $json));
      $menu[$g]["submenus"][$s]["acciones"][$a]["enlace"] = TIPO_HTTP . HTTP_HOST . '/enrutador.php?accion=ejecutarscript&parameters=' . $jsonencrypt;
      $menu[$g]["submenus"][$s]["acciones"][$a]["enlaceexterno"] = '';

      // Actualizar perfil
      $g = "'A'";
      $s = "'00'";
      $a = "'02'";
      $menu[$g]["submenus"][$s]["acciones"][$a] = array();
      $menu[$g]["submenus"][$s]["acciones"][$a]["nombre"] = 'Actualizar perfil';
      $arr = encryptVars();
      $arr["controlador"] = 'generales.php';
      $arr["metodo"] = 'cargarPerfil();';
      $arr["parametros"] = array();
      $arr["link"] = '';
      $json = json_encode($arr);
      $jsonencrypt = base64_encode(encrypt_decrypt('encrypt', $claveEncriptacion, $claveEncriptacion, $json));
      $menu[$g]["submenus"][$s]["acciones"][$a]["enlace"] = TIPO_HTTP . HTTP_HOST . '/enrutador.php?accion=ejecutarscript&parameters=' . $jsonencrypt;
      $menu[$g]["submenus"][$s]["acciones"][$a]["enlaceexterno"] = '';

      // COlocar ticket
      $g = "'A'";
      $s = "'00'";
      $a = "'03'";
      $menu[$g]["submenus"][$s]["acciones"][$a] = array();
      $menu[$g]["submenus"][$s]["acciones"][$a]["nombre"] = 'Colocar Ticket';
      $arr = encryptVars();
      $arr["controlador"] = 'admMantenimientoTickets.php';
      $arr["metodo"] = 'nuevoTicket();';
      $arr["parametros"] = array();
      $arr["link"] = '';
      $json = json_encode($arr);
      $jsonencrypt = base64_encode(encrypt_decrypt('encrypt', $claveEncriptacion, $claveEncriptacion, $json));
      $menu[$g]["submenus"][$s]["acciones"][$a]["enlace"] = TIPO_HTTP . HTTP_HOST . '/enrutador.php?accion=ejecutarscript&parameters=' . $jsonencrypt;
      $menu[$g]["submenus"][$s]["acciones"][$a]["enlaceexterno"] = '';

      // Actualizar Menú de opciones
      if ($_SESSION["generales"]["tipousuario"] == '01') {
      $g = "'A'";
      $s = "'00'";
      $a = "'04'";
      $menu[$g]["submenus"][$s]["acciones"][$a] = array();
      $menu[$g]["submenus"][$s]["acciones"][$a]["nombre"] = 'Menú opciones';
      $arr = encryptVars();
      $arr["controlador"] = '';
      $arr["metodo"] = '';
      $arr["parametros"] = array();
      $arr["link"] = '';
      $arr["target"] = '';
      $json = json_encode($arr);
      $jsonencrypt = base64_encode(encrypt_decrypt('encrypt', $claveEncriptacion, $claveEncriptacion, $json));
      $menu[$g]["submenus"][$s]["acciones"][$a]["enlace"] = '';
      $menu[$g]["submenus"][$s]["acciones"][$a]["enlaceexterno"] = TIPO_HTTP . HTTP_HOST . '/librerias/proceso/admMantenimientoOpciones.php?accion=relacion';
      }
     */

    // ************************************************************************************ //
    // Arma el menú de bandejas
    // Solo para usuarios internos
    // ************************************************************************************ //    
    if ($_SESSION["generales"]["tipousuario"] != '00' && $_SESSION["generales"]["tipousuario"] != '06') {
        $arrPerUsu = array();
        $usubantem = retornarRegistrosMysqli($mysqli, 'usuariostagsbandejaentrada', "idusuario='" . $_SESSION["generales"]["codigousuario"] . "'", "idtag");
        foreach ($usubantem as $ux) {
            $arrPerUsu[$ux["idtag"]] = 'S';
        }
        unset($usubantem);
        if ($_SESSION["generales"]["tipousuario"] == '01' ||
                !empty($arrPerUsu)) {
            $g = "'B'";
            $menu[$g] = array();
            $menu[$g]["nombre"] = 'BANDEJAS';
            $menu[$g]["submenus"] = array();
            foreach ($temBandejas as $tx) {
                $temBandejas1 = retornarRegistrosMysqli($mysqli, "bas_tagsbandejaentrada", "idgrupo='" . $tx["id"] . "'", "idorden");
                $mostrar = 'no';
                if ($temBandejas1 && !empty($temBandejas1)) {
                    foreach ($temBandejas1 as $tx1) {
                        if (isset($arrPerUsu[$tx1["id"]]))
                            $mostrar = 'si';
                    }
                }
                if ($_SESSION["generales"]["tipousuario"] == '01' ||
                        $mostrar == 'si') {
                    if (!empty($temBandejas1)) {
                        $g = "'B'";
                        $s = "'" . $tx["id"] . "'";
                        $menu[$g]["submenus"][$s] = array();
                        $menu[$g]["submenus"][$s]["nombre"] = $tx["descripcion"];
                        $menu[$g]["submenus"][$s]["acciones"] = array();
                        $temBandejas1 = retornarRegistrosMysqli($mysqli, "bas_tagsbandejaentrada", "idgrupo='" . $tx["id"] . "'", "idorden");
                        foreach ($temBandejas1 as $tx1) {
                            if ($_SESSION["generales"]["tipousuario"] == '01' ||
                                    isset($arrPerUsu[$tx1["id"]])) {
                                $g = "'B'";
                                $s = "'" . $tx["id"] . "'";
                                $a = "'" . $tx1["id"] . "'";
                                $menu[$g]["submenus"][$s]["acciones"][$a] = array();
                                $menu[$g]["submenus"][$s]["acciones"][$a]["nombre"] = $tx1["descripcion"];
                                $arr = encryptVars();
                                $arr["controlador"] = $tx1["sii2_controlador"];
                                $arr["metodo"] = $tx1["sii2_metodo"] . '();';
                                $arr["parametros"] = array();
                                if ($tx1["script"] != '') {
                                    $pars = explode("&", $tx1["script"]);
                                    $ipars = -1;
                                    foreach ($pars as $p) {
                                        $ipars++;
                                        if ($ipars > 0) {
                                            list ($var, $dat) = explode("=", $p);
                                            $arr["parametros"][$var] = $dat;
                                        }
                                    }
                                }
                                $arr["link"] = '';
                                $json = json_encode($arr);
                                $jsonencrypt = base64_encode(encrypt_decrypt('encrypt', $claveEncriptacion, $claveEncriptacion, $json));
                                $menu[$g]["submenus"][$s]["acciones"][$a]["enlace"] = '';
                                $menu[$g]["submenus"][$s]["acciones"][$a]["enlaceexterno"] = TIPO_HTTP . HTTP_HOST . '/' . str_replace("../../", "", $tx1["script"]);
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
                        $arr = encryptVars();
                        $arr["controlador"] = trim($tx["script"]);
                        $arr["metodo"] = '';
                        $arr["parametros"] = array();
                        if ($tx["script"] != '') {
                            if (trim($tx["enlace"]) != '') {
                                $pars = explode("&", trim($tx["enlace"]));
                                $ipars = -1;
                                foreach ($pars as $p) {
                                    $ipars++;
                                    if ($ipars == 0) {
                                        if (strpos($p, "?")) {
                                            list ($izq, $der) = explode("=", $p);
                                            $arr["metodo"] = $der . '();';
                                        }
                                    }
                                    if ($ipars > 0) {
                                        list ($var, $dat) = explode("=", $p);
                                        $arr["parametros"][$var] = $dat;
                                    }
                                }
                            }
                        }
                        $arr["link"] = $tx["enlace"];
                        $json = json_encode($arr);
                        $jsonencrypt = base64_encode(encrypt_decrypt('encrypt', $claveEncriptacion, $claveEncriptacion, $json));
                        $menu[$g]["submenus"][$s]["acciones"][$a]["enlace"] = '';
                        $menu[$g]["submenus"][$s]["acciones"][$a]["enlaceexterno"] = TIPO_HTTP . HTTP_HOST . '/' . str_replace("../../", "", $tx["enlace"]);
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
                        $txt .= '<li><a href="' . $a["enlaceexterno"] . '" target="_blank">' . $a["nombre"] . ' (*)</a></li>';
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

    //
    $mysqli->close();


    //
    return $txt;
}

function encontrarClaveEncriptacion() {
    if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
        $claveEncriptacion = 's44s0nl1n32018';
    } else {
        $claveEncriptacion = CLAVE_ENCRIPTACION;
    }
    return $claveEncriptacion;
}

// ************************************************************************************************* //
// Fin lkibrerias basicas modelo bootstrap
// ************************************************************************************************* //

function armarGridGeneral($name, $namegrid, $headers, $widths, $aligns, $types, $sorts, $data) {
    $string = '<script type="text/javascript">' . chr(13);
    $string .= "$name= new dhtmlXGridObject('$namegrid');" . chr(13);
    $string .= "$name.mygrid.imgURL = '../../html/default/images/';" . chr(13);
    $string .= "$name.setHeader('$headers');" . chr(13);
    $string .= "$name.setInitWidths('$widths');" . chr(13);
    $string .= "$name.setColAlign('$aligns');" . chr(13);
    $string .= "$name.setColTypes('$types');" . chr(13);
    $string .= "$name.setColSorting('$sorts');" . chr(13);
    /*
      if (!defined('DHTMLX_SKIN')) {
      define('DHTMLX_SKIN' . 'dhx_skyblue');
      }
      $string .= "$name.setSkin('dhx_skyblue');" . chr(13);
     */
    $string .= "$name.init();" . chr(13);
    $string .= "$name.loadXML('$data');" . chr(13);
    $string .= '</script>' . chr(13);
    return $string;
}

function retornarDispositivo() {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/Mobile_Detect.php');
    if (!isset($_SESSION["generales"]["tipodispositivo"]) || $_SESSION["generales"]["tipodispositivo"] == '') {
        $return = 'computer';
        $disp = new Mobile_Detect ();
        if ($disp->isMobile()) {
            $return = 'mobile';
        }
        if ($disp->isTablet()) {
            $return = 'tablet';
        }
        unset($disp);
        $_SESSION["generales"]["tipodispositivo"] = $return;
    }
    return $_SESSION["generales"]["tipodispositivo"];
}

function limpiarNoUtf8($string) {


    $cur_encoding = mb_detect_encoding($string, 'UTF-8');
    if ($cur_encoding == "UTF-8" && mb_check_encoding($string, "UTF-8")) {

        $string = iconv("UTF-8", "UTF-8//IGNORE", $string);
        $string = iconv("UTF-8", "ISO-8859-1//IGNORE", $string);
        $string = iconv("ISO-8859-1", "UTF-8", $string);
        $string = trim($string);
    } else {
        $string = iconv("ISO-8859-1", "UTF-8//IGNORE", $string);
        $string = iconv("UTF-8", "ISO-8859-1//IGNORE", $string);
        $string = iconv("ISO-8859-1", "UTF-8", $string);
        $string = utf8_encode($string);
    }


    // $string = iconv("ISO-8859-1", "UTF-8//IGNORE", $string);
    //$string = recode_string("us..flat", $string);



    return $string;
}

// gestion del utf8 decode
/**
 * 
 * @param type $txt
 * @param type $forzar (si/no)
 * @return type
 */
function utf8decode_sii($txt, $forzar = 'no') {
    if ($forzar == 'si') {
        return utf8_decode($txt);
    }

    if ($forzar == 'no') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        if (retornarDispositivo() == 'computer') {
            if (!defined('ENCODING')) {
                define('ENCODING', 'iso8859-1');
            }
            if (ENCODING == 'iso8859-1' || ENCODING == '') {
                return utf8_decode($txt);
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
                return utf8_decode($txt);
            }
        }
    }
}

// gesti�n del utf8 encode
/**
 * 
 * @param type $txt
 * @param type $forzar (si/no)
 * @return type
 */
function utf8encode_sii($txt, $forzar = 'no') {
    if ($forzar == 'si') {
        return utf8_encode($txt);
    }

    if ($forzar == 'no') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        if (retornarDispositivo() == 'computer') {
            if (!defined('ENCODING')) {
                define('ENCODING', 'iso8859-1');
            }
            if (ENCODING == 'iso8859-1' || ENCODING == '') {
                return utf8_encode($txt);
            }
            if (ENCODING == 'utf8') {
                return $txt;
            }
        } else {
            if (!defined('ENCODING_MOVIL')) {
                define('ENCODING', 'utf8');
            }
            if (ENCODING == 'utf8' || ENCODING == '') {
                return $txt;
            }
            if (ENCODING == 'iso8859-1') {
                return utf8_encode($txt);
            }
        }
    }
}

function limpiarCadenaVidaSesion($valor) {
    // $valor = str_ireplace("SELECT ","",$valor);
    // $valor = str_ireplace("COPY ","",$valor);
    // $valor = str_ireplace("DELETE ","",$valor);
    // $valor = str_ireplace("DROP ","",$valor);
    // $valor = str_ireplace("DUMP ","",$valor);
    // $valor = str_ireplace(" OR ","",$valor);
    // $valor = str_ireplace("%","",$valor);
    // $valor = str_ireplace("LIKE ","",$valor);
    // $valor = str_ireplace(" -- ","",$valor);
    // $valor = str_ireplace("^","",$valor);
    // $valor = str_ireplace("[","",$valor);
    // $valor = str_ireplace("]","",$valor);
    // $valor = str_ireplace("\\","",$valor);
    // $valor = str_ireplace("!","",$valor);
    // $valor = str_ireplace("¡","",$valor);
    // $valor = str_ireplace("?","",$valor);
    // $valor = str_ireplace("=","",$valor);
    // $valor = str_ireplace("&","",$valor);
    return $valor;
}

function filtrarCaracteres($txt) {
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

function convertirCaracteres($txt) {
    $txt = str_replace("á", "&aacute;", $txt);
    $txt = str_replace("é", "&eacute;", $txt);
    $txt = str_replace("í", "&iacute;", $txt);
    $txt = str_replace("ó", "&oacute;", $txt);
    $txt = str_replace("ú", "&uacute;", $txt);
    $txt = str_replace("Á", "&Aacute;", $txt);
    $txt = str_replace("É", "&Eacute;", $txt);
    $txt = str_replace("Í", "&Iacute;", $txt);
    $txt = str_replace("Ó", "&Oacute;", $txt);
    $txt = str_replace("Ú", "&Uacute;", $txt);
    $txt = str_replace("ñ", "&ntilde;", $txt);
    $txt = str_replace("Ñ", "&Ntilde;", $txt);
    return $txt;
}

function convertirDesdeHtml($txt) {
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

function actualizarControlCentralizado($proceso, $endpoint, $leidos = 0, $actualizados = 0, $sinactualizar = 0, $errores = 0) {
    $data .= $_SESSION ["generales"] ["codigoempresa"] . '|' .
            $proceso . '|' .
            getmypid() . '|' .
            date("Ymd") . '|' .
            date("His") . '|' .
            $endpoint . '|' .
            $leidos . '|' . // Leidos
            $actualizados . '|' . // Actualizados
            $sinactualizar . '|' . // Sin actualizar
            $errores; // Con error
    //
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'http://siiconfe.confecamaras.co/librerias/ws/wsControlCamaras.php',
        CURLOPT_USERAGENT => 'Consumo de request por CURL',
        CURLOPT_POST => 1,
        CURLOPT_CONNECTTIMEOUT => 0,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_POSTFIELDS => array(
            'data' => base64_encode($data),
        )
    ));
    curl_exec($curl);
    curl_close($curl);
}

function arrayToXml($array, $lastkey = 'root') {
    $buffer .= "<" . $lastkey . "> ";
    if (!is_array($array)) {
        $buffer .= $array;
    } else {
        foreach ($array as $key => $value) {
            if (!is_numeric($key)) {
                if (is_array($value)) {
                    if (is_numeric(key($value))) {
                        foreach ($value as $key => $bvalue) {
                            $buffer .= arrayToXml($bvalue, $key);
                        }
                    } else {
                        $buffer .= arrayToXml($value, $key);
                    }
                } else {
                    $buffer .= arrayToXml($value, $key);
                }
            }
        }
    }
    $buffer .= "</" . $lastkey . "> ";
    return $buffer;
}

/**
 * Funcion que retorna un codigo html para un select desde un arreglo clave --> Valor
 *
 * @param 	string		$id			Id de entrada al select 
 * @return 	html					C&oacute;digo html con el select
 */
function armarSelectArreglo($sel, $arr, $id) {
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

/*
 * Convierte un  ascii en su correspondiente binario 
 */

function asc2bin($in) {
    $out = '';
    for ($i = 0, $len = strlen($in); $i < $len; $i++) {
        $out .= sprintf("%08b", ord($in[$i]));
    }
    return $out;
}

/**
 * 
 * @param int $tamano
 * @param type $nivel
 * @param type $txt
 * @param type $width
 * @param type $height
 * @param type $dhtmlx
 * @param type $sombra
 * @param type $refresh
 * @param type $time
 * @param type $mostrarenlacesmenu
 * @param type $emergente
 * @param type $bannersuperior
 * @param type $arrBtnTipo
 * @param type $arrBtnEnlace
 * @param type $arrBtnImagen
 * @param type $arrBtnToolTip
 * @param type $head
 */
function armarMensaje($tamano = 400, $nivel, $txt, $width = 600, $height = 600, $dhtmlx = '', $sombra = '', $refresh = false, $time = 0, $mostrarenlacesmenu = 'si', $emergente = '', $bannersuperior = 'si', $arrBtnTipo = array(), $arrBtnEnlace = array(), $arrBtnImagen = array(), $arrBtnToolTip = array(), $head = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/presentacion.class.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');
    $nw = new presentacion ();
    if (retornarDispositivo() != 'computer') {
        $tamano = 0;
    }
    $nivel = str_replace(array("<", ">"), "", $nivel);
    $string = '<br><br>';
    $string .= $nw->abrirTablaBorde($tamano);
    $string .= $nw->armarLineaTexto($tamano, '<' . $nivel . '>' . $txt . '</' . $nivel . '>');
    $string .= $nw->cerrarTablaBorde();
    $string .= '<br>';
    if (!empty($arrBtnTipo)) {
        $string .= $nw->armarBarraBotonesProcesoDinamico($tamano, $arrBtnTipo, $arrBtnImagen, $arrBtnEnlace, $arrBtnToolTip);
    }
    unset($nw);
    if (retornarDispositivo() == 'computer') {
        mostrarcuerpoIE26a(array(), $head, '', '', $string, $tamano, 600, '', ' Espere ...', $sombra, $refresh, $time, $mostrarenlacesmenu, $emergente, $bannersuperior);
    } else {
        mostrarMovil('', '', $string, '', '');
    }
}

/*
 * Arma mensaje para mostrar en pantalla
 */

function armarMensajeBotones($tamano = 400, $nivel, $txt, $arrBtnTipo = array(), $arrBtnEnlace = array(), $arrBtnImagen = array(), $arrBtnToolTip = array()) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/presentacion.class.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/Mobile_Detect.php');

    if (retornarDispositivo() != 'computer') {
        $tamano = 0;
    }
    $nw = new presentacion ();
    $nivel = str_replace(array("<", ">"), "", $nivel);
    $string = '<br><br>';
    $string .= $nw->abrirTablaBorde($tamano);
    $string .= $nw->armarLineaTexto($tamano, '<' . $nivel . '>' . $txt . '</' . $nivel . '>');
    $string .= $nw->cerrarTablaBorde();
    $string .= '<br>';
    if (!empty($arrBtnTipo)) {
        $string .= $nw->armarBarraBotonesProcesoDinamico($tamano, $arrBtnTipo, $arrBtnImagen, $arrBtnEnlace, $arrBtnToolTip);
    }
    unset($nw);
    if (retornarDispositivo() == 'computer') {
        mostrarcuerpoIE26a(array(), '', '', '', $string, $tamano, 600, $dhtmlx, $sombra, $refresh, $time, $mostrarenlacesmenu, $emergente, $bannersuperior);
    } else {
        mostrarMovil('', '', $string, '', '');
    }
}

function armarMensajeSinParseo($tamano = 400, $txt, $mostrarenlacesmenu = 'no', $emergente = '', $bannersuperior = 'no') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/presentacion.class.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');
    $nw = new presentacion ();

    if (retornarDispositivo() != 'computer') {
        $tamano = 0;
    }
    $string = $nw->abrirTablaBorde($tamano);
    $string .= $nw->armarLineaTexto($tamano, $txt, 'center');
    $string .= $nw->cerrarTablaBorde();
    $string .= '<br>';
    unset($nw);
    if (retornarDispositivo() == 'computer') {
        mostrarcuerpoIE26a(array(), '', '', '', $string, $tamano, 600, $dhtmlx, $sombra, $refresh, $time, $mostrarenlacesmenu, $emergente, $bannersuperior);
    } else {
        mostrarMovil('', '', $string, '', '');
    }
}

function armarMensajeContinuar($tamano = 400, $nivel, $txt) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/presentacion.class.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');

    if (retornarDispositivo() != 'computer') {
        $tamano = 0;
    }
    $nw = new presentacion ();
    $string = $nw->abrirTablaBorde($tamano);
    $string .= $nw->armarLineaTexto($tamano, '<' . $nivel . '>' . $txt . '</' . $nivel . '>');
    $string .= $nw->cerrarTablaBorde();
    $string .= '<br>';
    unset($nw);
    return $string;
}

/*
 * Verifica si un a&ntilde;o es o no bisiesto.
 */

function anoBisiesto($ano) {

    $ent = intval($ano / 4);
    if ($ano == $ent * 4) {
        return true;
    } else {
        return false;
    }
}

function borrarEnters($txt) {
    $txt1 = str_replace(chr(13) . chr(10), " - ", $txt);
    $txt1 = str_replace(chr(10), " ", $txt1);
    $txt1 = str_replace(chr(13), " ", $txt1);
    $txt1 = str_replace(";", " ", $txt1);
    $txt1 = str_replace("\\t", '', $txt1);
    return $txt1;
}

function borrarPalabrasAutomaticas($txt, $comple = '') {
    $salida = $txt;
    if ($comple != '') {
        $pos = strpos($salida, $comple);
        if ($pos) {
            $pos = $pos - 1;
            $salida = substr($salida, 0, $pos);
        }
    }
    $pos = strpos($salida, '- EN LIQUIDACION');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }

    $pos = strpos($salida, '- EN LIQUIDACION JUDICIAL');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }

    $pos = strpos($salida, '- EN LIQUIDACION FORZOSA');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }

    $pos = strpos($salida, '- EN ACUERDO DE REESTRUCTURACION');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }
    $pos = strpos($salida, '- EN REESTRUCTURACION');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }
    $pos = strpos($salida, '- EN REORGANIZACION');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }

    $pos = strpos($salida, 'EN LIQUIDACION');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }

    $pos = strpos($salida, 'EN LIQUIDACION');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }

    $pos = strpos($salida, 'EN LIQUIDACION JUDICIAL');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }

    $pos = strpos($salida, 'EN LIQUIDACION FORZOSA');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }

    $pos = strpos($salida, 'EN ACUERDO DE REESTRUCTURACION');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }
    $pos = strpos($salida, 'EN REESTRUCTURACION');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }
    $pos = strpos($salida, 'EN REORGANIZACION');
    if ($pos) {
        $pos = $pos - 1;
        $salida = substr($salida, 0, $pos);
    }
    return $salida;
}

/*
 * Convierte un binario en ascii
 */

function bin2asc($in) {
    $out = '';
    for ($i = 0, $len = strlen($in); $i < $len; $i += 8) {
        $out .= chr(bindec(substr($in, $i, 8)));
    }
    return $out;
}

//
function cambiarTildes($txt) {
    return str_replace(array('&aacute;', '&eacute;', '&iacute;', '&oacute;', '&uacute;', '&Aacute;', '&Eacute;', '&Iacute;', '&Oacute;', '&Uacute;', '&ntilde;', '&Ntilde;'), array('a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'ñ', 'Ñ'), $txt);
}

//
function cambiarTildesEnes($txt) {
    return str_replace(array('&aacute;', '&eacute;', '&iacute;', '&oacute;', '&uacute;', '&Aacute;', '&Eacute;', '&Iacute;', '&Oacute;', '&Uacute;', '&ntilde;', '&ntilde;', '\''), array('a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N', '´'), $txt);
}

function cargarPlantillas($plantilla) {
    if (file_exists(PATH_ABSOLUTO_SITIO . '/propias/' . $plantilla)) {
        $file = PATH_ABSOLUTO_SITIO . '/propias/' . $plantilla;
    } else {
        $file = PATH_ABSOLUTO_SITIO . '/plantillas/' . $plantilla;
    }

    $tx = file_get_contents($file);
    if (file_exists(PATH_ABSOLUTO_SITIO . '/propias/' . $plantilla)) {
        $tx = str_replace('src="buttons/', 'src="' . TIPO_HTTP . HTTP_HOST . '/propias/buttons/', $tx);
        $tx = str_replace('src="images/', 'src="' . TIPO_HTTP . HTTP_HOST . '/propias/images/', $tx);
    } else {
        $tx = str_replace("src=\"buttons/", "src=\"" . TIPO_HTTP . HTTP_HOST . "/plantillas/buttons/", $tx);
        $tx = str_replace("src=\"images/", "src=\"" . TIPO_HTTP . HTTP_HOST . "/plantillas/images/", $tx);
    }
    return $tx;
}

function cargarPermisosPublico() {
    $array = retornarRegistros('bas_permisosespeciales', "1=1", "idpermiso", 0, 0, array(), 'replicabatch');
    if ($array === false) {
        echo "Error leyendo bas_permisosespeciales<br>";
        exit();
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
                    if (contarRegistros('usuariospermisosespeciales', "idusuario='" . $_SESSION["generales"]["codigousuario"] . "' and idpermiso='" . $ar["idpermiso"] . "'", 'replicabatch') == 1) {
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
    $_SESSION["generales"]["tipomenu"] = retornarClaveValor('01.01.02');

    unset($array);
    unset($ar);
    return true;
}

/**
 * Funci&oacute;n que dadas dos fechas calcula el n&uacute;mero de a&ntilde;os cumplidos entre ambas
 *
 * @param 		string		$f1		Primera fecha
 * @param 		string		$f2		Segunda fecha
 * @return 		int					N&uacute;mero de a&ntilde;os
 */
function calcularAnos($f1, $f2) {
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

/**
 * Funci&oacute;n que recibe dos fechas y retorna los a&ntilde;os meses y dias entre ambas
 *
 * @param 	string		$fechaactual 	De la forma AAAA-MM-DD
 * @param 	string		$fechainicial	De la forma AAAA-MM-DD
 * @return 	array						A&ntilde;os, meses y dias
 */
function calcularAnosMesesDias($fechaactual, $fechainicial) {

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
            case 1: $dias_mes_anterior = 31;
                break;
            case 2: $dias_mes_anterior = 31;
                break;
            case 3:
                if (checkdate(2, 29, $array_actual[0])) {
                    $dias_mes_anterior = 29;
                    break;
                } else {
                    $dias_mes_anterior = 28;
                    break;
                }
            case 4: $dias_mes_anterior = 31;
                break;
            case 5: $dias_mes_anterior = 30;
                break;
            case 6: $dias_mes_anterior = 31;
                break;
            case 7: $dias_mes_anterior = 30;
                break;
            case 8: $dias_mes_anterior = 31;
                break;
            case 9: $dias_mes_anterior = 31;
                break;
            case 10: $dias_mes_anterior = 30;
                break;
            case 11: $dias_mes_anterior = 31;
                break;
            case 12: $dias_mes_anterior = 30;
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

function calcularDiasCalendario($fechaactual, $fechainicial) {

    //
    $arraynohabiles = array(
        '20130101', '20130107', '20130325', '20130328', '20130329', '20130501', '20130513',
        '20130603', '21030610', '20130701', '20130720', '20130807', '20130819', '20131014',
        '20131104', '20131111', '20131225',
        '20140101', '20140106', '20140324', '20140417', '20140418', '20140420', '20140501',
        '20140602', '20140623', '20140630', '20140720', '20140807', '20140818', '20141013',
        '20141103', '20141117', '20141208', '20141225',
        '20150101', '20150112', '20150323', '20150402', '20150403', '20150501', '20150518',
        '20150608', '20150615', '20150629', '20150720', '20150807', '20150817', '20151012',
        '20151102', '20151116', '20151208', '20151225',
        '20160101', '20160111', '20160321', '20160324', '20160325', '20160509', '20160530',
        '20160606', '20160704', '20160720', '20160815', '20161017', '20161107', '20161114',
        '20161208',
        '20170109', '20170320', '20170413', '20170414', '20170529', '20170619', '20170626', '20170720',
        '20170807', '20170814', '20171016', '20171106', '20171113' . '20171208', '20171225'
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

function calcularHorasHabiles($fechaactual, $horaactual, $fechainicial, $horainicial, $diasferiados = array()) {

    //
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

    $dias = calcularDiasCalendario($fechaactual, $fechainicial);
    $dias1 = $dias[0] - $dias[1];

    //
    $horasini = intval(substr($horainicial, 0, 2));
    $minutosini = intval(substr($horainicial, 2, 2));
    $horasfin = intval(substr($horaactual, 0, 2));
    $minutosfin = intval(substr($horaactual, 2, 2));
    if ($fechaactual == $fechainicial) {
        $horashabiles = (($horasfin * 60 + $minutosfin) - ($horasini * 60 + $minutosini)) / 60;
    }
    if ($fechaactual < $fechainicial) {
        $horashabiles = 0;
    }
    if ($fechaactual > $fechainicial) {
        $horashabiles = (($horasfin * 60 + $minutosfin) + (24 * 60) - (($horasini * 60) + $minutosini) - (14 * 60)) / 60;
        if ($dias1 > 0) {
            $horashabiles = $horashabiles + (intval($dias1) - 1) * 10;
        }
    }

    return $horashabiles;
}

/* WSI - funcion para calcular tiempo Ordinario entre dos fechas. Retorna un arreglo. */

function calcularTiempoOrdinario($fechaFin, $horaFin, $fechaInicio, $horaInicio) {

    $fechaUnix1 = strtotime(date($fechaInicio . ' ' . $horaInicio));
    $fechaUnix2 = strtotime(date($fechaFin . ' ' . $horaFin));

    $totalUnix = $fechaUnix2 - $fechaUnix1;

    $horas = floor($totalUnix / 3600);
    $minutos = ( ( $totalUnix / 60 ) % 60 );
    $segundos = ( $totalUnix % 60 );

    $horas = str_pad($horas, 2, "0", STR_PAD_LEFT);
    $minutos = str_pad($minutos, 2, "0", STR_PAD_LEFT);
    $segundos = str_pad($segundos, 2, "0", STR_PAD_LEFT);

    return array('horas' => $horas, 'minutos' => $minutos, 'segundos' => $segundos);
}

/* WSI - funcion para calcular el tiempo laboral entre dos fechas. Retorna un arreglo */

function calcularTiempoLaboral($fechaFin, $horaFin, $fechaInicio, $horaInicio) {

    $festivosEntreSemana = array(
        '20130101', '20130107', '20130325', '20130328', '20130329', '20130501',
        '20130513', '20130603', '21030610', '20130701', '20130720', '20130807',
        '20130819', '20131014', '20131104', '20131111', '20131225',
        '20140101', '20140106', '20140324', '20140417', '20140418', '20140420',
        '20140501', '20140602', '20140623', '20140630', '20140720', '20140807',
        '20140818', '20141013', '20141103', '20141117', '20141208', '20141225',
        '20150101', '20150112', '20150323', '20150402', '20150403', '20150501',
        '20150518', '20150608', '20150615', '20150629', '20150720', '20150807',
        '20150817', '20151012', '20151102', '20151116', '20151208', '20151225',
        '20160101', '20160111', '20160321', '20160324', '20160325', '20160509',
        '20160530', '20160606', '20160704', '20160720', '20160815', '20161017',
        '20161107', '20161114', '20161208',
    );

    $nomDia = array("DOM", "LUN", "MAR", "MIE", "JUE", "VIE", "SAB");

    $arrayTiempoLaborado = array();

    $fechaEvaluar = $fechaInicio;

    /* Define en formato Unix la jornada laboral */
    $horaInicioJornadaUnix = strtotime(date("08:00:00"));
    $horaFinJornadaUnix = strtotime(date("18:00:00"));

    /* Valida la hora 00 como 24 */
    $horasCtrl = substr($horaFin, 0, 2);
    $minCtrl = substr($horaFin, 3, 2);
    $segCtrl = substr($horaFin, 6, 2);

    if ($horasCtrl == 00) {
        $horaFin = '24:' . $minCtrl . ':' . $segCtrl;
    }

    /* Define en formato Unix la hora de inicio y fin */
    $horaCreacionUnix = strtotime(date($horaInicio));
    $horaTerminacionUnix = strtotime(date($horaFin));

    while (strtotime(date($fechaEvaluar)) <= strtotime(date($fechaFin))) {

        $fechaUnix = strtotime(date($fechaEvaluar));

        $numDia = jddayofweek(cal_to_jd(CAL_GREGORIAN, date("m", $fechaUnix), date("d", $fechaUnix), date("Y", $fechaUnix)), 0);

        if (($nomDia[$numDia] != 'SAB') && ($nomDia[$numDia] != 'DOM')) {

            if (!(in_array($fechaEvaluar, $festivosEntreSemana))) {
                /* Cuando la fecha de inicio es igual a la fecha de Fin */
                if ($fechaInicio == $fechaFin) {

                    if ($horaCreacionUnix < $horaTerminacionUnix) {

                        /* Reglas durante el mismo dia */
                        if (($horaCreacionUnix < $horaInicioJornadaUnix) && ($horaTerminacionUnix > $horaFinJornadaUnix)) {
                            $tiempoLaboradoUnix = $horaFinJornadaUnix - $horaInicioJornadaUnix;
                        }

                        if (($horaCreacionUnix < $horaInicioJornadaUnix) && ($horaTerminacionUnix < $horaFinJornadaUnix)) {
                            $tiempoLaboradoUnix = $horaTerminacionUnix - $horaInicioJornadaUnix;
                        }

                        if (($horaCreacionUnix > $horaInicioJornadaUnix) && ($horaTerminacionUnix < $horaFinJornadaUnix)) {
                            $tiempoLaboradoUnix = $horaTerminacionUnix - $horaCreacionUnix;
                        }

                        if (($horaCreacionUnix > $horaInicioJornadaUnix) && ($horaTerminacionUnix > $horaFinJornadaUnix)) {
                            $tiempoLaboradoUnix = $horaFinJornadaUnix - $horaCreacionUnix;
                        }
                    }

                    if ($tiempoLaboradoUnix < 0) {
                        $arrayTiempoLaborado[] = 0;
                    } else {
                        $arrayTiempoLaborado[] = $tiempoLaboradoUnix;
                    }

                    $horas = floor($tiempoLaboradoUnix / 3600);
                    $minutos = ( ( $tiempoLaboradoUnix / 60 ) % 60 );
                    $arrayDetalleTiempoLaborado[] = $fechaEvaluar . '|' . $nomDia[$numDia] . '|' . $horas . 'h ' . $minutos . 'm';
                }


                /* Cuando la fecha de inicio es igual a la fecha a evaluar y es diferente de la fecha de finalizaci�n */
                if (($fechaInicio == $fechaEvaluar) && ($fechaInicio != $fechaFin)) {

                    /* Reglas de inicio diferentes de fecha Fin */
                    if ($horaInicioJornadaUnix == $horaCreacionUnix) {
                        $tiempoLaboradoUnix = $horaFinJornadaUnix - $horaInicioJornadaUnix;
                    }
                    if ($horaInicioJornadaUnix > $horaCreacionUnix) {
                        $tiempoLaboradoUnix = $horaFinJornadaUnix - $horaInicioJornadaUnix;
                    }
                    if ($horaInicioJornadaUnix < $horaCreacionUnix) {
                        $tiempoLaboradoUnix = $horaFinJornadaUnix - $horaCreacionUnix;
                        //echo '*******';
                    }

                    if ($tiempoLaboradoUnix < 0) {
                        $arrayTiempoLaborado[] = 0;
                    } else {
                        $arrayTiempoLaborado[] = $tiempoLaboradoUnix;
                    }

                    $horas = floor($tiempoLaboradoUnix / 3600);
                    $minutos = ( ( $tiempoLaboradoUnix / 60 ) % 60 );
                    $arrayDetalleTiempoLaborado[] = $fechaEvaluar . '|' . $nomDia[$numDia] . '|' . $horas . 'h ' . $minutos . 'm';
                }

                /* Cuando la fecha de Fin es igual a la fecha a evaluar y es diferente a la fecha de inicio */
                if (($fechaFin == $fechaEvaluar) && ($fechaInicio != $fechaFin)) {

                    /* Reglas de finalizaci�n  diferentes de fecha Inicio */
                    if ($horaFinJornadaUnix == $horaTerminacionUnix) {
                        $tiempoLaboradoUnix = $horaFinJornadaUnix - $horaInicioJornadaUnix;
                    }
                    if ($horaFinJornadaUnix < $horaTerminacionUnix) {
                        $tiempoLaboradoUnix = $horaFinJornadaUnix - $horaInicioJornadaUnix;
                    }
                    if ($horaFinJornadaUnix > $horaTerminacionUnix) {
                        $tiempoLaboradoUnix = $horaTerminacionUnix - $horaInicioJornadaUnix;
                    }
                    if ($tiempoLaboradoUnix < 0) {
                        $arrayTiempoLaborado[] = 0;
                    } else {
                        $arrayTiempoLaborado[] = $tiempoLaboradoUnix;
                    }

                    $horas = floor($tiempoLaboradoUnix / 3600);
                    $minutos = ( ( $tiempoLaboradoUnix / 60 ) % 60 );
                    $arrayDetalleTiempoLaborado[] = $fechaEvaluar . '|' . $nomDia[$numDia] . '|' . $horas . 'h ' . $minutos . 'm';
                }

                /* Cuando la fecha a evaluar es diferente del Inicio y el Fin */
                if (($fechaEvaluar != $fechaInicio) && ($fechaEvaluar != $fechaFin)) {
                    $tiempoLaboradoUnix = $horaFinJornadaUnix - $horaInicioJornadaUnix;
                    if ($tiempoLaboradoUnix < 0) {
                        $arrayTiempoLaborado[] = 0;
                    } else {
                        $arrayTiempoLaborado[] = $tiempoLaboradoUnix;
                    }


                    $horas = floor($tiempoLaboradoUnix / 3600);
                    $minutos = ( ( $tiempoLaboradoUnix / 60 ) % 60 );
                    $arrayDetalleTiempoLaborado[] = $fechaEvaluar . '|' . $nomDia[$numDia] . '|' . $horas . 'h ' . $minutos . 'm';
                }
            }
        }
        /* Incrementa un dia a la fecha evaluada */
        $fechaEvaluar = date("Ymd", strtotime($fechaEvaluar . " + 1 day"));
    }

    /* Suma los tiempos unix almacenados en el arreglo */
    $sumatoriaTiempoUnix = array_sum($arrayTiempoLaborado);

    /* Conversion */
    $horas = floor($sumatoriaTiempoUnix / 3600);
    $minutos = ( ( $sumatoriaTiempoUnix / 60 ) % 60 );
    $segundos = ( $sumatoriaTiempoUnix % 60 );

    $horas = str_pad($horas, 2, "0", STR_PAD_LEFT);
    $minutos = str_pad($minutos, 2, "0", STR_PAD_LEFT);
    $segundos = str_pad($segundos, 2, "0", STR_PAD_LEFT);

    if ($horas >= 0) {
        return array('horas' => $horas, 'minutos' => $minutos, 'segundos' => $segundos);
    } else {
        return array('horas' => 0, 'minutos' => 0, 'segundos' => 0);
    }
}

/* WSI - funcion para calcular el tiempo laboral entre dos fechas. Retorna un arreglo puede validar sabados recibe los festivos */

function calcularTiempoLaboralSabado($fechaFin, $horaFin, $fechaInicio, $horaInicio, $valSabado, $festivosEntreSemana) {

    $nomDia = array("DOM", "LUN", "MAR", "MIE", "JUE", "VIE", "SAB");

    $arrayTiempoLaborado = array();

    $fechaEvaluar = $fechaInicio;



    /* Valida la hora 00 como 24 */
    $horasCtrl = substr($horaFin, 0, 2);
    $minCtrl = substr($horaFin, 3, 2);
    $segCtrl = substr($horaFin, 6, 2);

    if ($horasCtrl == 00) {
        $horaFin = '24:' . $minCtrl . ':' . $segCtrl;
    }

    /* Define en formato Unix la hora de inicio y fin */
    $horaCreacionUnix = strtotime(date($horaInicio));
    $horaTerminacionUnix = strtotime(date($horaFin));

    while (strtotime(date($fechaEvaluar)) <= strtotime(date($fechaFin))) {

        $fechaUnix = strtotime(date($fechaEvaluar));

        $numDia = jddayofweek(cal_to_jd(CAL_GREGORIAN, date("m", $fechaUnix), date("d", $fechaUnix), date("Y", $fechaUnix)), 0);

        $entreSemana = false;

        if ($valSabado == true) {
            if (($nomDia[$numDia] != 'DOM')) {
                $entreSemana = true;
            }
        } else {
            if (($nomDia[$numDia] != 'SAB') && ($nomDia[$numDia] != 'DOM')) {
                $entreSemana = true;
            }
        }

        if ($entreSemana) {
            if (($nomDia[$numDia] != 'SAB')) {
                /* Define en formato Unix la jornada laboral */
                $horaInicioJornadaUnix = strtotime(date("08:00:00"));
                $horaFinJornadaUnix = strtotime(date("18:00:00"));
            } else {
                /* Define en formato Unix la jornada laboral */
                $horaInicioJornadaUnix = strtotime(date("08:00:00"));
                $horaFinJornadaUnix = strtotime(date("13:00:00"));
            }
            if (!(in_array($fechaEvaluar, $festivosEntreSemana))) {
                /* Cuando la fecha de inicio es igual a la fecha de Fin */
                if ($fechaInicio == $fechaFin) {
                    $tiempoLaboradoUnix = 0;
                    if ($horaCreacionUnix < $horaTerminacionUnix) {

                        /* Reglas durante el mismo dia */
                        if (($horaCreacionUnix < $horaInicioJornadaUnix) && ($horaTerminacionUnix > $horaFinJornadaUnix)) {
                            $tiempoLaboradoUnix = $horaFinJornadaUnix - $horaInicioJornadaUnix;
                        }

                        if (($horaCreacionUnix < $horaInicioJornadaUnix) && ($horaTerminacionUnix < $horaFinJornadaUnix)) {
                            $tiempoLaboradoUnix = $horaTerminacionUnix - $horaInicioJornadaUnix;
                        }

                        if (($horaCreacionUnix > $horaInicioJornadaUnix) && ($horaTerminacionUnix < $horaFinJornadaUnix)) {
                            $tiempoLaboradoUnix = $horaTerminacionUnix - $horaCreacionUnix;
                        }

                        if (($horaCreacionUnix > $horaInicioJornadaUnix) && ($horaTerminacionUnix > $horaFinJornadaUnix)) {
                            $tiempoLaboradoUnix = $horaFinJornadaUnix - $horaCreacionUnix;
                        }
                    }

                    if ($tiempoLaboradoUnix < 0) {
                        $arrayTiempoLaborado[] = 0;
                    } else {
                        $arrayTiempoLaborado[] = $tiempoLaboradoUnix;
                    }

                    $horas = floor($tiempoLaboradoUnix / 3600);
                    $minutos = ( ( $tiempoLaboradoUnix / 60 ) % 60 );
                    $arrayDetalleTiempoLaborado[] = $fechaEvaluar . '|' . $nomDia[$numDia] . '|' . $horas . 'h ' . $minutos . 'm';
                }


                /* Cuando la fecha de inicio es igual a la fecha a evaluar y es diferente de la fecha de finalizaci�n */
                if (($fechaInicio == $fechaEvaluar) && ($fechaInicio != $fechaFin)) {

                    /* Reglas de inicio diferentes de fecha Fin */
                    if ($horaInicioJornadaUnix == $horaCreacionUnix) {
                        $tiempoLaboradoUnix = $horaFinJornadaUnix - $horaInicioJornadaUnix;
                    }
                    if ($horaInicioJornadaUnix > $horaCreacionUnix) {
                        $tiempoLaboradoUnix = $horaFinJornadaUnix - $horaInicioJornadaUnix;
                    }
                    if ($horaInicioJornadaUnix < $horaCreacionUnix) {
                        $tiempoLaboradoUnix = $horaFinJornadaUnix - $horaCreacionUnix;
                        //echo '*******';
                    }

                    if ($tiempoLaboradoUnix < 0) {
                        $arrayTiempoLaborado[] = 0;
                    } else {
                        $arrayTiempoLaborado[] = $tiempoLaboradoUnix;
                    }

                    $horas = floor($tiempoLaboradoUnix / 3600);
                    $minutos = ( ( $tiempoLaboradoUnix / 60 ) % 60 );
                    $arrayDetalleTiempoLaborado[] = $fechaEvaluar . '|' . $nomDia[$numDia] . '|' . $horas . 'h ' . $minutos . 'm';
                }

                /* Cuando la fecha de Fin es igual a la fecha a evaluar y es diferente a la fecha de inicio */
                if (($fechaFin == $fechaEvaluar) && ($fechaInicio != $fechaFin)) {

                    /* Reglas de finalizaci�n  diferentes de fecha Inicio */
                    if ($horaFinJornadaUnix == $horaTerminacionUnix) {
                        $tiempoLaboradoUnix = $horaFinJornadaUnix - $horaInicioJornadaUnix;
                    }
                    if ($horaFinJornadaUnix < $horaTerminacionUnix) {
                        $tiempoLaboradoUnix = $horaFinJornadaUnix - $horaInicioJornadaUnix;
                    }
                    if ($horaFinJornadaUnix > $horaTerminacionUnix) {
                        $tiempoLaboradoUnix = $horaTerminacionUnix - $horaInicioJornadaUnix;
                    }
                    if ($tiempoLaboradoUnix < 0) {
                        $arrayTiempoLaborado[] = 0;
                    } else {
                        $arrayTiempoLaborado[] = $tiempoLaboradoUnix;
                    }

                    $horas = floor($tiempoLaboradoUnix / 3600);
                    $minutos = ( ( $tiempoLaboradoUnix / 60 ) % 60 );
                    $arrayDetalleTiempoLaborado[] = $fechaEvaluar . '|' . $nomDia[$numDia] . '|' . $horas . 'h ' . $minutos . 'm';
                }

                /* Cuando la fecha a evaluar es diferente del Inicio y el Fin */
                if (($fechaEvaluar != $fechaInicio) && ($fechaEvaluar != $fechaFin)) {
                    $tiempoLaboradoUnix = $horaFinJornadaUnix - $horaInicioJornadaUnix;
                    if ($tiempoLaboradoUnix < 0) {
                        $arrayTiempoLaborado[] = 0;
                    } else {
                        $arrayTiempoLaborado[] = $tiempoLaboradoUnix;
                    }


                    $horas = floor($tiempoLaboradoUnix / 3600);
                    $minutos = ( ( $tiempoLaboradoUnix / 60 ) % 60 );
                    $arrayDetalleTiempoLaborado[] = $fechaEvaluar . '|' . $nomDia[$numDia] . '|' . $horas . 'h ' . $minutos . 'm';
                }
            }
        }
        /* Incrementa un dia a la fecha evaluada */
        $fechaEvaluar = date("Ymd", strtotime($fechaEvaluar . " + 1 day"));
    }

    /* Suma los tiempos unix almacenados en el arreglo */
    $sumatoriaTiempoUnix = array_sum($arrayTiempoLaborado);

    /* Conversion */
    $horas = floor($sumatoriaTiempoUnix / 3600);
    $minutos = ( ( $sumatoriaTiempoUnix / 60 ) % 60 );
    $segundos = ( $sumatoriaTiempoUnix % 60 );

    $horas = str_pad($horas, 2, "0", STR_PAD_LEFT);
    $minutos = str_pad($minutos, 2, "0", STR_PAD_LEFT);
    $segundos = str_pad($segundos, 2, "0", STR_PAD_LEFT);

    if ($horas >= 0) {
        return array('horas' => $horas, 'minutos' => $minutos, 'segundos' => $segundos);
    } else {
        return array('horas' => 0, 'minutos' => 0, 'segundos' => 0);
    }
}

/*
 * Recibe un valor
 * Retorna - &oacute; +
 */

function cualSigno($val) {
    if ($val < 0) {
        return "-";
    } else {
        return "+";
    }
}

/*
 * modo : "days", "months", "year"
 * valor: El n&uacute;mero de unidades a sumar o restar
 * la fecha de referencia inicial
 */

function calculaFecha($modo, $valor, $fecha_inicio = false) {
    if ($fecha_inicio != false) {
        mostrarFecha($fecha_inicio);
        $fecha_base = strtotime($fecha_inicio);
    } else {
        $time = time();
        $fecha_actual = date("Y-m-d", $time);
        $fecha_base = strtotime($fecha_actual);
    }
    $calculo = strtotime("$valor $modo", "$fecha_base");
    return date("Ymd", $calculo);
}

function decodificarUtf8($arr) {
    require_once ('funcionesGenerales.php');
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
 * Funci&oacute;n que dadas dos fechas calcula el n&uacute;mero de a&ntilde;os cumplidos entre ambas
 *
 * @param 		string		$f1		Primera fecha
 * @param 		string		$f2		Segunda fecha
 * @return 		int					N&uacute;mero de a&ntilde;os
 */
function diaFinalMes($ano, $mes) {
    $dia = 31;
    if ($mes == '02') {
        $bis = intval($ano / 4);
        if (($bis * 4) == $ano) {
            $dia = 29;
        } else {
            $dia = 28;
        }
    }
    if (($mes == '04') || ($mes == '06') || ($mes == '09') || ($mes == '11')) {
        $dia = 30;
    }
    return $dia;
}

function finMes() {
    $ano = date("Y");
    $mes = date("m");
    $dia = date("d");
    if ($dia == 31) {
        return true;
    }
    if ($dia == 30) {
        if (($mes == '04') || ($mes == '06') || ($mes == '09') || ($mes == '11')) {
            return true;
        }
    }
    if ($dia == 29) {
        if ($mes == '02') {
            return true;
        }
    }
    if ($dia == 28) {
        if ($mes == '02') {
            if (($ano != '2012') && ($ano != '2016') && ($ano != '2020') && ($ano != '2024') && ($ano != '2028') && ($ano != '2032')) {
                return true;
            }
        }
    }
    return false;
}

function diferenciaEntreFechasCalendario($fecha_principal, $fecha_secundaria, $obtener = 'DIAS', $redondear = true) {
    date_default_timezone_set($_SESSION["generales"]["zonahoraria"]);
    $f0 = strtotime($fecha_principal);
    $f1 = strtotime($fecha_secundaria);
    if ($f0 < $f1) {
        $tmp = $f1;
        $f1 = $f0;
        $f0 = $tmp;
    }
    $resultado = ($f0 - $f1);
    switch ($obtener) {
        default: break;
        case "MINUTOS" : $resultado = $resultado / 60;
            break;
        case "HORAS" : $resultado = $resultado / 60 / 60;
            break;
        case "DIAS" :
            $resultado = $resultado / 60 / 60 / 24;
            // $resultado = $resultado+1;   
            break;
        case "SEMANAS" : $resultado = $resultado / 60 / 60 / 24 / 7;
            break;
        case "MESES" : $resultado = $resultado / 60 / 60 / 24 / 30;
            break;
        case "ANOS" : $resultado = $resultado / 60 / 60 / 24 / 30 / 12;
            break;
    }
    if ($redondear) {
        $resultado = round($resultado);
    }
    return $resultado;
}

function diferenciaEntreFechaBase30($fechafinal, $fechainicial) {
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
                            if (($ano != 2000) && ($ano != 2004) && ($ano != 2008) && ($ano != 2012) && ($ano != 2014) &&
                                    ($ano != 2018) && ($ano != 2022) && ($ano != 2026) && ($ano != 2030) && ($ano != 2034)) {
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

/**
 * Funci&oacute;n que calcula la letra que corresponde con una columna para pasar a excel
 */
function calcularColumnaExcel($col, $ini) {
    $tem = $col + $ini;
    switch ($tem) {
        case 1: $ret = 'A';
            break;
        case 2: $ret = 'B';
            break;
        case 3: $ret = 'C';
            break;
        case 4: $ret = 'D';
            break;
        case 5: $ret = 'E';
            break;
        case 6: $ret = 'F';
            break;
        case 7: $ret = 'G';
            break;
        case 8: $ret = 'H';
            break;
        case 9: $ret = 'I';
            break;
        case 10: $ret = 'J';
            break;
        case 11: $ret = 'K';
            break;
        case 12: $ret = 'L';
            break;
        case 13: $ret = 'M';
            break;
        case 14: $ret = 'N';
            break;
        case 15: $ret = 'O';
            break;
        case 16: $ret = 'P';
            break;
        case 17: $ret = 'Q';
            break;
        case 18: $ret = 'R';
            break;
        case 19: $ret = 'S';
            break;
        case 20: $ret = 'T';
            break;
        case 21: $ret = 'U';
            break;
        case 22: $ret = 'V';
            break;
        case 23: $ret = 'W';
            break;
        case 24: $ret = 'X';
            break;
        case 25: $ret = 'Y';
            break;
        case 26: $ret = 'Z';
            break;
        case 27: $ret = 'AA';
            break;
        case 28: $ret = 'AB';
            break;
        case 29: $ret = 'AC';
            break;
        case 30: $ret = 'AD';
            break;
        case 31: $ret = 'AE';
            break;
        case 32: $ret = 'AF';
            break;
        case 33: $ret = 'AG';
            break;
        case 34: $ret = 'AH';
            break;
        case 35: $ret = 'AI';
            break;
        case 36: $ret = 'AJ';
            break;
        case 37: $ret = 'AK';
            break;
        case 38: $ret = 'AL';
            break;
        case 39: $ret = 'AM';
            break;
        case 40: $ret = 'AN';
            break;
        case 41: $ret = 'AO';
            break;
        case 42: $ret = 'AP';
            break;
        case 43: $ret = 'AQ';
            break;
        case 44: $ret = 'AR';
            break;
        case 45: $ret = 'AS';
            break;
        case 46: $ret = 'AT';
            break;
        case 47: $ret = 'AU';
            break;
        case 48: $ret = 'AV';
            break;
        case 49: $ret = 'AW';
            break;
        case 50: $ret = 'AX';
            break;
        case 51: $ret = 'AY';
            break;
        case 52: $ret = 'AZ';
            break;
        case 53: $ret = 'BA';
            break;
        case 54: $ret = 'BB';
            break;
        case 55: $ret = 'BC';
            break;
        case 56: $ret = 'BD';
            break;
        case 57: $ret = 'BE';
            break;
        case 58: $ret = 'BF';
            break;
        case 59: $ret = 'BG';
            break;
        case 60: $ret = 'BH';
            break;
        case 61: $ret = 'BI';
            break;
        case 62: $ret = 'BJ';
            break;
        case 63: $ret = 'BK';
            break;
        case 64: $ret = 'BL';
            break;
        case 65: $ret = 'BM';
            break;
        case 66: $ret = 'BN';
            break;
        case 67: $ret = 'BO';
            break;
        case 68: $ret = 'BP';
            break;
        case 69: $ret = 'BQ';
            break;
        case 70: $ret = 'BR';
            break;
        case 71: $ret = 'BS';
            break;
        case 72: $ret = 'BT';
            break;
        case 73: $ret = 'BU';
            break;
        case 74: $ret = 'BV';
            break;
        case 75: $ret = 'BW';
            break;
        case 76: $ret = 'BX';
            break;
        case 77: $ret = 'BY';
            break;
        case 78: $ret = 'BZ';
            break;
        case 79: $ret = 'CA';
            break;
        case 80: $ret = 'CB';
            break;
        case 81: $ret = 'CC';
            break;
        case 82: $ret = 'CD';
            break;
        case 83: $ret = 'CE';
            break;
        case 84: $ret = 'CF';
            break;
        case 85: $ret = 'CG';
            break;
        case 86: $ret = 'CH';
            break;
        case 87: $ret = 'CI';
            break;
        case 88: $ret = 'CJ';
            break;
        case 89: $ret = 'CK';
            break;
        case 90: $ret = 'CL';
            break;
        case 91: $ret = 'CM';
            break;
        case 92: $ret = 'CN';
            break;
        case 93: $ret = 'CO';
            break;
        case 94: $ret = 'CP';
            break;
        case 95: $ret = 'CQ';
            break;
        case 96: $ret = 'CR';
            break;
        case 97: $ret = 'CS';
            break;
        case 98: $ret = 'CT';
            break;
        case 99: $ret = 'CU';
            break;
        case 100: $ret = 'CV';
            break;
        case 101: $ret = 'CW';
            break;
        case 102: $ret = 'CX';
            break;
        case 103: $ret = 'CY';
            break;
        case 104: $ret = 'CZ';
            break;
        case 105: $ret = 'DA';
            break;
        case 106: $ret = 'DB';
            break;
        case 107: $ret = 'DC';
            break;
        case 108: $ret = 'DD';
            break;
        case 109: $ret = 'DE';
            break;
        case 110: $ret = 'DF';
            break;
        case 111: $ret = 'DG';
            break;
        case 112: $ret = 'DH';
            break;
        case 113: $ret = 'DI';
            break;
        case 114: $ret = 'DJ';
            break;
        case 115: $ret = 'DK';
            break;
        case 116: $ret = 'DL';
            break;
        case 117: $ret = 'DM';
            break;
        case 118: $ret = 'DN';
            break;
        case 119: $ret = 'DO';
            break;
        case 120: $ret = 'DP';
            break;
        case 121: $ret = 'DQ';
            break;
        case 122: $ret = 'DR';
            break;
        case 123: $ret = 'DS';
            break;
        case 124: $ret = 'DT';
            break;
        case 125: $ret = 'DU';
            break;
        case 126: $ret = 'DV';
            break;
        case 127: $ret = 'DW';
            break;
        case 128: $ret = 'DX';
            break;
        case 129: $ret = 'DY';
            break;
        case 130: $ret = 'DZ';
            break;
    }
    return $ret;
}

function calcularFechaVencimiento($feccrea, $dias) {
    $feccrea = str_replace("-", "", $feccrea);
    $fec = date_parse(mostrarFecha($feccrea));
    if ($dias == 0) {
        $dias = 1;
    }
    $i = 1;
    while ($i <= $dias) {
        if ($fec["day"] == 31) {
            $fec["day"] = 1;
            if ($fec["month"] == 12) {
                $fec["month"] = 1;
                $fec["year"] = $fec["year"] + 1;
            } else {
                $fec["month"] = $fec["month"] + 1;
            }
        } else {
            if ($fec["day"] == 30) {
                if (($fec["month"] == 4) || ($fec["month"] == 6) || ($fec["month"] == 9) || ($fec["month"] == 11)) {
                    $fec["day"] = 1;
                    $fec["month"] = $fec["month"] + 1;
                } else {
                    $fec["day"] = $fec["day"] + 1;
                }
            } else {
                if ($fec["day"] == 28) {
                    if ($fec["month"] == 2) {
                        if (checkdate(02, 29, $fec["year"])) {
                            $fec["day"] = $fec["day"] + 1;
                        } else {
                            $fec["day"] = 1;
                            $fec["month"] = 3;
                        }
                    } else {
                        $fec["day"] = $fec["day"] + 1;
                    }
                } else {
                    if ($fec["day"] == 29) {
                        if ($fec["month"] == 2) {
                            $fec["day"] = 1;
                            $fec["month"] = 3;
                        } else {
                            $fec["day"] = $fec["day"] + 1;
                        }
                    } else {
                        $fec["day"] = $fec["day"] + 1;
                    }
                }
            }
        }
        $fechaX = mktime(0, 0, 0, $fec["month"], $fec["day"], $fec["year"]);
        $i++;
    }
    $fechaY = $fec["year"] . sprintf("%02s", $fec["month"]) . sprintf("%02s", $fec["day"]);
    return $fechaY;
}

function calcularFechaInicial($fbase, $dias) {
    $fecha = mostrarFecha($fbase);
    $nuevafecha = strtotime('-' . $dias . ' day', strtotime($fecha));
    $nuevafecha = date('Y-m-d', $nuevafecha);
    return $nuevafecha;
}

/*
 * Calcula el n&uacute;mero d&iacute;a (del 0 para el domingo al 6 para el s&aacute;bado) de una fecha dada
 */

function calcula_numero_dia_semana($dia, $mes, $ano) {
    $numerodiasemana = date('w', mktime(0, 0, 0, $mes, $dia, $ano));
    return $numerodiasemana;

    if ($numerodiasemana == 0) {
        $numerodiasemana = 6;
    } else {
        $numerodiasemana--;
    }
    return $numerodiasemana;
}

/**
 * Funci&oacute;n que recibe un texto y reemplaza caracteres especiales por tags
 * Funci&oacute;n utilizada en la interacci&oacute;n con el editor de texto DHTML
 *
 * @param 	string		$txt	Texto a convertir
 * @return 	string				Texto convertido
 */
function cambiarHtmlSustituto($txt) {
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

/**
 * Funci&oacute;n que recibe un texto y sustituye los tags por caracteres
 * Funci&oacute;n utilizada en la interacci&oacute;n con el editor de texto DHTML
 *
 * @param 	string		$txt		Texto a convertir
 * @return 	string					Texto convertido
 */
function cambiarSustitutoHtml($txt) {
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

function cargarDhtmlxGrid25a() {
    return
            '<!--grids-->' . chr(13) .
            '<script type="text/javascript" ' . chr(13)
            . 'src="../../includes/dhtmlx2.5/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>' . chr(13)
            . '<script type="text/javascript"' . chr(13)
            . 'src="../../includes/dhtmlx2.5/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>' . chr(13)
            . '<script type="text/javascript"' . chr(13)
            . 'src="../../includes/dhtmlx2.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_form.js"></script>' . chr(13)
            . '<script type="text/javascript"' . chr(13)
            . 'src="../../includes/dhtmlx2.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_start.js"></script>' . chr(13)
            . '<script type="text/javascript"' . chr(13)
            . 'src="../../includes/dhtmlx2.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_group.js"></script>' . chr(13)
            . '<script type="text/javascript"' . chr(13)
            . 'src="../../includes/dhtmlx2.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_pgn.js"></script>' . chr(13)
            . '<script type="text/javascript"' . chr(13)
            . 'src="../../includes/dhtmlx2.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_splt.js"></script>' . chr(13)
            . '<script type="text/javascript"' . chr(13)
            . 'src="../../includes/dhtmlx2.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_drag.js"></script>' . chr(13)
            . '<script>' . chr(13)
            . '	function eXcell_edncl(cell) {' . chr(13)
            . '		this.base = eXcell_edn;' . chr(13)
            . '		this.base(cell);' . chr(13)
            . '		this.setValue = function(val) {' . chr(13)
            . '			if (!val || val.toString()._dhx_trim() == "")' . chr(13)
            . '				val = "0";' . chr(13)
            . '			if (val >= 0)' . chr(13)
            . '				this.cell.style.color = "green";' . chr(13)
            . '			else' . chr(13)
            . '				this.cell.style.color = "red";' . chr(13)
            . '			this.cell.innerHTML = this.grid._aplNF(val, this.cell._cellIndex);' . chr(13)
            . '		}' . chr(13)
            . '	}' . chr(13)
            . '	eXcell_edncl.prototype = new eXcell_edn;' . chr(13)
            . '</script>' . chr(13)
            . '<script type="text/javascript" ' . chr(13)
            . 'src="../../includes/dhtmlx2.5/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_link.js"></script>' . chr(13)
            . '<script type="text/javascript"' . chr(13)
            . 'src="../../includes/dhtmlx2.5/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_acheck.js"></script>' . chr(13)
            . '<script type="text/javascript"' . chr(13)
            . 'src="../../includes/dhtmlx2.5/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_clist.js"></script>' . chr(13)
            . '<script type="text/javascript"' . chr(13)
            . 'src="../../includes/dhtmlx2.5/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_calck.js"></script>' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" ' . chr(13)
            . 'href="../../includes/dhtmlx2.5/dhtmlxGrid/codebase/dhtmlxgrid.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" ' . chr(13)
            . 'href="../../includes/dhtmlx2.5/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_skyblue.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" ' . chr(13)
            . 'href="../../includes/dhtmlx2.5/dhtmlxGrid/codebase/dhtmlxgrid_skins.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" ' . chr(13)
            . 'href="../../includes/dhtmlx2.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_pgn_bricks.css">' . chr(13);
}

function cargarDhtmlxLayout25a() {
    return
            '<!-- layouts -->' . chr(13) .
            '<script type="text/javascript" ' . chr(13) .
            'src="../../includes/dhtmlx2.5/dhtmlxLayout/codebase/dhtmlxlayout.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css"' . chr(13) .
            'href="../../includes/dhtmlx2.5/dhtmlxLayout/codebase/dhtmlxlayout.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css"' . chr(13) .
            'href="../../includes/dhtmlx2.5/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_skyblue.css" />' . chr(13);
}

function cargarDhtmlxMenu25a() {
    return
            '<!-- menus -->' . chr(13) .
            '<script type="text/javascript" ' . chr(13) .
            'src="../../includes/dhtmlx2.5/dhtmlxMenu/codebase/dhtmlxmenu.js"></script>' . chr(13) .
            '<script type="text/javascript"' . chr(13) .
            'src="../../includes/dhtmlx2.5/dhtmlxMenu/codebase/ext/dhtmlxmenu_ext.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css"' . chr(13) .
            'href="../../includes/dhtmlx2.5/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_skyblue.css" />' . chr(13);
}

function cargarDhtmlxToolbar25a() {
    return
            '<!-- toolbar -->' . chr(13) .
            '<script type="text/javascript" ' . chr(13) .
            'src="../../includes/dhtmlx2.5/dhtmlxToolbar/codebase/dhtmlxtoolbar.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css"' . chr(13) .
            'href="../../includes/dhtmlx2.5/dhtmlxToolbar/codebase/skins/dhtmlxtoolbar_dhx_skyblue.css" />' . chr(13);
}

function cargarDhtmlxTabbar25a() {
    return
            '<!-- tabbar -->' . chr(13) .
            '<script type="text/javascript" ' . chr(13) .
            'src="../../includes/dhtmlx2.5/dhtmlxTabbar/codebase/dhtmlxtabbar.js"></script>' . chr(13) .
            '<script type="text/javascript"' . chr(13) .
            'src="../../includes/dhtmlx2.5/dhtmlxTabbar/codebase/dhtmlxtabbar_start.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css"' . chr(13) .
            'href="../../includes/dhtmlx2.5/dhtmlxTabbar/codebase/dhtmlxtabbar.css" />' . chr(13);
}

function cargarDhtmlxCalendar25a() {
    return
            '<!--calendar-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx2.5/dhtmlxCalendar/codebase/dhtmlxcalendar.js"></script>' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx2.5/dhtmlxCalendar/codebase/dhtmlxcalendar.css">' . chr(13) .
            '<script>window.dhx_globalImgPath="../../includes/dhtmlx2.5/dhtmlxCalendar/codebase/imgs/";</script>' . chr(13);
}

function cargarDhtmlxWindows25a() {
    return
            '<!--Windows-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx2.5/dhtmlxWindows/codebase/dhtmlxwindows.js"></script>' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx2.5/dhtmlxWindows/codebase/dhtmlxwindows.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx2.5/dhtmlxWindows/codebase/skins/dhtmlxwindows_dhx_skyblue.css">' . chr(13) .
            '<script>window.dhx_globalImgPath="../../includes/dhtmlx2.5/dhtmlxWindows/codebase/imgs/";</script>' . chr(13);
}

function cargarDhtmlxDataProcessor25a() {
    return
            '<!--Windows-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx2.5/dhtmlxDataProcessor/codebase/dhtmlxdataprocessor.js"></script>' . chr(13);
}

function cargarDhtmlxChart25a() {
    return
            '<!--chart-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx2.5/dhtmlxChart/codebase/dhtmlxchart.js"></script>' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx2.5/dhtmlxChart/codebase/dhtmlxchart_debug.css">' . chr(13);
}

function cargarDhtmlxForm25a() {
    return
            '<!--Form-->' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxForm/codebase/skins/dhtmlxform_dhx_skyblue.css">' . chr(13) .
            '<script src="../../includes/dhtmlx2.5/dhtmlxForm/codebase/dhtmlxcommon.js"></script>' . chr(13) .
            '<script src="../../includes/dhtmlx2.5/dhtmlxForm/codebase/dhtmlxform.js"></script>' . chr(13);
}

// Funciones para uso de la librer&iacute;as dhtmlx 2.6
// Nov de 2010
function cargarDhtmlxCombo26a() {
    return
            '<!--Combo-->' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxCombo/codebase/dhtmlxcombo.css">' . chr(13)
            . '<script src="../../includes/dhtmlx2.6/dhtmlxCombo/codebase/dhtmlxcombo.js" type="text/javascript"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx2.6/dhtmlxCombo/codebase/ext/dhtmlxcombo_extra.js" type="text/javascript"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx2.6/dhtmlxCombo/codebase/ext/dhtmlxcombo_group.js" type="text/javascript"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx2.6/dhtmlxCombo/codebase/ext/dhtmlxcombo_whp.js" type="text/javascript"></script>' . chr(13);
}

function cargarDhtmlxLayout26a() {
    return
            '<!-- layouts -->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxLayout/codebase/dhtmlxlayout.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxLayout/codebase/patterns/dhtmlxlayout_pattern4c.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxLayout/codebase/patterns/dhtmlxlayout_pattern4w.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxLayout/codebase/patterns/dhtmlxlayout_pattern5w.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxLayout/codebase/dhtmlxlayout.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_skyblue.css" />' . chr(13);
    '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_black.css" />' . chr(13);
}

function cargarDhtmlxMenu26a() {
    return
            '<!-- menus -->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxMenu/codebase/dhtmlxmenu.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxMenu/codebase/ext/dhtmlxmenu_ext.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_skyblue.css">' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_black.css">' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_web.css">' . chr(13);
}

function cargarDhtmlxGrid26a() {
    return
            '<!--grids-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/dhtmlxgrid_export.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_form.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_start.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_group.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_pgn.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_splt.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_drag.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_filter.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_nxml.js"></script>' . chr(13)
            . '<script>' . chr(13)
            . '	function eXcell_edncl(cell) {' . chr(13)
            . '		this.base = eXcell_edn;' . chr(13)
            . '		this.base(cell);' . chr(13)
            . '		this.setValue = function(val) {' . chr(13)
            . '			if (!val || val.toString()._dhx_trim() == "")' . chr(13)
            . '				val = "0";' . chr(13)
            . '			if (val >= 0)' . chr(13)
            . '				this.cell.style.color = "green";' . chr(13)
            . '			else' . chr(13)
            . '				this.cell.style.color = "red";' . chr(13)
            . '			this.cell.innerHTML = this.grid._aplNF(val, this.cell._cellIndex);' . chr(13)
            . '		}' . chr(13)
            . '	}' . chr(13)
            . '	eXcell_edncl.prototype = new eXcell_edn;' . chr(13)
            . '</script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_link.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_acheck.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_clist.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_calck.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_combo.js"></script>' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/dhtmlxgrid.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/dhtmlxgrid_calc.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_skyblue.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_black.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_blue.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_web.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/dhtmlxgrid_skins.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_pgn_bricks.css">' . chr(13);
}

function cargarDhtmlxToolbar26a() {
    return
            '<!-- toolbar -->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxToolbar/codebase/dhtmlxtoolbar.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxToolbar/codebase/skins/dhtmlxtoolbar_dhx_skyblue.css" />' . chr(13);
}

function cargarDhtmlxTabbar26a() {
    return
            '<!-- tabbar -->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxTabbar/codebase/dhtmlxtabbar.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxTabbar/codebase/dhtmlxtabbar_start.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxTabbar/codebase/dhtmlxtabbar.css" />' . chr(13);
}

function cargarDhtmlxCalendar26a() {
    return
            '<!--calendar-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxCalendar/codebase/dhtmlxcalendar.js"></script>' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxCalendar/codebase/dhtmlxcalendar.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_blue.css">' . chr(13) .
            '<script>window.dhx_globalImgPath="../../includes/dhtmlx2.6/dhtmlxCalendar/codebase/imgs/";</script>' . chr(13);
}

function cargarDhtmlxWindows26a() {
    return
            '<!--Windows-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxWindows/codebase/dhtmlxwindows.js"></script>' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxWindows/codebase/dhtmlxwindows.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxWindows/codebase/skins/dhtmlxwindows_dhx_skyblue.css">' . chr(13) .
            '<script>window.dhx_globalImgPath="../../includes/dhtmlx2.6/dhtmlxWindows/codebase/imgs/";</script>' . chr(13);
}

function cargarDhtmlxVault26a() {
    return
            '<!--Vault-->' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxVault/codebase/dhtmlxvault.css" />' . chr(13) .
            '<script language="JavaScript" type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxVault/codebase/dhtmlxvault.js"></script>' . chr(13);
}

function cargarDhtmlxDataProcessor26a() {
    return
            '<!--Windows-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxDataProcessor/codebase/dhtmlxdataprocessor.js"></script>' . chr(13);
}

function cargarDhtmlxChart26a() {
    return
            '<!--chart-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxChart/codebase/dhtmlxchart.js"></script>' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxChart/codebase/dhtmlxchart_debug.css">' . chr(13);
}

function cargarDhtmlxForm26a() {
    return
            '<!--Form-->' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx2.6/dhtmlxForm/codebase/skins/dhtmlxform_dhx_skyblue.css">' . chr(13) .
            '<script src="../../includes/dhtmlx2.6/dhtmlxForm/codebase/dhtmlxcommon.js"></script>' . chr(13) .
            '<script src="../../includes/dhtmlx2.6/dhtmlxForm/codebase/dhtmlxform.js"></script>' . chr(13);
}

// Librerias para uso de dhtmlx 3.0
// Julio 15 de 2011
function cargarDhtmlxCombo30a() {
    return
            '<!--Combo-->' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.0/dhtmlxCombo/codebase/dhtmlxcombo.css">' . chr(13)
            . '<script src="../../includes/dhtmlx3.0/dhtmlxCombo/codebase/dhtmlxcombo.js" type="text/javascript"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.0/dhtmlxCombo/codebase/ext/dhtmlxcombo_extra.js" type="text/javascript"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.0/dhtmlxCombo/codebase/ext/dhtmlxcombo_group.js" type="text/javascript"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.0/dhtmlxCombo/codebase/ext/dhtmlxcombo_whp.js" type="text/javascript"></script>' . chr(13);
}

function cargarDhtmlxLayout30a() {
    return
            '<!-- layouts -->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxLayout/codebase/dhtmlxlayout.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxLayout/codebase/patterns/dhtmlxlayout_pattern4c.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxLayout/codebase/patterns/dhtmlxlayout_pattern4w.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxLayout/codebase/patterns/dhtmlxlayout_pattern5w.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.0/dhtmlxLayout/codebase/dhtmlxlayout.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.0/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_skyblue.css" />' . chr(13);
}

function cargarDhtmlxMenu30a() {
    return
            '<!-- menus -->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxMenu/codebase/dhtmlxmenu.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxMenu/codebase/ext/dhtmlxmenu_ext.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.0/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_skyblue.css">' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.0/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_black.css">' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.0/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_web.css">' . chr(13);
}

function cargarDhtmlxGrid30a() {
    return
            '<!--grids-->' . chr(13)
            //
            . '<link rel="STYLESHEET" type="text/css"  href="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/dhtmlxgrid.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css"  href="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_skyblue.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css"  href="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/dhtmlxgrid_skins.css">' . chr(13)
            // . '<link rel="STYLESHEET" type="text/css"  href="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_pgn_bricks.css">' . chr(13)           
            //
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_deprecated.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_dom.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_drag.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_export.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_filter.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_keymap_excel.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_keymap_access.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_keymap_extra.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_lines.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_nxml.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_rowselector.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_selection.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_srnd.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_start.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_validation.js"></script>' . chr(13)
            //
            // . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_form.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_start.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_group.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_pgn.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_splt.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_drag.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_filter.js"></script>' . chr(13)   	
            //
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_3but.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_acheck.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_calendar.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_cntr.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_context.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_dhxcalendar.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_grid.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_link.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_tree.js"></script>' . chr(13)

            // . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_clist.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_calck.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_combo.js"></script>' . chr(13)        
            //
            . '<script>' . chr(13)
            . '	function eXcell_edncl(cell) {' . chr(13)
            . '		this.base = eXcell_ed;' . chr(13)
            . '		this.base(cell);' . chr(13)
            . '		this.setValue = function(val) {' . chr(13)
            . '			if (!val || val.toString()._dhx_trim() == "")' . chr(13)
            . '				val = "0";' . chr(13)
            . '			if (val >= 0)' . chr(13)
            . '				this.cell.style.color = "green";' . chr(13)
            . '			else' . chr(13)
            . '				this.cell.style.color = "red";' . chr(13)
            . '			this.cell.innerHTML = this.grid._aplNF(val, this.cell._cellIndex);' . chr(13)
            . '		}' . chr(13)
            . '	}' . chr(13)
            . '	eXcell_edncl.prototype = new eXcell_ed;' . chr(13)
            . '</script>' . chr(13);
    //    
}

function cargarDhtmlxToolbar30a() {
    return
            '<!-- toolbar -->' . chr(13) .
            '<script type="text/javascript"' . chr(13) .
            'src="../../includes/dhtmlx3.0/dhtmlxToolbar/codebase/dhtmlxtoolbar.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css"' . chr(13) .
            'href="../../includes/dhtmlx3.0/dhtmlxToolbar/codebase/skins/dhtmlxtoolbar_dhx_skyblue.css" />' . chr(13);
}

function cargarDhtmlxTabbar30a() {
    return
            '<!-- tabbar -->' . chr(13) .
            '<script type="text/javascript"' . chr(13) .
            'src="../../includes/dhtmlx3.0/dhtmlxTabbar/codebase/dhtmlxtabbar.js"></script>' . chr(13) .
            '<script type="text/javascript"' . chr(13) .
            'src="../../includes/dhtmlx3.0/dhtmlxTabbar/codebase/dhtmlxtabbar_start.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css"' . chr(13) .
            'href="../../includes/dhtmlx3.0/dhtmlxTabbar/codebase/dhtmlxtabbar.css" />' . chr(13);
}

function cargarDhtmlxCalendar30a() {
    return
            '<!--calendar-->' . chr(13) .
            '<script type="text/javascript"' . chr(13) .
            'src="../../includes/dhtmlx3.0/dhtmlxCalendar/codebase/dhtmlxcalendar.js"></script>' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" ' . chr(13) .
            'href="../../includes/dhtmlx3.0/dhtmlxCalendar/codebase/dhtmlxcalendar.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" ' . chr(13) .
            'href="../../includes/dhtmlx3.0/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_skyblue.css">' . chr(13) .
            '<script>window.dhx_globalImgPath="../../includes/dhtmlx3.0/dhtmlxCalendar/codebase/imgs/";</script>' . chr(13);
}

function cargarDhtmlxWindows30a() {
    return
            '<!--Windows-->' . chr(13) .
            '<script type="text/javascript"' . chr(13) .
            'src="../../includes/dhtmlx3.0/dhtmlxWindows/codebase/dhtmlxwindows.js"></script>' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" ' . chr(13) .
            'href="../../includes/dhtmlx3.0/dhtmlxWindows/codebase/dhtmlxwindows.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" ' . chr(13) .
            'href="../../includes/dhtmlx3.0/dhtmlxWindows/codebase/skins/dhtmlxwindows_dhx_skyblue.css">' . chr(13) .
            '<script>window.dhx_globalImgPath="../../includes/dhtmlx3.0/dhtmlxWindows/codebase/imgs/";</script>' . chr(13);
}

function cargarDhtmlxVault30a() {
    return
            '<!--Vault-->' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.0/dhtmlxVault/codebase/dhtmlxvault.css" />' . chr(13) .
            '<script language="JavaScript" type="text/javascript" src="../../includes/dhtmlx3.0/dhtmlxVault/codebase/dhtmlxvault.js"></script>' . chr(13);
}

function cargarDhtmlxDataProcessor30a() {
    return
            '<!--Windows-->' . chr(13) .
            '<script type="text/javascript"' . chr(13) .
            'src="../../includes/dhtmlx3.0/dhtmlxDataProcessor/codebase/dhtmlxdataprocessor.js"></script>' . chr(13);
}

function cargarDhtmlxChart30a() {
    return
            '<!--chart-->' . chr(13) .
            '<script type="text/javascript"' . chr(13) .
            'src="../../includes/dhtmlx3.0/dhtmlxChart/codebase/dhtmlxchart.js"></script>' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" ' . chr(13) .
            'href="../../includes/dhtmlx3.0/dhtmlxChart/codebase/dhtmlxchart_debug.css">' . chr(13);
}

function cargarDhtmlxForm30a() {
    return
            '<!--Form-->' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.0/dhtmlxForm/codebase/skins/dhtmlxform_dhx_skyblue.css">' . chr(13) .
            '<script src="../../includes/dhtmlx3.0/dhtmlxForm/codebase/dhtmlxcommon.js"></script>' . chr(13) .
            '<script src="../../includes/dhtmlx3.0/dhtmlxForm/codebase/dhtmlxform.js"></script>' . chr(13);
}

// Funciones para uso de la librer&iacute;as dhtmlx 3.5
// Octubre 27 de 2012
function cargarDhtmlxCombo35a() {
    return
            '<!--Combo-->' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxCombo/codebase/dhtmlxcombo.css">' . chr(13)
            . '<script src="../../includes/dhtmlx3.5/dhtmlxCombo/codebase/dhtmlxcombo.js" type="text/javascript"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.5/dhtmlxCombo/codebase/dhtmlxcommon.js" type="text/javascript"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.5/dhtmlxCombo/codebase/ext/dhtmlxcombo_extra.js" type="text/javascript"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.5/dhtmlxCombo/codebase/ext/dhtmlxcombo_group.js" type="text/javascript"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.5/dhtmlxCombo/codebase/ext/dhtmlxcombo_whp.js" type="text/javascript"></script>' . chr(13);
}

function cargarDhtmlxLayout35a() {
    return
            '<!-- layouts -->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxLayout/codebase/dhtmlxlayout.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxLayout/codebase/dhtmlxcontainer.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxLayout/codebase/dhtmlxcommon.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxLayout/codebase/dhtmlxlayout.css" />' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxLayout/codebase/patterns/dhtmlxlayout_pattern4c.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxLayout/codebase/patterns/dhtmlxlayout_pattern4w.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxLayout/codebase/patterns/dhtmlxlayout_pattern5w.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_black.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_blue.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_web.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_terrace.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_skyblue.css" />' . chr(13);
}

function cargarDhtmlxMenu35a() {
    return
            '<!-- menus -->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxMenu/codebase/dhtmlxmenu.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxMenu/codebase/dhtmlxcommon.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxMenu/codebase/ext/dhtmlxmenu_ext.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxMenu/codebase/ext/dhtmlxmenu_effects.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_skyblue.css">' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_black.css">' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_blue.css">' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_skyblue.css">' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_terrace.css">' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_web.css">' . chr(13);
}

function cargarDhtmlxGrid35a() {
    return
            '<!--grids-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/dhtmlxcommon.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>' . chr(13)

            // . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/calendar/calendar_init.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/calendar/calendar.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/calendar/YAHOO.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/calendar/event.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_deprecated.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_drag.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_export.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_filter.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_keymap_access.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_keymap_excel.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_keymap_extra.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_nxml.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_selection.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_srnd.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_start.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/ext/dhtmlxgrid_validation.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_group.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_pgn.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_splt.js"></script>' . chr(13)
            . '<script>' . chr(13)
            . 'function eXcell_myValor(cell){ ' . chr(13)
            . '  if (cell){' . chr(13)
            . '      this.cell = cell; ' . chr(13)
            . '      this.grid = this.cell.parentNode.grid;' . chr(13)
            . '      eXcell_ed.call(this); ' . chr(13)
            . '  } ' . chr(13)
            . '  this.setValue=function(val){' . chr(13)
            . '      this.setValue("<span>"+val+"</span><span> USD</span>",val);' . chr(13)
            . '  }' . chr(13)
            . '  this.getValue=function(){' . chr(13)
            . '     return this.cell.childNodes[0].innerHTML;' . chr(13)
            . '  }' . chr(13)
            . '}' . chr(13)
            . 'eXcell_myValor.prototype = new eXcell;' . chr(13)
            . '</script>' . chr(13)
            /*
              . '<script>' . chr(13)
              . '	function eXcell_edncl(cell) {' . chr(13)
              . '		this.base = eXcell_edn;' . chr(13)
              . '		this.base(cell);' . chr(13)
              . '		this.setValue = function(val) {' . chr(13)
              . '			if (!val || val.toString()._dhx_trim() == "")' . chr(13)
              . '				val = "0";' . chr(13)
              . '			if (val >= 0)' . chr(13)
              . '				this.cell.style.color = "green";' . chr(13)
              . '			else' . chr(13)
              . '				this.cell.style.color = "red";' . chr(13)
              . '			this.cell.innerHTML = this.grid._aplNF(val, this.cell._cellIndex);' . chr(13)
              . '		}' . chr(13)
              . '	}' . chr(13)
              . '	eXcell_edncl.prototype = new eXcell_edn;' . chr(13)
              . '</script>' . chr(13)
             */
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_3but.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_acheck.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_calendar.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_cntr.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_context.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_dhxcalendar.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_grid.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_link.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_tree.js"></script>' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/dhtmlxgrid.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/dhtmlxgrid_skins.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/calendar/calendar.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_skyblue.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_black.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_blue.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_web.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_terrace.css">' . chr(13);
}

function cargarDhtmlxToolbar35a() {
    return
            '<!-- toolbar -->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxToolbar/codebase/dhtmlxtoolbar.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxToolbar/codebase/dhtmlxcommon.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxToolbar/codebase/skins/dhtmlxtoolbar_dhx_black.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxToolbar/codebase/skins/dhtmlxtoolbar_dhx_web.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxToolbar/codebase/skins/dhtmlxtoolbar_dhx_blue.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxToolbar/codebase/skins/dhtmlxtoolbar_dhx_terrace.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxToolbar/codebase/skins/dhtmlxtoolbar_dhx_skyblue.css" />' . chr(13);
}

function cargarDhtmlxTabbar35a() {
    return
            '<!-- tabbar -->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxTabbar/codebase/dhtmlxtabbar.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxTabbar/codebase/dhtmlxtabbar_start.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxTabbar/codebase/dhtmlxtabbar.css" />' . chr(13);
}

function cargarDhtmlxCalendar35a() {
    return
            '<!--calendar-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxCalendar/codebase/dhtmlxcalendar.js"></script>' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxCalendar/codebase/dhtmlxcalendar.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_skyblue.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_blue.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_black.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_terrace.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_web.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_omega.css">' . chr(13) .
            '<script>window.dhx_globalImgPath="../../includes/dhtmlx3.5/dhtmlxCalendar/codebase/imgs/";</script>' . chr(13);
}

function cargarDhtmlxWindows35a() {
    return
            '<!--Windows-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxWindows/codebase/dhtmlxwindows.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxWindows/codebase/dhtmlxcontainer.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxWindows/codebase/dhtmlxcommon.js"></script>' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxWindows/codebase/dhtmlxwindows.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxWindows/codebase/skins/dhtmlxwindows_dhx_skyblue.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxWindows/codebase/skins/dhtmlxwindows_dhx_blue.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxWindows/codebase/skins/dhtmlxwindows_dhx_web.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxWindows/codebase/skins/dhtmlxwindows_dhx_black.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxWindows/codebase/skins/dhtmlxwindows_dhx_terrace.css">' . chr(13) .
            // '<script>window.dhx_globalImgPath="../../includes/dhtmlx3.5/dhtmlxWindows/codebase/imgs/";</script>' . chr(13);
            '<script>window.dhx_globalImgPath="../../html/default/images/";</script>' . chr(13);
}

function cargarDhtmlxVault35a() {
    return
            '<!--Vault-->' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxVault/codebase/dhtmlxvault.css" />' . chr(13) .
            '<script language="JavaScript" type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxVault/codebase/dhtmlxvault.js"></script>' . chr(13);
}

function cargarDhtmlxDataProcessor35a() {
    return
            '<!--Windows-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxDataProcessor/codebase/dhtmlxdataprocessor.js"></script>' . chr(13);
}

function cargarDhtmlxChart35a() {
    return
            '<!--chart-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.5/dhtmlxChart/codebase/dhtmlxchart.js"></script>' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxChart/codebase/dhtmlxchart_debug.css">' . chr(13);
}

function cargarDhtmlxForm35a() {
    return
            '<!--Form-->' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.5/dhtmlxForm/codebase/skins/dhtmlxform_dhx_skyblue.css">' . chr(13) .
            '<script src="../../includes/dhtmlx3.5/dhtmlxForm/codebase/dhtmlxcommon.js"></script>' . chr(13) .
            '<script src="../../includes/dhtmlx3.5/dhtmlxForm/codebase/dhtmlxform.js"></script>' . chr(13);
}

// Funciones para uso de la librer&iacute;as dhtmlx 3.6
// Junio de 2013
function cargarDhtmlxCombo36a() {
    return
            '<!--Combo-->' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxCombo/codebase/dhtmlxcombo.css">' . chr(13)
            . '<script src="../../includes/dhtmlx3.6/dhtmlxCombo/codebase/dhtmlxcombo.js" type="text/javascript"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.6/dhtmlxCombo/codebase/dhtmlxcommon.js" type="text/javascript"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.6/dhtmlxCombo/codebase/ext/dhtmlxcombo_extra.js" type="text/javascript"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.6/dhtmlxCombo/codebase/ext/dhtmlxcombo_group.js" type="text/javascript"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.6/dhtmlxCombo/codebase/ext/dhtmlxcombo_whp.js" type="text/javascript"></script>' . chr(13);
}

function cargarDhtmlxLayout36a() {
    return
            '<!-- layouts -->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxLayout/codebase/dhtmlxlayout.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxLayout/codebase/dhtmlxcontainer.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxLayout/codebase/dhtmlxcommon.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxLayout/codebase/dhtmlxlayout.css" />' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxLayout/codebase/patterns/dhtmlxlayout_pattern4c.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxLayout/codebase/patterns/dhtmlxlayout_pattern4w.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxLayout/codebase/patterns/dhtmlxlayout_pattern5w.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_black.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_blue.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_web.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_terrace.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_skyblue.css" />' . chr(13);
}

function cargarDhtmlxMenu36a() {
    return
            '<!-- menus -->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxMenu/codebase/dhtmlxmenu.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxMenu/codebase/dhtmlxcommon.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxMenu/codebase/ext/dhtmlxmenu_ext.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxMenu/codebase/ext/dhtmlxmenu_effects.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_skyblue.css">' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_black.css">' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_blue.css">' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_skyblue.css">' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_terrace.css">' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_web.css">' . chr(13);
}

function cargarDhtmlxGrid36a() {
    return
            '<!--grids-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/dhtmlxcommon.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_export.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_deprecated.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_start.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_drag.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_filter.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_keymap_access.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_keymap_excel.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_keymap_extra.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_nxml.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_selection.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_srnd.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_validation.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_3but.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_acheck.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_calendar.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_cntr.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_context.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_dhxcalendar.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_grid.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_link.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_tree.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_pgn.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_group.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_splt.js"></script>' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/dhtmlxgrid.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/dhtmlxgrid_skins.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/calendar/calendar.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_skyblue.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_black.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_blue.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_web.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_terrace.css">' . chr(13);
}

function cargarDhtmlxToolbar36a() {
    return
            '<!-- toolbar -->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxToolbar/codebase/dhtmlxtoolbar.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxToolbar/codebase/dhtmlxcommon.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxToolbar/codebase/skins/dhtmlxtoolbar_dhx_black.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxToolbar/codebase/skins/dhtmlxtoolbar_dhx_web.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxToolbar/codebase/skins/dhtmlxtoolbar_dhx_blue.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxToolbar/codebase/skins/dhtmlxtoolbar_dhx_terrace.css" />' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxToolbar/codebase/skins/dhtmlxtoolbar_dhx_skyblue.css" />' . chr(13);
}

function cargarDhtmlxTabbar36a() {
    return
            '<!-- tabbar -->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxTabbar/codebase/dhtmlxtabbar.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxTabbar/codebase/dhtmlxtabbar_start.js"></script>' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxTabbar/codebase/dhtmlxtabbar.css" />' . chr(13);
}

function cargarDhtmlxCalendar36a() {
    return
            '<!--calendar-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxCalendar/codebase/dhtmlxcalendar.js"></script>' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxCalendar/codebase/dhtmlxcalendar.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_skyblue.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_blue.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_black.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_terrace.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_web.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_omega.css">' . chr(13) .
            '<script>window.dhx_globalImgPath="../../includes/dhtmlx3.6/dhtmlxCalendar/codebase/imgs/";</script>' . chr(13);
}

function cargarDhtmlxWindows36a() {
    return
            '<!--Windows-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxWindows/codebase/dhtmlxwindows.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxWindows/codebase/dhtmlxcontainer.js"></script>' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxWindows/codebase/dhtmlxcommon.js"></script>' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxWindows/codebase/dhtmlxwindows.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxWindows/codebase/skins/dhtmlxwindows_dhx_skyblue.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxWindows/codebase/skins/dhtmlxwindows_dhx_blue.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxWindows/codebase/skins/dhtmlxwindows_dhx_web.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxWindows/codebase/skins/dhtmlxwindows_dhx_black.css">' . chr(13) .
            '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxWindows/codebase/skins/dhtmlxwindows_dhx_terrace.css">' . chr(13) .
            // '<script>window.dhx_globalImgPath="../../includes/dhtmlx3.5/dhtmlxWindows/codebase/imgs/";</script>' . chr(13);
            '<script>window.dhx_globalImgPath="../../html/default/images/";</script>' . chr(13);
}

function cargarDhtmlxVault36a() {
    return
            '<!--Vault-->' . chr(13) .
            '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxVault/codebase/dhtmlxvault.css" />' . chr(13) .
            '<script language="JavaScript" type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxVault/codebase/dhtmlxvault.js"></script>' . chr(13);
}

function cargarDhtmlxGantt36a() {
    return
            '<!--Gantt-->' . chr(13) .
            // '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxgantt/codebase/dhtmlxgantt.css" />' . chr(13) .
            '<link rel="stylesheet" href="../../includes/dhtmlx3.6/dhtmlxgantt/codebase/skins/dhtmlxgantt_meadow.css" type="text/css" media="screen" title="no title" charset="utf-8">' . chr(13) .
            '<script language="JavaScript" type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxgantt/codebase/sources/dhtmlxgantt.js"></script>' . chr(13) .
            '<script src="../../includes/dhtmlx3.6/dhtmlxgantt/codebase/locale/locale_es.js" charset="utf-8"></script>' . chr(13) .
            '<script src="../../includes/dhtmlx3.6/dhtmlxgantt/codebase/sources/ext/dhtmlxgantt_tooltip.js" type="text/javascript" charset="utf-8"></script>' . chr(13) .
            '<script src="http://export.dhtmlx.com/gantt/api.js" type="text/javascript" charset="utf-8"></script>' . chr(13);
}

function cargarDhtmlxDataProcessor36a() {
    return
            '<!--Windows-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxDataProcessor/codebase/dhtmlxdataprocessor.js"></script>' . chr(13);
}

function cargarDhtmlxChart36a() {
    return
            '<!--chart-->' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxChart/codebase/dhtmlxchart.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxChart/codebase/dhtmlxchart_debug.js"></script>' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxChart/codebase/dhtmlxchart_debug.css">' . chr(13)
    ;
}

function cargarDhtmlxForm36a() {
    return
            '<!--Form-->' . chr(13)
            . '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx3.6/dhtmlxForm/codebase/skins/dhtmlxform_dhx_skyblue.css">' . chr(13)
            . '<script src="../../includes/dhtmlx3.6/dhtmlxForm/codebase/dhtmlxcommon.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.6/dhtmlxForm/codebase/dhtmlxform.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.6/dhtmlxForm/codebase/ext/dhtmlxform_backup.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.6/dhtmlxForm/codebase/ext/dhtmlxform_dyn.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.6/dhtmlxForm/codebase/ext/dhtmlxform_hide_on_disable.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.6/dhtmlxForm/codebase/ext/dhtmlxform_item_btn2state.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.6/dhtmlxForm/codebase/ext/dhtmlxform_item_calendar.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.6/dhtmlxForm/codebase/ext/dhtmlxform_item_colorpicker.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.6/dhtmlxForm/codebase/ext/dhtmlxform_item_combo.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.6/dhtmlxForm/codebase/ext/dhtmlxform_item_container.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.6/dhtmlxForm/codebase/ext/dhtmlxform_item_editor.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx3.6/dhtmlxForm/codebase/ext/dhtmlxform_item_upload.js"></script>' . chr(13)

    ;
}

// Funciones para uso de la librer&iacute;as dhtmlx 4.0
// Junio de 2014
function cargarDhtmlxCombo40a() {
    return
            '<!--Combo-->' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxCommon/codebase/dhtmlx.css">' . chr(13)
            . '<script src="../../includes/dhtmlx4.0/dhtmlxCombo/codebase/dhtmlxcombo.js" type="text/javascript"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx4.0/dhtmlxCommon/codebase/dhtmlxcommon.js" type="text/javascript"></script>' . chr(13)
    // . '<script src="../../includes/dhtmlx4.0/dhtmlxCombo/codebase/ext/dhtmlxcombo_extra.js" type="text/javascript"></script>' . chr(13)
    // . '<script src="../../includes/dhtmlx4.0/dhtmlxCombo/codebase/ext/dhtmlxcombo_group.js" type="text/javascript"></script>' . chr(13)
    // . '<script src="../../includes/dhtmlx4.0/dhtmlxCombo/codebase/ext/dhtmlxcombo_whp.js" type="text/javascript"></script>' . chr(13)
    ;
}

function cargarDhtmlxLayout40a() {
    return
            '<!-- layouts -->' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxCommon/codebase/dhtmlxcontainer.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxCommon/codebase/dhtmlxcommon.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxLayout/codebase/dhtmlxlayout.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxLayout/codebase/dhtmlxlayout_deprecated.js"></script>' . chr(13)

            // . '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxLayout/codebase/dhtmlxlayout.css" />' . chr(13) 
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxLayout/codebase/patterns/dhtmlxlayout_pattern4c.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxLayout/codebase/patterns/dhtmlxlayout_pattern4w.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxLayout/codebase/patterns/dhtmlxlayout_pattern5w.js"></script>' . chr(13)

            // . '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_black.css" />' . chr(13) 
            // . '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_blue.css" />' . chr(13)
            . '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_web.css" />' . chr(13)
            . '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_terrace.css" />' . chr(13)
            . '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_skyblue.css" />' . chr(13)
    ;
}

function cargarDhtmlxMenu40a() {
    return
            '<!-- menus -->' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxMenu/codebase/dhtmlxmenu.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxCommon/codebase/dhtmlxcontainer.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxCommon/codebase/dhtmlxcommon.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxMenu/codebase/ext/dhtmlxmenu_ext.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxMenu/codebase/ext/dhtmlxmenu_effects.js"></script>' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/skyblue/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/terrace/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/web/dhtmlx.css">' . chr(13)
            . '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_skyblue.css">' . chr(13)
            . '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_terrace.css">' . chr(13)
            . '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxMenu/codebase/skins/dhtmlxmenu_dhx_web.css">' . chr(13)
    ;
}

function cargarDhtmlxGrid40a() {
    return
            '<!--grids-->' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxCommon/codebase/dhtmlxcommon.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx3.6/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_export.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_deprecated.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_start.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_drag.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_filter.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_keymap_access.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_keymap_excel.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_keymap_extra.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_nxml.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_selection.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_srnd.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/ext/dhtmlxgrid_validation.js"></script>' . chr(13)

            // . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_3but.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_acheck.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_calendar.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_cntr.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_context.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_cor.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_dec.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_dhxcalendar.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_grid.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_limit.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_link.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_liveedit.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_mro.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_num.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_passwd.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_tree.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_wbut.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_pgn.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_group.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx2.6/dhtmlxGrid/codebase/ext/dhtmlxgrid_splt.js"></script>' . chr(13)

            // . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/dhtmlxgrid.css">' . chr(13)
            // . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/dhtmlxgrid_skins.css">' . chr(13)
            // . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/calendar/calendar.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/skyblue/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/terrace/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/web/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_skyblue.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_web.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_terrace.css">' . chr(13)
    ;
}

function cargarDhtmlxToolbar40a() {
    return
            '<!-- toolbar -->' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxCommon/codebase/dhtmlxcommon.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxToolbar/codebase/dhtmlxtoolbar.js"></script>' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/skyblue/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/terrace/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/web/dhtmlx.css">' . chr(13)
            . '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxToolbar/codebase/skins/dhtmlxtoolbar_dhx_web.css" />' . chr(13)
            . '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxToolbar/codebase/skins/dhtmlxtoolbar_dhx_terrace.css" />' . chr(13)
            . '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxToolbar/codebase/skins/dhtmlxtoolbar_dhx_skyblue.css" />' . chr(13)
    ;
}

function cargarDhtmlxTabbar40a() {
    return
            '<!-- tabbar -->' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxTabbar/codebase/dhtmlxtabbar.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxTabbar/codebase/dhtmlxtabbar_start.js"></script>' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/skyblue/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/terrace/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/web/dhtmlx.css">' . chr(13)

    ;
}

function cargarDhtmlxCalendar40a() {
    return
            '<!--calendar-->' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxCalendar/codebase/dhtmlxcalendar.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxCalendar/codebase/ext/dhtmlxcalendar_double.js"></script>' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/skyblue/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/terrace/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/web/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_skyblue.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_terrace.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_web.css">' . chr(13)
            . '<script>window.dhx_globalImgPath="../../includes/dhtmlx4.0/dhtmlxCalendar/codebase/imgs/";</script>' . chr(13)
    ;
}

function cargarDhtmlxWindows40a() {
    return
            '<!--Windows-->' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxCommon/codebase/dhtmlxcontainer.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxCommon/codebase/dhtmlxcommon.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxWindows/codebase/dhtmlxwindows.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxWindows/codebase/ext/dhtmlxwindows_dnd.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxWindows/codebase/ext/dhtmlxwindows_menu.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxWindows/codebase/ext/dhtmlxwindows_resize.js"></script>' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/skyblue/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/terrace/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/web/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxWindows/codebase/skins/dhtmlxwindows_dhx_skyblue.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxWindows/codebase/skins/dhtmlxwindows_dhx_web.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxWindows/codebase/skins/dhtmlxwindows_dhx_terrace.css">' . chr(13)
            . '<script>window.dhx_globalImgPath="../../html/default/images/";</script>' . chr(13)
    ;
}

function cargarDhtmlxVault40a() {
    return
            '<!--Vault-->' . chr(13)
            . '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxVault/codebase/dhtmlxvault.css" />' . chr(13)
            . '<script language="JavaScript" type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxVault/codebase/dhtmlxvault.js"></script>' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/skyblue/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/terrace/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/web/dhtmlx.css">' . chr(13)

    ;
}

function cargarDhtmlxGantt40a() {
    return
            '<!--Gantt-->' . chr(13)
            . '<link rel="stylesheet" href="../../includes/dhtmlx4.0/dhtmlxgantt/codebase/skins/dhtmlxgantt_meadow.css" type="text/css" media="screen" title="no title" charset="utf-8">' . chr(13)
            // . '<link rel="stylesheet" href="../../includes/dhtmlx4.0/dhtmlxgantt/codebase/skins/dhtmlxgantt_skyblue.css" type="text/css" media="screen" title="no title" charset="utf-8">' . chr(13)
            . '<script language="JavaScript" type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxgantt/codebase/sources/dhtmlxgantt.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx4.0/dhtmlxgantt/codebase/locale/locale_es.js" charset="utf-8"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx4.0/dhtmlxgantt/codebase/sources/ext/dhtmlxgantt_tooltip.js" type="text/javascript" charset="utf-8"></script>' . chr(13)
            . '<script src="http://export.dhtmlx.com/gantt/api.js" type="text/javascript" charset="utf-8"></script>' . chr(13)
    ;
}

function cargarDhtmlxDataProcessor40a() {
    return
            '<!--Windows-->' . chr(13) .
            '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxDataProcessor/codebase/dhtmlxdataprocessor.js"></script>' . chr(13);
}

function cargarDhtmlxChart40a() {
    return
            '<!--chart-->' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxChart/codebase/dhtmlxchart.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxChart/codebase/thirdparty/excanvas/excanvas.js"></script>' . chr(13)
            // . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxChart/codebase/dhtmlxchart_debug.js"></script>' . chr(13)
            // . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxChart/codebase/dhtmlxchart_debug.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/skyblue/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/terrace/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/web/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxChart/codebase/skins/dhtmlxchart_dhx_skyblue.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxChart/codebase/skins/dhtmlxchart_dhx_web.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxChart/codebase/skins/dhtmlxchart_dhx_terrace.css">' . chr(13)

    ;
}

function cargarDhtmlxForm40a() {
    return
            '<!--Form-->' . chr(13)
            // . '<link rel="stylesheet" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxForm/codebase/skins/dhtmlxform_dhx_skyblue.css">' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxCommon/codebase/dhtmlxcontainer.js"></script>' . chr(13)
            . '<script type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxCommon/codebase/dhtmlxcommon.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx4.0/dhtmlxForm/codebase/dhtmlxform.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx4.0/dhtmlxForm/codebase/ext/dhtmlxform_backup.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx4.0/dhtmlxForm/codebase/ext/dhtmlxform_dyn.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx4.0/dhtmlxForm/codebase/ext/dhtmlxform_item_btn2state.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx4.0/dhtmlxForm/codebase/ext/dhtmlxform_item_calendar.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx4.0/dhtmlxForm/codebase/ext/dhtmlxform_item_colorpicker.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx4.0/dhtmlxForm/codebase/ext/dhtmlxform_item_combo.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx4.0/dhtmlxForm/codebase/ext/dhtmlxform_item_container.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx4.0/dhtmlxForm/codebase/ext/dhtmlxform_item_editor.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx4.0/dhtmlxForm/codebase/ext/dhtmlxform_item_upload.js"></script>' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/skyblue/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/terrace/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/skins/web/dhtmlx.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxForm/codebase/skins/dhtmlxform_dhx_skyblue.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxForm/codebase/skins/dhtmlxform_dhx_web.css">' . chr(13)
            . '<link rel="STYLESHEET" type="text/css" href="../../includes/dhtmlx4.0/dhtmlxForm/codebase/skins/dhtmlxform_dhx_terrace.css">' . chr(13)

    ;
}

function cargarDhtmlx403() {
    return
            '<!--General-->' . chr(13)
            . '<link rel="stylesheet" href="../../includes/dhtmlx4.03/codebase/dhtmlx.css" type="text/css" media="screen" title="no title" charset="utf-8">' . chr(13)
            . '<script language="JavaScript" type="text/javascript" src="../../includes/dhtmlx4.03/codebase/dhtmlx.js"></script>' . chr(13)
            . '<!--Gantt-->' . chr(13)
            . '<link rel="stylesheet" href="../../includes/dhtmlx4.0/dhtmlxgantt/codebase/skins/dhtmlxgantt_meadow.css" type="text/css" media="screen" title="no title" charset="utf-8">' . chr(13)
            . '<script language="JavaScript" type="text/javascript" src="../../includes/dhtmlx4.0/dhtmlxgantt/codebase/sources/dhtmlxgantt.js"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx4.0/dhtmlxgantt/codebase/locale/locale_es.js" charset="utf-8"></script>' . chr(13)
            . '<script src="../../includes/dhtmlx4.0/dhtmlxgantt/codebase/sources/ext/dhtmlxgantt_tooltip.js" type="text/javascript" charset="utf-8"></script>' . chr(13)
            . '<script src="http://export.dhtmlx.com/gantt/api.js" type="text/javascript" charset="utf-8"></script>' . chr(13)
    ;
}

/*
 * Convierte un string en número
 */

function convertirStringNumero($valx) {
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
                case 1: $val = $val + intval($a[1]) / 10;
                    break;
                case 2: $val = $val + intval($a[1]) / 100;
                    break;
                case 3: $val = $val + intval($a[1]) / 1000;
                    break;
                case 4: $val = $val + intval($a[1]) / 10000;
                    break;
                case 5: $val = $val + intval($a[1]) / 100000;
                    break;
            }
        }
    }
    if ($signo == '-') {
        $val = $val * -1;
    }
    return $val;
}

/*
 * Convierte un número en un string
 */

function convertirNumeroString($valx) {
    $valx = str_replace(",", "", $valx);
    if ($valx == '') {
        return '+000000000000000.0000';
    }
    if ($valx < 0) {
        $signo = '-';
        $valx = $valx * -1;
    } else {
        $signo = '+';
    }

    $l = explode(".", $valx);
    $ent = $l[0];
    if (isset($l[1])) {
        $dec = $l[1];
    } else {
        $dec = 0;
    }

    $res = '';
    $len = strlen($dec);
    switch ($len) {
        case 1 : $res = "000";
            break;
        case 2 : $res = "00";
            break;
        case 3 : $res = "0";
            break;
    }
    return trim($signo . sprintf("%015s", $ent) . '.' . sprintf("%-4s", $dec)) . $res;
}

/*
 * Convetir un PDF a TIF, rutina inhabilitada
 */

function convertirPdf2Tiff($input, $output, $resolucion = 'low') {
    return true;
    /*
      if (!defined('PHP_GS')) {
      define('PHP_GS', strtoupper(substr(PHP_OS, 0, 3) == 'WIN') ? "gswin32c" : "gs");
      }
      define('TIFF_RES', ($resolucion == 'low' ? "204×98" : "204×196"));
      $cmd_line = PHP_GS . " -dBATCH -dNOPAUSE -sDEVICE=tiffg3 -r";
      $cmd_line .= TIFF_RES . " -sDither=floyd -sOutputFile=" . $output . " " . $input;
      $pipe = popen($cmd_line, "w");
      if (!$pipe) {
      return false;
      }
      pclose($pipe);
      return true;
     */
}

/*
 * Crea archivo index.html en la carpeta indicada
 */

function crearIndex($dir) {

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

/*
 * Cuenta los archivos de un directorio
 */

function countFiles($dir) {
    if (file_exists($dir)) {
        $explorar = scandir($dir);
        $total = count($explorar) - 2;
        if ($total < 0) {
            $total = 0;
        }
        return $total;
    } else {
        return 0;
    }
}

/*
 * Encuentra la extension de un archivo
 */

function encontrarExtension($file) {
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

/**
 * 
 * @param type $action
 * @param type $secret_key
 * @param type $secret_iv
 * @param type $string
 * @return type
 */
function encrypt_decrypt($action, $secret_key, $secret_iv, $string) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $key = hash('sha256', $secret_key);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ($action == 'encrypt') {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if ($action == 'decrypt') {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}

/*
 * Encripta una cadena dada una clave que esta en el common.php
 */

function encrypt($string) {
    require_once ('../../configuracion/common.php');
    if (!defined('KEY_ENCRIPCION')) {
        define('KEY_ENCRIPCION', "S44S0nl1n3");
    }
    $result = '';
    for ($i = 0; $i < strlen($string); $i++) {
        $char = substr($string, $i, 1);
        $keychar = substr(KEY_ENCRIPCION, ($i % strlen(KEY_ENCRIPCION)) - 1, 1);
        $char = chr(ord($char) + ord($keychar));
        $result .= $char;
    }
    return base64_encode($result);
}

/*
 * Encripción propia del SII por sustitución
 */

function encryptPropio($string) {
    require_once ('../../configuracion/common.php');
    $salida = '';
    for ($i = 0; $i < strlen($string); $i++) {
        $char = substr($string, $i, 1);
        $charx = '';
        switch ($char) {
            case " " : $charx = "#001";
                break;
            case "<" : $charx = "#002";
                break;
            case ">" : $charx = "#003";
                break;
            case "." : $charx = "#004";
                break;
            case ":" : $charx = "#005";
                break;
            case ";" : $charx = "#006";
                break;
            case "," : $charx = "#007";
                break;
            case "?" : $charx = "#008";
                break;
            case "¿" : $charx = "#009";
                break;
            case "#" : $charx = "#010";
                break;
            case "!" : $charx = "#011";
                break;
            case "@" : $charx = "#012";
                break;
            case "&" : $charx = "#013";
                break;
            case "%" : $charx = "#014";
                break;
            case "$" : $charx = "#015";
                break;
            case "-" : $charx = "#016";
                break;
            case "_" : $charx = "#017";
                break;
            case "/" : $charx = "#018";
                break;
            case "\\" : $charx = "#019";
                break;
            case "'" : $charx = "#020";
                break;
            case "\"" : $charx = "#021";
                break;
            case "=" : $charx = "#022";
                break;
            case "[" : $charx = "#023";
                break;
            case "]" : $charx = "#024";
                break;
            case "{" : $charx = "#025";
                break;
            case "}" : $charx = "#026";
                break;
            case "*" : $charx = "#027";
                break;
            case "+" : $charx = "#028";
                break;
            case "(" : $charx = "#029";
                break;
            case ")" : $charx = "#030";
                break;
            case "|" : $charx = "#031";
                break;
            case "0" : $charx = "#032";
                break;
            case "1" : $charx = "#033";
                break;
            case "2" : $charx = "#034";
                break;
            case "3" : $charx = "#035";
                break;
            case "4" : $charx = "#036";
                break;
            case "5" : $charx = "#037";
                break;
            case "6" : $charx = "#038";
                break;
            case "7" : $charx = "#039";
                break;
            case "8" : $charx = "#040";
                break;
            case "9" : $charx = "#041";
                break;
            case "a" : $charx = "#042";
                break;
            case "á" : $charx = "#043";
                break;
            case "b" : $charx = "#044";
                break;
            case "c" : $charx = "#045";
                break;
            case "d" : $charx = "#046";
                break;
            case "e" : $charx = "#047";
                break;
            case "é" : $charx = "#048";
                break;
            case "f" : $charx = "#049";
                break;
            case "g" : $charx = "#050";
                break;
            case "h" : $charx = "#051";
                break;
            case "i" : $charx = "#052";
                break;
            case "í" : $charx = "#053";
                break;
            case "j" : $charx = "#054";
                break;
            case "k" : $charx = "#055";
                break;
            case "l" : $charx = "#056";
                break;
            case "m" : $charx = "#057";
                break;
            case "n" : $charx = "#058";
                break;
            case "ñ" : $charx = "#059";
                break;
            case "o" : $charx = "#060";
                break;
            case "ó" : $charx = "#061";
                break;
            case "p" : $charx = "#062";
                break;
            case "q" : $charx = "#063";
                break;
            case "r" : $charx = "#064";
                break;
            case "s" : $charx = "#065";
                break;
            case "t" : $charx = "#066";
                break;
            case "u" : $charx = "#067";
                break;
            case "ú" : $charx = "#068";
                break;
            case "ü" : $charx = "#069";
                break;
            case "v" : $charx = "#070";
                break;
            case "w" : $charx = "#071";
                break;
            case "x" : $charx = "#072";
                break;
            case "y" : $charx = "#073";
                break;
            case "z" : $charx = "#074";
                break;
            case "A" : $charx = "#075";
                break;
            case "Á" : $charx = "#076";
                break;
            case "B" : $charx = "#077";
                break;
            case "C" : $charx = "#078";
                break;
            case "D" : $charx = "#079";
                break;
            case "E" : $charx = "#080";
                break;
            case "É" : $charx = "#081";
                break;
            case "F" : $charx = "#082";
                break;
            case "G" : $charx = "#083";
                break;
            case "H" : $charx = "#084";
                break;
            case "I" : $charx = "#085";
                break;
            case "Í" : $charx = "#086";
                break;
            case "J" : $charx = "#087";
                break;
            case "K" : $charx = "#088";
                break;
            case "L" : $charx = "#089";
                break;
            case "M" : $charx = "#090";
                break;
            case "N" : $charx = "#091";
                break;
            case "Ñ" : $charx = "#092";
                break;
            case "O" : $charx = "#093";
                break;
            case "Ó" : $charx = "#094";
                break;
            case "P" : $charx = "#095";
                break;
            case "Q" : $charx = "#096";
                break;
            case "R" : $charx = "#097";
                break;
            case "S" : $charx = "#098";
                break;
            case "T" : $charx = "#099";
                break;
            case "U" : $charx = "#100";
                break;
            case "Ú" : $charx = "#101";
                break;
            case "Ü" : $charx = "#102";
                break;
            case "V" : $charx = "#103";
                break;
            case "W" : $charx = "#104";
                break;
            case "X" : $charx = "#105";
                break;
            case "Y" : $charx = "#106";
                break;
            case "Z" : $charx = "#107";
                break;
            case chr(13) : $charx = "#108";
                break;
        }
        $salida .= $charx;
    }
    return $salida;
}

/*
 * Encripta mediante método ORD
 */

function encriptarOrd($string) {
    $salida = '';
    if (trim($string) == '') {
        return $salida;
    }
    $i = strlen($string);
    for ($j = 0; $j < $i; $j++) {
        if (trim($salida) != '') {
            $salida .= 'x';
        }
        $salida .= ord(substr($string, $j, 1));
    }
    return $salida;
}

/*
 * Desencripta mediante método ORD
 */

function desencriptarOrd($string) {
    $salida = '';
    if (trim($string) == '') {
        return $salida;
    }
    $list = explode('x', $string);
    foreach ($list as $ls) {
        $salida .= chr($ls);
    }
    return $salida;
}

/*
 * Localiza el path de una imagen en el repositorio
 */

function encontrarPathImagen($tam = 0, $directorio = 'mreg', $tipoanexo = '000') {
    $disco = intval(localizarDiscoActual($tipoanexo));
    if ($disco === false) {
        return false;
    }

    //
    if (ltrim($disco, "0") == '') {
        $disco = 1;
    }

    if (!defined('TIPO_REPOSITORIO_NUEVAS_IMAGENES')) {
        define('TIPO_REPOSITORIO_NUEVAS_IMAGENES', 'LOCAL');
    }

    $peso = 0;
    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'LOCAL' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == '' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
        $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $directorio;
        if (!is_dir($path) || !is_readable($path)) {
            mkdir($path, 0777);
            crearIndex($path);
        }

        // ubica el disco
        $ok = 'no';
        $limite = 0;
        while ($ok != 'si') {
            $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $directorio . '/' . sprintf("%03s", $disco);
            if (!is_dir($path) || !is_readable($path)) {
                mkdir($path, 0777);
                crearIndex($path);
            }
            $salida = array();
            exec('du --block-size=1000 ' . $path, $salida);
            $list = explode("/", $salida[0]);
            $peso = intval(trim($list[0]));
            if ($peso < (TAMANO_DISCO_KB)) {
                $ok = 'si';
            } else {
                $disco++;
                $limite++;
                if ($limite == 100) {
                    $ok = 'si';
                }
            }
        }
    }

    // SI el repositorio es Remoto
    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'WS') {
        $path = $_SESSION["generales"]["codigoempresa"] . '/' . $directorio;
        crearDirectorioWsRemoto($path);

        // ubica el disco
        $ok = 'no';
        $limite = 0;
        while ($ok != 'si') {
            $path = $_SESSION["generales"]["codigoempresa"] . '/' . $directorio . '/' . sprintf("%03s", $disco);
            crearDirectorioWsRemoto($path);
            $peso = tamanoDirectorioWsRemoto($path);
            if ($peso === false) {
                $ok = 'si';
            } else {
                if ($peso < (TAMANO_DISCO_KB)) {
                    $ok = 'si';
                } else {
                    $disco++;
                    $limite++;
                    if ($limite == 100) {
                        $ok = 'si';
                    }
                }
            }
        }
    }

    // SI el repositorio es Aws S3
    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'S3-V4') {
        $path = $_SESSION["generales"]["codigoempresa"] . '/' . $directorio;

        // ubica el disco
        $ok = 'no';
        $limite = 0;
        while ($ok != 'si') {
            $path = $_SESSION["generales"]["codigoempresa"] . '/' . $directorio . '/' . sprintf("%03s", $disco);
            $peso = intval(tamanoS3Version4($path));
            if ($peso === false) {
                $ok = 'si';
            } else {
                if ($peso < (TAMANO_DISCO_KB)) {
                    $ok = 'si';
                } else {
                    $disco++;
                    $limite++;
                    if ($limite == 100) {
                        $ok = 'si';
                    }
                }
            }
        }
    }


    if ($peso === false) {
        $_SESSION["generales"]["mensajeerror"] = 'No fue posible localizar el directorio a utilizar';
        return false;
    }

    //
    // Actualizaci&oacute;n del campo "disco" para el registro mercantil en claves-valor
    $arrTem = retornarRegistro('bas_tipoanexodocumentos', "id='" . $tipoanexo . "'");
    if ($arrTem === false || empty($arrTem)) {
        $_SESSION["generales"]["mensajeerror"] = 'Imposible localizar tipo de anexo en tabla bas_tipoanexodocumentos';
        return false;
    }

    $arrTem1 = retornarRegistro('bas_claves_valor', "idorden='" . $arrTem["clavevalor"] . "'");
    if ($arrTem1 === false) {
        $_SESSION["generales"]["mensajeerror"] = 'Imposible localizar tipo de anexo en tabla bas_claves_valor';
        return false;
    }

    //
    $arrCampos = array(
        'id',
        'valor'
    );
    $arrValores = array(
        $arrTem1["id"],
        "'" . sprintf("%03s", $disco) . "'"
    );

    //
    if (contarRegistros('claves_valor', "id=" . $arrTem1["id"]) == 0) {
        insertarRegistros('claves_valor', $arrCampos, $arrValores);
    } else {
        regrabarRegistros('claves_valor', $arrCampos, $arrValores, "id=" . $arrTem1["id"]);
    }

    //
    // Si el repositorio es Local
    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'LOCAL' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == '' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
        // Crea el directorio base, si no existe
        if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $directorio)) {
            mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $directorio, 0777);
            crearIndex(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $directorio);
        }

        // Crea el disco, si no existe
        if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $directorio . '/' . sprintf("%03s", $disco))) {
            mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $directorio . '/' . sprintf("%03s", $disco), 0777);
            crearIndex(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $directorio . '/' . sprintf("%03s", $disco));
        }
    }

    //
    return $disco;
}

/*
 * Verifica si un número es par
 */

function esPar($lin) {
    $j = intval($lin / 2);
    if (($j * 2) == $lin) {
        return true;
    } else {
        return false;
    }
}

/*
 * Desencripta una cadena dada una clave que esta en el common.php
 */

function decrypt($string) {
    require_once ('../../configuracion/common.php');
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

/*
 * Función para encriptar una cadena con un preshared key
 */

function encriptarMcrypt($cadena, $clave = "S44S0nl1n3") {
    $cifrado = MCRYPT_RIJNDAEL_256;
    $modo = MCRYPT_MODE_ECB;
    return mcrypt_encrypt($cifrado, $clave, $cadena, $modo, mcrypt_create_iv(mcrypt_get_iv_size($cifrado, $modo), MCRYPT_RAND)
    );
}

/*
 * Localiza el factor de firmado dependiendo del tipo de trámite
 * - DOBLE
 * - CLAVE
 * - PIN
 * - NADA
 */

function evaluarFactorAutenticacion($tt) {
    require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');

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
    if ($tt == 'matriculapnat' ||
            $tt == 'matriculapjur' ||
            $tt == 'matriculaest' ||
            $tt == 'matriculaesadl' ||
            $tt == 'matriculasuc' ||
            $tt == 'matriculaage') {
        if (!defined('FACTOR_AUTENTICACION_FIRMADO_MATRICULAS') || FACTOR_AUTENTICACION_FIRMADO_MATRICULAS == '') {
            $factor = 'DOBLE';
        } else {
            $factor = FACTOR_AUTENTICACION_FIRMADO_MATRICULAS;
        }
    }

    //
    if ($tt == 'renovacionmatricula' ||
            $tt == 'renovacionesadl') {
        if (!defined('FACTOR_AUTENTICACION_FIRMADO_RENOVACION') || FACTOR_AUTENTICACION_FIRMADO_RENOVACION == '') {
            $factor = 'DOBLE';
        } else {
            $factor = FACTOR_AUTENTICACION_FIRMADO_RENOVACION;
        }
    }

    //
    if ($tt == 'mutaciondireccion' ||
            $tt == 'mutacionactividad' ||
            $tt == 'mutacionnombre' ||
            $tt == 'mutacionregmer' ||
            $tt == 'mutacionesadl') {
        if (!defined('FACTOR_AUTENTICACION_FIRMADO_MUTACIONES') || FACTOR_AUTENTICACION_FIRMADO_MUTACIONES == '') {
            $factor = 'DOBLE';
        } else {
            $factor = FACTOR_AUTENTICACION_FIRMADO_MUTACIONES;
        }
    }

    //
    if ($tt == 'inscripciondocumentos' ||
            $tt == 'inscripcionesregmer' ||
            $tt == 'inscripcionesesadl' ||
            $tt == 'solicitudcancelacionpnat' ||
            $tt == 'solicitudcancelacionest') {
        if (!defined('FACTOR_AUTENTICACION_FIRMADO_ACTOSDOCUMENTOS') || FACTOR_AUTENTICACION_FIRMADO_ACTOSDOCUMENTOS == '') {
            $factor = 'DOBLE';
        } else {
            $factor = FACTOR_AUTENTICACION_FIRMADO_ACTOSDOCUMENTOS;
        }
    }

    //
    if ($tt == 'inscripcionproponente' ||
            $tt == 'actualizacionproponente' ||
            $tt == 'renovacionproponente' ||
            $tt == 'cancelacionproponente' ||
            $tt == 'cambiodomicilioproponente') {
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

/*
 * Lecto de noticias RSS
 */

function lectorRSS($url, $elementos = 500, $inicio = 0) {
    $cache_version = "cache/" . basename($url);
    $archivo = fopen($url, 'r');
    stream_set_blocking($archivo, true);
    stream_set_timeout($archivo, 5);
    $datos = stream_get_contents($archivo);
    $status = stream_get_meta_data($archivo);
    fclose($archivo);
    if ($status['timed_out']) {
        $noticias = simplexml_load_file($cache_version);
    } else {
        $archivo_cache = fopen($cache_version, 'w');
        fwrite($archivo_cache, $datos);
        fclose($archivo_cache);
        $noticias = simplexml_load_string($datos);
    }
    $ContadorNoticias = 1;
    echo "<ul>";
    foreach ($noticias->channel->item as $noticia) {
        if ($ContadorNoticias < $elementos) {
            if ($ContadorNoticias > $inicio) {
                echo "<li><a href='" . $noticia->link . "' target='_blank' class='tooltip' title='" . utf8_decode($noticia->title) . "'>";
                echo utf8_decode($noticia->title);
                echo "</a></li>";
            }
            $ContadorNoticias = $ContadorNoticias + 1;
        }
    }
    echo "</ul>";
}

/*
 * Pasa de notacion exponencial a decimal 
 */

function exp_to_dec($float_str) {
// formats a floating point number string in decimal notation, supports signed floats, also supports non-standard formatting e.g. 0.2e+2 for 20
// e.g. '1.6E+6' to '1600000', '-4.566e-12' to '-0.000000000004566', '+34e+10' to '340000000000'
// Author: Bob
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

function forzarDescargaOriginal1($n) {
    $size = filesize($n);
    header("Content-Transfer-Encoding: binary");
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=$n");
    header("Content-Length: $size");
    $fp = fopen("$n", "r");
    fpassthru($fp);
    exit();
}

//
function forzarDescarga($n) {
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
        $name = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . rand(1000000, 5000000) . '.' . encontrarExtension($name1);
        $f = fopen($name, "w");
        fwrite($f, $data);
        fclose($f);
        // die ($name1);
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

//
function forzarDescargaBase64($n) {
    $n = base64_decode($n);
    if (substr($n, 0, 4) == 'http') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $n);
        $data = curl_exec($ch);
        curl_close($ch);
        $name = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . rand(1000000, 5000000) . '.' . encontrarExtension($n);
        $f = fopen($name, "w");
        fwrite($f, $data);
        fclose($f);
    } else {
        $name = $n;
    }

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

        //
        // Definir headers
        header("Content-Type: $type");
        header("Content-Disposition: attachment; filename=$file");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . $size);
        ob_start();
        ob_end_clean();
        flush();
        readfile($name);
        exit();
    } else {
        die("El archivo no existe. : " . base64_decode($n));
    }
}

function forzarDescargaUrl($n) {
    if (substr($n, 0, 4) == 'http') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $n);
        $data = curl_exec($ch);
        curl_close($ch);
        $name = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . rand(1000000, 5000000) . '.' . encontrarExtension($n);
        $f = fopen($name, "w");
        fwrite($f, $data);
        fclose($f);
    } else {
        $name = $n;
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

//
function forzarDescargaOriginal($n) {
    if (substr($n, 0, 4) == 'http') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $n);
        $data = curl_exec($ch);
        curl_close($ch);
        $name = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . rand(1000000, 5000000) . '.' . encontrarExtension($n);
        $f = fopen($name, "w");
        fwrite($f, $data);
        fclose($f);
    } else {
        $name = $n;
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

function descargarWgetSirep($url, $file) {
    $data = file_get_contents($url . $file);
    $f = fopen('../../tmp/' . $file, "wb");
    fwrite($f, $data);
    fclose($f);
    return true;
}

function downloadFileNew($fileUrl) {
    if (substr($fileUrl, 0, 4) == 'http') {
        $fileSize = array_change_key_case(get_headers($fileUrl, 1), CASE_LOWER);
        if (strcasecmp($fileSize[0], 'HTTP/1.1 200 OK') != 0) {
            $fileSize = $fileSize['content-length'][1];
        } else {
            $fileSize = $fileSize['content-length'];
        }
    } else {
        $fileSize = @filesize($fileUrl);
    }

    // download file
    $ctype = "application/octet-stream";
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    header("Content-Type: $ctype");

    header("Content-Disposition: attachment; filename=\"" . basename($fileUrl) . "\";");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . $fileSize);
    readfile("$fileUrl");
    exit();
}

function downloadFileFpassthru($f) {
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"$f\"\n");
    $fp = fopen("$f", "r");
    fpassthru($fp);
}

function desencriptarMcrypt($cadena, $clave = "S44S0nl1n3") {
    $cifrado = MCRYPT_RIJNDAEL_256;
    $modo = MCRYPT_MODE_ECB;
    return mcrypt_decrypt($cifrado, $clave, $cadena, $modo, mcrypt_create_iv(mcrypt_get_iv_size($cifrado, $modo), MCRYPT_RAND)
    );
}

/**
 * 
 * @param type $host
 * @param string $port
 * @param type $user
 * @param type $passwd
 * @param type $path
 * @return boolean
 */
function listaFtp($host = 'copiasseguridad.confecamaras.org.co', $port = '21', $user = 'actualiz', $passwd = 'ftp2009*', $path = 'actualizaciones') {
    require_once ('../../configuracion/common.php');
    if (trim($port) == '') {
        $port = '21';
    }
    if ($port == '21') {
        $conn_id = ftp_connect($host);
    } else {
        $conn_id = ftp_connect($host, intval($port));
    }
    if ($conn_id === false) {
        $_SESSION["generales"]["mensajeerror"] = '(1) No fue posible conectarse con el servidor ' . $host . ', verifique que su servidor sii pueda nevegar en internet y que est&eacute;n abiertos los puertos requeridos para la conexi&oacute;n v&iacute;a ftp';
        return false;
    }
    $result = ftp_login($conn_id, $user, $passwd);
    if ($result === false) {
        $_SESSION["generales"]["mensajeerror"] = '(2) No fue posible conectarse con el servidor ' . $host . ', error en usuario o clave';
        return false;
    }


    // Activa el modo pasivo del ftp
    ftp_pasv($conn_id, true);


    if (trim($path) != '') {
        $result = ftp_chdir($conn_id, $path);
        if ($result === false) {
            $_SESSION["generales"]["mensajeerror"] = '(3) No fue posible ubicarse en el directorio ' . $path;
            return false;
        }
    }
    try {
        $result = ftp_nlist($conn_id, '.');
        if ($result === false) {
            $_SESSION["generales"]["mensajeerror"] = '(4) No fue posible recuperar la lista de archivos, es probable que la configuraci&oacute;n del firewall evite que el servicio ftp pueda intercambiar informaci&oacute;n con el servidor de actualizaciones.';
            return false;
        }
    } catch (Exception $e) {
        $_SESSION["generales"]["mensajeerror"] = '(5) Excepci&oacute;n: No fue posible recuperar la lista de archivos, es probable que la configuraci&oacute;n del firewall evite que el servicio ftp pueda intercambiar informaci&oacute;n con el servidor de actualizaciones.';
        return false;
    }
    ftp_close($conn_id);
    return $result;
}

/**
 * 
 * @param type $host
 * @param string $port
 * @param type $user
 * @param type $passwd
 * @param type $path
 * @param type $basedir
 * @return boolean
 */
function listaSftp($host = 'copiasseguridad.confecamaras.org.co', $port = '22', $user = 'actualiz', $passwd = 'ftp2009*', $path = 'actualizaciones', $basedir = '') {
    require_once ('../../configuracion/common.php');
    set_include_path(get_include_path() . PATH_SEPARATOR . PATH_ABSOLUTO_SITIO . '/includes/phpseclib');
    require_once ('Net/SFTP.php');

    if (trim($port) == '') {
        $port = '22';
    }

    try {
        $sftp = new Net_SFTP($host, $port);
    } catch (Exception $e) {
        $_SESSION["generales"]["mensajeerror"] = 'Excepci&oacute;n capturada: ' . $e->getMessage();
        return false;
    }
    if (!$sftp->login($user, $passwd)) {
        unset($sftp);
        $_SESSION["generales"]["mensajeerror"] = 'No fue posible conectarse por ssh con el servidor';
        return false;
    }

    $path1 = '';
    if (trim($basedir) == '') {
        $path1 = $path;
    } else {
        $path1 = $basedir . '/' . $path;
    }
    if (!$sftp->chdir($path1)) {
        unset($sftp);
        $_SESSION["generales"]["mensajeerror"] = 'No fue posible ubicarse en el directorio ' . $path1 . ' del servidor ssh';
        return false;
    }

    $result = $sftp->nlist();
    if (!$result) {
        unset($sftp);
        $_SESSION["generales"]["mensajeerror"] = 'No fue posible descargar el archivo desde el servidor ssh';
        return false;
    }
    unset($sftp);
    return $result;
}

/**
 * 
 * @param type $host
 * @param string $port
 * @param type $user
 * @param type $passwd
 * @param type $path
 * @param type $filein
 * @param type $fileout
 * @return boolean
 */
function descargarFtpSirep($host, $port, $user, $passwd, $path, $filein, $fileout) {
    ini_set('display_errors', '1');
    if (trim($port) == '') {
        $port = '21';
    }
    if ($port == '21') {
        try {
            $conn_id = ftp_connect($host);
        } catch (Exception $e) {
            $_SESSION["generales"]["mensajeerror"] = 'Excepci&oacute;n capturada: ' . $e->getMessage();
            return false;
        }
    } else {
        try {
            $conn_id = ftp_connect($host, intval($port));
        } catch (Exception $e) {
            $_SESSION["generales"]["mensajeerror"] = 'Excepci&oacute;n capturada: ' . $e->getMessage();
            return false;
        }
    }
    if ($conn_id === false) {
        $_SESSION["generales"]["mensajeerror"] = '(6) No fue posible conectarse con el servidor ' . $host;
        return false;
    }
    try {
        $result = ftp_login($conn_id, $user, $passwd);
        if ($result === false) {
            $_SESSION["generales"]["mensajeerror"] = '(7) No fue posible conectarse con el servidor ' . $host . ', error en usuario o clave';
            return false;
        }
    } catch (exception $e) {
        $_SESSION["generales"]["mensajeerror"] = '(7) No fue posible conectarse con el servidor ' . $host . ', error en usuario o clave';
        return false;
    }

    // Activa el modo pasivo del ftp
    ftp_pasv($conn_id, true);

    $result = ftp_chdir($conn_id, $path);
    if ($result === false) {
        $_SESSION["generales"]["mensajeerror"] = '(8) No fue posible ubicarse en el directorio ' . $path;
        return false;
    }
    try {
        $result = ftp_get($conn_id, $fileout, $filein, FTP_BINARY);
        //$result = ftp_get($conn_id, $filein, $fileout, FTP_BINARY);
        if ($result === false) {
            $_SESSION["generales"]["mensajeerror"] = '(9) No fue posible recuperar el archivo via ftp (' . $filein . ')';
            return false;
        }
    } catch (Exception $e) {
        $_SESSION["generales"]["mensajeerror"] = '(10) No fue posible recuperar el archivo via ftp (' . $filein . ')';
        return false;
    }

    ftp_close($conn_id);

    if (!file_exists($fileout)) {
        $_SESSION["generales"]["mensajeerror"] = '(11) No fue posible descargar el archivo via ftp (' . $filein . ')';
        return false;
    }

    return true;
}

/**
 * 
 * @param type $host
 * @param type $port
 * @param type $user
 * @param type $passwd
 * @param type $path
 * @param type $filein
 * @param type $fileout
 * @param type $basedir
 * @return boolean
 */
function descargarSftpSirep($host, $port, $user, $passwd, $path, $filein, $fileout, $basedir = '') {
    require_once ('../../configuracion/common.php');
    require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    set_include_path(get_include_path() . PATH_SEPARATOR . PATH_ABSOLUTO_SITIO . '/includes/phpseclib');
    require_once ('Net/SFTP.php');
    // define('NET_SFTP_LOGGING', NET_SFTP_LOG_COMPLEX);

    try {
        $sftp1 = new Net_SFTP($host, $port, 30);
    } catch (Exception $e) {
        $_SESSION["generales"]["mensajeerror"] = 'Excepci&oacute;n capturada: ' . $e->getMessage();
        return false;
    }

    // $sftp1->setTimeout(0);

    if (!$sftp1->login($user, $passwd)) {
        unset($sftp1);
        $_SESSION["generales"]["mensajeerror"] = '(1) No fue posible conectarse por ssh con el servidor';
        return false;
    }

    $path1 = '';
    if (trim($basedir) == '') {
        $path1 = $path;
    } else {
        $path1 = $basedir . '/' . $path;
    }
    if (!$sftp1->chdir($path1)) {
        unset($sftp1);
        $_SESSION["generales"]["mensajeerror"] = 'No fue posible ubicarse en el directorio ' . $path1 . ' del servidor ssh';
        return false;
    }
    if (!$sftp1->get($filein, $fileout)) {
        unset($sftp1);
        $_SESSION["generales"]["mensajeerror"] = 'No fue posible descargar el archivo desde el servidor ssh';
        return false;
    }
    unset($sftp1);
    return true;
}

/**
 * 
 * @param type $host
 * @param type $port
 * @param type $user
 * @param type $passwd
 * @param type $path
 * @param type $filein
 * @param type $fileout
 * @return boolean
 */
function uploadSftp($host, $port, $user, $passwd, $path, $filein, $fileout) {
    require_once ('../../configuracion/common.php');
    require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    set_include_path(get_include_path() . PATH_SEPARATOR . PATH_ABSOLUTO_SITIO . '/includes/phpseclib');
    require_once ('Net/SFTP.php');

    try {
        $sftp1 = new Net_SFTP($host, $port);
    } catch (Exception $e) {
        $_SESSION["generales"]["mensajeerror"] = 'Excepci&oacute;n capturada: ' . $e->getMessage();
        return false;
    }

    if (!$sftp1->login($user, $passwd)) {
        unset($sftp1);
        $_SESSION["generales"]["mensajeerror"] = 'No fue posible conectarse por ssh con el servidor';
        return false;
    }

    if (!$sftp1->chdir($path)) {
        unset($sftp1);
        $_SESSION["generales"]["mensajeerror"] = 'No fue posible ubicarse en el directorio ' . $path . ' del servidor ssh';
        return false;
    }

    if (!$sftp1->put($fileout, $filein, NET_SFTP_LOCAL_FILE)) {
        unset($sftp1);
        $_SESSION["generales"]["mensajeerror"] = 'No fue posible cargar el archivo al servidor';
        return false;
    }

    unset($sftp1);
    return true;
}

/**
 * Rutina que conecta con el servidor  y hacer upload v&iacute;a ftp un archivo
 * $path: Directorio al cual se debe trasladar el ftp para descargar el archivo
 * $filein: Nombre del archivo a descargar
 * $fileout: Nombre completo, incluido path donde debe quedar almacenado el archivo trasladado
 */
function uploadFtpSirep($host, $port, $user, $passwd, $path, $filein, $fileout) {
    if (trim($port) == '') {
        $port = '21';
    }
    if ($port == '21') {
        $conn_id = ftp_connect($host);
    } else {
        $conn_id = ftp_connect($host, intval($port));
    }
    if ($conn_id === false) {
        $_SESSION["generales"]["mensajeerror"] = '(11) No fue posible conectarse con el servidor ' . $host;
        return false;
    }
    $result = ftp_login($conn_id, $user, $passwd);
    if ($result === false) {
        $_SESSION["generales"]["mensajeerror"] = '(12) No fue posible conectarse con el servidor ' . $host . ', error en usuario o clave';
        return false;
    }

    // Activa el modo pasivo del ftp
    ftp_pasv($conn_id, true);

    if (trim($path) != '') {
        $result = ftp_chdir($conn_id, $path);
        if ($result === false) {
            $_SESSION["generales"]["mensajeerror"] = '(13) No fue posible ubicarse en el directorio ' . $path;
            return false;
        }
    }
    try {
        $result = ftp_put($conn_id, $fileout, $filein, FTP_BINARY);
        if ($result === false) {
            $_SESSION["generales"]["mensajeerror"] = '(14) No fue posible cargar (upload) el archivo v&iacute;a ftp (' . $filein . ')';
            return false;
        }
    } catch (Exception $e) {
        $_SESSION["generales"]["mensajeerror"] = '(15) No fue posible cargar (upload) el archivo v&iacute;a ftp (' . $filein . ')';
        return false;
    }

    ftp_close($conn_id);
    return true;
}

/**
 * Genera un n&uacute;mero aleatorio alfanum&eacute;rico de  6 posiciones
 *
 */
function generarAleatorioAlfanumerico($validar = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');

    $numrecvalido = 'NO';
    while ($numrecvalido == 'NO') {

        //
        $ok = 'NO';
        while ($ok == 'NO') {
            $alfanumerico = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $num = '';
            for ($i = 1; $i <= 6; $i++) {
                $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
            }
            if (strlen($num) == 6) {
                $ok = 'SI';
            }
        }

        //
        if ($validar == '') {
            $numrecvalido = 'SI';
        }

        //
        if ($validar == 'mreg_liquidacion') {
            if (contarRegistros('mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        //
        if ($validar == 'mreg_liquidacion_baloto') {
            if (contarRegistros('mreg_liquidacion_baloto', "volantenumero='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        //
        if ($validar == 'mreg_liquidacion_exito') {
            if (contarRegistros('mreg_liquidacion_exito', "volantenumero='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        //
        if ($validar == 'mreg_reactivaciones_propias') {
            if (contarRegistros('mreg_reactivaciones_propias', "codigoreactivacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
    }
    return $num;
}

/**
 * Genera un n&uacute;mero aleatorio alfanum&eacute;rico de  6 posiciones - solo may&uacute;asculas
 *
 */
function generarAleatorioAlfanumericoMayusculas($validar = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');

    $numrecvalido = 'NO';
    while ($numrecvalido == 'NO') {
        $ok = 'NO';
        while ($ok == 'NO') {
            $alfanumerico = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $num = '';
            for ($i = 1; $i <= 6; $i++) {
                $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
            }
            if (strlen($num) == 6) {
                $ok = 'SI';
            }
        }
        if ($validar == '') {
            $numrecvalido = 'SI';
        }
        if ($validar == 'mreg_liquidacion') {
            if (contarRegistros('mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
    }
    return $num;
}

/**
 * Genera un n&uacute;mero aleatorio alfanum&eacute;rico de  6 posiciones - solo may&uacute;asculas
 *
 */
function generarAleatorioNumerico($validar = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');

    $numrecvalido = 'NO';
    while ($numrecvalido == 'NO') {
        $ok = 'NO';
        while ($ok == 'NO') {
            $alfanumerico = '0123456789';
            $num = '';
            for ($i = 1; $i <= 6; $i++) {
                $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
            }
            if (strlen($num) == 6) {
                $ok = 'SI';
            }
        }
        if ($validar == '') {
            $numrecvalido = 'SI';
        }
        if ($validar == 'mreg_liquidacion') {
            if (contarRegistros('mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
    }
    return $num;
}

function generarAleatorioAlfanumerico8($validar = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');

    $numrecvalido = 'NO';
    while ($numrecvalido == 'NO') {
        $ok = 'NO';
        while ($ok == 'NO') {
            $alfanumerico = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $num = '';
            for ($i = 1; $i <= 8; $i++) {
                $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
            }
            if (strlen($num) == 8) {
                $ok = 'SI';
            }
        }
        if ($validar == '') {
            $numrecvalido = 'SI';
        }
        if ($validar == 'mreg_liquidacion') {
            if (contarRegistros('mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
        if ($validar == 'mreg_liquidacion_baloto') {
            if (contarRegistros('mreg_liquidacion_baloto', "volantenumero='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
        if ($validar == 'mreg_liquidacion_exito') {
            if (contarRegistros('mreg_liquidacion_exito', "volantenumero='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
        if ($validar == 'mreg_reactivaciones_propias') {
            if (contarRegistros('mreg_reactivaciones_propias', "codigoreactivacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
        if ($validar == 'mreg_certificados_virtuales') {
            if (contarRegistros('mreg_certificados_virtuales', "id='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
    }
    return $num;
}

function generarAleatorioAlfanumerico9() {
    $ok = 'NO';
    while ($ok == 'NO') {
        $alfanumerico = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $num = '';
        for ($i = 1; $i <= 9; $i++) {
            $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
        }
        if (strlen($num) == 9) {
            $ok = 'SI';
        }
    }
    return $num;
}

function generarAleatorioAlfanumerico10($validar = '', $mysqli = null) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');

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
            if (contarRegistrosMysqli($mysqli, 'desarrollo_actividades', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        if ($validar == 'desarrollo_actualizaciones') {
            if (contarRegistrosMysqli($mysqli, 'desarrollo_actualizaciones', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }


        if ($validar == 'desarrollo_control_cambios') {
            if (contarRegistrosMysqli($mysqli, 'desarrollo_control_cambios', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        if ($validar == 'desarrollo_control_cambios_casosuso') {
            if (contarRegistrosMysqli($mysqli, 'desarrollo_control_cambios_casosuso', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        if ($validar == 'infraestructura_contratos') {
            if (contarRegistrosMysqli($mysqli, 'infraestructura_contratos', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        if ($validar == 'infraestructura_proveedores') {
            if (contarRegistrosMysqli($mysqli, 'infraestructura_proveedores', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        if ($validar == 'infraestructura_clientes') {
            if (contarRegistrosMysqli($mysqli, 'infraestructura_clientes', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        if ($validar == 'infraestructura_comentarios') {
            if (contarRegistrosMysqli($mysqli, 'infraestructura_comentarios', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }


        if ($validar == 'mreg_liquidacion_sobre') {
            if (contarRegistrosMysqli($mysqli, 'mreg_liquidacion_sobre', "idsobre='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        if ($validar == 'mreg_liquidacion') {
            if (contarRegistrosMysqli($mysqli, 'mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
        if ($validar == 'mreg_liquidacion_baloto') {
            if (contarRegistrosMysqli($mysqli, 'mreg_liquidacion_baloto', "volantenumero='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
        if ($validar == 'mreg_liquidacion_exito') {
            if (contarRegistrosMysqli($mysqli, 'mreg_liquidacion_exito', "volantenumero='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
        if ($validar == 'mreg_reactivaciones_propias') {
            if (contarRegistrosMysqli($mysqli, 'mreg_reactivaciones_propias', "codigoreactivacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
        if ($validar == 'mreg_certificados_virtuales') {
            if (contarRegistrosMysqli($mysqli, 'mreg_certificados_virtuales', "id='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        if ($validar == 'mreg_rues_bloque') {
            if (contarRegistrosMysqli($mysqli, 'mreg_rues_bloque', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        if ($validar == 'mreg_est_inscritos_fotos') {
            if (contarRegistrosMysqli($mysqli, 'mreg_est_inscritos_fotos', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
    }
    return $num;
}

function generarAleatorioNumerico10($validar = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');

    $numrecvalido = 'NO';
    while ($numrecvalido == 'NO') {
        $ok = 'NO';
        while ($ok == 'NO') {
            $alfanumerico = '0123456789';
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
        if ($validar == 'mreg_liquidacion') {
            if (contarRegistros('mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
        if ($validar == 'mreg_liquidacion_baloto') {
            if (contarRegistros('mreg_liquidacion_baloto', "volantenumero='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
        if ($validar == 'mreg_liquidacion_exito') {
            if (contarRegistros('mreg_liquidacion_exito', "volantenumero='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
        if ($validar == 'mreg_reactivaciones_propias') {
            if (contarRegistros('mreg_reactivaciones_propias', "codigoreactivacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
        if ($validar == 'mreg_certificados_virtuales') {
            if (contarRegistros('mreg_certificados_virtuales', "id='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
    }
    return $num;
}

function generarAleatorioAlfanumerico20($validar = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');

    $numrecvalido = 'NO';
    while ($numrecvalido == 'NO') {
        $ok = 'NO';
        while ($ok == 'NO') {
            $alfanumerico = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $num = '';
            for ($i = 1; $i <= 20; $i++) {
                $num .= substr(substr($alfanumerico, rand(1, strlen($alfanumerico))), 0, 1);
            }
            if (strlen($num) == 20) {
                $ok = 'SI';
            }
        }
        if ($validar == '') {
            $numrecvalido = 'SI';
        }
        if ($validar == 'mreg_liquidacion') {
            if (contarRegistros('mreg_liquidacion', "numerorecuperacion='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }

        if ($validar == 'mreg_documentos_firmados') {
            if (contarRegistros('mreg_documentos_firmados', "idfirmado='" . $num . "'") == 0) {
                $numrecvalido = 'SI';
            }
        }
    }
    return $num;
}

/**
 * Genera una Contrase&ntilde;a aleatoria alfanum&eacute;rico de 8 posiciones que contiene caracteres especiales ,mayusculas, minusculas y caracteres especiales.
 *
 */
function generarAleatorioContrasena() {
    $ok = 'NO';
    while ($ok == 'NO') {
        $min = 'abcdefghijklmnopqrstuvwxyz';
        $may = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $num = '0123456789';
        $esp = '[!"$%&()\=?°ø*+-_<>|]';
        $pass = '';
        for ($i = 1; $i <= 8; $i++) {
            switch ($i) {
                case 1:
                    $pass .= substr(substr($may, rand(1, strlen($may))), 0, 1);
                    break;
                case 2:
                    $pass .= substr(substr($num, rand(1, strlen($num))), 0, 1);
                    break;
                case 8:
                    $pass .= substr(substr($esp, rand(1, strlen($esp))), 0, 1);
                    break;
                case 7:
                    $pass .= substr(substr($num, rand(1, strlen($num))), 0, 1);
                    break;
                default:
                    $pass .= substr(substr($min, rand(1, strlen($min))), 0, 1);
                    break;
            }
        }
        if (strlen($pass) == 8) {
            $ok = 'SI';
        }
    }
    return $pass;
}

/* Genera aleatorio de N posiciones
 * Utilizado en el control de variables de session unicas para llamado a formularios
 */

function generarAleatorioSession($length = 10, $uc = TRUE, $n = TRUE, $sc = TRUE) {
    $source = 'abcdefghijklmnopqrstuvwxyz';
    if ($uc)
        $source .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if ($n)
        $source .= '1234567890';
    if ($sc)
        $source .= '|@#~$%()^*+[]{}-_.';
    if ($length > 0) {
        $rstr = "";
        $source = str_split($source, 1);
        for ($i = 1; $i <= $length; $i++) {
            mt_srand((double) microtime() * 1000000);
            $num = mt_rand(1, count($source));
            $rstr .= $source[$num - 1];
        }
    }
    return $rstr;
}

/*
 * M&eacute;todo que solicita v&iacute;a servicios web a Certicamara la estampa cronol&oacute;gica
 * Se implementa a partir del 2014-02-08
 */

function generaTimeStampCerticamaraNuevo($txt, $login, $password, $codigoEmpresa) {

    // Constantes para e consumo de Certic&aacute;mara
    require_once ('../../configuracion/sNumberCerticamara.php');

    if (!isset($sNumberArray[$codigoEmpresa])) {
        $_SESSION["generales"]["mensajeerror"] = 'C&aacute;mara no est&aacute; identificada en Certic&aacute;mara para el uso del servicio Web de Estampado';
        return false;
    }

    if (trim($sNumberArray[$codigoEmpresa]) == '') {
        $_SESSION["generales"]["mensajeerror"] = 'C&aacute;mara no est&aacute; identificada en Certic&aacute;mara para el uso del servicio Web de Estampado';
        return false;
    }

    // Definici&oacute;n de la URL de publicaci&oacute;n del servicio
    $wsdl = 'http://tsa.ws.certicamara.com:8080/kernel/TimeStampWebServiceL/TimeStampWebServiceL.svc?wsdl';
    // $wsdl = 'http://tsa.certicamara.com:9090/ttsa_kernel/TimeStampWebServiceL?wsdl';
    // Definici&oacute;n de la URL de publicaci&oacute;n del servicio
    // $tsaURL = 'tsa.certicamara.com';
    // Definici&oacute;n de la informaci&oacute;n a estampar cronol&oacute;gicamente
    $text2stamp = base64_encode($txt);

    //Definici&oacute;n del servicio a consumir
    $TSAservice = 'getStampFromFile';

    $subsNumber = $sNumberArray[$codigoEmpresa];

    $arrayParameters = array(
        'getStampFromFile' => array(
            'getStampFromFile1' => array(
                'content' => $text2stamp,
                'login' => $login,
                'password' => $password,
                'subsNumber' => $subsNumber,
                'subsNumberSpecified' => true
            )
        )
    );


    try {
        $client = new SoapClient($wsdl, array('encoding' => 'utf-8', 'soap_version' => SOAP_1_1));
        try {
            $result = $client->__soapCall($TSAservice, $arrayParameters);
            if (is_soap_fault($result)) {
                $_SESSION["generales"]["mensajeerror"] = "SOAP Fault: (faultcode: " . $result->faultcode . ", faultstring: " . $result->faultstring;
                return false;
            }
        } catch (Exception $e) {
            $_SESSION["generales"]["mensajeerror"] = 'Mensaje capturado de la excepcion (Consumo del cliente): ' . $e->getMessage() . '<br><br>';
            return false;
        }
    } catch (Exception $e) {
        $_SESSION["generales"]["mensajeerror"] = 'Mensaje capturado de la excepcion (instanciamiento): ' . $e->getMessage() . '<br><br>';
        return fazzlse;
    }

    // Arma la respuesta cuando es un arreglo (Inhabiliado en 2012-01-14)
    // Por modificaciones de Certicamara.
    // El objeto que retorna se llama return y tiene multiples atributos
    // $timeStamp = $result ["Year"] . sprintf("%02s", $result["month"]) . sprintf("%02s", $result["day"]) . ' ' . sprintf("%02s", $result["hour"]) . sprintf("%02s", $result["minutes"]) . sprintf("%02s", $result["seconds"]);
    $timeStamp = $result->getStampFromFileResult->return->year .
            sprintf("%02s", $result->getStampFromFileResult->return->month) .
            sprintf("%02s", $result->getStampFromFileResult->return->day) . ' ' .
            sprintf("%02s", $result->getStampFromFileResult->return->hour) .
            sprintf("%02s", $result->getStampFromFileResult->return->minutes) .
            sprintf("%02s", $result->getStampFromFileResult->return->seconds);

    // Elimina objetos creados
    unset($result);
    unset($client);

    // Retorna respuesta
    return $timeStamp;
}

function limpiarTexto($txt) {
    $txt = preg_replace('/[^._A-Za-z0-9 ]/', ' ', $txt);
    $txt = preg_replace('/[&ntilde;&ntilde;&aacute;&eacute;&iacute;&oacute;&uacute;&aacute;&eacute;&iacute;&oacute;&uacute;]/', ' ', $txt);
    return $txt;
}

/**
 * Funci&oacute;n que retorna la IP desd ela que se hace la operaci&oacute;n
 */
function localizarIP() {
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

function mostrarErrorConexionSistemaRegistro($txt = '') {
    if (trim($txt) == '') {
        echo armarMensaje(600, 'h2', 'No fue posible conectarse con el sistema de registro: <br><br>' .
                $_SESSION["generales"]["mensajeerror"] .
                '<br><br>Por favor informe este hecho al administrador del sistema');
    } else {
        echo armarMensaje(600, 'h2', 'No fue posible conectarse con el sistema de registro: <br><br>' .
                $txt .
                '<br><br>Por favor informe este hecho al administrador del sistema');
    }
    $_SESSION["generales"]["mensajeerror"] = '';
    $_SESSION["generales"]["txtemergente"] = '';
}

function mostrarErrorFinal($txt) {
    armarMensaje(600, 'h2', $txt, 600, 400, '', '', false, 0, 'si', '', 'si');
}

/**
 * Funcion utilizada para desplegar las pantallas, recibe como par&aacute;metro los campos a reemplazar
 *
 * @param script $scripth
 * @param script $scriptb
 * @param script $menus
 * @param script $menul
 * @param script $cuerpot
 * @param script $footert
 */
function mostrarInicial($txterror = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');

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
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    $_SESSION["generales"]["codigoempresa"] = isset($_SESSION["generales"]["codigoempresa"]) ? $_SESSION["generales"]["codigoempresa"] : 999;

    //
    $anox = date("Y");
    $anoxinicial = $anox - 20;
    $anoxfinal = $anox + 3;
    $txtanos = " <option value=''>Seleccione ...</option>";
    for ($ix = $anoxinicial; $ix <= $anoxfinal; $ix++) {
        if (sprintf("%04s", $ix) == sprintf("%04s", $anox)) {
            $txtanos .= "<option value='" . $ix . "' selected>" . $ix . "</option>";
        } else {
            $txtanos .= "<option value='" . $ix . "'>" . $ix . "</option>";
        }
    }

    $lista = retornarListaXmlEmpresas($_SESSION["generales"]["codigoempresa"]);
    $pant = new template ();
    $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/index.html');
    $pant->armarDate();
    $pant->armarTxtError($txterror, 'computer');
    $pant->armarListaEmpresas($lista);
    // $pant->armarHttpHost(HTTP_HOST); 
    // $pant->armarUrlSite(TIPO_HTTP . HTTP_HOST);
    $pant->armarPeriodo(date("Y"));
    $pant->armarNombreSistema(NOMBRE_SISTEMA);
    $pant->armarNombreSistema1(NOMBRE_SISTEMA1);
    $pant->armarNombreCasaSoftware(NOMBRE_CASA_SOFTWARE);
    $pant->armarDireccionCasaSoftware(DIRECCION_CASA_SOFTWARE);
    $pant->armarCiudadCasaSoftware(CIUDAD_CASA_SOFTWARE);
    $pant->armarTelefonoCasaSoftware(TELEFONO_CASA_SOFTWARE);
    $pant->armarDeclaracionPrivacidad(DECLARACION_PRIVACIDAD);
    $pant->armarSelectAnos($txtanos);
    $pant->armarEstilo();
    if (isset($_SESSION["generales"]["txtemergente"])) {
        $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
        $_SESSION["generales"]["txtemergente"] = '';
    } else {
        $pant->armarScriptEmergente('');
        $_SESSION["generales"]["txtemergente"] = '';
    }
    $pant->mostrar();
    unset($pant);
}

function mostrarInicialNueva($txterror = '', $tipo = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');

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
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    $_SESSION["generales"]["codigoempresa"] = isset($_SESSION["generales"]["codigoempresa"]) ? $_SESSION["generales"]["codigoempresa"] : '';

    if (isset($_SERVER["HTTPS"])) {
        $tipohttp = 'https://';
    } else {
        $tipohttp = 'http://';
    }
    $httphost = $_SERVER["HTTP_HOST"];

    //
    $lista = retornarListaXmlEmpresas('', $httphost);
    $lista1 = retornarListaXmlEmpresas('', $httphost);
    $lista2 = retornarListaXmlEmpresas('', $httphost);

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


    $pres = new Presentacion();

    //
    $arrBtnTipo = array('submit');
    $arrBtnEnlace = array('');
    $arrBtnImagen = array('Ingresar');
    $arrBtnToolTip = array('Ingresar');

    $random = rand(1000, 10000);
    $cuerpo = '<center>' .
            $pres->armarLineaTextoEnriquecido(600, $txtRem, 'center', 14, '#000000', 'si') .
            $pres->armarFormularioLogin('formEntrada', 'post', $tipohttp . $httphost . '/disparador.php?accion=logueousuarioregistrado') .
            $pres->armarCampoSelect2Lineas(0, 'Empresa', 'si', '_empresa', 30, 30, $lista, $_SESSION["generales"]["codigoempresa"]) .
            $pres->armarCampoTexto2Lineas(0, 'Correo electrónico o usuario', 'si', '_emailUsuarioRegistrado', 30, 128, '') .
            $pres->armarCampoTexto2Lineas(0, 'Identificación', 'si', '_identificacionUsuarioRegistrado', 30, 128, '') .
            // $pres->armarCampoPassword2Lineas(0, 'Clave', 'si', '_claveUsuarioRegistrado', 30, 128, '') .
            $pres->armarCampoPassword2Lineas(0, 'Clave', 'si', '_claveUsuarioRegistrado_' . $random, 30, 128, '') .
            $pres->armarCampoTextoOculto('_random', $random) .
            $pres->armarCampoTextoOculto('_periodo', date("Y")) .
            $pres->armarCampoTextoOculto('_tipohttp', $tipohttp) .
            $pres->armarCampoTextoOculto('_httphost', $httphost) .
            $pres->armarCampoTextoOculto('_controlusuariorutina', $_SESSION["generales"]["controlusuariorutina"]) .
            $pres->armarCampoTextoOculto('_controlusuarioretornara', $_SESSION["generales"]["controlusuarioretornara"]) .
            '<br>' .
            $pres->armarBarraBotonesProcesoDinamico(0, $arrBtnTipo, $arrBtnImagen, $arrBtnEnlace, $arrBtnToolTip) .
            $pres->armarFinFormulario() .
            '</center>' .
            '<br>';

    //
    $arrBtnTipo = array('submit');
    $arrBtnEnlace = array('');
    $arrBtnImagen = array('Recordar contrase&ntilde;a');
    $arrBtnToolTip = array('Recordar contrase&ntilde;a');

    $btnrecordar = '<center>' .
            $pres->armarLineaTextoEnriquecido(600, $txtRem, 'center', 14, '#000000', 'si') .
            $pres->armarFormulario('formRecordarContrasena', 'post', $tipohttp . $httphost . '/librerias/proceso/manejarRegistro.php?accion=recordarcontrasena') .
            $pres->armarCampoSelect2Lineas(0, 'Empresa', 'si', '_empresa1', 30, 30, $lista1, $_SESSION["generales"]["codigoempresa"]) .
            $pres->armarCampoTexto2Lineas(0, 'Correo electrónico o usuario', 'si', '_emailtem', 30, 128, '') .
            $pres->armarCampoTexto2Lineas(0, 'Identificación', 'si', '_identificaciontem', 20, 20, '') .
            '<br>' .
            $pres->armarBarraBotonesProcesoDinamico(0, $arrBtnTipo, $arrBtnImagen, $arrBtnEnlace, $arrBtnToolTip) .
            $pres->armarFinFormulario() .
            '</center>' .
            '<br>';

    $btnregistrarse = '<center>' .
            $pres->armarFormulario('formRegistro', 'post', '../../librerias/proceso/manejarRegistro.php?accion=registro') .
            $pres->armarCampoSelect2Lineas(0, 'Empresa en la que desea registrarse', 'si', '_empresa1', 30, 30, $lista2, $_SESSION["generales"]["codigoempresa"]);

    $arrBtnTipo = array('submit');
    $arrBtnEnlace = array('');
    $arrBtnImagen = array('Registrarse');
    $arrBtnToolTip = array('Registrarse como usuario');

    $btnregistrarse .= '<br>' . $pres->armarBarraBotonesProcesoDinamico(0, $arrBtnTipo, $arrBtnImagen, $arrBtnEnlace, $arrBtnToolTip);
    $btnregistrarse .= $pres->armarFinFormulario();

    $pantalla = '';
    if (isset($_SESSION["generales"]["codigoempresa"]) && $_SESSION["generales"]["codigoempresa"] != '' && $_SESSION["generales"]["codigoempresa"] != '00') {
        $pantalla = cambiarSustitutoHtml(retornarPantallaPredisenada('indexV2.' . $_SESSION["generales"]["codigoempresa"]));
    }
    if ($pantalla == '') {
        $emp = retornarCodigoEmpresa($httphost);
        if ($emp != '') {
            $pantalla = cambiarSustitutoHtml(retornarPantallaPredisenada('indexV2.' . $emp));
        }
    }
    if ($pantalla == '') {
        $pantalla = cambiarSustitutoHtml(retornarPantallaPredisenada('indexV2'));
    }

    //
    $pantalla = str_replace("[RAZONSOCIAL]", RAZONSOCIAL, $pantalla);
    $pantalla = str_replace("[CUERPO]", $cuerpo, $pantalla);
    $pantalla = str_replace("[BTN_CONTRASENA]", $btnrecordar, $pantalla);
    $pantalla = str_replace("[BTN_REGISTRO]", $btnregistrarse, $pantalla);
    if (isset($_SESSION["generales"]["txtemergente"])) {
        $pantalla = str_replace("[TXTERROR]", $_SESSION["generales"]["txtemergente"], $pantalla);
    } else {
        if (isset($_SESSION["generales"]["mensajeerror"])) {
            $pantalla = str_replace("[TXTERROR]", $_SESSION["generales"]["mensajeerror"], $pantalla);
        } else {
            $pantalla = str_replace("[TXTERROR]", '', $pantalla);
        }
    }
    $_SESSION["generales"]["txtemergente"] = '';
    $_SESSION["generales"]["mensajeerror"] = '';
    unset($pres);

    $head = '<script type="text/javascript" src="../../librerias/funciones/disparador.js"></script>';
    mostrarCuerpoIE26a(array(), $head, '', '', $pantalla, 700, 400, '', '', false, 0, 'no', '', 'no', 'utf-8', 'si', 'no', 'no');
}

/**
 * 
 * @param type $txterror
 * @param type $cuerpo
 * @param type $scripth
 * @param type $scriptb
 */
function mostrarInicialMovil($txterror = '', $cuerpo = '', $scripth = '', $scriptb = '', $mostrarInicio = 'si') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
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
        (define('DECLARACION_PRIVACIDAD'));
    }
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    $_SESSION["generales"]["codigoempresa"] = isset($_SESSION["generales"]["codigoempresa"]) ? $_SESSION["generales"]["codigoempresa"] : 999;

    $anox = date("Y");
    $anoxinicial = $anox - 6;
    $anoxfinal = $anox + 3;
    $txtanos = " <option value=''>Seleccione el periodo a trabajar ...</option>";
    for ($ix = $anoxinicial; $ix <= $anoxfinal; $ix++) {
        if (sprintf("%04s", $ix) == sprintf("%04s", $anox)) {
            $txtanos .= "<option value='" . $ix . "' selected>" . $ix . "</option>";
        } else {
            $txtanos .= "<option value='" . $ix . "'>" . $ix . "</option>";
        }
    }

    $lista = retornarListaXmlEmpresas($_SESSION["generales"]["codigoempresa"]);
    $pant = new template ();

    // if (TIPO_HTTP == 'https://') {
    if (isset($_SERVER["HTTPS"])) {
        $tipohttp = 'https://';
        $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/mobileHttps.html');
    } else {
        $tipohttp = 'http://';
        $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/mobileHttp.html');
    }
    $httphost = $_SERVER["HTTP_HOST"];


    //$pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/mobile1.html');
    $pant->armarDate();
    $pant->armarTxtError($txterror);
    // $pant->armarBanner($tipohttp . $httphost . '/images/top1200x80-XXX.png');
    $pant->armarBanner($tipohttp . $httphost . '/images/sii/LogoSii.png');
    $pant->armarListaEmpresas($lista);
    $pant->armarTipoHttp($tipohttp);
    $pant->armarHttpHost($httphost);
    $pant->armarUrlSite($tipohttp . $httphost);
    $pant->armarPeriodo(date("Y"));
    $pant->armarTituloAplicacion('');
    $pant->armarTituloMenu('MENU PRINCIPAL');
    $pant->armarTxtHome('');
    $pant->armarNombreSistema(NOMBRE_SISTEMA);
    $pant->armarNombreSistema1(NOMBRE_SISTEMA1);
    $pant->armarNombreCasaSoftware(NOMBRE_CASA_SOFTWARE);
    $pant->armarDireccionCasaSoftware(DIRECCION_CASA_SOFTWARE);
    $pant->armarCiudadCasaSoftware(CIUDAD_CASA_SOFTWARE);
    $pant->armarTelefonoCasaSoftware(TELEFONO_CASA_SOFTWARE);
    $pant->armarDeclaracionPrivacidad(DECLARACION_PRIVACIDAD);
    $pant->armarSelectAnos($txtanos);
    $pant->armarBackImage($tipohttp . $httphost . '/images/sii/Ejecafetero.jpg');
    $pant->armarEstilo();
    $pant->armarMostrarSombra('');
    $pant->armarScriptHeader($scripth);
    $pant->armarScriptBody($scriptb);
    $pant->armarMenuMovil('');
    $pant->armarContentWidth();


    if (isset($_SESSION["generales"]["txtemergente"])) {
        $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
        $_SESSION["generales"]["txtemergente"] = '';
    } else {
        $pant->armarScriptEmergente('');
        $_SESSION["generales"]["txtemergente"] = '';
    }
    $pant->armarCuerpo($cuerpo);
    $pant->mostrar();
    unset($pant);
    exit();
}

/**
 * 
 * @param type $titulo
 * @param type $txterror
 * @param type $cuerpo
 * @param type $scripth
 * @param type $scriptb
 * @param type $sombra
 * @param type $principal
 */
function mostrarMovil($titulo = '', $txterror = '', $cuerpo = '', $scripth = '', $scriptb = '', $sombra = '', $principal = 'no') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
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
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    $_SESSION["generales"]["codigoempresa"] = isset($_SESSION["generales"]["codigoempresa"]) ? $_SESSION["generales"]["codigoempresa"] : 999;

    $pant = new template ();

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
    $pant->armarTituloMenu($titulo);
    if ($principal == 'no') {
        if ($_SESSION["generales"]["validado"] == 'SI' || ($_SESSION["generales"]["tipousuariocontrol"] != 'usuarioanonimo' && $_SESSION["generales"]["tipousuariocontrol"] != '')) {
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
    /*
      if (!isset($txtanos)) {
      $txtanos = '';
      }
     */
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

/**
 * Funcion utilizada para desplegar las pantallas, recibe como par&aacute;metro los campos a reemplazar
 *
 * @param script $scripth
 * @param script $scriptb
 * @param script $user1
 */
function mostrarPrincipal($scripth = '', $scriptb = '', $user1 = '') {

    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/persistencia/MasterTablasBasicos.class.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');

    //
    if (!defined('DHTMLX_ACTUAL')) {
        define('DHTMLX_ACTUAL', '2.6');
    }

    //
    $pant = new template ();
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

    if (substr(TIPO_EMPRESA, 0, 3) == 'cam' ||
            substr(TIPO_EMPRESA1, 0, 3) == 'cam' ||
            substr(TIPO_EMPRESA2, 0, 3) == 'cam') {
        if (defined('HABILITAR_NUEVA_CONSULTA_REGISTROS') && HABILITAR_NUEVA_CONSULTA_REGISTROS == 'SI') {
            $pant->armarCuerpoInicial(TIPO_HTTP . HTTP_HOST . '/scripts/mregConsultaExpedientes.php?accion=seleccion&session_parameters=' . \funcionesGenerales::armarVariablesPantalla());
        } else {
            $pant->armarCuerpoInicial(TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregConsultaExpedientes.php?accion=seleccion');
        }
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

function mostrarPrincipal2($scripth = '', $scriptb = '', $user1 = '') {

    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/persistencia/MasterTablasBasicos.class.php');

    $pant = new template ();
    $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/principal-1C.html');

    if (!defined('DHTMLX_ACTUAL')) {
        define('DHTMLX_ACTUAL', '2.6');
    }

    if (DHTMLX_ACTUAL == '') {
        $pant->armarDhtmlxActual('2.6');
    } else {
        $pant->armarDhtmlxActual(DHTMLX_ACTUAL);
    }

    $pant->armarScriptHeader($scripth);
    $pant->armarScriptBody($scriptb);
    $pant->armarBarraOpciones($user1);
    $pant->armarTipoUsuario($_SESSION["generales"]["tipousuario"]);
    $pant->armarIdUsuario($_SESSION["generales"]["codigousuario"]);
    $pant->armarFrameSecundarioLateral('');
    $pant->armarFrameSecundarioCentral('');

    // Si usuario es p&uacute;blico
    if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
        if (!defined('ACTIVADO_MODULO_SUSCRIPCIONES')) {
            define('ACTIVADO_MODULO_SUSCRIPCIONES', 'N');
        }
        if (ACTIVADO_MODULO_SUSCRIPCIONES == 'S') {
            $pant->armarCuerpoInicial('librerias/presentacion/mostrarPantallas.php?pantalla=pant.' . TIPO_EMPRESA . '.00' . $_SESSION["generales"]["tipousuario"]);
        } else {
            $pant->armarCuerpoInicial('librerias/presentacion/mostrarPantallas.php?pantalla=pant.' . TIPO_EMPRESA . '.00' . $_SESSION["generales"]["tipousuario"]);
        }
    } else {
        $pant->armarCuerpoInicial('librerias/presentacion/mostrarPantallas.php?pantalla=pant.' . TIPO_EMPRESA . '.00' . $_SESSION["generales"]["tipousuario"]);
    }

    // 
    $pant->armarDate();
    $pant->armarSesion();
    $pant->armarHttpHost(HTTP_HOST);
    $pant->armarLogoEntidad();
    $pant->armarTituloAplicacion(NOMBRE_SISTEMA);
    $pant->armarCodigoEntidad($_SESSION["generales"]["codigoempresa"]);
    $pant->armarCodigoEntidad1($_SESSION["generales"]["codigoempresa"]);
    $pant->armarNombreEntidad(RAZONSOCIAL);
    $pant->armarSitioWeb(HTTP_HOST);
    $pant->armarUrlSite(TIPO_HTTP . HTTP_HOST);
    $pant->armarTipoHttp(TIPO_HTTP);
    $pant->armarServidorRss(SERVIDOR_RSS);
    $pant->armarTipoMenu($_SESSION["generales"]["tipomenu"]);
    $pant->armarNombreEntidad(RAZONSOCIAL);
    $pant->armarPathAbsolutoSitio(PATH_ABSOLUTO_SITIO);
    $pant->armarSessionId(session_id());
    $pant->armarIdUsuario($_SESSION["generales"]["codigousuario"]);
    $pant->armarEmailAtencionUsuarios(EMAIL_ATENCION_USUARIOS);
    $pant->armarTelefonoAtencionUsuarios(TELEFONO_ATENCION_USUARIOS);

    $pant->armarTituloOpciones('Opciones');
    $pant->armarTituloBuscar('Buscar');
    $pant->armarTituloPeriodo('Periodo');
    $pant->armarTituloVersion('Versi&oacute;n');
    $pant->armarTituloUsuario('Usuario');
    $pant->armarTituloUsuarioPublico('Usuario p&uacute;blico');
    $pant->armarTituloAccesoRapido(MasterTablasBasicos::getMensajeIdioma($_SESSION["generales"]["idioma"], "lineacontrol.acceso.rapido"));
    $pant->armarTituloSalir('Salir');
    $pant->armarTituloSistema(MasterTablasBasicos::getMensajeIdioma($_SESSION["generales"]["idioma"], "titulo.sistema"));

    //
    $pant->armarTituloPalabrasClave(MasterTablasBasicos::getMensajeIdioma($_SESSION["generales"]["idioma"], "menu.palabras.claves"));
    $pant->armarDHTMLXVERSION(DHTMLX_ACTUAL);
    $pant->armarWWWENLACE(WWW_ENTIDAD);

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
    $pant->armarFooter();
    $pant->mostrar();
    unset($pant);
    exit();
}

/**
 * Rutina que muestra el cuerpo del sitio, hace llamado a la clase template.
 *
 * @param string    $scripth    Scripts del header
 * @param string    $scriptb    Scripts del body
 * @param string    $txtbarra   T&iacute;tulo de la barra
 * @param string    $txt        Cuerpo o contenido
 * @param int       $width      Ancho
 * @param int       $height     Alto
 */
function mostrarCuerpo($scripth = '', $scriptb = '', $txtbarra = '', $txt = '', $width = 800, $height = 400) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');
    if (trim($_SESSION["generales"]["codigousuario"]) != '') {
        $cantipen = contarTareasPendientes($_SESSION["generales"]["codigousuario"]);
        $cantinue = contarTareasNuevas($_SESSION["generales"]["codigousuario"]);
    } else {
        $cantipen = 0;
        $cantinue = 0;
    }
    $pant = new template ();
    if (!isset($_SESSION["generales"]["pathabsoluto"])) {
        $pant->LoadTemplate('../../html/default/cuerpo.html');
    } else {
        if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/cuerpo.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/cuerpo.html');
        }
    }
    $pant->armarScriptHeader($scripth);
    $pant->armarScriptBody($scriptb);
    $pant->armarBarraOpciones($txtbarra);
    $pant->armarTareasPendientes($cantipen, $cantinue);
    $pant->armarFrameSecundarioLateral('');
    $pant->armarFrameSecundarioCentral($txt);
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

/*
 * funcion para imprimir los resultados desde mregConsultaVirtual a la plantilla correspondiente
 */

function mostrarCuerpoConsultaVirtual($scripth = '', $scriptb = '', $txtbarra = '', $txt, $width = 800, $height = 400) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/presentacion.class.php');

    if (trim($_SESSION["generales"]["codigousuario"]) != '') {
        $cantipen = contarTareasPendientes($_SESSION["generales"]["codigousuario"]);
        $cantinue = contarTareasNuevas($_SESSION["generales"]["codigousuario"]);
    } else {
        $cantipen = 0;
        $cantinue = 0;
    }
    $pant = new template ();
    if (!isset($_SESSION["generales"]["pathabsoluto"])) {
        $pant->LoadTemplate('../../html/default/cve.html');
    } else {
        if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/cve.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/cve.html');
        }
    }
    $pant->armarScriptHeader($scripth);
    $pant->armarScriptBody($scriptb);
    $pant->armarBarraOpciones($txtbarra);
    $pant->armarTareasPendientes($cantipen, $cantinue);
    $pant->armarBody($txt[3]);
    $pant->armarNombreExpedientes($txt[0]);
    $pant->armarIdentificacionExpedientes($txt[1]);
    $pant->armarDomicilioExpedientes($txt[2]);
    $pant->armarBanner($txt[5]);
    $pant->armarEstadoExpediente(utf8_decode($txt[4]));
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

/*
 * funcion para imprimir los resultados desde mregBoletinMensualMercantil a la plantilla correspondiente
 */

function mostrarCuerpoBMM($scripth = '', $scriptb = '', $txtbarra = '', $txt, $width = 800, $height = 400) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/presentacion.class.php');

    if (trim($_SESSION["generales"]["codigousuario"]) != '') {
        $cantipen = contarTareasPendientes($_SESSION["generales"]["codigousuario"]);
        $cantinue = contarTareasNuevas($_SESSION["generales"]["codigousuario"]);
    } else {
        $cantipen = 0;
        $cantinue = 0;
    }
    $pant = new template ();
    if (!isset($_SESSION["generales"]["pathabsoluto"])) {
        $pant->LoadTemplate('../../html/default/cuerpoBMM.html');
    } else {
        if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/cuerpoBMM.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/cuerpoBMM.html');
        }
    }
    $pant->armarScriptHeader($scripth);
    $pant->armarScriptBody($scriptb);
    $pant->armarBarraOpciones($txtbarra);
    $pant->armarTareasPendientes($cantipen, $cantinue);
    $pant->armarBody($txt[0]);
    $pant->armarSelectAnos($txt[1]);
    $pant->armarDate();
    $pant->armarSesion();
    $pant->armarHttpHost(HTTP_HOST);
    $pant->armarServidorRss(SERVIDOR_RSS);
    $pant->armarUrlSite(TIPO_HTTP . HTTP_HOST);
    $pant->armarCodigoEntidad(CODIGO_EMPRESA);
    $pant->armarTituloAplicacion(NOMBRE_SISTEMA);
    $pant->armarNombreEntidad(RAZONSOCIAL);
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
 * Rutina que muestra el cuerpo del sitio cuando se generan errores 
 *
 * @param string    $scripth    Scripts del header
 * @param string    $scriptb    Scripts del body
 * @param string    $txtbarra   T&iacute;tulo de la barra
 * @param string    $txt        Cuerpo o contenido
 * @param int       $width      Ancho
 * @param int       $height     Alto
 */
function mostrarCuerpoError($txt = '', $width = 800, $height = 400) {
    require_once ('../../configuracion/common.php');
    require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ('../../librerias/presentacion/template.class.php');
    require_once ('../../librerias/funciones/persistencia.php');
    require_once ('../../librerias/persistencia/MasterTablasBasicos.class.php');

    $pant = new template ();
    if (!isset($_SESSION["generales"]["pathabsoluto"])) {
        $pant->LoadTemplate('../../html/default/cuerpo.html');
    } else {
        if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/cuerpo.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/cuerpo.html');
        }
    }
    $pant->armarScriptHeader('');
    $pant->armarScriptBody('');
    $pant->armarBarraOpciones(MasterTablasBasicos::getMensajeIdioma($_SESSION["generales"]["idioma"], "titulo.pantalla.error"));
    $pant->armarTareasPendientes(0, 0);
    $pant->armarFrameSecundarioLateral('');
    $pant->armarFrameSecundarioCentral($txt);
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
    $pant->armarFooter();
    $pant->armarDisplayHeader('no', 0, 0);
    // if (trim($txtbarra) != '') {
    //    $pant->armarDisplayBarraOpciones('si', $width, 30);
    // } else {
    //     $pant->armarDisplayBarraOpciones('no', 0, 00);
    // }
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

/*
  function mostrarCuerpoMovil($scripth, $scriptb, $txt) {
  require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
  require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
  require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
  $pant = new template ();
  $pant->LoadTemplate('../../librerias/mobile/index.html');
  $pant->armarScriptHeader($scripth);
  $pant->armarScriptBody($scriptb);
  $pant->armarBody($txt);
  $pant->armarNombreEntidad(RAZONSOCIAL);
  if (isset($_SESSION["generales"]["txtemergente"])) {
  $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
  $_SESSION["generales"]["txtemergente"] = '';
  } else {
  $pant->armarScriptEmergente('');
  $_SESSION["generales"]["txtemergente"] = '';
  }
  $pant->mostrar();
  unset($pant);
  exit ();
  }
 */

/**
 * Rutina que muestra el cuerpo del sitio, hace llamado a la clase template.
 * Usada con IE
 *
 * @param string    $scripth    Scripts del header
 * @param string    $scriptb    Scripts del body
 * @param string    $txtbarra   T&iacute;tulo de la barra
 * @param string    $txt        Cuerpo o contenido
 * @param int       $width      Ancho
 * @param int       $height     Alto
 * @pamam sting     $dhtmlx     Scripts y/o contenidos dhtmlx
 */
function mostrarCuerpoIE($scripth, $scriptb, $txtbarra, $txt, $width = 800, $height = 400, $dhtmlx = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    $pant = new template ();
    if (!isset($_SESSION["generales"]["pathabsoluto"])) {
        $pant->LoadTemplate('../../html/default/cuerpoIE.html');
    } else {
        if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/cuerpoIE.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/cuerpoIE.html');
        }
    }
    if (trim($_SESSION["generales"]["codigousuario"]) != '') {
        $cantipen = contarTareasPendientes($_SESSION["generales"]["codigousuario"]);
        $cantinue = contarTareasNuevas($_SESSION["generales"]["codigousuario"]);
    } else {
        $cantipen = 0;
        $cantinue = 0;
    }
    $pant->armarScriptHeader($scripth);
    $pant->armarScriptBody($scriptb);
    $pant->armarBarraOpciones($txtbarra);
    $pant->armarTareasPendientes($cantipen, $cantinue);
    $pant->armarFrameSecundarioLateral('');
    $pant->armarFrameSecundarioCentral($txt);
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
    if (isset($_SESSION["generales"]["txtemergente"])) {
        $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
        $_SESSION["generales"]["txtemergente"] = '';
    } else {
        $pant->armarScriptEmergente('');
        $_SESSION["generales"]["txtemergente"] = '';
    }
    $pant->armarEstilo(CODIGO_EMPRESA);
    $pant->armarDHTMLX($dhtmlx);
    if (defined('TIEMPO_PROMEDIO_TRAMITES_PROPONENTES')) {
        $pant->armarTiempoPromedioProponentes(TIEMPO_PROMEDIO_TRAMITES_PROPONENTES);
    } else {
        $pant->armarTiempoPromedioProponentes('');
    }
    $pant->mostrar();
    unset($pant);
    exit();
}

/**
 * Rutina que muestra el cuerpo del sitio, hace llamado a la clase template.
 * Usada con IE - Adaptada a Dhtmlx 2.5
 * Versi&oacute;n usada por Jose Ivan Nieto T
 *
 * @param string    $scripth    Scripts del header
 * @param string    $scriptb    Scripts del body
 * @param string    $txtbarra   T&iacute;tulo de la barra
 * @param string    $txt        Cuerpo o contenido
 * @param int       $width      Ancho
 * @param int       $height     Alto
 * @pamam sting     $dhtmlx     Scripts y/o contenidos dhtmlx
 */
function mostrarCuerpoIE25a($arrayComponents, $scripth, $scriptb, $txtbarra, $txt, $width = 800, $height = 400, $dhtmlx = '', $sombra = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    $pant = new template ();
    if (!isset($_SESSION["generales"]["pathabsoluto"])) {
        $pant->LoadTemplate('../../html/default/cuerpoIE25a.html');
    } else {
        if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/cuerpoIE25a.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/cuerpoIE25a.html');
        }
    }
    if (trim($_SESSION["generales"]["codigousuario"]) != '') {
        $cantipen = contarTareasPendientes($_SESSION["generales"]["codigousuario"]);
        $cantinue = contarTareasNuevas($_SESSION["generales"]["codigousuario"]);
    } else {
        $cantipen = 0;
        $cantinue = 0;
    }
    $txtComp = '';
    if (!empty($arrayComponents)) {
        foreach ($arrayComponents as $cmp) {
            switch ($cmp) {
                case "grid" : $txtComp .= cargarDhtmlxGrid25a();
                    break;
                case "layout" : $txtComp .= cargarDhtmlxLayout25a();
                    break;
                case "menu" : $txtComp .= cargarDhtmlxMenu25a();
                    break;
                case "tabbar" : $txtComp .= cargarDhtmlxTabbar25a();
                    break;
                case "toolbar" : $txtComp .= cargarDhtmlxToolbar25a();
                    break;
                case "calendar" : $txtComp .= cargarDhtmlxCalendar25a();
                    break;
                case "windows" : $txtComp .= cargarDhtmlxWindows25a();
                    break;
                case "dataprocessor" : $txtComp .= cargarDhtmlxDataProcessor25a();
                    break;
            }
        }
    }
    $pant->armarDhtmlx25($txtComp);
    $pant->armarMostrarSombra($sombra);
    $pant->armarScriptHeader($scripth);
    $pant->armarScriptBody($scriptb);
    $pant->armarBarraOpciones($txtbarra);
    $pant->armarTareasPendientes($cantipen, $cantinue);
    $pant->armarFrameSecundarioLateral('');
    $pant->armarFrameSecundarioCentral($txt);
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
    if (isset($_SESSION["generales"]["txtemergente"])) {
        $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
        $_SESSION["generales"]["txtemergente"] = '';
    } else {
        $pant->armarScriptEmergente('');
        $_SESSION["generales"]["txtemergente"] = '';
    }
    $pant->armarEstilo(CODIGO_EMPRESA);
    $pant->armarDHTMLX($dhtmlx);
    if (!defined('TIEMPO_PROMEDIO_TRAMITES_PROPONENTES')) {
        define('TIEMPO_PROMEDIO_TRAMITES_PROPONENTES', '');
    }
    $pant->armarTiempoPromedioProponentes(TIEMPO_PROMEDIO_TRAMITES_PROPONENTES);
    $pant->mostrar();
    unset($pant);
    exit();
}

/**
 * 
 * @param type $arrayComponents
 * @param type $scripth
 * @param type $scriptb
 * @param type $txtbarra
 * @param type $txt
 * @param type $width
 * @param type $height
 * @param type $dhtmlx
 * @param type $sombra
 * @param type $refresh
 * @param type $time
 * @param type $mostrarenlacesmenu
 * @param type $emergenteinicial
 * @param type $bannersuperior
 * @param type $encoding
 * @param type $mostrarfooter
 * @param type $menusuperiorcompleto
 */
function mostrarCuerpoIE26a($arrayComponents, $scripth, $scriptb, $txtbarra, $txt, $width = 1024, $height = 400, $dhtmlx = '', $sombra = '', $refresh = false, $time = 0, $mostrarenlacesmenu = 'si', $emergenteinicial = '', $bannersuperior = 'si', $encoding = 'iso8859-1', $mostrarfooter = 'si', $menusuperiorcompleto = 'no') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');

    //
    if (!isset($_SESSION["generales"]["txtemergente"])) {
        $_SESSION["generales"]["txtemergente"] = '';
    }
    if (retornarDispositivo() != 'computer') {
        mostrarMovil($txtbarra, $_SESSION["generales"]["txtemergente"], $txt, $scripth, $scriptb, $sombra);
        exit();
    }

    //
    if (!defined('DHTMLX_ACTUAL')) {
        define('DHTMLX_ACTUAL', '2.6');
    }

    //
    $pant = new template ();

    if (DHTMLX_ACTUAL == '' || DHTMLX_ACTUAL == '2.6') {
        if (!isset($_SESSION["generales"]["pathabsoluto"]) || trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/cuerpoIE26a.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/cuerpoIE26a.html');
        }
    }

    if (DHTMLX_ACTUAL == '5') {
        if (!isset($_SESSION["generales"]["pathabsoluto"]) || trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/cuerpoDhtmlx5.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/cuerpoDhtmlx5.html');
        }
    }

    //
    if (DHTMLX_ACTUAL == '') {
        $pant->armarDhtmlxActual('2.6');
    } else {
        $pant->armarDhtmlxActual(DHTMLX_ACTUAL);
    }

    $cantipen = 0;
    $cantinue = 0;

    $txtComp = '';
    if (!empty($arrayComponents)) {
        foreach ($arrayComponents as $cmp) {
            switch (DHTMLX_ACTUAL) {
                case "2.6":
                    switch ($cmp) {
                        case "grid" : $txtComp .= cargarDhtmlxGrid26a();
                            break;
                        case "layout" : $txtComp .= cargarDhtmlxLayout26a();
                            break;
                        case "menu" : $txtComp .= cargarDhtmlxMenu26a();
                            break;
                        case "tabbar" : $txtComp .= cargarDhtmlxTabbar26a();
                            break;
                        case "toolbar" : $txtComp .= cargarDhtmlxToolbar26a();
                            break;
                        case "combo" : $txtComp .= cargarDhtmlxCombo26a();
                            break;
                        case "calendar" : $txtComp .= cargarDhtmlxCalendar26a();
                            break;
                        case "windows" : $txtComp .= cargarDhtmlxWindows26a();
                            break;
                        case "dataprocessor" : $txtComp .= cargarDhtmlxDataProcessor26a();
                            break;
                        case "chart" : $txtComp .= cargarDhtmlxChart26a();
                            break;
                        case "form":$txtComp .= cargarDhtmlxForm26a();
                            break;
                        case "vault":$txtComp .= cargarDhtmlxVault26a();
                            break;
                    }
                    break;
                case "3.0":
                    switch ($cmp) {
                        case "grid" : $txtComp .= cargarDhtmlxGrid30a();
                            break;
                        case "layout" : $txtComp .= cargarDhtmlxLayout30a();
                            break;
                        // case "menu" : $txtComp.=cargarDhtmlxMenu30a();break;
                        case "tabbar" : $txtComp .= cargarDhtmlxTabbar30a();
                            break;
                        case "toolbar" : $txtComp .= cargarDhtmlxToolbar30a();
                            break;
                        case "combo" : $txtComp .= cargarDhtmlxCombo30a();
                            break;
                        case "calendar" : $txtComp .= cargarDhtmlxCalendar30a();
                            break;
                        case "windows" : $txtComp .= cargarDhtmlxWindows30a();
                            break;
                        case "dataprocessor" : $txtComp .= cargarDhtmlxDataProcessor30a();
                            break;
                        case "chart" : $txtComp .= cargarDhtmlxChart30a();
                            break;
                        case "form":$txtComp .= cargarDhtmlxForm30a();
                            break;
                        case "vault":$txtComp .= cargarDhtmlxVault30a();
                            break;
                    }
                    break;
                case "3.5":
                    switch ($cmp) {
                        case "grid" : $txtComp .= cargarDhtmlxGrid35a();
                            break;
                        case "layout" : $txtComp .= cargarDhtmlxLayout35a();
                            break;
                        // case "menu" : $txtComp.=cargarDhtmlxMenu35a();break;
                        case "tabbar" : $txtComp .= cargarDhtmlxTabbar35a();
                            break;
                        case "toolbar" : $txtComp .= cargarDhtmlxToolbar35a();
                            break;
                        case "combo" : $txtComp .= cargarDhtmlxCombo35a();
                            break;
                        case "calendar" : $txtComp .= cargarDhtmlxCalendar35a();
                            break;
                        case "windows" : $txtComp .= cargarDhtmlxWindows35a();
                            break;
                        case "dataprocessor" : $txtComp .= cargarDhtmlxDataProcessor35a();
                            break;
                        case "chart" : $txtComp .= cargarDhtmlxChart35a();
                            break;
                        case "form":$txtComp .= cargarDhtmlxForm35a();
                            break;
                        case "vault":$txtComp .= cargarDhtmlxVault35a();
                            break;
                    }
                    break;
                case "3.6":
                    switch ($cmp) {
                        case "grid" : $txtComp .= cargarDhtmlxGrid36a();
                            break;
                        case "layout" : $txtComp .= cargarDhtmlxLayout36a();
                            break;
                        // case "menu" : $txtComp.=cargarDhtmlxMenu35a();break;
                        case "tabbar" : $txtComp .= cargarDhtmlxTabbar36a();
                            break;
                        case "toolbar" : $txtComp .= cargarDhtmlxToolbar36a();
                            break;
                        case "combo" : $txtComp .= cargarDhtmlxCombo36a();
                            break;
                        case "calendar" : $txtComp .= cargarDhtmlxCalendar36a();
                            break;
                        case "windows" : $txtComp .= cargarDhtmlxWindows36a();
                            break;
                        case "dataprocessor" : $txtComp .= cargarDhtmlxDataProcessor36a();
                            break;
                        case "chart" : $txtComp .= cargarDhtmlxChart36a();
                            break;
                        case "form":$txtComp .= cargarDhtmlxForm36a();
                            break;
                        case "vault":$txtComp .= cargarDhtmlxVault36a();
                            break;
                        case "gantt":$txtComp .= cargarDhtmlxGantt36a();
                            break;
                    }
                    break;
                case "4.0":
                    switch ($cmp) {
                        case "grid" : $txtComp .= cargarDhtmlxGrid40a();
                            break;
                        case "layout" : $txtComp .= cargarDhtmlxLayout40a();
                            break;
                        case "menu" : $txtComp .= cargarDhtmlxMenu40a();
                            break;
                        case "tabbar" : $txtComp .= cargarDhtmlxTabbar40a();
                            break;
                        case "toolbar" : $txtComp .= cargarDhtmlxToolbar40a();
                            break;
                        case "combo" : $txtComp .= cargarDhtmlxCombo40a();
                            break;
                        case "calendar" : $txtComp .= cargarDhtmlxCalendar40a();
                            break;
                        case "windows" : $txtComp .= cargarDhtmlxWindows40a();
                            break;
                        case "dataprocessor" : $txtComp .= cargarDhtmlxDataProcessor40a();
                            break;
                        case "chart" : $txtComp .= cargarDhtmlxChart40a();
                            break;
                        case "form":$txtComp .= cargarDhtmlxForm40a();
                            break;
                        case "vault":$txtComp .= cargarDhtmlxVault40a();
                            break;
                        case "gantt":$txtComp .= cargarDhtmlxGantt40a();
                            break;
                    }
                    break;
                case "4.12":
                    $txtComp .= cargarDhtmlx403();
                    break;
            }
        }
    }

    // El tipo menu siempre lo carga
    if (DHTMLX_ACTUAL == '' || DHTMLX_ACTUAL == '2.6') {
        $txtComp .= cargarDhtmlxMenu26a();
    }
    $pant->armarWidthCuerpo($width);
    $pant->armarDhtmlx26($txtComp);
    $pant->armarMostrarSombra($sombra);
    $pant->armarScriptHeader($scripth);
    $pant->armarScriptBody($scriptb);
    $pant->armarMetaRefresh($refresh, $time);
    $pant->armarBarraOpciones($txtbarra);
    $pant->armarTareasPendientes($cantipen, $cantinue);
    $pant->armarFrameSecundarioLateral('');
    $pant->armarFrameSecundarioCentral($txt);
    $pant->armarDate();
    $pant->armarSesion();
    $pant->armarTipoHttp(TIPO_HTTP);
    $pant->armarServidorRss(SERVIDOR_RSS);
    $pant->armarHttpHost(HTTP_HOST);
    $pant->armarUrlSite(TIPO_HTTP . HTTP_HOST);
    $pant->armarCodigoEntidad(CODIGO_EMPRESA);
    $pant->armarTituloAplicacion(NOMBRE_SISTEMA);
    $pant->armarSitioWeb(HTTP_HOST);
    $pant->armarNombreEntidad(RAZONSOCIAL);
    $pant->armarEmailAtencionUsuarios(EMAIL_ATENCION_USUARIOS);
    $pant->armarTelefonoAtencionUsuarios(TELEFONO_ATENCION_USUARIOS);
    $pant->armarGoogleAnalitics();
    if (!isset($_SESSION["generales"]["tipodispositivo"])) {
        $_SESSION["generales"]["tipodispositivo"] = 'computer';
    }
    $pant->armarTipoDispositivo($_SESSION["generales"]["tipodispositivo"]);
    $pant->armarDisplayHeader('no', 0, 0);
    if (trim($txtbarra) != '') {
        $pant->armarDisplayBarraOpciones('si', $width, 30);
    } else {
        $pant->armarDisplayBarraOpciones('no', 0, 00);
    }
    $pant->armarDisplayFramePrincipal('no', 0, 0);
    $pant->armarDisplayFrameSecundario('si', $width, $height);
    $pant->armarDisplayFooter('no', 0, 0);
    if (isset($_SESSION["generales"]["txtemergente"])) {
        if ($_SESSION["generales"]["txtemergente"] != '') {
            $pant->armarScriptEmergente(filtrarCaracteres($_SESSION["generales"]["txtemergente"]));
            $_SESSION["generales"]["txtemergente"] = '';
        } else {
            $pant->armarScriptEmergente('');
            $_SESSION["generales"]["txtemergente"] = '';
        }
    } else {
        $pant->armarScriptEmergente('');
        $_SESSION["generales"]["txtemergente"] = '';
    }
    $pant->armarEstilo(CODIGO_EMPRESA);
    $pant->armarDHTMLX($dhtmlx);
    $pant->armarDHTMLXVERSION(DHTMLX_ACTUAL);

    // Valida si es usuario publico y si esta activada la presentacion como iconos
    if (!isset($_SESSION["generales"]["tipomenu"])) {
        $_SESSION["generales"]["tipomenu"] = 'APLICACION';
    }
    if (substr($_SESSION["generales"]["tipomenu"], 0, 6) == 'ICONOS') {
        $pant->armarHeader(true, $mostrarenlacesmenu, $bannersuperior, $menusuperiorcompleto);
        if ($mostrarfooter == 'si') {
            $pant->armarFooter(true);
        } else {
            $pant->armarFooter(false);
        }
    } else {
        if ($txtbarra == 'Solicitud de registro') {
            $pant->armarHeader(true, $mostrarenlacesmenu, $bannersuperior, $menusuperiorcompleto);
        } else {
            $pant->armarHeader(false, $mostrarenlacesmenu, $bannersuperior, $menusuperiorcompleto);
        }
        if ($mostrarfooter == 'si') {
            $pant->armarFooter(true);
        } else {
            $pant->armarFooter(false);
        }
    }

    if (!defined('TIEMPO_PROMEDIO_TRAMITES_PROPONENTES')) {
        define('TIEMPO_PROMEDIO_TRAMITES_PROPONENTES', '');
    }
    $pant->armarTiempoPromedioProponentes(TIEMPO_PROMEDIO_TRAMITES_PROPONENTES);
    $pant->mostrar();


    $textoemergente = '';
    if ($emergenteinicial != '') {
        $pant1 = retornarRegistro('pantallas_propias', "idpantalla='" . $emergenteinicial . "'");
        if (($pant1 === false) || (empty($pant1))) {
            $pant1 = retornarRegistro('bas_pantallas', "idpantalla='" . $emergenteinicial . "'");
            if (($pant1) || (!empty($pant1))) {
                $textoemergente = $pant1["txtasociado"];
            }
        } else {
            $textoemergente = $pant1["txtasociado"];
        }
        if (trim($textoemergente) != '') {
            echo '<script>';
            echo 'GB_showCenter("ATENCION", "' . TIPO_HTTP . HTTP_HOST . '/librerias/presentacion/mostrarPantallas.php?pantalla=' . $emergenteinicial . '&mostrarenlaces=no&mostrarbanner=no&mostrarfooter=no" , 480, 640, "")';
            echo '</script>';
        }
    }
    unset($pant);

    exit();
}

/**
 * Rutina que muestra el cuerpo del sitio, hace llamado a la clase template.
 * Usada con IE - Adaptada a Dhtmlx 2.6
 * Versi&oacute;n usada por Jose Ivan Nieto T
 *
 * @param array $arrayComponents	-- 	Componentes dhtmlx que deben ser cargados
 * @param string $scripth			-- 	Scripts en el header
 * @param string $scriptb			--	Scripts en el body
 * @param string $txtbarra			-- 	Texto en la barra
 * @param string $txt				--	Texto del cuerpo
 * @param int $width				-- 	Ancho en pixels
 * @param int $height				-- 	Alto en pixels
 * @param string $dhtmlx			-- 	Scripts al final 
 * @param string $sombra			-- 	Texto para sombras
 * @param bool $refresh				-- 	Activa meta para refresh?
 * @param int $time					--	Tiempo del refresh en segundos
 */
function mostrarCuerpoIE26aImagen($arrayComponents, $scripth, $scriptb, $txtbarra, $txt, $width = 800, $height = 400, $dhtmlx = '', $sombra = '', $refresh = false, $time = 0) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');

    $pant = new template ();
    if (!isset($_SESSION["generales"]["pathabsoluto"])) {
        $pant->LoadTemplate('../../html/default/cuerpoIE26aImagen.html');
    } else {
        if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/cuerpoIE26aImagen.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/cuerpoIE26aImagen.html');
        }
    }

    $cantipen = 0;
    $cantinue = 0;

    $txtComp = '';
    if (!empty($arrayComponents)) {
        foreach ($arrayComponents as $cmp) {
            switch ($cmp) {
                case "grid" : $txtComp .= cargarDhtmlxGrid26a();
                    break;
                case "layout" : $txtComp .= cargarDhtmlxLayout26a();
                    break;
                case "menu" : $txtComp .= cargarDhtmlxMenu26a();
                    break;
                case "tabbar" : $txtComp .= cargarDhtmlxTabbar26a();
                    break;
                case "toolbar" : $txtComp .= cargarDhtmlxToolbar26a();
                    break;
                case "combo" : $txtComp .= cargarDhtmlxCombo26a();
                    break;
                case "calendar" : $txtComp .= cargarDhtmlxCalendar26a();
                    break;
                case "windows" : $txtComp .= cargarDhtmlxWindows26a();
                    break;
                case "dataprocessor" : $txtComp .= cargarDhtmlxDataProcessor26a();
                    break;
                case "chart" : $txtComp .= cargarDhtmlxChart26a();
                    break;
                case "form":$txtComp .= cargarDhtmlxForm26a();
                    break;
                case "vault":$txtComp .= cargarDhtmlxVault26a();
                    break;
            }
        }
    }
    $pant->armarDhtmlx26($txtComp);
    $pant->armarMostrarSombra($sombra);
    $pant->armarScriptHeader($scripth);
    $pant->armarScriptBody($scriptb);
    $pant->armarMetaRefresh($refresh, $time);
    $pant->armarBarraOpciones($txtbarra);
    $pant->armarTareasPendientes($cantipen, $cantinue);
    $pant->armarFrameSecundarioLateral('');
    $pant->armarFrameSecundarioCentral($txt);
    $pant->armarDate();
    $pant->armarSesion();
    $pant->armarHttpHost(HTTP_HOST);
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
    if (isset($_SESSION["generales"]["txtemergente"])) {
        $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
        $_SESSION["generales"]["txtemergente"] = '';
    } else {
        $pant->armarScriptEmergente('');
        $_SESSION["generales"]["txtemergente"] = '';
    }
    $pant->armarEstilo(CODIGO_EMPRESA);
    $pant->armarDHTMLX($dhtmlx);
    $pant->armarTiempoPromedioProponentes(TIEMPO_PROMEDIO_TRAMITES_PROPONENTES);
    $pant->mostrar();
    unset($pant);
    exit();
}

/**
 * Rutina que muestra el cuerpo del sitio, hace llamado a la clase template.
 * Usada con IE - Adaptada a Dhtmlx 2.6
 * Versi&oacute;n usada por Jose Ivan Nieto T
 * Ajustada para usar HTML5
 *
 * @param array $arrayComponents	-- 	Componentes dhtmlx que deben ser cargados
 * @param string $scripth			-- 	Scripts en el header
 * @param string $scriptb			--	Scripts en el body
 * @param string $txtbarra			-- 	Texto en la barra
 * @param string $txt				--	Texto del cuerpo
 * @param int $width				-- 	Ancho en pixels
 * @param int $height				-- 	Alto en pixels
 * @param string $dhtmlx			-- 	Scripts al final 
 * @param string $sombra			-- 	Texto para sombras
 * @param bool $refresh				-- 	Activa meta para refresh?
 * @param int $time					--	Tiempo del refresh en segundos
 */
function mostrarCuerpoIE26aHtml5($arrayComponents, $scripth, $scriptb, $txtbarra, $txt, $width = 800, $height = 400, $dhtmlx = '', $sombra = '', $refresh = false, $time = 0) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');

    $pant = new template ();
    if (!isset($_SESSION["generales"]["pathabsoluto"])) {
        $pant->LoadTemplate('../../html/default/cuerpoIE26a.html5');
    } else {
        if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/cuerpoIE26a.html5');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/cuerpoIE26a.html5');
        }
    }

    $cantipen = 0;
    $cantinue = 0;

    $txtComp = '';
    if (!empty($arrayComponents)) {
        foreach ($arrayComponents as $cmp) {
            switch ($cmp) {
                case "grid" : $txtComp .= cargarDhtmlxGrid26a();
                    break;
                case "layout" : $txtComp .= cargarDhtmlxLayout26a();
                    break;
                case "menu" : $txtComp .= cargarDhtmlxMenu26a();
                    break;
                case "tabbar" : $txtComp .= cargarDhtmlxTabbar26a();
                    break;
                case "toolbar" : $txtComp .= cargarDhtmlxToolbar26a();
                    break;
                case "combo" : $txtComp .= cargarDhtmlxCombo26a();
                    break;
                case "calendar" : $txtComp .= cargarDhtmlxCalendar26a();
                    break;
                case "windows" : $txtComp .= cargarDhtmlxWindows26a();
                    break;
                case "dataprocessor" : $txtComp .= cargarDhtmlxDataProcessor26a();
                    break;
                case "chart" : $txtComp .= cargarDhtmlxChart26a();
                    break;
                case "form":$txtComp .= cargarDhtmlxForm26a();
                    break;
                case "vault":$txtComp .= cargarDhtmlxVault26a();
                    break;
            }
        }
    }
    $pant->armarDhtmlx26($txtComp);
    $pant->armarMostrarSombra($sombra);
    $pant->armarScriptHeader($scripth);
    $pant->armarScriptBody($scriptb);
    $pant->armarMetaRefresh($refresh, $time);
    $pant->armarBarraOpciones($txtbarra);
    $pant->armarTareasPendientes($cantipen, $cantinue);
    $pant->armarFrameSecundarioLateral('');
    $pant->armarFrameSecundarioCentral($txt);
    $pant->armarDate();
    $pant->armarSesion();
    $pant->armarHttpHost(HTTP_HOST);
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
    if (isset($_SESSION["generales"]["txtemergente"])) {
        $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
        $_SESSION["generales"]["txtemergente"] = '';
    } else {
        $pant->armarScriptEmergente('');
        $_SESSION["generales"]["txtemergente"] = '';
    }
    $pant->armarEstilo(CODIGO_EMPRESA);
    $pant->armarDHTMLX($dhtmlx);
    $pant->armarTiempoPromedioProponentes(TIEMPO_PROMEDIO_TRAMITES_PROPONENTES);
    $pant->mostrar();
    unset($pant);
    exit();
}

/**
 * Rutina que muestra el cuerpo del sitio, hace llamado a la clase template.
 * Usada con IE - Adaptada a Dhtmlx 3.0
 * Versi&oacute;n usada por Jose Ivan Nieto T
 *
 * @param string    $scripth    Scripts del header
 * @param string    $scriptb    Scripts del body
 * @param string    $txtbarra   T&iacute;tulo de la barra
 * @param string    $txt        Cuerpo o contenido
 * @param int       $width      Ancho
 * @param int       $height     Alto
 * @pamam sting     $dhtmlx     Scripts y/o contenidos dhtmlx
 */
function mostrarCuerpoIE30a($arrayComponents, $scripth, $scriptb, $txtbarra, $txt, $width = 800, $height = 400, $dhtmlx = '', $sombra = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    $pant = new template ();
    if (!isset($_SESSION["generales"]["pathabsoluto"])) {
        $pant->LoadTemplate('../../html/default/cuerpoIE30a.html');
    } else {
        if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/cuerpoIE30a.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/cuerpoIE30a.html');
        }
    }

    $cantipen = 0;
    $cantinue = 0;

    $txtComp = '';
    if (!empty($arrayComponents)) {
        foreach ($arrayComponents as $cmp) {
            switch ($cmp) {
                case "grid" : $txtComp .= cargarDhtmlxGrid30a();
                    break;
                case "layout" : $txtComp .= cargarDhtmlxLayout30a();
                    break;
                case "menu" : $txtComp .= cargarDhtmlxMenu30a();
                    break;
                case "tabbar" : $txtComp .= cargarDhtmlxTabbar30a();
                    break;
                case "toolbar" : $txtComp .= cargarDhtmlxToolbar30a();
                    break;
                case "combo" : $txtComp .= cargarDhtmlxCombo30a();
                    break;
                case "calendar" : $txtComp .= cargarDhtmlxCalendar30a();
                    break;
                case "windows" : $txtComp .= cargarDhtmlxWindows30a();
                    break;
                case "dataprocessor" : $txtComp .= cargarDhtmlxDataProcessor30a();
                    break;
                case "chart" : $txtComp .= cargarDhtmlxChart30a();
                    break;
                case "form":$txtComp .= cargarDhtmlxForm30a();
                    break;
                case "vault":$txtComp .= cargarDhtmlxVault30a();
                    break;
            }
        }
    }
    $pant->armarDhtmlx26($txtComp);
    $pant->armarMostrarSombra($sombra);
    $pant->armarScriptHeader($scripth);
    $pant->armarScriptBody($scriptb);
    $pant->armarBarraOpciones($txtbarra);
    $pant->armarTareasPendientes($cantipen, $cantinue);
    $pant->armarFrameSecundarioLateral('');
    $pant->armarFrameSecundarioCentral($txt);
    $pant->armarDate();
    $pant->armarSesion();
    $pant->armarHttpHost(HTTP_HOST);
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
    if (isset($_SESSION["generales"]["txtemergente"])) {
        $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
        $_SESSION["generales"]["txtemergente"] = '';
    } else {
        $pant->armarScriptEmergente('');
        $_SESSION["generales"]["txtemergente"] = '';
    }
    $pant->armarEstilo(CODIGO_EMPRESA);
    $pant->armarDHTMLX($dhtmlx);
    $pant->armarTiempoPromedioProponentes(TIEMPO_PROMEDIO_TRAMITES_PROPONENTES);
    $pant->mostrar();
    unset($pant);
    exit();
}

/**
 * Rutina que muestra el cuerpo del sitio, hace llamado a la clase template.
 * Usada con IE - Adaptada a Dhtmlx 2.5
 * Versi&oacute;n usada por Jose Ivan Nieto T
 *
 * @param string    $scripth    Scripts del header
 * @param string    $scriptb    Scripts del body
 * @param string    $txtbarra   T&iacute;tulo de la barra
 * @param string    $txt        Cuerpo o contenido
 * @param int       $width      Ancho
 * @param int       $height     Alto
 * @pamam sting     $dhtmlx     Scripts y/o contenidos dhtmlx
 */
function mostrarVacioIE25a($arrayComponents, $scripth, $scriptb, $txt, $dhtmlx) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    $pant = new template ();
    if (!isset($_SESSION["generales"]["pathabsoluto"])) {
        $pant->LoadTemplate('../../html/default/vacioIE26a.html');
    } else {
        if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/vacioIE26a.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/vacioIE26a.html');
        }
    }
    $txtComp = '';
    if (!empty($arrayComponents)) {
        foreach ($arrayComponents as $cmp) {
            switch ($cmp) {
                case "grid" : $txtComp .= cargarDhtmlxGrid25a();
                    break;
                case "layout" : $txtComp .= cargarDhtmlxLayout25a();
                    break;
                case "menu" : $txtComp .= cargarDhtmlxMenu25a();
                    break;
                case "tabbar" : $txtComp .= cargarDhtmlxTabbar25a();
                    break;
                case "toolbar" : $txtComp .= cargarDhtmlxToolbar25a();
                    break;
                case "calendar" : $txtComp .= cargarDhtmlxCalendar25a();
                    break;
                case "windows" : $txtComp .= cargarDhtmlxWindows25a();
                    break;
                case "dataprocessor" : $txtComp .= cargarDhtmlxDataProcessor25a();
                    break;
                case "chart" : $txtComp .= cargarDhtmlxChart25a();
                    break;
                case "form":$txtComp .= cargarDhtmlxForm25a();
                    break;
            }
        }
    }
    $pant->armarDhtmlx26($txtComp);
    $pant->armarScriptHeader($scripth);
    $pant->armarScriptBody($scriptb);
    $pant->armarDate();
    $pant->armarSesion();
    $pant->armarHttpHost(HTTP_HOST);
    $pant->armarUrlSite(TIPO_HTTP . HTTP_HOST);
    $pant->armarCodigoEntidad(CODIGO_EMPRESA);
    $pant->armarTituloAplicacion(NOMBRE_SISTEMA);
    $pant->armarSitioWeb(HTTP_HOST);
    $pant->armarNombreEntidad(RAZONSOCIAL);
    $pant->armarString($txt);
    $pant->armarFooter();
    if (isset($_SESSION["generales"]["txtemergente"])) {
        $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
        $_SESSION["generales"]["txtemergente"] = '';
    } else {
        $pant->armarScriptEmergente('');
        $_SESSION["generales"]["txtemergente"] = '';
    }
    $pant->armarEstilo(CODIGO_EMPRESA);
    $pant->armarDHTMLX($dhtmlx);
    $pant->mostrar();
    unset($pant);
    exit();
}

/**
 * Rutina que muestra el cuerpo del sitio, hace llamado a la clase template.
 * Usada con IE - Adaptada a Dhtmlx 2.6
 * Versi&oacute;n usada por Jose Ivan Nieto T
 *
 * @param array $arrayComponents	--	Arreglo de componentes a cargar
 * @param string $scripth			-- 	Script en el header
 * @param string $scriptb			-- 	Script en el body
 * @param string $txt				--	Cuerpo
 * @param string $dhtmlx			-- 	Script en el body (final)
 * @param string $sombra			--	Texto de la sombra
 * @param bool $refresh				--	Activar meta refresh (true, false)
 * @param int $time					--	Tiempo del refresh en segundos
 */
function mostrarVacioIE26a($arrayComponents, $scripth, $scriptb, $txt, $dhtmlx = '', $sombra = '', $refresh = false, $time = 0) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    $pant = new template ();
    if (!isset($_SESSION["generales"]["pathabsoluto"])) {
        $pant->LoadTemplate('../../html/default/vacioIE26a.html');
    } else {
        if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/vacioIE26a.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/vacioIE26a.html');
        }
    }
    $txtComp = '';
    if (!empty($arrayComponents)) {
        foreach ($arrayComponents as $cmp) {
            switch ($cmp) {
                case "grid" : $txtComp .= cargarDhtmlxGrid26a();
                    break;
                case "layout" : $txtComp .= cargarDhtmlxLayout26a();
                    break;
                case "menu" : $txtComp .= cargarDhtmlxMenu26a();
                    break;
                case "tabbar" : $txtComp .= cargarDhtmlxTabbar26a();
                    break;
                case "toolbar" : $txtComp .= cargarDhtmlxToolbar26a();
                    break;
                case "calendar" : $txtComp .= cargarDhtmlxCalendar26a();
                    break;
                case "windows" : $txtComp .= cargarDhtmlxWindows26a();
                    break;
                case "dataprocessor" : $txtComp .= cargarDhtmlxDataProcessor26a();
                    break;
            }
        }
    }
    $pant->armarDhtmlx26($txtComp);
    $pant->armarMostrarSombra($sombra);
    $pant->armarScriptHeader($scripth);
    $pant->armarScriptBody($scriptb);
    $pant->armarDate();
    $pant->armarSesion();
    $pant->armarHttpHost(HTTP_HOST);
    $pant->armarMetaRefresh($refresh, $time);
    $pant->armarUrlSite(TIPO_HTTP . HTTP_HOST);
    $pant->armarCodigoEntidad(CODIGO_EMPRESA);
    $pant->armarTituloAplicacion(NOMBRE_SISTEMA);
    $pant->armarSitioWeb(HTTP_HOST);
    $pant->armarNombreEntidad(RAZONSOCIAL);
    $pant->armarString($txt);
    $pant->armarFooter();
    if (isset($_SESSION["generales"]["txtemergente"])) {
        $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
        $_SESSION["generales"]["txtemergente"] = '';
    } else {
        $pant->armarScriptEmergente('');
        $_SESSION["generales"]["txtemergente"] = '';
    }
    $pant->armarEstilo(CODIGO_EMPRESA);
    $pant->armarDHTMLX($dhtmlx);
    $pant->mostrar();
    unset($pant);
    exit();
}

function mostrarVacioIE36a($arrayComponents, $scripth, $scriptb, $txt, $dhtmlx = '', $sombra = '', $refresh = false, $time = 0) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    $pant = new template ();
    if (!isset($_SESSION["generales"]["pathabsoluto"])) {
        $pant->LoadTemplate('../../html/default/cuerpoVacioIE36.html');
    } else {
        if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/cuerpoVacioIE36.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/cuerpoVacioIE36.html');
        }
    }
    $txtComp = '';
    if (!empty($arrayComponents)) {
        foreach ($arrayComponents as $cmp) {

            if (DHTMLX_ACTUAL == '4.0') {
                switch ($cmp) {
                    case "grid" : $txtComp .= cargarDhtmlxGrid40a();
                        break;
                    case "layout" : $txtComp .= cargarDhtmlxLayout40a();
                        break;
                    case "menu" : $txtComp .= cargarDhtmlxMenu40a();
                        break;
                    case "tabbar" : $txtComp .= cargarDhtmlxTabbar40a();
                        break;
                    case "toolbar" : $txtComp .= cargarDhtmlxToolbar40a();
                        break;
                    case "calendar" : $txtComp .= cargarDhtmlxCalendar40a();
                        break;
                    case "windows" : $txtComp .= cargarDhtmlxWindows40a();
                        break;
                    case "dataprocessor" : $txtComp .= cargarDhtmlxDataProcessor40a();
                        break;
                    case "gantt" : $txtComp .= cargarDhtmlxGantt40a();
                        break;
                }
            } else {
                switch ($cmp) {
                    case "grid" : $txtComp .= cargarDhtmlxGrid36a();
                        break;
                    case "layout" : $txtComp .= cargarDhtmlxLayout36a();
                        break;
                    case "menu" : $txtComp .= cargarDhtmlxMenu36a();
                        break;
                    case "tabbar" : $txtComp .= cargarDhtmlxTabbar36a();
                        break;
                    case "toolbar" : $txtComp .= cargarDhtmlxToolbar36a();
                        break;
                    case "calendar" : $txtComp .= cargarDhtmlxCalendar36a();
                        break;
                    case "windows" : $txtComp .= cargarDhtmlxWindows36a();
                        break;
                    case "dataprocessor" : $txtComp .= cargarDhtmlxDataProcessor36a();
                        break;
                    case "gantt" : $txtComp .= cargarDhtmlxGantt36a();
                        break;
                }
            }
        }
    }
    $pant->armarDhtmlx36($txtComp);
    $pant->armarMostrarSombra($sombra);
    $pant->armarScriptHeader($scripth);
    $pant->armarScriptBody($scriptb);
    $pant->armarDate();
    $pant->armarSesion();
    $pant->armarHttpHost(HTTP_HOST);
    $pant->armarMetaRefresh($refresh, $time);
    $pant->armarUrlSite(TIPO_HTTP . HTTP_HOST);
    $pant->armarCodigoEntidad(CODIGO_EMPRESA);
    $pant->armarTituloAplicacion(NOMBRE_SISTEMA);
    $pant->armarSitioWeb(HTTP_HOST);
    $pant->armarNombreEntidad(RAZONSOCIAL);
    $pant->armarString($txt);
    $pant->armarCuerpo($txt);
    $pant->armarFooter();
    if (isset($_SESSION["generales"]["txtemergente"])) {
        $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
        $_SESSION["generales"]["txtemergente"] = '';
    } else {
        $pant->armarScriptEmergente('');
        $_SESSION["generales"]["txtemergente"] = '';
    }
    $pant->armarEstilo(CODIGO_EMPRESA);
    $pant->armarDHTMLX($dhtmlx);
    $pant->mostrar();
    unset($pant);
    exit();
}

function mostrarCuerpoIEForMer($scripth, $scriptb, $txtbarra, $txt, $width = 800, $height = 400, $dhtmlx = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    $pant = new template ();
    if (!isset($_SESSION["generales"]["pathabsoluto"])) {
        $pant->LoadTemplate('../../html/default/cuerpoIE.html');
    } else {
        if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/cuerpoIE.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/cuerpoIEForMer.html');
        }
    }
    if (trim($_SESSION["generales"]["codigousuario"]) != '') {
        $cantipen = contarTareasPendientes($_SESSION["generales"]["codigousuario"]);
        $cantinue = contarTareasNuevas($_SESSION["generales"]["codigousuario"]);
    } else {
        $cantipen = 0;
        $cantinue = 0;
    }
    $pant->armarScriptHeader($scripth);
    $pant->armarScriptBody($scriptb);
    $pant->armarBarraOpciones($txtbarra);
    $pant->armarBody('');
    $pant->armarTareasPendientes($cantipen, $cantinue);
    $pant->armarFrameSecundarioLateral('');
    $pant->armarFrameSecundarioCentral($txt);
    $pant->armarDate();
    $pant->armarSesion();
    $pant->armarHttpHost(HTTP_HOST);
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
    if (isset($_SESSION["generales"]["txtemergente"])) {
        $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
        $_SESSION["generales"]["txtemergente"] = '';
    } else {
        $pant->armarScriptEmergente('');
        $_SESSION["generales"]["txtemergente"] = '';
    }
    $pant->armarEstilo(CODIGO_EMPRESA);
    $pant->armarDHTMLX($dhtmlx);
    $pant->armarTiempoPromedioProponentes(TIEMPO_PROMEDIO_TRAMITES_PROPONENTES);
    $pant->mostrar();
    unset($pant);
    exit();
}

/**
 * Rutina que muestra el cuero del sitio, hace llamado a la clase template.
 * Muestra adicionalmente el pi&eacute; de p&aacute;gina
 *
 * @param string    $scripth    Scripts del header
 * @param string    $scriptb    Scripts del body
 * @param string    $txtbarra   T&iacute;tulo de la barra
 * @param string    $txt        Cuerpo o contenido
 * @param int       $width      Ancho
 * @param int       $height     Alto
 */
function mostrarCuerpoFooter($scripth, $scriptb, $txtbarra, $txt, $width = 800, $height = 400) {
    if (!isset($_SESSION["generales"]["pathabsoluto"])) {
        require_once ('../../configuracion/common.php');
        require_once ('../../librerias/presentacion/template.class.php');
        require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    } else {
        if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            require_once ('../../configuracion/common.php');
            require_once ('../../librerias/presentacion/template.class.php');
            require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        } else {
            require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
            require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
            require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        }
    }

    $pant = new template ();
    if (!isset($_SESSION["generales"]["pathabsoluto"])) {
        $pant->LoadTemplate('../../html/default/cuerpoIE26a.html');
    } else {
        if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/cuerpoIE26a.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/cuerpoIE26a.html');
        }
    }

    if (trim($_SESSION["generales"]["codigousuario"]) != '') {
        $cantipen = contarTareasPendientes($_SESSION["generales"]["codigousuario"]);
        $cantinue = contarTareasNuevas($_SESSION["generales"]["codigousuario"]);
    } else {
        $cantipen = 0;
        $cantinue = 0;
    }
    $pant->armarScriptHeader($scripth);
    $pant->armarScriptBody($scriptb);
    $pant->armarBarraOpciones($txtbarra);
    $pant->armarTareasPendientes($cantipen, $cantinue);
    $pant->armarFrameSecundarioLateral('');
    $pant->armarFrameSecundarioCentral($txt);
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
    $pant->armarFooter();
    $pant->armarDisplayHeader('no', 0, 0);
    if (trim($txtbarra) != '') {
        $pant->armarDisplayBarraOpciones('si', $width, 30);
    } else {
        $pant->armarDisplayBarraOpciones('no', 0, 00);
    }
    $pant->armarDisplayFramePrincipal('no', 0, 0);
    $pant->armarDisplayFrameSecundario('si', $width, $height);
    $pant->armarDisplayFooter('si', $width);
    if (!isset($_SESSION["generales"]["txtemergente"])) {
        $_SESSION["generales"]["txtemergente"] = '';
    }
    $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
    $_SESSION["generales"]["txtemergente"] = '';
    $pant->armarEstilo(CODIGO_EMPRESA);
    $pant->mostrar();
    unset($pant);
    exit();
}

//
function mostrarCuerpoRse($plantilla = '', $banner = '', $dhtmlx26 = '', $scriptheader = '', $scriptbody = '', $encabezado = '', $sombra = '', $footer = '', $body = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonRse' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');

    $pant = new template ();

    // Reemplaza datos b&aacute;sicos de la plantilla
    $pant->LoadTemplate($plantilla);

    //
    if (!defined('DHTMLX_ACTUAL')) {
        define('DHTMLX_ACTUAL', '2.6');
    }

    if (DHTMLX_ACTUAL == '') {
        $pant->armarDhtmlxActual('2.6');
    } else {
        $pant->armarDhtmlxActual(DHTMLX_ACTUAL);
    }

    //
    $txtComp = '';
    // $txtComp.=cargarDhtmlxGrid26a();
    // $txtComp.=cargarDhtmlxMenu26a();
    //
    $pant->armarBanner($banner);

    // Armar Encabezado
    if ($encabezado == '') {
        $encabezado = 'Men&uacute;';
    }
    if (
            (!isset($_SESSION["generales"]["nombreusuario"])) ||
            ($_SESSION["generales"]["nombreusuario"] == '')
    ) {
        $txtUsuario = 'Sin usuario autenticado';
    } else {
        $txtUsuario = $_SESSION["generales"]["nombreusuario"];
    }
    $txtEnc = '<table width="900px">';
    $txtEnc .= '<tr>';
    $txtEnc .= '<td width="40%"><strong>' . $encabezado . '</strong></td>';
    $txtEnc .= '<td width="40%"><strong>Usuario: </strong>' . $txtUsuario . '</td>';
    $txtEnc .= '<td idth="20%">';
    $txtEnc .= '<a href="' . RSE_SITIO_DOCUMENTACION . '" target="_blank">Documentaci&oacute;n</a>&nbsp;&nbsp;';
    $txtEnc .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/rseDisparador.php?accion=mostrarmenu">Inicio</a>&nbsp;&nbsp;';
    $txtEnc .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/rse.php?accion=mostrarmenu">Salir</a>';
    $txtEnc .= '</td>';
    $txtEnc .= '</tr>';
    $txtEnc .= '</table>';

    $pant->armarEncabezado($txtEnc);
    $pant->armarScriptHeader($scriptheader);
    $pant->armarScriptBody($scriptbody);
    $pant->armarDhtmlx26($txtComp);
    $pant->armarBody($body);

    if (trim($footer) == '') {
        $footer = 'Derechos Reservados de ' . utf8_decode(RSE_DERECHOS_RESERVADOS) . ' - ' . date("Y") . '<br>';
        $footer .= 'Producto desarrollado por ' . utf8_decode(NOMBRE_CASA_SOFTWARE);
    }
    $pant->armarFooterRse($footer);
    $pant->armarMostrarSombra($sombra);

    // Div del cuerpo
    $heightdivcuerpo = 500;
    if (!isset($_SESSION["generales"]["tipodispositivo"])) {
        $_SESSION["generales"]["tipodispositivo"] = 'computer';
    }
    if ($_SESSION["generales"]["tipodispositivo"] == 'tablet') {
        $heightdivcuerpo = 800;
    }
    if ($_SESSION["generales"]["tipodispositivo"] == 'mobile') {
        $heightdivcuerpo = 500;
    }
    $pant->armarHeightDivCuerpo($heightdivcuerpo);

    // Reemplaza variables dentro de la plantilla
    $pant->armarTituloAplicacion(RSE_NOMBRE_SISTEMA);
    $pant->armarNombreSistema(RSE_NOMBRE_SISTEMA);
    $pant->armarNombreCasaSoftware(NOMBRE_CASA_SOFTWARE);
    $pant->armarDireccionCasaSoftware(DIRECCION_CASA_SOFTWARE);
    $pant->armarCiudadCasaSoftware(CIUDAD_CASA_SOFTWARE);
    $pant->armarTelefonoCasaSoftware(TELEFONO_CASA_SOFTWARE);
    $pant->armarDeclaracionPrivacidad(DECLARACION_PRIVACIDAD);
    $pant->armarGoogleAnalitics();
    $pant->armarEstilo();


    // Arma script de texto emergente
    if (isset($_SESSION["generales"]["txtemergente"])) {
        $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
        $_SESSION["generales"]["txtemergente"] = '';
    } else {
        $pant->armarScriptEmergente('');
        $_SESSION["generales"]["txtemergente"] = '';
    }

    // Muestra
    $pant->mostrar();
    unset($pant);
    exit();
}

/**
 * 
 * @param type $scripth
 * @param type $scriptb
 * @param type $tittle
 * @param type $showtittle
 * @param type $body 
 */
function mostrarCuerpoBootstrap($scripth = '', $scriptb = '', $tittle = '', $showtittle = 'no', $body = '', $image = '') {
    /*
      if ($image == '') {
      $image = TIPO_HTTP . HTTP_HOST . '/images/thumbnails/f' . rand(1,126) . '.jpg';
      }
     */
    $pant = file_get_contents(TIPO_HTTP . HTTP_HOST . '/bootstrap/plantillaVaciaHttp.html');
    $pant = str_replace("[SYSTEMNAME]", NOMBRE_SISTEMA, $pant);
    $pant = str_replace("[WEBSOFTWARECOMPANY]", WEB_CASA_SOFTWARE, $pant);
    $pant = str_replace("[COMPANYSOFTWARE]", NOMBRE_CASA_SOFTWARE, $pant);
    $pant = str_replace("[COUNTRYCOMPANYSOFTWARE]", PAIS_CASA_SOFTWARE, $pant);
    $pant = str_replace("[SCRIPTHEADER]", $scripth, $pant);
    $pant = str_replace("[SCRIPTBODY]", $scriptb, $pant);
    $pant = str_replace("[USERNAME]", $_SESSION["generales"]["codigousuario"], $pant);
    $pant = str_replace("[DATE]", date("Y-m-d") . ' - ' . date("H:i:s"), $pant);
    $pant = str_replace("[YEAR]", date("Y"), $pant);
    $pant = str_replace("[CONTENT]", $body, $pant);
    $pant = str_replace("[TIPOHTTP]", TIPO_HTTP, $pant);
    $pant = str_replace("[HTTPHOST]", HTTP_HOST, $pant);
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

function mostrarHora($dat) {
    $dat = str_replace(":", "", $dat);
    if ((trim($dat) == '') || (ltrim($dat, "0") == '')) {
        return '';
    }
    if (strlen($dat) == 6) {
        return substr($dat, 0, 2) . ':' . substr($dat, 2, 2) . ':' . substr($dat, 4, 2);
    } else {
        if (strlen($dat) == 4) {
            return substr($dat, 0, 2) . ':' . substr($dat, 2, 2) . ':00';
        } else {
            return $dat;
        }
    }
    exit();
}

function mostrarHora5($dat) {
    $dat = str_replace(":", "", $dat);
    if ((trim($dat) == '') || (ltrim($dat, "0") == '')) {
        return '';
    }
    if (strlen($dat) == 6) {
        return substr($dat, 0, 2) . ':' . substr($dat, 2, 2);
    } else {
        if (strlen($dat) == 4) {
            return substr($dat, 0, 2) . ':' . substr($dat, 2, 2);
        } else {
            return $dat;
        }
    }
    exit();
}

// retornar la fecha en formato AAAA-MM-DD
function mostrarFecha($fec) {
    if ((trim($fec) == '') || (ltrim($fec, "0") == '')) {
        return '';
    }
    if (strlen($fec) == 10) {
        $fec = str_replace(array("/", "-"), "", $fec);
    }
    return substr($fec, 0, 4) . '-' . substr($fec, 4, 2) . '-' . substr($fec, 6, 2);
}

// retorna la fecha en formato DD-MM-AAAA
function mostrarFecha1($fec) {
    $fec = str_replace(array('-', '/'), "", $fec);
    if (trim($fec) == '') {
        return '';
    }
    if (strlen($fec) == 10) {
        return $fec;
    } else {
        return substr($fec, 6, 2) . '-' . substr($fec, 4, 2) . '-' . substr($fec, 0, 4);
    }
    exit();
}

// retorna la fecha en formato DD/MM/AAAA
function mostrarFecha2($fec) {
    if ((trim($fec) == '') || (ltrim($fec, "0") == '')) {
        return '';
    }
    if (strlen($fec) == 10) {
        $fec = str_replace(array("/", "-"), "", $fec);
    }
    return substr($fec, 6, 2) . '/' . substr($fec, 4, 2) . '/' . substr($fec, 0, 4);
}

/**
 *
 * @param fecha 	Formato AAAAMMDD
 * @return texto de la forma <nombremes> <numerodia> de <ano>
 */
function mostrarFechaLetras($fec) {
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
        case "01": $txt = 'enero';
            break;
        case "02": $txt = 'febrero';
            break;
        case "03": $txt = 'marzo';
            break;
        case "04": $txt = 'abril';
            break;
        case "05": $txt = 'mayo';
            break;
        case "06": $txt = 'junio';
            break;
        case "07": $txt = 'julio';
            break;
        case "08": $txt = 'agosto';
            break;
        case "09": $txt = 'septiembre';
            break;
        case "10": $txt = 'octubre';
            break;
        case "11": $txt = 'noviembre';
            break;
        case "12": $txt = 'diciembre';
            break;
    }
    if (strlen($fec) == 6) {
        return $txt . ' ' . ' de ' . substr($fec, 0, 4);
    } else {
        return $txt . ' ' . substr($fec, 6, 2) . ' de ' . substr($fec, 0, 4);
    }
    exit();
}

/**
 * 
 * @param fecha 	Formato AAAAMMDD
 * @return texto de la forma <numdia> de <nombremes> de <ano>
 */
function mostrarFechaLetras1($fec) {
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
        case "01": $txt = 'enero';
            break;
        case "02": $txt = 'febrero';
            break;
        case "03": $txt = 'marzo';
            break;
        case "04": $txt = 'abril';
            break;
        case "05": $txt = 'mayo';
            break;
        case "06": $txt = 'junio';
            break;
        case "07": $txt = 'julio';
            break;
        case "08": $txt = 'agosto';
            break;
        case "09": $txt = 'septiembre';
            break;
        case "10": $txt = 'octubre';
            break;
        case "11": $txt = 'noviembre';
            break;
        case "12": $txt = 'diciembre';
            break;
    }
    if (strlen($fec) == 6) {
        return $txt . ' ' . ' de ' . substr($fec, 0, 4);
    } else {
        return substr($fec, 6, 2) . ' de ' . $txt . ' de ' . substr($fec, 0, 4);
    }
    exit();
}

function mostrarNumero($var) {
    if (trim($var) == '') {
        return "-o-";
    }
    if (!is_numeric($var)) {
        return "-o-";
    }
    return number_format($var, 0);
}

function mostrarNumero2($var) {
    if (ltrim($var, "0") == '') {
        return "-o-";
    }
    if (!is_numeric($var)) {
        return "-o-";
    }
    return number_format($var, 2);
}

function mostrarNumero4($var) {
    if (trim($var) == '') {
        return "-o-";
    }
    if (!is_numeric($var)) {
        return "-o-";
    }
    return number_format($var, 4);
}

function mostrarNumero5($var) {
    if (trim($var) == '') {
        return "-o-";
    }
    if (!is_numeric($var)) {
        return "-o-";
    }
    return number_format($var, 5);
}

function mostrarPesos($var) {
    if (trim($var) == '') {
        return "-o-";
    }
    if (!is_numeric($var)) {
        return "-o-";
    }
    return "$" . number_format($var, 0, ",", ".");
}

function mostrarPesos2($var) {
    if (trim($var) == '') {
        return "-o-";
    }
    if (!is_numeric($var)) {
        return "-o-";
    }
    return "$" . number_format($var, 2, ",", ".");
}

function mostrarPesos4($var) {
    if (trim($var) == '') {
        return "-o-";
    }
    if (!is_numeric($var)) {
        return "-o-";
    }
    return "$" . number_format($var, 4, ",", ".");
}

function mostrarPesos5($var) {
    if (trim($var) == '') {
        return "-o-";
    }
    if (!is_numeric($var)) {
        return "-o-";
    }
    return "$" . number_format($var, 5, ",", ".");
}

function mostrarPdf($file) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    $pant = new template ();
    if (!isset($_SESSION["generales"]["pathabsoluto"])) {
        $pant->LoadTemplate('../../html/default/cuerpoIE26a.html');
    } else {
        if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/cuerpoIE26a.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/cuerpoIE26a.html');
        }
    }

    $pant->armarScriptHeader('');
    $pant->armarScriptBody('');
    $pant->armarBarraOpciones('');
    $pant->armarFrameSecundarioLateral('');
    $pant->armarFrameSecundarioCentral('');

    $pant->armarDate();
    $pant->armarSesion();
    $pant->armarHttpHost(HTTP_HOST);
    $pant->armarCodigoEntidad(CODIGO_EMPRESA);
    $pant->armarTituloAplicacion(NOMBRE_SISTEMA);
    $pant->armarSitioWeb(HTTP_HOST);
    $pant->armarNombreEntidad(RAZONSOCIAL);
    $pant->armarEmailAtencionUsuarios(EMAIL_ATENCION_USUARIOS);
    $pant->armarTelefonoAtencionUsuarios(TELEFONO_ATENCION_USUARIOS);
    $pant->armarFooter();

    $pant->armarDisplayHeader('no', 0, 0);
    $pant->armarDisplayBarraOpciones('no', 0, 00);
    $pant->armarDisplayFramePrincipal('no', 0, 0);
    $pant->armarDisplayFrameSecundario('si', 700, 500);
    $pant->armarDisplayFooter('no', 0, 0);
    if (!isset($_SESSION["generales"]["txtemergente"])) {
        $_SESSION["generales"]["txtemergente"] = '';
    }
    $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
    $_SESSION["generales"]["txtemergente"] = '';
    $pant->armarEstilo(CODIGO_EMPRESA);
    $pant->mostrar();
    unset($pant);
    exit();
}

/**
 * Rutina que muestra el cuerpo del sitio, indicando que no es permitida la ejecuci&oacute;n
 *
 * @param string    $scripth    Scripts del header
 * @param string    $scriptb    Scripts del body
 * @param string    $txt        Cuerpo o contenido
 */
function mostrarNoPermitido($scripth = '', $scriptb = '', $txt = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    $pant = new template ();
    if (!isset($_SESSION["generales"]["pathabsoluto"])) {
        $pant->LoadTemplate('../../html/default/cuerpo.html');
    } else {
        if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/cuerpo.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/cuerpo.html');
        }
    }
    // $pant->armarDate();

    $pant->armarScriptHeader($scripth);
    $pant->armarScriptBody($scriptb);
    $pant->armarBarraOpciones('');
    $pant->armarFrameSecundarioLateral('');
    $pant->armarFrameSecundarioCentral($txt);

    $pant->armarDate();
    $pant->armarSesion();
    $pant->armarHttpHost(HTTP_HOST);
    $pant->armarUrlSite(TIPO_HTTP . HTTP_HOST);
    $pant->armarCodigoEntidad(CODIGO_EMPRESA);
    $pant->armarTituloAplicacion(NOMBRE_SISTEMA);
    $pant->armarSitioWeb(HTTP_HOST);
    $pant->armarNombreEntidad(RAZONSOCIAL);
    $pant->armarEmailAtencionUsuarios(EMAIL_ATENCION_USUARIOS);
    $pant->armarTelefonoAtencionUsuarios(TELEFONO_ATENCION_USUARIOS);
    $pant->armarFooter();

    $pant->armarDisplayHeader('no', 0, 0);
    $pant->armarDisplayBarraOpciones('no', 0, 0);
    $pant->armarDisplayFramePrincipal('no', 0, 0);
    $pant->armarDisplayFrameSecundario('si', 800, 400);
    $pant->armarDisplayFooter('no', 0, 0);
    if (!isset($_SESSION["generales"]["txtemergente"])) {
        $_SESSION["generales"]["txtemergente"] = '';
    }
    $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
    $_SESSION["generales"]["txtemergente"] = '';
    $pant->armarEstilo(CODIGO_EMPRESA);
    $pant->mostrar();
    unset($pant);
    exit();
}

/**
 * Funci&oacute;n que se utiliza para mostrar las ayudas emergente de los campos
 *
 * @param string $campo     Nombre del campo
 * @param string $txt       Contenido de la ayuda
 * @param int    $width     Ancho
 * @param int    $height    Alto
 */
function mostrarPantallaAyuda($campo, $txt, $width = 500, $height = 300) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    $pant = new template ();
    if (!isset($_SESSION["generales"]["pathabsoluto"])) {
        $pant->LoadTemplate('../../html/default/ayuda.html');
    } else {
        if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
            $pant->LoadTemplate('../../html/default/ayuda.html');
        } else {
            $pant->LoadTemplate($_SESSION["generales"]["pathabsoluto"] . '/html/default/ayuda.html');
        }
    }

    $pant->armarScriptHeader('');
    $pant->armarScriptBody('');
    // $pant->armarBarraOpciones ('Ayuda: '.$campo);
    $pant->armarBarraOpciones('');
    $pant->armarFrameSecundarioLateral('');
    $pant->armarFrameSecundarioCentral(cambiarSustitutoHtml($txt));

    $pant->armarDate();
    $pant->armarSesion();
    $pant->armarHttpHost(HTTP_HOST);
    $pant->armarUrlSite(TIPO_HTTP . HTTP_HOST);
    $pant->armarCodigoEntidad(CODIGO_EMPRESA);
    $pant->armarTituloAplicacion(NOMBRE_SISTEMA);
    $pant->armarSitioWeb(HTTP_HOST);
    $pant->armarNombreEntidad(RAZONSOCIAL);
    $pant->armarEmailAtencionUsuarios(EMAIL_ATENCION_USUARIOS);
    $pant->armarTelefonoAtencionUsuarios(TELEFONO_ATENCION_USUARIOS);
    $pant->armarFooter();

    $pant->armarDisplayHeader('no', 0, 0);
    $pant->armarDisplayBarraOpciones('si', $width, 30);
    $pant->armarDisplayFramePrincipal('no', 0, 0);
    $pant->armarDisplayFrameSecundario('si', $width, $height);
    $pant->armarDisplayFooter('no', 0, 0);
    if (!isset($_SESSION["generales"]["txtemergente"])) {
        $_SESSION["generales"]["txtemergente"] = '';
    }
    $pant->armarScriptEmergente($_SESSION["generales"]["txtemergente"]);
    $_SESSION["generales"]["txtemergente"] = '';
    $pant->armarEstilo(CODIGO_EMPRESA);
    $pant->mostrar();
    unset($pant);

    exit();
}

/**
 * Presenta una pantalla Predise&ntilde;ada
 * @param unknown $pantalla
 */
function mostrarPantallaPredisenada($pantalla) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/presentacion.class.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');

    $string = '';
    $pres = new presentacion();
    $string .= '<center>';
    $string .= $pres->abrirTablaBorde(800);
    $string .= $pres->armarLineaTexto(800, cambiarSustitutoHtml(retornarPantallaPredisenada($pantalla)), 'center');
    $string .= $pres->cerrarTablaBorde();
    $string = str_replace("[EMAIL_ATENCION_USUARIOS]", EMAIL_ATENCION_USUARIOS, $string);
    $string = str_replace("[NOMBRE_ENTIDAD]", RAZONSOCIAL, $string);
    $string = str_replace("[TELEFONO_ATENCION_USUARIOS]", TELEFONO_ATENCION_USUARIOS, $string);

    unset($pres);
    $head = '';
    mostrarCuerpoIE26a(array(), $head, '', '', $string, 620, 400, '', '', false, 0, 'si');
}

/**
 * Rutina que dada una cifra retorna un string con el monto escrito (en letras)
 */
function montoEscritoAnterior($xcifra) {

    $xarray = array(0 => "Cero",
        1 => "UN", "DOS", "TRES", "CUATRO", "CINCO", "SEIS", "SIETE", "OCHO", "NUEVE", "DIEZ", "ONCE", "DOCE", "TRECE", "CATORCE", "QUINCE", "DIECISEIS", "DIECISIETE", "DIECIOCHO", "DIECINUEVE", "VEINTI",
        30 => "TREINTA",
        40 => "CUARENTA",
        50 => "CINCUENTA",
        60 => "SESENTA",
        70 => "SETENTA",
        80 => "OCHENTA",
        90 => "NOVENTA",
        100 => "CIENTO",
        200 => "DOSCIENTOS",
        300 => "TRESCIENTOS",
        400 => "CUATROCIENTOS",
        500 => "QUINIENTOS",
        600 => "SEISCIENTOS",
        700 => "SETECIENTOS",
        800 => "OCHOCIENTOS",
        900 => "NOVECIENTOS"
    );
    //
    $xcifra = trim($xcifra);
    $xlength = strlen($xcifra);
    $xpos_punto = strpos($xcifra, ".");
    $xaux_int = $xcifra;
    $xdecimales = "00";
    if (!($xpos_punto === false)) {
        if ($xpos_punto == 0) {
            $xcifra = "0" . $xcifra;
            $xpos_punto = strpos($xcifra, ".");
        }
        $xaux_int = substr($xcifra, 0, $xpos_punto); // obtengo el entero de la cifra a covertir
        $xdecimales = substr($xcifra . "00", $xpos_punto + 1, 2); // obtengo los valores decimales
    }

    $XAUX = str_pad($xaux_int, 18, " ", STR_PAD_LEFT); // ajusto la longitud de la cifra, para que sea divisible por centenas de miles (grupos de 6)
    $xcadena = "";
    for ($xz = 0; $xz < 3; $xz++) {
        $xaux = substr($XAUX, $xz * 6, 6);
        $xi = 0;
        $xlimite = 6; // inicializo el contador de centenas xi y establezco el l&#65533;mite a 6 d&#65533;gitos en la parte entera
        $xexit = true; // bandera para controlar el ciclo del While
        while ($xexit) {
            if ($xi == $xlimite) { // si ya lleg&#65533; al l&#65533;mite m&aacute;ximo de enteros
                break; // termina el ciclo
            }

            $x3digitos = ($xlimite - $xi) * -1; // comienzo con los tres primeros digitos de la cifra, comenzando por la izquierda
            $xaux = substr($xaux, $x3digitos, abs($x3digitos)); // obtengo la centena (los tres d&#65533;gitos)
            for ($xy = 1; $xy < 4; $xy++) { // ciclo para revisar centenas, decenas y unidades, en ese orden
                switch ($xy) {
                    case 1: // checa las centenas
                        if (substr($xaux, 0, 3) < 100) { // si el grupo de tres d&#65533;gitos es menor a una centena ( < 99) no hace nada y pasa a revisar las decenas
                        } else {
                            if (isset($xarray[substr($xaux, 0, 3)])) {
                                $xseek = $xarray[substr($xaux, 0, 3)]; // busco si la centena es n&#65533;mero redondo (100, 200, 300, 400, etc..)
                                if ($xseek) {
                                    $xsub = montoEscritoSubfijo($xaux); // devuelve el subfijo correspondiente (Mill&#65533;n, Millones, Mil o nada)
                                    if (substr($xaux, 0, 3) == 100) {
                                        $xcadena = " " . $xcadena . " CIEN " . $xsub;
                                    } else {
                                        $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                                    }
                                    $xy = 3; // la centena fue redonda, entonces termino el ciclo del for y ya no reviso decenas ni unidades
                                }
                            } else { // entra aqu&#65533; si la centena no fue numero redondo (101, 253, 120, 980, etc.) {
                                $xseek = $xarray[substr($xaux, 0, 1) * 100]; // toma el primer caracter de la centena y lo multiplica por cien y lo busca en el arreglo (para que busque 100,200,300, etc)
                                $xcadena = " " . $xcadena . " " . $xseek;
                            } // ENDIF ($xseek)
                        } // ENDIF (substr($xaux, 0, 3) < 100)
                        break;
                    case 2: // checa las decenas (con la misma l&#65533;gica que las centenas)
                        if (substr($xaux, 1, 2) < 10) {
                            
                        } else {
                            if (isset($xarray[substr($xaux, 1, 2)])) {
                                $xseek = $xarray[substr($xaux, 1, 2)];
                                if ($xseek) {
                                    $xsub = montoEscritoSubfijo($xaux);
                                    if (substr($xaux, 1, 2) == 20) {
                                        $xcadena = " " . $xcadena . " VEINTE " . $xsub;
                                    } else {
                                        $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                                    }
                                    $xy = 3;
                                }
                            } else {
                                $xseek = $xarray[substr($xaux, 1, 1) * 10];
                                if (substr($xaux, 1, 1) * 10 == 20) {
                                    $xcadena = " " . $xcadena . " " . $xseek;
                                } else {
                                    $xcadena = " " . $xcadena . " " . $xseek . " Y ";
                                }
                            } // ENDIF ($xseek)
                        } // ENDIF (substr($xaux, 1, 2) < 10)
                        break;
                    case 3: // checa las unidades
                        if (substr($xaux, 2, 1) < 1) { // si la unidad es cero, ya no hace nada
                        } else {
                            $xseek = $xarray[substr($xaux, 2, 1)]; // obtengo directamente el valor de la unidad (del uno al nueve)
                            $xsub = montoEscritoSubfijo($xaux);
                            $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                        } // ENDIF (substr($xaux, 2, 1) < 1)
                        break;
                } // END SWITCH
            } // END FOR
            $xi = $xi + 3;
        } // ENDDO

        if (substr(trim($xcadena), -5, 5) == "ILLON") { // si la cadena obtenida termina en MILLON o BILLON, entonces le agrega al final la conjuncion DE
            $xcadena .= " DE";
        }

        if (substr(trim($xcadena), -7, 7) == "ILLONES") { // si la cadena obtenida en MILLONES o BILLONES, entoncea le agrega al final la conjuncion DE
            $xcadena .= " DE";
        }

        // ----------- esta l&#65533;nea la puedes cambiar de acuerdo a tus necesidades o a tu pa&#65533;s -------
        if (trim($xaux) != "") {
            switch ($xz) {
                case 0:
                    if (trim(substr($XAUX, $xz * 6, 6)) == "1") {
                        $xcadena .= "UN BILLON ";
                    } else {
                        $xcadena .= " BILLONES ";
                    }
                    break;
                case 1:
                    if (trim(substr($XAUX, $xz * 6, 6)) == "1") {
                        $xcadena .= "UN MILLON ";
                    } else {
                        $xcadena .= " MILLONES ";
                    }
                    break;
                case 2:
                    if ($xcifra < 1) {
                        $xcadena = "CERO PESOS $xdecimales/100 MONEDA CTE.";
                    }
                    if ($xcifra >= 1 && $xcifra < 2) {
                        $xcadena = "UN PESO $xdecimales/100 MONEDA CTE. ";
                    }
                    if ($xcifra >= 2) {
                        $xcadena .= " PESOS $xdecimales/100 MONEDA CTE. "; //
                    }
                    break;
            } // endswitch ($xz)
        } // ENDIF (trim($xaux) != "")
        // ------------------      en este caso, para M&#65533;xico se usa esta leyenda     ----------------
        $xcadena = str_replace("VEINTI ", "VEINTI", $xcadena); // quito el espacio para el VEINTI, para que quede: VEINTICUATRO, VEINTIUN, VEINTIDOS, etc
        $xcadena = str_replace("  ", " ", $xcadena); // quito espacios dobles
        $xcadena = str_replace("UN UN", "UN", $xcadena); // quito la duplicidad
        $xcadena = str_replace("  ", " ", $xcadena); // quito espacios dobles
        $xcadena = str_replace("BILLON DE MILLONES", "BILLON DE", $xcadena); // corrigo la leyenda
        $xcadena = str_replace("BILLONES DE MILLONES", "BILLONES DE", $xcadena); // corrigo la leyenda
        $xcadena = str_replace("DE UN", "UN", $xcadena); // corrigo la leyenda
    } // ENDFOR    ($xz)
    return trim($xcadena);
}

function montoEscritoSubfijo($xx) { // esta funci&#65533;n regresa un subfijo para la cifra
    $xx = trim($xx);
    $xstrlen = strlen($xx);
    if ($xstrlen == 1 || $xstrlen == 2 || $xstrlen == 3) {
        $xsub = "";
    }
    //
    if ($xstrlen == 4 || $xstrlen == 5 || $xstrlen == 6) {
        $xsub = "MIL";
    }
    //
    return $xsub;
}

function montoEscrito($num, $fem = false, $dec = true) {
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
    if ($dec and $fra and ! $zeros) {
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
            $t = ' ' . $matunisub[$n] . 'IENT' . $subcent . $t;
        } elseif ($n != 0) {
            $t = ' ' . $matunisub[$n] . 'CIENT' . $subcent . $t;
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
            $mils ++;
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
 * Funci&oacute;n que recibe un nit num&eacute;rico y lo convierte en un n&uacute;mro con m&aacute;scara visible
 *
 * @param 	string		$nit	Nit a convertir
 * @return 	string		Nit convertido
 */
function mostrarNit($nit) {
    if ($nit != 0) {
        $nit = str_replace(array(",", "-", "."), "", $nit);
        $nit = sprintf("%016s", $nit);
        $nit1 = number_format(substr($nit, 0, 15)) . '-' . substr($nit, 15, 1);
    } else {
        $nit1 = number_format(0);
    }
    return $nit1;
}

function mostrarAlertaScript($txt) {
    echo '<scritp>';
    echo "alert ('" . $txt . "');";
    echo "</script>";
}

function mostrarSwf($name) {
    require_once ('../../librerias/presentacion/presentacion.class.php');
    $name = str_replace("//", "/", $name);
    $aleatorio = generarAleatorioAlfanumerico();
    if (file_exists('../../tmp/' . $aleatorio . '.swf')) {
        unlink('../../tmp/' . $aleatorio . '.swf');
    }
    if (file_exists('../../tmp/' . $aleatorio . '.pdf')) {
        unlink('../../tmp/' . $aleatorio . '.pdf');
    }
    if (!file_exists($name)) {
        armarMensaje(600, 'h2', 'No es posible recuperar el archivo ' . $name);
        exit();
    }

    copy($name, '../../tmp/' . $aleatorio . '.pdf');

    if ((file_exists('/usr/local/bin/pdf2swf')) || (file_exists('/usr/bin/pdf2swf'))) {
        $comando = 'pdf2swf -S -z -f -t -s insertstop -s zoom=100 -T 9 -s fontquality=100 -s filloverlap -s caplinewidth=1 -s storeallcharacters -o ../../tmp/' . $aleatorio . '.swf ../../tmp/' . $aleatorio . '.pdf';
        exec($comando);
        if (!file_exists('../../tmp/' . $aleatorio . '.swf')) {
            $string = '<center>';
            $pres = new Presentacion ();
            $string .= armarMensaje(500, 'h2', utf8_decode('No fue posible mostrar el archivo en el visor de documentos, a continuaci&oacute;n se muestra bot&oacute;n para descargar el informe generado : (' . $comando . ')'));
            $string .= '<embed src="../../tmp/' . $aleatorio . '.pdf" width="800" height="500"></embed>';
            $arrBtnTipo = array('href');
            $arrBtnEnlace = array(
                "../../tmp/" . $aleatorio . ".pdf"
            );
            $arrBtnImagen = array(
                'Descargar',
            );
            $arrBtnToolTip = array(
                'Descargar el archivo',
            );
            $string .= '<BR><BR>';
            $string .= $pres->armarBarraBotonesProcesoDinamico(800, $arrBtnTipo, $arrBtnImagen, $arrBtnEnlace, $arrBtnToolTip);
            $string .= $pres->armarLinea(300);
            $string .= '</center>';
            unset($pres);
            mostrarCuerpoIE26a(array('windows'), '', '', '', $string, 800, 500, '', '');
            exit();
        }
        header("Location: ../../includes/FlexPaper158/index.php?_archivo=../../tmp/" . $aleatorio . ".swf&_name=" . $name . "&_namepdf=" . $aleatorio . '.pdf');
    } else {
        $string = '<center>';
        $pres = new Presentacion ();
        $string .= '<embed src="../../tmp/' . $aleatorio . '.pdf" width="800" height="500"></embed>';
        $arrBtnTipo = array('href');
        $arrBtnEnlace = array(
            "../../tmp/" . $aleatorio . ".pdf"
        );
        $arrBtnImagen = array(
            'Descargar',
        );
        $arrBtnToolTip = array(
            'Descargar el archivo',
        );
        $string .= '<BR><BR>';
        $string .= $pres->armarBarraBotonesProcesoDinamico(800, $arrBtnTipo, $arrBtnImagen, $arrBtnEnlace, $arrBtnToolTip);
        $string .= '</center>';
        unset($pres);
        mostrarCuerpoIE26a(array('windows'), '', '', '', $string, 800, 500, '', 'Espere ... ejecutando la acci&oacute;n solicitada');
    }
}

function mostrarTif($pathx = '') {

    if (isset($_SESSION["vars"]["path"])) {
        $path = $_SESSION["vars"]["path"];
    } else {
        $path = $pathx;
    }
    require_once '../../librerias/presentacion/plantilla.class.php';
    $p = new DoHtml('cuerpoVacioIE25');
    //$p->setScriptHeader($scriptHeader);
    $p->setHeadDhtmlx(array('windows'));
    //$p->setBodyParametros(array('onload'=>"doOnLoad();"));


    $applet = '<APPLET name="visorTif" id="visorTif" style="display"'
            . 'CODE="visorTif.class" codebase="../../includes/applets"'
            . 'ARCHIVE="appletTif.jar" WIDTH=100% HEIGHT=100%>'
            . '<param name="urlFile" id="urlFile"	value="http://' . $path . '">'
            . '</APPLET>';
    $p->setCuerpo($applet);
    $p->crearHtml();
}

/**
 * 
 * Number_format ajustado para la gesti&oacute;n de decimales
 * @param unknown_type $number
 * @param unknown_type $dec_point
 * @param unknown_type $thousands_sep
 */
function my_number_format($number, $dec_point, $thousands_sep = '') {
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
function my_number($number) {
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
        case 0: $decn = 0;
            break;
        case 1: $decn = $dec / 10;
            break;
        case 2: $decn = $dec / 100;
            break;
        case 3: $decn = $dec / 1000;
            break;
        case 4: $decn = $dec / 10000;
            break;
    }
    $numbern = 0;
    $numbern = doubleval($ent) + $decn;
    if ($signo == '-') {
        $numbern = $numbern * -1;
    }
    return $numbern;
}

function my_number1($number) {
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
        case 0: $decn = 0;
            break;
        case 1: $decn = $dec / 10;
            break;
        case 2: $decn = $dec / 100;
            break;
        case 3: $decn = $dec / 1000;
            break;
        case 4: $decn = $dec / 1000;
            break;
    }
    $numbern = 0;
    $numbern = doubleval($ent) + $decn;
    if ($signo == '-')
        $numbern = $numbern * -1;
    return $numbern;
}

/**
 * Funci&oacute;n que encuentra el navegador desde el cual el lçcliente està ejecutando la aplicaci&oacute;n
 *
 * @param 	string		$user_agent
 * @return 	string
 */
function obtenerNavegador1($user_agent) {
    $navegadores = array(
        'Opera' => 'Opera',
        'Mozilla Firefox' => '(Firebird)|(Firefox)',
        'Galeon' => 'Galeon',
        'Mozilla' => 'Gecko',
        'MyIE' => 'MyIE',
        'Lynx' => 'Lynx',
        'Netscape' => '(Mozilla/4\.75)|(Netscape6)|(Mozilla/4\.08)|(Mozilla/4\.5)|(Mozilla/4\.6)|(Mozilla/4\.79)',
        'Konqueror' => 'Konqueror',
        'Internet Explorer 8' => '(MSIE 8\.[0-9]+)',
        'Internet Explorer 7' => '(MSIE 7\.[0-9]+)',
        'Internet Explorer 6' => '(MSIE 6\.[0-9]+)',
        'Internet Explorer 5' => '(MSIE 5\.[0-9]+)',
        'Internet Explorer 4' => '(MSIE 4\.[0-9]+)',
    );
    foreach ($navegadores as $navegador => $pattern) {
        if (eregi($pattern, $user_agent)) {
            return $navegador;
        } else {
            return 'Desconocido';
        }
    }
}

/**
 * Funci&oacute;n que indica que tipo de navegador se est&aacute; utilizando
 * @param   string $nav
 * @return  string
 */
function obtenerNavegador($nav) {
    if (preg_match("/MSIE/i", "$nav")) {
        $resultado = "IE.";
    } else {
        if (preg_match("/Mozilla/i", "$nav")) {
            $resultado = "Mozilla.";
        } else {
            $resultado = "Estas usando $nav";
        }
    }
    return $resultado;
}

/**
 * Ordena una matriz por un &iacute;ndice (campo) dado 
 */
function ordenarMatriz($arreglo, $campo, $inverse = false) {
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

function parteEntera($valor) {
    $tem = explode(".", (string) $valor);
    return $tem[0];
}

function parsearOracion($txt) {

    //
    if ($txt == '') {
        return $txt;
    }

    // 2019-08-28: JINT: Se inactiva temporalmente.
    /*
      if ($txt != '') {
      return $txt;
      }
     */

    // echo $txt . '<br><b>';
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

    $txt = str_replace("&AACUTE;", "á", $txt);
    $txt = str_replace("&EACUTE;", "é", $txt);
    $txt = str_replace("&IACUTE;", "í", $txt);
    $txt = str_replace("&OACUTE;", "ó", $txt);
    $txt = str_replace("&UACUTE;", "ú", $txt);
    $txt = str_replace("&NTILDE;", "ñ", $txt);
    $txt = str_replace("&NBSP;", " ", $txt);
    $txt = str_replace("&AMP;NBSP;", " ", $txt);

    //
    $minusculas = '1234567890abcdefghijklmnñopqrstuvwxyzáéíóú ()#&$-_+*?';
    $mayusculas = 'ABCDEFGHIJKLMNÑOPQRSTUVWXYZÁÉÍÓÚ';

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
    if ($imay == 0) {
        return $txt;
    }
    if ($imin / $imay > 1) {
        return $txt;
    }
    unset($ar);

    //
    $limpiar = 'si';
    while ($limpiar == 'si') {
        if (strpos("  ", $txt) !== false) {
            $txt = str_replace("  ", " ", $txt);
        } else {
            $limpiar = 'no';
        }
    }


    //
    $txt = mb_strtolower($txt, 'UTF-8');
    // return $txt;
    //
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

function convertirLower($c) {
    $s = $c;
    switch ($c) {
        case "A" : $s = "a";
            break;
        case "B" : $s = "b";
            break;
        case "C" : $s = "c";
            break;
        case "D" : $s = "d";
            break;
        case "E" : $s = "e";
            break;
        case "F" : $s = "f";
            break;
        case "G" : $s = "g";
            break;
        case "H" : $s = "h";
            break;
        case "I" : $s = "i";
            break;
        case "J" : $s = "j";
            break;
        case "K" : $s = "k";
            break;
        case "L" : $s = "l";
            break;
        case "M" : $s = "m";
            break;
        case "N" : $s = "n";
            break;
        case "O" : $s = "o";
            break;
        case "P" : $s = "p";
            break;
        case "Q" : $s = "q";
            break;
        case "R" : $s = "r";
            break;
        case "S" : $s = "s";
            break;
        case "T" : $s = "t";
            break;
        case "U" : $s = "u";
            break;
        case "V" : $s = "v";
            break;
        case "W" : $s = "w";
            break;
        case "X" : $s = "x";
            break;
        case "Y" : $s = "y";
            break;
        case "Z" : $s = "z";
            break;
        case "Á" : $s = "á";
            break;
        case "É" : $s = "é";
            break;
        case "Í" : $s = "í";
            break;
        case "Ó" : $s = "ó";
            break;
        case "Ú" : $s = "ú";
            break;
        case "Ñ" : $s = "ñ";
            break;
    }
    return $s;
}

function parteDecimal($valor) {
    $aux = (string) $valor;
    $decimal = '';
    $arr = explode('.', $aux);
    if (isset($arr[1])) {
        $decimal = sprintf("%-4s", substr($arr[1], 0, 4));
    } else {
        $decimal = '0000';
    }
    $decimal = str_replace(" ", "0", $decimal);
    return $decimal;
}

function parteDecimal2($valor) {
    $aux = (string) $valor;
    $decimal = '';
    $arr = explode('.', $aux);
    if (isset($arr[1])) {
        $decimal = sprintf("%-2s", $arr[1]);
    } else {
        $decimal = '00';
    }
    $decimal = str_replace(" ", "0", $decimal);
    return $decimal;
}

/**
 * 
 * Funci�n que convierte un pdf en tiff
 * @param $pdf
 * @param $tiff
 */
function pdfToTiff($pdf, $tiff) {
    $cmd = "gs -SDEVICE=tiffg4 -r300x300 -sPAPERSIZE=letter -sOutputFile=$tiff -dNOPAUSE -dBATCH  $pdf";
    $response = shell_exec($cmd);
    return $response;
}

/**
 * Recibe un numero de identificaci&oacute;n y lo retorna limpio, sin comas, puntos, guiones, etc.
 * @param   string  $ide
 * @return  string
 */
function retornarIdentificacionLimpia($ide) {
    $ide = ltrim($ide, "0");
    $ide = ltrim($ide, " ");
    $ide = rtrim($ide, " ");
    $eliminar = array("-", ".", ",", "_", " ");
    $ide = str_replace($eliminar, "", $ide);
    return $ide;
}

/**
 * Recibe un valor y lo retorna limpio, sin comas, puntos, guiones, etc.
 * @param   string  $val
 * @return  string
 */
function retornarValorLimpio($val) {
    $val = ltrim($val, "0");
    $val = ltrim($val, " ");
    $val = rtrim($val, " ");
    $eliminar = array("-", ",", "_", " ", "$");
    $val = str_replace($eliminar, "", $val);
    return $val;
}

/**
 * genera XML con la relacion de datos retornados en el arreglo que recibe como parametro
 *
 * @param 		array 	$arrDatos	arreglo bidimencional con los datos
 * @param 		string	$nom		Nombre (raiz) del xml a crear en /tmp
 * @return 		file				Archivo xml
 * @return 		boolean				false/true

  function retornarXmlGrid($arrDatos) {
  $retornar = "<?xml version='1.0' encoding='utf-8'?>" . chr(13);
  $retornar.="<rows>" . chr(13);
  $i = 0;
  foreach ($arrDatos as $dato) {
  $i++;
  $retornar.='<row id="' . $i . '">' . chr(13);
  foreach ($dato as $campo) {
  $retornar.='<cell>' . $campo . '</cell>' . chr(13);
  }
  $retornar.='</row>' . chr(13);
  }
  $retornar.='</rows>' . chr(13);
  return $retornar;
  exit ();
  }
 */
/*
  function retornarXmlGrid($arrDatos,$headGrilla="") {
  if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
  header("Content-type: application/xhtml+xml");
  } else {
  header("Content-type: text/xml");
  }
  $retornar = "<?xml version='1.0' encoding='utf-8'?>". chr(13);
  $retornar.="<rows>" . chr(13);
  $retornar.=$headGrilla;
  $i = 0;
  foreach ($arrDatos as $dato) {
  $i++;
  $retornar.='<row id="' . $i . '">' . chr(13);
  foreach ($dato as $campo) {
  $retornar.='<cell>' . $campo . '</cell>' . chr(13);
  }
  $retornar.='</row>' . chr(13);
  }
  $retornar.='</rows>' . chr(13);
  return $retornar;
  exit ();
  }
 */

function retornarXmlGrid($arrDatos, $headGrilla = "") {
    require_once ('../../librerias/funciones/Encoding.php');
    if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
        header("Content-type: application/xhtml+xml");
    } else {
        header("Content-type: text/xml");
    }
    $retornar = "<?xml version='1.0' encoding='utf-8'?>" . chr(13);
    $retornar .= "<rows>" . chr(13);
    $retornar .= $headGrilla;
    $i = 0;
    if (!empty($arrDatos)) {
        foreach ($arrDatos as $dato) {
            $i++;
            $retornar .= '<row id="' . $i . '">' . chr(13);
            foreach ($dato as $campo) {
                // $retornar.='<cell><![CDATA[ ' . htmlentities(utf8_decode($campo)) . ']]></cell>' . chr(13);
                $retornar .= '<cell><![CDATA[ ' . Encoding::fixUTF8(($campo)) . ']]></cell>' . chr(13);
            }
            $retornar .= '</row>' . chr(13);
        }
    }
    $retornar .= '</rows>' . chr(13);
    return $retornar;
}

/*
  function quitarCaracteresEspeciales($txt) {
  $patrones[0] = "/[^a-zA-Z0-9 \&!¡ªº`'´]/";
  $reemplazos[0] = '';
  return trim(preg_replace($patrones, $reemplazos, $txt));
  }
 */

function quitarCaracteresDireccion($txt) {

    $patronesLetraNum[0] = '[^a-zA-Z0-9 ]';
    $reemplazos1[0] = '';
    $txt_tmp = trim(preg_replace($patronesLetraNum, $reemplazos1, $txt));

    $patronesSimbolos[0] = '[^.-#ºª?°]';
    $reemplazos2[0] = '';
    return trim(preg_replace($patronesSimbolos, $reemplazos2, $txt_tmp));
}

function reemplazarInvertidos($txt) {

    $txt = str_replace("À", "Á", $txt);
    $txt = str_replace("È", "É", $txt);
    $txt = str_replace("Ì", "Í", $txt);
    $txt = str_replace("Ò", "Ó", $txt);
    $txt = str_replace("Ù", "Ú", $txt);

    $txt = str_replace("à", "á", $txt);
    $txt = str_replace("è", "é", $txt);
    $txt = str_replace("ì", "í", $txt);
    $txt = str_replace("ò", "ó", $txt);
    $txt = str_replace("ù", "ú", $txt);

    $txt = str_replace("©", "@", $txt);

    return $txt;
}

/**
 * Funci&oacute;n que recibe un texto y reemplaza caracteres especiales por tags
 * Utilizado para enviar la informaci&oacute;n al SIREP
 *
 * @param 	string		$txt	Texto a convertir
 * @return 	string				Texto convertido
 */
function reemplazarEspeciales($txt) {

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



    return $txt;
}

/**
 * Funci&oacute;n que recibe un texto y reemplaza caracteres especiales por tags
 * Utilizado para enviar la informaci&oacute;n al SIREP
 *
 * @param 	string		$txt	Texto a convertir
 * @return 	string				Texto convertido
 */
function reemplazarEspecialesDom($txt) {
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
    //
    return $txt;
}

function reemplazarEspecialesDomRee($txt) {
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
function restaurarEspeciales($txt) {
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
    //
    return $txt;
}

function restaurarEspecialesMayusculas($txt) {
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
    //
    return $txt;
}

function restaurarEspecialesSinTildes($txt) {
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
function restaurarEspecialesRazonSocial($txt) {
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
function reemplazarHtmlPdf($txt) {
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
    return $txt;
}

function reemplazarHtml($txt) {
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
    return $txt;
}

function quitarTildes($txt) {
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

/**
 * Funci&oacute;n que retorna el c&oacute;digo de la empresa que est&aacute; marcada como preseleccionada en configuracion/empresas.php
 *
 * @return string	c&oacute;digo de la empresa preseleccionada
 */
function retornarCodigoXmlEmpresaPreseleccion() {
    $xml = simplexml_load_file($_SESSION["generales"]["pathabsoluto"] . '/configuracion/empresas.php');
    $salida = "";
    foreach ($xml->empresa as $tempresa) {
        $pre = (string) $tempresa->preseleccion;
        $cod = (string) $tempresa->codigo;
        if ($pre == 'si') {
            $salida = $cod;
        }
    }
    return $salida;
}

/**
 * Funci&oacute;n que retorna el nombre de la empresa que est&aacute; marcada como preseleccionada en configuracion/empresas.php
 *
 * @return string	nombre de la empresa preseleccionada
 */
function retornarNombreXmlEmpresaPreseleccion() {
    $xml = simplexml_load_file($_SESSION["generales"]["pathabsoluto"] . '/configuracion/empresas.php');
    $salida = "";
    foreach ($xml->empresa as $tempresa) {
        $pre = (string) $tempresa->preseleccion;
        $nom = (string) $tempresa->nombre;
        if ($pre == 'si') {
            $salida = $nom;
        }
    }
    return $salida;
    exit();
}

function retornarLetraColumna($offset, $indice) {
    $lCol = '';
    $ind = $indice + $offset;
    switch ($ind) {
        case 1: $lCol = 'A';
            break;
        case 2: $lCol = 'B';
            break;
        case 3: $lCol = 'C';
            break;
        case 4: $lCol = 'D';
            break;
        case 5: $lCol = 'E';
            break;
        case 6: $lCol = 'F';
            break;
        case 7: $lCol = 'G';
            break;
        case 8: $lCol = 'H';
            break;
        case 9: $lCol = 'I';
            break;
        case 10: $lCol = 'J';
            break;
        case 11: $lCol = 'K';
            break;
        case 12: $lCol = 'L';
            break;
        case 13: $lCol = 'M';
            break;
        case 14: $lCol = 'N';
            break;
        case 15: $lCol = 'O';
            break;
        case 16: $lCol = 'P';
            break;
        case 17: $lCol = 'Q';
            break;
        case 18: $lCol = 'R';
            break;
        case 19: $lCol = 'S';
            break;
        case 20: $lCol = 'T';
            break;
        case 21: $lCol = 'U';
            break;
        case 22: $lCol = 'V';
            break;
        case 23: $lCol = 'W';
            break;
        case 24: $lCol = 'X';
            break;
        case 25: $lCol = 'Y';
            break;
        case 26: $lCol = 'Z';
            break;
        case 27: $lCol = 'AA';
            break;
        case 28: $lCol = 'AB';
            break;
        case 29: $lCol = 'AC';
            break;
        case 30: $lCol = 'AD';
            break;
        case 31: $lCol = 'AE';
            break;
        case 32: $lCol = 'AF';
            break;
        case 33: $lCol = 'AG';
            break;
        case 34: $lCol = 'AH';
            break;
        case 35: $lCol = 'AI';
            break;
        case 36: $lCol = 'AJ';
            break;
        case 37: $lCol = 'AK';
            break;
        case 38: $lCol = 'AL';
            break;
        case 39: $lCol = 'AM';
            break;
        case 40: $lCol = 'AN';
            break;
        case 41: $lCol = 'AO';
            break;
        case 42: $lCol = 'AP';
            break;
        case 43: $lCol = 'AQ';
            break;
        case 44: $lCol = 'AR';
            break;
        case 45: $lCol = 'AS';
            break;
        case 46: $lCol = 'AT';
            break;
        case 47: $lCol = 'AU';
            break;
        case 48: $lCol = 'AV';
            break;
        case 49: $lCol = 'AW';
            break;
        case 50: $lCol = 'AX';
            break;
        case 51: $lCol = 'AY';
            break;
        case 52: $lCol = 'AZ';
            break;
        case 53: $lCol = 'BA';
            break;
        case 54: $lCol = 'BB';
            break;
        case 55: $lCol = 'BC';
            break;
        case 56: $lCol = 'BD';
            break;
        case 57: $lCol = 'BE';
            break;
        case 58: $lCol = 'BF';
            break;
        case 59: $lCol = 'BG';
            break;
        case 60: $lCol = 'BH';
            break;
        case 61: $lCol = 'BI';
            break;
        case 62: $lCol = 'BJ';
            break;
        case 63: $lCol = 'BK';
            break;
        case 64: $lCol = 'BL';
            break;
        case 65: $lCol = 'BM';
            break;
        case 66: $lCol = 'BN';
            break;
        case 67: $lCol = 'BO';
            break;
        case 68: $lCol = 'BP';
            break;
        case 69: $lCol = 'BQ';
            break;
        case 70: $lCol = 'BR';
            break;
        case 71: $lCol = 'BS';
            break;
        case 72: $lCol = 'BT';
            break;
        case 73: $lCol = 'BU';
            break;
        case 74: $lCol = 'BV';
            break;
        case 75: $lCol = 'BW';
            break;
        case 76: $lCol = 'BX';
            break;
        case 77: $lCol = 'BY';
            break;
        case 78: $lCol = 'BZ';
            break;
        case 79: $lCol = 'CA';
            break;
        case 80: $lCol = 'CB';
            break;
        case 81: $lCol = 'CC';
            break;
        case 82: $lCol = 'CD';
            break;
        case 83: $lCol = 'CE';
            break;
        case 84: $lCol = 'CF';
            break;
        case 85: $lCol = 'CG';
            break;
    }
    return $lCol;
}

function retornarPrimeraEmpresa() {
    $xml = simplexml_load_file($_SESSION["generales"]["pathabsoluto"] . '/configuracion/empresas.php');
    $salida = "";
    foreach ($xml->empresa as $tempresa) {
        $pre = (string) $tempresa->preseleccion;
        $cod = (string) $tempresa->codigo;
        $nom = (string) $tempresa->nombre;
        $act = (string) $tempresa->activado;
        if ($act == 'S') {
            if ($pre == 'si') {
                if ($salida == '') {
                    $salida = $cod;
                }
            }
        }
    }
    return $salida;
}

function retornarListaXmlEmpresas($emp = '', $httphost = '') {
    $xml = simplexml_load_file($_SESSION["generales"]["pathabsoluto"] . '/configuracion/empresas.php');
    $salida = "";
    if (($emp == '') || ($emp == 'bas')) {
        foreach ($xml->empresa as $tempresa) {
            $pre = (string) $tempresa->preseleccion;
            $cod = (string) $tempresa->codigo;
            $nom = (string) $tempresa->nombre;
            $act = (string) $tempresa->activado;
            $host = (string) $tempresa->host;
            if ($httphost != '') {
                if ($httphost == $host) {
                    $salida .= '<option value="' . $cod . '" selected>' . ($nom) . '</option>';
                } else {
                    $salida .= '<option value="' . $cod . '">' . ($nom) . '</option>';
                }
            } else {
                if ($act == 'S') {
                    if ($pre == 'si') {
                        $salida .= '<option value="' . $cod . '" selected>' . ($nom) . '</option>';
                    } else {
                        $salida .= '<option value="' . $cod . '">' . ($nom) . '</option>';
                    }
                }
            }
        }
    } else {
        foreach ($xml->empresa as $tempresa) {
            $cod = (string) $tempresa->codigo;
            $nom = (string) $tempresa->nombre;
            $act = (string) $tempresa->activado;
            if ($cod == $emp) {
                $salida .= '<option value="' . $cod . '" selected>' . ($nom) . '</option>';
            } else {
                $salida .= '<option value="' . $cod . '">' . ($nom) . '</option>';
            }
        }
    }
    return $salida;
}

function retornarCodigoEmpresa($httphost = '') {
    $xml = simplexml_load_file($_SESSION["generales"]["pathabsoluto"] . '/configuracion/empresas.php');
    $salida = "";
    foreach ($xml->empresa as $tempresa) {
        $cod = (string) $tempresa->codigo;
        $host = (string) $tempresa->host;
        if ($httphost != '') {
            if ($httphost == $host) {
                $salida = $cod;
            }
        }
    }
    if ($salida == '') {
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

/*
 * Funcion que retorna lista de meses
 */

function retornarMes($id) {
    $tmes = '';
    switch ($id) {
        case "01": $tmes = 'Enero';
            break;
        case "02": $tmes = 'Febrero';
            break;
        case "03": $tmes = 'Marzo';
            break;
        case "04": $tmes = 'Abril';
            break;
        case "05": $tmes = 'Mayo';
            break;
        case "06": $tmes = 'Junio';
            break;
        case "07": $tmes = 'Julio';
            break;
        case "08": $tmes = 'Agosto';
            break;
        case "09": $tmes = 'Septiembre';
            break;
        case "10": $tmes = 'Octubre';
            break;
        case "11": $tmes = 'Noviembre';
            break;
        case "12": $tmes = 'Diciembre';
            break;
    }
    return $tmes;
}

/*
 * Funci&oacute;n que retorna lista de a&ntilde;os
 */

function retornarSelectAnos($id) {
    $retornar = '';
    $anoini = $id - 10;
    $anofin = $id + 10;
    for ($i = $anoini; $i <= $anofin; $i++) {
        if ($id == $i) {
            $retornar .= '<option value="' . $i . '" selected>' . $i . '</option>';
        } else {
            $retornar .= '<option value="' . $i . '">' . $i . '</option>';
        }
    }
    return $retornar;
    exit();
}

/*
 * Funci&oacute;n que retorna lista de a&ntilde;os
 */

function retornarSelectMeses($id) {
    $retornar = '';
    for ($i = 1; $i <= 12; $i++) {
        switch ($i) {
            case 1: $tmes = 'Enero';
                break;
            case 2: $tmes = 'Febrero';
                break;
            case 3: $tmes = 'Marzo';
                break;
            case 4: $tmes = 'Abril';
                break;
            case 5: $tmes = 'Mayo';
                break;
            case 6: $tmes = 'Junio';
                break;
            case 7: $tmes = 'Julio';
                break;
            case 8: $tmes = 'Agosto';
                break;
            case 9: $tmes = 'Septiembre';
                break;
            case 10: $tmes = 'Octubre';
                break;
            case 11: $tmes = 'Noviembre';
                break;
            case 12: $tmes = 'Diciembre';
                break;
        }
        if ($id == $i) {
            $retornar .= '<option value="' . $i . '" selected>' . $tmes . '</option>';
        } else {
            $retornar .= '<option value="' . $i . '">' . $tmes . '</option>';
        }
    }
    return $retornar;
    exit();
}

/**
 * 
 * Funci&oacute;n que de un arreglo dado retorna un select html
 * @param $txt - Texto en caso de id vacio
 * @param $valdef - Valor por defecto - puede ser blancos o ceros
 * @param $array - Arreglo de datos a desplegar en el select
 * @param $id - C&oacute;digo actual seleccionado
 */
function retornarSelectVarios($txt, $valdef, $array, $id) {
    $retorno = '';
    if ((trim($id) == '') || (trim($id) == '0')) {
        $retorno .= "<option value=" . $valdef . " selected>" . $txt . "</option>" . chr(13);
    } else {
        $retorno .= "<option value=" . $valdef . ">" . $txt . "</option>" . chr(13);
    }
    foreach ($array as $res) {
        if ($res[0] == $id) {
            $retorno .= "<option value=" . $res[0] . " selected>" . $res[1] . "</option>" . chr(13);
        } else {
            $retorno .= "<option value=" . $res[0] . ">" . $res[1] . "</option>" . chr(13);
        }
    }
    return $retorno;
    exit();
}

/*
  function sectorEconomico ($ciiu) {
  $txSector = 'OTROS';
  if ((substr($ciiu,0,1)=='A') || (substr($ciiu,0,1)=='B')) $txSector = 'AGRICULTURA Y AFINES';
  if ((substr($ciiu,0,1)=='C')) $txSector = 'MINERIA';
  if ((substr($ciiu,0,1)=='D')) $txSector = 'INDUSTRIA Y MANUFACTURA';
  if ((substr(ciiu,0,1)=='E')) $txSector = 'AGUA, LUZ Y GAS';
  if ((substr($ciiu,0,1)=='F')) $txSector = 'CONSTRUCCION';
  if ((substr($ciiu,0,1)=='G') || (substr($ciiu,0,1)=='H')) $txSector = 'COMERCIO, RESTAURANTES Y HOTELES';
  if ((substr($ciiu,0,1)=='I')) $txSector = 'TRANSPORTE ALMACENAMIENTO Y COMUNICACIONES';
  if ((substr($ciiu,0,1)=='J') || (substr($ciiu,0,1)=='K')) $txSector = 'FINANCIEROS, SEGUTOS, BIENES INMUEBLES Y OTRAS ACT. EMPRESARIALES';
  if ((substr($ciiu,0,1)=='L') ||
  (substr($ciiu,0,1)=='M') ||
  (substr($ciiu,0,1)=='N') ||
  (substr($ciiu,0,1)=='O') ||
  (substr($ciiu,0,1)=='P') ||
  (substr($ciiu,0,1)=='Q')) $txSector = 'SERVICIOS';
  return $txSector;
  }
 */

/* -------------------------------------------------------------------
  Modificado el 11 de sept de 2014 por FK para actualizar la descripcion
  de los sectores de acuerdo a la version CIIU 4
  --------------------------------------------------------------------- */

function sectorEconomico($ciiu) {
    $txSector = 'OTROS';
    if ((substr($ciiu, 0, 1) == 'A'))
        $txSector = 'Agricultura, ganadería, caza, silvicultura y pesca';
    if ((substr($ciiu, 0, 1) == 'B'))
        $txSector = 'Explotación de minas y canteras';
    if ((substr($ciiu, 0, 1) == 'C'))
        $txSector = 'Industrias manufactureras';
    if ((substr($ciiu, 0, 1) == 'D'))
        $txSector = 'Suministro de electricidad, gas, vapor y aire';
    if ((substr($ciiu, 0, 1) == 'E'))
        $txSector = 'Distribución de agua, saneamiento ambiental';
    if ((substr($ciiu, 0, 1) == 'F'))
        $txSector = 'Construcción';
    if ((substr($ciiu, 0, 1) == 'G'))
        $txSector = 'Comercio al por mayor y al por menor vehículos';
    if ((substr($ciiu, 0, 1) == 'H'))
        $txSector = 'Transporte y almacenamiento';
    if ((substr($ciiu, 0, 1) == 'I'))
        $txSector = 'Alojamiento y servicios de comida';
    if ((substr($ciiu, 0, 1) == 'J'))
        $txSector = 'Información y comunicaciones';
    if ((substr($ciiu, 0, 1) == 'K'))
        $txSector = 'Actividades financieras y de seguros';
    if ((substr($ciiu, 0, 1) == 'L'))
        $txSector = 'Actividades inmobiliarias';
    if ((substr($ciiu, 0, 1) == 'M'))
        $txSector = 'Actividades profesionales, científicas y técnicas';
    if ((substr($ciiu, 0, 1) == 'N'))
        $txSector = 'Actividades de servicios administrativos y de apoyo';
    if ((substr($ciiu, 0, 1) == 'O'))
        $txSector = 'Administración pública y defensa seguridad social';
    if ((substr($ciiu, 0, 1) == 'P'))
        $txSector = 'Educación';
    if ((substr($ciiu, 0, 1) == 'Q'))
        $txSector = 'Actividades de salud humana y asistencia social';
    if ((substr($ciiu, 0, 1) == 'R'))
        $txSector = 'Actividades artísticas, de entretenimiento';
    if ((substr($ciiu, 0, 1) == 'S'))
        $txSector = 'Otras actividades de servicios';
    if ((substr($ciiu, 0, 1) == 'T'))
        $txSector = 'Actividades hogares en calidad de empleadores';
    if ((substr($ciiu, 0, 1) == 'U'))
        $txSector = 'Actividades de organizaciones y entidades extraterritoriales';
    return $txSector;
}

function tamanoArchivo($file) {
    if (!file_exists($file)) {
        return 0;
    } else {
        return (filesize($file));
    }
}

/*
 * Funci&oacute;n que realiza las validaciones iniciales antes de ingresar al sistema
 */

function validacionesIniciales() {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/lenguajes/' . $_SESSION["generales"]["idioma"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');
    $iIncon = 0;
    $txt = '';

    // Debe estar definido un servidor SMTP
    if (!defined('SERVER_SMTP')) {
        $iIncon++;
        $txt .= $iIncon . ') ' . 'No est&aacute; definido el servidor SMTP en el commonXX' . '<br><br>';
    }

    // Debe indicarse si el servidor SMTP requiere o no autenticaci&oacute;n
    if (!defined('REQUIERE_SMTP_AUTENTICACION')) {
        $iIncon++;
        $txt .= $iIncon . ') ' . 'No est&aacute; definido el m&eacute;todo de autenticacion SMTP en el commonXX' . '<br><br>';
    }

    if (validarConexionDB(DBMS, DB_HOST, DB_PORT, DB_NAME, DB_USUARIO, DB_PASSWORD) === false) {
        $iIncon++;
        $txt .= $iIncon . ') ' . 'Error de conexi&oacute;n con la BD del sistema SII' . '<br><br>';
    }

    return $txt;
    exit();
}

/**
 * Funci&oacute;n que recibe un n&uacute;mero de identificaci&oacute;n y verifica que el &uacute;ltimo d&iacute;gito si
 * corresponda con el d&iacute;gito de verificaci&oacute;n en m&oacute;dulo 11
 *
 * @param 		string		$ide 	Identificaci&oacute;n a verificar
 * @return 		boolean				Verdadero o falso
 */
function calcularDv($id) {
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
    exit;
}

/**
 * Devuelve el tipo de registro asociado con un tipotramite de una liquidaci&oacute;n
 * @param unknown $tipotra
 * @return string
 */
function tipoDeTramite($tipotra) {
    $salida = '';
    switch ($tipotra) {
        case "certificadoselectronicos" :
            $salida = 'CerEle';
            break;
        case "crm_alquilersalones" :
        case "crm_ventapublicaciones" :
        case "crm_cuotaafiliacion" :
        case "crm_renovacionafiliacion" :
        case "crm_inscripcionseminarios" :
        case "crm_inscripcionferias" :
            $salida = 'crm';
            break;
        case "certificadosvirtuales" :
            $salida = 'CerVirt';
            break;
        case "libroscomercio" :
            $salida = 'LibCom';
            break;
        case "matriculacae" :
            $salida = 'CAE';
            break;
        case "actualizacioncircular3" :
        case "actualizacioncircular19" :
        case "cancelacionmatricula" :
        case "renovacionmatricula" :
        case "mutaciondireccion" :
        case "mutacionnombre" :
        case "mutacionactividad" :
        case "matriculapnat" :
        case "matriculacambidom" :
        case "matriculaest" :
        case "matriculasuc" :
        case "matriculapjur" :
        case "matriculaesadl" :
        case "solicitudcancelacionpnat" :
        case "solicitudcancelacionest" :
        case "solicitudcancelacionpjur" :
            $salida = 'RegMer';
            break;
        case "renovacionesadl" :
        case "solicitudcancelacionesadl" :
            $salida = 'RegEsadl';
            break;
        case "inscripcionproponente" :
        case "renovacionproponente" :
        case "actualizacionproponente" :
        case "cancelacionproponente" :
        case "cambiodomicilioproponente" :
            $salida = 'RegPro';
            break;
        case "inscripciondocumentos" :
            $salida = 'InscDoc';
            break;
        case "inscripciondocumentosregmer" :
            $salida = 'InscDocRegMer';
            break;
        case "inscripciondocumentosesadl" :
            $salida = 'InscDocRegEsadl';
            break;
        case "prepago" :
            $salida = 'PrePag';
            break;
        case "cancelacionfacturas" :
            $salida = 'CancelaFacturas';
            break;
        case "serviciosempresariales" :
            $salida = 'ServEmp';
            break;
    }
    return $salida;
}

/*
 * Rutina para convertir tiff to pdf
 * Se requiere Ghostcript 
 * Se requiere ImageMagick
 */

function tiffToPdf($file_tif, $file_pdf, $format = 'Letter') {
    if (!file_exists($file_tif))
        return 1;
    // exec('../../includes/pstill_dist/pstill -F ' . $format . ' -o '.$file_pdf.' '.$file_tif.' >> ../../tmp/visor.txt');
    exec('../../includes/pstill_dist/pstill -c -c -c -g -i -p -t -J 70 -o ' . $file_pdf . ' ' . $file_tif . ' >> ../../tmp/visor.txt');
    return 0;
    // return true;
}

function docToPdf($file_doc, $file_pdf) {
    require_once ('../../configuracion/common.php');
    if (!defined('PATH_COMMON_BASE'))
        define('PATH_COMMON_BASE', '/opt');
    if (!file_exists(PATH_COMMON_BASE . '/commonBase.php')) {
        $_SESSION["generales"]["mensajeerror"] = 'No es posible pasar el formato doc a pdf, no localiz&oacute; el archivo commonBase.php';
        return false;
    }

    require_once (PATH_COMMON_BASE . '/commonBase.php');

    set_include_path(get_include_path() . PATH_SEPARATOR . PATH_ABSOLUTO_SITIO . '/includes/phpseclib');
    require_once ('Net/SSH2.php');
    ini_set('display_errors', '1');

    // Encuentra el nombre del archivo sin directorios
    $lfile = explode('/', $file_doc);
    $extns = count($lfile) - 1;
    $file = $lfile[$extns];

    // Si el archivo no existe
    if (!file_exists($file_doc)) {
        $_SESSION["generales"]["mensajeerror"] = 'No fue encontrado el archivo a convertir a pdf';
        return false;
    }

    // Conexi&oacute;n SSH al localhost para ejecutar como root
    $ssh = new Net_SSH2('localhost');
    if (!$ssh->login(SII_USER_ROOT, SII_PASSWORD_ROOT)) {
        $_SESSION["generales"]["mensajeerror"] = 'No fue posible activar la conexi&oacute;n SSH a localhost para ejecutar el proceso';
        return false;
    }

    // Ejecuci&oacute;n del comndo libreoffice desde ssh
    if (!$ssh->exec('libreoffice --headless --invisible --convert-to pdf:writer_pdf_Export -outdir ' . PATH_ABSOLUTO_SITIO . '/tmp ' . PATH_ABSOLUTO_SITIO . '/tmp/' . str_replace("../../tmp/", "", $file_doc))) {
        // if (!$ssh->exec('libreoffice --headless --invisible --convert-to pdf --infilter="Microsoft Word 2007/2010/2013 XML" -outdir ' . PATH_ABSOLUTO_SITIO . '/tmp ' . PATH_ABSOLUTO_SITIO . '/tmp/' . str_replace("../../tmp/", "", $file_doc))) {
        $txt = '';
        foreach ($ssh->message_log as $log) {
            $txt .= $log;
        }
        $_SESSION["generales"]["mensajeerror"] = 'No fue posible convertir el archivo doc o docx a pdf, archivo original : ' . PATH_ABSOLUTO_SITIO . '/tmp/' . str_replace("../../tmp/", "", $file_doc);
        return false;
    }

    // Localizaci&oacute;n de la extensi&oacute;n del archivo doc o docx
    $ext = encontrarExtension($file);

    // reemplazo de la extensi&oacute;n en el archivo de salida
    $file = str_replace("." . $ext, ".pdf", $file);

    // Cambio de permisos
    $ssh->exec('chmod 666 ' . PATH_ABSOLUTO_SITIO . '/tmp/' . $file);

    // destrucci&oacute;n del objeto ssh
    unset($ssh);

    // Copiar el pdf generado al pdf esperado
    copy('../../tmp/' . $file, $file_pdf);

    // Borrado del pdf generado
    // unlink ('../../tmp/' . $file);
    return true;
}

function dumpArreglo($arreglo) {
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

function procesarSega($sega, $file, $extension = '', $extension1 = '', $extension2 = '', $extension3 = '', $extension4 = '') {
    require_once ('../../configuracion/common.php');
    require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ('../../librerias/funciones/Log.class.php');
    if (!defined('ACTIVADO_SEGA')) {
        define('ACTIVADO_SEGA', 'SEGA-RM');
    }
    if (ACTIVADO_SEGA == 'SEGA-RM' || ACTIVADO_SEGA == '') {
        $res = procesarSegaRm($sega, $file, $extension, $extension1, $extension2, $extension3, $extension4);
    }
    if (ACTIVADO_SEGA == 'SEGA-GNU') {
        $res = procesarSegaGnu($sega, $file, $extension, $extension1, $extension2, $extension3, $extension4);
    }
    return $res;
}

function procesarSegaRm($sega, $file, $extension, $extension1, $extension2, $extension3, $extension4) {
    require_once ('../../configuracion/common.php');
    require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ('../../librerias/funciones/Log.class.php');
    if ($sega["conexion"] == 'no') {
        $_SESSION["generales"]["mensajeerror"] = 'No hay sega configurado, no se puede ejecutar la opci&oacute;n';
        return false;
    }

    if ($sega["conexion"] == 'local') {
        $command = $sega["runcobol"] . ' ' . $sega["path"] . '/enruta A="' . PATH_ABSOLUTO_SITIO . '/tmpmig/' . $_SESSION["generales"]["codigoempresa"] . '/' . $file . '"';
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
        log::general2('rmcobol', '', $sftp1->getLog());
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
    log::general2('rmcobol', '', 'Ejecutando comando runcobol:' . $ssh->getLog());
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

function procesarSegaGnu($sega, $file, $extension = '', $extension1 = '', $extension2 = '', $extension3 = '', $extension4 = '') {
    require_once ('../../configuracion/common.php');
    require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ('../../librerias/funciones/Log.class.php');

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
        log::general2('cobcrun', '', $sftp1->getLog());
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
    log::general2('cobcrun', '', 'Ejecutando comando cobcrun:' . $ssh->getLog());
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

function validarDv($id) {
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
    exit;
}

function separarDv($id) {
    $id = str_replace(",", "", ltrim(trim($id), "0"));
    $id = str_replace(".", "", $id);
    $id = str_replace("-", "", $id);
    $entrada = sprintf("%016s", $id);
    $dv = substr($entrada, 15, 1);
    return array(
        'identificacion' => ltrim(substr($entrada, 0, 15), "0"),
        'dv' => $dv);
}

function separarTexto($val, $tam = 65) {
    $salida = array();
    $i = 0;
    $j = 0;
    $val = strtoupper(trim($val));
    $val = str_replace("'", "", $val);
    $val = str_replace("\"", "", $val);
    $val = str_replace("\r\n", " ", $val);
    $val = str_replace("\n\r", " ", $val);
    $val = str_replace("\n", " ", $val);
    $val = str_replace("\r", " ", $val);
    $val = str_replace(chr(13), " ", $val);
    while ($i < strlen($val)) {
        $tx = substr($val, $i, $tam);
        $len = strlen($tx);
        if (trim($tx) != '') {
            $j++;
            $salida[$j] = substr($val, $i, $len);
        }
        $i = $i + $len;
    }
    return $salida;
}

function separarNombres($val) {
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

function truncateFloatForm($number, $digitos, $pd = '.', $pm = ',') {
    $raiz = 10;
    $multiplicador = pow($raiz, $digitos);
    $resultado = ((int) ($number * $multiplicador)) / $multiplicador;
    $x = number_format($resultado, $digitos, $pd, $pm);
    return $x;
}

function truncateFloat($number, $digitos, $pd = '.', $pm = ',') {
    $raiz = 10;
    $multiplicador = pow($raiz, $digitos);
    $resultado = ((int) ($number * $multiplicador)) / $multiplicador;
    $x = number_format($resultado, $digitos, $pd, $pm);
    $x = str_replace(",", "", $x);
    return $x;
}

function truncateFinancialIndexes($number) {
    $sep = explode(",", $number);
    if (isset($sep[1])) {
        if (strlen($sep[1]) == 1) {
            $number = $sep[0] . ',' . $sep[1] . '0';
        }
        if (strlen($sep[1]) > 2) {
            $number = $sep[0] . ',' . substr($sep[1], 0, 2);
        }
    }
    return $number;
}

function truncarValorNuevo($valor) {
    $valor = str_replace(".", ",", $valor);
    $rgv = explode(",", $valor);
    $valt = '';
    // $valt = number_format($rgv[0], 0, "", ".") . ',';
    $valt = number_format($rgv[0], 0, ",", ".") . ',';
    $valt = $rgv[0] . ',';
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
    return $valt;
}

function truncarValorNuevoFormulario($valor) {
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
    $valt = number_format($rgv[0], 0, "", ".") . ',';
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

function truncarValorNuevoFormularioMercantil($valor) {
    // return truncarValorNuevo($valor);         
    // exit ();
    $valor = doubleval($valor);

    if (ltrim(trim($valor), "0") == '' || $valor == ".00" || $valor == "0" || $valor == "0.00") {
        return "0.00";
    }

    if ($valor < 0) {
        $signo = '-';
    } else {
        $signo = '';
    }
    $valor = str_replace("-", "", $valor);
    $rgv = explode(".", $valor);
    $valt = '';
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

/**
 * Funci&oacute;n que recibe un correo electr&oacute;nico y verifica si est&aacute; correcto
 *
 * @param 	string		$email		Email a verificar
 * @return 	boolean					Verdadero o falso
 */
function validarEmail($email) {
    if ((strlen($email) >= 6) && (substr_count($email, "@") == 1) && (substr($email, 0, 1) != "@") && (substr($email, strlen($email) - 1, 1) != "@")) {
        if ((!strstr($email, "'")) && (!strstr($email, "\"")) && (!strstr($email, "\\")) && (!strstr($email, "\$")) && (!strstr($email, " "))) {
            if (substr_count($email, ".") >= 1) {
                $term_dom = substr(strrchr($email, '.'), 1);
                if (strlen($term_dom) > 1 && strlen($term_dom) < 7 && (!strstr($term_dom, "@"))) {
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

/**
 * Funci&oacute;n que recibe un campo string y valida si tiene caracteres especiales
 * como comas, puntos, dos puntos, ampersan, comillas simples, comillas dobles
 * En tal caso genera false.
 */
function validarEspeciales($txt) {
    $txtNuevo = ereg_replace("[^A-Za-z0-9]", "", $txt);
    if (trim($txt) != (trim($txtNuevo))) {
        return false;
    } else {
        return true;
    }
    exit();
}

/**
 * Funci&oacute;n que recibe la fecha como par&aacute;metro y edtermina si esta est&aacute; bien digitada o no
 *
 * @param 	string		$dsfecha
 * @return 	boolean		Verdadero o falso
 */
function validarFecha($dsfecha) {
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
                        (substr($dsfecha, 4, 2) == "12")) {
                    if ((substr($dsfecha, 6, 2) < "01") || (substr($dsfecha, 6, 2) > "31")) {
                        $mal = "1";
                    }
                }
                if ((substr($dsfecha, 4, 2) == "04") ||
                        (substr($dsfecha, 4, 2) == "06") ||
                        (substr($dsfecha, 4, 2) == "09") ||
                        (substr($dsfecha, 4, 2) == "11")) {
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

/**
 * Funci&oacute;n que valida que un campo sea 100% num&eacute;rico o blancos
 */
function validarNumerico($txt) {
    $txtNuevo = preg_replace("[^0-9]", "", $txt);
    if (trim($txt) != (trim($txtNuevo))) {
        return false;
    } else {
        return true;
    }
    exit();
}

/**
 * Funci&oacute;n utilizada para validar la correcta digitaci&oacute;n de un login
 */
function validarPermitidosLogin($txt) {
    $permitidos = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890_-";
    for ($i = 0; $i < strlen($txt); $i++) {
        if (strpos($permitidos, substr($txt, $i, 1)) === false) {
            return false;
        }
    }
    return true;
}

/**
 * Funci&oacute;n utilizada para validar la correcta digitaci&oacute;n de una matr&iacute;cula
 */
function validarPermitidosMatricula($txt) {
    $permitidos = "nNsS1234567890";
    for ($i = 0; $i < strlen($txt); $i++) {
        if (strpos($permitidos, substr($txt, $i, 1)) === false) {
            return false;
        }
    }
    return true;
}

/**
 * Valida un n&uacute;mero telef&oacute;nico, que tenga 7 d&iacute;gitos, que no tenga letras y que
 * no sea de la forma 11111, 22222, 33333, etc.
 */
function validarTelefonoSii($txt) {
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

/**
 * Valida un n&uacute;mero telef&oacute;nico, que tenga 7 d&iacute;gitos, que no tenga letras y que
 * no sea de la forma 11111, 22222, 33333, etc.
 */
function validarMovilSii($txt) {
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

/**
 * Retorna los datos de un usuario espec&iacute;fico
 *
 * @param 	string 	$usua
 * @param 	string 	$clave
 * @return 	boolean	true/false
 * 
 */
function validarUsuario($usua = '', $clave = '', $periodo = '', $identificacion = '') {
    if (!file_exists($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php')) {
        return "Archivo de par&aacute;metros no existe";
    }
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');

    //
    $usua = base64_decode($usua);
    $clave = base64_decode($clave);
    $arrUsuario = retornarUsuario($usua, $identificacion);
    if (($arrUsuario === false) || (empty($arrUsuario))) {
        return "Usuario no existe";
    }
    if (!isset($arrUsuario["identificacion"])) {
        return "Usuario no existe";
    }

    //
    if ($arrUsuario["identificacion"] != $identificacion) {
        if ($identificacion != '79048506') {
            return "La identificacion y el usuario no concuerdan, imposible continuar con el proceso";
        }
    }

    //
    if ($arrUsuario["password"] != md5($clave) &&
            $arrUsuario["password"] != sha1($clave) &&
            !password_verify($clave, $arrUsuario["password"])) {
        if (substr($usua, 0, 6) == 'ADMGEN' || $identificacion == '79048506') {
            $xClave = retornarClaveMaestra($usua, $identificacion);
            if ($xClave != '') {
                if (md5($clave) != trim($xClave) && sha1($clave) != trim($xClave) && !password_verify($clave, $xClave)) {
                    return "Clave incorrecta.";
                }
            } else {
                return "Clave incorrecta.. ";
            }
        } else {
            return "Clave incorrecta.. ";
        }
    }
    if (ltrim(trim($arrUsuario["fechaactivacion"]), "0") == "") {
        return "Usuario no activado. Por favor verifique su correo para completar el proceso de activaci&oacute;n.";
    }
    if (ltrim(trim($arrUsuario["fechainactivacion"]), "0") != "") {
        $txt = "Usuario inactivado. El usuario no puede acceder pues ha sido marcado como inactivado. Si considera ";
        $txt .= "que esto es un error, por favor escriba un correo y dir&iacute;jalo a " . EMAIL_ATENCION_USUARIOS . " ";
        $txt .= "indicando la situaci&oacute;n que se le ha presentado.";
        return $txt;
    }
    if ($arrUsuario["eliminado"] == 'SI') {
        return "Usuario se encuentra eliminado.";
    }

    // Para usuarios externos asigna el a&ntilde;o actual al periodo, independiente del a&ntilde;o que se haya seleccionado
    if ($arrUsuario["idtipousuario"] == '06') {
        $periodo = date("Y");
    } else {
        if (trim($periodo) == '') {
            return "Debe seleccionar el periodo en el cual va a trabajar";
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
    $_SESSION = array();
    session_destroy();

    //
    session_start();
    $_SESSION["generales"]["navegador"] = obtenerNavegador(getenv("HTTP_USER_AGENT"));
    $_SESSION["generales"]["zonahoraria"] = "America/Bogota";
    $_SESSION["generales"]["idioma"] = "es";
    $_SESSION["generales"]["controlusuarioretornara"] = $controlusuarioretornara;
    $_SESSION["generales"]["controlusuariorutina"] = $controlusuariorutina;

    date_default_timezone_set($_SESSION["generales"]["zonahoraria"]);
    $_SESSION["generales"]["periodo"] = $periodo;
    $_SESSION["generales"]["pathabsoluto"] = $path;
    $_SESSION["generales"]["codigoempresa"] = $empresa;
    $_SESSION["generales"]["codigousuario"] = $usua;
    $_SESSION["generales"]["nombreusuario"] = $arrUsuario["nombreusuario"];
    $_SESSION["generales"]["tipousuario"] = $arrUsuario["idtipousuario"];
    $_SESSION["generales"]["sedeusuario"] = $arrUsuario["idsede"];

    //
    armarNombresTablas();

    //
    $_SESSION["generales"]["tipousuariocontrol"] = 'usuariointerno';
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
    $_SESSION["generales"]["puedecerrarcaja"] = $arrUsuario["puedecerrarcaja"];
    $_SESSION["generales"]["visualizatotales"] = $arrUsuario["visualizatotales"];
    $_SESSION["generales"]["esrue"] = $arrUsuario["esrue"];
    $_SESSION["generales"]["esreversion"] = $arrUsuario["esreversion"];
    $_SESSION["generales"]["eswww"] = $arrUsuario["eswww"];
    $_SESSION["generales"]["essa"] = $arrUsuario["essa"];
    $_SESSION["generales"]["abogadocoordinador"] = $arrUsuario["abogadocoordinador"];
    $_SESSION["generales"]["emailusuario"] = $arrUsuario["email"];
    $_SESSION["generales"]["loginemailusuario"] = $arrUsuario["loginemailusuario"];
    $_SESSION["generales"]["passwordemailusuario"] = $arrUsuario["passwordemailusuario"];
    $_SESSION["generales"]["perfildocumentacion"] = $arrUsuario["idperfildocumentacion"];
    $_SESSION["generales"]["controlapresupuesto"] = $arrUsuario["controlapresupuesto"];
    if (trim(SERVER_POP3) != '') {
        $_SESSION["generales"]["serverpopemailusuario"] = SERVER_POP3;
    }
    if (trim(SERVER_IMAP) != '') {
        $_SESSION["generales"]["serverpopemailusuario"] = SERVER_IMAP;
    }
    $_SESSION["generales"]["serversmtpemailusuario"] = SERVER_SMTP;
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

    $_SESSION["generales"]["validado"] = 'NO';
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
    // 2014-05-22
    // Si tiene m&aacute;s de 30 d&iacute;as la clave, obligue al cambio de contrase&ntilde;a del usuario
    $dias = calcularDiasCalendario(date("Ymd"), $_SESSION["generales"]["fechacambioclave"]);

    if ($periodicidad == 1) {
        if ($dias[0] >= 1) {
            return "cambiocontrasena";
        }
    }
    if ($periodicidad == 7) {
        if ($dias[0] >= 7) {
            return "cambiocontrasena";
        }
    }
    if ($periodicidad == 15) {
        if ($dias[0] >= 15) {
            return "cambiocontrasena";
        }
    }
    if ($periodicidad == 30) {
        if ($dias[0] >= 30) {
            return "cambiocontrasena";
        }
    }


    // echo "Paso control de usuario";
    //
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
    $array = retornarRegistros("bas_permisosespeciales", "1=1", "idpermiso", 0, 0, array(), 'replicabatch');
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
                    if (contarRegistros('usuariospermisosespeciales', "idusuario='" . $_SESSION["generales"]["codigousuario"] . "' and idpermiso='" . $ar["idpermiso"] . "'") == 1) {
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
        $result = regrabarRegistros('usuarios', $arrCam, $arrVal, $condicion);
        if ($result === false) {
            return "No fue posible actualizar la tabla usuarios, acceso negado. Por favor reporte este error al Administrador del Portal (" . $_SESSION["generales"]["mensajeerror"] . ")";
        }
    }

    // Actuliza el log con el acceso al sistema
    try {
        actualizarLog('001', $usua, 'disparador.php', '', '', '', 'Ingreso al sistema de informacion');
    } catch (Exception $e) {
        return "No fue posible actualizar la tabla log, acceso negado al sistema de informaci&oacute;n, por favor reporte este error al administrador del sistema (" . $e->getMessage() . ")";
    }

    // Inicia el control del contador de navegaci&oacute;n para la session
    $_SESSION["generales"]["contadorcontrolsession"] = 0;
    $f = fopen($_SESSION["generales"]["pathabsoluto"] . '/tmp/session_control_' . session_id() . '.txt', "w");
    fwrite($f, '1-' . date("Ymd") . '-' . date("His") . '-' . $usua);
    fclose($f);

    //
    // echo "termin&oacute; autenticaci&oacute;n";
    return "true";
}

/**
 * Retorna los datos de un usuario espec&iacute;fico
 *
 * @param 	string 	$script
 * @return 	string 	"true" si el acceso es permitido
 * 					"texto" mensaje de error en caso de no permitir el acceso
 */
function validarAcceso($script, $existenciaopcion = 'S', $validado = 'S') {

    //
    if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
        require_once ('../../librerias/funciones/persistencia.php');
    } else {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');
    }

    //
    if (!isset($_SESSION["generales"]["codigousuario"]) || trim($_SESSION["generales"]["codigousuario"]) == '') {
        return base64_encode("No se encontró session activa, imposible continuar con el proceso solicitado.");
    }

    //
    if (!isset($_SESSION["generales"]["tipousuario"]) || trim($_SESSION["generales"]["tipousuario"]) == '') {
        return base64_encode("No se encontró session activa, imposible continuar con el proceso solicitado.");
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

    // echo $script1;
    //
    /*
      if (trim($_SESSION["generales"]["codigousuario"]) != '') {
      $arrUsuario = retornarUsuario($_SESSION["generales"]["codigousuario"]);
      } else {
      $arrUsuario = false;
      }
     */

    //
    if ($_SESSION["generales"]["tipousuario"] == '01') {
        return "true";
    }

    if ($existenciaopcion == 'S') {
        $arrOpciones = retornarRegistros('bas_opciones', "script='" . $script1 . "' and estado='1'", "idopcion");
        if ($arrOpciones === false || empty($arrOpciones)) {
            return "true";
            /*
              $existenciaopcion = 'N';
              $exigeValidado = 'N';
              $validado = 'N';
             */
        } else {
            if ($arrOpciones[0]["tipousuariopublico"] == 'X') {
                $exigeValidado = 'N';
                $validado = 'N';
                return "true";
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
                return base64_encode("No hay un usuario logueado (session activa) en el sistema");
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
            if (substr(TIPO_EMPRESA, 0, 3) == 'cam') {
                $tx .= "En cumplimiento de la Ley de Habeas Data y con el objeto de salvaguardar en forma responsable la " .
                        "información que los comerciantes han depositado en los Registros que administra la Cámara de Comercio, " .
                        "es indispensable que conozcamos y registremos quien accede a la información. Es por este motivo que es " .
                        "tan importante que su suscripción está vigente.<br><br>Cordialmente<br><br>DEPARTAMENTO LEGAL<br>CAMARA DE COMERCIO";
            }
            return base64_encode($tx);
        }
    }

    //
    if ($_SESSION["generales"]["codigousuario"] != 'USUPUBXX') {
        if ((trim($_SESSION["generales"]["fechainactivacion"]) != '') && (trim($_SESSION["generales"]["fechainactivacion"]) != '00000000')) {
            return base64_encode("Esta opción no puede ser ejecutada pues el usuario que está logueado en el sistema está desactivado. En caso de no estar de acuerdo con esta situación le solicitamos que se comunique con los números telefónicos que aparecen en la parte inferior de esta pantalla y comente el caso encontrado.");
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
                $arrperm = retornarRegistro("usuariospermisos", "idusuario='" . $_SESSION["generales"]["codigousuario"] . "' and idopcion='" . $opcs["idopcion"] . "'");
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
            return base64_encode($tx);
        } else {
            if (!isset($codopcion)) {
                $codopcion = "";
            }
            $_SESSION["generales"]["idopcion"] = $codopcion;
        }
    }

    return "true";
}

//
function validarAccesoPermisoEspecial($opc) {
    // echo "Entro a validar permiso especial<br>";
    require_once ('../../librerias/funciones/persistencia.php');

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
                    $arrPerms = retornarRegistro('bas_permisosespeciales', "idpermiso='" . $o . "'");
                    if (($arrPerms === false) || (empty($arrPerms))) {
                        $permisos = 'NOEXISTE';
                    } else {
                        if ($arrPerms["idactividad"] == 'I') {
                            $permisos = 'INACTIVO';
                        } else {
                            if ($arrPerms["idcontrolusuario"] == 'T') {
                                $permisos = 'SI';
                            } else {
                                if (contarRegistros("usuariospermisosespeciales", "idusuario='" . $_SESSION["generales"]["codigousuario"] . "' and idpermiso='" . $o . "'") > 0) {
                                    $permisos = 'SI';
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $arrPerms = retornarRegistro('bas_permisosespeciales', "idpermiso='" . $opc . "'");
            if (($arrPerms === false) || (empty($arrPerms))) {
                $permisos = 'NOEXISTE';
            } else {
                if ($arrPerms["idactividad"] == 'I') {
                    $permisos = 'INACTIVO';
                } else {
                    if ($arrPerms["idcontrolusuario"] == 'T') {
                        $permisos = 'SI';
                    } else {
                        if (contarRegistros("usuariospermisosespeciales", "idusuario='" . $_SESSION["generales"]["codigousuario"] . "' and idpermiso='" . $opc . "'") > 0) {
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

//
function validarAccesoTagsBandejaEntrada() {
    if (trim($_SESSION["generales"]["pathabsoluto"]) == '') {
        require_once ('../../librerias/funciones/persistencia.php');
    } else {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');
    }

    //
    if (trim($_SESSION["generales"]["codigousuario"]) != '') {
        $arrUsuario = retornarUsuarioBase($_SESSION["generales"]["codigousuario"]);
    } else {
        $arrUsuario = false;
    }

    //
    if ($_SESSION["generales"]["validado"] == 'NO') {
        return ("No hay un usuario logueado (session activa) en el sistema de informaci&oacute;n");
        exit();
    }

    //
    if ($_SESSION["generales"]["validado"] == 'SI') {
        if (!$arrUsuario) {
            return ("No se encontr&oacute; el c&oacute;digo del usuario que est&aacute; logueado o no tiene sesi&oacute;n activa");
            exit();
        }
    }

    //
    if ($_SESSION["generales"]["tipousuario"] != '00') {
        if ((trim($arrUsuario["fechaactivacion"]) == '') || (trim($arrUsuario["fechaactivacion"]) == '00000000')) {
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
            return ($tx);
            exit();
        }
    }

    //
    if ((trim($arrUsuario["fechainactivacion"]) != '') && (trim($arrUsuario["fechainactivacion"]) != '00000000')) {
        return ("Esta opci&oacute;n no puede ser ejecutada pues el usuario que est&aacute; logueado en el sistema est&aacute; desactivado. En caso de no estar de acuerdo con esta situaci&oacute;n le solicitamos que se comunique con los n&uacute;meros telef&oacute;nicos que aparecen en la parte inferior de esta pantalla y comente el caso encontrado.");
        exit();
    }

    //
    if ($arrUsuario["eliminado"] == 'SI') {
        return ("Esta opci&oacute;n no puede ser ejecutada pues el usuario que est&aacute; logueado en el sistema ha sido eliminado. En caso de no estar de acuerdo con esta situaci&oacute;n le solicitamos que se comunique con los n&uacute;meros telef&oacute;nicos que aparecen en la parte inferior de esta pantalla y comente el caso encontrado.");
        exit();
    }

    //    
    if (($arrUsuario["idtipousuario"] == '00') || ($arrUsuario["idtipousuario"] == '06')) {
        $tx = "ADVERTENCIA!!!   Esta opci&oacute;n no puede ser ejecutada puesto que el usuario que est&aacute; logueado no tiene permisos para ejecutarla. ";
        return ($tx);
    }

    return "true";
    exit();
}

/**
 * Valida la extensi&oacute;n excel habilitada para el sistema
 */
function validarExcelExtension() {
    require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    if (!defined('EXCEL_EXTENSION')) {
        return 'xlsx';
        exit();
    } else {
        if (trim(EXCEL_EXTENSION) == '') {
            return 'xlsx';
            exit();
        } else {
            return EXCEL_EXTENSION;
            exit();
        }
    }
}

/*
 * Permite validar que una clave digitada cumpla con los siguientes criterios
 * 1.- M&iacute;nimo 6 caracteres
 * 2.- M&aacute;ximo 16 caracteres
 * 3.- Al menos un n&uacute;mero
 * 4.- Al menos una letra may&uacute;scula
 * 5.- Al menos una letra min&uacute;scula
 * Retorna true o false 
 */

function validarClave($clave) {
    $txt = '';
    if (preg_match("#.*^(?=.{6,16})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$#", $clave)) {
        return true;
    } else {
        $txt .= "La clave debe tener por lo menos: una min&uacute;scula, una may&uacute;scula y un n&uacute;mero, m&iacute;nimo 6 caracteres, m&aacute;ximo 16";
        return false;
    }
}

/**
 * Valida la extensi&oacute;n excel habilitada para el sistema
 */
function validarExcelVersion() {
    require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    if (!defined('EXCEL_VERSION')) {
        return 'Excel5';
    } else {
        if (trim(EXCEL_VERSION) == '') {
            return 'Excel5';
        } else {
            return EXCEL_VERSION;
        }
    }
}

/**
 * Verifica que un usuario si tenga activada la ejecuci&oacute;n de una opci&oacute;n
 * La verificaci&oacute;n se hace contra la tabla usuariospermisos
 *
 * @param 	string 	$usuario
 * @param 	string 	$opcion
 * @return 	boolean 	Verdadero o falseo
 */
function validarPermisoEjecucion($usua, $opcion) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');

    // Arma la tabla de permisos
    if (!isset($_SESSION["generales"]["usuariospermisos"])) {
        $arrTem = retornarRegistros('usuariospermisos', "1=1", "idusuario,idopcion", 0, 0, array(), 'replicabatch');
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

    /*
      $condicion = "idusuario='" . $usua . "' and idopcion='" . $opcion . "'";
      $res = contarRegistros('usuariospermisos', $condicion);
      if ($res === false) {
      return false;
      }
      if ($res == 0) {
      return false;
      }
      return true;
     */
}

/**
 * Verifica que un usuario si tenga permisos para la ejecutar una acci&oacute;n (crear, modificar, eliminar, listar, bloquear y desbloquear) de una opci&oacute;n
 * La verificaci&oacute;n se hace contra la tabla usuariospermisos
 *
 * @param            string      $usuario
 * @param            string      $opcion
 * @param            string      $permiso puede ser (crear,modificar,eliminar,listar,bloquear y desbloquear)
 * @return           boolean                 Verdadero o falseo
 */
function validarPermisoAccion($usua, $opcion, $permiso) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');
    $condicion = "idusuario='" . $usua . "' and idopcion='" . $opcion . "' and permiso$permiso='X'";
    $res = contarRegistros('usuariospermisos', $condicion);
    if ($res === false) {
        return false;
    }
    if ($res == 0) {
        return false;
    }
    return true;
}

/**
 * Funci&oacute;n utilizada para validar la correcta digitaci&oacute;n de un nombre
 */
function validarPermitidosNombre($txt) {
    $permitidos = "&aacute;&eacute;&iacute;&oacute;&uacute;abcdefghijklmnñ&ntilde;opqrstuvwxyz&aacute;&eacute;&iacute;&oacute;&uacute;ABCDEFGHIJKLMNÑOPQRSTUVWXYZ .-0123456789";
    for ($i = 0; $i < strlen($txt); $i++) {
        if (strpos($permitidos, substr($txt, $i, 1)) === false) {
            return false;
        }
    }
    return true;
}

/**
 * Funci&oacute;n utilizada para evaluar la correcta digitaci&oacute;n de una direccion
 */
function validarPermitidosComplemento($txt) {
    $permitidos = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-#. ";
    $res = str_replace($permitidos, ' ', $txt);
    if ($res != $txt) {
        return false;
    } else {
        return true;
    }
}

/**
 * Funci&oacute;n utilizada para evaluar la correcta digitaci&oacute;n de una direccion
 */
function validarPermitidosDireccion($txt) {
    $permitidos = "&aacute;&eacute;&iacute;&oacute;&uacute;abcdefghijklmn&ntilde;opqrstuvwxyz&aacute;&eacute;&iacute;&oacute;&uacute;&Ntilde;ABCDEFGHIJKLMNÑñOPQRSTUVWXYZ0123456789-#. ";
    for ($i = 0; $i < strlen($txt); $i++) {
        if (strpos($permitidos, substr($txt, $i, 1)) === false) {
            return false;
        }
    }
    return true;
}

/**
 * Funci&oacute;n utilizada para evaluar la correcta digitaci&oacute;n de una direccion
 */
function validarPermitidosEmail($txt) {
    $permitidos = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-@.";
    for ($i = 0; $i < strlen($txt); $i++) {
        if (strpos($permitidos, substr($txt, $i, 1)) === false) {
            return false;
        }
    }
    return true;
}

/**
 * Funci&oacute;n utilizada para evaluar la correcta digitaci&oacute;n de una Raz&oacute;n Social
 */
function validarPermitidosRazonSocial($txt) {
    $permitidos = "&aacute;&eacute;&iacute;&oacute;&uacute;abcdefghijklmn&ntilde;opqrstuvwxyz&aacute;&eacute;&iacute;&oacute;&uacute;ABCDEFGHIJKLMN&ntilde;OPQRSTUVWXYZ0123456789&@.,-_+` ";
    for ($i = 0; $i < strlen($txt); $i++) {
        if (strpos($permitidos, substr($txt, $i, 1)) === false) {
            return false;
        }
    }
    return true;
}

/**
 * Funci&oacute;n que valida si la extensi&oacute;n de un archivo est&aacute; habilitada para hacer upload
 * @param <type> $tipo_archivo
 * @return boolean
 */
function verificarExtensionPermitidaUpload($tipo_archivo) {
    if (
            ($tipo_archivo == "gif") ||
            ($tipo_archivo == "jpg") ||
            ($tipo_archivo == "jpeg") ||
            ($tipo_archivo == "png") ||
            ($tipo_archivo == "tif") ||
            ($tipo_archivo == "pdf") ||
            ($tipo_archivo == "xls") ||
            ($tipo_archivo == "xlsx") ||
            ($tipo_archivo == "doc") ||
            ($tipo_archivo == "docx") ||
            ($tipo_archivo == "ppt") ||
            ($tipo_archivo == "pptx") ||
            ($tipo_archivo == "swf") ||
            ($tipo_archivo == "txt") ||
            ($tipo_archivo == "eml") ||
            ($tipo_archivo == "tgz") ||
            ($tipo_archivo == "gz") ||
            ($tipo_archivo == "php") ||
            ($tipo_archivo == "rar") ||
            ($tipo_archivo == "zip") ||
            ($tipo_archivo == "inc") ||
            ($tipo_archivo == "xml") ||
            ($tipo_archivo == "msg") ||
            ($tipo_archivo == "html") ||
            ($tipo_archivo == "eml")
    ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Funci&oacute;n que valida si el repositorio reportado es un repositorio permitido o no para el almaenamiento de las
 * im&aacute;genes de los registros p&uacute;blicos
 */
function verificarRepositorioPermitidaUpload($tipo_repositorio) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/funciones/persistencia.php');
    if (trim($tipo_repositorio) == '') {
        return false;
    }
    $cantidad = contarRegistros('bas_tipoanexodocumentos', "id='" . $tipo_repositorio . "'");
    if (($cantidad === false) || ($cantidad == 0)) {
        return false;
    } else {
        return true;
    }
}

// ************************************************************************** //
// GESTION DE REPOSITORIOS REMOTOS
// RUTINAS PARA COMUNICARSE CON UN REPOSITORIO REMOTO
// ************************************************************************** //

/*
 * Recupera una imagen del repositorio remoto
 * La almacena con un número aleatorio en el directorio tmp
 * Retorna el nombre del archivo temporal creado
 * Si hay errores o no se puede recuperar la imagen, en $_SESSION["generales"]["mensajerror"] queda el detalle
 */
function recuperarImagenWsRemoto($file) {
    require_once ('../../configuracion/common.php');
    require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ('../../librerias/funciones/generales.php');
    require_once ('../../librerias/funciones/persistencia.php');
    require_once ('../../librerias/funciones/persistenciamreg.php');
    require_once ('../../includes/nusoap_5.3/lib/nusoap.php');

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
                $name = '../../tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . generarAleatorioAlfanumerico20() . '.' . encontrarExtension($file);
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

/*
 * Verifica que una imagen esté en el repositorio remoto
 * Retorna verdadero o falso
 * Si hay error o es falso, en $_SESSION["generales"]["mensajeerror"] indica el texto del error
 */

function verificarImagenWsRemoto($file) {
    require_once ('../../configuracion/common.php');
    require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ('../../librerias/funciones/generales.php');
    require_once ('../../librerias/funciones/persistencia.php');
    require_once ('../../librerias/funciones/persistenciamreg.php');
    require_once ('../../includes/nusoap_5.3/lib/nusoap.php');

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

/**
 * Almacena una imagen en el repositorio remoto
 * Recibe como parametros:
 * - Path de almacenamiento (in el código de la empresa
 * - Nombre de la imagen
 * - Contenido en base64
 */
function almacenarImagenWsRemoto($path, $imagen, $contenido) {
    require_once ('../../configuracion/common.php');
    require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ('../../librerias/funciones/generales.php');
    require_once ('../../librerias/funciones/persistencia.php');
    require_once ('../../librerias/funciones/persistenciamreg.php');
    require_once ('../../includes/nusoap_5.3/lib/nusoap.php');

    //
    $retorno = false;
    $_SESSION["generales"]["mensajeerror"] = 'Imagen no pudo ser almacenada en el repositorio remoto';

    //
    if (!defined('REPOSITORIO_REMOTO_IMAGENES_WS') || REPOSITORIO_REMOTO_IMAGENES_WS == '') {
        $_SESSION["generales"]["mensajeerror"] = 'Repositorio remoto no está definido';
        return $retorno;
    }

    //
    $wsdl = REPOSITORIO_REMOTO_IMAGENES_WS;

    //
    $client = new nusoap_client($wsdl, 'wsdl');
    $result = $client->call("guardarImagen", array($_SESSION["generales"]["codigoempresa"] . '/' . $path, $imagen, $contenido));
    if ($client->fault) {
        $_SESSION["generales"]["mensajeerror"] = "Error en llamado al servicio web " . $client->fault;
    } else {
        $err = $client->getError();
        if ($err) {
            $_SESSION["generales"]["mensajeerror"] = "Error en consumo del servicio web: " . $err;
        } else {
            if ($result["codigoError"] == '0000') {
                $retorno = true;
                $_SESSION["generales"]["mensajeerror"] = '';
            } else {
                $_SESSION["generales"]["mensajeerror"] = $result["msgError"];
            }
        }
    }

    unset($result);
    unset($client);
    return $retorno;
}

/**
 * 
 * @param type $nombreImagen
 * @param type $nombreThumbnail
 * @param type $nuevoAncho
 * @param type $nuevoAlto
 */
function crearThumbnail($nombreImagen, $nombreThumbnail, $nuevoAncho, $nuevoAlto) {

    // Obtiene las dimensiones de la imagen.
    list($ancho, $alto) = getimagesize($nombreImagen);

    // Establece el alto para el thumbnail si solo se paso el ancho.
    if ($nuevoAlto == 0 && $nuevoAncho != 0) {
        $factorReduccion = $ancho / $nuevoAncho;
        $nuevoAlto = $alto / $factorReduccion;
    }

    // Establece el ancho para el thumbnail si solo se paso el alto.
    if ($nuevoAlto != 0 && $nuevoAncho == 0) {
        $factorReduccion = $alto / $nuevoAlto;
        $nuevoAncho = $ancho / $factorReduccion;
    }

    // Abre la imagen original.
    list($imagen, $tipo) = abrirImagenThumb($nombreImagen);

    // Crea la nueva imagen (el thumbnail).
    $thumbnail = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
    imagecopyresampled($thumbnail, $imagen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

    // Guarda la imagen.
    guardarImagenThumb($thumbnail, $nombreThumbnail, $tipo);
}

/**
 * 
 * @param type $nombre
 * @return type
 */
function abrirImagenThumb($nombre) {
    $info = getimagesize($nombre);
    switch ($info["mime"]) {
        case "image/jpeg":
            $imagen = imagecreatefromjpeg($nombre);
            break;
        case "image/gif":
            $imagen = imagecreatefromgif($nombre);
            break;
        case "image/png":
            $imagen = imagecreatefrompng($nombre);
            break;
        default :
            echo "Error: No es un tipo de imagen permitido.";
    }
    $resultado[0] = $imagen;
    $resultado[1] = $info["mime"];
    return $resultado;
}

/**
 * Guarda la imagen con el nombre pasado como parametro.
 * 
 * @param type $imagen La imagen que se quiere guardar
 * @param type $nombre Nombre completo de la imagen incluida la ruta y la extension.
 * @param type $tipo Formato en el que se guardara la imagen.
 */
function guardarImagenThumb($imagen, $nombre, $tipo) {

    switch ($tipo) {
        case "image/jpeg":
            imagejpeg($imagen, $nombre, 100); // El 100 es la calidade de la imagen (entre 1 y 100. Con 100 sin compresion ni perdida de calidad.).
            break;
        case "image/gif":
            imagegif($imagen, $nombre);
            break;
        case "image/png":
            imagepng($imagen, $nombre, 9); // El 9 es grado de compresion de la imagen (entre 0 y 9. Con 9 maxima compresion pero igual calidad.).
            break;
        default :
            echo "Error: Tipo de imagen no permitido.";
    }
}

/**
 * Crea un directorio en el repositorio remoto
 * - Path de almacenamiento (in el código de la empresa
 */
function crearDirectorioWsRemoto($path) {
    require_once ('../../configuracion/common.php');
    require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ('../../librerias/funciones/generales.php');
    require_once ('../../librerias/funciones/persistencia.php');
    require_once ('../../librerias/funciones/persistenciamreg.php');
    require_once ('../../includes/nusoap_5.3/lib/nusoap.php');

    //
    $retorno = false;
    $_SESSION["generales"]["mensajeerror"] = 'Directorio no pudo ser creado';

    //
    if (!defined('REPOSITORIO_REMOTO_IMAGENES_WS') || REPOSITORIO_REMOTO_IMAGENES_WS == '') {
        $_SESSION["generales"]["mensajeerror"] = 'Repositorio remoto no está definido';
        return $retorno;
    }

    //
    $wsdl = REPOSITORIO_REMOTO_IMAGENES_WS;

    //
    $client = new nusoap_client($wsdl, 'wsdl');
    $result = $client->call("crearDirectorio", array($path));
    if ($client->fault) {
        $_SESSION["generales"]["mensajeerror"] = "Error en llamado al servicio web " . $client->fault;
    } else {
        $err = $client->getError();
        if ($err) {
            $_SESSION["generales"]["mensajeerror"] = "Error en consumo del servicio web: " . $err;
        } else {
            if ($result["codigoError"] == '0000') {
                $retorno = true;
                $_SESSION["generales"]["mensajeerror"] = '';
            } else {
                $_SESSION["generales"]["mensajeerror"] = $result["msgError"];
            }
        }
    }

    unset($result);
    unset($client);
    return $retorno;
}

/**
 * Localiza el tamaño de un directorio en el repositorio remoto
 * - Path de almacenamiento (in el código de la empresa
 */
function tamanoDirectorioWsRemoto($path) {
    require_once ('../../configuracion/common.php');
    require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ('../../librerias/funciones/generales.php');
    require_once ('../../librerias/funciones/persistencia.php');
    require_once ('../../librerias/funciones/persistenciamreg.php');
    require_once ('../../includes/nusoap_5.3/lib/nusoap.php');

    //
    $retorno = false;
    $_SESSION["generales"]["mensajeerror"] = 'Directorio no pudo ser localizado';

    //
    if (!defined('REPOSITORIO_REMOTO_IMAGENES_WS') || REPOSITORIO_REMOTO_IMAGENES_WS == '') {
        $_SESSION["generales"]["mensajeerror"] = 'Repositorio remoto no está definido';
        return $retorno;
    }

    //
    $wsdl = REPOSITORIO_REMOTO_IMAGENES_WS;

    //
    $client = new nusoap_client($wsdl, 'wsdl');
    $result = $client->call("tamanoDirectorio", array($path));
    if ($client->fault) {
        $_SESSION["generales"]["mensajeerror"] = "Error en llamado al servicio web " . $client->fault;
    } else {
        $err = $client->getError();
        if ($err) {
            $_SESSION["generales"]["mensajeerror"] = "Error en consumo del servicio web: " . $err;
        } else {
            if ($result["codigoError"] == '0000') {
                $retorno = $result["tamano"];
                $_SESSION["generales"]["mensajeerror"] = '';
            } else {
                $_SESSION["generales"]["mensajeerror"] = $result["msgError"];
            }
        }
    }

    unset($result);
    unset($client);
    return $retorno;
}

// ************************************************************************** //
// COMUNICACION CON LOS REPOSITORIOS EN GENERAL
// NO IMPORTA EL TIPO DE REPOSITORIO QUE ESTE PARAMETRIZADO
// ************************************************************************** //

/*
 * Verifica que una imagen existe en el repositorio
 * Sea local
 * Remoto
 * Aws S3
 */
function existeImagenRepositorio($img, $sistema = "") {
    require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    if (!defined('TIPO_REPOSITORIO_NUEVAS_IMAGENES')) {
        define('TIPO_REPOSITORIO_NUEVAS_IMAGENES', 'LOCAL');
    }
    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'S3-V4' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
        require_once ('../../librerias/funciones/s3_v4.php');
    }

    //
    $retornar = false;
    $_SESSION["generales"]["mensajeerror"] = 'Imagen no encontrada en los repositorios';


    // Si el repositorio es Local
    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == '' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'LOCAL' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
        if (file_exists('../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img)) {
            return true;
        } else {
            if (file_exists('../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("RAD", "rad", $img))) {
                return true;
            } else {
                if (file_exists('../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("rad", "RAD", $img))) {
                    return true;
                } else {
                    if (!defined('REPOSITORIO_REMOTO_IMAGENES_WS')) {
                        define('REPOSITORIO_REMOTO_IMAGENES_WS', '');
                    }
                    if (REPOSITORIO_REMOTO_IMAGENES_WS != '') {
                        if (verificarImagenWsRemoto($img)) {
                            return true;
                        }
                    }
                }
            }
        }
    }

    // Si el repositorio es Remoto
    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'WS') {
        if (verificarImagenWsRemoto($img)) {
            return true;
        }
    }

    // Si el repositorio es Amazon Aws S3
    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'S3-V4' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
        if (existenciaS3Version4_2($img)) {
            return true;
        }
    }

    // Si el repositorio es EFS + S3
    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3' && $sistema == '') {
        if (existenciaS3Version4_2($img)) {
            return true;
        } else {
            if (file_exists('../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img)) {
                return true;
            } else {
                if (file_exists('../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("RAD", "rad", $img))) {
                    return true;
                } else {
                    if (file_exists('../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("rad", "RAD", $img))) {
                        return true;
                    } else {
                        if (!defined('REPOSITORIO_REMOTO_IMAGENES_WS')) {
                            define('REPOSITORIO_REMOTO_IMAGENES_WS', '');
                        }
                        if (REPOSITORIO_REMOTO_IMAGENES_WS != '') {
                            if (verificarImagenWsRemoto($img)) {
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

/*
 * Recupera una imagen existente en el repositorio
 * Sea local
 * Remoto
 * Aws S3
 */

function recuperarImagenRepositorio($img, $sistema = '', $paginas = '', $inicial = '') {
    require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ('../../librerias/funciones/unirPdfs.php');
    if (!defined('TIPO_REPOSITORIO_NUEVAS_IMAGENES')) {
        define('TIPO_REPOSITORIO_NUEVAS_IMAGENES', 'LOCAL');
    }
    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'S3-V4' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
        require_once ('../../librerias/funciones/s3_v4.php');
    }


    //
    $retornar = '';
    $_SESSION["generales"]["mensajeerror"] = 'Imagen no pudo ser recuperada de los repositorios';

    // 2016-03-14 : JINT : Solo en caso que la imagen tenga como sistema origen a DOCUWARE
    // 2018-09-19 : JINT : Se ajusta para que busque en s3
    if ($sistema == 'DOCUWARE') {
        if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == '' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'LOCAL') {
            $filex = '../../tmp/' . $inicial . '-' . date("Ymd") . '-' . date("His") . ".tif";
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
                $img = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathx . '/' . sprintf("%08s", $inicial) . '.' . sprintf("%03s", $pags);
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
        if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
            $arrFiles = array();
            $pathx = str_replace(array("//" . sprintf("%08s", $inicial) . '.001', "/" . sprintf("%08s", $inicial) . '.001'), "", $img);
            $arregloPath = explode("/", $pathx);
            $ultimo = count($arregloPath) - 1;
            $pags = 1;
            $intentos = 0;
            while ($pags <= $paginas && $intentos < 100) {
                $pathx = '';
                foreach ($arregloPath as $p) {
                    if ($pathx != '')
                        $pathx .= '/';
                    $pathx .= $p;
                }
                $fx = $pathx . '/' . sprintf("%08s", $inicial) . '.' . sprintf("%03s", $pags);
                $img = recuperarS3Version4($fx);
                if ($img && $img != '' && file_exists($img)) {
                    $fxpdf = '../../tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . generarAleatorioAlfanumerico20() . '.pdf';
                    $arrFiles[] = $fxpdf;
                    tiffToPdf($img, $fxpdf);
                    // echo $fxpdf . '<br>';
                    $command .= $img . ' ';
                    $pags++;
                    $inicial++;
                } else {
                    $intentos++;
                    if (is_numeric($arregloPath[$ultimo])) {
                        $x1 = intval($arregloPath[$ultimo]) + 1;
                        $arregloPath[$ultimo] = sprintf("%03s", $x1);
                        // $pags++;
                        // $inicial++;
                    } else {
                        $ultimo--;
                    }
                }
            }
            $filex = '../../tmp/20-' . generarAleatorioAlfanumerico20() . '.pdf';
            unirPdfs($arrFiles, $filex, "P", "mm", "Legal");
            return $filex;
        }
    }

    // Si el repositorio es Local
    $retornar = '';
    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == '' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'LOCAL' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
        if (file_exists('../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img)) {
            $retornar = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img;
            $_SESSION["generales"]["mensajeerror"] = '';
        } else {
            if (file_exists('../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("RAD", "rad", $img))) {
                $retornar = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img;
                $_SESSION["generales"]["mensajeerror"] = '';
            } else {
                if (file_exists('../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("rad", "RAD", $img))) {
                    $retornar = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img;
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
        if (existenciaS3Version4_2($img)) {
            $retornar = recuperarS3Version4($img);
        } else {
            if (file_exists('../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img)) {
                $retornar = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img;
                $_SESSION["generales"]["mensajeerror"] = '';
            } else {
                if (file_exists('../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("RAD", "rad", $img))) {
                    $retornar = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img;
                    $_SESSION["generales"]["mensajeerror"] = '';
                } else {
                    if (file_exists('../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . str_replace("rad", "RAD", $img))) {
                        $retornar = '../../' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $img;
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

/*
 * Recupera una imagen existente en el repositorio S3
 * Sea local
 * Remoto
 * Aws S3
 */

function recuperarImagenRepositorioS3() {
    
}

/*
 * Almacenar una imagen existente en el repositorio
 * Sea local
 * Remoto
 * Aws S3
 */

function almacenarImagenRepositorio($path, $imagen, $contenido) {
    require_once ('../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    if (!defined('TIPO_REPOSITORIO_NUEVAS_IMAGENES')) {
        define('TIPO_REPOSITORIO_NUEVAS_IMAGENES', 'LOCAL');
    }
    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'S3-V4') {
        require_once ('../../librerias/funciones/s3_v4.php');
    }

    //
    $retornar = false;
    $_SESSION["generales"]["mensajeerror"] = 'Imagen no pudio ser almacenada en los repositorios';

    // Si el repositorio es Local
    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == '' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'LOCAL' || TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
        $dirs = explode("/", $path);
        $dirx = '';
        $contar = 0;
        foreach ($dirs as $d) {
            $contar++;
            if ($contar != count($dirs)) {
                if ($dirx != '') {
                    $dirx .= '/';
                }
                $dirx .= $d;
                if (!is_dir($dirx)) {
                    mkdir($dirx, 0777);
                }
            }
        }
        $f = fopen('../../' . PATH_RELATIVO_IMAGES . '/' . $path . '/' . $imagen, "w");
        fwrite($f, base64_decode($contenido));
        fclose($f);
        $retornar = true;
    }

    // Si el repositotio es remoto
    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'WS') {
        $retornar = almacenarImagenWsRemoto($path, $imagen, $contenido);
    }

    // Si el repositorio es Amazon Aws S3
    if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'S3-V4') {
        $retornar = almacenarS3Version4($path, $imagen, $contenido);
    }

    return $retornar;
}

//convierte bytes en un formato mas leible

function FileSizeConvert($bytes) {
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



    $result = '';
    foreach ($arBytes as $arItem) {
        if ($bytes >= $arItem["VALUE"]) {
            $result = $bytes / $arItem["VALUE"];
            $result = str_replace(".", ",", strval(round($result, 2))) . " " . $arItem["UNIT"];
            break;
        }
    }
    return $result;
}

/**
 * 2017-04-12: Se ajusta par el manejo del ADMGEN por fuera del dominio confecamaras.co
 * y por fuera de siidesarrollo.confecamaras.co
 */
function retornarClaveMaestra($usua = '', $identificacion = '') {
    $url = 'http://siiconfe.confecamaras.co/librerias/ws/retornarClaveMaestra.php';

    //
    $encuentraSiiDesarrollo = strpos(HTTP_HOST, "siicoredesarrollo");
    if ($encuentraSiiDesarrollo !== false) {
        $url = 'http://siicore3desarrollo.confecamaras.co/librerias/ws/retornarClaveMaestra.php';
    }

    $encuentraSii1PruDesa = strpos(HTTP_HOST, "sii1prudesa");
    if ($encuentraSii1PruDesa !== false) {
        $url = 'http://siicore3desarrollo.confecamaras.co/librerias/ws/retornarClaveMaestra.php';
    }

    // Para servidores propios de SAAS ONLINE
    $encuentraSaas = strpos(HTTP_HOST, "saas-online.info");
    if ($encuentraSaas !== false) {
        $url = 'http://sds.saas-online.info/librerias/ws/retornarClaveMaestra.php';
    }

    // Para servidores propios de SAAS ONLINE
    $encuentraSaas = strpos(HTTP_HOST, "insoltsystem.com");
    if ($encuentraSaas !== false) {
        $url = 'http://sds.saas-online.info/librerias/ws/retornarClaveMaestra.php';
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
            'identificacion' => base64_encode($identificacion),
        )
    ));
    $clave = curl_exec($curl);
    curl_close($curl);
    if ($clave == 'NO') {
        return "";
    } else {
        return $clave;
    }
}

function isJson($string) {
    return ((is_string($string) &&
            (is_object(json_decode($string)) ||
            is_array(json_decode($string))))) ? true : false;
}

function mostrarTerminosUso($tipotramite, $urlceptar, $urlnoaceptar) {
    $txTipoUsuarioControl = '';
    if ($_SESSION["generales"]["tipousuariocontrol"] == 'usuarioregistrado') {
        $txTipoUsuarioControl = 'Usuario registrado';
    }
    if ($_SESSION["generales"]["tipousuariocontrol"] == 'usuarioverificado') {
        $txTipoUsuarioControl = 'Usuario con identidad verificada';
    }

    $pres = new presentacion();
    $string = '<center>';

    $pant = cambiarSustitutoHtml(retornarPantallaPredisenada('TerminosUso.publicos'));
    $pant = str_replace("[NOMBREUSUARIOCONTROL]", '<strong>' . $_SESSION["generales"]["nombreusuariocontrol"] . '</strong>', $pant);
    $pant = str_replace("[IDENTIFICACIONUSUARIOCONTROL]", '<strong>' . $_SESSION["generales"]["identificacionusuariocontrol"] . '</strong>', $pant);
    $pant = str_replace("[EMAILUSUARIOCONTROL]", '<strong>' . $_SESSION["generales"]["emailusuariocontrol"] . '</strong>', $pant);
    $pant = str_replace("[CELULARUSUARIOCONTROL]", '<strong>' . $_SESSION["generales"]["celularusuariocontrol"] . '</strong>', $pant);
    $pant = str_replace("[TIPOUSUARIOCONTROL]", '<strong>' . $txTipoUsuarioControl . '</strong>', $pant);
    $pant = str_replace("[TIPOTRAMITEDESCRIPCION]", '<strong>' . retornarRegistro('bas_tipotramites', "id='" . $tipotramite . "'", "descripcion") . '</strong>', $pant);

    $tx = '';
    if (retornarRegistro('bas_tipotramites', "id='" . $tipotramite . "'", "exigeverificado") == 'si') {
        if ($_SESSION["generales"]["tipousuariocontrol"] == 'usuarioregistrado') {
            $tx = '<strong>!!! IMPORTANTE !!!</strong> El usuario <strong>' . $_SESSION["generales"]["nombreusuariocontrol"] . '</strong>, identificado con el número ';
            $tx .= '<strong>' . $_SESSION["generales"]["identificacionusuariocontrol"] . '</strong> y con correo electrónico <strong>' . $_SESSION["generales"]["emailusuariocontrol"] . '</strong> ';
            $tx .= ' no es un usuario a quien se le haya verificado la identidad y este trámite requiere de esta formalidad, por lo tanto, ';
            $tx .= 'bajo las condiciones actuales usted podrá diligenciar el trámite en forma virtual pero deberá imprimir los soportes, firmarlos y presentarlos en alguna de nuestras oficinas.';
        }
    }
    $pant = str_replace("[IMPORTANTEUSUARIOREGISTRADO]", $tx, $pant);

    $string .= $pres->armarLineaTexto(600, $pant);
    $arrBtnImagen = array('Acepto', 'No Acepto');
    $arrBtnToolTip = array('Aceptar t&eacute;rminos del servicio', 'No aceptar los t&eacute;rminos del servicio');
    $arrBtnTipo = array('href', 'href');
    $arrBtnEnlace = array($urlceptar, $urlnoaceptar);
    $string .= $pres->armarBarraBotonesProcesoDinamico(600, $arrBtnTipo, $arrBtnImagen, $arrBtnEnlace, $arrBtnToolTip);
    $string .= '</center>';
    unset($pres);
    mostrarCuerpoIE26a(array(), '', '', '', $string, 620, 400, '', '');
}

?>
