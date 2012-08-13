/* create users table */
CREATE TABLE users (
  username      VARCHAR(128) NOT NULL PRIMARY KEY,
  email         VARCHAR(128) NOT NULL,
  password      VARCHAR(128) NOT NULL,  /* in plain text */
  role          INTEGER NOT NULL,       /* 0: normal user, 1: administrator */
  first_name    VARCHAR(128),
  last_name     VARCHAR(128)
);
 
/* create posts table */
CREATE TABLE posts (
  post_id       INTEGER NOT NULL PRIMARY KEY,
  author_id     VARCHAR(128) NOT NULL
                CONSTRAINT fk_author REFERENCES users(username),
  create_time   INTEGER NOT NULL,        /* UNIX timestamp */
  title         VARCHAR(256) NOT NULL,   /* title of the post */
  content       TEXT,                    /* post body */
  status        INTEGER NOT NULL         /* 0: published; 1: draft; 2: pending; 2: denied */
);

/* create problem table */
CREATE TABLE problems (
  problem_id    INTEGER NOT NULL PRIMARY KEY,
  subject       VARCHAR(128) NOT NULL,   /* the subject: maths, phys, ... */
  chapter       VARCHAR(128) NOT NULL,   /* a chapter within the subject */
  problem_type  VARCHAR(128) NOT NULL,   /* problem type: text, equation, graph, composite, ... */
  content       TEXT                     /* problem definition: depending on the type it can be either 
                                            text + answer, page (class) name, ?... , I don't know yet what*/
);

/* insert some initial data records for testing */
INSERT INTO users VALUES ('admin', 'admin@example.com', 'demo', 1, 'Qiang', 'Xue');
INSERT INTO users VALUES ('demo', 'demo@example.com', 'demo', 0, 'Wei', 'Zhuo');
INSERT INTO posts VALUES (NULL, 'admin', 1175708482, 'first post', 'this is my first post', 0);
