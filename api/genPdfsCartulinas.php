<?php

function armarCartulinaValledupar($mysqli, $mat, $rec = '', $nombre = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/tcpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/examples/lang/eng.php');

    //
    if (!class_exists('PDFcart')) {

        class PDFcart extends TCPDF {

            //
            public function Header() {
                
            }

            //
            public function Footer() {
                
            }

        }

    }

    //
    if ($mat != '' && substr($mat,0,5) != 'NUEVA') {
        $expe = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $mat . "'");
    } else {
        $expe = array (
            'matricula' => 'PENDIENTE DE ASIGNAR',
            'nombre' => $nombre
        );
    }

    //
    $pdf = new PDFcart('L', PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Sistema Integrado de Información SII');
    $pdf->SetTitle('Cartulinas');
    $pdf->SetSubject('Cartulinas');
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->AddPage();
    $pdf->Image($_SESSION["generales"]["pathabsoluto"] . '/images/cartulina' . $_SESSION["generales"]["codigoempresa"] . '-' . date("Y") . '.jpg', 10, 10, 280, 210);

    $pdf->SetFont('courier', 'B', 10);
    $pdf->SetTextColor(0, 0, 0);

    $aleat = \funcionesGenerales::generarAleatorioAlfanumerico20();

    $pdf->SetXY(195, 39);
    $pdf->MultiCell(0, 20, $aleat, 0, 'L');

    $pdf->SetFont('courier', 'B', 20);
    $pdf->SetTextColor(0, 0, 0);

    $txt1 = 'Matrícula No. ' . $expe["matricula"];
    $txt2 = $expe["razonsocial"];

    $pdf->SetXY(45, 100);
    $pdf->MultiCell(180, 50, $txt1, 0, 'C');

    $pdf->SetXY(45, 113);
    $pdf->MultiCell(180, 50, $txt2, 0, 'C');

    //
    $name2 = $_SESSION["generales"]["codigoempresa"] . '-Cartulina-' . $aleat . '.pdf';
    $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name2;
    $pdf->Output($name1, "F");
    unset($pdf);

    //
    $arrCampos = array(
        'matricula',
        'fechahora',
        'recibo',
        'numerorecuperacion',
        'razonsocial',
        'usuario',
        'emailcontrol',
        'identificacioncontrol',
        'nombrecontrol',
        'ip'
    );
    $arrValores = array(
        "'" . $mat . "'",
        "'" . date("Ymd") . ' ' . date("H:i:s") . "'",
        "'" . $rec . "'",
        "'" . $aleat . "'",
        "'" . addslashes($expe["razonsocial"]) . "'",
        "'" . $_SESSION["generales"]["codigousuario"] . "'",
        "'" . $_SESSION["generales"]["emailcontrol"] . "'",
        "'" . $_SESSION["generales"]["identificacioncontrol"] . "'",
        "'" . addslashes($_SESSION["generales"]["nombrecontrol"]) . "'",
        "'" . \funcionesGenerales::localizarIP() . "'"
    );
    insertarRegistrosMysqliApi($mysqli, 'mreg_cartulinas_expedidas', $arrCampos, $arrValores);

    //
    return $name2;
}
