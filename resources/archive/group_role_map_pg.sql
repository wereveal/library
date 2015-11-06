--
-- Table structure for table {$dbPrefix}group_role_map
--

CREATE TABLE {$dbPrefix}group_role_map (
  grm_id SERIAL,
  group_id integer NOT NULL UNIQUE,
  role_id integer NOT NULL,
  PRIMARY KEY (grm_id)
);

CREATE INDEX grm_role_id_idx on {$dbPrefix}group_role_map (role_id);
CREATE INDEX grm_group_id_idx on {$dbPrefix}group_role_map (group_id);

ALTER TABLE ONLY {$dbPrefix}group_role_map
    ADD CONSTRAINT {$dbPrefix}grm_ibfk_1 FOREIGN KEY (group_id) REFERENCES {$dbPrefix}groups (group_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ONLY {$dbPrefix}group_role_map
    ADD CONSTRAINT {$dbPrefix}grm_ibfk_2 FOREIGN KEY (role_id) REFERENCES {$dbPrefix}roles (role_id) ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO {$dbPrefix}group_role_map
    (group_id, role_id)
VALUES
    (1,1),
    (2,2),
    (3,3),
    (4,4),
    (5,5);

