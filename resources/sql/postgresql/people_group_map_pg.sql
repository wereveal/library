--
-- Table structure for table {$dbPrefix}people_group_map
--

CREATE TABLE {$dbPrefix}people_group_map (
  pgm_id SERIAL,
  people_id integer NOT NULL,
  group_id integer NOT NULL DEFAULT '3',
  PRIMARY KEY (pgm_id)
);
CREATE INDEX pgm_people_id_idx on {$dbPrefix}people_group_map (people_id);
CREATE INDEX pgm_group_id_idx on {$dbPrefix}people_group_map (group_id);
CREATE UNIQUE INDEX people_group_idx on {$dbPrefix}people_group_map (people_id, group_id);

ALTER TABLE ONLY {$dbPrefix}people_group_map
    ADD CONSTRAINT {$dbPrefix}pgm_ibfk_1 FOREIGN KEY (people_id) REFERENCES {$dbPrefix}people (people_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ONLY {$dbPrefix}people_group_map
    ADD CONSTRAINT {$dbPrefix}pgm_ibfk_2 FOREIGN KEY (group_id) REFERENCES {$dbPrefix}groups (group_id) ON DELETE CASCADE ON UPDATE CASCADE;


INSERT INTO {$dbPrefix}people_group_map
    (people_id, group_id)
VALUES
    (1,1),
    (1,2),
    (1,3),
    (2,2),
    (2,3);

