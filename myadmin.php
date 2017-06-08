<?php
/* TODO:
 - service type, category, and services  adding
 - dealing with the SERVICE_TYPES_openvz define
 - add way to call/hook into install/uninstall
*/
return [
	'name' => 'Openvz Vps',
	'description' => 'Allows selling of Openvz Server and VPS License Types.  More info at https://www.netenberg.com/openvz.php',
	'help' => 'It provides more than one million end users the ability to quickly install dozens of the leading open source content management systems into their web space.  	Must have a pre-existing cPanel license with cPanelDirect to purchase a openvz license. Allow 10 minutes for activation.',
	'module' => 'vps',
	'author' => 'detain@interserver.net',
	'home' => 'https://github.com/detain/myadmin-openvz-vps',
	'repo' => 'https://github.com/detain/myadmin-openvz-vps',
	'version' => '1.0.0',
	'type' => 'service',
	'hooks' => [
		/*'function.requirements' => ['Detain\MyAdminOpenvz\Plugin', 'Requirements'],
		'vps.settings' => ['Detain\MyAdminOpenvz\Plugin', 'Settings'],
		'vps.activate' => ['Detain\MyAdminOpenvz\Plugin', 'Activate'],
		'vps.change_ip' => ['Detain\MyAdminOpenvz\Plugin', 'ChangeIp'],
		'ui.menu' => ['Detain\MyAdminOpenvz\Plugin', 'Menu'] */
	],
];
