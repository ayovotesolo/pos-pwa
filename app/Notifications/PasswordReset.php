<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// add this.

class PasswordReset extends ResetPassword// change extends from Notification to ResetPassword
{
	/**
	 * The password reset token.
	 *
	 * @var string
	 */
	public $token;

	/**
	 * Create a notification instance.
	 *
	 * @param  string  $token
	 * @return void
	 */
	public function __construct($token) {
		$this->token = $token;
	}

	/**
	 * Get the notification's channels.
	 *
	 * @param  mixed  $notifiable
	 * @return array|string
	 */
	public function via($notifiable) {
		return ['mail'];
	}

	/**
	 * Build the mail representation of the notification.
	 *
	 * @param  mixed  $notifiable
	 * @return \Illuminate\Notifications\Messages\MailMessage
	 */
	public function toMail($notifiable) {

		//$host = env('POS_BACKOFFICE_URL', 'backoffice.juztpoint.com');
		$host = "";

		return (new MailMessage)
			->view('vendor.notifications.email')
			->action('Reset Password', url('/password/reset', $this->token)); // add this. this is $actionUrl
	}
}