# Air Asia Secure Access and Rewards Redemption

IS 6465 HW4. The existing Air Asia application is extended in place in
`cschlawin/IS6465-codyschlawin/air-asia/airasia-hw2/`. The original
`cschlawin/air-asia` repository is not modified. The historical application
folder name is retained; there is no duplicate HW4 application or Laravel.

## Start in GitHub Codespaces

From the course repository root:

```bash
git pull --ff-only
cd air-asia
php -v
php -r 'var_export(PDO::getAvailableDrivers());'
php airasia-hw2/database/setup.php
php -S 0.0.0.0:8000 -t airasia-hw2 airasia-hw2/server.php
```

Open forwarded port **8000** (Private) and `/login.php`. `/` also redirects
to Login. Stop any other lab server using 8000 first, or choose another
available port. The original Codespace URL does not transfer to this repo.

**PHP version:** PHP 8 with PDO SQLite is required. The course Codespaces
configuration now selects its existing image's documented `8-bullseye`
variant instead of PHP 7. In an already-running PHP 7 Codespace, commit
local work and use **Codespaces: Rebuild Container**, then check `php -v`.
If PHP 8 is present but SQLite support/client is missing, install the
matching extension/client for your environment. Debian/Ubuntu system PHP:

```bash
sudo apt-get update
sudo apt-get install -y php-sqlite3 sqlite3
```

The devcontainer's `/usr/local/bin/php` build may instead need its bundled
`docker-php-ext-install pdo_sqlite` command; use `PDO::getAvailableDrivers()`
to verify the actual PHP executable. Do not add MySQL credentials.

**Use the complete server command above.** `server.php` is the small
built-in-server file-access guard used for the same purpose as the lab's
`php/router.php`; it is not a framework router or MVC refactor. It leaves
ordinary PHP page filenames as request handlers while blocking downloads
of database/SQL files and access to helpers, services and repositories.
Running an unguarded server at the course repository root exposes files
that are not public application pages. No production server deployment is
claimed by this coursework setup.

## Required grading accounts

| Role | Username | Test password |
| --- | --- | --- |
| Admin | bsmith | mysecret |
| Customer | pjones | acrobat |

Both go to Gift Card List after login, as required by the original document.
Only generated password hashes are stored in USER.password. These are
intentional coursework credentials; do not reuse personal passwords.

## Database access and migration

From `/workspaces/IS6465-codyschlawin/air-asia`:

```bash
cd airasia-hw2/database
sqlite3 rewards.db
```

At `sqlite>`:

```sql
.tables
.schema USER
.schema ACCOUNT
.schema REDEMPTION
.headers on
.mode column
SELECT userId, userName, firstName, lastName, role FROM USER;
SELECT accountId, userId, accountType, points FROM ACCOUNT;
SELECT COUNT(*) AS gift_cards FROM GIFTCARD;
SELECT COUNT(*) AS redemptions FROM REDEMPTION;
.quit
```

Use the singular table name `GIFTCARD`, not `GIFTCARDS`. Commands starting
with a dot run inside SQLite. `sqlite3 rewards.db < schema.sql` is a Bash
command and its short paths only work in the database directory.

For an existing HW3 database, use **`php airasia-hw2/database/setup.php` from
the Air Asia project folder**, not just the old schema import. The CLI-only
script creates USER and REDEMPTION, links the five existing account owners,
and rebuilds the old ACCOUNT table only when its USER foreign key is
missing. Schema, seed and migration run in one transaction, with a foreign
key check before commit. A failure rolls the operation back. Existing
card/account records, IDs, values and balances are retained; the committed
database has **21 GIFTCARD, five ACCOUNT, five USER, and zero REDEMPTION**
records before users perform redemption. All old cards, including SchlaGift,
and the saved Amazon values are preserved.

Routine setup reruns use fixed seed IDs with `ON CONFLICT(userId/cardId/
accountId) DO NOTHING`; they do not duplicate rows, reset passwords or
balances, or replace edited cards. Deliberate reseeding can reintroduce a
deleted seed ID. Back up useful local changes before schema work. If custom
old accounts reference users outside the fixtures, setup fails rather than
inventing identities or silently losing records.

`schema.sql` has exactly USER(userId, userName, password, firstName,
lastName, role), ACCOUNT(accountId, userId, accountType, points),
GIFTCARD(cardId, cardName, cardType, cardValue, points), and
REDEMPTION(redeemId, date, accountId, cardId, pointsRedeemed).
ACCOUNT references USER; REDEMPTION references ACCOUNT and GIFTCARD.
Deleting a card with redemption history is blocked by referential integrity
and gives a helpful message. An unredeemed card can still be deleted.

## Permissions and source precedence

The original AirAsia Requirements Document governs names, relationships,
roles and business rules. HW4 governs the added secure workflows. The two
Week 9 sources guide the page/service/repository separation, not adoption
of Laravel, controller classes or a new application router.

| Function | Admin | Customer |
| --- | --- | --- |
| View gift-card list/details | Yes | Yes |
| Add/update/delete gift cards | Yes | No |
| Add a customer plus linked account | Yes | No |
| View customer-profile.php | No | Own profile/accounts only |
| Redeem gift cards | Own account(s) | Own account(s) |
| Browser validation/integration harnesses | Yes | No |

**Permission reconciliation:** the generic HW4 example/rubric describes a
Customer redemption flow, but expressly defers to the original document.
Original use case 8, Redeem gift cards, lists **Customer and Admin**. Both
roles therefore may redeem, but neither may choose someone else's account
or receipt. Tests cover Admin redemption as well as the required Customer
success/insufficient-points flows. No user/role or account ID from a URL or
form is trusted as the current identity.

## Architecture and code style

Following Labs 1-7: PHP pages handle GET/POST, call functions and choose a
view/redirect; shared includes render the header/nav/footer; services apply
validation/business rules and transactions; `repositories/*PDO.php` holds
prepared SQL; includes/db.php opens SQLite with foreign keys enabled.
The existing Air Asia HTML/CSS, image and page paths are reused. Existing
snake_case validation names are retained for compatibility; new procedural
helpers use the Lab 7-style names requireLogin, requireRole, csrfToken,
verifyCsrfToken, authenticate and repository/service functions. The labs'
source files and original Air Asia repository remain unchanged.

| Location | Responsibility |
| --- | --- |
| login.php / authenticate.php | POST credentials, safe failure message, verify hash, regenerate session ID, redirect |
| logout.php | POST + CSRF, clear values/cookie, destroy session, redirect |
| includes/auth.php / authorization.php | Separate session name, cookie flags, current-user refresh, login/role/CSRF checks |
| Existing card-*.php | Authenticated reads; Admin-only writes with CSRF and existing validation |
| cust-add.php | Admin-only form and confirmation, without echoing passwords |
| customer-profile.php | Read-only current Customer plus all their linked account points |
| redeem.php / process-redemption.php | Select/confirm an owned account; atomic redemption; owned receipt |
| services/ | Gift-card validation, customer+account creation, authentication and redemption workflows |
| repositories/ | Prepared USER, ACCOUNT, GIFTCARD and REDEMPTION queries |
| database/setup.php | CLI-only transactional migration and nonduplicating fixture setup |
| server.php | Development file guard; public endpoints/assets only |
| tests/ | Pure validation and isolated in-memory service integration tests |

## Validation and security decisions

- Password verification uses password_verify(); new customer passwords are
  hashed by password_hash() in userService.php. The unchanged database
  attribute name is `password`, not an invented `password_hash` column.
- Successful login calls session_regenerate_id(true), clears old session
  data, and stores userId/userName/role plus CSRF/one-use form state. An
  anonymous session exists for the login CSRF token, as in Lab 7; no logged-in
  identity is created until credentials verify. Passwords/hashes are never
  placed in the session or redisplayed after customer submission.
- Cookies are HttpOnly and SameSite=Lax, with Secure on HTTPS. The app uses
  its own session name to avoid conflicting with the other course labs.
  Identity/role are re-read from USER on protected requests. Logout is POST.
- Every changing form, including login/logout and CRUD, validates a CSRF
  token on the server. Cached protected output is disabled. Direct URLs
  enforce login and roles before showing protected content.
- Customer creation trims/validates required names and account type to 100
  characters; usernames are unique case-insensitively. Role is always
  Customer regardless of posted data. New passwords are 8-72 bytes to match
  the bcrypt limit; the required seeded seven-character `acrobat` still
  verifies because login does not apply registration's minimum length.
  Starting points are a nonnegative integer set only by the Admin.
- Existing Retail/Dining/Entertainment/Travel card-type choices remain the
  documented allowlist. Card values are positive finite numbers and points
  are positive whole numbers. Card IDs are generated, never editable.
- A redemption retrieves the current card and own ACCOUNT from SQLite,
  checks sufficient points and conditionally updates that balance, inserts
  REDEMPTION and commits both. Any failure rolls back. Posted costs/points
  or user IDs cannot override the database/session. A one-use confirmation
  prevents refresh/replay; a changed points cost requires reviewing a new
  confirmation. GET never deducts points. Dates are stored in UTC.
- Customer profile uses only the session identity. Receipt and account
  queries enforce ownership. Multiple owned accounts can be selected;
  accounts belonging to other users are rejected even if a form is altered.
- Prepared statements bind all supplied values. Displayed data uses the
  existing htmlspecialchars helper. Errors do not expose raw SQL or hashes.

## Use Case Traceability

All page paths below are relative to `airasia-hw2/`. HW3 use cases remain
implemented; their writes now require Admin login and CSRF.

| Requirements use case | Page(s) | Tables | Verification |
| --- | --- | --- | --- |
| View a list of gift cards | card-list.php | GIFTCARD | Authenticated CGI List returns 21 saved cards; Customer/Admin navigation checked. |
| View details of one gift card | card-details.php | GIFTCARD | Authenticated selected details, IDs, role-specific links and CGI regression. |
| Add data for a new gift card | card-add.php | GIFTCARD | Admin valid POST inserts; Customer direct URL denied; invalid/CSRF requests rejected. |
| Update data for one gift card | card-update.php, card-details.php | GIFTCARD | Admin changes persist and redirect to selected details; Customer denied. |
| Delete a card | card-delete.php | GIFTCARD, REDEMPTION relationship | POST-confirmed deletion, GET/Cancel/no-confirm safety and redeemed-card FK tested. |
| Login | login.php, authenticate.php | USER | T09-T11 required credentials, failed login, regenerated session ID. |
| Log out of the system (HW4) | logout.php | Session | POST logout invalidates current/old session; protected request redirects. |
| Add a customer | cust-add.php | USER, ACCOUNT | T14 hashed password, forced Customer role, linked account and duplicate handling. |
| View customer profile and points (HW4) | customer-profile.php | USER, ACCOUNT | Customer sees own information despite altered URL IDs; Admin denied. |
| Redeem gift cards | redeem.php, process-redemption.php | GIFTCARD, ACCOUNT, REDEMPTION | T15-T16 success/insufficient/ownership/replay and forced rollback checks. |

## Tests and actual verification status

From the Air Asia project folder:

```bash
php airasia-hw2/tests/validation-tests.php
php airasia-hw2/tests/integration-tests.php
find airasia-hw2 -name '*.php' -exec php -l {} \;
```

- Validation: **29/29 passed**, pure checks with no database mutation.
- Integration: **22/22 passed**, authentication/customer/redemption checks
  against `sqlite::memory:`, including forced rollback failures.
- PHP CGI requests: required logins, all protected endpoints, direct-URL
  roles, existing CRUD regression, profile/receipt ownership, CSRF, customer
  creation, redemption success/failure/replay and logout passed using an
  isolated application/database/session copy. These are actual PHP requests
  and generated responses, not browser screenshots.
- Migration: twice-run setup preserved all 21 card rows and five account
  rows/values, with no foreign-key violations; no test purchase or extra
  customer was left in the committed database.

See test-report.md for T09-T16 steps, expected and actual outcomes, additional
checks and prior HW3 evidence. **A new HW4 Codespaces/browser session has not
been run by the assistant.** Rebuild/check PHP 8 as needed, then manually test
both grading accounts, direct URL denial, Add Customer, own Profile, one
successful and one insufficient-points redemption, logout and mobile layout.
The earlier HW3 screenshots do not certify the new HW4 flows.

Before submitting, commit/push any local data changes and submit the course
repository's Air Asia folder link in Canvas. No Canvas submission or
instructor approval is claimed. ai-use.txt retains the earlier history and
records this HW4 work and its verification limits.
