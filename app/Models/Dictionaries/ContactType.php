<?php

namespace App\Models\Dictionaries;

/**
 * @property int id
 * @property bool enabled
 * @property bool lock
 * @property string name
 * @property bool has_additional
 * @property string|null link_pattern
 * @property \Carbon\Carbon created_at
 * @property \Carbon\Carbon updated_at
 * @property string type
 */
class ContactType extends AbstractDictionaryItem
{
    /** @var int The id of email contact type */
    public const email = 1;

    /** @var int The id of phone contact type */
    public const phone = 2;

    /** @var int The id of mobile phone contact type */
    public const mobile = 3;

    /** @var int The id of Vkontakte contact type */
    public const vk = 4;

    /** @var int The id of Facebook contact type */
    public const fb = 5;

    /** @var int The id of Telegram contact type */
    public const telegram = 6;

    /** @var int The id of WhatsApp contact type */
    public const whatsapp = 7;

    /** @var int The id of Skype contact type */
    public const skype = 8;

    /** @var int The identifier of phone type for internal use */
    public const type_phone = 'phone';

    /** @var int The identifier of email type for internal use */
    public const type_email = 'email';

    /** @var int The identifier of string type for internal use */
    public const type_string = 'string';

    /** @var string Referenced table name */
    protected $table = 'dictionary_contact_types';

    /** @var string[] Attributes casting */
    protected $casts = [
        'enabled' => 'bool',
        'lock' => 'bool',
        'has_additional' => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
