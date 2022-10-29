<?php

namespace ilsanto29\ebayConnector;
use DTS\eBaySDK\OAuth\Services\OAuthService;

use Exception;


class OauthApp extends EbayOauthBase
{
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
            $this->pathToFileToStoreAppToken . $this->ruName,
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

}