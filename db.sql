CREATE TABLE IF NOT EXISTS `antidos_logs` (
  `ip` varchar(15) NOT NULL,
  `first` int(11) NOT NULL,
  `last` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  UNIQUE KEY `ip` (`ip`)
) ENGINE=MEMORY DEFAULT CHARSET=latin1;