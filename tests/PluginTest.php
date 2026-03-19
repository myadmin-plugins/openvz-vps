<?php

declare(strict_types=1);

namespace Detain\MyAdminOpenvz\Tests;

use Detain\MyAdminOpenvz\Plugin;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Unit tests for the Plugin class.
 *
 * Tests cover class structure, static properties, hook registration,
 * event handler signatures, and behavior of methods that interact
 * with external systems (via static analysis patterns).
 *
 * @covers \Detain\MyAdminOpenvz\Plugin
 */
class PluginTest extends TestCase
{
    /**
     * @var ReflectionClass<Plugin>
     */
    private ReflectionClass $reflection;

    /**
     * Set up the reflection instance used across tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(Plugin::class);
    }

    /**
     * Test that the Plugin class can be instantiated.
     *
     * @return void
     */
    public function testPluginCanBeInstantiated(): void
    {
        $plugin = new Plugin();
        $this->assertInstanceOf(Plugin::class, $plugin);
    }

    /**
     * Test that the class resides in the correct namespace.
     *
     * @return void
     */
    public function testClassNamespace(): void
    {
        $this->assertSame(
            'Detain\\MyAdminOpenvz',
            $this->reflection->getNamespaceName()
        );
    }

    /**
     * Test that the $name static property is set to the expected value.
     *
     * @return void
     */
    public function testNameProperty(): void
    {
        $this->assertSame('OpenVZ VPS', Plugin::$name);
    }

    /**
     * Test that the $description static property is a non-empty string.
     *
     * @return void
     */
    public function testDescriptionPropertyIsNonEmpty(): void
    {
        $this->assertIsString(Plugin::$description);
        $this->assertNotEmpty(Plugin::$description);
    }

    /**
     * Test that the $description contains expected keywords.
     *
     * @return void
     */
    public function testDescriptionContainsRelevantContent(): void
    {
        $this->assertStringContainsString('OpenVZ', Plugin::$description);
        $this->assertStringContainsString('container', Plugin::$description);
        $this->assertStringContainsString('virtualization', Plugin::$description);
    }

    /**
     * Test that $help is an empty string by default.
     *
     * @return void
     */
    public function testHelpPropertyIsEmpty(): void
    {
        $this->assertSame('', Plugin::$help);
    }

    /**
     * Test that $module is set to 'vps'.
     *
     * @return void
     */
    public function testModuleProperty(): void
    {
        $this->assertSame('vps', Plugin::$module);
    }

    /**
     * Test that $type is set to 'service'.
     *
     * @return void
     */
    public function testTypeProperty(): void
    {
        $this->assertSame('service', Plugin::$type);
    }

    /**
     * Test that all expected static properties exist on the class.
     *
     * @return void
     */
    public function testAllStaticPropertiesExist(): void
    {
        $expected = ['name', 'description', 'help', 'module', 'type'];
        foreach ($expected as $prop) {
            $this->assertTrue(
                $this->reflection->hasProperty($prop),
                "Expected static property \${$prop} to exist"
            );
            $this->assertTrue(
                $this->reflection->getProperty($prop)->isStatic(),
                "Expected \${$prop} to be static"
            );
            $this->assertTrue(
                $this->reflection->getProperty($prop)->isPublic(),
                "Expected \${$prop} to be public"
            );
        }
    }

    /**
     * Test that getHooks returns an array.
     *
     * @return void
     */
    public function testGetHooksReturnsArray(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertIsArray($hooks);
    }

    /**
     * Test that getHooks contains the expected event keys.
     *
     * @return void
     */
    public function testGetHooksContainsExpectedKeys(): void
    {
        $hooks = Plugin::getHooks();

        $this->assertArrayHasKey('vps.settings', $hooks);
        $this->assertArrayHasKey('vps.deactivate', $hooks);
        $this->assertArrayHasKey('vps.queue', $hooks);
    }

    /**
     * Test that getHooks does not contain an activate hook (it is commented out).
     *
     * @return void
     */
    public function testGetHooksDoesNotContainActivateHook(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertArrayNotHasKey('vps.activate', $hooks);
    }

    /**
     * Test that getHooks keys use the module property as prefix.
     *
     * @return void
     */
    public function testGetHooksKeysUseModulePrefix(): void
    {
        $hooks = Plugin::getHooks();
        foreach (array_keys($hooks) as $key) {
            $this->assertStringStartsWith(
                Plugin::$module . '.',
                $key,
                "Hook key '{$key}' should start with module prefix"
            );
        }
    }

    /**
     * Test that each hook value is a callable array with class and method.
     *
     * @return void
     */
    public function testGetHooksValuesAreCallableArrays(): void
    {
        $hooks = Plugin::getHooks();
        foreach ($hooks as $key => $value) {
            $this->assertIsArray($value, "Hook '{$key}' value should be an array");
            $this->assertCount(2, $value, "Hook '{$key}' should have exactly 2 elements");
            $this->assertSame(
                Plugin::class,
                $value[0],
                "Hook '{$key}' first element should be the Plugin class"
            );
            $this->assertIsString(
                $value[1],
                "Hook '{$key}' second element should be a method name string"
            );
        }
    }

    /**
     * Test that all hook methods referenced in getHooks actually exist.
     *
     * @return void
     */
    public function testGetHooksMethodsExist(): void
    {
        $hooks = Plugin::getHooks();
        foreach ($hooks as $key => $value) {
            $this->assertTrue(
                $this->reflection->hasMethod($value[1]),
                "Method '{$value[1]}' referenced in hook '{$key}' should exist"
            );
        }
    }

    /**
     * Test that all hook methods are public and static.
     *
     * @return void
     */
    public function testHookMethodsArePublicStatic(): void
    {
        $hooks = Plugin::getHooks();
        foreach ($hooks as $key => $value) {
            $method = $this->reflection->getMethod($value[1]);
            $this->assertTrue(
                $method->isPublic(),
                "Method '{$value[1]}' should be public"
            );
            $this->assertTrue(
                $method->isStatic(),
                "Method '{$value[1]}' should be static"
            );
        }
    }

    /**
     * Test that getActivate method exists and is public static.
     *
     * @return void
     */
    public function testGetActivateMethodExists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getActivate'));
        $method = $this->reflection->getMethod('getActivate');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
    }

    /**
     * Test that getActivate accepts a GenericEvent parameter.
     *
     * @return void
     */
    public function testGetActivateAcceptsGenericEvent(): void
    {
        $method = $this->reflection->getMethod('getActivate');
        $params = $method->getParameters();

        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());

        $type = $params[0]->getType();
        $this->assertNotNull($type);
        $this->assertSame(GenericEvent::class, $type->getName());
    }

    /**
     * Test that getDeactivate accepts a GenericEvent parameter.
     *
     * @return void
     */
    public function testGetDeactivateAcceptsGenericEvent(): void
    {
        $method = $this->reflection->getMethod('getDeactivate');
        $params = $method->getParameters();

        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());

        $type = $params[0]->getType();
        $this->assertNotNull($type);
        $this->assertSame(GenericEvent::class, $type->getName());
    }

    /**
     * Test that getSettings accepts a GenericEvent parameter.
     *
     * @return void
     */
    public function testGetSettingsAcceptsGenericEvent(): void
    {
        $method = $this->reflection->getMethod('getSettings');
        $params = $method->getParameters();

        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());

        $type = $params[0]->getType();
        $this->assertNotNull($type);
        $this->assertSame(GenericEvent::class, $type->getName());
    }

    /**
     * Test that getQueue accepts a GenericEvent parameter.
     *
     * @return void
     */
    public function testGetQueueAcceptsGenericEvent(): void
    {
        $method = $this->reflection->getMethod('getQueue');
        $params = $method->getParameters();

        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());

        $type = $params[0]->getType();
        $this->assertNotNull($type);
        $this->assertSame(GenericEvent::class, $type->getName());
    }

    /**
     * Test that the constructor takes no parameters.
     *
     * @return void
     */
    public function testConstructorHasNoParameters(): void
    {
        $constructor = $this->reflection->getConstructor();
        $this->assertNotNull($constructor);
        $this->assertCount(0, $constructor->getParameters());
    }

    /**
     * Test the exact number of public methods on the class.
     *
     * @return void
     */
    public function testPublicMethodCount(): void
    {
        $methods = $this->reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $ownMethods = array_filter($methods, function ($m) {
            return $m->getDeclaringClass()->getName() === Plugin::class;
        });

        $expectedMethods = [
            '__construct',
            'getHooks',
            'getActivate',
            'getDeactivate',
            'getSettings',
            'getQueue',
        ];

        $actualNames = array_map(function ($m) {
            return $m->getName();
        }, $ownMethods);

        sort($expectedMethods);
        sort($actualNames);

        $this->assertSame($expectedMethods, $actualNames);
    }

    /**
     * Test that getHooks returns exactly 3 hooks.
     *
     * @return void
     */
    public function testGetHooksReturnsExpectedCount(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertCount(3, $hooks);
    }

    /**
     * Test that the settings hook points to getSettings method.
     *
     * @return void
     */
    public function testSettingsHookCallable(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertSame([Plugin::class, 'getSettings'], $hooks['vps.settings']);
    }

    /**
     * Test that the deactivate hook points to getDeactivate method.
     *
     * @return void
     */
    public function testDeactivateHookCallable(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertSame([Plugin::class, 'getDeactivate'], $hooks['vps.deactivate']);
    }

    /**
     * Test that the queue hook points to getQueue method.
     *
     * @return void
     */
    public function testQueueHookCallable(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertSame([Plugin::class, 'getQueue'], $hooks['vps.queue']);
    }

    /**
     * Test that the class is not abstract.
     *
     * @return void
     */
    public function testClassIsNotAbstract(): void
    {
        $this->assertFalse($this->reflection->isAbstract());
    }

    /**
     * Test that the class is not final.
     *
     * @return void
     */
    public function testClassIsNotFinal(): void
    {
        $this->assertFalse($this->reflection->isFinal());
    }

    /**
     * Test that the class does not implement any interfaces.
     *
     * @return void
     */
    public function testClassImplementsNoInterfaces(): void
    {
        $this->assertEmpty($this->reflection->getInterfaceNames());
    }

    /**
     * Test that the class has no parent class (not extending anything).
     *
     * @return void
     */
    public function testClassHasNoParent(): void
    {
        $this->assertFalse($this->reflection->getParentClass());
    }

    /**
     * Test that the Plugin source file exists at the expected path.
     *
     * @return void
     */
    public function testPluginSourceFileExists(): void
    {
        $this->assertFileExists(
            dirname(__DIR__) . '/src/Plugin.php'
        );
    }

    /**
     * Test that the templates directory exists.
     *
     * @return void
     */
    public function testTemplatesDirectoryExists(): void
    {
        $this->assertDirectoryExists(
            dirname(__DIR__) . '/templates'
        );
    }

    /**
     * Test that expected shell template files exist.
     *
     * @dataProvider templateFileProvider
     *
     * @param string $template The template filename to check.
     * @return void
     */
    public function testTemplateFilesExist(string $template): void
    {
        $this->assertFileExists(
            dirname(__DIR__) . '/templates/' . $template
        );
    }

    /**
     * Provides template filenames that should exist.
     *
     * @return array<string, array{string}>
     */
    public function templateFileProvider(): array
    {
        return [
            'create' => ['create.sh.tpl'],
            'delete' => ['delete.sh.tpl'],
            'destroy' => ['destroy.sh.tpl'],
            'start' => ['start.sh.tpl'],
            'stop' => ['stop.sh.tpl'],
            'restart' => ['restart.sh.tpl'],
            'enable' => ['enable.sh.tpl'],
            'backup' => ['backup.sh.tpl'],
            'restore' => ['restore.sh.tpl'],
            'add_ip' => ['add_ip.sh.tpl'],
            'remove_ip' => ['remove_ip.sh.tpl'],
            'change_hostname' => ['change_hostname.sh.tpl'],
            'change_ip' => ['change_ip.sh.tpl'],
            'change_root' => ['change_root.sh.tpl'],
            'reinstall_os' => ['reinstall_os.sh.tpl'],
            'update_hdsize' => ['update_hdsize.sh.tpl'],
            'set_slices' => ['set_slices.sh.tpl'],
            'block_smtp' => ['block_smtp.sh.tpl'],
            'install_cpanel' => ['install_cpanel.sh.tpl'],
            'enable_quota' => ['enable_quota.sh.tpl'],
            'disable_quota' => ['disable_quota.sh.tpl'],
            'ensure_addon_ip' => ['ensure_addon_ip.sh.tpl'],
        ];
    }

    /**
     * Test that static properties have the correct types via reflection.
     *
     * @return void
     */
    public function testStaticPropertyTypes(): void
    {
        $this->assertIsString(Plugin::$name);
        $this->assertIsString(Plugin::$description);
        $this->assertIsString(Plugin::$help);
        $this->assertIsString(Plugin::$module);
        $this->assertIsString(Plugin::$type);
    }

    /**
     * Test that getHooks is a pure function returning consistent results.
     *
     * @return void
     */
    public function testGetHooksIsPure(): void
    {
        $first = Plugin::getHooks();
        $second = Plugin::getHooks();
        $this->assertSame($first, $second);
    }

    /**
     * Test that the getQueue method references template files
     * by checking the source code contains the templates path pattern.
     *
     * @return void
     */
    public function testGetQueueReferencesTemplatesDirectory(): void
    {
        $method = $this->reflection->getMethod('getQueue');
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();
        $filename = $method->getFileName();

        $this->assertNotFalse($filename);

        $lines = file($filename);
        $this->assertNotFalse($lines);

        $methodSource = implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        $this->assertStringContainsString('templates/', $methodSource);
        $this->assertStringContainsString('.sh.tpl', $methodSource);
    }

    /**
     * Test that getActivate method calls stopPropagation on the event.
     * Verified via static analysis of the method source.
     *
     * @return void
     */
    public function testGetActivateCallsStopPropagation(): void
    {
        $method = $this->reflection->getMethod('getActivate');
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();
        $filename = $method->getFileName();

        $lines = file($filename);
        $methodSource = implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        $this->assertStringContainsString('stopPropagation', $methodSource);
    }

    /**
     * Test that getDeactivate interacts with the global history object.
     * Verified via static analysis of the method source.
     *
     * @return void
     */
    public function testGetDeactivateUsesHistoryGlobal(): void
    {
        $method = $this->reflection->getMethod('getDeactivate');
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();
        $filename = $method->getFileName();

        $lines = file($filename);
        $methodSource = implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        $this->assertStringContainsString('$GLOBALS[\'tf\']', $methodSource);
        $this->assertStringContainsString('history->add', $methodSource);
    }

    /**
     * Test that getSettings configures both OpenVZ and SSD OpenVZ slice costs.
     * Verified via static analysis of the method source.
     *
     * @return void
     */
    public function testGetSettingsConfiguresSliceCosts(): void
    {
        $method = $this->reflection->getMethod('getSettings');
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();
        $filename = $method->getFileName();

        $lines = file($filename);
        $methodSource = implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        $this->assertStringContainsString('vps_slice_ovz_cost', $methodSource);
        $this->assertStringContainsString('vps_slice_ssd_ovz_cost', $methodSource);
    }

    /**
     * Test that getSettings references all expected OpenVZ parameter names.
     *
     * @dataProvider openvzParameterProvider
     *
     * @param string $param The parameter name to check for.
     * @return void
     */
    public function testGetSettingsReferencesOpenvzParameters(string $param): void
    {
        $method = $this->reflection->getMethod('getSettings');
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();
        $filename = $method->getFileName();

        $lines = file($filename);
        $methodSource = implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        $this->assertStringContainsString($param, $methodSource);
    }

    /**
     * Provides OpenVZ parameter setting keys expected in getSettings.
     *
     * @return array<string, array{string}>
     */
    public function openvzParameterProvider(): array
    {
        return [
            'avnumproc' => ['vps_slice_openvz_avnumproc'],
            'numproc' => ['vps_slice_openvz_numproc'],
            'numtcpsock' => ['vps_slice_openvz_numtcpsock'],
            'numothersock' => ['vps_slice_openvz_numothersock'],
            'cpuunits' => ['vps_slice_openvz_cpuunits'],
            'cpus' => ['vps_slice_openvz_cpus'],
            'dgramrcvbuf' => ['vps_slice_openvz_dgramrcvbuf'],
            'tcprcvbuf' => ['vps_slice_openvz_tcprcvbuf'],
            'tcpsndbuf' => ['vps_slice_openvz_tcpsndbuf'],
            'othersockbuf' => ['vps_slice_openvz_othersockbuf'],
            'numflock' => ['vps_slice_openvz_numflock'],
            'numpty' => ['vps_slice_openvz_numpty'],
            'shmpages' => ['vps_slice_openvz_shmpages'],
            'numiptent' => ['vps_slice_openvz_numiptent'],
        ];
    }

    /**
     * Test that getSettings handles out-of-stock dropdown settings.
     * Verified via static analysis.
     *
     * @return void
     */
    public function testGetSettingsConfiguresOutOfStockDropdowns(): void
    {
        $method = $this->reflection->getMethod('getSettings');
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();
        $filename = $method->getFileName();

        $lines = file($filename);
        $methodSource = implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        $this->assertStringContainsString('outofstock_openvz', $methodSource);
        $this->assertStringContainsString('outofstock_ssd_openvz', $methodSource);
        $this->assertStringContainsString('outofstock_openvz_la', $methodSource);
        $this->assertStringContainsString('outofstock_openvz_tx', $methodSource);
        $this->assertStringContainsString('outofstock_ssd_openvz_tx', $methodSource);
    }

    /**
     * Test that getSettings sets target to 'module' at start and 'global' at end.
     * Verified via static analysis.
     *
     * @return void
     */
    public function testGetSettingsSetsTargetModuleAndGlobal(): void
    {
        $method = $this->reflection->getMethod('getSettings');
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();
        $filename = $method->getFileName();

        $lines = file($filename);
        $methodSource = implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        $this->assertStringContainsString("setTarget('module')", $methodSource);
        $this->assertStringContainsString("setTarget('global')", $methodSource);
    }

    /**
     * Test that getQueue calls stopPropagation.
     * Verified via static analysis.
     *
     * @return void
     */
    public function testGetQueueCallsStopPropagation(): void
    {
        $method = $this->reflection->getMethod('getQueue');
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();
        $filename = $method->getFileName();

        $lines = file($filename);
        $methodSource = implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        $this->assertStringContainsString('stopPropagation', $methodSource);
    }

    /**
     * Test that event handler methods use get_service_define for type checking.
     *
     * @dataProvider eventHandlerMethodProvider
     *
     * @param string $methodName The method name to check.
     * @return void
     */
    public function testEventHandlersUseServiceDefine(string $methodName): void
    {
        $method = $this->reflection->getMethod($methodName);
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();
        $filename = $method->getFileName();

        $lines = file($filename);
        $methodSource = implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        $this->assertStringContainsString("get_service_define('OPENVZ')", $methodSource);
        $this->assertStringContainsString("get_service_define('SSD_OPENVZ')", $methodSource);
    }

    /**
     * Provides method names that should check service type defines.
     *
     * @return array<string, array{string}>
     */
    public function eventHandlerMethodProvider(): array
    {
        return [
            'getActivate' => ['getActivate'],
            'getDeactivate' => ['getDeactivate'],
            'getQueue' => ['getQueue'],
        ];
    }

    /**
     * Test that event handler methods use myadmin_log for logging.
     *
     * @dataProvider eventHandlerMethodProvider
     *
     * @param string $methodName The method name to check.
     * @return void
     */
    public function testEventHandlersUseMyadminLog(string $methodName): void
    {
        $method = $this->reflection->getMethod($methodName);
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();
        $filename = $method->getFileName();

        $lines = file($filename);
        $methodSource = implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        $this->assertStringContainsString('myadmin_log(', $methodSource);
    }

    /**
     * Test that multiple Plugin instances are independent.
     *
     * @return void
     */
    public function testMultipleInstancesAreIndependent(): void
    {
        $a = new Plugin();
        $b = new Plugin();

        $this->assertNotSame($a, $b);
        $this->assertInstanceOf(Plugin::class, $a);
        $this->assertInstanceOf(Plugin::class, $b);
    }

    /**
     * Test that getHooks returns correctly formatted hook key strings.
     *
     * @return void
     */
    public function testHookKeyFormat(): void
    {
        $hooks = Plugin::getHooks();
        foreach (array_keys($hooks) as $key) {
            $this->assertMatchesRegularExpression(
                '/^[a-z]+\.[a-z]+$/',
                $key,
                "Hook key '{$key}' should match pattern 'module.action'"
            );
        }
    }

    /**
     * Test that the class uses the Symfony GenericEvent import.
     *
     * @return void
     */
    public function testClassUsesGenericEventImport(): void
    {
        $filename = $this->reflection->getFileName();
        $this->assertNotFalse($filename);

        $content = file_get_contents($filename);
        $this->assertNotFalse($content);
        $this->assertStringContainsString(
            'use Symfony\\Component\\EventDispatcher\\GenericEvent;',
            $content
        );
    }
}
