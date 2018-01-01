								echo "export PATH=\"\$PATH:/usr/sbin:/sbin:/bin:/usr/bin:\";\n";
								echo "vzctl stop {$vps['vps_vzid']};\n";
								echo "vzctl destroy {$vps['vps_vzid']};\n";
{vps_create}
