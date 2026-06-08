ALTER TABLE `ospos_sales`
    ADD COLUMN `walkin_name` VARCHAR(100) DEFAULT NULL,
    ADD COLUMN `walkin_phone` VARCHAR(50) DEFAULT NULL,
    ADD COLUMN `walkin_cnic` VARCHAR(50) DEFAULT NULL,
    ADD COLUMN `pra_invoice_number` VARCHAR(64) DEFAULT NULL,
    ADD COLUMN `fbr_invoice_number` VARCHAR(64) DEFAULT NULL;
