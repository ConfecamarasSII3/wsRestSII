<?php

class funcionesRegistrales_grabarRegistroAnulacion {

    public static function grabarRegistroAnulacion($mysqli, $cb, $estado, $nameLog = '', $idmotivoe = '', $motivoe = '', $idusuarioe = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonPredefiniciones.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesCFE.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genWord.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/template.class.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/librerias/presentacion/presentacion.class.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');

        //
        if ($nameLog == '') {
            $nameLog = 'grabarRegistroAnulacion_' . date ("Ymd");
        }
        
        //
        if (trim($cb["recibo"]) == '') {
            \logApi::general2($nameLog, '', 'No se reportó recibo a reversar');
            return false;
        }

        //
        $rec = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', "recibo='" . $cb["recibo"] . "'");
        if ($rec === false || empty($rec)) {
            \logApi::general2($nameLog, '', 'No se encontro el recibo a reversar en la BD ' . $cb["recibo"]);
            return false;
        }

        //
        if ($rec["estado"] != '01' && $rec["estado"] != '02') {
            \logApi::general2($nameLog, '', 'El recibo ' . $cb["recibo"] . ' no está en un etsado valido que permita la reversion (' .$rec["estado"] . ')');
            return false;
        }

        //
        $tipoAnulacion = 'NORMAL';
        if ($estado == 'REFACTURACION') {
            $tipoAnulacion = 'REFACTURACION';
        }

        // Localiza en los parámetros de control cuales son los servicios a utilizar para el cuadre contable de las notas de reversion
        $servrevpub = '07500001';
        $servrevpri = '07500002';
        $servrevimppub = '07500003';
        $servrevimppri = '07500004';

        //
        $notareversion = '';
        $idliquidacion = 0;
        $tipotramite = '';

        // Encuentra liquidacion
        $arrLiq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "numerorecibo='" . $cb["recibo"] . "'");
        if ($arrLiq && !empty($arrLiq)) {
            $idliquidacion = $arrLiq["idliquidacion"];
            $tipotramite = $arrLiq["tipotramite"];
        }

        \logApi::general2($nameLog, '', 'Obtiene liquidación : ' . $idliquidacion);

        // Encuentra el usuario seleccionado para crear la nota de reversion
        $fechaRecibo = '';
        $horaRecibo = '';
        $recibo = '';
        $operacion = '';

        // Si el control de recibos lo tiene el SII entonces primero que todo registra la nota en SII
        // Si tipo de anulación es normal, entonces genera la nota de revsersion

        $arrTot = array();
        $arrDet = array();

        $fechaRecibo = date("Ymd");
        $horaRecibo = date("His");

        //
        $operacion = \funcionesRegistrales::generarSecuenciaOperacion($mysqli, $rec["usuario"], $fechaRecibo, $rec["usuario"], substr($rec["operacion"], 0, 2));
        if ($operacion === false) {
            $detalle = 'No es posible localizar la secuencia de operación para la reversion del recibo ' . $cb["recibo"];
            actualizarLogMysqliApi($mysqli, 'ERR', $_SESSION["generales"]["codigousuario"], 'grabarRegistroAnulacion', '', '', '', $detalle, '', '', '', '', '');
            \logApi::general2($nameLog, '', 'Error generando la secuencia de operacion para la reversion. No es posible generar la reversion.');
            return false;
        }

        //
        \logApi::general2($nameLog, '', 'Se genera la operación Nro. : ' . $operacion);


        //        
        $totpub = 0;
        $totpri = 0;
        $totimppub = 0;
        $totimppri = 0;
        $camori = '';
        $camdes = '';
        //
        if (trim($rec["numerointernorue"]) != '') {
            $tRue = retornarRegistroMysqliApi($mysqli, 'mreg_rue_radicacion', "numerointernorue='" . $rec["numerointernorue"] . "'");
            if ($tRue && !empty($tRue)) {
                $camori = $tRue["camaraorigen"];
                $camdes = $tRue["camaradestino"];
            }
        }

        // Arma los totales o datos generales del recibo
        $arrTot = array();
        $arrTot["idoperador"] = $rec["usuario"];
        $arrTot["idliquidacion"] = 0;
        $arrTot["sede"] = substr($rec["operacion"], 0, 2);
        $arrTot["factura"] = $rec["recibo"];
        $arrTot["idusuario"] = $rec["usuario"];
        $arrTot["usuario"] = $rec["usuario"];
        $arrTot["tipogasto"] = $rec["tipogasto"];
        $arrTot["idtipoidentificacioncliente"] = $rec["tipoidentificacion"];
        $arrTot["tipoidentificacion"] = $rec["tipoidentificacion"];
        $arrTot["identificacioncliente"] = $rec["identificacion"];
        $arrTot["identificacion"] = $rec["identificacion"];
        $arrTot["nombrepagador"] = $rec["razonsocial"];
        $arrTot["apellidopagador"] = '';
        $arrTot["nombrecliente"] = $rec["razonsocial"];
        $arrTot["apellidocliente"] = '';
        $arrTot["razonsocial"] = $rec["razonsocial"];
        $arrTot["apellidocliente"] = '';
        $arrTot["apellido1cliente"] = $rec["apellido1"];
        $arrTot["apellido2cliente"] = $rec["apellido2"];
        $arrTot["nombre1cliente"] = $rec["nombre1"];
        $arrTot["nombre2cliente"] = $rec["nombre2"];
        $arrTot["apellido1"] = $rec["apellido1"];
        $arrTot["apellido2"] = $rec["apellido2"];
        $arrTot["nombre1"] = $rec["nombre1"];
        $arrTot["nombre2"] = $rec["nombre2"];

        $arrTot["direccion"] = $rec["direccion"];
        $arrTot["municipio"] = $rec["municipio"];
        $arrTot["telefono"] = $rec["telefono1"];
        $arrTot["telefono1"] = $rec["telefono2"];
        $arrTot["movil"] = '';
        $arrTot["telefono2"] = '';
        $arrTot["email"] = $rec["email"];
        $arrTot["numeroliquidacion"] = $idliquidacion;
        $arrTot["tipotramite"] = $tipotramite;
        if ($rec["fecha"] != date("Ymd") && $tipoAnulacion == 'NORMAL') {
            $arrTot["valorneto"] = 0;
        } else {
            $arrTot["valorneto"] = $rec["valorneto"] * -1;
        }
        $arrTot["pagoprepago"] = 0;
        $arrTot["pagoafiliado"] = 0;
        $arrTot["pagoefectivo"] = 0;
        $arrTot["pagocheque"] = 0;
        $arrTot["pagoconsignacion"] = 0;
        $arrTot["pagoach"] = 0;
        $arrTot["pagopseach"] = 0;
        $arrTot["pagovisa"] = 0;
        $arrTot["pagomastercard"] = 0;
        $arrTot["pagocredencial"] = 0;
        $arrTot["pagoamerican"] = 0;
        $arrTot["pagodiners"] = 0;
        $arrTot["pagotdebito"] = 0;
        $arrTot["numeroautorizacion"] = '';
        $arrTot["numerocheque"] = '';
        $arrTot["cheque"] = '';
        $arrTot["idfranquicia"] = '';
        $arrTot["franquicia"] = '';
        $arrTot["nombrefranquicia"] = '';
        $arrTot["idcodbanco"] = '';
        $arrTot["codbanco"] = '';
        $arrTot["nombrebanco"] = '';
        $arrTot["alertaid"] = 0;
        $arrTot["alertaservicio"] = '';
        $arrTot["alertavalor"] = 0;
        $arrTot["proyectocaja"] = $rec["proyectocaja"];
        $arrTot["numerounicorue"] = $rec["numerounicorue"];
        $arrTot["numerointernorue"] = $rec["numerointernorue"];
        $arrTot["camaraorigen"] = $camori;
        $arrTot["camaradestino"] = $camdes;
        $arrTot["tipotramiterue"] = '';
        $arrTot["idformapago"] = '';
        $arrTot["estado"] = '01';

        $detRec = retornarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_detalle', "recibo='" . $cb["recibo"] . "'", "secuencia");

        // Arma el detalle de los servicios a reversar
        $arrDet = array();
        $iSec = 0;
        foreach ($detRec as $s) {
            $iSec++;
            $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $s["idservicio"] . "'");
            $arrDet[$iSec]["idservicio"] = $s["idservicio"];
            $arrDet[$iSec]["servicio"] = $s["idservicio"];
            $arrDet[$iSec]["matricula"] = $s["matricula"];
            $arrDet[$iSec]["proponente"] = $s["proponente"];
            $arrDet[$iSec]["ano"] = $s["ano"];
            $arrDet[$iSec]["cantidad"] = $s["cantidad"] * -1;
            $arrDet[$iSec]["valorbase"] = $s["valorbase"];
            $arrDet[$iSec]["porcentaje"] = $s["porcentaje"];
            $arrDet[$iSec]["valorservicio"] = $s["valorservicio"] * -1;
            $arrDet[$iSec]["identificacion"] = $s["identificacion"];
            $arrDet[$iSec]["razonsocial"] = $s["razonsocial"];
            $arrDet[$iSec]["categoria"] = '';
            $arrDet[$iSec]["organizacion"] = '';
            $arrDet[$iSec]["idtipodoc"] = '';
            $arrDet[$iSec]["numdoc"] = '';
            $arrDet[$iSec]["origendoc"] = '';
            $arrDet[$iSec]["expedienteafectado"] = $s["expedienteafectado"];
            $arrDet[$iSec]["fecharenovacionaplicable"] = $s["fecharenovacionaplicable"];
            $arrDet[$iSec]["ccos"] = '';
            $arrDet[$iSec]["porcentajeiva"] = $s["porcentajeiva"];
            $arrDet[$iSec]["valoriva"] = $s["valoriva"];
            $arrDet[$iSec]["servicioiva"] = $s["servicioiva"];
            $arrDet[$iSec]["porcentajedescuento"] = $s["porcentajedescuento"];
            $arrDet[$iSec]["valordescuento"] = $s["valordescuento"];
            $arrDet[$iSec]["serviciodescuento"] = $s["serviciodescuento"];
            $arrDet[$iSec]["clavecontrol"] = $s["clavecontrol"];

            //
            switch ($serv["idtipovalor"]) {
                case "1" : $totpub = $totpub + $s["valorservicio"];
                    break;
                case "2" : $totpri = $totpri + $s["valorservicio"];
                    break;
                case "3" : $totimppri = $totimppri + $s["valorservicio"];
                    break;
                case "4" : $totimppub = $totimppub + $s["valorservicio"];
                    break;
            }
        }

        // Si la fecha del recibo es diferente a la fecha de la devolucion
        // genera los servicios de ajuste para prevenir descuadres en caja
        // - servrevpub
        // - servrevpri
        // - servrevimppub
        // - servrevimppri
        if ($rec["fecha"] != date("Ymd") && $tipoAnulacion == 'NORMAL') {

            if ($totpub != 0) {
                $iSec++;
                $arrDet[$iSec]["idservicio"] = $servrevpub;
                $arrDet[$iSec]["servicio"] = $servrevpub;
                $arrDet[$iSec]["matricula"] = '';
                $arrDet[$iSec]["proponente"] = '';
                $arrDet[$iSec]["ano"] = '';
                $arrDet[$iSec]["cantidad"] = 1;
                $arrDet[$iSec]["porcentaje"] = 0;
                $arrDet[$iSec]["valorbase"] = 0;
                $arrDet[$iSec]["valorservicio"] = $totpub;
                $arrDet[$iSec]["identificacion"] = $arrTot["identificacion"];
                $arrDet[$iSec]["razonsocial"] = $arrTot["razonsocial"];
                $arrDet[$iSec]["categoria"] = '';
                $arrDet[$iSec]["organizacion"] = '';
                $arrDet[$iSec]["idtipodoc"] = '';
                $arrDet[$iSec]["numdoc"] = '';
                $arrDet[$iSec]["origendoc"] = '';
                $arrDet[$iSec]["fechadoc"] = '';
                $arrDet[$iSec]["expedienteafectado"] = '';
                $arrDet[$iSec]["fecharenovacionaplicable"] = '';
                $arrDet[$iSec]["ccos"] = '';
                $arrDet[$iSec]["porcentajeiva"] = 0;
                $arrDet[$iSec]["valoriva"] = 0;
                $arrDet[$iSec]["servicioiva"] = '';
                $arrDet[$iSec]["porcentajedescuento"] = 0;
                $arrDet[$iSec]["valordescuento"] = 0;
                $arrDet[$iSec]["serviciodescuento"] = '';
                $arrDet[$iSec]["clavecontrol"] = '';
            }
            if ($totpri != 0) {
                $iSec++;
                $arrDet[$iSec]["idservicio"] = $servrevpri;
                $arrDet[$iSec]["servicio"] = $servrevpri;
                $arrDet[$iSec]["matricula"] = '';
                $arrDet[$iSec]["proponente"] = '';
                $arrDet[$iSec]["ano"] = '';
                $arrDet[$iSec]["cantidad"] = 1;
                $arrDet[$iSec]["porcentaje"] = 0;
                $arrDet[$iSec]["valorbase"] = 0;
                $arrDet[$iSec]["valorservicio"] = $totpri;
                $arrDet[$iSec]["identificacion"] = $arrTot["identificacion"];
                $arrDet[$iSec]["razonsocial"] = $arrTot["razonsocial"];
                $arrDet[$iSec]["categoria"] = '';
                $arrDet[$iSec]["organizacion"] = '';
                $arrDet[$iSec]["idtipodoc"] = '';
                $arrDet[$iSec]["numdoc"] = '';
                $arrDet[$iSec]["origendoc"] = '';
                $arrDet[$iSec]["fechadoc"] = '';
                $arrDet[$iSec]["expedienteafectado"] = '';
                $arrDet[$iSec]["fecharenovacionaplicable"] = '';
                $arrDet[$iSec]["ccos"] = '';
                $arrDet[$iSec]["porcentajeiva"] = 0;
                $arrDet[$iSec]["valoriva"] = 0;
                $arrDet[$iSec]["servicioiva"] = '';
                $arrDet[$iSec]["porcentajedescuento"] = 0;
                $arrDet[$iSec]["valordescuento"] = 0;
                $arrDet[$iSec]["serviciodescuento"] = '';
                $arrDet[$iSec]["clavecontrol"] = '';
            }
            if ($totimppub != 0) {
                $iSec++;
                $arrDet[$iSec]["idservicio"] = $servrevimppub;
                $arrDet[$iSec]["servicio"] = $servrevimppub;
                $arrDet[$iSec]["matricula"] = '';
                $arrDet[$iSec]["proponente"] = '';
                $arrDet[$iSec]["ano"] = '';
                $arrDet[$iSec]["cantidad"] = 1;
                $arrDet[$iSec]["porcentaje"] = 0;
                $arrDet[$iSec]["valorbase"] = 0;
                $arrDet[$iSec]["valorservicio"] = $totimppub;
                $arrDet[$iSec]["identificacion"] = $arrTot["identificacion"];
                $arrDet[$iSec]["razonsocial"] = $arrTot["razonsocial"];
                $arrDet[$iSec]["categoria"] = '';
                $arrDet[$iSec]["organizacion"] = '';
                $arrDet[$iSec]["idtipodoc"] = '';
                $arrDet[$iSec]["numdoc"] = '';
                $arrDet[$iSec]["origendoc"] = '';
                $arrDet[$iSec]["fechadoc"] = '';
                $arrDet[$iSec]["expedienteafectado"] = '';
                $arrDet[$iSec]["fecharenovacionaplicable"] = '';
                $arrDet[$iSec]["ccos"] = '';
                $arrDet[$iSec]["porcentajeiva"] = 0;
                $arrDet[$iSec]["valoriva"] = 0;
                $arrDet[$iSec]["servicioiva"] = '';
                $arrDet[$iSec]["porcentajedescuento"] = 0;
                $arrDet[$iSec]["valordescuento"] = 0;
                $arrDet[$iSec]["serviciodescuento"] = '';
                $arrDet[$iSec]["clavecontrol"] = '';
            }
            if ($totimppri != 0) {
                $iSec++;
                $arrDet[$iSec]["idservicio"] = $servrevimppri;
                $arrDet[$iSec]["servicio"] = $servrevimppri;
                $arrDet[$iSec]["matricula"] = '';
                $arrDet[$iSec]["proponente"] = '';
                $arrDet[$iSec]["ano"] = '';
                $arrDet[$iSec]["cantidad"] = 1;
                $arrDet[$iSec]["porcentaje"] = 0;
                $arrDet[$iSec]["valorbase"] = 0;
                $arrDet[$iSec]["valorservicio"] = $totimppri;
                $arrDet[$iSec]["identificacion"] = $arrTot["identificacion"];
                $arrDet[$iSec]["razonsocial"] = $arrTot["razonsocial"];
                $arrDet[$iSec]["categoria"] = '';
                $arrDet[$iSec]["organizacion"] = '';
                $arrDet[$iSec]["idtipodoc"] = '';
                $arrDet[$iSec]["numdoc"] = '';
                $arrDet[$iSec]["origendoc"] = '';
                $arrDet[$iSec]["fechadoc"] = '';
                $arrDet[$iSec]["expedienteafectado"] = '';
                $arrDet[$iSec]["fecharenovacionaplicable"] = '';
                $arrDet[$iSec]["ccos"] = '';
                $arrDet[$iSec]["porcentajeiva"] = 0;
                $arrDet[$iSec]["valoriva"] = 0;
                $arrDet[$iSec]["servicioiva"] = '';
                $arrDet[$iSec]["porcentajedescuento"] = 0;
                $arrDet[$iSec]["valordescuento"] = 0;
                $arrDet[$iSec]["serviciodescuento"] = '';
                $arrDet[$iSec]["clavecontrol"] = '';
            }
        } else {
            \logApi::general2($nameLog, '', 'La fecha del recibo es diferente a la fecha de anulación.');
        }

        // Relación de formas de pago
        $detFp = retornarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_fpago', "recibo='" . $rec["recibo"] . "'", "id");
        $arrFp = array();
        $iFp = 0;
        foreach ($detFp as $fp) {
            $iFp++;
            $arrFp[$iFp]["tipo"] = $fp["tipo"];
            $arrFp[$iFp]["valor"] = $fp["valor"] * -1;
            $arrFp[$iFp]["banco"] = $fp["banco"];
            $arrFp[$iFp]["cheque"] = $fp["cheque"];
        }

        // Genera la secuencia de la nota de reversion y crea la nota en 
        // - mreg_recibosgenerados 
        // - mreg_recibosgenerados_detalle
        // - mreg_est_recibos
        $recibo = \funcionesRegistrales::generarSecuenciaReciboReversion($mysqli, 'M', $rec, $arrDet, $arrTot, $arrFp, $operacion, $fechaRecibo, $horaRecibo);
        if ($recibo === false) {
            $detalle = 'Error en generación de la nota de reversion : ' . $_SESSION["generales"]["mensajeerror"] . $cb["recibo"];
            actualizarLogMysqliApi($mysqli, 'ERR', $_SESSION["generales"]["codigousuario"], 'grabarRegistroAnulacion', '', '', '', $detalle, '', '', '', '', '');
            \logApi::general2($nameLog, '', 'Error en generación del recibo : ' . $_SESSION["generales"]["mensajeerror"]);
            return false;
        }

        //
        \logApi::general2($nameLog, '', 'Se genera la nota de reversión Nro. : ' . $recibo);
        $detalle = 'Se genera la nota de reversión Nro. : ' . $recibo;
        actualizarLogMysqliApi($mysqli, '002', $_SESSION["generales"]["codigousuario"], 'grabarRegistroAnulacion', '', '', '', $detalle, '', '', '', '', '');
            
        //
        $notareversion = $recibo;

        // Actualiza el recibo previamente generado a estado 03 en mreg_recibosgenerados
        if ($estado == 'REFACTURACION') {
            $arrCampos = array('estado');
            $arrValores = array("'04'");
        } else {
            $arrCampos = array('estado');
            $arrValores = array("'03'");
        }
        regrabarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', $arrCampos, $arrValores, "recibo='" . $rec["recibo"] . "'");

        // en mreg_est_recibos marca en 2 el ctranulacioon
        $arrCampos = array('ctranulacion');
        $arrValores = array("'2'");
        regrabarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', $arrCampos, $arrValores, "numerorecibo='" . $rec["recibo"] . "'");

        $resultado["codigoError"] = '0000';

        if ($idmotivoe == '') {
            $idmotivoe = '90';
        }
        if ($motivoe == '') {
            $motivoe = 'REVERSADO (" . $estado . ")';
        }

        // ****************************************************************************************************** //
        // Actualiza tabla de anulaciones / reversiones
        // ****************************************************************************************************** //
        $arrCampos = array(
            'fecha',
            'hora',
            'idusuario',
            'tipogeneral',
            'recibo',
            'operacion',
            'expediente',
            'notareversion',
            'idmotivo',
            'motivo'
        );
        $arrValues = array(
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . $_SESSION["generales"]["codigousuario"] . "'",
            "'RECIBOS'",
            "'" . $rec["recibo"] . "'",
            "'" . $rec["operacion"] . "'",
            "''",
            "'" . $notareversion . "'",
            "'" . $idmotivoe . "'",
            "'" . $motivoe . "'"
        );
        insertarRegistrosMysqliApi($mysqli, 'mreg_anulaciones', $arrCampos, $arrValues);

        // ********************************************************************************************
        // 2015-08-05 : JINT
        // Anula los certificados virtuales que se hubieren generado con el recibo
        // ********************************************************************************************
        // if ($estado != 'REFACTURACION') {
        $arrCampos = array('estado');
        $arrValores = array("'9'");
        regrabarRegistrosMysqliApi($mysqli, 'mreg_certificados_virtuales', $arrCampos, $arrValores, "recibo='" . $rec["recibo"] . "'");
        // }
        // ************************************************************************************* //
        // Otras afectaciones
        // ************************************************************************************* //
        $mats = array();
        $arrRecs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "numerorecibo='" . $rec["recibo"] . "' and (servicio between '01020201' and '01020208')", "matricula");
        foreach ($arrRecs as $s) {
            if (!isset($mats[$s["matricula"]])) {
                $mats[$s["matricula"]] = $s["matricula"];
            }
        }
        if (!empty($mats)) {
            foreach ($mats as $m) {
                $fecren = '';
                $anoren = '';
                $personal = 0;
                $pcttem = 0;
                $actvin = 0;
                $patrimonio = 0;
                $actcte = 0;
                $actnocte = 0;
                $actfij = 0;
                $fijnet = 0;
                $actval = 0;
                $actotr = 0;
                $acttot = 0;
                $pascte = 0;
                $paslar = 0;
                $pastot = 0;
                $patnet = 0;
                $paspat = 0;
                $balsoc = 0;
                $ingope = 0;
                $ingnoope = 0;
                $gasope = 0;
                $gasadm = 0;
                $gasnoope = 0;
                $cosven = 0;
                $gtoven = 0;
                $gasint = 0;
                $gasimp = 0;
                $utiope = 0;
                $utinet = 0;
                $arrRecs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $m . "' and (servicio between '01020201' and '01020208') and ctranulacion='0'", "numerorecibo");
                if ($arrRecs && !empty($arrRecs)) {
                    foreach ($arrRecs as $r) {
                        $fecren = $r["fecoperacion"];
                        $anoren = $r["anorenovacion"];
                    }
                }
                $arrFins = retornarRegistrosMysqliApi($mysqli, 'mreg_est_financiera', "matricula='" . $m . "' and anodatos='" . $anoren . "'", "anodatos,fechadatos");
                if ($arrFins && !empty($arrFins)) {
                    foreach ($arrFins as $f) {
                        if ($f["anodatos"] == $anoren && $f["fechadatos"] <= $fecren) {
                            $personal = $f["personal"];
                            $pcttem = $f["pcttemp"];
                            $actvin = $f["actvin"];
                            $patrimonio = $f["patrimonio"];
                            $actcte = $f["actcte"];
                            $actnocte = $f["actnocte"];
                            $actfij = $f["actfij"];
                            $fijnet = $f["fijnet"];
                            $actval = $f["actval"];
                            $actotr = $f["actotr"];
                            $acttot = $f["acttot"];
                            $pascte = $f["pascte"];
                            $paslar = $f["paslar"];
                            $pastot = $f["pastot"];
                            $patnet = $f["patnet"];
                            $paspat = $f["paspat"];
                            $balsoc = $f["balsoc"];
                            $ingope = $f["ingope"];
                            $ingnoope = $f["ingnoope"];
                            $gasope = $f["gasope"];
                            $gasadm = $f["gasadm"];
                            $gasnoope = $f["gasnoope"];
                            $cosven = $f["cosven"];
                            $gtoven = $f["gtoven"];
                            $gasint = $f["gasint"];
                            $gasimp = $f["gasimp"];
                            $utiope = $f["utiope"];
                            $utinet = $f["utinet"];
                        }
                    }
                }

                // Graba mreg_est_inscritos
                $arrCampos = array(
                    'fecrenovacion',
                    'ultanoren',
                    'anodatos',
                    'fecdatos',
                    'personal',
                    'personaltemp',
                    'actcte',
                    'actnocte',
                    'actfij',
                    'fijnet',
                    'actval',
                    'actotr',
                    'acttot',
                    'pascte',
                    'paslar',
                    'pastot',
                    'pattot',
                    'paspat',
                    'balsoc',
                    'ingope',
                    'ingnoope',
                    'gasope',
                    'gasnoope',
                    'gasint',
                    'gasimp',
                    'gtoven',
                    'gtoadm',
                    'utiope',
                    'utinet',
                    'cosven',
                    'actvin',
                    'patrimonio',
                    'fecactualizacion',
                    'compite360',
                    'rues',
                    'ivc'
                ); // 32
                $arrValores = array(
                    "'" . $fecren . "'",
                    "'" . $anoren . "'",
                    "'" . $anoren . "'",
                    "'" . $fecren . "'",
                    doubleval($personal),
                    doubleval($pcttem),
                    doubleval($actcte),
                    doubleval($actnocte),
                    doubleval($actfij),
                    doubleval($fijnet),
                    doubleval($actval),
                    doubleval($actotr),
                    doubleval($acttot),
                    doubleval($pascte),
                    doubleval($paslar),
                    doubleval($pastot),
                    doubleval($patnet),
                    doubleval($paspat),
                    doubleval($balsoc),
                    doubleval($ingope),
                    doubleval($ingnoope),
                    doubleval($gasope),
                    doubleval($gasnoope),
                    doubleval($gasint),
                    doubleval($gasimp),
                    doubleval($gtoven),
                    doubleval($gasnoope),
                    doubleval($utiope),
                    doubleval($utinet),
                    doubleval($cosven),
                    doubleval($actvin),
                    doubleval($patrimonio),
                    "'" . date("Ymd") . "'",
                    "'NO'",
                    "'NO'",
                    "'NO'"
                );
                unset($_SESSION["expedienteactual"]);
                regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $m . "'");

                // ****************************************************************************************************** //
                // Borra informacion financiera posterior a la ultima renovación
                // ****************************************************************************************************** //
                if ($fecren != '' && $anoren != '') {
                    $arrFins = retornarRegistrosMysqliApi($mysqli, 'mreg_est_financiera', "matricula='" . $m . "'", "anodatos,fechadatos");
                    if ($arrFins && !empty($arrFins)) {
                        foreach ($arrFins as $f) {
                            if ($f["anodatos"] > $anoren || ($f["anodatos"] == $anoren && $f["fechadatos"] > $fecren)) {
                                borrarRegistrosMysqliApi($mysqli, 'mreg_est_financiera', "id=" . $f["id"]);
                            }
                        }
                    }
                }
            }
        }

        //
        if ($tipoAnulacion != 'REFACTURACION') {
            if (ltrim($rec["codigobarras"], "0") != '') {
                $arrCampos = array(
                    'estadofinal',
                    'operadorfinal',
                    'fechaestadofinal',
                    'horaestadofinal'
                );
                $arrValores = array(
                    "'99'",
                    "'" . $_SESSION["mantrec"]["opesirep"] . "'",
                    "'" . date("Ymd") . "'",
                    "'" . date("His") . "'"
                );
                regrabarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', $arrCampos, $arrValores, "codigobarras=" . ltrim($rec["codigobarras"], "0"));
                $detalle = 'Cambio estado del codigo de barras No. ' . $rec["codigobarras"] . ', estado final: 99, operador final: ' . $_SESSION["mantrec"]["opesirep"];
                actualizarLogMysqliApi($mysqli, '069', $_SESSION["generales"]["codigousuario"], 'grabarRegistroAnulacion', '', '', '', $detalle, '', '');

                $arrCampos = array(
                    'codigobarras',
                    'fecha',
                    'hora',
                    'estado',
                    'impresiones',
                    'formareparto',
                    'operador',
                    'sucursal'
                );
                $arrValores = array(
                    ltrim($rec["codigobarras"], "0"),
                    "'" . date("Ymd") . "'",
                    "'" . date("His") . "'",
                    "'99'",
                    "'00'",
                    "''",
                    "'" . $_SESSION["generales"]["codigousuario"] . "'",
                    "'90'" // Sede RUE
                );
                insertarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_documentos', $arrCampos, $arrValores);
            }


            // ********************************************************************************************
            // 2017-08-16: JINT
            // Reporta a docxflow la anulación del código de barras
            // ********************************************************************************************
            if (ltrim($rec["codigobarras"], "0") != '') {
                if (SISTEMA_IMAGENES_REGISTRO == 'DOCXFLOW') {
                    if (defined('DOCXFLOW_SERVER') && trim(DOCXFLOW_SERVER) != '') {
                        $resDocXflow = \funcionesRegistrales::docXflowNotificarCambioEstado($mysqli, ltrim($rec["codigobarras"], "0"), '00');
                    }
                }
            }
        }

        // ************************************************************************************************************* //
        // 2019-07-31: JINT: si tiene servicios de renovación, recalcula la fecha de renovación de mreg-est-inscritos
        // ************************************************************************************************************* //
        foreach ($arrRecs as $s) {
            $retserv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $s["servicio"] . "'");
            if ($retserv && !empty($retserv)) {
                if ($retserv["tipoingreso"] == '02' ||
                        $retserv["tipoingreso"] == '03' ||
                        $retserv["tipoingreso"] == '12' ||
                        $retserv["tipoingreso"] == '13') {
                    if (trim($s["idmatricula"]) != '') {
                        $histopagos = encontrarHistoricoPagosMysqliApi($mysqli, trim($s["matricula"]), array(), array(), array());
                        if ($histopagos["fecultren"] != '') {
                            \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, trim($s["matricula"]), 'fecrenovacion', $histopagos["fecultren"], 'varchar', '', 'anulacionRecibos');
                        }
                        if ($histopagos["ultanoren"] != '') {
                            \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, trim($s["matricula"]), 'ultanoren', $histopagos["ultanoren"], 'varchar', '', 'anulacionRecibos');
                        }
                    }
                }
            }
        }

        //
        if (defined('CFE_FECHA_INICIAL') && CFE_FECHA_INICIAL != '' && CFE_FECHA_INICIAL <= date("Ymd")) {
            $resCfe = \funcionesCFE::seleccionRecibosCFE($mysqli, '00000000', '000000', '000000', $notareversion);
            \logApi::general2($nameLog, $notareversion, utf8_encode('ejecuto envio nota de reversion a CFE - Codigo : ' . $resCfe["codigoError"] . ' - Mensaje : ' . $resCfe["mensajeError"]));
            $detalle = 'ejecuto envio nota de reversion a CFE - Codigo : ' . $resCfe["codigoError"] . ' - Mensaje : ' . $resCfe["mensajeError"];
            actualizarLogMysqliApi($mysqli, '002', $_SESSION["generales"]["codigousuario"], 'grabarRegistroAnulacion', '', '', '', $detalle, '', '', '', '', '');
        }

        //
        return $notareversion;
    }

}

?>
