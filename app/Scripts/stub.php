#!/usr/bin/php
<?php
// este script es un ejemplo
usleep (5000000 + mt_rand (0, 2000000));
while ($line = trim (fgets (STDIN))) {
	if (!$line) break;
	echo $line."\n";
	/*if (mt_rand (0, 5) == 0) {
		echo "This is an error\n";
		sleep (1);
	}*/
	//usleep (200000);
	usleep (mt_rand (0, 200000));
}
