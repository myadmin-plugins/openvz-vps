if [ "$(vzlist -H {$vps_vzid} -o layout)" = "simfs" ]; then
	echo 'Contact support@interserver.net to have your VPS filesystem layout updated to enable backups' | \
	mail -s 'Backup Prevented, Contact support To Update VPS' "{$email}";
else
	/root/cpaneldirect/vps_swift_backup.sh {$vps_id} {$vps_vzid} {$param} 2>&1 | tee /root/cpaneldirect/backup{$vps_id}.log && \
	curl --connect-timeout 60 --max-time 600 -k -d action=backup_status -d vps_id={$vps_id} https://{$domain}/vps_queue.php || \
	curl --connect-timeout 60 --max-time 600 -k -d action=backup_status -d vps_id={$vps_id} https://{$domain}/vps_queue.php;
	cat /root/cpaneldirect/backup{$vps_id}.log | mail -v '{$email}';
	rm -f /root/cpaneldirect/backup{$vps_id}.log;
fi;
