<?php

namespace App;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    // テーブル名
    protected $table = 'users';

    // IDが自動増分されない場合
    // public $incrementing = false;

    // 主キーが整数でない場合
    // protected $keyType = 'string';

    // 複数代入させない属性 主キー
    protected $guarded = array('id');

    // 自動でタイムスタンプ付与
    public $timestamps = true;

    // 複数代入する属性
    // Factoryでinsertを許可するカラム
    protected $fillable = ['email', 'password', 'dept_id', 'job_id', 'is_admin'];

    // deptモデルが親
    public function dept()
    {
        return $this->belongsTo('App\Dept');
    }
    // jobモデルが親
    public function job()
    {
        return $this->belongsTo('App\Job');
    }

    // 1対1
    public function profile()
    {
        return $this->hasOne('App\Profile');
    }

    // 1対多
    public function AttendanceRecord()
    {
        return $this->hasMany('App\AttendanceRecord');
    }

    // 1対多
    public function PaidHoliday()
    {
        return $this->hasMany('App\PaidHoliday');
    }
    // 多対多
    public function Group()
    {
        return $this->belongsToMany('App\Group');
    }

    public function getJWTIdentifier()
    {
        // JWT トークンに保存する ID を返す
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        // JWT トークンに埋め込む追加の情報を返す
        return [];
    }

    // protected $hidden = [
    //     'password', 'remember_token',
    // ];

    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];

    /* セレクトボックス作成用
     * @param $model_name   モデル名 ex:'App\User'
     * @param $key_id   idにしたい項目 デフォルトは'id'
     */
    public static function getArraySelectBox($model_name, $key_id = 'id')
    {
        $arry = $model_name::get([$key_id, 'name'])->toArray();
        // formファザード用に配列を整形
        foreach ($arry as $key => $val) {
          $res[$val[$key_id]] = $val['name'];
        }
        return $res;
    }

    /**
     * 部署でフィルタリング
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $dept_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDeptFilter($query, ?string $dept_id)
    {
      if (!is_null($dept_id)) {
        return $query->where('dept_id', $dept_id);
      }
      return $query;
    }

    /**
     * 職種でフィルタリング
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $job_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJobFilter($query, ?string $job_id)
    {
      if (!is_null($job_id)) {
        return $query->where('job_id', $job_id);
      }
      return $query;
    }
    /**
     * 検索ワードでフィルタリング
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $job_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchFilter($query, ?string $keyword)
    {
      if (!is_null($keyword)) {
        return $query->join('profiles', 'users.id', '=', 'profiles.user_id')
                     ->where('email','like','%'.$keyword.'%')
                     ->orWhere('profiles.name','like','%'.$keyword.'%');
      }
      return $query;
    }

    /**
     * パラメータにuser_idがある場合かつ、カレントユーザがadminの場合、
     * 自分以外のユーザ情報を返却する
     * それ以外(通常)の場合、カレントユーザ情報を返却する
     * @param array  $request
     * @return array $user
     */
    public static function getUserOrNonSelf($request, $type = 'get')
    {
        $user = Auth::user();
        // getの場合
        if ($type === 'get') {
          // getパラメータにuser_idがない場合
          if (empty($request->input('user_id'))) return [$user, false];
          
          // adminユーザでない場合
          if ($user->is_admin !== 1) return [$user, false];
          
          $user = User::find($request->user_id);
          return [$user, true];

        // postの場合
        } else {
          // getパラメータにuser_idがない場合
          if (empty($request->user_id)) return [$user, false];
          
          // adminユーザでない場合
          if ($user->is_admin !== 1) return [$user, false];
          
          $user = User::find($request->user_id);
          return [$user, true];
        }
    }
}
