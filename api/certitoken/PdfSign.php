<?php

$root = dirname(__FILE__);

echo '*****'.$root.'*****'. PHP_EOL;

$apiPath = "$root/api/wrapperSign4J/WrapperSign4J.jar";
//tipo de firma
$signType = "PADES";
//ruta del archivo properties
$xmlConfigPath = "$root/config/properties.xml";

//si es certiToken
$isCertiToken = "true";
//ruta del certificado de firma o usuario de certitoken
$signP12Path = "79048506";
//password del certificado de firma o password de certitoken
$signP12Password = "Cafaivda2010";
//Serial certificado cuando es certitoken
$serialCert = "150176125209475749819496851124674191222";
//ruta archivo a firmar
$fileToSignPath ="/Users/WSI/NetBeansProjects/SDS//repositoriolocal//20/mreg/certificados/2016/11/t4UVmhfeFE.pdf";
//ruta para guardar el archivo firmado
$fileSignedPath ="/Users/WSI/NetBeansProjects/SDS//tmp/t4UVmhfeFE_firmado.pdf";

//Emisor del certificado cuando es certitoken
$issuerCert = "CN=AC SUB CERTICAMARA, O=CERTICAMARA S.A, OU=NIT 830084433-7, C=CO, ST=DISTRITO CAPITAL, L=BOGOTA, STREET=www.certicamara.com";

//si se requiere estampa
$stamp = "false";
// tipo de autenticacion estampa // user - cert
$stampType = "";
$stampP12Path = "";
$stampP12Password = "";
// si se requiere que tenga LTV(Long Term Validation) si se colaca true automaticamente el estampado sera "true" tambien

$ltv = "false";

//razon de firma
$signReason = "CERTITOKEN";
//Locacion de firma
$signLocation = "CO";
//frase que se puede agregar en la imagen que se le agregara al documento
$contentSignature = "";
//validacion que se coloca en la imagen por parte del lector de pdf
$imageValidation = "false";
/** numero de la pagina del documento(-1 ultima pagina) (0 en todas las paginas) o para colocarlos en varias paginas enviamos numeros separados por comas */
$numPages = "null";
/* buscar el string ingresado y colocar la imagen encima, si no se requiere buscar colocar la palabra "null" */
$stringToFind = "null";
$pdfSignTypeConstants = "CERTIFIED_FORM_FILLING_AND_ANNOTATIONS";

/**
 * @var $signImageAttrs string Atributos que se configuran si se quiere la firma visible:
 * <page>,
 * <lowerLeftX>,<lowerLeftY>,
 * <upperRightX>,<upperRightY>,
 * <pdf2SignImagePath>,
 * <signFieldName>,<contentSignature>
 */
$signImageAttrs = "";

$policyXmlEpes = "null";

$comand = "\"java\" -jar "
        . "\"$apiPath\" "
        . "\"$signType\" "
        . "\"$xmlConfigPath\" "
        . "\"$fileToSignPath\" "
        . "\"$fileSignedPath\" "
        . "\"$signP12Path\" "
        . "\"$signP12Password\" "
       . "\"$stamp\" "
        . "\"$stampP12Path\" "
        . "\"$stampP12Password\" "
        . "\"$signReason\" "
        . "\"$signLocation\" "
        . "\"$signImageAttrs\" "
		    . "\"$ltv\" "
		    . "\"$policyXmlEpes\" "
		    . "\"$stampType\" "
		    . "\"$isCertiToken\" "
		    . "\"$serialCert\" "
		    . "\"$issuerCert\" "
		    . "\"$pdfSignTypeConstants\"";

echo "Comando: $comand" . PHP_EOL;

try {
    /**
     * Realiza la firma PDF y establece la configuracion del grafo a incluir
     *
     * @param args
     *          1. Tipo de firma (PADES, CADES).
     *          2. Ruta del XML de configuracion con los siguientes datos:
     *             a. properties.general.CRL_PATH.
     *             b. properties.general.VERIFY_TYPE.
     *             c. properties.general.KEYSTORE_PATH.
     *             d. properties.general.TSA_CERTIFICATE.
     *             e. properties.general.TSA_CERTIFICATE_PASSWORD.
     *             f. properties.general.TSA_POLICY.
     *             g. properties.general.ACRO6LAYER.
     *          3. Ruta del archivo a firmar.
     *          4. Ruta del archivo firmado.
     *          5. Ruta del certificado de firma.
     *          6. Clave del certificado de firma.
     *          7. Estampa (bandera).
     *          8. Ruta del certificado de estampa.
     *          9. Clave del certificado de estampa.
     *          10. Si es XML se envia el ID de la etiqueta a firmar
     *              Si es PDF se envoa la Razon de la firma.
     *          11. Localicacion de la firma.
     *          12. Atributos del grafo
     *              en caso de ser (null, false, 0) no sera visible.
     * @throws Exception
     */
    $response = exec($comand.' 2>&1', $returns);
    echo "Response: ";
    print_r($response);

    echo "Returns: ";
    print_r($returns);
} catch (Exception $exc) {
    echo $exc->getTraceAsString();
}
