<?php
header("Content-Type:text/html; charset=UTF-8");
header("Pragma: no-cache");
header("Cache-Control: no-store, no-cache, must-revalidate");
date_default_timezone_set('Asia/Seoul');
ini_set('allow_url_fopen', 'On');
ini_set('allow_url_include', 'On');
ini_set("display_errors", 1);
ini_set('memory_limit','-1');
error_reporting(E_ERROR | E_WARNING | E_PARSE);
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$docRoot = '/usr/share/nginx/html';
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
include_once $docRoot . '/pbg/dbInfo.php';
include_once $docRoot . '/pbg/lib.php';
foreach(glob($docRoot . '/pbg/function/*.php') as $functionFiles)
{
    include_once $functionFiles;
}
include_once $docRoot . '/pbg/config.php';
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
include_once $docRoot . '/pbg/crontab/cronLib.php';
include_once $docRoot . '/pbg/crontab/cronConfig.php';
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Executing list of Crontabs
$cronListDocRoot = $docRoot . '/pbg/crontab/cronList/';
//// 시즌 업데이트
include_once $cronListDocRoot . 'seasonUpdate.php';
//// 실시간 전적 알림
include_once $cronListDocRoot . 'realtime.php';
//// 페이지 좋아요 요청
include_once $cronListDocRoot . 'pageLike_alarm.php';
//// 공식 카페 게시글 알림
include_once $cronListDocRoot . 'official_cafe.php';