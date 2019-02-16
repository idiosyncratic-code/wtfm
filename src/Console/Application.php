<?php

declare(strict_types=1);

namespace Idiosyncratic\Wtfm\Console;

use Exception;
use Symfony\Component\Console\Application as SymfonyConsoleApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function dirname;
use function getcwd;
use function getenv;
use function in_array;
use function is_file;
use function realpath;
use function sprintf;

class Application extends SymfonyConsoleApplication
{
    protected const VERSION = '0.1.0';

    /** @var string */
    protected $workingDir;

    /** @var string|null */
    protected $rootDir = null;

    public function __construct()
    {
        parent::__construct('Write the Forking Manual', self::VERSION);
    }

    /**
     * @inheritdoc
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->changeWorkingDir(getcwd() ?: '');

        if (! in_array($this->getCommandName($input), ['', 'version', 'help'])) {
            $this->setPackageRoot();
        }

        parent::doRun($input, $output);
    }

    protected function changeWorkingDir(string $dir) : void
    {
        $this->workingDir = $dir;
    }

    protected function getWorkingDir() : string
    {
        return $this->workingDir;
    }

    protected function setPackageRoot() : void
    {
        $home = realpath(getenv('HOME') ?: getenv('USERPROFILE') ?: '/');

        $dir = $this->getWorkingDir();

        while (dirname($dir) !== $dir && $dir !== $home) {
            if (is_file(sprintf('%s/composer.json', $dir))) {
                $this->rootDir =  $dir;

                break;
            }
        }

        if ($this->rootDir === null) {
            throw new Exception('Could not find the package root directory');
        }
    }
}
