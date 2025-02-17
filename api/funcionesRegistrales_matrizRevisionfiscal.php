<?php

class funcionesRegistrales_matrizRevisionfiscal {

    public static function matrizRevisionfiscal($mysqli = null, $listavinc = '') {
        $query = "select 
    matr.matricula,
    matr.numid,
    matr.razonsocial,
    matr.organizacion,
    matr.fecmatricula,
    matr.fecrenovacion,
    matr.ultanoren,
    matr.feccancelacion,
    matr.ctrestmatricula,
    ident.nombre,
    vinc.numid as NoIdentificacion,
    vinc.nombre as Revisorfiscal,
    vinc.tarjprof as TarjetaProfesional,
    vinc.vinculo,
    codvinc.descripcion,
    concat(idlibro,'-',numreg) as libronumeronombramiento,
    fecha
    from mreg_est_inscritos matr,
    mreg_est_vinculos vinc,
    bas_tipoidentificacion ident,
    mreg_codvinculos codvinc
    where vinculo in (" . $listavinc . ")        
    and estado='V'
    and matr.matricula=vinc.matricula
    and ident.homologacionsirep=vinc.idclase
    and vinc.vinculo=codvinc.id";
        try {
            $result = ejecutarQueryMysqliApi($mysqli, $query);
            return $result;
        } catch (Exception $ex) {
            $_SESSION["mensajeerror"] = 'Error en query ' . str_replace("'", "", $ex->getMessage());
            return $_SESSION["mensajeerror"];
        }
    }
}

?>
