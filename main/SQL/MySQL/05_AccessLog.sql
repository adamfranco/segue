-- --------------------------------------------------------

--
-- Table structure for table `segue_accesslog`
--

CREATE TABLE IF NOT EXISTS `segue_accesslog` (
  `agent_id` varchar(70) NOT NULL,
  `fk_slotname` varchar(50) character set utf8 collate utf8_bin NOT NULL,
  `tstamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`agent_id`,`fk_slotname`),
  KEY `fk_slotname` (`fk_slotname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `segue_accesslog`
--
ALTER TABLE `segue_accesslog`
  ADD CONSTRAINT `segue_accesslog_ibfk_1` FOREIGN KEY (`fk_slotname`) REFERENCES `segue_slot` (`shortname`) ON DELETE CASCADE ON UPDATE CASCADE;
