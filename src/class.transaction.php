<?php

    /**
      * Created on Sat Aug 04 2018
      *
      * class.transaction.php
      *
      * This class handles the delete clauses
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

class select extends PDO
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
      * @param  object $conn
      * @param  array $variables
      * @return array on success, null or exception on fail
     */
    public function __construct($array = NULL, $conn = NULL, $variables = NULL)
    {
        //PDO instance
        $this->conn = $conn;
        $this->variables = $variables;
        return $this->run($array);

    }

   /**
      * Created on Sat Aug 11 2018
      * @name   run()
      * @desc   execute the sql statements
      * @param  array $stmt
      * @return array $data or NULL
     */
    public function run($array = NULL)
    {

        try {

            $pdo->beginTransaction();

            $result = [];
            foreach ($array as $key => $value) {
                $class = substr($key, 0, -2);
                $result[] = new $class($value, $this->conn, $this->variables[$key]);
            }
    
            return $this->data = $pdo->commit() ? $result : NULL;

        } catch (Throwable $t) {
            $pdo->rollback();
            $this->exception = ['message' => $t->getMessage(), 'file' => $t->getFile(), 'line' => $t->getLine()];
            Log::general($t->getMessage().' | Caught: '.$t->getFile().' | '.$t->getLine());
        }

    }
}

?>