<?php 
/**
 * 网上虽然搜 PHP CUP 内存 使用率 这一类关键字出来的文章一大把，但大都是在Linux环境下，windows下只有提出靠cmd语句实现的观点，但少有实际直接可行的代码，辗转试了一遍 wmic , systeminfo 各种并不熟稔的cmd命令，并没有找到可行的方案，最终通过百度知道 的一篇答案找到利用vbs获取的实现方式，稍加改善，可以在windows环境下直接使用。
 */
class SystemInfoWindows
{
  /**
   * 判断指定路径下指定文件是否存在，如不存在则创建
   * @param string $fileName 文件名
   * @param string $content  文件内容
   * @return string 返回文件路径
   */
  private function getFilePath($fileName, $content)
  {
    $path = dirname(__FILE__) . "\\$fileName";
    if (!file_exists($path)) {
      file_put_contents($path, $content);
    }
    return $path;
  }

  /**
   * 获得cpu使用率vbs文件生成函数
   * @return string 返回vbs文件路径
   */
  private function getCupUsageVbsPath()
  {
    return $this->getFilePath(
      'cpu_usage.vbs',
      "On Error Resume Next
       Set objProc = GetObject(\"winmgmts:\\\\.\\root\cimv2:win32_processor='cpu0'\")
       WScript.Echo(objProc.LoadPercentage)"
    );
  }

  /**
   * 获得总内存及可用物理内存JSON vbs文件生成函数
   * @return string 返回vbs文件路径
   */
  private function getMemoryUsageVbsPath()
  {
    return $this->getFilePath(
      'memory_usage.vbs',
      "On Error Resume Next
       Set objWMI = GetObject(\"winmgmts:\\\\.\\root\cimv2\")
       Set colOS = objWMI.InstancesOf(\"Win32_OperatingSystem\")
       For Each objOS in colOS
         Wscript.Echo(\"{\"\"TotalVisibleMemorySize\"\":\" & objOS.TotalVisibleMemorySize & \",\"\"FreePhysicalMemory\"\":\" & objOS.FreePhysicalMemory & \"}\")
       Next"
    );
  }

  /**
   * 获得CPU使用率
   * @return Number
   */
  public function getCpuUsage()
  {
    $path = $this->getCupUsageVbsPath();
    exec("cscript -nologo $path", $usage);
    return $usage[0];
  }

  /**
   * 获得内存使用率数组
   * @return array
   */
  public function getMemoryUsage()
  {
    $path = $this->getMemoryUsageVbsPath();
    exec("cscript -nologo $path", $usage);
    $memory = json_decode($usage[0], true);
    $memory['usage'] = Round((($memory['TotalVisibleMemorySize'] - $memory['FreePhysicalMemory']) / $memory['TotalVisibleMemorySize']) * 100);
    return $memory;
  }
}
// 调用方式
$info = new SystemInfoWindows();
$cpu = $info->getCpuUsage();
$memory = $info->getMemoryUsage();

echo "当前系统CPU使用率：{$cpu}%，内存使用率{$memory['usage']}%";