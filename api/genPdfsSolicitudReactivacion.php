<?php

/**
 * 
 * @param type $dbx
 * @param type $nsipref
 * @param type $mat
 * @param type $nmat
 * @param type $org
 * @param type $cat
 * @param type $idc
 * @param type $ide
 * @param type $nom
 * @param type $car
 * @param type $txtFirmaElectronica
 * @return string
 */
function armarPdfSolicitudReactivacion($dbx, $nsipref, $mat, $nmat, $org, $cat, $idc, $ide, $nom, $car, $txtFirmaElectronica = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/fpdf_protection.php');
    if (!defined('FPDF_FONTPATH')) {
        define('FPDF_FONTPATH', $_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/font/');
    }

    if (!class_exists('PDFCerti')) {
        class PDFCerti extends FPDF {
        }
    }


    // Imprime encabezados
    $pdf = new PDFCerti("Portrait", "mm", "Letter");
    $pdf->AliasNbPages();

    $i = 20;
    $pdf->AddPage();
    $pdf->SetMargins(10, 20, 7);
    if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg')) {
        $pdf->Image($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg', 150, 20, 45, 28);
    }
    $i = $i + 5;
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(12, $i);
    $pdf->Cell(100, 4, '', 0, 0, 'L');
    $i = $i + 5;
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(12, $i);
    $pdf->Cell(100, 4, '', 0, 0, 'L');
    $pdf->Ln();
    $i = $pdf->GetY() + 5;
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(12, $i);
    $pdf->Cell(100, 4, retornarNombreMunicipioMysqliApi(null, MUNICIPIO) . ', ' . \funcionesGenerales::mostrarFechaLetras(date("Ymd")), 0, 0, 'L');
    $pdf->Ln();
    $pdf->Ln();
    $i = $pdf->GetY() + 10;
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(12, $i);
    $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode('Ref. SOLICITUD DE REACTIVACION'), 0, 0, 'L');
    $pdf->Ln();
    $i = $pdf->GetY();
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(12, $i);
    $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode('Código de control : ') . $nsipref, 0, 0, 'L');
    $pdf->Ln();
    $pdf->Ln();

    $pdf->SetFont('Helvetica', 'B', 10);

    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode('Señor(es)'), 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode(RAZONSOCIAL), 0, 'J', 0);
    // $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode(RAZONSOCIAL), 0, 0, 'L');
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode('Departamento de Registros Públicos'), 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, retornarNombreMunicipioMysqliApi($dbx, MUNICIPIO), 0, 0, 'L');
    $pdf->Ln();
    $pdf->Ln();

    $txttipo = retornarRegistroMysqliApi($dbx, 'mreg_tipoidentificacion', "id='" . $idc . "'", "descripcion");

    //
    if ($org == '01') {
        $tx = 'Yo, ' . $nom . ' identificado con ' .
                $txttipo .
                ' número ' . $ide .
                ' actuando en nombre propio me permito solicitar la reactivación de mi matrícula mercantil No. ' . $mat;
    }

    //
    if ($org == '02') {
        $tx = 'Yo, ' . $nom . ' identificado con ' .
                $txttipo .
                ' número ' . $ide .
                ' en calidad de propietario del establecimiento ' . $nmat .
                ' matriculado bajo el número ' . $mat .
                ', atentamente solicito la reactivación de la matrícula mercantil del establecimiento en cuestión.';
    }

    //
    if ($org > '02' && $cat == '1') {
        $tx = 'Yo, ' . $nom . ' identificado con ' .
                $txttipo .
                ' número ' . $ide .
                ' en calidad de ' . $car . ' de la persona jurídica ' . $nmat .
                ' matriculado bajo el número ' . $mat .
                ', atentamente solicito la reactivación de la matrícula mercantil.';
    }

    //
    if ($org > '02' && $cat == '2') {
        $tx = 'Yo, ' . $nom . ' identificado con ' .
                $txttipo .
                ' número ' . $ide .
                ' en calidad de ' . $car . ' de la sucursal ' . $nmat .
                ' matriculado bajo el número ' . $mat .
                ', atentamente solicito la reactivación de la matrícula mercantil.';
    }

    //
    if ($org > '02' && $cat == '3') {
        $tx = 'Yo, ' . $nom . ' identificado con ' .
                $txttipo .
                ' número ' . $ide .
                ' en calidad de ' . $car . ' de la agencia ' . $nmat .
                ' matriculado bajo el número ' . $mat .
                ', atentamente solicito la reactivación de la matrícula mercantil.';
    }

    $tx = \funcionesGenerales::utf8_decode($tx);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, $tx, 0, 'J', 0);
    $pdf->Ln();
    $pdf->Ln();


    //firmado electronico
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode($txtFirmaElectronica), 0, 'C', 0);
    $pdf->Ln();
    $pdf->Ln();

    $name1 = $nsipref . '-SolicitudReactivacion.pdf';
    $name = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name1;
    $pdf->Output($name, "F");
    return $name1;
}

?>