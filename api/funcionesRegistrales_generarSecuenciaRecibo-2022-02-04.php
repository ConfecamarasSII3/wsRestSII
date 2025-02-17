<?php

class funcionesRegistrales_generarSecuenciaRecibo {

    public static function generarSecuenciaRecibo($mysqli, $tipo = 'S', $fps = array(), $operacion = '', $fecha = '', $hora = '', $codbarras = '', $tiporegistro = '', $identificacion = '', $nombre = '', $organizacion = '', $categoria = '', $idtipodoc = '', $numdoc = '', $origendoc = '', $fechadoc = '', $estado = '', $fecharenovacionaplicable = '') {

        // ******************************************************************************* //
        // validas que todos los datos requeridos se pasen
        // ******************************************************************************* //
        if (trim($fecharenovacionaplicable) == '') {
            $fecharenovacionaplicable = $fecha;
        }
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
            \logApi::general2('reportarPago_' . date("Ymd"), '', 'Error recuperando la secuencia del recibo de caja : ' . $_SESSION["generales"]["mensajeerror"]);
            $_SESSION["generales"]["mensajeerror"] = 'Error recuperando la secuencia del recibo de caja';
            return false;
        } else {
            \logApi::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Secuencia generada : ' . $rec);
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
                'estadosms'
            );

            $_SESSION["tramite"]["tipotramiterue"] = '';
            if (substr($_SESSION["tramite"]["tipotramite"], 0, 4) == 'rues') {
                $_SESSION["tramite"]["tipotramiterue"] = substr($_SESSION["tramite"]["tipotramite"], 6);
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
                $_SESSION["tramite"]["valortotal"],
                $_SESSION["tramite"]["pagoprepago"],
                $_SESSION["tramite"]["pagoafiliado"],
                $_SESSION["tramite"]["pagoefectivo"],
                $_SESSION["tramite"]["pagocheque"],
                $_SESSION["tramite"]["pagoconsignacion"],
                $_SESSION["tramite"]["pagoach"],
                $_SESSION["tramite"]["pagovisa"],
                $_SESSION["tramite"]["pagomastercard"],
                $_SESSION["tramite"]["pagocredencial"],
                $_SESSION["tramite"]["pagoamerican"],
                $_SESSION["tramite"]["pagodiners"],
                $_SESSION["tramite"]["pagotdebito"],
                "'" . $_SESSION["tramite"]["numeroautorizacion"] . "'",
                "'" . $_SESSION["tramite"]["numerocheque"] . "'",
                "'" . $_SESSION["tramite"]["idfranquicia"] . "'",
                "'" . $_SESSION["tramite"]["nombrefranquicia"] . "'",
                "'" . $_SESSION["tramite"]["idcodban"] . "'",
                "''", // Nombre banco
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
                "'0'"
            );

            //
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores);
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
            $_SESSION["generales"]["mensajeerror"] = 'No fue posible crear el recibo de caja - 10 intentos';
            \logApi::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'No fue posible crear el recibo de caja - 10 intentos');
            return false;
        }

        // ************************************************************************************************ //
        // Actualiza el consecutivo en claves valor
        // ************************************************************************************************ //	
        \funcionesRegistrales::actualizarMregSecuenciasAsentarRecibo($mysqli, $tclave, $rec);


        // 2020-01-09: JINT
        $sitcredito = 'no';
        if ($_SESSION["tramite"]["pagovisa"] != 0 ||
                $_SESSION["tramite"]["pagomastercard"] != 0 ||
                $_SESSION["tramite"]["pagocredencial"] != 0 ||
                $_SESSION["tramite"]["pagoamerican"] != 0 ||
                $_SESSION["tramite"]["pagodiners"] != 0) {
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

            if ($tiporegistro == 'RegPro') {
                $prox = $d["expediente"];
            } else {
                $matx = $d["expediente"];
            }

            if ($d["idservicio"] != '') {
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
                    "'" . $identificacion . "'",
                    "'" . addslashes($nombre) . "'",
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
                $res = insertarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_detalle', $arrCampos, $arrValores);
                if ($res === false) {
                    \logApi::general2('generarSecuenciaReciboNuevaSii_' . date("Ymd"), '', 'Error insertando en mreg_recibosgenerados_detalle : ' . $_SESSION["generales"]["mensajeerror"]);
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
            $namePrint = $_SESSION["tramite"]["nombrepagador"];
            $namex = '';
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

//

            if ($d["idservicio"] != '') {
                $sec++;

                $arrValores = array(
                    "'" . $recx . "'",
                    "'0'",
                    "'" . $_SESSION["tramite"]["numerofactura"] . "'",
                    "'" . $fecha . "'",
                    "'" . $hora . "'",
                    "'" . $_SESSION["tramite"]["tipoidentificacionpagador"] . "'",
                    "'" . ltrim($_SESSION["tramite"]["identificacionpagador"], "0") . "'",
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
                    "'" . addslashes(substr(trim($_SESSION["tramite"]["apellido1pagador"]), 0, 50)) . "'",
                    "'" . addslashes(substr(trim($_SESSION["tramite"]["apellido2pagador"]), 0, 50)) . "'",
                    "'" . addslashes(substr(trim($_SESSION["tramite"]["nombre1pagador"]), 0, 50)) . "'",
                    "'" . addslashes(substr(trim($_SESSION["tramite"]["nombre2pagador"]), 0, 50)) . "'",
                    "'" . $_SESSION["tramite"]["numerointernorue"] . "'",
                    "'" . $_SESSION["tramite"]["numerounicorue"] . "'",
                    "'" . $operacion . "'",
                    "'" . addslashes(trim($_SESSION["tramite"]["direccionpagador"])) . "'",
                    "'" . addslashes(trim($_SESSION["tramite"]["municipiopagador"])) . "'",
                    "'" . addslashes(trim($_SESSION["tramite"]["telefonopagador"])) . "'",
                    "'" . addslashes(trim($_SESSION["tramite"]["emailpagador"])) . "'",
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

        //
        return $recx;
    }

}

?>
