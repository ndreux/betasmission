<?php

namespace BetasMission\MailType;

use Swift_Mime_SimpleMimeEntity;

/**
 * Class AbstractMessage
 */
abstract class AbstractMessage
{
    /**
     * @return string
     */
    abstract protected function getSubject();

    /**
     * @return string
     */
    abstract protected function getBody();

    /**
     * @return Swift_Mime_SimpleMimeEntity
     */
    public function getMessage()
    {
        $subject = '['.getenv('env').'] - '.$this->getSubject();

        return \Swift_Message::newInstance($subject)
            ->setFrom($this->getFrom())
            ->setTo($this->getTo())
            ->setBody($this->getBody())
            ->setContentType('text/html');
    }

    /**
     * @return string[]
     */
    protected function getFrom()
    {
        return ['no-reply@labox-ndr.no-ip.org' => 'Raspberry Pi'];
    }

    /**
     * @return string[]
     */
    protected function getTo()
    {
        return ['nclsdrx@gmail.com'];
    }
}
