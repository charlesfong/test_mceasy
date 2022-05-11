<?php

/**
 * @author jalil
 * @copyright 2017
 */

$string_json='
{
    "data":[
        {
            "obs": "Philippine",
            "server":{
                "data":
                [
                    "obs7.dxn2u.com",
                    "localhost",
                    "192.168.88.57"
                ]
            },
            "sides":{
                "data":
                [
                    {
                        "id":"mlmtest",
                        "host": "localhost",
                        "user": "mlmsystem",
                        "dbname": "db_mlmfinance_dxnphil_test",
                        "port": "5432",
                        "sync_dbname": "sync_central_test",
                        "header": "",
                        "admin_host": "127.0.0.1",
                        "admin_user": "mlmsystem",
                        "admin_pwd": "dxn065T34m^^",
                        "admin_dbname": "db_mlm_admin_test",
                        "admin_port": "5432",                        
                        "central_host": "localhost",
                        "central_user": "mlmsystem",
                        "central_pwd": "dxn065T34m^^",
                        "central_dbname": "db_central_test",
                        "central_port": "5432",
                        "epoint_server": "http://localhost/mlmtest/epoint/",                                               
                        "epoint_obs": "http://localhost/mlmtest/epoint/",
                        "epoint_host": "localhost",
                        "epoint_user": "mlmsystem",
                        "epoint_pwd": "dxn065T34m^^",
                        "epoint_dbname": "db_epoint_test",
                        "epoint_port": "5432",
                        "eworld_host": "localhost",
                        "eworld_user": "mlmsystem",
                        "eworld_pwd": "dxn065T34m^^",
                        "eworld_dbname": "db_member_test",
                        "eworld_port": "5432",
                        "bdc_host": "localhost",
                        "bdc_user": "mlmsystem",
                        "bdc_pwd": "dxn065T34m^^",
                        "bdc_dbname": "db_bdctest",
                        "bdc_port": "5432"
                        
                    },                
                    {
                        "id":"philippines",
                        "host": "localhost",
                        "user": "mlmsystem",
                        "pwd": "dxn065T34m^^",
                        "dbname": "db_mlmfinance_dxnphil",
                        "port": "5432",
                        "header": "",
                        "admin_host": "localhost",
                        "admin_user": "mlmsystem",
                        "admin_pwd": "dxn065T34m^^",
                        "admin_dbname": "db_mlm_admin",
                        "admin_port": "5432",                        
                        "central_host": "10.76.99.39",
                        "central_user": "mlmsystem",
                        "central_pwd": "mlmuser",
                        "central_dbname": "db_central",
                        "central_port": "9999",
                        "epoint_server": "https://eworld.dxn2u.com/",                                               
                        "epoint_obs": "https://obs7.dxn2u.com/philippines/epoint/",      
                        "eworld_host": "10.160.149.224",
                        "eworld_user": "iocuser",
                        "eworld_pwd": "5a87e3f696ae24e441df28e44644a219",
                        "eworld_dbname": "db_member_live",
                        "eworld_port": "5432",
                        "bdc_host": "119.110.96.199",
                        "bdc_user": "mlmusr",
                        "bdc_pwd": "mlmpass",
                        "bdc_dbname": "db_bdc",
                        "bdc_port": "5432"                                                                                                                 
                    },
                    {
                        "id":"philippinestraining",
                        "host": "localhost",
                        "user": "mlmsystem",
                        "pwd": "dxn065T34m^^",
                        "dbname": "db_mlmfinance_dxnphil_train",
                        "port": "5454",
                        "header": "",
                        "admin_host": "localhost",
                        "admin_user": "mlmsystem",
                        "admin_pwd": "dxn065T34m^^",
                        "admin_dbname": "db_mlm_admin_train",
                        "admin_port": "5454",                        
                        "central_host": "localhost",
                        "central_user": "mlmsystem",
                        "central_pwd": "dxn065T34m^^",
                        "central_dbname": "db_central_train",
                        "central_port": "5454",
                        "epoint_server": "https://neweworld.dxn2u.com/web/eppayPH/",                                               
                        "epoint_obs": "https://obs7.dxn2u.com/philippinestraining/epoint/"                                                                                               
                    },
                    {
                        "id":"philippinestest",
                        "host": "localhost",
                        "user": "mlmsystem",
                        "pwd": "dxn065T34m^^",
                        "dbname": "db_mlmfinance_dxnphil_test",
                        "port": "5454",
                        "sync_dbname": "sync_central_test",
                        "header": "",
                        "admin_host": "localhost",
                        "admin_user": "mlmsystem",
                        "admin_pwd": "dxn065T34m^^",
                        "admin_dbname": "db_mlm_admin_test",
                        "admin_port": "5454",                        
                        "central_host": "10.72.54.236",
                        "central_user": "mlmsystem",
                        "central_pwd": "mlmuser",
                        "central_dbname": "db_central_test",
                        "central_port": "5453",
                        "epoint_server": "https://epointdev.dxn2u.com/",                                               
                        "epoint_obs": "https://obs7.dxn2u.com/philippinestest/epoint/",
                        "epoint_host": "10.87.51.115",
                        "epoint_user": "epointdev",
                        "epoint_pwd": "372612566d285e9579061a918a3796da",
                        "epoint_dbname": "db_epoint_test",
                        "epoint_port": "5454",
                        "eworld_host": "10.160.149.228",
                        "eworld_user": "iocuser",
                        "eworld_pwd": "SotoEnak79",
                        "eworld_dbname": "db_member_test",
                        "eworld_port": "5434",
                        "bdc_host": "119.110.96.199",
                        "bdc_user": "mlmusr",
                        "bdc_pwd": "mlmpass",
                        "bdc_dbname": "db_bdctestz", 
                        "bdc_port": "5432"
                    },
                    {
                        "id":"ph_train",
                        "host": "localhost",
                        "user": "mlmsystem",
                        "pwd": "dxn065T34m^^",
                        "dbname": "db_mlmfinance_dxnphil_train",
                        "port": "5454",
                        "header": "",
                        "admin_host": "localhost",
                        "admin_user": "mlmsystem",
                        "admin_pwd": "dxn065T34m^^",
                        "admin_dbname": "db_mlm_admin_train",
                        "admin_port": "5454",                        
                        "central_host": "localhost",
                        "central_user": "mlmsystem",
                        "central_pwd": "dxn065T34m^^",
                        "central_dbname": "db_central_train",
                        "central_port": "5454",
                        "epoint_server": "https://neweworld.dxn2u.com/web/eppayPH/",                                               
                        "epoint_obs": "https://obs7.dxn2u.com/ph_train/epoint/"                                                                                                                                                                                                                                                                                             
                    },                    
                    {
                        "id":"ph_live",
                        "host": "localhost",
                        "user": "mlmsystem",
                        "pwd": "dxn065T34m^^",
                        "dbname": "db_mlmfinance_dxnphil",
                        "port": "5432",
                        "header": "",
                        "admin_host": "localhost",
                        "admin_user": "mlmsystem",
                        "admin_pwd": "dxn065T34m^^",
                        "admin_dbname": "db_mlm_admin",
                        "admin_port": "5432",                        
                        "central_host": "10.76.99.39",
                        "central_user": "mlmsystem",
                        "central_pwd": "mlmuser",
                        "central_dbname": "db_central",
                        "central_port": "9999",
                        "epoint_server": "",                                               
                        "epoint_obs": "https://obs7.dxn2u.com/ph_live/epoint/"                                                                                                                                                                                                                                                                                                                                                                                            
                    },                    
                    {
                        "id":"ph_ep",
                        "host": "localhost",
                        "user": "mlmsystem",
                        "pwd": "dxn065T34m^^",
                        "dbname": "db_mlmfinance_dxnphil",
                        "port": "5432",
                        "header": "",
                        "admin_host": "localhost",
                        "admin_user": "mlmsystem",
                        "admin_pwd": "dxn065T34m^^",
                        "admin_dbname": "db_mlm_admin",
                        "admin_port": "5432",                        
                        "central_host": "10.76.99.39",
                        "central_user": "mlmsystem",
                        "central_pwd": "mlmuser",
                        "central_dbname": "db_central",
                        "central_port": "9999",
                        "epoint_server": "https://eworld.dxn2u.com/",                                               
                        "epoint_obs": "https://obs7.dxn2u.com/philippines/epoint/",      
                        "eworld_host": "10.160.149.224",
                        "eworld_user": "iocuser",
                        "eworld_pwd": "5a87e3f696ae24e441df28e44644a219",
                        "eworld_dbname": "db_member_live",
                        "eworld_port": "5432",
                        "bdc_host": "119.110.96.199",
                        "bdc_user": "mlmusr",
                        "bdc_pwd": "mlmpass",
                        "bdc_dbname": "db_bdc",
                        "bdc_port": "5432"                                                                                                                 
                    }
                                        
                ]
            }

        }
    ]
}';

$decoded = json_decode($string_json);

class ClassConnection{
    public $id= "";
    public $host= "";
    public $user= "";
    public $password= "";
    public $dbname= "";
    public $port= "";
    public $header= "";
    public $sync_dbname= "";
    
    public $admin_host= "";
    public $admin_user= "";
    public $admin_password= "";
    public $admin_dbname= "";
    public $admin_port= "";
    
    public $central_host= "";
    public $central_user= "";
    public $central_password= "";
    public $central_dbname= "";
    public $central_port= "";
    
    public $epoint_server= "";
    public $epoint_obs= "";
    public $epoint_host= "";
    public $epoint_user= "";
    public $epoint_password= "";
    public $epoint_dbname= "";
    public $epoint_port= "";

    public $eworld_host= "";
    public $eworld_user= "";
    public $eworld_password= "";
    public $eworld_dbname= "";
    public $eworld_port= "";

    public $bdc_host= "";
    public $bdc_user= "";
    public $bdc_password= "";
    public $bdc_dbname= "";
    public $bdc_port= "";
    
}

$clsConnection= new ClassConnection();

if (!isset($_SERVER['HTTP_HOST']) || empty($_SERVER['HTTP_HOST'])){
    $clsConnection= null;    
    return;
}

$host= $_SERVER['HTTP_HOST'];
//echo $host."\n";

if(in_array($host,$decoded->data[0]->server->data)){
    $uri= explode("/",$_SERVER["REQUEST_URI"]);
    //var_dump($uri);

    $sides = $decoded->data[0]->sides->data;
    foreach($sides as $side){
        //echo "side=".$side->id."\n";
        //do something with it
        if($side->id===$uri[1]){
            $clsConnection->id= $side->id;
            $clsConnection->host= $side->host;
            $clsConnection->user= $side->user;
            $clsConnection->password= $side->pwd;
            $clsConnection->dbname= $side->dbname;
            $clsConnection->port= $side->port;
            $clsConnection->sync_dbname= $side->sync_dbname;
            //===================================================
            $clsConnection->admin_host= $side->admin_host;
            $clsConnection->admin_user= $side->admin_user;
            $clsConnection->admin_password= $side->admin_pwd;
            $clsConnection->admin_dbname= $side->admin_dbname;
            $clsConnection->admin_port= $side->admin_port;
            //====================================================
            $clsConnection->central_host= $side->central_host;
            $clsConnection->central_user= $side->central_user;
            $clsConnection->central_password= $side->central_pwd;
            $clsConnection->central_dbname= $side->central_dbname;
            $clsConnection->central_port= $side->central_port;
            //====================================================
            $clsConnection->epoint_server= $side->epoint_server;
            $clsConnection->epoint_obs= $side->epoint_obs;
            $clsConnection->epoint_host= $side->epoint_host;
            $clsConnection->epoint_user= $side->epoint_user;
            $clsConnection->epoint_password= $side->epoint_pwd;
            $clsConnection->epoint_dbname= $side->epoint_dbname;
            $clsConnection->epoint_port= $side->epoint_port;
            //=====================================================
            $clsConnection->eworld_host= $side->eworld_host;
            $clsConnection->eworld_user= $side->eworld_user;
            $clsConnection->eworld_password= $side->eworld_pwd;
            $clsConnection->eworld_dbname= $side->eworld_dbname;
            $clsConnection->eworld_port= $side->eworld_port;
            //=============================================
            $clsConnection->bdc_host= $side->bdc_host;
            $clsConnection->bdc_user= $side->bdc_user;
            $clsConnection->bdc_password= $side->bdc_pwd;
            $clsConnection->bdc_dbname= $side->bdc_dbname;
            $clsConnection->bdc_port= $side->bdc_port;
            
        }
    }
    
}

$strConn= json_encode($clsConnection);
//echo $strConn; 

?>