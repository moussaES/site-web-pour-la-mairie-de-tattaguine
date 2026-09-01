# 🏛️ SUNU TATTAGUINE — Portail Citoyen et d'Information Municipale

> **Commune de Tattaguine (Région de Fatick, Sénégal)**  
> *Plateforme numérique développée dans le cadre du Programme d'Appui à la Transparence et à l'Information Publique (PATIP-JF).*

---

## 📌 Présentation du Projet

**SUNU TATTAGUINE** est une plateforme web moderne conçue pour rapprocher l'administration municipale de Tattaguine de ses citoyens. Elle permet de diffuser les actualités locales, de publier les actes administratifs et délibérations, et d'offrir aux habitants un espace interactif pour transmettre leurs requêtes, réagir aux publications et recevoir les réponses officielles de la Mairie.

---

## 🚀 Technologies & Stack Technique

Le projet repose sur une architecture robuste, légère et hautement sécurisée, construite sans dépendance lourde afin d'assurer des performances optimales et une maintenance facilitée.

### 💻 Backend & Architecture MVC
* **PHP 8+ (Architecture MVC & POO)** : Structure Modèle-Vue-Contrôleur personnalisée et modulaire.
* **Routeur HTTP Dynamique** (`core/Router.php`) : Gestion propre des URLs conviviales (`/actualites`, `/documents`, `/mon-espace`, `/admin/messages`).
* **PDO / MySQL & MariaDB** : Requêtes préparées luttant contre les injections SQL, transactions et auto-migration de la base de données (`database/Migrate.php`).
* **Sécurité CSRF & Hachage** (`core/Security.php`) : Jetons anti-CSRF sur tous les formulaires et hachage fort `BCRYPT` pour les mots de passe.

---

### 🔑 Authentification & Gestion des Identités (RBAC)
* **Google OAuth 2.0 / GIS (Google Identity Services)** : Inscription et connexion en 1 clic via le SDK Google Identity (`accounts.google.com/gsi/client`) et validation sécurisée des jetons JWT.
* **Contrôle d'Accès basé sur les Rôles (RBAC)** :
  * `super_admin` & `redacteur` : Accès complet au panneau d'administration **SUNU TATTAGUINE**.
  * `citoyen` (Rôle 3) : Accès à l'espace citoyen personnel (`/mon-espace`).

---

### 📧 Messaging Transactionnel & Notifications Web Push
* **API Resend (`core/Mailer.php`)** : Service d'envoi d'e-mails transactionnels haute livrabilité (API REST cURL). Expédition automatique d'un e-mail d'information au citoyen dès que la Mairie répond à son commentaire ou à son message.
* **Firebase Cloud Messaging (FCM) Web Push (`core/FirebaseMessaging.php`)** : Notifications système en temps réel sur les écrans ordinateurs et mobiles (Android/iOS) via un Service Worker natif (`firebase-messaging-sw.js`).
* **Intégration WhatsApp Web & Mobile (`wa.me`)** : Génération automatique de liens de réponse WhatsApp formatés avec le code pays Sénégal (`+221`).
* **Système de Cloche de Notification (Header)** : Badge interactif `🔔 1` en surbrillance rouge s'affichant dans la barre de navigation dès qu'une nouvelle réponse municipale est disponible.

---

### 📱 Interface Utilisateur & Expérience Citoyenne (Frontend)
* **Design Responsive & Charte Nationale** : Interface moderne respectant les couleurs officielles du Sénégal (Vert Mairie `#00853F`, Jaune Or, Rouge).
* **Deep-Linking & Redirection Intelligente** : Redirection automatique des citoyens vers l'article et l'ancre exacte de commentaire (`#commentaires`) après leur connexion.
* **Confidentialité & Protection des données** : Nettoyage des données sensibles dans le profil citoyen pour protéger la vie privée des utilisateurs.

---

### 🏛️ Espace Administration (Back-Office)
* **Tableau de bord dynamique** : Statistiques en temps réel sur la fréquentation, les articles publiés, les commentaires en attente et les messages citoyens.
* **Gestion des Messages Citoyens (`/admin/messages`)** : Lecture modale, réponses directes sur le site, envoi par e-mail, ouverture Gmail Webmail et réponse WhatsApp.
* **Modération des Commentaires (`/admin/comments`)** : Validation, rejet, et publication de la Réponse Officielle de la Mairie sous le commentaire.
* **Gestion Documentaire & Actualités** : Téléversement d'images à la une, gestion des catégories d'actualités et publication des délibérations PDF.

---

## 🛠️ Configuration & Installation Locale

### 1. Prérequis
* **XAMPP** (ou Apache 2.4+ avec PHP 8.0+ et MariaDB / MySQL).
* Extension PHP `curl`, `pdo_mysql`, et `openssl` activées.

### 2. Base de données
1. Créez une base de données nommée `tattaguine_db`.
2. L'auto-migreur intégré (`database/Migrate.php`) créera et mettra à jour automatiquement toutes les tables requises au premier chargement.

### 3. Fichier de Configuration
Éditez le fichier [`config/config.php`](file:///c:/xampp/htdocs/site%20web%20mairie/config/config.php) pour configurer les clés d'API :

```php
// Google OAuth 2.0 Client ID
define('GOOGLE_CLIENT_ID', 'votre_google_client_id.apps.googleusercontent.com');

// API Resend (https://resend.com)
define('RESEND_API_KEY', 'votre_cle_api_resend');
define('RESEND_FROM_EMAIL', 'Sunu Tattaguine <onboarding@resend.dev>');

// Firebase Cloud Messaging (https://console.firebase.google.com)
define('FIREBASE_API_KEY', 'votre_api_key_firebase');
define('FIREBASE_PROJECT_ID', 'sunu-tattaguine');
define('FIREBASE_MESSAGING_SENDER_ID', 'votre_sender_id');
define('FIREBASE_APP_ID', 'votre_app_id');
define('FIREBASE_VAPID_KEY', 'votre_vapid_key');
define('FIREBASE_SERVER_KEY', 'votre_server_key');
```

---

## 🔐 Guide de Configuration Google OAuth 2.0 (Google Cloud Console)

Pour activer l'authentification Google Sign-In sans erreur `origin_mismatch` (Erreur 400), suivez cette procédure :

1. Accédez à la **Console Google Cloud** : [https://console.cloud.google.com/apis/credentials](https://console.cloud.google.com/apis/credentials).
2. Dans le menu de gauche **Google Auth Platform**, cliquez sur **Clients**.
3. Sélectionnez votre **ID client OAuth 2.0** dans la liste.
4. Renseignez les sections de configuration d'origine :

#### 🌐 Origines JavaScript autorisées (*Authorized JavaScript origins*) :
* `https://sunu-tattaguine.vercel.app`
* `http://localhost`
* `http://127.0.0.1`

#### 🔄 URI de redirection autorisés (*Authorized redirect URIs*) :
* `https://sunu-tattaguine.vercel.app/auth/google`
* `https://sunu-tattaguine.vercel.app/public/auth/google`
* `http://localhost/site%20web%20mairie/public/auth/google`

5. Cliquez sur **Enregistrer** (*Save*).

---

## 🌐 Routage & Déploiement Vercel (`public/`)

Le projet est configuré pour gérer de manière transparente les URLs avec ou sans le préfixe `/public` sur Vercel et serveurs Apache local (XAMPP).

* **Routage dynamique (`core/Router.php`)** : Prise en charge automatique des requêtes `/actualites/...` et `/public/actualites/...`.
* **Fichiers statiques (`vercel.json`)** : Routage direct des assets CSS/JS (`/assets/...` & `/public/assets/...`) et téléversements d'images/vidéos (`/uploads/...` & `/public/uploads/...`).

---

## 📄 Licence & Crédits

* **Éditeur** : Mairie de la Commune de Tattaguine (Région de Fatick, Sénégal).
* **Programme** : PATIP-JF.
* **Nom de l'application** : **SUNU TATTAGUINE**.