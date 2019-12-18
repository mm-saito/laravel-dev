<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceRecord extends Model
{
    // テーブル名
    protected $table = 'attendance_records';

    // 複数代入させない属性　主キー
    protected $guarded = array('id');

    // 複数代入する属性
    // Factoryでinsertを許可するカラム
    protected $fillable = [];

    // 自動でタイムスタンプ付与
    public $timestamps = true;

    Public function user()
    {
      return $this->belongsTo('App\User');
    }

    /**
     * 年でフィルタリング
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $year
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeYearFilter($query, ?string $year)
    {
        if (!is_null($year)) {
        return $query->where('year', $year);
        }
        return $query;
    }

    /**
     * 月でフィルタリング
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $month
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMonthFilter($query, ?string $month)
    {
        if (!is_null($month)) {
            return $query->where('month', $month);
        }
        return $query;
    }

    /**
     * 日でフィルタリング
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $day
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDayFilter($query, ?string $day)
    {
        if (!is_null($day)) {
            return $query->where('day', $day);
        }
        return $query;
    }

    /**
     * カレンダー表示用配列を作成する
     * @param array  $record
     * @return array $calendar
     */
    public static function makeCalendar($records, $year, $month)
    {
        // 日本語設定にする
        setlocale(LC_ALL, 'ja_JP.UTF-8');
        // 月初を取得
        $firstOfMonth = Carbon::parse($year. '-'. $month)->firstOfMonth();
        // 月末を取得
        $endOfMonth = $firstOfMonth->copy()->endOfMonth();
        
        // ループで1か月分を取得
        for ($i = 0; true; $i++) {

            if ($i === 0) {
                // 1日
                $date = $firstOfMonth;
            } else {
                // 2日以降
                $date = $firstOfMonth->addDays();
            }
            // 月末を超えたら終了
            if ($date > $endOfMonth) {
                break;
            }

            // カレンダーデータを作成
            $id = 0;
            $date = $date->format('Y-m-d');
            $day = Carbon::parse($date)->formatLocalized('%d');
            $week = Carbon::parse($date)->formatLocalized('%a');
            $start_time = '';
            $break_time = '';
            $end_time = '';
            $actual = '';

            // 勤怠がある日なら時間を上書き
            foreach ($records as $key => $record) {
                // その日に勤怠があれば時間を上書き
                if ($date === $record->date) {
                    $id = $record->id;
                    $start_time = $record->start_time;
                    $break_time = $record->break_time;
                    $end_time = $record->end_time;
                    $actual = $record->actual;
                }
            }
            $calendar[] = compact('id', 'day', 'week', 'start_time', 'end_time', 'actual');
        }
        return $calendar;
    }

    /**
     * 合計を作成する
     * @param array  $record
     * @return array $calendar
     */
    public static function makeTotal($records)
    {
        $total = 0;
        foreach ($records as $key => $record) {
                $total += $record->actual; 
        }

        return $total;
    }

    /**
     * 次月/前月を作成する
     * @param array  $record
     * @return array $calendar
     */
    public static function makePrevAndNext($year, $month)
    {
        $prev = Carbon::createMidnightDate($year, $month, 1)->subMonth();
        $next = Carbon::createMidnightDate($year, $month, 1)->addMonth();
        $prev_date['year']  = $prev->year;   // 前月 年
        $prev_date['month'] = $prev->month;  // 前月 月
        $next_date['year']  = $next->year;   // 次月 年
        $next_date['month'] = $next->month;  // 次月 月
        return [$prev_date, $next_date];
    }
}