<?php

namespace common\components\file\savers;

use GuzzleHttp\Client as HttpClient;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * Class Factory
 */
class Factory
{
    /**
     * @param string $content
     * @param string|null $fileName
     * @return null|UploadedFile
     */
    public static function buildFromString(string $content, string $fileName = null): ?UploadedFile
    {
        return new UploadedFile([
            'name' => $fileName,
            'tempName' => static::getTempFile($content),
            'type' => FileHelper::getMimeTypeByExtension($fileName),
            'size' => mb_strlen($content),
        ]);
    }

    /**
     * @param string $url
     * @param string|null $fileName
     * @return null|UploadedFile
     */
    public static function buildFromUrl(string $url, string $fileName = null): ?UploadedFile
    {
        // TODO: strict file type.
        // TODO: strict size.
        // TODO: use error codes.
        $client = new HttpClient();
        $response = $client->request('GET', $url);

        return new UploadedFile([
            'name' => $fileName ?? pathinfo($url, PATHINFO_BASENAME),
            'tempName' => static::getTempFile($response->getBody()),
            'type' => $response->getHeaderLine('Content-Type'),
            'size' => (int) $response->getHeaderLine('Content-Length'),
        ]);
    }

    /**
     * @param string $fileName
     * @return null|UploadedFile
     */
    public static function buildFromFile(string $fileName): ?UploadedFile
    {
        return new UploadedFile([
            'name' => $fileName,
            'tempName' => $fileName,
            'type' => FileHelper::getMimeTypeByExtension($fileName),
            'size' => filesize($fileName),
        ]);
    }

    /**
     * @param string $content
     * @return bool|resource
     */
    protected static function getTempFile(string $content)
    {
        $stream = fopen('php://memory','rb+');
        fwrite($stream, $content); // write file into stream
        rewind($stream); // reset stream pointer to start

        return $stream;
    }
}