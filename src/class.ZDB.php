<?php
/**
     * Created on Sat Aug 04 2018
    *
    * class.ZDB.php
    *
    * The ZDB.php file the base file of the ZDB library, it takes an array or read a JSON file 
    * and prepare the sql statement from the data and handle it and return the requested data 
    * from the database.
    *
    * @category  database wrapper
    * @package   ZDB
    * @author    zashboy
    * @license   https://www.gnu.org/licenses/gpl-3.0.en.html
    * @version   0.0.1
    * @link      https://www.zashboy.com
    * @see       /src
    * @since     File available since Release 0.0.1
    *
    * Copyright (c) 2018 zashboy.com
    */

class ZDB
{
   
    /**
      * Created on Sun Aug 05 2018
      * @desc The identifier of the requested array from the JSON file
      * @var  string
    */

    public $stmtid;

    /**
      * Created on Sun Aug 05 2018
      * @desc The input data which can be an array or a path to a Json file
      * @var  string or array
     */
    public $input;

    /**
      * Created on Sun Aug 05 2018
      * @desc The input data which can be an array or a path to a Json file
      * @var  string or array
     */
    public $var;

    /**
      * Created on Sun Aug 05 2018
      * @desc last selected id
      * @var  int
     */
    public static $lastSelectedId;
    
    /**
      * Created on Sun Aug 05 2018
      * @name   __construct()
      * @desc   Declare properties, decide if the input an array or a file, 
      * declare the input prop and teh config prop based on that, instatiate the PDO based on that
      * call the prepare function based on the type of the input
      * 
      * @param  string $stmtid
      * @param  array or file path $input
      * @var array $ var the variable values
      * @return exception on fail
     */

    public function __construct($input = NULL, $stmtid = NULL, $var = NULL)
    {

        $this->input = $input;
        $this->stmtid = $stmtid;
        $this->var = $var;
        static::$lastSelectedId = NULL;
        $this->exception = NULL;
        $this->data = NULL;
        $this->executiontime = -microtime(true);

        try {
            if(!isset($this->input)){
                throw new Exception('The input is undefined!');
            } elseif(is_array($this->input)){
                $this->inputarray = $this->input;
                $this->config = NULL;
                $this->conn = new PDO("mysql:host=" . HOSTNAME . ";dbname=" . DBNAME, USERNAME, PASSWORD);
                $this->prepInputArray();
            } elseif(is_file($this->input)){
                $this->inputfile = JSONHandler::read($this->input);
                if(!isset($this->inputfile)){
                    throw new Exception("It's not a valid JSON file");
                }
                $this->config = $this->inputfile['config'];
                $this->conn = new PDO("mysql:host=" . $this->config['HOSTNAME'] . ";dbname=" . $this->config['DBNAME'], $this->config['USERNAME'], $this->config['PASSWORD']);
                $this->prepInputFile();
            } else {
                throw new Exception("There has no appropriate input paremeter been added");
            }

        } catch (Throwable $t){
            $this->exception = ['message' => $t->getMessage(), 'file' => $t->getFile(), 'line' => $t->getLine()];
        }

    }

    /**
      * Created on Sun Aug 05 2018
      * @name   prepInputArray()
      * @desc   loop through the input array and instantiate the right classes if it is an array input
      * @param  array
      * @return array of results
     */

    public function prepInputArray()
    {
        $result = [];
        foreach ($this->inputarray as $key => $value) {
            //if we've got - character in 
            $separator = strpos($key, "-");
            //chop off the characters after the "-" character from the end of the keys to get the class name
            $class = $separator ? substr($key, 0, $separator) : $key;
            //get the characters after the "-" sign to make the index
            $index = $separator ? substr($key, $separator+1) : NULL;

            $result[$index] = new $class($value, $this->conn);
        }
        return $this->returnData($result);

    }

    /**
      * Created on Sun Aug 05 2018
      * @name   prepInputArray()
      * @desc   loop through the input array and instantiate the right classes if it is a json file input
      * @param  array
      * @return array of results
     */

    public function prepInputFile()
    {
        if(isset($this->stmtid) && isset($this->var)) {
            $result = [];
            foreach($this->inputfile[$this->stmtid] as $key => $value){
                //if we've got - character in 
                $separator = strpos($key, "-");
                //chop off the characters after the "-" character from the end of the keys to get the class name
                $class = $separator ? substr($key, 0, $separator) : $key;
                //get the characters after the "-" sign to make the index
                $index = $separator ? substr($key, $separator+1) : NULL;

                $vars = isset($this->var[$this->stmtid][$key]) ? $this->var[$this->stmtid][$key] : NULL;
                $result[$index] = new $class($value, $this->conn, $vars);
                self::$lastSelectedId = isset($result[$index]->data[0]['id']) ? $result[$index]->data[0]['id'] : NULL;

            }
            return $this->returnData($result);
        }
    }

    /**
      * Created on Sun Aug 05 2018
      * @name   runFile()
      * @desc   get the params and run the statement from the file
      * @param  string 
      * @param  array
      * @return array of results
     */

    public function runFile($key = NULL, $var = NULL)
    {

        $this->stmtid = $key;
        $this->var = $var;

        return $this->prepInputFile();

    }

    /**
      * Created on Sun Aug 05 2018
      * @name   runArray()
      * @desc   get the params and run the statement from the array
      * @param  array 
      * @return array of results
     */

    public function runArray($array = NULL)
    {

        $this->inputarray = $array;

        return $this->prepInputArray();

    }
    
    /**
      * Created on Sun Aug 05 2018
      * @name   lastSelectedId
      * @desc   return the last selected id from the queue if there is column with that name
      * @return integer
     */

    public static function lastSelectedId()
    {

        return self::$lastSelectedId;

    }
    
   /**
      * Created on Sun Aug 05 2018
      * @name   returnData()
      * @desc   collect the data results from the objects and return them
      * @param  array of objects
      * @return array
     */

    public function returnData($objects)
    {
        $data = [];
        foreach ($objects as $key => $value) {
            $data[$key] = $value->data;
        }
        //execution time 
        $data['exectime'] = $this->executiontime + microtime(true);

        return $this->data = $data;
    }

    /**
      * Created on Sun Aug 05 2018
      * @name   __debugInfo()
      * @desc   hide the sensitive data from the var_dump and print_r
      * @return array 
     */

    public function __debugInfo()
    {
        return ['data' => $this->data, 'exception' => $this->exception];
    }
}

?>