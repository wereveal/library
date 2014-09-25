<?php
/**
 *  @brief Creates a entity object.
 *  @file ConfigEntity.php
 *  @ingroup library models
 *  @namespace Ritc/Library/Entities
 *  @class ConfigEntity
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.1
 *  @date 2014-09-11 12:53:16
 *  @note A file in the Ritc Library
 *  @note <b>SQL for table<b>
 *  <pre>
 *  MySQL
 *  CREATE TABLE `{dbPrefix}config` (
 *    `config_id` int(11) NOT NULL AUTO_INCREMENT,
 *    `config_name` varchar(64) NOT NULL,
 *    `config_value` varchar(64) NOT NULL,
 *    PRIMARY KEY (`config_id`),
 *    UNIQUE KEY `config_name` (`config_name`)
 *  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4
 *
 *  PostgreSQL
 *  CREATE SEQUENCE config_id_seq;
 *  ALTER TABLE public.config_id_seq OWNER TO dbOwner;
 *  CREATE TABLE config (
 *      config_id integer DEFAULT nextval('config_id_seq'::regclass) NOT NULL,
 *      config_name character varying(64) NOT NULL,
 *      config_value character varying(64) NOT NULL
 *  );
 *  ALTER TABLE public.{dbPrefix}config OWNER TO dbOwner;
 *  ALTER TABLE ONLY {dbPrefix}config
 *      ADD CONSTRAINT {dbPrefix}config_pkey PRIMARY KEY (config_id);
 *  ALTER TABLE ONLY {dbPrefix}config
 *      ADD CONSTRAINT {dbPrefix}config_config_name_key UNIQUE (config_name);
 *
 *  Expected config key=>value pairs
 *
 *  INSERT INTO config (config_name, config_value) VALUES
 *  ('DISPLAY_DATE_FORMAT', 'm/d/Y'),
 *  ('EMAIL_DOMAIN', 'revealitconsulting.com'),
 *  ('EMAIL_FORM_TO', 'bill@revealitconsulting.com'),
 *  ('ERROR_EMAIL_ADDRESS', 'webmaster@revealitconsulting.com'),
 *  ('PAGE_META_DESCRIPTION', 'Reveal IT Consulting'),
 *  ('PAGE_META_KEYWORDS', 'Reveal IT Consulting'),
 *  ('PAGE_TEMPLATE', 'index.twig'),
 *  ('PAGE_TITLE', 'Reveal IT Consulting'),
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
 *  @note <pre><b>Change Log</b>
 *      v1.0.1 - minor change to the comments 09/11/2014 wer
 *      v1.0.0 - Initial version 04/01/2014 wer</pre>
**/
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

class ConfigEntity implements EntityInterface
{
    protected $config_id;
    protected $config_name;
    protected $config_value;

    public function setId($value = '')
    {
        $this->config_id = $value;
    }
    public function setKey($value = '')
    {
        $this->config_name = $value;
    }
    public function setValue($value = '')
    {
        $this->config_value = $value;
    }
    public function getId()
    {
        return $this->config_id;
    }
    public function getKey()
    {
        return $this->config_name;
    }
    public function getValue()
    {
        return $this->config_value;
    }
    /**
     *  returns an array of the properties
     *  @return array
    **/
    public function getAllProperties()
    {
        return array(
            'config_id'    => $this->config_id,
            'config_name'  => $this->config_name,
            'config_value' => $this->config_value
        );
    }
    /**
     *  Sets the values of all the entity properties.
     *  @param array $a_entity e.g., array('config_id'=>'', 'config_name'=>'', 'config_value'=>'')
     *  @return bool success or failure
    **/
    public function setAllProperties(array $a_entity = array())
    {
        $this->config_id    = isset($a_entity['config_id'])    ? $a_entity['config_id']    : '';
        $this->config_name  = isset($a_entity['config_name'])  ? $a_entity['config_name']  : '';
        $this->config_value = isset($a_entity['config_value']) ? $a_entity['config_value'] : '';
    }
}
