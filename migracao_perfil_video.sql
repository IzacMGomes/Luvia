-- migracao_perfil_video.sql
-- Execute no phpMyAdmin depois do database.sql e do cursos_schema.sql

USE plataforma_cursos;

ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS foto_perfil VARCHAR(255) DEFAULT NULL AFTER senha;

ALTER TABLE aulas
    ADD COLUMN IF NOT EXISTS video_arquivo VARCHAR(255) DEFAULT NULL AFTER video_url;
