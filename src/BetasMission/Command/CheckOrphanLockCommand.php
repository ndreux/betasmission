<?php

namespace BetasMission\Command;

use BetasMission\Helper\Locker;
use BetasMission\Helper\Mailer;
use BetasMission\MailType\OrphanLockMessage;
use DateTime;

/**
 * Class CheckOrphanLockCommand
 */
class CheckOrphanLockCommand extends AbstractCommand
{
    /**
     * @return bool|int
     */
    public function execute()
    {
        $tempFiles = scandir(Locker::LOCK_PATH);

        foreach ($tempFiles as $tempFile) {
            if (strpos($tempFile, Locker::LOCK_FILE) === false) {
                continue;
            }

            $lockFileTimeStamp = $this->getTimeStampLock(Locker::LOCK_PATH.$tempFile);
            if ($lockFileTimeStamp != 0 && (new DateTime('now'))->getTimestamp() > $lockFileTimeStamp + 3600) {
                return $this->sendAlert($tempFile, (new DateTime('now'))->setTimestamp($lockFileTimeStamp));
            }
        }

        return false;
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
     * @return int
     */
    public function sendAlert($tempFile, DateTime $sinceWhen)
    {
        return $this->getMailer()->send((new OrphanLockMessage($tempFile, $sinceWhen))->getMessage());
    }

    /**
     * @return Mailer
     */
    public function getMailer()
    {
        return new Mailer();
    }
}
