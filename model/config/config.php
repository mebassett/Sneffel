<?php
        define('db_host', 		'');					//MySQL database server
        define('db_name', 		'');			//MySQL database name
        define('db_user', 		'');						//MySQL user
        define('db_pass', 		'');						//Password
        define('tPref'	, 		'');							//table prefix

        define('fileUpload',		'./brandUploads');
        define('fileUploadURLPath',		'./brandUploads');

        define('fileUpload2',		'./uploads');
        define('fileUploadURLPath2',		'./uploads');               
        
        
        define('priceBrandBoard',50);
        define('priceExport',10);
        define('priceClear',5);
        define('priceClaim',5);
        define('priceReplay',25);
        
        define('db_ConnectionString', "mysql:host=".db_host.";dbname=".db_name.";unix_socket=/var/lib/mysql/mysql.sock");


?>
