README
======

BC Fetch XML

Copyright 2009 Brookins Consulting
License GNU GPL v2 (or later)

----------------------
About
----------------------

This extension fetches remote xml through a template operator.

It provides caching and debug features as additional options.

----------------------
fetchxml Operator Caching
----------------------

This website's custom copy of ghxmlretrieval operator extension caches results per siteaccess and request url in separate cachefiles using default eZ Publish rss cache storage directory and api.

This cache is cleared if all or rss cache is cleared. This cache expires per the site.ini [RSSSettings] Block 'CacheTime' which defaults to '1200' (Cache Time in Seconds).


----------------------
Special Thanks
----------------------

This extension was originally based on the ghxmlretrieval operator extension by Granite Horizon.