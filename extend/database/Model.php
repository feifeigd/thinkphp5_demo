<?php
/**
 * Created by PhpStorm.
 * User: luofei
 * Date: 2019/5/4
 * Time: 21:21
 */
namespace database;

use think\facade\Hook;
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
    public  function mysql_isconnect():bool{
        $servername = $this->config['hostname'] ?: 'localhost';
        $hostport = $this->config['hostport'] ?: '3306';
        $username = $this->config['username'] ?: 'root';
        $password = $this->config['password'] ?: '';
        $database = '';
        try{
            $this->connect = $this->connect ?: mysqli_connect($servername, $username, $password, $database, $hostport);

            return $this->connect;
        }catch (Exception $e){
            return false;
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

    /// 创建数据库
    public function create($dbname = 'cow'){
        switch ($this->config['type']){
            case 'mysql':
                return $this->mysql_create($dbname);
                break;
            default:
                return $this->mysql_create($dbname);
                break;
        }
    }

    public function mysql_create($dbname = 'cow'){
        if ($this->mysql_isconnect()){
            try{
                if ($this->mysql_has($dbname)){
                    $this->delete($dbname);
                }
                $create = mysqli_query($this->connect, "create database if not exists `{$dbname}` default character set utf8mb4");
                return $create ? 1 : 0;
            }catch (Exception $e){
                return 0;
            }
        }
        return -1;
    }

    /// 删除数据库
    public function delete($dbname = 'cow'): int{
        switch ($this->config['type']){
            case 'mysql':
                $this->mysql_delete($dbname);
                break;
            default:
                $this->mysql_delete($dbname);
                break;
        }
    }

    public function mysql_delete($dbname = 'cow'): int{
        if ($this->mysql_isconnect()){
            try{
                if (!$this->mysql_has($dbname))return 1;
                $delete = $this->db->execute("drop database `{$dbname}`;");
                return $delete !== false ? 1 : 0;
            }catch (Exception $e){
                return 0;
            }
        }
        return -1;
    }

    public function table_has($tablename = 'cow', $dbname = null): int{
        switch ($this->config['type']){
            case 'mysql':
                return $this->mysql_table_has($tablename, $dbname);
                break;
            default:
                return $this->mysql_table_has($tablename, $dbname);
                break;
        }
    }

    public function mysql_table_has($tablename = 'cow', $dbname = null): int{
        if ($this->mysql_isconnect()){
            try{
                $dbname = $dbname ?: $this->config['database'];
                $sql = "select table_name from information_schema.tables where table_name='".$this->config['prefix']."{$tablename}' and table_schema='{$dbname}'";
                $table = $this->db-query($sql);
                return $table ? 1 : 0;
            }catch (Exception $e){
                return 0;
            }
        }
        return -1;
    }

    /// 获取数据库表名称集合
    public function get_table_names($dbname = null){
        switch ($this->config['type']){
            case 'mysql':
                return $this->mysql_get_table_names($dbname);
                break;
            default:
                return $this->mysql_get_table_names($dbname);
                break;
        }
    }

    public function mysql_get_table_names($dbname = null){
        if ($this->mysql_isconnect()){
            try{
                $dbname = $dbname ?: $this->config['database'];
                $sql = "select * from information_schema.tables where table_schema='{$dbname}'";
                $tables = $this->db->query($sql);
                return $tables ?: [];
            }catch (Exception $e){
                return [];
            }
        }
        return -1;
    }

    public function create_table($tablename = 'cow', $cover = true){
        switch ($this->config['type']){
            case 'mysql':
                return $this->mysql_create_table($tablename, $cover);
                break;
            default:
                return $this->mysql_create_table($tablename, $cover);
                break;
        }
    }

    public function mysql_create_table($tablename = 'cow', $cover = true){
        if ($this->mysql_isconnect()){
            try{
                if ($this->mysql_table_has($tablename)){
                    if ($cover)$this->mysql_delete_table($tablename);
                    else return -2; // 数据表存在
                }
                $this->db->execute("create table `".$this->config['prefix']."{$tablename}` (id int not null auto_increment, primary key(id))");
                if ($this->mysql_table_has($tablename)){
                    return 1;
                }
                return 0;
            }catch (Exception $e){
                return 0;
            }
        }
        return -1;
    }

    public function delete_table($tablename = 'cow'){
        switch ($this->config['type']){
            case 'mysql':
                return $this->mysql_delete_table($tablename);
                break;
            default:
                return $this->mysql_delete_table($tablename);
                break;
        }
    }

    public function mysql_delete_table($tablename){
        if ($this->mysql_isconnect()){
            try{
                $this->db->execute("drop table `".$this->config['prefix']."{$tablename}`");
                if ($this->mysql_table_has($tablename)){
                    return 0;
                }
                return 1;
            }catch (Exception $e){
                return 0;
            }
        }
        return -1;
    }

    /// @param  type int  是否返回创建的数据表，0不返回 1返回
    public function exe_sql_file($filename, $pre = ['cow_cms_'=>'cowcms_'], $type = 0){
        switch ($this->config['type']){
            case 'mysql':
                return $this->mysql_exe_sql_file($filename, $pre, $type);
                break;
            default:
                return $this->mysql_exe_sql_file($filename, $pre, $type);
                break;
        }
    }

    /// @param  type int  是否返回创建的数据表，0不返回 1返回
    public function mysql_exe_sql_file($filename, $pre = ['cow_cms_'=>'cowcms_'], $type = 0)
    {
        $sqltotal = 0;  // 所有文件的总sql条数
        $sqlnum = 0;    // 当前sql语句是总sql语句的第几条
        $sqlfiles = []; // sql文件名数组
        if (!is_array($filename))$filename = [$filename];
        if ($this->mysql_isconnect()){
            if (count($pre) > 0){
                $pre_place = current($pre); // 要替换的前缀值
                $pre_original = current(array_flip($pre));  // 被替换的原始值
            }else {
                $pre_place = $this->config['prefix'];   // 要替换的前缀值
                $pre_original = 'cowcms_';  // 被替换的原始值
            }

            $sqlList = [];
            $engines = $this->mysql_get_engine();
            foreach ($filename as $key => $val){
                if (!is_file($val)){
                    continue;
                }
                $pathinfo = pathinfo($val);
                $file_name = $pathinfo['filename'];
                $sql = file_get_contents($val);
                preg_match('/ENGINE=([a-zA-Z]+)/i', $sql, $sqlEngine);
                if ($sqlEngine && $sqlEngine[1]){
                    if (!in_array($sqlEngine[1], $engines['engine'])){
                        $sql = str_replace('ENGINE='.$sqlEngine[1], 'ENGINE='.$engines['default'], $sql);
                    }
                }
                $sqlList[$file_name] = array_filter($this->reg_sql($sql, [$pre_original=>$pre_place]));
                $sqlList[$file_name] = count($sqlList[$file_name]);
                $sqltotal += count($sqlList[$file_name]);
            }

            try{
                $params = ['files'=>$sqlfiles, 'database'=>$this->config['database'], 'sqltotal'=>$sqltotal];
                Hook::listen('sql_begin', $params);
                foreach ($sqlList as $sqlfile => $sql_list){
                    $tables = [];
                    $inserts = [];
                    if (!$sql_list)continue;

                    $file_total = count($sql_list); // 当前文件的sql条数
                    foreach ($sql_list as $key => $val){
                        $sign = false;
                        // 获取创建的数据表
                        $patterns[] = "/create table if not exists `".$pre_place."([\S]+)`/i";
                        $patterns[] = "/create table `".$pre_place."([\S]+)`/i";
                        foreach ($patterns as $k => $pattern){
                            if (preg_match_all($pattern, $val, $matches)){
                                $table = $matches[1][0];
                                $tables[] = $table;
                                $params = [
                                    'files' => $sqlfiles,
                                    'file'  => $sqlfile,
                                    'database'  => $this->config['database'],
                                    'table' => $table,
                                    'progress'  => ($sqlnum + 1) / $sqltotal,
                                    'sqltotal'  => $sqltotal,
                                    'sqlnum'    => ($sqlnum + 1),
                                    'filenum'   => $key + 1,
                                ];
                                Hook::listen('sqlcreate_begin', $params);
                                $sign = "create";
                                break;
                            }
                        }
                        // 获取创建的数据表完成
                        // 插入记录
                        $pattern = "/insert into `".$pre_place."([\S]+)`/i";
                        if(!sign && preg_match_all($pattern, $val, $matches)){
                            $table = $matches[1][0];
                            $params = [
                                'files' => $sqlfiles,
                                'file'  => $sqlfile,
                                'database'  => $this->config['database'],
                                'table'     => $table,
                                'progress'  => ($sqlnum + 1) / $sqltotal,
                                'sqltotal'  => $sqltotal,
                                'sqlnum'    => $sqlnum + 1,
                                'filenum'   => $key + 1,
                            ];
                            Hook::listen('sqlinsert_begin', $params);
                            $sing = "insert";
                        }
                        // 插入记录完成
                        $this->db->execute($val);
                        $params = [
                            'files' => $sqlfiles,
                            'file'  => $sqlfile,
                            'database'  => $this->config['database'],
                            'table'     => $table,
                            'progress'  => ($sqlnum + 1) / $sqltotal,
                            'sqltotal'  => $sqltotal,
                            'sqlnum'    => $sqlnum + 1,
                            'filenum'   => $key + 1,
                        ];
                        if ($sign == 'create'){
                            Hook::listen('sqlcreate_end', $params);
                        }elseif ($sign == 'insert'){
                            Hook::listen('sqlinsert_end', $params);
                        }
                        ++$sqlnum;
                    }
                }
                $params = [
                    'file'  => $sqlfiles,
                    'database'  => $this->config['database'],
                    'sqltotal'  => $sqltotal,
                ];
                Hook::listen('sql_end', $params);
                return $type ? array_unique($tables) : 1;
            }catch (Exception $e){
                Hook::listen('sql_err', $e);
            }
        }
        return -1;
    }

    public function mysql_get_engine(){
        $engine = $this->db->query("show engines");
        $returnData = [];
        foreach (($engine ?: []) as $k=>$v){
            if (strtolower(trim($v['Support'])) != 'no'){
                $returnData['default'] = trim($v['Engine']);
            }
            $returnData['engine'][] = trim($v['Engine']);
        }
        return $returnData;
    }

    function reg_sql($sql, $pre = ['cowcms_'=>'cowcms_']){
        if (trim($sql) == '')return [];
        $returnData = [];
        if (count($pre) > 0){
            $pre_place = current($pre); // 要替换的前缀值
            $pre_original = current(array_flip($pre));  // 被替换的原始前缀
        }

        // 多行注释标记
        $comment = false;

        // 按行分割，兼容多个平台
        $sql = str_replace(['\r\n', '\r'], '\n', $sql);
        $sql = explode('\n', trim($sql));

        // 循环处理每一行
        foreach ($sql as $key=>$line){
            // 跳过空行
            if ($line == '')continue;
            // 跳过以#或者--开头的单行注释
            if (preg_match("/^(#|--)/", $line))continue;
            // 跳过以/**/包裹起来的单行注释
            if (preg_match("/^\/\*(.*?)\*\//", $line))continue;
            // 多行注释开始
            if (substr($line, 0, 2) == '/*'){
                $comment = true;
                continue;
            }
            // 多行注释结束
            if (substr($line, -2) == '*/'){
                $comment = false;
                continue;
            }
            // 多行注释没有结束，继续跳过
            if ($comment)continue;
            // 替换表前缀
            if ($pre_original != '')
                $line = str_replace('`'.$pre_original, '`'.$pre_place, $line);
            if ($line == 'begin;' || $line == 'commit;')continue;

            // sql语句
            array_push($returnData, $line);
        }
        $returnData = implode($returnData, '\n');
        $returnData = explode(";\r", $returnData);
        return $returnData;
    }
}
