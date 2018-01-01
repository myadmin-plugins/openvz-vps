export PATH="$PATH:/usr/sbin:/sbin:/bin:/usr/bin:";
eval $(grep '^IP_ADDRESS=' /etc/vz/conf/{$vps_vzid}.conf);
if [ "$(echo $IP_ADDRESS | cut -d' ' -f1) = {$newip} ] && [ $(echo $IP_ADDRESS |wc -w) -gt 1 ]; then
	vzctl set {$vps_vzid} --save --ipdel all --ipadd {$newip};
	for IP in $(echo $IP_ADDRESS | cut -d' ' -f2-); do
		vzctl set {$vps_vzid} --save --ipadd {$oldip};
	done;
	vzctl restart {$vps_vzid};
else
	vzctl set {$vps_vzid} --save --setmode restart --ipdel {$oldip} --ipadd {$newip};
fi;
