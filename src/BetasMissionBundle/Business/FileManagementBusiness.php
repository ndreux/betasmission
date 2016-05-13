<?php

namespace BetasMissionBundle\Business;

use Symfony\Bridge\Monolog\Logger;

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
            if (!is_dir(pathinfo($dst, PATHINFO_DIRNAME))) {
                mkdir(pathinfo($dst, PATHINFO_DIRNAME), 0777, true);
            }
            copy($src, $dst);
        } else {
            $dir = opendir($src);
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src.'/'.$file)) {
                        $this->copy($src.'/'.$file, $dst.'/'.$file);
                    } else {
                        if (!is_dir($dst)) {
                            mkdir($dst, 0777, true);
                        }
                        $this->logger->info('Copy : '.$src.'/'.$file.' to '.$dst.'/'.$file);
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
                        $this->logger->info('Remove : '.$src.'/'.$file);
                        unlink($src.'/'.$file);
                    }
                }
            }
            $this->logger->info('Remove : '.$src);
            rmdir($src);
            closedir($dir);
        }
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    public function isVideo($file)
    {
        if (!is_file($file)) {
            return false;
        }

        $isVideo = in_array(pathinfo($file, PATHINFO_EXTENSION), ['mp4', 'mkv', 'avi']);
        (!$isVideo) ? $this->logger->info(sprintf('The file %s is not a video file.', $file)) : null;

        return $isVideo;
    }

    /**
     * @param $directory
     *
     * @return string[]
     */
    public function scandir($directory)
    {
        return array_diff(scandir($directory), ['..', '.']);
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    public function isZip($file)
    {
        $filePathInfo = pathinfo($file);

        return in_array($filePathInfo['extension'], ['zip']);
    }

    /**
     * @param $text
     *
     * @return mixed|string
     */
    public function slugify($text)
    {
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = str_replace('-', '.', $text);

        return (empty($text)) ? null : $text;
    }
}
