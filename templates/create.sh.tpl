/root/cpaneldirect/provirted.phar create{if $module == 'quickservers'} --all{/if}{foreach item=$extraIp from=$extraips} --add-ip={$extraIp}{/foreach} --order-id={$id} --client-ip={$clientip} --password={$rootpass} {if $vps_vzid == "0"}{$vps_id}{else}{$vps_vzid}{/if} {$hostname} {$ip} {$vps_os|replace:'.tar.gz':''} {($settings.slice_hd * $vps_slices) + $settings.additional_hd} {$vps_slices * $settings.slice_ram} {((($vps_slices - 2) / 2) + 1)|ceil};
