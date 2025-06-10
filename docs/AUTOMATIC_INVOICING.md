# Automatické fakturování

Systém podporuje automatické generování faktur na základě definovaných služeb.

## Jak to funguje

1. **Vytvoření služby** - Definujete službu s parametry fakturace
2. **Automatická kontrola** - Systém denně kontroluje, které služby mají být fakturovány
3. **Generování faktur** - Automaticky vytvoří faktury podle definovaných kritérií

## Nastavení služby

### Základní parametry:
- **Název služby** - Popis služby
- **Dodavatel** - Kdo fakturu vystavuje
- **Klient** - Komu se faktura vystavuje
- **Bankovní účet** - Který účet se použije pro platbu

### Parametry fakturace:
- **Frekvence** - Jak často se fakturuje (měsíčně, čtvrtletně, ročně)
- **Den fakturace** - Který den v měsíci se vytvoří faktura (1-31)
- **Splatnost** - Počet dní od vytvoření do splatnosti
- **Datum začátku** - Od kdy se má služba fakturovat (volitelné)
- **Datum konce** - Do kdy se má služba fakturovat (volitelné)

### Položky služby:
- **Popis** - Co se fakturuje
- **Množství** - Kolik jednotek
- **Jednotka** - ks, hod, měs, atd.
- **Cena za jednotku** - Cena bez DPH
- **DPH** - Sazba DPH v procentech

## Automatické spouštění

### Cron job nastavení:

```bash
# Spouštět každý den v 6:00 ráno
0 6 * * * /path/to/project/scripts/generate-invoices.sh

# Nebo spouštět každou hodinu (pro testování)
0 * * * * /path/to/project/scripts/generate-invoices.sh
```

### Manuální spuštění:

```bash
# Spustit generování faktur
php bin/console app:generate-invoices

# Spustit v dry-run módu (pouze zobrazí co by se stalo)
php bin/console app:generate-invoices --dry-run

# Spustit s konkrétním datem
php bin/console app:generate-invoices --force-date=2024-01-15
```

## Logika rozhodování

Faktura se vytvoří, pokud:

1. **Služba je aktivní** (`is_active = true`)
2. **Je správný den v měsíci** (podle `invoice_day`)
3. **Služba už začala** (podle `start_date`)
4. **Služba ještě neskončila** (podle `end_date`)
5. **Od poslední faktury uplynula správná doba** (podle `frequency`)

### Příklady:

**Měsíční služba, 15. den:**
- Faktura se vytvoří 15. každého měsíce

**Čtvrtletní služba, 1. den:**
- Faktura se vytvoří 1. ledna, 1. dubna, 1. července, 1. října

**Roční služba, 31. den:**
- Faktura se vytvoří 31. prosince každého roku

## Monitoring

### Logy:
- Výsledky automatického generování se logují do `/var/log/invoice-generation.log`
- Chyby se logují do standardních Symfony logů

### Kontrola stavu:
```bash
# Zobrazit status všech služeb
php bin/console app:generate-invoices --dry-run

# Zkontrolovat logy
tail -f /var/log/invoice-generation.log
```

## Bezpečnost

- Pouze vlastník služby může vytvářet/editovat/mazat služby
- Automaticky generované faktury mají stejná oprávnění jako manuálně vytvořené
- Všechny operace se logují pro audit

## Troubleshooting

### Služba se nefakturuje:
1. Zkontrolujte, jestli je služba aktivní
2. Ověřte datum začátku/konce služby
3. Zkontrolujte, jestli je správný den v měsíci
4. Ověřte, jestli už nebyla faktura vytvořena v tomto období

### Chyby při generování:
1. Zkontrolujte logy Symfony
2. Ověřte, jestli existují všechny potřebné entity (dodavatel, klient, bankovní účet)
3. Zkontrolujte oprávnění k databázi

### Testování:
```bash
# Otestovat s konkrétním datem
php bin/console app:generate-invoices --force-date=2024-01-15 --dry-run

# Otestovat pouze jednu službu (přidat filtr do commandu)
```
