Basic starter class for connecting to the Content Runner API. For more information on accepted request headers, possible response headers, and status codes, refer to the [official documentation](https://api.contentrunner.com/apigility/documentation/ContentRunner-v1).

# Usage

## Setup

Before query the Content Runner API, you will need to add your API credentials. You can find your credentials [here](https://www.contentrunner.com/account/api) (if you haven't already, you will need to click the button to generate the credentials initially).

Once you have your credentials, add your API Username as the `$client_id` and your API Key as the `$client_secret`:

```php
private $client_id     = 'your_api_username';
private $client_secret = 'your_api_key';
```

Once your credentials are set, you're ready to instantiate your API object.

```php
$api = new ContentRunnerApi();
```

## Post a new order

To create a new order, you will need to create an array with the following order data:

* `purpose`: (optional, default: Article) Type of content being ordered (Article, Blog Post, Editing, Other, Press Release, Product Description, Resume, Technical Writing, Translation, White Paper, Website Copy, Guest Post)
* `style_guide`: (optional) Predefined Style Guide to be included with the specific article instructions. You can manage your style guides [here](https://www.contentrunner.com/settings/style)
* `project_notes`: (optional) For your eyes only. Your notes about the order, for personal reference.
* `articles`: Multidimensional array containing details for each article being ordered, including the following data:  
  *`title`: Title of article  
  *`instructions`: Instructions to the Writer on what the project entails  
  *`min_word_count`: Minimum acceptable word count  
  *`max_word_count`: Maximum acceptable word count  
  *`price`: Offering price to write the article (keep in mind that Writers will be paid 15% less after Content Runner fees)  
  *`days_to_complete`: Number of days Writer will have to complete article, once picked up  
  *`niche`: (optional) [Valid niches](https://www.contentrunner.com/niches)  
  *`order_type`: Type of order to place as (Direct, Pool, Open, Contact)  
  *`assign_to`: (optional for Open/Contact orders) Which Writers to make the article available to (Writer username for Direct, Writer Pool name for Pool. Open/Contact defaults to All Writers, can be restricted to "3+ Star Writers", "4+ Star Writers", "Most Active Writers")

*Note: If you have insufficient funds, the order will fail to be placed, and you will need to log into your [dashboard](https://www.contentrunner.com/billing/load) to reload. Funds cannot be loaded by API.*

```php
$order_details = array(
    "purpose" => "Product Description",
    "style_guide" => "Web Product Descriptions",
    "project_notes" => "Last batch of product descriptions for abc.com",
    "articles" => array(
        array(
            "title" => "Product X Description",
            "instructions" => "Write a description for Product X using the details at abc.com/x",
            "min_word_count" => 200,
            "max_word_count" => 300,
            "price" => 12.50,
            "days_to_complete" => 3,
            "niche" => "Style",
            "order_type" => "Direct",
            "assign_to" => "duchess"
        ),
        array(
            "title" => "Product Y Description",
            "instructions" => "Write a description for Product Y using the details at abc.com/y",
            "min_word_count" => 200,
            "max_word_count" => 300,
            "price" => 12.50,
            "days_to_complete" => 3,
            "niche" => "Style",
            "order_type" => "Pool",
            "assign_to" => "Site-wide Favorites"
        ),
        array(
            "title" => "Product Z Description",
            "instructions" => "Write a description for Product Z using the details at abc.com/z",
            "word_count_min" => 200,
            "word_count_max" => 300,
            "price" => 12.50,
            "days_to_complete" => 3,
            "niche" => "Style",
            "order_type" => "Contact",
            "assign_to" => "4+ Star Writers"
        )
    )
);

try {
    $api->post_order($order_data);
} catch(Exception $e) {
    die($e->getMessage());
}
```

## Retrieve article details

You can retrieve the details of your articles either individually or in bulk.

### Bulk article retrieval

When querying for articles in bulk, you can either retrieve all articles associated with your account or filter them by status and/or order number by passing an optional `$filters` argument.

```php
$filters = array(
    'status'   => 'Writing In-process',
    'order_no' => 2952
);

try {
    $articles = $api->get_article_details($filters);
} catch(Exception $e) {
    die($e->getMessage());
}
```

If the article retrieval was successful, an array will be returned in the following format:

```php
$results = array(
    "_links" => array(
        "self"  => "https://api.contentrunner.com/articles?status=Writing%20In-process&page=1",
        "first" => "https://api.contentrunner.com/articles?status=Writing%20In-process",
        "last"  => "https://api.contentrunner.com/articles?status=Writing%20In-process&page=2",
        "next"  => "https://api.contentrunner.com/articles?status=Writing%20In-process&page=2"
    ),
    "_embedded" => array(
        "article" => array(
            array(
                "id" => 15862,
                "title" => "Product X Description",
                "instructions" => "Write a description for Product X using the details at abc.com/x",
                "min_word_count" => 200,
                "max_word_count" => 300,
                "price" => 12.50,
                "days_to_complete" => 3,
                "current_deadline" => "2015-01-15",
                "submitted_at" => null,
                "accepted_at" => null,
                "created_at" => "2015-01-10 12:52:11",
                "order_no" => 2952,
                "niche" => "Style",
                "status" => "Writing In-process",
                "order_type" => "Direct"
                "pool" => null,
                "writer" => "duchess",
                "_links" => array(
                    "self" => array(
                        "href" => "https://api.contentrunner.com/articles/15862"
                    )
                )
            ),
            array(
                "id" => 15863,
                "title" => "Product Y Description",
                "instructions" => "Write a description for Product Y using the details at abc.com/y",
                "min_word_count" => 200,
                "max_word_count" => 300,
                "price" => 12.50,
                "days_to_complete" => 3,
                "current_deadline" => "2015-01-15",
                "submitted_at" => null,
                "accepted_at" => null,
                "created_at" => "2015-01-10 12:52:11",
                "order_no" => 2952,
                "niche" => "Style",
                "status" => "Writing In-process",
                "order_type" => "Pool"
                "pool" => "Site-wide Favorites",
                "writer" => "ppoovey",
                "_links" => array(
                    "self" => array(
                        "href" => "https://api.contentrunner.com/articles/15863"
                    )
                )
            ),
        )
    )
);
```
### Individual article retrieval

When querying individual article details, you will pass the article ID as the filter and pass `true` as the second argument to indicate that it is a single article lookup.

```php
try {
    $article = $api->get_article_details(1433, true);
} catch(Exception $e) {
    die($e->getMessage());
}
```

If the article retrieval was successful, an array will be returned in the following format:

```php
$results = array(
    "id" => 15864,
    "order_no" => 2952,
    "title" => "Product Z Description",
    "instructions" => "Write a description for Product Z using the details at abc.com/z",
    "niche" => "Style",
    "word_count_min" => 200,
    "word_count_max" => 300,
    "price" => 12.50,
    "days_to_complete" => 3,
    "order_type" => "Contact (4+ Star Writers)",
    "writer" => "cfiggis",
    "pool" => null,
    "status" => "Complete",
    "current_deadline" => "2015-01-13",
    "submitted_at" => "2015-01-12 07:52:44",
    "accepted_at" => "2015-01-12 11:02:35",
    "created_at" => "2015-01-10 12:52:11",
    "_links" => array(
        "self" => array(
            "href" => "https://api.contentrunner.com/articles/15864"
        )
    )
);
```