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

forgot
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

Вместо псевдонимов контроллеров &controller..., можно поставить id страниц на которых расположен тот или иной вызов сниппета.

&success - перенаправление после удачного действия сниппета.
&userGroupId - id групп, через запятую для нового зарегистрированного пользователя.
