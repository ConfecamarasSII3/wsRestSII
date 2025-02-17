<?php

/**
 * 
 * @param type $serialsobre
 * @param type $codigoempresa
 * @param type $pathabsoluto
 * @param type $logo
 * @param type $razonsocial
 * @param type $direccion
 * @param type $telefono
 * @param type $ciudad
 * @param type $email
 * @param type $reason
 * @param type $numliq
 * @param type $numrec
 * @param type $tipotramite
 * @param type $idecliente
 * @param type $nomcliente
 * @param type $idefirmante
 * @param type $nomfirmante
 * @param type $numfolios
 * @param type $numarchivos
 * @param type $dependencia
 * @param type $serie
 * @param type $subserie
 * @param type $textofirmante
 * @param type $archivos
 * @param type $signin_cert
 * @param type $private_cert
 * @param type $password_cert
 * @return string
 */
function armarPdfSobreDigital($serialsobre = '', $codigoempresa = '', $pathabsoluto = '', $logo = '', $razonsocial = '', $direccion = '', $telefono = '', $ciudad = '', $email = '', $reason = '', $numliq = '', $numrec = '', $tipotramite = '', $idecliente = '', $nomcliente = '', $idefirmante = '', $nomfirmante = '', $numfolios = '', $numarchivos = '', $dependencia = '', $serie = '', $subserie = '', $textofirmante = '', $archivos = array(), $signin_cert = '', $private_cert = '', $password_cert = '') {
    $_SESSION["generales"]["codigoempresa"] = $codigoempresa;    
    $_SESSION["generales"]["pathabsoluto"] = $pathabsoluto;    
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/tcpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/examples/lang/eng.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
    set_error_handler('myErrorHandler');
    
    ob_clean();
    if (!class_exists('PDFSobreNew')) {

        class PDFSobreNew extends TCPDF {

            public function Attachment($filestream) {
                if (!$this->pdfa_mode || ($this->pdfa_mode && $this->pdfa_version == 3)) {
                    if ((!TCPDF_STATIC::empty_string($filestream))
                            AND (@TCPDF_STATIC::file_exists($filestream) OR TCPDF_STATIC::isValidURL($filestream))
                            AND (!isset($this->embeddedfiles[basename($filestream)]))) {
                        $this->embeddedfiles[basename($filestream)] = array('f' => ++$this->n, 'n' => ++$this->n, 'file' => $filestream);
                    }
                }
            }

        }

    }

    // Imprime encabezados
    $pdf = new PDFSobreNew(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, 3);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();

    
    // Arma el codigo de barras
    $imagen = $_SESSION["generales"]["pathabsoluto"] . '/images/sobreManilaFinal1.png';
    $pdf->Image($imagen, 5, 5, 219, 270);
    $pdf->Image($logo, 10, 10, 30, 30);

    //
    $pdf->SetFont('Helvetica', '', 10);

    //
    $pdf->SetXY(0, 40);
    $pdf->Cell(0, 0, $razonsocial, 0, 1, 'C', 0, '', 0);
    $pdf->Cell(0, 0, $direccion, 0, 1, 'C', 0, '', 0);
    $pdf->Cell(0, 0, $telefono, 0, 1, 'C', 0, '', 0);
    $pdf->Cell(0, 0, $ciudad, 0, 1, 'C', 0, '', 0);
    $pdf->Ln();
    if (ltrim(trim($numliq),"0") != '') {
        $pdf->Cell(0, 0, 'Número de liquidación : ' . $numliq, 0, 1, 'C', 0, '', 0);
    }
    if (ltrim(trim($numrec),"0") != '') {
        $pdf->Cell(0, 0, 'Número de recuperación : ' . $numrec, 0, 1, 'C', 0, '', 0);
    }
    $pdf->Cell(0, 0, 'Fecha y hora de generación : ' . date("Y-m-d") . ' ' . date("H:i:s"), 0, 1, 'C', 0, '', 0);
    $pdf->Cell(0, 0, 'Tipo de trámite : ' . $tipotramite, 0, 1, 'C', 0, '', 0);
    $pdf->Cell(0, 0, 'Cliente : ' . $idecliente . ' - ' . $nomcliente, 0, 1, 'C', 0, '', 0);
    $pdf->Cell(0, 0, 'Firmante : ' . $idefirmante . ' - ' . $nomfirmante, 0, 1, 'C', 0, '', 0);
    $pdf->Cell(0, 0, 'Folios : ' . $numfolios, 0, 1, 'C', 0, '', 0);
    $pdf->Cell(0, 0, 'Archivos : ' . $numarchivos, 0, 1, 'C', 0, '', 0);
    $pdf->Cell(0, 0, 'Dependencia : ' . $dependencia, 0, 1, 'C', 0, '', 0);
    $pdf->Cell(0, 0, 'Serie : ' . $serie, 0, 1, 'C', 0, '', 0);
    $pdf->Cell(0, 0, 'Sub-Serie : ' . $subserie, 0, 1, 'C', 0, '', 0);
    $pdf->Cell(0, 0, 'Serial del sobre : ' . $serialsobre, 0, 1, 'C', 0, '', 0);
    $pdf->Ln();

    $pdf->MultiCell(0, 0, $textofirmante, 1, 'C', false, 0);
    $pdf->Ln();
    $pdf->Ln();

    $pdf->MultiCell(0, 0, '!!! IMPORTANTE !!!', 1, 'C', false, 0);
    $pdf->Ln();
    $pdf->Ln();


    $txt = '<p align="justify">De acuerdo con los procedimientos establecidos por la Cámara de Comercio para el firmado electrónico de trámites ';
    $txt .= 'que se presentan en forma no presencial, este sobre electrónico contiene la información digital de los formularios, ';
    $txt .= 'solicitudes y anexos que forman parte del trámite que se está realizando. Los mismos han sido aprobados por el firmante y se constituyen ';
    $txt .= 'en el soporte NO FISICO (DIGITAL) de los documentos a radicar.</p>';
    $txt .= '<p align="justify">Este sobre electrónico ha sido firmado digitalmente por la Cámara de Comercio para garantizar que el mismo y su contenido no pueda ser alterado después de su ';
    $txt .= 'elaboración y así asegurar la integridad de los documentos contenidos en el mismo, de acuerdo con los términos y principios establecidos en la Ley 527 ';
    $txt .= 'de 1999 y en los decretos que reglamentan las transacciones electrónicas en el territorio Colombiano.</p>';
    $txt .= '<p align="justify">Le sugerimos que lo almacene en un lugar seguro pues se constituye en el ORIGINAL de la información que se presentó en la Cámara de Comercio. ';
    $txt .= 'Este sobre, su contenido (anexos), así como la firma digital que lo avala, podrá ser visualizado con un visor de archivos PDF.</p>';
    $pdf->writeHTML($txt);

    //
    if (!empty($archivos)) {
        foreach ($archivos as $arc) {
            $pdf->Attachment($arc);
        }
    }

    // Firmar digitalmente
    $info = array(
        'Name' => $razonsocial,
        'Location' => $ciudad,
        'Reason' => $reason,
        'ContactInfo' => $email,
    );
    $pdf->Text(80, 220, 'Firmado digitalmente por');
    $pdf->Text(80, 224, $razonsocial);
    $pdf->Text(80, 228, date ("Y-m-d") . ' ' . date ("H:i:s"));
    $pdf->setSignature($signin_cert, $private_cert, $password_cert, '', 1, $info);
    $pdf->setSignatureAppearance(80, 220, 120, 20, -1);

    //
    $name1 = 'tmp/' . $codigoempresa . '-sobre-' . $numliq . '-' . date ("Ymd") . '-' . date ("His") . '.pdf';
    $name = $_SESSION["generales"]["pathabsoluto"] . '/' . $name1;
    $pdf->Output($name, 'F');
    unset($pdf);
    return $name1;
}

?>