# Charts of Accounts module transfer package

This package contains copies of the active Charts of Accounts source files from this application. No application source file was altered while preparing it.

## What to copy into the receiving application

Copy `source_files/application/controllers/Accounts.php` to `application/controllers/Accounts.php`.

Copy both files from `source_files/application/models/` to `application/models/`:

- `Accounts_model.php`
- `Coa_mapping_model.php`

Copy the complete `source_files/application/views/account/` directory to `application/views/account/`. It contains the account chart, opening balance, vouchers, journals, reports, bank payment/receipt/transfer, reconciliation, COA mapping, and print views.

## Admin menu

`admin_reference/sidebar.php` is an exact copy of the source sidebar. Do not overwrite the receiver's full sidebar if it has local changes. Merge the complete `Chat Of Account` `<li class="treeview ...">` block from the source sidebar, currently at lines 1147–1172, into the receiver's `application/views/layout/sidebar.php`. The controller expects the top menu key `ChatOfAccount`.

## Fee collection integration

`integration_reference/Studentfee.php` is an exact reference copy of the source fee controller. It contains the automatic fee-receipt voucher integration and the matching draft-voucher cleanup when a fee is deleted.

Do not overwrite a receiver-specific `Studentfee.php` wholesale. Compare and merge its accounting-specific changes: the `accounts_model` / `Coa_mapping_model` loads, `bank_account_id` handling, automatic voucher creation after `fee_deposit`, and draft voucher cleanup in `deleteFee`. The receiver must already have the standard fee module tables and `Feetype_model`.

## Database structure export required

There was no `.sql` or `.mysql` file in this source checkout, and the configured local MySQL server refused the connection during packaging. Therefore no database-structure file was copied or fabricated.

Export these module tables from the source database as structure only, then place the resulting file in this package and import it into the receiving database:

- `accounts`
- `voucher_type`
- `draft_voucher_entry`
- `draft_payable_entry`
- `journal_entry`
- `journal_entry_detail`
- `account_opening_balances`
- `coa_fee_type_map`

Use a structure-only export (no data) if the destination should start clean. The `Coa_mapping_model` can create `coa_fee_type_map` on first use, but it still needs the `accounts` and standard `feetype` tables. The reconciliation feature adds `reconciled`, `reconciled_bank_id`, and `reconciled_date` to `journal_entry_detail` automatically if they are missing.

The fee collection report additionally reads the receiving application's standard tables: `student_fees_deposite`, `student_fees_master`, `student_session`, `students`, `classes`, `fee_groups_feetype`, and `feetype`.

## Legacy reference files

`legacy_reference/Accounts_model1.php` and `legacy_reference/l-account/` are copied because they remain in the source tree. They are not used by the active `Accounts` controller, which loads `application/views/account/` and `Accounts_model.php`; do not install the legacy files unless you intentionally need the older implementation.

## Validation after import

1. Log in as an administrator with the existing relevant permissions.
2. Confirm the `Chat Of Account` menu appears and opens `accounts/all_accounts`.
3. Create a test account, opening balance, and journal entry.
4. Confirm the entry appears in the journal and trial balance.
5. Add a COA mapping for a fee type and test a fee receipt with a selected bank/cash account.

