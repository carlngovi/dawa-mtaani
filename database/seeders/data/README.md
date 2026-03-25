# Seeder Data Files

Place the following CSV files in this directory before running the seeders:

| File | Seeder | Rows |
|---|---|---|
| PBB_Retail.csv | PpbRegistryCacheSeeder | ~5,846 |
| PBB_Wholesale.csv | PpbRegistryCacheSeeder | ~293 |
| PPB_Hospitals.csv | PpbRegistryCacheSeeder | ~1,220 |
| PBB_Manufacturer.csv | PpbRegistryCacheSeeder | ~19 |
| Kenya_IEBC_Administrative_Units.csv | KenyaGeographySeeder | ~1,450 |

CSV files are excluded from git. Copy them from the project data folder
before running: php artisan db:seed --class=PpbRegistryCacheSeeder
