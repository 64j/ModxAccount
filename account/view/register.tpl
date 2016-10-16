<div class="container">
    <form class="" method="post" action="" name="regform" id="regform" enctype="multipart/form-data">
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="fullname">ФИО <sup class="text-danger">*</sup></label>
            </div>
            <div class="col-xs-9">
                <input class="form-control" type="text" id="fullname" name="fullname" value="<?= $fullname ?>" placeholder="Иванов Иван Иванович">
				<? if($error_fullname) { ?>
                    <div class="text-danger">
						<?= $error_fullname ?>
                    </div>
				<? } ?>
            </div>
        </div>
<!--		
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="street">Улица2</label>
            </div>
            <div class="col-xs-9">
                <input class="form-control" type="text" id="street" name="custom_field[1][2][3][street][small]" value="<?= $custom_field[1][2][3]['street']['small'] ?>">
				<? if($error_custom_field[1][2][3]['street']['small']) { ?>
                    <div class="text-danger">
						<?= $error_custom_field[1][2][3]['street']['small'] ?>
                    </div>
				<? } ?>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="street">Улица2</label>
            </div>
            <div class="col-xs-9">
                <input class="form-control" type="text" id="street" name="custom_field[1][2][street][small]" value="<?= $custom_field[1][2]['street']['small'] ?>">
				<? if($error_custom_field[1][2]['street']['small']) { ?>
                    <div class="text-danger">
						<?= $error_custom_field[1][2]['street']['small'] ?>
                    </div>
				<? } ?>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="address">Улица</label>
            </div>
            <div class="col-xs-9">
                <input class="form-control" type="text" id="address" name="custom_field[address][street]" value="<?= $custom_field['address']['street'] ?>">
				<? if($error_custom_field['address']['street']) { ?>
                    <div class="text-danger">
						<?= $error_custom_field['address']['street'] ?>
                    </div>
				<? } ?>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="house">Дом</label>
            </div>
            <div class="col-xs-9">
                <input class="form-control" type="text" id="house" name="custom_field[address][house]" value="<?= $custom_field['address']['house'] ?>">
				<? if($error_custom_field['address']['house']) { ?>
                    <div class="text-danger">
						<?= $error_custom_field['address']['house'] ?>
                    </div>
				<? } ?>
            </div>
        </div>-->
<!--		
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="firstname">Имя <sup class="text-danger">*</sup></label>
            </div>
            <div class="col-xs-9">
                <input class="form-control" type="text" id="firstname" name="firstname" value="<?= $firstname ?>" placeholder="Иван">
				<? if($error_firstname) { ?>
                    <div class="text-danger">
						<?= $error_firstname ?>
                    </div>
				<? } ?>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="lastname">Фамилия <sup class="text-danger">*</sup></label>
            </div>
            <div class="col-xs-9">
                <input class="form-control" type="text" id="lastname" name="lastname" value="<?= $lastname ?>" placeholder="Иванов">
				<? if($error_lastname) { ?>
                    <div class="text-danger">
						<?= $error_lastname ?>
                    </div>
				<? } ?>
            </div>
        </div>
		-->
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="email">Электронная почта <sup class="text-danger">*</sup></label>
            </div>
            <div class="col-xs-9">
                <input class="form-control" type="text" id="email" name="email" value="<?= $email ?>" placeholder="mail@mail.ru">
				<? if($error_email) { ?>
                    <div class="text-danger">
						<?= $error_email ?>
                    </div>
				<? } ?>
            </div>
        </div>
<!--		
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="phone">Телефон <sup class="text-danger">*</sup></label>
            </div>
            <div class="col-xs-9">
                <input class="form-control" type="text" id="phone" name="phone" value="<?= $phone ?>" placeholder="+7 (___) ___-__-__">
				<? if($error_phone) { ?>
                    <div class="text-danger">
						<?= $error_phone ?>
                    </div>
				<? } ?>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="mobilephone">Мобильный телефон
                    <sup class="text-danger">*</sup></label>
            </div>
            <div class="col-xs-9">
                <input class="form-control" type="text" id="mobilephone" name="mobilephone" value="<?= $mobilephone ?>" placeholder="+7 (___) ___-__-__">
				<? if($error_mobilephone) { ?>
                    <div class="text-danger">
						<?= $error_mobilephone ?>
                    </div>
				<? } ?>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="dob">Дата рождения</label>
            </div>
            <div class="col-xs-9">
                <input class="form-control" type="text" id="dob" name="dob" value="<?= $dob ?>" placeholder="01-01-1970">
				<? if($error_dob) { ?>
                    <div class="text-danger">
						<?= $error_dob ?>
                    </div>
				<? } ?>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label">Пол</label>
            </div>
            <div class="col-xs-9">
                <label class="radio-inline">
                    <input type="radio" name="gender" value="1"<? if($gender == 1) { ?> checked="checked"<? } ?>>
                    Мужской </label>
                <label class="checkbox-inline">
                    <input type="radio" name="gender" value="2"<? if($gender == 2) { ?> checked="checked"<? } ?>>
                    Женский </label>
				<? if($error_gender) { ?>
                    <div class="text-danger">
						<?= $error_gender ?>
                    </div>
				<? } ?>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="country">Страна</label>
            </div>
            <div class="col-xs-9">
                <select class="form-control" id="country" name="country">
					<?= $country_select ?>
                </select>
				<? if($error_country) { ?>
                    <div class="text-danger">
						<?= $error_country ?>
                    </div>
				<? } ?>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="city">Город</label>
            </div>
            <div class="col-xs-9">
                <input class="form-control" type="text" id="city" name="city" value="<?= $city ?>">
				<? if($error_city) { ?>
                    <div class="text-danger">
						<?= $error_city ?>
                    </div>
				<? } ?>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="address">Дом</label>
            </div>
            <div class="col-xs-9">
                <input class="form-control" type="text" id="address" name="custom_field[address][house]" value="<?= $custom_field['address']['house'] ?>">
				<? if($error_custom_field['address']['house']) { ?>
                    <div class="text-danger">
						<?= $error_custom_field['address']['house'] ?>
                    </div>
				<? } ?>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="address">Улица</label>
            </div>
            <div class="col-xs-9">
                <input class="form-control" type="text" id="address" name="custom_field[address][street]" value="<?= $custom_field['address']['street'] ?>">
				<? if($error_custom_field['address']['street']) { ?>
                    <div class="text-danger">
						<?= $error_custom_field['address']['street'] ?>
                    </div>
				<? } ?>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="address">Город</label>
            </div>
            <div class="col-xs-9">
                <input class="form-control" type="text" id="address" name="custom_field[address][city]" value="<?= $custom_field['address']['city'] ?>">
				<? if($error_custom_field['address']['city']) { ?>
                    <div class="text-danger">
						<?= $error_custom_field['address']['city'] ?>
                    </div>
				<? } ?>
            </div>
        </div>
		-->
		
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="photo">Фото</label>
            </div>
            <div class="col-xs-9">
                <input type="file" id="photo" name="photo" class="hidden">
                <p class="help-block">Выберите изображение. Размер файла не должен превышать 100 КБ</p>
				<? if($error_photo) { ?>
                    <div class="text-danger">
						<?= $error_photo ?>
                    </div>
				<? } ?>
				<? if($photo_cache) { ?>
					<input type="hidden" id="photo_cache" name="photo_cache" value="<?= $photo_cache ?>">
					<img src="<?= $photo_cache_path ?>" alt=""/>
					<label class="btn btn-danger btn-xs btn-del-photo">Удалить</label>
				<? } else { ?>
					<label class="btn btn-primary btn-xs btn-add-photo">Добавить</label>
				<? } ?>
            </div>
        </div>
		
        <hr>
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="password">Пароль <sup class="text-danger">*</sup></label>
            </div>
            <div class="col-xs-9">
                <input class="form-control" type="password" id="password" name="password">
				<? if($error_password) { ?>
                    <div class="text-danger">
						<?= $error_password ?>
                    </div>
				<? } ?>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="confirm">Подтверждение пароля <sup class="text-danger">*</sup></label>
            </div>
            <div class="col-xs-9">
                <input class="form-control" type="password" id="confirm" name="confirm">
				<? if($error_confirm) { ?>
                    <div class="text-danger">
						<?= $error_confirm ?>
                    </div>
				<? } ?>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-xs-3">
                <label class="control-label" for="captcha">Проверочный код <sup class="text-danger">*</sup></label>
            </div>
            <div class="col-xs-9">
                <input class="form-control" type="text" id="captcha" name="captcha">
				<? if($error_captcha) { ?>
                    <div class="text-danger">
						<?= $error_captcha ?>
                    </div>
				<? } ?>
                <img src="assets/captcha" alt="captcha" width="120px" height="60px"/></div>
        </div>
        <hr>
        <div class="row form-group">
            <div class="col-xs-3">&nbsp;</div>
            <div class="col-xs-9 text-right">
                <a href="<?= $controllerLogin ?>" class="btn btn-primary">Войти
                    <i class="fa fa-sign-in"></i>
                </a>
                <a href="<?= $controllerForgot ?>" class="btn btn-primary">
                    Напомнить пароль <i class="fa fa-unlock"></i>
                </a>
                <button class="btn btn-success" type="submit" name="action" value="register">Зарегистрироваться
                    <i class="fa fa-edit"></i>
                </button>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
$(document).ready(function() {
	
	var config = <?php echo $json_config ?>;
	
		$(document).delegate('.btn-del-photo', 'click', function(e) {
			var $this = $(this),
				$parent = $this.parent(),
				$row = $this.closest('.form-group');

			jQuery.ajax({
				url: 'ajax?route=account/controller/register/del_photo',
				type: 'POST',
				dataType: 'json',
				data: $.param(config) + '&photo=' + $('img', $parent).attr('src'),
				success: function(json) {
					if(json['redirect']) {
						location = json['redirect'];
					} else if(json['error']) {
						$('#photo').parent().append('<div class="text-danger">' + data.error + '</div>');
					} else {
						$('#photo', $parent).val('');
						$this.remove();
						$('div.text-danger, img, [name=photo_cache]', $row).remove();
						$parent.append('<label class="btn btn-primary btn-xs btn-add-photo">Добавить</label>');
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		});

		$(document).delegate('.btn-add-photo', 'click', function(e) {
			$('#photo').click()
		});

		$('#photo').on('change', function(e) {
			var $this = $(this),
				$parent = $this.parent(),
				$row = $this.closest('.form-group'),
				file = this.files[0],
				formData = new FormData();

			formData.append('photo', file);
			formData.append('controllerProfile', config.controllerProfile);
			$('div.text-danger', $row).remove();

			if(file.size > 102400) {
				$('#photo').parent().append('<div class="text-danger">Файл изображения превышает допустимые размеры.</div>');
			} else if(!(file.type.indexOf('image/') + 1)) {
				$('#photo').parent().append('<div class="text-danger">Выберите файл изображения. Неверный формат файла.</div>');
			} else {
				jQuery.ajax({
					url: 'ajax?route=account/controller/register/add_photo',
					type: 'POST',
					dataType: 'json',
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
					success: function(json) {
						if(json['error']) {
							$('#photo').parent().append('<div class="text-danger">' + json['error'] + '</div>');
						} else if(json['path']) {
							$('.btn-add-photo', $row).remove();
							$('[name=photo]').val('');
							$parent.append('<input type="hidden" id="photo_cache" name="photo_cache" value="' + json['name'] + '"><img src="' + json['path'] + '" alt="" width="100px" height="auto" /> <label class="btn btn-danger btn-xs btn-del-photo">Удалить</label>');
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				})
			}
		});

    $('#regform2 :submit').click(function(e) {
        e.preventDefault();

        var form = $(this).closest('form'),
            params = 'action=' + $(this).val() + '&' + $.param(config) + '&' + form.serialize();

        $.ajax({
            url: 'ajax?route=account/controller/register/ajax',
            dataType: 'json',
            type: 'post',
            data: params,
            beforeSend: function() {
				form.fadeTo(250, 0.5);
                $('.has-error').removeClass('has-error')
            },
            success: function(json) {
				form.fadeTo(150, 1);
                $('div.text-danger').remove();
				
                if(json['redirect']) {
                    location = json['redirect'];
                } else if(json['error']) {					
                    for(i in json['error']) {					
                        var $field = $('[name="' + i + '"]', form);
                        $field.closest('.form-group').addClass('has-error');
                        $field.closest('div').append('<div class="text-danger">' + json['error'][i] + '</div>');
                    }
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    });
});
</script>