<?php

namespace Database\Seeders;

use App\Models\Dictionaries\UserContactType;

class ContactTypesSeeder extends GenericSeeder
{
    /**
     * @var array|\array[][]
     *
     * Defaults:
     * 'enabled' => true
     */
    protected array $data = [
        UserContactType::class => [
            UserContactType::email => [
                'name' => 'Email',
                'type' => UserContactType::type_email,
                'has_additional' => false,
                'link_pattern' => 'mailto:%s',
                'lock' => true,
            ],

            UserContactType::phone => [
                'name' => 'Телефон',
                'type' => UserContactType::type_phone,
                'has_additional' => true,
                'link_pattern' => 'tel:%s',
                'lock' => true,
            ],

            UserContactType::mobile => [
                'name' => 'Мобильный',
                'type' => UserContactType::type_phone,
                'has_additional' => false,
                'link_pattern' => 'tel:%s',
                'lock' => true,
            ],

            UserContactType::vk => [
                'name' => 'VK',
                'type' => UserContactType::type_string,
                'has_additional' => false,
                'link_pattern' => '%s',
                'lock' => true,
            ],

            UserContactType::fb => [
                'name' => 'Facebook',
                'type' => UserContactType::type_string,
                'has_additional' => false,
                'link_pattern' => '%s',
                'lock' => true,
            ],

            UserContactType::telegram => [
                'name' => 'Telegram',
                'type' => UserContactType::type_string,
                'has_additional' => false,
                'link_pattern' => '%s',
                'lock' => true,
            ],

            UserContactType::whatsapp => [
                'name' => 'WhatsApp',
                'type' => UserContactType::type_string,
                'has_additional' => false,
                'link_pattern' => '%s',
                'lock' => true,
            ],

            UserContactType::skype => [
                'name' => 'Skype',
                'type' => UserContactType::type_string,
                'has_additional' => false,
                'link_pattern' => 'skype:%s?call',
                'lock' => true,
            ],
        ],
    ];
}
