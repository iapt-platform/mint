<?php

/**
 * v3 用例的种子数据。
 *
 * 不同环境（local / staging / prod）数据不同，必要时按环境覆盖。
 * 当前取值来自本地开发库实测 + openapi spec 里的 example。
 */

return [
    // 一个 translation 类型、有翻译进度与译文数据的 channel
    'channel' => '4414c96e-4903-11ef-af72-571c3fee08e6',

    // reactions / reactions/tally 用的 target（collection）
    'target_id' => '5b4511c1-c99f-47e7-9798-97b9e5229e18',

    // tipitaka-related-paragraphs 的两组互斥坐标
    'book'      => 1,
    'para'      => 2,
    'book_name' => 'dn1',
    'cs_para'   => 4,

    // 搜索关键词 + 一条真实 OpenSearch 文档 id（search/{id} 的 200 用例）
    'search_q'       => 'dukkha',
    'search_doc_id'  => 'term_f8afdcaa-ad68-4795-b3d8-d5f12bdb3a5f',
];
