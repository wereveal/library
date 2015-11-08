#!/bin/bash
mkdir Abstracts
mkdir Controllers
mkdir Entities
mkdir Interfaces
mkdir Models
mkdir Tests
mkdir Traits
mkdir Views
mkdir -p resources/config
mkdir -p resources/sql
mkdir -p resources/templates
mkdir -p resources/themes
echo "<?php" > index.php
echo 'header("Location: http://" . $_SERVER["SERVER_NAME"] . "/");' >> index.php
echo '?>' >> index.php
cp index.php Abstracts/
cp index.php Controllers/
cp index.php Entities/
cp index.php Interfaces/
cp index.php Models/
cp index.php Tests/
cp index.php Traits/
cp index.php Views/
cp index.php resources/
cp index.php resources/config
cp index.php resources/sql
cp index.php resources/templates
cp index.php resources/themes

