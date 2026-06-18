# I.sAunny — version Symfony 7

Migration complète du blog PHP natif vers **Symfony 7** (PHP 8.2+).

## Schéma (MCD respecté)

- **T_ARTICLE** (id_article, titre, image, description, date) — `appartient` à 1 catégorie
- **T_CATEGORIE** (id_categorie, nom) — `contient` 0..n articles
- **T_COMMENTAIRE** (id_commentaire, contenu, date_commentaire, statut) — lié à 1 article + 1 membre
- **T_MEMBRE** (id_membre, email, password, role, pseudo) — `envoie` 0..n commentaires

Tables et colonnes d'origine **conservées** via les attributs `#[ORM\Table]` /
`#[ORM\Column(name: ...)]`. Tu peux brancher la même base MySQL sans rien recréer.

## Correspondance PHP natif → Symfony

| PHP natif | Symfony 7 |
|-----------|-----------|
| `config/database.php` (PDO) | Doctrine ORM + entités/repositories |
| `config/mail.php` | `App\Service\MailService` + Symfony Mailer |
| Vérifs `$_SESSION["user"]` / `role` | Firewall + `access_control` + `#[IsGranted]` |
| `password_hash` / `password_verify` | Hasher `auto` (compatible bcrypt existant) |
| `htmlspecialchars(...)` | Échappement automatique Twig |
| Requêtes SQL en dur | Repositories Doctrine |
| Includes header/footer | Layout `base.html.twig` + partials |

## Routes

### Public
| URL | Route |
|-----|-------|
| `/` | `app_home` |
| `/articles` | `app_articles` |
| `/article/{id}` | `app_article_show` (+ formulaire commentaire) |
| `/categories` | `app_categories` |
| `/categorie/{id}` | `app_categorie_show` |
| `/contact` | `app_contact` |
| `/a-propos` | `app_about` |
| `/mentions-legales` | `app_mentions` |
| `/politique-de-confidentialite` | `app_politique` |
| `/connexion` `/inscription` `/deconnexion` | `app_login` / `app_register` / `app_logout` |
| `/compte` | `app_account` (ROLE_USER) |

### Admin (ROLE_ADMIN)
| URL | Route |
|-----|-------|
| `/admin` | `app_admin_dashboard` |
| `/admin/articles` (+ creer / {id}/modifier / {id}/supprimer) | `app_admin_article*` |
| `/admin/categories` (+ creer / {id}/modifier / {id}/supprimer) | `app_admin_categor*` |
| `/admin/membres` | `app_admin_members` |
| `/admin/commentaires` (+ {id}/valider / {id}/supprimer) | `app_admin_comment*` |

## Installation

```bash
composer install
cp .env.local.example .env.local      # règle DATABASE_URL, APP_SECRET, MAILER_DSN
php bin/console doctrine:schema:validate
symfony serve                          # ou : php -S localhost:8000 -t public
```

## Base de données : deux cas

### A) Tu gardes ta base existante (projet PHP natif)
Tes tables existent déjà. Ne lance pas la migration ; marque-la comme déjà appliquée :

```bash
php bin/console doctrine:migrations:version --add --all
```

Vérifie ensuite que le mapping colle :

```bash
php bin/console doctrine:schema:validate
```

### B) Tu recrées la base de zéro (nouvelle machine, livraison)

```bash
php bin/console doctrine:database:create        # crée la base "isaunny" si absente
php bin/console doctrine:migrations:migrate      # crée les 4 tables + clés étrangères
```

La migration `migrations/Version20260101000000.php` crée `T_CATEGORIE`, `T_MEMBRE`,
`T_ARTICLE`, `T_COMMENTAIRE` (InnoDB, utf8mb4) avec les contraintes du MCD.

### Créer un premier administrateur
Le projet d'origine n'avait pas d'interface de création d'admin (le rôle se met en
base). Même principe ici :

1. Inscris-toi via `/inscription` (rôle `membre` par défaut).
2. Passe ce compte en admin :

```sql
UPDATE T_MEMBRE SET role = 'admin' WHERE email = 'ton-email@example.com';
```

## À faire de ton côté

1. Copier tes images dans `public/img/` : `logo.png`, `accueil.png`, `articles.png`,
   `categorie.png`, `contact.png`, `a-propos.png`, `mentions.png`, `rgpd.png`,
   ainsi que les images d'articles. Le `accueil.css` réel est déjà intégré.
2. Renseigner un `APP_SECRET` aléatoire et un `MAILER_DSN` valide.

## Améliorations de sécurité vs version PHP

- **CSRF** : suppressions, validations (commentaires/catégories/articles) passent
  en POST + jeton CSRF. Avant, c'étaient des liens GET (`delete.php?id=...`),
  vulnérables au CSRF.
- La page admin « membres » **n'affiche plus le hash des mots de passe** (donnée
  sensible inutilement exposée dans le code d'origine).
- Échappement Twig + requêtes paramétrées Doctrine (anti-XSS / anti-injection SQL).
- Comme dans l'original, le commentaire posté part en statut `en_attente` et n'est
  visible qu'après validation par un admin.
