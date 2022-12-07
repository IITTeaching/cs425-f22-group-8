CREATE OR REPLACE VIEW branch_info AS (
    SELECT name, cast(a.number AS TEXT) || ' ' || a.direction || ' ' || a.street_name || ', ' || a.city || ', ' || a.state || ', ' || a.zipcode AS address FROM branch
    JOIN addresses a on a.id = branch.address
);


CREATE OR REPLACE VIEW state_options AS (
    SELECT '<option value="' || abbreviation || '">' || name || '</option>' FROM States
);


CREATE OR REPLACE VIEW get_account_types AS (
    SELECT unnest(enum_range(NULL::AccountType))::text AS account_type
);

CREATE OR REPLACE FUNCTION username_in_use(user_name TEXT)
    RETURNS BOOL AS $username_already_in_use$

DECLARE
    username_already_in_use BOOL;
BEGIN
   SELECT COUNT(*)::INT::BOOL INTO username_already_in_use FROM Logins l WHERE l.username = user_name;
    RETURN username_already_in_use;
END;

$username_already_in_use$ LANGUAGE PlpgSQL;


CREATE OR REPLACE FUNCTION email_in_use(email_to_check TEXT)
    RETURNS BOOL AS $email_already_in_use$

DECLARE
    email_already_in_use BOOL;
BEGIN
    SELECT COUNT(*)::INT::BOOL INTO email_already_in_use FROM Customers c WHERE c.email = email_to_check;
    RETURN email_already_in_use;
END;

$email_already_in_use$ LANGUAGE PlpgSQL;