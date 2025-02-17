<?php

class funcionesRegistrales_matrizActividadservicio {

    public static function matrizActividadservicio($mysqli, $tipo, $anno, $fechaini, $fechafin, $condicionPresencial, $condicionVirtual, $condicionRueReceptora, $condicionRueResponsable, $servicios, $serviciosRenovacion, $serviciosCertificados) {
        if ($tipo == 0) {
            $query = "select s.idservicio, s.nombre,
    sum(case when r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionPresencial . " AND trim(r.servicio)=trim(s.idservicio) then 1 else 0 end) as CantidadPresencial,
    sum(case when r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionPresencial . " AND trim(r.servicio)=trim(s.idservicio) then abs(r.valor) else 0 end) as ValorPresencial,
    sum(case when r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionVirtual . " AND trim(r.servicio)=trim(s.idservicio) then 1 else 0 end) as CantidadVirtual,
    sum(case when r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionVirtual . " AND trim(r.servicio)=trim(s.idservicio) then abs(r.valor) else 0 end) as ValorVirtual,
    sum(case when r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionRueReceptora . " AND trim(r.servicio)=trim(s.idservicio) then 1 else 0 end) as CantidadRueResponsable,
    sum(case when r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionRueReceptora . " AND trim(r.servicio)=trim(s.idservicio) then abs(r.valor) else 0 end ) as ValorRueResponsable,
    sum(case when r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionRueResponsable . " AND trim(r.servicio)=trim(s.idservicio) then 1 else 0 end) as CantidadRueReceptora,
    sum(case when r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionRueResponsable . " AND trim(r.servicio)=trim(s.idservicio) then abs(r.valor) else 0 end) as ValorRueReceptora 
    FROM mreg_servicios AS s 
    INNER JOIN 
     mreg_est_recibos AS r on s.idservicio=r.servicio
     WHERE trim(s.idservicio) LIKE '" . $servicios . "' AND trim(s.nombre)!=''
     group by s.idservicio, s.nombre";

            // 2018-02-12: JINT: prueba sin contar reversados
            $query = "select r.servicio, s.nombre,
    sum(case when r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionPresencial . " AND trim(r.servicio)=trim(s.idservicio) then r.cantidad else 0 end) as CantidadPresencial,
    sum(case when r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionPresencial . " AND trim(r.servicio)=trim(s.idservicio) then abs(r.valor) else 0 end) as ValorPresencial,
    sum(case when r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionVirtual . " AND trim(r.servicio)=trim(s.idservicio) then r.cantidad else 0 end) as CantidadVirtual,
    sum(case when r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionVirtual . " AND trim(r.servicio)=trim(s.idservicio) then abs(r.valor) else 0 end) as ValorVirtual,
    sum(case when r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionRueReceptora . " AND trim(r.servicio)=trim(s.idservicio) then r.cantidad else 0 end) as CantidadRueResponsable,
    sum(case when r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionRueReceptora . " AND trim(r.servicio)=trim(s.idservicio) then abs(r.valor) else 0 end ) as ValorRueResponsable,
    sum(case when r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionRueResponsable . " AND trim(r.servicio)=trim(s.idservicio) then r.cantidad else 0 end) as CantidadRueReceptora,
    sum(case when r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionRueResponsable . " AND trim(r.servicio)=trim(s.idservicio) then abs(r.valor) else 0 end) as ValorRueReceptora 
    FROM mreg_servicios AS s 
    INNER JOIN 
     mreg_est_recibos AS r on s.idservicio=r.servicio
     WHERE trim(r.servicio) LIKE '" . $servicios . "' and substring(r.numerorecibo,1,1) IN ('S','R')
     group by r.servicio, s.nombre";
        }


        if ($tipo == 1) {
            $query = "SELECT s.idservicio, s.nombre,
      (SELECT count(*) Cantidad  FROM mreg_est_recibos AS r WHERE 
      r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionPresencial . " AND r.servicio=s.idservicio)
      AS CantidadPresencial,
      (SELECT IFNULL(sum(abs(r.valor)),0) Valor FROM mreg_est_recibos AS r WHERE 
      r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionPresencial . " AND r.servicio=s.idservicio)
      AS ValorPresencial,
      (SELECT count(*) Cantidad FROM mreg_est_recibos AS r WHERE 
      r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionVirtual . " AND r.servicio=s.idservicio)
      AS CantidadVirtual,
      (SELECT IFNULL(sum(abs(r.valor)),0) Valor FROM mreg_est_recibos AS r 
      WHERE r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionVirtual . " AND r.servicio=s.idservicio)
      AS ValorVirtual,
      (SELECT count(*) Cantidad FROM mreg_est_recibos AS r WHERE 
      r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionRueReceptora . " AND r.servicio=s.idservicio)
      AS CantidadRueResponsable,
      (SELECT IFNULL(sum(abs(r.valor)),0) Valor FROM mreg_est_recibos AS r WHERE 
      r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionRueReceptora . " AND r.servicio=s.idservicio)
      AS ValorRueResponsable,
      (SELECT count(*) Cantidad FROM mreg_est_recibos AS r WHERE 
      r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionRueResponsable . " AND r.servicio=s.idservicio)
      AS CantidadRueReceptora,
      (SELECT IFNULL(sum(abs(r.valor)),0) Valor FROM mreg_est_recibos AS r WHERE 
      r.anorenovacion LIKE '" . $anno . "' AND (r.fecoperacion BETWEEN '" . $fechaini . "' AND '" . $fechafin . "') AND " . $condicionRueResponsable . " AND r.servicio=s.idservicio)
      AS ValorRueReceptora 
      FROM mreg_servicios AS s WHERE (s.idservicio NOT LIKE '" . $serviciosReopnovacion . "' AND s.idservicio NOT LIKE '" . $serviciosCertificados . "') AND trim(s.nombre)!=''";
        }

        try {

            $result = ejecutarQueryMysqliApi($mysqli, $query);
            return $result;
        } catch (Exception $ex) {
            $_SESSION["mensajeerror"] = 'Error en query ' . str_replace("'", "", $ex->getMessage());
            $result = $_SESSION["mensajeerror"];
            return $result;
        }
    }
}

?>
