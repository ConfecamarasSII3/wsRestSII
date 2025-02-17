<?php

/**
 * 
 * @param type $numrec
 * @param type $numliq
 * @param type $txtFirmaElectronica
 * @param type $txtFirmaManuscrita
 * @return type
 */
function armarPdfMutacionGeneralTcpdf($dbx = null, $numrec = '', $numliq = 0, $txtFirmaElectronica = '', $txtFirmaManuscrita = '', $idClaseFirmante = '', $numIdFirmante = '', $nombreFirmante = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/tcpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/examples/lang/eng.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    // Define llamado a libreria fpdf
    if (!class_exists('PDFCerti')) {

        class PDFCerti extends TCPDF {

            function Header() {
                $i = 0;
                // $this->AddPage();
                // $this->SetMargins(20, 40, 10);
                if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg')) {
                    $this->Image($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg', 150, 10, 35, 28);
                }
                // $this->Image($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg', 150, 20, 45, 28);
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
                $this->Cell(100, 4, retornarNombreMunicipioMysqliApi(null, MUNICIPIO) . ', ' . \funcionesGenerales::mostrarFechaLetras(date("Ymd")), 0, 0, 'L');
                $this->Ln();
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Helvetica', 'B', 9);
                $this->SetXY(20, $i);
                $this->Cell(100, 4, 'Ref. ACTUALIZACIÓN DE DATOS (MUTACIONES).', 0, 0, 'L');
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Helvetica', 'B', 9);
                $this->SetXY(20, $i);
                $this->Cell(100, 4, ('Número de recuperación : ') . $_SESSION["formulario"]["numrec"], 0, 0, 'L');
                $this->Ln();
                $this->Ln();
                $i = $this->GetY();
                $this->SetY($i);
            }

        }

    }

    //echo "entro a pdf armar mutacion<br>";
    // Imprime encabezados
    $pdf = new PDFCerti(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Sistema Integrado de Información SII');
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

    // echo "imnprimio titulos<br>";
    //
    if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
        $tx = 'Yo, ' . $_SESSION["formulario"]["datos"]["nombre"] . ' identificado con ' .
                retornarNombreTablasSirepMysqliApi(null, '38', $_SESSION["formulario"]["datos"]["tipoidentificacion"]) .
                ' número ' . $_SESSION["formulario"]["datos"]["identificacion"] .
                ' matriculado el registro mercantil bajo el número ' . $_SESSION["formulario"]["datos"]["matricula"] .
                ' obrando en mi propio nombre, atentamente solicito se apliquen los siguientes cambios en el registro mercantil: ';
    }

    //
    if ($_SESSION["formulario"]["datos"]["organizacion"] == '02') {
        if (trim($_SESSION["formulario"]["datos"]["propietarios"][1]["nombrepropietario"]) == '') {
            $_SESSION["formulario"]["datos"]["propietarios"][1]["nombrepropietario"] = '_______________________________________________';
            $_SESSION["formulario"]["datos"]["propietarios"][1]["identificacionpropietario"] = '________________________';
            $txttipo = '_______';
        } else {
            $txttipo = retornarNombreTablasSirepMysqliApi(null, '38', $_SESSION["formulario"]["datos"]["propietarios"][1]["idtipoidentificacionpropietario"]);
        }

        $tx = 'Yo, ' . $_SESSION["formulario"]["datos"]["propietarios"][1]["nombrepropietario"] . ' identificado con ' .
                $txttipo .
                ' número ' . $_SESSION["formulario"]["datos"]["propietarios"][1]["identificacionpropietario"] .
                ' en calidad de propietario del establecimiento ' . $_SESSION["formulario"]["datos"]["nombre"] .
                ' matriculado bajo el número ' . $_SESSION["formulario"]["datos"]["matricula"] .
                ' atentamente solicito se apliquen los siguientes cambios en el registro mercantil:<br>';
    }

    //
    if (($_SESSION["formulario"]["datos"]["organizacion"] > '02') && ($_SESSION["formulario"]["datos"]["categoria"] == '1')) {
        if ($txtFirmaElectronica == "") {
            $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] = '_______________________________________________';
            $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] = '________________________';
            $txttipo = '_______';
        } else {
            $txttipo = retornarNombreTablasSirepMysqliApi(null, '38', $_SESSION["tramite"]["tipoidefirmante"]);
            $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] = $_SESSION["tramite"]["nombre1firmante"] . ' ' . $_SESSION["tramite"]["nombre2firmante"] . ' ' . $_SESSION["tramite"]["apellido1firmante"] . ' ' . $_SESSION["tramite"]["apellido2firmante"];
            $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] = $_SESSION["tramite"]["identificacionfirmante"];
        }
        $tx = 'Yo, ' . $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] . ' identificado con ' .
                $txttipo .
                ' número ' . $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] .
                ' en calidad de representante legal de la persona jurídica ' . $_SESSION["formulario"]["datos"]["nombre"] .
                ' matriculada/inscrita bajo el número ' . $_SESSION["formulario"]["datos"]["matricula"] .
                ' atentamente solicito se apliquen los siguientes cambios al registro :<br>';
    }

    //
    if (($_SESSION["formulario"]["datos"]["organizacion"] > '02') && ($_SESSION["formulario"]["datos"]["categoria"] == '2')) {
        if
        ((!isset($_SESSION["tramite"]["identificacionfirmante"]))) {
            $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] = '_______________________________________________';
            $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] = '________________________';
            $txttipo = '_______';
        } else {
            $txttipo = retornarNombreTablasSirepMysqliApi(null, '38', $_SESSION["tramite"]["tipoidefirmante"]);
            $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] = $_SESSION["tramite"]["nombre1firmante"] . ' ' . $_SESSION["tramite"]["nombre2firmante"] . ' ' . $_SESSION["tramite"]["apellido1firmante"] . ' ' . $_SESSION["tramite"]["apellido2firmante"];
            $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] = $_SESSION["tramite"]["identificacionfirmante"];
        }
        $tx = 'Yo, ' . $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] . ' identificado con ' .
                $txttipo .
                ' número ' . $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] .
                ' en calidad de representante legal de la persona jurídica ' . $_SESSION["formulario"]["datos"]["nombre"] .
                ' matriculada bajo el número ' . $_SESSION["formulario"]["datos"]["matricula"] .
                ' atentamente solicito se apliquen los siguientes cambios al registro :<br>';
    }

    //
    if (($_SESSION["formulario"]["datos"]["organizacion"] > '02') && ($_SESSION["formulario"]["datos"]["categoria"] == '3')) {
        if (!isset($_SESSION["formulario"]["datos"]["nombreadministrador"]) || $_SESSION["formulario"]["datos"]["nombreadministrador"] == '') {
            if (isset($_SESSION["tramite"]["nombrefirmante"]) && $_SESSION["tramite"]["nombrefirmante"] != '') {
                $_SESSION["formulario"]["datos"]["nombreadministrador"] = $_SESSION["tramite"]["nombrefirmante"];
                $_SESSION["formulario"]["datos"]["identificacionadministrador"] = $_SESSION["tramite"]["identificacionfirmante"];
                $txttipo = '';
                $tcargo = 'en calidad de administrador de la agencia';
            } else {
                $_SESSION["formulario"]["datos"]["nombreadministrador"] = '___________________________________________';
                $_SESSION["formulario"]["datos"]["identificacionadministrador"] = '_______________';
                $txttipo = '_______';
                $tcargo = '________________________________';
            }
        } else {
            $txttipo = retornarNombreTablasSirepMysqliApi(null, '38', $_SESSION["formulario"]["datos"]["idtipoidentificacionadministrador"]);
            $tcargo = 'en calidad de administrador de la agencia';
        }
        $tx = 'Yo, ' . $_SESSION["formulario"]["datos"]["nombreadministrador"] . ' identificado documento no. ' .
                $_SESSION["formulario"]["datos"]["identificacionadministrador"] . ', ' .
                'atentamente solicito se apliquen los siguientes cambios al registro de la agencia ' . $_SESSION["formulario"]["datos"]["nombre"] . ' ';
        'matriculada bajo el número ' . $_SESSION["formulario"]["datos"]["matricula"] .
                '.<br><br>';
    }


    $tx = ($tx);

    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(20);
    $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
    // $pdf->MultiCell(190, 4, $tx, 0, 'J', 0);
    // echo "imprimio encabezados<br>";

    $iCambios = 0;

    // Cambio de nombre
    if ($_SESSION["formulario"]["datosanteriores"]["nombre"] != $_SESSION["formulario"]["datos"]["nombre"]) {
        $iCambios++;
        $tx = $iCambios . '.) Se solicita el cambio del nombre de <strong>' . $_SESSION["formulario"]["datosanteriores"]["nombre"] . '</strong> por ';
        $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["nombre"] . '</strong>';
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
        // echo "imprimio cambio de nombre<br>";
    }

    // Direccion comercial    
    if ($_SESSION["formulario"]["datosanteriores"]["dircom"] != $_SESSION["formulario"]["datos"]["dircom"]) {
        $iCambios++;
        if (trim($_SESSION["formulario"]["datosanteriores"]["dircom"]) == '') {
            $tx = $iCambios . '.) Se solicita registrar como Dirección Comercial  : <strong>' . $_SESSION["formulario"]["datos"]["dircom"] . '</strong>.';
        } else {
            $tx = $iCambios . '.) Se solicita el cambio de Dirección Comercial de <strong>' . $_SESSION["formulario"]["datosanteriores"]["dircom"] . '</strong> por ';
            $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["dircom"] . '</strong>';
        }
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
        // echo "imprimio cambio de dircom<br>";
    }

    // Telefono comercial 1
    if ($_SESSION["formulario"]["datosanteriores"]["telcom1"] != $_SESSION["formulario"]["datos"]["telcom1"]) {
        $iCambios++;
        if (trim($_SESSION["formulario"]["datosanteriores"]["telcom1"]) == '') {
            $tx = $iCambios . '.) Se solicita registrar como Teléfono Comercial No. 1 el número <strong>' . $_SESSION["formulario"]["datos"]["telcom1"] . '</strong>.';
        } else {
            $tx = $iCambios . '.) Se solicita el cambio del Teléfono Comercial No. 1 de <strong>' . $_SESSION["formulario"]["datosanteriores"]["telcom1"] . '</strong> por ';
            $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["telcom1"] . '</strong>';
        }
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
        // echo "imprimio cambio de telcom1<br>";
    }

    // Telefono comercial 2
    if ($_SESSION["formulario"]["datosanteriores"]["telcom2"] != $_SESSION["formulario"]["datos"]["telcom2"]) {
        $iCambios++;
        if (trim($_SESSION["formulario"]["datosanteriores"]["telcom2"]) == '') {
            $tx = $iCambios . '.) Se solicita registrar como Teléfono Comercial No. 2 el número <strong>' . $_SESSION["formulario"]["datos"]["telcom2"] . '</strong>.';
        } else {
            if (trim($_SESSION["formulario"]["datos"]["telcom2"]) != '') {
                $tx = $iCambios . '.) Se solicita el cambio del Teléfono Comercial No. 2 de <strong>' . $_SESSION["formulario"]["datosanteriores"]["telcom2"] . '</strong> por ';
                $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["telcom2"] . '</strong>';
            } else {
                $tx = $iCambios . '.) Se solicita suprimir el Teléfono Comercial No. 2 <strong>' . $_SESSION["formulario"]["datosanteriores"]["telcom2"] . '</strong>';
            }
        }
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
        // echo "imprimio cambio de telcom2<br>";
    }

    // Telefono comercial 3
    if ($_SESSION["formulario"]["datosanteriores"]["celcom"] != $_SESSION["formulario"]["datos"]["celcom"]) {
        $iCambios++;
        if (trim($_SESSION["formulario"]["datosanteriores"]["celcom"]) == '') {
            $tx = $iCambios . '.) Se solicita registrar como Teléfono Comercial No. 3 el número <strong>' . $_SESSION["formulario"]["datos"]["celcom"] . '</strong>.';
        } else {
            if (trim($_SESSION["formulario"]["datos"]["celcom"]) != '') {
                $tx = $iCambios . '.) Se solicita el cambio del Teléfono Comercial No. 3 de <strong>' . $_SESSION["formulario"]["datosanteriores"]["celcom"] . '</strong> por ';
                $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["celcom"] . '</strong>';
            } else {
                $tx = $iCambios . '.) Se solicita suprimir el Teléfono Comercial No. 3 <strong>' . $_SESSION["formulario"]["datosanteriores"]["celcom"] . '</strong>';
            }
        }
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
        // echo "imprimio cambio de celcom<br>";
    }

    // Barrio comercial
    if (ltrim($_SESSION["formulario"]["datosanteriores"]["barriocom"], "0") != ltrim($_SESSION["formulario"]["datos"]["barriocom"], "0")) {
        $iCambios++;
        if (ltrim(trim($_SESSION["formulario"]["datosanteriores"]["barriocom"]), "0") == '') {
            $tx = $iCambios . '.) Se solicita registrar como barrio de ubicación comercial : <strong>' . $_SESSION["formulario"]["datos"]["barriocom"] . '</strong>.';
        } else {
            if (ltrim(trim($_SESSION["formulario"]["datos"]["barriocom"]), "0") != '') {
                $tx = $iCambios . '.) Se solicita el cambio del barrio de ubicación comercial de <strong>' . $_SESSION["formulario"]["datosanteriores"]["barriocom"] . '</strong> por ';
                $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["barriocom"] . '</strong>';
            } else {
                $tx = $iCambios . '.) Se solicita suprimir el barrio de ubicación comercial <strong>' . $_SESSION["formulario"]["datosanteriores"]["barriocom"] . '</strong>';
            }
        }
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
        // echo "imprimio cambio de barriocom<br>";
    }

    // Zona rural o urbana
    if (date("Y") < '2024') {
        if ($_SESSION["formulario"]["datosanteriores"]["codigozonacom"] != $_SESSION["formulario"]["datos"]["codigozonacom"]) {
            $tcodigozonacomant = '';
            $tcodigozonacom = '';
            if ($_SESSION["formulario"]["datosanteriores"]["codigozonacom"] == 'R') {
                $tcodigozonacomant = 'RURAL';
            }
            if ($_SESSION["formulario"]["datosanteriores"]["codigozonacom"] == 'U') {
                $tcodigozonacomant = 'URBANA';
            }
            if ($_SESSION["formulario"]["datos"]["codigozonacom"] == 'R') {
                $tcodigozonacom = 'RURAL';
            }
            if ($_SESSION["formulario"]["datos"]["codigozonacom"] == 'U') {
                $tcodigozonacom = 'URBANA';
            }
            $iCambios++;
            if (trim($_SESSION["formulario"]["datosanteriores"]["codigozonacom"]) == '') {
                $tx = $iCambios . '.) Se solicita incluir como zona de ubicación  : <strong>' . $tcodigozonacom . '</strong>.';
            } else {
                if (trim($_SESSION["formulario"]["datos"]["codigozonacom"]) != '') {
                    $tx = $iCambios . '.) Se solicita el cambio de zona de ubicación de <strong>' . $tcodigozonacomant . '</strong> por ';
                    $tx .= '<strong>' . $tcodigozonacom . '</strong>';
                } else {
                    $tx = $iCambios . '.) Se solicita suprimir la zona de ubicación comercial <strong>' . $_SESSION["formulario"]["datosanteriores"]["codigozonacom"] . '</strong>';
                }
            }
            $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
            // echo "imprimio cambio de barriocom<br>";
        }
    }

    // Municipio comercial
    if ($_SESSION["formulario"]["datosanteriores"]["muncom"] != $_SESSION["formulario"]["datos"]["muncom"]) {
        $iCambios++;
        $tx = $iCambios . '.) Se solicita el cambio del Municipio comercial de <strong>' . $_SESSION["formulario"]["datosanteriores"]["muncom"] . '</strong> por ';
        $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["muncom"] . '</strong>';
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
        // echo "imprimio cambio de muncom<br>";
    }

    // Email comercial
    if ($_SESSION["formulario"]["datosanteriores"]["emailcom"] != $_SESSION["formulario"]["datos"]["emailcom"]) {
        $iCambios++;
        if (trim($_SESSION["formulario"]["datosanteriores"]["emailcom"]) == '') {
            $tx = $iCambios . '.) Se solicita registrar como correo electrónico comercial No. 1 : <strong>' . $_SESSION["formulario"]["datos"]["emailcom"] . '</strong>.';
        } else {
            if (trim($_SESSION["formulario"]["datos"]["emailcom"]) != '') {
                $tx = $iCambios . '.) Se solicita el cambio del correo electrónico comercial No. 1 de <strong>' . $_SESSION["formulario"]["datosanteriores"]["emailcom"] . '</strong> por ';
                $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["emailcom"] . '</strong>';
            } else {
                $tx = $iCambios . '.) Se solicita suprimir el correo electrónico comercial No. 1 <strong>' . $_SESSION["formulario"]["datosanteriores"]["emailcom"] . '</strong>';
            }
        }
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
        // echo "imprimio cambio de emailcom<br>";
    }

    // Email comercial No 2
    if ($_SESSION["formulario"]["datosanteriores"]["emailcom2"] != $_SESSION["formulario"]["datos"]["emailcom2"]) {
        $iCambios++;
        if (trim($_SESSION["formulario"]["datosanteriores"]["emailcom2"]) == '') {
            $tx = $iCambios . '.) Se solicita registrar como correo electrónico comercial No. 2 : <strong>' . $_SESSION["formulario"]["datos"]["emailcom2"] . '</strong>.';
        } else {
            if (trim($_SESSION["formulario"]["datos"]["emailcom2"]) != '') {
                $tx = $iCambios . '.) Se solicita el cambio del correo electrónico comercial No. 2 de <strong>' . $_SESSION["formulario"]["datosanteriores"]["emailcom2"] . '</strong> por ';
                $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["emailcom2"] . '</strong>';
            } else {
                $tx = $iCambios . '.) Se solicita suprimir el correo electrónico comercial No. 2 <strong>' . $_SESSION["formulario"]["datosanteriores"]["emailcom2"] . '</strong>';
            }
        }
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
    }

    // Email comercial No 3
    if ($_SESSION["formulario"]["datosanteriores"]["emailcom3"] != $_SESSION["formulario"]["datos"]["emailcom3"]) {
        $iCambios++;
        if (trim($_SESSION["formulario"]["datosanteriores"]["emailcom3"]) == '') {
            $tx = $iCambios . '.) Se solicita registrar como correo electrónico comercial No. 3 : <strong>' . $_SESSION["formulario"]["datos"]["emailcom3"] . '</strong>.';
        } else {
            if (trim($_SESSION["formulario"]["datos"]["emailcom3"]) != '') {
                $tx = $iCambios . '.) Se solicita el cambio del correo electrónico comercial No. 3 de <strong>' . $_SESSION["formulario"]["datosanteriores"]["emailcom3"] . '</strong> por ';
                $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["emailcom3"] . '</strong>';
            } else {
                $tx = $iCambios . '.) Se solicita suprimir el correo electrónico comercial No. 3 <strong>' . $_SESSION["formulario"]["datosanteriores"]["emailcom3"] . '</strong>';
            }
        }
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
    }

    // Ubicación
    if (date("Y") < '2024') {
        if ($_SESSION["formulario"]["datosanteriores"]["ctrubi"] != $_SESSION["formulario"]["datos"]["ctrubi"]) {
            $tubiant = retornarNombreTablaBasicaMysqliApi(null, 'mreg_ubicacion', $_SESSION["formulario"]["datosanteriores"]["ctrubi"]);
            $tubi = retornarNombreTablaBasicaMysqliApi(null, 'mreg_ubicacion', $_SESSION["formulario"]["datos"]["ctrubi"]);
            $iCambios++;
            if (trim($_SESSION["formulario"]["datosanteriores"]["ctrubi"]) == '' || $_SESSION["formulario"]["datosanteriores"]["ctrubi"] == '0') {
                $tx = $iCambios . '.) Se solicita incluir como ubicación  : <strong>' . $tubi . '</strong>.';
            } else {
                if ($_SESSION["formulario"]["datos"]["ctrubi"] != '') {
                    $tx = $iCambios . '.) Se solicita el cambio de ubicación de <strong>' . $tubiant . '</strong> por ';
                    $tx .= '<strong>' . $tubi . '</strong>';
                } else {
                    $tx = $iCambios . '.) Se solicita suprimir la ubicación comercial <strong>' . $tubiant . '</strong>';
                }
            }
            $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
            // echo "imprimio cambio de barriocom<br>";
        }
    }

    // Personal 
    if (!isset($_SESSION["formulario"]["datos"]["personal"])) {
        $_SESSION["formulario"]["datos"]["personal"] = 0;
    }
    if (!isset($_SESSION["formulario"]["datosanteriores"]["personal"])) {
        $_SESSION["formulario"]["datosanteriores"]["personal"] = 0;
    }
    if ($_SESSION["formulario"]["datosanteriores"]["personal"] != $_SESSION["formulario"]["datos"]["personal"]) {
        $iCambios++;
        $tx = $iCambios . '.) Se solicita el cambio de número de personas contratadas de <strong>' . $_SESSION["formulario"]["datosanteriores"]["personal"] . '</strong> por ';
        $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["personal"] . '</strong>';
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
        // echo "imprimio cambio de barriocom<br>";
    }

    // Cantidad mujeres
    if (!isset($_SESSION["formulario"]["datos"]["cantidadmujeres"])) {
        $_SESSION["formulario"]["datos"]["cantidadmujeres"] = 0;
    }
    if (!isset($_SESSION["formulario"]["datosanteriores"]["cantidadmujeres"])) {
        $_SESSION["formulario"]["datosanteriores"]["cantidadmujeres"] = 0;
    }
    if ($_SESSION["formulario"]["datosanteriores"]["cantidadmujeres"] != $_SESSION["formulario"]["datos"]["cantidadmujeres"]) {
        $iCambios++;
        $tx = $iCambios . '.) Se solicita el cambio de número de mujeres contratadas de <strong>' . $_SESSION["formulario"]["datosanteriores"]["cantidadmujeres"] . '</strong> por ';
        $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["cantidadmujeres"] . '</strong>';
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
        // echo "imprimio cambio de barriocom<br>";
    }

    // Cantidad mujeres en cargos directivos
    if (!isset($_SESSION["formulario"]["datos"]["cantidadmujerescargosdirectivos"])) {
        $_SESSION["formulario"]["datos"]["cantidadmujerescargosdirectivos"] = 0;
    }
    if (!isset($_SESSION["formulario"]["datosanteriores"]["cantidadmujerescargosdirectivos"])) {
        $_SESSION["formulario"]["datosanteriores"]["cantidadmujerescargosdirectivos"] = 0;
    }
    if ($_SESSION["formulario"]["datosanteriores"]["cantidadmujerescargosdirectivos"] != $_SESSION["formulario"]["datos"]["cantidadmujerescargosdirectivos"]) {
        $iCambios++;
        $tx = $iCambios . '.) Se solicita el cambio de número de mujeres en cargos directivos de <strong>' . $_SESSION["formulario"]["datosanteriores"]["cantidadmujerescargosdirectivos"] . '</strong> por ';
        $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["cantidadmujerescargosdirectivos"] . '</strong>';
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
        // echo "imprimio cambio de barriocom<br>";
    }

    if ($_SESSION["formulario"]["datos"]["organizacion"] != '02' && $_SESSION["formulario"]["datos"]["categoria"] != '3') {
        // Direccion notificacion    
        if ($_SESSION["formulario"]["datosanteriores"]["dirnot"] != $_SESSION["formulario"]["datos"]["dirnot"]) {
            $iCambios++;
            if (trim($_SESSION["formulario"]["datosanteriores"]["dirnot"]) == '') {
                $tx = $iCambios . '.) Se solicita registrar como Dirección de Notificación  : <strong>' . $_SESSION["formulario"]["datos"]["dirnot"] . '</strong>.';
            } else {
                $tx = $iCambios . '.) Se solicita el cambio de Dirección de Notificación de <strong>' . $_SESSION["formulario"]["datosanteriores"]["dirnot"] . '</strong> por ';
                $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["dirnot"] . '</strong>';
            }
            $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
            // echo "imprimio cambio de dirnot<br>";
        }

        // Telefono notificacion 1
        if (date("Y") < '2024') {
            if ($_SESSION["formulario"]["datosanteriores"]["telnot"] != $_SESSION["formulario"]["datos"]["telnot"]) {
                $iCambios++;
                if (trim($_SESSION["formulario"]["datosanteriores"]["telnot"]) == '') {
                    $tx = $iCambios . '.) Se solicita registrar como Teléfono de Notificación No. 1 el número <strong>' . $_SESSION["formulario"]["datos"]["telnot"] . '</strong>.';
                } else {
                    if ($_SESSION["formulario"]["datos"]["telnot"] != '') {
                        $tx = $iCambios . '.) Se solicita el cambio del Teléfono de Notificación  No. 1 de <strong>' . $_SESSION["formulario"]["datosanteriores"]["telnot"] . '</strong> por ';
                        $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["telnot"] . '</strong>';
                    } else {
                        $tx = $iCambios . '.) Se solicita suprimir el Teléfono de Notificación  No. 1 <strong>' . $_SESSION["formulario"]["datosanteriores"]["telnot"] . '</strong>';
                    }
                }
                $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
                // echo "imprimio cambio de telnot1<br>";
            }

            // Telefono notificacion 2
            if ($_SESSION["formulario"]["datosanteriores"]["telnot2"] != $_SESSION["formulario"]["datos"]["telnot2"]) {
                $iCambios++;
                if (trim($_SESSION["formulario"]["datosanteriores"]["telnot2"]) == '') {
                    $tx = $iCambios . '.) Se solicita registrar como Teléfono de Notificación No. 2 el número <strong>' . $_SESSION["formulario"]["datos"]["telnot2"] . '</strong>.';
                } else {
                    if ($_SESSION["formulario"]["datos"]["telnot2"] != '') {
                        $tx = $iCambios . '.) Se solicita el cambio del Teléfono de Notificación No. 2 de <strong>' . $_SESSION["formulario"]["datosanteriores"]["telnot2"] . '</strong> por ';
                        $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["telnot2"] . '</strong>';
                    } else {
                        $tx = $iCambios . '.) Se solicita suprimir el Teléfono de Notificación  No. 2 <strong>' . $_SESSION["formulario"]["datosanteriores"]["telnot2"] . '</strong>';
                    }
                }
                $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
                // echo "imprimio cambio de telnot2<br>";
            }

            // Telefono notificación 3
            if ($_SESSION["formulario"]["datosanteriores"]["celnot"] != $_SESSION["formulario"]["datos"]["celnot"]) {
                $iCambios++;
                if (trim($_SESSION["formulario"]["datosanteriores"]["celnot"]) == '') {
                    $tx = $iCambios . '.) Se solicita registrar como Teléfono de Notificación No. 3 el número <strong>' . $_SESSION["formulario"]["datos"]["celnot"] . '</strong>.';
                } else {
                    if ($_SESSION["formulario"]["datos"]["celnot"] != '') {
                        $tx = $iCambios . '.) Se solicita el cambio del Teléfono de Notificación No. 3 de <strong>' . $_SESSION["formulario"]["datosanteriores"]["celnot"] . '</strong> por ';
                        $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["celnot"] . '</strong>';
                    } else {
                        $tx = $iCambios . '.) Se solicita suprimir el Teléfono de Notificación  No. 3 <strong>' . $_SESSION["formulario"]["datosanteriores"]["celnot"] . '</strong>';
                    }
                }
                $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
                // echo "imprimio cambio de celnot<br>";
            }
        }

        // Barrio de notifación
        if (ltrim($_SESSION["formulario"]["datosanteriores"]["barrionot"], "0") != ltrim($_SESSION["formulario"]["datos"]["barrionot"], "0")) {
            $iCambios++;
            if (ltrim(trim($_SESSION["formulario"]["datosanteriores"]["barrionot"]), "0") == '') {
                $tx = $iCambios . '.) Se solicita registrar como barrio de ubicación para Notificación  : <strong>' . $_SESSION["formulario"]["datos"]["barrionot"] . '</strong>.';
            } else {
                if (ltrim(trim($_SESSION["formulario"]["datos"]["barrionot"]), "0") != '') {
                    $tx = $iCambios . '.) Se solicita el cambio del barrio de ubicación para Notificación de <strong>' . $_SESSION["formulario"]["datosanteriores"]["barrionot"] . '</strong> por ';
                    $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["barrionot"] . '</strong>';
                } else {
                    $tx = $iCambios . '.) Se solicita suprimir el barrio de ubicación de Notificación <strong>' . $_SESSION["formulario"]["datosanteriores"]["barrionot"] . '</strong>';
                }
            }
            $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
            // echo "imprimio cambio de barrionot<br>";
        }

        // Municipio de notificación
        if ($_SESSION["formulario"]["datosanteriores"]["munnot"] != $_SESSION["formulario"]["datos"]["munnot"]) {
            $iCambios++;
            $tx = $iCambios . '.) Se solicita el cambio del Municipio de Notificación de <strong>' . $_SESSION["formulario"]["datosanteriores"]["munnot"] . '</strong> por ';
            $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["munnot"] . '</strong>';
            $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
            // echo "imprimio cambio de munnot<br>";
        }

        // Email notificación
        if ($_SESSION["formulario"]["datosanteriores"]["emailnot"] != $_SESSION["formulario"]["datos"]["emailnot"]) {
            $iCambios++;
            if (trim($_SESSION["formulario"]["datosanteriores"]["emailcom"]) == '') {
                $tx = $iCambios . '.) Se solicita registrar como correo electrónico de Notificación : <strong>' . $_SESSION["formulario"]["datos"]["emailnot"] . '</strong>.';
            } else {
                if (trim($_SESSION["formulario"]["datos"]["emailcom"]) != '') {
                    $tx = $iCambios . '.) Se solicita el cambio del correo electrónico de Notificación de <strong>' . $_SESSION["formulario"]["datosanteriores"]["emailnot"] . '</strong> por ';
                    $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["emailnot"] . '</strong>';
                } else {
                    $tx = $iCambios . '.) Se solicita suprimir el  correo electrónico de Notificación <strong>' . $_SESSION["formulario"]["datosanteriores"]["emailnot"] . '</strong>';
                }
            }
            $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
            // echo "imprimio cambio de emailnot<br>";
        }

        // Ubicación de la sede administrativa
        if (date("Y") < '2024') {
            if ($_SESSION["formulario"]["datosanteriores"]["tiposedeadm"] != $_SESSION["formulario"]["datos"]["tiposedeadm"]) {
                $tsant = '';
                $ts = '';
                if ($_SESSION["formulario"]["datosanteriores"]["tiposedeadm"] == '1') {
                    $tsant = 'PROPIA';
                }
                if ($_SESSION["formulario"]["datosanteriores"]["tiposedeadm"] == '2') {
                    $tsant = 'ARRIENDO';
                }
                if ($_SESSION["formulario"]["datosanteriores"]["tiposedeadm"] == '3') {
                    $tsant = 'COMODATO';
                }
                if ($_SESSION["formulario"]["datosanteriores"]["tiposedeadm"] == '4') {
                    $tsant = 'PRESTAMO';
                }
                if ($_SESSION["formulario"]["datos"]["tiposedeadm"] == '1') {
                    $ts = 'PROPIA';
                }
                if ($_SESSION["formulario"]["datos"]["tiposedeadm"] == '2') {
                    $ts = 'ARRIENDO';
                }
                if ($_SESSION["formulario"]["datos"]["tiposedeadm"] == '3') {
                    $ts = 'COMODATO';
                }
                if ($_SESSION["formulario"]["datos"]["tiposedeadm"] == '4') {
                    $ts = 'PRESTAMO';
                }
                $iCambios++;
                if (trim($_SESSION["formulario"]["datosanteriores"]["tiposedeadm"]) == '' || $_SESSION["formulario"]["datosanteriores"]["tiposedeadm"] == '0') {
                    $tx = $iCambios . '.) Se solicita incluir como sede administrativa  : <strong>' . $ts . '</strong>.';
                } else {
                    if (trim($_SESSION["formulario"]["datos"]["tiposedeadm"]) != '') {
                        $tx = $iCambios . '.) Se solicita el cambio de sede administrativa de <strong>' . $tsant . '</strong> por ';
                        $tx .= '<strong>' . $ts . '</strong>';
                    } else {
                        $tx = $iCambios . '.) Se solicita suprimir la sede administrativa <strong>' . $tsant . '</strong>';
                    }
                }
                $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
            }
        }
    }

    // Numero predial
    if ($_SESSION["formulario"]["datosanteriores"]["numpredial"] != $_SESSION["formulario"]["datos"]["numpredial"]) {
        $iCambios++;
        if (trim($_SESSION["formulario"]["datosanteriores"]["numpredial"]) == '') {
            $tx = $iCambios . '.) Se solicita registrar como número del predial o cédula catastral : <strong>' . $_SESSION["formulario"]["datos"]["numpredial"] . '</strong>.';
        } else {
            if (trim($_SESSION["formulario"]["datos"]["numpredial"]) != '') {
                $tx = $iCambios . '.) Se solicita el cambio del número del predial o cédula catastral de <strong>' . $_SESSION["formulario"]["datosanteriores"]["numpredial"] . '</strong> por ';
                $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["numpredial"] . '</strong>';
            } else {
                $tx = $iCambios . '.) Se solicita suprimir el número del predial o cédula catastral <strong>' . $_SESSION["formulario"]["datosanteriores"]["numpredial"] . '</strong>';
            }
        }
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
    }


    if ($_SESSION["formulario"]["datosanteriores"]["ciius"][1] != $_SESSION["formulario"]["datos"]["ciius"][1]) {
        $iCambios++;
        if (trim($_SESSION["formulario"]["datosanteriores"]["ciius"][1]) == '') {
            $tx = $iCambios . '.) Se solicita registrar como código de actividad principal : <strong>' . $_SESSION["formulario"]["datos"]["ciius"][1] . '</strong>.';
        } else {
            $tx = $iCambios . '.) Se solicita el cambio del código de actividad principal de <strong>' . $_SESSION["formulario"]["datosanteriores"]["ciius"][1] . '</strong> por ';
            $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["ciius"][1] . '</strong>';
        }
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
        // echo "imprimio cambio de ciiu1<br>";
    }

    if ($_SESSION["formulario"]["datosanteriores"]["ciius"][2] != $_SESSION["formulario"]["datos"]["ciius"][2]) {
        $iCambios++;
        if (trim($_SESSION["formulario"]["datosanteriores"]["ciius"][2]) == '') {
            $tx = $iCambios . '.) Se solicita registrar como código de actividad secundaria : <strong>' . $_SESSION["formulario"]["datos"]["ciius"][2] . '</strong>.';
        } else {
            if (trim($_SESSION["formulario"]["datos"]["ciius"][2]) == '') {
                $tx = $iCambios . '.) Se solicita suprimir la actividad secundaria : <strong>' . $_SESSION["formulario"]["datosanteriores"]["ciius"][2] . '</strong>.';
            } else {
                $tx = $iCambios . '.) Se solicita el cambio del código de actividad secundaria de <strong>' . $_SESSION["formulario"]["datosanteriores"]["ciius"][2] . '</strong> por ';
                $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["ciius"][2] . '</strong>';
            }
        }
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
        // echo "imprimio cambio de ciiu2<br>";
    }

    if ($_SESSION["formulario"]["datosanteriores"]["ciius"][3] != $_SESSION["formulario"]["datos"]["ciius"][3]) {
        $iCambios++;
        if (trim($_SESSION["formulario"]["datosanteriores"]["ciius"][3]) == '') {
            $tx = $iCambios . '.) Se solicita registrar como código de actividad adicional : <strong>' . $_SESSION["formulario"]["datos"]["ciius"][3] . '</strong>.';
        } else {
            if (trim($_SESSION["formulario"]["datos"]["ciius"][3]) == '') {
                $tx = $iCambios . '.) Se solicita suprimir la actividad adicional : <strong>' . $_SESSION["formulario"]["datosanteriores"]["ciius"][3] . '</strong>.';
            } else {
                $tx = $iCambios . '.) Se solicita el cambio del código de actividad adicional de <strong>' . $_SESSION["formulario"]["datosanteriores"]["ciius"][3] . '</strong> por ';
                $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["ciius"][3] . '</strong>';
            }
        }
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
        // echo "imprimio cambio de ciiu3<br>";
    }

    if ($_SESSION["formulario"]["datosanteriores"]["ciius"][4] != $_SESSION["formulario"]["datos"]["ciius"][4]) {
        $iCambios++;
        if (trim($_SESSION["formulario"]["datosanteriores"]["ciius"][4]) == '') {
            $tx = $iCambios . '.) Se solicita registrar como código de actividad adicional : <strong>' . $_SESSION["formulario"]["datos"]["ciius"][4] . '</strong>.';
        } else {
            if (trim($_SESSION["formulario"]["datos"]["ciius"][4]) == '') {
                $tx = $iCambios . '.) Se solicita suprimir la actividad adicional : <strong>' . $_SESSION["formulario"]["datosanteriores"]["ciius"][4] . '</strong>.';
            } else {
                $tx = $iCambios . '.) Se solicita el cambio del código de actividad adicional de <strong>' . $_SESSION["formulario"]["datosanteriores"]["ciius"][4] . '</strong> por ';
                $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["ciius"][4] . '</strong>';
            }
        }
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
        // echo "imprimio cambio de ciiu4<br>";
    }

    if ($_SESSION["formulario"]["datosanteriores"]["organizacion"] == '01' || ($_SESSION["formulario"]["datosanteriores"]["organizacion"] > '02' && $_SESSION["formulario"]["datosanteriores"]["categoria"] == '1')) {
        if ($_SESSION["formulario"]["datosanteriores"]["feciniact1"] != $_SESSION["formulario"]["datos"]["feciniact1"]) {
            $iCambios++;
            if (trim($_SESSION["formulario"]["datosanteriores"]["feciniact1"]) == '') {
                $tx = $iCambios . '.) Se solicita registrar como fecha de inicio de la actividad principal : <strong>' . $_SESSION["formulario"]["datos"]["feciniact1"] . '</strong>.';
            } else {
                if (trim($_SESSION["formulario"]["datos"]["feciniact1"]) != '') {
                    $tx = $iCambios . '.) Se solicita el cambio del fecha de inicio de actividad principal de <strong>' . $_SESSION["formulario"]["datosanteriores"]["feciniact1"] . '</strong> por ';
                    $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["feciniact1"] . '</strong>';
                } else {
                    $tx = $iCambios . '.) Se solicita suprimir la fecha de inicio de actividad principal <strong>' . $_SESSION["formulario"]["datosanteriores"]["feciniact1"] . '</strong>.';
                }
            }
            $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
            // echo "imprimio cambio de feciniact1<br>";
        }

        if ($_SESSION["formulario"]["datosanteriores"]["feciniact2"] != $_SESSION["formulario"]["datos"]["feciniact2"]) {
            $iCambios++;
            if (trim($_SESSION["formulario"]["datosanteriores"]["feciniact2"]) == '') {
                $tx = $iCambios . '.) Se solicita registrar como fecha de inicio de la actividad secundaria : <strong>' . $_SESSION["formulario"]["datos"]["feciniact2"] . '</strong>.';
            } else {
                if (trim($_SESSION["formulario"]["datos"]["feciniact2"]) != '') {
                    $tx = $iCambios . '.) Se solicita el cambio del fecha de inicio de actividad secundaria de <strong>' . $_SESSION["formulario"]["datosanteriores"]["feciniact2"] . '</strong> por ';
                    $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["feciniact2"] . '</strong>';
                } else {
                    $tx = $iCambios . '.) Se solicita suprimir la fecha de inicio de actividad secundaria <strong>' . $_SESSION["formulario"]["datosanteriores"]["feciniact2"] . '</strong>.';
                }
            }
            $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
            // echo "imprimio cambio de feciniact2<br>";
        }
    }

    if ($_SESSION["formulario"]["datosanteriores"]["desactiv"] != $_SESSION["formulario"]["datos"]["desactiv"]) {
        $iCambios++;
        if (trim($_SESSION["formulario"]["datosanteriores"]["desactiv"]) == '') {
            $tx = $iCambios . '.) Se solicita registrar como descripción de la actividad  : <strong>' . $_SESSION["formulario"]["datos"]["desactiv"] . '</strong>.';
        } else {
            if (trim($_SESSION["formulario"]["datos"]["desactiv"]) != '') {
                $tx = $iCambios . '.) Se solicita el cambio de la descripción de la actividad de <strong>' . $_SESSION["formulario"]["datosanteriores"]["desactiv"] . '</strong> por ';
                $tx .= '<strong>' . $_SESSION["formulario"]["datos"]["desactiv"] . '</strong>';
            } else {
                $tx = $iCambios . '.) Se solicita suprimir la descripción de la actividad <strong>' . $_SESSION["formulario"]["datosanteriores"]["desactiv"] . '</strong>.';
            }
        }
        $pdf->writeHTML($tx . '<br>', true, false, true, false, 'J');
        // echo "imprimio cambio de desactiv<br>";
    }

    // echo "imnprimio cambios<br>";

    $pdf->Ln();
    $pdf->Ln();

    //
    // 2019-03-14: JINT: Se incluye por solicitud de la CC de Barrancabermeja.
    $tx = 'Autorizo a la Cámara de Comercio para que envíe alertas relacionadas con los registros públicos, a los numeros celulares aquí ';
    $tx .= 'relacionados, sin cargo alguno a los empresarios.<br><br>';
    $tx .= 'De conformidad con lo establecido en el artículo 67 del Código de Procedimiento Administrativo y de lo Contencioso Administrativo, ';
    $tx .= 'autorizo a la Cámara de Comercio para que envíe notificaciones relacionadas con los registros a los correos electrónicos ';
    $tx .= 'aquí informados, sin cargo a los empresarios.';
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(20);
    $pdf->writeHTML($tx, true, false, true, false, 'J');
    $pdf->Ln();
    $pdf->Ln();

    /*
      $tx = "<strong>Atención:</strong> Si está actuando por poder especial anexe el original debidamente autenticado, o en su defecto copia simple del original autenticado. En el poder deben especificarse claramente cuales son las facultades otorgadas al apoderado.<br>";
      $pdf->SetFont('Helvetica', '', 10);
      $pdf->SetX(20);
      $pdf->writeHTML($tx, true, false, true, false, 'J');
      $pdf->Ln();
      $pdf->Ln();
     */

    if (trim($txtFirmaElectronica) == '' && $txtFirmaManuscrita == '') {
        //Firmado manual
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(20);
        $pdf->Cell(190, 4, 'Nombre: _______________________________________', 0, 0, 'L', 0);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(20);
        $pdf->Cell(190, 4, ('Identificación: _______________________________________'), 0, 0, 'L', 0);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(20);
        $pdf->Cell(190, 4, 'Firma: _______________________________________', 0, 0, 'L', 0);
    }
    if (trim($txtFirmaElectronica) != '') {
        //firmado electrónico
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->SetX(20);
        $pdf->MultiCell(190, 4, ($txtFirmaElectronica), 0, 'J', 0);
    }
    if (trim($txtFirmaManuscrita) != '') {
        //Firmado manual
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(20);
        $pdf->Cell(190, 4, 'Nombre: ' . $nombreFirmante, 0, 0, 'L', 0);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(20);
        $pdf->Cell(190, 4, ('Identificación: ' . $numIdFirmante), 0, 0, 'L', 0);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(20);
        $pdf->Cell(190, 4, 'Firma: ', 0, 0, 'L', 0);
        $posY = $pdf->GetY();
        $tmpfile = '../../tmp/' . rand(1000000, 9999999) . '-' . date("Ymd") . '-' . date("His") . '.jpg';
        $f = fopen($tmpfile, "wb");
        fwrite($f, base64_decode($txtFirmaManuscrita));
        fclose($f);
        $pdf->Image($tmpfile, 40, $posY, 40, 30);
        unlink($tmpfile);
    }

    $pdf->Ln();
    $pdf->Ln();

    $name = $_SESSION["generales"]["codigoempresa"] . '-MutacionGeneral-' . session_id() . '-' . date("Ymd") . '-' . date("His") . '.pdf';
    $pdf->Output($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, "F");
    return $name;
}

?>