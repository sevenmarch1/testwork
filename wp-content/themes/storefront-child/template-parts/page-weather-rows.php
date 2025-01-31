<?php

use Vnet\Entities\PostCities;
use Vnet\Helpers\ArrayHelper;

$data = ArrayHelper::get($this->args, 'data');

if ($data) {
    foreach ($data as $row) {

        $city = PostCities::getById($row['ID']);
        $temp = $city->getTemperature(); ?>
        <tr>
            <td><?= esc_html($row['city']); ?></td>
            <td><?= esc_html($row['country'] ?: 'Не указано'); ?></td>
            <td><?= $temp ? 'Температура: ' . $temp . ' °C' : 'Не удалось получить данные о погоде.'; ?></td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="3">Нет данных</td>
    </tr>
<?php } ?>