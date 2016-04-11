<?php

namespace BetasMission\MailType;

use DateTime;

/**
 * Class OrphanLockMessage
 */
class OrphanLockMessage extends AbstractMessage
{
    /**
     * @var string
     */
    private $lockFile;

    /**
     * @var DateTime
     */
    private $sinceWhen;

    /**
     * @param string   $lockFile
     * @param DateTime $sinceWhen
     */
    public function __construct($lockFile, DateTime $sinceWhen)
    {
        $this->lockFile  = $lockFile;
        $this->sinceWhen = $sinceWhen;
    }

    /**
     * @return string
     */
    protected function getSubject()
    {
        return 'Alert : script locked';
    }

    /**
     * @return string
     */
    protected function getBody()
    {
        $html = 'Hello<br/><br/>';
        $html .= sprintf('The script %s is locked since %s<br/>', $this->lockFile, $this->sinceWhen->format('Y-m-d H:i:s'));

        return $html;
    }
}
