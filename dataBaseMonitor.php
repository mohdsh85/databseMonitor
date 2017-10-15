<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of dataBaseMonitor
 *
 * @author Shareif email: mohdsh85@gmail.com
 * know more about your database 
 * check tables need to be optimized 
 * get action and control for your db
 */
class dataBaseMonitor extends CI_Controller {
    
    /*
     * get constructor values and inherit data require 
     */
    public function __construct() {
            parent::__construct();
            $this->load->database();
    }
    /*
     * put extra conditions on your remap function if you need 
     */
    public function _remap($method, $params = array()){
        if($this->can_access())//this is could be database or session  check or ip check as i will showe you 
            call_user_func_array(array($this, $method), $params);
        else
        {
            echo 'cant\'t Access this Page:( ';
        }
    }
    /*
     * IP inside the below function will be static to make it work form any connection 
     */
    public function can_access()
    {
        
        $ips='10.10.10.10';//set your IP address here to avoid accessibailty from any other IPs
        ////make this ip to be read dynamiclly not static
        //this is to define your trusted IPs
        $array=array('10.10.10.10');
            if(!in_array($ips,$array))
                return false;
        
        return true;
    }
    /*
     * get status for your database simply and print it out directly to screen 
     * you can load view here for your result put i will print it directly 
     * database status will return for you status and innodb status 
     */
    public function databaseStatus()
    {
        echo '<style>body{background:black;color:white;}</style>';
        exec("mysql  -u ".$this->db->username." --password=".$this->db->password." ".$this->db->database." -e 'status;SHOW ENGINE INNODB STATUS \G'", $output, $return_var);
        foreach($output as $line)
        {
            echo '> '.$line.'<br/>';
            flush();
            ob_flush();
        }

    }
    
    /*
     * get your database tables infrom from your schema 
     * you can use execute command or php commands to get database tables status 
     * and which tables need to be optimized or not 
     */
    public function databaseHealth()
    {
        $link = mysql_connect($this->db->hostname, $this->db->username, $this->db->password);       
        if (!$link) {
            die('Not connected : ' . mysql_error());
        }
         // make foo the current db
        $db_selected = mysql_select_db('information_schema', $link);
        if (!$db_selected) {
            die ('Can\'t use  : ' . mysql_error());
        }
        else
        {
          //  echo 'data base selected';
        }
        $data= '<style>body{background:gray;color:white;}</style>';
        $result = mysql_query("select TABLE_NAME,"
                  . " round ((( data_length+index_length) / 1024 /1024), 2) as totalSize, "
                  . "round ((data_free/104/1024),2) as freeSize from TABLES where TABLE_SCHEMA='".$this->db->database."' order by totalSize;");

        $data.='<table border="1" cellpadding="2" cellspascing="2" width="600px"> ';  
            while ($row=mysql_fetch_array($result)) {
              //  echo $row["totalSize"]    ;
                $tbl = $row["TABLE_NAME"];
                $data.='<tr>';
                $data.='<td>'.$tbl.'</td>';
                $data.='<td>'.$row["totalSize"].'</td>';
                $data.='<td>'.$row["freeSize"].'</td>';
                if($row["freeSize"]>0)
                    $data.='<td>Need Optimize</td><td><input type="button" onclick="optimizeTBL(\''.$row["TABLE_NAME"].'\')" value="Optimize"/></td>';    
                else
                    $data.='<td>Normal</td><td></td>';    
            }
            $data.='</table>';
            mysql_close();
            echo $data;
    }
    /*
     * statrt optimizing your table using Ajax command 
     * or using web call 
     */
    public function optimize_table($tableName) {
        exec("mysql  -u ".$this->db->username." --password=".$this->db->password." ".$this->db->database." -e 'OPTIMIZE TABLE ".$tableName ."'", $output, $return_var);        
        echo '<style>body{background:black;color:white;}</style>';
        foreach($output as $line){
            echo '> '.$line.'<br/>';
            if(usleep(40000)!=0)
            {
                echo "sleep failed script termination"; 
                break;
            }
            flush();
            ob_flush();
        }
    }
    /*
     * call this using your cron job tools 
     * make your database connection stable 
     * kill any unrequire connections 
     * 
     */
    public function killDbProcess()
    {
        $result = mysql_query("SHOW FULL PROCESSLIST");
        //  print_r($result);
        while ($row=mysql_fetch_array($result)) {
            $process_id=$row["Id"];
            if ($row["Time"] >= 60 || ($row["Command"] =='Sleep' && $row["Time"] >= 15))
            {
                 $sql="KILL $process_id";
                     mysql_query($sql);
             }
        }
    }
        
}
