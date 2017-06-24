<?php

namespace Detain\MyAdminOpenvz;

use Detain\Openvz\Openvz;
use Symfony\Component\EventDispatcher\GenericEvent;

class Plugin {

	public static $name = 'Openvz Vps';
	public static $description = 'Allows selling of Openvz Server and VPS License Types.  More info at https://www.netenberg.com/openvz.php';
	public static $help = 'It provides more than one million end users the ability to quickly install dozens of the leading open source content management systems into their web space.  	Must have a pre-existing cPanel license with cPanelDirect to purchase a openvz license. Allow 10 minutes for activation.';
	public static $module = 'vps';
	public static $type = 'service';


	public function __construct() {
	}

	public static function getHooks() {
		return [
			'vps.settings' => [__CLASS__, 'getSettings'],
		];
	}

	public static function getActivate(GenericEvent $event) {
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

	public static function getMenu(GenericEvent $event) {
		$menu = $event->getSubject();
		$module = 'licenses';
		if ($GLOBALS['tf']->ima == 'admin') {
			$menu->add_link($module, 'choice=none.reusable_openvz', 'icons/database_warning_48.png', 'ReUsable Openvz Licenses');
			$menu->add_link($module, 'choice=none.openvz_list', 'icons/database_warning_48.png', 'Openvz Licenses Breakdown');
			$menu->add_link($module.'api', 'choice=none.openvz_licenses_list', 'whm/createacct.gif', 'List all Openvz Licenses');
		}
	}

	public static function getRequirements(GenericEvent $event) {
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

	public static function getSettings(GenericEvent $event) {
		$module = 'vps';
		$settings = $event->getSubject();
		$settings->add_text_setting($module, 'Slice Costs', 'vps_slice_ovz_cost', 'OpenVZ VPS Cost Per Slice:', 'OpenVZ VPS will cost this much for 1 slice.', $settings->get_setting('VPS_SLICE_OVZ_COST'));
		$settings->add_text_setting($module, 'Slice Costs', 'vps_slice_ssd_ovz_cost', 'SSD OpenVZ VPS Cost Per Slice:', 'SSD OpenVZ VPS will cost this much for 1 slice.', $settings->get_setting('VPS_SLICE_SSD_OVZ_COST'));
		$settings->add_text_setting($module, 'Slice OpenVZ Amounts', 'vps_slice_openvz_avnumproc', 'avnumproc', 'The average number of processes and threads. ', $settings->get_setting('VPS_SLICE_OPENVZ_AVNUMPROC'));
		$settings->add_text_setting($module, 'Slice OpenVZ Amounts', 'vps_slice_openvz_numproc', 'numproc', 'The maximal number of processes and threads the VE may create. ', $settings->get_setting('VPS_SLICE_OPENVZ_NUMPROC'));
		$settings->add_text_setting($module, 'Slice OpenVZ Amounts', 'vps_slice_openvz_numtcpsock', 'numtcpsock', 'The number of TCP sockets (PF_INET family, SOCK_STREAM type). This parameter limits the number of TCP connections and, thus, the number of clients the server application can handle in parallel. ', $settings->get_setting('VPS_SLICE_OPENVZ_NUMTCPSOCK'));
		$settings->add_text_setting($module, 'Slice OpenVZ Amounts', 'vps_slice_openvz_numothersock', 'numothersock', ' The number of sockets other than TCP ones. Local (UNIX-domain) sockets are used for communications inside the system. UDP sockets are used, for example, for Domain Name Service (DNS) queries. UDP and other sockets may also be used in some very specialized applications (SNMP agents and others). ', $settings->get_setting('VPS_SLICE_OPENVZ_NUMOTHERSOCK'));
		$settings->add_text_setting($module, 'Slice OpenVZ Amounts', 'vps_slice_openvz_cpuunits', 'cpuunits', '', $settings->get_setting('VPS_SLICE_OPENVZ_CPUUNITS'));
		$settings->add_text_setting($module, 'Slice OpenVZ Amounts', 'vps_slice_openvz_cpus', 'slices per core', '', $settings->get_setting('VPS_SLICE_OPENVZ_CPUS'));
		$settings->add_text_setting($module, 'Slice OpenVZ Amounts', 'vps_slice_openvz_dgramrcvbuf', 'dgramrcvbuf', 'The total size of receive buffers of UDP and other datagram protocols. ', $settings->get_setting('VPS_SLICE_OPENVZ_DGRAMRCVBUF'));
		$settings->add_text_setting($module, 'Slice OpenVZ Amounts', 'vps_slice_openvz_tcprcvbuf', 'tcprcvbuf', 'The total size of receive buffers for TCP sockets, i.e. the amount of kernel memory allocated for the data received from the remote side, but not read by the local application yet. ', $settings->get_setting('VPS_SLICE_OPENVZ_TCPRCVBUF'));
		$settings->add_text_setting($module, 'Slice OpenVZ Amounts', 'vps_slice_openvz_tcpsndbuf', 'tcpsndbuf', 'The total size of send buffers for TCP sockets, i.e. the amount of kernel memory allocated for the data sent from an application to a TCP socket, but not acknowledged by the remote side yet. ', $settings->get_setting('VPS_SLICE_OPENVZ_TCPSNDBUF'));
		$settings->add_text_setting($module, 'Slice OpenVZ Amounts', 'vps_slice_openvz_othersockbuf', 'othersockbuf', 'The total size of UNIX-domain socket buffers, UDP, and other datagram protocol send buffers. ', $settings->get_setting('VPS_SLICE_OPENVZ_OTHERSOCKBUF'));
		$settings->add_text_setting($module, 'Slice OpenVZ Amounts', 'vps_slice_openvz_numflock', 'numflock', 'The number of file locks created by all VE processes. ', $settings->get_setting('VPS_SLICE_OPENVZ_NUMFLOCK'));
		$settings->add_text_setting($module, 'Slice OpenVZ Amounts', 'vps_slice_openvz_numpty_base', 'numpty_base', 'This setting is multiplied by the number of slices. This parameter is usually used to limit the number of simultaneous shell sessions.', $settings->get_setting('VPS_SLICE_OPENVZ_NUMPTY_BASE'));
		$settings->add_text_setting($module, 'Slice OpenVZ Amounts', 'vps_slice_openvz_numpty', 'numpty', 'This parameter is usually used to limit the number of simultaneous shell sessions.', $settings->get_setting('VPS_SLICE_OPENVZ_NUMPTY'));
		$settings->add_text_setting($module, 'Slice OpenVZ Amounts', 'vps_slice_openvz_shmpages', 'shmpages', 'The total size of shared memory (IPC, shared anonymous mappings and tmpfs objects). ', $settings->get_setting('VPS_SLICE_OPENVZ_SHMPAGES'));
		$settings->add_text_setting($module, 'Slice OpenVZ Amounts', 'vps_slice_openvz_numiptent', 'numiptent', 'The number of IP packet filtering entries. ', $settings->get_setting('VPS_SLICE_OPENVZ_NUMIPTENT'));
		$settings->add_select_master($module, 'Default Servers', $module, 'new_vps_openvz_server', 'OpenVZ NJ Server', NEW_VPS_OPENVZ_SERVER, 6, 1);
		$settings->add_select_master($module, 'Default Servers', $module, 'new_vps_ssd_openvz_server', 'SSD OpenVZ NJ Server', NEW_VPS_SSD_OPENVZ_SERVER, 5, 1);
		$settings->add_select_master($module, 'Default Servers', $module, 'new_vps_la_openvz_server', 'OpenVZ LA Server', NEW_VPS_LA_OPENVZ_SERVER, 6, 2);
		//$settings->add_select_master($module, 'Default Servers', $module, 'new_vps_ny_openvz_server', 'OpenVZ NY4 Server', NEW_VPS_NY_OPENVZ_SERVER, 0, 3);
		$settings->add_dropdown_setting($module, 'Out of Stock', 'outofstock_openvz', 'Out Of Stock OpenVZ Secaucus', 'Enable/Disable Sales Of This Type', $settings->get_setting('OUTOFSTOCK_OPENVZ'), array('0', '1'), array('No', 'Yes',));
		$settings->add_dropdown_setting($module, 'Out of Stock', 'outofstock_ssd_openvz', 'Out Of Stock SSD OpenVZ Secaucus', 'Enable/Disable Sales Of This Type', $settings->get_setting('OUTOFSTOCK_SSD_OPENVZ'), array('0', '1'), array('No', 'Yes',));
		$settings->add_dropdown_setting($module, 'Out of Stock', 'outofstock_openvz_la', 'Out Of Stock OpenVZ Los Angeles', 'Enable/Disable Sales Of This Type', $settings->get_setting('OUTOFSTOCK_OPENVZ_LA'), array('0', '1'), array('No', 'Yes',));
		$settings->add_dropdown_setting($module, 'Out of Stock', 'outofstock_openvz_ny', 'Out Of Stock OpenVZ Equinix NY4', 'Enable/Disable Sales Of This Type', $settings->get_setting('OUTOFSTOCK_OPENVZ_NY'), array('0', '1'), array('No', 'Yes',));
		$settings->add_dropdown_setting($module, 'Out of Stock', 'outofstock_ssd_openvz_ny', 'Out Of Stock SSD OpenVZ Equinix NY4', 'Enable/Disable Sales Of This Type', $settings->get_setting('OUTOFSTOCK_SSD_OPENVZ_NY'), array('0', '1'), array('No', 'Yes',));
	}

}
