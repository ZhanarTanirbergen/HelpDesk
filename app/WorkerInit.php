<?php

namespace App;

class WorkerInit {

    static private $oInstance = null;
    static private $sQueueName = 'init';
    private $oQueue = null;

    static public function run()
    {
        if (is_null(self::$oInstance)) {
            self::$oInstance = new self(new Queue(self::$sQueueName));
            self::$oInstance->listen();
        }
    }

    static public function getQueueName()
    {
        return self::$sQueueName;
    }

    private function __construct($_oQueue)
    {
        $this->oQueue = $_oQueue;
    }

    private function listen()
    {
        $this->oQueue->listen($this);
    }

    public function execute($_aData)
    {                
        sleep(rand(5, 10));
        try {
            $dbh = new \PDO('mysql:host=172.17.0.1;dbname=final', 'admin', '1q2w3e4r', array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); 
            // set the PDO error mode to exception
            $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            echo "Connected successfully"; 
            print_r($_aData);
        }
        catch(PDOException $e)
        {
            echo "Connection failed: " . $e->getMessage();
        }
     
        $sql = "UPDATE `requests` SET `status`= 'INIT' WHERE id = :requestId";
        $stmt = $dbh->prepare($sql);
        $stmt->execute(['requestId' => $_aData['id']]);

        $oQueue = new Queue('process');
        $oQueue->addTask(['id' => $_aData['id']]);
        
    }

}