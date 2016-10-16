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

[!account?<br>
&controller=`account`<br>
&controllerRegister=`account/register`<br>
&controllerLogin=`account`<br>
&controllerForgot=`account/forgot`<br>
&controllerProfile=`account/profile`<br>
&success=``<br>
&userGroupId=``<br>
!]

register

[!account?<br>
&controller=`account/register`<br>
&controllerRegister=`account/register`<br>
&controllerLogin=`account`<br>
&controllerForgot=`account/forgot`<br>
&controllerProfile=`account/profile`<br>
&success=``<br>
&userGroupId=``<br>
!]

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

Восстановление пароля

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
