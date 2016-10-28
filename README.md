# ModxAccount
<h3>Личный кабинет веб пользователя MODx Evo</h3>

<p>Сниппет для регистрации, входа, напоминания пароля и личного кабинета пользователя.</p>

<p>Создётся сниппет <b>account</b> с кодом</p>

```php
<?php
require MODX_BASE_PATH.'assets/snippets/account/snippet.account.php';
?>
```

<p>
Создаётся документ "Личный кабинет" -> псевдоним -> <b>account</b> <br>
Далее в нём дочерние ресурсы<br>
Регистрация -> <b>register</b><br>
Профиль -> <b>profile</b><br>
Восстановление пароля -> <b>forgot</b><br>
<br>
и на каждой странице ставится вызов сниппета
</p>

<b>account</b>
<pre>
[!account?
&controller=`account`
&controllerRegister=`account/register`
&controllerLogin=`account`
&controllerForgot=`account/forgot`
&controllerProfile=`account/profile`
&success=``
&userGroupId=``
!]
</pre>

<b>register</b>
<pre>
[!account?
&controller=`account/register`
&controllerRegister=`account/register`
&controllerLogin=`account`
&controllerForgot=`account/forgot`
&controllerProfile=`account/profile`
&success=``
&userGroupId=``
!]
</pre>

<b>profile</b>
<pre>
[!account?
&controller=`account/profile`
&controllerRegister=`account/register`
&controllerLogin=`account`
&controllerForgot=`account/forgot`
&controllerProfile=`account/profile`
&success=``
&userGroupId=``
!]
</pre>

<b>forgot</b>
<pre>
[!account?
&controller=`account/forgot`
&controllerRegister=`account/register`
&controllerLogin=`account`
&controllerForgot=`account/forgot`
&controllerProfile=`account/profile`
&success=``
&userGroupId=``
!]
</pre>

<p>
Вместо псевдонимов контроллеров &controller..., можно поставить id страниц на которых расположен тот или иной вызов сниппета.
</p>
<p>
<b>&success</b> - перенаправление после удачного действия сниппета.
</p>
<p>
<b>&userGroupId</b> - id групп, через запятую для нового зарегистрированного пользователя.
</p>

При создании вложенности документов, как указанно выше и используя вложенные URL, 
вызов сниппета можно сократить до одной строчки
<pre>
[!account?&userGroupId=``!]
</pre>
либо использовать свои шаблоны
<pre>
[!account?
&tpl=`@FILE:assets/snippets/account/view/register.tpl.txt`
&userGroupId=``
!]
</pre>

<h3>AJAX</h3>
<p>Для работы через ajax используется <b>ModxLoader</b> - https://github.com/64j/ModxLoader </p>

<h3>Капча</h3>
<p>Используется эта https://github.com/64j/ModxCaptcha</p>
либо создать сниппет captcha и вывести его на отдельной странице с шаблоном blank и типом text/plain
```php
<?php
$chars = !empty($modx->config['captcha_words']) ? preg_replace('![^\w\d]*!', '', $modx->config['captcha_words']) : '1234567890';
$chars = substr(str_shuffle($chars), 0, 5);
if(isset($_REQUEST['key'])) {
	$_SESSION['veriword_' . md5($_REQUEST['key'])] = $chars;
} else {
	$_SESSION['veriword'] = $chars;
}
header("Pragma: no-cache");
header("Content-Type:image/png");
$img = imagecreate(210, 100);
imagecolorallocatealpha($img, 255, 255, 255, 127);
$color = imagecolorallocate($img, 0, 0, 0);
$x = 10;
for($i = 0; $i < strlen($chars); $i++) {
	$letter = mb_substr($chars, $i, 1, 'UTF-8');
	imagettftext($img, 70, rand(-10, 10), $x, 75, $color, MODX_MANAGER_PATH . "includes/ttf/ftb_____.ttf", $letter);
	$x += 35;
}
imagepng($img);
imagedestroy($img);
?>
```
<hr>
Ветка обсуждения сниппета на форуме <a href="http://modx.im/blog/addons/4750.html" target="_blank">modx.im</a>
