<?php

class funcionesRegistrales_actualizarMregInscritosPendienteNuevoCertificado {

    public static function actualizarMregInscritosPendienteNuevoCertificado($mysqli = null, $libro = null, $acto = null, $matricula = null) {
        $acto = retornarRegistroMysqliApi($mysqli, 'mreg_actos', "idlibro='" . $libro . "' and idacto='" . $acto . "'");
        if ($acto["idgrupoacto"] != '002' && // Cancelaciones
                $acto["idgrupoacto"] != '003' && // Mutaciones
                $acto["idgrupoacto"] != '004' && // Libros
                $acto["idgrupoacto"] != '046' && // Sitios web
                $acto["idgrupoacto"] != '047' && // habilitación del servicio de transporte
                $acto["idgrupoacto"] != '052' && // Proponente
                $acto["idgrupoacto"] != '999' // otros
        ) {
            if (ltrim($matricula, "0") != '') {
                $exptx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . ltrim($matricula, "0") . "'", "matricula,organizacion,categoria,ctrestmatricula,pendiente_ajuste_nuevo_formato,fecha_pendiente_ajuste_nuevo_formato");
                if ($exptx &&
                        ($exptx["ctrestmatricula"] == 'MA' ||
                        $exptx["ctrestmatricula"] == 'IA' ||
                        $exptx["ctrestmatricula"] == 'MI' ||
                        $exptx["ctrestmatricula"] == 'II' ||
                        $exptx["ctrestmatricula"] == 'MR' ||
                        $exptx["ctrestmatricula"] == 'IR')
                ) {
                    if ($exptx["organizacion"] > '02' && ($exptx["categoria"] == '1' || $exptx["categoria"] == '2')) {
                        $marcar = 'no';
                        if ($exptx["organizacion"] == '12' || $exptx["organizacion"] == '14') {
                            if (defined('FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL') && FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL != '' && FECHA_INICIO_NUEVO_CERTIFICADO_CERESADL <= date("Ymd")) {
                                $marcar = 'si';
                            } else {
                                if (defined('FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI') && FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI != '' && FECHA_INICIO_NUEVO_CERTIFICADO_CEREXI <= date("Ymd")) {
                                    $marcar = 'si';
                                }
                            }
                        }
                        if ($marcar == 'si') {
                            if ($exptx["pendiente_ajuste_nuevo_formato"] == '') {
                                $arrCampos = array(
                                    'pendiente_ajuste_nuevo_formato',
                                    'fecha_pendiente_ajuste_nuevo_formato'
                                );
                                $arrValores = array(
                                    "'P'",
                                    "'" . date("Ymd") . ' ' . date("His") . "'"
                                );
                                regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . ltrim($matricula, "0") . "'");
                            }
                        }
                    }
                }
            }
        }
        return true;
    }


}

?>
