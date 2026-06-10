# Cinema Ticketing System

Application web de réservation de billets de cinéma, développée en PHP (Architecture MVC), MySQL, HTML et CSS.

## 📋 Prérequis
- Un serveur web local (XAMPP, WAMP, MAMP, ou serveur Apache/Nginx)
- PHP 7.4 ou supérieur
- MySQL ou MariaDB

## 🚀 Guide de Déploiement

### 1. Installation du projet
1. Placez le dossier `Cinema` dans le répertoire racine de votre serveur web :
   - Pour XAMPP : `C:\xampp\htdocs\Cinema`
   - Pour WAMP : `C:\wamp64\www\Cinema`
   - Pour MAMP : `/Applications/MAMP/htdocs/Cinema`

### 2. Configuration de la Base de Données
1. Lancez votre serveur local (Apache et MySQL).
2. Ouvrez **phpMyAdmin** (généralement accessible via `http://localhost/phpmyadmin`).
3. Importez le fichier **`cinema.sql`** qui se trouve à la racine du projet.
   *Ce script va créer automatiquement la base de données `cinema_db`, toutes les tables (users, films, rooms, schedules, seats, reservations), les contraintes nécessaires pour éviter le surbooking, et un compte Administrateur.*

### 3. Connexion à la Base de Données
Si vos identifiants MySQL locaux sont différents des identifiants par défaut (`root` et aucun mot de passe), vous devez les modifier dans le projet :
1. Ouvrez le fichier `config/Database.php`.
2. Modifiez la méthode `getConnection()` avec vos identifiants :
   ```php
   $this->conn = new PDO("mysql:host=localhost;dbname=cinema_db", "VOTRE_UTILISATEUR", "VOTRE_MOT_DE_PASSE");
   ```

### 4. Lancement de l'Application
1. Ouvrez votre navigateur web.
2. Accédez à l'URL suivante : [http://localhost/Cinema](http://localhost/Cinema)
3. L'application vous redirigera automatiquement vers la page de connexion.

## 🔐 Comptes de test
Le script SQL crée automatiquement un compte avec les droits d'administration pour tester l'application :

* **Administrateur :**
  * **Username :** `admin`
  * **Mot de passe :** `password`

Vous pouvez bien sûr créer de nouveaux comptes utilisateurs depuis la page d'inscription (Register).

## 🛠 Note sur les spécifications
- Le projet remplit toutes les exigences fonctionnelles des Sprints 1 à 5 du cahier des charges (Anti-surbooking, Session Sécurisée, Hashage de mots de passe, validations d'âge, contraintes de cohérence SQL).
- **Déviation volontaire :** La spécification initiale demandait une colonne `type` (Standard, VIP) dans la table `seats`. Cette fonctionnalité a été retirée à la demande du client pour unifier les places. Le code et la base de données reflètent cette optimisation.
