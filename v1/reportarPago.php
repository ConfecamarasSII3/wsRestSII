<?php
namespace libreriaswsRestSII;
use libreriaswsRestSII\API;
trait reportarPago {

    public function reportarPago(API $api) {

        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/gestionRecibos.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesEspeciales.php');
        set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["operador"] = '';
        $_SESSION["jsonsalida"]["identificacioncontrol"] = '';
        $_SESSION["jsonsalida"]["nombrecontrol"] = '';
        $_SESSION["jsonsalida"]["emailcontrol"] = '';
        $_SESSION["jsonsalida"]["celularcontrol"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("operador", true);
        $api->validarParametro("idliquidacion", true);
        $api->validarParametro("numerorecuperacion", false);
        $api->validarParametro("valorpagado", true);
        $api->validarParametro("fechapago", true);
        $api->validarParametro("horapago", true);
        $api->validarParametro("formapago", true);
        $api->validarParametro("codigofirmapdf", false);

        // En caso de usuario público se debe enviar la info del usuario logueado
        if ($_SESSION["entrada"]["operador"] == 'USUPUBXX') {
            $api->validarParametro("identificacioncontrol", true);
            $api->validarParametro("nombrecontrol", true);
            $api->validarParametro("emailcontrol", true);
            $api->validarParametro("celularcontrol", false);
        }

        // Valida número de autorizacion, codigo del banco y franquicia si la forma de pago es
        // diferente a efectivo, consignacion, prepago o afiliados
        if ($_SESSION["entrada"]["formapago"] != '01' && $_SESSION["entrada"]["formapago"] != '06' && $_SESSION["entrada"]["formapago"] != '91' && $_SESSION["entrada"]["formapago"] != '92') {
            $api->validarParametro("numeroautorizacion", true);
            $api->validarParametro("idbanco", true);
            $api->validarParametro("idfranquicia", false);
        }

        // En caso de pago por prepago o con cargo a afiliados se debe enviar la identificación y 
        // la clave del prepago o afiliado.
        if ($_SESSION["entrada"]["formapago"] == '91' || $_SESSION["entrada"]["formapago"] == '92') {
            $api->validarParametro("identificacionprepagoafiliado", true);
            $api->validarParametro("claveprepagoafiliado", true);
        }

        // ***************************************************************************** //
        // Completa códigos cuando sea necesario.
        // ***************************************************************************** // 
        $_SESSION["entrada"]["formapago"] = sprintf("%02s", $_SESSION["entrada"]["formapago"]);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('reportarPago', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no habilitado para consumir este método';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // *********************************************************************** //
        // Abre la conexión con la BD
        // *********************************************************************** //        
        $mysqli = conexionMysqliApi();

        // **************************************************************************** //
        // Crea el reporte del pago
        // **************************************************************************** //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"]);
        if ($_SESSION["tramite"] === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidacion no encontrada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (
                $_SESSION["tramite"]["idestado"] != '01' &&
                $_SESSION["tramite"]["idestado"] != '02' &&
                $_SESSION["tramite"]["idestado"] != '03' &&
                $_SESSION["tramite"]["idestado"] != '04' &&
                $_SESSION["tramite"]["idestado"] != '05' &&
                $_SESSION["tramite"]["idestado"] != '06' &&
                $_SESSION["tramite"]["idestado"] != '08' &&
                $_SESSION["tramite"]["idestado"] != '19' &&
                $_SESSION["tramite"]["idestado"] != '44'
        ) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidacion se encuentra en un estado no valido para ser recibida';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if ($_SESSION["entrada"]["formapago"] == '91' || $_SESSION["entrada"]["formapago"] == '92') {
            \funcionesRegistralesEspeciales::calcularTarifaEspecial2021($mysqli, $_SESSION["entrada"]["idliquidacion"]);
            $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"]);
            if ($_SESSION["tramite"] === false) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = '9999';
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidacion no encontrada';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        //
        if ($_SESSION["entrada"]["formapago"] != '91' && $_SESSION["entrada"]["formapago"] != '92') {
            if (doubleval($_SESSION["tramite"]["valortotal"]) != doubleval($_SESSION["entrada"]["valorpagado"])) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = '9999';
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El valor de la liquidación no corresponde con el valor pagado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } else {
            $_SESSION["entrada"]["valorpagado"] = $_SESSION["tramite"]["valortotal"];
        }

        //
        $tx = retornarRegistroMysqliApi($mysqli, 'mreg_formaspago', "id='" . $_SESSION["entrada"]["formapago"] . "'");
        if ($tx === false || empty($tx)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Forma de pago incorrecta';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // **************************************************************************************************** //
        // En caso de usuario USUPUBXX se valida que los campos emailcontrol, identificacioncontrol, 
        // nombrecontrol y celularcontrol
        // Si el usuario es diferente de USUPUBXX, entonces el usuario debe estar activo y ser tipo cajero
        // *************************************************************************************************** //
        if ($_SESSION["entrada"]["operador"] == 'USUPUBXX') {
            $sedeusuario = '99';
            if (!isset($_SESSION["entrada"]["emailcontrol"]) || $_SESSION["entrada"]["emailcontrol"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportó el email del usuario control o que está logueado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (!isset($_SESSION["entrada"]["nombrecontrol"]) || $_SESSION["entrada"]["nombrecontrol"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportó el nombre del usuario control o que está logueado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            /*
            if (!isset($_SESSION["entrada"]["celularcontrol"]) || $_SESSION["entrada"]["celularcontrol"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportó el celular del usuario control o que está logueado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }             * 
            */
            if (!isset($_SESSION["entrada"]["identificacioncontrol"]) || $_SESSION["entrada"]["identificacioncontrol"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se reportó la identificación del usuario control o que está logueado';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } else {
            $usux = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION["entrada"]["operador"] . "'");
            if ($usux === false || empty($usux)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no localizado en la BD del sistema';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (ltrim(trim($usux["fechainactivacion"]), "0") != '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario reportado está inactivo';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if (ltrim(trim($usux["fechaactivacion"]), "0") == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario reportado no está activo';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            if ($usux["escajero"] != 'SI') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no es tipo cajero';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            $sedeusuario = $usux["idsede"];
            $_SESSION["entrada"]["emailcontrol"] = $usux["email"];
            $_SESSION["entrada"]["nombrecontrol"] = $usux["nombreusuario"];
            $_SESSION["entrada"]["celularcontrol"] = $usux["celular"];
            $_SESSION["entrada"]["identificacioncontrol"] = $usux["identificacion"];
        }
        $_SESSION["jsonsalida"]["operador"] = $_SESSION["entrada"]["operador"];
        $_SESSION["jsonsalida"]["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
        $_SESSION["jsonsalida"]["nombrecontrol"] = $_SESSION["entrada"]["nombrecontrol"];
        $_SESSION["jsonsalida"]["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
        $_SESSION["jsonsalida"]["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];

        //
        $_SESSION["tramite"]["idestado"] = '06'; // En proceso de pago electrónico
        $_SESSION["tramite"]["idformapago"] = $_SESSION["entrada"]["formapago"];
        $_SESSION["tramite"]["proyectocaja"] = '001';
        $_SESSION["tramite"]["origen"] = 'electronico';
        $_SESSION["tramite"]["idfranquicia"] = $_SESSION["entrada"]["idfranquicia"];
        $_SESSION["tramite"]["numeroautorizacion"] = $_SESSION["entrada"]["numeroautorizacion"];
        $_SESSION["tramite"]["idcodban"] = $_SESSION["entrada"]["idbanco"];
        $_SESSION["tramite"]["enviara"] = 'C';
        $_SESSION["tramite"]["emailcontrol"] = $_SESSION["jsonsalida"]["emailcontrol"];

        //
        $okFpago = 'no';
        if ($_SESSION["entrada"]["formapago"] == '01') { // Contado
            $_SESSION["tramite"]["pagoefectivo"] = $_SESSION["entrada"]["valorpagado"];
            $okFpago = 'si';
        }
        if ($_SESSION["entrada"]["formapago"] == '02') { // Cheque
            $_SESSION["tramite"]["pagocheque"] = $_SESSION["entrada"]["valorpagado"];
            $okFpago = 'si';
        }
        if ($_SESSION["entrada"]["formapago"] == '03') { // Tarjeta debito
            $_SESSION["tramite"]["pagotdebito"] = $_SESSION["entrada"]["valorpagado"];
            $okFpago = 'si';
        }
        if ($_SESSION["entrada"]["formapago"] == '04') { // Tarjeta crédito
            $_SESSION["tramite"]["pagovisa"] = $_SESSION["entrada"]["valorpagado"];
            $okFpago = 'si';
        }
        if ($_SESSION["entrada"]["formapago"] == '05') { // pago electronico
            $_SESSION["tramite"]["pagoach"] = $_SESSION["entrada"]["valorpagado"];
            $okFpago = 'si';
        }
        if ($_SESSION["entrada"]["formapago"] == '06') { // pago consignacion
            $_SESSION["tramite"]["pagoconsignacion"] = $_SESSION["entrada"]["valorpagado"];
            $okFpago = 'si';
        }

        if (
                $_SESSION["entrada"]["formapago"] == '07' || // pago en bancos
                $_SESSION["entrada"]["formapago"] == '08' || // pago ath
                $_SESSION["entrada"]["formapago"] == '09' || // pago pse/ach
                $_SESSION["entrada"]["formapago"] == '10' || // pago efecty
                $_SESSION["entrada"]["formapago"] == '11'
        ) { // pago refernciado
            $_SESSION["tramite"]["pagoefectivo"] = $_SESSION["entrada"]["valorpagado"];
            $okFpago = 'si';
        }

        if ($_SESSION["entrada"]["formapago"] == '91') { // Con cargo a prepago
            if ($_SESSION["entrada"]["identificacionprepagoafiliado"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Debe informar la identificación de la cuenta de prepago';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $preps = retornarRegistroMysqliApi($mysqli, 'mreg_prepagos', "identificacion='" . $_SESSION["entrada"]["identificacionprepagoafiliado"] . "'");
            if ($preps === false || empty($preps)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontr+ó registro de prepago para esta identificación';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);                
            }
            if ($_SESSION["entrada"]["claveprepagoafiliado"] == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Debe reportarse la clave del prepago';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);   
            }
            $clavelimpia = \funcionesGenerales::encrypt_decrypt('decrypt', 'c0nf3c4m4r4s', 'c0nf3c4m4r4s', trim($_SESSION["entrada1"]["claveprepagoafiliado"]));
            if (
                    $preps["clave"] != md5($clavelimpia) &&
                    $preps["clave"] != sha1($clavelimpia) &&
                    !password_verify($clavelimpia, $preps["clave"]) &&
                    $preps["clave"] != md5($_SESSION["entrada"]["claveprepagoafiliado"]) &&
                    $preps["clave"] != sha1($_SESSION["entrada"]["claveprepagoafiliado"]) &&
                    !password_verify($_SESSION["entrada"]["claveprepagoafiliado"], $preps["clave"])
            ) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'La clave reportada no corresponde';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);   
            }            
            $_SESSION["tramite"]["pagoprepago"] = $_SESSION["entrada"]["valorpagado"];
            $okFpago = 'si';
        }

        if ($_SESSION["entrada"]["formapago"] == '92') { // Con cargo a cupo de afiliado
            $_SESSION["tramite"]["pagoafiliado"] = $_SESSION["entrada"]["valorpagado"];
            $_SESSION["tramite"]["cargoafiliacion"] = 'SI';
            $okFpago = 'si';
        }

        //
        if ($okFpago == 'no') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Forma de pago no permitida';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // En caso de forma de pago 92 (Afiliado) verifica que el afiliado esté vigente, la clave concuerde y
        // tenga cupo para pagar        
        if ($_SESSION["entrada"]["formapago"] == '92') {
            $matx = '';
            $cupo = 0;
            $exps = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "numid='" . $_SESSION["entrada"]["identificacionprepagoafiliado"] . "' or nit='" . $_SESSION["entrada"]["identificacionprepagoafiliado"] . "'", "matricula");
            if (!$exps || empty($exps)) {
                $exps = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "cpnumnit='" . $_SESSION["entrada"]["identificacionprepagoafiliado"] . "'", "matricula");
            }
            if ($exps && !empty($exps)) {
                foreach ($exps as $exp) {
                    if (
                            trim($exp["matricula"]) != '' &&
                            $exp["ctrestmatricula"] == 'MA' && 
                            ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1') || ($exp["organizacion"] > '02' && $exp["categoria"] == '2')) &&
                            $exp["ctrafiliacion"] == '1'
                    ) {
                        $matx = $exp["matricula"];
                    }
                }
            }
            unset($exps);
            if ($matx == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = '9999';
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se localizó ningún expediente afiliado activo que concuerde con la identificación reportada';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            //
            $formacalculocupoafiliados = retornarClaveValorMysqliApi($mysqli, '90.01.60');
            if ($formacalculocupoafiliados == 'CANTI_CERTIFICADOS') {
                $resx1 = \funcionesRegistrales::consultarSaldoAfiliadoCantidadMysqliApi($mysqli, $matx);
                if ($resx1 && !empty($resx1)) {
                    foreach ($resx1 as $rx1) {
                        $cupo = $rx1["cupo"];
                    }
                }
            } else {
                $resx1 = \funcionesRegistrales::consultarSaldoAfiliadoMysqliApi($mysqli, $matx);
                if ($resx1 && !empty($resx1)) {
                    foreach ($resx1 as $rx1) {
                        $cupo = $rx1["cupo"];
                    }
                }
            }
            unset($resx1);
            if ($cupo - $_SESSION["entrada"]["valorpagado"] < 0) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = '9999';
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No tiene cupo suficiente como afiliado para cubrir el valor del servicio. Matrícula : ' . $matx . ', cupo actual : ' . $cupo;
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            $afil = retornarRegistroMysqliApi($mysqli, 'mreg_claves_afiliados', "matricula='" . $matx . "'");
            if ($afil === false || empty($afil)) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = '9999';
                $_SESSION["jsonsalida"]["mensajeerror"] = 'El afiliado no tiene una clave asignada para uso del cupo. Matrícula : ' . $matx . ', cupo actual : ' . $cupo;
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            //
            $clavelimpia = \funcionesGenerales::encrypt_decrypt('decrypt', 'c0nf3c4m4r4s', 'c0nf3c4m4r4s', trim($_SESSION["entrada1"]["claveprepagoafiliado"]));
            if (
                    $afil["clave"] != md5($clavelimpia) &&
                    $afil["clave"] != sha1($clavelimpia) &&
                    !password_verify($clavelimpia, $afil["clave"]) &&
                    $afil["clave"] != md5($_SESSION["entrada1"]["claveprepagoafiliado"]) &&
                    $afil["clave"] != sha1($_SESSION["entrada1"]["claveprepagoafiliado"]) &&
                    !password_verify($_SESSION["entrada1"]["claveprepagoafiliado"], $afil["clave"])
            ) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = '9999';
                $_SESSION["jsonsalida"]["mensajeerror"] = 'La clave de afiliado utilizada no corresponde con la clave asignada al afiliado Matrícula : ' . $matx . ', Clave utilizada : ' . $_SESSION["entrada"]["claveprepagoafiliado"] . ' - ' . $clavelimpia;
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }


        //
        $resultado = \funcionesRegistrales::grabarLiquidacionMreg($mysqli);
        if (!$resultado) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error actualizando la liquidacion en la BD, imposible continuar con el pago : ' . $_SESSION["generales"]["mensajeerror"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }



        // ********************************************************************* //
        // Actualiza el pago en SII
        // ********************************************************************* //
        $tipogasto = '0';
        if ($_SESSION["entrada"]["formapago"] == '92') {
            $tipogasto = '1';
        }

        //
        $res = \gestionRecibos::asentarRecibos($mysqli, $_SESSION["entrada"]["idliquidacion"], $tipogasto, '07', '', '', $_SESSION["entrada"]["operador"]);

        //
        if ($res["codigoError"] != '0000') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible generar el recibo de caja en el sistema : ' . $res["msgError"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {
            $_SESSION["jsonsalida"]["idliquidacion"] = $_SESSION["entrada"]["idliquidacion"];
            $_SESSION["jsonsalida"]["numerorecuperacion"] = $_SESSION["tramite"]["numerorecuperacion"];
            $_SESSION["jsonsalida"]["numerorecibo"] = $res["numeroRecibo"];
            $_SESSION["jsonsalida"]["numerooperacion"] = $res["numeroOperacion"];
            $_SESSION["jsonsalida"]["radicacion"] = $res["codigoBarras"];

            // *********************************************************************************************** //
            // 2019-07-05 : Weymer :  Retorna arreglo de servicios pagados
            // *********************************************************************************************** // 
            $_SESSION["jsonsalida"]["servicios"] = array();
            $servbase = '';
            $res1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "numerorecibo='" . $res["numeroRecibo"] . "'", "id");
            if ($res1 && !empty($res1)) {
                foreach ($res1 as $s1) {
                    $mat = '';
                    $pro = '';
                    $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . trim($s1['servicio']) . "'");
                    if ($serv["tipoingreso"] > '20' && $serv["tipoingreso"] <= '30') {
                        $pro = $s1["matricula"];
                    } else {
                        $mat = $s1["matricula"];
                    }
                    $servicio = array();
                    if ($servbase == '') {
                        $servbase = trim($s1['servicio']);
                    }
                    $servicio['servicio'] = trim($s1['servicio']);
                    $servicio['nservicio'] = $serv["nombre"];
                    $servicio['matricula'] = $mat;
                    $servicio['proponente'] = $pro;
                    $servicio['identificacion'] = $s1["identificacion"];
                    $servicio['nombre'] = $s1["nombre"];
                    $servicio['cantidad'] = doubleval($s1['cantidad']);
                    $servicio['valorbase'] = doubleval($s1['base']);
                    $servicio['valorservicio'] = doubleval($s1['valor']);
                    $servicio['ano'] = trim($s1['anorenovacion']);
                    $servicios[] = $servicio;
                }
                $_SESSION['jsonsalida']['servicios'] = $servicios;
            }

            // *********************************************************************************************** //
            // 2019-07-05 : Weymer :  Retorna URL recibo 
            // *********************************************************************************************** // 
            if ($res["numeroRecibo"] != '') {
                $anxs = retornarRegistroMysqliApi($mysqli, 'mreg_radicacionesanexos', "numerorecibo='" . $res["numeroRecibo"] . "' and tipoanexo='509' and eliminado<>'SI'", "path");
                if ($anxs && !empty($anxs)) {
                    $_SESSION['jsonsalida']["recibo"][]['path'] = TIPO_HTTP . HTTP_HOST . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["entrada"]["codigoempresa"] . "/" . $anxs;
                }
            }


            $_SESSION["jsonsalida"]["certificados"] = array();

            // 2018-08-27: JINT: Se adiciona búsqueda de certificados relacionados.
            $certs = retornarRegistrosMysqliApi($mysqli, 'mreg_certificados_virtuales', "recibo='" . $_SESSION["jsonsalida"]["numerorecibo"] . "'", "id");
            if ($certs && !empty($certs)) {
                foreach ($certs as $cx) {
                    $cert1 = array();
                    $cert1["codigoverificacion"] = $cx["id"];
                    $cert1["tipocertificado"] = $cx["tipocertificado"];
                    $cert1["path"] = TIPO_HTTP . HTTP_HOST . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $cx["path"];
                    $_SESSION["jsonsalida"]["certificados"][] = $cert1;
                }
            }
            unset($certs);

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

}
