<nav>
    <ul class="hmenu_slidesubmenu clearfix">
        <?php foreach ($menu as $text => $element): ?>
        <li>
            <?php echo ActiveHtml::link($text, $element['href']); ?>
            <?php if (isset($element['submenu'])): ?>
            <ul>
                <?php foreach ($element['submenu'] as $subtext => $subel): ?>
                <li>
<?php
$txt = <<<HTML
<strong>{$subtext}</strong>
{$subel['descr']}
HTML;
?>
                    <?php echo ActiveHtml::link($txt, $subel['href']) ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
</nav>