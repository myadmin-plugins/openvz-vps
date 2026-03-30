---
name: add-setting
description: Adds a new setting entry to getSettings() in src/Plugin.php for the myadmin-openvz-vps plugin. Use when user says 'add setting', 'new config option', 'add dropdown', 'new pricing field', or 'out of stock for new location'. Follows the $settings->add_text_setting() / add_dropdown_setting() / add_select_master() pattern with _() i18n wrapping. Do NOT use for reading settings or adding settings to other plugins.
---
# add-setting

## Critical

- **All user-facing strings MUST be wrapped in `_()`** — this includes group labels, titles, and descriptions.
- `$settings->setTarget('module')` must appear before any `add_*` calls; `$settings->setTarget('global')` must close the method. Never remove these bookends.
- Setting keys are lowercase with underscores (e.g., `outofstock_openvz_tx`). The corresponding `get_setting()` argument is the same key uppercased (e.g., `'OUTOFSTOCK_OPENVZ_TX'`).
- For `add_select_master()`, guard the default value with `defined('CONST_NAME') ? CONST_NAME : ''` — never assume the constant is defined.
- Tab indentation throughout (see `.scrutinizer.yml`).

## Instructions

1. **Identify the setting type** needed:
   - Plain text / numeric input → `add_text_setting()`
   - Yes/No or fixed-choice dropdown → `add_dropdown_setting()`
   - Server selector (populates from master server list) → `add_select_master()`

   Verify the correct method by cross-referencing existing calls in `src/Plugin.php` inside `getSettings()`.

2. **Choose or create a group label** (second argument). Existing groups in this file:
   - `_('Slice Costs')` — per-slice pricing
   - `_('Slice OpenVZ Amounts')` — resource-limit numerics
   - `_('Default Servers')` — server selectors
   - `_('Out of Stock')` — location stock toggles

   Use an existing group when it fits. Only introduce a new label string if none apply.

3. **Pick the setting key** (third argument). Convention: `vps_` prefix for resource/pricing keys; `outofstock_` prefix for stock flags; `new_vps_` prefix for default-server keys. Key must be lowercase with underscores.

4. **Add the call** inside `getSettings()` in `src/Plugin.php`, after the last entry in the same group (keep groups contiguous).

   **Text setting:**
   ```php
   $settings->add_text_setting(self::$module, _('Group Label'), 'setting_key', _('Human Title'), _('Description of what this controls.'), $settings->get_setting('SETTING_KEY'));
   ```

   **Dropdown setting (Yes/No stock toggle pattern):**
   ```php
   $settings->add_dropdown_setting(self::$module, _('Out of Stock'), 'outofstock_openvz_xx', _('Out Of Stock OpenVZ Location Name'), _('Enable/Disable Sales Of This Type'), $settings->get_setting('OUTOFSTOCK_OPENVZ_XX'), ['0', '1'], ['No', 'Yes']);
   ```

   **Server selector:**
   ```php
   $settings->add_select_master(_(self::$module), _('Default Servers'), self::$module, 'new_vps_openvz_xx_server', _('OpenVZ Location Name Server'), defined('NEW_VPS_OPENVZ_XX_SERVER') ? NEW_VPS_OPENVZ_XX_SERVER : '', 6, 1);
   ```
   The 7th argument is the server type ID (6 = OpenVZ, 5 = SSD OpenVZ). The 8th argument is a datacenter/location ID.

5. **Verify placement**: the new line must appear between `$settings->setTarget('module');` and `$settings->setTarget('global');` in `src/Plugin.php`. Confirm with a quick read of the file after editing.

6. **Run tests** to confirm nothing is broken:
   ```bash
   vendor/bin/phpunit tests/ -v
   ```

## Examples

**User says:** "Add an out of stock toggle for OpenVZ in Miami"

**Actions taken:**
1. Setting type: dropdown (Yes/No stock toggle)
2. Group: `_('Out of Stock')` — matches existing pattern
3. Key: `outofstock_openvz_mia`
4. Insert after the last `add_dropdown_setting` in the Out of Stock group in `src/Plugin.php`:

```php
$settings->add_dropdown_setting(self::$module, _('Out of Stock'), 'outofstock_openvz_mia', _('Out Of Stock OpenVZ Miami'), _('Enable/Disable Sales Of This Type'), $settings->get_setting('OUTOFSTOCK_OPENVZ_MIA'), ['0', '1'], ['No', 'Yes']);
```

**Result:** New toggle appears in the admin settings UI under the "Out of Stock" group, stored as `outofstock_openvz_mia`, readable via `$settings->get_setting('OUTOFSTOCK_OPENVZ_MIA')` or the `OUTOFSTOCK_OPENVZ_MIA` constant elsewhere.

---

**User says:** "Add a pricing field for SSD OpenVZ Miami cost per slice"

**Actions taken:**
1. Setting type: text (numeric pricing)
2. Group: `_('Slice Costs')`
3. Key: `vps_slice_ssd_ovz_mia_cost`
4. Insert after the last Slice Costs entry in `src/Plugin.php`:

```php
$settings->add_text_setting(self::$module, _('Slice Costs'), 'vps_slice_ssd_ovz_mia_cost', _('SSD OpenVZ Miami VPS Cost Per Slice'), _('SSD OpenVZ Miami VPS will cost this much for 1 slice.'), $settings->get_setting('VPS_SLICE_SSD_OVZ_MIA_COST'));
```

**Result:** New text input in admin settings under "Slice Costs", persisted as `vps_slice_ssd_ovz_mia_cost`.

## Common Issues

**Setting shows blank/missing in UI after adding:**
- Verify the `get_setting()` key is the exact uppercase version of the setting key (`'vps_slice_foo'` → `'VPS_SLICE_FOO'`). A mismatch causes a silent empty return.

**`defined()` check missing for `add_select_master` default:**
- If you see a PHP notice `Use of undefined constant NEW_VPS_..._SERVER`, wrap the default: `defined('NEW_VPS_OPENVZ_XX_SERVER') ? NEW_VPS_OPENVZ_XX_SERVER : ''`.

**String not translated / appears raw:**
- Ensure every human-readable string argument is wrapped in `_()`. Bare string literals will not be translated and will cause i18n test failures.

**New setting placed outside the `setTarget` bookends:**
- If the setting is ignored or assigned to the wrong target, confirm your line appears after `$settings->setTarget('module');` and before `$settings->setTarget('global');` in `src/Plugin.php`.

**PHPUnit failure after edit:**
- Run `vendor/bin/phpunit tests/ -v` and check `tests/PluginTest.php` for assertions about expected setting keys — add the new key to any `assertArrayHasKey` or settings-list assertions there.
