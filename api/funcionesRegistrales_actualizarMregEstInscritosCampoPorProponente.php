<?php

class funcionesRegistrales_actualizarMregEstInscritosCampoPorProponente {

    public static function actualizarMregEstInscritosCampoPorProponente($dbx  = null, $proponente = '', $campo = '', $contenido = '', $tipocampo = 'varchar', $codbarras = '', $tipotramite = '', $recibo = '') {


        // Valida que el expedienmte tenga diferencias
        if (ltrim($proponente, "0") == '') {
            return true;
        }

        $datoori = retornarRegistroMysqliApi($dbx,'mreg_est_inscritos', "proponente='" . $proponente . "'", $campo);
        if (ltrim(trim($datoori), "0") != ltrim(trim($contenido), "0")) {
            $arrCampos = array(
                $campo,
                'fecactualizacion',
                'compite360',
                'rues',
                'ivc'
            );
            if ($tipocampo == 'varchar') {
                $arrValores = array(
                    "'" . trim($contenido) . "'",
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
            $res = regrabarRegistrosMysqliApi($dbx,'mreg_est_inscritos', $arrCampos, $arrValores, "proponente='" . $proponente . "'");
            if ($res === false) {
                \logApi::general2('actualizarMregEstInscritosCampoPorProponente_' . date("Ymd"), ltrim($proponente, "0"), 'Error regrabando en mreg_est_inscritos: ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }

            if (trim($datoori["matricula"]) != '') {
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
                    "'" . ltrim($datoori["matricula"], "0") . "'",
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
                $res = insertarRegistrosMysqliApi($dbx,'mreg_campos_historicos_' . date("Y"), $arrCampos, $arrValores);
                if ($res === false) {
                    \logApi::general2('actualizarMregEstInscritosCampoPorProponente_' . date("Ymd"), ltrim($datoori["matricula"], "0"), 'Error grabando en mreg_campos_historicos: ' . $_SESSION["generales"]["mensajeerror"]);
                    return false;
                }
            }
        }
        return true;
    }

}

?>
