<?php

namespace App\Service\Batch;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Request;

/**
 * @author Eunjung Kim
 * @file ShopifyApiServiceImpl.php
 * @package App\Service\Batch
 * @brief Shopify Batch Service
 */
class ShopifyApiServiceImpl implements ShopifyApiService
{
    public function shopifyFullUrl($storeURL)
    {
        if (strpos($storeURL, $this->url_suffix) !== false) {
            return $storeURL;
        }
        return $this->url_prefix . $storeURL . $this->url_suffix;
    }

    public function getProductsQL($token, $sellerId, $cursor = null, $startUpdateDate = '')
    {
        try {
            //shopify url
            $apiOrderUrl = '/admin/api/graphql.json';
            $apiOrderUrl = $this->shopifyFullUrl($sellerId) . $apiOrderUrl;

            dump($apiOrderUrl);
            //requested data
            $reqQL = "
                query(\$cursor: String) {
                    products(first: 70, after: \$cursor) {
                        pageInfo {
                            hasNextPage
                            hasPreviousPage
                            }
                        edges {
                            cursor
                            node {
                                id
                                title
                                totalVariants
                                productType
                                tags
                                descriptionHtml
                                updatedAt
                                variants(first:3) {
                                    edges {
                                        node {
                                            id
                                            legacyResourceId 
                                            title
                                            displayName
                                            barcode
                                            sku
                                            price
                                            weight
                                            weightUnit
                                            availableForSale
                                            createdAt
                                            updatedAt
                                            fulfillmentService {
                                                type
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            ";
            $reqQuery = '';

            if (!empty($startUpdateDate)) {
                if (strlen($reqQuery) == 0) {
                    $reqQuery .= 'created_at:>=\'' . $startUpdateDate . '\'';
                } else {
                    $reqQuery .= ' AND created_at:>=\'' . $startUpdateDate . '\'';
                }
            }

            $reqQLValue = [
                'cursor' => $cursor,
                'reqQuery' => $reqQuery
            ];

            $http = new GuzzleClient;
            $response = $http->post($apiOrderUrl, [
                'headers' => [
                    'X-Shopify-Access-Token' => $token,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'query' => $reqQL,
                    'variables' => $reqQLValue
                ]
            ]);

            $res = json_decode($response->getBody()->getContents(), true);

            dump($res);
            if (isset($res['data'])) {
                return $res['data'];
            } else {
                if (isset($res['errors']) && $res['errors'][0]['message'] == 'Throttled') {
                    return 'R';
                } else {
                    return 'N';
                }
            }
        } catch (\Exception $exception) {
            dump($exception);
            return null;
        }
    }
}