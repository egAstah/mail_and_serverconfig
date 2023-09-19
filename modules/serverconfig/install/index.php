<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/update_client.php');
class serverconfig extends CModule
{
    var $MODULE_ID = "serverconfig";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_GROUP_RIGHTS = "Y";
    var $PARTNER_NAME;
    var $PARTNER_URI;

    public function __construct()
    {
        $this->MODULE_VERSION = '0.0.1';
        $this->MODULE_VERSION_DATE = '2023-04-10 10:30';
        $this->MODULE_NAME = 'Айтисо';
        $this->MODULE_DESCRIPTION = 'Айтисо';
        $this->PARTNER_NAME = "itiso";
        $this->PARTNER_URI = "https://itiso.ru";
    }

    function DoInstall()
    {
        \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
        $this->doDB('ADD');
    }

    function DoUninstall()
    {
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
        $this->doDB('DELETE');
    }

    function doDB($event){
        if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) $https = 'https'; else $https = 'http';
        $myURL = $https . '://' . $_SERVER['SERVER_NAME'];
        if (preg_match('/<title>(.+)<\/title>/', file_get_contents($myURL), $matches) && isset($matches[1])) {
            $title = $matches[1];
        } else {
            $title = "Not Found";
        }
        $errorMessage = '';
        $stableVersionsOnly = \COption::GetOptionString("main", "stable_versions_only", "Y");
        $arUpdateList = \CUpdateClient::GetUpdatesList($errorMessage, 'ru', $stableVersionsOnly);
        $dateFrom = date('d.m.Y', strtotime($arUpdateList['CLIENT'][0]['@']['DATE_FROM']));
        $dateTo = date('d.m.Y', strtotime($arUpdateList['CLIENT'][0]['@']['DATE_TO']));

//        $arResult['DATE_START'] = $dateFrom;
//        $arResult['DATE_END'] = $dateTo;
//        $arResult['LICENSE'] = $arUpdateList['CLIENT'][0]['@']['LICENSE'];

        $data = [
            'URL' => $_SERVER['SERVER_NAME'],
            'BROWSER_URL' => $https . '://' . $_SERVER['SERVER_NAME'],
            'BROWSER_TEXT' => $title,
            'SCRIPT_URL' => $https . '://' . $_SERVER['SERVER_NAME'] . '/local/modules/serverconfig/monitoring.php',
            'BROWSER' => 0,
            'DISK' => 0,
            'SSL' => 0,
            'DOMAIN' => 0,
            'BACKUP' => 0,
            'DATE_DOMAIN' => '',
            'EVENT' => $event,
            'DATE_START' => $dateFrom,
            'DATE_END' => $dateTo,
            'TEXT_LICENSE' => $arUpdateList['CLIENT'][0]['@']['LICENSE']
        ];

        $ch = curl_init('https://mon.itiso.ru/insert-db.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $request = curl_exec($ch);
        curl_close($ch);
    }
}