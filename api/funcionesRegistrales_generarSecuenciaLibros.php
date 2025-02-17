<?php

class funcionesRegistrales_generarSecuenciaLibros {

    public static function generarSecuenciaLibros($dbx, $libro = '', $crear = 'si') {
        if (strlen($libro) == 2) {
            if ($libro < '50') {
                $libro = 'RM' . $libro;
            } else {
                $libro = 'RE' . $libro;
            }
        }
        $ins = 0;
        $ins = \funcionesRegistrales::retornarMregSecuencia($dbx, 'LIBRO-' . $libro);
        if ($ins == 0) {
            $_SESSION["generales"]["mensajeerror"] = 'No fue posible localizar la ultima inscripcion para el libro' . $libro;
            return false;
        }
        $seguir = "si";
        while ($seguir == 'si') {
            $ins++;
            if (contarRegistrosMysqliApi($dbx, 'mreg_est_inscripciones', "libro='" . $libro . "' and registro='" . $ins . "'") == 0) {
                $seguir = 'no';
            }
        }
        \funcionesRegistrales::actualizarMregSecuenciasAsentarRecibo($dbx, 'LIBRO-' . $libro, $ins);
        if ($crear == 'si') {
            $arrCampos = array(
                'libro',
                'registro',
                'dupli',
                'fecharegistro',
                'matricula',
                'proponente',
                'tipoidentificacion',
                'identificacion',
                'fechadocumento',
                'origendocumento',
                'numerodocumento',
                'codigolibro',
                'descripcionlibro',
                'paginainicial',
                'numeropaginas',
                'acto',
                'noticia',
                'fecharadicacion',
                'firma',
                'clavefirmado',
                'ctrsello',
                'ctrrotulo',
                'ctrrecurso',
                'ctrnotificacion',
                'ctrrevoca',
                'registrorevocacion',
                'estado'
            );
            $arrValores = array(
                "'" . $libro . "'",
                "'" . $ins . "'",
                "'01'",
                "'" . date("Ymd") . "'",
                "''",
                "''",
                "''",
                "''",
                "''",
                "''",
                "''",
                "''",
                "''",
                "''",
                "''",
                "''",
                "''",
                "''",
                "''",
                "''",
                "'NO'",
                "'0'",
                "'0'",
                "''",
                "'0'",
                "''",
                "'V'"
            );
            $res = insertarRegistrosMysqliApi($dbx, 'mreg_est_inscripciones', $arrCampos, $arrValores);
            if ($res === false) {
                return false;
            }
        }
        return $ins;
    }

}

?>
