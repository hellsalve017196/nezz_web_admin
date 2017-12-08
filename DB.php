<?php
require_once 'config.php';

class DB
{
    public static $instance;
    private $_msqli,$_query,$_results = array(),$_count=0;

    public static function getInstance()//becoz of this ,db constructor is calling one time
    {
        if(!isset(self::$instance))
        {
            self::$instance = new DB();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->_msqli = new mysqli(constant("DB_HOST"),constant("DB_USER"),constant("DB_PASS"),constant("DB_NAME"));
        if($this->_msqli->connect_error)
        {
            die($this->_msqli->connect_error);
        }
    }

    public function query($sql)
    {
        if($this->_query = $this->_msqli->query($sql))
        {

        }
        else
        {
            echo 'please check your query';
        }
        return $this;
    }

    public function results()
    {
        while($row = $this->_query->fetch_object())
        {
            $this->_results[] = $row;
        }

        return $this->_results;
    }

    public function reseting()
    {
        $this->_results = null;
    }

    public function count()
    {
        $this->_count = $this->_query->num_rows;
        return $this->_count;
    }
}
?>