<?php
// sem key
$sem_key = ftok(__FILE__, 'b');
$sem_id  = sem_get($sem_key);
// shm key
$shm_key      = ftok(__FILE__, 'm');
$shm_id       = shm_attach($shm_key, 1024, 0666);
const SHM_VAR = 1;
$child_pid    = [];
// fork 2 child process
for ($i = 1; $i <= 2; $i++) {
    $pid = pcntl_fork();
    if ($pid < 0) {
        exit();
    } else if (0 == $pid) {
        // 获取锁
        sem_acquire($sem_id);
        if (shm_has_var($shm_id, SHM_VAR)) {
            $counter = shm_get_var($shm_id, SHM_VAR);
            $counter += 1;
            shm_put_var($shm_id, SHM_VAR, $counter);
        } else {
            $counter = 1;
            shm_put_var($shm_id, SHM_VAR, $counter);
        }
        // 释放锁，一定要记得释放，不然就一直会被阻锁死
        sem_release($sem_id);
        exit;
    } else if ($pid > 0) {
        $child_pid[] = $pid;
    }
}
while (!empty($child_pid)) {
    foreach ($child_pid as $pid_key => $pid_item) {
        pcntl_waitpid($pid_item, $status, WNOHANG);
        unset($child_pid[$pid_key]);
    }
}
// 休眠2秒钟，2个子进程都执行完毕了
sleep(2);
echo '最终结果' . shm_get_var($shm_id, SHM_VAR) . PHP_EOL;
// 记得删除共享内存数据，删除共享内存是有顺序的，先remove后detach，顺序反过来php可能会报错
shm_remove($shm_id);
shm_detach($shm_id);
