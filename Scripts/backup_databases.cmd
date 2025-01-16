cd "C:\Program Files\PostgreSQL\9.1\bin>"
pg_dump -U postgres --host localhost --port 5432 -Fc bdppeao > E:\msimier\databases\bdppeao_format_c_20210630.backup
pg_dump -U postgres --host localhost --port 5432 -Fp bdppeao > E:\msimier\databases\bdppeao_format_p_20210630.backup
pg_dump -U postgres --host localhost --port 5432 -Fp --column-inserts bdppeao > E:\msimier\\databases\bdppeao_format_i_20210630.backup