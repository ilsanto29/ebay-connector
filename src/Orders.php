<?php

namespace ilsanto29\ebayConnector;

use DTS\eBaySDK\Fulfillment\Types\GetAnOrderRestRequest;
use Exception;

use DTS\eBaySDK\Fulfillment\Services\FulfillmentService;
use DTS\eBaySDK\Fulfillment\Types\GetOrdersRestRequest;
use DTS\eBaySDK\Account\Services;
use DTS\eBaySDK\Account\Types;
use DTS\eBaySDK\Account\Enums;

class Orders
{
    private $config = null;
    public function __construct($config = [])
    {
        if(!empty( $config ) ) {
            $this->setConfig($config);
        }
    }

    /**
     * @param array $config
     * @throws Exception
     */
    private function setConfig($config = []):void {
        $this->config = $config;
    }

    public function getOrders($authorization, $dt = "2022-10-01T00:00:00.000Z"):array {
        $this->config["authorization"] = trim($authorization);
        $this->config["marketplaceId"] = Enums\MarketplaceIdEnum::C_EBAY_IT;
        $getOrders = new FulfillmentService($this->config);
        $ordersRequest = new GetOrdersRestRequest();
        $ordersRequest->filter = "creationdate:[2022-10-01T00:00:00.000Z]";
        $ordersRequest->limit = "10";
        $ordersRequest->offset = "0";
        $getAnOrderRestRequest = new GetAnOrderRestRequest();
        $getAnOrderRestRequest->orderId = "1";
        $order = $getOrders->getAnOrder($getAnOrderRestRequest); //getOrders($ordersRequest);
        print_r( $order ); die();
        return [];
    }

    public function getPolicy($authorization){
        /**
         * Create the service object.
         */
        $service = new Services\AccountService([
            'authorization' => $authorization,
            'sandbox' => true
        ]);

        /**
         * Create the request object.
         */
        $request = new GetFulfillmentPoliciesByMarketplaceRestRequest();

        /**
         * Note how URI parameters are just properties on the request object.
         */
        $request->marketplace_id = Enums\MarketplaceIdEnum::C_EBAY_IT;

        /**
         * Send the request.
         */
        $response = $service->getFulfillmentPoliciesByMarketPlace($request);

        /**
         * Output the result of calling the service operation.
         */
        echo "====================\nFulfillment Policies\n====================\n";
        printf("\nStatus Code: %s\n\n", $response->getStatusCode());
        if (isset($response->errors)) {
            foreach ($response->errors as $error) {
                printf(
                    "%s: %s\n%s\n\n",
                    $error->errorId,
                    $error->message,
                    $error->longMessage
                );
            }
        }

        if ($response->getStatusCode() === 200) {
            foreach ($response->fulfillmentPolicies as $policy) {
                printf(
                    "(%s) %s: %s\n",
                    $policy->fulfillmentPolicyId,
                    $policy->name,
                    $policy->description
                );
            }
        }
    }
}