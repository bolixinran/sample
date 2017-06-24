<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;


use Auth;
class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];


    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->activation_token = str_random(30);
        });
    }

    public function gravatar($size = '100')
   {
       $hash = md5(strtolower(trim($this->attributes['email'])));
       return "http://www.gravatar.com/avatar/$hash?s=$size";
   }

   public function statuses()//指明一个用户拥有多条微博
    {
        return $this->hasMany(Status::class);
    }

   public function store(Request $request)
    {
        $this->validate($request, [//为什么前面已经验证了一次合法性为什么这里还再验证一次呢
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required'
        ]);
        return;
    }


    public function feed()
    {
        $user_ids = Auth::user()->followings->pluck('id')->toArray();
        array_push($user_ids, Auth::user()->id);
        return Status::whereIn('user_id', $user_ids)//拿到关注的用户发布的数据
                              ->with('user')
                              ->orderBy('created_at', 'desc');
    }



    public function followers()
    {
        return $this->belongsToMany(User::Class, 'followers', 'user_id', 'follower_id');
    }
    //我们可以通过 followers 来获取粉丝关系列表    $user->followers();

    public function followings()
    {
        return $this->belongsToMany(User::Class, 'followers', 'follower_id', 'user_id');
    }

    //通过 followings 来获取用户关注人列表，如：   $user->followings();


    public function follow($user_ids)//关注逻辑
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids, false);
    }

    public function unfollow($user_ids)//取消关注逻辑
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);//这里也是接收一个数组参数
    }


    public function isFollowing($user_id)//判断是否关注了某用户
    {
        return $this->followings->contains($user_id);//这个属性怎么来的呢
    }
}
