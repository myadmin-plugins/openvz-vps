export PATH="$PATH:/usr/sbin:/sbin:/bin:/usr/bin:";
vzctl set {$vps_vzid} --save --onboot yes;
vzctl set {$vps_vzid} --save --disabled no;
