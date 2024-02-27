<div>
    Уважаемый(ая), {{$userProfile->fullName}}
</div>

<?php
// Добавляем стили к изображениям в описании новости
$newsDescriptionWithStyles = str_replace('<img', '<img style="max-width: 100%; height: auto;"', $news->description);
?>

<div class="news-description">
    {!!$newsDescriptionWithStyles!!}
</div>

<div style="margin-top: 30px">
    <span>С уважением, партнёрская программа «EXCURR»</span><br>
    <span>Телефон : +7-812-600-30-60</span><br>
    <span>Email : info@parus-a.ru</span><br>
    <span>Адрес офиса: 190098, г. Санкт-Петербург, Галерная улица, дом 11, пом-16 Н</span><br>
</div>
<br>
<img width=300 src="https://lk.excurr.ru/storage/images/email_logo.jpg" alt="excurr.ru"/>
