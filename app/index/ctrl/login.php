<?php
/**
 *============================
 * author:Farmer
 * time:2017/6/4 22:39
 * blog:blog.icodef.com
 * function:
 *============================
 */

namespace app\index\ctrl;

class login {
    function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $json = ['code' => -1, 'msg' => '系统错误'];
            $ret = isExist($_POST, [
                'user' => ['regex' => ['/^[\x{4e00}-\x{9fa5}\w\@\.]{2,}$/u', '用户名不符合规则'], 'msg' => '请输入用户名', 'sql' => 'user'],//中文匹配头疼
                'pwd' => ['regex' => ['/^[\\~!@#$%^&*()-_=+|{}\[\], .?\/:;\'\"\d\w]{6,16}$/', '密码不符合规范'], 'msg' => '请输入密码', 'sql' => 'password'],
            ], $data);
            if ($ret === true) {
                if ($userMsg = getUser($_POST['user'])) {
                    setcookie('token',getToken($userMsg['uid']),time()+86400,'/');
                    setcookie('uid',$userMsg['uid'],time()+86400,'/');
                    $json['code']=0;
                    $json['msg']='登陆成功';
                } else {
                    $json['msg'] = '账号不存在';
                }
            } else {
                $json['msg'] = $ret;
            }
            return json($json);
        } else {
            V()->assign('title', '登陆页面');
            V()->display();
        }
    }

    function register() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $json = ['code' => -1, 'msg' => '系统错误'];
            $ret = isExist($_POST, [
                'user' => [ 'func' => ['isUser'],'regex' => ['/^[\w]{2,10}$/', '用户名不符合规则'], 'msg' => '请输入用户名', 'sql' => 'user'],//中文匹配头疼
                'pwd' => ['regex' => ['/^[\\~!@#$%^&*()-_=+|{}\[\], .?\/:;\'\"\d\w]{6,16}$/', '密码不符合规范'], 'msg' => '请输入密码', 'sql' => 'password'],
                'email' => [ 'func' => ['isEmail'],'regex' => ['/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', '邮箱不符合规则', 'msg' => '请输入邮箱'], 'sql' => 'email'],
                'inv_code' => ['regex' => ['/^[0-9a-z]{6}$/', '邀请码不符合规则'], '请输入验证码', 'func' => ['isInvCode']]
            ], $data);
            if ($ret === true) {
                $json['code'] = 0;
                $json['msg'] = '注册成功';
                $data['reg_time'] = time();
                DB('user')->insert($data);
                DB('inv_code')->update(['inv_use_uid'=>DB()->lastinsertid(),'inv_use_time'=>time()],['inv_code'=>$_POST['inv_code']]);
                DB(':radusergroup')->insert(['username'=>$data['user'],'groupname'=>'VIP0']);
                DB(':radcheck')->insert(['username'=>$data['user'],'attribute'=>'Cleartext-Password','op'=>'==','value'=>$data['password']]);
            } else {
                $json['msg'] = $ret;
            }
            return json($json);
        } else {
            V()->assign('title', '注册页面');
            V()->display();
        }
    }
}