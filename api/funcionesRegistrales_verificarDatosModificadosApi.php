<?php

class funcionesRegistrales_verificarDatosModificadosApi {

    public static function verificarDatosModificadosApi($dbx, $numliq, $numrad, $tipotra, $datos, $codigoEmpresa = '', $version = '1510') {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        //
        $arreglo = array(
            'cambidom' => 'N',
            'datosbasicos' => 'S',
            'perjur' => 'S',
            'ubicacion' => 'S',
            'repleg' => 'S',
            'facultades' => 'S',
            'clasi1510' => 'S',
            'inffin1510' => 'S',
            'inffin399a' => 'N',
            'inffin399b' => 'N',
            'sitcontrol' => 'S',
            'exp1510' => 'S'
        );

        if (empty($datos)) {
            return $arreglo;
        }

        if (trim($numliq) == '') {
            return $arreglo;
        }

        if (trim($numliq) == '0') {
            return $arreglo;
        }

        if (trim($tipotra) == 'inscripcionproponente') {
            return $arreglo;
        }

        if (trim($tipotra) == 'cancelacionproponente') {
            $arreglo = array(
                'cambidom' => 'N',
                'datosbasicos' => 'N',
                'perjur' => 'N',
                'ubicacion' => 'N',
                'repleg' => 'N',
                'facultades' => 'N',
                'clasi1510' => 'N',
                'sitcontrol' => 'N',
                'inffin1510' => 'N',
                'inffin399a' => 'N',
                'inffin399b' => 'N',
                'exp1510' => 'N'
            );
            return $arreglo;
        }

        if (trim($tipotra) == 'cambiodomicilioproponente') {
            $arreglo = array(
                'cambidom' => 'S',
                'datosbasicos' => 'S',
                'perjur' => 'S',
                'ubicacion' => 'S',
                'repleg' => 'S',
                'facultades' => 'S',
                'clasi1510' => 'S',
                'sitcontrol' => 'S',
                'inffin1510' => 'S',
                'inffin399a' => 'N',
                'inffin399b' => 'N',
                'exp1510' => 'S'
            );

            return $arreglo;
        }

        if (trim($tipotra) == 'actualizacionespecial') {
            $arreglo = array(
                'cambidom' => 'S',
                'datosbasicos' => 'S',
                'perjur' => 'S',
                'ubicacion' => 'S',
                'repleg' => 'S',
                'facultades' => 'S',
                'clasi1510' => 'S',
                'sitcontrol' => 'S',
                'inffin1510' => 'S',
                'inffin399a' => 'N',
                'inffin399b' => 'N',
                'exp1510' => 'S'
            );
            if ($datos["inffin399a_fechacorte"] != '' && $datos["inffin399a_pregrabado"] != 'si') {
                $arreglo["inffin399a"] = 'S';
            }
            if ($datos["inffin399b_fechacorte"] != '' && $datos["inffin399b_pregrabado"] != 'si') {
                $arreglo["inffin399b"] = 'S';
            }
            return $arreglo;
        }

        if (trim($tipotra) == 'actualizacionproponente399') {
            $arreglo = array(
                'cambidom' => 'N',
                'datosbasicos' => 'N',
                'perjur' => 'N',
                'ubicacion' => 'N',
                'repleg' => 'N',
                'facultades' => 'N',
                'clasi1510' => 'N',
                'sitcontrol' => 'N',
                'inffin1510' => 'N',
                'inffin399a' => 'N',
                'inffin399b' => 'N',
                'exp1510' => 'N'
            );
            if ($datos["inffin399a_fechacorte"] != '' && $datos["inffin399a_pregrabado"] != 'si') {
                $arreglo["inffin399a"] = 'S';
            }
            if ($datos["inffin399b_fechacorte"] != '' && $datos["inffin399b_pregrabado"] != 'si') {
                $arreglo["inffin399b"] = 'S';
            }
            return $arreglo;
        }


        if (trim($tipotra) == '') {
            return $arreglo;
        }

        $datosliq = 0;
        $arrTem = retornarRegistroMysqliApi($dbx, 'mreg_liquidaciondatosoriginal', "idliquidacion=" . $numliq);
        if ($arrTem && !empty($arrTem)) {
            $datosliq = $arrTem["xml"];
        }
        if ($datosliq === false) {
            $_SESSION["generales"]["mensajeerror"] = 'El sistema retorn&oacute; un error al buscar los datos originales en la tabla mreg_liquidaciondatosoriginal (false) (' . $numliq . ') (' . str_replace("'", "", $_SESSION["generales"]["mensajeerror"]) . ')';
            return false;
        }
        if ($datosliq == "0") {
            $datosliq = retornarRegistroMysqliApi($dbx, 'mreg_radicacionesdatosoriginal', "idradicacion='" . ltrim($numrad, "0") . "'", "*", "U");
            if ($datosliq === false) {
                $_SESSION["generales"]["mensajeerror"] = 'El sistema retorn&oacute; un error al buscar los datos originales en la tabla mreg_radicacionesdatosoriginal (false) (' . $numrad . ') (' . str_replace("'", "", $_SESSION["generales"]["mensajeerror"]) . ')';
                return false;
            }
            if ($datosliq == "0" || empty($datosliq)) {
                $_SESSION["generales"]["mensajeerror"] = 'El sistema no localiz&oacute; los datos originales de la radicacion, se hace imposible encontrar las diferencias (' . $numrad . ')';
                return false;
            }
        }

        //
        if ((trim($tipotra) == 'actualizacionproponente') || (trim($tipotra) == 'renovacionproponente')) {

            $arreglo = array(
                'cambidom' => 'N',
                'datosbasicos' => 'N',
                'perjur' => 'N',
                'ubicacion' => 'N',
                'repleg' => 'N',
                'facultades' => 'N',
                'clasi1510' => 'N',
                'inffin1510' => 'N',
                'inffin399a' => 'N',
                'inffin399b' => 'N',
                'sitcontrol' => 'N',
                'exp1510' => 'N'
            );

            $datosoriginal = \funcionesGenerales::desserializarExpedienteProponente($dbx, $datosliq, '', 'no', 'Recuperando datos originales');

            // Verifica si los datos basicos 
            if (
                    ($datosoriginal["nombre"]) != ($datos["nombre"]) ||
                    ($datosoriginal["idtipoidentificacion"]) != ($datos["idtipoidentificacion"]) ||
                    ($datosoriginal["identificacion"]) != ($datos["identificacion"]) ||
                    ($datosoriginal["tamanoempresa"]) != ($datos["tamanoempresa"]) ||
                    ($datosoriginal["nit"]) != ($datos["nit"])
            ) {
                $arreglo["datosbasicos"] = 'S';
            }

            // Verifica si los datos de constitucion han cambiado
            if ($datos["organizacion"] == '99') {
                if (
                        ($datosoriginal["idtipodocperjur"]) != ($datos["idtipodocperjur"]) ||
                        ($datosoriginal["numdocperjur"]) != ($datos["numdocperjur"]) ||
                        ($datosoriginal["origendocperjur"]) != ($datos["origendocperjur"]) ||
                        ($datosoriginal["fecdocperjur"]) != ($datos["fecdocperjur"]) ||
                        ($datosoriginal["fechaconstitucion"]) != ($datos["fechaconstitucion"]) ||
                        ($datosoriginal["fechavencimiento"]) != ($datos["fechavencimiento"])
                ) {
                    $arreglo["perjur"] = 'S';
                }
            }

            // Verifica si los datos de ubicacion
            if (
                    (trim($datosoriginal["dircom"])) != (trim($datos["dircom"])) ||
                    (trim($datosoriginal["muncom"])) != (trim($datos["muncom"])) ||
                    (trim($datosoriginal["telcom1"])) != (trim($datos["telcom1"])) ||
                    (trim($datosoriginal["telcom2"])) != (trim($datos["telcom2"])) ||
                    (trim($datosoriginal["faxcom"])) != (trim($datos["faxcom"])) ||
                    (trim($datosoriginal["celcom"])) != (trim($datos["celcom"])) ||
                    (trim($datosoriginal["emailcom"])) != (trim($datos["emailcom"])) ||
                    (trim($datosoriginal["dirnot"])) != (trim($datos["dirnot"])) ||
                    (trim($datosoriginal["munnot"])) != (trim($datos["munnot"])) ||
                    (trim($datosoriginal["telnot"])) != (trim($datos["telnot"])) ||
                    (trim($datosoriginal["celnot"])) != (trim($datos["celnot"])) ||
                    (trim($datosoriginal["faxnot"])) != (trim($datos["faxnot"])) ||
                    (trim($datosoriginal["emailnot"])) != (trim($datos["emailnot"]))
            ) {
                $arreglo["ubicacion"] = 'S';
            }

            // Si cambian los representantes legales
            if ($datos["organizacion"] == '99') {
                if ($datosoriginal["representanteslegales"] != $datos["representanteslegales"]) {
                    $arreglo["repleg"] = 'S';
                }
            }

            // Si cambian las facultades
            if ($datos["organizacion"] == '99') {
                if ($datosoriginal["facultades"] != $datos["facultades"]) {
                    $arreglo["facultades"] = 'S';
                }
            }

            // Si cambian los datos de la situacion de control
            if ($datosoriginal["sitcontrol"] != $datos["sitcontrol"]) {
                $arreglo["sitcontrol"] = 'S';
            }

            // Si cambia la clasificacion
            $clasiori = array();
            if (!empty($datosoriginal["clasi1510"])) {
                foreach ($datosoriginal["clasi1510"] as $cnt) {
                    if (!isset($clasiori[$cnt])) {
                        $clasiori[$cnt] = $cnt;
                    }
                }
            }
            asort($clasiori);

            //
            $clasi = array();
            foreach ($datos["clasi1510"] as $cnt) {
                if (!isset($clasi[$cnt])) {
                    $clasi[$cnt] = $cnt;
                }
            }


            asort($clasi);
            if ($clasiori != $clasi) {
                $arreglo["clasi1510"] = 'S';
            }
            unset($clasiori);
            unset($clasi);

            // Si cambia la informacion financiera
            // 2017-06-29: JINT: Se ajusta la rutina para validar solamente los datos financieros requeridos por proponentes
            if (
                    (ltrim($datosoriginal["inffin1510_fechacorte"], '0') != ltrim($datos["inffin1510_fechacorte"], '0')) ||
                    (floatval($datosoriginal["inffin1510_actcte"]) != floatval($datos["inffin1510_actcte"])) ||
                    (floatval($datosoriginal["inffin1510_acttot"]) != floatval($datos["inffin1510_acttot"])) ||
                    (floatval($datosoriginal["inffin1510_pascte"]) != floatval($datos["inffin1510_pascte"])) ||
                    (floatval($datosoriginal["inffin1510_pastot"]) != floatval($datos["inffin1510_pastot"])) ||
                    (floatval($datosoriginal["inffin1510_patnet"]) != floatval($datos["inffin1510_patnet"])) ||
                    (floatval($datosoriginal["inffin1510_utiope"]) != floatval($datos["inffin1510_utiope"])) ||
                    (floatval($datosoriginal["inffin1510_gasint"]) != floatval($datos["inffin1510_gasint"]))
            ) {
                $arreglo["inffin1510"] = 'S';
            }

            // Si cambia la informacion financiera 399a
            if (
                    (ltrim($datosoriginal["inffin399a_fechacorte"], '0') != ltrim($datos["inffin399a_fechacorte"], '0')) ||
                    (floatval($datosoriginal["inffin399a_actcte"]) != floatval($datos["inffin399a_actcte"])) ||
                    (floatval($datosoriginal["inffin399a_acttot"]) != floatval($datos["inffin399a_acttot"])) ||
                    (floatval($datosoriginal["inffin399a_pascte"]) != floatval($datos["inffin399a_pascte"])) ||
                    (floatval($datosoriginal["inffin399a_pastot"]) != floatval($datos["inffin399a_pastot"])) ||
                    (floatval($datosoriginal["inffin399a_patnet"]) != floatval($datos["inffin399a_patnet"])) ||
                    (floatval($datosoriginal["inffin399a_utiope"]) != floatval($datos["inffin399a_utiope"])) ||
                    (floatval($datosoriginal["inffin399a_gasint"]) != floatval($datos["inffin399a_gasint"]))
            ) {
                $arreglo["inffin399a"] = 'S';
            }

            // Si cambia la informacion financiera 399b
            if (
                    (ltrim($datosoriginal["inffin399b_fechacorte"], '0') != ltrim($datos["inffin399b_fechacorte"], '0')) ||
                    (floatval($datosoriginal["inffin399b_actcte"]) != floatval($datos["inffin399b_actcte"])) ||
                    (floatval($datosoriginal["inffin399b_acttot"]) != floatval($datos["inffin399b_acttot"])) ||
                    (floatval($datosoriginal["inffin399b_pascte"]) != floatval($datos["inffin399b_pascte"])) ||
                    (floatval($datosoriginal["inffin399b_pastot"]) != floatval($datos["inffin399b_pastot"])) ||
                    (floatval($datosoriginal["inffin399b_patnet"]) != floatval($datos["inffin399b_patnet"])) ||
                    (floatval($datosoriginal["inffin399b_utiope"]) != floatval($datos["inffin399b_utiope"])) ||
                    (floatval($datosoriginal["inffin399b_gasint"]) != floatval($datos["inffin399b_gasint"]))
            ) {
                $arreglo["inffin399b"] = 'S';
            }


            // Verificacion de la experiencia
            $arrTemAnt = array();
            if (!empty($datosoriginal["exp1510"])) {
                foreach ($datosoriginal["exp1510"] as $cnt) {
                    $arrTemAnt[$cnt["secuencia"]] = $cnt;
                    $arrTemAnt[$cnt["secuencia"]]["elimino"] = 'si';
                }
            }

            foreach ($datos["exp1510"] as $cnt) {
                $sec = sprintf("%03s", $cnt["secuencia"]);
                if (!isset($arrTemAnt[$sec])) {
                    $arreglo["exp1510-" . $sec] = 'S';
                    $arreglo["exp1510"] = 'S';
                } else {
                    if (!isset($arrTemAnt[$sec]["clasif"]))
                        $arrTemAnt[$sec]["clasif"] = array();
                    if (!isset($cnt["clasif"]))
                        $cnt["clasif"] = array();
                    $arrTemAnt[$sec]["elimino"] = 'no';
                    if ($arrTemAnt[$sec]["celebradopor"] != $cnt["celebradopor"] ||
                            $arrTemAnt[$sec]["nombrecontratista"] != $cnt["nombrecontratista"] ||
                            $arrTemAnt[$sec]["nombrecontratante"] != $cnt["nombrecontratante"] ||
                            floatval($arrTemAnt[$sec]["valor"]) != floatval($cnt["valor"]) ||
                            $arrTemAnt[$sec]["clasif"] != $cnt["clasif"] ||
                            floatval($arrTemAnt[$sec]["porcentaje"]) != floatval($cnt["porcentaje"])
                    ) {
                        $arreglo["exp1510-" . $sec] = 'S';
                        $arreglo["exp1510"] = 'S';
                    } else {
                        $arreglo["exp1510-" . $sec] = 'N';
                    }
                }
            }

            foreach ($arrTemAnt as $sec => $cnt) {
                if ($cnt["elimino"] == 'si') {
                    $arreglo["exp1510-" . $sec] = 'E';
                    $arreglo["exp1510"] = 'S';
                }
            }
            return $arreglo;
        }
    }


}

?>
