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
     *
     * @return bool
     */
    public function copy($src, $dst)
    {
        if (is_file($src)) {
            if (!is_dir(pathinfo($dst, PATHINFO_DIRNAME))) {
                $this->mkdir(pathinfo($dst, PATHINFO_DIRNAME));
            }

            $this->logger->info('Copy : '.$src.' to '.$dst);

            return copy($src, $dst);
        }

        foreach ($this->scandir($src) as $file) {

            $srcPath = $src.'/'.$file;
            $dstPath = $dst.'/'.$file;


            $this->copy($srcPath, $dstPath);
            $this->logger->info('Copy : '.$srcPath.' to '.$dstPath);

        }

        return false;
    }

    /**
     * @param string $src
     *
     * @return bool
     */
    public function remove($src)
    {
        if (is_file($src)) {
            $this->logger->info('Remove : '.$src);

            return unlink($src);
        }

        foreach ($this->scandir($src) as $file) {
            $filePath = $src.'/'.$file;
            $this->remove($filePath);
        }

        $this->logger->info('Remove : '.$src);

        return rmdir($src);
    }

    /**
     * Create recursively a directory with 777 permission
     *
     * @param string $path
     *
     * @return bool
     */
    private function mkdir($path)
    {
        return mkdir($path, 0777, true);
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    public function isVideo($file)
    {
        $fileInfo = pathinfo($file);
        if (!isset($fileInfo['extension'])) {
            return false;
        }

        $isVideo = in_array($fileInfo['extension'], ['mp4', 'mkv', 'avi']);
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
