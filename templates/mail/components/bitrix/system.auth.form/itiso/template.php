<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CJSCore::Init();
$page = $_GET['page'];
if($page == 'decrypt') $action = '/?page=decrypt'; else $action = '/?message=new';
?>
<section class="loginpage">
    <div class="loginpage__block">
        <p class="loginpage__logo">
            Цифровой ямщик
        </p>
        <? if ($arResult["FORM_TYPE"] == "login"): ?>
            <form class="loginpage__form" name="system_auth_form<?= $arResult["RND"] ?>" method="post" target="_top"
                  action="<?=$action?>">
                <p class="loginpage__form-title">
                    Войти
                </p>
                <? if ($arResult["BACKURL"] <> ''): ?>
                    <input type="hidden" name="backurl" value="<?= $arResult["BACKURL"] ?>"/>
                <? endif ?>
                <?
                if ($arResult['SHOW_ERRORS'] == 'Y' && $arResult['ERROR'])
                    ShowMessage($arResult['ERROR_MESSAGE']);
                ?>
                <? foreach ($arResult["POST"] as $key => $value): ?>
                    <input type="hidden" name="<?= $key ?>" value="<?= $value ?>"/>
                <? endforeach ?>
                <input type="hidden" name="AUTH_FORM" value="Y"/>
                <input type="hidden" name="TYPE" value="AUTH"/>
                <input type="text" class="loginpage__form-field" name="USER_LOGIN"
                       placeholder="<?= GetMessage("AUTH_LOGIN") ?>" maxlength="50" value="" size="17"/>
                <script>
                    BX.ready(function () {
                        var loginCookie = BX.getCookie("<?=CUtil::JSEscape($arResult["~LOGIN_COOKIE_NAME"])?>");
                        if (loginCookie) {
                            var form = document.forms["system_auth_form<?=$arResult["RND"]?>"];
                            var loginInput = form.elements["USER_LOGIN"];
                            loginInput.value = loginCookie;
                        }
                    });
                </script>
                <input type="password" class="loginpage__form-field" name="USER_PASSWORD"
                       maxlength="255" size="17"
                       placeholder="<?= GetMessage("AUTH_PASSWORD") ?>"
                       autocomplete="off"/>
                <? if ($arResult["SECURE_AUTH"]): ?>
                    <span class="bx-auth-secure" id="bx_auth_secure<?= $arResult["RND"] ?>"
                          title="<? echo GetMessage("AUTH_SECURE_NOTE") ?>" style="display:none">
					<div class="bx-auth-secure-icon"></div>
				</span>
                    <noscript>
				<span class="bx-auth-secure" title="<? echo GetMessage("AUTH_NONSECURE_NOTE") ?>">
					<div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
				</span>
                    </noscript>
                    <script type="text/javascript">
                        document.getElementById('bx_auth_secure<?=$arResult["RND"]?>').style.display = 'inline-block';
                    </script>
                <? endif ?>
<!--                --><?// if ($arResult["STORE_PASSWORD"] == "Y"): ?>
<!--                    <input type="checkbox" id="USER_REMEMBER_frm" name="USER_REMEMBER" value="Y"/>-->
<!--                    <label for="USER_REMEMBER_frm">--><?// echo GetMessage("AUTH_REMEMBER_SHORT") ?><!--</label>-->
<!--                --><?// endif ?>
                <? if ($arResult["CAPTCHA_CODE"]): ?>
                    <tr>
                        <td colspan="2">
                            <? echo GetMessage("AUTH_CAPTCHA_PROMT") ?>:<br/>
                            <input type="hidden" name="captcha_sid" value="<? echo $arResult["CAPTCHA_CODE"] ?>"/>
                            <img src="/bitrix/tools/captcha.php?captcha_sid=<? echo $arResult["CAPTCHA_CODE"] ?>"
                                 width="180" height="40" alt="CAPTCHA"/><br/><br/>
                            <input type="text" name="captcha_word" maxlength="50" value=""/></td>
                    </tr>
                <? endif ?>
                <? if ($arResult["NEW_USER_REGISTRATION"] == "Y"): ?>
                    <p class="loginpage__form-new"> Нет учетной записи?
                        <a href="<?= $arResult["AUTH_REGISTER_URL"] ?>"
                           rel="nofollow">Создайте её!</a>
                    </p>
                <? endif ?>
                <p class="loginpage__form-new">
                    <a href="<?= $arResult["AUTH_FORGOT_PASSWORD_URL"] ?>"
                       rel="nofollow">Не удается получить доступ к своей учетной записи?</a>
                </p>
                <div class="loginpage__form-submitWrapper">
                    <input class="loginpage__form-submit" type="submit" name="Login"
                           value="<?= GetMessage("AUTH_LOGIN_BUTTON") ?>"/>
                </div>
            </form>
        <?else:?>
            <form class="loginpage__form"  action="<?= $arResult["AUTH_URL"] ?>">
                <p style="text-align: center" class="loginpage__form-new"><?= $arResult["USER_NAME"] ?></p>
                <p style="text-align: center" class="loginpage__form-new">
                    <a href="<?= $arResult["PROFILE_URL"] ?>"><?= GetMessage("AUTH_PROFILE") ?></a>
                </p>
                <div class="loginpage__form-submitWrapper">
                    <? foreach ($arResult["GET"] as $key => $value): ?>
                        <input type="hidden" name="<?= $key ?>" value="<?= $value ?>"/>
                    <? endforeach ?>
                    <?= bitrix_sessid_post() ?>
                    <input type="hidden" name="logout" value="yes"/>
                    <input type="submit" class="loginpage__form-submit" name="logout_butt" value="<?= GetMessage("AUTH_LOGOUT_BUTTON") ?>"/>
                </div>
            </form>
        <? endif ?>
    </div>
</section>