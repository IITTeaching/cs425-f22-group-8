DROP FUNCTION IF EXISTS statement;

CREATE OR REPLACE FUNCTION statement(account INT, month INT, year INT)
    RETURNS TABLE(day DATE,
                  transaction_type TransactionType,
                  transaction_amount DOUBLE PRECISION,
                  transaction_description TEXT
                 ) AS $$
DECLARE
BEGIN
    RETURN QUERY SELECT date, type, amount, description FROM Transactions
                    WHERE account_number = account AND EXTRACT(YEAR FROM date) = year AND EXTRACT(MONTH FROM date) = month
                    ORDER BY date; -- TODO: To show the account balance at that time, the query would have to go backwards through the ledger, using the current balance and going back.
END
$$ LANGUAGE plpgsql;

SELECT * FROM statement(1, 12, 2022);