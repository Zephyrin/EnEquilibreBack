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
 *     description="A Gallery save jewel with separators and name."
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\GalleryRepository::class")
 */
class Gallery implements Translatable
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
    private $main;

    /**
     * @var MediaObject|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaObject")
     */
    private $separator;

    /**
     * @var MediaObject|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaObject")
     */
    private $showCase;

    /**
     * @var Collection|MediaObject[]
     * @ORM\ManyToMany(targetEntity="App\Entity\MediaObject")
     * @ORM\JoinTable(name="gallery_medias",
     *      joinColumns={@ORM\JoinColumn(name="gallery_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="media_id", referencedColumnName="id")}
     *      )
     */
    private $medias;

    /**
     * @var array|null
     */
    private $translations;

    /**
     * @var string|null
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=1024, nullable=true)
     * @SWG\Property(description="The title of the main page.")
     * @Asset\Length(
     *  max=1024,
     *  allowEmptyString = true
     * )
     */
    private $title;

    /**
     * @var string|null
     * @Gedmo\Translatable
     * @ORM\Column(type="string", nullable=true)
     * @SWG\Property(description="The description of the gallery.")
     */
    private $description;

    /**
     * @var int
     * @ORM\Column(type="integer", name="order_")
     */
    private $order;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

    public function __construct()
    {
        $this->order = 3527;
        $this->medias = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMain(): ?MediaObject
    {
        return $this->main;
    }

    public function setMain(?MediaObject $main): self
    {
        $this->main = $main;

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

    public function getShowCase(): ?MediaObject
    {
        return $this->showCase;
    }

    public function setShowCase(?MediaObject $showcase): self
    {
        $this->showCase = $showcase;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
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

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function setMedias(array $medias): self
    {
        $this->medias = $medias;
        return $this;
    }
    public function addMedia(MediaObject $media): self
    {
        $this->medias[] = $media;
        return $this;
    }

    public function removeMedia(MediaObject $media): self
    {
        if ($this->medias->contains($media)) {
            $this->medias->removeElement($media);
        }
        return $this;
    }
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}
