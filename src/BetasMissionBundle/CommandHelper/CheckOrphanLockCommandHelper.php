<?php

namespace BetasMissionBundle\CommandHelper;


use BetasMissionBundle\Helper\Locker;
use BetasMissionBundle\Helper\Mailer;
use BetasMissionBundle\Helper\Logger;
use BetasMissionBundle\MailType\OrphanLockMessage;
use DateTime;

class CheckOrphanLockCommandHelper extends AbstractCommandHelper
{
    const LOCK_FILE_LIFETIME = 3600;
    /**
     * @var Mailer $mailer
     */
    private $mailer;

    /**
     * CheckOrphanLockCommandHelper constructor.
     *
     * @param Logger $logger
     * @param Mailer $mailer
     */
    public function __construct(Logger $logger, Mailer $mailer)
    {
        parent::__construct($logger);
        $this->mailer = $mailer;
    }


    /**
     * @param string $tempFile
     *
     * @return int
     */
    public function getTimeStampLock($tempFile)
    {
        return file_exists($tempFile) ? filectime($tempFile) : 0;
    }

    /**
     * @param string   $tempFile
     * @param DateTime $sinceWhen
     *
     * @return int
     */
    public function sendAlert($tempFile, DateTime $sinceWhen)
    {
        return $this->mailer->send((new OrphanLockMessage($tempFile, $sinceWhen))->getMessage());
    }

    /**
     * Return true if the given file is one of the app lock file
     *
     * @param string $tempFile
     *
     * @return bool
     */
    public function isBetasmissionLockFile($tempFile)
    {
        return strpos($tempFile, Locker::LOCK_FILE) === false;
    }

    /**
     * Return true if the given timestamp is out of date
     *
     * @param int $lockFileTimeStamp
     *
     * @return bool
     */
    public function isLockFileOutOfDate($lockFileTimeStamp)
    {
        return $lockFileTimeStamp != 0 && (new DateTime('now'))->getTimestamp() > $lockFileTimeStamp + self::LOCK_FILE_LIFETIME;
    }
}