<?php

class funcionesRegistrales_desserializarReciboSirep {

    public static function desserializarReciboSirep($mysqli = null, $xml = '') {

        $retorno = array();
        $retorno["control"] = 'NUEVO';
        $retorno["archivo"] = '';
        $retorno["operacion"] = '';
        $retorno["fecopera"] = '';
        $retorno["horpago"] = '';
        $retorno["ctranul"] = '';
        $retorno["tipogasto"] = ''; // Incluir
        $retorno["numfactura"] = '';
        $retorno["numrecibo"] = '';
        $retorno["idclase"] = '';
        $retorno["numid"] = '';
        $retorno["nompago"] = '';
        $retorno["apellido1"] = ''; // Incluir
        $retorno["apellido2"] = ''; // Incluir
        $retorno["nombre1"] = ''; // Incluir
        $retorno["nombre2"] = ''; // Incluir
        $retorno["totpago"] = 0;
        $retorno["idliquidacion"] = 0;
        $retorno["idmon"] = '';
        $retorno["ctrreliq"] = '';
        $retorno["ben1429"] = '';
        $retorno["numemp"] = '';
        $retorno["munrec"] = '';
        $retorno["email"] = '';
        $retorno["direccion"] = ''; // Incluir
        $retorno["telefono"] = ''; // Incluir
        $retorno["movil"] = ''; // Incluir
        $retorno["proyectocaja"] = '';
        $retorno["usuariosirep"] = '';
        $retorno["numerounicorue"] = '';
        $retorno["numerointernorue"] = '';
        $retorno["numinterno"] = '';
        $retorno["numunico"] = '';
        $retorno["camaraorigen"] = '';
        $retorno["camaradestino"] = '';
        $retorno["estadoemail"] = '';
        $retorno["estadosms"] = '';
        $retorno["idformapago"] = '';
        $retorno["operador"] = '';
        $retorno["usuario"] = '';
        $retorno["sucursal"] = '';
        $retorno["tipotramite"] = '';
        $_SESSION["recibo"]["datos"]["pagoprepago"] = 0;
        $_SESSION["recibo"]["datos"]["pagoafiliado"] = 0;
        $_SESSION["recibo"]["datos"]["pagoefectivo"] = 0;
        $_SESSION["recibo"]["datos"]["pagocheque"] = 0;
        $_SESSION["recibo"]["datos"]["pagoconsignacion"] = 0;
        $_SESSION["recibo"]["datos"]["pagopseach"] = 0;
        $_SESSION["recibo"]["datos"]["pagovisa"] = 0;
        $_SESSION["recibo"]["datos"]["pagomastercard"] = 0;
        $_SESSION["recibo"]["datos"]["pagocredencial"] = 0;
        $_SESSION["recibo"]["datos"]["pagoamerican"] = 0;
        $_SESSION["recibo"]["datos"]["pagodiners"] = 0;
        $_SESSION["recibo"]["datos"]["pagotdebito"] = 0;
        $_SESSION["recibo"]["datos"]["numeroautorizacion"] = '';
        $_SESSION["recibo"]["datos"]["cheque"] = '';
        $_SESSION["recibo"]["datos"]["franquicia"] = '';
        $_SESSION["recibo"]["datos"]["nombrefranquicia"] = '';
        $_SESSION["recibo"]["datos"]["codbanco"] = '';
        $_SESSION["recibo"]["datos"]["nombrebanco"] = '';
        $retorno["codbarras"] = array();
        $retorno["servicios"] = array();
        $retorno["fp"] = array();
        $retorno["xml"] = $xml;

        if (trim($xml) != '') {
            $retorno["control"] = 'MODIFICACION';
            $dom = new DomDocument('1.0', 'utf-8');
            $result = $dom->loadXML($xml);
            $reg1 = $dom->getElementsByTagName("respuesta");
            foreach ($reg1 as $reg) {
                $retorno["archivo"] = $reg->getElementsByTagName("archivo")->item(0)->textContent;
                $retorno["operacion"] = $reg->getElementsByTagName("operacion")->item(0)->textContent;
                $retorno["operador"] = substr($reg->getElementsByTagName("operacion")->item(0)->textContent, 2, 3);
                $retorno["fecopera"] = $reg->getElementsByTagName("fecopera")->item(0)->textContent;
                $retorno["horpago"] = $reg->getElementsByTagName("horpago")->item(0)->textContent;
                $retorno["ctranul"] = $reg->getElementsByTagName("ctranul")->item(0)->textContent;
                $retorno["numfactura"] = $reg->getElementsByTagName("numfactura")->item(0)->textContent;
                $retorno["numrecibo"] = $reg->getElementsByTagName("numrecibo")->item(0)->textContent;
                $retorno["idclase"] = $reg->getElementsByTagName("idclase")->item(0)->textContent;
                $retorno["numid"] = ltrim($reg->getElementsByTagName("numid")->item(0)->textContent, "0");
                $retorno["nompago"] = trim(($reg->getElementsByTagName("nompago")->item(0)->textContent));
                $retorno["totpago"] = ltrim($reg->getElementsByTagName("totpago")->item(0)->textContent, "0");
                if ($reg->getElementsByTagName("stotpago")->item(0)->textContent == '-') {
                    $retorno["totpago"] = $retorno["totpago"] * -1;
                }
                $retorno["idmon"] = $reg->getElementsByTagName("idmon")->item(0)->textContent;
                $retorno["ctrreliq"] = $reg->getElementsByTagName("ctrreliq")->item(0)->textContent;
                $retorno["ben1429"] = $reg->getElementsByTagName("ben1429")->item(0)->textContent;
                $retorno["numemp"] = ltrim($reg->getElementsByTagName("numemp")->item(0)->textContent, "0");
                if (isset($reg->getElementsByTagName("usuariosirep")->item(0)->textContent)) {
                    $retorno["usuariosirep"] = ltrim($reg->getElementsByTagName("usuariosirep")->item(0)->textContent, "0");
                    $retorno["usuario"] = ltrim($reg->getElementsByTagName("usuariosirep")->item(0)->textContent, "0");
                }
                if (isset($reg->getElementsByTagName("munrec")->item(0)->textContent)) {
                    $retorno["munrec"] = ltrim($reg->getElementsByTagName("munrec")->item(0)->textContent, "0");
                }
                if (isset($reg->getElementsByTagName("email")->item(0)->textContent)) {
                    $retorno["email"] = ltrim($reg->getElementsByTagName("email")->item(0)->textContent, "0");
                }
                if (isset($reg->getElementsByTagName("proyectocaja")->item(0)->textContent)) {
                    $retorno["proyectocaja"] = ltrim($reg->getElementsByTagName("proyectocaja")->item(0)->textContent, "0");
                }
                if (isset($reg->getElementsByTagName("numerounicorue")->item(0)->textContent)) {
                    $retorno["numerounicorue"] = ltrim($reg->getElementsByTagName("numerounicorue")->item(0)->textContent, "0");
                    $retorno["numunico"] = ltrim($reg->getElementsByTagName("numerounicorue")->item(0)->textContent, "0");
                }
                if (isset($reg->getElementsByTagName("numerointernorue")->item(0)->textContent)) {
                    $retorno["numerointernorue"] = ltrim($reg->getElementsByTagName("numerointernorue")->item(0)->textContent, "0");
                    $retorno["numinterno"] = ltrim($reg->getElementsByTagName("numerointernorue")->item(0)->textContent, "0");
                }
                if (isset($reg->getElementsByTagName("camaraorigen")->item(0)->textContent)) {
                    $retorno["camaraorigen"] = ltrim($reg->getElementsByTagName("camaraorigen")->item(0)->textContent, "0");
                }
                if (isset($reg->getElementsByTagName("camaradestino")->item(0)->textContent)) {
                    $retorno["camaradestino"] = ltrim($reg->getElementsByTagName("camaradestino")->item(0)->textContent, "0");
                }
            }

            $i = 0;
            $reg1 = $dom->getElementsByTagName("codbarras");
            foreach ($reg1 as $reg) {
                $i++;
                $retorno["codbarras"][$i] = ltrim($reg->textContent, "0");
            }

            $reg1 = $dom->getElementsByTagName("servicio");
            $i = 0;
            foreach ($reg1 as $reg) {
                $i++;
                $retorno["servicios"][$i]["idserv"] = $reg->getElementsByTagName("idserv")->item(0)->textContent;
                $retorno["servicios"][$i]["vrserv"] = ltrim($reg->getElementsByTagName("vrserv")->item(0)->textContent, "0");
                if ($reg->getElementsByTagName("sgvrserv")->item(0)->textContent == '-') {
                    $retorno["servicios"][$i]["vrserv"] = doubleval($retorno["servicios"][$i]["vrserv"]) * -1;
                }
                $retorno["servicios"][$i]["cntserv"] = ltrim($reg->getElementsByTagName("cntserv")->item(0)->textContent, "0");
                if ($reg->getElementsByTagName("sgcntserv")->item(0)->textContent == '-') {
                    $retorno["servicios"][$i]["cntserv"] = doubleval($retorno["servicios"][$i]["cntserv"]) * -1;
                }
                $retorno["servicios"][$i]["ctrgtoadmt"] = $reg->getElementsByTagName("ctrgtoadmt")->item(0)->textContent;
                if ($i == 1) {
                    $retorno["tipogasto"] = $reg->getElementsByTagName("ctrgtoadmt")->item(0)->textContent;
                }
                $retorno["servicios"][$i]["porcentaje"] = 0;
                $retorno["servicios"][$i]["vracti"] = ltrim($reg->getElementsByTagName("vracti")->item(0)->textContent, "0");
                $retorno["servicios"][$i]["idmatricula"] = '';
                $retorno["servicios"][$i]["idproponente"] = '';
                $temServ = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $retorno["servicios"][$i]["idserv"] . "'");
                if ($temServ["tipoingreso"] < '21' && $temServ["tipoingreso"] > '30') {
                    $retorno["servicios"][$i]["idmatricula"] = ltrim($reg->getElementsByTagName("idmatricula")->item(0)->textContent, "0");
                } else {
                    $retorno["servicios"][$i]["idproponente"] = ltrim($reg->getElementsByTagName("idmatricula")->item(0)->textContent, "0");
                }
                $retorno["servicios"][$i]["anorenova"] = ltrim($reg->getElementsByTagName("anorenova")->item(0)->textContent, "0");
                $retorno["servicios"][$i]["identificacion"] = $retorno["numid"];
                $retorno["servicios"][$i]["razonsocial"] = $retorno["nompago"];
            }

            $reg1 = $dom->getElementsByTagName("fp");
            $i = 0;
            foreach ($reg1 as $reg) {
                $i++;
                $retorno["fp"][$i]["tipo"] = $reg->getElementsByTagName("fptipo")->item(0)->textContent;
                $retorno["fp"][$i]["valor"] = doubleval(ltrim(substr($reg->getElementsByTagName("fpvalor")->item(0)->textContent, 0, 15), "0"));
                $retorno["fp"][$i]["valor"] = $retorno["fp"][$i]["valor"] + doubleval(substr($reg->getElementsByTagName("fpvalor")->item(0)->textContent, 16, 2) / 100);
                if ($reg->getElementsByTagName("sfpvalor")->item(0)->textContent == '-') {
                    $retorno["fp"][$i]["valor"] = doubleval($retorno["fp"][$i]["valor"]) * -1;
                }
                $retorno["fp"][$i]["banco"] = $reg->getElementsByTagName("fpbanco")->item(0)->textContent;
                $retorno["fp"][$i]["cheque"] = ltrim($reg->getElementsByTagName("fpcheque")->item(0)->textContent, "0");
            }

            unset($reg);
            unset($reg1);
            unset($dom);
        }

        //
        return $retorno;
    }

}

?>
