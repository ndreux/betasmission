<?php

namespace BetasMissionBundle\MailType;

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
        return \Swift_Message::newInstance($this->getSubject())
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
        return ['no-reply@labox-ndr.no-ip.org' => '['.getenv('env').'] - Raspberry Pi'];
    }

    /**
     * @return string[]
     */
    protected function getTo()
    {
        return ['nclsdrx@gmail.com'];
    }
}
