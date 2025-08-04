# MySQL Docker Configuration

## Problém s připojením k databázi

Pokud se objevuje chyba:
```
SQLSTATE[HY000] [1130] Host '172.19.0.x' is not allowed to connect to this MySQL server
```

## Řešení

1. **Docker Compose konfigurace** - Přidány parametry pro MySQL:
   - `--bind-address=0.0.0.0` - povoluje připojení ze všech IP adres
   - `--skip-name-resolve` - zrychluje připojení a vyhýbá se DNS problémům

2. **Inicializační skript** - `init.sql` zajišťuje:
   - Vytvoření uživatele `app_user` s oprávněními pro připojení z jakékoliv IP adresy
   - Správná oprávnění pro databázi `app_db`

## Nasazení změn

Po provedení změn je potřeba:

1. **Zastavit kontejnery:**
   ```bash
   cd .docker
   docker compose down
   ```

2. **Smazat databázový volume (POZOR: ztratíte data!):**
   ```bash
   docker volume rm admin_db_app
   ```

3. **Spustit kontejnery znovu:**
   ```bash
   docker compose up -d
   ```

4. **Spustit migrace:**
   ```bash
   docker exec -i admin-php-1 bash -c 'bin/console doctrine:migrations:migrate --no-interaction'
   ```

## Alternativní řešení bez ztráty dat

Pokud nechcete ztratit data, můžete:

1. Exportovat databázi před změnami
2. Provést změny
3. Importovat data zpět

Nebo použít SQL příkazy přímo v běžícím MySQL kontejneru:

```bash
docker exec -i admin-db-1 mysql -u root -p -e "
GRANT ALL PRIVILEGES ON app_db.* TO 'app_user'@'%';
FLUSH PRIVILEGES;
"
```
