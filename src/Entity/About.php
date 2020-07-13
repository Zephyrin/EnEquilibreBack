<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;
use Symfony\Component\Validator\Constraints as Asset;
use Swagger\Annotations as SWG;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @SWG\Definition(
 *     description="A About give a way to present the about page."
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\AboutRepository::class")
 */
class About implements Translatable
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @ORM\Id
     */
    private $id;
    /**
     * @var MediaObject|null
     * 
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaObject")
     */
    private $background;

    /**
     * @var MediaObject|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaObject")
     */
    private $separator;

    /**
     * @var array|null
     */
    private $translations;

    /**
     * @var string|null
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=1024, nullable=true)
     * @SWG\Property(description="The about of the about page.")
     * @Asset\Length(
     *  max=1024,
     *  allowEmptyString = true
     * )
     */
    private $about;

    /**
     * @var string|null
     * @Gedmo\Translatable
     * @ORM\Column(type="string", nullable=true)
     * @SWG\Property(description="The comment of the about page.")
     */
    private $comment;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBackground(): ?MediaObject
    {
        return $this->background;
    }

    public function setBackground(?MediaObject $background): self
    {
        $this->background = $background;

        return $this;
    }

    public function getSeparator(): ?MediaObject
    {
        return $this->separator;
    }

    public function setSeparator(?MediaObject $separator): self
    {
        $this->separator = $separator;

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

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setAbout(?string $about): self
    {
        $this->about = $about;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}
