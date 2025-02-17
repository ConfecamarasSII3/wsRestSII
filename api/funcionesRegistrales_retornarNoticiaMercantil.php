<?php

class funcionesRegistrales_retornarNoticiaMercantil {

    public static function retornarNoticiaMercantil($mysqli, $criterio = '1', $lib = '', $numreg = '', $dup = '', $fini = '', $mat = '', $pro = '', $tide = '', $numid = '') {

        if ($criterio == '1') {

            $filtrafirma = 'NO';

//
            if (!isset($_SESSION["noticia"]["numini"])) {
                $_SESSION["noticia"]["numini"] = '';
            }

//
            if ($lib == '') {
                $lib = $_SESSION["noticia"]["idlibro"];
            }
            if ($fini == '') {
                $fini = $_SESSION["noticia"]["fechainicial"];
            }
            if ($numreg == '') {
                $numreg = $_SESSION["noticia"]["numini"];
            }

//
            if (strlen($lib) == '2') {
                if ($lib < '50') {
                    $lib = 'RM' . $lib;
                } else {
                    $lib = 'RE' . $lib;
                }
            }

//
            $respuesta = array();
            if ($numreg != '') {
                if (substr($lib, 0, 2) == 'RP') {
                    $respuesta = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones_proponentes', "libro='" . $lib . "' and registro >='" . $numreg . "'", "libro,registro", '*', $_SESSION["noticia"]["offset"], $_SESSION["noticia"]["retornar"]);
                } else {
                    $respuesta = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "libro='" . $lib . "' and registro >='" . $numreg . "'", "libro,registro", '*', $_SESSION["noticia"]["offset"], $_SESSION["noticia"]["retornar"]);
                }
            } else {
                if ($fini != '') {
                    if (substr($lib, 0, 2) == 'RP') {
                        $respuesta = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones_proponentes', "libro='" . $lib . "' and fecharegistro >='" . $fini . "'", "libro,registro", '*', $_SESSION["noticia"]["offset"], $_SESSION["noticia"]["retornar"]);
                    } else {
                        $respuesta = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "libro='" . $lib . "' and fecharegistro >='" . $fini . "'", "fecharegistro,registro", '*', $_SESSION["noticia"]["offset"], $_SESSION["noticia"]["retornar"]);
                    }
                } else {
                    if (substr($lib, 0, 2) == 'RP') {
                        $respuesta = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones_proponentes', "libro='" . $lib . "'", "libro,registro", '*', $_SESSION["noticia"]["offset"], $_SESSION["noticia"]["retornar"]);
                    } else {
                        $respuesta = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "libro='" . $lib . "'", "fecharegistro,registro", '*', $_SESSION["noticia"]["offset"], $_SESSION["noticia"]["retornar"]);
                    }
                    
                }
            }
            if ($respuesta === false) {
                return false;
            }
            if (empty($respuesta)) {
                return array();
            }
        }
        if ($criterio == '5') {

            if ($mat == '') {
                $mat = $_SESSION["noticia"]["matricula"];
            }

            $respuesta = array();
            $respuesta = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "matricula='" . $mat . "'", "libro,registro,dupli",'*');
            if ($respuesta === false) {
                return false;
            }
            if (empty($respuesta)) {
                return array();
            }
        }
        if ($criterio == '6') {

            if ($pro == '') {
                $pro = $_SESSION["noticia"]["proponente"];
            }

            $respuesta = array();
            $respuesta = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones_proponentes', "proponente='" . $pro . "'", "libro,registro",'*');
            if ($respuesta === false) {
                return false;
            }
            if (empty($respuesta)) {
                return array();
            }
        }

        if ($criterio == '7') {
            if ($lib == '') {
                $lib = $_SESSION["noticia"]["idlibro"];
            }
            if ($numreg == '') {
                $numreg = $_SESSION["noticia"]["registro"];
            }
            if ($dup == '') {
                $dup = $_SESSION["noticia"]["dupli"];
            }

//
            if (strlen($lib) == 2) {
                if ($lib < '50') {
                    $lib = 'RM' . $lib;
                } else {
                    $lib = 'RE' . $lib;
                }
            }

//
            if (substr($lib, 0, 2) == 'RP') {
                $respuesta = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones_proponentes', "libro='" . $lib . "' and registro='" . $numreg . "'", "libro,registro",'*');
            } else {
                $respuesta = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "libro='" . $lib . "' and registro='" . $numreg . "' and dupli='" . $dup . "'", "libro,registro,dupli",'*');
            }
            if ($respuesta === false) {
                return false;
            }
            if (empty($respuesta)) {
                return false;
            }
        }

        if ($criterio == '8') {
            $filtrafirma = 'NO';
            $respuesta = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "identificacion='" . $numid . "'", "libro,registro,dupli",'*');
            if ($respuesta === false) {
                return false;
            }
            if (empty($respuesta)) {
                return false;
            }
        }

//
        $retorno = array();
        $i = -1;
        foreach ($respuesta as $r) {
            $i++;
            $retorno[$i]["id"] = $r["id"];
            $retorno[$i]["libro"] = $r["libro"];
            $retorno[$i]["numregistro"] = $r["registro"];
            $retorno[$i]["dupli"] = $r["dupli"];
            $retorno[$i]["ctrrevoca"] = $r["ctrrevoca"];
            $retorno[$i]["fecha"] = $r["fecharegistro"];
            $retorno[$i]["fecharegistro"] = $r["fecharegistro"];
            $retorno[$i]["hora"] = $r["horaregistro"];
            $retorno[$i]["horaregistro"] = $r["horaregistro"];
            $retorno[$i]["acto"] = $r["acto"];
            if (!isset($r["matricula"])) {
                $retorno[$i]["matricula"] = '';
            } else {
                $retorno[$i]["matricula"] = $r["matricula"];
            }
            $retorno[$i]["proponente"] = $r["proponente"];
            $retorno[$i]["tipoidentificacion"] = $r["tipoidentificacion"];
            $retorno[$i]["identificacion"] = $r["identificacion"];
            $retorno[$i]["nombre"] = \funcionesGenerales::restaurarEspeciales($r["nombre"]);
            $retorno[$i]["tipodoc"] = $r["tipodocumento"];
            $retorno[$i]["numdoc"] = $r["numerodocumento"];
            $retorno[$i]["origendoc"] = $r["idorigendoc"];
            $retorno[$i]["fechadoc"] = $r["fechadocumento"];
            if (!isset($r["idcodlibro"])) {
                $retorno[$i]["idlibrovii"] = '';
            } else {
                $retorno[$i]["idlibrovii"] = $r["idcodlibro"];
            }
            if (!isset($r["codigolibro"])) {
                $retorno[$i]["codigolibro"] = '';
            } else {
                $retorno[$i]["codlibrovii"] = $r["codigolibro"];
            }
            if (!isset($r["paginainicial"])) {
                $retorno[$i]["paginainicialvii"] = '';
            } else {
                $retorno[$i]["paginainicialvii"] = $r["paginainicial"];
            }
            if (!isset($r["numeropaginas"])) {
                $retorno[$i]["paginasvii"] = '';
            } else {
                $retorno[$i]["paginasvii"] = $r["numeropaginas"];
            }
            if (!isset($r["descripcionlibro"])) {
                $retorno[$i]["nombrevii"] = '';
            } else {
                $retorno[$i]["nombrevii"] = $r["descripcionlibro"];
            }
            $retorno[$i]["idmunipdoc"] = $r["municipiodocumento"];
            $retorno[$i]["txtorigen"] = $r["origendocumento"];
            if (!isset($r["noticia"])) {
                $retorno[$i]["txtnoticia"] = '';
            } else {
                $retorno[$i]["txtnoticia"] = $r["noticia"];
            }
            $retorno[$i]["estado"] = $r["estado"];
            $retorno[$i]["numpubrue"] = trim((string)$r["idpublicacionrue"]);
            $retorno[$i]["fecpubrue"] = $r["fecpublicacionrue"];
            $retorno[$i]["id"] = $r["id"];
            $retorno[$i]["idradicacion"] = $r["idradicacion"];
            $retorno[$i]["clavefirmado"] = $r["clavefirmado"];
            $retorno[$i]["operador"] = $r["operador"];
            $retorno[$i]["camaraanterior"] = $r["camaraanterior"];
            $retorno[$i]["libroanterior"] = $r["libroanterior"];
            $retorno[$i]["camaraanterior"] = $r["camaraanterior"];
            $retorno[$i]["registroanterior"] = $r["registroanterior"];
            $retorno[$i]["fecharegistroanterior"] = $r["fecharegistroanterior"];
            if (substr($retorno[$i]["libro"], 0, 2) == 'RP') {
                if (trim($retorno[$i]["txtnoticia"]) == '') {
                    switch ($retorno[$i]["acto"]) {
                        case "01" : $retorno[$i]["txtnoticia"] = 'INSCRIPCION DE PROPONENTE';
                            break;
                        case "02" : $retorno[$i]["txtnoticia"] = 'RENOVACION DE PROPONENTE';
                            break;
                        case "03" : $retorno[$i]["txtnoticia"] = 'ACTUALIZACION DE PROPONENTE';
                            break;
                        case "04" : $retorno[$i]["txtnoticia"] = 'CANCELACION DE PROPONENTE';
                            break;
                        case "05" : $retorno[$i]["txtnoticia"] = 'CESACION DE EFECTOS DE PROPONENTE';
                            break;
                        case "07" : $retorno[$i]["txtnoticia"] = 'CONTRATO / MULTA O SANCION';
                            break;
                        case "15" : $retorno[$i]["txtnoticia"] = 'CANCELACION POR CAMBIO DE DOMICILIO DE PROPONENTE';
                            break;
                        case "16" : $retorno[$i]["txtnoticia"] = 'INSCRIPCION POR CAMBIO DE DOMICILIO DE PROPONENTE';
                            break;
                        default: $retorno[$i]["txtnoticia"] = 'ACTO NO IDENTIFICADO';
                            break;
                    }
                }
            }

// 2017-09-06: JINT: Para conocer si existe o no sello en el repositorio
            $retorno[$i]["pathimagensello"] = '';
            $retorno[$i]["idimagensello"] = 0;
            if (strlen($r["libro"]) == 4) {
                $txLib = "'" . $r["libro"] . "','" . substr($r["libro"], 2, 2) . "'";
            } else {
                $txLib = "'" . $r["libro"] . "'";
            }
            $temx = retornarRegistroMysqliApi($mysqli, 'mreg_radicacionesanexos', "libro IN (" . $txLib . ") and registro='" . $r["registro"] . "' and tipoanexo='505'");
            if ($temx && !empty($temx)) {
                $retorno[$i]["pathimagensello"] = $temx["path"];
                $retorno[$i]["idimagensello"] = $temx["idanexo"];
            }
        }
        unset($respuesta);
        return $retorno;
    }    
}

?>
