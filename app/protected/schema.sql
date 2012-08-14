CREATE TABLE chapters (
  chapter_id    INTEGER NOT NULL PRIMARY KEY,
  subject       VARCHAR(128) NOT NULL   /* the subject: maths, phys, ... */
                CONSTRAINT fk_subject REFERENCES subjects(name),
  name          VARCHAR(128) NOT NULL
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
  chapter_id    INTEGER NOT NULL         /* a chapter within the subject */
                CONSTRAINT fk_chapter REFERENCES chapters(chapter_id),
  problem_type  VARCHAR(128) NOT NULL,   /* problem type: text, equation, graph, composite, ... */
  content       TEXT                     /* problem definition: depending on the type it can be either 
                                            text + answer, page (class) name, ?... , I don't know yet what*/
);

/* create subjects table */
CREATE TABLE subjects (
  name          VARCHAR(128) NOT NULL PRIMARY KEY
);

/* create users table */
CREATE TABLE users (
  username      VARCHAR(128) NOT NULL PRIMARY KEY,
  email         VARCHAR(128) NOT NULL,
  password      VARCHAR(128) NOT NULL,  /* in plain text */
  role          INTEGER NOT NULL,       /* 0: normal user, 1: administrator */
  first_name    VARCHAR(128),
  last_name     VARCHAR(128)
);

/* insert some initial data records for testing */
INSERT INTO users VALUES ('admin', 'admin@example.com', 'demo', 1, 'Qiang', 'Xue');
INSERT INTO users VALUES ('demo', 'demo@example.com', 'demo', 0, 'Wei', 'Zhuo');
INSERT INTO posts VALUES (NULL, 'admin', 1175708482, 'first post', 'this is my first post', 0);
insert into subjects values ('maths');
insert into subjects values ('phys');
insert into chapters values (null,'maths','Chapter 1');
insert into chapters values (null,'maths','Chapter 2');
insert into chapters values (null,'maths','Chapter 3');
insert into chapters values (null,'physs','Test');
insert into problems values (null,1,'text','<problem><text>Problem question 1.</text><answer>The answer 1.</answer></problem>');
insert into problems values (null,1,'text','<problem><text>Problem question 2.</text><answer>The answer 2.</answer></problem>');
insert into problems values (null,1,'text','<problem><text>Problem question 3.</text><answer>The answer 3.</answer></problem>');
insert into problems values (null,1,'text','<problem><text>Problem question 4.</text><answer>The answer 4.</answer></problem>');
insert into problems values (null,1,'text','<problem><text>Problem question 5.</text><answer>The answer 5.</answer></problem>');
insert into problems values (null,2,'text','<problem><text>Problem question 6.</text><answer>The answer 6.</answer></problem>');
insert into problems values (null,2,'text','<problem><text>Problem question 7.</text><answer>The answer 7.</answer></problem>');
insert into problems values (null,3,'text','<problem><text>Problem question 8.</text><answer>The answer 8.</answer></problem>');
insert into problems values (null,3,'text','<problem><text>Problem question 9.</text><answer>The answer 9.</answer></problem>');
insert into problems values (null,4,'text','<problem><text>Problem question 10.</text><answer>The answer 10.</answer></problem>');
insert into problems values (null,4,'text','<problem><text>Problem question 11.</text><answer>The answer 11.</answer></problem>');
