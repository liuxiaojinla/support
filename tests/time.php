<?php

use Xin\Support\Time;

require_once '../vendor/autoload.php';

var_dump(Time::parse('2021-01-01 00:00:00'));
var_dump("当前时间：" . Time::date()->format('Y-m-d H:i:s'));
var_dump("当前时间：" . Time::date(Time::nowTimestamp())->format('Y-m-d H:i:s'));
var_dump("当前时间：" . Time::date(Time::date())->format('Y-m-d H:i:s'));
var_dump("当前时间：" . Time::now()->format('Y-m-d H:i:s.u'));
var_dump("当前时间（毫秒）：" . Time::milliseconds());
var_dump("当前时间（微秒）：" . Time::microseconds());

var_dump("添加秒：" . Time::addSeconds(null, Time::daysToSeconds())->format('Y-m-d H:i:s'));
var_dump("减少秒：" . Time::subSeconds(null, Time::daysToSeconds())->format('Y-m-d H:i:s'));

var_dump("截止到分钟：" . Time::minuteDate()->format('Y-m-d H:i:s'));
var_dump("截止到分钟：" . Time::minute(null, 15));
var_dump("截止到分钟（开始）：" . Time::startOfMinute());
var_dump("截止到分钟（结束）：" . Time::endOfMinute());
var_dump("截止到分钟（开始）：" . Time::date(Time::startOfMinute())->format('Y-m-d H:i:s'));
var_dump("截止到分钟（结束）：" . Time::date(Time::endOfMinute())->format('Y-m-d H:i:s'));
var_dump("截止到分钟区间：", Time::minuteRange(null, true));
var_dump("添加分钟：" . Time::addMinutes(Time::date()->getTimestamp(), 15)->format('Y-m-d H:i:s'));
var_dump("减少分钟：" . Time::subMinutes(Time::date(), 15)->format('Y-m-d H:i:s'));

var_dump("截止到小时：" . Time::hourDate()->format('Y-m-d H:i:s'));
var_dump("截止到小时：" . Time::hour());
var_dump("截止到小时（开始）：" . Time::startOfHour());
var_dump("截止到小时（结束）：" . Time::endOfHour());
var_dump("截止到小时（开始）：" . Time::date(Time::startOfHour())->format('Y-m-d H:i:s'));
var_dump("截止到小时（结束）：" . Time::date(Time::endOfHour())->format('Y-m-d H:i:s'));
var_dump("截止到小时区间：", Time::hourRange(null, true));
var_dump("添加小时：" . Time::addHours(Time::date()->getTimestamp(), 15)->format('Y-m-d H:i:s'));
var_dump("减少小时：" . Time::subHours(Time::date(), 15)->format('Y-m-d H:i:s'));

var_dump("今天：" . Time::today());
var_dump("今天：" . Time::todayDate()->format('Y-m-d H:i:s'));
var_dump("今天：" . Time::endOfTodayDate()->format('Y-m-d H:i:s'));
var_dump("今天：", Time::todayRange(null, true));

var_dump("昨天：" . Time::yesterday());
var_dump("昨天：" . Time::yesterdayDate()->format('Y-m-d H:i:s'));
var_dump("昨天：" . Time::endOfYesterdayDate()->format('Y-m-d H:i:s'));
var_dump("昨天：", Time::yesterdayRange(null, true));

var_dump(Time::date(Time::daysAgo(100, true))->format('Y-m-d H:i:s'));
var_dump(Time::dayAgoRange(100, true, true));
var_dump(Time::dayAgoRange(100, true, false));
var_dump(Time::dayRange(0, 10, true));
var_dump(Time::dayRange(0, 0));
var_dump("添加天：" . Time::addDays(Time::date()->getTimestamp(), 15)->format('Y-m-d H:i:s'));
var_dump("减少天：" . Time::subDays(Time::date(), 15)->format('Y-m-d H:i:s'));

var_dump("本周：" . Time::week());
var_dump("本周（开始）：" . Time::weekDate(null, 12)->format('Y-m-d H:i:s'));
var_dump("本周（结束）：" . Time::endOfWeekDate(null, 12)->format('Y-m-d H:i:s'));
var_dump("本周（开始）：" . Time::startOfWeek());
var_dump("本周（结束）：" . Time::endOfWeek());
var_dump("本周区间：", Time::weekRange(null, true));

var_dump("上周：" . Time::lastWeek());
var_dump("上周（开始）：" . Time::lastWeekDate()->format('Y-m-d H:i:s'));
var_dump("上周（结束）：" . Time::endOfLastWeekDate()->format('Y-m-d H:i:s'));
var_dump("上周（开始）：" . Time::startOfLastWeek());
var_dump("上周（结束）：" . Time::endOfLastWeek());
var_dump("上周：", Time::lastWeekRange(null, true));

var_dump("添加周：" . Time::addWeeks(Time::date()->getTimestamp(), 1, true)->format('Y-m-d H:i:s'));
var_dump("减少周：" . Time::subWeeks(Time::date(), 1, true)->format('Y-m-d H:i:s'));

var_dump("本月：" . Time::month());
var_dump("本月（开始）：" . Time::monthDate()->format('Y-m-d H:i:s'));
var_dump("本月（结束）：" . Time::endOfMonthDate()->format('Y-m-d H:i:s'));
var_dump("本月（开始）：" . Time::startOfMonth());
var_dump("本月（结束）：" . Time::endOfMonth());
var_dump("本月区间：", Time::monthRange(null, true));

var_dump("上月：" . Time::lastMonth());
var_dump("上月（开始）：" . Time::lastMonthDate()->format('Y-m-d H:i:s'));
var_dump("上月（结束）：" . Time::endOfLastMonthDate()->format('Y-m-d H:i:s'));
var_dump("上月（开始）：" . Time::startOfLastMonth());
var_dump("上月（结束）：" . Time::endOfLastMonth());
var_dump("上月区间：", Time::lastMonthRange(null, true));

var_dump("添加月：" . Time::addMonths(Time::date(), 1, true)->format('Y-m-d H:i:s'));
var_dump("减少月：" . Time::subMonths(Time::date(), 1, true)->format('Y-m-d H:i:s'));

var_dump("本年：" . Time::year());
var_dump("本年（开始）：" . Time::yearDate()->format('Y-m-d H:i:s'));
var_dump("本年（结束）：" . Time::endOfYearDate()->format('Y-m-d H:i:s'));
var_dump("本年（开始）：" . Time::startOfYear());
var_dump("本年（结束）：" . Time::endOfYear());
var_dump("本年区间：", Time::yearRange(null, true));

var_dump("去年：" . Time::lastYear());
var_dump("去年（开始）：" . Time::lastYearDate()->format('Y-m-d H:i:s'));
var_dump("去年（结束）：" . Time::endOfLastYearDate()->format('Y-m-d H:i:s'));
var_dump("去年（开始）：" . Time::startOfLastYear());
var_dump("去年（结束）：" . Time::endOfLastYear());
var_dump("去年区间：", Time::lastYearRange(null, true));

var_dump("添加年：" . Time::addYears(Time::date(), 1, true)->format('Y-m-d H:i:s'));
var_dump("减少年：" . Time::subYears(Time::date(), 1, true)->format('Y-m-d H:i:s'));

var_dump('是否为闰年：' . (Time::isLeapYear() ? '是' : '否'));

var_dump("当前秒：" . Time::currentSecond());
var_dump("当前分钟：" . Time::currentMinute());
var_dump("当前小时：" . Time::currentHour());
var_dump("今天是星期" . Time::dayOfWeek());
var_dump("今天是星期" . Time::dayOfWeekName());
var_dump("今天是本月的第" . Time::dayOfMonth() . "天");
var_dump("今天是本年的第" . Time::dayOfYear() . "天");
var_dump("本月是本年的第" . Time::currentMonth() . "月");
var_dump("今年是" . Time::currentYear() . "年");
var_dump("本月天数：" . Time::dayCountInMonth());
var_dump("本年的天数：" . Time::dayCountInYear());

var_dump(Time::date('-1days'));
