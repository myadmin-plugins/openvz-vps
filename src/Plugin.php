<?php

namespace Detain\MyAdminOpenvz;

use Detain\Openvz\Openvz;
use Symfony\Component\EventDispatcher\GenericEvent;

class Plugin {

	public function __construct() {
	}

	public static function Activate(GenericEvent $event) {
		// will be executed when the licenses.license event is dispatched
		$license = $event->getSubject();
		if ($event['category'] == SERVICE_TYPES_FANTASTICO) {
			myadmin_log('licenses', 'info', 'Openvz Activation', __LINE__, __FILE__);
			function_requirements('activate_openvz');
			activate_openvz($license->get_ip(), $event['field1']);
			$event->stopPropagation();
		}
	}

	public static function ChangeIp(GenericEvent $event) {
		if ($event['category'] == SERVICE_TYPES_FANTASTICO) {
			$license = $event->getSubject();
			$settings = get_module_settings('licenses');
			$openvz = new Openvz(FANTASTICO_USERNAME, FANTASTICO_PASSWORD);
			myadmin_log('licenses', 'info', "IP Change - (OLD:".$license->get_ip().") (NEW:{$event['newip']})", __LINE__, __FILE__);
			$result = $openvz->editIp($license->get_ip(), $event['newip']);
			if (isset($result['faultcode'])) {
				myadmin_log('licenses', 'error', 'Openvz editIp('.$license->get_ip().', '.$event['newip'].') returned Fault '.$result['faultcode'].': '.$result['fault'], __LINE__, __FILE__);
				$event['status'] = 'error';
				$event['status_text'] = 'Error Code '.$result['faultcode'].': '.$result['fault'];
			} else {
				$GLOBALS['tf']->history->add($settings['TABLE'], 'change_ip', $event['newip'], $license->get_ip());
				$license->set_ip($event['newip'])->save();
				$event['status'] = 'ok';
				$event['status_text'] = 'The IP Address has been changed.';
			}
			$event->stopPropagation();
		}
	}

	public static function Menu(GenericEvent $event) {
		// will be executed when the licenses.settings event is dispatched
		$menu = $event->getSubject();
		$module = 'licenses';
		if ($GLOBALS['tf']->ima == 'admin') {
			$menu->add_link($module, 'choice=none.reusable_openvz', 'icons/database_warning_48.png', 'ReUsable Openvz Licenses');
			$menu->add_link($module, 'choice=none.openvz_list', 'icons/database_warning_48.png', 'Openvz Licenses Breakdown');
			$menu->add_link($module.'api', 'choice=none.openvz_licenses_list', 'whm/createacct.gif', 'List all Openvz Licenses');
		}
	}

	public static function Requirements(GenericEvent $event) {
		// will be executed when the licenses.loader event is dispatched
		$loader = $event->getSubject();
		$loader->add_requirement('crud_openvz_list', '/../vendor/detain/crud/src/crud/crud_openvz_list.php');
		$loader->add_requirement('crud_reusable_openvz', '/../vendor/detain/crud/src/crud/crud_reusable_openvz.php');
		$loader->add_requirement('get_openvz_licenses', '/../vendor/detain/myadmin-openvz-vps/src/openvz.inc.php');
		$loader->add_requirement('get_openvz_list', '/../vendor/detain/myadmin-openvz-vps/src/openvz.inc.php');
		$loader->add_requirement('openvz_licenses_list', '/../vendor/detain/myadmin-openvz-vps/src/openvz_licenses_list.php');
		$loader->add_requirement('openvz_list', '/../vendor/detain/myadmin-openvz-vps/src/openvz_list.php');
		$loader->add_requirement('get_available_openvz', '/../vendor/detain/myadmin-openvz-vps/src/openvz.inc.php');
		$loader->add_requirement('activate_openvz', '/../vendor/detain/myadmin-openvz-vps/src/openvz.inc.php');
		$loader->add_requirement('get_reusable_openvz', '/../vendor/detain/myadmin-openvz-vps/src/openvz.inc.php');
		$loader->add_requirement('reusable_openvz', '/../vendor/detain/myadmin-openvz-vps/src/reusable_openvz.php');
		$loader->add_requirement('class.Openvz', '/../vendor/detain/openvz-vps/src/Openvz.php');
		$loader->add_requirement('vps_add_openvz', '/vps/addons/vps_add_openvz.php');
	}

	public static function Settings(GenericEvent $event) {
		// will be executed when the licenses.settings event is dispatched
		$settings = $event->getSubject();
		$settings->add_text_setting('licenses', 'Openvz', 'openvz_username', 'Openvz Username:', 'Openvz Username', $settings->get_setting('FANTASTICO_USERNAME'));
		$settings->add_text_setting('licenses', 'Openvz', 'openvz_password', 'Openvz Password:', 'Openvz Password', $settings->get_setting('FANTASTICO_PASSWORD'));
		$settings->add_dropdown_setting('licenses', 'Openvz', 'outofstock_licenses_openvz', 'Out Of Stock Openvz Licenses', 'Enable/Disable Sales Of This Type', $settings->get_setting('OUTOFSTOCK_LICENSES_FANTASTICO'), array('0', '1'), array('No', 'Yes', ));
	}

}
