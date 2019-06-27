<?php

namespace App\Services\AnsibleDynamicInventory;

use App\Services\CentreonModel\HostService;
use App\Services\CentreonModel\HostTemplateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

use App\Model\Eloquent\centreon\Host;
use App\Model\Eloquent\centreon_storage\Host as StorageHost;
use App\Model\Eloquent\centreon\HostGroup;
use App\Model\Eloquent\centreon_storage\HostGroup as StorageHostGroup;

class AnsibleInventoryService {

    const inventory_cache_key = 'ansible_host_inventory';

    public function reloadCacheHostInventory ($includeMeta = false) {
        $inventory = $this->getFormattedHostTemplateListAsGroupList($includeMeta);
        Cache::put(self::inventory_cache_key, $inventory, 60);
    }

    public function getHostInventory ($includeMeta = false) {
        $inventory = Cache::get(self::inventory_cache_key, null);

        if (!isset($inventory)) {
            $inventory = $this->getFormattedHostTemplateListAsGroupList($includeMeta);
            Cache::put(self::inventory_cache_key, $inventory, 60);
        }

        return $inventory;
    }

    public function getFormattedHostTemplateListAsGroupList ($includeMeta = false) {
        $htSvc = new HostTemplateService();

        $hostTemplateList = $htSvc->getList();

        $outputList = [];
        foreach ($hostTemplateList as $ht) {
            $outputList[$ht->host_name] = $this->formatHostTemplateAsGroup($ht);
        }

        if ($includeMeta)
            $outputList['_meta']['hostvars'] = $this->getFormattedHostList();

        return $outputList;
    }

    public function formatHostTemplateAsGroup (Host $ht) {
        $svc = new HostTemplateService();

        $hostList = $svc->getAssociatedChildHostNames($ht);
        $childTemplateList = $svc->getAssociatedChildHostTemplateNames($ht);
        $macroList = [];
        foreach ($ht->macros as $macro) {
            $name = $macro->host_macro_name;
            $name = str_replace('$_HOST', '', $name);
            $name = str_replace('$', '', $name);
            $name = strtolower($name);
            $macroList[$name] = $macro->host_macro_value;
        }

        $output = [
            'hosts' => $hostList,
            'children' => $childTemplateList,
        ];

        if (! empty($macroList))
            $output['vars'] = $macroList;

        return $output;
    }

    public function getFormattedHostList () {
        $hSvc = new HostService();

        $hostList = $hSvc->getList();

        $outputList = [];
        foreach ($hostList as $h) {
            $outputList[$h->host_name] = $this->formatHost($h);
        }

        return $outputList;
    }

    public function formatHost (Host $h) {
        $hostProps = [
            'ansible_host' => $h->host_address
        ];

        $hostMacroProps = [];
        foreach ($h->macros as $macro) {
            //echo 'JB';

            $name = $macro->host_macro_name;
            $name = str_replace('$_HOST', '', $name);
            $name = str_replace('$', '', $name);
            $name = strtolower($name);
            $hostMacroProps[$name] = $macro->host_macro_value;
        }

        $output = array_merge($hostProps, $hostMacroProps);

        return $output;
    }

}