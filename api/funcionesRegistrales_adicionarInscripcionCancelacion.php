<?php

class funcionesRegistrales_adicionarInscripcionCancelacion {

    /**
     * 
     * @param type $dbx
     * @param type $tramae
     * @param type $tradat
     * @param type $tiptra
     * @param type $numacto
     * @return type¨
     */
    public static function adicionarInscripcionCancelacion($dbx, $tramae, $tradat, $tiptra, $numacto) {

        $xActo = '';
        switch ($numacto) {
            case 1 : $xActo = $tramae["idacto1"];
                break;
            case 2 : $xActo = $tramae["idacto2"];
                break;
            case 3 : $xActo = $tramae["idacto3"];
                break;
            case 4 : $xActo = $tramae["idacto4"];
                break;
            case 5 : $xActo = $tramae["idacto5"];
                break;
        }

        $res = array();
        $res ["tiporegistro"] = $tiptra["tiporegistro"];
        $res ["tiposello"] = '90.20.31';
        $res ["libro"] = substr($xActo, 0, 4);
        $res ["numreg"] = '';
        $res ["organizacion"] = $tradat["organizacion"];
        $res ["filesello"] = '';
        $res ["noticia"] = 'CANCELACION MATRICULA MERCANTIL';
        if ($tradat["organizacion"] == '02') {
            $res ["noticia"] = 'CANCELACION MATRICULA MERCANTIL DE ESTABLECIMIENTO DE COMERCIO';
        }
        if (substr($xActo, 5, 4) == '0532') {
            $res ["noticia"] = 'CANCELACION MATRICULA MERCANTIL POR CAMBIO DE DOMICILIO';
            if ($tradat["municipiodestino"] != '') {
                $res["noticia"] .= ' A LA CIUDAD DE ' . retornarRegistroMysqliApi($dbx, 'bas_municipios', "codigomunicipio='" . $tradat["municipiodestino"] . "'", "ciudad");
            }
        }
        $res ["matricula"] = $tradat["matriculaafectada"];
        $res ["proponente"] = '';
        $res ["tipodoc"] = $tradat["tipodoc"];
        $res ["numdoc"] = $tradat["numdoc"];
        $res ["origendoc"] = $tradat["origendoc"];
        $res ["fechadoc"] = $tradat["fechadoc"];
        $res ["municipio"] = $tradat["mundoc"];
        $res ["acto"] = substr($xActo, 5, 4);
        $res ["fecha"] = date("Ymd");
        $res ["hora"] = date("His");
        $res ["tipoidentificacion"] = $tradat["tipoidentificacion"];
        $res ["identificacion"] = $tradat["identificacion"];
        $res ["nombre"] = $tradat["razonsocial"];
        $res ["ope"] = $_SESSION["generales"]["idcodigosirepcaja"]; // 12016-01-12 : JINT : Asigna el registro añ cajero que hce el trámite
        $res ["bandeja"] = $tiptra["bandeja"];

        return $res;
    }
    
    /**
     * 
     * @param type $dbx
     * @param type $mat
     * @param type $motivo
     * @return string
     */
    public static function adicionarInscripcionCancelacionIndividual($dbx, $mat, $idmotivo = '', $descripcionmotivo = '') {

        $exp = retornarRegistroMysqliApi($dbx, 'mreg_est_inscritos', "matricula='" . $mat . "'");
        $xlib = 'RM15';
        $xacto = '0530';
        if ($idmotivo == '02') {
            $xacto = '0531';
        }        
        if ($exp["organizacion"] != '01') {
            $xacto = '0540';
        }
        $res = array();
        $res ["tiporegistro"] = 'RegMer';
        $res ["tiposello"] = '90.20.31';
        $res ["libro"] = $xlib;
        $res ["numreg"] = '';
        $res ["organizacion"] = $exp["organizacion"];
        $res ["filesello"] = '';
        $res ["noticia"] = 'CANCELACION MATRICULA MERCANTIL';
        if ($exp["organizacion"] == '02') {
            $res ["noticia"] = 'CANCELACION MATRICULA MERCANTIL DE ESTABLECIMIENTO DE COMERCIO';
        }
        $res ["matricula"] = $mat;
        $res ["proponente"] = '';
        $res ["tipodoc"] = '06';
        $res ["numdoc"] = 'N/A';
        $res ["origendoc"] = 'EL COMERCIANTE';
        $res ["fechadoc"] = date ("Ymd");
        $res ["municipio"] = $exp["muncom"];
        $res ["acto"] = $xacto;
        $res ["fecha"] = date("Ymd");
        $res ["hora"] = date("His");
        $res ["tipoidentificacion"] = $exp["idclase"];
        $res ["identificacion"] = $exp["numid"];
        $res ["nombre"] = $exp["razonsocial"];
        $res ["ope"] = $_SESSION["generales"]["idcodigosirepcaja"]; 
        $res ["bandeja"] = "4.-REGMER";
        $res ["idmotivo"] = $idmotivo;
        $res ["descripcionmotivo"] = $descripcionmotivo;
        return $res;
    }
}

?>
