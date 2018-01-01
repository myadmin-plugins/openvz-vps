								echo "export PATH=\"\$PATH:/usr/sbin:/sbin:/bin:/usr/bin:\";
if [ \"\$(grep -E \"^IP_ADDRESS.*[ \\\"]+{$ipreg}[ \\\"]+\" /etc/vz/conf/{$vps['vps_vzid']}.conf)\" = \"\" ]; then
	vzctl set {$vps['vps_vzid']} --save --setmode restart --ipadd {$ipesc};
fi;\n";
