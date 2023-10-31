export default [
    {
        'title': 'ПРИЧАЛЫ И РЕЙСЫ', 'route': '',
        'roles': ['admin', 'office_manager', 'piers_manager', 'accountant'],
        'items': [
            {'title': 'Рейсы', 'route': 'trip-list', 'roles': ['admin', 'office_manager', 'piers_manager', 'accountant']},
            {'title': 'Причалы/Остановки', 'route': 'pier-list', 'roles': ['admin', 'office_manager', 'piers_manager', 'accountant']},
        ]
    },
    {
        'title': 'ПАРТНЕРЫ, КАССЫ', 'route': '',
        'roles': ['admin', 'office_manager', 'accountant', 'promoter_manager'],
        'items': [
            {'title': 'Компании-партнёры', 'route': 'partners-list', 'roles': ['admin', 'office_manager', 'accountant']},
            {'title': 'Представители', 'route': 'representatives-list', 'roles': ['admin', 'office_manager', 'accountant']},
            {'title': 'Мобильные кассы', 'route': 'terminals-list', 'roles': ['admin', 'office_manager', 'accountant']},
            {'title': 'Промоутеры', 'route': 'promoters-list', 'roles': ['admin', 'office_manager', 'accountant', 'promoter_manager']},
        ]
    },
    {
        'title': 'КОМПАНИЯ', 'route': '',
        'roles': ['admin', 'office_manager', 'accountant'],
        'items': [
            {'title': 'Сотрудники', 'route': 'staff-list', 'roles': ['admin', 'office_manager', 'accountant']},
            {'title': 'Каталог экскурсий', 'route': 'excursion-list', 'roles': ['admin', 'office_manager', 'accountant']},
            {'title': 'Промокоды', 'route': 'promo-code-list', 'roles': ['admin', 'office_manager', 'accountant']},
            {'title': 'Настройки', 'route': 'settings', 'roles': ['admin', 'office_manager', 'accountant']},
            {'title': 'Справочники', 'route': 'dictionaries', 'roles': ['admin', 'office_manager', 'accountant']},
        ]
    },
    {
        'title': 'РЕЕСТРЫ', 'route': '',
        'roles': ['admin', 'office_manager', 'piers_manager', 'accountant', 'promoter_manager'],
        'items': [
            {'title': 'Реестр заказов', 'route': 'orders-registry', 'roles': ['admin', 'office_manager', 'piers_manager', 'accountant']},
            {'title': 'Реестр билетов', 'route': 'tickets-registry', 'roles': ['admin', 'office_manager', 'piers_manager', 'accountant']},
            {'title': 'Реестр броней', 'route': 'reserves-registry', 'roles': ['admin', 'office_manager', 'piers_manager', 'accountant']},
            {'title': 'Реестр транзакций по кассам', 'route': 'transactions-registry', 'roles': ['admin', 'accountant']},
            {'title': 'Реестр по промоутерам', 'route': 'promoters-registry', 'roles': ['admin', 'accountant', 'promoter_manager']},
        ]
    },
    {
        'title': 'СТАТИСТИКА', 'route': '',
        'roles': ['admin'],
        'items': [
            {'title': 'Продажи с сайта Алые Паруса', 'route': 'statistics-sales', 'roles': ['admin']},
            {'title': 'Статистика за сегодня', 'route': 'statistics-sales-today', 'roles': ['admin']},
            {'title': 'Статистика QR-коды', 'route': 'statistics-qr-codes', 'roles': ['admin']},
        ]
    },

];
