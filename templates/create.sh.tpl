/root/cpaneldirect/provirted.phar create{if $module == 'quickservers'} --all{/if}{foreach item=$extraIp from=$extraips} --add-ip={$extraIp}{/foreach} --order-id={$id} --client-ip={$clientip} {$vzid|escapeshellarg} {$hostname|escapeshellarg} {$ip} {$vps_os|replace:'.tar.gz':''|escapeshellarg} {($settings.slice_hd * $vps_slices) + $settings.additional_hd} {$vps_slices * $settings.slice_ram} {$vps_slices} {$rootpass|escapeshellarg};