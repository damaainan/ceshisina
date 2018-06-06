<?php 
declare(strict_types=1);
/**
 * Created by Sublime.
 * Date: 2018-06-04 18:34:47
 * 根据文件夹的名称重命名文件夹内文件名
 */
/**
 * @param  string $dir 名录名
 * @param  array  $ext 后缀范围
 * @param  array  $flag 切断标识
 * @return void
 */
function renameFiles(string $dir, array $ext, array $flag): void{
    if(!is_dir($dir)){
        echo "非文件夹";
        die;
    }
    $dirArr = scandir($dir);
    // 扫描文件夹 
    foreach ($dirArr as $val) {
        if(is_dir($val)){
            // 设置切断标识 
            // $dirs = explode($flag, $val);// 或者字符串替换 循环替换 
            $dirname = $val;
            foreach ($flag as $fl) {
                $val = str_replace($fl, '', $val); // 有数字 用正则 
                // $val = preg_replace($fl, '', $val); // 有数字 用正则 
            }
            // 处理 获取 名称
            // $dirname = $dirs[1];
            $nameflag = $val; // 要在文件名前加的前缀
            $files = scandir($dir . $dirname);
            foreach ($files as $va) {
                $arr = explode('.', $va);
                $len = count($arr);
                if(in_array($arr[$len - 1], $ext)){
                    $oldname = $va;
                    if(strpos($oldname, $nameflag) !== false){
                        continue; // 已改名的跳过
                    }
                    // 在给定的后缀范围内 重命名
                    // 文件名 循环替换标识
                    foreach ($flag as $fl) {
                        $va = str_replace($fl, '', $va); // 有数字 用正则 
                    }
                    // 然后将获取的文件名加载名称之前 
                    $newname = $nameflag . $va;
                    // 重命名文件 
                    rename($dir  . $dirname . '/' . $oldname, $dir . $dirname . '/' . $newname);
                }
            }
        }
    }
    // 列出所有文件夹

    // 获得文件夹名
    // 获得文件夹下需要重命名的文件类型 文件名
    // 
}

renameFiles('./',['txt'],['111']);
// renameFiles('./',['txt'],['/[abcABC]-\d{1,2}/']);