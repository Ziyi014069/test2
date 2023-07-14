<?php

namespace App\Packages;

//package
use App\Packages\Common;
//Model
use App\Models\Admin_log as AdminLogModel;

class AdminLog
{
    private $Common;

    public function __construct()
    {
        // parent::__construct();
        $this->Common = new Common;
    }

    public function fCreate($page,$operate,$operateId = null,$dataBeforeModification = [],$dataAfterModification = [] ,$name = 'system'){
        $aData = [
            'page' => $page,
            'operate' => $operate,
            'operateId' => $operateId,
            'dataBeforeModification' => json_encode($dataBeforeModification) ,
            'dataAfterModification' => json_encode($dataAfterModification),
            'creator'=> $name,
            'ipOfCreator'=> $_SERVER['REMOTE_ADDR'],
            'lastUpdater'=> $name,
            'ipOfLastUpdater'=> $_SERVER['REMOTE_ADDR'],
        ];


        AdminLogModel::create($aData);
    }
}
