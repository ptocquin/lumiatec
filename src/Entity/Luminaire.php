<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass="App\Repository\LuminaireRepository")
 */
class Luminaire
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
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"luminaire"})
     */
    private $serial;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"luminaire"})
     */
    private $ligne;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"luminaire"})
     */
    private $colonne;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Controller", inversedBy="luminaires")
     * @ORM\JoinColumn(name="controller_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $controller;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Pcb", mappedBy="luminaire", orphanRemoval=true)
     */
    private $pcbs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Channel", mappedBy="luminaire", orphanRemoval=true)
     */
    private $channels;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="luminaires")
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Cluster", inversedBy="luminaire")
     * @Groups({"luminaire"})
     */
    private $cluster;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Log", mappedBy="luminaire")
     */
    private $logs;


    public function __construct()
    {
        $this->pcbs = new ArrayCollection();
        $this->channels = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->logs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?int
    {
        return $this->address;
    }

    public function setAddress(int $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getSerial(): ?string
    {
        return $this->serial;
    }

    public function setSerial(?string $serial): self
    {
        $this->serial = $serial;

        return $this;
    }

    public function getLigne(): ?int
    {
        return $this->ligne;
    }

    public function setLigne(?int $ligne): self
    {
        $this->ligne = $ligne;

        return $this;
    }

    public function getColonne(): ?int
    {
        return $this->colonne;
    }

    public function setColonne(?int $colonne): self
    {
        $this->colonne = $colonne;

        return $this;
    }

    public function getController(): ?Controller
    {
        return $this->controller;
    }

    public function setController(?Controller $controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return Collection|Pcb[]
     */
    public function getPcbs(): Collection
    {
        return $this->pcbs;
    }

    public function addPcb(Pcb $pcb): self
    {
        if (!$this->pcbs->contains($pcb)) {
            $this->pcbs[] = $pcb;
            $pcb->setLuminaire($this);
        }

        return $this;
    }

    public function removePcb(Pcb $pcb): self
    {
        if ($this->pcbs->contains($pcb)) {
            $this->pcbs->removeElement($pcb);
            // set the owning side to null (unless already changed)
            if ($pcb->getLuminaire() === $this) {
                $pcb->setLuminaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Channel[]
     */
    public function getChannels(): Collection
    {
        return $this->channels;
    }

    public function addChannel(Channel $channel): self
    {
        if (!$this->channels->contains($channel)) {
            $this->channels[] = $channel;
            $channel->setLuminaire($this);
        }

        return $this;
    }

    public function removeChannel(Channel $channel): self
    {
        if ($this->channels->contains($channel)) {
            $this->channels->removeElement($channel);
            // set the owning side to null (unless already changed)
            if ($channel->getLuminaire() === $this) {
                $channel->setLuminaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
    }

    public function getCluster(): ?Cluster
    {
        return $this->cluster;
    }

    public function setCluster(?Cluster $cluster): self
    {
        $this->cluster = $cluster;

        return $this;
    }

    /**
     * @return Collection|Log[]
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(Log $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs[] = $log;
            $log->setLuminaire($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->contains($log)) {
            $this->logs->removeElement($log);
            // set the owning side to null (unless already changed)
            if ($log->getLuminaire() === $this) {
                $log->setLuminaire(null);
            }
        }

        return $this;
    }

}
