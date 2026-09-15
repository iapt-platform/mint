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

    'accepted' => ':attribute ကို လက်ခံရပါမည်။',
    'accepted_if' => ':other သည် :value ဖြစ်သောအခါ :attribute ကို လက်ခံရပါမည်။',
    'active_url' => ':attribute သည် မမှန်ကန်သော URL ဖြစ်သည်။',
    'after' => ':attribute သည် :date နောက်ပိုင်း ရက်စွဲဖြစ်ရပါမည်။',
    'after_or_equal' => ':attribute သည် :date နှင့် ညီမျှသော သို့မဟုတ် နောက်ပိုင်း ရက်စွဲဖြစ်ရပါမည်။',
    'alpha' => ':attribute တွင် စာလုံးများသာ ပါဝင်နိုင်သည်။',
    'alpha_dash' => ':attribute တွင် စာလုံး၊ ဂဏန်း၊ တိုက်ပိုင်း နှင့် အောက်မျဉ်းများသာ ပါဝင်နိုင်သည်။',
    'alpha_num' => ':attribute တွင် စာလုံး နှင့် ဂဏန်းများသာ ပါဝင်နိုင်သည်။',
    'array' => ':attribute သည် array ဖြစ်ရပါမည်။',
    'before' => ':attribute သည် :date မတိုင်မီ ရက်စွဲဖြစ်ရပါမည်။',
    'before_or_equal' => ':attribute သည် :date နှင့် ညီမျှသော သို့မဟုတ် မတိုင်မီ ရက်စွဲဖြစ်ရပါမည်။',
    'between' => [
        'numeric' => ':attribute သည် :min နှင့် :max အကြား ဖြစ်ရပါမည်။',
        'file' => ':attribute သည် :min နှင့် :max ကီလိုဘိုက်အကြား ဖြစ်ရပါမည်။',
        'string' => ':attribute သည် :min နှင့် :max လုံးကြားရှိ စာလုံးများ ဖြစ်ရပါမည်။',
        'array' => ':attribute တွင် :min နှင့် :max ခုကြားရှိ အရာများ ပါဝင်ရပါမည်။',
    ],
    'boolean' => ':attribute ကွက်လပ်သည် true သို့မဟုတ် false ဖြစ်ရပါမည်။',
    'confirmed' => ':attribute အတည်ပြုချက် မကိုက်ညီပါ။',
    'current_password' => 'စကားဝှက် မှားယွင်းနေပါသည်။',
    'date' => ':attribute သည် မမှန်ကန်သော ရက်စွဲဖြစ်သည်။',
    'date_equals' => ':attribute သည် :date နှင့် ညီမျှသော ရက်စွဲဖြစ်ရပါမည်။',
    'date_format' => ':attribute သည် :format ပုံစံနှင့် မကိုက်ညီပါ။',
    'declined' => ':attribute ကို ငြင်းပယ်ရပါမည်။',
    'declined_if' => ':other သည် :value ဖြစ်သောအခါ :attribute ကို ငြင်းပယ်ရပါမည်။',
    'different' => ':attribute နှင့် :other သည် မတူညီရပါမည်။',
    'digits' => ':attribute သည် :digits လုံးရှိ ဂဏန်းဖြစ်ရပါမည်။',
    'digits_between' => ':attribute သည် :min နှင့် :max လုံးကြားရှိ ဂဏန်းဖြစ်ရပါမည်။',
    'dimensions' => ':attribute တွင် မမှန်ကန်သော ပုံ၏ အရွယ်အစားများ ရှိနေသည်။',
    'distinct' => ':attribute ကွက်လပ်တွင် ထပ်တူတန်ဖိုး ရှိနေသည်။',
    'email' => ':attribute သည် မှန်ကန်သော အီးမေးလ်လိပ်စာ ဖြစ်ရပါမည်။',
    'ends_with' => ':attribute သည် အောက်ပါတို့ထဲမှ တစ်ခုဖြင့် ဆုံးရပါမည်: :values။',
    'enum' => 'ရွေးချယ်ထားသော :attribute မမှန်ကန်ပါ။',
    'exists' => 'ရွေးချယ်ထားသော :attribute ရှိပြီးဖြစ်သည်။',
    'file' => ':attribute သည် ဖိုင်ဖြစ်ရပါမည်။',
    'filled' => ':attribute ကွက်လပ်တွင် တန်ဖိုး ရှိရပါမည်။',
    'gt' => [
        'numeric' => ':attribute သည် :value ထက် ကြီးရပါမည်။',
        'file' => ':attribute သည် :value ကီလိုဘိုက်ထက် ကြီးရပါမည်။',
        'string' => ':attribute သည် :value လုံးထက် ပိုများသော စာလုံးများ ရှိရပါမည်။',
        'array' => ':attribute တွင် :value ခုထက် ပိုများသော အရာများ ရှိရပါမည်။',
    ],
    'gte' => [
        'numeric' => ':attribute သည် :value နှင့် ညီမျှသော သို့မဟုတ် ကြီးရပါမည်။',
        'file' => ':attribute သည် :value ကီလိုဘိုက်နှင့် ညီမျှသော သို့မဟုတ် ကြီးရပါမည်။',
        'string' => ':attribute သည် :value လုံးနှင့် ညီမျှသော သို့မဟုတ် ပိုများသော စာလုံးများ ရှိရပါမည်။',
        'array' => ':attribute တွင် :value ခု သို့မဟုတ် ပိုများသော အရာများ ရှိရပါမည်။',
    ],
    'image' => ':attribute သည် ပုံဖိုင်ဖြစ်ရပါမည်။',
    'in' => 'ရွေးချယ်ထားသော :attribute မမှန်ကန်ပါ။',
    'in_array' => ':attribute ကွက်လပ်သည် :other တွင် မရှိပါ။',
    'integer' => ':attribute သည် ကိန်းပြည့်ဖြစ်ရပါမည်။',
    'ip' => ':attribute သည် မှန်ကန်သော IP လိပ်စာဖြစ်ရပါမည်။',
    'ipv4' => ':attribute သည် မှန်ကန်သော IPv4 လိပ်စာဖြစ်ရပါမည်။',
    'ipv6' => ':attribute သည် မှန်ကန်သော IPv6 လိပ်စာဖြစ်ရပါမည်။',
    'mac_address' => ':attribute သည် မှန်ကန်သော MAC လိပ်စာဖြစ်ရပါမည်။',
    'json' => ':attribute သည် မှန်ကန်သော JSON string ဖြစ်ရပါမည်။',
    'lt' => [
        'numeric' => ':attribute သည် :value ထက် သေးငယ်ရပါမည်။',
        'file' => ':attribute သည် :value ကီလိုဘိုက်ထက် သေးငယ်ရပါမည်။',
        'string' => ':attribute သည် :value လုံးထက် နည်းသော စာလုံးများ ရှိရပါမည်။',
        'array' => ':attribute တွင် :value ခုထက် နည်းသော အရာများ ရှိရပါမည်။',
    ],
    'lte' => [
        'numeric' => ':attribute သည် :value နှင့် ညီမျှသော သို့မဟုတ် သေးငယ်ရပါမည်။',
        'file' => ':attribute သည် :value ကီလိုဘိုက်နှင့် ညီမျှသော သို့မဟုတ် သေးငယ်ရပါမည်။',
        'string' => ':attribute သည် :value လုံးနှင့် ညီမျှသော သို့မဟုတ် နည်းသော စာလုံးများ ရှိရပါမည်။',
        'array' => ':attribute တွင် :value ခုထက် ပိုများသော အရာများ မပါဝင်ရပါ။',
    ],
    'max' => [
        'numeric' => ':attribute သည် :max ထက် မကြီးရပါ။',
        'file' => ':attribute သည် :max ကီလိုဘိုက်ထက် မကြီးရပါ။',
        'string' => ':attribute သည် :max လုံးထက် မပိုရပါ။',
        'array' => ':attribute တွင် :max ခုထက် ပိုများသော အရာများ မပါဝင်ရပါ။',
    ],
    'mimes' => ':attribute သည် ဤအမျိုးအစား ဖိုင်ဖြစ်ရပါမည်: :values။',
    'mimetypes' => ':attribute သည် ဤအမျိုးအစား ဖိုင်ဖြစ်ရပါမည်: :values။',
    'min' => [
        'numeric' => ':attribute သည် အနည်းဆုံး :min ဖြစ်ရပါမည်။',
        'file' => ':attribute သည် အနည်းဆုံး :min ကီလိုဘိုက်ဖြစ်ရပါမည်။',
        'string' => ':attribute သည် အနည်းဆုံး :min လုံးရှိသော စာလုံးများ ဖြစ်ရပါမည်။',
        'array' => ':attribute တွင် အနည်းဆုံး :min ခုရှိသော အရာများ ပါဝင်ရပါမည်။',
    ],
    'multiple_of' => ':attribute သည် :value ၏ အဆတိုးဖြစ်ရပါမည်။',
    'not_in' => 'ရွေးချယ်ထားသော :attribute မမှန်ကန်ပါ။',
    'not_regex' => ':attribute ပုံစံ မမှန်ကန်ပါ။',
    'numeric' => ':attribute သည် ဂဏန်းဖြစ်ရပါမည်။',
    'password' => 'စကားဝှက် မှားယွင်းနေပါသည်။',
    'present' => ':attribute ကွက်လပ် ရှိရပါမည်။',
    'prohibited' => ':attribute ကွက်လပ် ခွင့်မပြုပါ။',
    'prohibited_if' => ':other သည် :value ဖြစ်သောအခါ :attribute ကွက်လပ် ခွင့်မပြုပါ။',
    'prohibited_unless' => ':other သည် :values တွင် မပါဝင်သမျှ :attribute ကွက်လပ် ခွင့်မပြုပါ။',
    'prohibits' => ':attribute ကွက်လပ်သည် :other ၏ ပါဝင်မှုကို တားမြစ်သည်။',
    'regex' => ':attribute ပုံစံ မမှန်ကန်ပါ။',
    'required' => ':attribute ကွက်လပ် လိုအပ်သည်။',
    'required_if' => ':other သည် :value ဖြစ်သောအခါ :attribute ကွက်လပ် လိုအပ်သည်။',
    'required_unless' => ':other သည် :values တွင် မပါဝင်သမျှ :attribute ကွက်လပ် လိုအပ်သည်။',
    'required_with' => ':values ရှိသောအခါ :attribute ကွက်လပ် လိုအပ်သည်။',
    'required_with_all' => ':values များ ရှိသောအခါ :attribute ကွက်လပ် လိုအပ်သည်။',
    'required_without' => ':values မရှိသောအခါ :attribute ကွက်လပ် လိုအပ်သည်။',
    'required_without_all' => ':values များ မရှိသောအခါ :attribute ကွက်လပ် လိုအပ်သည်။',
    'same' => ':attribute နှင့် :other တူညီရပါမည်။',
    'size' => [
        'numeric' => ':attribute သည် :size ဖြစ်ရပါမည်။',
        'file' => ':attribute သည် :size ကီလိုဘိုက် ဖြစ်ရပါမည်။',
        'string' => ':attribute သည် :size လုံးရှိသော စာလုံးများ ဖြစ်ရပါမည်။',
        'array' => ':attribute တွင် :size ခုရှိသော အရာများ ပါဝင်ရပါမည်။',
    ],
    'starts_with' => ':attribute သည် အောက်ပါတို့ထဲမှ တစ်ခုဖြင့် စတင်ရပါမည်: :values။',
    'string' => ':attribute သည် စာသားဖြစ်ရပါမည်။',
    'timezone' => ':attribute သည် မှန်ကန်သော အချိန်ဇုန်ဖြစ်ရပါမည်။',
    'unique' => ':attribute ကို အသုံးပြုပြီးဖြစ်သည်။',
    'uploaded' => ':attribute တင်သွင်းမှု မအောင်မြင်ပါ။',
    'url' => ':attribute သည် မှန်ကန်သော URL ဖြစ်ရပါမည်။',
    'uuid' => ':attribute သည် မှန်ကန်သော UUID ဖြစ်ရပါမည်။',

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
