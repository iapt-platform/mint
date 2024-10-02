<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageIndexController extends Controller
{
    public function index(){
        $nav = [
                [
                    'title'=>'最新',
                    'id'=>'new',
                    'link'=>config('mint.server.dashboard_base_path').'/community/list',
                ],
                [
                    'title'=>'圣典',
                    'id'=>'pali',
                    'link'=>config('mint.server.dashboard_base_path').'/palicanon/list',
                ],
                [
                    'title'=>'课程',
                    'id'=>'course',
                    'link'=>config('mint.server.dashboard_base_path').'/course/list',
                ],
                [
                    'title'=>'字典',
                    'id'=>'dict',
                    'link'=>config('mint.server.dashboard_base_path').'/dict/recent',
                ],
                [
                    'title'=>'文集',
                    'id'=>'anthology',
                    'link'=>config('mint.server.dashboard_base_path').'/anthology/list',
                ],
                [
                    'title'=>'注册/登录',
                    'id'=>'sign_in',
                    'link'=>config('mint.server.dashboard_base_path').'/anonymous/users/sign-in',
                ],
            ];
        $wish = [
            [
                'title'=>'翻译一套三藏',
                'description'=>'我们希望把完整的巴利三藏、义注、复注、nissaya都翻译成为中文。',
            ],
            [
                'title'=>'整理一本词典',
                'description'=>'我们希望借助沉淀下来的数据，整理一套完整的巴中字典。这项工作将在整个三藏翻译的过程中逐渐完成。',
            ],
            [
                'title'=>'开发一个平台',
                'description'=>'我们会持续开发和维护wikipali平台，并不断发展新的功能，令其越来越方便与巴利翻译和研究。',
            ],
        ];

        $Gallery = [
            [
                'image'=>'/assets/gallery/02.jpg',
                'title'=>'云台翻译中心',
                'id'=>'desc_01',
                'description'=>'远眺翻译中心',
            ],
            [
                'image'=>'/assets/gallery/01.jpg',
                'title'=>'翻译人才培养',
                'id'=>'desc_02',
                'description'=>'翻译中还不断地培养翻译人才，加入到翻译工作中。',
            ],
            [
                'image'=>'/assets/gallery/03.jpg',
                'title'=>'云台翻译中心',
                'id'=>'desc_03',
                'description'=>'外景',
            ],
            [
                'image'=>'/assets/gallery/04.jpg',
                'title'=>'翻译中心接待室',
                'id'=>'desc_04',
                'description'=>'人工湖畔的接待室，访客活动中心',
            ],
            [
                'image'=>'/assets/gallery/05.jpg',
                'title'=>'办公环境',
                'id'=>'desc_05',
                'description'=>'27寸竖屏保证翻译的效率和质量，站坐交替的升降台，保证翻译者的健康。',
            ],
        ];
        return view('typhoon',[
            'nav'=>$nav,
            'title' => '巴 利 圣 典 文 库',
            'subtitle' => '巴利圣典翻译计划欢迎您的参与',
            'description' => '巴利语学习与翻译工具',
            'gallery' => $Gallery,
            'api' => config('app.url').'/api/v2',
        ]);
    }

}
