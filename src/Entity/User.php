<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Controller", mappedBy="users")
     */
    private $controllers;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Luminaire", mappedBy="users")
     */
    private $luminaires;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Recipe", mappedBy="user")
     */
    private $recipes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Program", mappedBy="user")
     */
    private $programs;

    /**
     * @ORM\OneToMany(targetEntity=Run::class, mappedBy="user")
     */
    private $runs;


    public function __construct()
    {
        $this->controllers = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->luminaires = new ArrayCollection();
        $this->recipes = new ArrayCollection();
        $this->programs = new ArrayCollection();
        $this->runs = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->email;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Controller[]
     */
    public function getControllers(): Collection
    {
        return $this->controllers;
    }

    public function addController(Controller $controller): self
    {
        if (!$this->controllers->contains($controller)) {
            $this->controllers[] = $controller;
            $controller->addUser($this);
        }

        return $this;
    }

    public function removeController(Controller $controller): self
    {
        if ($this->controllers->contains($controller)) {
            $this->controllers->removeElement($controller);
            $controller->removeUser($this);
        }

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
        if (!$this->luminaires->contains($luminaire)) {
            $this->luminaires[] = $luminaire;
            $luminaire->addUser($this);
        }

        return $this;
    }

    public function removeLuminaire(Luminaire $luminaire): self
    {
        if ($this->luminaires->contains($luminaire)) {
            $this->luminaires->removeElement($luminaire);
            $luminaire->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|Recipe[]
     */
    public function getRecipes(): Collection
    {
        return $this->recipes;
    }

    public function addRecipe(Recipe $recipe): self
    {
        if (!$this->recipes->contains($recipe)) {
            $this->recipes[] = $recipe;
            $recipe->setUser($this);
        }

        return $this;
    }

    public function removeRecipe(Recipe $recipe): self
    {
        if ($this->recipes->contains($recipe)) {
            $this->recipes->removeElement($recipe);
            // set the owning side to null (unless already changed)
            if ($recipe->getUser() === $this) {
                $recipe->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Program[]
     */
    public function getPrograms(): Collection
    {
        return $this->programs;
    }

    public function addProgram(Program $program): self
    {
        if (!$this->programs->contains($program)) {
            $this->programs[] = $program;
            $program->setUser($this);
        }

        return $this;
    }

    public function removeProgram(Program $program): self
    {
        if ($this->programs->contains($program)) {
            $this->programs->removeElement($program);
            // set the owning side to null (unless already changed)
            if ($program->getUser() === $this) {
                $program->setUser(null);
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
            $run->setUser($this);
        }

        return $this;
    }

    public function removeRun(Run $run): self
    {
        if ($this->runs->contains($run)) {
            $this->runs->removeElement($run);
            // set the owning side to null (unless already changed)
            if ($run->getUser() === $this) {
                $run->setUser(null);
            }
        }

        return $this;
    }

}
