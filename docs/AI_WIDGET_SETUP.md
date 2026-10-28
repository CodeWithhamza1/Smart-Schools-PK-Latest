# AI Widget Setup

1. Import `application/migrations/001_create_ai_widget_tables.sql` into the application database.
2. Set the server environment variable `GROQ_API_KEY`. The application reads it through `application/config/groq.php`; the key is never sent to the browser.
   If Hostinger does not provide environment variables, set `$config['groq_api_key']` in `application/config/groq_sample.php` as `application/config/groq.php` directly on the server and keep that value out of Git/source archives.
3. The dashboard partial is included by `application/views/admin/dashboard.php` and calls `Ai_widget/*` endpoints.
   Upload `application/views/partials/ai_widget.php` and ensure the `application/views/partials/` directory exists on Hostinger. The dashboard now skips the optional widget safely if that file is missing.
4. No scheduler is required on Hostinger. Every `ask`, `sessions`, `history`, and `new_session` request performs opportunistic cleanup of sessions inactive for 48+ hours. Expired sessions are blocked immediately even before cleanup runs.

5. The optional cleanup endpoint remains available for servers that support scheduled requests:

   `https://YOUR_HOST/index.php/cron/ai_widget_cleanup/YOUR_CRON_SECRET`

## Current read-only AI tools

Students, attendance, fees, results, classes, sections, subjects, class timetable, staff, staff profiles, staff leave requests, homework, library books, library due books, transport routes and vehicles, hostels and rooms, inventory stock, income, expenses, admission enquiries, visitors, complaints, phone calls, postal dispatch/receive, calendar events, learning content, notices, online exams, and syllabus subjects/status.

## How questions are answered

The assistant runs a short tool-calling loop (up to 6 model calls per question). The model can call:

- `school_report` - the ready-made reports above, plus `fee_collection` (fees received between two dates). Always used for fees, because fee payments are JSON inside `student_fees_deposite.amount_detail`.
- `get_database_schema` - lists table names, searches them, or returns the columns of up to 8 tables.
- `read_only_sql` - one SELECT (joins, GROUP BY, totals, date filters; max 50 rows) for anything the reports do not cover. If the query fails, the database error is returned to the model so it can correct the query.

`read_only_sql` is admin-only and is guarded on the server: a single SELECT only, no comments or semicolons, no write/DDL keywords, no credential-like columns, and it cannot touch `users`, `roles`, `permissions`, sessions, `sms_config`, `email_config`, `payment_settings`, `sch_settings` or the `ai_widget_*` tables (checked anywhere in the statement, including comma joins and subqueries). It runs inside a read-only transaction that is always rolled back. If the AI service itself is unreachable, common questions (pending fees, attendance, staff and student lookups, income, expenses) are still answered directly from the database.
