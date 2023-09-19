<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? //here you can place your own messages
switch ($arResult["MESSAGE_CODE"]) {
    case "E01":
        ?><? //When user not found
        break;
    case "E02":
        ?><? //User was successfully authorized after confirmation
        break;
    case "E03":
        ?><? //User already confirm his registration
        break;
    case "E04":
        ?><? //Missed confirmation code
        break;
    case "E05":
        ?><? //Confirmation code provided does not match stored one
        break;
    case "E06":
        ?><? //Confirmation was successfull
        break;
    case "E07":
        ?><? //Some error occured during confirmation
        break;
}
?>
<? if ($arResult["SHOW_FORM"]): ?>
    <section class="loginpage">
        <div class="loginpage__block">
            <p class="loginpage__logo">
                Цифровой ямщик
            </p>
            <form class="loginpage__form" method="post" action="<? echo $arResult["FORM_ACTION"] ?>">
                <p class="loginpage__form-title">Введите код подтверждения</p>
                <p class="loginpage__form-new">На Вашу почту выслан код подтверждения.</p>
                <input class="loginpage__form-field" type="hidden"
                       name="<? echo $arParams["LOGIN"] ?>"
                       maxlength="50"
                       value="<? echo $_SESSION["loginUser"] ?>" size="17"
                       placeholder="Email"
                />
                <input class="loginpage__form-field" type="text"
                       name="<? echo $arParams["CONFIRM_CODE"] ?>"
                       maxlength="50"
                       value="<? echo $arResult["CONFIRM_CODE"] ?>" size="17"
                       placeholder="Код подтверждения"
                />
                <div class="loginpage__form-submitWrapper">
                    <input class="loginpage__form-submit" type="submit"
                           value="<? echo GetMessage("CT_BSAC_CONFIRM") ?>"/>
                </div>
                <input type="hidden" name="<? echo $arParams["USER_ID"] ?>" value="<? echo $arResult["USER_ID"] ?>"/>
            </form>
        </div>
    </section>
<? elseif (!$USER->IsAuthorized()): ?>
    <?
    $APPLICATION->IncludeComponent(
        "bitrix:system.auth.form",
        "itiso",
        array(
            "FORGOT_PASSWORD_URL" => "/auth/forget.php",
            "PROFILE_URL" => "/auth/personal.php",
            "REGISTER_URL" => "/auth/registration.php",
            "SHOW_ERRORS" => "Y"
        )
    );
    ?>
<? endif ?>