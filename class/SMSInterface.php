<?php

interface SMSInterface
{
    public function prepareSMS($message, $to);
    public function sendSMS();
}