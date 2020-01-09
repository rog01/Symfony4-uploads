<?php
/**
 * Created by PhpStorm.
 * User: James
 * Date: 07/01/2020
 * Time: 08:12
 */

namespace App\Service;


use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploaderHelper
{
    const ARTICLE_IMAGE = 'article_image';

    private $uploadsPath;
    private $requestStackContext;

    public function __construct(string $uploadsPath, RequestStackContext $requestStackContext){

        $this->uploadsPath = $uploadsPath;
        $this->requestStackContext = $requestStackContext;
    }


    public function uploadArticleImage(UploadedFile $uploadedFile): string {

        $destination  = $this->uploadsPath.'/'.self::ARTICLE_IMAGE;
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);

        $newFileName = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();
        $uploadedFile->move($destination, $newFileName);

        return $newFileName;
    }

    public function getPublicPath(string $path): string {

        return '/uploads/'.$path;
        //return $this->requestStackContext->getBasePath().'/uploads/'.$path;
        // $this->requestStackContext->getBasePath(). rajoute a nouveau le lien vers le dossier projet
        // Le lien vers l image ne fonctionne plus
    }
}