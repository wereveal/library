<?php
/**
 *  @note <b>SQL for table<b>
 *  <pre>
 *  MySQL
 *  CREATE TABLE `{dbPrefix}constants` (
 *    `const_id` int(11) NOT NULL AUTO_INCREMENT,
 *    `const_name` varchar(64) NOT NULL,
 *    `const_value` varchar(64) NOT NULL,
 *    PRIMARY KEY (`const_id`),
 *    UNIQUE KEY `const_name` (`const_name`)
 *  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4
 *
 *  PostgreSQL
 *  CREATE SEQUENCE const_id_seq;
 *  ALTER TABLE public.const_id_seq OWNER TO dbOwner;
 *  CREATE TABLE constants (
 *      const_id integer DEFAULT nextval('const_id_seq'::regclass) NOT NULL,
 *      const_name character varying(64) NOT NULL,
 *      const_value character varying(64) NOT NULL
 *  );
 *  ALTER TABLE public.{dbPrefix}constants OWNER TO dbOwner;
 *  ALTER TABLE ONLY {dbPrefix}constants
 *      ADD CONSTRAINT {dbPrefix}const_pkey PRIMARY KEY (const_id);
 *  ALTER TABLE ONLY {dbPrefix}constants
 *      ADD CONSTRAINT {dbPrefix}const_const_name_key UNIQUE (const_name);
 *
 *  Expected constants key=>value pairs
 *
 *  INSERT INTO {dbPrefix}constants (const_name, const_value) VALUES
 *  ('DISPLAY_DATE_FORMAT', 'm/d/Y'),
 *  ('EMAIL_DOMAIN', 'revealitconsulting.com'),
 *  ('EMAIL_FORM_TO', 'bill@revealitconsulting.com'),
 *  ('ERROR_EMAIL_ADDRESS', 'webmaster@revealitconsulting.com'),
 *  ('PAGE_TEMPLATE', 'index.twig'),
 *  ('THEMES_DIR', ''),
 *  ('THEME_NAME', ''),
 *  ('ADMIN_THEME_NAME', ''),
 *  ('CSS_DIR_NAME', 'css'),
 *  ('HTML_DIR_NAME', 'html'),
 *  ('JS_DIR_NAME', 'js'),
 *  ('IMAGE_DIR_NAME', 'images'),
 *  ('ADMIN_DIR_NAME', 'manager'),
 *  ('ASSETS_DIR_NAME', 'assets'),
 *  ('FILES_DIR_NAME', 'files'),
 *  ('DISPLAY_PHONE_FORMAT', 'XXX-XXX-XXXX'),
 *  ('RIGHTS_HOLDER', 'Reveal IT Consulting')
 *  </pre>
 */

namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

class ConstantsEntity implements EntityInterface
{
    private $a_properties;

    public function getAllProperties()
    {
        return $this->a_properties;
    }
    public function setAllProperties(array $a_entity = array())
    {
        $this->a_properties = $a_entity;
    }
}