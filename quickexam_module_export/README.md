# Quick Examination module export

This package was extracted from `D:\All Scholls\JFS_Latest_06JUNE` on 2026-08-24. It is for another application built from the same CodeIgniter school-system source.

## What is included

The files under `application/` are copied at their original application paths:

- `application/controllers/admin/Quickexam.php` — `/admin/quickexam` endpoints.
- `application/models/Quickexam_model.php` — test creation, student loading, subjects, dashboard data, and publishing.
- `application/models/Whatsappcampaign_model.php` — the directly loaded model used by **Publish & Notify**. It is a shared model in the source application; merge it carefully if the target already has a WhatsApp campaign implementation.
- `application/views/admin/quickexam/index.php` — combined create/marks-entry screen.
- `application/views/admin/quickexam/_marks_grid.php` — AJAX marks grid.
- `application/views/admin/quickexam/entries.php` — legacy standalone entry view retained for compatibility/reference. The current controller redirects `entries()` to the combined screen.

The `integration/source_snapshots/` folder contains the source snapshots of the three shared files that expose or render the feature:

- `application/controllers/admin/Admin.php` — loads `quickexam_model` and supplies dashboard widget data.
- `application/views/admin/dashboard.php` — renders the Quick Examination dashboard widget.
- `application/views/layout/sidebar.php` — adds the Quick Examination menu link.

These snapshots are included for comparison. Do not blindly overwrite customized target files; merge the Quick Examination blocks into the target copies.

## Installation

1. Confirm that the target is the same CodeIgniter source family and that its normal exam-group feature works first.
2. Copy the files under this package's `application/` directory into the target `application/` directory, preserving case and folders.
3. If `Whatsappcampaign_model.php` already exists in the target, merge the source version or at minimum ensure it contains `createExamResultCampaign()` and its supporting methods. The Quick Examination controller loads this model during construction.
4. Merge the Quick Examination changes from the three snapshots into the target's `Admin.php`, `dashboard.php`, and `sidebar.php`. The source locations can be found with:

   ```text
   quickexam_model
   quick_exam_widget
   Examinations/quickexam
   admin/quickexam
   ```

5. Check the target's `application/config/routes.php`. No special route is required when CodeIgniter's normal controller/method fallback is enabled; `/admin/quickexam` resolves to `admin/quickexam`. Add an explicit route only if that target disables fallback routing.
6. Review and run `database/quickexam_schema.sql` against the target database. It only bootstraps the `Teacher Quick Tests` row and does not create duplicate exam tables.
7. Run `database/quickexam_verify.sql`. All required standard exam/student tables must exist, and the `Teacher Quick Tests` group should be present.
8. Log in as a staff account with the existing `exam_group` RBAC permissions. The feature requires `can_view`, `can_add`, and `can_edit` as appropriate.

## Database and notification notes

Quick Examination stores tests and marks in the standard exam-group tables. It does not have a separate report table, PDF renderer, or new report route. Published marks therefore remain available through the existing exam-result/marksheet reporting pipeline.

`Publish & Notify` always sends the existing `exam_result` mail/SMS notification path. Its WhatsApp queue is optional: if `whatsapp_campaigns` and `whatsapp_campaign_items` are absent, the test still publishes and the response reports that WhatsApp could not be queued. If the target already uses the source WhatsApp campaign module, install that module's own complete schema/worker configuration before enabling WhatsApp delivery; this export does not guess or overwrite that broader shared schema.

## Smoke test

1. Open `/admin/quickexam`.
2. Create a test with a title, class, section, date, subject, max marks, and at least one active student.
3. Confirm the marks grid loads, save a draft, switch subjects if using separate subject entry, and reload the grid.
4. Publish without notification and confirm the result is visible through the existing exam-result flow.
5. If WhatsApp is configured, repeat with **Publish & Notify** and confirm a campaign is queued and the worker/provider is configured.

## Integrity checklist

- Preserve the existing exam-group table schema and foreign-key relationships.
- Do not replace customized `Admin.php`, `dashboard.php`, `sidebar.php`, or `Whatsappcampaign_model.php` wholesale without merging first.
- Keep CSRF, RBAC, session, and existing `mailsmsconf` configuration enabled.
- Confirm the selected class/section has active students in the current session.
- Test both single-subject and separate-subject entry if both are needed.
- Verify marks, attendance, publish state, and existing marksheet output after installation.

