# Ajuste técnico: IVA múltiple en `factura_prove_rs`

## Objetivo

Permitir que una Factura Gasto de proveedor registre más de un porcentaje de IVA en la misma factura. El formulario anterior consolidaba la base gravada en un solo campo `Valor {IVA}%` para bienes y servicios, con un porcentaje principal en `ivabp`. El cambio agrega captura dinámica por porcentaje y mantiene los campos históricos consolidados para compatibilidad con contabilidad, retenciones y reportes existentes.

## Alcance implementado

- En `factura_prove_rs/factura_prove.php` se agregaron funciones JavaScript para agregar filas dinámicas de IVA, calcular base, IVA y total por fila, consolidar bienes/servicios y serializar el detalle en `iva_multiple_json`.
- En `factura_prove_rs/_Ajax.server.php` se agregó la estructura visual en la sección **VALORES FACTURA INCLUIDO IMPUESTO** para registrar filas de IVA múltiple en bienes y servicios.
- En `factura_prove_rs/_Ajax.server.php` se agregó lógica PHP para leer `iva_multiple_json`, recalcular los campos históricos consolidados y persistir el detalle por porcentaje en una tabla intermedia.
- El reporte `contabilidad_rep_rete_compras` no existe en este repositorio; por eso no se modificó. La tabla intermedia queda preparada para que dicho módulo consulte el detalle por porcentaje mediante la factura, serie, sucursal, empresa y proveedor.

## Tabla nueva propuesta

> Ejecutar una vez en la base Informix antes de usar el nuevo guardado.

```sql
create table saefprv_iva_det (
    fpid_cod_empr      integer not null,
    fpid_cod_sucu      integer not null,
    fpid_num_fact      varchar(20) not null,
    fpid_num_seri      varchar(20) not null,
    fpid_cod_clpv      integer not null,
    fpid_tip_reg       char(1) not null, -- B=bienes, S=servicios
    fpid_por_iva       decimal(9,6) not null,
    fpid_base_imp      decimal(16,4) default 0 not null,
    fpid_val_iva       decimal(16,4) default 0 not null,
    fpid_val_total     decimal(16,4) default 0 not null,
    fpid_cod_sust      varchar(20),
    fpid_cod_ret       varchar(20),
    fpid_est_reg       char(1) default '1' not null,
    fpid_user_web      varchar(30),
    fpid_fech_server   datetime year to second
);

create index ix_saefprv_iva_det_01 on saefprv_iva_det
    (fpid_cod_empr, fpid_cod_sucu, fpid_num_seri, fpid_num_fact, fpid_cod_clpv);

create index ix_saefprv_iva_det_02 on saefprv_iva_det
    (fpid_cod_empr, fpid_cod_sucu, fpid_por_iva, fpid_tip_reg);
```

## Relación con tablas actuales

La relación lógica principal es con `saefprv`:

```sql
select *
from saefprv f
left join saefprv_iva_det d
  on d.fpid_cod_empr = f.fprv_cod_empr
 and d.fpid_cod_sucu = f.fprv_cod_sucu
 and d.fpid_num_fact = f.fprv_num_fact
 and d.fpid_num_seri = f.fprv_num_seri
 and d.fpid_cod_clpv = f.fprv_cod_clpv
where f.fprv_cod_empr = ?
  and f.fprv_cod_sucu = ?
  and f.fprv_num_fact = ?;
```

## Consulta previa al cambio

```sql
select fprv_cod_empr, fprv_cod_sucu, fprv_cod_clpv, fprv_num_seri, fprv_num_fact,
       fprv_val_grab as base_bienes_gravada,
       fprv_val_gra0 as base_bienes_0,
       fprv_val_grbs as base_servicios_gravada,
       fprv_val_gr0s as base_servicios_0,
       fprv_val_piva as porcentaje_iva_principal,
       fprv_val_viva as iva_total,
       fprv_val_totl as total_factura
from saefprv
where fprv_cod_empr = ?
  and fprv_cod_sucu = ?
  and fprv_num_fact = ?;
```

## Validación posterior al cambio

```sql
select fpid_tip_reg, fpid_por_iva,
       sum(fpid_base_imp) base_imponible,
       sum(fpid_val_iva) iva,
       sum(fpid_val_total) total
from saefprv_iva_det
where fpid_cod_empr = ?
  and fpid_cod_sucu = ?
  and fpid_num_seri = ?
  and fpid_num_fact = ?
  and fpid_cod_clpv = ?
group by fpid_tip_reg, fpid_por_iva
order by fpid_tip_reg, fpid_por_iva;
```

Comparar contra los campos consolidados de `saefprv`:

```sql
select f.fprv_val_grab + f.fprv_val_grbs as base_gravada_saefprv,
       f.fprv_val_gra0 + f.fprv_val_gr0s as base_0_saefprv,
       f.fprv_val_viva as iva_saefprv,
       sum(case when d.fpid_por_iva > 0 then d.fpid_base_imp else 0 end) as base_gravada_detalle,
       sum(case when d.fpid_por_iva = 0 then d.fpid_base_imp else 0 end) as base_0_detalle,
       sum(d.fpid_val_iva) as iva_detalle
from saefprv f
left join saefprv_iva_det d
  on d.fpid_cod_empr = f.fprv_cod_empr
 and d.fpid_cod_sucu = f.fprv_cod_sucu
 and d.fpid_num_fact = f.fprv_num_fact
 and d.fpid_num_seri = f.fprv_num_seri
 and d.fpid_cod_clpv = f.fprv_cod_clpv
where f.fprv_cod_empr = ?
  and f.fprv_cod_sucu = ?
  and f.fprv_num_seri = ?
  and f.fprv_num_fact = ?
  and f.fprv_cod_clpv = ?
group by 1, 2, 3;
```

## Ejemplo de inserción de detalle IVA 0%, 5%, 12% y 15%

```sql
insert into saefprv_iva_det
(fpid_cod_empr, fpid_cod_sucu, fpid_num_fact, fpid_num_seri, fpid_cod_clpv,
 fpid_tip_reg, fpid_por_iva, fpid_base_imp, fpid_val_iva, fpid_val_total,
 fpid_est_reg, fpid_user_web, fpid_fech_server)
values
(1, 1, '000000123', '001001', 1001, 'B', 0,  100.00,  0.00, 100.00, '1', 'admin', current),
(1, 1, '000000123', '001001', 1001, 'B', 5,  200.00, 10.00, 210.00, '1', 'admin', current),
(1, 1, '000000123', '001001', 1001, 'S', 12, 300.00, 36.00, 336.00, '1', 'admin', current),
(1, 1, '000000123', '001001', 1001, 'S', 15, 400.00, 60.00, 460.00, '1', 'admin', current);
```

## Consulta esperada para `contabilidad_rep_rete_compras`

El reporte puede seguir usando `saefprv` para totales históricos y unirse a `saefprv_iva_det` cuando necesite desglose por porcentaje:

```sql
select f.fprv_fec_emis, f.fprv_num_seri, f.fprv_num_fact, f.fprv_cod_clpv,
       d.fpid_tip_reg,
       d.fpid_por_iva,
       d.fpid_base_imp,
       d.fpid_val_iva,
       d.fpid_val_total,
       f.fprv_num_rete,
       f.fprv_val_ret1,
       f.fprv_val_ret2,
       f.fprv_val_iva1,
       f.fprv_val_iva2
from saefprv f
left join saefprv_iva_det d
  on d.fpid_cod_empr = f.fprv_cod_empr
 and d.fpid_cod_sucu = f.fprv_cod_sucu
 and d.fpid_num_fact = f.fprv_num_fact
 and d.fpid_num_seri = f.fprv_num_seri
 and d.fpid_cod_clpv = f.fprv_cod_clpv
where f.fprv_cod_empr = ?
  and f.fprv_fec_emis between ? and ?
order by f.fprv_fec_emis, f.fprv_num_seri, f.fprv_num_fact, d.fpid_tip_reg, d.fpid_por_iva;
```

## Recomendaciones de pruebas

1. Crear la tabla `saefprv_iva_det` en una base de pruebas.
2. Registrar una factura con bienes IVA 0%, bienes IVA 5%, servicios IVA 12% y servicios IVA 15%.
3. Confirmar que los campos consolidados de `saefprv` suman correctamente:
   - `fprv_val_grab + fprv_val_grbs` = suma de bases con IVA mayor a 0%.
   - `fprv_val_gra0 + fprv_val_gr0s` = suma de bases 0%.
   - `fprv_val_viva` = suma de todos los valores IVA.
   - `fprv_val_totl` = bases + ICE + IVA + no objeto + exento.
4. Confirmar que `saefprv_iva_det` contiene una fila por cada porcentaje/tipo ingresado.
5. Generar retención y verificar que las bases de fuente e IVA continúan calculándose desde los campos consolidados.
6. Ejecutar una consulta de reporte con `left join` a `saefprv_iva_det` y verificar que no se pierdan facturas históricas sin detalle múltiple.
7. Comparar totales antes y después del cambio para facturas con un solo IVA; deben mantenerse iguales.

## Archivos modificados

- `factura_prove_rs/factura_prove.php`: funciones JavaScript para filas dinámicas, cálculos y serialización JSON.
- `factura_prove_rs/_Ajax.server.php`: render del nuevo bloque, lectura del JSON, consolidación de bases/IVA y guardado en `saefprv_iva_det`.
