--
-- Table structure for table {$dbPrefix}roles
--

CREATE TABLE {$dbPrefix}roles (
  role_id SERIAL,
  role_name character varying(20) NOT NULL,
  role_description text NOT NULL,
  role_level integer NOT NULL DEFAULT '4',
  role_immutable smallint NOT NULL DEFAULT '0',
  PRIMARY KEY (role_id)
);
CREATE UNIQUE INDEX roles_role_name_idx ON {$dbPrefix}roles (role_name);

INSERT INTO {$dbPrefix}roles
    (role_name, role_description, role_level, role_immutable)
VALUES
    ('superadmin','Has Access to Everything.',1,1),
    ('admin','Has complete access to the administration area.',2,1),
    ('editor','Can modify the CMS content.',3,1),
    ('registered','Registered User',4,1),
    ('anonymous','Anonymous User',5,1);

