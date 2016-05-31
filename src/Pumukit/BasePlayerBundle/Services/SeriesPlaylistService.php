<?php

namespace Pumukit\BasePlayerBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Pumukit\SchemaBundle\Document\MultimediaObject;
use Pumukit\SchemaBundle\Document\Track;
use Pumukit\SchemaBundle\Document\Series;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SeriesPlaylistService
{
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
        $this->mmobjRepo = $this->dm->getRepository('PumukitSchemaBundle:MultimediaObject');
    }

    public function getPlaylistMmobjs(Series $series)
    {
        $mmobjs = array();
        $qb = $this->mmobjRepo->createStandardQueryBuilder()
                   ->field('series')->references($series);
        $mmobjs = $qb->getQuery()->execute();

        return $mmobjs;
    }
}
