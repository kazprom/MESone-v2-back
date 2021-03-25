<?php

namespace App\Http\Controllers;

class DatabaseController extends Controller
{
    /**
     * Получить список установленных драйверов DB
     *
     * @return array
     */
    public function availableDrivers(): array
    {
        return pdo_drivers();
    }
}
