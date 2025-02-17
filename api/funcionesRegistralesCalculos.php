<?php

class funcionesRegistralesCalculos {

    public static function calcularFechaRenovacion($dbx = null, $mat = '', $serviciosRenovacion = array(), $serviciosAfiliacion = array(), $serviciosMatricula = array()) {

        $salida = '';

        //
        if ($mat == '') {
            return $salida;
        }

        //
        $cerrarMysqli = 'no';
        if ($dbx == null) {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
            $cerrarMysqli = 'si';
        }

        //
        $fcorte = retornarRegistroMysqliApi($dbx, "mreg_cortes_renovacion", "ano='" . date("Y") . "'", "corte");
        $exp = retornarRegistroMysqliApi($dbx, "mreg_est_inscritos", "matricula='" . $mat . "'", "fecrenant,ultanorenant");
        if ($exp && !empty($exp)) {
            if ($exp["fecrenant"] != '') {
                $salida = $exp["fecrenant"];
            }
        }
        if (!isset($serviciosRenovacion) || empty($serviciosRenovacion)) {
            $serviciosRenovacion = array();
            $serviciosMatricula = array();
            $temx1 = retornarRegistrosMysqliApi($dbx, "mreg_servicios", "tipoingreso IN ('02','03','12','13')", "idservicio");
            foreach ($temx1 as $x1) {
                $serviciosRenovacion[] = $x1["idservicio"];
                if ($x1["tipoingreso"] == '02' || $x1["tipoingreso"] == '12') {
                    $serviciosMatricula[$x1["idservicio"]] = $x1["idservicio"];
                }
            }
        }
        $tServiciosRenovacion = '';
        foreach ($serviciosRenovacion as $s) {
            if ($tServiciosRenovacion != '') {
                $tServiciosRenovacion .= ",";
            }
            $tServiciosRenovacion .= "'" . $s . "'";
        }
        if (!isset($serviciosAfiliacion) || empty($serviciosAfiliacion)) {
            $serviciosAfiliacion = array();
            $temx1 = retornarRegistrosMysqliApi($dbx, "mreg_servicios", "1=1", "idservicio");
            foreach ($temx1 as $x1) {
                if ($x1["grupoventas"] == '02') {
                    $serviciosAfiliacion[$x1["idservicio"]] = $x1["idservicio"];
                }
            }
        }
        $tServiciosAfiliacion = '';
        foreach ($serviciosAfiliacion as $s) {
            if ($tServiciosAfiliacion != '') {
                $tServiciosAfiliacion .= ",";
            }
            $tServiciosAfiliacion .= "'" . $s . "'";
        }
        if (!isset($serviciosMatricula) || empty($serviciosMatricula)) {
            $serviciosMatricula = array();
            $temx1 = retornarRegistrosMysqliApi($dbx, "mreg_servicios", "tipoingreso IN ('02','03','12','13')", "idservicio");
            foreach ($temx1 as $x1) {
                if ($x1["tipoingreso"] == '02' || $x1["tipoingreso"] == '12') {
                    $serviciosMatricula[$x1["idservicio"]] = $x1["idservicio"];
                }
            }
        }
        $estadosCbNoAsentadoLocal = array('00', '05', '06', '09', '10', '07', '17', '19', '39', '40', '41', '42', '43', '50', '51', '52', '53', '99');
        $estadosCbNoAsentadoRues = array('01', '05', '06', '07', '09', '10', '17', '19', '23', '39', '40', '41', '42', '43', '50', '51', '52', '53', '99');
        $estadosCbRadicadosLocal = array('01', '02', '03', '04', '09', '10');
        $recs = retornarRegistrosMysqliApi($dbx, 'mreg_est_recibos', "(matricula='" . $mat . "') and (servicio IN (" . $tServiciosRenovacion . ")) and (tipogasto IN ('0','8')) and (ctranulacion = '0')", "fecoperacion,horaoperacion,anorenovacion");
        if ($recs && !empty($recs)) {
            $anosren = array();
            foreach ($recs as $r) {
                if (substr($r["numerorecibo"], 0, 1) == 'S' || substr($r["numerorecibo"], 0, 1) == 'R' || substr($r["numerorecibo"], 0, 1) == 'Z') {
                    $cba = retornarRegistroMysqliApi($dbx, 'mreg_est_codigosbarras', "recibo='" . $r["numerorecibo"] . "'");
                    $inc = 'no';
                    $ai = 'no';
                    if ($cba === false || empty($cba)) {
                        $inc = 'si';
                    } else {
                        if ($r["tipogasto"] == '0' && !in_array($cba["estadofinal"], $estadosCbNoAsentadoLocal)) {
                            if (in_array($cba["estadofinal"], $estadosCbRadicadosLocal)) {
                                $liq = retornarRegistroMysqliApi($dbx, 'mreg_liquidacion', "numerorecibo='" . $r["numerorecibo"] . "'");
                                if ($liq && !empty($liq) && $liq["controlactividadaltoimpacto"] == 'S') {
                                    $inc = 'si';
                                    $ai = 'si';
                                }
                            } else {
                                $inc = 'si';
                            }
                        } else {
                            if ($r["tipogasto"] == '8' && !in_array($cba["estadofinal"], $estadosCbNoAsentadoRues)) {
                                $inc = 'si';
                            } else {
                                if ($inc == 'no') {
                                    if ($cba["verificacionsoportes"] != 'SI') {
                                        $inc = 'si';
                                    }
                                }
                            }
                        }
                    }
                    if ($inc == 'si') {
                        if (!isset($anosren[$r["anorenovacion"]]) || $anosren[$r["anorenovacion"]] == 1) {
                            if (ltrim(trim($r["fecharenovacionaplicable"]), "0") != '') {
                                $salida = $r["fecharenovacionaplicable"];
                            } else {
                                $salida = $r["fecoperacion"];
                            }
                        }
                    }
                }
            }
        }
        unset($recs);
        unset($anosren);

        // 2020-01-23: JINT
        if ($salida == '') {
            $exp = retornarRegistroMysqliApi($dbx, 'mreg_est_inscritos', "matricula='" . $mat . "'", "matricula,fecmatricula,fecrenovacion,ultanoren,fecrenant,camant,ultanorenant");
            if ($exp["camant"] != '') {
                $salida = $exp["fecrenant"];
            } else {
                if ($exp["fecrenovacion"] == '') {
                    $salida = $exp["fecrenovacion"];
                } else {
                    $salida = $exp["fecmatricula"];
                }
            }
        }

        //
        if ($salida != '') {
            $exp = retornarRegistroMysqliApi($dbx, 'mreg_est_inscritos', "matricula='" . $mat . "'", "matricula,fecmatricula,fecrenovacion,fecrenant,camant,ultanorenant");
            if ($exp["fecmatricula"] >= $salida["fecultren"]) {
                if ($exp["camant"] != '') {
                    if ($salida < $exp["fecrenant"]) {
                        $salida = $exp["fecrenant"];
                    }
                } else {
                    $salida = $exp["fecmatricula"];
                }
            }
        }

        //
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }

        //
        return $salida;
    }

    public static function calcularUltimoAnoRenovado($dbx = null, $mat = '', $serviciosRenovacion = array(), $serviciosAfiliacion = array(), $serviciosMatricula = array()) {

        $salida = '';

        //
        if ($mat == '') {
            return $salida;
        }

        //
        $cerrarMysqli = 'no';
        if ($dbx == null) {
            $dbx = new mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
            $cerrarMysqli = 'si';
        }

        //
        $fcorte = retornarRegistroMysqliApi($dbx, "mreg_cortes_renovacion", "ano='" . date("Y") . "'", "corte");

        //
        $exp = retornarRegistroMysqliApi($dbx, "mreg_est_inscritos", "matricula='" . $mat . "'", "fecrenant,ultanorenant");
        if ($exp && !empty($exp)) {
            if ($exp["ultanorenant"] != '') {
                $salida = $exp["ultanorenant"];
            }
        }

        //
        if (!isset($serviciosRenovacion) || empty($serviciosRenovacion)) {
            $serviciosRenovacion = array();
            $serviciosMatricula = array();
            $temx1 = retornarRegistrosMysqliApi($dbx, "mreg_servicios", "tipoingreso IN ('02','03','12','13')", "idservicio");
            foreach ($temx1 as $x1) {
                $serviciosRenovacion[] = $x1["idservicio"];
                if ($x1["tipoingreso"] == '02' || $x1["tipoingreso"] == '12') {
                    $serviciosMatricula[$x1["idservicio"]] = $x1["idservicio"];
                }
            }
        }

        //
        $tServiciosRenovacion = '';
        foreach ($serviciosRenovacion as $s) {
            if ($tServiciosRenovacion != '') {
                $tServiciosRenovacion .= ",";
            }
            $tServiciosRenovacion .= "'" . $s . "'";
        }

        //
        if (!isset($serviciosAfiliacion) || empty($serviciosAfiliacion)) {
            $serviciosAfiliacion = array();
            $temx1 = retornarRegistrosMysqliApi($dbx, "mreg_servicios", "1=1", "idservicio");
            foreach ($temx1 as $x1) {
                if ($x1["grupoventas"] == '02') {
                    $serviciosAfiliacion[$x1["idservicio"]] = $x1["idservicio"];
                }
            }
        }

        //
        $tServiciosAfiliacion = '';
        foreach ($serviciosAfiliacion as $s) {
            if ($tServiciosAfiliacion != '') {
                $tServiciosAfiliacion .= ",";
            }
            $tServiciosAfiliacion .= "'" . $s . "'";
        }

        //
        if (!isset($serviciosMatricula) || empty($serviciosMatricula)) {
            $serviciosMatricula = array();
            $temx1 = retornarRegistrosMysqliApi($dbx, "mreg_servicios", "tipoingreso IN ('02','03','12','13')", "idservicio");
            foreach ($temx1 as $x1) {
                if ($x1["tipoingreso"] == '02' || $x1["tipoingreso"] == '12') {
                    $serviciosMatricula[$x1["idservicio"]] = $x1["idservicio"];
                }
            }
        }

        //
        // $estadosCbNoAsentadoLocal = array('00', '01', '02', '03', '04', '05', '06', '07', '17', '19','39', '40', '41', '42', '43', '50', '51', '52', '53', '99');
        // $estadosCbNoAsentadoRues = array('00', '01', '02', '03', '04', '05', '06', '07', '17', '19', '23', '39', '40', '41', '42', '43', '50', '51', '52', '53', '99');
        $estadosCbNoAsentadoLocal = array('00', '05', '06', '09', '10', '07', '17', '19', '39', '40', '41', '42', '43', '50', '51', '52', '53', '99');
        $estadosCbNoAsentadoRues = array('01', '05', '06', '07', '09', '10', '17', '19', '23', '39', '40', '41', '42', '43', '50', '51', '52', '53', '99');
        $estadosCbRadicadosLocal = array('01', '02', '03', '04', '09', '10');
        $recs = retornarRegistrosMysqliApi($dbx, 'mreg_est_recibos', "(matricula='" . $mat . "') and (servicio IN (" . $tServiciosRenovacion . ")) and (tipogasto IN ('0','8')) and (ctranulacion = '0')", "fecoperacion,horaoperacion,anorenovacion");
        if ($recs && !empty($recs)) {
            $anosren = array();
            foreach ($recs as $r) {
                if (substr($r["numerorecibo"], 0, 1) == 'S' || substr($r["numerorecibo"], 0, 1) == 'R' || substr($r["numerorecibo"], 0, 1) == 'Z') {
                    $cba = retornarRegistroMysqliApi($dbx, 'mreg_est_codigosbarras', "recibo='" . $r["numerorecibo"] . "'");
                    $inc = 'no';
                    $ai = 'no';
                    if ($cba === false || empty($cba)) {
                        $inc = 'si';
                    } else {
                        if ($r["tipogasto"] == '0' && !in_array($cba["estadofinal"], $estadosCbNoAsentadoLocal)) {
                            if (in_array($cba["estadofinal"], $estadosCbRadicadosLocal)) {
                                $liq = retornarRegistroMysqliApi($dbx, 'mreg_liquidacion', "numerorecibo='" . $r["numerorecibo"] . "'");
                                if ($liq && !empty($liq) && $liq["controlactividadaltoimpacto"] == 'S') {
                                    $inc = 'si';
                                    $ai = 'si';
                                }
                            } else {
                                $inc = 'si';
                            }
                        } else {
                            if ($r["tipogasto"] == '8' && !in_array($cba["estadofinal"], $estadosCbNoAsentadoRues)) {
                                $inc = 'si';
                            } else {
                                if ($inc == 'no') {
                                    if ($cba["verificacionsoportes"] != 'SI') {
                                        $inc = 'si';
                                    }
                                }
                            }
                        }
                    }
                    if ($inc == 'si') {
                        if (!isset($anosren[$r["anorenovacion"]]) || $anosren[$r["anorenovacion"]] == 1) {
                            $salida = $r["anorenovacion"];
                        }
                    }
                }
            }
        }
        unset($recs);
        unset($anosren);

        // 2020-01-23: JINT
        if ($salida["fecultren"] == '') {
            $exp = retornarRegistroMysqliApi($dbx, 'mreg_est_inscritos', "matricula='" . $mat . "'", "matricula,fecmatricula,fecrenovacion,ultanoren,fecrenant,camant,ultanorenant");
            if ($exp["camant"] != '') {
                $salida = $exp["ultanorenant"];
            } else {
                if ($exp["fecrenovacion"] == '') {
                    $salida = $exp["ultanoren"];
                } else {
                    $salida = substr($exp["fecmatricula"], 0, 4);
                }
            }
        }

        //
        if ($salida != '') {
            $exp = retornarRegistroMysqliApi($dbx, 'mreg_est_inscritos', "matricula='" . $mat . "'", "matricula,fecmatricula,fecrenovacion,fecrenant,camant,ultanorenant");
            if ($exp["fecmatricula"] >= $salida["fecultren"]) {
                if ($exp["camant"] != '') {
                    if ($salida < $exp["ultanorenant"]) {
                        $salida = $exp["ultanorenant"];
                    }
                } else {
                    $salida = substr($exp["fecmatricula"], 0, 4);
                }
            }
        }

        //
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }

        //
        return $salida;
    }

    public static function calcularFechaVencimiento($dbx, $mat = '', $num = 0) {
        $retorno = '';
        $rvs = retornarRegistrosMysqliApi($dbx, 'mreg_est_vigencias', "matricula='" . $mat . "'", "fecha");
        $ivs = 0;
        if ($rvs && !empty($rvs)) {
            foreach ($rvs as $rv) {
                $ivs++;
                if ($ivs == $num) {
                    $retorno = $rv["fecha"];
                }
            }
        }
        return $retorno;
    }

    public static function calcularFechaDisolucion($dbx, $mat = '') {
        $fecdisolucion = '';
        if ($mat == '') {
            return $fecdisolucion;
        }
        $exp = retornarRegistroMysqliApi($dbx, 'mreg_est_inscritos', "matricula='" . $mat . "'","organizacion,categoria,fecdisolucion,fecvigencia");
        if ($exp === false || empty ($exp)) {
            return $fecdisolucion;
        }
        if ($exp["organizacion"] == '01' || $exp["organizacion"] == '02' || ($exp["organizacion"] > '02' && ($exp["categoria"] == '2' || $exp["categoria"] == '3'))) {
            return $fecdisolucion;
        }
        
        //
        $query = "
            SELECT 
                mreg_est_inscripciones.fecharegistro,
                mreg_est_inscripciones.horaregistro,
                mreg_est_inscripciones.libro,
                mreg_est_inscripciones.acto,
                mreg_est_inscripciones.ctrrevoca,
                mreg_actos.idlibro,
                mreg_actos.idacto,
                mreg_actos.idgrupoacto
            FROM 
		mreg_est_inscripciones
            INNER JOIN 
		mreg_actos ON mreg_actos.idlibro = mreg_est_inscripciones.libro AND  mreg_actos.idacto = mreg_est_inscripciones.acto AND mreg_actos.idgrupoacto IN ('009','011')
            WHERE 
		mreg_est_inscripciones.matricula = '" . $mat . "'
            ORDER by
		mreg_est_inscripciones.fecharegistro, 
		mreg_est_inscripciones.horaregistro";

        //
        $res = ejecutarQueryMysqliApi($dbx, $query);
        if ($res && !empty($res)) {
            foreach ($res as $rs1) {
                if ($rs1["ctrrevoca"] != '1') {
                    if ($rs1["idgrupoacto"] == '009') {
                        $fecdisolucion = $rs1["fecharegistro"];
                    }
                    if ($rs1["idgrupoacto"] == '011') {
                        $fecdisolucion = '';
                    }
                }
            }
        }
        
        return $fecdisolucion;
    }
    
    public static function calcularFechaLiquidacion($dbx, $mat = '') {
        $fecliquidacion = '';
        if ($mat == '') {
            return $fecliquidacion;
        }
        $exp = retornarRegistroMysqliApi($dbx, 'mreg_est_inscritos', "matricula='" . $mat . "'","organizacion,categoria,fecliquidacion,fecvigencia");
        if ($exp === false || empty ($exp)) {
            return $fecliquidacion;
        }
        if ($exp["organizacion"] == '01' || $exp["organizacion"] == '02' || ($exp["organizacion"] > '02' && ($exp["categoria"] == '2' || $exp["categoria"] == '3'))) {
            return $fecliquidacion;
        }
        
        //
        $query = "
            SELECT 
                mreg_est_inscripciones.fecharegistro,
                mreg_est_inscripciones.horaregistro,
                mreg_est_inscripciones.libro,
                mreg_est_inscripciones.acto,
                mreg_est_inscripciones.ctrrevoca,
                mreg_actos.idlibro,
                mreg_actos.idacto,
                mreg_actos.idgrupoacto
            FROM 
		mreg_est_inscripciones
            INNER JOIN 
		mreg_actos ON mreg_actos.idlibro = mreg_est_inscripciones.libro AND  mreg_actos.idacto = mreg_est_inscripciones.acto AND mreg_actos.idgrupoacto IN ('010')
            WHERE 
		mreg_est_inscripciones.matricula = '" . $mat . "'
            ORDER by
		mreg_est_inscripciones.fecharegistro, 
		mreg_est_inscripciones.horaregistro";

        //
        $res = ejecutarQueryMysqliApi($dbx, $query);
        if ($res && !empty($res)) {
            foreach ($res as $rs1) {
                if ($rs1["ctrrevoca"] != '1') {
                    if ($rs1["idgrupoacto"] == '009') {
                        $fecliquidacion = $rs1["fecharegistro"];
                    }
                }
            }
        }        
        return $fecliquidacion;
    }
    
    public static function calcularFechaCancelacion($dbx, $mat = '') {
        $feccancelacion = '';
        if ($mat == '') {
            return $feccancelacion;
        }
        $exp = retornarRegistroMysqliApi($dbx, 'mreg_est_inscritos', "matricula='" . $mat . "'","organizacion,categoria,feccancelacion,ctrestmatricula");
        if ($exp === false || empty ($exp)) {
            return $feccancelacion;
        }
        if ($exp["ctrestmatricula"] != 'MC' && $exp["ctrestmatricula"] != 'MF' && $exp["ctrestmatricula"] != 'MG' && $exp["ctrestmatricula"] != 'IC' && $exp["ctrestmatricula"] != 'IF' && $exp["ctrestmatricula"] != 'IG') {
            return $feccancelacion;
        }
        
        //
        $query = "
            SELECT 
                mreg_est_inscripciones.fecharegistro,
                mreg_est_inscripciones.horaregistro,
                mreg_est_inscripciones.libro,
                mreg_est_inscripciones.acto,
                mreg_est_inscripciones.ctrrevoca,
                mreg_actos.idlibro,
                mreg_actos.idacto,
                mreg_actos.idgrupoacto
            FROM 
		mreg_est_inscripciones
            INNER JOIN 
		mreg_actos ON mreg_actos.idlibro = mreg_est_inscripciones.libro AND  mreg_actos.idacto = mreg_est_inscripciones.acto AND mreg_actos.idgrupoacto IN ('002','010')
            WHERE 
		mreg_est_inscripciones.matricula = '" . $mat . "'
            ORDER by
		mreg_est_inscripciones.fecharegistro, 
		mreg_est_inscripciones.horaregistro";

        //
        $res = ejecutarQueryMysqliApi($dbx, $query);
        if ($res && !empty($res)) {
            foreach ($res as $rs1) {
                if ($rs1["ctrrevoca"] != '1') {
                    if ($rs1["idgrupoacto"] == '002') {
                        $feccancelacion = $rs1["fecharegistro"];
                    }
                    if ($rs1["idgrupoacto"] == '010') {
                        $feccancelacion = $rs1["fecharegistro"];
                    }                    
                }
            }
        } else {
            $feccancelacion = $exp["feccancelacion"];
        }       
        
        //
        return $feccancelacion;
    }

}
