/root/cpaneldirect/provirted.phar update --hd={($settings.slice_hd * $vps_slices) + $settings.additional_hd} --ram={$vps_slices * $settings.slice_ram} --cpu={((($vps_slices - 2) / 2) + 1)|ceil} --cgroups={$vps_slices} {if $vps_vzid == "0"}{$vps_id}{else}{$vps_vzid|escapeshellarg}{/if};
