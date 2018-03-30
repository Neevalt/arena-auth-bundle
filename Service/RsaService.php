<?php

namespace Neevalt\ArenaAuthBundle\Service;

class RsaService
{
    /**
     * @var ClientRSA
     */
    private $clientRSA;
    /**
     * @var bool
     */
    private $isClientRSA;
    /**
     * @var null|string
     */
    private $wsdlUrl;

    /**
     * ClientRSAService constructor.
     *
     * @param ClientRSA   $clientRSA
     * @param bool        $isClientRSA
     * @param null|string $wsdlUrl
     */
    public function __construct(
        ClientRSA $clientRSA,
        bool $isClientRSA,
        ?string $wsdlUrl
    ) {
        $this->clientRSA = $clientRSA;
        $this->isClientRSA = $isClientRSA;
        $this->wsdlUrl = $wsdlUrl;
    }

    /**
     * Creates instance of ClientRSA class that provides helping methods to retrieve
     * a user's data.
     *
     * @return ClientRSA
     */
    public function getClientRSA(): ClientRSA
    {
        if ($this->isClientRSA && !$this->clientRSA->isInit()) {
            if (null !== $this->wsdlUrl) {
                $this->clientRSA->setComposantSecurite($this->wsdlUrl);
            }
            $this->clientRSA->setHeaders();
        }

        return $this->clientRSA;
    }
}
