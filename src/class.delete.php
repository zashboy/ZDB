<?php

    /**
      * Created on Sat Aug 04 2018
      *
      * class.delete.php
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

class delete extends PDO
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
        //PDO insatnce
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

        $tableName = $where = $orderby = $limit = $bindarr = NULL;

        if(is_array($array)){
            foreach ($array as $key => $value) {
                $$key = $value;
            }
        }
        if(isset($tableName) && isset($where)){

            $tableName = isset($this->variables['tableName']) ? "`".str_replace("`","``",$this->variables['tableName'])."`" : "`".str_replace("`","``",$tableName)."`";
            $where = $this->where($where);
            $orderby = $this->orderby($orderby);
             $limit = $this->limit($limit);
    
            return array('stmt' => "DELETE FROM " . $tableName . " " . $where['where'] .  $orderby .  $limit, 'bindarr' => $where['bindarr']);

        }

    }
    /**
      * Created on Thu Aug 02 2018
      * @name   where()
      * @desc   prepare the where clause to sql
      * @param  array,string 
      * @return string
     */

    public function where($where = NULL)
    {
        if(isset($where)){

            if(is_array($where) && count($where) > 0){

                $_where = '';
                $_where .= 'WHERE ';

                foreach ($where as $key => $value) {
                    $_where .= '`' . $key . '` = :' . $key;

                        for ($i=1; $i < count($where); $i++) { 
                            $_where .= ', ';
                        }
                    $_where .= ' ';

                    $bindarr[':' . $key] = isset($this->variables['where'][$key]) ? $this->variables['where'][$key] : $value;
                }
                return array('where' => $_where, 'bindarr' => $bindarr);

            } else {
                return 'WHERE ' . $where . ' ';
            }
        } else {
            return NULL;
        }

    }
    
   /**
      * Created on Thu Aug 02 2018
      * @name   orderby()
      * @desc   prepare the statement's orderby portion to sql
      * @param  array,string $orderby
      * @return string
     */

    public function orderby($orderby = NULL)
    {
        $orderby = isset($this->variables['orderby']) ? $this->variables['orderby'] : $orderby;
        return isset($orderby) ? (is_array($orderby) ? 'ORDER BY `' . $orderby[0] . '` ' . $orderby[1] . ' ' : 'ORDER BY `' . $orderby . '` ') : NULL;
    }

    /**
      * Created on Thu Aug 02 2018
      * @name   limit()
      * @desc   prepare the statement's limit portion to sql
      * @param  array,string
      * @return string
     */

    public function limit($limit = NULL)
    {
        $limit = isset($this->variables['limit']) ? $this->variables['limit'] : $limit;
        return isset($limit) ? (is_array($limit) ? 'LIMIT ' . $limit[0] . ',' . $limit[1] . ' ' : 'LIMIT ' . $limit . ' ') : NULL;
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
                    return $this->data = $query->rowCount() . ' rows deleted';

                } else {
                    return $this->data = NULL;
            }  
            
        } catch (Throwable $t) {
            $this->exception = ['message' => $t->getMessage(), 'file' => $t->getFile(), 'line' => $t->getLine()];
        }

    }
}

?>