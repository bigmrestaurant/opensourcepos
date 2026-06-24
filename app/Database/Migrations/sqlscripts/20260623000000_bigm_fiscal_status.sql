ALTER TABLE `ospos_sales`
    ADD COLUMN `pra_fiscal_status` TINYINT(1) NULL DEFAULT NULL COMMENT '0=failed 1=success',
    ADD COLUMN `fbr_fiscal_status` TINYINT(1) NULL DEFAULT NULL COMMENT '0=failed 1=success';
