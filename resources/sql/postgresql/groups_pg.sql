--
-- Table structure for table {$dbPrefix}groups
--

CREATE TABLE {$dbPrefix}groups (
    group_id SERIAL,
    group_name character varying(40) NOT NULL UNIQUE,
    group_description character varying(128) NOT NULL,
    group_auth_level integer NOT NULL DEFAULT 0,
    group_immutable smallint NOT NULL DEFAULT 0,
    PRIMARY KEY (group_id)
);

INSERT INTO {$dbPrefix}groups
    (group_name, group_description, group_auth_level, group_immutable)
VALUES
    ('SuperAdmin','The group for super administrators. There should be only a couple of these.',10,1),
    ('Managers','Most people accessing the manager should be in this group.',9,1),
    ('Editor','Editor for the CMS which doesn&#039;t exist in the FtpManager',5,1),
    ('Registered','The group for people that should&#039;t have access to the manager.',3,1),
    ('Anonymous','Not logged in, possibly unregistered',0,1);