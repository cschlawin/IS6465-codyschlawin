# HW4 Test Report

Date: November 23, 2026. Automated tester: OpenAI ChatGPT / Codex.
Environment: PHP 8.4.27 CLI/CGI, PDO SQLite, isolated database/session copy.
No new HW4 Codespaces/browser execution is claimed. Historical HW3 student
browser tests below remain historical and are not reused as HW4 passes.

## Required HW4 cases

### T09

- **Steps/input:** GET Login, POST bsmith/mysecret with CSRF, inspect session cookie/values and List; retry pre-login session ID.
- **Expected:** Admin signs in; ID rotates; old ID cannot access protected content.
- **Actual:** bsmith/mysecret signs in; session ID rotates, old ID cannot access protected content; Admin navigation visible and session contains no password/hash.
- **Status:** PASS (automated PHP CGI, not an observed browser session).

### T10

- **Steps/input:** POST pjones/acrobat; open List, Details and Profile; alter userId/accountId query values.
- **Expected:** Customer signs in, sees only own account/profile and no Admin controls.
- **Actual:** pjones/acrobat signs in; Customer navigation omits Admin actions; details permit redemption; altered profile identifiers still show only pjones account/points.
- **Status:** PASS (automated PHP CGI, not an observed browser session).

### T11

- **Steps/input:** Try incorrect password, unknown/SQL-like usernames and missing CSRF.
- **Expected:** Safe general failure, no identity, no SQL errors; missing CSRF denied.
- **Actual:** Wrong password, unknown username and SQL-like username fail with the same safe message; no identity created; missing login CSRF rejected.
- **Status:** PASS (automated PHP CGI, not an observed browser session).

### T12

- **Steps/input:** Request all 12 protected page endpoints without identity, including nested course-folder paths.
- **Expected:** Redirect to Login and stop protected output.
- **Actual:** All 12 protected page endpoints redirect anonymous requests without content; nested course-folder redirect also correct.
- **Status:** PASS (automated PHP CGI, not an observed browser session).

### T13

- **Steps/input:** Customer directly GET/POST Add/Update/Delete cards, Add Customer and browser harnesses; Admin opens Customer profile.
- **Expected:** 403 on disallowed role paths; no changed records.
- **Actual:** Customer direct GET and POST to four Admin pages and two test harnesses return 403 with no changes. Admin customer-profile access is denied.
- **Status:** PASS (automated PHP CGI, not an observed browser session).

### T14

- **Steps/input:** Admin POST new-hw4/NewPass123!, first/last name, Rewards and 5000 points; post role=Admin; try duplicate and negative opening points.
- **Expected:** One Customer plus linked account; hashed password; no escalation or duplicate/invalid writes; new login works.
- **Actual:** Admin created Customer plus account atomically; submitted Admin role ignored, password hashed/not echoed, new customer login works, duplicate and invalid input create nothing.
- **Status:** PASS (automated PHP CGI, not an observed browser session).

### T15

- **Steps/input:** Customer confirms card 1 using own account 2; tamper posted cost/user, replay POST and access receipt as another user. Also test Admin own-account redemption per original use case 8.
- **Expected:** One redemption and matching database-priced deduction; owned receipt; replay/cross-user requests blocked.
- **Actual:** Customer redemption inserted one record and deducted database cost from own account; receipt shows new balance; posted price/user ignored, replay rejected, foreign receipt hidden. Original-spec Admin redemption also passed using own account.
- **Status:** PASS (automated PHP CGI, not an observed browser session).

### T16

- **Steps/input:** On the isolated test database, set Customer account balance to zero and confirm a card. Try another user account ID.
- **Expected:** Helpful rejection; no redemption or points change.
- **Actual:** Insufficient points leaves all rows/balances unchanged; forged other-user account selection also rejected without writes.
- **Status:** PASS (automated PHP CGI, not an observed browser session).

## Regression and additional checks

- **Price change — PASS:** when the card points cost changed after the
  confirmation form was shown, submission was rejected and no balance or
  redemption changed. The user must review the changed card again.

- **T02-T08 regression — PASS:** Authenticated List/Details/Add/Update/Delete, GET/cancel/no-confirm safety, selected-details redirect, invalid IDs/points and CSRF rejection passed; final card count 21.
- **T01/integration — PASS:** Browser-style CGI outputs show 29/29 validation and 22/22 service checks; neither harness changed the test application database.
- **Logout — PASS:** GET does not log out; POST with CSRF clears/destroys the session; both no-cookie and old-cookie protected access redirect to Login.
- **Serving guard — PASS:** Development file guard rejects database, seed, setup, helper/service/repository paths, including encoded database path.

- All 30 PHP files passed syntax checks at the final verification stage.
- The CLI validation harness passed 29/29; service harness passed 22/22.
  Database byte comparisons confirmed the web harnesses did not alter the
  application database; service writes were only in memory.
- The service harness deliberately aborted REDEMPTION insertion after a
  points update. The exception propagated and rollback restored the exact
  prior balance/count. A forced ACCOUNT insert failure also rolled back
  its new USER record. No partial transaction was left behind.
- Twice-run migration preserved every original GIFTCARD and ACCOUNT value;
  foreign_key_check returned no violations. Published fixture state is
  21 GIFTCARD, five ACCOUNT, five USER, zero REDEMPTION.

## Defects and decisions reviewed during development

- The course devcontainer still selected PHP 7, although Air Asia requires
  PHP 8. Changed its existing VARIANT setting to 8-bullseye; no Lab source
  was changed. Container rebuilding itself was not performed here.
- Corrected the session cookie path during development so an application
  served at / uses / rather than //. CGI login/logout tests passed afterward.
- Added CSRF/role checks before existing CRUD processing so hidden links
  cannot substitute for authorization. Direct GET/POST denial passed.
- Original requirements permit Admin and Customer redemption; the general
  HW4 example limits it to Customer but defers to the original. Kept both
  roles for own-account redemption and tested both. Customer Profile remains
  Customer-only. This reconciliation is documented rather than hidden.
- Preserved the USER.password name/100-character attribute and used hashes,
  rather than copying Lab 7's unrelated users/password_hash schema.
- Adopted the lab's file-serving protection purpose without Laravel, an
  MVC router/controller-class refactor, or a replacement application root.

## Manual HW4 Codespaces checklist — not yet run

1. Pull main, verify PHP 8/PDO SQLite, rebuild the container if necessary,
   run database/setup.php and launch with the guarded command in README.
2. Log in as bsmith/mysecret. Confirm Admin List/Add/Customer links, existing
   CRUD and customer creation, then log out using the navigation button.
3. Log in as pjones/acrobat. Confirm no Admin controls; paste card-add.php
   and cust-add.php directly and confirm access denied. Check My Profile.
4. Open Profile with another user's/account's ID in the URL and confirm
   it remains your own. Choose a card, confirm redemption, and check the
   receipt/current balance plus a new REDEMPTION row in SQLite.
5. Test insufficient points using a deliberately prepared test account/card,
   record the exact initial balance/cost and confirm neither table changes.
   Do not spend or alter useful demonstration data without tracking it.
6. Log out; reopen a protected URL and confirm Login. Check desktop/mobile
   layout and both Admin-only browser harnesses. Record actual outcomes here
   before claiming a manual PASS or submitting in Canvas.

## Historical HW3 baseline and student-reported evidence

The following record is preserved from HW3. Any statements about unprotected
pages, original file paths, old test dates, previous publication status, or
pending HW3 steps apply to that earlier baseline, not the current HW4 app.

# HW3 Test Report

## Relocation verification November 23 2026

Copied the committed project from cschlawin/air-asia at
c811488dd382de5b1022dcf5713e2f42e43895e6 into
cschlawin/IS6465-codyschlawin under air-asia/.
The source repository and its Git history remain intact; earlier test
results below retain their original dates and execution scope.
The committed source database has 21 GIFTCARD and five ACCOUNT records;
PRAGMA integrity_check returned ok. Application code and binary assets
are copied without changes; documentation paths are updated for the new
folder. Run the existing test/setup commands from the Air Asia project
folder, not the course repository root.
A new Codespaces/browser run from the destination has not been performed.


Test date: November 12, 2026. Tester: OpenAI ChatGPT / Codex.

## Environment and method

Executed PHP 8.4.27 CLI and CGI with PDO SQLite in the assistant's Linux
workspace. CGI requests used REQUEST_METHOD, QUERY_STRING, form-encoded POST
bodies and the actual PHP files. Assertions inspected generated HTML, HTTP
status/Location headers and SQLite rows. Workflow mutations used an isolated
temporary copy of the application/database, not the submitted rewards.db.
No test account or workflow test card was left in the submitted database.

The PASS results below apply to **server-side execution through PHP CGI**.
They do not mean a visual browser test or GitHub Codespaces run was completed.
Chromium could not launch because socket creation was denied by the local
execution environment; the browser connection attempt returned no usable
session. The assistant did not run Codespaces. A later student-reported
manual check is recorded below; other browser checks remain pending.

## Required test cases

### T01

- **Steps/input:** Run `php airasia-hw2/tests/validation-tests.php` and request `/tests/validation-tests.php`. Compare database bytes/rows before and after.
- **Expected:** All validation cases show PASS; harness never changes data.
- **Actual:** 29/29 CLI and rendered-CGI validation checks passed; database bytes and rows unchanged.
- **Status:** PASS (automated PHP CLI/CGI, not manual browser).

### T02

- **Steps/input:** Request `/card-list.php` with the seeded database; inspect each image, card name, points, stylesheet and selected-details URL.
- **Expected:** 20 cards render from GIFTCARD; asset and navigation targets exist; shared layout appears once.
- **Actual:** List rendered 20 database cards; all local image, stylesheet and link targets exist; shared layout appears once.
- **Status:** PASS (automated PHP CLI/CGI, not manual browser).

### T03

- **Steps/input:** Request `/card-details.php?card_id=N` for IDs 1-20; compare with SQLite.
- **Expected:** Each selected record displays cardId, cardName, cardType, cardValue and points with update/delete/list navigation.
- **Actual:** All 20 seeded IDs rendered their own names/types, read-only attributes and selected update/delete links.
- **Status:** PASS (automated PHP CLI/CGI, not manual browser).

### T04

- **Steps/input:** GET Add; POST cardName=Coursework Test Card, cardType=Retail, cardValue=12.50, points=1250. Inspect SQLite and redirect.
- **Expected:** GET changes nothing; POST inserts one generated-ID row and redirects to card-list.php.
- **Actual:** GET made no changes; valid POST inserted one row (ID 21) and returned 303 to card-list.php.
- **Status:** PASS (automated PHP CLI/CGI, not manual browser).

### T05

- **Steps/input:** Open Update for the new card; POST Updated Coursework Card, Travel, 30.25, 3000. Reopen details; resubmit unchanged values.
- **Expected:** Prefilled fields/read-only ID; four values persist; 303 redirects to the selected details, including unchanged resubmit.
- **Actual:** Prefilled fields and read-only ID verified; POST persisted all four changes and returned 303 to selected details; unchanged resubmit also passed.
- **Status:** PASS (automated PHP CLI/CGI, not manual browser).

### T06

- **Steps/input:** Open Delete with GET and confirm=yes in the URL; follow Cancel. POST no/missing/array confirmation, then confirm=yes. Compare rows and accounts.
- **Expected:** GET/Cancel do not delete; unconfirmed POST is rejected; affirmative POST deletes only selected card and redirects to List.
- **Actual:** GET plus confirm=yes and Cancel preserved record; missing/no/array POST confirmation rejected; affirmative POST deleted only selected test card and returned 303 to list.
- **Status:** PASS (automated PHP CLI/CGI, not manual browser).

### T07

- **Steps/input:** Submit 13 invalid payloads to both Add and Update: blank/101-character name, altered type, zero/negative/NaN/infinite value, fractional/negative/zero/overflow points, name/points arrays. Also submit a script/quote-containing name.
- **Expected:** 422 field errors and zero writes for invalid data; valid quote/markup text is stored as data and escaped on every view; safe entered values are retained.
- **Actual:** 13 altered/invalid payloads rejected on both Add and Update (26 checks), with no writes; script/quote-containing name safely persisted and escaped in list/details/update/delete; safe values retained after rejection.
- **Status:** PASS (automated PHP CLI/CGI, not manual browser).

### T08

- **Steps/input:** GET and POST Details/Update/Delete with missing, empty, zero, negative, fractional, alphabetic, exponent, SQL-like, array, overflowing and unknown ID=999999.
- **Expected:** 400 or 404 plus a helpful return link; no warnings, raw SQL errors or unintended writes.
- **Actual:** 11 missing/malformed/array/overflow/unknown ID variants safely rejected on Details, Update and Delete for GET and POST (66 checks); zero unintended changes.
- **Status:** PASS (automated PHP CLI/CGI, not manual browser).

## Additional checks actually performed

- All 12 PHP files passed `php -l`, including the unchanged login prototype.
- SQLite integrity_check returned `ok`; the delivered database has exactly
  20 GIFTCARD and five ACCOUNT rows and only those two application tables
  (SQLite also maintains its internal sqlite_sequence table).
- Running schema and seed twice preserved rows/counts. Rerunning seed after
  editing a seed record preserved the edit instead of overwriting it.
- The harness left database bytes and all rows unchanged. The CRUD test
  suite left ACCOUNT data unchanged.
- Temporarily removing the test database produced a friendly 503 response,
  without creating an empty replacement or printing raw connection details.
- All source/asset URLs in List resolve to actual project files. The later
  student-reported narrow-layout check is documented below.

## Defects and review decisions

1. Existing gift-card pages printed C-style comments before HTML. This can
   expose comments and interfere with Location headers. Converted affected
   page comments to PHP comments before output. Retest: Add, Update and
   Delete returned the required 303 Location headers without leading text.
2. The initial T03 assertion compared H&M literally with escaped HTML and
   incorrectly failed. Corrected the test comparison to decode HTML text;
   output escaping was retained. Retest: all 20 selected records passed.
3. Existing static Update returned to List and there was no Delete page.
   Implemented the current HW3 flow and confirmed POST-only deletion.
   Retest: T05-T06 passed.
4. Card values have no documented two-decimal storage restriction. Used
   positive finite numeric validation with `step="any"`; display remains
   two-decimal USD formatting. The old HTML-only constraints do not replace
   PHP validation. Retest: harness and invalid-input checks passed.

## Student-reported Codespaces results

Date: November 12, 2026. Tester: Cody Schlawin.
Evidence: the student's report in this project conversation, not an
assistant-observed browser session or screenshot.

Student report: "I was able to launch the website, interact with active
pages, add, update and delete a new card."

| Check | Actual result | Status |
| --- | --- | --- |
| Codespaces launch and basic page navigation | Student reported that the site launched and active pages were usable. | PASS (student-reported basic smoke test) |
| T04 basic Add workflow | Student reported adding a new card. Exact field values were not supplied. | PASS (student-reported basic operation) |
| T05 basic Update workflow | Student reported updating the new card. Individual field/redirect assertions were not supplied. | PASS (student-reported basic operation) |
| T06 basic Delete workflow | Student reported deleting the new card. Cancel was later confirmed to preserve SchlaGift; crafted unconfirmed POSTs were tested automatically. | PASS (student-reported basic operation) |
| SQLite access and table listing | Earlier supplied terminal output showed rewards.db opened and .tables returned ACCOUNT and GIFTCARD. | PASS (student-provided terminal evidence) |

These results supplement the detailed automated tests rather than asserting
that every manual test substep passed. Database counts and saved records
are confirmed by the terminal evidence below. Additional browser results
and their evidence are recorded in the following section. Assertions not
specifically reported by the student are not represented as manual passes.

### Database validation evidence supplied by the student

The later Codespaces terminal transcript confirms SQLite 3.53.4 opened
rewards.db and reported the following results:

| Check | Actual result | Status |
| --- | --- | --- |
| Required tables | .tables lists ACCOUNT and GIFTCARD. | PASS |
| Gift-card schema | .schema GIFTCARD matches the required five attributes, generated primary key, required strings and numeric checks. | PASS |
| Gift-card minimum count | SELECT COUNT(*) FROM GIFTCARD returned 21. | PASS (minimum 20) |
| Account minimum count | SELECT COUNT(*) FROM ACCOUNT returned 5. | PASS (minimum 5) |
| Persisted new record | cardId 21: SchlaGift, Entertainment, cardValue 1000000000.0, points 4. | PASS (record persisted; value/points meet the documented positive-number rules) |
| Persisted existing-card change | cardId 1: Amazon Gift Card, cardValue 30.0, points 3000. | PASS (saved values visible in SQLite) |

The student confirmed the 21-card total is intentional because a new card
was retained. There is no requirement to remove it. The unusually large
value and small points amount are allowed by the current documented rules;
no maximum value or value-to-points exchange rule was specified.

The initial query SELECT * FROM GIFTCARDS failed because the approved table
name is singular: GIFTCARD. The corrected query succeeded. This was a query
typo, not an application defect; no schema rename or code change is needed.

Evidence scope: these results describe the student's local Codespaces
database. They do not establish that its changed rewards.db has been
committed/pushed to GitHub. The assistant did not modify the database in
response to this evidence.

### Additional browser checks completed by the student

Date: November 12, 2026. Evidence: supplied screenshots and direct
student confirmations in the project conversation.

| Check | Actual result and evidence | Status |
| --- | --- | --- |
| T01 browser validation harness | Full-page screenshot shows 29/29 passed and PASS on every listed case. | PASS |
| T08 malformed ID | Screenshot for card_id=abc shows the valid-positive-integer message; student confirmed return to List. | PASS |
| T08 nonexistent ID | Screenshot for card_id=999999 shows the card-not-found message; student confirmed return to List. | PASS |
| T06 Delete Cancel | Student confirmed Cancel worked and SchlaGift was still present. No count was re-queried after this particular check. | PASS (card-preservation behavior) |
| T07 blank required field | Student reported submission blocked with "Please fill out this field". | PASS (native browser validation) |
| T07 negative points | Student reported "Value must be greater than or equal to 1" after entering -1. | PASS (native browser validation) |
| Narrow/mobile layout | After instructions to inspect List, Details and Add at narrow width, student reported all pages were visible in a narrowed mobile view. No layout defect was reported. | PASS (student-reported visual smoke test) |

The required-field and minimum-value browser checks are not proof that
crafted POST requests were rejected on the server. That behavior, along
with safe value retention, was independently exercised in the recorded
automated PHP CGI tests. Browser error screenshots visibly show the expected
messages without PHP warnings or raw database errors.

No new code defect was reported during these manual checks. Exact viewport
dimensions, every card's browser details, all navigation links, and every
crafted POST case were not individually evidenced in the manual session;
the earlier detailed automated tests remain the evidence for those
applicable server-side assertions. No additional manual PASS is inferred.

## Manual Codespaces/browser checklist

The reported manual checks are recorded above. This checklist is retained
as a repeatable reference; it is not a claim that every listed substep was
individually observed. No browser/Codespaces code defect remains reported.

1. Open the repository in Codespaces; pull main, verify PHP 8 and pdo_sqlite,
   and run the setup/count checks in README. Start the server on port 8000.
2. Open `/card-list.php` directly without logging in. Confirm all current cards (21 in the latest student check)
   display names, points and images with no broken layout on desktop/mobile.
3. Open two different cards and confirm the details differ and show all
   five attributes. Use List/Add navigation and each Back/Cancel link.
4. Add a uniquely named temporary test card, update it and confirm the
   redirected details show the changes. Check field errors and safe input
   preservation for invalid submissions (disable native browser validation
   if intentionally testing server rejection).
5. Open Delete, cancel and verify the card remains. Then confirm deletion;
   check that only the selected test card disappears and at least 20 cards
   remain. The student's retained SchlaGift card does not need to be removed.
6. Visit `card-details.php?card_id=abc` and `?card_id=999999`; confirm helpful
   errors, then open `/tests/validation-tests.php` and confirm 29/29 PASS.
7. Record actual browser/Codespaces outcomes here, including any fixes and
   retests. Commit your own changes and database state before submission.

## Submission status

Code and database tests described above were actually executed. The assistant
will verify the GitHub commit after publishing; an unverified push or Canvas
submission is not treated as a passing test. Canvas submission remains the
student's responsibility. This report does not certify instructor approval.
