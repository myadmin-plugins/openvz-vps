									echo "export PATH=\"\$PATH:/usr/sbin:/sbin:/bin:/usr/bin:\";\n";
									echo "vzctl set {$vps['vps_vzid']} --save --onboot no;\n";
									echo "vzctl set {$vps['vps_vzid']} --save --disabled yes;\n";
									echo "vzctl stop {$vps['vps_vzid']};\n";
									echo "vzctl destroy {$vps['vps_vzid']};\n";
