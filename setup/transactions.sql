CREATE OR REPLACE FUNCTION withdrawal(account_number INT, amount DOUBLE PRECISION, description TEXT)
RETURNS DOUBLE PRECISION AS $withdrawn$
DECLARE
    withdrawn DOUBLE PRECISION;
    not_valid BOOL;
BEGIN
   SELECT INTO not_valid ((amount > balance) AND (NOT can_go_negative)) AS not_valid FROM Account WHERE number = account_number;
   RAISE NOTICE 'Value: %', not_valid;
    IF not_valid THEN
        RAISE NOTICE 'This account can not withdrawal more money than it has.';
        withdrawn = 0;
    ELSE
        UPDATE Account SET balance = (balance - amount) WHERE number = account_number;
        INSERT INTO Transactions(account_number, type, amount, description) VALUES(account_number, 'Withdrawal', amount, description);
        withdrawn = amount;
    END IF;

    RETURN withdrawn;
END
$withdrawn$ LANGUAGE plpgsql;

-- SELECT withdrawal(1, 20, 'Test') AS amount;