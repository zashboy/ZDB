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
      * @name   __construct()
      * @desc   Declare properties, decide if the input an array or a file, 
      * declare the input prop and teh config prop based on that, instatiate the PDO based on that
      * call the prepare function based on the type of the input
      * 
      * @param  string $stmtid
      * @param  array or file path $input
      * @var array $ var the variable values
      * @return exception on fail (log it)
     */

    public function __construct($stmtid = NULL, $input = NULL, $var = NULL)
    {

        $this->stmtid = $stmtid;
        $this->input = $input;
        $this->var = $var;
        $this->exception = NULL;
        $this->data = NULL;
        $this->executiontime = -microtime(true);

        try {
            if(!isset($input)){
                throw new Exception('The input is undefined!');
            } elseif(is_array($this->input)){
                $this->input = $input;
                $this->config = NULL;
                $this->conn = new PDO("mysql:host=" . HOSTNAME . ";dbname=" . DBNAME, USERNAME, PASSWORD);
                $this->prepInputArray();
            } elseif(is_file($this->input)){
                $this->input = JSONHandler::read($this->input);
                if(!isset($this->input)){
                    throw new Exception("It's not a valid JSON file");
                }
                $this->config = $this->input['config'];
                $this->conn = new PDO("mysql:host=" . $this->config['HOSTNAME'] . ";dbname=" . $this->config['DBNAME'], $this->config['USERNAME'], $this->config['PASSWORD']);
                $this->prepInputFile();
            } else {
                throw new Exception("There is no appropriate input paremeter added");
            }

        } catch (Throwable $t){
            $this->exception = ['message' => $t->getMessage(), 'file' => $t->getFile(), 'line' => $t->getLine()];
            Log::general($t->getMessage().' | Caught: '.$t->getFile().' | '.$t->getLine());
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
        foreach ($this->input as $key => $value) {
            //chop off the characters after the "-" character from the end of the keys
            $class = strpos($key, "-") ? substr($key, 0, strpos($key, "-")) : $key;
            $result[] = new $class($value, $this->conn);
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
        $result = [];
        foreach($this->input[$this->stmtid] as $key => $value){
            //chop off the characters after the "-" character from the end of the keys
            $class = strpos($key, "-") ? substr($key, 0, strpos($key, "-")) : $key;
            $vars = isset($this->var[$this->stmtid][$key]) ? $this->var[$this->stmtid][$key] : NULL;
            $result[] = new $class($value, $this->conn, $vars);
        }
        return $this->returnData($result);

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
            $data[] = $value->data;
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