<?php

class funcionesGestionRecibos {

    /**
     * 
     * @param type $mysqli
     * @param type $liq
     * @param type $dat
     * @param type $mom
     * @return type
     */
    public static function validarLiquidacionRecibo($mysqli, $idliquidacion, $nameLog = '') {
        $retornar = array(
            'generar' => 'si',
            'recibo' => '',
            'operacion' => '',
            'fecha' => '',
            'hora' => '',
            'estado' => '',
            'codigobarras' => '',
            'recibogob' => '',
            'operaciongob' => '',
            'fechagob' => '',
            'horagob' => '',
            'estadogob' => '',
        );

        $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', "idliquidacion=" . $idliquidacion);
        if ($arrX && !empty($arrX)) {
            $retornar["generar"] = 'no';
            foreach ($arrX as $ax) {
                if ($ax["tiporecibo"] == '' || $ax["tiporecibo"] == 'S') {
                    $retornar["recibo"] = $arrX["recibo"];
                    $retornar["operacion"] = $arrX["operacion"];
                    $retornar["fecha"] = $arrX["fecha"];
                    $retornar["hora"] = $arrX["hora"];
                    $retornar["estado"] = $arrX["estado"];
                    $retornar["codigobarras"] = $arrX["codigobarras"];
                }
                if ($ax["tiporecibo"] == 'G') {
                    $retornar["recibogob"] = $arrX["recibo"];
                    $retornar["operaciongob"] = $arrX["operacion"];
                    $retornar["fechagob"] = $arrX["fecha"];
                    $retornar["horagob"] = $arrX["hora"];
                    $retornar["estadogob"] = $arrX["estado"];
                }
            }
            \logApi::general2($nameLog, $idliquidacion, \funcionesGenerales::utf8_encode('Recibo de caja generado previamente en SII : Recibo : ' . $retornar["recibo"] . ', Operacion : ' . $retornar["operacion"] . ', Fecha y hora : ' . $retornar["fecha"] . ' ' . $retornar["hora"] . ', Codigo barras: ' . $retornar["codigobarras"]));
            if ($retornar["recibogob"] != '') {
                \logApi::general2($nameLog, $idliquidacion, \funcionesGenerales::utf8_encode('Recibo de caja generado previamente en SII : Recibo : ' . $retornar["recibogob"] . ', Operacion : ' . $retornar["operaciongob"] . ', Fecha y hora : ' . $retornar["fechagob"] . ' ' . $retornar["horagob"]));
            }
        }
        return $retornar;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $data
     * @param type $nameLog
     * @param type $arrServs
     * @return string
     */
    public static function generarRecibo($mysqli, $data = array(), $nameLog = '', $arrServs = array()) {
        $retornar = array(
            'codigoError' => '0000',
            'msgError' => '',
            'recibo' => '',
            'operacion' => '',
            'fecha' => '',
            'hora' => '',
            'estado' => '',
            'codigobarras' => '',
            'recibogob' => '',
            'operaciongob' => '',
            'fechagob' => '',
            'horagob' => '',
            'estadogob' => '',
            'nombrerecibo' => '',
            'identificacionrecibo' => ''
        );

        if ($data["fechareciboagenerar"] == '') {
            $retornar["fecha"] = date("Ymd");
            $retornar["fechagob"] = date("Ymd");
        } else {
            $retornar["fecha"] = $data["fechareciboagenerar"];
            $retornar["fechagob"] = $data["fechareciboagenerar"];
        }
        $retornar["hora"] = date("His");
        $retornar["horagob"] = date("His");

        $retornar["operacion"] = \funcionesRegistrales::generarSecuenciaOperacion($mysqli, $data["cajero"], $retornar["fecha"], $data["cajero"], $data["sede"]);
        if ($data["totalgobernacion"] != 0) {
            $retornar["operaciongob"] = \funcionesRegistrales::generarSecuenciaOperacion($mysqli, $data["cajero"], $retornar["fechagob"], $data["cajero"], $data["sede"]);
        }

        if ($retornar["operacion"] === false || $retornar["operaciongob"] === false) {
            $retornar["codigoError"] = '9999';
            $resultado["msgError"] = 'No fue posible generar la operación / recibo de caja en el sistema : ' . $_SESSION["generales"]["mensajeerror"];
            \logApi::general2($nameLog, $data["idliquidacion"], \funcionesGenerales::utf8_encode('No fue posible generar el recibo de caja en el sistema : ' . $_SESSION["generales"]["mensajeerror"]));
            $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
            return $resultado;
        }
        \logApi::general2($nameLog, $data["idliquidacion"], \funcionesGenerales::utf8_encode('Genero numero de operacion: ' . $retornar["operacion"]));
        if ($data["totalgobernacion"] != 0) {
            \logApi::general2($nameLog, $data["idliquidacion"], \funcionesGenerales::utf8_encode('Genero numero de operacion (gobernacion): ' . $retornar["operaciongob"]));
        }

        // ************************************************************************************************************** //
        // Localiza la foma de pago
        // ************************************************************************************************************** //
        $arrFp = array();
        $arrFp["cheque"] = '';
        $arrFp["chequebanco"] = '';
        $arrFp["consignacion"] = '';
        $arrFp["consignacionbanco"] = '';
        $arrFp["ach"] = '';
        $arrFp["achbanco"] = '';
        $arrFp["visa"] = '';
        $arrFp["visabanco"] = '';
        $arrFp["mastercard"] = '';
        $arrFp["mastercardbanco"] = '';
        $arrFp["credencial"] = '';
        $arrFp["credencialbanco"] = '';
        $arrFp["american"] = '';
        $arrFp["americanbanco"] = '';
        $arrFp["diners"] = '';
        $arrFp["dinersbanco"] = '';
        $arrFp["tdebito"] = '';
        $arrFp["tdebitobanco"] = '';
        if ($data["tipogasto"] != '1' && $data["tipogasto"] != '2' && $data["tipogasto"] != '3' && $data["tipogasto"] != '5' && $data["tipogasto"] != '9' && $data["tipogasto"] != 'A') {
            if ($data["pagocheque"] != 0) {
                $arrFp["cheque"] = $data["numerocheque"];
                $arrFp["chequebanco"] = $data["idcodban"];
            }
            if ($data["pagovisa"] != 0) {
                $arrFp["visa"] = $data["numeroautorizacion"];
                $arrFp["visabanco"] = '';
            }
            if ($data["pagomastercard"] != 0) {
                $arrFp["mastercard"] = $data["numeroautorizacion"];
                $arrFp["mastercardbanco"] = '';
            }
            if ($data["pagoamerican"] != 0) {
                $arrFp["american"] = $data["numeroautorizacion"];
                $arrFp["americanbanco"] = '';
            }
            if ($data["pagocredencial"] != 0) {
                $arrFp["credencial"] = $data["numeroautorizacion"];
                $arrFp["credencialbanco"] = '';
            }
            if ($data["pagodiners"] != 0) {
                $arrFp["diners"] = $data["numeroautorizacion"];
                $arrFp["dinersbanco"] = '';
            }
            if ($data["pagoconsignacion"] != 0) {
                $arrFp["consignacion"] = $data["numerocheque"];
                $arrFp["consignacionbanco"] = $data["idcodban"];
            }
            if ($data["pagoach"] != 0) {
                $arrFp["ach"] = $data["numeroautorizacion"];
                $arrFp["achbanco"] = '';
            }
        }

        //
        $retornar["identificacionrecibo"] = $data["identificacioncliente"];
        if ($data["tipogasto"] == '1' || $data["tipogasto"] == '2' || $data["tipogasto"] == '3') {
            $retornar["nombrerecibo"] = $data["nombrecliente"];
        } else {
            if ($data["idtipoidentificacioncliente"] == '2') {
                $retornar["nombrerecibo"] = $data["nombrecliente"];
            } else {
                $retornar["nombrerecibo"] = $data["apellido1cliente"];
                if (trim((string) $data["apellido2cliente"]) != '') {
                    $retornar["nombrerecibo"] .= ' ' . $data["apellido2cliente"];
                }
                if (trim((string) $data["nombre1cliente"]) != '') {
                    $retornar["nombrerecibo"] .= ' ' . $data["nombre1cliente"];
                }
                if (trim((string) $data["nombre2cliente"]) != '') {
                    $retornar["nombrerecibo"] .= ' ' . $data["nombre2cliente"];
                }
            }
        }

        //
        if ($data["tipogasto"] == '1' || $data["tipogasto"] == '2' || $data["tipogasto"] == '3') {
            $retornar["recibo"] = \funcionesGestionRecibos::generarSecuenciaRecibo($mysqli, 'H', $data, $arrFp, $retornar["operacion"], $retornar["fecha"], $retornar["hora"], '', $data["tiporegistro"], $retornar["identificacionrecibo"], $retornar["nombrerecibo"], '', '', '', '', '', '', '', '', 'S', $arrServs, $nameLog);
        } else {
            if ($data["tipogasto"] == '5') {
                if (CONTABILIZAR_PREPAGO_COMO == 'CXP') {
                    $retornar["recibo"] = \funcionesGestionRecibos::generarSecuenciaRecibo($mysqli, 'S', $data, $arrFp, $retornar["operacion"], $retornar["fecha"], $retornar["hora"], '', $data["tiporegistro"], $retornar["identificacionrecibo"], $retornar["nombrerecibo"], '', '', '', '', '', '', '', $data["fecharenaplicable"], 'S', $arrServs, $nameLog);
                    if ($data["totalgobernacion"] != 0) {
                        $retornar["recibogob"] = \funcionesGestionRecibos::generarSecuenciaRecibo($mysqli, 'S', $data, $arrFp, $retornar["operaciongob"], $retornar["fechagob"], $retornar["horagob"], '', $data["tiporegistro"], $retornar["identificacionrecibo"], $retornar["nombrerecibo"], '', '', '', '', '', '', '', '', 'G', $arrServs, $nameLog);
                    }
                } else {
                    $retornar["recibo"] = \funcionesGestionRecibos::generarSecuenciaRecibo($mysqli, 'H', $data, $arrFp, $retornar["operacion"], $retornar["fecha"], $retornar["hora"], '', $data["tiporegistro"], $retornar["identificacionrecibo"], $retornar["nombrerecibo"], '', '', '', '', '', '', '', '', 'S', $arrServs, $nameLog);
                }
            } else {
                if ($data["tipogasto"] == '9') {
                    $retornar["recibo"] = \funcionesGestionRecibos::generarSecuenciaRecibo($mysqli, 'D', $data, $arrFp, $retornar["operacion"], $retornar["fecha"], $retornar["hora"], '', $data["tiporegistro"], $retornar["identificacionrecibo"], $retornar["nombrerecibo"], '', '', '', '', '', '', '', 'S', $arrServs, $nameLog);
                } else {
                    $retornar["recibo"] = \funcionesGestionRecibos::generarSecuenciaRecibo($mysqli, 'S', $data, $arrFp, $retornar["operacion"], $retornar["fecha"], $retornar["hora"], '', $data["tiporegistro"], $retornar["identificacionrecibo"], $retornar["nombrerecibo"], '', '', '', '', '', '', $data["fecharenaplicable"], 'S', $arrServs, $nameLog);
                    if ($data["totalgobernacion"] != 0) {
                        $retornar["recibogob"] = \funcionesGestionRecibos::generarSecuenciaRecibo($mysqli, 'S', $data, $arrFp, $retornar["operaciongob"], $retornar["fechagob"], $retornar["horagob"], '', $data["tiporegistro"], $retornar["identificacionrecibo"], $retornar["nombrerecibo"], '', '', '', '', '', '', '', '', 'G', $arrServs, $nameLog);
                    }
                }
            }
        }

        if ($retornar["recibo"] === false || $retornar["recibogob"] === false) {
            $resultado["codigoError"] = '9999';
            $resultado["msgError"] = 'No fue posible generar el recibo de caja en el sistema : ' . $_SESSION["generales"]["mensajeerror"];
            \logApi::general2($nameLog, $data["idliquidacion"], \funcionesGenerales::utf8_encode('No fue posible generar el recibo de caja en el sistema (3) : ' . $_SESSION["generales"]["mensajeerror"]));
            $_SESSION["generales"]["codigousuario"] = $_SESSION["generales"]["codigousuariooriginal"];
            return $resultado;
        } else {
            \logApi::general2($nameLog, $data["idliquidacion"], \funcionesGenerales::utf8_encode('Recibo generado : ' . $retornar["recibo"]));
        }

        $txtx = 'Es reliquidacion: ' . $data["reliquidacion"] .
                ', Incremento en el cupo de certificados: ' . $data["incrementocupocertificados"] .
                ', Entra a generar recibo de caja: ' . $retornar["recibo"];
        if ($retornar["recibogob"] != '') {
            $txtx .= ', Entra a generar recibo de caja gobernacion: ' . $retornar["recibogob"];
        }

        \logApi::general2($nameLog, $data["idliquidacion"], $txtx);
        $retornar["estado"] = '01';
        $retornar["estadogob"] = '01';

        return $retornar;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $tipo
     * @param type $data
     * @param type $fps
     * @param type $operacion
     * @param type $fecha
     * @param type $hora
     * @param type $codbarras
     * @param type $tiporegistro
     * @param type $identificacion
     * @param type $nombre
     * @param type $organizacion
     * @param type $categoria
     * @param type $idtipodoc
     * @param type $numdoc
     * @param type $origendoc
     * @param type $fechadoc
     * @param type $estado
     * @param type $fecharenovacionaplicable
     * @param type $tiporecibo
     * @param type $arrServs
     * @param type $nameLog
     * @return bool|string
     */
    public static function generarSecuenciaRecibo($mysqli, $tipo = 'S', $data = array(), $fps = array(), $operacion = '', $fecha = '', $hora = '', $codbarras = '', $tiporegistro = '', $identificacion = '', $nombre = '', $organizacion = '', $categoria = '', $idtipodoc = '', $numdoc = '', $origendoc = '', $fechadoc = '', $estado = '', $fecharenovacionaplicable = '', $tiporecibo = 'S', $arrServs = array(), $nameLog = '') {

        // ******************************************************************************* //
        // validas que todos los datos requeridos se pasen
        // ******************************************************************************* //
        if (trim((string)$data["fecharenaplicable"]) == '') {
            $data["fecharenaplicable"] = $fecha;
        }
        $errores = array();

        if (!empty($errores)) {
            $_SESSION["generales"]["mensajeerror"] = '';
            foreach ($errores as $e) {
                $_SESSION["generales"]["mensajeerror"] .= $e . " ** ";
            }
            return false;
        }

        //
        $pagoprepago = 0;
        $pagoafiliado = 0;
        $pagoefectivo = 0;
        $pagocheque = 0;
        $pagoconsignacion = 0;
        $pagoach = 0;
        $pagovisa = 0;
        $pagomastercard = 0;
        $pagocredencial = 0;
        $pagoamerican = 0;
        $pagodiners = 0;
        $pagotdebito = 0;

        //
        if ($tiporecibo == 'S') {
            $totalesterecibo = $data["totalcamara"];
        } else {
            $totalesterecibo = $data["totalgobernacion"];
        }

        // **************************************************************************************************************** //
        // Encuentra el total a pagar por cada forma de pago
        // y dependiendo de si es Servicio o Gobernacion
        // Resta por orden Efedtivo, Cheque, Consignacion, Ach, Visa, mastercard, Credencialm, American, Diners y Tdebito
        // **************************************************************************************************************** //
        if ($tiporecibo == 'S' && $data["totalgobernacion"] == 0) {
            $pagoprepago = $data["pagoprepago"];
            $pagoafiliado = $data["pagoafiliado"];
            $pagoefectivo = $data["pagoefectivo"];
            $pagocheque = $data["pagocheque"];
            $pagoconsignacion = $data["pagoconsignacion"];
            $pagoach = $data["pagoach"];
            $pagovisa = $data["pagovisa"];
            $pagomastercard = $data["pagomastercard"];
            $pagocredencial = $data["pagocredencial"];
            $pagoamerican = $data["pagoamerican"];
            $pagodiners = $data["pagodiners"];
            $pagotdebito = $data["pagotdebito"];
        }

        if ($tiporecibo == 'G' && $data["totalcamara"] == 0) {
            $pagoprepago = $data["pagoprepago"];
            $pagoafiliado = $data["pagoafiliado"];
            $pagoefectivo = $data["pagoefectivo"];
            $pagocheque = $data["pagocheque"];
            $pagoconsignacion = $data["pagoconsignacion"];
            $pagoach = $data["pagoach"];
            $pagovisa = $data["pagovisa"];
            $pagomastercard = $data["pagomastercard"];
            $pagocredencial = $data["pagocredencial"];
            $pagoamerican = $data["pagoamerican"];
            $pagodiners = $data["pagodiners"];
            $pagotdebito = $data["pagotdebito"];
        }

        if ($tiporecibo == 'S' && $data["totalgobernacion"] != 0) {
            if ($data["pagoprepago"] != 0) {
                $pagoprepago = $totalesterecibo;
            } else {
                if ($data["pagoafiliado"] != 0) {
                    $pagoafiliado = $totalesterecibo;
                } else {
                    $totalfaltante = $totalesterecibo;
                    if ($data["pagoefectivo"] >= $totalfaltante) {
                        $pagoefectivo = $totalfaltante;
                        $totalfaltante = 0;
                    } else {
                        $pagoefectivo = $data["pagoefectivo"];
                        $totalfaltante = $totalfaltante - $data["pagoefectivo"];
                    }
                    if ($totalfaltante > 0) {
                        if ($data["pagocheque"] >= $totalfaltante) {
                            $pagocheque = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagocheque = $data["pagocheque"];
                            $totalfaltante = $totalfaltante - $data["pagocheque"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($data["pagoconsignacion"] >= $totalfaltante) {
                            $pagoconsignacion = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagoconsignacion = $data["pagoconsignacion"];
                            $totalfaltante = $totalfaltante - $data["pagoconsignacion"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($data["pagoach"] >= $totalfaltante) {
                            $pagoach = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagoach = $data["pagoach"];
                            $totalfaltante = $totalfaltante - $data["pagoach"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($data["pagovisa"] >= $totalfaltante) {
                            $pagovisa = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagovisa = $data["pagovisa"];
                            $totalfaltante = $totalfaltante - $data["pagovisa"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($data["pagomastercard"] >= $totalfaltante) {
                            $pagomastercard = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagomastercard = $data["pagomastercard"];
                            $totalfaltante = $totalfaltante - $data["pagomastercard"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($data["pagocredencial"] >= $totalfaltante) {
                            $pagocredencial = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagocredencial = $data["pagocredencial"];
                            $totalfaltante = $totalfaltante - $data["pagocredencial"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($data["pagoamerican"] >= $totalfaltante) {
                            $pagoamerican = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagoamerican = $data["pagoamerican"];
                            $totalfaltante = $totalfaltante - $data["pagoamerican"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($data["pagodiners"] >= $totalfaltante) {
                            $pagodiners = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagodiners = $data["pagodiners"];
                            $totalfaltante = $totalfaltante - $data["pagodiners"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($data["pagotdebito"] >= $totalfaltante) {
                            $pagotdebito = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagotdebito = $data["pagotdebito"];
                            $totalfaltante = $totalfaltante - $data["pagotdebito"];
                        }
                    }
                }
            }
        }

        if ($tiporecibo == 'G' && $totalcamara != 0) {
            if ($data["pagoprepago"] != 0) {
                $pagoprepago = $totalesterecibo;
            } else {
                if ($data["pagoafiliado"] != 0) {
                    $pagoafiliado = $totalesterecibo;
                } else {
                    $totalfaltante = $totalcamara;
                    if ($data["pagoefectivo"] >= $totalfaltante) {
                        $pagoefectivo = $totalfaltante;
                        $totalfaltante = 0;
                    } else {
                        $pagoefectivo = $data["pagoefectivo"];
                        $totalfaltante = $totalfaltante - $data["pagoefectivo"];
                    }
                    if ($totalfaltante > 0) {
                        if ($data["pagocheque"] >= $totalfaltante) {
                            $pagocheque = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagocheque = $data["pagocheque"];
                            $totalfaltante = $totalfaltante - $data["pagocheque"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($data["pagoconsignacion"] >= $totalfaltante) {
                            $pagoconsignacion = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagoconsignacion = $data["pagoconsignacion"];
                            $totalfaltante = $totalfaltante - $data["pagoconsignacion"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($data["pagoach"] >= $totalfaltante) {
                            $pagoach = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagoach = $data["pagoach"];
                            $totalfaltante = $totalfaltante - $data["pagoach"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($data["pagovisa"] >= $totalfaltante) {
                            $pagovisa = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagovisa = $data["pagovisa"];
                            $totalfaltante = $totalfaltante - $data["pagovisa"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($data["pagomastercard"] >= $totalfaltante) {
                            $pagomastercard = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagomastercard = $data["pagomastercard"];
                            $totalfaltante = $totalfaltante - $data["pagomastercard"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($data["pagocredencial"] >= $totalfaltante) {
                            $pagocredencial = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagocredencial = $data["pagocredencial"];
                            $totalfaltante = $totalfaltante - $data["pagocredencial"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($data["pagoamerican"] >= $totalfaltante) {
                            $pagoamerican = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagoamerican = $data["pagoamerican"];
                            $totalfaltante = $totalfaltante - $data["pagoamerican"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($data["pagodiners"] >= $totalfaltante) {
                            $pagodiners = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagodiners = $data["pagodiners"];
                            $totalfaltante = $totalfaltante - $data["pagodiners"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($data["pagotdebito"] >= $totalfaltante) {
                            $pagotdebito = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagotdebito = $data["pagotdebito"];
                            $totalfaltante = $totalfaltante - $data["pagotdebito"];
                        }
                    }

                    //
                    $pagoefectivo = $data["pagoefectivo"] - $pagoefectivo;
                    $pagocheque = $data["pagocheque"] - $pagocheque;
                    $pagoconsignacion = $data["pagoconsignacion"] - $pagoconsignacion;
                    $pagoach = $data["pagoach"] - $pagoach;
                    $pagovisa = $data["pagovisa"] - $pagovisa;
                    $pagomastercard = $data["pagomastercard"] - $pagomastercard;
                    $pagocredencial = $data["pagocredencial"] - $pagocredencial;
                    $pagoamerican = $data["pagoamerican"] - $pagoamerican;
                    $pagodiners = $data["pagodiners"] - $pagodiners;
                    $pagotdebito = $data["pagotdebito"] - $pagotdebito;
                }
            }
        }

        //
        $rec = 0;
        $recx = '';

        // ************************************************************************************************ //
        // Localiza el numero del recibo a generar dependiendo del tipo de documento
        // ************************************************************************************************ //
        if ($tipo == 'S') { // Si son recibos normales
            $tclave = 'RECIBOS-NORMALES';
            $rec = \funcionesRegistrales::retornarMregSecuenciaMaxAsentarRecibo($mysqli, $tclave);
        }
        if ($tipo == 'M') { // Si son notas de reversion
            $tclave = 'RECIBOS-NOTAS';
            $rec = \funcionesRegistrales::retornarMregSecuenciaMaxAsentarRecibo($mysqli, $tclave);
        }
        if ($tipo == 'H') { // Si son gastos administrativos
            $tclave = 'RECIBOS-GA';
            $rec = \funcionesRegistrales::retornarMregSecuenciaMaxAsentarRecibo($mysqli, $tclave);
        }
        if ($tipo == 'D') { // Si son consultas
            $tclave = 'RECIBOS-CO';
            $rec = \funcionesRegistrales::retornarMregSecuenciaMaxAsentarRecibo($mysqli, $tclave);
        }
        if ($rec === false) {
            \logApi::general2($nameLog, $data["idliquidacion"], 'Error recuperando la secuencia del recibo de caja : ' . $_SESSION["generales"]["mensajeerror"]);
            $_SESSION["generales"]["mensajeerror"] = 'Error recuperando la secuencia del recibo de caja';
            return false;
        }

        // ************************************************************************************************ //
        if ($rec == '') {
            $rec = 0;
        } else {
            $rec = intval($rec);
        }

        // ************************************************************************************************ //
        // Revisa que el recibo no esta creado previamente, de ser asi, genera un nuevo numero
        // ************************************************************************************************ //
        $seguir = "si";
        $intentos = 0;
        $creo = 'no';
        while ($seguir == 'si') {
            $rec++;
            $recx = $tipo . sprintf("%09s", $rec);
            $arrCampos = array(
                'recibo',
                'operacion',
                'factura',
                'codigobarras',
                'fecha',
                'hora',
                'usuario',
                'tipogasto',
                'tipoidentificacion',
                'identificacion',
                'razonsocial',
                'nombre1',
                'nombre2',
                'apellido1',
                'apellido2',
                'direccion',
                'direccionnot',
                'municipio',
                'municipionot',
                'pais',
                'lenguaje',
                'telefono1',
                'telefono2',
                'email',
                'zonapostal',
                'codposcom',
                'codposnot',
                'codigoregimen',
                'responsabilidadtributaria',
                'responsabilidadfiscal',
                'codigoimpuesto',
                'nombreimpuesto',
                'idliquidacion',
                'tipotramite',
                'valorneto',
                'pagoprepago',
                'pagoafiliado',
                'pagoefectivo',
                'pagocheque',
                'pagoconsignacion',
                'pagopseach',
                'pagovisa',
                'pagomastercard',
                'pagocredencial',
                'pagoamerican',
                'pagodiners',
                'pagotdebito',
                'numeroautorizacion',
                'cheque',
                'franquicia',
                'nombrefranquicia',
                'codbanco',
                'nombrebanco',
                'alertaid',
                'alertaservicio',
                'alertavalor',
                'proyectocaja',
                'numerounicorue',
                'numerointernorue',
                'tipotramiterue',
                'idformapago',
                'estado',
                'estadoemail',
                'estadosms',
                'tiporecibo'
            );

            //
            $arrValores = array(
                "'" . $recx . "'",
                "'" . $operacion . "'",
                "'" . $data["numerofactura"] . "'",
                "''",
                "'" . $fecha . "'",
                "'" . $hora . "'",
                "'" . $data["cajero"] . "'",
                "'" . $data["tipogasto"] . "'",
                "'" . $data["idtipoidentificacioncliente"] . "'",
                "'" . ltrim($data["identificacioncliente"], "0") . "'",
                "'" . addslashes(trim($nombre)) . "'",
                "'" . addslashes(trim($data["nombre1cliente"])) . "'",
                "'" . addslashes(trim($data["nombre2cliente"])) . "'",
                "'" . addslashes(trim($data["apellido1cliente"])) . "'",
                "'" . addslashes(trim($data["apellido2cliente"])) . "'",
                "'" . addslashes(trim($data["direccion"])) . "'",
                "'" . addslashes(trim($data["direccionnot"])) . "'",
                "'" . trim($data["idmunicipio"]) . "'",
                "'" . trim($data["idmunicipionot"]) . "'",
                "'" . trim($data["pais"]) . "'",
                "'" . trim($data["lenguaje"]) . "'",
                "'" . trim($data["telefono"]) . "'",
                "'" . trim($data["movil"]) . "'",
                "'" . addslashes(trim($data["email"])) . "'",
                "'" . trim($data["zonapostal"]) . "'",
                "'" . trim($data["codposcom"]) . "'",
                "'" . trim($data["codposnot"]) . "'",
                "'" . trim($data["codigoregimen"]) . "'",
                "'" . addslashes(trim($data["responsabilidadtributaria"])) . "'",
                "'" . addslashes(trim($data["responsabilidadfiscal"])) . "'",
                "'" . addslashes(trim($data["codigoimpuesto"])) . "'",
                "'" . addslashes(trim($data["nombreimpuesto"])) . "'",
                $data["numeroliquidacion"],
                "'" . trim($data["tipotramite"]) . "'",
                $totalesterecibo,
                $pagoprepago,
                $pagoafiliado,
                $pagoefectivo,
                $pagocheque,
                $pagoconsignacion,
                $pagoach,
                $pagovisa,
                $pagomastercard,
                $pagocredencial,
                $pagoamerican,
                $pagodiners,
                $pagotdebito,
                "'" . $data["numeroautorizacion"] . "'",
                "'" . $data["numerocheque"] . "'",
                "'" . $data["idfranquicia"] . "'",
                "'" . $data["nombrefranquicia"] . "'",
                "'" . $data["idcodban"] . "'",
                "'" . addslashes($data["nombrebanco"]) . "'",
                $data["alertaid"],
                "'" . $data["alertaservicio"] . "'",
                $data["alertavalor"],
                "'" . $data["proyectocaja"] . "'",
                "'" . $data["rues_numerounico"] . "'",
                "'" . $data["rues_numerointerno"] . "'",
                "'" . $data["tipotramiterue"] . "'",
                "'" . $data["idformapago"] . "'",
                "'01'",
                "'0'",
                "'0'",
                "'" . $tiporecibo . "'"
            );

            //
            if (!defined('DB_LOCK_TABLES_ASENTAMIENTO') || trim(DB_LOCK_TABLES_ASENTAMIENTO) == '' || DB_LOCK_TABLES_ASENTAMIENTO == 'SI') {
                $res = insertarRegistrosWithLockMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores);
            } else {
                $res = insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores);
            }

            //
            if ($res) {
                $seguir = 'no';
                $creo = 'si';
            } else {
                $intentos++;
                if ($intentos > 5) {
                    $seguir = 'no';
                    $creo = 'no';
                }
            }
        }


        if ($creo == 'no') {
            $_SESSION["generales"]["mensajeerror"] = 'No fue posible crear el recibo de caja para consecutivo : ' . $recx;
            \logApi::general2($nameLog, $data["idliquidacion"], $_SESSION["generales"]["mensajeerror"]);
            return false;
        }

        // ************************************************************************************************ //
        // Actualiza el consecutivo en claves valor
        // ************************************************************************************************ //	
        \funcionesRegistrales::actualizarMregSecuenciasAsentarRecibo($mysqli, $tclave, $rec);

        // 2020-01-09: JINT
        $sitcredito = 'no';
        if (
                $data["pagovisa"] != 0 ||
                $data["pagomastercard"] != 0 ||
                $data["pagocredencial"] != 0 ||
                $data["pagoamerican"] != 0 ||
                $data["pagodiners"] != 0
        ) {
            $sitcredito = 'si';
        }

        // ************************************************************************************************ //
        // Arma el detalle del recibo y lo graba
        // ************************************************************************************************ //
        $existeDiasMora = 'no';
        $columnName = 'diasmora';
        $result = ejecutarQueryMysqliApi($mysqli, "SHOW COLUMNS FROM mreg_recibosgenerados_detalle WHERE Field = '$columnName'");
        if ($result && !empty($result)) {
            $existeDiasMora = 'si';
        }
        $existeServicioOrigen = 'no';
        $columnName = 'idservicioorigen';
        $result = ejecutarQueryMysqliApi($mysqli, "SHOW COLUMNS FROM mreg_recibosgenerados_detalle WHERE Field = '$columnName'");
        if ($result && !empty($result)) {
            $existeServicioOrigen = 'si';
        }

        $arrCampos = array(
            'recibo',
            'secuencia',
            'fecha',
            'idservicio',
            // 'cc',
            'matricula',
            'proponente',
            'tipogasto',
            'ano',
            'cantidad',
            'valorbase',
            'porcentaje',
            'valorservicio',
            'identificacion',
            'razonsocial',
            'organizacion',
            'categoria',
            'idtipodoc',
            'numdoc',
            'origendoc',
            'fechadoc',
            'expedienteafectado',
            'fecharenovacionaplicable',
            'porcentajeiva',
            'valoriva',
            'servicioiva',
            'porcentajedescuento',
            'valordescuento',
            'serviciodescuento',
            'idalerta',
            'clavecontrol'
        );
        if ($existeServicioOrigen == 'si') {
            $arrCampos[] = 'idservicioorigen';
        }
        if ($existeDiasMora == 'si') {
            $arrCampos[] = 'diasmora';
        }
        $sec = 0;
        foreach ($data["liquidacion"] as $d) {
            $matx = '';
            $prox = '';
            if (!isset($d["expediente"])) {
                $d["expediente"] = '';
            }
            if (!isset($d["idservicio"])) {
                $d["idservicio"] = '';
            }
            if (!isset($d["ano"])) {
                $d["ano"] = '';
            }
            if (!isset($d["cantidad"])) {
                $d["cantidad"] = 0;
            }
            if (!isset($d["valorbase"])) {
                $d["valorbase"] = 0;
            }
            if (!isset($d["porcentaje"])) {
                $d["porcentaje"] = 0;
            }
            if (!isset($d["valorservicio"])) {
                $d["valorservicio"] = 0;
            }
            if (!isset($d["expedienteafiliado"])) {
                $d["expedienteafiliado"] = '';
            }
            if (!isset($d["ccos"])) {
                $d["ccos"] = '';
            }

            if (!isset($d["porcentajeiva"])) {
                $d["porcentajeiva"] = 0;
            }
            if (!isset($d["valoriva"])) {
                $d["valoriva"] = 0;
            }
            if (!isset($d["servicioiva"])) {
                $d["servicioiva"] = '';
            }
            if (!isset($d["porcentajedescuento"])) {
                $d["porcentajedescuento"] = 0;
            }
            if (!isset($d["valordescuento"])) {
                $d["valordescuento"] = 0;
            }
            if (!isset($d["serviciodescuento"])) {
                $d["serviciodescuento"] = '';
            }
            if (!isset($d["idalerta"])) {
                $d["idalerta"] = 0;
            }
            if ($d["idalerta"] == '') {
                $d["idalerta"] = 0;
            }
            if ($d["clavecontrol"] == '') {
                $d["clavecontrol"] = '';
            }
            if ($d["servicioorigen"] == '') {
                $d["servicioorigen"] = '';
            }
            if ($d["diasmora"] == '') {
                $d["diasmora"] = 0;
            }

            if ($tiporegistro == 'RegPro') {
                $prox = $d["expediente"];
            } else {
                $matx = $d["expediente"];
            }

            if ($d["idservicio"] != '') {
                $incluir = '';
                if (!defined('SEPARAR_RECIBOS') || SEPARAR_RECIBOS == 'NO') {
                    $incluir = 'si';
                } else {
                    if ($tiporecibo == 'S' && ltrim((string) $arrServs[$d["idservicio"]]["conceptodepartamental"], "0") == '') {
                        $incluir = 'si';
                    }
                    if ($tiporecibo == 'G' && ltrim((string) $arrServs[$d["idservicio"]]["conceptodepartamental"], "0") != '') {
                        $incluir = 'si';
                    }
                }
                if ($incluir == 'si') {
                    $sec++;
                    $arrValores = array(
                        "'" . $recx . "'",
                        $sec,
                        "'" . $fecha . "'",
                        "'" . $d["idservicio"] . "'",
                        // "''",
                        "'" . ltrim($matx, "0") . "'",
                        "'" . ltrim($prox, "0") . "'",
                        "'" . $data["tipogasto"] . "'",
                        "'" . ltrim($d["ano"], "0") . "'",
                        intval($d["cantidad"]),
                        doubleval($d["valorbase"]),
                        doubleval($d["porcentaje"]),
                        doubleval($d["valorservicio"]),
                        "'" . ltrim($data["identificacioncliente"], "0") . "'",
                        "'" . addslashes($nombre) . "'",
                        "'" . $organizacion . "'",
                        "'" . $categoria . "'",
                        "'" . $idtipodoc . "'",
                        "'" . $numdoc . "'",
                        "'" . addslashes($origendoc) . "'",
                        "'" . $fechadoc . "'",
                        "'" . $d["expedienteafiliado"] . "'",
                        "'" . $data["fecharenaplicable"] . "'",
                        doubleval($d["porcentajeiva"]),
                        doubleval($d["valoriva"]),
                        "'" . $d["servicioiva"] . "'",
                        doubleval($d["porcentajedescuento"]),
                        doubleval($d["valordescuento"]),
                        "'" . $d["serviciodescuento"] . "'",
                        $d["idalerta"],
                        "'" . $d["clavecontrol"] . "'"
                    );
                    if ($existeServicioOrigen == 'si') {
                        $arrValores[] = "'" . $d["servicioorigen"] . "'";
                    }
                    if ($existeDiasMora == 'si') {
                        $arrValores[] = $d["diasmora"];
                    }
                    $res = insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_detalle', $arrCampos, $arrValores);
                    if ($res === false) {
                        \logApi::general2($nameLog, $data["idliquidacion"], 'Error insertando en mreg_recibosgenerados_detalle : ' . $_SESSION["generales"]["mensajeerror"]);
                        return false;
                    }
                }
            }
        }

        // ************************************************************************************************ //
        // Arma formas de pago del recibo y las graba
        // ************************************************************************************************ //
        $arrCampos = array(
            'recibo',
            'tipo',
            'valor',
            'banco',
            'cheque',
        );
        $sec = 0;

        //
        if ($pagoefectivo != 0) {
            $arrValores = array(
                "'" . $recx . "'",
                "'1'",
                $pagoefectivo,
                "''",
                "''"
            );
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_fpago', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2($nameLog, $data["idliquidacion"], 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }

        if ($pagocheque != 0) {
            $arrValores = array(
                "'" . $recx . "'",
                "'2'",
                $pagocheque,
                "'" . $fps["chequebanco"] . "'",
                "'" . $fps["cheque"] . "'"
            );
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_fpago', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2($nameLog, $data["idliquidacion"], 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }

        if ($pagoconsignacion != 0) {
            $arrValores = array(
                "'" . $recx . "'",
                "'5'",
                $pagoconsignacion,
                "'" . $fps["consignacionbanco"] . "'",
                "'" . $fps["consignacion"] . "'"
            );
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_fpago', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2($nameLog, $data["idliquidacion"], 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }

        if ($pagoach != 0) {
            $arrValores = array(
                "'" . $recx . "'",
                "'7'",
                $pagoach,
                "'ACH'",
                "'" . $fps["ach"] . "'"
            );
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_fpago', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2($nameLog, $data["idliquidacion"], 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }

        if ($pagovisa != 0) {
            $arrValores = array(
                "'" . $recx . "'",
                "'3'",
                $pagovisa,
                "'VISA'",
                "'" . $fps["visa"] . "'"
            );
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_fpago', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2($nameLog, $data["idliquidacion"], 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }

        if ($pagomastercard != 0) {
            $arrValores = array(
                "'" . $recx . "'",
                "'3'",
                $pagomastercard,
                "'MASTERCARD'",
                "'" . $fps["mastercard"] . "'"
            );
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_fpago', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2($nameLog, $data["idliquidacion"], 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }

        if ($pagocredencial != 0) {
            $arrValores = array(
                "'" . $recx . "'",
                "'3'",
                $pagocredencial,
                "'CREDENCIAL'",
                "'" . $fps["credencial"] . "'"
            );
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_fpago', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2($nameLog, $data["idliquidacion"], 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }

        if ($pagoamerican != 0) {
            $arrValores = array(
                "'" . $recx . "'",
                "'3'",
                $pagoamerican,
                "'AMERICAN'",
                "'" . $fps["american"] . "'"
            );
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_fpago', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2($nameLog, $data["idliquidacion"], 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }

        if ($pagodiners != 0) {
            $arrValores = array(
                "'" . $recx . "'",
                "'3'",
                $pagodiners,
                "'DINERS'",
                "'" . $fps["diners"] . "'"
            );
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_fpago', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2($nameLog, $data["idliquidacion"], 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }

        if ($pagotdebito != 0) {
            $arrValores = array(
                "'" . $recx . "'",
                "'7'",
                $pagotdebito,
                "'T.DEBITO'",
                "'" . $fps["tdebito"] . "'"
            );
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_fpago', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2($nameLog, $data["idliquidacion"], 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }


        // ************************************************************************************************ //
        // 2016-07-31 : JINT
        // crea el recibo automáticamente en mreg_est_recibos
        // ************************************************************************************************ //
        $arrCampos = array(
            'numerorecibo',
            'ctranulacion',
            'numfactura',
            'fecoperacion',
            'horaoperacion',
            'idclase',
            'identificacion',
            'nombre',
            'operador',
            'sucursal',
            'ccos',
            'unidad',
            'producto',
            'servicio',
            'serviciodescuento',
            'cantidad',
            'valor',
            'tipogasto',
            'base',
            'moneda',
            'tasa',
            'codigocontable',
            'matricula',
            'activos',
            'anorenovacion',
            'formapago',
            'apellido1',
            'apellido2',
            'nombre1',
            'nombre2',
            'numinterno',
            'numunico',
            'numerooperacion',
            'direccion',
            'municipio',
            'telefono',
            'email',
            'compite360',
            'proyecto',
            'expedienteafectado',
            'fecharenovacionaplicable',
            'idalerta',
            'clavecontrol'
        );
        $arrValores = array();
        $sec = 0;
        if (!isset($data["camaradestino"])) {
            $data["camaradestino"] = '';
        }
        if (!isset($data["camaraorigen"])) {
            $data["camaraorigen"] = '';
        }
        if (!isset($data["numerointernorue"])) {
            $data["numerointernorue"] = '';
        }
        if (!isset($data["numerounicorue"])) {
            $data["numerounicorue"] = '';
        }
        if (!isset($data["proyecto"])) {
            $data["proyecto"] = '001';
        }

        foreach ($data["liquidacion"] as $d) {

            if (!isset($d["expediente"])) {
                $d["expediente"] = '';
            }
            if (!isset($d["idservicio"])) {
                $d["idservicio"] = '';
            }
            if (!isset($d["ano"])) {
                $d["ano"] = '';
            }
            if (!isset($d["cantidad"])) {
                $d["cantidad"] = 0;
            }
            if (!isset($d["valorbase"])) {
                $d["valorbase"] = 0;
            }
            if (!isset($d["porcentaje"])) {
                $d["porcentaje"] = 0;
            }
            if (!isset($d["valorservicio"])) {
                $d["valorservicio"] = 0;
            }
            if (!isset($d["expedienteafiliado"])) {
                $d["expedienteafiliado"] = '';
            }
            if (!isset($d["ccos"])) {
                $d["ccos"] = '';
            }
            if (!isset($d["idalerta"])) {
                $d["idalerta"] = 0;
            }
            if ($d["idalerta"] == '') {
                $d["idalerta"] = 0;
            }
            if ($d["clavecontrol"] == '') {
                $d["clavecontrol"] = '';
            }

            //            
            $cc = '';
            if ($data["tipogasto"] == '7') {
                $cc = $data["camaradestino"];
            }
            if ($data["tipogasto"] == '8') {
                $cc = $data["camaraorigen"];
            }

            $fp = '1';
            switch ($data["idformapago"]) {
                case "02":
                    $fp = '2';
                    break;
                case "03":
                    $fp = '7';
                    break;
                case "04":
                    $fp = '3';
                    break;
                case "05":
                    if ($sitcredito == 'si') {
                        $fp = '3';
                    } else {
                        $fp = '7';
                    }
                    break;
                case "06":
                    $fp = '5';
                    break;
                case "09":
                    $fp = '7';
                    break;
                case "90":
                    $fp = '4';
                    break;
                default:
                    break;
            }

            if ($d["idservicio"] != '') {
                $incluir = '';
                if (!defined('SEPARAR_RECIBOS') || SEPARAR_RECIBOS == 'NO') {
                    $incluir = 'si';
                } else {
                    if ($tiporecibo == 'S' && ltrim((string) $arrServs[$d["idservicio"]]["conceptodepartamental"], "0") == '') {
                        $incluir = 'si';
                    }
                    if ($tiporecibo == 'G' && ltrim((string) $arrServs[$d["idservicio"]]["conceptodepartamental"], "0") != '') {
                        $incluir = 'si';
                    }
                }
                if ($incluir == 'si') {
                    $sec++;

                    $arrValores = array(
                        "'" . $recx . "'",
                        "'0'",
                        "'" . $data["numerofactura"] . "'",
                        "'" . $fecha . "'",
                        "'" . $hora . "'",
                        "'" . $data["idtipoidentificacioncliente"] . "'",
                        "'" . ltrim($data["identificacioncliente"], "0") . "'",
                        "'" . addslashes(trim($nombre)) . "'",
                        "'" . $data["cajero"] . "'",
                        "'" . $data["sede"] . "'",
                        "'" . $d["ccos"] . "'", // Ccos
                        "''", // Unidad
                        "''", // Servicio
                        "'" . $d["idservicio"] . "'",
                        "''", // Servicio descuento
                        intval($d["cantidad"]),
                        doubleval($d["valorservicio"]),
                        "'" . $data["tipogasto"] . "'",
                        doubleval($d["valorbase"]),
                        "'001'",
                        0,
                        "'" . $cc . "'",
                        "'" . ltrim($d["expediente"], "0") . "'",
                        doubleval($d["valorbase"]),
                        "'" . $d["ano"] . "'",
                        "'" . $fp . "'",
                        "'" . addslashes(substr(trim($data["apellido1cliente"]), 0, 50)) . "'",
                        "'" . addslashes(substr(trim($data["apellido2cliente"]), 0, 50)) . "'",
                        "'" . addslashes(substr(trim($data["nombre1cliente"]), 0, 50)) . "'",
                        "'" . addslashes(substr(trim($data["nombre2cliente"]), 0, 50)) . "'",
                        "'" . $data["numerointernorue"] . "'",
                        "'" . $data["numerounicorue"] . "'",
                        "'" . $operacion . "'",
                        "'" . addslashes(trim($data["direccion"])) . "'",
                        "'" . addslashes(trim($data["idmunicipio"])) . "'",
                        "'" . addslashes(trim($data["telefono"])) . "'",
                        "'" . addslashes(trim($data["email"])) . "'",
                        "'NO'",
                        "'" . sprintf("%03s", $data["proyectocaja"]) . "'",
                        "'" . $d["expedienteafiliado"] . "'",
                        "'" . $data["fecharenaplicable"] . "'",
                        $d["idalerta"],
                        "'" . $d["clavecontrol"] . "'"
                    );
                    $res = insertarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', $arrCampos, $arrValores);
                    if ($res === false) {
                        \logApi::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Error insertando en mreg_est_recibos : ' . $_SESSION["generales"]["mensajeerror"]);
                    }
                }
            }
        }

        //
        return $recx;
    }

}
