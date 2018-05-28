Bundle d'authentification Arena
===============================

Prérequis
---------

* Composer
* Php >= 7.1.3

Ce bundle a été testé avec la version 4 de Symfony.

Installer le bundle
-------------------

Depuis la console, 

```sh
composer require neevalt/arena-auth-bundle
```

Activer et configurer le bundle
-------------------------------

### Activer le bundle

Grâce à Symfony Flex, le bundle est activé automatiquement.  Pour en bénéficier, il faut activer les paramètres de
sécurité dans `config/security.yaml`. Supprimez les paramètres de sécurité par défaut et 
remplacez les par un import :

```yml
imports:
    - { resource: "@ArenaAuthBundle/Resources/config/security.yaml" }
```

### Configurer le bundle

Pour configurer le bundle, créez le fichier `config/packages/arena_auth.yaml`.
La structure de ce fichier doit respecter la suivante :

```yml
arena_auth:
    app_nom: Nom Test
    wsdlurl: ~
    roles: [ROLE_GEST]
    user_loader_id: Neevalt\ArenaAuthBundle\Security\User\ArenaAuthUserLoader
    is_client_rsa: false
    redirect_logout: ~
    user_class: Neevalt\ArenaAuthBundle\Security\User\ArenaAuthUser
    refresh_user: '%kernel.debug%'
```

Ces valeurs sont celles par défaut. Adaptez les avec les paramètres qui correspondent à votre application.

* `app_nom` est le nom de votre application.

* `wsdlurl` correspond à l'URL du module de sécurité.Cette URL vous est fournie par la cellule identité après 
installation du composant. Si mis à `null`, celui-ci n'est pas utilisé.

* `roles` est le tableau de rôles que vous voulez attribuer à votre utilisateur. C'est utile lors de la phase de 
développement, pour par exemple voir les différents affichages d'une page selon le rôle 
(comme ROLE_ADMIN, ROLE_USER, ...).

* `user_loader_id` est l'id du service qui gère l'autorisation. Voir *Gérer l'authentification*.

* `is_client_rsa` va permettre de pouvoir demander au bundle d'utiliser ou non le module de sécurité. Par exemple,
si vous travaillez en local, vous ne passez pas par Arena et n'avez donc pas besoin de ce module, on laissera donc la
variable à `false`. Par défaut, un utilisateur avec le nom défini dans `username` avec les rôles définis dans `roles`
sera simulé. Mettre cette variable à `true` permettra l'accès à une instance de la classe `ClientRSA` au moment de 
l'attribution des rôles, en plus de la stocker en session sous le nom `"clientRSA"`.

* `redirect_logout` est à renseigner si vous ne souhaitez pas rediriger vers le nom de domaine lors de la déconnexion.

* `user_class` est la classe correspondant à l'utilisateur à authentifier. Ce paramètre permet d'étendre la classe de
base pour y ajouter des membres par exemple.

* `refresh_user` détermine si le token d'authentification doit mettre à jour les rôles de l'utilisateur ou non.
Typiquement, si la gestion de vos rôles est lourde (comme pour un appel en base de données), il vaut mieux passer cette
 variable à `false`. En mode dev où on est en revanche souvent amené à changer les rôles, on peut la laisser à `true`.
 La valeur par défaut vaut `true` en mode dev, `false` en mode prod.

Il est à noter que le changement de ces paramètres ne sera pas forcément affiché dans
la toolbar Symfony, mais il sera néanmoins effectif.

Gérer l'authentification
------------------------

Pour modifier le comportement par défaut du bundle et attribuer soi même les différents rôles aux utilisateurs, il faut
 spécifier la logique d'authentification dans une classe qui sera utilisée par le bundle.

Cette classe devra implémenter l'interface `ArenaAuthUserLoaderInterface`. On peut par exemple écrire :

```php
/* src/Security/MyCustomUserLoader.php */

namespace App\Security;

use Neevalt\ArenaAuthBundle\Security\User\ArenaAuthUserLoaderInterface;
use Neevalt\ArenaAuthBundle\Security\User\ArenaAuthUser;
use Psr\Log\LoggerInterface;

class MyCustomUserLoader implements ArenaAuthUserLoaderInterface
{
    private $logger;
    
    // On peut par exempler injecter le Logger ici.
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function loadUser(ArenaAuthUser $user): ArenaAuthUser
    {
        // vaut le paramètre is_client_rsa
        if (ArenaAuthUser::DEFAULT_USERNAME !== $user->getUsername()) {
            if ($user->isGroupeAppartenance('appli_admin')) {
                $user->addRole('ROLE_ADMIN');
            } elseif ($user->isGroupeAppartenance('appli_adsi')) {
                $user->addRole('ROLE_ADSI');
            }
        } else {
            $user->setRoles(['ROLE_VIEWER', 'ROLE_DEV']);
        }
        
        $uid = $user->getUsername();
        $roles = implode(', ', $user->getRoles());
        $this->logger->info("${uid} s'authentifie avec les rôles : [${roles}].");
        
        return $user;
    }
}
```

**Attention ! Les rôles doivent respecter le format `ROLE_*`. Ne pas suivre ce format peut conduire à des erreurs 
(notamment la fonction is_granted() de twig).**

Il faut ensuite prévenir le bundle de l'existence de cette classe, pour qu'elle remplace celle par défaut.

Cela ce fait via la configuration, dans `config/packages/arena_auth.yaml` :

```yml
arena_auth:
    user_loader_id: App\Security\MyCustomUserLoader
```

En utilisant le `services.yaml` de base, le nom de la classe suffit comme paramètre. Si vous avez déclaré le service
manuellement, avec un id spécifique, indiquez celui-ci à la place du nom de la classe.

Ces rôles peuvent être hiérarchisés dans `config/security.yaml` :

```yml
security:
    role_hierarchy:
        ROLE_ADSI: ROLE_VIEWER
        ROLE_ADMIN: [ROLE_ADSI, ROLE_DEV]
```

Si vous avez activé la toolbar, elle devrait vous indiquer l'utilisateur authentifié.

Gérer la déconnexion
--------------------

Le bundle permet d'avoir accès à une route de déconnexion, qui détruit la session et redirige vers Arena.
Pour l'activer, il faut simplement importer le fichier de routing dans `config/routes.yaml` :

```yml
arena_auth_logout:
    resource: "@ArenaAuthBundle/Resources/config/routing.yaml"
```

On peut ensuite se servir d'une route appelée `arena_auth_logout` :

```twig
<a href="{{ path('arena_auth_logout') }}">Déconnexion</a>
```