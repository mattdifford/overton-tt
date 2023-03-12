<?php

// CONFIG
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Scraper.php';

$output_file = __DIR__ ."/output.json";

ini_set('display_errors', 1);
libxml_use_internal_errors(true);
$f = @fopen($output_file, "r+");
if ($f !== false) {
    ftruncate($f, 0);
    fclose($f);
}
// ENDCONFIG


$initialPages = json_decode(file_get_contents(__DIR__ . "/config.json"), true); // Get domains & URLs to scrape


$scrapeUrls = [];
$resultsUrls = [];
$output = [];

// Loop through each TLD from the config file, and collect all of the URLs
foreach ($initialPages['domains'] as $key => $data) {
    // Init Guzzle Client
    $scraper = new Scraper($data);
    $scrapeUrls = $data['urls'];
    scrapeUrls($scrapeUrls, 'findResults', 'printError');
    scrapeUrls($resultsUrls, 'parseResult', 'printError', 50);
}

echo json_encode($output, JSON_PRETTY_PRINT);
file_put_contents($output_file, json_encode($output));


/**
 * @param \GuzzleHttp\Psr7\Response $response
 * @param $index
 * @return void
 */
function findResults(\GuzzleHttp\Psr7\Response $response, $index): void
{
    global $scrapeUrls, $resultsUrls, $scraper;
    printf("URL success: %s\n\r", $scrapeUrls[$index]);
    $doc = new DOMDocument;
    $doc->loadHTML((string)$response->getBody());
    $path = new DOMXPath($doc);
    $query = $path->query(
        "//div[contains(@class, 'finder-results')]//li[contains(@class, 'gem-c-document-list__item')]/a"
    );
    foreach ($query as $item) {
        $resultsUrls[] = normalizeUrl($item->getAttribute('href'), $scraper->base_uri);
    };
}

/**
 * @param $reason
 * @param $index
 * @return void
 */
function printError($reason, $index): void
{
    global $scrapeUrls;
    printf(
        "failed: %s, \n  reason: %s\n",
        $scrapeUrls[$index],
        $reason,
    );
}

/**
 * @param \GuzzleHttp\Psr7\Response $response
 * @param $index
 * @return void
 */
function parseResult(\GuzzleHttp\Psr7\Response $response, $index): void
{
    global $output, $resultsUrls;
    printf("Scraping Result: %s\n\r", $resultsUrls[$index]);
    $doc = new DOMDocument;
    $doc->loadHTML((string)$response->getBody());
    $path = new DOMXPath($doc);
    $title = $path->query("//meta[@property='og:title']");
    $authors = $path->query("//div[contains(@class, 'gem-c-metadata')]//a[contains(@class, 'govuk-link')]");
    $authorsOutput = [];
    foreach ($authors as $author) {
        $authorsOutput[] = $author->textContent;
    }
    $output[] = ["title" => $title[0]->getAttribute('content'), "authors" => $authorsOutput];
    printf("Result scraped: %s\n\r", $resultsUrls[$index]);
}

/**
 * @param $urls
 * @param $successCallback
 * @param $errorCallback
 * @return void
 * This function takes an array of URLs and turns them into a Guzzle Pool,
 * before making the requests, and handling callbacks
 */
function scrapeUrls($urls, $successCallback, $errorCallback, $limit = false): void
{
    $_start = microtime(true);
    global $scraper;
    if ($limit && count($urls) > $limit) {
        printf("Skipping %d requests\n\r", count($urls) - $limit);
        $urls = array_slice($urls, 0, $limit);
    }
    foreach ($urls as $key => $url) {
        sleep($scraper->delay);
        $response = $scraper->client->get($url, ['future' => true]);
        printf("Made GET request to %s\n\r", $url);
        $response->getStatusCode() >= 400 ?
            call_user_func($errorCallback, $response, $key) :
            call_user_func($successCallback, $response, $key);
    }
    printf('finished %d requests in %.2f seconds\n', count($urls), microtime(true) - $_start);
}


/**
 * @param $path
 * @param $domain
 * @return string
 * Will normalize a URL to ensure it is absolute
 */
function normalizeUrl($path, $domain): string
{
    return str_starts_with($path, "/") ? $domain . $path : $path;
}