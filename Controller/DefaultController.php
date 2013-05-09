<?php

namespace Azine\GeoBlockingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AzineGeoBlockingBundle:Default:index.html.twig', array('name' => $name));
    }
}
