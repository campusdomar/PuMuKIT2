<?php

namespace Pumukit\WorkflowBundle\Tests\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Pumukit\EncoderBundle\Services\PicExtractorService;
use Pumukit\SchemaBundle\Document\MultimediaObject;
use Pumukit\SchemaBundle\Document\Pic;
use Pumukit\SchemaBundle\Document\Series;
use Pumukit\SchemaBundle\Document\Track;
use Pumukit\SchemaBundle\Services\MultimediaObjectPicService;
use Pumukit\WorkflowBundle\EventListener\PicExtractorListener;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @coversNothing
 */
class PicExtractorListenerTest extends WebTestCase
{
    /**
     * @var DocumentManager
     */
    private $dm;
    private $repo;
    private $logger;
    private $picExtractorListener;
    private $videoPath;
    private $factoryService;
    private $profileService;
    private $mmsPicService;
    private $autoExtractPic = true;

    public function setUp()
    {
        $options = ['environment' => 'test'];
        static::bootKernel($options);

        $this->dm = static::$kernel->getContainer()->get('doctrine_mongodb')->getManager();
        $this->repo = $this->dm->getRepository(MultimediaObject::class);
        $this->logger = static::$kernel->getContainer()->get('logger');
        $this->videoPath = realpath(__DIR__.'/../Resources/data/track.mp4');
        $this->factoryService = static::$kernel->getContainer()->get('pumukitschema.factory');
        $this->profileService = static::$kernel->getContainer()->get('pumukitencoder.profile');

        $this->dm->getDocumentCollection(MultimediaObject::class)->remove([]);
        $this->dm->getDocumentCollection(Series::class)->remove([]);

        $mmsPicService = $this->getMockBuilder(MultimediaObjectPicService::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $mmsPicService
            ->method('addPicFile')
            ->willReturn('multimedia object')
        ;

        $picExtractorService = $this->getMockBuilder(PicExtractorService::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $picExtractorService
            ->method('extractPic')
            ->willReturn('success')
        ;
        $this->picExtractorListener = new PicExtractorListener($this->dm, $picExtractorService, $this->logger, $this->profileService, $this->autoExtractPic);
    }

    public function tearDown(): void
    {
        $this->dm->close();
        $this->repo = null;
        $this->logger = null;
        $this->videoPath = null;
        $this->factoryService = null;
        $this->picExtractorListener = null;
        gc_collect_cycles();
        parent::tearDown();
    }

    public function testGeneratePicFromVideo(): void
    {
        $this->generatePicFromFile();
    }

    public function testAddDefaultAudioPic(): void
    {
        $this->markTestSkipped('S');

        $this->generatePicFromFile(true);
    }

    public function testPicExtractorVideoError(): void
    {
        $mmsPicService = $this->getMockBuilder(MultimediaObjectPicService::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $mmsPicService
            ->method('addPicFile')
            ->willReturn('multimedia object')
        ;

        $picExtractorService = $this->getMockBuilder(PicExtractorService::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $picExtractorService
            ->method('extractPic')
            ->willReturn('Error')
        ;
        $picExtractorListener = new PicExtractorListener($this->dm, $picExtractorService, $this->logger, $this->profileService, $this->autoExtractPic);

        $this->generatePicFromFileError($picExtractorListener);
    }

    public function testPicExtractorAudioError(): void
    {
        $this->markTestSkipped('S');

        $mmsPicService = $this->getMockBuilder(MultimediaObjectPicService::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $mmsPicService
            ->method('addPicFile')
            ->willReturn(null)
        ;
        $picExtractorService = $this->getMockBuilder(PicExtractorService::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $picExtractorService
            ->method('extractPic')
            ->willReturn('success')
        ;

        $picExtractorListener = new PicExtractorListener($this->dm, $picExtractorService, $this->logger, $this->profileService, $this->autoExtractPic);

        $this->generatePicFromFileError($picExtractorListener, true);
    }

    private function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    private function generatePicFromFile(bool $isAudio = false): void
    {
        [$mm, $track] = $this->createMultimediaObjectAndTrack($isAudio);

        $this->assertTrue($mm->getPics()->isEmpty());
        $this->assertCount(0, $mm->getPics()->toArray());
        $this->assertTrue($this->invokeMethod($this->picExtractorListener, 'generatePic', [$mm, $track]));

        $pic = new Pic();
        $mm->addPic($pic);

        $this->dm->persist($mm);
        $this->dm->flush();

        $this->assertFalse($mm->getPics()->isEmpty());
        $this->assertCount(1, $mm->getPics()->toArray());
        $this->assertFalse($this->invokeMethod($this->picExtractorListener, 'generatePic', [$mm, $track]));
    }

    private function generatePicFromFileError(PicExtractorListener $picExtractorListener, bool $isAudio = false): void
    {
        [$mm, $track] = $this->createMultimediaObjectAndTrack($isAudio);

        $this->assertTrue($mm->getPics()->isEmpty());
        $this->assertCount(0, $mm->getPics()->toArray());
        $this->assertFalse($this->invokeMethod($picExtractorListener, 'generatePic', [$mm, $track]));
    }

    private function createMultimediaObjectAndTrack(bool $isAudio): array
    {
        $series = $this->factoryService->createSeries();
        $mm = $this->factoryService->createMultimediaObject($series);

        $track = new Track();
        $track->addTag('master');
        $track->setPath($this->videoPath);
        $track->setOnlyAudio($isAudio);
        $track->setWidth(640);
        $track->setHeight(480);

        $mm->addTrack($track);

        $this->dm->persist($mm);
        $this->dm->flush();

        return [
            $mm,
            $track,
        ];
    }
}
