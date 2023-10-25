<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageIndexController extends Controller
{
    public function index(){
        $nav = [
                [
                    'title'=>'最新',
                    'link'=>'community',
                ],
                [
                    'title'=>'圣典',
                    'link'=>'palicanon',
                ],
                [
                    'title'=>'课程',
                    'link'=>'course',
                ],
                [
                    'title'=>'字典',
                    'link'=>'dict',
                ],
                [
                    'title'=>'文集',
                    'link'=>'dict',
                ],
            ];
        return view('solarize',['nav'=>$nav,'title' => '巴 利 圣 典 文 库','subtitle' => '巴利圣典翻译计划欢迎您的参与']);
    }
    
}
