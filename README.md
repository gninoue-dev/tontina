# Tontina — la tontine transparente 🇨🇮

**Tontina** est une application web Laravel pour gérer une tontine sans cahier : ordre de passage, échéances, cotisations en espèces ou en Mobile Money, pénalités de retard, rappels WhatsApp, et une page de transparence que chaque membre peut consulter depuis son téléphone.

## Le problème

En Côte d'Ivoire, la tontine est une façon très répandue d'épargner entre collègues, voisins, commerçantes ou membres d'une famille : chacun cotise le même montant à chaque tour, et un membre différent reçoit la cagnotte à chaque fois.

Mais la gestion repose souvent sur un cahier ou sur la mémoire de l'organisateur :
- **qui a payé ?** Les paiements arrivent en espèces, par Orange Money, MTN MoMo, Moov Money ou Wave, et on perd vite le fil ;
- **qui reçoit quand ?** L'ordre de passage se discute ou s'oublie ;
- **les retards** se règlent à l'amiable… ou se transforment en disputes ;
- **les membres doivent faire confiance** sans pouvoir vérifier.

## Ce que fait Tontina

| | |
|---|---|
| 👥 **Membres** | Nom et numéro ivoirien à 10 chiffres. Les formats courants (`07 07 12 34 56`, `+225 05.05…`) sont normalisés automatiquement. |
| 🎲 **Ordre de passage** | Saisi à la main ou tiré au sort, puis verrouillé au démarrage. |
| 📅 **Tours planifiés** | Un tour par membre, chaque semaine, toutes les deux semaines ou chaque mois. Les fins de mois sont gérées : le 31 janvier est suivi du 28 février. |
| 💵 **Cotisations** | Espèces, Orange Money, MTN MoMo, Moov Money ou Wave, avec la référence de la transaction. Un même membre ne peut pas cotiser deux fois pour un tour. |
| ⏰ **Pénalités** | Appliquées automatiquement aux paiements arrivés après l'échéance. |
| 💬 **Rappels WhatsApp** | Un clic ouvre WhatsApp avec un message de rappel déjà rédigé pour le membre en retard. Pas de SMS payant. |
| 💰 **Remise de la cagnotte** | Possible seulement quand tout le monde a cotisé. Le tour est ensuite figé, et la tontine se termine après le dernier tour. |
| 🔍 **Transparence** | Chaque tontine a un lien secret à partager dans le groupe. Il montre l'ordre de passage et qui a cotisé, sans les numéros de téléphone. |

**Tontina ne touche jamais à l'argent.** L'application sert de registre partagé : les paiements se font comme d'habitude, et l'organisateur les enregistre.

## Démarrer

Prérequis : PHP 8.3+ (extensions `pdo_sqlite`, `intl`, `mbstring`), Composer et Node.js.

```bash
git clone https://github.com/gninoue-dev/tontina.git
cd tontina
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed      # base SQLite + données de démonstration
npm install && npm run build
php artisan serve
```

Ouvre http://localhost:8000 :
- compte de démonstration : `demo@tontina.ci` / `password` ;
- page de transparence de la tontine de démonstration : http://localhost:8000/t/demo-adjame

## Tests

```bash
php artisan test
```

34 tests (134 assertions) : inscription et connexion (dont la limitation des tentatives), création et validation, normalisation des numéros, tirage au sort, planification des tours, pénalités, double paiement, remise de la cagnotte, fin de la tontine, autorisations entre organisateurs, page publique sans numéros. Ils tournent aussi sur GitHub Actions.

## Architecture

- **Laravel 13**, Blade, Tailwind CSS 4, SQLite par défaut (MySQL ou PostgreSQL possibles via `.env`).
- `app/Actions` : les règles métier (`StartTontine`, `DrawPayoutOrder`, `RecordContribution`, `PayOutRound`), chacune testée à travers les pages.
- `app/Policies/TontinePolicy` : seul l'organisateur gère sa tontine ; les routes imbriquées vérifient que le membre, le tour ou le paiement appartient bien à la tontine de l'URL.
- Montants en entiers FCFA, affichés à la française (`25 000 FCFA`).

## Prochaines étapes

- [ ] Plusieurs gestionnaires par tontine (trésorier, secrétaire)
- [ ] Export PDF du relevé de fin de tontine
- [ ] Tontines « à enchères », où l'ordre de passage se négocie
- [ ] Application mobile (PWA hors ligne)

## Licence
MIT — © 2026 Dev Gninoue
