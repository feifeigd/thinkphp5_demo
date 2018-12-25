<?php

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application');

$tags = [];
$paths = [];

if($module_dir = opendir(APP_PATH)){
    while(false !== ($module_name = readdir($module_dir))){
        echo $module_name.'<br>';
        $module_path = APP_PATH . DIRECTORY_SEPARATOR . $module_name;   // 构建子目录
        if(is_dir($module_path)){
            $module = strtolower($module_name);
            $module_child_dir =opendir($module_path);
            while(false !== ($module_child_name = readdir($module_child_dir))){
                $module_child_path = $module_path.DIRECTORY_SEPARATOR.$module_child_name; 
                if(is_dir($module_child_path) && $module_child_name == 'controller'){
                    $controller_dir = opendir($module_child_path);
                    while(false !== ($controller_file = readdir($controller_dir))){
                        $controller_path = $module_child_path.DIRECTORY_SEPARATOR.$controller_file;
                        $controller_name = strtolower(basename($controller_path, '.php'));
                        echo "{$controller_name}<br>";
                        $contents = file_get_contents($controller_path);
                        if(preg_match_all('/swagger:\s*([^\n]+)/i', $contents, $swagger_matches)){
                            // 添加tag
                            $found_tag = false;
                            foreach($tags as $tag){
                                if($tag['name'] == $controller_name){
                                    $found_tag = true;
                                    break;
                                }
                            }
                            if(!$found_tag){
                                $tags[] = ['name'=>$controller_name, 'description'=>$swagger_matches[1][0]];
                            }
                            // 添加path
                            if(preg_match_all('/\/\*((?!\*\/).)+\*\//s', $contents, $func_matches)){
                                $length = count($func_matches[0]);
                                if($length > 1){
                                    for($i = 1; $i < $length; ++$i){
                                        $func_array = [];
                                        // 解析每个方法
                                        $func_contents = $func_matches[0][$i];
                                        echo "func_contents={$func_contents}<br>";
                                        // 方法说明
                                        if(!preg_match_all('/(get|post|delete)\s*:\s*([^\n]+)/i', $func_contents, $matches)){
                                            continue;
                                        }
                                        $method = $matches[1][0];
                                        $summary = $matches[2][0];
                                        echo "method={$method}, summary={$summary}<br>";
                                        if(!preg_match_all('/path\s*:\s*([^\n]+)/i', $func_contents, $matches)){
                                            continue;
                                        }
                                        $path = $matches[1][0];
                                        echo "path={$path}<br>";
                                        // 方法名称
                                        $operations = explode('/', $path);
                                        $operationId = preg_match_all('/method\s*:\s*([^\n]+)/i', $func_contents, $matches) ? $matches[1][0] : $operations[0];
                                        //$paths[$path] = [];
                                        $parameters = [];
                                        $func = [
                                            'tags'  => [$controller_name],
                                            'summary'   => $summary,
                                            'description'   => '',
                                            'operationId'   => $operationId,
                                            'produces'  => ['application/json'],
                                        ];
                                        // 参数
                                        $pattern  = '/param\s*:\s*(?<name>\w+)\s*-\s*\{(?<type>\w+(?<array>\[\])?)\}\s*(=\s*((\[(?<enum>[^]]+)\])|(?<default>[^\s]+))\s*)?(?<summary>[^*]+)/i';
                                        if(preg_match_all($pattern, $func_contents, $matches)){
                                            $names = $matches['name'];  // 参数名称
                                            $types = $matches['type'];  // 参数名称
                                            $enums = $matches['enum'];  // 参数枚举
                                            $defaults = $matches['default'];  // 默认值
                                            $summarys = $matches['summary'];  // 参数说明
                                            $arrays = $matches['array'];  // 参数说明

                                            $params_count = count($names);
                                            for($j = 0; $j < $params_count; ++$j){
                                                $in = $method == 'get' ? 'query' : 'formData';
                                                if(false !== strpos($path, '{'.$names[$j].'}')){
                                                    $in = 'path';
                                                }
                                                $parameter = [
                                                    'name'  => $names[$j],
                                                    'in'    => $in,
                                                    'required'  => true,
                                                    'description'   => $summarys[$j],
                                                ];
                                                if('' !== $defaults[$j]){
                                                    $parameter['required'] = false;
                                                    $parameter['defaultValue'] = $defaults[$j];
                                                }
                                                $type = str_replace('[]', '', $types[$j]);
                                                if('int' == $type)$type = 'integer';
                                                if('' != $arrays[$j]){
                                                    $parameter['type'] = 'array';
                                                    $parameter['items'] = ['type' => str_replace('[]', '', $type),];
                                                    $parameter['collectionFormat'] = 'brackets'; // url带中括号
                                                    //$parameter['collectionFormat'] = 'multi'; // url不带中括号
                                                }else if('' != $enums[$j]){ // 是否枚举参数
                                                    $enum = explode('|', $enums[$j]);
                                                    $parameter['type'] = $type;
                                                    $parameter['enum'] = $enum;
                                                }else{
                                                    $parameter['type'] = $type;
                                                }
                                                $parameters[] = $parameter;
                                            }
                                        }
                                        $func['parameters'] = $parameters;
                                        // 生成api访问路径
                                        $paths['/'.$module.'/'.$controller_name.'/'.$path][$method] = $func;
                                    }
                                }
                            }
                        }
                    }
                    closedir($controller_dir);
                }
            }
            closedir($module_child_dir);
        }        
    }
    closedir($module_dir);
}else{
    die('open dir failed:'.APP_PATH);
}

$swagger = [
    'swagger'   => '2.0',
    'info'  => [
        'description'   => 'APP 后台服务',
        'version'   => '1.0.0',
        'title' => ' [我的APP] Swagger',
        'termsOfService'    => 'http://www.d7kj.com',
        'contact'   => [
            'email' => 'feifeigd@21cn.com',
        ],
        'license'   => [
            'name'  => 'Apache 2.0',
            'url'   => 'http://www.apache.org/licenses/LICENSE-2.0.html',
        ],
    ],
    'host'  => $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'],
    'basePath'  => '',
    'tags'  => $tags,
    'schemes'   => [
        'http'
    ],
    'paths' => $paths,
    'securityDefinitions'   => [

    ],
    'definitions'   => [

    ],
    'externalDocs'  => [
        'description'   => 'Find out more about Swagger',
        'url'   => 'http://swagger.io',
    ],
];

$jsonFile = fopen("swagger/swagger.json", "w") or die("Unable to open file!");
fwrite($jsonFile, json_encode($swagger));
fclose($jsonFile);
//exit;
// 跳转到Swagger UI
$url = '/swagger/index.html';
Header('HTTP/1.1 303 See Other'); 
Header("Location: $url"); 
exit;
