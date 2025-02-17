<?php

/*
 * Se recibe json con la siguiente información
 * 
 */

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait ConsultarRegistrosPublicos {

    /**
     * Método que retorna las tarjeras con las estadísticas diarias calculadas en línea
     * 
     * Recibe
     * - codigoempresa
     * - usuariows
     * - token
     * - idusuario
     * - tipopusuario
     * - emailcontrol
     * - identificacioncontrol
     * - celularcontrol
     * - fecha
     * 
     * Retorna
     * - Matriculados
     * - Renovados
     * - Solicitudes de registro
     * - Noticia mercantil
     * - Noticia ESADL
     * - Noticia Proponentes
     * - Recibos generados
     * - Afiliados
     * 
     * @param API $api
     */
    public function consultarEstadisticasDiarias(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['tarjetas'] = array();

        // Verifica método de recepcion de parámetros
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);

        //
        $api->validarParametro("fecha", true);

        //
        $_SESSION["entrada"]["fecha"] = str_replace(array("/", "-"), "", $_SESSION["entrada"]["fecha"]);

        //
        if (!$api->validarToken('consultarEstadisticasDiarias', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $tarjetas = array();

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
        // Tarjeta matriculados
        // ********************************************************************** // 
        $cant = contarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "fecmatricula='" . $_SESSION["entrada"]["fecha"] . "' and ctrestmatricula IN ('MA','MI','MC','IA','IC','IF','II','AS')");
        $tarjeta = array();
        $tarjeta["nombre"] = 'Matriculados';
        $tarjeta["cantidad"] = $cant;
        $tarjeta["icono"] = '';
        $tarjeta["color"] = 'bg-primary';

        $arr = array();
        $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
        $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $arr["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
        $arr["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
        $arr["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
        $arr["fechainvocacion"] = date("Ymd");
        $arr["horainvocacion"] = date("His");
        $arr["script"] = 'mregConsultaMatriculados.php';
        $arr["accion"] = 'seleccion';
        $arr["parametros"] = array();
        $json = json_encode($arr);
        $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));

        $tarjeta["link"] = array();
        $tarjeta["link"]["aplicacion"] = 'sii1';
        $tarjeta["link"]["titulo"] = 'Ver matriculados';

        $tarjeta["link"]["href"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
        $tarjetas [] = $tarjeta;


        // ********************************************************************** //
        // Tarjeta renovados
        // ********************************************************************** // 
        $cant = contarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "fecrenovacion='" . $_SESSION["entrada"]["fecha"] . "' and ctrestmatricula IN ('MA','MI','MC','IA','IC','IF','II','AS') and ultanoren='" . substr($_SESSION["entrada"]["fecha"], 0, 4) . "' and fecmatricula < '" . $_SESSION["entrada"]["fecha"] . "'");
        $tarjeta = array();
        $tarjeta["nombre"] = 'Renovados';
        $tarjeta["cantidad"] = $cant;
        $tarjeta["icono"] = '';
        $tarjeta["color"] = 'bg-success';

        $arr = array();
        $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
        $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $arr["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
        $arr["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
        $arr["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
        $arr["fechainvocacion"] = date("Ymd");
        $arr["horainvocacion"] = date("His");
        $arr["script"] = 'mregConsultaRenovados.php';
        $arr["accion"] = 'seleccion';
        $arr["parametros"] = array();
        $json = json_encode($arr);
        $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
        $tarjeta["link"] = array();
        $tarjeta["link"]["aplicacion"] = 'sii1';
        $tarjeta["link"]["titulo"] = 'Ver renovados';
        $tarjeta["link"]["href"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
        $tarjetas [] = $tarjeta;

        // ********************************************************************** //
        // Tarjeta Solicitudes de Registro
        // ********************************************************************** // 
        $cant = contarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', "fecharadicacion='" . $_SESSION["entrada"]["fecha"] . "'");
        $tarjeta = array();
        $tarjeta["nombre"] = 'Solicitudes de Registro';
        $tarjeta["cantidad"] = $cant;
        $tarjeta["icono"] = '';
        $tarjeta["color"] = 'bg-info';

        $arr = array();
        $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
        $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $arr["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
        $arr["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
        $arr["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
        $arr["fechainvocacion"] = date("Ymd");
        $arr["horainvocacion"] = date("His");
        $arr["script"] = 'mregConsultaSolicitudesRegistro.php';
        $arr["accion"] = 'seleccion';
        $arr["parametros"] = array();
        $json = json_encode($arr);
        $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
        $tarjeta["link"] = array();
        $tarjeta["link"]["aplicacion"] = 'sii1';
        $tarjeta["link"]["titulo"] = 'Ver relación de solicitudes';
        $tarjeta["link"]["href"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
        $tarjetas [] = $tarjeta;

        // ********************************************************************** //
        // Inscripciones en los libros mercantiless
        // ********************************************************************** // 
        $cant = contarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "fecharegistro='" . $_SESSION["entrada"]["fecha"] . "' and (libro between 'RM01' and 'RM22')");
        $tarjeta = array();
        $tarjeta["nombre"] = 'Inscripciones Registro Mercantil';
        $tarjeta["cantidad"] = $cant;
        $tarjeta["icono"] = '';
        $tarjeta["color"] = 'bg-warning';

        $arr = array();
        $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
        $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $arr["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
        $arr["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
        $arr["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
        $arr["fechainvocacion"] = date("Ymd");
        $arr["horainvocacion"] = date("His");
        $arr["script"] = 'mregConsultaNoticiaMercantil.php';
        $arr["accion"] = 'mostrarformulariomercantil';
        $arr["parametros"] = array();
        $json = json_encode($arr);
        $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
        $tarjeta["link"] = array();
        $tarjeta["link"]["aplicacion"] = 'sii1';
        $tarjeta["link"]["titulo"] = 'Ver noticia mercantil';
        $tarjeta["link"]["href"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
        $tarjetas [] = $tarjeta;

        // ********************************************************************** //
        // Inscripciones en los libros ESADL
        // ********************************************************************** // 
        $cant = contarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "fecharegistro='" . $_SESSION["entrada"]["fecha"] . "' and (libro between 'RE51' and 'RE55')");
        $tarjeta = array();
        $tarjeta["nombre"] = 'Inscripciones Registro Esadl';
        $tarjeta["cantidad"] = $cant;
        $tarjeta["icono"] = '';
        $tarjeta["color"] = 'bg-danger';

        $arr = array();
        $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
        $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $arr["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
        $arr["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
        $arr["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
        $arr["fechainvocacion"] = date("Ymd");
        $arr["horainvocacion"] = date("His");
        $arr["script"] = 'mregConsultaNoticiaMercantil.php';
        $arr["accion"] = 'mostrarformularioesadl';
        $arr["parametros"] = array();
        $json = json_encode($arr);
        $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
        $tarjeta["link"] = array();
        $tarjeta["link"]["aplicacion"] = 'sii1';
        $tarjeta["link"]["titulo"] = 'Ver noticia Esadl';
        $tarjeta["link"]["href"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
        $tarjetas [] = $tarjeta;

        // ********************************************************************** //
        // Inscripciones en los libros Proponentes
        // ********************************************************************** // 
        $cant = contarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones_proponentes', "fecharegistro='" . $_SESSION["entrada"]["fecha"] . "'");
        $tarjeta = array();
        $tarjeta["nombre"] = 'Inscripciones Proponentes';
        $tarjeta["cantidad"] = $cant;
        $tarjeta["icono"] = '';
        $tarjeta["color"] = 'bg-primary';

        $arr = array();
        $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
        $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $arr["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
        $arr["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
        $arr["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
        $arr["fechainvocacion"] = date("Ymd");
        $arr["horainvocacion"] = date("His");
        $arr["script"] = 'mregConsultaNoticiaMercantil.php';
        $arr["accion"] = 'mostrarformularioproponentes';
        $arr["parametros"] = array();
        $json = $api->json($arr);
        $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
        $tarjeta["link"] = array();
        $tarjeta["link"]["aplicacion"] = 'sii1';
        $tarjeta["link"]["titulo"] = 'Ver noticia Proponentes';
        $tarjeta["link"]["href"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
        $tarjetas [] = $tarjeta;

        //WSI 2018-01-28  
        if ($_SESSION["entrada"]["tipousuario"] != '00' && $_SESSION["entrada"]["tipousuario"] != '06') {
            if (trim($_SESSION["entrada"]["idusuario"]) == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Código del usuario es obligatorio';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {

                // ********************************************************************** //
                // Recibos generados
                // ********************************************************************** // 

                $cant = contarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', "fecha='" . $_SESSION["entrada"]["fecha"] . "' and tipogasto IN ('0','6','7')");
                $tarjeta = array();
                $tarjeta["nombre"] = 'Recibos generados';
                $tarjeta["cantidad"] = $cant;
                $tarjeta["icono"] = '';
                $tarjeta["color"] = 'bg-success';

                $arr = array();
                $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                $arr["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
                $arr["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
                $arr["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
                $arr["fechainvocacion"] = date("Ymd");
                $arr["horainvocacion"] = date("His");
                $arr["script"] = 'mregConsultaRecibos.php';
                $arr["accion"] = 'seleccion';
                $arr["parametros"] = array();
                $json = json_encode($arr);
                $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                $tarjeta["link"] = array();
                $tarjeta["link"]["aplicacion"] = 'sii1';
                $tarjeta["link"]["titulo"] = 'Ver recibos';
                $tarjeta["link"]["href"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                $tarjetas [] = $tarjeta;

                // ********************************************************************** //
                // Afiliados activos
                // ********************************************************************** // 
                $cant = contarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "ctrafiliacion='1' and ctrestmatricula = 'MA'");
                $tarjeta = array();
                $tarjeta["nombre"] = 'Afiliados activos';
                $tarjeta["cantidad"] = $cant;
                $tarjeta["icono"] = '';
                $tarjeta["color"] = 'bg-info';

                $arr = array();
                $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                $arr["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
                $arr["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
                $arr["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
                $arr["fechainvocacion"] = date("Ymd");
                $arr["horainvocacion"] = date("His");
                // $arr["script"] = 'mregConsultaAfiliados.php';
                $arr["script"] = 'mregDetalladoAfiliados.php';
                $arr["accion"] = 'seleccion';
                $arr["parametros"] = array();
                $json = json_encode($arr);
                $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                $tarjeta["link"] = array();
                $tarjeta["link"]["aplicacion"] = 'sii1';
                $tarjeta["link"]["titulo"] = 'Ver afiliados';
                $tarjeta["link"]["href"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                $tarjetas [] = $tarjeta;
            }
        }

        //
        $mysqli->close();

        //
        $_SESSION['jsonsalida']['tarjetas'] = $tarjetas;

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }    

    /**
     * Método que retorna un expediente mercantil
     * 
     * Recibe
     * - codigoempresa
     * - usuariows
     * - token
     * - idusuario
     * - tipopusuario
     * - emailcontrol
     * - identificacioncontrol
     * - celularcontrol
     * - matricula
     * - establecimientosrues : S o N
     * 
     * Retorna
     * - json con el expediente
     * 
     * @param API $api
     */
    public function consultarExpedienteMercantilSii2(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['expediente'] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);

        $api->validarParametro("matricula", true);
        $api->validarParametro("establecimientosrues", true);

        //
        if (!$api->validarToken('consultarExpedienteMercantil', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Localiza la matrícula entregada
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();
        $temx = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["matricula"], '', '', '', $_SESSION["entrada"]["establecimientosrues"]);
        $mysqli->close();
        
        // En caso de no encontrar la matrícula
        if ($temx === false || empty($temx)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "0001";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La matrícula no fue localizada en la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $_SESSION["jsonsalida"]["expediente"] = $temx;

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    /**
     * Método que retorna la relación de trámites de un usuario
     * Si se trata de un usuario externo busca en mreg_liquidacion
     * Si se trata de un usuario interno busca en mreg_est_codigosbarras
     * 
     * Recibe
     * - codigoempresa
     * - usuariows
     * - token
     * - idusuario
     * - tipopusuario
     * - emailcontrol
     * - identificacioncontrol
     * - celularcontrol
     * - semilla
     * - cantidad a recibir
     * 
     * Retorna
     * - json con la lista de liquidaciones o lista de códigos de barras
     * 
     * @param API $api
     */
    public function consultarRelacionTramites(API $api) {
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
        $_SESSION["jsonsalida"]["cantidad"] = 0;
        $_SESSION['jsonsalida']['registros'] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);

        $api->validarParametro("semilla", false);
        $api->validarParametro("cantidad", true);

        //
        if (!$api->validarToken('consultarRelacionTramites', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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

        //WSIERRA 2018-01-28 Ajuste del paginador
        $_SESSION["entrada"]["semilla"] = intval($_SESSION["entrada"]["semilla"]);
        $consulta["retornar"] = intval($_SESSION["entrada"]["cantidad"]);
        $consulta["offset"] = 0;

        if ($_SESSION["entrada"]["semilla"] != '0') {
            $consulta["offset"] = $_SESSION["entrada"]["semilla"] * $consulta["retornar"];
        }

        // ********************************************************************** //
        // Si tipo de usuario es 00 (públicos) o 06 (Externos)
        // Busca la información en mreg_liquidacion
        // ********************************************************************** // 
        $_SESSION["entrada"]["tipousuario"] = sprintf("%02s", $_SESSION["entrada"]["tipousuario"]);
        if ($_SESSION["entrada"]["tipousuario"] == '00' || $_SESSION["entrada"]["tipousuario"] == '06') {
            if (trim($_SESSION["entrada"]["emailcontrol"]) == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Correo electrónico es obligatorio';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', "emailcontrol='" . $_SESSION["entrada"]["emailcontrol"] . "'", "fecha desc", "idliquidacion,numerorecuperacion,tipotramite,fecha,idestado,identificacioncliente,nombrecliente,idproponentebase,idmatriculabase", $consulta["offset"], $consulta["retornar"]);

            if ($temx && !empty($temx)) {
                $_SESSION["jsonsalida"]["cantidad"] = count($temx);
                foreach ($temx as $t) {
                    $registro = array();
                    $registro["numerorecuperacion"] = $t["numerorecuperacion"];
                    $registro["fecha"] = $t["fecha"];
                    $registro["idliquidacion"] = $t["idliquidacion"];
                    // $registro["tipotramite"] = \funcionesSii2::mostrarFecha($t["fecha"]).' / '.strtoupper($t["numerorecuperacion"]) . ' - ' . $t["tipotramite"];
                    $registro["tipotramite"] = strtoupper($t["numerorecuperacion"]) . ' - ' . $t["tipotramite"];
                    $registro["idestado"] = $t["idestado"];
                    $registro["txtestado"] = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacionestados', "id='" . $t["idestado"] . "'", "descripcion");
                    $registro["identificacion"] = $t["identificacioncliente"];
                    $registro["nombre"] = $t["nombrecliente"];
                    $registro["matricula"] = isset($t["idmatriculabase"]) ? $t["idmatriculabase"] : '';
                    $registro["proponente"] = isset($t["idproponentebase"]) ? $t["idproponentebase"] : '';
                    $registro["links"] = array();

                    // Si está pagado
                    if ($t["idestado"] == '07' || $t["idestado"] == '09') {
                        $arr = array();
                        $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                        $arr["idusuario"] = 'USUPUBXX';
                        $arr["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
                        $arr["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
                        $arr["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
                        $arr["fechainvocacion"] = date("Ymd");
                        $arr["horainvocacion"] = date("His");
                        $arr["script"] = 'mregSoportesPago.php';
                        $arr["accion"] = 'mostrarsoportes';
                        $arr["parametros"] = array();
                        $arr["parametros"]["liquidacion"] = $t["idliquidacion"];
                        $json = json_encode($arr);
                        $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                        $link = array();
                        $link["aplicacion"] = 'sii1';
                        $link["titulo"] = 'Ver soportes';
                        $link["href"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                        $registro["links"][] = $link;
                        $link["aplicacion"] = 'sii1';
                        $link["titulo"] = 'Consultar estado trámite';
                        $link["href"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                        $registro["links"][] = $link;
                    } else {
                        // Si no está anulado
                        if ($t["idestado"] != '99') {
                            $tt = retornarRegistroMysqliApi($mysqli, "bas_tipotramites", "id='" . $t["tipotramite"] . "'");
                            if ($tt && !empty($tt)) {
                                $arr = array();
                                $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                                $arr["idusuario"] = 'USUPUBXX';
                                $arr["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
                                $arr["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
                                $arr["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
                                $arr["fechainvocacion"] = date("Ymd");
                                $arr["horainvocacion"] = date("His");
                                $arr["script"] = $tt["script"];
                                $arr["accion"] = 'validarseleccionrecuperada';
                                $arr["parametros"] = array();
                                $arr["parametros"]["_numrec"] = $t["numerorecuperacion"];
                                $json = json_encode($arr);
                                $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                                $link = array();
                                $link["aplicacion"] = 'sii1';
                                $link["titulo"] = 'Recuperar trámite';
                                $link["href"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                                $registro["links"][] = $link;
                            }
                        }
                    }

                    //
                    $_SESSION['jsonsalida']['registros'][] = $registro;
                }
            }
        }

        // ********************************************************************** //
        // Si tipo de usuario es 00 (públicos) o 06 (Externos)
        // Busca la información en mreg_liquidacion
        // ********************************************************************** // 
        if ($_SESSION["entrada"]["tipousuario"] != '00' && $_SESSION["entrada"]["tipousuario"] != '06') {
            if (trim($_SESSION["entrada"]["idusuario"]) == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Código del usuario es obligatorio';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            //
            if ($_SESSION["entrada"]["tipousuario"] == '01') {
                $temy = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', "estadofinal IN ('01','02','03','04','09','11','13','14','22','23','34','35','38') and fechaestadofinal>'20161231'", "fecharadicacion asc", "*", $consulta["offset"], $consulta["retornar"]);
            } else {
                $usu = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION["entrada"]["idusuario"] . "'");
                $opefin = "'" . $_SESSION["entrada"]["idusuario"] . "'";
                if (trim($usu["idcodigosirepcaja"]) != '') {
                    $opefin .= ",'" . $usu["idcodigosirepcaja"] . "'";
                }
                if (trim($usu["idcodigosirepdigitacion"]) != '') {
                    $opefin .= ",'" . $usu["idcodigosirepdigitacion"] . "'";
                }
                if (trim($usu["idcodigosirepregistro"]) != '') {
                    $opefin .= ",'" . $usu["idcodigosirepregistro"] . "'";
                }

                $temy = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', "operadorfinal IN(" . $opefin . ") and estadofinal IN ('01','02','03','04','09','11','13','14','22','23','34','35','38') and fechaestadofinal>'20161231'", "fecharadicacion asc", "*", $consulta["offset"], $consulta["retornar"]);
            }

            //
            if ($temy && !empty($temy)) {
                $_SESSION["jsonsalida"]["cantidad"] = count($temy);
                foreach ($temy as $t) {
                    // Localizar ruta
                    $tr = retornarRegistroMysqliApi($mysqli, "mreg_codrutas", "id='" . $t["actoreparto"] . "'");

                    // Carga el registro
                    $registro = array();
                    $registro["codigobarras"] = trim($t["codigobarras"]);
                    $registro["fecharadicacion"] = trim($t["fecharadicacion"]);
                    $registro["operacion"] = trim($t["operacion"]);
                    $registro["recibo"] = trim($t["recibo"]);
                    $registro["operador"] = trim($t["operadorfinal"]);
                    $registro["matricula"] = trim($t["matricula"]);
                    $registro["proponente"] = trim($t["proponente"]);
                    $registro["tipoidentificacion"] = trim($t["idclase"]);
                    $registro["identificacion"] = trim($t["numid"]);
                    $registro["nombre"] = trim($t["nombre"]);
                    $registro["idestado"] = trim($t["estadofinal"]);
                    $registro["txtestado"] = retornarRegistroMysqliApi($mysqli, 'mreg_codestados_rutamercantil', "id='" . $t["estadofinal"] . "'", "descripcion");
                    if (trim($registro["txtestado"]) == '') {
                        $registro["txtestado"] = retornarRegistroMysqliApi($mysqli, 'mreg_codestados_rutaproponentes', "id='" . $t["estadofinal"] . "'", "descripcion");
                    }
                    $registro["fechaestadofinal"] = trim($t["fechaestadofinal"]);
                    $registro["horaestadofinal"] = trim($t["horaestadofinal"]);
                    $registro["actoreparto"] = trim($t["actoreparto"]);
                    //$registro["txtactoreparto"] = \funcionesSii2::mostrarFecha($registro["fecharadicacion"]).' - '.trim($tr["descripcion"]);
                    $registro["txtactoreparto"] = trim($tr["descripcion"]);
                    $registro["links"] = [];

                    // Arma los enlaces
                    switch ($t["estadofinal"]) {
                        case "01" : // En caso de escaneo
                        case "02" : // En caso de escaneo
                        case "03" : // En caso de escaneo
                        case "09" : // Reingresado
                            //
                            // Consultar ruta
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregConsultaRutaDocumentos.php';
                            $arr["accion"] = 'traerruta';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Ver ruta del documento';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            // Escanear
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregExpedienteGrafico.php';
                            $arr["accion"] = 'buscarcodigobarras';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                            $arr["parametros"]["numerooperacion"] = $t["operacion"];
                            $arr["parametros"]["identificacion"] = $t["numid"];
                            $arr["parametros"]["matricula"] = $t["matricula"];
                            $arr["parametros"]["proponente"] = $t["proponente"];
                            $arr["parametros"]["nombre"] = $t["nombre"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Escanear o digitalizar';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            // Finalizar escaneo
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregMostrarBandejas.php';
                            $arr["accion"] = 'finalizarescaneo';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Finalizar digitalización y enrutar';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            //
                            break;

                        case "04" : // Estudio y registro - Mercantil / ESADL
                        case "11" : // Inscrito - Mercantil / ESADL
                        case "13" : // Enviado a registro - Proponentes
                        case "22" : // Inscrito - Proponentes
                            // Consultar ruta
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregConsultaRutaDocumentos.php';
                            $arr["accion"] = 'traerruta';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Ver ruta del documento';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            // Estudiar mercantil
                            if ($tr["tipo"] == 'ME' || $tr["tipo"] == 'ES') {
                                $arr = array();
                                $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                                $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                                $arr["emailcontrol"] = '';
                                $arr["identificacioncontrol"] = '';
                                $arr["celularcontrol"] = '';
                                $arr["fechainvocacion"] = date("Ymd");
                                $arr["horainvocacion"] = date("His");
                                $arr["script"] = 'mregEstudiarMercantil.php';
                                $arr["accion"] = 'cargarcodigobarras';
                                $arr["parametros"] = array();
                                $arr["parametros"]["codbarras"] = $t["codigobarras"];
                                $json = json_encode($arr);
                                $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                                $link = array();
                                $link["aplicacion"] = 'sii1';
                                $link["titulo"] = 'Estudiar trámite';
                                $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                                $registro["links"][] = $link;
                            }

                            // Estudiar proponente
                            if ($tr["tipo"] == 'PR') {
                                $arr = array();
                                $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                                $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                                $arr["emailcontrol"] = '';
                                $arr["identificacioncontrol"] = '';
                                $arr["celularcontrol"] = '';
                                $arr["fechainvocacion"] = date("Ymd");
                                $arr["horainvocacion"] = date("His");
                                $arr["script"] = 'mregEstudiarProponentes.php';
                                $arr["accion"] = 'cargarcodigobarras';
                                $arr["parametros"] = array();
                                $arr["parametros"]["codbarras"] = $t["codigobarras"];
                                $json = json_encode($arr);
                                $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                                $link = array();
                                $link["aplicacion"] = 'sii1';
                                $link["titulo"] = 'Estudiar trámite';
                                $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                                $registro["links"][] = $link;
                            }

                            // Devolver trámite
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregDevolverTramite.php';
                            $arr["accion"] = 'cargarcodigobarras';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codbarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Devolver trámite';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            // Finalizar estudio
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregMostrarBandejas.php';
                            $arr["accion"] = 'finalizarestudio';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codbarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Finaliar estudio';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            // Archivar trámite
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregMostrarBandejas.php';
                            $arr["accion"] = 'archivartramite';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codbarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Archivar trámite';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;
                            break;


                        case "14" : // Digitación proponentes
                        case "23" : // Digitaciuón mercantil
                        case "24" : // Digitaciuón mercantil
                            // Consultar ruta
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregConsultaRutaDocumentos.php';
                            $arr["accion"] = 'traerruta';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Ver ruta del documento';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            // Digitar trámite mercantil
                            if ($tr["tipo"] == 'ME' || $tr["tipo"] == 'ES') {
                                $arr = array();
                                $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                                $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                                $arr["emailcontrol"] = '';
                                $arr["identificacioncontrol"] = '';
                                $arr["celularcontrol"] = '';
                                $arr["fechainvocacion"] = date("Ymd");
                                $arr["horainvocacion"] = date("His");
                                $arr["script"] = 'mregMostrarBandejas.php';
                                $arr["accion"] = 'mostrardigitacion';
                                $arr["parametros"] = array();
                                $arr["parametros"]["codbarras"] = $t["codigobarras"];
                                $json = json_encode($arr);
                                $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                                $link = array();
                                $link["aplicacion"] = 'sii1';
                                $link["titulo"] = 'Digitar trámite';
                                $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                                $registro["links"][] = $link;
                            }

                            // Digitar trámite de proponentes
                            if ($tr["tipo"] == 'PR') {
                                $arr = array();
                                $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                                $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                                $arr["emailcontrol"] = '';
                                $arr["identificacioncontrol"] = '';
                                $arr["celularcontrol"] = '';
                                $arr["fechainvocacion"] = date("Ymd");
                                $arr["horainvocacion"] = date("His");
                                $arr["script"] = 'mregMostrarBandejas.php';
                                $arr["accion"] = 'digitarformularioproponentes';
                                $arr["parametros"] = array();
                                $arr["parametros"]["codbarras"] = $t["codigobarras"];
                                $json = json_encode($arr);
                                $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                                $link = array();
                                $link["aplicacion"] = 'sii1';
                                $link["titulo"] = 'Digitar trámite';
                                $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                                $registro["links"][] = $link;
                            }

                            // Finalizar digitación
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregMostrarBandejas.php';
                            $arr["accion"] = 'finalizardigitacion';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codbarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Finalizar digitación';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            // Archivar trámite
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregMostrarBandejas.php';
                            $arr["accion"] = 'archivartramite';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codbarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Archivar trámite';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            //
                            break;

                        case "34" : // para firma
                        case "35" : // para firma
                            // Consultar ruta
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregConsultaRutaDocumentos.php';
                            $arr["accion"] = 'traerruta';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Ver ruta del documento';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            // Firmar inscripciones mercantil
                            if ($tr["tipo"] == 'ME' || $tr["tipo"] == 'ES') {
                                $arr = array();
                                $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                                $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                                $arr["emailcontrol"] = '';
                                $arr["identificacioncontrol"] = '';
                                $arr["celularcontrol"] = '';
                                $arr["fechainvocacion"] = date("Ymd");
                                $arr["horainvocacion"] = date("His");
                                $arr["script"] = 'mregFirmaInscripciones.php';
                                $arr["accion"] = 'cargarrelacioncodigobarras';
                                $arr["parametros"] = array();
                                $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                                $arr["parametros"]["criterio"] = '1';
                                $json = json_encode($arr);
                                $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                                $link = array();
                                $link["aplicacion"] = 'sii1';
                                $link["titulo"] = 'Firmar inscripciones|';
                                $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                                $registro["links"][] = $link;
                            }

                            // Firmar inscripciones proponentes
                            if ($tr["tipo"] == 'PR') {
                                $arr = array();
                                $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                                $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                                $arr["emailcontrol"] = '';
                                $arr["identificacioncontrol"] = '';
                                $arr["celularcontrol"] = '';
                                $arr["fechainvocacion"] = date("Ymd");
                                $arr["horainvocacion"] = date("His");
                                $arr["script"] = 'mregFirmaInscripciones.php';
                                $arr["accion"] = 'cargarrelacioncodigobarras';
                                $arr["parametros"] = array();
                                $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                                $arr["parametros"]["criterio"] = '2';
                                $json = json_encode($arr);
                                $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                                $link = array();
                                $link["aplicacion"] = 'sii1';
                                $link["titulo"] = 'Firmar inscripciones|';
                                $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                                $registro["links"][] = $link;
                            }

                            // Archivar trámite
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregMostrarBandejas.php';
                            $arr["accion"] = 'archivartramite';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codbarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Archivar trámite';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;
                            break;

                        case "38" : // en control de calidad
                            // Consultar ruta
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregConsultaRutaDocumentos.php';
                            $arr["accion"] = 'traerruta';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Ver ruta del documento';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            // Finalizar control de calidad
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregFinalizarControlCalidad.php';
                            $arr["accion"] = 'cargarcodigobarras';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Finalizar control de calidad';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;
                            break;
                    }

                    //
                    $_SESSION['jsonsalida']['registros'][] = $registro;
                }
            }
        }

        //
        $mysqli->close();


        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    /**
     * Método que retorna una liquidación 
     * 
     * Recibe
     * - codigoempresa
     * - usuariows
     * - token
     * - idusuario
     * - tipopusuario
     * - emailcontrol
     * - identificacioncontrol
     * - celularcontrol
     * - idliquidacion
     * - numerorecuperacion
     * 
     * Retorna
     * - json con el contenido de la liquidación
     * 
     * @param API $api
     */
    public function consultarLiquidacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        //
        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // ********************************************************************** //
        // array de respuesta
        // ********************************************************************** //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['tramite'] = array();

        // ********************************************************************** //
        // Verifica que  método de recepcion de parámetros sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);
        $api->validarParametro("ip", false);
        $api->validarParametro("sistemaorigen", false);

        $api->validarParametro("idliquidacion", false);
        $api->validarParametro("numerorecuperacion", false);

        // ********************************************************************** //
        // R.- Recuperar: Solo recupera la liquidación sin controles
        // P.- Procesar: Recupera la liquidación y arma controles dependiendo del
        // estado y el tipo de trámite
        // ********************************************************************** //
        $api->validarParametro("tipoconsulta", true);

        //
        if (!$api->validarToken('consultarLiquidacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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

        //
        if (ltrim(trim((string)$_SESSION["entrada"]["idliquidacion"]), "0") != '') {
            $numliq = ltrim(trim($_SESSION["entrada"]["idliquidacion"]), "0");
            $tipo = 'L';
        } else {
            if (trim((string)$_SESSION["entrada"]["numerorecuperacion"]) != '') {
                $numliq = trim((string)$_SESSION["entrada"]["numerorecuperacion"]);
                $tipo = 'NR';
            }
        }

        // ********************************************************************** //
        // Recupera la liquidación
        // ********************************************************************** //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $numliq, $tipo);
        if ($_SESSION["tramite"] === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $msj = '';
            if ($tipo == 'NR') {
                $msj = 'Número de recuperación no localizado en el SII. No es posible recuperar la liquidación.';
            }
            if ($tipo == 'L') {
                $msj = 'Liquidación no localizada';
            }

            $_SESSION["jsonsalida"]["mensajeerror"] = $msj;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        if (substr($_SESSION["tramite"]["fecha"], 0, 4) < date("Y")) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La liquidación a recuperar no pertenece al año actual.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }




        if (empty($_SESSION["tramite"]["liquidacion"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La liquidación no fue calculada posteriormente. Requiere iniciar un nuevo trámite.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        //
        $_SESSION['jsonsalida']['tramite'] = $_SESSION["tramite"];
        // $_SESSION['jsonsalida']['anorenovacion']=substr($_SESSION["tramite"]["fecha"], 0, 4);
        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    /**
     * 
     * @param API $api
     */
    public function consultarBotonesLiquidacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        //
        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // ********************************************************************** //
        // array de respuesta
        // ********************************************************************** //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']["idliquidacion"] = '';

        // ********************************************************************** //
        // Verifica que  método de recepcion de parámetros sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);
        $api->validarParametro("ip", false);
        $api->validarParametro("sistemaorigen", false);

        $api->validarParametro("idliquidacion", true);
        $api->validarParametro("numerorecuperacion", false);
        $api->validarParametro("requieresoportes", true);
        $api->validarParametro("soportescompletos", true);
        $api->validarParametro("formularioscompletos", true);

        //
        if (!$api->validarToken('consultarBotonesLiquidacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Crea la conexión con la BD
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();
        if ($mysqli == false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BC';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if (ltrim(trim($_SESSION["entrada"]["idliquidacion"]), "0") != '') {
            $numBuscar = ltrim(trim($_SESSION["entrada"]["idliquidacion"]), "0");
            $tipo = 'L';
        } else {
            if (trim($_SESSION["entrada"]["numerorecuperacion"]) != '') {
                $numBuscar = trim($_SESSION["entrada"]["numerorecuperacion"]);
                $tipo = 'NR';
            }
        }


        // ************************************************************************ //
        // Arma variables de session
        // ************************************************************************ //        
        $res = \funcionesGenerales::asignarVariablesSession($mysqli, $_SESSION["entrada"]);
        if ($res === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de autenticacion, usuario no puede consumir el API - problemas de sesion';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Recupera la liquidación
        // ********************************************************************** //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $numBuscar, $tipo);
        if ($_SESSION["tramite"] === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // ******************************************************************** //
        // Inicializa variables control visualización
        // ******************************************************************** //

        $construirVolanteBancos = 'NO';
        $firmaElectronica = 'NO';
        $firmaManuscrita = 'NO';
        $pagarEnCamara = 'NO';
        $pagoElectronico = 'NO';
        $pagoCajero = 'NO';
        $modificarLiquidacion = 'NO';
        $abandonarLiquidacion = 'SI';
        $consultarSobreDigital = 'NO';


        if (!defined('ACUERDO_BANCOS_RECIBIR_FORMULARIOS')) {
            define('ACUERDO_BANCOS_RECIBIR_FORMULARIOS', 'N');
        }
        if (!defined('EXIGIR_FIRMADO_PARA_PAGO_RENOVACION')) {
            define('EXIGIR_FIRMADO_PARA_PAGO_RENOVACION', 'S');
        }


        //$_SESSION["tramite"]["idestado"]='';
        //Permite recibir el pago Usuario Cajero
        if ($_SESSION["generales"]["escajero"] == 'SI') {
            if ($_SESSION["tramite"]["idestado"] <= '05' || $_SESSION["tramite"]["idestado"] == '08' || $_SESSION["tramite"]["idestado"] == '19' || $_SESSION["tramite"]["idestado"] == '44') {
                $pagoCajero = 'SI';
            }
        }

        // Marcar para pago en la cámara de comercio con usuario público
        if (($_SESSION["generales"]["escajero"] != 'SI') &&
                ($_SESSION["entrada"]["tipousuario"] == '00' || $_SESSION["entrada"]["tipousuario"] == '06')) {


            //Control # 1
            if ($_SESSION["entrada"]["requieresoportes"] == 'NO') {
                if ($_SESSION["entrada"]["formularioscompletos"] == 'SI') {
                    if ($_SESSION["tramite"]["idestado"] <= '05' || $_SESSION["tramite"]["idestado"] == '08') {
                        $pagarEnCamara = 'SI';
                    }
                }
            }

            //Control # 2
            if ($_SESSION["entrada"]["requieresoportes"] == 'SI') {
                if ($_SESSION["entrada"]["soportescompletos"] == 'SI') {
                    if ($_SESSION["entrada"]["formularioscompletos"] == 'SI') {
                        if ($_SESSION["tramite"]["idestado"] <= '05') {
                            $pagarEnCamara = 'SI';
                        }
                    }
                }
            }
        }

        //Permite generar volante de pago desde un usuario cajero
        if (ACTIVAR_PAGO_BANCOS == 'S') {

            if ($_SESSION["generales"]["escajero"] == 'SI') {
                if ($_SESSION["entrada"]["formularioscompletos"] == 'SI') {
                    if ($_SESSION["tramite"]["idestado"] <= '05') {
                        $construirVolanteBancos = 'SI';
                    }
                }
            }

            //Permite generar volante de pago desde un usuario público
            if (($_SESSION["generales"]["escajero"] != 'SI') &&
                    ($_SESSION["entrada"]["tipousuario"] == '00' || $_SESSION["entrada"]["tipousuario"] == '06')) {

                //Control # 1
                if ($_SESSION["entrada"]["requieresoportes"] == 'NO') {
                    if ($_SESSION["entrada"]["formularioscompletos"] == 'SI') {
                        if ($_SESSION["tramite"]["idestado"] <= '05') {
                            $construirVolanteBancos = 'SI';
                        }
                    }
                }

                //Control # 2
                if ($_SESSION["entrada"]["requieresoportes"] == 'SI') {
                    if ($_SESSION["entrada"]["soportescompletos"] == 'SI') {
                        if ($_SESSION["entrada"]["formularioscompletos"] == 'SI') {
                            if ($_SESSION["tramite"]["idestado"] <= '05') {
                                $construirVolanteBancos = 'SI';
                            }
                        }
                    }
                }

                //Control # 3
                if (ACUERDO_BANCOS_RECIBIR_FORMULARIOS == 'S') {
                    if ($_SESSION["entrada"]["formularioscompletos"] == 'SI') {
                        $construirVolanteBancos = 'SI';
                    }
                }
            }
        }


        // Permite realizar pago electrónico usuario público
        if (($_SESSION["generales"]["escajero"] != 'SI') &&
                ($_SESSION["entrada"]["tipousuario"] == '00' || $_SESSION["entrada"]["tipousuario"] == '06')) {

            if ($_SESSION["entrada"]["formularioscompletos"] == 'SI') {
                if ($_SESSION["tramite"]["idestado"] == '19' ||
                        $_SESSION["tramite"]["idestado"] == '44' ||
                        ($_SESSION["tramite"]["idestado"] == '08' && $_SESSION["tramite"]["firmadoelectronicamente"] == 'si') ||
                        ($_SESSION["tramite"]["idestado"] == '08' && $_SESSION["tramite"]["firmadomanuscrita"] == 'si') ||
                        ($_SESSION["tramite"]["idestado"] == '05' &&
                        ($_SESSION["tramite"]["firmadoelectronicamente"] == 'si' || $_SESSION["tramite"]["firmadomanuscrita"] == 'si')) ||
                        EXIGIR_FIRMADO_PARA_PAGO_RENOVACION == 'N') {
                    $pagoElectronico = 'SI';
                }
            }
        }


        // Firmado en forma manuscrita (usuario cajero o público)
        if (($_SESSION["generales"]["escajero"] == 'SI') ||
                ($_SESSION["entrada"]["tipousuario"] == '00' || $_SESSION["entrada"]["tipousuario"] == '06')) {

            //Control # 1
            if ($_SESSION["entrada"]["requieresoportes"] == 'NO') {
                if ($_SESSION["entrada"]["formularioscompletos"] == 'SI') {
                    if ($_SESSION["tramite"]["idestado"] <= '05') {
                        $firmaManuscrita = 'SI';
                    }
                }
            }

            //Control # 2
            if ($_SESSION["entrada"]["requieresoportes"] == 'SI') {
                if ($_SESSION["entrada"]["soportescompletos"] == 'SI') {
                    if ($_SESSION["entrada"]["formularioscompletos"] == 'SI') {
                        if ($_SESSION["tramite"]["idestado"] <= '05') {
                            $firmaManuscrita = 'SI';
                        }
                    }
                }
            }
        }

        // Firmado en forma electrónica como usuario público
        if (($_SESSION["generales"]["escajero"] != 'SI') &&
                ($_SESSION["entrada"]["tipousuario"] == '00' || $_SESSION["entrada"]["tipousuario"] == '06')) {

            //Control # 1
            if ($_SESSION["entrada"]["requieresoportes"] == 'NO') {
                if ($_SESSION["entrada"]["formularioscompletos"] == 'SI') {
                    if ($_SESSION["tramite"]["idestado"] <= '05') {

                        if (($_SESSION["tramite"]["firmadoelectronicamente"] != 'si')) {
                            $firmaElectronica = 'SI';
                        }
                    }
                }
            }

            //Control # 2
            if ($_SESSION["entrada"]["requieresoportes"] == 'SI') {
                if ($_SESSION["entrada"]["soportescompletos"] == 'SI') {
                    if ($_SESSION["entrada"]["formularioscompletos"] == 'SI') {
                        if ($_SESSION["tramite"]["idestado"] <= '05') {
                            if (($_SESSION["tramite"]["firmadoelectronicamente"] != 'si')) {
                                $firmaElectronica = 'SI';
                            }
                        }
                    }
                }
            }
        }


        // Si el trámite está firmado electrónicamente, permite descargar el sobre digital
        $pathSobreDigital = '';
        if (($_SESSION["tramite"]["idestado"] == '19') || ($_SESSION["tramite"]["firmadoelectronicamente"] == 'si')) {
            $arrTem = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_sobre', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"]);
            if ($arrTem && !empty($arrTem)) {
                $consultarSobreDigital = 'SI';
                $pathSobreDigital = TIPO_HTTP . HTTP_HOST . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $arrTem["path"];
                //
                if (!file_exists($pathSobreDigital)) {
                    $consultarSobreDigital = 'NO';
                    $pathSobreDigital = '';
                }
            }
        }

        // En caso de estar firmado en forma manuscrita que permita descargar el sobre digital
        if ($_SESSION["tramite"]["idestado"] == '44') {
            $arrTem = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_sobre', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"]);
            if ($arrTem && !empty($arrTem)) {
                $consultarSobreDigital = 'SI';
                $pathSobreDigital = TIPO_HTTP . HTTP_HOST . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $arrTem["path"];
                //
                if (!file_exists($pathSobreDigital)) {
                    $consultarSobreDigital = 'NO';
                    $pathSobreDigital = '';
                }
            }
        }

        //Modificar
        if ($_SESSION ["tramite"] ["idestado"] < '05') {
            $modificarLiquidacion = 'SI';
        }


        // ******************************************************************** //
        // Panel de botones
        // ******************************************************************** //

        $tempPanel = array();
        $tempPanel["tipo"] = 'boton';
        $tempPanel["titulopanel"] = 'Opciones Disponibles';
        $tempPanel["inputs"] = array();

        // Botón Marcar para pago en Caja
        $temp = array();
        $temp["tipo"] = 'botton';
        $temp["mostrar"] = $pagarEnCamara;
        $temp["label"] = 'Pagar en la cámara de comercio';
        $temp["tooltip"] = 'Si desea realizar el pago en cualquiera de las oficinas de la cámara de comercio.';
        $temp["color"] = 'btn-primary';
        $temp["icono"] = 'fas fa-thumbtack';
        $temp["x"] = '';
        $temp["y"] = 'marcarPagoCaja';
        $temp["z"] = 'ren_pagarCaja';
        $temp["modal"] = '';
        $temp["href"] = '';
        $tempPanel["inputs"][] = $temp;

        // Botón Imprimir Volante Pago
        $temp = array();
        $temp["tipo"] = 'botton';
        $temp["mostrar"] = $construirVolanteBancos;
        $temp["label"] = 'Generar Volante Pago';
        $temp["tooltip"] = 'Si desea generar un volante de pago y realizar el pago en bancos o corresponsales bancarios.';
        $temp["color"] = 'btn-primary';
        $temp["icono"] = 'fas fa-eye';
        $temp["x"] = '';
        $temp["y"] = 'construirVolanteBancos';
        $temp["z"] = 'ren_imprimirVolante';
        $temp["modal"] = '';
        $temp["href"] = '';
        $tempPanel["inputs"][] = $temp;

        // Botón Firma Electrónica
        $arr = array();
        $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
        $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $arr["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
        $arr["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
        $arr["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
        $arr["fechainvocacion"] = date("Ymd");
        $arr["horainvocacion"] = date("His");
        $arr["script"] = "mregFirmadoElectronico.php";
        $arr["accion"] = 'mostrarpantalla';
        $arr["parametros"] = array();
        $arr["parametros"]["_numrec"] = $_SESSION["tramite"]["numerorecuperacion"];
        $json = json_encode($arr);
        $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
        //
        $temp = array();
        $temp["tipo"] = 'botton';
        $temp["mostrar"] = $firmaElectronica;
        $temp["label"] = 'Firma Electrónica';
        $temp["tooltip"] = 'Si desea realizar el firmado electrónico del trámite. Es necesario para realizar virtualmente la totalidad del trámite.';
        $temp["color"] = 'btn-primary';
        $temp["icono"] = 'fas fa-thumbs-up';
        $temp["x"] = '';
        $temp["y"] = '';
        $temp["z"] = 'ren_firmadoElectronico';
        $temp["modal"] = '';
        $temp["href"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
        $tempPanel["inputs"][] = $temp;

        // Botón Firma Manuscrita
        $temp = array();
        $temp["tipo"] = 'botton';
        $temp["mostrar"] = $firmaManuscrita;
        $temp["label"] = 'Firma Manuscrita';
        $temp["tooltip"] = 'Si desea realizar el firmado manuscrito (mecánico) del trámite realizado. Es necesario para realizar virtualmente la totalidad del trámite.';
        $temp["color"] = 'btn-primary';
        $temp["icono"] = 'fas fa-pencil-alt';
        $temp["x"] = '';
        $temp["y"] = '';
        $temp["z"] = 'ren_firmadoManuscrito';
        $temp["modal"] = '';
        $temp["href"] = '';
        $tempPanel["inputs"][] = $temp;

        // Botón Pago Electrónico
        $arr = array();
        $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
        $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $arr["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
        $arr["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
        $arr["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
        $arr["fechainvocacion"] = date("Ymd");
        $arr["horainvocacion"] = date("His");
        $arr["script"] = TIPO_HTTP . HTTP_HOST . "/scripts/mregPagoElectronico.php&session_parameters=" . \funcionesGenerales::armarVariablesPantalla();
        $arr["accion"] = 'validarseleccion';
        $arr["parametros"] = array();
        $arr["parametros"]["_numrec"] = $_SESSION["tramite"]["numerorecuperacion"];
        $json = json_encode($arr);
        $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
        
        //
        $temp = array();
        $temp["tipo"] = 'botton';
        $temp["mostrar"] = $pagoElectronico;
        $temp["label"] = 'Pago Electrónico';
        $temp["tooltip"] = 'Si desea realizar el pago electrónico desde cualquiera de las alternativas de recaudo virtual disponibles por la cámara de comercio.';
        $temp["color"] = 'btn-primary';
        $temp["icono"] = 'fas fa-credit-card';
        $temp["x"] = '';
        $temp["y"] = '';
        $temp["z"] = 'ren_pagoElectronico';
        $temp["modal"] = '';
        $temp["href"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
        $tempPanel["inputs"][] = $temp;

        // Botón Pago en Caja

        $arr = array();
        $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
        $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
        $arr["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
        $arr["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
        $arr["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
        $arr["fechainvocacion"] = date("Ymd");
        $arr["horainvocacion"] = date("His");
        $arr["script"] = TIPO_HTTP . HTTP_HOST . "/scripts/mregRecibirPagos.php&session_parameters=" . \funcionesGenerales::armarVariablesPantalla();
        $arr["accion"] = 'validarseleccion';
        $arr["parametros"] = array();
        $arr["parametros"]["_numrec"] = $_SESSION["tramite"]["numerorecuperacion"];
        $json = json_encode($arr);
        $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));

        //        
        $temp = array();
        $temp["tipo"] = 'botton';
        $temp["mostrar"] = $pagoCajero;
        $temp["label"] = 'Recibir Pago';
        $temp["tooltip"] = 'Si desea recibir el pago en caja de la cámara de comercio.';
        $temp["color"] = 'btn-primary';
        $temp["icono"] = 'fas fa-money-bill-alt';
        $temp["x"] = '';
        $temp["y"] = '';
        $temp["z"] = 'ren_pagoCajero';
        $temp["modal"] = '';
        $temp["href"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
        $tempPanel["inputs"][] = $temp;

        // Boton Sobre Digital
        $temp = array();
        $temp["tipo"] = 'botton';
        $temp["mostrar"] = $consultarSobreDigital;
        $temp["label"] = 'Obtener Sobre Digital';
        $temp["tooltip"] = 'Si desea obtener el sobre digital generado como resultado del firmado electrónico.';
        $temp["color"] = 'btn-primary';
        $temp["icono"] = 'fas fa-certificate';
        $temp["x"] = '';
        $temp["y"] = '';
        $temp["z"] = 'ren_consultarSobreDigital';
        $temp["modal"] = 'SI';
        $temp["href"] = $pathSobreDigital;
        $tempPanel["inputs"][] = $temp;

        // Botón Modificar Liquidación
        $temp = array();
        $temp["tipo"] = 'botton';
        $temp["mostrar"] = $modificarLiquidacion;
        $temp["label"] = 'Modificar Liquidación';
        $temp["tooltip"] = 'Si desea modificar la solicitud de liquidación previamente realizada.';
        $temp["color"] = 'btn-secondary';
        $temp["icono"] = 'fas fa-reply-all';
        $temp["x"] = '';
        $temp["y"] = '';
        $temp["z"] = 'ren_modificarLiquidacion';
        $temp["modal"] = '';
        $temp["href"] = '';
        $tempPanel["inputs"][] = $temp;

        // Boton Abandonar
        $temp = array();
        $temp["tipo"] = 'botton';
        $temp["mostrar"] = $abandonarLiquidacion;
        $temp["label"] = 'Abandonar Trámite';
        $temp["tooltip"] = 'Si desea abandonar el trámite y posteriormente recuperarlo.';
        $temp["color"] = 'btn-secondary';
        $temp["icono"] = 'fas fa-sign-out-alt';
        $temp["x"] = '';
        $temp["y"] = '';
        $temp["z"] = 'ren_abandonarLiq';
        $temp["modal"] = '';
        $temp["href"] = '';
        $tempPanel["inputs"][] = $temp;

        //

        $_SESSION['jsonsalida']["idliquidacion"] = $_SESSION["tramite"]["idliquidacion"];
        $_SESSION['jsonsalida']["numerorecuperacion"] = $_SESSION["tramite"]["numerorecuperacion"];
        $_SESSION['jsonsalida']["tipousuario"] = $_SESSION['entrada']["tipousuario"];
        $_SESSION["jsonsalida"]["escajero"] = $_SESSION["generales"]["escajero"];
        $_SESSION['jsonsalida']["idestado"] = $_SESSION["tramite"]["idestado"];
        $_SESSION['jsonsalida']["txtestado"] = retornarRegistroMysqliApi($mysqli, "mreg_liquidacionestados", "id='" . $_SESSION["tramite"]["idestado"] . "'", "descripcion");
        //$_SESSION["jsonsalida"]["controles"] = array();
        $_SESSION["jsonsalida"]["controles"] = $tempPanel;

        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    /**
     * Retorna la relación de matrículas a renovar
     * 
     * Recibe
     * - codigoempresa
     * - usuariows
     * - token
     * - idusuario
     * - tipopusuario
     * - emailcontrol
     * - identificacioncontrol
     * - celularcontrol
     * - ip
     * - sistemaorigen
     * - matricula
     * - identificacion
     * - idliquidacion
     * - procesartodas
     * - cancelarmatricula
     * - benley1780
     * 
     * Retorna
     * - json con la lista de matrículas a renovar
     * 
     * @param API $api
     */
    public function relacionMatriculasRenovar(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        //
        $mysqli = conexionMysqliApi();

        // ********************************************************************** //
        // Fecha de corte de renovaciones
        // ********************************************************************** //
        $fcorte = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");


        // ********************************************************************** //
        // array de respuesta
        // ********************************************************************** //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["cantidad"] = 0;
        $_SESSION["jsonsalida"]["fecha_servidor"] = '';
        $_SESSION["jsonsalida"]["hora_servidor"] = '';
        $_SESSION["jsonsalida"]["tramite"] = array();
        $_SESSION["jsonsalida"]["alertasAdministrativas"] = array();
        $_SESSION["jsonsalida"]["alertasRegistrales"] = array();
        $_SESSION["jsonsalida"]["controles"] = array();


        // ********************************************************************** //
        // Verifica que  método de recepcion de parámetros sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, false);
        $api->validarParametro("emailcontrol", true, true);
        $api->validarParametro("identificacioncontrol", true, true);
        $api->validarParametro("celularcontrol", true, true);
        $api->validarParametro("ip", true, true);
        $api->validarParametro("sistemaorigen", true, true);

        //
        $api->validarParametro("idliquidacion", false, false);
        $api->validarParametro("procesartodas", true);
        $api->validarParametro("cancelarmatricula", true);
        $api->validarParametro("benley1780", true);

        $api->validarParametro("matricula", false, false);
        $api->validarParametro("identificacion", false, false);


        if (ltrim($_SESSION["entrada"]["idliquidacion"], "0") != '') {
            if (trim($_SESSION["entrada"]["matricula"]) == '' && trim($_SESSION["entrada"]["identificacion"]) == '') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se recibió número de matrícula ni número de identificación';
                $mysqli->close();
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ********************************************************************** //
        // Valida procesar todas
        // ********************************************************************** //
        if (trim($_SESSION["entrada"]["procesartodas"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No indicó si se procesa una matrícula o todas las matrículas asociadas';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if (trim($_SESSION["entrada"]["procesartodas"]) != 'N' && trim($_SESSION["entrada"]["procesartodas"]) != 'L' && trim($_SESSION["entrada"]["procesartodas"]) != 'S' && trim($_SESSION["entrada"]["procesartodas"]) != 'SP') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Indicador "procesar todas" erróneo (S, N, L)';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida control de cancelación
        // ********************************************************************** //        
        if (trim($_SESSION["entrada"]["cancelarmatricula"]) != 'SI' && trim($_SESSION["entrada"]["cancelarmatricula"]) != 'NO') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Indicó en forma errónea si renovará para cancelar o no (' . $_SESSION["entrada"]["cancelarmatricula"] . ')';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida control de beneficio Ley 1780
        // ********************************************************************** //                
        if (trim($_SESSION["entrada"]["benley1780"]) != 'S' && trim($_SESSION["entrada"]["benley1780"]) != 'N' && trim($_SESSION["entrada"]["benley1780"]) != 'P' && trim($_SESSION["entrada"]["benley1780"]) != 'R') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Control de beneficio de Ley 1780 erróneo';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida versión del SII
        // ********************************************************************** //                
        if (trim($_SESSION["entrada"]["sistemaorigen"]) != 'SII1' && trim($_SESSION["entrada"]["sistemaorigen"]) != 'SII2') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Control de versionado del sii erróneo';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // ********************************************************************** //
        // Valida que se pueda consumir el método
        // ********************************************************************** //
        if (!$api->validarToken('relacionMatriculasRenovar', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ************************************************************************ //
        // Arma variables de session
        // ************************************************************************ //        
        $res = \funcionesGenerales::asignarVariablesSession($mysqli, $_SESSION["entrada"]);
        if ($res === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de autenticacion, usuario no puede consumir el API - problemas de sesion';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ************************************************************************ //
        // En caos de no tener número de recuperacion, la crea
        // ************************************************************************ //        
        if (ltrim($_SESSION["entrada"]["idliquidacion"], "0") == '') {
            $_SESSION ["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, 0, 'VC');
            $_SESSION ["tramite"]["codigoError"] = '0000';
            $_SESSION ["tramite"]["mensajeError"] = '0000';
            $_SESSION ["tramite"]["matriculabase"] = $_SESSION["entrada"]["matricula"];
            $_SESSION ["tramite"]["idexpedientebase"] = $_SESSION["entrada"]["matricula"];
            $_SESSION ["tramite"]["identificacionbase"] = $_SESSION["entrada"]["identificacion"];
            $_SESSION ["tramite"]["ctrcancelacion"] = $_SESSION["entrada"]["cancelarmatricula"];
            $_SESSION ["tramite"]["procesartodas"] = $_SESSION["entrada"]["procesartodas"];
            $_SESSION ["tramite"]["benley1780"] = $_SESSION["entrada"]["procesartodas"];
            $_SESSION ["tramite"]["reliquidacion"] = 'no';
            $_SESSION ["tramite"]["idestado"] = '01';
            $_SESSION ["tramite"]["idliquidacion"] = \funcionesGenerales::retornarSecuencia($mysqli, 'LIQUIDACION-REGISTROS');
            $_SESSION ["tramite"]["numeroliquidacion"] = $_SESSION ["tramite"]["idliquidacion"];
            $_SESSION ["tramite"]["numerorecuperacion"] = \funcionesGenerales::asignarNumeroRecuperacion($mysqli, 'mreg');
        } else {
            $temx = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);
            $_SESSION ["tramite"]["idliquidacion"] = $temx["idliquidacion"];
            $_SESSION ["tramite"]["numeroliquidacion"] = $temx["idliquidacion"];
            $_SESSION ["tramite"]["numerorecuperacion"] = $temx["numerorecuperacion"];

            //AQUI WSI 2018-02-19 Para reutilizar las selecciones al modificar liquidación 
            $_SESSION ["tramite"]["numeroempleados"] = $temx["numeroempleados"];
            $_SESSION ["tramite"]["cumplorequisitosbenley1780"] = $temx["cumplorequisitosbenley1780"];
            $_SESSION ["tramite"]["mantengorequisitosbenley1780"] = $temx["mantengorequisitosbenley1780"];
            $_SESSION ["tramite"]["renunciobeneficiosley1780"] = $temx["renunciobeneficiosley1780"];
            $_SESSION ["tramite"]["ctrcancelacion"] = $temx["ctrcancelacion"];
        }

        //
        $retorno = array();
        $retorno["codigoalerta"] = '0000';
        $retorno["mensajealerta"] = '';
        $retorno["idexpedientebase"] = '';
        $retorno["idmatriculabase"] = '';
        $retorno["nombrebase"] = '';
        $retorno["nom1base"] = '';
        $retorno["nom2base"] = '';
        $retorno["ape1base"] = '';
        $retorno["ape2base"] = '';
        $retorno["tipoidentificacionbase"] = '';
        $retorno["identificacionbase"] = '';
        $retorno["organizacionbase"] = '';
        $retorno["categoriabase"] = '';
        $retorno["afiliadobase"] = '';
        $retorno["email"] = '';
        $retorno["direccion"] = '';
        $retorno["telefono"] = '';
        $retorno["movil"] = '';
        $retorno["idmunicipio"] = '';
        $retorno["benley1780"] = '';
        $retorno["cumplorequisitosbenley1780"] = '';
        $retorno["mantengorequisitosbenley1780"] = '';
        $retorno["renunciobeneficiosley1780"] = '';
        $retorno["multadoponal"] = '';
        $retorno["matriculas"] = array();

        //
        $propJurisdiccion = '';
        if ($_SESSION["entrada"]["matricula"] != '') {

            switch (trim($_SESSION["entrada"]["procesartodas"])) {
                case 'S':
                    $tipoData = 'E';
                    break;
                case 'N':
                    $tipoData = 'N';
                    break;
                case 'L':
                    $tipoData = 'N';
                    break;
                default:
                    break;
            }

            $arrTem = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["matricula"], '', '', '', $tipoData);


            if ($arrTem === false || empty($arrTem)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente no localizado en el SII';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if ($arrTem["estadomatricula"] != 'MA' &&
                    $arrTem["estadomatricula"] != 'MI' &&
                    $arrTem["estadomatricula"] != 'IA' &&
                    $arrTem["estadomatricula"] != 'II') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El expediente seleccionado no se encuentra activo (registra el estado ' . $arrTem["estadomatricula"] . ')';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {
                $retorno["idexpedientebase"] = $_SESSION["entrada"]["matricula"];
                $retorno["idmatriculabase"] = $_SESSION["entrada"]["matricula"];
                $retorno["nombrebase"] = $arrTem["nombre"];
                $retorno["nom1base"] = $arrTem["nom1"];
                $retorno["nom2base"] = $arrTem["nom2"];
                $retorno["ape1base"] = $arrTem["ape1"];
                $retorno["ape2base"] = $arrTem["ape2"];
                $retorno["tipoidentificacionbase"] = $arrTem["tipoidentificacion"];
                $retorno["identificacionbase"] = $arrTem["identificacion"];
                $retorno["organizacionbase"] = $arrTem["organizacion"];
                $retorno["categoriabase"] = $arrTem["categoria"];
                $retorno["afiliadobase"] = $arrTem["afiliado"];
                $retorno["email"] = $arrTem["emailcom"];
                $retorno["direccion"] = $arrTem["dircom"];
                $telcom = '';
                $celcom = '';
                if (strlen($arrTem["telcom1"]) == 7) {
                    $telcom = $arrTem["telcom1"];
                } else {
                    if (strlen($arrTem["telcom1"]) == 10) {
                        $celcom = $arrTem["telcom1"];
                    }
                }
                if (strlen($arrTem["telcom2"]) == 7) {
                    if (trim($telcom) == '') {
                        $telcom = $arrTem["telcom2"];
                    }
                } else {
                    if (strlen($arrTem["telcom2"]) == 10) {
                        if (trim($celcom) == '') {
                            $celcom = $arrTem["telcom2"];
                        }
                    }
                }
                if (strlen($arrTem["celcom"]) == 7) {
                    if (trim($telcom) == '') {
                        $telcom = $arrTem["celcom"];
                    }
                } else {
                    if (strlen($arrTem["celcom"]) == 10) {
                        if (trim($celcom) == '') {
                            $celcom = $arrTem["celcom"];
                        }
                    }
                }
                $retorno["telefono"] = $telcom;
                $retorno["movil"] = $celcom;
                $retorno["idmunicipio"] = $arrTem["muncom"];
                $retorno["benley1780"] = $arrTem["benley1780"];
                $propJurisdiccion = 'S';
            }
        }

        //
        if ($_SESSION["entrada"]["matricula"] == '' && $_SESSION["entrada"]["identificacion"] != '') {
            $arrTemX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "numid like '" . $_SESSION["entrada"]["identificacion"] . "%' or nit like '" . $_SESSION["entrada"]["identificacion"] . "%'", "numid");
            if ($arrTemX === false || empty($arrTemX)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Identificacion no localizado en el SII (*)';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $arrTem = array();
            foreach ($arrTemX as $t) {
                if (ltrim(trim($t["matricula"]), "0") != '') {
                    if ($t["ctrestmatricula"] == 'MA' || $t["ctrestmatricula"] == 'MI' || $t["ctrestmatricula"] == 'IA' || $t["ctrestmatricula"] == 'II') {
                        if (empty($arrTem)) {
                            $arrTem = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $t["matricula"], '', '', '', 'N');
                            $propJurisdiccion = 'S';
                            $retorno["idexpedientebase"] = $t["matricula"];
                            $retorno["idmatriculabase"] = $t["matricula"];
                            $retorno["nombrebase"] = $arrTem["nombre"];
                            $retorno["nom1base"] = $arrTem["nom1"];
                            $retorno["nom2base"] = $arrTem["nom2"];
                            $retorno["ape1base"] = $arrTem["ape1"];
                            $retorno["ape2base"] = $arrTem["ape2"];
                            $retorno["tipoidentificacionbase"] = $arrTem["tipoidentificacion"];
                            $retorno["identificacionbase"] = $arrTem["identificacion"];
                            $retorno["organizacionbase"] = $arrTem["organizacion"];
                            $retorno["categoriabase"] = $arrTem["categoria"];
                            $retorno["afiliadobase"] = $arrTem["afiliado"];
                            $retorno["email"] = $arrTem["emailcom"];
                            $retorno["direccion"] = $arrTem["dircom"];
                            $telcom = '';
                            $celcom = '';
                            if (strlen($arrTem["telcom1"]) == 7) {
                                $telcom = $arrTem["telcom1"];
                            } else {
                                if (strlen($arrTem["telcom1"]) == 10) {
                                    $celcom = $arrTem["telcom1"];
                                }
                            }
                            if (strlen($arrTem["telcom2"]) == 7) {
                                if (trim($telcom) == '') {
                                    $telcom = $arrTem["telcom2"];
                                }
                            } else {
                                if (strlen($arrTem["telcom2"]) == 10) {
                                    if (trim($celcom) == '') {
                                        $celcom = $arrTem["telcom2"];
                                    }
                                }
                            }
                            if (strlen($arrTem["celcom"]) == 7) {
                                if (trim($telcom) == '') {
                                    $telcom = $arrTem["celcom"];
                                }
                            } else {
                                if (strlen($arrTem["celcom"]) == 10) {
                                    if (trim($celcom) == '') {
                                        $celcom = $arrTem["celcom"];
                                    }
                                }
                            }
                            $retorno["telefono"] = $telcom;
                            $retorno["movil"] = $celcom;
                            $retorno["idmunicipio"] = $arrTem["muncom"];
                            $retorno["benley1780"] = $arrTem["benley1780"];
                        }
                    }
                }
            }
            if (empty($arrTem)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Identificacion no localizado en el SII (**)';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // 
        if ($arrTem["organizacion"] == '02') {
            if (count($arrTem["propietarios"]) == 1) {
                if ($arrTem["propietarios"][1]["camarapropietario"] == CODIGO_EMPRESA ||
                        ltrim($arrTem["propietarios"][1]["camarapropietario"], "0") == '') {
                    if ($arrTem["propietarios"][1]["matriculapropietario"] != '') {
                        $arrTem1 = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, ltrim(trim($arrTem["propietarios"][1]["matriculapropietario"]), "0"), '', '', '', 'N');
                        if ($arrTem1 && !empty($arrTem1) && $arrTem1 != 0) {
                            if ($arrTem1["estadomatricula"] == 'MA' ||
                                    $arrTem1["estadomatricula"] == 'MI' ||
                                    $arrTem1["estadomatricula"] == 'IA' ||
                                    $arrTem1["estadomatricula"] == 'II' ||
                                    $arrTem1["estadomatricula"] == 'MC') {
                                $propJurisdiccion = 'S';
                                if ($_SESSION["entrada"]["procesartodas"] == 'S' || $_SESSION["entrada"]["procesartodas"] == 'SP') {
                                    if ($arrTem1["estadomatricula"] != 'MC') {
                                        $arrTem = $arrTem1;
                                        $retorno["idexpedientebase"] = $arrTem1["matricula"];
                                        $retorno["idmatriculabase"] = $arrTem1["matricula"];
                                        $retorno["nombrebase"] = $arrTem1["nombre"];
                                        $retorno["nom1base"] = $arrTem1["nom1"];
                                        $retorno["nom2base"] = $arrTem1["nom2"];
                                        $retorno["ape1base"] = $arrTem1["ape1"];
                                        $retorno["ape2base"] = $arrTem1["ape2"];
                                        $retorno["tipoidentificacionbase"] = $arrTem1["tipoidentificacion"];
                                        $retorno["identificacionbase"] = $arrTem1["identificacion"];
                                        $retorno["organizacionbase"] = $arrTem1["organizacion"];
                                        $retorno["categoriabase"] = $arrTem1["categoria"];
                                        $retorno["afiliadobase"] = $arrTem1["afiliado"];
                                        $retorno["email"] = $arrTem1["emailcom"];
                                        $retorno["direccion"] = $arrTem1["dircom"];
                                        $telcom = '';
                                        $celcom = '';
                                        if (strlen($arrTem1["telcom1"]) == 7) {
                                            $telcom = $arrTem1["telcom1"];
                                        } else {
                                            if (strlen($arrTem1["telcom1"]) == 10) {
                                                $celcom = $arrTem1["telcom1"];
                                            }
                                        }
                                        if (strlen($arrTem1["telcom2"]) == 7) {
                                            if (trim($telcom) == '') {
                                                $telcom = $arrTem1["telcom2"];
                                            }
                                        } else {
                                            if (strlen($arrTem1["telcom2"]) == 10) {
                                                if (trim($celcom) == '') {
                                                    $celcom = $arrTem1["telcom2"];
                                                }
                                            }
                                        }
                                        if (strlen($arrTem1["celcom"]) == 7) {
                                            if (trim($telcom) == '') {
                                                $telcom = $arrTem1["celcom"];
                                            }
                                        } else {
                                            if (strlen($arrTem1["celcom"]) == 10) {
                                                if (trim($celcom) == '') {
                                                    $celcom = $arrTem1["celcom"];
                                                }
                                            }
                                        }
                                        $retorno["telefono"] = $telcom;
                                        $retorno["movil"] = $celcom;
                                        $retorno["idmunicipio"] = $arrTem1["muncom"];
                                        $retorno["benley1780"] = $arrTem1["benley1780"];
                                    }
                                }
                            } else {
                                $propJurisdiccion = 'N';
                            }
                        } else {
                            $propJurisdiccion = 'N';
                        }
                        unset($arrTem1);
                    } else {
                        $propJurisdiccion = 'N';
                    }
                } else {
                    $propJurisdiccion = 'N';
                }
            } else {
                $propJurisdiccion = 'N';
                if (count($arrTem["propietarios"]) > 1) {
                    if ($arrTem["propietarios"][1]["camarapropietario"] == CODIGO_EMPRESA ||
                            ltrim($arrTem["propietarios"][1]["camarapropietario"], "0") == '') {
                        $propJurisdiccion = 'S';
                    }
                }
            }
        }

        // 2017-07-24: JINT: Para determinar si el propietario está dentro o fuera de la jurisdiccion
        // cuanto se trate de sucursales y agencias
        if ($arrTem["organizacion"] > '02' && ($arrTem["categoria"] == '2' || $arrTem["categoria"] == '3')) {
            $propJurisdiccion = 'S';
            if ($arrTem["cpcodcam"] != '00' && $arrTem["cpcodcam"] != CODIGO_EMPRESA) {
                $propJurisdiccion = 'N';
            }
        }


        //
        $i = -1;
        if ($_SESSION["entrada"]["procesartodas"] != 'SP') {
            $i++;
            $retorno["matriculas"][$i]["idtipoidentificacion"] = $arrTem["tipoidentificacion"];
            $retorno["matriculas"][$i]["identificacion"] = $arrTem["identificacion"];
            $retorno["matriculas"][$i]["cc"] = CODIGO_EMPRESA;
            $retorno["matriculas"][$i]["matricula"] = $arrTem["matricula"];
            $retorno["matriculas"][$i]["nombre"] = mb_strtoupper($arrTem["nombre"], 'utf-8');
            $retorno["matriculas"][$i]["ape1"] = $arrTem["ape1"];
            $retorno["matriculas"][$i]["ape2"] = $arrTem["ape2"];
            $retorno["matriculas"][$i]["nom1"] = $arrTem["nom1"];
            $retorno["matriculas"][$i]["nom2"] = $arrTem["nom2"];
            $retorno["matriculas"][$i]["organizacion"] = $arrTem["organizacion"];
            $retorno["matriculas"][$i]["categoria"] = $arrTem["categoria"];
            $retorno["matriculas"][$i]["txtorganizacion"] = retornarRegistroMysqliApi($mysqli, "bas_organizacionjuridica", "id='" . $arrTem["organizacion"] . "'", "descripcion");
            $retorno["matriculas"][$i]["txtcategoria"] = retornarRegistroMysqliApi($mysqli, "bas_categorias", "id='" . $arrTem["categoria"] . "'", "descripcion");
            $retorno["matriculas"][$i]["identificacionpropietario"] = '';
            $retorno["matriculas"][$i]["identificacionrepresentantelegal"] = '';
            if ($arrTem["organizacion"] == '02') {
                $retorno["matriculas"][$i]["identificacionpropietario"] = $arrTem["propietarios"][1]["idtipoidentificacionpropietario"];
            }
            if ($arrTem["organizacion"] > '02') {
                $retorno["matriculas"][$i]["identificacionrepresentantelegal"] = $arrTem["replegal"][1]["identificacionreplegal"];
            }
            $retorno["matriculas"][$i]["ultimoanorenovado"] = $arrTem["ultanoren"];
            if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {
                $retorno["matriculas"][$i]["ultimosactivos"] = $arrTem["acttot"];
            } else {
                $retorno["matriculas"][$i]["ultimosactivos"] = $arrTem["actvin"];
            }
            $retorno["matriculas"][$i]["afiliado"] = $arrTem["afiliado"];
            $retorno["matriculas"][$i]["ultimoanoafiliado"] = $arrTem["ultanorenafi"];
            $retorno["matriculas"][$i]["propietariojurisdiccion"] = $propJurisdiccion;
            $retorno["matriculas"][$i]["disolucion"] = '';
            if ($arrTem["disueltaporvencimiento"] == 'si' || $arrTem["disueltaporacto510"] == 'si') {
                $retorno["matriculas"][$i]["disolucion"] = 'S';
            }
            $retorno["matriculas"][$i]["fechadisolucion"] = $arrTem["fechadisolucion"];
            $retorno["matriculas"][$i]["fechanacimiento"] = $arrTem["fechanacimiento"];
            $retorno["matriculas"][$i]["fechamatricula"] = $arrTem["fechamatricula"];
            $retorno["matriculas"][$i]["fecmatant"] = $arrTem["fecmatant"];
            $retorno["matriculas"][$i]["fecharenovacion"] = $arrTem["fecharenovacion"];
            $retorno["matriculas"][$i]["benart7"] = $arrTem["art7"];
            $retorno["matriculas"][$i]["benley1780"] = $arrTem["benley1780"];
            $retorno["matriculas"][$i]["circular19"] = '';
            $retorno["matriculas"][$i]["municipio"] = $arrTem["muncom"];
            $retorno["matriculas"][$i]["clasegenesadl"] = $arrTem["clasegenesadl"];
            $retorno["matriculas"][$i]["claseespesadl"] = $arrTem["claseespesadl"];
            $retorno["matriculas"][$i]["econsoli"] = $arrTem["claseeconsoli"];
            $retorno["matriculas"][$i]["expedienteinactivo"] = '';
            if ($arrTem["estadomatricula"] == 'MI' || $arrTem["estadomatricula"] == 'II') {
                $retorno["matriculas"][$i]["expedienteinactivo"] = 'S';
            }
            $retorno["matriculas"][$i]["dircom"] = $arrTem["dircom"];
            $retorno["matriculas"][$i]["emailcom"] = $arrTem["emailcom"];
            $retorno["matriculas"][$i]["telcom1"] = $arrTem["telcom1"];
            $retorno["matriculas"][$i]["telcom2"] = $arrTem["telcom2"];
            $retorno["matriculas"][$i]["telcom3"] = $arrTem["celcom"];
            $retorno["matriculas"][$i]["multadoponal"] = '';
        }

        if ($_SESSION["entrada"]["procesartodas"] == 'L' ||
                $_SESSION["entrada"]["procesartodas"] == 'S' ||
                $_SESSION["entrada"]["procesartodas"] == 'SP') {
            if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {
                foreach ($arrTem["establecimientos"] as $est) {
                    $i++;
                    $retorno["matriculas"][$i]["idtipoidentificacion"] = '';
                    $retorno["matriculas"][$i]["identificacion"] = '';
                    $retorno["matriculas"][$i]["cc"] = CODIGO_EMPRESA;
                    $retorno["matriculas"][$i]["matricula"] = $est["matriculaestablecimiento"];
                    $retorno["matriculas"][$i]["nombre"] = $est["nombreestablecimiento"];
                    $retorno["matriculas"][$i]["ape1"] = '';
                    $retorno["matriculas"][$i]["ape2"] = '';
                    $retorno["matriculas"][$i]["nom1"] = '';
                    $retorno["matriculas"][$i]["nom2"] = '';
                    $retorno["matriculas"][$i]["organizacion"] = '02';
                    $retorno["matriculas"][$i]["categoria"] = '';
                    $retorno["matriculas"][$i]["txtorganizacion"] = 'Establecimiento de comercio';
                    $retorno["matriculas"][$i]["txtcategoria"] = '';
                    $retorno["matriculas"][$i]["identificacionpropietario"] = $arrTem["identificacion"];
                    $retorno["matriculas"][$i]["identificacionrepresentantelegal"] = '';
                    $retorno["matriculas"][$i]["ultimoanorenovado"] = $est["ultanoren"];
                    $retorno["matriculas"][$i]["ultimosactivos"] = $est["actvin"];
                    $retorno["matriculas"][$i]["afiliado"] = '';
                    $retorno["matriculas"][$i]["ultimoanoafiliado"] = '';
                    $retorno["matriculas"][$i]["propietariojurisdiccion"] = $propJurisdiccion;
                    $retorno["matriculas"][$i]["disolucion"] = '';
                    $retorno["matriculas"][$i]["fechadisolucion"] = '';
                    $retorno["matriculas"][$i]["fechamatricula"] = $est["fechamatricula"];
                    $retorno["matriculas"][$i]["fecmatant"] = '';
                    $retorno["matriculas"][$i]["fecharenovacion"] = $est["fecharenovacion"];
                    $retorno["matriculas"][$i]["benart7"] = '';
                    $retorno["matriculas"][$i]["benley1780"] = '';
                    $retorno["matriculas"][$i]["circular19"] = '';
                    $retorno["matriculas"][$i]["municipio"] = $est["muncom"];
                    $retorno["matriculas"][$i]["clasegenesadl"] = '';
                    $retorno["matriculas"][$i]["claseespesadl"] = '';
                    $retorno["matriculas"][$i]["econsoli"] = '';
                    $retorno["matriculas"][$i]["expedienteinactivo"] = '';
                    if ($est["estadodatosestablecimiento"] == 'MI') {
                        $retorno["matriculas"][$i]["expedienteinactivo"] = 'S';
                    }
                    $retorno["matriculas"][$i]["dircom"] = $est["dircom"];
                    $retorno["matriculas"][$i]["emailcom"] = $est["emailcom"];
                    $retorno["matriculas"][$i]["telcom1"] = $est["telcom1"];
                    $retorno["matriculas"][$i]["telcom2"] = $est["telcom2"];
                    $retorno["matriculas"][$i]["telcom3"] = $est["telcom3"];
                    $retorno["matriculas"][$i]["multadoponal"] = '';
                }

                foreach ($arrTem["sucursalesagencias"] as $est) {
                    if ($est["estado"] == 'MA' || $est["estado"] == 'MI') {
                        $i++;
                        $retorno["matriculas"][$i]["idtipoidentificacion"] = '';
                        $retorno["matriculas"][$i]["identificacion"] = '';
                        $retorno["matriculas"][$i]["cc"] = CODIGO_EMPRESA;
                        $retorno["matriculas"][$i]["matricula"] = $est["matriculasucage"];
                        $retorno["matriculas"][$i]["nombre"] = $est["nombresucage"];
                        $retorno["matriculas"][$i]["ape1"] = '';
                        $retorno["matriculas"][$i]["ape2"] = '';
                        $retorno["matriculas"][$i]["nom1"] = '';
                        $retorno["matriculas"][$i]["nom2"] = '';
                        $retorno["matriculas"][$i]["organizacion"] = $arrTem["organizacion"];
                        $retorno["matriculas"][$i]["categoria"] = $est["categoria"];
                        $retorno["matriculas"][$i]["txtorganizacion"] = retornarRegistroMysqliApi($mysqli, "bas_organizacionjuridica", "id='" . $arrTem["organizacion"] . "'", "descripcion");
                        $retorno["matriculas"][$i]["txtcategoria"] = retornarRegistroMysqliApi($mysqli, "bas_categorias", "id='" . $est["categoria"] . "'", "descripcion");
                        $retorno["matriculas"][$i]["identificacionpropietario"] = $arrTem["identificacion"];
                        $retorno["matriculas"][$i]["identificacionrepresentantelegal"] = '';
                        $retorno["matriculas"][$i]["ultimoanorenovado"] = $est["ultanoren"];
                        $retorno["matriculas"][$i]["ultimosactivos"] = $est["actvin"];
                        $retorno["matriculas"][$i]["afiliado"] = '';
                        $retorno["matriculas"][$i]["ultimoanoafiliado"] = '';
                        $retorno["matriculas"][$i]["propietariojurisdiccion"] = 'S';
                        $retorno["matriculas"][$i]["disolucion"] = '';
                        $retorno["matriculas"][$i]["fechadisolucion"] = '';
                        $retorno["matriculas"][$i]["fechamatricula"] = $est["fechamatricula"];
                        $retorno["matriculas"][$i]["fecmatant"] = '';
                        $retorno["matriculas"][$i]["fecharenovacion"] = $est["fecharenovacion"];
                        $retorno["matriculas"][$i]["benart7"] = '';
                        $retorno["matriculas"][$i]["benley1780"] = '';
                        $retorno["matriculas"][$i]["circular19"] = '';
                        $retorno["matriculas"][$i]["municipio"] = $est["muncom"];
                        $retorno["matriculas"][$i]["clasegenesadl"] = '';
                        $retorno["matriculas"][$i]["claseespesadl"] = '';
                        $retorno["matriculas"][$i]["econsoli"] = '';
                        $retorno["matriculas"][$i]["expedienteinactivo"] = '';
                        if ($est["estado"] == 'MI') {
                            $retorno["matriculas"][$i]["expedienteinactivo"] = 'S';
                        }
                        $retorno["matriculas"][$i]["dircom"] = $est["dircom"];
                        $retorno["matriculas"][$i]["emailcom"] = $est["emailcom"];
                        $retorno["matriculas"][$i]["telcom1"] = $est["telcom1"];
                        $retorno["matriculas"][$i]["telcom2"] = $est["telcom2"];
                        $retorno["matriculas"][$i]["telcom3"] = $est["telcom3"];
                        $retorno["matriculas"][$i]["multadoponal"] = '';
                    }
                }

                if (!defined('RENOVACION_ACTIVAR_NACIONALES')) {
                    define('RENOVACION_ACTIVAR_NACIONALES', 'N');
                }
                if ($_SESSION["entrada"]["procesartodas"] == 'S' && substr(RENOVACION_ACTIVAR_NACIONALES, 0, 1) == 'S') {
                    $inat = 0;
                    foreach ($arrTem["establecimientosnacionales"] as $est) {
                        $i++;
                        $inat++;
                        $retorno["matriculas"][$i]["idtipoidentificacion"] = '';
                        $retorno["matriculas"][$i]["identificacion"] = '';
                        $retorno["matriculas"][$i]["cc"] = $est["cc"];
                        $retorno["matriculas"][$i]["matricula"] = $est["matriculaestablecimiento"];
                        $retorno["matriculas"][$i]["nombre"] = $est["nombreestablecimiento"];
                        $retorno["matriculas"][$i]["ape1"] = '';
                        $retorno["matriculas"][$i]["ape2"] = '';
                        $retorno["matriculas"][$i]["nom1"] = '';
                        $retorno["matriculas"][$i]["nom2"] = '';
                        $retorno["matriculas"][$i]["organizacion"] = $est["organizacion"];
                        $retorno["matriculas"][$i]["categoria"] = $est["categoria"];
                        $retorno["matriculas"][$i]["txtorganizacion"] = retornarRegistroMysqliApi($mysqli, "bas_organizacionjuridica", "id='" . $est["organizacion"] . "'", "descripcion");
                        $retorno["matriculas"][$i]["txtcategoria"] = retornarRegistroMysqliApi($mysqli, "bas_categorias", "id='" . $est["categoria"] . "'", "descripcion");
                        $retorno["matriculas"][$i]["identificacionpropietario"] = $arrTem["identificacion"];
                        $retorno["matriculas"][$i]["identificacionrepresentantelegal"] = '';
                        $retorno["matriculas"][$i]["ultimoanorenovado"] = $est["ultanoren"];
                        $retorno["matriculas"][$i]["ultimosactivos"] = $est["actvin"];
                        $retorno["matriculas"][$i]["afiliado"] = '';
                        $retorno["matriculas"][$i]["ultimoanoafiliado"] = '';
                        $retorno["matriculas"][$i]["propietariojurisdiccion"] = 'N';
                        $retorno["matriculas"][$i]["disolucion"] = '';
                        $retorno["matriculas"][$i]["fechadisolucion"] = '';
                        $retorno["matriculas"][$i]["fechamatricula"] = $est["fechamatricula"];
                        $retorno["matriculas"][$i]["fecmatant"] = '';
                        $retorno["matriculas"][$i]["fecharenovacion"] = $est["fecharenovacion"];
                        $retorno["matriculas"][$i]["benart7"] = '';
                        $retorno["matriculas"][$i]["benley1780"] = '';
                        $retorno["matriculas"][$i]["circular19"] = '';
                        $retorno["matriculas"][$i]["municipio"] = $est["muncom"];
                        $retorno["matriculas"][$i]["clasegenesadl"] = '';
                        $retorno["matriculas"][$i]["claseespesadl"] = '';
                        $retorno["matriculas"][$i]["econsoli"] = '';
                        $retorno["matriculas"][$i]["expedienteinactivo"] = '';
                        if ($est["estadodatosestablecimiento"] == 'MI') {
                            $retorno["matriculas"][$i]["expedienteinactivo"] = 'S';
                        }
                        $retorno["matriculas"][$i]["dircom"] = $est["dircom"];
                        $retorno["matriculas"][$i]["emailcom"] = $est["emailcom"];
                        $retorno["matriculas"][$i]["telcom1"] = $est["telcom1"];
                        $retorno["matriculas"][$i]["telcom2"] = $est["telcom2"];
                        $retorno["matriculas"][$i]["telcom3"] = $est["telcom3"];
                        $retorno["matriculas"][$i]["multadoponal"] = '';

                        //WSI 2018-02-26 Gestión de datos de establecimientos nacionales asociado a liquidación.
                        if (ltrim($_SESSION["entrada"]["idliquidacion"], "0") != 0) {
                            if ($inat == 1) {
                                borrarRegistrosMysqliApi($mysqli, 'mreg_establecimientos_nacionales', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"]);
                            }
                            $arrCampos = array(
                                'idliquidacion',
                                'cc',
                                'matricula',
                                'razonsocial',
                                'organizacion',
                                'categoria',
                                'estado',
                                'fechamatricula',
                                'fecharenovacion',
                                'ultanoren',
                                'dircom',
                                'barriocom',
                                'telcom1',
                                'telcom2',
                                'telcom3',
                                'muncom',
                                'emailcom',
                                'ctrubi',
                                'zonapostalcom',
                                'dirnot',
                                'barrionot',
                                'telnot1',
                                'munnot',
                                'emailnot',
                                'zonapostalnot',
                                'tipolocal',
                                'tipopropietario',
                                'afiliado',
                                'desactiv',
                                'ciiu1',
                                'shd1',
                                'ciiu2',
                                'shd2',
                                'ciiu3',
                                'shd3',
                                'ciiu4',
                                'shd4',
                                'personal',
                                'actvin'
                            );
                            $arrValores = array(
                                "'" . ltrim($_SESSION["entrada"]["idliquidacion"], "0") . "'",
                                "'" . $est["cc"] . "'",
                                "'" . ltrim($est["matriculaestablecimiento"], "0") . "'",
                                "'" . addslashes($est["nombreestablecimiento"]) . "'",
                                "'" . ($est["organizacion"]) . "'",
                                "'" . ($est["categoria"]) . "'",
                                "'" . ($est["estadomatricula"]) . "'",
                                "'" . ($est["fechamatricula"]) . "'",
                                "'" . ($est["fecharenovacion"]) . "'",
                                "'" . ($est["ultanoren"]) . "'",
                                "'" . addslashes($est["dircom"]) . "'",
                                "'" . addslashes($est["nbarriocom"]) . "'",
                                "'" . ($est["telcom1"]) . "'",
                                "'" . ($est["telcom2"]) . "'",
                                "'" . ($est["telcom3"]) . "'",
                                "'" . ($est["muncom"]) . "'",
                                "'" . addslashes($est["emailcom"]) . "'",
                                "'" . ($est["ctrubi"]) . "'",
                                "'" . ($est["codpostalcom"]) . "'",
                                "'" . addslashes($est["dirnot"]) . "'",
                                "'" . addslashes($est["nbarrionot"]) . "'",
                                "''",
                                "'" . ($est["munnot"]) . "'",
                                "'" . addslashes($est["emailnot"]) . "'",
                                "'" . ($est["codpostalnot"]) . "'",
                                "'" . ($est["tipolocal"]) . "'",
                                "'" . ($est["tipopropietario"]) . "'",
                                "'" . ($est["afiliado"]) . "'",
                                "'" . addslashes($est["desactiv"]) . "'",
                                "'" . ($est["ciiu1"]) . "'",
                                "'" . ($est["shd1"]) . "'",
                                "'" . ($est["ciiu2"]) . "'",
                                "'" . ($est["shd2"]) . "'",
                                "'" . ($est["ciiu3"]) . "'",
                                "'" . ($est["shd3"]) . "'",
                                "'" . ($est["ciiu4"]) . "'",
                                "'" . ($est["shd4"]) . "'",
                                intval($est["personal"]),
                                doubleval($est["actvin"])
                            );
                            $res = insertarRegistrosmysqliApi($mysqli, 'mreg_establecimientos_nacionales', $arrCampos, $arrValores);
                            if ($res == false) {
                                $mysqli->close();
                                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                                $_SESSION["jsonsalida"]["mensajeerror"] = 'Error grabado establecimientos nacionales' . $_SESSION["generales"]["mensajeerror"];
                                $api->response($api->json($_SESSION["jsonsalida"]), 200);
                            }
                        }
                        //FIN WSI
                    }
                }
            }
        }

        // Confirma si tiene o no beneficio de la Ley 1780
        // Aplica para el 2017
        if (date("Y") == '2017') {
            if ($retorno["matriculas"][0]["organizacion"] == '01' ||
                    ($retorno["matriculas"][0]["organizacion"] > '02' &&
                    $retorno["matriculas"][0]["categoria"] == '1')) {
                if ($retorno["matriculas"][0]["fechamatricula"] >= '20160502') {
                    if ($retorno["matriculas"][0]["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matriculas"][0]["matricula"] . "' and servicio='01090110' and (fecoperacion between '20160101' and '20161231')") > 0) {
                            $retorno["matriculas"][0]["benley1780"] = 'S';
                            $retorno["matriculas"]["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2018
        if (date("Y") == '2018') {
            if ($retorno["matriculas"][0]["organizacion"] == '01' || ($retorno["matriculas"][0]["organizacion"] > '02' && $retorno["matriculas"][0]["categoria"] == '1')) {
                if ($retorno["matriculas"][0]["fechamatricula"] >= '20170101') {
                    if ($retorno["matriculas"][0]["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matriculas"][0]["matricula"] . "' and servicio='01090110' and (fecoperacion between '20170101' and '20171231')") > 0) {
                            $retorno["matriculas"][0]["benley1780"] = 'S';
                            $retorno["matriculas"]["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        // Aplica para el 2019
        if (date("Y") == '2019') {
            if ($retorno["matriculas"][0]["organizacion"] == '01' || ($retorno["matriculas"][0]["organizacion"] > '02' && $retorno["matriculas"][0]["categoria"] == '1')) {
                if ($retorno["matriculas"][0]["fechamatricula"] >= '20180101') {
                    if ($retorno["matriculas"][0]["benley1780"] == '') {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $retorno["matriculas"][0]["matricula"] . "' and servicio='01090110' and (fecoperacion between '20180101' and '20181231')") > 0) {
                            $retorno["matriculas"][0]["benley1780"] = 'S';
                            $retorno["matriculas"]["benley1780"] = 'S';
                        }
                    }
                }
            }
        }

        //
        // Verifica codigo de policia
        if (!defined('ACTIVAR_CONTROL_MULTAS_PONAL')) {
            define('ACTIVAR_CONTROL_MULTAS_PONAL', 'NO');
        }

        //
        if (ACTIVAR_CONTROL_MULTAS_PONAL == 'SI-NOBLOQUEAR') {
            if ($retorno["matriculas"][0]["organizacion"] == '01' && $_SESSION["entrada"]["procesartodas"] != 'SP') {
                $resx = \funcionesGenerales::consultarMultasPolicia($mysqli, $retorno["matriculas"][0]["idtipoidentificacion"], $retorno["matriculas"][0]["identificacion"], $_SESSION ["tramite"]["idliquidacion"]);
                if ($resx == 'SI') {
                    $retorno["matriculas"][0]["multadoponal"] = 'S';
                    $retorno["multadoponal"] = 'S';
                } else {
                    $retorno["matriculas"][0]["multadoponal"] = 'N';
                    $retorno["multadoponal"] = 'N';
                }
            }
        }

        //
        if (ACTIVAR_CONTROL_MULTAS_PONAL == 'SI-BLOQUEAR') {
            if ($retorno["matriculas"][0]["organizacion"] == '01' && $_SESSION["entrada"]["procesartodas"] != 'SP') {
                $resx = \funcionesGenerales::consultarMultasPolicia($mysqli, $retorno["matriculas"][0]["idtipoidentificacion"], $retorno["matriculas"][0]["identificacion"], $_SESSION ["tramite"]["idliquidacion"]);
                if ($resx == 'SI') {
                    if (isset($retorno["matriculas"][1])) {
                        $_SESSION["jsonsalida"]["codigoerror"] = '5000';
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'Se ha encontrado que la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'por la Policía Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s) - ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'No ha(n) sido pagada(s).<br><br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'Para realizar la renovación de la matrícula asociada con la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'se deberá(n) pagar la(s) misma(s) y esperar a que sea(n) registrada(s) en el ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'Sistema Nacional de Medidas Correctiva.<br><br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/verMultas.php?idliquidacion=' . $_SESSION ["tramite"]["idliquidacion"] . '" target="_blank">Ver Las multas</a><br><br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'Si desea continuar con el proceso de renovación de las matrículas asociadas ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'a la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' oprima el ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'siguiente enlace<br><br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregRenovacionMatriculaSii1.php?accion=validarseleccionnuevasinpropietario&procesartodas=SP&matricula=' . $retorno["matriculas"][0]["matricula"] . '&identificacion=' . $retorno["matriculas"][0]["identificacion"] . '&cancelarmatricula=' . $_SESSION["entrada"]["cancelarmatricula"] . '&benley1780=' . $_SESSION["entrada"]["benley1780"] . '">Renovar establecimientos sucursales y agencias</a><br><br>';
                        $_SESSION["jsonsalida"]["matriculas"] = array();
                    } else {
                        $_SESSION["jsonsalida"]["codigoerror"] = '5000';
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'Se ha encontrado que la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'tiene multas reportadas al Registro Nacional de Medidas Correctivas (RNMC) administrado ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'por la Policía Nacional y alguna(s) de ella(s) se encuentra(n) vencida(s) - ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'No ha(n) sido pagada(s).<br><br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'Para realizar la renovación de la matrícula asociada con la identificación ' . $retorno["matriculas"][0]["identificacion"] . ' ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'se deberá(n) pagar la(s) misma(s) y esperar a que sea(n) registrada(s) en el ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'Sistema Nacional de Medidas Correctiva.<br><br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/verMultas.php?idliquidacion=' . $_SESSION ["tramite"]["idliquidacion"] . '" target="_blank">Ver Las multas</a><br><br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'No es posible continuar con el proceso de renovación. ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'Para volver al módulo de renovación y seleccionar otro expediente ';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'oprima el siguiente enlace.<br><br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregRenovacionMatriculaSii1.php?accion=pantallaseleccion">Abandonar renovación</a><br><br>';
                        $_SESSION["jsonsalida"]["matriculas"] = array();
                    }
                } else {
                    $retorno["matriculas"][0]["multadoponal"] = 'N';
                    $retorno["multadoponal"] = 'N';
                }
            }
        }

        //
        // Datos básicos del trámite
        $_SESSION["tramite"]["emailcontrol"] = $_SESSION["generales"]["emailusuariocontrol"];
        $_SESSION["tramite"]["idexpedientebase"] = $retorno["idexpedientebase"];
        $_SESSION["tramite"]["idmatriculabase"] = $retorno["idmatriculabase"];
        $_SESSION["tramite"]["nombrebase"] = $retorno["nombrebase"];
        $_SESSION["tramite"]["nom1base"] = $retorno["nom1base"];
        $_SESSION["tramite"]["nom2base"] = $retorno["nom2base"];
        $_SESSION["tramite"]["ape1base"] = $retorno["ape1base"];
        $_SESSION["tramite"]["ape2base"] = $retorno["ape2base"];
        $_SESSION["tramite"]["tipoidentificacionbase"] = $retorno["tipoidentificacionbase"];
        $_SESSION["tramite"]["identificacionbase"] = $retorno ["identificacionbase"];
        $_SESSION["tramite"]["organizacionbase"] = $retorno["organizacionbase"];
        $_SESSION["tramite"]["categoriabase"] = $retorno["categoriabase"];
        $_SESSION["tramite"]["afiliadobase"] = $retorno["afiliadobase"];

        // Datos del cliente
        $_SESSION["tramite"]["idtipoidentificacioncliente"] = $_SESSION["tramite"]["tipoidentificacionbase"];
        $_SESSION["tramite"]["identificacioncliente"] = $_SESSION["tramite"]["identificacionbase"];
        $_SESSION["tramite"]["nombrecliente"] = $_SESSION["tramite"]["nombrebase"];
        $_SESSION["tramite"]["nombre1cliente"] = $_SESSION["tramite"]["nom1base"];
        $_SESSION["tramite"]["nombre2cliente"] = $_SESSION["tramite"]["nom2base"];
        $_SESSION["tramite"]["apellido1cliente"] = $_SESSION["tramite"]["ape1base"];
        $_SESSION["tramite"]["apellido2cliente"] = $_SESSION["tramite"]["ape2base"];

        $_SESSION["tramite"]["email"] = $retorno ["email"];
        $_SESSION["tramite"]["direccion"] = $retorno["direccion"];
        $_SESSION["tramite"]["idmunicipio"] = $retorno["idmunicipio"];

        if ($_SESSION["tramite"]["benley1780"] == 'S') {
            $_SESSION["tramite"]["benley1780"] = $retorno["benley1780"];
        }

        // 2017-12-16: JINT: Multado ponal
        if ($retorno["multadoponal"] == 'S') {
            $_SESSION["tramite"]["multadoponal"] = 'S';
        } else {
            $_SESSION["tramite"]["multadoponal"] = 'N';
        }

        $_SESSION ["tramite"]["telefono"] = $retorno["telefono"];
        $_SESSION ["tramite"]["movil"] = $retorno["movil"];

        //
        if ($_SESSION["tramite"]["idtipoidentificacioncliente"] == '2') {
            $_SESSION["tramite"]["tipocliente"] = 'PJ';
        } else {
            $_SESSION["tramite"]["tipocliente"] = 'PN';
        }

        $_SESSION["tramite"]["razonsocialcliente"] = $_SESSION["tramite"]["nombrecliente"];

        // Datos del pagador
        if ($_SESSION["tramite"]["idtipoidentificacioncliente"] == '2') {
            $_SESSION["tramite"]["tipopagador"] = 'PJ';
        } else {
            $_SESSION["tramite"]["tipopagador"] = 'PN';
        }
        $_SESSION["tramite"]["tipoidentificacionpagador"] = $_SESSION["tramite"]["idtipoidentificacioncliente"];
        $_SESSION["tramite"]["identificacionpagador"] = $_SESSION["tramite"]["identificacioncliente"];

        $_SESSION["tramite"]["razonsocialpagador"] = $_SESSION["tramite"]["razonsocialcliente"];
        $_SESSION["tramite"]["nombre1pagador"] = $_SESSION["tramite"]["nombre1cliente"];
        $_SESSION["tramite"]["nombre2pagador"] = $_SESSION["tramite"]["nombre2cliente"];
        $_SESSION["tramite"]["apellido1pagador"] = $_SESSION["tramite"]["apellido1cliente"];
        $_SESSION["tramite"]["apellido2pagador"] = $_SESSION["tramite"]["apellido2cliente"];
        $_SESSION["tramite"]["telefonopagador"] = $_SESSION ["tramite"]["telefono"];
        $_SESSION["tramite"]["movilpagador"] = $_SESSION ["tramite"]["movil"];
        $_SESSION["tramite"]["emailpagador"] = $_SESSION ["tramite"]["email"];

        /**
         * Verifica que no existan procesos previos de liquidación para la matrícula que estén en proceso de pago electrónico
         * Valida la tabla mreg_liquidacion:
         * tipo de trámite "renovacionmatricula" o "renovacionesadl"
         * Número de identificacion = número de identificación base
         * Estado = '06'
         * idexpediente = Numero de matrícula seleccionada
         */
        if (trim((string)$_SESSION ["tramite"]["idexpedientebase"], '0') != '') {
            $condicion = "idexpedientebase='" . ltrim(trim((string)$_SESSION["tramite"]["idexpedientebase"]), '0') . "' and ";
            $condicion .= "(tipotramite='renovacionmatricula' or tipotramite='renovacionesadl') and ";
            $condicion .= "idestado='06'";

            //
            $cantidad = contarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', $condicion);
            if ($cantidad > 0) {
                $regliq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', $condicion);
                if (($regliq === false) || (empty($regliq))) {
                    $num1rec = '';
                } else {
                    $num1rec = trim((string)$regliq ["numerorecuperacion"]);
                }
                unset($regliq);
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION ["jsonsalida"]["mensajeerror"] = 'No se puede iniciar la operación debido a que el número de referencia o número de factura utilizado se ';
                $_SESSION ["jsonsalida"]["mensajeerror"] .= 'encuentra actualmente asociado a otro proceso de pago iniciado previamente, por  favor espere unos minutos e ';
                $_SESSION ["jsonsalida"]["mensajeerror"] .= 'intente nuevamente hasta que el sistema obtenga el resultado final de la transacción.';
                if ($num1rec != '') {
                    $_SESSION ["jsonsalida"]["mensajeerror"] .= ' Número de recuperación: ' . $num1rec;
                }
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // Verifica que las matriculas no esten inactivas
        $caninactivas = 0;
        if (!defined('FECHA_CONTROL_INACTIVAS')) {
            define('FECHA_CONTROL_INACTIVAS', '20140601');
        }
        if (date("Ymd") >= FECHA_CONTROL_INACTIVAS) {
            $matinactiva = '';
            $nominactiva = '';
            $caninactivas = 0;
            foreach ($retorno["matriculas"] as $m) {
                if ($m ["expedienteinactivo"] == '1' || $m ["expedienteinactivo"] == 'S') {
                    $caninactivas ++;
                    if ($caninactivas == 1) {
                        $matinactiva = $m ["matricula"];
                        $nominactiva = $m ["nombre"];
                    }
                }
            }
        }



        // Inicializa arreglo del tramite
        $_SESSION ["tramite"]["caninactivas"] = $caninactivas;
        $_SESSION ["tramite"]["fecha"] = date("Ymd");
        $_SESSION ["tramite"]["hora"] = date("H:i:s");
        $_SESSION ["tramite"]["idusuario"] = $_SESSION ["entrada"]["idusuario"];
        $_SESSION ["tramite"]["tipotramite"] = "renovacionmatricula";
        if (($retorno["matriculas"][0]["organizacion"] == '12') || ($retorno["matriculas"][0]["organizacion"] == '14')) {
            if ($retorno["matriculas"][0]["categoria"] == '1') {
                $_SESSION ["tramite"]["tipotramite"] = "renovacionesadl";
            }
        }
        $_SESSION ["tramite"]["iptramite"] = $_SESSION["entrada"]["ip"];
        $_SESSION ["tramite"]["idestado"] = '01';
        $_SESSION ["tramite"]["idexpedientebase"] = $_SESSION ["tramite"]["matriculabase"];
        if (substr($_SESSION ["generales"]["tipousuario"], 0, 2) == '06') {
            $_SESSION ["tramite"]["idtipoidentificacioncliente"] = ltrim((string)$_SESSION ["generales"]["idtipoidentificacionusuario"], '0');
            $_SESSION ["tramite"]["identificacioncliente"] = ltrim((string)$_SESSION ["generales"]["identificacionusuario"], '0');
            $_SESSION ["tramite"]["nombrecliente"] = $_SESSION ["generales"]["nombreusuario"];
            $_SESSION ["tramite"]["email"] = $_SESSION ["generales"]["emailusuario"];
            $_SESSION ["tramite"]["direccion"] = $_SESSION ["generales"]["direccionusuario"];
            $_SESSION ["tramite"]["idmunicipio"] = $_SESSION ["generales"]["idmunicipiousuario"];
            $_SESSION ["tramite"]["telefono"] = $_SESSION ["generales"]["telefonousuario"];
            $_SESSION ["tramite"]["movil"] = $_SESSION ["generales"]["movilusuario"];
        }

        //
        $i = 0;

        //
        $candidatoreafiliacion = "no";
        $porrenovar = 0;
        $disueltos = 0;
        $circular19 = 0;
        $_SESSION["tramite"]["bloquear5anios"] = 'no';
        $inactivas = '';

        $ix1 = 0;
        foreach ($retorno["matriculas"] as $matricula) {

            if ($matricula["expedienteinactivo"] == 'S') {
                $inactivas .= $matricula["matricula"] . ' ';
            }

            // 2017-06-07 : JINT : AlertaTemprana
            $ix1++;
            if ($ix1 == 1) {
                \funcionesGenerales::programarAlertaTemprana($mysqli, 'RegMer', $_SESSION["tramite"]["idliquidacion"], $matricula["matricula"], '', 'renovacion');
            }

            //
            $ultimo = $matricula ["ultimoanorenovado"];

            // 2016-03-31 : JINT: Control para bloqueo de los últimos 5 años
            if ((date("Y") - $ultimo) >= 5) {
                $_SESSION["tramite"]["bloquear5anios"] = 'si';
            }

            //
            if (($matricula ["organizacion"] != '02') && ($matricula ["categoria"] != '2') && ($matricula ["categoria"] != '3')) {
                if ($_SESSION["tramite"]["ctrcancelacion"] == 'SI') {
                    if (date("Ymd") > $fcorte) {
                        $actual = intval(date("Y"));
                    } else {
                        if ($ultimo < date("Y") - 1) {
                            $actual = intval(date("Y")) - 1;
                        } else {
                            $actual = $ultimo;
                        }
                    }
                } else {
                    if ($matricula ["disolucion"] == 'S') {
                        $actual = date("Y");
                        $disueltos = 1;
                    } else {
                        $actual = date("Y");
                    }
                }
            } else {
                if ($_SESSION ["tramite"]["ctrcancelacion"] == 'SI') {
                    if (date("Ymd") > $fcorte) {
                        $actual = intval(date("Y"));
                    } else {
                        if ($ultimo < date("Y") - 1) {
                            $actual = intval(date("Y")) - 1;
                        } else {
                            $actual = $ultimo;
                        }
                    }
                } else {
                    $actual = date("Y");
                }
            }

            //
            if ($ultimo == $actual) {
                $_SESSION["tramite"]["reliquidacion"] = 'si';
            }

            if (($matricula ["organizacion"] != '02') && ($matricula ["categoria"] != '2') && ($matricula ["categoria"] != '3')) {
                $anosdebe = 0;
                // $beneficiario = 'N';
                $beneficiario = $matricula ["benart7"];
                for ($j = $ultimo + 1; $j <= $actual; $j ++) {
                    $anosdebe ++;
                    if ($j == $actual) {
                        $beneficiario = $matricula ["benart7"];
                    }
                }
                if ($beneficiario == "S") {
                    if ($anosdebe > 1) {
                        $beneficiario = 'P';
                    } else {
                        if ((date("Ymd") <= $fcorte || $_SESSION ["tramite"]["reliquidacion"] == 'si')) {
                            $ben1780okx = '';
                        } else {
                            $beneficiario = 'P';
                        }
                    }
                }
            } else {
                $beneficiario = 'N';
            }

            if (($matricula ["organizacion"] != '02') && ($matricula ["categoria"] != '2') && ($matricula ["categoria"] != '3')) {
                $anosdebe = 0;
                $beneficiario1780 = $matricula ["benley1780"];
                for ($j = $ultimo + 1; $j <= $actual; $j ++) {
                    $anosdebe ++;
                    if ($j == $actual) {
                        $beneficiario1780 = $matricula ["benley1780"];
                    }
                }
                if ($beneficiario1780 == 'S') {
                    if ($anosdebe > 1) {
                        $beneficiario1780 = 'P';
                    } else {
                        // 2017-11-26: JINT: Se incluye este control para permitir hacer pruebas de Ley 1780
                        // en los ambientes de pruebas y en fechas posteriores al 2017-03-31
                        if (TIPO_AMBIENTE == 'PRUEBAS' || TIPO_AMBIENTE == 'QA') {
                            $ben1780okx = '';
                        } else {
                            if ((date("Ymd") <= $fcorte || $_SESSION ["tramite"]["reliquidacion"] == 'si')) {
                                $ben1780okx = '';
                            } else {
                                $beneficiario1780 = 'P';
                            }
                        }
                    }
                }
                // }
            } else {
                $beneficiario1780 = 'N';
            }


            //
            // Control incluido en mayo 29 de 2013
            // Por solicitud de la CC Neiva
            if ($matricula ["afiliado"] == 'E' || $matricula ["afiliado"] == 'D') {
                if (intval($matricula ["ultimoanoafiliado"]) == ($actual - 1)) {
                    $candidatoreafiliacion = "si";
                }
            }

            if ($ultimo < $actual) {
                for ($j = $ultimo + 1; $j <= $actual; $j ++) {
                    $incluir = 'si';
                    if (($matricula["organizacion"] == '12' ||
                            $matricula["organizacion"] == '14') && $matricula["categoria"] == '1') {
                        if ($j < '2013') {
                            $incluir = 'no';
                        }
                    }
                    if ($incluir == 'si') {
                        $temp = array();
                        $porrenovar ++;
                        $i ++;
                        if ($j == $actual) {
                            $temp ["registrobase"] = 'S';
                        } else {
                            $temp ["registrobase"] = 'N';
                        }
                        $temp ["cc"] = $matricula ["cc"];
                        $temp ["textocc"] = retornarRegistroMysqliApi($mysqli, "bas_camaras", "id='" . $matricula ["cc"] . "'", "nombre");
                        $temp ["matricula"] = $matricula ["matricula"];
                        $temp ["proponente"] = '';
                        $temp ["numrue"] = '';
                        $temp ["idtipoidentificacion"] = $matricula ["idtipoidentificacion"];
                        $temp ["identificacion"] = ltrim($matricula ["identificacion"], '0');
                        $temp ["razonsocial"] = mb_strtoupper($matricula["nombre"], 'utf-8');
                        $temp ["ape1"] = $matricula ["ape1"];
                        $temp ["ape2"] = $matricula ["ape2"];
                        $temp ["nom1"] = $matricula ["nom1"];
                        $temp ["nom2"] = $matricula ["nom2"];
                        $temp ["organizacion"] = $matricula ["organizacion"];
                        $temp ["categoria"] = $matricula ["categoria"];
                        $temp ["txtorganizacion"] = retornarRegistroMysqliApi($mysqli, "bas_organizacionjuridica", "id='" . $matricula ["organizacion"] . "'", "descripcion");
                        $temp ["txtcategoria"] = '';
                        switch ($temp["categoria"]) {
                            case "1" :$temp ["txtcategoria"] = 'Principal';
                                break;
                            case "2" :$temp ["txtcategoria"] = 'Sucursal';
                                break;
                            case "3" :$temp ["txtcategoria"] = 'Agencia';
                                break;
                        }

                        $temp ["identificacionpropietario"] = $matricula ["identificacionpropietario"];
                        $temp ["identificacionrepresentantelegal"] = $matricula ["identificacionrepresentantelegal"];
                        $temp ["afiliado"] = $matricula ["afiliado"];
                        if ($j == $actual) {
                            $temp ["ultimoanoafiliado"] = $matricula ["ultimoanoafiliado"];
                            if ($matricula ["ultimoanoafiliado"] == $actual) {
                                $temp ["afiliado"] = 'N';
                            }
                        } else {
                            $temp ["ultimoanoafiliado"] = '0000';
                        }
                        $temp ["ultimoanorenovado"] = $j;
                        if ($temp ["registrobase"] == 'S') {
                            $temp ["primeranorenovado"] = $ultimo + 1;
                        } else {
                            $temp ["primeranorenovado"] = '';
                        }
                        $temp ["propietariojurisdiccion"] = $matricula ["propietariojurisdiccion"];
                        $temp ["ultimosactivos"] = $matricula ["ultimosactivos"];
                        // Arma arreglo de expedientes

                        $filtro = "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"] . " and matricula=" . $temp["matricula"] . " and ultimoanorenovado=" . $temp["ultimoanorenovado"];

                        $arrExpLiq = retornarRegistroMysqliApi($mysqli, "mreg_liquidacionexpedientes", $filtro, '*');

                        if (!$arrExpLiq || empty($arrExpLiq)) {
                            $temp ["nuevosactivos"] = 0;
                            $temp ["fechanacimiento"] = '';
                            $temp ["reliquidacion"] = '';
                            $temp ["renovaresteano"] = '';
                        } else {
                            $temp ["nuevosactivos"] = $arrExpLiq["nuevosactivos"];
                            $temp ["fechanacimiento"] = $arrExpLiq["fechanacimiento"];
                            $temp ["reliquidacion"] = $arrExpLiq["reliquidacion"];
                            $temp ["renovaresteano"] = $arrExpLiq["renovaresteano"];
                        }
                        unset($arrExpLiq);

                        $temp ["actividad"] = '';
                        $temp ["benart7"] = $beneficiario;
                        $temp ["benley1780"] = $beneficiario1780;
                        $temp ["disolucion"] = $matricula ["disolucion"];
                        $temp ["fechadisolucion"] = $matricula ["fechadisolucion"];
                        $temp ["fechamatricula"] = $matricula ["fechamatricula"];
                        $temp ["fecmatant"] = $matricula ["fecmatant"];
                        $temp ["fecharenovacion"] = $matricula ["fecharenovacion"];
                        $temp ["clasegenesadl"] = $matricula ["clasegenesadl"];
                        $temp ["claseespesadl"] = $matricula ["claseespesadl"];
                        $temp ["econsoli"] = $matricula ["econsoli"];

                        $selsi = '';
                        $selno = '';
                        $selin = '';
                        if ($matricula ["disolucion"] == 'S') {
                            $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . $j . "'", "corte");
                            if ($matricula ["fechadisolucion"] <= $fcorte1) {
                                $temp ["renovaresteano"] = 'no';
                                $selno = 'S';
                            } else {
                                $selsi = 'S';
                            }
                        } else {
                            //WSI 2018-02-26
                            if (trim($temp ["renovaresteano"]) == 'no') {
                                $selno = 'S';
                            } else {
                                $selsi = 'S';
                            }
                        }

                        //
                        $temp["renovaresteanosii2"] = array();
                        $temp["renovaresteanosii2"][] = array(
                            'label' => 'SI',
                            'val' => 'si',
                            'selected' => $selsi,
                            'name' => 'renovaresteano'
                        );
                        $temp["renovaresteanosii2"][] = array(
                            'label' => 'NO',
                            'val' => 'no',
                            'selected' => $selno,
                            'name' => 'renovaresteano'
                        );
                        if ($_SESSION["generales"]["escajero"] == 'SI') {
                            $temp["renovaresteanosii2"][] = array(
                                'label' => 'INACT',
                                'val' => 'in',
                                'selected' => $selin,
                                'name' => 'renovaresteano'
                            );
                        }
                        //
                        $_SESSION ["tramite"]["expedientes"][] = $temp;
                    }
                }
            } else {

                if ($_SESSION ["tramite"]["ctrcancelacion"] != 'SI') {
                    $_SESSION ["tramite"]["reliquidacion"] = 'si';
                    $i ++;
                    $temp = array();
                    $temp ["registrobase"] = 'S';
                    $temp ["cc"] = $matricula ["cc"];
                    $temp ["textocc"] = retornarRegistroMysqliApi($mysqli, "bas_camaras", "id='" . $matricula ["cc"] . "'", "nombre");
                    $temp ["matricula"] = $matricula ["matricula"];
                    $temp ["proponente"] = '';
                    $temp ["numrue"] = '';
                    $temp ["idtipoidentificacion"] = $matricula ["idtipoidentificacion"];
                    $temp ["identificacion"] = ltrim((string)$matricula ["identificacion"], '0');
                    $temp ["razonsocial"] = mb_strtoupper($matricula["nombre"], 'utf-8');
                    $temp ["ape1"] = $matricula ["ape1"];
                    $temp ["ape2"] = $matricula ["ape2"];
                    $temp ["nom1"] = $matricula ["nom1"];
                    $temp ["nom2"] = $matricula ["nom2"];
                    $temp ["organizacion"] = $matricula ["organizacion"];
                    $temp ["categoria"] = $matricula ["categoria"];
                    $temp ["identificacionpropietario"] = $matricula ["identificacionpropietario"];
                    $temp ["identificacionrepresentantelegal"] = $matricula ["identificacionrepresentantelegal"];
                    $temp ["afiliado"] = $matricula ["afiliado"];
                    $temp ["ultimoanoafiliado"] = $matricula ["ultimoanoafiliado"];
                    $temp ["ultimoanorenovado"] = $actual;
                    $temp ["primeranorenovado"] = $actual;
                    $temp ["propietariojurisdiccion"] = $matricula ["propietariojurisdiccion"];
                    $temp ["ultimosactivos"] = $matricula ["ultimosactivos"];
                    $temp ["nuevosactivos"] = 0;
                    $temp ["actividad"] = '';
                    $temp ["benart7"] = $beneficiario;
                    $temp ["benley1780"] = $beneficiario1780;
                    $temp ["disolucion"] = $matricula ["disolucion"];
                    $temp ["fechadisolucion"] = $matricula ["fechadisolucion"];
                    $temp ["fechamatricula"] = $matricula ["fechamatricula"];
                    $temp ["fecmatant"] = $matricula ["fecmatant"];
                    $temp ["fecharenovacion"] = $matricula ["fecharenovacion"];
                    $temp ["clasegenesadl"] = $matricula ["clasegenesadl"];
                    $temp ["claseespesadl"] = $matricula ["claseespesadl"];
                    $temp ["econsoli"] = $matricula ["econsoli"];
                    $temp ["reliquidacion"] = 'si';
                    $temp ["renovaresteano"] = 'si';
                    $selsi = '';
                    $selno = '';
                    $selin = '';
                    if ($matricula ["disolucion"] == 'S') {
                        $fcorte1 = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . $j . "'", "corte");
                        if ($matricula ["fechadisolucion"] <= $fcorte1) {
                            $temp ["renovaresteano"] = 'no';
                            $selno = 'S';
                        } else {
                            $selsi = 'S';
                        }
                    } else {
                        //WSI 2018-02-26
                        if (trim($temp ["renovaresteano"]) == 'no') {
                            $selno = 'S';
                        } else {
                            $selsi = 'S';
                        }
                    }

                    //
                    $temp["renovaresteanosii2"] = array();
                    $temp["renovaresteanosii2"][] = array(
                        'label' => 'SI',
                        'val' => 'si',
                        'selected' => $selsi,
                        //'selected' => '',
                        'name' => 'renovaresteano'
                    );
                    $temp["renovaresteanosii2"][] = array(
                        'label' => 'NO',
                        'val' => 'no',
                        'selected' => $selno,
                        'name' => 'renovaresteano'
                    );
                    if ($_SESSION["generales"]["escajero"] == 'SI') {
                        $temp["renovaresteanosii2"][] = array(
                            'label' => 'INACT',
                            'val' => 'in',
                            'selected' => $selin,
                            'name' => 'renovaresteano'
                        );
                    }
                    $_SESSION ["tramite"]["expedientes"][] = $temp;
                }
            }
            if ($ix1 == 1) {
                $tempBase = $temp;
            }
        }

        // ********************************************************************* //
        // Salva la liquidacion, toma como base $_SESSION["tramite"]
        // ********************************************************************* //
        \funcionesRegistrales::grabarLiquidacionMreg($mysqli);

        //        
        if ($_SESSION["jsonsalida"]["codigoerror"] == '0000') {
            if ($inactivas != '') {
                $_SESSION["jsonsalida"]["codigoerror"] = '5000';
                $_SESSION["jsonsalida"]["mensajeerror"] = '!!! IMPORTANTE !!!<br><br>';
                $_SESSION["jsonsalida"]["mensajeerror"] .= 'Se han encontrado matrículas inactivas (' . trim($inactivas) . '), estsa deben ser reactivadas ';
                $_SESSION["jsonsalida"]["mensajeerror"] .= 'antes de continuar con el proceso de renovación.';
                $mysqli->close();
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if ($porrenovar == 0) {
                if ($_SESSION ["tramite"]["reliquidacion"] == 'si') {
                    $_SESSION["jsonsalida"]["codigoerror"] = '4000';
                    $_SESSION["jsonsalida"]["mensajeerror"] = '!!! IMPORTANTE !!!<br><br>';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'Los expedientes se encuentran al día (renovados), por lo tanto si continua con el proceso<br>';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'realizará una RELIQUIDACION DE ACTIVOS<br>';
                } else {
                    if ($_SESSION ["tramite"]["ctrcancelacion"] == 'SI') {
                        if (date("Ymd") <= $fcorte) {
                            $_SESSION["jsonsalida"]["codigoerror"] = '4000';
                            $_SESSION["jsonsalida"]["mensajeerror"] = '!!! IMPORTANTE !!!<br><br>';
                            $_SESSION["jsonsalida"]["mensajeerror"] .= 'El usuario  ha  marcado que  renovará para cancelar pero<br>';
                            $_SESSION["jsonsalida"]["mensajeerror"] .= 'el expediente  se   encuentra  al  día  en  su  renovación,<br>';
                            $_SESSION["jsonsalida"]["mensajeerror"] .= 'Dado que aún no ha pasado el ' . \funcionesGenerales::mostrarFechaLetras1($fcorte) . ', no es obligatorio ';
                            $_SESSION["jsonsalida"]["mensajeerror"] .= 'que renueve.<br>';
                        } else {
                            $_SESSION["jsonsalida"]["codigoerror"] = '4000';
                            $_SESSION["jsonsalida"]["mensajeerror"] = '!!! IMPORTANTE !!!<br><br>';
                            $_SESSION["jsonsalida"]["mensajeerror"] .= 'Aparentemente no se encontraron a&ntilde;os para renovar,<br>';
                            $_SESSION["jsonsalida"]["mensajeerror"] .= 'por favor  informe  este hecho al administrador de<br>';
                            $_SESSION["jsonsalida"]["mensajeerror"] .= 'la Cámara de Comercio si considera que es un error.<br>';
                        }
                    } else {
                        $_SESSION["jsonsalida"]["codigoerror"] = '4000';
                        $_SESSION["jsonsalida"]["mensajeerror"] = '!!! IMPORTANTE !!!<br><br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'Aparentemente no se encontraron a&ntilde;os para renovar,<br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'por favor  informe  este hecho al administrador de<br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'la Cámara de Comercio si considera que es un error.<br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'Recuerde que en caso de personas jurídicas disueltas<br>';
                        $_SESSION["jsonsalida"]["mensajeerror"] .= 'y en liquidación, no deben renovar.<br>';
                    }
                }
            } else {
                if ($disueltos == 1) {
                    $_SESSION["jsonsalida"]["codigoerror"] = '4000';
                    $_SESSION["jsonsalida"]["mensajeerror"] = '!!! IMPORTANTE !!!<br><br>';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'En nuestros registros aparece que la matrícula de la persona jurídica<br>';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'se encuentra disuelta, por lo tanto y de acuerdo como lo estipula la Ley,<br>';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'no tiene obligación de renovar los a&ntilde;os durante los cuales se encuentre en<br>';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'dicho estado. Sin embargo mientras el establecimiento de comercio esté<br>';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'abierto al público, por el mismo se deberá pagar la renovación.<br>';
                }
            }

            //
            if ($_SESSION["tramite"]["bloquear5anios"] == 'si') {
                if ($_SESSION["generales"]["tipousuario"] == '00' || $_SESSION["generales"]["tipousuario"] == '06') {
                    $_SESSION["jsonsalida"]["codigoerror"] = '5000';
                    $_SESSION["jsonsalida"]["mensajeerror"] = '!!! IMPORTANTE !!!<br><br>';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'No es posible continuar con la renovacion, el expediente tiene mas de 5 años sin haber renovado.<br><br>';
                } else {
                    $_SESSION["jsonsalida"]["codigoerror"] = '6000';
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Se ha encontrado que existen matrículas asociadas al trámite que tienen más de 5 años sin haber renovado, ';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= 'Si está seguro de continuar, por favor oprima el siguiente enlace<br><br>';
                    $_SESSION["jsonsalida"]["mensajeerror"] .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregRenovacionMatriculaSii1.php?accion=continuarrenovacion5anios">Continuar con la renovación</a>';
                }
            }
        }

        // ***************************************************************************************
        // Arma el json de salida
        // ***************************************************************************************
        $_SESSION["jsonsalida"]["cantidad"] = 0;
        $_SESSION["jsonsalida"]["fecha_servidor"] = date("Ymd");
        $_SESSION["jsonsalida"]["hora_servidor"] = date("His");
        $_SESSION["jsonsalida"]["tramite"] = $_SESSION["tramite"];

        $i = -1;
        foreach ($retorno["matriculas"] as $m) {
            $i++;
            $_SESSION["jsonsalida"]["matriculas"][$i]["idtipoidentificacion"] = $m["idtipoidentificacion"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["identificacion"] = $m["identificacion"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["cc"] = $m["cc"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["textocc"] = retornarRegistroMysqliApi($mysqli, "bas_camaras", "id='" . $m["cc"] . "'", "nombre");
            $_SESSION["jsonsalida"]["matriculas"][$i]["matricula"] = $m["matricula"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["nombre"] = $m["nombre"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["ape1"] = $m["ape1"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["ape2"] = $m["ape2"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["nom1"] = $m["nom1"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["nom2"] = $m["nom2"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["organizacion"] = $m["organizacion"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["txtorganizacion"] = retornarRegistroMysqliApi($mysqli, "bas_organizacionjuridica", "id='" . $m["organizacion"] . "'", "descripcion");
            $_SESSION["jsonsalida"]["matriculas"][$i]["categoria"] = $m["categoria"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["txtcategoria"] = '';
            switch ($m["categoria"]) {
                case "1": $_SESSION["jsonsalida"]["matriculas"][$i]["txtcategoria"] = 'Persona jurídica principal';
                    break;
                case "2": $_SESSION["jsonsalida"]["matriculas"][$i]["txtcategoria"] = 'Sucursal';
                    break;
                case "3": $_SESSION["jsonsalida"]["matriculas"][$i]["txtcategoria"] = 'Agencia';
                    break;
            }
            $_SESSION["jsonsalida"]["matriculas"][$i]["identificacionpropietario"] = $m["identificacionpropietario"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["identificacionrepresentantelegal"] = $m["identificacionrepresentantelegal"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["ultimoanorenovado"] = $m["ultimoanorenovado"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["ultimosactivos"] = $m["ultimosactivos"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["afiliado"] = $m["afiliado"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["ultimoanoafiliado"] = $m["ultimoanoafiliado"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["propietariojurisdiccion"] = $m["propietariojurisdiccion"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["disolucion"] = $m["disolucion"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["fechadisolucion"] = $m["fechadisolucion"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["fechanacimiento"] = isset($m["fechanacimiento"]) ? $m["fechanacimiento"] : '';
            $_SESSION["jsonsalida"]["matriculas"][$i]["fechamatricula"] = $m["fechamatricula"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["fecmatant"] = $m["fecmatant"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["fecharenovacion"] = $m["fecharenovacion"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["benart7"] = $m["benart7"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["benley1780"] = $m["benley1780"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["circular19"] = $m["circular19"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["municipio"] = $m["municipio"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["clasegenesadl"] = $m["clasegenesadl"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["claseespesadl"] = $m["claseespesadl"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["econsoli"] = $m["econsoli"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["expedienteinactivo"] = $m["expedienteinactivo"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["dircom"] = $m["dircom"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["emailcom"] = $m["emailcom"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["telcom1"] = $m["telcom1"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["telcom2"] = $m["telcom2"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["telcom3"] = $m["telcom3"];
            $_SESSION["jsonsalida"]["matriculas"][$i]["multadoponal"] = $m["multadoponal"];
        }

        // **************************************************************************** //
        // Alertas Administrativas
        // **************************************************************************** //
        $query = '(';
        if (trim((string)$_SESSION ["entrada"]["matricula"]) != '') {
            $query = "matricula='" . trim((string)$_SESSION ["entrada"]["matricula"]) . "'";
        }
        if (trim((string)$_SESSION ["entrada"]["identificacion"]) != '') {
            if ($query != '') {
                $query .= ' or ';
            }
            $query = "identificacion='" . trim((string)$_SESSION ["entrada"]["identificacion"]) . "'";
        }
        $query .= ") and idestado='VI' and eliminad<>'SI'";

        //
        $aleAdm = retornarRegistrosMysqliApi($mysqli, 'mreg_alertas', $query, "id");
        $i = 0;
        if ($aleAdm && !empty($aleAdm)) {
            $_SESSION["jsonsalida"]["alertasAdministrativas"]["codigoerror"] = '5000';
            $_SESSION["jsonsalida"]["alertasAdministrativas"]["mensajeerror"] = 'El expediente seleccionado tiene registradas alertas administrativas.';
            $_SESSION["jsonsalida"]["alertasAdministrativas"]["alertas"] = array();
            foreach ($aleAdm as $a) {
                $i++;
                $_SESSION["jsonsalida"]["alertasAdministrativas"]["alertas"][$i] = $a;
                $_SESSION["jsonsalida"]["alertasAdministrativas"]["alertas"][$i]["txttipoalerta"] = '';
                switch ($a["tipoalerta"]) {
                    case "1" : $_SESSION["jsonsalida"]["alertasAdministrativas"]["alertas"][$i]["txttipoalerta"] = 'Valor a favor del cliente';
                        break;
                    case "2" : $_SESSION["jsonsalida"]["alertasAdministrativas"]["alertas"][$i]["txttipoalerta"] = 'Informativa';
                        break;
                    case "3" : $_SESSION["jsonsalida"]["alertasAdministrativas"]["alertas"][$i]["txttipoalerta"] = 'Restrictiva';
                        break;
                    case "4" : $_SESSION["jsonsalida"]["alertasAdministrativas"]["alertas"][$i]["txttipoalerta"] = 'Valor a favor de la Cámara';
                        break;
                }
            }
        }

        // **************************************************************************** //
        // Alertas Registrales
        // **************************************************************************** //
        $query = '(';
        if (trim($_SESSION ["entrada"]["matricula"]) != '') {
            $query = "matricula='" . trim($_SESSION ["entrada"]["matricula"]) . "'";
        }
        if (trim($_SESSION ["entrada"]["identificacion"]) != '') {
            if ($query != '') {
                $query .= ' or ';
            }
            $query = "identificacion='" . trim($_SESSION ["entrada"]["identificacion"]) . "'";
        }
        $query .= ") and idestado<>'AP' and idestado<>'IN'";

        //
        $aleReg = retornarRegistrosMysqliApi($mysqli, 'mreg_alertas_registro', $query, "id");
        $i = 0;
        if ($aleReg && !empty($aleReg)) {
            $_SESSION["jsonsalida"]["alertasRegistrales"]["codigoerror"] = '5000';
            $_SESSION["jsonsalida"]["alertasRegistrales"]["mensajeerror"] = 'El expediente seleccionado tiene registradas alertas del registro.';
            $_SESSION["jsonsalida"]["alertasRegistrales"]["alertas"] = array();

            foreach ($aleReg as $a) {
                $i++;
                $_SESSION["jsonsalida"]["alertasRegistrales"]["alertas"][$i] = $a;
                $_SESSION["jsonsalida"]["alertasRegistrales"]["alertas"][$i]["txttipoalerta"] = '';
                switch ($a["tipoalerta"]) {
                    case "1" : $_SESSION["jsonsalida"]["alertasRegistrales"]["alertas"][$i]["txttipoalerta"] = 'Informativa';
                        break;
                }
            }
        }

        // **************************************************************************** //
        // Localiza el estado en texto
        // **************************************************************************** //
        $_SESSION["tramite"]["txtestado"] = retornarRegistroMysqliApi($mysqli, "mreg_liquidacionestados", "id='" . $_SESSION["tramite"]["idestado"] . "'", "descripcion");

        // **************************************************************************** //
        // Activación de controles
        // **************************************************************************** //

        $_SESSION["jsonsalida"]["controles"] = array();

        // ******************************************************************** //
        // Panel Número de empleados
        // ******************************************************************** //
        $tempPanel = array();
        $tempPanel["tipo"] = 'data';
        $tempPanel["titulopanel"] = 'Información de empleados';
        $tempPanel["inputs"] = array();

        // Campo número de empleados
        $temp = array();
        $temp["tipo"] = 'input';
        $temp["mostrar"] = 'SI';
        $temp["encabezado"] = '';
        $temp["label"] = 'Número de empleados a nivel nacional';
        $temp["id"] = 'numeroempleados';
        $temp["name"] = 'numeroempleados';
        $temp["type"] = 'text';
        $temp["size"] = '6';
        $temp["value"] = $_SESSION["tramite"]["numeroempleados"];
        $temp["placeholder"] = '';
        $tempPanel["inputs"][] = $temp;
        $_SESSION["jsonsalida"]["controles"][] = $tempPanel;

        // ******************************************************************** //
        // Panel ley 1780
        // ******************************************************************** //
        // *************************************************************************************** //
        // Lógica para beneficios Ley 1780
        // *************************************************************************************** //
        $mostrarcumple = 'no';
        $mostrarmantiene = 'no';

        if (!isset($_SESSION ["tramite"]["cumplorequisitosbenley1780"])) {
            $_SESSION ["tramite"]["cumplorequisitosbenley1780"] = '';
        }
        if (!isset($_SESSION ["tramite"]["mantengorequisitosbenley1780"])) {
            $_SESSION ["tramite"]["mantengorequisitosbenley1780"] = '';
        }
        if (!isset($_SESSION ["tramite"]["renunciobeneficiosley1780"])) {
            $_SESSION ["tramite"]["renunciobeneficiosley1780"] = '';
        }
        if ($tempBase["benley1780"] == 'S') {
            if ($tempBase["organizacion"] != '02' &&
                    ($tempBase["categoria"] == '' || $tempBase["categoria"] == '0' || $tempBase["categoria"] == '1')) {
                if ($tempBase["fechamatricula"] >= '20160502') {
                    $anoactual = date("Y");
                    $anoanterior = $anoactual - 1;
                    if (substr($tempBase["fechamatricula"], 0, 4) == $anoanterior) {
                        $mostrarcumple = 'si';
                        $mostrarmantiene = 'si';
                    }
                }
            }
        }

        $tempPanel = array();
        $tempPanel["tipo"] = 'data';
        $tempPanel["titulopanel"] = 'Beneficios Ley 1780 de 2016';

        //

        $tempPanel["avisopie"] = array();

        $tempPanel["avisopie"]["texto"] = 'Para continuar con los beneficios de la Ley 1780 y de acuerdo con lo ' .
                'indicado en el decreto 639 de 2017, deberá anexar los siguientes soportes:<br><br>' .
                '1.- Relación de trabajadores vinculados directamente con la empresa, si los tuviere, indicando el nombre e identificación de los mismos<br>' .
                '2.- Certiticar que la empresa ha realizado los aportes al Sistema de Seguridad Social Integral y demás contribuciones de nómina, en caso de estar obligada a ello, y ha cumplido con sus obligaciones oportunamente en materia tributaria<br>' .
                '3.- Presentar copia de los estados financieros debidamente firmados por el contador o revisor fiscal, según el caso, con corte al 31 de diciembre del año inmediatamente anterior<br>' .
                '4.- Certificar que la titularidad de la mitad más uno de las cuotas, acciones o participaciones en que se divide el capital de la sociedad o empresa, pertenezcan a socios con edades entre 18 y 35 años.<br>';

        $tempPanel["avisopie"]["color"] = 'warning';
        //

        $tempPanel["inputs"] = array();

        // Campo fecha de nacimiento
        $temp = array();
        $temp["tipo"] = 'input';
        if ($tempBase["organizacion"] == '01' &&
                $mostrarcumple == 'si' &&
                $mostrarmantiene == 'si') {
            $temp["mostrar"] = 'SI';
            $temp["obligatorio"] = 'SI';
        } else {
            $temp["mostrar"] = 'NO';
            $temp["obligatorio"] = 'NO';
        }
        $temp["encabezado"] = '';
        $temp["label"] = 'Fecha de nacimiento';
        $temp["id"] = 'fechanacimiento';
        $temp["name"] = 'fechanacimiento';
        $temp["type"] = 'date';
        $temp["size"] = '10';
        $temp["value"] = $_SESSION ["tramite"]["expedientes"][0]["fechanacimiento"];
        $temp["placeholder"] = '';
        $tempPanel["inputs"][] = $temp;

        // Campo cumplorequisitosbenley1780

        $temp = array();
        $temp["tipo"] = 'select';
        $selsi = '';
        $selno = '';

        if ($mostrarcumple == 'si') {
            $temp["mostrar"] = 'SI';
            $temp["obligatorio"] = 'SI';
            $selsi = 'S';
        } else {
            $temp["mostrar"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $selno = 'S';
        }
        $temp["encabezado"] = '';
        $temp["label"] = 'Cumplo con los requisitos establecidos para acceder al beneficio de la Ley 1780 de 2016';
        $temp["id"] = 'cumplorequisitosbenley1780';
        $temp["name"] = 'cumplorequisitosbenley1780';
        $temp["type"] = 'text';
        $temp["size"] = '2';
        $temp["opc"] = array();


        $temp["opc"][] = array(
            'label' => 'SI',
            'val' => 'S',
            'selected' => $selsi
        );
        $temp["opc"][] = array(
            'label' => 'NO',
            'val' => 'N',
            'selected' => $selno
        );
        $temp["value"] = $_SESSION ["tramite"]["cumplorequisitosbenley1780"];
        $temp["placeholder"] = '';
        $tempPanel["inputs"][] = $temp;


        // Campo mantengorequisitosbenley1780
        $temp = array();
        $temp["tipo"] = 'select';
        $temp["mostrar"] = 'SI';
        $selsi = '';
        $selno = '';
        if ($mostrarmantiene == 'si') {
            $temp["mostrar"] = 'SI';
            $temp["obligatorio"] = 'SI';
            $selsi = 'S';
        } else {
            $temp["mostrar"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $selno = 'S';
        }

        $temp["encabezado"] = '';
        $temp["label"] = 'Mantengo los requisitos establecidos para acceder al beneficio de la Ley 1780 de 2016';
        $temp["id"] = 'mantengorequisitosbenley1780';
        $temp["name"] = 'mantengorequisitosbenley1780';
        $temp["type"] = 'text';
        $temp["size"] = '2';
        $temp["opc"] = array();
        $temp["opc"][] = array(
            'label' => 'SI',
            'val' => 'S',
            'selected' => $selsi
        );
        $temp["opc"][] = array(
            'label' => 'NO',
            'val' => 'N',
            'selected' => $selno
        );
        $temp["value"] = $_SESSION ["tramite"]["mantengorequisitosbenley1780"];
        $temp["placeholder"] = '';
        $tempPanel["inputs"][] = $temp;

        // Campo renunciobeneficiosley1780
        $temp = array();
        $temp["tipo"] = 'select';
        if ($mostrarcumple == 'si' && $mostrarmantiene == 'si') {
            $temp["mostrar"] = 'SI';
            $temp["obligatorio"] = 'SI';
        } else {
            $temp["mostrar"] = 'NO';
            $temp["obligatorio"] = 'NO';
        }

        $temp["encabezado"] = '';
        $temp["label"] = 'Renuncio voluntariamente a los beneficios de la ley 1780 de 2016';
        $temp["id"] = 'renunciobeneficiosley1780';
        $temp["name"] = 'renunciobeneficiosley1780';
        $temp["type"] = 'text';
        $temp["size"] = '2';
        $temp["opc"] = array();
        $temp["opc"][] = array(
            'label' => 'SI',
            'val' => 'S',
            'selected' => ''
        );
        $temp["opc"][] = array(
            'label' => 'NO',
            'val' => 'N',
            'selected' => ''
        );
        $temp["value"] = $_SESSION ["tramite"]["renunciobeneficiosley1780"];
        $temp["placeholder"] = '';

        $tempPanel["inputs"][] = $temp;

        //
        $_SESSION["jsonsalida"]["controles"][] = $tempPanel;


        // ******************************************************************** //
        // Controles adicionales de liquidación
        // ******************************************************************** //
        $tempPanel = array();
        $tempPanel["tipo"] = 'data';
        $tempPanel["titulopanel"] = 'Controles adicionales a la liquidación';
        $tempPanel["inputs"] = array();

        // Campo incluirafiliacion si es usuario publico
        if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Liquidar cuota de afiliación (S o N)';
            $temp["id"] = 'incluirafiliacion';
            $temp["name"] = 'incluirafiliacion';
            $temp["type"] = 'text';
            $temp["size"] = '2';
            $temp["opc"] = array();
            $temp["opc"][] = array(
                'label' => 'SI',
                'val' => 'S',
                'selected' => 'si'
            );
            $temp["opc"][] = array(
                'label' => 'NO',
                'val' => 'N',
                'selected' => ''
            );
            $temp["value"] = 'S';
            $temp["placeholder"] = '';
            $tempPanel["inputs"][] = $temp;
        }

        // Campo incluirafiliacion si es usuario no publico
        if ($_SESSION["generales"]["codigousuario"] != 'USUPUBXX') {
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';

            $temp["label"] = 'Liquidar cuota de afiliación (S o N)';
            $temp["id"] = 'incluirafiliacion';
            $temp["name"] = 'incluirafiliacion';
            $temp["type"] = 'text';
            $temp["size"] = '2';
            $temp["opc"] = array();
            $temp["opc"][] = array(
                'label' => 'SI',
                'val' => 'S',
                'selected' => ''
            );
            $temp["opc"][] = array(
                'label' => 'NO',
                'val' => 'N',
                'selected' => ''
            );
            $temp["value"] = 'S';
            $temp["placeholder"] = '';
            $tempPanel["inputs"][] = $temp;
        }

        // Campo incluirformularios si es usuario público
        if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'NO';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Liquidar formularios (S o N)';
            $temp["id"] = 'incluirformularios';
            $temp["name"] = 'incluirformularios';
            $temp["type"] = 'text';
            $temp["size"] = '2';
            $temp["opc"] = array();
            $temp["opc"][] = array(
                'label' => 'SI',
                'val' => 'S',
                'selected' => 'si'
            );
            $temp["opc"][] = array(
                'label' => 'NO',
                'val' => 'N',
                'selected' => ''
            );
            $temp["value"] = 'S';
            $temp["placeholder"] = '';
            $tempPanel["inputs"][] = $temp;
        }

        // Campo incluirformularios si es usuario interno
        if ($_SESSION["generales"]["codigousuario"] != 'USUPUBXX') {
            $temp = array();
            $temp["tipo"] = 'select';
            $temp["mostrar"] = 'SI';
            $temp["obligatorio"] = 'SI';
            $temp["encabezado"] = '';
            $temp["label"] = 'Liquidar formularios (S o N)';
            $temp["id"] = 'incluirformularios';
            $temp["name"] = 'incluirformularios';
            $temp["type"] = 'text';
            $temp["size"] = '2';
            $temp["opc"] = array();
            $temp["opc"][] = array(
                'label' => 'SI',
                'val' => 'S',
                'selected' => ''
            );
            $temp["opc"][] = array(
                'label' => 'NO',
                'val' => 'N',
                'selected' => ''
            );
            $temp["value"] = 'S';
            $temp["placeholder"] = '';
            $tempPanel["inputs"][] = $temp;
        }

        // Campo incluirdiploma
        /*
          $temp = array();
          $temp["tipo"] = 'select';
          $temp["mostrar"] = 'SI';
          $temp["encabezado"] = '';
          $temp["label"] = 'Liquidar diploma (S o N)';
          $temp["id"] = 'incluirdiploma';
          $temp["name"] = 'incluirdiploma';
          $temp["type"] = 'text';
          $temp["size"] = '2';
          $temp["opc"] = array();
          $temp["opc"][] = array(
          'label' => 'SI',
          'val' => 'S',
          'selected' => ''
          );
          $temp["opc"][] = array(
          'label' => 'NO',
          'val' => 'N',
          'selected' => ''
          );
          $temp["value"] = 'N';
          $tempPanel["inputs"][] = $temp;
         */

        // Campo incluircartulina
        /*
          $temp = array();
          $temp["tipo"] = 'select';
          $temp["mostrar"] = 'SI';
          $temp["encabezado"] = '';
          $temp["label"] = 'Liquidar cartulina (S o N)';
          $temp["id"] = 'incluircartulina';
          $temp["name"] = 'incluircartulina';
          $temp["type"] = 'text';
          $temp["opc"] = array();
          $temp["opc"][] = array(
          'label' => 'SI',
          'val' => 'S',
          'selected' => ''
          );
          $temp["opc"][] = array(
          'label' => 'NO',
          'val' => 'N',
          'selected' => ''
          );
          $temp["size"] = '2';
          $temp["value"] = 'N';
          $temp["placeholder"] = '';
          $tempPanel["inputs"][] = $temp;
         */

        // Campo incluirfletes
        /*
          $temp = array();
          $temp["tipo"] = 'select';
          $temp["mostrar"] = 'NO';
          $temp["encabezado"] = '';
          $temp["label"] = 'Liquidar fletes (S o N)';
          $temp["id"] = 'incluirfletes';
          $temp["name"] = 'incluirfletes';
          $temp["type"] = 'text';
          $temp["size"] = '2';
          $temp["opc"] = array();
          $temp["opc"][] = array(
          'label' => 'SI',
          'val' => 'S',
          'selected' => ''
          );
          $temp["opc"][] = array(
          'label' => 'NO',
          'val' => 'N',
          'selected' => ''
          );
          $temp["value"] = 'N';
          $temp["placeholder"] = '';
          $tempPanel["inputs"][] = $temp;
         */

        // Campo incluircertificados - usuario público
        if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'NO';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Cantidad de certificados a incluir';
            $temp["id"] = 'incluircertificados';
            $temp["name"] = 'incluircertificados';
            $temp["type"] = 'number';
            $temp["size"] = '2';
            $temp["value"] = '0';
            $temp["placeholder"] = '';
            $tempPanel["inputs"][] = $temp;
        }

        // Campo incluircertificados - usuario público
        if ($_SESSION["generales"]["codigousuario"] != 'USUPUBXX') {
            $temp = array();
            $temp["tipo"] = 'input';
            $temp["mostrar"] = 'SI';
            $temp["obligatorio"] = 'NO';
            $temp["encabezado"] = '';
            $temp["label"] = 'Cantidad de certificados a incluir';
            $temp["id"] = 'incluircertificados';
            $temp["name"] = 'incluircertificados';
            $temp["type"] = 'number';
            $temp["size"] = '2';
            $temp["value"] = '1';
            $temp["placeholder"] = '';
            $tempPanel["inputs"][] = $temp;
        }


        $_SESSION["jsonsalida"]["controles"][] = $tempPanel;

        // ******************************************************************** //
        // Panel de botones
        // ******************************************************************** //
        $tempPanel = array();
        $tempPanel["tipo"] = 'boton';
        $tempPanel["titulopanel"] = '';
        $tempPanel["inputs"] = array();

        // Botón Liquidar
        $temp = array();
        $temp["tipo"] = 'botton';
        $temp["mostrar"] = 'SI';
        $temp["label"] = 'LIQUIDAR';
        $temp["color"] = 'btn-primary';
        $temp["x"] = 'TramitesController';
        $temp["y"] = 'liquidacion';
        $temp["z"] = 'liquidacion';
        $temp["href"] = '';
        $tempPanel["inputs"][] = $temp;

        // Boton Abandonar
        $temp = array();
        $temp["tipo"] = 'botton';
        $temp["mostrar"] = 'SI';
        $temp["label"] = 'ABANDONAR';
        $temp["color"] = 'btn-secondary';
        $temp["x"] = 'TramitesController';
        $temp["y"] = 'renovarCancelarMatricula';
        $temp["z"] = 'renovarMatricula';
        $temp["href"] = '';
        $tempPanel["inputs"][] = $temp;

        $_SESSION["jsonsalida"]["controles"][] = $tempPanel;

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function eliminarAnexoLiquidacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        //
        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // ********************************************************************** //
        // array de respuesta
        // ********************************************************************** //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        // ********************************************************************** //
        // Verifica que  método de recepcion de parámetros sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);

        //
        $api->validarParametro("idanexo", true);

        //
        if (!$api->validarToken('eliminarAnexoLiquidacion ', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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

        //        
        $temx = retornarRegistroMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idanexo=" . $_SESSION ["entrada"]["idanexo"] . " and eliminado='NO'");
        if ($temx === false || empty($temx)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Anexo no localizado en base de datos';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $arrCampos = array('eliminado');
        $arrValores = array("'SI'");
        regrabarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, "idanexo=" . $_SESSION["entrada"]["idanexo"]);

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function evaluarFactorAutenticacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        //
        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // ********************************************************************** //
        // array de respuesta
        // ********************************************************************** //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        // ********************************************************************** //
        // Verifica que  método de recepcion de parámetros sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);

        //
        $api->validarParametro("tipotramite", true);

        //
        if (!$api->validarToken('evaluarFactorAutenticacion ', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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

        //
        $factor = '';

        //
        if ($_SESSION["entrada"]["tipotramite"] == 'activacion') {
            if (!defined('FACTOR_AUTENTICACION_FIRMADO_ACTIVACIONES') || FACTOR_AUTENTICACION_FIRMADO_ACTIVACIONES == '') {
                $factor = 'DOBLE';
            } else {
                $factor = FACTOR_AUTENTICACION_FIRMADO_ACTIVACIONES;
            }
        }

        //
        if ($_SESSION["entrada"]["tipotramite"] == 'matriculapnat' ||
                $_SESSION["entrada"]["tipotramite"] == 'matriculapjur' ||
                $_SESSION["entrada"]["tipotramite"] == 'matriculaest' ||
                $_SESSION["entrada"]["tipotramite"] == 'matriculaesadl' ||
                $_SESSION["entrada"]["tipotramite"] == 'matriculasuc' ||
                $_SESSION["entrada"]["tipotramite"] == 'matriculaage') {
            if (!defined('FACTOR_AUTENTICACION_FIRMADO_MATRICULAS') || FACTOR_AUTENTICACION_FIRMADO_MATRICULAS == '') {
                $factor = 'DOBLE';
            } else {
                $factor = FACTOR_AUTENTICACION_FIRMADO_MATRICULAS;
            }
        }

        //
        if ($_SESSION["entrada"]["tipotramite"] == 'renovacionmatricula' ||
                $_SESSION["entrada"]["tipotramite"] == 'renovacionesadl') {
            if (!defined('FACTOR_AUTENTICACION_FIRMADO_RENOVACION') || FACTOR_AUTENTICACION_FIRMADO_RENOVACION == '') {
                $factor = 'DOBLE';
            } else {
                $factor = FACTOR_AUTENTICACION_FIRMADO_RENOVACION;
            }
        }

        //
        if ($_SESSION["entrada"]["tipotramite"] == 'mutaciondireccion' ||
                $_SESSION["entrada"]["tipotramite"] == 'mutacionactividad' ||
                $_SESSION["entrada"]["tipotramite"] == 'mutacionnombre') {
            if (!defined('FACTOR_AUTENTICACION_FIRMADO_MUTACIONES') || FACTOR_AUTENTICACION_FIRMADO_MUTACIONES == '') {
                $factor = 'DOBLE';
            } else {
                $factor = FACTOR_AUTENTICACION_FIRMADO_MUTACIONES;
            }
        }

        //
        if ($_SESSION["entrada"]["tipotramite"] == 'inscripciondocumentos' ||
                $_SESSION["entrada"]["tipotramite"] == 'inscripcionesregmer' ||
                $_SESSION["entrada"]["tipotramite"] == 'inscripcionesesadl') {
            if (!defined('FACTOR_AUTENTICACION_FIRMADO_ACTOSDOCUMENTOS') || FACTOR_AUTENTICACION_FIRMADO_ACTOSDOCUMENTOS == '') {
                $factor = 'DOBLE';
            } else {
                $factor = FACTOR_AUTENTICACION_FIRMADO_ACTOSDOCUMENTOS;
            }
        }

        //
        if ($_SESSION["entrada"]["tipotramite"] == 'inscripcionproponente' ||
                $_SESSION["entrada"]["tipotramite"] == 'actualizacionproponente' ||
                $_SESSION["entrada"]["tipotramite"] == 'renovacionproponente' ||
                $_SESSION["entrada"]["tipotramite"] == 'cancelacionproponente' ||
                $_SESSION["entrada"]["tipotramite"] == 'cambiodomicilioproponente') {
            if (!defined('FACTOR_AUTENTICACION_FIRMADO_PROPONENTES') || FACTOR_AUTENTICACION_FIRMADO_PROPONENTES == '') {
                $factor = 'DOBLE';
            } else {
                $factor = FACTOR_AUTENTICACION_FIRMADO_PROPONENTES;
            }
        }

        if ($factor == '') {
            $factor = 'DOBLE';
        }

        //
        $mysqli->close();

        $_SESSION["jsonsalida"]["factor"] = $factor;

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function recuperarAnexoLiquidacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        //
        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // ********************************************************************** //
        // array de respuesta
        // ********************************************************************** //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        //$_SESSION['jsonsalida']['base64'] = '';
        //$_SESSION['jsonsalida']['extension'] = '';
        $_SESSION['jsonsalida']['link'] = '';

        // ********************************************************************** //
        // Verifica que  método de recepcion de parámetros sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);

        $api->validarParametro("idanexo", true);

        //
        if (!$api->validarToken('recuperarAnexoLiquidacion ', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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

        //        
        $temx = retornarRegistroMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idanexo=" . $_SESSION ["entrada"]["idanexo"] . " and eliminado='NO'");
        if ($temx === false || empty($temx)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Anexo no localizado en base de datos';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $path = '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $temx["path"] . '/' . $_SESSION["entrada"]["idanexo"] . '.' . $temx["tipoarchivo"];

        $nametmp = $_SESSION ["generales"]["pathabsoluto"] . $path;
        if (!file_exists($nametmp)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Anexo no localizado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {
            $link = TIPO_HTTP . HTTP_HOST . $path;
            $_SESSION["jsonsalida"]["link"] = $link;
        }


        $mysqli->close();
        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function recuperarAnexoBalanceLiquidacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        //
        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // ********************************************************************** //
        // array de respuesta
        // ********************************************************************** //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["link"] = '';
        //$_SESSION["jsonsalida"]["base64"] = '';
        //$_SESSION["jsonsalida"]["extension"] = '';
        // ********************************************************************** //
        // Verifica que  método de recepcion de parámetros sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);
        $api->validarParametro("idliquidacion", true);

        //
        if (!$api->validarToken('recuperarAnexoBalanceLiquidacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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

        //                
        $exps = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "idliquidacion=" . $_SESSION ["entrada"]["idliquidacion"]);
        if ($exps === false || empty($exps)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // revisa si existe el archivo con el soporte del balance
        // ********************************************************************** // 

        $path = '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION ["generales"]["codigoempresa"] . '/mreg/fotoBalances/' . substr($exps ["numerorecuperacion"], 0, 3) . '/' . $exps["numerorecuperacion"] . '-Balance.pdf';

        $nametmp = $_SESSION ["generales"]["pathabsoluto"] . $path;
        if (!file_exists($nametmp)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Balance no localizado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {
            $link = TIPO_HTTP . HTTP_HOST . $path;
            $_SESSION["jsonsalida"]["link"] = $link;
        }

        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function retornarControlesUsuario(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        //
        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // ********************************************************************** //
        // array de respuesta
        // ********************************************************************** //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        // ********************************************************************** //
        // Verifica que  método de recepcion de parámetros sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);

        //
        if (!$api->validarToken('retornarControlesUsuario ', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["idusuario"] == 'USUPUBXX') {
            $_SESSION["jsonsalida"]["escajero"] = 'NO';
            $_SESSION["jsonsalida"]["eswww"] = 'SI';
            $_SESSION["jsonsalida"]["esbanco"] = 'NO';
            $_SESSION["jsonsalida"]["esbanco"] = 'NO';
            $_SESSION["jsonsalida"]["gastoadministrativo"] = 'NO';
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

        //        
        $temx = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION ["entrada"]["idusuario"] . "'");
        if ($temx === false || empty($temx)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no encontrado en la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $_SESSION["jsonsalida"]["escajero"] = $temx["escajero"];
        $_SESSION["jsonsalida"]["eswww"] = $temx["eswww"];
        $_SESSION["jsonsalida"]["esbanco"] = $temx["esbanco"];
        $_SESSION["jsonsalida"]["gastoadministrativo"] = $temx["gastoadministrativo"];


        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function retornarDatosFormularioMercantil(API $api) {
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
        $_SESSION["jsonsalida"]["formulario"] = array();

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
        $api->validarParametro("cc", false);
        $api->validarParametro("matricula", true);

        //
        if (!$api->validarToken('retornarDatosFormularioMercantil', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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

        // ********************************************************************** //
        // Recupera datos del expediente
        // ********************************************************************** //         
        $exps = retornarRegistroMysqliApi($mysqli, "mreg_liquidaciondatos", "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"] . " and expediente='" . $_SESSION["entrada"]["matricula"] . "'");
        if ($exps === false || empty($exps)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Formulario no localizado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $_SESSION["jsonsalida"]["formulario"] = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $exps["xml"]);

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    /**
     * Retorna la relación de matrículas a renovar
     * 
     * Recibe
     * - codigoempresa
     * - usuariows
     * - token
     * - idusuario
     * - tipopusuario
     * - emailcontrol
     * - identificacioncontrol
     * - celularcontrol
     * - idliquidacion
     * 
     * Retorna
     * - Retorna si las matrículas de la liquidación se ven afectadas o no por el control POT
     * 
     * @param API $api
     */
    public function retornarControlPonalPot(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        //
        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // ********************************************************************** //
        // array de respuesta
        // ********************************************************************** //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['matriculas'] = array();

        // ********************************************************************** //
        // Verifica que  método de recepcion de parámetros sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);

        $api->validarParametro("idliquidacion", true);

        //
        if (!$api->validarToken('retornarControlPonalPot ', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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

        //        
        $temx = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacionexpedientes', "idliquidacion=" . $_SESSION ["entrada"]["idliquidacion"] . " and registrobase = 'S'", "matricula");
        if ($temx === false || empty($temx)) {
            $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        } else {
            foreach ($temx as $tx) {
                $potini = 'no';
                $potfin = 'no';
                $inii = retornarRegistrosMysqliApi($mysqli, 'mreg_renovacion_control_matricula', "idliquidacion=" . $_SESSION ["entrada"]["idliquidacion"] . " and matricula='" . $tx ["matricula"] . "' and momento='I");
                $inif = retornarRegistrosMysqliApi($mysqli, 'mreg_renovacion_control_matricula', "idliquidacion=" . $_SESSION ["entrada"]["idliquidacion"] . " and matricula='" . $tx ["matricula"] . "' and momento='F");
                foreach ($inii as $ix) {
                    if ($ix["dato"] == 'ciiu1' || $ix["dato"] == 'ciiu2' || $ix["dato"] == 'ciiu3' || $ix["dato"] == 'ciiu4') {
                        if ($ix["contenido"] == 'I5630' || $ix["contenido"] == 'S9609') {
                            $potini = 'si';
                        }
                    }
                }
                foreach ($inif as $ix) {
                    if ($ix["dato"] == 'ciiu1' || $ix["dato"] == 'ciiu2' || $ix["dato"] == 'ciiu3' || $ix["dato"] == 'ciiu4') {
                        if ($ix["contenido"] == 'I5630' || $ix["contenido"] == 'S9609') {
                            $potfin = 'si';
                        }
                    }
                }
                if ($potini == 'no' && $potfin == 'no') {
                    $registro = array(
                        'matricula' => $tx["matricula"],
                        'control' => 'NO'
                    );
                    $_SESSION['jsonsalida']['matriculas'][] = $registro;
                }
                if ($potini == 'no' && $potfin == 'si') {
                    $registro = array(
                        'matricula' => $tx["matricula"],
                        'control' => 'SI'
                    );
                    $_SESSION['jsonsalida']['matriculas'][] = $registro;
                }
                if ($potini == 'si' && $potfin == 'no') {
                    $registro = array(
                        'matricula' => $tx["matricula"],
                        'control' => 'NO'
                    );
                    $_SESSION['jsonsalida']['matriculas'][] = $registro;
                }
                if ($potini == 'si' && $potfin == 'si') {
                    $registro = array(
                        'matricula' => $tx["matricula"],
                        'control' => 'NO'
                    );
                    $tini = array();
                    foreach ($inii as $ikey => $ivalor) {
                        $tini[$ikey] = $ivalor;
                    }
                    $tfin = array();
                    foreach ($inif as $ikey => $ivalor) {
                        $tfin[$ikey] = $ivalor;
                    }
                    if ($tini["nombre"] != $tfin["nombre"] ||
                            $tini["dircom"] != $tfin["dircom"] ||
                            $tini["muncom"] != $tfin["mumcom"]) {
                        $registro = array(
                            'matricula' => $tx["matricula"],
                            'control' => 'SI'
                        );
                    }
                    $_SESSION['jsonsalida']['matriculas'][] = $registro;
                }
            }
        }

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    /**
     * Retorna la lista de soportes que se deben anexar dependiendo de las condiciones de la liquidación
     * 
     * Recibe
     * - codigoempresa
     * - usuariows
     * - token
     * - idusuario
     * - tipopusuario
     * - emailcontrol
     * - identificacioncontrol
     * - celularcontrol
     * - idliquidacion
     * - tiposoporte : ponal-pot, ponal-multas, ley1780
     * 
     * Retorna
     * - Retorna la lista de las matrículas y los soportes que se requieren en cada caso
     * 
     * @param API $api
     */
    public function retornarSoportesLiquidacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        //
        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // ********************************************************************** //
        // array de respuesta
        // ********************************************************************** //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['titulo'] = '';
        $_SESSION['jsonsalida']['expedientes'] = array();

        // ********************************************************************** //
        // Verifica que  método de recepcion de parámetros sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);

        //
        $api->validarParametro("idliquidacion", true, true);
        $api->validarParametro("tiposoporte", false);

        //
        if ($_SESSION["entrada"]["tiposoporte"] != 'ponal-pot' &&
                $_SESSION["entrada"]["tiposoporte"] != 'ponal-multas' &&
                $_SESSION["entrada"]["tiposoporte"] != 'ley1780' &&
                $_SESSION["entrada"]["tiposoporte"] != 'balance') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Tipo de soporte solicitado incorrecto';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if (!$api->validarToken('retornarSoportesLiquidacion ', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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

        //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"]);
        if ($_SESSION["tramite"] === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($_SESSION["entrada"]["tiposoporte"] === 'ponal-pot') {
            if ($_SESSION["tramite"]["controlactividadaltoimpacto"] != 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No corresponde a un comerciante que requiera soportes de uso de suelos';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $_SESSION["jsonsalida"]["titulo"] = 'SOPORTES CODIGO DE POLICIA - ACTIVIDADES DE ALTO IMPACTO';
            foreach ($_SESSION["tramite"]["expedientes"] as $ex) {
                if ($ex["registrobase"] == 'S') {
                    if ($ex["controlpot"] == 'S') {
                        $expediente = array();
                        $expediente["expediente"] = $ex["matricula"];
                        $expediente["nombre"] = mb_strtoupper($ex["razonsocial"], 'utf-8');
                        $expediente["soportes"] = array();
                        $soporte = array();
                        $soporte["identificador"] = 'regmer-esadl-ponal-pot';
                        $soporte["descripcion"] = 'Certificación uso de suelos (POT autorizado)';
                        $soporte["observaciones"] = retornarRegistroMysqliApi($mysqli, 'mreg_soportesproponentes_1510', "identificador='regmer-esadl-ponal-pot'", "observaciones");
                        $soporte["idtipodoc"] = retornarRegistroMysqliApi($mysqli, 'mreg_soportesproponentes_1510', "tipotramitegenerico='soporteponalaltoimpacto' and identificador='regmer-esadl-ponal-pot'", "idtipodoc");
                        $soporte["documentos"] = array();
                        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"] . " and identificador='regmer-esadl-ponal-pot' and expediente='" . $ex["matricula"] . "' and eliminado<>'SI'", "idanexo");
                        if ($temx && !empty($temx)) {
                            foreach ($temx as $tx) {
                                $dcto = array();
                                $dcto["idanexo"] = $tx["idanexo"];
                                $dcto["observaciones"] = $tx["observaciones"];
                                $dcto["idtipodoc"] = $tx["idtipodoc"];
                                $pathAnexo = TIPO_HTTP . HTTP_HOST . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["entrada"]["codigoempresa"] . '/' . $tx["path"] . $tx["idanexo"] . '.' . $tx["tipoarchivo"];
                                $dcto["link"] = $pathAnexo;
                                $soporte["documentos"][] = $dcto;
                            }
                        }
                        $expediente["soportes"][] = $soporte;
                        $_SESSION["jsonsalida"]["expedientes"][] = $expediente;
                    }
                }
            }
        }

        if ($_SESSION["entrada"]["tiposoporte"] === 'ponal-multas') {
            if ($_SESSION["tramite"]["multadoponal"] != 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No corresponde a un comerciante que requiera soportes de pago de multas';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $_SESSION["jsonsalida"]["titulo"] = 'SOPORTES CODIGO DE POLICIA - MULTAS VENCIDAS';
            foreach ($_SESSION["tramite"]["expedientes"] as $ex) {
                if ($ex["organizacion"] == '01') {
                    if ($ex["registrobase"] == 'S') {
                        $expediente = array();
                        $expediente["expediente"] = $ex["matricula"];
                        $expediente["nombre"] = mb_strtoupper($ex["razonsocial"], 'utf-8');
                        $expediente["soportes"] = array();
                        $soporte = array();
                        $soporte["identificador"] = 'regmer-esadl-ponal-pagomultas';
                        $soporte["descripcion"] = 'Soporte del pago de multas por incumplimiento del código de policía';
                        $soporte["observaciones"] = retornarRegistroMysqliApi($mysqli, 'mreg_soportesproponentes_1510', "identificador='regmer-esadl-ponal-pagomultas'", "observaciones");
                        $soporte["idtipodoc"] = retornarRegistroMysqliApi($mysqli, 'mreg_soportesproponentes_1510', "tipotramitegenerico='soporteponalmultas' and identificador='regmer-esadl-ponal-pagomultas'", "idtipodoc");
                        $soporte["documentos"] = array();
                        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"] . " and identificador='regmer-esadl-ponal-pagomultas' and expediente='" . $ex["matricula"] . "'  and eliminado<>'SI'", "idanexo");
                        if ($temx && !empty($temx)) {
                            foreach ($temx as $tx) {
                                $dcto = array();
                                $dcto["idanexo"] = $tx["idanexo"];
                                $dcto["observaciones"] = $tx["observaciones"];
                                $dcto["idtipodoc"] = $tx["idtipodoc"];
                                $pathAnexo = TIPO_HTTP . HTTP_HOST . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["entrada"]["codigoempresa"] . '/' . $tx["path"] . $tx["idanexo"] . '.' . $tx["tipoarchivo"];
                                $dcto["link"] = $pathAnexo;
                                $soporte["documentos"][] = $dcto;
                            }
                        }
                        $expediente["soportes"][] = $soporte;
                        $_SESSION["jsonsalida"]["expedientes"][] = $expediente;
                    }
                }
            }
        }

        if ($_SESSION["entrada"]["tiposoporte"] === 'ley1780') {
            if ($_SESSION["tramite"]["cumplorequisitosbenley1780"] != 'S' ||
                    $_SESSION["tramite"]["mantengorequisitosbenley1780"] != 'S' ||
                    $_SESSION["tramite"]["renunciobeneficiosley1780"] == 'S') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'no accedera a los beneficios de Ley 1780, no requiere soportes';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $_SESSION["jsonsalida"]["titulo"] = 'SOPORTES PARA ACCEDER A LOS BENEFICIOS DE LA LEY 1780';
            foreach ($_SESSION["tramite"]["expedientes"] as $ex) {
                if ($ex["registrobase"] == 'S') {
                    if ($ex["organizacion"] != '02' && $ex["categoria"] != '2' && $ex["categoria"] != '3') {
                        $expediente = array();
                        $expediente["expediente"] = $ex["matricula"];
                        $expediente["nombre"] = mb_strtoupper($ex["razonsocial"], 'utf-8');
                        $expediente["soportes"] = array();
                        $tems = retornarRegistrosMysqliApi($mysqli, 'mreg_soportesproponentes_1510', "tipotramitegenerico='renovacionmatricula'", "orden,identificador");
                        if ($tems && !empty($tems)) {
                            foreach ($tems as $ts) {
                                $soporte = array();
                                $soporte["identificador"] = $ts["identificador"];
                                $soporte["descripcion"] = $ts["descripcion"];
                                $soporte["observaciones"] = $ts["observaciones"];
                                $soporte["idtipodoc"] = $ts["idtipodoc"];
                                $soporte["documentos"] = array();
                                $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"] . " and identificador='" . $ts["identificador"] . "'  and eliminado<>'SI'", "idanexo");


                                if ($temx && !empty($temx)) {
                                    foreach ($temx as $tx) {
                                        $dcto = array();
                                        $dcto["idanexo"] = $tx["idanexo"];
                                        $dcto["observaciones"] = $tx["observaciones"];
                                        $dcto["idtipodoc"] = $tx["idtipodoc"];
                                        $pathAnexo = TIPO_HTTP . HTTP_HOST . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["entrada"]["codigoempresa"] . '/' . $tx["path"] . $tx["idanexo"] . '.' . $tx["tipoarchivo"];
                                        $dcto["link"] = $pathAnexo;
                                        $soporte["documentos"][] = $dcto;
                                    }
                                }

                                $expediente["soportes"][] = $soporte;
                            }
                        }
                        $_SESSION["jsonsalida"]["expedientes"][] = $expediente;
                    }
                }
            }
        }

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function uploadAnexoBalanceLiquidacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        //
        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // ********************************************************************** //
        // array de respuesta
        // ********************************************************************** //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["link"] = '';

        // ********************************************************************** //
        // Verifica que  método de recepcion de parámetros sea POST
        // ********************************************************************** //
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

        //
        $api->validarParametro("idliquidacion", true, true); // obligatorio
        $api->validarParametro("base64", true);
        $api->validarParametro("extension", true);



        if (trim($_SESSION["entrada"]["base64"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se recibe el contenido del archivo';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (trim($_SESSION["entrada"]["extension"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se definió la extensión del archivo';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if (!$api->validarToken('uploadAnexoBalanceLiquidacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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

        //                
        $exps = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "idliquidacion=" . $_SESSION ["entrada"]["idliquidacion"]);
        if ((!$exps) || empty($exps)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // ********************************************************************** //
        // Revisa si existe el path donde se almacenara el soporte
        // ********************************************************************** // 
        $path = $_SESSION ["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION ["generales"]["codigoempresa"] . '/mreg';
        if (!is_dir($path)) {
            mkdir($path, 0777);
            \funcionesGenerales::crearIndex($path);
        }
        $path = $_SESSION ["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION ["generales"]["codigoempresa"] . '/mreg/fotoBalances';
        if (!is_dir($path)) {
            mkdir($path, 0777);
            \funcionesGenerales::crearIndex($path);
        }
        $path = $_SESSION ["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION ["generales"]["codigoempresa"] . '/mreg/fotoBalances/' . substr($exps ["numerorecuperacion"], 0, 3);
        if (!is_dir($path)) {
            mkdir($path, 0777);
            \funcionesGenerales::crearIndex($path);
        }

        // ********************************************************************** //
        // Almacena el soporte
        // ********************************************************************** //         

        $pathSoporteUpload = $_SESSION ["generales"]["codigoempresa"] . '/mreg/fotoBalances/' . substr($exps ["numerorecuperacion"], 0, 3) . '/' . $exps["numerorecuperacion"] . '-Balance.pdf';


        $pathAbsolutoRepositorio = PATH_ABSOLUTO_IMAGES . '/' . $pathSoporteUpload;
        $f = fopen($pathAbsolutoRepositorio, "w");
        fwrite($f, base64_decode($_SESSION["entrada"]["base64"]));
        fclose($f);


        if (!file_exists($pathAbsolutoRepositorio)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Anexo Balance (fisico) no localizado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {
            $pathRelativoRepositorio = PATH_RELATIVO_IMAGES . '/' . $pathSoporteUpload;
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Creado Anexo Balance #' . $exps["numerorecuperacion"];
            $_SESSION["jsonsalida"]["link"] = TIPO_HTTP . HTTP_HOST . '/' . $pathRelativoRepositorio;
        }


        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    /**
     * Retorna la lista de soportes que se deben anexar dependiendo de las condiciones de la liquidación
     * 
     * Recibe
     * - codigoempresa
     * - usuariows
     * - token
     * - idusuario
     * - tipopusuario
     * - emailcontrol
     * - identificacioncontrol
     * - celularcontrol
     * - idliquidacion
     * - tiposoporte : ponal-pot, ponal-multas, ley1780
     * 
     * Retorna
     * - Retorna la lista de las matrículas y los soportes que se requieren en cada caso
     * 
     * @param API $api
     */
    public function uploadSoporteLiquidacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        //
        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // ********************************************************************** //
        // array de respuesta
        // ********************************************************************** //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["link"] = '';

        // ********************************************************************** //
        // Verifica que  método de recepcion de parámetros sea POST
        // ********************************************************************** //
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

        //
        $api->validarParametro("idliquidacion", true, true); // obligatorio
        $api->validarParametro("sectransaccion", false); // para renovacion es vacio
        $api->validarParametro("expediente", false); // para renovación es obligatorio
        $api->validarParametro("identificacion", false); // Para renovación puede ser vacio
        $api->validarParametro("nombre", false); // Para renovación puede ser vacio
        $api->validarParametro("identificador", false); // obligatorio
        $api->validarParametro("descripcion", true); // obligatorio
        $api->validarParametro("fechadoc", true); // obligatorio (AAAAMMDD)
        $api->validarParametro("idtipodoc", true); // obligatorio
        $api->validarParametro("numdoc", false); // Para renovación puede ser vacio
        $api->validarParametro("origendoc", true); // obligatorio
        $api->validarParametro("base64", true);
        $api->validarParametro("extension", true);



        if (trim($_SESSION["entrada"]["base64"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se recibe el contenido del archivo';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (trim($_SESSION["entrada"]["extension"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se definió la extensión del archivo';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if (!$api->validarToken('uploadSoporteLiquidacion ', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $nametmp = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . rand(1000000, 3000000) . '.' . $_SESSION["entrada"]["extension"];
        $f = fopen($nametmp, "wb");
        fwrite($f, base64_decode($_SESSION["entrada"]["base64"]));
        fclose($f);



        // ********************************************************************** //
        // Crea la conexión con la BD
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $dirx = date("Ymd");
        $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx;
        if (!is_dir($path) || !is_readable($path)) {
            mkdir($path, 0777);
            \funcionesGenerales::crearIndex($path);
        }

        //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"]);
        if ($_SESSION["tramite"] === false || empty($_SESSION["tramite"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $secX = 0;
        $condicion = "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"] . " and identificador='" . $_SESSION["entrada"]["identificador"] . "' and sectransaccion = '" . sprintf("%03s", $_SESSION["entrada"]["sectransaccion"]) . "'";
        $arrTems = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', $condicion, "secuenciaanexo");
        if ($arrTems && empty($arrTems)) {
            $secX = 0;
        } else {
            foreach ($arrTems as $s) {
                $secX = $s["secuenciaanexo"];
            }
        }
        unset($arrTems);

        //
        $secX++;

        //
        $arrCampos = array(
            'idliquidacion',
            'sectransaccion',
            'identificador',
            'secuenciaanexo',
            'expediente',
            'tipoanexo',
            'idradicacion',
            'numerorecibo',
            'numerooperacion',
            'identificacion',
            'nombre',
            'idtipodoc',
            'numdoc',
            'fechadoc',
            'txtorigendoc',
            'path',
            'tipoarchivo',
            'observaciones',
            'bandeja',
            'eliminado'
        );

        //
        $sectra = sprintf("%03s", $_SESSION["entrada"]["sectransaccion"]);

        // Por defecto signa los datos de la matricula afectada
        $expediente = $_SESSION["entrada"]["expediente"];
        $identificacion = $_SESSION["entrada"]["identificacion"];
        $nombre = $_SESSION["entrada"]["nombre"];
        $tipodoc = $_SESSION["entrada"]["idtipodoc"];
        $numdoc = $_SESSION["entrada"]["numdoc"];
        $fechadoc = str_replace(array("/", "-"), "", $_SESSION["entrada"]["fechadoc"]);
        $origendoc = $_SESSION["entrada"]["origendoc"];

        //         
        $bandeja = '4.-REGMER';

        // Almacena el registro en la BD
        $arrValores = array(
            $_SESSION["entrada"]["idliquidacion"],
            "'" . $sectra . "'",
            "'" . $_SESSION["entrada"]["identificador"] . "'",
            $secX,
            "'" . $expediente . "'",
            "'501'",
            0,
            "''",
            "''",
            "'" . $identificacion . "'",
            "'" . $nombre . "'",
            "'" . $tipodoc . "'",
            "'" . $numdoc . "'",
            "'" . $fechadoc . "'",
            "'" . $origendoc . "'",
            "'liquidacionmreg/" . $dirx . "/'",
            "'" . $_SESSION["entrada"]["extension"] . "'",
            "'" . addslashes(strtoupper($_SESSION["entrada"]["descripcion"])) . "'",
            "'" . $bandeja . "'",
            "'NO'"
        );

        $res = insertarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, 'si');
        if ($res === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $idAnexo = $_SESSION["generales"]["lastId"];

        $pathSoporteUpload = $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idAnexo . '.' . $_SESSION["entrada"]["extension"];

        $pathAbsolutoRepositorio = PATH_ABSOLUTO_IMAGES . '/' . $pathSoporteUpload;

        copy($nametmp, $pathAbsolutoRepositorio);

        if (!file_exists($pathAbsolutoRepositorio)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Anexo (fisico) no localizado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {
            $pathRelativoRepositorio = PATH_RELATIVO_IMAGES . '/' . $pathSoporteUpload;
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Creado anexo #' . $idAnexo;
            $_SESSION["jsonsalida"]["link"] = TIPO_HTTP . HTTP_HOST . '/' . $pathRelativoRepositorio;
        }



        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    /**
     * Retorna si un formulario está o no grabado
     * 
     * Recibe
     * - codigoempresa
     * - usuariows
     * - token
     * - idusuario
     * - tipopusuario
     * - emailcontrol
     * - identificacioncontrol
     * - celularcontrol
     * - idliquidacion
     * - expediente
     * 
     * Retorna
     * - >Si o no
     * 
     * @param API $api
     */
    public function validacionFormularioGrabado(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        //
        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // ********************************************************************** //
        // array de respuesta
        // ********************************************************************** //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        // ********************************************************************** //
        // Verifica que  método de recepcion de parámetros sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);

        $api->validarParametro("idliquidacion", true);
        $api->validarParametro("expediente", true);

        //
        if (!$api->validarToken('validacionFormularioGrabado', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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

        //
        $temValid = retornarRegistroMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION ["entrada"]["idliquidacion"] . " and expediente='" . $_SESSION ["entrada"]["expediente"] . "'");
        if ($temValid === false || empty($temValid)) {
            $_SESSION["jsonsalida"]["codigoerror"] = '8000';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se detecta formulario grabado para el expediente ' . $_SESSION ["entrada"]["expediente"] . '.';
        } else {
            $datos = \funcionesGenerales::desserializarExpedienteMatricula($mysqli, $temValid["xml"]);
            if ($datos["organizacion"] == '02' || $datos["categoria"] == '2' || $datos["categoria"] == '3') {
                $_SESSION["jsonsalida"]["codigoerror"] = '0000';
            } else {
                if (floatval($datos["acttot"]) != floatval($datos["paspat"])) {
                    $_SESSION["jsonsalida"]["codigoerror"] = '9999';
                } else {
                    $_SESSION["jsonsalida"]["codigoerror"] = '0000';
                }
            }
        }

        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
