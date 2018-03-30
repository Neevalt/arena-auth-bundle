<?php

namespace Neevalt\ArenaAuthBundle\Security\User;

use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ArenaAuthUser implements UserInterface, EquatableInterface
{
    /**
     * const used as default username (before actually setting it).
     */
    public const DEFAULT_USERNAME = 'logintest';
    /**
     * @var string
     */
    protected $username;
    /**
     * @var null|string
     */
    protected $password;
    /**
     * @var null|string
     */
    protected $salt;
    /**
     * @var (Role|string)[]
     */
    protected $roles = [];
    /**
     * @var null|string
     */
    protected $nom;
    /**
     * @var null|string
     */
    protected $prenom;
    /**
     * @var null|string
     */
    protected $nomComplet;
    /**
     * @var null|\DateTime
     */
    protected $dateNaissance;
    /**
     * @var null|string
     */
    protected $numen;
    /**
     * @var null|string
     */
    protected $mail;
    /**
     * @var null|string
     */
    protected $etablissementAffectation;
    /**
     * @var null|string
     */
    protected $fonction;
    /**
     * @var null|string
     */
    protected $fonctionAdmin;
    /**
     * @var null|string
     */
    protected $typeEnsi;
    /**
     * @var string[]
     */
    protected $listeGroupes = [];
    /**
     * @var string[]
     */
    protected $listeEtablissements = [];
    /**
     * @var string[]
     */
    protected $listeEtablissementsDelegations = [];
    /**
     * @var null|string
     */
    protected $discipline;
    /**
     * @var null|string
     */
    protected $origine;

    /**
     * User constructor.
     *
     * @param string $username
     */
    public function __construct(string $username)
    {
        $this->username = $username;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return ArenaAuthUser
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Sets the roles granted to the user.
     *
     * @param (Role|string)[] $roles
     *
     * @return ArenaAuthUser
     */
    public function setRoles($roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Adds a user's role.
     *
     * @param Role|string $role
     *
     * @return ArenaAuthUser
     */
    public function addRole($role): self
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * Removes a user's role.
     *
     * @param Role|string $role
     *
     * @return ArenaAuthUser
     */
    public function removeRole($role): self
    {
        if (false !== ($key = array_search($role, $this->roles, true))) {
            unset($this->roles[$key]);
        }

        return $this;
    }

    /**
     * @param string $nomGroupe
     *
     * @return bool
     */
    public function isGroupeAppartenance(string $nomGroupe): bool
    {
        return in_array($nomGroupe, $this->listeGroupes, true);
    }

    /**
     * @return bool
     */
    public function isDelegation(): bool
    {
        return (bool) count($this->listeEtablissementsDelegations);
    }

    /**
     * @param string $discipline
     *
     * @return bool
     */
    public function isDicipline(string $discipline): bool
    {
        return !strpos($this->disciplines, $discipline);
    }

    /**
     * @return null|string
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     *
     * @return ArenaAuthUser
     */
    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    /**
     * @param string $prenom
     *
     * @return ArenaAuthUser
     */
    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getNomComplet(): ?string
    {
        return $this->nomComplet;
    }

    /**
     * @param string $nomComplet
     *
     * @return ArenaAuthUser
     */
    public function setNomComplet($nomComplet): self
    {
        $this->nomComplet = $nomComplet;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateNaissance(): ?\DateTime
    {
        return $this->dateNaissance;
    }

    /**
     * @param \DateTime $dateNaissance
     *
     * @return ArenaAuthUser
     */
    public function setDateNaissance(\DateTime $dateNaissance): self
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getNumen(): ?string
    {
        return $this->numen;
    }

    /**
     * @param string $numen
     *
     * @return ArenaAuthUser
     */
    public function setNumen($numen): self
    {
        $this->numen = $numen;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMail(): ?string
    {
        return $this->mail;
    }

    /**
     * @param string $mail
     *
     * @return ArenaAuthUser
     */
    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * @return string
     */
    public function getEtablissementAffectation(): ?string
    {
        return $this->etablissementAffectation;
    }

    /**
     * @param string $etablissementAffectation
     *
     * @return ArenaAuthUser
     */
    public function setEtablissementAffectation(string $etablissementAffectation): self
    {
        $this->etablissementAffectation = $etablissementAffectation;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    /**
     * @param string $fonction
     *
     * @return ArenaAuthUser
     */
    public function setFonction(string $fonction): self
    {
        $this->fonction = $fonction;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFonctionAdmin(): ?string
    {
        return $this->fonctionAdmin;
    }

    /**
     * @param string $fonctionAdmin
     *
     * @return ArenaAuthUser
     */
    public function setFonctionAdmin(string $fonctionAdmin): self
    {
        $this->fonctionAdmin = $fonctionAdmin;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTypeEnsi(): ?string
    {
        return $this->typeEnsi;
    }

    /**
     * @param string $typeEnsi
     *
     * @return ArenaAuthUser
     */
    public function setTypeEnsi(string $typeEnsi): self
    {
        $this->typeEnsi = $typeEnsi;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getListeGroupes(): array
    {
        return $this->listeGroupes;
    }

    /**
     * @param string[] $listeGroupes
     *
     * @return ArenaAuthUser
     */
    public function setListeGroupes(array $listeGroupes): self
    {
        $this->listeGroupes = $listeGroupes;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getListeEtablissements(): array
    {
        return $this->listeEtablissements;
    }

    /**
     * @param string[] $listeEtablissements
     *
     * @return ArenaAuthUser
     */
    public function setListeEtablissements(array $listeEtablissements): self
    {
        $this->listeEtablissements = $listeEtablissements;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getListeEtablissementsDelegations(): array
    {
        return $this->listeEtablissementsDelegations;
    }

    /**
     * @param string[] $listeEtablissementsDelegations
     *
     * @return ArenaAuthUser
     */
    public function setListeEtablissementsDelegations(array $listeEtablissementsDelegations): self
    {
        $this->listeEtablissementsDelegations = $listeEtablissementsDelegations;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDiscipline(): ?string
    {
        return $this->discipline;
    }

    /**
     * @param string $disciplines
     *
     * @return ArenaAuthUser
     */
    public function setDiscipline(string $disciplines): self
    {
        $this->discipline = $disciplines;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getOrigine(): ?string
    {
        return $this->origine;
    }

    /**
     * @param null|string $origine
     *
     * @return ArenaAuthUser
     */
    public function setOrigine(?string $origine): self
    {
        $this->origine = $origine;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(UserInterface $user)
    {
        if ($user instanceof self) {
            $isEqual = count($this->getRoles()) === count($user->getRoles());
            if ($isEqual) {
                foreach ($this->getRoles() as $role) {
                    if (!in_array($role, $user->getRoles(), true)) {
                        return false;
                    }
                }
            }

            return $isEqual;
        }

        return false;
    }
}
