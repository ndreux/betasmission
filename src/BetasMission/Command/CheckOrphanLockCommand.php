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
            if ((new DateTime())->getTimestamp() > $lockFileTimeStamp + 3600) {
                return $this->sendAlert($tempFile, (new DateTime())->setTimestamp($lockFileTimeStamp));
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
        return filectime($tempFile);
    }

    /**
     * @return int
     */
    public function sendAlert($tempFile, DateTime $sinceWhen)
    {
        return (new Mailer())->send((new OrphanLockMessage($tempFile, $sinceWhen))->getMessage());
    }
}
