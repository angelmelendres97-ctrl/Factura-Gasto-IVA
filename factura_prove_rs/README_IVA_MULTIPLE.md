# IVA múltiple en Factura Gasto (`factura_prove_rs`)

## Objetivo
Permitir que una factura de proveedor registre más de una tarifa de IVA en la misma transacción (por ejemplo 0%, 5%, 12% y 15%), separando bases e IVA para bienes y servicios, sin perder compatibilidad con los campos actuales de `saefprv` usados por retenciones, diario y reportes.

## Resumen técnico
El formulario conserva los campos históricos (`fprv_val_grab`, `fprv_val_grbs`, `fprv_val_piva`, `fprv_val_viva`, etc.) como acumulados. Se agrega un detalle intermedio por tarifa de IVA para almacenar la composición real de la factura.

Archivos modificados:
- `factura_prove_rs/factura_prove.php`: función JavaScript `facturaProveActualizarIvaMultiple()` para calcular IVA por tarifa, armar JSON y actualizar los totales legacy.
- `factura_prove_rs/_Ajax.server.php`: render del bloque IVA múltiple, lectura del JSON, acumulación para totales/retenciones y guardado en tabla detalle.
- `factura_prove_rs/ifx.sql`: script de creación de tabla detalle.

## Tabla nueva propuesta

```sql
CREATE TABLE IF NOT EXISTS saefprv_iva_det (
  fiva_id BIGINT NOT NULL AUTO_INCREMENT,
  fiva_cod_empr INT NOT NULL,
  fiva_cod_sucu INT NOT NULL,
  fiva_cod_ejer INT NOT NULL,
  fiva_cod_tran VARCHAR(20) NOT NULL,
  fiva_cod_clpv INT NOT NULL,
  fiva_num_fact VARCHAR(50) NOT NULL,
  fiva_num_seri VARCHAR(50) NULL,
  fiva_tipo_reg VARCHAR(20) NOT NULL DEFAULT 'FACTURA',
  fiva_por_iva DECIMAL(10,2) NOT NULL,
  fiva_base_bienes DECIMAL(14,2) NOT NULL DEFAULT 0,
  fiva_val_iva_bienes DECIMAL(14,2) NOT NULL DEFAULT 0,
  fiva_total_bienes DECIMAL(14,2) NOT NULL DEFAULT 0,
  fiva_base_servicios DECIMAL(14,2) NOT NULL DEFAULT 0,
  fiva_val_iva_servicios DECIMAL(14,2) NOT NULL DEFAULT 0,
  fiva_total_servicios DECIMAL(14,2) NOT NULL DEFAULT 0,
  fiva_cod_asto VARCHAR(50) NULL,
  fiva_usuario_id BIGINT NULL,
  fiva_fecha_server DATETIME NULL,
  PRIMARY KEY (fiva_id),
  KEY idx_saefprv_iva_det_factura (fiva_cod_empr, fiva_cod_sucu, fiva_cod_ejer, fiva_cod_tran, fiva_cod_clpv, fiva_num_fact),
  KEY idx_saefprv_iva_det_porcentaje (fiva_por_iva)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
```

## Consultas antes del cambio

```sql
SELECT fprv_cod_empr, fprv_cod_sucu, fprv_cod_ejer, fprv_cod_tran,
       fprv_cod_clpv, fprv_num_fact, fprv_val_grab, fprv_val_grbs,
       fprv_val_gra0, fprv_val_gr0s, fprv_val_piva, fprv_val_viva,
       fprv_val_totl
FROM saefprv
WHERE fprv_cod_empr = 1
ORDER BY fprv_fec_emis DESC
LIMIT 20;
```

## Validación posterior

```sql
SELECT d.fiva_num_fact, d.fiva_por_iva,
       d.fiva_base_bienes, d.fiva_val_iva_bienes,
       d.fiva_base_servicios, d.fiva_val_iva_servicios,
       d.fiva_total_bienes + d.fiva_total_servicios AS total_tarifa
FROM saefprv_iva_det d
WHERE d.fiva_cod_empr = 1
ORDER BY d.fiva_fecha_server DESC, d.fiva_num_fact, d.fiva_por_iva;
```

Comparación contra acumulados de `saefprv`:

```sql
SELECT f.fprv_num_fact,
       f.fprv_val_grab + f.fprv_val_grbs AS base_gravada_saefprv,
       SUM(d.fiva_base_bienes + d.fiva_base_servicios) AS base_gravada_detalle,
       f.fprv_val_viva AS iva_saefprv,
       SUM(d.fiva_val_iva_bienes + d.fiva_val_iva_servicios) AS iva_detalle
FROM saefprv f
JOIN saefprv_iva_det d
  ON d.fiva_cod_empr = f.fprv_cod_empr
 AND d.fiva_cod_sucu = f.fprv_cod_sucu
 AND d.fiva_cod_ejer = f.fprv_cod_ejer
 AND d.fiva_cod_tran = f.fprv_cod_tran
 AND d.fiva_cod_clpv = f.fprv_cod_clpv
 AND d.fiva_num_fact = f.fprv_num_fact
GROUP BY f.fprv_num_fact, f.fprv_val_grab, f.fprv_val_grbs, f.fprv_val_viva;
```

## Ejemplo con IVA 0%, 5%, 12% y 15%

```sql
SELECT 0 AS iva, 100.00 AS base_bienes, 0.00 AS iva_bienes
UNION ALL SELECT 5, 200.00, 10.00
UNION ALL SELECT 12, 300.00, 36.00
UNION ALL SELECT 15, 400.00, 60.00;
```

Total esperado de bases: 1000.00. Total esperado de IVA: 106.00. Total factura, sin ICE/no objeto/exento: 1106.00.

## Preparación para `contabilidad_rep_rete_compras`
El reporte actualmente puede seguir usando `saefprv` para totales acumulados. Para explotar el detalle por tarifa, deberá unir opcionalmente `saefprv_iva_det` con las mismas claves de factura y agrupar por `fiva_por_iva`.

```sql
SELECT f.fprv_num_fact, d.fiva_por_iva,
       SUM(d.fiva_base_bienes + d.fiva_base_servicios) AS base_por_tarifa,
       SUM(d.fiva_val_iva_bienes + d.fiva_val_iva_servicios) AS iva_por_tarifa
FROM saefprv f
LEFT JOIN saefprv_iva_det d
  ON d.fiva_cod_empr = f.fprv_cod_empr
 AND d.fiva_cod_sucu = f.fprv_cod_sucu
 AND d.fiva_cod_ejer = f.fprv_cod_ejer
 AND d.fiva_cod_tran = f.fprv_cod_tran
 AND d.fiva_cod_clpv = f.fprv_cod_clpv
 AND d.fiva_num_fact = f.fprv_num_fact
WHERE f.fprv_cod_empr = 1
GROUP BY f.fprv_num_fact, d.fiva_por_iva;
```

## Recomendaciones de prueba
1. Crear la tabla en una base de pruebas.
2. Registrar una factura solo con IVA 15% y comparar contra el comportamiento anterior.
3. Registrar una factura con 0%, 5%, 12% y 15% en bienes y servicios.
4. Validar que `saefprv` almacene acumulados correctos y que `saefprv_iva_det` almacene una fila por tarifa usada.
5. Generar retención y confirmar que las bases de fuente e IVA se calculen sobre la suma de todas las tarifas.
6. Ejecutar las consultas de comparación antes/después y revisar diferencias de redondeo de centavos.
7. Revisar `contabilidad_rep_rete_compras` con la consulta de ejemplo antes de modificar el reporte.
