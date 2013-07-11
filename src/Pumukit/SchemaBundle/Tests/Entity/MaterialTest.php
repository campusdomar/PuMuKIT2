<?php

namespace Pumukit\SchemaBundle\Tests\Entity;

use Pumukit\SchemaBundle\Entity\MultimediaObject;
use Pumukit\SchemaBundle\Entity\Material;

class MaterialTest extends \PHPUnit_Framework_TestCase
{

    public function testGetterAndSetter()
    {
        $mm     = new MultimediaObject();
        $tags   = array('tag_a', 'tag_b');
        $url    = '/mnt/video/123/23435.mp4';
        $path   = '/mnt/video/123/23435.mp4';
        $mime   = 'video/mpeg4';
        $rank   = 123;
        $hide   = FALSE;

        
        $material = new material();

        $material->setMultimediaObject($mm);
        $material->setTags($tags);
        $material->setUrl($url);
        $material->setPath($path);
        $material->setMimeType($mime);
        $material->setRank($rank);
        $material->setHide($hide);

        $this->assertEquals($mm, $material->getMultimediaObject());      
        $this->assertEquals($tags, $material->getTags());
        $this->assertEquals($url, $material->getUrl());
        $this->assertEquals($path, $material->getPath());
        $this->assertEquals($mime, $material->getMimeType());
        $this->assertEquals($rank, $material->getRank());
        $this->assertFalse($hide, $material->getHide());
    }

    public function testMaxSize()
    {
        $size      = 5368709120; // 5GB, integer types in 32 bits machines only admit 2GB
        
        $material    = new material();
        $material->setSize($size);
        $this->assertEquals($size,$material->getSize());
    }

    public function testTagCollection()
    {
        $material = new material();
        $this->assertFalse($material->containsTag('t'));
        $material->addTag('t');
        $this->assertTrue($material->containsTag('t'));
        $material->removeTag('t');
        $this->assertFalse($material->containsTag('t'));
        
        //Repeat Tag
        $this->assertFalse($material->containsTag('t'));
        $material->addTag('t');
        $material->addTag('t');
        $this->assertTrue($material->containsTag('t'));
        $material->removeTag('t');
        $this->assertFalse($material->containsTag('t'));
        $this->assertFalse($material->removeTag('t'));

        
        //containsAllTag and containsAnyTag
        $material->addTag('t1');
        $material->addTag('t2');
        $material->addTag('t3');
        $this->assertTrue($material->containsAnyTag(array('t0', 't2')));
        $this->assertTrue($material->containsAnyTag(array('t2', 't3')));
        $this->assertFalse($material->containsAnyTag(array('t0', 't4')));
        $this->assertTrue($material->containsAllTags(array('t1', 't2')));
        $this->assertTrue($material->containsAllTags(array('t1')));
        $this->assertFalse($material->containsAllTags(array('t0', 't2')));
        $this->assertFalse($material->containsAllTags(array('t0', 't1', 't2', 't3')));
    }


    public function testRef()
    {
        $t1 = new material();
        $t2 = new material();

        $t2->setRef($t1);
        $this->assertEquals(null, $t1->getRef());
        $this->assertEquals($t1, $t2->getRef());	
    }

}