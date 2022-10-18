<?php

use Phalcon\Mvc\User\Component;

class GlobalVariable extends Component
{
	public $acceptUploadTypes;
	public $rateUSDSGD;
	public $rateUSDHKD;
    public $timeZone;
    public $curTime;
    public $localTime;
	public function __construct()
	{
        date_default_timezone_set('UTC');//default for Application - NOT ONLY for current script
        $this->timeZone = 8*3600;
        $this->curTime = time();
        $this->localTime = time() + $this->timeZone;
        $this->timeZoneStr = 'UTC+08:00';
	    //accept upload file types
		$this->acceptUploadTypes = array(
			"image/jpeg" => array("type" => "image", "ext" => ".jpg"),
			"image/pjpeg" => array("type" => "image", "ext" => ".jpg"),
			"image/png" => array("type" => "image", "ext" => ".png"),
			"image/bmp" => array("type" => "image", "ext" => ".bmp"),
			"image/x-windows-bmp" => array("type" => "image", "ext" => ".bmp"),
			"image/x-icon" => array("type" => "image", "ext" => ".ico"),
			"image/ico" => array("type" => "image", "ext" => ".ico"),
			"image/gif" => array("type" => "image", "ext" => ".gif"),
			"text/plain" => array("type" => "file", "ext" => ".txt"),
			"application/msword" => array("type" => "file", "ext" => ".doc"),
			"application/vnd.openxmlformats-officedocument.wordprocessingml.document" => array("type" => "file", "ext" => ".docx"),
			"application/vnd.ms-excel" => array("type" => "file", "ext" => ".xls"),
			"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => array("type" => "file", "ext" => ".xlsx"),
			"application/pdf" => array("type" => "file", "ext" => ".pdf"),
		);
        //Google captcha server
        $this->site_key = '6LcVn1oUAAAAADwBqd1Zz3iME10qfVYYNWov8hMQ';
        $this->serect_key = '6LcVn1oUAAAAABi2RFoQ1vNWo6ta8GQbeA1meZwq';

        // Google captcha local
        if (defined('LOCAL_RECAPTCHA') && LOCAL_RECAPTCHA) {
            $this->site_key = '6Lf5_VkUAAAAAOZUQH01nSnkiwMWfc5mxHqZ771m';
            $this->serect_key = '6Lf5_VkUAAAAAAAc6SaSI_RiNkxGyV8tYB460UMC';
        }
		
	}
}