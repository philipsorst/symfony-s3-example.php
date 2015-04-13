<?php

namespace Dontdrinkandroot\SymfonyS3ExampleBundle\Controller;

use Aws\S3\S3Client;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $s3Client = $this->get('ddr_symfony_s3_example.s3client');
        var_dump($s3Client->listBuckets());

        return $this->render('DdrSymfonyS3ExampleBundle:Default:index.html.twig');
    }
}
