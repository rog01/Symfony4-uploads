<?php
/**
 * Created by PhpStorm.
 * User: James
 * Date: 07/01/2020
 * Time: 08:12
 */

namespace App\Service;


use Gedmo\Sluggable\Util\Urlizer;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploaderHelper
{
    const ARTICLE_IMAGE = 'article_image';
    const ARTICLE_REFERENCE = 'article_reference';

    private $filesystem;
    private $requestStackContext;
    private $publicAssetBaseUrl;
    private $logger;

    public function __construct(FilesystemInterface $uploadsFilesystem,RequestStackContext $requestStackContext, LoggerInterface $logger, String $uploaderAssetsBaseUrl){

        $this->filesystem = $uploadsFilesystem;
        $this->requestStackContext = $requestStackContext;
        $this->logger = $logger;
        $this->publicAssetBaseUrl = $uploaderAssetsBaseUrl;
    }


    public function uploadArticleImage(File $file, ?string $existingFilename): string {

        //$destination  = $this->uploadsPath.'/'.self::ARTICLE_IMAGE;

        $newFilename = $this->uploadFile($file, self::ARTICLE_IMAGE, true);

        if($existingFilename){
            try{
                $result = $this->filesystem->delete(self::ARTICLE_IMAGE.'/'.$existingFilename);
                if($result === false){
                    throw new \Exception(sprintf('Could not delete old uploaded file "%s" ', $newFilename));
                }

            }
            catch (FileNotFoundException $e){
                $this->logger->alert(sprintf('Old uploadedfile "%s" was missing when trying to delete', $existingFilename));
            }

        }

        return $newFilename;
    }

    public function uploadArticleReference(File $file): string{

        return $this->uploadFile($file, self::ARTICLE_REFERENCE, false);
    }

    /**
     * @return resource
     */
    public function readStream(string $path)
    {
        //$filesystem = $isPublic ? $this->filesystem : $this->privateFilesystem;
        $resource = $this->filesystem->readStream($path);
        if ($resource === false) {
            throw new \Exception(sprintf('Error opening stream for "%s"', $path));
        }
        return $resource;
    }

    public function deleteFile(string $path)
    {
        //$filesystem = $isPublic ? $this->filesystem : $this->privateFilesystem;
        $result = $this->filesystem->delete($path);

        if ($result === false) {
            throw new \Exception(sprintf('Error deleting "%s"', $path));
        }
    }

    public function uploadFile(File $file, string $directory, bool $isPublic): string {
        if($file instanceof UploadedFile){
            $originalFilename = $file->getClientOriginalName();
        } else{
            $originalFilename = $file->getFileName();
        }

        //$filesystem = $isPublic ? $this->filesystem: $this->privateFilesystem;

        $newFileName = Urlizer::urlize(pathinfo($originalFilename, PATHINFO_FILENAME)).'-'.uniqid().'.'.$file->guessExtension();

        $stream = fopen($file->getPathname(),'r');

        $result = $this->filesystem->write(
            $directory.'/'.$newFileName,
            $stream,
            [
                'visibility' => $isPublic ? AdapterInterface::VISIBILITY_PUBLIC : AdapterInterface::VISIBILITY_PRIVATE
            ]
        );

        if($result === false){
            throw new \Exception(sprintf('Could not write uploaded file "%s" ', $newFileName));
        }

        if(is_resource($stream)){
            fclose($stream);
        }

        return $newFileName;
    }

    public function getPublicPath(string $path): string
    {
        //return $this->publicAssetBaseUrl.'/'.$path;

        $fullPath = $this->publicAssetBaseUrl.$path;
        // if it's already absolute, just return
        if (strpos($fullPath, '://') !== false) {
            return $fullPath;
        }

        // needed if you deploy under a subdirectory
        return $this->requestStackContext
                ->getBasePath().$fullPath;
    }
}