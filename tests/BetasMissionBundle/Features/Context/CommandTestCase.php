<?php

namespace Tests\BetasMissionBundle\Features\Context;

use AppKernel;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

class CommandTestCase extends WebTestCase
{

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string[]
     */
    protected static $createdFiles;

    /**
     * @BeforeSuite
     */
    public static function prepare()
    {
        static::$createdFiles = [];
    }
    
    /**
     * @AfterSuite
     */
    public static function clean()
    {
        foreach (static::$createdFiles as $file) {
            static::remove($file);
        }
        static::$createdFiles = [];
    }
    
    /**
     * Runs a command and returns it output
     */
    public function runCommand(Client $client, $command)
    {
        $application = new Application($client->getKernel());
        $application->setAutoExit(false);

        $fp     = tmpfile();
        $input  = new StringInput($command);
        $output = new StreamOutput($fp);

        $application->run($input, $output);

        fseek($fp, 0);
        $txtOutput = '';
        while (!feof($fp)) {
            $txtOutput = fread($fp, 4096);
        }
        fclose($fp);

        return $txtOutput;
    }

    /**
     * @param array $options
     *
     * @return AppKernel
     */
    protected static function createKernel(array $options = [])
    {
        return new AppKernel('test', false);
    }

    /**
     * @param string $file
     */
    protected function createFile($file)
    {
        $pathInfo = pathinfo($file);

        if (!is_dir($pathInfo['dirname'])) {
            mkdir($pathInfo['dirname'], 0777, true);
        }

        touch($file);
    }

    /**
     * @param string $src
     */
    protected static function remove($src)
    {
        if (!file_exists($src)) {
            return;
        }

        if (is_file($src)) {
            unlink($src);
        } else {
            $dir = opendir($src);
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src.'/'.$file)) {
                        static::remove($src.'/'.$file);
                    } else {
                        unlink($src.'/'.$file);
                    }
                }
            }
            rmdir($src);
            closedir($dir);
        }
    }
}