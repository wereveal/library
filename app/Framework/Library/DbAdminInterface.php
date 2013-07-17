<?php
namespace Wer\Framework\Interfaces;

interface DbAdminInterface
{
    public function showColumns($table_name);
    public function showTables();
    public function showViews();
}
