#!/bin/bash

# Script pro automatické generování faktur
# Spouští se denně přes cron job

# Nastavení cesty k projektu
PROJECT_DIR="/var/www/symfony"

# Přejít do adresáře projektu
cd $PROJECT_DIR

# Spustit command pro generování faktur
php bin/console app:generate-invoices

# Logovat výsledek
echo "$(date): Automatické generování faktur dokončeno" >> /var/log/invoice-generation.log
