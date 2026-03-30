# MyAdmin OpenVZ VPS Plugin

Composer plugin that provisions and manages OpenVZ container VPS instances for MyAdmin via Symfony EventDispatcher hooks.

## Commands

```bash
composer install                          # install deps
vendor/bin/phpunit tests/ -v             # run tests
```

```bash
# Syntax-check plugin source
php -l src/Plugin.php
```

```bash
# Sync docs and stage after changes
caliber refresh && git add CLAUDE.md .claude/ CALIBER_LEARNINGS.md 2>/dev/null
```

## Architecture

- **Plugin entry**: `src/Plugin.php` — `Detain\MyAdminOpenvz\Plugin`
- **Hooks registered**: `vps.settings` → `getSettings()` · `vps.deactivate` → `getDeactivate()` · `vps.queue` → `getQueue()`
- **Templates**: `templates/*.sh.tpl` (primary) · `templates/backup/*.sh.tpl` (backup variants)
- **Tests**: `tests/PluginTest.php` · config `phpunit.xml.dist`
- **Autoload**: `Detain\MyAdminOpenvz\` → `src/` · `Detain\MyAdminOpenvz\Tests\` → `tests/`
- **CI/CD**: `.github/` — GitHub Actions workflows for automated testing and deployment pipelines
- **IDE Config**: `.idea/` — IDE project files including `inspectionProfiles/`, `deployment.xml`, `encodings.xml`

## Hook Patterns

**Settings** (`getSettings`): calls `$settings->add_text_setting()`, `add_dropdown_setting()`, `add_select_master()` — always wrap labels in `_()`.

**Queue** (`getQueue`): checks `in_array($event['type'], [get_service_define('OPENVZ'), get_service_define('SSD_OPENVZ')])`, renders `templates/{action}.sh.tpl` via `\TFSmarty`, logs via `myadmin_log(self::$module, ...)`, appends to `$event['output']`, calls `$event->stopPropagation()`.

**Deactivate** (`getDeactivate`): same type check, logs deactivation, adds to history queue with action `'delete'`.

## Template Operations

Each shell operation has a matching `.sh.tpl` in `templates/` and a backup variant in `templates/backup/`.

Existing operations: `add_ip` · `backup` · `block_smtp` · `change_hostname` · `change_ip` · `change_root` · `create` · `delete` · `destroy` · `disable_quota` · `enable` · `enable_quota` · `ensure_addon_ip` · `install_cpanel` · `reinstall_os` · `remove_ip` · `restart` · `restore` · `set_slices` · `start` · `stop` · `update_hdsize`

When adding a new operation `foo`:
1. Create `templates/foo.sh.tpl`
2. Create `templates/backup/foo.sh.tpl`
3. The `getQueue()` method auto-discovers templates by action name — no PHP changes needed unless adding a new hook.

## Settings Keys

- Pricing: `vps_slice_ovz_cost` · `vps_slice_ssd_ovz_cost`
- Resource limits: `vps_slice_openvz_avnumproc` · `vps_slice_openvz_numproc` · `vps_slice_openvz_cpuunits` · `vps_slice_openvz_cpus` · etc.
- Default servers: `new_vps_openvz_server` · `new_vps_ssd_openvz_server`
- Stock flags: `outofstock_openvz` · `outofstock_ssd_openvz` · `outofstock_openvz_la` · `outofstock_openvz_tx` · `outofstock_ssd_openvz_tx`

## Conventions

- Logging: `myadmin_log(self::$module, 'info'|'error', $message, __LINE__, __FILE__, self::$module, $serviceId, true, false, $custId)`
- Type checks: always check both `'OPENVZ'` and `'SSD_OPENVZ'` service defines
- `self::$module` = `'vps'` throughout
- Tab indentation (see `.scrutinizer.yml`)
- i18n: wrap all user-facing strings in `_()`
- After changes: `caliber refresh && git add CLAUDE.md .claude/ CALIBER_LEARNINGS.md 2>/dev/null`

<!-- caliber:managed:pre-commit -->
## Before Committing

**IMPORTANT:** Before every git commit, you MUST ensure Caliber syncs agent configs with the latest code changes.

First, check if the pre-commit hook is already installed:
```bash
grep -q "caliber" .git/hooks/pre-commit 2>/dev/null && echo "hook-active" || echo "no-hook"
```

- If **hook-active**: the hook handles sync automatically — just commit normally. Tell the user: "Caliber will sync your agent configs automatically via the pre-commit hook."
- If **no-hook**: run Caliber manually before committing:
  1. Tell the user: "Caliber: Syncing agent configs with your latest changes..."
  2. Run: `caliber refresh && git add CLAUDE.md .claude/ .cursor/ .cursorrules .github/copilot-instructions.md .github/instructions/ AGENTS.md CALIBER_LEARNINGS.md .agents/ .opencode/ 2>/dev/null`
  3. After it completes, briefly tell the user what Caliber updated. Then proceed with the commit.

If `caliber` is not found, tell the user: "This project uses Caliber for agent config sync. Run /setup-caliber to get set up."
<!-- /caliber:managed:pre-commit -->

<!-- caliber:managed:learnings -->
## Session Learnings

Read `CALIBER_LEARNINGS.md` for patterns and anti-patterns learned from previous sessions.
These are auto-extracted from real tool usage — treat them as project-specific rules.
<!-- /caliber:managed:learnings -->

<!-- caliber:managed:sync -->
## Context Sync

This project uses [Caliber](https://github.com/caliber-ai-org/ai-setup) to keep AI agent configs in sync across Claude Code, Cursor, Copilot, and Codex.
Configs update automatically before each commit via `caliber refresh`.
If the pre-commit hook is not set up, run `/setup-caliber` to configure everything automatically.
<!-- /caliber:managed:sync -->
