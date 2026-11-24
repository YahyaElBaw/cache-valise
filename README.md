# Gestion des Cache-Valises (Laravel 13)

Application web de gestion de fabrication par lots, charges, ventes/facturation, règlements, caisse espèces unique et rentabilité (Devise : Dinar Tunisien `DT`, 3 décimales).

## Prérequis
- PHP 8.3+ avec extensions `bcmath`, `pdo_mysql`, `mbstring`, `xml`, `gd`, `dom`
- Composer 2+
- MySQL / MariaDB
- Node.js & npm (uniquement pour recompiler les assets si besoin)

## Installation

1. Cloner ou placer le projet puis installer les dépendances :
   ```bash
   composer install
   ```
2. Configurer `.env` (base `cache_valises`) et `.env.testing` (base `cache_valises_test`), puis générer la clé si nécessaire :
   ```bash
   php artisan key:generate
   ```
3. Créer les bases MySQL `cache_valises` et `cache_valises_test`.
4. Exécuter les migrations et les seeders (compte admin + données de démonstration) :
   ```bash
   php artisan migrate:fresh --seed
   ```
5. Lancer le serveur local :
   ```bash
   php artisan serve
   ```

## Compte administrateur par défaut
- **E-mail** : `admin@cache-valises.tn`
- **Mot de passe** : `password`

*(Modifiable après connexion dans **Profil & Mot de passe** ; l'inscription publique est désactivée.)*

## Tests automatisés

Exécuter toute la suite de tests (incluant les 9 tests de recette + tests de sécurité) :
```bash
php artisan test
```

## Sauvegarde de la base de données
```bash
mysqldump -u root cache_valises > backup_cache_valises.sql
```
