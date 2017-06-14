<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\User;//引入模型
use Auth;//引入用户注册自动登陆


class UsersController extends Controller
{
  public function create()
  {
      return view('users.create');
  }

  public function show($id)
  {
      $user = User::findOrFail($id);
      return view('users.show', compact('user'));
  }

  public function store(Request $request)//这个是post请求认证数据
    {
      $this->validate($request, [  //这里将数据进行合法性认证
          'name' => 'required|max:50',
          'email' => 'required|email|unique:users|max:255',
          'password' => 'required|confirmed|min:6'
      ]);

      $user = User::create([//用户模型 User::create() 创建成功后会返回一个用户对象，并包含新注册用户的所有信息。我们将新注册用户的所有信息赋值给变量 $user，并通过路由跳转来进行数据绑定。
          'name' => $request->name,
          'email' => $request->email,
          'password' => bcrypt($request->password),
      ]);

      Auth::login($user);
      session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');//保存一段一次请求内的session
      return redirect()->route('users.show', [$user]);//后面那个是路由参数
    }
}
