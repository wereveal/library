<?php
/**
 *  @brief Creates a entity object.
 *  @file ConfigEntity.php
 *  @ingroup library models
 *  @namespace Ritc/Library/Entities
 *  @class ConfigEntity
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2014-04-01 14:20:11
 *  @note A file in the Ritc Library version 1.0
 *  @note <b>SQL for table<b>
 *  <pre>
 *  CREATE TABLE `config` (
 *    `config_id` int(11) NOT NULL AUTO_INCREMENT,
 *    `config_name` varchar(64) NOT NULL,
 *    `config_value` varchar(64) NOT NULL,
 *    PRIMARY KEY (`config_id`),
 *    UNIQUE KEY `config_name` (`config_name`)
 *  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4
 *  </pre>
 *  <pre>
 *  CREATE TABLE config (
 *      config_id integer DEFAULT nextval('app_config_id_seq'::regclass) NOT NULL,
 *      config_name character varying(64) NOT NULL,
 *      config_value character varying(64) NOT NULL
 *  )
 *  </pre>
 *  <pre>
 *  ALTER TABLE ONLY config
 *      ADD CONSTRAINT config_pkey PRIMARY KEY (config_id);
 *  </pre>
 *  <pre>Expected config key=>value pairs
 *  INSERT INTO config VALUES
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
 *  </pre>
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 - Initial version 04/01/2014 wer</pre>
**/
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

class ConfigEntity implements EntityInterface
{
    private $config_id;
    private $config_name;
    private $config_value;

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
