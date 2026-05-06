create table users 
(
    id serial primary key,
    username varchar(100) unique not null,
    password text not null,
    role varchar(20) default 'user'
);


create table books 
(
    id serial primary key,
    title text not null,
    author text not null,
    year date,
    genre text,
    publisher text,
    pages integer,
    isbn text,
    rating NUMERIC(3,1),
    description text,
    created_by integer,
    foreign key (created_by) references users(id) on delete set null
);


create table wishlist 
(
    id serial primary key,
    user_id integer,
    book_id integer,
    foreign key (user_id) references users(id) on delete cascade,
    foreign key (book_id) references books(id) on delete cascade
);
