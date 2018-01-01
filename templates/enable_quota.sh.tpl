export PATH="$PATH:/usr/sbin:/sbin:/bin:/usr/bin:";
vzctl set {$vps_vzid} --quotaugidlimit 200 --save --setmode restart;
