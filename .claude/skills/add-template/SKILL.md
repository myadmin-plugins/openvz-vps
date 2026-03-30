---
name: add-template
description: Adds a new shell operation template pair to templates/ and templates/backup/. Use when user says 'add operation', 'new VPS action', 'create template for X', or needs a new shell command executed on containers. Requires creating both templates/{op}.sh.tpl and templates/backup/{op}.sh.tpl. Do NOT use for modifying existing templates or adding PHP hook logic.
---
# add-template

## Critical

- **Always create both files**: the primary template in `templates/` AND the backup variant in `templates/backup/`. Missing either breaks the backup/legacy server path.
- Operation name must be `snake_case` (e.g., `add_ip`, `change_hostname`). The filename **is** the action key — `getQueue()` auto-discovers by matching the action name to the template filename. No PHP changes needed.
- Never interpolate unsanitized shell variables. All Smarty variables passed to shell must use `|escapeshellarg` unless they are numeric IDs.
- Verify no template with the same name already exists in `templates/` before creating.

## Instructions

### Step 1 — Determine the operation name and required variables

Identify:
- The operation name in `snake_case` (e.g., `set_password`)
- Does it need a `$param` (e.g., an IP, hostname, value)? → use `{$param|escapeshellarg}`
- Does it compute from slices/settings? → use `{$vps_slices}`, `{$settings.slice_hd}`, etc.
- Does it only need the container ID? → use the standard ID pattern (see Step 2)

Verify the operation doesn't already exist by checking `templates/` for a matching filename:

```bash
ls templates/
```

For reference, `templates/create.sh.tpl` and `templates/backup/create.sh.tpl` illustrate the expected file pair structure.

### Step 2 — Create the primary template (provirted.phar path)

This is the **primary** template used on modern servers running `provirted`. Name it `templates/OPERATION_NAME.sh.tpl`.

**Pattern — simple action (no param):**
```smarty
/root/cpaneldirect/provirted.phar {provirted-subcommand} {if $vps_vzid == "0"}{$vps_id}{else}{$vps_vzid|escapeshellarg}{/if};
```

**Pattern — action with a single param:**
```smarty
/root/cpaneldirect/provirted.phar {provirted-subcommand} --{flag}={$param|escapeshellarg} {if $vps_vzid == "0"}{$vps_id}{else}{$vps_vzid|escapeshellarg}{/if};
```

**Pattern — action with computed resource values:**
```smarty
/root/cpaneldirect/provirted.phar update --hd={($settings.slice_hd * $vps_slices) + $settings.additional_hd} --ram={$vps_slices * $settings.slice_ram} {if $vps_vzid == "0"}{$vps_id}{else}{$vps_vzid|escapeshellarg}{/if};
```

Existing `provirted.phar` subcommands for reference: `start`, `stop`, `restart`, `delete`, `create`, `update`, `add-ip`, `remove-ip`.

Verify the file was created and contains the correct container ID conditional before proceeding.

### Step 3 — Create the backup template (legacy vzctl path)

This is used on older backup/legacy servers running bare `vzctl`. Name it `templates/backup/OPERATION_NAME.sh.tpl`. It **always** starts with the PATH export.

**Pattern — simple action:**
```bash
export PATH="$PATH:/usr/sbin:/sbin:/bin:/usr/bin:";
vzctl {vzctl-subcommand} {$vps_vzid};
```

**Pattern — action with param:**
```bash
export PATH="$PATH:/usr/sbin:/sbin:/bin:/usr/bin:";
vzctl set {$vps_vzid} --save --setmode restart --{flag} {$param|escapeshellarg};
```

**Pattern — action with computed values:**
```bash
export PATH="$PATH:/usr/sbin:/sbin:/bin:/usr/bin:";
vzctl set {$vps_vzid} --save --{flag} {$computed_value};
```

Note: backup templates use `{$vps_vzid}` directly (no `{if $vps_vzid == "0"}` conditional — legacy servers always have a real VZID).

Verify the file starts with `export PATH=` before proceeding.

### Step 4 — Run tests

```bash
vendor/bin/phpunit tests/ -v
```

Verify all tests pass.

## Examples

**User says:** "Add a `suspend_ct` operation that suspends a container"

**Actions taken:**

1. Check `ls templates/` — no `suspend_ct.sh.tpl` found
2. Create primary template `templates/suspend_ct.sh.tpl`:
```smarty
/root/cpaneldirect/provirted.phar suspend {if $vps_vzid == "0"}{$vps_id}{else}{$vps_vzid|escapeshellarg}{/if};
```
3. Create backup template `templates/backup/suspend_ct.sh.tpl`:
```bash
export PATH="$PATH:/usr/sbin:/sbin:/bin:/usr/bin:";
vzctl set {$vps_vzid} --save --disabled 1;
```
4. Run tests → all pass

**Result:** `getQueue()` auto-discovers both templates. When a VPS action with type `suspend_ct` is queued, the correct template is rendered based on whether the server uses provirted or legacy vzctl.

---

**User says:** "Add a `set_password` operation that takes a new root password as a param"

Primary template `templates/set_password.sh.tpl`:
```smarty
/root/cpaneldirect/provirted.phar update --password={$param|escapeshellarg} {if $vps_vzid == "0"}{$vps_id}{else}{$vps_vzid|escapeshellarg}{/if};
```

Backup template `templates/backup/set_password.sh.tpl`:
```bash
export PATH="$PATH:/usr/sbin:/sbin:/bin:/usr/bin:";
vzctl set {$vps_vzid} --save --userpasswd root:{$param|escapeshellarg};
```

## Common Issues

**Template not discovered / action silently ignored:**
- Cause: filename does not exactly match the action name passed to `getQueue()`.
- Fix: verify the file is named exactly `{exact_action_name}.sh.tpl`. Check the action string in the queue event for typos.

**Shell injection risk flagged in review:**
- Cause: `{$param}` used without `|escapeshellarg` in a shell command.
- Fix: always append `|escapeshellarg` to any Smarty variable interpolated into a shell argument: `{$param|escapeshellarg}`.

**Backup template fails on legacy server with `command not found`:**
- Cause: missing `export PATH=` line at the top of the backup template.
- Fix: ensure the first line is exactly `export PATH="$PATH:/usr/sbin:/sbin:/bin:/usr/bin:";`

**Backup template using `{if $vps_vzid == "0"}` conditional:**
- Cause: copy-pasted from a primary template.
- Fix: backup templates always use `{$vps_vzid}` directly — no conditional. Legacy servers guarantee a real VZID.

**Tests fail after adding template:**
- Check `tests/PluginTest.php` for any assertions on template file lists — add the new operation name to any such arrays.
- Run: `vendor/bin/phpunit tests/ -v` and read the failure output.
