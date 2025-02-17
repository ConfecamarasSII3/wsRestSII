<?php

class funcionesRegistrales_determinarTamanoEmpresarial {

    /**
     * 
     * @param type $mysqli
     * @param type $ciiu
     * @param type $ingresos
     * @param type $anodatos
     * @param type $fechadatos
     * @param type $anomatricula
     * @return string
     */
    public static function determinarTamanoEmpresarial($mysqli, $ciiu, $ingresos, $anodatos, $fechadatos, $anomatricula = 'no') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        //
        $salida = array(
            'tamanotexto' => '',
            'ingresosuvt' => 0,
            'uvt' => 0
        );

        if ($ciiu != '') {
            $arrCiiu = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiu like '%" . $ciiu . "'");
            if ($arrCiiu && !empty($arrCiiu)) {
                if (isset($arrCiiu["sector"]) && $arrCiiu["sector"] != '') {
                    $arrTam = retornarRegistroMysqliApi($mysqli, 'bas_tamano_empresarial', "sector='" . $arrCiiu["sector"] . "'");
                    if ($arrTam && !empty($arrTam)) {
                        $arrSal = retornarRegistrosMysqliApi($mysqli, 'bas_smlv', "1=1", "fecha");
                        foreach ($arrSal as $s) {
                            if ($anomatricula == 'no') {
                                if ($s["fecha"] < $anodatos . '0101') {
                                    $salida["uvt"] = $s["uvt"];
                                }
                            }
                            if ($anomatricula == 'si') {
                                if ($s["fecha"] <= $fechadatos) {
                                    $salida["uvt"] = $s["uvt"];
                                }
                            }
                        }
                        if ($salida["uvt"] != 0 && is_numeric($salida["uvt"]) && is_numeric($ingresos)) {
                            $salida["ingresosuvt"] = $ingresos / $salida["uvt"];
                        } else {
                            $salida["ingresosuvt"] = 0;
                        }
                        if ($salida["ingresosuvt"] >= $arrTam["micromin"] && $salida["ingresosuvt"] <= $arrTam["micromax"]) {
                            $salida["tamanotexto"] = 'MICRO EMPRESA';
                        }
                        if ($salida["ingresosuvt"] >= $arrTam["pequemin"] && $salida["ingresosuvt"] <= $arrTam["pequemax"]) {
                            $salida["tamanotexto"] = 'PEQUEÑA EMPRESA';
                        }
                        if ($salida["ingresosuvt"] >= $arrTam["medimin"] && $salida["ingresosuvt"] <= $arrTam["medimax"]) {
                            $salida["tamanotexto"] = 'MEDIANA EMPRESA';
                        }
                        if ($salida["ingresosuvt"] >= $arrTam["granmin"]) {
                            $salida["tamanotexto"] = 'GRAN EMPRESA';
                        }
                    }
                }
            }
        }
        return $salida;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $activos
     * @param type $personal
     * @param type $fechadatos
     * @return type
     */
    public static function determinarTamanoEmpresarialActivos($mysqli = null, $activos = 0, $personal = 0, $fechadatos = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        //
        $salida = array(
            'tamanocodigo' => '',
            'tamanotexto' => '',
            'tamanoresumido' => '',
            'smmlv' => 0
        );

        $asmmlv = retornarRegistrosMysqliApi($mysqli, 'bas_smlv', "fecha <= '" . $fechadatos . "'", "fecha");
        foreach ($asmmlv as $sm) {
            $salida["smmlv"] = $sm["salario"];
        }
        if ($salida["smmlv"] == 0) {
            return false;
        }
        if (($activos / $salida["smmlv"]) <= 500 && $personal <= 10) {
            $salida["tamanocodigo"] = '1';
            $salida["tamanotexto"] = 'MICRO EMPRESA';
            $salida["tamanoresumido"] = 'micro';
        } else {
            if (($activos / $salida["smmlv"]) <= 5000 && $personal <= 50) {
                $salida["tamanocodigo"] = '2';
                $salida["tamanotexto"] = 'PEQUEÑA EMPRESA';
                $salida["tamanoresumido"] = 'pequena';
            } else {
                if (($activos / $salida["smmlv"]) <= 30000 && $personal <= 200) {
                    $salida["tamanocodigo"] = '3';
                    $salida["tamanotexto"] = 'MEDIANA EMPRESA';
                    $salida["tamanoresumido"] = 'mediana';
                } else {
                    $salida["tamanocodigo"] = '4';
                    $salida["tamanotexto"] = 'GRAN EMPRESA';
                    $salida["tamanoresumido"] = 'gran';
                }
            }
        }
        return $salida;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $ciiu
     * @param type $ingresos
     * @param type $anodatos
     * @param type $fechadatos
     * @param type $anomatricula
     * @return string
     */
    public static function determinarTamanoEmpresarialUvts($mysqli = null, $ciiu = '', $ingresos = 0, $anodatos = '', $fechadatos = '', $anomatricula = 'no') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        //
        $salida = array(
            'tamanocodigo' => '',
            'tamanotexto' => '',
            'tamanoresumido' => '',
            'ingresosuvt' => 0,
            'uvt' => 0,
            'sector' => ''
        );

        //
        if ($ciiu != '') {
            if ($anomatricula == 'si') {
                $ano = $anodatos;
            } else {
                $ano = $anodatos - 1;
            }
            $arrCiiu = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiu like '%" . $ciiu . "'");
            if ($arrCiiu && !empty($arrCiiu)) {
                if (isset($arrCiiu["sector"]) && $arrCiiu["sector"] != '') {
                    $salida["sector"] = $arrCiiu["sector"];
                    $arrTam = retornarRegistroMysqliApi($mysqli, 'bas_tamano_empresarial', "sector='" . $arrCiiu["sector"] . "'");
                    if ($arrTam && !empty($arrTam)) {
                        // $uvt = 0;
                        $arrSal = retornarRegistrosMysqliApi($mysqli, 'bas_smlv', "1=1", "fecha");
                        foreach ($arrSal as $s) {
                            if ($anomatricula == 'no') {
                                if ($s["fecha"] <= $ano . '0101') {
                                    $salida["uvt"] = $s["uvt"];
                                }
                            }
                            if ($anomatricula == 'si') {
                                if ($s["fecha"] <= $fechadatos) {
                                    $salida["uvt"] = $s["uvt"];
                                }
                            }
                        }
                        if ($salida["uvt"] != 0 && is_numeric($salida["uvt"]) && is_numeric($ingresos)) {
                            $salida["ingresosuvt"] = $ingresos / $salida["uvt"];
                        } else {
                            $salida["ingresosuvt"] = 0;
                        }
                        if ($salida["ingresosuvt"] >= $arrTam["micromin"] && $salida["ingresosuvt"] <= $arrTam["micromax"]) {
                            $salida["tamanotexto"] = 'MICRO EMPRESA';
                            $salida["tamanocodigo"] = '1';
                            $salida["tamanoresumido"] = 'micro';                                
                        }
                        if ($salida["ingresosuvt"] >= $arrTam["pequemin"] && $salida["ingresosuvt"] <= $arrTam["pequemax"]) {
                            $salida["tamanotexto"] = 'PEQUEÑA EMPRESA';
                            $salida["tamanocodigo"] = '2';
                            $salida["tamanoresumido"] = 'pequena';                            
                        }
                        if ($salida["ingresosuvt"] >= $arrTam["medimin"] && $salida["ingresosuvt"] <= $arrTam["medimax"]) {
                            $salida["tamanotexto"] = 'MEDIANA EMPRESA';
                            $salida["tamanocodigo"] = '3';
                            $salida["tamanoresumido"] = 'mediana';                            
                        }
                        if ($salida["ingresosuvt"] >= $arrTam["granmin"]) {
                            $salida["tamanotexto"] = 'GRAN EMPRESA';
                            $salida["tamanocodigo"] = '4';
                            $salida["tamanoresumido"] = 'gran';                            
                        }
                    }
                }
            }
        }
        return $salida;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $ciiu
     * @param type $ingresos
     * @param type $anodatos
     * @param type $fechadatos
     * @param type $anomatricula (si/no)
     * @return string
     */
    public static function determinarTamanoEmpresarialUvbs($mysqli = null, $ciiu = '', $ingresos = 0, $anodatos = '', $fechadatos = '', $anomatricula = 'no') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        //
        $salida = array(
            'tamanocodigo' => '',
            'tamanotexto' => '',
            'tamanoresumido' => '',
            'ingresosuvb' => 0,
            'uvb' => 0,
            'sector' => 0,
        );

        //
        if ($ciiu != '') {
            if ($anomatricula == 'si') {
                $ano = $anodatos;
            } else {
                $ano = $anodatos - 1;
            }
            $arrCiiu = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiu like '%" . $ciiu . "'");
            if ($arrCiiu && !empty($arrCiiu)) {
                $salida["sector"] = $arrCiiu["sector"];
                if (isset($arrCiiu["sector"]) && $arrCiiu["sector"] != '') {
                    $arrTam = retornarRegistroMysqliApi($mysqli, 'bas_tamano_empresarial_uvb', "ano='" . $anodatos . "' and sector='" . $arrCiiu["sector"] . "'");
                    if ($arrTam && !empty($arrTam)) {
                        // $uvt = 0;
                        $arrSal = retornarRegistrosMysqliApi($mysqli, 'bas_smlv', "1=1", "fecha");
                        foreach ($arrSal as $s) {
                            if ($anomatricula == 'no') {
                                if ($s["fecha"] <= $ano . '0101') {
                                    $salida["uvb"] = $s["uvb"];
                                }
                            }
                            if ($anomatricula == 'si') {
                                if ($s["fecha"] <= $fechadatos) {
                                    $salida["uvb"] = $s["uvb"];
                                }
                            }
                        }
                        if ($salida["uvb"] != 0 && is_numeric($salida["uvb"]) && is_numeric($ingresos)) {
                            $salida["ingresosuvb"] = $ingresos / $salida["uvb"];
                        } else {
                            $salida["ingresosuvb"] = 0;
                        }
                        if ($salida["ingresosuvb"] >= $arrTam["micromin"] && $salida["ingresosuvb"] <= $arrTam["micromax"]) {
                            $salida["tamanotexto"] = 'MICRO EMPRESA';
                            $salida["tamanocodigo"] = '1';
                            $salida["tamanoresumido"] = 'micro';         
                        }
                        if ($salida["ingresosuvb"] >= $arrTam["pequemin"] && $salida["ingresosuvb"] <= $arrTam["pequemax"]) {
                            $salida["tamanotexto"] = 'PEQUEÑA EMPRESA';
                            $salida["tamanocodigo"] = '2';
                            $salida["tamanoresumido"] = 'pequena';     
                        }
                        if ($salida["ingresosuvb"] >= $arrTam["medimin"] && $salida["ingresosuvb"] <= $arrTam["medimax"]) {
                            $salida["tamanotexto"] = 'MEDIANA EMPRESA';
                            $salida["tamanocodigo"] = '3';
                            $salida["tamanoresumido"] = 'mediana'; 
                        }
                        if ($salida["ingresosuvb"] >= $arrTam["granmin"]) {
                            $salida["tamanotexto"] = 'GRAN EMPRESA';
                            $salida["tamanocodigo"] = '4';
                            $salida["tamanoresumido"] = 'gran';
                        }
                    }
                }
            }
        }        
        return $salida;
    }

}

?>
