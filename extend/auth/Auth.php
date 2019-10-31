<?php


namespace auth;

use user\Base;

class Auth
{
    protected $config = [
        'auth_group'    => null,    // 用户组数据表名
        'auth_rule'     => 'auth_auth', // 权限规则表
        'auth_user'     => null,    // 用户信息表
        'auth_field'    => 'auth',
    ];

    function __construct($config = [])
    {
        if($config){
            $this->config = array_merge($this->config, $config);
        }
    }

    /// 检查权限
    /// @param $name string|array 需要验证的规则列表，支持逗号分隔权限规则或索引数组
    /// @param $mode string 支持check模式
    /// @param $relation string 如果为'or' 表示满足任一条规则即通过验证；如果为'and'则表示需满足所有规则才能通过验证
    public function check($name, $userInfo = [], $mode = 'url', $relation = 'or'):bool{
        if(!isInstall('auth'))return true;
        if(!$userInfo || !$userInfo['id'])return false;

        $uid = $userInfo['id'];
        $degree = $userInfo['type'] ? $userInfo['type'] : 'admin';
        $authList = $this->get_rule($uid, $degree); // 需检测的权限列表
        if($mode == 'url'){
            $module = strtolower(request()->module());
            $controller = strtolower(request()->controller());
            $action = strtolower(request()->action());

            $authWhere = [
                'm' => $module,
                'c' => $controller,
                'a' => $action,
                'status'    => 1,
            ];
            $authCount = db('auth_auth')->where($authWhere)->count();
            if($authCount <= 0) return true;

            $name = [$module, $module.'/'.$controller, $module.'/'.$controller.'/'.$action];
            return $this->url_check($name, $authList, 'url', $relation);
        }
        return $this->menu_check($name, $authList, $relation);
    }

    /// 检查权限
    /// @param $name string|array 需要验证的规则列表，支持逗号分隔的权限规则或索引数组
    /// @param $authList array 要检测的权限列表
    /// @param $mode string 支持check模式
    /// @param $relation string 如果为'or' 表示满足任一条规则即通过验证；如果为'and'则表示需满足所有规则才能通过验证
    public function url_check($name, $authList, $mode = 'url', $relation = 'or'){
        if(!$authList || count($authList) <= 0)return true;
        if(is_string($name)){
            $name = strtolower($name);
            if(strpos($name, ',') !== false)$name = explode(',', $name);
            else $name = [$name];
        }else {
            foreach ($name as $k=>$v){
                $name[$k] = strtolower($v);
            }
        }
        $list = []; // 保存验证通过的规则名
        if('url' == $mode){
            $REQUEST = unserialize(strtolower(serialize(request()->param())));
        }

        foreach ($authList as $auth){
            $auth = strtolower($auth);
            $query = preg_replace('/^.+\?/U', '', $auth);
            if('url' == $mode && $query != $auth){
                parse_str($query, $param);
                $intersect = array_intersect_assoc($REQUEST, $param);
                $auth = preg_replace('/\?.*$/U', '', $auth);
                if(in_array($auth, $name) && $intersect == $param)
                    $list[] = $auth;
            }else {
                if(in_array($auth, $name))
                    $list[] = $auth;
            }
        }

        if('or' == $relation && !empty($list))
            return true;
        $diff = array_diff($name, $list);
        if('and' == $relation && empty($diff))
            return true;
        return false;
    }

    /// 检查菜单权限
    /// @param $name string|array 需要验证的规则列表，支持逗号分隔的权限规则或者索引数组
    /// @param $authList array 要检测的权限列表
    /// @param $relation string 如果为'or' 表示满足任一条规则即通过验证；如果为'and'则表示需满足所有规则才能通过验证
    public function menu_check($name, $authList, $relation = 'or'):bool{
        if (!$authList || count($authList) <= 0)
            return true;
        if(is_string($name)){
            $name = strtolower($name);
            if (strpos($name, ',') !== false)
                $name = explode(',', $name);
            else
                $name = [$name];
        }
        $list = []; // 保存验证通过的规则名
        foreach ($authList as $auth){
            $auth = strtolower($auth);
            $query = preg_replace('/^.+\?/U', '', $auth);
            $auth_ = preg_replace('/\?.+$/U', '', $auth);
            foreach ($name as $n){
                $n = strtolower($n);
                $nameQuery = preg_replace('/^.+\?/U', '', $n);
                $nameAuth = preg_replace('/\?.+$/U', '', $n);
                if($query){
                    parse_str($query, $authParam);
                    parse_str($nameQuery, $nameParam);
                    $intersect = array_intersect_assoc($nameParam, $authParam);
                    if ($auth_ == $nameAuth && $intersect == $authParam)
                        $list[] =$auth;
                }else{
                    if($auth_ == $nameAuth)
                        $list[] = $auth;
                }
            }
        }

        if ('or' == $relation && !empty($list))
            return true;
        if('and' == $relation && empty(array_diff($name, $list)))
            return true;
        return false;
    }

    /// 检查权限
    /// @param $degree string 用户身份 admin管理员, member会员
    /// @param $uid int 认证用户的id
    /// @return array 返回规则列表
    public function get_rule($uid, $degree = 'admin'): array{
        $degree = $degree ?: 'admin';
        $uid = $uid ?: session('uid');
        if(!$uid) return [1];
        if($degree == 'admin'){
            $this->config['auth_group'] = 'admin_group';
            $this->config['auth_user'] = 'admin_user';
        }elseif($degree == 'admin'){
            $this->config['auth_group'] = 'member_group';
            $this->config['auth_user'] = 'member_user';
        }else {

        }
        $userWhere[] = ['id', '=', $uid];
        $userInfo = db($this->config['auth_user'])->where($userWhere)->find();
        if(count($userInfo) <= 0)return [1];

        $groupId = $userInfo['group_id'];
        $groupWhere[] = ['id', 'in', $groupId];
        $groupAuth = db($this->config['auth_group'])->where($groupWhere)->column($this->config['auth_field']);
        $groupAuth[] = $userInfo[$this->config['auth_field']];

        $authListId = implode(',', $groupAuth);
        $authListId = explode(',', $authListId);
        $authListId = array_unique($authListId);

        $authList = [];
        foreach ($authListId as $v){
            $v = trim($v);
            if($v && is_numeric($v))$authList[] = $v;
        }
        $authList = array_unique($authList);

        $authWhere[] = ['id', 'in', $authList];
        $authList = db($this->config['auth_rule'])->where($authWhere)->select();

        $returnData = [];
        foreach ($authList as $v){
            $m = trim($v['m']);
            $c = trim($v['c']);
            $a = trim($v['a']);
            $p = trim($v['p']); // 参数

            if(!$m)continue;
            $authStr = $m;
            if($c){
                $authStr .= '/'.$c;
                if($a)$authStr .= '/'.$a;
            }
            if($p)$authStr .= '?'.$p;
            $returnData[] = $authStr;
        }
        return $returnData;
    }
}
