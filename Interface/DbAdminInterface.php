<?php
namespace Ritc\Library\Interface;

interface DbAdminInterface
{
    public function showColumns($table_name);
    public function showTables();
    public function showViews();
}
