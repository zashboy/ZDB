<?php

    /**
      * Created on Sat Aug 04 2018
      *
      * class.generic.php
      *
      * This class handles the generic clauses
      *
      * @category  database wrapper
      * @package   ZDB
      * @author    zashboy
      * @license   https://www.gnu.org/licenses/gpl-3.0.en.html
      * @version   0.0.1
      * @link      https://www.zashboy.com
      * @see       class.ZDB.php
      * @since     File available since Release 0.0.1
      *
      * Copyright (c) 2018 zashboy.com
     */

class generic extends PDO
{

   /**
      * Created on Sat Aug 04 2018
      * @desc Array of the statement variable passed by the zdb.php
      * @var  array
     */
    public $array;

   /**
      * Created on Sat Aug 04 2018
      * @desc PDO instance
      * @var  object
     */
    public $conn;

   /**
      * Created on Sat Aug 04 2018
      * @desc user variables
      * @var  array
     */
    private $variables;

   /**
      * Created on Sat Aug 04 2018
      * @name   __construct
      * @desc   declare connection property and the statement, execute the prepared statement and return its value
      * @param  array $array
      * @return array on success, null or exception on fail
     */
    public function __construct($array = NULL, $conn = NULL, $variables = NULL)
    {
        //PDO instance
        $this->conn = $conn;
        $this->variables = $variables;
        //the prepared statement
        $stmt = $this->prepareStmt($array);
        return $this->run($stmt);

    }

   /**
      * Created on Sat Aug 04 2018
      * @name   prepareStmt
      * @desc   Gathering the necessary info and prepare the statement
      * @param  array $array
      * @return array statement string, array for binding, fetch option int
     */

    public function prepareStmt($array = NULL)
    {

        $sql = $bindarr = NULL;

        if(is_array($array)){
            foreach ($array as $key => $value) {
                $$key = $value;
            }
        }
        if(isset($sql)){
            $bindarr = isset($this->variables['bindarr']) ? $this->variables['bindarr'] : $bindarr;
            return array('stmt' => $sql, 'bindarr' => $bindarr);

        }

    }

   /**
      * Created on Sat Aug 11 2018
      * @name   run()
      * @desc   execute the sql statement 
      * @param  array $stmt
      * @return array $data or NULL
     */
    public function run($stmt = NULL)
    {

        try {

            $query = $this->conn->prepare($stmt['stmt']);
            $dataRaw = $query->execute($stmt['bindarr']);

                if($query->rowCount() != 0){
                    return $this->data = $query->rowCount();

                } else {
                return $this->data = NULL;
            }  
            
        } catch (Throwable $t) {
            $this->exception = ['message' => $t->getMessage(), 'file' => $t->getFile(), 'line' => $t->getLine()];
            Log::general($t->getMessage().' | Caught: '.$t->getFile().' | '.$t->getLine());
        }

    }
}

?>