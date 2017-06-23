<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\User;//引入模型
use Auth;//引入用户注册自动登陆


class UsersController extends Controller
{

  public function __construct()
    {
      $this->middleware('auth', [//只有登陆的用户才能编辑资料  中间件的判断是  当为游客的时候
          'only' => ['edit', 'update','show','destroy']//只有登陆的用户才能进行用户的查看
      ]);

      $this->middleware('guest',[//已经注册的用户还能进行登陆和注册
          'only' => ['create']   //设置只有注册了的用户才能进行访问
      ]);

        // 我们在 __construct 方法中调用了middleware方法，该方法接收两个参数，第一个为中间件的名称，第二个为要进行过滤的动作。
        // 我们通过 only 方法来为 指定动作 使用 Auth 中间件进行过滤。
    }

    public function index()
    {
        $users = User::paginate(2);//分页
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
        //用于指定一些只允许未登录用户访问的动作，因此我们需要通过对 guest 属性进行设置，只让未登录用户访问登录页面和注册页面。
    }

  public function show($id)
  {
      $user = User::findOrFail($id);
      return view('users.show', compact('user'));//一个长度为1的数组  数组里面是个对象
                                                  //像这种路由都期待一个参数 来决定要显示谁的资料
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

    public function edit($id)
    {

        $user = User::findOrFail($id);
        $this->authorize('update', $user);//权限认证
        return view('users.edit', compact('user'));
    }

    public function update($id, Request $request)
   {

       $this->validate($request, [
           'name' => 'required|max:50',
           'password' => 'confirmed|min:6' //这里的话  是用_confirmation 来判断
       ]);

       $user = User::findOrFail($id);
       $this->authorize('update', $user);//权限认证

       $data = [];
       $data['name'] = $request->name;
       if ($request->password) {//当用户的密码不为空时候才更新
           $data['password'] = bcrypt($request->password);
       }

       $user->update($data);

       session()->flash('success', '个人资料更新成功！');



       return redirect()->route('users.show', $id);
   }


   public function destroy($id)
   {
       $user = User::findOrFail($id);
       $this->authorize('destroy', $user);//使用授权策略
       $user->delete();
       session()->flash('success', '成功删除用户！');
       return back();
   }
}
