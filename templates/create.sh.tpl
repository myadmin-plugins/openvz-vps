{assign var=ostemplate value=$vps_os|replace:'.tar.gz':''|replace:'.tar.xz':''}
{assign var=wiggle value=1000}
{assign var=dcache_wiggle value=400000}
{assign var=cpus value=$vps_slices}
{if in_array($vps_custid, [2773, 8, 2304])} {* we privileged select few *}}
{assign var=cpuunits value=1500 * 1.5 * $vps_slices}
{assign var=cpulimit value=100 * $vps_slices}
{else}
{assign var=cpuunits value=1500 * $vps_slices}
{assign var=cpulimit value=25 * $vps_slices}
{/if}
{assign var=diskspace value=1024 * 1024 * (($settings.slice_hd * $vps_slices) + $settings.additional_hd)}
{assign var=diskspace_b value=1024 * 1024 * (($settings.slice_hd * $vps_slices) + $settings.additional_hd)}
{assign var=diskinodes value=1280 * 1024 * $vps_slices}
{assign var=diskinodes_b value=1536 * 1024 * $vps_slices}
{* numproc, numtcpsock, and numothersock    barrier = limit *}
{assign var=avnumproc value=300 * $vps_slices}
{assign var=avnumproc_b value=$avnumproc}
{assign var=numproc value=250 * $vps_slices}
{assign var=numproc_b value=$numproc}
{assign var=numtcpsock value=1800 * $vps_slices}
{assign var=numtcpsock_b value=$numtcpsock}
{assign var=numothersock value=1900 * $vps_slices}
{assign var=numothersock_b value=$numothersock}
{* $numfile >= $avnumproc * 32 *}
{assign var=numfile value=32 * $avnumproc}
{* $numfile(bar) = $numfile(limit) *}
{assign var=numfile_b value=$numfile}
{* dcachesize(bar) >= $numfile * 384 *}
{assign var=dcachesize value=384 * $numfile + $dcache_wiggle}
{assign var=dcachesize_b value=384 * $numfile_b + $dcache_wiggle}
{* GARUNTED SLA MEMORY *}
{assign var=vmguarpages value=((256 * $settings.slice_ram) * $vps_slices) - $wiggle}
{assign var=ram value=$settings.slice_ram * $vps_slices}
{* $privvmpages >= $vmguarpages *}
{assign var=privvmpages value=((256 * $settings.slice_ram) * $vps_slices)}
{assign var=privvmpages_b value=$privvmpages + $wiggle}
{assign var=oomguarpages value=$vmguarpages}
{* kmemsize(bar) >= 40kb * avnumproc + dcachesize(lim) *}
{assign var=kmemsize value=(45 * 1024 * $avnumproc + $dcachesize)}
{assign var=kmemsize_b value=(45 * 1024 * $avnumproc_b + $dcachesize_b)}
{* dgramrcvbuf(bar) >= 129kb *}
{assign var=dgramrcvbuf value=2075488 * $vps_slices}
{assign var=dgramrcvbuf_b value=$dgramrcvbuf}
{* tcprcvbuf(bar) >= 64k *}
{assign var=tcprcvbuf value=8958464 * $vps_slices}
{* tcprcvbuf(lim) - tcprcvbuf(bar) >= 2.5KB * numtcpsock *}
{assign var=tcprcvbuf_b value=(2561 * $numtcpsock) + $tcprcvbuf}
{* tcpsndbuf(bar) >= 64k *}
{assign var=tcpsndbuf value=8958464 * $vps_slices}
{* tcpsndbuf(lim) - tcpsndbuf(bar) >= 2.5KB * numtcpsock *}
{assign var=tcpsndbuf_b value=(2561 * $numtcpsock) + $tcpsndbuf}
{* othersockbuf(bar) >= 129kb *}
{assign var=othersockbuf value=775488 * $vps_slices}
{* othersockbuf(lim) - othersockbuf(bar) >= 2.5KB * numtcpsock *}
{assign var=othersockbuf_b value=(2561 * $numothersock) + $othersockbuf}
{assign var=shmpages value=100000 * $vps_slices}
{assign var=shmpages_b value=$shmpages}
{assign var=numpty value=35 + (24 * $vps_slices)}
{assign var=numpty_b value=$numpty}
{assign var=numflock value=8200 * $vps_slices}
{assign var=numflock_b value=8200 * $vps_slices}
{* gives a like 200-300 range *}
{assign var=numiptent value=2000 * $vps_slices}
{assign var=numiptent_b value=$numiptent}
function iprogress() { curl --connect-timeout 60 --max-time 240 -k -d action=install_progress -d progress=$1 -d server={$id} 'https://myvps2.interserver.net/vps_queue.php' < /dev/null > /dev/null 2>&1; }
iprogress 10 &
if [ ! -e /vz/template/cache/{$vps_os} ]; then
  wget -O /vz/template/cache/{$vps_os} {$vps_os};
fi;
iprogress 15 &
if [ "$(echo "{$vps_os}" | grep "xz$")" != "" ]; then
  newtemplate="$(echo "{$vps_os}" | sed s#"\.xz$"#".gz"#g)";
  if [ -e "/vz/template/cache/$newtemplate" ]; then
	echo "Already Exists in .gz, not changing anything";
  else
	echo "Recompressing {$vps_os} to .gz";
	xz -d --keep "/vz/template/cache/{$vps_os}";
	gzip -9 "$(echo "/vz/template/cache/{$vps_os}" | sed s#"\.xz$"#""#g)";
  fi;
  template="$newtemplate";
fi;
iprogress 20 &
if [ "$(uname -i)" = "x86_64" ]; then
  limit=9223372036854775807
else
  limit=2147483647
fi;
if [ "$(vzctl 2>&1 |grep "vzctl set.*--force")" = "" ]; then
  layout=""
  force=""
else
  if [ "$(mount | grep "^$(df /vz |grep -v ^File | cut -d" " -f1)" | cut -d" " -f5)" = "ext3" ]; then
	layout=simfs;
  else
	if [ $(echo "$(uname -r | cut -d\. -f1-2) * 10" | bc -l | cut -d\. -f1) -eq 26 ] && [ $(uname -r | cut -d\. -f3 | cut -d- -f1) -lt 32 ]; then
	  layout=simfs;
	else
	  layout=ploop;
	fi;
  fi;
  layout="--layout $layout";
  force="--force"
fi;
if [ ! -e /etc/vz/conf/ve-vps.small.conf ] && [ -e /etc/vz/conf/ve-basic.conf-sample ]; then
  config="--config basic"
  config=""
else
  config="--config vps.small";
fi;
/usr/sbin/vzctl create {$vzid} --ostemplate {$ostemplate} $layout $config --ipadd {$ip} --hostname {$hostname} 2>&1 || \
{
	/usr/sbin/vzctl destroy {$vzid} 2>&1;
	if [ "$layout" == "--layout ploop" ]; then
	  layout="--layout simfs";
	fi;
	/usr/sbin/vzctl create {$vzid} --ostemplate {$ostemplate} $layout $config --ipadd {$ip} --hostname {$hostname} 2>&1;
};
iprogress 40 &
mkdir -p /vz/root/{$vzid};
/usr/sbin/vzctl set {$vzid} \
 --save $force \
 --cpuunits {$cpuunits} \
 --cpulimit {$cpulimit} \
 --cpus {$cpus} \
 --diskspace {$diskspace}:{$diskspace_b} \
 --diskinodes {$diskinodes}:{$diskinodes_b} \
 --numproc {$numproc}:{$numproc_b} \
 --numtcpsock {$numtcpsock}:{$numtcpsock_b} \
 --numothersock {$numothersock}:{$numothersock_b} \
 --vmguarpages {$vmguarpages}:$limit \
 --kmemsize unlimited:unlimited {* {$kmemsize}:{$kmemsize_b} *} \
 --tcpsndbuf {$tcpsndbuf}:{$tcpsndbuf_b} \
 --tcprcvbuf {$tcprcvbuf}:{$tcprcvbuf_b} \
 --othersockbuf {$othersockbuf}:{$othersockbuf_b} \
 --dgramrcvbuf {$dgramrcvbuf}:{$dgramrcvbuf_b} \
 --oomguarpages {$oomguarpages}:$limit \
 --privvmpages {$privvmpages}:{$privvmpages_b} \
 --numfile {$numfile}:{$numfile_b} \
 --numflock {$numflock}:{$numflock_b} {* unlimited:unlimited *} \
 --physpages 0:$limit \
 --dcachesize {$dcachesize}:{$dcachesize_b} \
 --numiptent {$numiptent}:{$numiptent_b} \
 --avnumproc {$avnumproc}:{$avnumproc_b} \
 --numpty {$numpty}:{$numpty_b} \
 --shmpages {$shmpages}:{$shmpages_b} 2>&1;
if [ -e /proc/vz/vswap ]; then
  /bin/mv -f /etc/vz/conf/{$vzid}.conf /etc/vz/conf/{$vzid}.conf.backup;
#  grep -Ev '^(KMEMSIZE|LOCKEDPAGES|PRIVVMPAGES|SHMPAGES|NUMPROC|PHYSPAGES|VMGUARPAGES|OOMGUARPAGES|NUMTCPSOCK|NUMFLOCK|NUMPTY|NUMSIGINFO|TCPSNDBUF|TCPRCVBUF|OTHERSOCKBUF|DGRAMRCVBUF|NUMOTHERSOCK|DCACHESIZE|NUMFILE|AVNUMPROC|NUMIPTENT|ORIGIN_SAMPLE|SWAPPAGES)=' > /etc/vz/conf/{$vzid}.conf <  /etc/vz/conf/{$vzid}.conf.backup;
  grep -Ev '^(KMEMSIZE|PRIVVMPAGES)=' > /etc/vz/conf/{$vzid}.conf <  /etc/vz/conf/{$vzid}.conf.backup;
  /bin/rm -f /etc/vz/conf/{$vzid}.conf.backup;
  /usr/sbin/vzctl set {$vzid} --ram {$ram}M --swap {$ram}M --save;
  /usr/sbin/vzctl set {$vzid} --reset_ub;
fi;
iprogress 50 &
if [ -e /usr/sbin/vzcfgvalidate ]; then
 /usr/sbin/vzcfgvalidate -r /etc/vz/conf/{$vzid}.conf;
fi;
/usr/sbin/vzctl set {$vzid} --save --devices c:1:3:rw --devices c:10:200:rw --capability net_admin:on;
/usr/sbin/vzctl set {$vzid} --save --nameserver '8.8.8.8 64.20.34.50' --searchdomain interserver.net --onboot yes;
/usr/sbin/vzctl set {$vzid} --save --noatime yes 2>/dev/null;
iprogress 60 &
{foreach item=extraip from=$extraips}
/usr/sbin/vzctl set {$vzid} --save --ipadd {$extraip} 2>&1;
{/foreach}
/usr/sbin/vzctl start {$vzid} 2>&1;
/usr/sbin/vzctl set {$vzid} --save --userpasswd root:{$rootpass} 2>&1;
iprogress 80 &
/usr/sbin/vzctl exec {$vzid} mkdir -p /dev/net;
/usr/sbin/vzctl exec {$vzid} mknod /dev/net/tun c 10 200;
/usr/sbin/vzctl exec {$vzid} chmod 600 /dev/net/tun;
iprogress 90 &
/root/cpaneldirect/vzopenvztc.sh > /root/vzopenvztc.sh && sh /root/vzopenvztc.sh;
/usr/sbin/vzctl set {$vzid} --save --userpasswd root:{$rootpass} 2>&1;
sshcnf="$(find /vz/root/{$vzid}/etc/*ssh/sshd_config 2>/dev/null)";
if [ -e "$sshcnf" ]; then
{if isset($ssh_key)}
 vzctl exec {$vzid} "mkdir -p /root/.ssh;"
 vzctl exec {$vzid} "echo {$ssh_key} >> /root/.ssh/authorized_keys2;"
 vzctl exec {$vzid} "chmod go-w /root; chmod 700 /root/.ssh; chmod 600 /root/.ssh/authorized_keys2;"
{/if}
 if [ "$(grep "^PermitRootLogin" $sshcnf)" = "" ]; then
  echo "PermitRootLogin yes" >> $sshcnf;
  echo "Added PermitRootLogin line in $sshcnf";
  kill -HUP $(vzpid $(pidof sshd) |grep "[[:space:]]{$vzid}[[:space:]]" | sed s#"{$vzid}.*ssh.*$"#""#g);
 elif [ "$(grep "^PermitRootLogin" $sshcnf)" != "PermitRootLogin yes" ]; then
  sed s#"^PermitRootLogin.*$"#"PermitRootLogin yes"#g -i $sshcnf;
  echo "Updated PermitRootLogin line in $sshcnf";
  kill -HUP $(vzpid $(pidof sshd) |grep "[[:space:]]{$vzid}[[:space:]]" | sed s#"{$vzid}.*ssh.*$"#""#g);
 fi;
fi;
if [ "{$ostemplate}" = "centos-7-x86_64-nginxwordpress" ]; then
	vzctl exec {$vzid} /root/change.sh {$rootpass} 2>&1;
fi;

if [ "{$ostemplate}" = "ubuntu-15.04-x86_64-xrdp" ]; then
	/usr/sbin/vzctl set {$vzid} --save --userpasswd kvm:{$rootpass} 2>&1;
fi;
iprogress 100 &
