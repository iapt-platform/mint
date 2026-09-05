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

    'accepted' => 'ຕ້ອງຍອມຮັບ :attribute.',
    'accepted_if' => 'ຕ້ອງຍອມຮັບ :attribute ເມື່ອ :other ເປັນ :value.',
    'active_url' => ':attribute ບໍ່ແມ່ນ URL ທີ່ຖືກຕ້ອງ.',
    'after' => ':attribute ຕ້ອງເປັນວັນທີຫຼັງ :date.',
    'after_or_equal' => ':attribute ຕ້ອງເປັນວັນທີຫຼັງ ຫຼື ເທົ່າກັບ :date.',
    'alpha' => ':attribute ຕ້ອງມີແຕ່ຕົວອັກສອນເທົ່ານັ້ນ.',
    'alpha_dash' => ':attribute ຕ້ອງມີແຕ່ຕົວອັກສອນ, ຕົວເລກ, ຂີດກາງ ແລະ ຂີດລຸ່ມເທົ່ານັ້ນ.',
    'alpha_num' => ':attribute ຕ້ອງມີແຕ່ຕົວອັກສອນ ແລະ ຕົວເລກເທົ່ານັ້ນ.',
    'array' => ':attribute ຕ້ອງເປັນອາເຣ.',
    'before' => ':attribute ຕ້ອງເປັນວັນທີກ່ອນ :date.',
    'before_or_equal' => ':attribute ຕ້ອງເປັນວັນທີກ່ອນ ຫຼື ເທົ່າກັບ :date.',
    'between' => [
        'numeric' => ':attribute ຕ້ອງຢູ່ລະຫວ່າງ :min ຫາ :max.',
        'file' => ':attribute ຕ້ອງມີຂະໜາດລະຫວ່າງ :min ຫາ :max ກິໂລໄບຕ໌.',
        'string' => ':attribute ຕ້ອງມີຄວາມຍາວລະຫວ່າງ :min ຫາ :max ຕົວອັກສອນ.',
        'array' => ':attribute ຕ້ອງມີລະຫວ່າງ :min ຫາ :max ລາຍການ.',
    ],
    'boolean' => 'ຊ່ອງ :attribute ຕ້ອງເປັນ true ຫຼື false.',
    'confirmed' => 'ການຢືນຢັນ :attribute ບໍ່ກົງກັນ.',
    'current_password' => 'ລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ.',
    'date' => ':attribute ບໍ່ແມ່ນວັນທີທີ່ຖືກຕ້ອງ.',
    'date_equals' => ':attribute ຕ້ອງເປັນວັນທີເທົ່າກັບ :date.',
    'date_format' => ':attribute ບໍ່ກົງກັບຮູບແບບ :format.',
    'declined' => 'ຕ້ອງປະຕິເສດ :attribute.',
    'declined_if' => 'ຕ້ອງປະຕິເສດ :attribute ເມື່ອ :other ເປັນ :value.',
    'different' => ':attribute ແລະ :other ຕ້ອງແຕກຕ່າງກັນ.',
    'digits' => ':attribute ຕ້ອງມີ :digits ຫຼັກ.',
    'digits_between' => ':attribute ຕ້ອງມີລະຫວ່າງ :min ຫາ :max ຫຼັກ.',
    'dimensions' => ':attribute ມີຂະໜາດຮູບພາບບໍ່ຖືກຕ້ອງ.',
    'distinct' => 'ຊ່ອງ :attribute ມີຄ່າຊ້ຳກັນ.',
    'email' => ':attribute ຕ້ອງເປັນທີ່ຢູ່ອີເມວທີ່ຖືກຕ້ອງ.',
    'ends_with' => ':attribute ຕ້ອງລົງທ້າຍດ້ວຍໜຶ່ງໃນ: :values.',
    'enum' => ':attribute ທີ່ເລືອກບໍ່ຖືກຕ້ອງ.',
    'exists' => ':attribute ທີ່ເລືອກມີຢູ່ແລ້ວ.',
    'file' => ':attribute ຕ້ອງເປັນໄຟລ໌.',
    'filled' => 'ຊ່ອງ :attribute ຕ້ອງມີຄ່າ.',
    'gt' => [
        'numeric' => ':attribute ຕ້ອງຫຼາຍກວ່າ :value.',
        'file' => ':attribute ຕ້ອງໃຫຍ່ກວ່າ :value ກິໂລໄບຕ໌.',
        'string' => ':attribute ຕ້ອງຍາວກວ່າ :value ຕົວອັກສອນ.',
        'array' => ':attribute ຕ້ອງມີຫຼາຍກວ່າ :value ລາຍການ.',
    ],
    'gte' => [
        'numeric' => ':attribute ຕ້ອງຫຼາຍກວ່າ ຫຼື ເທົ່າກັບ :value.',
        'file' => ':attribute ຕ້ອງໃຫຍ່ກວ່າ ຫຼື ເທົ່າກັບ :value ກິໂລໄບຕ໌.',
        'string' => ':attribute ຕ້ອງຍາວກວ່າ ຫຼື ເທົ່າກັບ :value ຕົວອັກສອນ.',
        'array' => ':attribute ຕ້ອງມີ :value ລາຍການຂຶ້ນໄປ.',
    ],
    'image' => ':attribute ຕ້ອງເປັນຮູບພາບ.',
    'in' => ':attribute ທີ່ເລືອກບໍ່ຖືກຕ້ອງ.',
    'in_array' => 'ຊ່ອງ :attribute ບໍ່ມີຢູ່ໃນ :other.',
    'integer' => ':attribute ຕ້ອງເປັນຈຳນວນເຕັມ.',
    'ip' => ':attribute ຕ້ອງເປັນທີ່ຢູ່ IP ທີ່ຖືກຕ້ອງ.',
    'ipv4' => ':attribute ຕ້ອງເປັນທີ່ຢູ່ IPv4 ທີ່ຖືກຕ້ອງ.',
    'ipv6' => ':attribute ຕ້ອງເປັນທີ່ຢູ່ IPv6 ທີ່ຖືກຕ້ອງ.',
    'mac_address' => ':attribute ຕ້ອງເປັນທີ່ຢູ່ MAC ທີ່ຖືກຕ້ອງ.',
    'json' => ':attribute ຕ້ອງເປັນຂໍ້ຄວາມ JSON ທີ່ຖືກຕ້ອງ.',
    'lt' => [
        'numeric' => ':attribute ຕ້ອງນ້ອຍກວ່າ :value.',
        'file' => ':attribute ຕ້ອງນ້ອຍກວ່າ :value ກິໂລໄບຕ໌.',
        'string' => ':attribute ຕ້ອງສັ້ນກວ່າ :value ຕົວອັກສອນ.',
        'array' => ':attribute ຕ້ອງມີໜ້ອຍກວ່າ :value ລາຍການ.',
    ],
    'lte' => [
        'numeric' => ':attribute ຕ້ອງນ້ອຍກວ່າ ຫຼື ເທົ່າກັບ :value.',
        'file' => ':attribute ຕ້ອງນ້ອຍກວ່າ ຫຼື ເທົ່າກັບ :value ກິໂລໄບຕ໌.',
        'string' => ':attribute ຕ້ອງສັ້ນກວ່າ ຫຼື ເທົ່າກັບ :value ຕົວອັກສອນ.',
        'array' => ':attribute ຕ້ອງມີບໍ່ເກີນ :value ລາຍການ.',
    ],
    'max' => [
        'numeric' => ':attribute ຕ້ອງບໍ່ຫຼາຍກວ່າ :max.',
        'file' => ':attribute ຕ້ອງບໍ່ໃຫຍ່ກວ່າ :max ກິໂລໄບຕ໌.',
        'string' => ':attribute ຕ້ອງບໍ່ຍາວກວ່າ :max ຕົວອັກສອນ.',
        'array' => ':attribute ຕ້ອງມີບໍ່ເກີນ :max ລາຍການ.',
    ],
    'mimes' => ':attribute ຕ້ອງເປັນໄຟລ໌ປະເພດ: :values.',
    'mimetypes' => ':attribute ຕ້ອງເປັນໄຟລ໌ປະເພດ: :values.',
    'min' => [
        'numeric' => ':attribute ຕ້ອງມີຄ່າຢ່າງໜ້ອຍ :min.',
        'file' => ':attribute ຕ້ອງມີຂະໜາດຢ່າງໜ້ອຍ :min ກິໂລໄບຕ໌.',
        'string' => ':attribute ຕ້ອງມີຢ່າງໜ້ອຍ :min ຕົວອັກສອນ.',
        'array' => ':attribute ຕ້ອງມີຢ່າງໜ້ອຍ :min ລາຍການ.',
    ],
    'multiple_of' => ':attribute ຕ້ອງເປັນຜົນຄູນຂອງ :value.',
    'not_in' => ':attribute ທີ່ເລືອກບໍ່ຖືກຕ້ອງ.',
    'not_regex' => 'ຮູບແບບຂອງ :attribute ບໍ່ຖືກຕ້ອງ.',
    'numeric' => ':attribute ຕ້ອງເປັນຕົວເລກ.',
    'password' => 'ລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ.',
    'present' => 'ຕ້ອງມີຊ່ອງ :attribute.',
    'prohibited' => 'ຊ່ອງ :attribute ຖືກຫ້າມ.',
    'prohibited_if' => 'ຊ່ອງ :attribute ຖືກຫ້າມເມື່ອ :other ເປັນ :value.',
    'prohibited_unless' => 'ຊ່ອງ :attribute ຖືກຫ້າມ ເວັ້ນເສຍແຕ່ :other ຢູ່ໃນ :values.',
    'prohibits' => 'ຊ່ອງ :attribute ຫ້າມບໍ່ໃຫ້ມີ :other.',
    'regex' => 'ຮູບແບບຂອງ :attribute ບໍ່ຖືກຕ້ອງ.',
    'required' => 'ຕ້ອງຕື່ມຊ່ອງ :attribute.',
    'required_if' => 'ຕ້ອງຕື່ມຊ່ອງ :attribute ເມື່ອ :other ເປັນ :value.',
    'required_unless' => 'ຕ້ອງຕື່ມຊ່ອງ :attribute ເວັ້ນເສຍແຕ່ :other ຢູ່ໃນ :values.',
    'required_with' => 'ຕ້ອງຕື່ມຊ່ອງ :attribute ເມື່ອມີ :values.',
    'required_with_all' => 'ຕ້ອງຕື່ມຊ່ອງ :attribute ເມື່ອມີ :values ທັງໝົດ.',
    'required_without' => 'ຕ້ອງຕື່ມຊ່ອງ :attribute ເມື່ອບໍ່ມີ :values.',
    'required_without_all' => 'ຕ້ອງຕື່ມຊ່ອງ :attribute ເມື່ອບໍ່ມີຄ່າໃດໆໃນ :values.',
    'same' => ':attribute ແລະ :other ຕ້ອງກົງກັນ.',
    'size' => [
        'numeric' => ':attribute ຕ້ອງເທົ່າກັບ :size.',
        'file' => ':attribute ຕ້ອງມີຂະໜາດ :size ກິໂລໄບຕ໌.',
        'string' => ':attribute ຕ້ອງມີ :size ຕົວອັກສອນ.',
        'array' => ':attribute ຕ້ອງມີ :size ລາຍການ.',
    ],
    'starts_with' => ':attribute ຕ້ອງເລີ່ມຕົ້ນດ້ວຍໜຶ່ງໃນ: :values.',
    'string' => ':attribute ຕ້ອງເປັນຂໍ້ຄວາມ.',
    'timezone' => ':attribute ຕ້ອງເປັນເຂດເວລາທີ່ຖືກຕ້ອງ.',
    'unique' => ':attribute ຖືກໃຊ້ໄປແລ້ວ.',
    'uploaded' => 'ອັບໂຫຼດ :attribute ບໍ່ສຳເລັດ.',
    'url' => ':attribute ຕ້ອງເປັນ URL ທີ່ຖືກຕ້ອງ.',
    'uuid' => ':attribute ຕ້ອງເປັນ UUID ທີ່ຖືກຕ້ອງ.',

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
