<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\saver;

use GuzzleHttp\Client as HttpClient;
use Mimey\MimeTypes;
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
            'type' => (new MimeTypes())->getMimeType(pathinfo($fileName)['extension'] ?? null),
            'size' => mb_strlen($content),
        ]);
    }

    /**
     * @param string $url
     * @param string|null $fileName
     * @return null|UploadedFile
     * @throws \GuzzleHttp\Exception\GuzzleException
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
            'tempName' => static::getTempFile((string) $response->getBody()),
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
            'type' => (new MimeTypes())->getMimeType(pathinfo($fileName)['extension'] ?? null),
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