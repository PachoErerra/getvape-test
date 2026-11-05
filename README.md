# getvape-test

Тестова копія інтернет-магазину getvape.com.ua (ocStore 3.0.3.7) — https://getvape.vapors.app

- Хостинг: «Хостинг Україна» (adm.tools), акаунт ma630802, папка сайту `getvape.vapors.app/www/site`, PHP 8.2.
- Будь-який коміт у `main` → GitHub Actions «Оновити тестовий сайт» заливає змінені файли на хостинг по FTP (секрети `FTP_HOST`, `FTP_USER`, `FTP_PASSWORD`).
- «Забрати код з хостингу» — ручний запуск, переносить код з хостингу в сховище.
- `config.php` і `admin/config.php` (паролі бази) в сховищі немає — вони лише на хостингу.
- Картинки товарів не копіюються: якщо файлу немає, сайт бере його з getvape.com.ua (`catalog/model/tool/image.php`, `.htaccess`).
- Робочий сайт getvape.com.ua це сховище не зачіпає; перенесення змін на робочий — вручну.
