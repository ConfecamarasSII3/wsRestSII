<?php

class funcionesRegistrales_consumirWsVRRECREC {

    public static function consumirWsVRRECREC($mysqli = null, $rec = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');

        //
        $rec = trim($rec);

        //
        if ($rec == '') {
            $resultado = array();
            $resultado["codigoError"] = '9990';
            $resultado["msgError"] = 'Debe indicar el numero del recibo';
            return $resultado;
        }

        // Consume el ws del SIREP
        $buscarEnSii = 'si';
        $resultado = array();
        $resultado["codigoError"] = '0000';
        $resultado["msgError"] = '';

        //
        $resultado["datos"] = array();

        //
        $cerrarMysql = 'no';
        if ($mysqli === null) {
            $mysqli = conexionMysqliApi();
            $cerrarMysql = 'si';
        }

        //
        $arrTem = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', "recibo='" . $rec . "'");

        //
        if ($arrTem === false) {
            if ($cerrarMysql == 'si') {
                $mysqli->close();
            }
            $resultado["codigoError"] = '9990';
            $resultado["msgError"] = 'Error en conexión';
            return $resultado;
        }
        
        //
        if (empty($arrTem)) {
            if ($cerrarMysql == 'si') {
                $mysqli->close();
            }
            $resultado["codigoError"] = '9990';
            $resultado["msgError"] = 'El recibo no fue localizado en el sistema SII (' . $rec . ')';
            return $resultado;
        }

        //
        $resultado["datos"] = $arrTem;
        $resultado["datos"]["control"] = 'MODIFICACION';
        $resultado["datos"]["archivo"] = '';
        $resultado["datos"]["operador"] = '';
        $resultado["datos"]["sucursal"] = '';
        if (strlen($arrTem["operacion"]) == 12) {
            $resultado["datos"]["sucursal"] = substr($arrTem["operacion"], 0, 2);
            $resultado["datos"]["operador"] = substr($arrTem["operacion"], 2, 3);
        } else {
            list($xsed, $xope, $xfec, $xcons) = explode("-", $arrTem["operacion"]);
            $resultado["datos"]["sucursal"] = $xsed;
            $resultado["datos"]["operador"] = $xope;
        }
        $resultado["datos"]["fecopera"] = $arrTem["fecha"];
        $resultado["datos"]["horpago"] = $arrTem["hora"];

        //
        $resultado["datos"]["ctranul"] = '';
        switch ($arrTem["estado"]) {
            case "01":
                $resultado["datos"]["ctranul"] = '0';
                break;
            case "02":
                $resultado["datos"]["ctranul"] = '0';
                break;
            case "03":
                $resultado["datos"]["ctranul"] = '2';
                break;
            case "99":
                $resultado["datos"]["ctranul"] = '1';
                break;
        }

        //
        $resultado["datos"]["numfactura"] = $arrTem["factura"];
        $resultado["datos"]["numrecibo"] = $arrTem["recibo"];
        $resultado["datos"]["idclase"] = $arrTem["tipoidentificacion"];
        $resultado["datos"]["numid"] = $arrTem["identificacion"];
        $resultado["datos"]["nompago"] = $arrTem["razonsocial"];
        $resultado["datos"]["totpago"] = $arrTem["valorneto"];
        $resultado["datos"]["idmon"] = '001';
        $resultado["datos"]["ben1429"] = '';
        $resultado["datos"]["ctrreliq"] = 0;
        if ($arrTem["idliquidacion"] != 0) {
            $aLiq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "idliquidacion=" . $arrTem["idliquidacion"]); 
            if ($aLiq && $aLiq["reliquidacion"] == 'si') {
                $resultado["datos"]["ctrreliq"] = '1';
            }
        }
        $resultado["datos"]["numemp"] = 0;
        $resultado["datos"]["munrec"] = $arrTem["municipio"];
        $resultado["datos"]["telefono"] = $arrTem["telefono1"];
        $resultado["datos"]["telefono1"] = $arrTem["telefono1"];
        $resultado["datos"]["telefono2"] = $arrTem["telefono2"];
        $resultado["datos"]["movil"] = $arrTem["telefono2"];
        $resultado["datos"]["usuariosirep"] = $arrTem["usuario"];
        $resultado["datos"]["usuario"] = $arrTem["usuario"];
        $resultado["datos"]["numunico"] = $arrTem["numerounicorue"];
        $resultado["datos"]["numinterno"] = $arrTem["numerointernorue"];
        $resultado["datos"]["camaraorigen"] = '';
        $resultado["datos"]["camaradestino"] = '';
        if ($arrTem["numerointernorue"] != '') {
            $resultado["datos"]["camaraorigen"] = substr($arrTem["numerointernorue"], 17, 2);
            $resultado["datos"]["camaradestino"] = substr($arrTem["numerointernorue"], 19, 2);
        }
        $resultado["datos"]["codbarras"] = array();
        $resultado["datos"]["servicios"] = array();
        $resultado["datos"]["fp"] = array();
        $resultado["datos"]["xml"] = '';

        //
        $resultado["datos"]["codbarras"][1] = $arrTem["codigobarras"];

        //
        $arrTem1 = retornarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_detalle', "recibo='" . $rec . "'", "secuencia");
        $iServ = 0;
        foreach ($arrTem1 as $t1) {
            $iServ++;
            $resultado["datos"]["servicios"][$iServ]["idserv"] = $t1["idservicio"];
            $resultado["datos"]["servicios"][$iServ]["vrserv"] = $t1["valorservicio"];
            $resultado["datos"]["servicios"][$iServ]["cntserv"] = $t1["cantidad"];
            $resultado["datos"]["servicios"][$iServ]["ctrgtoadmt"] = $arrTem["tipogasto"];
            $resultado["datos"]["servicios"][$iServ]["vracti"] = $t1["valorbase"];
            $resultado["datos"]["servicios"][$iServ]["porcentaje"] = $t1["porcentaje"];
            $resultado["datos"]["servicios"][$iServ]["idmatricula"] = $t1["matricula"];
            $resultado["datos"]["servicios"][$iServ]["idproponente"] = $t1["proponente"];
            $resultado["datos"]["servicios"][$iServ]["anorenova"] = $t1["ano"];
            $resultado["datos"]["servicios"][$iServ]["identificacion"] = $t1["identificacion"];
            $resultado["datos"]["servicios"][$iServ]["razonsocial"] = $t1["razonsocial"];
            $resultado["datos"]["servicios"][$iServ]["categoria"] = $t1["categoria"];
            $resultado["datos"]["servicios"][$iServ]["organizacion"] = $t1["organizacion"];
            $resultado["datos"]["servicios"][$iServ]["idtipodoc"] = $t1["idtipodoc"];
            $resultado["datos"]["servicios"][$iServ]["numdoc"] = $t1["numdoc"];
            $resultado["datos"]["servicios"][$iServ]["origendoc"] = $t1["origendoc"];
            $resultado["datos"]["servicios"][$iServ]["fechadoc"] = $t1["fechadoc"];
            $resultado["datos"]["servicios"][$iServ]["expedienteafectado"] = $t1["expedienteafectado"];
            $resultado["datos"]["servicios"][$iServ]["fecharenovacionaplicable"] = $t1["fecharenovacionaplicable"];
            $resultado["datos"]["servicios"][$iServ]["porcentajeiva"] = $t1["porcentajeiva"];
            $resultado["datos"]["servicios"][$iServ]["valoriva"] = $t1["valoriva"];
            $resultado["datos"]["servicios"][$iServ]["servicioiva"] = $t1["servicioiva"];
            $resultado["datos"]["servicios"][$iServ]["porcentajedescuento"] = $t1["porcentajedescuento"];
            $resultado["datos"]["servicios"][$iServ]["valordescuento"] = $t1["valordescuento"];
            $resultado["datos"]["servicios"][$iServ]["serviciodescuento"] = $t1["serviciodescuento"];
            $resultado["datos"]["servicios"][$iServ]["clavecontrol"] = $t1["clavecontrol"];
        }

        //
        $arrTem1 = retornarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados_fpago', "recibo='" . $rec . "'");
        $iServ = 0;
        foreach ($arrTem1 as $t1) {
            $iServ++;
            $resultado["datos"]["fp"][$iServ]["tipo"] = $t1["tipo"];
            $resultado["datos"]["fp"][$iServ]["valor"] = $t1["valor"];
            $resultado["datos"]["fp"][$iServ]["banco"] = $t1["banco"];
            $resultado["datos"]["fp"][$iServ]["cheque"] = $arrTem["cheque"];
        }
        if ($iServ == 0) {
            $iServ++;
            $resultado["datos"]["fp"][$iServ]["tipo"] = '1';
            $resultado["datos"]["fp"][$iServ]["valor"] = $arrTem["valorneto"];
            $resultado["datos"]["fp"][$iServ]["banco"] = '';
            $resultado["datos"]["fp"][$iServ]["cheque"] = '';
        }

        //
        if ($cerrarMysql == 'si') {
            $mysqli->close();
        }

        //
        // $mysqli->close();
        return $resultado;
    }

}

?>
