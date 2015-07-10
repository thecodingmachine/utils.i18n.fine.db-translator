--
-- Table structure for table `message_translations`
--

CREATE TABLE IF NOT EXISTS `message_translations` (
  `msg_key` varchar(150) NOT NULL,
  `language` varchar(10) NOT NULL,
  `message` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Stores translations for the mouf/utils.i18n.fine.db-translator package';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `message_translations`
--
ALTER TABLE `message_translations`
 ADD PRIMARY KEY (`msg_key`,`language`);
