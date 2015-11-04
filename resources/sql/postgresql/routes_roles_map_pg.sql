--
-- Table structure for table {$dbPrefix}routes_roles_map
--

CREATE TABLE {$dbPrefix}routes_roles_map (
  rrm_id SERIAL,
  route_id integer NOT NULL DEFAULT '0',
  role_id integer NOT NULL DEFAULT '0',
  PRIMARY KEY (rrm_id)
);
CREATE INDEX rrm_route_id_idx ON {$dbPrefix}routes_roles_map (route_id);
CREATE INDEX rrm_role_id_idx ON {$dbPrefix}routes_roles_map (role_id);
CREATE UNIQUE INDEX rrm_key ON {$dbPrefix}routes_roles_map (route_id, role_id);
ALTER TABLE ONLY {$dbPrefix}routes_roles_map
    ADD CONSTRAINT {$dbPrefix}routes_roles_map_ibfk_1 FOREIGN KEY (route_id) REFERENCES {$dbPrefix}routes (route_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ONLY {$dbPrefix}routes_roles_map
    ADD CONSTRAINT {$dbPrefix}routes_roles_map_ibfk_2 FOREIGN KEY (role_id) REFERENCES {$dbPrefix}roles (role_id) ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO {$dbPrefix}routes_roles_map
    (route_id, role_id)
VALUES
    (1,1),
    (1,2),
    (2,1),
    (2,2),
    (2,3),
    (2,4),
    (2,5),
    (3,1),
    (3,2),
    (4,1),
    (4,2),
    (5,1),
    (5,2),
    (6,1),
    (6,2),
    (7,1),
    (7,2),
    (8,1),
    (8,2),
    (9,1),
    (10,1),
    (10,2),
    (10,3),
    (10,4),
    (10,5);

