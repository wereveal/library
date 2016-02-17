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
echo "Deny from all" > .htaccess
echo "Place Holder" > Abstracts/.keep_me
echo "Place Holder" > Controllers/.keep_me
echo "Place Holder" > Entities/.keep_me
echo "Place Holder" > Interfaces/.keep_me
echo "Place Holder" > Models/.keep_me
echo "Place Holder" > Tests/.keep_me
echo "Place Holder" > Traits/.keep_me
echo "Place Holder" > Views/.keep_me
echo "Place Holder" > resources/.keep_me
echo "Place Holder" > resources/config/.keep_me
echo "Place Holder" > resources/sql/.keep_me
echo "Place Holder" > resources/themes/.keep_me
echo "<h3>An Error Has Occured.</h3>" > resources/templates/no_file.twig
echo "<h3>An Error Has Occured.</h3>" > resources/templates/default/no_file.twig
echo "<h3>An Error Has Occured.</h3>" > resources/templates/elements/no_file.twig
echo "<h3>An Error Has Occured.</h3>" > resources/templates/pages/no_file.twig
echo "<h3>An Error Has Occured.</h3>" > resources/templates/snippets/no_file.twig
echo "<h3>An Error Has Occured.</h3>" > resources/templates/tests/no_file.twig

