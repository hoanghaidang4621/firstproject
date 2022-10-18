<?php
/**
 * My
 *
 * My Common functions
 */
class My extends Phalcon\Mvc\User\Component
{
	//set language
	public function setLanguage($lang)
	{
		$this->session->set('lang', $lang);
		$translationPath = __DIR__ . '/../messages';
		include_once $translationPath.'/'.$lang.'.php';
	}
    public function localTime($time)
    {
        return $time + $this->globalVariable->timeZone;
    }

    //convert local site time to UTC0
    public function UTCTime($time)
    {
        return $time - $this->globalVariable->timeZone;
    }
	//send email
	public function sendEmail($frommail,$tomail,$subject,$message,$fromfullname, $tofullname, $reply_to_email, $reply_to_name)
	{
		$mail = $this->myMailer;
		$result = array();
		try {
			//reply to
			$mail->AddReplyTo($reply_to_email, $reply_to_name);
			//
			$mail->SetFrom($frommail, $fromfullname); //from (verified email address)
			$mail->Subject = $subject; //subject
			//message
			$body = $message;
			//$body = preg_replace("/\\/i",'',$body);
			$mail->MsgHTML($body);
			//
			//recipient
			$mail->AddAddress($tomail, $tofullname);
			
			// add bbc
			//$mail->AddBCC($bbc_email, $bbc_name);
			//Success
			$isSent = $mail->Send();
			if ($isSent) {
				$result['success'] = true;
				$result['message'] = "Message sent!";
			}
			else
			{
				$result['success'] = false;
				$result['message'] = "Mailer Error: " . $mail->ErrorInfo;
			}
		} catch (phpmailerException $e) {
			$result['success'] = false;
			$result['message'] = "Mailer Error: " . $e->errorMessage();//Pretty error messages from PHPMailer
		} catch (Exception $e) {
			$result['success'] = false;
			$result['message'] = "Mailer Error: " . $e->getMessage();//Boring error messages from anything else!
		}
		$mail->ClearAllRecipients();
		$mail->ClearReplyTos();
		$mail->ClearAttachments();
		return $result;
	}


}

