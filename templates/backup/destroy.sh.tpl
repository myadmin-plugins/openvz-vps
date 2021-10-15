export PATH="$PATH:/usr/sbin:/sbin:/bin:/usr/bin:";
vzctl set {$vps_vzid} --save --onboot no;
vzctl set {$vps_vzid} --save --disabled yes;
vzctl stop {$vps_vzid};
vzctl destroy {$vps_vzid};
