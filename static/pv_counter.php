<?php include 'pv_counter.php'; ?>
<?php 
date_default_timezone_set('Asia/shanghai');

// 获取当前页面的 URL
$page_url = $_SERVER['REQUEST_URI'];

// 定义日志文件路径
$log_file = 'pv.log';

// 获取锁文件路径
$lock_file = $log_file . '.lock';

// 尝试获取锁
$lock_handle = fopen($lock_file, 'w');
if (flock($lock_handle, LOCK_EX)) {

    // 读取日志文件中的浏览量
    $views = [];
    if (file_exists($log_file)) {
        $views = json_decode(file_get_contents($log_file), true);
    }
    $page_views = isset($views[$page_url]) ? $views[$page_url] : 0;

    // 获取今天的日期
    $date = date('Y-m-d');

    // 如果今天的 PV 已经达到上限，不再增加浏览量
    $pv_limit = 999;
    $today_views = isset($views[$date]) ? array_sum($views[$date]) : 0;
    if ($today_views < $pv_limit) {
        $views['total'] = isset($views['total']) ? $views['total'] + 1 : 1;

        // 更新今天的 PV
        $views[$date] = isset($views[$date]) ? $views[$date] : [];
        $views[$date][$page_url] = isset($views[$date][$page_url]) ? $views[$date][$page_url] + 1 : 1;

        // 将 PV 写入日志文件
        file_put_contents($log_file, json_encode($views));
    }

    // 释放锁
    flock($lock_handle, LOCK_UN);
}

// 关闭锁文件句柄
fclose($lock_handle);

// 获取今天的 PV 数量和当前页面的 PV 数量
$today_views = isset($views[$date]) ? array_sum($views[$date]) : 0;
$page_views = isset($views[$date][$page_url]) ? $views[$date][$page_url] : 0;

// 输出当前页面的总浏览量和今天的浏览量
echo '总' . $views['total'] . '本' . $page_views . '今' . $today_views;
