# VENOM CRM — Boilerplate

[![CI](https://github.com/gmaxsoft/symfony_crm_boilerplate/actions/workflows/ci.yml/badge.svg)](https://github.com/gmaxsoft/symfony_crm_boilerplate/actions/workflows/ci.yml)

System CRM zbudowany w architekturze **Modular Monolith** z oddzielonym backendem (REST API) i frontendem (SPA).

![VENOM CRM — podgląd interfejsu](preview.webp)

---

## Stack technologiczny

### Backend
| Technologia | Wersja | Rola |
|---|---|---|
| PHP | 8.3 | Runtime |
| Symfony | 7.4 LTS | Framework PHP |
| Doctrine ORM | 3.6 | Warstwa bazy danych (ORM) |
| Doctrine Migrations | 3.7 | Wersjonowanie schematu DB |
| MySQL | 8.0+ | Baza danych (produkcja) |
| SQLite | 3.x | Baza danych (testy) |
| LexikJWT Bundle | 3.2 | Uwierzytelnianie JWT |
| Symfony Security | 7.4 | Firewalle, access control |
| NelmioCors Bundle | 2.6 | Obsługa CORS |
| PHPUnit | 12 | Testy jednostkowe i integracyjne |
| Psalm | 6.x | Statyczna analiza PHP |
| PHP CS Fixer | 3.x | Formatowanie kodu PHP |

### Frontend
| Technologia | Wersja | Rola |
|---|---|---|
| Vue.js | 3.x | Framework SPA |
| TypeScript | 5.x | Typowanie statyczne |
| Vite | 8.x | Bundler / dev server |
| Vuetify | 3.x | Biblioteka komponentów UI |
| Vue Router | 4.x | Routing SPA |
| Pinia | 2.x | State management |
| Axios | 1.x | Klient HTTP |
| Playwright | 1.x | Testy E2E (end-to-end) |
| ESLint | 9.x | Linting TypeScript/Vue |
| MDI Icons | — | Zestaw ikon Material Design |

---

## Architektura

Projekt stosuje wzorzec **Modular Monolith** — jedna aplikacja backendowa podzielona na niezależne moduły domenowe. Moduły komunikują się przez serwisy i zdarzenia domenowe, bez bezpośrednich zależności między kontrolerami.

```
symfony_crm_boiler/
├── backend/                     ← Symfony REST API
│   ├── config/
│   │   ├── packages/
│   │   │   ├── doctrine.yaml
│   │   │   ├── security.yaml    ← JWT firewalle
│   │   │   └── lexik_jwt_authentication.yaml
│   │   ├── routes.yaml          ← Auto-discovery kontrolerów
│   │   └── services.yaml        ← DI z podziałem per moduł
│   ├── migrations/              ← Doctrine Migrations
│   ├── tests/
│   │   ├── Unit/                ← Testy jednostkowe (PHPUnit)
│   │   └── Integration/         ← Testy integracyjne (PHPUnit + SQLite)
│   └── src/
│       ├── Kernel.php
│       ├── Modules/
│       │   ├── Auth/            ← Logowanie + /api/auth/me
│       │   ├── Dashboard/       ← Statystyki /api/dashboard
│       │   ├── Customers/       ← CRUD /api/customers
│       │   ├── Access/          ← CRUD /api/access/roles
│       │   └── Admin/           ← CRUD /api/admin/users
│       └── Shared/
│           ├── Command/         ← app:db:seed
│           ├── Contract/        ← ModuleInterface, AbstractApiController
│           ├── Event/           ← DomainEvent (base)
│           ├── Exception/       ← DomainException, NotFoundException
│           └── ValueObject/     ← Email (przykład)
│
└── frontend/                    ← Vue 3 SPA
    ├── e2e/                     ← Testy E2E Playwright
    │   ├── helpers/auth.ts      ← loginViaApi, login, logout
    │   ├── auth.spec.ts         ← Testy logowania i sesji
    │   ├── customers.spec.ts    ← Testy CRUD kontrahentów
    │   └── admin.spec.ts        ← Testy użytkowników i ról
    └── src/
        ├── api/                 ← Warstwa HTTP (axios per moduł)
        ├── composables/         ← useNotify (snackbar)
        ├── layouts/
        │   ├── AuthLayout.vue   ← Layout strony logowania
        │   └── AdminLayout.vue  ← Sidebar + TopBar + router-view
        ├── modules/
        │   ├── Auth/LoginPage.vue
        │   ├── Dashboard/DashboardPage.vue
        │   ├── Customers/       ← CustomersPage + CustomerDialog
        │   ├── Access/          ← AccessPage + RoleDialog
        │   └── Admin/           ← AdminPage + UserDialog
        ├── router/index.ts      ← Vue Router + navigation guard JWT
        ├── stores/auth.ts       ← Pinia: login, logout, fetchMe
        └── types/index.ts       ← TypeScript interfejsy
```

### Struktura modułu backendowego

Każdy moduł w `src/Modules/{NazwaModułu}/` zawiera:

```
{ModuleName}/
├── Controller/     ← HTTP — atrybuty #[Route], zwracają JSON
├── Entity/         ← Encje Doctrine (wykluczone z DI)
├── Repository/     ← ServiceEntityRepository z metodami domenowymi
├── Service/        ← Logika biznesowa (wstrzykiwana przez DI)
├── DTO/            ← Obiekty transferu danych (wykluczone z DI)
├── Event/          ← Zdarzenia domenowe
├── EventListener/  ← Nasłuchiwacze zdarzeń
└── {Name}Module.php ← Implementacja ModuleInterface
```

---

## Baza danych

### Tabele

| Tabela | Moduł | Opis |
|---|---|---|
| `roles` | Access | Role użytkowników |
| `users` | Admin | Konta użytkowników CRM |
| `customers` | Customers | Kontrahenci |

### Domyślne role

| Rola | Opis |
|---|---|
| Administrator | Pełny dostęp do systemu |
| Pracownik administracyjny | Dostęp do kontrahentów i raportów |
| Handlowiec | Dostęp do własnych kontrahentów |

---

## Wymagania

- PHP **8.3+** z rozszerzeniami: `pdo_mysql`, `openssl`, `mbstring`, `intl`
- **Composer** 2.x
- **MySQL** 8.0+
- **Node.js** 18+ i **npm** 9+
- (opcjonalnie) [Laravel Herd](https://herd.laravel.com/) lub inny serwer PHP

---

## Uruchomienie projektu

### 1. Klonowanie repozytorium

```bash
git clone https://github.com/gmaxsoft/symfony_crm_boilerplate.git
cd symfony_crm_boilerplate
```

### 2. Backend — konfiguracja

```bash
cd backend
composer install --ignore-platform-req=ext-redis
```

#### Skonfiguruj zmienne środowiskowe

Skopiuj plik `.env` do `.env.local` i uzupełnij dane:

```bash
cp .env .env.local
```

Edytuj `.env.local`:

```dotenv
# Zmień dane dostępowe do MySQL
DATABASE_URL="mysql://USER:PASSWORD@127.0.0.1:3306/venom_crm?serverVersion=8.0&charset=utf8mb4"

# Opcjonalnie zmień hasło JWT
JWT_PASSPHRASE=twoje_tajne_haslo
```

#### Wygeneruj klucze JWT

```bash
# Na Linux/macOS:
php bin/console lexik:jwt:generate-keypair

# Na Windows (Herd) — ustaw zmienną środowiskową i uruchom:
# $env:OPENSSL_CONF = "C:\Users\TWOJ_USER\.config\herd\bin\php83\extras\ssl\openssl.cnf"
php bin/console lexik:jwt:generate-keypair
```

#### Utwórz bazę danych i uruchom migracje

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

#### Wypełnij bazę danymi startowymi (seed)

```bash
php bin/console app:db:seed
```

Seed tworzy domyślne role oraz konto administratora:

| Parametr | Wartość domyślna |
|---|---|
| E-mail | `admin@venom.pl` |
| Hasło | `Admin123!` |

Możesz zmienić dane logowania:

```bash
php bin/console app:db:seed --admin-email=twoj@email.pl --admin-password=TwojeHaslo
```

Aby wyczyścić i ponownie wypełnić bazę:

```bash
php bin/console app:db:seed --fresh
```

#### Uruchom serwer deweloperski

> ⚠️ **Ważne:** Poniższe komendy uruchamiaj zawsze z katalogu `backend/` — nie z głównego katalogu projektu!

```bash
cd backend

# Symfony CLI (zalecane)
symfony server:start

# Lub wbudowany serwer PHP (alternatywa bez Symfony CLI)
php -S 0.0.0.0:8000 -t public
```

Backend dostępny pod: **http://localhost:8000**

---

### 3. Frontend — konfiguracja

```bash
cd ../frontend
npm install
```

#### Uruchom serwer deweloperski

> ⚠️ **Ważne:** Poniższe komendy uruchamiaj z katalogu `frontend/` — nie z głównego katalogu projektu!

```bash
npm run dev
```

Frontend dostępny pod: **http://localhost:5173**

---

### 4. Pierwsze logowanie

1. Otwórz **http://localhost:5173** — aplikacja przekieruje na `/login`
2. Zaloguj się danymi z seeda:
   - **E-mail:** `admin@venom.pl`
   - **Hasło:** `Admin123!`
3. Zmień hasło po pierwszym logowaniu przez panel **Użytkownicy** → edycja konta

---

## API — endpointy

### Autoryzacja

| Metoda | Endpoint | Opis | Dostęp |
|---|---|---|---|
| POST | `/api/auth/login` | Logowanie, zwraca token JWT | Publiczny |
| GET | `/api/auth/me` | Dane zalogowanego użytkownika | JWT |

**Przykład logowania:**

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@venom.pl","password":"Admin123!"}'
```

**Odpowiedź:**
```json
{ "token": "eyJ0eXAiOiJKV1QiLCJhbGci..." }
```

**Użycie tokena w kolejnych żądaniach:**
```bash
curl http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1Qi..."
```

### Endpointy modułów (wymagają JWT)

| Moduł | Endpoint | Metody |
|---|---|---|
| Dashboard | `/api/dashboard` | GET |
| Customers | `/api/customers` | GET, POST |
| Customers | `/api/customers/{id}` | GET, PUT, DELETE |
| Access | `/api/access/roles` | GET, POST |
| Access | `/api/access/roles/{id}` | GET, PUT, DELETE |
| Admin | `/api/admin/users` | GET, POST |
| Admin | `/api/admin/users/{id}` | GET, PUT, DELETE |

---

## Testy

### Testy jednostkowe i integracyjne (PHPUnit)

Projekt zawiera **testy jednostkowe** i **integracyjne** backendu napisane w PHPUnit 12.

#### Środowisko testowe

| Narzędzie | Rola |
|---|---|
| PHPUnit 12 | Framework testowy |
| `symfony/test-pack` | WebTestCase, BrowserKit |
| `dama/doctrine-test-bundle` | Rollback transakcji po każdym teście integracyjnym |
| SQLite | Izolowana baza dla testów — nie wymaga MySQL |

Każdy test integracyjny jest automatycznie owijany w transakcję i wycofywany po zakończeniu — baza pozostaje czysta między testami.

#### Struktura testów

```
backend/tests/
├── Unit/
│   ├── Entity/
│   │   ├── RoleTest.php          # Stałe, gettery/settery, kolekcja użytkowników
│   │   ├── UserTest.php          # fullName, normalizacja ról ROLE_*, lifecycle
│   │   └── CustomerTest.php      # Pola opcjonalne, domyślny status, onPreUpdate
│   └── Service/
│       ├── RoleServiceTest.php   # CRUD, delete z użytkownikami → LogicException
│       ├── CustomerServiceTest.php  # Paginacja, opiekun, NotFoundException
│       └── UserServiceTest.php   # Hashowanie hasła, walidacja roli
└── Integration/
    ├── ApiTestCase.php           # Klasa bazowa: helpers JWT, tworzenie danych
    ├── Auth/
    │   └── AuthApiTest.php       # POST /login, GET /me (z/bez tokenu)
    ├── Access/
    │   └── RoleApiTest.php       # CRUD ról, 409 przy usuwaniu roli z użytkownikami
    ├── Admin/
    │   └── UserApiTest.php       # CRUD użytkowników, walidacja 422/404
    ├── Customers/
    │   └── CustomerApiTest.php   # CRUD, paginacja, wyszukiwanie
    └── Dashboard/
        └── DashboardApiTest.php  # Statystyki, liczniki, autoryzacja
```

#### Uruchamianie testów PHPUnit

> Wszystkie komendy wykonuj z katalogu `backend/`

```bash
# Inicjalizacja bazy testowej (pierwsze uruchomienie lub po zmianie encji)
php bin/console doctrine:schema:create --env=test

# Wszystkie testy (jednostkowe + integracyjne)
php bin/phpunit

# Tylko testy jednostkowe
php bin/phpunit --testsuite Unit

# Tylko testy integracyjne
php bin/phpunit --testsuite Integration

# Konkretna klasa
php bin/phpunit tests/Unit/Entity/UserTest.php

# Konkretna metoda
php bin/phpunit --filter testGetFullName

# Z raportem pokrycia kodu (wymaga Xdebug lub PCOV)
php bin/phpunit --coverage-html var/coverage
```

#### Wyniki

```
Tests: 112, Assertions: 279
 ✓ 70 testów jednostkowych
 ✓ 42 testy integracyjne
```

---

### Testy E2E (Playwright)

Testy end-to-end sprawdzają działanie pełnej aplikacji (frontend + backend) w przeglądarce Chromium.

> **Wymaganie:** przed uruchomieniem E2E musi działać backend na porcie 8000
> (`symfony server:start` lub `php -S 127.0.0.1:8000 -t public`)

#### Scenariusze testowe

| Plik | Scenariusze | Liczba testów |
|---|---|---|
| `e2e/auth.spec.ts` | Logowanie, wylogowanie, przekierowania, token w localStorage | 8 |
| `e2e/customers.spec.ts` | CRUD kontrahentów, wyszukiwanie, nawigacja sidebar | 7 |
| `e2e/admin.spec.ts` | CRUD użytkowników CRM, CRUD ról dostępu | 10 |

**Łącznie: 25 testów E2E**

#### Uruchamianie testów E2E

> Wszystkie komendy wykonuj z katalogu `frontend/`

```bash
# Uruchom wszystkie testy E2E (headless)
npm run e2e

# Tryb interaktywny z UI Playwright (wizualne debugowanie)
npm run e2e:ui

# Z widoczną przeglądarką
npm run e2e:headed

# Otwórz ostatni raport HTML
npm run e2e:report

# Konkretny plik testów
npx playwright test e2e/auth.spec.ts

# Konkretny test
npx playwright test --grep "poprawne logowanie"
```

#### Instalacja przeglądarek Playwright (pierwsze uruchomienie)

```bash
cd frontend
npx playwright install chromium
```

---

## Jakość kodu

### PHP — statyczna analiza i formatowanie

#### Psalm (statyczna analiza)

```bash
cd backend

# Analiza statyczna (bez cache)
vendor/bin/psalm --no-cache

# Z raportem w formacie GitHub Actions
vendor/bin/psalm --no-cache --output-format=github
```

#### PHP CS Fixer (formatowanie kodu)

```bash
cd backend

# Sprawdź styl kodu (bez zmian)
vendor/bin/php-cs-fixer fix --dry-run --diff

# Automatyczna naprawa stylu kodu
vendor/bin/php-cs-fixer fix
```

### Frontend — ESLint

```bash
cd frontend

# Sprawdź błędy
npm run lint

# Automatyczna naprawa
npm run lint:fix
```

---

## CI/CD — GitHub Actions

Projekt posiada pełny pipeline CI składający się z 4 równoległych jobów:

| Job | Narzędzia | Co sprawdza |
|---|---|---|
| `backend-static` | PHP CS Fixer, Psalm | Styl kodu i analiza statyczna PHP |
| `backend-tests` | PHPUnit + SQLite | 112 testów jednostkowych i integracyjnych |
| `frontend` | ESLint, Vite Build | Linting TypeScript/Vue + build produkcyjny |
| `e2e` | Playwright (Chromium) | 25 testów E2E (wymaga sukcesu backend-tests i frontend) |

Pipeline uruchamia się automatycznie przy każdym `push` lub `pull request` do gałęzi `main` i `develop`.

Po zakończeniu testu E2E dostępny jest artefakt **`playwright-report`** (HTML raport, 14 dni) w zakładce Actions → wybrane uruchomienie → Artifacts.

---

## Przydatne komendy

```bash
# Backend
php bin/console debug:router              # Lista wszystkich tras
php bin/console debug:container           # Lista usług w DI
php bin/console doctrine:schema:validate  # Walidacja schematu DB
php bin/console cache:clear               # Czyszczenie cache

# Nowa migracja po zmianie encji
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate

# Seed
php bin/console app:db:seed                          # Podstawowy seed
php bin/console app:db:seed --fresh                  # Czyszczenie + seed
php bin/console app:db:seed --admin-email=x@y.pl     # Własny e-mail

# Frontend
npm run dev        # Dev server (hot reload)
npm run build      # Build produkcyjny
npm run preview    # Podgląd buildu produkcyjnego
npm run lint       # ESLint
npm run e2e        # Testy E2E Playwright
npm run e2e:ui     # Playwright UI (interaktywny)
```

---

## Rozwój projektu

### Dodawanie nowego modułu

1. Utwórz katalog `src/Modules/{NazwaModułu}/` z podkatalogami
2. Dodaj wpis w `config/services.yaml`:
   ```yaml
   App\Modules\NazwaModułu\:
       resource: '../src/Modules/NazwaModułu/'
       exclude:
           - '../src/Modules/NazwaModułu/Entity/'
           - '../src/Modules/NazwaModułu/DTO/'
   ```
3. Dodaj routing w `config/routes.yaml`:
   ```yaml
   modules_nazwamodzulu:
       resource: '../src/Modules/NazwaModułu/Controller/'
       type: attribute
   ```
4. Dodaj odpowiadający komponent Vue w `frontend/src/modules/NazwaModułu/`
5. Dodaj trasę w `frontend/src/router/index.ts`

---

## Licencja

MIT — projekt jest boilerplatem do dalszego rozwoju.
