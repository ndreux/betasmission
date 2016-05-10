<?php

namespace BetasMissionBundle\Helper;

use Swift_Mailer;
use Swift_Mime_SimpleMimeEntity;

/**
 * Class Mailer
 */
class Mailer
{
    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * Mailer constructor.
     *
     * @param string $user
     * @param string $password
     */
    public function __construct($user, $password)
    {
        $this->user     = $user;
        $this->password = $password;
    }


    /**
     * @param Swift_Mime_SimpleMimeEntity $message
     *
     * @return int
     */
    public function send(Swift_Mime_SimpleMimeEntity $message)
    {
        if ($this->isTestEnv()) {
            return 1;
        }

        return $this->getSwiftMailerInstance()->send($message);
    }

    /**
     * @return Swift_Mailer
     */
    private function getSwiftMailerInstance()
    {
        $transport = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
            ->setUsername($this->user)
            ->setPassword($this->password);

        return new \Swift_Mailer($transport);
    }

    /**
     * @return bool
     */
    private function isTestEnv()
    {
        return getenv('env') == 'TEST';
    }
}
