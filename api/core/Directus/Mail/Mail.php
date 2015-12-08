<?php

namespace Directus\Mail;

use \Clousure;
use \Swift_Message;
use Directus\Bootstrap;

class Mail
{
    protected $mailer = null;
    public function __construct($mailer)
    {
        $this->mailer = $mailer;
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
            extract($data);
            include $viewFullPath;
            $viewContent = ob_get_clean();
            $message->setBody($viewContent);
        }

        $instance->sendMessage($message);
    }
}