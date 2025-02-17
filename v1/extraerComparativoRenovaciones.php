<?php

if (isset($argv) && $argv[1] == 'ejecutarbackground') {
    $txt = '';
    $i = 0;
    ini_set('memory_limit', '5096M');
    ini_set('display_errors', 1);
    ini_set('default_socket_timeout', 14400);
    ini_set('set_time_limit', 14400);
    ini_set('soap.wsdl_cache_enabled', '0');
    ini_set('soap.wsdl_cache_ttl', '0');
    session_start();
    require_once("../configuracion/common.php");

    $_SESSION["generales"]["pathabsoluto"] = PATH_ABSOLUTO_SITIO;
    $parametros = json_decode(base64_decode($argv[2]), "true");

    $_SESSION["generales"]["codigoempresa"] = $parametros["codigoempresa"];
    $_SESSION["generales"]["codigousuario"] = $parametros["codigousuario"];

    if (!isset($_SESSION["generales"]["emailusuariocontrol"])) {
        $_SESSION["generales"]["emailusuariocontrol"] = $parametros["emailusuariosistema"];
    }
    if (!isset($_SESSION["generales"]["identificacionusuariocontrol"])) {
        $_SESSION["generales"]["identificacionusuariocontrol"] = '';
    }

    $_SESSION["generales"]["emailusuario"] = $parametros["codigousuario"];
    ejecutarBackground($parametros);
    exit();
}

include('validarSession.php');
ini_set('memory_limit', '4096M');
ini_set('display_errors', 1);
ini_set('default_socket_timeout', 14400);
ini_set('set_time_limit', 14400);
ini_set('soap.wsdl_cache_enabled', '0');
ini_set('soap.wsdl_cache_ttl', '0');

// Valida que el script pueda ser ejecutado
$script = __FILE__;
$ok = \funcionesGenerales::validarAcceso($script);

//
if ($ok == false) {
    \funcionesGenerales::armarMensaje($_SESSION["generales"]["txtemergente"]);
    exit();
}

// Valida la accion solicitada
if (isset($_SESSION["vars"]["accion"])) {
    $accion = $_SESSION["vars"]["accion"];
} else {
    $accion = '';
}

//
$enproceso = retornarRegistroMysqliApi(null, 'control_ejecucion_unica', "idproceso='extraerComparativoRenovaciones-" . date("Ymd") . "'");
if ($enproceso && !empty($enproceso)) {
    \funcionesGenerales::armarMensaje('La opción no puede ser ejecutada dado que en este momento se está generando una extracción previamente solicitada');
    exit();
}

// ejecuta la accion solicitada
switch ($accion) {
    case "seleccion":
        mostrarSeleccion();
        break;
    case "generarextraccion":
        generarExtraccion();
        break;
}

/**
 * Funci&oacute;n que muestra la pantalla de selecci&oacute;n del c&oacute;digo de barras a procesar
 */
function mostrarSeleccion() {

    require_once('../configuracion/common.php');
    require_once('../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once('../librerias/funciones/generales.php');
    require_once('../librerias/funciones/persistencia.php');
    require_once('../librerias/presentacion/presentacion.class.php');

    //
    $pres = new presentacionBootstrap();
    $string = $pres->abrirPanelGeneral(900);
    $string .= $pres->armarCampoTextoOculto('session_parameters', \funcionesGenerales::armarVariablesPantalla());
    $string .= $pres->armarCampoTextoOculto('tipohttp', TIPO_HTTP);
    $string .= $pres->armarCampoTextoOculto('httphost', HTTP_HOST);
    $string .= $pres->armarCampoTextoOculto('codigoempresa', $_SESSION["generales"]["codigoempresa"]);
    $string .= $pres->armarEncabezadoNuevo(RAZONSOCIAL);
    $string .= '<br>';
    $string .= $pres->armarLineaTextoInformativa('Comparativo renovaciones - Extracción', 'center', 'h2');
    $string .= $pres->abrirPanel();

    // Abrimos la conixón con al BD de lectura
    $mysqli = conexionMysqliApi('replicabatch');

    $string .= $pres->abrirRow();
    $string .= $pres->armarCampoDateMd('Año de enovaciones a revisar', 'si', '_anoren', 4, 'S', '');
    $string .= $pres->armarCampoTextoMdOnKey('Email control', 'si', '_emailusuariosistema', 4, $_SESSION["generales"]["emailusuariocontrol"], '', '', '', 'lowercase');
    $string .= $pres->cerrarRow();

    $string .= '<br>';

    // ********************************************************************************** //
    // Arma Botones
    // ********************************************************************************** //

    $arrBtnTipo = array('javascript');
    $arrBtnImagen = array('Generar');
    $arrBtnEnlace = array('generarExtraccion()');
    $string .= $pres->armarBotonesDinamicos($arrBtnTipo, $arrBtnImagen, $arrBtnEnlace);
    $string .= $pres->cerrarPanel();
    $string .= '<br>';

    $string .= $pres->cerrarPanelGeneral();
    unset($pres);
    $scriptheader = '<script type="text/javascript" src="../js/extraerDetalladoActos.js"></script>';
    \funcionesGenerales::mostrarCuerpoBootstrap($scriptheader, '', '', '', '', $string, '', 'plantillaVaciaHttp.html');

    // Cerrar la conxión con la BD transacción (escritura)
    $mysqli->close();
}

// Evalua los parámetros y lanza la extracción
function generarExtraccion() {
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

    //    
    $_SESSION["eda"]["anoren"] = str_replace(array("/", "-", " "), "", $_SESSION["vars"]["_anoren"]);
    $_SESSION["eda"]["emailusuariosistema"] = $_SESSION["vars"]["_emailusuariosistema"];
    $_SESSION["eda"]["aleatorio"] = \funcionesGenerales::generarAleatorioAlfanumerico20();
    $_SESSION["eda"]["fechahora"] = date("Ymd") . ' ' . date("His");

    //
    $arrJson = array(
        "codigoempresa" => CODIGO_EMPRESA,
        "codigousuario" => $_SESSION["generales"]["codigousuario"],
        "nombreusuario" => $_SESSION["generales"]["nombreusuariocontrol"],
        "aleatorio" => $_SESSION["eda"]["aleatorio"],
        "fechahora" => $_SESSION["eda"]["fechahora"],
        "anoren" => $_SESSION["eda"]["anoren"],
        "emailusuariosistema" => $_SESSION["eda"]["emailusuariosistema"]
    );

    //    
    $jsonencode = json_encode($arrJson);
    exec("php " . $_SESSION["generales"]["pathabsoluto"] . "/scripts/extraerComparativoRenovaciones.php ejecutarbackground " . base64_encode($jsonencode) . " > " . $_SESSION["generales"]["pathabsoluto"] . "/tmp/" . $_SESSION["generales"]["codigoempresa"] . "-extraerDetalladoActos-" . date("Ymd") . "-" . date("His") . ".log &", $output);

    $txt = 'Apreciado usuario, se ha lanzado la extracción del Informe comparativo de renovaciones, tan pronto termine el proceso, a su correo electrónico (' . $_SESSION["eda"]["emailusuariosistema"] . ') llegará un email con el enlace para descargar la informacion.<br><br>';
    $txt .= 'Código de identificacion de la extracción : ' . $_SESSION["eda"]["aleatorio"];
    \funcionesGenerales::armarMensaje($txt);
    exit();
}

function ejecutarBackground($arrParms = array()) {
    require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/EncodingNew.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
    require_once($_SESSION["generales"]["pathabsoluto"] . '/api/presentacion.class.php');
    set_error_handler('myErrorHandler');

    // Log de texto
    $nameLog = 'extraerComparativoRenovaciones_' . date("Ymd") . '_' . date("His") . '_' . session_id();
    $code = $arrParms["codigousuario"];
    \logApi::general2($nameLog, $arrParms["aleatorio"], 'Inicio extraccion');

    // Conexión con la BD transaccional (escritura)
    $mysqli = conexionMysqliApi();

    // Escribimos log de base datos indicando que se inicio la extracción
    actualizarLogMysqliApi($mysqli, '026', $_SESSION["generales"]["codigousuario"], 'extraerComparativoRenovaciones.php', '', '', '', addslashes('Incio extraccion codigo: ' . $arrParms["aleatorio"] . ', ' . 'Inicio'), '', '', '');

    // Creamos registro de control de ejecución únic apara prvenir que se envíe simultaneamete la extracciónb desde otras pantallas
    $arrCampos = array(
        'idproceso',
        'fecha',
        'hora',
        'idusuario'
    );
    $arrValores = array(
        "'extraerComparativoRenovaciones-" . date("Ymd") . "'",
        "''",
        "''",
        "''"
    );
    insertarRegistrosMysqliApi($mysqli, 'control_ejecucion_unica', $arrCampos, $arrValores);

    // Se ejecuta el procso de la extracción
    $timeini = date("H:i:s");

    //
    $_SESSION["relacion"] = retornarMatriculas($mysqli, $arrParms);

    //
    $txtFechas = '';
    if ($arrParms["fecini"] != '' && $arrParms["fecfin"] != '') {
        $txtFechas = 'Periodo comprendido entre el ' . \funcionesGenerales::mostrarFecha($arrParms["fecini"]) . ' y el ' . \funcionesGenerales::mostrarFecha($arrParms["fecfin"]);
    } else {
        $txtFechas = 'Totalidad de los actos.';
    }

    $salida = $_SESSION["generales"]["codigoempresa"] . '-ExtraerComparativoRenovaciones-' . date("Ymd") . '-' . date("His") . '.csv';
    $fsal = fopen($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $salida, "w");
    fwrite($fsal, RAZONSOCIAL . "\r\n");
    fwrite($fsal, 'Comparativo renovaciones' . "\r\n");
    fwrite($fsal, 'Año del corte : ' . $arrParms["anoren"] . "\r\n");
    fwrite($fsal, 'Fecha de generacion : ' . date("Y/m/d") . ' - ' . date("H:i:s") . "\r\n");
    fwrite($fsal, 'Generado por: ' . $arrParms["nombreusuario"] . "\r\n");
    fwrite($fsal, "\r\n");

    $lin = "Operador;Fecha;Matrícula;Organizacion; Categoria;Nombre;FchRenAnoAnterior;ActivosAnoAnterior;PagadoAnoAnterior;BeneficioAnoAnterior;;FchRenAnoActual;ActivosAnoActual;PagadoAnoActual;BeneficioAnoActual;";
    fwrite($fsal, $lin . "\r\n");

    $iLin = 10;
    $numlineas = 0;
    foreach ($_SESSION["relacion"]["renglones"] as $ren) {

        //
        $lin = '';
        $lin .= $ren["usuario"] . ';';
        $lin .= $ren["fecha"] . ';';
        $lin .= $ren["matricula"] . ';';
        $lin .= $ren["organizacion"] . ';';
        $lin .= $ren["categoria"] . ';';
        $lin .= $ren["nombre"] . ';';
        $lin .= $ren["fecrenanoant"] . ';';
        $lin .= $ren["actrenantant"] . ';';
        $lin .= $ren["valrenant"] . ';';
        $lin .= $ren["valbenant"] . ';';
        $lin .= $ren["fecrenanoact"] . ';';
        $lin .= $ren["actrenantact"] . ';';
        $lin .= $ren["valrenact"] . ';';
        $lin .= $ren["valbenact"] . ';';
        $iLin++;
        fwrite($fsal, $lin . "\r\n");
    }

    fclose($fsal);

    // Log de texto indicando que se termino la extracción
    \logApi::general2($nameLog, $arrParms["aleatorio"], 'Termino extraccion');
    \logApi::general2($nameLog, '', '');

    // Escribimos log de base datos indicando que se termino la extracción
    $txt = 'Se ejecutó Informe comparativo de renovaciones';
    actualizarLogMysqliApi($mysqli, '026', $_SESSION["generales"]["codigousuario"], 'extraerComparativoRenovaciones.php', '', '', '', addslashes($txt), '', '', '');

    // Eliminar el control de ejecución única
    borrarRegistrosMysqliApi($mysqli, 'control_ejecucion_unica', "idproceso='extraerComparativoRenovaciones-" . date("Ymd") . "'");

    // Cerrar conxión con la bd transaccionl (escritura)
    $mysqli->close();

    $timefin = date("H:i:s");
    $txt = 'Se ejecutó Informe comparativo de renovaciones<br><bR>';
    $txt .= 'Hora de inicio : ' . $timeini . '<br>';
    $txt .= 'Hora final : ' . $timefin . '<br>';
    // $txt .= 'Total registros de generados : ' . $cant . '<br><bR>';
    $txt .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/tmp/' . $salida . '">Descargar</a><br>';

    //
    $remail = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $arrParms["emailusuariosistema"], 'Informe detallado de actos ' . $_SESSION["generales"]["codigoempresa"] . "-" . $arrParms["fechahora"], $txt);
    if ($remail === false) {
        sleep(5);
        $remail = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $arrParms["emailusuariosistema"], 'Informe detallado de actos ' . $_SESSION["generales"]["codigoempresa"] . "-" . $arrParms["fechahora"], $txt);
    }
    exit();
}

function retornarMatriculas($mysqli, $arrParms) {
    $anoant = $arrParms["anoren"] - 1;
    $lisren = array();
    $rens = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "anorenovacion = '" . $arrParms["anoren"] . "'", "numerorecibo, fecoperacion");
    if ($rens && !empty($rens)) {
        foreach ($rens as $r) {
            if ($r["matricula"] != '') {
                if ($r["tipogasto"] == '0' || $r["tipogasto"] == '8') {
                    if (substr($r["idservicio"], 0, 6) == '010201' || substr($r["idservicio"], 0, 6) == '010202' || $r["idservicio"] == '01090110' || $r["idservicio"] == '01090111') {
                        if (!isset($lisren[$r["numerorecibo"]])) {
                            $lisren[$r["numerorecibo"]] = array();
                        }
                        if (!isset($lisren[$r["numerorecibo"]][$r["matricula"]])) {
                            $lisren[$r["numerorecibo"]][$r["matricula"]] = array();
                            $exp = retornarRegistroMysqliApi($mysqli, 'mrg_est_inscritos', "matricula='" . $r["matricula"] . "'");
                            $lisren[$r["numerorecibo"]][$r["matricula"]]["matricula"] = $r["matricula"];
                            $lisren[$r["numerorecibo"]][$r["matricula"]]["usuario"] = $r["operador"];
                            $lisren[$r["numerorecibo"]][$r["matricula"]]["nombre"] = $exp["nombre"];
                            $lisren[$r["numerorecibo"]][$r["matricula"]]["organizacion"] = $exp["organizacion"];
                            $lisren[$r["numerorecibo"]][$r["matricula"]]["categoria"] = $exp["categoria"];
                            $lisren[$r["numerorecibo"]][$r["matricula"]]["fecrenanoant"] = '';
                            $lisren[$r["numerorecibo"]][$r["matricula"]]["actrenant"] = 0;
                            $lisren[$r["numerorecibo"]][$r["matricula"]]["valrenant"] = 0;
                            $lisren[$r["numerorecibo"]][$r["matricula"]]["valbenanrt"] = 0;
                            $lisren[$r["numerorecibo"]][$r["matricula"]]["fecrenanoact"] = '';
                            $lisren[$r["numerorecibo"]][$r["matricula"]]["actrenact"] = 0;
                            $lisren[$r["numerorecibo"]][$r["matricula"]]["valrenact"] = 0;
                            $lisren[$r["numerorecibo"]][$r["matricula"]]["valbenact"] = 0;
                        }
                        if (substr($r["idservicio"], 0, 6) == '010202') {
                            $lisren[$r["numerorecibo"]][$r["matricula"]]["fecrenanoact"] = $r["fecoperacion"];
                            $lisren[$r["numerorecibo"]][$r["matricula"]]["actrenantact"] = $r["activos"];
                            $lisren[$r["numerorecibo"]][$r["matricula"]]["valrenact"] = $r["valor"];
                        }
                        if ($r["idservicio"] == '01090110' || $r["idservicio"] == '01090111') {
                            $lisren[$r["numerorecibo"]][$r["matricula"]]["valbenact"] = $r["valor"] * -1;
                        }
                    }
                    if (substr($r["idservicio"], 0, 6) == '010202') {
                        $rensa = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $r["matricula"] . "' and anorenovacion = '" . $anoant . "'", "numerorecibo, fecoperacion");
                        if ($rensa && !empty($rensa)) {
                            foreach ($rensa as $ra) {
                                if ($ra["tipogasto"] == '0' || $ra["tipogasto"] == '8') {
                                    if (substr($ra["idservicio"], 0, 6) == '010201' || substr($ra["idservicio"], 0, 6) == '010202' || $r["idservicio"] == '01090110' || $r["idservicio"] == '01090111') {
                                        if (substr($ra["idservicio"], 0, 6) == '010201' || substr($ra["idservicio"], 0, 6) == '010202') {
                                            $lisren[$r["numerorecibo"]][$r["matricula"]]["fecrenanoant"] = $ra["fecoperacion"];
                                            $lisren[$r["numerorecibo"]][$r["matricula"]]["actrenantant"] = $ra["activos"];
                                            $lisren[$r["numerorecibo"]][$r["matricula"]]["valrenant"] = $ra["valor"];
                                        }
                                        if ($ra["idservicio"] == '01090110' || $ra["idservicio"] == '01090111') {
                                            $lisren[$r["numerorecibo"]][$r["matricula"]]["valbenant"] = $ra["valor"] * -1;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
