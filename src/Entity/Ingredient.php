<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * @ORM\Entity(repositoryClass="App\Repository\IngredientRepository")
 */
class Ingredient
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"recipe","program"})
     */
    private $level;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Led", inversedBy="ingredients")
     * @Groups({"recipe","program"})
     */
    private $led;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Recipe", inversedBy="ingredients")
     */
    private $recipe;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"recipe","program"})
     * @Assert\Range(
     *      min = 0,
     *      max = 1
     * )
     */
    private $pwm_start;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"recipe","program"})
     * @Assert\Range(
     *      min = 0,
     *      max = 1
     * )
     */
    private $pwm_stop;


    public function __construct()
    {
        $this->led = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getLed(): ?Led
    {
        return $this->led;
    }

    public function setLed(?Led $led): self
    {
        $this->led = $led;

        return $this;
    }

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(?Recipe $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }

    public function getPwmStart(): ?float
    {
        return $this->pwm_start;
    }

    public function setPwmStart(?float $pwm_start): self
    {
        $this->pwm_start = $pwm_start;

        return $this;
    }

    public function getPwmStop(): ?float
    {
        return $this->pwm_stop;
    }

    public function setPwmStop(?float $pwm_stop): self
    {
        $this->pwm_stop = $pwm_stop;

        return $this;
    }

}
