<?php

namespace BetasMission\Business;

use BetasMission\Helper\Logger;

/**
 * Class FileManagementBusiness
 */
class FileManagementBusiness
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * FileManagementBusiness constructor.
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $src
     * @param string $dst
     */
    public function copy($src, $dst)
    {
        if (is_file($src)) {
            copy($src, $dst);
        } else {
            $dir = opendir($src);
            mkdir($dst);
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src.'/'.$file)) {
                        $this->copy($src.'/'.$file, $dst.'/'.$file);
                    } else {
                        $this->logger->log('Copy : '.$src.'/'.$file.' to '.$dst.'/'.$file);
                        copy($src.'/'.$file, $dst.'/'.$file);
                    }
                }
            }
            closedir($dir);
        }
    }

    /**
     * @param string $src
     */
    public function remove($src)
    {
        if (is_file($src)) {
            unlink($src);
        } else {
            $dir = opendir($src);
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src.'/'.$file)) {
                        $this->remove($src.'/'.$file);
                    } else {
                        $this->logger->log('Remove : '.$src.'/'.$file);
                        unlink($src.'/'.$file);
                    }
                }
            }
            $this->logger->log('Remove : '.$src);
            rmdir($src);
            closedir($dir);
        }
    }
}
