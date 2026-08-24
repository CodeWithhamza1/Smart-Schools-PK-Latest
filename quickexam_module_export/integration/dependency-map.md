# Runtime dependency map

## Controller endpoints

| Endpoint | Runtime dependencies |
|---|---|
| `index` | `class_model`, `subject_model`, `setting_model`, `quickexam_model`, layout header/footer |
| `entries` | Redirect only; retained legacy view is `admin/quickexam/entries.php` |
| `createtest` | `form_validation`, `customlib`, `quickexam_model`, `examgroupstudent_model`, `batchsubject_model`, `setting_model`, `_marks_grid` |
| `getsubjects` | `quickexam_model` |
| `loadmarksgrid` | `quickexam_model`, `examgroupstudent_model`, `batchsubject_model`, `setting_model`, `_marks_grid` |
| `savemarks` | `form_validation`, `examgroupstudent_model` |
| `publish` | `form_validation`, `examgroup_model`, `examgroupstudent_model`, `quickexam_model`, `mailsmsconf`, optional `whatsappcampaign_model` |

## Existing source files that must remain available

These are standard same-source application dependencies and are intentionally not duplicated in the export:

- `application/core/Admin_Controller.php`
- `application/models/Examgroup_model.php`
- `application/models/Examgroupstudent_model.php`
- `application/models/Batchsubject_model.php`
- `application/models/Class_model.php`
- `application/models/Subject_model.php`
- `application/models/Setting_model.php`
- `application/libraries/Mailsmsconf.php`
- the normal CodeIgniter database, session, form-validation, URL, and security helpers/libraries

The export includes the custom `Whatsappcampaign_model.php` because the controller explicitly loads it, even though WhatsApp queueing is optional at runtime.

