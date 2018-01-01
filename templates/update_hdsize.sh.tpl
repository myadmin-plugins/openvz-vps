{assign var=soft value=(($settings['slice_hd'] * $vps_slices) + $settings['additional_hd']) * 1024 * 1024}
{assign var=hard value=(1 + ($settings['slice_hd'] * $vps_slices) + $settings['additional_hd']) * 1024 * 1024}
export PATH="$PATH:/usr/sbin:/sbin:/bin:/usr/bin:";
vzctl set {$vps_vzid} --diskspace='{$soft}:{$hard}' --save;
