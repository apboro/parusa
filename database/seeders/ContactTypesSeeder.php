<?php

namespace Database\Seeders;

use App\Models\Dictionaries\ContactType;

class ContactTypesSeeder extends GenericSeeder
{
    /**
     * @var array|\array[][]
     *
     * Defaults:
     * 'enabled' => true
     */
    protected array $data = [
        ContactType::class => [
            ContactType::email => [
                'name' => 'Email',
                'type' => ContactType::type_email,
                'has_additional' => false,
                'link_pattern' => 'mailto:%s',
                'lock' => true,
            ],

            ContactType::phone => [
                'name' => 'Телефон',
                'type' => ContactType::type_phone,
                'has_additional' => true,
                'link_pattern' => 'tel:%s',
                'lock' => true,
            ],

            ContactType::mobile => [
                'name' => 'Мобильный',
                'type' => ContactType::type_phone,
                'has_additional' => false,
                'link_pattern' => 'tel:%s',
                'lock' => true,
            ],

            ContactType::vk => [
                'name' => 'VK',
                'type' => ContactType::type_string,
                'has_additional' => false,
                'link_pattern' => '%s',
                'lock' => true,
            ],

            ContactType::fb => [
                'name' => 'Facebook',
                'type' => ContactType::type_string,
                'has_additional' => false,
                'link_pattern' => '%s',
                'lock' => true,
            ],

            ContactType::telegram => [
                'name' => 'Telegram',
                'type' => ContactType::type_string,
                'has_additional' => false,
                'link_pattern' => '%s',
                'lock' => true,
            ],

            ContactType::whatsapp => [
                'name' => 'WhatsApp',
                'type' => ContactType::type_string,
                'has_additional' => false,
                'link_pattern' => '%s',
                'lock' => true,
            ],

            ContactType::skype => [
                'name' => 'Skype',
                'type' => ContactType::type_string,
                'has_additional' => false,
                'link_pattern' => 'skype:%s?call',
                'lock' => true,
            ],
        ],
    ];
}
