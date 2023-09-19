create table reservation
(
    id integer primary key,
    name varchar(255) not null,
    period tstzrange not null
);