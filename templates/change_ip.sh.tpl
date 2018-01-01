{assign var='param' value=','|explode:$param}
export PATH="$PATH:/usr/sbin:/sbin:/bin:/usr/bin:";
eval $(grep '^IP_ADDRESS=' /etc/vz/conf/{$vps_vzid}.conf);
if [ "$(echo $IP_ADDRESS | cut -d' ' -f1) = {$param[1]} ] && [ $(echo $IP_ADDRESS |wc -w) -gt 1 ]; then
	vzctl set {$vps_vzid} --save --ipdel all --ipadd {$param[1]};
	for IP in $(echo $IP_ADDRESS | cut -d' ' -f2-); do
		vzctl set {$vps_vzid} --save --ipadd {$param[0]};
	done;
	vzctl restart {$vps_vzid};
else
	vzctl set {$vps_vzid} --save --setmode restart --ipdel {$param[0]} --ipadd {$param[1]};
fi;
