--
-- Table structure for table {$dbPrefix}people
--

CREATE TABLE {$dbPrefix}people (
  people_id SERIAL,
  login_id character varying(60) NOT NULL,
  real_name character varying(50) NOT NULL,
  short_name character varying(8) NOT NULL DEFAULT '',
  password character varying(128) NOT NULL,
  description character varying(250) NOT NULL DEFAULT '',
  is_logged_in smallint NOT NULL DEFAULT 0,
  bad_login_count integer NOT NULL DEFAULT 0,
  bad_login_ts integer NOT NULL DEFAULT 0,
  is_active smallint NOT NULL DEFAULT 1,
  is_immutable smallint NOT NULL DEFAULT 0,
  created_on timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (people_id),
  UNIQUE (login_id)
);

INSERT INTO {$dbPrefix}people
    (login_id, real_name, short_name, password, description, is_logged_in, bad_login_count, bad_login_ts, is_active, is_immutable, created_on)
VALUES
    ('SuperAdmin','Super Admin','GSA','$2y$10$mAQZrjwnPDkfpdhmdfqxFuBJwY7w5HeCli2qs2H2Kg69w0MooNsJW','The all powerful Admin',0,0,0,1,1,'2012-08-12 07:55:28'),
    ('Admin','Admin','ADM','$2y$10$mAQZrjwnPDkfpdhmdfqxFuBJwY7w5HeCli2qs2H2Kg69w0MooNsJW','Allowed to admin the backend.',1,0,0,1,1,'2015-09-04 18:15:55');

