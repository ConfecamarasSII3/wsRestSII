<?php

class funcionesPdfs {

    public static function generarSelloRegistros($dbx, $registro, $libro, $numreg, $fecha, $hora, $acto, $txtacto, $expediente, $nombre, $nit, $usuarioFirma = '') {
        require_once ('fpdf153/fpdf_protection.php');
        if (!defined('FPDF_FONTPATH')) {
            // define('FPDF_FONTPATH', $_SESSION["generales"]["pathabsoluto"] . '/includes/fpdf153/fonts/');
            define('FPDF_FONTPATH', 'fpdf153/fonts/');
        }
        $dimension = array(180, 140);
        $pdf = new FPDF("Portrait", "mm", $dimension);
        $pdf->AliasNbPages();
        $pdf->AddPage();
        if (file_exists('../../../images/logocamara' . CODIGO_EMPRESA . '.jpg')) {
            $pdf->Image('../../../images/logocamara' . CODIGO_EMPRESA . '.jpg', 10, 7, 25, 25);
        }
        $pdf->SetXY(30, 12);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->MultiCell(150, 4, RAZONSOCIAL, 0, 'C');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(150, 4, "Nit. " . NIT, 0, 0, 'C');
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        switch ($registro) {
            case "Prop": $pdf->SetFont('Arial', 'B', 6);
                $pdf->Cell(150, 4, "DEL REGISTRO DE PROPONENTES", 0, 0, "C");
                break;
            case "RegPro": $pdf->SetFont('Arial', 'B', 6);
                $pdf->Cell(150, 4, "DEL REGISTRO DE PROPONENTES", 0, 0, "C");
                break;
            case "RegMer": $pdf->SetFont('Arial', 'B', 6);
                $pdf->Cell(150, 4, "DEL REGISTRO MERCANTIL", 0, 0, "C");
                break;
            case "RegEsadl": $pdf->SetFont('Arial', 'B', 6);
                $pdf->Cell(150, 4, "DEL REGISTRO DE LAS ENTIDADES SIN ANIMO DE LUCRO", 0, 0, "C");
                break;
        }
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(150, 4, "Libro: " . $libro, 0, 0, 'C');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(150, 4, "Numero Registro: " . $numreg, 0, 0, 'C');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(150, 4, "Fecha: " . $fecha, 0, 0, 'C');
        $pdf->Ln();
        if (trim($hora) != '') {
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->Cell(150, 4, "Hora: " . $hora, 0, 0, 'C');
            $pdf->Ln();
        }
        $pdf->Ln();

        //2017-11-30 WSIERRA:  Blanquear $expediente si cumple las siguientes opciones
        switch ($expediente) {
            case 'NUEVANAT':
            case 'NUEVAJUR':
            case 'NUEVAEST':
            case 'NUEVASUC':
            case 'NUEVAAGE':
            case 'NUEVAESA':
                $expediente = '';
                break;
            default:
                break;
        }
        //
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(150, 4, "Expediente: " . $expediente, 0, 0, 'C');
        $pdf->Ln();
        if (trim($nit) != '') {
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->Cell(150, 4, "Nit: " . $nit, 0, 0, 'C');
            $pdf->Ln();
        }
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->MultiCell(150, 4, "Nombre: " . utf8_decode($nombre), 0, 'C');
        // $pdf->Cell(150, 4, "Nombre: " . utf8_decode($nombre), 0, 0, 'C');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(150, 4, "Acto: " . $acto, 0, 0, 'C');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->MultiCell(150, 4, "Noticia: " . utf8_decode($txtacto), 0, 'C');
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Cell(150, 4, "El secretario (o su delegado)", 0, 0, 'C');
        $pdf->Ln();
        $y = $pdf->GetY();

        //
        if (trim($usuarioFirma) == '') {
            if ($_SESSION["generales"]["codigousuario"] == '' || $_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
                $usuarioFirma = retornarClaveValorSii2($dbx, '90.01.01');
            } else {
                $usuarioFirma = $_SESSION["generales"]["codigousuario"];
            }
        }

        // $usuFirma = retornarRegistroSinUtf8('usuariosfirmas', "idusuario='" . $usuarioFirma . "'");
        $usuFirma = retornarRegistroMysqli2($dbx, 'usuariosfirmas', "idusuario='" . $usuarioFirma . "'");
        if ($usuFirma === false || empty($usuFirma)) {
            $usuarioFirma = retornarClaveValorSii2($dbx, '90.01.01');
            // $usuFirma = retornarRegistroSinUtf8('usuariosfirmas', "idusuario='" . $usuarioFirma . "'");
            $usuFirma = retornarRegistroMysqli2($dbx, 'usuariosfirmas', "idusuario='" . $usuarioFirma . "'");
        }

        if ($usuFirma === false || empty($usuFirma)) {
            return false;
        }

        $f = fopen('../../../tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . session_id() . '-firma.jpg', "wb");
        fwrite($f, $usuFirma["firma"]);
        fclose($f);
        unset($usuFirma);

        $pdf->Image('../../../tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . session_id() . '-firma.jpg', 90, $y, 20, 20);
        unlink('../../../tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . session_id() . '-firma.jpg');
        $name = '../../../tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $registro . '-' . $libro . '-' . $numreg . '.pdf';
        $name1 = $_SESSION["generales"]["codigoempresa"] . '-' . $registro . '-' . $libro . '-' . $numreg . '.pdf';
        $pdf->Output($name, "F");

        \logSii2::general2('GeneracionSellosInscripciones_' . date("Ymd"), '', 'Sello - ' . $libro . '-' . $numreg . ', sellado por: ' . $usuarioFirma);
        return $name1;
    }

}
