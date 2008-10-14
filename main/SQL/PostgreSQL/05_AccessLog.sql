-- --------------------------------------------------------

--
-- Table structure for table segue_accesslog
--

CREATE TABLE IF NOT EXISTS segue_accesslog (
  agent_id varchar(70) NOT NULL,
  fk_slotname varchar(50) NOT NULL,
  tstamp timestamp with time zone NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (agent_id,fk_slotname)
);

CREATE INDEX segue_accesslog_fk_slotname_index ON segue_accesslog (fk_slotname);

--
-- Constraints for dumped tables
--

--
-- Constraints for table segue_accesslog
--
ALTER TABLE segue_accesslog
  ADD CONSTRAINT segue_accesslog_ibfk_1 FOREIGN KEY (fk_slotname) REFERENCES segue_slot (shortname) ON DELETE CASCADE ON UPDATE CASCADE;
