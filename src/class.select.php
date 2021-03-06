<?php

    /**
      * Created on Sat Aug 04 2018
      *
      * class.select.php
      *
      * This class handles the select clauses
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
      * @desc PDO instance
      * @var  object
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
        //variables array 
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

        $what = $tableName = $where = $orderby = $groupby = $limit = $bindarr = NULL;
        //default fetch option FETCH_ASSOC
        $fetch = 2;

        if(is_array($array)){
            foreach ($array as $key => $value) {
                $$key = $value;
            }
        }

        if(isset($tableName)){

            $what = $this->what($what);
            //if the tablename declared in the variables array then use that one
            $tableName = isset($this->variables['tableName']) ? "`" . str_replace("`", "``", $this->variables['tableName']) . "`" : "`" . str_replace("`", "``", $tableName) ."`";
            $where = $this->where($where);
            $orderby = $this->orderby($orderby);
            $groupby = $this->groupby($groupby);
            $limit = $this->limit($limit);

            if(!isset($bindarr) && isset($where['bindarr'])){
                $bindarr_fin = $where['bindarr'];
            } elseif(!isset($where['bindarr'])){
                $bindarr_fin = $bindarr;
            } else {
                $bindarr_fin = array_merge($bindarr, $where['bindarr']); // keys in the arrays cannot be the same or the last one overwrite the previous one
            }


            return array('stmt' => "SELECT " . $what  .  " FROM " . $tableName . " " . $where['where'] .  $orderby . $groupby . $limit, 'bindarr' => $bindarr_fin, 'fetch' => $fetch);

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

                    if(count($where) > 1) {
                        $_where .= ' AND ';
                    }

                    if($value == '$lastSelectedId'){
                        $bindarr[':' . $key] = ZDB::lastSelectedId();

                    } else {
                        $bindarr[':' . $key] = isset($this->variables['where'][$key]) ? $this->variables['where'][$key] : $value;

                    }
                }
                return array('where' => preg_replace('/ AND $/', '', $_where), 'bindarr' => $bindarr);

            } else {
                return array('where' => 'WHERE ' . $where . ' ', 'bindarr' => NULL);
            }
        } else {
            return ['where' => NULL];
        }

    }

    /**
      * Created on Thu Aug 02 2018
      * @name   what() -- requested data
      * @desc   prepare the requested data for sql
      * @param  string
      * @return string
     */

    public function what($what = NULL)
    {
        return isset($what) ? $what : '*';
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
        return isset($orderby) ? (is_array($orderby) ? ' ORDER BY `' . $orderby[0] . '` ' . $orderby[1] . ' ' : ' ORDER BY `' . $orderby . '` ') : NULL;
    }
   /**
      * Created on Fri Nov 23 2018
      * @name   groupby()
      * @desc   prepare the statement's groupby portion to sql
      * @param  string $groupby
      * @return string
     */

    public function groupby($groupby = NULL)
    {
        $groupby = isset($this->variables['groupby']) ? $this->variables['groupby'] : $groupby;
        return isset($groupby) ? ' GROUP BY `' . $groupby . '` ' : NULL;
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
        return isset($limit) ? (is_array($limit) ? ' LIMIT ' . $limit[0] . ',' . $limit[1] . ' ' : ' LIMIT ' . $limit . ' ') : NULL;
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

        $fetch = isset($stmt['fetch']) ? $stmt['fetch'] : NULL;

        try {

            $this->query = $this->conn->prepare($stmt['stmt']);
            $this->bindarr = $stmt['bindarr'];
            $this->runQuery = $this->query->execute($this->bindarr);

            if(ZDB::$debug_mode) {
                echo '<div style="background-color:red;color:white;overflow-wrap: normal;">';
                print_r($this);
                echo '</div>';
            }

            if($this->query->rowCount() != 0){
                $data = $this->query->fetchAll($fetch);

                return $this->data = $data;

            } else {
                return $this->data = NULL;
            }
            
        } catch (Throwable $t) {
            $this->exception = ['message' => $t->getMessage(), 'file' => $t->getFile(), 'line' => $t->getLine()];

            if(ZDB::$debug_mode) {
                echo '<div style="background-color:red;color:white;overflow-wrap: normal;">';
                print_r($this);
                echo '</div>';
            }
            
            return $this->data = NULL;
        }

    }
}

?>