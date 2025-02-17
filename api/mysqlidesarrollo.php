<?php
$cam = substr($fuente, 2);
$logsql = true;
$dbms = 'mysqli';
$dbport = '3306';
$dbusuario = 'rootsiiaurora';
$dbpassword = '8nI0d:UtL+Fs';
$debugdb = false;
$depersistency = true;

//
switch ($cam) {
    case "01" :
        $dbname = 'sii_armenia';
        $dbusuario = 'aurora_sii_1';
        $dbpassword = '0pa6OMVddH1r..1';
        break;

    case "02" :
        $dbname = 'sii_barranca';
        $dbusuario = 'aurora_sii_2';
        $dbpassword = '0pa5G5fmQQOtk22';
        break;

    case "06" :
        $dbname = 'sii_buenaventura';
        $dbusuario = 'aurora_sii_6';
        $dbpassword = '0paslg3L4J4ZYY6';
        break;

    case "07" :
        $dbname = 'sii_buga';
        $dbusuario = 'aurora_sii_7';
        $dbpassword = '0pa8MelXxwx/9c7';
        break;

    case "10" :
        $dbname = 'sii_cartago';
        $dbusuario = 'aurora_sii_10';
        $dbpassword = '1papnE05lebDuA0';
        break;

    case "11" :
        $dbname = 'sii_11';
        $dbusuario = 'aurora_sii_11';
        $dbpassword = 'cnCnt4pr11';
        break;

    case "12" :
        $dbname = 'sii_chinchina';
        $dbusuario = 'aurora_sii_12';
        $dbpassword = '1pa0keevuSJb062';
        break;

    case "13" :
        $dbname = 'sii_duitama';
        $dbusuario = 'aurora_sii_13';
        $dbpassword = '1paEXMBHPwyRLk3';
        break;

    case "14" :
        $dbname = 'sii_girardot';
        $dbusuario = 'aurora_sii_14';
        $dbpassword = '1pavJu5bpZXcHU4';
        break;

    case "15" :
        $dbname = 'sii_honda';
        $dbusuario = 'aurora_sii_15';
        $dbpassword = '1paQDoqpYnqBks5';
        break;

    case "16" :
        $dbname = 'sii_ibague';
        $dbusuario = 'aurora_sii_16';
        $dbpassword = '1paGEzudBfZC5c6';
        break;

    case "17" :
        $dbname = 'sii_ipiales';
        $dbusuario = 'aurora_sii_17';
        $dbpassword = '1paxwekIyJeR6A7';
        break;

    case "18" :
        $dbname = 'sii_dorada';
        $dbusuario = 'aurora_sii_18';
        $dbpassword = '1patPzlXCw43qc8';
        break;

    case "19" :
        $dbname = 'sii_magangue';
        $dbusuario = 'aurora_sii_19';
        $dbpassword = '1pastfhgsfY6UU9';
        break;

    case "20" :
        $dbname = 'sii_manizales';
        $dbusuario = 'aurora_sii_20';
        $dbpassword = '2payGq/OCjNnn60';
        break;

    case "22" :
        $dbname = 'sii_monteria';
        $dbusuario = 'aurora_sii_22';
        $dbpassword = '2paQNrt3k.51dc2';
        break;

    case "23" :
        $dbname = 'sii_23';
        $dbusuario = 'aurora_sii_23';
        $dbpassword = '2paC2b/G2kqEL23';
        break;

    case "24" :
        $dbname = 'sii_palmira';
        $dbusuario = 'aurora_sii_24';
        $dbpassword = '2paf257HAjqGkM4';
        break;

    case "25" :
        $dbname = 'sii_pamplona';
        $dbusuario = 'aurora_sii_25';
        $dbpassword = '2pastH1m7RIN/25';
        break;

    case "26" :
        $dbname = 'sii_pasto';
        $dbusuario = 'aurora_sii_26';
        $dbpassword = '2pa2w4B1FoCHMI6';
        break;

    case "27" :
        $dbname = 'sii_pereira';
        $dbusuario = 'aurora_sii_27';
        $dbpassword = '2pa7UISFgw0E/o7';
        break;

    case "28" :
        $dbname = 'sii_cauca';
        $dbusuario = 'aurora_sii_28';
        $dbpassword = '2pa.nGeOl8BQzk8';
        break;

    case "30" :
        $dbname = 'sii_guajira';
        $dbusuario = 'aurora_sii_30';
        $dbpassword = '3panYYQnzVUOsM0';
        break;

    case "31" :
        $dbname = 'sii_sanandres';
        $dbusuario = 'aurora_sii_31';
        $dbpassword = '3pa5Ddhr31FwnM1';
        break;

    case "32" :
        $dbname = 'sii_santamarta';
        $dbusuario = 'aurora_sii_32';
        $dbpassword = 'paCdZhfBjShuc32';
        break;

    case "33" :
        $dbname = 'sii_santarosa';
        $dbusuario = 'aurora_sii_33';
        $dbpassword = '3pa/FYAoq5vx6c3';
        break;

    case "34" :
        $dbname = 'sii_sincelejo';
        $dbusuario = 'aurora_sii_34';
        $dbpassword = '3pah.6QYe2R7zM4';
        break;

    case "35" :
        $dbname = 'sii_sogamoso';
        $dbusuario = 'aurora_sii_35';
        $dbpassword = '3pakF8/PXzmMm.5';
        break;

    case "36" :
        $dbname = 'sii_tulua';
        $dbusuario = 'aurora_sii_36';
        $dbpassword = '3pa5eT1HoG9bFA6';
        break;

    case "37" :
        $dbname = 'sii_tumaco';
        $dbusuario = 'aurora_sii_37';
        $dbpassword = '3padp/6aYO3VeA7';
        break;

    case "38" :
        $dbname = 'sii_38';
        $dbusuario = 'aurora_sii_38';
        $dbpassword = 'TnUj4pr38';
        break;

    case "39" :
        $dbname = 'sii_valledupar';
        $dbusuario = 'aurora_sii_39';
        $dbpassword = '3palJyfIJtGVr.9';
        break;

    case "40" :
        $dbname = 'sii_40';
        $dbusuario = 'aurora_sii_40';
        $dbpassword = '4paAd3ZevpYn760';
        break;

    case "41" :
        $dbname = 'sii_florencia';
        $dbusuario = 'aurora_sii_41';
        $dbpassword = '4pafCnSqhUQI/A1';
        break;

    case "42" :
        $dbname = 'sii_amazonas';
        $dbusuario = 'aurora_sii_42';
        $dbpassword = '4paIIriA91yI4M2';
        break;

    case "43" :
        $dbname = 'sii_sevilla';
        $dbusuario = 'aurora_sii_43';
        $dbpassword = '4paE5rKqceOJe63';
        break;

    case "44" :
        $dbname = 'sii_uraba';
        $dbusuario = 'aurora_sii_44';
        $dbpassword = '4pa8JMlcQbnUW64';
        break;

    case "45" :
        $dbname = 'sii_espinal';
        $dbusuario = 'aurora_sii_45';
        $dbpassword = '4pactkbgWvUnEA5';
        break;

    case "46" :
        $dbname = 'sii_ptoasis';
        $dbusuario = 'aurora_sii_46';
        $dbpassword = '4paQMjx1zLUsjc6';
        break;

    case "47" :
        $dbname = 'sii_facatativa';
        $dbusuario = 'aurora_sii_47';
        $dbpassword = '4pa2BQPpu3l.4o7';
        break;

    case "48" :
        $dbname = 'sii_arauca';
        $dbusuario = 'aurora_sii_48';
        $dbpassword = '4pabMhYq92BTnU8';
        break;

    case "49" :
        $dbname = 'sii_ocana';
        $dbusuario = 'aurora_sii_49';
        $dbpassword = '4paiWzpPdq5iTg9';
        break;

    case "50" :
        $dbname = 'sii_casanare';
        $dbusuario = 'aurora_sii_50';
        $dbpassword = '5paWemup2H1qCk0';
        break;

    case "51" :
        $dbname = 'sii_orienteantioqueno';
        $dbusuario = 'aurora_sii_51';
        $dbpassword = '5paORYizuDiwMI1';
        break;

    case "52" :
        $dbname = 'sii_mmedio';
        $dbusuario = 'aurora_sii_52';
        $dbpassword = '5paCK2un9fS.N.2';
        break;

    case "53" :
        $dbname = 'sii_aguachica';
        $dbusuario = 'aurora_sii_53';
        $dbpassword = '5pa4muwMs6ii7w3';
        break;

    case "54" :
        $dbname = 'sii_dosquebradas';
        $dbusuario = 'aurora_sii_54';
        $dbpassword = '5paKL6EA3qB14Y4';
        break;

    case "55" :
        $dbname = 'sii_aburra';
        $dbusuario = 'aurora_sii_55';
        $dbpassword = '5pamwStQ6Si2';
        break;

    case "56" :
        $dbname = 'sii_saravena';
        $dbusuario = 'aurora_sii_56';
        $dbpassword = '5paRSFvg0MPKgc6';
        break;

    case "57" :
        $dbname = 'sii_sanjose';
        $dbusuario = 'aurora_sii_57';
        $dbpassword = '5paz88kDzkRNdY7';
        break;
}


if (substr($fuente, 0, 2) == 'P-') {
    if (in_array($cam, $listards1)) {
        $dbhost = 'srv-sii-bd-aurora-0-cluster.cluster-cghnnivk3tvt.us-east-1.rds.amazonaws.com';
    } else {
        $dbhost = 'srv-sii-bd-aurora-0-cluster-02.cluster-cghnnivk3tvt.us-east-1.rds.amazonaws.com';
    }
}

if (substr($fuente, 0, 2) == 'R-') {
    if (in_array($cam, $listards1)) {
        $dbhost = 'srv-sii-bd-aurora-0-cluster.cluster-ro-cghnnivk3tvt.us-east-1.rds.amazonaws.com';
    } else {
        $dbhost = 'srv-sii-bd-aurora-0-cluster-02.cluster-ro-cghnnivk3tvt.us-east-1.rds.amazonaws.com';
    }
}

if (substr($fuente, 0, 2) == 'D-') {
    if (in_array($cam, $listards1)) {
        $dbhost = 'srv-sii-bd-desarrollo.cx6qu4imixln.us-east-1.rds.amazonaws.com';
    } else {
        $dbhost = 'srv-sii-bd-desarrollo-nodo-02.cx6qu4imixln.us-east-1.rds.amazonaws.com';
    }
}

if (substr($fuente, 0, 2) == 'Q-') {
    if (in_array($cam, $listards1)) {
        $dbhost = 'srv-sii-bd-qa.cghnnivk3tvt.us-east-1.rds.amazonaws.com';
    } else {
        $dbhost = 'srv-sii-bd-qa-g2.cghnnivk3tvt.us-east-1.rds.amazonaws.com';
    }
}
?>