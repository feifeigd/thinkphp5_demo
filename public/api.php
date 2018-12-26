<?php

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application');

// 处理单个函数
function handleFunc($controller_name, $func_contents, &$method, &$path){
  echo "func_contents={$func_contents}<br>";
  // 方法说明
  if(!preg_match_all('/(get|post|delete)\s*:\s*([^\n]+)/i', $func_contents, $matches)){
    return false;
  }
  $method = $matches[1][0];
  $summary = $matches[2][0];
  echo "method={$method}, summary={$summary}<br>";
  if(!preg_match_all('/path\s*:\s*([^\n]+)/i', $func_contents, $matches)){
    return false;
  }
  $path = $matches[1][0];
  echo "path={$path}<br>";
  // 方法名称
  $operations = explode('/', $path);
  $operationId = preg_match_all('/method\s*:\s*([^\n]+)/i', $func_contents, $matches) ? $matches[1][0] : $operations[0];
  //$paths[$path] = [];
  $func = [
    'tags'  => [$controller_name],
    'summary'   => $summary,
    'description'   => '',
    'operationId'   => $operationId,
    'produces'  => ['application/json'],
    // 参数
    'parameters' => handleParams($method, $path, $func_contents),
  ];
  return $func;
}

// 解析所有参数
function handleParams($method, $path, $func_contents){
  $parameters = [];
  $pattern  = '/param\s+\$(?<name>\w+)\s*-\s*\{(?<type>\w+(?<array>\[\])?)\}\s*(=\s*((\[(?<enum>[^]]+)\])|(?<default>[^\s]+))\s*)?(?<summary>[^*]+)/i';
  if(preg_match_all($pattern, $func_contents, $matches)){
    $names = $matches['name'];  // 参数名称
    $types = $matches['type'];  // 参数名称
    $enums = $matches['enum'];  // 参数枚举
    $defaults = $matches['default'];  // 默认值
    $summarys = $matches['summary'];  // 参数说明
    $arrays = $matches['array'];  // 参数说明

    $params_count = count($names);
    for($j = 0; $j < $params_count; ++$j){
      $parameters[] = handleParam($method, $path, $names[$j], $types[$j], $arrays[$j], $enums[$j], $defaults[$j], $summarys[$j]);
    }
  }
  return $parameters;
}

// 解析 param 标记
function handleParam($method, $path, $name, $type, $array, $enum, $default, $summary){
  $in = $method == 'get' ? 'query' : 'formData';
  if(false !== strpos($path, '{'.$name.'}')){
    $in = 'path';
  }
  $parameter = [
    'name'  => $name,
    'in'    => $in,
    'required'  => true,
    'description'   => $summary,
  ];
  if('' !== $default){
    $parameter['required'] = false;
    $parameter['defaultValue'] = $default;
  }
  $type = str_replace('[]', '', $type);
  if('int' == $type)$type = 'integer';
  if('' != $array){
    $parameter['type'] = 'array';
    $parameter['items'] = ['type' => str_replace('[]', '', $type),];
    $parameter['collectionFormat'] = 'brackets'; // url带中括号
    //$parameter['collectionFormat'] = 'multi'; // url不带中括号
  }else if('' != $enum){ // 是否枚举参数
    $enum = explode('|', $enum);
    $parameter['type'] = $type;
    $parameter['enum'] = $enum;
  }else{
    $parameter['type'] = $type;
  }
  return $parameter;
}

$tags = [];
$paths = [];

function addTag($controller_name, $description){
  global $tags;
  $found_tag = false;
  foreach($tags as $tag){
    if($tag['name'] == $controller_name){
      $found_tag = true;
      break;
    }
  }
  if(!$found_tag){
    $tags[] = ['name'=>$controller_name, 'description'=>$description];
  }
}

function addPath($module, $controller_name, $contents){
  if(preg_match_all('/\/\*((?!\*\/).)+\*\//s', $contents, $func_matches)){
    global $paths;
    $length = count($func_matches[0]);
    for($i = 1; $i < $length; ++$i){  // $length > 1
      // 解析每个方法
      $func_contents = $func_matches[0][$i];
      $method = ''; $path = '';
      if(false !== ($func = handleFunc($controller_name, $func_contents, $method, $path)))
        // 生成api访问路径
        $paths['/'.$module.'/'.$controller_name.'/'.$path][$method] = $func;
    }
  }
}

if($module_dir = opendir(APP_PATH)){
    while(false !== ($module_name = readdir($module_dir))){ // 一个module
        echo $module_name.'<br>';
        $module_path = APP_PATH . DIRECTORY_SEPARATOR . $module_name;   // 构建子目录
        if(is_dir($module_path)){
            $module = strtolower($module_name);
            $module_child_dir =opendir($module_path);
            while(false !== ($module_child_name = readdir($module_child_dir))){
                $module_child_path = $module_path.DIRECTORY_SEPARATOR.$module_child_name; 
                if(is_dir($module_child_path) && $module_child_name == 'controller'){ // 只变量controller目录
                    $controller_dir = opendir($module_child_path);
                    while(false !== ($controller_file = readdir($controller_dir))){// 一个controller文件
                        $controller_path = $module_child_path.DIRECTORY_SEPARATOR.$controller_file;
                        $controller_name = strtolower(basename($controller_path, '.php'));
                        echo "{$controller_name}<br>";
                        $contents = file_get_contents($controller_path);  // 控制器代码
                        if(preg_match_all('/swagger:\s*([^\n]+)/i', $contents, $swagger_matches)){
                            // 添加tag
                            addTag($controller_name, $swagger_matches[1][0]);
                            // 添加path
                            addPath($module, $controller_name, $contents);
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
