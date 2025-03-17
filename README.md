# Feed for HF Pager

### Requirements
1. PHP 8.3+ with php-imap extension
2. MySQL 8.0

### Installation
1. ```git clone https://github.com/ra0ued/zabtech-pager.git```
2. Properly fill `.env` file
3. ```composer install```
4. ```php bin/console doctrine:database:create```
5. ```php bin/console doctrine:migrations:migrate```

### Usage
Command launch by cron: ```php bin/console pager:fetch-emails``` (it will fetch all emails from email address set in env file at EMAIL_FROM field). Once a minure recommended.
If emails successfully fetched they will appear as paginated feed at route `/`.
