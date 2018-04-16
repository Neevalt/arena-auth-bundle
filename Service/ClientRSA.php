<?php

namespace Neevalt\ArenaAuthBundle\Service;

use Exception;
use SoapClient;
use stdClass;

/**
 * Accès aux paramètres Utilisateur et Etablissements derrière RSA.
 *
 * Gestion de l'accès avec ou non le Composant de Sécurité WS
 * Gestion de l'accès avec ou non les fonctions Apache
 */
class ClientRSA
{
    private $sAppNom;
    private $aHeaderHTTP;
    private $aHeaderClientRSA;
    private $oComposantSecuriteWS;
    private $sTypeExtractionRNE;
    private $isInit = false;

    /**
     * Initialise ClientRSA.
     *
     * @param string $appNom : Nom de l'application
     */
    public function __construct($appNom)
    {
        // Initialisation des Variables
        $this->aHeaderHTTP = [];
        $this->aHeaderClientRSA = [];
        $this->sAppNom = $appNom;
    }

    /**
     * @return bool
     */
    public function isInit()
    {
        return $this->isInit;
    }

    /**
     * Initialise le Composant de Sécurité.
     *
     * @param string $sUrlComposantSecurite : url du Composant de Sécurité.
     *                                      Cette URL vous est fournie par la cellule identité après installation du composant
     *
     * @return bool
     */
    public function setComposantSecurite($sUrlComposantSecurite)
    {
        // Connexion SOAP
        if (class_exists('SoapClient')) {
            try {
                $aOption = ['soap_version' => SOAP_1_1, 'trace' => true];
                $this->oComposantSecuriteWS = new SoapClient($sUrlComposantSecurite, $aOption);
            } catch (Exception $ex) {
                return false;
            }
        }

        return true;
    }

    public function getComposantSecurite()
    {
        return $this->oComposantSecuriteWS;
    }

    public function getComposantSecuriteVersion()
    {
        if (null !== $this->oComposantSecuriteWS) {
            $oVersion = $this->oComposantSecuriteWS->getVersion();

            if ($oVersion instanceof stdClass && isset($oVersion->return)) {
                $sVersion = $oVersion->return;
            } else {
                $sVersion = 'Erreur Version Composant';
            }
        } else {
            $sVersion = 'Composant non initialisé';
        }

        return $sVersion;
    }

    /**
     * Récupère le Header RSA pour construire un tableau qui sera utilisé par ClientRSA.
     *
     * @param bool $bRequestHeadersLocal :
     *                                   - true : force l'utilisation de requestHeadersLocal()
     *                                   - false : utilisation des fonctions apache() si disponibles, sinon requestHeadersLocal()
     *
     * Utilisation ou non du Composant de Sécurité WS pour créer le tableau utilisé par ClientRSA
     */
    public function setHeaders($bRequestHeadersLocal = false)
    {
        // Initialisation
        $this->aHeaderHTTP = [];
        $this->aHeaderClientRSA = [];
        $this->initHeaderClient();

        // Récupération Provenance Utilisateur
        isset($_SERVER['HTTP_REFERER']) ? $this->aHeaderClientRSA['portail'] = $_SERVER['HTTP_REFERER'] : $this->aHeaderClientRSA['portail'] = '';

        // Récupération En-Tête HTTP
        if (function_exists('apache_request_headers') && false === $bRequestHeadersLocal) {
            // Fonction apache_request_headers est disponible
            $this->aHeaderHTTP = apache_request_headers();
        } else {
            // Fonction apache_request_headers n'est pas disponible, on appelle une fonction "maison"
            $this->aHeaderHTTP = $this->requestHeadersLocal();
        }

        // Alimentation du HeaderClient
        if (null !== $this->oComposantSecuriteWS) {
            // Composant Activé => Création du HeaderClient à  partir du Composant
            $this->setHeaderClientFromComposant();
        } else {
            // Composant Non Activé => Création du HeaderClient à  partir du HeaderHTTP
            $this->setHeaderClientFromHTTP();
        }
        $this->isInit = true;
    }

    // Retourne le tableau du Header HTTP
    public function getHeaderHTTP()
    {
        return $this->aHeaderHTTP;
    }

    // Retourne le tableau utilisé par ClientRSA pour donner des informations sur l'utilisateur connecté
    public function getHeaderClientRSA()
    {
        return $this->aHeaderClientRSA;
    }

    // Retourne le Portail de l'utilisateur
    public function getPortail()
    {
        if (isset($this->aHeaderClientRSA['portail']) && !empty($this->aHeaderClientRSA['portail'])) {
            return $this->aHeaderClientRSA['portail'];
        }
        if (isset($this->aHeaderHTTP['X-Forwarded-Host'])) {
            return $this->aHeaderHTTP['X-Forwarded-Host'];
        }

        return '';
    }

    // Retourne le nom : ctln
    public function getNom()
    {
        return isset($this->aHeaderClientRSA['nomFamille']) ? $this->aHeaderClientRSA['nomFamille'] : '';
    }

    // Retourne le prénom : ctfn
    public function getPrenom()
    {
        return isset($this->aHeaderClientRSA['prenom']) ? $this->aHeaderClientRSA['prenom'] : '';
    }

    // Retourne le nom complet : cn
    public function getNomComplet()
    {
        return isset($this->aHeaderClientRSA['nom']) ? $this->aHeaderClientRSA['nom'] : '';
    }

    // Retourne la Date de Naissance : datenaissance
    public function getDateNaissance()
    {
        return isset($this->aHeaderClientRSA['dateNaissance']) ? $this->aHeaderClientRSA['dateNaissance'] : '';
    }

    // Retourne le Numen : employeeNumber
    public function getNumen()
    {
        return isset($this->aHeaderClientRSA['numen']) ? $this->aHeaderClientRSA['numen'] : '';
    }

    // Retourne l'uid : ct-remote-user
    public function getUID()
    {
        return isset($this->aHeaderClientRSA['uid']) ? $this->aHeaderClientRSA['uid'] : '';
    }

    // Retourne le Mail : ctemail
    public function getMail()
    {
        return isset($this->aHeaderClientRSA['mail']) ? $this->aHeaderClientRSA['mail'] : '';
    }

    // Retourne Etablissement d'affectation : rne
    public function getEtablissementAffectation()
    {
        return isset($this->aHeaderClientRSA['etabAffectation']) ? $this->aHeaderClientRSA['etabAffectation'] : '';
    }

    // Retourne la Fonction : title
    public function getFonction()
    {
        return isset($this->aHeaderClientRSA['fonction']) ? $this->aHeaderClientRSA['fonction'] : '';
    }

    // Retourne la Fonction Administrative : FrEduRneResp
    public function getFonctionAdmin()
    {
        return isset($this->aHeaderClientRSA['fonctionAdm']) ? $this->aHeaderClientRSA['fonctionAdm'] : '';
    }

    // Retourne le TypeEnsi : typensi
    public function getTypeEnsi()
    {
        return isset($this->aHeaderClientRSA['typeEnsi']) ? $this->aHeaderClientRSA['typeEnsi'] : '';
    }

    // Retourne les Groupes de l'utilisateur : ctgrps
    public function getListeGroupes()
    {
        isset($this->aHeaderClientRSA['ctgrps']) ? $aGroupes = explode(',', $this->aHeaderClientRSA['ctgrps']) : $aGroupes = [];

        return $aGroupes;
    }

    // Retourne liste des Etablissements de l'utilisateur
    public function getListeEtablissements()
    {
        isset($this->aHeaderClientRSA['listeRne']) ? $aEtablissements = explode(',', $this->aHeaderClientRSA['listeRne']) : $aEtablissements = [];

        return $aEtablissements;
    }

    // Retourne liste des Etablissements en Délégations de l'utilisateur
    public function getListeEtablissementsDelegations()
    {
        isset($this->aHeaderClientRSA['listeRneDeleg']) ? $aEtablissements = explode(',', $this->aHeaderClientRSA['listeRneDeleg']) : $aEtablissements = [];

        return $aEtablissements;
    }

    public function getDiscipline()
    {
        isset($this->aHeaderClientRSA['discim']) ? $disciplines = $this->aHeaderClientRSA['discim'] : $disciplines = '';

        return $disciplines;
    }

    public function isDelegation()
    {
        return !empty(trim($this->aHeaderClientRSA['listeRneDeleg']));
    }

    private function requestHeadersLocal()
    {
        $arh = [];
        $rx_http = '/\AHTTP_/';

        foreach ($_SERVER as $key => $val) {
            if (preg_match($rx_http, $key)) {
                $arh_key = preg_replace($rx_http, '', $key);
                // manipulations de chaines pour restaurer la casse initiale
                $rx_matches = explode('_', $arh_key);

                if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
                    foreach ($rx_matches as $ak_key => $ak_val) {
                        $rx_matches[$ak_key] = ucfirst($ak_val);
                    }
                    $arh_key = implode('-', $rx_matches);
                }

                // Transformation des clés pour les rendre identiques à  celles retournées par apache_request_headers
                $arh_key = strtolower($arh_key);

                switch ($arh_key) {
                    case 'employeenumber': // Numen
                        $arh_key = 'employeeNumber';

                        break;
                    case 'fredufonctadm':
                        $arh_key = 'FrEduFonctAdm';

                        break;
                    case 'fredurneresp':
                        $arh_key = 'FrEduRneResp';

                        break;
                    case 'fredurne':
                        $arh_key = 'FrEduRne';

                        break;
                    case 'freduresdel':
                        $arh_key = 'FrEduResDel';

                        break;
                }

                // Construction du tableau des en-têtes HTTP
                $arh[$arh_key] = $val;
            }
        }

        return $arh;
    }

    private function initHeaderClient()
    {
        // Initialisation du HeaderClient Portail
        $this->aHeaderClientRSA['portail'] = '';

        // Initialisation du HeaderClient Utilisateur
        $this->aHeaderClientRSA['ctgrps'] = '';
        $this->aHeaderClientRSA['dateNaissance'] = '';
        $this->aHeaderClientRSA['etabAffectation'] = '';
        $this->aHeaderClientRSA['fonction'] = '';
        $this->aHeaderClientRSA['fonctionAdm'] = '';
        $this->aHeaderClientRSA['mail'] = '';
        $this->aHeaderClientRSA['nom'] = '';
        $this->aHeaderClientRSA['nomFamille'] = '';
        $this->aHeaderClientRSA['numen'] = '';
        $this->aHeaderClientRSA['prenom'] = '';
        $this->aHeaderClientRSA['typeEnsi'] = '';
        $this->aHeaderClientRSA['uid'] = '';
        $this->aHeaderClientRSA['discim'] = '';

        // Initialisation du HeaderClient Etablissement
        $this->aHeaderClientRSA['listeRne'] = '';
        $this->aHeaderClientRSA['listeRneDeleg'] = '';
    }

    private function setHeaderClientFromHTTP()
    {
        // Initialisation des informations Utilisateur
        $this->setUtilisateurFromHTTP();

        // Définit le type d'extraction en fonction du type d'utilisateur
        if ('DIR' === $this->getFonction() || $this->isDelegation()) {
            $this->sTypeExtractionRNE = 'ETAB_RESP';
        } else {
            $this->sTypeExtractionRNE = 'ETAB_ENS';
        }

        // Initialisation des informations Etablissement
        $this->setEtablissementFromHTTP();
    }

    private function setHeaderClientFromComposant()
    {
        // Initialisation
        $aHeaderComposant = [];

        // Création d'un tableau à  partir des en-têtes HTTP destinés au Composant
        foreach ($this->aHeaderHTTP as $sKey => $sValue) {
            // Alimente le tableau des valeurs
            $aHeaderComposant[] = ['key' => $sKey, 'value' => $sValue];

            // Ajoute certaines clés pour compatibilité avec anciennes versions du composant
            switch ($sKey) {
                case 'FrEduRne':
                    $aHeaderComposant[] = ['key' => 'frEdurne', 'value' => $sValue];

                    break;
            }
        }

        // Initialisation des informations Utilisateur
        $this->setUtilisateurFromComposant($aHeaderComposant);

        // Définit le type d'extraction en fonction du type d'utilisateur
        if ('DIR' === $this->getFonction() || $this->isDelegation()) {
            $this->sTypeExtractionRNE = 'ETAB_RESP';
        } else {
            $this->sTypeExtractionRNE = 'ETAB_ENS';
        }

        // Initialisation des informations Etablissement
        $this->setEtablissementFromComposant($aHeaderComposant);
    }

    private function setUtilisateurFromHTTP()
    {
        // Création du HeaderClient Utilisateur à partir du HeaderHTTP
        isset($this->aHeaderHTTP['ctgrps']) ? $this->aHeaderClientRSA['ctgrps'] = $this->aHeaderHTTP['ctgrps'] : $this->aHeaderClientRSA['ctgrps'] = '';
        isset($this->aHeaderHTTP['datenaissance']) ? $this->aHeaderClientRSA['dateNaissance'] = $this->aHeaderHTTP['datenaissance'] : $this->aHeaderClientRSA['dateNaissance'] = '';
        isset($this->aHeaderHTTP['rne']) ? $this->aHeaderClientRSA['etabAffectation'] = $this->aHeaderHTTP['rne'] : $this->aHeaderClientRSA['etabAffectation'] = '';
        isset($this->aHeaderHTTP['title']) ? $this->aHeaderClientRSA['fonction'] = $this->aHeaderHTTP['title'] : $this->aHeaderClientRSA['fonction'] = '';
        isset($this->aHeaderHTTP['FrEduFonctAdm']) ? $this->aHeaderClientRSA['fonctionAdm'] = $this->aHeaderHTTP['FrEduFonctAdm'] : $this->aHeaderClientRSA['fonctionAdm'] = '';
        isset($this->aHeaderHTTP['ctemail']) ? $this->aHeaderClientRSA['mail'] = $this->aHeaderHTTP['ctemail'] : $this->aHeaderClientRSA['mail'] = '';
        isset($this->aHeaderHTTP['cn']) ? $this->aHeaderClientRSA['nom'] = $this->aHeaderHTTP['cn'] : $this->aHeaderClientRSA['nom'] = '';
        isset($this->aHeaderHTTP['ctln']) ? $this->aHeaderClientRSA['nomFamille'] = $this->aHeaderHTTP['ctln'] : $this->aHeaderClientRSA['nomFamille'] = '';
        isset($this->aHeaderHTTP['employeeNumber']) ? $this->aHeaderClientRSA['numen'] = $this->aHeaderHTTP['employeeNumber'] : $this->aHeaderClientRSA['numen'] = '';
        isset($this->aHeaderHTTP['ctfn']) ? $this->aHeaderClientRSA['prenom'] = $this->aHeaderHTTP['ctfn'] : $this->aHeaderClientRSA['prenom'] = '';
        isset($this->aHeaderHTTP['typensi']) ? $this->aHeaderClientRSA['typeEnsi'] = $this->aHeaderHTTP['typensi'] : $this->aHeaderClientRSA['typeEnsi'] = '';
        isset($this->aHeaderHTTP['ct-remote-user']) ? $this->aHeaderClientRSA['uid'] = $this->aHeaderHTTP['ct-remote-user'] : $this->aHeaderClientRSA['uid'] = '';
        isset($this->aHeaderHTTP['discim']) ? $this->aHeaderClientRSA['discim'] = $this->aHeaderHTTP['discim'] : $this->aHeaderClientRSA['discim'] = '';
    }

    private function setEtablissementFromHTTP()
    {
        // Création du HeaderClient Etablissement à  partir du HeaderHTTP
        $sListeEtabs = '';
        $sListeEtabsDeleg = '';

        // Sélection des Etablissements en Responsabilité ("ETAB_RESP") ou en Enseignement ("ETAB_ENS")
        if ('ETAB_RESP' === $this->sTypeExtractionRNE) {
            // On cherche les Etablissements en Responsabilité
            if (isset($this->aHeaderHTTP['FrEduRneResp'])) {
                // Récupération des Etablissements en Responsabilité (FrEduRneResp)
                $aEduRne = explode(',', $this->aHeaderHTTP['FrEduRneResp']);

                // Parcours des Etablissements
                for ($iIndex = 0; $iIndex < count($aEduRne); ++$iIndex) {
                    $aTab = explode('$', $aEduRne[$iIndex]);
                    $sRne = $aTab[0];

                    if (8 === strlen($sRne)) {
                        // Code RNE conforme
                        if (false === strpos($sListeEtabs, $sRne)) {
                            // Etablissement n'est pas dans la liste => ajout
                            if ('' !== $sListeEtabs) {
                                $sListeEtabs .= ',';
                            }

                            $sListeEtabs .= $sRne;
                        }
                    }
                }
            }

            // On cherche les Etablissements en Délégation
            if (isset($this->aHeaderHTTP['FrEduResDel'])) {
                // Récupération des Etablissements en Délégation (FrEduResDel)
                $aListeAppRne = explode(',', $this->aHeaderHTTP['FrEduResDel']);

                foreach ($aListeAppRne as $sKeyAppRne) {
                    // Parcours des Applications en Délégation
                    $aAppRne = explode('|', $sKeyAppRne);
                    $sNomApp = $aAppRne[0];

                    if ($sNomApp === $this->sAppNom) {
                        // Application en Délégation correspond à l'Application utilisée => Recherche des Etablissements
                        $aListeRne = explode('=', $aAppRne[5]);
                        $aTabRne = explode(';', $aListeRne[1]);

                        // Parcours des Etablissements pours lesquels l'Application est en Délégation
                        foreach ($aTabRne as $sKeyTabRne) {
                            // Récupération du Rne
                            $aRne = explode('$', $sKeyTabRne);
                            $sRne = $aRne[0];

                            if (8 === strlen($sRne)) {
                                // Code RNE conforme
                                if (false === strpos($sListeEtabsDeleg, $sRne)) {
                                    // Etablissement n'est pas dans la liste => ajout
                                    if ('' !== $sListeEtabsDeleg) {
                                        $sListeEtabsDeleg .= ',';
                                    }

                                    $sListeEtabsDeleg .= $sRne;
                                }
                            }
                        }
                    }
                }
            }
        } else {
            // On cherche les Etablissements en Enseignement
            if (isset($this->aHeaderHTTP['FrEduRne'])) {
                // Récupération des Etablissements en Enseignement (FrEduRne)
                $aEduRne = explode(',', $this->aHeaderHTTP['FrEduRne']);

                // Parcours des Etablissements
                for ($iIndex = 0; $iIndex < count($aEduRne); ++$iIndex) {
                    $aTab = explode('$', $aEduRne[$iIndex]);
                    $sRne = $aTab[0];

                    if (8 === strlen($sRne)) {
                        // Code RNE conforme
                        if (false === strpos($sListeEtabs, $sRne)) {
                            // Etablissement n'est pas dans la liste => ajout
                            if ('' !== $sListeEtabs) {
                                $sListeEtabs .= ',';
                            }

                            $sListeEtabs .= $sRne;
                        }
                    }
                }
            }
        }

        $this->aHeaderClientRSA['listeRne'] = $sListeEtabs;
        $this->aHeaderClientRSA['listeRneDeleg'] = $sListeEtabsDeleg;
    }

    private function setUtilisateurFromComposant($aHeaderComposant)
    {
        // Création du HeaderClient Utilisateur à partir du HeaderComposant
        $aParametres = ['httpHeaders' => $aHeaderComposant];
        $oUtilisateur = $this->oComposantSecuriteWS->getUtilisateur($aParametres);

        if ($oUtilisateur instanceof stdClass && isset($oUtilisateur->return)) {
            // Composant Sécurité utilisable => Création du HeaderClient Utilisateur à partir du Composant
            foreach ($oUtilisateur->return as $sKey => $sValue) {
                if ('etabFrEduRne' === $sKey || 'etabFrEduRneResp' === $sKey) {
                    // Informations Etablissements gérées dans getEtablissement()
                    continue;
                }

                $this->aHeaderClientRSA[$sKey] = $sValue;
            }
        }

        // Ajout des données non gérées par le Composant
        isset($this->aHeaderHTTP['discim']) ? $this->aHeaderClientRSA['discim'] = $this->aHeaderHTTP['discim'] : $this->aHeaderClientRSA['discim'] = '';
    }

    private function setEtablissementFromComposant($aHeaderComposant)
    {
        // Création du HeaderClient Etablissement à partir du HeaderComposant
        if ('ETAB_RESP' === $this->sTypeExtractionRNE) {
            $sTypeExtraction = 'ETAB_N_UAJ';
        } else {
            $sTypeExtraction = 'ETAB_ENS';
        }

        $sListeEtabs = '';
        $sListeEtabsDeleg = '';

        $aParametres = [
            'httpHeaders' => $aHeaderComposant,
            'typeExtraction' => $sTypeExtraction,
            'nomApp' => $this->sAppNom,
            'resource' => null,
            'modeFim' => false,
        ];

        // Extraction des Etablissements
        $oEtablissement = $this->oComposantSecuriteWS->getEtablissements($aParametres);

        // Composant Sécurité utilisable => Création du HeaderClient Etablissement à partir du Composant
        if ($oEtablissement instanceof stdClass && isset($oEtablissement->return)) {
            if (is_array($oEtablissement->return)) {
                // Plusieurs Etablissements
                foreach ($oEtablissement->return as $sKey => $pValue) {
                    $sRne = $pValue->codeRne;
                    $sDelegation = $pValue->appliDelegation;

                    if (8 === strlen($sRne)) {
                        // Code RNE conforme
                        if (null === $sDelegation) {
                            // Etablissement en Responsabilité
                            if (false === strpos($sListeEtabs, $sRne)) {
                                // Etablissement n'est pas dans la liste => ajout
                                if ('' !== $sListeEtabs) {
                                    $sListeEtabs .= ',';
                                }

                                $sListeEtabs .= $sRne;
                            }
                        } else {
                            // Etablissement en Délégation
                            if (false === strpos($sListeEtabsDeleg, $sRne)) {
                                // Etablissement n'est pas dans la liste => ajout
                                if ('' !== $sListeEtabsDeleg) {
                                    $sListeEtabsDeleg .= ',';
                                }

                                $sListeEtabsDeleg .= $sRne;
                            }
                        }
                    }
                }
            } else {
                // 1 seul Etablissement
                $sRne = $oEtablissement->return->codeRne;
                $sDelegation = $oEtablissement->return->appliDelegation;

                if (8 === strlen($sRne)) {
                    // Code RNE conforme
                    if (null === $sDelegation) {
                        // Etablissement en Responsabilité
                        $sListeEtabs = $sRne;
                    } else {
                        // Etablissement en Délégation
                        $sListeEtabsDeleg = $sRne;
                    }
                }
            }

            $this->aHeaderClientRSA['listeRne'] = $sListeEtabs;
            $this->aHeaderClientRSA['listeRneDeleg'] = $sListeEtabsDeleg;
        }
    }
}
