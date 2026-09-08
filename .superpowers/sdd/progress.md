# SDD Progress — Admin orders / inventory / overview

Branch: feat/restructure
Plan: docs/superpowers/plans/2026-09-08-admin-orders-inventory-overview.md
Started: 2026-09-08
Note: Do not commit unless the user asks. Ledger records file diffs, not commits.

Task 1: complete (working tree, no commit, review clean). Minors: flash uses unlocked pre-read; extra product lock on pending→processing; cancelled test does not re-assert timestamp null; Mailer untested; no delivered-unpaid case.
Task 2: complete (working tree, no commit, review clean). Minors: Confirm/Cancel visual vs tab order on small screens; backdrop CSS duplicated. ⚠️ browser keyboard/manual still needed.
Task 3: complete (working tree, no commit, review clean after dialog filter-data fix). Minors: extra unfiltered allForAdmin load on orders tab; no DB test for SQL WHERE; inventory/buyers a11y deferred.
Task 4: complete (working tree, no commit, review clean). Minors: unlabeled images/ path field; 2N hasOrderItems; form values not sticky; create price defaults 0; duplicated category/badge lists; no update() test; low-stock KPI includes hidden (Task 5 will filter).
Task 5: complete (working tree, no commit, review clean). Minors: awaiting filter includes cancelled unpaid (fix in Task 6); get_defined_vars() brittle; phpMyAdmin smoke not run.
Task 6: complete (working tree, no commit, review clean). Minors: filter test uses deltas not equality; leftover compiled backdrop class (CSS rebuilt after). HTTP smoke as admin: payment POST advances pending→Processing + flash; hide removes product from homepage; ordered delete blocked; unused delete succeeds.
Cleanup: no tracked duplicates to delete. Removed dead package.json main. Did not delete historical docs or .superpowers.

All tasks: complete. Uncommitted on feat/restructure.

