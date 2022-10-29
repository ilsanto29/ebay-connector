<?php
namespace ilsanto29\ebayConnector;
use Exception;

class OauthMerchant extends EbayOauthBase
{
    public function getUserRedirectUrl($scope = []):string {
        if (!$scope) {
            $scope = $this->scopeForAuthorizationCodeGrantType;
        }
        $service = $this->getOAuthService($scope);
        $url = $service->redirectUrlForUser(["state" => "", "scope"=>"authorization_code"]);
        return $url;
    }

    public function getUserToken($code, $scope = null) {
        if (!$scope) {
            $scope = $this->scopeForAuthorizationCodeGrantType;
        }

        $service = $this->getOAuthService($scope);
        $userTokenRequest = new \DTS\eBaySDK\OAuth\Types\GetUserTokenRestRequest();
        $userTokenRequest->code = $code;
        $response = $service->getUserToken($userTokenRequest);
        if ($response->getStatusCode() !== 200) {
            printf(
                "%s: %s\n\n",
                $response->error,
                $response->error_description
            );
        } else {
            printf(
                "USER ACCESS TOKEN:%s\nTOKEN TYPE:%s\nEXPIRES IN:%s\nREFRESHTOKEN:%s\n\n",
                $response->access_token,
                $response->token_type,
                $response->expires_in,
                $response->refresh_token
            );
        }
        return $response;
    }
}
