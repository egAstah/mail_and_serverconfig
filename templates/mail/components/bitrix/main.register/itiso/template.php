<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

/**
 * Bitrix vars
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponentTemplate $this
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
?>
<section class="loginpage">
    <div class="loginpage__block">
        <p class="loginpage__logo">
            Цифровой ямщик
        </p>
        <? if ($USER->IsAuthorized()): ?>
            <p style="text-align: center" class="loginpage__form-new"><? echo GetMessage("MAIN_REGISTER_AUTH") ?></p>
        <? else: ?>
            <form class="loginpage__form" method="post" action="<?= POST_FORM_ACTION_URI ?>" name="regform"
                  enctype="multipart/form-data">
                <?
                if ($arResult["BACKURL"] <> ''):
                    ?>
                    <input type="hidden" name="backurl" value="<?= $arResult["BACKURL"] ?>"/>
                <?
                endif;
                ?>
                <p class="loginpage__form-title"><?= GetMessage("AUTH_REGISTER") ?></p>
                <?
                if (count($arResult["ERRORS"]) > 0):
                    foreach ($arResult["ERRORS"] as $key => $error)
                        if (intval($key) == 0 && $key !== 0)
                            $arResult["ERRORS"][$key] = str_replace("#FIELD_NAME#", "&quot;" . GetMessage("REGISTER_FIELD_" . $key) . "&quot;", $error);

                    ShowError(implode("<br />", $arResult["ERRORS"]));

                elseif ($arResult["USE_EMAIL_CONFIRMATION"] === "Y"):
                    ?>
<!--                    <p class="loginpage__form-new">--><?// echo GetMessage("REGISTER_EMAIL_WILL_BE_SENT") ?><!--</p>-->
                <? endif ?>
                <? foreach ($arResult["SHOW_FIELDS"] as $FIELD): ?>

                    <?
                    switch ($FIELD) {
                        case "PASSWORD":
                            ?><input class="loginpage__form-field" size="30" type="password"
                                     name="REGISTER[<?= $FIELD ?>]"
                                     value="<?= $arResult["VALUES"][$FIELD] ?>" autocomplete="off"
                                     placeholder="<?= GetMessage("REGISTER_FIELD_" . $FIELD) ?>"
                                     class="bx-auth-input"/>
                            <?
                            break;
                        case "CONFIRM_PASSWORD":
                            ?><input class="loginpage__form-field" size="30" type="password"
                                     name="REGISTER[<?= $FIELD ?>]"
                                     value="<?= $arResult["VALUES"][$FIELD] ?>"
                                     placeholder="<?= GetMessage("REGISTER_FIELD_" . $FIELD) ?>"
                                     autocomplete="off" /><?
                            break;
                        default:
                            ?><input class="loginpage__form-field" size="30" type="text"
                                     name="REGISTER[<?= $FIELD ?>]"
                                     value="<?= $arResult["VALUES"][$FIELD] ?>"
                                     placeholder="<?= GetMessage("REGISTER_FIELD_" . $FIELD) ?>"
                            />
                        <?
                    } ?>
                <? endforeach ?>
                <? // ******************** /User properties ***************************************************?>
                <?
                /* CAPTCHA */
                if ($arResult["USE_CAPTCHA"] == "Y") {
                    ?>
                    <p class="loginpage__form-new"><?= GetMessage("REGISTER_CAPTCHA_TITLE") ?></p>
                    <input type="hidden" name="captcha_sid" value="<?= $arResult["CAPTCHA_CODE"] ?>"/>
                    <img src="/bitrix/tools/captcha.php?captcha_sid=<?= $arResult["CAPTCHA_CODE"] ?>"
                         width="180" height="40" alt="CAPTCHA"/>
                    <div><?= GetMessage("REGISTER_CAPTCHA_PROMT") ?>:<span class="starrequired">*</span>
                    </div>
                    <input type="text" name="captcha_word" maxlength="50" value="" autocomplete="off"/>
                    <?
                }
                /* !CAPTCHA */
                ?>
                <p class="loginpage__form-new"><? echo $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"]; ?></p>
                <p class="loginpage__form-new"><span class="starrequired">*</span><?= GetMessage("AUTH_REQ") ?></p>
                <div class="loginpage__form-submitWrapper">
                    <input class="loginpage__form-submit" type="submit" name="register_submit_button"
                           value="<?= GetMessage("AUTH_REGISTER") ?>"/>
                </div>
            </form>

        <? endif ?>
    </div>
</section>