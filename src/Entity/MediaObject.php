<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use JMS\Serializer\Annotation\SerializedName;
use App\Repository\MediaObjectRepository;
use Symfony\Component\Validator\Constraints as Asset;

/**
 * @ORM\Entity(repositoryClass=MediaObjectRepository::class)
 * @SWG\Definition(
 *     description="Give an object that represent an image."
 * )
 * 
 */
class MediaObject
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @ORM\Id
     */
    protected $id;

    /**
     * @var string
     * 
     * @SerializedName("filePath")
     * @ORM\Column(type="string")
     */
    private $filePath;

    /**
     * @var string|null
     * 
     * @ORM\Column(type="string", length=1024, nullable=true)
     * @SWG\Property(description="The description of image use for alt description.")
     * @Asset\Length(
     *  max=1024,
     *  allowEmptyString = true
     * )
     */
    private $description;

    public function __construct() {
        $this->filePath = "";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    } 

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }
}