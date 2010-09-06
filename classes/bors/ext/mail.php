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

		Если $mail - массив, то он считается массивом вида ($subject, $text)
	*/
	static function send($user, $mail, $from = NULL)
	{
		require_once('engines/mail.php');

		if(!is_object($mail))
		{
			$title = NULL;

			if(is_array($mail))
			{
				$title = $mail[0];
				$mail  = $mail[1];
			}

			$text = $mail;

			$mail = bors_markup_markdown::factory($text);

			if($title)
				$mail->set_title($title, false);

			//TODO: ввести другие виды разметки
		}

		require_once("engines/smarty/assign.php");
		$text = template_assign_data('xfile:aviaport/mail.txt', array('body' => $mail->text()));
		$html = $mail->get('html') ? template_assign_data('xfile:aviaport/mail.html', array(
			'body' => $mail->html(),
			'skip_title' => true,
		)) : NULL;

		send_mail(
			self::make_recipient($user),
			$mail->title(),
			$text,
			$html,
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
