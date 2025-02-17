<?php

namespace api;

use api\API;

trait mantenimientoServicios {

    public function mantenimientoServiciosModificarCuentas(API $api) {
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');
        require_once ('presentacion.class.php');

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';
        $_SESSION ["jsonsalida"] ["html"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("servicio", true);
        $api->validarParametro("cajero", true);
        $api->validarParametro("grupo", true);
        $api->validarParametro("session_parameters", false);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }
        $_SESSION["entrada"]["servicio"] = sprintf("%08s", $_SESSION["entrada"]["servicio"]);
        $mysqli = conexionMysqliApi();
        if ($_SESSION["entrada"]["grupo"] == 'fppres') {
            $temx = retornarRegistrosMysqliApi($mysqli, 'jsp7_maestro_presupuesto', "1=1", "codigocuenta");
            $arreglo = array();
            foreach ($temx as $tx) {
                $arreglo[$tx["codigocuenta"]] = $tx["codigocuenta"] . ' - ' . $tx["nombre"];
            }
            unset($temx);
        } else {
            $temx = retornarRegistrosMysqliApi($mysqli, 'jsp7_maestro_cuentas', "1=1", "codigocuenta");
            $arreglo = array();
            foreach ($temx as $tx) {
                $arreglo[$tx["codigocuenta"]] = $tx["codigocuenta"] . ' - ' . $tx["nombre"];
            }
            unset($temx);
        }
        $temx = retornarRegistroMysqliApi($mysqli, 'mreg_ingresos_servicios_niif', "idservicio='" . $_SESSION["entrada"]["servicio"] . "' and idoperador='" . $_SESSION["entrada"]["cajero"] . "'");
        if ($temx === false || empty($temx)) {
            $ctadeb = '';
            $ctacre = '';
        } else {
            $tgrupo = '';
            switch ($_SESSION["entrada"]["grupo"]) {
                case "fpcontado":
                    $ctadeb = $temx["fpcontadodb"];
                    $ctacre = $temx["fpcontadocr"];
                    break;
                case "fpcredito":
                    $ctadeb = $temx["fpcreditodb"];
                    $ctacre = $temx["fpcreditocr"];
                    break;
                case "fpcheque":
                    $ctadeb = $temx["fpchequedb"];
                    $ctacre = $temx["fpchequecr"];
                    break;
                case "fptdebito":
                    $ctadeb = $temx["fptdebitodb"];
                    $ctacre = $temx["fptdebitocr"];
                    break;
                case "fptcredito":
                    $ctadeb = $temx["fptcreditodb"];
                    $ctacre = $temx["fptcreditocr"];
                    break;
                case "fpconsignacion":
                    $ctadeb = $temx["fpconsignaciondb"];
                    $ctacre = $temx["fpconsignacioncr"];
                    break; 
                case "fpqr":
                    $ctadeb = $temx["fpqrdb"];
                    $ctacre = $temx["fpqrcr"];
                    break;                
                case "fpdevolucion":
                    $ctadeb = $temx["fpdevoluciondb"];
                    $ctacre = $temx["fpdevolucioncr"];
                    break;
                case "fprues":
                    $ctadeb = $temx["fpruedb"];
                    $ctacre = $temx["fpruecr"];
                    break;
                case "fpprepago":
                    $ctadeb = $temx["fpprepagodb"];
                    $ctacre = $temx["fpprepagocr"];
                    break;

                case "fppres":
                    $ctadeb = $temx["ctapresdeb"];
                    $ctacre = $temx["ctaprescre"];
                    break;
            }
        }
        $mysqli->close();

        $tgrupo = '';
        switch ($_SESSION["entrada"]["grupo"]) {
            case "fpcontado":
                $tgrupo = 'Contado';
                break;
            case "fpcredito":
                $tgrupo = 'Venta a crédito';
                break;
            case "fpcheque":
                $tgrupo = 'En cheque';
                break;
            case "fptdebito":
                $tgrupo = 'Tarjeta débito';
                break;
            case "fptcredito":
                $tgrupo = 'Tarjeta crédito';
                break;
            case "fpconsignacion":
                $tgrupo = 'Consignación';
                break;
            case "fpqr":
                $tgrupo = 'Pagos con QR';
                break;
            case "fpdevolucion":
                $tgrupo = 'Devoluciones';
                break;
            case "fprues":
                $tgrupo = 'Rues';
                break;
            case "fpprepago":
                $tgrupo = 'Prepago';
                break;
            case "fppres":
                $tgrupo = 'Presupuesto';
                break;
        }

        //
        $pres = new \presentacionBootstrap ();
        $string = $pres->abrirPanel();
        $string .= $pres->armarLineaTextoInformativa('<strong>Servicio:</strong> ' . $_SESSION["entrada"]["servicio"] . ', <strong>Cajero:</strong> ' . $_SESSION["entrada"]["cajero"] . ', <strong>Grupo:</strong> ' . $tgrupo, 'center');
        $string .= $pres->armarCampoTextoOculto('xservicio', $_SESSION["entrada"]["servicio"]);
        $string .= $pres->armarCampoTextoOculto('xcajero', $_SESSION["entrada"]["cajero"]);
        $string .= $pres->armarCampoTextoOculto('xgrupo', $_SESSION["entrada"]["grupo"]);
        $string .= '<br>';
        $string .= $pres->armarCampoSelect('Débito', 'no', 'xcuentadeb', $ctadeb, $arreglo);
        $string .= $pres->armarCampoSelect('Cédito', 'no', 'xcuentacre', $ctacre, $arreglo);
        $string .= '<br>';
        $arrBtnTipo = array();
        $arrBtnImagen = array();
        $arrBtnEnlace = array();
        $arrBtnTipo [] = 'javascript';
        $arrBtnImagen [] = 'Grabar';
        $arrBtnEnlace [] = 'asociarCuentas();';
        $string .= $pres->armarBotonesDinamicos($arrBtnTipo, $arrBtnImagen, $arrBtnEnlace);
        $string .= $pres->cerrarPanel();
        unset($pres);

        //
        // $mysqli->close();
        //
        $_SESSION ["jsonsalida"] ["html"] = base64_encode($string);

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        // \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function mantenimientoServiciosModificarCuentasGrabar(API $api) {
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');
        require_once ('presentacion.class.php');

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';
        $_SESSION ["jsonsalida"] ["html"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("servicio", false);
        $api->validarParametro("cajero", false);
        $api->validarParametro("grupo", false);
        $api->validarParametro("cuentadeb", false);
        $api->validarParametro("cuentacre", false);
        $api->validarParametro("session_parameters", false);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }
        $_SESSION["entrada"]["servicio"] = sprintf("%08s", $_SESSION["entrada"]["servicio"]);
        $_SESSION["entrada"]["cajero"] = str_replace("|", "", $_SESSION["entrada"]["cajero"]);
        $_SESSION["entrada"]["grupo"] = str_replace("|", "", $_SESSION["entrada"]["grupo"]);
        $_SESSION["entrada"]["cuentadeb"] = str_replace("|", "", $_SESSION["entrada"]["cuentadeb"]);
        $_SESSION["entrada"]["cuentacre"] = str_replace("|", "", $_SESSION["entrada"]["cuentacre"]);
        $mysqli = conexionMysqliApi();
        $temx = retornarRegistroMysqliApi($mysqli, 'mreg_ingresos_servicios_niif', "idservicio='" . $_SESSION["entrada"]["servicio"] . "' and idoperador='" . $_SESSION["entrada"]["cajero"] . "'");
        if ($temx === false || empty($temx)) {
            $nuevo = 'si';
            $temx = array(
                'idservicio' => $_SESSION["entrada"]["servicio"],
                'idoperador' => $_SESSION["entrada"]["cajero"],
                'fpcontadodb' => '',
                'fpcontadocr' => '',
                'fpcreditodb' => '',
                'fpcreditocr' => '',
                'fpchequedb' => '',
                'fpchequecr' => '',
                'fptdebitodb' => '',
                'fptdebitocr' => '',
                'fptcreditodb' => '',
                'fptcreditocr' => '',
                'fpconsignaciondb' => '',
                'fpconsignacioncr' => '', 
                'fpqrdb' => '',
                'fpqrcr' => '',                
                'fpdevoluciondb' => '',
                'fpdevolucioncr' => '',
                'fpruedb' => '',
                'fpruecr' => '',
                'fpprepagodb' => '',
                'fpprepagocr' => '',
                'ccos' => '',
                'programa' => '',
                'anexo' => '',
                'proyecto' => '',
                'ctapres' => '',
                'ctapresdeb' => '',
                'ctaprescre' => ''
            );
        } else {
            $nuevo = 'no';
        }
        switch ($_SESSION["entrada"]["grupo"]) {
            case "fpcontado":
                $temx["fpcontadodb"] = $_SESSION["entrada"]["cuentadeb"];
                $temx["fpcontadocr"] = $_SESSION["entrada"]["cuentacre"];
                break;
            case "fpcredito":
                $temx["fpcreditodb"] = $_SESSION["entrada"]["cuentadeb"];
                $temx["fpcreditocr"] = $_SESSION["entrada"]["cuentacre"];
                break;
            case "fpcheque":
                $temx["fpchequedb"] = $_SESSION["entrada"]["cuentadeb"];
                $temx["fpchequecr"] = $_SESSION["entrada"]["cuentacre"];
                break;
            case "fptdebito":
                $temx["fptdebitodb"] = $_SESSION["entrada"]["cuentadeb"];
                $temx["fptdebitocr"] = $_SESSION["entrada"]["cuentacre"];
                break;
            case "fptcredito":
                $temx["fptcreditodb"] = $_SESSION["entrada"]["cuentadeb"];
                $temx["fptcreditocr"] = $_SESSION["entrada"]["cuentacre"];
                break;
            case "fpconsignacion":
                $temx["fpconsignaciondb"] = $_SESSION["entrada"]["cuentadeb"];
                $temx["fpconsignacioncr"] = $_SESSION["entrada"]["cuentacre"];
                break;
            case "fpqr":
                $temx["fpqrdb"] = $_SESSION["entrada"]["cuentadeb"];
                $temx["fpqrcr"] = $_SESSION["entrada"]["cuentacre"];
                break;
            case "fpdevolucion":
                $temx["fpdevoluciondb"] = $_SESSION["entrada"]["cuentadeb"];
                $temx["fpdevolucioncr"] = $_SESSION["entrada"]["cuentacre"];
                break;
            case "fprues":
                $temx["fpruedb"] = $_SESSION["entrada"]["cuentadeb"];
                $temx["fpruecr"] = $_SESSION["entrada"]["cuentacre"];
                break;
            case "fpprepago":
                $temx["fpprepagodb"] = $_SESSION["entrada"]["cuentadeb"];
                $temx["fpprepagocr"] = $_SESSION["entrada"]["cuentacre"];
                break;

            case "fppres":
                $temx["ctapresdeb"] = $_SESSION["entrada"]["cuentadeb"];
                $temx["ctaprescre"] = $_SESSION["entrada"]["cuentacre"];
                break;
        }
        $arrCampos = array(
            'idservicio',
            'idoperador',
            'fpcontadodb',
            'fpcontadocr',
            'fpcreditodb',
            'fpcreditocr',
            'fpchequedb',
            'fpchequecr',
            'fptdebitodb',
            'fptdebitocr',
            'fptcreditodb',
            'fptcreditocr',
            'fpconsignaciondb',
            'fpconsignacioncr',            
            'fpqrdb',
            'fpqrcr',
            'fpdevoluciondb',
            'fpdevolucioncr',
            'fpruedb',
            'fpruecr',
            'fpprepagodb',
            'fpprepagocr',
            'ccos',
            'programa',
            'anexo',
            'proyecto',
            'ctapres',
            'ctapresdeb',
            'ctaprescre'
        );
        $arrValores = array(
            "'" . $temx["idservicio"] . "'",
            "'" . $temx["idoperador"] . "'",
            "'" . (string)$temx["fpcontadodb"] . "'",
            "'" . (string)$temx["fpcontadocr"] . "'",
            "'" . (string)$temx["fpcreditodb"] . "'",
            "'" . (string)$temx["fpcreditocr"] . "'",
            "'" . (string)$temx["fpchequedb"] . "'",
            "'" . (string)$temx["fpchequecr"] . "'",
            "'" . (string)$temx["fptdebitodb"] . "'",
            "'" . (string)$temx["fptdebitocr"] . "'",
            "'" . (string)$temx["fptcreditodb"] . "'",
            "'" . (string)$temx["fptcreditocr"] . "'",
            "'" . (string)$temx["fpconsignaciondb"] . "'",
            "'" . (string)$temx["fpconsignacioncr"] . "'",            
            "'" . (string)$temx["fpqrdb"] . "'",
            "'" . (string)$temx["fpqrcr"] . "'",
            "'" . (string)$temx["fpdevoluciondb"] . "'",
            "'" . (string)$temx["fpdevolucioncr"] . "'",
            "'" . (string)$temx["fpruedb"] . "'",
            "'" . (string)$temx["fpruecr"] . "'",
            "'" . (string)$temx["fpprepagodb"] . "'",
            "'" . (string)$temx["fpprepagocr"] . "'",
            "'" . (string)$temx["ccos"] . "'",
            "'" . (string)$temx["programa"] . "'",
            "'" . (string)$temx["anexo"] . "'",
            "'" . (string)$temx["proyecto"] . "'",
            "'" . (string)$temx["ctapres"] . "'",
            "'" . (string)$temx["ctapresdeb"] . "'",
            "'" . (string)$temx["ctaprescre"] . "'"
        );

        if ($temx["fpcontadodb"] != '' ||
                $temx["fpcontadocr"] != '' ||
                $temx["fpcreditodb"] != '' ||
                $temx["fpcreditocr"] != '' ||
                $temx["fpchequedb"] != '' ||
                $temx["fpchequecr"] != '' ||
                $temx["fptdebitodb"] != '' ||
                $temx["fptdebitocr"] != '' ||
                $temx["fptcreditodb"] != '' ||
                $temx["fptcreditocr"] != '' ||
                $temx["fpconsignaciondb"] != '' ||
                $temx["fpconsignacioncr"] != '' ||
                $temx["fpqrdb"] != '' ||
                $temx["fpqrcr"] != '' ||
                $temx["fpdevoluciondb"] != '' ||
                $temx["fpdevolucioncr"] != '' ||
                $temx["fpruedb"] != '' ||
                $temx["fpruecr"] != '' ||
                $temx["fpprepagodb"] != '' ||
                $temx["fpprepagocr"] != '' ||
                $temx["ccos"] != '' ||
                $temx["programa"] != '' ||
                $temx["anexo"] != '' ||
                $temx["proyecto"] != '' ||
                $temx["ctapres"] != '' ||
                $temx["ctapresdeb"] != '' ||
                $temx["ctaprescre"] != ''
        ) {
            borrarRegistrosMysqliApi($mysqli, 'mreg_ingresos_servicios_niif', "idservicio='" . $temx["idservicio"] . "' and idoperador='" . $temx["idoperador"] . "'");
            insertarRegistrosMysqliApi($mysqli, 'mreg_ingresos_servicios_niif', $arrCampos, $arrValores);
        } else {
            borrarRegistrosMysqliApi($mysqli, 'mreg_ingresos_servicios_niif', "id=" . "idservicio='" . $temx["idservicio"] . "' and idoperador='" . $temx["idoperador"] . "'");
        }
        $mysqli->close();
        $_SESSION ["jsonsalida"] ["html"] = '';

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        // \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function mantenimientoServiciosModificarOtros(API $api) {
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');
        require_once ('presentacion.class.php');

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';
        $_SESSION ["jsonsalida"] ["html"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("servicio", true);
        $api->validarParametro("cajero", true);
        $api->validarParametro("session_parameters", false);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }
        $_SESSION["entrada"]["servicio"] = sprintf("%08s", $_SESSION["entrada"]["servicio"]);
        $mysqli = conexionMysqliApi();
        $temx = retornarRegistroMysqliApi($mysqli, 'mreg_ingresos_servicios_niif', "idservicio='" . $_SESSION["entrada"]["servicio"] . "' and idoperador='" . $_SESSION["entrada"]["cajero"] . "'");
        if ($temx === false || empty($temx)) {
            $ccos = '';
            $anexo = '';
            $programa = '';
            $proyecto = '';
        } else {
            $ccos = $temx["ccos"];
            $anexo = $temx["anexo"];
            $programa = $temx["programa"];
            $proyecto = $temx["proyecto"];
        }
        $temx = retornarRegistrosMysqliApi($mysqli, 'jsp7_ccos', "1=1", "idccos");
        $arregloCcos = array();
        foreach ($temx as $tx) {
            $arregloCcos[$tx["idccos"]] = $tx["idccos"] . ' - ' . $tx["descripcion"];
        }

        $temx = retornarRegistrosMysqliApi($mysqli, 'anexostributarios', "1=1", "id");
        $arregloAnexos = array();
        foreach ($temx as $tx) {
            $arregloAnexos[$tx["id"]] = $tx["id"] . ' - ' . $tx["nombre"];
        }

        $arregloProgramas = array();
        $arregloProyectos = array();

        $mysqli->close();

        //
        $pres = new \presentacionBootstrap ();
        $string = $pres->abrirPanel();
        $string .= $pres->armarLineaTextoInformativa('<strong>Servicio:</strong> ' . $_SESSION["entrada"]["servicio"] . ', <strong>Cajero:</strong> ' . $_SESSION["entrada"]["cajero"], 'center');
        $string .= $pres->armarCampoTextoOculto('xservicio', $_SESSION["entrada"]["servicio"]);
        $string .= $pres->armarCampoTextoOculto('xcajero', $_SESSION["entrada"]["cajero"]);
        $string .= '<br>';
        $string .= $pres->armarCampoSelect('Centro Costos', 'no', 'xccos', $ccos, $arregloCcos);
        $string .= $pres->armarCampoSelect('Anexo', 'no', 'xanexo', $anexo, $arregloAnexos);
        $string .= $pres->armarCampoTexto('Programa', 'no', 'xprograma', $programa);
        $string .= $pres->armarCampoTexto('Proyecto', 'no', 'xproyecto', $proyecto);
        $string .= '<br>';
        $arrBtnTipo = array();
        $arrBtnImagen = array();
        $arrBtnEnlace = array();
        $arrBtnTipo [] = 'javascript';
        $arrBtnImagen [] = 'Grabar';
        $arrBtnEnlace [] = 'asociarOtros();';
        $string .= $pres->armarBotonesDinamicos($arrBtnTipo, $arrBtnImagen, $arrBtnEnlace);
        $string .= $pres->cerrarPanel();
        unset($pres);

        //
        // $mysqli->close();
        //
        $_SESSION ["jsonsalida"] ["html"] = base64_encode($string);

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        // \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function mantenimientoServiciosModificarOtrosGrabar(API $api) {
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');
        require_once ('presentacion.class.php');

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';
        $_SESSION ["jsonsalida"] ["html"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("servicio", false);
        $api->validarParametro("cajero", false);
        $api->validarParametro("ccos", false);
        $api->validarParametro("programa", false);
        $api->validarParametro("anexo", false);
        $api->validarParametro("proyecto", false);
        $api->validarParametro("session_parameters", false);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }
        $_SESSION["entrada"]["servicio"] = sprintf("%08s", $_SESSION["entrada"]["servicio"]);
        $_SESSION["entrada"]["cajero"] = str_replace("|", "", $_SESSION["entrada"]["cajero"]);
        $_SESSION["entrada"]["ccos"] = str_replace("|", "", $_SESSION["entrada"]["ccos"]);
        $_SESSION["entrada"]["programa"] = str_replace("|", "", $_SESSION["entrada"]["programa"]);
        $_SESSION["entrada"]["anexo"] = str_replace("|", "", $_SESSION["entrada"]["anexo"]);
        $_SESSION["entrada"]["proyecto"] = str_replace("|", "", $_SESSION["entrada"]["proyecto"]);

        $mysqli = conexionMysqliApi();
        $temx = retornarRegistroMysqliApi($mysqli, 'mreg_ingresos_servicios_niif', "idservicio='" . $_SESSION["entrada"]["servicio"] . "' and idoperador='" . $_SESSION["entrada"]["cajero"] . "'",'*','P');
        if ($temx === false || empty($temx)) {
            $nuevo = 'si';
            $temx = array(
                'idservicio' => $_SESSION["entrada"]["servicio"],
                'idoperador' => (string) $_SESSION["entrada"]["cajero"],
                'fpcontadodb' => '',
                'fpcontadocr' => '',
                'fpcreditodb' => '',
                'fpcreditocr' => '',
                'fpchequedb' => '',
                'fpchequecr' => '',
                'fptdebitodb' => '',
                'fptdebitocr' => '',
                'fptcreditodb' => '',
                'fptcreditocr' => '',
                'fpconsignaciondb' => '',
                'fpconsignacioncr' => '',
                'fpqrdb' => '',
                'fpqrcr' => '',
                'fpdevoluciondb' => '',
                'fpdevolucioncr' => '',
                'fpruedb' => '',
                'fpruecr' => '',
                'fpprepagodb' => '',
                'fpprepagocr' => '',
                'ccos' => '',
                'programa' => '',
                'anexo' => '',
                'proyecto' => '',
                'ctapres' => '',
                'ctapresdeb' => '',
                'ctaprescre' => ''
            );
        } else {
            $nuevo = 'no';
        }
        
        //
        $temx["ccos"] = $_SESSION["entrada"]["ccos"];
        $temx["programa"] = $_SESSION["entrada"]["programa"];
        $temx["anexo"] = $_SESSION["entrada"]["anexo"];
        $temx["proyecto"] = $_SESSION["entrada"]["proyecto"];

        $arrCampos = array(
            'idservicio',
            'idoperador',
            'fpcontadodb',
            'fpcontadocr',
            'fpcreditodb',
            'fpcreditocr',
            'fpchequedb',
            'fpchequecr',
            'fptdebitodb',
            'fptdebitocr',
            'fptcreditodb',
            'fptcreditocr',
            'fpconsignaciondb',
            'fpconsignacioncr',
            'fpqrdb',
            'fpqrcr',
            'fpdevoluciondb',
            'fpdevolucioncr',
            'fpruedb',
            'fpruecr',
            'fpprepagodb',
            'fpprepagocr',
            'ccos',
            'programa',
            'anexo',
            'proyecto',
            'ctapres',
            'ctapresdeb',
            'ctaprescre'
        );
        $arrValores = array(
            "'" . $temx["idservicio"] . "'",
            "'" . $temx["idoperador"] . "'",
            "'" . (string)$temx["fpcontadodb"] . "'",
            "'" . (string)$temx["fpcontadocr"] . "'",
            "'" . (string)$temx["fpcreditodb"] . "'",
            "'" . (string)$temx["fpcreditocr"] . "'",
            "'" . (string)$temx["fpchequedb"] . "'",
            "'" . (string)$temx["fpchequecr"] . "'",
            "'" . (string)$temx["fptdebitodb"] . "'",
            "'" . (string)$temx["fptdebitocr"] . "'",
            "'" . (string)$temx["fptcreditodb"] . "'",
            "'" . (string)$temx["fptcreditocr"] . "'",
            "'" . (string)$temx["fpconsignaciondb"] . "'",
            "'" . (string)$temx["fpconsignacioncr"] . "'",
            "'" . (string)$temx["fpqrdb"] . "'",
            "'" . (string)$temx["fpqrcr"] . "'",
            "'" . (string)$temx["fpdevoluciondb"] . "'",
            "'" . (string)$temx["fpdevolucioncr"] . "'",
            "'" . (string)$temx["fpruedb"] . "'",
            "'" . (string)$temx["fpruecr"] . "'",
            "'" . (string)$temx["fpprepagodb"] . "'",
            "'" . (string)$temx["fpprepagocr"] . "'",
            "'" . (string)$temx["ccos"] . "'",
            "'" . (string)$temx["programa"] . "'",
            "'" . (string)$temx["anexo"] . "'",
            "'" . (string)$temx["proyecto"] . "'",
            "'" . (string)$temx["ctapres"] . "'",
            "'" . (string)$temx["ctapresdeb"] . "'",
            "'" . (string)$temx["ctaprescre"] . "'"
        );

        if ($temx["fpcontadodb"] != '' ||
                $temx["fpcontadocr"] != '' ||
                $temx["fpcreditodb"] != '' ||
                $temx["fpcreditocr"] != '' ||
                $temx["fpchequedb"] != '' ||
                $temx["fpchequecr"] != '' ||
                $temx["fptdebitodb"] != '' ||
                $temx["fptdebitocr"] != '' ||
                $temx["fptcreditodb"] != '' ||
                $temx["fptcreditocr"] != '' ||
                $temx["fpconsignaciondb"] != '' ||
                $temx["fpconsignacioncr"] != '' ||
                $temx["fpqrdb"] != '' ||
                $temx["fpqrcr"] != '' ||
                $temx["fpdevoluciondb"] != '' ||
                $temx["fpdevolucioncr"] != '' ||
                $temx["fpruedb"] != '' ||
                $temx["fpruecr"] != '' ||
                $temx["fpprepagodb"] != '' ||
                $temx["fpprepagocr"] != '' ||
                $temx["ccos"] != '' ||
                $temx["programa"] != '' ||
                $temx["anexo"] != '' ||
                $temx["proyecto"] != '' ||
                $temx["ctapres"] != '' ||
                $temx["ctapresdeb"] != '' ||
                $temx["ctaprescre"] != ''
        ) {
            borrarRegistrosMysqliApi($mysqli, 'mreg_ingresos_servicios_niif', "idservicio='" . $temx["idservicio"] . "' and idoperador='" . $temx["idoperador"] . "'");
            insertarRegistrosMysqliApi($mysqli, 'mreg_ingresos_servicios_niif', $arrCampos, $arrValores);
        } else {
            borrarRegistrosMysqliApi($mysqli, 'mreg_ingresos_servicios_niif', "idservicio='" . $temx["idservicio"] . "' and idoperador='" . $temx["idoperador"] . "'");
        }
        $mysqli->close();
        $_SESSION ["jsonsalida"] ["html"] = '';

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        // \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function mantenimientoServiciosDuplicarParametrizacionNiif(API $api) {
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');
        require_once ('presentacion.class.php');

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';
        $_SESSION ["jsonsalida"] ["html"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("servicio", true);
        $api->validarParametro("session_parameters", false);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }
        $_SESSION["entrada"]["servicio"] = sprintf("%08s", $_SESSION["entrada"]["servicio"]);
        $mysqli = conexionMysqliApi();
        $arreglo = array();
        $nserv = '';
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_servicios', "1=1", "idservicio");
        foreach ($temx as $tx) {
            $arreglo[$tx["idservicio"]] = $tx["idservicio"] . ' - ' . $tx["nombre"];
            if ($tx["idservicio"] == $_SESSION["entrada"]["servicio"]) {
                $nserv = $tx["nombre"];
            }
        }
        $mysqli->close();

        //
        $pres = new \presentacionBootstrap ();
        $string = $pres->abrirPanel();
        $string .= $pres->armarLineaTextoInformativa('<strong>Servicio:</strong> ' . $_SESSION["entrada"]["servicio"] . ' - ' . $nserv, 'center');
        $string .= $pres->armarCampoTextoOculto('xservicio', $_SESSION["entrada"]["servicio"]);
        $string .= '<br>';
        $string .= $pres->armarCampoSelect('Duplicar desde', 'no', 'xservicioorigen', '', $arreglo);
        $string .= '<br>';
        $arrBtnTipo = array();
        $arrBtnImagen = array();
        $arrBtnEnlace = array();
        $arrBtnTipo [] = 'javascript';
        $arrBtnImagen [] = 'Duplicar';
        $arrBtnEnlace [] = 'duplicarParametrosNiifContinuar();';
        $string .= $pres->armarBotonesDinamicos($arrBtnTipo, $arrBtnImagen, $arrBtnEnlace);
        $string .= $pres->cerrarPanel();
        unset($pres);

        //
        // $mysqli->close();
        //
        $_SESSION ["jsonsalida"] ["html"] = base64_encode($string);

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        // \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function mantenimientoServiciosDuplicarParametrizacionNiifContinuar(API $api) {
        require_once ('mysqli.php');
        require_once ('funcionesGenerales.php');
        require_once ('presentacion.class.php');

        // array de respuesta
        $_SESSION ["jsonsalida"] = array();
        $_SESSION ["jsonsalida"] ["codigoerror"] = '0000';
        $_SESSION ["jsonsalida"] ["mensajeerror"] = '';
        $_SESSION ["jsonsalida"] ["html"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION ["jsonsalida"] ["codigoerror"] = "9999";
            $_SESSION ["jsonsalida"] ["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION ["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("servicio", false);
        $api->validarParametro("servicioorigen", false);
        $api->validarParametro("session_parameters", false);

        // ********************************************************************** //
        // Valida session_parameters
        // ********************************************************************** //
        if (isset($_SESSION ["entrada"] ["session_parameters"]) && $_SESSION ["entrada"] ["session_parameters"] != '') {
            \funcionesGenerales::desarmarVariablesPantalla($_SESSION ["entrada"] ["session_parameters"]);
        }
        $_SESSION["entrada"]["servicio"] = sprintf("%08s", $_SESSION["entrada"]["servicio"]);
        $_SESSION["entrada"]["servicioorigen"] = sprintf("%08s", $_SESSION["entrada"]["servicioorigen"]);

        $mysqli = conexionMysqliApi();
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_ingresos_servicios_niif', "idservicio='" . $_SESSION["entrada"]["servicioorigen"] . "'", "id");
        if ($temx && !empty($temx)) {
            borrarRegistrosMysqliApi($mysqli, 'mreg_ingresos_servicios_niif', "idservicio='" . $_SESSION["entrada"]["servicio"] . "'");
            foreach ($temx as $tx) {
                $arrCampos = array(
                    'idservicio',
                    'idoperador',
                    'fpcontadodb',
                    'fpcontadocr',
                    'fpcreditodb',
                    'fpcreditocr',
                    'fpchequedb',
                    'fpchequecr',
                    'fptdebitodb',
                    'fptdebitocr',
                    'fptcreditodb',
                    'fptcreditocr',
                    'fpconsignaciondb',
                    'fpconsignacioncr',
                    'fpqrdb',
                    'fpqrcr',
                    'fpdevoluciondb',
                    'fpdevolucioncr',
                    'fpruedb',
                    'fpruecr',
                    'fpprepagodb',
                    'fpprepagocr',
                    'ccos',
                    'programa',
                    'anexo',
                    'proyecto',
                    'ctapres',
                    'ctapresdeb',
                    'ctaprescre'
                );
                $arrValores = array(
                    "'" . $_SESSION["entrada"]["servicio"] . "'",
                    "'" . $tx["idoperador"] . "'",
                    "'" . $tx["fpcontadodb"] . "'",
                    "'" . $tx["fpcontadocr"] . "'",
                    "'" . $tx["fpcreditodb"] . "'",
                    "'" . $tx["fpcreditocr"] . "'",
                    "'" . $tx["fpchequedb"] . "'",
                    "'" . $tx["fpchequecr"] . "'",
                    "'" . $tx["fptdebitodb"] . "'",
                    "'" . $tx["fptdebitocr"] . "'",
                    "'" . $tx["fptcreditodb"] . "'",
                    "'" . $tx["fptcreditocr"] . "'",
                    "'" . $tx["fpconsignaciondb"] . "'",
                    "'" . $tx["fpconsignacioncr"] . "'",
                    "'" . $tx["fpqrdb"] . "'",
                    "'" . $tx["fpqrcr"] . "'",
                    "'" . $tx["fpdevoluciondb"] . "'",
                    "'" . $tx["fpdevolucioncr"] . "'",
                    "'" . $tx["fpruedb"] . "'",
                    "'" . $tx["fpruecr"] . "'",
                    "'" . $tx["fpprepagodb"] . "'",
                    "'" . $tx["fpprepagocr"] . "'",
                    "'" . $tx["ccos"] . "'",
                    "'" . $tx["programa"] . "'",
                    "'" . $tx["anexo"] . "'",
                    "'" . $tx["proyecto"] . "'",
                    "'" . $tx["ctapres"] . "'",
                    "'" . $tx["ctapresdeb"] . "'",
                    "'" . $tx["ctaprescre"] . "'"
                );
                insertarRegistrosMysqliApi($mysqli, 'mreg_ingresos_servicios_niif', $arrCampos, $arrValores);
            }
        }
        $mysqli->close();
        $_SESSION ["jsonsalida"] ["html"] = '';

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        // \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION ["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
