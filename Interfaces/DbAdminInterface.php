<?php
namespace Ritc\Library\Interfaces;

interface DbAdminInterface
{
    public function showColumns($table_name);
    public function showTables();
    public function showViews();
}
