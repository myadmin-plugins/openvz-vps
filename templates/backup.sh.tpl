								echo "    echo 'Contact support@interserver.net to have your VPS filesystem layout updated to enable backups' | \\\n";
								echo "        mail -s 'Backup Prevented, Contact support To Update VPS' ".$GLOBALS['tf']->accounts->cross_reference($vps['vps_custid']).";\n";
								echo "else\n";
/root/cpaneldirect/vps_swift_backup.sh {$vps_id} {$vps_vzid} {$param1} 2>&1 | tee /root/cpaneldirect/backup{$vps_id}.log && \
curl --connect-timeout 60 --max-time 600 -k -d action=backup_status -d vps_id={$vps_id} https://{$domain}/vps_queue.php || \
curl --connect-timeout 60 --max-time 600 -k -d action=backup_status -d vps_id={$vps_id} https://{$domain}/vps_queue.php;
cat /root/cpaneldirect/backup{$vps_id}.log | mail -v '{$email}';
rm -f /root/cpaneldirect/backup{$vps_id}.log;
								echo "fi;\n";
