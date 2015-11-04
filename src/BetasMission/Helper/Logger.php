<?php

namespace BetasMission\Helper;

use DateTime;

/**
 * Class Logger
 */
class Logger
{
    const LOG_PATH = '/var/log/betasmission/';
    const LOG_FILE = 'betasmission-%s';

    /** @var string  */
    private $logfile;

    /** @var string */
    private $context;

    /**
     * Constructor
     */
    public function __construct($context = null)
    {
        $this->logfile = $this->getLogfile();
        $this->context = $context;
    }

    /**
     * Log a message in the logfile of the day
     *
     * @param string $message
     *
     * @return int
     */
    public function log($message)
    {
        if (!$this->isTestEnv()) {
            return 0;
        }

        $now = new DateTime();
        $log = $now->format('Y-m-d H:i:s').' - '.$message."\n";

        return file_put_contents($this->getLogfile(), $log, FILE_APPEND);
    }

    /**
     * @return string
     */
    private function getLogfile()
    {
        $logfile = sprintf(self::LOG_FILE, date('Y-m-d'));

        if (!in_array($this->context, Context::getAvailableContexts())) {
            return self::LOG_PATH.$logfile.'.log';
        }

        return self::LOG_PATH.$logfile.$this->context.'.log';
    }

    /**
     * @return bool
     */
    private function isTestEnv()
    {
        return getenv('env') == 'TEST';
    }
}
