<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute 必须被接受。',
    'accepted_if' => '当 :other 为 :value 时，:attribute 必须被接受。',
    'active_url' => ':attribute 不是有效的 URL。',
    'after' => ':attribute 必须是 :date 之后的日期。',
    'after_or_equal' => ':attribute 必须是等于或晚于 :date 的日期。',
    'alpha' => ':attribute 只能包含字母。',
    'alpha_dash' => ':attribute 只能包含字母、数字、破折号和下划线。',
    'alpha_num' => ':attribute 只能包含字母和数字。',
    'array' => ':attribute 必须是数组。',
    'before' => ':attribute 必须是 :date 之前的日期。',
    'before_or_equal' => ':attribute 必须是等于或早于 :date 的日期。',
    'between' => [
        'numeric' => ':attribute 必须在 :min 和 :max 之间。',
        'file' => ':attribute 必须在 :min 和 :max 千字节之间。',
        'string' => ':attribute 必须在 :min 和 :max 个字符之间。',
        'array' => ':attribute 必须在 :min 和 :max 个元素之间。',
    ],
    'boolean' => ':attribute 字段必须为 true 或 false。',
    'confirmed' => ':attribute 两次输入不一致。',
    'current_password' => '密码不正确。',
    'date' => ':attribute 不是有效的日期。',
    'date_equals' => ':attribute 必须是等于 :date 的日期。',
    'date_format' => ':attribute 与格式 :format 不匹配。',
    'declined' => ':attribute 必须被拒绝。',
    'declined_if' => '当 :other 为 :value 时，:attribute 必须被拒绝。',
    'different' => ':attribute 与 :other 必须不同。',
    'digits' => ':attribute 必须是 :digits 位数字。',
    'digits_between' => ':attribute 必须在 :min 和 :max 位数字之间。',
    'dimensions' => ':attribute 的图片尺寸无效。',
    'distinct' => ':attribute 字段存在重复值。',
    'email' => ':attribute 必须是有效的电子邮件地址。',
    'ends_with' => ':attribute 必须以下列之一结尾：:values。',
    'enum' => '所选的 :attribute 无效。',
    'exists' => '所选的 :attribute 无效。',
    'file' => ':attribute 必须是文件。',
    'filled' => ':attribute 字段不能为空。',
    'gt' => [
        'numeric' => ':attribute 必须大于 :value。',
        'file' => ':attribute 必须大于 :value 千字节。',
        'string' => ':attribute 必须多于 :value 个字符。',
        'array' => ':attribute 必须多于 :value 个元素。',
    ],
    'gte' => [
        'numeric' => ':attribute 必须大于或等于 :value。',
        'file' => ':attribute 必须大于或等于 :value 千字节。',
        'string' => ':attribute 必须大于或等于 :value 个字符。',
        'array' => ':attribute 必须有 :value 个或更多元素。',
    ],
    'image' => ':attribute 必须是图片。',
    'in' => '所选的 :attribute 无效。',
    'in_array' => ':attribute 字段不存在于 :other 中。',
    'integer' => ':attribute 必须是整数。',
    'ip' => ':attribute 必须是有效的 IP 地址。',
    'ipv4' => ':attribute 必须是有效的 IPv4 地址。',
    'ipv6' => ':attribute 必须是有效的 IPv6 地址。',
    'mac_address' => ':attribute 必须是有效的 MAC 地址。',
    'json' => ':attribute 必须是有效的 JSON 字符串。',
    'lt' => [
        'numeric' => ':attribute 必须小于 :value。',
        'file' => ':attribute 必须小于 :value 千字节。',
        'string' => ':attribute 必须少于 :value 个字符。',
        'array' => ':attribute 必须少于 :value 个元素。',
    ],
    'lte' => [
        'numeric' => ':attribute 必须小于或等于 :value。',
        'file' => ':attribute 必须小于或等于 :value 千字节。',
        'string' => ':attribute 必须小于或等于 :value 个字符。',
        'array' => ':attribute 不能多于 :value 个元素。',
    ],
    'max' => [
        'numeric' => ':attribute 不能大于 :max。',
        'file' => ':attribute 不能大于 :max 千字节。',
        'string' => ':attribute 不能多于 :max 个字符。',
        'array' => ':attribute 不能多于 :max 个元素。',
    ],
    'mimes' => ':attribute 必须是以下类型的文件：:values。',
    'mimetypes' => ':attribute 必须是以下类型的文件：:values。',
    'min' => [
        'numeric' => ':attribute 至少为 :min。',
        'file' => ':attribute 至少为 :min 千字节。',
        'string' => ':attribute 至少为 :min 个字符。',
        'array' => ':attribute 至少有 :min 个元素。',
    ],
    'multiple_of' => ':attribute 必须是 :value 的倍数。',
    'not_in' => '所选的 :attribute 无效。',
    'not_regex' => ':attribute 格式无效。',
    'numeric' => ':attribute 必须是数字。',
    'password' => '密码不正确。',
    'present' => ':attribute 字段必须存在。',
    'prohibited' => ':attribute 字段被禁止。',
    'prohibited_if' => '当 :other 为 :value 时，:attribute 字段被禁止。',
    'prohibited_unless' => '除非 :other 在 :values 中，否则 :attribute 字段被禁止。',
    'prohibits' => ':attribute 字段禁止 :other 出现。',
    'regex' => ':attribute 格式无效。',
    'required' => ':attribute 字段是必填项。',
    'required_if' => '当 :other 为 :value 时，:attribute 字段是必填项。',
    'required_unless' => '除非 :other 在 :values 中，否则 :attribute 字段是必填项。',
    'required_with' => '当 :values 存在时，:attribute 字段是必填项。',
    'required_with_all' => '当 :values 都存在时，:attribute 字段是必填项。',
    'required_without' => '当 :values 不存在时，:attribute 字段是必填项。',
    'required_without_all' => '当 :values 都不存在时，:attribute 字段是必填项。',
    'same' => ':attribute 与 :other 必须匹配。',
    'size' => [
        'numeric' => ':attribute 必须为 :size。',
        'file' => ':attribute 必须为 :size 千字节。',
        'string' => ':attribute 必须为 :size 个字符。',
        'array' => ':attribute 必须包含 :size 个元素。',
    ],
    'starts_with' => ':attribute 必须以下列之一开头：:values。',
    'string' => ':attribute 必须是字符串。',
    'timezone' => ':attribute 必须是有效的时区。',
    'unique' => ':attribute 已经存在。',
    'uploaded' => ':attribute 上传失败。',
    'url' => ':attribute 必须是有效的 URL。',
    'uuid' => ':attribute 必须是有效的 UUID。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];

