# ZDB
---
	
# A PHP database handler library based on PDO, the input can be an array or a JSON file, it supports multiple queries, as well. (it works with mariadb and mysql)
---
## Installation
---
Install with composer
1. if you use composer you can install it if you add it to your composer.json 
2. connection parameters
	1.you can define them like global variables in your config.php
	```PHP
		define('HOSTNAME', 'your_hostname');
		define('DBNAME', 'your_databasename');
		define('USERNAME', 'your_username');
		define('PASSWORD', 'your_password');
		define('PATH', 'your/base/path');
	<?php
	?>
	``` 
	2. or you can declare them in the json file (sample json in the src folder)
   
Manual install
1. you can clone the repository to your hdd or server (git clone git@github.com:zashboy/ZDB.git)
2.  you need to care about the autoloading for example:
   ```PHP
   <?php
		spl_autoload_register(function($class){
            foreach(glob('*/your-path-to-src-folder/src/class.' . $class . '.php') as $config){
                require_once ($config);
            }
		});
	?>
   ```
3.	see the 2. in the Install with composer list above
---
## Usage 
---
>Where you need to get some data from the database and you would start to code the PDO query, you just need to instatiate the ZDB class and give it to your array of the variables or a path to a JSON file which contains all the data what you need.

for example with a JSON file:
```PHP
<?php
	$test = new ZDB('>>currently used section<<', PATH . 'filename.json', ['getPages' => ['select-1' => ['where' => ['name' => 'disclaimer']]]]);
?>
```
example with an array
```PHP
$test = new ZDB(NULL, ['delete'=>['tableName' => 'websites', 'where'=>['website_id'=>1]],
        'insert'=>['tableName' => 'websites', 'values'=>['website_name'=>'jsdkjsakfsa','server_name'=>'dasdadasfas','creation_date'=>'1980-11-23']]]);
```	
If you've got your object you can find all of your responses in the data property
```PHP
<?php
$test->data; // all of the data in an array;
$test->exception; //caught exceptions
$test->data->exectime //the execution time in microseconds
?>
```
You can find a detailed documentation on the link below

[ZDB Documentation](https://www.zashboy.com/zdb "ZDB Documentation")

	