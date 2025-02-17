<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarEstablecimientosNacionales {

    public function consultarEstablecimientosNacionales(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');
        
        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["cantidad"] = 0;
        $_SESSION['jsonsalida']['renglones'] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("tipoidentificacion", true);
        $api->validarParametro("identificacion", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarEstablecimientosNacionales', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        $mysqli = conexionMysqliApi();        
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        //
        $res = \funcionesRues::consultarEstablecimientosNacionales($mysqli, $_SESSION["entrada"]["tipoidentificacion"], $_SESSION["entrada"]["identificacion"]);
        if (!empty($res)) {
            $res1 = array ();
            foreach ($res as $rs) {
                $_SESSION["jsonsalida"]["cantidad"]++;
                $rsx = array ();
                $rsx["codigocamara"] = $rs["codigo_camara"];
                $rsx["matricula"] = $rs["matricula"];
                $rsx["razonsocial"] = $rs["razon_social"];
                $rsx["organizacion"] = $rs["codigo_organizacion_juridica"];
                $rsx["categoria"] = $rs["codigo_categoria_matricula"];
                if ($rs["codigo_estado_matricula"] == '01' || $rs["codigo_estado_matricula"] == '08         ' || $rs["codigo_estado_matricula"] == '09') {
                    $rsx["estado"] = 'MA';
                } else {
                    $rsx["estado"] = 'MC';
                }
                $rsx["fechamatricula"] = $rs["fecha_matricula"];
                $rsx["fechacancelacion"] = $rs["fecha_cancelacion"];                
                $rsx["ultanoren"] = $rs["ultimo_ano_renovado"];
                $rsx["fecharenovacion"] = $rs["fecha_renovacion"];
                $rsx["anorenant"] = $rs["ano_renovado_anterior"];
                $rsx["fecharenovacionant"] = $rs["fecha_renovacion_anterior"];                
                $rsx["activosvinculados"] = $rs["valor_est_ag_suc"];
                $rsx["muncom"] = $rs["municipio_comercial"];
                $rsx["muncomnombre"] = retornarRegistroMysqliApi($mysqli,'bas_municipios',"codigomunicipio='" . $rs["municipio_comercial"] . "'", "ciudad");
                $rsx["dircom"] = $rs["direccion_comercial"];
                $rsx["barriocom"] = $rs["barrio_comercial"];
                // $rsx["codigo_postal_comercial"] = $rs["codigo_postal_comercial"];
                $rsx["telcom1"] = $rs["telefono_comercial_1"];
                $rsx["telcom2"] = $rs["telefono_comercial_2"];
                $rsx["telcom3"] = $rs["telefono_comercial_3"];
                $rsx["emailcom"] = $rs["correo_electronico_comercial"];
                // $rsx["codigo_ubicacion_empresa"] = $rs["codigo_ubicacion_empresa"];
                $rsx["dirnot"] = $rs["direccion_fiscal"];
                $rsx["barrionot"] = $rs["barrio_fiscal"];
                // $rsx["codigo_postal_fiscal"] = $rs["codigo_postal_fiscal"];
                $rsx["munnot"] = $rs["municipio_fiscal"];
                if (ltrim((string)$rsx["munnot"],"0") != '') {
                    $rsx["munnotnombre"] = retornarRegistroMysqliApi($mysqli,'bas_municipios',"codigomunicipio='" . $rs["municipio_fiscal"] . "'", "ciudad");
                } else {
                    $rsx["munnotnombre"] = '';
                }
                $rsx["emailnot"] = $rs["correo_electronico_fiscal"];
                $rsx["empleados"] = $rs["empleados"];
                $rsx["ciiu1"] = $rs["ciiu1"];
                $rsx["ciiu2"] = $rs["ciiu2"];
                $rsx["ciiu3"] = $rs["ciiu3"];
                $rsx["ciiu4"] = $rs["ciiu4"];
                $rsx["actividad"] = $rs["desc_Act_Econ"];
                $rsx["tipoprop"] = $rs["tipo_propietario"];
                $rsx["tipolocal"] = $rs["codigo_tipo_local"];
                $rsx["afiliado"] = $rs["afiliado"];
                $rsx["historicorenovaciones"] = $rs["historicoRenovaciones"];
                $res1[] = $rsx;
            }
            $_SESSION['jsonsalida']['renglones'] = $res1;
        }
        
        //
        $mysqli->close();
        
        //
        $api->response($api->json($_SESSION["jsonsalida"]), 200);        
    }

}
