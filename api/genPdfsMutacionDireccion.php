<?php

function armarPdfMutacionDireccion($dbx = null, $numrec = '', $numliq = 0, $txtFirmaElectronica = '') {
    require_once ('../componentes/fpdf186/fpdf.php');

    // Define llamado a libreria fpdf
    if (!defined('FPDF_FONTPATH')) {
        define('FPDF_FONTPATH', '../componentes/fpdf186/fonts/');
    }
    if (!class_exists('PDFCerti')) {

        class PDFCerti extends FPDF {

            function salto($lin, $arrDoc) {
                $lin1 = $this->GetY();
                $lin1 = $lin1 + $lin;
                if ($lin1 > 250) {
                    $this->titulo($arrDoc);
                    $lin1 = $this->GetY();
                }
                $this->Sety($lin1);
            }

            function titulo() {
                $i = 20;
                $this->AddPage();
                $this->SetMargins(10, 20, 7);
                    if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg')) {
                        $this->Image($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg', 150, 20, 45, 28);
                    }
                $i = $i + 5;
                $this->SetFont('Arial', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, '', 0, 0, 'L');
                $i = $i + 5;
                $this->SetFont('Arial', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, '', 0, 0, 'L');
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Arial', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, retornarNombreMunicipioMysqliApi(null,MUNICIPIO) . ', ' . \funcionesGenerales::mostrarFechaLetras(date("Ymd")), 0, 0, 'L');
                $this->Ln();
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Arial', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, 'Ref. SOLICITUD DE CAMBIO DE DIRECCION', 0, 0, 'L');
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Arial', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, \funcionesGenerales::utf8_decode('Número de recuperación : ') . $_SESSION["formulario"]["numrec"], 0, 0, 'L');
                $this->Ln();
                $this->Ln();
            }

        }

    }


    // Imprime encabezados
    $pdf = new PDFCerti("Portrait", "mm", "Letter");
    $pdf->AliasNbPages();
    $pdf->titulo();
    $pdf->SetFont('Arial', 'B', 10);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode('Señor(es)'), 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode(RAZONSOCIAL), 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode('Departamento de Registros Públicos'), 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, retornarNombreMunicipioMysqliApi(null,MUNICIPIO), 0, 0, 'L');
    $pdf->Ln();
    $pdf->Ln();
    
    if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
        $tx = 'Yo, ' . $_SESSION["formulario"]["datos"]["nombre"] . ' identificado con ' .
                retornarNombreTablasSirepMysqliApi(null,'38', $_SESSION["formulario"]["datos"]["tipoidentificacion"]) .
                ' número ' . $_SESSION["formulario"]["datos"]["identificacion"] .
                ' matriculado el registro mercantil bajo el número ' . $_SESSION["formulario"]["datos"]["matricula"] .
                ' obrando en mi propio nombre, atentamente solicito el cambio de la información de ubicación, la cual a partir ' .
                ' de la fecha aparecerá así: ';
    }
    if ($_SESSION["formulario"]["datos"]["organizacion"] == '02') {
        if ((!isset($_SESSION["tramite"]["identificacionfirmante"]))) {
            $_SESSION["formulario"]["datos"]["propietarios"][1]["nombrepropietario"] = '_______________________________________________';
            $_SESSION["formulario"]["datos"]["propietarios"][1]["identificacionpropietario"] = '________________________';
            $txttipo = '_______';
        } else {
            //$txttipo = retornarNombreTablasSirep('38', $_SESSION["formulario"]["datos"]["propietarios"][1]["idtipoidentificacionpropietario"]);
            $txttipo = retornarNombreTablasSirepMysqliApi(null,'38', $_SESSION["tramite"]["tipoidefirmante"]);
            $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] = $_SESSION["tramite"]["nombre1firmante"] . ' ' . $_SESSION["tramite"]["nombre2firmante"] . ' ' . $_SESSION["tramite"]["apellido1firmante"] . ' ' . $_SESSION["tramite"]["apellido2firmante"];
            $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] = $_SESSION["tramite"]["identificacionfirmante"];
        }
        $tx = 'Yo, ' . $_SESSION["formulario"]["datos"]["propietarios"][1]["nombrepropietario"] . ' identificado con ' .
                $txttipo .
                ' número ' . $_SESSION["formulario"]["datos"]["propietarios"][1]["identificacionpropietario"] .
                ' en calidad de propietario del establecimiento ' . $_SESSION["formulario"]["datos"]["nombre"] .
                ' matriculado bajo el número ' . $_SESSION["formulario"]["datos"]["matricula"] .
                ' atentamente solicito el cambio de la información de ubicación, la cual a partir ' .
                ' de la fecha aparecerá así: ';
    }
    if (($_SESSION["formulario"]["datos"]["organizacion"] > '02') && ($_SESSION["formulario"]["datos"]["categoria"] == '1')) {

        //if ((!isset($_SESSION["tramite"]["identificacionfirmante"]))) {
        if ($txtFirmaElectronica == '') {

                $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] = '_______________________________________________';
                $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] = '________________________';
                $txttipo = '_______';
        } else {
            //$txttipo = retornarNombreTablasSirep('38', $_SESSION["formulario"]["datos"]["replegal"][1]["idtipoidentificacionreplegal"]);
            $txttipo = retornarNombreTablasSirepMysqliApi(null,'38', $_SESSION["tramite"]["tipoidefirmante"]);
            $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] = $_SESSION["tramite"]["nombre1firmante"] . ' ' . $_SESSION["tramite"]["nombre2firmante"] . ' ' . $_SESSION["tramite"]["apellido1firmante"] . ' ' . $_SESSION["tramite"]["apellido2firmante"];
            $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] = $_SESSION["tramite"]["identificacionfirmante"];
        }
        $tx = 'Yo, ' . $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] . ' identificado con ' .
                $txttipo .
                ' número ' . $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] .
                ' en calidad de representante legal de la persona jurídica ' . $_SESSION["formulario"]["datos"]["nombre"] .
                ' matriculada/inscrita bajo el número ' . $_SESSION["formulario"]["datos"]["matricula"] .
                ' atentamente solicito el cambio de la información de ubicación, la cual a partir ' .
                ' de la fecha aparecerá así: ';
    }
    if (($_SESSION["formulario"]["datos"]["organizacion"] > '02') && ($_SESSION["formulario"]["datos"]["categoria"] == '2')) {

            if ($txtFirmaElectronica == '') {
                $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] = '_______________________________________________';
                $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] = '________________________';
                $txttipo = '_______';

        } else {
            //$txttipo = retornarNombreTablasSirep('38', $_SESSION["formulario"]["datos"]["replegal"][1]["idtipoidentificacionreplegal"]);
            $txttipo = retornarNombreTablasSirepMysqliApi(null,'38', $_SESSION["tramite"]["tipoidefirmante"]);
            $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] = $_SESSION["tramite"]["nombre1firmante"] . ' ' . $_SESSION["tramite"]["nombre2firmante"] . ' ' . $_SESSION["tramite"]["apellido1firmante"] . ' ' . $_SESSION["tramite"]["apellido2firmante"];
            $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] = $_SESSION["tramite"]["identificacionfirmante"];
        }
        $tx = 'Yo, ' . $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] . ' identificado con ' .
                $txttipo .
                ' número ' . $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] .
                ' en calidad de representante legal de la persona jurídica ' . $_SESSION["formulario"]["datos"]["nombre"] .
                ' matriculada bajo el número ' . $_SESSION["formulario"]["datos"]["matricula"] .
                ' atentamente solicito el cambio de la información de ubicación, la cual a partir ' .
                ' de la fecha aparecerá así: ';
    }
    if (($_SESSION["formulario"]["datos"]["organizacion"] > '02') && ($_SESSION["formulario"]["datos"]["categoria"] == '3')) {
        if (
                (!isset($_SESSION["formulario"]["datos"]["nombreadministrador"])) ||
                (trim($_SESSION["formulario"]["datos"]["nombreadministrador"]) == '')) {
            $_SESSION["formulario"]["datos"]["nombreadministrador"] = '_______________________________________________';
            $_SESSION["formulario"]["datos"]["identificacionadministrador"] = '________________________';
            $txttipo = '_______';
        } else {
            $txttipo = retornarNombreTablasSirepMysqliApi(null,'38', $_SESSION["formulario"]["datos"]["idtipoidentificacionadministrador"]);
        }
        $tx = 'Yo, ' . $_SESSION["formulario"]["datos"]["nombreadministrador"] . ' identificado con ' .
                $txttipo .
                ' número ' . $_SESSION["formulario"]["datos"]["identificacionadministrador"] .
                ' en calidad de administrador de la agencia ' . $_SESSION["formulario"]["datos"]["nombre"] .
                ' matriculada bajo el número ' . $_SESSION["formulario"]["datos"]["matricula"] .
                ' atentamente solicito el cambio de la información de ubicación, la cual a partir ' .
                ' de la fecha aparecerá así: ';
    }

    $tx = \funcionesGenerales::utf8_decode($tx);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, $tx, 0, 'J', 0);
    $pdf->Ln();
    $pdf->Ln();

    if ($_SESSION["formulario"]["datos"]["modcom"] == 'S') {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, 'DATOS DE UBICACION COMERCIAL', 0, 'J', 0);
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode('Dirección: ' . $_SESSION["formulario"]["datos"]["dircom"]), 0, 'J', 0);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, 'Domicilio: ' . $_SESSION["formulario"]["datos"]["muncom"] . ' - ' . retornarNombreMunicipioMysqliApi(null,$_SESSION["formulario"]["datos"]["muncom"]), 0, 'J', 0);
        $pdf->Ln(1);
        if (trim($_SESSION["formulario"]["datos"]["telcom1"]) != '') {
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetX(12);
            $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode('Teléfono Comercial: ') . $_SESSION["formulario"]["datos"]["telcom1"], 0, 'J', 0);
            $pdf->Ln(1);
        }
        if (trim($_SESSION["formulario"]["datos"]["telcom2"]) != '') {
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetX(12);
            $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode('Teléfono Comercial: ') . $_SESSION["formulario"]["datos"]["telcom2"], 0, 'J', 0);
            $pdf->Ln(1);
        }
        if (trim($_SESSION["formulario"]["datos"]["celcom"]) != '') {
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetX(12);
            $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode('Teléfono Móvil: ') . $_SESSION["formulario"]["datos"]["celcom"], 0, 'J', 0);
            $pdf->Ln(1);
        }
        if (trim($_SESSION["formulario"]["datos"]["faxcom"]) != '') {
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetX(12);
            $pdf->MultiCell(190, 4, 'Fax Comercial: ' . $_SESSION["formulario"]["datos"]["faxcom"], 0, 'J', 0);
            $pdf->Ln(1);
        }
        if (trim($_SESSION["formulario"]["datos"]["emailcom"]) != '') {
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetX(12);
            $pdf->MultiCell(190, 4, 'Email Comercial: ' . $_SESSION["formulario"]["datos"]["emailcom"], 0, 'J', 0);
            $pdf->Ln(1);
        }
        if (trim($_SESSION["formulario"]["datos"]["emailcom2"]) != '') {
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetX(12);
            $pdf->MultiCell(190, 4, 'Email Comercial (2): ' . $_SESSION["formulario"]["datos"]["emailcom2"], 0, 'J', 0);
            $pdf->Ln(1);
        }
        if (trim($_SESSION["formulario"]["datos"]["emailcom3"]) != '') {
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetX(12);
            $pdf->MultiCell(190, 4, 'Email Comercial (3): ' . $_SESSION["formulario"]["datos"]["emailcom3"], 0, 'J', 0);
            $pdf->Ln(1);
        }
        $pdf->Ln();
    }

    if ($_SESSION["formulario"]["datos"]["modnot"] == 'S') {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, 'DATOS DE UBICACION PARA NOTIFICACION', 0, 'J', 0);
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode('Dirección: ' . $_SESSION["formulario"]["datos"]["dirnot"]), 0, 'J', 0);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, 'Domicilio: ' . $_SESSION["formulario"]["datos"]["munnot"] . ' - ' . retornarNombreMunicipioMysqliApi(null,$_SESSION["formulario"]["datos"]["munnot"]), 0, 'J', 0);
        $pdf->Ln(1);
        if (trim($_SESSION["formulario"]["datos"]["telnot"]) != '') {
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetX(12);
            $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode('Teléfono Notificación: ') . $_SESSION["formulario"]["datos"]["telnot"], 0, 'J', 0);
            $pdf->Ln(1);
        }
        if (trim($_SESSION["formulario"]["datos"]["telnot2"]) != '') {
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetX(12);
            $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode('Teléfono Notificación: ') . $_SESSION["formulario"]["datos"]["telnot2"], 0, 'J', 0);
            $pdf->Ln(1);
        }
        if (trim($_SESSION["formulario"]["datos"]["celnot"]) != '') {
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetX(12);
            $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode('Teléfono Móvil: ') . $_SESSION["formulario"]["datos"]["celnot"], 0, 'J', 0);
            $pdf->Ln(1);
        }
        if (trim($_SESSION["formulario"]["datos"]["faxnot"]) != '') {
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetX(12);
            $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode('Fax Notificación: ') . $_SESSION["formulario"]["datos"]["faxnot"], 0, 'J', 0);
            $pdf->Ln(1);
        }
        if (trim($_SESSION["formulario"]["datos"]["emailnot"]) != '') {
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetX(12);
            $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode('Email Notificación: ') . $_SESSION["formulario"]["datos"]["emailnot"], 0, 'J', 0);
            $pdf->Ln(1);
        }
        $pdf->Ln();
    }
    $pdf->Ln();

    if (trim($txtFirmaElectronica) == '') {

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, 'Nombre: _______________________________________', 0, 'J', 0);
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode('Identificación: _______________________________________'), 0, 'J', 0);
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, 'Firma: _______________________________________', 0, 'J', 0);
        $pdf->Ln();
        $pdf->Ln();
    } else {
        //firmado electronico
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode($txtFirmaElectronica), 0, 'C', 0);
    }
    $pdf->Ln();
    $pdf->Ln();

    /*
    if (trim($txtFirmaElectronica) == '') {
        $tx = "Importante: Se debe reconocer el contenido del documento y la firma del solicitante ante notario o hacer presentación " .
                "personal ante el secretario de la Cámara de Comercio, ya que este documento debe inscribirse en el Registro " .
                "correspondiente.";
        $tx = \funcionesGenerales::utf8_decode($tx);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, $tx, 0, 'J', 0);
        $pdf->Ln();
    }
    */
    $tx = "Atención: Si está actuando por poder especial anexe su original o fotocopia autenticada. " .
            "En el poder deben especificarse claramente cuales son las facultades otorgadas al apoderado. " .
            "correspondiente.";


    $tx = \funcionesGenerales::utf8_decode($tx);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, $tx, 0, 'J', 0);
    $pdf->Ln();
    $pdf->Ln();

    $name = '../tmp/' . session_id() . '-MutacionDireccion.pdf';
    $pdf->Output($name, "F");
    return $name;
}

?>