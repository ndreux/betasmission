<?php

namespace BetasMission\Helper;

use Swift_Mailer;
use Swift_Mime_SimpleMimeEntity;

/**
 * Class Mailer
 */
class Mailer
{
    /**
     * @param Swift_Mime_SimpleMimeEntity $message
     *
     * @return int
     */
    public function send(Swift_Mime_SimpleMimeEntity $message)
    {
        return $this->getSwiftMailerInstance()->send($message);
    }

    /**
     * @return Swift_Mailer
     */
    private function getSwiftMailerInstance()
    {
        $transport = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
            ->setUsername('osaxis20@gmail.com')
            ->setPassword('admin#osaxis');

        return new \Swift_Mailer($transport);
    }
}