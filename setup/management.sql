DROP FUNCTION IF EXISTS statement;
DROP FUNCTION IF EXISTS pending_transactions;

CREATE OR REPLACE FUNCTION statement(account INT, month INT, year INT)
    RETURNS TABLE(day timestamp,
                  transaction_amount DOUBLE PRECISION,
                  account_balance DOUBLE PRECISION,
                  transaction_description TEXT
                 ) AS $$
DECLARE
BEGIN
    RETURN QUERY SELECT s.date, s.amount, s.running_total AS running_total, s.description FROM
                (SELECT date, description, amount, SUM(amount) OVER (ORDER BY date ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS running_total
                 FROM transactions
                 WHERE account_number = account) s
                WHERE EXTRACT(YEAR FROM date) = year AND EXTRACT(MONTH FROM date) = month
                 ORDER BY s.date;
END
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION pending_transactions(account INT)
    RETURNS TABLE(day timestamp,
                  transaction_amount DOUBLE PRECISION,
                  account_balance DOUBLE PRECISION,
                  transaction_description TEXT
                 ) AS $$
DECLARE
BEGIN
    RETURN QUERY SELECT * FROM statement(account, EXTRACT(MONTH FROM now())::INT, EXTRACT(YEAR FROM now())::INT);
END
$$ LANGUAGE plpgsql;


CREATE OR REPLACE VIEW branch_info AS (
    SELECT name, cast(a.number AS TEXT) || ' ' || a.direction || ' ' || a.street_name || ', ' || a.city || ', ' || a.state || ', ' || a.zipcode AS address FROM branch
    JOIN addresses a on a.id = branch.address
);

SELECT * FROM branch_info;


SELECT * FROM pending_transactions(1);
-- SELECT * FROM statement(1, 11, 2022);