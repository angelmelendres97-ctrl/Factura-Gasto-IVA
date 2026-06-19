# Reporte fiscal de compras con IVA múltiple

## Objetivo

El módulo `cont_fiscal_compras` mantiene la consulta histórica de compras y agrega la opción **Consulta Nuevo** para validar y explotar el nuevo modelo de IVA múltiple registrado por `factura_prove_rs`.

La nueva consulta presenta bases imponibles e IVA por porcentaje, retenciones y total a pagar sin alterar la consulta anterior. Está orientada a facturas de gasto/proveedor registradas en `saefprv` y enriquecidas con el detalle `saefprv_iva_det`.

> Nota: en este repositorio no se encontró el archivo `DETALLE DE REPORTE EXEL.xlsx` dentro de `cont_fiscal_compras`; por ello el PDF se aproxima al diseño solicitado mediante DataTables `pdfHtml5`, orientación horizontal, columnas en el orden funcional indicado y totales en pie de tabla.

## Diferencias entre la consulta anterior y la nueva

| Consulta | Botón | Fuente IVA | Uso recomendado |
| --- | --- | --- | --- |
| Histórica | `Consultar` | Campos consolidados históricos de `saefprv`/`saeminv` | Reportes ya validados y comparativos históricos. |
| Nueva | `Consulta Nuevo` | `saefprv` con `LEFT JOIN` a `saefprv_iva_det` | Reporte fiscal moderno con bases e IVA separados por 15%, 5% y 0%. |

La consulta nueva usa `LEFT JOIN` para que las facturas antiguas sin registros en `saefprv_iva_det` sigan apareciendo. Cuando no existe detalle, usa como respaldo los campos históricos:

- Base gravada: `fprv_val_grab + fprv_val_grbs`.
- Base 0%: `fprv_val_gra0 + fprv_val_gr0s`.
- IVA histórico: `fprv_val_viva`.

## Tablas utilizadas

### `saefprv`

Tabla principal de facturas de proveedor. La nueva consulta utiliza, entre otros, estos campos:

- Identificación: `fprv_cod_empr`, `fprv_cod_sucu`, `fprv_cod_clpv`, `fprv_num_seri`, `fprv_num_fact`.
- Datos de reporte: `fprv_fec_emis`, `fprv_cod_tran`, `fprv_det_fprv`, `fprv_cod_asto`, `fprv_cod_ejer`, `fprv_mes_fprv`.
- Respaldo histórico: `fprv_val_grab`, `fprv_val_grbs`, `fprv_val_gra0`, `fprv_val_gr0s`, `fprv_val_viva`, `fprv_val_vice`.

### `saefprv_iva_det`

Detalle nuevo para porcentajes múltiples de IVA:

```sql
saefprv_iva_det(
    fpid_cod_empr      integer not null,
    fpid_cod_sucu      integer not null,
    fpid_num_fact      varchar(20) not null,
    fpid_num_seri      varchar(20) not null,
    fpid_cod_clpv      integer not null,
    fpid_tip_reg       char(1) not null,
    fpid_por_iva       decimal(9,6) not null,
    fpid_base_imp      decimal(16,4) default 0 not null,
    fpid_val_iva       decimal(16,4) default 0 not null,
    fpid_val_total     decimal(16,4) default 0 not null,
    fpid_cod_sust      varchar(20),
    fpid_cod_ret       varchar(20),
    fpid_est_reg       char(1) default '1' not null,
    fpid_user_web      varchar(30),
    fpid_fech_server   date
)
```

### `saeret`

La nueva consulta agrupa las retenciones por asiento contable (`rete_cod_asto`, `asto_cod_sucu`, `asto_cod_ejer`, `asto_num_prdo`). La primera retención se muestra junto con la factura y las retenciones adicionales se muestran en filas secundarias, conservando vacías las columnas de cabecera de la factura.

## SQL principal utilizado

La consulta de IVA múltiple consolida el detalle por porcentaje antes de unirlo con `saefprv`:

```sql
left join (
    select fpid_cod_empr, fpid_cod_sucu, fpid_num_fact, fpid_num_seri, fpid_cod_clpv,
           sum(case when round(fpid_por_iva, 2) = 15 then fpid_base_imp else 0 end) as valor_15,
           sum(case when round(fpid_por_iva, 2) = 5 then fpid_base_imp else 0 end) as valor_5,
           sum(case when round(fpid_por_iva, 2) = 0 then fpid_base_imp else 0 end) as valor_0,
           sum(case when round(fpid_por_iva, 2) = 15 then fpid_val_iva else 0 end) as iva_15,
           sum(case when round(fpid_por_iva, 2) = 5 then fpid_val_iva else 0 end) as iva_5
    from saefprv_iva_det
    where fpid_est_reg = '1'
    group by fpid_cod_empr, fpid_cod_sucu, fpid_num_fact, fpid_num_seri, fpid_cod_clpv
) iva on iva.fpid_cod_empr = f.fprv_cod_empr
     and iva.fpid_cod_sucu = f.fprv_cod_sucu
     and iva.fpid_num_fact = f.fprv_num_fact
     and iva.fpid_num_seri = f.fprv_num_seri
     and iva.fpid_cod_clpv = f.fprv_cod_clpv
```

## Columnas del reporte nuevo

1. RUC
2. Razón Social
3. Fecha
4. Tipo
5. Serie
6. Secuencial
7. Detalle
8. VALOR 15%
9. VALOR 5%
10. VALOR 0%
11. IVA 15%
12. IVA 5%
13. Total Factura
14. Código Retención
15. No. Retención
16. Valor Base Retención
17. Valor Retenido
18. Total a Pagar
19. Asiento Contable

## Ejemplo de resultado

| RUC | Razón Social | Fecha | Tipo | Serie | Secuencial | VALOR 15% | VALOR 5% | VALOR 0% | IVA 15% | IVA 5% | Total Factura | Código Retención | Valor Base Retención | Valor Retenido | Total a Pagar |
| --- | --- | --- | --- | --- | --- | ---: | ---: | ---: | ---: | ---: | ---: | --- | ---: | ---: | ---: |
| 0790100719001 | PROVEEDOR EJEMPLO | 21/05/2026 | FACTURA COMPRA PROVEEDOR | 001-003 | 43022 | 128.23 | 74.62 | 0.00 | 19.23 | 3.73 | 225.81 | 312 | 202.85 | 4.06 | 214.86 |
| | | | | | | 0.00 | 0.00 | 0.00 | 0.00 | 0.00 | 0.00 | 725 | 22.96 | 6.89 | |

## Flujo completo

1. `factura_prove_rs` captura las filas de bienes/servicios y los porcentajes de IVA.
2. El navegador serializa la captura en `iva_multiple_json`.
3. El servidor recalcula bases e IVA y conserva los campos históricos consolidados de `saefprv`.
4. Si la tabla existe, se registra el detalle por porcentaje en `saefprv_iva_det`.
5. En `cont_fiscal_compras`, el usuario presiona **Consulta Nuevo**.
6. `buscar_nuevo.php` consulta `saefprv`, une con `saefprv_iva_det` mediante `LEFT JOIN`, agrega retenciones desde `saeret` y devuelve JSON para DataTables.
7. El usuario puede exportar a Excel, CSV, impresión o PDF horizontal desde los botones de DataTables.

## Validación funcional sugerida

- Registrar una factura con IVA 15% y 5%; validar que las columnas `VALOR 15%`, `VALOR 5%`, `IVA 15%` e `IVA 5%` cuadren con `saefprv_iva_det`.
- Registrar o consultar una factura histórica sin `saefprv_iva_det`; validar que aparezca usando los valores consolidados de `saefprv`.
- Registrar una factura con dos retenciones; validar que la segunda retención se muestre en una fila secundaria con los datos de factura vacíos.
- Exportar a PDF y verificar que el reporte salga en orientación horizontal con el mismo orden de columnas del grid.
- Comparar `Total a Pagar` contra `Total Factura - suma(Valor Retenido)`.
