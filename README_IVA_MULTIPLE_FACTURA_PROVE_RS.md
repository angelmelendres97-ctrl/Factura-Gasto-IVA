# Ajuste técnico: IVA múltiple en `factura_prove_rs`

## Objetivo

Permitir que una Factura Gasto de proveedor registre uno o varios porcentajes de IVA en la misma factura, sin mantener en pantalla campos fijos históricos como `Valor 15%`, `Valor 0%`, `IVA` o `IVA %` debajo de las columnas de bienes, servicios y total. La captura y el cálculo ahora nacen de filas dinámicas por porcentaje, mientras que los campos consolidados históricos de `saefprv` se actualizan internamente para compatibilidad.

## Diseño visual actualizado

La sección **VALORES FACTURA INCLUIDO IMPUESTO** queda organizada en tres columnas:

1. **BIENES**
   - El usuario agrega filas dinámicas con `% IVA`, `Base`, `IVA` calculado y `Total`.
   - Se permiten porcentajes como 0%, 5%, 12% y 15%.
   - Debajo de la tabla ya no se muestran campos fijos históricos.
   - Se muestra solo un resumen dinámico generado con los porcentajes realmente ingresados, por ejemplo `Valor 0%`, `Valor 5%`, `IVA 5%` y `Total bienes`.

2. **SERVICIOS**
   - Tiene el mismo comportamiento que bienes.
   - Los porcentajes usados en servicios se resumen dinámicamente según las filas ingresadas.

3. **TOTAL**
   - Agrupa bienes y servicios por porcentaje de IVA.
   - Si la factura usa IVA 0%, 5% y 15%, se muestran únicamente esos conceptos: `Valor 0%`, `Valor 5%`, `Valor 15%`, `IVA 5%`, `IVA 15%` y `Total general`.
   - No se muestran porcentajes que no existan en la factura.

## Relación entre campos dinámicos e históricos

El detalle visual y operativo se maneja con filas dinámicas y se serializa en `iva_multiple_json`. Cada fila contiene:

- `tipo`: `bienes` o `servicios`.
- `porcentaje_iva`: porcentaje aplicado.
- `base_imponible`: base ingresada.
- `valor_iva`: IVA calculado automáticamente desde base y porcentaje.
- `total`: base más IVA.

Los campos históricos de `saefprv` se mantienen ocultos en el formulario y se recalculan internamente para no romper procesos existentes:

- `fprv_val_grab` / `fprv_val_grbs`: suma de bases de bienes/servicios con IVA mayor a 0%.
- `fprv_val_gra0` / `fprv_val_gr0s`: suma de bases de bienes/servicios con IVA 0%.
- `fprv_val_viva`: suma total del IVA de bienes y servicios.
- `fprv_val_piva`: porcentaje histórico referencial. Cuando hay múltiples porcentajes, no debe usarse como único origen del detalle.
- `fprv_val_totl`: total consolidado de factura.

## Persistencia del detalle

Antes de guardar, JavaScript sincroniza las filas dinámicas en `iva_multiple_json`. En PHP se vuelve a leer ese JSON, se recalculan los valores consolidados y se registra el detalle en `saefprv_iva_det`.

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

## Consulta de validación del detalle

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

## Impacto en `contabilidad_rep_rete_compras`

El módulo existe en este repositorio. Actualmente el reporte consulta las bases e IVA desde los campos consolidados de `saefprv`:

- Base gravada: `fprv_val_grab + fprv_val_grbs`.
- Base 0%: `fprv_val_gra0 + fprv_val_gr0s`.
- IVA: `fprv_val_viva`.
- Total: suma de bases históricas más IVA.

Ese comportamiento debe mantenerse para no afectar facturas históricas ni reportes existentes. Para reportes nuevos o columnas de desglose por porcentaje, el módulo debe usar un `left join` hacia `saefprv_iva_det`, de modo que:

- Las facturas antiguas sin detalle múltiple sigan apareciendo.
- Las facturas nuevas puedan mostrar base e IVA por porcentaje real.
- Los totales generales sigan conciliando contra `saefprv`.

Consulta sugerida para desglose opcional:

```sql
select f.fprv_fec_emis, f.fprv_num_seri, f.fprv_num_fact, f.fprv_cod_clpv,
       d.fpid_tip_reg,
       d.fpid_por_iva,
       sum(d.fpid_base_imp) as base_porcentaje,
       sum(d.fpid_val_iva) as iva_porcentaje,
       sum(d.fpid_val_total) as total_porcentaje,
       f.fprv_val_grab + f.fprv_val_grbs as base_gravada_historica,
       f.fprv_val_gra0 + f.fprv_val_gr0s as base_0_historica,
       f.fprv_val_viva as iva_historico
from saefprv f
left join saefprv_iva_det d
  on d.fpid_cod_empr = f.fprv_cod_empr
 and d.fpid_cod_sucu = f.fprv_cod_sucu
 and d.fpid_num_fact = f.fprv_num_fact
 and d.fpid_num_seri = f.fprv_num_seri
 and d.fpid_cod_clpv = f.fprv_cod_clpv
 and d.fpid_est_reg = '1'
where f.fprv_cod_empr = ?
  and f.fprv_fec_emis between ? and ?
group by 1,2,3,4,5,6,10,11,12
order by f.fprv_fec_emis, f.fprv_num_seri, f.fprv_num_fact, d.fpid_tip_reg, d.fpid_por_iva;
```

## Recomendaciones de pruebas

1. Registrar una factura con bienes IVA 0%, bienes IVA 5%, servicios IVA 12% y servicios IVA 15%.
2. Confirmar que en pantalla no aparecen los campos fijos históricos debajo de bienes, servicios ni total.
3. Confirmar que los resúmenes dinámicos muestran solo los porcentajes usados.
4. Confirmar que `iva_multiple_json` contiene una fila por detalle ingresado.
5. Confirmar que `saefprv_iva_det` contiene una fila por tipo y porcentaje ingresado.
6. Confirmar que los campos consolidados de `saefprv` siguen cuadrando con el detalle.
7. Ejecutar el reporte de compras y retenciones actual para validar que no pierde facturas históricas.
8. Ejecutar una consulta opcional con `left join` a `saefprv_iva_det` para validar el nuevo desglose por porcentaje.

## Archivos modificados

- `factura_prove_rs/_Ajax.server.php`: rediseño visual de la sección, ocultando campos históricos y agregando contenedores de resumen dinámico.
- `factura_prove_rs/factura_prove.php`: cálculo automático de IVA por fila, agrupación dinámica por porcentaje, actualización interna de campos históricos y serialización en `iva_multiple_json`.
- `README_IVA_MULTIPLE_FACTURA_PROVE_RS.md`: documentación del diseño visual, persistencia, campos históricos y preparación del reporte `contabilidad_rep_rete_compras`.

## Carga automática desde Clave de Acceso SRI

Cuando el usuario ingresa una Clave de Acceso de 49 dígitos, el formulario ejecuta `xajax_clave_acceso` para consultar el comprobante autorizado, guardar temporalmente el XML recibido y leerlo con `simplexml_load_file`. En ese flujo se procesan:

- `infoTributaria`: establecimiento, punto de emisión, secuencial, RUC del proveedor y tipo de documento.
- `infoFactura`: identificación del comprador, fecha de emisión y `totalConImpuestos`.
- `detalles`: descripción que se pasa al detalle de la factura.
- Datos complementarios usados por las secciones de proveedor, retención y directorio mediante los mismos scripts AJAX existentes (`cargarDatosClpv`, `totales` y `totales1`).

La lectura de `infoFactura/totalConImpuestos/totalImpuesto` transforma cada impuesto del XML en una fila dinámica de IVA múltiple. Para cada nodo se toma:

- `tarifa`, cuando existe, como porcentaje real de IVA.
- `codigoPorcentaje` como respaldo para códigos conocidos: 0 = 0%, 2 = 12%, 4 = 15%, 5 = 5%, 10 = 13%.
- `baseImponible` como base de la fila.
- `valor` como IVA informado por el XML autorizado.

Después de cargar esos datos, el servidor envía al navegador el script `cargarIvaMultipleDesdeSri(...)`. Esta función limpia las filas anteriores de bienes/servicios, crea las filas dinámicas necesarias, ejecuta `calcularIvaMultiple()`, actualiza los resúmenes visibles y sincroniza `iva_multiple_json`. Con esto, una factura cargada desde XML queda igual que una factura digitada manualmente y puede guardarse en `saefprv_iva_det`.

El flujo también mantiene la actualización interna de campos históricos (`valor_grab12b`, `valor_grab0b`, `valor_grab12t`, `valor_grab0t`, `valor_exentoIva`, IVA y total consolidado) para que retenciones, directorio y reportes existentes sigan operando con `saefprv`.

### Validación recomendada para clave de acceso

1. Consultar una clave de acceso cuyo XML tenga IVA 0% y 15%.
2. Confirmar que se crean dos filas dinámicas y que el resumen TOTAL muestra solo `Valor 0%`, `Valor 15%`, `IVA 15%` y `Total general`.
3. Consultar una clave de acceso con IVA 5%, 12% y 15%.
4. Confirmar que `iva_multiple_json` queda lleno antes de guardar.
5. Confirmar que proveedor, fecha, serie, secuencial, autorización, detalle, retenciones y directorio se siguen completando con los scripts AJAX existentes.
6. Guardar y validar que `saefprv_iva_det` contiene los porcentajes cargados desde el XML.
