								echo "export PATH=\"\$PATH:/usr/sbin:/sbin:/bin:/usr/bin:\";\n";
								echo "vzctl set {$vps['vps_vzid']} --save --setmode restart --ipdel " . escapeshellarg($vps['history_old_value']) .";\n";
