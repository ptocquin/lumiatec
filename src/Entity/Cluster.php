<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass="App\Repository\ClusterRepository")
 */
class Cluster
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
    private $label;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Controller", inversedBy="clusters")
     */
    private $controller;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Luminaire", mappedBy="cluster")
     */
    private $luminaires;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Log", mappedBy="cluster")
     */
    private $logs;

    /**
     * @ORM\OneToMany(targetEntity=Run::class, mappedBy="cluster")
     */
    private $runs;

    public function __toString()
    {
        return strval($this->label);
    }

    public function __construct()
    {
        $this->luminaire = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->runs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?int
    {
        return $this->label;
    }

    public function setLabel(int $label): self
    {
        $this->label = $label;

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
     * @return Collection|Luminaire[]
     */
    public function getLuminaires(): Collection
    {
        return $this->luminaires;
    }

    public function addLuminaire(Luminaire $luminaire): self
    {
        if (!$this->luminaire->contains($luminaire)) {
            $this->luminaire[] = $luminaire;
            $luminaire->setCluster($this);
        }

        return $this;
    }

    public function removeLuminaire(Luminaire $luminaire): self
    {
        if ($this->luminaire->contains($luminaire)) {
            $this->luminaire->removeElement($luminaire);
            // set the owning side to null (unless already changed)
            if ($luminaire->getCluster() === $this) {
                $luminaire->setCluster(null);
            }
        }

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
            $log->setCluster($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->contains($log)) {
            $this->logs->removeElement($log);
            // set the owning side to null (unless already changed)
            if ($log->getCluster() === $this) {
                $log->setCluster(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Run[]
     */
    public function getRuns(): Collection
    {
        return $this->runs;
    }

    public function addRun(Run $run): self
    {
        if (!$this->runs->contains($run)) {
            $this->runs[] = $run;
            $run->setCluster($this);
        }

        return $this;
    }

    public function removeRun(Run $run): self
    {
        if ($this->runs->contains($run)) {
            $this->runs->removeElement($run);
            // set the owning side to null (unless already changed)
            if ($run->getCluster() === $this) {
                $run->setCluster(null);
            }
        }

        return $this;
    }
}
