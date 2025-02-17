<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait calcularTamanoEmpresarial {

    public function calcularTamanoEmpresarial(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['matricula'] = '';
        $_SESSION['jsonsalida']['tamano_codigo'] = '';
        $_SESSION['jsonsalida']['tamano_texto'] = '';
        $_SESSION['jsonsalida']['tamano_certificados'] = '';
        $_SESSION['jsonsalida']['forma_calculo'] = '';
        $_SESSION['jsonsalida']['fecha_datos'] = '';
        $_SESSION['jsonsalida']['ano_datos'] = '';
        $_SESSION['jsonsalida']['ciiu'] = '';
        $_SESSION['jsonsalida']['ingresos'] = '';
        $_SESSION['jsonsalida']['activos'] = '';
        $_SESSION['jsonsalida']['personal'] = '';
        $_SESSION['jsonsalida']['ingresos_uvt'] = '';
        $_SESSION['jsonsalida']['ingresos_uvb'] = '';
        $_SESSION['jsonsalida']['valor_uvt'] = '';
        $_SESSION['jsonsalida']['valor_uvb'] = '';
        $_SESSION['jsonsalida']['sector_economico'] = '';
        $_SESSION['jsonsalida']['sector_economico_textual'] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            exit ();
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("matricula", false);
        $api->validarParametro("origen", true);
        if ($_SESSION["entrada"]["origen"] == 'alcortereportado') {
            $api->validarParametro("fechacorte", true);
        } else {
            $api->validarParametro("fechacorte", false);
        }
        $api->validarParametro("activos", false);
        $api->validarParametro("personal", false);
        $api->validarParametro("ciiu", false);
        $api->validarParametro("ingresos", false);
        $api->validarParametro("ambiente", false);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('calcularTamanoEmpresarial', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($_SESSION["entrada"]["origen"] != 'actual' && $_SESSION["entrada"]["origen"] != 'alcortereportado' && $_SESSION["entrada"]["origen"] != 'simular') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error en el parámetro origen';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            exit ();
        }

        //
        if (!isset($_SESSION["entrada"]["matricula"])) {
            $_SESSION["entrada"]["matricula"] = '';
        }

        //
        if ($_SESSION["entrada"]["origen"] == 'actual' || $_SESSION["entrada"]["origen"] == 'alcortereportado') {
            if ($_SESSION["entrada"]["matricula"] == '') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Para el origen indicado es indispensable el número de matrícula';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
                exit ();
            }
        }
        
        if ($_SESSION["entrada"]["origen"] == 'alcortereportado') {
            if ($_SESSION["entrada"]["fechacorte"] == '') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Debe indicar la fecha de corte';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
                exit ();
            }
        }

        //
        if ($_SESSION["entrada"]["fechacorte"] != '') {
            if (\funcionesGenerales::validarFecha($_SESSION["entrada"]["fechacorte"]) === false) {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Fecha de corte reportada en forma erronea';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
                exit ();
            }
        }

        if ($_SESSION["entrada"]["origen"] == 'simular') {
            if ($_SESSION["entrada"]["ciiu"] == '') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El campo ciiu debe traer un contenido';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
                exit ();
            }
            if ($_SESSION["entrada"]["ingresos"] == '') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El campo ingresos debe tener un contenido';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
                exit ();
            }
        }
        
        //
        $mysqli = false;
        if (!isset($_SESSION["entrada"]["ambiente"]) || $_SESSION["entrada"]["ambiente"] == '' || $_SESSION["entrada"]["ambiente"] == 'A') {
            $mysqli = conexionMysqliApi();
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'D') {
            $mysqli = conexionMysqliApi('D-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'P') {
            $mysqli = conexionMysqliApi('P-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9904";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
                
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            exit ();
        }

        //
        if ($_SESSION["entrada"]["fechacorte"] == '') {
            $_SESSION["entrada"]["fechacorte"] = date ("Ymd");
        }
        
        //
        $tamano = \funcionesGenerales::calcularTamanoEmpresarial($mysqli, $_SESSION["entrada"]["matricula"], $_SESSION["entrada"]["origen"], $_SESSION["entrada"]["fechacorte"], $_SESSION["entrada"]["activos"], $_SESSION["entrada"]["personal"], $_SESSION["entrada"]["ciiu"], $_SESSION["entrada"]["ingresos"]);

        //
        $mysqli->close();

        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = $tamano["codigoerror"];
        $_SESSION["jsonsalida"]["mensajeerror"] = $tamano["mensajeerror"];
        $_SESSION['jsonsalida']['matricula'] = $_SESSION["entrada"]["matricula"];
        $_SESSION['jsonsalida']['tamano_codigo'] = $tamano["codigo"];
        $_SESSION['jsonsalida']['tamano_textoresumido'] = $tamano["textoresumido"];
        $_SESSION['jsonsalida']['tamano_textocompleto'] = $tamano["textocompleto"];
        $_SESSION['jsonsalida']['forma_calculo'] = $tamano["forma"];
        $_SESSION['jsonsalida']['fecha_datos'] = $tamano["fechadatos"];
        $_SESSION['jsonsalida']['ano_datos'] = $tamano["anodatos"];
        $_SESSION['jsonsalida']['ciiu'] = $tamano["ciiu"];
        $_SESSION['jsonsalida']['ingresos'] = $tamano["ingresos"];
        $_SESSION['jsonsalida']['activos'] = $tamano["activos"];
        $_SESSION['jsonsalida']['personal'] = $tamano["personal"];
        $_SESSION['jsonsalida']['ingresos_uvt'] = $tamano["ingresosuvt"];
        $_SESSION['jsonsalida']['ingresos_uvb'] = $tamano["ingresosuvb"];
        $_SESSION['jsonsalida']['ingresos_uvt_2'] = \funcionesGenerales::mostrarNumero2simple($tamano["ingresosuvt"]);
        $_SESSION['jsonsalida']['ingresos_uvb_2'] = \funcionesGenerales::mostrarNumero2simple($tamano["ingresosuvb"]);        
        $_SESSION['jsonsalida']['valor_uvt'] = $tamano["uvt"];
        $_SESSION['jsonsalida']['valor_uvb'] = $tamano["uvb"];
        $_SESSION['jsonsalida']['sector_economico'] = $tamano["sector"];
        switch ($tamano["sector"]) {
            case "C" : $_SESSION['jsonsalida']['sector_economico_textual'] = 'Comercio'; break;
            case "S" : $_SESSION['jsonsalida']['sector_economico_textual'] = 'Servicios'; break;
            case "M" : $_SESSION['jsonsalida']['sector_economico_textual'] = 'Manufactura'; break;
            
        }
        $_SESSION['jsonsalida']['encontro'] = $tamano["encontro"];
        $_SESSION['jsonsalida']['fechacorte'] = $tamano["fechacorte"];

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
