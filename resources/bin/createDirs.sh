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
mkdir -p resources/templates/default
mkdir -p resources/templates/elements
mkdir -p resources/templates/pages
mkdir -p resources/templates/snippets'
mkdir -p resources/templates/tests
echo "<?php" > index.php
echo 'header("Location: http://" . $_SERVER["SERVER_NAME"] . "/");' >> index.php
echo '?>' >> index.php
echo "<h3>An Error Has Occured.</h3>" > resources/templates/no_file.twig
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
cp index.php resources/themes
cp resources/templates/no_file.twig resources/templates/default
cp resources/templates/no_file.twig resources/templates/elements
cp resources/templates/no_file.twig resources/templates/pages
cp resources/templates/no_file.twig resources/templates/snippets'
cp resources/templates/no_file.twig resources/templates/tests
