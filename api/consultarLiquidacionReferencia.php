<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarLiquidacionReferencia {

    public function consultarLiquidacionReferencia(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]['idusuario'] = '';
        $_SESSION["jsonsalida"]['identificacioncontrol'] = '';
        $_SESSION["jsonsalida"]['nombrecontrol'] = '';
        $_SESSION["jsonsalida"]['emailcontrol'] = '';
        $_SESSION["jsonsalida"]['celularcontrol'] = '';
        $_SESSION["jsonsalida"]['referencia'] = '';
        $_SESSION["jsonsalida"]['idliquidacion'] = '';
        $_SESSION["jsonsalida"]['numerorecuperacion'] = '';
        $_SESSION["jsonsalida"]['fecha'] = '';
        $_SESSION["jsonsalida"]['hora'] = '';        
        $_SESSION["jsonsalida"]['tipotramite'] = '';
        $_SESSION["jsonsalida"]['subtipotramite'] = '';
        $_SESSION["jsonsalida"]['idestado'] = '';
        $_SESSION["jsonsalida"]['matricula'] = '';
        $_SESSION["jsonsalida"]['proponente'] = '';
        $_SESSION["jsonsalida"]['tipoidentificacion'] = '';
        $_SESSION["jsonsalida"]['identificacion'] = '';
        $_SESSION["jsonsalida"]['nombre'] = '';
        $_SESSION["jsonsalida"]['email'] = '';
        $_SESSION["jsonsalida"]['celular'] = '';
        $_SESSION["jsonsalida"]['valor'] = '';
        $_SESSION["jsonsalida"]['recibo'] = '';
        $_SESSION["jsonsalida"]['fecharecibo'] = '';
        $_SESSION["jsonsalida"]['horarecibo'] = '';
        $_SESSION["jsonsalida"]['recibogob'] = '';
        $_SESSION["jsonsalida"]['fecharecibogob'] = '';
        $_SESSION["jsonsalida"]['horarecibogob'] = '';        
        $_SESSION["jsonsalida"]['radicacion'] = '';
        $_SESSION["jsonsalida"]['operacion'] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("referencia", true);


        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarLiquidacionReferencia', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();

        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($_SESSION["entrada"]["idusuario"] == 'USUPUBXX') {
            if (!isset($_SESSION["entrada"]["identificacioncontrol"]) || $_SESSION["entrada"]["identificacioncontrol"] == '' |
            !isset($_SESSION["entrada"]["nombrecontrol"]) || $_SESSION["entrada"]["nombrecontrol"] == '' ||
            !isset($_SESSION["entrada"]["emailcontrol"]) || $_SESSION["entrada"]["emailcontrol"] == '' ||
            !isset($_SESSION["entrada"]["celularcontrol"]) || $_SESSION["entrada"]["celularcontrol"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportaron los datos de control del usuario público';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $_SESSION["jsonsalida"]["idusuario"] = $_SESSION["entrada"]["idusuario"];
            $_SESSION["jsonsalida"]["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
            $_SESSION["jsonsalida"]["nombrecontrol"] = $_SESSION["entrada"]["nombrecontrol"];
            $_SESSION["jsonsalida"]["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
            $_SESSION["jsonsalida"]["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
        } else {
            $usux = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION["entrada"]["idusuario"] . "'");
            if ($usux === false || empty($usux) ||
            $usux["eliminado"] == 'SI' ||
            ltrim(trim((string)$usux["fechaactivacion"]),"0") == '' ||
            ltrim(trim((string)$usux["fechainactivacion"]),"0") != '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El usuario reportado no está habilitado o no existe en la BD';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);                
            }
            $_SESSION["jsonsalida"]["idusuario"] = $_SESSION["entrada"]["idusuario"];
            $_SESSION["jsonsalida"]["identificacioncontrol"] = $usux["identificacion"];
            $_SESSION["jsonsalida"]["nombrecontrol"] = $usux["nombreusuario"];
            $_SESSION["jsonsalida"]["emailcontrol"] = $usux["email"];
            $_SESSION["jsonsalida"]["celularcontrol"] = $usux["celular"];
        }

        //
        $numliq = substr(trim((string)$_SESSION["entrada"]["referencia"]), 2);

        if ($numliq == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó el número de referencia';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Consulta la liquidacion
        // ********************************************************************** //
        $liq = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $numliq, 'L');
        if ($liq === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidacion no encontrada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($liq["idestado"] > '05' && $liq["idestado"] != '19' && $liq["idestado"] != '44') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La liquidación no se encuentra en un estado que permita su procesamiento para pago';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if (substr($liq["fecha"], 0, 4) != date("Y")) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La liquidación pertenece a un periodo anual diferente al actual';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $fcorte = retornarRegistroMysqliApi($mysqli,'mreg_cortes_renovacion',"ano='" . date("Y") . "'");
        if (date("Ymd") > $fcorte) {
            if ($liq["fecha"] <= $fcorte) {
                if ($liq["tipotramite"] == 'renovacionmatricula' || $liq["tipotramite"] == 'renovacionesadl') {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'La liquidación fue expedida antes del ' . $fcorte . ', no debe ser recaudada por posibles cambios en el valor de la misma.';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }
        }

        //
        $_SESSION["jsonsalida"]['referencia'] = $_SESSION["entrada"]["referencia"];
        $_SESSION["jsonsalida"]['idliquidacion'] = trim((string)$liq['idliquidacion']);
        $_SESSION["jsonsalida"]['numerorecuperacion'] = trim((string)$liq['numerorecuperacion']);
        $_SESSION["jsonsalida"]['tipotramite'] = trim((string)$liq['tipotramite']);
        $_SESSION["jsonsalida"]['subtipotramite'] = trim((string)$liq['subtipotramite']);
        $_SESSION["jsonsalida"]['fecha'] = trim((string)$liq['fecha']);
        $_SESSION["jsonsalida"]['hora'] = trim((string)$liq['hora']);
        $_SESSION["jsonsalida"]['idestado'] = trim((string)$liq['idestado']);
        $_SESSION["jsonsalida"]['matricula'] = trim((string)$liq['idmatriculabase']);
        $_SESSION["jsonsalida"]['proponente'] = trim((string)$liq['idproponentebase']);
        if (trim($_SESSION["jsonsalida"]['matricula']) == '') {
            $_SESSION["jsonsalida"]['matricula'] = trim((string)$liq['idexpedientebase']);
        }
        $_SESSION["jsonsalida"]['valor'] = trim((string)$liq['valortotal']);
        $_SESSION["jsonsalida"]['recibo'] = trim((string)$liq['numerorecibo']);
        $_SESSION["jsonsalida"]['fecharecibo'] = trim((string)$liq['fecharecibo']);
        $_SESSION["jsonsalida"]['horarecibo'] = trim((string)$liq['horarecibo']);
        
        $_SESSION["jsonsalida"]['recibogob'] = trim((string)$liq['numerorecibogob']);
        $_SESSION["jsonsalida"]['fecharecibogob'] = trim((string)$liq['fecharecibogob']);
        $_SESSION["jsonsalida"]['horarecibogob'] = trim((string)$liq['horarecibogob']);
        
        $_SESSION["jsonsalida"]['radicacion'] = trim((string)$liq['numeroradicacion']);
        $_SESSION["jsonsalida"]['operacion'] = trim((string)$liq['numerooperacion']);
        
        $_SESSION["jsonsalida"]['tipoidentificacion'] = trim((string)$liq['idtipoidentificacioncliente']);
        $_SESSION["jsonsalida"]['identificacion'] = trim((string)$liq['identificacioncliente']);
        $_SESSION["jsonsalida"]['nombre'] = trim((string)$liq['nombre1cliente'] . ' ' . $liq['nombre2cliente'] . ' ' . $liq['apellido1cliente'] . ' ' . $liq['apellido2cliente']);
        if ($_SESSION["jsonsalida"]['nombre'] == '') {
            $_SESSION["jsonsalida"]['nombre'] = trim((string)$liq['nombrecliente']) . ' ' . trim((string)$liq['apellidocliente']);            
        }
        $_SESSION["jsonsalida"]['email'] = trim((string)$liq['emailcliente']);
        $_SESSION["jsonsalida"]['celular'] = trim((string)$liq['movil']);


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
