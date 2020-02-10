<?php
/**
 * Created by PhpStorm.
 * User: James
 * Date: 12/01/2020
 * Time: 15:34
 */

namespace App\Api;


use Symfony\Component\Validator\Constraints as Assert;

class ArticleReferenceUploadApiModel
{
    /**
     * @Assert\NotBlank()
     */
    public $filename;

    /**
     * @Assert\NotBlank()
     */
    private $data;

    private $decodedData;

    public function setData(?string $data)
    {
        $this->data = $data;
        $this->decodedData = base64_decode($data);
    }

    public function getDecodedData(): ?string
    {
        return $this->decodedData;
    }
}
