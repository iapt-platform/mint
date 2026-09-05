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
                'title' => __('home.cards.library.title'),
                'lead' => __('home.cards.library.lead'),
                'image' => 'library',
                'tint' => '#2E4A63',
                'align' => 'start',
                'href' => '/library',
                'cta' => __('home.cards.library.cta'),
                'available' => true,
            ],
            [
                'slug' => 'course',
                'eyebrow' => 'Course',
                'title' => __('home.cards.course.title'),
                'lead' => __('home.cards.course.lead'),
                'image' => 'course',
                'tint' => '#2A5257',
                'align' => 'end',
                'href' => '/library/course',
                'cta' => __('home.cards.course.cta'),
                'available' => true,
            ],
            [
                'slug' => 'workspace',
                'eyebrow' => 'Workspace',
                'title' => __('home.cards.workspace.title'),
                'lead' => __('home.cards.workspace.lead'),
                'image' => 'workspace',
                'tint' => '#2C4A3C',
                'align' => 'start',
                'href' => '/pcd-v2026/workspace',
                'cta' => __('home.cards.workspace.cta'),
                'available' => true,
            ],
            [
                'slug' => 'development',
                'eyebrow' => 'Development',
                'title' => __('home.cards.development.title'),
                'lead' => __('home.cards.development.lead'),
                'image' => 'development',
                'tint' => '#4A4A48',
                'align' => 'end',
                'href' => null,
                'cta' => __('home.cards.development.cta'),
                'available' => false,
            ],
        ];

        return view('pages.home', [
            'cards' => $cards,
        ]);
    }
}
