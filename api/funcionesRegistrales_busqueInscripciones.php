<?php

class funcionesRegistrales_busqueInscripciones {

    public static function busqueInscripciones($mysqli, $mat, $actos, $fini = '') {
        $ix = 0;
        $regs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "matricula='" . $mat . "'", "fecharegistro");
        if ($regs && !empty($regs)) {
            foreach ($regs as $r) {
                if ($r["fecharegistro"] > $fini) {
                    if ($r["libro"] != 'RM07' && $r["libro"] != 'RE52') {
                        $ind = $r["libro"] . '-' . $r["acto"];
                        if (!isset($actos[$ind])) {
                            if (!\funcionesGenerales::validarFecha($r["fecharegistro"])) {
                                \funcionesGenerales::enviarCorreoError('Matrícula: ' . $mat . ', registro: ' . $r["libro"] . '-' . $r["registro"] . ', error en fecha de inscripción en libros (' . $r["fecharegistro"] . '), por favor revisar');
                                    return false;            
                            }
                            if (\funcionesGenerales::diferenciaEntreFechasCalendario(date("Ymd"), $r["fecharegistro"]) < 1096) {
                                $ix++;
                            }
                        } else {
                            if ($actos[$ind]["idgrupoacto"] != '018' && $actos[$ind]["idgrupoacto"] != '019' && $actos[$ind]["idgrupoacto"] != '001') {
                                if (!\funcionesGenerales::validarFecha($r["fecharegistro"])) {
                                   \funcionesGenerales::enviarCorreoError('Matrícula: ' . $mat . ', registro: ' . $r["libro"] . '-' . $r["registro"] . ', error en fecha de inscripcion en libros (' . $r["fecharegistro"] . '), por favor revisar');
                                    return false;            
                                }
                                if (\funcionesGenerales::diferenciaEntreFechasCalendario(date("Ymd"), $r["fecharegistro"]) < 1096) {
                                    $ix++;
                                }
                            }
                        }
                    }
                }
            }
        }
        unset($regs);
        if ($ix > 0) {
            return true;
        }
        return false;
    }
}

?>
