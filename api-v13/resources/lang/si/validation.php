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

    'accepted' => ':attribute පිළිගත යුතුය.',
    'accepted_if' => ':other යනු :value වන විට :attribute පිළිගත යුතුය.',
    'active_url' => ':attribute වලංගු URL එකක් නොවේ.',
    'after' => ':attribute යනු :date ට පසු දිනයක් විය යුතුය.',
    'after_or_equal' => ':attribute යනු :date ට සමාන හෝ ඊට පසු දිනයක් විය යුතුය.',
    'alpha' => ':attribute හි අකුරු පමණක් අඩංගු විය යුතුය.',
    'alpha_dash' => ':attribute හි අකුරු, සංඛ්‍යා, ඉරි සහ යටි ඉරි පමණක් අඩංගු විය යුතුය.',
    'alpha_num' => ':attribute හි අකුරු සහ සංඛ්‍යා පමණක් අඩංගු විය යුතුය.',
    'array' => ':attribute අරාවක් විය යුතුය.',
    'before' => ':attribute යනු :date ට පෙර දිනයක් විය යුතුය.',
    'before_or_equal' => ':attribute යනු :date ට සමාන හෝ ඊට පෙර දිනයක් විය යුතුය.',
    'between' => [
        'numeric' => ':attribute යනු :min සහ :max අතර විය යුතුය.',
        'file' => ':attribute යනු :min සහ :max කිලෝබයිට් අතර විය යුතුය.',
        'string' => ':attribute යනු :min සහ :max අක්ෂර අතර විය යුතුය.',
        'array' => ':attribute හි :min සිට :max දක්වා අයිතම තිබිය යුතුය.',
    ],
    'boolean' => ':attribute ක්ෂේත්‍රය සත්‍ය හෝ අසත්‍ය විය යුතුය.',
    'confirmed' => ':attribute තහවුරු කිරීම නොගැලපේ.',
    'current_password' => 'මුරපදය වැරදිය.',
    'date' => ':attribute වලංගු දිනයක් නොවේ.',
    'date_equals' => ':attribute යනු :date ට සමාන දිනයක් විය යුතුය.',
    'date_format' => ':attribute :format ආකෘතිය සමඟ නොගැලපේ.',
    'declined' => ':attribute ප්‍රතික්ෂේප කළ යුතුය.',
    'declined_if' => ':other යනු :value වන විට :attribute ප්‍රතික්ෂේප කළ යුතුය.',
    'different' => ':attribute සහ :other වෙනස් විය යුතුය.',
    'digits' => ':attribute :digits ඉලක්කම් විය යුතුය.',
    'digits_between' => ':attribute :min සහ :max ඉලක්කම් අතර විය යුතුය.',
    'dimensions' => ':attribute හි වලංගු නොවන රූප මිම්ම් ඇත.',
    'distinct' => ':attribute ක්ෂේත්‍රයේ අනුපිටපත් අගයක් ඇත.',
    'email' => ':attribute වලංගු විද්‍යුත් තැපෑල් ලිපිනයක් විය යුතුය.',
    'ends_with' => ':attribute පහත ඒවායින් එකක් සමඟ අවසන් විය යුතුය: :values.',
    'enum' => 'තෝරාගත් :attribute වලංගු නොවේ.',
    'exists' => 'තෝරාගත් :attribute දැනටමත් පවතී.',
    'file' => ':attribute ගොනුවක් විය යුතුය.',
    'filled' => ':attribute ක්ෂේත්‍රයේ අගයක් තිබිය යුතුය.',
    'gt' => [
        'numeric' => ':attribute :value ට වඩා වැඩි විය යුතුය.',
        'file' => ':attribute :value කිලෝබයිට් ට වඩා වැඩි විය යුතුය.',
        'string' => ':attribute :value අක්ෂරයන්ට වඩා වැඩි විය යුතුය.',
        'array' => ':attribute හි :value ට වඩා වැඩි අයිතම තිබිය යුතුය.',
    ],
    'gte' => [
        'numeric' => ':attribute :value ට සමාන හෝ වැඩි විය යුතුය.',
        'file' => ':attribute :value කිලෝබයිට් ට සමාන හෝ වැඩි විය යුතුය.',
        'string' => ':attribute :value අක්ෂරයන්ට සමාන හෝ වැඩි විය යුතුය.',
        'array' => ':attribute හි :value හෝ ඊට වැඩි අයිතම තිබිය යුතුය.',
    ],
    'image' => ':attribute රූපයක් විය යුතුය.',
    'in' => 'තෝරාගත් :attribute වලංගු නොවේ.',
    'in_array' => ':attribute ක්ෂේත්‍රය :other හි නොපවතී.',
    'integer' => ':attribute පූර්ණ සංඛ්‍යාවක් විය යුතුය.',
    'ip' => ':attribute වලංගු IP ලිපිනයක් විය යුතුය.',
    'ipv4' => ':attribute වලංගු IPv4 ලිපිනයක් විය යුතුය.',
    'ipv6' => ':attribute වලංගු IPv6 ලිපිනයක් විය යුතුය.',
    'mac_address' => ':attribute වලංගු MAC ලිපිනයක් විය යුතුය.',
    'json' => ':attribute වලංගු JSON තන්තුවක් විය යුතුය.',
    'lt' => [
        'numeric' => ':attribute :value ට වඩා අඩු විය යුතුය.',
        'file' => ':attribute :value කිලෝබයිට් ට වඩා අඩු විය යුතුය.',
        'string' => ':attribute :value අක්ෂරයන්ට වඩා අඩු විය යුතුය.',
        'array' => ':attribute හි :value ට වඩා අඩු අයිතම තිබිය යුතුය.',
    ],
    'lte' => [
        'numeric' => ':attribute :value ට සමාන හෝ අඩු විය යුතුය.',
        'file' => ':attribute :value කිලෝබයිට් ට සමාන හෝ අඩු විය යුතුය.',
        'string' => ':attribute :value අක්ෂරයන්ට සමාන හෝ අඩු විය යුතුය.',
        'array' => ':attribute හි :value ට වඩා වැඩි අයිතම නොතිබිය යුතුය.',
    ],
    'max' => [
        'numeric' => ':attribute :max ට වඩා වැඩි නොවිය යුතුය.',
        'file' => ':attribute :max කිලෝබයිට් ට වඩා වැඩි නොවිය යුතුය.',
        'string' => ':attribute :max අක්ෂරයන්ට වඩා වැඩි නොවිය යුතුය.',
        'array' => ':attribute හි :max ට වඩා වැඩි අයිතම නොතිබිය යුතුය.',
    ],
    'mimes' => ':attribute :values ආකාරයේ ගොනුවක් විය යුතුය.',
    'mimetypes' => ':attribute :values ආකාරයේ ගොනුවක් විය යුතුය.',
    'min' => [
        'numeric' => ':attribute අවම වශයෙන් :min විය යුතුය.',
        'file' => ':attribute අවම වශයෙන් :min කිලෝබයිට් විය යුතුය.',
        'string' => ':attribute අවම වශයෙන් :min අක්ෂර විය යුතුය.',
        'array' => ':attribute හි අවම වශයෙන් :min අයිතම තිබිය යුතුය.',
    ],
    'multiple_of' => ':attribute :value ගුණයක් විය යුතුය.',
    'not_in' => 'තෝරාගත් :attribute වලංගු නොවේ.',
    'not_regex' => ':attribute ආකෘතිය වලංගු නොවේ.',
    'numeric' => ':attribute සංඛ්‍යාවක් විය යුතුය.',
    'password' => 'මුරපදය වැරදිය.',
    'present' => ':attribute ක්ෂේත්‍රය තිබිය යුතුය.',
    'prohibited' => ':attribute ක්ෂේත්‍රය තහනම් කර ඇත.',
    'prohibited_if' => ':other යනු :value වන විට :attribute ක්ෂේත්‍රය තහනම් කර ඇත.',
    'prohibited_unless' => ':other :values හි නොමැති නම් :attribute ක්ෂේත්‍රය තහනම් කර ඇත.',
    'prohibits' => ':attribute ක්ෂේත්‍රය :other තිබීම තහනම් කරයි.',
    'regex' => ':attribute ආකෘතිය වලංගු නොවේ.',
    'required' => ':attribute ක්ෂේත්‍රය අවශ්‍ය වේ.',
    'required_if' => ':other යනු :value වන විට :attribute ක්ෂේත්‍රය අවශ්‍ය වේ.',
    'required_unless' => ':other :values හි නොමැති නම් :attribute ක්ෂේත්‍රය අවශ්‍ය වේ.',
    'required_with' => ':values තිබෙන විට :attribute ක්ෂේත්‍රය අවශ්‍ය වේ.',
    'required_with_all' => ':values තිබෙන විට :attribute ක්ෂේත්‍රය අවශ්‍ය වේ.',
    'required_without' => ':values නොමැති විට :attribute ක්ෂේත්‍රය අවශ්‍ය වේ.',
    'required_without_all' => ':values කිසිවක් නොමැති විට :attribute ක්ෂේත්‍රය අවශ්‍ය වේ.',
    'same' => ':attribute සහ :other ගැලපිය යුතුය.',
    'size' => [
        'numeric' => ':attribute :size විය යුතුය.',
        'file' => ':attribute :size කිලෝබයිට් විය යුතුය.',
        'string' => ':attribute :size අක්ෂර විය යුතුය.',
        'array' => ':attribute හි :size අයිතම අඩංගු විය යුතුය.',
    ],
    'starts_with' => ':attribute පහත ඒවායින් එකක් සමඟ ආරම්භ විය යුතුය: :values.',
    'string' => ':attribute තන්තුවක් විය යුතුය.',
    'timezone' => ':attribute වලංගු වේලා කලාපයක් විය යුතුය.',
    'unique' => ':attribute දැනටමත් ගෙන ඇත.',
    'uploaded' => ':attribute උඩුගත කිරීම අසාර්ථක විය.',
    'url' => ':attribute වලංගු URL එකක් විය යුතුය.',
    'uuid' => ':attribute වලංගු UUID එකක් විය යුතුය.',

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
