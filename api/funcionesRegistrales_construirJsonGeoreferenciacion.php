<?php

class funcionesRegistrales_construirJsonGeoreferenciacion {

    public static function construirJsonGeoreferenciacion($mysqli, $exp) {

        $arrJson = array();

        //
        $arrJson["matricula"] = $exp["matricula"];
        if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
            $arrJson["idclase"] = $exp["idclase"];
            $arrJson["numid"] = $exp["numid"];
            $arrJson["nit"] = $exp["nit"];
        } else {
            $arrJson["idclase"] = null;
            $arrJson["numid"] = null;
            $arrJson["nit"] = null;
        }
        $arrJson["razonsocial"] = $exp["razonsocial"];
        if (trim($exp["sigla"]) != '') {
            $arrJson["sigla"] = $exp["sigla"];
        } else {
            $arrJson["sigla"] = null;
        }
        $arrJson["organizacion"] = $exp["organizacion"];
        $arrJson["categoria"] = $exp["categoria"];
        $arrJson["estado"] = 'MA';
        switch ($exp["ctrestmatricula"]) {
            case "MC" :
            case "IC" :
            case "MF" :
            case "MG" :
            case "IF" :
            case "IG" :
                $arrJson["estado"] = 'MC';
                break;
        }

        $arrJson["fecmatricula"] = $exp["fecmatricula"];
        $arrJson["fecrenovacion"] = $exp["fecrenovacion"];
        $arrJson["ultanoren"] = $exp["ultanoren"];
        if (trim($exp["feccancelacion"]) != '') {
            $arrJson["feccancelacion"] = $exp["feccancelacion"];
        } else {
            $arrJson["feccancelacion"] = null;
        }

        $arrJson["dircom"] = $exp["dircom"];
        $arrJson["muncom"] = $exp["muncom"];
        $arrJson["telcom1"] = $exp["telcom1"];
        if (trim($exp["telcom2"]) != '') {
            $arrJson["telcom2"] = $exp["telcom2"];
        } else {
            $arrJson["telcom2"] = null;
        }
        if (trim($exp["emailcom"]) != '') {
            $arrJson["emailcom"] = $exp["emailcom"];
        } else {
            $arrJson["emailcom"] = null;
        }

        $arrJson["ciiu1"] = null;
        $arrJson["ciiu2"] = null;
        $arrJson["ciiu3"] = null;
        $arrJson["ciiu4"] = null;
        $arrJson["ciiu1des"] = null;
        $arrJson["ciiu2des"] = null;
        $arrJson["ciiu3des"] = null;
        $arrJson["ciiu4des"] = null;

        //
        $arrJson["ciiu1"] = substr($exp["ciiu1"], 1);
        $arrJson["ciiu1des"] = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiu='" . $exp["ciiu1"] . "'", "descripcion");
        if ($exp["ciiu2"] != '') {
            $arrJson["ciiu2"] = substr($exp["ciiu2"], 1);
            $arrJson["ciiu2des"] = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiu='" . $exp["ciiu2"] . "'", "descripcion");
        }
        if ($exp["ciiu3"] != '') {
            $arrJson["ciiu3"] = substr($exp["ciiu3"][3], 1);
            $arrJson["ciiu3des"] = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiu='" . $exp["ciiu3"] . "'", "descripcion");
        }
        if ($exp["ciiu4"] != '') {
            $arrJson["ciiu4"] = substr($exp["ciiu4"][4], 1);
            $arrJson["ciiu4des"] = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiu='" . $exp["ciiu4"] . "'", "descripcion");
        }


        $arrJson["tamanoempresarial"] = null;
        $arrJson["empleados"] = $exp["personal"];
        $arrJson["ingresosoperacionales"] = null;
        $arrJson["patrimonio"] = null;
        if ($exp["organizacion"] == '01' || ($exp["organizacion"] > '02' && $exp["categoria"] == '1')) {
            $tememp = array();
            $tex = retornarRegistrosMysqliApi($mysqli, 'mreg_tamano_empresarial', "matricula='" . $exp["matricula"] . "'", 'anodatos,fechadatos');
            if ($tex && !empty($tex)) {
                foreach ($tex as $tex1) {
                    $tememp["ciiutamanoempresarial"] = $tex1["ciiu"];
                    $tememp["ingresostamanoempresarial"] = $tex1["ingresos"];
                    $tememp["anodatostamanoempresarial"] = $tex1["anodatos"];
                    $tememp["fechadatostamanoempresarial"] = $tex1["fechadatos"];
                }
                if ($tememp["ciiutamanoempresarial"] == '') {
                    $tememp["ciiutamanoempresarial"] = $exp["ciiu1"];
                }
                if ($tememp["ingresostamanoempresarial"] == '') {
                    $tememp["ingresostamanoempresarial"] = $exp["ingope"];
                }
                if ($tememp["anodatostamanoempresarial"] == '') {
                    $tememp["anodatostamanoempresarial"] = $exp["ultanoren"];
                }
                if ($tememp["fechadatostamanoempresarial"] == '') {
                    $tememp["fechadatostamanoempresarial"] = $exp["anodatos"];
                }
                $anomatricula = 'no';
                if ($exp["fecrenovacion"] == $exp["fecmatricula"]) {
                    $anomatricula = 'si';
                }
                $tememp["tamanoempresarial957"] = \funcionesRegistrales::determinarTamanoEmpresarial($mysqli, $tememp["ciiutamanoempresarial"], $tememp["ingresostamanoempresarial"], $tememp["anodatostamanoempresarial"], $tememp["fechadatostamanoempresarial"], $anomatricula);
                if ($tememp["tamanoempresarial957"] == 'MICRO EMPRESA') {
                    $arrJson["tamanoempresarial"] = '01';
                }
                if ($tememp["tamanoempresarial957"] == 'PEQUEÑA EMPRESA') {
                    $arrJson["tamanoempresarial"] = '02';
                }
                if ($tememp["tamanoempresarial957"] == 'MEDIANA EMPRESA') {
                    $arrJson["tamanoempresarial"] = '03';
                }
                if ($tememp["tamanoempresarial957"] == 'GRAN EMPRESA') {
                    $arrJson["tamanoempresarial"] = '04';
                }
            }
            $arrJson["activos"] = $exp["acttot"];
            $arrJson["ingresosoperacionales"] = $exp["ingope"];
            $arrJson["patrimonio"] = $exp["pattot"];
        } else {
            $arrJson["activos"] = $exp["actvin"];
        }


        $arrJson["propietarios"] = array();

        // Si es establecimiento
        if ($exp["organizacion"] == '02') {
            $props = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matricula='" . $exp["matricula"] . "'");
            foreach ($props as $p) {
                if ($p["matriculapropietario"] != '' && ($p["codigocamara"] == '' || $p["codigocamara"] == CODIGO_EMPRESA)) {
                    $p1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $p["matriculapropietario"] . "'");
                    if ($p1 && !empty($p1)) {
                        $prop = array();
                        $prop["idclase"] = $p1["idclase"];
                        $prop["numid"] = $p1["numid"];
                        $prop["razonsocial"] = $p1["razonsocial"];
                        $arrJson["propietarios"][] = $prop;
                    }
                } else {
                    $prop = array();
                    $prop["idclase"] = $p["tipoidentificacion"];
                    $prop["numid"] = $p["identificacion"];
                    $prop["razonsocial"] = $p["razonsocial"];
                    $arrJson["propietarios"][] = $prop;
                }
            }
        }

        // Sucursales y agencias
        if ($exp["organizacion"] > '02' && ($exp["categoria"] == '2' || $exp["categoria"] == '3')) {
            if ($exp["cpnummat"] != '' && ($exp["cpcodcam"] == '' || $exp["cpcodcam"] == CODIGO_EMPRESA)) {
                $p1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $exp["cpnummat"] . "'");
                if ($p1 && !empty($p1)) {
                    $prop = array();
                    $prop["idclase"] = $p1["idclase"];
                    $prop["numid"] = $p1["numid"];
                    $prop["razonsocial"] = $p1["razonsocial"];
                    $arrJson["propietarios"][] = $prop;
                }
            } else {
                $prop = array();
                $prop["idclase"] = '2';
                $prop["numid"] = $exp["cpnumnit"];
                $prop["razonsocial"] = $exp["cprazsoc"];
                $arrJson["propietarios"][] = $prop;
            }
        }

        // Representantes legales
        $arrJson["representanteslegales"] = array();
        if ($exp["organizacion"] > '02' && $exp["categoria"] == '1') {
            $vins = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $exp["matricula"] . "'", "id");
            if ($vins && !empty($vins)) {
                foreach ($vins as $v) {
                    if ($v["estado"] == 'V') {
                        $tv = retornarRegistroMysqliApi($mysqli, 'mreg_codvinculos', "id='" . $v["vinculo"] . "'");
                        if ($tv && !empty($tv)) {
                            if ($tv["tipovinculo"] == 'RLP' || $tv["tipovinculoceresadl"] == 'RLP') {
                                $rl = array();
                                $rl["idclasereplegal"] = $v["idclase"];
                                $rl["numidreplegal"] = $v["numid"];
                                $rl["nombrereplegal"] = $v["nombre"];
                                $arrJson["representanteslegales"][] = $rl;
                            }
                        }
                    }
                }
            }
        }

        return $arrJson;
    }

}

?>
