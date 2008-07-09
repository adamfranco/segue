-- --------------------------------------------------------

--
-- Table structure for table `segue_plugins_rssfeed_cache`
--

CREATE TABLE `segue_plugins_rssfeed_cache` (
  `url` text NOT NULL,
  `cache_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `feed_data` text NOT NULL,
  KEY `url` (`url`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cache of remote feed data.';