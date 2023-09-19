<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;

Loader::includeModule("main");

global $USER;
$mailClass = new lib\MailItiso;

function fileArr($files)
{
    $arr = [];
    $diff = count($files['file']) - count($files['file'], COUNT_RECURSIVE);
    if ($diff == 0) {
        $arr = array($files['file']);
    } else {
        foreach ($files['file'] as $k => $l) {
            foreach ($l as $i => $v) {
                $arr[$i][$k] = $v;
            }
        }
    }
    return $arr;
}

switch ($_POST['event']) {
    case 'send-message':
        $userIdMain = [];
        $filter = ['EMAIL' => trim($_POST['usersSent'])];
        $rsUsers = CUser::GetList(($by = "personal_country"), ($order = "desc"), $filter);
        while ($ob = $rsUsers->Fetch()) {
            $userIdMain[] = $ob['ID'];
        }
        $newUser = 0;
        $newPassword = '';
        if (count($userIdMain) < 1) {
            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $code = substr(str_shuffle($permitted_chars), 0, 8);
            $newPassword = $code;
            $user = new CUser;
            $arField = [
                'EMAIL' => trim($_POST['usersSent']),
                'LOGIN' => trim($_POST['usersSent']),
                'PASSWORD' => $code,
                'CONFIRM_PASSWORD' => $code,
                'ACTIVE' => 'Y'
            ];
            $arRes = $user->Add($arField);
            $userIdMain[] = $arRes;
            $newUser = 1;
        }

        if ($_POST['subject'] == '') $_POST['subject'] = 'Без темы';
        foreach ($userIdMain as $item) {
            $idUser = $item;
            $rsUser = $USER->GetByID($idUser);
            $arUser = $rsUser->Fetch();
            $userToEmail = $arUser['EMAIL'];
            $userFromName = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'];
            $rsUser = $USER->GetByID($USER->GetID());
            $arUser = $rsUser->Fetch();
            $userFromEmail = $arUser['EMAIL'];
            $id = $mailClass::createMessage(['UF_FROM' => '']);
            $hashFolder = substr(hash('md5', $userToEmail), 0, 8);
            mkdir('/home/bitrix/ext_www/trackonlive.ru/upload/messages/' . $hashFolder, 0777, true);
            $arFile = fileArr($_FILES);
            foreach ($arFile as $item) {
                $delFile[] = $item['name'];
                move_uploaded_file($item['tmp_name'], '/home/bitrix/ext_www/trackonlive.ru/upload/messages/' . $hashFolder . '/' . $item['name']);
            }

            $dataCurl = [
                'event' => 'encrypt',
                'message' => $_POST['message'],
                'email' => hash('md5', $userToEmail),
                'file' => $_FILES,
                'subject' => $_POST['subject'],
                'date' => date('d.m.Y H:i:s'),
                'from' => $userFromEmail,
                'to' => $userToEmail,
                'id' => $id->GetID()
            ];
            dataCurl($dataCurl);

//            $dataCurl = [
//                'event' => 'encrypt',
//                'message' => $_POST['message'],
//                'email' => hash('md5', $userFromEmail),
//                'file' => $_FILES,
//                'subject' => $_POST['subject'],
//                'date' => date('d.m.Y H:i:s'),
//                'from' => $userFromEmail,
//                'to' => $userToEmail,
//                'id' => $id->GetID()
//            ];

            $arFile = json_decode(dataCurl($dataCurl), true);
//            echo '1';
//            print_r(dataCurl($dataCurl));

            $bodyName = explode('/', $arFile['body'])[4];

            $arIdFiles = [];
            $fileId = CFile::SaveFile(
                array(
                    "name" => $bodyName,
                    "size" => '',
                    "tmp_name" => 'http://62.109.7.153' . $arFile['body'],
                    "old_file" => "0",
                    "del" => "N",
                    "MODULE_ID" => "",
                    "description" => ""
                ),
                'mails',
                false,
                false
            );
            $arIdFiles[] = $fileId;
            foreach ($arFile['attachment'] as $item) {
                $attachmentName = explode('/', $item)[4];
                $fileId = CFile::SaveFile(
                    array(
                        "name" => $attachmentName,
                        "size" => '',
                        "tmp_name" => 'http://62.109.7.153' . $item,
                        "old_file" => "0",
                        "del" => "N",
                        "MODULE_ID" => "",
                        "description" => ""
                    ),
                    'mails',
                    false,
                    false
                );
                $arIdFiles[] = $fileId;
            }

            if ($newUser == 0) {
                $arEventFields = [
                    'USER_TO' => $userToEmail,
                    'USER_NAME' => $userFromName . ' (' . $userFromEmail . ')',
                    'SUBJECT' => $_POST['subject'],
                    'NEW_USER' => 'Для расшифровки сообщения, пожалуйста, перейдите по ссылке и загрузите файлы из письма. https://trackonlive.ru/?page=decrypt'
                ];
            } else {
                $arEventFields = [
                    'USER_TO' => $userToEmail,
                    'USER_NAME' => $userFromName . ' (' . $userFromEmail . ')',
                    'SUBJECT' => $_POST['subject'],
                    'NEW_USER' => '
                        Вы были зарегистрированы на сайте <b>https://trackonlive.ru</b>
                        <br>Логин: <b>' . $userToEmail . '</b>
                        <br> Ваш пароль - <b>' . $newPassword . '</b>
                        <br>Для расшифровки сообщения, пожалуйста, перейдите по ссылке и загрузите файлы из письма. https://trackonlive.ru/?page=decrypt'
                ];
            }
            CEvent::Send("NOTICE_USER_MAIL", 's1', $arEventFields, 'N', '', $arIdFiles);
        }

        if ($_POST['usersCopy'] != '') {
            $arUserCopy = explode(',', str_replace(' ', '', $_POST['usersCopy']));
            $userIdCopy = [];
            foreach ($arUserCopy as $item) {
                $filter = ['EMAIL' => $item];
                $rsUsers = CUser::GetList(($by = "personal_country"), ($order = "desc"), $filter);
                while ($ob = $rsUsers->Fetch()) {
                    $userIdCopy[] = $ob['ID'];
                }
            }
        }

        if (count($userIdCopy) > 0) {
            foreach ($userIdCopy as $item) {
                $rsUser = $USER->GetByID($item);
                $arUser = $rsUser->Fetch();
                $userToEmail = $arUser['EMAIL'];
                $userFromName = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'];
                $rsUser = $USER->GetByID($USER->GetID());
                $arUser = $rsUser->Fetch();
                $userFromEmail = $arUser['EMAIL'];
                $id = $mailClass::createMessage(['UF_FROM' => '']);

                $dataCurl = [
                    'event' => 'encrypt',
                    'message' => $_POST['message'],
                    'email' => hash('md5', $userToEmail),
                    'file' => $_FILES,
                    'subject' => $_POST['subject'],
                    'date' => date('d.m.Y H:i:s'),
                    'from' => $userFromEmail,
                    'to' => $userToEmail,
                    'id' => $id->GetID()
                ];
//                dataCurl($dataCurl);

//                $dataCurl = [
//                    'event' => 'encrypt',
//                    'message' => $_POST['message'],
//                    'email' => hash('md5', $userFromEmail),
//                    'file' => $_FILES,
//                    'subject' => $_POST['subject'],
//                    'date' => date('d.m.Y H:i:s'),
//                    'from' => $userFromEmail,
//                    'to' => $userToEmail,
//                    'id' => $id->GetID()
//                ];

                $arFile = json_decode(dataCurl($dataCurl), true);
                $bodyName = explode('/', $arFile['body'])[3];

                $arIdFiles = [];
                $fileId = CFile::SaveFile(
                    array(
                        "name" => $bodyName,
                        "size" => '',
                        "tmp_name" => 'http://62.109.7.153' . $arFile['body'],
                        "old_file" => "0",
                        "del" => "N",
                        "MODULE_ID" => "",
                        "description" => ""
                    ),
                    'mails',
                    false,
                    false
                );
                $arIdFiles[] = $fileId;
                foreach ($arFile['attachment'] as $item) {
                    $attachmentName = explode('/', $item)[3];
                    $fileId = CFile::SaveFile(
                        array(
                            "name" => $attachmentName,
                            "size" => '',
                            "tmp_name" => 'http://62.109.7.153' . $item,
                            "old_file" => "0",
                            "del" => "N",
                            "MODULE_ID" => "",
                            "description" => ""
                        ),
                        'mails',
                        false,
                        false
                    );
                    $arIdFiles[] = $fileId;
                }

                $arEventFields = [
                    'USER_TO' => $userToEmail,
                    'USER_NAME' => $userFromName . ' (' . $userFromEmail . ')',
                    'SUBJECT' => $_POST['subject'],
                    'NEW_USER' => 'Для расшифровки сообщения, пожалуйста, перейдите по ссылке и загрузите файлы из письма. https://trackonlive.ru/?page=decrypt'
                ];

                CEvent::Send("NOTICE_USER_MAIL", 's1', $arEventFields, 'N', '', $arIdFiles);
            }
        }
        break;
    case 'descrypt-message':
        $rsUser = $USER->GetByID($USER->GetID());
        $arUser = $rsUser->Fetch();
        $userFromEmail = $arUser['EMAIL'];
        $hashFolder = substr(hash('md5', $userFromEmail), 0, 8);
        $files = fileArr($_FILES);
        $idMessage = '';
        foreach ($files as $file) {
            if ($file['type'] != 'application/x-zip-compressed') {
                $content = file_get_contents($file['tmp_name']);
                $idMessage = explode('|', $content)[0];
            } else {
                move_uploaded_file($file['tmp_name'], '/home/bitrix/ext_www/trackonlive.ru/upload/messages/' . $hashFolder . '/' . $file['name']);
                $content = file_get_contents('/home/bitrix/ext_www/trackonlive.ru/upload/messages/' . $hashFolder . '/' . $file['name']);
                file_put_contents('/home/bitrix/ext_www/trackonlive.ru/upload/messages/' . $hashFolder . '/' . $file['name'], $content);
                $zip = new ZipArchive();
                $zip->open('/home/bitrix/ext_www/trackonlive.ru/upload/messages/' . $hashFolder . '/' . $file['name']);
                $zip->extractTo('/home/bitrix/ext_www/trackonlive.ru/upload/messages/' . $hashFolder . '/', array('message'));
                $zip->close();
                $content = file_get_contents('/home/bitrix/ext_www/trackonlive.ru/upload/messages/' . $hashFolder . '/message');
                $idMessage = explode('|', $content)[0];
                unlink('/home/bitrix/ext_www/trackonlive.ru/upload/messages/' . $hashFolder . '/message');
            }
        }

        $dataCurl = [
            'event' => 'decrypt-message',
            'email' => hash('md5', $arUser['EMAIL']),
            'file' => $_FILES,
            'from' => $userFromEmail,
            'id' => $idMessage
        ];
        $result = json_decode(dataCurl($dataCurl), true);
//        print_r(dataCurl($dataCurl));
//    print_r($hashFolder);
        $body = json_decode($result['body'], true);
        if ($body) {
            $rsUsers = CUser::GetList(($by = "personal_country"), ($order = "desc"), ['EMAIL' => $body['from']]);
            while ($ob = $rsUsers->Fetch()) {
                $body['info-user-from'] = $ob['LAST_NAME'] . ' ' . $ob['NAME'] . '(' . $body['from'] . ')';
            }
            $rsUsers = CUser::GetList(($by = "personal_country"), ($order = "desc"), ['EMAIL' => $body['to']]);
            while ($ob = $rsUsers->Fetch()) {
                $body['info-user-to'] = $ob['LAST_NAME'] . ' ' . $ob['NAME'] . ' (' . $body['to'] . ')';
            }
            $attachment = $result['attachment'];

            foreach ($attachment as $key => $item) {
                $key++;
                $contentFIle = file_get_contents('http://62.109.7.153/certs/docs/' . $idMessage . '/' . $item);
                file_put_contents('/home/bitrix/ext_www/trackonlive.ru/upload/messages/' . $hashFolder . '/' . $item, $contentFIle);
                $fileName = explode('decode_', $item)[1];
                $body['links'][] = '<a href="https://trackonlive.ru/upload/messages/' . $hashFolder . '/' . $item . '" target="_blank"> ' . $item . '</a><br>';
            }
            echo json_encode($body);
        } else {
            echo 'error';
        }

        break;
    case 'delete-message':
        $mailClass::deleteMessage($_POST['id']);
        break;
    case 'save-drafts':
        $rsUser = $USER->GetByID($_POST['user']);
        $arUser = $rsUser->Fetch();
        $userFromEmail = hash('md5', $arUser['EMAIL']);

        $dataCurl = [
            'event' => 'encrypt',
            'message' => $_POST['message'],
            'email' => $userFromEmail
        ];
        $message = dataCurl($dataCurl);

        $arFields = [
            'UF_DATE' => date('d.m.Y H:i:s'),
            'UF_FROM' => $_POST['user'],
            'UF_TO' => $_POST['user'],
            'UF_SUBJECT' => $_POST['subject'],
            'UF_MESSAGE' => $message,
            'UF_COPY' => 'N',
            'UF_DELETE' => 'N',
            'UF_DRAFTS' => 'Y'
        ];
        $mailClass::createMessage($arFields);
        break;
    case 'asnwer-message':
        $arUser = explode(',', $_POST['userTo']);
//        array_pop($arUser);
//        print_r($arUser);
        foreach ($arUser as $item) {
            $rsUser = $USER->GetByID($USER->GetID());
            $arUser = $rsUser->Fetch();
            $userFromEmail = $arUser['EMAIL'];
            $userFromName = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'];
            $rsUser = $USER->GetByID($item);
            $arUser = $rsUser->Fetch();
            $userToEmail = $arUser['EMAIL'];

            $rsUser = $USER->GetByID($item);
            $arUser = $rsUser->Fetch();
            $userFromEmail = hash('md5', $arUser['EMAIL']);

            $dataCurl = [
                'event' => 'encrypt',
                'message' => $_POST['message'],
                'email' => hash('md5', $userToEmail)
            ];
            $message = dataCurl($dataCurl);

            $arFields = [
                'UF_DATE' => date('d.m.Y H:i:s'),
                'UF_FROM' => $_POST['user'],
                'UF_TO' => $item,
                'UF_SUBJECT' => $_POST['subject'],
                'UF_MESSAGE' => $message,
                'UF_COPY' => 'N',
                'UF_DELETE' => 'N',
                'UF_DRAFTS' => 'N',
                'UF_COPY_TO' => ''
            ];

            $id = $mailClass::createMessage($arFields);

            $arEventFields = [
                'USER_TO' => $userToEmail,
                'USER_NAME' => $userFromName . '(' . $userFromEmail . ')',
                'SUBJECT' => $_POST['subject'],
                'ID' => $id->GetID()
            ];
            CEvent::Send("NOTICE_USER_MAIL", 's1', $arEventFields);

            $rsUser = $USER->GetByID($USER->GetID());
            $arUser = $rsUser->Fetch();
            $userFromEmail = $arUser['EMAIL'];

            $dataCurl = [
                'event' => 'encrypt',
                'message' => $_POST['message'],
                'email' => hash('md5', $userFromEmail)
            ];
            $message = dataCurl($dataCurl);

            $arFields = [
                'UF_DATE' => date('d.m.Y H:i:s'),
                'UF_FROM' => $USER->GetID(),
                'UF_TO' => $USER->GetID(),
                'UF_SUBJECT' => $_POST['subject'],
                'UF_MESSAGE' => $message,
                'UF_COPY' => 'N',
                'UF_DELETE' => 'N',
                'UF_DRAFTS' => 'N',
                'UF_ID_MAIN' => '',
                'UF_USERS_COPY' => '',
                'UF_COPY_TO' => $item
            ];

            $mailClass::createMessage($arFields);
        }
        break;
    case 'update-message':
        $arMessage = explode(',', $_POST['arMessage']);
        $listMsg = $mailClass::getList([
            'UF_TO' => $USER->GetID(),
            '!UF_FROM' => $USER->GetID(),
            'UF_DELETE' => 'N',
            'UF_DRAFTS' => 'N'
        ]);
        $html = '';
        $arResult = [];
        if (count($listMsg) > 0) {
            foreach ($listMsg as $item) {
                if (!in_array($item['id'], $arMessage)) {
                    $arResult[] = $item;
                }
            }
            foreach ($arResult as $item) {
                if ($item['dateRead'] == 'не прочитано') $noRead = 'no-read'; else $noRead = '';
                $html .= ' <a href="?page=' . $_POST["page"] . '&message=' . $item["id"] . '">
                     <div class="client__list-item client-item ' . $noRead . ' " id="open-message" data-id="' . $item["id"] . '">
                        <div class="client-item__content">
                            <p class="client-item__from">' . $item["user_from"] . '(' . $item["user_from_email"] . ')</p>
                            <div class="client-item__header">
                                <p class="client-item__title">' . $item["subject"] . '</p>
                                <p class="client-item__dt">' . $item["date"] . '</p>
                            </div>
                        </div>
                     </div>
                    </a>
            ';
            }
        }
        $countMail = $mailClass::countMail($USER->GetID());

        $result = [
            'html' => $html,
            'inbox' => $countMail['inbox']
        ];

        echo json_encode($result);

        break;
}

function dataCurl($dataCurl)
{
    $ch = curl_init('http://62.109.7.153/certs/request.php');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataCurl, JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $request = curl_exec($ch);
    curl_close($ch);
    $message = json_decode($request, JSON_UNESCAPED_UNICODE)['data'];
    return $request;
}