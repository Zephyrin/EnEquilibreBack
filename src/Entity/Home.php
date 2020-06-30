<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;
use Symfony\Component\Validator\Constraints as Asset;
use Swagger\Annotations as SWG;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @SWG\Definition(
 *     description="A Home give a way to group equipments depending on their home like 'MSR' or 'Mammut'"
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\HomeRepository")
 */
class Home
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @ORM\Id
     * @Exclude()
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
}
