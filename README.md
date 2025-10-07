# ShopifyApiServiceImpl

A Laravel service for fetching product data from Shopify stores using GraphQL API.

## What It Does

This service connects to Shopify stores and retrieves product information in batches. It handles pagination and rate limiting automatically.

## Methods

### `shopifyFullUrl($storeURL)`

Builds the complete Shopify store URL.

**Parameters:**
- `$storeURL` - Store name or URL

**Returns:**
- Complete Shopify URL

---

### `getProductsQL($token, $sellerId, $cursor, $startUpdateDate)`

Gets products from a Shopify store.

**Parameters:**
- `$token` - Your Shopify API access token
- `$sellerId` - Store name/URL
- `$cursor` - For pagination (optional)
- `$startUpdateDate` - Filter by creation date (optional)

**Returns:**
- `array` - Product data (success)
- `'R'` - Rate limited (try again later)
- `'N'` - Error occurred
- `null` - Exception thrown

**What You Get:**
- 70 products per request
- Up to 3 variants per product
- Product info: id, title, type, tags, description, update time
- Variant info: id, title, barcode, SKU, price, weight, availability

**API Used**
POST /admin/api/graphql.json

**Dependencies**

Laravel Framework
GuzzleHTTP

**Notes**
Fetches 70 products at a time
Use cursor for next page
Handle rate limits in your code
Debug output uses dump() - replace with logging for production
