<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use JMS\Serializer\Annotation\SerializedName;
use App\Repository\ViewTranslateRepository;
use Symfony\Component\Validator\Constraints as Asset;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity(repositoryClass=ViewTranslateRepository::class)
 * @SWG\Definition(
 *     description="Give an object that represent a translation for the view."
 * )
 * 
 */
class ViewTranslate implements Translatable
{
    /**
     * The key of the translation.
     * 
     * @ORM\Column(type="string", length=128, nullable=false, unique=true)
     * @ORM\Id
     * @Asset\Length(max=128, allowEmptyString = false)
     * @var string
     *
     */
    protected $key;

    /**
     * @SWG\Property(description="The translate for the key in english or french.")
     *
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=64535, nullable=true)
     * 
     * @var string|null
     */
    private $translate;

    /**
     * @var array|null
     */
    private $translations;


    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    public function getTranslate(): ?string
    {
        return $this->translate;
    }

    public function setTranslate(?string $translate): self
    {
        $this->translate = $translate;
        return $this;
    }

    public function getTranslations(): ?array
    {
        return $this->translations;
    }

    public function setTranslations(?array $translations): self
    {
        $this->translations = $translations;
        return $this;
    }
}
