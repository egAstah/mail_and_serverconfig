<?php

namespace Itiso\ModuleConfig;

use Bitrix\Main;

define('TELEGRAM_TOKEN', '1864232820:AAGYjIQrIxNsnZRk9MtW0eGX6tl05fY1Wi0');
define('TELEGRAM_CHATID', '-518774102');

class ServerConfig
{
    // Список модулей и их версий
    public static function listModuleVersion()
    {
        $errorMessage = '';
        $stableVersionsOnly = \COption::GetOptionString("main", "stable_versions_only", "Y");
//        $arUpdateList = \CUpdateClient::GetUpdatesList($errorMessage, LANG, $stableVersionsOnly);
//        $dateFrom = date('d.m.Y', strtotime($arUpdateList['CLIENT'][0]['@']['DATE_FROM']));
//        $dateTo = date('d.m.Y', strtotime($arUpdateList['CLIENT'][0]['@']['DATE_TO']));

//        $result['DATE_LICENSE'] = $dateFrom . ' - ' . $dateTo;

        $rsInstalledModules = \CModule::GetList();

        while ($ar = $rsInstalledModules->Fetch()) {
            $info = \CModule::CreateModuleObject($ar['ID']);
            $result['MODULES'][] = [
                'ID' => $ar['ID'],
                'VERSION' => $info->MODULE_VERSION
            ];
        }
        return $result;
    }

    // Информация о домене
    public static function infoDomain($domain)
    {
        $server = 'whois.tcinet.ru';
        $tld = explode('.', $domain);
        $tld = array_pop($tld);

        switch ($tld) {
            case 'net':
            case 'com':
                $server = 'whois.verisign-grs.com';
                break;
            case 'org':
                $server = 'whois.pir.org';
                break;
            //case 'pet': $server = 'whois.nic.pet'; break;
            //case 'pet': $server = 'whois.donuts.co'; break;
            //case 'pet': $server = 'whois.publicinterestregistry.net'; break;
            //case 'pet': $server = 'whois.nic.net'; break;
            //case 'pet': $server = 'whois.domain-registry.pet'; break;
            //case 'pet': $server = 'whois.nic.zm'; break;
            //case 'pet': $server = 'whois.crsnic.net'; break;
            case 'pet':
                $server = 'https://whois.uniregistry.net/whois/?keyword=';
                break;
        }

        $host = $domain;

        $socket = fsockopen($server, 43);
        if ($socket) {
            fputs($socket, $host . PHP_EOL);

            $arr = [];
            while (!feof($socket)) {
                $arr[] = fgets($socket, 128);
            }
            fclose($socket);
            $resultDate = '';
            foreach ($arr as $item) {
                $dateDomain = explode(':', $item);
                if ($dateDomain[0] == 'free-date') {
                    $resultDate = $dateDomain[1];
                }
            }
            $resultDate = date('d.m.Y', strtotime($resultDate));
            $nowDate = date('d.m.Y');
            $datediff = strtotime($resultDate) - strtotime($nowDate);
            $dayToEnd = round($datediff / (60 * 60 * 24));
            if ($dayToEnd <= 30) {
                $result = [
                    "VAL" => 'Домен ' . $domain . ' - заканчивается ' . date('d.m.Y', strtotime($resultDate)),
                    'DATE' => date('d.m.Y', strtotime($resultDate))
                ];
            } else {
                $result = [
                    "VAL" => 'Домен ' . $domain . ' доступен до ' . date('d.m.Y', strtotime($resultDate)),
                    'DATE' => date('d.m.Y', strtotime($resultDate))
                ];
            }
            return $result;
        }
    }

    // Последний бэкап
    public static function lastBackup()
    {
        $dir = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/backup';
        $result = scandir($dir);
        if ($result) {
            unset($result[count($result) - 1]);
            $lastDate = explode('_', $result[count($result) - 1])[0];
            $year = substr($lastDate, 0, -4);
            $month = substr($lastDate, 4, 2);
            $day = substr($lastDate, 6, 2);
            $date = $day . '.' . $month . '.' . $year;

            $resultDate = date('d.m.Y', strtotime($date));
            $nowDate = date('d.m.Y');
            $datediff = strtotime($resultDate) - strtotime($nowDate);
            $dayToEnd = round($datediff / (60 * 60 * 24));
            if ($dayToEnd >= 7) {
                return '1';
            } else {
                return '2';
            }
        } else {
            return 'Резервных копий нет';
        }

        //header('Content-Type: application/json');
//$arResult = [];
//
//if($_REQUEST['framework'] == 'bitrix' && $_REQUEST['type'] == 'backup') {
//    define("NOT_CHECK_PERMISSIONS", true);
//    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
//
//    $queryEventLogs = CEventLog::GetList(["ID" => "DESC"], ["AUDIT_TYPE_ID" => 'BACKUP_%'], ["nTopCount" => 1]);
//    if ($eventLog = $queryEventLogs -> fetch()) {
//        $arResult['backup'] = ['date' => $eventLog['TIMESTAMP_X'], 'type' => $eventLog['AUDIT_TYPE_ID'], 'desc' => $eventLog['DESCRIPTION']];
//    }
//}
    }

    // Срок действия SSL сертификата
    public static function checkSSL($domain)
    {
        $url = 'http://' . $domain;
        $orignalParse = parse_url($url, PHP_URL_HOST);
        $get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
        $read = stream_socket_client("ssl://" . $orignalParse . ":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);
        $cert = stream_context_get_params($read);
        $certInfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);

        if($certInfo){
            $validTo = date(DATE_RFC2822, $certInfo['validTo_time_t']);
            $date = new \DateTime($validTo);
            $result = [
                'VAL' => 'Сертификат домена ' . $domain . ' заканчивается ' . $date->format('d.m.Y H:i:s'),
                'DATE' => $date->format('d.m.Y H:i:s')
            ];
        }else{
            $result = [
                'VAL' => 'Сертификат домена ' . $domain . ' не найден'
            ];
        }

        return $result;
    }

    // Свободное место на диске
    public static function diskSize()
    {
        $disks = [
            "/",
        ];

        foreach ($disks as $disk) {
            $df = disk_free_space($disk) / 1024 / 1024 / 1024;
            $arResult['disk'][$disk] = round($df, 2) . ' Гб';
        }

        return $arResult;
    }

    // Проверка доступности домена
    public static function isDomainAvailible($domain)
    {
        if (!filter_var('http://' . $domain, FILTER_VALIDATE_URL)) {
            return false;
        }

        $curlInit = curl_init($domain);
        curl_setopt($curlInit, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curlInit, CURLOPT_HEADER, true);
        curl_setopt($curlInit, CURLOPT_NOBODY, true);
        curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curlInit);
        curl_close($curlInit);
        if ($response) {
            $result = ['VAL' => 'Домен - ' . $domain . ' доступен'];
        } else {
            $result = ['VAL' => 'Домен - ' . $domain . ' недоступен'];
        }
        return $result;
    }

    // Отправка сообщений в телегу
    public static function messageToTelegram($type, $domain, $status, $ar = [])
    {

        $icon = "\xE2\x9C\x85";
        if ($status <> 0)
            $icon = "\xE2\x9D\x8C";

        if ($type == 'APP' && $status <> 0) {
            $icon = "\xe2\x9d\x97 \xe2\x9d\x97 \xe2\x9d\x97"; // ❗ ❗ ❗
        }

        $text = $icon . " " . $type . " " . $domain;

        if ($ar) { // Если передадим массив, то его отправим
            $text = "";
            foreach ($ar as $item) {
                if ($text) {
                    $text .= "\n";
                    if (empty($item["STATUS"])) {
                        $icon_new = $icon;
                    } else {
                        $icon_new = "\xE2\x9C\x85";
                        if ($item["STATUS"] <> 0)
                            $icon_new = "\xE2\x9D\x8C";
                    }
                    $text .= $icon_new . " "; // Первая строка - заголовок, поэтому иконку со второй добавляем
                }

                $text .= $item["VAL"];
            }
        }

        if ($type == 'Disk') {
            $text .= "\nStatus: $status";
        }

        $ch = curl_init();
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_URL => 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/sendMessage',
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_POSTFIELDS => array(
                    'chat_id' => TELEGRAM_CHATID,
                    'text' => $text,
                    'parse_mode' => "markdown",
                ),
            )
        );
        curl_exec($ch);
    }
    public static function generateCode(){
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = substr(str_shuffle($permitted_chars), 0, 8);
        return $code;
    }
}