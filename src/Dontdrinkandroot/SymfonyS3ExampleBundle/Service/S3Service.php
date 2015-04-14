<?php

namespace Dontdrinkandroot\SymfonyS3ExampleBundle\Service;

use Aws\S3\S3Client;
use Guzzle\Service\Resource\ResourceIteratorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3Service
{

    /**
     * @var S3Client
     */
    private $s3Client;

    /**
     * @var string
     */
    private $region;

    public function __construct(S3Client $s3Client, $region = 'eu-central-1')
    {
        $this->s3Client = $s3Client;
        $this->region = $region;
    }

    /**
     * @return array
     */
    public function listBuckets()
    {
        $buckets = $this->s3Client->listBuckets();

        return $buckets->get('Buckets');
    }

    /**
     * @param $bucketName
     *
     * @return ResourceIteratorInterface
     */
    public function listBucket($bucketName)
    {
        $iterator = $this->s3Client->getIterator(
            'ListObjects',
            [
                'Bucket' => $bucketName
            ]
        );

        return $iterator;
    }

    /**
     * @param string       $bucketName
     * @param string       $fileName
     * @param UploadedFile $file
     *
     * @return bool
     */
    public function uploadFile($bucketName, $fileName, $file)
    {
        $actualFileName = $fileName;
        if (empty($fileName)) {
            $actualFileName = $file->getClientOriginalName();
        }

        $result = $this->s3Client->putObject(
            [
                'Bucket'     => $bucketName,
                'Key'        => $actualFileName,
                'SourceFile' => $file->getRealPath()
            ]
        );

        $this->s3Client->waitUntilObjectExists(
            [
                'Bucket' => $bucketName,
                'Key'    => $actualFileName
            ]
        );

        return true;
    }

    /**
     * @param string $bucketName
     * @param string $key
     *
     * @return bool
     */
    public function deleteFile($bucketName, $key)
    {
        $this->s3Client->deleteObject(
            [
                'Bucket' => $bucketName,
                'Key'    => $key
            ]
        );

        return true;
    }

    /**
     * @param string $bucketName
     *
     * @return bool
     */
    public function createBucket($bucketName)
    {
        $this->s3Client->createBucket(
            [
                'Bucket'             => $bucketName,
                'LocationConstraint' => $this->region
            ]
        );

        $this->s3Client->waitUntilBucketExists(
            [
                'Bucket' => $bucketName
            ]
        );

        return true;
    }

    /**
     * @param string $bucketName
     *
     * @return bool
     */
    public function deleteBucket($bucketName)
    {
        $this->s3Client->deleteBucket(
            [
                'Bucket' => $bucketName
            ]
        );

        $this->s3Client->waitUntilBucketNotExists(
            [
                'Bucket' => $bucketName
            ]
        );

        return true;
    }

    /**
     * @param string $bucketName
     * @param string $key
     *
     * @return string
     */
    public function getSignedUrl($bucketName, $key)
    {
        $signedUrl = $this->s3Client->getObjectUrl($bucketName, $key, '+10 minutes');

        return $signedUrl;
    }
}
