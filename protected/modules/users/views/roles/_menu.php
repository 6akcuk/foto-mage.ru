<?php
    if (!isset($selected)) $selected = '';

    $items = array(
        'Роли' => 'roles/index',
        'Операции' => 'roles/operations',
        'Связь роли с операциями' => 'roles/link',
    )
?>

<section class="block">
    <h4 class="green">Меню</h4>
    <ul class="pivots">
        <?php foreach ($items as $name => $url): ?>
        <li>
            <?php echo ActiveHtml::link('<span class="go iconify">4</span><strong>'. $name .'</strong>', $this->createUrl($url), ($selected == $name) ? array('class' => 'selected') : '') ?>
        </li>
        <?php endforeach; ?>
    </ul>
</section>