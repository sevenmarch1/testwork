<?php

require_once __DIR__ . '/vendor/autoload.php';

\Vnet\Loader::getInstance()

    ->setJquerySrc('assets/jquery3/jquery3.min.js')

    ->addAdminStyle('admin-index', THEME_URI . 'assets/css/admin.index.css')

    ->setup();

// регестрируем типы постов
\Vnet\Types\TypeCities::getInstance()->setup();

// регестрируем таксономии
\Vnet\Types\TaxCitiesCountries::getInstance()->setup();

\Vnet\Admin::getInstance()->setup();

// Регестрируем виджеты
\Vnet\WidgetsRegister::getInstance()->setup();