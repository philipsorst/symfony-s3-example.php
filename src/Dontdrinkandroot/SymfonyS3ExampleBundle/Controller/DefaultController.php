<?php

namespace Dontdrinkandroot\SymfonyS3ExampleBundle\Controller;

use Dontdrinkandroot\SymfonyS3ExampleBundle\Service\S3Service;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    public function createBucketAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('name', 'text')
            ->add('save', 'submit', ['label' => 'Create Bucket'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $bucketName = $form->get('name')->getData();

            $this->getS3Service()->createBucket($bucketName);

            return $this->redirectToRoute('ddr_symfony_s3_example_list_bucket', ['bucketName' => $bucketName]);
        }

        return $this->render(
            'DdrSymfonyS3ExampleBundle:Default:createBucket.html.twig',
            ['form' => $form->createView()]
        );
    }

    public function deleteBucketAction($bucketName)
    {
        $this->getS3Service()->deleteBucket($bucketName);

        return $this->redirectToRoute('ddr_symfony_s3_example_list_buckets');
    }

    public function listBucketsAction()
    {
        $buckets = $this->getS3Service()->listBuckets();

        return $this->render('DdrSymfonyS3ExampleBundle:Default:listBuckets.html.twig', ['buckets' => $buckets]);
    }

    public function showFileAction($bucketName, $key)
    {
        $url = $this->getS3Service()->getSignedUrl($bucketName, $key);

        return $this->redirect($url);
    }

    public function listBucketAction($bucketName)
    {
        $resources = $this->getS3Service()->listBucket($bucketName);

        return $this->render(
            'DdrSymfonyS3ExampleBundle:Default:listBucket.html.twig',
            ['bucketName' => $bucketName, 'resources' => $resources]
        );
    }

    public function uploadFileAction(Request $request, $bucketName)
    {
        $form = $this->createFormBuilder()
            ->add('file', 'file')
            ->add('name', 'text', ['required' => false])
            ->add('save', 'submit', ['label' => 'Add to Bucket'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $files = $request->files;
            if ($files->count() > 1) {
                throw new \Exception('Do only upload one file at a time');
            }

            /** @var UploadedFile $file */
            $file = $files->getIterator()->current()['file'];

            $fileName = $form->get('name')->getData();

            $this->getS3Service()->uploadFile($bucketName, $fileName, $file);

            return $this->redirectToRoute('ddr_symfony_s3_example_list_bucket', ['bucketName' => $bucketName]);
        }

        return $this->render(
            'DdrSymfonyS3ExampleBundle:Default:uploadFile.html.twig',
            ['bucketName' => $bucketName, 'form' => $form->createView()]
        );
    }

    public function deleteFileAction($bucketName, $key)
    {
        $this->getS3Service()->deleteFile($bucketName, $key);

        return $this->redirectToRoute('ddr_symfony_s3_example_list_bucket', ['bucketName' => $bucketName]);
    }

    /**
     * @return S3Service
     */
    protected function getS3Service()
    {
        return $this->get('ddr_symfony_s3_example.s3service');
    }
}
