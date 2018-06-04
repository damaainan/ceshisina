<?php 
/**
 * Created by Sublime.
 * Date: 2018-06-04 18:34:47
 * 根据文件夹的名称重命名文件夹内文件名
 */
/**
 * @param  string $dir 名录名
 * @param  array  $ext 后缀范围
 * @param  string  $flag 切断标识
 * @return void
 */
function renameFiles(string $dir, array $ext, string $flag){
    if(!is_dir($dir)){
        return false;
    }
    $dirArr = glob();
    // 扫描文件夹 
    foreach ($dirArr as $val) {
        if(is_dir($val)){
            // 设置切断标识 
            $dirs = explode($flag, $val);
            // 处理 获取 名称
            $dirname = $dirs[1];
            $files = glob($dir . '/' . $val);
            foreach ($files as $va) {
                $arr = explode('.', $va);
                if(in_array($arr[count[$arr] - 1], $ext){
                    // 在给定的后缀范围内 重命名
                    // 将获取的文件名加载名称之前 
                }
            }
        }
    }
    // 列出所有文件夹

    // 获得文件夹名
    // 获得文件夹下需要重命名的文件类型 文件名
    // 
}