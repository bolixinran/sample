<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;//引入用户信息验证类

class SessionsController extends Controller
{

  public function __construct()
    {
        $this->middleware('guest', [
            'only' => ['create']   //只有未登录状态才能进行访问
        ]);
    }

  public function create()
  {
      return view('sessions.create');
  }

  public function store(Request $request)
    {
       $this->validate($request, [
           'email' => 'required|email|max:255',
           'password' => 'required'
       ]);//验证信息合法性

       $credentials = [
           'email'    => $request->email,
           'password' => $request->password,
       ];

       if (Auth::attempt($credentials,$request->has('remember'))) {//接受一个数组
           // 登录成功后的相关操作
           session()->flash('success', '欢迎回来！');//Auth::user()  这玩意是个对象
           return redirect()->intended(route('users.show', [Auth::user()]));//[Auth::user()]获取当前用户的信息 如果是数组的话 就自动判断传入的参数
           //intended重定向到上次尝试访问的页面   没有的话 就访问默认页面
       } else {
           // 登录失败后的相关操作
           session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
           return redirect()->back();
       }

       return;
    }

    public function destroy()
    {
        Auth::logout();
        session()->flash('success', '您已成功退出！');
        return redirect('login');
    }
}
