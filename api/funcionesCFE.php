<?php

class funcionesCFE {

    /**
     * Rutina que se encarga de barrer la tabla de recibos generados para una fecha y rango de horas determinadas y
     * genera los registros a enviar a la tabla mreg_recibosgenerados_json_cfe
     * @param type $mysqli
     * @param type $fecha
     * @param type $horaini
     * @param type $horafin
     * @return boolean
     */
    public static function seleccionRecibosCFE($mysqli = null, $fecha = '00000000', $horaini = '000000', $horafin = '235959', $recibox = '') {

        //
        $recibo = ltrim($recibox, "0");

        //
        $salida = array();
        $salida["codigoError"] = '0000';
        $salida["mensajeError"] = '';
        $salida["cantidadrecibos"] = 0;

        //
        if ($fecha == '' && $recibo == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Fecha de cargue no reportada y/o recibo/nota no reportado';
            return $salida;
        }

        if ($fecha < '20200101' && $recibo == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Fecha no debe ser anterior al 20200101';
            return $salida;
        }

        //
        $nameLog = 'seleccionRecibosCFE_' . date("Ymd");
        \logApi::general2($nameLog, '', 'Inicia seleccion de Recibos/Notas para cargar a CFE');

        //
        if ($mysqli === null) {
            $mysqli = conexionMysqliApi();
            $cerrarMysql = 'si';
        } else {
            $cerrarMysql = 'no';
        }

        //
        if ($recibo != '') {
            \logApi::general2($nameLog, '', 'Cargue unitario de Recibos/Notas al CFE - Recibo No. ' . $recibo);
            if (defined('CONTABILIZAR_PREPAGO_COMO') && CONTABILIZAR_PREPAGO_COMO == 'CXP') {
                $condicion = "(recibo='" . $recibo . "') and (tipogasto IN ('','0','5','7')) and (estado <> '99')";
            } else {
                $condicion = "(recibo='" . $recibo . "') and (tipogasto IN ('','0','7')) and (estado <> '99')";
            }
            $recs1 = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', $condicion);
            $recs = array();
            $recs[] = $recs1;
        } else {
            if (trim($horaini) == '') {
                $horaini = '000000';
            }
            if (trim($horafin) == '' || trim($horafin) == '000000') {
                $horafin = '235959';
            }
            \logApi::general2($nameLog, '', 'Selección de Recibos/Notas a cargar al CFE - Fecha : ' . $fecha . ', rango de horas : ' . $horaini . ' y ' . $horafin);
            if (defined('CONTABILIZAR_PREPAGO_COMO') && CONTABILIZAR_PREPAGO_COMO == 'CXP') {
                $condicion = "(fecha='" . $fecha . "') and (hora between '" . sprintf("%06s", $horaini) . "' and '" . sprintf("%06s", $horafin) . "') and (tipogasto IN ('','0','5','7')) and (estado <> '99') and (substr(recibo,1,1) IN ('S','M'))";
            } else {
                $condicion = "(fecha='" . $fecha . "') and (hora between '" . sprintf("%06s", $horaini) . "' and '" . sprintf("%06s", $horafin) . "') and (tipogasto IN ('','0','7')) and (estado <> '99') and (substr(recibo,1,1) IN ('S','M'))";
            }
            $recs = retornarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $condicion, "recibo");
        }

        //
        if ($recs === false) {
            \logApi::general2($nameLog, '', 'Error al seleccionar Recibos/Notas a cargar a CFE - ' . $_SESSION["generales"]["mensajeerror"]);
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Sin Recibos/Notas para enviar a CFE';
            return $salida;
        }

        //
        if (empty($recs)) {
            \logApi::general2($nameLog, '', 'No se encontraron Recibos/Notas para el rango de fechas y horas seleccionado');
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Sin Recibos/Notas para enviar a CFE';
            return $salida;
        }

        // \logApi::general2($nameLog, '', 'Se seleccionaron ' . count($recs) . ' Recibos/Notas a cargar a CFE.');
        //
        $cant = 0;
        $canterrores = 0;
        foreach ($recs as $rec) {
            if (isset($rec["recibo"]) && $rec["recibo"] != '') {
                if (!isset($rec["estado_cfe"])) {
                    $rec["estado_cfe"] = '';
                }
                if ($rec["estado_cfe"] != '' && $rec["estado_cfe"] != '0') {
                    \logApi::general2($nameLog, '', 'Recibo/Nota ' . $rec["recibo"] . ' previamente enviado y en estado ' . $rec["estado_cfe"]);
                }
                if ($rec["estado_cfe"] == '' ||
                        $rec["estado_cfe"] == '0' ||
                        $rec["estado_cfe"] == '2' ||
                        $rec["estado_cfe"] == '3' ||
                        $rec["estado_cfe"] == '4' ||
                        $rec["estado_cfe"] == '6' ||
                        $rec["estado_cfe"] == 'E'
                ) {
                    $iDet = 0;
                    $esfacturable = 'no';
                    $dets = array();
                    $detsx = retornarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_detalle', "recibo='" . $rec["recibo"] . "'", "id");

                    $dets1 = array();
                    $tieneivas = 'no';
                    foreach ($detsx as $dt) {
                        $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $dt["idservicio"] . "'");
                        if ($serv["idesiva"] == 'si') {
                            $tieneivas = 'si';
                        }
                    }

                    //
                    if ($tieneivas == 'no') {
                        foreach ($detsx as $dt) {
                            if ($dt["idservicio"] == '06010008') {
                                $dt["idservicio"] = '06010002';
                            }
                            if (!isset($dets1[$dt["idservicio"]])) {
                                $dets1[$dt["idservicio"]] = $dt;
                            } else {
                                $dets1[$dt["idservicio"]]["valorservicio"] = $dets1[$dt["idservicio"]]["valorservicio"] + $dt["valorservicio"];
                                $dets1[$dt["idservicio"]]["valorbase"] = $dets1[$dt["idservicio"]]["valorbase"] + $dt["valorbase"];
                            }
                        }
                        $detsx = array();
                        foreach ($dets1 as $dt) {
                            if ($dt["valorservicio"] != 0) {
                                $detsx[] = $dt;
                            }
                        }
                    }

                    if ($detsx && !empty($detsx)) {
                        foreach ($detsx as $det) {
                            if ($det["idservicio"] != '' && substr($det["idservicio"], 0, 4) != '0750' && $det["valorservicio"] != 0 && $det["idalerta"] == 0) {
                                $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $det["idservicio"] . "'");
                                if ($serv === false || empty($serv)) {
                                    $serv = array();
                                    $serv["nombre"] = 'Servicio desconocido';
                                    $serv["idesiva"] = 'N';
                                    $serv["valorservicio"] = 0;
                                    $serv["conceptodepartamental"] = '';
                                    $serv["facturable_electronicamente"] = 'SI';
                                    $serv["idgravado1"] = '';
                                    $serv["idgravado2"] = '';
                                    $serv["idgravado3"] = '';
                                    $serv["idgravado4"] = '';
                                    $serv["idgravado5"] = '';
                                    $serv["idgravado6"] = '';
                                    $serv["idgravado7"] = '';
                                }
                                $serv["esgravadoiva"] = 'no';
                                if (trim($serv["idgravado1"]) != '' ||
                                        trim($serv["idgravado2"]) != '' ||
                                        trim($serv["idgravado3"]) != '' ||
                                        trim($serv["idgravado4"]) != '' ||
                                        trim($serv["idgravado5"]) != '' ||
                                        trim($serv["idgravado6"]) != '' ||
                                        trim($serv["idgravado7"]) != '') {
                                    $serv["esgravadoiva"] = 'si';
                                }
                                if ($serv["idesiva"] == 'S' && $serv["esgravadoiva"] == 'si') {
                                    $serv["idesiva"] = 'N';
                                }
                                if ($serv["idesiva"] == 'S') {
                                    $serv["nombre"] = 'IVA';
                                }

                                //
                                $iDet++;
                                $dets[$iDet] = $det;
                                $dets[$iDet]["nombre"] = $serv["nombre"];
                                $dets[$iDet]["porcentajeaplicado"] = 0;
                                $dets[$iDet]["gravadoiva"] = '';
                                $dets[$iDet]["esiva"] = '';
                                $dets[$iDet]["conceptodepartamental"] = $serv["conceptodepartamental"];

                                if ($serv["esgravadoiva"] == 'si') {
                                    $dets[$iDet]["gravadoiva"] = 'si';
                                }
                                if ($serv["idesiva"] == 'S') {
                                    $dets[$iDet]["esiva"] = 'si';
                                    $dets[$iDet]["porcentajeaplicado"] = $serv["valorservicio"];
                                }
                                if ($serv["facturable_electronicamente"] == 'SI') {
                                    $esfacturable = 'si';
                                }
                            }
                        }
                        if ($esfacturable == 'no') {
                            $arrCampos = array(
                                'estado_cfe',
                                'fechahora_envio_cfe'
                            );
                            $arrValores = array(
                                "'1'",
                                "''"
                            );
                            regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $rec["recibo"] . "'");
                            \logApi::general2($nameLog, '', 'Recibo/Nota ' . $rec["recibo"] . ' no tiene servicios que sean facturables');
                        }
                        if ($esfacturable == 'si') {
                            if (substr($rec["recibo"], 0, 1) == 'M') {
                                if ($rec["numerofacturareversar"] == '' && $rec["prefijofacturareversar"] == '') {
                                    $result = \funcionesCFE::procesarNotasCreditoCFE($mysqli, $rec, $dets, 'si');
                                } else {
                                    if ($rec["factura"] == '') {
                                        $result = \funcionesCFE::procesarNotasCreditoCFE($mysqli, $rec, $dets, 'si');
                                    } else {
                                        $reccru = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', "recibo='" . $rec["factura"] . "'", '*', 'U');
                                        if ($reccru === false || empty($reccru)) {
                                            \logApi::general2($nameLog, '', 'Nota ' . $rec["recibo"] . ' sin recibo reversado asociado');
                                            $salida["mensajeError"] .= '<br>';
                                            $salida["mensajeError"] .= 'Nota ' . $rec["recibo"] . ' sin recibo reversado asociado';
                                            $result = false;
                                        } else {
                                            if ($reccru["fecha"] < CFE_FECHA_INICIAL) {
                                                $result = \funcionesCFE::procesarNotasCreditoCFE($mysqli, $rec, $dets, 'si');
                                            } else {
                                                if ($reccru["estado_cfe"] == '0' || $reccru["estado_cfe"] == '') {
                                                    \logApi::general2($nameLog, '', 'Nota ' . $rec["recibo"] . ' el recibo que se está reversando (' . $rec["factura"] . ') no ha sido facturado electrónicamente aún');
                                                    $salida["mensajeError"] .= '<br>';
                                                    $salida["mensajeError"] .= 'Nota ' . $rec["recibo"] . ' el recibo que se está reversando (' . $rec["factura"] . ') no ha sido facturado electrónicamente aún';
                                                    $result = false;
                                                }
                                                if ($reccru["estado_cfe"] == '5' || $reccru["estado_cfe"] == '7' || $reccru["estado_cfe"] == '8') {
                                                    $result = \funcionesCFE::procesarNotasCreditoCFE($mysqli, $rec, $dets, 'no');
                                                }
                                                if ($reccru["estado_cfe"] == '2' || $reccru["estado_cfe"] == '3' || $reccru["estado_cfe"] == '4' || $reccru["estado_cfe"] == '6' || $reccru["estado_cfe"] == '9' || $reccru["estado_cfe"] == 'E') {
                                                    \logApi::general2($nameLog, '', 'Nota ' . $rec["recibo"] . ' el recibo que se está reversando (' . $rec["factura"] . ') no ha terminado su proceso de facturación electrónica.');
                                                    $salida["mensajeError"] .= '<br>';
                                                    $salida["mensajeError"] .= 'Nota ' . $rec["recibo"] . ' el recibo que se está reversando (' . $rec["factura"] . ') no ha terminado su proceso de facturación electrónica.';
                                                    $result = false;
                                                }
                                            }
                                        }
                                    }
                                }
                            } else {
                                $result = \funcionesCFE::procesarRecibosCFE($mysqli, $rec, $dets);
                            }

                            if ($result) {
                                if ($result["codigoError"] == '0000') {
                                    $ocfe = date("Y-m-d") . ' ' . date("H:i:s") . '<br>Enviado al CFE<hr>';
                                    $arrCampos = array(
                                        'estado_cfe',
                                        'fechahora_envio_cfe',
                                        'observaciones_cfe'
                                    );
                                    $arrValores = array(
                                        "'2'",
                                        "'" . date("Ymd") . ' ' . date("His") . "'",
                                        "'" . addslashes($ocfe) . "'"
                                    );
                                    regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $rec["recibo"] . "'");
                                    $cant++;
                                    \logApi::general2($nameLog, '', 'Recibo/Nota ' . $rec["recibo"] . ' cargado al CFE');
                                } else {
                                    if ($result["codigoError"] != '0001' && $result["codigoError"] != '0002') {
                                        $ocfe = date("Y-m-d") . ' ' . date("H:i:s") . '<br>Enviado al CFE con errores : ' . $result["mensajeError"] . '<hr>';
                                        $arrCampos = array(
                                            'estado_cfe',
                                            'fechahora_envio_cfe',
                                            'observaciones_cfe'
                                        );
                                        $arrValores = array(
                                            "'E'",
                                            "'" . date("Ymd") . ' ' . date("His") . "'",
                                            "'" . addslashes($ocfe) . "'"
                                        );
                                        regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $rec["recibo"] . "'");
                                        \logApi::general2($nameLog, '', 'Error cargando Recibo/Nota ' . $rec["recibo"] . ' al CFE - ' . $result["mensajeError"]);
                                        if (trim($result["mensajeError"]) != '') {
                                            $salida["mensajeError"] .= '<br>';
                                            $salida["mensajeError"] .= $rec["recibo"] . ' - ' . $result["mensajeError"];
                                        }
                                    } else {
                                        $salida["mensajeError"] .= '<br>';
                                        $salida["mensajeError"] .= $rec["recibo"] . ' - ' . $result["mensajeError"];
                                    }
                                }
                            } else {
                                \logApi::general2($nameLog, '', 'Error cargando Recibo/Nota ' . $rec["recibo"] . ' al CFE - No se recibio respuesta del metodo enviarReciboCFE');
                                if (trim($salida["mensajeError"]) != '') {
                                    $salida["mensajeError"] .= '<br>';
                                    $salida["mensajeError"] .= $rec["recibo"] . ' - ' . 'No se recibio respuesta del metodo enviarReciboCFE';
                                }
                            }
                        }
                    }
                }
            }
        }

        //
        if ($cerrarMysql == 'si') {
            $mysqli->close();
        }

        //
        if ($salida["mensajeError"] != '') {
            $salida["codigoError"] = '9999';
        }

        //
        $salida["cantidadrecibos"] = $cant;
        return $salida;
    }

    public static function procesarNotasCreditoCFE($mysqli, $rec, $dets, $sinfactura = 'no') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $nameLog = 'procesarNotasCreditoCFE_' . date("Ymd");

        $salida = array();
        $salida["codigoError"] = '0000';
        $salida["mensajeError"] = 'Procesado correctamente';
        $salida["json"] = '';
        $salida["jsonresponse"] = '';

        //
        if (substr($rec["recibo"], 0, 1) != 'M') {
            $salida["codigoError"] = '0001';
            $salida["mensajeError"] = 'Pendiente de procesar - no es nota de reversión';
            return $salida;
        }

        //
        $bruto = 0;
        $base = 0;
        $baseimponible = 0;
        $cargos = 0;
        $iva = 0;
        $valivas = 0;
        $redondeos = 0;
        $piva = 0;
        $ivacalculado = 0;
        $cantidadivas = 0;
        $neto = 0;
        $descuentos = 0;
        $iLin = 0;
        $txtServicio = '';

        //
        $txtx = '';
        foreach ($dets as $dt) {
            $dt["valorservicio"] = $dt["valorservicio"] * -1;
            $iLin++;
            if ($iLin == 1) {
                $txtServicio = $dt["nombre"];
            }

            if ($dt["valorservicio"] >= 0) {
                if ($dt["esiva"] == 'si') {
                    $iva = $iva + $dt["valorservicio"];
                    $piva = 19;
                    $cantidadivas++;
                } else {
                    $bruto = $bruto + $dt["valorservicio"];
                    if ($dt["gravadoiva"] == 'si') {
                        $baseimponible = $baseimponible + $dt["valorservicio"];
                        $base = $base + $dt["valorservicio"];
                        $ivacalculado = $ivacalculado + round($dt["valorservicio"] * 0.19, 0);
                        $piva = 19;
                    }
                }
            }

            //
            $neto = $neto + $dt["valorservicio"];

            $txtx .= $iLin . '.)  Servicio: ' . $dt["idservicio"] . ' ' . $dt["nombre"] . ', EsIva : ' . $dt["esiva"] . ', EsGravadoIva : ' . $dt["gravadoiva"] . ', Valor: ' . $dt["valorservicio"] . "\r\n";
            // \logApi::general2($nameLog, $rec["recibo"], $tlin);
        }
        \logApi::general2($nameLog, $rec["recibo"], $txtx);

        //
        if ($iva != $ivacalculado) {
            $salida["codigoError"] = '0001';
            $salida["mensajeError"] = 'Error en el IVA aplicado (' . $iva . ') vs el IVA calculado (' . $ivacalculado . ')';
            return $salida;
        }

        //
        $arrJson = array();
        $arrJson["nroLote"] = 0;
        $arrJson["fecha"] = date("Y-m-d");
        $arrJson["documentos"] = array();

        //
        $doc = array();
        $doc["codigoSiiCamara"] = CODIGO_EMPRESA;
        $doc["identificadorCamara"] = null;
        $doc["codigoSiiSucursal"] = substr($rec["operacion"], 0, 2);
        $doc["sucursal"] = null;
        $doc["usuariosii"] = $rec["usuario"];
        $doc["usuarioSii"] = $rec["usuario"];
        $doc["usuarioExpide"] = '';
        if ($rec["usuario"] == 'USUPUBXX') {
            $doc["usuarioExpide"] = 'PROCESOS VIRTUALES';
        } else {
            $doc["usuarioExpide"] = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $rec["usuario"] . "'", "nombreusuario");
        }
        $doc["usuarioAprueba"] = '';
        if (defined('CFE_FACTURADOR_NOMBRE_APROBADOR') && CFE_FACTURADOR_NOMBRE_APROBADOR != '') {
            $doc["usuarioAprueba"] = CFE_FACTURADOR_NOMBRE_APROBADOR;
        }
        $doc["tipoNumeracion"] = "2"; // Notas crédito
        $doc["nroDocumento"] = $rec["recibo"];
        $doc["codigoMoneda"] = "COP";
        $doc["descripcion"] = "";
        if ($rec["fechaexpedicionfactura"] != '') {
            $doc["fechaExpedicion"] = \funcionesGenerales::mostrarFechaDDMMYYYY($rec["fechaexpedicionfactura"]);
            $doc["horaExpedicion"] = \funcionesGenerales::mostrarHora($rec["hora"]);
            $doc["fechaVencimiento"] = \funcionesGenerales::mostrarFechaDDMMYYYY($rec["fechaexpedicionfactura"]);
        } else {
            $doc["fechaExpedicion"] = \funcionesGenerales::mostrarFechaDDMMYYYY($rec["fecha"]);
            $doc["horaExpedicion"] = \funcionesGenerales::mostrarHora($rec["hora"]);
            $doc["fechaVencimiento"] = \funcionesGenerales::mostrarFechaDDMMYYYY($rec["fecha"]);
        }

        // ************************************************************* //
        // Documento referenciado
        // ************************************************************* //
        $doc["listaDocumentosReferenciados"] = array();
        if ($rec["prefijofacturareversar"] != '') {
            $recref = retornarRegistroMysqliApi($mysqli, 'mreg_cfe_facturas', "prefijofactura='" . $rec["prefijofacturareversar"] . "' and nrofactura='" . $rec["nrofacturareversar"] . "'", '*', 'U');
            if ($recref === false || empty($recref)) {
                $dr = array();
                $dr["nroDocRef"] = $rec["prefijofacturareversar"] . $rec["numerofacturareversar"];
                $dr["tipo"] = "FE";
                $dr["fecha"] = \funcionesGenerales::mostrarFechaDDMMYYYY($rec["fechafacturareversar"]);
                $dr["algoritmo"] = "CUFE-SHA384";
                $dr["cufe"] = $rec["cufefacturareversar"];
                $doc["listaDocumentosReferenciados"][] = $dr;
            } else {
                $dr = array();
                $dr["nroDocRef"] = $rec["prefijofacturareversar"] . $rec["numerofacturareversar"];
                $dr["tipo"] = "FE";
                $dr["fecha"] = \funcionesGenerales::mostrarFechaDDMMYYYY($recref["fechafactura"]);
                $dr["algoritmo"] = "CUFE-SHA384";
                $dr["cufe"] = $rec["cufefacturareversar"];
                $doc["listaDocumentosReferenciados"][] = $dr;
            }
        } else {
            if ($rec["factura"] != '') {
                $recref = retornarRegistroMysqliApi($mysqli, 'mreg_cfe_facturas', "recibo='" . $rec["factura"] . "'", '*', 'U');
                if ($recref && !empty($recref)) {
                    $dr = array();
                    $dr["nroDocRef"] = $recref["prefijofactura"] . $recref["nrofactura"];
                    $dr["tipo"] = "FE";
                    $dr["fecha"] = \funcionesGenerales::mostrarFechaDDMMYYYY($recref["fechafactura"]);
                    $dr["algoritmo"] = "CUFE-SHA384";
                    $dr["cufe"] = $recref["cufe"];
                    $doc["listaDocumentosReferenciados"][] = $dr;
                }
            }
        }

        if (empty($doc["listaDocumentosReferenciados"])) {
            $doc["listaDocumentosReferenciados"] = null;
        }

        // ************************************************************* //
        // Asigna la descripción de la nota.
        // ************************************************************* //
        $doc["descripcion"] = "NOTA CREDITO " . $txtServicio . " RECIBO NO. " . $rec["factura"] . '. Factura No. ' . $dr["nroDocRef"];

        // ************************************************************* //
        // Servicios facturados y descuentos
        // ************************************************************* //        
        $doc["docRecibidoProductos"] = array();
        $doc["listaCargosDescuentos"] = array();
        $doc["listaCorrecciones"] = array();
        $corr = array();
        $corr["id"] = 1;
        $corr["codigo"] = 1;
        $corr["descripcion"] = 'Se reversa por anulación de la factura';

        $iDesc = 0;

        //
        $erroresDescuentos = '';
        $servorigendescuentos = array();
        $servorigeniva = array();
        $bruto = 0;

        // ****************************************************** //
        // Localiza descuentos
        // ****************************************************** //
        $servdesc["idservicio"] = '';
        $servdesc["Razon"] = '';
        $servdesc["base"] = 0;
        $servdesc["porcentaje"] = 0;
        $servdesc["valor"] = 0;
        $servdesc["aplicado"] = '';
        foreach ($dets as $dt) {
            $dt["valorservicio"] = $dt["valorservicio"] * -1;
            if ($dt["valorservicio"] < 0 && ($dt["idservicio"] < '01090151' || $dt["idservicio"] > '01090160')) {
                $servdesc["idservicio"] = $dt["idservicio"];
                $servdesc["Razon"] = $dt["nombre"];
                $servdesc["base"] = 0;
                $servdesc["porcentaje"] = 0;
                $servdesc["valor"] = abs($dt["valorservicio"]);
                $servdesc["aplicado"] = 'pe';
                if ($dt["idservicio"] == '01090110') {
                    $servdesc["base"] = abs($dt["valorservicio"]);
                    $servdesc["porcentaje"] = 100;
                }
                if ($dt["idservicio"] == '01090111') {
                    $servdesc["base"] = abs($dt["valorservicio"]);
                    $servdesc["porcentaje"] = 100;
                }
            }
        }

        // ****************************************************** //
        // Localiza impuestos
        // ****************************************************** //
        $servorigeniva["Razon"] = '';
        $servorigeniva["base"] = 0;
        $servorigeniva["porcentaje"] = 0;
        $servorigeniva["valor"] = 0;
        $servorigeniva["aplicado"] = '';
        foreach ($dets as $dt) {
            $dt["valorservicio"] = $dt["valorservicio"] * -1;
            if ($dt["valorservicio"] > 0 && $dt["esiva"] == 'si') {
                $servorigeniva["porcentaje"] = 19;
                $servorigeniva["valor"] = $servorigeniva["valor"] + $dt["valorservicio"];
                $servorigeniva["aplicado"] = 'pe';
                $servorigeniva["Razon"] = $dt["nombre"];
            } else {
                if ($dt["valorservicio"] > 0 && $dt["gravadoiva"] == 'si') {
                    $servorigeniva["base"] = $servorigeniva["base"] + $dt["valorservicio"];
                }
            }
        }


        // ****************************************************** //
        // Procesa servicios
        // ****************************************************** //
        $linprod = 0;
        foreach ($dets as $dt) {
            $dt["valorservicio"] = $dt["valorservicio"] * -1;
            if ($dt["valorservicio"] > 0) {
                if ($dt["esiva"] != 'si') {
                    $prd = array();
                    if ($dt["cantidad"] == 0) {
                        $dt["cantidad"] = 1;
                    }
                    $prd["cantidad"] = number_format(abs($dt["cantidad"]), 2, '.', '');
                    $prd["descripcion"] = $dt["nombre"];
                    if (ltrim((string) $dt["conceptodepartamental"], "0") == '') {
                        $prd["cargo"] = 'CAM';
                    } else {
                        $prd["cargo"] = 'DEP';
                    }
                    $prd["identificador"] = null;
                    $prd["imprimible"] = true;
                    $prd["codigoPrecio"] = '01';
                    $prd["codigoUnidad"] = '94'; // Unidad
                    $prd["esMuestraComercial"] = false;
                    $prd["idProducto"] = $dt["idservicio"];
                    $prd["valorTotal"] = number_format($dt["valorservicio"], 2, '.', '');
                    $valortotalproducto = $dt["valorservicio"];
                    $sumarbruto = $dt["valorservicio"];
                    $prd["pagable"] = true;

                    $prd["descripcion"] = $dt["nombre"] . ' (' . $dt["cantidad"] . ')';
                    $prd["valorUnitario"] = number_format($dt["valorservicio"], 2, ".", "");
                    $prd["cantidad"] = number_format(1, 2, ".", "");

                    //
                    $prd["impuestos"] = array();
                    if ($dt["gravadoiva"] == 'si') {
                        if ($cantidadivas == 1) {
                            if ($servorigeniva["aplicado"] == 'pe') {
                                $imps = array();
                                $imps["baseGravable"] = $servorigeniva["base"];
                                $imps["nombre"] = $servorigeniva["Razon"];
                                $imps["valor"] = number_format($servorigeniva["valor"], 2, '.', '');
                                $imps["codigo"] = '01';
                                $imps["porcentaje"] = number_format($servorigeniva["porcentaje"], 2, '.', '');
                                $imps["valorPorUnidad"] = number_format($prd["valorUnitario"], 4, '.', '');
                                $prd["impuestos"][] = $imps;
                                $servorigeniva["aplicado"] = 'si';
                                $valivas = $valivas + $servorigeniva["valor"];
                            }
                        } else {
                            $valiva = round($dt["valorservicio"] * 0.19, 2);
                            $valivas = $valivas + $valiva;
                            $imps = array();
                            $imps["baseGravable"] = $dt["valorservicio"];
                            $imps["nombre"] = 'IVA';
                            $imps["valor"] = number_format($valiva, 2, '.', '');
                            $imps["codigo"] = '01';
                            $imps["porcentaje"] = 19.00;
                            $imps["valorPorUnidad"] = number_format($dt["valorservicio"], 2, '.', '');
                            $prd["impuestos"][] = $imps;
                        }
                    }

                    //
                    $prd["listaCargosDescuentos"] = array();

                    //
                    $iDesc = 0;
                    if ($servdesc["aplicado"] == 'pe') {
                        if ($servdesc["idservicio"] == '01090110') {
                            if ($dt["idservicio"] == '01020101' ||
                                    $dt["idservicio"] == '01020106' ||
                                    $dt["idservicio"] == '01020108' ||
                                    $dt["idservicio"] == '01020109' ||
                                    ($dt["idservicio"] >= '01020112' && $dt["idservicio"] <= '01020118')) {
                                $desc = array();
                                $desc["id"] = $linprod; // otros descuentos
                                $desc["esCargo"] = false; // otros descuentos
                                $desc["codigo"] = '11'; // otros descuentos
                                $desc["codigo"] = '00'; // 2021-08-17
                                $desc["Razon"] = $servdesc["Razon"];
                                $desc["base"] = number_format($servdesc["base"], 2, '.', '');
                                $desc["porcentaje"] = number_format($servdesc["porcentaje"], 2, '.', '');
                                $desc["valor"] = number_format($servdesc["valor"], 2, '.', '');
                                $prd["listaCargosDescuentos"][] = $desc;
                                $prd["valorTotal"] = number_format($dt["valorservicio"] - $servdesc["valor"], 2, '.', '');
                                $valortotalproducto = $dt["valorservicio"] - $servdesc["valor"];
                                $sumarbruto = $dt["valorservicio"] - $servdesc["valor"];
                                $servdesc["aplicado"] = 'si';
                            }
                        }
                        if ($servdesc["idservicio"] == '01090111') {
                            if ($dt["idservicio"] == '01020201') {
                                $desc = array();
                                $desc["id"] = $iDesc; // otros descuentos
                                $desc["esCargo"] = false; // otros descuentos
                                $desc["codigo"] = '11'; // otros descuentos
                                $desc["codigo"] = '00'; // 2021-08-17
                                $desc["Razon"] = $servdesc["Razon"];
                                $desc["base"] = $servdesc["base"];
                                $desc["porcentaje"] = $servdesc["porcentaje"];
                                $desc["valor"] = $servdesc["valor"];
                                $prd["listaCargosDescuentos"][] = $desc;
                                $prd["valorTotal"] = number_format($dt["valorservicio"] - $servdesc["valor"], 2, '.', '');
                                $valortotalproducto = $dt["valorservicio"] - $servdesc["valor"];
                                $sumarbruto = $dt["valorservicio"] - $servdesc["valor"];
                                $servdesc["aplicado"] = 'si';
                            }
                        }
                    }

                    // Aplica beneficios de decreto 1756
                    if ($dt["clavecontrol"] == '') {
                        $cnt = $dt["idservicio"] . '-' . $dt["matricula"] . '-' . ltrim(trim($dt["ano"]), "0");
                    } else {
                        if (strlen($dt["clavecontrol"]) > 10) {
                            list ($sv, $mt, $an) = explode("-", $dt["clavecontrol"]);
                            $dt["clavecontrol"] = $sv . '-' . $mt . '-' . ltrim(trim($an), "0");
                        }
                        $cnt = $dt["clavecontrol"];
                    }
                    foreach ($dets as $dtx) {
                        if ($dtx["idservicio"] >= '01090151' && $dtx["idservicio"] <= '01090160') {
                            \logApi::general2($nameLog, $rec["recibo"], 'Encontro descuento tipo ' . $dtx["idservicio"]);
                            if ($dtx["clavecontrol"] == '') {
                                $servx1 = '';
                                switch ($dtx["idservicio"]) {
                                    case "01050151" : $servx1 = '01020201';
                                        break;
                                    case "01050153" : $servx1 = '01020202';
                                        break;
                                }
                                if ($servx1 != '') {
                                    $dtx["clavecontrol"] = $servx1 . '-' . $dtx["expediente"] . '-' . $dtx["ano"];
                                }
                            }
                            if ($dtx["clavecontrol"] != '') {
                                if (strlen($dtx["clavecontrol"]) > 10) {
                                    list ($sv, $mt, $an) = explode("-", $dtx["clavecontrol"]);
                                    $dtx["clavecontrol"] = $sv . '-' . $mt . '-' . ltrim(trim($an), "0");
                                }
                                if ($cnt == $dtx["clavecontrol"]) {
                                    $desc = array();
                                    $desc["id"] = $linprod; // otros descuentos
                                    $desc["esCargo"] = false; // otros descuentos
                                    $desc["codigo"] = '11'; // otros descuentos
                                    $desc["codigo"] = '00'; // 2021-08-17
                                    $desc["Razon"] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $dtx["idservicio"] . "'", "nombre");
                                    $porcdes = abs($dtx["valorservicio"]) / $dt["valorservicio"] * 100;
                                    $desc["base"] = number_format($dt["valorservicio"], 2, '.', '');
                                    $desc["porcentaje"] = number_format($porcdes, 2, '.', '');
                                    $desc["valor"] = number_format(abs($dtx["valorservicio"]), 2, '.', '');
                                    $prd["listaCargosDescuentos"][] = $desc;
                                    $prd["valorTotal"] = number_format($dt["valorservicio"] - abs($dtx["valorservicio"]), 2, '.', '');
                                    $valortotalproducto = $dt["valorservicio"] - abs($dtx["valorservicio"]);
                                    $sumarbruto = $dt["valorservicio"] - abs($dtx["valorservicio"]);
                                }
                            }
                        }
                    }

                    //
                    if ($servdesc["aplicado"] == 'pe') {
                        if ($servdesc["idservicio"] != '01090110' && $servdesc["idservicio"] != '01090111') {
                            if ($servdesc["valor"] >= $dt["valorservicio"]) {
                                $desc = array();
                                $desc["id"] = $linprod; // otros descuentos
                                $desc["esCargo"] = false; // otros descuentos
                                $desc["codigo"] = '11'; // otros descuentos
                                $desc["codigo"] = '00'; // 2021-08-17
                                $desc["Razon"] = $servdesc["Razon"];
                                $servdesc["valor"] = $servdesc["valor"] - $dt["valorservicio"];
                                $desc["base"] = number_format($dt["valorservicio"], 2, '.', '');
                                $desc["porcentaje"] = number_format(100, 2, '.', '');
                                $desc["valor"] = number_format($servdesc["valor"], 2, '.', '');
                                $prd["listaCargosDescuentos"][] = $desc;
                                $prd["valorTotal"] = number_format(0, 2, '.', '');
                                $valortotalproducto = 0;
                                $sumarbruto = 0;
                            } else {
                                $desc = array();
                                $desc["id"] = $linprod; // otros descuentos
                                $desc["esCargo"] = false; // otros descuentos
                                $desc["codigo"] = '11'; // otros descuentos
                                $desc["codigo"] = '00'; // 2021-08-17
                                $desc["Razon"] = $servdesc["Razon"];
                                $porcdes = $servdesc["valor"] / $dt["valorservicio"] * 100;
                                $desc["base"] = number_format($dt["valorservicio"], 2, '.', '');
                                $desc["porcentaje"] = number_format($porcdes, 2, '.', '');
                                $desc["valor"] = number_format($servdesc["valor"], 2, '.', '');
                                $prd["listaCargosDescuentos"][] = $desc;
                                $prd["valorTotal"] = number_format($dt["valorservicio"] - $servdesc["valor"], 2, '.', '');
                                $valortotalproducto = $dt["valorservicio"] - $servdesc["valor"];
                                $sumarbruto = $dt["valorservicio"] - $servdesc["valor"];
                                $servdesc["aplicado"] = 'si';
                            }
                        }
                    }

                    //
                    $prd["item"] = array();
                    $prd["item"]["codigoEstandar"] = '999';
                    $prd["item"]["descripcion"] = $dt["nombre"];

                    //
                    if ($valortotalproducto != 0) {
                        $linprod++;
                        $prd["numeroLinea"] = $linprod;
                        $doc["docRecibidoProductos"][] = $prd;
                    }

                    $bruto = $bruto + $sumarbruto;
                }
            }
        }

        //
        if ($valivas != $iva) {
            $redondeos = $iva - $valivas;
        }

        // ********************************************************* //
        // Impuestos y deducciones
        // ********************************************************* //
        $doc["subtotalesImpuestosDeduccion"] = array();

        //
        if ($iva != 0) {
            $imp = array();
            $imp["baseGravable"] = $servorigeniva["base"];
            $imp["nombre"] = 'IVA';
            $imp["valor"] = number_format($valivas, 2, '.', '');
            // $imp["valor"] = number_format($iva, 2, '.', '');
            $imp["codigo"] = '01';
            $imp["porcentaje"] = 19.00;
            $doc["subtotalesImpuestosDeduccion"][] = $imp;
        }

        /*
          if ($servdesc["idservicio"] != '' && $servdesc["aplicado"] == 'pe') {
          $imp = array();
          $imp["baseGravable"] = 0;
          $imp["nombre"] = $servdesc["Razon"];
          $imp["valor"] = number_format($servdesc["valor"], 2, '.', '');
          $imp["codigo"] = '01';
          $imp["porcentaje"] = 0;
          $doc["subtotalesImpuestosDeduccion"][] = $imp;
          }
         */

        // ************************************************************* //
        // Objeto tipo de pago
        // ************************************************************* //
        $doc["tipoPago"] = array();
        $doc["tipoPago"]["metodoPago"] = '1'; // Contado
        $doc["tipoPago"]["codigoMedioPago"] = '10'; // Efectivo
        switch ($rec["idformapago"]) {
            case "01": $doc["tipoPago"]["codigoMedioPago"] = '10';
                break; // Cheque
            case "02": $doc["tipoPago"]["codigoMedioPago"] = '20';
                break; // Cheque
            case "03": $doc["tipoPago"]["codigoMedioPago"] = '49';
                break; // Tarjeta débito
            case "04": $doc["tipoPago"]["codigoMedioPago"] = '48';
                break; // tarjeta crédito
            case "05": $doc["tipoPago"]["codigoMedioPago"] = '49';
                break; // pago electrónico
            case "06": $doc["tipoPago"]["codigoMedioPago"] = '42';
                break; // consignacion
            case "07": $doc["tipoPago"]["codigoMedioPago"] = '42';
                break; // pago en bancos
            case "08": $doc["tipoPago"]["codigoMedioPago"] = '10';
                break; // ATH
            case "09": $doc["tipoPago"]["codigoMedioPago"] = '3';
                break; // ACH
            case "10": $doc["tipoPago"]["codigoMedioPago"] = '10';
                break; // Efecty
            case "11": $doc["tipoPago"]["codigoMedioPago"] = '93';
                break; // pago referneciado
            case "91": $doc["tipoPago"]["codigoMedioPago"] = 'ZZZ';
                break; // cargo a prepago
            case "92": $doc["tipoPago"]["codigoMedioPago"] = 'ZZZ';
                break; // cargo afiliados
        }
        $doc["tipoPago"]["fechaVencimiento"] = \funcionesGenerales::mostrarFechaDDMMYYYY($rec["fecha"]);
        $doc["tipoPago"]["idDian"] = '1';

        // ************************************************ //
        // Data del adquiriente
        // ************************************************ //
        $doc["adquiriente"] = array();
        $doc["adquiriente"]["codDocumentoDian"] = \funcionesGenerales::homologarDianTipoidentificacion($rec["tipoidentificacion"]);
        if ($rec["tipoidentificacion"] == '2') {
            $sepIde = \funcionesGenerales::separarDv($rec["identificacion"]);
            $nide = $sepIde["identificacion"];
            $dv = $sepIde["dv"];
            $nat = '1'; // Juridicas
            $rf = 'R-99-PJ';
            $nr = $rec["razonsocial"];
            $cr = '48';
        } else {
            $nide = $rec["identificacion"];
            $dv = null;
            $nat = '2'; // Naturales
            $rf = 'R-99-PN';
            $nr = null;
            $cr = '49';
        }

        //
        if ($nide == '222222222222') {
            $nat = '3';
        }

        //
        if ($rec["tipoidentificacion"] == '2') {
            $doc["adquiriente"]["razonSocial"] = $rec["razonsocial"];
        } else {
            $doc["adquiriente"]["razonSocial"] = $rec["apellido1"];
            if (trim($rec["apellido2"]) != '') {
                $doc["adquiriente"]["razonSocial"] .= ' ' . $rec["apellido2"];
            }
            if (trim($rec["nombre1"]) != '') {
                $doc["adquiriente"]["razonSocial"] .= ' ' . $rec["nombre1"];
            }
            if (trim($rec["nombre2"]) != '') {
                $doc["adquiriente"]["razonSocial"] .= ' ' . $rec["nombre2"];
            }
        }

        //
        $doc["adquiriente"]["numeroIdentificacion"] = $nide;
        $doc["adquiriente"]["dv"] = $dv;
        $doc["adquiriente"]["razonSocial"] = $rec["razonsocial"];
        $doc["adquiriente"]["nombreRegistrado"] = $nr;
        $doc["adquiriente"]["primerNombre"] = $rec["nombre1"];
        if ($rec["nombre2"] == '') {
            $rec["nombre2"] = null;
        }
        $doc["adquiriente"]["segundoNombre"] = $rec["nombre2"];
        $doc["adquiriente"]["primerApellido"] = $rec["apellido1"];
        if ($rec["apellido2"] == '') {
            $rec["apellido2"] = null;
        }
        $doc["adquiriente"]["segundoApellido"] = $rec["apellido2"];
        $doc["adquiriente"]["particula"] = null;
        $doc["adquiriente"]["cont"] = null;

        if ($nide == '222222222222') {
            $doc["adquiriente"]["razonSocial"] = 'CLIENTE FINAL GENERICO';
            $doc["adquiriente"]["nombreRegistrado"] = null;
            $doc["adquiriente"]["primerNombre"] = null;
            $doc["adquiriente"]["segundoNombre"] = null;
            $doc["adquiriente"]["primerApellido"] = null;
            $doc["adquiriente"]["segundoApellido"] = null;
            $doc["adquiriente"]["particula"] = null;
            $doc["adquiriente"]["cont"] = null;
            $rec["email"] = CFE_EMAIL_CLIENTE_FINAL;
            $rec["telefono1"] = CFE_FACTURADOR_TEL1;
            $rec["telefono2"] = CFE_FACTURADOR_TEL2;
            $rec["codposcom"] = CFE_FACTURADOR_CODPOSCOM;
            $rec["codposnot"] = CFE_FACTURADOR_CODPOSNOT;
            $rec["municipio"] = CFE_FACTURADOR_MUNCOM;
            $rec["direccion"] = CFE_FACTURADOR_DIRCOM;
            $rec["municipionot"] = CFE_FACTURADOR_MUNNOT;
            $rec["direccionnot"] = CFE_FACTURADOR_DIRNOT;
        }

        if ($rec["telefono1"] == '') {
            $rec["telefono1"] = null;
        }
        if ($rec["telefono2"] == '') {
            $rec["telefono2"] = null;
        }
        if ($rec["email"] == '') {
            $rec["email"] = null;
        }
        if (!isset($rec["codposcom"]) || $rec["codposcom"] == '') {
            $rec["codposcom"] = null;
        }
        if (!isset($rec["codposnot"]) || $rec["codposnot"] == '') {
            $rec["codposnot"] = null;
        }

        //
        if ($rec["codposcom"] != null && $rec["codposcom"] != '' && $nide != '222222222222') {
            $zonas = retornarRegistroMysqliApi($mysqli, 'bas_codigos_postales', "municipio='" . $rec["municipio"] . "' and codigopostal='" . $rec["codposcom"] . "'", "id");
            if ($zonas === false || empty($zonas)) {
                $rec["codposcom"] = null;
            }
        }

        //
        if ($rec["codposnot"] != null && $rec["codposnot"] != '' && $nide != '222222222222') {
            $zonas = retornarRegistroMysqliApi($mysqli, 'bas_codigos_postales', "municipio='" . $rec["municipionot"] . "' and codigopostal='" . $rec["codposnot"] . "'", "id");
            if ($zonas === false || empty($zonas)) {
                $rec["codposnot"] = null;
            }
        }

        //
        if ($rec["codposcom"] == null && $nide != '222222222222') {
            $zonas = retornarRegistrosMysqliApi($mysqli, 'bas_codigos_postales', "municipio='" . $rec["municipio"] . "'", "id");
            if ($zonas && !empty($zonas)) {
                foreach ($zonas as $z) {
                    if ($z["tipo"] == 'Urbano') {
                        if ($rec["codposcom"] == '') {
                            $rec["codposcom"] = $z["codigopostal"];
                        }
                    }
                }
            }
        }

        //
        if ($rec["codposnot"] == null && $nide != '222222222222') {
            $zonas = retornarRegistrosMysqliApi($mysqli, 'bas_codigos_postales', "municipio='" . $rec["municipionot"] . "'", "id");
            if ($zonas && !empty($zonas)) {
                foreach ($zonas as $z) {
                    if ($z["tipo"] == 'Urbano') {
                        if ($rec["codposnot"] == '') {
                            $rec["codposnot"] = $z["codigopostal"];
                        }
                    }
                }
            }
        }


        $doc["adquiriente"]["telefono1"] = $rec["telefono1"];
        $doc["adquiriente"]["telefono2"] = $rec["telefono2"];
        if (TIPO_AMBIENTE == 'PRUEBAS') {
            if (!defined('CFE_EMAIL_PRUEBAS') || trim(CFE_EMAIL_PRUEBAS) == '') {
                $doc["adquiriente"]["email"] = EMAIL_NOTIFICACION_PRUEBAS;
                if (trim($doc["adquiriente"]["email"]) == '') {
                    $doc["adquiriente"]["email"] = 'jint@confecamaras.org.co';
                }
            } else {
                $doc["adquiriente"]["email"] = CFE_EMAIL_PRUEBAS;
            }
        } else {
            $doc["adquiriente"]["email"] = $rec["email"];
        }
        $doc["adquiriente"]["zonaPostal"] = null; // revisar
        $doc["adquiriente"]["respTributario"] = ""; // Responsable tributario
        $direc = array();
        $direc["codigoPais"] = "CO";
        $direc["nombrePais"] = "COLOMBIA";
        $direc["codigoLenguajePais"] = "es";
        $direc["codigoDepartamento"] = substr(sprintf("%05s", $rec["municipio"]), 0, 2);
        $direc["nombreDepartamento"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $rec["municipio"]) . "'", "departamento");
        $direc["codigoCiudad"] = sprintf("%05s", $rec["municipio"]);
        $direc["nombreCiudad"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $rec["municipio"]) . "'", "ciudad");
        $direc["direccionFisica"] = $rec["direccion"];
        $direc["codigoPostal"] = $rec["codposcom"];
        $doc["adquiriente"]["direccion"] = $direc;
        $direcf = array(); // revisar como enviar la data de notificacion judicial
        $direcf["codigoPais"] = "CO";
        $direcf["nombrePais"] = "COLOMBIA";
        $direcf["codigoLenguajePais"] = "es";
        $direcf["codigoDepartamento"] = substr(sprintf("%05s", $rec["municipionot"]), 0, 2);
        $direcf["nombreDepartamento"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $rec["municipionot"]) . "'", "departamento");
        $direcf["codigoCiudad"] = sprintf("%05s", $rec["municipionot"]);
        $direcf["nombreCiudad"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $rec["municipionot"]) . "'", "ciudad");
        $direcf["direccionFisica"] = $rec["direccionnot"];
        $direcf["codigoPostal"] = $rec["codposnot"];
        $doc["adquiriente"]["direccionFiscal"] = $direcf;
        $doc["adquiriente"]["codigoRegimen"] = $cr; // revisar
        $doc["adquiriente"]["naturaleza"] = $nat; // persona jurídica (1) o natural (2)
        $doc["adquiriente"]["codigoImpuesto"] = "01"; // revisar
        $doc["adquiriente"]["nombreImpuesto"] = "IVA"; // revisar
        $doc["adquiriente"]["responsabilidadFiscal"] = $rf; // Reponsabilidad otros como juridica o natural
        if (trim($doc["adquiriente"]["responsabilidadFiscal"]) != 'O-13' &&
                trim($doc["adquiriente"]["responsabilidadFiscal"]) != 'O-15' &&
                trim($doc["adquiriente"]["responsabilidadFiscal"]) != 'O-23' &&
                trim($doc["adquiriente"]["responsabilidadFiscal"]) != 'O-47' &&
                trim($doc["adquiriente"]["responsabilidadFiscal"]) != 'R-99-PN'
        ) {
            $doc["adquiriente"]["responsabilidadFiscal"] = 'R-99-PN';
        }

        //
        // ****************************************************************************** //
        // datos del mandante
        // ****************************************************************************** //
        if ($rec["numerointernorue"] != '') {
            $doc["identificacionMandante"] = array();
            $camManda = retornarRegistroMysqliApi($mysqli, 'datos_empresas', "camara='" . substr($rec["numerointernorue"], 19, 2) . "'");
            if ($camManda === false || empty($camManda)) {
                $salida["codigoError"] = '9999';
                $salida["mensajeError"] = 'Mandante : ' . substr($rec["numerointernorue"], 19, 2) . ' no almacenado en datos_empresa';
                return $salida;
            }
            $sepIde = \funcionesGenerales::separarDv($camManda["identificacion"]);
            if (!isset($camManda["dv"]) || trim($camManda["dv"]) == '') {
                $camManda["dv"] = null;
            }
            if (!isset($camManda["razonsocial"]) || trim($camManda["razonsocial"]) == '') {
                $camManda["razonsocial"] = null;
            }
            if (!isset($camManda["nombreregistrado"]) || trim($camManda["nombreregistrado"]) == '') {
                $camManda["nombreregistrado"] = null;
            }
            if (!isset($camManda["telefono1"]) || trim($camManda["telefono1"]) == '') {
                $camManda["telefono1"] = null;
            }
            if (!isset($camManda["telefono2"]) || trim($camManda["telefono2"]) == '') {
                $camManda["telefono2"] = null;
            }
            if (!isset($camManda["email"]) || trim($camManda["email"]) == '') {
                $camManda["email"] = null;
            }
            if (!isset($camManda["zonapostal"]) || trim($camManda["zonapostal"]) == '') {
                $camManda["zonapostal"] = null;
            }
            if (!isset($camManda["codposcom"]) || trim($camManda["codposcom"]) == '') {
                $camManda["codposcom"] = null;
            }
            if (!isset($camManda["codposnot"]) || trim($camManda["codposnot"]) == '') {
                $camManda["codposnot"] = null;
            }
            if (!isset($camManda["muncom"]) || trim($camManda["muncom"]) == '') {
                $camManda["muncom"] = null;
            }
            if (!isset($camManda["dircom"]) || trim($camManda["dircom"]) == '') {
                $camManda["dircom"] = null;
            }
            if (!isset($camManda["dirnot"]) || trim($camManda["dirnot"]) == '') {
                $camManda["dirnot"] = null;
            }
            if (!isset($camManda["codigoregimen"]) || trim($camManda["codigoregimen"]) == '') {
                $camManda["codigoregimen"] = null;
            }
            $doc["identificacionMandante"]["codDocumentoDian"] = "31";
            $doc["identificacionMandante"]["numeroIdentificacion"] = $sepIde["identificacion"];
            $doc["identificacionMandante"]["dv"] = $sepIde["dv"];
            $doc["identificacionMandante"]["razonSocial"] = $camManda["razonsocial"];
            $doc["identificacionMandante"]["nombreRegistrado"] = $camManda["nombreregistrado"];
            $doc["identificacionMandante"]["primerNombre"] = null;
            $doc["identificacionMandante"]["segundoNombre"] = null;
            $doc["identificacionMandante"]["primerApellido"] = null;
            $doc["identificacionMandante"]["segundoApellido"] = null;
            $doc["identificacionMandante"]["particula"] = null;
            $doc["identificacionMandante"]["cont"] = null;
            $doc["identificacionMandante"]["telefono1"] = $camManda["telefono1"];
            $doc["identificacionMandante"]["telefono2"] = $camManda["telefono2"];
            $doc["identificacionMandante"]["email"] = $camManda["email"];
            $doc["identificacionMandante"]["zonaPostal"] = $camManda["zonapostal"];
            $direc = array();
            $direc["codigoPais"] = "CO";
            $direc["nombrePais"] = "COLOMBIA";
            $direc["codigoLenguajePais"] = "es";
            $direc["codigoDepartamento"] = substr(sprintf("%05s", $camManda["muncom"]), 0, 2);
            $direc["nombreDepartamento"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $camManda["muncom"]) . "'", "departamento");
            $direc["codigoCiudad"] = sprintf("%05s", $camManda["muncom"]);
            $direc["nombreCiudad"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $camManda["muncom"]) . "'", "ciudad");
            $direc["direccionFisica"] = $camManda["dircom"];
            $direc["codigoPostal"] = $camManda["codposcom"];
            $doc["identificacionMandante"]["direccion"] = $direc;
            $direc = array();
            $direc["codigoPais"] = "CO";
            $direc["nombrePais"] = "COLOMBIA";
            $direc["codigoLenguajePais"] = "es";
            $direc["codigoDepartamento"] = substr(sprintf("%05s", $camManda["munnot"]), 0, 2);
            $direc["nombreDepartamento"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $camManda["munnot"]) . "'", "departamento");
            $direc["codigoCiudad"] = sprintf("%05s", $camManda["munnot"]);
            $direc["nombreCiudad"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $camManda["munnot"]) . "'", "ciudad");
            $direc["direccionFisica"] = $camManda["dirnot"];
            $direc["codigoPostal"] = $camManda["codposnot"];
            $doc["identificacionMandante"]["direccionFiscal"] = $direc;
            $doc["identificacionMandante"]["codigoRegimen"] = $camManda["codigoregimen"];
            $doc["identificacionMandante"]["naturaleza"] = '1';
            $doc["identificacionMandante"]["responsabilidadFiscal"] = $camManda["responsabilidadfiscal"];
            if (trim($doc["identificacionMandante"]["responsabilidadFiscal"]) != 'O-13' &&
                    trim($doc["identificacionMandante"]["responsabilidadFiscal"]) != 'O-15' &&
                    trim($doc["identificacionMandante"]["responsabilidadFiscal"]) != 'O-23' &&
                    trim($doc["identificacionMandante"]["responsabilidadFiscal"]) != 'O-47' &&
                    trim($doc["identificacionMandante"]["responsabilidadFiscal"]) != 'R-99-PN'
            ) {
                $doc["identificacionMandante"]["responsabilidadFiscal"] = 'R-99-PN';
            }
            $doc["identificacionMandante"]["codigoImpuesto"] = $camManda["codigoimpuesto"];
            $doc["identificacionMandante"]["nombreImpuesto"] = $camManda["nombreimpuesto"];
            $doc["identificacionMandante"]["respTributario"] = $camManda["responsabilidadtributaria"];
        }

        // ****************************************************************************** //
        // datos del facturador
        // ****************************************************************************** //
        $doc["facturador"] = array();
        if (defined('CFE_FACTURADOR_RAZONSOCIAL') && CFE_FACTURADOR_RAZONSOCIAL != '') {
            $camFactu = array();
            $camFactu["identificacion"] = str_replace(array(".", ",", "-", " "), "", NIT);
            $camFactu["razonsocial"] = CFE_FACTURADOR_RAZONSOCIAL;
            $camFactu["nombreregistrado"] = CFE_FACTURADOR_NOMBREREGISTRADO;
            $camFactu["telefono1"] = CFE_FACTURADOR_TEL1;
            $camFactu["telefono2"] = CFE_FACTURADOR_TEL2;
            $camFactu["email"] = CFE_FACTURADOR_EMAIL;
            $camFactu["muncom"] = CFE_FACTURADOR_MUNCOM;
            $camFactu["dircom"] = CFE_FACTURADOR_DIRCOM;
            $camFactu["codposcom"] = CFE_FACTURADOR_CODPOSCOM;
            $camFactu["munnot"] = CFE_FACTURADOR_MUNNOT;
            $camFactu["dirnot"] = CFE_FACTURADOR_DIRNOT;
            $camFactu["codposnot"] = CFE_FACTURADOR_CODPOSNOT;
            $camFactu["codigoregimen"] = CFE_FACTURADOR_CODIGOREGIMEN;
            $camFactu["responsabilidadfiscal"] = CFE_FACTURADOR_RESPOFISCAL;
            $camFactu["codigoimpuesto"] = CFE_FACTURADOR_CODIGOIMPUESTO;
            $camFactu["nombreimpuesto"] = CFE_FACTURADOR_CODIGOIMPUESTONOMBRE;
            $camFactu["responsabilidadtributaria"] = CFE_FACTURADOR_RESPOTRIBUTARIO;
        } else {
            $camFactu = retornarRegistroMysqliApi($mysqli, 'datos_empresas', "camara='" . CODIGO_EMPRESA . "'");
        }
        if ($camFactu === false || empty($camFactu)) {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador : ' . CODIGO_EMPRESA . ' no almacenado en datos_empresa';
            return $salida;
        }

        //
        if ($camFactu["razonsocial"] == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador sin razonsocial en datos_empresas';
            return $salida;
        }
        if ($camFactu["telefono1"] == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador sin telefono1 en datos_empresas';
            return $salida;
        }
        if ($camFactu["email"] == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador sin email en datos_empresas';
            return $salida;
        }
        if ($camFactu["muncom"] == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador sin muncom en datos_empresas';
            return $salida;
        }
        if ($camFactu["dircom"] == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador sin dircom en datos_empresas';
            return $salida;
        }
        if ($camFactu["codigoregimen"] == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador sin codigoregimen en datos_empresas';
            return $salida;
        }
        if ($camFactu["responsabilidadfiscal"] == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador sin responsabilidadfiscal en datos_empresas';
            return $salida;
        }
        if ($camFactu["codigoimpuesto"] == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador sin codigoImpuesto en datos_empresas';
            return $salida;
        }
        if ($camFactu["nombreimpuesto"] == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador sin nombreImpuesto en datos_empresas';
            return $salida;
        }

        //
        $sepIde = \funcionesGenerales::separarDv($camFactu["identificacion"]);
        if (!isset($camFactu["razonsocial"]) || trim($camFactu["razonsocial"]) == '') {
            $camFactu["razonsocial"] = null;
        }
        if (!isset($camFactu["nombreregistrado"]) || trim($camFactu["nombreregistrado"]) == '') {
            $camFactu["nombreregistrado"] = null;
        }
        if (!isset($camFactu["telefono1"]) || trim($camFactu["telefono1"]) == '') {
            $camFactu["telefono1"] = null;
        }
        if (!isset($camFactu["telefono2"]) || trim($camFactu["telefono2"]) == '') {
            $camFactu["telefono2"] = null;
        }
        if (!isset($camFactu["email"]) || trim($camFactu["email"]) == '') {
            $camFactu["email"] = null;
        }
        if (!isset($camFactu["zonapostal"]) || trim($camFactu["zonapostal"]) == '') {
            $camFactu["zonapostal"] = null;
        }
        if (!isset($camFactu["codposcom"]) || trim($camFactu["codposcom"]) == '') {
            $camFactu["codposcom"] = null;
        }
        if (!isset($camFactu["codposnot"]) || trim($camFactu["codposnot"]) == '') {
            $camFactu["codposnot"] = null;
        }
        if (!isset($camFactu["muncom"]) || trim($camFactu["muncom"]) == '') {
            $camFactu["muncom"] = null;
        }
        if (!isset($camFactu["dircom"]) || trim($camFactu["dircom"]) == '') {
            $camFactu["dircom"] = null;
        }
        if (!isset($camFactu["dirnot"]) || trim($camFactu["dirnot"]) == '') {
            $camFactu["dirnot"] = null;
        }
        if (!isset($camFactu["codigoregimen"]) || trim($camFactu["codigoregimen"]) == '') {
            $camFactu["codigoregimen"] = null;
        }
        $doc["facturador"]["codDocumentoDian"] = "31";
        $doc["facturador"]["numeroIdentificacion"] = $sepIde["identificacion"];
        $doc["facturador"]["dv"] = $sepIde["dv"];
        $doc["facturador"]["razonSocial"] = $camFactu["razonsocial"];
        if (trim($camFactu["nombreregistrado"]) == '') {
            $camFactu["nombreregistrado"] = $camFactu["razonsocial"];
        }
        $doc["facturador"]["nombreRegistrado"] = $camFactu["nombreregistrado"];
        $doc["facturador"]["primerNombre"] = null;
        $doc["facturador"]["segundoNombre"] = null;
        $doc["facturador"]["primerApellido"] = null;
        $doc["facturador"]["segundoApellido"] = null;
        $doc["facturador"]["particula"] = null;
        $doc["facturador"]["cont"] = null;

        $doc["facturador"]["telefono1"] = $camFactu["telefono1"];
        $doc["facturador"]["telefono2"] = $camFactu["telefono2"];
        $doc["facturador"]["email"] = $camFactu["email"];
        if (trim($camFactu["zonapostal"]) == '') {
            $camFactu["zonapostal"] = '000000';
        }
        $doc["facturador"]["zonaPostal"] = $camFactu["zonapostal"];
        $direc = array();
        $direc["codigoPais"] = "CO";
        $direc["nombrePais"] = "COLOMBIA";
        $direc["codigoLenguajePais"] = "es";
        $direc["codigoDepartamento"] = substr(sprintf("%05s", $camFactu["muncom"]), 0, 2);
        $direc["nombreDepartamento"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $camFactu["muncom"]) . "'", "departamento");
        $direc["codigoCiudad"] = sprintf("%05s", $camFactu["muncom"]);
        $direc["nombreCiudad"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $camFactu["muncom"]) . "'", "ciudad");
        $direc["direccionFisica"] = $camFactu["dircom"];
        $direc["codigoPostal"] = $camFactu["codposcom"];
        $doc["facturador"]["direccion"] = $direc;
        $direc = array();
        $direc["codigoPais"] = "CO";
        $direc["nombrePais"] = "COLOMBIA";
        $direc["codigoLenguajePais"] = "es";
        $direc["codigoDepartamento"] = substr(sprintf("%05s", $camFactu["munnot"]), 0, 2);
        $direc["nombreDepartamento"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $camFactu["munnot"]) . "'", "departamento");
        $direc["codigoCiudad"] = sprintf("%05s", $camFactu["munnot"]);
        $direc["nombreCiudad"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $camFactu["munnot"]) . "'", "ciudad");
        $direc["direccionFisica"] = $camFactu["dirnot"];
        $direc["codigoPostal"] = $camFactu["codposnot"];
        $doc["facturador"]["direccionFiscal"] = $direc;
        $doc["facturador"]["codigoRegimen"] = $camFactu["codigoregimen"];
        $doc["facturador"]["naturaleza"] = '1';
        $doc["facturador"]["responsabilidadFiscal"] = $camFactu["responsabilidadfiscal"];
        if (trim($doc["facturador"]["responsabilidadFiscal"]) != 'O-13' &&
                trim($doc["facturador"]["responsabilidadFiscal"]) != 'O-15' &&
                trim($doc["facturador"]["responsabilidadFiscal"]) != 'O-23' &&
                trim($doc["facturador"]["responsabilidadFiscal"]) != 'O-47' &&
                trim($doc["facturador"]["responsabilidadFiscal"]) != 'R-99-PN'
        ) {
            $doc["facturador"]["responsabilidadFiscal"] = 'R-99-PN';
        }
        $doc["facturador"]["codigoImpuesto"] = $camFactu["codigoimpuesto"];
        $doc["facturador"]["nombreImpuesto"] = $camFactu["nombreimpuesto"];
        $doc["facturador"]["respTributario"] = $camFactu["responsabilidadtributaria"];

        // ****************************************************************************** //
        // Datos finales del documento
        // ****************************************************************************** //
        $doc["numeroFacturaGenerado"] = null;
        $doc["nroResolucion"] = null;
        $doc["fechaInicialResolucion"] = null;
        $doc["fechaFinalResolucion"] = null;
        $doc["nroInicialResolucion"] = null;
        $doc["nroFinalResolucion"] = null;
        $doc["consecutivoResolucion"] = null;
        $doc["horaInicioResolucion"] = null;
        $doc["horaFinResolucion"] = null;

        // ****************************************************************************** //
        // Imagen  y datos de la factura si esta se envía pre-generada
        // ****************************************************************************** //
        $doc["base64"] = null;
        $doc["urlAnexos"] = null;
        $doc["posicionXCufe"] = 35;
        $doc["posicionYCufe"] = 50;
        $doc["rotacionCufe"] = 0;
        $doc["fuenteCufe"] = 8;
        $doc["posicionXQr"] = 125;
        $doc["posicionYQr"] = 265;

        //
        $doc["redondeo"] = number_format($redondeos, 2, ".", "");
        $doc["subtotal"] = number_format($bruto, 2, ".", "");
        $doc["iva"] = number_format($valivas, 2, ".", "");
        $doc["total"] = number_format($neto, 2, ".", "");
        $doc["totalDescuentos"] = number_format($descuentos, 2, ".", "");
        $doc["totalBaseImponible"] = number_format($baseimponible, 2, ".", "");
        $doc["totalCargos"] = number_format($cargos, 2, ".", "");
        $doc["subtotalMasTributos"] = number_format(($bruto + $valivas), 2, ".", "");
        $doc["montoscrito"] = \funcionesGenerales::montoEscrito($neto);

        //
        $arrJson["documentos"][] = $doc;

        // ****************************************************************************** //
        // Encuentra número del lote
        // ****************************************************************************** //        
        $arrJson["nroLote"] = \funcionesGenerales::retornarSecuencia($mysqli, 'LOTE-CFE');

        // ****************************************************************************** //
        // Pasa el arreglo a json
        // ****************************************************************************** //        
        $salida["json"] = json_encode($arrJson);

        // ****************************************************************************** //
        // Almacena el json que se enviará al CFE
        // ****************************************************************************** //
        if (!is_dir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"])) {
            mkdir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"], 0777);
        }

        if (!is_dir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg')) {
            mkdir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg', 0777);
        }
        if (!is_dir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/cfe_json')) {
            mkdir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/cfe_json', 0777);
        }
        if (!is_dir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/cfe_json/' . $rec["fecha"])) {
            mkdir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/cfe_json/' . $rec["fecha"], 0777);
        }
        $n = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/cfe_json/' . $rec["fecha"] . '/' . $rec["recibo"] . '-request.json';
        $f = fopen($n, "w");
        fwrite($f, $salida["json"]);
        fclose($f);

        // ****************************************************************************** //
        // Consume el componente CFE
        // ****************************************************************************** //
        if (defined('CFE_URL_API') && trim(CFE_URL_API) != '') {
            $url = CFE_URL_API;
        } else {
            $url = 'http://facturaelectronica.aspsols.com/facturador/recibir-docs/';
        }

        //
        $headers = array(
            'Content-Type:application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        // curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $salida["json"]);
        $result = curl_exec($ch);
        curl_close($ch);

        //
        $nameLog1 = 'procesarNotasCreditoCFE_envios_' . date("Ymd");
        \logApi::general2($nameLog1, $rec["recibo"], 'Enviado al CFE - Respuesta del CFE: ' . $result);

        // ****************************************************************************** //
        // Almacena la respuesta del componente CFE
        // ****************************************************************************** //
        $n = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/cfe_json/' . $rec["fecha"] . '/' . $rec["recibo"] . '-response.json';
        $f = fopen($n, "w");
        fwrite($f, $result);
        fclose($f);
        $salida["jsonresponse"] = $result;

        // ****************************************************************************** //
        // Almacena tabla mreg_recibosgenerados_json_cfe
        // ****************************************************************************** //
        $response = json_decode($result, true);
        if (!isset($response[0]["status"])) {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'No se recibió una respuesta válida desde el CFE (1)';
            return $salida;
        }
        if (!isset($response[0]["documentoRecibido"])) {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'No se recibió una respuesta válida desde el CFE (2)';
            return $salida;
        }
        if ($response[0]["documentoRecibido"] != $rec["recibo"]) {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Documento recibido desde el CFE (' . $response[0]["documentoRecibido"] . ') no corresponde al enviado (' . $rec["recibo"] . ')';
            return $salida;
        }

        //
        $status = $response[0]["status"];
        $cufe = '';
        $cude = '';
        $estadoDoc = '';
        $observaciones = '';
        if (isset($response[0]["cufe"])) {
            $cufe = $response[0]["cufe"];
        }
        if (isset($response[0]["cude"])) {
            $cude = $response[0]["cude"];
        }
        if (isset($response[0]["estadoDoc"])) {
            $estadoDoc = $response[0]["estadoDoc"];
        }
        if (isset($response[0]["message"])) {
            $observaciones = $response[0]["message"];
        }
        if (isset($response[0]["errorMessages"]) && !empty($response[0]["errorMessages"])) {
            foreach ($response[0]["errorMessages"] as $msg) {
                $observaciones .= "\r\n" . $msg["message"];
            }
        }

        //
        $arrCampos = array(
            'recibo',
            'fechahoraenvio',
            'json',
            'jsonresponse',
            'status',
            'cufe',
            'cude',
            'estadodoc',
            'observaciones'
        );
        $arrValores = array(
            "'" . $rec["recibo"] . "'",
            "'" . date("Ymd") . ' ' . date("His") . "'",
            "'" . addslashes($salida["json"]) . "'",
            "'" . addslashes($salida["jsonresponse"]) . "'",
            "'" . $status . "'",
            "'" . $cufe . "'",
            "'" . $cude . "'",
            "'" . $estadoDoc . "'",
            "'" . addslashes($observaciones) . "'"
        );
        insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_json_cfe', $arrCampos, $arrValores);

        //
        $arrCampos = array(
            'recibo',
            'fecha',
            'hora',
            'estado',
            'prefijofactura',
            'nrofactura',
            'fechafactura',
            'prefijonotacredito',
            'nronotacredito',
            'fechanotacredito',
            'cufe',
            'cude',
            'jsonrequest',
            'jsonresponse',
            'xml',
            'observaciones'
        );
        $arrValores = array(
            "'" . $rec["recibo"] . "'",
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . $status . "'",
            "''",
            "''",
            "''",
            "''",
            "''",
            "''",
            "'" . $cufe . "'",
            "'" . $cude . "'",
            "'" . addslashes($salida["json"]) . "'",
            "'" . addslashes($salida["jsonresponse"]) . "'",
            "''",
            "'" . addslashes($observaciones) . "'"
        );
        insertarRegistrosMysqliApi($mysqli, 'mreg_cfe_log', $arrCampos, $arrValores);

        // ****************************************************************************** //
        // Retorna la salida 
        // ****************************************************************************** //        
        if ($status != 'OK') {
            $salida["codigoError"] = '9999';
            $salida["status"] = $status;
            $salida["mensajeError"] = $observaciones;
        }

        //
        return $salida;
    }

    public static function procesarRecibosCFE($mysqli, $rec, $dets) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $nameLog = 'procesarRecibosCFE_' . date("Ymd");

        $salida = array();
        $salida["codigoError"] = '0000';
        $salida["mensajeError"] = '';
        $salida["status"] = '';
        $salida["json"] = '';
        $salida["jsonresponse"] = '';

        //
        if (substr($rec["recibo"], 0, 1) == 'M') {
            $salida["codigoError"] = '0001';
            $salida["mensajeError"] = 'Pendiente de procesar - notas de reversión';
            return $salida;
        }

        //
        $bruto = 0;
        $base = 0;
        $baseimponible = 0;
        $cargos = 0;
        $redondeos = 0;
        $valivas = 0;
        $iva = 0;
        $piva = 0;
        $ivacalculado = 0;
        $cantidadivas = 0;
        $neto = 0;
        $descuentos = 0;
        $cantidescuentos = 0;
        $iLin = 0;
        $txtServicio = '';

        $arrIvas = array();

        //
        $txtx = '';

        foreach ($dets as $dt) {
            $iLin++;
            if ($iLin == 1) {
                $txtServicio = $dt["nombre"];
            }

            if ($dt["valorservicio"] >= 0) {
                if ($dt["esiva"] == 'si') {
                    $iva = $iva + $dt["valorservicio"];
                    $piva = 19;
                    $cantidadivas++;
                    $aiva = array();
                    $aiva["base"] = $dt["valorbase"];
                    $aiva["porcentaje"] = $dt["porcentaje"];
                    $aiva["valor"] = $dt["valorservicio"];
                    $arrIvas[] = $aiva;
                } else {
                    $bruto = $bruto + $dt["valorservicio"];
                    if ($dt["gravadoiva"] == 'si') {
                        $baseimponible = $baseimponible + $dt["valorservicio"];
                        $base = $base + $dt["valorservicio"];
                        $ivacalculado = $ivacalculado + round($dt["valorservicio"] * 0.19, 0);
                        $piva = 19;
                    }
                }
            }

            //
            $neto = $neto + $dt["valorservicio"];

            $txtx .= $iLin . '.)  Servicio: ' . $dt["idservicio"] . ' ' . $dt["nombre"] . ', EsIva : ' . $dt["esiva"] . ', EsGravadoIva : ' . $dt["gravadoiva"] . ', Valor: ' . $dt["valorservicio"] . "\r\n";
            // \logApi::general2($nameLog, $rec["recibo"], $tlin);
        }
        \logApi::general2($nameLog, $rec["recibo"], $txtx);

        //
        if (abs($iva) - abs($ivacalculado) > 2) {
            \logApi::general2($nameLog, $rec["recibo"], 'Error en el IVA calculado ' . $iva . ' - ' . $ivacalculado);
            $salida["codigoError"] = '0001';
            $salida["mensajeError"] = 'Error en el IVA aplicado (' . $iva . ') vs el IVA calculado (' . $ivacalculado . ')';
            return $salida;
        } else {
            \logApi::general2($nameLog, $rec["recibo"], 'Comparativo IVA ' . $iva . ' - ' . $ivacalculado);
        }

        //
        $arrJson = array();
        $arrJson["nroLote"] = 0;
        $arrJson["fecha"] = date("Y-m-d");
        $arrJson["documentos"] = array();

        //
        $doc = array();
        $doc["codigoSiiCamara"] = CODIGO_EMPRESA;
        $doc["identificadorCamara"] = null;
        $doc["codigoSiiSucursal"] = substr($rec["operacion"], 0, 2);
        $doc["sucursal"] = null;
        $doc["usuariosii"] = $rec["usuario"];
        $doc["usuarioSii"] = $rec["usuario"];
        $doc["usuarioExpide"] = '';
        if ($rec["usuario"] == 'USUPUBXX') {
            $doc["usuarioExpide"] = 'PROCESOS VIRTUALES';
        } else {
            $doc["usuarioExpide"] = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $rec["usuario"] . "'", "nombreusuario");
        }
        $doc["usuarioAprueba"] = '';
        if (defined('CFE_FACTURADOR_NOMBRE_APROBADOR') && CFE_FACTURADOR_NOMBRE_APROBADOR != '') {
            $doc["usuarioAprueba"] = CFE_FACTURADOR_NOMBRE_APROBADOR;
        }
        $doc["tipoNumeracion"] = "1"; // Recibo normal
        $doc["nroDocumento"] = $rec["recibo"];
        $doc["codigoMoneda"] = "COP";
        $doc["descripcion"] = $txtServicio;
        if ($rec["fechaexpedicionfactura"] != '') {
            $doc["fechaExpedicion"] = \funcionesGenerales::mostrarFechaDDMMYYYY($rec["fechaexpedicionfactura"]);
            $doc["horaExpedicion"] = \funcionesGenerales::mostrarHora($rec["hora"]);
            $doc["fechaVencimiento"] = \funcionesGenerales::mostrarFechaDDMMYYYY($rec["fechaexpedicionfactura"]);
        } else {
            $doc["fechaExpedicion"] = \funcionesGenerales::mostrarFechaDDMMYYYY($rec["fecha"]);
            $doc["horaExpedicion"] = \funcionesGenerales::mostrarHora($rec["hora"]);
            $doc["fechaVencimiento"] = \funcionesGenerales::mostrarFechaDDMMYYYY($rec["fecha"]);
        }

        // ************************************************************* //
        // Servicios facturados y descuentos
        // ************************************************************* //
        $doc["docRecibidoProductos"] = array();
        $doc["listaCargosDescuentos"] = array();
        $iDesc = 0;

        //
        $erroresDescuentos = '';
        $servorigendescuentos = array();
        $servorigeniva = array();
        $bruto = 0;

        // ****************************************************** //
        // Localiza descuentos
        // ****************************************************** //
        $servdesc["idservicio"] = '';
        $servdesc["Razon"] = '';
        $servdesc["base"] = 0;
        $servdesc["porcentaje"] = 0;
        $servdesc["valor"] = 0;
        $servdesc["aplicado"] = '';
        foreach ($dets as $dt) {
            if ($dt["valorservicio"] < 0 && ($dt["idservicio"] < '01090151' || $dt["idservicio"] > '01090160')) {
                $cantidescuentos++;
                $servdesc["idservicio"] = $dt["idservicio"];
                $servdesc["Razon"] = $dt["nombre"];
                $servdesc["base"] = 0;
                $servdesc["porcentaje"] = 0;
                $servdesc["valor"] = abs($dt["valorservicio"]);
                $servdesc["aplicado"] = 'pe';
                if ($dt["idservicio"] == '01090110') {
                    $servdesc["base"] = abs($dt["valorservicio"]);
                    $servdesc["porcentaje"] = 100;
                }
                if ($dt["idservicio"] == '01090111') {
                    $servdesc["base"] = abs($dt["valorservicio"]);
                    $servdesc["porcentaje"] = 100;
                }
            }
        }

        // ****************************************************** //
        // Localiza impuestos
        // ****************************************************** //
        $servorigeniva["Razon"] = '';
        $servorigeniva["base"] = 0;
        $servorigeniva["porcentaje"] = 0;
        $servorigeniva["valor"] = 0;
        $servorigeniva["aplicado"] = '';
        foreach ($dets as $dt) {
            if ($dt["valorservicio"] > 0 && $dt["esiva"] == 'si') {
                $servorigeniva["porcentaje"] = 19;
                $servorigeniva["valor"] = $servorigeniva["valor"] + $dt["valorservicio"];
                $servorigeniva["aplicado"] = 'pe';
                $servorigeniva["Razon"] = $dt["nombre"];
            } else {
                if ($dt["valorservicio"] > 0 && $dt["gravadoiva"] == 'si') {
                    $servorigeniva["base"] = $servorigeniva["base"] + $dt["valorservicio"];
                }
            }
        }


        // ****************************************************** //
        // Procesa servicios
        // ****************************************************** //
        $linprod = 0;
        foreach ($dets as $dt) {
            if ($dt["valorservicio"] > 0) {
                if ($dt["esiva"] != 'si') {

                    //
                    $prd = array();
                    if ($dt["cantidad"] == 0) {
                        $dt["cantidad"] = 1;
                    }

                    //
                    $prd["numeroLinea"] = $linprod;
                    $prd["cantidad"] = number_format($dt["cantidad"], 2, '.', '');
                    $prd["descripcion"] = $dt["nombre"];
                    if (ltrim((string) $dt["conceptodepartamental"], "0") == '') {
                        $prd["cargo"] = 'CAM';
                    } else {
                        $prd["cargo"] = 'DEP';
                    }
                    $prd["identificador"] = null;
                    $prd["imprimible"] = true;
                    $prd["codigoPrecio"] = '01';
                    $prd["codigoUnidad"] = '94'; // Unidad
                    $prd["esMuestraComercial"] = false;
                    $prd["idProducto"] = $dt["idservicio"];
                    $prd["valorTotal"] = number_format($dt["valorservicio"], 2, '.', '');
                    $valortotalproducto = $dt["valorservicio"];
                    $sumarbruto = $dt["valorservicio"];
                    $prd["pagable"] = true;
                    $prd["descripcion"] = $dt["nombre"] . ' (' . $dt["cantidad"] . ')';
                    $prd["valorUnitario"] = number_format($dt["valorservicio"], 2, ".", "");
                    $prd["cantidad"] = number_format(1, 2, ".", "");

                    //
                    $prd["impuestos"] = array();
                    if ($dt["gravadoiva"] == 'si') {

                        $imps = array();
                        $imps["baseGravable"] = $dt["valorservicio"];
                        $imps["nombre"] = 'IVA';
                        $imps["valor"] = round($dt["valorservicio"] * 19 / 100, 0);
                        $imps["codigo"] = '01';
                        $imps["porcentaje"] = 19;
                        $imps["valorPorUnidad"] = $dt["valorservicio"] / $dt["cantidad"];
                        $prd["impuestos"][] = $imps;
                        $valivas = $valivas + $imps["valor"];

                        /*
                          if ($cantidadivas == 1) {

                          if ($servorigeniva["aplicado"] == 'pe') {
                          $imps = array();
                          $imps["baseGravable"] = $servorigeniva["base"];
                          $imps["nombre"] = $servorigeniva["Razon"];
                          $imps["valor"] = number_format($servorigeniva["valor"], 2, '.', '');
                          $imps["codigo"] = '01';
                          $imps["porcentaje"] = number_format($servorigeniva["porcentaje"], 2, '.', '');
                          $imps["valorPorUnidad"] = number_format($prd["valorUnitario"], 4, '.', '');
                          $prd["impuestos"][] = $imps;
                          $servorigeniva["aplicado"] = 'si';
                          $valivas = $valivas + $servorigeniva["valor"];
                          }
                          } else {
                          $valiva = round($dt["valorservicio"] * 0.19, 2);
                          $valivas = $valivas + $valiva;
                          $imps = array();
                          $imps["baseGravable"] = $dt["valorservicio"];
                          $imps["nombre"] = 'IVA';
                          $imps["valor"] = number_format($valiva, 2, '.', '');
                          $imps["codigo"] = '01';
                          $imps["porcentaje"] = 19.00;
                          $imps["valorPorUnidad"] = number_format($dt["valorservicio"], 2, '.', '');
                          $prd["impuestos"][] = $imps;
                          }
                         */
                    }

                    //
                    $prd["listaCargosDescuentos"] = array();

                    // Aplica descuento por beneficio de ley 1780
                    $iDesc = 0;
                    if ($servdesc["aplicado"] == 'pe') {
                        if ($servdesc["idservicio"] == '01090110') {
                            if ($dt["idservicio"] == '01020101' ||
                                    $dt["idservicio"] == '01020106' ||
                                    $dt["idservicio"] == '01020108' ||
                                    $dt["idservicio"] == '01020109' ||
                                    ($dt["idservicio"] >= '01020112' && $dt["idservicio"] <= '01020118')) {
                                $desc = array();
                                $desc["id"] = $linprod; // otros descuentos
                                $desc["esCargo"] = false; // otros descuentos
                                $desc["codigo"] = '11'; // otros descuentos 00.- No condicionado 01.- Condicionado
                                $desc["codigo"] = '00'; // 2021-08-17
                                $desc["Razon"] = $servdesc["Razon"];
                                $desc["base"] = number_format($servdesc["base"], 2, '.', '');
                                $desc["porcentaje"] = number_format($servdesc["porcentaje"], 2, '.', '');
                                $desc["valor"] = number_format($servdesc["valor"], 2, '.', '');
                                $prd["listaCargosDescuentos"][] = $desc;
                                $prd["valorTotal"] = number_format($dt["valorservicio"] - $servdesc["valor"], 2, '.', '');
                                $valortotalproducto = $dt["valorservicio"] - $servdesc["valor"];
                                $sumarbruto = $dt["valorservicio"] - $servdesc["valor"];
                                $servdesc["aplicado"] = 'si';
                            }
                        }
                        if ($servdesc["idservicio"] == '01090111') {
                            if ($dt["idservicio"] == '01020201') {
                                $desc = array();
                                $desc["id"] = $linprod; // otros descuentos
                                $desc["esCargo"] = false; // otros descuentos
                                $desc["codigo"] = '11'; // otros descuentos
                                $desc["codigo"] = '00'; // 2021-08-17
                                $desc["Razon"] = $servdesc["Razon"];
                                $desc["base"] = $servdesc["base"];
                                $desc["porcentaje"] = $servdesc["porcentaje"];
                                $desc["valor"] = $servdesc["valor"];
                                $prd["listaCargosDescuentos"][] = $desc;
                                $prd["valorTotal"] = number_format($dt["valorservicio"] - $servdesc["valor"], 2, '.', '');
                                $valortotalproducto = $dt["valorservicio"] - $servdesc["valor"];
                                $sumarbruto = $dt["valorservicio"] - $servdesc["valor"];
                                $servdesc["aplicado"] = 'si';
                            }
                        }
                    }

                    // Aplica beneficios de decreto 1756
                    if ($dt["clavecontrol"] == '') {
                        $cnt = $dt["idservicio"] . '-' . $dt["matricula"] . '-' . ltrim(trim($dt["ano"]), "0");
                    } else {
                        if (strlen($dt["clavecontrol"]) > 10) {
                            list ($sv, $mt, $an) = explode("-", $dt["clavecontrol"]);
                            $dt["clavecontrol"] = $sv . '-' . $mt . '-' . ltrim(trim($an), "0");
                        }
                        $cnt = $dt["clavecontrol"];
                    }

                    //
                    foreach ($dets as $dtx) {
                        if ($dtx["idservicio"] >= '01090151' && $dtx["idservicio"] <= '01090160') {
                            // \logApi::general2($nameLog, $rec["recibo"], 'Encontro descuento tipo ' . $dtx["idservicio"]);
                            if ($dtx["clavecontrol"] != '') {
                                if (strlen($dtx["clavecontrol"]) > 10) {
                                    list ($sv, $mt, $an) = explode("-", $dtx["clavecontrol"]);
                                    $dtx["clavecontrol"] = $sv . '-' . $mt . '-' . ltrim(trim($an), "0");
                                }
                                if ($cnt == $dtx["clavecontrol"]) {
                                    $desc = array();
                                    $desc["id"] = $linprod; // otros descuentos
                                    $desc["esCargo"] = false; // otros descuentos
                                    $desc["codigo"] = '11'; // otros descuentos
                                    $desc["codigo"] = '00'; // 2021-08-17
                                    $desc["Razon"] = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $dtx["idservicio"] . "'", "nombre");
                                    $porcdes = abs($dtx["valorservicio"]) / $dt["valorservicio"] * 100;
                                    $desc["base"] = number_format($dt["valorservicio"], 2, '.', '');
                                    $desc["porcentaje"] = number_format($porcdes, 2, '.', '');
                                    $desc["valor"] = number_format(abs($dtx["valorservicio"]), 2, '.', '');
                                    $prd["listaCargosDescuentos"][] = $desc;
                                    $prd["valorTotal"] = number_format($dt["valorservicio"] - abs($dtx["valorservicio"]), 2, '.', '');
                                    $valortotalproducto = $dt["valorservicio"] - abs($dtx["valorservicio"]);
                                    $sumarbruto = $dt["valorservicio"] - abs($dtx["valorservicio"]);
                                }
                            }
                        }
                    }

                    // Aplica otros descuentos
                    if ($servdesc["aplicado"] == 'pe') {
                        if ($servdesc["idservicio"] != '01090110' && $servdesc["idservicio"] != '01090111') {
                            if ($servdesc["valor"] >= $dt["valorservicio"]) {
                                $desc = array();
                                $desc["id"] = $linprod; // otros descuentos
                                $desc["esCargo"] = false; // otros descuentos
                                $desc["codigo"] = '11'; // otros descuentos
                                $desc["codigo"] = '00'; // 2021-08-17
                                $desc["Razon"] = $servdesc["Razon"];
                                $servdesc["valor"] = $servdesc["valor"] - $dt["valorservicio"];
                                $desc["base"] = number_format($dt["valorservicio"], 2, '.', '');
                                $desc["porcentaje"] = number_format(100, 2, '.', '');
                                $desc["valor"] = number_format($servdesc["valor"], 2, '.', '');
                                $prd["listaCargosDescuentos"][] = $desc;
                                $prd["valorTotal"] = number_format(0, 2, '.', '');
                                $valortotalproducto = 0;
                                $sumarbruto = 0;
                            } else {
                                $desc = array();
                                $desc["id"] = $linprod; // otros descuentos
                                $desc["esCargo"] = false; // otros descuentos
                                $desc["codigo"] = '11'; // otros descuentos
                                $desc["codigo"] = '00'; // 2021-08-17
                                $desc["Razon"] = $servdesc["Razon"];
                                $porcdes = $servdesc["valor"] / $dt["valorservicio"] * 100;
                                $desc["base"] = number_format($dt["valorservicio"], 2, '.', '');
                                $desc["porcentaje"] = number_format($porcdes, 2, '.', '');
                                $desc["valor"] = number_format($servdesc["valor"], 2, '.', '');
                                $prd["listaCargosDescuentos"][] = $desc;
                                $prd["valorTotal"] = number_format($dt["valorservicio"] - $servdesc["valor"], 2, '.', '');
                                $valortotalproducto = $dt["valorservicio"] - $servdesc["valor"];
                                $sumarbruto = $dt["valorservicio"] - $servdesc["valor"];
                                $servdesc["aplicado"] = 'si';
                            }
                        }
                    }

                    //
                    $prd["item"] = array();
                    $prd["item"]["codigoEstandar"] = '999';
                    $prd["item"]["descripcion"] = $dt["nombre"];
                    if ($valortotalproducto != 0) {
                        $linprod++;
                        $prd["numeroLinea"] = $linprod;
                        $doc["docRecibidoProductos"][] = $prd;
                    }
                    $bruto = $bruto + $sumarbruto;
                }
            }
        }

        //
        if ($valivas != $iva) {
            $redondeos = $iva - $valivas;
        }

        // ********************************************************* //
        // Impuestos y deducciones
        // ********************************************************* //
        $doc["subtotalesImpuestosDeduccion"] = array();
        if (!empty($arrIvas)) {
            $imp = array();
            foreach ($arrIvas as $aiva) {
                $imp["baseGravable"] = $imp["baseGravable"] + $aiva["base"];
                $imp["nombre"] = 'IVA';
                $imp["valor"] = $imp["valor"] + $aiva["valor"];
                $imp["codigo"] = '01';
                $imp["porcentaje"] = 19;
            }
            $doc["subtotalesImpuestosDeduccion"][] = $imp;
        }

        // ************************************************************* //
        // Objeto tipo de pago
        // ************************************************************* //
        $doc["tipoPago"] = array();
        $doc["tipoPago"]["metodoPago"] = '1'; // Contado
        $doc["tipoPago"]["codigoMedioPago"] = '10'; // Efectivo
        switch ($rec["idformapago"]) {
            case "01": $doc["tipoPago"]["codigoMedioPago"] = '10';
                break; // Cheque
            case "02": $doc["tipoPago"]["codigoMedioPago"] = '20';
                break; // Cheque
            case "03": $doc["tipoPago"]["codigoMedioPago"] = '49';
                break; // Tarjeta débito
            case "04": $doc["tipoPago"]["codigoMedioPago"] = '48';
                break; // tarjeta crédito
            case "05": $doc["tipoPago"]["codigoMedioPago"] = '49';
                break; // pago electrónico
            case "06": $doc["tipoPago"]["codigoMedioPago"] = '42';
                break; // consignacion
            case "07": $doc["tipoPago"]["codigoMedioPago"] = '42';
                break; // pago en bancos
            case "08": $doc["tipoPago"]["codigoMedioPago"] = '10';
                break; // ATH
            case "09": $doc["tipoPago"]["codigoMedioPago"] = '3';
                break; // ACH
            case "10": $doc["tipoPago"]["codigoMedioPago"] = '10';
                break; // Efecty
            case "11": $doc["tipoPago"]["codigoMedioPago"] = '93';
                break; // pago referneciado
            case "91": $doc["tipoPago"]["codigoMedioPago"] = 'ZZZ';
                break; // cargo a prepago
            case "92": $doc["tipoPago"]["codigoMedioPago"] = 'ZZZ';
                break; // cargo afiliados
        }
        $doc["tipoPago"]["fechaVencimiento"] = \funcionesGenerales::mostrarFechaDDMMYYYY($rec["fecha"]);
        $doc["tipoPago"]["idDian"] = '1';

        // ************************************************ //
        // Data del adquiriente
        // ************************************************ //
        $doc["adquiriente"] = array();

        $doc["adquiriente"]["codDocumentoDian"] = \funcionesGenerales::homologarDianTipoidentificacion($rec["tipoidentificacion"]);
        if ($rec["tipoidentificacion"] == '2') {
            $sepIde = \funcionesGenerales::separarDv($rec["identificacion"]);
            $nide = $sepIde["identificacion"];
            $dv = $sepIde["dv"];
            $nat = '1'; // Juridicas
            $rf = 'R-99-PN';
            $nr = $rec["razonsocial"];
            $cr = '48';
        } else {
            $nide = $rec["identificacion"];
            $dv = null;
            $nat = '2'; // Naturales
            $rf = 'R-99-PN';
            $nr = null;
            $cr = '49';
        }

        //
        if ($nide == '222222222222') {
            $nat = '3';
        }

        //
        if ($rec["tipoidentificacion"] == '2') {
            $doc["adquiriente"]["razonSocial"] = $rec["razonsocial"];
        } else {
            $doc["adquiriente"]["razonSocial"] = $rec["apellido1"];
            if (trim($rec["apellido2"]) != '') {
                $doc["adquiriente"]["razonSocial"] .= ' ' . $rec["apellido2"];
            }
            if (trim($rec["nombre1"]) != '') {
                $doc["adquiriente"]["razonSocial"] .= ' ' . $rec["nombre1"];
            }
            if (trim($rec["nombre2"]) != '') {
                $doc["adquiriente"]["razonSocial"] .= ' ' . $rec["nombre2"];
            }
        }

        //

        $doc["adquiriente"]["numeroIdentificacion"] = $nide;
        $doc["adquiriente"]["dv"] = $dv;
        $doc["adquiriente"]["razonSocial"] = $rec["razonsocial"];
        $doc["adquiriente"]["nombreRegistrado"] = $nr;
        $doc["adquiriente"]["primerNombre"] = $rec["nombre1"];
        if ($rec["nombre2"] == '') {
            $rec["nombre2"] = null;
        }
        $doc["adquiriente"]["segundoNombre"] = $rec["nombre2"];
        $doc["adquiriente"]["primerApellido"] = $rec["apellido1"];
        if ($rec["apellido2"] == '') {
            $rec["apellido2"] = null;
        }
        $doc["adquiriente"]["segundoApellido"] = $rec["apellido2"];
        $doc["adquiriente"]["particula"] = null;
        $doc["adquiriente"]["cont"] = null;
        if ($rec["telefono1"] == '') {
            $rec["telefono1"] = null;
        }
        if ($nide == '222222222222') {
            $doc["adquiriente"]["razonSocial"] = 'CLIENTE FINAL GENERICO';
            $doc["adquiriente"]["nombreRegistrado"] = null;
            $doc["adquiriente"]["primerNombre"] = null;
            $doc["adquiriente"]["segundoNombre"] = null;
            $doc["adquiriente"]["primerApellido"] = null;
            $doc["adquiriente"]["segundoApellido"] = null;
            $doc["adquiriente"]["particula"] = null;
            $doc["adquiriente"]["cont"] = null;
            $rec["email"] = CFE_EMAIL_CLIENTE_FINAL;
            $rec["telefono1"] = CFE_FACTURADOR_TEL1;
            $rec["telefono2"] = CFE_FACTURADOR_TEL2;
            $rec["codposcom"] = CFE_FACTURADOR_CODPOSCOM;
            $rec["codposnot"] = CFE_FACTURADOR_CODPOSNOT;
            $rec["municipio"] = CFE_FACTURADOR_MUNCOM;
            $rec["direccion"] = CFE_FACTURADOR_DIRCOM;
            $rec["municipionot"] = CFE_FACTURADOR_MUNNOT;
            $rec["direccionnot"] = CFE_FACTURADOR_DIRNOT;
        }
        if ($rec["telefono1"] == '') {
            $rec["telefono1"] = null;
        }
        if ($rec["telefono2"] == '') {
            $rec["telefono2"] = null;
        }
        if ($rec["email"] == '') {
            $rec["email"] = null;
        }
        if (!isset($rec["codposcom"]) || $rec["codposcom"] == '') {
            $rec["codposcom"] = null;
        }
        if (!isset($rec["codposnot"]) || $rec["codposnot"] == '') {
            $rec["codposnot"] = null;
        }

        //
        if (($rec["municipio"] == '' || $rec["municipio"] == '00000') && $nide != '222222222222') {
            $liq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "numerorecibo='" . $rec["recibo"] . "'");
            if ($liq && !empty($liq)) {
                $rec["municipio"] = $liq["idmunicipio"];
                $rec["direccion"] = $liq["direccion"];
                $rec["municipionot"] = $liq["idmunicipio"];
                $rec["direccionnot"] = $liq["direccion"];
            }
        }

        //
        if ($rec["codposcom"] != null && $rec["codposcom"] != '' && $nide != '222222222222') {
            $zonas = retornarRegistroMysqliApi($mysqli, 'bas_codigos_postales', "municipio='" . $rec["municipio"] . "' and codigopostal='" . $rec["codposcom"] . "'", "id");
            if ($zonas === false || empty($zonas)) {
                $rec["codposcom"] = null;
            }
        }

        //
        if ($rec["codposnot"] != null && $rec["codposnot"] != '' && $nide != '222222222222') {
            $zonas = retornarRegistroMysqliApi($mysqli, 'bas_codigos_postales', "municipio='" . $rec["municipionot"] . "' and codigopostal='" . $rec["codposnot"] . "'", "id");
            if ($zonas === false || empty($zonas)) {
                $rec["codposnot"] = null;
            }
        }

        //
        if ($rec["codposcom"] == null && $nide != '222222222222') {
            $zonas = retornarRegistrosMysqliApi($mysqli, 'bas_codigos_postales', "municipio='" . $rec["municipio"] . "'", "id");
            if ($zonas && !empty($zonas)) {
                foreach ($zonas as $z) {
                    if ($z["tipo"] == 'Urbano') {
                        if ($rec["codposcom"] == '') {
                            $rec["codposcom"] = $z["codigopostal"];
                        }
                    }
                }
            }
        }

        //
        if ($rec["codposnot"] == null && $nide != '222222222222') {
            $zonas = retornarRegistrosMysqliApi($mysqli, 'bas_codigos_postales', "municipio='" . $rec["municipionot"] . "'", "id");
            if ($zonas && !empty($zonas)) {
                foreach ($zonas as $z) {
                    if ($z["tipo"] == 'Urbano') {
                        if ($rec["codposnot"] == '') {
                            $rec["codposnot"] = $z["codigopostal"];
                        }
                    }
                }
            }
        }

        $doc["adquiriente"]["telefono1"] = $rec["telefono1"];
        $doc["adquiriente"]["telefono2"] = $rec["telefono2"];
        if (TIPO_AMBIENTE == 'PRUEBAS') {
            if (!defined('CFE_EMAIL_PRUEBAS') || trim(CFE_EMAIL_PRUEBAS) == '') {
                $doc["adquiriente"]["email"] = EMAIL_NOTIFICACION_PRUEBAS;
                if (trim($doc["adquiriente"]["email"]) == '') {
                    $doc["adquiriente"]["email"] = 'jint@confecamaras.org.co';
                }
            } else {
                $doc["adquiriente"]["email"] = CFE_EMAIL_PRUEBAS;
            }
        } else {
            $doc["adquiriente"]["email"] = $rec["email"];
        }
        $doc["adquiriente"]["zonaPostal"] = null; // revisar
        $doc["adquiriente"]["respTributario"] = ""; // Responsable tributario

        $direc = array();
        $direc["codigoPais"] = "CO";
        $direc["nombrePais"] = "COLOMBIA";
        $direc["codigoLenguajePais"] = "es";
        $direc["codigoDepartamento"] = substr(sprintf("%05s", $rec["municipio"]), 0, 2);
        $direc["nombreDepartamento"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $rec["municipio"]) . "'", "departamento");
        $direc["codigoCiudad"] = sprintf("%05s", $rec["municipio"]);
        $direc["nombreCiudad"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $rec["municipio"]) . "'", "ciudad");
        $direc["direccionFisica"] = $rec["direccion"];
        $direc["codigoPostal"] = $rec["codposcom"];
        $doc["adquiriente"]["direccion"] = $direc;
        $direcf = array(); // revisar como enviar la data de notificacion judicial
        $direcf["codigoPais"] = "CO";
        $direcf["nombrePais"] = "COLOMBIA";
        $direcf["codigoLenguajePais"] = "es";
        $direcf["codigoDepartamento"] = substr(sprintf("%05s", $rec["municipionot"]), 0, 2);
        $direcf["nombreDepartamento"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $rec["municipionot"]) . "'", "departamento");
        $direcf["codigoCiudad"] = sprintf("%05s", $rec["municipionot"]);
        $direcf["nombreCiudad"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $rec["municipionot"]) . "'", "ciudad");
        $direcf["direccionFisica"] = $rec["direccionnot"];
        $direcf["codigoPostal"] = $rec["codposnot"];
        $doc["adquiriente"]["direccionFiscal"] = $direcf;
        $doc["adquiriente"]["codigoRegimen"] = $cr; // revisar - Opcional
        $doc["adquiriente"]["naturaleza"] = $nat; // persona jurídica (1) o natural (2)
        $doc["adquiriente"]["codigoImpuesto"] = "01"; // revisar
        $doc["adquiriente"]["nombreImpuesto"] = "IVA"; // revisar
        $doc["adquiriente"]["responsabilidadFiscal"] = $rf; // Reponsabilidad otros como juridica o natural
        if (trim($doc["adquiriente"]["responsabilidadFiscal"]) != 'O-13' &&
                trim($doc["adquiriente"]["responsabilidadFiscal"]) != 'O-15' &&
                trim($doc["adquiriente"]["responsabilidadFiscal"]) != 'O-23' &&
                trim($doc["adquiriente"]["responsabilidadFiscal"]) != 'O-47' &&
                trim($doc["adquiriente"]["responsabilidadFiscal"]) != 'R-99-PN'
        ) {
            $doc["adquiriente"]["responsabilidadFiscal"] = 'R-99-PN';
        }

        // }
        // ****************************************************************************** //
        // datos del mandante
        // ****************************************************************************** //
        if ($rec["numerointernorue"] != '') {
            $doc["identificacionMandante"] = array();
            $camManda = retornarRegistroMysqliApi($mysqli, 'datos_empresas', "camara='" . substr($rec["numerointernorue"], 19, 2) . "'");
            if ($camManda === false || empty($camManda)) {
                $salida["codigoError"] = '9999';
                $salida["mensajeError"] = 'Mandante : ' . substr($rec["numerointernorue"], 19, 2) . ' no almacenado en datos_empresa';
                return $salida;
            }
            $sepIde = \funcionesGenerales::separarDv($camManda["identificacion"]);
            if (!isset($camManda["dv"]) || trim($camManda["dv"]) == '') {
                $camManda["dv"] = null;
            }
            if (!isset($camManda["razonsocial"]) || trim($camManda["razonsocial"]) == '') {
                $camManda["razonsocial"] = null;
            }
            if (!isset($camManda["nombreregistrado"]) || trim($camManda["nombreregistrado"]) == '') {
                $camManda["nombreregistrado"] = null;
            }
            if (!isset($camManda["telefono1"]) || trim($camManda["telefono1"]) == '') {
                $camManda["telefono1"] = null;
            }
            if (!isset($camManda["telefono2"]) || trim($camManda["telefono2"]) == '') {
                $camManda["telefono2"] = null;
            }
            if (!isset($camManda["email"]) || trim($camManda["email"]) == '') {
                $camManda["email"] = null;
            }
            if (!isset($camManda["zonapostal"]) || trim($camManda["zonapostal"]) == '') {
                $camManda["zonapostal"] = null;
            }
            if (!isset($camManda["codposcom"]) || trim($camManda["codposcom"]) == '') {
                $camManda["codposcom"] = null;
            }
            if (!isset($camManda["codposnot"]) || trim($camManda["codposnot"]) == '') {
                $camManda["codposnot"] = null;
            }
            if (!isset($camManda["muncom"]) || trim($camManda["muncom"]) == '') {
                $camManda["muncom"] = null;
            }
            if (!isset($camManda["dircom"]) || trim($camManda["dircom"]) == '') {
                $camManda["dircom"] = null;
            }
            if (!isset($camManda["dirnot"]) || trim($camManda["dirnot"]) == '') {
                $camManda["dirnot"] = null;
            }
            if (!isset($camManda["codigoregimen"]) || trim($camManda["codigoregimen"]) == '') {
                $camManda["codigoregimen"] = null;
            }

            $doc["identificacionMandante"]["codDocumentoDian"] = "31";
            $doc["identificacionMandante"]["numeroIdentificacion"] = $sepIde["identificacion"];
            $doc["identificacionMandante"]["dv"] = $sepIde["dv"];
            $doc["identificacionMandante"]["razonSocial"] = $camManda["razonsocial"];
            $doc["identificacionMandante"]["nombreRegistrado"] = $camManda["nombreregistrado"];
            $doc["identificacionMandante"]["primerNombre"] = null;
            $doc["identificacionMandante"]["segundoNombre"] = null;
            $doc["identificacionMandante"]["primerApellido"] = null;
            $doc["identificacionMandante"]["segundoApellido"] = null;
            $doc["identificacionMandante"]["particula"] = null;
            $doc["identificacionMandante"]["cont"] = null;
            $doc["identificacionMandante"]["telefono1"] = $camManda["telefono1"];
            $doc["identificacionMandante"]["telefono2"] = $camManda["telefono2"];
            $doc["identificacionMandante"]["email"] = $camManda["email"];
            $doc["identificacionMandante"]["zonaPostal"] = $camManda["zonapostal"];
            $direc = array();
            $direc["codigoPais"] = "CO";
            $direc["nombrePais"] = "COLOMBIA";
            $direc["codigoLenguajePais"] = "es";
            $direc["codigoDepartamento"] = substr(sprintf("%05s", $camManda["muncom"]), 0, 2);
            $direc["nombreDepartamento"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $camManda["muncom"]) . "'", "departamento");
            $direc["codigoCiudad"] = sprintf("%05s", $camManda["muncom"]);
            $direc["nombreCiudad"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $camManda["muncom"]) . "'", "ciudad");
            $direc["direccionFisica"] = $camManda["dircom"];
            $direc["codigoPostal"] = $camManda["codposcom"];
            $doc["identificacionMandante"]["direccion"] = $direc;
            $direc = array();
            $direc["codigoPais"] = "CO";
            $direc["nombrePais"] = "COLOMBIA";
            $direc["codigoLenguajePais"] = "es";
            $direc["codigoDepartamento"] = substr(sprintf("%05s", $camManda["munnot"]), 0, 2);
            $direc["nombreDepartamento"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $camManda["munnot"]) . "'", "departamento");
            $direc["codigoCiudad"] = sprintf("%05s", $camManda["munnot"]);
            $direc["nombreCiudad"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $camManda["munnot"]) . "'", "ciudad");
            $direc["direccionFisica"] = $camManda["dirnot"];
            $direc["codigoPostal"] = $camManda["codposnot"];
            $doc["identificacionMandante"]["direccionFiscal"] = $direc;
            $doc["identificacionMandante"]["codigoRegimen"] = $camManda["codigoregimen"];
            $doc["identificacionMandante"]["naturaleza"] = '1';
            $doc["identificacionMandante"]["responsabilidadFiscal"] = $camManda["responsabilidadfiscal"];
            if (trim($doc["identificacionMandante"]["responsabilidadFiscal"]) != 'O-13' &&
                    trim($doc["identificacionMandante"]["responsabilidadFiscal"]) != 'O-15' &&
                    trim($doc["identificacionMandante"]["responsabilidadFiscal"]) != 'O-23' &&
                    trim($doc["identificacionMandante"]["responsabilidadFiscal"]) != 'O-47' &&
                    trim($doc["identificacionMandante"]["responsabilidadFiscal"]) != 'R-99-PN'
            ) {
                $doc["identificacionMandante"]["responsabilidadFiscal"] = 'R-99-PN';
            }
            $doc["identificacionMandante"]["codigoImpuesto"] = $camManda["codigoimpuesto"];
            $doc["identificacionMandante"]["nombreImpuesto"] = $camManda["nombreimpuesto"];
            $doc["identificacionMandante"]["respTributario"] = $camManda["responsabilidadtributaria"];
        }

        // ****************************************************************************** //
        // datos del facturador
        // ****************************************************************************** //
        $doc["facturador"] = array();
        if (defined('CFE_FACTURADOR_RAZONSOCIAL') && CFE_FACTURADOR_RAZONSOCIAL != '') {
            $camFactu = array();
            $camFactu["identificacion"] = str_replace(array(".", ",", "-", " "), "", NIT);
            $camFactu["razonsocial"] = CFE_FACTURADOR_RAZONSOCIAL;
            $camFactu["nombreregistrado"] = CFE_FACTURADOR_NOMBREREGISTRADO;
            $camFactu["telefono1"] = CFE_FACTURADOR_TEL1;
            $camFactu["telefono2"] = CFE_FACTURADOR_TEL2;
            $camFactu["email"] = CFE_FACTURADOR_EMAIL;
            $camFactu["muncom"] = CFE_FACTURADOR_MUNCOM;
            $camFactu["dircom"] = CFE_FACTURADOR_DIRCOM;
            $camFactu["codposcom"] = CFE_FACTURADOR_CODPOSCOM;
            $camFactu["munnot"] = CFE_FACTURADOR_MUNNOT;
            $camFactu["dirnot"] = CFE_FACTURADOR_DIRNOT;
            $camFactu["codposnot"] = CFE_FACTURADOR_CODPOSNOT;
            $camFactu["codigoregimen"] = CFE_FACTURADOR_CODIGOREGIMEN;
            $camFactu["responsabilidadfiscal"] = CFE_FACTURADOR_RESPOFISCAL;
            $camFactu["codigoimpuesto"] = CFE_FACTURADOR_CODIGOIMPUESTO;
            $camFactu["nombreimpuesto"] = CFE_FACTURADOR_CODIGOIMPUESTONOMBRE;
            $camFactu["responsabilidadtributaria"] = CFE_FACTURADOR_RESPOTRIBUTARIO;
        } else {
            $camFactu = retornarRegistroMysqliApi($mysqli, 'datos_empresas', "camara='" . CODIGO_EMPRESA . "'");
        }
        if ($camFactu === false || empty($camFactu)) {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador : ' . CODIGO_EMPRESA . ' no almacenado en datos_empresa';
            return $salida;
        }
        if ($camFactu["razonsocial"] == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador sin razonsocial en datos_empresas';
            return $salida;
        }
        if ($camFactu["telefono1"] == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador sin telefono1 en datos_empresas';
            return $salida;
        }
        if ($camFactu["email"] == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador sin email en datos_empresas';
            return $salida;
        }
        if ($camFactu["muncom"] == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador sin muncom en datos_empresas';
            return $salida;
        }
        if ($camFactu["dircom"] == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador sin dircom en datos_empresas';
            return $salida;
        }
        if ($camFactu["codigoregimen"] == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador sin codigoregimen en datos_empresas';
            return $salida;
        }
        if ($camFactu["responsabilidadfiscal"] == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador sin responsabilidadfiscal en datos_empresas';
            return $salida;
        }
        if ($camFactu["codigoimpuesto"] == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador sin codigoImpuesto en datos_empresas';
            return $salida;
        }
        if ($camFactu["nombreimpuesto"] == '') {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Facturador sin nombreImpuesto en datos_empresas';
            return $salida;
        }

        $sepIde = \funcionesGenerales::separarDv($camFactu["identificacion"]);
        if (!isset($camFactu["razonsocial"]) || trim($camFactu["razonsocial"]) == '') {
            $camFactu["razonsocial"] = null;
        }
        if (!isset($camFactu["nombreregistrado"]) || trim($camFactu["nombreregistrado"]) == '') {
            $camFactu["nombreregistrado"] = null;
        }
        if (!isset($camFactu["telefono1"]) || trim($camFactu["telefono1"]) == '') {
            $camFactu["telefono1"] = null;
        }
        if (!isset($camFactu["telefono2"]) || trim($camFactu["telefono2"]) == '') {
            $camFactu["telefono2"] = null;
        }
        if (!isset($camFactu["email"]) || trim($camFactu["email"]) == '') {
            $camFactu["email"] = null;
        }
        if (!isset($camFactu["zonapostal"]) || trim($camFactu["zonapostal"]) == '') {
            $camFactu["zonapostal"] = null;
        }
        if (!isset($camFactu["codposcom"]) || trim($camFactu["codposcom"]) == '') {
            $camFactu["codposcom"] = null;
        }
        if (!isset($camFactu["codposnot"]) || trim($camFactu["codposnot"]) == '') {
            $camFactu["codposnot"] = null;
        }
        if (!isset($camFactu["muncom"]) || trim($camFactu["muncom"]) == '') {
            $camFactu["muncom"] = null;
        }
        if (!isset($camFactu["dircom"]) || trim($camFactu["dircom"]) == '') {
            $camFactu["dircom"] = null;
        }
        if (!isset($camFactu["dirnot"]) || trim($camFactu["dirnot"]) == '') {
            $camFactu["dirnot"] = null;
        }
        if (!isset($camFactu["codigoregimen"]) || trim($camFactu["codigoregimen"]) == '') {
            $camFactu["codigoregimen"] = null;
        }
        $doc["facturador"]["codDocumentoDian"] = "31";
        $doc["facturador"]["numeroIdentificacion"] = $sepIde["identificacion"];
        $doc["facturador"]["dv"] = $sepIde["dv"];
        $doc["facturador"]["razonSocial"] = $camFactu["razonsocial"];
        if (trim((string) $camFactu["nombreregistrado"]) == '') {
            $camFactu["nombreregistrado"] = $camFactu["razonsocial"];
        }
        $doc["facturador"]["nombreRegistrado"] = $camFactu["nombreregistrado"];
        $doc["facturador"]["primerNombre"] = null;
        $doc["facturador"]["segundoNombre"] = null;
        $doc["facturador"]["primerApellido"] = null;
        $doc["facturador"]["segundoApellido"] = null;
        $doc["facturador"]["particula"] = null;
        $doc["facturador"]["cont"] = null;

        $doc["facturador"]["telefono1"] = $camFactu["telefono1"];
        $doc["facturador"]["telefono2"] = $camFactu["telefono2"];
        $doc["facturador"]["email"] = $camFactu["email"];
        if (trim((string) $camFactu["zonapostal"]) == '') {
            $camFactu["zonapostal"] = '000000';
        }
        $doc["facturador"]["zonaPostal"] = $camFactu["zonapostal"];
        $direc = array();
        $direc["codigoPais"] = "CO";
        $direc["nombrePais"] = "COLOMBIA";
        $direc["codigoLenguajePais"] = "es";
        $direc["codigoDepartamento"] = substr(sprintf("%05s", $camFactu["muncom"]), 0, 2);
        $direc["nombreDepartamento"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $camFactu["muncom"]) . "'", "departamento");
        $direc["codigoCiudad"] = sprintf("%05s", $camFactu["muncom"]);
        $direc["nombreCiudad"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $camFactu["muncom"]) . "'", "ciudad");
        $direc["direccionFisica"] = $camFactu["dircom"];
        $direc["codigoPostal"] = $camFactu["codposcom"];
        $doc["facturador"]["direccion"] = $direc;
        $direc = array();
        $direc["codigoPais"] = "CO";
        $direc["nombrePais"] = "COLOMBIA";
        $direc["codigoLenguajePais"] = "es";
        $direc["codigoDepartamento"] = substr(sprintf("%05s", $camFactu["munnot"]), 0, 2);
        $direc["nombreDepartamento"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $camFactu["munnot"]) . "'", "departamento");
        $direc["codigoCiudad"] = sprintf("%05s", $camFactu["munnot"]);
        $direc["nombreCiudad"] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . sprintf("%05s", $camFactu["munnot"]) . "'", "ciudad");
        $direc["direccionFisica"] = $camFactu["dirnot"];
        $direc["codigoPostal"] = $camFactu["codposnot"];
        $doc["facturador"]["direccionFiscal"] = $direc;
        $doc["facturador"]["codigoRegimen"] = $camFactu["codigoregimen"];
        $doc["facturador"]["naturaleza"] = '1';
        $doc["facturador"]["responsabilidadFiscal"] = $camFactu["responsabilidadfiscal"];
        if (trim((string) $doc["facturador"]["responsabilidadFiscal"]) != 'O-13' &&
                trim((string) $doc["facturador"]["responsabilidadFiscal"]) != 'O-15' &&
                trim((string) $doc["facturador"]["responsabilidadFiscal"]) != 'O-23' &&
                trim((string) $doc["facturador"]["responsabilidadFiscal"]) != 'O-47' &&
                trim((string) $doc["facturador"]["responsabilidadFiscal"]) != 'R-99-PN'
        ) {
            $doc["facturador"]["responsabilidadFiscal"] = 'R-99-PN';
        }

        $doc["facturador"]["codigoImpuesto"] = $camFactu["codigoimpuesto"];
        $doc["facturador"]["nombreImpuesto"] = $camFactu["nombreimpuesto"];
        $doc["facturador"]["respTributario"] = $camFactu["responsabilidadtributaria"];

        // ****************************************************************************** //
        // Datos finales del documento
        // ****************************************************************************** //
        $doc["numeroFacturaGenerado"] = null;
        $doc["nroResolucion"] = null;
        $doc["fechaInicialResolucion"] = null;
        $doc["fechaFinalResolucion"] = null;
        $doc["nroInicialResolucion"] = null;
        $doc["nroFinalResolucion"] = null;
        $doc["consecutivoResolucion"] = null;
        $doc["horaInicioResolucion"] = null;
        $doc["horaFinResolucion"] = null;

        // ****************************************************************************** //
        // Imagen  y datos de la factura si esta se envía pre-generada
        // ****************************************************************************** //
        $doc["base64"] = null;
        $doc["urlAnexos"] = null;
        $doc["posicionXCufe"] = 35;
        $doc["posicionYCufe"] = 50;
        $doc["rotacionCufe"] = 0;
        $doc["fuenteCufe"] = 8;
        $doc["posicionXQr"] = 125;
        $doc["posicionYQr"] = 265;
        $doc["listaDocumentosReferenciados"] = array();

        //
        $doc["redondeo"] = number_format($redondeos, 2, ".", "");
        $doc["subtotal"] = number_format($bruto, 2, ".", "");
        $doc["iva"] = number_format($valivas, 2, ".", "");
        $doc["total"] = number_format($neto, 2, ".", "");
        $doc["totalDescuentos"] = number_format($descuentos, 2, ".", "");
        $doc["totalBaseImponible"] = number_format($baseimponible, 2, ".", "");
        $doc["totalCargos"] = number_format($cargos, 2, ".", "");
        $doc["subtotalMasTributos"] = number_format(($bruto + $valivas), 2, ".", "");
        $doc["montoscrito"] = \funcionesGenerales::montoEscrito($neto);

        //
        $arrJson["documentos"][] = $doc;

        // ****************************************************************************** //
        // Encuentra número del lote
        // ****************************************************************************** //        
        $arrJson["nroLote"] = \funcionesGenerales::retornarSecuencia($mysqli, 'LOTE-CFE');

        // ****************************************************************************** //
        // Pasa el arreglo a json
        // ****************************************************************************** //        
        $salida["json"] = json_encode($arrJson);

        //
        $nameLog1 = 'procesarRecibosCFE_envios_' . date("Ymd");

        // ****************************************************************************** //
        // Almacena el json que se enviará al CFE
        // ****************************************************************************** //
        if (!is_dir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"])) {
            mkdir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"], 0777);
        }

        if (!is_dir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg')) {
            mkdir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg', 0777);
        }
        if (!is_dir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/cfe_json')) {
            mkdir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/cfe_json', 0777);
        }
        if (!is_dir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/cfe_json/' . $rec["fecha"])) {
            mkdir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/cfe_json/' . $rec["fecha"], 0777);
        }

        if (is_dir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/cfe_json/' . $rec["fecha"])) {
            $n = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/cfe_json/' . $rec["fecha"] . '/' . $rec["recibo"] . '-request.json';
            try {
                $f = fopen($n, "w");
                fwrite($f, $salida["json"]);
                fclose($f);
            } catch (Exception $e) {
                \logApi::general2($nameLog1, $rec["recibo"], 'Error creando log en cfe_json (request) ' . $e->getMessage());
            }
        }

        // ****************************************************************************** //
        // Consume el componente CFE
        // ****************************************************************************** //
        if (defined('CFE_URL_API') && trim(CFE_URL_API) != '') {
            $url = CFE_URL_API;
        } else {
            $url = 'http://facturaelectronica.aspsols.com/facturador/recibir-docs/';
        }
        $headers = array(
            'Content-Type:application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        // curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $salida["json"]);
        $result = curl_exec($ch);
        curl_close($ch);
        \logApi::general2($nameLog1, $rec["recibo"], 'Enviado al CFE - Respuesta del CFE: ' . $result);

        // ****************************************************************************** //
        // Almacena la respuesta del componente CFE
        // ****************************************************************************** //
        $n = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/cfe_json/' . $rec["fecha"] . '/' . $rec["recibo"] . '-response.json';
        try {
            $f = fopen($n, "w");
            fwrite($f, $result);
            fclose($f);
        } catch (Exception $e) {
            \logApi::general2($nameLog1, $rec["recibo"], 'Error creando log en cfe_json (response) ' . $e->getMessage());
        }
        $salida["jsonresponse"] = $result;

        // ****************************************************************************** //
        // Almacena tabla mreg_recibosgenerados_json_cfe
        // ****************************************************************************** //
        $response = json_decode($result, true);
        if (!isset($response[0]["status"])) {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'No se recibió una repsuesta válida desde el CFE (1)';
            return $salida;
        }
        if (!isset($response[0]["documentoRecibido"])) {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'No se recibió una repsuesta válida desde el CFE (2)';
            return $salida;
        }
        if ($response[0]["documentoRecibido"] != $rec["recibo"]) {
            $salida["codigoError"] = '9999';
            $salida["mensajeError"] = 'Documento recibido desde el CFE (' . $response[0]["documentoRecibido"] . ') no corresponde al enviado (' . $rec["recibo"] . ')';
            return $salida;
        }

        //
        $status = $response[0]["status"];
        $cufe = '';
        $cude = '';
        $estadoDoc = '';
        $observaciones = '';
        if (isset($response[0]["cufe"])) {
            $cufe = $response[0]["cufe"];
        }
        if (isset($response[0]["cude"])) {
            $cude = $response[0]["cude"];
        }
        if (isset($response[0]["estadoDoc"])) {
            $estadoDoc = $response[0]["estadoDoc"];
        }
        if (isset($response[0]["message"])) {
            $observaciones = $response[0]["message"];
        }
        if (isset($response[0]["errorMessages"]) && !empty($response[0]["errorMessages"])) {
            foreach ($response[0]["errorMessages"] as $msg) {
                $observaciones .= "\r\n" . $msg["message"];
            }
        }

        //
        $arrCampos = array(
            'recibo',
            'fechahoraenvio',
            'json',
            'jsonresponse',
            'status',
            'cufe',
            'cude',
            'estadodoc',
            'observaciones'
        );
        $arrValores = array(
            "'" . $rec["recibo"] . "'",
            "'" . date("Ymd") . ' ' . date("His") . "'",
            "'" . addslashes($salida["json"]) . "'",
            "'" . addslashes($salida["jsonresponse"]) . "'",
            "'" . $status . "'",
            "'" . $cufe . "'",
            "'" . $cude . "'",
            "'" . $estadoDoc . "'",
            "'" . addslashes($observaciones) . "'"
        );
        insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_json_cfe', $arrCampos, $arrValores);

        //
        $arrCampos = array(
            'recibo',
            'fecha',
            'hora',
            'estado',
            'prefijofactura',
            'nrofactura',
            'fechafactura',
            'prefijonotacredito',
            'nronotacredito',
            'fechanotacredito',
            'cufe',
            'cude',
            'jsonrequest',
            'jsonresponse',
            'xml',
            'observaciones'
        );
        $arrValores = array(
            "'" . $rec["recibo"] . "'",
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . $status . "'",
            "''",
            "''",
            "''",
            "''",
            "''",
            "''",
            "'" . $cufe . "'",
            "'" . $cude . "'",
            "'" . addslashes($salida["json"]) . "'",
            "'" . addslashes($salida["jsonresponse"]) . "'",
            "''",
            "'" . addslashes($observaciones) . "'"
        );
        insertarRegistrosMysqliApi($mysqli, 'mreg_cfe_log', $arrCampos, $arrValores);

        // ****************************************************************************** //
        // Retorna la salida 
        // ****************************************************************************** //        
        if ($status != 'OK') {
            $salida["codigoError"] = '9999';
            $salida["status"] = $status;
            $salida["mensajeError"] = $observaciones;
        }

        //
        return $salida;
    }

    public static function returnState($nrec) {
        $mysqli = conexionMysqliApi();
        $rec = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', "recibo='" . $nrec . "'");
        $mysqli->close();
        $campos = array();
        $campos["camaraSii"] = CODIGO_EMPRESA;
        $campos["sucursalSii"] = substr($rec["operacion"], 0, 2);
        $campos["tipoNumeracion"] = '1';
        if (substr($rec["recibo"], 0, 1) == 'M') {
            $campos["tipoNumeracion"] = '2';
        }
        $campos["numDoc"] = $rec["recibo"];

        // ****************************************************************************** //
        // Consume el componente CFE
        // ****************************************************************************** //
        if (defined('CFE_URL_API_EST') && trim(CFE_URL_API_EST) != '') {
            $url = CFE_URL_API_EST;
        } else {
            $url = 'http://facturaelectronica.aspsols.com/consultar/estado-documento/';
        }

        //
        $url1 = $url . '?camaraSii=' . CODIGO_EMPRESA . '&sucursalSii=' . substr($rec["operacion"], 0, 2) . '&tipoNumeracion=' . $campos["tipoNumeracion"] . '&numDoc=' . $rec["recibo"];

        //
        $headers = array(
            'Content-Type:application/json'
        );

        //
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $_SESSION["generales"]["url"] = $url1;
        return $result;
    }

}
