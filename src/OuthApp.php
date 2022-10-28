<?php

namespace ilsanto29\ebayConnector;
use DTS\eBaySDK\OAuth\Services\OAuthService;

use Exception;


class OuthApp
{
    /**
     * @var bool
     */
    private $prod = true;

    /**
     * @var array
     */
    private $credentials;

    /**
     * @var string
     */
    private $ruName;

    /**
     * @var string
     */
    private $authorizationCode;

    /**
     * @var bool
     */
    private $debug = false;

    private $pathToFileToStoreAppToken;

    /**
     * @var array
     * @see https://developer.ebay.com/my/keys See "OAuth Scopes" link.
     */
    private $scopeForClientCredentialGrantType = [
        'https://api.ebay.com/oauth/api_scope',
        'https://api.ebay.com/oauth/api_scope/buy.guest.order',
        'https://api.ebay.com/oauth/api_scope/buy.item.feed',
        'https://api.ebay.com/oauth/api_scope/buy.marketing',
        'https://api.ebay.com/oauth/api_scope/buy.product.feed',
        'https://api.ebay.com/oauth/api_scope/buy.marketplace.insights',
        'https://api.ebay.com/oauth/api_scope/buy.proxy.guest.order',
        'https://api.ebay.com/oauth/api_scope/sell.logistics.pudo',
    ];

    /**
     * @var array
     * @see https://developer.ebay.com/my/keys See "OAuth Scopes" link.
     */
    private $scopeForAuthorizationCodeGrantType = [
        'https://api.ebay.com/oauth/api_scope',
        'https://api.ebay.com/oauth/api_scope/buy.order.readonly',
        'https://api.ebay.com/oauth/api_scope/buy.guest.order',
        'https://api.ebay.com/oauth/api_scope/sell.marketing.readonly',
        'https://api.ebay.com/oauth/api_scope/sell.marketing',
        'https://api.ebay.com/oauth/api_scope/sell.inventory.readonly',
        'https://api.ebay.com/oauth/api_scope/sell.inventory',
        'https://api.ebay.com/oauth/api_scope/sell.account.readonly',
        'https://api.ebay.com/oauth/api_scope/sell.account',
        'https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly',
        'https://api.ebay.com/oauth/api_scope/sell.fulfillment',
        'https://api.ebay.com/oauth/api_scope/sell.analytics.readonly',
        'https://api.ebay.com/oauth/api_scope/sell.marketplace.insights.readonly',
        'https://api.ebay.com/oauth/api_scope/commerce.catalog.readonly',
        'https://api.ebay.com/oauth/api_scope/buy.shopping.cart',
        'https://api.ebay.com/oauth/api_scope/buy.offer.auction',
    ];

    public function __construct($config = [])
    {
        if(!empty( $config ) ) {
            $this->setConfig($config);
        }
    }



    public function getApplicationAccessToken($scope = [])
    {
        $response = null;
        $response = $this->getAppTokenFromStorage();

        if( !empty( $response ) ) {
            $this->applicationAccessToken = $response["token"];
            if( !$this->appTokenIsExpired($response) ) return $this->applicationAccessToken;
        }

        if (!$scope) {
            $scope = $this->scopeForClientCredentialGrantType;
        }


        $service = $this->getOAuthService($scope);
        $response = $service->getAppToken();

        if ($response->getStatusCode() !== 200) {
            return [$response->error . ': ' . $response->error_description];
        }

        file_put_contents(
            $this->pathToFileToStoreAppToken,
            json_encode([
                'validUntil' =>
                    time() + (int) $response->expires_in,
                'token' => $response->access_token,
            ])
        );
        return $this->applicationAccessToken = $response->access_token;
    }


    private function getAppTokenFromStorage() {
        return null;
    }
    /**
     * @param array $config
     * @throws Exception
     */
    private function setConfig($config = []):void {
        if (
            empty($config['credentials']['appId']) ||
            empty($config['credentials']['devId']) ||
            empty($config['credentials']['certId'])
        ) {
            throw new Exception(
                'You must specify "credentials", which is an array consisting of "appId", "devId" and "certId" elements'
            );
        }

        $this->setCredentials($config['credentials']);

        if( empty( $config["ruName"] ) ) {
            throw new Exception(
                'You must specify "ruName"'
            );
        }
        $this->setRuName($config["ruName"]);

        if( empty( $config["pathToFileToStoreAppToken"] ) ) {
            throw new Exception(
                'You must specify "pathToFileToStoreAppToken" where the application save you app store token'
            );
        }

        $this->setPathToFileToStoreAppToken($config["pathToFileToStoreAppToken"]);
        /**
         * Set debug mode
         */
        $this->setDebug((!empty($config["debug"])?$config["debug"]:false));

        if (!empty($config['scopeForAuthorizationCodeGrantType'])) {
            $this->scopeForAuthorizationCodeGrantType =
                $config['scopeForAuthorizationCodeGrantType'];
        }

        if (!empty($config['scopeForClientCredentialGrantType'])) {
            $this->scopeForAuthorizationCodeGrantType =
                $config['scopeForClientCredentialGrantType'];
        }

        /**
         * Set if you are in production or sandbox (develop) mode
         */
        $this->prod = !empty($config['prod']) ? true : false;
        $this->sandbox = !$this->prod;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }



        /**
     * @return bool
     */
    public function isProd(): bool
    {
        return $this->prod;
    }

    /**
     * @param bool $prod
     */
    public function setProd(bool $prod): void
    {
        $this->prod = $prod;
    }

    /**
     * @return array
     */
    public function getCredentials(): array
    {
        return $this->credentials;
    }

    /**
     * @param array $credentials
     */
    public function setCredentials(array $credentials): void
    {
        $this->credentials = $credentials;
    }

    /**
     * @return string
     */
    public function getRuName(): string
    {
        return $this->ruName;
    }

    /**
     * @param string $ruName
     */
    public function setRuName(string $ruName): void
    {
        $this->ruName = $ruName;
    }

    /**
     * @return string
     */
    public function getAuthorizationCode(): string
    {
        return $this->authorizationCode;
    }

    /**
     * @param string $authorizationCode
     */
    public function setAuthorizationCode(string $authorizationCode): void
    {
        $this->authorizationCode = $authorizationCode;
    }

    /**
     * @return mixed
     */
    public function getPathToFileToStoreAppToken()
    {
        return $this->pathToFileToStoreAppToken;
    }

    /**
     * @param mixed $pathToFileToStoreAppToken
     */
    public function setPathToFileToStoreAppToken($pathToFileToStoreAppToken): void
    {
        $this->pathToFileToStoreAppToken = $pathToFileToStoreAppToken;
    }

    /**
     * @return array
     */
    public function getScopeForClientCredentialGrantType(): array
    {
        return $this->scopeForClientCredentialGrantType;
    }

    /**
     * @param array $scopeForClientCredentialGrantType
     */
    public function setScopeForClientCredentialGrantType(array $scopeForClientCredentialGrantType): void
    {
        $this->scopeForClientCredentialGrantType = $scopeForClientCredentialGrantType;
    }

    /**
     * @return array
     */
    public function getScopeForAuthorizationCodeGrantType(): array
    {
        return $this->scopeForAuthorizationCodeGrantType;
    }

    /**
     * @param array $scopeForAuthorizationCodeGrantType
     */
    public function setScopeForAuthorizationCodeGrantType(array $scopeForAuthorizationCodeGrantType): void
    {
        $this->scopeForAuthorizationCodeGrantType = $scopeForAuthorizationCodeGrantType;
    }


    /**
     * @param array $scope
     * @return \DTS\eBaySDK\OAuth\Services\OAuthService
     */
    private function getOAuthService($scope)
    {
        return
            new OAuthService([
                'credentials' => $this->credentials,
                'debug' => $this->debug,
                'ruName' => $this->ruName,
                'sandbox' => $this->sandbox,
                'scope' => $scope,
            ]);
    }


}