# 📊 Invoice System - Fakturační systém

Webová aplikace pro správu faktur, klientů, dodavatelů a automatické generování opakujících se faktur.

## 🚀 Technologie

- **Backend:** Symfony 6.2, PHP 8.2
- **Database:** MySQL 8.0
- **Webserver:** Nginx + PHP-FPM (v jednom kontejneru)
- **Deployment:** Docker, Coolify
- **PDF generování:** DomPDF

## ✨ Hlavní funkce

### 📋 Správa entit
- **Faktury** - Vytváření, editace a zobrazení faktur
- **Klienti** - Správa klientů s kontaktními údaji
- **Dodavatelé** - Správa dodavatelů (vaše firmy)
- **Služby** - Definice opakujících se služeb pro automatickou fakturaci
- **Bankovní účty** - Správa bankovních účtů dodavatelů

### 🔄 Automatická fakturace
- Definice opakujících se služeb (měsíční, čtvrtletní, roční)
- Automatické generování faktur podle plánu
- Nastavitelný den fakturace a splatnost
- Manuální nebo automatické spouštění přes cron

### 📨 Komunikace
- Systém pro odesílání faktur emailem
- Šablony emailových zpráv
- Sledování odeslané komunikace

### 📄 Export
- Generování PDF faktur
- QR kódy pro platby

## 📦 Instalace

### Lokální vývoj

1. **Klonovat repozitář:**
```bash
git clone <repository-url>
cd admin
```

2. **Nakonfigurovat prostředí:**
Zkopírujte `.env` a upravte databázové přístupy podle potřeby.

3. **Spustit Docker kontejnery (s lokální databází):**
```bash
cd .docker
docker compose --profile local up -d
```

**Poznámka:** `--profile local` spustí lokální MySQL databázi. Bez tohoto parametru se databáze nespustí (použije se pro produkci s Coolify managed DB).

4. **Nainstalovat závislosti:**
```bash
docker exec -it invoice-php composer install
```

5. **Připravit databázi:**
```bash
# Vytvořit databázi a schéma
docker exec -it invoice-php composer prepare-database

# Nebo použít migrace
docker exec -it invoice-php php bin/console doctrine:migrations:migrate
```

6. **Přístup k aplikaci:**
- Web: http://localhost (nebo dle konfigurace portů)

### Produkční nasazení (Coolify)

Projekt je připraven pro deployment přes Coolify:

1. V Coolify vytvořte novou aplikaci z Git repository
2. Nastavte environment variables (DATABASE_URL, APP_SECRET, atd.)
3. Coolify automaticky buildne a deployne aplikaci
4. Po deploy ručně spusťte přípravu databáze:
```bash
docker exec -it <container> composer prepare-database
```

## 🛠️ Užitečné příkazy

### Makefile příkazy
```bash
make up              # Spustit kontejnery
make down            # Zastavit kontejnery
make restart         # Restartovat kontejnery
make shell           # Přístup do PHP kontejneru
make logs            # Zobrazit logy
make prepare-db      # Připravit databázi
make migrate         # Spustit migrace
make cache-clear     # Vymazat cache
```

### Symfony příkazy
```bash
# Automatické generování faktur
php bin/console app:generate-invoices

# Dry-run (pouze ukázat co by se stalo)
php bin/console app:generate-invoices --dry-run

# S konkrétním datem
php bin/console app:generate-invoices --force-date=2024-01-15
```

## 📚 Dokumentace

- [Automatická fakturace](docs/AUTOMATIC_INVOICING.md)
- [Komunikační systém](docs/KOMUNIKACE_SYSTEM.md)

## 🐳 Docker architektura

- **php kontejner** - Kombinovaný kontejner s PHP-FPM + Nginx (supervisord)
- **db kontejner** - MySQL 8.0 databáze

### Volumes
- `symfony_app_var` - Cache a logy
- `symfony_app_vendor` - Composer dependencies
- `db_app` - MySQL data

## 🔐 Bezpečnost

- Přihlašování uživatelů pomocí Symfony Security
- Role-based access control
- Multi-tenancy - každý uživatel vidí pouze své data

## 📝 Licence

Proprietary
