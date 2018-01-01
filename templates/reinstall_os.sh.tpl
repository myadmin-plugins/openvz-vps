export PATH="$PATH:/usr/sbin:/sbin:/bin:/usr/bin:";
vzctl stop {$vps_vzid};
vzctl destroy {$vps_vzid};
{vps_create}
