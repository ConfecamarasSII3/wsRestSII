<?php
 
function unirPdfsApi($lista = array(), $salida = '', $orientation = 'P', $unit = 'mm', $format = 'A4') {
    unirPdfsApiV2 ($lista, $salida);
    /*
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/fpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/fpdi/fpdi.php');

    if (!class_exists('concat_pdf')) {

        class concat_pdf extends FPDI {

            var $files = array();

            function setFiles($files) {
                $this->files = $files;
            }

            function concat() {
                foreach ($this->files AS $file) {

                    if (!empty($file)) {
                        $pagecount = $this->setSourceFile($file);
                        for ($i = 1; $i <= $pagecount; $i++) {
                            $tplidx = $this->ImportPage($i);
                            $s = $this->getTemplatesize($tplidx);
                            $sx = '';
                            if ($s['h'] > $s['w']) {
                                $sx = 'P';
                            } else {
                                $sx = 'L';
                            }
                            $this->AddPage($sx);
                            $this->useTemplate($tplidx);
                        }
                    } else {
                        $this->AddPage();
                    }
                }
            }

        }

    }

    //$pdf = new concat_pdf("Portrait", "mm", "Letter");//Ajuste necesario en certificados virtuales
    //"P","mm","A4"
    $pdf = new concat_pdf($orientation, $unit, $format);
    $pdf->setFiles($lista);
    $pdf->concat();
    $pdf->Output($salida, 'F');
    */
}

function unirPdfsApiV2($lista = array(), $salida = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/fpdf_merge.php');
    $merge = new FPDF_Merge();
    foreach ($lista as $f) {
        $merge->add($f);
    }
    $merge->output($salida);
    unset ($merge);
}
?>