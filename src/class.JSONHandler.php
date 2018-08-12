<?php
   /**
      * Created on Sun Aug 12 2018
      *
      * class.JSONHandler.php
      *
      * Read and write JSON files
      *
      * @category  helper
      * @package   ZDB
      * @author    zashboy
      * @license   https://www.gnu.org/licenses/gpl-3.0.en.html
      * @version   0.0.1
      * @link      https://www.zashboy.com/zdb
      * @see       class.ZDB.php
      * @since     File available since Release 0.0.1
      *
      * Copyright (c) 2018 zashboy.com
     */

class JSONHandler
{
    public function __construct()
    {
        
    }

   /**
      * Created on Sun Aug 12 2018
      * @name   read()
      * @desc   Read the contents of a json file and return it 
      * @param  string $file
      * @param  boolean $arr
      * @return string or NULL
     */

    public static function read($file = NULL, $arr = true)
    {
        try{
            if(!file_exists($file)){
                throw new Exception("JSON file does not exists");
            } else {
                $json = json_decode(file_get_contents($file), $arr);

                return is_null($json) ? NULL : $json;
                
            }
        } catch (Throwable $t) {
            throw $t;
            Log::general($t->getMessage().' | Caught: '.$t->getFile().' | '.$t->getLine());
        }
    }

   /**
      * Created on Sun Aug 12 2018
      * @name   write()
      * @desc   Write json string to a file
      * @param  string $dir path/to/file
      * @param  string $contents
      * @return boolean
     */

    public static function write($dir = NULL, $contents = NULL)
    {
        $parts = explode('/', $dir);
        $file = array_pop($parts);
        $dir = '';

        foreach($parts as $part) {
            if (! is_dir($dir .= "{$part}/")) mkdir($dir);
        }

        return file_put_contents("{$dir}{$file}", json_encode($contents), FILE_APPEND);

    }

}// end class
