# ModxAccount
Личный кабинет веб пользоватля MODx Evo

Сниппет для регистрации, входа, напоминания пароля и личного кабинета пользователя.

Создаётся документ "Личный кабинет" -> псевдоним -> account
Далее в нём дочерние ресурсы
Регистрация -> register
Профиль -> profile
Восстановление пароля -> forgot

и на каждой странице ставится вызов сниппета

account
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

register
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

profile

[!account?<br>
&controller=`account/profile`<br>
&controllerRegister=`account/register`<br>
&controllerLogin=`account`<br>
&controllerForgot=`account/forgot`<br>
&controllerProfile=`account/profile`<br>
&success=``<br>
&userGroupId=``<br>
!]

forgot

[!account?<br>
&controller=`account/forgot`<br>
&controllerRegister=`account/register`<br>
&controllerLogin=`account`<br>
&controllerForgot=`account/forgot`<br>
&controllerProfile=`account/profile`<br>
&success=``<br>
&userGroupId=``<br>
!]

Вместо псевдонимов контроллеров &controller..., можно поставить id страниц на которых расположен тот или иной вызов сниппета.

&success - перенаправление после удачного действия сниппета.
&userGroupId - id групп, через запятую для нового зарегистрированного пользователя.
