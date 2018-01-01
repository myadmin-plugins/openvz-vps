								echo "export PATH=\"\$PATH:/usr/sbin:/sbin:/bin:/usr/bin:\";\n";
								echo "vzctl set {$vps['vps_vzid']} --diskspace='{$soft}:{$hard}' --save;\n";
