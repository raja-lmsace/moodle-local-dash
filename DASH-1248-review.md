# DASH-1248 — Custom field select filter options in wrong order

**Branch:** `DASH-1248` (based off `Dash-2.7`, commit `a71ac55`)
**Commit:** `fa95e6c`
**File changed:** `classes/data_grid/filter/customfield_filter.php`
**Status:** ready for review / not merged

> This document is included in the PR to give the reviewer full context. It can be removed
> from the branch before merging if you prefer not to keep ticket notes in the repo.

---

## Symptom

On a dashboard (e.g. `course/view.php?id=52`) the **Language** filter — backed by the course
custom field "Language" — lists its options in a different order than the field's own
configuration page (`course/customfield.php`).

- Config page order (from `mdl_customfield_field.configdata` → `options`):
  `English, Spanish, Italian, French, Chinese, …`
- Dashboard filter order: scrambled, and only languages actually assigned to at least one
  course appear.

## Root cause

`local_dash\data_grid\filter\customfield_filter::init()` builds the filter's option list from
the **data** table, not the field definition:

```php
$options = $DB->get_records_sql_menu(
    "SELECT cd.value AS key1, cd.value AS key2 FROM {customfield_data} cd
                                  WHERE cd.fieldid = :fieldid
                                  GROUP BY cd.value",   // no ORDER BY
    $params
);
...
foreach ($options as $key => $option) {          // <-- iterates in DB order
    if (isset($selectoptions[$option])) {
        $this->add_option($key, format_string($selectoptions[$option]));
    }
}
```

Two consequences:

1. **Order follows the database, not the field config.** The query has no `ORDER BY`, so the
   rows come back in whatever order the DB chooses. On **PostgreSQL** (this site), a `GROUP BY`
   with no `ORDER BY` returns rows in hash-aggregate order — effectively arbitrary. The
   `foreach` then adds options in that order.
2. **Only used values appear.** `GROUP BY cd.value` returns only the distinct values actually
   stored on courses, so unused languages never show in the filter. (This part is intentional
   and is **kept** — a filter should only offer values that match something.)

For a `customfield_select` field, the stored `cd.value` is the **1-based index** into the
configured options (e.g. English=1, Spanish=2). `$selectoptions = $field->get_options()`
returns the labels keyed by that same index, in configured order. The bug is simply that the
code iterates the DB result (`$options`) instead of the configured list (`$selectoptions`).

## The fix

Iterate over the **configured options** (which are already in field order) and emit only those
present in the data. The set of options shown is unchanged; only the order is corrected.

### Core course/select branch

```php
// before
foreach ($options as $key => $option) {
    if (isset($selectoptions[$option])) {
        $this->add_option($key, format_string($selectoptions[$option]));
    }
}

// after
foreach ($selectoptions as $key => $option) {
    if (isset($options[$key])) {
        $this->add_option($key, format_string($option));
    }
}
```

`$selectoptions` is keyed by option index in configured order; `$options` is keyed by the
stored index. `isset($options[$key])` keeps only used options. `add_option()` is called with
the same key/label pairs as before — just in configured order.

### `local_metadata` "menu" branch (same flaw, fixed analogously)

```php
// before
$selectoptions = explode("\n", $metafield->param1);
foreach ($options as $key => $option) {
    if (in_array($option, $selectoptions)) {
        $this->add_option($key, format_string($option));
    }
}

// after
$selectoptions = array_map('trim', explode("\n", $metafield->param1));
foreach ($selectoptions as $option) {
    if (isset($options[$option])) {
        $this->add_option($options[$option], format_string($option));
    }
}
```

Here the stored value is the option **text**, so iteration is over the trimmed config lines and
membership is checked against the used values. **Note the incidental `trim()`:** `param1` is
typically stored with `\r\n` line endings, so the original `explode("\n", …)` left a trailing
`\r` on each option, meaning `in_array()` would fail to match clean DB values. Trimming both
fixes the ordering and repairs that latent matching bug. Please confirm this is acceptable
scope; if you want this branch strictly order-only, the `trim()` can be discussed separately.

## What was deliberately NOT changed

- **The non-select `else` branches** (free-text custom fields, line ~114 and the metadata
  non-menu branch). These have no configured order to honor — they list distinct stored text
  values. If consistent ordering is desired there, the better change is to add
  `ORDER BY cd.value` to the SQL, but that is a separate improvement and out of scope for this
  ticket.
- **The "only used values appear" behavior.** Kept intentionally.

## Risk / impact

- Low. Pure presentation-order change in one filter class; the option key/value pairs handed to
  `add_option()` are identical, only their order changes. No SQL change, no schema change.
- The stored filter value is the option key (index/text), so existing saved filters /
  preferences remain valid.
- Affects all `local_dash` `customfield_filter` instances (any course custom field select used
  as a dashboard filter), not just "Language".

## How to test

1. Check out `DASH-1248`.
2. On a dashboard with the Language custom-field filter, open the filter dropdown.
3. Confirm the options now appear in the same order as `course/customfield.php` for the
   Language field (subset = only languages in use), instead of the previous arbitrary order.
4. Apply a filter value and confirm results are still filtered correctly (no behavior change,
   only order).
5. If `local_metadata` is installed, repeat with a `menu`-type metadata field, including one
   whose options were saved with Windows line endings, to exercise the `trim()` path.

## Reviewer checklist

- [ ] Agree iterating configured options is the right approach vs. adding `ORDER BY` to SQL.
- [ ] OK with the incidental `trim()` correctness fix in the metadata branch (or split it out).
- [ ] Decide whether the non-select branches should also get deterministic ordering.
- [ ] Confirm `get_options()` / `get_options_array()` keying assumption across supported Moodle
      versions (3.9 vs 3.10+).
