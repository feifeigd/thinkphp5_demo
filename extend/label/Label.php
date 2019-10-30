<?php


namespace label;


class Label
{
    /// 替换字段模板中的标签
    /// @param string string 要替换的标签模板字符串
    /// @param field array 字段信息
    /// @param start string 标签开始字符串
    /// @param end string 标签结束字符串
    public function replace_field_label($string, $field, $start = '[', $end = ']'){
        $field = array_merge($field, $field['property']);
        return $this->replace_value($string, $field, $start, $end);
    }

    /// 把标签替换成对应的值
    /// @param string string 文件路径
    public function replace_value($string, $value, $start = '[', $end = ']'){
        $returnData = preg_replace_callback('/\[([A-Za-z0-9_]+)]/i', function ($m)use($value){
            extract($value);
            if(!trim($m[1]))return false;
            $var = false;
            eval("\$var=isset(\$".$m[1].") ? \$".$m[1]." : '".$m[1]."';");
            return $var;
        }, $string);
        return $returnData;
    }
}
