--
-- Table structure for table {$dbPrefix}routes_group_map
--
CREATE TABLE {$dbPrefix}routes_group_map (
  rgm_id SERIAL,
  route_id integer NOT NULL DEFAULT '0',
  group_id integer NOT NULL DEFAULT '0',
  PRIMARY KEY (rgm_id)
);
CREATE INDEX rgm_group_id_idx ON {$dbPrefix}routes_group_map (group_id);
CREATE INDEX rgm_route_id_idx ON {$dbPrefix}routes_group_map (route_id);
CREATE UNIQUE INDEX rgm_idx ON {$dbPrefix}routes_group_map (route_id, group_id);
ALTER TABLE {$dbPrefix}routes_group_map
    ADD CONSTRAINT {$dbPrefix}routes_group_map_ibfk_1 FOREIGN KEY (route_id) REFERENCES {$dbPrefix}routes (route_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE {$dbPrefix}routes_group_map
    ADD CONSTRAINT {$dbPrefix}routes_group_map_ibfk_2 FOREIGN KEY (group_id) REFERENCES {$dbPrefix}groups (group_id) ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO {$dbPrefix}routes_group_map
    (route_id, group_id)
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

