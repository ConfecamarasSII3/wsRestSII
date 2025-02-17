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
function armarPdfSolicitudReingreso($dbx, $arrCod = array(), $lisAnx = array(), $idc, $ide, $nom, $txtFirmaElectronica = '') {
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
    $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode('Ref. SOLICITUD DE REINGRESO'), 0, 0, 'L');
    $pdf->Ln();
    $i = $pdf->GetY();
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(12, $i);
    $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode('Código de barras : ') . $arrCod["codbarras"], 0, 0, 'L');
    $pdf->Ln();
    $pdf->Ln();
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
    $pdf->Ln();
    $pdf->Ln();

    $txttipo = retornarRegistroMysqliApi($dbx, 'mreg_tipoidentificacion', "id='" . $idc . "'", "descripcion");

    //
    $tx = 'Yo, ' . $nom . ' identificado con ' .
            $txttipo .
            ' número ' . $ide .
            ' me permito solicitar el reingreso del trámite radicado bajo el código de barras No. ' . $arrCod["codbarras"] . ' que fue requerido el ' .
            \funcionesGenerales::mostrarFecha($arrCod["fechadevolucion"]) . ' mediante acto administrrativo No. ' . $arrCod["numerodevolucion"] . ' ' .
            'puesto que se debían subsanar aspectos que limitaban su registro o asentamiento.';

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

    $name1 = $arrCod["codbarras"] . '-SolicitudReingreso-' . date("Ymd") . '-'.date("His") . '.pdf';
    $name = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name1;
    $pdf->Output($name, "F");
    return $name1;
}
