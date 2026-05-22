-- JaanaHai Carpool — PostgreSQL Schema
-- Converted from MySQL/MariaDB carpool.sql

CREATE TABLE IF NOT EXISTS users (
    uid         SERIAL PRIMARY KEY,
    name        VARCHAR(25)  NOT NULL,
    hash        VARCHAR(255) NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    gender      VARCHAR(10)  NOT NULL,
    contactno   BIGINT       NOT NULL,
    description TEXT         DEFAULT NULL,
    credits     INTEGER      DEFAULT 0
);

CREATE TABLE IF NOT EXISTS offers (
    id          SERIAL PRIMARY KEY,
    uid         INTEGER      NOT NULL,
    "from"      VARCHAR(250) NOT NULL,
    "to"        VARCHAR(250) NOT NULL,
    uptime      TIMESTAMP    NOT NULL,
    downtime    TIMESTAMP    NOT NULL DEFAULT '0001-01-01 00:00:00',
    people      INTEGER      NOT NULL DEFAULT 1,
    price       INTEGER      NOT NULL DEFAULT 0,
    vehicle     VARCHAR(250) NOT NULL,
    description TEXT         DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS route (
    routeid     SERIAL PRIMARY KEY,
    cid         INTEGER      NOT NULL,
    place       VARCHAR(250) NOT NULL,
    serialno    INTEGER      NOT NULL
);

CREATE TABLE IF NOT EXISTS notifications (
    slno        SERIAL PRIMARY KEY,
    sender      INTEGER      NOT NULL,
    receiver    INTEGER      NOT NULL,
    type        INTEGER      NOT NULL,
    cid         INTEGER      NOT NULL,
    timestamp   TIMESTAMP    NOT NULL DEFAULT NOW(),
    status      VARCHAR(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS comments (
    slno        SERIAL PRIMARY KEY,
    sender      INTEGER      NOT NULL,
    comment     TEXT         NOT NULL,
    cid         INTEGER      NOT NULL
);
