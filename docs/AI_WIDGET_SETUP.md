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

`read_only_sql` is admin-only and works on **any table** in the database: one SELECT per call (joins, subqueries, UNION, GROUP BY, totals, date filters), up to 200 rows (100 by default). It is guarded on the server: a single SELECT only, no comments or semicolons, no write/DDL/server statements, and it runs inside a read-only transaction that is always rolled back.

Because query results are sent to the AI provider, credentials are protected:
- Columns that look like credentials (password, token, secret, api key, auth key, salt, OTP code, card number...) are never listed and come back as `[hidden]`; SQL that names them is rejected.
- These tables are hidden from the assistant: `ci_sessions`, `email_config`, `sms_config`, `payment_settings` and `ai_widget_*`. Change this with `$config['ai_widget_blocked_tables'] = array('ci_sessions', 'email_config', ...);` in `application/config/groq.php` (`*` works as a wildcard; an empty array hides nothing).

If the AI service itself is unreachable, common questions (pending fees, attendance, staff and student lookups, income, expenses) are still answered directly from the database.

## Answers that do not need the AI service

Common questions are answered straight from the database before the AI is called (instant, no tokens, and they keep working if the Groq key is wrong or Groq is unreachable): total students, students per class, pending fees (total, or for one month), fees collected for a date range (today, yesterday, this/last week or month, a month name), and greetings. If the AI call fails, the assistant also tries to answer attendance, staff, income and expense questions directly, marked "Direct report - AI offline". Questions that filter by class or section are never answered that way, because the direct reports cannot filter.

## Troubleshooting

If the widget says the AI service rejected the key (HTTP 401), create a new key at console.groq.com and set it in `application/config/groq.php` (`$config['groq_api_key']`) or in the `GROQ_API_KEY` environment variable, which takes priority. If it says the server could not reach the AI service, check internet access and the `curl.cainfo` certificate bundle in php.ini. The exact reason is also written to `application/logs/` (`Groq rejected the API key`, `Groq network failure`, `Groq HTTP failure`).
