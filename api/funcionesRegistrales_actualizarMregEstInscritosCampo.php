<?php

class funcionesRegistrales_actualizarMregEstInscritosCampo {

    public static function actualizarMregEstInscritosCampo($dbx = null, $matricula = '', $campo = '', $contenido = '', $tipocampo = 'varchar', $codbarras = '', $tipotramite = '', $recibo = '') {


        // Valida que el expedienmte tenga diferencias 
        if (ltrim($matricula, "0") == '') {
            return true;
        }
        if ($campo == '') {
            return true;
        }

        //
        $datoori = retornarRegistroMysqliApi($dbx, 'mreg_est_inscritos', "matricula='" . $matricula . "'", $campo);
        if (trim((string)$datoori) != trim((string)$contenido)) {
            $arrCampos = array(
                $campo,
                'fecactualizacion',
                'compite360',
                'rues',
                'ivc'
            );
            if ($tipocampo == 'varchar') {
                $arrValores = array(
                    "'" . addslashes(trim((string)$contenido)) . "'",
                    "'" . date("Ymd") . "'",
                    "'NO'",
                    "'NO'",
                    "'NO'"
                );
            }
            if ($tipocampo == 'int') {
                $arrValores = array(
                    intval($contenido),
                    "'" . date("Ymd") . "'",
                    "'NO'",
                    "'NO'",
                    "'NO'"
                );
            }
            if ($tipocampo == 'double') {
                $arrValores = array(
                    doubleval($contenido),
                    "'" . date("Ymd") . "'",
                    "'NO'",
                    "'NO'",
                    "'NO'"
                );
            }
            unset($_SESSION["expedienteactual"]);
            $res = regrabarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $matricula . "'");
            if ($res === false) {
                \logApi::general2('actualizarMregEstInscritos_' . date("Ymd"), ltrim($matricula, "0"), 'Error regrabando en mreg_est_inscritos: ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
            
            if ($campo == 'razonsocial') {
                borrarRegistrosMysqliApi($dbx, 'mreg_est_inscritos_campos', "matricula='" . $matricula . "' and campo='nombrebase64'");
                if ($contenido != '') {
                    $arrCampos = array(
                        'matricula',
                        'campo',
                        'contenido'
                    );
                    $arrValores = array(
                        "'" . $matricula . "'",
                        "'nombrebase64'",
                        "'" . base64_encode($contenido) . "'"
                    );
                    insertarRegistrosMysqliApi($dbx, 'mreg_est_inscritos_campos', $arrCampos, $arrValores);
                }
            }
            
            if ($campo == 'sigla') {
                borrarRegistrosMysqliApi($dbx, 'mreg_est_inscritos_campos', "matricula='" . $matricula . "' and campo='siglabase64'");
                if ($contenido != '') {
                    $arrCampos = array(
                        'matricula',
                        'campo',
                        'contenido'
                    );
                    $arrValores = array(
                        "'" . $matricula . "'",
                        "'siglabase64'",
                        "'" . base64_encode($contenido) . "'"
                    );
                    insertarRegistrosMysqliApi($dbx, 'mreg_est_inscritos_campos', $arrCampos, $arrValores);
                }
            }

            //
            if (!isset($_SESSION["generales"]["codigousuario"])) {
                $_SESSION["generales"]["codigousuario"] = '';
            }
            
            //
            $arrCampos = array(
                'matricula',
                'campo',
                'fecha',
                'hora',
                'codigobarras',
                'datoanterior',
                'datonuevo',
                'usuario',
                'ip',
                'tipotramite',
                'recibo'
            );
            $arrValores = array(
                "'" . ltrim($matricula, "0") . "'",
                "'" . $campo . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "'" . $codbarras . "'",
                "'" . addslashes($datoori) . "'",
                "'" . addslashes($contenido) . "'",
                "'" . $_SESSION["generales"]["codigousuario"] . "'",
                "'" . \funcionesGenerales::localizarIP() . "'",
                "'" . $tipotramite . "'",
                "'" . $recibo . "'"
            );
            $res = insertarRegistrosMysqliApi($dbx, 'mreg_campos_historicos_' . date("Y"), $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2('actualizarMregCamposHistoricos_' . date("Ymd"), ltrim($matricula, "0"), 'Error grabando en mreg_campos_historicos: ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }

            //
            $datax = \funcionesRegistrales::retornarExpedienteMercantil($dbx, ltrim($matricula, "0"));

            //
            if ($datax["hashcontrol"] != '') {
                $h1 = explode("|", $datax["hashcontrol"]);
                $h11 = $h1[1];
            } else {
                $h11 = '';
            }
            if ($datax["hashcontrolnuevo"] != '') {
                $h2 = explode("|", $datax["hashcontrolnuevo"]);
                $h21 = $h2[1];
            } else {
                $h21 = '';
            }

            //
            if ($h11 != $h21) {
                $arrCampos = array(
                    'hashcontrol'
                );
                $arrValores = array(
                    "'" . date("Ymd") . '|' . $h21 . "'"
                );
                regrabarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . ltrim($matricula, "0") . "'");
            }
        }

        //
        return true;
    }

    public static function actualizarMregEstInscritosCampoCampos($dbx, $matricula, $campo, $contenido = '', $codbarras = '', $tipotramite = '', $recibo = '') {


        // Valida que el expedienmte tenga diferencias
        if (ltrim($matricula, "0") == '' || $campo == '') {
            return true;
        }

        //
        $dataori = retornarRegistroMysqliApi($dbx, 'mreg_est_inscritos_campos', "matricula='" . $matricula . "' and campo='" . $campo . "'");
        if (!isset($dataori) || $dataori === false || empty($dataori)) {
            $arrCampos = array(
                'matricula',
                'campo',
                'contenido'
            );
            $arrValores = array(
                "'" . $matricula . "'",
                "'" . $campo . "'",
                "'" . addslashes($contenido) . "'"
            );
            insertarRegistrosMysqliApi($dbx, 'mreg_est_inscritos_campos', $arrCampos, $arrValores);
        } else {
            $arrCampos = array(
                'matricula',
                'campo',
                'contenido'
            );
            $arrValores = array(
                "'" . $matricula . "'",
                "'" . $campo . "'",
                "'" . addslashes($contenido) . "'"
            );
            regrabarRegistrosMysqliApi($dbx, 'mreg_est_inscritos_campos', $arrCampos, $arrValores, "id=" . $dataori["id"]);
        }

        if (!isset($dataori) || $dataori === false || empty($dataori)) {
            $dataori = array();
            $dataori["contenido"] = '';
        }
        $arrCampos = array(
            'matricula',
            'campo',
            'fecha',
            'hora',
            'codigobarras',
            'datoanterior',
            'datonuevo',
            'usuario',
            'ip',
            'tipotramite',
            'recibo'
        );
        $arrValores = array(
            "'" . ltrim($matricula, "0") . "'",
            "'" . $campo . "'",
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . $codbarras . "'",
            "'" . addslashes($dataori["contenido"]) . "'",
            "'" . addslashes($contenido) . "'",
            "'" . $_SESSION["generales"]["codigousuario"] . "'",
            "'" . \funcionesGenerales::localizarIP() . "'",
            "'" . $tipotramite . "'",
            "'" . $recibo . "'"
        );
        $res = insertarRegistrosMysqliApi($dbx, 'mreg_campos_historicos_' . date("Y"), $arrCampos, $arrValores);
        if ($res === false) {
            \logApi::general2('actualizarMregCamposHistoricos_' . date("Ymd"), ltrim($matricula, "0"), 'Error grabando en mreg_campos_historicos: ' . $_SESSION["generales"]["mensajeerror"]);
            return false;
        }

        //
        return true;
    }

}

?>
