<?php

define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/update_client.php");

header('Content-Type: application/json');
$arResult = [];

if($_REQUEST['framework'] == 'bitrix' && $_REQUEST['type'] == 'backup') {
    $queryEventLogs = CEventLog::GetList(["ID" => "DESC"], ["AUDIT_TYPE_ID" => 'BACKUP_%'], ["nTopCount" => 1]);
    if ($eventLog = $queryEventLogs -> fetch()) {
        $arResult['backup'] = ['date' => $eventLog['TIMESTAMP_X'], 'type' => $eventLog['AUDIT_TYPE_ID'], 'desc' => $eventLog['DESCRIPTION']];
    }
}

if($_REQUEST['type'] == 'disk') {
    $disks = [
        "/",
    ];
    $disk_sizes = [];

    foreach ($disks as $disk) {
        $df = disk_free_space($disk) / 1024 / 1024 / 1024;
        $arResult['disk'][$disk] = round($df, 2);
    }
}

if($_REQUEST['type'] == 'modules'){
    $errorMessage = '';
    $stableVersionsOnly = \COption::GetOptionString("main", "stable_versions_only", "Y");
    $arUpdateList = \CUpdateClient::GetUpdatesList($errorMessage, LANG, $stableVersionsOnly);
    $dateFrom = date('d.m.Y', strtotime($arUpdateList['CLIENT'][0]['@']['DATE_FROM']));
    $dateTo = date('d.m.Y', strtotime($arUpdateList['CLIENT'][0]['@']['DATE_TO']));

    $arResult['DATE_LICENSE'] = 'c ' . $dateFrom . ' по ' . $dateTo;

    $rsInstalledModules = \CModule::GetList();

    while ($ar = $rsInstalledModules->Fetch()) {
        $info = \CModule::CreateModuleObject($ar['ID']);
        $arResult['MODULES'][] = [
            'ID' => $ar['ID'],
            'VERSION' => $info->MODULE_VERSION
        ];
    }
}

echo json_encode($arResult);
?>