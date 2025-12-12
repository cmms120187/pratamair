-- Fix Migration: Hapus record migration yang gagal
-- Jalankan query ini di phpMyAdmin atau database client Hostinger

-- 1. Hapus record migration yang gagal
DELETE FROM migrations 
WHERE migration = '2025_12_11_095930_add_work_hours_to_production_daily_grades_table';

-- 2. Cek apakah kolom sudah ada (opsional, untuk verifikasi)
-- DESCRIBE production_daily_grades;

-- 3. Jika ada kolom yang belum ada, tambahkan manual (jika perlu):
-- ALTER TABLE production_daily_grades 
-- ADD COLUMN IF NOT EXISTS target_per_hour INT NULL AFTER production_date,
-- ADD COLUMN IF NOT EXISTS start_time TIME NULL AFTER target_per_hour,
-- ADD COLUMN IF NOT EXISTS end_time TIME NULL AFTER start_time,
-- ADD COLUMN IF NOT EXISTS break_duration DECIMAL(3,1) NULL AFTER end_time;

