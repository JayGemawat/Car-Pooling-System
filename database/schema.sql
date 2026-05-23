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
    credits     INTEGER      DEFAULT 0,
    random      VARCHAR(6)   DEFAULT ''
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
    description TEXT         DEFAULT NULL,
    status      VARCHAR(20)  DEFAULT 'open',
    distance_km NUMERIC(8,2) DEFAULT NULL
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
    status      VARCHAR(255) DEFAULT NULL,
    seen        BOOLEAN      NOT NULL DEFAULT FALSE,
    deleted_at  TIMESTAMP    DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS comments (
    slno        SERIAL PRIMARY KEY,
    sender      INTEGER      NOT NULL,
    comment     TEXT         NOT NULL,
    cid         INTEGER      NOT NULL
);

CREATE TABLE IF NOT EXISTS push_subscriptions (
    id         SERIAL PRIMARY KEY,
    uid        INTEGER NOT NULL,
    endpoint   TEXT    NOT NULL UNIQUE,
    p256dh     TEXT    NOT NULL,
    auth_key   TEXT    NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS rate_limits (
    id         SERIAL PRIMARY KEY,
    key        VARCHAR(255) NOT NULL,
    hits       INTEGER NOT NULL DEFAULT 1,
    window_end BIGINT  NOT NULL
);

-- Run these if tables already exist without the new columns:
-- ALTER TABLE users  ADD COLUMN IF NOT EXISTS random  VARCHAR(6)  DEFAULT '';
-- ALTER TABLE offers ADD COLUMN IF NOT EXISTS status  VARCHAR(20) DEFAULT 'open';
-- ALTER TABLE offers ADD COLUMN IF NOT EXISTS distance_km NUMERIC(8,2) DEFAULT NULL;
-- ALTER TABLE notifications ADD COLUMN IF NOT EXISTS seen       BOOLEAN   NOT NULL DEFAULT FALSE;
-- ALTER TABLE notifications ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP DEFAULT NULL;

-- Performance indexes
CREATE INDEX IF NOT EXISTS idx_notif_receiver ON notifications(receiver, deleted_at, seen);
CREATE INDEX IF NOT EXISTS idx_offers_uptime  ON offers(uptime, status);
CREATE INDEX IF NOT EXISTS idx_route_cid      ON route(cid, serialno);
