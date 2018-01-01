									echo "export PATH=\"\$PATH:/usr/sbin:/sbin:/bin:/usr/bin:\";\n";
									echo "vzctl set {$vps['vps_vzid']} --save --onboot yes;\n";
									echo "vzctl set {$vps['vps_vzid']} --save --disabled no;\n";
