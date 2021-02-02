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
use CaptainHook\App\Config\Action;
use CaptainHook\App\Config\Options;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Exception\ActionFailed;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SebastianFeldmann\Cli\Command\Result;
use SebastianFeldmann\Cli\Processor\ProcOpen;
use SebastianFeldmann\Git\Operator\Index;
use SebastianFeldmann\Git\Repository;

class InfectionActionUnitTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $config;
    /**
     * @var IO|MockObject
     */
    private $io;
    /**
     * @var Repository|MockObject
     */
    private $repository;
    /**
     * @var Action|MockObject
     */
    private $action;
    /**
     * @var ValidateAuthorAction
     */
    private $hook;

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->config = $this->createMock(Config::class);
        $this->io = $this->createMock(IO::class);
        $this->repository = $this->createMock(Repository::class);
        $this->action = $this->createMock(Action::class);
        $this->hook = $this->createPartialMock(InfectionAction::class, ['invokeInfectionProcess']);
    }

    /**
     * @test
     */
    public function invokingInfectionSuccessfullWillNotThrowException()
    {
        $result = new Result('./vendor/bin/infection', 0);

        $this->hook->expects(self::once())
            ->method('invokeInfectionProcess')
            ->willReturn($result);

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function failingInfectionWillThrowException()
    {
        $this->expectException(ActionFailed::class);
        $this->expectExceptionMessageMatches('/<error>.+<\/error>\noutput\nerror/');

        $result = new Result('./vendor/bin/infection', 1, 'output', 'error');

        $this->hook->expects(self::once())
            ->method('invokeInfectionProcess')
            ->willReturn($result);

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function invokingInfectionWithDefaultPathIfNoConfigOptionWasPassed()
    {
        $result = new Result('./vendor/bin/infection', 0);

        $this->hook->expects(self::once())
            ->method('invokeInfectionProcess')
            ->with('./vendor/bin/infection', [])
            ->willReturn($result);

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function invokingInfectionWithCustomPath()
    {
        $result = new Result('infection.phar', 0);

        $this->action->expects(self::once())
            ->method('getOptions')
            ->willReturn(new Options(['infection' => 'infection.phar']));

        $this->hook->expects(self::once())
            ->method('invokeInfectionProcess')
            ->with('infection.phar', [])
            ->willReturn($result);

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function invokingInfectionWithAdditionalParamsAsArraySucceeds()
    {
        $result = new Result('./vendor/bin/infection', 0);

        $this->action->expects(self::once())
            ->method('getOptions')
            ->willReturn(new Options(['args' => ['-j4']]));

        $this->hook->expects(self::once())
            ->method('invokeInfectionProcess')
            ->with('./vendor/bin/infection', ['-j4'])
            ->willReturn($result);

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function invokingInfectionWithAdditionalParamsAsStringFails()
    {
        $result = new Result('./vendor/bin/infection', 0);

        $this->action->expects(self::once())
            ->method('getOptions')
            ->willReturn(new Options(['args' => '-j4']));

        $this->hook->expects(self::once())
            ->method('invokeInfectionProcess')
            ->with('./vendor/bin/infection', [])
            ->willReturn($result);

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function stagedGitFilesArePassedToInfectionViaFilterParam()
    {
        $result = new Result('./vendor/bin/infection', 0);

        $indexOperator = $this->createMock(Index::class);
        $indexOperator->expects(self::once())
            ->method('getStagedFilesOfType')
            ->willReturn(['src/test1.php', 'src/test2.php']);

        $this->repository->expects(self::once())
            ->method('getIndexOperator')
            ->willReturn($indexOperator);

        $this->hook->expects(self::once())
            ->method('invokeInfectionProcess')
            ->with('./vendor/bin/infection', ["--filter='src/test1.php','src/test2.php'"])
            ->willReturn($result);

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }

    /**
     * @test
     */
    public function cliPathAndParamsGetShellEscapedBeforeInvokingInfection()
    {
        $result = new Result('./vendor/bin/infection', 0);

        $this->hook = $this->createPartialMock(InfectionAction::class, ['getProcessor']);

        $processor = $this->createMock(ProcOpen::class);
        $processor->expects(self::once())
            ->method('run')
            ->with("\~./vendor/bin/infection --filter='src/te\$t1.php','src/te\$t2.php'")
            ->willReturn($result);

        $indexOperator = $this->createMock(Index::class);
        $indexOperator->expects(self::once())
            ->method('getStagedFilesOfType')
            ->willReturn(['src/te$t1.php', 'src/te$t2.php']);

        $this->action->expects(self::once())
            ->method('getOptions')
            ->willReturn(new Options(['infection' => '~./vendor/bin/infection']));

        $this->repository->expects(self::once())
            ->method('getIndexOperator')
            ->willReturn($indexOperator);

        $this->hook->expects(self::once())
            ->method('getProcessor')
            ->willReturn($processor);

        $this->hook->execute($this->config, $this->io, $this->repository, $this->action);
    }
}
