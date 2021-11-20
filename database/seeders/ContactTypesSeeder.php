<?php

namespace Database\Seeders;

use App\Models\Dictionaries\ContactType;

class ContactTypesSeeder extends GenericSeeder
{
    protected array $data = [
        ContactType::class => [
            ContactType::email => [
                'name' => 'Email',
                'type' => ContactType::type_email,
                'has_additional' => false,
                'link_pattern' => 'mailto:%s',
                'enabled' => true,
                'lock' => true,
            ],

            ContactType::phone => [
                'name' => 'Телефон',
                'type' => ContactType::type_phone,
                'has_additional' => true,
                'link_pattern' => 'tel:%s',
                'enabled' => true,
                'lock' => true,
            ],

            ContactType::mobile => [
                'name' => 'Мобильный',
                'type' => ContactType::type_phone,
                'has_additional' => false,
                'link_pattern' => 'tel:%s',
                'enabled' => true,
                'lock' => true,
            ],

            ContactType::vk => [
                'name' => 'VK',
                'type' => ContactType::type_string,
                'has_additional' => false,
                'link_pattern' => null,
                'enabled' => true,
                'lock' => true,
            ],

            ContactType::fb => [
                'name' => 'Facebook',
                'type' => ContactType::type_string,
                'has_additional' => false,
                'link_pattern' => null,
                'enabled' => true,
                'lock' => true,
            ],

            ContactType::telegram => [
                'name' => 'Telegram',
                'type' => ContactType::type_string,
                'has_additional' => false,
                'link_pattern' => null,
                'enabled' => true,
                'lock' => true,
            ],

            ContactType::whatsapp => [
                'name' => 'Whatsapp',
                'type' => ContactType::type_string,
                'has_additional' => false,
                'link_pattern' => null,
                'enabled' => true,
                'lock' => true,
            ],

            ContactType::skype => [
                'name' => 'Skype',
                'type' => ContactType::type_string,
                'has_additional' => false,
                'link_pattern' => 'skype:%s?call',
                'enabled' => true,
                'lock' => true,
            ],
        ],
    ];
}
