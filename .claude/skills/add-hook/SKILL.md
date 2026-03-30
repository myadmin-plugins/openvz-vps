---
name: add-hook
description: Registers a new Symfony EventDispatcher hook in getHooks() and implements the handler method in src/Plugin.php. Use when user says 'add hook', 'handle new event', 'listen for X', or needs to respond to a new vps.* event. Follows the GenericEvent type check and stopPropagation() pattern. Do NOT use for modifying existing hooks or adding shell templates (see add-operation skill).
---
# add-hook

## Critical

- Always check **both** service types: `get_service_define('OPENVZ')` and `get_service_define('SSD_OPENVZ')` in the `in_array()` guard — never handle only one.
- `$event->stopPropagation()` **must** be called inside the type-check block, not after it.
- All user-facing strings must be wrapped in `_()`.
- Use tab indentation (enforced by `.scrutinizer.yml`).
- `self::$module` is always `'vps'` — never hardcode the string.
- After editing `src/Plugin.php`, run `caliber refresh && git add CLAUDE.md .claude/ CALIBER_LEARNINGS.md 2>/dev/null`.

## Instructions

1. **Identify the event name and subject type.**
   Determine the full event name (e.g., `vps.activate`, `vps.suspend`) and whether the subject is a service class object (has `getId()`/`getCustid()`) or an array (`$event->getSubject()` returns array).
   Verify the event is not already registered in `getHooks()` in `src/Plugin.php` before proceeding.

2. **Register the hook in `getHooks()`.**
   Open `src/Plugin.php` and add an entry to the array returned by `getHooks()`:
   ```php
   public static function getHooks()
   {
       return [
           self::$module.'.settings'  => [__CLASS__, 'getSettings'],
           self::$module.'.deactivate' => [__CLASS__, 'getDeactivate'],
           self::$module.'.queue'     => [__CLASS__, 'getQueue'],
           self::$module.'.{eventSuffix}' => [__CLASS__, 'get{HandlerName}'],  // add here
       ];
   }
   ```
   Use the naming convention: event suffix `activate` → method `getActivate`, `suspend` → `getSuspend`.

3. **Implement the handler method.**
   Add the static method immediately after the last handler in the class, before the closing `}`. Follow this exact skeleton:

   **For object subjects** (service class with `getId()`/`getCustid()`):
   ```php
   /**
    * @param \Symfony\Component\EventDispatcher\GenericEvent $event
    */
   public static function get{HandlerName}(GenericEvent $event)
   {
       if (in_array($event['type'], [get_service_define('OPENVZ'), get_service_define('SSD_OPENVZ')])) {
           $serviceClass = $event->getSubject();
           myadmin_log(self::$module, 'info', self::$name.' {Action Description}', __LINE__, __FILE__, self::$module, $serviceClass->getId(), true, false, $serviceClass->getCustid());
           $event->stopPropagation();
       }
   }
   ```

   **For array subjects** (queue-style, subject is `$serviceInfo` array):
   ```php
   /**
    * @param \Symfony\Component\EventDispatcher\GenericEvent $event
    */
   public static function get{HandlerName}(GenericEvent $event)
   {
       if (in_array($event['type'], [get_service_define('OPENVZ'), get_service_define('SSD_OPENVZ')])) {
           $serviceInfo = $event->getSubject();
           $settings = get_module_settings(self::$module);
           myadmin_log(self::$module, 'info', self::$name.' {Action Description}', __LINE__, __FILE__, self::$module, $serviceInfo[$settings['PREFIX'].'_id'], true, false, $serviceInfo[$settings['PREFIX'].'_custid']);
           $event->stopPropagation();
       }
   }
   ```

4. **Verify the implementation.**
   - Confirm `getHooks()` now contains the new entry.
   - Confirm the handler method is `public static` and accepts `GenericEvent $event`.
   - Confirm `in_array($event['type'], [get_service_define('OPENVZ'), get_service_define('SSD_OPENVZ')])` wraps all logic.
   - Confirm `$event->stopPropagation()` is inside the `if` block.
   - Run `vendor/bin/phpunit tests/ -v` and confirm no failures.

5. **Sync docs.**
   ```bash
   caliber refresh && git add CLAUDE.md .claude/ CALIBER_LEARNINGS.md 2>/dev/null
   ```

## Examples

**User says:** "Add a hook to handle `vps.suspend` events for OpenVZ VPS."

**Actions taken:**

1. Add to `getHooks()` in `src/Plugin.php`:
   ```php
   self::$module.'.suspend' => [__CLASS__, 'getSuspend'],
   ```

2. Add handler method:
   ```php
   /**
    * @param \Symfony\Component\EventDispatcher\GenericEvent $event
    */
   public static function getSuspend(GenericEvent $event)
   {
       if (in_array($event['type'], [get_service_define('OPENVZ'), get_service_define('SSD_OPENVZ')])) {
           $serviceClass = $event->getSubject();
           myadmin_log(self::$module, 'info', self::$name.' Suspension', __LINE__, __FILE__, self::$module, $serviceClass->getId(), true, false, $serviceClass->getCustid());
           $event->stopPropagation();
       }
   }
   ```

**Result:** `vps.suspend` events dispatched for OPENVZ or SSD_OPENVZ service types are now handled by this plugin. Other plugins will not receive the event because `stopPropagation()` is called.

## Common Issues

- **`getHooks()` entry added but handler never fires:** The event name in `getHooks()` must exactly match the dispatched event string. Check for typos — `vps.deactivate` vs `vps.deActivate`. Event names are lowercase with a dot separator.

- **Both service types not handled:** If only `OPENVZ` is in the `in_array()` and the event fires for an SSD_OPENVZ service, the handler silently skips it with no log. Always include both defines.

- **`stopPropagation()` outside the `if` block:** Placing `$event->stopPropagation()` after the closing `}` of the type check means propagation is stopped even for non-OPENVZ events, breaking other plugins. It must be the last statement *inside* the `if`.

- **PHPUnit fails with `Call to undefined function get_service_define()`:** This function is a MyAdmin global. Tests must be run with the correct bootstrap: `vendor/bin/phpunit tests/ -v`. Running `phpunit` directly without the bootstrap file generated by `composer install` will fail.

- **Method not `static`:** All handler methods must be `public static`. Non-static handlers registered as `[__CLASS__, 'method']` will cause a PHP warning or error when the EventDispatcher tries to call them statically.
