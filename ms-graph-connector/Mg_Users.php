<?php

require_once __DIR__."/vendor/autoload.php";

use Microsoft\Graph\GraphServiceClient;
use Microsoft\Kiota\Abstractions\ApiException;
use Microsoft\Kiota\Authentication\Oauth\ClientCredentialContext;
use Microsoft\Graph\Generated\Users\UsersRequestBuilderGetRequestConfiguration;
use Microsoft\Graph\Generated\Users\Item\UserItemRequestBuilderGetRequestConfiguration;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class Mg_Users
{
    private $graphServiceClient;
    private $resultPerPage = 900; // Number of users per page, max value can be 999

    public function __construct($tenantDetail = [])
    {
        $tenant_id = $tenantDetail['tenant_id'];
        $app_id = $tenantDetail['app_id'];
        $client_secret = $tenantDetail['client_secret'];
        $tokenRequestContext = new ClientCredentialContext($tenant_id, $app_id, $client_secret);
        $this->graphServiceClient = new GraphServiceClient($tokenRequestContext, []);
    }
    
    // fetch all users list from Entra server
    public function getUsers($filters = [])
    {   
        try {
            $requestConfiguration = new UsersRequestBuilderGetRequestConfiguration();
            $queryParameters = UsersRequestBuilderGetRequestConfiguration::createQueryParameters();
            if (isset($filters['select']) && !empty($filters['select'])) {
                $queryParameters->select = $filters['select'];
            }
            $queryParameters->top = $this->resultPerPage;
            $requestConfiguration->queryParameters = $queryParameters;

            $allUsers = [];
            $nextLink = null;
            do {
                if (!empty($nextLink)) {
                    // Use the nextLink as the request URL for the next page
                    $result = $this->graphServiceClient->users()->withUrl($nextLink)->get()->wait();
                } else {
                    // Initial request to /users
                    $result = $this->graphServiceClient->users()->get($requestConfiguration)->wait();
                }
                $allUsers = array_merge($allUsers, $result->getValue());
                $nextLink = $result->getOdataNextLink();
            } while (!empty($nextLink));

            unset($result);

            return $allUsers;
        } catch (IdentityProviderException $e) {
            $errorResp = $e->getResponseBody();
            $error['code'] = $errorResp['error'];
            $error['message'] = $errorResp['error_description'];
            return $error;
        } catch (ApiException $e) {
            $error['code'] = $e->getError()->getCode();
            $error['message'] = $e->getError()->getMessage();
            return $error;
            //throw $e; // Re-throw API exception for proper handling
        }
    }

    // Fetch user details by user id
    public function getUserByUserId($id, $filters = [])
    {        
        try {
            $requestConfiguration = new UserItemRequestBuilderGetRequestConfiguration();
            $queryParameters = UserItemRequestBuilderGetRequestConfiguration::createQueryParameters();
            if (isset($filters['select']) && !empty($filters['select'])) {
                $queryParameters->select = $filters['select'];
            }
            $requestConfiguration->queryParameters = $queryParameters;

            $result = $this->graphServiceClient->users()->byUserId($id)->get($requestConfiguration)->wait();
            return $result;
        } catch (IdentityProviderException $e) {
            $errorResp = $e->getResponseBody();
            $error['code'] = $errorResp['error'];
            $error['message'] = $errorResp['error_description'];
            return $error;
        } catch (ApiException $e) {
            $error['code'] = $e->getError()->getCode();
            $error['message'] = $e->getError()->getMessage();
            return $error;
            //throw $e; // Re-throw API exception for proper handling
        }
    }
}
