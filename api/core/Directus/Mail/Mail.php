<?php

namespace Directus\Mail;

use \Clousure;
use \Swift_Message;
use Directus\Bootstrap;

class Mail
{
    protected $mailer = null;
    protected $settings = [];

    public function __construct($mailer)
    {
        $this->mailer = $mailer;
        $DirectusSettingsTableGateway = new \Zend\Db\TableGateway\TableGateway('directus_settings', Bootstrap::get('zendDb'));
        $rowSet = $DirectusSettingsTableGateway->select();
        foreach ($rowSet as $setting) {
            $this->settings[$setting['collection']][$setting['name']] = $setting['value'];
        }
    }

    public function sendMessage($message)
    {
        $this->mailer->send($message);
    }

    public static function send($viewPath, $data, $callback)
    {
        $instance = new static(Bootstrap::get('mailer'));

        $message = Swift_Message::newInstance();
        call_user_func($callback, $message);

        if ($message->getBody() == null) {
            // Slim Extras View twig act weird on this version
            ob_start();
            $app = Bootstrap::get('app');
            $viewFullPath = $app->container['settings']['templates.path'].$viewPath;
            $data = array_merge(['settings'=>$instance->settings], $data);
            extract($data);
            include $viewFullPath;
            $viewContent = nl2br(ob_get_clean());
            $message->setBody($viewContent, 'text/html');
        }

        $instance->sendMessage($message);
    }
}