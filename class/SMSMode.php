<?php
require_once "./vendor/autoload.php";
require_once "./class/SMSInterface.php";

use WBW\Library\SmsMode\Model\Authentication;
use WBW\Library\SmsMode\Provider\ApiProvider;
use WBW\Library\SmsMode\Request\SendingSmsMessageRequest;


class SMSMode implements SMSInterface
{
    private ApiProvider $provider;
    private $apiToken;
    private SendingSmsMessageRequest $smsRequest;

    public function __construct($debug = false)
    {

        // Create the API provider.
        $this->provider = new ApiProvider(new Authentication());
        $this->provider->setDebug($debug);
    }
    /**
     * Get the value of apiToken
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * Set the value of apiToken
     */
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;
        $this->provider->getAuthentication()->setAccessToken($apiToken);
    }

    public function prepareSMS($message, $to)
    {
        $this->smsRequest = new SendingSmsMessageRequest();
        $this->smsRequest->addNumero($to);
        $this->smsRequest->setMessage($message);
    }

    public function sendSMS()
    {
        if ($this->smsRequest->getNumero()) {
            return $this->provider->sendingSmsMessage($this->smsRequest);
        } else {
            return null;
        }
    }
}