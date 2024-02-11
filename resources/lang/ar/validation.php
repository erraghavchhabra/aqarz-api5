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

    'accepted' => 'ال :attribute يجب ان يكون مقبول.',
    'active_url' => 'ال :attribute ليس رابط صالح.',
    'after' => 'ال :attribute يجب ان يكون تاريخ :date.',
    'after_or_equal' => 'ال :attribute يجب ان يكون تاريخ يساوي او اكبر :date.',
    'alpha' => 'ال :attribute يجب ان يحتوي حروف.',
    'alpha_dash' => 'ال :attribute يجب ان يحتوى حروف وارقام وداش فقط.',
    'alpha_num' => 'ال :attribute يجب ان يحتوي ارقام وحروف فقط.',
    'array' => 'ال :attribute يجب ان يكون مصفوفة.',
    'before' => 'ال :attribute يجب ان يكون تاريخ قبل :date.',
    'before_or_equal' => 'ال :attribute يجب ان يكون تاريخ قبل او يساوي :date.',
    'between' => [
        'numeric' => 'ال :attribute يجب ان يكون بين :min و :max.',
        'file' => 'ال :attribute يجب ان يكون بين :min و :max كيلومتر.',
        'string' => 'The :attribute must be between :min and :max characters.',
        'array' => 'The :attribute must have between :min and :max items.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'date' => 'The :attribute is not a valid date.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => 'هذا :attribute يجب ان يكون بريد الكتروني صالح',
    'ends_with' => 'The :attribute must end with one of the following: :values.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'string' => 'The :attribute must be greater than :value characters.',
        'array' => 'The :attribute must have more than :value items.',
    ],
    'gte' => [
        'numeric' => 'ال :attribute يجب ان يكون يساوي او اكبر :value.',
        'file' => 'ال :attribute يجب ان يكون يساوي او اكبر :value كيلومتر.',
        'string' => 'ال :attribute يجب ان يكون اقل او يساوي :value حروف.',
        'array' => 'ال :attribute يجب ان يكون يحتوى :value عنصر او اكثر.',
    ],
    'image' => 'الحقل :attribute يجب ان يكون صورة.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'ال :attribute غير موجود في :other.',
    'integer' => 'ال :attribute يجب ان يكون رقم صحيح.',
    'ip' => 'The :attribute must be a valid IP address.',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'lt' => [
        'numeric' => 'The :attribute must be less than :value.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'string' => 'The :attribute must be less than :value characters.',
        'array' => 'The :attribute must have less than :value items.',
    ],
    'lte' => [
        'numeric' => 'The :attribute must be less than or equal :value.',
        'file' => 'The :attribute must be less than or equal :value kilobytes.',
        'string' => 'The :attribute must be less than or equal :value characters.',
        'array' => 'The :attribute must not have more than :value items.',
    ],
    'max' => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file' => 'The :attribute may not be greater than :max kilobytes.',
        'string' => 'The :attribute may not be greater than :max characters.',
        'array' => 'The :attribute may not have more than :max items.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'string' => 'The :attribute must be at least :min characters.',
        'array' => 'The :attribute must have at least :min items.',
    ],
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => ' :attribute غير صحيح.',
    'numeric' => 'The :attribute must be a number.',
    'password' => 'كلمة المرور غير صحيحة.',
    'present' => 'The :attribute field must be present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'numeric' => 'The :attribute must be :size.',
        'file' => 'The :attribute must be :size kilobytes.',
        'string' => 'The :attribute must be :size characters.',
        'array' => 'The :attribute must contain :size items.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid zone.',
   // 'unique' => 'The :attribute has already been taken.',
    'unique' => 'الحقل :attribute مستخدما سابقا ',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute format is invalid.',
    'uuid' => 'The :attribute must be a valid UUID.',

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

    'attributes' => [
        'mobile'=>'الجوال',
        'country_code'=>'مفتاح الدولة',
        'code'=>'كود التحقق',
        'password'=>'كلمة المرور',
        'password_confirmation'=>'تأكيد كلمة المرور',
        'logo'=>'الصورة',
        'type'=>'النوع',
        'client_name'=>'اسم العميل',
        'client_mobile'=>'رقم جوال العميل',
        'source_type'=>'نوع المصدر',
        'request_type'=>'نوع الطلب',
        'ads_number'=>'رقم الاعلان',
        'priority'=>'الأولوية',
        'remember'=>'تذكر',
        'remember_date_time'=>'تاريخ التذكر',
         'name_ar'=>'الاسم بالعربية',
        'name_en'=>'الاسم بالانجليزية',
        'description_ar'=>'الوصف بالعربية',
        'description_en'=>'الوصف بالانجليزية',
        'NotFound'=>'غير موجود',
        'id'=>'رقم',
        'estate_id'=>'رقم العقار',
        'rate'=>'التقييم',
        'identity_number'=>'رقم الهوية',
        'identity_file'=>'صورة الهوية',
        'job_type'=>'المسمى الوظيفي',
        'city_id'=>'رقم المدينة',
        'job_start_date'=>'تاريخ بدأ العمل',
        'total_salary'=>'إجمالي الراتب',
        'birthday'=>'تاريخ الميلاد',
        'estate_type_id'=>'نوع العقار',
        'finance_interval'=>'فترة التمويل',
        'estate_price'=>'سعر العقار',
        'available_amount'=>'القيمة المتوفرة',
        'solidarity_partner'=>'كفيل',
        'solidarity_salary'=>'راتب الكفيل',
        'national_address'=>'العنوان الوطني',
        'national_address_display'=>'عرض العنوان الوطني',
        'is_first_home'=>'هل البيت الاول',
        'photo'=>'الصور',
        'lat'=>'خط طول',
        'lan'=>'خط عرض',
        'note'=>'ملاحظات ',
        'address'=>'العنوان',
        'owner_name'=>'اسم المالك',
        'email'=>'البريد الالكتروني',
    ],

];
