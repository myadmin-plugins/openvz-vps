								echo "export PATH=\"\$PATH:/usr/sbin:/sbin:/bin:/usr/bin:\";\n";
								echo "vzctl set {$vps['vps_vzid']} --quotaugidlimit 0 --save --setmode restart;\n";

