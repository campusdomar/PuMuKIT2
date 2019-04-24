<?php

namespace Pumukit\BasePlayerBundle\Controller;

use Pumukit\SchemaBundle\Document\MultimediaObject;
use Pumukit\SchemaBundle\Document\Track;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Pumukit\BasePlayerBundle\Event\BasePlayerEvents;
use Pumukit\BasePlayerBundle\Event\ViewedEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

abstract class BasePlayerController extends Controller
{
    /**
     * @Route("/videoplayer/{id}", name="pumukit_videoplayer_index")
     *
     * @param Request          $request
     * @param MultimediaObject $multimediaObject
     *
     * @return mixed
     */
    abstract public function indexAction(Request $request, MultimediaObject $multimediaObject);

    /**
     * @Route("/videoplayer/magic/{secret}", name="pumukit_videoplayer_magicindex")
     *
     * @param Request          $request
     * @param MultimediaObject $multimediaObject
     *
     * @return mixed
     */
    abstract public function magicAction(Request $request, MultimediaObject $multimediaObject);

    /**
     * @param MultimediaObject $multimediaObject
     * @param Track|null       $track
     */
    protected function dispatchViewEvent(MultimediaObject $multimediaObject, Track $track = null)
    {
        $event = new ViewedEvent($multimediaObject, $track);
        $this->get('event_dispatcher')->dispatch(BasePlayerEvents::MULTIMEDIAOBJECT_VIEW, $event);
    }
}
