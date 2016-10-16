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

[!account?
&controller=`account/register`
&controllerRegister=`account/register`
&controllerLogin=`account`
&controllerForgot=`account/forgot`
&controllerProfile=`account/profile`
&success=``
&userGroupId=``
!]

profile

[!account?
&controller=`account/profile`
&controllerRegister=`account/register`
&controllerLogin=`account`
&controllerForgot=`account/forgot`
&controllerProfile=`account/profile`
&success=``
&userGroupId=``
!]

Восстановление пароля

[!account?
&controller=`account/forgot`
&controllerRegister=`account/register`
&controllerLogin=`account`
&controllerForgot=`account/forgot`
&controllerProfile=`account/profile`
&success=``
&userGroupId=``
!]

Вместо псевдонимов контроллеров &controller..., можно поставить id страниц на которых расположен тот или иной вызов сниппета.

&success - перенаправление после удачного действия сниппета.
&userGroupId - группы, через запятую для нового зарегистрированного пользователя.
