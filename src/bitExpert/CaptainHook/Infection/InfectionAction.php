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
        $options = $action->getOptions()->getAll();
        $infectionCli = $options['infection'] ?? './vendor/bin/infection';
        $infectionArgs = (isset($options['args']) && is_array($options['args'])) ? $options['args'] : [];

        $changedPHPFiles = $repository->getIndexOperator()->getStagedFilesOfType('php');
        if (count($changedPHPFiles) > 0) {
            array_walk($changedPHPFiles, function (&$item, $key) {
                $item = escapeshellarg($item);
            });
            $infectionArgs[] = '--filter='.implode(',', $changedPHPFiles);
        }

        $result = $this->invokeInfectionProcess($infectionCli, $infectionArgs);
        if (!$result->isSuccessful()) {
            $errorMessage = '<error>Running Infection failed!</error>';

            if (!empty($result->getStdOut())) {
                $errorMessage .= PHP_EOL . $result->getStdOut();
            }

            if (!empty($result->getStdErr())) {
                $errorMessage .= PHP_EOL . $result->getStdErr();
            }

            throw new ActionFailed($errorMessage);
        }

        if (!empty($result->getStdOut())) {
            $io->write($result->getStdOut());
        }
    }

    /**
     * @param string $infectionCli
     * @param array<string> $infectionArgs
     * @return Result
     */
    protected function invokeInfectionProcess(string $infectionCli, array $infectionArgs): Result
    {
        $infectionCli = escapeshellcmd($infectionCli);
        foreach ($infectionArgs as $infectionArg) {
            $infectionCli .= ' ' . $infectionArg;
        }

        return $this->getProcessor()->run($infectionCli);
    }

    /**
     * @return Processor
     */
    protected function getProcessor(): Processor
    {
        return new Processor();
    }
}
