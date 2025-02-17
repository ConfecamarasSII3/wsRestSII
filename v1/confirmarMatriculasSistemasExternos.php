<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait confirmarMatriculasSistemasExternos
{

    public function confirmarMatriculasSistemasExternos(API $api)
    {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        //
        $tini = date("Ymd") . ' ' . date("His");

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("sistemadestino", true);
        $api->validarParametro("idenvio", true);
        $api->validarParametro("estado", true);
        $api->validarParametro("numeroasignado", false);
        $api->validarParametro("observaciones", false);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('confirmarMatriculasSistemasExternos', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        \logApi::general2('api_' . __FUNCTION__, 'Entrada:', var_export($_SESSION["entrada"], true));
        
        //
        $mysqli = conexionMysqliApi();

        //
        $reg = retornarRegistroMysqliApi($mysqli, 'mreg_envio_matriculas_api', "trim(idenvio)='" . $_SESSION["entrada"]["idenvio"] . "' and trim(sistemadestino)='" . $_SESSION["entrada"]["sistemadestino"] . "'");

        //
        if ($reg === false || empty($reg)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9998";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Id de envío no está asociado a ninguna solicitud enviada para el sistemadestino reportado';
            $mysqli->close();
            \logApi::general2('api_' . __FUNCTION__, '', var_export($_SESSION["jsonsalida"], true));
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($_SESSION["entrada"]["estado"] != 'ER') {
            if (trim($reg["estadoenvio"]) != 'PE' && trim($reg["estadoenvio"]) != 'ER') {
                $_SESSION["jsonsalida"]["codigoerror"] = "9997";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El envío asociado al id ' . $_SESSION["entrada"]["idenvio"] . ' fue previamente reportado como ' . $reg["estadoenvio"];
                $mysqli->close();
                \logApi::general2('api_' . __FUNCTION__, '', var_export($_SESSION["jsonsalida"], true));
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }
        //
        $codasig = '';
        if (isset($_SESSION["entrada"]["numeroasignado"]) && trim($_SESSION["entrada"]["numeroasignado"]) != '') {
            $codasig = $_SESSION["entrada"]["numeroasignado"];
        }

        //
        $obs = '';
        if (isset($_SESSION["entrada"]["observaciones"]) && trim($_SESSION["entrada"]["observaciones"]) != '') {
            $obs = $_SESSION["entrada"]["observaciones"];
        }

        //
        $arrCampos = array(
            'estadoenvio',
            'fechahorarespuesta',
            'codigoasignadorespuesta',
            'observaciones'
        );
        $arrValores = array(
            "'" . $_SESSION["entrada"]["estado"] . "'",
            "'" . date("Ymd") . ' ' . date("His") . "'",
            "'" . $codasig . "'",
            "'" . addslashes($obs) . "'"
        );
        $result = regrabarRegistrosMysqliApi($mysqli, 'mreg_envio_matriculas_api', $arrCampos, $arrValores, "idenvio='" . $_SESSION["entrada"]["idenvio"] . "'");

        //
        if ($result === false || empty($result)) {
            \logApi::general2('api_' . __FUNCTION__, '', 'No fue posible almacenar el numero asignado a la matricula, idenvio: ' . $_SESSION["entrada"]["idenvio"]);
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible almacenar el numero asignado a la matricula';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($_SESSION["entrada"]["estado"] == 'OK') {
            \logApi::general2('api_' . __FUNCTION__, '', 'Confirmación satisfactoria del id ' . $_SESSION["entrada"]["idenvio"]);
            $_SESSION["jsonsalida"]["mensajeerror"] = '0000';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Confirmación satisfactoria del id ' . $_SESSION["entrada"]["idenvio"];
        }

        //
        if ($_SESSION["entrada"]["estado"] == 'ER') {
            \logApi::general2('api_' . __FUNCTION__, '', 'El id ' . $_SESSION["entrada"]["idenvio"] . ' será reportado nuevamente para confirmación');
            $_SESSION["jsonsalida"]["mensajeerror"] = '0000';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El id ' . $_SESSION["entrada"]["idenvio"] . ' será reportado nuevamente para confirmación';            
            $sist = retornarRegistroMysqliApi($mysqli, 'mreg_control_sistemas_externos', "sistemadestino='" . $reg["sistemadestino"] . "' and tiporeporte='" . $reg["tiporeporte"] . "'");
            if ($sist && !empty ($sist)) {
                if ($sist["emailcontrol"] != '') {
                    $asunto = 'Error en reporte de matrículas a sistemas externos, id envio : ' . $_SESSION["entrada"]["idenvio"] . ', matricula No. ' . $reg["matricula"];
                    $mensaje = 'Se reportó error en el envío de matrículas a ' . $sist["sistemadestino"] . '<br><br>';
                    $mensaje .= 'Id de envio: ' . $_SESSION["entrada"]["idenvio"] . '<br>';
                    $mensaje .= 'Matricula: ' . $reg["matricula"] . '<br>';
                    $mensaje .= 'Observaciones: ' . $obs;                    
                    \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $sist["emailcontrol"], $asunto, $mensaje);
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
