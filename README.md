# Overton Tech Test

This PHP script is to scrape the contents of given URLs on the gov.uk website, and search for specific documents on
those pages

### Features

- I have used a config.json file to allow for easier expansion/control in the future, such as multiple base sites, more
  URLs within those sites, and different timeouts based for each site
- I have set a UserAgent on the requests in an attempt to reduce the likelihood of requests being blocked

### Current limitations/things I'd probably do differently with more time

- No caching. I would probably use the built-in caching plugin within Guzzle, but I've not used this before so would
  take some time to get set up etc. An alternative could be to save the output from each site into a separate file,
  check when this file was last modified, and only re-fetch from this site when the file is older than X minutes/hours
- I would make the XPath queries configurable within the config also, to allow for easily adding more data to scrape,
  and also to accommodate for different document structure on different pages/sites

### Scaling up

For the proposed scaled version of this, I would probably try to take advantage of Pools within Guzzle, to allow us to
asynchronously make the requests. In combination with the proxy servers, we should be able to create something which
spreads a high number of requests fairly lightly over multiple sites and servers

I would also use a database rather than just a config file to configure this, which would then also allow us to flag
when a domain has given us a 429 response, and not include this in the pool of URLs to scrape