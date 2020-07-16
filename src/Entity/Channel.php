<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass="App\Repository\ChannelRepository")
 */
class Channel
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"luminaire"})
     */
    private $channel;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"luminaire"})
     */
    private $iPeek;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Luminaire", inversedBy="channels")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE"))
     */
    private $luminaire;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Led", inversedBy="channels")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"luminaire"})
     */
    private $led;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChannel(): ?int
    {
        return $this->channel;
    }

    public function setChannel(int $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function getIPeek(): ?int
    {
        return $this->iPeek;
    }

    public function setIPeek(int $iPeek): self
    {
        $this->iPeek = $iPeek;

        return $this;
    }

    public function getLuminaire(): ?Luminaire
    {
        return $this->luminaire;
    }

    public function setLuminaire(?Luminaire $luminaire): self
    {
        $this->luminaire = $luminaire;

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
}
