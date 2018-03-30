<?php

namespace Neevalt\ArenaAuthBundle\Security\User;

use Neevalt\ArenaAuthBundle\Service\RsaService;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ArenaAuthUserProvider implements UserProviderInterface
{
    /**
     * @var ArenaAuthUserLoaderInterface
     */
    private $userLoader;
    /**
     * @var RsaService
     */
    private $rsaService;
    /**
     * @var bool
     */
    private $refreshUser;

    /**
     * UserProvider constructor.
     *
     * @param ArenaAuthUserLoaderInterface $userLoader
     * @param RsaService                   $rsaService
     * @param bool                         $refreshUser
     */
    public function __construct(ArenaAuthUserLoaderInterface $userLoader, RsaService $rsaService, bool $refreshUser)
    {
        $this->userLoader = $userLoader;
        $this->rsaService = $rsaService;
        $this->refreshUser = $refreshUser;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->initUser(new ArenaAuthUser($username));

        return $this->userLoader->loadUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof ArenaAuthUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        if (!$this->refreshUser) {
            return $user;
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return 'Neevalt\AuthBundle\Security\User\User' === $class;
    }

    /**
     * Init user's fields if possible.
     *
     * @param ArenaAuthUser $user
     *
     * @return ArenaAuthUser
     */
    private function initUser(ArenaAuthUser $user): ArenaAuthUser
    {
        $clientRsa = $this->rsaService->getClientRSA();
        if ($clientRsa->isInit()) {
            $dateNaissance = \DateTime::createFromFormat('d/m/Y', $clientRsa->getDateNaissance());
            if (!$dateNaissance) {
                $dateNaissance = \DateTime::createFromFormat('Y-d-m', $clientRsa->getDateNaissance());
            }
            if (!$dateNaissance) {
                $dateNaissance = new \DateTime('0000-00-00');
            }
            $user->setUsername($clientRsa->getUID())
                ->setNom($clientRsa->getNom())
                ->setPrenom($clientRsa->getPrenom())
                ->setNomComplet($clientRsa->getNomComplet())
                ->setDateNaissance($dateNaissance)
                ->setNumen($clientRsa->getNumen())
                ->setMail($clientRsa->getMail())
                ->setEtablissementAffectation($clientRsa->getEtablissementAffectation())
                ->setFonction($clientRsa->getFonction())
                ->setFonctionAdmin($clientRsa->getFonctionAdmin())
                ->setTypeEnsi($clientRsa->getTypeEnsi())
                ->setListeGroupes($clientRsa->getListeGroupes())
                ->setListeEtablissements($clientRsa->getListeEtablissements())
                ->setListeEtablissementsDelegations($clientRsa->getListeEtablissementsDelegations())
                ->setDiscipline($clientRsa->getDiscipline())
                ->setOrigine($clientRsa->getPortail());
        }

        return $user;
    }
}
