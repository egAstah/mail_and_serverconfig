<?

include $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/autoload.php';

AddEventHandler("main", "OnBeforeUserRegister", Array("RegistrationUser", "OnBeforeUserRegisterHandler"));

class RegistrationUser
{
    function OnBeforeUserRegisterHandler(&$arFields)
    {
        session_start();
        $_SESSION['loginUser'] = $arFields["LOGIN"];
    }
}
AddEventHandler('main', 'OnBeforeEventSend', Array("MyForm", "my_OnBeforeEventSend"));
class MyForm
{
    public static function my_OnBeforeEventSend($arFields, $arTemplate)
    {

//        $log = date('Y-m-d H:i:s') . ' ' . print_r($arTemplate, true);
//        file_put_contents('/home/bitrix/ext_www/trackonlive.ru/local/log.txt', $log, FILE_APPEND);

//        sleep(15);
//        \Bitrix\Main\IO\Directory::deleteDirectory('/home/bitrix/ext_www/trackonlive.ru/upload/mails');
    }
}