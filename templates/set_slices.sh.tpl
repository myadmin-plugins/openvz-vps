{assign var=wiggle value=1000}
{assign var=dcache_wiggle value=400000}
{assign var=cpus value=$vps_slices}
{if in_array($vps_custid, [2773, 8, 2304])}
{assign var=cpuunits value=1500 * 1.5 * $vps_slices}
{assign var=cpulimit value=100 * $vps_slices}
{else}
{assign var=cpuunits value=1500 * $vps_slices}
{assign var=cpulimit value=25 * $vps_slices}
{/if}
{assign var=diskspace value=1040000 * (($settings.slice_hd * $vps_slices) + $settings.additional_hd)}
{assign var=diskspace_b value=1040000 * (($settings.slice_hd * $vps_slices) + $settings.additional_hd)}
{assign var=diskinodes value=800000 * $vps_slices}
{assign var=diskinodes_b value=1000000 * $vps_slices}
{assign var=avnumproc value=200 * $vps_slices}
{assign var=avnumproc_b value=$avnumproc}
{assign var=numproc value=150 * $vps_slices}
{assign var=numproc_b value=$numproc}
{assign var=numtcpsock value=300 * $vps_slices}
{assign var=numtcpsock_b value=$numtcpsock}
{assign var=numothersock value=500 * $vps_slices}
{assign var=numothersock_b value=$numothersock}
{assign var=numfile value=32 * $avnumproc}
{assign var=numfile_b value=$numfile}
{assign var=dcachesize value=384 * $numfile + $dcache_wiggle}
{assign var=dcachesize_b value=384 * $numfile_b + $dcache_wiggle}
{assign var=vmguarpages value=((256 * $settings.slice_ram) * $vps_slices) - $wiggle}
{assign var=ram value=$settings.slice_ram * $vps_slices}
{assign var=privvmpages value=((256 * $settings.slice_ram) * $vps_slices)}
{assign var=privvmpages_b value=$privvmpages + $wiggle}
{assign var=oomguarpages value=$vmguarpages}
{assign var=kmemsize value=(45 * 1024 * $avnumproc + $dcachesize)}
{assign var=kmemsize_b value=(45 * 1024 * $avnumproc_b + $dcachesize_b)}
{assign var=dgramrcvbuf value=262 * 1024 * $vps_slices}
{assign var=dgramrcvbuf_b value=$dgramrcvbuf}
{assign var=tcprcvbuf value=958464 * $vps_slices}
{assign var=tcprcvbuf_b value=(2561 * $numtcpsock) + $tcprcvbuf}
{assign var=tcpsndbuf value=958464 * $vps_slices}
{assign var=tcpsndbuf_b value=(2561 * $numtcpsock) + $tcpsndbuf}
{assign var=othersockbuf value=$dgramrcvbuf}
{assign var=othersockbuf_b value=(2561 * $numothersock) + $othersockbuf}
{assign var=shmpages value=100000 * $vps_slices}
{assign var=shmpages_b value=$shmpages}
{assign var=numpty value=35 + (24 * $vps_slices)}
{assign var=numpty_b value=$numpty}
{assign var=numflock value=300 * $vps_slices}
{assign var=numflock_b value=500 * $vps_slices}
{assign var=numiptent value=250 * $vps_slices}
{assign var=numiptent_b value=$numiptent}
if [ "$(uname -i)" = "x86_64" ]; then
  limit=9223372036854775807
else
  limit=2147483647
fi;
vmd5="$(md5sum /etc/vz/conf/{$vps_vzid}.conf | cut -d" " -f1)";
/usr/sbin/vzctl set {$vps_vzid} \
 --save \
 --force \
 --cpuunits {$cpuunits} \
 --cpus {$cpus} \
 --diskspace {$diskspace}:{$diskspace_b} \
 --diskinodes {$diskinodes}:{$diskinodes_b} \
 --numproc {$numproc}:{$numproc_b} \
 --numtcpsock {$numtcpsock}:{$numtcpsock_b} \
 --numothersock {$numothersock}:{$numothersock_b} \
 --vmguarpages {$vmguarpages}:{literal}${limit}{/literal} \
 --kmemsize unlimited:unlimited \
 --tcpsndbuf {$tcpsndbuf}:{$tcpsndbuf_b} \
 --tcprcvbuf {$tcprcvbuf}:{$tcprcvbuf_b} \
 --othersockbuf {$othersockbuf}:{$othersockbuf_b} \
 --dgramrcvbuf {$dgramrcvbuf}:{$dgramrcvbuf_b} \
 --oomguarpages {$oomguarpages}:{literal}${limit}{/literal} \
 --privvmpages {$privvmpages}:{$privvmpages_b} \
 --numfile {$numfile}:{$numfile_b} \
 --numflock {$numflock}:{$numflock_b} \
 --physpages 0:{literal}${limit}{/literal} \
 --dcachesize {$dcachesize}:{$dcachesize_b} \
 --numiptent {$numiptent}:{$numiptent_b} \
 --avnumproc {$avnumproc}:{$avnumproc_b} \
 --numpty {$numpty}:{$numpty_b}  \
 --shmpages {$shmpages}:{$shmpages_b};
 if [ -e /proc/vz/vswap ]; then
  /bin/mv -f /etc/vz/conf/{$vps_vzid}.conf /etc/vz/conf/{$vps_vzid}.conf.backup;
#  grep -Ev '^(KMEMSIZE|LOCKEDPAGES|PRIVVMPAGES|SHMPAGES|NUMPROC|PHYSPAGES|VMGUARPAGES|OOMGUARPAGES|NUMTCPSOCK|NUMFLOCK|NUMPTY|NUMSIGINFO|TCPSNDBUF|TCPRCVBUF|OTHERSOCKBUF|DGRAMRCVBUF|NUMOTHERSOCK|DCACHESIZE|NUMFILE|AVNUMPROC|NUMIPTENT|ORIGIN_SAMPLE|SWAPPAGES)=' > /etc/vz/conf/{$vps_vzid}.conf <  /etc/vz/conf/{$vps_vzid}.conf.backup;
  grep -Ev '^(KMEMSIZE|PRIVVMPAGES)=' > /etc/vz/conf/{$vps_vzid}.conf <  /etc/vz/conf/{$vps_vzid}.conf.backup;
  /bin/rm -f /etc/vz/conf/{$vps_vzid}.conf.backup;
  /usr/sbin/vzctl set {$vps_vzid} --ram {$ram}M --swap {$ram}M --save;
  /usr/sbin/vzctl set {$vps_vzid} --reset_ub;
fi;

if [ -e /usr/sbin/vzcfgvalidate ]; then
  /usr/sbin/vzcfgvalidate -r /etc/vz/conf/{$vps_vzid}.conf >/dev/null 2>&1;
fi;
vmd52="$(md5sum /etc/vz/conf/{$vps_vzid}.conf | cut -d" " -f1)";
echo "Original MD5      {literal}${vmd5}{/literal}";
echo "New MD5           {literal}${vmd52}{/literal}";
if [ ! "{literal}${vmd5}{/literal}" = "{literal}${vmd52}{/literal}" ]; then
  echo "          Config File Changed, Restarting VPS";
else
  echo "          No Config File Changes";
fi;
