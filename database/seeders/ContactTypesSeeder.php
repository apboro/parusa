<?php

namespace Database\Seeders;

use App\Models\Dictionaries\ContactType;

class ContactTypesSeeder extends GenericSeeder
{
    protected array $data = [
        ContactType::class => [
            ContactType::email => [
                'name' => 'Email',
                'has_additional' => false,
                'link_pattern' => 'mailto:%s',
                'enabled' => true,
                'lock' => true
            ],

            ContactType::phone => [
                'name' => 'Телефон',
                'has_additional' => true,
                'link_pattern' => 'tel:%s',
                'enabled' => true,
                'lock' => true
            ],

            ContactType::mobile => [
                'name' => 'Мобильный',
                'has_additional' => false,
                'link_pattern' => 'tel:%s',
                'enabled' => true,
                'lock' => true
            ],

            ContactType::vk => [
                'name' => 'VK',
                'has_additional' => false,
                'link_pattern' => null,
                'enabled' => true,
                'lock' => true
            ],

            ContactType::fb => [
                'name' => 'Facebook',
                'has_additional' => false,
                'link_pattern' => null,
                'enabled' => true,
                'lock' => true
            ],

            ContactType::telegram => [
                'name' => 'Telegram',
                'has_additional' => false,
                'link_pattern' => null,
                'enabled' => true,
                'lock' => true
            ],

            ContactType::whatsapp => [
                'name' => 'Whatsapp',
                'has_additional' => false,
                'link_pattern' => null,
                'enabled' => true,
                'lock' => true
            ],

            ContactType::skype => [
                'name' => 'Skype',
                'has_additional' => false,
                'link_pattern' => 'skype:%s?call',
                'enabled' => true,
                'lock' => true
            ],
        ],
    ];
}
