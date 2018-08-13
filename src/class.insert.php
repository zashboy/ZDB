<?php

    /**
      * Created on Sat Aug 04 2018
      *
      * class.insert.php
      *
      * This class handles the insert clauses
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

class  insert extends PDO
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
        var_dump($stmt);

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

        $tableName = $values = $bindarr = NULL;

        if(is_array($array)){
            foreach ($array as $key => $value) {
                $$key = $value;
            }
        }
        if(isset($tableName) && isset($values)){

            $tableName = "`".str_replace("`","``",$tableName)."`";
            $values = $this->values($values);
    
            return array('stmt' => "INSERT INTO " . $tableName . "(" . $values['col'] . ") VALUES (" . $values['val'] . ")", 'bindarr' => $values['bindarr']);

        }

    }

    /**
      * Created on Thu Aug 02 2018
      * @name   values()
      * @desc   prepare the values for insertion
      * @param  array,string 
      * @return string
     */

    public function values($array = NULL)
    {

        $col = implode(',', array_keys($array));
        $value = ':' . implode(',:', array_keys($array));
        $bindarr = array_combine(explode(',', $value), $array);

        return array('col' => $col, 'val' => $value, 'bindarr' => $bindarr);

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
                    return $this->data = $query->rowCount() . ' rows inserted';

                } else {
                    return $this->data = NULL;
            }  
            
        } catch (Throwable $t) {
            $this->exception = ['message' => $t->getMessage(), 'file' => $t->getFile(), 'line' => $t->getLine()];
        }

    }
}

?>