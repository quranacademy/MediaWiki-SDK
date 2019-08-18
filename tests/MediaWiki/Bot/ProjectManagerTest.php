<?php

declare(strict_types=1);

namespace MediaWiki\Tests\MediaWiki\Bot;

use MediaWiki\Api\ApiCollection;
use MediaWiki\Bot\ProjectManager;
use MediaWiki\Project\ProjectFactoryInterface;
use MediaWiki\Services\ServiceManager;
use MediaWiki\Tests\Stubs\ExampleProject;
use MediaWiki\Tests\TestCase;
use Mockery;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class ProjectManagerTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $projectsDirectory;

    /**
     * @var string
     */
    private $projectsDirectoryPath;

    public function setUp(): void
    {
        $this->projectsDirectory = vfsStream::setup('projects');
        $this->projectsDirectoryPath = vfsStream::url('projects');
    }

    public function testSetGetNamespace(): void
    {
        $projectFactory = Mockery::mock(ProjectFactoryInterface::class);

        $projectManager = new ProjectManager($projectFactory, $this->projectsDirectoryPath);

        // returns $this
        $this->assertEquals($projectManager, $projectManager->setNamespace('MyNamespace'));
        $this->assertEquals('MyNamespace', $projectManager->getNamespace());
    }

    public function testProjectExists(): void
    {
        $projectFactory = Mockery::mock(ProjectFactoryInterface::class);

        $projectManager = new ProjectManager($projectFactory, $this->projectsDirectoryPath);

        $this->assertFalse($projectManager->projectExists('foo'));

        vfsStream::newFile('foo.php')->at($this->projectsDirectory);

        $this->assertTrue($projectManager->projectExists('foo'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testLoadNotExistingProject(): void
    {
        $projectFactory = Mockery::mock(ProjectFactoryInterface::class);

        $projectManager = new ProjectManager($projectFactory, $this->projectsDirectoryPath);

        $projectManager->loadProject('foo');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLoadProject(): void
    {
        $content = file_get_contents(__DIR__.'/../../Stubs/ExampleProject.php');

        vfsStream::newFile('example-project.php')->at($this->projectsDirectory)->withContent($content);

        require_once $this->projectsDirectoryPath.'/example-project.php';

        $projectFactory = Mockery::mock(ProjectFactoryInterface::class);

        $apiCollection = new ApiCollection();
        $serviceManager = new ServiceManager($apiCollection);

        $project = new ExampleProject($apiCollection, $serviceManager);

        $projectFactory->shouldReceive('createProject')->with(
            ExampleProject::getApiUrls(),
            'MediaWiki\Tests\Stubs\ExampleProject'
        )->once()->andReturn($project);

        $projectsFolder = vfsStream::url('projects');

        $projectManager = new ProjectManager($projectFactory, $projectsFolder);

        $projectManager->setNamespace('MediaWiki\Tests\Stubs');

        $loadedProject = $projectManager->loadProject('example-project');

        $this->assertEquals($project, $loadedProject);
    }

    public function testProjectsFolder(): void
    {
        $projectFactory = Mockery::mock(ProjectFactoryInterface::class);

        $projectManager = new ProjectManager($projectFactory, $this->projectsDirectoryPath);

        $this->assertEquals($this->projectsDirectoryPath, $projectManager->getProjectsDirectory());
    }
}
