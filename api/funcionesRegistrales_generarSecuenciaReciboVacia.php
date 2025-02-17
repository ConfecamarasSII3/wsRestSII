<?php

class funcionesRegistrales_generarSecuenciaReciboVacia {

    public static function generarSecuenciaReciboVacia($mysqli, $tipo = 'S') {

//
        $rec = 0;
        $recx = '';

// ************************************************************************************************ //
// Localiza el numero del recibo a generar dependiendo del tipo de documento
// ************************************************************************************************ //
        if ($tipo == 'S') { // Si son recibos normales
            $tclave = 'RECIBOS-NORMALES';
            $rec = \funcionesRegistrales::retornarMregSecuenciaMaxAsentarRecibo($mysqli, $tclave);
        }
        if ($tipo == 'M') { // Si son notas de reversion
            $tclave = 'RECIBOS-NOTAS';
            $rec = \funcionesRegistrales::retornarMregSecuenciaMaxAsentarRecibo($mysqli, $tclave);
        }
        if ($tipo == 'H') { // Si son gastos administrativos
            $tclave = 'RECIBOS-GA';
            $rec = \funcionesRegistrales::retornarMregSecuenciaMaxAsentarRecibo($mysqli, $tclave);
        }
        if ($tipo == 'D') { // Si son consultas
            $tclave = 'RECIBOS-CO';
            $rec = \funcionesRegistrales::retornarMregSecuenciaMaxAsentarRecibo($mysqli, $tclave);
        }
        if ($rec === false) {
            \logApi::general2('generarSecuenciaReciboVacia_' . date("Ymd"), '', 'Error recuperando la secuencia del recibo de caja : ' . $_SESSION["generales"]["mensajeerror"]);
            $_SESSION["generales"]["mensajeerror"] = 'Error recuperando la secuencia del recibo de caja';
            return false;
        } else {
            \logApi::general2('generarSecuenciaReciboVacia_' . date("Ymd"), '', 'Secuencia generada : ' . $rec);
        }

// ************************************************************************************************ //
        if ($rec == '') {
            $rec = 0;
        } else {
            $rec = intval($rec);
        }

// ************************************************************************************************ //
// Revisa que el recibo no esta creado previamente, de ser asi, genera un nuevo numero
// ************************************************************************************************ //
        $seguir = "si";
        while ($seguir == 'si') {
            $rec++;
            $recx = $tipo . sprintf("%09s", $rec);
            if (contarRegistrosMysqliApi($mysqli, 'mreg_recibosgenerados', "recibo='" . $recx . "'") == 0) {
                $seguir = 'no';
            }
        }

// ************************************************************************************************ //
// Actualiza el consecutivo en claves valor
// ************************************************************************************************ //	
        \funcionesRegistrales::actualizarMregSecuenciasAsentarRecibo($mysqli, $tclave, $rec);

//
        return $recx;
    }

}

?>
