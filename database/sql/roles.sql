-- CALAJUAN database roles. Run once in pgAdmin as the postgres user.

-- Create the two roles. LOGIN lets them sign in.
-- These passwords are for local development only; change them before deployment.
CREATE ROLE calajuan_app LOGIN PASSWORD 'change_me_app';
CREATE ROLE calajuan_readonly LOGIN PASSWORD 'change_me_readonly';

-- Both roles may connect to the database and look inside the public schema
-- (without USAGE, they couldn't even see the tables).
GRANT CONNECT ON DATABASE calajuan TO calajuan_app, calajuan_readonly;
GRANT USAGE ON SCHEMA public TO calajuan_app, calajuan_readonly;

-- calajuan_app: may read, add, change, and delete rows in every table.
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO calajuan_app;

-- INSERT needs the sequences, which generate the auto-numbered ids.
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO calajuan_app;

-- calajuan_readonly: may only read.
GRANT SELECT ON ALL TABLES IN SCHEMA public TO calajuan_readonly;

-- migrate:fresh deletes and recreates every table, which would wipe the grants above.
-- These default privileges re-apply them automatically to tables created later
-- by the current user (postgres, the one that runs your migrations).
ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO calajuan_app;
ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT USAGE, SELECT ON SEQUENCES TO calajuan_app;
ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT SELECT ON TABLES TO calajuan_readonly;