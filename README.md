# 🔄 SyncMigrate

Automatically resolves Laravel migration order based on foreign key dependencies.

This package scans your migration files, detects foreign key relationships, and runs them in the correct order using topological sorting. No need to rename or manually order your files.

---

## ✅ Features

- Detects `foreignId()->constrained()` relationships
- Sorts migrations based on dependencies
- Runs migrations in the correct order
- No config required – works out of the box

---

## 📦 Installation

You can install SyncMigrate via Composer directly from Packagist:

``` bash
composer require hanan467/sync-migrate
```
## Usage
``` bash
php artisan migrate:sync
```


