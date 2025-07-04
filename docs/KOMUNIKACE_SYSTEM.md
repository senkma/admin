# Komunikační systém

## Přehled

Komunikační systém umožňuje automatické odesílání emailů s fakturami a dalšími informacemi. Systém je navržen tak, aby byl univerzální a podporoval externí SMTP servery.

## Funkce

### 1. Entita Communication
- **id**: Unikátní identifikátor
- **email**: Email příjemce
- **message**: Text zprávy
- **user**: Uživatel, který komunikaci vytvořil
- **supplier**: Volitelný dodavatel
- **client**: Volitelný klient
- **service**: Volitelná služba
- **invoice**: Volitelná faktura (jako příloha)
- **status**: Status komunikace (připraveno, vykonáno, zrušeno)
- **createdAt**: Datum vytvoření
- **sentAt**: Datum odeslání
- **errorMessage**: Chybová zpráva při neúspěšném odeslání

### 2. Automatické vytváření komunikací
Při vykonání služby s aktivním checkboxem "Odeslat email" se automaticky vytvoří komunikace s:
- Emailem klienta nebo dodavatele
- Automaticky generovanou zprávou
- Připojenou fakturou

### 3. Odesílání emailů
- **Manuální**: Tlačítko "Vykonat" u jednotlivých komunikací
- **Hromadné**: Tlačítko "Odeslat všechny připravené"
- **Automatické**: Pomocí cron jobu

## Konfigurace

### SMTP nastavení (.env)
```bash
# Pro development - lokální SMTP server
MAILER_DSN=smtp://localhost:1025

# Pro produkci - externí SMTP server
MAILER_DSN=smtp://username:password@smtp.server.com:587?encryption=tls&auth_mode=login

# Pro Gmail
MAILER_DSN=gmail://username:password@default

# Odesílatel
MAILER_FROM_EMAIL=noreply@example.com
MAILER_FROM_NAME="Fakturační systém"
```

### Cron job pro automatické odesílání
Přidejte do crontab pro denní odesílání v 9:00:
```bash
0 9 * * * docker exec admin-php-1 php bin/console app:send-communication-emails
```

## Použití

### 1. Vytvoření komunikace
- Přejděte na `/communications`
- Klikněte na "Vytvořit novou komunikaci"
- Vyplňte formulář
- Uložte

### 2. Nastavení služby pro automatické emaily
- Editujte službu
- Zaškrtněte "Odeslat email při vykonání služby"
- Při vykonání služby se automaticky vytvoří komunikace

### 3. Odesílání emailů
- **Jednotlivě**: Klikněte na "Vykonat" u komunikace
- **Hromadně**: Klikněte na "Odeslat všechny připravené"
- **Automaticky**: Nastavte cron job

## Console příkazy

### Odesílání emailů
```bash
# Odeslat všechny připravené komunikace
php bin/console app:send-communication-emails

# Odeslat pouze pro konkrétního uživatele
php bin/console app:send-communication-emails --user-id=1

# Omezit počet odeslaných emailů
php bin/console app:send-communication-emails --limit=10

# Dry-run (pouze zobrazí co by se odeslalo)
php bin/console app:send-communication-emails --dry-run
```

## Bezpečnost

- Komunikace jsou vázané na uživatele (každý uživatel vidí pouze své)
- Používá se Symfony Security Voter pro kontrolu přístupu
- Všechny akce vyžadují přihlášení

## Rozšíření

### Přidání nového SMTP serveru
1. Upravte MAILER_DSN v .env souboru
2. Případně přidejte specifické nastavení do config/packages/mailer.yaml

### Přidání PDF příloh
V EmailService::attachInvoicePdf() implementujte generování PDF faktury.

### Přidání dalších typů komunikací
Rozšiřte entitu Communication o další vztahy a upravte formuláře.

## Troubleshooting

### Emaily se neodesílají
1. Zkontrolujte MAILER_DSN v .env
2. Zkontrolujte logy: `docker exec admin-php-1 tail -f var/log/dev.log`
3. Otestujte připojení: vytvořte komunikaci a zkuste ji odeslat manuálně

### Chyby v cron jobu
1. Zkontrolujte, zda má cron job správná oprávnění
2. Zkontrolujte logy systému
3. Spusťte příkaz manuálně pro testování

## Status komunikací

- **Připraveno** (žlutá): Komunikace je připravena k odeslání
- **Vykonáno** (zelená): Email byl úspěšně odeslán
- **Zrušeno** (červená): Při odesílání došlo k chybě
