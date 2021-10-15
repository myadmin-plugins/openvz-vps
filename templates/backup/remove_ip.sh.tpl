export PATH="$PATH:/usr/sbin:/sbin:/bin:/usr/bin:";
vzctl set {$vps_vzid} --save --setmode restart --ipdel {$param|escapeshellarg};
