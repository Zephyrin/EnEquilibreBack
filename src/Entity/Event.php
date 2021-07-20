<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use JMS\Serializer\Annotation\Exclude;
use Symfony\Component\Validator\Constraints as Asset;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     description="Event description for events page :)"
 * )
 * @ORM\Entity(repositoryClass=EventRepository::class)
 */
class Event
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=2048, nullable=true)
     * @Asset\Length(
     *  max=2048,
     *  allowEmptyString = true
     * )
     * @Gedmo\Translatable
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=2048, nullable=true)
     * @Gedmo\Translatable
     * @Asset\Length(
     *  max=2048,
     *  allowEmptyString = true
     * )
     */
    private $subTitle;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Translatable
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=MediaObject::class)
     */
    private $image;

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

    /**
     * @var array|null
     */
    private $translations;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSubTitle(): ?string
    {
        return $this->subTitle;
    }

    public function setSubTitle(?string $subTitle): self
    {
        $this->subTitle = $subTitle;

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

    public function getImage(): ?MediaObject
    {
        return $this->image;
    }

    public function setImage(?MediaObject $image): self
    {
        $this->image = $image;

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

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
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
