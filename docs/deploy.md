# Deployment (Debian server, office network only)

The app runs at `https://inventur.sth.care` and is only reachable from inside the office network.
Nothing is exposed to the internet. Login is Microsoft Entra ID (SSO), which requires HTTPS for
any redirect URI other than `localhost` — that is what most of this setup is about.

```
phone / PC (office Wi-Fi/LAN)
   │  DNS: inventur.sth.care → 192.168.1.50 (A record at GoDaddy)
   ▼
Debian server: Apache + PHP-FPM 8.4 + MySQL 8.4
   │  certificate: Let's Encrypt via DNS-01 (challenge delegated to deSEC)
   ▼
login.microsoftonline.com → https://inventur.sth.care/site/auth?authclient=azure
```

Replace `192.168.1.50` everywhere with the server's actual address.

## 1. Network and DNS (once)

1. **Fixed IP:** reserve an address for the server in the router (DHCP reservation).
2. **deSEC account** (desec.io, free): register `sth-acme.dedyn.io` and create an API token.
   It is only used for the certificate challenge; `sth.care` itself stays at GoDaddy.
   (GoDaddy only offers API access to accounts with many domains, so certbot can't use it directly.)
3. **GoDaddy DNS for `sth.care`** — add two records, nothing else changes (mail/M365 untouched):

   | Type  | Name                       | Value                                |
   |-------|----------------------------|--------------------------------------|
   | A     | `inventur`                 | `192.168.1.50`                       |
   | CNAME | `_acme-challenge.inventur` | `_acme-challenge.sth-acme.dedyn.io`  |

4. **Router DNS rebinding protection:** routers like the FRITZ!Box drop DNS answers that point to
   private IPs. Add `inventur.sth.care` to the exceptions, otherwise the name doesn't resolve in the office.

Check from a laptop in the office: `dig +short inventur.sth.care` → `192.168.1.50`.

## 2. Server packages

```bash
sudo apt install apache2 php8.4-fpm php8.4-cli php8.4-mysql php8.4-intl php8.4-mbstring php8.4-curl php8.4-xml php8.4-gd composer git unzip
sudo a2enmod proxy_fcgi setenvif rewrite ssl headers
sudo a2enconf php8.4-fpm
```

`php -v` must satisfy the `php` constraint in `composer.json`.

**MySQL 8.4:** Debian only ships MariaDB. To match the dev environment (DDEV uses MySQL 8.4),
install MySQL 8.4 LTS from the official MySQL APT repository (dev.mysql.com/downloads/repo/apt).

```sql
CREATE DATABASE inventur CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'inventur'@'localhost' IDENTIFIED BY '<strong password>';
GRANT ALL PRIVILEGES ON inventur.* TO 'inventur'@'localhost';
```

MySQL only needs to listen on localhost (default).

## 3. Application

The repo is private: add a read-only **deploy key** (GitHub → repo → Settings → Deploy keys) for the server.

```bash
sudo mkdir -p /var/www/medino-inventur && sudo chown $USER: /var/www/medino-inventur
git clone git@github.com:frameyohomies/medungeon.git /var/www/medino-inventur
cd /var/www/medino-inventur
composer install --no-dev --optimize-autoloader
sudo chown -R www-data: runtime web/assets
```

Do **not** set `YII_ENV` on the server: `web/index.php` and `yii` default to `prod`
(debug off, no Gii/debug module — those are dev-only packages).

Create the two untracked config files:

`config/db.php`

```php
<?php

return [
    'class' => \yii\db\Connection::class,
    'dsn' => 'mysql:host=localhost;dbname=inventur',
    'username' => 'inventur',
    'password' => '<strong password>',
    'charset' => 'utf8mb4',
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
];
```

`config/params-local.php` (generate a new key with `openssl rand -base64 32`; never reuse the dev values)

```php
<?php

return [
    'cookieValidationKey' => '<random string>',
    'azureClientSecret' => '<production client secret>',
    'azureReturnUrl' => 'https://inventur.sth.care/site/auth?authclient=azure',
];
```

```bash
sudo chmod 640 config/db.php config/params-local.php
sudo chgrp www-data config/db.php config/params-local.php
sudo -u www-data php yii migrate --interactive=0
```

## 4. Certificate (Let's Encrypt via DNS-01)

Run as root so the renewal cron job can write to `/etc/ssl` and reload Apache:

```bash
curl https://get.acme.sh | sh -s email=ck@sth.care
export DEDYN_TOKEN=<deSEC token>
~/.acme.sh/acme.sh --issue --server letsencrypt -d inventur.sth.care --dns dns_desec --challenge-alias sth-acme.dedyn.io
~/.acme.sh/acme.sh --install-cert -d inventur.sth.care \
    --key-file /etc/ssl/private/inventur.key \
    --fullchain-file /etc/ssl/certs/inventur.pem \
    --reloadcmd "systemctl reload apache2"
```

acme.sh stores the token and renews automatically via its own cron job.

## 5. Apache

```bash
sudo cp docs/apache-inventur.conf /etc/apache2/sites-available/inventur.conf
sudo a2ensite inventur && sudo a2dissite 000-default
sudo apachectl configtest && sudo systemctl reload apache2
```

## 6. Microsoft Entra

In the app registration (Entra admin center → App registrations → the app):

- **Authentication → Web → Redirect URIs:** add `https://inventur.sth.care/site/auth?authclient=azure`.
  It must match `azureReturnUrl` exactly.
- **Certificates & secrets:** create a production client secret for `params-local.php`.
  **Secrets expire (max. 24 months) — put the expiry date in the calendar**, login breaks silently when it lapses.
- Optional: Enterprise applications → the app → Properties → **Assignment required = Yes**,
  then assign the users/groups who may log in. Otherwise every account in the tenant (including guests)
  is created as a user on first login.

## 7. Backups

The server is a single machine — a dead disk means the inventory is gone. Nightly dump, kept 30 days,
copied off the server (NAS or similar).

`/root/.my.cnf` (chmod 600):

```ini
[client]
user=inventur
password=<strong password>
```

`/etc/cron.d/inventur-backup`:

```
30 2 * * * root mkdir -p /var/backups/inventur && mysqldump --single-transaction --no-tablespaces inventur | gzip > /var/backups/inventur/inventur-$(date +\%F).sql.gz && find /var/backups/inventur -name '*.sql.gz' -mtime +30 -delete
```

Add a copy of `/var/backups/inventur` to the NAS, and test a restore into a scratch database once.

## 8. Updating

```bash
cd /var/www/medino-inventur
git pull
composer install --no-dev --optimize-autoloader
sudo -u www-data php yii migrate --interactive=0
sudo -u www-data php yii cache/flush-all
```

## Checklist after go-live

- [ ] `https://inventur.sth.care` opens on a phone in the office Wi-Fi without a certificate warning
- [ ] Microsoft login works and returns to the app
- [ ] No debug toolbar visible; `/gii` returns 404
- [ ] `/index-test.php` returns 403
- [ ] A backup file appears in `/var/backups/inventur` the next morning
- [ ] Client secret expiry is in the calendar
