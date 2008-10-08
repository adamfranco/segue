-- --------------------------------------------------------

--
-- Table structure for table `segue_plugins_rssfeed_cache`
--

CREATE TABLE segue_plugins_rssfeed_cache (
  url text NOT NULL,
  cache_time timestamp with time zone NOT NULL default CURRENT_TIMESTAMP
  feed_data text NOT NULL,
);

CREATE INDEX segue_plugins_rssfeed_cache_url_index ON segue_plugins_rssfeed_cache (url);