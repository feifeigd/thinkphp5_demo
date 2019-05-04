<?php
/**
 * Created by PhpStorm.
 * User: luofei
 * Date: 2019/5/4
 * Time: 21:21
 */
namespace database;

use \think\Db;
use think\Exception;

class Model
{
    private $config = false;
    private $db;
    private $connect;

    public function __construct($config = [])
    {
        if(count($config) > 0){
            $this->config = $config;
        }else{
            $this->config = config('database');
        }
        $this->db = Db::connect($this->config);
    }

    /// 检测是否连接数据库成功
    /// @return int
    public function isconnect(){
        switch ($this->config['type']){
            case 'mysql':
                return $this->mysql_isconnect();
                break;
            default:
                return $this->mysql_isconnect();
                break;
        }
    }

    /// 检测Mysql是否连接数据库成功
    /// @return int
    public  function mysql_isconnect(){
        $servername = $this->config['hostname'] ?: 'localhost';
        $hostport = $this->config['hostport'] ?: '3306';
        $username = $this->config['username'] ?: 'root';
        $password = $this->config['password'] ?: '';
        $database = '';
        try{
            $this->connect = $this->connect ?: mysqli_connect($servername, $username, $password, $database, $hostport);
            //dump($this->connect);die;
            return $this->connect ? 1 : 0;
        }catch (Exception $e){
            dump('mysql_isconnect');dump($e);die;
            return 0;
        }
    }

    /// 检测数据库是否存在
    /// @param $dbname string 数据库名称
    /// @return int
    public function has($dbname = 'cow'){
        switch ($this->config['type']){
            case 'mysql':
                return $this->mysql_has($dbname);
            default:
                return $this->mysql_has($dbname);
        }
    }

    public function mysql_has($dbname = 'cow'){
        if($this->mysql_isconnect())
            try{
                $sql = 'select * from information_schema.schemata where schema_name="'.$dbname.'"';
                $check = $this->db->query($sql);
                return $check ? 1: 0;
            }catch(Exception $e){
                dump($e);die;
                return 0;
            }
        else return -1;
    }
}