<div>
    Для {{$partner->name}}
</div>

<?php
// Добавляем стили к изображениям в описании новости
$newsDescriptionWithStyles = str_replace('<img', '<img style="max-width: 100%; height: auto;"', $news->description);
?>

<div class="news-description">
    {!!$newsDescriptionWithStyles!!}
</div>
