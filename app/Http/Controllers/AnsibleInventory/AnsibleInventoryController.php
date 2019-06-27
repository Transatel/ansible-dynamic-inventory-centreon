<?php

namespace App\Http\Controllers\AnsibleInventory;

#use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Model\Eloquent\centreon\Host;
use App\Services\AnsibleDynamicInventory\AnsibleInventoryService;

class AnsibleInventoryController extends Controller {

    public function reloadInventoryCache () {
        $svc = new AnsibleInventoryService();
        $svc->reloadCacheHostInventory(true);
        return response()->make();
    }

    public function getCompleteInventory () {
        $svc = new AnsibleInventoryService();

        $inventory = $svc->getHostInventory(true);

        if (empty($inventory))
            return response()->make('{}');

        return response()->json($inventory);
    }

    public function getHostDetails ($name) {
        $svc = new AnsibleInventoryService();

        $host = Host::whereHostName($name)->first();

        if (! isset($host))
            return response()->make('{}');

        $hostVars = $svc->formatHost($host);

        return response()->json($hostVars);
    }
}