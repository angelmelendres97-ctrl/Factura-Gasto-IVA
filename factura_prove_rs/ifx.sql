-- ifx
alter table saeorpr add orpr_est_fact varchar(1) default 'N';
alter table saeorpr add orpr_bod_fabr integer;
alter table saebode add bode_cod_clpv integer;

alter table saepais add pais_fact_bien varchar(1) default 'S';
alter table saepais add pais_fact_serv varchar(1) default 'S';
alter table saepais add pais_cero_ele varchar(1) default 'S';
alter table saepais add pais_cero_pre varchar(1) default 'S'; 

-- mysql
CREATE TABLE `orpr_fac_prove` (
	`id_orpr_fac` INT(11) NOT NULL AUTO_INCREMENT,
	`empr_cod_empr` INT(11) NULL DEFAULT NULL,
	`sucu_cod_sucu` INT(11) NULL DEFAULT NULL,
	`clpv_cod_clpv` INT(11) NULL DEFAULT NULL,
	`clpv_nom_clpv` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`clpv_ruc_clpv` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`fprv_num_fact` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`fprv_num_serie` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`fprv_fech_fact` DATE NULL DEFAULT NULL,
	`fprv_tot_fprv` DECIMAL(10,2) NULL DEFAULT NULL,
	`orpr_sec_orpr` INT(11) NULL DEFAULT NULL,
	`orpr_cod_orpr` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`fprv_cod_asto` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`fecha_server` DATETIME NULL DEFAULT NULL,
	`usuario_id` BIGINT(20) NULL DEFAULT NULL,
	PRIMARY KEY (`id_orpr_fac`),
	INDEX `FK_orpr_fac_prove_usuario` (`usuario_id`),
	CONSTRAINT `FK_orpr_fac_prove_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`USUARIO_ID`)
)
COLLATE='utf8_bin'
ENGINE=InnoDB
;

CREATE TABLE IF NOT EXISTS `pais_imp_comb` (
  `id_imp_comb` int(11) NOT NULL AUTO_INCREMENT,
  `imp_nombre` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `imp_valor` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_imp_comb`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='IMPUESTO A GASOLINA';

-- Volcando datos para la tabla servicable.pais_imp_comb: ~3 rows (aproximadamente)
/*!40000 ALTER TABLE `pais_imp_comb` DISABLE KEYS */;
INSERT INTO `pais_imp_comb` (`id_imp_comb`, `imp_nombre`, `imp_valor`) VALUES
	(1, 'SUPER', 0.0),
	(2, 'REGULAR', 0.00),
	(3, 'DIESEL', 0.00);



select  orpr_sec_orpr, orpr_cod_orpr   from saeorpr where
				orpr_cod_empr  = 1 and
				orpr_est_fact  = 'N' and
				orpr_bod_fabr in (  select bode_cod_bode from saebode where
						bode_cod_empr = 	 1 and
						bode_cod_clpv = 12 )
				order by 2;


-- IVA multiple por factura de proveedor
CREATE TABLE IF NOT EXISTS `saefprv_iva_det` (
  `fiva_id` BIGINT NOT NULL AUTO_INCREMENT,
  `fiva_cod_empr` INT NOT NULL,
  `fiva_cod_sucu` INT NOT NULL,
  `fiva_cod_ejer` INT NOT NULL,
  `fiva_cod_tran` VARCHAR(20) NOT NULL,
  `fiva_cod_clpv` INT NOT NULL,
  `fiva_num_fact` VARCHAR(50) NOT NULL,
  `fiva_num_seri` VARCHAR(50) NULL,
  `fiva_tipo_reg` VARCHAR(20) NOT NULL DEFAULT 'FACTURA',
  `fiva_por_iva` DECIMAL(10,2) NOT NULL,
  `fiva_base_bienes` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `fiva_val_iva_bienes` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `fiva_total_bienes` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `fiva_base_servicios` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `fiva_val_iva_servicios` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `fiva_total_servicios` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `fiva_cod_asto` VARCHAR(50) NULL,
  `fiva_usuario_id` BIGINT NULL,
  `fiva_fecha_server` DATETIME NULL,
  PRIMARY KEY (`fiva_id`),
  KEY `idx_saefprv_iva_det_factura` (`fiva_cod_empr`, `fiva_cod_sucu`, `fiva_cod_ejer`, `fiva_cod_tran`, `fiva_cod_clpv`, `fiva_num_fact`),
  KEY `idx_saefprv_iva_det_porcentaje` (`fiva_por_iva`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
