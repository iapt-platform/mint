<?php

namespace App\Http\Controllers;

class PageIndexController extends Controller
{
    public function index()
    {
        // 首页四个分流栏目：加栏目只需往 $cards 里追加一条，模板无需改动。
        $cards = [
            [
                'slug' => 'library',
                'eyebrow' => 'Library',
                'title' => '圣典文库',
                'lead' => '三藏 · 义注 · 复注 · 字典',
                'image' => 'library',
                'tint' => '#2E4A63',
                'align' => 'start',
                'href' => '/library',
                'cta' => '进入文库',
                'available' => true,
            ],
            [
                'slug' => 'course',
                'eyebrow' => 'Course',
                'title' => '次第课程',
                'lead' => '从零开始，逐部研读',
                'image' => 'course',
                'tint' => '#2A5257',
                'align' => 'end',
                'href' => '/library/course',
                'cta' => '开始学习',
                'available' => true,
            ],
            [
                'slug' => 'workspace',
                'eyebrow' => 'Workspace',
                'title' => '翻译工作台',
                'lead' => '术语、协作、校对、发布',
                'image' => 'workspace',
                'tint' => '#2C4A3C',
                'align' => 'start',
                'href' => '/pcd-v2026/workspace',
                'cta' => '进入工作台',
                'available' => true,
            ],
            [
                'slug' => 'development',
                'eyebrow' => 'Development',
                'title' => '二次开发',
                'lead' => 'API · MCP · GitHub',
                'image' => 'development',
                'tint' => '#4A4A48',
                'align' => 'end',
                'href' => null,
                'cta' => '即将上线',
                'available' => false,
            ],
        ];

        return view('pages.home', [
            'cards' => $cards,
        ]);
    }
}
