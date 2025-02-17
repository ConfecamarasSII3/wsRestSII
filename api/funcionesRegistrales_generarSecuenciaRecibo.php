<?php

class funcionesRegistrales_generarSecuenciaRecibo {

    public static function generarSecuenciaRecibo($mysqli, $tipo = 'S', $fps = array(), $operacion = '', $fecha = '', $hora = '', $codbarras = '', $tiporegistro = '', $identificacion = '', $nombre = '', $organizacion = '', $categoria = '', $idtipodoc = '', $numdoc = '', $origendoc = '', $fechadoc = '', $estado = '', $fecharenovacionaplicable = '', $tiporecibo = 'S', $arrServs = array()) {

        // ******************************************************************************* //
        // validas que todos los datos requeridos se pasen
        // ******************************************************************************* //
        if (trim($fecharenovacionaplicable) == '') {
            $fecharenovacionaplicable = $fecha;
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
        $totalliquidacion = 0;
        $totalcamara = 0;
        $totalgobernacion = 0;
        $totalesterecibo = 0;

        $pagoprepago = 0;
        $pagoafiliado = 0;
        $pagoefectivo = 0;
        $pagocheque = 0;
        $pagoconsignacion = 0;
        $pagoqr = 0;
        $pagoach = 0;
        $pagovisa = 0;
        $pagomastercard = 0;
        $pagocredencial = 0;
        $pagoamerican = 0;
        $pagodiners = 0;
        $pagotdebito = 0;

        if (!defined('SEPARAR_RECIBOS') || SEPARAR_RECIBOS == 'NO') {
            $totalcamara = $_SESSION["tramite"]["valortotal"];
        } else {
            foreach ($_SESSION["tramite"]["liquidacion"] as $d) {
                $totalliquidacion = $totalliquidacion + $d["valorservicio"];
                if (ltrim((string) $arrServs[$d["idservicio"]]["conceptodepartamental"], '0') == '') {
                    $totalcamara = $totalcamara + $d["valorservicio"];
                }
                if (ltrim((string) $arrServs[$d["idservicio"]]["conceptodepartamental"], '0') != '') {
                    $totalgobernacion = $totalgobernacion + $d["valorservicio"];
                }
            }
        }

        //
        if ($tiporecibo == 'S') {
            $totalesterecibo = $totalcamara;
        } else {
            $totalesterecibo = $totalgobernacion;
        }

        // **************************************************************************************************************** //
        // Encuentra el total a pagar por cada forma de pago
        // y dependiendo de si es Servicio o Gobernacion
        // Resta por orden Efedtivo, Cheque, Consignacion, Ach, Visa, mastercard, Credencialm, American, Diners y Tdebito
        // **************************************************************************************************************** //
        if ($tiporecibo == 'S' && $totalgobernacion == 0) {
            $pagoprepago = $_SESSION["tramite"]["pagoprepago"];
            $pagoafiliado = $_SESSION["tramite"]["pagoafiliado"];
            $pagoefectivo = $_SESSION["tramite"]["pagoefectivo"];
            $pagocheque = $_SESSION["tramite"]["pagocheque"];
            $pagoconsignacion = $_SESSION["tramite"]["pagoconsignacion"];            
            $pagoqr = $_SESSION["tramite"]["pagoqr"];
            $pagoach = $_SESSION["tramite"]["pagoach"];
            $pagovisa = $_SESSION["tramite"]["pagovisa"];
            $pagomastercard = $_SESSION["tramite"]["pagomastercard"];
            $pagocredencial = $_SESSION["tramite"]["pagocredencial"];
            $pagoamerican = $_SESSION["tramite"]["pagoamerican"];
            $pagodiners = $_SESSION["tramite"]["pagodiners"];
            $pagotdebito = $_SESSION["tramite"]["pagotdebito"];
        }

        if ($tiporecibo == 'G' && $totalcamara == 0) {
            $pagoprepago = $_SESSION["tramite"]["pagoprepago"];
            $pagoafiliado = $_SESSION["tramite"]["pagoafiliado"];
            $pagoefectivo = $_SESSION["tramite"]["pagoefectivo"];
            $pagocheque = $_SESSION["tramite"]["pagocheque"];
            $pagoconsignacion = $_SESSION["tramite"]["pagoconsignacion"];
            $pagoqr = $_SESSION["tramite"]["pagoqr"];
            $pagoach = $_SESSION["tramite"]["pagoach"];
            $pagovisa = $_SESSION["tramite"]["pagovisa"];
            $pagomastercard = $_SESSION["tramite"]["pagomastercard"];
            $pagocredencial = $_SESSION["tramite"]["pagocredencial"];
            $pagoamerican = $_SESSION["tramite"]["pagoamerican"];
            $pagodiners = $_SESSION["tramite"]["pagodiners"];
            $pagotdebito = $_SESSION["tramite"]["pagotdebito"];
        }

        if ($tiporecibo == 'S' && $totalgobernacion != 0) {
            if ($_SESSION["tramite"]["pagoprepago"] != 0) {
                $pagoprepago = $totalesterecibo;
            } else {
                if ($_SESSION["tramite"]["pagoafiliado"] != 0) {
                    $pagoafiliado = $totalesterecibo;
                } else {
                    $totalfaltante = $totalesterecibo;
                    if ($_SESSION["tramite"]["pagoefectivo"] >= $totalfaltante) {
                        $pagoefectivo = $totalfaltante;
                        $totalfaltante = 0;
                    } else {
                        $pagoefectivo = $_SESSION["tramite"]["pagoefectivo"];
                        $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagoefectivo"];
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagocheque"] >= $totalfaltante) {
                            $pagocheque = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagocheque = $_SESSION["tramite"]["pagocheque"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagocheque"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagoconsignacion"] >= $totalfaltante) {
                            $pagoconsignacion = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagoconsignacion = $_SESSION["tramite"]["pagoconsignacion"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagoconsignacion"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagoqr"] >= $totalfaltante) {
                            $pagoqr = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagoqr = $_SESSION["tramite"]["pagoqr"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagoqr"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagoach"] >= $totalfaltante) {
                            $pagoach = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagoach = $_SESSION["tramite"]["pagoach"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagoach"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagovisa"] >= $totalfaltante) {
                            $pagovisa = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagovisa = $_SESSION["tramite"]["pagovisa"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagovisa"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagomastercard"] >= $totalfaltante) {
                            $pagomastercard = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagomastercard = $_SESSION["tramite"]["pagomastercard"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagomastercard"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagocredencial"] >= $totalfaltante) {
                            $pagocredencial = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagocredencial = $_SESSION["tramite"]["pagocredencial"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagocredencial"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagoamerican"] >= $totalfaltante) {
                            $pagoamerican = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagoamerican = $_SESSION["tramite"]["pagoamerican"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagoamerican"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagodiners"] >= $totalfaltante) {
                            $pagodiners = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagodiners = $_SESSION["tramite"]["pagodiners"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagodiners"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagotdebito"] >= $totalfaltante) {
                            $pagotdebito = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagotdebito = $_SESSION["tramite"]["pagotdebito"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagotdebito"];
                        }
                    }
                }
            }
        }

        if ($tiporecibo == 'G' && $totalcamara != 0) {
            if ($_SESSION["tramite"]["pagoprepago"] != 0) {
                $pagoprepago = $totalesterecibo;
            } else {
                if ($_SESSION["tramite"]["pagoafiliado"] != 0) {
                    $pagoafiliado = $totalesterecibo;
                } else {
                    $totalfaltante = $totalcamara;
                    if ($_SESSION["tramite"]["pagoefectivo"] >= $totalfaltante) {
                        $pagoefectivo = $totalfaltante;
                        $totalfaltante = 0;
                    } else {
                        $pagoefectivo = $_SESSION["tramite"]["pagoefectivo"];
                        $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagoefectivo"];
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagocheque"] >= $totalfaltante) {
                            $pagocheque = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagocheque = $_SESSION["tramite"]["pagocheque"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagocheque"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagoconsignacion"] >= $totalfaltante) {
                            $pagoconsignacion = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagoconsignacion = $_SESSION["tramite"]["pagoconsignacion"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagoconsignacion"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagoqr"] >= $totalfaltante) {
                            $pagoqr = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagoqr = $_SESSION["tramite"]["pagoqr"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagoqr"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagoach"] >= $totalfaltante) {
                            $pagoach = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagoach = $_SESSION["tramite"]["pagoach"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagoach"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagovisa"] >= $totalfaltante) {
                            $pagovisa = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagovisa = $_SESSION["tramite"]["pagovisa"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagovisa"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagomastercard"] >= $totalfaltante) {
                            $pagomastercard = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagomastercard = $_SESSION["tramite"]["pagomastercard"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagomastercard"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagocredencial"] >= $totalfaltante) {
                            $pagocredencial = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagocredencial = $_SESSION["tramite"]["pagocredencial"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagocredencial"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagoamerican"] >= $totalfaltante) {
                            $pagoamerican = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagoamerican = $_SESSION["tramite"]["pagoamerican"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagoamerican"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagodiners"] >= $totalfaltante) {
                            $pagodiners = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagodiners = $_SESSION["tramite"]["pagodiners"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagodiners"];
                        }
                    }
                    if ($totalfaltante > 0) {
                        if ($_SESSION["tramite"]["pagotdebito"] >= $totalfaltante) {
                            $pagotdebito = $totalfaltante;
                            $totalfaltante = 0;
                        } else {
                            $pagotdebito = $_SESSION["tramite"]["pagotdebito"];
                            $totalfaltante = $totalfaltante - $_SESSION["tramite"]["pagotdebito"];
                        }
                    }

                    //
                    $pagoefectivo = $_SESSION["tramite"]["pagoefectivo"] - $pagoefectivo;
                    $pagocheque = $_SESSION["tramite"]["pagocheque"] - $pagocheque;
                    $pagoconsignacion = $_SESSION["tramite"]["pagoconsignacion"] - $pagoconsignacion;
                    $pagoqr = $_SESSION["tramite"]["pagoqr"] - $pagoqr;
                    $pagoach = $_SESSION["tramite"]["pagoach"] - $pagoach;
                    $pagovisa = $_SESSION["tramite"]["pagovisa"] - $pagovisa;
                    $pagomastercard = $_SESSION["tramite"]["pagomastercard"] - $pagomastercard;
                    $pagocredencial = $_SESSION["tramite"]["pagocredencial"] - $pagocredencial;
                    $pagoamerican = $_SESSION["tramite"]["pagoamerican"] - $pagoamerican;
                    $pagodiners = $_SESSION["tramite"]["pagodiners"] - $pagodiners;
                    $pagotdebito = $_SESSION["tramite"]["pagotdebito"] - $pagotdebito;
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
            \logApi::general2('reportarPago_' . date("Ymd"), '', 'Error recuperando la secuencia del recibo de caja : ' . $_SESSION["generales"]["mensajeerror"]);
            $_SESSION["generales"]["mensajeerror"] = 'Error recuperando la secuencia del recibo de caja';
            return false;
        }

        // ************************************************************************************************ //
        if ($rec == '') {
            $rec = 0;
        } else {
            $rec = intval($rec);
        }

        //
        $xnombre = '';
        if ($_SESSION["tramite"]["idtipoidentificacioncliente"] != '2' && $_SESSION["tramite"]["apellido1cliente"] == '' && $_SESSION["tramite"]["apellido2cliente"] == '' && $_SESSION["tramite"]["nombre2cliente"] == '') {
            $xnombre = $_SESSION["tramite"]["nombrecliente"];
        } else {
            if ($_SESSION["tramite"]["idtipoidentificacioncliente"] == '2') {
                $xnombre = $_SESSION["tramite"]["nombrecliente"];
            } else {
                $xnombre = $_SESSION["tramite"]["apellido1cliente"];
                if (trim($_SESSION["tramite"]["apellido2cliente"]) != '') {
                    $xnombre .= ' ' . $_SESSION["tramite"]["apellido2cliente"];
                }
                if (trim($_SESSION["tramite"]["nombre1cliente"]) != '') {
                    $xnombre .= ' ' . $_SESSION["tramite"]["nombre1cliente"];
                }
                if (trim($_SESSION["tramite"]["nombre2cliente"]) != '') {
                    $xnombre .= ' ' . $_SESSION["tramite"]["nombre2cliente"];
                }
            }
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
                'pagoqr',
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

            $_SESSION["tramite"]["tipotramiterue"] = '';
            if (substr($_SESSION["tramite"]["tipotramite"], 0, 4) == 'rues') {
                $_SESSION["tramite"]["tipotramiterue"] = substr($_SESSION["tramite"]["tipotramite"], 6);
            }



            //
            $arrValores = array(
                "'" . $recx . "'",
                "'" . $operacion . "'",
                "'" . $_SESSION["tramite"]["numerofactura"] . "'",
                "'" . $codbarras . "'",
                "'" . $fecha . "'",
                "'" . $hora . "'",
                "'" . $_SESSION["generales"]["cajero"] . "'",
                "'" . $_SESSION["tramite"]["tipogasto"] . "'",
                "'" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "'",
                "'" . ltrim($_SESSION["tramite"]["identificacioncliente"], "0") . "'",
                "'" . addslashes(trim($xnombre)) . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["nombre1cliente"])) . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["nombre2cliente"])) . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["apellido1cliente"])) . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["apellido2cliente"])) . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["direccion"])) . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["direccionnot"])) . "'",
                "'" . trim($_SESSION["tramite"]["idmunicipio"]) . "'",
                "'" . trim($_SESSION["tramite"]["idmunicipionot"]) . "'",
                "'" . trim($_SESSION["tramite"]["pais"]) . "'",
                "'" . trim($_SESSION["tramite"]["lenguaje"]) . "'",
                "'" . trim($_SESSION["tramite"]["telefono"]) . "'",
                "'" . trim($_SESSION["tramite"]["movil"]) . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["email"])) . "'",
                "'" . trim($_SESSION["tramite"]["zonapostal"]) . "'",
                "'" . trim($_SESSION["tramite"]["codposcom"]) . "'",
                "'" . trim($_SESSION["tramite"]["codposnot"]) . "'",
                "'" . trim($_SESSION["tramite"]["codigoregimen"]) . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["responsabilidadtributaria"])) . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["responsabilidadfiscal"])) . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["codigoimpuesto"])) . "'",
                "'" . addslashes(trim($_SESSION["tramite"]["nombreimpuesto"])) . "'",
                $_SESSION["tramite"]["numeroliquidacion"],
                "'" . trim($_SESSION["tramite"]["tipotramite"]) . "'",
                $totalesterecibo,
                $pagoprepago,
                $pagoafiliado,
                $pagoefectivo,
                $pagocheque,
                $pagoconsignacion,
                $pagoqr,
                $pagoach,
                $pagovisa,
                $pagomastercard,
                $pagocredencial,
                $pagoamerican,
                $pagodiners,
                $pagotdebito,
                "'" . $_SESSION["tramite"]["numeroautorizacion"] . "'",
                "'" . $_SESSION["tramite"]["numerocheque"] . "'",
                "'" . $_SESSION["tramite"]["idfranquicia"] . "'",
                "'" . $_SESSION["tramite"]["nombrefranquicia"] . "'",
                "'" . $_SESSION["tramite"]["idcodban"] . "'",
                "'" . addslashes($_SESSION["tramite"]["nombrebanco"]) . "'",
                $_SESSION["tramite"]["alertaid"],
                "'" . $_SESSION["tramite"]["alertaservicio"] . "'",
                $_SESSION["tramite"]["alertavalor"],
                "'" . $_SESSION["tramite"]["proyectocaja"] . "'",
                "'" . $_SESSION["tramite"]["rues_numerounico"] . "'",
                "'" . $_SESSION["tramite"]["rues_numerointerno"] . "'",
                "'" . $_SESSION["tramite"]["tipotramiterue"] . "'",
                "'" . $_SESSION["tramite"]["idformapago"] . "'",
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
                if ($intentos > 10) {
                    $seguir = 'no';
                    $creo = 'no';
                }
            }
        }


        if ($creo == 'no') {
            $_SESSION["generales"]["mensajeerror"] = 'No fue posible crear el recibo de caja para consecutivo : ' . $recx;
            \logApi::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', $_SESSION["generales"]["mensajeerror"]);
            return false;
        }

        // ************************************************************************************************ //
        // Actualiza el consecutivo en claves valor
        // ************************************************************************************************ //	
        \funcionesRegistrales::actualizarMregSecuenciasAsentarRecibo($mysqli, $tclave, $rec);

        // 2020-01-09: JINT
        $sitcredito = 'no';
        if (
                $_SESSION["tramite"]["pagovisa"] != 0 ||
                $_SESSION["tramite"]["pagomastercard"] != 0 ||
                $_SESSION["tramite"]["pagocredencial"] != 0 ||
                $_SESSION["tramite"]["pagoamerican"] != 0 ||
                $_SESSION["tramite"]["pagodiners"] != 0
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
        foreach ($_SESSION["tramite"]["liquidacion"] as $d) {
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
                        "'" . $_SESSION["tramite"]["tipogasto"] . "'",
                        "'" . ltrim($d["ano"], "0") . "'",
                        intval($d["cantidad"]),
                        doubleval($d["valorbase"]),
                        doubleval($d["porcentaje"]),
                        doubleval($d["valorservicio"]),
                        "'" . ltrim($_SESSION["tramite"]["identificacioncliente"], "0") . "'",
                        "'" . addslashes($xnombre) . "'",
                        "'" . $organizacion . "'",
                        "'" . $categoria . "'",
                        "'" . $idtipodoc . "'",
                        "'" . $numdoc . "'",
                        "'" . addslashes($origendoc) . "'",
                        "'" . $fechadoc . "'",
                        "'" . $d["expedienteafiliado"] . "'",
                        "'" . $fecharenovacionaplicable . "'",
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
                        \logApi::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Error insertando en mreg_recibosgenerados_detalle : ' . $_SESSION["generales"]["mensajeerror"]);
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
                \logApi::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
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
                \logApi::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
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
                \logApi::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }
        
        if ($pagoqr != 0) {
            $arrValores = array(
                "'" . $recx . "'",
                "'8'",
                $pagoqr,
                "'" . $fps["consignacionbanco"] . "'",
                "'" . $fps["consignacion"] . "'"
            );
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_fpago', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
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
                \logApi::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
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
                \logApi::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
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
                \logApi::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
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
                \logApi::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
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
                \logApi::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
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
                \logApi::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
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
                \logApi::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
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
        if (!isset($_SESSION["tramite"]["camaradestino"])) {
            $_SESSION["tramite"]["camaradestino"] = '';
        }
        if (!isset($_SESSION["tramite"]["camaraorigen"])) {
            $_SESSION["tramite"]["camaraorigen"] = '';
        }
        if (!isset($_SESSION["tramite"]["numerointernorue"])) {
            $_SESSION["tramite"]["numerointernorue"] = '';
        }
        if (!isset($_SESSION["tramite"]["numerounicorue"])) {
            $_SESSION["tramite"]["numerounicorue"] = '';
        }
        if (!isset($_SESSION["tramite"]["proyecto"])) {
            $_SESSION["tramite"]["proyecto"] = '001';
        }

        foreach ($_SESSION["tramite"]["liquidacion"] as $d) {

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
            if ($_SESSION["tramite"]["tipogasto"] == '7') {
                $cc = $_SESSION["tramite"]["camaradestino"];
            }
            if ($_SESSION["tramite"]["tipogasto"] == '8') {
                $cc = $_SESSION["tramite"]["camaraorigen"];
            }

            $fp = '1';
            switch ($_SESSION["tramite"]["idformapago"]) {
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
                case "12":
                    $fp = '8';
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

            //
            $namePrint = $xnombre;
            $namex = $xnombre;
            /*
            if ($_SESSION["tramite"]["idtipoidentificacioncliente"] != '2' && $_SESSION["tramite"]["apellido1cliente"] == '' && $_SESSION["tramite"]["apellido2cliente"] == '' && $_SESSION["tramite"]["nombre2cliente"] == '') {
                $namex = $_SESSION["tramite"]["nombrecliente"];
            } else {
                if (trim($_SESSION["tramite"]["apellido1pagador"]) != '') {
                    $namex .= $_SESSION["tramite"]["apellido1pagador"];
                }
                if (trim($_SESSION["tramite"]["apellido2pagador"]) != '') {
                    if ($namex != '') {
                        $namex .= ' ';
                    }
                    $namex .= $_SESSION["tramite"]["apellido2pagador"];
                }
                if (trim($_SESSION["tramite"]["nombre1pagador"]) != '') {
                    if ($namex != '') {
                        $namex .= ' ';
                    }
                    $namex .= $_SESSION["tramite"]["nombre1pagador"];
                }
                if (trim($_SESSION["tramite"]["nombre2pagador"]) != '') {
                    if ($namex != '') {
                        $namex .= ' ';
                    }
                    $namex .= $_SESSION["tramite"]["nombre2pagador"];
                }
            }
            if ($namex != $_SESSION["tramite"]["nombrepagador"] && strlen($namex) > $_SESSION["tramite"]["nombrepagador"]) {
                $namePrint = $namex;
            }
            */
            //

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
                        "'" . $_SESSION["tramite"]["numerofactura"] . "'",
                        "'" . $fecha . "'",
                        "'" . $hora . "'",
                        "'" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "'",
                        "'" . ltrim($_SESSION["tramite"]["identificacioncliente"], "0") . "'",
                        "'" . addslashes(trim($namePrint)) . "'",
                        "'" . $_SESSION["generales"]["cajero"] . "'",
                        "'" . $_SESSION["tramite"]["sede"] . "'",
                        "'" . $d["ccos"] . "'", // Ccos
                        "''", // Unidad
                        "''", // Servicio
                        "'" . $d["idservicio"] . "'",
                        "''", // Servicio descuento
                        intval($d["cantidad"]),
                        doubleval($d["valorservicio"]),
                        "'" . $_SESSION["tramite"]["tipogasto"] . "'",
                        doubleval($d["valorbase"]),
                        "'001'",
                        0,
                        "'" . $cc . "'",
                        "'" . ltrim($d["expediente"], "0") . "'",
                        doubleval($d["valorbase"]),
                        "'" . $d["ano"] . "'",
                        "'" . $fp . "'",
                        "'" . addslashes(substr(trim($_SESSION["tramite"]["apellido1cliente"]), 0, 50)) . "'",
                        "'" . addslashes(substr(trim($_SESSION["tramite"]["apellido2cliente"]), 0, 50)) . "'",
                        "'" . addslashes(substr(trim($_SESSION["tramite"]["nombre1cliente"]), 0, 50)) . "'",
                        "'" . addslashes(substr(trim($_SESSION["tramite"]["nombre2cliente"]), 0, 50)) . "'",
                        "'" . $_SESSION["tramite"]["numerointernorue"] . "'",
                        "'" . $_SESSION["tramite"]["numerounicorue"] . "'",
                        "'" . $operacion . "'",
                        "'" . addslashes(trim($_SESSION["tramite"]["direccion"])) . "'",
                        "'" . addslashes(trim($_SESSION["tramite"]["idmunicipio"])) . "'",
                        "'" . addslashes(trim($_SESSION["tramite"]["telefono"])) . "'",
                        "'" . addslashes(trim($_SESSION["tramite"]["email"])) . "'",
                        "'NO'",
                        "'" . sprintf("%03s", $_SESSION["tramite"]["proyectocaja"]) . "'",
                        "'" . $d["expedienteafiliado"] . "'",
                        "'" . $fecharenovacionaplicable . "'",
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
