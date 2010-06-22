<?php

/**
	Упрощённая отсылка почты пользователю
*/

class bors_ext_mail extends bors_empty
{
	/** Отправить пользователю $user почту $mail
		$user - объект, содержащий поля $email и $title. Почта будет отправлена на "$title <$email>"
		$mail - объект, содержащий поля $title [Subject], $text [текстовое содержимое письма], $html [html]
		$from - необязательное поле объекта $user отправителя.

		Если $user или $from не являются объектами, то они считаются строкой с именем/email адресата
		$mail->html() может отсутствовать.

		Если $mail - текст, то он считается текстом в автоматически пределяемой разметке:
			- Если первые две строки - заголовок в markdown-разметке, то markdown
	*/
	static function send($user, $mail, $from = NULL)
	{
		require_once('engines/mail.php');

		if(!is_object($mail))
		{
			$text = $mail;
			if(bors_markup_markdown::title_extract($text))
				$mail = bors_markup_markdown::factory($text);

			//TODO: ввести другие виды разметки
		}

		send_mail(
			self::make_recipient($user),
			$mail->title() . ' ' . date('r'),
			$mail->text(),
			$mail->get('html'),
			self::make_recipient($from),
			$mail->get('headers')
		);
	}

	static function make_recipient($user)
	{
		if(!$user)
			return NULL;

		if(!is_object($user))
			return $user;

		$name  = $user->title();
		$email = $user->email();
		return "$name <$email>";
	}
}
