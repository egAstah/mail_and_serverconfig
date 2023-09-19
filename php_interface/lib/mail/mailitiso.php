<?php

namespace lib;

use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
use Bitrix\Main\FileTable;

Loader::includeModule("highloadblock");
Loader::includeModule("main");

class MailItiso
{
    public static function createMessage($arFields)
    {
        $entity = HL\HighloadBlockTable::compileEntity(1);
        $entity_data_class = $entity->getDataClass();
        $id = $entity_data_class::add($arFields);
        return $id;
    }

    public static function getList($filter)
    {
        global $USER;
        $entity = HL\HighloadBlockTable::compileEntity(1);
        $entity_data_class = $entity->getDataClass();

        $rsData = $entity_data_class::getList(array(
            "select" => ['*'],
            "order" => ['UF_DATE' => 'DESC'],
            "filter" => $filter
        ));

        while ($arData = $rsData->Fetch()) {
            $rsUser = $USER->GetByID($arData['UF_TO']);
            $arUser = $rsUser->Fetch();
            $userNameTo = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'];
            $userToId = $arUser['ID'];
            $userToEmail = $arUser['EMAIL'];

            $rsUser = $USER->GetByID($arData['UF_FROM']);
            $arUser = $rsUser->Fetch();
            $userNameFrom = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'];
            $userFromEmail = $arUser['EMAIL'];
            $userFromId = $arUser['ID'];


            if ($arData['UF_DATE'] != '')
                $date = $arData['UF_DATE']->format("d.m.Y H:i");
            else $date = '';
            if ($arData['UF_DATEREAD'] != '')
                $dateRead = $arData['UF_DATEREAD']->format("d.m.Y H:i:s");
            else $dateRead = 'не прочитано';

            $result[] = [
                'id' => $arData['ID'],
                'date' => $date,
                'user_to' => $userNameTo,
                'user_to_id' => $userToId,
                'user_to_email' => $userToEmail,
                'user_from' => $userNameFrom,
                'user_from_id' => $userFromId,
                'user_from_email' => $userFromEmail,
                'subject' => $arData['UF_SUBJECT'],
                'message' => $arData['UF_MESSAGE'],
                'dateRead' => $dateRead
            ];
        }
        return $result;
    }

    public static function readMessage($id, $user)
    {
        global $USER;
        $entity = HL\HighloadBlockTable::compileEntity(1);
        $entity_data_class = $entity->getDataClass();

        $rsData = $entity_data_class::getList(array(
            "select" => ['*'],
            "order" => ['UF_DATE' => 'DESC'],
            "filter" => [
                'ID' => $id
            ]
        ));

        while ($arData = $rsData->Fetch()) {

            $rsUser = $USER->GetByID($arData['UF_TO']);
            $arUser = $rsUser->Fetch();
            $userNameTo = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'];
            $userToId = $arUser['ID'];
            $userToEmail = $arUser['EMAIL'];

            $rsUser = $USER->GetByID($arData['UF_FROM']);
            $arUser = $rsUser->Fetch();
            $userNameFrom = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'];
            $userFromEmail = $arUser['EMAIL'];
            $userFromId = $arUser['ID'];
            if ($arData['UF_DATEREAD'] != '')
                $dateRead = $arData['UF_DATEREAD']->format("d.m.Y H:i:s");
            else $dateRead = 'не прочитано';
            if ($arData['UF_DATE'] != '')
                $date = $arData['UF_DATE']->format("d.m.Y H:i");
            else $date = '';

            $dataCurl = [
                'event' => 'decrypt',
                'message' => $arData['UF_MESSAGE'],
                'email' => hash('md5', $userToEmail)
            ];

            $ch = curl_init( 'http://62.109.7.153/certs/request.php' );
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataCurl, JSON_UNESCAPED_UNICODE));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            $request = curl_exec($ch);
            curl_close($ch);
            $message = json_decode($request, JSON_UNESCAPED_UNICODE)['data'];

            $result = [
                'id' => $arData['ID'],
                'dateread' => $dateRead,
                'user_to' => $userNameTo,
                'user_to_id' => $userToId,
                'user_to_email' => $userToEmail,
                'user_from' => $userNameFrom,
                'user_from_email' => $userFromEmail,
                'user_from_id' => $userFromId,
                'subject' => $arData['UF_SUBJECT'],
                'message' => $message,
                'date' => $date,
                'listUser' => $arData['UF_USERS_COPY']
            ];
        }

        if ($user == $result['user_to_id']) {
            if ($result['dateread'] == 'не прочитано') {
                $data = [
                    'UF_DATEREAD' => date('d.m.Y H:i:s')
                ];
                $entity_data_class::update($id, $data);
            }
        }
        return $result;
    }

    public static function deleteMessage($id)
    {
        $entity = HL\HighloadBlockTable::compileEntity(1);
        $entity_data_class = $entity->getDataClass();
        $data = [
            'UF_DELETE' => 'Y'
        ];
        $entity_data_class::update($id, $data);
    }

    public static function answerAll($id)
    {
        $entity = HL\HighloadBlockTable::compileEntity(1);
        $entity_data_class = $entity->getDataClass();

        $answerAll = $entity_data_class::getList(array(
            "select" => ['*'],
            "order" => ['UF_DATE' => 'DESC'],
            "filter" => [
                'UF_ID_MAIN' => $id
            ]
        ));
        while ($ob = $answerAll->Fetch()) {
            $result = $ob['UF_USERS_COPY'];
        }
        return $result;
    }

    public static function countMail($user)
    {
        $entity = HL\HighloadBlockTable::compileEntity(1);
        $entity_data_class = $entity->getDataClass();

        $filterInbox = [
            'UF_TO' => $user,
            '!UF_FROM' => $user,
            'UF_DELETE' => 'N',
            'UF_DRAFTS' => 'N',
            'UF_DATEREAD' => '',
            'UF_COPY' => 'N'
        ];
        $inbox = $entity_data_class::getList(array(
            "select" => ['*'],
            "order" => ['UF_DATE' => 'DESC'],
            "filter" => $filterInbox
        ));
        $countInbox = 0;
        while ($arData = $inbox->Fetch()) {
            $countInbox++;
        }
        if($countInbox == 0) $countInbox = '';

        $filterDrafts = [
            'UF_FROM' => $user,
            'UF_DRAFTS' => 'Y',
            'UF_DELETE' => 'N'
        ];
        $drafts = $entity_data_class::getList(array(
            "select" => ['*'],
            "order" => ['UF_DATE' => 'DESC'],
            "filter" => $filterDrafts
        ));
        $countDrafts = 0;
        while ($arData = $drafts->Fetch()) {
            $countDrafts++;
        }
        if($countDrafts == 0) $countDrafts = '';

        $result = [
            'inbox' => $countInbox,
            'drafts' => $countDrafts
        ];

        return $result;
    }

    public static function curlRequest($data){
        $ch = curl_init( 'http://62.109.7.153/certs/request.php' );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }
}