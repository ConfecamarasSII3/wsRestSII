<?php

class funcionesRegistrales_generarSecuenciaReciboReversion {

    public static function generarSecuenciaReciboReversion($mysqli, $tipo = 'S', $recori = array(), $arrDet = array(), $arrTot = array(), $fps = array(), $operacion = '', $fecha = '', $hora = '', $codbarras = '', $tiporegistro = '', $identificacion = '', $nombre = '', $organizacion = '', $categoria = '', $idtipodoc = '', $numdoc = '', $origendoc = '', $fechadoc = '', $nameLog = '') {

        //
        if ($nameLog == '') {
            $nameLog = 'generarSecuenciaReciboReversion_' . date("Ymd");
        }

        // ******************************************************************************* //
        // validas que todos los datos requeridos se pasen
        // ******************************************************************************* //
        $errores = array();
        if (!empty($fps)) {
            $jx = 0;
            foreach ($fps as $d) {
                $jx++;
                if (!isset($d["tipo"])) {
                    $errores[] = 'FPago ' . $jx . ' . Tipo no reportado';
                }
                if (!isset($d["valor"])) {
                    $errores[] = 'FPago ' . $jx . ' . Valor no reportado';
                }
                if (!isset($d["banco"])) {
                    $errores[] = 'FPago ' . $jx . ' . Banco no reportado';
                }
                if (!isset($d["cheque"])) {
                    $errores[] = 'FPago ' . $jx . ' . Cheque no reportado';
                }
            }
        }

        if (!empty($errores)) {
            $_SESSION["generales"]["mensajeerror"] = '';
            foreach ($errores as $e) {
                $_SESSION["generales"]["mensajeerror"] .= $e . " ** ";
            }
            return false;
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
            \logApi::general2($nameLog, '', 'Error recuperando la secuencia del recibo de caja : ' . $_SESSION["generales"]["mensajeerror"]);
            $_SESSION["generales"]["mensajeerror"] = 'Error recuperando la secuencia del recibo de caja';
            return false;
        } else {
            \logApi::general2($nameLog, '', 'Secuencia generada : ' . $rec);
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
        while ($seguir == 'si') {
            $rec++;
            $recx = $tipo . sprintf("%09s", $rec);
            if (contarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', "recibo='" . $recx . "'") == 0) {
                $seguir = 'no';
            }
        }

        // ************************************************************************************************ //
        // Actualiza el consecutivo en claves valor
        // ************************************************************************************************ //	
        \funcionesRegistrales::actualizarMregSecuenciasAsentarRecibo($mysqli, $tclave, $rec);

        // ************************************************************************************************ //
        // Arma los arreglos para grabar el recibo
        // ************************************************************************************************ //
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
            'justificacionreversion'
        );

        $arrTot["tipotramiterue"] = '';
        if (substr($recori["tipotramite"], 0, 4) == 'rues') {
            $arrTot["tipotramiterue"] = substr($recori["tipotramite"], 6);
        }

        //
        $xnombre = '';
        if ($recori["tipoidentificacion"] == '2') {
            $xnombre = $recori["razonsocial"];
        } else {
            $xnombre = $recori["apellido1"];
            if (trim($recori["apellido2"]) != '') {
                $xnombre .= ' ' . $recori["apellido2"];
            }
            if (trim($recori["nombre1"]) != '') {
                $xnombre .= ' ' . $recori["nombre1"];
            }
            if (trim($recori["nombre2"]) != '') {
                $xnombre .= ' ' . $recori["nombre2"];
            }
        }

        //
        if (!isset($recori["justificacionreversion"])) {
            $recori["justificacionreversion"] = '';
        }

        //
        $arrValores = array(
            "'" . $recx . "'",
            "'" . $operacion . "'",
            "'" . $recori["recibo"] . "'",
            "'" . $recori["codigobarras"] . "'",
            "'" . $fecha . "'",
            "'" . $hora . "'",
            "'" . $recori["usuario"] . "'",
            "'" . $recori["tipogasto"] . "'",
            "'" . $recori["tipoidentificacion"] . "'",
            "'" . ltrim($recori["identificacion"], "0") . "'",
            "'" . addslashes(trim($xnombre)) . "'",
            "'" . addslashes(trim($recori["nombre1"])) . "'",
            "'" . addslashes(trim($recori["nombre2"])) . "'",
            "'" . addslashes(trim($recori["apellido1"])) . "'",
            "'" . addslashes(trim($recori["apellido2"])) . "'",
            "'" . addslashes(trim($recori["direccion"])) . "'",
            "'" . addslashes(trim($recori["direccionnot"])) . "'",
            "'" . trim($recori["municipio"]) . "'",
            "'" . trim($recori["municipionot"]) . "'",
            "'" . trim($recori["pais"]) . "'",
            "'" . trim($recori["lenguaje"]) . "'",
            "'" . trim($recori["telefono1"]) . "'",
            "'" . trim($recori["telefono2"]) . "'",
            "'" . addslashes(trim($recori["email"])) . "'",
            "'" . trim($recori["zonapostal"]) . "'",
            "'" . trim($recori["codposcom"]) . "'",
            "'" . trim($recori["codposnot"]) . "'",
            "'" . trim($recori["codigoregimen"]) . "'",
            "'" . addslashes(trim($recori["responsabilidadtributaria"])) . "'",
            "'" . addslashes(trim($recori["responsabilidadfiscal"])) . "'",
            "'" . addslashes(trim($recori["codigoimpuesto"])) . "'",
            "'" . addslashes(trim($recori["nombreimpuesto"])) . "'",
            $recori["idliquidacion"],
            "'" . trim($recori["tipotramite"]) . "'",
            $arrTot["valorneto"],
            $arrTot["pagoprepago"],
            $arrTot["pagoafiliado"],
            $arrTot["pagoefectivo"],
            $arrTot["pagocheque"],
            $arrTot["pagoconsignacion"],
            $arrTot["pagoach"],
            $arrTot["pagovisa"],
            $arrTot["pagomastercard"],
            $arrTot["pagocredencial"],
            $arrTot["pagoamerican"],
            $arrTot["pagodiners"],
            $arrTot["pagotdebito"],
            "''",
            "''",
            "''",
            "''",
            "''",
            "''", // Nombre banco
            0,
            "''",
            0,
            "'" . $recori["proyectocaja"] . "'",
            "'" . $recori["numerounicorue"] . "'",
            "'" . $recori["numerointernorue"] . "'",
            "'" . $arrTot["tipotramiterue"] . "'",
            "'" . $recori["idformapago"] . "'",
            "'01'",
            "'0'",
            "'0'",
            "'" . addslashes(trim($recori["justificacionreversion"])) . "'"
        );

        //
        $res = insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores);
        if ($res === false) {
            \logApi::general2($nameLog, '', 'Error insertando en mreg_recibosgenerados : ' . $_SESSION["generales"]["mensajeerror"]);
            return false;
        }

        // 2020-01-09: JINT
        $sitcredito = 'no';
        if ($arrTot["pagovisa"] != 0 ||
                $arrTot["pagomastercard"] != 0 ||
                $arrTot["pagocredencial"] != 0 ||
                $arrTot["pagoamerican"] != 0 ||
                $arrTot["pagodiners"] != 0) {
            $sitcredito = 'si';
        }

        // ************************************************************************************************ //
        // Arma el detalle del recibo y lo graba
        // ************************************************************************************************ //
        $arrCampos = array(
            'recibo',
            'secuencia',
            'fecha',
            'idservicio',
            // 'cc',
            'matricula',
            'proponente',
            // 'tipogasto',
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
            'clavecontrol'
        );
        $sec = 0;
        foreach ($arrDet as $d) {
            $matx = '';
            $prox = '';
            if ($tiporegistro == 'RegPro') {
                $prox = $d["proponente"];
            } else {
                $matx = $d["matricula"];
            }

            if ($d["idservicio"] != '') {
                if (!isset($d["ano"])) {
                    $d["ano"] = '';
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
                if (!isset($d["expedienteafectado"])) {
                    $d["expedienteafectado"] = '';
                }
                if (!isset($d["fecharenovacionaplicable"])) {
                    $d["fecharenovacionaplicable"] = '';
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
                if (!isset($d["clavecontrol"])) {
                    $d["clavecontrol"] = '';
                }

                $sec++;
                $arrValores = array(
                    "'" . $recx . "'",
                    $sec,
                    "'" . $fecha . "'",
                    "'" . $d["idservicio"] . "'",
                    // "''",
                    "'" . ltrim($matx, "0") . "'",
                    "'" . ltrim($prox, "0") . "'",
                    // "'" . $tots["tipogasto"] . "'",
                    "'" . ltrim($d["ano"], "0") . "'",
                    intval($d["cantidad"]),
                    doubleval($d["valorbase"]),
                    doubleval($d["porcentaje"]),
                    doubleval($d["valorservicio"]),
                    "'" . $identificacion . "'",
                    "'" . addslashes($nombre) . "'",
                    "'" . $organizacion . "'",
                    "'" . $categoria . "'",
                    "'" . $idtipodoc . "'",
                    "'" . $numdoc . "'",
                    "'" . addslashes($origendoc) . "'",
                    "'" . $fechadoc . "'",
                    "'" . $d["expedienteafectado"] . "'",
                    "'" . $d["fecharenovacionaplicable"] . "'",
                    doubleval($d["porcentajeiva"]),
                    doubleval($d["valoriva"]),
                    "'" . $d["servicioiva"] . "'",
                    doubleval($d["porcentajedescuento"]),
                    doubleval($d["valordescuento"]),
                    "'" . $d["serviciodescuento"] . "'",
                    "'" . $d["clavecontrol"] . "'"
                );
                $res = insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_detalle', $arrCampos, $arrValores);
                if ($res === false) {
                    \logApi::general2($nameLog, '', 'Error insertando en mreg_recibosgenerados_detalle : ' . $_SESSION["generales"]["mensajeerror"]);
                    return false;
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
        foreach ($fps as $fp) {
            $arrValores = array(
                "'" . $recx . "'",
                "'" . $fp["tipo"] . "'",
                $fp["valor"],
                "'" . $fp["banco"] . "'",
                "'" . $fp["cheque"] . "'"
            );
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_fpago', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2($nameLog, '', 'Error insertando en mreg_recibosgenerados_fpago : ' . $_SESSION["generales"]["mensajeerror"]);
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
            'clavecontrol'
        );
        $arrValores = array();
        $sec = 0;
        foreach ($arrDet as $d) {
            if (!isset($d["ccos"])) {
                $d["ccos"] = '';
            }
            if (!isset($d["ccos"])) {
                $d["ccos"] = '';
            }
            if (!isset($d["expedienteafectado"])) {
                $d["expedienteafectado"] = '';
            }

//            
            $cc = '';
            if ($recori["tipogasto"] == '7') {
                $cc = $arrTot["camaradestino"];
            }
            if ($recori == '8') {
                $cc = $arrTot["camaraorigen"];
            }

//
            $expediente = '';
            if ($tiporegistro == 'RegPro') {
                $expediente = $d["proponente"];
            } else {
                $expediente = $d["matricula"];
            }

//
            $fp = '1';
            switch ($recori["idformapago"]) {
                case "02" : $fp = '2';
                    break;
                case "03" : $fp = '7';
                    break;
                case "04" : $fp = '3';
                    break;
                case "05" :
                    if ($sitcredito == 'si') {
                        $fp = '3';
                    } else {
                        $fp = '7';
                    }
                    break;
                case "06" : $fp = '5';
                    break;
                case "09" : $fp = '7';
                    break;
                case "90" : $fp = '4';
                    break;
            }

//
            $namePrint = $recori["razonsocial"];
            $namex = '';
            if (trim($recori["apellido1"]) != '') {
                $namex .= $recori["apellido1"];
            }
            if (trim($recori["apellido2"]) != '') {
                if ($namex != '') {
                    $namex .= ' ';
                }
                $namex .= $recori["apellido2"];
            }
            if (trim($recori["nombre1"]) != '') {
                if ($namex != '') {
                    $namex .= ' ';
                }
                $namex .= $recori["nombre1"];
            }
            if (trim($recori["nombre2"]) != '') {
                if ($namex != '') {
                    $namex .= ' ';
                }
                $namex .= $recori["nombre2"];
            }

//

            if ($d["idservicio"] != '') {
                $sec++;

                $arrValores = array(
                    "'" . $recx . "'",
                    "'0'",
                    "'" . $recori["recibo"] . "'",
                    "'" . $fecha . "'",
                    "'" . $hora . "'",
                    "'" . $recori["tipoidentificacion"] . "'",
                    "'" . ltrim($recori["identificacion"], "0") . "'",
                    "'" . addslashes(trim($namePrint)) . "'",
                    "'" . $recori["usuario"] . "'",
                    "'" . substr($recori["operacion"], 0, 2) . "'",
                    "'" . $d["ccos"] . "'", // Ccos
                    "''", // Unidad
                    "''", // Servicio
                    "'" . $d["idservicio"] . "'",
                    "''", // Servicio descuento
                    intval($d["cantidad"]),
                    doubleval($d["valorservicio"]),
                    "'" . $recori["tipogasto"] . "'",
                    doubleval($d["valorbase"]),
                    "'001'",
                    0,
                    "'" . $cc . "'",
                    "'" . ltrim($expediente, "0") . "'",
                    doubleval($d["valorbase"]),
                    "'" . $d["ano"] . "'",
                    "'" . $fp . "'",
                    "'" . addslashes(substr(trim($recori["apellido1"]), 0, 50)) . "'",
                    "'" . addslashes(substr(trim($recori["apellido2"]), 0, 50)) . "'",
                    "'" . addslashes(substr(trim($recori["nombre1"]), 0, 50)) . "'",
                    "'" . addslashes(substr(trim($recori["nombre2"]), 0, 50)) . "'",
                    "'" . $recori["numerointernorue"] . "'",
                    "'" . $recori["numerounicorue"] . "'",
                    "'" . $operacion . "'",
                    "'" . addslashes(trim($recori["direccion"])) . "'",
                    "'" . addslashes(trim($recori["municipio"])) . "'",
                    "'" . addslashes(trim($recori["telefono1"])) . "'",
                    "'" . addslashes(trim($recori["email"])) . "'",
                    "'NO'",
                    "'" . sprintf("%03s", $recori["proyectocaja"]) . "'",
                    "'" . $d["expedienteafectado"] . "'",
                    "'" . $d["clavecontrol"] . "'"
                );
                $res = insertarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', $arrCampos, $arrValores);
                if ($res === false) {
                    \logApi::general2($nameLog, '', 'Error insertando en mreg_est_recibos : ' . $_SESSION["generales"]["mensajeerror"]);
                }
            }
        }

//
        return $recx;
    }


}

?>
