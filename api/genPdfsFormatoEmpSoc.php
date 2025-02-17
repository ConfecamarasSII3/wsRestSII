<?php

/**
 * 
 * @param type $dbx
 * @param type $numrec
 * @param type $numliq
 * @param type $txtFirmaElectronica
 * @param type $txtFirmaManuscrita
 * @param type $nombreFirmante
 * @param type $numIdFirmante
 * @param type $fechaimprimir
 * @return string
 */
function armarPdfFormatoEmpSoc($dbx = null, $numrec = '', $numliq = 0) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/tcpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/examples/lang/eng.php');

    if (!class_exists('PDFEmpSoc')) {

        class PDFEmpSoc extends TCPDF {

            public $fechaimprimir = '';

            function Header() {
                $i = 0;
                if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg')) {
                    $this->Image($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg', 160, 10, 30, 28);
                }
                $i = $i + 5;
                $this->SetFont('Helvetica', 'B', 10);
                $this->SetXY(20, $i);
                $this->Cell(100, 4, '', 0, 0, 'L');
                $i = $i + 5;
                $this->SetFont('Helvetica', 'B', 10);
                $this->SetXY(20, $i);
                $this->Cell(100, 4, '', 0, 0, 'L');
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Helvetica', 'B', 10);
                $this->SetXY(20, $i);
                if ($this->fechaimprimir == '') {
                    $this->Cell(100, 4, retornarNombreMunicipioMysqliApi(null, MUNICIPIO) . ', ' . \funcionesGenerales::mostrarFechaLetras(date("Ymd")), 0, 0, 'L');
                } else {
                    $this->Cell(100, 4, retornarNombreMunicipioMysqliApi(null, MUNICIPIO) . ', ' . \funcionesGenerales::mostrarFechaLetras($this->fechaimprimir), 0, 0, 'L');
                }
                $this->Ln();
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Helvetica', 'B', 9);
                $this->SetXY(20, $i);
                $this->Cell(100, 4, 'Ref. MANIFESTACIÓN RELATIVA AL DESARROLLO DE EMPRENDIMIENTOS SOCIALES', 0, 0, 'L');
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Helvetica', 'B', 9);
                $this->SetXY(20, $i);
                $this->Cell(100, 4, ('Número de recuperación : ') . $_SESSION["tramite"]["numerorecuperacion"], 0, 0, 'L');
                $this->Ln();
                $this->Ln();
                $i = $this->GetY();
                $this->SetY($i);
            }

        }

    }

    //echo "entro a pdf armar mutacion<br>";
    // Imprime encabezados
    $pdf = new PDFEmpSoc(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    $pdf->fechaimprimir = date("Ymd");
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('SII');
    $pdf->SetTitle('Solicitud');
    $pdf->SetSubject('Solicitud');
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(20, 40, 10);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->setLanguageArray($l);
    $pdf->AddPage();
    // $pdf->titulo();
    $pdf->SetFont('Helvetica', 'B', 10);

    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(20);
    $pdf->Cell(100, 4, ('Señor(es)'), 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetX(20);
    $pdf->Cell(100, 4, RAZONSOCIAL_RESUMIDA, 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetX(20);
    $pdf->Cell(100, 4, ('Departamento de Registros Públicos'), 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetX(20);
    $pdf->Cell(100, 4, retornarNombreMunicipioMysqliApi(null, MUNICIPIO), 0, 0, 'L');
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();

    //
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(20);
    $pdf->writeHTML('MANIFESTACIÓN RELATIVA AL DESARROLLO DE EMPRENDIMIENTOS SOCIALES', true, false, true, false, 'C');
    $pdf->Ln();

    //
    if ($_SESSION["tramite"]["fecharecibo"] != '') {
        $tx = '<strong>Fecha de pago :</strong> ' . $_SESSION["tramite"]["fecharecibo"] . '<br>';
        $tx .= '<strong>Número del recibo de pago :</strong> ' . $_SESSION["tramite"]["numerorecibo"] . '<br>';
    } else {
        $tx = '<strong>Fecha de solicitud :</strong> ' . date("Y-m-d") . '<br>';
    }
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(20);
    $pdf->writeHTML($tx, true, false, true, false, 'J');
    $pdf->Ln();

    //
    $tx = '<strong>Razón social o nombre :</strong> ' . $_SESSION["formulario"]["datos"]["nombre"] . '<br>';
    $tx .= '<strong>Nit o identificación :</strong> ' . $_SESSION["formulario"]["datos"]["identificacion"] . '<br>';
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(20);
    $pdf->writeHTML($tx, true, false, true, false, 'J');
    $pdf->Ln();

    //
    $tx = 'El literal a del artículo 3 de la Ley 2234 de 2022 define Emprendimiento Social como aquél “(…) adelantado ';
    $tx .= 'por personas naturales o jurídicas con o sin ánimo de lucro que mediante el empleo de técnicas empresariales ';
    $tx .= 'y de mercado, busca solucionar problemáticas, así como atender y/o fortalecer procesos que afectan diversos ';
    $tx .= 'ámbitos de las comunidades, beneficiando principalmente, aunque no de manera exclusiva a comunidades y/o ';
    $tx .= 'poblaciones en condición de vulnerabilidad.”<br><br>';
    $tx .= 'Teniendo en cuenta la definición legal, atendiendo la instrucción de la Superintendencia de Sociedades, ';
    $tx .= 'esta entidad cameral requiere que responda afirmativa (SI) o negativamente (NO) si usted considera que ';
    $tx .= 'su (s) actividad (es) se desarrolla (n) como emprendimiento social (marque con una equis “X”):<br><br>';
    if ($_SESSION["formulario"]["datos"]["emprendimientosocial"] == 'S') {
        $tx .= '<strong>SI:___ X ___</strong><br>';
        $tx .= '<strong>NO:_________</strong><br><br>';
    } else {
        if ($_SESSION["formulario"]["datos"]["emprendimientosocial"] == 'N') {
            $tx .= '<strong>SI:_________</strong><br>';
            $tx .= '<strong>NO:___ X ___</strong><br><br>';
        } else {
            $tx .= '<strong>SI:_________</strong><br>';
            $tx .= '<strong>NO:_________</strong><br><br>';
        }
    }
    if ($_SESSION["formulario"]["datos"]["emprendimientosocial"] == 'S') {
        if ($_SESSION["formulario"]["datos"]["empsoccategorias"] != '') {
            $tx .= '<strong>CATEGORIAS DEL EMPREDIMIENTO SOCIAL:</strong><br>';
            foreach ($_SESSION["formulario"]["datos"] as $key => $valor) {
                if (substr($key, 0, 10) == 'empsoccat_') {
                    if ($valor == 'S') {
                        $tx .= '- ' . retornarRegistroMysqliApi($dbx, 'tablas', "tabla='empsoc_categorias' and idcodigo='" . $key . "'", "descripcion") . "<br>";
                    }
                }
            }
            if ($_SESSION["formulario"]["datos"]["empsoccategorias_otros"] != '') {
                $tx .= '- CUALES: ' . $_SESSION["formulario"]["datos"]["empsoccategorias_otros"] . '<br>';
            }
            $tx .= '<br>';
        }
        if ($_SESSION["formulario"]["datos"]["empsocbeneficiarios"] != '') {
            $tx .= '<strong>BENEFICIARIOS DEL EMPREDIMIENTO SOCIAL:</strong><br>';
            foreach ($_SESSION["formulario"]["datos"] as $key => $valor) {
                if (substr($key, 0, 10) == 'empsocben_') {
                    if ($valor == 'S') {
                        $tx .= '- ' . retornarRegistroMysqliApi($dbx, 'tablas', "tabla='empsoc_beneficiarios' and idcodigo='" . $key . "'", "descripcion") . "<br>";
                    }
                }
            }
            if ($_SESSION["formulario"]["datos"]["empsocbeneficiarios_otros"] != '') {
                $tx .= '- CUALES: ' . $_SESSION["formulario"]["datos"]["empsocbeneficiarios_otros"] . '<br>';
            }

            $tx .= '<br>';
        }
    }
    $tx .= '<strong>DECLARACIÓN:</strong><br>';
    $tx .= 'La información antes suministrada hace parte del trámite solicitado (matricula o renovación) y se entiende ';
    $tx .= 'firmado con la suscripción del formulario RUES.<br><br>';
    $tx .= '<strong>*Nota:</strong> Cualquier falsedad en que se incurra podrá ser sancionada de acuerdo con la Ley ';
    $tx .= '(artículo 38 del Código de Comercio, normas concordantes y complementarias).';

    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(20);
    $pdf->writeHTML($tx, true, false, true, false, 'J');
    $pdf->Ln();
    $pdf->Ln();

    $name = session_id() . '-FormatoEmpSoc-' . date("Ymd") . '-' . date("His") . '.pdf';
    $pdf->Output($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, "F");
    return $name;
}

?>