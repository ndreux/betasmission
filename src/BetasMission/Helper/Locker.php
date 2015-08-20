<?php

namespace BetasMission\Helper;

/**
 * Class Locker.
 */
class Locker
{
    const LOCK_FILE = 'betasmission.lock';
    const LOCK_PATH = '/tmp/';

    /**
     * Constructor.
     *
     * @param null $context
     */
    public function __construct($context = null)
    {
        $this->context = $context;
    }

    /**
     */
    public function lock()
    {
        return file_put_contents($this->getLockFile(), ' ');
    }

    /**
     * @return bool
     */
    public function unlock()
    {
        if ($this->isLocked()) {
            return unlink($this->getLockFile());
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return (file_exists($this->getLockFile()));
    }

    /**
     * @return string
     */
    private function getLockFile()
    {
        if (!in_array($this->context, Context::getAvailableContexts())) {
            return self::LOCK_PATH.self::LOCK_FILE;
        }

        return self::LOCK_PATH.self::LOCK_FILE.$this->context;
    }
}
