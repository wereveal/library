<?php
/**
 *  @brief SQL for constants table and basic accessors.
 *  @file ConstantsEntity.php
 *  @ingroup ritc_library entities
 *  @namespace Ritc\Library\Entities
 *  @class ConstantsEntity
 *  @author William E Reveal
 *  @date 2015-10-06 14:20:33
 *  @version 1.0.0
 *  @note <b>SQL for table<b>
 *  <pre>
 *  MySQL
CREATE TABLE `{dbPrefix}constants` (
`const_id` int(11) NOT NULL AUTO_INCREMENT,
`const_name` varchar(64) NOT NULL DEFAULT '',
`const_value` varchar(64) NOT NULL DEFAULT '',
`const_immutable` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY (`const_id`),
UNIQUE KEY `config_key` (`const_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
 *
 *  PostgreSQL
CREATE SEQUENCE const_id_seq;
ALTER TABLE public.const_id_seq OWNER TO dbOwner;
CREATE TABLE constants (
    const_id integer DEFAULT nextval('const_id_seq'::regclass) NOT NULL,
    const_name character varying(64) NOT NULL,
    const_value character varying(64) NOT NULL
);
ALTER TABLE public.{dbPrefix}constants OWNER TO dbOwner;
ALTER TABLE ONLY {dbPrefix}constants
    ADD CONSTRAINT {dbPrefix}const_pkey PRIMARY KEY (const_id);
ALTER TABLE ONLY {dbPrefix}constants
    ADD CONSTRAINT {dbPrefix}const_const_name_key UNIQUE (const_name);

Expected constants key=>value pairs

INSERT INTO {$dbPrefix}constants (const_name, const_value, const_immutable)
VALUES
('DISPLAY_DATE_FORMAT','m/d/Y',1),
('EMAIL_DOMAIN','revealitconsulting.com',1),
('EMAIL_FORM_TO','bill@revealitconsulting.com',1),
('ERROR_EMAIL_ADDRESS','webmaster@revealitconsulting.com',1),
('PAGE_TEMPLATE','index.twig',1),
('THEME_NAME','',1),
('ADMIN_THEME_NAME','',1),
('CSS_DIR_NAME','css',1),
('HTML_DIR_NAME','html',1),
('JS_DIR_NAME','js',1),
('IMAGE_DIR_NAME','images',1),
('ADMIN_DIR_NAME','manager',1),
('ASSETS_DIR_NAME','assets',1),
('FILES_DIR_NAME','files',1),
('DISPLAY_PHONE_FORMAT','XXX-XXX-XXXX',1),
('THEMES_DIR','',1),
('RIGHTS_HOLDER','Reveal IT Consulting',1),
('ENCRYPT_TYPE','sha1',1),
('PRIVATE_DIR_NAME','private',1),
('TMP_DIR_NAME','tmp',1),
('DEVELOPER_MODE','true',1),
('SESSION_IDLE_TIME','1800',1);
 *  </pre>
 */

namespace Ritc\Library\Entities;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\EntityInterface;

class ConstantsEntity implements EntityInterface
{
    private $a_properties;

    public function __construct(array $a_properties = array())
    {
        $this->setAllProperties($a_properties);
    }
    public function getId()
    {
        return $this->a_properties['const_id'];
    }
    public function setId($value)
    {
        $this->a_properties['const_id'] = $value;
    }
    public function getName()
    {
        return $this->a_properties['const_name'];
    }
    public function setName($value)
    {
        $this->a_properties['const_name'] = $value;
    }
    public function getValue()
    {
        return $this->a_properties['const_value'];
    }
    public function setValue($value)
    {
        $this->a_properties['const_value'] = $value;
    }
    public function getImmutable()
    {
        return $this->a_properties['const_immutable'];
    }
    public function setImmutable($value)
    {
        $this->a_properties['const_immutable'] = $value;
    }
    public function getAllProperties()
    {
        return $this->a_properties;
    }
    public function setAllProperties(array $a_properties = array())
    {
        $required_keys = [
            'const_id',
            'const_name',
            'const_value',
            'const_immutable'
        ];
        $a_properties = Arrays::createRequiredPairs($a_properties, $required_keys, 'delete_undesired_keys');
        $this->a_properties = $a_properties;
    }
}
