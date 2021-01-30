<?php

/*
 * This file is part of the Captain Hook Infection plugin package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace bitExpert\CaptainHook\Infection;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Exception\ActionFailed;
use CaptainHook\App\Hook\Action;
use SebastianFeldmann\Cli\Command\Result;
use SebastianFeldmann\Cli\Processor\ProcOpen as Processor;
use SebastianFeldmann\Git\Repository;

class InfectionAction implements Action
{
    /**
     * @var Processor
     */
    private $runner;

    /**
     * Creates new {@link \bitExpert\CaptainHook\Infection\InfectionAction}.
     */
    public function __construct()
    {
        $this->runner = new Processor();
    }

    /**
     * Executes the action.
     *
     * @param \CaptainHook\App\Config $config
     * @param \CaptainHook\App\Console\IO $io
     * @param \SebastianFeldmann\Git\Repository $repository
     * @param \CaptainHook\App\Config\Action $action
     * @return void
     * @throws \Exception
     */
    public function execute(Config $config, IO $io, Repository $repository, Config\Action $action): void
    {
    }

    /**
     * @param string $infectionCli
     * @param array $infectionArgs
     * @return Result
     */
    protected function invokeInfectionProcess(string $infectionCli, array $infectionArgs): Result
    {
        $infectionCli = escapeshellcmd($infectionCli);
        foreach($infectionArgs as $infectionArg) {
            $infectionCli .= ' ' . escapeshellarg($infectionArg);
        }

        return $this->runner->run($infectionCli);
    }
}
