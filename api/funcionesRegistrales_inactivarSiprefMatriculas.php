<?php

class funcionesRegistrales_inactivarSiprefMatriculas {

    public static function inactivarSiprefMatriculas($mysqli, $mat, $fmat, $fren) {

// carga tabla actos
        // $_SESSION["actos"] = array();
        if (!isset($_SESSION["actos"])) {
            $_SESSION["actos"] = array();
            $rets = retornarRegistrosMysqliApi($mysqli, 'mreg_actos', "1=1", "idlibro,idacto");
            foreach ($rets as $r) {
                $ind = $r["idlibro"] . '-' . $r["idacto"];
                $_SESSION["actos"][$ind] = $r;
            }
        }
        unset($rets);

//
        $return = false;
        
//
        
        if (!\funcionesGenerales::validarFecha($fmat)) {
            \funcionesGenerales::enviarCorreoError('Matrícula: ' . $mat . ', error en fecha de matrícula (' . $fmat . '), por favor revisar');
            return false;
        }
        if (!\funcionesGenerales::validarFecha($fren)) {
            \funcionesGenerales::enviarCorreoError('Matrícula: ' . $mat . ', error en fecha de renovacion (' . $fren . '), por favor revisar');
            return false;            
        }
        
        if (\funcionesGenerales::diferenciaEntreFechasCalendario(date("Ymd"), $fmat) > 1095) {
            if ($fmat != $fren) {
                if (\funcionesGenerales::diferenciaEntreFechasCalendario(date("Ymd"), $fren) > 1095) {
                    if (\funcionesRegistrales::busqueInscripciones($mysqli, $mat, $_SESSION["actos"], $fren)) {
                        $return = false;
                    } else {
                        $return = true;
                    }
                }
            } else {
                if (\funcionesRegistrales::busqueInscripciones($mysqli, $mat, $_SESSION["actos"], $fren)) {
                    $return = false;
                } else {
                    $return = true;
                }
            }
        }

//
        if ($return == true) {
            $reacts = retornarRegistrosMysqliApi($mysqli, "mreg_sipref_controlevidencias", "matricula='" . $mat . "' and tipotramite='reactivacionmatricula'", "matricula,fecha");
            if ($reacts && !empty($reacts)) {
                foreach ($reacts as $rx) {
                    if (!\funcionesGenerales::validarFecha($rx["fecha"])) {
                        \funcionesGenerales::enviarCorreoError('Matrícula: ' . $mat . ', error en fecha de reactivación (' . $rx["fecha"] . '), por favor revisar');
                        return false;
                    }
                    if (\funcionesGenerales::diferenciaEntreFechasCalendario(date("Ymd"), $rx["fecha"]) < 1096) {
                        $return = false;
                    }
                }
            }
        }

        //
        return $return;
    }
}

?>
