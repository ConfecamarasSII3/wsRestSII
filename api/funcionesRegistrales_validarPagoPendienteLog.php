<?php

class funcionesRegistrales_validarPagoPendienteLog {

    public static function validarPagoPendienteLog($mysqli = null, $numliq = '', $logName = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesPagoElectronico.php');

        $nameLog = $logName;
        if ($nameLog == '') {
            $nameLog = 'validarPagosPendientes_' . date("Ymd");
        }

        //
        $arrSal = array();
        $arrSal["idliquidacion"] = $numliq;
        $arrSal["idestado"] = '06';
        $arrSal["numeroautorizacion"] = '';
        $arrSal["mensaje"] = '';
        $arrSal["link"] = '';

        //
        $liq = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $numliq);

        //
        if ($liq["gateway"] == 'TUCOMPRA') {            
        }

        //
        if ($liq["gateway"] == 'PAYU') {            
        }

        //
        if ($liq["gateway"] == 'EPAYCO') {            
        }

        //
        if ($liq["gateway"] == 'P2P') {
            $referencia = sprintf("%02s", $_SESSION["generales"]["codigoempresa"]) . sprintf("%08s", $numliq);
            $numeroautorizacion = '';
            $estado = '';
            $arrResult = \funcionesPagoElectronico::consultarPlaceToPay($referencia, $liq["ticketid"]);
            if (isset($arrResult["status"]["status"])) {
                $estado = $arrResult["status"]["status"];
            }
            if ($estado == 'REJECTED') {
                if (isset($arrResult["payment"])) {
                    foreach ($arrResult["payment"] as $key => $value) {
                        $estado = $value["status"]["status"];
                        $numeroautorizacion = $value["authorization"];
                    }
                }
            }

            if ($estado == '') {
                $msg = "No es posible consultar el estado de la transacción con P2P";
                \logApi::general2($nameLog, $referencia, $msg);
                $arrSal["idestado"] = '06';
                $arrSal["numeroautorizacion"] = '';
                $arrSal["mensaje"] = 'No fue posible consultar el estado e la transacción  en P2P';
            }

            if ($estado == 'FAILED' || $estado == 'REJECTED') {
                $arrSal["idestado"] = '88';
                $arrSal["numeroautorizacion"] = $numeroautorizacion;
            }

            //
            if ($estado == 'NOT_AUTHORIZED') {
                $arrSal["idestado"] = '88';
                $arrSal["numeroautorizacion"] = $numeroautorizacion;
            }

            //
            if ($estado == 'PENDING') {
                $arrSal["idestado"] = '06';
                $arrSal["numeroautorizacion"] = $numeroautorizacion;
            }

            //
            if ($estado == 'APPROVED' || $estado == 'OK') {
                $arrSal["idestado"] = '07';
                $arrSal["numeroautorizacion"] = $numeroautorizacion;
                $ok = \funcionesPagoElectronico::asentarPlaceToPay($mysqli, $numliq, $arrResult);
                if ($ok) {
                    $arrSal["link"] = TIPO_HTTP . HTTP_HOST . '/retornoPlacetoPay.php?accion=validar&referencia=' . $referencia;
                }
            }
        }

        //
        if ($liq["gateway"] == 'AVISORREST') {
            $referencia = sprintf("%02s", $_SESSION["generales"]["codigoempresa"]) . sprintf("%08s", $numliq);
            $numeroautorizacion = '';
            $estado = '';
            $arrResult = \funcionesPagoElectronico::consultarAvisorRest($referencia, $liq["ticketid"]);
            if ($arrResult === false || empty($arrResult)) {
                $arrSal["idestado"] = '06';
                $arrSal["mensaje"] = 'No fue posible validar el estado de la transacción en Avisor';
            } else {
                $tranState = '';
                $transValue = 0;
                $trazabilityCode = '';
                \logApi::general2($logName, $referencia, 'Respuesta de zona virtual' . " : " . var_export($arrResult, true));
                if ($arrResult["ReturnCode"] == 'SUCCESS') {
                    $estado = $arrResult["TranState"];
                    $trazabilityCode = $arrResult["TrazabilityCode"];
                }
                if ($estado == 'CREATED') {
                    $arrSal["idestado"] = '06';
                    $arrSal["numeroautorizacion"] = $trazabilityCode;
                    $arrSal["mensaje"] = 'Transaccion pendiente en Avisor';
                }
                if ($estado == 'NOT_AUTHORIZED') {
                    $arrSal["idestado"] = '08';
                    $arrSal["numeroautorizacion"] = $trazabilityCode;
                    $arrSal["mensaje"] = 'No autorizada o rechazada';
                }
                if ($estado == 'PENDING' || $estado == 'BANK' || $estado == 'CREATED' || $estado == 'CAPTURED') {
                    $arrSal["idestado"] = '06';
                    $arrSal["numeroautorizacion"] = $trazabilityCode;
                    $arrSal["mensaje"] = 'Transaccion pendiente en Avisor';
                }
                if ($estado == 'EXPIRED') {
                    $arrSal["idestado"] = '88';
                    $arrSal["numeroautorizacion"] = $trazabilityCode;
                    $arrSal["mensaje"] = 'Tansaccion expirada';
                }
                if ($estado == 'OK') {
                    $arrSal["idestado"] = '07';
                    $arrSal["numeroautorizacion"] = $trazabilityCode;
                    $arrSal["mensaje"] = 'Tansaccion aprobada';
                    $ok = \funcionesPagoElectronico::asentarAvisorRest($mysqli, $numliq, $arrResult);
                    if ($ok) {
                        $arrSal["link"] = TIPO_HTTP . HTTP_HOST . '/retornoAvisorRest.php?accion=validar&_referencia=' . $referencia;
                    }
                }
            }
        }

        //
        if ($liq["gateway"] == 'GOU') {
            $referencia = sprintf("%02s", $_SESSION["generales"]["codigoempresa"]) . sprintf("%08s", $numliq);
            $numeroautorizacion = '';
            $estado = '';
            $arrResult = \funcionesPagoElectronico::consultarGou($referencia, $liq["ticketid"]);
            if (isset($arrResult["status"]["status"])) {
                $estado = $arrResult["status"]["status"];
            }
            if ($estado == 'REJECTED') {
                if (isset($arrResult["payment"])) {
                    foreach ($arrResult["payment"] as $key => $value) {
                        $estado = $value["status"]["status"];
                        $numeroautorizacion = $value["authorization"];
                    }
                }
            }

            if ($estado == '') {
                $msg = "No es posible consultar el estado de la transacción con GOU";
                \logApi::general2($nameLog, $referencia, $msg);
                $arrSal["idestado"] = '06';
                $arrSal["numeroautorizacion"] = '';
                $arrSal["mensaje"] = 'No fue posible consultar el estado e la transacción  en Gou';
            }

            if ($estado == 'FAILED' || $estado == 'REJECTED') {
                $arrSal["idestado"] = '88';
                $arrSal["numeroautorizacion"] = $numeroautorizacion;
            }

            //
            if ($estado == 'NOT_AUTHORIZED') {
                $arrSal["idestado"] = '88';
                $arrSal["numeroautorizacion"] = $numeroautorizacion;
            }

            //
            if ($estado == 'PENDING') {
                $arrSal["idestado"] = '06';
                $arrSal["numeroautorizacion"] = $numeroautorizacion;
            }

            //
            if ($estado == 'APPROVED' || $estado == 'OK') {
                $arrSal["idestado"] = '07';
                $arrSal["numeroautorizacion"] = $numeroautorizacion;
                $ok = \funcionesPagoElectronico::asentarGou($mysqli, $numliq, $arrResult);
                if ($ok) {
                    $arrSal["link"] = TIPO_HTTP . HTTP_HOST . '/retornoGou.php?accion=validar&referencia=' . $referencia;
                }
            }
        }

        //
        if ($liq["gateway"] == 'ZV') {
            $referencia = sprintf("%02s", $_SESSION["generales"]["codigoempresa"]) . sprintf("%08s", $numliq);
            $numeroautorizacion = '';
            $estadoPago = '';
            $pagoTerminado = '';
            $arrZonaVirtual = \funcionesPagoElectronico::consultarZonaVirtual($referencia);
            if ($arrZonaVirtual === false || empty($arrZonaVirtual)) {
                $arrSal["idestado"] = '06';
                $arrSal["mensaje"] = 'No fue posible validar el estado de la transacción en zona virtual';
            } else {
                \logApi::general2($logName, $referencia, 'Respuesta de zona virtual' . " : " . var_export($arrZonaVirtual, true));
                $status = isset($arrZonaVirtual["int_estado"]) ? trim($arrZonaVirtual["int_estado"]) : '';
                if ($status == 1) {
                    $cantidadPagos = isset($arrZonaVirtual["int_cantidad_pagos"]) ? trim($arrZonaVirtual["int_cantidad_pagos"]) : '';
                    $cadenaPago = isset($arrZonaVirtual["str_res_pago"]) ? trim($arrZonaVirtual["str_res_pago"]) : '';
                    if ($cantidadPagos >= 1) {
                        if (trim($cadenaPago) != "") {
                            $arrDatosPago = explode("|", $cadenaPago);
                            $pagoTerminado = isset($arrDatosPago[3]) ? trim($arrDatosPago[3]) : "";
                            $estadoPago = isset($arrDatosPago[4]) ? trim($arrDatosPago[4]) : "";
                            $numeroautorizacion = isset($arrDatosPago[1]) ? trim($arrDatosPago[1]) : "";
                            $medioPago = isset($arrDatosPago[20]) ? trim($arrDatosPago[20]) : "";
                        }
                    } else {
                        $arrSal["idestado"] = '06';
                        $arrSal["mensaje"] = 'Transacción no encontdada en el gateway de zona virtual';
                        $msg = 'No se encuentra la transacción en Zona Virtual.';
                        \logApi::general2($logName, $referencia, $msg);
                    }

                    if ($numeroautorizacion != '') {
                        $arrCampos = array(
                            'gateway',
                            'ticketid',
                        );
                        $arrValores = array(
                            "'ZV'",
                            "'" . trim($arrDatosPago[0]) . "'",
                        );
                        $arrReg = regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', $arrCampos, $arrValores, "idliquidacion='" . $numliq . "'");
                        if ($arrReg === false || empty($arrReg)) {
                            $arrSal["idestado"] = '06';
                            $arrSal["mensaje"] = 'La transacción no pudo ser actualizada luego de consultar en zona virtual';
                            $msg = 'La transacción no pudo ser actualizada luego de consultar en zona virtual';
                            \logApi::general2($logName, $referencia, $msg);
                        }
                    }
                } else {
                    $arrSal["idestado"] = '06';
                    $arrSal["mensaje"] = 'La respuesta de zona virtual no es satisfactoria, status diferente a 1';
                    $msg = 'La respuesta de zona virtual no es satisfactoria, status diferente a 1';
                    \logApi::general2($logName, $referencia, $msg);
                }
            }

            //
            if ($estadoPago == '') {
                $arrSal["idestado"] = '06';
                $arrSal["mensaje"] = 'No es posible consultar el estado de la transacción con ZonaVirtual';
                $msg = "No es posible consultar el estado de la transacción con ZonaVirtual";
                \logApi::general2($logName, $referencia, $msg);
            } else {
                \logApi::general2($logName, $referencia, "Transacción en estado : " . \funcionesGenerales::describeEstadoZonaVirtual($estadoPago) . " en gateway ZonaVirtual.");
            }


            //La transacción fue terminada
            if ($pagoTerminado != 1) {
                $arrSal["idestado"] = '06';
                $arrSal["mensaje"] = 'El pago no ha sido terminado en zona virtual';
                $msg = "El pago no ha sido terminado en zona virtual";
                \logApi::general2($logName, $referencia, $msg);
            } else {
                if ($estadoPago == 1) { // Aprobado
                    $arrSal["idestado"] = '07';
                    $arrSal["numeroautorizacion"] = $numeroautorizacion;
                    $ok = \funcionesPagoElectronico::asentarZonaVirtual($mysqli, $numliq, $arrZonaVirtual);
                    if ($ok) {
                        $arrSal["link"] = TIPO_HTTP . HTTP_HOST . '/retornoZonaVirtual.php?referencia=' . $referencia;
                    }
                } else {
                    switch ($estadoPago) {
                        case 200: //$msj = "PAGO INICIADO";
                        case 888: //$msj = "PAGO PENDIENTE POR INICIAR";
                        case 999: //$msj = "PAGO PENDIENTE POR FINALIZAR";
                        case 4001: //$msj = "PENDIENTE POR CR";
                            $arrSal["idestado"] = '06';
                            $arrSal["numeroautorizacion"] = $numeroautorizacion;
                            break;

                        case 4000: //$msj = "RECHAZADO CR";
                        case 1000: //$msj = "PAGO RECHAZADO";
                        case 1002: //$msj = "PAGO RECHAZADO";
                            $arrSal["idestado"] = '88';
                            $arrSal["numeroautorizacion"] = $numeroautorizacion;
                            break;
                        case 4003: //$msj = "ERROR CR";
                        case 777: //$msj = "PAGO DECLINADO";
                        case 1001: //$msj = "ERROR ENTRE ACH Y EL BANCO (RECHAZADA)";
                            $arrSal["idestado"] = '88';
                            $arrSal["numeroautorizacion"] = $numeroautorizacion;
                            break;

                        default:
                            $arrSal["idestado"] = '06';
                            $arrSal["numeroautorizacion"] = $numeroautorizacion;
                            break;
                    }
                }
            }
        }



        //
        return $arrSal;
    }

}
?>
