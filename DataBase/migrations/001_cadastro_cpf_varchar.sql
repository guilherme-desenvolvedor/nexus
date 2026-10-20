-- Migração 001 — corrige CPF/Telefone/CEP da tabela `cadastro`
-- (originalmente aplicada em 26/08/2026; reincluída aqui porque o banco
--  restaurado voltou ao schema antigo).
--
-- Problema: CPF, Telefone e CEP estavam como INT. CPF/telefone reais (11 díg.)
-- estouram o INT de 32 bits e viram 2147483647; CEP perde zero à esquerda.
-- CPF ainda era PRIMARY KEY AUTO_INCREMENT, o que é incorreto.
--
-- ATENÇÃO: faça backup antes (mysqldump nexus_db > backup.sql).

ALTER TABLE `cadastro` DROP PRIMARY KEY;
ALTER TABLE `cadastro` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `cadastro` MODIFY `CPF`      VARCHAR(11) NOT NULL;
ALTER TABLE `cadastro` MODIFY `Telefone` VARCHAR(15) NOT NULL;
ALTER TABLE `cadastro` MODIFY `CEP`      VARCHAR(9)  NOT NULL;
ALTER TABLE `cadastro` ADD UNIQUE KEY `uq_cpf` (`CPF`);
